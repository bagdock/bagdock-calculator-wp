<?php
/**
 * [bagdock_calculator] shortcode.
 *
 * Renders the container element the SDK loader hydrates. Attributes are
 * sanitised against the same rules the Settings page uses so a hand-authored
 * shortcode can't smuggle arbitrary HTML or invalid IDs into the page.
 *
 * Example:
 *
 *   [bagdock_calculator
 *       embed_key="ek_live_…"
 *       facility_id="fac_01H…"
 *       preset="home-goods"
 *       region="uk_ie"
 *       storefront_url="https://demo.bagdock.com/book"
 *       button_label="Find my storage size"]
 *
 * Omitting any attribute falls back to the Settings page default.
 *
 * @package Bagdock\Calculator
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Bagdock_Calculator_Shortcode {

	public const TAG = 'bagdock_calculator';

	/**
	 * @var Bagdock_Calculator_Shortcode|null
	 */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register(): void {
		add_shortcode( self::TAG, array( $this, 'render' ) );
	}

	/**
	 * @param array<string,string>|string $atts    Raw shortcode attributes.
	 * @param string|null                  $content Shortcode body (used as button label).
	 */
	public function render( $atts, $content = null ): string {
		$defaults = Bagdock_Calculator_Options::render_defaults();

		$atts = shortcode_atts(
			array(
				'embed_key'      => $defaults['embed_key'],
				'facility_id'    => $defaults['facility_id'],
				'storefront_url' => $defaults['storefront_url'],
				'region'         => $defaults['region'],
				'preset'         => $defaults['preset'],
				'button_label'   => __( 'Size calculator', 'bagdock-calculator' ),
				'mode'           => 'button', // 'button' | 'inline'
				'class'          => '',
			),
			is_array( $atts ) ? $atts : array(),
			self::TAG
		);

		$embed_key      = (string) $atts['embed_key'];
		$facility_id    = (string) $atts['facility_id'];
		$storefront_url = Bagdock_Calculator_Options::sanitize_url( (string) $atts['storefront_url'] );
		$region         = Bagdock_Calculator_Options::sanitize_region( (string) $atts['region'] );
		$preset         = Bagdock_Calculator_Options::sanitize_preset( (string) $atts['preset'] );
		$button_label   = (string) $atts['button_label'];
		$mode           = 'inline' === $atts['mode'] ? 'inline' : 'button';
		$extra_class    = sanitize_html_class( (string) $atts['class'], '' );

		if ( $content ) {
			// Shortcode body wins over the button_label attribute for
			// Gutenberg-free users who'd rather write markup directly.
			$button_label = wp_strip_all_tags( (string) $content );
		}

		// Invalid embed key → surface a capability-gated warning (so end
		// visitors don't see debug noise) and render nothing. This is a
		// soft-fail; hard-failing breaks every page on the site the first
		// time an operator rotates their key.
		if ( '' !== $embed_key && ! Bagdock_Calculator_Options::is_valid_embed_key( $embed_key ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				return sprintf(
					'<!-- %s -->',
					esc_html__( 'Bagdock Calculator: embed_key does not match the ek_live_/ek_test_ format.', 'bagdock-calculator' )
				);
			}
			return '';
		}

		if ( '' !== $facility_id && ! Bagdock_Calculator_Options::is_valid_facility_id( $facility_id ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				return sprintf(
					'<!-- %s -->',
					esc_html__( 'Bagdock Calculator: facility_id must start with fac_ and be at least 20 chars.', 'bagdock-calculator' )
				);
			}
			return '';
		}

		// Defensive: if the enqueue filter has been disabled globally,
		// fire it directly here. The assets singleton memoises so this
		// is safe to call from every shortcode on the page.
		Bagdock_Calculator_Assets::instance()->enqueue_frontend();

		$container_attrs = array(
			'data-bagdock-calculator' => '',
		);

		// `data-key` is the unified-key attribute the SDK loader reads.
		// Omitting it renders the calculator in "BYO config" mode where
		// presets ship but no operator branding / overrides load.
		if ( '' !== $embed_key ) {
			$container_attrs['data-key'] = $embed_key;
		}
		if ( '' !== $facility_id ) {
			$container_attrs['data-facility-id'] = $facility_id;
		}
		if ( '' !== $storefront_url ) {
			$container_attrs['data-storefront-url'] = $storefront_url;
		}
		if ( '' !== $region ) {
			$container_attrs['data-region'] = $region;
		}
		if ( '' !== $preset ) {
			$container_attrs['data-preset'] = $preset;
		}

		$attrs_string = '';
		foreach ( $container_attrs as $name => $value ) {
			$attrs_string .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		$classes = 'bagdock-calculator-embed';
		if ( '' !== $extra_class ) {
			$classes .= ' ' . $extra_class;
		}

		if ( 'inline' === $mode ) {
			// Inline mode renders the container only; host page is expected
			// to style it. The SDK will mount its modal-less surface when
			// we ship it; for now the modal still opens on top.
			return sprintf(
				'<div class="%s" %s></div>',
				esc_attr( $classes ),
				$attrs_string // Each value is esc_attr'd above.
			);
		}

		// Button mode: a click trigger + the hidden container.
		return sprintf(
			'<div class="%s">'
				. '<button type="button" class="bagdock-calculator-button" data-bagdock-calculator-open>%s</button>'
				. '<div %s></div>'
				. '</div>',
			esc_attr( $classes ),
			esc_html( $button_label ),
			$attrs_string
		);
	}
}
