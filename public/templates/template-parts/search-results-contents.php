<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
?>
<div class="easy-hotel">
    <div class="eshb-archive-wrapper eshb-search-result-wrapper eshb-container">
        <?php
            $search = new ESHB_Search();
            $eshb_default_start_end_date = ESHB_Helper::get_eshb_default_start_end_date();
            $eshb_start_date = $eshb_default_start_end_date['start_date'];
            $eshb_end_date = $eshb_default_start_end_date['end_date'];
            $eshb_adult_quantity = 1;
            $eshb_children_quantity = 0;
            $eshb_room_quantity = 1;

            if (isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
                $eshb_start_date = !empty( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash($_GET['start_date'])) : $eshb_start_date;
                $eshb_end_date = !empty( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash($_GET['end_date'])) : $eshb_end_date;
                $eshb_adult_quantity = !empty( $_GET['adult_quantity'] ) ? sanitize_text_field( wp_unslash($_GET['adult_quantity'] ) ): 1;
                $eshb_children_quantity = !empty( $_GET['children_quantity'] ) ? sanitize_text_field( wp_unslash($_GET['children_quantity'] )) : 0;
                $eshb_room_quantity = !empty( $_GET['room_quantity'] ) ? sanitize_text_field( wp_unslash($_GET['room_quantity'] )) : 1;
            }

            $eshb_available_accomodation_ids = $search->eshb_get_available_accomodation_ids($eshb_start_date, $eshb_end_date, $eshb_adult_quantity, $eshb_children_quantity, $eshb_room_quantity);

            if(is_array($eshb_available_accomodation_ids) && count($eshb_available_accomodation_ids) > 0){

                $eshb_settings = get_option( 'eshb_settings' );
                $eshb_posts_per_page = isset($eshb_settings['accomodation_posts_per_page']) && !empty($eshb_settings['accomodation_posts_per_page']) ? $eshb_settings['accomodation_posts_per_page'] : 6;
                $eshb_posts_per_row = isset($eshb_settings['accomodation_posts_per_row']) && !empty($eshb_settings['accomodation_posts_per_row']) ? $eshb_settings['accomodation_posts_per_row'] : 3;
                $eshb_posts_order_by = isset($eshb_settings['accomodation_posts_order_by']) && !empty($eshb_settings['accomodation_posts_order_by']) ? $eshb_settings['accomodation_posts_order_by'] : 'id';
                $eshb_posts_order = isset($eshb_settings['accomodation_posts_order']) && !empty($eshb_settings['accomodation_posts_order']) ? $eshb_settings['accomodation_posts_order'] : 'DESC';
                $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
                $eshb_style = isset($eshb_settings['archive-page-template-style']) && !empty($eshb_settings['archive-page-template-style']) ? $eshb_settings['archive-page-template-style'] : 'style-one';
                $eshb_available_accomodations_args = array(
                    'post_type' => 'eshb_accomodation',
                    'post__in'  => $eshb_available_accomodation_ids,
                    'paged'          => $paged,
                    'posts_per_page'  => $eshb_posts_per_page,
                    'orderby' => $eshb_posts_order_by,
                    'order' => $eshb_posts_order
                );

                $eshb_available_accomodations = new WP_Query($eshb_available_accomodations_args);

                $eshb_view = new ESHB_View();
                $eshb_template = $eshb_view->eshb_get_accomodation_grid($eshb_available_accomodations, $eshb_adult_quantity, $eshb_children_quantity, $eshb_posts_per_row, 'eshb_thumbnail',  $eshb_style, $eshb_start_date, $eshb_end_date );

            }else {
                ?>
                    <p class="eshb-search-error"><?php echo esc_html__( 'No available accommodations found for the selected dates.', 'easy-hotel' ) ?></p>
                <?php
            }
        ?>
    </div>
</div>