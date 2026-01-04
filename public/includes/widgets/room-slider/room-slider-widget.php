<?php
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\register_controls;

defined('ABSPATH') || die();
class Eshb_Room_Slider_Widget  extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *   
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */

    public function get_name()
    {
        return 'eshb-room-slider';
    }

    /**
     * Get widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */

    public function get_title()
    {
        return esc_html__('Easy Hotel Room Slider', 'easy-hotel');
    }

    /**
     * Get widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'easy-hotel-widget-icon';
    }


    public function get_categories() {
        return [ 'easy_hotel_category' ];
    }

    public function get_keywords()
    {
        return ['room_slider'];
    }
    protected function register_controls()
    {

        

        $eshb_categories = get_terms( 'eshb_category' );

        $cat_array = [];
        foreach ( $eshb_categories as $category ) {
            $cat_array[ $category->slug ] = $category->name;
        }




        $this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Content', 'easy-hotel' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
            'rt_room_slider_style',
            [
                'label'   => esc_html__('Select Style', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'style1',
                'options' => [
                    'style1' => esc_html__('Style 1', 'easy-hotel'),                   
                    'style2' => esc_html__('Style 2', 'easy-hotel'),                   
                    'style3' => esc_html__('Style 3', 'easy-hotel'),                   
                    'style4' => esc_html__('Style 4', 'easy-hotel'),                   
                ],
            ]
        );

        $this->add_control(
			'category',
			[
				'label'   => esc_html__( 'Category', 'easy-hotel' ),				
				'type'        => Controls_Manager::SELECT2,
                'options'     => $cat_array,
                'default'     => [],
				'multiple' => true,	
				'separator' => 'before',		
			]

		);
        $this->add_control(
            'show_excerpt',
            [
                'label' => esc_html__( 'Show Excerpt', 'easy-hotel' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'easy-hotel' ),
                'label_off' => esc_html__( 'Hide', 'easy-hotel' ),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'rt_room_slider_style' => 'style1'
                ],
                'separator' => 'before',
            ]
        );
        $this->add_control(
			'excerpt_length',
			[
				'label' => esc_html__( 'Excerpt Length', 'easy-hotel' ),
				'type' => Controls_Manager::TEXT,
				'default' => 30,
                'condition' => [
                    'rt_room_slider_style' => 'style1',
                    'show_excerpt' => 'yes'
                ]
			]
		);
        $this->add_control(
            'show_all_features_icons',
            [
                'label' => esc_html__( 'Show all feature icons', 'easy-hotel' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'easy-hotel' ),
                'label_off' => esc_html__( 'Hide', 'easy-hotel' ),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'rt_room_slider_style' => 'style1'
                ],
                'separator' => 'before',
            ]
        );
		$this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'thumbnail',
                'default' => 'large',
                'separator' => 'before',
                'separator' => 'before',
            ]
        );

	
		$this->add_control(
			'per_page',
			[
				'label' => esc_html__( 'Project Show Per Page', 'easy-hotel' ),
				'type' => Controls_Manager::TEXT,
				'default' => -1,
				'separator' => 'before',
			]
		);
        $this->add_control(
			'room_order',
			[
				'label'   => esc_html__( 'Order', 'easy-hotel' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ASC',				
				'options' => [
					'ASC' => 'ASC',
					'DESC' => 'DESC',
				],											
			]
		);

		$this->add_control(
			'room_orderby',
			[
				'label'   => esc_html__( 'Order By', 'easy-hotel' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',				
				'options' => array(
                    'none' => 'none',
                    'id' => 'ID',
                    'date' => 'Date',
                    'title' => 'Title',
                    'name' => 'name',
                    'menu_order' => 'Menu Order',
                    'random' => 'Random'
                  ),							
			]
		);

		$this->add_control(
			'room_offset',
			[
				'label' => esc_html__( 'Offset', 'easy-hotel' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '0',
			]
		);
        $this->add_control(
			'pricing_prefix',
			[
				'label' => esc_html__( 'Pricing Prefix', 'easy-hotel' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'From', 'easy-hotel' ),
				'placeholder' => esc_html__( 'Type your text here', 'easy-hotel' ),
			]
		);

		$this->add_control(
            'btn_content',
            [
                'label' => esc_html__('Button', 'easy-hotel'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
		$this->add_control(
			'btn_text',
			[
				'label' => esc_html__( 'Text', 'easy-hotel' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'View Details', 'easy-hotel' ),
				'placeholder' => esc_html__( 'Type your text here', 'easy-hotel' ),
			]
		);
		$this->end_controls_section();


       
        $this->start_controls_section(
            'content_slider',
            [
                'label' => esc_html__('Slider Settings', 'easy-hotel'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'col_xl',
            [
                'label'   => esc_html__('Wide Screen > 1399px', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 4,
                'options' => [
                    '1' => esc_html__('1 Column', 'easy-hotel'),
                    '2' => esc_html__('2 Column', 'easy-hotel'),
                    '3' => esc_html__('3 Column', 'easy-hotel'),
                    '4' => esc_html__('4 Column', 'easy-hotel'),
                    '4.5' => esc_html__('4.5 Column', 'easy-hotel'),
                    '5' => esc_html__('5 Column', 'easy-hotel'),
                    '6' => esc_html__('6 Column', 'easy-hotel'),
                ],
                'separator' => 'before',

            ]

        );

        $this->add_control(
            'col_lg',
            [
                'label'   => esc_html__('Desktops > 1199px', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 4,
                'options' => [
                    '1' => esc_html__('1 Column', 'easy-hotel'),
                    '2' => esc_html__('2 Column', 'easy-hotel'),
                    '3' => esc_html__('3 Column', 'easy-hotel'),
                    '4' => esc_html__('4 Column', 'easy-hotel'),
                    '5' => esc_html__('5 Column', 'easy-hotel'),
                    '6' => esc_html__('6 Column', 'easy-hotel'),
                ],
                'separator' => 'before',

            ]

        );

        $this->add_control(
            'col_md',
            [
                'label'   => esc_html__('Desktops > 991px', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 3,
                'options' => [
                    '1' => esc_html__('1 Column', 'easy-hotel'),
                    '2' => esc_html__('2 Column', 'easy-hotel'),
                    '3' => esc_html__('3 Column', 'easy-hotel'),
                    '4' => esc_html__('4 Column', 'easy-hotel'),
                    '5' => esc_html__('5 Column', 'easy-hotel'),
                    '6' => esc_html__('6 Column', 'easy-hotel'),
                ],
                'separator' => 'before',

            ]

        );

        $this->add_control(
            'col_sm',
            [
                'label'   => esc_html__('Tablets > 767px', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 2,
                'options' => [
                    '1' => esc_html__('1 Column', 'easy-hotel'),
                    '2' => esc_html__('2 Column', 'easy-hotel'),
                    '3' => esc_html__('3 Column', 'easy-hotel'),
                    '4' => esc_html__('4 Column', 'easy-hotel'),
                    '5' => esc_html__('5 Column', 'easy-hotel'),
                    '6' => esc_html__('6 Column', 'easy-hotel'),
                ],
                'separator' => 'before',

            ]

        );

        $this->add_control(
            'col_xs',
            [
                'label'   => esc_html__('Tablets < 768px', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 1,
                'options' => [
                    '1' => esc_html__('1 Column', 'easy-hotel'),
                    '2' => esc_html__('2 Column', 'easy-hotel'),
                    '3' => esc_html__('3 Column', 'easy-hotel'),
                    '4' => esc_html__('4 Column', 'easy-hotel'),
                    '5' => esc_html__('5 Column', 'easy-hotel'),
                    '6' => esc_html__('6 Column', 'easy-hotel'),
                ],
                'separator' => 'before',

            ]

        );

        $this->add_control(
            'slides_ToScroll',
            [
                'label'   => esc_html__('Slide To Scroll', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 2,
                'options' => [
                    '1' => esc_html__('1 Item', 'easy-hotel'),
                    '2' => esc_html__('2 Item', 'easy-hotel'),
                    '3' => esc_html__('3 Item', 'easy-hotel'),
                    '4' => esc_html__('4 Item', 'easy-hotel'),
                ],
                'separator' => 'before',

            ]

        );
        $this->add_control(
            'rt_pslider_effect',
            [
                'label' => esc_html__('Slider Effect', 'easy-hotel'),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => esc_html__('Default', 'easy-hotel'),
                    'fade' => esc_html__('Fade', 'easy-hotel'),
                    'flip' => esc_html__('Flip', 'easy-hotel'),
                    'cube' => esc_html__('Cube', 'easy-hotel'),
                    'coverflow' => esc_html__('Coverflow', 'easy-hotel'),
                    'creative' => esc_html__('Creative', 'easy-hotel'),
                    'cards' => esc_html__('Cards', 'easy-hotel'),
                ],
            ]
        );

        $this->add_control(
            'slider_dots',
            [
                'label'   => esc_html__('Navigation Dots', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
            ]

        );
        $this->add_control(
            'slider_dots_color',
            [
                'label' => esc_html__('Navigation Dots Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination .swiper-pagination-bullet' => 'background-color: {{VALUE}} !important;',
                ],
                'condition' => ['slider_dots' => 'true',],
            ]
        );
        $this->add_control(
			'slider_dots_opacity',
			[
				'label' => esc_html__( 'Opacity', 'easy-hotel' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination .swiper-pagination-bullet' => 'opacity: {{SIZE}}{{UNIT}};',
				],
                'condition' => ['slider_dots' => 'true',],
			]
		);
        $this->add_control(
            'slider_dots_color_active',
            [
                'label' => esc_html__('Active Navigation Dots Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .swiper-pagination .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background: {{VALUE}} !important;',
                ],
                'condition' => ['slider_dots' => 'true',],
            ]
        );

        $this->add_responsive_control(
            'slider_nav',
            [
                'label'   => esc_html__('Navigation Nav', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'pcat_nav_text_bg',
            [
                'label' => esc_html__('Nav BG Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rt_room_slider-btn-wrapper .swiper-button-prev' => 'background: {{VALUE}} !important;',
                    '{{WRAPPER}} .rt_room_slider-btn-wrapper .swiper-button-next' => 'background: {{VALUE}} !important;',
                ],
                'condition' => ['slider_nav' => 'true',],
            ]
        );
        $this->add_control(
            'pcat_nav_text_bg_hover',
            [
                'label' => esc_html__('Nav BG Hover Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rt_room_slider-btn-wrapper .swiper-button-prev:hover' => 'background: {{VALUE}} !important;',
                    '{{WRAPPER}} .rt_room_slider-btn-wrapper .swiper-button-next:hover' => 'background: {{VALUE}} !important;',
                ],
                'condition' => ['slider_nav' => 'true',],
            ]
        );
        $this->add_control(
            'pcat_nav_text_bg_icon',
            [
                'label' => esc_html__('Nav BG Icon Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rt_room_slider-btn-wrapper .swiper-button-prev' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .rt_room_slider-btn-wrapper .swiper-button-next' => 'color: {{VALUE}} !important;',
                ],
                'condition' => ['slider_nav' => 'true',],
            ]
        );
        $this->add_control(
            'pcat_nav_text_bg_hover_icon',
            [
                'label' => esc_html__('Nav BG Icon Hover Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rt_room_slider-btn-wrapper .swiper-button-prev:hover' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .rt_room_slider-btn-wrapper .swiper-button-next:hover' => 'color: {{VALUE}} !important;',
                ],
                'condition' => ['slider_nav' => 'true',],
            ]
        );

        $this->add_responsive_control(
            'nav_top_gap',
            [
                'label' => esc_html__('Navigation Top Gap', 'easy-hotel'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'show_label' => true,
                'separator' => 'before',
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-nav-btn' => 'margin-top: {{SIZE}}{{UNIT}} !important;',
                ],
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'slider_dots',
                            'operator' => '==',
                            'value' => 'true',
                        ],
                        [
                            'name' => 'slider_nav',
                            'operator' => '==',
                            'value' => 'true',
                        ],
                    ],
                ],
                
            ]
        );
        

        $this->add_control(
            'slider_autoplay',
            [
                'label'   => esc_html__('Autoplay', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'slider_autoplay_speed',
            [
                'label'   => esc_html__('Autoplay Slide Speed', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 3000,
                'options' => [
                    '1000' => esc_html__('1 Seconds', 'easy-hotel'),
                    '2000' => esc_html__('2 Seconds', 'easy-hotel'),
                    '3000' => esc_html__('3 Seconds', 'easy-hotel'),
                    '4000' => esc_html__('4 Seconds', 'easy-hotel'),
                    '5000' => esc_html__('5 Seconds', 'easy-hotel'),
                ],
                'separator' => 'before',
                'condition' => [
                    'slider_autoplay' => 'true',
                ],
            ]
        );

        $this->add_control(
            'slider_interval',
            [
                'label'   => esc_html__('Autoplay Interval', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 3000,
                'options' => [
                    '5000' => esc_html__('5 Seconds', 'easy-hotel'),
                    '4000' => esc_html__('4 Seconds', 'easy-hotel'),
                    '3000' => esc_html__('3 Seconds', 'easy-hotel'),
                    '2000' => esc_html__('2 Seconds', 'easy-hotel'),
                    '1000' => esc_html__('1 Seconds', 'easy-hotel'),
                ],
                'separator' => 'before',
                'condition' => [
                    'slider_autoplay' => 'true',
                ],
            ]
        );

        $this->add_control(
            'slider_stop_on_interaction',
            [
                'label'   => esc_html__('Stop On Interaction', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
                'condition' => [
                    'slider_autoplay' => 'true',
                ],
            ]

        );

        $this->add_control(
            'slider_stop_on_hover',
            [
                'label'   => esc_html__('Stop on Hover', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
                'condition' => [
                    'slider_autoplay' => 'true',
                ],
            ]

        );

        $this->add_control(
            'slider_loop',
            [
                'label'   => esc_html__('Loop', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'slider_centerMode',
            [
                'label'   => esc_html__('Center Mode', 'easy-hotel'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',

            ]

        );

        $this->add_responsive_control(
            'item_gap_custom',
            [
                'label' => esc_html__('Item Gap', 'easy-hotel'),
                'type' => Controls_Manager::SLIDER,
                'show_label' => true,
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 15,
                ],

                'selectors' => [
                    '{{WRAPPER}} .rs-addon-slider .grid-item' => 'padding:0 {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();

                
        $this->start_controls_section(
            'item_container_style',
            [
                'label' => esc_html__( 'Item', 'easy-hotel' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );           
        $this->add_control(
            'item_bg_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Background', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_control(
            'item_bg_overlay',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Overlay BG Color', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'item_overlay_background',
				'types' => [ 'classic', 'gradient', 'video' ],
				'selector' => '{{WRAPPER}} .eshb-item-grid .grid-item .hover-bg-two',
			]
		);
        $this->add_control(
            'item_bg_overlay_hover',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Overlay BG Hover Color', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'item_overlay_background_hover',
				'types' => [ 'classic', 'gradient', 'video' ],
				'selector' => '{{WRAPPER}} .eshb-item-grid .grid-item .hover-bg-one',
			]
		);  
        $this->add_responsive_control(
            'item_padding',
            [
                'label' => esc_html__( 'Padding', 'easy-hotel' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-item-grid .grid-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],               
            ]
        );
        $this->add_control(
			'item_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .eshb-item-grid .grid-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);	
        $this->end_controls_section();


        $this->start_controls_section(
            'room_slider_title_styles',
            [
                'label' => esc_html__('Title', 'easy-hotel'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .room_slider-title' => 'color: {{VALUE}}',
                    
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .room_slider-title',
            ]
        );
        
        $this->end_controls_section();     




        $this->start_controls_section(
            'designation_styles',
            [
                'label' => esc_html__('Sub Title', 'easy-hotel'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'subtitle_color',
            [
                'label' => esc_html__('Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .subtitle' => 'color: {{VALUE}}',                    
                ],
            ]
        );
  

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'subtitle_typography',
                'selector' => '{{WRAPPER}} .subtitle',
            ]
        );

        $this->add_responsive_control(
            'subtitle__padding',
            [
                'label' => esc_html__('Padding', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .dynamic .author-area .designation' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    '{{WRAPPER}} .dynamic .author p.disc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    '{{WRAPPER}} .dynamic .desc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    '{{WRAPPER}} .dynamic .author span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_responsive_control(
            'subtitle__margin',
            [
                'label' => esc_html__('Margin', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .dynamic .author-area .designation' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    '{{WRAPPER}} .dynamic .author p.disc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    '{{WRAPPER}} .dynamic .desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    '{{WRAPPER}} .dynamic .author span' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->end_controls_section(); 



        $this->start_controls_section(
            'des__styles',
            [
                'label' => esc_html__('Description', 'easy-hotel'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'des__color',
            [
                'label' => esc_html__('Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .room_slider-teaser' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'des__typography',
                'selector' => '{{WRAPPER}} .room_slider-teaser',
            ]
        );
       
        $this->end_controls_section();           

        $this->start_controls_section(
            'all_features_styles',
            [
                'label' => esc_html__('All Features', 'easy-hotel'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'heading_icon_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Icon', 'easy-hotel' ),
                'separator' => 'after'
            ]
        ); 
        
        $this->add_control(
            'all_features_icon_color',
            [
                'label' => esc_html__('Icon Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rt_room_slider.eshb-item-grid .all-features .feature i' => 'color: {{VALUE}}',                    
                ],
            ]
        );
        
        $this->add_responsive_control(
            'icon_size',
            [
                'label' => esc_html__( 'Icon Size', 'easy-hotel' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 400,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rt_room_slider.eshb-item-grid .all-features .feature i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],               
            ]
        );
        
        
        $this->add_control(
            'all_features_color',
            [
                'label' => esc_html__('Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rt_room_slider.eshb-item-grid .all-features .feature' => 'color: {{VALUE}}',                    
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'all_features_typography',
                'selector' => '{{WRAPPER}} .rt_room_slider.eshb-item-grid .all-features .feature',
            ]
        );
        
        $this->add_responsive_control(
            'all_features__padding',
            [
                'label' => esc_html__('Padding', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .rt_room_slider.eshb-item-grid .all-features .feature' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'all_features__margin',
            [
                'label' => esc_html__('Margin', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .rt_room_slider.eshb-item-grid .all-features .feature' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section(); 


    }

    
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $btn_text = $settings['btn_text'];
        $thumbnail_size = isset($settings['thumbnail_size']) && !empty($settings['thumbnail_size']) ? $settings['thumbnail_size'] : 'eshb_thumbnail';
        $excerpt_length = isset($settings['excerpt_length']) && !empty($settings['excerpt_length']) ? $settings['excerpt_length'] : 25;
        $pricing_prefix = isset($settings['pricing_prefix']) ? $settings['pricing_prefix'] : '';

        $col_xl          = $settings['col_xl'];
        $col_xl          = !empty($col_xl) ? $col_xl : 4;
        $slidesToShow    = $col_xl;
        $autoplaySpeed   = $settings['slider_autoplay_speed'];
        $autoplaySpeed   = !empty($autoplaySpeed) ? $autoplaySpeed : '1000';
        $interval        = $settings['slider_interval'];
        $interval        = !empty($interval) ? $interval : '3000';
        $slidesToScroll  = $settings['slides_ToScroll'];
        $room_slider_autoplay = $settings['slider_autoplay'] === 'true' ? 'true' : 'false';
        $pauseOnHover    = $settings['slider_stop_on_hover'] === 'true' ? 'true' : 'false';
        $pauseOnInter    = $settings['slider_stop_on_interaction'] === 'true' ? 'true' : 'false';
        $room_sliderDots      = $settings['slider_dots'] == 'true' ? 'true' : 'false';
        $room_sliderNav       = $settings['slider_nav'] == 'true' ? 'true' : 'false';
        $infinite        = $settings['slider_loop'] === 'true' ? 'true' : 'false';
        $centerMode      = $settings['slider_centerMode'] === 'true' ? 'true' : 'false';
        $col_lg          = $settings['col_lg'];
        $col_md          = $settings['col_md'];
        $col_sm          = $settings['col_sm'];
        $col_xs          = $settings['col_xs'];
        
        //$item_gap        = $settings['item_gap_custom']['size'];
        $item_gap        = !empty($item_gap) ? $item_gap : '30';
        $next_text       = !empty($next_text) ? $next_text : '';
        $unique          = wp_rand(2012, 35120);
     
        if ($room_slider_autoplay == 'true') {
            $room_slider_autoplay = 'autoplay: { ';
            $room_slider_autoplay .= 'delay: ' . $interval;
            if ($pauseOnHover == 'true') {
                $room_slider_autoplay .= ', pauseOnMouseEnter: true';
            } else {
                $room_slider_autoplay .= ', pauseOnMouseEnter: false';
            }
            if ($pauseOnInter == 'true') {
                $room_slider_autoplay .= ', disableOnInteraction: true';
            } else {
                $room_slider_autoplay .= ', disableOnInteraction: false';
            }
            $room_slider_autoplay .= ' }';
        } else {
            $room_slider_autoplay = 'autoplay: false';
        }

        $effect = $settings['rt_pslider_effect'];

        if ($effect == 'fade') {
            $seffect = "effect: 'fade', fadeEffect: { crossFade: true, },";
        } elseif ($effect == 'cube') {
            $seffect = "effect: 'cube',";
        } elseif ($effect == 'flip') {
            $seffect = "effect: 'flip',";
        } elseif ($effect == 'coverflow') {
            $seffect = "effect: 'coverflow',";
        } elseif ($effect == 'creative') {
            $seffect = "effect: 'creative', creativeEffect: { prev: { translate: [0, 0, -400], }, next: { translate: ['100%', 0, 0], }, },";
        } elseif ($effect == 'cards') {
            $seffect = "effect: 'cards',";
        } else {
            $seffect = '';
        }
     
        $sstyle = $settings['rt_room_slider_style'];

        $eshb_settings = get_option('eshb_settings');
        $string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';
       

        $blank = "";
        ?>

      
            <div class=" room_slider-inner-wrapper room_slider-inner-wrapper-<?php echo esc_attr($unique); ?> section-dark eshb-text-light no-top no-bottom position-relative overflow-hidden z-1000">            
              
                <div class="swiper rt_room_slider-<?php echo esc_attr($unique); ?> rt_room_slider <?php echo esc_attr( $sstyle )?> eshb-item-grid">
                    <div class="swiper-wrapper">
                        <?php
                        
                            $hotel_core = new ESHB_Core();
                            $hotel_view = new ESHB_View();

                            $eshb_settings = get_option('eshb_settings');
                            
                            

                            $cat = $settings['category'];

                            $args = array(
                                'post_type'      => 'eshb_accomodation',
                                'posts_per_page' => $settings['per_page'],
                                'orderby' 		 => $settings['room_orderby'],
                                'order' 		 => $settings['room_order'],
                                'offset' 		 => $settings['room_offset'],					
                                                        
                            );
            
                            if(!empty($cat)){
                                $args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- necessary taxonomy filter, limited query
                                    array(
                                        'taxonomy' => 'eshb_category',
                                        'field'    => 'slug', 
                                        'terms'    => $cat 
                                    ),
                                );
                            }
            
                            $best_wp = new WP_Query($args);	



                            $x = 0;
                            $animation_delay = 0.2;
                            while($best_wp->have_posts()): $best_wp->the_post();
                                $animation_delay+=0.1;
                                $x++;
                                
                                $accomodation_id = get_the_ID();
                                $price = $hotel_core->get_eshb_price_html('', '', $accomodation_id);
                                $eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
                                $accomodation_info_group = $eshb_accomodation_metaboxes['accomodation_info_group'];
                                $booking_url = get_the_permalink($accomodation_id);
                                $total_capacity = $eshb_accomodation_metaboxes['total_capacity'];
                                $price = $hotel_core->get_eshb_price_html('', '', $accomodation_id);
                                $numeric_price = $hotel_core->get_eshb_price('', '', $accomodation_id);
                                
                                $perodicity_string = apply_filters( 'eshb_perodicity_string_in_loop', $string_night, $accomodation_id, $eshb_settings);

                                $sstyle = $sstyle == 'style2' ? 'style1' : $sstyle;
                                include plugin_dir_path(__FILE__) . $sstyle.".php";
                            
                            endwhile;

                            wp_reset_postdata();
            
                        ?>
                    </div> 
                </div>
                  
           </div>
           <?php if( !empty($room_sliderDots == 'true' || $room_sliderNav == 'true') ) : ?>
                    <div class="rt_room_slider-btn-wrapper rt_room_slider-btn-wrapper-<?php echo esc_attr($unique); ?>">
                        <div class="swiper-pagination"></div>
                        <!-- If we need navigation buttons -->
                        <div class="nav-btn swiper-button-prev"></div>
                        <div class="nav-btn swiper-button-next"></div>
                        <!-- If we need scrollbar -->
                        <div class="swiper-scrollbar"></div>
                    </div>
            <?php endif; ?>         


        
        <script type="text/javascript">
            jQuery(document).ready(function() {
                var swiper<?php echo esc_attr($unique); ?><?php echo esc_attr($unique); ?> = new Swiper(".rt_room_slider-<?php echo esc_attr($unique); ?>", {
                    slidesPerView: 1,
                    <?php echo esc_attr($seffect); ?>
                    speed: <?php echo esc_attr($autoplaySpeed); ?>,
                    slidesPerGroup: 1,
                    loop: <?php echo esc_attr($infinite); ?>,
                    <?php echo esc_attr($room_slider_autoplay); ?>,
                    spaceBetween: <?php echo esc_attr($item_gap); ?>,
                    // pagination: {
                    //     el: ".swiper-pagination",
                    //     clickable: true,
                    //     type: "fraction",
                    // },
                    centeredSlides: <?php echo esc_attr($centerMode); ?>,
                    navigation: {
                        nextEl: ".rt_room_slider-btn-wrapper-<?php echo esc_attr($unique); ?> .swiper-button-next",
                        prevEl: ".rt_room_slider-btn-wrapper-<?php echo esc_attr($unique); ?> .swiper-button-prev",
                    },
                    breakpoints: {
                        <?php
                                echo (!empty($col_xs)) ?  '575: { slidesPerView: ' . esc_attr($col_xs) . ' },' : '';
                                echo (!empty($col_sm)) ?  '767: { slidesPerView: ' . esc_attr($col_sm) . ' },' : '';
                                echo (!empty($col_md)) ?  '991: { slidesPerView: ' . esc_attr($col_md) . ' },' : '';
                                echo (!empty($col_lg)) ?  '1199: { slidesPerView: ' . esc_attr($col_lg) . ' },' : '';
                                ?>
                        1399: {
                            slidesPerView: <?php echo esc_attr($col_xl); ?>,
                            spaceBetween: <?php echo esc_attr($item_gap); ?>
                        }
                    }
                });
            });
        </script>
<?php
    }
}