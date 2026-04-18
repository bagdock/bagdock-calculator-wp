<?php
/**
 * Elementor widget exposing the Bagdock Calculator shortcode as a drag-and-drop
 * element with a controls panel.
 *
 * @package Bagdock\Calculator
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// WordPress plugins conventionally don't namespace classes exposed on the
// autoload path; Elementor discovers widgets by class_exists at runtime.
// phpcs:enable

final class Bagdock_Calculator_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name(): string {
		return 'bagdock_calculator';
	}

	public function get_title(): string {
		return __( 'Bagdock Calculator', 'bagdock-calculator' );
	}

	public function get_icon(): string {
		return 'eicon-calculator';
	}

	/**
	 * @return array<int,string>
	 */
	public function get_categories(): array {
		return array( 'general' );
	}

	/**
	 * @return array<int,string>
	 */
	public function get_keywords(): array {
		return array( 'bagdock', 'calculator', 'storage', 'size', 'self-storage' );
	}

	protected function register_controls(): void {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Calculator', 'bagdock-calculator' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'mode',
			array(
				'label'   => __( 'Display', 'bagdock-calculator' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'button',
				'options' => array(
					'button' => __( 'Button that opens modal', 'bagdock-calculator' ),
					'inline' => __( 'Inline (always visible)', 'bagdock-calculator' ),
				),
			)
		);

		$this->add_control(
			'button_label',
			array(
				'label'       => __( 'Button label', 'bagdock-calculator' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'Size calculator', 'bagdock-calculator' ),
				'label_block' => true,
				'condition'   => array( 'mode' => 'button' ),
			)
		);

		$this->add_control(
			'facility_id',
			array(
				'label'       => __( 'Facility ID override', 'bagdock-calculator' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => 'fac_…',
				'description' => __( 'Leave blank to use the default from plugin settings.', 'bagdock-calculator' ),
			)
		);

		$this->add_control(
			'preset',
			array(
				'label'   => __( 'Preset', 'bagdock-calculator' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''            => __( 'Use default from settings', 'bagdock-calculator' ),
					'home-goods'  => __( 'Home goods', 'bagdock-calculator' ),
					'vehicle'     => __( 'Vehicle', 'bagdock-calculator' ),
					'business'    => __( 'Business', 'bagdock-calculator' ),
					'wine'        => __( 'Wine', 'bagdock-calculator' ),
				),
			)
		);

		$this->add_control(
			'region',
			array(
				'label'   => __( 'Region override', 'bagdock-calculator' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''      => __( 'Use default', 'bagdock-calculator' ),
					'uk_ie' => __( 'UK & Ireland', 'bagdock-calculator' ),
					'eu'    => __( 'Europe', 'bagdock-calculator' ),
					'usa'   => __( 'United States', 'bagdock-calculator' ),
				),
			)
		);

		$this->add_control(
			'storefront_url',
			array(
				'label'       => __( 'Storefront URL override', 'bagdock-calculator' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://example.com/book',
				'show_external' => false,
				'default'     => array(
					'url'         => '',
					'is_external' => false,
					'nofollow'    => false,
				),
			)
		);

		$this->add_control(
			'embed_key_override',
			array(
				'label'       => __( 'Embed key override (rare)', 'bagdock-calculator' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => 'ek_live_…',
				'description' => __( 'Leave blank unless you need to mount the calculator for a different operator on this specific placement.', 'bagdock-calculator' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings       = $this->get_settings_for_display();
		$storefront_url = '';
		if ( isset( $settings['storefront_url']['url'] ) && is_string( $settings['storefront_url']['url'] ) ) {
			$storefront_url = $settings['storefront_url']['url'];
		}

		$shortcode_atts = array(
			'embed_key'      => (string) ( $settings['embed_key_override'] ?? '' ),
			'facility_id'    => (string) ( $settings['facility_id'] ?? '' ),
			'storefront_url' => $storefront_url,
			'region'         => (string) ( $settings['region'] ?? '' ),
			'preset'         => (string) ( $settings['preset'] ?? '' ),
			'button_label'   => (string) ( $settings['button_label'] ?? '' ),
			'mode'           => (string) ( $settings['mode'] ?? 'button' ),
		);

		// Strip empties so the shortcode falls through to the plugin
		// Settings defaults for anything the editor didn't override.
		$shortcode_atts = array_filter(
			$shortcode_atts,
			static fn( $v ): bool => '' !== $v
		);

		// Output is generated by the shortcode which escapes every piece.
		echo Bagdock_Calculator_Shortcode::instance()->render( $shortcode_atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
