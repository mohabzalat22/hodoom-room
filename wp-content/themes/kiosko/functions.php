<?php
/**
 * Kiosko functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Kiosko
 * @since Kiosko 1.0
 */


if ( ! function_exists( 'kiosko_support' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since Kiosko 1.0
	 *
	 * @return void
	 */
	function kiosko_support() {

		// Enqueue editor styles.
		add_editor_style( 'style.css' );

		// Make theme available for translation.
		load_theme_textdomain( 'kiosko' );
	}

endif;

add_action( 'after_setup_theme', 'kiosko_support' );

if ( ! function_exists( 'kiosko_styles' ) ) :

	/**
	 * Enqueue styles.
	 *
	 * @since Kiosko 1.0
	 *
	 * @return void
	 */
	function kiosko_styles() {
		// Register theme stylesheet.
		$theme_version = wp_get_theme()->get( 'Version' );

		$version_string = is_string( $theme_version ) ? $theme_version : false;
		wp_register_style(
			'kiosko-style',
			get_template_directory_uri() . '/style.css',
			array(),
			$version_string
		);

		// Enqueue theme stylesheet.
		wp_enqueue_style( 'kiosko-style' );

		// Enqueue the additional stylesheet for Twenty Twenty Three.
		if ( class_exists( 'WooCommerce' ) && function_exists( 'WC' ) && defined( 'WC_ABSPATH' ) && file_exists( WC_ABSPATH . 'assets/css/twenty-twenty-three.css' ) ) {

			wp_enqueue_style( 'woocommerce-twenty-twenty-three', WC()->plugin_url() . '/assets/css/twenty-twenty-three.css' );

			wp_dequeue_style( 'woocommerce-general' );
		}
	}

endif;

add_action( 'wp_enqueue_scripts', 'kiosko_styles' );

if ( ! function_exists( 'kiosko_woocommerce_init' ) ) :
	/**
	 * Initialize WooCommerce compatibility.
	 *
	 * @since Kiosko 1.0
	 * @return void
	 */
	function kiosko_woocommerce_init() {
		if ( ! class_exists( 'WooCommerce' ) || ! defined( 'WC_ABSPATH' ) || ! file_exists( WC_ABSPATH . 'includes/theme-support/class-wc-twenty-twenty-three.php' ) ) {
			return;
		}

		// Load WooCommerce compatibility file if WooCommerce is loaded and the compatibility file exists.
		include_once WC_ABSPATH . 'includes/theme-support/class-wc-twenty-twenty-three.php';
	}
endif;

add_action( 'after_setup_theme', 'kiosko_woocommerce_init' );

if ( ! function_exists( 'kiosko_product_search_form_action' ) ) :
	/**
	 * Make WooCommerce product searches submit to the shop archive.
	 *
	 * The core search block defaults to the site home URL, which causes
	 * product search forms to submit to the root instead of the shop base.
	 *
	 * @since Kiosko 1.0
	 *
	 * @param string $block_content The block content.
	 * @param array  $block         The full block, including attributes.
	 * @return string Modified block content.
	 */
	function kiosko_product_search_form_action( $block_content, $block ) {
		if ( 'core/search' !== ( $block['blockName'] ?? '' ) ) {
			return $block_content;
		}

		$query = $block['attrs']['query'] ?? array();
		if ( empty( $query['post_type'] ) || 'product' !== $query['post_type'] ) {
			return $block_content;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			return $block_content;
		}

		$shop_url = wc_get_page_permalink( 'shop' );
		if ( empty( $shop_url ) ) {
			return $block_content;
		}

		return preg_replace(
			'/<form([^>]*?)action=["\']([^"\']+)["\']/i',
			'<form$1action="' . esc_url( $shop_url ) . '"',
			$block_content,
			1
		);
	}
endif;

add_filter( 'render_block', 'kiosko_product_search_form_action', 10, 2 );

if ( ! function_exists( 'kiosko_redirect_filtered_home_to_shop' ) ) :
	/**
	 * Redirect filtered requests from the front page to the shop archive.
	 *
	 * WooCommerce product filters can build URLs from the current page context,
	 * which leads to the root URL being used instead of the shop base path.
	 *
	 * @since Kiosko 1.0
	 *
	 * @return void
	 */
	function kiosko_redirect_filtered_home_to_shop() {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		if ( ! is_front_page() && ! is_home() ) {
			return;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$shop_page_id = wc_get_page_id( 'shop' );
		$shop_url     = $shop_page_id ? get_permalink( $shop_page_id ) : wc_get_page_permalink( 'shop' );
		if ( empty( $shop_url ) ) {
			return;
		}

		$filter_params = array();
		foreach ( array_keys( $_GET ) as $key ) {
			$clean_key = sanitize_key( $key );
			if ( 'min_price' === $clean_key || 'max_price' === $clean_key || 'rating_filter' === $clean_key || 'filter_stock_status' === $clean_key || 0 === strpos( $clean_key, 'filter_' ) || 0 === strpos( $clean_key, 'query_type_' ) ) {
				$filter_params[ $clean_key ] = isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : '';
			}
		}

		if ( empty( $filter_params ) ) {
			return;
		}

		wp_safe_redirect( add_query_arg( $filter_params, $shop_url ) );
		exit;
	}
endif;

add_action( 'template_redirect', 'kiosko_redirect_filtered_home_to_shop' );
