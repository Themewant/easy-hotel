<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
get_header();
$hotel_core = new ESHB_Core();
$accomodation_id = get_the_ID();
$eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
$eshb_accomodation_metaboxes_side = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes_side', true);

$accomodation_gallery = $eshb_accomodation_metaboxes_side['accomodation_gallery'];
$accomodation_video = !empty($eshb_accomodation_metaboxes_side['accomodation_video']) ? $eshb_accomodation_metaboxes_side['accomodation_video'] : '';
$accomodation_video_source = !empty($eshb_accomodation_metaboxes_side['accomodation_video_source']) ? $eshb_accomodation_metaboxes_side['accomodation_video_source'] : '';
$accomodation_gallery_ids = explode( ',', $accomodation_gallery );
$accomodation_gallery_slides_per_view = !empty($eshb_accomodation_metaboxes_side['slides_per_view']) ? $eshb_accomodation_metaboxes_side['slides_per_view'] : '2.1';
$accomodation_video_height = !empty($eshb_accomodation_metaboxes_side['accomodation_video_height']) ? $eshb_accomodation_metaboxes_side['accomodation_video_height'] : 0;

$accomodation_info_group = !empty($eshb_accomodation_metaboxes['accomodation_info_group']) ? $eshb_accomodation_metaboxes['accomodation_info_group'] : array();
$eshb_settings = get_option('eshb_settings');
$booking_type = isset($eshb_settings['booking-type']) && !empty($eshb_settings['booking-type']) ? $eshb_settings['booking-type'] : 'woocommerce';
$external_booking_link = isset($eshb_settings['external-booking-link']) && !empty($eshb_settings['external-booking-link']) ? $eshb_settings['external-booking-link'] : false;
$booking_form_style = isset($eshb_settings['booking-form-style']) && !empty($eshb_settings['booking-form-style']) ? $eshb_settings['booking-form-style'] : 'style-one';

if (isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
    $booking_form_style = !empty($_GET['booking-form-style']) ? sanitize_text_field(wp_unslash($_GET['booking-form-style'])) : $booking_form_style;
}

$price = $hotel_core->get_eshb_price_html('', '', $accomodation_id);
$numeric_price = $hotel_core->get_eshb_price('', '', $accomodation_id);

// Translations
$string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';
$string_book_now = isset($eshb_settings['string_book_now']) && !empty($eshb_settings['string_book_now']) ? $eshb_settings['string_book_now'] : 'Book Now';
$post_class = 'eshb-details-page style-two';
$post_class = $booking_type == 'disable' ? $post_class . ' eshb-disabled-booking' : $post_class;
$related_accomodations = isset($eshb_settings['related-accomodation-switcher-in-single']) && !empty($eshb_settings['related-accomodation-switcher-in-single']) ? $eshb_settings['related-accomodation-switcher-in-single'] : '';

$show_day_wise_pricing = $eshb_settings['day-wise-pricing'] ?? true;
$show_check_in_out_time = $eshb_settings['check-in-out-time'] ?? true;
$show_availability_calendar = $eshb_settings['availability-calendar'] ?? true;
$show_gallery = $eshb_settings['accomodation-gallery'] ?? true;

$ESHB_View = new ESHB_View(); 

