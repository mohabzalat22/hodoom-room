<?php

/**
 * Banner Section
 *
 * @package aster_storefront
 */

$wp_customize->add_section(
	'aster_storefront_banner_section',
	array(
		'panel'    => 'aster_storefront_front_page_options',
		'title'    => esc_html__( 'Banner Section', 'aster-storefront' ),
		'priority' => 10,
	)
);

// Banner Section - Enable Section.
$wp_customize->add_setting(
	'aster_storefront_enable_banner_section',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_banner_section',
		array(
			'label'    => esc_html__( 'Enable Banner Section', 'aster-storefront' ),
			'section'  => 'aster_storefront_banner_section',
			'settings' => 'aster_storefront_enable_banner_section',
		)
	)
);

if ( isset( $wp_customize->selective_refresh ) ) {
	$wp_customize->selective_refresh->add_partial(
		'aster_storefront_enable_banner_section',
		array(
			'selector' => '#aster_storefront_banner_section .section-link',
			'settings' => 'aster_storefront_enable_banner_section',
		)
	);
}

// Banner Section - Banner Slider Content Type.
$wp_customize->add_setting(
	'aster_storefront_banner_slider_content_type',
	array(
		'default'           => 'post',
		'sanitize_callback' => 'aster_storefront_sanitize_select',
	)
);

$wp_customize->add_control(
	'aster_storefront_banner_slider_content_type',
	array(
		'label'           => esc_html__( 'Select Banner Slider Content Type', 'aster-storefront' ),
		'section'         => 'aster_storefront_banner_section',
		'settings'        => 'aster_storefront_banner_slider_content_type',
		'type'            => 'select',
		'active_callback' => 'aster_storefront_is_banner_slider_section_enabled',
		'choices'         => array(
			'page' => esc_html__( 'Page', 'aster-storefront' ),
			'post' => esc_html__( 'Post', 'aster-storefront' ),
		),
	)
);

for ( $aster_storefront_i = 1; $aster_storefront_i <= 3; $aster_storefront_i++ ) {

	// Banner Section - Select Banner Post.
	$wp_customize->add_setting(
		'aster_storefront_banner_slider_content_post_' . $aster_storefront_i,
		array(
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		'aster_storefront_banner_slider_content_post_' . $aster_storefront_i,
		array(
			/* translators: %d: Select Post Count. */
			'label'           => sprintf( esc_html__( 'Select Post %d', 'aster-storefront' ), $aster_storefront_i ),
			'section'         => 'aster_storefront_banner_section',
			'settings'        => 'aster_storefront_banner_slider_content_post_' . $aster_storefront_i,
			'active_callback' => 'aster_storefront_is_banner_slider_section_and_content_type_post_enabled',
			'type'            => 'select',
			'choices'         => aster_storefront_get_post_choices(),
		)
	);

	// Banner Section - Select Banner Page.
	$wp_customize->add_setting(
		'aster_storefront_banner_slider_content_page_' . $aster_storefront_i,
		array(
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		'aster_storefront_banner_slider_content_page_' . $aster_storefront_i,
		array(
			/* translators: %d: Select Page Count. */
			'label'           => sprintf( esc_html__( 'Select Page %d', 'aster-storefront' ), $aster_storefront_i ),
			'section'         => 'aster_storefront_banner_section',
			'settings'        => 'aster_storefront_banner_slider_content_page_' . $aster_storefront_i,
			'active_callback' => 'aster_storefront_is_banner_slider_section_and_content_type_page_enabled',
			'type'            => 'select',
			'choices'         => aster_storefront_get_page_choices(),
		)
	);

	// Banner Section - Button Label.
	$wp_customize->add_setting(
		'aster_storefront_banner_button_label_' . $aster_storefront_i,
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'aster_storefront_banner_button_label_' . $aster_storefront_i,
		array(
			/* translators: %d: Button Label Count. */
			'label'           => sprintf( esc_html__( 'Button Label %d', 'aster-storefront' ), $aster_storefront_i ),
			'section'         => 'aster_storefront_banner_section',
			'settings'        => 'aster_storefront_banner_button_label_' . $aster_storefront_i,
			'type'            => 'text',
			'active_callback' => 'aster_storefront_is_banner_slider_section_enabled',
		)
	);

	// Banner Section - Button Link.
	$wp_customize->add_setting(
		'aster_storefront_banner_button_link_' . $aster_storefront_i,
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'aster_storefront_banner_button_link_' . $aster_storefront_i,
		array(
			/* translators: %d: Button Link Count. */
			'label'           => sprintf( esc_html__( 'Button Link %d', 'aster-storefront' ), $aster_storefront_i ),
			'section'         => 'aster_storefront_banner_section',
			'settings'        => 'aster_storefront_banner_button_link_' . $aster_storefront_i,
			'type'            => 'url',
			'active_callback' => 'aster_storefront_is_banner_slider_section_enabled',
		)
	);
}

