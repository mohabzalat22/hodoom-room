<?php
/**
 * Title: Author Bio Box
 * Slug: wild-lemon/author-bio
 * Categories: wild-lemon
 * Description: Lemon-tinted author card with avatar, name, and biography.
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"28px","right":"32px","bottom":"28px","left":"32px"},"blockGap":"20px"},"border":{"radius":"14px"}},"backgroundColor":"accent-2","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group has-accent-2-background-color has-background" style="border-radius:14px;padding-top:28px;padding-right:32px;padding-bottom:28px;padding-left:32px">
	<!-- wp:avatar {"size":56,"isLink":true} /-->

	<!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group">
		<!-- wp:group {"style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
		<div class="wp-block-group">
			<!-- wp:post-author-name {"isLink":true,"style":{"typography":{"fontWeight":"700"}},"fontSize":"medium"} /-->

			<!-- wp:paragraph {"style":{"typography":{"fontWeight":"600","letterSpacing":"0.1em","textTransform":"uppercase"}},"textColor":"muted","fontSize":"x-small"} -->
			<p class="has-muted-color has-text-color has-x-small-font-size" style="font-weight:600;letter-spacing:0.1em;text-transform:uppercase"><?php esc_html_e( 'Author', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:post-author-biography {"style":{"typography":{"fontSize":"14.5px","lineHeight":"1.6"}},"textColor":"contrast-2"} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
