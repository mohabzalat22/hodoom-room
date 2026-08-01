<?php
/**
 * Merchant Default Module Adapter.
 *
 * Auto-detects module type and capabilities from get_option_groups().
 * Provides generic campaign summaries for modules without custom adapters.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Default_Module_Adapter
 *
 * Falls back to auto-detected campaign handling when a module
 * doesn't register a custom adapter. Scans get_option_groups()
 * for flexible_content fields to determine module type.
 *
 * @since 2.3.0
 */
class Merchant_Default_Module_Adapter implements Merchant_Module_Abilities_Interface {

	/**
	 * The module identifier.
	 *
	 * @var string
	 */
	private $module_id;

	/**
	 * Cached campaign field ID (null = not scanned, false = not found).
	 *
	 * @var string|false|null
	 */
	private $campaign_field_id_cache = null;

	/**
	 * Constructor.
	 *
	 * @param string $module_id The module identifier.
	 */
	public function __construct( $module_id ) {
		$this->module_id = $module_id;
	}

	/**
	 * Get the flexible_content field ID used for campaigns.
	 *
	 * Scans get_option_groups() for a flexible_content field.
	 *
	 * @return string|null Campaign field ID, or null if not campaign-based.
	 */
	public function get_campaign_field_id() {
		if ( null !== $this->campaign_field_id_cache ) {
			return false === $this->campaign_field_id_cache ? null : $this->campaign_field_id_cache;
		}

		$module = Merchant_Modules::get_module( $this->module_id );

		if ( ! $module ) {
			$this->campaign_field_id_cache = false;
			return null;
		}

		$option_groups = $module->get_option_groups();

		foreach ( $option_groups as $group ) {
			if ( ! isset( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
				continue;
			}

			foreach ( $group['fields'] as $field ) {
				if ( isset( $field['type'] ) && 'flexible_content' === $field['type'] && isset( $field['id'] ) ) {
					$this->campaign_field_id_cache = $field['id'];
					return $field['id'];
				}
			}
		}

		$this->campaign_field_id_cache = false;
		return null;
	}

	/**
	 * Get the field used as the campaign title/name.
	 *
	 * Looks for 'title-field' in layout definition, falls back to 'offer-title'.
	 *
	 * @return string The campaign title field key.
	 */
	public function get_campaign_title_field() {
		$module = Merchant_Modules::get_module( $this->module_id );

		if ( ! $module ) {
			return 'offer-title';
		}

		$option_groups = $module->get_option_groups();

		foreach ( $option_groups as $group ) {
			if ( ! isset( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
				continue;
			}

			foreach ( $group['fields'] as $field ) {
				if ( isset( $field['type'] ) && 'flexible_content' === $field['type'] ) {
					if ( isset( $field['title-field'] ) ) {
						return $field['title-field'];
					}
					break 2;
				}
			}
		}

		return 'offer-title';
	}

	/**
	 * Get a generic campaign summary.
	 *
	 * Extracts key field values: status, discount type/value.
	 *
	 * @param array<string, mixed> $campaign_data Raw campaign field values.
	 *
	 * @return array<string, mixed> Key highlights of this campaign.
	 */
	public function summarize_campaign( $campaign_data ) {
		$summary = array();

		// Status detection.
		if ( isset( $campaign_data['status'] ) ) {
			$summary['status'] = $campaign_data['status'];
		}

		// Discount type/value if present.
		if ( isset( $campaign_data['discount_type'] ) ) {
			$summary['discount_type'] = $campaign_data['discount_type'];
		}
		if ( isset( $campaign_data['discount_value'] ) ) {
			$summary['discount_value'] = $campaign_data['discount_value'];
		}

		$summary['field_count'] = count( $campaign_data );

		return $summary;
	}

	/**
	 * Return schema overrides. Default adapter uses fully auto-generated schema.
	 *
	 * @return array<string, mixed>|null Always null for default adapter.
	 */
	public function get_schema_overrides() {
		return null;
	}

	/**
	 * Get the list of available operations for this module.
	 *
	 * Settings-only: get-settings, update-settings.
	 * Campaign-based: adds campaign CRUD.
	 * Product bundles: settings-only in Free; Pro adds bundle CRUD.
	 * Size chart: settings-only in Free; Pro adds size-chart CRUD + assign.
	 * Wait list: settings-only in Free; Pro adds subscriber list/update/delete.
	 *
	 * @return array<int, string> Available operation slugs.
	 */
	public function get_available_operations() {
		// Product bundles: bundle CRUD is a Pro-only capability.
		if ( 'product-bundles' === $this->module_id ) {
			$ops = array( 'get-settings', 'update-settings' );

			if ( defined( 'MERCHANT_PRO_VERSION' ) ) {
				$ops = array_merge( $ops, array(
					'list-bundles',
					'create-bundle',
					'update-bundle',
					'delete-bundle',
				) );
			}

			return $ops;
		}

		// Size chart: entity CRUD + assign is a Pro-only capability.
		if ( 'size-chart' === $this->module_id ) {
			$ops = array( 'get-settings', 'update-settings' );

			if ( defined( 'MERCHANT_PRO_VERSION' ) ) {
				$ops = array_merge( $ops, array(
					'list-size-charts',
					'create-size-chart',
					'update-size-chart',
					'delete-size-chart',
					'assign-size-chart',
				) );
			}

			return $ops;
		}

		// Wait list: subscriber triage is a Pro-only capability.
		if ( 'wait-list' === $this->module_id ) {
			$ops = array( 'get-settings', 'update-settings' );

			if ( defined( 'MERCHANT_PRO_VERSION' ) ) {
				$ops = array_merge( $ops, array(
					'list-waitlist-subscribers',
					'update-waitlist-subscriber-status',
					'delete-waitlist-subscriber',
				) );
			}

			return $ops;
		}

		$base = array( 'get-settings', 'update-settings' );

		if ( null !== $this->get_campaign_field_id() ) {
			$base = array_merge( $base, array(
				'list-campaigns',
				'create-campaign',
				'update-campaign',
				'delete-campaign',
			) );
		}

		return array_merge( $base, $this->product_meta_ops() );
	}

	/**
	 * Get the product-settings/attribute-swatches ops this module additionally advertises.
	 *
	 * pre-orders and add-to-cart-text are free modules served by the
	 * product-settings pair, so they always advertise it. product-audio,
	 * product-video, and product-brand-image are Pro-only, so the pair
	 * only appears when Pro is present. product-swatches mirrors that with
	 * the attribute-swatches pair.
	 *
	 * @return array<int, string>
	 */
	private function product_meta_ops() {
		$free_product_settings_modules = array( 'pre-orders', 'add-to-cart-text' );
		$pro_product_settings_modules  = array( 'product-audio', 'product-video', 'product-brand-image' );

		if ( in_array( $this->module_id, $free_product_settings_modules, true ) ) {
			return array( 'get-product-settings', 'update-product-settings' );
		}

		if ( in_array( $this->module_id, $pro_product_settings_modules, true ) && defined( 'MERCHANT_PRO_VERSION' ) ) {
			return array( 'get-product-settings', 'update-product-settings' );
		}

		if ( 'product-swatches' === $this->module_id && defined( 'MERCHANT_PRO_VERSION' ) ) {
			return array( 'get-attribute-swatches', 'update-attribute-swatches' );
		}

		return array();
	}
}
