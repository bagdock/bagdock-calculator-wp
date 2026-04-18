<?php
/**
 * Classic editor TinyMCE button. Inserts a `[bagdock_calculator]` shortcode
 * with a tiny modal prompt for facility override.
 *
 * Only registers when Classic Editor is actually available (WP 6+ sites
 * often ship Gutenberg-only). Guarding on `user_can_richedit` means we
 * don't serve dead JS to plain-text authors either.
 *
 * @package Bagdock\Calculator
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Bagdock_Calculator_Tinymce {

	/**
	 * @var Bagdock_Calculator_Tinymce|null
	 */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'maybe_add_button' ) );
	}

	public function maybe_add_button(): void {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		if ( 'true' !== get_user_option( 'rich_editing' ) ) {
			return;
		}
		add_filter( 'mce_external_plugins', array( $this, 'register_plugin' ) );
		add_filter( 'mce_buttons', array( $this, 'register_button' ) );
	}

	/**
	 * @param array<string,string> $plugins
	 * @return array<string,string>
	 */
	public function register_plugin( array $plugins ): array {
		$plugins['bagdock_calculator'] = BAGDOCK_CALCULATOR_URL . 'assets/js/tinymce-button.js';
		return $plugins;
	}

	/**
	 * @param array<int,string> $buttons
	 * @return array<int,string>
	 */
	public function register_button( array $buttons ): array {
		$buttons[] = 'bagdock_calculator';
		return $buttons;
	}
}
