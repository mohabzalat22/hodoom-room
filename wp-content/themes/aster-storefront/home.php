<?php

/**
 * The main template file
 * 
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package aster_storefront
 */

get_header();

$aster_storefront_column = get_theme_mod( 'aster_storefront_archive_column_layout', 'column-1' );
?>
<main id="primary" class="site-main">

	<?php

	if ( have_posts() ) :

		if ( is_home() && ! is_front_page() ) :
		endif;
		?>

		<div class="aster_storefront-archive-layout grid-layout <?php echo esc_attr( $aster_storefront_column ); ?>">
			<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post();

			get_template_part( 'template-parts/content', get_post_format() );

			endwhile;
			?>
		</div>
		<?php
		do_action( 'aster_storefront_posts_pagination' );

	else :

		get_template_part( 'template-parts/content', 'none' );

	endif;
	?>

</main>

<?php
if ( aster_storefront_is_sidebar_enabled() ) {
	get_sidebar();
}

get_footer();