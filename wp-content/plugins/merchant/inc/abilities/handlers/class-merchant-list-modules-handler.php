<?php
/**
 * Merchant List Modules Handler.
 *
 * Handles the merchant/list-modules ability.
 * Returns all modules with their type, status, and available operations.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_List_Modules_Handler
 *
 * Discovers and lists all Merchant modules with enriched metadata
 * for AI clients: module type, campaign count, available operations,
 * pro status, and activation state.
 *
 * @since 2.3.0
 */
class Merchant_List_Modules_Handler extends Merchant_Abstract_Handler {

	/**
	 * Handle the list-modules request.
	 *
	 * @param array<string, mixed> $params { filter?: string, section?: string }
	 *
	 * @return array<string, mixed> Response envelope.
	 */
	public function handle( $params ) {
		$filter  = isset( $params['filter'] ) ? $params['filter'] : '';
		$section = isset( $params['section'] ) ? $params['section'] : '';

		$modules_data = Merchant_Admin_Modules::$modules_data;
		$active_map   = get_option( 'merchant-modules', array() );
		$modules      = array();
		$active_count = 0;

		foreach ( $modules_data as $module_id => $data ) {
			// Exclude internal modules.
			if ( Merchant_Abilities_Registry::is_excluded( $module_id ) ) {
				continue;
			}

			$is_active = ! empty( $active_map[ $module_id ] );

			// Apply status filter.
			if ( 'active' === $filter && ! $is_active ) {
				continue;
			}
			if ( 'inactive' === $filter && $is_active ) {
				continue;
			}

			// Apply section filter.
			if ( '' !== $section && isset( $data['section'] ) && $data['section'] !== $section ) {
				continue;
			}

			if ( $is_active ) {
				++$active_count;
			}

			$module_entry = array(
				'module_id' => $module_id,
				'is_active' => $is_active,
				'is_pro'    => ! empty( $data['pro'] ),
				'section'   => isset( $data['section'] ) ? $data['section'] : '',
			);

			$modules[] = $module_entry;
		}

		return array(
			'success' => true,
			'data'    => array(
				'modules'      => $modules,
				'total_count'  => count( $modules ),
				'active_count' => $active_count,
			),
		);
	}
}
