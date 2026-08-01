<?php
/**
 * Buy Now — Button rendering.
 *
 * Renders the Buy Now button on single product pages and shop archive pages.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Buy_Now_Button_Renderer
 *
 * @since 2.3.0
 */
class Merchant_Buy_Now_Button_Renderer {

	/**
	 * Campaign resolver instance.
	 *
	 * @var Merchant_Buy_Now_Resolver
	 */
	private $resolver;

	/**
	 * Module settings.
	 *
	 * @var array<string, mixed>
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param Merchant_Buy_Now_Resolver $resolver Campaign resolver.
	 * @param array<string, mixed>      $settings Module settings.
	 */
	public function __construct( Merchant_Buy_Now_Resolver $resolver, array $settings ) {
		$this->resolver = $resolver;
		$this->settings = $settings;
	}

	/**
	 * Check if the popup data attribute should be rendered.
	 *
	 * @param array<string, mixed> $campaign Campaign data.
	 *
	 * @return bool
	 */
	private function should_render_popup_attr( array $campaign ): bool {
		return defined( 'MERCHANT_PRO_VERSION' )
			&& Merchant_Modules::is_module_active( 'added-to-cart-popup' )
			&& ! empty( $campaign['show_cart_popup'] ?? 0 );
	}

	/**
	 * Single product buy now button.
	 *
	 * @return void
	 */
	public function single_product_button(): void {
		// Don't include on Sticky Add to Cart.
		if ( did_filter( 'merchant_sticky_add_to_cart_template_args' ) ) {
			return;
		}

		// Don't include on Quick View.
		if ( did_action( 'merchant_quick_view_before_add_to_cart' ) ) {
			return;
		}

		global $post, $product;

		/**
		 * Hook 'merchant_is_pre_order_product' Checks if the product is a pre-order.
		 *
		 * @param int $product_id The product ID.
		 *
		 * @since 2.1.9
		 */
		$is_pre_order = apply_filters( 'merchant_is_pre_order_product', $product->get_id() );
		if ( $is_pre_order === true || $is_pre_order === 1 ) { // @phpstan-ignore identical.alwaysFalse
			return;
		}

		if ( isset( $this->settings['display-product'] ) && ! $this->settings['display-product'] ) {
			return;
		}

		// Resolve campaign for this product.
		$campaign = $this->resolver->resolve( $product );

		// No matching campaign — no button.
		if ( null === $campaign ) {
			return;
		}

		if ( ! empty( $product ) ) {
			if ( 'yes' === get_post_meta( $post->ID, '_is_pre_order', true ) && strtotime( get_post_meta( $post->ID, '_pre_order_date', true ) ) > time() ) {
				return;
			}
		}

		// Get button text from campaign (fall back to global setting for backward compat).
		$text = $campaign['button_text'] ?? Merchant_Admin_Options::get( 'buy-now', 'button-text', esc_html__( 'Buy Now', 'merchant' ) );

		// Build icon HTML.
		$icon_key      = $campaign['button_icon'] ?? 'none';
		$icon_position = $campaign['button_icon_position'] ?? 'before';
		$custom_svg    = $campaign['button_icon_svg'] ?? '';
		$icon_html     = Merchant_Buy_Now_Icons::render( $icon_key, $icon_position, $custom_svg );

		$_wrapper_classes   = array();
		$_wrapper_classes[] = $product->get_type() === 'variable' ? 'disabled' : '';

		/**
		 * Hook 'merchant_module_buy_now_wrapper_class'
		 *
		 * @since 1.8
		 */
		$wrapper_classes = apply_filters( 'merchant_module_buy_now_wrapper_class', $_wrapper_classes );

		$_attrs = array();

		// Add the disabled attribute if the product is variable.
		if ( $product->get_type() === 'variable' ) {
			$_attrs['disabled'] = 'disabled';
		}

		/**
		 * Hook 'merchant_module_buy_now_wrapper_attrs'
		 *
		 * @since 1.9.16
		 */
		$attrs = apply_filters( 'merchant_module_buy_now_wrapper_attrs', $_attrs );

		// Convert attributes array to a string.
		$attributes = '';
		foreach ( $attrs as $key => $value ) {
			$attributes .= sprintf( '%s="%s" ', esc_attr( $key ), esc_attr( $value ) );
		}

		$popup_attr = $this->should_render_popup_attr( $campaign ) ? ' data-show-popup="1"' : '';
		?>
        <!-- Don't define type="submit" because it creates issue with block themes. The button is inside the form, so by default the type is already "submit". -->
		<button name="merchant-buy-now" value="<?php echo absint( $product->get_ID() ); ?>" class="button alt wp-element-button merchant-buy-now-button <?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" data-campaign-id="<?php echo esc_attr( $campaign['flexible_id'] ?? '' ); ?>" <?php echo wp_kses( trim( $attributes ), merchant_kses_allowed_tags() ); ?><?php echo $popup_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attribute ?>>
			<?php if ( 'before' === $icon_position ) : ?>
				<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG output ?>
			<?php endif; ?>
            <?php echo esc_html( Merchant_Translator::translate( $text ) ); ?>
			<?php if ( 'after' === $icon_position ) : ?>
				<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG output ?>
			<?php endif; ?>
        </button>
		<?php
	}

