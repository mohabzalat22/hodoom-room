<?php
/**
 * Buy Now — Icon Presets.
 *
 * Static helper class providing SVG markup for built-in icon presets
 * and the list of available icon choices for admin select fields.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Buy_Now_Icons
 *
 * @since 2.2.8
 */
class Merchant_Buy_Now_Icons {

	/**
	 * SVG markup for built-in icon presets.
	 *
	 * @var array<string, string>
	 */
	private static $icons = array(
		'cart'      => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
		'lightning' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
		'arrow'     => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
	);

	/**
	 * Get SVG markup for an icon key.
	 *
	 * @param string $icon_key The icon key (none, cart, lightning, arrow, custom).
	 *
	 * @return string SVG markup or empty string.
	 */
	public static function get_svg( string $icon_key ): string {
		if ( 'none' === $icon_key || empty( $icon_key ) ) {
			return '';
		}

		return self::$icons[ $icon_key ] ?? '';
	}

	/**
	 * Get available icon choices for admin select field.
	 *
	 * @return array<string, string>
	 */
	public static function get_available_icons(): array {
		return array(
			'none'      => __( 'None', 'merchant' ),
			'cart'      => __( 'Cart', 'merchant' ),
			'lightning' => __( 'Lightning', 'merchant' ),
			'arrow'     => __( 'Arrow', 'merchant' ),
			'custom'    => __( 'Custom SVG', 'merchant' ),
		);
	}

	/**
	 * Render an icon wrapped in a span with the correct position class.
	 *
	 * @param string $icon_key  The icon key.
	 * @param string $position  Position: 'before' or 'after'.
	 * @param string $custom_svg Custom SVG markup (when icon_key = 'custom').
	 *
	 * @return string HTML markup or empty string.
	 */
	public static function render( string $icon_key, string $position = 'before', string $custom_svg = '' ): string {
		if ( 'custom' === $icon_key && ! empty( $custom_svg ) ) {
			$svg = wp_kses( $custom_svg, self::get_allowed_svg_tags() );
		} else {
			$svg = self::get_svg( $icon_key );
		}

		if ( empty( $svg ) ) {
			return '';
		}

		$class = 'after' === $position ? 'merchant-buy-now-icon merchant-buy-now-icon--after' : 'merchant-buy-now-icon';

		return '<span class="' . esc_attr( $class ) . '">' . $svg . '</span>';
	}

	/**
	 * Get allowed HTML tags for SVG sanitization via wp_kses().
	 *
	 * @return array<string, array<string, bool>>
	 */
	private static function get_allowed_svg_tags(): array {
		return array(
			'svg'      => array(
				'xmlns'        => true,
				'width'        => true,
				'height'       => true,
				'viewbox'      => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'class'        => true,
			),
			'path'     => array( 'd' => true, 'fill' => true, 'stroke' => true ),
			'circle'   => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true ),
			'rect'     => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true ),
			'line'     => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true ),
			'polyline' => array( 'points' => true, 'fill' => true, 'stroke' => true ),
			'polygon'  => array( 'points' => true, 'fill' => true, 'stroke' => true ),
			'g'        => array( 'fill' => true, 'stroke' => true, 'transform' => true ),
		);
	}
}
