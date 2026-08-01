<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Merchant - Multi Language Translator.
 */
if ( ! class_exists( 'Merchant_Translator' ) ) {
	class Merchant_Translator {

		/**
		 * @var Merchant_Language_Strategy $language_strategy The language strategy class.
		 */
		private static $language_strategy;

		/**
		 * Merchant_Translator constructor.
		 */
		public function __construct() {
			self::set_language_strategy();
		}

		/**
		 * Check if Polylang is active.
		 *
		 * @return bool
		 */
		public static function is_polylang_active() {
			return function_exists( 'pll_count_posts' );
		}

		/**
		 * Check if WPML is active.
		 *
		 * @return bool
		 */
		public static function is_wpml_active() {
			return function_exists( 'icl_object_id' );
		}

		/**
		 * Set the translator class.
		 */
		public static function set_language_strategy(): void {
			if ( self::$language_strategy !== null ) {
				return;
			}

			if ( self::is_polylang_active() ) {
				$translator_object = new Merchant_PolyLang_Support();
			} elseif ( self::is_wpml_active() ) {
				$translator_object = new Merchant_WPML_Support();
			} else {
				$translator_object = new Merchant_No_Plugin_Support();
			}

			/**
			 * Filter the translator class.
			 *
			 * @param Merchant_Language_Strategy $translator_object The translator class object.
			 *
			 * @since 1.8
			 */
			self::$language_strategy = apply_filters( 'merchant_translator_class_object', $translator_object );
		}

		/**
		 * Register a string for translation.
		 *
		 * @param string $string_to_register The string to translate.
		 * @param string $context            The context of the string.
		 */
		public static function register_string( $string_to_register, $context = 'merchant' ): void {
			self::set_language_strategy();
			self::$language_strategy->register_string( $string_to_register, $context );
		}

		/**
		 * Translate a string.
		 *
		 * @param string $string_to_translate The string to translate.
		 *
		 * @return string
		 */
		public static function translate( $string_to_translate ) {
			self::set_language_strategy();
			$translated_string = self::$language_strategy->translate_string( $string_to_translate );

			/**
			 * Filter the translated string.
			 *
			 * @param string $string_to_translate The string to translate.
			 * @param string $translated_string   The translated string.
			 *
			 * @since 1.8
			 */
			return apply_filters( 'merchant_multi_lang_translated_string', $translated_string, $string_to_translate );
		}

		/**
		 * Translate a taxonomy term slug or ID to the current (or specified) language.
		 *
		 * Accepts a single term or an array of terms. When an array is given,
		 * each element is translated individually and an array is returned.
		 *
		 * @param array<int, string|int>|string|int $term     Term slug(s) or term ID(s) to translate.
		 * @param string                            $taxonomy The taxonomy name (e.g. 'product_cat').
		 * @param string|null                        $language Target language code. Null = current language.
		 *
		 * @return array<int, string|int>|string|int Translated term(s) (same type as input).
		 *
		 * @since 2.1.9
		 */
		public static function translate_term( $term, $taxonomy, $language = null ) {
			self::set_language_strategy();

			if ( is_array( $term ) ) {
				return array_map(
					static function ( $single ) use ( $taxonomy, $language ) {
						return self::$language_strategy->translate_term( $single, $taxonomy, $language );
					},
					$term
				);
			}

			return self::$language_strategy->translate_term( $term, $taxonomy, $language );
		}

		/**
		 * Translate a taxonomy term slug or ID to the default (primary) language.
		 *
		 * Useful for admin UI where saved values must always be in the default language
		 * regardless of the admin's active dashboard language.
		 *
		 * @param string|int $term     The term slug or term ID to translate.
		 * @param string     $taxonomy The taxonomy name (e.g. 'product_cat').
		 *
		 * @return string|int Translated term in the default language (same type as input).
		 *
		 * @since 2.1.9
		 */
		public static function translate_term_to_default( $term, $taxonomy ) {
			self::set_language_strategy();
			$default_lang = self::$language_strategy->get_default_language();

			return self::$language_strategy->translate_term( $term, $taxonomy, $default_lang );
		}

		/**
		 * Switch the active language context for subsequent queries.
		 *
		 * Must be paired with restore_language() to prevent side effects.
		 *
		 * @param string $language Language code to switch to.
		 *
		 * @return void
		 *
		 * @since 2.1.9
		 */
		public static function switch_language( $language ): void {
			self::set_language_strategy();
			self::$language_strategy->switch_language( $language );
		}

		/**
		 * Restore the language context to what it was before switch_language().
		 *
		 * @return void
		 *
		 * @since 2.1.9
		 */
		public static function restore_language(): void {
			self::set_language_strategy();
			self::$language_strategy->restore_language();
		}

		/**
		 * Switch to the default (primary) language for subsequent queries.
		 *
		 * Convenience wrapper around switch_language( get_default_language() ).
		 * Must be paired with restore_language().
		 *
		 * @return void
		 *
		 * @since 2.1.9
		 */
		public static function switch_to_default_language(): void {
			self::set_language_strategy();
			$default = self::$language_strategy->get_default_language();

			if ( ! empty( $default ) ) {
				self::$language_strategy->switch_language( $default );
			}
		}

		/**
		 * Disable language filtering for subsequent queries.
		 *
		 * Useful for admin dropdowns that must show ALL terms regardless of
		 * language (including terms that only exist in secondary languages).
		 * Must be paired with restore_language().
		 *
		 * - WPML: passes 'all' to wpml_switch_language
		 * - Polylang: passes '' to get_terms_args lang filter
		 * - No-Plugin: no-op
		 *
		 * @return void
		 *
		 * @since 2.1.9
		 */
		public static function switch_to_all_languages(): void {
			self::set_language_strategy();
			$code = self::$language_strategy->get_all_languages_code();

			self::$language_strategy->switch_language( $code );
		}

		/**
		 * Get the language code for a specific post.
		 *
		 * Reliable in any context (page load, AJAX, REST) because it
		 * queries the post's stored language rather than relying on
		 * the current request's language detection.
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return string Language code (e.g. 'ar'), or empty string.
		 *
		 * @since 2.1.9
		 */
		public static function get_post_language( $post_id ) {
			self::set_language_strategy();

			return self::$language_strategy->get_post_language( $post_id );
		}
	}
}
