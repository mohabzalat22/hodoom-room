<?php
/**
 * Buy Now — Admin Options.
 *
 * Defines the campaign-based flexible_content repeater and global settings sections.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Campaign icons choices.
$buy_now_icon_choices = Merchant_Buy_Now_Icons::get_available_icons();

if ( ! function_exists( 'merchant_buy_now_get_popup_toggle_field' ) ) {
	/**
	 * Return the popup toggle field based on Pro/module availability.
	 *
	 * - Free only: info notice with upgrade CTA.
	 * - Pro installed, popup inactive: info notice with activation link.
	 * - Pro + popup active: switcher toggle.
	 *
	 * @return array<string, mixed>
	 */
	function merchant_buy_now_get_popup_toggle_field(): array {
		if ( ! defined( 'MERCHANT_PRO_VERSION' ) ) {
			return array(
				'id'      => 'show_cart_popup_info',
				'type'    => 'info',
				'content' => esc_html__( 'Show the Added to Cart Popup instead of redirecting immediately. This feature requires Merchant Pro.', 'merchant' ),
			);
		}

		if ( ! Merchant_Modules::is_module_active( 'added-to-cart-popup' ) ) {
			$activation_url = admin_url( 'admin.php?page=merchant&module=added-to-cart-popup' );

			return array(
				'id'      => 'show_cart_popup_info',
				'type'    => 'info',
				'content' => sprintf(
					/* translators: %s: module activation link */
					esc_html__( 'To show a popup before redirecting, activate the %s module first.', 'merchant' ),
					'<a href="' . esc_url( $activation_url ) . '">' . esc_html__( 'Added to Cart Popup', 'merchant' ) . '</a>'
				),
			);
		}

		return array(
			'id'      => 'show_cart_popup',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Show Added to Cart Popup', 'merchant' ),
			'desc'    => esc_html__( 'Display the Added to Cart Popup before redirecting to checkout.', 'merchant' ),
			'default' => 0,
		);
	}
}

/**
 * Campaigns
 */

