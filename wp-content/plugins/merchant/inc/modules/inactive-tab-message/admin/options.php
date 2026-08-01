<?php
/**
 * Inactive Tab Message — Admin — Option group definitions.
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
 * Messages Section.
 */

return array(
	array(
	'title'  => esc_html__( 'Messages', 'merchant' ),
	'module' => 'inactive-tab-message',
	'fields' => array(

		array(
			'id'      => 'info_dynamic_variables',
			'type'    => 'info',
			'content' => '<strong>' . esc_html__( 'Dynamic Variables:', 'merchant' ) . '</strong><br>'
				. '<code>{cart_count}</code> — ' . esc_html__( 'Number of items in cart', 'merchant' ) . '<br>'
				. '<code>{cart_total}</code> — ' . esc_html__( 'Formatted cart total with currency', 'merchant' ) . '<br>'
				. '<code>{site_name}</code> — ' . esc_html__( 'Your site name', 'merchant' ),
		),

		array(
			'id'      => 'message',
			'type'    => 'text',
			'title'   => esc_html__( 'Empty cart message', 'merchant' ),
			'desc'    => esc_html__( 'Shown when the cart is empty and user switches to another tab.', 'merchant' ),
			'default' => esc_html__( '✋ Don\'t forget this', 'merchant' ),
		),

		array(
			'id'      => 'abandoned_message',
			'type'    => 'text',
			'title'   => esc_html__( 'Cart items message', 'merchant' ),
			'desc'    => esc_html__( 'Shown when the user has items in the cart and switches tab.', 'merchant' ),
			'default' => esc_html__( '✋ You left something in the cart', 'merchant' ),
		),

		array(
			'id'      => 'high_value_message',
			'type'    => 'text',
			'title'   => esc_html__( 'High-value cart message', 'merchant' ),
			'desc'    => esc_html__( 'Shown when the cart total exceeds the threshold below. Leave empty to disable.', 'merchant' ),
			'default' => '',
		),

		array(
			'id'        => 'high_value_threshold',
			'type'      => 'number',
			'title'     => esc_html__( 'High-value threshold', 'merchant' ),
			'desc'      => esc_html__( 'Cart total amount that triggers the high-value message.', 'merchant' ),
			'default'   => 100,
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array(
						'field'    => 'high_value_message',
						'operator' => '!==',
						'value'    => '',
					),
				),
			),
			'ai_meta'   => array(
				'usage_hint' => 'Minimum cart total (in store currency) that triggers the high_value_message instead of the abandoned_message. Only active when high_value_message is not empty.',
			),
		),

		array(
			'id'      => 'return_message',
			'type'    => 'text',
			'title'   => esc_html__( 'Return-to-tab message', 'merchant' ),
			'desc'    => esc_html__( 'Brief message shown when the user returns to the tab. Leave empty to disable.', 'merchant' ),
			'default' => '',
		),

		array(
			'id'        => 'return_duration',
			'type'      => 'range',
			'title'     => esc_html__( 'Return message duration (seconds)', 'merchant' ),
			'min'       => 1,
			'max'       => 10,
			'step'      => 1,
			'unit'      => 's',
			'default'   => 2,
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array(
						'field'    => 'return_message',
						'operator' => '!==',
						'value'    => '',
					),
				),
			),
		),

	),
),
	array(
	'title'  => esc_html__( 'Favicon', 'merchant' ),
	'module' => 'inactive-tab-message',
	'fields' => array(

		array(
			'id'      => 'enable_favicon',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Change favicon on tab leave', 'merchant' ),
			'desc'    => esc_html__( 'Replace the browser favicon when the tab is inactive. Restored on return.', 'merchant' ),
			'default' => 0,
			'ai_meta' => array(
				'toggle_for' => 'favicon_type',
				'usage_hint' => 'Enable this toggle before setting favicon_type, favicon_emoji, or favicon_url. When enabled, the browser favicon is replaced while the tab is inactive.',
			),
		),

		array(
			'id'        => 'favicon_type',
			'type'      => 'radio',
			'title'     => esc_html__( 'Favicon type', 'merchant' ),
			'options'   => array(
				'emoji' => esc_html__( 'Emoji', 'merchant' ),
				'image' => esc_html__( 'Custom image', 'merchant' ),
			),
			'default'   => 'emoji',
			'condition' => array( 'enable_favicon', '==', '1' ),
		),

		array(
			'id'          => 'favicon_emoji',
			'type'        => 'select',
			'title'       => esc_html__( 'Favicon emoji', 'merchant' ),
			'options'     => array(
				'wave'  => '👋 ' . esc_html__( 'Wave', 'merchant' ),
				'bell'  => '🔔 ' . esc_html__( 'Bell', 'merchant' ),
				'cart'  => '🛒 ' . esc_html__( 'Cart', 'merchant' ),
				'clock' => '⏰ ' . esc_html__( 'Clock', 'merchant' ),
				'alert' => '❗ ' . esc_html__( 'Alert', 'merchant' ),
				'money' => '💰 ' . esc_html__( 'Money', 'merchant' ),
				'fire'  => '🔥 ' . esc_html__( 'Fire', 'merchant' ),
				'star'  => '⭐ ' . esc_html__( 'Star', 'merchant' ),
			),
			'default'     => 'wave',
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array(
						'field'    => 'enable_favicon',
						'operator' => '===',
						'value'    => true,
					),
					array(
						'field'    => 'favicon_type',
						'operator' => '===',
						'value'    => 'emoji',
					),
				),
			),
		),

		array(
			'id'          => 'favicon_url',
			'type'        => 'upload',
			'title'       => esc_html__( 'Custom favicon image', 'merchant' ),
			'desc'        => esc_html__( 'Upload a square image (recommended 32×32 or 64×64 pixels).', 'merchant' ),
			'default'     => '',
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array(
						'field'    => 'enable_favicon',
						'operator' => '===',
						'value'    => true,
					),
					array(
						'field'    => 'favicon_type',
						'operator' => '===',
						'value'    => 'image',
					),
				),
			),
		),

	),
),
	array(
	'title'  => esc_html__( 'Message Rotation', 'merchant' ),
	'module' => 'inactive-tab-message',
	'fields' => array(

		array(
			'id'      => 'enable_rotation',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Enable rotating messages', 'merchant' ),
			'desc'    => esc_html__( 'When enabled, the tab title cycles through the messages below instead of showing the single messages above.', 'merchant' ),
			'default' => 0,
			'ai_meta' => array(
				'toggle_for' => 'rotation_messages',
				'usage_hint' => 'Enable this toggle before setting rotation_messages. When enabled, the single message fields above are ignored — only the rotation list is used.',
			),
		),

		array(
			'id'           => 'rotation_messages',
			'type'         => 'sortable_repeater',
			'title'        => esc_html__( 'Rotation messages', 'merchant' ),
			'desc'         => esc_html__( 'Add messages to cycle through. Dynamic variables ({cart_count}, {cart_total}, {site_name}) are supported.', 'merchant' ),
			'button_label' => esc_html__( 'Add Message', 'merchant' ),
			'default'      => array(),
			'condition'    => array( 'enable_rotation', '==', '1' ),
			'ai_meta'      => array(
				'toggled_by' => 'enable_rotation',
				'usage_hint' => 'List of messages to cycle through on the inactive tab. Only active when enable_rotation=1. Supports dynamic variables: {cart_count}, {cart_total}, {site_name}.',
			),
		),

		array(
			'id'        => 'rotation_interval',
			'type'      => 'range',
			'title'     => esc_html__( 'Rotation interval (seconds)', 'merchant' ),
			'min'       => 1,
			'max'       => 10,
			'step'      => 1,
			'unit'      => 's',
			'default'   => 3,
			'condition' => array( 'enable_rotation', '==', '1' ),
		),

	),
),
	array(
	'title'  => esc_html__( 'Scrolling Text', 'merchant' ),
	'module' => 'inactive-tab-message',
	'fields' => array(

		array(
			'id'      => 'enable_scroll',
			'type'    => 'switcher',
			'title'   => esc_html__( 'Scroll long messages', 'merchant' ),
			'desc'    => esc_html__( 'When enabled, the tab title will scroll (marquee) if the message is longer than the tab can display. Direction is automatically detected for RTL languages (Arabic, Farsi, etc.).', 'merchant' ),
			'default' => 0,
		),

	),
),
);
