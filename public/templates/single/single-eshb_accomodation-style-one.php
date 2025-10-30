<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
get_header();
if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
    echo wp_kses_post(do_blocks('<!-- wp:template-part {"slug":"header"} /-->'));
}

$ESHB_View = new ESHB_View(); 
$accomodation_id = get_the_ID();
$eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);

$accomodation_info_group = !empty($eshb_accomodation_metaboxes['accomodation_info_group']) ? $eshb_accomodation_metaboxes['accomodation_info_group'] : array();
$eshb_settings = get_option('eshb_settings');
$booking_form_style = isset($eshb_settings['booking-form-style']) && !empty($eshb_settings['booking-form-style']) ? $eshb_settings['booking-form-style'] : 'style-two';

if (isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
    $booking_form_style = !empty($_GET['booking-form-style']) ? sanitize_text_field(wp_unslash($_GET['booking-form-style'])) : $booking_form_style;
}

$booking_type = isset($eshb_settings['booking-type']) && !empty($eshb_settings['booking-type']) ? $eshb_settings['booking-type'] : 'woocommerce';
$hotel_core = new ESHB_Core();
$price = $hotel_core->get_eshb_price_html('', '', $accomodation_id);
$numeric_price = $hotel_core->get_eshb_price('', '', $accomodation_id);
$booking_form = $eshb_settings['booking-form'] ?? true;
$post_class = 'eshb-details-page';
$post_class = !$booking_form || $booking_type == 'disable' || empty($numeric_price) ? $post_class . ' eshb-disabled-booking' : $post_class;
$related_accomodations = isset($eshb_settings['related-accomodation-switcher-in-single']) && !empty($eshb_settings['related-accomodation-switcher-in-single']) ? $eshb_settings['related-accomodation-switcher-in-single'] : '';

$string_related_sub_title = isset($eshb_settings['string_related_sub_title']) && !empty($eshb_settings['string_related_sub_title']) ? $eshb_settings['string_related_sub_title'] : '';
$string_related__title = isset($eshb_settings['string_related__title']) && !empty($eshb_settings['string_related__title']) ? $eshb_settings['string_related__title'] : '';

$show_day_wise_pricing = $eshb_settings['day-wise-pricing'] ?? true;
$show_check_in_out_time = $eshb_settings['check-in-out-time'] ?? true;
$show_availability_calendar = $eshb_settings['availability-calendar'] ?? true;
$show_gallery = $eshb_settings['accomodation-gallery'] ?? true;


?>
    <div <?php echo esc_attr(post_class($post_class)) ?>>
        <div class="eshb-accomodation-gallery-section">
                <?php
            if($show_gallery) {
                echo esc_html($ESHB_View->eshb_get_gallery_html($accomodation_id, '', 'full', false));
            }
            ?>
        </div>
        <div class="eshb-container">
            <?php 
                while ( have_posts() ) : the_post(); ?>
                    <div class="eshb-row">

                        <div id="eshb-contents">
                            <h1 class="eshb-single-title"> <?php  the_title(); ?> </h1>
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
                                
                                if($show_day_wise_pricing){
                                    $ESHB_View->eshb_day_wise_pricing_table_html($accomodation_id, true); 
                                }
                                
                                ?>
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

                        <div id="eshb-aside">
                            <?php
                                if($booking_form && $booking_type != 'disable' && !empty($numeric_price)){
                                    $booking_form = $ESHB_View->eshb_get_booking_form_html($accomodation_id, $booking_form_style);
                                    echo esc_html( $booking_form );
                                }
                            ?>
                        </div>
                    </div>
                
                <?php 

                
                
                endwhile; wp_reset_postdata();
            ?>

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
                
                    include $related_room_slider_template;
                }
            ?>
        </div>        
    </div>
<?php

get_footer();

if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
    echo wp_kses_post(do_blocks('<!-- wp:template-part {"slug":"footer"} /-->'));
} 
?>