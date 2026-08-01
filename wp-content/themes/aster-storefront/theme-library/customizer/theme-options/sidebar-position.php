<?php

/**
 * Sidebar Position
 *
 * @package aster_storefront
 */

$wp_customize->add_section(
	'aster_storefront_sidebar_position',
	array(
		'title' => esc_html__( 'Sidebar Position', 'aster-storefront' ),
		'panel' => 'aster_storefront_theme_options',
	)
);


// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_global_sidebar_separator', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_global_sidebar_separator', array(
	'label' => __( 'Global Sidebar Position', 'aster-storefront' ),
	'section' => 'aster_storefront_sidebar_position',
	'settings' => 'aster_storefront_global_sidebar_separator',
)));

// Sidebar Position - Global Sidebar Position.
$wp_customize->add_setting(
	'aster_storefront_sidebar_position',
	array(
		'sanitize_callback' => 'aster_storefront_sanitize_select',
		'default'           => 'right-sidebar',
	)
);

$wp_customize->add_control(
	'aster_storefront_sidebar_position',
	array(
		'label'   => esc_html__( 'Select Sidebar Position', 'aster-storefront' ),
		'section' => 'aster_storefront_sidebar_position',
		'type'    => 'select',
		'choices' => array(
			'right-sidebar' => esc_html__( 'Right Sidebar', 'aster-storefront' ),
			'left-sidebar'  => esc_html__( 'Left Sidebar', 'aster-storefront' ),
			'no-sidebar'    => esc_html__( 'No Sidebar', 'aster-storefront' ),
		),
	)
);

// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_post_sidebar_separator', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_post_sidebar_separator', array(
	'label' => __( 'Post Sidebar Position', 'aster-storefront' ),
	'section' => 'aster_storefront_sidebar_position',
	'settings' => 'aster_storefront_post_sidebar_separator',
)));

// Sidebar Position - Post Sidebar Position.
$wp_customize->add_setting(
	'aster_storefront_post_sidebar_position',
	array(
		'sanitize_callback' => 'aster_storefront_sanitize_select',
		'default'           => 'right-sidebar',
	)
);

$wp_customize->add_control(
	'aster_storefront_post_sidebar_position',
	array(
		'label'   => esc_html__( 'Select Sidebar Position', 'aster-storefront' ),
		'section' => 'aster_storefront_sidebar_position',
		'type'    => 'select',
		'choices' => array(
			'right-sidebar' => esc_html__( 'Right Sidebar', 'aster-storefront' ),
			'left-sidebar'  => esc_html__( 'Left Sidebar', 'aster-storefront' ),
			'no-sidebar'    => esc_html__( 'No Sidebar', 'aster-storefront' ),
		),
	)
);

// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_page_sidebar_separator', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_page_sidebar_separator', array(
	'label' => __( 'Page Sidebar Position', 'aster-storefront' ),
	'section' => 'aster_storefront_sidebar_position',
	'settings' => 'aster_storefront_page_sidebar_separator',
)));

// Sidebar Position - Page Sidebar Position.
$wp_customize->add_setting(
	'aster_storefront_page_sidebar_position',
	array(
		'sanitize_callback' => 'aster_storefront_sanitize_select',
		'default'           => 'right-sidebar',
	)
);

$wp_customize->add_control(
	'aster_storefront_page_sidebar_position',
	array(
		'label'   => esc_html__( 'Select Sidebar Position', 'aster-storefront' ),
		'section' => 'aster_storefront_sidebar_position',
		'type'    => 'select',
		'choices' => array(
			'right-sidebar' => esc_html__( 'Right Sidebar', 'aster-storefront' ),
			'left-sidebar'  => esc_html__( 'Left Sidebar', 'aster-storefront' ),
			'no-sidebar'    => esc_html__( 'No Sidebar', 'aster-storefront' ),
		),
	)
);

// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_sidebar_width_separator', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_sidebar_width_separator', array(
	'label' => __( 'Sidebar Width Setting', 'aster-storefront' ),
	'section' => 'aster_storefront_sidebar_position',
	'settings' => 'aster_storefront_sidebar_width_separator',
)));


$wp_customize->add_setting( 'aster_storefront_sidebar_width', array(
	'default'           => '30',
	'sanitize_callback' => 'aster_storefront_sanitize_range_value',
) );

$wp_customize->add_control(new Aster_Storefront_Customize_Range_Control($wp_customize, 'aster_storefront_sidebar_width', array(
	'section'     => 'aster_storefront_sidebar_position',
	'label'       => __( 'Adjust Sidebar Width', 'aster-storefront' ),
	'description' => __( 'Adjust the width of the sidebar.', 'aster-storefront' ),
	'input_attrs' => array(
		'min'  => 10,
		'max'  => 50,
		'step' => 1,
	),
)));

$wp_customize->add_setting( 'aster_storefront_sidebar_widget_font_size', array(
    'default'           => 24,
    'sanitize_callback' => 'absint',
) );

// Add control for site title size
$wp_customize->add_control( 'aster_storefront_sidebar_widget_font_size', array(
    'type'        => 'number',
    'section'     => 'aster_storefront_sidebar_position',
    'label'       => __( 'Sidebar Widgets Heading Font Size ', 'aster-storefront' ),
    'input_attrs' => array(
        'min'  => 10,
        'max'  => 100,
        'step' => 1,
    ),
));