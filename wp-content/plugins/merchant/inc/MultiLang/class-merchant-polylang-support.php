<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Merchant - Polylang Support
 *
 * This class is not meant to be used directly. It will be used by the Merchant_Translator class.
 */
if ( ! class_exists( 'Merchant_PolyLang_Support' ) ) {
	class Merchant_PolyLang_Support implements Merchant_Language_Strategy {

		/**
		 * Static memoization cache for term translations.
		 *
		 * Persists across instances since set_language_strategy() creates
		 * new instances on each call.
		 *
		 * @var array<string, string|int>
		 */
		private static $term_cache = array();

		/**
		 * Stored reference to the language-switching filter callback.
		 *
		 * Keeping a handle allows targeted removal via remove_filter()
		 * instead of the destructive remove_all_filters().
		 *
		 * @var callable|null
		 */
		private $lang_filter_callback = null;

		/**
		 * Register a string for translation.
		 *
		 * @param string $string_to_register The string to translate.
		 * @param string $context            The context of the string.
		 * @param bool   $multiline          Whether the string is multiline or not.
		 */
		public function register_string( $string_to_register, $context, $multiline = false ): void {
			pll_register_string( $context, $string_to_register, 'Merchant', $multiline );
		}

		/**
		 * Translate a string.
		 *
		 * @param string $string_to_translate The string to translate.
		 *
		 * @return string
		 */
		public function translate_string( $string_to_translate ) {
			return pll__( $string_to_translate );
		}

		/**
		 * Get Current language code
		 *
		 * @return string
		 */
		public function get_current_lang() {
			return (string) pll_current_language();
		}

		/**
		 * Translate a taxonomy term slug or ID via Polylang.
		 *
		 * @param string|int  $term     The term slug or term ID to translate.
		 * @param string      $taxonomy The taxonomy name (e.g. 'product_cat').
		 * @param string|null $language Target language code. Null = current language.
		 *
		 * @return string|int Translated term (same type as input).
		 *
		 * @since 2.1.9
		 */
		public function translate_term( $term, $taxonomy, $language = null ) {
			$lang      = $language ?? (string) pll_current_language();
			$cache_key = $term . '|' . $taxonomy . '|' . $lang;

			if ( isset( self::$term_cache[ $cache_key ] ) ) {
				return self::$term_cache[ $cache_key ];
			}

			$result = is_string( $term )
				? $this->translate_slug( $term, $taxonomy, $lang )
				: $this->translate_id( $term, $lang );

			self::$term_cache[ $cache_key ] = $result;

			return $result;
		}

		/**
		 * Translate a taxonomy term slug via Polylang.
		 *
		 * @param string $slug     The term slug to translate.
		 * @param string $taxonomy The taxonomy name.
		 * @param string $lang     Target language code.
		 *
		 * @return string Translated slug, or original if no translation found.
		 */
		private function translate_slug( $slug, $taxonomy, $lang ) {
			// Temporarily bypass Polylang's language filter so get_term_by()
			// can resolve the default-language slug even when the visitor is
			// browsing in a secondary language.
			$bypass = static function ( $args ) {
				$args['lang'] = '';

				return $args;
			};
			add_filter( 'get_terms_args', $bypass, 999 );
			$term_obj = get_term_by( 'slug', $slug, $taxonomy );
			remove_filter( 'get_terms_args', $bypass, 999 );

			if ( ! $term_obj ) {
				return $slug;
			}

			$translated_id = pll_get_term( $term_obj->term_id, $lang );

			if ( empty( $translated_id ) ) {
				return $slug;
			}

			$translated_term = get_term( $translated_id, $taxonomy );

			return ( $translated_term && ! is_wp_error( $translated_term ) ) ? $translated_term->slug : $slug;
		}

		/**
		 * Translate a taxonomy term ID via Polylang.
		 *
		 * @param int    $term_id The term ID to translate.
		 * @param string $lang    Target language code.
		 *
		 * @return int Translated term ID, or original if no translation found.
		 */
		private function translate_id( $term_id, $lang ) {
			$translated_id = pll_get_term( $term_id, $lang );

			return ! empty( $translated_id ) ? $translated_id : $term_id;
		}

		/**
		 * Switch the active language context for subsequent queries.
		 *
		 * @param string $language Language code to switch to.
		 *
		 * @return void
		 *
		 * @since 2.1.9
		 */
		public function switch_language( $language ) {
			// Store the callback so restore_language() can remove this exact filter.
			$this->lang_filter_callback = static function ( $args ) use ( $language ) {
				$args['lang'] = $language;

				return $args;
			};

			add_filter( 'get_terms_args', $this->lang_filter_callback, 999 );
		}

		/**
		 * Restore the language context to what it was before switch_language().
		 *
		 * @return void
		 *
		 * @since 2.1.9
		 */
		public function restore_language() {
			if ( null !== $this->lang_filter_callback ) {
				remove_filter( 'get_terms_args', $this->lang_filter_callback, 999 );
				$this->lang_filter_callback = null;
			}
		}

		/**
		 * Get the default (primary) language code from Polylang.
		 *
		 * @return string Language code (e.g. 'en').
		 *
		 * @since 2.1.9
		 */
		public function get_default_language() {
			return pll_default_language();
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
			if ( function_exists( 'pll_get_post_language' ) ) {
				$lang = pll_get_post_language( $post_id, 'slug' );

				return $lang ? $lang : '';
			}

			return '';
		}
	}
}
