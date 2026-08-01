<?php
/**
 * Product Settings Validator.
 *
 * Thin wrapper over Merchant_Field_Validator::validate_against_spec() that
 * adds the array-shaped field types the product-settings descriptors use:
 * multiselect, user_list, url_list, uploads, media, media_url. Scalar types
 * (select, radio, switcher, number, range, color, date_time, text) delegate
 * straight to the shared field validator.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Product_Settings_Validator
 *
 * @since 2.3.0
 */
class Merchant_Product_Settings_Validator {

	/**
	 * Field types handled directly by this wrapper rather than delegated.
	 *
	 * @var array<int, string>
	 */
	private const WRAPPER_TYPES = array( 'multiselect', 'user_list', 'url_list', 'uploads', 'media', 'media_url' );

	/**
	 * The shared field validator scalar types delegate to.
	 *
	 * @var Merchant_Field_Validator
	 */
	private $field_validator;

	/**
	 * URL acceptance policy for media_url/url_list values: site-attachment
	 * OR registered oEmbed provider.
	 *
	 * @var callable(string $url, string $kind): (true|array{code: string, message: string})
	 */
	private $is_valid_url;

	/**
	 * URL acceptance policy for uploads src values: site-attachment ONLY —
	 * uploads never consult the oEmbed provider branch.
	 *
	 * @var callable(string $url, string $kind): (true|array{code: string, message: string})
	 */
	private $is_valid_attachment_url;

	/**
	 * Resolves whether a URL matches a registered oEmbed provider, with no
	 * discovery fetch. Ctor-injectable so tests never touch the real
	 * WP_oEmbed provider list.
	 *
	 * @var callable(string $url): (string|false)
	 */
	private $oembed_provider_resolver;

	/**
	 * Constructor.
	 *
	 * @param Merchant_Field_Validator $field_validator          Shared field validator for scalar types.
	 * @param callable|null            $is_valid_url             URL policy for media_url/url_list; defaults to the D-1 media URL policy.
	 * @param callable|null            $oembed_provider_resolver Resolves a URL to its oEmbed provider (or false); defaults to the real WP_oEmbed lookup.
	 * @param callable|null            $is_valid_attachment_url  URL policy for uploads src; defaults to the attachment-only D-1 policy.
	 */
	public function __construct( $field_validator, $is_valid_url = null, $oembed_provider_resolver = null, $is_valid_attachment_url = null ) {
		$this->field_validator          = $field_validator;
		$this->oembed_provider_resolver = null !== $oembed_provider_resolver ? $oembed_provider_resolver : array( __CLASS__, 'default_oembed_provider_resolver' );
		$this->is_valid_url             = null !== $is_valid_url ? $is_valid_url : array( $this, 'validate_media_url' );
		$this->is_valid_attachment_url  = null !== $is_valid_attachment_url ? $is_valid_attachment_url : array( $this, 'validate_attachment_only_url' );
	}

	/**
	 * Validate and sanitize a set of updates against a module's descriptor fields.
	 *
	 * @param array<string, array<string, mixed>> $descriptor_fields Map of meta_key => field_def.
	 * @param array<string, mixed>                 $updates           Key-value pairs to validate.
	 *
	 * @return array{valid: array<string, mixed>, errors: array<int, array<string, mixed>>}
	 */
	public function validate( array $descriptor_fields, array $updates ) {
		$valid  = array();
		$errors = array();

		$scalar_defs  = array();
		$scalar_input = array();

		foreach ( $updates as $field_id => $value ) {
			if ( ! array_key_exists( $field_id, $descriptor_fields ) ) {
				$errors[] = array(
					'code'    => 'unknown_field',
					'field'   => $field_id,
					'message' => sprintf( "Unknown field '%s'.", $field_id ),
				);
				continue;
			}

			$field_def = $descriptor_fields[ $field_id ];
			$type      = isset( $field_def['type'] ) ? $field_def['type'] : '';

			if ( ! in_array( $type, self::WRAPPER_TYPES, true ) ) {
				$scalar_defs[ $field_id ]  = $field_def;
				$scalar_input[ $field_id ] = $value;
				continue;
			}

			$result = $this->validate_wrapper_field( $field_id, $type, $value, $field_def );

			if ( null !== $result['error'] ) {
				$errors[] = $result['error'];
			} else {
				$valid[ $field_id ] = $result['value'];
			}
		}

		if ( ! empty( $scalar_input ) ) {
			$scalar_result = $this->field_validator->validate_against_spec( $scalar_defs, $scalar_input );
			$valid         = array_merge( $valid, $scalar_result['valid'] );
			$errors        = array_merge( $errors, $scalar_result['errors'] );
		}

		return array(
			'valid'  => $valid,
			'errors' => $errors,
		);
	}

