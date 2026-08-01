<?php
/**
 * Merchant Update Product Settings Handler.
 *
 * Handles the merchant/update-product-settings ability. Validates and
 * writes per-product settings of a product-settings-pair module straight
 * to post meta, same as the Merchant_Metabox save path writes it, strictly
 * whitelisted against the module's descriptor.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Update_Product_Settings_Handler
 *
 * Requires the module to be active — write operations on inactive
 * modules are blocked by design (mirrors merchant/update-module-settings).
 *
 * @since 2.3.0
 */
class Merchant_Update_Product_Settings_Handler extends Merchant_Abstract_Handler {

	/**
	 * The product-settings validator.
	 *
	 * @var Merchant_Product_Settings_Validator
	 */
	private $validator;

	/**
	 * Constructor.
	 *
	 * @param Merchant_Product_Settings_Validator|null $validator The product-settings validator; builds its own field validator when omitted.
	 */
	public function __construct( $validator = null ) {
		$this->validator = $validator ? $validator : new Merchant_Product_Settings_Validator( new Merchant_Field_Validator( new Merchant_Schema_Generator() ) );
	}

	/**
	 * Handle the update-product-settings request.
	 *
	 * @param array<string, mixed> $params { product_id: int, module: string, updates: array<string, mixed> }
	 *
	 * @return array<string, mixed>|WP_Error Response envelope or WP_Error on failure.
	 */
	public function handle( $params ) {
		$module_id  = isset( $params['module'] ) ? $params['module'] : '';
		$product_id = isset( $params['product_id'] ) ? (int) $params['product_id'] : 0;
		$updates    = isset( $params['updates'] ) ? $params['updates'] : array();

		$error = $this->preflight_write( $module_id );
		if ( null !== $error ) {
			return $error;
		}

		if ( ! in_array( $module_id, Merchant_Product_Settings_Registry::get_modules(), true ) ) {
			return $this->unsupported_module_error( $module_id );
		}

		$error = $this->validate_product( $product_id );
		if ( null !== $error ) {
			return $error;
		}

		$fields     = Merchant_Product_Settings_Registry::get_ability_fields( $module_id );
		$validation = $this->validator->validate( $fields, $updates );

		if ( empty( $validation['valid'] ) && ! empty( $validation['errors'] ) ) {
			return new WP_Error(
				'ability_invalid_input',
				'All submitted fields failed validation.',
				array(
					'status' => 400,
					'errors' => $validation['errors'],
				)
			);
		}

		$updated_fields = $this->write_fields( $product_id, $validation['valid'] );

		/**
		 * Fires after a product-settings update writes its validated fields.
		 *
		 * @param int                   $product_id     The product id.
		 * @param string                $module_id      The module identifier.
		 * @param array<string, mixed>  $updated_fields Field id => {previous_value, new_value}.
		 *
		 * @since 2.3.0
		 */
		do_action( 'merchant_product_settings_saved', $product_id, $module_id, $updated_fields );

		return array(
			'success'  => true,
			'data'     => array(
				'product_id'     => $product_id,
				'module'         => $module_id,
				'updated_fields' => $updated_fields,
				'updated_count'  => count( $updated_fields ),
			),
			'warnings' => $validation['errors'],
		);
	}

	/**
	 * Write each validated field to post meta, capturing the before/after diff.
	 *
	 * @param int                   $product_id  The product id.
	 * @param array<string, mixed> $valid_fields Field id => validated value.
	 *
	 * @return array<string, array{previous_value: mixed, new_value: mixed}>
	 */
	private function write_fields( $product_id, array $valid_fields ) {
		$updated_fields = array();

		foreach ( $valid_fields as $field_id => $new_value ) {
			$previous_value = get_post_meta( $product_id, $field_id, true );

			update_post_meta( $product_id, $field_id, $new_value );

			$updated_fields[ $field_id ] = array(
				'previous_value' => $previous_value,
				'new_value'      => $new_value,
			);
		}

		return $updated_fields;
	}

	/**
	 * Build the unsupported_module error, echoing the currently supported module slugs.
	 *
	 * @param string $module_id The requested module identifier.
	 *
	 * @return WP_Error
	 */
	private function unsupported_module_error( $module_id ) {
		return new WP_Error(
			'unsupported_module',
			sprintf( "Module '%s' does not support the product-settings ability pair.", $module_id ),
			array(
				'status' => 422,
				'data'   => array( 'supported_modules' => Merchant_Product_Settings_Registry::get_modules() ),
			)
		);
	}

	/**
	 * Validate that the given id is a product post.
	 *
	 * @param int $product_id The product id.
	 *
	 * @return WP_Error|null WP_Error if invalid, null if valid.
	 */
	private function validate_product( $product_id ) {
		if ( ! get_post( $product_id ) || 'product' !== get_post_type( $product_id ) ) {
			return new WP_Error(
				'product_not_found',
				sprintf( "Product '%d' not found.", $product_id ),
				array( 'status' => 404 )
			);
		}

		return null;
	}
}
