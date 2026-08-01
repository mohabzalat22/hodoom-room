<?php
/**
 * Merchant Field Validator.
 *
 * Validates and sanitizes field values submitted via the WP Abilities API.
 * Implements a 4-stage pipeline: unknown key rejection, type coercion,
 * constraint enforcement, and WordPress sanitization.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Validator
 *
 * Validates input fields against a module's option group definitions.
 * Produces valid values, errors, and warnings.
 *
 * @since 2.3.0
 */
class Merchant_Field_Validator {

	/**
	 * Schema generator for field lookup.
	 *
	 * @var Merchant_Schema_Generator
	 */
	private $schema_generator;

	/**
	 * Field registry used to delegate sanitization to the canonical field classes.
	 *
	 * @var Merchant_Field_Registry|null
	 */
	private $field_registry;

	/**
	 * Accumulated errors from fields_group recursive validation.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private $fields_group_errors = array();

	/**
	 * Constructor.
	 *
	 * @param Merchant_Schema_Generator    $schema_generator Schema generator instance.
	 * @param Merchant_Field_Registry|null $field_registry   Optional field registry; resolved lazily when null.
	 */
	public function __construct( $schema_generator, $field_registry = null ) {
		$this->schema_generator = $schema_generator;
		$this->field_registry   = $field_registry;
	}

	/**
	 * Resolve the field registry, lazily falling back to the singleton.
	 *
	 * @return Merchant_Field_Registry
	 */
	private function registry() {
		if ( null === $this->field_registry ) {
			$this->field_registry = Merchant_Field_Registry::instance();
		}

		return $this->field_registry;
	}

	/**
	 * Validate and sanitize input fields against a module's definitions.
	 *
	 * @param string               $module_id The module identifier.
	 * @param array<string, mixed> $input     Key-value pairs of field updates.
	 * @param array<string, mixed> $context   Optional context for conditional evaluation.
	 *
	 * @return array{valid: array<string, mixed>, errors: array<int, array<string, mixed>>, warnings: array<int, array<string, mixed>>}
	 */
	public function validate_and_sanitize( $module_id, $input, $context = array() ) {
		$valid    = array();
		$errors   = array();
		$warnings = array();

		$this->fields_group_errors = array();

		$known_fields = $this->schema_generator->get_field_ids( $module_id );

		foreach ( $input as $field_id => $value ) {
			// Stage 1: Unknown key rejection.
			if ( ! in_array( $field_id, $known_fields, true ) ) {
				$errors[] = array(
					'code'         => 'invalid_field_id',
					'field'        => $field_id,
					'message'      => sprintf( "Unknown field ID '%s'.", $field_id ),
					'valid_fields' => $known_fields,
				);
				continue;
			}

			$field_def = $this->schema_generator->get_field_definition( $module_id, $field_id );

			if ( null === $field_def ) {
				continue;
			}

			$type = isset( $field_def['type'] ) ? $field_def['type'] : '';

			// Stage 2: Type coercion & validation.
			$type_result = $this->validate_type( $field_id, $value, $type, $field_def );
			if ( null !== $type_result['error'] ) {
				$errors[] = $type_result['error'];
				continue;
			}
			$value = $type_result['value'];

			// Stage 3: Constraint enforcement.
			$constraint_error = $this->validate_constraints( $field_id, $value, $type, $field_def );
			if ( null !== $constraint_error ) {
				$errors[] = $constraint_error;
				continue;
			}

			// Stage 4: WordPress sanitization.
			$value = $this->sanitize_value( $value, $type, $field_def );

			$valid[ $field_id ] = $value;
		}

		$errors = array_merge( $errors, $this->fields_group_errors );

		return array(
			'valid'    => $valid,
			'errors'   => $errors,
			'warnings' => $warnings,
		);
	}

