<?php

/**
 * Header Options
 *
 * @package aster_storefront
 */
// ---------------------------------------- GENERAL OPTIONBS ----------------------------------------------------


// ---------------------------------------- PRELOADER ----------------------------------------------------

$wp_customize->add_section(
	'aster_storefront_general_options',
	array(
		'panel' => 'aster_storefront_theme_options',
		'title' => esc_html__( 'General Options', 'aster-storefront' ),
	)
);

// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_preloader_separator', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_preloader_separator', array(
	'label' => __( 'Enable / Disable Site Preloader Section', 'aster-storefront' ),
	'section' => 'aster_storefront_general_options',
	'settings' => 'aster_storefront_preloader_separator',
) ) );

// General Options - Enable Preloader.
$wp_customize->add_setting(
	'aster_storefront_enable_preloader',
	array(
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
		'default'           => false,
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_preloader',
		array(
			'label'   => esc_html__( 'Enable Preloader', 'aster-storefront' ),
			'section' => 'aster_storefront_general_options',
		)
	)
);

// Preloader Style Setting
$wp_customize->add_setting(
    'aster_storefront_preloader_style',
    array(
        'default'           => 'style1',
        'sanitize_callback' => 'sanitize_text_field',
    )
);

$wp_customize->add_control(
    'aster_storefront_preloader_style',
    array(
        'type'     => 'select',
        'label'    => esc_html__('Select Preloader Styles', 'aster-storefront'),
		'active_callback' => 'aster_storefront_is_preloader_style',
        'section'  => 'aster_storefront_general_options',
        'choices'  => array(
            'style1' => esc_html__('Style 1', 'aster-storefront'),
            'style2' => esc_html__('Style 2', 'aster-storefront'),
            'style3' => esc_html__('Style 3', 'aster-storefront'),
        ),
    )
);

// Preloader Background Color Setting
$wp_customize->add_setting(
	'aster_storefront_preloader_background_color_setting',
	 array(
		'default' => '',
		'sanitize_callback' => 'sanitize_hex_color',
	)
);

$wp_customize->add_control(
	new WP_Customize_Color_Control(
		$wp_customize, 'aster_storefront_preloader_background_color_setting', 
		array(
			'label' => __('Preloader Background Color', 'aster-storefront'),
			'active_callback' => 'aster_storefront_is_preloader_style',
			'section' => 'aster_storefront_general_options',
		)
	)
);

// Preloader Background Image Setting
$wp_customize->add_setting(
	'aster_storefront_preloader_background_image_setting', 
	array(
		'default' => '',
		'sanitize_callback' => 'esc_url_raw',
	)
);

$wp_customize->add_control(
	new WP_Customize_Image_Control(
		$wp_customize, 'aster_storefront_preloader_background_image_setting',
		 array(
			'label' => __('Preloader Background Image', 'aster-storefront'),
			'active_callback' => 'aster_storefront_is_preloader_style',
			'section' => 'aster_storefront_general_options',
		)
	)
);

// ---------------------------------------- PAGINATION ----------------------------------------------------


// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_pagination_separator', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_pagination_separator', array(
	'label' => __( 'Enable / Disable Pagination Section', 'aster-storefront' ),
	'section' => 'aster_storefront_general_options',
	'settings' => 'aster_storefront_pagination_separator',
) ) );

// Pagination - Enable Pagination.
$wp_customize->add_setting(
	'aster_storefront_enable_pagination',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_pagination',
		array(
			'label'    => esc_html__( 'Enable Pagination', 'aster-storefront' ),
			'section'  => 'aster_storefront_general_options',
			'settings' => 'aster_storefront_enable_pagination',
			'type'     => 'checkbox',
		)
	)
);

// Pagination - Pagination Type.
$wp_customize->add_setting(
	'aster_storefront_pagination_type',
	array(
		'default'           => 'default',
		'sanitize_callback' => 'aster_storefront_sanitize_select',
	)
);

$wp_customize->add_control(
	'aster_storefront_pagination_type',
	array(
		'label'           => esc_html__( 'Pagination Type', 'aster-storefront' ),
		'section'         => 'aster_storefront_general_options',
		'settings'        => 'aster_storefront_pagination_type',
		'active_callback' => 'aster_storefront_is_pagination_enabled',
		'type'            => 'select',
		'choices'         => array(
			'default' => __( 'Default (Older/Newer)', 'aster-storefront' ),
			'numeric' => __( 'Numeric', 'aster-storefront' ),
		),
	)
);

// ---------------------------------------- BREADCRUMB ----------------------------------------------------


// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_breadcrumb_separators', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_breadcrumb_separators', array(
	'label' => __( 'Enable / Disable Breadcrumb Section', 'aster-storefront' ),
	'section' => 'aster_storefront_general_options',
	'settings' => 'aster_storefront_breadcrumb_separators',
)));


