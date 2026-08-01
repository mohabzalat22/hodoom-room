<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Merchant_Add_Module {

	/**
	 * WooCommerce only.
	 *
	 */
	public $wc_only = false;

	/**
	 * Module section.
	 *
	 */
	public $module_section = '';

	/**
	 * Module id.
	 *
	 */
	public $module_id = '';

	/**
	 * Module default settings.
	 *
	 */
	public $module_default_settings = array();

	/**
	 * Module data.
	 *
	 */
	public $module_data = array();

	/**
	 * AI prompt examples shown in the "Ask AI" popup for this module.
	 *
	 * Subclasses set this in their constructor with a 'blurb' string
	 * and a 'prompts' array of example prompts.
	 *
	 * @var array
	 */
	protected $ai_examples = array();

	/**
	 * Module options.
	 *
	 */
	public $module_options_path = '';

	/**
	 * Whether the module has a shortcode or not.
	 *
	 * @var bool
	 */
	public $has_shortcode = false;

	protected $has_analytics = false;

	/**
	 * Option group definitions cache.
	 *
	 * @var array<int, array<string, mixed>>|null
	 */
	protected $option_groups = null;

	/**
	 * Initialize the module's option group definitions.
	 *
	 * Subclasses override this to define their settings panels.
	 * Each element in the returned array corresponds to one settings
	 * section in the admin UI.
	 *
	 * @return array<int, array<string, mixed>> Array of option group arrays, each with 'module', 'title', and 'fields' keys.
	 */
	protected function init_option_groups() {
		return array();
	}

	/**
	 * Get the module's option group definitions.
	 *
	 * Returns cached definitions if available. On first call,
	 * initializes from the subclass and applies the filter for
	 * third-party extensibility.
	 *
	 * @return array<int, array<string, mixed>> Filtered option group definitions.
	 */
	public function get_option_groups() {
		if ( $this->option_groups !== null ) {
			return $this->option_groups;
		}

		$groups = $this->init_option_groups();

		/**
		 * Filter a module's option group definitions.
		 *
		 * Allows third-party plugins to add, remove, or modify
		 * the field definitions for any module.
		 *
		 * @param array<int, array<string, mixed>> $groups Array of option group arrays.
		 *
		 * @since 2.3.0
		 */
		$this->option_groups = apply_filters(
			"merchant_{$this->module_id}_option_groups",
			$groups
		);

		return $this->option_groups;
	}


	/**
	 * Constructor.
	 *
	 */
	public function __construct() {
		// Add and expose the module into the plugin dashboard.
		add_filter( 'merchant_modules', array( $this, 'add_module' ) );

		// Add module options.
		add_filter( 'merchant_module_file_path', array( $this, 'add_module_options' ), 10, 2 );

		// Add class to body to identify if module is active or not.
		add_filter( 'admin_body_class', array( $this, 'add_module_activation_status_class' ), 10, 2 );

		// Handle modules list item class.
		add_filter( "merchant_admin_module_{$this->module_id}_list_item_class", array( $this, 'modules_list_item_class' ) );

		if ( $this->has_shortcode ) {
			add_action( 'wp', array( $this, 'setup_product_object' ) );
			add_shortcode( 'merchant_module_' . str_replace( '-', '_', $this->module_id ), array( $this, 'shortcode_handler' ) );
		}

		// Remove merchant shortcodes from Botiga product card short description
		add_filter( 'botiga_loop_product_elements', function( $elements ) {
			add_filter( 'get_the_excerpt', function( $excerpt ) {
				return preg_replace( '/\[merchant_[^]]+]/', '', $excerpt );
			} );

			return $elements;
		} );
	}

	/**
	 * Check if the module has analytics.
	 *
	 * @return bool
	 */
	public function has_analytics() {
		return $this->has_analytics;
	}

	/**
	 * Get the module's AI prompt examples.
	 *
	 * @return array Array with 'blurb' and 'prompts' keys, or an empty array if the module has none.
	 */
	public function get_ai_examples() {
		/**
		 * Filter a module's AI prompt examples.
		 *
		 * @param array $ai_examples Array with 'blurb' and 'prompts' keys.
		 *
		 * @since 2.3.0
		 */
		return apply_filters( "merchant_{$this->module_id}_ai_examples", $this->ai_examples );
	}

	/**
	 * Get all analytics metrics and allow modules to filter them.
	 *
	 * @return array List of available metrics.
	 */
	public function analytics_metrics() {
		/**
		 * Hook: merchant_analytics_module_metrics
		 *
		 * @param array  $metrics   List of available metrics.
		 * @param string $module_id Module ID.
		 *
		 * @since 2.0
		 */
		return apply_filters( 'merchant_analytics_module_metrics', $this->default_analytics_metrics(), $this->module_id, $this );
	}

	/**
	 * Get analytics metrics.
	 *
	 * @return array List of available metrics.
	 */
	protected function default_analytics_metrics() {
		return array(
			'campaigns'    => true,
			'impressions'  => true,
			'clicks'       => true,
			'ctr'          => true,
			'revenue'      => true,
			'orders_count' => true,
			'aov'          => true,
		);
	}

	/**
	 * Active modules class handler.
	 *
	 */
	public function add_module_activation_status_class( $classes ) {
		if ( ! $this->is_module_settings_page() ) {
			return $classes;
		}

		if ( Merchant_Modules::is_module_active( $this->module_id ) ) {
			$classes = $classes . ' merchant-module-enabled';
		} else {
			$classes = $classes . ' merchant-module-disabled';
		}

		return $classes;
	}

	/**
	 * Modules list item class.
	 *
	 * @param string $module_class
	 *
	 * @return string
	 */
	public function modules_list_item_class( $module_class ) {
		if ( $this->wc_only && ! class_exists( 'Woocommerce' ) ) {
			$module_class = $module_class . ' merchant-module-wc-only';
		}

		return $module_class;
	}

	/**
	 * Is module settings page.
	 *
	 * @return bool
	 */
	public function is_module_settings_page() {
		return isset( $_GET['page'] ) && 'merchant' === $_GET['page'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				&& isset( $_GET['module'] ) && $this->module_id === $_GET['module']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Get module settings.
	 *
	 */
	public function get_module_settings() {
		$settings = get_option( 'merchant', array() );

		// Default settings.
		$defaults = $this->module_default_settings;

		if ( empty( $settings[ $this->module_id ] ) ) {
			$settings[ $this->module_id ] = $defaults;
		}

		// Parse settings with defaults.
		// Todo: check if recursive_parse_args() works for all modules and remove the condition.
		$settings = $this->module_id === 'product-labels' ? $this->recursive_parse_args( $settings[ $this->module_id ], $defaults ) : wp_parse_args( $settings[ $this->module_id ], $defaults );

		/**
		 * Hook: merchant_module_settings
		 *
		 * @param array  $settings  Module settings.
		 * @param string $module_id Module ID.
		 *
		 * @since 1.9.16
		 */
		$settings = apply_filters( 'merchant_module_settings', $settings, $this->module_id );

		return $settings;
	}

	/**
	 * Update module settings.
	 *
	 * @param array $module_settings
	 *
	 * @return void
	 */
	public function update_module_settings( $module_settings ) {
		$settings = get_option( 'merchant', array() );

		$settings[ $this->module_id ] = $module_settings;

		/**
		 * Hook: merchant_module_settings_update
		 *
		 * @param array  $settings  Module settings.
		 * @param string $module_id Module ID.
		 *
		 * @since 2.0.0
		 */
		$settings = apply_filters( 'merchant_module_settings_update', $settings, $this->module_id );

		update_option( 'merchant', $settings );
	}

	/**
	 * Add module.
	 *
	 */
	public function add_module( $modules ) {
		$modules[ $this->module_section ]['modules'][ $this->module_id ] = $this->module_data;

		return $modules;
	}

	/**
	 * Add module options.
	 *
	 * For migrated modules (those with option groups defined),
	 * renders settings directly from get_option_groups().
	 * Unmigrated modules fall back to including $module_options_path.
	 *
	 * @param string $module_path    Default file path.
	 * @param string $merchant_module Module ID being rendered.
	 *
	 * @return string File path to include, or empty string if already rendered.
	 */
	public function add_module_options( $module_path, $merchant_module ) {
		if ( $this->module_id !== $merchant_module ) {
			return $module_path;
		}

		$groups = $this->get_option_groups();

		if ( ! empty( $groups ) ) {
			$this->before_render_option_groups();

			foreach ( $groups as $group ) {
				Merchant_Admin_Options::create( $group );
			}

			$this->after_render_option_groups();

			return '';
		}

		return $this->module_options_path;
	}

	/**
	 * Called before option groups are rendered in the admin.
	 *
	 * Override in subclasses that need to fire actions
	 * before settings fields (e.g., help banners).
	 *
	 * @return void
	 */
	protected function before_render_option_groups() {}

	/**
	 * Called after option groups are rendered in the admin.
	 *
	 * Override in subclasses that need to fire actions
	 * after settings fields.
	 *
	 * @return void
	 */
	protected function after_render_option_groups() {}

	/**
	 * Display error message if the shortcode is placed in the wrong place.
	 *
	 * @return mixed|null
	 */
	public function shortcode_placement_error() {
		/*
		 * translators: %s: module id
		 */
		$message = __( 'The shortcode <strong>[merchant_module_%s]</strong> can only be used on single product pages.', 'merchant' );
		$message = sprintf( $message, str_replace( '-', '_', $this->module_id ) );
		$message = wp_kses( $message, array(
			'strong' => array(),
		) );

		/**
		 * Filter the shortcode error message html content.
		 *
		 * @param string $message_content
		 * @param string $module_id
		 *
		 * @since 1.8
		 */
		return apply_filters( 'merchant_module_shortcode_error_message_html',
			'<div class="merchant-shortcode-wrong-placement">' .
			$message
			. '</div>',
			$this->module_id );
	}

	/**
	 * Check if shortcode is enabled.
	 *
	 * @return bool
	 */
	public function is_shortcode_enabled() {

		/**
		 * Hook 'merchant_{$this->module_id}_is_shortcode_enabled'
		 * 
		 * @since 1.9.3
		 */
		return apply_filters( "merchant_{$this->module_id}_is_shortcode_enabled", Merchant_Admin_Options::get( $this->module_id, 'use_shortcode', false ) );
	}

	/**
	 * Recursively merges user-defined arguments into default arguments.
	 *
	 * @param $args
	 * @param $defaults
	 *
	 * @return mixed
	 */
	private function recursive_parse_args( $args, $defaults ) {
		$result = $defaults;

		foreach ( $args as $key => $value ) {
			// If the value is an array and the corresponding default is also an array, merge them recursively.
			if ( is_array( $value ) && isset( $result[ $key ] ) && is_array( $result[ $key ] ) ) {
				$result[ $key ] = $this->recursive_parse_args( $value, $result[ $key ] );
			} else {
				$result[ $key ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Ensure $product is an object in the Breakdance builder editor.
	 * If $product is a string, convert it to a WooCommerce product object.
	 *
	 * @return void
	 */
	public function setup_product_object() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( ! is_product() ) {
			return;
		}

		global $product;

		// Check if $product is a string and not already an object
		if ( ! is_object( $product ) && is_string( $product ) ) {
			// Retrieve the product object by slug
			$product_object = wc_get_product( get_page_by_path( $product, OBJECT, 'product' ) );

			// Update global $product with the retrieved product object if found
			if ( $product_object ) {
				$product = $product_object;
			}
		}
	}
}
