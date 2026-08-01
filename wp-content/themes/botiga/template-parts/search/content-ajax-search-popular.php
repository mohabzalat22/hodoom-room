<?php
/**
 * Template part for displaying ajax search popular products fallback.
 *
 * @package Botiga\Templates
 *
 * @since 2.4.2
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$query = $args['query'];
$data  = $args['data'];
?>

<p class="botiga-ajax-search__no-results botiga-ajax-search__no-results--with-popular">
	<?php echo esc_html__( 'No products found. Showing popular products instead.', 'botiga' ); ?>
</p>
<br/>

<?php
/**
 * Hook 'botiga_shop_ajax_search_before_products_loop'
 *
 * Reuse the same hooks so the wrapper markup stays consistent.
 *
 * @param WP_Query $query Query object.
 * @param array    $data  Ajax data.
 *
 * @since 2.4.2
 */
do_action( 'botiga_shop_ajax_search_before_products_loop', $query, $data );

if ( $query->have_posts() ) :

	/**
	 * Hook 'botiga_shop_ajax_search_products_loop_start'
	 *
	 * @param WP_Query $query Query object.
	 * @param array    $data  Ajax data.
	 *
	 * @since 2.4.2
	 */
	do_action( 'botiga_shop_ajax_search_products_loop_start', $query, $data );

	while ( $query->have_posts() ) :
		$query->the_post();

		$_post = get_post();

		botiga_get_template_part( 'template-parts/search/content', 'ajax-search-item', array( 'post_id' => $_post->ID ) );
	endwhile;

	/**
	 * Hook 'botiga_shop_ajax_search_products_loop_end'
	 *
	 * @param WP_Query $query Query object.
	 * @param array    $data  Ajax data.
	 *
	 * @since 2.4.2
	 */
	do_action( 'botiga_shop_ajax_search_products_loop_end', $query, $data );

endif;

/**
 * Hook 'botiga_shop_ajax_search_after_products_loop'
 *
 * @param WP_Query $query Query object.
 * @param array    $data  Ajax data.
 *
 * @since 2.4.2
 */
do_action( 'botiga_shop_ajax_search_after_products_loop', $query, $data );