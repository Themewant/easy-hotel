<?php
defined('ABSPATH') || die();
class Eshb_Room_Gallery_Widget  extends \Elementor\Widget_Base {
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
        return 'eshb-room-room-gallery';
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
        return esc_html__('Easy Hotel Room Gallery', 'easy-hotel');
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
        return ['room_room_gallery'];
    }
    protected function register_controls()
    {

        
        $this->start_controls_section(
            'content_room_gallery',
            [
                'label' => esc_html__('Room Gallery Settings', 'easy-hotel'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_group_control(
            Elementor\Group_Control_Image_Size::get_type(),
            [
                'name' => 'thumbnail',
                'default' => 'full',
                'separator' => 'before',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'col_xl',
            [
                'label'   => esc_html__('Wide Screen > 1399px', 'easy-hotel'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 1,
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
                'type'    => \Elementor\Controls_Manager::SELECT,
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
            'col_md',
            [
                'label'   => esc_html__('Desktops > 991px', 'easy-hotel'),
                'type'    => \Elementor\Controls_Manager::SELECT,
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
                'type'    => \Elementor\Controls_Manager::SELECT,
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
                'type'    => \Elementor\Controls_Manager::SELECT,
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
                'type'    => \Elementor\Controls_Manager::SELECT,
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
            'room_gallery_effect',
            [
                'label' => esc_html__('Room Gallery Effect', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::SELECT,
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
            'room_gallery_dots',
            [
                'label'   => esc_html__('Navigation Dots', 'easy-hotel'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
            ]

        );
        $this->add_control(
            'room_gallery_dots_color',
            [
                'label' => esc_html__('Navigation Dots Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination .swiper-pagination-bullet' => 'background-color: {{VALUE}} !important;',
                ],
                'condition' => ['room_gallery_dots' => 'true',],
            ]
        );
        $this->add_control(
			'room_gallery_dots_opacity',
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
                'condition' => ['room_gallery_dots' => 'true',],
			]
		);
        $this->add_control(
            'room_gallery_dots_color_active',
            [
                'label' => esc_html__('Active Navigation Dots Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .swiper-pagination .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background: {{VALUE}} !important;',
                ],
                'condition' => ['room_gallery_dots' => 'true',],
            ]
        );

        $this->add_responsive_control(
            'room_gallery_nav',
            [
                'label'   => esc_html__('Navigation Nav', 'easy-hotel'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'room_gallerynav_text_bg',
            [
                'label' => esc_html__('Nav BG Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rt_room_gallery-btn-wrapper .swiper-button-prev' => 'background: {{VALUE}} !important;',
                    '{{WRAPPER}} .rt_room_gallery-btn-wrapper .swiper-button-next' => 'background: {{VALUE}} !important;',
                ],
                'condition' => ['room_gallery_nav' => 'true',],
            ]
        );
        $this->add_control(
            'room_gallerynav_text_bg_hover',
            [
                'label' => esc_html__('Nav BG Hover Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rt_room_gallery-btn-wrapper .swiper-button-prev:hover' => 'background: {{VALUE}} !important;',
                    '{{WRAPPER}} .rt_room_gallery-btn-wrapper .swiper-button-next:hover' => 'background: {{VALUE}} !important;',
                ],
                'condition' => ['room_gallery_nav' => 'true',],
            ]
        );
        $this->add_control(
            'room_gallerynav_text_bg_icon',
            [
                'label' => esc_html__('Nav BG Icon Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rt_room_gallery-btn-wrapper .swiper-button-prev' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .rt_room_gallery-btn-wrapper .swiper-button-next' => 'color: {{VALUE}} !important;',
                ],
                'condition' => ['room_gallery_nav' => 'true',],
            ]
        );
        $this->add_control(
            'room_gallerynav_text_bg_hover_icon',
            [
                'label' => esc_html__('Nav BG Icon Hover Color', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rt_room_gallery-btn-wrapper .swiper-button-prev:hover' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .rt_room_gallery-btn-wrapper .swiper-button-next:hover' => 'color: {{VALUE}} !important;',
                ],
                'condition' => ['room_gallery_nav' => 'true',],
            ]
        );

        $this->add_responsive_control(
            'nav_top_gap',
            [
                'label' => esc_html__('Navigation Top Gap', 'easy-hotel'),
                'type' => \Elementor\Controls_Manager::SLIDER,
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
                            'name' => 'room_gallery_dots',
                            'operator' => '==',
                            'value' => 'true',
                        ],
                        [
                            'name' => 'room_gallery_nav',
                            'operator' => '==',
                            'value' => 'true',
                        ],
                    ],
                ],
                
            ]
        );
        

        $this->add_control(
            'room_gallery_autoplay',
            [
                'label'   => esc_html__('Autoplay', 'easy-hotel'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'room_gallery_autoplay_speed',
            [
                'label'   => esc_html__('Autoplay Slide Speed', 'easy-hotel'),
                'type'    => \Elementor\Controls_Manager::SELECT,
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
                    'room_gallery_autoplay' => 'true',
                ],
            ]
        );

        $this->add_control(
            'room_gallery_interval',
            [
                'label'   => esc_html__('Autoplay Interval', 'easy-hotel'),
                'type'    => \Elementor\Controls_Manager::SELECT,
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
                    'room_gallery_autoplay' => 'true',
                ],
            ]
        );

        $this->add_control(
            'room_gallery_stop_on_interaction',
            [
                'label'   => esc_html__('Stop On Interaction', 'easy-hotel'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
                'condition' => [
                    'room_gallery_autoplay' => 'true',
                ],
            ]

        );

        $this->add_control(
            'room_gallery_stop_on_hover',
            [
                'label'   => esc_html__('Stop on Hover', 'easy-hotel'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
                'condition' => [
                    'room_gallery_autoplay' => 'true',
                ],
            ]

        );

        $this->add_control(
            'room_gallery_loop',
            [
                'label'   => esc_html__('Loop', 'easy-hotel'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'false',
                'options' => [
                    'true' => esc_html__('Enable', 'easy-hotel'),
                    'false' => esc_html__('Disable', 'easy-hotel'),
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'room_gallery_centerMode',
            [
                'label'   => esc_html__('Center Mode', 'easy-hotel'),
                'type'    => \Elementor\Controls_Manager::SELECT,
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
                'type' => \Elementor\Controls_Manager::SLIDER,
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
            ]
        );
        $this->end_controls_section();

                
        $this->start_controls_section(
            'item_container_style',
            [
                'label' => esc_html__( 'Item', 'easy-hotel' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );           
        $this->add_control(
            'item_bg_style',
            [
                'type' => \Elementor\Controls_Manager::HEADING,
                'label' => esc_html__( 'Background', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_responsive_control(
            'item_padding',
            [
                'label' => esc_html__( 'Padding', 'easy-hotel' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .accomodation-gallery .swiper-slide' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .accomodation-gallery .swiper-slide' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);	
        $this->end_controls_section();


    

    }

    
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $thumbnail_size = isset($settings['thumbnail_size']) && !empty($settings['thumbnail_size']) ? $settings['thumbnail_size'] : 'eshb_thumbnail';
    

        $col_xl          = $settings['col_xl'];
        $col_xl          = !empty($col_xl) ? $col_xl : 4;
        $slidesToShow    = $col_xl;
        $autoplaySpeed   = $settings['room_gallery_autoplay_speed'];
        $autoplaySpeed   = !empty($autoplaySpeed) ? $autoplaySpeed : '1000';
        $interval        = $settings['room_gallery_interval'];
        $interval        = !empty($interval) ? $interval : '3000';
        $slidesToScroll  = $settings['slides_ToScroll'];
        $room_gallery_autoplay = $settings['room_gallery_autoplay'] === 'true' ? 'true' : 'false';
        $pauseOnHover    = $settings['room_gallery_stop_on_hover'] === 'true' ? 'true' : 'false';
        $pauseOnInter    = $settings['room_gallery_stop_on_interaction'] === 'true' ? 'true' : 'false';
        $room_galleryDots      = $settings['room_gallery_dots'] == 'true' ? 'true' : 'false';
        $room_galleryNav       = $settings['room_gallery_nav'] == 'true' ? 'true' : 'false';
        $infinite        = $settings['room_gallery_loop'] === 'true' ? 'true' : 'false';
        $centerMode      = $settings['room_gallery_centerMode'] === 'true' ? 'true' : 'false';
        $col_lg          = $settings['col_lg'];
        $col_md          = $settings['col_md'];
        $col_sm          = $settings['col_sm'];
        $col_xs          = $settings['col_xs'];
        
        $item_gap        = $settings['item_gap_custom']['size'];
        $item_gap        = !empty($item_gap) ? $item_gap : '30';
        $next_text       = !empty($next_text) ? $next_text : '';
        $unique          = wp_rand(2012, 35120);
     
        if ($room_gallery_autoplay == 'true') {
            $room_gallery_autoplay = 'autoplay: { ';
            $room_gallery_autoplay .= 'delay: ' . $interval;
            if ($pauseOnHover == 'true') {
                $room_gallery_autoplay .= ', pauseOnMouseEnter: true';
            } else {
                $room_gallery_autoplay .= ', pauseOnMouseEnter: false';
            }
            if ($pauseOnInter == 'true') {
                $room_gallery_autoplay .= ', disableOnInteraction: true';
            } else {
                $room_gallery_autoplay .= ', disableOnInteraction: false';
            }
            $room_gallery_autoplay .= ' }';
        } else {
            $room_gallery_autoplay = 'autoplay: false';
        }

        $effect = $settings['room_gallery_effect'];

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

    
        $blank = "";
        

            $ESHB_View = new ESHB_View();

            $accomodation_id = get_the_ID();

           echo esc_html($ESHB_View->eshb_get_gallery_html($accomodation_id, $unique, $thumbnail_size, $room_galleryDots, $room_galleryNav));
      
       

        ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                var swiper<?php echo esc_attr($unique); ?><?php echo esc_attr($unique); ?> = new Swiper(".has-accomodation-gallery-<?php echo esc_attr($unique); ?>", {
                    slidesPerView: 1,
                    <?php echo esc_attr($seffect); ?>
                    speed: <?php echo esc_attr($autoplaySpeed); ?>,
                    slidesPerGroup: 1,
                    loop: <?php echo esc_attr($infinite); ?>,
                    <?php echo esc_attr($room_gallery_autoplay); ?>,
                    spaceBetween: <?php echo esc_attr($item_gap); ?>,
                    centeredSlides: <?php echo esc_attr($centerMode); ?>,
                    <?php
                        if ($room_galleryNav == 'true') {
                            echo 'navigation: { nextEl: ".has-accomodation-gallery-' . esc_attr($unique) . ' .swiper-button-next", prevEl: ".has-accomodation-gallery-' . esc_attr($unique) . ' .swiper-button-prev", },';
                        }
                    ?>
                    <?php if ($room_galleryDots == 'true') { ?>
                        pagination: {
                            el: ".has-accomodation-gallery-<?php echo esc_attr($unique); ?> .swiper-pagination",
                            clickable: true,
                        },
                    <?php } ?>
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