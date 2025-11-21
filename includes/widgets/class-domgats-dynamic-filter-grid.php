<?php
/**
 * Dynamic Filter Grid widget.
 *
 * @package DomGats\Widgets
 */

namespace DomGats\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Displays a grid of posts with basic filtering and sorting.
 *
 * This is a foundational implementation that can be expanded with AJAX and additional controls.
 */
class Dynamic_Filter_Grid_Widget extends Domgats_Base_Widget {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'domgats_dynamic_filter_grid';
	}

	/**
	 * Title shown in Elementor panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Dynamic Filter Grid', 'domgats-widgets-for-elementor' );
	}

	/**
	 * Widget keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'posts', 'grid', 'filter', 'ajax', 'domgats' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {
		$this->register_query_controls();
		$this->register_layout_controls();
		$this->register_style_controls();
	}

	/**
	 * Query controls for selecting content source.
	 */
	protected function register_query_controls() {
		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Query', 'domgats-widgets-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'query_post_type',
			[
				'label'   => __( 'Source', 'domgats-widgets-for-elementor' ),
				'type'    => Controls_Manager::SELECT2,
				'options' => $this->get_public_post_types(),
				'default' => 'post',
			]
		);

		$this->add_control(
			'query_posts_per_page',
			[
				'label'   => __( 'Items Per Page', 'domgats-widgets-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 50,
			]
		);

		$this->add_control(
			'query_orderby',
			[
				'label'   => __( 'Order By', 'domgats-widgets-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'date'          => __( 'Date', 'domgats-widgets-for-elementor' ),
					'title'         => __( 'Title', 'domgats-widgets-for-elementor' ),
					'menu_order'    => __( 'Menu Order', 'domgats-widgets-for-elementor' ),
					'rand'          => __( 'Random', 'domgats-widgets-for-elementor' ),
				],
				'default' => 'date',
			]
		);

		$this->add_control(
			'query_order',
			[
				'label'   => __( 'Order', 'domgats-widgets-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'DESC' => __( 'Descending', 'domgats-widgets-for-elementor' ),
					'ASC'  => __( 'Ascending', 'domgats-widgets-for-elementor' ),
				],
				'default' => 'DESC',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Layout controls for grid behavior.
	 */
	protected function register_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'domgats-widgets-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'          => __( 'Columns', 'domgats-widgets-for-elementor' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				],
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'selectors'      => [
					'{{WRAPPER}} .domgats-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				],
			]
		);

		$this->add_responsive_control(
			'grid_gap',
			[
				'label'      => __( 'Gap', 'domgats-widgets-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
					],
				],
				'default'    => [
					'size' => 24,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .domgats-grid' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style controls for card text.
	 */
	protected function register_style_controls() {
		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Card', 'domgats-widgets-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_typography_controls( '{{WRAPPER}} .domgats-card__title', 'title_typography', __( 'Title Typography', 'domgats-widgets-for-elementor' ) );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'meta_typography',
				'label'    => __( 'Meta Typography', 'domgats-widgets-for-elementor' ),
				'selector' => '{{WRAPPER}} .domgats-card__meta',
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => __( 'Card Padding', 'domgats-widgets-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .domgats-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->register_common_style_controls();
	}

	/**
	 * Render front-end output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$query_args = [
			'post_type'      => $settings['query_post_type'],
			'posts_per_page' => (int) $settings['query_posts_per_page'],
			'orderby'        => $settings['query_orderby'],
			'order'          => $settings['query_order'],
		];

		$query = new WP_Query( $query_args );

		echo '<div class="domgats-widget domgats-grid" role="list">';

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$this->render_card();
			}
		} else {
			echo '<div class="domgats-card domgats-card--empty" role="listitem">';
			echo '<p>' . esc_html__( 'No results found.', 'domgats-widgets-for-elementor' ) . '</p>';
			echo '</div>';
		}

		wp_reset_postdata();

		echo '</div>';
	}

	/**
	 * Render a single card.
	 */
	private function render_card() {
		$permalink = get_permalink();

		echo '<article class="domgats-card" role="listitem">';

		if ( has_post_thumbnail() ) {
			echo '<a class="domgats-card__thumb" href="' . esc_url( $permalink ) . '">';
			the_post_thumbnail( 'large', [ 'loading' => 'lazy' ] );
			echo '</a>';
		}

		echo '<div class="domgats-card__body">';
		echo '<h3 class="domgats-card__title"><a href="' . esc_url( $permalink ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
		echo '<div class="domgats-card__meta">' . esc_html( get_the_date() ) . '</div>';
		echo '<p class="domgats-card__excerpt">' . esc_html( wp_trim_words( get_the_excerpt(), 20 ) ) . '</p>';
		echo '</div>';

		echo '</article>';
	}

	/**
	 * List of public post types for the query control.
	 *
	 * @return array
	 */
	private function get_public_post_types() {
		$post_types = get_post_types(
			[
				'public' => true,
			],
			'objects'
		);

		$options = [];

		foreach ( $post_types as $type => $object ) {
			$options[ $type ] = $object->labels->singular_name;
		}

		return $options;
	}
}
