<?php

/**
 * Quick View AJAX modal content template.
 *
 * @var array $args Template args: product (WC_Product), settings (array).
 *
 * @since 2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$product  = $args['product'];
$settings = $args['settings'];

$product_id    = $product->get_id();
$hide_quantity = ( empty( $settings[ 'show_quantity' ] ) ) ? 'merchant-hide-quantity' : '';
?>
	<div id="product-<?php echo absint( $product_id ); ?>" <?php wc_product_class( '', $product ); ?>>
		<div class="merchant-quick-view-row">
			<div class="merchant-quick-view-column">
				<div class="merchant-quick-view-product-gallery">
					<?php woocommerce_show_product_images(); ?>
				</div>
			</div>

			<div class="merchant-quick-view-column">
				<div class="merchant-quick-view-summary product-gallery-summary">
					<div class="merchant-quick-view-product-title">
						<h2 class="product_title entry-title"><?php echo esc_html( $product->get_title() ); ?></h2>
					</div>

					<?php if ( 0 !== $product->get_average_rating() ) : ?>
						<div class="merchant-quick-view-product-rating">
							<div class="woocommerce-product-rating">
								<?php echo wp_kses( wc_get_rating_html( $product->get_average_rating() ), merchant_kses_allowed_tags() ); ?>
							</div>
						</div>
					<?php endif; ?>

					<div class="merchant-quick-view-product-price">
						<div class="price"><?php echo wp_kses( $product->get_price_html(), merchant_kses_allowed_tags() ); ?></div>
					</div>

					<?php if ( 'top' === $settings[ 'place_product_description' ] ) : ?>
						<?php
						/**
						 * Hook 'merchant_quick_view_before_product_description'
						 *
						 * @since 1.9.14
						 */
						do_action( 'merchant_quick_view_before_product_description' );

						$description = $settings[ 'description_style' ] === 'full' ? $product->get_description() : $product->get_short_description();

						/**
						 * `merchant_quick_view_description`
						 *
						 * @since 1.9.16
						 */
						$description = apply_filters( 'merchant_quick_view_description', $description );
						?>
						<div class="merchant-quick-view-product-excerpt">
							<?php echo wp_kses_post( do_shortcode( $description ) ); ?>
						</div>

						<?php
						/**
						 * Hook 'merchant_quick_view_before_product_description'
						 *
						 * @since 1.9.14
						 */
						do_action( 'merchant_quick_view_after_product_description' );
						?>
					<?php endif; ?>

					<?php
					/**
					 * Hook 'merchant_quick_view_before_add_to_cart'
					 *
					 * @since 1.9.14
					 */
					do_action( 'merchant_quick_view_before_add_to_cart', $product, $settings );
					?>
					<div class="merchant-quick-view-product-add-to-cart <?php echo esc_attr( $hide_quantity ); ?>">
						<?php woocommerce_template_single_add_to_cart(); ?>
					</div>

					<a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" class="merchant-quick-view-full-details-link">
						<?php echo esc_html__( 'View Full Details', 'merchant' ); ?>
					</a>

					<?php
					/**
					 * Hook 'merchant_quick_view_after_add_to_cart'
					 *
					 * @since 1.9.14
					 */
					do_action( 'merchant_quick_view_after_add_to_cart', $product, $settings );

					/**
					 * Hook 'merchant_quick_view_product_summary'
					 *
					 * Fires right after the add-to-cart area, mirroring where modules like
					 * Trust Badges and Payment Logos render on the real single product page
					 * (`woocommerce_single_product_summary`, priority 30).
					 *
					 * @since 2.3.0
					 */
					do_action( 'merchant_quick_view_product_summary', $product, $settings );
					?>

					<?php if ( 'bottom' === $settings[ 'place_product_description' ] ) : ?>
						<?php
						/**
						 * Hook 'merchant_quick_view_before_product_description'
						 *
						 * @since 1.9.14
						 */
						do_action( 'merchant_quick_view_before_product_description' );

						$description = $settings[ 'description_style' ] === 'full' ? $product->get_description() : $product->get_short_description();

						/**
						 * `merchant_quick_view_description`
						 *
						 * @since 1.9.16
						 */
						$description = apply_filters( 'merchant_quick_view_description', $description );
						?>
						<div class="merchant-quick-view-product-excerpt">
							<?php echo wp_kses_post( do_shortcode( $description ) ); ?>
						</div>

						<?php
						/**
						 * Hook 'merchant_quick_view_after_product_description'
						 *
						 * @since 1.9.14
						 */
						do_action( 'merchant_quick_view_after_product_description' );
						?>
					<?php endif; ?>
					<div class="merchant-quick-view-product-meta">
						<?php woocommerce_template_single_meta(); ?>
					</div>

					<?php
					/**
					 * Hook 'merchant_quick_view_after_product_excerpt'
					 *
					 * @since 1.0.0
					 */
					do_action( 'merchant_quick_view_after_product_meta' );
					?>
				</div>
			</div>
		</div>
	</div>