	/**
	 * Validate and sanitize campaign field updates.
	 *
	 * Same 4-stage pipeline as validate_and_sanitize() but resolves
	 * field definitions from inside a flexible_content campaign layout.
	 *
	 * @param string               $module_id         The module identifier.
	 * @param string               $campaign_field_id The flexible_content field ID (e.g. 'campaigns').
	 * @param array<string, mixed> $input             Key-value pairs of campaign field updates.
	 *
	 * @return array{valid: array<string, mixed>, errors: array<int, array<string, mixed>>, warnings: array<int, array<string, mixed>>}
	 */
	public function validate_campaign_updates( $module_id, $campaign_field_id, $input ) {
		$valid    = array();
		$errors   = array();
		$warnings = array();

		$this->fields_group_errors = array();

		$known_fields = $this->schema_generator->get_campaign_field_ids( $module_id, $campaign_field_id );

		foreach ( $input as $field_id => $value ) {
			// Stage 1: Unknown key rejection.
			if ( ! in_array( $field_id, $known_fields, true ) ) {
				$errors[] = array(
					'code'         => 'invalid_field_id',
					'field'        => $field_id,
					'message'      => sprintf( "Unknown campaign field ID '%s'.", $field_id ),
					'valid_fields' => $known_fields,
				);
				continue;
			}

			$field_def = $this->schema_generator->get_campaign_field_definition(
				$module_id,
				$campaign_field_id,
				$field_id
			);

			if ( null === $field_def ) {
				continue;
			}

			$type = isset( $field_def['type'] ) ? $field_def['type'] : '';

			// Stage 2: Type coercion & validation.
			$type_result = $this->validate_type( $field_id, $value, $type, $field_def );
			if ( null !== $type_result['error'] ) {
				$errors[] = $type_result['error'];
				continue;
			}
			$value = $type_result['value'];

			// Stage 3: Constraint enforcement.
			$constraint_error = $this->validate_constraints( $field_id, $value, $type, $field_def );
			if ( null !== $constraint_error ) {
				$errors[] = $constraint_error;
				continue;
			}

			// Stage 4: WordPress sanitization.
			$value = $this->sanitize_value( $value, $type, $field_def );

			$valid[ $field_id ] = $value;
		}

		$errors = array_merge( $errors, $this->fields_group_errors );

		return array(
			'valid'    => $valid,
			'errors'   => $errors,
			'warnings' => $warnings,
		);
	}

	/**
	 * Validate input against hand-authored field definitions.
	 *
	 * Unlike validate_and_sanitize(), the field defs here aren't looked up from a
	 * module's option groups — the caller supplies them directly (e.g. a bundle's
	 * per-item schema). Runs the same type-coercion and constraint stages, plus
	 * sanitization, so callers get back values ready to persist.
	 *
	 * @param array<string, array<string, mixed>> $field_defs Map of field_id => field definition.
	 * @param array<string, mixed>                $input      Key-value pairs to validate.
	 *
	 * @return array{valid: array<string, mixed>, errors: array<int, array<string, mixed>>}
	 */
	public function validate_against_spec( array $field_defs, array $input ) {
		$valid  = array();
		$errors = array();

		$this->fields_group_errors = array();

		foreach ( $input as $field_id => $value ) {
			if ( ! array_key_exists( $field_id, $field_defs ) ) {
				$errors[] = array(
					'code'    => 'unknown_field',
					'field'   => $field_id,
					'message' => sprintf( "Unknown field '%s'.", $field_id ),
				);
				continue;
			}

			$field_def = $field_defs[ $field_id ];
			$type      = isset( $field_def['type'] ) ? $field_def['type'] : '';

			$type_result = $this->validate_type( $field_id, $value, $type, $field_def );
			if ( null !== $type_result['error'] ) {
				$errors[] = $type_result['error'];
				continue;
			}
			$value = $type_result['value'];

			$constraint_error = $this->validate_constraints( $field_id, $value, $type, $field_def );
			if ( null !== $constraint_error ) {
				$errors[] = $constraint_error;
				continue;
			}

			$valid[ $field_id ] = $this->sanitize_value( $value, $type, $field_def );
		}

		$errors = array_merge( $errors, $this->fields_group_errors );

		return array(
			'valid'  => $valid,
			'errors' => $errors,
		);
	}

