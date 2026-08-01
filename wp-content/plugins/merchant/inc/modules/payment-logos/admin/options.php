<?php
/**
 * Payment Logos — Option group definitions.
 *
 * Pure data file. Returns the option groups array
 * consumed by init_option_groups().
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Icon Source.
 */

return array(
	array(
	'title'  => esc_html__( 'Payment Logos', 'merchant' ),
	'module' => Merchant_Payment_Logos::MODULE_ID,
	'fields' => array(

		array(
			'id'      => 'icon_source',
			'type'    => 'select',
			'title'   => esc_html__( 'Icon source', 'merchant' ),
			'options' => array(
				'gallery' => esc_html__( 'Media Gallery (upload your own)', 'merchant' ),
				'library' => esc_html__( 'Built-in Icon Library', 'merchant' ),
			),
			'default' => 'gallery',
		),

		array(
			'id'        => 'logos',
			'type'      => 'gallery',
			'label'     => esc_html__( 'Select logos', 'merchant' ),
			'condition' => array( 'icon_source', '==', 'gallery' ),
		),

		array(
			'id'        => 'library_icons',
			'type'      => 'choices',
			'title'     => esc_html__( 'Select payment icons', 'merchant' ),
			'multiple'  => true,
			'options'   => Merchant_Payment_Icons_Library::get_choices_options(),
			'default'   => array( 'visa', 'mastercard', 'paypal' ),
			'condition' => array( 'icon_source', '==', 'library' ),
		),

	),
),
	array(
	'title'  => esc_html__( 'Display Locations', 'merchant' ),
	'module' => Merchant_Payment_Logos::MODULE_ID,
	'fields' => array(

		array(
			'id'      => 'show_on_single_product',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Single Product Page', 'merchant' ),
			'default' => 1,
		),

		array(
			'id'      => 'show_on_cart_page',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Cart Page', 'merchant' ),
			'default' => 0,
		),

		array(
			'id'         => 'cart_position',
			'type'       => 'select',
			'title'      => esc_html__( 'Cart page position', 'merchant' ),
			'options'    => array(
				'before_table'               => esc_html__( 'Before cart table', 'merchant' ),
				'after_table'                => esc_html__( 'After cart table', 'merchant' ),
				'before_proceed_to_checkout' => esc_html__( 'Before "Proceed to checkout" button', 'merchant' ),
				'after_cart_totals'          => esc_html__( 'After cart totals', 'merchant' ),
			),
			'default'    => 'after_table',
			'conditions' => array(
				'terms' => array(
					array(
						'field'    => 'show_on_cart_page',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
		),

		array(
			'id'      => 'show_on_checkout_page',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Checkout Page', 'merchant' ),
			'default' => 0,
		),

		array(
			'id'         => 'checkout_position',
			'type'       => 'select',
			'title'      => esc_html__( 'Checkout page position', 'merchant' ),
			'options'    => array(
				'before_checkout_form'          => esc_html__( 'Before checkout form', 'merchant' ),
				'before_customer_details'       => esc_html__( 'Before customer details', 'merchant' ),
				'after_customer_details'        => esc_html__( 'After customer details', 'merchant' ),
				'before_place_order'            => esc_html__( 'Before "Place order" button', 'merchant' ),
				'after_checkout_form'           => esc_html__( 'After checkout form', 'merchant' ),
			),
			'default'    => 'before_checkout_form',
			'conditions' => array(
				'terms' => array(
					array(
						'field'    => 'show_on_checkout_page',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
		),

		array(
			'id'      => 'show_on_footer',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Footer (site-wide)', 'merchant' ),
			'default' => 0,
		),

		array(
			'id'      => 'show_on_archive_pages',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Archive / Shop Pages', 'merchant' ),
			'default' => 0,
		),

		array(
			'id'         => 'archive_position',
			'type'       => 'select',
			'title'      => esc_html__( 'Archive page position', 'merchant' ),
			'options'    => array(
				'before_loop' => esc_html__( 'Before product loop', 'merchant' ),
				'after_loop'  => esc_html__( 'After product loop', 'merchant' ),
			),
			'default'    => 'after_loop',
			'conditions' => array(
				'terms' => array(
					array(
						'field'    => 'show_on_archive_pages',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
		),

	),
),
	array(
	'title'  => esc_html__( 'Settings', 'merchant' ),
	'module' => Merchant_Payment_Logos::MODULE_ID,
	'fields' => array(

		array(
			'id'      => 'align',
			'type'    => 'select',
			'title'   => esc_html__( 'Align logos', 'merchant' ),
			'options' => array(
				'flex-start' => esc_html__( 'Left', 'merchant' ),
				'center'     => esc_html__( 'Center', 'merchant' ),
				'flex-end'   => esc_html__( 'Right', 'merchant' ),
			),
			'default' => 'flex-start',
		),

		array(
			'id'      => 'title',
			'type'    => 'text',
			'title'   => esc_html__( 'Text above the logos', 'merchant' ),
			'default' => esc_html__( '🔒 Safe & Secure Checkout', 'merchant' ),
		),

		array(
			'id'      => 'font-size',
			'type'    => 'range',
			'title'   => esc_html__( 'Font size', 'merchant' ),
			'min'     => 1,
			'max'     => 250,
			'step'    => 1,
			'default' => 18,
			'unit'    => 'px',
		),

		array(
			'id'      => 'text-color',
			'type'    => 'color',
			'title'   => esc_html__( 'Text color', 'merchant' ),
			'default' => '#212121',
		),

		array(
			'id'      => 'margin-top',
			'type'    => 'range',
			'title'   => esc_html__( 'Margin top', 'merchant' ),
			'min'     => 1,
			'max'     => 250,
			'step'    => 1,
			'default' => 20,
			'unit'    => 'px',
		),

		array(
			'id'      => 'margin-bottom',
			'type'    => 'range',
			'title'   => esc_html__( 'Margin bottom', 'merchant' ),
			'min'     => 1,
			'max'     => 250,
			'step'    => 1,
			'default' => 20,
			'unit'    => 'px',
		),

		array(
			'id'      => 'image-max-width',
			'type'    => 'range',
			'title'   => esc_html__( 'Image max width', 'merchant' ),
			'min'     => 1,
			'max'     => 250,
			'step'    => 1,
			'default' => 80,
			'unit'    => 'px',
		),

		array(
			'id'      => 'image-max-height',
			'type'    => 'range',
			'title'   => esc_html__( 'Image max height', 'merchant' ),
			'min'     => 1,
			'max'     => 250,
			'step'    => 1,
			'default' => 100,
			'unit'    => 'px',
		),

	),
),
	array(
	'title'  => esc_html__( 'Container Styling', 'merchant' ),
	'module' => Merchant_Payment_Logos::MODULE_ID,
	'fields' => array(

		array(
			'id'      => 'container_bg_color',
			'type'    => 'color',
			'title'   => esc_html__( 'Background color', 'merchant' ),
			'default' => 'transparent',
		),

		array(
			'id'      => 'container_padding',
			'type'    => 'range',
			'title'   => esc_html__( 'Padding', 'merchant' ),
			'min'     => 0,
			'max'     => 100,
			'step'    => 1,
			'default' => 0,
			'unit'    => 'px',
		),

		array(
			'id'      => 'container_border_radius',
			'type'    => 'range',
			'title'   => esc_html__( 'Border radius', 'merchant' ),
			'min'     => 0,
			'max'     => 100,
			'step'    => 1,
			'default' => 0,
			'unit'    => 'px',
		),

		array(
			'id'      => 'container_border_color',
			'type'    => 'color',
			'title'   => esc_html__( 'Border color', 'merchant' ),
			'default' => 'transparent',
		),

		array(
			'id'      => 'container_border_width',
			'type'    => 'range',
			'title'   => esc_html__( 'Border width', 'merchant' ),
			'min'     => 0,
			'max'     => 20,
			'step'    => 1,
			'default' => 0,
			'unit'    => 'px',
		),

	),
),
	array(
	'title'  => esc_html__( 'Exclusion Rules', 'merchant' ),
	'module' => Merchant_Payment_Logos::MODULE_ID,
	'fields' => array(

		array(
			'id'      => 'exclude_products_toggle',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Exclude specific products', 'merchant' ),
			'default' => 0,
			'ai_meta' => array(
				'toggle_for'     => 'excluded_products',
				'semantic_group' => 'product_exclusion',
				'usage_hint'     => 'Enable this toggle before setting excluded_products. Hides payment logos on specific individual product pages.',
			),
		),

		array(
			'id'            => 'excluded_products',
			'type'          => 'products_selector',
			'title'         => esc_html__( 'Excluded Products', 'merchant' ),
			'multiple'      => true,
			'desc'          => esc_html__( 'Payment logos will not appear on these products.', 'merchant' ),
			'allowed_types' => array( 'simple', 'variable', 'external', 'grouped' ),
			'conditions'    => array(
				'terms' => array(
					array(
						'field'    => 'exclude_products_toggle',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
			'ai_meta' => array(
				'entity'            => 'product',
				'reference_type'    => 'static',
				'value_format'      => 'product_id',
				'semantic_group'    => 'product_exclusion',
				'abstraction_level' => 1,
				'toggled_by'        => 'exclude_products_toggle',
				'usage_hint'        => 'Use ONLY for excluding specific individual products. For category exclusions, prefer excluded_categories — it is dynamic and auto-excludes future products.',
			),
		),

		array(
			'id'      => 'exclude_categories_toggle',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Exclude specific categories', 'merchant' ),
			'default' => 0,
			'ai_meta' => array(
				'toggle_for'     => 'excluded_categories',
				'semantic_group' => 'product_exclusion',
				'usage_hint'     => 'Enable this toggle before setting excluded_categories. Hides payment logos on all products within the selected categories.',
			),
		),

		array(
			'id'          => 'excluded_categories',
			'type'        => 'select_ajax',
			'title'       => esc_html__( 'Excluded Categories', 'merchant' ),
			'source'      => 'options',
			'multiple'    => true,
			'options'     => Merchant_Admin_Options::get_category_select2_choices(),
			'placeholder' => esc_html__( 'Select categories', 'merchant' ),
			'desc'        => esc_html__( 'Payment logos will not appear on products in these categories.', 'merchant' ),
			'conditions'  => array(
				'terms' => array(
					array(
						'field'    => 'exclude_categories_toggle',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
			'ai_meta' => array(
				'entity'            => 'category',
				'reference_type'    => 'dynamic',
				'taxonomy'          => 'product_cat',
				'value_format'      => 'slug',
				'semantic_group'    => 'product_exclusion',
				'abstraction_level' => 3,
				'toggled_by'        => 'exclude_categories_toggle',
				'usage_hint'        => 'Use when excluding products by category. Dynamic: future products added to the category are automatically excluded. Prefer over excluded_products for category-level intent.',
			),
		),

	),
),
	array(
	'module' => Merchant_Payment_Logos::MODULE_ID,
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
			'content' => esc_html__( 'If you are using a page builder or a theme that supports shortcodes, then you can output the module using the shortcode above. This might be useful if, for example, you find that you want to control the position of the module output more precisely than with the module settings. Note that the shortcodes can only be used on single product pages.', 'merchant' ),
		),
		array(
			'id'        => 'shortcode_text',
			'type'      => 'text_readonly',
			'title'     => esc_html__( 'Shortcode text', 'merchant' ),
			'default'   => '[merchant_module_' . str_replace( '-', '_', Merchant_Payment_Logos::MODULE_ID ) . ']',
			'condition' => array( 'use_shortcode', '==', '1' ),
		),
	),
),
);
