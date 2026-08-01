<?php
/**
 * Right Buttons Panel.
 *
 * @package aster_storefront
 */
?>
<div class="panel-right">
	<div class="pro-btn theme-btn">
		<div class="screenshot">
			<img src="<?php echo esc_url(get_template_directory_uri()) .'/theme-library/getting-started/images/pro-screenshot.png'; ?>" />
		</div>
		<br>
		<div class="theme-info">
			<h3><?php esc_html_e( 'Storefront Wordpress Theme', 'aster-storefront' ); ?></h3>
			<div class="theme-price">
				<span class="price-text"><?php esc_html_e( 'Price:', 'aster-storefront' ); ?></h6>
				<del><?php esc_html_e( '$49', 'aster-storefront' ); ?></del>
				<span><?php esc_html_e( '$39', 'aster-storefront' ); ?></span>
			</div>
			<div class="panelbutton">
				<a class="button button-primary" href="<?php echo esc_url( ASTER_STOREFRONT_PREMIUM_PAGE ); ?>" title="<?php esc_attr_e( 'Go Pro', 'aster-storefront' ); ?>" target="_blank"><?php esc_html_e( 'Try Premium', 'aster-storefront' ); ?></a>

				<a class="button button-primary" href="<?php echo esc_url( ASTER_STOREFRONT_PRO_DEMO ); ?>" title="<?php esc_attr_e( 'Live Demo', 'aster-storefront' ); ?>" target="_blank"><?php esc_html_e( 'Live Demo', 'aster-storefront' ); ?></a>
			</div>
			<a class="button button-primary pro-doc" href="<?php echo esc_url( ASTER_STOREFRONT_PREMIUM_DOCUMENTATION ); ?>" title="<?php esc_attr_e( 'Pro Documentation', 'aster-storefront' ); ?>" target="_blank"><?php esc_html_e( 'Pro Documentation', 'aster-storefront' ); ?></a>
		</div>
	</div>
	<div class="pro-btn bundle-btn">
		<div class="bundle-img">
			<img src="<?php echo esc_url(get_template_directory_uri()) .'/theme-library/getting-started/images/bundle.png'; ?>" />
		</div>
		<br>
		<h3><?php esc_html_e( 'WP Theme Bundle', 'aster-storefront' ); ?></h3>
		<p><?php esc_html_e( 'Get access to a collection of premium WordPress themes in one bundle. Enjoy effortless website building, full customization, and dedicated customer support for a smooth, professional web experience.', 'aster-storefront' ); ?></p>
		<a class="button button-primary" href="<?php echo esc_url( ASTER_STOREFRONT_BUNDLE_PAGE ); ?>" title="<?php esc_attr_e( 'Go Pro', 'aster-storefront' ); ?>" target="_blank">
            <?php esc_html_e( 'Exclusive Theme Bundle - $79', 'aster-storefront' ); ?>
        </a>
	</div>
</div>