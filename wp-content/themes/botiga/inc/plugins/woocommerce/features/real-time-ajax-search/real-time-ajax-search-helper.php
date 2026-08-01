<?php
/**
 * Real Time Ajax Search
 *
 * @package Botiga
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Botiga_Real_Time_Ajax_Search_Helper {

	/**
	 * Get products (title bucket).
	 *
	 * @param array $data Ajax data.
	 *
	 * @return WP_Query
	 */
	public static function get_products( $data = array() ) {

		if ( empty( $data ) || empty( $data['search-term'] ) ) {
			return new WP_Query();
		}

		$query_args                              = self::get_base_query_args( $data );
		$query_args['botiga_search_term']         = (string) $data['search-term'];
		$query_args['botiga_ajax_title_bucket']   = true;

		// Title-only "word start" filter (scoped to this query only).
		add_filter( 'posts_where', array( __CLASS__, 'filter_ajax_search_title_word_start' ), 10, 2 );

		$qry = new WP_Query( $query_args );

		remove_filter( 'posts_where', array( __CLASS__, 'filter_ajax_search_title_word_start' ), 10 );

		// Enable search by SKU.
		if ( ! empty( $data['enable-search-by-sku'] ) ) {
			$qry->posts      = array_unique(
				array_merge(
					self::get_products_by_sku( $data ),
					$qry->posts
				),
				SORT_REGULAR
			);
			$qry->post_count = count( $qry->posts );
		}

		return $qry;
	}

	/**
	 * Get products by SKU.
	 *
	 * @param array $data Ajax data.
	 *
	 * @return array
	 */
	public static function get_products_by_sku( $data = array() ) {

		if ( empty( $data ) || empty( $data['search-term'] ) ) {
			return array();
		}

		$posts_per_page = ! empty( $data['posts-per-page'] ) ? absint( $data['posts-per-page'] ) : 15;
		$order          = ! empty( $data['order'] ) ? (string) $data['order'] : 'asc';
		$orderby        = ! empty( $data['orderby'] ) ? (string) $data['orderby'] : 'title';

		$args = array(
			'post_type'      => array( 'product', 'product_variation' ),
			'posts_per_page' => $posts_per_page,
			'order'          => $order,
			'orderby'        => $orderby,
			'post_status'    => array( 'publish' ),
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_sku',
					'value'   => (string) $data['search-term'],
					'compare' => 'LIKE',
				),
			),
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => array( 'exclude-from-search' ),
					'operator' => 'NOT IN',
				),
			),
		);

		if ( get_option( 'woocommerce_hide_out_of_stock_items' ) === 'yes' ) {
			$args['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => 'NOT LIKE',
			);
		}

		if ( 'price' === $orderby ) {
			$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = 'meta_value_num';
		}

		$qry_sku = new WP_Query( $args );

		return ! empty( $qry_sku->posts ) ? $qry_sku->posts : array();
	}

	/**
	 * Handle query post clauses (front-end search by SKU).
	 *
	 * @param array  $clauses Query clauses.
	 * @param object $query   Query object.
	 *
	 * @return array
	 */
	public static function set_query_post_clauses( $clauses, $query ) {
		global $wpdb;

		// Bail on YITH filters request.
		if ( isset( $_GET['yith_wcan'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $clauses;
		}

		// Bail if not applicable query.
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
			return $clauses;
		}

		if ( 'product' !== $query->get( 'post_type' ) ) {
			return $clauses;
		}

		$search_term = trim( (string) $query->get( 's' ) );

		if ( '' === $search_term ) {
			return $clauses;
		}

		$search_like    = '%' . $wpdb->esc_like( $search_term ) . '%';
		$existing_where = trim( $clauses['where'] );

		// Join SKU meta (for OR search).
		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS botiga_sku_pm ON ( {$wpdb->posts}.ID = botiga_sku_pm.post_id )";

		// Normalize existing WHERE (remove leading AND).
		if ( 0 === strpos( $existing_where, 'AND ' ) ) {
			$existing_where = substr( $existing_where, 4 );
		}

		// Build WHERE clause safely.
		if ( '' === $existing_where ) {
			$clauses['where'] = $wpdb->prepare(
				" AND ( botiga_sku_pm.meta_key = '_sku' AND botiga_sku_pm.meta_value LIKE %s )",
				$search_like
			);
		} else {
			$clauses['where'] = $wpdb->prepare(
				" AND ( ( {$existing_where} ) OR ( botiga_sku_pm.meta_key = '_sku' AND botiga_sku_pm.meta_value LIKE %s ) )", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$search_like
			);
		}

		// Exclude out of stock products.
		if ( 'yes' !== get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			return $clauses;
		}

		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS botiga_stock_pm ON ( {$wpdb->posts}.ID = botiga_stock_pm.post_id AND botiga_stock_pm.meta_key = '_stock_status' )";

		$clauses['where'] .= $wpdb->prepare(
			" AND ( botiga_stock_pm.meta_value IS NULL OR botiga_stock_pm.meta_value != %s )",
			'outofstock'
		);

		return $clauses;
	}

	/**
	 * Get products (Search + Refine).
	 *
	 * @param array $data Ajax data.
	 *
	 * @return WP_Query
	 */
	public static function get_products_search_and_refine( $data = array() ) {

		if ( empty( $data ) || empty( $data['search-term'] ) ) {
			return new WP_Query();
		}

		$title_query = self::get_products( $data );
		$title_ids   = ! empty( $title_query->posts ) ? wp_list_pluck( $title_query->posts, 'ID' ) : array();

		$taxonomy_ids = self::get_products_by_taxonomy_refine_ids( $data, $title_ids );

		if ( empty( $taxonomy_ids ) ) {
			return $title_query;
		}

		$limit      = ! empty( $data['posts-per-page'] ) ? absint( $data['posts-per-page'] ) : 15;
		$merged_ids = array();

		foreach ( $title_ids as $id ) {
			if ( count( $merged_ids ) >= $limit ) {
				break;
			}

			$merged_ids[] = absint( $id );
		}

		foreach ( $taxonomy_ids as $id ) {
			if ( count( $merged_ids ) >= $limit ) {
				break;
			}

			$id = absint( $id );

			if ( ! in_array( $id, $merged_ids, true ) ) {
				$merged_ids[] = $id;
			}
		}

		if ( empty( $merged_ids ) ) {
			return $title_query;
		}

		$args = self::get_base_query_args( $data );

		$args['post__in']       = $merged_ids; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostIn
		$args['posts_per_page'] = count( $merged_ids );
		$args['orderby']        = 'post__in';
		$args['no_found_rows']  = true;

		return new WP_Query( $args );
	}

	/**
	 * Get product IDs matched by taxonomy refine logic in strict bucket order:
	 * categories -> tags -> attributes.
	 *
	 * @param array $data        Ajax data.
	 * @param array $exclude_ids Exclude IDs.
	 *
	 * @return array
	 */
	protected static function get_products_by_taxonomy_refine_ids( $data, $exclude_ids = array() ) {

		$ids = array();

		$cat_ids     = self::get_products_by_single_taxonomy_match( $data, 'product_cat', $exclude_ids );
		$ids         = array_merge( $ids, $cat_ids );
		$exclude_ids = array_merge( $exclude_ids, $cat_ids );

		$tag_ids     = self::get_products_by_single_taxonomy_match( $data, 'product_tag', $exclude_ids );
		$ids         = array_merge( $ids, $tag_ids );
		$exclude_ids = array_merge( $exclude_ids, $tag_ids );

		if ( empty( $data['show-attributes'] ) || empty( $data['selected-attributes'] ) || ! is_array( $data['selected-attributes'] ) ) {
			return array_values( array_unique( $ids ) );
		}

		foreach ( $data['selected-attributes'] as $taxonomy ) {
			$taxonomy = sanitize_key( (string) $taxonomy );

			if ( empty( $taxonomy ) || 0 !== strpos( $taxonomy, 'pa_' ) || ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$attr_ids = self::get_products_by_single_taxonomy_match( $data, $taxonomy, $exclude_ids );

			if ( empty( $attr_ids ) ) {
				continue;
			}

			$ids         = array_merge( $ids, $attr_ids );
			$exclude_ids = array_merge( $exclude_ids, $attr_ids );
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Get product IDs for a taxonomy whose term name matches the search term.
	 *
	 * @param array  $data        Ajax data.
	 * @param string $taxonomy    Taxonomy.
	 * @param array  $exclude_ids Exclude IDs.
	 *
	 * @return array
	 */
	protected static function get_products_by_single_taxonomy_match( $data, $taxonomy, $exclude_ids = array() ) {

		if ( 'product_cat' === $taxonomy && empty( $data['show-categories'] ) ) {
			return array();
		}

		if ( 'product_tag' === $taxonomy && empty( $data['show-tags'] ) ) {
			return array();
		}

		$term_ids = self::get_term_ids_by_name_like( $taxonomy, (string) $data['search-term'] );

		if ( empty( $term_ids ) ) {
			return array();
		}

		$args = self::get_base_query_args( $data );

		// Refine queries must not be restricted by the original text search.
		unset( $args['s'] );

		$args['posts_per_page'] = ! empty( $data['posts-per-page'] ) ? absint( $data['posts-per-page'] ) : 15;
		$args['no_found_rows']  = true;

		if ( ! empty( $exclude_ids ) ) {
			$args['post__not_in'] = array_map( 'absint', $exclude_ids ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn
		}

		$args['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => $term_ids,
			'operator' => 'IN',
		);

		$q = new WP_Query( $args );

		return ! empty( $q->posts ) ? wp_list_pluck( $q->posts, 'ID' ) : array();
	}

	/**
	 * Get common query args for product queries.
	 *
	 * @param array $data Ajax data.
	 *
	 * @return array
	 */
	protected static function get_base_query_args( $data ) {

		$posts_per_page = ! empty( $data['posts-per-page'] ) ? absint( $data['posts-per-page'] ) : 15;
		$order          = ! empty( $data['order'] ) ? (string) $data['order'] : 'asc';
		$orderby        = ! empty( $data['orderby'] ) ? (string) $data['orderby'] : 'title';

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => $posts_per_page,
			'order'          => $order,
			'orderby'        => $orderby,
			'post_status'    => array( 'publish' ),
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => array( 'exclude-from-search' ),
					'operator' => 'NOT IN',
				),
			),
		);

		if ( get_option( 'woocommerce_hide_out_of_stock_items' ) === 'yes' ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => 'NOT LIKE',
				),
			);
		}

		if ( 'price' === $orderby ) {
			$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['orderby']  = 'meta_value_num';
		}

		return $args;
	}

	/**
	 * Get term IDs matching a search term for a taxonomy.
	 *
	 * @param string $taxonomy    Taxonomy name.
	 * @param string $search_term Search term.
	 *
	 * @return array
	 */
	protected static function get_term_ids_by_name_like( $taxonomy, $search_term ) {

		if ( empty( $taxonomy ) || empty( $search_term ) || ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'name__like' => $search_term,
				'fields'     => 'ids',
				'number'     => 10,
				'hide_empty' => true,
			)
		);

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		return array_map( 'absint', (array) $terms );
	}

	/**
	 * Title-only "word start" filter for the title bucket query.
	 *
	 * @param string   $where SQL WHERE clause.
	 * @param WP_Query $query Query object.
	 *
	 * @return string
	 */
	public static function filter_ajax_search_title_word_start( $where, $query ) {
		global $wpdb;

		if ( empty( $query->query_vars['botiga_ajax_title_bucket'] ) ) {
			return $where;
		}

		if ( empty( $query->query_vars['post_type'] ) || 'product' !== $query->query_vars['post_type'] ) {
			return $where;
		}

		if ( empty( $query->query_vars['botiga_search_term'] ) ) {
			return $where;
		}

		$term = trim( (string) $query->query_vars['botiga_search_term'] );

		if ( '' === $term ) {
			return $where;
		}

		$like = $wpdb->esc_like( $term );

		$where .= $wpdb->prepare(
			" AND ( {$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_title LIKE %s )",
			$like . '%',
			'% ' . $like . '%',
			'%-' . $like . '%'
		);

		return $where;
	}
	
	/**
	 * Get popular products fallback query.
	 *
	 * @param array $data Ajax data.
	 *
	 * @return WP_Query
	 */
	public static function get_popular_products( $data = array() ) {

		$limit = ! empty( $data['posts-per-page'] ) ? absint( $data['posts-per-page'] ) : 15;

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => $limit,
			'post_status'    => array( 'publish' ),
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'meta_key'       => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => array( 'exclude-from-search' ),
					'operator' => 'NOT IN',
				),
			),
		);

		if ( get_option( 'woocommerce_hide_out_of_stock_items' ) === 'yes' ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => 'NOT LIKE',
				),
			);
		}

		return new WP_Query( $args );
	}
}