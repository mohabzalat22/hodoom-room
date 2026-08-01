<?php
/**
 * Title: Post List Rows
 * Slug: wild-lemon/query-list
 * Categories: wild-lemon, query
 * Block Types: core/query
 * Description: Media rows of posts with thumbnail, meta, title, and excerpt, plus pagination.
 * Inserter: no
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|60"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:query {"queryId":20,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"align":"wide"} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
			<!-- wp:group {"className":"wl-media-row","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}},"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}}},"layout":{"type":"default"}} -->
			<div class="wp-block-group wl-media-row" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
				<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"3/2","style":{"border":{"radius":"10px"}}} /-->

				<!-- wp:group {"style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"default"}} -->
				<div class="wp-block-group">
					<!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
					<div class="wp-block-group">
						<!-- wp:post-terms {"term":"category","style":{"typography":{"fontWeight":"600","letterSpacing":"0.1em","textTransform":"uppercase"},"elements":{"link":{"color":{"text":"var:preset|color|muted-2"},"typography":{"textDecoration":"none"}}}},"textColor":"muted-2","fontSize":"x-small"} /-->

						<!-- wp:post-date {"style":{"typography":{"fontWeight":"600","letterSpacing":"0.1em","textTransform":"uppercase"}},"textColor":"muted-2","fontSize":"x-small"} /-->
					</div>
					<!-- /wp:group -->

					<!-- wp:post-title {"isLink":true,"style":{"typography":{"fontSize":"28px","lineHeight":"1.2"}}} /-->

					<!-- wp:post-excerpt {"excerptLength":25,"style":{"typography":{"fontSize":"15px","lineHeight":"1.55"}},"textColor":"muted"} /-->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		<!-- /wp:post-template -->

		<!-- wp:query-pagination {"paginationArrow":"arrow","className":"wl-pagination","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
			<!-- wp:query-pagination-previous /-->

			<!-- wp:query-pagination-numbers /-->

			<!-- wp:query-pagination-next /-->
		<!-- /wp:query-pagination -->

		<!-- wp:query-no-results -->
			<!-- wp:pattern {"slug":"wild-lemon/hidden-no-results"} /-->
		<!-- /wp:query-no-results -->
	</div>
	<!-- /wp:query -->
</div>
<!-- /wp:group -->
