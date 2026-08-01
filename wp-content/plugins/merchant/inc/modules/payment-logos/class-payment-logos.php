<?php

/**
 * Payment Logos
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Payment Logos Class.
 *
 */
class Merchant_Payment_Logos extends Merchant_Add_Module {

	/**
	 * Module ID.
	 *
	 */
	const MODULE_ID = 'payment-logos';

	/**
	 * Is module preview.
	 *
	 * @var bool
	 */
	public static $is_module_preview = false;

	/**
	 * Whether the module has a shortcode or not.
	 *
	 * @var bool
	 */
	public $has_shortcode = true;

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

		$this->module_section = 'build-trust';

		$this->module_default_settings = array(
			'icon_source'               => 'gallery',
			'logos'                     => '',
			'library_icons'             => array( 'visa', 'mastercard', 'paypal' ),
			'title'                     => __( '🔒 Safe & Secure Checkout', 'merchant' ),
			'show_on_single_product'    => 1,
			'show_on_cart_page'         => 0,
			'cart_position'             => 'after_table',
			'show_on_checkout_page'     => 0,
			'checkout_position'         => 'before_checkout_form',
			'show_on_footer'            => 0,
			'show_on_archive_pages'     => 0,
			'archive_position'          => 'after_loop',
			'container_bg_color'        => 'transparent',
			'container_padding'         => 0,
			'container_border_radius'   => 0,
			'container_border_color'    => 'transparent',
			'container_border_width'    => 0,
			'exclude_products_toggle'   => 0,
			'excluded_products'         => array(),
			'exclude_categories_toggle' => 0,
			'excluded_categories'       => array(),
		);

		$this->module_data = Merchant_Admin_Modules::$modules_data[ self::MODULE_ID ];

		// AI prompt examples.
		$this->ai_examples = array(
			'blurb'   => esc_html__( 'See what AI can do for your payment logos, from picking which cards and wallets display to choosing where they appear across your store.', 'merchant' ),
			'prompts' => array(
				esc_html__( 'Explore the abilities WPVibe gives you access to, then show the Visa, Mastercard, and PayPal logos from the built-in icon library on my product pages', 'merchant' ),
				esc_html__( 'Check what abilities you have available through WPVibe, then also show the payment logos on the cart page, right before the "Proceed to checkout" button', 'merchant' ),
				esc_html__( "Look at your available WPVibe abilities, then change the text above the payment logos to 'Guaranteed Safe Checkout' and center them", 'merchant' ),
				esc_html__( 'See what abilities WPVibe exposes, then hide the payment logos on all products in the Gift Cards category', 'merchant' ),
			),
		);

		$this->module_options_path = MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/admin/options.php';

		if ( is_admin() && parent::is_module_settings_page() ) {
			$this->register_admin_preview_hooks();
		}

		if ( Merchant_Modules::is_module_active( self::MODULE_ID ) && is_admin() ) {
			$this->init_translations();
		}

		if ( ! Merchant_Modules::is_module_active( self::MODULE_ID ) ) {
			return;
		}

		if ( is_admin() && ! wp_doing_ajax() && ! parent::is_module_settings_page() ) {
			return;
		}

