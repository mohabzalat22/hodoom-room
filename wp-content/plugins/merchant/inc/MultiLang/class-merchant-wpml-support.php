<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Merchant - WPML Support
 *
 * This class is not meant to be used directly. It will be used by the Merchant_Translator class.
 */
if ( ! class_exists( 'Merchant_WPML_Support' ) ) {
	class Merchant_WPML_Support implements Merchant_Language_Strategy {

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
		 * Register a string for translation.
		 *
		 * @param string $string_to_register The string to translate.
		 * @param string $context            The context of the string.
		 * @param bool   $multiline          Not used!.
		 */
		public function register_string( $string_to_register, $context, $multiline = false ): void {
			/**
			 * @see   https://wpml.org/wpml-hook/wpml_register_single_string/
			 *
			 * @param string $domain             The text domain.
			 * @param string $context            The context of the string.
			 * @param string $string_to_register The string to translate.
			 *
			 * @since 1.8.0
			 */
			do_action( 'wpml_register_single_string', 'Merchant', $context, $string_to_register );
		}

		/**
		 * Translate a string.
		 *
		 * @param string $string_to_translate The string to translate.
		 *
		 * @return string
		 */
		public function translate_string( $string_to_translate ) {
			/**
			 * @see   https://wpml.org/wpml-hook/wpml_translate_single_string/
			 *
			 * @param string $string_to_translate The string to translate.
			 * @param string $domain              The text domain.
			 * @param string $name                The string name.
			 *
			 * @since 1.8.0
			 */
			return apply_filters( 'wpml_translate_single_string', $string_to_translate, 'Merchant', $string_to_translate );
		}

		/**
		 * Get Current language code
		 *
		 * @return string
		 */
		public function get_current_lang() {
			/**
			 * @see   https://wpml.org/wpml-hook/wpml_current_language/
			 *
			 * @since 1.9.0
			 */
			return apply_filters( 'wpml_current_language', null );
		}

		/**
		 * Translate a taxonomy term slug or ID via WPML.
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
			$cache_key = $term . '|' . $taxonomy . '|' . ( $language ?? '' );

			if ( isset( self::$term_cache[ $cache_key ] ) ) {
				return self::$term_cache[ $cache_key ];
			}

			$result = is_string( $term )
				? $this->translate_slug( $term, $taxonomy, $language )
				: $this->translate_id( $term, $taxonomy, $language );

			self::$term_cache[ $cache_key ] = $result;

			return $result;
		}

		/**
		 * Translate a taxonomy term slug via WPML.
		 *
		 * @param string      $slug     The term slug to translate.
		 * @param string      $taxonomy The taxonomy name.
		 * @param string|null $language Target language code.
		 *
		 * @return string Translated slug, or original if no translation found.
		 */
		private function translate_slug( $slug, $taxonomy, $language ) {
			/**
			 * Switch to 'all' languages so get_term_by() can resolve the
			 * default-language slug even when the visitor is browsing in
			 * a secondary language (WPML filters term queries by current lang).
			 *
			 * @see   https://wpml.org/wpml-hook/wpml_switch_language/
			 * @since 2.1.9
			 */
			do_action( 'wpml_switch_language', 'all' );
			$term_obj = get_term_by( 'slug', $slug, $taxonomy );

			/**
			 * Restore the original WPML language context.
			 *
			 * @see   https://wpml.org/wpml-hook/wpml_switch_language/
			 * @since 2.1.9
			 */
			do_action( 'wpml_switch_language', null );

			if ( ! $term_obj ) {
				return $slug;
			}

			/**
			 * @see https://wpml.org/wpml-hook/wpml_object_id/
			 *
			 * @since 2.1.9
			 */
			$translated_id   = apply_filters( 'wpml_object_id', $term_obj->term_id, $taxonomy, true, $language );
			$translated_term = get_term( $translated_id, $taxonomy );

			return ( $translated_term && ! is_wp_error( $translated_term ) ) ? $translated_term->slug : $slug;
		}

		/**
		 * Translate a taxonomy term ID via WPML.
		 *
		 * @param int         $term_id  The term ID to translate.
		 * @param string      $taxonomy The taxonomy name.
		 * @param string|null $language Target language code.
		 *
		 * @return int Translated term ID, or original if no translation found.
		 */
		private function translate_id( $term_id, $taxonomy, $language ) {
			/**
			 * @see https://wpml.org/wpml-hook/wpml_object_id/
			 *
			 * @since 2.1.9
			 */
			$translated_id = apply_filters( 'wpml_object_id', $term_id, $taxonomy, true, $language );

			return $translated_id ? $translated_id : $term_id;
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
			/**
			 * Switch WPML language context.
			 *
			 * @see https://wpml.org/wpml-hook/wpml_switch_language/
			 *
			 * @since 2.1.9
			 */
			do_action( 'wpml_switch_language', $language );
		}

		/**
		 * Restore the language context to what it was before switch_language().
		 *
		 * @return void
		 *
		 * @since 2.1.9
		 */
		public function restore_language() {
			/**
			 * Restore WPML language context to the original language.
			 *
			 * Passing null restores the language that was active before the last switch.
			 *
			 * @see https://wpml.org/wpml-hook/wpml_switch_language/
			 *
			 * @since 2.1.9
			 */
			do_action( 'wpml_switch_language', null );
		}

		/**
		 * Get the default (primary) language code from WPML.
		 *
		 * @return string Language code (e.g. 'en').
		 *
		 * @since 2.1.9
		 */
		public function get_default_language() {
			/**
			 * Get the WPML default language.
			 *
			 * @see https://wpml.org/wpml-hook/wpml_default_language/
			 *
			 * @since 2.1.9
			 */
			return apply_filters( 'wpml_default_language', '' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_all_languages_code() {
			return 'all';
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_post_language( $post_id ) {
			/**
			 * @see https://wpml.org/wpml-hook/wpml_post_language_details/
			 *
			 * @since 2.1.9
			 */
			$details = apply_filters( 'wpml_post_language_details', null, $post_id );

			if ( is_array( $details ) && ! empty( $details['language_code'] ) ) {
				return $details['language_code'];
			}

			return '';
		}
	}
}
