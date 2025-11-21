<?php
/**
 * Plugin loader for DomGat's Widgets.
 *
 * @package DomGats\Widgets
 */

namespace DomGats\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles registering Elementor widgets and shared assets.
 */
class Plugin_Loader {

	/**
	 * Plugin_Loader constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * Load required files.
	 */
	private function includes() {
		require_once DOMGATS_WIDGETS_PATH . 'includes/class-domgats-base-widget.php';
		require_once DOMGATS_WIDGETS_PATH . 'includes/widgets/class-domgats-dynamic-filter-grid.php';
	}

	/**
	 * Register hooks.
	 */
	private function hooks() {
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_frontend_assets' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
	}

	/**
	 * Register all custom widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {
		$widgets_manager->register( new Dynamic_Filter_Grid_Widget() );
	}

	/**
	 * Register shared assets within Elementor context.
	 */
	public function register_frontend_assets() {
		wp_register_style(
			'domgats-widgets',
			DOMGATS_WIDGETS_URL . 'assets/css/domgats-widgets.css',
			[],
			DOMGATS_WIDGETS_VERSION
		);

		wp_register_script(
			'domgats-widgets',
			DOMGATS_WIDGETS_URL . 'assets/js/domgats-widgets.js',
			[ 'jquery', 'elementor-frontend' ],
			DOMGATS_WIDGETS_VERSION,
			true
		);
	}

	/**
	 * Enqueue assets in the Elementor editor so widgets render correctly.
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_style( 'domgats-widgets' );
		wp_enqueue_script( 'domgats-widgets' );
	}
}
