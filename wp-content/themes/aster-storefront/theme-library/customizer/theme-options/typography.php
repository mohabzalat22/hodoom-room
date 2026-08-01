<?php

/**
 * Typography Settings
 *
 * @package aster_storefront
 */

$wp_customize->add_section(
	'aster_storefront_typography',
	array(
		'panel' => 'aster_storefront_theme_options',
		'title' => esc_html__( 'Typography Settings', 'aster-storefront' ),
	)
);

// Typography - Site Title Font.
$wp_customize->add_setting(
	'aster_storefront_site_title_font',
	array(
		'default'           => 'Raleway',
		'sanitize_callback' => 'aster_storefront_sanitize_google_fonts',
	)
);

$wp_customize->add_control(
	'aster_storefront_site_title_font',
	array(
		'label'    => esc_html__( 'Site Title Font Family', 'aster-storefront' ),
		'section'  => 'aster_storefront_typography',
		'settings' => 'aster_storefront_site_title_font',
		'type'     => 'select',
		'choices'  => aster_storefront_get_all_google_font_families(),
	)
);

// Typography - Site Description Font.
$wp_customize->add_setting(
	'aster_storefront_site_description_font',
	array(
		'default'           => 'Raleway',
		'sanitize_callback' => 'aster_storefront_sanitize_google_fonts',
	)
);

$wp_customize->add_control(
	'aster_storefront_site_description_font',
	array(
		'label'    => esc_html__( 'Site Description Font Family', 'aster-storefront' ),
		'section'  => 'aster_storefront_typography',
		'settings' => 'aster_storefront_site_description_font',
		'type'     => 'select',
		'choices'  => aster_storefront_get_all_google_font_families(),
	)
);

// Typography - Header Font.
$wp_customize->add_setting(
	'aster_storefront_header_font',
	array(
		'default'           => 'Playfair Display',
		'sanitize_callback' => 'aster_storefront_sanitize_google_fonts',
	)
);

$wp_customize->add_control(
	'aster_storefront_header_font',
	array(
		'label'    => esc_html__( 'Heading Font Family', 'aster-storefront' ),
		'section'  => 'aster_storefront_typography',
		'settings' => 'aster_storefront_header_font',
		'type'     => 'select',
		'choices'  => aster_storefront_get_all_google_font_families(),
	)
);

// Typography - Body Font.
$wp_customize->add_setting(
	'aster_storefront_body_font',
	array(
		'default'           => 'Jost',
		'sanitize_callback' => 'aster_storefront_sanitize_google_fonts',
	)
);

$wp_customize->add_control(
	'aster_storefront_body_font',
	array(
		'label'    => esc_html__( 'Body Font Family', 'aster-storefront' ),
		'section'  => 'aster_storefront_typography',
		'settings' => 'aster_storefront_body_font',
		'type'     => 'select',
		'choices'  => aster_storefront_get_all_google_font_families(),
	)
);
