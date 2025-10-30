<?php
/**
 * eshb_booking_form Widget
 *
 */
use Elementor\Group_Control_Text_Shadow;
use Elementor\Utils;
use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

defined( 'ABSPATH' ) || die();

class Eshb_Booking_Form_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * Retrieve widget name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */

    public function get_name() {
        return 'eshb_booking_form';
    }   


    /**
     * Get widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Easy Hotel Booking Form', 'easy-hotel' );
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

    protected function register_controls() {

        $this->start_controls_section(
            'eshb_booking_form_section',
            [
                'label' => esc_html__( 'Settings', 'easy-hotel' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
   
        $this->add_control(
            'form_style',
            [
                'label'       => esc_html__( 'Check In Label', 'easy-hotel' ),
                'type'        => Controls_Manager::SELECT,
                'label_block' => false,                    
                'separator'   => 'before', 
                'default'     => 'style-one',
                'options'     => array(
                    'style-one' => esc_html__( 'Style One', 'easy-hotel' ) . '',
                    'style-two' => esc_html__( 'Style Two', 'easy-hotel' ) . '',
                    )
            ]   
        );   
        $this->end_controls_section();

        
        $this->start_controls_section(
            'eshb_booking_form_form_style',
            [
                'label' => esc_html__( 'Form', 'easy-hotel' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );           
        // form width
        $this->add_responsive_control(
            'eshb_booking_form_width',
            [
                'label' => esc_html__( 'Width', 'easy-hotel' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1200,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form.style-two,{{WRAPPER}} .eshb-booking .eshb-booking-form.style-one' => 'width: {{SIZE}}{{UNIT}};',
                ],               
            ]
        );


        $this->add_control(
            'eshb_booking_form_bg_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Background', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'eshb_booking_form_background',
				'types' => [ 'classic', 'gradient'],
				'selector' => '{{WRAPPER}} .eshb-booking .eshb-booking-form.style-two,{{WRAPPER}} .eshb-booking .eshb-booking-form.style-one',
			]
		);  


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'eshb_booking_form_border',
                'label' => esc_html__( 'Border', 'easy-hotel' ),
                'selector' => '{{WRAPPER}} .eshb-booking .eshb-booking-form.style-two,{{WRAPPER}} .eshb-booking .eshb-booking-form.style-one',
            ]
        );


        $this->add_control(
		    'eshb_booking_form_border_radius',
		    [
		        'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
		        'type' => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', '%' ],
		        'selectors' => [
		            '{{WRAPPER}} .eshb-booking-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',        
    	        ],
		    ]
		);
        $this->add_responsive_control(
            'eshb_booking_form_padding',
            [
                'label' => esc_html__( 'Padding', 'easy-hotel' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],               
            ]
        );
        $this->add_control(
            'eshb_booking_form_title_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Form Title', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_control(
            'eshb_booking_form_title_color',
            [
                'label' => esc_html__( 'Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group.form-title-wrapper h3' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group.form-title-wrapper h4' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group.form-title-wrapper span' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_section();


        $this->start_controls_section(
            'eshb_booking_form_fields_geoup_style',
            [
                'label' => esc_html__( 'Fields', 'easy-hotel' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );      
        $this->add_responsive_control(
            'eshb_booking_form_fields_group_padding',
            [
                'label' => esc_html__( 'Fields Group Padding', 'easy-hotel' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form > .eshb-form-group' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],               
            ]
        );
        $this->add_control(
            'eshb_booking_form_label_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Label', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_control(
            'eshb_booking_form_label_color',
            [
                'label' => esc_html__( 'Label Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group .field-label' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-booking-form-customer-details' => 'border-top-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
		    Group_Control_Typography::get_type(),
		    [
		        'name' => 'label_typography',
		        'selector' => '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group .field-label',
		        
		    ]
		);
        $this->add_control(
            'eshb_booking_form_input_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Input', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_control(
            'eshb_booking_form_input_color',
            [
                'label' => esc_html__( 'Input Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group .form-control' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_booking_form_input_placeholder_color',
            [
                'label' => esc_html__( 'Input Placeholder Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group .form-control::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
		    Group_Control_Border::get_type(),
		    [
		        'name' => 'input_border',
		        'selector' => '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group .form-control',
		    ]
		);
        $this->add_group_control(
		    Group_Control_Typography::get_type(),
		    [
		        'name' => 'input_typography',
		        'selector' => '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group .form-control',
		        
		    ]
		);

        $this->add_control(
            'eshb_booking_form_plus_minus_btn_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Plus Minus Button', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_responsive_control(
		    'btn_padding',
		    [
		        'label' => esc_html__( 'Padding', 'easy-hotel' ),
		        'type' => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', 'em', '%' ],
		        'selectors' => [
		            '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		        ],
		    ]
		);
        $this->add_responsive_control(
		    'btn_margin',
		    [
		        'label' => esc_html__( 'Margin', 'easy-hotel' ),
		        'type' => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', 'em', '%' ],
		        'selectors' => [
		            '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		        ],
		    ]
		);
        $this->start_controls_tabs('_tabs_plus_minus_button');

		$this->start_controls_tab(
            'style_plus_minus_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'easy-hotel' ),
            ]
        ); 

        $this->add_control(
		    'btn_input_text_color',
		    [
		        'label' => esc_html__( 'Input Color', 'easy-hotel' ),
		        'type' => Controls_Manager::COLOR,		      
		        'selectors' => [
		            '{{WRAPPER}} .eshb-booking .eshb-booking-form.style-two .eshb-form-groups .eshb-form-group .de-number input,{{WRAPPER}} .eshb-booking .eshb-booking-form.style-one .eshb-form-groups .eshb-form-group .de-number input' => 'color: {{VALUE}};',
		        ],
		    ]
		);

		$this->add_control(
		    'btn_text_color',
		    [
		        'label' => esc_html__( 'Text Color', 'easy-hotel' ),
		        'type' => Controls_Manager::COLOR,		      
		        'selectors' => [
		            '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus' => 'color: {{VALUE}};',
		        ],
		    ]
		);

		$this->add_group_control(
		    Group_Control_Typography::get_type(),
		    [
		        'name' => 'btn_typography',
		        'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus',
		        
		    ]
		);
		$this->add_group_control(
		    Group_Control_Background::get_type(),
			[
				'name' => 'background_normal',
				'label' => esc_html__( 'Background', 'easy-hotel' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' =>'{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus',
			]
		);
	
		$this->add_group_control(
		    Group_Control_Border::get_type(),
		    [
		        'name' => 'button_border',
		        'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus',
		    ]
		);

		$this->add_control(
		    'button_border_radius',
		    [
		        'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
		        'type' => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', '%' ],
		        'selectors' => [
		            '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',        
    	        ],
		    ]
		);

		$this->add_group_control(
		    Group_Control_Box_Shadow::get_type(),
		    [
		        'name' => 'button_box_shadow',
		        'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus',
		    ]
		);
		$this->end_controls_tab();

		$this->start_controls_tab(
            'style_plus_minus_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'easy-hotel' ),
            ]
        ); 

		$this->add_control(
		    'btn_text_hover_color',
		    [
		        'label' => esc_html__( 'Text Color', 'easy-hotel' ),
		        'type' => Controls_Manager::COLOR,		      
		        'selectors' => [
		            '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus:hover, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus:hover' => 'color: {{VALUE}};',
		        ],
		    ]
		);
		$this->add_group_control(
		    Group_Control_Typography::get_type(),
		    [
		        'name' => 'btn_hover_typography',
		        'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus:hover, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus:hover',
		        
		    ]
		);

		$this->add_group_control(
		    Group_Control_Background::get_type(),
			[
				'name' => 'hover_background',
				'label' => esc_html__( 'Background', 'easy-hotel' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus',
			]
		);	

		$this->add_group_control(
		    Group_Control_Border::get_type(),
		    [
		        'name' => 'button_hover_border',
		        'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus:hover, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus:hover',
		    ]
		);

		$this->add_control(
		    'button_hover_border_radius',
		    [
		        'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
		        'type' => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', '%' ],
		        'selectors' => [
		            '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus:hover, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		    	],
		    ]
		);
		$this->add_group_control(
		    Group_Control_Box_Shadow::get_type(),
		    [
		        'name' => 'button_hover_box_shadow',
				'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-group .d-minus:hover, {{WRAPPER}} .eshb-booking-form .eshb-form-group .d-plus:hover',
		    ]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

        $this->end_controls_section();

        
        $this->start_controls_section(
            'eshb_booking_form_services_styles',
            [
                'label' => esc_html__( 'Services', 'easy-hotel' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );   
        $this->add_responsive_control(
		    'services_item_margin',
		    [
		        'label' => esc_html__( 'Margin', 'easy-hotel' ),
		        'type' => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', 'em', '%' ],
		        'selectors' => [
		            '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group.extra-services-wrapper .service-list .service-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		        ],
		    ]
		);       
        $this->add_control(
            'services_item_color',
            [
                'label' => esc_html__( 'Item Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,		      
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group.extra-services-wrapper .service-list .service-item label' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
		    Group_Control_Typography::get_type(),
		    [
		        'name' => 'services_item_typography',
		        'selector' => '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group.extra-services-wrapper .service-list .service-item',
		        
		    ]
		);
        $this->add_control(
            'services_item_pricing_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Pricing', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_control(
            'services_item_pricing_text_color',
            [
                'label' => esc_html__( 'Text Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group.extra-services-wrapper .service-list .service-item .price-quantity .price' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'services_item_pricing_input_text_color',
            [
                'label' => esc_html__( 'Input Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form.style-two .eshb-form-group.extra-services-wrapper .service-list .service-item .price-quantity .service-quantity-selector .de-number input' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form.style-one .eshb-form-group.extra-services-wrapper .service-list .service-item .price-quantity .service-quantity-selector .de-number input' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'services_item_pricing_input_border_color',
            [
                'label' => esc_html__( 'Input Border Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form.style-one .eshb-form-group.extra-services-wrapper .service-list .service-item .price-quantity .service-quantity-selector .de-number span.quantity-wrapper' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form.style-two .eshb-form-group.extra-services-wrapper .service-list .service-item .price-quantity .service-quantity-selector .de-number span.quantity-wrapper' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'services_item_pricing_popup_border_color',
            [
                'label' => esc_html__( 'Popup Border Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group.extra-services-wrapper .service-list .service-item .price-quantity .service-quantity-selector' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form .eshb-form-group.extra-services-wrapper .service-list .service-item .price-quantity .service-quantity-selector::before' => 'border-left-color: {{VALUE}};'
                ],
            ]
        );
        $this->end_controls_section();


        $this->start_controls_section(
            'eshb_booking_form_submit_btn_styles',
            [
                'label' => esc_html__( 'Submit Button', 'easy-hotel' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );   
        $this->add_responsive_control(
		    'submit_btn_padding',
		    [
		        'label' => esc_html__( 'Padding', 'easy-hotel' ),
		        'type' => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', 'em', '%' ],
		        'selectors' => [
		            '{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		        ],
		    ]
		);
        $this->add_responsive_control(
		    'submit_btn_margin',
		    [
		        'label' => esc_html__( 'Margin', 'easy-hotel' ),
		        'type' => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', 'em', '%' ],
		        'selectors' => [
		            '{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		        ],
		    ]
		);       
        $this->start_controls_tabs('_tabs_submit_button');

        $this->start_controls_tab(
            'style_submit_submit_btn_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'easy-hotel' ),
            ]
        ); 
        
        $this->add_control(
            'submit_btn_text_color',
            [
                'label' => esc_html__( 'Text Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,		      
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'submit_btn_typography',
                'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn',
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'submit_btn_background_normal',
                'label' => esc_html__( 'Background', 'easy-hotel' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' =>'{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'submit_btn_border',
                'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn',
            ]
        );
        
        $this->add_control(
            'submit_btn_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',        
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'submit_btn_box_shadow',
                'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn',
            ]
        );
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'style_submit_submit_btn_hover_tab',
            [
                'label' => esc_html__( 'Hover', 'easy-hotel' ),
            ]
        ); 
        
        $this->add_control(
            'submit_btn_text_hover_color',
            [
                'label' => esc_html__( 'Text Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,		      
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'submit_btn_hover_typography',
                'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn:hover',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'submit_btn_hover_background',
                'label' => esc_html__( 'Background', 'easy-hotel' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .eshb-booking-form.style-one .eshb-form-submit-btn:hover, {{WRAPPER}} .eshb-booking-form.style-two .eshb-form-submit-btn:hover',
            ]
        );	
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'submit_btn_hover_border',
                'selector' => '{{WRAPPER}} .eshb-booking-form.style-one .eshb-form-submit-btn:hover, {{WRAPPER}} .eshb-booking-form.style-two .eshb-form-submit-btn:hover',
            ]
        );
        
        $this->add_control(
            'submit_btn_hover_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'submit_btn_hover_box_shadow',
                'selector' => '{{WRAPPER}} .eshb-booking-form .eshb-form-submit-btn:hover',
            ]
        );
        
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_control(
            'eshb_booking_form_message_color',
            [
                'label' => esc_html__( 'Message Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,		      
                'selectors' => [
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form.style-one .eshb-form-err .status' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-booking .eshb-booking-form.style-two .eshb-form-err .status' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label' => esc_html__( 'Message Typography', 'easy-hotel' ),
                'name' => 'eshb_booking_form_message_typography',
                'selector' => '{{WRAPPER}} .eshb-booking .eshb-booking-form.style-one .eshb-form-err .status,{{WRAPPER}} .eshb-booking .eshb-booking-form.style-two .eshb-form-err .status',
            ]
        );

        $this->end_controls_section();

    }

    protected function render() {
        $settings = $this->get_settings_for_display();       
        $form_style = $settings['form_style'];
        echo do_shortcode( '[eshb_booking_form style="'. $form_style .'"]' );
    }
}
