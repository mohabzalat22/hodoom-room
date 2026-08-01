<?php
/**
 * Single Variation Selection Notice.
 *
 * @package Botiga
 */

/**
 * Enqueue single product scripts.
 *
 * @since 1.5.8
 */
function botiga_single_enqueue_product_variation_notice_script() {
	// Only on single product pages.
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	// Ensure WooCommerce is active.
	if ( ! function_exists( 'wc_get_product' ) ) {
		return;
	}

	$product_id = get_queried_object_id();

	if ( empty( $product_id ) ) {
		return;
	}

	$product = wc_get_product( $product_id );

	if ( ! $product ) {
		return;
	}

	// Only for variable products.
	if ( ! $product->is_type( 'variable' ) ) {
		return;
	}

	wp_enqueue_script(
		'botiga-variation-selection-notice',
		get_template_directory_uri() . '/assets/js/botiga-variation-selection-notice.min.js',
		array( 'jquery', 'wc-add-to-cart-variation' ),
		BOTIGA_VERSION,
		true
	);

	wp_localize_script(
		'botiga-variation-selection-notice',
		'botigaVariationNotice',
		array(
			'fallbackMessage' => esc_html__(
				'Please select all required options before adding this product to your cart.',
				'botiga'
			),

			/* translators: %s: a list of missing attributes (e.g. "Color and Size"). */
			'missingMessage'  => esc_html__(
				'Please select %s before adding this product to your cart.',
				'botiga'
			),

			// For 2+ items, used between items except the last.
			'listSeparator'   => esc_html__( ', ', 'botiga' ),

			// Used between the last 2 items for a more natural sentence.
			'listConjunction' => esc_html__( ' and ', 'botiga' ),

			'scrollOffset'    => 80,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'botiga_single_enqueue_product_variation_notice_script', 20 );
