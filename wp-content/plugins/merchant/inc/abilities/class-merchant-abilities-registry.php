<?php
/**
 * Merchant Abilities Registry.
 *
 * Registers all gateway abilities with WordPress and manages
 * module adapter resolution.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Abilities_Registry
 *
 * Central registry that registers all Merchant abilities with the
 * WP Abilities API and resolves module adapters for campaign
 * summary formatting.
 *
 * @since 2.3.0
 */
class Merchant_Abilities_Registry {

	/**
	 * Module IDs excluded from all ability surfaces.
	 *
	 * Single source of truth — handlers reference this
	 * instead of maintaining their own copies.
	 *
	 * @var array<int, string>
	 */
	private static $excluded_module_ids = array( 'global-settings' );

	/**
	 * Check if a module is excluded from abilities.
	 *
	 * @param string $module_id The module identifier.
	 *
	 * @return bool True if excluded.
	 */
	public static function is_excluded( $module_id ) {
		return in_array( $module_id, self::$excluded_module_ids, true );
	}

	/**
	 * Schema generator instance.
	 *
	 * @var Merchant_Schema_Generator
	 */
	private $schema_generator;

	/**
	 * Field validator instance.
	 *
	 * @var Merchant_Field_Validator
	 */
	private $field_validator;

	/**
	 * Campaign resolver instance.
	 *
	 * @var Merchant_Campaign_Resolver
	 */
	private $campaign_resolver;

	/**
	 * Registered module adapters.
	 *
	 * @var array<string, Merchant_Module_Abilities_Interface>
	 */
	private $adapters = array();

	/**
	 * Ability definitions contributed by other plugins (e.g. merchant-pro).
	 *
	 * @var array<lowercase-string&non-falsy-string, array<string, mixed>>
	 */
	private $contributed = array();

	/**
	 * Constructor.
	 *
	 * @param Merchant_Schema_Generator  $schema_generator  Schema generator instance.
	 * @param Merchant_Field_Validator   $field_validator    Field validator instance.
	 * @param Merchant_Campaign_Resolver $campaign_resolver  Campaign resolver instance.
	 */
	public function __construct( $schema_generator, $field_validator, $campaign_resolver ) {
		$this->schema_generator  = $schema_generator;
		$this->field_validator   = $field_validator;
		$this->campaign_resolver = $campaign_resolver;
	}

	/**
	 * Register all 10 gateway abilities with WordPress, followed by any
	 * abilities contributed via register_ability_definition().
	 *
	 * @return void
	 */
	public function register_all_abilities() {
		$abilities = $this->get_ability_definitions();

		foreach ( $abilities as $ability_id => $definition ) {
			wp_register_ability( $ability_id, $definition );
		}

		foreach ( $this->contributed as $ability_id => $definition ) {
			wp_register_ability( $ability_id, $definition );
		}
	}

	/**
	 * Contribute an ability definition to be registered alongside the core map.
	 *
	 * Lets other plugins (e.g. merchant-pro) add abilities without
	 * modifying the core definitions. Contributions are deduped by id;
	 * registering the same id again overwrites the previous definition.
	 *
	 * @param lowercase-string&non-falsy-string $id  The ability identifier.
	 * @param array<string, mixed>              $def The ability definition, in the same
	 *                                                shape as wp_register_ability() expects.
	 *
	 * @return void
	 */
	public function register_ability_definition( $id, $def ) {
		$this->contributed[ $id ] = $def;
	}

	/**
	 * Register a custom module adapter.
	 *
	 * @param string                             $module_id The module identifier.
	 * @param Merchant_Module_Abilities_Interface $adapter   The adapter instance.
	 *
	 * @return void
	 */
	public function register_adapter( $module_id, $adapter ) {
		$this->adapters[ $module_id ] = $adapter;
	}