	/**
	 * Dispatch a single wrapper-typed field to its dedicated validator.
	 *
	 * @param string               $field_id  Field identifier.
	 * @param string               $type      Wrapper field type.
	 * @param mixed                $value     Input value.
	 * @param array<string, mixed> $field_def Full field definition.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_wrapper_field( $field_id, $type, $value, $field_def ) {
		switch ( $type ) {
			case 'multiselect':
				return $this->validate_multiselect( $field_id, $value, $field_def );
			case 'user_list':
				return $this->validate_user_list( $field_id, $value );
			case 'url_list':
				return $this->validate_url_list( $field_id, $value, $field_def );
			case 'uploads':
				return $this->validate_uploads( $field_id, $value, $field_def );
			case 'media':
				return $this->validate_media( $field_id, $value );
			case 'media_url':
				return $this->validate_media_url_field( $field_id, $value, $field_def );
		}

		return array( 'value' => $value, 'error' => null );
	}

	/**
	 * Validate a multiselect field: array of entries, each in the allowed option set.
	 *
	 * @param string               $field_id  Field identifier.
	 * @param mixed                $value     Input value.
	 * @param array<string, mixed> $field_def Full field definition.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_multiselect( $field_id, $value, $field_def ) {
		if ( ! is_array( $value ) ) {
			return array( 'value' => null, 'error' => $this->type_error( $field_id, 'an array' ) );
		}

		if ( isset( $field_def['options'] ) ) {
			$options = array_keys( $field_def['options'] );
		} elseif ( isset( $field_def['options_callback'] ) ) {
			$options = array_values( (array) call_user_func( $field_def['options_callback'] ) );
		} else {
			$options = array();
		}

		$result = array();
		foreach ( array_slice( $value, 0, 200 ) as $entry ) {
			if ( ! in_array( $entry, $options, true ) ) {
				return array(
					'value' => null,
					'error' => array(
						'code'           => 'invalid_field_value',
						'field'          => $field_id,
						'message'        => sprintf( "Value '%s' is not a valid option for '%s'.", $entry, $field_id ),
						'allowed_values' => $options,
					),
				);
			}
			$result[] = $entry;
		}

		return array( 'value' => $result, 'error' => null );
	}

	/**
	 * Validate a user_list field: array of positive user ids (numeric strings coerced).
	 *
	 * @param string $field_id Field identifier.
	 * @param mixed  $value    Input value.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_user_list( $field_id, $value ) {
		if ( ! is_array( $value ) ) {
			return array( 'value' => null, 'error' => $this->type_error( $field_id, 'an array' ) );
		}

		$result = array();
		foreach ( array_slice( $value, 0, 200 ) as $entry ) {
			if ( ! is_numeric( $entry ) || (int) $entry <= 0 ) {
				return array( 'value' => null, 'error' => $this->type_error( $field_id, 'an array of positive user ids' ) );
			}
			$result[] = (int) $entry;
		}

		return array( 'value' => $result, 'error' => null );
	}

	/**
	 * Validate a url_list field: array of strings, each through the URL policy.
	 *
	 * @param string               $field_id  Field identifier.
	 * @param mixed                $value     Input value.
	 * @param array<string, mixed> $field_def Full field definition.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_url_list( $field_id, $value, $field_def ) {
		if ( ! is_array( $value ) ) {
			return array( 'value' => null, 'error' => $this->type_error( $field_id, 'an array' ) );
		}

		$kind   = isset( $field_def['kind'] ) ? $field_def['kind'] : '';
		$result = array();

		foreach ( array_slice( $value, 0, 50 ) as $entry ) {
			if ( ! is_string( $entry ) ) {
				return array( 'value' => null, 'error' => $this->type_error( $field_id, 'an array of URL strings' ) );
			}

			$check = call_user_func( $this->is_valid_url, $entry, $kind );
			if ( true !== $check ) {
				return array( 'value' => null, 'error' => $this->url_error( $field_id, $check ) );
			}

			$result[] = $entry;
		}

		return array( 'value' => $result, 'error' => null );
	}

	/**
	 * Validate an uploads field: plain string entries, or {src,thumb} objects
	 * when the descriptor sets thumb=true. src always goes through the URL
	 * policy; thumb (when present) is an attachment id.
	 *
	 * @param string               $field_id  Field identifier.
	 * @param mixed                $value     Input value.
	 * @param array<string, mixed> $field_def Full field definition.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_uploads( $field_id, $value, $field_def ) {
		if ( ! is_array( $value ) ) {
			return array( 'value' => null, 'error' => $this->type_error( $field_id, 'an array' ) );
		}

		$has_thumb = ! empty( $field_def['thumb'] );
		$kind      = isset( $field_def['kind'] ) ? $field_def['kind'] : '';
		$result    = array();

		foreach ( array_slice( $value, 0, 50 ) as $entry ) {
			$src = $has_thumb ? ( is_array( $entry ) && isset( $entry['src'] ) ? $entry['src'] : null ) : $entry;

			if ( ! is_string( $src ) ) {
				return array( 'value' => null, 'error' => $this->type_error( $field_id, 'valid upload entries' ) );
			}

			$check = call_user_func( $this->is_valid_attachment_url, $src, $kind );
			if ( true !== $check ) {
				return array( 'value' => null, 'error' => $this->url_error( $field_id, $check ) );
			}

			$result[] = $has_thumb
				? array(
					'src'   => $src,
					'thumb' => isset( $entry['thumb'] ) ? absint( $entry['thumb'] ) : 0,
				)
				: $src;
		}

		return array( 'value' => $result, 'error' => null );
	}

	/**
	 * Validate a media field: a positive attachment id that is an image.
	 *
	 * @param string $field_id Field identifier.
	 * @param mixed  $value    Input value.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_media( $field_id, $value ) {
		if ( ! is_numeric( $value ) || (int) $value <= 0 ) {
			return array( 'value' => null, 'error' => $this->type_error( $field_id, 'a positive attachment id' ) );
		}

		$id = (int) $value;

		if ( 'attachment' !== get_post_type( $id ) || ! wp_attachment_is_image( $id ) ) {
			return array(
				'value' => null,
				'error' => array(
					'code'    => 'invalid_field_value',
					'field'   => $field_id,
					'message' => sprintf( "Field '%s' must reference an image attachment.", $field_id ),
				),
			);
		}

		return array( 'value' => $id, 'error' => null );
	}

	/**
	 * Validate a media_url field: a single URL string through the URL policy.
	 *
	 * @param string               $field_id  Field identifier.
	 * @param mixed                $value     Input value.
	 * @param array<string, mixed> $field_def Full field definition.
	 *
	 * @return array{value: mixed, error: array<string, mixed>|null}
	 */
	private function validate_media_url_field( $field_id, $value, $field_def ) {
		if ( ! is_string( $value ) ) {
			return array( 'value' => null, 'error' => $this->type_error( $field_id, 'a URL string' ) );
		}

		$kind  = isset( $field_def['kind'] ) ? $field_def['kind'] : '';
		$check = call_user_func( $this->is_valid_url, $value, $kind );

		if ( true !== $check ) {
			return array( 'value' => null, 'error' => $this->url_error( $field_id, $check ) );
		}

		return array( 'value' => $value, 'error' => null );
	}

