<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Merchant_Recent_Sales_Notifications
 */
class Merchant_Recent_Sales_Notifications extends Merchant_Add_Module {
	/**
	 * Module ID.
	 *
	 */
	const MODULE_ID = 'recent-sales-notifications';

	/**
	 * Module template path.
	 */
	const MODULE_TEMPLATES_PATH = 'modules/' . self::MODULE_ID;

	/**
	 * Initialize the module's option group definitions.
	 *
	 * @return array<int, array<string, mixed>> Array of option group arrays.
	 */
	protected function init_option_groups() {
		return require MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/admin/options.php';
	}

	/**
	 * Constructor.
	 *
	 * Sets up the module.
	 */
	public function __construct() {
		// Module id.
		$this->module_id = self::MODULE_ID;

		// WooCommerce only.
		$this->wc_only = true;

		// Module section.
		$this->module_section = 'build-trust';

		// Module data.
		$this->module_data = Merchant_Admin_Modules::$modules_data[ self::MODULE_ID ];

		// AI prompt examples.
		$this->ai_examples = array(
			'blurb'   => esc_html__( 'See what AI can do for your recent sales notifications, from choosing which purchase and cart activity they show to adjusting how far back they look and which products they leave out.', 'merchant' ),
			'prompts' => array(
				esc_html__( 'Explore the abilities WPVibe gives you access to, then turn on the recent purchase notifications and only show orders from the last 24 hours', 'merchant' ),
				esc_html__( 'Check what abilities you have available through WPVibe, then set the sales count notification to only appear once a product has at least 10 purchases in the past week', 'merchant' ),
				esc_html__( 'Look at your available WPVibe abilities, then switch the customer name format to first name plus initial and hide product names in the notifications', 'merchant' ),
				esc_html__( 'See what abilities WPVibe exposes, then exclude the Wireless Earbuds product so it never triggers a notification', 'merchant' ),
			),
		);

		// Module options path.
		$this->module_options_path = MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/admin/options.php';

		if ( Merchant_Modules::is_module_active( self::MODULE_ID ) && is_admin() ) {
			// Init translations.
			$this->init_translations();
		}

		if ( is_admin() && parent::is_module_settings_page() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );
			// Admin preview box.
			add_filter( 'merchant_module_preview', array( $this, 'render_admin_preview' ), 10, 2 );
		}

		// Parent construct.
		parent::__construct();
	}

	/**
	 * Init translations.
	 *
	 * @return void
	 */
	public function init_translations() {
		$settings = $this->get_module_settings();
		if ( ! empty( $settings['product_purchases_count']['template_singular'] ) ) {
			Merchant_Translator::register_string( $settings['product_purchases_count']['template_singular'], 'Recent sales notifications: Sales Pop' );
		}
		if ( ! empty( $settings['product_purchases_count']['template_plural'] ) ) {
			Merchant_Translator::register_string( $settings['product_purchases_count']['template_plural'], 'Recent sales notifications: Sales Pop' );
		}
		if ( ! empty( $settings['single_product_purchase']['template_full_data'] ) ) {
			Merchant_Translator::register_string( $settings['single_product_purchase']['template_full_data'], 'Recent sales notifications: Product Purchases' );
		}
		if ( ! empty( $settings['single_product_purchase']['template_name_only'] ) ) {
			Merchant_Translator::register_string( $settings['single_product_purchase']['template_name_only'], 'Recent sales notifications: Product Purchases' );
		}
		if ( ! empty( $settings['single_product_purchase']['template_no_data'] ) ) {
			Merchant_Translator::register_string( $settings['single_product_purchase']['template_no_data'], 'Recent sales notifications: Product Purchases' );
		}
		if ( ! empty( $settings['product_carts_count']['template_singular'] ) ) {
			Merchant_Translator::register_string( $settings['product_carts_count']['template_singular'], 'Recent sales notifications: Cart Summary' );
		}
		if ( ! empty( $settings['product_carts_count']['template_plural'] ) ) {
			Merchant_Translator::register_string( $settings['product_carts_count']['template_plural'], 'Recent sales notifications: Cart Summary' );
		}
		if ( ! empty( $settings['single_product_add_to_cart']['template_full_data'] ) ) {
			Merchant_Translator::register_string( $settings['single_product_add_to_cart']['template_full_data'], 'Recent sales notifications: Cart Notification' );
		}
		if ( ! empty( $settings['single_product_add_to_cart']['template_name_only'] ) ) {
			Merchant_Translator::register_string( $settings['single_product_add_to_cart']['template_name_only'], 'Recent sales notifications: Cart Notification' );
		}
		if ( ! empty( $settings['single_product_add_to_cart']['template_no_data'] ) ) {
			Merchant_Translator::register_string( $settings['single_product_add_to_cart']['template_no_data'], 'Recent sales notifications: Cart Notification' );
		}
		if ( ! empty( $settings['product_views_settings']['template_singular'] ) ) {
			Merchant_Translator::register_string( $settings['product_views_settings']['template_singular'], 'Recent sales notifications: Visitors Count' );
		}
		if ( ! empty( $settings['product_views_settings']['template_plural'] ) ) {
			Merchant_Translator::register_string( $settings['product_views_settings']['template_plural'], 'Recent sales notifications: Visitors Count' );
		}
	}

	/**
	 * Render admin preview
	 *
	 * @param Merchant_Admin_Preview $preview
	 * @param string                 $module
	 *
	 * @return Merchant_Admin_Preview
	 */
	public function render_admin_preview( $preview, $module ) {
		if ( self::MODULE_ID === $module ) {
			// HTML.
			$preview->set_html( merchant_get_template_part( 'modules/' . self::MODULE_ID . '/admin', 'preview', array(), true ) );
		}

		return $preview;
	}

	public function admin_enqueue_assets() {
		wp_enqueue_style( 'merchant-admin-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/admin/preview.min.css', array(), MERCHANT_VERSION );
		wp_enqueue_script(
			'merchant-admin-' . self::MODULE_ID,
			MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/admin/preview.min.js',
			array( 'jquery' ),
			MERCHANT_VERSION,
			true
		);
		wp_localize_script( 'merchant-admin-' . self::MODULE_ID, 'MerchantRSN', array(
			'merchant_url' => MERCHANT_URI,
		) );
	}
}

add_action( 'init', static function () {
	Merchant_Modules::create_module( new Merchant_Recent_Sales_Notifications() );
} );