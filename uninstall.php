<?php
/**
 * Uninstall handler — runs when an admin clicks "Delete" in Plugins > Installed.
 *
 * `register_uninstall_hook` is unreliable when the plugin is already deactivated
 * (WordPress can't load the hook callback), so the recommended pattern is a
 * top-level `uninstall.php` guarded by `WP_UNINSTALL_PLUGIN`.
 *
 * We deliberately only delete our own option rows. Pages that use the
 * shortcode keep their content — stripping shortcode-rendered output on
 * uninstall would silently mutate user content.
 *
 * @package Bagdock\Calculator
 */

declare( strict_types=1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options = array(
	'bagdock_calculator_embed_key',
	'bagdock_calculator_storefront_url',
	'bagdock_calculator_default_facility_id',
	'bagdock_calculator_region',
	'bagdock_calculator_preset',
	'bagdock_calculator_cdn_bundle_url',
	'bagdock_calculator_cdn_stylesheet_url',
);

foreach ( $options as $opt ) {
	delete_option( $opt );
	delete_site_option( $opt );
}
