<?php
defined( 'ABSPATH' ) || die();

class Eshb_Room_Grid_Widget_Bricks extends \Bricks\Element {

	// Element properties
	public $category     = 'general'; // Use predefined element category 'general'
	public $name         = 'eshb-room-grid'; // Make sure to prefix your elements
	public $icon         = 'ti-header'; // Themify icon font class
	public $tag      = 'div';


	/**
	 * Get widget title.
	 *
	 * Retrieve widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */


	// Simple associative array of all sizes
	function get_all_image_size_names() {
		$sizes = get_intermediate_image_sizes();
		$result = [];

		foreach ($sizes as $size) {
			$result[$size] = $size;
		}

		return $result;
	}

	public function get_label() {
		return __( 'Easy Hotel Room Grid', 'easy-hotel' );
	}


	// Set builder control groups
	public function set_control_groups() {
		$this->control_groups['content'] = [ // Unique group identifier (lowercase, no spaces)
		'title' => esc_html__( 'Content Settings', 'easy-hotel' ), // Localized control group title
		'tab' => 'content', // Set to either "content" or "style"
		];

		$this->control_groups['item'] = [
		'title' => esc_html__( 'Item', 'easy-hotel' ),
		'tab' => 'style',
		];

		$this->control_groups['style'] = [
		'title' => esc_html__( 'Contents', 'easy-hotel' ),
		'tab' => 'style',
		];
		
		$this->control_groups['button'] = [
		'title' => esc_html__( 'Button', 'easy-hotel' ),
		'tab' => 'style',
		];
	
	}
	
	/**
	 * Register rsgallery widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */

	public function enqueue_scripts() {
		wp_enqueue_style( 'eshb-style' );
	}

