<?php
/**
 * Buy Now — Migration (v1 → v2).
 *
 * Converts flat module settings to campaign-based architecture.
 * Runs once on plugin update, creating a single "Default Campaign"
 * from existing settings to preserve backward compatibility.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Buy_Now_Migration
 *
 * @since 2.2.8
 */
class Merchant_Buy_Now_Migration {

	/**
	 * Data version option key.
	 *
	 * @var string
	 */
	const VERSION_OPTION = 'merchant_buy_now_data_version';

	/**
	 * Current data version.
	 *
	 * @var int
	 */
	const CURRENT_VERSION = 2;

	/**
	 * Check if migration is needed and run it.
	 *
	 * @return void
	 */
	public function maybe_migrate(): void {
		$version = (int) get_option( self::VERSION_OPTION, 0 );

		if ( $version >= self::CURRENT_VERSION ) {
			return;
		}

		// Prevent concurrent migration on high-traffic sites.
		$lock_key = 'merchant_buy_now_migrating';
		if ( get_transient( $lock_key ) ) {
			return;
		}
		set_transient( $lock_key, 1, MINUTE_IN_SECONDS );

		$this->migrate_v1_to_v2();
		update_option( self::VERSION_OPTION, self::CURRENT_VERSION );
		delete_transient( $lock_key );
	}

	/**
	 * Run v1 → v2 migration.
	 *
	 * @return void
	 */
	private function migrate_v1_to_v2(): void {
		$settings = Merchant_Admin_Options::get_all( 'buy-now' );

		// Already has campaigns — skip.
		if ( ! empty( $settings['campaigns'] ) ) {
			return;
		}

		$campaign = $this->build_default_campaign( $settings );

		// Save the campaigns array into module settings.
		Merchant_Admin_Options::set( 'buy-now', 'campaigns', array( $campaign ) );
	}

	/**
	 * Build a default campaign from legacy flat settings.
	 *
	 * Maps old exclusion toggles and button text to the new campaign structure.
	 * All new features (upsell, icons, cart clearing) default to off.
	 *
	 * @param array<string, mixed> $old_settings The legacy settings.
	 *
	 * @return array<string, mixed> The campaign data.
	 */
	public function build_default_campaign( array $old_settings ): array {
		$has_exclusion = ! empty( $old_settings['exclusion'] );

		return array(
			'layout'            => 'campaign-details',
			'flexible_id'       => function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : uniqid( 'mbn-', true ),
			'campaign_status'   => 'active',
			'offer-title'       => 'Default Campaign',

			// Targeting: show on all, use exclusions from old settings.
			'rules_to_display'  => 'all',
			'product_ids'       => array(),
			'category_slugs'    => array(),
			'tag_slugs'         => array(),
			'brand_slugs'       => array(),

			// Map old exclusions.
			'exclude_products_toggle'   => $has_exclusion && ! empty( $old_settings['excluded_products'] ) ? 1 : 0,
			'excluded_products'         => $old_settings['excluded_products'] ?? array(),
			'exclude_categories_toggle' => $has_exclusion && ! empty( $old_settings['excluded_categories'] ) ? 1 : 0,
			'excluded_categories'       => $old_settings['excluded_categories'] ?? array(),
			'exclude_tags_toggle'       => $has_exclusion && ! empty( $old_settings['excluded_tags'] ) ? 1 : 0,
			'excluded_tags'             => $old_settings['excluded_tags'] ?? array(),
			'exclude_brands_toggle'     => $has_exclusion && ! empty( $old_settings['excluded_brands'] ) ? 1 : 0,
			'excluded_brands'           => $old_settings['excluded_brands'] ?? array(),

			// User condition: all (v1 had no user targeting).
			'user_condition'         => 'all',
			'user_condition_roles'   => array(),
			'user_condition_users'   => array(),
			'user_exclusion_enabled' => 0,
			'exclude_roles'          => array(),
			'exclude_users'          => array(),

			// Behavior: preserve v1 defaults.
			'cart_action'         => 'keep',
			'redirect_to'        => 'checkout',
			'custom_redirect_url' => '',

			// Design: use old button text, no icon.
			'button_text'          => $old_settings['button-text'] ?? 'Buy Now',
			'button_icon'          => 'none',
			'button_icon_position' => 'before',
			'button_icon_svg'      => '',

			// Upsell: off.
			'upsell_enabled'      => 0,
			'upsell_product_ids'  => array(),
			'upsell_title'        => '',
			'upsell_accept_text'  => '',
			'upsell_decline_text' => '',
		);
	}
}
