<?php

/**
 * Color Option
 *
 * @package aster_storefront
 */

// Primary Color.
$wp_customize->add_setting(
	'aster_storefront_primary_color',
	array(
		'default'           => '#ff0000',
		'sanitize_callback' => 'sanitize_hex_color',
	)
);

$wp_customize->add_control(
	new WP_Customize_Color_Control(
		$wp_customize,
		'aster_storefront_primary_color',
		array(
			'label'    => __( 'Primary Color', 'aster-storefront' ),
			'section'  => 'colors',
			'priority' => 5,
		)
	)
);