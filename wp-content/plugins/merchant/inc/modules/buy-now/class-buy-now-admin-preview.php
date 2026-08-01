<?php
/**
 * Buy Now — Admin preview rendering.
 *
 * Handles the admin preview box and asset enqueuing for the Buy Now module
 * settings page.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Buy_Now_Admin_Preview
 *
 * @since 2.3.0
 */
class Merchant_Buy_Now_Admin_Preview {

	/**
	 * Module ID constant.
	 *
	 * @var string
	 */
	const MODULE_ID = 'buy-now';

	/**
	 * Enqueue admin CSS and JS on the module settings page.
	 *
	 * @return void
	 */
	public function enqueue_css(): void {
		$page   = ( ! empty( $_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$module = ( ! empty( $_GET['module'] ) ) ? sanitize_text_field( wp_unslash( $_GET['module'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'merchant' === $page && self::MODULE_ID === $module ) {
			wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/buy-now.min.css', array(), MERCHANT_VERSION );
			wp_enqueue_style( 'merchant-admin-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/admin/preview.min.css', array(), MERCHANT_VERSION );
			wp_enqueue_script( 'merchant-admin-' . self::MODULE_ID, MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/admin/preview.min.js', array( 'jquery' ), MERCHANT_VERSION, true );

			// Localize icon SVG map for live preview rendering.
			wp_localize_script( 'merchant-admin-' . self::MODULE_ID, 'merchantBuyNowPreview', array(
				'icons' => array(
					'cart'      => Merchant_Buy_Now_Icons::get_svg( 'cart' ),
					'lightning' => Merchant_Buy_Now_Icons::get_svg( 'lightning' ),
					'arrow'     => Merchant_Buy_Now_Icons::get_svg( 'arrow' ),
				),
			) );
		}
	}

	/**
	 * Render admin preview.
	 *
	 * @param Merchant_Admin_Preview   $preview  The preview object.
	 * @param string                   $module   The current module ID.
	 * @param array<string, mixed>     $settings Module settings.
	 *
	 * @return Merchant_Admin_Preview
	 */
	public function render( $preview, string $module, array $settings ) {
		if ( self::MODULE_ID === $module ) {
			// HTML.
			$preview->set_html( array( $this, 'content' ), $settings );

			// Display Customizer toggle.
			$preview->set_class( 'customize-button', '.merchant-buy-now-button', array(), 'merchant-custom-buy-now-button' );

			// Margin Top/Bottom (global CSS variables).
			$preview->set_css( 'margin-top', '.merchant-buy-now-button', '--mrc-buy-now-margin-top', 'px' );
			$preview->set_css( 'margin-bottom', '.merchant-buy-now-button', '--mrc-buy-now-margin-bottom', 'px' );

			// Padding CSS variables (global).
			$preview->set_css( 'padding_top_bottom', '.merchant-buy-now-button', '--mrc-buy-now-padding-top-bottom', 'px' );
			$preview->set_css( 'padding_left_right', '.merchant-buy-now-button', '--mrc-buy-now-padding-left-right', 'px' );

			// Font size and border radius (global CSS variables).
			$preview->set_css( 'font-size', '.merchant-buy-now-button', '--mrc-buy-now-font-size', 'px' );
			$preview->set_css( 'border-radius', '.merchant-buy-now-button', '--mrc-buy-now-border-radius', 'px' );
		}

		return $preview;
	}

	/**
	 * Output admin preview content.
	 *
	 * @param array<string, mixed> $settings The module settings.
	 *
	 * @return void
	 */
	public function content( array $settings ): void {
		/**
		 * Hook 'merchant_module_buy_now_wrapper_class'
		 *
		 * @since 1.8
		 */
		$wrapper_classes = apply_filters( 'merchant_module_buy_now_wrapper_class', array() );

		// Get first campaign's button text for initial render.
		$campaigns   = $settings['campaigns'] ?? array();
		$first       = ! empty( $campaigns ) ? $campaigns[0] : array();
		$button_text = $first['button_text'] ?? ( $settings['button-text'] ?? __( 'Buy Now', 'merchant' ) );
		$icon_key    = $first['button_icon'] ?? 'none';
		$icon_pos    = $first['button_icon_position'] ?? 'before';
		$custom_svg  = $first['button_icon_svg'] ?? '';

		// Render icon HTML.
		$icon_html   = Merchant_Buy_Now_Icons::render( $icon_key, $icon_pos, $custom_svg );
		$icon_before = ( 'before' === $icon_pos || 'none' === $icon_key ) ? $icon_html : '';
		$icon_after  = ( 'after' === $icon_pos && 'none' !== $icon_key ) ? $icon_html : '';
		?>
		<!-- Product Page Preview (default visible) -->
		<div class="merchant-buy-now-preview-product-page show">
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
					<a href="#" class="merchant-buy-now-button <?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"><?php
						echo wp_kses( $icon_before, merchant_kses_allowed_tags( array( 'all' ) ) );
						echo esc_html( $button_text );
						echo wp_kses( $icon_after, merchant_kses_allowed_tags( array( 'all' ) ) );
					?></a>
				</div>
			</div>
		</div>

		<!-- Popup Preview (hidden by default, shown when Popup Style section is clicked) -->
		<div class="merchant-buy-now-preview-popup">
			<div class="merchant-buy-now-popup-preview-overlay">
				<div class="merchant-buy-now-popup-preview-box">
					<h3 class="merchant-buy-now-popup-preview-title"><?php esc_html_e( 'Complete your purchase!', 'merchant' ); ?></h3>
					<p class="merchant-buy-now-popup-preview-description"><?php esc_html_e( 'You might also like these', 'merchant' ); ?></p>
					<div class="merchant-buy-now-popup-preview-products">
						<div class="merchant-buy-now-popup-preview-product">
							<div class="merchant-buy-now-popup-preview-product-checkbox">
								<input type="checkbox" checked disabled />
							</div>
							<div class="merchant-buy-now-popup-preview-product-image"></div>
							<div class="merchant-buy-now-popup-preview-product-info">
								<span class="product-name"><?php esc_html_e( 'Product Name', 'merchant' ); ?></span>
								<span class="product-price">$19.99</span>
							</div>
						</div>
						<div class="merchant-buy-now-popup-preview-product">
							<div class="merchant-buy-now-popup-preview-product-checkbox">
								<input type="checkbox" checked disabled />
							</div>
							<div class="merchant-buy-now-popup-preview-product-image"></div>
							<div class="merchant-buy-now-popup-preview-product-info">
								<span class="product-name"><?php esc_html_e( 'Another Product', 'merchant' ); ?></span>
								<span class="product-price">$24.99</span>
							</div>
						</div>
					</div>
					<div class="merchant-buy-now-popup-preview-actions is-btn-stacked">
						<button class="merchant-buy-now-popup-preview-accept"><?php esc_html_e( 'Yes, add to my order', 'merchant' ); ?></button>
						<button class="merchant-buy-now-popup-preview-decline"><?php esc_html_e( 'No thanks, just checkout', 'merchant' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
