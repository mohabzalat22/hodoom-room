<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Usage Tracker functionality to understand what's going on client's sites.
 */
class Botiga_Usage_Tracking {

	/**
	 * The slug that will be used to save the option of Usage Tracker.
	 */
	const SETTINGS_SLUG = 'botiga-usage-tracking-enabled';

	/**
	 * Initialize the usage tracking system.
	 */
	public static function init_system() {

		/**
		 * Filter whether the Usage Tracking code is allowed to be loaded.
		 *
		 * @param bool $allowed Whether usage tracking is allowed.
		 */
		if ( ! apply_filters( 'botiga_usage_tracking_is_allowed', true ) ) { // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingSinceComment
			return;
		}

		$usage_tracking = new self();
		$usage_tracking->init();

		$send_task = new Botiga_Send_Usage_Task();
		$send_task->init();
	}

	/**
	 * Attach hooks to the WordPress API.
	 */
	public function init() {

		// Hook to cancel task when option is disabled (only for lite version).
		if ( ! defined( 'BOTIGA_PRO_VERSION' ) ) {
			add_action( 'update_option_' . self::SETTINGS_SLUG, array( $this, 'maybe_cancel_task_on_option_change' ), 10, 2 );
		}

		// Schedule the task if enabled.
		if ( self::is_enabled() ) {
			$this->schedule_task();
		}
	}

