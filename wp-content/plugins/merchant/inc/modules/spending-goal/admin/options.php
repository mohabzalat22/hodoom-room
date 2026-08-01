<?php

/**
 * Spending Goal — Option group definitions.
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
	'title'  => esc_html__( 'Settings', 'merchant' ),
	'module' => Merchant_Spending_Goal::MODULE_ID,
	'fields' => array(

		array(
			'id'      => 'spending_goal',
			'type'    => 'number',
			'title'   => esc_html__( 'Spending goal', 'merchant' ),
			'step'    => 0.01,
			'default' => 150,
			'ai_meta' => array(
				'semantic_group' => 'offer_rules',
				'usage_hint'     => 'The cart total threshold the customer must reach to unlock the spending goal discount.',
			),
		),

		array(
			'id'      => 'total_type',
			'type'    => 'select',
			'title'   => esc_html__( 'Based on', 'merchant' ),
			'desc'    => esc_html__( 'Choose the basis for the spending goal. “Cart Subtotal” excludes additional calculated discounts, whereas “Cart Total” includes them.', 'merchant' ),
			'options' => array(
				'subtotal' => esc_html__( 'Cart subtotal', 'merchant' ),
				'total'    => esc_html__( 'Cart total', 'merchant' ),
			),
			'default' => 'subtotal',
			'ai_meta' => array(
				'semantic_group' => 'offer_rules',
				'usage_hint'     => 'Determines whether the spending_goal threshold is compared against the cart subtotal (pre-discount) or cart total (post-discount).',
			),
		),

		array(
			'id'      => 'discount_type',
			'type'    => 'select',
			'title'   => esc_html__( 'Discount type', 'merchant' ),
			'options' => array(
				'percent' => esc_html__( 'Percent', 'merchant' ),
				'fixed'   => esc_html__( 'Fixed amount', 'merchant' ),
			),
			'default' => 'percent',
			'ai_meta' => array(
				'semantic_group' => 'offer_rules',
				'usage_hint'     => 'Type of discount granted when the spending goal is reached. percent = percentage off cart total; fixed = fixed amount off.',
			),
		),

		array(
			'id'      => 'discount_amount',
			'type'    => 'number',
			'title'   => esc_html__( 'Discount amount', 'merchant' ),
			'step'    => 0.01,
			'default' => 10,
			'ai_meta' => array(
				'semantic_group' => 'offer_rules',
				'usage_hint'     => 'Amount of the discount applied when the spending goal is reached. Interpretation depends on discount_type.',
			),
		),

		array(
			'id'      => 'discount_name',
			'type'    => 'text',
			'title'   => esc_html__( 'Discount name', 'merchant' ),
			'default' => esc_html__( 'Spending goal', 'merchant' ),
			'desc'    => esc_html__( 'This will be the name of the applied discount on the cart page.', 'merchant' ),
		),

		array(
			'id'      => 'inclusion',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Product Inclusion', 'merchant' ),
			'desc'    => esc_html__( 'Include only certain products or categories', 'merchant' ),
			'default' => 0,
			'ai_meta' => array(
				'toggle_for'     => 'included_products',
				'semantic_group' => 'product_targeting',
				'usage_hint'     => 'Enable this to count only specific products or categories toward the spending goal. Reveals included_products and included_categories.',
			),
		),

		array(
			'id'         => 'included_products',
			'type'       => 'products_selector',
			'title'      => esc_html__( 'Include Products', 'merchant' ),
			'multiple'   => true,
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array(
						'field'    => 'inclusion',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
			'ai_meta'    => array(
				'entity'          => 'product',
				'reference_type'  => 'static',
				'value_format'    => 'product_id',
				'semantic_group'  => 'product_targeting',
				'abstraction_level' => 1,
				'toggled_by'      => 'inclusion',
				'usage_hint'      => 'Products counted toward the spending goal. Requires inclusion to be enabled.',
			),
		),

		array(
			'id'          => 'included_categories',
			'type'        => 'select_ajax',
			'title'       => esc_html__( 'Include Categories', 'merchant' ),
			'source'      => 'options',
			'multiple'    => true,
			'options'     => Merchant_Admin_Options::get_category_select2_choices(),
			'placeholder' => esc_html__( 'Select categories', 'merchant' ),
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array(
						'field'    => 'inclusion',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
			'ai_meta'     => array(
				'entity'          => 'category',
				'reference_type'  => 'dynamic',
				'taxonomy'        => 'product_cat',
				'value_format'    => 'slug',
				'semantic_group'  => 'product_targeting',
				'abstraction_level' => 3,
				'toggled_by'      => 'inclusion',
				'usage_hint'      => 'Categories counted toward the spending goal. Requires inclusion to be enabled.',
			),
		),

		array(
			'id'      => 'exclusion',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Product Exclusion', 'merchant' ),
			'desc'    => esc_html__( 'Exclude certain products or categories', 'merchant' ),
			'default' => 0,
			'ai_meta' => array(
				'toggle_for'     => 'excluded_products',
				'semantic_group' => 'product_exclusion',
				'usage_hint'     => 'Enable this to exclude specific products or categories from counting toward the spending goal. Reveals excluded_products and excluded_categories.',
			),
		),

		array(
			'id'         => 'excluded_products',
			'type'       => 'products_selector',
			'title'      => esc_html__( 'Exclude Products', 'merchant' ),
			'multiple'   => true,
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array(
						'field'    => 'exclusion',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
			'ai_meta'    => array(
				'entity'          => 'product',
				'reference_type'  => 'static',
				'value_format'    => 'product_id',
				'semantic_group'  => 'product_exclusion',
				'abstraction_level' => 1,
				'toggled_by'      => 'exclusion',
				'usage_hint'      => 'Products excluded from counting toward the spending goal. Requires exclusion to be enabled.',
			),
		),

		array(
			'id'          => 'excluded_categories',
			'type'        => 'select_ajax',
			'title'       => esc_html__( 'Exclude Categories', 'merchant' ),
			'source'      => 'options',
			'multiple'    => true,
			'options'     => Merchant_Admin_Options::get_category_select2_choices(),
			'placeholder' => esc_html__( 'Select categories', 'merchant' ),
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array(
						'field'    => 'exclusion',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
			'ai_meta'     => array(
				'entity'          => 'category',
				'reference_type'  => 'dynamic',
				'taxonomy'        => 'product_cat',
				'value_format'    => 'slug',
				'semantic_group'  => 'product_exclusion',
				'abstraction_level' => 3,
				'toggled_by'      => 'exclusion',
				'usage_hint'      => 'Categories excluded from counting toward the spending goal. Requires exclusion to be enabled.',
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
				'usage_hint'     => 'Controls which users are eligible for this spending goal discount. Set this BEFORE setting user_condition_users or user_condition_roles.',
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
			'ai_meta'   => array(
				'entity'          => 'role',
				'reference_type'  => 'dynamic',
				'value_format'    => 'role_slug',
				'semantic_group'  => 'user_targeting',
				'abstraction_level' => 2,
				'usage_hint'      => 'Roles eligible for the spending goal discount. Only used when user_condition is roles.',
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
			'ai_meta'   => array(
				'entity'          => 'user',
				'reference_type'  => 'static',
				'value_format'    => 'user_id',
				'semantic_group'  => 'user_targeting',
				'abstraction_level' => 1,
				'usage_hint'      => 'Specific users eligible for the spending goal discount. Only used when user_condition is customers.',
			),
		),

		array(
			'id'         => 'user_exclusion_enabled',
			'type'       => 'switcher',
			'title'      => esc_html__( 'Exclusion List', 'merchant' ),
			'desc'       => esc_html__( 'Choose the users who will not see this offer.', 'merchant' ),
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
			'ai_meta'    => array(
				'toggle_for'     => 'exclude_roles',
				'semantic_group' => 'user_exclusion',
				'usage_hint'     => 'Enable this to reveal exclude_users and exclude_roles fields.',
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
			'ai_meta'    => array(
				'entity'          => 'user',
				'reference_type'  => 'static',
				'value_format'    => 'user_id',
				'semantic_group'  => 'user_exclusion',
				'abstraction_level' => 1,
				'toggled_by'      => 'user_exclusion_enabled',
				'usage_hint'      => 'Users excluded from the spending goal discount. Requires user_exclusion_enabled.',
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
			'ai_meta'    => array(
				'entity'          => 'role',
				'reference_type'  => 'dynamic',
				'value_format'    => 'role_slug',
				'semantic_group'  => 'user_exclusion',
				'abstraction_level' => 2,
				'toggled_by'      => 'user_exclusion_enabled',
				'usage_hint'      => 'Roles excluded from the spending goal discount. Requires user_exclusion_enabled.',
			),
		),

		array(
			'id'      => 'enable_auto_slide_in',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Enable Auto Slide In', 'merchant' ),
			'desc'    => esc_html__( 'This will make the widget slide in each time a product is added to the cart.', 'merchant' ),
			'default' => 1,
		),
	),
),
	array(
	'title'  => esc_html__( 'Text Formatting Settings', 'merchant' ),
	'module' => Merchant_Spending_Goal::MODULE_ID,
	'fields' => array(

		array(
			'id'      => 'text_goal_zero',
			'type'    => 'text',
			'title'   => esc_html__( 'When the goal target is at 0%', 'merchant' ),
			'default' => esc_html__( 'Spend {spending_goal} to get a {discount_amount} discount!', 'merchant' ),
			'desc'    => esc_html__( 'Default is: Spend {spending_goal} to get a {discount_amount} discount!', 'merchant' ),
		),

		array(
			'id'      => 'text_goal_started',
			'type'    => 'text',
			'title'   => esc_html__( 'When the goal target is between 1-99%', 'merchant' ),
			'default' => esc_html__( 'Spend {spending_goal} more to get a {discount_amount} discount!', 'merchant' ),
			'desc'    => esc_html__( 'Default is: Spend {spending_goal} more to get a {discount_amount} discount!', 'merchant' ),
		),

		array(
			'id'      => 'text_goal_reached',
			'type'    => 'text',
			'title'   => esc_html__( 'When the goal target is at 100%', 'merchant' ),
			'default' => esc_html__( 'Congratulations! You got a discount of {discount_amount} on this order!', 'merchant' ),
			'desc'    => esc_html__( 'Default: Congratulations! You got a discount of {discount_amount} on this order!', 'merchant' ),
		),
	),
),
	array(
	'module' => Merchant_Spending_Goal::MODULE_ID,
	'title'  => esc_html__( 'Style', 'merchant' ),
	'fields' => array(

		array(
			'id'      => 'gradient_start',
			'type'    => 'color',
			'title'   => esc_html__( 'Gradient start', 'merchant' ),
			'default' => '#5e5e5e',
		),

		array(
			'id'      => 'gradient_end',
			'type'    => 'color',
			'title'   => esc_html__( 'Gradient end', 'merchant' ),
			'default' => '#212121',
		),

		array(
			'id'      => 'progress_bar',
			'type'    => 'color',
			'title'   => esc_html__( 'Progress bar color', 'merchant' ),
			'default' => '#d83a3b',
		),

		array(
			'id'      => 'content_width',
			'type'    => 'range',
			'title'   => esc_html__( 'Content width', 'merchant' ),
			'min'     => 0,
			'max'     => 600,
			'step'    => 1,
			'unit'    => 'px',
			'default' => 300,
		),

		array(
			'id'      => 'content_bg_color',
			'type'    => 'color',
			'title'   => esc_html__( 'Content background color', 'merchant' ),
			'default' => '#f9f9f9',
		),

		array(
			'id'      => 'content_text_color',
			'type'    => 'color',
			'title'   => esc_html__( 'Content text color', 'merchant' ),
			'default' => '#3c434a',
		),
	),
),
);
