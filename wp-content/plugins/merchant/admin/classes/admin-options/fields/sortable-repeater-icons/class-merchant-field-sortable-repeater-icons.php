<?php
/**
 * Merchant Field: Sortable Repeater with Icons.
 *
 * Extends sortable repeater to support per-item icon selection.
 * Items are stored as objects: { text: string, icon: string }.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Sortable_Repeater_Icons
 *
 * Renders a repeatable, sortable list of text inputs with per-row icon picker.
 *
 * @since 2.3.0
 */
class Merchant_Field_Sortable_Repeater_Icons extends Merchant_Field_Sortable_Repeater {

	/**
	 * Default icon keys available for the icon picker.
	 *
	 * Modules can override this by passing an 'icons' array in the field config.
	 * An empty string key ('') is deliberately excluded — it represents
	 * "Use campaign icon" and is always available as the reset option.
	 *
	 * @since 2.3.0
	 *
	 * @return array<string, string> Icon key => human-readable label.
	 */
	public static function get_default_icons(): array {
		return array(
			'check2'         => esc_html__( 'Check (circle)', 'merchant' ),
			'check3'         => esc_html__( 'Check (square)', 'merchant' ),
			'rtb-shield'     => esc_html__( 'Shield', 'merchant' ),
			'rtb-truck'      => esc_html__( 'Truck', 'merchant' ),
			'rtb-leaf'       => esc_html__( 'Leaf', 'merchant' ),
			'rtb-star'       => esc_html__( 'Star', 'merchant' ),
			'rtb-heart'      => esc_html__( 'Heart', 'merchant' ),
			'rtb-clock'      => esc_html__( 'Clock', 'merchant' ),
			'rtb-package'    => esc_html__( 'Package', 'merchant' ),
			'rtb-money-back' => esc_html__( 'Money back', 'merchant' ),
			'rtb-lock'       => esc_html__( 'Lock', 'merchant' ),
			'rtb-thumbs-up'  => esc_html__( 'Thumbs up', 'merchant' ),
			'rtb-fire'       => esc_html__( 'Fire', 'merchant' ),
			'rtb-award'      => esc_html__( 'Award', 'merchant' ),
			'rtb-refresh'    => esc_html__( 'Refresh', 'merchant' ),
		);
	}

	/**
	 * Resolve the allowed icon keys for this field instance.
	 *
	 * If the field config includes a non-empty 'icons' array, use that.
	 * Otherwise, fall back to the default icons.
	 *
	 * @since 2.3.0
	 *
	 * @return array<int, string> Flat list of allowed icon keys.
	 */
	public function get_allowed_icons(): array {
		if ( ! empty( $this->field['icons'] ) && is_array( $this->field['icons'] ) ) {
			return $this->field['icons'];
		}

		return array_keys( self::get_default_icons() );
	}

	/**
	 * Type-specific sanitization for the sortable-repeater-icons field.
	 *
	 * Handles:
	 * - JSON string of objects: [{"text":"...", "icon":"..."}, ...]
	 * - JSON string of strings (old format): ["text1", "text2"] → converts to objects
	 * - PHP array of objects or strings
	 *
	 * @since 2.3.0
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return array<int, array{text: string, icon: string}> The sanitized value as array of { text, icon } arrays.
	 */
	protected function sanitize_value( $value ) {
		// Decode JSON strings.
		if ( is_string( $value ) ) {
			$value = json_decode( $value, true );
		}

		// Non-array input returns empty.
		if ( ! is_array( $value ) ) {
			return array();
		}

		$allowed_icons = $this->get_allowed_icons();
		$result        = array();

		foreach ( $value as $item ) {
			if ( is_array( $item ) ) {
				// New object format.
				$text = isset( $item['text'] ) ? sanitize_text_field( $item['text'] ) : '';
				$icon = isset( $item['icon'] ) ? $item['icon'] : '';
			} elseif ( is_string( $item ) ) {
				// Old string format — convert to object.
				$text = sanitize_text_field( $item );
				$icon = '';
			} else {
				continue;
			}

			// Validate icon key against allowed list.
			if ( '' !== $icon && ! in_array( $icon, $allowed_icons, true ) ) {
				$icon = '';
			}

			$result[] = array(
				'text' => $text,
				'icon' => $icon,
			);
		}

		return $result;
	}
}
