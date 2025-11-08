<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$settings = [];
$thumbnail_size = 'eshb_thumbnail';
$excerpt_length = 25;
$col_xl          = 3;
$slidesToShow    = $col_xl;
$autoplaySpeed   = '1000';
$interval        = '3000';
$room_slider_autoplay = 'false';
$room_sliderDots      = 'false';
$room_sliderNav       = 'true';
$infinite        = 'true';
$centerMode      = 'false';
$col_lg          = 3;
$col_md          = 3;
$col_sm          = 1;
$col_xs          = 1;
$item_gap        = 30;
$unique          = wp_rand(2012, 35120);
$seffect = '';
$sstyle = 'style1';
$blank = "";
$per_page = -1;
$cat = '';
$nonce_action = ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action');
$nonce = wp_create_nonce($nonce_action);
$eshb_settings = get_option( 'eshb_settings' );
$pricing_prefix = isset($eshb_settings['string_from']) && !empty($eshb_settings['string_from']) ? $eshb_settings['string_from'] : '';
$btn_text = isset($eshb_settings['view_details']) && !empty($eshb_settings['view_details']) ? $eshb_settings['view_details'] : '';
$posts_per_page = isset($eshb_settings['accomodation_posts_per_page']) && !empty($eshb_settings['accomodation_posts_per_page']) ? $eshb_settings['accomodation_posts_per_page'] : 6;
$pricing_prefix = ($pricing_prefix) ? $pricing_prefix : __('From', 'easy-hotel') ;
$btn_text = ($btn_text) ? $btn_text : __('View Details', 'easy-hotel') ;
$string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';
$default_start_end_date = ESHB_Helper::get_eshb_default_start_end_date();
$start_date = $default_start_end_date['start_date'];
$end_date = $default_start_end_date['end_date'];
?>


    <div class=" room_slider-inner-wrapper room_slider-inner-wrapper-<?php echo esc_attr($unique); ?> section-dark text-light no-top no-bottom position-relative overflow-hidden z-1000">            
      
        <div class="swiper rt_room_slider-<?php echo esc_attr($unique); ?> rt_room_slider <?php echo esc_attr( $sstyle )?> eshb-item-grid">
            <div class="swiper-wrapper">
                <?php
                
                    $hotel_core = new ESHB_Core();

                    $args = array(
                        'post_type'      => 'eshb_accomodation',
                        'posts_per_page' => $posts_per_page,								
                    );
    
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
                       
                        $total_capacity = $eshb_accomodation_metaboxes['total_capacity'];
                        $price = $hotel_core->get_eshb_price_html('', '', $accomodation_id);
                        $numeric_price = $hotel_core->get_eshb_price('', '', $accomodation_id);
                        $perodicity_string = apply_filters( 'eshb_perodicity_string_in_loop', $string_night, $accomodation_id, $eshb_settings);

                        $booking_url = add_query_arg( 
                            array( 
                                'nonce' => $nonce,
                                'start_date' => $start_date, 
                                'end_date' => $end_date, 
                                'adult_quantity' => $adult_quantity, 
                                'children_quantity' => $children_quantity,
                            ), 
                            get_the_permalink($accomodation_id) 
                        );

                       require ESHB_PL_PATH . 'public/includes/widgets/room-slider/' . $sstyle.".php";
                    
                    endwhile;
    
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
        var swiper<?php echo esc_attr($unique); ?> = new Swiper(".rt_room_slider-<?php echo esc_attr($unique); ?>", {
            slidesPerView: 3,
            speed: <?php echo esc_attr($autoplaySpeed); ?>,
            slidesPerGroup: 1,
            loop: <?php echo esc_attr($infinite); ?>,
            spaceBetween: <?php echo esc_attr($item_gap); ?>,
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
                    slidesPerView: 3,
                    spaceBetween: <?php echo esc_attr($item_gap); ?>
                }
            }
        });
    });
</script>
<?php
