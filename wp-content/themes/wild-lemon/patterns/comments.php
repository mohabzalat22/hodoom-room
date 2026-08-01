<?php
/**
 * Title: Comments
 * Slug: wild-lemon/comments
 * Categories: wild-lemon, text
 * Block Types: core/comments
 * Description: Comments area with comments list, pagination, and comment form.
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:comments {"className":"wp-block-comments-query-loop","style":{"spacing":{"margin":{"top":"var:preset|spacing|70"}}}} -->
<div class="wp-block-comments wp-block-comments-query-loop" style="margin-top:var(--wp--preset--spacing--70)">
	<!-- wp:heading {"style":{"typography":{"fontSize":"32px"}}} -->
	<h2 class="wp-block-heading" style="font-size:32px"><?php esc_html_e( 'Comments', 'wild-lemon' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:comments-title {"level":3,"style":{"typography":{"fontSize":"20px"}}} /-->

	<!-- wp:comment-template -->
		<!-- wp:group {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|50"}}}} -->
		<div class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--50)">
			<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
			<div class="wp-block-group">
				<!-- wp:avatar {"size":48} /-->

				<!-- wp:group {"style":{"spacing":{"blockGap":"6px"}},"layout":{"type":"default"}} -->
				<div class="wp-block-group">
					<!-- wp:comment-author-name {"style":{"typography":{"fontWeight":"600"}},"fontSize":"small"} /-->

					<!-- wp:comment-date {"style":{"typography":{"fontSize":"13px"}},"textColor":"muted-2"} /-->

					<!-- wp:comment-content {"style":{"typography":{"fontSize":"16px","lineHeight":"1.6"}},"fontFamily":"newsreader"} /-->

					<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
					<div class="wp-block-group">
						<!-- wp:comment-edit-link {"fontSize":"x-small"} /-->

						<!-- wp:comment-reply-link {"fontSize":"x-small"} /-->
					</div>
					<!-- /wp:group -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	<!-- /wp:comment-template -->

	<!-- wp:comments-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"space-between"}} -->
		<!-- wp:comments-pagination-previous /-->

		<!-- wp:comments-pagination-next /-->
	<!-- /wp:comments-pagination -->

	<!-- wp:post-comments-form /-->
</div>
<!-- /wp:comments -->
