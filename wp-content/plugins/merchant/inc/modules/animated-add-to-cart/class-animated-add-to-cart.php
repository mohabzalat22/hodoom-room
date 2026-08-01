<?php

/**
 * Animated Add To Cart.
 * 
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Animated Add To Cart Class.
 * 
 */
class Merchant_Animated_Add_To_Cart extends Merchant_Add_Module {

	/**
	 * Module ID.
	 *
	 */
	const MODULE_ID = 'animated-add-to-cart';

	/**
	 * Selector for the CSS variable declarations — no ":not()", since get_variable_css()
	 * truncates a selector at its first ":".
	 */
	const CSS_VAR_SELECTOR = '.add_to_cart_button,.product_type_grouped,.single_add_to_cart_button';

	/**
	 * Is module preview.
	 * 
	 */
	public static bool $is_module_preview = false;

	/**
	 * Whether a product was just added to the cart during this request.
	 *
	 * Catches the non-AJAX single product page flow, which has no client-side event to hook.
	 */
	private static bool $just_added_to_cart = false;

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

		// Parent construct.
		parent::__construct();

		// Module section.
		$this->module_section = 'improve-experience';

		// Module default settings.
		$this->module_default_settings = array(
			'trigger'                     => 'on-mouse-hover',
			'animation'                   => 'swing',
			'speed'                       => 1,
			'glow_color_start'            => '#ff6b6b',
			'glow_color_end'              => '#ffffff',
			'success_animation_enabled'   => 0,
			'success_animation_style'     => 'aatc-success-flash',
			'success_speed'               => 1,
			'success_flash_color'         => '#22c55e',
			'show_on_archive_pages'       => 1,
			'show_on_single_product_page' => 1,
		);

		// Module data.
		$this->module_data = Merchant_Admin_Modules::$modules_data[ self::MODULE_ID ];

		// AI prompt examples.
		$this->ai_examples = array(
			'blurb'   => esc_html__( 'See what AI can do for your add to cart button animation, from choosing the animation style to setting whether it plays on mouse hover or on page load.', 'merchant' ),
			'prompts' => array(
				esc_html__( 'Explore the abilities WPVibe gives you access to, then set the add to cart button animation to Bounce', 'merchant' ),
				esc_html__( 'Check what abilities you have available through WPVibe, then switch the add to cart animation to Pulse instead', 'merchant' ),
				esc_html__( 'Look at your available WPVibe abilities, then use the Tada animation on the add to cart button and play it on page load instead of mouse hover', 'merchant' ),
			),
		);

		// Module options path.
		$this->module_options_path = MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/admin/options.php';

		// Is module preview page.
		if ( is_admin() && parent::is_module_settings_page() ) {
			self::$is_module_preview = true;

			// Enqueue admin styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_css' ) );

			// Admin preview box.
			add_filter( 'merchant_module_preview', array( $this, 'render_admin_preview' ), 10, 2 );

			// Custom CSS.
			// The custom CSS should be added here as well due to ensure preview box works properly.
			add_filter( 'merchant_custom_css', array( $this, 'admin_custom_css' ) );

		}

		if ( ! Merchant_Modules::is_module_active( self::MODULE_ID ) ) {
			return;
		}

		// Return early if it's on admin but not in the respective module settings page.
		if ( is_admin() && ! parent::is_module_settings_page() ) {
			return; 
		}

		// Enqueue styles.
		add_action( 'merchant_enqueue_before_main_css_js', array( $this, 'enqueue_css' ) );

		// Enqueue scripts.
		add_action( 'merchant_enqueue_after_main_css_js', array( $this, 'enqueue_scripts' ) );

		// Localize script.
		add_filter( 'merchant_localize_script', array( $this, 'localize_script' ) );

		// Flag same-request add to cart, for non-AJAX single product page submits.
		add_action( 'woocommerce_add_to_cart', array( $this, 'flag_just_added_to_cart' ) );

		// Handle body class.
		add_filter( 'body_class', array( $this, 'body_class' ) );

