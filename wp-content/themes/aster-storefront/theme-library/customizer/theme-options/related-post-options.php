<?php

/**
 * Related Post Options
 *
 * @package aster_storefront
 */

$wp_customize->add_section(
	'aster_storefront_related_post_options',
	array(
		'title' => esc_html__( 'Related Post Options', 'aster-storefront' ),
		'panel' => 'aster_storefront_theme_options',
	)
);

// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_related_post_separator', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_related_post_separator', array(
	'label' => __( 'Enable / Disable Related Post Section', 'aster-storefront' ),
	'section' => 'aster_storefront_related_post_options',
	'settings' => 'aster_storefront_related_post_separator',
) ) );


// Post Options - Show / Hide Related Posts.
$wp_customize->add_setting(
	'aster_storefront_post_hide_related_posts',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_post_hide_related_posts',
		array(
			'label'   => esc_html__( 'Show / Hide Related Posts', 'aster-storefront' ),
			'section' => 'aster_storefront_related_post_options',
		)
	)
);

$wp_customize->add_setting(
    'aster_storefront_related_posts_count',
    array(
        'default'           => '',
        'sanitize_callback' => 'absint', 
    )
);

// Add control for number of related posts
$wp_customize->add_control(
    'aster_storefront_related_posts_count',
    array(
        'type'        => 'number',
        'label'       => esc_html__( 'Number of Related Posts to Display', 'aster-storefront' ),
        'section'     => 'aster_storefront_related_post_options',
        'input_attrs' => array(
            'min'  => 1,
            'max'  => 5,
            'step' => 1,
        ),
    )
);

// Post Options - Related Post Label.
$wp_customize->add_setting(
	'aster_storefront_post_related_post_label',
	array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

$wp_customize->add_control(
	'aster_storefront_post_related_post_label',
	array(
		'label'    => esc_html__( 'Related Posts Label', 'aster-storefront' ),
		'section'  => 'aster_storefront_related_post_options',
		'settings' => 'aster_storefront_post_related_post_label',
		'type'     => 'text',
	)
);