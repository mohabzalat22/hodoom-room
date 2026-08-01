<?php
/**
 * Title: Header
 * Slug: wild-lemon/header
 * Categories: wild-lemon
 * Block Types: core/template-part/header
 * Description: Site header with the site title, navigation, and a search button.
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

?>
<!-- wp:group {"align":"full","className":"wl-header","style":{"spacing":{"padding":{"top":"20px","right":"var:preset|spacing|60","bottom":"20px","left":"var:preset|spacing|60"}},"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group alignfull wl-header" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:20px;padding-right:var(--wp--preset--spacing--60);padding-bottom:20px;padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:site-title {"level":0,"className":"wl-logo"} /-->

	<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
	<div class="wp-block-group">
		<!-- wp:navigation {"overlayMenu":"mobile","style":{"spacing":{"blockGap":"var:preset|spacing|40"}}} /-->

		<!-- wp:search {"label":"<?php esc_attr_e( 'Search', 'wild-lemon' ); ?>","showLabel":false,"placeholder":"<?php esc_attr_e( 'Search', 'wild-lemon' ); ?>","buttonText":"<?php esc_attr_e( 'Search', 'wild-lemon' ); ?>","buttonPosition":"button-only","buttonUseIcon":false,"className":"wl-search-pill"} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
