<?php

/**
 * Reasons To Buy — Option group definitions.
 *
 * Pure data file. Returns the option groups array
 * consumed by init_option_groups().
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(
	array(
	'title'  => esc_html__( 'Settings', 'merchant' ),
	'module' => Merchant_Reasons_To_Buy::MODULE_ID,
	'fields' => array(
		array(
			'id'           => 'reasons_to_buy',
			'type'         => 'flexible_content',
			'button_label' => esc_html__( 'Add New', 'merchant' ),
			'style'        => Merchant_Reasons_To_Buy::MODULE_ID . '-style default',
			'sorting'      => true,
			'accordion'    => true,
			'duplicate'    => true,
			'layouts'      => array(
				'single-reason' => array(
					'title'       => esc_html__( 'Reasons To Buy', 'merchant' ),
					'title-field' => 'title',
					'status-field' => 'campaign_status',
					'fields'      => array(
						array(
							'id'      => 'campaign_status',
							'type'    => 'select',
							'title'   => esc_html__( 'Status', 'merchant' ),
							'options' => array(
								'active'   => esc_html__( 'Active', 'merchant' ),
								'inactive' => esc_html__( 'Inactive', 'merchant' ),
							),
							'default' => 'active',
							'ai_meta' => array(
								'usage_hint' => 'Controls whether this campaign is active or inactive. Set to active to enable, inactive to disable.',
							),
						),
						array(
							'id'      => 'title',
							'type'    => 'text',
							'title'   => esc_html__( 'Title', 'merchant' ),
							'default' => esc_html__( 'Reasons to buy list', 'merchant' ),
							'desc'    => '',
						),

						array(
							'id'      => 'display_rules',
							'type'    => 'select',
							'title'   => esc_html__( 'Products that will display the list.', 'merchant' ),
							'options' => array(
								'all'        => esc_html__( 'All Products', 'merchant' ),
								'products'   => esc_html__( 'Specific Products', 'merchant' ),
								'categories' => esc_html__( 'Specific Categories', 'merchant' ),
								'tags'       => esc_html__( 'Specific Tags', 'merchant' ),
								'brands'     => esc_html__( 'Specific Brands', 'merchant' ),
							),
							'default' => 'all',
							'ai_meta' => array(
								'semantic_group' => 'product_targeting',
								'usage_hint'     => 'Controls which products show this reasons-to-buy list. Set this BEFORE setting product_ids, category_slugs, tag_slugs, or brand_slugs — those fields are conditional on this value.',
							),
						),

						array(
							'id'            => 'product_ids',
							'type'          => 'products_selector',
							'multiple'      => true,
							'desc'          => esc_html__( 'Select the product(s) on which the list will appear.', 'merchant' ),
							'condition'     => array( 'display_rules', '==', 'products' ),
							'allowed_types' => array( 'simple', 'variable' ),
							'ai_meta'       => array(
								'entity'            => 'product',
								'reference_type'    => 'static',
								'value_format'      => 'product_id',
								'semantic_group'    => 'product_targeting',
								'abstraction_level' => 1,
								'usage_hint'        => 'Use when targeting specific individual products by name or SKU. Requires display_rules=products.',
							),
						),

						array(
							'id'          => 'category_slugs',
							'type'        => 'select_ajax',
							'title'       => esc_html__( 'Categories', 'merchant' ),
							'source'      => 'options',
							'multiple'    => true,
							'options'     => Merchant_Admin_Options::get_category_select2_choices(),
							'placeholder' => esc_html__( 'Select categories', 'merchant' ),
							'desc'        => esc_html__( 'Select the product categories where the list will appear.', 'merchant' ),
							'condition'   => array( 'display_rules', '==', 'categories' ),
							'ai_meta'     => array(
								'entity'            => 'category',
								'reference_type'    => 'dynamic',
								'taxonomy'          => 'product_cat',
								'value_format'      => 'slug',
								'semantic_group'    => 'product_targeting',
								'abstraction_level' => 3,
								'usage_hint'        => 'Use when targeting products by category. Dynamic: future products added to the category are included. Requires display_rules=categories.',
							),
						),

						array(
							'id'          => 'tag_slugs',
							'type'        => 'select_ajax',
							'title'       => esc_html__( 'Tags', 'merchant' ),
							'source'      => 'options',
							'multiple'    => true,
							'options'     => Merchant_Admin_Options::get_tag_select2_choices(),
							'placeholder' => esc_html__( 'Select tags', 'merchant' ),
							'desc'        => esc_html__( 'Select the product tags where the list will appear.', 'merchant' ),
							'condition'   => array( 'display_rules', '==', 'tags' ),
							'ai_meta'     => array(
								'entity'            => 'tag',
								'reference_type'    => 'dynamic',
								'taxonomy'          => 'product_tag',
								'value_format'      => 'slug',
								'semantic_group'    => 'product_targeting',
								'abstraction_level' => 2,
								'usage_hint'        => 'Use when targeting products by tag. Dynamic: future products added to the tag are included. Requires display_rules=tags.',
							),
						),
						array(
							'id'          => 'brand_slugs',
							'type'        => 'select_ajax',
							'title'       => esc_html__( 'Brands', 'merchant' ),
							'source'      => 'options',
							'multiple'    => true,
							'options'     => Merchant_Admin_Options::get_brand_select2_choices(),
							'placeholder' => esc_html__( 'Select brands', 'merchant' ),
							'desc'        => esc_html__( 'Select the product brands where the list will appear.', 'merchant' ),
							'condition'   => array( 'display_rules', '==', 'brands' ),
							'ai_meta'     => array(
								'entity'            => 'brand',
								'reference_type'    => 'dynamic',
								'taxonomy'          => 'product_brand',
								'value_format'      => 'slug',
								'semantic_group'    => 'product_targeting',
								'abstraction_level' => 4,
								'usage_hint'        => 'Use when targeting products by brand. Dynamic: future products added to the brand are included. Requires display_rules=brands.',
							),
						),

						array(
							'id'         => 'exclude_products_toggle',
							'type'       => 'switcher',
							'title'      => esc_html__( 'Exclude products', 'merchant' ),
							'default'    => 0,
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'display_rules',
										'operator' => 'in',
										'value'    => array( 'all', 'categories', 'tags', 'brands' ),
									),
								),
							),
							'ai_meta'    => array(
								'toggle_for'     => 'excluded_products',
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'Enable this toggle before setting excluded_products. Only available when display_rules is all, categories, tags, or brands.',
							),
						),

						array(
							'id'            => 'excluded_products',
							'type'          => 'products_selector',
							'title'         => esc_html__( 'Excluded Products List', 'merchant' ),
							'multiple'      => true,
							'desc'          => esc_html__( 'Exclude products from this list.', 'merchant' ),
							'allowed_types' => array( 'simple', 'variable' ),
							'conditions'    => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'display_rules',
										'operator' => 'in',
										'value'    => array( 'all', 'categories', 'tags', 'brands' ),
									),
									array(
										'field'    => 'exclude_products_toggle',
										'operator' => '===',
										'value'    => true,
									),
								),
							),
							'ai_meta'       => array(
								'entity'            => 'product',
								'reference_type'    => 'static',
								'value_format'      => 'product_id',
								'semantic_group'    => 'product_exclusion',
								'abstraction_level' => 1,
								'toggled_by'        => 'exclude_products_toggle',
								'usage_hint'        => 'Use ONLY for excluding specific individual products. For brand/category/tag exclusions, prefer excluded_brands/excluded_categories/excluded_tags — they are dynamic and auto-exclude future products.',
							),
						),

						array(
							'id'         => 'exclude_categories_toggle',
							'type'       => 'switcher',
							'title'      => esc_html__( 'Exclude categories', 'merchant' ),
							'default'    => 0,
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'display_rules',
										'operator' => 'in',
										'value'    => array( 'all', 'tags', 'brands' ),
									),
								),
							),
							'ai_meta'    => array(
								'toggle_for'     => 'excluded_categories',
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'Enable this toggle before setting excluded_categories. Only available when display_rules is all, tags, or brands.',
							),
						),

						array(
							'id'          => 'excluded_categories',
							'type'        => 'select_ajax',
							'title'       => esc_html__( 'Excluded Categories List', 'merchant' ),
							'source'      => 'options',
							'multiple'    => true,
							'options'     => Merchant_Admin_Options::get_category_select2_choices(),
							'placeholder' => esc_html__( 'Select categories', 'merchant' ),
							'desc'        => esc_html__( 'Exclude categories from this list.', 'merchant' ),
							'conditions'  => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'display_rules',
										'operator' => 'in',
										'value'    => array( 'all', 'tags', 'brands' ),
									),
									array(
										'field'    => 'exclude_categories_toggle',
										'operator' => '===',
										'value'    => true,
									),
								),
							),
							'ai_meta'     => array(
								'entity'            => 'category',
								'reference_type'    => 'dynamic',
								'taxonomy'          => 'product_cat',
								'value_format'      => 'slug',
								'semantic_group'    => 'product_exclusion',
								'abstraction_level' => 3,
								'toggled_by'        => 'exclude_categories_toggle',
								'usage_hint'        => 'Use when excluding products by category. Dynamic: future products added to the category are automatically excluded.',
							),
						),

						array(
							'id'         => 'exclude_tags_toggle',
							'type'       => 'switcher',
							'title'      => esc_html__( 'Exclude product tags', 'merchant' ),
							'default'    => 0,
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'display_rules',
										'operator' => 'in',
										'value'    => array( 'all', 'categories', 'brands' ),
									),
								),
							),
							'ai_meta'    => array(
								'toggle_for'     => 'excluded_tags',
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'Enable this toggle before setting excluded_tags. Only available when display_rules is all, categories, or brands.',
							),
						),

						array(
							'id'          => 'excluded_tags',
							'type'        => 'select_ajax',
							'title'       => esc_html__( 'Excluded Tags List', 'merchant' ),
							'source'      => 'options',
							'multiple'    => true,
							'options'     => Merchant_Admin_Options::get_tag_select2_choices(),
							'placeholder' => esc_html__( 'Select tags', 'merchant' ),
							'desc'        => esc_html__( 'Exclude tags from this list.', 'merchant' ),
							'conditions'  => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'display_rules',
										'operator' => 'in',
										'value'    => array( 'all', 'categories', 'brands' ),
									),
									array(
										'field'    => 'exclude_tags_toggle',
										'operator' => '===',
										'value'    => true,
									),
								),
							),
							'ai_meta'     => array(
								'entity'            => 'tag',
								'reference_type'    => 'dynamic',
								'taxonomy'          => 'product_tag',
								'value_format'      => 'slug',
								'semantic_group'    => 'product_exclusion',
								'abstraction_level' => 2,
								'toggled_by'        => 'exclude_tags_toggle',
								'usage_hint'        => 'Use when excluding products by tag. Dynamic: future products added to the tag are automatically excluded.',
							),
						),

						array(
							'id'         => 'exclude_brands_toggle',
							'type'       => 'switcher',
							'title'      => esc_html__( 'Exclude Brands', 'merchant' ),
							'default'    => 0,
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'display_rules',
										'operator' => 'in',
										'value'    => array( 'all', 'categories', 'tags' ),
									),
								),
							),
							'ai_meta'    => array(
								'toggle_for'     => 'excluded_brands',
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'Enable this toggle before setting excluded_brands. Only available when display_rules is all, categories, or tags.',
							),
						),

						array(
							'id'          => 'excluded_brands',
							'type'        => 'select_ajax',
							'title'       => esc_html__( 'Excluded Brands List', 'merchant' ),
							'source'      => 'options',
							'multiple'    => true,
							'options'     => Merchant_Admin_Options::get_brand_select2_choices(),
							'placeholder' => esc_html__( 'Select brands', 'merchant' ),
							'desc'        => esc_html__( 'Exclude brands from this list.', 'merchant' ),
							'conditions'  => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'display_rules',
										'operator' => 'in',
										'value'    => array( 'all', 'categories', 'tags' ),
									),
									array(
										'field'    => 'exclude_brands_toggle',
										'operator' => '===',
										'value'    => true,
									),
								),
							),
							'ai_meta'     => array(
								'entity'            => 'brand',
								'reference_type'    => 'dynamic',
								'taxonomy'          => 'product_brand',
								'value_format'      => 'slug',
								'semantic_group'    => 'product_exclusion',
								'abstraction_level' => 4,
								'toggled_by'        => 'exclude_brands_toggle',
								'usage_hint'        => 'Use when excluding products by brand. Dynamic: future products added to the brand are automatically excluded.',
							),
						),

						array(
							'id'           => 'items',
							'type'         => 'sortable_repeater_icons',
							'sorting'      => true,
							'title'        => esc_html__( 'List items', 'merchant' ),
							'desc'         => '',
							'button_label' => esc_html__( 'Add new item', 'merchant' ),
							'default'      => array(
								array(
									'text' => esc_html__( '100% Polyester.', 'merchant' ),
									'icon' => '',
								),
							),
						),

						// Placement
						array(
							'id'      => 'placement',
							'type'    => 'select',
							'title'   => esc_html__( 'Placement on product page', 'merchant' ),
							'options' => array(
								'after-short-description' => esc_html__( 'After short description', 'merchant' ),
								'before-cart-form'        => esc_html__( 'Before add to cart form', 'merchant' ),
								'after-cart-form'         => esc_html__( 'After add to cart form', 'merchant' ),
								'bottom-product-summary'  => esc_html__( 'Bottom of product summary', 'merchant' ),
							),
							'default' => 'bottom-product-summary',
						),

						// Display Icon.
						array(
							'id'      => 'display_icon',
							'type'    => 'switcher',
							'title'   => esc_html__( 'Display icon', 'merchant' ),
							'default' => 1,
						),

						// List items Spacing.
						array(
							'id'      => 'spacing',
							'type'    => 'range',
							'title'   => esc_html__( 'List items spacing', 'merchant' ),
							'min'     => 0,
							'max'     => 80,
							'step'    => 1,
							'unit'    => 'px',
							'default' => 5,
						),

						// Title color.
						array(
							'id'      => 'title_color',
							'type'    => 'color',
							'title'   => esc_html__( 'Title color', 'merchant' ),
							'default' => '#212121',
						),

						// List items color.
						array(
							'id'      => 'items_color',
							'type'    => 'color',
							'title'   => esc_html__( 'List items color', 'merchant' ),
							'default' => '#777',
						),

						// List items Icon color.
						array(
							'id'      => 'icon_color',
							'type'    => 'color',
							'title'   => esc_html__( 'List items Icon color', 'merchant' ),
							'default' => '#212121',
						),
					),
				),
			),
			'default'      => array(
				array(
					'layout' => 'single-reason',
				),
			),
		),
	),
),
	array(
	'module' => Merchant_Reasons_To_Buy::MODULE_ID,
	'title'  => esc_html__( 'Use shortcode', 'merchant' ),
	'fields' => array(
		array(
			'id'      => 'use_shortcode',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Use shortcode', 'merchant' ),
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
			'default'   => '[merchant_module_' . str_replace( '-', '_', Merchant_Reasons_To_Buy::MODULE_ID ) . ']',
			'condition' => array( 'use_shortcode', '==', '1' ),
		),
	),
),
);
