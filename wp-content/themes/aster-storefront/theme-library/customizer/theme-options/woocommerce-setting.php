<?php

/**
 * WooCommerce Settings
 *
 * @package aster_storefront
 */

$wp_customize->add_section(
	'aster_storefront_woocommerce_settings',
	array(
		'panel' => 'aster_storefront_theme_options',
		'title' => esc_html__( 'WooCommerce Settings', 'aster-storefront' ),
	)
);

//WooCommerce - Products per page.
$wp_customize->add_setting( 'aster_storefront_products_per_page', array(
    'default'           => 9,
    'sanitize_callback' => 'absint',
));

$wp_customize->add_control( 'aster_storefront_products_per_page', array(
    'type'        => 'number',
    'section'     => 'aster_storefront_woocommerce_settings',
    'label'       => __( 'Products Per Page', 'aster-storefront' ),
    'input_attrs' => array(
        'min'  => 0,
        'max'  => 50,
        'step' => 1,
    ),
));

//WooCommerce - Products per row.
$wp_customize->add_setting( 'aster_storefront_products_per_row', array(
    'default'           => '3',
    'sanitize_callback' => 'aster_storefront_sanitize_choices',
) );

$wp_customize->add_control( 'aster_storefront_products_per_row', array(
    'label'    => __( 'Products Per Row', 'aster-storefront' ),
    'section'  => 'aster_storefront_woocommerce_settings',
    'settings' => 'aster_storefront_products_per_row',
    'type'     => 'select',
    'choices'  => array(
        '2' => '2',
		'3' => '3',
		'4' => '4',
    ),
) );

//WooCommerce - Show / Hide Related Product.
$wp_customize->add_setting(
	'aster_storefront_related_product_show_hide',
	array(
		'default'           => true,
		'sanitize_callback' => 'aster_storefront_sanitize_switch',
	)
);

$wp_customize->add_control(
	new Aster_Storefront_Toggle_Switch_Custom_Control(
		$wp_customize,
		'aster_storefront_related_product_show_hide',
		array(
			'label'   => esc_html__( 'Show / Hide Related product', 'aster-storefront' ),
			'section' => 'aster_storefront_woocommerce_settings',
		)
	)
);

// WooCommerce - Product Sale Position.
$wp_customize->add_setting(
	'aster_storefront_product_sale_position', 
	array(
		'default' => 'left',
		'sanitize_callback' => 'sanitize_text_field',
));

$wp_customize->add_control(
	'aster_storefront_product_sale_position', 
	array(
		'label' => __('Product Sale Position', 'aster-storefront'),
		'section' => 'aster_storefront_woocommerce_settings',
		'settings' => 'aster_storefront_product_sale_position',
		'type' => 'radio',
		'choices' => 
	array(
		'left' => __('Left', 'aster-storefront'),
		'right' => __('Right', 'aster-storefront'),
	),
));