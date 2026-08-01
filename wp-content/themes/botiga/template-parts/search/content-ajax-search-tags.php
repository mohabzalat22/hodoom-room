<?php
/**
 * Template part for displaying ajax search tags wrapper.
 *
 * @package Botiga\Templates
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$terms = $args['terms'];

do_action( 'botiga_before_ajax_search_tags' );
?>

<h3 class="botiga-ajax-search__heading-subtitle"><?php echo esc_html__( 'Tags', 'botiga' ); ?></h3>

<div class="botiga-ajax-search-tags">
	<?php
	// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
	foreach ( $terms as $tag ) :
		botiga_get_template_part(
			'template-parts/search/content',
			'ajax-search-tags-item',
			array(
				'tag' => $tag,
			)
		);
	endforeach;
	// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
	?>
</div>

<?php
do_action( 'botiga_after_ajax_search_tags' );