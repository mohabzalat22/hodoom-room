<?php
/**
 * Title: Browse by Topic
 * Slug: wild-lemon/home-topics
 * Categories: wild-lemon
 * Description: Lemon-tinted strip with a label and a pill-style tag cloud.
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|60","bottom":"var:preset|spacing|50","left":"var:preset|spacing|60"},"blockGap":"var:preset|spacing|40"},"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}}},"backgroundColor":"accent-2","layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
<div class="wp-block-group alignfull has-accent-2-background-color has-background" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:paragraph {"style":{"typography":{"fontWeight":"700","letterSpacing":"0.14em","textTransform":"uppercase"}},"fontSize":"x-small"} -->
	<p class="has-x-small-font-size" style="font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php esc_html_e( 'Browse by topic', 'wild-lemon' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:tag-cloud {"numberOfTags":12,"smallestFontSize":"14px","largestFontSize":"14px","className":"wl-topic-pills"} /-->
</div>
<!-- /wp:group -->
