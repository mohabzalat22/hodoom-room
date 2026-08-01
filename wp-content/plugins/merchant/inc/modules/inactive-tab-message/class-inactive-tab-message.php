<?php

/**
 * Inactive Tab Message.
 * 
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once __DIR__ . '/class-inactive-tab-message-resolver.php';

/**
 * Inactive Tab Message Class.
 * 
 */
class Merchant_Inactive_Tab_Message extends Merchant_Add_Module {

	/**
	 * Module ID.
	 *
	 */
	const MODULE_ID = 'inactive-tab-message';

	/**
	 * Is module preview.
	 *
	 * @var bool
	 */
	public static $is_module_preview = false;

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
		$this->setup_module();
		parent::__construct();

		if ( is_admin() && $this->is_module_settings_page() ) {
			$this->register_admin_hooks();
		}

		if ( Merchant_Modules::is_module_active( self::MODULE_ID ) && is_admin() ) {
			$this->init_translations();
		}

		if ( ! Merchant_Modules::is_module_active( self::MODULE_ID ) ) {
			return;
		}

		// Return early if it's on admin but not in the respective module settings page.
		if ( is_admin() && ! $this->is_module_settings_page() ) {
			return;
		}

		$this->register_frontend_hooks();
	}

	/**
	 * Set up module identity: ID, flags, section, defaults, data, and options path.
	 *
	 * @return void
	 */
	protected function setup_module(): void {
		$this->module_id      = self::MODULE_ID;
		$this->wc_only        = true;
		$this->module_section = 'reduce-abandonment';

		$this->module_default_settings = array(
			// Existing (backward compatible).
			'message'              => __( '✋ Don\'t forget this', 'merchant' ),
			'abandoned_message'    => __( '✋ You left something in the cart', 'merchant' ),

			// High-value cart.
			'high_value_message'   => '',
			'high_value_threshold' => 100,

			// Rotating messages.
			'enable_rotation'      => 0,
			'rotation_messages'    => array(),
			'rotation_interval'    => 3,

			// Favicon.
			'enable_favicon'       => 0,
			'favicon_type'         => 'emoji',
			'favicon_emoji'        => 'wave',
			'favicon_url'          => '',

			// Return message.
			'return_message'       => '',
			'return_duration'      => 2,

			// Scrolling text.
			'enable_scroll'        => 0,
		);

		$this->module_data        = Merchant_Admin_Modules::$modules_data[ self::MODULE_ID ];
		$this->module_options_path = MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/admin/options.php';

		// AI prompt examples.
		$this->ai_examples = array(
			'blurb'   => esc_html__( 'See what AI can do for your inactive tab messages, from tailoring the text shoppers see when they switch away to swapping the favicon and rotating through a list of messages.', 'merchant' ),
			'prompts' => array(
				esc_html__( "Explore the abilities WPVibe gives you access to, then set the cart-items tab message to 'You left {cart_count} items behind'", 'merchant' ),
				esc_html__( 'Check what abilities you have available through WPVibe, then show a high-value cart inactive tab message when the cart total goes over $150', 'merchant' ),
				esc_html__( 'Look at your available WPVibe abilities, then change the browser favicon to the cart emoji while the tab is inactive', 'merchant' ),
				esc_html__( 'See what abilities WPVibe exposes, then turn on rotating tab messages that cycle every 5 seconds', 'merchant' ),
			),
		);
	}

	/**
	 * Register hooks that are only needed on the module's admin settings page.
	 *
	 * @return void
	 */
	protected function register_admin_hooks(): void {
		self::$is_module_preview = true;

		// Enqueue admin styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_css' ) );

		// Admin preview box.
		add_filter( 'merchant_module_preview', array( $this, 'render_admin_preview' ), 10, 2 );
	}

	/**
	 * Register hooks that power the frontend functionality.
	 *
	 * @return void
	 */
	protected function register_frontend_hooks(): void {
		// Enqueue scripts.
		add_action( 'merchant_enqueue_after_main_css_js', array( $this, 'enqueue_scripts' ) );

		// Localize script.
		add_filter( 'merchant_localize_script', array( $this, 'localize_script' ) );

		// Add merchant selector and content to cart fragments.
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_count_fragment' ) );
	}

	/**
	 * Init translations.
	 *
	 * @return void
	 */
	public function init_translations() {
		$settings = $this->get_module_settings();
		$resolver = new Merchant_Inactive_Tab_Message_Resolver();

		foreach ( $resolver->get_translatable_strings( $settings ) as $entry ) {
			Merchant_Translator::register_string( $entry['string'], esc_html( $entry['name'] ) );
		}
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
			wp_enqueue_style( 'merchant-admin-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/admin/preview.min.css', array(), MERCHANT_VERSION );
			wp_enqueue_script( 'merchant-admin-' . self::MODULE_ID . '-preview', MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/admin/preview.min.js', array( 'jquery' ), MERCHANT_VERSION, true );
			wp_localize_script(
				'merchant-admin-' . self::MODULE_ID . '-preview',
				'merchantItmPreview',
				array(
					'siteName' => get_bloginfo( 'name' ),
				)
			);
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Enqueue vendor favico.js (shared with cart-count-favicon module).
		wp_enqueue_script( 'favico', MERCHANT_URI . 'assets/js/vendor/favico.js', array(), MERCHANT_VERSION, true );

		// Register and enqueue the main module script.
		wp_enqueue_script( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/inactive-tab-message.min.js', array( 'favico' ), MERCHANT_VERSION, true );
	}

	/**
	 * Localize script with module settings.
	 *
	 * @param array<string, mixed> $setting The merchant global object setting parameter.
	 * @return array<string, mixed> The merchant global object setting parameter.
	 */
	public function localize_script( $setting ) {
		$module_settings = $this->get_module_settings();

		$cart_count = 0;
		$cart_total = '';
		$wc         = function_exists( 'WC' ) ? WC() : null;

		if ( $wc && $wc->cart ) { // @phpstan-ignore booleanAnd.rightAlwaysTrue
			$cart_count = $wc->cart->get_cart_contents_count();
			$cart_total = html_entity_decode( wp_strip_all_tags( $wc->cart->get_cart_total() ), ENT_QUOTES, 'UTF-8' );
		}

		$site_name = get_bloginfo( 'name' );

		$resolver = new Merchant_Inactive_Tab_Message_Resolver();
		$data     = $resolver->prepare_localized_data( $module_settings, $cart_count, $cart_total, $site_name );

		// Translate messages.
		$translate_keys = array(
			'inactive_tab_message',
			'inactive_tab_abandoned_message',
			'inactive_tab_high_value_message',
			'inactive_tab_return_message',
		);

		foreach ( $translate_keys as $key ) {
			if ( ! empty( $data[ $key ] ) ) {
				$data[ $key ] = Merchant_Translator::translate( $data[ $key ] );
			}
		}

		// Translate rotation messages.
		if ( ! empty( $data['inactive_tab_rotation_messages'] ) ) {
			$data['inactive_tab_rotation_messages'] = array_map(
				array( 'Merchant_Translator', 'translate' ),
				$data['inactive_tab_rotation_messages']
			);
		}

		// Also pass raw cart total for threshold comparison in JS.
		$data['inactive_tab_cart_total_raw'] = 0;
		if ( $wc && $wc->cart ) { // @phpstan-ignore booleanAnd.rightAlwaysTrue
			$data['inactive_tab_cart_total_raw'] = (float) $wc->cart->get_cart_contents_total();
		}

		// Resolve favicon attachment ID to URL.
		if ( ! empty( $data['inactive_tab_favicon_url'] ) ) {
			$favicon_attachment_url            = wp_get_attachment_url( (int) $data['inactive_tab_favicon_url'] );
			$data['inactive_tab_favicon_url'] = $favicon_attachment_url ? $favicon_attachment_url : '';
		}

		return array_merge( $setting, $data );
	}

	/**
	 * Render admin preview.
	 *
	 * @param Merchant_Admin_Preview $preview
	 * @param string                $module
	 *
	 * @return Merchant_Admin_Preview
	 */
	public function render_admin_preview( $preview, $module ) {
		if ( self::MODULE_ID === $module ) {
			ob_start();
			self::admin_preview_content();
			$content = (string) ob_get_clean();

			// HTML.
			$preview->set_html( $content );

			// All live preview updates (text, favicon, rotation) are handled
			// by the module's own admin/preview.js to avoid conflicts with
			// the global preview system's catch-all change handler.
		}

		return $preview;
	}

	/**
	 * Admin preview content.
	 *
	 * Enhanced preview showing a simulated browser tab with favicon and message.
	 *
	 * @return void
	 */
	public function admin_preview_content() {
		$settings    = $this->get_module_settings();
		$favicon_url = get_site_icon_url() ? get_site_icon_url( 512 ) : MERCHANT_URI . 'inc/modules/' . self::MODULE_ID . '/admin/images/wplogo.svg';

		// Map string keys to emoji characters (must match JS emojiMap).
		$emoji_map = array(
			'wave'  => '👋',
			'bell'  => '🔔',
			'cart'  => '🛒',
			'clock' => '⏰',
			'alert' => '❗',
			'money' => '💰',
			'fire'  => '🔥',
			'star'  => '⭐',
		);

		$favicon_key   = $settings['favicon_emoji'] ?? 'wave';
		$favicon_emoji = $emoji_map[ $favicon_key ] ?? $emoji_map['wave'];

		?>

		<div class="mrc-preview-inactive-tab-message">
			<div class="mrc-inactive-tab-preview-tabs">
				<!-- Active tab (original state) -->
				<div class="mrc-inactive-tab-preview-tab mrc-inactive-tab-preview-tab--active">
					<div class="mrc-inactive-tab-preview-tab__favicon">
						<img src="<?php echo esc_url( $favicon_url ); ?>" alt="" />
					</div>
					<div class="mrc-inactive-tab-preview-tab__title">
						<?php echo esc_html__( 'Your Page Title', 'merchant' ); ?>
					</div>
					<span class="mrc-inactive-tab-preview-tab__close dashicons dashicons-no-alt"></span>
				</div>

				<!-- Inactive tab (shows the message) -->
				<div class="mrc-inactive-tab-preview-tab mrc-inactive-tab-preview-tab--inactive">
					<div class="mrc-inactive-tab-preview-tab__favicon mrc-inactive-tab-preview-tab__favicon--swap">
					<?php if ( ! empty( $settings['enable_favicon'] ) && 'image' === ( $settings['favicon_type'] ?? 'emoji' ) && ! empty( $settings['favicon_url'] ) ) : ?>
						<?php $custom_favicon_url = wp_get_attachment_url( (int) $settings['favicon_url'] ); ?>
						<?php if ( $custom_favicon_url ) : ?>
							<img class="mrc-inactive-tab-favicon-custom" src="<?php echo esc_url( $custom_favicon_url ); ?>" alt="" />
						<?php else : ?>
							<img src="<?php echo esc_url( $favicon_url ); ?>" alt="" />
						<?php endif; ?>
					<?php elseif ( ! empty( $settings['enable_favicon'] ) && 'emoji' === ( $settings['favicon_type'] ?? 'emoji' ) ) : ?>
						<span class="mrc-inactive-tab-favicon-emoji"><?php echo esc_html( $favicon_emoji ); ?></span>
					<?php else : ?>
						<img src="<?php echo esc_url( $favicon_url ); ?>" alt="" />
					<?php endif; ?>
					</div>
					<div class="mrc-inactive-tab-preview-tab__title mrc-inactive-tab-message-text">
						<?php echo esc_html( Merchant_Translator::translate( $settings['message'] ) ); ?>
					</div>
					<span class="mrc-inactive-tab-preview-tab__close dashicons dashicons-no-alt"></span>
				</div>
			</div>

			<div class="mrc-inactive-tab-preview-label">
				<?php echo esc_html__( 'Active tab', 'merchant' ); ?>
				<span class="mrc-inactive-tab-preview-label--right"><?php echo esc_html__( 'Inactive tab (preview)', 'merchant' ); ?></span>
			</div>
		</div>

		<?php
	}

	/**
	 * Cart count fragments.
	 *
	 * @param array<string, mixed> $fragments The cart fragments.
	 * @return array<string, mixed> The cart fragments.
	 */
	public function cart_count_fragment( $fragments ) {
		if ( Merchant_Modules::is_module_active( 'inactive-tab-message' ) ) {
			$wc = function_exists( 'WC' ) ? WC() : null;
			if ( $wc && $wc->cart ) { // @phpstan-ignore booleanAnd.rightAlwaysTrue
				$fragments['.merchant_cart_count']     = $wc->cart->get_cart_contents_count();
				$fragments['.merchant_cart_total']     = html_entity_decode( wp_strip_all_tags( $wc->cart->get_cart_total() ), ENT_QUOTES, 'UTF-8' );
				$fragments['.merchant_cart_total_raw'] = (float) $wc->cart->get_cart_contents_total();
			}
		}

		return $fragments;
	}
}

// Initialize the module.
add_action( 'init', function() {
	Merchant_Modules::create_module( new Merchant_Inactive_Tab_Message() );
} );
