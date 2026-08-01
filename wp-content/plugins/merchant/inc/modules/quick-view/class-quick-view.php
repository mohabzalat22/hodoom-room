<?php

/**
 * Quick View.
 * 
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Quick View Class.
 * 
 */
class Merchant_Quick_View extends Merchant_Add_Module {

	/**
	 * Module ID.
	 *
	 */
	const MODULE_ID = 'quick-view';

	/**
	 * Module templates path.
	 *
	 */
	const MODULE_TEMPLATES = 'modules/' . self::MODULE_ID;

	/**
	 * Is module preview.
	 * 
	 */
	public static $is_module_preview = false;

	/**
	 * Whether the module has a shortcode or not.
	 *
	 * @var bool
	 */
	public $has_shortcode = true;

	private static $instance = null;

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
		$this->module_section = 'convert-more';

		// Module default settings.
		$this->module_default_settings = array(
			'button_type'                  => 'text',
			'button_text'                  => __( 'Quick View', 'merchant' ),
			'button_icon'                  => 'eye',
			'button_position'              => 'overlay',
			'button-position-top'          => 50,
			'button-position-left'         => 50,
			'mobile_position'              => false,
			'button-position-top-mobile'   => 50,
			'button-position-left-mobile'  => 50,
			'modal_layout'                 => 'modal',
			'show_navigation_arrows'       => 0,
			'sync_url_hash'                => 0,
			'zoom_effect'                  => 1,
			'show_quantity'                => 1,
			'place_product_description'    => 'top',
			'description_style'            => 'short',
			'place_product_image'          => 'thumbs-at-left',
            'show_buy_now_button'          => false,
            'show_suggested_products'      => true,
            'suggested_products_module'    => 'bulk_discounts',
            'suggested_products_placement' => 'after_add_to_cart',
		);

		// Module data.
		$this->module_data = Merchant_Admin_Modules::$modules_data[ self::MODULE_ID ];