	/**
	 * Validate and coerce the value to the expected type.
	 *
	 * @param string               $field_id  Field identifier.
	 * @param mixed                $value     Input value.
	 * @param string               $type      Merchant field type.
	 * @param array<string, mixed> $field_def Full field definition.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_type( $field_id, $value, $type, $field_def ) {
		switch ( $type ) {
			case 'text_readonly':
				return array(
					'value' => $value,
					'error' => array(
						'code'    => 'readonly_field',
						'field'   => $field_id,
						'message' => sprintf( "Field '%s' is read-only and cannot be updated.", $field_id ),
					),
				);

			case 'text':
			case 'textarea':
			case 'textarea_code':
			case 'textarea_multiline':
			case 'url':
			case 'upload':
			case 'select':
			case 'radio':
			case 'radio_alt':
			case 'products_selector':
			case 'gallery':
				// gallery stores a comma-separated string of attachment/product IDs,
				// like products_selector — mirrors Merchant_Field_Gallery::sanitize_value().
				return $this->validate_string_type( $field_id, $value );

			case 'number':
			case 'range':
				return $this->validate_numeric_type( $field_id, $value, $field_def );

			case 'switcher':
			case 'checkbox':
				return $this->validate_boolean_type( $field_id, $value );

			case 'color':
				return $this->validate_color_type( $field_id, $value );

			case 'date_time':
				return $this->validate_date_time_type( $field_id, $value, $field_def );
		}

		if ( ! $this->registry()->has( $type ) ) {
			return array(
				'value' => $value,
				'error' => array(
					'code'    => 'unsupported_field_type',
					'field'   => $field_id,
					'message' => sprintf( "Field '%s' has unsupported type '%s'.", $field_id, $type ),
				),
			);
		}

		$kind = $this->schema_generator->get_json_type( $type );

		// Reject scalars for object kinds and for array types that would silently drop to []; others tolerate strings their field class decodes.
		$needs_array = ( 'object' === $kind )
			|| in_array( $type, array( 'checkbox_multiple', 'flexible_content' ), true );

		if ( $needs_array && ! is_array( $value ) ) {
			return array(
				'value' => $value,
				'error' => array(
					'code'    => 'invalid_field_value',
					'field'   => $field_id,
					'message' => sprintf(
						"Field '%s' expects %s value.",
						$field_id,
						'object' === $kind ? 'an object' : 'an array'
					),
				),
			);
		}

		return array( 'value' => $value, 'error' => null );
	}

	/**
	 * Validate that the value is a string.
	 *
	 * @param string $field_id Field identifier.
	 * @param mixed  $value    Input value.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_string_type( $field_id, $value ) {
		if ( ! is_string( $value ) ) {
			return array(
				'value' => $value,
				'error' => $this->make_type_error( $field_id, 'string' ),
			);
		}

		return array( 'value' => $value, 'error' => null );
	}

	/**
	 * Validate and coerce a numeric value.
	 *
	 * Casts to float when the step has a decimal point, otherwise int.
	 *
	 * @param string               $field_id  Field identifier.
	 * @param mixed                $value     Input value.
	 * @param array<string, mixed> $field_def Full field definition.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_numeric_type( $field_id, $value, $field_def ) {
		if ( ! is_numeric( $value ) ) {
			return array(
				'value' => $value,
				'error' => $this->make_type_error( $field_id, 'number' ),
			);
		}

		$step = isset( $field_def['step'] ) ? $field_def['step'] : 1;
		if ( is_float( $step ) || ( is_string( $step ) && strpos( (string) $step, '.' ) !== false ) ) {
			$value = (float) $value;
		} else {
			$value = (int) $value;
		}

		return array( 'value' => $value, 'error' => null );
	}

	/**
	 * Validate and coerce a boolean-like value (0/1/"0"/"1"/true/false → int).
	 *
	 * @param string $field_id Field identifier.
	 * @param mixed  $value    Input value.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_boolean_type( $field_id, $value ) {
		if ( ! in_array( $value, array( 0, 1, '0', '1', true, false ), true ) ) {
			return array(
				'value' => $value,
				'error' => $this->make_type_error( $field_id, 'boolean (0 or 1)' ),
			);
		}

		return array( 'value' => (int) (bool) $value, 'error' => null );
	}

	/**
	 * Validate a CSS color value.
	 *
	 * Accepts hex (#RGB, #RGBA, #RRGGBB, #RRGGBBAA), rgb(), rgba(), hsl(), hsla().
	 * Patterns match Merchant_Field_Color::sanitize_value() for consistency.
	 *
	 * @param string $field_id Field identifier.
	 * @param mixed  $value    Input value.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_color_type( $field_id, $value ) {
		if ( ! is_string( $value ) || ! $this->is_valid_css_color( $value ) ) {
			return array(
				'value' => $value,
				'error' => array(
					'code'    => 'invalid_field_value',
					'field'   => $field_id,
					'message' => sprintf(
						"Value '%s' is not a valid CSS color. Accepted: hex (#RGB, #RRGGBB, #RRGGBBAA), rgb(), rgba(), hsl(), hsla().",
						is_string( $value ) ? $value : gettype( $value )
					),
				),
			);
		}

		return array( 'value' => $value, 'error' => null );
	}

	/**
	 * Check whether a string is a valid CSS color value.
	 *
	 * @param string $value The trimmed color string.
	 *
	 * @return bool
	 */
	private function is_valid_css_color( $value ) {
		// Hex: #RGB, #RGBA, #RRGGBB, #RRGGBBAA.
		if ( preg_match( '/^#([A-Fa-f0-9]{3,4}){1,2}$/', $value ) ) {
			return true;
		}

		// rgb(R, G, B) or rgba(R, G, B, A).
		if ( preg_match( '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+)\s*)?\)$/', $value ) ) {
			return true;
		}

		// hsl(H, S%, L%) or hsla(H, S%, L%, A).
		if ( preg_match( '/^hsla?\(\s*\d{1,3}\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%\s*(,\s*(0|1|0?\.\d+)\s*)?\)$/', $value ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Validate a date/time string value.
	 *
	 * Accepts the native air-datepicker format (m-d-Y h:i A),
	 * ISO 8601 (Y-m-d, Y-m-dTH:i, Y-m-dTH:i:s), and m/d/y.
	 * Non-native formats are normalized to m-d-Y h:i A.
	 * Empty strings are accepted only when ai_meta.allow_empty is true.
	 *
	 * @param string               $field_id  Field identifier.
	 * @param mixed                $value     Input value.
	 * @param array<string, mixed> $field_def Full field definition.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_date_time_type( $field_id, $value, $field_def ) {
		if ( ! is_string( $value ) ) {
			return array(
				'value' => $value,
				'error' => $this->make_type_error( $field_id, 'date/time string' ),
			);
		}

		// Empty string gating via ai_meta.allow_empty.
		if ( '' === $value ) {
			$allow_empty = isset( $field_def['ai_meta']['allow_empty'] ) && $field_def['ai_meta']['allow_empty'];

			if ( $allow_empty ) {
				return array( 'value' => $value, 'error' => null );
			}

			return array(
				'value' => $value,
				'error' => array(
					'code'    => 'invalid_field_value',
					'field'   => $field_id,
					'message' => sprintf( "Field '%s' requires a date/time value.", $field_id ),
				),
			);
		}

		$native_format = 'm-d-Y h:i A';

		// Try native format first (m-d-Y h:i A, e.g. 06-24-2026 03:30 PM).
		if ( preg_match( '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2} [AP]M$/', $value ) ) {
			$d = DateTime::createFromFormat( $native_format, $value );

			if ( $d && $d->format( $native_format ) === $value ) {
				return array( 'value' => $value, 'error' => null );
			}
		}

		// Try ISO 8601 with seconds (Y-m-dTH:i:s).
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $value ) ) {
			$d = DateTime::createFromFormat( 'Y-m-d\TH:i:s', $value );

			if ( $d && $d->format( 'Y-m-d\TH:i:s' ) === $value ) {
				return array( 'value' => $d->format( $native_format ), 'error' => null );
			}
		}

		// Try ISO 8601 without seconds (Y-m-dTH:i).
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value ) ) {
			$d = DateTime::createFromFormat( 'Y-m-d\TH:i', $value );

			if ( $d && $d->format( 'Y-m-d\TH:i' ) === $value ) {
				return array( 'value' => $d->format( $native_format ), 'error' => null );
			}
		}