		// Custom CSS.
		add_filter( 'merchant_custom_css', array( $this, 'frontend_custom_css' ) );
	}

	/**
	 * Admin enqueue CSS.
	 * 
	 * @return void
	 */
	public function admin_enqueue_css() {
		$page   = ( ! empty( $_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$module = ( ! empty( $_GET['module'] ) ) ? sanitize_text_field( wp_unslash( $_GET['module'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'merchant' === $page && self::MODULE_ID === $module ) {
			wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/animated-add-to-cart.min.css', array(), MERCHANT_VERSION );
			wp_enqueue_style( 'merchant-admin-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/admin/preview.min.css', array(), MERCHANT_VERSION );
			wp_enqueue_script( 'merchant-admin-' . self::MODULE_ID, MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/admin/preview.min.js', array( 'jquery' ), MERCHANT_VERSION, true );
		}
	}

	/**
	 * Enqueue CSS.
	 * 
	 * @return void
	 */
	public function enqueue_css() {

		// Specific module styles.
		wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/animated-add-to-cart.min.css', array(), MERCHANT_VERSION );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/animated-add-to-cart.min.js', array(), MERCHANT_VERSION, true );
	}

	/**
	 * Localize script.
	 *
	 * @param array<string, mixed> $setting The localized settings array.
	 * @return array<string, mixed> $setting The localized settings array.
	 */
	public function localize_script( $setting ) {
		$settings = $this->get_module_settings();

		// Success feedback is action feedback, not the attention-grabbing trigger animation, so it always plays on add-to-cart regardless of the Page Scope settings.
		$setting['animated_add_to_cart_trigger']         = $settings['trigger'] ?? 'on-mouse-hover';
		$setting['animated_add_to_cart_success_enabled'] = ! empty( $settings['success_animation_enabled'] );
		$setting['animated_add_to_cart_success_style']   = $settings['success_animation_style'] ?? 'aatc-success-flash';
		$setting['animated_add_to_cart_just_added']      = self::$just_added_to_cart;

		return $setting;
	}

	/**
	 * Flag that a product was added to the cart during this request.
	 *
	 * @return void
	 */
	public function flag_just_added_to_cart() {
		self::$just_added_to_cart = true;
	}

	/**
	 * Render admin preview
	 *
	 * @param Merchant_Admin_Preview $preview
	 * @param string $module
	 *
	 * @return Merchant_Admin_Preview
	 */
	public function render_admin_preview( $preview, $module ) {
		if ( self::MODULE_ID === $module ) {
			ob_start();
			self::admin_preview_content();
			$content = ob_get_clean();

			// HTML.
			$preview->set_html( false === $content ? '' : $content );

			// Trigger.
			$preview->set_class( 'trigger', '.add_to_cart_button', array( 'on-mouse-hover', 'on-page-load', 'on-scroll-into-view' ) );

			// Animation.
			$preview->set_class( 'animation', '.add_to_cart_button', array(
				'flash',
				'bounce',
				'zoom-in',
				'shake',
				'pulse',
				'jello-shake',
				'wobble',
				'vibrate',
				'swing',
				'tada',
				'glow',
			) );

		}

		return $preview;
	}

	/**
	 * Admin preview content.
	 * 
	 * @return void
	 */
	public function admin_preview_content() {
		?>

		<div class="mrc-preview-single-product-elements">
			<div class="mrc-preview-left-column">
				<div class="mrc-preview-product-image-wrapper">
					<div class="mrc-preview-product-image"></div>
					<div class="mrc-preview-product-image-thumbs">
						<div class="mrc-preview-product-image-thumb"></div>
						<div class="mrc-preview-product-image-thumb"></div>
						<div class="mrc-preview-product-image-thumb"></div>
					</div>
				</div>
			</div>
			<div class="mrc-preview-right-column">
				<div class="mrc-preview-text-placeholder"></div>
				<div class="mrc-preview-text-placeholder mrc-mw-70"></div>
				<div class="mrc-preview-text-placeholder mrc-mw-30"></div>
				<div class="mrc-preview-text-placeholder mrc-mw-40"></div>
				<a href="#" class="add_to_cart_button"><?php echo esc_html__( 'Add To Cart', 'merchant' ); ?></a>
			</div>
		</div>

		<?php
	}

	/**
	 * Add body class.
	 *
	 * @param array<int, string> $classes The body classes.
	 * @return array<int, string> $classes The body classes.
	 */
	public function body_class( $classes ) {
		$settings = $this->get_module_settings();

		$classes[] = 'merchant-animated-add-to-cart merchant-animated-add-to-cart-' . esc_attr( $settings[ 'animation' ] );
	
		return $classes;
	}

	/**
	 * Custom CSS.
	 *
	 * @return string
	 */
	public function get_module_custom_css() {
		$settings = $this->get_module_settings();

		$trigger         = $settings['trigger'] ?? 'on-mouse-hover';
		$animation_style = $settings['animation'] ?? 'swing';
		$iteration_count = 'on-mouse-hover' === $trigger ? 'infinite' : 1;

		$button_selector = '.add_to_cart_button:not(.merchant-buy-now-button),.product_type_grouped:not(.merchant-buy-now-button),.single_add_to_cart_button:not(.merchant-buy-now-button)';

		$css = '';

		// CSS variables. Also auto-wires live admin-preview updates via Merchant_Custom_CSS.
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'speed', '1', self::CSS_VAR_SELECTOR, '--merchant-aatc-speed' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'glow_color_start', '#ff6b6b', self::CSS_VAR_SELECTOR, '--merchant-aatc-glow-start' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'glow_color_end', '#ffffff', self::CSS_VAR_SELECTOR, '--merchant-aatc-glow-end' );

		$css .= $button_selector . '{';
		$css .= '	transition: all .3s ease-in;';
		$css .= '}';

		if ( 'on-mouse-hover' === $trigger ) {
			$css .= '.add_to_cart_button:not(.merchant-buy-now-button):hover,';
			$css .= '.product_type_grouped:not(.merchant-buy-now-button):hover,';
			$css .= '.single_add_to_cart_button:not(.merchant-buy-now-button):hover {';
		} elseif ( 'on-scroll-into-view' === $trigger ) {
			// Named "aatc-" not "merchant-" — Botiga's theme CSS stretches any [class*="merchant-"]
			// element in the add-to-cart wrapper to full width.
			$css .= '.add_to_cart_button.aatc-animate-in:not(.merchant-buy-now-button),';
			$css .= '.product_type_grouped.aatc-animate-in:not(.merchant-buy-now-button),';
			$css .= '.single_add_to_cart_button.aatc-animate-in:not(.merchant-buy-now-button) {';
		} else {
			// on-page-load.
			$css .= $button_selector . '{';
		}

		switch ( $animation_style ) {
			case 'flash':
				$css .= 'animation: merchant-flash calc(1s * var(--merchant-aatc-speed, 1)) both;';
				break;

			case 'bounce':
				$css .= 'animation: merchant-bounce calc(.3s * var(--merchant-aatc-speed, 1)) alternate;';
				$css .= 'animation-iteration-count: 4;';
				break;

			case 'zoom-in':
				$css .= 'transform: scale(1.2);';
				break;

			case 'shake':
				$css .= 'animation: merchant-shake calc(.3s * var(--merchant-aatc-speed, 1));';
				$css .= 'animation-iteration-count: 2;';
				break;

			case 'pulse':
				$css .= 'animation: merchant-pulse calc(1.5s * var(--merchant-aatc-speed, 1)) ease-in-out ' . $iteration_count . ' both;';
				break;

			case 'jello-shake':
				$css .= 'animation: merchant-jello-shake calc(1.5s * var(--merchant-aatc-speed, 1)) ' . $iteration_count . ' both;';
				break;

			case 'wobble':
				$css .= 'animation: merchant-wobble calc(1.5s * var(--merchant-aatc-speed, 1)) ease-in-out ' . $iteration_count . ' both;';
				break;

			case 'vibrate':
				$css .= 'animation: merchant-vibrate calc(.3s * var(--merchant-aatc-speed, 1)) linear 4 both;';
				break;

			case 'swing':
				$css .= 'animation: merchant-swing calc(2s * var(--merchant-aatc-speed, 1)) ease-in-out ' . $iteration_count . ' alternate;';
				break;

			case 'tada':
				$css .= 'animation: merchant-tada calc(1s * var(--merchant-aatc-speed, 1)) ' . $iteration_count . ' both;';
				break;

			case 'glow':
				$css .= 'animation: merchant-glow calc(2s * var(--merchant-aatc-speed, 1)) ease-in-out ' . $iteration_count . ' both;';
				break;
		}

		$css .= '}';

		return $css;
	}

	/**
	 * CSS variables for the success animation (color, speed).
	 *
	 * Separate from get_module_custom_css() since success feedback ignores Page Scope.
	 *
	 * @return string
	 */
	private function get_success_animation_css() {
		$css = '';

		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'success_flash_color', '#22c55e', self::CSS_VAR_SELECTOR, '--merchant-aatc-success-flash-color' );
		$css .= Merchant_Custom_CSS::get_variable_css( self::MODULE_ID, 'success_speed', '1', self::CSS_VAR_SELECTOR, '--merchant-aatc-success-speed' );

		return $css;
	}

	/**
	 * Whether the module should render on the current page, based on the page-scope settings.
	 *
	 * @param array<string, mixed> $settings The module settings.
	 *
	 * @return bool
	 */
	private function should_show_on_current_page( $settings ) {
		if ( is_product() && ! empty( $settings['show_on_single_product_page'] ) ) {
			return true;
		}

		if ( ( is_shop() || is_product_taxonomy() ) && ! empty( $settings['show_on_archive_pages'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Admin custom CSS.
	 *
	 * @param string $css The custom CSS.
	 * @return string $css The custom CSS.
	 */
	public function admin_custom_css( $css ) {
		$css .= $this->get_success_animation_css();
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
		$settings = $this->get_module_settings();

		// Success feedback always plays regardless of Page Scope, so its CSS isn't gated below.
		$css .= $this->get_success_animation_css();

		if ( ! $this->should_show_on_current_page( $settings ) ) {
			return $css;
		}

		$css .= $this->get_module_custom_css();

		return $css;
	}
}

// Initialize the module.
add_action( 'init', function() {
	Merchant_Modules::create_module( new Merchant_Animated_Add_To_Cart() );
} );
