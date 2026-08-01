<?php
/**
 * Merchant Update Settings Handler.
 *
 * Handles the merchant/update-module-settings ability.
 * Validates, sanitizes, and applies field updates with before/after diffs.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Update_Settings_Handler
 *
 * Validates input fields via the field validator pipeline,
 * applies valid updates using read-modify-write on the
 * module's settings, and returns before/after diffs.
 *
 * Requires the module to be active (write operations on
 * inactive modules are blocked by design).
 *
 * @since 2.3.0
 */
class Merchant_Update_Settings_Handler extends Merchant_Abstract_Handler {

	/**
	 * Field validator instance.
	 *
	 * @var Merchant_Field_Validator
	 */
	private $field_validator;

	/**
	 * Constructor.
	 *
	 * @param Merchant_Field_Validator $field_validator Field validator instance.
	 */
	public function __construct( $field_validator ) {
		$this->field_validator = $field_validator;
	}

	/**
	 * Handle the update-module-settings request.
	 *
	 * @param array<string, mixed> $params { module_id: string, updates: array<string, mixed> }
	 *
	 * @return array<string, mixed>|WP_Error Response envelope or WP_Error on failure.
	 */
	public function handle( $params ) {
		$module_id = isset( $params['module_id'] ) ? $params['module_id'] : '';
		$updates   = isset( $params['updates'] ) ? $params['updates'] : array();

		$error = $this->preflight_write( $module_id );
		if ( null !== $error ) {
			return $error;
		}

		// Validate and sanitize via the field validator pipeline.
		$validation = $this->field_validator->validate_and_sanitize( $module_id, $updates );

		// All fields failed → WP_Error.
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

		// Read current settings for diff.
		$module   = Merchant_Modules::get_module( $module_id );
		$settings = $module ? $module->get_module_settings() : array();

		// Build before/after diff for valid fields.
		$updated_fields = array();
		foreach ( $validation['valid'] as $field_id => $new_value ) {
			$updated_fields[ $field_id ] = array(
				'previous_value' => isset( $settings[ $field_id ] ) ? $settings[ $field_id ] : null,
				'new_value'      => $new_value,
			);
			$settings[ $field_id ] = $new_value;
		}

		// Save if we have valid updates.
		if ( ! empty( $updated_fields ) ) {
			$this->save_module_settings( $module_id, $settings );
		}

		return array(
			'success'  => true,
			'data'     => array(
				'updated_fields' => $updated_fields,
				'updated_count'  => count( $updated_fields ),
			),
			'warnings' => $validation['errors'],
		);
	}
}
