<?php
/**
 * Title: 404
 * Slug: wild-lemon/hidden-404
 * Categories: wild-lemon
 * Description: Content for the 404 error template — oversized numeral, search, quick links, and recent posts.
 * Inserter: no
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

$wild_lemon_recipes     = get_category_by_slug( 'recipes' );
$wild_lemon_field_notes = get_category_by_slug( 'field-notes' );
$wild_lemon_recipes_url = $wild_lemon_recipes ? get_category_link( $wild_lemon_recipes ) : home_url( '/' );
$wild_lemon_notes_url   = $wild_lemon_field_notes ? get_category_link( $wild_lemon_field_notes ) : home_url( '/' );
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","right":"var:preset|spacing|60","bottom":"var:preset|spacing|70","left":"var:preset|spacing|60"}},"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:html -->
			<div class="wl-404-numeral" aria-hidden="true">
				<span class="digit">4</span>
				<span class="o"><span class="ring"></span><span class="seed"></span></span>
				<span class="digit">4</span>
			</div>
			<!-- /wp:html -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:group {"style":{"spacing":{"blockGap":"22px"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group">
				<!-- wp:paragraph {"style":{"typography":{"fontWeight":"700","letterSpacing":"0.14em","textTransform":"uppercase"}},"textColor":"muted-2","fontSize":"x-small"} -->
				<p class="has-muted-2-color has-text-color has-x-small-font-size" style="font-weight:700;letter-spacing:0.14em;text-transform:uppercase"><?php esc_html_e( 'Error 404 · page not found', 'wild-lemon' ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"46px","letterSpacing":"-0.015em","lineHeight":"1.08"}}} -->
				<h1 class="wp-block-heading" style="font-size:46px;letter-spacing:-0.015em;line-height:1.08"><?php esc_html_e( 'This path’s a little overgrown.', 'wild-lemon' ); ?></h1>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"style":{"typography":{"fontSize":"19px","lineHeight":"1.6"}},"textColor":"contrast-2","fontFamily":"newsreader"} -->
				<p class="has-contrast-2-color has-text-color has-newsreader-font-family" style="font-size:19px;line-height:1.6"><?php esc_html_e( 'The page you were foraging for has wandered off — moved, unpublished, or never quite ripened. Let’s get you back to something in season.', 'wild-lemon' ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:search {"label":"<?php esc_attr_e( 'Search', 'wild-lemon' ); ?>","showLabel":false,"placeholder":"<?php esc_attr_e( 'Search the garden…', 'wild-lemon' ); ?>","buttonText":"<?php esc_attr_e( 'Search', 'wild-lemon' ); ?>","buttonPosition":"button-inside","buttonUseIcon":false,"className":"wl-404-search"} /-->

				<!-- wp:group {"style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
				<div class="wp-block-group">
					<!-- wp:paragraph {"style":{"typography":{"fontSize":"13px","fontWeight":"500"}},"textColor":"muted-2"} -->
					<p class="has-muted-2-color has-text-color" style="font-size:13px;font-weight:500"><?php esc_html_e( 'Or wander into:', 'wild-lemon' ); ?></p>
					<!-- /wp:paragraph -->

					<!-- wp:buttons {"className":"wl-chips","style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
					<div class="wp-block-buttons wl-chips">
						<!-- wp:button -->
						<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Latest posts', 'wild-lemon' ); ?></a></div>
						<!-- /wp:button -->

						<!-- wp:button -->
						<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $wild_lemon_recipes_url ); ?>"><?php esc_html_e( 'Recipes', 'wild-lemon' ); ?></a></div>
						<!-- /wp:button -->

						<!-- wp:button -->
						<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $wild_lemon_notes_url ); ?>"><?php esc_html_e( 'Field Notes', 'wild-lemon' ); ?></a></div>
						<!-- /wp:button -->
					</div>
					<!-- /wp:buttons -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|60","bottom":"var:preset|spacing|70","left":"var:preset|spacing|60"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--60)">
	<!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:heading {"style":{"typography":{"fontSize":"30px"}}} -->
		<h2 class="wp-block-heading" style="font-size:30px"><?php esc_html_e( 'While you’re here', 'wild-lemon' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}},"fontSize":"small"} -->
		<p class="has-small-font-size" style="font-weight:600"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Browse all posts', 'wild-lemon' ); ?></a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":30,"query":{"perPage":3,"pages":1,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"align":"wide"} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":3}} -->
			<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"default"}} -->
			<div class="wp-block-group">
				<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"3/2","style":{"border":{"radius":"12px"}}} /-->

				<!-- wp:group {"style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"default"}} -->
				<div class="wp-block-group">
					<!-- wp:post-terms {"term":"category","style":{"typography":{"fontWeight":"600","letterSpacing":"0.1em","textTransform":"uppercase"},"elements":{"link":{"color":{"text":"var:preset|color|accent-3"},"typography":{"textDecoration":"none"}}}},"fontSize":"x-small"} /-->

					<!-- wp:post-title {"level":3,"isLink":true,"style":{"typography":{"fontSize":"23px","lineHeight":"1.25"}}} /-->

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
