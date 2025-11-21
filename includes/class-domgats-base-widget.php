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
}
