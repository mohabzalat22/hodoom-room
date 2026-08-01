<?php
/**
 * Real Time Ajax Search
 *
 * @package Botiga
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Include the helper class.
require get_template_directory() . '/inc/plugins/woocommerce/features/real-time-ajax-search/real-time-ajax-search-helper.php';

/**
 * Real Time Ajax Search
 * 
 */
class Botiga_Real_Time_Ajax_Search {
	
	/**
	 * Cached suggestions availability flag.
	 *
	 * @var bool|null
	 */
	protected $has_suggestions_cache = null;

	/**
	 * Constructor.
	 * 
	 */
	public function __construct() {
		$ajax_search = get_theme_mod( 'shop_search_enable_ajax', 0 );
		if ( $ajax_search ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 11 );

			add_action( 'botiga_shop_ajax_search_products_loop_start', array( $this, 'products_loop_wrapper_open' ), 10, 2 );
			add_action( 'botiga_shop_ajax_search_products_loop_end', array( $this, 'products_loop_wrapper_close' ), 10, 2 );
			add_action( 'botiga_shop_ajax_search_products_loop_end', array( $this, 'see_all_button' ), 15, 2 );
			add_action( 'botiga_shop_ajax_search_after_products_loop', array( $this, 'suggestions_loop_wrapper_open' ), 5, 2 );
			add_action( 'botiga_shop_ajax_search_after_products_loop', array( $this, 'categories' ), 10, 2 );
			add_action( 'botiga_shop_ajax_search_after_products_loop', array( $this, 'tags' ), 15, 2 );
			add_action( 'botiga_shop_ajax_search_after_products_loop', array( $this, 'attributes' ), 20, 2 );
			add_action( 'botiga_shop_ajax_search_after_products_loop', array( $this, 'suggestions_loop_wrapper_close' ), 25, 2 );

			add_action('wp_ajax_botiga_ajax_search_callback', array( $this, 'ajax_callback' ) );
			add_action('wp_ajax_nopriv_botiga_ajax_search_callback', array( $this, 'ajax_callback' ) );

			add_filter( 'botiga_custom_css_output', array( $this, 'custom_css' ) );
		}

