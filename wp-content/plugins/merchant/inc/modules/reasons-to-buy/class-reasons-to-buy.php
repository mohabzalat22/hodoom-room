<?php

/**
 * Reasons To Buy.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Reasons to buy class.
 *
 */
class Merchant_Reasons_To_Buy extends Merchant_Add_Module {

	/**
	 * Module ID.
	 *
	 */
	const MODULE_ID = 'reasons-to-buy';

	/**
	 * Is module preview.
	 *
	 * @var bool
	 */
	public static $is_module_preview = false;

	/**
	 * Admin assets handler.
	 *
	 * @var Merchant_Reasons_To_Buy_Admin_Assets
	 */
	private $admin_assets;

	/**
	 * Admin preview handler.
	 *
	 * @var Merchant_Reasons_To_Buy_Admin_Preview
	 */
	private $admin_preview;

	/**
	 * Translation handler.
	 *
	 * @var Merchant_Reasons_To_Buy_Translations
	 */
	private $translations;

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
		$this->module_id = self::MODULE_ID;
		$this->wc_only   = true;

		parent::__construct();

		$this->setup_module();
		$this->create_collaborators();
		$this->register_hooks();
		$this->init_translations();
	}

	/**
	 * Configure module properties required by the parent class.
	 *
	 * @return void
	 */
	private function setup_module(): void {
		$this->module_section      = 'build-trust';
		$this->module_data         = Merchant_Admin_Modules::$modules_data[ self::MODULE_ID ];
		$this->module_options_path = MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/admin/options.php';

		// AI prompt examples.
		$this->ai_examples = array(
			'blurb'   => esc_html__( 'See what AI can do for your reasons-to-buy lists, from building a new list of selling points to choosing which products it shows on.', 'merchant' ),
			'prompts' => array(
				esc_html__( "Explore the abilities WPVibe gives you access to, then create a reasons-to-buy list titled 'Why shop with us' with the items 'Free returns within 30 days', 'Ships within 24 hours', and '2-year warranty included'", 'merchant' ),
				esc_html__( "Check what abilities you have available through WPVibe, then add 'Handmade in Italy' to the 'Why shop with us' list", 'merchant' ),
				esc_html__( "Look at your available WPVibe abilities, then show the 'Why shop with us' list only on products in the Leather Goods category", 'merchant' ),
				esc_html__( "See what abilities WPVibe exposes, then exclude the Clearance tag from the 'Why shop with us' list", 'merchant' ),
			),
		);
	}

	/**
	 * Instantiate collaborator objects.
	 *
	 * @return void
	 */
	private function create_collaborators(): void {
		$this->admin_assets  = new Merchant_Reasons_To_Buy_Admin_Assets( self::MODULE_ID );
		$this->admin_preview = new Merchant_Reasons_To_Buy_Admin_Preview( self::MODULE_ID );
		$this->translations  = new Merchant_Reasons_To_Buy_Translations();
	}

	/**
	 * Register all WordPress hooks, delegating to collaborators.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		add_filter( 'merchant_reasons_to_buy_wrapper_class', array( $this, 'html_wrapper_class' ) );

		if ( is_admin() && parent::is_module_settings_page() ) {
			self::$is_module_preview = true;

			add_action( 'admin_enqueue_scripts', array( $this->admin_assets, 'enqueue_css' ) );
			add_action( 'admin_enqueue_scripts', array( $this->admin_assets, 'enqueue_js' ) );
			add_filter( 'merchant_admin_localize_script', array( $this->admin_preview, 'localize_script' ) );
			add_filter( 'merchant_module_preview', array( $this->admin_preview, 'render' ), 10, 2 );
			// The custom CSS should be added here as well due to ensure preview box works properly.
			add_filter( 'merchant_custom_css', array( $this, 'admin_custom_css' ) );
		}
	}

	/**
	 * Register translatable strings when the module is active.
	 *
	 * @return void
	 */
	private function init_translations(): void {
		if ( is_admin() && Merchant_Modules::is_module_active( self::MODULE_ID ) ) {
			$this->translations->register( $this->get_module_settings() );
		}
	}

	/**
	 * HTML wrapper class.
	 *
	 * @param array<int, string> $classes The wrapper classes.
	 *
	 * @return array<int, string> $classes The wrapper classes.
	 */
	public function html_wrapper_class( array $classes ): array {
		$settings = $this->get_module_settings();

		if ( ! empty( $settings['display_icon'] ) ) {
			$classes[] = 'show-icon';
		}

		return $classes;
	}

	/**
	 * Custom CSS.
	 *
	 * @return string
	 */
	public function get_module_custom_css() {
		return '';
	}

	/**
	 * Admin custom CSS.
	 *
	 * @param string $css The custom CSS.
	 *
	 * @return string $css The custom CSS.
	 */
	public function admin_custom_css( $css ) {
		$css .= $this->get_module_custom_css();

		return $css;
	}
}

// Collaborator classes (extracted for SRP).
require_once __DIR__ . '/class-reasons-to-buy-translations.php';
require_once __DIR__ . '/admin/class-reasons-to-buy-admin-assets.php';
require_once __DIR__ . '/admin/class-reasons-to-buy-admin-preview.php';

// Initialize the module.
add_action( 'init', function () {
	Merchant_Modules::create_module( new Merchant_Reasons_To_Buy() );
} );