	public function set_controls() {

		$eshb_categories = get_terms( 'eshb_category' );

        $cat_array = [];
        foreach ( $eshb_categories as $category ) {
            $cat_array[ $category->slug ] = $category->name;
        }

		$this->controls['room_grid_style'] = [
			'tab' => 'content',
			'group' => 'content',
			'label'   => esc_html__( 'Select Style', 'easy-hotel' ),
			'type'    => 'select',
			'default' => 'default',				
			'options' => [
				'default' => 'Default',
				'1' => 'Style 1',
				'2' => 'Style 2',
				'3' => 'Style 3',
			],											
		];

		$this->controls['category'] = [	
				'tab' => 'content',
				'group' => 'content',
				'label'   => esc_html__( 'Category', 'easy-hotel' ),				
				'type'        => 'select',
                'options'     => $cat_array,
                'default'     => [],
				'multiple' => true,	
						
			];

		$this->controls['thumbnail_size'] = [
			'tab' => 'style',
			'group' => 'content',
			'label' => esc_html__( 'Thumbnail Size', 'easy-hotel' ),
			'type' => 'select',
			'options' => $this->get_all_image_size_names(),
			'inline' => true,
			'placeholder' => esc_html__( 'Select size', 'easy-hotel' ),
			'multiple' => false, 
			'searchable' => true,
			'clearable' => true,
			];

	
		$this->controls['per_page'] = [
				'tab' => 'content',
				'group' => 'content',
				'label' => esc_html__( 'Project Show Per Page', 'easy-hotel' ),
				'type' => 'text',
				'default' => -1,
				
			];

		$this->controls['room_columns'] =
			[
				'tab' => 'content',
				'group' => 'content',
				'label'   => esc_html__( 'Columns', 'easy-hotel' ),
				'type'    => 'select',				
				'options' => [
					'1' => esc_html__( '1 Column', 'easy-hotel' ),		
					'2' => esc_html__( '2 Column', 'easy-hotel' ),
					'3' => esc_html__( '3 Column', 'easy-hotel' ),
					'4' => esc_html__( '4 Column', 'easy-hotel' ),
					'6' => esc_html__( '6 Column', 'easy-hotel' ),
				],
				'default' => 3,
				//'required' => ['room_grid_style',  '!=', '1']								
			];

		$this->controls['room_order'] = [
				'tab' => 'content',
				'group' => 'content',
				'label'   => esc_html__( 'Order', 'easy-hotel' ),
				'type'    => 'select',
				'default' => 'ASC',				
				'options' => [
					'ASC' => 'ASC',
					'DESC' => 'DESC',
				],											
			];

		$this->controls['room_orderby'] = [
				'tab' => 'content',
				'group' => 'content',
				'label'   => esc_html__( 'Order By', 'easy-hotel' ),
				'type'    => 'select',
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
			];

		$this->controls['room_offset'] = [
				'tab' => 'content',
				'group' => 'content',
				'label' => esc_html__( 'Offset', 'easy-hotel' ),
				'type' => 'number',
				'default' => '0',
			];

		$this->controls['pricing_prefix'] = [	'tab' => 'content',
				'group' => 'content',
				'label' => esc_html__( 'Pricing Prefix', 'easy-hotel' ),
				'type' => 'text',
				'default' => esc_html__( 'From', 'easy-hotel' ),
				'placeholder' => esc_html__( 'Type your text here', 'easy-hotel' ),
			];

		$this->controls['btn_text'] = [	
				'tab' => 'content',
				'group' => 'content',
				'label' => esc_html__( 'Text', 'easy-hotel' ),
				'type' => 'text',
				'default' => esc_html__( 'View Details', 'easy-hotel' ),
				'placeholder' => esc_html__( 'Type your text here', 'easy-hotel' ),
			];
		


		$this->controls['item_background'] = [
				'tab' => 'style',
				'group' => 'item',
				'name' => 'item_background',
				'label' => esc_html__( 'Background', 'easy-hotel' ),
				'type' => 'background',
				'css' => [
					[
						'property' => 'background',
						'selector' => '.grid-item',
					]
				]
				
			];	
		$this->controls['item_overlay_background'] = [	
				'tab' => 'style',
				'group' => 'item',
				'label' => esc_html__( 'Overlay Background', 'easy-hotel' ),
				'type' => 'background',
				'css' => [
					[
						'property' => 'background',
						'selector' => '.grid-item .item-inner .hover-bg-one',
					]
				]
			];
		$this->controls['item_border'] = [	
				'tab' => 'style',
				'group' => 'item',
				'label' => esc_html__( 'Border', 'easy-hotel' ),
				'type' => 'border',
				'css' => [
					[
						'property' => 'border',
						'selector' => '.grid-item .item-inner',
					]
				]
			];	
		$this->controls['item_padding'] = [	
				'tab' => 'style',
				'group' => 'item',
				'label' => esc_html__( 'Padding', 'easy-hotel' ),
				'type' => 'dimensions',
				'css' => [
					[
					'property' => 'padding',
					'selector' => '.grid-item',
					]
				],
			];	
		$this->controls['item_margin'] = [	'tab' => 'style',
				'group' => 'item',
				'label' => esc_html__( 'Margin', 'easy-hotel' ),
				'type' => 'dimensions',
				'css' => [
					[
					'property' => 'margin',
					'selector' => '.grid-item',
					]
				],
			];
		$this->controls['item_gap'] = [	
				'tab' => 'style',
				'group' => 'item',
				'label' => esc_html__( 'Item Gap', 'easy-hotel' ),
				'type' => 'slider',
				'units'=> [
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
				'css' => [
					[
					'property' => 'gap',
					'selector' => '.eshb-item-grid',
					]
				],
				'required' => ['room_grid_style',  '!=', '1']	
				];
		$this->controls['item_bottom_spacing'] = [	
			'tab' => 'style',
				'group' => 'item',
				'label' => esc_html__( 'Item Bottom Gap', 'easy-hotel' ),
				'type' => 'slider',
				'units'=> [
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
				'css' => [
					[
					'property' => 'margin',
					'selector' => '.grid-item',
					]
				],
				'required' => ['room_grid_style',  '!=', '1']	
			];
		$this->controls['title_color'] = [
				'tab' => 'style',
				'group' => 'style',
                'label' => esc_html__( 'Title Color', 'easy-hotel' ),
                'type' => 'color', 
				'css' => [
					[
					'property' => 'color',
					'selector' => '.grid-item .p-title, .grid-item .p-title a',
					]
				],   
				'inline' => true,
            ];
        $this->controls['title_color_hover'] = [
				'tab' => 'style',
				'group' => 'style',
                'label' => esc_html__( 'Title Hover Color', 'easy-hotel' ),
                'type' => 'color', 
				'css' => [
					[
					'property' => 'color',
					'selector' => '.grid-item .p-title a:hover, .grid-item .p-title a:hover',
					]
				],   
				'inline' => true,
				'required' => ['room_grid_style',  '=', '1']	
            ];

        $this->controls['title_typography'] = [
				'tab' => 'style',
				'group' => 'style',
				'label' => esc_html__( 'Title Typography', 'easy-hotel' ),
				'type' => 'typography',
				'css' => [
					[
					'property' => 'typography',
					'selector' => '.grid-item .p-title',
					],
				],   
				'inline' => true,
			];

		$this->controls['des_color'] = [
				'tab' => 'style',
				'group' => 'style',
                'label' => esc_html__( 'Description Color', 'easy-hotel' ),
                'type' => 'color',
				          
				    
				'css' => [
					[
					'property' => 'color',
					'selector' => '.grid-item .desc',
					]
				],             
            ];	
		$this->controls['des_typography'] = [
				'tab' => 'style',
				'group' => 'style',
				'label' => esc_html__( 'Description Typography', 'easy-hotel' ),         
				'type' => 'typography',  
				'css' => [
					[
					'property' => 'typography',
					'selector' => '.grid-item .desc, .grid-item p',
					]
				],  
				'required' => ['room_grid_style',  '=', '1']
			];

        $this->controls['capacity_color'] = [
				'tab' => 'style',
				'group' => 'style',
                'label' => esc_html__( 'Capacity Color', 'easy-hotel' ),
                'type' => 'color',
				
				'css' => [
					[
					'property' => 'color',
					'selector' => '.grid-item span.capacity',
					]
				],                  
            ];
		$this->controls['capacity_typography'] = [
				'tab' => 'style',
				'group' => 'style',
				'label' => esc_html__( 'Capacity Typography', 'easy-hotel' ),
				'type' => 'typography',
				'css' => [
					[
					'property' => 'typography',
					'selector' => '.grid-item span.capacity',
					]
				],   
			];

		$this->controls['pricing_label_typography'] = [
				'tab' => 'style',
				'group' => 'style',
				'type' => 'typography',
				'label' => esc_html__( 'Label Typography', 'easy-hotel' ),
				 'css' =>[ 
					[
					'property' => 'typography',
                    'selector'=> '.grid-item .pricing-info .label',                 
                	]
				],  
			];


		$this->controls['price_color'] = [	
				'tab' => 'style',
				'group' => 'style',
                'label' => esc_html__( 'Price Color', 'easy-hotel' ),
                'type' => 'color',
				'css' => [
					['property' => 'color',
                    'selector' => '.grid-item .item-inner .pricing-info .price, .grid-item .pricing-info .label',                
					'important' => true 
					]
                ],               
            ];	
		
		$this->controls['pricing_typography'] = [
				'tab' => 'style',
				'group' => 'style',
				'label' => esc_html__( 'Pricing Typography', 'easy-hotel' ),
				'type' => 'typography',
				 'css' => [
					[
					'property' => 'typography',
                    '.grid-item .item-inner .pricing-info .price',     
					'important' => true,            
                	]
				], 
				'required' => ['room_grid_style',  '!=', '3']
			];

		$this->controls['button_typography'] = [
			'tab' => 'style',
			'group' => 'button',
			'label' => esc_html__( 'Typography', 'easy-hotel' ),
			'type' => 'typography',
			'css' => [
				[
				'property' => 'typography',
				'selector' => '.grid-item .details-btn',
				]
			],                  
		];
		$this->controls['button_color'] = [
				'tab' => 'style',
				'group' => 'button',
                'label' => esc_html__( 'Color', 'easy-hotel' ),
                'type' => 'color',
				
				'css' => [
					[
					'property' => 'color',
					'selector' => '.grid-item .details-btn',
					]
				],                  
            ];
		$this->controls['button_bg_color'] = [
				'tab' => 'style',
				'group' => 'button',
                'label' => esc_html__( 'Background', 'easy-hotel' ),
                'type' => 'color',
				
				'css' => [
					[
					'property' => 'background-color',
					'selector' => '.grid-item .details-btn',
					]
				],                  
            ];
		$this->controls['button_padding'] = [
			'tab' => 'style',
			'group' => 'button',
			'label' => esc_html__( 'Padding', 'easy-hotel' ),
			'type' => 'dimensions',
			'css' => [
				[
				'property' => 'padding',
				'selector' => '.grid-item .details-btn',
				]
			],
		];
		$this->controls['button_border'] = [
			'tab' => 'style',
			'group' => 'button',
			'label' => esc_html__( 'Border', 'easy-hotel' ),
			'type' => 'border',
			'css' => [
				[
				'property' => 'border',
				'selector' => '.grid-item .details-btn',
				],
			],
			'inline' => true,
			'small' => true,
		];
		$this->controls['button_hover_separator'] = [
			'group'      => 'button',
			'label'      => esc_html__( 'Hover', 'easy-hotel' ),
			'type'       => 'separator',
			'fullAccess' => true,
		];
		$this->controls['button_color_hover'] = [
				'tab' => 'style',
				'group' => 'button',
                'label' => esc_html__( 'Color', 'easy-hotel' ),
                'type' => 'color',
				
				'css' => [
					[
					'property' => 'color',
					'selector' => '.grid-item .details-btn:hover',
					]
				],                  
            ];
		$this->controls['button_bg_color_hover'] = [
				'tab' => 'style',
				'group' => 'button',
                'label' => esc_html__( 'Hover Background', 'easy-hotel' ),
                'type' => 'color',
				
				'css' => [
					[
					'property' => 'background-color',
					'selector' => '.grid-item .details-btn:hover',
					]
				],                  
            ];

			
	}

	/**
	 * Render rsgallery widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	public function render() {


		$settings = $this->settings;

		$this->set_attribute( '_root', 'class', ['eshb-room-grid-wrapper'] );
		
		$room_columns = $settings['room_columns'];
		$cat = !empty($settings['category']) ? $settings['category'] : '';
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
		<div <?php echo esc_attr($this->render_attributes( '_root' )) ?>>
			<div class="room-grid eshb-room-grid eshb-item-grid <?php echo esc_attr($grid_style); ?>" style="grid-template-columns: repeat(<?php echo esc_attr( $room_columns ); ?>, 1fr)">
			
					<?php 

					$hotel_core = new ESHB_Core();
					$hotel_view = new ESHB_View();

					$eshb_settings = get_option('eshb_settings');
					$string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';
					
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					$args = array(
						'post_type'      => 'eshb_accomodation',
						'posts_per_page' => $settings['per_page'],	
						'orderby' 		 => $settings['room_orderby'],
						'order' 		 => $settings['room_order'],
						'offset' 		 => $settings['room_offset'],							
					);

					// Using tax_query intentionally to filter accommodation by category.
					// Query is limited and optimized; acceptable in this context.
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
		</div>
		<?php	
		
		
	}


}