// Breadcrumb - Enable Breadcrumb.
$wp_customize->add_setting(
	'aster_storefront_enable_breadcrumb',
	array(
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
		'default'           => true,
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_breadcrumb',
		array(
			'label'   => esc_html__( 'Enable Breadcrumb', 'aster-storefront' ),
			'section' => 'aster_storefront_general_options',
		)
	)
);

// Breadcrumb - Separator.
$wp_customize->add_setting(
	'aster_storefront_breadcrumb_separator',
	array(
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '/',
	)
);

$wp_customize->add_control(
	'aster_storefront_breadcrumb_separator',
	array(
		'label'           => esc_html__( 'Separator', 'aster-storefront' ),
		'active_callback' => 'aster_storefront_is_breadcrumb_enabled',
		'section'         => 'aster_storefront_general_options',
	)
);


// ---------------------------------------- Website layout ----------------------------------------------------


// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_layuout_separator', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_layuout_separator', array(
	'label' => __( 'Website Layout Setting', 'aster-storefront' ),
	'section' => 'aster_storefront_general_options',
	'settings' => 'aster_storefront_layuout_separator',
)));


$wp_customize->add_setting(
	'aster_storefront_website_layout',
	array(
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
		'default'           => false,
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_website_layout',
		array(
			'label'   => esc_html__('Boxed Layout', 'aster-storefront'),
			'section' => 'aster_storefront_general_options',
		)
	)
);

$wp_customize->add_setting('aster_storefront_layout_width_margin', array(
	'default'           => 50,
	'sanitize_callback' => 'aster_storefront_sanitize_range_value',
));

$wp_customize->add_control(new Aster_Storefront_Customize_Range_Control($wp_customize, 'aster_storefront_layout_width_margin', array(
		'label'       => __('Set Width', 'aster-storefront'),
		'description' => __('Adjust the width around the website layout by moving the slider. Use this setting to customize the appearance of your site to fit your design preferences.', 'aster-storefront'),
		'section'     => 'aster_storefront_general_options',
		'settings'    => 'aster_storefront_layout_width_margin',
		'active_callback' => 'aster_storefront_is_layout_enabled',
		'input_attrs' => array(
			'min'  => 0,
			'max'  => 130,
			'step' => 1,
		),
)));



// ---------------------------------------- HEADER OPTIONS ----------------------------------------------------	


$wp_customize->add_section(
	'aster_storefront_header_options',
	array(
		'panel' => 'aster_storefront_theme_options',
		'title' => esc_html__( 'Header Options', 'aster-storefront' ),
	)
);


// Add setting for sticky header
$wp_customize->add_setting(
	'aster_storefront_enable_sticky_header',
	array(
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
		'default'           => false,
	)
);

// Add control for sticky header setting
$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_sticky_header',
		array(
			'label'   => esc_html__( 'Enable Sticky Header', 'aster-storefront' ),
			'section' => 'aster_storefront_header_options',
		)
	)
);

// Header Options - Enable Topbar.
$wp_customize->add_setting(
	'aster_storefront_enable_topbar',
	array(
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
		'default'           => true,
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_topbar',
		array(
			'label'   => esc_html__( 'Enable Topbar', 'aster-storefront' ),
			'section' => 'aster_storefront_header_options',
		)
	)
);

// Header Options - Contact Number.
$wp_customize->add_setting(
	'aster_storefront_discount_topbar_text',
	array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

$wp_customize->add_control(
	'aster_storefront_discount_topbar_text',
	array(
		'label'           => esc_html__( 'Topbar Discount Text', 'aster-storefront' ),
		'section'         => 'aster_storefront_header_options',
		'type'            => 'text',
		'active_callback' => 'aster_storefront_is_topbar_enabled',
	)
);

// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_menu_separator', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_menu_separator', array(
	'label' => __( 'Menu Settings', 'aster-storefront' ),
	'section' => 'aster_storefront_header_options',
	'settings' => 'aster_storefront_menu_separator',
)));

$wp_customize->add_setting( 'aster_storefront_menu_font_size', array(
    'default'           => 15,
    'sanitize_callback' => 'absint',
) );

// Add control for site title size
$wp_customize->add_control( 'aster_storefront_menu_font_size', array(
    'type'        => 'number',
    'section'     => 'aster_storefront_header_options',
    'label'       => __( 'Menu Font Size ', 'aster-storefront' ),
    'input_attrs' => array(
        'min'  => 10,
        'max'  => 100,
        'step' => 1,
    ),
));

$wp_customize->add_setting( 'menu_text_transform', array(
    'default'           => 'capitalize', // Default value for text transform
    'sanitize_callback' => 'sanitize_text_field',
) );

// Add control for menu text transform
$wp_customize->add_control( 'menu_text_transform', array(
    'type'     => 'select',
    'section'  => 'aster_storefront_header_options', // Adjust the section as needed
    'label'    => __( 'Menu Text Transform', 'aster-storefront' ),
    'choices'  => array(
        'none'       => __( 'None', 'aster-storefront' ),
        'capitalize' => __( 'Capitalize', 'aster-storefront' ),
        'uppercase'  => __( 'Uppercase', 'aster-storefront' ),
        'lowercase'  => __( 'Lowercase', 'aster-storefront' ),
    ),
) );

