<?php
/**
 * Merchant Get Product Settings Handler.
 *
 * Handles the merchant/get-product-settings ability. Reads the per-product
 * settings of a product-settings-pair module (pre-orders, add-to-cart-text,
 * and — when Pro is active — product-audio/video/brand-image) straight from
 * post meta, same as the Merchant_Metabox save path writes it.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Get_Product_Settings_Handler
 *
 * Works on both active and inactive modules (inactive modules include
 * is_active: false in the response) — reads mirror merchant/get-module-settings.
 *
 * @since 2.3.0
 */
class Merchant_Get_Product_Settings_Handler extends Merchant_Abstract_Handler {

	/**
	 * Handle the get-product-settings request.
	 *
	 * @param array<string, mixed> $params { product_id: int, module: string, include_schema?: bool }
	 *
	 * @return array<string, mixed>|WP_Error Response envelope or WP_Error on failure.
	 */
	public function handle( $params ) {
		$module_id      = isset( $params['module'] ) ? $params['module'] : '';
		$product_id     = isset( $params['product_id'] ) ? (int) $params['product_id'] : 0;
		$include_schema = isset( $params['include_schema'] ) ? $this->parse_bool( $params['include_schema'], true ) : true;

		$error = $this->preflight( $module_id );
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

		$fields         = Merchant_Product_Settings_Registry::get_ability_fields( $module_id );
		$active_modules = get_option( 'merchant-modules', array() );

		$data = array(
			'product_id' => $product_id,
			'module'     => $module_id,
			'is_active'  => ! empty( $active_modules[ $module_id ] ),
			'settings'   => $this->read_settings( $product_id, $fields ),
		);

		if ( $include_schema ) {
			$data['field_schema'] = $this->build_field_schema( $fields );
		}

		return array(
			'success' => true,
			'data'    => $data,
		);
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

	/**
	 * Read every descriptor field's raw stored value from post meta.
	 *
	 * @param int                                  $product_id The product id.
	 * @param array<string, array<string, mixed>> $fields     Map of meta_key => field_def.
	 *
	 * @return array<string, mixed>
	 */
	private function read_settings( $product_id, array $fields ) {
		$settings = array();

		foreach ( $fields as $field_id => $field_def ) {
			$settings[ $field_id ] = get_post_meta( $product_id, $field_id, true );
		}

		return $settings;
	}

	/**
	 * Build the JSON-schema-shaped field_schema from the descriptor fields.
	 *
	 * @param array<string, array<string, mixed>> $fields Map of meta_key => field_def.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function build_field_schema( array $fields ) {
		$schema = array();

		foreach ( $fields as $field_id => $field_def ) {
			$schema[ $field_id ] = $this->field_def_to_property( $field_def );
		}

		return $schema;
	}

	/**
	 * Translate a descriptor field_def into a JSON-schema property.
	 *
	 * Mirrors Merchant_Pro_Bundle_Abilities::field_def_to_property() for the
	 * scalar types, and adds the array-shaped wrapper types the product-settings
	 * validator introduces (multiselect/user_list/url_list/uploads/media/media_url).
	 *
	 * @param array<string, mixed> $field_def A single field definition from the descriptor.
	 *
	 * @return array<string, mixed>
	 */
	private function field_def_to_property( array $field_def ) {
		$type = isset( $field_def['type'] ) ? $field_def['type'] : 'text';

		switch ( $type ) {
			case 'switcher':
				$property = array( 'type' => 'boolean' );
				break;

			case 'number':
			case 'range':
				$property = array( 'type' => 'number' );
				if ( isset( $field_def['min'] ) ) {
					$property['minimum'] = $field_def['min'];
				}
				if ( isset( $field_def['max'] ) ) {
					$property['maximum'] = $field_def['max'];
				}
				break;

			case 'select':
			case 'radio':
				$property = array( 'type' => 'string' );
				if ( isset( $field_def['options'] ) ) {
					$property['enum'] = array_keys( $field_def['options'] );
				}
				break;

			case 'multiselect':
				$property = array(
					'type'  => 'array',
					'items' => array( 'type' => 'string' ),
				);
				if ( isset( $field_def['options'] ) ) {
					$property['items']['enum'] = array_keys( $field_def['options'] );
				}
				break;

			case 'user_list':
				$property = array(
					'type'  => 'array',
					'items' => array( 'type' => 'integer' ),
				);
				break;

			case 'url_list':
				$property = array(
					'type'  => 'array',
					'items' => array( 'type' => 'string' ),
				);
				break;

			case 'uploads':
				$item     = empty( $field_def['thumb'] )
					? array( 'type' => 'string' )
					: array(
						'type'       => 'object',
						'properties' => array(
							'src'   => array( 'type' => 'string' ),
							'thumb' => array( 'type' => 'integer' ),
						),
					);
				$property = array(
					'type'  => 'array',
					'items' => $item,
				);
				break;

			case 'media':
				$property = array( 'type' => 'integer' );
				break;

			default:
				$property = array( 'type' => 'string' );
				break;
		}

		if ( isset( $field_def['ai_meta']['usage_hint'] ) ) {
			$property['x-merchant-usage-hint'] = $field_def['ai_meta']['usage_hint'];
		}

		return $property;
	}
}
