<?php
/**
 * Buy Now — Add-to-cart listener.
 *
 * Handles the Buy Now form submission for simple/variable products
 * and grouped products. Manages cart clearing, add-to-cart, and redirect.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Buy_Now_Listener
 *
 * @since 2.3.0
 */
class Merchant_Buy_Now_Listener {

	/**
	 * Campaign resolver instance.
	 *
	 * @var Merchant_Buy_Now_Resolver
	 */
	private $resolver;

	/**
	 * Constructor.
	 *
	 * @param Merchant_Buy_Now_Resolver $resolver The campaign resolver.
	 */
	public function __construct( Merchant_Buy_Now_Resolver $resolver ) {
		$this->resolver = $resolver;
	}

	/**
	 * Handle Buy Now submission for simple/variable products.
	 *
	 * Hooked to `wp_loaded`.
	 *
	 * @return void
	 */
	public function handle_simple(): void {
		$product_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['merchant-buy-now'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return;
		}

		if ( ! $product->is_type( 'grouped' ) ) {
			$variation_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['variation_id'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$quantity     = (int) sanitize_text_field( $_REQUEST['quantity'] ?? 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$campaign_id  = sanitize_text_field( wp_unslash( $_REQUEST['merchant_campaign_id'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Resolve campaign for this product (for cart action and redirect).
			$campaign = null;
			if ( ! empty( $campaign_id ) ) {
				$campaign = $this->resolver->find_by_id( $campaign_id );
			} else {
				$campaign = $this->resolver->resolve( $product );
			}

			// Cart action: clear cart if campaign says so.
			if ( $campaign && 'clear' === ( $campaign['cart_action'] ?? 'keep' ) ) {
				WC()->cart->empty_cart();
			}

			// Suppress the Added to Cart Popup when the campaign has the popup toggle off.
			if ( $campaign && empty( $campaign['show_cart_popup'] ?? 0 ) ) {
				add_filter( 'merchant_pro_added_to_cart_popup_should_initiate', '__return_false' );
			}

			if ( $variation_id ) {
				WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );
			} else {
				WC()->cart->add_to_cart( $product_id, $quantity );
			}

			// Determine redirect URL.
			$redirect_url = wc_get_checkout_url();
			if ( $campaign && 'custom_url' === ( $campaign['redirect_to'] ?? 'checkout' ) && ! empty( $campaign['custom_redirect_url'] ) ) {
				$redirect_url = esc_url( $campaign['custom_redirect_url'] );
			}

			/**
			 * Filter the Buy Now redirect URL.
			 *
			 * @param string     $redirect_url The redirect URL.
			 * @param array|null $campaign     The matched campaign data.
			 *
			 * @since 2.2.8
			 */
			$redirect_url = apply_filters( 'merchant_buy_now_redirect_url', $redirect_url, $campaign );

			wp_safe_redirect( $redirect_url );
			$this->terminate();
		}
	}

	/**
	 * Handle Buy Now submission for grouped products.
	 *
	 * Hooked to `wp_loaded` with priority 999.
	 *
	 * @return void
	 */
	public function handle_grouped(): void {
		$product_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['merchant-buy-now'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return;
		}

		if ( $product->is_type( 'grouped' ) ) {
			$quantity_set      = false;
			$was_added_to_cart = false;
			$added_to_cart     = array();
			$items             = isset( $_REQUEST['quantity'] ) && is_array( $_REQUEST['quantity'] ) ? wp_unslash( $_REQUEST['quantity'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			foreach ( $items as $item => $quantity ) {
				$quantity = wc_stock_amount( $quantity );
				if ( $quantity <= 0 ) {
					continue;
				}
				$quantity_set = true;

				/**
				 * `woocommerce_add_to_cart_validation`
				 *
				 * @since WC 7.2.0
				 */
				$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $item, $quantity );

				// Suppress total recalculation until finished.
				remove_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20 );

				if ( $passed_validation && false !== WC()->cart->add_to_cart( $item, (int) $quantity ) ) {
					$was_added_to_cart      = true;
					$added_to_cart[ $item ] = $quantity;
				}

				add_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );
			}

			if ( ! $was_added_to_cart && ! $quantity_set ) {
				if ( ! wc_has_notice( __( 'Please choose the quantity of items you wish to add to your cart&hellip;', 'merchant' ), 'error' ) ) {
					wc_add_notice( __( 'Please choose the quantity of items you wish to add to your cart&hellip;', 'merchant' ), 'error' );
				}
				return;
			} elseif ( $was_added_to_cart ) {
				wc_add_to_cart_message( $added_to_cart );
			}
		}

		wp_safe_redirect( wc_get_checkout_url() );
		$this->terminate();
	}

	/**
	 * Handle AJAX add-to-cart for Buy Now popup flow.
	 *
	 * @return void
	 */
	public function ajax_add_to_cart(): void {
		if ( ! check_ajax_referer( 'merchant_buy_now_nonce', '_wpnonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
			return; // @phpstan-ignore deadCode.unreachable
		}

		$product_id   = absint( $_POST['product_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$quantity     = absint( $_POST['quantity'] ?? 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$variation_id = absint( $_POST['variation_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$campaign_id  = sanitize_text_field( $_POST['campaign_id'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => 'Invalid product.' ) );
			return; // @phpstan-ignore deadCode.unreachable
		}

		$campaign = $this->resolver->find_by_id( $campaign_id );

		if ( $campaign && 'clear' === ( $campaign['cart_action'] ?? 'keep' ) ) {
			WC()->cart->empty_cart();
		}

		$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );

		if ( ! $cart_item_key ) {
			wp_send_json_error( array( 'message' => 'Could not add product to cart.' ) );
			return; // @phpstan-ignore deadCode.unreachable
		}

		$redirect_url = wc_get_checkout_url();
		if ( $campaign && 'custom_url' === ( $campaign['redirect_to'] ?? 'checkout' ) && ! empty( $campaign['custom_redirect_url'] ) ) {
			$redirect_url = esc_url( $campaign['custom_redirect_url'] );
		}

		/**
		 * Filter the Buy Now redirect URL.
		 *
		 * @param string     $redirect_url The redirect URL.
		 * @param array|null $campaign     The matched campaign data.
		 *
		 * @since 2.2.8
		 */
		$redirect_url = apply_filters( 'merchant_buy_now_redirect_url', $redirect_url, $campaign );

		/**
		 * Collect WC cart fragments so the Added to Cart Popup module
		 * can inject its popup_data and popup_template into the response.
		 *
		 * @since 2.2.8
		 */
		$fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array() );

		$cart_hash = WC()->cart->get_cart_hash();

		wp_send_json_success( array(
			'redirect_url' => $redirect_url,
			'fragments'    => $fragments,
			'cart_hash'    => $cart_hash,
		) );
	}

	/**
	 * Terminate execution.
	 *
	 * Extracted to a protected method so tests can override it
	 * instead of calling `exit` which halts the test runner.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function terminate(): void {
		exit;
	}
}
