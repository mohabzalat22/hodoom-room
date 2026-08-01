<?php
/**
 * Template: Fields Group — status dropdown partial.
 *
 * Renders the status (active/inactive) select field for a fields group.
 *
 * @var array<string, mixed>  $settings         Field settings.
 * @var mixed  $value            Field value.
 * @var string $module_id        Module ID.
 * @var bool   $inside_flexible  Whether group is inside flexible_content.
 * @var array<string, mixed>  $args             Extra args when inside flexible_content.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_field = Merchant_Field_Fields_Group::get_status_field_definition( $settings, $value, $module_id );

if ( $inside_flexible ) {
	Merchant_Field_Fields_Group::render_sub_field(
		$status_field,
		isset( $args['value'][ $settings['id'] ][ $status_field['id'] ] ) ? $args['value'][ $settings['id'] ][ $status_field['id'] ] : $status_field['default'] ?? '',
		"name=\"merchant[{$status_field['id']}]",
		"name=\"merchant[{$args['id']}][{$args['option_key']}][{$settings['id']}][{$status_field['id']}]\"  data-name=\"merchant[{$args['id']}][0][{$settings['id']}][{$status_field['id']}]",
		$module_id
	);
} else {
	Merchant_Field_Fields_Group::render_sub_field(
		$status_field,
		isset( $value[ $status_field['id'] ] ) ? $value[ $status_field['id'] ] : '',
		"name=\"merchant[{$status_field['id']}]",
		"name=\"merchant[{$settings['id']}][{$status_field['id']}]",
		$module_id
	);
}
