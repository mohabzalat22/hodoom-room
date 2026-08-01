<?php

/**
 * Quick View modal template — side panel / drawer layout.
 *
 * @var array $args Template args: settings (array).
 *
 * @since 2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$settings = $args['settings'];
?>
	<div class="single-product merchant-quick-view-modal merchant-quick-view-<?php echo esc_attr( $settings[ 'place_product_image' ] ); ?> merchant-quick-view-layout-side-panel">
		<div class="merchant-quick-view-overlay"></div>
		<div class="merchant-quick-view-inner">
			<div class="merchant-quick-view-header">
				<?php if ( ! empty( $settings[ 'show_navigation_arrows' ] ) ) : ?>
				<a href="#" class="merchant-quick-view-nav-prev" title="<?php echo esc_attr__( 'Previous product', 'merchant' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
						<path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
					</svg>
				</a>
				<a href="#" class="merchant-quick-view-nav-next" title="<?php echo esc_attr__( 'Next product', 'merchant' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
						<path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/>
					</svg>
				</a>
				<?php endif; ?>
				<a href="#" class="merchant-quick-view-close-button" title="<?php echo esc_attr__( 'Close quick view modal', 'merchant' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
						<path d="M193.94 256L296.5 153.44l21.15-21.15c3.12-3.12 3.12-8.19 0-11.31l-22.63-22.63c-3.12-3.12-8.19-3.12-11.31 0L160 222.06 36.29 98.34c-3.12-3.12-8.19-3.12-11.31 0L2.34 120.97c-3.12 3.12-3.12 8.19 0 11.31L126.06 256 2.34 379.71c-3.12 3.12-3.12 8.19 0 11.31l22.63 22.63c3.12 3.12 8.19 3.12 11.31 0L160 289.94 262.56 392.5l21.15 21.15c3.12 3.12 8.19 3.12 11.31 0l22.63-22.63c3.12-3.12 3.12-8.19 0-11.31L193.94 256z"/>
					</svg>
				</a>
			</div>
			<div class="merchant-quick-view-content"></div>
			<div class="merchant-quick-view-loader">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
					<path opacity="0.4" d="M478.71 364.58zm-22 6.11l-27.83-15.9a15.92 15.92 0 0 1-6.94-19.2A184 184 0 1 1 256 72c5.89 0 11.71.29 17.46.83-.74-.07-1.48-.15-2.23-.21-8.49-.69-15.23-7.31-15.23-15.83v-32a16 16 0 0 1 15.34-16C266.24 8.46 261.18 8 256 8 119 8 8 119 8 256s111 248 248 248c98 0 182.42-56.95 222.71-139.42-4.13 7.86-14.23 10.55-22 6.11z" />
					<path d="M271.23 72.62c-8.49-.69-15.23-7.31-15.23-15.83V24.73c0-9.11 7.67-16.78 16.77-16.17C401.92 17.18 504 124.67 504 256a246 246 0 0 1-25 108.24c-4 8.17-14.37 11-22.26 6.45l-27.84-15.9c-7.41-4.23-9.83-13.35-6.2-21.07A182.53 182.53 0 0 0 440 256c0-96.49-74.27-175.63-168.77-183.38z" />
				</svg>
			</div>
		</div>
	</div>
