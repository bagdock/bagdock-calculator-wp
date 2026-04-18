<?php
/**
 * Plugin Name:       Bagdock Calculator
 * Plugin URI:        https://github.com/bagdock/bagdock-calculator-wp
 * Description:       Embed the Bagdock storage size calculator anywhere on your WordPress site. Shortcode, Gutenberg block, Elementor widget, and Classic editor button included.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Bagdock
 * Author URI:        https://bagdock.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bagdock-calculator
 * Domain Path:       /languages
 *
 * @package Bagdock\Calculator
 */

declare( strict_types=1 );

// WordPress plugins are entry points — never allow direct execution. Checking
// ABSPATH is the canonical pattern in the WordPress coding standards handbook.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BAGDOCK_CALCULATOR_VERSION', '0.1.0' );
define( 'BAGDOCK_CALCULATOR_FILE', __FILE__ );
define( 'BAGDOCK_CALCULATOR_DIR', plugin_dir_path( __FILE__ ) );
define( 'BAGDOCK_CALCULATOR_URL', plugin_dir_url( __FILE__ ) );

// SDK v1 is pinned deliberately. Bagdock publishes `@bagdock/calculator`
// breaking changes under new /v2, /v3 CDN paths so sites that auto-update
// the plugin don't silently inherit major-version regressions. The plugin
// settings page exposes an override for sites that want to opt in early.
define( 'BAGDOCK_CALCULATOR_DEFAULT_CDN', 'https://cdn.bagdock.com/calculator/v1/embed.js' );
define( 'BAGDOCK_CALCULATOR_DEFAULT_STYLESHEET', 'https://cdn.bagdock.com/calculator/v1/index.css' );

require_once BAGDOCK_CALCULATOR_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'Bagdock_Calculator_Plugin', 'on_activate' ) );
register_deactivation_hook( __FILE__, array( 'Bagdock_Calculator_Plugin', 'on_deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		Bagdock_Calculator_Plugin::instance()->boot();
	}
);
