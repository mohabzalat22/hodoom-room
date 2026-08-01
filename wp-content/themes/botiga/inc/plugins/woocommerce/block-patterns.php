<?php
/**
 * WooCommerce block pattern registration.
 *
 * @package Botiga
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'botiga_get_woocommerce_block_patterns' ) ) :
	/**
	 * Get WooCommerce-specific Botiga block patterns.
	 *
	 * @since 2.4.6
	 *
	 * @return array
	 */
	function botiga_get_woocommerce_block_patterns() {
		return array();
	}
endif;

if ( ! function_exists( 'botiga_add_woocommerce_block_patterns' ) ) :
	/**
	 * Add WooCommerce-specific patterns to the shared Botiga pattern list.
	 *
	 * This file is only loaded from the WooCommerce integration layer, so these
	 * patterns are registered only when WooCommerce is active.
	 *
	 * @since 2.4.6
	 *
	 * @param array $patterns Existing block patterns.
	 * @return array
	 */
	function botiga_add_woocommerce_block_patterns( $patterns ) {
		$woocommerce_patterns = botiga_get_woocommerce_block_patterns();

		if ( empty( $woocommerce_patterns ) || ! is_array( $woocommerce_patterns ) ) {
			return $patterns;
		}

		return array_merge( $patterns, $woocommerce_patterns );
	}
endif;

add_filter( 'botiga_block_patterns', 'botiga_add_woocommerce_block_patterns' );