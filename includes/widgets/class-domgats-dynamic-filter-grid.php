<?php

/**
 * Dynamic Filter Grid widget.
 *
 * @package DomGats\Widgets
 */

namespace DomGats\Widgets;

use WP_Query;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Displays a grid of posts with advanced filtering, sorting, pagination, and templating options.
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
        return __('Dynamic Filter Grid', 'domgats-widgets-for-elementor');
    }

    /**
     * Widget keywords.
     *
     * @return array
     */
    public function get_keywords() {
        return ['posts', 'grid', 'filter', 'ajax', 'domgats', 'masonry', 'slider'];
    }

    /**
     * Place widget under custom category.
     *
     * @return array
     */
    public function get_categories() {
        return ['domgats-widgets'];
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        $this->register_query_controls();
        $this->register_filter_controls();
        $this->register_layout_controls();
        $this->register_pagination_controls();
        $this->register_cta_controls();
        $this->register_template_controls();
        $this->register_no_results_controls();
        $this->register_external_controls();
        $this->register_advanced_controls();
        $this->register_style_controls();
    }

    /**
     * Query controls for selecting content source.
     */
    protected function register_query_controls() {
        $this->start_controls_section(
            'section_query',
            [
                'label' => __('Query', 'domgats-widgets-for-elementor'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'query_post_type',
            [
                'label'   => __('Source', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::SELECT2,
                'options' => $this->get_public_post_types(),
                'default' => class_exists('WooCommerce') ? 'product' : 'post',
            ]
        );

        $this->add_control(
            'query_taxonomy',
            [
                'label'       => __('Taxonomy Filter', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_public_taxonomies(),
                'default'     => 'category',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'query_terms',
            [
                'label'       => __('Limit to Terms', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_all_terms_options(),
                'multiple'    => true,
                'label_block' => true,
                'description' => __('Pre-filter the query with these terms. Front-end filters are applied on top.', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'query_posts_per_page',
            [
                'label'   => __('Items Per Page', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
                'min'     => 1,
                'max'     => 50,
            ]
        );

        $this->add_control(
            'query_offset',
            [
                'label'       => __('Offset', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 0,
                'description' => __('Skip this many posts before starting to collect results.', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'query_include_ids',
            [
                'label'       => __('Include IDs', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_posts_options(),
                'multiple'    => true,
                'label_block' => true,
                'description' => __('Select posts/products to force-include.', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'query_exclude_ids',
            [
                'label'       => __('Exclude IDs', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_posts_options(),
                'multiple'    => true,
                'label_block' => true,
                'description' => __('Select posts/products to exclude.', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'query_pinned_ids',
            [
                'label'       => __('Pin/Promote IDs', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_posts_options(),
                'multiple'    => true,
                'label_block' => true,
                'description' => __('Select posts/products that should appear first (page 1 only). Counts toward items per page.', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'query_orderby',
            [
                'label'   => __('Order By', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    'date'          => __('Date', 'domgats-widgets-for-elementor'),
                    'title'         => __('Title', 'domgats-widgets-for-elementor'),
                    'menu_order'    => __('Menu Order', 'domgats-widgets-for-elementor'),
                    'rand'          => __('Random', 'domgats-widgets-for-elementor'),
                    'meta_value'    => __('Custom Field (Text)', 'domgats-widgets-for-elementor'),
                    'meta_value_num' => __('Custom Field (Number)', 'domgats-widgets-for-elementor'),
                ],
                'default' => 'date',
            ]
        );

        $this->add_control(
            'query_meta_key',
            [
                'label'       => __('Custom Field Key', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::TEXT,
                'condition'   => [
                    'query_orderby' => ['meta_value', 'meta_value_num'],
                ],
                'description' => __('ACF or custom meta key used for ordering or filtering.', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'query_meta_type',
            [
                'label'     => __('Meta Type', 'domgats-widgets-for-elementor'),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'CHAR'   => 'CHAR',
                    'NUMERIC' => 'NUMERIC',
                    'DATE'   => 'DATE',
                ],
                'default'   => 'CHAR',
                'condition' => [
                    'query_orderby' => ['meta_value', 'meta_value_num'],
                ],
            ]
        );

        $this->add_control(
            'query_exclude_terms',
            [
                'label'       => __('Exclude Terms', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_all_terms_options(),
                'multiple'    => true,
                'label_block' => true,
                'description' => __('Exclude these terms from the query.', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'query_include_authors',
            [
                'label'       => __('Include Authors', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_authors_options(),
                'multiple'    => true,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'query_exclude_authors',
            [
                'label'       => __('Exclude Authors', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_authors_options(),
                'multiple'    => true,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'query_current_post_only',
            [
                'label'        => __('Current Post Only', 'domgats-widgets-for-elementor'),
                'type'         => Controls_Manager::SWITCHER,
                'description'  => __('Return only the current post/page (useful for context loops).', 'domgats-widgets-for-elementor'),
                'default'      => '',
            ]
        );

        $this->add_control(
            'query_ignore_sticky',
            [
                'label'   => __('Ignore Sticky Posts', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'query_avoid_duplicates',
            [
                'label'       => __('Avoid Duplicates in Page', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SWITCHER,
                'default'     => '',
                'description' => __('Exclude posts already queried earlier on the page (uses main query globals).', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'query_order',
            [
                'label'   => __('Order', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    'DESC' => __('Descending', 'domgats-widgets-for-elementor'),
                    'ASC'  => __('Ascending', 'domgats-widgets-for-elementor'),
                ],
                'default' => 'DESC',
            ]
        );

        $this->add_control(
            'show_sorting',
            [
                'label'        => __('Show Sorting Dropdown', 'domgats-widgets-for-elementor'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Show', 'domgats-widgets-for-elementor'),
                'label_off'    => __('Hide', 'domgats-widgets-for-elementor'),
                'default'      => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Filter controls for the UI.
     */
    protected function register_filter_controls() {
        $this->start_controls_section(
            'section_filters',
            [
                'label' => __('Filter Settings', 'domgats-widgets-for-elementor'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'filter_enabled',
            [
                'label'        => __('Enable Filters', 'domgats-widgets-for-elementor'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'domgats-widgets-for-elementor'),
                'label_off'    => __('No', 'domgats-widgets-for-elementor'),
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'filter_ui',
            [
                'label'     => __('Filter UI', 'domgats-widgets-for-elementor'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'pills',
                'options'   => [
                    'dropdown'  => __('Dropdown', 'domgats-widgets-for-elementor'),
                    'checkbox'  => __('Checkboxes', 'domgats-widgets-for-elementor'),
                    'pills'     => __('Pills', 'domgats-widgets-for-elementor'),
                    'tags'      => __('Tags', 'domgats-widgets-for-elementor'),
                ],
                'condition' => [
                    'filter_enabled' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'filter_show_all',
            [
                'label'     => __('Show "All" Option', 'domgats-widgets-for-elementor'),
                'type'      => Controls_Manager::SWITCHER,
                'default'   => 'yes',
                'condition' => [
                    'filter_enabled' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'filter_default',
            [
                'label'       => __('Default Filter', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_all_terms_options(),
                'multiple'    => false,
                'label_block' => true,
                'condition'   => [
                    'filter_enabled' => 'yes',
                ],
            ]
        );

        $preset = new Repeater();
        $preset->add_control(
            'preset_label',
            [
                'label'   => __('Preset Label', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::TEXT,
                'default' => __('Preset', 'domgats-widgets-for-elementor'),
            ]
        );
        $preset->add_control(
            'preset_terms',
            [
                'label'       => __('Terms', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_all_terms_options(),
                'multiple'    => true,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'filter_presets',
            [
                'label'       => __('Preset Filter Sets', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $preset->get_controls(),
                'default'     => [],
                'title_field' => '{{{ preset_label }}}',
                'condition'   => [
                    'filter_enabled' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'filter_logic',
            [
                'label'     => __('Combination Logic', 'domgats-widgets-for-elementor'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'OR',
                'options'   => [
                    'OR'  => __('OR', 'domgats-widgets-for-elementor'),
                    'AND' => __('AND', 'domgats-widgets-for-elementor'),
                ],
                'condition' => [
                    'filter_enabled' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'filter_deep_link',
            [
                'label'     => __('Enable Deep Linking', 'domgats-widgets-for-elementor'),
                'type'      => Controls_Manager::SWITCHER,
                'default'   => 'yes',
                'description' => __('Persist filters in the URL query string.', 'domgats-widgets-for-elementor'),
                'condition' => [
                    'filter_enabled' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'meta_filter_enable',
            [
                'label'     => __('Enable ACF/Meta Filter', 'domgats-widgets-for-elementor'),
                'type'      => Controls_Manager::SWITCHER,
                'default'   => '',
                'description' => __('Adds a secondary filter sourced from a meta/ACF field.', 'domgats-widgets-for-elementor'),
                'condition' => [
                    'filter_enabled' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'meta_filter_key',
            [
                'label'       => __('Meta Key (ACF field name)', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::TEXT,
                'condition'   => [
                    'meta_filter_enable' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'meta_filter_ui',
            [
                'label'     => __('Meta Filter UI', 'domgats-widgets-for-elementor'),
                'type'      => Controls_Manager::SELECT,
                'default'   => 'dropdown',
                'options'   => [
                    'dropdown'  => __('Dropdown', 'domgats-widgets-for-elementor'),
                    'checkbox'  => __('Checkboxes', 'domgats-widgets-for-elementor'),
                    'pills'     => __('Pills', 'domgats-widgets-for-elementor'),
                    'tags'      => __('Tags', 'domgats-widgets-for-elementor'),
                ],
                'condition' => [
                    'meta_filter_enable' => 'yes',
                ],
            ]
        );

        $meta_repeater = new Repeater();
        $meta_repeater->add_control(
            'meta_option_label',
            [
                'label'   => __('Label', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::TEXT,
                'default' => __('Option', 'domgats-widgets-for-elementor'),
            ]
        );
        $meta_repeater->add_control(
            'meta_option_value',
            [
                'label'   => __('Meta Value', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::TEXT,
                'default' => '',
            ]
        );

        $this->add_control(
            'meta_filter_options',
            [
                'label'       => __('Meta Filter Options', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $meta_repeater->get_controls(),
                'default'     => [],
                'title_field' => '{{{ meta_option_label }}}',
                'condition'   => [
                    'meta_filter_enable' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Layout controls.
     */
    protected function register_layout_controls() {
        $this->start_controls_section(
            'section_layout',
            [
                'label' => __('Layout', 'domgats-widgets-for-elementor'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'layout_type',
            [
                'label'   => __('Layout Type', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid'    => __('Grid', 'domgats-widgets-for-elementor'),
                    'masonry' => __('Masonry', 'domgats-widgets-for-elementor'),
                    'slider'  => __('Slider', 'domgats-widgets-for-elementor'),
                ],
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label'          => __('Columns', 'domgats-widgets-for-elementor'),
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
                'condition'      => [
                    'layout_type!' => 'slider',
                ],
            ]
        );

        $this->add_responsive_control(
            'grid_gap',
            [
                'label'      => __('Gap', 'domgats-widgets-for-elementor'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
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
                    '{{WRAPPER}} .domgats-masonry' => 'column-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'equal_height',
            [
                'label'        => __('Equal Height Cards', 'domgats-widgets-for-elementor'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'description'  => __('Normalize card heights for tidy grids.', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'animation_style',
            [
                'label'   => __('Item Animation', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'fade',
                'options' => [
                    'none'      => __('None', 'domgats-widgets-for-elementor'),
                    'fade'      => __('Fade In', 'domgats-widgets-for-elementor'),
                    'slide-up'  => __('Slide Up', 'domgats-widgets-for-elementor'),
                    'slide-right' => __('Slide Right', 'domgats-widgets-for-elementor'),
                    'zoom-in'   => __('Zoom In', 'domgats-widgets-for-elementor'),
                ],
            ]
        );

        $this->add_control(
            'animation_stagger',
            [
                'label'       => __('Animation Stagger (ms)', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 80,
                'min'         => 0,
                'description' => __('Delay between items for cascade effects.', 'domgats-widgets-for-elementor'),
                'condition'   => [
                    'animation_style!' => 'none',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Pagination controls.
     */
    protected function register_pagination_controls() {
        $this->start_controls_section(
            'section_pagination',
            [
                'label' => __('Pagination & Loading', 'domgats-widgets-for-elementor'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'pagination_type',
            [
                'label'   => __('Pagination Type', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'numbers',
                'options' => [
                    'numbers' => __('Numbered Pagination', 'domgats-widgets-for-elementor'),
                    'load_more' => __('Load More Button', 'domgats-widgets-for-elementor'),
                    'infinite' => __('Infinite Scroll', 'domgats-widgets-for-elementor'),
                ],
            ]
        );

        $this->add_control(
            'items_per_load',
            [
                'label'       => __('Items Per Load (AJAX)', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 6,
                'min'         => 1,
                'max'         => 50,
                'description' => __('Used for load more and infinite scroll.', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'loading_animation',
            [
                'label'   => __('Loading Animation', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'spinner',
                'options' => [
                    'spinner' => __('Spinner', 'domgats-widgets-for-elementor'),
                    'fade'    => __('Fade Placeholder', 'domgats-widgets-for-elementor'),
                    'none'    => __('None', 'domgats-widgets-for-elementor'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * CTA controls.
     */
    protected function register_cta_controls() {
        $this->start_controls_section(
            'section_cta',
            [
                'label' => __('Call to Action Buttons', 'domgats-widgets-for-elementor'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'cta_text',
            [
                'label'   => __('Button Text', 'domgats-widgets-for-elementor'),
                'type'    => Controls_Manager::TEXT,
                'default' => __('Learn More', 'domgats-widgets-for-elementor'),
            ]
        );

        $repeater->add_control(
            'cta_link',
            [
                'label'         => __('Link', 'domgats-widgets-for-elementor'),
                'type'          => Controls_Manager::URL,
                'placeholder'   => __('https://your-link.com', 'domgats-widgets-for-elementor'),
                'show_external' => true,
            ]
        );

        $repeater->add_control(
            'cta_icon',
            [
                'label' => __('Icon', 'domgats-widgets-for-elementor'),
                'type'  => Controls_Manager::ICONS,
            ]
        );

        $repeater->add_control(
            'cta_visibility_logged_in',
            [
                'label'     => __('Only show when logged in', 'domgats-widgets-for-elementor'),
                'type'      => Controls_Manager::SWITCHER,
                'default'   => '',
            ]
        );

        $this->add_control(
            'cta_buttons',
            [
                'label'       => __('Buttons', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [],
                'title_field' => '{{{ cta_text }}}',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Template selection controls.
     */
    protected function register_template_controls() {
        $this->start_controls_section(
            'section_templates',
            [
                'label' => __('Templates', 'domgats-widgets-for-elementor'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'use_loop_template',
            [
                'label'        => __('Use Elementor Template', 'domgats-widgets-for-elementor'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'domgats-widgets-for-elementor'),
                'label_off'    => __('No', 'domgats-widgets-for-elementor'),
                'default'      => '',
                'description'  => __('Renders each card using the selected Elementor template.', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'loop_template_id',
            [
                'label'     => __('Loop Template', 'domgats-widgets-for-elementor'),
                'type'      => Controls_Manager::SELECT,
                'options'   => $this->get_elementor_templates(),
                'condition' => [
                    'use_loop_template' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * No results controls.
     */
    protected function register_no_results_controls() {
        $this->start_controls_section(
            'section_no_results',
            [
                'label' => __('No Results', 'domgats-widgets-for-elementor'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'no_results_message',
            [
                'label'       => __('Message', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::TEXT,
                'default'     => __('No results found.', 'domgats-widgets-for-elementor'),
            ]
        );

        $this->add_control(
            'no_results_template',
            [
                'label'     => __('Elementor Template', 'domgats-widgets-for-elementor'),
                'type'      => Controls_Manager::SELECT,
                'options'   => $this->get_elementor_templates(),
                'default'   => '0',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * External data controls.
     */
    protected function register_external_controls() {
        $this->start_controls_section(
            'section_external',
            [
                'label' => __('External Data', 'domgats-widgets-for-elementor'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'external_enable',
            [
                'label'        => __('Enable External Data', 'domgats-widgets-for-elementor'),
                'type'         => Controls_Manager::SWITCHER,
                'description'  => __('Pull items from a JSON/REST endpoint instead of WordPress posts.', 'domgats-widgets-for-elementor'),
                'default'      => '',
            ]
        );

        $this->add_control(
            'external_url',
            [
                'label'       => __('Endpoint URL', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::URL,
                'placeholder' => __('https://api.example.com/items', 'domgats-widgets-for-elementor'),
                'condition'   => [
                    'external_enable' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'external_root',
            [
                'label'       => __('Items Path', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::TEXT,
                'default'     => '',
                'description' => __('Optional dot-notation path to the list within the JSON (e.g. data.items).', 'domgats-widgets-for-elementor'),
                'condition'   => [
                    'external_enable' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Visibility and conditional logic.
     */
    protected function register_advanced_controls() {
        $this->start_controls_section(
            'section_visibility',
            [
                'label' => __('Conditional Logic', 'domgats-widgets-for-elementor'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'visibility_logged_in_only',
            [
                'label'        => __('Show only to logged-in users', 'domgats-widgets-for-elementor'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => '',
            ]
        );

        $roles = wp_roles()->get_names();

        $this->add_control(
            'visibility_roles',
            [
                'label'       => __('Allowed Roles', 'domgats-widgets-for-elementor'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $roles,
                'multiple'    => true,
                'label_block' => true,
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Style controls.
     */
    protected function register_style_controls() {
        $this->start_controls_section(
            'section_card_style',
            [
                'label' => __('Card', 'domgats-widgets-for-elementor'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'card_background',
                'selector' => '{{WRAPPER}} .domgats-card',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .domgats-card',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'card_shadow',
                'selector' => '{{WRAPPER}} .domgats-card',
            ]
        );

        $this->add_responsive_control(
            'card_padding',
            [
                'label'      => __('Card Padding', 'domgats-widgets-for-elementor'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .domgats-card__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'title_typography',
                'label'    => __('Title Typography', 'domgats-widgets-for-elementor'),
                'selector' => '{{WRAPPER}} .domgats-card__title',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'meta_typography',
                'label'    => __('Meta Typography', 'domgats-widgets-for-elementor'),
                'selector' => '{{WRAPPER}} .domgats-card__meta',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_filter_style',
            [
                'label' => __('Filter Bar', 'domgats-widgets-for-elementor'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'filter_typography',
                'selector' => '{{WRAPPER}} .domgats-filter',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'filter_border',
                'selector' => '{{WRAPPER}} .domgats-filter',
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

        if (! $this->passes_visibility_rules($settings)) {
            return;
        }

        $filters = $this->get_current_filters($settings);

        $initial = $this->build_items($settings, $filters, 1);

        $animation_class = $this->get_animation_class($settings['animation_style'] ?? 'none');

        $widget_id = 'domgats-grid-' . $this->get_id();

        $config = [
            'widgetId'        => $widget_id,
            'restUrl'         => esc_url_raw(rest_url(Rest_Controller::REST_NAMESPACE . '/grid')),
            'nonce'           => wp_create_nonce('wp_rest'),
            'paginationType'  => $settings['pagination_type'],
            'layout'          => $settings['layout_type'],
            'animation'       => $settings['animation_style'],
            'stagger'         => isset($settings['animation_stagger']) ? (int) $settings['animation_stagger'] : 80,
            'deepLink'        => ('yes' === ($settings['filter_deep_link'] ?? '')),
            'settings'        => $this->prepare_client_settings($settings),
            'filters'         => $filters,
        ];

        echo '<div class="domgats-widget-wrapper" data-domgats-grid data-widget-id="' . esc_attr($widget_id) . '" data-config="' . esc_attr(wp_json_encode($config)) . '">';

        $this->render_filter_bar($settings, $filters);

        echo '<div class="domgats-loading" aria-live="polite" aria-busy="true"><div class="domgats-spinner" aria-hidden="true"></div></div>';

        $this->render_grid($settings, $initial['items'], $animation_class);

        $this->render_pagination($settings, $initial['page'], $initial['max_pages']);

        echo '</div>';
    }

    /**
     * AJAX handler entrypoint.
     *
     * @param array $settings Settings sent from frontend.
     * @param array $filters  Filters applied by user.
     * @param int   $page     Page number.
     *
     * @return array
     */
    public function handle_ajax_query(array $settings, array $filters, $page = 1) {
        // Element IDs are not guaranteed in AJAX; ensure consistent defaults.
        $settings = wp_parse_args(
            $settings,
            [
                'pagination_type' => 'numbers',
                'layout_type'     => 'grid',
                'animation_style' => 'fade',
            ]
        );

        $items = $this->build_items($settings, $filters, $page);

        ob_start();
        $animation_class = $this->get_animation_class($settings['animation_style'] ?? 'none');
        $this->render_grid($settings, $items['items'], $animation_class, false);
        $html = ob_get_clean();

        ob_start();
        $this->render_pagination($settings, $items['page'], $items['max_pages']);
        $pagination = ob_get_clean();

        return [
            'html'        => $html,
            'pagination'  => $pagination,
            'total'       => $items['total'],
            'max_pages'   => $items['max_pages'],
            'page'        => $items['page'],
        ];
    }

    /**
     * Render filter bar.
     *
     * @param array $settings Widget settings.
     * @param array $filters  Current filters.
     */
    private function render_filter_bar(array $settings, array $filters) {
        if ('yes' !== ($settings['filter_enabled'] ?? '')) {
            return;
        }

        $taxonomy = $settings['query_taxonomy'] ?? 'category';
        $terms    = $this->get_terms_for_taxonomy($taxonomy);

        echo '<div class="domgats-filter-bar" role="toolbar" aria-label="' . esc_attr__('Content filters', 'domgats-widgets-for-elementor') . '">';

        if (! empty($settings['filter_presets']) && is_array($settings['filter_presets'])) {
            echo '<div class="domgats-filter domgats-filter--presets" role="list">';
            foreach ($settings['filter_presets'] as $preset) {
                $label = $preset['preset_label'] ?? '';
                $terms = isset($preset['preset_terms']) ? array_filter((array) $preset['preset_terms']) : [];
                $data  = $terms ? implode(',', array_map('absint', $terms)) : '';
                if (! $label) {
                    continue;
                }
                echo '<button type="button" class="domgats-filter__item" data-filter-preset="' . esc_attr($data) . '" role="listitem">' . esc_html($label) . '</button>';
            }
            echo '</div>';
        }

        if ($terms) {
            $ui_type       = $settings['filter_ui'];
            $current_terms = isset($filters['terms']) ? (array) $filters['terms'] : [];
            $show_all      = 'yes' === ($settings['filter_show_all'] ?? '');
            $default       = $settings['filter_default'] ?? '';

            if ('dropdown' === $ui_type) {
                echo '<label class="domgats-filter domgats-filter--dropdown">';
                echo '<span class="screen-reader-text">' . esc_html__('Filter content', 'domgats-widgets-for-elementor') . '</span>';
                echo '<select data-filter-control="terms" aria-label="' . esc_attr__('Filter content', 'domgats-widgets-for-elementor') . '">';

                if ($show_all) {
                    echo '<option value="">' . esc_html__('All', 'domgats-widgets-for-elementor') . '</option>';
                }

                foreach ($terms as $term_id => $label) {
                    $selected = (in_array((string) $term_id, $current_terms, true) || (empty($current_terms) && (string) $default === (string) $term_id)) ? 'selected' : '';
                    echo '<option value="' . esc_attr($term_id) . '" ' . $selected . '>' . esc_html($label) . '</option>';
                }

                echo '</select>';
                echo '</label>';
            } else {
                echo '<div class="domgats-filter domgats-filter--' . esc_attr($ui_type) . '" role="list">';
                if ($show_all) {
                    $active = empty($current_terms) ? 'is-active' : '';
                    echo '<button type="button" class="domgats-filter__item ' . esc_attr($active) . '" data-filter-click="terms" data-term="" role="listitem">' . esc_html__('All', 'domgats-widgets-for-elementor') . '</button>';
                }
                foreach ($terms as $term_id => $label) {
                    $is_active = (in_array((string) $term_id, $current_terms, true) || (empty($current_terms) && (string) $default === (string) $term_id));
                    $class     = $is_active ? 'is-active' : '';
                    $attr      = ('checkbox' === $ui_type) ? 'data-multi="1"' : '';
                    echo '<button type="button" class="domgats-filter__item ' . esc_attr($class) . '" data-filter-click="terms" data-term="' . esc_attr($term_id) . '" ' . $attr . ' role="listitem">';
                    echo esc_html($label);
                    echo '</button>';
                }
                echo '</div>';
            }
        }

        if ('yes' === ($settings['meta_filter_enable'] ?? '') && ! empty($settings['meta_filter_options']) && ! empty($settings['meta_filter_key'])) {
            $ui_type        = $settings['meta_filter_ui'] ?? 'dropdown';
            $current_values = isset($filters['meta']) ? (array) $filters['meta'] : [];
            $show_all       = 'yes' === ($settings['filter_show_all'] ?? '');
            echo '<div class="domgats-filter-group" role="group" aria-label="' . esc_attr__('Meta filters', 'domgats-widgets-for-elementor') . '">';

            if ('dropdown' === $ui_type) {
                echo '<label class="domgats-filter domgats-filter--dropdown">';
                echo '<span class="screen-reader-text">' . esc_html__('Meta filter', 'domgats-widgets-for-elementor') . '</span>';
                echo '<select data-filter-control="meta" aria-label="' . esc_attr__('Meta filter', 'domgats-widgets-for-elementor') . '">';
                if ($show_all) {
                    echo '<option value="">' . esc_html__('All', 'domgats-widgets-for-elementor') . '</option>';
                }
                foreach ($settings['meta_filter_options'] as $option) {
                    $val      = $option['meta_option_value'] ?? '';
                    $label    = $option['meta_option_label'] ?? $val;
                    $selected = in_array($val, $current_values, true) ? 'selected' : '';
                    echo '<option value="' . esc_attr($val) . '" ' . $selected . '>' . esc_html($label) . '</option>';
                }
                echo '</select>';
                echo '</label>';
            } else {
                echo '<div class="domgats-filter domgats-filter--' . esc_attr($ui_type) . '" role="list">';
                if ($show_all) {
                    $active = empty($current_values) ? 'is-active' : '';
                    echo '<button type="button" class="domgats-filter__item ' . esc_attr($active) . '" data-filter-click="meta" data-term="" role="listitem">' . esc_html__('All', 'domgats-widgets-for-elementor') . '</button>';
                }
                foreach ($settings['meta_filter_options'] as $option) {
                    $val      = $option['meta_option_value'] ?? '';
                    $label    = $option['meta_option_label'] ?? $val;
                    $is_active = in_array($val, $current_values, true);
                    $class     = $is_active ? 'is-active' : '';
                    $attr      = ('checkbox' === $ui_type) ? 'data-multi="1"' : '';
                    echo '<button type="button" class="domgats-filter__item ' . esc_attr($class) . '" data-filter-click="meta" data-term="' . esc_attr($val) . '" ' . $attr . ' role="listitem">';
                    echo esc_html($label);
                    echo '</button>';
                }
                echo '</div>';
            }

            echo '</div>';
        }

        if ('yes' === ($settings['show_sorting'] ?? 'no')) {
            echo '<div class="domgats-sort">';
            echo '<label>';
            echo '<span class="screen-reader-text">' . esc_html__('Sort content', 'domgats-widgets-for-elementor') . '</span>';
            echo '<select data-sort aria-label="' . esc_attr__('Sort content', 'domgats-widgets-for-elementor') . '">';

            $current_orderby = $settings['query_orderby'] ?? 'date';
            $current_order   = $settings['query_order'] ?? 'DESC';

            $options = [
                'date|DESC'       => __('Date: Newest', 'domgats-widgets-for-elementor'),
                'date|ASC'        => __('Date: Oldest', 'domgats-widgets-for-elementor'),
                'title|ASC'       => __('Title: A → Z', 'domgats-widgets-for-elementor'),
                'title|DESC'      => __('Title: Z → A', 'domgats-widgets-for-elementor'),
                'menu_order|ASC'  => __('Menu Order', 'domgats-widgets-for-elementor'),
                'rand|DESC'       => __('Random', 'domgats-widgets-for-elementor'),
            ];

            foreach ($options as $value => $label) {
                list($orderby, $order) = explode('|', $value);
                $selected                = ($orderby === $current_orderby && $order === $current_order) ? 'selected' : '';
                echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }

            echo '</select>';
            echo '</label>';
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Render grid wrapper.
     *
     * @param array  $settings Widget settings.
     * @param array  $items    Items to render.
     * @param string $animation_class Animation class to add per card.
     */
    private function render_grid(array $settings, array $items, $animation_class, $wrap = true) {
        $layout = $settings['layout_type'];
        $classes = ['domgats-grid'];

        if ('masonry' === $layout) {
            $classes[] = 'domgats-masonry';
        }

        if ('slider' === $layout) {
            $classes[] = 'domgats-slider';
        }

        if ('yes' === ($settings['equal_height'] ?? 'yes')) {
            $classes[] = 'is-equal-height';
        }

        $wrapper_class = implode(' ', $classes);

        if ($wrap) {
            echo '<div class="' . esc_attr($wrapper_class) . '" data-layout="' . esc_attr($layout) . '" role="list">';
        }

        if (empty($items)) {
            $this->render_empty_state($settings);
            if ($wrap) {
                echo '</div>';
            }
            return;
        }

        foreach ($items as $index => $item) {
            $delay = isset($settings['animation_stagger']) ? ((int) $settings['animation_stagger'] * $index) : 0;
            echo '<article class="domgats-card ' . esc_attr($animation_class) . '" data-animate-delay="' . esc_attr($delay) . '" role="listitem">';
            $this->render_card_contents($settings, $item);
            echo '</article>';
        }

        if ($wrap) {
            echo '</div>';
        }
    }

    /**
     * Render pagination UI.
     *
     * @param array $settings Widget settings.
     * @param int   $page     Current page.
     * @param int   $max      Max pages.
     */
    private function render_pagination(array $settings, $page, $max) {
        $type = $settings['pagination_type'];

        if ($max <= 1) {
            return;
        }

        echo '<div class="domgats-pagination" data-pagination="' . esc_attr($type) . '" data-page="' . esc_attr($page) . '" data-max="' . esc_attr($max) . '">';

        if ('numbers' === $type) {
            for ($i = 1; $i <= $max; $i++) {
                $active = $i === (int) $page ? 'is-active' : '';
                echo '<button type="button" class="domgats-page ' . esc_attr($active) . '" data-page="' . esc_attr($i) . '">' . esc_html($i) . '</button>';
            }
        }

        if ('load_more' === $type) {
            $next = ($page < $max) ? $page + 1 : 0;
            if ($next) {
                echo '<button type="button" class="domgats-load-more" data-next="' . esc_attr($next) . '">' . esc_html__('Load More', 'domgats-widgets-for-elementor') . '</button>';
            }
        }

        if ('infinite' === $type) {
            $next = ($page < $max) ? $page + 1 : 0;
            if ($next) {
                echo '<div class="domgats-infinite-sentinel" data-next="' . esc_attr($next) . '" aria-hidden="true"></div>';
            }
        }

        echo '</div>';
    }

    /**
     * Render a single card.
     *
     * @param array $settings Widget settings.
     * @param array $item     Item data.
     */
    private function render_card_contents(array $settings, array $item) {
        $use_template = 'yes' === ($settings['use_loop_template'] ?? '') && ! empty($settings['loop_template_id']);

        if ($use_template && isset($item['post'])) {
            // Render Elementor template with current post context.
            $post = get_post($item['post']);
            if ($post) {
                setup_postdata($post);
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($settings['loop_template_id']);
                wp_reset_postdata();
                return;
            }
        }

        if (! empty($item['image'])) {
            echo '<a class="domgats-card__thumb" href="' . esc_url($item['link']) . '"><img src="' . esc_url($item['image']) . '" alt="' . esc_attr($item['title']) . '" loading="lazy"></a>';
        }

        echo '<div class="domgats-card__body">';
        echo '<h3 class="domgats-card__title"><a href="' . esc_url($item['link']) . '">' . esc_html($item['title']) . '</a></h3>';
        if (! empty($item['meta'])) {
            echo '<div class="domgats-card__meta">' . esc_html($item['meta']) . '</div>';
        }
        if (! empty($item['excerpt'])) {
            echo '<p class="domgats-card__excerpt">' . esc_html($item['excerpt']) . '</p>';
        }

        $this->render_card_ctas($settings);

        echo '</div>';
    }

    /**
     * Render CTA buttons within a card.
     *
     * @param array $settings Widget settings.
     */
    private function render_card_ctas(array $settings) {
        if (empty($settings['cta_buttons']) || ! is_array($settings['cta_buttons'])) {
            return;
        }

        echo '<div class="domgats-card__cta">';
        $count = 0;

        foreach ($settings['cta_buttons'] as $button) {
            if ($count >= 3) {
                break; // Hard limit per spec.
            }

            if (! empty($button['cta_visibility_logged_in']) && ! is_user_logged_in()) {
                continue;
            }

            if (empty($button['cta_text'])) {
                continue;
            }

            $url          = $button['cta_link']['url'] ?? '#';
            $target_attr  = ! empty($button['cta_link']['is_external']) ? ' target="_blank"' : '';
            $rel_values   = [];
            if (! empty($button['cta_link']['is_external'])) {
                $rel_values[] = 'noreferrer';
                $rel_values[] = 'noopener';
            }
            if (! empty($button['cta_link']['nofollow'])) {
                $rel_values[] = 'nofollow';
            }
            $rel_attr = $rel_values ? ' rel="' . esc_attr(implode(' ', array_unique($rel_values))) . '"' : '';

            echo '<a class="domgats-button" href="' . esc_url($url) . '"' . $target_attr . $rel_attr . '>';
            if (! empty($button['cta_icon']['value'])) {
                Icons_Manager::render_icon($button['cta_icon'], ['aria-hidden' => 'true', 'class' => 'domgats-button__icon']);
            }
            echo '<span>' . esc_html($button['cta_text']) . '</span>';
            echo '</a>';

            $count++;
        }
        echo '</div>';
    }

    /**
     * Render empty state or template.
     *
     * @param array $settings Widget settings.
     */
    private function render_empty_state(array $settings) {
        if (! empty($settings['no_results_template']) && '0' !== $settings['no_results_template']) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($settings['no_results_template']);
            return;
        }

        echo '<div class="domgats-card domgats-card--empty" role="listitem"><p>' . esc_html($settings['no_results_message']) . '</p></div>';
    }

    /**
     * Build items for rendering.
     *
     * @param array $settings Widget settings.
     * @param array $filters  Filters.
     * @param int   $page     Page.
     *
     * @return array
     */
    private function build_items(array $settings, array $filters, $page) {
        if (! empty($settings['external_enable']) && ! empty($settings['external_url']['url'])) {
            return $this->build_external_items($settings, $page);
        }

        if (! empty($settings['query_current_post_only']) && get_the_ID()) {
            return [
                'items'     => [$this->normalize_post_item(get_the_ID())],
                'total'     => 1,
                'max_pages' => 1,
                'page'      => 1,
            ];
        }

        $pinned_ids = $this->parse_ids_field($settings['query_pinned_ids'] ?? []);

        $per_page = ('numbers' === ($settings['pagination_type'] ?? 'numbers'))
            ? (int) ($settings['query_posts_per_page'] ?? 6)
            : (int) ($settings['items_per_load'] ?? ($settings['query_posts_per_page'] ?? 6));

        $pinned_items = [];
        if ($pinned_ids && 1 === (int) $page) {
            $pinned_query = new WP_Query(
                [
                    'post__in'  => $pinned_ids,
                    'post_type' => $settings['query_post_type'] ?? 'post',
                    'post_status' => 'publish',
                    'orderby'   => 'post__in',
                    'posts_per_page' => count($pinned_ids),
                ]
            );
            if ($pinned_query->have_posts()) {
                while ($pinned_query->have_posts()) {
                    $pinned_query->the_post();
                    $pinned_items[] = $this->normalize_post_item();
                }
                wp_reset_postdata();
            }
        }

        $args = $this->build_query_args($settings, $filters, $page);

        if ($pinned_items && 1 === (int) $page) {
            $args['posts_per_page'] = max(0, ((int) $args['posts_per_page']) - count($pinned_items));
            $args['post__not_in']   = array_unique(
                array_merge(
                    $args['post__not_in'] ?? [],
                    $pinned_ids
                )
            );
        }

        $query = $args['posts_per_page'] > 0 ? new WP_Query($args) : null;

        $items = $pinned_items;

        if ($query && $query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $items[] = $this->normalize_post_item();
            }
            wp_reset_postdata();
        }

        $found_posts = ($query instanceof WP_Query) ? (int) $query->found_posts : 0;
        $max_pages   = ($query instanceof WP_Query) ? (int) $query->max_num_pages : 1;

        $total = $found_posts + count($pinned_items);

        return [
            'items'     => $items,
            'total'     => $total,
            'max_pages' => $max_pages,
            'page'      => (int) $page,
        ];
    }

    /**
     * Normalize WP post into item array.
     *
     * @return array
     */
    private function normalize_post_item($post_id = null) {
        $post_id = $post_id ? absint($post_id) : get_the_ID();

        if (! $post_id) {
            return [];
        }

        $image = '';
        if (has_post_thumbnail($post_id)) {
            $image = get_the_post_thumbnail_url($post_id, 'large');
        }

        return [
            'post'    => $post_id,
            'title'   => get_the_title($post_id),
            'link'    => get_permalink($post_id),
            'excerpt' => wp_trim_words(get_the_excerpt($post_id), 22),
            'meta'    => get_the_date('', $post_id),
            'image'   => $image,
        ];
    }

    /**
     * Build query arguments for WP_Query.
     *
     * @param array $settings Widget settings.
     * @param array $filters  Filters applied.
     * @param int   $page     Current page.
     *
     * @return array
     */
    private function build_query_args(array $settings, array $filters, $page) {
        $post_type  = $settings['query_post_type'] ?? (class_exists('WooCommerce') ? 'product' : 'post');
        if (empty($post_type) || ! post_type_exists($post_type)) {
            $post_type = class_exists('WooCommerce') ? 'product' : 'post';
        }
        $per_page   = ('numbers' === ($settings['pagination_type'] ?? 'numbers'))
            ? (int) ($settings['query_posts_per_page'] ?? 6)
            : (int) ($settings['items_per_load'] ?? ($settings['query_posts_per_page'] ?? 6));
        $orderby    = $settings['query_orderby'] ?? 'date';
        $order      = $settings['query_order'] ?? 'DESC';
        $meta_key   = $settings['query_meta_key'] ?? '';
        $offset     = isset($settings['query_offset']) ? (int) $settings['query_offset'] : 0;
        $taxonomy   = $settings['query_taxonomy'] ?? '';

        // Default to product_cat when querying products without a valid taxonomy.
        if ('product' === $post_type && (empty($taxonomy) || ! taxonomy_exists($taxonomy))) {
            $taxonomy = 'product_cat';
        }
        if ($taxonomy && ! taxonomy_exists($taxonomy)) {
            $taxonomy = '';
        }

        $args = [
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'paged'          => max(1, (int) $page),
            'posts_per_page' => $per_page,
            'orderby'        => $orderby,
            'order'          => $order,
            'offset'         => $offset,
            'ignore_sticky_posts' => ('yes' === ($settings['query_ignore_sticky'] ?? 'yes')),
        ];

        // Avoid rendering the current page inside its own grid.
        if (get_the_ID()) {
            $args['post__not_in'] = array_unique(array_merge($args['post__not_in'] ?? [], [get_the_ID()]));
        }

        if ('meta_value' === $orderby || 'meta_value_num' === $orderby) {
            $args['meta_key'] = $meta_key;
            if (! empty($settings['query_meta_type'])) {
                $args['meta_type'] = $settings['query_meta_type'];
            }
        }

        $include_ids = $this->parse_ids_field($settings['query_include_ids'] ?? []);
        if ($include_ids) {
            $args['post__in'] = $include_ids;
        }

        $exclude_ids = $this->parse_ids_field($settings['query_exclude_ids'] ?? []);
        if ($exclude_ids) {
            $args['post__not_in'] = array_unique(array_merge($args['post__not_in'] ?? [], $exclude_ids));
        }

        $tax_query = [];
        $terms     = $settings['query_terms'] ?? [];

        if (! empty($terms) && $taxonomy) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => array_map('absint', (array) $terms),
            ];
        }

        $filter_terms = array_filter(array_map('absint', (array) ($filters['terms'] ?? [])));

        if (! empty($filter_terms) && $taxonomy) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $filter_terms,
                'operator' => ('AND' === ($settings['filter_logic'] ?? 'OR')) ? 'AND' : 'IN',
            ];
        }

        $exclude_terms = $settings['query_exclude_terms'] ?? [];
        if (! empty($exclude_terms) && $taxonomy) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => array_map('absint', (array) $exclude_terms),
                'operator' => 'NOT IN',
            ];
        }

        if ($tax_query) {
            if (count($tax_query) > 1) {
                $args['tax_query'] = array_merge(
                    ['relation' => 'AND'],
                    $tax_query
                );
            } else {
                $args['tax_query'] = $tax_query;
            }
        }

        $meta_query = [];

        if ('yes' === ($settings['meta_filter_enable'] ?? '') && ! empty($settings['meta_filter_key']) && ! empty($filters['meta'])) {
            $values = array_filter((array) $filters['meta']);
            if ($values) {
                if ('AND' === ($settings['filter_logic'] ?? 'OR')) {
                    foreach ($values as $meta_val) {
                        $meta_query[] = [
                            'key'     => $settings['meta_filter_key'],
                            'value'   => $meta_val,
                            'compare' => '=',
                        ];
                    }
                } else {
                    $meta_query[] = [
                        'key'     => $settings['meta_filter_key'],
                        'value'   => $values,
                        'compare' => 'IN',
                    ];
                }
            }
        }

        if ($meta_query) {
            if (count($meta_query) > 1) {
                $args['meta_query'] = array_merge(['relation' => 'AND'], $meta_query);
            } else {
                $args['meta_query'] = $meta_query;
            }
        }

        if (! empty($settings['query_include_authors'])) {
            $args['author__in'] = array_filter(array_map('absint', (array) $settings['query_include_authors']));
        }

        if (! empty($settings['query_exclude_authors'])) {
            $args['author__not_in'] = array_filter(array_map('absint', (array) $settings['query_exclude_authors']));
        }

        if ('yes' === ($settings['query_avoid_duplicates'] ?? '')) {
            global $wp_query;
            if ($wp_query instanceof \WP_Query && ! empty($wp_query->posts)) {
                $seen = wp_list_pluck($wp_query->posts, 'ID');
                $args['post__not_in'] = array_unique(array_merge($args['post__not_in'] ?? [], $seen));
            }
        }

        /**
         * Allow developers to modify query args.
         *
         * @since 1.0.0
         */
        return apply_filters('domgats/widgets/query_args', $args, $settings, $filters);
    }

    /**
     * Build items from external JSON.
     *
     * @param array $settings Settings.
     * @param int   $page     Page number.
     *
     * @return array
     */
    private function build_external_items(array $settings, $page) {
        $url = $settings['external_url']['url'] ?? '';
        if (! $url) {
            return [
                'items'     => [],
                'total'     => 0,
                'max_pages' => 0,
                'page'      => $page,
            ];
        }

        $response = wp_remote_get(esc_url_raw($url), ['timeout' => 10]);

        if (is_wp_error($response)) {
            return [
                'items'     => [],
                'total'     => 0,
                'max_pages' => 0,
                'page'      => $page,
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (! $data) {
            return [
                'items'     => [],
                'total'     => 0,
                'max_pages' => 0,
                'page'      => $page,
            ];
        }

        if (! empty($settings['external_root'])) {
            $path = explode('.', $settings['external_root']);
            foreach ($path as $segment) {
                if (isset($data[$segment])) {
                    $data = $data[$segment];
                }
            }
        }

        if (! is_array($data)) {
            return [
                'items'     => [],
                'total'     => 0,
                'max_pages' => 0,
                'page'      => $page,
            ];
        }

        $items      = array_map([$this, 'normalize_external_item'], $data);
        $per_page   = max(1, (int) ($settings['items_per_load'] ?? 6));
        $total      = count($items);
        $max_pages  = (int) ceil($total / $per_page);
        $offset     = ($page - 1) * $per_page;
        $items_page = array_slice($items, $offset, $per_page);

        return [
            'items'     => $items_page,
            'total'     => $total,
            'max_pages' => $max_pages,
            'page'      => $page,
        ];
    }

    /**
     * Normalize external item into expected structure.
     *
     * @param array $item Raw item.
     *
     * @return array
     */
    private function normalize_external_item($item) {
        return [
            'title'   => isset($item['title']) ? (string) $item['title'] : '',
            'link'    => isset($item['url']) ? (string) $item['url'] : '#',
            'excerpt' => isset($item['excerpt']) ? (string) $item['excerpt'] : '',
            'meta'    => isset($item['meta']) ? (string) $item['meta'] : '',
            'image'   => isset($item['image']) ? (string) $item['image'] : '',
        ];
    }

    /**
     * Get default filters for first render.
     *
     * @param array $settings Settings.
     *
     * @return array
     */
    private function get_current_filters(array $settings) {
        $filters = [
            'terms' => [],
            'meta'  => [],
        ];

        if (! empty($settings['filter_default'])) {
            $filters['terms'] = [$settings['filter_default']];
        }

        return $filters;
    }

    /**
     * Prepare subset of settings safe to send to client.
     *
     * @param array $settings Widget settings.
     *
     * @return array
     */
    private function prepare_client_settings(array $settings) {
        $keys = [
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
            'query_meta_key',
            'query_meta_type',
            'filter_enabled',
            'filter_ui',
            'filter_show_all',
            'filter_logic',
            'filter_default',
            'filter_deep_link',
            'filter_presets',
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

        $client = [];
        foreach ($keys as $key) {
            if (isset($settings[$key])) {
                $client[$key] = $settings[$key];
            }
        }

        return $client;
    }

    /**
     * Public post types.
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

        foreach ($post_types as $type => $object) {
            $options[$type] = $object->labels->singular_name;
        }

        return $options;
    }

    /**
     * Public taxonomies.
     *
     * @return array
     */
    private function get_public_taxonomies() {
        $taxonomies = get_taxonomies(
            [
                'public' => true,
            ],
            'objects'
        );

        $options = [];
        foreach ($taxonomies as $taxonomy => $object) {
            $options[$taxonomy] = $object->label;
        }

        return $options;
    }

    /**
     * Get terms for taxonomy.
     *
     * @param string $taxonomy Taxonomy name.
     *
     * @return array
     */
    private function get_terms_for_taxonomy($taxonomy) {
        if (! $taxonomy || ! taxonomy_exists($taxonomy)) {
            return [];
        }

        $terms = get_terms(
            [
                'taxonomy'   => $taxonomy,
                'hide_empty' => true,
            ]
        );

        if (is_wp_error($terms)) {
            return [];
        }

        $options = [];
        foreach ($terms as $term) {
            $options[$term->term_id] = $term->name;
        }

        return $options;
    }

    /**
     * Aggregate all terms for select controls.
     *
     * @return array
     */
    private function get_all_terms_options() {
        $taxonomies = $this->get_public_taxonomies();
        $options    = [];

        foreach (array_keys($taxonomies) as $tax) {
            $terms = $this->get_terms_for_taxonomy($tax);
            foreach ($terms as $id => $label) {
                $options[$id] = $label . ' (' . $tax . ')';
            }
        }

        return $options;
    }

    /**
     * Get authors list for selects.
     *
     * @return array
     */
    private function get_authors_options() {
        $users = get_users(
            [
                'who'    => 'authors',
                'fields' => ['ID', 'display_name'],
            ]
        );

        $options = [];
        foreach ($users as $user) {
            $options[$user->ID] = $user->display_name;
        }

        return $options;
    }

    /**
     * Parse IDs field accepting arrays or comma-separated strings.
     *
     * @param mixed $value Value from settings.
     * @return array
     */
    private function parse_ids_field($value) {
        if (is_array($value)) {
            return array_filter(array_map('absint', $value));
        }

        if (is_string($value) && '' !== trim($value)) {
            return array_filter(array_map('absint', explode(',', $value)));
        }

        return [];
    }

    /**
     * Get selectable posts/products for select controls.
     *
     * @return array
     */
    private function get_posts_options() {
        $posts = get_posts(
            [
                'post_type'      => 'any',
                'posts_per_page' => 200,
                'post_status'    => 'publish',
                'orderby'        => 'title',
                'order'          => 'ASC',
                'fields'         => 'ids',
            ]
        );

        $options = [];
        foreach ($posts as $post_id) {
            $options[$post_id] = get_the_title($post_id) . ' (#' . $post_id . ')';
        }

        return $options;
    }
}
