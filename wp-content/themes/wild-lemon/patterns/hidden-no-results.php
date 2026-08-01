<?php
/**
 * Title: No Results
 * Slug: wild-lemon/hidden-no-results
 * Categories: wild-lemon
 * Description: Message shown when a query returns no posts.
 * Inserter: no
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"textAlign":"center","level":2,"style":{"typography":{"fontSize":"28px"}}} -->
	<h2 class="wp-block-heading has-text-align-center" style="font-size:28px"><?php esc_html_e( 'Nothing found', 'wild-lemon' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"16px","lineHeight":"1.6"}},"textColor":"muted"} -->
	<p class="has-text-align-center has-muted-color has-text-color" style="font-size:16px;line-height:1.6"><?php esc_html_e( 'No posts were found. Try a different search, or browse a topic instead.', 'wild-lemon' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:search {"label":"<?php esc_attr_e( 'Search', 'wild-lemon' ); ?>","showLabel":false,"placeholder":"<?php esc_attr_e( 'Search the site…', 'wild-lemon' ); ?>","buttonText":"<?php esc_attr_e( 'Search', 'wild-lemon' ); ?>","className":"wl-search-form","style":{"layout":{"selfStretch":"fixed","flexSize":"420px"}},"align":"center"} /-->
</div>
<!-- /wp:group -->
