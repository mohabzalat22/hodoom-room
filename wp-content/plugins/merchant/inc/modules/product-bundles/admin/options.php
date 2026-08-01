<?php

/**
 * Product bundles — Option group definitions.
 *
 * Pure data file. Returns the option groups array
 * consumed by init_option_groups().
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Hook functionality before including modules options.
 *
 * @since 1.9.8
 */

return array(
	array(
	'module' => Merchant_Product_Bundles::MODULE_ID,
	'title'  => esc_html__( 'Product page settings', 'merchant' ),
	'fields' => array(

		array(
			'id'      => 'price_range',
			'type'    => 'switcher',
			'title'   => __( 'Display bundle price as range for variable products', 'merchant' ),
			'desc'   => __( 'Display a price range for bundles that include variable products.', 'merchant' ),
			'default' => '1',
			'ai_meta' => array(
				'usage_hint' => 'Only affects bundles that contain variable products. When on, the bundle price shows as a range (e.g. $10–$30). When off, a single computed price is shown.',
			),
		),

		array(
			'id'      => 'bundled_thumb',
			'type'    => 'switcher',
			'title'   => __( 'Display bundled product thumbnails', 'merchant' ),
			'default' => '1',
		),

		array(
			'id'      => 'bundled_description',
			'type'    => 'switcher',
			'title'   => __( 'Display bundled product descriptions', 'merchant' ),
			'default' => 0,
		),

		array(
			'id'      => 'bundled_qty',
			'type'    => 'switcher',
			'title'   => __( 'Display bundled product quantities', 'merchant' ),
			'default' => '1',
		),

		array(
			'id'      => 'bundled_link_single',
			'type'    => 'switcher',
			'title'   => __( 'Make bundled product thumbnails and titles clickable', 'merchant' ),
			'default' => '1',
		),

		array(
			'id'      => 'bundled_price',
			'type'    => 'select',
			'title'   => __( 'Display the prices of bundled products', 'merchant' ),
			'options' => array(
				'price'    => __( 'Price per unit', 'merchant' ),
				'subtotal' => __( 'Subtotal', 'merchant' ),
				'no'       => __( 'Hide', 'merchant' ),
			),
			'default' => 'price',
			'ai_meta' => array(
				'usage_hint' => 'Controls how individual bundled product prices are displayed. Use price for per-unit display, subtotal for quantity-multiplied totals, or no to hide prices. Does not affect the bundle total price.',
			),
		),

		array(
			'id'      => 'bundled_price_from',
			'type'    => 'select',
			'title'   => __( 'Calculate the prices of bundled products based on', 'merchant' ),
			'options' => array(
				'regular_price' => __( 'Regular price', 'merchant' ),
				'sale_price'    => __( 'Sale Price', 'merchant' ),
			),
			'default' => 'sale_price',
			'ai_meta' => array(
				'usage_hint' => 'Determines the price base for bundled product calculations. Use sale_price to reflect current sale discounts, or regular_price to always use the non-discounted price regardless of active sales.',
			),
		),

		array(
			'id'      => 'placement',
			'type'    => 'select',
			'title'   => __( 'Where to display the bundled products', 'merchant' ),
			'options' => array(
				'woocommerce_before_add_to_cart_form' => __( 'Before add to cart section', 'merchant' ),
				'woocommerce_after_add_to_cart_form'  => __( 'After add to cart section', 'merchant' ),
			),
			'default' => 'before_form',
		),
	),
),
	array(
	'module' => Merchant_Product_Bundles::MODULE_ID,
	'title'  => esc_html__( 'Cart settings', 'merchant' ),
	'fields' => array(
		array(
			'id'      => 'hide_bundled_cart',
			'type'    => 'switcher',
			'title'   => __( 'Hide bundled products in cart', 'merchant' ),
			'default' => 0,
			'ai_meta' => array(
				'usage_hint' => 'Hides individual bundled items in the cart page so only the bundle parent is shown. Does NOT affect mini cart — use hide_bundled_mini_cart for that.',
			),
		),

		array(
			'id'      => 'hide_bundled_mini_cart',
			'type'    => 'switcher',
			'title'   => __( 'Hide bundled products in mini cart', 'merchant' ),
			'default' => 0,
			'ai_meta' => array(
				'usage_hint' => 'Hides individual bundled items in the mini cart widget so only the bundle parent is shown. Does NOT affect the main cart page — use hide_bundled_cart for that.',
			),
		),

//      array(
//          'id'      => '_woopq_decimal',
//          'type'    => 'switcher',
//          'title'   => __( 'Allow decimal product quantity', 'merchant' ),
//          'default' => 0,
//      ),

		array(
			'id'      => 'bundled_link',
			'type'    => 'switcher',
			'title'   => __( 'Include links to bundled products on cart page', 'merchant' ),
			'default' => '1',
		),

		array(
			'id'      => 'cart_contents_count',
			'type'    => 'select',
			'title'   => __( 'Cart contents count will include', 'merchant' ),
			'options' => array(
				'bundle' => __( 'The bundle as one product', 'merchant' ),
				'both'   => __( 'Both bundle and bundled products', 'merchant' ),
			),
			'default' => 'bundle',
			'ai_meta' => array(
				'usage_hint' => 'Controls the cart item count shown in the cart icon. Use bundle to count the bundle as 1 item, or both to count the bundle plus each individual bundled product separately.',
			),
		),

		array(
			'id'      => 'hide_bundled',
			'type'    => 'radio',
			'title'   => __( 'Show bundled products', 'merchant' ),
			'options' => array(
				'text' => __( 'List inline', 'merchant' ),
				'list' => __( 'Bulleted list', 'merchant' ),
			),
			'default' => 'text',
		),
	),
),
	array(
	'module' => Merchant_Product_Bundles::MODULE_ID,
	'title'  => esc_html__( 'Order email settings', 'merchant' ),
	'fields' => array(
		array(
			'id'      => 'hide_bundled_order_email',
			'type'    => 'switcher',
			'title'   => __( 'Hide bundled products in order email', 'merchant' ),
			'default' => 0,
		),
	),
),
	array(
	'module' => Merchant_Product_Bundles::MODULE_ID,
	'title'  => esc_html__( 'Use shortcode', 'merchant' ),
	'fields' => array(
		array(
			'id'      => 'use_shortcode',
			'type'    => 'switcher',
			'title'   => __( 'Use shortcode', 'merchant' ),
			'default' => 0,
		),
		array(
			'type'    => 'info',
			'id'      => 'shortcode_info',
			'content' => esc_html__( 'If you are using a page builder or a theme that supports shortcodes, then you can output the module using the shortcode above. This might be useful if, for example, you find that you want to control the position of the module output more precisely than with the module settings. Note that the shortcodes can only be used on single product pages.',
				'merchant' ),
		),
		array(
			'id'        => 'shortcode_text',
			'type'      => 'text_readonly',
			'title'     => esc_html__( 'Shortcode text', 'merchant' ),
			'default'   => '[merchant_module_' . str_replace( '-', '_', Merchant_Product_Bundles::MODULE_ID ) . ']',
			'condition' => array( 'use_shortcode', '==', '1' ),
		),
	),
),
);
