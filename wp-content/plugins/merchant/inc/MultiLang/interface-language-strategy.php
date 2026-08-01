<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Merchant - Multi Language
 */
if ( ! interface_exists( 'Merchant_Language_Strategy' ) ) {
	interface Merchant_Language_Strategy {

		/**
		 * Register a string for translation.
		 *
		 * @param string $string_to_register The string to translate.
		 * @param string $context            The context of the string.
		 * @param bool   $multiline          Whether the string is multiline or not.
		 */
		public function register_string( $string_to_register, $context, $multiline = false ): void;

		/**
		 * Translate a string.
		 *
		 * @param string $string_to_translate The string to translate.
		 *
		 * @return string
		 */
		public function translate_string( $string_to_translate );

		/**
		 * Get Current language code
		 *
		 * @return string
		 */
		public function get_current_lang();

		/**
		 * Translate a taxonomy term slug or ID to another language.
		 *
		 * When $term is a string (slug), returns the translated slug.
		 * When $term is an int (term ID), returns the translated term ID.
		 * Returns the original $term unchanged if no translation is found.
		 *
		 * @param string|int  $term     The term slug or term ID to translate.
		 * @param string      $taxonomy The taxonomy name (e.g. 'product_cat').
		 * @param string|null $language Target language code. Null = current language.
		 *
		 * @return string|int Translated term (same type as input).
		 *
		 * @since 2.1.9
		 */
		public function translate_term( $term, $taxonomy, $language = null );

		/**
		 * Switch the active language context for subsequent queries.
		 *
		 * Call restore_language() to revert to the original language.
		 *
		 * @param string $language Language code to switch to.
		 *
		 * @return void
		 *
		 * @since 2.1.9
		 */
		public function switch_language( $language );

		/**
		 * Restore the language context to what it was before switch_language().
		 *
		 * @return void
		 *
		 * @since 2.1.9
		 */
		public function restore_language();

		/**
		 * Get the default (primary) language code.
		 *
		 * @return string Language code (e.g. 'en'), or empty string if unavailable.
		 *
		 * @since 2.1.9
		 */
		public function get_default_language();

		/**
		 * Get the language code that disables language filtering.
		 *
		 * Intended for use with switch_language() to query terms
		 * across all languages (e.g. admin Select2 dropdowns).
		 *
		 * @return string The "all languages" code for this plugin.
		 *
		 * @since 2.1.9
		 */
		public function get_all_languages_code();

		/**
		 * Get the language code for a specific post.
		 *
		 * Useful in AJAX contexts where the current language detection
		 * may be unreliable, allowing callers to determine the actual
		 * language of the content being evaluated.
		 *
		 * @param int $post_id The post ID to check.
		 *
		 * @return string Language code (e.g. 'ar', 'en'), or empty string if unavailable.
		 *
		 * @since 2.1.9
		 */
		public function get_post_language( $post_id );
	}
}
