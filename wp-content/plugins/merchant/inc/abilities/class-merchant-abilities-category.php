<?php
/**
 * Merchant Abilities Category.
 *
 * Registers the "merchant" ability category with WordPress.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Abilities_Category
 *
 * Registers the `merchant` ability category during the
 * `wp_abilities_api_categories_init` hook so that all
 * Merchant abilities are grouped under a single category
 * in the WordPress Abilities API.
 *
 * @since 2.3.0
 */
class Merchant_Abilities_Category {

	/**
	 * Hook into the WP Abilities API category registration.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'wp_abilities_api_categories_init', array( static::class, 'register_category' ) );
	}

	/**
	 * Register the "merchant" ability category with WordPress.
	 *
	 * @return void
	 */
	public static function register_category() {
		wp_register_ability_category( 'merchant', array(
			'label'       => __( 'Merchant — WooCommerce Toolkit', 'merchant' ),
			'description' => __( 'Manage Merchant modules, campaigns, settings, and analytics.', 'merchant' ),
		) );
	}
}
