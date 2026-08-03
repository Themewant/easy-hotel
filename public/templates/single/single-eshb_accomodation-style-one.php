<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
get_header();
if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
    echo wp_kses_post(do_blocks('<!-- wp:template-part {"slug":"header"} /-->'));
}

$ESHB_View = new ESHB_View(); 
$eshb_accomodation_id = get_the_ID();
$eshb_accomodation_metaboxes = get_post_meta($eshb_accomodation_id, 'eshb_accomodation_metaboxes', true);

$eshb_accomodation_info_group = !empty($eshb_accomodation_metaboxes['accomodation_info_group']) ? $eshb_accomodation_metaboxes['accomodation_info_group'] : array();
$eshb_settings = get_option('eshb_settings');
$eshb_booking_form_style = isset($eshb_settings['booking-form-style']) && !empty($eshb_settings['booking-form-style']) ? $eshb_settings['booking-form-style'] : 'style-two';

if (isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
    $eshb_booking_form_style = !empty($_GET['booking-form-style']) ? sanitize_text_field(wp_unslash($_GET['booking-form-style'])) : $eshb_booking_form_style;
}

$eshb_booking_type = isset($eshb_settings['booking-type']) && !empty($eshb_settings['booking-type']) ? $eshb_settings['booking-type'] : 'woocommerce';
$eshb_hotel_core = new ESHB_Core();
$eshb_price = $eshb_hotel_core->get_eshb_price_html('', '', $eshb_accomodation_id);
$eshb_numeric_price = $eshb_hotel_core->get_eshb_price('', '', $eshb_accomodation_id);
$eshb_booking_form = $eshb_settings['booking-form'] ?? true;
$eshb_post_class = 'eshb-details-page';
$eshb_post_class = !$eshb_booking_form || $eshb_booking_type == 'disable' || empty($eshb_numeric_price) ? $eshb_post_class . ' eshb-disabled-booking' : $eshb_post_class;
$eshb_related_accomodations = isset($eshb_settings['related-accomodation-switcher-in-single']) && !empty($eshb_settings['related-accomodation-switcher-in-single']) ? $eshb_settings['related-accomodation-switcher-in-single'] : '';

$eshb_string_related_sub_title = isset($eshb_settings['string_related_sub_title']) && !empty($eshb_settings['string_related_sub_title']) ? $eshb_settings['string_related_sub_title'] : '';
$eshb_string_related__title = isset($eshb_settings['string_related__title']) && !empty($eshb_settings['string_related__title']) ? $eshb_settings['string_related__title'] : '';

$eshb_show_day_wise_pricing = $eshb_settings['day-wise-pricing'] ?? true;
$eshb_show_check_in_out_time = $eshb_settings['check-in-out-time'] ?? true;
$eshb_show_availability_calendar = $eshb_settings['availability-calendar'] ?? true;
$eshb_show_gallery = $eshb_settings['accomodation-gallery'] ?? true;


?>
    <div <?php echo esc_attr(post_class($eshb_post_class)) ?>>
        <div class="eshb-accomodation-gallery-section">
                <?php
            if($eshb_show_gallery) {
                echo esc_html($ESHB_View->eshb_get_gallery_html($eshb_accomodation_id, '', 'full', false));
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
                                    if ( ! empty( $eshb_accomodation_info_group ) ) {
                                        foreach ( $eshb_accomodation_info_group as $eshb_group ) { ?>
                                            <p class="info">
                                                <?php 
                                                    if(!empty($eshb_group['info_icon'])){ ?>
                                                        <i class="info-icon <?php echo esc_html($eshb_group['info_icon']); ?>"></i>
                                                   <?php }

                                                    if(!empty($eshb_group['info_icon_img']['url'])){ 
                                                        $eshb_icon_img_url = $eshb_group['info_icon_img']['url'];
                                                        ?>
                                                        <img src="<?php echo esc_url($eshb_icon_img_url); ?>" alt="info Icon" class="info-icon">
                                                    <?php }
                                                ?>
                                                
                                                <span class="info-title"><?php echo esc_html($eshb_group['info_title']); ?></span>
                                            </p>
                                        <?php }
                                    }
                                ?>
                                </div>
                                <?php 
                                
                                if($eshb_show_day_wise_pricing){
                                    $ESHB_View->eshb_day_wise_pricing_table_html($eshb_accomodation_id, true); 
                                }
                                
                                ?>
                                <?php 
                                 // Bricks content render
                                 $eshb_bricks_data = false;
                                 if(class_exists('Bricks\Helpers')){
                                    $eshb_bricks_data = Bricks\Helpers::get_bricks_data( get_the_ID(), 'content' );
                                 }
                                 

                                if ( $eshb_bricks_data ) {
                                    Bricks\Frontend::render_content( $eshb_bricks_data );
                                }else{
                                    the_content();
                                }
                                
                                ?>
                                
                              
                                <?php 
                                if($eshb_show_check_in_out_time){
                                    $ESHB_View->eshb_get_eshb_check_in_out_times_html(true);
                                }
                                ?>
                                <?php 
                                if($eshb_show_availability_calendar){
                                    $ESHB_View->eshb_get_availability_calendar_html(); 
                                }
                                ?>
                            </div>
                            <?php do_action('eshb_after_single_accomodation_content'); ?>
                        </div>

                        <div id="eshb-aside">
                            <?php
                                if($eshb_booking_form && $eshb_booking_type != 'disable' && !empty($eshb_numeric_price)){
                                    $eshb_booking_form = $ESHB_View->eshb_get_booking_form_html($eshb_accomodation_id, $eshb_booking_form_style);
                                    echo esc_html( $eshb_booking_form );
                                }
                            ?>
                        </div>
                    </div>
                
                <?php 

                
                
                endwhile; wp_reset_postdata();
            ?>

            <?php 
                if($eshb_related_accomodations){             
                    // Check if the custom template exists in your plugin directory
                    $eshb_related_room_slider_plugin_template = ESHB_PL_PATH . 'public/templates/template-parts/related-room-slider.php';
                    $eshb_related_room_slider_theme_template = get_stylesheet_directory() . '/easy-hotel/templates/template-parts/related-room-slider.php';
                    $eshb_related_room_slider_child_theme_template = get_template_directory() . '/easy-hotel/templates/template-parts/related-room-slider.php';

                    if (file_exists($eshb_related_room_slider_child_theme_template)) {
                        $eshb_related_room_slider_template = $eshb_related_room_slider_child_theme_template;
                    }else if (file_exists($eshb_related_room_slider_theme_template)) {
                        $eshb_related_room_slider_template = $eshb_related_room_slider_theme_template;
                    } else {
                        $eshb_related_room_slider_template = $eshb_related_room_slider_plugin_template;
                    }
                
                    include $eshb_related_room_slider_template;
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