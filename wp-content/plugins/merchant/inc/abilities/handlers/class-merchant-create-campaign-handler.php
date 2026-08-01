<?php
/**
 * Merchant Create Campaign Handler.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Create_Campaign_Handler
 *
 * Creates a new campaign with field validation.
 * Uses the registry adapter for campaign field detection.
 *
 * @since 2.3.0
 */
class Merchant_Create_Campaign_Handler extends Merchant_Abstract_Handler {

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
	 * @param Merchant_Abilities_Registry $registry        Abilities registry.
	 * @param Merchant_Field_Validator    $field_validator  Field validator.
	 */
	public function __construct( $registry, $field_validator = null ) {
		$this->registry        = $registry;
		$this->field_validator = $field_validator;
	}

	/**
	 * Return the maximum number of campaigns allowed per module.
	 *
	 * Uses the merchant_ability_array_limits filter key 'flexible_content'.
	 * Default is 100.
	 *
	 * @return int
	 */
	private function get_campaign_cap() {
		$defaults = array( 'flexible_content' => 100 );

		/**
		 * Filter the maximum number of items allowed per array field type.
		 *
		 * @since 2.3.0
		 * @param array $defaults Map of field type => max items.
		 */
		$limits = apply_filters( 'merchant_ability_array_limits', $defaults );

		return isset( $limits['flexible_content'] ) ? (int) $limits['flexible_content'] : 100;
	}

	/**
	 * Handle the create-campaign request.
	 *
	 * @param array<string, mixed> $params { module_id: string, campaign: array<string, mixed> }
	 *
	 * @return array<string, mixed>|WP_Error Response envelope or WP_Error on failure.
	 */
	public function handle( $params ) {
		$module_id     = isset( $params['module_id'] ) ? $params['module_id'] : '';
		$campaign_data = isset( $params['campaign'] ) ? $params['campaign'] : array();

		$error = $this->preflight_write( $module_id );
		if ( null !== $error ) {
			return $error;
		}

		$resolved = $this->resolve_campaign_adapter( $this->registry, $module_id );
		if ( isset( $resolved['error'] ) ) {
			return $resolved['error'];
		}

		$campaign_field = $resolved['campaign_field'];

		// Extract structural fields before validation.
		// These are not user-facing fields and would be rejected as unknown.
		$caller_layout      = isset( $campaign_data['layout'] ) ? $campaign_data['layout'] : '';
		$caller_flexible_id = isset( $campaign_data['flexible_id'] ) ? $campaign_data['flexible_id'] : '';
		unset( $campaign_data['layout'], $campaign_data['flexible_id'] );

		// Validate campaign fields.
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

			$validation = $this->field_validator->validate_campaign_updates(
				$module_id,
				$campaign_field,
				$campaign_data
			);
			if ( ! empty( $validation['errors'] ) ) {
				return new WP_Error(
					'ability_invalid_input',
					'Campaign data failed validation. No campaign was created — correct the fields listed in "errors" and retry.',
					array(
						'status' => 400,
						'errors' => $validation['errors'],
					)
				);
			}
			$campaign_data = $validation['valid'];
		}

		// Inject structural fields required by the flexible_content system.
		// Use caller-provided values if present, otherwise auto-generate.
		if ( ! empty( $caller_layout ) ) {
			$campaign_data['layout'] = $caller_layout;
		} else {
			$layout_key = $this->registry->get_schema_generator()->get_default_layout_key(
				$module_id,
				$campaign_field
			);
			if ( null !== $layout_key ) {
				$campaign_data['layout'] = $layout_key;
			}
		}

		if ( ! empty( $caller_flexible_id ) ) {
			$campaign_data['flexible_id'] = $caller_flexible_id;
		} else {
			$campaign_data['flexible_id'] = wp_generate_uuid4();
		}

		$module    = Merchant_Modules::get_module( $module_id );
		$settings  = $module ? $module->get_module_settings() : array();
		$campaigns = isset( $settings[ $campaign_field ] ) && is_array( $settings[ $campaign_field ] )
			? $settings[ $campaign_field ]
			: array();

		$cap = $this->get_campaign_cap();
		if ( count( $campaigns ) >= $cap ) {
			return new WP_Error(
				'campaign_limit_reached',
				sprintf( 'Campaign limit of %d reached for module \'%s\'. Delete an existing campaign before adding another.', $cap, $module_id ),
				array( 'status' => 422 )
			);
		}

		// Append new campaign.
		$new_index   = count( $campaigns );
		$campaigns[] = $campaign_data;

		$settings[ $campaign_field ] = $campaigns;
		$this->save_module_settings( $module_id, $settings );

		return array(
			'success' => true,
			'data'    => array(
				'module_id' => $module_id,
				'index'     => $new_index,
				'campaign'  => $campaign_data,
			),
		);
	}
}
