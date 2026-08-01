<?php
/**
 * Native block pattern registration.
 *
 * @package Botiga
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'botiga_get_block_pattern_categories' ) ) :
	/**
	 * Get shared Botiga block pattern categories.
	 *
	 * These categories live in the free theme so Botiga and Botiga Pro can both
	 * register native block patterns into the same stable category slugs.
	 *
	 * @since 2.4.6
	 *
	 * @return array
	 */
	function botiga_get_block_pattern_categories() {
		$categories = array(
			'botiga-hero'                => array(
				'label' => esc_html__( 'Botiga Hero', 'botiga' ),
			),
			'botiga-featured-products'   => array(
				'label' => esc_html__( 'Botiga Featured Products', 'botiga' ),
			),
			'botiga-product-categories'  => array(
				'label' => esc_html__( 'Botiga Product Categories', 'botiga' ),
			),
			'botiga-promotional-banners' => array(
				'label' => esc_html__( 'Botiga Promotional Banners', 'botiga' ),
			),
			'botiga-product-highlights'  => array(
				'label' => esc_html__( 'Botiga Product Highlights', 'botiga' ),
			),
			'botiga-testimonials'        => array(
				'label' => esc_html__( 'Botiga Testimonials', 'botiga' ),
			),
			'botiga-newsletter'          => array(
				'label' => esc_html__( 'Botiga Newsletter', 'botiga' ),
			),
			'botiga-trust-benefits'      => array(
				'label' => esc_html__( 'Botiga Trust / Benefits', 'botiga' ),
			),
			'botiga-cta'                 => array(
				'label' => esc_html__( 'Botiga CTA', 'botiga' ),
			),
			'botiga-shop'                => array(
				'label' => esc_html__( 'Botiga Shop', 'botiga' ),
			),
		);

		/**
		 * Filters the shared Botiga block pattern categories.
		 *
		 * @since 2.4.6
		 *
		 * @param array $categories Pattern categories keyed by category slug.
		 */
		return apply_filters( 'botiga_block_pattern_categories', $categories );
	}
endif;

if ( ! function_exists( 'botiga_get_block_patterns' ) ) :
	/**
	 * Get Botiga block patterns.
	 *
	 * The free theme can register starter patterns here. Botiga Pro and
	 * WooCommerce-specific files can extend this list through the same filter.
	 *
	 * @since 2.4.6
	 *
	 * @return array
	 */
	function botiga_get_block_patterns() {
		/**
		 * Filters the Botiga block pattern list.
		 *
		 * Each pattern should follow the native register_block_pattern() schema.
		 *
		 * @since 2.4.6
		 *
		 * @param array $patterns Pattern definitions keyed by pattern slug.
		 */
		return apply_filters( 'botiga_block_patterns', array() );
	}
endif;

if ( ! function_exists( 'botiga_register_block_pattern_categories' ) ) :
	/**
	 * Register Botiga block pattern categories.
	 *
	 * @since 2.4.6
	 *
	 * @return void
	 */
	function botiga_register_block_pattern_categories() {
		if ( ! function_exists( 'register_block_pattern_category' ) ) {
			return;
		}

		$categories = botiga_get_block_pattern_categories();

		if ( empty( $categories ) || ! is_array( $categories ) ) {
			return;
		}

		foreach ( $categories as $category_slug => $category_properties ) {
			if ( empty( $category_slug ) || empty( $category_properties['label'] ) ) {
				continue;
			}

			register_block_pattern_category( $category_slug, $category_properties );
		}
	}
endif;

if ( ! function_exists( 'botiga_register_block_patterns' ) ) :
	/**
	 * Register Botiga block patterns.
	 *
	 * @since 2.4.6
	 *
	 * @return void
	 */
	function botiga_register_block_patterns() {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		$patterns = botiga_get_block_patterns();

		if ( empty( $patterns ) || ! is_array( $patterns ) ) {
			return;
		}

		foreach ( $patterns as $pattern_slug => $pattern_properties ) {
			if ( empty( $pattern_slug ) || empty( $pattern_properties['title'] ) || empty( $pattern_properties['content'] ) ) {
				continue;
			}

			register_block_pattern( $pattern_slug, $pattern_properties );
		}
	}
endif;

add_action( 'init', 'botiga_register_block_pattern_categories', 9 );
add_action( 'init', 'botiga_register_block_patterns', 20 );