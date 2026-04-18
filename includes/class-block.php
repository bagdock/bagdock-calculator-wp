<?php
/**
 * Gutenberg block registration.
 *
 * Uses server-side rendering so the same sanitisation/fallback logic the
 * shortcode uses applies identically to blocks. The editor preview is a
 * ServerSideRender component that fetches this PHP callback via the REST
 * API, which means authors see a faithful preview without the SDK having
 * to mount inside the editor iframe.
 *
 * @package Bagdock\Calculator
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Bagdock_Calculator_Block {

	/**
	 * @var Bagdock_Calculator_Block|null
	 */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register(): void {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	public function register_block(): void {
		// `register_block_type` with a block.json path auto-handles
		// editor asset registration when `wp-scripts build` has produced
		// a `build/` directory. We pass `render_callback` so even
		// hand-installed builds (no wp-scripts) render identically.
		$block_dir = BAGDOCK_CALCULATOR_DIR . 'blocks/bagdock-calculator';

		$has_build = file_exists( $block_dir . '/build/block.json' );
		$metadata  = $has_build ? $block_dir . '/build' : $block_dir;

		register_block_type(
			$metadata,
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * @param array<string,mixed> $attributes
	 */
	public function render_block( array $attributes ): string {
		$shortcode_atts = array();

		// Only forward attributes that were explicitly set in the block.
		// Empty strings are preserved so a block that clears an inherited
		// default (e.g. wants to unset facility_id) actually does so.
		$map = array(
			'embedKey'      => 'embed_key',
			'facilityId'    => 'facility_id',
			'storefrontUrl' => 'storefront_url',
			'region'        => 'region',
			'preset'        => 'preset',
			'buttonLabel'   => 'button_label',
			'mode'          => 'mode',
			'className'     => 'class',
		);

		foreach ( $map as $block_attr => $sc_attr ) {
			if ( array_key_exists( $block_attr, $attributes ) && '' !== $attributes[ $block_attr ] ) {
				$shortcode_atts[ $sc_attr ] = (string) $attributes[ $block_attr ];
			}
		}

		return Bagdock_Calculator_Shortcode::instance()->render( $shortcode_atts );
	}
}
