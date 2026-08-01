<?php
/**
 * Footer Social Icons
 *
 * @package aster_storefront
 */

$wp_customize->add_section(
	'aster_storefront_footer_icon_options',
	array(
		'panel' => 'aster_storefront_theme_options',
		'title' => esc_html__( 'Footer Social Icons', 'aster-storefront' ),
	)
);

// Add Separator Custom Control
$wp_customize->add_setting( 'aster_storefront_footer_icon_separators', array(
	'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new Aster_Storefront_Separator_Custom_Control( $wp_customize, 'aster_storefront_footer_icon_separators', array(
	'label' => __( 'Footer Icon Settings', 'aster-storefront' ),
	'section' => 'aster_storefront_footer_icon_options',
	'settings' => 'aster_storefront_footer_icon_separators',
)));

// Footer Section - Enable Section.
$wp_customize->add_setting(
	'aster_storefront_enable_footer_icon_section',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_footer_icon_section',
		array(
			'label'    => esc_html__( 'Show / Hide Footer Icon', 'aster-storefront' ),
			'section'  => 'aster_storefront_footer_icon_options',
			'settings' => 'aster_storefront_enable_footer_icon_section',
		)
	)
);

// Add setting for Facebook Link
$wp_customize->add_setting(
	'aster_storefront_footer_facebook_link',
	array(
		'default'           => 'https://www.facebook.com/',
		'sanitize_callback' => 'esc_url_raw',
	)
);

$wp_customize->add_control(
	'aster_storefront_footer_facebook_link',
	array(
		'label'           => esc_html__( 'Facebook Link', 'aster-storefront'  ),
		'section'         => 'aster_storefront_footer_icon_options',
		'settings'        => 'aster_storefront_footer_facebook_link',
		'type'      => 'url'
	)
);

// Add setting for Facebook Icon Changing
$wp_customize->add_setting(
	'aster_storefront_facebook_icon',
	array(
        'default' => 'fab fa-facebook-f',
		'sanitize_callback' => 'sanitize_text_field',
		'capability' => 'edit_theme_options',
		
	)
);	

$wp_customize->add_control(new Aster_Storefront_Change_Icon_Control($wp_customize, 
	'aster_storefront_facebook_icon',
	array(
	    'label'   		=> __('Facebook Icon','aster-storefront'),
	    'section' 		=> 'aster_storefront_footer_icon_options',
		'iconset' => 'fb',
	))  
);


// Add setting for Twitter Link
$wp_customize->add_setting(
	'aster_storefront_footer_twitter_link',
	array(
		'default'           => 'https://x.com/',
		'sanitize_callback' => 'esc_url_raw',
	)
);

$wp_customize->add_control(
	'aster_storefront_footer_twitter_link',
	array(
		'label'           => esc_html__( 'Twitter Link', 'aster-storefront'  ),
		'section'         => 'aster_storefront_footer_icon_options',
		'settings'        => 'aster_storefront_footer_twitter_link',
		'type'      => 'url'
	)
);

// Add setting for Twitter Icon Changing
$wp_customize->add_setting(
	'aster_storefront_twitter_icon',
	array(
        'default' => 'fab fa-twitter',
		'sanitize_callback' => 'sanitize_text_field',
		'capability' => 'edit_theme_options',
		
	)
);	

$wp_customize->add_control(new Aster_Storefront_Change_Icon_Control($wp_customize, 
	'aster_storefront_twitter_icon',
	array(
	    'label'   		=> __('Twitter Icon','aster-storefront'),
	    'section' 		=> 'aster_storefront_footer_icon_options',
		'iconset' => 'fb',
	))  
);

// Add setting for Instagram Link
$wp_customize->add_setting(
	'aster_storefront_footer_instagram_link',
	array(
		'default'           => 'https://www.instagram.com/',
		'sanitize_callback' => 'esc_url_raw',
	)
);

$wp_customize->add_control(
	'aster_storefront_footer_instagram_link',
	array(
		'label'           => esc_html__( 'Instagram Link', 'aster-storefront'  ),
		'section'         => 'aster_storefront_footer_icon_options',
		'settings'        => 'aster_storefront_footer_instagram_link',
		'type'      => 'url'
	)
);

// Add setting for Instagram Icon Changing
$wp_customize->add_setting(
	'aster_storefront_instagram_icon',
	array(
        'default' => 'fab fa-instagram',
		'sanitize_callback' => 'sanitize_text_field',
		'capability' => 'edit_theme_options',
		
	)
);	

$wp_customize->add_control(new Aster_Storefront_Change_Icon_Control($wp_customize, 
	'aster_storefront_instagram_icon',
	array(
	    'label'   		=> __('Instagram Icon','aster-storefront'),
	    'section' 		=> 'aster_storefront_footer_icon_options',
		'iconset' => 'fb',
	))  
);

// Add setting for Linkedin Link
$wp_customize->add_setting(
	'aster_storefront_footer_linkedin_link',
	array(
		'default'           => 'https://in.linkedin.com/',
		'sanitize_callback' => 'esc_url_raw',
	)
);

$wp_customize->add_control(
	'aster_storefront_footer_linkedin_link',
	array(
		'label'           => esc_html__( 'Linkedin Link', 'aster-storefront'  ),
		'section'         => 'aster_storefront_footer_icon_options',
		'settings'        => 'aster_storefront_footer_linkedin_link',
		'type'      => 'url'
	)
);

// Add setting for Linkedin Icon Changing
$wp_customize->add_setting(
	'aster_storefront_linkedin_icon',
	array(
        'default' => 'fab fa-linkedin',
		'sanitize_callback' => 'sanitize_text_field',
		'capability' => 'edit_theme_options',
		
	)
);	

$wp_customize->add_control(new Aster_Storefront_Change_Icon_Control($wp_customize, 
	'aster_storefront_linkedin_icon',
	array(
	    'label'   		=> __('Linkedin Icon','aster-storefront'),
	    'section' 		=> 'aster_storefront_footer_icon_options',
		'iconset' => 'fb',
	))  
);

// Add setting for Youtube Link
$wp_customize->add_setting(
	'aster_storefront_footer_youtube_link',
	array(
		'default'           => 'https://www.youtube.com/',
		'sanitize_callback' => 'esc_url_raw',
	)
);

$wp_customize->add_control(
	'aster_storefront_footer_youtube_link',
	array(
		'label'           => esc_html__( 'Youtube Link', 'aster-storefront'  ),
		'section'         => 'aster_storefront_footer_icon_options',
		'settings'        => 'aster_storefront_footer_youtube_link',
		'type'      => 'url'
	)
);

// Add setting for Youtube Icon Changing
$wp_customize->add_setting(
	'aster_storefront_youtube_icon',
	array(
        'default' => 'fab fa-youtube',
		'sanitize_callback' => 'sanitize_text_field',
		'capability' => 'edit_theme_options',
		
	)
);	

$wp_customize->add_control(new Aster_Storefront_Change_Icon_Control($wp_customize, 
	'aster_storefront_youtube_icon',
	array(
	    'label'   		=> __('Youtube Icon','aster-storefront'),
	    'section' 		=> 'aster_storefront_footer_icon_options',
		'iconset' => 'fb',
	))  
);