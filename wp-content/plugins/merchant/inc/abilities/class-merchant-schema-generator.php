<?php
/**
 * Merchant Schema Generator.
 *
 * Converts module option group definitions and current wp_options values
 * into JSON-ready structures for the WP Abilities API.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Schema_Generator
 *
 * Reads a module's `get_option_groups()` field definitions and
 * current settings values, producing a structured response
 * suitable for AI clients. Supports field type mapping,
 * schema inclusion/exclusion, caching, and campaign detection.
 *
 * @since 2.3.0
 */
class Merchant_Schema_Generator {

	/**
	 * Field types that are display-only and should be excluded.
	 *
	 * @var array<int, string>
	 */
	private static $excluded_types = array(
		'info',
		'warning',
		'divider',
		'custom_callback',
		'info_block',
		'content',
	);

	/**
	 * Field type (canonical registry key) to JSON schema kind mapping.
	 *
	 * @var array<string, string>
	 */
	private static $type_map = array(
		'text'                    => 'string',
		'text_readonly'           => 'string',
		'textarea'                => 'string',
		'textarea_code'           => 'string',
		'textarea_multiline'      => 'string',
		'number'                  => 'number',
		'range'                   => 'number',
		'select'                  => 'string',
		'select_ajax'             => 'array',
		'select_size_chart'       => 'number',
		'radio'                   => 'string',
		'radio_alt'               => 'string',
		'buttons'                 => 'string',
		'buttons_alt'             => 'string',
		'buttons_content'         => 'string',
		'image_picker'            => 'string',
		'color'                   => 'string',
		'switcher'                => 'boolean',
		'checkbox'                => 'boolean',
		'checkbox_multiple'       => 'array',
		'gallery'                 => 'string',
		'choices'                 => 'array',
		'products_selector'       => 'string',
		'reviews_selector'        => 'array',
		'wc_coupons'              => 'string',
		'date_time'               => 'string',
		'flexible_content'        => 'array',
		'fields_group'            => 'object',
		'upload'                  => 'string',
		'url'                     => 'string',
		'create_page'             => 'string',
		'hook_select'             => 'object',
		'sortable'                => 'array',
		'sortable_repeater'       => 'array',
		'sortable_repeater_icons' => 'array',
		'dimensions'              => 'object',
		'responsive_dimensions'   => 'object',
	);

	/**
	 * Maps ai_meta keys to x-merchant-* JSON Schema extension properties.
	 *
	 * @var array<string, string>
	 */
	private static $ai_meta_map = array(
		'entity'             => 'x-merchant-entity',
		'reference_type'     => 'x-merchant-reference-type',
		'taxonomy'           => 'x-merchant-taxonomy',
		'value_format'       => 'x-merchant-value-format',
		'usage_hint'         => 'x-merchant-usage-hint',
		'semantic_group'     => 'x-merchant-semantic-group',
		'abstraction_level'  => 'x-merchant-abstraction-level',
		'toggle_for'         => 'x-merchant-toggle-for',
		'toggled_by'         => 'x-merchant-toggled-by',
		'allow_empty'        => 'x-merchant-allow-empty',
	);

