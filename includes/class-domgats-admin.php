<?php
/**
 * Admin utilities for DomGat's Widgets.
 *
 * @package DomGats\Widgets
 */

namespace DomGats\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds a Tools page that records environment details for debugging.
 */
class Admin {

	/**
	 * Option key for the stored environment log.
	 */
	const LOG_OPTION = 'domgats_widgets_env_log';

	/**
	 * Boot hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_tools_page' ] );
	}

	/**
	 * Register the Tools > DomGats Widgets page.
	 */
	public function register_tools_page() {
		$hook = add_management_page(
			__( "DomGat's Widgets", 'domgats-widgets-for-elementor' ),
			__( "DomGat's Widgets", 'domgats-widgets-for-elementor' ),
			'manage_options',
			'domgats-widgets-about',
			[ $this, 'render_page' ]
		);

		add_action( "load-$hook", [ $this, 'record_environment_versions' ] );
	}

	/**
	 * Record environment versions into an option.
	 */
	public function record_environment_versions() {
		$entry = [
			'timestamp'   => current_time( 'mysql' ),
			'wordpress'   => get_bloginfo( 'version' ),
			'php'         => PHP_VERSION,
			'elementor'   => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '',
			'woocommerce' => defined( 'WC_VERSION' ) ? WC_VERSION : '',
			'acf'         => defined( 'ACF_VERSION' ) ? ACF_VERSION : '',
		];

		$log = get_option( self::LOG_OPTION, [] );
		array_unshift( $log, $entry );
		$log = array_slice( $log, 0, 10 );

		update_option( self::LOG_OPTION, $log, false );
	}

	/**
	 * Render the Tools page.
	 */
	public function render_page() {
		$log = get_option( self::LOG_OPTION, [] );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( "DomGat's Widgets Environment", 'domgats-widgets-for-elementor' ); ?></h1>
			<p><?php esc_html_e( 'The latest entry is refreshed each time you open this page.', 'domgats-widgets-for-elementor' ); ?></p>
			<table class="widefat striped" style="max-width: 720px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Captured', 'domgats-widgets-for-elementor' ); ?></th>
						<th><?php esc_html_e( 'WordPress', 'domgats-widgets-for-elementor' ); ?></th>
						<th><?php esc_html_e( 'PHP', 'domgats-widgets-for-elementor' ); ?></th>
						<th><?php esc_html_e( 'Elementor', 'domgats-widgets-for-elementor' ); ?></th>
						<th><?php esc_html_e( 'WooCommerce', 'domgats-widgets-for-elementor' ); ?></th>
						<th><?php esc_html_e( 'ACF', 'domgats-widgets-for-elementor' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $log ) ) : ?>
						<tr>
							<td colspan="6"><?php esc_html_e( 'No entries recorded yet.', 'domgats-widgets-for-elementor' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $log as $entry ) : ?>
							<tr>
								<td><?php echo esc_html( $entry['timestamp'] ?? '' ); ?></td>
								<td><?php echo esc_html( $entry['wordpress'] ?? '' ); ?></td>
								<td><?php echo esc_html( $entry['php'] ?? '' ); ?></td>
								<td><?php echo esc_html( $entry['elementor'] ?? __( 'Not active', 'domgats-widgets-for-elementor' ) ); ?></td>
								<td><?php echo esc_html( $entry['woocommerce'] ?? __( 'Not active', 'domgats-widgets-for-elementor' ) ); ?></td>
								<td><?php echo esc_html( $entry['acf'] ?? __( 'Not active', 'domgats-widgets-for-elementor' ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
