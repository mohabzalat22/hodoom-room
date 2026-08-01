<?php
/**
 * Reasons To Buy — Admin Assets.
 *
 * Enqueues CSS and JavaScript for the module settings
 * (admin preview) page.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Merchant_Reasons_To_Buy_Admin_Assets
 *
 * Single responsibility: enqueue admin-side styles and scripts
 * for the Reasons To Buy module.
 */
class Merchant_Reasons_To_Buy_Admin_Assets {

	/**
	 * Module ID.
	 *
	 * @var string
	 */
	private $module_id;

	/**
	 * Constructor.
	 *
	 * @param string $module_id The module identifier.
	 */
	public function __construct( string $module_id ) {
		$this->module_id = $module_id;
	}

	/**
	 * Enqueue admin CSS.
	 *
	 * @return void
	 */
	public function enqueue_css(): void {
		wp_enqueue_style(
			'merchant-' . $this->module_id,
			MERCHANT_URI . 'assets/css/modules/' . $this->module_id . '/reasons-to-buy.min.css',
			array(),
			MERCHANT_VERSION
		);
		wp_enqueue_style(
			'merchant-admin-' . $this->module_id,
			MERCHANT_URI . 'assets/css/modules/' . $this->module_id . '/admin/preview.min.css',
			array(),
			MERCHANT_VERSION
		);
	}

	/**
	 * Enqueue admin JavaScript.
	 *
	 * @return void
	 */
	public function enqueue_js(): void {
		wp_enqueue_script(
			"merchant-{$this->module_id}",
			MERCHANT_URI . "assets/js/modules/{$this->module_id}/admin/preview.min.js",
			array( 'jquery' ),
			MERCHANT_VERSION,
			true
		);
	}
}
