<?php
use Elementor\Group_Control_Css_Filter;
use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Group_Control_Background;

defined( 'ABSPATH' ) || die();
class Eshb_Room_Grid_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve rsgallery widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'eshb-room-grid';
	}		

	/**
	 * Get widget title.
	 *
	 * Retrieve rsgallery widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Easy Hotel Room Grid', 'easy-hotel' );
	}

	public function get_icon() {
        return esc_attr('easy-hotel-widget-icon');
    }


    public function get_categories() {
        return [ 'easy_hotel_category' ];
    }


	
	
	/**
	 * Register rsgallery widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */

	

	protected function register_controls() {

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
			'room_grid_style',
			[
				'label'   => esc_html__( 'Select Style', 'easy-hotel' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '1',				
				'options' => [
					'default' => 'Default',
					'1' => 'Style 1',
					'2' => 'Style 2',
					'3' => 'Style 3',
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
			'room_columns',
			[
				'label'   => esc_html__( 'Columns', 'easy-hotel' ),
				'type'    => Controls_Manager::SELECT,				
				'options' => [
					'1' => esc_html__( '1 Column', 'easy-hotel' ),		
					'2' => esc_html__( '2 Column', 'easy-hotel' ),
					'3' => esc_html__( '3 Column', 'easy-hotel' ),
					'4' => esc_html__( '4 Column', 'easy-hotel' ),
					'6' => esc_html__( '6 Column', 'easy-hotel' ),
				],
				'default' => 3,
				'separator' => 'before',
				'condition' => [
					'room_grid_style!' => '1'
				]							
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
			'room_item_styles',
			[
				'label' => esc_html__( 'Item', 'easy-hotel' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'item_background',
				'label' => esc_html__( 'Background', 'easy-hotel' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .room-grid .grid-item',
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
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'item_border',
				'selector' => '{{WRAPPER}} .room-grid .grid-item',
			]
		);
		$this->add_responsive_control(
			'item_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .room-grid .grid-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);			
		$this->add_responsive_control(
			'item_padding',
			[
				'label' => esc_html__( 'Padding', 'easy-hotel' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .room-grid .grid-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);	
		$this->add_responsive_control(
			'item_margin',
			[
				'label' => esc_html__( 'Margin', 'easy-hotel' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .room-grid .grid-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);
		$this->add_responsive_control(
			'item_gap',
			[
				'label' => esc_html__( 'Item Gap', 'easy-hotel' ),
				'type' => Controls_Manager::SLIDER,
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
                    '{{WRAPPER}} .room-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
				'condition' => [
					'room_grid_style!' => '1'
				]	
			]
		);
		$this->add_responsive_control(
			'item_bottom_spacing',
			[
				'label' => esc_html__( 'Item Bottom Gap', 'easy-hotel' ),
				'type' => Controls_Manager::SLIDER,
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
				'default' => [
					'unit' => '%',
					'size' => 6,
				],	
				'selectors' => [
                    '{{WRAPPER}} .room-grid .grid-item' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
                ],
				'condition' => [
					'room_grid_style!' => '1'
				]	
			]
		);
		$this->end_controls_section();
		
        $this->start_controls_section(
			'section_slider_style',
			[
				'label' => esc_html__( 'Style', 'easy-hotel' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
            'title_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Title', 'easy-hotel' ),
                'separator' => 'after',               
            ]
        );
        $this->add_control(
            'title_color',
            [
                'label' => esc_html__( 'Title Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .room-grid .grid-item .p-title a' => 'color: {{VALUE}};',      
                    '{{WRAPPER}} .room-grid .grid-item .p-title' => 'color: {{VALUE}};',      
                ],                
            ]
        );
        $this->add_control(
            'title_color_hover',
            [
                'label' => esc_html__( 'Title Hover Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .room-grid .grid-item .p-title a:hover' => 'color: {{VALUE}};',                                                                        
                ],   
				'condition' => ['room_grid_style' => ['1']],             
            ]
            
        );
        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__( 'Title Typography', 'easy-hotel' ),
				'selector' => '{{WRAPPER}} .room-grid .grid-item .p-title',                    
			]
		);
		$this->add_control(
            'desc_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Description', 'easy-hotel' ),
                'separator' => 'after',               
            ]
        );
		$this->add_control(
            'des_color',
            [
                'label' => esc_html__( 'Description Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
				'separator' => 'before',          
				'condition' => ['room_grid_style' => ['1']],
                'selectors' => [
                    '{{WRAPPER}} .room-grid .grid-item .desc' => 'color: {{VALUE}};',                 
                ],                
            ]
        );	
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'des_typography',
				'label' => esc_html__( 'Description Typography', 'easy-hotel' ),
				'selector' => '{{WRAPPER}} .room-grid .grid-item .desc,{{WRAPPER}} .room-grid .grid-item p',            
				'condition' => ['room_grid_style' => ['1']],
			]
		);
        $this->add_control(
            'capacity_color',
            [
                'label' => esc_html__( 'Capacity Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
				'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .room-grid .grid-item span.capacity' => 'color: {{VALUE}};',                 
                ],                
            ]
        );
        $this->add_control(
            'capacity_color_hover',
            [
                'label' => esc_html__( 'Capacity Hover Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .room-grid .grid-item span.capacity:hover' => 'color: {{VALUE}};',     
                ],                
            ]            
        ); 		
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'capacity_typography',
				'label' => esc_html__( 'Capacity Typography', 'easy-hotel' ),
				'selector' => '{{WRAPPER}} .room-grid .grid-item span.capacity',
			]
		);
		$this->add_control(
            'pricing_styles',
            [
                'type' => Controls_Manager::HEADING,
                'label' => esc_html__( 'Pricing', 'easy-hotel' ),
                'separator' => 'after',               
            ]
        );
		$this->add_control(
            'pricing_label_color',
            [
                'label' => esc_html__( 'Label Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,    
                'selectors' => [
                    '{{WRAPPER}} .room-grid .grid-item .item-inner .pricing-info .label' => 'color: {{VALUE}};',                 
                ],                
            ]
        );	
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'pricing_label_typography',
				'label' => esc_html__( 'Label Typography', 'easy-hotel' ),
				'selector' => '{{WRAPPER}} .room-grid .grid-item .pricing-info .label',
			]
		);
		$this->add_control(
            'price_color',
            [
                'label' => esc_html__( 'Price Color', 'easy-hotel' ),
                'type' => Controls_Manager::COLOR,        
                'selectors' => [
                    '{{WRAPPER}} .room-grid .grid-item .item-inner .pricing-info .price' => 'color: {{VALUE}};',                 
                ],                
            ]
        );	
		
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'pricing_typography',
				'label' => esc_html__( 'Pricing Typography', 'easy-hotel' ),
				'selector' => '{{WRAPPER}} .room-grid .grid-item .pricing-info .price',
			]
		);

        $this->end_controls_section();

        	$this->start_controls_section(
        		    '_section_style_button',
        		    [
        		        'label' => esc_html__( 'Button', 'easy-hotel' ),
        		        'tab' => Controls_Manager::TAB_STYLE,
        		    ]
        		);
        		$this->start_controls_tabs( '_tabs_button' );

        		$this->start_controls_tab(
                    'style_normal_tab',
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
        		            '{{WRAPPER}} .room-grid .grid-item .details-btn' => 'color: {{VALUE}};',
        		        ],
        		    ]
        		);
				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'content_typography',
						'selector' => '{{WRAPPER}} .room-grid .grid-item .details-btn',
					]
				);
        		$this->add_group_control(
        		    Group_Control_Background::get_type(),
        			[
        				'name' => 'btn_background_normal',
        				'label' => esc_html__( 'Background', 'easy-hotel' ),
        				'types' => [ 'classic', 'gradient' ],
        				'selector' => '{{WRAPPER}} .room-grid .grid-item .details-btn',
        			]
        		);	
				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' => 'btn_border',
						'selector' => '{{WRAPPER}} .room-grid .grid-item .details-btn',
					]
				);
				$this->add_responsive_control(
					'btn_padding',
					[
						'label' => esc_html__( 'Padding', 'easy-hotel' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' => [
							'{{WRAPPER}} .room-grid .grid-item .details-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
						],
					]
				);			
				$this->add_responsive_control(
					'btn_border_radius',
					[
						'label' => esc_html__( 'Border Radius', 'easy-hotel' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' => [
							'{{WRAPPER}} .room-grid .grid-item .details-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
						],
					]
				);	
				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' => 'btn_box_shadow',
						'selector' => '{{WRAPPER}} .room-grid .grid-item .details-btn',
					]
				);		
        	$this->end_controls_tab();

        	$this->start_controls_tab(
                    'style_hover_tab',
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
        		            '{{WRAPPER}} .room-grid .grid-item .details-btn:hover' => 'color: {{VALUE}};',
        		        ],
        		    ]
        		);
        		$this->add_group_control(
        		    Group_Control_Background::get_type(),
        			[
        				'name' => 'btn_background_hover',
        				'label' => esc_html__( 'Background', 'easy-hotel' ),
        				'types' => [ 'classic', 'gradient' ],
        				'selector' => '{{WRAPPER}} .room-grid .grid-item .details-btn:hover',
        			]
        		);
				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' => 'btn_box_shadow_hover',
						'selector' => '{{WRAPPER}} .room-grid .grid-item .details-btn:hover',
					]
				);
        		$this->end_controls_tab();
        		$this->end_controls_tabs();	
        	$this->end_controls_section();

			$this->start_controls_section(
				'section_best_seller_style',
				[
					'label' => esc_html__( 'Best Seller', 'easy-hotel' ),
					'tab' => Controls_Manager::TAB_STYLE,
					'condition' => ['room_grid_style' => ['3']],
				]
				);
				$this->add_control(
					'best_seller_color',
					[
						'label' => esc_html__( 'Color', 'easy-hotel' ),
						'type' => Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .room-grid .grid-item .best-seller' => 'color: {{VALUE}};',      
						],                
					]
				);
				$this->add_group_control(
					Group_Control_Typography::get_type(),
					[
						'name' => 'best_seller_typography',
						'label' => esc_html__( 'Typography', 'easy-hotel' ),
						'selector' => '{{WRAPPER}} .room-grid .grid-item .best-seller',                    
					]
				);
				$this->add_group_control(
        		    Group_Control_Background::get_type(),
        			[
        				'name' => 'best_seller_background',
        				'label' => esc_html__( 'Background', 'easy-hotel' ),
        				'types' => [ 'classic', 'gradient' ],
        				'selector' => '{{WRAPPER}} .room-grid .grid-item .best-seller',
        			]
        		);
				$this->add_responsive_control(
					'best_sellerpadding',
					[
						'label' => esc_html__( 'Padding', 'easy-hotel' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' => [
							'{{WRAPPER}} .room-grid .grid-item .best-seller' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
						],
					]
				);
	
			$this->end_controls_section();
	}

	/**
	 * Render rsgallery widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$room_columns = $settings['room_columns'];
		$cat = $settings['category'];
		$grid_style = 'style'.$settings['room_grid_style'];
		$btn_text = $settings['btn_text'];
		$pricing_prefix = isset($settings['pricing_prefix']) ? $settings['pricing_prefix'] : '';
		$thumbnail_size = isset($settings['thumbnail_size']) ? $settings['thumbnail_size'] : 'eshb_thumbnail';

		$today_date = gmdate('Y-m-d'); // Get today's date

        // Create a DateTime object from today's date

        $date = new DateTime($today_date);

        // Add one day
        $date->modify('+1 day');

        // Get the new date in 'Y-m-d' format
        $tomorrow_date = $date->format('Y-m-d');
		
		$start = new DateTime($today_date);
        $end = new DateTime($tomorrow_date);
		
		?>

		<div class="room-grid eshb-item-grid <?php echo esc_attr($grid_style); ?>" style="grid-template-columns: repeat(<?php echo esc_attr( $room_columns ); ?>, 1fr)">
		
				<?php 

				$hotel_core = new ESHB_Core();
				$hotel_view = new ESHB_View();

				$eshb_settings = get_option('eshb_settings');
				
				$string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';
				
				
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

				$i = 0;
				$animation_delay = 0.2;

				while($best_wp->have_posts()): $best_wp->the_post();

					$animation_delay+=0.1;

					$accomodation_id = get_the_ID();

					$eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
					$accomodation_info_group = $eshb_accomodation_metaboxes['accomodation_info_group'];
					
					$booking_url = get_the_permalink($accomodation_id);
					
					$price = $hotel_core->get_eshb_price_html('', '', $accomodation_id);
					$numeric_price = $hotel_core->get_eshb_price('', '', $accomodation_id);
					
					$excerpt = $hotel_view->eshb_custom_excerpt(35, $accomodation_id);

					$perodicity_string = apply_filters( 'eshb_perodicity_string_in_loop', $string_night, $accomodation_id, $eshb_settings);
				
					include plugin_dir_path(__FILE__) . $grid_style .".php";  

				endwhile;
				wp_reset_postdata();
				?>
			
		</div>

		<?php	
		
	}


}