	/**
	 * Whether Usage Tracking is enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {

		/**
		 * Filter whether the Usage Tracking is enabled.
		 *
		 * @param bool $enabled Whether usage tracking is enabled.
		 */
		return (bool) apply_filters( // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingSinceComment
			'botiga_usage_tracking_is_enabled',
			get_option( self::SETTINGS_SLUG, false )
		);
	}

	/**
	 * Schedule the usage tracking task.
	 */
	public function schedule_task() {

		if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
			return;
		}

		// Check if already scheduled.
		if ( as_next_scheduled_action( 'botiga_send_usage_data', array(), 'botiga' ) ) {
			return;
		}

		// Schedule to run weekly.
		as_schedule_recurring_action( time(), WEEK_IN_SECONDS, 'botiga_send_usage_data', array(), 'botiga', true );
	}

	/**
	 * Cancel the usage tracking task if disabled.
	 */
	public function cancel_task() {

		if ( self::is_enabled() || ! function_exists( 'as_unschedule_action' ) ) {
			return;
		}

		as_unschedule_action( 'botiga_send_usage_data', array(), 'botiga' );
	}

	/**
	 * Maybe cancel task when option changes.
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $value     The new option value.
	 */
	public function maybe_cancel_task_on_option_change( $old_value, $value ) {

		// If the option is being disabled (changed to 'no', false, or empty).
		if ( $value === 'no' || $value === false || empty( $value ) ) {
			$this->cancel_task();
		}
	}

	/**
	 * Get the User Agent string that will be sent to the API.
	 *
	 * @return string
	 */
	public function get_user_agent() {

		$version = BOTIGA_VERSION;

		if ( defined( 'BOTIGA_PRO_VERSION' ) ) {
			$version .= ' Pro/' . BOTIGA_PRO_VERSION;
		}

		return 'aThemes Botiga/' . $version . '; ' . get_bloginfo( 'url' );
	}

	/**
	 * Get data for sending to the server.
	 *
	 * @return array
	 */
	public function get_data() {

		global $wpdb;

		$theme_data = wp_get_theme();
		$is_pro     = defined( 'BOTIGA_PRO_VERSION' );

		$data = array(
			// Generic data (environment) - keys without prefix.
			'url'                    => home_url(),
			'php_version'            => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
			'wp_version'             => get_bloginfo( 'version' ),
			'mysql_version'          => $wpdb->db_version(),
			'server_version'         => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
			'is_ssl'                 => (int) is_ssl(),
			'is_multisite'           => (int) is_multisite(),
			'is_wpcom'               => (int) ( defined( 'IS_WPCOM' ) && IS_WPCOM ),
			'is_wpcom_vip'           => (int) ( ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV ) || ( function_exists( 'wpcom_is_vip' ) && wpcom_is_vip() ) ),
			'is_wp_cache'            => (int) ( defined( 'WP_CACHE' ) && WP_CACHE ),
			'is_wp_rest_api_enabled' => (int) $this->is_rest_api_enabled(),
			'is_user_logged_in'      => (int) is_user_logged_in(),
			'sites_count'            => $this->get_sites_total(),
			'active_plugins'         => $this->get_active_plugins(),
			'theme_name'             => $theme_data->get( 'Name' ),
			'theme_version'          => $theme_data->get( 'Version' ),
			'locale'                 => get_locale(),
			'timezone_offset'        => wp_timezone_string(),
			// Botiga-specific data - keys with athemes_botiga_ prefix.
			'athemes_botiga_version'             => BOTIGA_VERSION,
			'athemes_botiga_pro_version'         => $is_pro ? BOTIGA_PRO_VERSION : '',
			'athemes_botiga_license_key'         => $this->get_license_key(),
			'athemes_botiga_license_type'        => $this->get_license_type(),
			'athemes_botiga_is_pro'              => (int) $is_pro,
			'athemes_botiga_lite_installed_date' => $this->get_installed_date( 'lite' ),
			'athemes_botiga_pro_installed_date'  => $is_pro ? $this->get_installed_date( 'pro' ) : '',
			'athemes_botiga_active_modules'      => $this->get_active_modules(),
			'athemes_botiga_settings'            => $this->get_settings(),
		);

		if ( $data['is_multisite'] ) {
			$data['url_primary'] = network_site_url();
		}

		if ( ! $is_pro ) {
			unset( $data['athemes_botiga_pro_version'] );
		}

		/**
		 * Filter the usage tracking data.
		 *
		 * @param array $data Usage tracking data.
		 */
		return apply_filters( 'botiga_usage_tracking_data', $data ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingSinceComment
	}

	/**
	 * Get the installed date for Botiga or Botiga Pro.
	 *
	 * @param string $type Either 'lite' or 'pro'.
	 *
	 * @return int Unix timestamp of installation date.
	 */
	private function get_installed_date( $type = 'lite' ) {

		$option_name = 'botiga_installed_date';
		
		if ( $type === 'pro' ) {
			$option_name = 'botiga_pro_installed_date';
		}

		$installed_date = get_option( $option_name, 0 );

		// If not set, set it now.
		if ( empty( $installed_date ) ) {
			$installed_date = time();
			update_option( $option_name, $installed_date );
		}

		return (int) $installed_date;
	}

	/**
	 * Get the license key.
	 *
	 * @return string
	 */
	private function get_license_key() {

		$license_key = trim( get_option( 'botiga_pro_license_key', '' ) );

		if ( empty( $license_key ) ) {
			return '';
		}

		return sanitize_text_field( $license_key );
	}

	/**
	 * Get the license type.
	 *
	 * @return string
	 */
	private function get_license_type() {

		if ( ! defined( 'BOTIGA_PRO_VERSION' ) ) {
			return 'lite';
		}

		// Get the license item name from option (for future plan names like "agency", "lifetime", etc.)
		$license_item_name = get_option( 'botiga_pro_license_item_name', '' );

		// If option is empty or default, return 'pro', otherwise return the plan name
		if ( empty( $license_item_name ) || $license_item_name === 'Botiga Pro' ) {
			return 'pro';
		}

		return sanitize_text_field( strtolower( $license_item_name ) );
	}

	/**
	 * Get all active modules.
	 *
	 * @return array
	 */
	private function get_active_modules() {

		if ( ! function_exists( 'botiga_get_modules_ids' ) ) {
			return array();
		}

		$all_module_ids = botiga_get_modules_ids();
		$active_modules = array();

		foreach ( $all_module_ids as $module_id ) {
			if ( Botiga_Modules::is_module_active( $module_id ) ) {
				$active_modules[] = $module_id;
			}
		}

		return $active_modules;
	}

	/**
	 * Get all settings, except those with sensitive data.
	 *
	 * @return array
	 */
	private function get_settings() {

		// Get all theme mods (customizer settings).
		$theme_mods = get_theme_mods();

		if ( empty( $theme_mods ) || ! is_array( $theme_mods ) ) {
			return array();
		}

		return $theme_mods;
	}

	/**
	 * Get the list of active plugins.
	 *
	 * @return array
	 */
	private function get_active_plugins() {

		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$active = is_multisite() ?
			array_merge( get_option( 'active_plugins', array() ), array_flip( get_site_option( 'active_sitewide_plugins', array() ) ) ) :
			get_option( 'active_plugins', array() );

		$plugins = array_intersect_key( get_plugins(), array_flip( $active ) );

		return array_map(
			static function ( $plugin ) {
				if ( isset( $plugin['Version'] ) ) {
					return $plugin['Version'];
				}

				return 'Not Set';
			},
			$plugins
		);
	}

	/**
	 * Test if the REST API is accessible.
	 *
	 * The REST API might be inaccessible due to various security measures,
	 * or it might be completely disabled by a plugin.
	 *
	 * @return bool
	 */
	private function is_rest_api_enabled() {

		$url      = rest_url( 'wp/v2/types/post' );
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'cookies'   => is_user_logged_in() ? wp_unslash( $_COOKIE ) : array(),
				'headers'   => array(
					'Cache-Control' => 'no-cache',
					'X-WP-Nonce'    => wp_create_nonce( 'wp_rest' ),
				),
			)
		);

		// When testing the REST API, an error was encountered, leave early.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// When testing the REST API, an unexpected result was returned, leave early.
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return false;
		}

		// The REST API did not behave correctly, leave early.
		$body = wp_remote_retrieve_body( $response );

		if ( ! $this->is_json( $body ) ) {
			return false;
		}

		// We are all set. Confirm the connection.
		return true;
	}

	/**
	 * Check if a string is valid JSON.
	 *
	 * @param string $value The string to check.
	 *
	 * @return bool
	 */
	private function is_json( $value ) {

		if ( ! is_string( $value ) ) {
			return false;
		}

		json_decode( $value, true );

		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Total number of sites.
	 *
	 * @return int
	 */
	private function get_sites_total() {
		return function_exists( 'get_blog_count' ) ? (int) get_blog_count() : 1;
	}
}

// Initialize usage tracking.
add_action( 'init', array( 'Botiga_Usage_Tracking', 'init_system' ) );

