<?php
/**
 * Title: Author Header
 * Slug: wild-lemon/author-header
 * Categories: wild-lemon
 * Description: Author archive header with avatar, name, and biography.
 * Inserter: no
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","right":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|60"}},"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:avatar {"size":120} /-->

		<!-- wp:group {"style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group">
			<!-- wp:paragraph {"style":{"typography":{"fontWeight":"700","letterSpacing":"0.14em","textTransform":"uppercase"}},"textColor":"muted-2","fontSize":"x-small"} -->
			<p class="has-muted-2-color has-text-color has-x-small-font-size" style="font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php esc_html_e( 'Author', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:query-title {"type":"archive","showPrefix":false,"style":{"typography":{"fontSize":"48px","letterSpacing":"-0.015em","lineHeight":"1.1"}}} /-->

			<!-- wp:post-author-biography {"style":{"typography":{"fontSize":"19px","lineHeight":"1.55"}},"textColor":"contrast-2","fontFamily":"newsreader"} /-->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
