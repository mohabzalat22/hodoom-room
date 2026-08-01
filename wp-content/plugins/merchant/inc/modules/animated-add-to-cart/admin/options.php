<?php
/**
 * Animated Add to Cart
 * 
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Animation
 */

return array(
	array(
		'module' => 'animated-add-to-cart',
		'title'  => esc_html__( 'Animation', 'merchant' ),
		'fields' => array(
			array(
				'id'      => 'show_on_archive_pages',
				'type'    => 'switcher',
				'title'   => esc_html__( 'Archive / Shop Pages', 'merchant' ),
				'desc'    => esc_html__( 'Show the animation on the shop and category archive pages.', 'merchant' ),
				'default' => 1,
			),

			array(
				'id'      => 'show_on_single_product_page',
				'type'    => 'switcher',
				'title'   => esc_html__( 'Single Product Page', 'merchant' ),
				'desc'    => esc_html__( 'Show the animation on the single product page. The success animation further down always plays on add to cart, regardless of these toggles.', 'merchant' ),
				'default' => 1,
			),

			array(
				'type' => 'divider',
			),

			array(
				'id'      => 'trigger',
				'type'    => 'radio',
				'title'   => esc_html__( 'Activate this animation', 'merchant' ),
				'options' => array(
					'on-mouse-hover'      => esc_html__( 'On mouse hover', 'merchant' ),
					'on-page-load'        => esc_html__( 'On page load', 'merchant' ),
					'on-scroll-into-view' => esc_html__( 'On scroll into view', 'merchant' ),
				),
				'default' => 'on-mouse-hover',
			),

			array(
				'id'      => 'speed',
				'type'    => 'range',
				'min'     => '0.5',
				'max'     => '2',
				'step'    => '0.1',
				'unit'    => 'x',
				'default' => '1',
				'title'   => esc_html__( 'Animation speed (duration multiplier)', 'merchant' ),
			),

			array(
				'id'      => 'animation',
				'type'    => 'buttons_alt',
				'title'   => esc_html__( 'Animation style', 'merchant' ),
				'class'   => 'merchant-animated-buttons',
				'desc'    => esc_html__( 'Move your mouse over each option to see the animations. Click on one of the buttons to select that animation.', 'merchant' ),
				'options' => array(
					'flash'       => 'Flash',
					'bounce'      => 'Bounce',
					'zoom-in'     => 'Zoom in',
					'shake'       => 'Shake',
					'pulse'       => 'Pulse',
					'jello-shake' => 'Jello Shake',
					'wobble'      => 'Wobble',
					'vibrate'     => 'Vibrate',
					'swing'       => 'Swing',
					'tada'        => 'Tada',
					'glow'        => 'Glow',
				),
				'default' => 'swing',
			),

			array(
				'id'         => 'glow_color_start',
				'type'       => 'color',
				'title'      => esc_html__( 'Glow color (start)', 'merchant' ),
				'default'    => '#ff6b6b',
				'conditions' => array(
					'terms' => array(
						array(
							'field'    => 'animation',
							'operator' => '===',
							'value'    => 'glow',
						),
					),
				),
			),

			array(
				'id'         => 'glow_color_end',
				'type'       => 'color',
				'title'      => esc_html__( 'Glow color (end)', 'merchant' ),
				'default'    => '#ffffff',
				'conditions' => array(
					'terms' => array(
						array(
							'field'    => 'animation',
							'operator' => '===',
							'value'    => 'glow',
						),
					),
				),
			),

		),
	),
	array(
		'module' => 'animated-add-to-cart',
		'title'  => esc_html__( 'Success Animation', 'merchant' ),
		'fields' => array(

			array(
				'id'      => 'success_animation_enabled',
				'type'    => 'switcher',
				'title'   => esc_html__( 'Play animation after adding to cart', 'merchant' ),
				'default' => 0,
			),

			array(
				'id'         => 'success_animation_style',
				'type'       => 'select',
				'title'      => esc_html__( 'Success animation style', 'merchant' ),
				'options'    => array(
					'aatc-success-flash'  => esc_html__( 'Flash', 'merchant' ),
					'aatc-success-bounce' => esc_html__( 'Bounce', 'merchant' ),
				),
				'default'    => 'aatc-success-flash',
				'conditions' => array(
					'terms' => array(
						array(
							'field'    => 'success_animation_enabled',
							'operator' => '===',
							'value'    => true,
						),
					),
				),
			),

			array(
				'id'         => 'success_speed',
				'type'       => 'range',
				'min'        => '0.5',
				'max'        => '2',
				'step'       => '0.1',
				'unit'       => 'x',
				'default'    => '1',
				'title'      => esc_html__( 'Success animation speed (duration multiplier)', 'merchant' ),
				'conditions' => array(
					'terms' => array(
						array(
							'field'    => 'success_animation_enabled',
							'operator' => '===',
							'value'    => true,
						),
					),
				),
			),

			array(
				'id'         => 'success_flash_color',
				'type'       => 'color',
				'title'      => esc_html__( 'Flash color', 'merchant' ),
				'default'    => '#22c55e',
				'conditions' => array(
					'relation' => 'AND',
					'terms'    => array(
						array(
							'field'    => 'success_animation_enabled',
							'operator' => '===',
							'value'    => true,
						),
						array(
							'field'    => 'success_animation_style',
							'operator' => '===',
							'value'    => 'aatc-success-flash',
						),
					),
				),
			),
		),
	),
);
