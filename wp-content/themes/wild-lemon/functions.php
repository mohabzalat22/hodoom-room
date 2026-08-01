<?php
/**
 * Wild Lemon functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

if ( ! function_exists( 'wild_lemon_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since Wild Lemon 1.0
	 *
	 * @return void
	 */
	function wild_lemon_setup() {
		add_editor_style( 'style.css' );
	}
endif;
add_action( 'after_setup_theme', 'wild_lemon_setup' );

if ( ! function_exists( 'wild_lemon_enqueue_styles' ) ) :
	/**
	 * Enqueues the theme stylesheet.
	 *
	 * @since Wild Lemon 1.0
	 *
	 * @return void
	 */
	function wild_lemon_enqueue_styles() {
		wp_enqueue_style(
			'wild-lemon-style',
			get_stylesheet_uri(),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'wild_lemon_enqueue_styles' );

if ( ! function_exists( 'wild_lemon_block_styles' ) ) :
	/**
	 * Registers the block style variations bundled with the theme.
	 *
	 * @since Wild Lemon 1.0
	 *
	 * @return void
	 */
	function wild_lemon_block_styles() {
		register_block_style(
			'core/post-terms',
			array(
				'name'  => 'wild-lemon-pill',
				'label' => __( 'Lemon Pill', 'wild-lemon' ),
			)
		);
		register_block_style(
			'core/post-terms',
			array(
				'name'  => 'wild-lemon-pill-outline',
				'label' => __( 'Outline Pill', 'wild-lemon' ),
			)
		);
	}
endif;
add_action( 'init', 'wild_lemon_block_styles' );

if ( ! function_exists( 'wild_lemon_pattern_categories' ) ) :
	/**
	 * Registers the block pattern category used by the theme.
	 *
	 * @since Wild Lemon 1.0
	 *
	 * @return void
	 */
	function wild_lemon_pattern_categories() {
		register_block_pattern_category(
			'wild-lemon',
			array(
				'label'       => __( 'Wild Lemon', 'wild-lemon' ),
				'description' => __( 'Patterns bundled with the Wild Lemon theme.', 'wild-lemon' ),
			)
		);
	}
endif;
add_action( 'init', 'wild_lemon_pattern_categories' );
