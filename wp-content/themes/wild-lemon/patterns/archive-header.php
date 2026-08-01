<?php
/**
 * Title: Archive Header
 * Slug: wild-lemon/archive-header
 * Categories: wild-lemon
 * Description: Lemon-tinted archive header with label, archive title, and description.
 * Inserter: no
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","right":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|60"},"blockGap":"14px"},"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}}},"backgroundColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-accent-2-background-color has-background" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:paragraph {"align":"wide","style":{"typography":{"fontWeight":"700","letterSpacing":"0.14em","textTransform":"uppercase"}},"textColor":"muted","fontSize":"x-small"} -->
	<p class="alignwide has-muted-color has-text-color has-x-small-font-size" style="font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php esc_html_e( 'Archive', 'wild-lemon' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:query-title {"type":"archive","showPrefix":false,"align":"wide","style":{"typography":{"fontSize":"52px","letterSpacing":"-0.015em","lineHeight":"1.1"}}} /-->

	<!-- wp:term-description {"align":"wide","style":{"typography":{"fontSize":"19px","lineHeight":"1.55"},"layout":{"selfStretch":"fixed","flexSize":"560px"}},"textColor":"contrast-2","fontFamily":"newsreader"} /-->
</div>
<!-- /wp:group -->
