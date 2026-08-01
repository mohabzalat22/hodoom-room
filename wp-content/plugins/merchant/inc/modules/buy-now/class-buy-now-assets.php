<?php
/**
 * Buy Now — Asset enqueuing and CSS variable mapping.
 *
 * Handles frontend/admin CSS/JS enqueuing and generates
 * dynamic custom CSS for the Buy Now button and upsell popup.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Buy_Now_Assets
 *
 * @since 2.3.0
 */
class Merchant_Buy_Now_Assets {

	/**
	 * Module ID constant — avoids coupling to the main class.
	 */
	const MODULE_ID = 'buy-now';

	/**
	 * Enqueue frontend CSS.
	 *
	 * @return void
	 */
	public function enqueue_css() {
		wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/buy-now.min.css', array(), MERCHANT_VERSION );
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/buy-now.min.js', array(), MERCHANT_VERSION, true );

		wp_localize_script( 'merchant-' . self::MODULE_ID, 'merchant_buy_now_params', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'merchant_buy_now_nonce' ),
		) );
	}

	/**
	 * Generate the full custom CSS string.
	 *
	 * @return string
	 */
	public function get_custom_css(): string {
		$css = '';

		// ── Button variables ──────────────────────────────────────────────
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'text-color', '#ffffff', '.merchant-buy-now-button', '--mrc-buy-now-text-color' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'text-hover-color', '#ffffff', '.merchant-buy-now-button', '--mrc-buy-now-text-hover-color' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'border-color', '#212121', '.merchant-buy-now-button', '--mrc-buy-now-border-color' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'border-hover-color', '#414141', '.merchant-buy-now-button', '--mrc-buy-now-border-hover-color' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'background-color', '#212121', '.merchant-buy-now-button', '--mrc-buy-now-background-color' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'background-hover-color', '#414141', '.merchant-buy-now-button', '--mrc-buy-now-background-hover-color' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'font-size', '16', '.merchant-buy-now-button', '--mrc-buy-now-font-size', 'px' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'padding_top_bottom', '13', '.merchant-buy-now-button', '--mrc-buy-now-padding-top-bottom', 'px' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'padding_left_right', '24', '.merchant-buy-now-button', '--mrc-buy-now-padding-left-right', 'px' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'margin-top', '10', '.merchant-buy-now-button', '--mrc-buy-now-margin-top', 'px' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'margin-bottom', '10', '.merchant-buy-now-button', '--mrc-buy-now-margin-bottom', 'px' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'border-radius', '0', '.merchant-buy-now-button', '--mrc-buy-now-border-radius', 'px' );

		return $css;
	}
}