// Menu Text Color 
$wp_customize->add_setting(
	'aster_storefront_menu_text_color', 
	array(
		'default' => '',
		'sanitize_callback' => 'sanitize_hex_color',
	)
);

$wp_customize->add_control(
	new WP_Customize_Color_Control(
		$wp_customize, 
		'aster_storefront_menu_text_color', 
		array(
			'label' => __('Menu Color', 'aster-storefront'),
			'section' => 'aster_storefront_header_options',
		)
	)
);

// Sub Menu Text Color 
$wp_customize->add_setting(
	'aster_storefront_sub_menu_text_color', 
	array(
		'default' => '',
		'sanitize_callback' => 'sanitize_hex_color',
	)
);

$wp_customize->add_control(
	new WP_Customize_Color_Control(
		$wp_customize, 
		'aster_storefront_sub_menu_text_color', 
		array(
			'label' => __('Sub Menu Color', 'aster-storefront'),
			'section' => 'aster_storefront_header_options',
		)
	)
);

// ----------------------------------------SITE IDENTITY----------------------------------------------------

$wp_customize->add_setting( 'aster_storefront_site_title_size', array(
    'default'           => 30, 
    'sanitize_callback' => 'absint', 
) );

// Add control for site title size
$wp_customize->add_control( 'aster_storefront_site_title_size', array(
    'type'        => 'number',
    'section'     => 'title_tagline', 
    'label'       => __( 'Site Title Font Size ', 'aster-storefront' ),
    'input_attrs' => array(
        'min'  => 10,
        'max'  => 100,
        'step' => 1,
    ),
) );


// Site Logo - Enable Setting.
$wp_customize->add_setting(
	'aster_storefront_enable_site_logo',
	array(
		'default'           => true, // Default is to display the logo.
		'sanitize_callback' => 'aster_storefront_sanitize_switch', // Sanitize using a custom switch function.
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_site_logo',
		array(
			'label'    => esc_html__( 'Enable Site Logo', 'aster-storefront' ),
			'section'  => 'title_tagline', // Section to add this control.
			'settings' => 'aster_storefront_enable_site_logo',
		)
	)
);

// Site Title - Enable Setting.
$wp_customize->add_setting(
	'aster_storefront_enable_site_title_setting',
	array(
		'default'           => false,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_site_title_setting',
		array(
			'label'    => esc_html__( 'Enable Site Title', 'aster-storefront' ),
			'section'  => 'title_tagline',
			'settings' => 'aster_storefront_enable_site_title_setting',
		)
	)
);

// Tagline - Enable Setting.
$wp_customize->add_setting(
	'aster_storefront_enable_tagline_setting',
	array(
		'default'           => false,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_tagline_setting',
		array(
			'label'    => esc_html__( 'Enable Tagline', 'aster-storefront' ),
			'section'  => 'title_tagline',
			'settings' => 'aster_storefront_enable_tagline_setting',
		)
	)
);

$wp_customize->add_setting('aster_storefront_site_logo_width', array(
    'default'           => 200,
    'sanitize_callback' => 'aster_storefront_sanitize_range_value',
));

$wp_customize->add_control(new Aster_Storefront_Customize_Range_Control($wp_customize, 'aster_storefront_site_logo_width', array(
    'label'       => __('Adjust Site Logo Width', 'aster-storefront'),
    'description' => __('This setting controls the Width of Site Logo', 'aster-storefront'),
    'section'     => 'title_tagline',
    'settings'    => 'aster_storefront_site_logo_width',
    'input_attrs' => array(
        'min'  => 0,
        'max'  => 400,
        'step' => 5,
    ),
)));

// ---------------------------------------- ANIMATION ----------------------------------------------------

$wp_customize->add_setting( 'aster_storefront_animation_separator', array(
	'sanitize_callback' => 'sanitize_text_field',
) );
$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_animation_separator', array(
	'label' => __( 'Enable / Disable Animation', 'aster-storefront' ),
	'section' => 'aster_storefront_general_options',
	'settings' => 'aster_storefront_animation_separator',
) ) );
// Animation Enable / Disable
$wp_customize->add_setting('aster_storefront_enable_animation',array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);
$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control($wp_customize,'aster_storefront_enable_animation',array(
			'label'   => esc_html__( 'Enable Animation', 'aster-storefront' ),
			'section' => 'aster_storefront_general_options',
		)
	)
);
// Topbar Padding Setting
$wp_customize->add_setting('aster_storefront_topbar_padding',array(
		'default' => '',
		'sanitize_callback' => 'absint'
	)
);
$wp_customize->add_control('aster_storefront_topbar_padding',array(
		'label' => __('Topbar Padding','aster-storefront'),
		'section' => 'aster_storefront_header_options',
		'type' => 'number',
		'input_attrs' => array(
			'min' => 0,
			'max' => 50,
			'step' => 1
		)
	)
);