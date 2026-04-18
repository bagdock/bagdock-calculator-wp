<?php
/**
 * Elementor integration bootstrap. The actual widget class is loaded lazily
 * only when Elementor is active to avoid fatal errors on sites that do not
 * use Elementor.
 *
 * @package Bagdock\Calculator
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Bagdock_Calculator_Elementor {

	/**
	 * @var Bagdock_Calculator_Elementor|null
	 */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Registered on the `elementor/widgets/register` hook, which only
	 * fires when Elementor has finished booting. Safe to reference
	 * Elementor classes here.
	 *
	 * @param object $widgets_manager Elementor widgets manager.
	 */
	public function register_widget( $widgets_manager ): void {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}
		if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) {
			return;
		}

		require_once BAGDOCK_CALCULATOR_DIR . 'includes/widgets/class-elementor-widget.php';

		if ( method_exists( $widgets_manager, 'register' ) ) {
			$widgets_manager->register( new Bagdock_Calculator_Elementor_Widget() );
		} elseif ( method_exists( $widgets_manager, 'register_widget_type' ) ) {
			// Elementor < 3.5 compatibility.
			$widgets_manager->register_widget_type( new Bagdock_Calculator_Elementor_Widget() );
		}
	}
}
