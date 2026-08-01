<?php

/**
 * Add To Cart Text — Option group definitions.
 *
 * Pure data file. Returns the option groups array
 * consumed by init_option_groups().
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Settings

return array(
	array(
	'module' => Merchant_Add_To_Cart_Text::MODULE_ID,
	'title'  => esc_html__( 'Simple Product', 'merchant' ),
	'fields' => array(
		array(
			'id'      => 'simple_product_shop_label',
			'type'    => 'text',
			'title'   => esc_html__( 'Label', 'merchant' ),
			'default' => esc_html__( 'Add to cart', 'merchant' ),
			
		),
		array(
			'id'      => 'simple_product_shop_label_info',
			'type'    => 'info',
			/* Translators: 1. Link open tag 2. Link close tag */
			'content' => sprintf( esc_html__( 'If you want to add a different label for each product, go to %1$sProducts%2$s, edit the desired product, set the label inside the metabox.',
				'merchant' ),
				'<a href="' . esc_url( admin_url( 'edit.php?post_type=product' ) ) . '">',
				'</a>' ),
		),

		array(
			'id'      => 'simple_product_custom_single_label',
			'type'    => 'switcher',
			'title'   => __( 'Customize label on single product page', 'merchant' ),
			'default' => 0,
			'ai_meta' => array(
				'toggle_for' => 'simple_product_label',
				'usage_hint' => 'Enable this toggle before setting simple_product_label. When disabled, the shop label is used on single product pages as well.',
			),
		),

		array(
			'id'        => 'simple_product_label',
			'type'      => 'text',
			'title'     => esc_html__( 'Label on single product page', 'merchant' ),
			'default'   => esc_html__( 'Add to cart', 'merchant' ),
			'condition' => array( 'simple_product_custom_single_label', '==', '1' ),
			'ai_meta'   => array(
				'toggled_by' => 'simple_product_custom_single_label',
				'usage_hint' => 'Add-to-cart button label on the single product page for simple products. Only active when simple_product_custom_single_label=1.',
			),
		),
	),
),
	array(
	'module' => Merchant_Add_To_Cart_Text::MODULE_ID,
	'title'  => esc_html__( 'Variable Product', 'merchant' ),
	'fields' => array(
		array(
			'id'      => 'variable_product_shop_label',
			'type'    => 'text',
			'title'   => esc_html__( 'Label', 'merchant' ),
			'default' => esc_html__( 'Select options', 'merchant' ),
			
		),
		array(
			'id'      => 'variable_product_shop_label_info',
			'type'    => 'info',
			/* Translators: 1. Link open tag 2. Link close tag */
			'content' => sprintf( esc_html__( 'If you want to add a different label for each product, go to %1$sProducts%2$s, edit the desired product, set the label inside the metabox.',
				'merchant' ),
				'<a href="' . esc_url( admin_url( 'edit.php?post_type=product' ) ) . '">',
				'</a>' ),
		),

		array(
			'id'      => 'variable_product_custom_single_label',
			'type'    => 'switcher',
			'title'   => __( 'Customize label on single product page', 'merchant' ),
			'default' => 0,
			'ai_meta' => array(
				'toggle_for' => 'variable_product_label',
				'usage_hint' => 'Enable this toggle before setting variable_product_label. When disabled, the variable shop label is used on single product pages as well.',
			),
		),

		array(
			'id'        => 'variable_product_label',
			'type'      => 'text',
			'title'     => esc_html__( 'Label on single product page', 'merchant' ),
			'default'   => esc_html__( 'Add to cart', 'merchant' ),
			'condition' => array( 'variable_product_custom_single_label', '==', '1' ),
			'ai_meta'   => array(
				'toggled_by' => 'variable_product_custom_single_label',
				'usage_hint' => 'Add-to-cart button label on the single product page for variable products. Only active when variable_product_custom_single_label=1.',
			),
		),
	),
),
	array(
	'module' => Merchant_Add_To_Cart_Text::MODULE_ID,
	'title'  => esc_html__( 'Out of Stock Product', 'merchant' ),
	'fields' => array(
		array(
			'id'      => 'out_of_stock_custom_label',
			'type'    => 'switcher',
			'title'   => __( 'Alter the label text when the product is out of stock', 'merchant' ),
			'default' => 0,
			'ai_meta' => array(
				'toggle_for' => 'out_of_stock_shop_label',
				'usage_hint' => 'Enable this toggle before setting out_of_stock_shop_label. When enabled, out-of-stock products show a custom button label instead of the default.',
			),
		),
		array(
			'id'        => 'out_of_stock_shop_label',
			'type'      => 'text',
			'title'     => esc_html__( 'Label', 'merchant' ),
			'default'   => esc_html__( 'Out of stock', 'merchant' ),
			'condition' => array( 'out_of_stock_custom_label', '==', '1' ),
			'ai_meta'   => array(
				'toggled_by' => 'out_of_stock_custom_label',
				'usage_hint' => 'Button label shown on out-of-stock products in the shop. Only active when out_of_stock_custom_label=1.',
			),
		),
	),
),
);
