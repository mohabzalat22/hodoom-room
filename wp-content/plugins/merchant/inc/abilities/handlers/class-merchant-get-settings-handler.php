<?php
/**
 * Merchant Get Settings Handler.
 *
 * Handles the merchant/get-module-settings ability.
 * Returns a module's settings with optional field schemas.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Get_Settings_Handler
 *
 * Reads module settings using the schema generator.
 * Works on both active and inactive modules (inactive
 * modules include is_active: false in the response).
 *
 * @since 2.3.0
 */
class Merchant_Get_Settings_Handler extends Merchant_Abstract_Handler {

	/**
	 * Schema generator instance.
	 *
	 * @var Merchant_Schema_Generator
	 */
	private $schema_generator;

	/**
	 * Constructor.
	 *
	 * @param Merchant_Schema_Generator $schema_generator Schema generator instance.
	 */
	public function __construct( $schema_generator ) {
		$this->schema_generator = $schema_generator;
	}

	/**
	 * Handle the get-module-settings request.
	 *
	 * @param array<string, mixed> $params { module_id: string, include_schema?: bool }
	 *
	 * @return array<string, mixed>|WP_Error Response envelope or WP_Error on failure.
	 */
	public function handle( $params ) {
		$module_id      = isset( $params['module_id'] ) ? $params['module_id'] : '';
		$include_schema = isset( $params['include_schema'] ) ? $this->parse_bool( $params['include_schema'], true ) : true;

		$error = $this->preflight( $module_id );
		if ( null !== $error ) {
			return $error;
		}

		// Check activation status.
		$active_modules = get_option( 'merchant-modules', array() );
		$is_active      = ! empty( $active_modules[ $module_id ] );

		// Generate settings via schema generator.
		$settings = $this->schema_generator->generate( $module_id, $include_schema );

		return array(
			'success' => true,
			'data'    => array(
				'module_id' => $module_id,
				'is_active' => $is_active,
				'settings'  => $settings,
			),
		);
	}
}
