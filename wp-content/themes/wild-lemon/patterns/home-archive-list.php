<?php
/**
 * Title: From the Archive
 * Slug: wild-lemon/home-archive-list
 * Categories: wild-lemon, query
 * Block Types: core/query
 * Description: Text-forward list of older posts with date, title, and category.
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|60","bottom":"var:preset|spacing|70","left":"var:preset|spacing|60"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"align":"wide","style":{"typography":{"fontSize":"32px"}}} -->
	<h2 class="wp-block-heading alignwide" style="font-size:32px"><?php esc_html_e( 'From the archive', 'wild-lemon' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:query {"queryId":11,"query":{"perPage":4,"pages":1,"offset":4,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"align":"wide","className":"wl-archive-list"} -->
	<div class="wp-block-query alignwide wl-archive-list">
		<!-- wp:post-template {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
			<!-- wp:group {"className":"wl-row-link","style":{"spacing":{"padding":{"top":"22px","bottom":"22px"}},"border":{"top":{"color":"var:preset|color|line","width":"1px"}}},"layout":{"type":"default"}} -->
			<div class="wp-block-group wl-row-link" style="border-top-color:var(--wp--preset--color--line);border-top-width:1px;padding-top:22px;padding-bottom:22px">
				<!-- wp:post-date {"style":{"typography":{"fontSize":"13px"}},"textColor":"muted-2"} /-->

				<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontSize":"23px","lineHeight":"1.3"}}} /-->

				<!-- wp:post-terms {"term":"category","style":{"typography":{"fontWeight":"600","letterSpacing":"0.1em","textTransform":"uppercase"},"elements":{"link":{"color":{"text":"var:preset|color|accent-3"},"typography":{"textDecoration":"none"}}}},"fontSize":"x-small"} /-->
			</div>
			<!-- /wp:group -->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->
</div>
<!-- /wp:group -->
