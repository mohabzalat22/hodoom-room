<?php
/**
 * Merchant Abilities Permissions.
 *
 * Permission callback functions for WP Abilities API.
 * Enforces two layers: MCP write access guard + WP capability checks.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Abilities_Permissions
 *
 * Provides permission callbacks for each Merchant ability.
 * Write abilities check the `wpvibe_mcp_write_access` toggle
 * BEFORE checking WP capabilities. Read abilities bypass
 * the write access guard entirely.
 *
 * Uses Merchant_Option::get() (no filter) for the permission
 * check — this is a conscious choice to avoid third-party
 * code overriding the security toggle via the
 * `merchant_get_option` filter.
 *
 * @since 2.3.0
 */
class Merchant_Abilities_Permissions {

	/**
	 * Abilities that require write access to be enabled.
	 *
	 * @var array<int, string>
	 */
	private static $write_abilities = array(
		'merchant/toggle-module',
		'merchant/update-module-settings',
		'merchant/create-campaign',
		'merchant/update-campaign',
		'merchant/delete-campaign',
	);

	/**
	 * Register an ability id as requiring write access.
	 *
	 * Push-only: appends the id if it isn't already tracked. There is
	 * no counterpart to remove an id — shrinking the write gate is not
	 * exposed, since the contribution hook that calls this can re-fire
	 * on every request and must stay idempotent.
	 *
	 * @param string $id The ability identifier (e.g. 'merchant/create-bundle').
	 *
	 * @return void
	 */
	public static function register_write_ability( $id ) {
		if ( ! in_array( $id, self::$write_abilities, true ) ) {
			self::$write_abilities[] = $id;
		}
	}

	/**
	 * Get the abilities currently gated by write access.
	 *
	 * @return array<int, string>
	 */
	public static function get_write_abilities() {
		return self::$write_abilities;
	}

	/**
	 * Check if MCP write access is enabled.
	 *
	 * Reads from Merchant_Option::get() (no filter) to avoid
	 * third-party code overriding this security check.
	 *
	 * @return bool
	 */
	public static function is_write_access_enabled() {
		return (bool) Merchant_Option::get( 'global-settings', 'wpvibe_mcp_write_access', false );
	}

	/**
	 * Permission callback for a given ability.
	 *
	 * @param string $ability_id The ability identifier (e.g. 'merchant/toggle-module').
	 *
	 * @return bool|WP_Error True if allowed, WP_Error if denied.
	 */
	public static function check( $ability_id ) {
		// Step 1: Write access guard (before capability check).
		if ( in_array( $ability_id, self::$write_abilities, true ) && ! self::is_write_access_enabled() ) {
			return new WP_Error(
				'write_access_disabled',
				__( 'MCP write access is disabled. Enable it in Merchant → Global Settings → WPVibe banner.', 'merchant' ),
				array( 'status' => 403 )
			);
		}

		// Step 2: WordPress capability check.
		$capability = self::get_required_capability( $ability_id );

		if ( ! current_user_can( $capability ) ) {
			return new WP_Error(
				'ability_forbidden',
				__( 'You do not have permission to perform this action.', 'merchant' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Get the required WordPress capability for an ability.
	 *
	 * toggle-module requires manage_options (system-level).
	 * All other abilities require manage_woocommerce (shop manager scope).
	 *
	 * @param string $ability_id The ability identifier.
	 *
	 * @return string The WordPress capability slug.
	 */
	private static function get_required_capability( $ability_id ) {
		if ( 'merchant/toggle-module' === $ability_id ) {
			return 'manage_options';
		}

		return 'manage_woocommerce';
	}
}
