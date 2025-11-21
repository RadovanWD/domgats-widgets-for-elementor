<?php
/**
 * REST controller for DomGat's Widgets.
 *
 * @package DomGats\Widgets
 */

namespace DomGats\Widgets;

use WP_REST_Request;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles REST endpoints used by the widgets.
 */
class Rest_Controller {

	const REST_NAMESPACE = 'domgats-widgets/v1';

	/**
	 * Boot the controller.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/grid',
			[
				'methods'             => WP_REST_Server::READABLE | WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'handle_grid' ],
				'permission_callback' => [ $this, 'permission_check' ],
				'args'                => [
					'settings' => [
						'required' => true,
						'type'     => 'object',
					],
					'filters'  => [
						'required' => false,
						'type'     => 'object',
					],
					'page'     => [
						'required' => false,
						'type'     => 'integer',
					],
				],
			]
		);
	}

	/**
	 * Permission callback to allow filtering for guests while still allowing plugins to restrict.
	 *
	 * @param WP_REST_Request $request Request instance.
	 *
	 * @return bool
	 */
	public function permission_check( $request ) {
		return apply_filters( 'domgats/widgets/rest_permission', true, $request );
	}

	/**
	 * Handle grid AJAX/REST requests.
	 *
	 * @param WP_REST_Request $request Request instance.
	 *
	 * @return array
	 */
	public function handle_grid( WP_REST_Request $request ) {
		$settings = $this->sanitize_settings( (array) $request->get_param( 'settings' ) );
		$filters  = $this->sanitize_filters( (array) $request->get_param( 'filters' ) );
		$page     = max( 1, (int) $request->get_param( 'page' ) );

		$widget = new Dynamic_Filter_Grid_Widget();

		return $widget->handle_ajax_query( $settings, $filters, $page );
	}

	/**
	 * Sanitize inbound settings to reduce surface area.
	 *
	 * @param array $settings Raw settings.
	 *
	 * @return array
	 */
	private function sanitize_settings( array $settings ) {
		$allowed = [
			'query_post_type',
			'query_taxonomy',
			'query_terms',
			'query_posts_per_page',
			'query_orderby',
			'query_order',
			'query_offset',
			'query_include_ids',
			'query_exclude_ids',
			'query_pinned_ids',
			'query_exclude_terms',
			'query_include_authors',
			'query_exclude_authors',
			'query_current_post_only',
			'query_ignore_sticky',
			'query_avoid_duplicates',
			'query_pinned_ids',
			'query_meta_key',
			'query_meta_type',
			'filter_enabled',
			'filter_ui',
			'filter_show_all',
			'filter_logic',
			'filter_default',
			'filter_deep_link',
			'meta_filter_enable',
			'meta_filter_key',
			'meta_filter_ui',
			'meta_filter_options',
			'layout_type',
			'columns',
			'columns_tablet',
			'columns_mobile',
			'grid_gap',
			'grid_gap_tablet',
			'grid_gap_mobile',
			'equal_height',
			'use_loop_template',
			'loop_template_id',
			'pagination_type',
			'items_per_load',
			'loading_animation',
			'animation_style',
			'animation_stagger',
			'show_sorting',
			'external_enable',
			'external_url',
			'external_root',
			'cta_buttons',
			'no_results_message',
			'no_results_template',
		];

		$sanitized = [];

		foreach ( $settings as $key => $value ) {
			if ( ! in_array( $key, $allowed, true ) ) {
				continue;
			}

			$sanitized[ $key ] = $this->sanitize_deep( $value );
		}

		return $sanitized;
	}

	/**
	 * Sanitize filters payload.
	 *
	 * @param array $filters Request filters.
	 *
	 * @return array
	 */
	private function sanitize_filters( array $filters ) {
		$sanitized = [];

		foreach ( $filters as $key => $value ) {
			if ( is_array( $value ) ) {
				$sanitized[ sanitize_key( $key ) ] = array_map( 'sanitize_text_field', $value );
			} else {
				$sanitized[ sanitize_key( $key ) ] = sanitize_text_field( $value );
			}
		}

		return $sanitized;
	}

	/**
	 * Basic value sanitizer.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return string
	 */
	private function sanitize_value( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return $value + 0; // Ensure numeric type.
		}

		return sanitize_text_field( (string) $value );
	}

	/**
	 * Recursively sanitize arrays/values.
	 *
	 * @param mixed $value Value.
	 *
	 * @return mixed
	 */
	private function sanitize_deep( $value ) {
		if ( is_array( $value ) ) {
			$clean = [];
			foreach ( $value as $k => $v ) {
				$clean[ sanitize_key( (string) $k ) ] = $this->sanitize_deep( $v );
			}
			return $clean;
		}

		return $this->sanitize_value( $value );
	}
}