	/**
	 * Get the adapter for a module.
	 *
	 * Returns a custom adapter if registered, otherwise a default adapter.
	 *
	 * @param string $module_id The module identifier.
	 *
	 * @return Merchant_Module_Abilities_Interface
	 */
	public function get_adapter( $module_id ) {
		if ( isset( $this->adapters[ $module_id ] ) ) {
			return $this->adapters[ $module_id ];
		}

		return new Merchant_Default_Module_Adapter( $module_id );
	}

	/**
	 * Get the schema generator instance.
	 *
	 * @return Merchant_Schema_Generator
	 */
	public function get_schema_generator() {
		return $this->schema_generator;
	}

	/**
	 * Get the field validator instance.
	 *
	 * @return Merchant_Field_Validator
	 */
	public function get_field_validator() {
		return $this->field_validator;
	}

	/**
	 * Get the campaign resolver instance.
	 *
	 * @return Merchant_Campaign_Resolver
	 */
	public function get_campaign_resolver() {
		return $this->campaign_resolver;
	}

	/**
	 * Build all 10 ability definition arrays.
	 *
	 * @return array<lowercase-string&non-falsy-string, array<string, mixed>>
	 */
	private function get_ability_definitions() {
		$registry = $this;

		return array(
			'merchant/list-modules'           => array(
				'label'               => __( 'List Merchant Modules', 'merchant' ),
				'description'         => __( 'Discover all available Merchant modules with their types and status.', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
				),
				'input_schema'        => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'filter'  => array(
							'type'        => 'string',
							'description' => 'Filter by status: active, inactive, or empty for all.',
							'enum'        => array( 'active', 'inactive' ),
							'default'     => '',
						),
						'section' => array(
							'type'        => 'string',
							'description' => 'Filter by section slug.',
							'default'     => '',
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => 'Module listing with counts.',
					'required'    => array( 'success', 'data' ),
					'properties'  => array(
						'success' => array( 'type' => 'boolean' ),
						'data'    => array(
							'type'       => 'object',
							'properties' => array(
								'modules'      => array(
									'type'  => 'array',
									'items' => array(
										'type'       => 'object',
										'properties' => array(
											'module_id' => array( 'type' => 'string' ),
											'is_active' => array( 'type' => 'boolean' ),
											'is_pro'    => array( 'type' => 'boolean' ),
											'section'   => array( 'type' => 'string' ),
										),
									),
								),
								'total_count'  => array( 'type' => 'integer' ),
								'active_count' => array( 'type' => 'integer' ),
							),
						),
					),
				),
				'permission_callback' => function () {
					return Merchant_Abilities_Permissions::check( 'merchant/list-modules' );
				},
				'execute_callback'    => function ( $params = array() ) {
					$handler = new Merchant_List_Modules_Handler();
					return $handler->handle( $params );
				},
			),
			'merchant/toggle-module'          => array(
				'label'               => __( 'Toggle Merchant Module', 'merchant' ),
				'description'         => __( 'Activate or deactivate a Merchant module.', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => true,
					),
				),
				'input_schema'        => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'module_id' => array(
							'type'        => 'string',
							'minLength'   => 1,
							'description' => 'The module identifier.',
						),
						'action'    => array(
							'type'        => 'string',
							'description' => 'activate or deactivate.',
							'enum'        => array( 'activate', 'deactivate' ),
						),
					),
					'required'             => array( 'module_id', 'action' ),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => 'Toggle result with before/after status.',
					'required'    => array( 'success', 'data' ),
					'properties'  => array(
						'success' => array( 'type' => 'boolean' ),
						'data'    => array(
							'type'       => 'object',
							'properties' => array(
								'module_id'       => array( 'type' => 'string' ),
								'previous_status' => array( 'type' => 'string' ),
								'new_status'      => array( 'type' => 'string' ),
							),
						),
					),
				),
				'permission_callback' => function () {
					return Merchant_Abilities_Permissions::check( 'merchant/toggle-module' );
				},
				'execute_callback'    => function ( $params = array() ) {
					$handler = new Merchant_Toggle_Module_Handler();
					return $handler->handle( $params );
				},
			),
			'merchant/get-module-settings'    => array(
				'label'               => __( 'Get Module Settings', 'merchant' ),
				'description'         => __( 'Read a module\'s settings with full field schemas.', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
				),
				'input_schema'        => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'module_id'      => array(
							'type'        => 'string',
							'minLength'   => 1,
							'description' => 'The module identifier.',
						),
						'include_schema' => array(
							'type'        => 'boolean',
							'description' => 'Include field schemas in response.',
							'default'     => true,
						),
					),
					'required'             => array( 'module_id' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'data'    => array(
							'type'       => 'object',
							'properties' => array(
								'module_id' => array( 'type' => 'string' ),
								'is_active' => array( 'type' => 'boolean' ),
								'settings'  => array( 'type' => 'object' ),
							),
						),
					),
				),
				'permission_callback' => function () {
					return Merchant_Abilities_Permissions::check( 'merchant/get-module-settings' );
				},
				'execute_callback'    => function ( $params = array() ) use ( $registry ) {
					$handler = new Merchant_Get_Settings_Handler( $registry->get_schema_generator() );
					return $handler->handle( $params );
				},
			),
			'merchant/update-module-settings' => array(
				'label'               => __( 'Update Module Settings', 'merchant' ),
				'description'         => __( 'Update a module\'s settings with validation and before/after reporting.', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => true,
					),
				),
				'input_schema'        => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'module_id' => array(
							'type'        => 'string',
							'minLength'   => 1,
							'description' => 'The module identifier.',
						),
						'updates'   => array(
							'type'          => 'object',
							'description'   => 'Key-value pairs of settings to update.',
							'maxProperties' => 50,
						),
					),
					'required'             => array( 'module_id', 'updates' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'  => array( 'type' => 'boolean' ),
						'data'     => array(
							'type'       => 'object',
							'properties' => array(
								'updated_fields' => array( 'type' => 'object' ),
								'updated_count'  => array( 'type' => 'integer' ),
							),
						),
						'errors'   => array( 'type' => 'array' ),
						'warnings' => array( 'type' => 'array' ),
					),
				),
				'permission_callback' => function () {
					return Merchant_Abilities_Permissions::check( 'merchant/update-module-settings' );
				},
				'execute_callback'    => function ( $params = array() ) use ( $registry ) {
					$handler = new Merchant_Update_Settings_Handler(
						$registry->get_field_validator()
					);
					return $handler->handle( $params );
				},
			),
			'merchant/list-campaigns'         => array(
				'label'               => __( 'List Module Campaigns', 'merchant' ),
				'description'         => __( 'List campaigns with configuration summaries and analytics snapshots.', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
				),
				'input_schema'        => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'module_id' => array(
							'type'        => 'string',
							'minLength'   => 1,
							'description' => 'The module identifier.',
						),
					),
					'required'             => array( 'module_id' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'data'    => array(
							'type'       => 'object',
							'properties' => array(
								'module_id'    => array( 'type' => 'string' ),
								'campaigns'    => array(
									'type'  => 'array',
									'items' => array(
										'type'       => 'object',
										'properties' => array(
											'index'   => array( 'type' => 'integer' ),
											'name'    => array( 'type' => 'string' ),
											'summary' => array( 'type' => 'object' ),
										),
									),
								),
								'total_count'  => array( 'type' => 'integer' ),
								'field_schema' => array(
									'type'        => 'object',
									'description' => 'Per-field JSON schema for this module\'s campaign layout, including x-merchant-* AI guidance.',
								),
							),
						),
					),
				),
				'permission_callback' => function () {
					return Merchant_Abilities_Permissions::check( 'merchant/list-campaigns' );
				},
				'execute_callback'    => function ( $params = array() ) use ( $registry ) {
					$handler = new Merchant_List_Campaigns_Handler( $registry );
					return $handler->handle( $params );
				},
			),
			'merchant/create-campaign'        => array(
				'label'               => __( 'Create Campaign', 'merchant' ),
				'description'         => __( 'Create a new campaign with defaults and validation. Call merchant/list-campaigns or merchant/get-module-settings (include_schema=true) first to read the field contract and per-field usage hints.', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => false,
					),
				),
				'input_schema'        => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'module_id' => array(
							'type'        => 'string',
							'minLength'   => 1,
							'description' => 'The module identifier.',
						),
						'campaign'  => array(
							'type'          => 'object',
							'description'   => 'Campaign data to create.',
							'maxProperties' => 50,
						),
					),
					'required'             => array( 'module_id' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'data'    => array(
							'type'       => 'object',
							'properties' => array(
								'module_id' => array( 'type' => 'string' ),
								'index'     => array( 'type' => 'integer' ),
								'campaign'  => array( 'type' => 'object' ),
							),
						),
					),
				),
				'permission_callback' => function () {
					return Merchant_Abilities_Permissions::check( 'merchant/create-campaign' );
				},
				'execute_callback'    => function ( $params = array() ) use ( $registry ) {
					$handler = new Merchant_Create_Campaign_Handler(
						$registry,
						$registry->get_field_validator()
					);
					return $handler->handle( $params );
				},
			),
			'merchant/update-campaign'        => array(
				'label'               => __( 'Update Campaign', 'merchant' ),
				'description'         => __( 'Update specific fields of an existing campaign with validation and diff reporting. Call merchant/list-campaigns or merchant/get-module-settings (include_schema=true) first to read the field contract and per-field usage hints.', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => true,
					),
				),
				'input_schema'        => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'module_id'           => array(
							'type'        => 'string',
							'minLength'   => 1,
							'description' => 'The module identifier.',
						),
						'campaign_identifier' => array(
							'type'        => 'string',
							'minLength'   => 1,
							'description' => 'Campaign flexible_id UUID, numeric index (0-based), or campaign name.',
						),
						'updates'             => array(
							'type'        => 'object',
							'description' => 'Key-value pairs to update.',
						),
					),
					'required'             => array( 'module_id', 'campaign_identifier', 'updates' ),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => 'Update result with before/after diff and optional validation warnings.',
					'required'    => array( 'success', 'data' ),
					'properties'  => array(
						'success'  => array( 'type' => 'boolean' ),
						'data'     => array(
							'type'       => 'object',
							'properties' => array(
								'module_id'      => array( 'type' => 'string' ),
								'campaign_index' => array( 'type' => 'integer' ),
								'updated_fields' => array( 'type' => 'object' ),
							),
						),
						'warnings' => array( 'type' => 'array' ),
					),
				),
				'permission_callback' => function () {
					return Merchant_Abilities_Permissions::check( 'merchant/update-campaign' );
				},
				'execute_callback'    => function ( $params = array() ) use ( $registry ) {
					$handler = new Merchant_Update_Campaign_Handler(
						$registry->get_campaign_resolver(),
						$registry,
						$registry->get_field_validator()
					);
					return $handler->handle( $params );
				},
			),
			'merchant/delete-campaign'        => array(
				'label'               => __( 'Delete Campaign', 'merchant' ),
				'description'         => __( 'Delete a campaign with confirmation guard.', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => true,
						'idempotent'  => true,
					),
				),
				'input_schema'        => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'module_id'           => array(
							'type'        => 'string',
							'minLength'   => 1,
							'description' => 'The module identifier.',
						),
						'campaign_identifier' => array(
							'type'        => 'string',
							'minLength'   => 1,
							'description' => 'Campaign flexible_id UUID. Use merchant/list-campaigns to find it.',
						),
						'confirm'             => array(
							'type'        => 'boolean',
							'description' => 'Confirmation guard.',
							'default'     => false,
						),
					),
					'required'             => array( 'module_id', 'campaign_identifier', 'confirm' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'data'    => array(
							'type'       => 'object',
							'properties' => array(
								'module_id'                 => array( 'type' => 'string' ),
								'deleted_index'             => array( 'type' => 'integer' ),
								'deleted_name'              => array( 'type' => 'string' ),
								'remaining_campaigns_count' => array( 'type' => 'integer' ),
							),
						),
					),
				),
				'permission_callback' => function () {
					return Merchant_Abilities_Permissions::check( 'merchant/delete-campaign' );
				},
				'execute_callback'    => function ( $params = array() ) use ( $registry ) {
					$handler = new Merchant_Delete_Campaign_Handler(
						$registry->get_campaign_resolver(),
						$registry
					);
					return $handler->handle( $params );
				},
			),
			'merchant/get-analytics'          => array(
				'label'               => __( 'Get Analytics', 'merchant' ),
				'description'         => __( 'View analytics at global, module, or campaign scope.', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
				),
				'input_schema'        => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'scope'       => array(
							'type'        => 'string',
							'description' => 'Analytics scope: global, module, or campaign.',
							'enum'        => array( 'global', 'module', 'campaign' ),
							'default'     => 'global',
						),
						'module_id'   => array(
							'type'        => 'string',
							'description' => 'Module identifier (required for module/campaign scope).',
						),
						'campaign_id' => array(
							'type'        => 'string',
							'description' => 'Campaign identifier (required for campaign scope).',
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'data'    => array(
							'type'       => 'object',
							'properties' => array(
								'scope'       => array( 'type' => 'string' ),
								'metrics'     => array( 'type' => 'object' ),
								'module_id'   => array( 'type' => 'string' ),
								'campaign_id' => array( 'type' => 'string' ),
							),
						),
					),
				),
				'permission_callback' => function () {
					return Merchant_Abilities_Permissions::check( 'merchant/get-analytics' );
				},
				'execute_callback'    => function ( $params = array() ) {
					$handler = new Merchant_Get_Analytics_Handler();
					return $handler->handle( $params );
				},
			),
			'merchant/get-recommendations'    => array(
				'label'               => __( 'Get Recommendations', 'merchant' ),
				'description'         => __( 'Get optimization recommendations based on analytics data.', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
				),
				'input_schema'        => array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'module_id'  => array(
							'type'        => 'string',
							'description' => 'Optional. Limit recommendations to a specific module.',
						),
						'focus'      => array(
							'type'        => 'string',
							'description' => 'Recommendation focus area.',
							'enum'        => array( 'conversion', 'revenue', 'engagement', 'general' ),
							'default'     => 'general',
						),
						'date_range' => array(
							'type'        => 'object',
							'description' => 'Optional date range for recommendations. Defaults to last 30 days.',
							'properties'  => array(
								'start' => array(
									'type'        => 'string',
									'description' => 'Start date (Y-m-d or m/d/y).',
								),
								'end'   => array(
									'type'        => 'string',
									'description' => 'End date (Y-m-d or m/d/y).',
								),
							),
						),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'data'    => array(
							'type'       => 'object',
							'properties' => array(
								'recommendations'       => array(
									'type'  => 'array',
									'items' => array(
										'type'       => 'object',
										'properties' => array(
											'flag'      => array( 'type' => 'string' ),
											'severity'  => array( 'type' => 'string' ),
											'module_id' => array( 'type' => 'string' ),
											'context'   => array( 'type' => 'string' ),
											'metrics'   => array( 'type' => 'object' ),
										),
									),
								),
								'total_recommendations' => array( 'type' => 'integer' ),
							),
						),
					),
				),
				'permission_callback' => function () {
					return Merchant_Abilities_Permissions::check( 'merchant/get-recommendations' );
				},
				'execute_callback'    => function ( $params = array() ) use ( $registry ) {
					$handler = new Merchant_Get_Recommendations_Handler( $registry );
					return $handler->handle( $params );
				},
			),
		);
	}
}
