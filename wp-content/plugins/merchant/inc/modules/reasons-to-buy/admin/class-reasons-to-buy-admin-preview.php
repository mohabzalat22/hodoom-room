<?php
/**
 * Reasons To Buy — Admin Preview.
 *
 * Handles the admin preview rendering and script localisation
 * for the module settings page.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Merchant_Reasons_To_Buy_Admin_Preview
 *
 * Single responsibility: produce the admin preview HTML
 * and supply localised data for the preview JavaScript.
 */
class Merchant_Reasons_To_Buy_Admin_Preview {

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
	 * Render the admin preview.
	 *
	 * Hooked to `merchant_module_preview`.
	 *
	 * @param Merchant_Admin_Preview $preview The preview instance.
	 * @param string                 $module  The current module ID.
	 *
	 * @return Merchant_Admin_Preview
	 */
	public function render( $preview, string $module ) {
		if ( $this->module_id !== $module ) {
			return $preview;
		}

		$preview->set_html( $this->get_preview_html() );

		return $preview;
	}

	/**
	 * Localise Script data.
	 *
	 * Hooked to `merchant_admin_localize_script`.
	 *
	 * @param array<string, mixed> $data Existing localisation data.
	 *
	 * @return array<string, mixed>
	 */
	public function localize_script( array $data ): array {
		// Ensure field classes are loaded (registry loads them in its constructor).
		Merchant_Field_Registry::instance();

		$icon_keys = Merchant_Field_Sortable_Repeater_Icons::get_default_icons();

		$data['icons'] = array();
		foreach ( $icon_keys as $key => $label ) {
			$data['icons'][ $key ] = Merchant_SVG_Icons::get_svg_icon( $key );
		}

		return $data;
	}

	/**
	 * Get the preview HTML markup.
	 *
	 * @return string
	 */
	public function get_preview_html(): string {
		ob_start();
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
            <div class="mrc-preview-right-column merchant-reasons-list-preview">
                <div class="mrc-preview-text-placeholder"></div>
                <div class="mrc-preview-text-placeholder mrc-mw-70"></div>
                <div class="mrc-preview-text-placeholder mrc-mw-30"></div>
                <div class="mrc-preview-text-placeholder mrc-mw-40 mrc-hide-on-smaller-screens"></div>
                <div class="mrc-preview-module-content">
                    <div class="merchant-product-brand-image">
                        <div class="merchant-reasons-list">
                            <strong class="merchant-reasons-list-title"></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
		return (string) ob_get_clean();
	}
}
