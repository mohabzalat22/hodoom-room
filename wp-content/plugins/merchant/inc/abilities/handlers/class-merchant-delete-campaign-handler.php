<?php
/**
 * Merchant Delete Campaign Handler.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Delete_Campaign_Handler
 *
 * Deletes a campaign with confirmation guard.
 * Uses the registry adapter for campaign field detection.
 *
 * @since 2.3.0
 */
class Merchant_Delete_Campaign_Handler extends Merchant_Abstract_Handler {

	/**
	 * Campaign resolver instance.
	 *
	 * @var Merchant_Campaign_Resolver
	 */
	private $campaign_resolver;

	/**
	 * Abilities registry for adapter resolution.
	 *
	 * @var Merchant_Abilities_Registry
	 */
	private $registry;

	/**
	 * Constructor.
	 *
	 * @param Merchant_Campaign_Resolver  $campaign_resolver Campaign resolver.
	 * @param Merchant_Abilities_Registry $registry          Abilities registry.
	 */
	public function __construct( $campaign_resolver, $registry ) {
		$this->campaign_resolver = $campaign_resolver;
		$this->registry          = $registry;
	}

	/**
	 * Handle the delete-campaign request.
	 *
	 * @param array<string, mixed> $params { module_id, campaign_identifier, confirm }
	 *
	 * @return array<string, mixed>|WP_Error Response envelope or WP_Error on failure.
	 */
	public function handle( $params ) {
		$module_id  = isset( $params['module_id'] ) ? $params['module_id'] : '';
		$identifier = isset( $params['campaign_identifier'] ) ? $params['campaign_identifier'] : null;
		$confirm    = isset( $params['confirm'] ) ? $this->parse_bool( $params['confirm'] ) : false;

		$error = $this->preflight_write( $module_id );
		if ( null !== $error ) {
			return $error;
		}

		// Confirmation guard.
		if ( ! $confirm ) {
			return new WP_Error(
				'deletion_not_confirmed',
				"Set 'confirm' to true to delete the campaign.",
				array( 'status' => 400 )
			);
		}

		// UUID-only guard: reject index and name identifiers for TOCTOU safety.
		if ( ! wp_is_uuid( $identifier ) ) {
			return new WP_Error(
				'uuid_required',
				"Delete requires a campaign UUID (flexible_id). Use merchant/list-campaigns to find it.",
				array( 'status' => 400 )
			);
		}

		$resolved_adapter = $this->resolve_campaign_adapter( $this->registry, $module_id );
		if ( isset( $resolved_adapter['error'] ) ) {
			return $resolved_adapter['error'];
		}

		$campaign_field = $resolved_adapter['campaign_field'];
		$title_field    = $resolved_adapter['title_field'];

		// Resolve campaign.
		$resolved = $this->campaign_resolver->resolve( $module_id, $identifier, $campaign_field, $title_field );
		if ( isset( $resolved['error'] ) ) {
			return new WP_Error(
				$resolved['error']['code'],
				$resolved['error']['message'],
				array( 'status' => 404 )
			);
		}

		$index    = $resolved['index'];
		$campaign = $resolved['campaign'];
		$name     = isset( $campaign[ $title_field ] ) ? $campaign[ $title_field ] : '';

		// Remove and reindex.
		$module    = Merchant_Modules::get_module( $module_id );
		$settings  = $module ? $module->get_module_settings() : array();
		$campaigns = isset( $settings[ $campaign_field ] ) ? $settings[ $campaign_field ] : array();

		array_splice( $campaigns, $index, 1 );

		$settings[ $campaign_field ] = array_values( $campaigns );
		$this->save_module_settings( $module_id, $settings );

		return array(
			'success' => true,
			'data'    => array(
				'module_id'                 => $module_id,
				'deleted_index'             => $index,
				'deleted_name'              => $name,
				'remaining_campaigns_count' => count( $campaigns ),
			),
		);
	}
}