		// Try date-only ISO (Y-m-d).
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			$d = DateTime::createFromFormat( 'Y-m-d', $value );

			if ( $d && $d->format( 'Y-m-d' ) === $value ) {
				$d->setTime( 0, 0, 0 );

				return array( 'value' => $d->format( $native_format ), 'error' => null );
			}
		}

		// Try slash format (m/d/y or m/d/yyyy).
		if ( preg_match( '#^\d{1,2}/\d{1,2}/\d{2,4}$#', $value ) ) {
			$parts  = explode( '/', $value );
			$format = strlen( $parts[2] ) <= 2 ? 'n/j/y' : 'n/j/Y';
			$d      = DateTime::createFromFormat( $format, $value );

			if ( $d && $d->format( $format ) === $value ) {
				$d->setTime( 0, 0, 0 );

				return array( 'value' => $d->format( $native_format ), 'error' => null );
			}
		}

		return array(
			'value' => $value,
			'error' => array(
				'code'    => 'invalid_field_value',
				'field'   => $field_id,
				'message' => sprintf(
					"Value '%s' is not a valid date/time. Accepted formats: m-d-Y h:i A (e.g. 06-24-2026 03:30 PM), Y-m-d, Y-m-dTH:i:s, m/d/y.",
					$value
				),
			),
		);
	}

	/**
	 * Validate constraints (enum, min/max, step).
	 *
	 * @param string               $field_id  Field identifier.
	 * @param mixed                $value     Coerced value.
	 * @param string               $type      Merchant field type.
	 * @param array<string, mixed> $field_def Full field definition.
	 *
	 * @return array<string, mixed>|null Error array, or null if valid.
	 */
	private function validate_constraints( $field_id, $value, $type, $field_def ) {
		// Enum check.
		if ( in_array( $type, array( 'select', 'radio', 'radio_alt' ), true ) ) {
			if ( isset( $field_def['options'] ) ) {
				$allowed = array_keys( $field_def['options'] );
			} elseif ( isset( $field_def['choices'] ) && is_array( $field_def['choices'] ) ) {
				$allowed = $field_def['choices'];
			} else {
				$allowed = null;
			}

			if ( null !== $allowed && ! in_array( $value, $allowed, true ) ) {
				return array(
					'code'           => 'invalid_field_value',
					'field'          => $field_id,
					'message'        => sprintf( "Value '%s' is not a valid option for '%s'.", $value, $field_id ),
					'allowed_values' => $allowed,
				);
			}
		}

		// Min/Max check.
		if ( in_array( $type, array( 'number', 'range' ), true ) ) {
			if ( isset( $field_def['min'] ) && $value < $field_def['min'] ) {
				return array(
					'code'    => 'invalid_field_value',
					'field'   => $field_id,
					'message' => sprintf( "Value %s is below minimum %s for '%s'.", $value, $field_def['min'], $field_id ),
				);
			}
			if ( isset( $field_def['max'] ) && $value > $field_def['max'] ) {
				return array(
					'code'    => 'invalid_field_value',
					'field'   => $field_id,
					'message' => sprintf( "Value %s exceeds maximum %s for '%s'.", $value, $field_def['max'], $field_id ),
				);
			}
		}

		return null;
	}

	/**
	 * Sanitize a value using WordPress sanitization functions.
	 *
	 * @param mixed                $value     The value to sanitize.
	 * @param string               $type      Merchant field type.
	 * @param array<string, mixed> $field_def Full field definition.
	 *
	 * @return mixed Sanitized value.
	 */
	private function sanitize_value( $value, $type, $field_def ) {
		switch ( $type ) {
			case 'gallery':
				// Not delegated: the field class only sanitize_text_field()s; the absint/CSV/cap hardening lives here.
				$raw_ids = array_filter( explode( ',', (string) $value ) );
				$ids     = array_filter( array_map( 'absint', $raw_ids ) );
				$ids     = array_slice( array_values( $ids ), 0, $this->get_array_cap( $type ) );
				return implode( ',', $ids );

			case 'select_ajax':
				return $this->sanitize_select_ajax( $value, $field_def );

			case 'color':
			case 'date_time':
				return sanitize_text_field( $value );

			case 'select':
			case 'radio':
			case 'radio_alt':
				// Enum-constrained already; sanitize_text_field (not sanitize_key) preserves mixed-case keys like popFade.
				return sanitize_text_field( $value );

			case 'fields_group':
				return $this->validate_fields_group( $field_def['id'], $value, $field_def );
		}

		$registry = $this->registry();

		if ( ! $registry->has( $type ) ) {
			return is_array( $value ) ? array() : sanitize_text_field( (string) $value );
		}

		/** @var Merchant_Field_Interface $field */
		$field = $registry->create( $type, $field_def, $value );

		// preprocess() only where the AI array shape matches the human $_POST shape; reviews_selector's would corrupt the array.
		if ( in_array( $type, array( 'flexible_content', 'textarea_code', 'textarea_multiline' ), true ) ) {
			$value = $field->preprocess( $value );
		}

		$value = $field->sanitize( $value );

		// preserve_keys = true so an under-cap value matches the human save path; object kinds are never sliced (would drop named keys).
		if ( is_array( $value ) && 'array' === $this->schema_generator->get_json_type( $type ) ) {
			$value = array_slice( $value, 0, $this->get_array_cap( $type ), true );
		}

		return $value;
	}

	/**
	 * Return the maximum allowed items for an array field type.
	 *
	 * Defaults may be overridden via the merchant_ability_array_limits filter.
	 *
	 * @param string $type Field type key.
	 *
	 * @return int The cap for the given type, or PHP_INT_MAX if uncapped.
	 */
	private function get_array_cap( $type ) {
		$defaults = array(
			'gallery'                 => 50,
			'select_ajax'             => 200,
			'checkbox_multiple'       => 200,
			'choices'                 => 200,
			'reviews_selector'        => 200,
			'sortable'                => 200,
			'sortable_repeater'       => 200,
			'sortable_repeater_icons' => 200,
			// Shares the create-campaign cap filter key so both write paths use one ceiling.
			'flexible_content'        => 100,
		);

		/**
		 * Filter the maximum number of items allowed per array field type.
		 *
		 * @since 2.3.0
		 * @param array $defaults Map of field type => max items.
		 */
		$limits = apply_filters( 'merchant_ability_array_limits', $defaults );

		return isset( $limits[ $type ] ) ? (int) $limits[ $type ] : PHP_INT_MAX;
	}

	/**
	 * Validate and sanitize the sub-fields of a fields_group.
	 *
	 * Routes each sub-field through sanitize_value() rather than delegating the whole
	 * group, so the per-type preprocess decision (e.g. reviews_selector's wire-shape)
	 * and sub-field validation errors are preserved.
	 *
	 * @param string               $parent_id The parent field_group ID (for error reporting).
	 * @param array<string, mixed> $value     The submitted associative array of sub-field values.
	 * @param array<string, mixed> $field_def The fields_group field definition (contains 'fields').
	 *
	 * @return array<string, mixed> The validated and sanitized sub-field values.
	 */
	private function validate_fields_group( $parent_id, $value, $field_def ) {
		if ( ! isset( $field_def['fields'] ) || ! is_array( $field_def['fields'] ) ) {
			return $value;
		}

		$sub_field_defs = array();
		foreach ( $field_def['fields'] as $sub_field ) {
			if ( isset( $sub_field['id'] ) ) {
				$sub_field_defs[ $sub_field['id'] ] = $sub_field;
			}
		}

		$validated = array();
		foreach ( $value as $sub_key => $sub_value ) {
			if ( ! isset( $sub_field_defs[ $sub_key ] ) ) {
				continue;
			}

			$sub_def  = $sub_field_defs[ $sub_key ];
			$sub_type = isset( $sub_def['type'] ) ? $sub_def['type'] : '';

			// Stage 2: Type coercion.
			$type_result = $this->validate_type( $sub_key, $sub_value, $sub_type, $sub_def );
			if ( null !== $type_result['error'] ) {
				$type_result['error']['field'] = $parent_id . '.' . $type_result['error']['field'];
				$this->fields_group_errors[]   = $type_result['error'];
				continue;
			}
			$sub_value = $type_result['value'];

			// Stage 3: Constraint enforcement.
			$constraint_error = $this->validate_constraints( $sub_key, $sub_value, $sub_type, $sub_def );
			if ( null !== $constraint_error ) {
				$constraint_error['field']   = $parent_id . '.' . $constraint_error['field'];
				$this->fields_group_errors[] = $constraint_error;
				continue;
			}

			// Stage 4: Sanitization.
			$sub_value = $this->sanitize_value( $sub_value, $sub_type, $sub_def );

			$validated[ $sub_key ] = $sub_value;
		}

		// Preserve the virtual display-status (active/inactive) control.
		if ( 
			Merchant_Schema_Generator::ensure_field_classes()
			&& Merchant_Field_Fields_Group::has_status_field( $field_def ) 
		) {
			$status_key = $field_def['id'] . '_status';
			if ( isset( $value[ $status_key ] ) ) {
				$status_def = Merchant_Field_Fields_Group::get_status_field_definition( $field_def );
				$allowed    = array_keys( $status_def['options'] );
				$raw        = is_string( $value[ $status_key ] ) ? sanitize_text_field( $value[ $status_key ] ) : '';
				if ( in_array( $raw, $allowed, true ) ) {
					$validated[ $status_key ] = $raw;
				} else {
					$this->fields_group_errors[] = array(
						'code'    => 'invalid_field_value',
						'field'   => $parent_id . '.' . $status_key,
						'message' => sprintf( "Field '%s' must be one of: %s.", $status_key, implode( ', ', $allowed ) ),
					);
				}
			}
		}

		return $validated;
	}

	/**
	 * Sanitize a select_ajax value.
	 *
	 * When inline options are provided, values are kept verbatim if they match
	 * the allowlist — preserving percent-encoded taxonomy slugs (e.g. Arabic
	 * category slugs that sanitize_text_field would destroy).
	 * Mirrors Merchant_Field_Select_Ajax::sanitize_value().
	 *
	 * @param mixed                $value     Scalar or array of submitted values.
	 * @param array<string, mixed> $field_def Field definition (may include 'options').
	 *
	 * @return mixed Sanitized scalar or array.
	 */
	private function sanitize_select_ajax( $value, $field_def ) {
		$has_options = ! empty( $field_def['options'] ) && is_array( $field_def['options'] );

		if ( $has_options ) {
			$valid_ids = wp_list_pluck( $field_def['options'], 'id' );
			$cap       = $this->get_array_cap( 'select_ajax' );

			if ( is_array( $value ) ) {
				$filtered = array_values(
					array_filter(
						$value,
						static function ( $v ) use ( $valid_ids ) {
							return in_array( $v, $valid_ids, true );
						}
					)
				);
				return array_slice( $filtered, 0, $cap );
			}

			return in_array( $value, $valid_ids, true ) ? $value : '';
		}

		// AJAX-loaded source: sanitize normally.
		if ( is_array( $value ) ) {
			$cap       = $this->get_array_cap( 'select_ajax' );
			$sanitized = array_filter( array_map( 'sanitize_text_field', $value ) );
			return array_slice( array_values( $sanitized ), 0, $cap );
		}

		return sanitize_text_field( $value );
	}

	/**
	 * Build a type mismatch error.
	 *
	 * @param string $field_id      Field identifier.
	 * @param string $expected_type Expected type description.
	 *
	 * @return array<string, string> Error array.
	 */
	private function make_type_error( $field_id, $expected_type ) {
		return array(
			'code'    => 'invalid_field_value',
			'field'   => $field_id,
			'message' => sprintf( "Field '%s' expects a %s value.", $field_id, $expected_type ),
		);
	}
}
