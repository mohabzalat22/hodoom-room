<?php
/**
 * Title: Footer
 * Slug: wild-lemon/footer
 * Categories: wild-lemon
 * Block Types: core/template-part/footer
 * Description: Full site footer with the site title, tagline, link columns, and credit line.
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:group {"align":"full","className":"wl-footer","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|60"},"blockGap":"var:preset|spacing|50"},"elements":{"link":{"color":{"text":"#DDD7C9"}}}},"backgroundColor":"contrast","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull wl-footer has-base-color has-contrast-background-color has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|60"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:group {"className":"wl-footer-brand","style":{"spacing":{"blockGap":"14px"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group wl-footer-brand">
			<!-- wp:site-title {"level":0,"className":"wl-logo","style":{"typography":{"fontSize":"26px"}}} /-->

			<!-- wp:site-tagline {"style":{"color":{"text":"#B5AE9E"},"typography":{"lineHeight":"1.6"}},"fontSize":"small"} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|70"}},"layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"top"}} -->
		<div class="wp-block-group">
			<!-- wp:group {"style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group">
				<!-- wp:paragraph {"style":{"color":{"text":"#B5AE9E"},"typography":{"fontWeight":"600","letterSpacing":"0.12em","textTransform":"uppercase"}},"fontSize":"x-small"} -->
				<p class="has-text-color has-x-small-font-size" style="color:#B5AE9E;font-weight:600;letter-spacing:0.12em;text-transform:uppercase"><?php esc_html_e( 'Read', 'wild-lemon' ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:categories {"showHierarchy":false,"showPostCounts":false,"className":"wl-footer-list"} /-->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group">
				<!-- wp:paragraph {"style":{"color":{"text":"#B5AE9E"},"typography":{"fontWeight":"600","letterSpacing":"0.12em","textTransform":"uppercase"}},"fontSize":"x-small"} -->
				<p class="has-text-color has-x-small-font-size" style="color:#B5AE9E;font-weight:600;letter-spacing:0.12em;text-transform:uppercase"><?php esc_html_e( 'Elsewhere', 'wild-lemon' ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:page-list {"className":"wl-footer-list"} /-->

				<!-- wp:paragraph {"fontSize":"small"} -->
				<p class="has-small-font-size"><a href="<?php echo esc_url( get_feed_link() ); ?>"><?php esc_html_e( 'RSS', 'wild-lemon' ); ?></a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|40"}},"border":{"top":{"color":"#3A352C","width":"1px"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide" style="border-top-color:#3A352C;border-top-width:1px;padding-top:var(--wp--preset--spacing--40)">
		<!-- wp:paragraph {"style":{"color":{"text":"#B5AE9E"},"typography":{"fontSize":"13px"}}} -->
		<p class="has-text-color" style="color:#B5AE9E;font-size:13px">&copy;&nbsp;<?php echo esc_html( date_i18n( 'Y' ) ); ?>&nbsp;<?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"style":{"color":{"text":"#B5AE9E"},"typography":{"fontSize":"13px"}}} -->
		<p class="has-text-color" style="color:#B5AE9E;font-size:13px">
			<?php
			$wild_lemon_wordpress_link = '<a href="' . esc_url( __( 'https://wordpress.org', 'wild-lemon' ) ) . '" rel="nofollow">WordPress</a>';
			/* translators: %s: WordPress link. */
			echo wp_kses_post( sprintf( __( 'Proudly powered by %s', 'wild-lemon' ), $wild_lemon_wordpress_link ) );
			?>
		</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
