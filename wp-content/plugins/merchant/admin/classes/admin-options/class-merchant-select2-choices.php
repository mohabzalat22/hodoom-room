<?php
/**
 * Merchant Select2 Choices.
 *
 * Provides formatted Select2 choice data for categories, tags,
 * brands, user roles, and customers. Extracted from
 * {@see Merchant_Admin_Options} to isolate data-provider concerns.
 *
 * @package Merchant
 * @since   1.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Select2_Choices
 *
 * Static data providers that return `{id, text}` arrays
 * formatted for Select2 dropdowns in module settings.
 *
 * @since 1.9.3
 */
class Merchant_Select2_Choices {

	/**
	 * Get product category choices formatted for Select2.
	 *
	 * Retrieves all WooCommerce product categories and formats them
	 * as a flat array of `{id, text}` objects suitable for Select2,
	 * preserving hierarchical indentation via non-breaking spaces.
	 *
	 * @since 1.9.3
	 *
	 * @return array<int, array{id: string, text: string}> Formatted category choices.
	 */
	public static function get_category_select2_choices() {
		$choices = array();

		// Bypass language filtering so every term is visible, including
		// categories that exist only in a non-default language.
		// Deduplication happens in build_category_select2_choices().
		Merchant_Translator::switch_to_all_languages();
		$categories = merchant_get_product_categories();
		Merchant_Translator::restore_language();

		if ( is_array( $categories ) && ! empty( $categories ) ) {
			$choices = self::build_category_select2_choices( $categories );
		}

		return $choices;
	}

	/**
	 * Recursively build Select2 choices from hierarchical categories.
	 *
	 * @since 1.9.3
	 *
	 * @param array<int, array{id?: int, slug?: string, name?: string, children?: array<int, mixed>}> $categories Category data.
	 * @param int                                                                                     $level Current nesting depth (used for indentation).
	 *
	 * @return array<int, array{id: string, text: string}> Formatted choices.
	 */
	private static function build_category_select2_choices( $categories, $level = 0 ) {
		$choices = array();

		foreach ( $categories as $cat ) {
			$slug    = $cat['slug'] ?? '';
			$term_id = $cat['id'] ?? 0;

			// Deduplicate: skip non-default language versions that have a default counterpart.
			// Keeps the default-language term (ASCII slug) and orphans (no default version).
			$default_term_id = Merchant_Translator::translate_term_to_default( $term_id, 'product_cat' );
			if ( $default_term_id !== $term_id ) {
				continue;
			}

			$indent = str_repeat( '&nbsp;', $level * 4 );
			$label  = self::resolve_term_label( $term_id, 'product_cat', $cat['name'] ?? '' );

			$choices[] = array(
				'id'   => esc_attr( $slug ),
				'text' => esc_html( $indent . $label ),
			);

			if ( ! empty( $cat['children'] ) ) {
				$choices = array_merge( $choices, self::build_category_select2_choices( $cat['children'], $level + 1 ) );
			}
		}

		return $choices;
	}

	/**
	 * Get product tag choices formatted for Select2.
	 *
	 * Retrieves all WooCommerce product tags and formats them
	 * as a flat array of `{id, text}` objects for Select2.
	 *
	 * @since 1.9.3
	 *
	 * @return array<int, array{id: string, text: string}> Formatted tag choices.
	 */
	public static function get_tag_select2_choices() {
		$choices = array();

		// Bypass language filtering so every term is visible.
		Merchant_Translator::switch_to_all_languages();
		$tags = get_terms(
			array(
				'taxonomy'   => 'product_tag',
				'hide_empty' => false,
			)
		);
		Merchant_Translator::restore_language();

		if ( is_array( $tags ) && ! is_wp_error( $tags ) && ! empty( $tags ) ) { // @phpstan-ignore function.impossibleType
			foreach ( $tags as $tag ) {
				// Deduplicate: skip non-default language versions that have a default counterpart.
				$default_tag_id = Merchant_Translator::translate_term_to_default( $tag->term_id, 'product_tag' );
				if ( $default_tag_id !== $tag->term_id ) {
					continue;
				}

				$label = self::resolve_term_label( $tag->term_id, 'product_tag', $tag->name );

				$choices[] = array(
					'id'   => esc_attr( $tag->slug ),
					'text' => esc_html( $label ),
				);
			}
		}

		return $choices;
	}

