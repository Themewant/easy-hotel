<?php
/**
 * eshb_search Widget
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

class Eshb_Search_Widget extends \Elementor\Widget_Base {

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
        return 'eshb_search';
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
        return esc_html__( 'Easy Hotel Search', 'easy-hotel' );
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
            'eshb_search_container_style',
            [
                'label' => esc_html__( 'Container', 'easy-hotel' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );           
        $this->add_control(
            'eshb_search_bg_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Background', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'eshb_search_background',
				'types' => [ 'classic', 'gradient', 'video' ],
				'selector' => '{{WRAPPER}} .eshb-search',
			]
		);
        $this->add_control(
            'eshb_search_background_backdrop_filter',
            [
                'label'       => esc_html__( 'Backdrop Blur (px)', 'easy-hotel' ),
                'type'        => Controls_Manager::NUMBER,
                'label_block' => false,                    
                'selectors'    => [
                    '{{WRAPPER}} .eshb-search' => 'backdrop-filter: blur({{VALUE}}px)'
                ],
            ]   
        );   
        $this->add_responsive_control(
            'eshb_search_padding',
            [
                'label' => esc_html__( 'Padding', 'easy-hotel' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-search' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],               
            ]
        );
        $this->end_controls_section();


        $this->start_controls_section(
            'eshb_search_fields_geoup_style',
            [
                'label' => esc_html__( 'Fields Group', 'easy-hotel' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );      
        $this->add_responsive_control(
            'eshb_search_fields_group_padding',
            [
                'label' => esc_html__( 'Fields Group Padding', 'easy-hotel' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-search .eshb-search-form > .eshb-form-group' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],               
            ]
        );
        $this->add_control(
            'eshb_search_label_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Label', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_control(
            'eshb_search_label_color',
            [
                'label' => esc_html__( 'Label Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-search .eshb-search-form .eshb-form-group .field-label' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
		    Group_Control_Typography::get_type(),
		    [
		        'name' => 'label_typography',
		        'selector' => '{{WRAPPER}} .eshb-search .eshb-search-form .eshb-form-group .field-label',
		        
		    ]
		);
        $this->add_control(
            'eshb_search_input_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Input', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_control(
            'eshb_search_input_color',
            [
                'label' => esc_html__( 'Input Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-search .eshb-search-form .eshb-form-group .form-control' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
		    Group_Control_Typography::get_type(),
		    [
		        'name' => 'input_typography',
		        'selector' => '{{WRAPPER}} .eshb-search .eshb-search-form .eshb-form-group .form-control',
		        
		    ]
		);

        $this->add_control(
            'eshb_search_plus_minus_btn_styles',
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
		            '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
		            '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
		    'btn_text_color',
		    [
		        'label' => esc_html__( 'Text Color', 'easy-hotel' ),
		        'type' => Controls_Manager::COLOR,		      
		        'selectors' => [
		            '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus' => 'color: {{VALUE}};',
		        ],
		    ]
		);

		$this->add_group_control(
		    Group_Control_Typography::get_type(),
		    [
		        'name' => 'btn_typography',
		        'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus',
		        
		    ]
		);
		$this->add_group_control(
		    Group_Control_Background::get_type(),
			[
				'name' => 'background_normal',
				'label' => esc_html__( 'Background', 'easy-hotel' ),
				'types' => [ 'classic', 'gradient' ],
                
				'selector' =>'{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus',
			]
		);
	
		$this->add_group_control(
		    Group_Control_Border::get_type(),
		    [
		        'name' => 'button_border',
		        'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus',
		    ]
		);

		$this->add_control(
		    'button_border_radius',
		    [
		        'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
		        'type' => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', '%' ],
		        'selectors' => [
		            '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',        
    	        ],
		    ]
		);

		$this->add_group_control(
		    Group_Control_Box_Shadow::get_type(),
		    [
		        'name' => 'button_box_shadow',
		        'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus',
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
		            '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus:hover, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus:hover' => 'color: {{VALUE}};',
		        ],
		    ]
		);
		$this->add_group_control(
		    Group_Control_Typography::get_type(),
		    [
		        'name' => 'btn_hover_typography',
		        'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus:hover, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus:hover',
		        
		    ]
		);

		$this->add_group_control(
		    Group_Control_Background::get_type(),
			[
				'name' => 'hover_background',
				'label' => esc_html__( 'Background', 'easy-hotel' ),
				'types' => [ 'classic', 'gradient' ],
                
				'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus',
			]
		);	

		$this->add_group_control(
		    Group_Control_Border::get_type(),
		    [
		        'name' => 'button_hover_border',
		        'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus:hover, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus:hover',
		    ]
		);

		$this->add_control(
		    'button_hover_border_radius',
		    [
		        'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
		        'type' => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', '%' ],
		        'selectors' => [
		            '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus:hover, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		    	],
		    ]
		);
		$this->add_group_control(
		    Group_Control_Box_Shadow::get_type(),
		    [
		        'name' => 'button_hover_box_shadow',
				'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-group .d-minus:hover, {{WRAPPER}} .eshb-search-form .eshb-form-group .d-plus:hover',
		    ]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

        $this->end_controls_section();

    
        $this->start_controls_section(
            'eshb_search_submit_btn_styles',
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
		            '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
		            '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'submit_btn_typography',
                'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn',
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'submit_btn_background_normal',
                'label' => esc_html__( 'Background', 'easy-hotel' ),
                'types' => [ 'classic', 'gradient' ],
                
                'selector' =>'{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'submit_btn_border',
                'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn',
            ]
        );
        
        $this->add_control(
            'submit_btn_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',        
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'submit_btn_box_shadow',
                'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn',
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
                    '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'submit_btn_hover_typography',
                'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn:hover',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'submit_btn_hover_background',
                'label' => esc_html__( 'Background', 'easy-hotel' ),
                'types' => [ 'classic', 'gradient' ],
                
                'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn:hover',
            ]
        );	
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'submit_btn_hover_border',
                'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn:hover',
            ]
        );
        
        $this->add_control(
            'submit_btn_hover_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'submit_btn_hover_box_shadow',
                'selector' => '{{WRAPPER}} .eshb-search-form .eshb-form-submit-btn:hover',
            ]
        );
        
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();

    }

    
  
    protected function render() {

        $settings = $this->get_settings_for_display();       
        echo do_shortcode( '[eshb_search_form]' );
        
    }
}
