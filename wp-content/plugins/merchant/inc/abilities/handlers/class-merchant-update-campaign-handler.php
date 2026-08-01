<?php
/**
 * Merchant Update Campaign Handler.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Update_Campaign_Handler
 *
 * Updates campaign fields with validation and diff reporting.
 * Uses the registry adapter for campaign field detection.
 *
 * @since 2.3.0
 */
class Merchant_Update_Campaign_Handler extends Merchant_Abstract_Handler {

	/**
	 * Campaign resolver instance.
	 *
	 * @var Merchant_Campaign_Resolver
	 */
	private $campaign_resolver;

	/**
	 * Abilities registry for adapter resolution.
	 *
	 * @var Merchant_Abilities_Registry
	 */
	private $registry;

	/**
	 * Field validator instance.
	 *
	 * @var Merchant_Field_Validator|null
	 */
	private $field_validator;

	/**
	 * Constructor.
	 *
	 * @param Merchant_Campaign_Resolver  $campaign_resolver Campaign resolver.
	 * @param Merchant_Abilities_Registry $registry          Abilities registry.
	 * @param Merchant_Field_Validator    $field_validator    Field validator.
	 */
	public function __construct( $campaign_resolver, $registry, $field_validator = null ) {
		$this->campaign_resolver = $campaign_resolver;
		$this->registry          = $registry;
		$this->field_validator   = $field_validator;
	}

	/**
	 * Handle the update-campaign request.
	 *
	 * @param array<string, mixed> $params { module_id, campaign_identifier, updates }
	 *
	 * @return array<string, mixed>|WP_Error Response envelope or WP_Error on failure.
	 */
	public function handle( $params ) {
		$module_id  = isset( $params['module_id'] ) ? $params['module_id'] : '';
		$identifier = isset( $params['campaign_identifier'] ) ? $params['campaign_identifier'] : null;
		$updates    = isset( $params['updates'] ) ? $params['updates'] : array();

		$error = $this->preflight_write( $module_id );
		if ( null !== $error ) {
			return $error;
		}

		$resolved_adapter = $this->resolve_campaign_adapter( $this->registry, $module_id );
		if ( isset( $resolved_adapter['error'] ) ) {
			return $resolved_adapter['error'];
		}

		$campaign_field = $resolved_adapter['campaign_field'];
		$title_field    = $resolved_adapter['title_field'];

		// Resolve campaign.
		$resolved = $this->campaign_resolver->resolve( $module_id, $identifier, $campaign_field, $title_field );
		if ( isset( $resolved['error'] ) ) {
			$error_data = array( 'status' => 404 );
			if ( isset( $resolved['error']['available'] ) ) {
				$error_data['available'] = $resolved['error']['available'];
			}

			return new WP_Error(
				$resolved['error']['code'],
				$resolved['error']['message'],
				$error_data
			);
		}

		$index    = $resolved['index'];
		$campaign = $resolved['campaign'];

		// Validate when schemas are available.
		$validation_warnings = array();
		if ( null !== $this->field_validator ) {
			$known_fields = $this->registry->get_schema_generator()->get_campaign_field_ids(
				$module_id,
				$campaign_field
			);

			if ( empty( $known_fields ) ) {
				return new WP_Error(
					'schema_unavailable',
					sprintf( "Campaign schema for module '%s' has no known fields. Cannot validate input.", $module_id ),
					array( 'status' => 422 )
				);
			}

			$validation          = $this->field_validator->validate_campaign_updates(
				$module_id,
				$campaign_field,
				$updates
			);
			$updates             = $validation['valid'];
			$validation_warnings = $validation['errors'];
		}

		if ( empty( $updates ) && ! empty( $validation_warnings ) ) {
			return new WP_Error(
				'ability_invalid_input',
				'All submitted fields failed validation.',
				array(
					'status' => 400,
					'errors' => $validation_warnings,
				)
			);
		}

		// Apply validated updates and build diff.
		$schema_generator = $this->registry->get_schema_generator();
		$diff             = array();
		foreach ( $updates as $field_id => $new_value ) {
			$existing  = isset( $campaign[ $field_id ] ) ? $campaign[ $field_id ] : null;
			$field_def = $schema_generator->get_campaign_field_definition( $module_id, $campaign_field, $field_id );
			$is_group  = $field_def && isset( $field_def['type'] ) && 'fields_group' === $field_def['type'];

			// Merge group sub-fields into the existing group so a partial update (e.g. toggling
			// a group status flag) does not drop un-submitted sub-fields. Other field types replace.
			$applied = ( $is_group && is_array( $existing ) && is_array( $new_value ) )
				? array_merge( $existing, $new_value )
				: $new_value;

			$diff[ $field_id ]     = array( 'previous_value' => $existing, 'new_value' => $applied );
			$campaign[ $field_id ] = $applied;
		}

		// Save only if there are valid updates.
		if ( ! empty( $diff ) ) {
			$module    = Merchant_Modules::get_module( $module_id );
			$settings  = $module ? $module->get_module_settings() : array();
			$campaigns = isset( $settings[ $campaign_field ] ) ? $settings[ $campaign_field ] : array();

			$campaigns[ $index ] = $campaign;

			$this->save_module_setting( $module_id, $campaign_field, $campaigns );
		}

		return array(
			'success'  => true,
			'data'     => array(
				'module_id'      => $module_id,
				'campaign_index' => $index,
				'updated_fields' => $diff,
			),
			'warnings' => $validation_warnings,
		);
	}
}
