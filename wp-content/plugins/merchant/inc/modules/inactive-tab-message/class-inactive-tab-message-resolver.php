<?php
/**
 * Inactive Tab Message Resolver.
 *
 * Pure logic class — no WordPress dependencies.
 * Prepares data for JS localization, selects messages, and interpolates variables.
 *
 * @package Merchant
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Inactive_Tab_Message_Resolver
 */
class Merchant_Inactive_Tab_Message_Resolver {

	/**
	 * Prepare data array for JavaScript localization.
	 *
	 * @param array<string, mixed> $settings   Module settings from get_module_settings().
	 * @param int                  $cart_count Cart item count.
	 * @param string               $cart_total Formatted cart total (with currency).
	 * @param string               $site_name  Site name from get_bloginfo().
	 *
	 * @return array<string, mixed> Keyed data to merge into merchant.setting JS object.
	 */
	public function prepare_localized_data( array $settings, int $cart_count, string $cart_total, string $site_name ): array {
		return array(
			// Existing.
			'inactive_tab_message'           => $settings['message'] ?? '',
			'inactive_tab_abandoned_message' => $settings['abandoned_message'] ?? '',
			'inactive_tab_cart_count'        => $cart_count,
			'inactive_tab_cart_total'        => $cart_total,
			'inactive_tab_site_name'         => $site_name,

			// High-value cart.
			'inactive_tab_high_value_message'   => $settings['high_value_message'] ?? '',
			'inactive_tab_high_value_threshold' => (float) ( $settings['high_value_threshold'] ?? 100 ),

			// Rotating messages.
			'inactive_tab_enable_rotation'   => (int) ( $settings['enable_rotation'] ?? 0 ),
			'inactive_tab_rotation_messages' => $settings['rotation_messages'] ?? array(),
			'inactive_tab_rotation_interval' => (int) ( $settings['rotation_interval'] ?? 3 ),

			// Favicon.
			'inactive_tab_enable_favicon' => (int) ( $settings['enable_favicon'] ?? 0 ),
			'inactive_tab_favicon_type'   => $settings['favicon_type'] ?? 'emoji',
			'inactive_tab_favicon_emoji'  => $settings['favicon_emoji'] ?? '',
			'inactive_tab_favicon_url'    => $settings['favicon_url'] ?? '',

			// Return message.
			'inactive_tab_return_message'  => $settings['return_message'] ?? '',
			'inactive_tab_return_duration' => (int) ( $settings['return_duration'] ?? 2 ),

			// Scrolling text.
			'inactive_tab_enable_scroll' => (int) ( $settings['enable_scroll'] ?? 0 ),
		);
	}

	/**
	 * Select the appropriate message based on cart state.
	 *
	 * Three tiers:
	 * 1. Empty cart     → 'message'
	 * 2. High-value     → 'high_value_message' (if threshold set and total >= threshold)
	 * 3. Has items      → 'abandoned_message'
	 *
	 * @param array<string, mixed> $settings       Module settings.
	 * @param int                  $cart_count     Number of items in cart.
	 * @param float                $cart_total_raw Raw cart total (numeric, no currency).
	 *
	 * @return string Selected message text.
	 */
	public function select_message( array $settings, int $cart_count, float $cart_total_raw ): string {
		if ( 0 === $cart_count ) {
			return $settings['message'] ?? '';
		}

		$threshold = (float) ( $settings['high_value_threshold'] ?? 0 );
		$high_msg  = $settings['high_value_message'] ?? '';

		if ( $threshold > 0 && $cart_total_raw >= $threshold && '' !== $high_msg ) {
			return $high_msg;
		}

		return $settings['abandoned_message'] ?? '';
	}

	/**
	 * Interpolate dynamic variable placeholders in a message string.
	 *
	 * Supported placeholders: {cart_count}, {cart_total}, {site_name}.
	 *
	 * @param string               $message   Message string with placeholders.
	 * @param array<string, string> $variables Associative array: cart_count, cart_total, site_name.
	 *
	 * @return string Message with placeholders replaced.
	 */
	public function interpolate_variables( string $message, array $variables ): string {
		if ( '' === $message ) {
			return '';
		}

		$replacements = array(
			'{cart_count}' => $variables['cart_count'] ?? '0',
			'{cart_total}' => $variables['cart_total'] ?? '',
			'{site_name}'  => $variables['site_name'] ?? '',
		);

		return str_replace( array_keys( $replacements ), array_values( $replacements ), $message );
	}

	/**
	 * Collect all translatable strings from module settings.
	 *
	 * Each entry has 'string' (the text) and 'name' (the translation context label).
	 *
	 * @param array<string, mixed> $settings Module settings.
	 *
	 * @return array<int, array{string: string, name: string}> Array of translatable string items.
	 */
	public function get_translatable_strings( array $settings ): array {
		$strings = array();

		$fields = array(
			'message'            => 'Inactive tab: empty cart message',
			'abandoned_message'  => 'Inactive tab: cart items message',
			'high_value_message' => 'Inactive tab: high-value cart message',
			'return_message'     => 'Inactive tab: return message',
		);

		foreach ( $fields as $key => $label ) {
			if ( ! empty( $settings[ $key ] ) ) {
				$strings[] = array(
					'string' => $settings[ $key ],
					'name'   => $label,
				);
			}
		}

		if ( ! empty( $settings['rotation_messages'] ) && is_array( $settings['rotation_messages'] ) ) {
			foreach ( $settings['rotation_messages'] as $index => $msg ) {
				if ( ! empty( $msg ) ) {
					$strings[] = array(
						'string' => $msg,
						'name'   => 'Inactive tab: rotation message #' . ( $index + 1 ),
					);
				}
			}
		}

		return $strings;
	}
}
