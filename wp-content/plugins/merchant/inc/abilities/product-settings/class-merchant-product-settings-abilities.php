<?php
/**
 * Product Settings Abilities Contribution.
 *
 * Mirrors Merchant_Pro_Abilities_Registrar's split/deferral mechanics
 * locally, free-side: it can't consume the Pro registrar itself (pre-orders
 * and add-to-cart-text must register on free-only sites, and the registrar
 * plus its MERCHANT_PRO_VERSION guard live in merchant-pro), so it carries
 * the same init()/contribute()/register_contributions() split and defers
 * building the ability definitions to wp_abilities_api_init:5.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Product_Settings_Abilities
 *
 * Provides the get/update-product-settings ability definitions, contributed
 * onto the free abilities registry via register_ability_definition() —
 * never wp_register_ability() directly.
 *
 * @since 2.3.0
 */
class Merchant_Product_Settings_Abilities {

	/**
	 * The free abilities registry, held between contribute() and the
	 * deferred register_contributions() callback.
	 *
	 * @var Merchant_Abilities_Registry|null
	 */
	private static $registry = null;

	/**
	 * Wire the contribution hook onto the free registry's extension point.
	 *
	 * No string literals, no guard, no definition building here — this
	 * runs at plugins_loaded:20, before `init`.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'merchant_abilities_register_modules', array( __CLASS__, 'contribute' ) );
	}

	/**
	 * Contribute onto the free registry.
	 *
	 * Registers the write id now (no translations), then defers building the
	 * ability definitions to wp_abilities_api_init:5 — before the free
	 * registry replays contributions at the default priority 10.
	 *
	 * @param Merchant_Abilities_Registry $registry The free plugin's abilities registry.
	 *
	 * @return void
	 */
	public static function contribute( $registry ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		Merchant_Abilities_Permissions::register_write_ability( 'merchant/update-product-settings' );

		self::$registry = $registry;

		add_action( 'wp_abilities_api_init', array( __CLASS__, 'register_contributions' ), 5 );
	}

	/**
	 * Build every ability definition and push it onto the registry.
	 *
	 * Runs on wp_abilities_api_init, which never fires before `init`, so
	 * get_ability_definitions()'s __() calls load the textdomain at the
	 * right time.
	 *
	 * @return void
	 */
	public static function register_contributions() {
		if ( null === self::$registry ) {
			return;
		}

		foreach ( self::get_ability_definitions() as $id => $def ) {
			self::$registry->register_ability_definition( $id, $def );
		}
	}

	/**
	 * Build the get/update-product-settings ability definition arrays.
	 *
	 * All translatable strings live here — this method only runs lazily on
	 * wp_abilities_api_init (post-init), never at plugins_loaded.
	 *
	 * @return array<lowercase-string&non-falsy-string, array<string, mixed>>
	 */
	public static function get_ability_definitions() {
		$modules = Merchant_Product_Settings_Registry::get_modules();

		return array(
			'merchant/get-product-settings'    => array(
				'label'               => __( 'Get Product Settings', 'merchant' ),
				'description'         => __( 'Read a product\'s settings for a supported per-product module (pre-orders, add-to-cart-text, and — with Merchant Pro — product-audio/video/brand-image).', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
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
						'product_id'     => array(
							'type'        => 'integer',
							'description' => 'The product id.',
						),
						'module'         => array(
							'type' => 'string',
							'enum' => $modules,
						),
						'include_schema' => array(
							'type'        => 'boolean',
							'description' => 'Include field schemas in the response.',
							'default'     => true,
						),
					),
					'required'             => array( 'product_id', 'module' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'data'    => array( 'type' => 'object' ),
					),
				),
				'permission_callback' => self::get_permission_callback(),
				'execute_callback'    => function ( $params = array() ) {
					$handler = new Merchant_Get_Product_Settings_Handler();
					return $handler->handle( $params );
				},
			),
			'merchant/update-product-settings' => array(
				'label'               => __( 'Update Product Settings', 'merchant' ),
				'description'         => __( 'Update a product\'s settings for a supported per-product module, strictly whitelisted against the module\'s descriptor.', 'merchant' ),
				'category'            => 'merchant',
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
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
						'product_id' => array(
							'type'        => 'integer',
							'description' => 'The product id.',
						),
						'module'     => array(
							'type' => 'string',
							'enum' => $modules,
						),
						'updates'    => array(
							'type'          => 'object',
							'description'   => 'Key-value pairs of settings to update.',
							'maxProperties' => 30,
						),
					),
					'required'             => array( 'product_id', 'module', 'updates' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'  => array( 'type' => 'boolean' ),
						'data'     => array( 'type' => 'object' ),
						'warnings' => array( 'type' => 'array' ),
					),
				),
				'permission_callback' => self::update_permission_callback(),
				'execute_callback'    => function ( $params = array() ) {
					$handler = new Merchant_Update_Product_Settings_Handler();
					return $handler->handle( $params );
				},
			),
		);
	}

	/**
	 * Build the get-product-settings permission callback: manage_woocommerce,
	 * plus the object-aware read check when a numeric product_id is present.
	 *
	 * @return callable(array<string, mixed>): (bool|WP_Error)
	 */
	private static function get_permission_callback() {
		return function ( $input ) {
			if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce shop-manager capability.
				return new WP_Error(
					'ability_forbidden',
					__( 'You do not have permission to perform this action.', 'merchant' ),
					array( 'status' => 403 )
				);
			}

			$product_id = self::resolve_product_id( $input );
			if ( null === $product_id ) {
				return true;
			}

			return wc_rest_check_post_permissions( 'product', 'read', $product_id );
		};
	}

	/**
	 * Build the update-product-settings permission callback: the write-access
	 * gate first, then the object-aware edit check.
	 *
	 * @return callable(array<string, mixed>): (bool|WP_Error)
	 */
	private static function update_permission_callback() {
		return function ( $input ) {
			$write_gate = Merchant_Abilities_Permissions::check( 'merchant/update-product-settings' );
			if ( true !== $write_gate ) {
				return $write_gate;
			}

			$product_id = self::resolve_product_id( $input );
			if ( null === $product_id ) {
				return new WP_Error(
					'invalid_id',
					__( 'A valid product id is required.', 'merchant' ),
					array( 'status' => 400 )
				);
			}

			return wc_rest_check_post_permissions( 'product', 'edit', $product_id );
		};
	}

	/**
	 * Resolve the numeric product id out of raw ability input.
	 *
	 * Defensive on purpose: the mcp-adapter can call the permission
	 * callback before validate_input runs, so product_id may still be a
	 * numeric string or missing entirely.
	 *
	 * @param array<string, mixed> $input Raw ability input.
	 *
	 * @return int|null The resolved id, or null when absent/non-numeric.
	 */
	private static function resolve_product_id( $input ) {
		if ( ! isset( $input['product_id'] ) || ! is_numeric( $input['product_id'] ) ) {
			return null;
		}

		return (int) $input['product_id'];
	}

	/**
	 * Reset all static state. Primarily for test isolation.
	 *
	 * @return void
	 */
	public static function reset() {
		self::$registry = null;
	}
}
