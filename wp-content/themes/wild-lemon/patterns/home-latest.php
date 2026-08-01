<?php
/**
 * Title: Latest Posts Grid
 * Slug: wild-lemon/home-latest
 * Categories: wild-lemon, query
 * Block Types: core/query
 * Description: Three-column grid of recent posts with a section heading and an all-posts link.
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|60"},"blockGap":"var:preset|spacing|50"},"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:heading {"style":{"typography":{"fontSize":"32px"}}} -->
		<h2 class="wp-block-heading" style="font-size:32px"><?php esc_html_e( 'Latest from the garden', 'wild-lemon' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}},"fontSize":"small"} -->
		<p class="has-small-font-size" style="font-weight:600"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'All posts', 'wild-lemon' ); ?></a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":10,"query":{"perPage":3,"pages":1,"offset":1,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"align":"wide"} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":3}} -->
			<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group">
				<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"3/2","style":{"border":{"radius":"12px"}}} /-->

				<!-- wp:group {"style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"default"}} -->
				<div class="wp-block-group">
					<!-- wp:post-terms {"term":"category","style":{"typography":{"fontWeight":"600","letterSpacing":"0.1em","textTransform":"uppercase"},"elements":{"link":{"color":{"text":"var:preset|color|accent-3"},"typography":{"textDecoration":"none"}}}},"fontSize":"x-small"} /-->

					<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontSize":"24px","lineHeight":"1.25"}}} /-->

					<!-- wp:post-excerpt {"excerptLength":20,"style":{"typography":{"fontSize":"15px","lineHeight":"1.55"}},"textColor":"muted"} /-->

					<!-- wp:post-date {"style":{"typography":{"fontSize":"13px"}},"textColor":"muted-2"} /-->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->
</div>
<!-- /wp:group -->
