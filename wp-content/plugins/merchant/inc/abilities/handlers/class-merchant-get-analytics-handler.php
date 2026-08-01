<?php
/**
 * Merchant Get Analytics Handler.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Get_Analytics_Handler
 *
 * Handles the merchant/get-analytics ability.
 * Returns analytics data at global, module, or campaign scope.
 *
 * @since 2.3.0
 */
class Merchant_Get_Analytics_Handler extends Merchant_Abstract_Handler {

	/**
	 * Handle the get-analytics request.
	 *
	 * @param array<string, mixed> $params { scope, module_id?, campaign_id?, date_range?, metrics? }
	 *
	 * @return array<string, mixed>|WP_Error Response envelope or WP_Error on failure.
	 */
	public function handle( $params ) {
		$scope     = isset( $params['scope'] ) ? $params['scope'] : 'global';
		$module_id = isset( $params['module_id'] ) ? $params['module_id'] : '';

		// Check analytics enabled.
		$analytics_enabled = Merchant_Option::get( 'global-settings', 'analytics_toggle', true );

		if ( ! $analytics_enabled ) {
			return new WP_Error(
				'analytics_disabled',
				'Analytics is disabled in Merchant global settings.',
				array( 'status' => 422 )
			);
		}

		// Parse date range.
		$date_range = isset( $params['date_range'] ) ? $params['date_range'] : array();
		$start_date = isset( $date_range['start'] ) ? $date_range['start'] : '';
		$end_date   = isset( $date_range['end'] ) ? $date_range['end'] : '';

		// Default: last 30 days.
		if ( empty( $start_date ) || empty( $end_date ) ) {
			$end_date   = gmdate( 'm/d/y' );
			$start_date = gmdate( 'm/d/y', strtotime( '-30 days' ) );
		} else {
			// Convert ISO 8601 to m/d/y if needed.
			$start_date = $this->normalize_date( $start_date );
			$end_date   = $this->normalize_date( $end_date );
		}

		if ( false === $start_date || false === $end_date ) {
			return new WP_Error(
				'invalid_date_range',
				'Invalid date format. Use Y-m-d (ISO 8601) or m/d/y.',
				array( 'status' => 400 )
			);
		}

		// Create data provider and set dates.
		$provider = new Merchant_Analytics_Data_Provider();
		$provider->set_start_date( $start_date );
		$provider->set_end_date( $end_date );

		$data = array( 'scope' => $scope );

		// Pro gate for module-specific scopes.
		if ( 'global' !== $scope && ! empty( $module_id ) ) {
			if ( isset( Merchant_Admin_Modules::$modules_data[ $module_id ] ) ) {
				$module_data = Merchant_Admin_Modules::$modules_data[ $module_id ];
				if ( ! empty( $module_data['pro'] ) && ! defined( 'MERCHANT_PRO_VERSION' ) ) {
					return new WP_Error(
						'module_is_pro',
						sprintf( "Module '%s' requires Merchant Pro.", $module_id ),
						array( 'status' => 403 )
					);
				}
			}
		}

		switch ( $scope ) {
			case 'global':
				$data['metrics'] = array(
					'revenue'             => $provider->get_revenue(),
					'total_impressions'   => $provider->get_total_impressions(),
					'orders_count'        => $provider->get_orders_count(),
					'average_order_value' => $provider->get_average_order_value(),
					'conversion_rate'     => $provider->get_conversion_rate_percentage(),
				);
				break;

			case 'module':
				if ( empty( $module_id ) ) {
					return new WP_Error(
						'missing_module_id',
						'module_id is required for module scope.',
						array( 'status' => 400 )
					);
				}
				$data['module_id'] = $module_id;
				$data['metrics']   = array(
					'impressions'         => $provider->get_module_impressions( $module_id ),
					'clicks'              => $provider->get_module_clicks( $module_id ),
					'orders_count'        => $provider->get_module_orders_count( $module_id ),
					'revenue'             => $provider->get_module_revenue( $module_id ),
					'average_order_value' => $provider->get_module_average_order_value( $module_id ),
					'conversion_rate'     => $provider->get_module_conversion_rate_percentage( $module_id ),
					'ctr'                 => $provider->get_module_ctr_percentage( $module_id ),
				);
				break;

			case 'campaign':
				// FIX: was $params['campaign_identifier'], now matches schema.
				$campaign_id = isset( $params['campaign_id'] ) ? $params['campaign_id'] : '';
				if ( empty( $module_id ) || '' === $campaign_id ) {
					return new WP_Error(
						'missing_params',
						'module_id and campaign_id are required for campaign scope.',
						array( 'status' => 400 )
					);
				}
				$data['module_id']   = $module_id;
				$data['campaign_id'] = $campaign_id;
				$data['metrics']     = array(
					'impressions'         => $provider->get_campaign_impressions( $campaign_id, $module_id ),
					'clicks'              => $provider->get_campaign_clicks( $campaign_id, $module_id ),
					'orders_count'        => $provider->get_campaign_orders_count( $campaign_id, $module_id ),
					'revenue'             => $provider->get_campaign_revenue( $campaign_id, $module_id ),
					'average_order_value' => $provider->get_campaign_average_order_value( $campaign_id, $module_id ),
					'ctr'                 => $provider->get_campaign_ctr_percentage( $campaign_id, $module_id ),
				);
				break;
		}

		return array(
			'success' => true,
			'data'    => $data,
		);
	}

	/**
	 * Normalize a date string to m/d/y format.
	 *
	 * @param string $date Date string (Y-m-d or m/d/y).
	 *
	 * @return string|false Normalized date or false on failure.
	 */
	private function normalize_date( $date ) {
		// ISO 8601: Y-m-d.
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			$d = DateTime::createFromFormat( 'Y-m-d', $date );
			return $d ? $d->format( 'm/d/y' ) : false;
		}

		// Legacy: m/d/y.
		if ( preg_match( '#^\d{1,2}/\d{1,2}/\d{2,4}$#', $date ) ) {
			return $date;
		}

		return false;
	}
}
