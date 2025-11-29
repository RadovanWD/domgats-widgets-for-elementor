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
define( 'DGWFE_VERSION', '1.0.0' );
define( 'DGWFE_FILE', __FILE__ );
define( 'DGWFE_PATH', plugin_dir_path( __FILE__ ) );
define( 'DGWFE_URL', plugin_dir_url( __FILE__ ) );
define( 'DGWFE_NONCE_ACTION', 'dgwfe-request' );

// Backwards-compatible constants.
if ( ! defined( 'DOMGATS_WIDGETS_VERSION' ) ) {
	define( 'DOMGATS_WIDGETS_VERSION', DGWFE_VERSION );
}
if ( ! defined( 'DOMGATS_WIDGETS_PLUGIN_FILE' ) ) {
	define( 'DOMGATS_WIDGETS_PLUGIN_FILE', DGWFE_FILE );
}
if ( ! defined( 'DOMGATS_WIDGETS_PATH' ) ) {
	define( 'DOMGATS_WIDGETS_PATH', DGWFE_PATH );
}
if ( ! defined( 'DOMGATS_WIDGETS_URL' ) ) {
	define( 'DOMGATS_WIDGETS_URL', DGWFE_URL );
}

/**
 * Safely build an asset version using filemtime when possible.
 *
 * @param string $relative Relative path from plugin root.
 *
 * @return string
 */
function dgwfe_asset_version( $relative ) {
	$file = DGWFE_PATH . ltrim( $relative, '/\\' );

	if ( file_exists( $file ) ) {
		return (string) filemtime( $file );
	}

	return DGWFE_VERSION;
}

/**
 * Register shared assets.
 */
function dgwfe_register_shared_assets() {
	wp_register_style(
		'domgats-widgets',
		DGWFE_URL . 'assets/css/domgats-widgets.css',
		[],
		dgwfe_asset_version( 'assets/css/domgats-widgets.css' )
	);

	wp_register_script(
		'domgats-widgets',
		DGWFE_URL . 'assets/js/domgats-widgets.js',
		[ 'jquery' ],
		dgwfe_asset_version( 'assets/js/domgats-widgets.js' ),
		true
	);
}

/**
 * Helper to build script localization data shared by REST and AJAX.
 *
 * @return array
 */
function dgwfe_get_script_data() {
	$rest_namespace = class_exists( '\DomGats\Widgets\Rest_Controller' )
		? \DomGats\Widgets\Rest_Controller::REST_NAMESPACE
		: 'domgats-widgets/v1';

	return [
		'restUrl'      => esc_url_raw( rest_url( $rest_namespace . '/grid' ) ),
		'ajaxUrl'      => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
		'nonce'        => wp_create_nonce( DGWFE_NONCE_ACTION ),
		'nonceAction'  => DGWFE_NONCE_ACTION,
		'transport'    => 'rest',
		'i18n'         => [
			'loading'  => __( 'Loading...', 'domgats-widgets-for-elementor' ),
			'noResult' => __( 'No results found.', 'domgats-widgets-for-elementor' ),
		],
	];
}

/**
 * Show admin notice when Elementor is missing.
 */
function dgwfe_missing_elementor_notice() {
	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'DomGat\'s Widgets for Elementor requires Elementor to be installed and activated.', 'domgats-widgets-for-elementor' )
	);
}

/**
 * Bootstrap plugin components when Elementor is ready.
 */
function dgwfe_bootstrap() {
	if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
		return;
	}

	load_plugin_textdomain( 'domgats-widgets-for-elementor', false, dirname( plugin_basename( DGWFE_FILE ) ) . '/languages' );
	dgwfe_register_shared_assets();

	require_once DGWFE_PATH . 'includes/class-domgats-plugin-loader.php';

	new \DomGats\Widgets\Plugin_Loader();
}

/**
 * Check for Elementor and start the plugin.
 */
function dgwfe_init() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'dgwfe_missing_elementor_notice' );
		return;
	}

	add_action( 'elementor/init', 'dgwfe_bootstrap', 20 );
}

add_action( 'plugins_loaded', 'dgwfe_init', 20 );
add_action( 'wp_enqueue_scripts', 'dgwfe_register_shared_assets' );

// Admin-only utilities are available even when Elementor is inactive.
if ( is_admin() ) {
	require_once DGWFE_PATH . 'includes/class-domgats-admin.php';
	add_action(
		'plugins_loaded',
		static function () {
			new \DomGats\Widgets\Admin();
		}
	);
}
