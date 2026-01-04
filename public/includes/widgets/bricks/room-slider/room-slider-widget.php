<?php
defined('ABSPATH') || die();
class Eshb_Room_Slider_Widget_Bricks  extends \Bricks\Element {

    // Element properties
	public $category     = 'general'; // Use predefined element category 'general'
	public $name         = 'eshb-room-slider'; // Make sure to prefix your elements
	public $icon         = 'ti-header'; // Themify icon font class
	public $scripts      = ['swiper', 'eshb-public-script']; // Script(s) run when element is rendered on frontend or updated in builder
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
		return __( 'Easy Hotel Room Slider', 'easy-hotel' );
	}

    public function enqueue_scripts() {
		wp_enqueue_style( 'swiper', ESHB_PL_URL . 'public/assets/css/swiper-bundle.min.css', array(), '1.0.0', 'all' );
        wp_enqueue_script( 'swiper', ESHB_PL_URL . 'public/assets/js/swiper-bundle.min.js', array('jquery'),'1.0.0',true );
		wp_enqueue_script( 'eshb-public-script', ESHB_PL_URL . 'public/assets/js/public.js', array(),'1.0.0',true );
	}

    // Set builder control groups
	public function set_control_groups() {
		$this->control_groups['content'] = [ // Unique group identifier (lowercase, no spaces)
		'title' => esc_html__( 'Content Settings', 'easy-hotel' ), // Localized control group title
		'tab' => 'content', // Set to either "content" or "style"
		];

        $this->control_groups['slider_settings'] = [ // Unique group identifier (lowercase, no spaces)
		'title' => esc_html__( 'Slider Settings', 'easy-hotel' ), // Localized control group title
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
        
        $this->control_groups['features'] = [
		'title' => esc_html__( 'Features', 'easy-hotel' ),
		'tab' => 'style',
		];
	}


    public function set_controls()
    {

        

        $eshb_categories = get_terms( 'eshb_category' );

        $cat_array = [];
        foreach ( $eshb_categories as $category ) {
            $cat_array[ $category->slug ] = $category->name;
        }

        $eshb_categories = get_terms( 'eshb_category' );

        $cat_array = [];
        foreach ( $eshb_categories as $category ) {
            $cat_array[ $category->slug ] = $category->name;
        }

		

		$this->controls['rt_room_slider_style'] = [
			'tab' => 'content',
			'group' => 'content',
			'label'   => esc_html__( 'Select Style', 'easy-hotel' ),
			'type'    => 'select',		
			'options' => [
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

    

            // Wide Screen > 1399px
        $this->controls['col_xl'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Wide Screen > 1399px', 'easy-hotel'),
            'type'    => 'select',
            'default' => 4,
            'options' => [
                '1'   => esc_html__('1 Column', 'easy-hotel'),
                '2'   => esc_html__('2 Column', 'easy-hotel'),
                '3'   => esc_html__('3 Column', 'easy-hotel'),
                '4'   => esc_html__('4 Column', 'easy-hotel'),
                '4.5' => esc_html__('4.5 Column', 'easy-hotel'),
                '5'   => esc_html__('5 Column', 'easy-hotel'),
                '6'   => esc_html__('6 Column', 'easy-hotel'),
            ],
        ];

        // Desktops > 1199px
        $this->controls['col_lg'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Desktops > 1199px', 'easy-hotel'),
            'type'    => 'select',
            'default' => 4,
            'options' => [
                '1' => esc_html__('1 Column', 'easy-hotel'),
                '2' => esc_html__('2 Column', 'easy-hotel'),
                '3' => esc_html__('3 Column', 'easy-hotel'),
                '4' => esc_html__('4 Column', 'easy-hotel'),
                '5' => esc_html__('5 Column', 'easy-hotel'),
                '6' => esc_html__('6 Column', 'easy-hotel'),
            ],
        ];

        // Desktops > 991px
        $this->controls['col_md'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Desktops > 991px', 'easy-hotel'),
            'type'    => 'select',
            'default' => 3,
            'options' => [
                '1' => esc_html__('1 Column', 'easy-hotel'),
                '2' => esc_html__('2 Column', 'easy-hotel'),
                '3' => esc_html__('3 Column', 'easy-hotel'),
                '4' => esc_html__('4 Column', 'easy-hotel'),
                '5' => esc_html__('5 Column', 'easy-hotel'),
                '6' => esc_html__('6 Column', 'easy-hotel'),
            ],
        ];

        // Tablets > 767px
        $this->controls['col_sm'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Tablets > 767px', 'easy-hotel'),
            'type'    => 'select',
            'default' => 2,
            'options' => [
                '1' => esc_html__('1 Column', 'easy-hotel'),
                '2' => esc_html__('2 Column', 'easy-hotel'),
                '3' => esc_html__('3 Column', 'easy-hotel'),
                '4' => esc_html__('4 Column', 'easy-hotel'),
                '5' => esc_html__('5 Column', 'easy-hotel'),
                '6' => esc_html__('6 Column', 'easy-hotel'),
            ],
        ];

        // Tablets < 768px
        $this->controls['col_xs'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Tablets < 768px', 'easy-hotel'),
            'type'    => 'select',
            'default' => 1,
            'options' => [
                '1' => esc_html__('1 Column', 'easy-hotel'),
                '2' => esc_html__('2 Column', 'easy-hotel'),
                '3' => esc_html__('3 Column', 'easy-hotel'),
                '4' => esc_html__('4 Column', 'easy-hotel'),
                '5' => esc_html__('5 Column', 'easy-hotel'),
                '6' => esc_html__('6 Column', 'easy-hotel'),
            ],
        ];

        // Slide To Scroll
        $this->controls['slides_ToScroll'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Slide To Scroll', 'easy-hotel'),
            'type'    => 'select',
            'default' => 2,
            'options' => [
                '1' => esc_html__('1 Item', 'easy-hotel'),
                '2' => esc_html__('2 Item', 'easy-hotel'),
                '3' => esc_html__('3 Item', 'easy-hotel'),
                '4' => esc_html__('4 Item', 'easy-hotel'),
            ],
        ];

        // Slider Effect
        $this->controls['rt_pslider_effect'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Slider Effect', 'easy-hotel'),
            'type'    => 'select',
            'default' => 'default',
            'options' => [
                'default'   => esc_html__('Default', 'easy-hotel'),
                'fade'      => esc_html__('Fade', 'easy-hotel'),
                'flip'      => esc_html__('Flip', 'easy-hotel'),
                'cube'      => esc_html__('Cube', 'easy-hotel'),
                'coverflow' => esc_html__('Coverflow', 'easy-hotel'),
                'creative'  => esc_html__('Creative', 'easy-hotel'),
                'cards'     => esc_html__('Cards', 'easy-hotel'),
            ],
        ];

        // Navigation Dots
        $this->controls['slider_dots'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Navigation Dots', 'easy-hotel'),
            'type'    => 'select',
            'default' => 'false',
            'options' => [
                'true'  => esc_html__('Enable', 'easy-hotel'),
                'false' => esc_html__('Disable', 'easy-hotel'),
            ],
        ];

        // Navigation Dots Color
        $this->controls['slider_dots_color'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Navigation Dots Color', 'easy-hotel'),
            'type'    => 'color',
            'css'     => [
                '.swiper-pagination .swiper-pagination-bullet' => 'background-color: {{VALUE}} !important;',
            ],
            'required' => ['slider_dots', '=', 'true'],
        ];

        // Opacity
        $this->controls['slider_dots_opacity'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Opacity', 'easy-hotel'),
            'type'    => 'number',
            'css'     => [
                '.swiper-pagination .swiper-pagination-bullet' => 'opacity: {{VALUE}};',
            ],
            'required' => ['slider_dots', '=', 'true'],
        ];

        // Active Navigation Dots Color
        $this->controls['slider_dots_color_active'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Active Navigation Dots Color', 'easy-hotel'),
            'type'    => 'color',
            'css'     => [
                '.swiper-pagination .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background-color: {{VALUE}} !important;',
            ],
            'required' => ['slider_dots', '=', 'true'],
        ];

        // Navigation Nav
        $this->controls['slider_nav'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Navigation Nav', 'easy-hotel'),
            'type'    => 'select',
            'default' => 'false',
            'options' => [
                'true'  => esc_html__('Enable', 'easy-hotel'),
                'false' => esc_html__('Disable', 'easy-hotel'),
            ],
        ];

        // Nav BG Color
        $this->controls['pcat_nav_text_bg'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Nav BG Color', 'easy-hotel'),
            'type'    => 'color',
            'css'     => [
                [
                    'property' => 'background',
                    'selector' => '.swiper-button-prev, .swiper-button-next'
                ]
            ],
            'required' => ['slider_nav', '=', 'true'],
        ];

        // Nav BG Hover Color
        $this->controls['pcat_nav_text_bg_hover'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Nav BG Hover Color', 'easy-hotel'),
            'type'    => 'color',
            'css'     => [
                [
                    'property' => 'background',
                    'selector' => '.swiper-button-prev:hover, .swiper-button-next:hover'
                ]
            ],
            'required' => ['slider_nav', '=', 'true'],
        ];

        // Nav BG Icon Color
        $this->controls['pcat_nav_text_bg_icon'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Nav BG Icon Color', 'easy-hotel'),
            'type'    => 'color',
            'css'     => [
                [
                    'property' => 'color',
                    'selector' => '.swiper-button-prev, .swiper-button-next'
                ]
            ],
            'required' => ['slider_nav', '=', 'true'],
        ];

        // Nav BG Icon Hover Color
        $this->controls['pcat_nav_text_bg_hover_icon'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Nav BG Icon Hover Color', 'easy-hotel'),
            'type'    => 'color',
            'css'     => [
                '.swiper-button-prev:hover' => 'color: {{VALUE}} !important;',
                '.swiper-button-next:hover' => 'color: {{VALUE}} !important;',
            ],
            'required' => ['slider_nav', '=', 'true'],
        ];

        // Navigation Top Gap
        $this->controls['nav_top_gap'] = [
            'tab'   => 'content',
            'group' => 'slider_settings',
            'label' => esc_html__('Navigation Top Gap', 'easy-hotel'),
            'type'  => 'slider',
            'css'   => [
                '.swiper-nav-btn' => 'margin-top: {{VALUE}};',
            ],
            'required' => ['slider_nav', '=', 'true'],
        ];

        // Autoplay
        $this->controls['slider_autoplay'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Autoplay', 'easy-hotel'),
            'type'    => 'select',
            'default' => 'false',
            'options' => [
                'true'  => esc_html__('Enable', 'easy-hotel'),
                'false' => esc_html__('Disable', 'easy-hotel'),
            ],
        ];

        // Autoplay Slide Speed
        $this->controls['slider_autoplay_speed'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Autoplay Slide Speed', 'easy-hotel'),
            'type'    => 'select',
            'default' => 3000,
            'options' => [
                '1000' => esc_html__('1 Seconds', 'easy-hotel'),
                '2000' => esc_html__('2 Seconds', 'easy-hotel'),
                '3000' => esc_html__('3 Seconds', 'easy-hotel'),
                '4000' => esc_html__('4 Seconds', 'easy-hotel'),
                '5000' => esc_html__('5 Seconds', 'easy-hotel'),
            ],
            'required' => ['slider_autoplay', '=', 'true'],
        ];

        // Autoplay Interval
        $this->controls['slider_interval'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Autoplay Interval', 'easy-hotel'),
            'type'    => 'select',
            'default' => 3000,
            'options' => [
                '5000' => esc_html__('5 Seconds', 'easy-hotel'),
                '4000' => esc_html__('4 Seconds', 'easy-hotel'),
                '3000' => esc_html__('3 Seconds', 'easy-hotel'),
                '2000' => esc_html__('2 Seconds', 'easy-hotel'),
                '1000' => esc_html__('1 Seconds', 'easy-hotel'),
            ],
            'required' => ['slider_autoplay', '=', 'true'],
        ];

        // Stop On Interaction
        $this->controls['slider_stop_on_interaction'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Stop On Interaction', 'easy-hotel'),
            'type'    => 'select',
            'default' => 'false',
            'options' => [
                'true'  => esc_html__('Enable', 'easy-hotel'),
                'false' => esc_html__('Disable', 'easy-hotel'),
            ],
            'required' => ['slider_autoplay', '=', 'true'],
        ];

        // Stop on Hover
        $this->controls['slider_stop_on_hover'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Stop on Hover', 'easy-hotel'),
            'type'    => 'select',
            'default' => 'false',
            'options' => [
                'true'  => esc_html__('Enable', 'easy-hotel'),
                'false' => esc_html__('Disable', 'easy-hotel'),
            ],
            'required' => ['slider_autoplay', '=', 'true'],
        ];

        // Loop
        $this->controls['slider_loop'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Loop', 'easy-hotel'),
            'type'    => 'select',
            'default' => 'false',
            'options' => [
                'true'  => esc_html__('Enable', 'easy-hotel'),
                'false' => esc_html__('Disable', 'easy-hotel'),
            ],
        ];

        // Center Mode
        $this->controls['slider_centerMode'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Center Mode', 'easy-hotel'),
            'type'    => 'select',
            'default' => 'false',
            'options' => [
                'true'  => esc_html__('Enable', 'easy-hotel'),
                'false' => esc_html__('Disable', 'easy-hotel'),
            ],
        ];

        // Item Gap
        $this->controls['item_gap_custom'] = [
            'tab'     => 'content',
            'group'   => 'slider_settings',
            'label'   => esc_html__('Item Gap', 'easy-hotel'),
            'type'    => 'slider',
            'default' => [
                'unit' => 'px',
                'size' => 15,
            ],
            'css' => [
                '.eshb-item-grid' => 'padding:0 {{VALUE}};',
            ],
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
						'selector' => '.eshb-item-grid',
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
						'selector' => '.eshb-item-grid .item-inner .hover-bg-one',
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
						'selector' => '.eshb-item-grid .item-inner',
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
					'selector' => '.eshb-item-grid',
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
					'selector' => '.eshb-item-grid',
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
					'selector' => '.eshb-item-grid',
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
					'selector' => '.eshb-item-grid .p-title, .eshb-item-grid .p-title a',
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
					'selector' => '.eshb-item-grid .p-title a:hover, .eshb-item-grid .p-title a:hover',
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
					'selector' => '.eshb-item-grid .p-title',
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
					'selector' => '.eshb-item-grid .desc',
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
					'selector' => '.eshb-item-grid .desc, .eshb-item-grid p',
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
					'selector' => '.eshb-item-grid span.capacity',
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
					'selector' => '.eshb-item-grid span.capacity',
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
                    'selector'=> '.eshb-item-grid .pricing-info .label',                 
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
                    'selector' => '.eshb-item-grid .item-inner .pricing-info .price, .eshb-item-grid .pricing-info .label',                
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
                    '.eshb-item-grid .item-inner .pricing-info .price',     
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
				'selector' => '.eshb-item-grid .details-btn',
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
					'selector' => '.eshb-item-grid .details-btn',
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
					'selector' => '.eshb-item-grid .details-btn',
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
				'selector' => '.eshb-item-grid .details-btn',
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
				'selector' => '.eshb-item-grid .details-btn',
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
					'selector' => '.eshb-item-grid .details-btn:hover',
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
					'selector' => '.eshb-item-grid .details-btn:hover',
					]
				],                  
            ];
         
        
        // Heading (Icon section title)
            $this->controls['heading_icon_styles'] = [
                'tab'       => 'style',
                'group'     => 'features',
                'type'      => 'separator',
                'label'     => esc_html__('Icon', 'easy-hotel'),
                'separator' => 'after',
            ];

            // Icon Color
            $this->controls['all_features_icon_color'] = [
                'tab'       => 'style',
                'group'     => 'features',
                'label'     => esc_html__('Icon Color', 'easy-hotel'),
                'type'      => 'color',
                'css'       => [
                    [
                        'selector' => '.rt_room_slider.eshb-item-grid .all-features .feature i',
                        'property' => 'color',
                    ],
                ],
            ];

            // Icon Size
            $this->controls['icon_size'] = [
                'tab'       => 'style',
                'group'     => 'features',
                'label'     => esc_html__('Icon Size', 'easy-hotel'),
                'type'      => 'slider',
                'units'     => ['px', '%'],
                'range'     => [
                    'px' => ['min' => 1, 'max' => 400],
                    '%'  => ['min' => 1, 'max' => 100],
                ],
                'css'       => [
                    [
                        'selector' => '.rt_room_slider.eshb-item-grid .all-features .feature i',
                        'property' => 'font-size',
                    ],
                ],
            ];

            // Feature Color
            $this->controls['all_features_color'] = [
                'tab'   => 'style',
                'group' => 'features',
                'label' => esc_html__('Color', 'easy-hotel'),
                'type'  => 'color',
                'css'   => [
                    [
                        'selector' => '.rt_room_slider.eshb-item-grid .all-features .feature',
                        'property' => 'color',
                    ],
                ],
            ];

            // Typography (group control)
            $this->controls['all_features_typography'] = [
                'tab'     => 'style',
                'group'   => 'features',
                'label'   => esc_html__('Typography', 'easy-hotel'),
                'type'    => 'typography',
                'css'     => [
                    [
                        'selector' => '.rt_room_slider.eshb-item-grid .all-features .feature',
                    ],
                ],
            ];

            // Padding
            $this->controls['all_features__padding'] = [
                'tab'       => 'style',
                'group'     => 'features',
                'label'     => esc_html__('Padding', 'easy-hotel'),
                'type'      => 'dimensions',
                'units'     => ['px', '%', 'em'],
                'css'       => [
                    [
                        'selector' => '.rt_room_slider.eshb-item-grid .all-features .feature',
                        'property' => 'padding',
                    ],
                ],
            ];

            // Margin
            $this->controls['all_features__margin'] = [
                'tab'       => 'style',
                'group'     => 'features',
                'label'     => esc_html__('Margin', 'easy-hotel'),
                'type'      => 'dimensions',
                'units'     => ['px', '%', 'em'],
                'css'       => [
                    [
                        'selector' => '.rt_room_slider.eshb-item-grid .all-features .feature',
                        'property' => 'margin',
                    ],
                ],
            ];

 


    }

    
    public function render()
    {
        $settings = $this->settings;

		$this->set_attribute( '_root', 'class', ['eshb-room-slider-wrapper'] );

        $btn_text = $settings['btn_text'];
        $thumbnail_size = isset($settings['thumbnail_size']) && !empty($settings['thumbnail_size']) ? $settings['thumbnail_size'] : 'eshb_thumbnail';
        $excerpt_length = isset($settings['excerpt_length']) && !empty($settings['excerpt_length']) ? $settings['excerpt_length'] : 25;
        $pricing_prefix = isset($settings['pricing_prefix']) ? $settings['pricing_prefix'] : '';

        $col_xl         = !empty($settings['col_xl']) ? $settings['col_xl'] : 4;
        $slidesToShow   = $col_xl;

        $autoplaySpeed  = !empty($settings['slider_autoplay_speed']) ? $settings['slider_autoplay_speed'] : 1000;
        $interval       = !empty($settings['slider_interval']) ? $settings['slider_interval'] : 3000;
        $slidesToScroll = !empty($settings['slides_ToScroll']) ? $settings['slides_ToScroll'] : 1;

        $room_slider_autoplay = !empty($settings['slider_autoplay']) && $settings['slider_autoplay'] === 'true' ? 'true' : 'false';
        $pauseOnHover         = !empty($settings['slider_stop_on_hover']) && $settings['slider_stop_on_hover'] === 'true' ? 'true' : 'false';
        $pauseOnInter         = !empty($settings['slider_stop_on_interaction']) && $settings['slider_stop_on_interaction'] === 'true' ? 'true' : 'false';
        $room_sliderDots      = !empty($settings['slider_dots']) && $settings['slider_dots'] === 'true' ? 'true' : 'false';
        $room_sliderNav       = !empty($settings['slider_nav']) && $settings['slider_nav'] === 'true' ? 'true' : 'false';
        $infinite             = !empty($settings['slider_loop']) && $settings['slider_loop'] === 'true' ? 'true' : 'false';
        $centerMode           = !empty($settings['slider_centerMode']) && $settings['slider_centerMode'] === 'true' ? 'true' : 'false';

        $col_lg = !empty($settings['col_lg']) ? $settings['col_lg'] : 3;
        $col_md = !empty($settings['col_md']) ? $settings['col_md'] : 2;
        $col_sm = !empty($settings['col_sm']) ? $settings['col_sm'] : 2;
        $col_xs = !empty($settings['col_xs']) ? $settings['col_xs'] : 1;

        
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
     
        $sstyle = 'style' . $settings['rt_room_slider_style'];

        $eshb_settings = get_option('eshb_settings');
        $string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';

        $blank = "";
        ?>

        <div <?php echo esc_attr($this->render_attributes('_root')) ?>
            data-swiper='<?php echo esc_attr(wp_json_encode([
                "slidesPerView"   => !empty($col_xl) ? (int) $col_xl : 4,
                "speed"           => !empty($autoplaySpeed) ? (int) $autoplaySpeed : 1000,
                "slidesPerGroup"  => !empty($slidesToScroll) ? (int) $slidesToScroll : 1,
                "loop"            => ($infinite === 'true'),
                "autoplay"        => ($room_slider_autoplay === 'true') ? ["delay" => (int) $interval, "disableOnInteraction" => false] : false,
                "spaceBetween"    => !empty($item_gap) ? (int) $item_gap : 30,
                "centeredSlides"  => ($centerMode === 'true'),
                "pagination"      => ($room_sliderDots === 'true') ? ["el" => ".swiper-pagination", "clickable" => true] : false,
                "navigation"      => ($room_sliderNav === 'true') ? [
                    "nextEl" => ".rt_room_slider-btn-wrapper-$unique .swiper-button-next",
                    "prevEl" => ".rt_room_slider-btn-wrapper-$unique .swiper-button-prev"
                ] : false,
                "breakpoints"     => [
                    575  => !empty($col_xs) ? ["slidesPerView" => (int) $col_xs] : null,
                    767  => !empty($col_sm) ? ["slidesPerView" => (int) $col_sm] : null,
                    991  => !empty($col_md) ? ["slidesPerView" => (int) $col_md] : null,
                    1199 => !empty($col_lg) ? ["slidesPerView" => (int) $col_lg] : null,
                    1399 => ["slidesPerView" => (int) $col_xl, "spaceBetween" => (int) $item_gap],
                ]
            ])); ?>'>

            <div class="room_slider-inner-wrapper room_slider-inner-wrapper-<?php echo esc_attr($unique); ?> section-dark eshb-text-light no-top no-bottom position-relative overflow-hidden z-1000">            
                <div class="swiper rt_room_slider-<?php echo esc_attr($unique); ?> rt_room_slider <?php echo esc_attr($sstyle)?> eshb-item-grid">
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

            <?php if ($room_sliderDots === 'true' || $room_sliderNav === 'true'): ?>
                <div class="rt_room_slider-btn-wrapper rt_room_slider-btn-wrapper-<?php echo esc_attr($unique); ?>">
                    <div class="swiper-pagination"></div>
                    <div class="nav-btn swiper-button-prev"></div>
                    <div class="nav-btn swiper-button-next"></div>
                    <div class="swiper-scrollbar"></div>
                </div>
            <?php endif; ?>
            <?php
            wp_add_inline_script(
                'eshb-public-script',
                "
                (function($){
                    var swiperTimeout = 0;
                    if ($('body').hasClass('wp-theme-bricks') && !$('body').hasClass('bricks-is-frontend')) {
                        var bricksTimeouts = window.bricksTimeouts || {};
                        if (Object.keys(bricksTimeouts).length > 0) {
                            swiperTimeout = bricksTimeouts.bricksSwiper || 250; 
                        } else {
                            swiperTimeout = 2000;
                        }
                    }
                    setTimeout(() => {
                        document.querySelectorAll('[data-swiper]').forEach(function(el) {
                            let config = JSON.parse(el.getAttribute('data-swiper'));
                            console.log('config', config);
                            new Swiper(el.querySelector('.swiper'), config);
                        });
                    }, swiperTimeout);
                })(jQuery);
                "
            );
            ?>
           
        </div>

        
<?php
    }
}