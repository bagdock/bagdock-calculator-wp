<?php
/**
 * Option accessors. Centralised so the shortcode, block, Elementor widget,
 * and TinyMCE preview all read and sanitise the same way.
 *
 * @package Bagdock\Calculator
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Bagdock_Calculator_Options {

	public const KEY_EMBED_KEY        = 'bagdock_calculator_embed_key';
	public const KEY_STOREFRONT_URL   = 'bagdock_calculator_storefront_url';
	public const KEY_DEFAULT_FACILITY = 'bagdock_calculator_default_facility_id';
	public const KEY_REGION           = 'bagdock_calculator_region';
	public const KEY_PRESET           = 'bagdock_calculator_preset';
	public const KEY_CDN_BUNDLE       = 'bagdock_calculator_cdn_bundle_url';
	public const KEY_CDN_STYLESHEET   = 'bagdock_calculator_cdn_stylesheet_url';

	/**
	 * Presets accepted by the SDK. The `mixed` preset is intentionally
	 * excluded from the Settings dropdown — it's useful for demos but
	 * overwhelming as a default for real storefronts.
	 */
	public const PRESET_CHOICES = array( 'home-goods', 'vehicle', 'business', 'wine' );
	public const REGION_CHOICES = array( '', 'uk_ie', 'eu', 'usa' );

	/**
	 * Embed keys follow the unified-key format used by `@bagdock/hive` and
	 * `@bagdock/loyalty`: `ek_live_…` or `ek_test_…` with the
	 * `calculator:read` scope. We validate the prefix here to catch obvious
	 * mistakes (e.g. pasting a restricted key meant for server use).
	 */
	public static function is_valid_embed_key( string $key ): bool {
		if ( '' === $key ) {
			return true; // Allow empty — settings page surfaces a warning instead of blocking save.
		}
		return (bool) preg_match( '/^ek_(live|test)_[A-Za-z0-9_-]{8,}$/', $key );
	}

	public static function is_valid_facility_id( string $id ): bool {
		if ( '' === $id ) {
			return true;
		}
		return (bool) preg_match( '/^fac_[A-Za-z0-9]{16,}$/', $id );
	}

	public static function sanitize_preset( string $preset ): string {
		return in_array( $preset, self::PRESET_CHOICES, true ) ? $preset : 'home-goods';
	}

	public static function sanitize_region( string $region ): string {
		return in_array( $region, self::REGION_CHOICES, true ) ? $region : '';
	}

	public static function sanitize_url( string $url ): string {
		$trimmed = trim( $url );
		if ( '' === $trimmed ) {
			return '';
		}
		$clean = esc_url_raw( $trimmed, array( 'https', 'http' ) );
		return is_string( $clean ) ? $clean : '';
	}

	/**
	 * Build the default attribute bag that shortcode/block/Elementor merge
	 * user overrides into. Keeps defaults in one place.
	 *
	 * @return array<string,string>
	 */
	public static function render_defaults(): array {
		return array(
			'embed_key'      => (string) get_option( self::KEY_EMBED_KEY, '' ),
			'facility_id'    => (string) get_option( self::KEY_DEFAULT_FACILITY, '' ),
			'storefront_url' => (string) get_option( self::KEY_STOREFRONT_URL, '' ),
			'region'         => (string) get_option( self::KEY_REGION, '' ),
			'preset'         => (string) get_option( self::KEY_PRESET, 'home-goods' ),
		);
	}

	public static function cdn_bundle_url(): string {
		$stored = (string) get_option( self::KEY_CDN_BUNDLE, BAGDOCK_CALCULATOR_DEFAULT_CDN );
		return '' !== $stored ? $stored : BAGDOCK_CALCULATOR_DEFAULT_CDN;
	}

	public static function cdn_stylesheet_url(): string {
		$stored = (string) get_option( self::KEY_CDN_STYLESHEET, BAGDOCK_CALCULATOR_DEFAULT_STYLESHEET );
		return '' !== $stored ? $stored : BAGDOCK_CALCULATOR_DEFAULT_STYLESHEET;
	}
}
