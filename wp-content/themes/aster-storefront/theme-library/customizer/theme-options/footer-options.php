<?php

/**
 * Footer Options
 *
 * @package aster_storefront
 */

$wp_customize->add_section(
	'aster_storefront_footer_options',
	array(
		'panel' => 'aster_storefront_theme_options',
		'title' => esc_html__( 'Footer Options', 'aster-storefront' ),
	)
);

// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_footer_separators', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_footer_separators', array(
	'label' => __( 'Footer Settings', 'aster-storefront' ),
	'section' => 'aster_storefront_footer_options',
	'settings' => 'aster_storefront_footer_separators',
)));

// Footer Section - Enable Section.
$wp_customize->add_setting(
	'aster_storefront_enable_footer_section',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_footer_section',
		array(
			'label'    => esc_html__( 'Show / Hide Footer', 'aster-storefront' ),
			'section'  => 'aster_storefront_footer_options',
			'settings' => 'aster_storefront_enable_footer_section',
		)
	)
);

// column // 
$wp_customize->add_setting(
	'aster_storefront_footer_widget_column',
	array(
        'default'			=> '4',
		'capability'     	=> 'edit_theme_options',
		'sanitize_callback' => 'aster_storefront_sanitize_select',
		
	)
);	

$wp_customize->add_control(
	'aster_storefront_footer_widget_column',
	array(
	    'label'   		=> __('Select Widget Column','aster-storefront'),
		'description' => __('Note: Default footer widgets are shown. Add your preferred widgets in (Appearance > Widgets > Footer) to see changes.', 'aster-storefront'), 
	    'section' 		=> 'aster_storefront_footer_options',
		'type'			=> 'select',
		'choices'        => 
		array(
			'' => __( 'None', 'aster-storefront' ),
			'1' => __( '1 Column', 'aster-storefront' ),
			'2' => __( '2 Column', 'aster-storefront' ),
			'3' => __( '3 Column', 'aster-storefront' ),
			'4' => __( '4 Column', 'aster-storefront' )
		) 
	) 
);

//  BG Color // 
$wp_customize->add_setting('footer_background_color_setting', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_hex_color',
));

$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_background_color_setting', array(
    'label' => __('Footer Background Color', 'aster-storefront'),
    'section' => 'aster_storefront_footer_options',
)));

// Footer Background Image Setting
$wp_customize->add_setting('footer_background_image_setting', array(
    'default' => '',
    'sanitize_callback' => 'esc_url_raw',
));

$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'footer_background_image_setting', array(
    'label' => __('Footer Background Image', 'aster-storefront'),
    'section' => 'aster_storefront_footer_options',
)));

// Footer Background Attachment
$wp_customize->add_setting(
	'aster_storefront_footer_image_attachment_setting',
	array(
		'default'=> 'scroll',
		'sanitize_callback' => 'aster_storefront_sanitize_choices'
	)
);

$wp_customize->add_control(
	'aster_storefront_footer_image_attachment_setting',
	array(
		'type' => 'select',
		'label' => __('Footer Background Attatchment','aster-storefront'),
		'choices' => array(
			'fixed' => __('fixed','aster-storefront'),
			'scroll' => __('scroll','aster-storefront'),
		),
		'section'=> 'aster_storefront_footer_options',
  	)
);

$wp_customize->add_setting('aster_storefront_footer_text_transform', array(
    'default' => 'none',
    'sanitize_callback' => 'sanitize_text_field',
));

// Add Footer Heading Text Transform Control
$wp_customize->add_control('aster_storefront_footer_text_transform', array(
    'label' => __('Footer Text Heading Transform', 'aster-storefront'),
    'section' => 'aster_storefront_footer_options',
    'settings' => 'aster_storefront_footer_text_transform',
    'type' => 'select',
    'choices' => array(
        'none' => __('None', 'aster-storefront'),
        'capitalize' => __('Capitalize', 'aster-storefront'),
        'uppercase' => __('Uppercase', 'aster-storefront'),
        'lowercase' => __('Lowercase', 'aster-storefront'),
    ),
));

// Footer Heading Alignment
$wp_customize->add_setting(
	'aster_storefront_footer_header_align',
	array(
		'default' 			=> 'left',
		'sanitize_callback' => 'sanitize_text_field'
	)
);

$wp_customize->add_control(
	'aster_storefront_footer_header_align',
	array(
		'label' => __('Footer Heading Alignment ','aster-storefront'),
		'section' => 'aster_storefront_footer_options',
		'type'			=> 'select',
		'choices' => 
		array(
			'left' => __('Left','aster-storefront'),
			'right' => __('Right','aster-storefront'),
			'center' => __('Center','aster-storefront'),
		),
	)
);

// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_copyright_separators', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_copyright_separators', array(
	'label' => __( 'Copyright Settings', 'aster-storefront' ),
	'section' => 'aster_storefront_footer_options',
	'settings' => 'aster_storefront_copyright_separators',
)));

// Copyright Section - Enable Section.
$wp_customize->add_setting(
	'aster_storefront_enable_copyright_section',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_copyright_section',
		array(
			'label'    => esc_html__( 'Show / Hide Copyright', 'aster-storefront' ),
			'section'  => 'aster_storefront_footer_options',
			'settings' => 'aster_storefront_enable_copyright_section',
		)
	)
);

$wp_customize->add_setting(
	'aster_storefront_footer_copyright_text',
	array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'refresh',
	)
);

$wp_customize->add_control(
	'aster_storefront_footer_copyright_text',
	array(
		'label'    => esc_html__( 'Copyright Text', 'aster-storefront' ),
		'section'  => 'aster_storefront_footer_options',
		'settings' => 'aster_storefront_footer_copyright_text',
		'type'     => 'textarea',
	)
);

//Copyright Alignment
$wp_customize->add_setting(
	'aster_storefront_footer_bottom_align',
	array(
		'default' 			=> 'center',
		'sanitize_callback' => 'sanitize_text_field'
	)
);

$wp_customize->add_control(
	'aster_storefront_footer_bottom_align',
	array(
		'label' => __('Copyright Alignment ','aster-storefront'),
		'section' => 'aster_storefront_footer_options',
		'type'			=> 'select',
		'choices' => 
		array(
			'left' => __('Left','aster-storefront'),
			'right' => __('Right','aster-storefront'),
			'center' => __('Center','aster-storefront'),
		),
	)
);

// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_scroll_separators', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_scroll_separators', array(
	'label' => __( 'Scroll Top Settings', 'aster-storefront' ),
	'section' => 'aster_storefront_footer_options',
	'settings' => 'aster_storefront_scroll_separators',
)));

// Footer Options - Scroll Top.
$wp_customize->add_setting(
	'aster_storefront_scroll_top',
	array(
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
		'default'           => true,
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_scroll_top',
		array(
			'label'   => esc_html__( 'Enable Scroll Top Button', 'aster-storefront' ),
			'section' => 'aster_storefront_footer_options',
		)
	)
);

// icon //
$wp_customize->add_setting(
	'aster_storefront_scroll_btn_icon',
	array(
        'default' => 'fas fa-chevron-up',
		'sanitize_callback' => 'sanitize_text_field',
		'capability' => 'edit_theme_options',
		
	)
);	

$wp_customize->add_control(new Aster_Storefront_Change_Icon_Control($wp_customize, 
	'aster_storefront_scroll_btn_icon',
	array(
	    'label'   		=> __('Scroll Top Icon','aster-storefront'),
	    'section' 		=> 'aster_storefront_footer_options',
		'iconset' => 'fa',
	))  
);
$wp_customize->add_setting( 'aster_storefront_scroll_top_position', array(
    'default'           => 'bottom-right',
    'sanitize_callback' => 'aster_storefront_sanitize_scroll_top_position',
) );

// Add control for Scroll Top Button Position
$wp_customize->add_control( 'aster_storefront_scroll_top_position', array(
    'label'    => __( 'Scroll Top Button Position', 'aster-storefront' ),
    'section'  => 'aster_storefront_footer_options',
    'settings' => 'aster_storefront_scroll_top_position',
    'type'     => 'select',
    'choices'  => array(
        'bottom-right' => __( 'Bottom Right', 'aster-storefront' ),
        'bottom-left'  => __( 'Bottom Left', 'aster-storefront' ),
        'bottom-center'=> __( 'Bottom Center', 'aster-storefront' ),
    ),
) );

$wp_customize->add_setting( 'aster_storefront_scroll_top_shape', array(
	'default'           => 'box',
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( 'aster_storefront_scroll_top_shape', array(
	'label'    => __( 'Scroll to Top Button Shape', 'aster-storefront' ),
	'section'  => 'aster_storefront_footer_options',
	'settings' => 'aster_storefront_scroll_top_shape',
	'type'     => 'radio',
	'choices'  => array(
		'box'        => __( 'Box', 'aster-storefront' ),
		'curved-box' => __( 'Curved Box', 'aster-storefront' ),
		'circle'     => __( 'Circle', 'aster-storefront' ),
	),
) );