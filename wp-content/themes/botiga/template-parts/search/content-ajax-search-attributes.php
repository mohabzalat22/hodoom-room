<?php
/**
 * Template part for displaying ajax search attributes wrapper.
 *
 * @package Botiga\Templates
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$attributes = $args['attributes'];

do_action( 'botiga_before_ajax_search_attributes' );
?>

<div class="botiga-ajax-search-attributes">
	<?php 
	// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
	foreach ( $attributes as $taxonomy => $terms ) : 
	?>
		<?php $taxonomy_label = wc_attribute_label( $taxonomy ); ?>

		<h3 class="botiga-ajax-search__heading-subtitle">
			<?php echo esc_html( $taxonomy_label ); ?>
		</h3>

		<div class="botiga-ajax-search-attributes__terms">
			<?php
			foreach ( $terms as $term ) :
				botiga_get_template_part(
					'template-parts/search/content',
					'ajax-search-attributes-item',
					array(
						'taxonomy' => $taxonomy,
						'term'     => $term,
					)
				);
			endforeach;
			?>
		</div>
	<?php 
	endforeach; 
	// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
	?>
</div>

<?php
do_action( 'botiga_after_ajax_search_attributes' );