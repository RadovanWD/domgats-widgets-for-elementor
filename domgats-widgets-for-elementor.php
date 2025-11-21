<?php
/**
 * Plugin Name: DomGat's Widgets for Elementor
 * Description: A suite of advanced widgets for Elementor, starting with the Dynamic Filter Grid.
 * Plugin URI:  https://example.com
 * Author:      DomGat
 * Version:     1.0.0
 * Text Domain: domgats-widgets-for-elementor
 *
 * @package DomGats\Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Plugin constants.
define( 'DOMGATS_WIDGETS_VERSION', '1.0.0' );
define( 'DOMGATS_WIDGETS_PLUGIN_FILE', __FILE__ );
define( 'DOMGATS_WIDGETS_PATH', plugin_dir_path( __FILE__ ) );
define( 'DOMGATS_WIDGETS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check for Elementor and bootstrap the plugin.
 */
function domgats_widgets_init() {
	// Bail early if Elementor is not loaded.
	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action(
			'admin_notices',
			static function () {
				printf(
					'<div class="notice notice-warning"><p>%s</p></div>',
					esc_html__( 'DomGat\'s Widgets for Elementor requires Elementor to be installed and activated.', 'domgats-widgets-for-elementor' )
				);
			}
		);

		return;
	}

	// Load translations.
	load_plugin_textdomain( 'domgats-widgets-for-elementor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	// Bootstrap the loader.
	require_once DOMGATS_WIDGETS_PATH . 'includes/class-domgats-plugin-loader.php';

	new \DomGats\Widgets\Plugin_Loader();
}

add_action( 'plugins_loaded', 'domgats_widgets_init' );

/**
 * Enqueue shared assets.
 */
function domgats_widgets_enqueue_assets() {
	wp_register_style(
		'domgats-widgets',
		DOMGATS_WIDGETS_URL . 'assets/css/domgats-widgets.css',
		[],
		DOMGATS_WIDGETS_VERSION
	);

	wp_register_script(
		'domgats-widgets',
		DOMGATS_WIDGETS_URL . 'assets/js/domgats-widgets.js',
		[ 'jquery' ],
		DOMGATS_WIDGETS_VERSION,
		true
	);
}

add_action( 'wp_enqueue_scripts', 'domgats_widgets_enqueue_assets' );
*** End Patch