	/**
	 * Build a type mismatch error.
	 *
	 * @param string $field_id Field identifier.
	 * @param string $expected Expected value description.
	 *
	 * @return array<string, string>
	 */
	private function type_error( $field_id, $expected ) {
		return array(
			'code'    => 'invalid_field_value',
			'field'   => $field_id,
			'message' => sprintf( "Field '%s' expects %s.", $field_id, $expected ),
		);
	}

	/**
	 * Build a URL-rejection error, surfacing the policy's structured reason when given one.
	 *
	 * @param string                     $field_id Field identifier.
	 * @param array<string, string>|bool $check    The URL policy's return value (false, or a code/message array).
	 *
	 * @return array<string, string>
	 */
	private function url_error( $field_id, $check ) {
		if ( is_array( $check ) && isset( $check['code'], $check['message'] ) ) {
			return array(
				'code'    => $check['code'],
				'field'   => $field_id,
				'message' => $check['message'],
			);
		}

		return $this->type_error( $field_id, 'an accepted URL' );
	}

	/**
	 * Permissive URL policy: accepts anything. Not used by default — an
	 * explicit opt-in for callers that don't need the D-1 media URL policy.
	 *
	 * @param string $url  The URL to check.
	 * @param string $kind The field kind (e.g. audio|video).
	 *
	 * @return true
	 */
	public static function permissive_url_check( $url, $kind ) {
		return true;
	}

