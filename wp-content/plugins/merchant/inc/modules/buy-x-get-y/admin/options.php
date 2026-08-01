<?php

/**
 * Buy X Get Y — Option group definitions.
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
	'module' => Merchant_Buy_X_Get_Y::MODULE_ID,
	'title'  => esc_html__( 'Offers', 'merchant' ),
	'fields' => array(
		array(
			'id'           => 'rules',
			'type'         => 'flexible_content',
			'sorting'      => true,
			'accordion'    => true,
			'duplicate'    => true,
			'style'        => Merchant_Buy_X_Get_Y::MODULE_ID . '-style default',
			'button_label' => esc_html__( 'Add New Offer', 'merchant' ),
			'layouts'      => array(
				'offer-details' => array(
					'title'       => esc_html__( 'Campaign', 'merchant' ),
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
							'default' => esc_html__( 'Campaign', 'merchant' ),
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
							'default' => 'products',
							'ai_meta' => array(
								'semantic_group' => 'product_targeting',
								'usage_hint'     => 'Controls which products this campaign applies to. Set this BEFORE setting product_ids, category_slugs, tag_slugs, or brand_slugs — those fields are conditional on this value.',
							),
						),
						array(
							'id'            => 'product_ids',
							'type'          => 'products_selector',
							'title'         => esc_html__( 'Customer buys', 'merchant' ),
							'multiple'      => true,
							'desc'          => esc_html__( 'Select the products that will show the offer', 'merchant' ),
							'condition'     => array( 'rules_to_display', '==', 'products' ),
							'allowed_types' => array( 'simple', 'variable', 'variation' ),
							'ai_meta' => array(
								'entity'            => 'product',
								'reference_type'    => 'static',
								'value_format'      => 'comma_separated_ids',
								'semantic_group'    => 'product_targeting',
								'abstraction_level' => 1,
								'usage_hint'        => 'Comma-separated WooCommerce product IDs as a string (e.g. "19" or "19,25,31"). Use search-products to find IDs first. Requires rules_to_display=products.',
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
								'value_format'      => 'comma_separated_ids',
								'semantic_group'    => 'product_exclusion',
								'abstraction_level' => 1,
								'toggled_by'        => 'exclude_products_toggle',
								'usage_hint'        => 'Comma-separated WooCommerce product IDs as a string (e.g. "19" or "19,25"). Use ONLY for excluding specific individual products. For brand/category/tag exclusions, prefer excluded_brands/excluded_categories/excluded_tags.',
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
							'title'      => esc_html__( 'Exclude Brands', 'merchant' ),
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
								'usage_hint'        => 'Use when excluding products by brand. Dynamic: future products added to the brand are automatically excluded. Prefer this over excluded_products when intent is brand-level.',
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
								'usage_hint'     => 'When enabled, products currently on sale are excluded from this campaign. Standalone flag — no additional data field required.',
							),
						),
						array(
							'id'      => 'min_quantity',
							'type'    => 'number',
							'min'     => 1,
							'step'    => 1,
							'title'   => esc_html__( 'Quantity', 'merchant' ),
							'desc'    => esc_html__( 'The minimum quantity that customers should purchase to get the offer', 'merchant' ),
							'default' => 1,
							'ai_meta' => array(
								'semantic_group' => 'offer_rules',
								'usage_hint'     => 'The minimum purchase quantity to trigger this offer (the "Buy X" part). Not the gift quantity.',
							),
						),
						array(
							'id'       => 'customer_get_product_ids',
							'type'     => 'products_selector',
							'title'    => esc_html__( 'Customer Gets', 'merchant' ),
							'multiple' => false,
							'desc'     => esc_html__( 'Select the products that the customer will get when they purchase the minimum required quantity.',
								'merchant' ),
							'ai_meta' => array(
								'entity'         => 'product',
								'reference_type' => 'static',
								'value_format'   => 'comma_separated_ids',
								'usage_hint'     => 'Single WooCommerce product ID as a string (e.g. "16"). This is the gift product the customer receives. Use search-products to find the ID.',
							),
						),
						array(
							'id'        => 'external',
							'label'     => __( 'Display the offer on all products in the bundle', 'merchant' ),
							'type'      => 'checkbox',
							'default'   => 0,
							'condition' => array( 'rules_to_display', '==', 'products' ),
							'ai_meta'   => array(
								'usage_hint' => 'When enabled, the offer widget is displayed on all product pages in the bundle, not just the trigger product. Only applies when rules_to_display=products.',
							),
						),
						array(
							'id'      => 'quantity_mode',
							'type'    => 'select',
							'title'   => esc_html__( 'Gift quantity', 'merchant' ),
							'desc'    => esc_html__( 'Choose the quantity of the gift product.', 'merchant' ),
							'options' => array(
								'one_product' => esc_html__( 'The gift is always a single product', 'merchant' ),
								'matches_x'   => esc_html__( 'Gift quantity matches the purchased product quantity', 'merchant' ),
								'custom'      => esc_html__( 'Custom quantity', 'merchant' ),
							),
							'default' => 'custom',
							'ai_meta' => array(
								'semantic_group' => 'offer_rules',
								'usage_hint'     => 'Controls the gift quantity mode. Set to custom and use the quantity field for a specific number. The quantity field is conditional on this being set to custom.',
							),
						),
						array(
							'id'      => 'quantity',
							'type'    => 'number',
							'min'     => 1,
							'step'    => 1,
							'title'   => esc_html__( 'Quantity', 'merchant' ),
							'default' => 3,
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'quantity_mode',
										'operator' => '===',
										'value'    => 'custom',
									),
								),
							),
							'ai_meta' => array(
								'semantic_group' => 'offer_rules',
								'usage_hint'     => 'The gift quantity (the "Get Y" part). Only used when quantity_mode=custom.',
							),
						),
						array(
							'id'      => 'discount_type',
							'type'    => 'radio',
							'title'   => esc_html__( 'Discount Type', 'merchant' ),
							'options' => array(
								'percentage' => esc_html__( 'Percentage Discount', 'merchant' ),
								'fixed'      => esc_html__( 'Fixed Discount', 'merchant' ),
								'shipping'   => esc_html__( 'Free shipping', 'merchant' ),
							),
							'default' => 'percentage',
							'ai_meta' => array(
								'usage_hint' => 'Choose percentage for relative discounts, fixed for absolute amounts, shipping for free shipping.',
							),
						),

						array(
							'id'      => 'discount',
							'type'    => 'number',
							'min'     => 0,
							'step'    => 0.01,
							//'title'   => esc_html__( 'Discount Value', 'merchant' ),
							'default' => 1,
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'discount_type',
										'operator' => 'in',
										'value'    => array( 'percentage', 'fixed' ),
									),
								),
							),
							'ai_meta' => array(
								'semantic_group' => 'offer_rules',
								'usage_hint'     => 'The discount amount applied to the gift product. Percentage (e.g. 50 for 50% off) or fixed currency amount, depending on discount_type. Only visible when discount_type is percentage or fixed.',
							),
						),

						array(
							'id'      => 'discount_target',
							'type'    => 'select',
							'title'   => esc_html__( 'Apply discount to', 'merchant' ),
							'options' => array(
								'regular' => esc_html__( 'Regular Price', 'merchant' ),
								'sale'    => esc_html__( 'Sale Price', 'merchant' ),
							),
							'default' => 'sale',
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'discount_type',
										'operator' => 'in',
										'value'    => array( 'percentage', 'fixed' ),
									),
								),
							),
							'ai_meta' => array(
								'semantic_group' => 'offer_rules',
								'usage_hint'     => 'Whether the discount is calculated from the regular price or the sale price of the gift product.',
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
								'usage_hint'        => 'Use when excluding users by role. Dynamic: future users assigned the role are excluded. Prefer over exclude_users for role-level exclusions.',
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
							'id'             => 'product_single_page',
							'type'           => 'fields_group',
							'title'          => esc_html__( 'Product Single Page', 'merchant' ),
							'sub-desc'       => esc_html__( 'Use these settings to control how bulk discount offers appear on product pages.', 'merchant' ),
							'state'          => 'open',
							'default'        => 'active',
							'accordion'      => true,
							'display_status' => true,
							'ai_meta'        => array(
								'semantic_group' => 'display_settings',
								'usage_hint'     => 'Optional display settings for the product page. When creating campaigns, omit this field entirely to use defaults. All sub-fields (placement, labels, colors, etc.) have sensible defaults and do not need to be provided.',
							),
							'fields'         => array(
								array(
									'id'      => 'single_product_placement',
									'type'    => 'radio',
									'title'   => esc_html__( 'Placement on product page', 'merchant' ),
									'options' => array(
										'after-cart-form'  => esc_html__( 'After add to cart', 'merchant' ),
										'before-cart-form' => esc_html__( 'Before add to cart', 'merchant' ),
									),
									'default' => 'after-cart-form',
								),

								// Text Formatting Settings
								array(
									'id'      => 'title',
									'type'    => 'text',
									'title'   => esc_html__( 'Offer title', 'merchant' ),
									'default' => esc_html__( 'Buy One Get One', 'merchant' ),
								),

								array(
									'id'      => 'buy_label',
									'type'    => 'text',
									'title'   => esc_html__( 'Buy label', 'merchant' ),
									'default' => esc_html__( 'Buy {quantity}', 'merchant' ),
									'desc'        => __( 'You can use these codes in the content.', 'merchant' ),
									'hidden_desc' => sprintf(
									/* Translators: %1$s: bogo offered product quantity */
										__(
											'<strong>%1$s:</strong> to show offered product quantity',
											'merchant'
										),
										'{quantity}'
									),
								),

								array(
									'id'      => 'get_label',
									'type'    => 'text',
									'title'   => esc_html__( 'Get label', 'merchant' ),
									'default' => esc_html__( 'Get {quantity} with {discount} off', 'merchant' ),
									'desc'        => __( 'You can use these codes in the content.', 'merchant' ),
									'hidden_desc' => sprintf(
									/* Translators: %1$s: bogo offered product quantity, %2$s: bogo offer discount */
										__(
											'<strong>%1$s:</strong> to show offered product quantity<br><strong>%2$s:</strong> to show offer discount',
											'merchant'
										),
										'{quantity}',
										'{discount}'
									),
								),

								array(
									'id'      => 'button_text',
									'type'    => 'text',
									'title'   => esc_html__( 'Button text', 'merchant' ),
									'default' => esc_html__( 'Add To Cart', 'merchant' ),
								),

								// Style Settings
								array(
									'id'      => 'title_font_weight',
									'type'    => 'select',
									'title'   => esc_html__( 'Font weight', 'merchant' ),
									'options' => array(
										'lighter' => esc_html__( 'Light', 'merchant' ),
										'normal'  => esc_html__( 'Normal', 'merchant' ),
										'bold'    => esc_html__( 'Bold', 'merchant' ),
									),
									'default' => 'normal',
								),

								array(
									'id'      => 'title_font_size',
									'type'    => 'range',
									'title'   => esc_html__( 'Font size', 'merchant' ),
									'min'     => 0,
									'max'     => 100,
									'step'    => 1,
									'unit'    => 'px',
									'default' => 16,
								),

								array(
									'id'      => 'title_text_color',
									'type'    => 'color',
									'title'   => esc_html__( 'Title text color', 'merchant' ),
									'default' => '#212121',
								),

								array(
									'id'      => 'label_bg_color',
									'type'    => 'color',
									'title'   => esc_html__( 'Label background color', 'merchant' ),
									'default' => '#d61313',
								),

								array(
									'id'      => 'label_text_color',
									'type'    => 'color',
									'title'   => esc_html__( 'Label text color', 'merchant' ),
									'default' => '#fff',
								),

								array(
									'id'      => 'arrow_bg_color',
									'type'    => 'color',
									'title'   => esc_html__( 'Arrow background color', 'merchant' ),
									'default' => '#d61313',
								),

								array(
									'id'      => 'arrow_text_color',
									'type'    => 'color',
									'title'   => esc_html__( 'Arrow text color', 'merchant' ),
									'default' => '#fff',
								),


								array(
									'id'      => 'offer_border_color',
									'type'    => 'color',
									'title'   => esc_html__( 'Offer border color', 'merchant' ),
									'default' => '#cccccc',
								),

								array(
									'id'      => 'offer_border_radius',
									'type'    => 'range',
									'title'   => esc_html__( 'Offer border Radius', 'merchant' ),
									'min'     => 0,
									'max'     => 100,
									'step'    => 1,
									'unit'    => 'px',
									'default' => 5,
								),
							),
						),
						array(
							'id'             => 'cart_page',
							'type'           => 'fields_group',
							'title'          => esc_html__( 'Cart Page', 'merchant' ),
							'sub-desc'       => esc_html__( 'Use these settings to control how bulk discount offers appear on the cart page.', 'merchant' ),
							'state'          => 'closed',
							'default'        => 'inactive',
							'accordion'      => true,
							'display_status' => true,
							'ai_meta'        => array(
								'semantic_group' => 'display_settings',
								'usage_hint'     => 'Optional display settings for the cart page. When creating campaigns, omit this field entirely to use defaults. Defaults to inactive.',
							),
							'fields'         => array(
								// Text Formatting Settings
								array(
									'id'      => 'title',
									'type'    => 'text',
									'title'   => esc_html__( 'Offer title', 'merchant' ),
									'default' => esc_html__( 'You are eligible to get {offer_quantity}x', 'merchant' ),
									'desc'        => __( 'You can use these codes in the content.', 'merchant' ),
									'hidden_desc' => sprintf(
									/* Translators: %1$s: bogo offered product quantity */
										__(
											'<strong>%1$s:</strong> to show offered product quantity',
											'merchant'
										),
										'{offer_quantity}'
									),
								),

								array(
									'id'      => 'discount_text',
									'type'    => 'text',
									'title'   => esc_html__( 'Discount text', 'merchant' ),
									'default' => esc_html__( 'with {discount} off', 'merchant' ),
									'desc'        => __( 'You can use these codes in the content.', 'merchant' ),
									'hidden_desc' => sprintf(
									/* Translators: %1$s: bogo Discount amount */
										__(
											'<strong>%1$s:</strong> to show discount amount',
											'merchant'
										),
										'{discount}'
									),
								),

								array(
									'id'      => 'button_text',
									'type'    => 'text',
									'title'   => esc_html__( 'Button text', 'merchant' ),
									'default' => esc_html__( 'Add To Cart', 'merchant' ),
								),
							),
						),
						array(
							'id'             => 'checkout_page',
							'type'           => 'fields_group',
							'title'          => esc_html__( 'Checkout Page', 'merchant' ),
							'sub-desc'       => esc_html__( 'Use these settings to control how Buy X get Y offers appear on the checkout page.', 'merchant' ),
							'state'          => 'closed',
							'default'        => 'inactive',
							'accordion'      => true,
							'display_status' => true,
							'ai_meta'        => array(
								'semantic_group' => 'display_settings',
								'usage_hint'     => 'Optional display settings for the checkout page. When creating campaigns, omit this field entirely to use defaults. Defaults to inactive.',
							),
							'fields'         => array(
								array(
									'id'      => 'placement',
									'type'    => 'select',
									'title'   => esc_html__( 'Placement', 'merchant' ),
									'options' => array(
										'before_billing_details'     => esc_html__( 'Before Billing Details', 'merchant' ),
										'after_billing_details'      => esc_html__( 'After Billing Details', 'merchant' ),
										'before_order_details'       => esc_html__( 'Before Order Details', 'merchant' ),
										'before_payment_options'     => esc_html__( 'Before Payment Gateways', 'merchant' ),
										'before_order_placement_btn' => esc_html__( 'Before Order Placement Button', 'merchant' ),
										'after_order_placement_btn'  => esc_html__( 'After Order Placement Button', 'merchant' ),
									),
									'default' => 'before_payment_options',
								),
								array(
									'id'      => 'title',
									'type'    => 'text',
									'title'   => esc_html__( 'Offer title', 'merchant' ),
									'default' => esc_html__( 'You are eligible to get {offer_quantity}x', 'merchant' ),
									'desc'        => __( 'You can use these codes in the content.', 'merchant' ),
									'hidden_desc' => sprintf(
									/* Translators: %1$s: bogo offer quantity */
										__(
											'<strong>%1$s:</strong> to show offer quantity',
											'merchant'
										),
										'{offer_quantity}'
									),
								),
								array(
									'id'      => 'discount_text',
									'type'    => 'text',
									'title'   => esc_html__( 'Discount text', 'merchant' ),
									'default' => esc_html__( 'with {discount} off', 'merchant' ),
									'desc'        => __( 'You can use these codes in the content.', 'merchant' ),
									'hidden_desc' => sprintf(
									/* Translators: %1$s: bogo Discount amount */
										__(
											'<strong>%1$s:</strong> to show discount amount',
											'merchant'
										),
										'{discount}'
									),
								),
								array(
									'id'      => 'button_text',
									'type'    => 'text',
									'title'   => esc_html__( 'Button text', 'merchant' ),
									'default' => esc_html__( 'Add To Cart', 'merchant' ),
								),
							),
						),
						array(
							'id'             => 'thank_you_page',
							'type'           => 'fields_group',
							'title'          => esc_html__( 'Thank You Page', 'merchant' ),
							'sub-desc'       => esc_html__( 'Use these settings to control how Buy X get Y offers appear on the thank you page.', 'merchant' ),
							'state'          => 'closed',
							'default'        => 'inactive',
							'accordion'      => true,
							'display_status' => true,
							'ai_meta'        => array(
								'semantic_group' => 'display_settings',
								'usage_hint'     => 'Optional display settings for the thank-you page. When creating campaigns, omit this field entirely to use defaults. Defaults to inactive.',
							),
							'fields'         => array(
								// Text Formatting Settings
								array(
									'id'      => 'title',
									'type'    => 'text',
									'title'   => esc_html__( 'Offer title', 'merchant' ),
									'default' => esc_html__( 'Last chance to get {offer_quantity}x', 'merchant' ),
									'desc'        => __( 'You can use these codes in the content.', 'merchant' ),
									'hidden_desc' => sprintf(
									/* Translators: %1$s: bogo {offer_quantity} tag */
										__(
											'<strong>%1$s:</strong> to show offer quantity',
											'merchant'
										),
										'{offer_quantity}'
									),
								),

								array(
									'id'      => 'placement',
									'type'    => 'select',
									'title'   => esc_html__( 'Placement', 'merchant' ),
									'options' => array(
										'on_top'               => esc_html__( 'On Top', 'merchant' ),
										'before_order_details' => esc_html__( 'Before Order details', 'merchant' ),
										'after_order_details'  => esc_html__( 'After Order details', 'merchant' ),
									),
									'default' => 'before_order_details',
								),

								array(
									'id'      => 'discount_text',
									'type'    => 'text',
									'title'   => esc_html__( 'Discount text', 'merchant' ),
									'default' => esc_html__( 'with {discount} off', 'merchant' ),
									'desc'        => __( 'You can use these codes in the content.', 'merchant' ),
									'hidden_desc' => sprintf(
									/* Translators: %1$s: bogo Discount amount */
										__(
											'<strong>%1$s:</strong> to show discount amount',
											'merchant'
										),
										'{discount}'
									),
								),

								array(
									'id'      => 'button_text',
									'type'    => 'text',
									'title'   => esc_html__( 'Button text', 'merchant' ),
									'default' => esc_html__( 'Add To Cart', 'merchant' ),
								),
							),
						),
					),
				),
			),
			'default'      => array(
				array(
					'layout' => 'offer-details',
					'label'  => esc_html__( 'Buy 1 Get 1', 'merchant' ),
				),
			),
		),
	),
),
	// Shortcode
	array(
		'module' => Merchant_Buy_X_Get_Y::MODULE_ID,
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
			'default'   => '[merchant_module_' . str_replace( '-', '_', Merchant_Buy_X_Get_Y::MODULE_ID ) . ']',
			'condition' => array( 'use_shortcode', '==', '1' ),
		),
	),
),
);