<?php

/**
 * Single Post Options
 *
 * @package aster_storefront
 */

$wp_customize->add_section(
	'aster_storefront_single_post_options',
	array(
		'title' => esc_html__( 'Single Post Options', 'aster-storefront' ),
		'panel' => 'aster_storefront_theme_options',
	)
);


// Post Options - Show / Hide Date.
$wp_customize->add_setting(
	'aster_storefront_single_post_hide_date',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_single_post_hide_date',
		array(
			'label'   => esc_html__( 'Show / Hide Date', 'aster-storefront' ),
			'section' => 'aster_storefront_single_post_options',
		)
	)
);

// Post Options - Show / Hide Author.
$wp_customize->add_setting(
	'aster_storefront_single_post_hide_author',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_single_post_hide_author',
		array(
			'label'   => esc_html__( 'Show / Hide Author', 'aster-storefront' ),
			'section' => 'aster_storefront_single_post_options',
		)
	)
);

// Post Options - Show / Hide Comments.
$wp_customize->add_setting(
	'aster_storefront_single_post_hide_comments',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_single_post_hide_comments',
		array(
			'label'   => esc_html__( 'Show / Hide Comments', 'aster-storefront' ),
			'section' => 'aster_storefront_single_post_options',
		)
	)
);

// Post Options - Show / Hide Time.
$wp_customize->add_setting(
	'aster_storefront_single_post_hide_time',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_single_post_hide_time',
		array(
			'label'   => esc_html__( 'Show / Hide Time', 'aster-storefront' ),
			'section' => 'aster_storefront_single_post_options',
		)
	)
);

// Post Options - Show / Hide Category.
$wp_customize->add_setting(
	'aster_storefront_single_post_hide_category',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_single_post_hide_category',
		array(
			'label'   => esc_html__( 'Show / Hide Category', 'aster-storefront' ),
			'section' => 'aster_storefront_single_post_options',
		)
	)
);

// Post Options - Show / Hide Tag.
$wp_customize->add_setting(
	'aster_storefront_post_hide_tags',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_post_hide_tags',
		array(
			'label'   => esc_html__( 'Show / Hide Tag', 'aster-storefront' ),
			'section' => 'aster_storefront_single_post_options',
		)
	)
);

// Post Options - Comment Title.
$wp_customize->add_setting(
	'aster_storefront_blog_post_comment_title',
	array(
		'default'=> 'Leave a Reply',
		'sanitize_callback'	=> 'sanitize_text_field'
	)
);

$wp_customize->add_control(
	'aster_storefront_blog_post_comment_title',
	array(
		'label'	=> __('Comment Title','aster-storefront'),
		'input_attrs' => array(
			'placeholder' => __( 'Leave a Reply', 'aster-storefront' ),
		),
		'section'=> 'aster_storefront_single_post_options',
		'type'=> 'text'
	)
);