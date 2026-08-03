<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
// The unprefixed names below are the contract with
// public/includes/widgets/room-slider/styleN.php, which is also fed by the
// Elementor room-slider widget. Renaming them here would break that partial.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Slider partial contract.
$settings = [];
$thumbnail_size = 'eshb_thumbnail';
$excerpt_length = 25;
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$eshb_col_xl          = 3;
$eshb_slides_to_show  = $eshb_col_xl;
$eshb_autoplay_speed  = '1000';
$eshb_interval        = '3000';
$eshb_room_slider_autoplay = 'false';
$eshb_room_slider_dots     = 'false';
$eshb_room_slider_nav      = 'true';
$eshb_infinite        = 'false';
$eshb_center_mode     = 'false';
$eshb_col_lg          = 3;
$eshb_col_md          = 3;
$eshb_col_sm          = 1;
$eshb_col_xs          = 1;
$eshb_item_gap        = 30;
$eshb_unique          = wp_rand(2012, 35120);
$eshb_seffect = '';
$eshb_sstyle = 'style1';
$eshb_blank = "";
$per_page = -1;
$cat = '';
$eshb_nonce_action = ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action');
$eshb_nonce = wp_create_nonce($eshb_nonce_action);
$eshb_settings = get_option( 'eshb_settings' );
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Slider partial contract.
$pricing_prefix = isset($eshb_settings['string_from']) && !empty($eshb_settings['string_from']) ? $eshb_settings['string_from'] : '';
$btn_text = isset($eshb_settings['view_details']) && !empty($eshb_settings['view_details']) ? $eshb_settings['view_details'] : '';
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$eshb_posts_per_page = isset($eshb_settings['accomodation_posts_per_page']) && !empty($eshb_settings['accomodation_posts_per_page']) ? $eshb_settings['accomodation_posts_per_page'] : 6;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Slider partial contract.
$pricing_prefix = ($pricing_prefix) ? $pricing_prefix : __('From', 'easy-hotel') ;
$btn_text = ($btn_text) ? $btn_text : __('View Details', 'easy-hotel') ;
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$eshb_default_start_end_date = ESHB_Helper::get_eshb_default_start_end_date();
$eshb_start_date = $eshb_default_start_end_date['start_date'];
$eshb_end_date = $eshb_default_start_end_date['end_date'];


$eshb_hotel_core = new ESHB_Core();
$hotel_view = new ESHB_View(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Slider partial contract.

$eshb_settings = get_option('eshb_settings');
$eshb_string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';
$eshb_current_post_id = get_the_ID();

$cat = wp_get_post_terms( $eshb_current_post_id, 'eshb_category', array( 'fields' => 'slugs' ) ); // Get categories of the current post

$eshb_args = array(
    'post_type'      => 'eshb_accomodation',
    'posts_per_page' => $eshb_posts_per_page,
);

if(!empty($cat)){
    $eshb_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- necessary taxonomy filter, limited query
        array(
            'taxonomy' => 'eshb_category',
            'field'    => 'slug',
            'terms'    => $cat
        ),
    );
}

$eshb_best_wp = new WP_Query($eshb_args);

