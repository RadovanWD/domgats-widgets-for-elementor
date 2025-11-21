<?php
/**
 * Base widget class shared across DomGat widgets.
 *
 * @package DomGats\Widgets
 */

namespace DomGats\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Provides shared controls and helpers for all widgets.
 */
abstract class Domgats_Base_Widget extends Widget_Base {

	/**
	 * Return widget icon for Elementor panel.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * Load common widget styles/scripts.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'domgats-widgets' ];
	}

	/**
	 * Load common widget styles/scripts.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'domgats-widgets' ];
	}

	/**
	 * Register common layout and typography controls.
	 */
	protected function register_common_style_controls() {
		$this->start_controls_section(
			'section_container_style',
			[
				'label' => __( 'Container', 'domgats-widgets-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'container_background',
				'selector' => '{{WRAPPER}} .domgats-widget',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .domgats-widget',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_shadow',
				'selector' => '{{WRAPPER}} .domgats-widget',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => __( 'Padding', 'domgats-widgets-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .domgats-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_margin',
			[
				'label'      => __( 'Margin', 'domgats-widgets-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .domgats-widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Typography controls shared across widgets.
	 *
	 * @param string $selector CSS selector.
	 * @param string $name     Control name prefix.
	 * @param string $label    Label to display.
	 */
	protected function add_typography_controls( $selector, $name = 'typography', $label = '' ) {
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => $name,
				'label'    => $label ? $label : __( 'Typography', 'domgats-widgets-for-elementor' ),
				'selector' => $selector,
			]
		);
	}

	/**
	 * Return available Elementor templates (loop capable).
	 *
	 * @return array
	 */
	protected function get_elementor_templates() {
		$options = [ '0' => __( '— Select Template —', 'domgats-widgets-for-elementor' ) ];

		$templates = get_posts(
			[
				'post_type'      => 'elementor_library',
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);

		foreach ( $templates as $template ) {
			$options[ $template->ID ] = $template->post_title;
		}

		return $options;
	}

	/**
	 * Whether ACF is available.
	 *
	 * @return bool
	 */
	protected function is_acf_active() {
		return function_exists( 'acf_get_field_groups' );
	}

	/**
	 * Check conditional rendering rules.
	 *
	 * @param array $settings Widget settings.
	 *
	 * @return bool
	 */
	protected function passes_visibility_rules( array $settings ) {
		if ( ! empty( $settings['visibility_logged_in_only'] ) && ! is_user_logged_in() ) {
			return false;
		}

		if ( ! empty( $settings['visibility_roles'] ) && is_array( $settings['visibility_roles'] ) ) {
			$user        = wp_get_current_user();
			$allowed     = array_map( 'sanitize_text_field', $settings['visibility_roles'] );
			$user_roles  = (array) $user->roles;
			$has_allowed = array_intersect( $allowed, $user_roles );

			if ( empty( $has_allowed ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Provide common animation-related CSS class names.
	 *
	 * @param string $style Animation style.
	 *
	 * @return string
	 */
	protected function get_animation_class( $style ) {
		$allowed = [
			'none',
			'fade',
			'slide-up',
			'slide-right',
			'zoom-in',
		];

		if ( ! in_array( $style, $allowed, true ) ) {
			return '';
		}

		return 'none' === $style ? '' : 'domgats-animate domgats-animate--' . $style;
	}
}
