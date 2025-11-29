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
		require_once DGWFE_PATH . 'includes/class-domgats-rest-controller.php';
		require_once DGWFE_PATH . 'includes/class-domgats-base-widget.php';
		require_once DGWFE_PATH . 'includes/widgets/class-domgats-dynamic-filter-grid.php';
	}

	/**
	 * Register hooks.
	 */
	private function hooks() {
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_frontend_assets' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
		add_action( 'init', [ $this, 'boot_rest' ] );
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
	 * Register custom Elementor category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'domgats-widgets',
			[
				'title' => __( "DomGat's Widgets", 'domgats-widgets-for-elementor' ),
				'icon'  => 'eicon-gallery-grid',
			]
		);
	}

	/**
	 * Register shared assets within Elementor context.
	 */
	public function register_frontend_assets() {
		dgwfe_register_shared_assets();

		// Ensure Elementor assets load before ours when used inside the editor.
		wp_register_script(
			'domgats-widgets',
			DGWFE_URL . 'assets/js/domgats-widgets.js',
			[ 'jquery', 'elementor-frontend' ],
			dgwfe_asset_version( 'assets/js/domgats-widgets.js' ),
			true
		);

		wp_localize_script(
			'domgats-widgets',
			'domgatsWidgetsData',
			dgwfe_get_script_data()
		);
	}

	/**
	 * Enqueue assets in the Elementor editor so widgets render correctly.
	 */
	public function enqueue_editor_assets() {
		dgwfe_register_shared_assets();
		wp_enqueue_style( 'domgats-widgets' );
		wp_enqueue_script( 'domgats-widgets' );
	}

	/**
	 * Boot REST controller.
	 */
	public function boot_rest() {
		new Rest_Controller();
	}
}
