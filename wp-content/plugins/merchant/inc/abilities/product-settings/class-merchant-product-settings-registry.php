<?php
/**
 * Product Settings Registry.
 *
 * Maps each product-settings-pair module slug to its metabox class (and file),
 * and derives the ability field specs from that class's get_field_definitions()
 * — the single source of truth replacing the deleted hand-authored descriptors.
 * Pro appends its modules via the merchant_product_settings_metabox_classes filter.
 *
 * @package Merchant
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Product_Settings_Registry
 *
 * @since 2.3.0
 */
class Merchant_Product_Settings_Registry {

	/**
	 * The slug => { class, file } map, after the Pro filter has appended.
	 *
	 * Values carry only class-name and file-path strings — no field data, no
	 * class loading — so this is safe to call at any time (incl. the early
	 * ability-definition enum build on wp_abilities_api_init:5).
	 *
	 * @return array<string, array{class: string, file: string}>
	 */
	private static function map() {
		$map = array(
			'pre-orders'       => array(
				'class' => 'Merchant_Pre_Orders_Metabox',
				'file'  => MERCHANT_DIR . 'inc/modules/pre-orders/admin/class-pre-orders-metabox.php',
			),
			'add-to-cart-text' => array(
				'class' => 'Merchant_Add_To_Cart_Text_Metabox',
				'file'  => MERCHANT_DIR . 'inc/modules/add-to-cart-text/admin/class-add-to-cart-text-metabox.php',
			),
		);

		/**
		 * Filter the product-settings metabox-class map.
		 *
		 * Lets Merchant Pro register its own product-settings metabox classes
		 * (product-audio, product-video, product-brand-image). Callbacks MUST
		 * add only class-name and absolute file-path strings — never translated
		 * strings or field data — so this filter stays safe to apply before init.
		 *
		 * @param array<string, array{class: string, file: string}> $map Slug => { class, file }.
		 *
		 * @since 2.3.0
		 */
		return apply_filters( 'merchant_product_settings_metabox_classes', $map );
	}

	/**
	 * List the module slugs the product-settings pair serves.
	 *
	 * @return array<int, string>
	 */
	public static function get_modules() {
		return array_keys( self::map() );
	}

	/**
	 * Resolve a module slug to its metabox class name, lazily loading the
	 * class file (and the base metabox class it extends) when needed.
	 *
	 * @param string $module The module slug.
	 *
	 * @return string|null The class name, or null when unregistered/unloadable.
	 */
	public static function resolve( $module ) {
		$map = self::map();

		if ( ! isset( $map[ $module ]['class'] ) ) {
			return null;
		}

		$class = $map[ $module ]['class'];
		$file  = $map[ $module ]['file'];

		if ( ! class_exists( $class ) ) {
			if ( ! class_exists( 'Merchant_Metabox' ) ) {
				require_once MERCHANT_DIR . 'inc/classes/class-merchant-metabox.php';
			}
			if ( '' !== $file && file_exists( $file ) ) {
				require_once $file;
			}
		}

		return class_exists( $class ) ? $class : null;
	}

	/**
	 * Build the ability field specs for a module: every ai_meta-bearing field
	 * from its metabox, transformed into the validator/schema-ready shape.
	 *
	 * @param string $module The module slug.
	 *
	 * @return array<string, array<string, mixed>> meta_key => ability spec.
	 */
	public static function get_ability_fields( $module ) {
		$class = self::resolve( $module );

		if ( null === $class ) {
			return array();
		}

		$fields = array();
		foreach ( $class::get_field_definitions() as $meta_key => $def ) {
			if ( isset( $def['ai_meta'] ) ) {
				$fields[ $meta_key ] = self::to_ability_spec( $def );
			}
		}

		return $fields;
	}

	/**
	 * Promote a metabox field type to its ability (validator) type.
	 *
	 * @param string               $type Metabox type (or ai_meta.type_override).
	 * @param array<string, mixed> $def  The full metabox field definition.
	 *
	 * @return string
	 */
	public static function map_metabox_type( $type, array $def ) {
		if ( 'select' === $type && ! empty( $def['multiple'] ) ) {
			return 'multiselect';
		}

		switch ( $type ) {
			case 'select-ajax':
				return 'user_list';
			case 'repeater':
				return 'url_list';
			default:
				return $type;
		}
	}

	/**
	 * Transform a metabox field definition into the descriptor-shaped ability
	 * spec the validator and the get-handler schema builder consume.
	 *
	 * @param array<string, mixed> $def The metabox field definition (must carry ai_meta).
	 *
	 * @return array<string, mixed>
	 */
	public static function to_ability_spec( array $def ) {
		$ai   = ( isset( $def['ai_meta'] ) && is_array( $def['ai_meta'] ) ) ? $def['ai_meta'] : array();
		$base = isset( $ai['type_override'] ) ? $ai['type_override'] : ( isset( $def['type'] ) ? $def['type'] : 'text' );

		$spec = array( 'type' => self::map_metabox_type( $base, $def ) );

		if ( isset( $ai['options_callback'] ) ) {
			$spec['options_callback'] = $ai['options_callback'];
		} elseif ( isset( $def['options'] ) ) {
			$spec['options'] = $def['options'];
		}

		if ( isset( $def['min'] ) ) {
			$spec['min'] = $def['min'];
		}
		if ( isset( $def['max'] ) ) {
			$spec['max'] = $def['max'];
		}
		if ( isset( $ai['kind'] ) ) {
			$spec['kind'] = $ai['kind'];
		}
		if ( ! empty( $ai['thumb'] ) ) {
			$spec['thumb'] = true;
		}

		$spec['ai_meta'] = $ai;

		return $spec;
	}
}