	/**
	 * Generate the full settings response for a module.
	 *
	 * @param string $module_id      The module identifier.
	 * @param bool   $include_schema Whether to include field schemas alongside values.
	 *
	 * @return array<string, mixed> The structured settings response.
	 */
	public function generate( $module_id, $include_schema = true ) {
		$module = Merchant_Modules::get_module( $module_id );

		if ( ! $module ) {
			return array();
		}

		$option_groups = $module->get_option_groups();

		if ( empty( $option_groups ) ) {
			return array();
		}

		$settings = $module->get_module_settings();

		$groups_output = array();
		foreach ( $option_groups as $group ) {
			$group_data = array(
				'title'  => isset( $group['title'] ) ? $group['title'] : '',
				'fields' => array(),
			);

			if ( ! isset( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
				$groups_output[] = $group_data;
				continue;
			}

			foreach ( $group['fields'] as $field_def ) {
				$field_output = $this->process_field( $field_def, $settings, $include_schema );

				if ( null !== $field_output ) {
					$group_data['fields'][] = $field_output;
				}
			}

			$groups_output[] = $group_data;
		}

		return array( 'option_groups' => $groups_output );
	}

	/**
	 * Get all field IDs for a module (flat list).
	 *
	 * @param string $module_id The module identifier.
	 *
	 * @return array<int, string> List of valid field IDs.
	 */
	public function get_field_ids( $module_id ) {
		$module = Merchant_Modules::get_module( $module_id );

		if ( ! $module ) {
			return array();
		}

		$ids = array();
		$option_groups = $module->get_option_groups();

		foreach ( $option_groups as $group ) {
			if ( ! isset( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
				continue;
			}

			foreach ( $group['fields'] as $field_def ) {
				if ( ! isset( $field_def['id'] ) ) {
					continue;
				}

				$type = isset( $field_def['type'] ) ? $field_def['type'] : '';
				if ( in_array( $type, self::$excluded_types, true ) ) {
					continue;
				}

				$ids[] = $field_def['id'];
			}
		}

		return $ids;
	}

	/**
	 * Get the field definition for a specific field.
	 *
	 * @param string $module_id The module identifier.
	 * @param string $field_id  The field identifier.
	 *
	 * @return array<string, mixed>|null The field definition, or null if not found.
	 */
	public function get_field_definition( $module_id, $field_id ) {
		$module = Merchant_Modules::get_module( $module_id );

		if ( ! $module ) {
			return null;
		}

		$option_groups = $module->get_option_groups();

		foreach ( $option_groups as $group ) {
			if ( ! isset( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
				continue;
			}

			foreach ( $group['fields'] as $field_def ) {
				if ( isset( $field_def['id'] ) && $field_def['id'] === $field_id ) {
					return $field_def;
				}
			}
		}

		return null;
	}

	/**
	 * Get field IDs inside a flexible_content campaign layout.
	 *
	 * Walks option_groups → fields → flexible_content → layouts → fields
	 * to extract campaign-level field IDs.
	 *
	 * @param string $module_id         The module identifier.
	 * @param string $campaign_field_id The flexible_content field ID (e.g. 'campaigns').
	 *
	 * @return array<int, string> List of campaign field IDs.
	 */
	public function get_campaign_field_ids( $module_id, $campaign_field_id ) {
		$layout_fields = $this->get_campaign_layout_fields( $module_id, $campaign_field_id );

		$ids = array();
		foreach ( $layout_fields as $field_def ) {
			if ( ! isset( $field_def['id'] ) ) {
				continue;
			}

			$type = isset( $field_def['type'] ) ? $field_def['type'] : '';
			if ( in_array( $type, self::$excluded_types, true ) ) {
				continue;
			}

			$ids[] = $field_def['id'];
		}

		return $ids;
	}

	/**
	 * Get a field definition from inside a campaign layout.
	 *
	 * @param string $module_id         The module identifier.
	 * @param string $campaign_field_id The flexible_content field ID.
	 * @param string $field_id          The campaign field identifier.
	 *
	 * @return array<string, mixed>|null The field definition, or null if not found.
	 */
	public function get_campaign_field_definition( $module_id, $campaign_field_id, $field_id ) {
		$layout_fields = $this->get_campaign_layout_fields( $module_id, $campaign_field_id );

		foreach ( $layout_fields as $field_def ) {
			if ( isset( $field_def['id'] ) && $field_def['id'] === $field_id ) {
				return $field_def;
			}
		}

		return null;
	}

	/**
	 * Get the default layout key for a flexible_content campaign field.
	 *
	 * Returns the first layout key from the layouts array, which is the
	 * key used in the hidden 'layout' input when the admin UI saves campaigns.
	 *
	 * @param string $module_id         The module identifier.
	 * @param string $campaign_field_id The flexible_content field ID (e.g. 'rules', 'offers').
	 *
	 * @return string|null The layout key (e.g. 'offer-details'), or null if not found.
	 */
	public function get_default_layout_key( $module_id, $campaign_field_id ) {
		$module = Merchant_Modules::get_module( $module_id );

		if ( ! $module ) {
			return null;
		}

		$option_groups = $module->get_option_groups();

		foreach ( $option_groups as $group ) {
			if ( ! isset( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
				continue;
			}

			foreach ( $group['fields'] as $field_def ) {
				if ( ! isset( $field_def['id'] ) || $field_def['id'] !== $campaign_field_id ) {
					continue;
				}

				if ( ! isset( $field_def['type'] ) || 'flexible_content' !== $field_def['type'] ) {
					continue;
				}

				if ( empty( $field_def['layouts'] ) || ! is_array( $field_def['layouts'] ) ) {
					return null;
				}

				$keys = array_keys( $field_def['layouts'] );

				return $keys[0];
			}
		}

		return null;
	}

	/**
	 * Get the full nested field schema for a campaign's default layout.
	 *
	 * @param string $module_id      The module identifier.
	 * @param string $campaign_field The flexible_content field ID (e.g. 'offers').
	 *
	 * @return array<string, mixed> An object schema with `properties`, or empty array.
	 */
	public function get_campaign_fields_schema( $module_id, $campaign_field ) {
		$layout_fields = $this->get_campaign_layout_fields( $module_id, $campaign_field );

		return $this->build_layout_items_schema( $layout_fields );
	}

	/**
	 * Get the raw field definitions from a flexible_content layout.
	 *
	 * @param string $module_id         The module identifier.
	 * @param string $campaign_field_id The flexible_content field ID.
	 *
	 * @return array<int, array<string, mixed>> List of field definition arrays.
	 */
	private function get_campaign_layout_fields( $module_id, $campaign_field_id ) {
		$module = Merchant_Modules::get_module( $module_id );

		if ( ! $module ) {
			return array();
		}

		$option_groups = $module->get_option_groups();

		foreach ( $option_groups as $group ) {
			if ( ! isset( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
				continue;
			}

			foreach ( $group['fields'] as $field_def ) {
				if ( ! isset( $field_def['id'] ) || $field_def['id'] !== $campaign_field_id ) {
					continue;
				}

				if ( ! isset( $field_def['type'] ) || 'flexible_content' !== $field_def['type'] ) {
					continue;
				}

				if ( ! isset( $field_def['layouts'] ) || ! is_array( $field_def['layouts'] ) ) {
					return array();
				}

				// Return fields from the first layout.
				$layout = reset( $field_def['layouts'] );

				return isset( $layout['fields'] ) ? $layout['fields'] : array();
			}
		}

		return array();
	}

	/**
	 * Process a single field definition into the output format.
	 *
	 * @param array<string, mixed> $field_def      The field definition.
	 * @param array<string, mixed> $settings       Current module settings.
	 * @param bool                 $include_schema Whether to include schema.
	 *
	 * @return array<string, mixed>|null The processed field, or null if excluded.
	 */
	private function process_field( $field_def, $settings, $include_schema ) {
		if ( ! isset( $field_def['id'] ) ) {
			return null;
		}

		$type = isset( $field_def['type'] ) ? $field_def['type'] : '';

		// Exclude display-only types.
		if ( in_array( $type, self::$excluded_types, true ) ) {
			return null;
		}

		$field_id = $field_def['id'];
		$default  = isset( $field_def['default'] ) ? $field_def['default'] : null;
		$value    = isset( $settings[ $field_id ] ) ? $settings[ $field_id ] : $default;

		if ( 'fields_group' === $type && self::ensure_field_classes() && Merchant_Field_Fields_Group::has_status_field( $field_def ) ) {
			$value = $this->normalize_status_group_value( $field_def, $value );
		}

		$output = array(
			'id'    => $field_id,
			'value' => $value,
		);

		if ( $include_schema ) {
			$output['schema'] = $this->build_schema( $field_def );
		}

		return $output;
	}

	/**
	 * Ensure a display_status fields_group value exposes its <id>_status.
	 *
	 * Unsaved groups resolve to the scalar group default; saved groups are arrays
	 * that may predate the status sub-key. Normalize both so the read value always
	 * carries the current active/inactive state, matching the object schema.
	 *
	 * @param array<string, mixed> $field_def The fields_group definition.
	 * @param mixed                $value     Resolved value (scalar default or array).
	 * @return array<string, mixed>
	 */
	private function normalize_status_group_value( $field_def, $value ) {
		$status_def     = Merchant_Field_Fields_Group::get_status_field_definition( $field_def );
		$status_id      = $status_def['id'];
		$status_default = isset( $status_def['default'] ) ? $status_def['default'] : 'active';
		if ( ! is_array( $value ) ) {
			return array( $status_id => $status_default );
		}
		if ( ! isset( $value[ $status_id ] ) ) {
			$value[ $status_id ] = $status_default;
		}
		return $value;
	}

	/**
	 * Resolve a Merchant field type to its JSON-schema kind (unknown types resolve to 'string').
	 *
	 * @param string $type Merchant field type key.
	 *
	 * @return string One of string|number|boolean|array|object.
	 */
	public function get_json_type( $type ) {
		return isset( self::$type_map[ $type ] ) ? self::$type_map[ $type ] : 'string';
	}

	/**
	 * Build the JSON schema for a field definition.
	 *
	 * @param array<string, mixed> $field_def The field definition.
	 *
	 * @return array<string, mixed> The JSON schema object.
	 */
	private function build_schema( $field_def ) {
		$type      = isset( $field_def['type'] ) ? $field_def['type'] : '';
		$json_type = $this->get_json_type( $type );

		$schema = array(
			'type'  => $json_type,
			'title' => isset( $field_def['title'] ) ? $field_def['title'] : '',
		);

		// Field description.
		if ( isset( $field_def['desc'] ) && '' !== $field_def['desc'] ) {
			$schema['description'] = $field_def['desc'];
		}

		// Default value.
		if ( isset( $field_def['default'] ) ) {
			$schema['default'] = $field_def['default'];
		}

		// Read-only flag.
		if ( 'text_readonly' === $type ) {
			$schema['readOnly'] = true;
		}

		// Number/range constraints.
		if ( in_array( $type, array( 'number', 'range' ), true ) ) {
			if ( isset( $field_def['min'] ) ) {
				$schema['minimum'] = $field_def['min'];
			}
			if ( isset( $field_def['max'] ) ) {
				$schema['maximum'] = $field_def['max'];
			}
			if ( isset( $field_def['step'] ) ) {
				$schema['multipleOf'] = $field_def['step'];
			}
			if ( isset( $field_def['unit'] ) ) {
				$schema['unit'] = $field_def['unit'];
			}
		}

		// Enum options (select, radio, radio_alt).
		if ( in_array( $type, array( 'select', 'radio', 'radio_alt' ), true ) && isset( $field_def['options'] ) ) {
			$schema['enum']    = array_keys( $field_def['options'] );
			$schema['options'] = $field_def['options'];
		}

		// Color format.
		if ( 'color' === $type ) {
			$schema['format'] = 'css-color';
		}

		// Date/time format.
		if ( 'date_time' === $type ) {
			$schema['format']                    = 'date-time';
			$schema['x-merchant-native-format']  = 'm-d-Y h:i A';
			$schema['x-merchant-format-example'] = '06-24-2026 03:30 PM';
		}

		// Pro gating.
		if ( ! empty( $field_def['pro'] ) ) {
			$schema['pro_only'] = true;
		}

		// Condition metadata.
		if ( isset( $field_def['condition'] ) ) {
			$schema['condition'] = $field_def['condition'];
		}
		if ( isset( $field_def['conditions'] ) ) {
			$schema['conditions'] = $field_def['conditions'];
		}

		// Recurse into the default layout so nested fields carry their own ai_meta.
		if ( 'flexible_content' === $type && ! empty( $field_def['layouts'] ) && is_array( $field_def['layouts'] ) ) {
			$layout = reset( $field_def['layouts'] );
			$items  = $this->build_layout_items_schema( isset( $layout['fields'] ) ? $layout['fields'] : array() );

			if ( ! empty( $items ) ) {
				$schema['items'] = $items;
			}
		}

		// Fields group: describe sub-fields (and the virtual status control) as nested properties.
		if ( 'fields_group' === $type ) {
			$sub_fields = ( isset( $field_def['fields'] ) && is_array( $field_def['fields'] ) ) ? $field_def['fields'] : array();
			$props      = $this->build_properties_map( $sub_fields );
			$this->maybe_add_status_property( $field_def, $props );
			if ( ! empty( $props ) ) {
				$schema['properties'] = $props;
			}
		}

		// AI meta extensions.
		$this->emit_ai_meta( $field_def, $schema );

		return $schema;
	}

	/**
	 * Build a properties map from an array of field definitions.
	 *
	 * Iterates field defs, skips entries missing `id` or whose `type` is in
	 * `$excluded_types`, and maps each surviving field ID to its schema.
	 *
	 * @param array<mixed> $field_defs Field definitions.
	 *
	 * @return array<string, mixed> Properties map (field ID → schema), may be empty.
	 */
	private function build_properties_map( array $field_defs ) {
		$properties = array();

		foreach ( $field_defs as $field_def ) {
			if ( ! isset( $field_def['id'] ) ) {
				continue;
			}

			$type = isset( $field_def['type'] ) ? $field_def['type'] : '';
			if ( in_array( $type, self::$excluded_types, true ) ) {
				continue;
			}

			$properties[ $field_def['id'] ] = $this->build_schema( $field_def );
		}

		return $properties;
	}

	/**
	 * Build the `items` object schema for a flexible_content layout's fields.
	 *
	 * Each non-display field becomes a property whose schema is produced by
	 * build_schema(), carrying its full ai_meta (x-merchant-*) automatically.
	 *
	 * @param array<int, array<string, mixed>> $layout_fields Resolved layout field defs.
	 *
	 * @return array<string, mixed> An object schema with `properties`, or empty array.
	 */
	private function build_layout_items_schema( array $layout_fields ) {
		$properties = $this->build_properties_map( $layout_fields );

		if ( empty( $properties ) ) {
			return array();
		}

		return array(
			'type'       => 'object',
			'properties' => $properties,
		);
	}

	/**
	 * Append the virtual status sub-field schema when the group enables it.
	 *
	 * Checks if the field class is loaded and if the group has a status flag,
	 * then injects the virtual `<id>_status` property with enum and usage hint.
	 *
	 * @param array<string, mixed> $field_def  The fields_group definition.
	 * @param array<mixed>         $properties Properties map (by reference).
	 *
	 * @return void
	 */
	private function maybe_add_status_property( $field_def, &$properties ) {
		if ( ! self::ensure_field_classes() || ! Merchant_Field_Fields_Group::has_status_field( $field_def ) ) {
			return;
		}

		$status_def = Merchant_Field_Fields_Group::get_status_field_definition( $field_def );
		$schema     = $this->build_schema( $status_def );

		$schema['x-merchant-usage-hint'] = 'Per-location display toggle (distinct from campaign_status). Enum is enforced on write: "active" shows this location, "inactive" hides it. If this key is absent from a saved record, the displayed state falls back to this field\'s schema default shown here.';

		$properties[ $status_def['id'] ] = $schema;
	}

	/**
	 * Ensure the admin field classes are loaded (REST loads them lazily).
	 *
	 * If `Merchant_Field_Fields_Group` is not loaded but `Merchant_Field_Registry`
	 * is available, triggers the registry singleton which eager-loads field classes.
	 *
	 * @return bool Whether Merchant_Field_Fields_Group is available.
	 */
	public static function ensure_field_classes() {
		if ( ! class_exists( 'Merchant_Field_Fields_Group' ) && class_exists( 'Merchant_Field_Registry' ) ) {
			Merchant_Field_Registry::instance();
		}

		return class_exists( 'Merchant_Field_Fields_Group' );
	}

	/**
	 * Emit x-merchant-* extension properties from a field's ai_meta.
	 *
	 * Reads the optional 'ai_meta' key from a field definition and maps
	 * each recognized key to its x-merchant-* JSON Schema extension property.
	 * Unknown keys are silently ignored to allow forward-compatible additions.
	 *
	 * @param array<string, mixed> $field_def The field definition containing an optional 'ai_meta' key.
	 * @param array<string, mixed> $schema    The schema array to append extensions to (modified by reference).
	 * @return void
	 */
	private function emit_ai_meta( $field_def, &$schema ): void {
		if ( ! isset( $field_def['ai_meta'] ) || ! is_array( $field_def['ai_meta'] ) ) {
			return;
		}

		foreach ( $field_def['ai_meta'] as $key => $value ) {
			if ( ! isset( self::$ai_meta_map[ $key ] ) ) {
				continue;
			}

			$schema[ self::$ai_meta_map[ $key ] ] = $value;
		}
	}
}