	/**
	 * D-1 media URL policy: a URL is accepted only if its scheme is https
	 * AND it is either a site-attachment whose mime matches $kind, or a URL
	 * matching a registered oEmbed provider with no discovery fetch.
	 *
	 * @param mixed  $url  The URL to check.
	 * @param string $kind The expected media kind ('audio'|'video').
	 *
	 * @return true|array{code: string, message: string}
	 */
	public function validate_media_url( $url, $kind ) {
		return $this->check_media_url_policy( $url, $kind, true );
	}

	/**
	 * D-1 media URL policy, attachment branch only — no oEmbed provider
	 * fallback. Used for uploads src values, which stay attachment-based.
	 *
	 * @param mixed  $url  The URL to check.
	 * @param string $kind The expected media kind ('audio'|'video').
	 *
	 * @return true|array{code: string, message: string}
	 */
	public function validate_attachment_only_url( $url, $kind ) {
		return $this->check_media_url_policy( $url, $kind, false );
	}

	/**
	 * Shared D-1 policy check: https scheme, then site-attachment match,
	 * then — when allowed — a registered oEmbed provider match.
	 *
	 * @param mixed  $url                  The URL to check.
	 * @param string $kind                 The expected media kind ('audio'|'video').
	 * @param bool   $allow_provider_match Whether the oEmbed provider branch may be consulted.
	 *
	 * @return true|array{code: string, message: string}
	 */
	private function check_media_url_policy( $url, $kind, $allow_provider_match ) {
		if ( ! is_string( $url ) || 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) ) {
			return $this->media_url_rejection( $url );
		}

		$attachment_id = attachment_url_to_postid( $url );
		if ( $attachment_id > 0 ) {
			$mime = get_post_mime_type( $attachment_id );
			if ( is_string( $mime ) && 0 === strpos( $mime, $kind . '/' ) ) {
				return true;
			}
		}

		if ( $allow_provider_match ) {
			$provider = call_user_func( $this->oembed_provider_resolver, $url );
			if ( false !== $provider ) {
				return true;
			}
		}

		return $this->media_url_rejection( $url );
	}

	/**
	 * Resolve a URL's oEmbed provider with no discovery fetch. Real default
	 * for the injectable oembed_provider_resolver.
	 *
	 * @param string $url The URL to resolve.
	 *
	 * @return string|false
	 */
	public static function default_oembed_provider_resolver( $url ) {
		return _wp_oembed_get_object()->get_provider( $url, array( 'discover' => false ) );
	}

	/**
	 * Build the D-1 policy rejection error.
	 *
	 * @param mixed $url The rejected value.
	 *
	 * @return array{code: string, message: string}
	 */
	private function media_url_rejection( $url ) {
		return array(
			'code'    => 'invalid_media_url',
			'message' => sprintf(
				"URL '%s' is not accepted — it must be https and either a site-attachment matching the expected media type, or a registered oEmbed provider URL.",
				is_string( $url ) ? $url : gettype( $url )
			),
		);
	}
}
