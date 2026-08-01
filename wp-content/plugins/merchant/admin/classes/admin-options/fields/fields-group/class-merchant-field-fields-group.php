<?php
/**
 * Merchant Field: Fields Group
 *
 * A container field that groups multiple sub-fields together, with optional
 * accordion display, status flag, and support for nesting inside flexible_content.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Fields_Group
 *
 * Renders a collapsible group of nested sub-fields.
 *
 * @since 2.2.5
 */
class Merchant_Field_Fields_Group extends Merchant_Abstract_Field {

	/**
	 * Whether this group renders a status (active/inactive) control.
	 *
	 * @since 2.3.0
	 * @param array<string, mixed> $settings The group field definition.
	 * @return bool
	 */
	public static function has_status_field( $settings ) {
		return ! empty( $settings['display_status'] ) && $settings['display_status'] === true;
	}

	/**
	 * Build the virtual status sub-field definition for a group.
	 *
	 * Injected at render time, not declared in `fields`. ID is `<group_id>_status`;
	 * defaults to the group's `default` (else 'active'). A malformed filter return
	 * (non-array or missing `id`/`options`) falls back to the unfiltered definition.
	 *
	 * @since 2.3.0
	 * @param array<string, mixed> $settings  The group field definition.
	 * @param mixed                $value     Current group value (passed to the filter).
	 * @param string               $module_id Module ID (passed to the filter).
	 * @return array<string, mixed>
	 */
	public static function get_status_field_definition( $settings, $value = null, $module_id = '' ) {
		$default_def = array(
			'id'      => $settings['id'] . '_status',
			'type'    => 'select',
			'title'   => esc_html__( 'Status', 'merchant' ),
			'options' => array(
				'inactive' => esc_html__( 'Inactive', 'merchant' ),
				'active'   => esc_html__( 'Active', 'merchant' ),
			),
			'default' => isset( $settings['default'] ) ? $settings['default'] : 'active',
		);

		/**
		 * Filter the status field configuration for a group.
		 *
		 * A malformed return (non-array or missing 'id'/'options') is silently
		 * ignored and the default definition is used instead.
		 *
		 * @since 1.9.12
		 *
		 * @param mixed  $default_def The default status field definition.
		 * @param array<string, mixed> $settings    The parent group field settings.
		 * @param mixed                $value       Current group value.
		 * @param string               $module_id   Module ID.
		 */
		$filtered = apply_filters( 'merchant_group_status_field', $default_def, $settings, $value, $module_id );

		return ( is_array( $filtered ) && isset( $filtered['id'], $filtered['options'] ) ) ? $filtered : $default_def;
	}

	/**
	 * Render the fields group (standard usage, not inside flexible_content).
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$control_field_status = self::has_status_field( $this->field );
		$accordion            = ! empty( $this->field['accordion'] ) && $this->field['accordion'] === true;
		$state                = ! empty( $this->field['state'] ) && $this->field['state'] === 'open';

		$this->get_template_part( 'template', array(
			'accordion'            => $accordion,
			'control_field_status' => $control_field_status,
			'state'                => $state,
			'inside_flexible'      => false,
			'args'                 => array(),
		) );
	}

	/**
	 * Render the fields group.
	 *
	 * Public static so flexible_content can call it with extra parameters
	 * for nested usage inside layouts.
	 *
	 * @since 2.2.5
	 *
	 * @param array<string, mixed>  $settings         Field settings.
	 * @param mixed  $value            Field value.
	 * @param string $module_id        Module ID.
	 * @param bool   $inside_flexible  Whether this group is inside a flexible_content layout.
	 * @param array<string, mixed>  $args             Extra arguments when inside flexible_content.
	 *
	 * @return void
	 */
	public static function render_group( $settings, $value, $module_id = '', $inside_flexible = false, $args = array() ) {
		$control_field_status = self::has_status_field( $settings );
		$accordion            = ! empty( $settings['accordion'] ) && $settings['accordion'] === true;
		$state                = ! empty( $settings['state'] ) && $settings['state'] === 'open';

		include __DIR__ . '/template.php';
	}

	/**
	 * Type-specific sanitization for the fields group.
	 *
	 * Fields group values are sanitized per-sub-field during save_options,
	 * so this method returns the value as-is.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		if ( ! is_array( $value ) || empty( $this->field['fields'] ) ) {
			return $value;
		}

		$registry  = Merchant_Field_Registry::instance();
		$sanitized = array();

		foreach ( $this->field['fields'] as $sub_field ) {
			if ( ! isset( $sub_field['id'] ) ) {
				continue;
			}

			$sub_id    = $sub_field['id'];
			$sub_value = $value[ $sub_id ] ?? ( $sub_field['default'] ?? null );
			$sub_type  = $sub_field['type'] ?? 'text';

			if ( $registry->has( $sub_type ) ) {
				$instance = $registry->create( $sub_type, $sub_field, $sub_value );
				if ( $instance !== null ) {
					$sub_value = $instance->preprocess( $sub_value );
					$sub_value = $instance->sanitize( $sub_value );
				}
			} else {
				$sub_value = sanitize_text_field( $sub_value );
			}

			$sanitized[ $sub_id ] = $sub_value;
		}

		// Preserve the status sub-field if present (injected by render_group).
		$status_id = $this->field['id'] . '_status';
		if ( isset( $value[ $status_id ] ) ) {
			$sanitized[ $status_id ] = sanitize_text_field( $value[ $status_id ] );
		}

		return $sanitized;
	}
}
