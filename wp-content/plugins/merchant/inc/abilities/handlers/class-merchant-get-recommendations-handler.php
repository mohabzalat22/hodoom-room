<?php
/**
 * Merchant Get Recommendations Handler.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Get_Recommendations_Handler
 *
 * Handles the merchant/get-recommendations ability.
 * Delegates to the recommendations engine and wraps
 * the result in a response envelope.
 *
 * @since 2.3.0
 */
class Merchant_Get_Recommendations_Handler extends Merchant_Abstract_Handler {

	/**
	 * The abilities registry.
	 *
	 * @var Merchant_Abilities_Registry
	 */
	private $registry;

	/**
	 * Constructor.
	 *
	 * @param Merchant_Abilities_Registry $registry The abilities registry.
	 */
	public function __construct( $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Handle the get-recommendations request.
	 *
	 * @param array<string, mixed> $params { module_id?, focus?, date_range? }.
	 *
	 * @return array<string, mixed>|WP_Error Response envelope or WP_Error on failure.
	 */
	public function handle( $params ) {
		// Check analytics enabled.
		$analytics_enabled = Merchant_Option::get( 'global-settings', 'analytics_toggle', true );

		if ( ! $analytics_enabled ) {
			return new WP_Error(
				'analytics_disabled',
				'Analytics is disabled in Merchant global settings.',
				array( 'status' => 422 )
			);
		}

		$module_id  = isset( $params['module_id'] ) ? $params['module_id'] : null;
		$focus      = isset( $params['focus'] ) ? $params['focus'] : 'general';
		$date_range = isset( $params['date_range'] ) ? $params['date_range'] : array();

		$engine          = new Merchant_Recommendations_Engine( $this->registry );
		$recommendations = $engine->generate( $module_id, $focus, $date_range );

		return array(
			'success' => true,
			'data'    => array(
				'recommendations'       => $recommendations,
				'total_recommendations' => count( $recommendations ),
			),
		);
	}
}
