<?php
/**
 * Merchant Abstract Handler.
 *
 * Base class for all ability handlers. Provides shared validation
 * methods: module existence, pro gate, active-module check,
 * campaign adapter resolution, and WP_Error construction.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Abstract_Handler
 *
 * @since 2.3.0
 */
abstract class Merchant_Abstract_Handler {

	/**
	 * Validate that a module exists and is not excluded.
	 *
	 * @param string $module_id The module identifier.
	 *
	 * @return WP_Error|null WP_Error if invalid, null if valid.
	 */
	protected function validate_module( $module_id ) {
		if ( Merchant_Abilities_Registry::is_excluded( $module_id )
			|| ! isset( Merchant_Admin_Modules::$modules_data[ $module_id ] ) ) {
			return new WP_Error(
				'module_not_found',
				sprintf( "Module '%s' not found.", $module_id ),
				array( 'status' => 404 )
			);
		}

		return null;
	}

	/**
	 * Check if a module is pro-only and Merchant Pro is not installed.
	 *
	 * @param string $module_id The module identifier.
	 *
	 * @return WP_Error|null WP_Error if pro-gated, null if allowed.
	 */
	protected function check_pro_gate( $module_id ) {
		$module_data = Merchant_Admin_Modules::$modules_data[ $module_id ];

		if ( ! empty( $module_data['pro'] ) && ! defined( 'MERCHANT_PRO_VERSION' ) ) {
			return new WP_Error(
				'module_is_pro',
				sprintf( "Module '%s' requires Merchant Pro.", $module_id ),
				array( 'status' => 403 )
			);
		}

		return null;
	}

	/**
	 * Check if a module is active.
	 *
	 * @param string $module_id The module identifier.
	 *
	 * @return WP_Error|null WP_Error if inactive, null if active.
	 */
	protected function require_active_module( $module_id ) {
		$active_modules = get_option( 'merchant-modules', array() );

		if ( empty( $active_modules[ $module_id ] ) ) {
			return new WP_Error(
				'module_not_active',
				sprintf( "Module '%s' must be active. Use toggle-module to activate it first.", $module_id ),
				array( 'status' => 422 )
			);
		}

		return null;
	}

	/**
	 * Run standard pre-flight checks: module exists + pro gate.
	 *
	 * @param string $module_id The module identifier.
	 *
	 * @return WP_Error|null First error encountered, or null if all pass.
	 */
	protected function preflight( $module_id ) {
		$error = $this->validate_module( $module_id );
		if ( null !== $error ) {
			return $error;
		}

		return $this->check_pro_gate( $module_id );
	}

	/**
	 * Run write pre-flight checks: module exists + pro gate + active.
	 *
	 * @param string $module_id The module identifier.
	 *
	 * @return WP_Error|null First error encountered, or null if all pass.
	 */
	protected function preflight_write( $module_id ) {
		$error = $this->preflight( $module_id );
		if ( null !== $error ) {
			return $error;
		}

		return $this->require_active_module( $module_id );
	}

	/**
	 * Resolve a campaign adapter and validate campaign support.
	 *
	 * @param Merchant_Abilities_Registry $registry  The abilities registry.
	 * @param string                      $module_id The module identifier.
	 *
	 * @return array{adapter: Merchant_Module_Abilities_Interface, campaign_field: string, title_field: string}|array{error: WP_Error}
	 */
	protected function resolve_campaign_adapter( $registry, $module_id ) {
		$adapter        = $registry->get_adapter( $module_id );
		$campaign_field = $adapter->get_campaign_field_id();

		if ( null === $campaign_field ) {
			return array(
				'error' => new WP_Error(
					'not_campaign_module',
					sprintf( "Module '%s' does not support campaigns.", $module_id ),
					array( 'status' => 422 )
				),
			);
		}

		return array(
			'adapter'        => $adapter,
			'campaign_field' => $campaign_field,
			'title_field'    => $adapter->get_campaign_title_field(),
		);
	}

	/**
	 * Save module settings using read-modify-write.
	 *
	 * @param string               $module_id The module identifier.
	 * @param array<string, mixed> $settings  The full settings array.
	 *
	 * @return void
	 */
	protected function save_module_settings( $module_id, $settings ) {
		$all_options               = get_option( 'merchant', array() );
		$all_options[ $module_id ] = $settings;
		update_option( 'merchant', $all_options );
	}

	/**
	 * Save a single module setting field.
	 *
	 * @param string $module_id The module identifier.
	 * @param string $field_id  The settings field to update.
	 * @param mixed  $value     The new value.
	 *
	 * @return void
	 */
	protected function save_module_setting( $module_id, $field_id, $value ) {
		Merchant_Admin_Options::set( $module_id, $field_id, $value );
	}

	/**
	 * Parse a boolean value from input that may arrive as a string.
	 *
	 * Transport layers (MCP, REST) may serialize booleans as strings.
	 * PHP's (bool) "false" evaluates to true (non-empty string), so
	 * we need explicit handling.
	 *
	 * @param mixed $value    The value to parse.
	 * @param bool  $fallback The fallback when value is not recognized.
	 *
	 * @return bool The parsed boolean.
	 */
	protected function parse_bool( $value, $fallback = false ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_string( $value ) ) {
			$lower = strtolower( trim( $value ) );
			if ( in_array( $lower, array( 'true', '1', 'yes' ), true ) ) {
				return true;
			}
			if ( in_array( $lower, array( 'false', '0', 'no', '' ), true ) ) {
				return false;
			}
		}

		if ( is_numeric( $value ) ) {
			return (bool) $value;
		}

		return $fallback;
	}
}
