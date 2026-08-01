<?php
/**
 * Merchant Toggle Module Handler.
 *
 * Handles the merchant/toggle-module ability.
 * Activates or deactivates a Merchant module.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Toggle_Module_Handler
 *
 * Activates or deactivates a Merchant module, firing the
 * same action hooks as the AJAX handler for consistency.
 *
 * @since 2.3.0
 */
class Merchant_Toggle_Module_Handler extends Merchant_Abstract_Handler {

	/**
	 * Handle the toggle-module request.
	 *
	 * @param array<string, mixed> $params { module_id: string, action: 'activate'|'deactivate' }
	 *
	 * @return array<string, mixed>|WP_Error Response envelope or WP_Error on failure.
	 */
	public function handle( $params ) {
		$module_id = isset( $params['module_id'] ) ? $params['module_id'] : '';
		$action    = isset( $params['action'] ) ? $params['action'] : '';

		// Pre-flight: exists + pro gate (no active check — toggle changes activation).
		$error = $this->preflight( $module_id );
		if ( null !== $error ) {
			return $error;
		}

		$active_modules = get_option( 'merchant-modules', array() );
		$was_active     = ! empty( $active_modules[ $module_id ] );

		if ( 'activate' === $action && ! $was_active ) {
			Merchant_Modules::set_module_active( $module_id );

			return $this->success_response( $module_id, 'inactive', 'active' );
		}

		if ( 'deactivate' === $action && $was_active ) {
			Merchant_Modules::set_module_inactive( $module_id );

			return $this->success_response( $module_id, 'active', 'inactive' );
		}

		// Noop — already in the requested state.
		$current = $was_active ? 'active' : 'inactive';

		return $this->success_response( $module_id, $current, $current );
	}

	/**
	 * Build a success response.
	 *
	 * @param string $module_id       Module identifier.
	 * @param string $previous_status Previous activation status.
	 * @param string $new_status      New activation status.
	 *
	 * @return array<string, mixed> Response envelope.
	 */
	private function success_response( $module_id, $previous_status, $new_status ) {
		return array(
			'success' => true,
			'data'    => array(
				'module_id'       => $module_id,
				'previous_status' => $previous_status,
				'new_status'      => $new_status,
			),
		);
	}
}
