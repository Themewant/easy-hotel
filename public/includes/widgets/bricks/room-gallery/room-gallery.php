<?php
defined('ABSPATH') || die();

class Eshb_Room_Gallery_Widget extends \Bricks\Element {

    public $category     = 'general'; // Use predefined element category 'general'
	public $name         = 'eshb-room-room-gallery'; // Make sure to prefix your elements
	public $icon         = 'ti-header'; // Themify icon font class
	public $tag          = 'div';
	// Called by the Bricks builder after each re-render (see public/assets/js/swiper-init.js).
	public $scripts      = [ 'eshbInitSwipers' ];


    /**
     * Get widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */

    public function get_label()
    {
        return esc_html__('Easy Hotel Room Gallery', 'easy-hotel');
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
	
	}
    
    public function set_controls()
    {

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
                
        
    }

    
    public function enqueue_scripts() {
        // Swiper and the plugin styles, loaded only where this element is used.
        eshb_enqueue_frontend_assets();
    }

    public function render()
    {
        $settings = $this->settings;

		$this->set_attribute( '_root', 'class', ['eshb-room-gallery-wrapper'] );

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
            $room_slider_autoplay = array(
                'delay'                => (int) $interval,
                'pauseOnMouseEnter'    => $pauseOnHover == 'true',
                'disableOnInteraction' => $pauseOnInter == 'true',
            );
        } else {
            $room_slider_autoplay = false;
        }

        $effect = $settings['rt_pslider_effect'] ?? '';

        if ($effect == 'fade') {
            $seffect = array( 'effect' => 'fade', 'fadeEffect' => array( 'crossFade' => true ) );
        } elseif ($effect == 'creative') {
            $seffect = array(
                'effect'         => 'creative',
                'creativeEffect' => array(
                    'prev' => array( 'translate' => array( 0, 0, -400 ) ),
                    'next' => array( 'translate' => array( '100%', 0, 0 ) ),
                ),
            );
        } elseif ( in_array( $effect, array( 'cube', 'flip', 'coverflow', 'cards' ), true ) ) {
            $seffect = array( 'effect' => $effect );
        } else {
            $seffect = array();
        }


        $ESHB_View = new ESHB_View();

        $accomodation_id = get_the_ID();
        ?>
        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Bricks escapes each attribute value in render_attributes(). ?>
        <div <?php echo $this->render_attributes('_root'); ?>>
            <?php $ESHB_View->eshb_get_gallery_html($accomodation_id, $unique, $thumbnail_size, $room_sliderDots, $room_sliderNav); // Prints the gallery itself. ?>
        </div>
        <?php
        $gallery_selector = '.has-accomodation-gallery-' . $unique;

        $breakpoints = array();
        foreach ( array( 575 => $col_xs, 767 => $col_sm, 991 => $col_md, 1199 => $col_lg ) as $width => $cols ) {
            if ( ! empty( $cols ) ) {
                $breakpoints[ $width ] = array( 'slidesPerView' => (float) $cols );
            }
        }
        $breakpoints[1399] = array(
            'slidesPerView' => (float) $col_xl,
            'spaceBetween'  => (int) $item_gap,
        );

        $options = array_merge(
            array( 'slidesPerView' => 1 ),
            $seffect,
            array(
                'speed'          => (int) $autoplaySpeed,
                'slidesPerGroup' => 1,
                'loop'           => $infinite === 'true',
                'autoplay'       => $room_slider_autoplay,
                'spaceBetween'   => (int) $item_gap,
                'centeredSlides' => $centerMode === 'true',
            )
        );
        if ( $room_sliderNav == 'true' ) {
            $options['navigation'] = array(
                'nextEl' => $gallery_selector . ' .swiper-button-next',
                'prevEl' => $gallery_selector . ' .swiper-button-prev',
            );
        }
        if ( $room_sliderDots == 'true' ) {
            $options['pagination'] = array(
                'el'        => $gallery_selector . ' .swiper-pagination',
                'clickable' => true,
            );
        }
        $options['breakpoints'] = $breakpoints;

        eshb_print_swiper_config( $gallery_selector, $options );
    }
}