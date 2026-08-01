<?php
/**
 * Template part for displaying ajax search attribute term item content.
 *
 * @package Botiga\Templates
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

do_action( 'botiga_before_ajax_search_attributes_item' );

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
$term     = $args['term'];
$taxonomy = $args['taxonomy'];
// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

$shop_url       = wc_get_page_permalink( 'shop' );
$attribute_slug = str_replace( 'pa_', '', $taxonomy );
$filter_key     = 'filter_' . $attribute_slug;

$url = add_query_arg(
	array(
		'btsf-filter'                   => 1,
		$filter_key                     => $term->slug,
		'query_type_' . $attribute_slug => 'and',
	),
	$shop_url
);
?>

<a class="botiga-ajax-search__item botiga-ajax-search__item-attribute" href="<?php echo esc_url( $url ); ?>">
	<div class="botiga-ajax-search__item-info">
		<h3><?php echo esc_html( $term->name ); ?></h3>
	</div>
</a>

<?php
do_action( 'botiga_after_ajax_search_attributes_item' );