if($eshb_best_wp->have_posts() && $eshb_best_wp->found_posts > 1){

    ?>
    <div class="eshb-archive-wrapper related-accomodations related-accomodations-style-one">
        <div class="easy-hotel-heading">
            <?php if (!empty($eshb_string_related_sub_title)) { ?>
                <div class="easy-related-subtitle"><?php echo esc_html($eshb_string_related_sub_title); ?></div>
            <?php } else { ?>
                <div class="easy-related-subtitle"><?php esc_html_e( 'DISCOVER', 'easy-hotel' ); ?></div>
            <?php } ?>

            <?php if (!empty($eshb_string_related__title)) { ?>
                <h2 class="easy-related-title"><?php echo esc_html($eshb_string_related__title); ?></h2>
            <?php } else { ?>
                <h2 class="easy-related-title"><?php esc_html_e( 'More Rooms', 'easy-hotel' ); ?></h2>
            <?php } ?>
        </div>

        <div class=" room_slider-inner-wrapper room_slider-inner-wrapper-<?php echo esc_attr($eshb_unique); ?> section-dark eshb-text-light no-top no-bottom position-relative overflow-hidden z-1000">
            <div class="swiper rt_room_slider-<?php echo esc_attr($eshb_unique); ?> rt_room_slider <?php echo esc_attr( $eshb_sstyle )?> eshb-item-grid">
                <div class="swiper-wrapper">
                    <?php
                        // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Slider partial contract.
                        $eshb_x = 0;
                        $animation_delay = 0.2;
                        while($eshb_best_wp->have_posts()): $eshb_best_wp->the_post();
                            $animation_delay+=0.1;
                            $eshb_x++;

                            $accomodation_id = get_the_ID();
                            $price = $eshb_hotel_core->get_eshb_price_html('', '', $accomodation_id);
                            $eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
                            $eshb_adult_quantity = isset($eshb_accomodation_metaboxes['adult_capacity']) ? intval($eshb_accomodation_metaboxes['adult_capacity']) : 1;
                            $eshb_children_quantity = isset($eshb_accomodation_metaboxes['children_capacity']) ? intval($eshb_accomodation_metaboxes['children_capacity']) : 0;
                            $eshb_total_capacity = !empty($eshb_accomodation_metaboxes['total_capacity']) ? $eshb_accomodation_metaboxes['total_capacity'] : $eshb_adult_quantity + $eshb_children_quantity;
                            $accomodation_info_group = !empty($eshb_accomodation_metaboxes['accomodation_info_group']) ? $eshb_accomodation_metaboxes['accomodation_info_group'] : array();


                            $price = $eshb_hotel_core->get_eshb_price_html('', '', $accomodation_id);
                            $numeric_price = $eshb_hotel_core->get_eshb_price('', '', $accomodation_id);
                            $perodicity_string = apply_filters( 'eshb_perodicity_string_in_loop', $eshb_string_night, $accomodation_id, $eshb_settings);

                            $booking_url = add_query_arg(
                                array(
                                    'nonce' => $eshb_nonce,
                                    'start_date' => $eshb_start_date,
                                    'end_date' => $eshb_end_date,
                                    'adult_quantity' => $eshb_adult_quantity,
                                    'children_quantity' => $eshb_children_quantity,
                                ),
                                get_the_permalink($accomodation_id)
                            );

                        if($accomodation_id !== $eshb_current_post_id) {
                            require ESHB_PL_PATH . 'public/includes/widgets/room-slider/' . $eshb_sstyle.".php";
                        }

                        endwhile;
                        // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
                    ?>
                </div>
            </div>
        </div>
        <?php if( !empty($eshb_room_slider_dots == 'true' || $eshb_room_slider_nav == 'true') ) : ?>
                <div class="rt_room_slider-btn-wrapper rt_room_slider-btn-wrapper-<?php echo esc_attr($eshb_unique); ?>">
                    <div class="swiper-pagination"></div>
                    <!-- If we need navigation buttons -->
                    <div class="nav-btn swiper-button-prev"></div>
                    <div class="nav-btn swiper-button-next"></div>
                    <!-- If we need scrollbar -->
                    <div class="swiper-scrollbar"></div>
                </div>
        <?php endif; ?>

    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            var swiper<?php echo esc_attr($eshb_unique); ?> = new Swiper(".rt_room_slider-<?php echo esc_attr($eshb_unique); ?>", {
                slidesPerView: 3,
                speed: <?php echo esc_attr($eshb_autoplay_speed); ?>,
                slidesPerGroup: 1,
                loop: <?php echo esc_attr($eshb_infinite); ?>,
                spaceBetween: <?php echo esc_attr($eshb_item_gap); ?>,
                centeredSlides: <?php echo esc_attr($eshb_center_mode); ?>,
                navigation: {
                    nextEl: ".rt_room_slider-btn-wrapper-<?php echo esc_attr($eshb_unique); ?> .swiper-button-next",
                    prevEl: ".rt_room_slider-btn-wrapper-<?php echo esc_attr($eshb_unique); ?> .swiper-button-prev",
                },
                breakpoints: {
                    0: {
                        slidesPerView: 1,
                    },
                    375: {
                        slidesPerView: 1,
                    },
                    480: {
                        slidesPerView: 1,
                    },
                    575: {
                        slidesPerView: 1,
                    },
                    <?php
                            echo (!empty($eshb_col_xs)) ?  '575: { slidesPerView: ' . esc_attr($eshb_col_xs) . ' },' : '';
                            echo (!empty($eshb_col_sm)) ?  '767: { slidesPerView: ' . esc_attr($eshb_col_sm) . ' },' : '';
                            echo (!empty($eshb_col_md)) ?  '991: { slidesPerView: ' . esc_attr($eshb_col_md) . ' },' : '';
                            echo (!empty($eshb_col_lg)) ?  '1199: { slidesPerView: ' . esc_attr($eshb_col_lg) . ' },' : '';
                            ?>
                    1399: {
                        slidesPerView: 3,
                        spaceBetween: <?php echo esc_attr($eshb_item_gap); ?>
                    }
                }
            });
        });
    </script>
    <?php
}