	/**
	 * Shop archive product buy now button.
	 *
	 * @return void
	 */
	public function shop_archive_button(): void {
		global $post, $product;

		/**
		 * Hook 'merchant_is_pre_order_product' Checks if the product is a pre-order.
		 *
		 * @param int $product_id The product ID.
		 *
		 * @since 2.1.9
		 */
		$is_pre_order = apply_filters( 'merchant_is_pre_order_product', $product->get_id() );
		if ( $is_pre_order === true || $is_pre_order === 1 ) { // @phpstan-ignore identical.alwaysFalse
			return;
		}

		if ( ! $product->is_in_stock() ) {
			return;
		}

		if ( ! is_product() && isset( $this->settings['display-archive'] ) && ! $this->settings['display-archive'] ) {
			return;
		}

		if ( is_product() && isset( $this->settings['display-upsell-related'] ) && ! $this->settings['display-upsell-related'] ) {
			return;
		}

		if ( ! in_array( $product->get_type(), array( 'simple', 'merchant_pro_bundle' ), true ) ) {
			return;
		}

		if ( $product->is_type( 'merchant_pro_bundle' ) && $product->has_variables() ) {
			return;
		}

		if ( ! empty( $product )
			&& Merchant_Modules::is_module_active( 'pre-orders' )
			&& 'yes' === get_post_meta( $post->ID, '_is_pre_order', true )
			&& strtotime( get_post_meta( $post->ID, '_pre_order_date', true ) ) > time()
		) {
			return;
		}

		// Resolve campaign for this product.
		$campaign = $this->resolver->resolve( $product );

		// No matching campaign — no button.
		if ( null === $campaign ) {
			return;
		}

		// Get button text from campaign.
		$text = $campaign['button_text'] ?? Merchant_Admin_Options::get( 'buy-now', 'button-text', esc_html__( 'Buy Now', 'merchant' ) );

		// Build icon HTML.
		$icon_key      = $campaign['button_icon'] ?? 'none';
		$icon_position = $campaign['button_icon_position'] ?? 'before';
		$custom_svg    = $campaign['button_icon_svg'] ?? '';
		$icon_html     = Merchant_Buy_Now_Icons::render( $icon_key, $icon_position, $custom_svg );

		// Build redirect URL with campaign ID.
		$redirect_url = add_query_arg( array(
			'merchant-buy-now'     => $product->get_ID(),
			'merchant_campaign_id' => $campaign['flexible_id'] ?? '',
		), wc_get_checkout_url() );

		/**
		 * Hook 'merchant_module_buy_now_wrapper_class'
		 *
		 * @since 1.8
		 */
		$wrapper_classes = apply_filters( 'merchant_module_buy_now_wrapper_class', array() );

		$popup_attr = $this->should_render_popup_attr( $campaign ) ? ' data-show-popup="1"' : '';
		?>
		<a href="<?php echo esc_url( $redirect_url ); ?>" class="button alt wp-element-button product_type_simple add_to_cart_button merchant-buy-now-button <?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" data-campaign-id="<?php echo esc_attr( $campaign['flexible_id'] ?? '' ); ?>"<?php echo $popup_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static attribute ?>>
			<?php if ( 'before' === $icon_position ) : ?>
				<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG output ?>
			<?php endif; ?>
			<?php echo esc_html( Merchant_Translator::translate( $text ) ); ?>
			<?php if ( 'after' === $icon_position ) : ?>
				<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG output ?>
			<?php endif; ?>
		</a>
		<?php
	}
}
