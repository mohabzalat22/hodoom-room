<?php
/**
 * Template for sortable-repeater-icons field.
 *
 * Extends sortable-repeater with a per-row icon picker dropdown.
 * Items are stored as JSON array of { text, icon } objects.
 *
 * @var array<string, mixed>                   $settings  Field configuration.
 * @var mixed                                  $value     Current saved value.
 * @var string                                 $module_id Module ID.
 * @var Merchant_Field_Sortable_Repeater_Icons $field     Field instance.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Resolve icons: use field config override or field class defaults.
$allowed_icons = $field->get_allowed_icons();
$icon_library  = array();
foreach ( $allowed_icons as $icon_key ) {
	$icon_library[ $icon_key ] = Merchant_SVG_Icons::get_svg_icon( $icon_key );
}

$sorting_disabled = isset( $settings['sorting'] ) && false === $settings['sorting'];
$button_label     = $settings['button_label'] ?? esc_html__( 'Add new item', 'merchant' );
?>
<div class="merchant-sortable-repeater-icons-control<?php echo $sorting_disabled ? ' disable-sorting' : ''; ?>"
	data-icons="<?php echo esc_attr( (string) wp_json_encode( $icon_library ) ); ?>">

	<div class="merchant-sortable-repeater-icons sortable regular-field">
		<div class="repeater">
			<button type="button" class="merchant-icon-picker-toggle" data-icon="" title="<?php esc_attr_e( 'Select icon', 'merchant' ); ?>">
				<span class="dashicons dashicons-plus-alt2"></span>
			</button>
			<div class="merchant-icon-picker-dropdown" style="display:none;">
				<button type="button" class="merchant-icon-option" data-icon="" title="<?php esc_attr_e( 'Use campaign icon', 'merchant' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
				<?php foreach ( $icon_library as $key => $svg ) : ?>
					<button type="button" class="merchant-icon-option" data-icon="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $key ); ?>">
						<?php echo wp_kses( $svg, merchant_kses_allowed_tags( array(), false ) ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<input type="text" value="" class="repeater-input" /><span class="dashicons dashicons-menu"></span><a class="customize-control-sortable-repeater-delete"
				href="#"><span class="dashicons dashicons-no-alt"></span></a>
		</div>
	</div>

	<button class="button customize-control-sortable-repeater-icons-add" type="button"><?php echo esc_html( $button_label ); ?></button>
	<input class="merchant-sortable-repeater-input" type="hidden" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="<?php echo esc_attr( (string) wp_json_encode( $value ) ); ?>" />
</div>
<?php
