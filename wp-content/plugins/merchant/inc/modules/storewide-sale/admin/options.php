<?php

/**
 * Storewide Sale — Option group definitions.
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
	'title'  => esc_html__( 'Discounts', 'merchant' ),
	'module' => Merchant_Storewide_Sale::MODULE_ID,
	'fields' => array(
		array(
			'id'           => 'offers',
			'type'         => 'flexible_content',
			'button_label' => esc_html__( 'Add New Campaign', 'merchant' ),
			'style'        => Merchant_Storewide_Sale::MODULE_ID . '-style default',
			'sorting'      => true,
			'accordion'    => true,
			'duplicate'    => true,
			'layouts'      => array(
				'offer-details' => array(
					'title'       => esc_html__( 'Storewide Discount Campaign', 'merchant' ),
					'title-field' => 'offer-title', // text field ID to use as title for the layout
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
							'id'      => 'offer-title',
							'type'    => 'text',
							'title'   => esc_html__( 'Offer name', 'merchant' ),
							'default' => esc_html__( 'Storewide Discount Campaign', 'merchant' ),
							'desc'    => esc_html__( 'Internal campaign name. This is not visible to customers.', 'merchant' ),
						),
						array(
							'id'      => 'rules_to_display',
							'type'    => 'select',
							'title'   => esc_html__( 'Trigger', 'merchant' ),
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
								'usage_hint'     => 'Controls which products this campaign applies to. Set this BEFORE setting products_to_display, category_slugs, tag_slugs, or brand_slugs — those fields are conditional on this value.',
							),

						),
						array(
							'id'        => 'products_to_display',
							'type'      => 'products_selector',
							//'title'    => esc_html__( 'Select a product', 'merchant' ),
							'multiple'  => true,
							'desc'      => esc_html__( 'Select the products included in this discount campaign.', 'merchant' ),
							'condition' => array( 'rules_to_display', '==', 'products' ),
							'ai_meta' => array(
								'entity'            => 'product',
								'reference_type'    => 'static',
								'value_format'      => 'product_id',
								'semantic_group'    => 'product_targeting',
								'abstraction_level' => 1,
								'usage_hint'        => 'Use when targeting specific individual products by name or SKU. Requires rules_to_display=products.',
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
							'desc'        => esc_html__( 'Select the product categories that will show the offer.', 'merchant' ),
							'condition'   => array( 'rules_to_display', '==', 'categories' ),
							'ai_meta' => array(
								'entity'            => 'category',
								'reference_type'    => 'dynamic',
								'taxonomy'          => 'product_cat',
								'value_format'      => 'slug',
								'semantic_group'    => 'product_targeting',
								'abstraction_level' => 3,
								'usage_hint'        => 'Use when targeting products by category. Dynamic: future products added to the category are included. Requires rules_to_display=categories.',
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
							'desc'        => esc_html__( 'Select the product tags that will show the offer.', 'merchant' ),
							'condition'   => array( 'rules_to_display', '==', 'tags' ),
							'ai_meta' => array(
								'entity'            => 'tag',
								'reference_type'    => 'dynamic',
								'taxonomy'          => 'product_tag',
								'value_format'      => 'slug',
								'semantic_group'    => 'product_targeting',
								'abstraction_level' => 2,
								'usage_hint'        => 'Use when targeting products by tag. Dynamic: future products added to the tag are included. Requires rules_to_display=tags.',
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
							'desc'        => esc_html__( 'Select the product brands that will show the offer.', 'merchant' ),
							'condition'   => array( 'rules_to_display', '==', 'brands' ),
							'ai_meta' => array(
								'entity'            => 'brand',
								'reference_type'    => 'dynamic',
								'taxonomy'          => 'product_brand',
								'value_format'      => 'slug',
								'semantic_group'    => 'product_targeting',
								'abstraction_level' => 4,
								'usage_hint'        => 'Use when targeting products by brand. Dynamic: future products added to the brand are included. Requires rules_to_display=brands.',
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
										'field'    => 'rules_to_display',
										'operator' => 'in',
										'value'    => array( 'all', 'categories', 'tags', 'brands' ),
									),
								),
							),
							'ai_meta' => array(
								'toggle_for'     => 'excluded_products',
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'Enable this toggle before setting excluded_products. Only available when rules_to_display is all, categories, tags, or brands.',
							),

						),

						array(
							'id'         => 'excluded_products',
							'type'       => 'products_selector',
							'title'      => esc_html__( 'Exclude Products', 'merchant' ),
							'multiple'   => true,
							'desc'       => esc_html__( 'Exclude products from this campaign.', 'merchant' ),
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'rules_to_display',
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
							'ai_meta' => array(
								'entity'            => 'product',
								'reference_type'    => 'static',
								'value_format'      => 'product_id',
								'semantic_group'    => 'product_exclusion',
								'abstraction_level' => 1,
								'toggled_by'        => 'exclude_products_toggle',
								'usage_hint'        => 'Use ONLY for excluding specific individual products. For brand/category/tag exclusions, prefer excluded_brands/excluded_categories/excluded_tags.',
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
										'field'    => 'rules_to_display',
										'operator' => 'in',
										'value'    => array( 'all', 'tags', 'brands' ),
									),
								),
							),
							'ai_meta' => array(
								'toggle_for'     => 'excluded_categories',
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'Enable this toggle before setting excluded_categories. Only available when rules_to_display is all, tags, or brands.',
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
							'desc'        => esc_html__( 'Exclude categories from this campaign.', 'merchant' ),
							'conditions'  => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'rules_to_display',
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
							'ai_meta' => array(
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
										'field'    => 'rules_to_display',
										'operator' => 'in',
										'value'    => array( 'all', 'categories', 'brands' ),
									),
								),
							),
							'ai_meta' => array(
								'toggle_for'     => 'excluded_tags',
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'Enable this toggle before setting excluded_tags. Only available when rules_to_display is all, categories, or brands.',
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
							'desc'        => esc_html__( 'Exclude tags from this campaign.', 'merchant' ),
							'conditions'  => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'rules_to_display',
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
							'ai_meta' => array(
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
							'title'      => esc_html__( 'Exclude brands', 'merchant' ),
							'default'    => 0,
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'rules_to_display',
										'operator' => 'in',
										'value'    => array( 'all', 'categories', 'tags' ),
									),
								),
							),
							'ai_meta' => array(
								'toggle_for'     => 'excluded_brands',
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'Enable this toggle before setting excluded_brands. Only available when rules_to_display is all, categories, or tags.',
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
							'desc'        => esc_html__( 'Exclude brands from this campaign.', 'merchant' ),
							'conditions'  => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'rules_to_display',
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
							'ai_meta' => array(
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
							'id'         => 'exclude_onsale_products_toggle',
							'type'       => 'switcher',
							'title'      => esc_html__( 'Exclude On-Sale products', 'merchant' ),
							'default'    => 0,
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'rules_to_display',
										'operator' => 'in',
										'value'    => array( 'all', 'categories', 'tags', 'brands' ),
									),
								),
							),
							'ai_meta' => array(
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'When enabled, products currently on sale are excluded from this campaign. Standalone flag.',
							),

						),

						array(
							'id'      => 'discount_type',
							'type'    => 'radio',
							'title'   => esc_html__( 'Discount', 'merchant' ),
							'options' => array(
								'percentage_discount' => esc_html__( 'Percentage', 'merchant' ),
								'fixed_discount'      => esc_html__( 'Fixed', 'merchant' ),
							),
							'default' => 'percentage_discount',
							'ai_meta' => array(
								'usage_hint' => 'Choose percentage_discount for relative discounts or fixed_discount for absolute amounts.',
							),

						),
						array(
							'id'      => 'discount_value',
							'type'    => 'number',
							'default' => 10,
							'min'     => 0,
							'step'    => 0.01,
							'ai_meta' => array(
								'semantic_group' => 'offer_rules',
								'usage_hint'     => 'The discount amount. Percentage (e.g. 10 for 10%% off) or fixed currency amount, depending on discount_type.',
							),

						),

						array(
							'id'      => 'discount_target',
							'type'    => 'select',
							'title'   => __( 'Apply discount to', 'merchant' ),
							'options' => array(
								'regular' => __( 'Regular Price', 'merchant' ),
								'sale'    => __( 'Sale Price', 'merchant' ),
							),
							'default' => 'regular',
							'ai_meta' => array(
								'semantic_group' => 'offer_rules',
								'usage_hint'     => 'Whether the discount is calculated from the regular price or the sale price.',
							),

						),

						array(
							'id'      => 'user_condition',
							'type'    => 'select',
							'title'   => esc_html__( 'User Condition', 'merchant' ),
							'options' => array(
								'all'       => esc_html__( 'All Users', 'merchant' ),
								'customers' => esc_html__( 'Selected Users', 'merchant' ),
								'roles'     => esc_html__( 'Selected Roles', 'merchant' ),
							),
							'default' => 'all',
							'ai_meta' => array(
								'semantic_group' => 'user_targeting',
								'usage_hint'     => 'Controls which users see this campaign. Set this BEFORE setting user_condition_roles or user_condition_users.',
							),

						),

						array(
							'id'        => 'user_condition_roles',
							'type'      => 'select_ajax',
							'title'     => esc_html__( 'User Roles', 'merchant' ),
							'desc'      => esc_html__( 'This will limit the offer to users with these roles.', 'merchant' ),
							'source'    => 'options',
							'multiple'  => true,
							'classes'   => array( 'flex-grow' ),
							'options'   => Merchant_Admin_Options::get_user_roles_select2_choices(),
							'condition' => array( 'user_condition', '==', 'roles' ),
							'ai_meta' => array(
								'entity'            => 'role',
								'reference_type'    => 'dynamic',
								'value_format'      => 'role_slug',
								'semantic_group'    => 'user_targeting',
								'abstraction_level' => 2,
								'usage_hint'        => 'Use when targeting users by role. Dynamic: future users assigned the role are included. Requires user_condition=roles.',
							),

						),

						array(
							'id'        => 'user_condition_users',
							'type'      => 'select_ajax',
							'title'     => esc_html__( 'Users', 'merchant' ),
							'desc'      => esc_html__( 'This will limit the offer to the selected customers.', 'merchant' ),
							'source'    => 'user',
							'multiple'  => true,
							'classes'   => array( 'flex-grow' ),
							'condition' => array( 'user_condition', '==', 'customers' ),
							'ai_meta' => array(
								'entity'            => 'user',
								'reference_type'    => 'static',
								'value_format'      => 'user_id',
								'semantic_group'    => 'user_targeting',
								'abstraction_level' => 1,
								'usage_hint'        => 'Use when targeting specific individual users. Static: only selected users, not future users. Requires user_condition=customers.',
							),

						),

						array(
							'id'         => 'user_exclusion_enabled',
							'type'       => 'switcher',
							'title'      => esc_html__( 'Exclusion List', 'merchant' ),
							'desc'       => esc_html__( 'Select the users that will not show the offer.', 'merchant' ),
							'default'    => 0,
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'user_condition',
										'operator' => 'in',
										'value'    => array( 'all', 'roles' ),
									),
								),
							),
							'ai_meta' => array(
								'toggle_for'     => 'exclude_roles',
								'semantic_group' => 'user_exclusion',
								'usage_hint'     => 'Enable this toggle before setting exclude_roles or exclude_users. Gates both user exclusion fields.',
							),

						),

						array(
							'id'         => 'exclude_roles',
							'type'       => 'select_ajax',
							'title'      => esc_html__( 'Exclude Roles', 'merchant' ),
							'desc'       => esc_html__( 'This will exclude the offer for users with these roles.', 'merchant' ),
							'source'     => 'options',
							'multiple'   => true,
							'classes'    => array( 'flex-grow' ),
							'options'    => Merchant_Admin_Options::get_user_roles_select2_choices(),
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'user_condition',
										'operator' => 'in',
										'value'    => array( 'all' ),
									),
									array(
										'field'    => 'user_exclusion_enabled',
										'operator' => '===',
										'value'    => true,
									),
								),
							),
							'ai_meta' => array(
								'entity'            => 'role',
								'reference_type'    => 'dynamic',
								'value_format'      => 'role_slug',
								'semantic_group'    => 'user_exclusion',
								'abstraction_level' => 2,
								'toggled_by'        => 'user_exclusion_enabled',
								'usage_hint'        => 'Use when excluding users by role. Dynamic: future users assigned the role are excluded.',
							),

						),

						array(
							'id'         => 'exclude_users',
							'type'       => 'select_ajax',
							'title'      => esc_html__( 'Exclude Users', 'merchant' ),
							'desc'       => esc_html__( 'This will exclude the offer for the selected customers.', 'merchant' ),
							'source'     => 'user',
							'multiple'   => true,
							'classes'    => array( 'flex-grow' ),
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'user_condition',
										'operator' => 'in',
										'value'    => array( 'all', 'roles' ),
									),
									array(
										'field'    => 'user_exclusion_enabled',
										'operator' => '===',
										'value'    => true,
									),
								),
							),
							'ai_meta' => array(
								'entity'            => 'user',
								'reference_type'    => 'static',
								'value_format'      => 'user_id',
								'semantic_group'    => 'user_exclusion',
								'abstraction_level' => 1,
								'toggled_by'        => 'user_exclusion_enabled',
								'usage_hint'        => 'Use ONLY for excluding specific individual users. For role-level exclusions, prefer exclude_roles.',
							),

						),

						array(
							'id'      => 'availability',
							'type'    => 'radio',
							'title'   => esc_html__( 'Availability', 'merchant' ),
							'options' => array(
								'always'   => esc_html__( 'Always available', 'merchant' ),
								'specific' => esc_html__( 'Specific dates', 'merchant' ),
							),
							'default' => 'always',
							'ai_meta' => array(
								'usage_hint' => 'Controls whether this campaign is always active or only during specific dates. Set to specific before configuring start_date and end_date.',
							),

						),
						array(
							'id'          => 'start_date',
							'type'        => 'date_time',
							'title'       => esc_html__( 'Start at', 'merchant' ),
							'condition'   => array( 'availability', '==', 'specific' ),
							'placeholder' => esc_html__( 'mm/dd/yy, --:-- --', 'merchant' ),
							'ai_meta' => array(
								'allow_empty'  => true,
								'value_format' => 'm-d-Y h:i A',
								'usage_hint'   => 'Optional. Campaign start date. Format: m-d-Y h:i A (e.g., 06-24-2026 03:30 PM). Requires availability=specific.',
							),

						),
						array(
							'id'          => 'end_date',
							'type'        => 'date_time',
							'title'       => esc_html__( 'Ends at', 'merchant' ),
							'condition'   => array( 'availability', '==', 'specific' ),
							'placeholder' => esc_html__( 'mm/dd/yy, --:-- --', 'merchant' ),
							'desc'        => sprintf(
							/*
							 * translators:
							 * %1$s: time zone
							 * %2$s WordPress setting link
							*/
								esc_html__( 'Leave it empty if you don’t want to have an end date. The times set above are in the %1$s timezone, according to your settings from %2$s.',
									'merchant' ),
								'<strong>' . wp_timezone_string() . '</strong>',
								'<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '" target="_blank">' . esc_html__( 'WordPress Settings', 'merchant' ) . '</a>'
							),
							'ai_meta' => array(
								'allow_empty'  => true,
								'value_format' => 'm-d-Y h:i A',
								'usage_hint'   => 'Optional. Campaign end date. Leave empty for no end. Format: m-d-Y h:i A (e.g., 06-24-2026 03:30 PM). Requires availability=specific.',
							),

						),
					),
				),
			),
			'default'      => array(
				array(
					'layout'         => 'offer-details',
					'min_quantity'   => 2,
					'discount'       => 10,
					'discount_type'  => 'percentage_discount',
					'availability'   => 'always',
					'user_condition' => 'all',
				),
			),
		),
		array(
			'id'          => 'helping_instructions',
			'type'        => 'info_block',
			'description' => esc_html__( 'You can display a sale badge on products eligible for this discount by using Merchant’s Product Labels module.', 'merchant' ),
			'button_text' => esc_html__( 'View Product Labels', 'merchant' ),
			'button_link' => esc_url( admin_url( 'admin.php?page=merchant&module=product-labels' ) ),
		),
	),
),
);