<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Merchant - No Plugin Support
 *
 * This class is not meant to be used directly. It will be used by the Merchant_Translator class.
 */
if ( ! class_exists( 'Merchant_No_Plugin_Support' ) ) {
	class Merchant_No_Plugin_Support implements Merchant_Language_Strategy {

		/**
		 * Register a string for translation.
		 *
		 * @param string $string_to_register The string to translate.
		 * @param string $context            The context of the string.
		 * @param bool   $multiline          Whether the string is multiline or not.
		 */
		public function register_string( $string_to_register, $context, $multiline = false ): void {
			// Do nothing.
		}

		/**
		 * Translate a string.
		 *
		 * @param string $string_to_translate The string to translate.
		 *
		 * @return string
		 */
		public function translate_string( $string_to_translate ) {
			return $string_to_translate;
		}

		/**
		 * Get WordPress language code
		 *
		 * @return string
		 */
		public function get_current_lang() {
			return get_locale();
		}

		/**
		 * Translate a taxonomy term — passthrough (no multilingual plugin active).
		 *
		 * @param string|int  $term     The term slug or term ID.
		 * @param string      $taxonomy The taxonomy name.
		 * @param string|null $language Target language code (ignored).
		 *
		 * @return string|int Same value as input.
		 *
		 * @since 2.1.9
		 */
		public function translate_term( $term, $taxonomy, $language = null ) {
			return $term;
		}

		/**
		 * Switch the active language context — no-op without a plugin.
		 *
		 * @param string $language Language code (ignored).
		 *
		 * @return void
		 *
		 * @since 2.1.9
		 */
		public function switch_language( $language ) {
			// No-op: no multilingual plugin active.
		}

		/**
		 * Restore the language context — no-op without a plugin.
		 *
		 * @return void
		 *
		 * @since 2.1.9
		 */
		public function restore_language() {
			// No-op: no multilingual plugin active.
		}

		/**
		 * Get the default language code — not applicable without a plugin.
		 *
		 * @return string Empty string.
		 *
		 * @since 2.1.9
		 */
		public function get_default_language() {
			return '';
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_all_languages_code() {
			return '';
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_post_language( $post_id ) {
			return '';
		}
	}
}