?>
    <div <?php echo esc_attr(post_class($post_class)) ?>>

        <div class="container">
            <?php 
                if($booking_type != 'disable' && !empty($numeric_price)){
                    ?>
                        <div class="minimal-booking">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
                                    <div class="row g-0">
                                        <div class="col-md-6 text-center">
                                            <div class="left-col text-light p-4 h-100 fadeInRight">
                                                <div class="de_count fs-15 fadeInRight animated">
                                                    <h2 class="mb-0"><?php echo wp_kses_post($price); ?></h2>
                                                    <span><?php echo esc_html( $string_night ) ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 text-center">
                                            <div class="right-col text-light p-4 h-100 fadeInRight bgcustom animated">
                                                <div class="de_count fs-15 fadeInRight">
                                                    <?php 
                                                        if($booking_type != 'woocommerce'){ ?>
                                                            <a class="btn-main btn-line no-bg mt-lg-4" href="<?php echo esc_url( $external_booking_link ); ?>" target="_blank"><?php echo esc_html( $string_book_now ) ?></a>
                                                    <?php }else{ ?>
                                                            <a class="easy-hotel-toggler-booking-form-modal btn-main btn-line no-bg mt-lg-4" href="#"><?php echo esc_html( $string_book_now ) ?></a>
                                                        <?php }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="easy-hotel-booking-modal" style="display: none;">
                                <div class="easy-hotel-booking-overlay"></div>
                                <div class="easy-hotel-booking-modal-body">
                                    <button type="button" class="easy-hotel-booking-modal-closer"><span class="dashicons dashicons-no"></span></button>
                                    <div class="easy-hotel-booking-modal-content">
                                        <?php
                                            $booking_form = $ESHB_View->eshb_get_booking_form_html($accomodation_id, $booking_form_style);
                                            echo esc_html( $booking_form );
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                }
                
            ?>
            
           
            
            <div class="row justify-content-center">
                <div class="col-lg-7 text-center">
                    <h3 class="fadeInUp animated excerpt">
                        <?php echo esc_html( get_the_excerpt() ); ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="eshb-accomodation-gallery-section">
            <?php
            if($show_gallery) {
                echo wp_kses_post($ESHB_View->eshb_get_gallery_html($accomodation_id, '', 'full', false));
            }
            ?>
        </div>
            
                        
        <div class="eshb-container contents-container">

            <?php 
                while ( have_posts() ) : the_post(); ?>
                    <div class="eshb-row">

                        <div id="eshb-contents" class="full-width">
                           
                            <div class="eshb-contnents-inner">
                                <div class="basic-information-list">
                                <?php 
                                    if ( ! empty( $accomodation_info_group ) ) {
                                        foreach ( $accomodation_info_group as $group ) { ?>
                                            <p class="info">
                                                <?php 
                                                    if(!empty($group['info_icon'])){ ?>
                                                        <i class="info-icon <?php echo esc_html($group['info_icon']); ?>"></i>
                                                   <?php }

                                                    if(!empty($group['info_icon_img']['url'])){ 
                                                        $icon_img_url = $group['info_icon_img']['url'];
                                                        ?>
                                                        <img src="<?php echo esc_url($icon_img_url); ?>" alt="info Icon" class="info-icon">
                                                    <?php }
                                                ?>
                                                
                                                <span class="info-title"><?php echo esc_html($group['info_title']); ?></span>
                                            </p>
                                        <?php }
                                    }
                                ?>
                                </div>
                                <?php 
                                 // Bricks content render
                                 $bricks_data = false;
                                 if(class_exists('Bricks\Helpers')){
                                    $bricks_data = Bricks\Helpers::get_bricks_data( get_the_ID(), 'content' );
                                 }
                                 

                                if ( $bricks_data ) {
                                    Bricks\Frontend::render_content( $bricks_data );
                                }else{
                                    the_content();
                                }
                                ?>
                                <?php  
                                    if($show_day_wise_pricing){
                                        $ESHB_View->eshb_day_wise_pricing_table_html($accomodation_id, true); 
                                    }
                                ?>
                                <?php 
                                if($show_check_in_out_time){
                                    $ESHB_View->eshb_get_eshb_check_in_out_times_html(true);
                                }
                                ?>
                                <?php 
                                if($show_availability_calendar){
                                    $ESHB_View->eshb_get_availability_calendar_html(); 
                                }
                                ?>
                            </div>
                            <?php do_action('eshb_after_single_accomodation_content'); ?>
                        </div>
                    </div>
                <?php 
               

               endwhile; wp_reset_postdata();
            ?>
        </div>
        <?php 
            if($related_accomodations){             
                // Check if the custom template exists in your plugin directory
                $related_room_slider_plugin_template = ESHB_PL_PATH . 'public/templates/template-parts/related-room-slider.php';
                $related_room_slider_theme_template = get_stylesheet_directory() . '/easy-hotel/templates/template-parts/related-room-slider.php';
                $related_room_slider_child_theme_template = get_template_directory() . '/easy-hotel/templates/template-parts/related-room-slider.php';

                if (file_exists($related_room_slider_child_theme_template)) {
                    $related_room_slider_template = $related_room_slider_child_theme_template;
                }else if (file_exists($related_room_slider_theme_template)) {
                    $related_room_slider_template = $related_room_slider_theme_template;
                } else {
                    $related_room_slider_template = $related_room_slider_plugin_template;
                }
            
                require $related_room_slider_template;
            }
        ?>
        

    </div>
<?php

get_footer();

