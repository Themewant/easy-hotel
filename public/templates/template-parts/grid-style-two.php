
<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $hotel_core = new ESHB_Core();
    $hotel_view = new ESHB_View();
    $nonce_action = ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action');
    $nonce = wp_create_nonce($nonce_action);

    $eshb_settings = get_option('eshb_settings');
    $string_from = isset($eshb_settings['string_from']) && !empty($eshb_settings['string_from']) ? $eshb_settings['string_from'] : '';
    $btn_text = isset($eshb_settings['view_details']) && !empty($eshb_settings['view_details']) ? $eshb_settings['view_details'] : '';
    $btn_text = ($btn_text) ? $btn_text : __('View Details', 'easy-hotel') ;
    $string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';

    if ($query->have_posts()) {

        ?>
        <div class="eshb-item-grid style-two">
        <?php

        $animation_delay = 0.2;

        while ($query->have_posts()) {
            
            $animation_delay+=0.1;
            $query->the_post();
            $accomodation_id = get_the_ID();
            $metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
            $adult_capacity = isset($metaboxes['adult_capacity']) ? intval($metaboxes['adult_capacity']) : 1;
            $children_capacity = isset($metaboxes['children_capacity']) ? intval($metaboxes['children_capacity']) : 0;
            $total_capacity = !empty($metaboxes['total_capacity']) ? $metaboxes['total_capacity'] : $adult_capacity + $children_capacity;
            $accomodation_info_group = !empty($metaboxes['accomodation_info_group']) ? $metaboxes['accomodation_info_group'] : array();
            $price = $hotel_core->get_eshb_price_html($start_date, $end_date, $accomodation_id);
            $numeric_price = $hotel_core->get_eshb_price('', '', $accomodation_id);
            $title = get_the_title($accomodation_id);
            $excerpt = $hotel_view->eshb_custom_excerpt(60, $accomodation_id);
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

            if(has_post_thumbnail($accomodation_id)) {
                $thumbnail_url = get_the_post_thumbnail_url( $accomodation_id, 'eshb_thumbnail');
            } else {
                $thumbnail_url = ESHB_DIR_URL . 'public/assets/img/placeholder.png';
            }

            ?>

            <div class="grid-item  wow fadeInUp animated" data-wow-delay="<?php echo esc_attr( $animation_delay )?>s">
                <div class="item-inner">
                    <!-- Image -->
                    <div class="thumbnail-col col-lg-6 col-sm-12 col-12">
                        <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="Thumbnail" class="thumbnail">
                        <div class="pricing">
                            <?php 
                            if(!empty($numeric_price)){
                            ?>
                                <h3 class="price"><?php echo wp_kses_post($price); ?>
                                <div class="label"> / <?php echo esc_html( eshb_get_translated_string($perodicity_string) );?></div></h3>
                            <?php 
                                } 
                            ?>
                        </div>
                    </div>
                    <!-- Text -->
                    <div class="contents-col col-lg-6 col-sm-12 col-12">
                    
                        <h3 class="p-title"><a href="<?php the_permalink(); ?>"><?php echo get_the_title($accomodation_id); ?></a></h3>
                        <div class="capacities">
                            <?php 
                                if ( ! empty( $accomodation_info_group ) && is_array($accomodation_info_group) && count($accomodation_info_group) > 0) {
                                    $x = 0;
                                    foreach ( $accomodation_info_group as $group ) { 
                                        $x++;
                                        if($x >= 3) break;
                                        ?>
                                        <span class="capacity">
                                            <?php echo esc_html($group['info_title']); ?>
                                        </span>
                                    <?php }
                                }
                            ?>
                        </div>
                        <p class="desc"><?php echo esc_html($excerpt) ?></p>
                        <a class="details-btn" href="<?php echo esc_url( $booking_url ); ?>"><?php echo esc_html( $btn_text )?></a>
                    
                    </div>
                </div>
            </div>
            
            <?php
            
        }

        ?>

        </div>

        <?php

        echo esc_html($hotel_view->eshb_get_pagination($query));
        

    } else {
        echo '<p class="eshb-search-error">No available accommodations found for the selected dates.</p>';
    }
    
    wp_reset_postdata();

