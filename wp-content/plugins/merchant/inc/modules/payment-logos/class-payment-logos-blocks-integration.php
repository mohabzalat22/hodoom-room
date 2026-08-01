<?php
/**
 * Payment Logos - WooCommerce Blocks Integration.
 *
 * Handles enqueuing and data localisation for block-based cart & checkout pages.
 * Mirrors the architecture used by Merchant Pro modules such as
 * free-shipping-progress-bar and recently-viewed-products.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Merchant_Payment_Logos_Blocks_Integration
 *
 * @since 2.2.8
 */
class Merchant_Payment_Logos_Blocks_Integration {

	/**
	 * Module ID.
	 *
	 * @var string
	 */
	const MODULE_ID = 'payment-logos';

	/**
	 * Singleton instance.
	 *
	 * @var Merchant_Payment_Logos_Blocks_Integration|null
	 */
	private static $instance = null;

	/**
	 * Private constructor — use get_instance().
	 */
	private function __construct() {}

	/**
	 * Get singleton instance.
	 *
	 * @since 2.2.8
	 *
	 * @return Merchant_Payment_Logos_Blocks_Integration
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register hooks.
	 *
	 * Called from the main module class after the module-active guard.
	 *
	 * @since 2.2.8
	 *
	 * @return void
	 */
	public function load_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_block_assets' ) );
	}

	/**
	 * Enqueue the blocks JS and localise data when on a block-layout cart or checkout page.
	 *
	 * @since 2.2.8
	 *
	 * @return void
	 */
	public function enqueue_block_assets() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( ! $this->should_enqueue_on_current_page() ) {
			return;
		}

		$is_cart     = merchant_is_cart_block_layout() && is_cart();
		$is_checkout = merchant_is_checkout_block_layout() && is_checkout();

		// Read settings the same way the main class does.
		$show_on_cart     = (bool) Merchant_Admin_Options::get( self::MODULE_ID, 'show_on_cart_page', 0 );
		$cart_position    = Merchant_Admin_Options::get( self::MODULE_ID, 'cart_position', 'after_table' );
		$show_on_checkout = (bool) Merchant_Admin_Options::get( self::MODULE_ID, 'show_on_checkout_page', 0 );
		$checkout_position = Merchant_Admin_Options::get( self::MODULE_ID, 'checkout_position', 'before_checkout_form' );

		$cart_enabled     = $is_cart && $show_on_cart;
		$checkout_enabled = $is_checkout && $show_on_checkout;

		if ( ! $cart_enabled && ! $checkout_enabled ) {
			return;
		}

		wp_enqueue_script(
			'merchant-payment-logos-blocks',
			MERCHANT_URI . 'assets/js/modules/payment-logos/payment-logos-blocks.min.js',
			array( 'jquery' ),
			MERCHANT_VERSION,
			true
		);

		// Also load the module's frontend CSS so logos are styled in block layout.
		wp_enqueue_style(
			'merchant-payment-logos',
			MERCHANT_URI . 'assets/css/modules/payment-logos/payment-logos.min.css',
			array(),
			MERCHANT_VERSION
		);

		wp_localize_script(
			'merchant-payment-logos-blocks',
			'merchant_payment_logos_blocks_data',
			array(
				'cart_enabled'      => $cart_enabled,
				'cart_position'     => $cart_position,
				'cart_html'         => $cart_enabled ? $this->get_block_html( 'merchant-payment-logos--cart' ) : '',
				'checkout_enabled'  => $checkout_enabled,
				'checkout_position' => $checkout_position,
				'checkout_html'     => $checkout_enabled ? $this->get_block_html( 'merchant-payment-logos--checkout' ) : '',
			)
		);
	}

	/**
	 * Determine whether to enqueue block assets on the current page.
	 *
	 * @since 2.2.8
	 *
	 * @return bool
	 */
	protected function should_enqueue_on_current_page() {
		return ( merchant_is_cart_block_layout() && is_cart() )
			|| ( merchant_is_checkout_block_layout() && is_checkout() );
	}

	/**
	 * Capture the rendered HTML for a given location class.
	 *
	 * Delegates to the main module's render method via output buffering.
	 *
	 * @since 2.2.8
	 *
	 * @param string $location_class CSS modifier class for the logos wrapper.
	 *
	 * @return string
	 */
	protected function get_block_html( $location_class ) {
		$module = Merchant_Modules::get_module( self::MODULE_ID );
		if ( ! ( $module instanceof Merchant_Payment_Logos ) ) {
			return '';
		}

		return $module->render_logos_to_string( $location_class );
	}
}
