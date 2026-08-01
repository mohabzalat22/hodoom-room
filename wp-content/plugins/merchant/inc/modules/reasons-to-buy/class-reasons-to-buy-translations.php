<?php
/**
 * Reasons To Buy — Translation Registration.
 *
 * Registers translatable strings from the module settings
 * with the translation layer.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Merchant_Reasons_To_Buy_Translations
 *
 * Single responsibility: iterate module settings and register
 * every reason-text with the translation provider.
 */
class Merchant_Reasons_To_Buy_Translations {

	/**
	 * Callable used to register a translatable string.
	 *
	 * Signature: fn( string $text, string $context ): void
	 *
	 * @var callable
	 */
	private $register_callback;

	/**
	 * Constructor.
	 *
	 * @param callable|null $register_callback Optional. Defaults to Merchant_Translator::register_string().
	 */
	public function __construct( ?callable $register_callback = null ) {
		$this->register_callback = $register_callback ?? array( 'Merchant_Translator', 'register_string' );
	}

	/**
	 * Register all translatable reason texts found in the settings.
	 *
	 * @param array<string, mixed> $settings Module settings array.
	 *
	 * @return void
	 */
	public function register( array $settings ): void {
		if ( empty( $settings['reasons_to_buy'] ) || ! is_array( $settings['reasons_to_buy'] ) ) {
			return;
		}

		foreach ( $settings['reasons_to_buy'] as $reason ) {
			if ( empty( $reason['items'] ) || ! is_array( $reason['items'] ) ) {
				continue;
			}

			foreach ( $reason['items'] as $item ) {
				$text = is_array( $item ) ? ( $item['text'] ?? '' ) : $item;

				if ( ! empty( $text ) ) {
					( $this->register_callback )( $text, esc_html__( 'Reasons to buy: reason text', 'merchant' ) );
				}
			}
		}
	}
}
