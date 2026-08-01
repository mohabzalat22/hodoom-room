<?php

/**
 * Service Section
 *
 * @package aster_storefront
 */

$wp_customize->add_section(
	'aster_storefront_product_section',
	array(
		'panel'    => 'aster_storefront_front_page_options',
		'title'    => esc_html__( 'Trending Product Section', 'aster-storefront' ),
		'priority' => 10,
	)
);

// Service Section - Enable Section.
$wp_customize->add_setting(
	'aster_storefront_enable_service_section',
	array(
		'default'           => false,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_enable_service_section',
		array(
			'label'    => esc_html__( 'Enable Trending Product Section', 'aster-storefront' ),
			'section'  => 'aster_storefront_product_section',
			'settings' => 'aster_storefront_enable_service_section',
		)
	)
);

if ( isset( $wp_customize->selective_refresh ) ) {
	$wp_customize->selective_refresh->add_partial(
		'aster_storefront_enable_service_section',
		array(
			'selector' => '#aster_storefront_service_section .section-link',
			'settings' => 'aster_storefront_enable_service_section',
		)
	);
}

// Service Section - Button Label.
$wp_customize->add_setting(
	'aster_storefront_trending_product_heading',
	array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	)
);

$wp_customize->add_control(
	'aster_storefront_trending_product_heading',
	array(
		'label'           => esc_html__( 'Heading', 'aster-storefront' ),
		'section'         => 'aster_storefront_product_section',
		'settings'        => 'aster_storefront_trending_product_heading',
		'type'            => 'text',
		'active_callback' => 'aster_storefront_is_service_section_enabled',
	)
);

if(class_exists('woocommerce')){

$aster_storefront_args = array(
	'type'                     => 'product',
	'child_of'                 => 0,
	'parent'                   => '',
	'orderby'                  => 'term_group',
	'order'                    => 'ASC',
	'hide_empty'               => false,
	'hierarchical'             => 1,
	'number'                   => '',
	'taxonomy'                 => 'product_cat',
	'pad_counts'               => false
);
$aster_storefront_hide_categories = get_categories($aster_storefront_args);
$aster_storefront_cat_posts = array();
$aster_storefront_m = 0;
$aster_storefront_cat_posts[]='Select';
foreach($aster_storefront_hide_categories as $aster_storefront_category){
	if($aster_storefront_m==0){
		$aster_storefront_default = $aster_storefront_category->slug;
		$aster_storefront_m++;
	}
	$aster_storefront_cat_posts[$aster_storefront_category->slug] = $aster_storefront_category->name;
}

$wp_customize->add_setting('aster_storefront_trending_product_category',array(
	'default'	=> 'uncategorized',
	'sanitize_callback' => 'aster_storefront_sanitize_select',
));
$wp_customize->add_control('aster_storefront_trending_product_category',array(
	'type'    => 'select',
	'choices' => $aster_storefront_cat_posts,
	'label' => __('Select category to display products ','aster-storefront'),
	'section' => 'aster_storefront_product_section',
	'active_callback' => 'aster_storefront_is_service_section_enabled',
));
}