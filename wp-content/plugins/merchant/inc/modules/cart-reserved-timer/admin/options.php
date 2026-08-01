<?php

/**
 * Cart Reserved Timer — Option group definitions.
 *
 * Pure data file. Returns the option groups array
 * consumed by init_option_groups().
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Variables
$icon_path = MERCHANT_URI . 'assets/images/icons/' . Merchant_Cart_Reserved_timer::MODULE_ID;

// Settings

return array(
	array(
	'title'  => esc_html__( 'Settings', 'merchant' ),
	'module' => Merchant_Cart_Reserved_timer::MODULE_ID,
	'fields' => array(
		array(
			'id'      => 'duration',
			'type'    => 'number',
			'title'   => esc_html__( 'Count down duration minutes', 'merchant' ),
			'default' => 10,
		),

		array(
			'id'      => 'reserved_message',
			'type'    => 'text',
			'title'   => esc_html__( 'Cart reserved message', 'merchant' ),
			'default' => esc_html__( 'An item in your cart is in high demand.', 'merchant' ),
		),

		array(
			'id'      => 'timer_message_minutes',
			'type'    => 'text',
			'title'   => esc_html__( 'Timer message for > 1 min ', 'merchant' ),
			'default' => esc_html__( 'Your cart is saved for {timer} minutes!', 'merchant' ),
		),

		array(
			'id'      => 'timer_message_seconds',
			'type'    => 'text',
			'title'   => esc_html__( 'Timer message for  < 1 min', 'merchant' ),
			'default' => esc_html__( 'Your cart is saved for {timer} seconds!', 'merchant' ),
		),

		array(
			'id'      => 'time_expires',
			'type'    => 'radio',
			'title'   => esc_html__( 'What to do after the timer expires?', 'merchant' ),
			'options' => array(
				'hide-timer' => esc_html__( 'Hide timer', 'merchant' ),
				'clear-cart' => esc_html__( 'Clear cart', 'merchant' ),
			),
			'default' => 'clear-cart',
			'ai_meta' => array(
				'usage_hint' => 'clear-cart removes all items from the cart on expiry — destructive. Use hide-timer to just hide the countdown without affecting cart contents.',
			),
		),

		array(
			'id'      => 'icon',
			'type'    => 'choices',
			'title'   => esc_html__( 'Choose an icon', 'merchant' ),
			'class'   => 'merchant-module-page-setting-field-choices-icon',
			'options' => array(
				'none'       => $icon_path . '/cancel.svg',
				'fire'       => $icon_path . '/fire.svg',
				'clock'      => $icon_path . '/clock.svg',
				'hour-glass' => $icon_path . '/hour-glass.svg',
			),
			'default' => 'fire',
		),

		array(
			'id'      => 'background_color',
			'type'    => 'color',
			'title'   => esc_html__( 'Background Color', 'merchant' ),
			'default' => '#f4f6f8',
		),

	),
),
	array(
	'module' => Merchant_Cart_Reserved_timer::MODULE_ID,
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
			'default'   => '[merchant_module_' . str_replace( '-', '_', Merchant_Cart_Reserved_timer::MODULE_ID ) . ']',
			'condition' => array( 'use_shortcode', '==', '1' ),
		),
	),
),
);
