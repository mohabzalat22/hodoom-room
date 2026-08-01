<?php
/**
 * Buy Now — Campaign Resolver.
 *
 * Resolves the first matching active campaign for a given product and user context.
 * Follows the first-match-wins pattern used by Buy X Get Y and FBT modules.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Buy_Now_Resolver
 *
 * Iterates over campaigns in array order (matching admin drag-and-drop order).
 * For each campaign, checks active status, product targeting, per-campaign
 * exclusions, and user conditions. First match wins.
 *
 * @since 2.2.8
 */
class Merchant_Buy_Now_Resolver {

	/**
	 * Campaigns array from module settings.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private $campaigns;

	/**
	 * Resolution cache keyed by product_id + user_id.
	 *
	 * @var array<string, array<string, mixed>|null>
	 */
	private $cache = array();

	/**
	 * Constructor.
	 *
	 * @param array<int, array<string, mixed>> $campaigns The campaigns array from settings.
	 */
	public function __construct( array $campaigns ) {
		$this->campaigns = $campaigns;
	}

	/**
	 * Resolve the first matching campaign for a product.
	 *
	 * @param WC_Product $product The product to resolve for.
	 *
	 * @return array<string, mixed>|null The matching campaign data or null if no match.
	 */
	public function resolve( $product ) {
		$cache_key = $this->get_cache_key( $product );

		if ( array_key_exists( $cache_key, $this->cache ) ) {
			return $this->cache[ $cache_key ];
		}

		foreach ( $this->campaigns as $campaign ) {
			if ( $this->campaign_matches( $campaign, $product ) ) {
				$this->cache[ $cache_key ] = $campaign;

				return $campaign;
			}
		}

		$this->cache[ $cache_key ] = null;

		return null;
	}

	/**
	 * Clear the resolution cache.
	 *
	 * @return void
	 */
	public function clear_cache(): void {
		$this->cache = array();
	}

	/**
	 * Find a campaign by its flexible_id.
	 *
	 * Centralised lookup used by both the main module class and
	 * the upsell popup to avoid duplicated search logic.
	 *
	 * @param string $campaign_id The campaign flexible_id.
	 *
	 * @return array<string, mixed>|null The matching campaign or null.
	 */
	public function find_by_id( string $campaign_id ): ?array {
		foreach ( $this->campaigns as $campaign ) {
			if ( ( $campaign['flexible_id'] ?? '' ) === $campaign_id ) {
				return $campaign;
			}
		}

		return null;
	}

	/**
	 * Check if a campaign matches the product and user context.
	 *
	 * @param array<string, mixed> $campaign The campaign data.
	 * @param WC_Product           $product  The product.
	 *
	 * @return bool
	 */
	private function campaign_matches( array $campaign, $product ): bool {
		// Step 1: Check active status.
		if ( ( $campaign['campaign_status'] ?? 'active' ) !== 'active' ) {
			return false;
		}

		// Step 2: Check product targeting.
		if ( ! $this->matches_product_targeting( $campaign, $product ) ) {
			return false;
		}

		// Step 3: Check product exclusions.
		if ( $this->is_product_excluded( $campaign, $product ) ) {
			return false;
		}

		// Step 4: Check user condition.
		if ( function_exists( 'merchant_is_user_condition_passed' ) && ! merchant_is_user_condition_passed( $campaign ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if product matches the campaign's targeting rules.
	 *
	 * @param array<string, mixed> $campaign The campaign data.
	 * @param WC_Product           $product  The product.
	 *
	 * @return bool
	 */
	private function matches_product_targeting( array $campaign, $product ): bool {
		$rule       = $campaign['rules_to_display'] ?? 'all';
		$product_id = $product->get_id();

		switch ( $rule ) {
			case 'all':
				return true;

			case 'products':
				$ids = array_map( 'intval', (array) ( $campaign['product_ids'] ?? array() ) );
				return in_array( $product_id, $ids, true );

			case 'categories':
				$slugs = (array) ( $campaign['category_slugs'] ?? array() );
				return ! empty( $slugs ) && has_term( $slugs, 'product_cat', $product_id );

			case 'tags':
				$slugs = (array) ( $campaign['tag_slugs'] ?? array() );
				return ! empty( $slugs ) && has_term( $slugs, 'product_tag', $product_id );

			case 'brands':
				$slugs = (array) ( $campaign['brand_slugs'] ?? array() );
				return ! empty( $slugs ) && has_term( $slugs, 'product_brand', $product_id );

			default:
				return false;
		}
	}

	/**
	 * Check if product is excluded by campaign exclusion rules.
	 *
	 * @param array<string, mixed> $campaign The campaign data.
	 * @param WC_Product           $product  The product.
	 *
	 * @return bool
	 */
	private function is_product_excluded( array $campaign, $product ): bool {
		if ( function_exists( 'merchant_is_product_excluded' ) ) {
			$exclusion_data = array_merge( $campaign, array(
				'exclusion_enabled' => ! empty( $campaign['exclude_products_toggle'] )
					|| ! empty( $campaign['exclude_categories_toggle'] )
					|| ! empty( $campaign['exclude_tags_toggle'] )
					|| ! empty( $campaign['exclude_brands_toggle'] ),
			) );

			return merchant_is_product_excluded( $product->get_id(), $exclusion_data );
		}

		return false;
	}

	/**
	 * Generate cache key for a product + user combination.
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return string
	 */
	private function get_cache_key( $product ): string {
		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;

		return $product->get_id() . '_' . $user_id;
	}
}