		$this->register_frontend_hooks();
	}

	/**
	 * Register hooks required by the admin settings/preview page.
	 *
	 * Called only when the current admin page is this module's settings page.
	 *
	 * @since 2.2.8
	 *
	 * @return void
	 */
	private function register_admin_preview_hooks() {
		self::$is_module_preview = true;

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_css' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_js' ) );
		add_filter( 'merchant_admin_localize_script', array( $this, 'localize_script' ) );
		add_filter( 'merchant_module_preview', array( $this, 'render_admin_preview' ), 10, 2 );

		// Custom CSS must also be added here so the preview box renders correctly.
		add_filter( 'merchant_custom_css', array( $this, 'admin_custom_css' ) );
	}

	/**
	 * Register all frontend hooks for an active module.
	 *
	 * Called only when the module is active and we are either on the frontend
	 * or performing an AJAX request.
	 *
	 * @since 2.2.8
	 *
	 * @return void
	 */
	private function register_frontend_hooks() {
		add_action( 'admin_init', array( $this, 'add_image_sizes' ) );
		add_action( 'wp_ajax_merchant_regenerate_images', array( $this, 'regenerate_images' ) );
		add_action( 'merchant_enqueue_before_main_css_js', array( $this, 'enqueue_css' ) );
		add_filter( 'merchant_custom_css', array( $this, 'frontend_custom_css' ) );

		$this->register_display_hooks();

		require_once MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/class-payment-logos-blocks-integration.php';
		Merchant_Payment_Logos_Blocks_Integration::get_instance()->load_hooks();
	}

	/**
	 * Register hooks for all enabled display locations.
	 *
	 * @since 2.2.8
	 *
	 * @return void
	 */
	private function register_display_hooks() {
		$settings = $this->get_module_settings();

		$this->maybe_register_product_hook( $settings );
		$this->maybe_register_cart_hook( $settings );
		$this->maybe_register_checkout_hook( $settings );
		$this->maybe_register_footer_hook( $settings );
		$this->maybe_register_archive_hook( $settings );
	}

	/**
	 * Register the single-product display hook if enabled.
	 *
	 * @param array<string, mixed> $settings Module settings.
	 *
	 * @return void
	 */
	private function maybe_register_product_hook( $settings ) {
		if ( ! empty( $settings['show_on_single_product'] ) ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'payment_logos_output' ), 30 );

			/**
			 * Add payment logos inside the Quick View modal.
			 *
			 * @since 2.3.0
			 */
			add_action( 'merchant_quick_view_product_summary', array( $this, 'payment_logos_output' ), 30 );
		}
	}

	/**
	 * Register the cart page display hook if enabled and not using block layout.
	 *
	 * @param array<string, mixed> $settings Module settings.
	 *
	 * @return void
	 */
	private function maybe_register_cart_hook( $settings ) {
		if ( empty( $settings['show_on_cart_page'] ) || merchant_is_cart_block_layout() ) {
			return;
		}

		$position = $settings['cart_position'] ?? 'after_table';
		$hook_map = array(
			'before_table'               => 'woocommerce_before_cart_table',
			'after_table'                => 'woocommerce_after_cart_table',
			'before_proceed_to_checkout' => 'woocommerce_proceed_to_checkout',
			'after_cart_totals'          => 'woocommerce_after_cart_totals',
		);

		add_action( $hook_map[ $position ] ?? 'woocommerce_after_cart_table', array( $this, 'cart_page_output' ) );
	}

	/**
	 * Register the checkout page display hook if enabled and not using block layout.
	 *
	 * @param array<string, mixed> $settings Module settings.
	 *
	 * @return void
	 */
	private function maybe_register_checkout_hook( $settings ) {
		if ( empty( $settings['show_on_checkout_page'] ) || merchant_is_checkout_block_layout() ) {
			return;
		}

		$position = $settings['checkout_position'] ?? 'before_checkout_form';
		$hook_map = array(
			'before_checkout_form'    => 'woocommerce_before_checkout_form',
			'before_customer_details' => 'woocommerce_before_checkout_billing_form',
			'after_customer_details'  => 'woocommerce_after_checkout_billing_form',
			'before_place_order'      => 'woocommerce_review_order_before_submit',
			'after_checkout_form'     => 'woocommerce_after_checkout_form',
		);

		add_action( $hook_map[ $position ] ?? 'woocommerce_before_checkout_form', array( $this, 'checkout_page_output' ), 5 );
	}

	/**
	 * Register the footer display hook if enabled.
	 *
	 * @param array<string, mixed> $settings Module settings.
	 *
	 * @return void
	 */
	private function maybe_register_footer_hook( $settings ) {
		if ( ! empty( $settings['show_on_footer'] ) ) {
			add_action( 'wp_footer', array( $this, 'footer_output' ) );
		}
	}

	/**
	 * Register the archive/shop page display hook if enabled.
	 *
	 * @param array<string, mixed> $settings Module settings.
	 *
	 * @return void
	 */
	private function maybe_register_archive_hook( $settings ) {
		if ( empty( $settings['show_on_archive_pages'] ) ) {
			return;
		}

		$position = $settings['archive_position'] ?? 'after_loop';
		$hook     = 'before_loop' === $position
			? 'woocommerce_before_shop_loop'
			: 'woocommerce_after_shop_loop';

		add_action( $hook, array( $this, 'archive_page_output' ) );
	}

	/**
	 * Add custom image sizes for the merchant module.
	 *
	 * It runs on AJAX requests as it hooks into the `admin_init` hook, so
	 * there is no need to call it again in the `$this->regenerate_images` method.
	 *
	 * @return void
	 */
	public function add_image_sizes() {
		$settings = $this->get_module_settings();
		$width    = (int) ( $settings['image-max-width'] ?? 80 );
		$height   = (int) ( $settings['image-max-height'] ?? 100 );

		add_image_size( 'merchant_payment_logos', $width, $height );
	}

	/**
	 * AJAX handler: regenerate image sizes for specified attachments.
	 *
	 * @return void
	 */
	public function regenerate_images() {
		check_ajax_referer( 'merchant', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You are not allowed to do this.', 'merchant' ) );
		}

		$attachments          = $this->parse_attachment_ids_from_request();
		$is_dimension_changed = filter_var(
			sanitize_text_field( wp_unslash( $_POST['is_dimension_changed'] ?? '' ) ),
			FILTER_VALIDATE_BOOLEAN
		);

		if ( ! function_exists( 'wp_get_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$generated_images = array();

		foreach ( $attachments as $attachment_id ) {
			if ( $this->regenerate_single_attachment( $attachment_id, $is_dimension_changed ) ) {
				$generated_images[] = $attachment_id;
			}
		}

		wp_send_json_success( $generated_images );
	}

	/**
	 * Parse and sanitize attachment IDs from the current POST request.
	 *
	 * @return int[] Array of valid attachment IDs.
	 */
	private function parse_attachment_ids_from_request() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in regenerate_images().
		$raw = isset( $_POST['attachments'] ) && is_array( $_POST['attachments'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['attachments'] ) )
			: array();
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$ids = array();
		foreach ( $raw as $value ) {
			$id = (int) $value;
			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}

		return $ids;
	}

	/**
	 * Regenerate metadata for a single attachment if needed.
	 *
	 * @param int  $attachment_id       The attachment ID.
	 * @param bool $is_dimension_changed Whether dimensions have changed (forces regeneration).
	 *
	 * @return bool True if the attachment was regenerated, false otherwise.
	 */
	private function regenerate_single_attachment( $attachment_id, $is_dimension_changed ) {
		$metadata = wp_get_attachment_metadata( $attachment_id );

		if ( ! $is_dimension_changed && ! empty( $metadata['sizes']['merchant_payment_logos'] ) ) {
			return false;
		}

		$file = get_attached_file( $attachment_id );
		if ( false === $file ) {
			return false;
		}

		$updated_metadata = wp_generate_attachment_metadata( $attachment_id, $file );
		if ( empty( $updated_metadata ) ) {
			return false;
		}

		wp_update_attachment_metadata( $attachment_id, $updated_metadata );

		return true;
	}

	/**
	 * Print shortcode content.
	 *
	 * Validates context then delegates rendering to render_logos() via
	 * render_logos_to_string() to avoid a duplicate render path.
	 *
	 * @return string
	 */
	public function shortcode_handler() {
		if ( ! Merchant_Modules::is_module_active( $this->module_id ) ) {
			return '';
		}

		if ( ! $this->is_shortcode_enabled() ) {
			return '';
		}

		if ( ! is_singular( 'product' ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				return $this->shortcode_placement_error();
			}

			return '';
		}

		$settings   = $this->get_module_settings();
		$product_id = get_the_ID();
		if ( false !== $product_id && ! $this->should_display_on_product( $product_id, $settings ) ) {
			return '';
		}

		$shortcode_content = $this->render_logos_to_string( '', $settings );

		/**
		 * Filter the shortcode html content.
		 *
		 * @param string    $shortcode_content shortcode html content.
		 * @param string    $module_id         module id.
		 * @param int|false $post_id           product id.
		 *
		 * @since 1.8
		 */
		return apply_filters( 'merchant_module_shortcode_content_html', $shortcode_content, $this->module_id, get_the_ID() );
	}

	/**
	 * Init translations.
	 *
	 * @return void
	 */
	public function init_translations() {
		$settings = $this->get_module_settings();
		if ( ! empty( $settings['title'] ) ) {
			Merchant_Translator::register_string( $settings['title'], esc_html__( 'Payment logos text above logos', 'merchant' ) );
		}
	}

	/**
	 * Localize script data.
	 *
	 * Exposes all library icon URLs and the current icon_source to the admin preview JS.
	 *
	 * @since 2.2.8
	 *
	 * @param array<string, mixed> $data Existing localized data.
	 *
	 * @return array<string, mixed>
	 */
	public function localize_script( $data ) {
		$settings = $this->get_module_settings();

		$data['payment_logos'] = array(
			'icon_source'   => $settings['icon_source'] ?? 'gallery',
			'library_icons' => $settings['library_icons'] ?? array( 'visa', 'mastercard', 'paypal' ),
			'icons_url_map' => Merchant_Payment_Icons_Library::get_all_icons(),
		);

		return $data;
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
			wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/payment-logos.min.css', array(), MERCHANT_VERSION );
			wp_enqueue_style( 'merchant-admin-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/admin/preview.min.css', array(), MERCHANT_VERSION );
		}
	}

	/**
	 * Admin enqueue scripts.
	 *
	 * @return void
	 */
	public function admin_enqueue_js() {
		if ( $this->is_module_settings_page() ) {
			wp_enqueue_script( "merchant-{$this->module_id}", MERCHANT_URI . "assets/js/modules/{$this->module_id}/admin/preview.min.js", array( 'jquery' ), MERCHANT_VERSION, true );
		}
	}

	/**
	 * Determine whether the module's assets/CSS should be output on the current page.
	 *
	 * Centralises the repeated page-detection logic shared between enqueue_css()
	 * and frontend_custom_css().
	 *
	 * @since 2.2.8
	 *
	 * @param array<string, mixed> $settings Module settings.
	 *
	 * @return bool
	 */
	private function should_show_on_current_page( $settings ) {
		if ( is_singular( 'product' ) && ! empty( $settings['show_on_single_product'] ) ) {
			return true;
		}

		if ( function_exists( 'is_cart' ) && is_cart() && ! empty( $settings['show_on_cart_page'] ) ) {
			return true;
		}

		if ( function_exists( 'is_checkout' ) && is_checkout() && ! empty( $settings['show_on_checkout_page'] ) ) {
			return true;
		}

		if ( ! empty( $settings['show_on_footer'] ) ) {
			return true;
		}

		if ( ( is_shop() || is_product_taxonomy() ) && ! empty( $settings['show_on_archive_pages'] ) ) {
			return true;
		}

		if ( Merchant_Modules::is_module_active( 'quick-view' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Enqueue CSS.
	 *
	 * @return void
	 */
	public function enqueue_css() {
		$settings = $this->get_module_settings();

		if ( ! $this->should_show_on_current_page( $settings ) ) {
			return;
		}

		wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/payment-logos.min.css', array(), MERCHANT_VERSION );
	}

	/**
	 * Render admin preview
	 *
	 * @param mixed  $preview The admin preview object.
	 * @param string $module  The module ID.
	 *
	 * @return mixed
	 */
	public function render_admin_preview( $preview, $module ) {
		if ( self::MODULE_ID === $module ) {
			ob_start();
			self::admin_preview_content();
			$content = ob_get_clean();

			$preview->set_html( $content );
			$preview->set_text( 'title', '.merchant-payment-logos-title > strong' );

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
				<div class="mrc-preview-text-placeholder mrc-mw-40 mrc-hide-on-smaller-screens"></div>
				<div class="mrc-preview-addtocart-placeholder mrc-hide-on-smaller-screens"></div>
				<?php $this->payment_logos_output(); ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Get logos based on the current icon source setting.
	 *
	 * Returns different value types depending on the icon_source setting:
	 * - 'library' mode  → string[] of absolute icon URLs.
	 * - 'gallery' mode with logos saved → string[] of attachment IDs (numeric strings).
	 * - 'gallery' mode with no logos (placeholder) → string[] of placeholder image URLs.
	 *
	 * @param array<string, mixed>|string $settings_or_logos Module settings array or legacy logos string (deprecated).
	 *
	 * @return string[] Array of URLs or attachment IDs (see above).
	 */
	public function get_logos( $settings_or_logos = array() ) {
		if ( is_string( $settings_or_logos ) ) {
			$settings = array(
				'icon_source' => 'gallery',
				'logos'       => $settings_or_logos,
			);
		} else {
			$settings = $settings_or_logos;
		}

		$icon_source = $settings['icon_source'] ?? 'gallery';


		if ( 'library' === $icon_source ) {
			$selected_keys = $settings['library_icons'] ?? array( 'visa', 'mastercard', 'paypal' );
			if ( ! is_array( $selected_keys ) ) {
				$selected_keys = array( 'visa', 'mastercard', 'paypal' );
			}

			$urls = array();
			foreach ( $selected_keys as $key ) {
				$url = Merchant_Payment_Icons_Library::get_icon_url( $key );
				if ( ! empty( $url ) ) {
					$urls[] = $url;
				}
			}

			return $urls;
		}


		$logos = $settings['logos'] ?? '';

		if ( is_array( $logos ) ) {
			return $logos;
		}

		return ! empty( $logos )
			? array_filter( explode( ',', $logos ) )
			: array(
				MERCHANT_URI . 'inc/modules/' . self::MODULE_ID . '/admin/images/visa.svg',
				MERCHANT_URI . 'inc/modules/' . self::MODULE_ID . '/admin/images/mastercard.svg',
				MERCHANT_URI . 'inc/modules/' . self::MODULE_ID . '/admin/images/paypal.svg',
			);
	}

	/**
	 * Get placeholder logos alternative descriptions.
	 *
	 * @since 2.2.8
	 *
	 * @return array<string, string> Array of logos alternative descriptions.
	 */
	public function get_placeholder_logos_alt_map() {
		return array(
			'visa.svg'   => __( 'Visa', 'merchant' ),
			'mastercard.svg' => __( 'Mastercard', 'merchant' ),
			'paypal.svg'     => __( 'PayPal', 'merchant' ),
		);
	}

	/**
	 * Check if payment logos should display on a specific product.
	 *
	 * @since 2.2.8
	 *
	 * @param int                  $product_id The product ID to check.
	 * @param array<string, mixed> $settings   Module settings (fetched if not provided).
	 *
	 * @return bool True if logos should display, false if excluded.
	 */
	public function should_display_on_product( $product_id, $settings = array() ) {
		$product_id = (int) $product_id;

		if ( empty( $settings ) ) {
			$settings = $this->get_module_settings();
		}


		if ( ! empty( $settings['exclude_products_toggle'] ) && ! empty( $settings['excluded_products'] ) ) {
			$excluded_products = is_array( $settings['excluded_products'] )
				? $settings['excluded_products']
				: array( $settings['excluded_products'] );

			if ( in_array( $product_id, array_map( 'intval', $excluded_products ), true ) ) {
				return false;
			}
		}


		if ( ! empty( $settings['exclude_categories_toggle'] ) && ! empty( $settings['excluded_categories'] ) ) {
			$excluded_categories = is_array( $settings['excluded_categories'] )
				? $settings['excluded_categories']
				: array( $settings['excluded_categories'] );

			$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'slugs' ) );

			if ( ! is_wp_error( $product_categories ) && ! empty( array_intersect( $product_categories, $excluded_categories ) ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the inline style attribute for the container.
	 *
	 * @since 2.2.8
	 *
	 * @param array<string, mixed> $settings Module settings.
	 *
	 * @return string The style attribute string.
	 */
	private function get_container_style_attr( $settings ) {
		$styles = array();

		$bg_color = $settings['container_bg_color'] ?? 'transparent';
		if ( 'transparent' !== $bg_color && ! empty( $bg_color ) ) {
			$styles[] = 'background-color:' . esc_attr( $bg_color );
		}

		$padding = (int) ( $settings['container_padding'] ?? 0 );
		if ( $padding > 0 ) {
			$styles[] = 'padding:' . $padding . 'px';
		}

		$border_radius = (int) ( $settings['container_border_radius'] ?? 0 );
		if ( $border_radius > 0 ) {
			$styles[] = 'border-radius:' . $border_radius . 'px';
		}

		$border_color = $settings['container_border_color'] ?? 'transparent';
		$border_width = (int) ( $settings['container_border_width'] ?? 0 );
		if ( $border_width > 0 && 'transparent' !== $border_color && ! empty( $border_color ) ) {
			$styles[] = 'border:' . $border_width . 'px solid ' . esc_attr( $border_color );
		}

		if ( empty( $styles ) ) {
			return '';
		}

		return 'style="' . esc_attr( implode( ';', $styles ) ) . '"';
	}

	/**
	 * Render the payment logos markup.
	 *
	 * Shared render method used by all output hooks.
	 *
	 * @since 2.2.8
	 *
	 * @param string               $location_class Optional CSS class for the location context.
	 * @param array<string, mixed> $settings       Module settings (fetched if not provided).
	 *
	 * @return void
	 */
	private function render_logos( $location_class = '', $settings = array() ) {
		if ( empty( $settings ) ) {
			$settings = $this->get_module_settings();
		}

		$icon_source    = $settings['icon_source'] ?? 'gallery';
		$is_placeholder = 'gallery' === $icon_source && empty( $settings['logos'] );
		$logos          = $this->get_logos( $settings );

		$container_classes = 'merchant-payment-logos';
		if ( ! empty( $location_class ) ) {
			$container_classes .= ' ' . $location_class;
		}
		?>

		<div class="<?php echo esc_attr( $container_classes ); ?>" <?php echo $this->get_container_style_attr( $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

			<?php if ( ! empty( $settings['title'] ) ) : ?>
				<div class="merchant-payment-logos-title">
					<strong><?php echo esc_html( Merchant_Translator::translate( $settings['title'] ) ); ?></strong>
				</div>
			<?php endif; ?>

			<?php
			if ( 'library' === $icon_source ) :
				$this->render_library_images( $logos );
			elseif ( ! $is_placeholder ) :
				$this->render_gallery_images( $logos );
			else :
				$this->render_placeholder_images( $logos );
			endif;
			?>

		</div>

		<?php
	}

	/**
	 * Render the library icon images block.
	 *
	 * @since 2.2.8
	 *
	 * @param string[] $logos Array of icon URLs from the built-in library.
	 *
	 * @return void
	 */
	private function render_library_images( $logos ) {
		?>
		<div class="merchant-payment-logos-images is-library">
			<?php
			foreach ( $logos as $icon_url ) :
				$icon_key = $this->get_icon_key_from_url( $icon_url );
				$alt_text = ! empty( $icon_key ) ? Merchant_Payment_Icons_Library::get_icon_label( $icon_key ) : '';
				?>
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $alt_text ); ?>" />
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render the gallery (media-library) images block.
	 *
	 * @since 2.2.8
	 *
	 * @param string[] $logos Array of attachment IDs (numeric strings).
	 *
	 * @return void
	 */
	private function render_gallery_images( $logos ) {
		?>
		<div class="merchant-payment-logos-images">
			<?php
			foreach ( $logos as $image_id ) {
				echo wp_kses_post( wp_get_attachment_image( (int) $image_id, 'merchant_payment_logos' ) );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render the placeholder images block (shown when no gallery logos are saved).
	 *
	 * @since 2.2.8
	 *
	 * @param string[] $logos Array of placeholder image URLs.
	 *
	 * @return void
	 */
	private function render_placeholder_images( $logos ) {
		$alt_map = $this->get_placeholder_logos_alt_map();
		?>
		<div class="merchant-payment-logos-images is-placeholder">
			<?php
			foreach ( $logos as $logo_src ) :
				$basename = basename( $logo_src );
				$logo_alt = isset( $alt_map[ $basename ] ) ? $alt_map[ $basename ] : '';
				?>
				<img src="<?php echo esc_url( $logo_src ); ?>" alt="<?php echo esc_attr( $logo_alt ); ?>" />
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Extract the icon key from a library icon URL.
	 *
	 * @since 2.2.8
	 *
	 * @param string $url The icon URL.
	 *
	 * @return string The icon key, or empty string if not found.
	 */
	private function get_icon_key_from_url( $url ) {
		$filename = basename( $url, '.svg' );

		if ( Merchant_Payment_Icons_Library::has_icon( $filename ) ) {
			return $filename;
		}

		return '';
	}

	/**
	 * Render payment logos to a string (for WC Blocks injection).
	 *
	 * Used by Merchant_Payment_Logos_Blocks_Integration to capture
	 * the rendered HTML without echoing it.
	 *
	 * @since 2.2.8
	 *
	 * @param string               $location_class CSS location modifier class.
	 * @param array<string, mixed> $settings       Module settings (fetched if not provided).
	 *
	 * @return string
	 */
	public function render_logos_to_string( $location_class = '', $settings = array() ) {
		ob_start();
		$this->render_logos( $location_class, $settings );

		return (string) ob_get_clean();
	}

	/**
	 * Render payment logos on the single product page.
	 * TODO: Render through template files.
	 *
	 * @return void
	 */
	public function payment_logos_output() {
		if ( $this->is_shortcode_enabled() ) {
			return;
		}

		if ( is_archive() || is_page() ) {
			return;
		}

		$settings   = $this->get_module_settings();
		$product_id = get_the_ID();
		if ( false !== $product_id && ! $this->should_display_on_product( $product_id, $settings ) ) {
			return;
		}

		$this->render_logos( '', $settings );
	}

	/**
	 * Render payment logos on the cart page.
	 *
	 * @since 2.2.8
	 *
	 * @return void
	 */
	public function cart_page_output() {
		$this->render_logos( 'merchant-payment-logos--cart' );
	}

	/**
	 * Render payment logos on the checkout page.
	 *
	 * @since 2.2.8
	 *
	 * @return void
	 */
	public function checkout_page_output() {
		$this->render_logos( 'merchant-payment-logos--checkout' );
	}

	/**
	 * Render payment logos in the footer.
	 *
	 * @since 2.2.8
	 *
	 * @return void
	 */
	public function footer_output() {
		$this->render_logos( 'merchant-payment-logos--footer' );
	}

	/**
	 * Render payment logos on archive / shop pages.
	 *
	 * @since 2.2.8
	 *
	 * @return void
	 */
	public function archive_page_output() {
		$this->render_logos( 'merchant-payment-logos--archive' );
	}

	/**
	 * Custom CSS.
	 *
	 * @return string
	 */
	public function get_module_custom_css() {
		$css = '';

		// Font Size.
		$css .= Merchant_Custom_CSS::get_variable_css( 'payment-logos', 'font-size', '18', '.merchant-payment-logos', '--mrc-plogos-font-size', 'px' );

		// Text Color.
		$css .= Merchant_Custom_CSS::get_variable_css( 'payment-logos', 'text-color', '#212121', '.merchant-payment-logos', '--mrc-plogos-text-color' );

		// Margin Top.
		$css .= Merchant_Custom_CSS::get_variable_css( 'payment-logos', 'margin-top', '20', '.merchant-payment-logos', '--mrc-plogos-margin-top', 'px' );

		// Margin Bottom.
		$css .= Merchant_Custom_CSS::get_variable_css( 'payment-logos', 'margin-bottom', '20', '.merchant-payment-logos', '--mrc-plogos-margin-bottom', 'px' );

		// Align.
		$css .= Merchant_Custom_CSS::get_variable_css( 'payment-logos', 'align', 'flex-start', '.merchant-payment-logos', '--mrc-plogos-align' );

		// Image Max Width.
		$css .= Merchant_Custom_CSS::get_variable_css( 'payment-logos', 'image-max-width', '80', '.merchant-payment-logos', '--mrc-plogos-image-max-width', 'px' );

		// Image Max Height.
		$css .= Merchant_Custom_CSS::get_variable_css( 'payment-logos', 'image-max-height', '100', '.merchant-payment-logos', '--mrc-plogos-image-max-height', 'px' );

		return $css;
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
	 *
	 * @return string
	 */
	public function frontend_custom_css( $css ) {
		$settings = $this->get_module_settings();

		if ( ! $this->should_show_on_current_page( $settings ) ) {
			return $css;
		}

		$css .= $this->get_module_custom_css();

		return $css;
	}
}

// Load the icon library.
require_once MERCHANT_DIR . 'inc/modules/payment-logos/class-payment-icons-library.php';

// Initialize the module.
add_action( 'init', function() {
	Merchant_Modules::create_module( new Merchant_Payment_Logos() );
} );
