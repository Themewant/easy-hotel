<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
?>
<div class="easy-hotel">
    <div class="eshb-archive-wrapper eshb-container">
        <?php
            $search = new ESHB_Search();
            $default_start_end_date = ESHB_Helper::get_eshb_default_start_end_date();
            $start_date = $default_start_end_date['start_date'];
            $end_date = $default_start_end_date['end_date'];
            $adult_quantity = 1;
            $children_quantity = 0;
            $room_quantity = 1;

            if (isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['nonce'])), ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action'))) {
                $start_date = !empty( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash($_GET['start_date'])) : $start_date;
                $end_date = !empty( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash($_GET['end_date'])) : $end_date;
                $adult_quantity = !empty( $_GET['adult_quantity'] ) ? sanitize_text_field( wp_unslash($_GET['adult_quantity'] ) ): 1;
                $children_quantity = !empty( $_GET['children_quantity'] ) ? sanitize_text_field( wp_unslash($_GET['children_quantity'] )) : 0;
                $room_quantity = !empty( $_GET['room_quantity'] ) ? sanitize_text_field( wp_unslash($_GET['room_quantity'] )) : 1;
            }

            $available_accomodation_ids = $search->eshb_get_available_accomodation_ids($start_date, $end_date, $adult_quantity, $children_quantity, $room_quantity);

            if(is_array($available_accomodation_ids) && count($available_accomodation_ids) > 0){

                $eshb_settings = get_option( 'eshb_settings' );
                $posts_per_page = isset($eshb_settings['accomodation_posts_per_page']) && !empty($eshb_settings['accomodation_posts_per_page']) ? $eshb_settings['accomodation_posts_per_page'] : 6;
                $posts_per_row = isset($eshb_settings['accomodation_posts_per_row']) && !empty($eshb_settings['accomodation_posts_per_row']) ? $eshb_settings['accomodation_posts_per_row'] : 3;
                $posts_order_by = isset($eshb_settings['accomodation_posts_order_by']) && !empty($eshb_settings['accomodation_posts_order_by']) ? $eshb_settings['accomodation_posts_order_by'] : 'id';
                $posts_order = isset($eshb_settings['accomodation_posts_order']) && !empty($eshb_settings['accomodation_posts_order']) ? $eshb_settings['accomodation_posts_order'] : 'DESC';
                $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
                $available_accomodations_args = array(
                    'post_type' => 'eshb_accomodation',
                    'post__in'  => $available_accomodation_ids,
                    'paged'          => $paged,
                    'posts_per_page'  => $posts_per_page,
                    'orderby' => $posts_order_by,
                    'order' => $posts_order
                );

                $available_accomodations = new WP_Query($available_accomodations_args);

                $view = new ESHB_View();
                $template = $view->eshb_get_accomodation_grid($available_accomodations, $adult_quantity, $children_quantity, $posts_per_row, 'eshb_thumbnail', 1, $start_date, $end_date );

            }else {
                ?>
                    <p class="eshb-search-error"><?php esc_html__( 'No available accommodations found for the selected dates.', 'easy-hotel' ) ?></p>
                <?php
            }
        ?>
    </div>
</div>