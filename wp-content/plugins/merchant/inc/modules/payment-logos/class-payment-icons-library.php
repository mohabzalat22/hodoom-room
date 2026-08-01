<?php
/**
 * Payment Icons Library.
 *
 * Provides a centralized registry of built-in payment method SVG icons.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Payment Icons Library Class.
 *
 * @since 2.2.8
 */
class Merchant_Payment_Icons_Library {

	/**
	 * Base directory path for icon SVG files.
	 *
	 * @var string
	 */
	private static $icons_dir = '';

	/**
	 * Base URI for icon SVG files.
	 *
	 * @var string
	 */
	private static $icons_uri = '';

	/**
	 * Registry of available payment icons.
	 *
	 * Each entry maps a key to its human-readable label and filename.
	 *
	 * @var array<string, array{label: string, file: string}>
	 */
	private static $registry = array(
		'visa'           => array(
			'label' => 'Visa',
			'file'  => 'visa.svg',
		),
		'mastercard'     => array(
			'label' => 'Mastercard',
			'file'  => 'mastercard.svg',
		),
		'amex'           => array(
			'label' => 'American Express',
			'file'  => 'americanexpress.svg',
		),
		'discover'       => array(
			'label' => 'Discover',
			'file'  => 'discover.svg',
		),
		'paypal'         => array(
			'label' => 'PayPal',
			'file'  => 'paypal.svg',
		),
		'stripe'         => array(
			'label' => 'Stripe',
			'file'  => 'stripe.svg',
		),
		'apple-pay'      => array(
			'label' => 'Apple Pay',
			'file'  => 'applepay.svg',
		),
		'google-pay'     => array(
			'label' => 'Google Pay',
			'file'  => 'googlepay.svg',
		),
		'klarna'         => array(
			'label' => 'Klarna',
			'file'  => 'klarna.svg',
		),
		'alipay'         => array(
			'label' => 'Alipay',
			'file'  => 'alipay.svg',
		),
		'bitcoin'        => array(
			'label' => 'Bitcoin',
			'file'  => 'bitcoin.svg',
		),
		'amazon-pay'     => array(
			'label' => 'Amazon Pay',
			'file'  => 'amazonpay.svg',
		),
		'bitpay'         => array(
			'label' => 'BitPay',
			'file'  => 'bitpay.svg',
		),
		'cash-app'       => array(
			'label' => 'Cash App',
			'file'  => 'cashapp.svg',
		),
		'ethereum'       => array(
			'label' => 'Ethereum',
			'file'  => 'ethereum.svg',
		),
		'moneygram'      => array(
			'label' => 'MoneyGram',
			'file'  => 'moneygram.svg',
		),
		'payoneer'       => array(
			'label' => 'Payoneer',
			'file'  => 'payoneer.svg',
		),
		'skrill'         => array(
			'label' => 'Skrill',
			'file'  => 'skrill.svg',
		),
		'square'         => array(
			'label' => 'Square',
			'file'  => 'square.svg',
		),
		'unionpay'       => array(
			'label' => 'UnionPay',
			'file'  => 'unionpay.svg',
		),
		'western-union'  => array(
			'label' => 'Western Union',
			'file'  => 'westernunion.svg',
		),
	);

	/**
	 * Get the base directory path for icon files.
	 *
	 * @since 2.2.8
	 *
	 * @return string
	 */
	private static function get_icons_dir() {
		if ( empty( self::$icons_dir ) ) {
			self::$icons_dir = MERCHANT_DIR . 'inc/modules/payment-logos/admin/images/';
		}

		return self::$icons_dir;
	}

	/**
	 * Get the base URI for icon files.
	 *
	 * @since 2.2.8
	 *
	 * @return string
	 */
	private static function get_icons_uri() {
		if ( empty( self::$icons_uri ) ) {
			self::$icons_uri = MERCHANT_URI . 'inc/modules/payment-logos/admin/images/';
		}

		return self::$icons_uri;
	}

	/**
	 * Get the URL for a specific icon.
	 *
	 * @since 2.2.8
	 *
	 * @param string $key The icon key (e.g. 'visa', 'mastercard').
	 *
	 * @return string The icon URL, or empty string if the key is not found.
	 */
	public static function get_icon_url( $key ) {
		if ( ! isset( self::$registry[ $key ] ) ) {
			return '';
		}

		return self::get_icons_uri() . self::$registry[ $key ]['file'];
	}

	/**
	 * Get the filesystem path for a specific icon.
	 *
	 * @since 2.2.8
	 *
	 * @param string $key The icon key (e.g. 'visa', 'mastercard').
	 *
	 * @return string The icon file path, or empty string if the key is not found.
	 */
	public static function get_icon_path( $key ) {
		if ( ! isset( self::$registry[ $key ] ) ) {
			return '';
		}

		return self::get_icons_dir() . self::$registry[ $key ]['file'];
	}

	/**
	 * Get all available icons with their metadata.
	 *
	 * @since 2.2.8
	 *
	 * @return array<string, array{label: string, url: string, path: string}>
	 */
	public static function get_all_icons() {
		$icons = array();
		foreach ( self::$registry as $key => $data ) {
			$icons[ $key ] = array(
				'label' => $data['label'],
				'url'   => self::get_icon_url( $key ),
				'path'  => self::get_icon_path( $key ),
			);
		}

		return $icons;
	}

	/**
	 * Get all available icon keys.
	 *
	 * @since 2.2.8
	 *
	 * @return string[]
	 */
	public static function get_icon_keys() {
		return array_keys( self::$registry );
	}

	/**
	 * Get the human-readable label for a specific icon.
	 *
	 * @since 2.2.8
	 *
	 * @param string $key The icon key.
	 *
	 * @return string The label, or empty string if the key is not found.
	 */
	public static function get_icon_label( $key ) {
		if ( ! isset( self::$registry[ $key ] ) ) {
			return '';
		}

		return self::$registry[ $key ]['label'];
	}

	/**
	 * Get icon options formatted for admin choices field.
	 *
	 * Returns an array of key => URL suitable for the 'choices' field type.
	 *
	 * @since 2.2.8
	 *
	 * @return array<string, string>
	 */
	public static function get_choices_options() {
		$options = array();
		foreach ( self::$registry as $key => $data ) {
			$options[ $key ] = self::get_icon_url( $key );
		}

		return $options;
	}

	/**
	 * Check if an icon key exists in the library.
	 *
	 * @since 2.2.8
	 *
	 * @param string $key The icon key.
	 *
	 * @return bool
	 */
	public static function has_icon( $key ) {
		return isset( self::$registry[ $key ] );
	}
}
