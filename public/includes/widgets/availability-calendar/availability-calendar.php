<?php
/**
 * eshb_availability_calendar Widget
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

class Eshb_Availability_Calendar_Widget extends \Elementor\Widget_Base {

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
        return 'eshb_availability_calendar';
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
        return esc_html__( 'Easy Hotel Availability Calendar', 'easy-hotel' );
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
            'eshb_availability_calendar_container_style',
            [
                'label' => esc_html__( 'Container', 'easy-hotel' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );           
        $this->add_control(
            'eshb_availability_calendar_container_bg_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Background', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'eshb_availability_calendar_container_background',
				'types' => [ 'classic', 'gradient', 'video' ],
				'selector' => '{{WRAPPER}} .eshb-availability-calendars-area',
			]
		);
 
        $this->add_responsive_control(
            'eshb_availability_calendar_container_padding',
            [
                'label' => esc_html__( 'Padding', 'easy-hotel' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],               
            ]
        );
        $this->add_control(
		    'eshb_availability_calendar_container_border_radius',
		    [
		        'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
		        'type' => Controls_Manager::DIMENSIONS,
		        'size_units' => [ 'px', '%' ],
		        'selectors' => [
		            '{{WRAPPER}} .eshb-availability-calendars-area' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',        
    	        ],
		    ]
		);
        $this->end_controls_section();


        $this->start_controls_section(
            'eshb_availability_calendar_style',
            [
                'label' => esc_html__( 'Calendar', 'easy-hotel' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );   
        $this->add_control(
            'eshb_availability_calendar_title_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Calendar Title', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_title_color',
            [
                'label' => esc_html__( 'Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .calendar-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_background',
            [
                'label' => esc_html__( 'Background Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker::before' => 'border-bottom-color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker::after' => 'border-bottom-color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .calendar-table' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td.off' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td.off.in-range' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td.off.start-date' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td.off.end-date' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_border_color',
            [
                'label' => esc_html__( 'Border Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .calendar-table' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'eshb_availability_calendar_table_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Table', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_table_heading_color',
            [
                'label' => esc_html__( 'Heading Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker th' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_table_date_color',
            [
                'label' => esc_html__( 'Date Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'eshb_availability_calendar_table_date_active_color',
            [
                'label' => esc_html__( 'Date Active Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td.active' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'eshb_availability_calendar_table_date_hover_color',
            [
                'label' => esc_html__( 'Date Hover Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td.active:hover' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'eshb_availability_calendar_table_date_booked_color',
            [
                'label' => esc_html__( 'Booked Date Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td.booked-date' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td.booked-date.active' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'eshb_availability_calendar_table_date_hover_bg_color',
            [
                'label' => esc_html__( 'Date Active/Hover Background Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td.active' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td.active:hover' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker td:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'eshb_availability_calendar_apply_button_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Apply Button', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_apply_button_color',
            [
                'label' => esc_html__( 'Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.applyBtn' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_apply_button_hover_color',
            [
                'label' => esc_html__( 'Hover Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.applyBtn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_apply_button_bg_color',
            [
                'label' => esc_html__( 'Background Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.applyBtn' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_apply_button_hover_bg_color',
            [
                'label' => esc_html__( 'Background Hover Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.applyBtn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_apply_button_border_color',
            [
                'label' => esc_html__( 'Border Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.applyBtn' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_apply_button_hover_border_color',
            [
                'label' => esc_html__( 'Border Hover Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.applyBtn:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'eshb_availability_calendar_cancel_button_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Cancel Button', 'easy-hotel' ),
                'separator' => 'before',               
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_cancel_button_color',
            [
                'label' => esc_html__( 'Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.cancelBtn' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_cancel_button_hover_color',
            [
                'label' => esc_html__( 'Hover Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.cancelBtn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_cancel_button_bg_color',
            [
                'label' => esc_html__( 'Background Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.cancelBtn' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_cancel_button_hover_bg_color',
            [
                'label' => esc_html__( 'Background Hover Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.cancelBtn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_cancel_button_border_color',
            [
                'label' => esc_html__( 'Border Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.cancelBtn' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'eshb_availability_calendar_cancel_button_hover_border_color',
            [
                'label' => esc_html__( 'Border Hover Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eshb-availability-calendars-area .eshb-availability-calendars .daterangepicker .range_inputs button.cancelBtn:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

    }

    protected function render() {
        $settings = $this->get_settings_for_display();       
        echo do_shortcode( '[eshb_availability_calendar]' );
    }
}
