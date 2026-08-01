<?php
/**
 * Template part for displaying ajax search tag item content.
 *
 * @package Botiga\Templates
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

do_action( 'botiga_before_ajax_search_tags_item' );

$tag_item = $args['tag'];

$shop_url = wc_get_page_permalink( 'shop' );

$url = add_query_arg(
	array(
		'btsf-filter'    => 1,
		'product_tag'    => $tag_item->slug,
		'query_type_tag' => 'and',
	),
	$shop_url
);

?>

<a class="botiga-ajax-search__item botiga-ajax-search__item-tag" href="<?php echo esc_url( $url ); ?>">
	<div class="botiga-ajax-search__item-info">
		<h3><?php echo esc_html( $tag_item->name ); ?></h3>
	</div>
</a>

<?php
do_action( 'botiga_after_ajax_search_tags_item' );