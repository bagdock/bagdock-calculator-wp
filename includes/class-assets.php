<?php
/**
 * CDN bundle + stylesheet enqueue. Loader runs on every page by default
 * so the shortcode can live in sidebars/footers that WP can't predict at
 * render time. Opt-out filter `bagdock_calculator_always_enqueue` lets
 * performance-conscious sites switch to on-demand enqueue from the
 * shortcode/block render paths.
 *
 * @package Bagdock\Calculator
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Bagdock_Calculator_Assets {

	/**
	 * @var Bagdock_Calculator_Assets|null
	 */
	private static $instance = null;

	/**
	 * @var bool Whether the frontend script+style have already been enqueued
	 *           this request.
	 */
	private $enqueued = false;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register(): void {
		$always_enqueue = (bool) apply_filters( 'bagdock_calculator_always_enqueue', true );

		if ( $always_enqueue ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ) );
		}

		// Admin-side settings page assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );

		// Block editor assets (Gutenberg).
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor' ) );
	}

	/**
	 * Enqueue the public-facing calculator script. Safe to call multiple
	 * times — WordPress dedupes by handle and our internal flag prevents
	 * redundant `wp_enqueue_script` noise during debugging.
	 */
	public function enqueue_frontend(): void {
		if ( $this->enqueued ) {
			return;
		}
		$this->enqueued = true;

		wp_register_style(
			'bagdock-calculator',
			Bagdock_Calculator_Options::cdn_stylesheet_url(),
			array(),
			BAGDOCK_CALCULATOR_VERSION
		);

		// `async` keeps the bundle off the critical render path. The
		// IIFE auto-initialises on DOMContentLoaded, so async is safe.
		wp_register_script(
			'bagdock-calculator',
			Bagdock_Calculator_Options::cdn_bundle_url(),
			array(),
			BAGDOCK_CALCULATOR_VERSION,
			array(
				'strategy'  => 'async',
				'in_footer' => true,
			)
		);

		wp_enqueue_style( 'bagdock-calculator' );
		wp_enqueue_script( 'bagdock-calculator' );
	}

	public function enqueue_admin( string $hook ): void {
		if ( 'settings_page_bagdock-calculator' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'bagdock-calculator-admin',
			BAGDOCK_CALCULATOR_URL . 'assets/css/admin.css',
			array(),
			BAGDOCK_CALCULATOR_VERSION
		);
	}

	public function enqueue_block_editor(): void {
		$asset_path = BAGDOCK_CALCULATOR_DIR . 'blocks/bagdock-calculator/build/index.asset.php';
		$deps       = array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' );
		$version    = BAGDOCK_CALCULATOR_VERSION;

		// Prefer compiled asset manifest when `wp-scripts build` has run
		// in CI. Falls back to hardcoded deps for dev installs where the
		// user hasn't run a build step.
		if ( file_exists( $asset_path ) ) {
			$manifest = include $asset_path;
			if ( is_array( $manifest ) ) {
				if ( isset( $manifest['dependencies'] ) && is_array( $manifest['dependencies'] ) ) {
					$deps = $manifest['dependencies'];
				}
				if ( isset( $manifest['version'] ) ) {
					$version = (string) $manifest['version'];
				}
			}
		}

		$script_rel = file_exists( BAGDOCK_CALCULATOR_DIR . 'blocks/bagdock-calculator/build/index.js' )
			? 'blocks/bagdock-calculator/build/index.js'
			: 'blocks/bagdock-calculator/index.js';

		wp_enqueue_script(
			'bagdock-calculator-block',
			BAGDOCK_CALCULATOR_URL . $script_rel,
			$deps,
			$version,
			true
		);

		wp_set_script_translations(
			'bagdock-calculator-block',
			'bagdock-calculator',
			BAGDOCK_CALCULATOR_DIR . 'languages'
		);
	}
}