	/**
	 * Get product brand choices formatted for Select2.
	 *
	 * Retrieves all terms from the `product_brand` taxonomy
	 * (WooCommerce Brands) and formats them for Select2.
	 * Returns an empty array if the taxonomy does not exist.
	 *
	 * @since 1.9.3
	 *
	 * @return array<int, array{id: string, text: string}> Formatted brand choices.
	 */
	public static function get_brand_select2_choices() {
		$choices = array();

		if ( ! taxonomy_exists( 'product_brand' ) ) {
			return $choices;
		}

		// Bypass language filtering so every term is visible.
		Merchant_Translator::switch_to_all_languages();
		$brands = get_terms(
			array(
				'taxonomy'   => 'product_brand',
				'hide_empty' => false,
			)
		);
		Merchant_Translator::restore_language();

		if ( is_array( $brands ) && ! is_wp_error( $brands ) && ! empty( $brands ) ) { // @phpstan-ignore function.impossibleType
			foreach ( $brands as $brand ) {
				// Deduplicate: skip non-default language versions that have a default counterpart.
				$default_brand_id = Merchant_Translator::translate_term_to_default( $brand->term_id, 'product_brand' );
				if ( $default_brand_id !== $brand->term_id ) {
					continue;
				}

				$label = self::resolve_term_label( $brand->term_id, 'product_brand', $brand->name );

				$choices[] = array(
					'id'   => esc_attr( $brand->slug ),
					'text' => esc_html( $label ),
				);
			}
		}

		return $choices;
	}

	/**
	 * Resolve the current-language label for a term using its ID.
	 *
	 * Translates the default-language term_id to the current language,
	 * then fetches the translated term's name. Falls back to the provided
	 * default name when no translation exists.
	 *
	 * @since 2.1.9
	 *
	 * @param int    $term_id      Default-language term ID.
	 * @param string $taxonomy     Taxonomy name.
	 * @param string $default_name Fallback name if no translation exists.
	 *
	 * @return string The current-language term name, or $default_name.
	 */
	private static function resolve_term_label( $term_id, $taxonomy, $default_name ) {
		if ( empty( $term_id ) ) {
			return $default_name;
		}

		// translate_term( int ) returns the translated term_id via pll_get_term / wpml_object_id.
		$translated_id = Merchant_Translator::translate_term( $term_id, $taxonomy );

		if ( $translated_id === $term_id ) {
			// No translation exists, keep the default name.
			return $default_name;
		}

		$translated_term = get_term( (int) $translated_id, $taxonomy );

		if ( $translated_term instanceof \WP_Term ) {
			return $translated_term->name;
		}

		return $default_name;
	}

	/**
	 * Get user role choices formatted for Select2.
	 *
	 * Retrieves all editable WordPress user roles and formats
	 * them as `{id, text}` objects for Select2 dropdowns.
	 *
	 * @since 1.9.3
	 *
	 * @return array<int, array{id: string, text: string}> Formatted role choices.
	 */
	public static function get_user_roles_select2_choices() {
		$choices = array();

		// get_editable_roles() is in wp-admin/includes/user.php
		// which is not loaded outside admin context (e.g. REST API).
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		$user_roles = get_editable_roles();

		if ( ! empty( $user_roles ) ) {
			foreach ( $user_roles as $role_id => $role_data ) {
				$choices[] = array(
					'id'   => $role_id,
					'text' => $role_data['name'],
				);
			}
		}

		return $choices;
	}

	/**
	 * Get customer user choices formatted for Select2.
	 *
	 * Retrieves all users with the `customer` role and returns them
	 * as `{id, text}` objects. Results are cached in a transient
	 * with no expiration; the cache is cleared via
	 * {@see Merchant_Select2_Choices::clear_customer_choices_cache()}.
	 *
	 * @since 1.9.3
	 *
	 * @return array<int, array{id: int, text: string}> Formatted customer choices.
	 */
	public static function get_customers_select2_choices() {
		$cache_key = 'customers_select2_choices';
		$choices   = get_transient( $cache_key );

		if ( ! empty( $choices ) && is_array( $choices ) ) {
			return $choices;
		}

		$customer_users = get_users(
			array(
				'role'   => 'customer',
				'fields' => array( 'ID', 'display_name' ),
			)
		);

		$choices = array();
		if ( ! empty( $customer_users ) ) {
			foreach ( $customer_users as $user ) {
				$choices[] = array(
					'id'   => $user->ID,
					'text' => $user->display_name,
				);
			}
		}

		set_transient( $cache_key, $choices );

		return $choices;
	}

	/**
	 * Clear the customers Select2 choices transient cache.
	 *
	 * Hooked to `clean_user_cache` to invalidate the customer
	 * list whenever a customer user is created, updated, or deleted.
	 *
	 * @since 1.9.3
	 *
	 * @param int     $user_id The user ID being cleaned.
	 * @param WP_User $user    The user object.
	 *
	 * @return void
	 */
	public function clear_customer_choices_cache( $user_id, $user ) {
		$user_roles = ! empty( $user->roles ) ? $user->roles : array();
		if ( in_array( 'customer', $user_roles, true ) ) {
			delete_transient( 'customers_select2_choices' );
		}
	}
}