		// AI prompt examples.
		$this->ai_examples = array(
			'blurb'   => esc_html__( 'See what AI can do for your Quick View popup, from restyling the button to rearranging what shows inside the modal.', 'merchant' ),
			'prompts' => array(
				esc_html__( "Explore the abilities WPVibe gives you access to, then set the Quick View button to show an icon and text, label it 'View Details', and use the cart icon", 'merchant' ),
				esc_html__( 'Check what abilities you have available through WPVibe, then overlay the Quick View button (labeled View Details) on the product image, positioned 20% from the top and 80% from the left', 'merchant' ),
				esc_html__( 'Look at your available WPVibe abilities, then widen the quick view modal to 1200px, move the gallery thumbnails to the bottom, and show the full product description', 'merchant' ),
				esc_html__( 'See what abilities WPVibe exposes, then enable ajax add to cart in the quick view popup and hide the quantity selector', 'merchant' ),
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

		if ( Merchant_Modules::is_module_active( self::MODULE_ID ) && is_admin() ) {
			// Init translations.
			$this->init_translations();
		}

		if ( ! Merchant_Modules::is_module_active( self::MODULE_ID ) ) {
			return;
		}

		// Return early if it's on admin but not in the respective module settings page.
		if ( is_admin() && ! wp_doing_ajax() && ! parent::is_module_settings_page() ) {
			return;
		}

		// Enqueue styles.
		add_action( 'merchant_enqueue_before_main_css_js', array( $this, 'enqueue_css' ) );

		// Enqueue scripts.
		add_action( 'merchant_enqueue_after_main_css_js', array( $this, 'enqueue_scripts' ) );

		// Localize script.
		add_filter( 'merchant_localize_script', array( $this, 'localize_script' ) );

		// Handle Botiga theme scripts for compatibility.
		if ( defined( 'BOTIGA_PRO_URI' ) && defined( 'BOTIGA_PRO_VERSION' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'modal_compatibility_with_botiga_theme' ) );
		}

		// Button Position.
        if ( ! $this->is_shortcode_enabled() ) {
	        $button_position = Merchant_Admin_Options::get( self::MODULE_ID, 'button_position', 'overlay' );

	        if ( 'before' === $button_position ) {
		        add_action( 'woocommerce_after_shop_loop_item', array( $this, 'quick_view_button' ), 5 );
	        } elseif ( 'after' === $button_position ) {
		        add_action( 'woocommerce_after_shop_loop_item', array( $this, 'quick_view_button' ), 15 );
	        } elseif ( 'overlay' === $button_position ) {
		        if ( merchant_is_kadence_active() ) {
                    add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'quick_view_button' ), 35 );
                    //add_filter( 'kadence_archive_content_wrap_start', array( $this, 'add_quick_view_button' ) );
		        } elseif ( merchant_is_blocksy_active() ) {
                    add_action( 'blocksy:woocommerce:product-card:thumbnail:end', array( $this, 'quick_view_button' ) );
                } elseif ( merchant_is_botiga_active() ) {
                    add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'quick_view_button' ) );
		        } elseif ( merchant_is_oceanwp_active() ) {
                    add_action( 'ocean_after_archive_product_image', array( $this, 'quick_view_button' ) );
		        } elseif ( merchant_is_flatsome_active() ) {
                    add_action( 'flatsome_woocommerce_shop_loop_images', array( $this, 'quick_view_button' ) );
		        } elseif ( merchant_is_storefront_active() ) {
			        remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
			        add_action( 'woocommerce_before_shop_loop_item_title', function () {
				        echo '<div class="merchant-storefront-thumbnail-wrapper">';
				        woocommerce_template_loop_product_thumbnail();
                        $this->quick_view_button();
				        echo '</div>';
			        } );
		        } elseif ( merchant_is_astra_active() ) {
			        add_action( 'woocommerce_after_shop_loop_item', array( $this, 'quick_view_button' ), 7 );
		        } else {
			        add_action( 'woocommerce_after_shop_loop_item', array( $this, 'quick_view_button' ) );
		        }
	        }
        }

		// Inject quick view modal output on footer.
		add_action( 'wp_footer', array( $this, 'modal_output' ) );

        // Show Suggested Module
        $suggested_placement = Merchant_Admin_Options::get( self::MODULE_ID, 'suggested_products_placement', 'after_add_to_cart' );
        add_action( 'merchant_quick_view_' . $suggested_placement, array( $this, 'render_suggested_module_content' ), 10, 2 );

        // Let other modules (e.g. Pro asset managers) ask which suggested-products
        // module is active without depending on Quick View's own option keys.
        add_filter( 'merchant_quick_view_suggested_products_module', array( $this, 'get_active_suggested_products_module' ) );

        // Show Buy Now Module
        add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'render_buy_now_button' ) );

		// Custom CSS.
		add_filter( 'merchant_custom_css', array( $this, 'frontend_custom_css' ) );

		// Modal content ajax callback.
		add_action( 'wp_ajax_merchant_quick_view_content', array( $this, 'modal_content_ajax_callback' ) );
		add_action( 'wp_ajax_nopriv_merchant_quick_view_content', array( $this, 'modal_content_ajax_callback' ) );

		// Initialize AJAX add to cart handler (separate class).
		$this->init_ajax_add_to_cart();
	}

	/**
	 * Initialize AJAX add to cart functionality.
	 * 
	 * @return void
	 */
	private function init_ajax_add_to_cart() {
		Merchant_Quick_View_Ajax_Add_To_Cart::get_instance()->init();
	}

	/**
     * Singleton
     *
	 * @return self|null
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Init translations.
	 *
	 * @return void
	 */
	public function init_translations() {
		$settings = $this->get_module_settings();
		if ( ! empty( $settings['button_text'] ) ) {
			Merchant_Translator::register_string( $settings['button_text'], esc_html__( 'Quick view button text', 'merchant' ) );
		}
	}

	/**
	 * Print shortcode content.
	 *
	 * @return string
	 */
	public function shortcode_handler() {
		// Check if module is active.
		if ( ! Merchant_Modules::is_module_active( $this->module_id ) ) {
			return '';
		}

		// Check if shortcode is enabled.
		if ( ! $this->is_shortcode_enabled() ) {
			return '';
		}

        global $product;

        $product_id = is_object( $product ) ? $product->get_id() : get_the_ID();

        ob_start();
		$this->quick_view_button( 'shortcode' );
		$shortcode_content = ob_get_clean();

		/**
		 * Filter the shortcode html content.
		 *
		 * @param string $shortcode_content shortcode html content
		 * @param string $module_id         module id
		 * @param int    $post_id           product id
		 *
		 * @since 1.8
		 */
		return apply_filters( 'merchant_module_shortcode_content_html', $shortcode_content, $this->module_id, $product_id );
	}

	/**
     * Concatenate the quick view button with the content start wrap.
     *
	 * @return string
	 */
	public function add_quick_view_button() {
		return $this->quick_view_button() . '<div class="product-details content-bg entry-content-wrap">';
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
			wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/quick-view.min.css', array(), MERCHANT_VERSION );
			wp_enqueue_style( 'merchant-admin-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/admin/preview.min.css', array(), MERCHANT_VERSION );
		}
	}

	/**
	 * Enqueue CSS.
	 * 
	 * @return void
	 */
	public function enqueue_css() {

		// Specific module styles.
		wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/quick-view.min.css', array(), MERCHANT_VERSION );
	}

	/**
	 * Enqueue scripts.
	 * 
	 * @return void
	 */
	public function enqueue_scripts() {
        if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '10.3', '>=' ) ) {
            wp_enqueue_script( 'wc-zoom' );
            wp_enqueue_script( 'wc-flexslider' );
        } else {
            wp_enqueue_script( 'zoom' );
            wp_enqueue_script( 'flexslider' );
        }
		wp_enqueue_script( 'wc-single-product' );
		wp_enqueue_script( 'wc-add-to-cart-variation' );

		// Register and enqueue the main module script.
		wp_enqueue_script( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/quick-view.min.js', array(), MERCHANT_VERSION, true );
	}

	/**
	 * Localize script with module settings.
	 * 
	 * @param array $setting The merchant global object setting parameter.
	 * @return array $setting The merchant global object setting parameter.
	 */
	public function localize_script( $setting ) {
		$module_settings = $this->get_module_settings();

		$setting[ 'quick_view' ]       = true;
		$setting[ 'quick_view_zoom' ]  = $module_settings[ 'zoom_effect' ];
		$setting[ 'ajax_add_to_cart' ] = ! empty( $module_settings[ 'ajax_add_to_cart' ] );
		$setting[ 'sync_url_hash' ]    = ! empty( $module_settings[ 'sync_url_hash' ] );

		return $setting;
	}

	/**
	 * Enqueue botiga theme scripts.
	 * 
	 * @return void
	 */
	public function modal_compatibility_with_botiga_theme() {
		if ( ! wp_script_is( 'botiga-product-swatch' ) ) {
			wp_enqueue_script( 'botiga-product-swatch', BOTIGA_PRO_URI . 'assets/js/botiga-product-swatch.min.js', array(), BOTIGA_PRO_VERSION, true );
		}
	
		if ( ! wp_script_is( 'botiga-checkout-quantity-input' ) ) {
			wp_enqueue_script( 'botiga-checkout-quantity-input', BOTIGA_PRO_URI . 'assets/js/botiga-checkout-quantity-input.min.js', array( 'jquery' ), BOTIGA_PRO_VERSION, true );
		}
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
			$preview->set_html( $content );

			// Button Type.
			$preview->set_class( 'button_type', '.merchant-quick-view-button', array( 'icon', 'icon-text', 'text' ) );

			// Button Text.
			$preview->set_text( 'button_text', '.button-text' );

			// Button Icon.
			$preview->set_svg_icon( 'button_icon', '.quick-view-icon' );

			// Button Position.
			$preview->set_class( 'button_position', '.merchant-quick-view-preview', array( 'before', 'after', 'overlay' ) );

		}

		return $preview;
	}

	/**
	 * Admin preview content.
	 * 
	 * @return void
	 */
	public function admin_preview_content() {
		$settings = $this->get_module_settings();

		?>

		<div class="merchant-quick-view-preview <?php echo esc_attr( $settings[ 'button_position' ] ); ?>">
			<div class="image-wrapper">
				<div class="button-position button-position-overlay">
					<?php $this->admin_preview_quick_view_button(); ?>
				</div>
			</div>
			<h3><?php echo esc_html__( 'Product Title', 'merchant' ); ?></h3>
			<p><?php echo esc_html__( 'The product description normally is displayed here.', 'merchant' ); ?></p>
			<div class="button-position button-position-before">
				<?php $this->admin_preview_quick_view_button(); ?>
			</div>
			<div>
				<a href="#" class="add_to_cart_button"><?php echo esc_html__( 'Add To Cart', 'merchant' ); ?></a>
			</div>
			<div class="button-position button-position-after">
				<?php $this->admin_preview_quick_view_button(); ?>
			</div>

		</div>

		<?php
	}

	/**
	 * Admin preview quick view button.
	 * 
	 * @return void
	 */
	public function admin_preview_quick_view_button() {
		$settings = $this->get_module_settings();

		?>

		<a href="#" class="merchant-quick-view-button <?php echo esc_attr( $settings[ 'button_type' ] ); ?>">
			<span class="button-type button-type-icon">
				<span class="quick-view-icon">
					<?php echo wp_kses( Merchant_SVG_Icons::get_svg_icon( $settings[ 'button_icon' ] ), merchant_kses_allowed_tags( array(), false ) ); ?>
				</span>
			</span>
			<span class="button-type button-type-icon-text">
				<span class="quick-view-icon">
					<?php echo wp_kses( Merchant_SVG_Icons::get_svg_icon( $settings[ 'button_icon' ] ), merchant_kses_allowed_tags( array(), false ) ); ?>
				</span>
				<span class="button-text"><?php echo esc_html( $settings[ 'button_text' ] ); ?></span>
			</span>
			<span class="button-type button-type-text">
				<span class="button-text"><?php echo esc_html( $settings[ 'button_text' ] ); ?></span>
			</span>
		</a>

		<?php
	}

	/**
	 * Modal content ajax callback.
	 *
	 * @return void
	 */
	public function modal_content_ajax_callback() {
		check_ajax_referer( 'merchant-nonce', 'nonce' );

		if ( ! isset( $_POST['product_id'] ) || ! function_exists( 'wc_get_product' ) ) {
			wp_send_json_error();
		}

		$args = array(
			'product_id' => absint( $_POST['product_id'] ),
		);

		global $product, $post;

		$settings = $this->get_module_settings();
		$product  = wc_get_product( $args['product_id'] );

		if ( is_wp_error( $product ) || empty( $product ) ) {
			wp_send_json_error();
		}

		// Mirror the real single-product page's main-query post so functions like
		// `get_the_ID()` (used by other modules hooking into `merchant_quick_view_product_summary`,
		// e.g. Payment Logos' exclusion checks) resolve correctly during this AJAX request.
		$post = get_post( $args['product_id'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		$content = merchant_get_template_part(
			self::MODULE_TEMPLATES,
			'content',
			array(
				'product'  => $product,
				'settings' => $settings,
			),
			true
		);

		wp_send_json_success( $content );
	}


	/**
	 * Modal output.
	 *
	 * @return void
	 */
	public function modal_output() {
		$settings = $this->get_module_settings();
		$template = ( 'side-panel' === $settings[ 'modal_layout' ] ) ? 'side-panel' : 'modal';

		merchant_get_template_part( self::MODULE_TEMPLATES, $template, array( 'settings' => $settings ) );
	}

	/**
	 * Quick view button.
	 * 
	 * @return void
	 */
	public function quick_view_button( $context = '' ) {
		global $product;

        if ( ! is_object( $product ) ) {
            return;
        }

		$settings   = $this->get_module_settings();
		$product_id = $product->get_id();

		$button_text_html = '';
		$button_icon_html = '';

		if ( 'icon' === $settings[ 'button_type' ] || 'icon-text' === $settings[ 'button_type' ] ) {
			$button_icon_html = Merchant_SVG_Icons::get_svg_icon( $settings[ 'button_icon' ] );
		}

		if ( 'text' === $settings[ 'button_type' ] || 'icon-text' === $settings[ 'button_type' ] ) {
			$button_text_html = '<span>' . Merchant_Translator::translate( $settings[ 'button_text' ] ) . '</span>';
		}

        $classes  = 'button wp-element-button merchant-quick-view-button';
        $classes .= $context !== 'shortcode' ? ' merchant-quick-view-position-' . ( $settings[ 'button_position' ] ?? '' ) : '';
        $classes .= ( $context !== 'shortcode' && ! empty( $settings['mobile_position'] ) ) ? ' merchant-quick-view-position-has-mobile-position' : '';
		?>
        <button class="<?php echo esc_attr( $classes ); ?>" data-product-id="<?php echo absint( $product_id ); ?>" type="button">
            <?php echo wp_kses( $button_icon_html, merchant_kses_allowed_tags( array(), false ) ) . wp_kses_post( $button_text_html ); ?>
        </button>
		<?php
	}

	/**
     * Renders the Buy Now button.
     *
	 * @return void
	 */
	public function render_buy_now_button() {
		if ( ! Merchant_Modules::is_module_active( Merchant_Buy_Now::MODULE_ID ) ) {
			return;
		}

		// Don't include on Single Product
		if ( ! did_action( 'merchant_quick_view_before_add_to_cart' ) ) {
			return;
		}

		$show_buy_now = (bool) Merchant_Admin_Options::get( $this->module_id, 'show_buy_now_button', false );
        if ( ! $show_buy_now ) {
            return;
        }

		global $product;

		/**
		 * Filters whether the Buy Now button should be excluded for a specific product in Quick View.
		 *
		 * This filter allows modules (like Buy Now) to control the visibility of the Buy Now button
		 * in the Quick View modal based on their own exclusion rules.
		 *
		 * @param bool       $is_excluded Whether the product is excluded. Default false.
		 * @param WC_Product $product     The product object.
		 *
		 * @since 2.2.4
		 */
		if ( apply_filters( 'merchant_buy_now_is_excluded', false, $product ) ) {
			return;
		}

		$text = Merchant_Admin_Options::get( Merchant_Buy_Now::MODULE_ID, 'button-text', esc_html__( 'Buy Now', 'merchant' ) );

		$_wrapper_classes   = array();
		$_wrapper_classes[] = $product->get_type() === 'variable' ? 'disabled' : '';

		/**
		 * Hook 'merchant_module_buy_now_wrapper_class'
		 *
		 * @since 1.8
		 */
		$wrapper_classes = apply_filters( 'merchant_module_buy_now_wrapper_class', $_wrapper_classes );
		?>
        <!-- Don't define type="submit" because it creates issue with block themes. The button is inside the form, so by default the type is already "submit". -->
        <button name="merchant-buy-now" value="<?php echo absint( $product->get_ID() ); ?>" class="button alt wp-element-button merchant-buy-now-button <?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"><?php echo esc_html( Merchant_Translator::translate( $text ) ); ?></button>
		<?php
    }

	/**
     * Renders the suggested module content based on the provided settings.
     *
	 * @param $product
	 * @param $settings
	 *
	 * @return void
	 */
	public function render_suggested_module_content( $product, $settings ) {
		$show_suggested = (bool) Merchant_Admin_Options::get( $this->module_id, 'show_suggested_products', true );

		if ( ! $show_suggested || empty( $product ) ) {
			return;
		}

		$suggested_module = $settings['suggested_products_module'] ?? 'bulk_discounts';

		switch ( $suggested_module ) {
			case 'bulk_discounts':
				$this->print_bulk_discounts( $product );
				break;

			case 'buy_x_get_y':
				$this->print_buy_x_get_y( $product );
				break;

			case 'frequently_bought_together':
				$this->print_frequently_bought_together( $product );
				break;
		}
	}

	/**
	 * Gets the currently configured suggested-products module.
	 *
	 * Filter callback for `merchant_quick_view_suggested_products_module`, so
	 * other modules can check which module is active without reading Quick
	 * View's option keys directly.
	 *
	 * @param string|false $default_value Default value passed through the filter.
	 *
	 * @return string|false The active module slug, or false if suggested products are disabled.
	 */
	public function get_active_suggested_products_module( $default_value = false ) {
		if ( ! Merchant_Admin_Options::get( self::MODULE_ID, 'show_suggested_products', true ) ) {
			return false;
		}

		return Merchant_Admin_Options::get( self::MODULE_ID, 'suggested_products_module', 'bulk_discounts' );
	}

	/**
     * Prints the bulk discounts for the specified product.
     *
	 * @param $product
	 *
	 * @return void
	 */
	public function print_bulk_discounts( $product ) {
		if ( ! merchant_is_pro_active() || ! Merchant_Modules::is_module_active( Merchant_Volume_Discounts::MODULE_ID ) ) {
			return;
		}

		$product_id     = $product->get_id();
		$discount_tiers = Merchant_Pro_Volume_Discounts::availabe_offers( $product_id );

        if ( ! empty( $discount_tiers ) ) {
	        merchant_get_template_part(
		        Merchant_Volume_Discounts::MODULE_TEMPLATES_PATH,
		        'single-product',
		        array(
			        'settings'              => Merchant_Admin_Options::get_all( Merchant_Volume_Discounts::MODULE_ID ),
			        'product'               => $product,
			        'discount_tiers'        => $discount_tiers,
			        'product_price'         => $product->get_price(),
			        'in_cart'               => Merchant_Pro_Volume_Discounts::is_in_cart( $product_id ),
			        'product_cart_quantity' => Merchant_Pro_Volume_Discounts::get_product_cart_quantity( $product_id ),
			        'product_id'            => $product_id,
		        )
	        );
        }
	}

	/**
     * Prints the Buy X Get Y offers for the specified product.
     *
	 * @param $product
	 *
	 * @return void
	 */
	public function print_buy_x_get_y( $product ) {
		if ( ! merchant_is_pro_active() || ! Merchant_Modules::is_module_active( Merchant_Buy_X_Get_Y::MODULE_ID ) ) {
			return;
		}

		$eligibility = Merchant_Pro_BXGY_Service_Provider::eligibility();

		if ( ! $eligibility ) {
			return;
		}

		$product_id = $product->get_id();
		$offers     = $eligibility->get_eligible_for_product( $product_id );

		if ( ! empty( $offers ) ) {
			merchant_get_template_part(
				Merchant_Buy_X_Get_Y::MODULE_TEMPLATES,
				'single-product',
				array(
					'offers'    => $offers,
					'nonce'     => wp_create_nonce( 'merchant_bogo_add_to_cart' ),
					'settings'  => Merchant_Admin_Options::get_all( Merchant_Buy_X_Get_Y::MODULE_ID ),
					'product'   => $product_id,
					'module_id' => Merchant_Buy_X_Get_Y::MODULE_ID,
				)
			);
		}
	}

	/**
     * Prints the frequently bought together bundles for the specified product.
     *
	 * @param $product
	 *
	 * @return void
	 */
	public function print_frequently_bought_together( $product ) {
		if ( ! merchant_is_pro_active() || ! Merchant_Modules::is_module_active( Merchant_Frequently_Bought_Together::MODULE_ID ) ) {
			return;
		}

		$post_id        = $product->get_id();
		$repository     = new Merchant_Pro_FBT_Offer_Repository( Merchant_Frequently_Bought_Together::MODULE_ID );
		$price_calc     = new Merchant_Pro_FBT_Price_Calculator( Merchant_Frequently_Bought_Together::MODULE_ID );
		$bundle_mapper  = new Merchant_Pro_FBT_Bundle_Mapper( $price_calc );
		$offer_resolver = new Merchant_Pro_FBT_Offer_Resolver( $repository, $bundle_mapper );
		$bundles        = $offer_resolver->available_offers( $post_id );

		if ( ! empty( $bundles ) ) {
			merchant_get_template_part(
				Merchant_Frequently_Bought_Together::MODULE_TEMPLATES_PATH,
				'single-product-v3',
				array(
					'bundle'    => reset( $bundles ),
					'nonce'     => wp_create_nonce( Merchant_Pro_Frequently_Bought_Together::AJAX_NONCE_ACTION ),
					'settings'  => Merchant_Admin_Options::get_all( Merchant_Frequently_Bought_Together::MODULE_ID ),
					'module_id' => Merchant_Frequently_Bought_Together::MODULE_ID,
				)
			);
		}
	}
	
	/**
	 * Custom CSS.
	 * 
	 * @return string
	 */
	public function get_module_custom_css() {
		$css = '';

		// Button Icon Color.
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'icon-color', '#ffffff', '.merchant-quick-view-button', '--mrc-qv-button-icon-color' );

		// Button Icon Color (hover).
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'icon-hover-color', '#ffffff', '.merchant-quick-view-button', '--mrc-qv-button-icon-color-hover' );

		// Button Text Color.
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'text-color', '#ffffff', '.merchant-quick-view-button', '--mrc-qv-button-text-color' );

		// Button Text Color (hover).
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'text-hover-color', '#ffffff', '.merchant-quick-view-button', '--mrc-qv-button-text-color-hover' );

		// Button Border Color.
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'border-color', '#212121', '.merchant-quick-view-button', '--mrc-qv-button-border-color' );

		// Button Border Color (hover).
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'border-hover-color', '#414141', '.merchant-quick-view-button', '--mrc-qv-button-border-color-hover' );

		// Button Background Color.
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'background-color', '#212121', '.merchant-quick-view-button', '--mrc-qv-button-bg-color' );

		// Button Background Color (hover).
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'background-hover-color', '#414141', '.merchant-quick-view-button', '--mrc-qv-button-bg-color-hover' );

		// Button Position Top.
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'button-position-top', '50', '.merchant-quick-view-button', '--mrc-qv-button-position-top', '%' );

		// Button Position Top (Mobile).
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'button-position-top-mobile', '50', '.merchant-quick-view-button', '--mrc-qv-button-position-top-mobile', '%' );

		// Button Position Left.
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'button-position-left', '50', '.merchant-quick-view-button', '--mrc-qv-button-position-left', '%' );

		// Button Position Left (Mobile).
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'button-position-left-mobile', '50', '.merchant-quick-view-button', '--mrc-qv-button-position-left-mobile', '%' );

		// Modal Width.
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'modal_width', 1000, '.merchant-quick-view-modal', '--mrc-qv-modal-width', 'px' );

		// Modal Height.
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'modal_height', 500, '.merchant-quick-view-modal', '--mrc-qv-modal-height', 'px' );

		// Side Panel Width.
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'side_panel_width', 420, '.merchant-quick-view-modal', '--mrc-qv-panel-width', 'px' );

		// Sale Price Color.
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'sale-price-color', '#212121', '.merchant-quick-view-modal', '--mrc-qv-modal-sale-price-color' );

		// Regular Price Color.
		$css .= Merchant_Custom_CSS::get_variable_css( 'quick-view', 'regular-price-color', '#414141', '.merchant-quick-view-modal', '--mrc-qv-modal-regular-price-color' );

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
	 * @return string $css The custom CSS.
	 */
	public function frontend_custom_css( $css ) {
		$css .= $this->get_module_custom_css();

		$theme      = wp_get_theme();
		$theme_name = $theme->get( 'Name' );

		if ( 'Astra' === $theme_name ) {
			$css .= '
                .astra-shop-thumbnail-wrap {
                    position: relative;
                }
                .astra-shop-thumbnail-wrap img {
                    width: 100%;
                }
            ';
        }

		if ( 'Storefront' === $theme_name ) {
			$css .= '
                .merchant-storefront-thumbnail-wrapper {
                    position: relative;
                }
            ';
        }

		return $css;
	}
}

// Initialize the module.
add_action( 'init', function() {
	// Load AJAX add to cart class file.
	require_once MERCHANT_DIR . 'inc/modules/quick-view/class-quick-view-ajax-add-to-cart.php';
	
	// Create and initialize the module.
	Merchant_Modules::create_module( Merchant_Quick_View::get_instance() );
} );
