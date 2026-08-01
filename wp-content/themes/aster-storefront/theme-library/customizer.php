<?php

/**
 * Aster Storefront Theme Customizer
 *
 * @package aster_storefront
 */

// Sanitize callback.
require get_template_directory() . '/theme-library/customizer/sanitize-callback.php';

// Active Callback.
require get_template_directory() . '/theme-library/customizer/active-callback.php';

// Custom Controls.
require get_template_directory() . '/theme-library/customizer/custom-controls/custom-controls.php';

// Icon Controls.
require get_template_directory() . '/theme-library/customizer/custom-controls/icon-control.php';

function aster_storefront_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'aster_storefront_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'aster_storefront_customize_partial_blogdescription',
			)
		);
	}

	// Upsell Section.
	$wp_customize->add_section(
		new Aster_Storefront_Upsell_Section(
			$wp_customize,
			'upsell_section',
			array(
				'title'            => __( 'Aster Storefront Pro', 'aster-storefront' ),
				'button_text'      => __( 'Buy Pro', 'aster-storefront' ),
				'url'              => 'https://asterthemes.com/products/storefront-wordpress-theme',
				'background_color' => '#ff0000',
				'text_color'       => '#fff',
				'priority'         => 0,
			)
		)
	);

	// Doc Section.
	$wp_customize->add_section(
		new Aster_Storefront_Upsell_Section(
			$wp_customize,
			'doc_section',
			array(
				'title'            => __( 'Documentation', 'aster-storefront' ),
				'button_text'      => __( 'Free Doc', 'aster-storefront' ),
				'url'              => 'https://demo.asterthemes.com/docs/aster-storefront-free',
				'background_color' => '#ff0000',
				'text_color'       => '#fff',
				'priority'         => 1,
			)
		)
	);

	// Theme Options.
	require get_template_directory() . '/theme-library/customizer/theme-options.php';

	// Front Page Options.
	require get_template_directory() . '/theme-library/customizer/front-page-options.php';

	// Colors.
	require get_template_directory() . '/theme-library/customizer/colors.php';

}
add_action( 'customize_register', 'aster_storefront_customize_register' );

function aster_storefront_customize_partial_blogname() {
	bloginfo( 'name' );
}

function aster_storefront_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

function aster_storefront_customize_preview_js() {
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_script( 'aster-storefront-customizer', get_template_directory_uri() . '/resource/js/customizer' . $min . '.js', array( 'customize-preview' ), ASTER_STOREFRONT_VERSION, true );
}
add_action( 'customize_preview_init', 'aster_storefront_customize_preview_js' );

function aster_storefront_custom_control_scripts() {
	$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'aster-storefront-custom-controls-css', get_template_directory_uri() . '/resource/css/custom-controls' . $min . '.css', array(), '1.0', 'all' );

	wp_enqueue_script( 'aster-storefront-custom-controls-js', get_template_directory_uri() . '/resource/js/custom-controls' . $min . '.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' ), '1.0', true );
}
add_action( 'customize_controls_enqueue_scripts', 'aster_storefront_custom_control_scripts' );