return array(
	array(
	'module' => 'buy-now',
	'title'  => esc_html__( 'Campaigns', 'merchant' ),
	'fields' => array(
		array(
			'id'           => 'campaigns',
			'type'         => 'flexible_content',
			'sorting'      => true,
			'accordion'    => true,
			'duplicate'    => true,
			'style'        => 'buy-now-style default',
			'button_label' => esc_html__( 'Add New Campaign', 'merchant' ),
			'layouts'      => array(
				'campaign-details' => array(
					'title'        => esc_html__( 'Campaign', 'merchant' ),
					'title-field'  => 'offer-title',
					'status-field' => 'campaign_status',
					'fields'       => array(

						// ── Campaign Meta ─────────────────────────────────────
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
							'title'   => esc_html__( 'Campaign name', 'merchant' ),
							'default' => esc_html__( 'Campaign', 'merchant' ),
						),

						// ── Product Targeting ─────────────────────────────────
						array(
							'id'      => 'rules_to_display',
							'type'    => 'select',
							'title'   => esc_html__( 'Apply to', 'merchant' ),
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
								'usage_hint'     => 'Controls which products this campaign applies to. Set this BEFORE setting product_ids, category_slugs, tag_slugs, or brand_slugs — those fields are conditional on this value.',
							),
						),
						array(
							'id'            => 'product_ids',
							'type'          => 'products_selector',
							'title'         => esc_html__( 'Products', 'merchant' ),
							'multiple'      => true,
							'desc'          => esc_html__( 'Select products that will show the Buy Now button.', 'merchant' ),
							'condition'     => array( 'rules_to_display', '==', 'products' ),
							'allowed_types' => array( 'simple', 'variable', 'variation' ),
							'ai_meta'       => array(
								'entity'          => 'product',
								'reference_type'  => 'static',
								'value_format'    => 'product_id',
								'semantic_group'  => 'product_targeting',
								'abstraction_level' => 1,
								'usage_hint'      => 'List of product IDs this campaign targets. Only used when rules_to_display is set to products.',
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
							'desc'        => esc_html__( 'Select product categories that will show the Buy Now button.', 'merchant' ),
							'condition'   => array( 'rules_to_display', '==', 'categories' ),
							'ai_meta'     => array(
								'entity'          => 'category',
								'reference_type'  => 'dynamic',
								'taxonomy'        => 'product_cat',
								'value_format'    => 'slug',
								'semantic_group'  => 'product_targeting',
								'abstraction_level' => 3,
								'usage_hint'      => 'Category slugs this campaign targets. Only used when rules_to_display is set to categories.',
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
							'desc'        => esc_html__( 'Select product tags that will show the Buy Now button.', 'merchant' ),
							'condition'   => array( 'rules_to_display', '==', 'tags' ),
							'ai_meta'     => array(
								'entity'          => 'tag',
								'reference_type'  => 'dynamic',
								'taxonomy'        => 'product_tag',
								'value_format'    => 'slug',
								'semantic_group'  => 'product_targeting',
								'abstraction_level' => 2,
								'usage_hint'      => 'Tag slugs this campaign targets. Only used when rules_to_display is set to tags.',
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
							'desc'        => esc_html__( 'Select product brands that will show the Buy Now button.', 'merchant' ),
							'condition'   => array( 'rules_to_display', '==', 'brands' ),
							'ai_meta'     => array(
								'entity'          => 'brand',
								'reference_type'  => 'dynamic',
								'taxonomy'        => 'product_brand',
								'value_format'    => 'slug',
								'semantic_group'  => 'product_targeting',
								'abstraction_level' => 4,
								'usage_hint'      => 'Brand slugs this campaign targets. Only used when rules_to_display is set to brands.',
							),
						),

						// ── Product Exclusions ────────────────────────────────
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
							'ai_meta'    => array(
								'toggle_for'     => 'excluded_products',
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'Enable this to reveal the excluded_products field. Must be true before setting excluded_products.',
							),
						),
						array(
							'id'            => 'excluded_products',
							'type'          => 'products_selector',
							'title'         => esc_html__( 'Excluded Products', 'merchant' ),
							'multiple'      => true,
							'desc'          => esc_html__( 'Select products to exclude from this campaign.', 'merchant' ),
							'allowed_types' => array( 'simple', 'variable', 'variation' ),
							'ai_meta'       => array(
								'entity'          => 'product',
								'reference_type'  => 'static',
								'value_format'    => 'product_id',
								'semantic_group'  => 'product_exclusion',
								'abstraction_level' => 1,
								'toggled_by'      => 'exclude_products_toggle',
								'usage_hint'      => 'Products excluded from this campaign. Requires exclude_products_toggle to be enabled.',
							),
							'conditions'    => array(
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
										'value'    => array( 'all', 'products', 'tags', 'brands' ),
									),
								),
							),
							'ai_meta'    => array(
								'toggle_for'     => 'excluded_categories',
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'Enable this to reveal the excluded_categories field. Must be true before setting excluded_categories.',
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
							'ai_meta'     => array(
								'entity'          => 'category',
								'reference_type'  => 'dynamic',
								'taxonomy'        => 'product_cat',
								'value_format'    => 'slug',
								'semantic_group'  => 'product_exclusion',
								'abstraction_level' => 3,
								'toggled_by'      => 'exclude_categories_toggle',
								'usage_hint'      => 'Categories excluded from this campaign. Requires exclude_categories_toggle to be enabled.',
							),
							'conditions'  => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'rules_to_display',
										'operator' => 'in',
										'value'    => array( 'all', 'products', 'tags', 'brands' ),
									),
									array(
										'field'    => 'exclude_categories_toggle',
										'operator' => '===',
										'value'    => true,
									),
								),
							),
						),
						array(
							'id'         => 'exclude_tags_toggle',
							'type'       => 'switcher',
							'title'      => esc_html__( 'Exclude tags', 'merchant' ),
							'default'    => 0,
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'rules_to_display',
										'operator' => 'in',
										'value'    => array( 'all', 'products', 'categories', 'brands' ),
									),
								),
							),
							'ai_meta'    => array(
								'toggle_for'     => 'excluded_tags',
								'semantic_group' => 'product_exclusion',
								'usage_hint'     => 'Enable this to reveal the excluded_tags field. Must be true before setting excluded_tags.',
							),
						),
						array(
							'id'          => 'excluded_tags',
							'type'        => 'select_ajax',
							'title'       => esc_html__( 'Excluded Tags', 'merchant' ),
							'source'      => 'options',
							'multiple'    => true,
							'options'     => Merchant_Admin_Options::get_tag_select2_choices(),
							'placeholder' => esc_html__( 'Select tags', 'merchant' ),
							'ai_meta'     => array(
								'entity'          => 'tag',
								'reference_type'  => 'dynamic',
								'taxonomy'        => 'product_tag',
								'value_format'    => 'slug',
								'semantic_group'  => 'product_exclusion',
								'abstraction_level' => 2,
								'toggled_by'      => 'exclude_tags_toggle',
								'usage_hint'      => 'Tags excluded from this campaign. Requires exclude_tags_toggle to be enabled.',
							),
							'conditions'  => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'rules_to_display',
										'operator' => 'in',
										'value'    => array( 'all', 'products', 'categories', 'brands' ),
									),
									array(
										'field'    => 'exclude_tags_toggle',
										'operator' => '===',
										'value'    => true,
									),
								),
							),
						),

						// ── User Conditions ───────────────────────────────────
						array(
							'id'      => 'user_condition',
							'type'    => 'select',
							'title'   => esc_html__( 'User Condition', 'merchant' ),
							'desc'    => esc_html__( 'Choose which users can see the Buy Now button for this campaign.', 'merchant' ),
							'options' => array(
								'all'       => esc_html__( 'All Users', 'merchant' ),
								'customers' => esc_html__( 'Selected Users', 'merchant' ),
								'roles'     => esc_html__( 'Selected Roles', 'merchant' ),
							),
							'default' => 'all',
							'ai_meta' => array(
								'semantic_group' => 'user_targeting',
								'usage_hint'     => 'Controls which users see this campaign. Set to all, customers (specific users), or roles. Set this BEFORE setting user_condition_users or user_condition_roles.',
							),
						),
						array(
							'id'        => 'user_condition_roles',
							'type'      => 'select_ajax',
							'title'     => esc_html__( 'User Roles', 'merchant' ),
							'desc'      => esc_html__( 'This will limit the campaign to users with these roles.', 'merchant' ),
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
								'usage_hint'      => 'Roles allowed to see this campaign. Only used when user_condition is set to roles.',
							),
						),
						array(
							'id'        => 'user_condition_users',
							'type'      => 'select_ajax',
							'title'     => esc_html__( 'Users', 'merchant' ),
							'desc'      => esc_html__( 'This will limit the campaign to the selected customers.', 'merchant' ),
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
								'usage_hint'      => 'Specific user IDs allowed to see this campaign. Only used when user_condition is set to customers.',
							),
						),
						array(
							'id'         => 'user_exclusion_enabled',
							'type'       => 'switcher',
							'title'      => esc_html__( 'Exclusion List', 'merchant' ),
							'desc'       => esc_html__( 'Exclude specific users or roles from this campaign.', 'merchant' ),
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
								'usage_hint'     => 'Enable this to reveal exclude_roles and exclude_users fields.',
							),
						),
						array(
							'id'         => 'exclude_roles',
							'type'       => 'select_ajax',
							'title'      => esc_html__( 'Exclude Roles', 'merchant' ),
							'desc'       => esc_html__( 'Users with these roles will not see the Buy Now button.', 'merchant' ),
							'source'     => 'options',
							'multiple'   => true,
							'classes'    => array( 'flex-grow' ),
							'options'    => Merchant_Admin_Options::get_user_roles_select2_choices(),
							'ai_meta'    => array(
								'entity'          => 'role',
								'reference_type'  => 'dynamic',
								'value_format'    => 'role_slug',
								'semantic_group'  => 'user_exclusion',
								'abstraction_level' => 2,
								'toggled_by'      => 'user_exclusion_enabled',
								'usage_hint'      => 'Roles excluded from seeing this campaign. Requires user_exclusion_enabled to be true.',
							),
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
						),
						array(
							'id'         => 'exclude_users',
							'type'       => 'select_ajax',
							'title'      => esc_html__( 'Exclude Users', 'merchant' ),
							'desc'       => esc_html__( 'These users will not see the Buy Now button.', 'merchant' ),
							'source'     => 'user',
							'multiple'   => true,
							'classes'    => array( 'flex-grow' ),
							'ai_meta'    => array(
								'entity'          => 'user',
								'reference_type'  => 'static',
								'value_format'    => 'user_id',
								'semantic_group'  => 'user_exclusion',
								'abstraction_level' => 1,
								'toggled_by'      => 'user_exclusion_enabled',
								'usage_hint'      => 'Specific users excluded from seeing this campaign. Requires user_exclusion_enabled to be true.',
							),
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
						),

						// ── Button Behavior ───────────────────────────────────
						array(
							'id'      => 'cart_action',
							'type'    => 'select',
							'title'   => esc_html__( 'Cart Action', 'merchant' ),
							'desc'    => esc_html__( 'What to do with existing cart items when Buy Now is clicked.', 'merchant' ),
							'options' => array(
								'keep'  => esc_html__( 'Keep existing cart items', 'merchant' ),
								'clear' => esc_html__( 'Clear cart before adding product', 'merchant' ),
							),
							'default' => 'keep',
							'ai_meta' => array(
								'semantic_group' => 'offer_rules',
								'usage_hint'     => 'Determines whether existing cart items are kept or cleared when the Buy Now button is clicked.',
							),
						),
						array(
							'id'      => 'redirect_to',
							'type'    => 'select',
							'title'   => esc_html__( 'Redirect After Purchase', 'merchant' ),
							'options' => array(
								'checkout'   => esc_html__( 'Checkout page', 'merchant' ),
								'custom_url' => esc_html__( 'Custom URL', 'merchant' ),
							),
							'default' => 'checkout',
							'ai_meta' => array(
								'semantic_group' => 'offer_rules',
								'usage_hint'     => 'Where the user is redirected after clicking Buy Now. Set to custom_url to use custom_redirect_url.',
							),
						),
						array(
							'id'        => 'custom_redirect_url',
							'type'      => 'text',
							'title'     => esc_html__( 'Custom Redirect URL', 'merchant' ),
							'desc'      => esc_html__( 'Enter the full URL to redirect to after adding to cart.', 'merchant' ),
							'default'   => '',
							'condition' => array( 'redirect_to', '==', 'custom_url' ),
						),

						// ── Button Design ─────────────────────────────────────
						array(
							'id'      => 'button_text',
							'type'    => 'text',
							'title'   => esc_html__( 'Button text', 'merchant' ),
							'default' => esc_html__( 'Buy Now', 'merchant' ),
						),
						array(
							'id'      => 'button_icon',
							'type'    => 'select',
							'title'   => esc_html__( 'Button icon', 'merchant' ),
							'options' => $buy_now_icon_choices,
							'default' => 'none',
						),
						array(
							'id'         => 'button_icon_position',
							'type'       => 'select',
							'title'      => esc_html__( 'Icon position', 'merchant' ),
							'options'    => array(
								'before' => esc_html__( 'Before text', 'merchant' ),
								'after'  => esc_html__( 'After text', 'merchant' ),
							),
							'default'    => 'before',
							'conditions' => array(
								'relation' => 'AND',
								'terms'    => array(
									array(
										'field'    => 'button_icon',
										'operator' => 'in',
										'value'    => array( 'cart', 'lightning', 'arrow', 'custom' ),
									),
								),
							),
						),
						array(
							'id'        => 'button_icon_svg',
							'type'      => 'textarea',
							'title'     => esc_html__( 'Custom SVG markup', 'merchant' ),
							'desc'      => esc_html__( 'Paste your custom SVG icon markup here.', 'merchant' ),
							'default'   => '',
							'condition' => array( 'button_icon', '==', 'custom' ),
							'sanitize'  => 'merchant_sanitize_svg',
						),

						// ── Added to Cart Popup Integration ──────────────────
						merchant_buy_now_get_popup_toggle_field(),

					),
				),
			),
		),
	),
),
	array(
	'title'  => esc_html__( 'Button Style', 'merchant' ),
	'module' => 'buy-now',
	'fields' => array(

		// Customize The Button or Inherit from Themes.
		array(
			'id'      => 'customize-button',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Customize Button', 'merchant' ),
			'default' => 1,
		),

		array(
			'id'        => 'text-color',
			'type'      => 'color',
			'title'     => esc_html__( 'Button text color', 'merchant' ),
			'default'   => '#ffffff',
			'condition' => array( 'customize-button', '==', '1' ),
		),

		array(
			'id'        => 'text-hover-color',
			'type'      => 'color',
			'title'     => esc_html__( 'Button text color hover', 'merchant' ),
			'default'   => '#ffffff',
			'condition' => array( 'customize-button', '==', '1' ),
		),

		array(
			'id'        => 'border-color',
			'type'      => 'color',
			'title'     => esc_html__( 'Button border color', 'merchant' ),
			'default'   => '#212121',
			'condition' => array( 'customize-button', '==', '1' ),
		),

		array(
			'id'        => 'border-hover-color',
			'type'      => 'color',
			'title'     => esc_html__( 'Button border color hover', 'merchant' ),
			'default'   => '#414141',
			'condition' => array( 'customize-button', '==', '1' ),
		),

		array(
			'id'        => 'background-color',
			'type'      => 'color',
			'title'     => esc_html__( 'Button background color', 'merchant' ),
			'default'   => '#212121',
			'condition' => array( 'customize-button', '==', '1' ),
		),

		array(
			'id'        => 'background-hover-color',
			'type'      => 'color',
			'title'     => esc_html__( 'Button background color hover', 'merchant' ),
			'default'   => '#414141',
			'condition' => array( 'customize-button', '==', '1' ),
		),

		array(
			'id'        => 'button-size',
			'type'      => 'select',
			'title'     => esc_html__( 'Button size', 'merchant' ),
			'options'   => array(
				'small'  => esc_html__( 'Small', 'merchant' ),
				'medium' => esc_html__( 'Medium', 'merchant' ),
				'large'  => esc_html__( 'Large', 'merchant' ),
				'custom' => esc_html__( 'Custom', 'merchant' ),
			),
			'default'   => 'medium',
			'condition' => array( 'customize-button', '==', '1' ),
		),

		array(
			'id'        => 'font-size',
			'type'      => 'range',
			'title'     => esc_html__( 'Font size', 'merchant' ),
			'min'       => 1,
			'max'       => 100,
			'step'      => 1,
			'default'   => 16,
			'unit'      => 'px',
			'condition' => array( 'customize-button', '==', '1' ),
		),

		array(
			'id'         => 'margin-top',
			'type'       => 'range',
			'title'      => esc_html__( 'Margin top', 'merchant' ),
			'min'        => 0,
			'max'        => 100,
			'step'       => 1,
			'default'    => 10,
			'unit'       => 'px',
			'conditions' => array(
				'terms' => array(
					array(
						'field'    => 'customize-button',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
		),

		array(
			'id'         => 'margin-bottom',
			'type'       => 'range',
			'title'      => esc_html__( 'Margin bottom', 'merchant' ),
			'min'        => 0,
			'max'        => 100,
			'step'       => 1,
			'default'    => 10,
			'unit'       => 'px',
			'conditions' => array(
				'terms' => array(
					array(
						'field'    => 'customize-button',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
		),

		array(
			'id'         => 'padding_top_bottom',
			'type'       => 'range',
			'title'      => esc_html__( 'Padding Top/Bottom', 'merchant' ),
			'min'        => 0,
			'max'        => 100,
			'step'       => 1,
			'default'    => 12,
			'unit'       => 'px',
			'conditions' => array(
				'terms' => array(
					array(
						'field'    => 'customize-button',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
		),

		array(
			'id'         => 'padding_left_right',
			'type'       => 'range',
			'title'      => esc_html__( 'Padding Left/Right', 'merchant' ),
			'min'        => 0,
			'max'        => 100,
			'step'       => 1,
			'default'    => 24,
			'unit'       => 'px',
			'conditions' => array(
				'terms' => array(
					array(
						'field'    => 'customize-button',
						'operator' => '===',
						'value'    => true,
					),
				),
			),
		),

		array(
			'id'        => 'border-radius',
			'type'      => 'range',
			'title'     => esc_html__( 'Border radius', 'merchant' ),
			'min'       => 0,
			'max'       => 9999,
			'step'      => 1,
			'unit'      => 'px',
			'default'   => 0,
			'condition' => array( 'customize-button', '==', '1' ),
		),

	),
),
	array(
	'module' => 'buy-now',
	'title'  => esc_html__( 'Display Settings', 'merchant' ),
	'fields' => array(

		array(
			'id'      => 'display-archive',
			'type'    => 'checkbox',
			'label'   => __( 'Show on product archive', 'merchant' ),
			'default' => 1,
		),
		array(
			'id'      => 'display-product',
			'type'    => 'checkbox',
			'label'   => __( 'Show on single product page', 'merchant' ),
			'default' => 1,
		),
		array(
			'id'      => 'display-upsell-related',
			'type'    => 'checkbox',
			'label'   => __( 'Show on upsell and related products', 'merchant' ),
			'default' => 1,
		),

		// Loading position/priority on shop archive.
		array(
			'id'      => 'hook-order-shop-archive',
			'type'    => 'hook_select',
			'title'   => __( 'Loading position and priority on shop archive', 'merchant' ),
			'options' => array(
				'woocommerce_before_shop_loop_item'       => __( 'Before shop loop item', 'merchant' ),
				'woocommerce_before_shop_loop_item_title' => __( 'Before shop loop item title', 'merchant' ),
				'woocommerce_shop_loop_item_title'        => __( 'Shop loop item title', 'merchant' ),
				'woocommerce_after_shop_loop_item_title'  => __( 'After shop loop item title', 'merchant' ),
				'woocommerce_after_shop_loop_item'        => __( 'After shop loop item', 'merchant' ),
			),
			'min'     => -999,
			'max'     => 999,
			'step'    => 1,
			'unit'    => '',
			'order'   => true,
			'default' => array(
				'hook_name'     => 'woocommerce_after_shop_loop_item',
				'hook_priority' => 10,
			),
		),
		array(
			'id'      => 'hook-order-shop-archive_info',
			'type'    => 'info',
			'content' => esc_html__( 'This is a developer level feature. The buy now button module is "hooked" into a specific location on the shop archive pages. Themes and other plugins might also add additional elements to the same location. By modifying the loading postiion and priority, you have the ability to customize the placement of this element on that particular location. A lower number = a higher priority, so the module will appear higher on the page.', 'merchant' ),
		),

		// Loading position/priority on single product.
		array(
			'id'      => 'hook-order-single-product',
			'type'    => 'hook_select',
			'title'   => __( 'Loading position and priority on single product', 'merchant' ),
			'options' => array(
				'woocommerce_before_add_to_cart_button'   => __( 'Before add to cart button', 'merchant' ),
				'woocommerce_after_add_to_cart_button'    => __( 'After add to cart button', 'merchant' ),
				'woocommerce_before_add_to_cart_quantity' => __( 'Before add to cart quantity', 'merchant' ),
				'woocommerce_after_add_to_cart_quantity'  => __( 'After add to cart quantity', 'merchant' ),
			),
			'min'     => -999,
			'max'     => 999,
			'step'    => 1,
			'unit'    => '',
			'order'   => true,
			'default' => array(
				'hook_name'     => 'woocommerce_after_add_to_cart_button',
				'hook_priority' => 10,
			),
		),
		array(
			'id'      => 'hook-order-single-product_info',
			'type'    => 'info',
			'content' => esc_html__( 'This is a developer level feature. The buy now button module is "hooked" into a specific location on the single product pages. Themes and other plugins might also add additional elements to the same location. By modifying the loading postiion and priority, you have the ability to customize the placement of this element on that particular location. A lower number = a higher priority, so the module will appear higher on the page.', 'merchant' ),
		),
	),
),
);
