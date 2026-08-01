<?php
/**
 * Merchant List Campaigns Handler.
 *
 * Handles the merchant/list-campaigns ability.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_List_Campaigns_Handler
 *
 * Lists campaigns for a campaign-based module with
 * summaries from the module adapter.
 *
 * @since 2.3.0
 */
class Merchant_List_Campaigns_Handler extends Merchant_Abstract_Handler {

	/**
	 * Abilities registry for adapter resolution.
	 *
	 * @var Merchant_Abilities_Registry
	 */
	private $registry;

	/**
	 * Constructor.
	 *
	 * @param Merchant_Abilities_Registry $registry Abilities registry.
	 */
	public function __construct( $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Handle the list-campaigns request.
	 *
	 * @param array<string, mixed> $params { module_id: string }
	 *
	 * @return array<string, mixed>|WP_Error Response envelope or WP_Error on failure.
	 */
	public function handle( $params ) {
		$module_id = isset( $params['module_id'] ) ? $params['module_id'] : '';

		$error = $this->preflight( $module_id );
		if ( null !== $error ) {
			return $error;
		}

		$resolved = $this->resolve_campaign_adapter( $this->registry, $module_id );
		if ( isset( $resolved['error'] ) ) {
			return $resolved['error'];
		}

		$adapter        = $resolved['adapter'];
		$campaign_field = $resolved['campaign_field'];
		$title_field    = $resolved['title_field'];

		// Read campaigns from settings.
		$module    = Merchant_Modules::get_module( $module_id );
		$settings  = $module ? $module->get_module_settings() : array();
		$campaigns = isset( $settings[ $campaign_field ] ) && is_array( $settings[ $campaign_field ] )
			? $settings[ $campaign_field ]
			: array();

		$output = array();
		foreach ( $campaigns as $index => $campaign_data ) {
			$output[] = array(
				'index'   => $index,
				'name'    => isset( $campaign_data[ $title_field ] ) ? $campaign_data[ $title_field ] : '',
				'summary' => $adapter->summarize_campaign( $campaign_data ),
			);
		}

		$field_schema = $this->registry->get_schema_generator()->get_campaign_fields_schema( $module_id, $campaign_field );

		return array(
			'success' => true,
			'data'    => array(
				'module_id'    => $module_id,
				'campaigns'    => $output,
				'total_count'  => count( $output ),
				'field_schema' => $field_schema,
			),
		);
	}
}
