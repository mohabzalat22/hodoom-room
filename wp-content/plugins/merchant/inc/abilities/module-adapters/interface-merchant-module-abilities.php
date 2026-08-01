<?php
/**
 * Merchant Module Abilities Interface.
 *
 * Defines the contract for module-specific campaign handling
 * and operation discovery in the WP Abilities API layer.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Module_Abilities_Interface
 *
 * Modules can implement this interface to provide richer
 * campaign summaries, custom field transformations, and
 * fine-grained operation discovery for the WP Abilities API.
 *
 * @since 2.3.0
 */
interface Merchant_Module_Abilities_Interface {

	/**
	 * Get the flexible_content field ID used for campaigns.
	 *
	 * Return null for settings-only modules.
	 *
	 * @return string|null Campaign field ID, or null if not campaign-based.
	 */
	public function get_campaign_field_id();

	/**
	 * Get the field used as the campaign title/name.
	 *
	 * @return string The campaign title field key.
	 */
	public function get_campaign_title_field();

	/**
	 * Get a human-readable summary of a campaign's configuration.
	 *
	 * @param array<string, mixed> $campaign_data Raw campaign field values.
	 *
	 * @return array<string, mixed> Key highlights of this campaign.
	 */
	public function summarize_campaign( $campaign_data );

	/**
	 * Return schema overrides, or null to use fully auto-generated schema.
	 *
	 * @return array<string, mixed>|null Schema overrides.
	 */
	public function get_schema_overrides();

	/**
	 * Get the list of available operations for this module.
	 *
	 * @return array<int, string> e.g., ['get-settings', 'update-settings', 'list-campaigns', ...]
	 */
	public function get_available_operations();
}
