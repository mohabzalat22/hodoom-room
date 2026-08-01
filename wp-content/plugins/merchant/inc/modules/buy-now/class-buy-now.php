<?php

/**
 * Buy Now.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Buy Now Class.
 *
 */
class Merchant_Buy_Now extends Merchant_Add_Module {

	/**
	 * Module ID.
	 *
	 */
	const MODULE_ID = 'buy-now';

	/**
	 * Is module preview.
	 *
	 * @var bool
	 */
	public static $is_module_preview = false;

	/**
	 * Campaign resolver instance.
	 *
	 * @var Merchant_Buy_Now_Resolver|null
	 */
	private $resolver;



	/**
	 * Assets instance.
	 *
	 * @var Merchant_Buy_Now_Assets|null
	 */
	private $assets;

	/**
	 * Button renderer instance.
	 *
	 * @var Merchant_Buy_Now_Button_Renderer|null
	 */
	private $button_renderer;

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
	 */
	public function __construct() {

		// Module id.
		$this->module_id = self::MODULE_ID;

		// WooCommerce only.
		$this->wc_only = true;

		parent::__construct();

		// Module section.
		$this->module_section = 'reduce-abandonment';

		// Module default settings.
        $this->module_default_settings = array(
                'button-text'         => __( 'Buy Now', 'merchant' ),
                'customize-button'    => 1,
        );

		// Module data.
		$this->module_data = Merchant_Admin_Modules::$modules_data[ self::MODULE_ID ];

		// AI prompt examples.
		$this->ai_examples = array(
			'blurb'   => esc_html__( "See what AI can do for your Buy Now campaigns, from launching one scoped to a category to customizing the button's text, icon, and where it sends shoppers after purchase.", 'merchant' ),
			'prompts' => array(
				esc_html__( 'Explore the abilities WPVibe gives you access to, then create a Buy Now campaign called Summer Rush that shows the button on the Summer Essentials category', 'merchant' ),
				esc_html__( "Check what abilities you have available through WPVibe, then change the Summer Rush Buy Now campaign's button text to 'Checkout Now' and show the lightning icon before it", 'merchant' ),
				esc_html__( 'Look at your available WPVibe abilities, then set the Summer Rush Buy Now campaign to clear the existing cart before adding the product and redirect shoppers to a custom URL after purchase', 'merchant' ),
				esc_html__( 'See what abilities WPVibe exposes, then exclude the Beach Towel product from the Summer Rush Buy Now campaign', 'merchant' ),
			),
		);

		// Module options path.
		$this->module_options_path = MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/admin/options.php';

		// Is module preview page.
		if ( is_admin() && parent::is_module_settings_page() ) {
			self::$is_module_preview = true;

			// Admin preview (delegated to extracted class).
			$admin_preview = new Merchant_Buy_Now_Admin_Preview();
			add_action( 'admin_enqueue_scripts', array( $admin_preview, 'enqueue_css' ) );
			add_filter( 'merchant_module_preview', function ( $preview, $module ) use ( $admin_preview ) {
				return $admin_preview->render( $preview, $module, $this->get_module_settings() );
			}, 10, 2 );

			// Custom CSS.
			// The custom CSS should be added here as well due to ensure preview box works properly.
			add_filter( 'merchant_custom_css', array( $this, 'admin_custom_css' ) );

		}

		if ( Merchant_Modules::is_module_active( self::MODULE_ID ) && is_admin() ) {
			// Init translations.
			$this->init_translations();
		}

		if ( ! Merchant_Modules::is_module_active( self::MODULE_ID ) ) {
			return;
		}

		// Run migration if needed.
		$migration = new Merchant_Buy_Now_Migration();
		$migration->maybe_migrate();

		// Initialize the resolver with campaigns from settings.
		$settings   = $this->get_module_settings();
		$campaigns  = $settings['campaigns'] ?? array();
		$this->resolver = new Merchant_Buy_Now_Resolver( $campaigns );


		$this->assets = new Merchant_Buy_Now_Assets();

		// Allow other modules (like Quick View) to check if a product is excluded.
		add_filter( 'merchant_buy_now_is_excluded', array( $this, 'check_product_exclusion' ), 10, 2 );

		// Return early if it's on admin but not in the respective module settings page.
		if ( is_admin() && ! wp_doing_ajax() && ! parent::is_module_settings_page() ) {
			return;
		}

		// Enqueue frontend assets (delegated to extracted class).
		add_action( 'merchant_enqueue_before_main_css_js', array( $this->assets, 'enqueue_css' ) );
		add_action( 'merchant_enqueue_before_main_css_js', array( $this->assets, 'enqueue_scripts' ) );

		// Buy now listener (delegated to extracted class).
		$listener = new Merchant_Buy_Now_Listener( $this->resolver );
		add_action( 'wp_loaded', array( $listener, 'handle_simple' ) );
		add_action( 'wp_loaded', array( $listener, 'handle_grouped' ), 999 );
		add_action( 'wp_ajax_merchant_buy_now_add_to_cart', array( $listener, 'ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_merchant_buy_now_add_to_cart', array( $listener, 'ajax_add_to_cart' ) );

		// Button rendering (delegated to extracted class).
		$this->button_renderer = new Merchant_Buy_Now_Button_Renderer( $this->resolver, $settings );

		// Single product buy now button.
		$single_product_hook = ! empty( $settings['hook-order-single-product'] ) ? $settings['hook-order-single-product'] : array(
			'hook_name' => 'woocommerce_after_add_to_cart_button',
			'hook_priority' => 10,
		);
		add_action( $single_product_hook['hook_name'], array( $this->button_renderer, 'single_product_button' ), $single_product_hook['hook_priority'] );

		// Shop archive buy now button.
		$shop_archive_hook = ! empty( $settings['hook-order-shop-archive'] ) ? $settings['hook-order-shop-archive'] : array(
			'hook_name' => 'woocommerce_after_shop_loop_item',
			'hook_priority' => 10,
		);
		add_action( $shop_archive_hook['hook_name'], array( $this->button_renderer, 'shop_archive_button' ), $shop_archive_hook['hook_priority'] );

		// Custom CSS.
		add_filter( 'merchant_custom_css', array( $this, 'frontend_custom_css' ) );
    
		// Module wrapper class.
		add_filter( 'merchant_module_buy_now_wrapper_class', array( $this, 'html_wrapper_class' ) );
	}

	/**
	 * Init translations.
	 *
	 * @return void
	 */
	public function init_translations() {
		$settings  = $this->get_module_settings();
		$campaigns = $settings['campaigns'] ?? array();

		foreach ( $campaigns as $campaign ) {
			if ( ! empty( $campaign['button_text'] ) ) {
				Merchant_Translator::register_string( $campaign['button_text'], esc_html__( 'Buy now button text', 'merchant' ) );
			}


		}

		// Legacy support.
		if ( ! empty( $settings['button-text'] ) ) {
			Merchant_Translator::register_string( $settings['button-text'], esc_html__( 'Buy now button text', 'merchant' ) );
		}
	}

	/**
	 * Get the button renderer instance.
	 *
	 * Used by compatibility layers (e.g. Ohio theme) that need to
	 * re-hook the archive button to a different action.
	 *
	 * @return Merchant_Buy_Now_Button_Renderer|null
	 */
	public function get_button_renderer() {
		return $this->button_renderer;
	}

	/**
	 * Custom CSS.
	 *
	 * @return string
	 */
	public function get_module_custom_css() {
		if ( ! $this->assets ) {
			$this->assets = new Merchant_Buy_Now_Assets();
		}

		return $this->assets->get_custom_css();
	}

	/**
	 * Admin custom CSS.
	 *
	 * @param string $css The custom CSS.
	 * @return string $css The custom CSS.
	 */
	public function admin_custom_css( $css ) {
		$css .= $this->get_module_custom_css();

		return $css;
	}

	/**
	 * Frontend custom CSS.
	 *
	 * @param string $css The custom CSS.
	 * @return string $css The custom CSS.
	 */
	public function frontend_custom_css( $css ) {
		$css .= $this->get_module_custom_css();

		return $css;
	}
  
	/**
	 * HTML wrapper class.
	 *
	 * @param array<int, string> $classes The wrapper classes.
	 *
	 * @return array<int, string> $classes The wrapper classes.
	 */
	public function html_wrapper_class( $classes ) {
		$settings = $this->get_module_settings();

		if ( ! empty( $settings['customize-button'] ) ) {
			$classes[] = 'merchant-custom-buy-now-button';
		}

		return $classes;
	}

	/**
	 * Check if a product should be excluded from the Buy Now button.
	 *
	 * This method is called by the 'merchant_buy_now_is_excluded' filter,
	 * allowing other modules (like Quick View) to check exclusion status
	 * without directly accessing the resolver.
	 *
	 * @param bool       $is_excluded Current exclusion status.
	 * @param WC_Product $product     The product to check.
	 *
	 * @return bool True if the product should be excluded, false otherwise.
	 * @since 2.2.4
	 */
	public function check_product_exclusion( $is_excluded, $product ) {
		// If already excluded by another filter, respect that decision.
		if ( $is_excluded ) {
			return $is_excluded;
		}

		// No matching campaign = product is excluded.
		if ( $this->resolver ) {
			return null === $this->resolver->resolve( $product );
		}

		return false;
	}
}

// Load campaign architecture classes.
require_once MERCHANT_DIR . 'inc/modules/buy-now/class-buy-now-icons.php';
require_once MERCHANT_DIR . 'inc/modules/buy-now/class-buy-now-resolver.php';
require_once MERCHANT_DIR . 'inc/modules/buy-now/class-buy-now-migration.php';

require_once MERCHANT_DIR . 'inc/modules/buy-now/class-buy-now-assets.php';
require_once MERCHANT_DIR . 'inc/modules/buy-now/class-buy-now-listener.php';
require_once MERCHANT_DIR . 'inc/modules/buy-now/class-buy-now-admin-preview.php';
require_once MERCHANT_DIR . 'inc/modules/buy-now/class-buy-now-button-renderer.php';

// Initialize the module.
add_action( 'init', function() {
	Merchant_Modules::create_module( new Merchant_Buy_Now() );
} );
