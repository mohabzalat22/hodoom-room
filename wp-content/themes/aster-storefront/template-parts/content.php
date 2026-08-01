<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package aster_storefront
 */

?>

<?php $aster_storefront_readmore = get_theme_mod( 'aster_storefront_readmore_button_text','Read More');

// Get featured image dimension settings
$aster_storefront_featured_image_dimension = get_theme_mod('aster_storefront_blog_post_featured_image_dimension', 'default');
$aster_storefront_custom_width = get_theme_mod('aster_storefront_blog_post_featured_image_custom_width', '');
$aster_storefront_custom_height = get_theme_mod('aster_storefront_blog_post_featured_image_custom_height', '');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="mag-post-single wow zoomIn delay-1000">
		<div class="mag-post-img">
			<?php
				// Check if "Custom Image Size" is selected
				if ($aster_storefront_featured_image_dimension === 'custom' && !empty($aster_storefront_custom_width) && !empty($aster_storefront_custom_height)) {
					echo '<img src="' . esc_url(get_the_post_thumbnail_url()) . '" style="width: ' . esc_attr($aster_storefront_custom_width) . 'px !important; height: ' . esc_attr($aster_storefront_custom_height) . 'px !important; object-fit: cover;">';
				} else {
					aster_storefront_post_thumbnail();
				}
            ?>
		</div>
		<div class="mag-post-detail">
			<div class="mag-post-category">
				<?php aster_storefront_categories_list(); ?>
			</div>
			<?php
			if ( is_singular() ) :
				the_title( '<h1 class="entry-title mag-post-title">', '</h1>' );
			else :
				if ( get_theme_mod( 'aster_storefront_post_hide_post_heading', true ) ) { 
					the_title( '<h2 class="entry-title mag-post-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			    }
			endif;
			?>
			<div class="mag-post-meta">
				<?php
				aster_storefront_posted_on();
				aster_storefront_posted_by();
				aster_storefront_posted_comments();
				aster_storefront_posted_time();
				?>
			</div>
			<?php if ( get_theme_mod( 'aster_storefront_post_hide_post_content', true ) ) { ?>
				<div class="mag-post-excerpt">
					<?php the_excerpt(); ?>
				</div>
		    <?php } ?>
			<?php if ( get_theme_mod( 'aster_storefront_post_readmore_button', true ) === true ) : ?>
				<div class="mag-post-read-more">
					<a href="<?php the_permalink(); ?>" class="read-more-button">
						<?php if ( ! empty( $aster_storefront_readmore ) ) { ?> <?php echo esc_html( $aster_storefront_readmore ); ?> <?php } ?>
						<i class="<?php echo esc_attr( get_theme_mod( 'aster_storefront_readmore_btn_icon', 'fas fa-chevron-right' ) ); ?>"></i>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>

</article><!-- #post-<?php the_ID(); ?> -->