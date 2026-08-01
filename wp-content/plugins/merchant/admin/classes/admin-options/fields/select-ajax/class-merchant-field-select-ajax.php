<?php
/**
 * Merchant Field: Select Ajax
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Select_Ajax
 *
 * Renders a Select2 dropdown that loads options via AJAX.
 *
 * @since 2.2.5
 */
class Merchant_Field_Select_Ajax extends Merchant_Abstract_Field {

	/**
	 * Render the select-ajax field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the select-ajax field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		// When inline options are provided, validate against the allowlist.
		// This preserves percent-encoded taxonomy slugs (e.g. Arabic categories)
		// that sanitize_text_field would destroy by stripping %xx octets.
		if ( ! empty( $this->field['options'] ) && is_array( $this->field['options'] ) ) {
			$valid_ids = wp_list_pluck( $this->field['options'], 'id' );

			if ( is_array( $value ) ) {
				return array_values(
					array_filter(
						$value,
						static function ( $v ) use ( $valid_ids ) {
							return in_array( $v, $valid_ids, true );
						}
					)
				);
			}

			return in_array( $value, $valid_ids, true ) ? $value : '';
		}

		// For AJAX-loaded sources (post, user, product), sanitize normally.
		if ( is_array( $value ) ) {
			return array_filter( array_map( 'sanitize_text_field', $value ) );
		}

		return sanitize_text_field( $value );
	}
}
