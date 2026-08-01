<?php
/**
 * Title: Footer Compact
 * Slug: wild-lemon/footer-compact
 * Categories: wild-lemon
 * Block Types: core/template-part/footer
 * Description: Compact single-row site footer with the site title and credit line.
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:group {"align":"full","className":"wl-footer","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|60","bottom":"var:preset|spacing|40","left":"var:preset|spacing|60"}},"elements":{"link":{"color":{"text":"#DDD7C9"}}}},"backgroundColor":"contrast","textColor":"base","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group alignfull wl-footer has-base-color has-contrast-background-color has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:site-title {"level":0,"className":"wl-logo","style":{"typography":{"fontSize":"20px"}}} /-->

	<!-- wp:paragraph {"style":{"color":{"text":"#B5AE9E"},"typography":{"fontSize":"13px"}}} -->
	<p class="has-text-color" style="color:#B5AE9E;font-size:13px">
		<?php
		$wild_lemon_wordpress_link = '<a href="' . esc_url( __( 'https://wordpress.org', 'wild-lemon' ) ) . '" rel="nofollow">WordPress</a>';
		echo esc_html( '© ' . date_i18n( 'Y' ) . ' ' . get_bloginfo( 'name' ) ) . ' · ';
		/* translators: %s: WordPress link. */
		echo wp_kses_post( sprintf( __( 'Proudly powered by %s', 'wild-lemon' ), $wild_lemon_wordpress_link ) );
		?>
	</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
