<?php
defined('ABSPATH') || die();
class Eshb_Room_Basic_Info_Widget extends \Elementor\Widget_Base {
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
        return 'eshb-room-basic-info';
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
        return esc_html__('Easy Hotel Room Basic Info', 'easy-hotel');
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
        return ['room_basic_info'];
    }
    protected function register_controls()
    {

        
        $this->start_controls_section(
            'content_room_basic_info',
            [
                'label' => esc_html__('Room Basic Info Settings', 'easy-hotel'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
			'show_price',
			[
				'label' => esc_html__( 'Show Price', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'textdomain' ),
				'label_off' => esc_html__( 'Hide', 'textdomain' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

        $this->end_controls_section();

         $this->start_controls_section(
            'wrapper_style',
            [
                'label' => esc_html__( 'Wrapper', 'easy-hotel' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );   
        $this->add_control(
            'wrapper_bg_color',
            [
                'type' => \Elementor\Controls_Manager::COLOR,
                'label' => esc_html__( 'Background Color', 'easy-hotel' ),
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .eshb-basic-information-wrapper' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'wrapper_padding',
            [
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'label' => esc_html__( 'Padding', 'easy-hotel' ),
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-basic-information-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'border',
				'selector' => '{{WRAPPER}} .eshb-basic-information-wrapper',
			]
		);
        $this->add_responsive_control(
            'wrapper_border_radius',
            [
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-basic-information-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );	

        $this->add_responsive_control(
            'justify_content',
            [
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'label' => esc_html__( 'Justify Content', 'easy-hotel' ),
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__( 'Start', 'easy-hotel' ),
                        'icon' => 'eicon-flex eicon-justify-start-h',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'easy-hotel' ),
                        'icon' => 'eicon-flex eicon-justify-center-h',
                    ],
                    'flex-end' => [
                        'title' => esc_html__( 'End', 'easy-hotel' ),
                        'icon' => 'eicon-flex eicon-justify-end-h',
                    ],
                    'space-between' => [
                        'title' => esc_html__( 'Space Between', 'easy-hotel' ),
                        'icon' => 'eicon-flex eicon-justify-space-between-h',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .basic-information-list' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'item_style',
            [
                'label' => esc_html__( 'Item', 'easy-hotel' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );           
        $this->add_responsive_control(
            'items_space_between',
            [
                'type' => \Elementor\Controls_Manager::SLIDER,
                'label' => esc_html__( 'Space Between', 'easy-hotel' ),
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'default' => [
					'unit' => 'px',
					'size' => 20,
				],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 300,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-basic-information-wrapper .basic-information-list' => 'gap: {{SIZE}}{{UNIT}}',
                ],
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'icon_style',
            [
                'label' => esc_html__( 'Icon', 'easy-hotel' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );           
        $this->add_control(
            'icon_color',
            [
                'type' => \Elementor\Controls_Manager::COLOR,
                'label' => esc_html__( 'Color', 'easy-hotel' ),
                'selectors' => [
                    '{{WRAPPER}} .eshb-basic-information-wrapper .basic-information-list .info-icon' => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'icon_size',
            [
                'type' => \Elementor\Controls_Manager::SLIDER,
                'label' => esc_html__( 'Size', 'easy-hotel' ),
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'default' => [
					'unit' => 'px',
					'size' => 20,
				],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 300,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-basic-information-wrapper .basic-information-list .info-icon' => 'font-size: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .eshb-basic-information-wrapper .basic-information-list img.info-icon' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );
        $this->add_control(
            'icon_space_between',
            [
                'type' => \Elementor\Controls_Manager::SLIDER,
                'label' => esc_html__( 'Space Between', 'easy-hotel' ),
                'size_units' => [ 'px', 'em', 'rem', 'custom' ],
                'default' => [
					'unit' => 'px',
					'size' => 20,
				],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 300,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-basic-information-wrapper .basic-information-list .info' => 'gap: {{SIZE}}{{UNIT}}',
                ],
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'label_style',
            [
                'label' => esc_html__( 'Label', 'easy-hotel' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        ); 
        $this->add_control(
            'label_color',
            [
                'type' => \Elementor\Controls_Manager::COLOR,
                'label' => esc_html__( 'Color', 'easy-hotel' ),
                'selectors' => [
                    '{{WRAPPER}} .eshb-basic-information-wrapper .basic-information-list .info' => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'selector' => '{{WRAPPER}} .eshb-basic-information-wrapper .basic-information-list .info',
			]
		);
        $this->end_controls_section();

        $this->start_controls_section(
            'pricing_style',
            [
                'label' => esc_html__( 'Pricing', 'easy-hotel' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        ); 

        $this->add_control(
            'pricing_color',
            [
                'type' => \Elementor\Controls_Manager::COLOR,
                'label' => esc_html__( 'Color', 'easy-hotel' ),
                'selectors' => [
                    '{{WRAPPER}} .eshb-basic-information-wrapper .price' => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'pricing_typography',
				'selector' => '{{WRAPPER}} .eshb-basic-information-wrapper .price',
			]
		);
        $this->add_control(
			'pricing_label',
			[
				'label' => esc_html__( 'Label', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
        $this->add_control(
            'pricing_label_color',
            [
                'type' => \Elementor\Controls_Manager::COLOR,
                'label' => esc_html__( 'Color', 'easy-hotel' ),
                'selectors' => [
                    '{{WRAPPER}} .eshb-basic-information-wrapper .price .label' => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'pricing_label_typography',
				'selector' => '{{WRAPPER}} .eshb-basic-information-wrapper .price .label',
			]
		);
        $this->end_controls_section();

    }

    
    protected function render()
    {   
        $settings = $this->get_settings_for_display();
        $show_price = $settings['show_price'];
        $accomodation_id = get_the_ID();

        $hotel_core = new ESHB_Core();   
        $price = $hotel_core->get_eshb_price_html('', '', $accomodation_id);
        $eshb_settings = get_option('eshb_settings');
		$string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';
        $perodicity_string = apply_filters( 'eshb_perodicity_string_in_loop', $string_night, $accomodation_id, $eshb_settings);
				
			
        $eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);


        $output = '<div class="eshb-basic-information-wrapper">';
        $output .= '<div class="basic-information-list">';

        if ( ! empty( $eshb_accomodation_metaboxes['accomodation_info_group'] ) ) {
            foreach ( $eshb_accomodation_metaboxes['accomodation_info_group'] as $group ) {
                $icon = !empty($group['info_icon']) ? '<i class="info-icon ' . esc_html($group['info_icon']) . '"></i>' : '';
                $img = !empty($group['info_icon_img']['url']) ? '<img src="' . esc_url($group['info_icon_img']['url']) . '" class="info-icon"> ' : '';
                $title = esc_html($group['info_title']);
                $output .= "<div class='info'>{$icon}{$img}<span class='info-title'>{$title}</span></div>";
            }
        }
        if ($show_price == 'yes') {
            $output .= '<h3 class="price">' . wp_kses_post($price) . '<div class="label"> / ' . esc_html( eshb_get_translated_string($perodicity_string) ) . '</div></h3>';
        }

        $output .= '</div>';
        $output .= '</div>';

        echo $output;
        
    }
}