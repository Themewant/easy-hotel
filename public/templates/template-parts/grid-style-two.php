
<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $eshb_hotel_core = new ESHB_Core();
    $eshb_hotel_view = new ESHB_View();
    $eshb_nonce_action = ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action');
    $eshb_nonce = wp_create_nonce($eshb_nonce_action);

    $eshb_settings = get_option('eshb_settings');
    $eshb_string_from = isset($eshb_settings['string_from']) && !empty($eshb_settings['string_from']) ? $eshb_settings['string_from'] : '';
    $eshb_btn_text = isset($eshb_settings['view_details']) && !empty($eshb_settings['view_details']) ? $eshb_settings['view_details'] : '';
    $eshb_btn_text = ($eshb_btn_text) ? $eshb_btn_text : __('View Details', 'easy-hotel') ;
    $eshb_string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';

    if ($query->have_posts()) {

        ?>
        <div class="eshb-item-grid style-two">
        <?php
        $eshb_animation_delay = 0.2;

        while ($query->have_posts()) {
            
            $eshb_animation_delay+=0.1;
            $query->the_post();
            $eshb_accomodation_id = get_the_ID();
            $eshb_metaboxes = get_post_meta($eshb_accomodation_id, 'eshb_accomodation_metaboxes', true);
            $eshb_adult_capacity = isset($eshb_metaboxes['adult_capacity']) ? intval($eshb_metaboxes['adult_capacity']) : 1;
            $eshb_children_capacity = isset($eshb_metaboxes['children_capacity']) ? intval($eshb_metaboxes['children_capacity']) : 0;
            $eshb_total_capacity = !empty($eshb_metaboxes['total_capacity']) ? $eshb_metaboxes['total_capacity'] : $eshb_adult_capacity + $eshb_children_capacity;
            $eshb_accomodation_info_group = !empty($eshb_metaboxes['accomodation_info_group']) ? $eshb_metaboxes['accomodation_info_group'] : array();
            $eshb_price = $eshb_hotel_core->get_eshb_price_html($start_date, $end_date, $eshb_accomodation_id);
            $eshb_numeric_price = $eshb_hotel_core->get_eshb_price('', '', $eshb_accomodation_id);
            $title = get_the_title($eshb_accomodation_id);
            $eshb_excerpt = $eshb_hotel_view->eshb_custom_excerpt(60, $eshb_accomodation_id);
            $eshb_perodicity_string = apply_filters( 'eshb_perodicity_string_in_loop', $eshb_string_night, $eshb_accomodation_id, $eshb_settings);
            
            $eshb_booking_url = add_query_arg(
                array( 
                    'nonce' => $eshb_nonce, 
                    'start_date' => $start_date, 
                    'end_date' => $end_date, 
                    'adult_quantity' => $adult_quantity, 
                    'children_quantity' => $children_quantity,
                ), 
                get_the_permalink($eshb_accomodation_id) 
            );

            if(has_post_thumbnail($eshb_accomodation_id)) {
                $eshb_thumbnail_url = get_the_post_thumbnail_url( $eshb_accomodation_id, 'eshb_thumbnail');
            } else {
                $eshb_thumbnail_url = ESHB_DIR_URL . 'public/assets/img/placeholder.png';
            }

            ?>

            <div class="grid-item  wow fadeInUp animated" data-wow-delay="<?php echo esc_attr( $eshb_animation_delay )?>s">
                <div class="item-inner">
                    <!-- Image -->
                    <div class="thumbnail-col col-lg-6 col-sm-12 col-12">
                        <img src="<?php echo esc_url( $eshb_thumbnail_url ); ?>" alt="Thumbnail" class="thumbnail">
                        <div class="pricing">
                            <?php 
                            if(!empty($eshb_numeric_price)){
                            ?>
                                <h3 class="price"><?php echo wp_kses_post($eshb_price); ?>
                                <div class="label"> / <?php echo esc_html( eshb_get_translated_string($eshb_perodicity_string) );?></div></h3>
                            <?php 
                                } 
                            ?>
                        </div>
                    </div>
                    <!-- Text -->
                    <div class="contents-col col-lg-6 col-sm-12 col-12">
                    
                        <h3 class="p-title"><a href="<?php echo esc_url( get_permalink( $eshb_accomodation_id ) ); ?>"><?php echo wp_kses_post( get_the_title( $eshb_accomodation_id ) ); ?></a></h3>
                        <div class="capacities">
                            <?php 
                                if ( ! empty( $eshb_accomodation_info_group ) && is_array($eshb_accomodation_info_group) && count($eshb_accomodation_info_group) > 0) {
                                    $eshb_x = 0;
                                    foreach ( $eshb_accomodation_info_group as $eshb_group ) { 
                                        $eshb_x++;
                                        if($eshb_x >= 3) break;
                                        ?>
                                        <span class="capacity">
                                            <?php echo esc_html($eshb_group['info_title']); ?>
                                        </span>
                                    <?php }
                                }
                            ?>
                        </div>
                        <p class="desc"><?php echo esc_html($eshb_excerpt) ?></p>
                        <a class="details-btn" href="<?php echo esc_url( $eshb_booking_url ); ?>"><?php echo esc_html( $eshb_btn_text )?></a>
                    
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        </div>
        <?php
        echo esc_html($eshb_hotel_view->eshb_get_pagination($query));
        

    } else {
        echo '<p class="eshb-search-error">No available accommodations found for the selected dates.</p>';
    }
    
    wp_reset_postdata();

