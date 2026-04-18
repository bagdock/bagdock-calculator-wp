<?php
/**
 * Main plugin bootstrap. Singleton wiring every subsystem.
 *
 * @package Bagdock\Calculator
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once BAGDOCK_CALCULATOR_DIR . 'includes/class-options.php';
require_once BAGDOCK_CALCULATOR_DIR . 'includes/class-assets.php';
require_once BAGDOCK_CALCULATOR_DIR . 'includes/class-shortcode.php';
require_once BAGDOCK_CALCULATOR_DIR . 'includes/class-settings.php';
require_once BAGDOCK_CALCULATOR_DIR . 'includes/class-block.php';
require_once BAGDOCK_CALCULATOR_DIR . 'includes/class-elementor.php';
require_once BAGDOCK_CALCULATOR_DIR . 'includes/class-tinymce.php';

/**
 * Plugin container. Lazily instantiated and memoised for the request.
 */
final class Bagdock_Calculator_Plugin {

	/**
	 * @var Bagdock_Calculator_Plugin|null
	 */
	private static $instance = null;

	/**
	 * @var bool Whether boot() has already run for this request.
	 */
	private $booted = false;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Wire hooks. Intentionally idempotent — WordPress fires `plugins_loaded`
	 * once per request, but tests and third-party admin tools sometimes
	 * re-trigger it via `do_action`.
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		load_plugin_textdomain(
			'bagdock-calculator',
			false,
			dirname( plugin_basename( BAGDOCK_CALCULATOR_FILE ) ) . '/languages'
		);

		Bagdock_Calculator_Assets::instance()->register();
		Bagdock_Calculator_Shortcode::instance()->register();
		Bagdock_Calculator_Settings::instance()->register();
		Bagdock_Calculator_Block::instance()->register();
		Bagdock_Calculator_Tinymce::instance()->register();

		// Elementor loads late and only when Elementor itself is active.
		// Registering on `elementor/widgets/register` avoids fatals on
		// sites that don't have Elementor installed.
		add_action(
			'elementor/widgets/register',
			array( Bagdock_Calculator_Elementor::instance(), 'register_widget' )
		);
	}

	/**
	 * Activation: seed default option rows if missing so the Settings page
	 * isn't blank on first visit. Never overwrite existing values — a user
	 * may be re-activating after a temporary deactivation.
	 */
	public static function on_activate(): void {
		$defaults = array(
			'bagdock_calculator_embed_key'           => '',
			'bagdock_calculator_storefront_url'      => '',
			'bagdock_calculator_default_facility_id' => '',
			'bagdock_calculator_region'              => '',
			'bagdock_calculator_preset'              => 'home-goods',
			'bagdock_calculator_cdn_bundle_url'      => BAGDOCK_CALCULATOR_DEFAULT_CDN,
			'bagdock_calculator_cdn_stylesheet_url'  => BAGDOCK_CALCULATOR_DEFAULT_STYLESHEET,
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key, false ) ) {
				add_option( $key, $value );
			}
		}
	}

	/**
	 * Deactivation hook. Currently a no-op — we keep options around so
	 * operators who toggle the plugin (e.g. during debugging) don't lose
	 * their embed key. Full data removal happens in `uninstall.php`.
	 */
	public static function on_deactivate(): void {
		// Reserved for future cleanup (transients, scheduled events).
	}
}
