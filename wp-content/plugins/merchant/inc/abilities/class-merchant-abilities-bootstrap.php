<?php
/**
 * Merchant Abilities Bootstrap.
 *
 * One-time initialization for the WP Abilities API integration.
 * Requires all abilities classes, creates the registry,
 * registers the category, and fires the extension hook.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Abilities_Bootstrap
 *
 * Entry point for the Merchant Abilities layer.
 * Called from merchant.php behind a `function_exists('wp_register_ability')` guard.
 *
 * @since 2.3.0
 */
class Merchant_Abilities_Bootstrap {

	/**
	 * Whether init() has been called.
	 *
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * The abilities registry, held until wp_abilities_api_init fires.
	 *
	 * @var Merchant_Abilities_Registry|null
	 */
	private static $registry = null;

	/**
	 * Initialize the abilities layer.
	 *
	 * Idempotent — safe to call multiple times.
	 *
	 * @return void
	 */
	public static function init() {
		if ( self::$initialized ) {
			return;
		}

		self::$initialized = true;
		self::require_files();

		// Register the "merchant" ability category.
		Merchant_Abilities_Category::register();

		// Build core services with dependency injection.
		$schema_generator = new Merchant_Schema_Generator();

		// Validator resolves the field registry lazily: its field classes load on `init`, after this plugins_loaded bootstrap.
		$field_validator   = new Merchant_Field_Validator( $schema_generator );
		$campaign_resolver = new Merchant_Campaign_Resolver();

		// Build the registry (no wp_register_ability calls yet).
		$registry = new Merchant_Abilities_Registry(
			$schema_generator,
			$field_validator,
			$campaign_resolver
		);

		// Wire the product-settings pair's contribution hook (init() only adds
		// the action below — no strings, no def building at this point).
		Merchant_Product_Settings_Abilities::init();

		/**
		 * Allows Merchant Pro and third-party plugins to extend the abilities
		 * layer in two ways:
		 *
		 * 1. Register custom MODULE ADAPTERS (campaign summary formatters)
		 *    via $registry->register_adapter(). Pro does not extend field
		 *    definitions via any filter — all 'pro' => true fields already
		 *    live in core.
		 * 2. Contribute additional ability definitions via
		 *    $registry->register_ability_definition() — the seam
		 *    Merchant_Product_Settings_Abilities and Merchant Pro's
		 *    Merchant_Pro_Abilities_Registrar both use, deferring their
		 *    definition builds to wp_abilities_api_init so no __() call
		 *    ever loads a textdomain before `init`.
		 *
		 * @param Merchant_Abilities_Registry $registry The abilities registry.
		 *
		 * @since 2.3.0
		 */
		do_action( 'merchant_abilities_register_modules', $registry );

		// Abilities must be registered on the wp_abilities_api_init action.
		self::$registry = $registry;
		add_action( 'wp_abilities_api_init', array( __CLASS__, 'register_abilities' ) );
	}

	/**
	 * Register all abilities with WordPress.
	 *
	 * Fires on wp_abilities_api_init as required by the WP Abilities API.
	 *
	 * @return void
	 */
	public static function register_abilities() {
		if ( self::$registry ) {
			self::$registry->register_all_abilities();
		}
	}

	/**
	 * Require all abilities layer files.
	 *
	 * @return void
	 */
	private static function require_files() {
		$dir = MERCHANT_DIR . 'inc/abilities/';

		// Core infrastructure.
		require_once $dir . 'class-merchant-abilities-category.php';
		require_once $dir . 'class-merchant-abilities-registry.php';
		require_once $dir . 'class-merchant-abilities-permissions.php';
		require_once $dir . 'class-merchant-schema-generator.php';
		require_once $dir . 'class-merchant-field-validator.php';
		require_once $dir . 'class-merchant-campaign-resolver.php';

		// Module adapters.
		require_once $dir . 'module-adapters/interface-merchant-module-abilities.php';
		require_once $dir . 'module-adapters/class-merchant-default-module-adapter.php';

		// Handlers.
		$handlers_dir = $dir . 'handlers/';
		// Handler base class (must be loaded before concrete handlers).
		require_once $handlers_dir . 'class-merchant-abstract-handler.php';
		require_once $handlers_dir . 'class-merchant-list-modules-handler.php';
		require_once $handlers_dir . 'class-merchant-toggle-module-handler.php';
		require_once $handlers_dir . 'class-merchant-get-settings-handler.php';
		require_once $handlers_dir . 'class-merchant-update-settings-handler.php';
		require_once $handlers_dir . 'class-merchant-list-campaigns-handler.php';
		require_once $handlers_dir . 'class-merchant-create-campaign-handler.php';
		require_once $handlers_dir . 'class-merchant-update-campaign-handler.php';
		require_once $handlers_dir . 'class-merchant-delete-campaign-handler.php';
		require_once $handlers_dir . 'class-merchant-get-analytics-handler.php';
		require_once $handlers_dir . 'class-merchant-get-recommendations-handler.php';

		// Recommendations engine.
		require_once $dir . 'class-merchant-recommendations-engine.php';

		// Pro extends the module set via the merchant_product_settings_metabox_classes filter.
		$product_settings_dir = $dir . 'product-settings/';
		require_once $product_settings_dir . 'class-merchant-product-settings-validator.php';
		require_once $product_settings_dir . 'class-merchant-product-settings-registry.php';
		require_once $handlers_dir . 'class-merchant-get-product-settings-handler.php';
		require_once $handlers_dir . 'class-merchant-update-product-settings-handler.php';
		require_once $product_settings_dir . 'class-merchant-product-settings-abilities.php';
	}
}