		$enable_search_by_sku = get_theme_mod( 'shop_search_ajax_enable_search_by_sku', 0 );
		if( $enable_search_by_sku ) {
			add_filter( 'posts_clauses', array( 'Botiga_Real_Time_Ajax_Search_Helper', 'set_query_post_clauses' ), 10, 2 );
		}
	}

	/**
	 * Enqueue scripts and styles.
	 * 
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_register_script( 'botiga-ajax-search', get_template_directory_uri() . '/assets/js/botiga-ajax-search.min.js', array( 'jquery' ), BOTIGA_VERSION, true );
		wp_enqueue_script( 'botiga-ajax-search' );
		wp_localize_script( 'botiga-ajax-search', 'botiga_ajax_search', array( 'nonce' => wp_create_nonce( 'botiga-ajax-search-random-nonce' ) ) );
	}

	/**
	 * Products loop wrapper open.
	 * 
	 * @return void
	 */
	public function products_loop_wrapper_open( $query, $data ) {
		
		if ( ! empty( $data['is_popular_fallback'] ) ) {
			botiga_get_template_part( 'template-parts/search/content', 'ajax-search-loop-start-popular' );
			return;
		}

		botiga_get_template_part( 'template-parts/search/content', 'ajax-search-loop-start' );
	}

	/**
	 * Products loop wrapper close.
	 * 
	 * @return void
	 */
	public function products_loop_wrapper_close( $query, $data ) {
		
		if ( ! empty( $data['is_popular_fallback'] ) ) {
			botiga_get_template_part( 'template-parts/search/content', 'ajax-search-loop-end-popular' );
			return;
		}

		botiga_get_template_part( 'template-parts/search/content', 'ajax-search-loop-end' );
	}

	/**
	 * See All Button.
	 * 
	 * @return void
	 */
	public function see_all_button( $query, $data ) {
	
		if ( ! empty( $data['is_popular_fallback'] ) ) {
			return;
		}
					
		$see_all_button = get_theme_mod( 'shop_search_ajax_display_see_all', 0 );
		if( ! $see_all_button ) {
			return;
		}

		$search_link_mounted = add_query_arg( 'post_type', 'product', get_search_link( $data['search-term'] ) );

		botiga_get_template_part( 'template-parts/search/content', 'ajax-search-see-all-button', array( 'search_link_mounted' => $search_link_mounted, 'query' => $query ) );
	}

	/**
	 * Categories.
	 * 
	 * @return void
	 */
	public function categories( $query, $data ) {
		
		if ( empty( $data['show-categories'] ) ) {
			return;
		}

		if ( empty( $data['search-term'] ) ) {
			return;
		}

		$terms = $this->get_matching_terms( 'product_cat', $data['search-term'] );

		if ( empty( $terms ) ) {
			return;
		}

		botiga_get_template_part( 'template-parts/search/content', 'ajax-search-categories', array( 'terms' => $terms ) );
	}
	
	/**
	 * Suggestions loop wrapper open.
	 *
	 * @param WP_Query $query Query object.
	 * @param array    $data  Ajax data.
	 *
	 * @return void
	 */
	public function suggestions_loop_wrapper_open( $query, $data ) {
	
		if ( ! $this->has_suggestions_cached( $data ) ) {
			return;
		}
		
		botiga_get_template_part( 'template-parts/search/content', 'ajax-search-suggestions-start' );
	}
	
	/**
	 * Suggestions loop wrapper close.
	 *
	 * @param WP_Query $query Query object.
	 * @param array    $data  Ajax data.
	 *
	 * @return void
	 */
	public function suggestions_loop_wrapper_close( $query, $data ) {
	
		if ( ! $this->has_suggestions_cached( $data ) ) {
			return;
		}
		
		botiga_get_template_part( 'template-parts/search/content', 'ajax-search-suggestions-end' );
	}
	
	/**
	 * Tags.
	 *
	 * @param WP_Query $query Query object.
	 * @param array    $data  Ajax data.
	 *
	 * @return void
	 */
	public function tags( $query, $data ) {
		
		if ( empty( $data['show-tags'] ) ) {
			return;
		}

		if ( empty( $data['search-term'] ) ) {
			return;
		}

		$terms = $this->get_matching_terms( 'product_tag', $data['search-term'] );

		if ( empty( $terms ) ) {
			return;
		}

		botiga_get_template_part( 'template-parts/search/content', 'ajax-search-tags', array( 'terms' => $terms ) );
	}
	
	/**
	 * Attributes.
	 *
	 * @param WP_Query $query Query object.
	 * @param array    $data  Ajax data.
	 *
	 * @return void
	 */
	public function attributes( $query, $data ) {

		if ( empty( $data['show-attributes'] ) ) {
			return;
		}

		if ( empty( $data['search-term'] ) ) {
			return;
		}

		if ( empty( $data['selected-attributes'] ) || ! is_array( $data['selected-attributes'] ) ) {
			return;
		}

		$matched_attributes = array();

		foreach ( $data['selected-attributes'] as $taxonomy ) {
			$taxonomy = sanitize_key( (string) $taxonomy );

			if ( empty( $taxonomy ) ) {
				continue;
			}

			if ( 0 !== strpos( $taxonomy, 'pa_' ) ) {
				continue;
			}

			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$terms = $this->get_matching_terms( $taxonomy, $data['search-term'] );

			if ( empty( $terms ) ) {
				continue;
			}

			$matched_attributes[ $taxonomy ] = $terms;
		}

		if ( empty( $matched_attributes ) ) {
			return;
		}

		botiga_get_template_part(
			'template-parts/search/content',
			'ajax-search-attributes',
			array(
				'attributes' => $matched_attributes,
			)
		);
	}

	/**
	 * Ajax Search Callback.
	 * 
	 * @return void
	 */
	public function ajax_callback() {
		check_ajax_referer( 'botiga-ajax-search-random-nonce', 'nonce' );

		$this->has_suggestions_cache = null;
		
		$data = array();

		/**
		 * Hook 'botiga_ajax_search_search_term'
		 *
		 * @since 1.0.0
		 */
		$data['search-term'] = isset( $_POST['search_term'] )
			? sanitize_text_field( apply_filters( 'botiga_ajax_search_search_term', sanitize_text_field( wp_unslash( $_POST['search_term'] ) ) ) )
			: '';

		/**
		 * Hook 'botiga_shop_ajax_search_posts_per_page'
		 *
		 * @since 1.0.0
		 */
		$data['posts-per-page'] = absint(
			apply_filters( 'botiga_shop_ajax_search_posts_per_page', get_theme_mod( 'shop_search_ajax_posts_per_page', 15 ) )
		);

		/**
		 * Hook 'botiga_shop_ajax_search_order'
		 *
		 * @since 1.0.0
		 */
		$data['order'] = (string) apply_filters( 'botiga_shop_ajax_search_order', get_theme_mod( 'shop_search_ajax_order', 'asc' ) );

		/**
		 * Hook 'botiga_shop_ajax_search_orderby'
		 *
		 * @since 1.0.0
		 */
		$data['orderby'] = (string) apply_filters( 'botiga_shop_ajax_search_orderby', get_theme_mod( 'shop_search_ajax_orderby', 'title' ) );

		/**
		 * Hook 'botiga_shop_ajax_search_enable_search_by_sku'
		 *
		 * @since 1.0.0
		 */
		$data['enable-search-by-sku'] = (bool) apply_filters(
			'botiga_shop_ajax_search_enable_search_by_sku',
			get_theme_mod( 'shop_search_ajax_enable_search_by_sku', 0 )
		);

		/**
		 * Hook 'botiga_shop_ajax_search_show_categories'
		 *
		 * @since 1.0.0
		 */
		$data['show-categories'] = (bool) apply_filters(
			'botiga_shop_ajax_search_show_categories',
			get_theme_mod( 'shop_search_ajax_show_categories', 1 )
		);

		/**
		* Hook 'botiga_shop_ajax_search_show_tags'
		*
		* @since 2.4.2
		*/
		$data['show-tags'] = (bool) apply_filters(
			'botiga_shop_ajax_search_show_tags',
			get_theme_mod( 'shop_search_ajax_show_tags', 1 )
		);
		
		/**
		* Hook 'botiga_shop_ajax_search_show_attributes'
		*
		* @since 2.4.2
		*/
		$data['show-attributes'] = (bool) apply_filters(
			'botiga_shop_ajax_search_show_attributes',
			get_theme_mod( 'shop_search_ajax_show_attributes', 1 )
		);
		
		/**
		* Hook 'botiga_shop_ajax_search_selected_attributes'
		*
		* @since 2.4.2
		*/
		$data['selected-attributes'] = apply_filters(
			'botiga_shop_ajax_search_selected_attributes',
			get_theme_mod( 'shop_search_ajax_attributes', '' )
		);
		
		/**
		 * Hook 'botiga_shop_search_enable_popular_products'
		 *
		 * @since 2.4.2
		 */
		$data['enable-popular-products'] = (bool) apply_filters(
			'botiga_shop_search_enable_popular_products',
			get_theme_mod( 'shop_search_enable_popular_products', 0 )
		);
		
		// Read request "type" for consistency (JS already sends it).
		$data['type'] = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'product';
		
		if ( 'product' !== $data['type'] ) {
			$data['type'] = 'product';
		}
			
		// Normalize selected attributes only when enabled.
		if ( empty( $data['show-attributes'] ) ) {
			$data['selected-attributes'] = array();
		} else {
			$selected_attributes = array();

			if ( ! empty( $data['selected-attributes'] ) ) {
				$raw_attributes = is_array( $data['selected-attributes'] )
					? $data['selected-attributes']
					: explode( ',', (string) $data['selected-attributes'] );

				foreach ( $raw_attributes as $taxonomy ) {
					$taxonomy = sanitize_key( trim( (string) $taxonomy ) );

					if ( empty( $taxonomy ) ) {
						continue;
					}

					if ( 0 !== strpos( $taxonomy, 'pa_' ) ) {
						continue;
					}

					if ( ! taxonomy_exists( $taxonomy ) ) {
						continue;
					}

					$selected_attributes[] = $taxonomy;
				}
			}

			$data['selected-attributes'] = array_values( array_unique( $selected_attributes ) );
		}

		$query = Botiga_Real_Time_Ajax_Search_Helper::get_products_search_and_refine( $data );

		ob_start();
		botiga_get_template_part(
			'template-parts/search/content',
			'ajax-search',
			array(
				'query' => $query,
				'data'  => $data,
			)
		);
		$output = ob_get_clean();

		if ( $query->have_posts() || $this->has_suggestions_cached( $data ) ) {
			wp_send_json(
				array(
					'status' => 'success',
					'output' => wp_kses_post( $output ),
				)
			);
		}
		
		// Popular products fallback (only when enabled).
		if ( ! empty( $data['enable-popular-products'] ) ) {
			$popular_query                 = Botiga_Real_Time_Ajax_Search_Helper::get_popular_products( $data );
			$data['is_popular_fallback']   = true;

			ob_start();
			botiga_get_template_part(
				'template-parts/search/content',
				'ajax-search-popular',
				array(
					'query' => $popular_query,
					'data'  => $data,
				)
			);
			$popular_output = ob_get_clean();

			if ( $popular_query->have_posts() ) {
				wp_send_json(
					array(
						'status' => 'success',
						'type'   => 'popular-products',
						'output' => wp_kses_post( $popular_output ),
					)
				);
			}
		}

		$output = '<p class="botiga-ajax-search__no-results">' . esc_html__( 'No products found.', 'botiga' ) . '</p>';

		wp_send_json(
			array(
				'status' => 'success',
				'type'   => 'no-results',
				'output' => wp_kses_post( $output ),
			)
		);
	}

	/**
	 * Custom CSS
	 * 
	 * @param string $css Custom CSS.
	 * 
	 * @return string
	 */
	public function custom_css( $css ) {
		$shop_ajax_search = get_theme_mod( 'shop_search_enable_ajax', 0 );

		if( ! $shop_ajax_search ) {
			return $css;
		}

		$css .= Botiga_Custom_CSS::get_border_color_rgba_css( 'color_body_text', '#212121', '.botiga-ajax-search__wrapper ,.botiga-ajax-search__item+.botiga-ajax-search__item:before', '0.1', true );
		$css .= Botiga_Custom_CSS::get_background_color_rgba_css( 'color_body_text', '#212121', '.botiga-ajax-search__divider', '0.1', true );

		return $css;
	}
	
	/**
	 * Get matching terms for a taxonomy.
	 *
	 * @param string $taxonomy    Taxonomy name.
	 * @param string $search_term Search term.
	 * @param int    $limit       Max terms.
	 *
	 * @return array
	 */
	protected function get_matching_terms( $taxonomy, $search_term, $limit = 6 ) {
		
		if ( empty( $taxonomy ) || empty( $search_term ) ) {
			return array();
		}

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		$args = array(
			'taxonomy'   => $taxonomy,
			'name__like' => $search_term,
			'number'     => absint( $limit ),
			'hide_empty' => true,
		);

		$terms = get_terms( $args );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		return $terms;
	}
	
	/**
	 * Get cached suggestions existence.
	 *
	 * @param array $data Ajax data.
	 *
	 * @return bool
	 */
	protected function has_suggestions_cached( $data ) {
		
		if ( null !== $this->has_suggestions_cache ) {
			return $this->has_suggestions_cache;
		}

		$this->has_suggestions_cache = $this->has_suggestions( $data );

		return $this->has_suggestions_cache;
	}
	
	/**
	 * Check if any suggestions exist for current request.
	 *
	 * @param array $data Ajax data.
	 *
	 * @return bool
	 */
	protected function has_suggestions( $data ) {
	
		if ( empty( $data['search-term'] ) ) {
			return false;
		}

		if ( ! empty( $data['show-categories'] ) ) {
			$terms = $this->get_matching_terms( 'product_cat', $data['search-term'], 1 );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				return true;
			}
		}

		if ( ! empty( $data['show-tags'] ) ) {
			$terms = $this->get_matching_terms( 'product_tag', $data['search-term'], 1 );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				return true;
			}
		}

		if ( empty( $data['show-attributes'] ) || empty( $data['selected-attributes'] ) || ! is_array( $data['selected-attributes'] ) ) {
			return false;
		}

		foreach ( $data['selected-attributes'] as $taxonomy ) {
			$taxonomy = sanitize_key( (string) $taxonomy );

			if ( empty( $taxonomy ) ) {
				continue;
			}

			if ( 0 !== strpos( $taxonomy, 'pa_' ) ) {
				continue;
			}

			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$terms = $this->get_matching_terms( $taxonomy, $data['search-term'], 1 );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				return true;
			}
		}

		return false;
	}
}

new Botiga_Real_Time_Ajax_Search();
