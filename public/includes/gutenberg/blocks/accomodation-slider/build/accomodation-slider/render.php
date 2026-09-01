<?php
/**
 * PHP file to use when rendering the `easy-hotel/accomodationgrid` block on the front-end.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $attributes is supplied by WordPress to the block render callback.
$eshb_attributes = $attributes ?? [];
$eshb_is_related_post = $eshb_attributes['is_related_post'] ?? false;
$eshb_style = $eshb_attributes['grid_style'] ?? '1';
$eshb_sstyle = 'style'.$eshb_style;
$per_page = $eshb_attributes['per_page'] ?? 10;
$eshb_slides_per_view = $eshb_attributes['slidesPerView'] ?? 1;
$eshb_slides_per_view_tablet = $eshb_attributes['slidesPerViewTablet'] ?? 1;
$eshb_slides_per_view_mobile = $eshb_attributes['slidesPerViewMobile'] ?? 1;
$eshb_slides_per_view_mobile_small = $eshb_attributes['slidesPerViewMobileSmall'] ?? 1;

$eshb_slides_to_scroll = $eshb_attributes['slidesToScroll'] ?? 1;
$eshb_space_between = $eshb_attributes['spaceBetween'] ?? 10;
$eshb_autoplay = $eshb_attributes['autoplay'] ? 'true' : 'false';
$eshb_autoplay_speed = $eshb_attributes['autoplaySpeed'] ?? 3000;
$eshb_pause_on_hover = $eshb_attributes['pauseOnHover'] ? 'true' : 'false';
$eshb_pause_on_inter = $eshb_attributes['pauseOnInter'] ? 'true' : 'false';

$eshb_centered_slides = 'false';
$eshb_speed = $eshb_attributes['speed'] ?? 300;
$eshb_effect = $eshb_attributes['effect'] ?? 'slide';
$eshb_loop = $eshb_attributes['loop'] ? 'true' : 'false';

if ($eshb_autoplay == 'true') {
    $eshb_autoplay = 'autoplay: { ';
    $eshb_autoplay .= 'delay: ' . $eshb_autoplay_speed;
    if ($eshb_pause_on_hover == 'true') {
        $eshb_autoplay .= ', pauseOnMouseEnter: true';
    } else {
        $eshb_autoplay .= ', pauseOnMouseEnter: false';
    }
    if ($eshb_pause_on_inter == 'true') {
        $eshb_autoplay .= ', disableOnInteraction: true';
    } else {
        $eshb_autoplay .= ', disableOnInteraction: false';
    }
    $eshb_autoplay .= ' }';
} else {
    $eshb_autoplay = 'autoplay: false';
}

$eshb_grid_style = $eshb_attributes['grid_style'] ?? 'default';
$eshb_grid_style = 'style'. $eshb_grid_style;
$eshb_btn_text  = $eshb_attributes['btn_text'] ?? 'Book Now';
$eshb_pricing_prefix = '';
$eshb_room_order = $eshb_attributes['room_order'] ?? 'ASC';
$eshb_room_orderby = $eshb_attributes['room_orderby'] ?? 'date';
$eshb_room_offset = $eshb_attributes['room_offset'] ?? 0;
$eshb_today_date = ESHB_Helper::eshb_today(); // Today in the site timezone, not UTC
$cat = $eshb_attributes['category'] ?? '';
// Create a DateTime object from today's date

$eshb_date = new DateTime($eshb_today_date);

// Add one day
$eshb_date->modify('+1 day');

// Get the new date in 'Y-m-d' format
$eshb_tomorrow_date = $eshb_date->format('Y-m-d');

$eshb_start = new DateTime($eshb_today_date);
$eshb_end = new DateTime($eshb_tomorrow_date);

// Dynamic Styles Processing helper
$eshb_ensure_unit = function( $value ) {
    if ( $value === '' || $value === null ) return '0px';
    if ( is_numeric( $value ) && $value != 0 ) return $value . 'px';
    return $value;
};

// Helper for generating inline styles
$eshb_get_inline_styles = function( $style_map ) use ( $eshb_ensure_unit ) {
    $styles = [];
    foreach ( $style_map as $prop => $value ) {
        if ( $value !== '' && $value !== null && $value !== 'inherit' && $value !== '0px' ) {
            $styles[] = $prop . ':' . $value;
        }
    }
    return implode( ';', $styles );
};

$eshb_vars = [];

// Item Styles
$eshb_item_styles = [];
if ( ! empty( $eshb_attributes['itemBackgroundColor'] ) ) {
    $eshb_item_styles['background-color'] = $eshb_attributes['itemBackgroundColor'];
}
if ( ! empty( $eshb_attributes['itemBackgroundColorHover'] ) ) {
    $eshb_vars[] = '--eshb-agi-bg-hover:' . esc_attr( $eshb_attributes['itemBackgroundColorHover'] );
}
if ( ! empty( $eshb_attributes['itemBackgroundColorTwo'] ) ) {
    $eshb_item_styles['background-color'] = $eshb_attributes['itemBackgroundColorTwo'];
}
if ( ! empty( $eshb_attributes['itemBackgroundGradient'] ) ) {
    $eshb_item_styles['background'] = $eshb_attributes['itemBackgroundGradient'];
}
if ( ! empty( $eshb_attributes['itemBackgroundGradientHover'] ) ) {
    $eshb_vars[] = '--eshb-agi-bg-hover:' . esc_attr( $eshb_attributes['itemBackgroundGradientHover'] );
}

$eshb_i_border_radius = $eshb_attributes['itemBorderRadius'] ?? [];
if ( ! empty( $eshb_i_border_radius['top'] ) ) $eshb_item_styles['border-top-left-radius'] = $eshb_ensure_unit( $eshb_i_border_radius['top'] );
if ( ! empty( $eshb_i_border_radius['right'] ) ) $eshb_item_styles['border-top-right-radius'] = $eshb_ensure_unit( $eshb_i_border_radius['right'] );
if ( ! empty( $eshb_i_border_radius['bottom'] ) ) $eshb_item_styles['border-bottom-left-radius'] = $eshb_ensure_unit( $eshb_i_border_radius['bottom'] );
if ( ! empty( $eshb_i_border_radius['left'] ) ) $eshb_item_styles['border-bottom-right-radius'] = $eshb_ensure_unit( $eshb_i_border_radius['left'] );

$eshb_item_inline_style = $eshb_get_inline_styles( $eshb_item_styles );

// Overlay Styles
// Overlay One Styles
$eshb_overlay_one_styles = [];
if ( ! empty( $eshb_attributes['itemOverlayBackgroundColor'] ) ) {
    $eshb_overlay_one_styles['background-color'] = $eshb_attributes['itemOverlayBackgroundColor'];
}
if ( ! empty( $eshb_attributes['itemOverlayBackgroundColorHover'] ) ) {
    $eshb_overlay_one_styles['--eshb-agi-overlay-bg-hover'] = $eshb_attributes['itemOverlayBackgroundColorHover'];
}
if ( ! empty( $eshb_attributes['itemOverlayBackgroundGradient'] ) ) {
    $eshb_overlay_one_styles['background'] = $eshb_attributes['itemOverlayBackgroundGradient'];
}
if ( ! empty( $eshb_attributes['itemOverlayBackgroundGradientHover'] ) ) {
    $eshb_overlay_one_styles['--eshb-agi-overlay-bg-hover'] = $eshb_attributes['itemOverlayBackgroundGradientHover'];
}
$eshb_overlay_inline_style = $eshb_get_inline_styles( $eshb_overlay_one_styles );

// Overlay Two Styles
$eshb_overlay_two_styles = [];
if ( ! empty( $eshb_attributes['itemOverlayBackgroundColorTwo'] ) ) {
    $eshb_overlay_two_styles['background-color'] = $eshb_attributes['itemOverlayBackgroundColorTwo'];
}
if ( ! empty( $eshb_attributes['itemOverlayBackgroundColorTwoHover'] ) ) {
    $eshb_overlay_two_styles['--eshb-agi-overlay-bg-two-hover'] = $eshb_attributes['itemOverlayBackgroundColorTwoHover'];
}
if ( ! empty( $eshb_attributes['itemOverlayBackgroundGradientTwo'] ) ) {
    $eshb_overlay_two_styles['background'] = $eshb_attributes['itemOverlayBackgroundGradientTwo'];
}
if ( ! empty( $eshb_attributes['itemOverlayBackgroundGradientTwoHover'] ) ) {
    $eshb_overlay_two_styles['--eshb-agi-overlay-bg-two-hover'] = $eshb_attributes['itemOverlayBackgroundGradientTwoHover'];
}
$eshb_overlay_two_inline_style = $eshb_get_inline_styles( $eshb_overlay_two_styles );

// Title Styles
$eshb_title_styles = [];
if ( ! empty( $eshb_attributes['itemTitleColor'] ) ) {
    $eshb_title_styles['color'] = $eshb_attributes['itemTitleColor'];
}
if ( ! empty( $eshb_attributes['itemTitleColorHover'] ) ) {
    $eshb_vars[] = '--eshb-agit-color-hover:' . esc_attr( $eshb_attributes['itemTitleColorHover'] );
}

$eshb_it_typo = $eshb_attributes['itemTitleTypography'] ?? [];
if ( ! empty( $eshb_it_typo['fontSize'] ) ) $eshb_title_styles['font-size'] = $eshb_it_typo['fontSize'];
if ( ! empty( $eshb_it_typo['fontWeight'] ) ) $eshb_title_styles['font-weight'] = $eshb_it_typo['fontWeight'];
if ( ! empty( $eshb_it_typo['lineHeight'] ) ) $eshb_title_styles['line-height'] = $eshb_it_typo['lineHeight'];
if ( ! empty( $eshb_it_typo['textTransform'] ) ) $eshb_title_styles['text-transform'] = $eshb_it_typo['textTransform'];
if ( ! empty( $eshb_it_typo['letterSpacing'] ) ) $eshb_title_styles['letter-spacing'] = $eshb_it_typo['letterSpacing'];

$eshb_title_inline_style = $eshb_get_inline_styles( $eshb_title_styles );

// Description Styles
$eshb_desc_styles = [];
if ( ! empty( $eshb_attributes['itemDescriptionColor'] ) ) {
    $eshb_desc_styles['color'] = $eshb_attributes['itemDescriptionColor'];
}
if ( ! empty( $eshb_attributes['itemDescriptionColorHover'] ) ) {
    $eshb_vars[] = '--eshb-agid-color-hover:' . esc_attr( $eshb_attributes['itemDescriptionColorHover'] );
}

$eshb_id_typo = $eshb_attributes['itemDescriptionTypography'] ?? [];
if ( ! empty( $eshb_id_typo['fontSize'] ) ) $eshb_desc_styles['font-size'] = $eshb_id_typo['fontSize'];
if ( ! empty( $eshb_id_typo['fontWeight'] ) ) $eshb_desc_styles['font-weight'] = $eshb_id_typo['fontWeight'];
if ( ! empty( $eshb_id_typo['lineHeight'] ) ) $eshb_desc_styles['line-height'] = $eshb_id_typo['lineHeight'];
if ( ! empty( $eshb_id_typo['textTransform'] ) ) $eshb_desc_styles['text-transform'] = $eshb_id_typo['textTransform'];
if ( ! empty( $eshb_id_typo['letterSpacing'] ) ) $eshb_desc_styles['letter-spacing'] = $eshb_id_typo['letterSpacing'];

$eshb_desc_inline_style = $eshb_get_inline_styles( $eshb_desc_styles );

// Capacities Styles
$eshb_capacities_item_styles = [];
if ( ! empty( $eshb_attributes['capacitiesItemColor'] ) ) {
    $eshb_capacities_item_styles['color'] = $eshb_attributes['capacitiesItemColor'];
}
if ( ! empty( $eshb_attributes['capacitiesItemColorHover'] ) ) {
    $eshb_vars[] = '--eshb-agid-cap-item-color-hover:' . esc_attr( $eshb_attributes['capacitiesItemColorHover'] );
}


$eshb_ci_typo = $eshb_attributes['capacitiesItemTypography'] ?? [];
if ( ! empty( $eshb_ci_typo['fontSize'] ) ) $eshb_capacities_item_styles['font-size'] = $eshb_ci_typo['fontSize'];
if ( ! empty( $eshb_ci_typo['fontWeight'] ) ) $eshb_capacities_item_styles['font-weight'] = $eshb_ci_typo['fontWeight'];
if ( ! empty( $eshb_ci_typo['lineHeight'] ) ) $eshb_capacities_item_styles['line-height'] = $eshb_ci_typo['lineHeight'];
if ( ! empty( $eshb_ci_typo['textTransform'] ) ) $eshb_capacities_item_styles['text-transform'] = $eshb_ci_typo['textTransform'];
if ( ! empty( $eshb_ci_typo['letterSpacing'] ) ) $eshb_capacities_item_styles['letter-spacing'] = $eshb_ci_typo['letterSpacing'];

$eshb_capacities_item_inline_style = $eshb_get_inline_styles( $eshb_capacities_item_styles );

// Pricing Styles
$eshb_price_styles = [];
if ( ! empty( $eshb_attributes['itemPricingColor'] ) ) {
    $eshb_price_styles['color'] = $eshb_attributes['itemPricingColor'];
}
if ( ! empty( $eshb_attributes['itemPricingColorHover'] ) ) {
    $eshb_vars[] = '--eshb-agip-color-hover:' . esc_attr( $eshb_attributes['itemPricingColorHover'] );
}


$eshb_ip_typo = $eshb_attributes['itemPricingTypography'] ?? [];
if ( ! empty( $eshb_ip_typo['fontSize'] ) ) $eshb_price_styles['font-size'] = $eshb_ip_typo['fontSize'];
if ( ! empty( $eshb_ip_typo['fontWeight'] ) ) $eshb_price_styles['font-weight'] = $eshb_ip_typo['fontWeight'];
if ( ! empty( $eshb_ip_typo['lineHeight'] ) ) $eshb_price_styles['line-height'] = $eshb_ip_typo['lineHeight'];
if ( ! empty( $eshb_ip_typo['textTransform'] ) ) $eshb_price_styles['text-transform'] = $eshb_ip_typo['textTransform'];
if ( ! empty( $eshb_ip_typo['letterSpacing'] ) ) $eshb_price_styles['letter-spacing'] = $eshb_ip_typo['letterSpacing'];

$eshb_price_inline_style = $eshb_get_inline_styles( $eshb_price_styles );

// Pricing Periodicity Styles
$eshb_price_periodicity_styles = [];
if ( ! empty( $eshb_attributes['itemPricingPerodicityColor'] ) ) {
    $eshb_price_periodicity_styles['color'] = $eshb_attributes['itemPricingPerodicityColor'];
}
if ( ! empty( $eshb_attributes['itemPricingPerodicityColorHover'] ) ) {
    $eshb_vars[] = '--eshb-agip-periodicity-color-hover:' . esc_attr( $eshb_attributes['itemPricingPerodicityColorHover'] );
}

$eshb_ipp_typo = $eshb_attributes['itemPricingPerodicityTypography'] ?? [];
if ( ! empty( $eshb_ipp_typo['fontSize'] ) ) $eshb_price_periodicity_styles['font-size'] = $eshb_ipp_typo['fontSize'];
if ( ! empty( $eshb_ipp_typo['fontWeight'] ) ) $eshb_price_periodicity_styles['font-weight'] = $eshb_ipp_typo['fontWeight'];
if ( ! empty( $eshb_ipp_typo['lineHeight'] ) ) $eshb_price_periodicity_styles['line-height'] = $eshb_ipp_typo['lineHeight'];
if ( ! empty( $eshb_ipp_typo['textTransform'] ) ) $eshb_price_periodicity_styles['text-transform'] = $eshb_ipp_typo['textTransform'];
if ( ! empty( $eshb_ipp_typo['letterSpacing'] ) ) $eshb_price_periodicity_styles['letter-spacing'] = $eshb_ipp_typo['letterSpacing'];

$eshb_price_periodicity_inline_style = $eshb_get_inline_styles( $eshb_price_periodicity_styles );

// Button Styles
$eshb_button_styles = [];
if ( ! empty( $eshb_attributes['itemButtonBackgroundColor'] ) ) {
    $eshb_button_styles['background-color'] = $eshb_attributes['itemButtonBackgroundColor'];
}
if ( ! empty( $eshb_attributes['itemButtonBackgroundColorHover'] ) ) {
    $eshb_vars[] = '--eshb-agib-bg-hover:' . esc_attr( $eshb_attributes['itemButtonBackgroundColorHover'] );
}
if ( ! empty( $eshb_attributes['itemButtonColor'] ) ) {
    $eshb_button_styles['color'] = $eshb_attributes['itemButtonColor'];
}
if ( ! empty( $eshb_attributes['itemButtonColorHover'] ) ) {
    $eshb_vars[] = '--eshb-agib-color-hover:' . esc_attr( $eshb_attributes['itemButtonColorHover'] );
}
if ( ! empty( $eshb_attributes['itemButtonBackgroundGradient'] ) ) {
    $eshb_button_styles['background'] = $eshb_attributes['itemButtonBackgroundGradient'];
}
if ( ! empty( $eshb_attributes['itemButtonBackgroundGradientHover'] ) ) {
    $eshb_vars[] = '--eshb-agib-bg-hover:' . esc_attr( $eshb_attributes['itemButtonBackgroundGradientHover'] );
}

$eshb_ib_typo = $eshb_attributes['itemButtonTypography'] ?? [];
if ( ! empty( $eshb_ib_typo['fontSize'] ) ) $eshb_button_styles['font-size'] = $eshb_ib_typo['fontSize'];
if ( ! empty( $eshb_ib_typo['fontWeight'] ) ) $eshb_button_styles['font-weight'] = $eshb_ib_typo['fontWeight'];
if ( ! empty( $eshb_ib_typo['lineHeight'] ) ) $eshb_button_styles['line-height'] = $eshb_ib_typo['lineHeight'];
if ( ! empty( $eshb_ib_typo['textTransform'] ) ) $eshb_button_styles['text-transform'] = $eshb_ib_typo['textTransform'];
if ( ! empty( $eshb_ib_typo['letterSpacing'] ) ) $eshb_button_styles['letter-spacing'] = $eshb_ib_typo['letterSpacing'];

$eshb_button_inline_style = $eshb_get_inline_styles( $eshb_button_styles );

$eshb_style_attr = '';
if ( ! empty( $eshb_vars ) ) {
    $eshb_style_attr .= implode( ';', $eshb_vars ) . ';';
}

$eshb_unique      = wp_rand(2012, 35120);
$eshb_gallery_dots = true;
$eshb_gallery_nav  = true;
$eshb_thumbnail_size = 'eshb_thumbnail';
$eshb_current_accomodation_id = get_the_ID();
if($eshb_is_related_post){
    $cat = wp_get_post_terms( $eshb_current_accomodation_id, 'eshb_category', array( 'fields' => 'slugs' ) ); // Get categories of the current post
}
?>
<div 
    class="eshb-accomodation-slider-block-wrap" 
    data-unique="<?php echo esc_attr($eshb_unique); ?>"  
    data-slides-per-view="<?php echo esc_attr($eshb_slides_per_view); ?>"
    data-slides-per-view-tablet="<?php echo esc_attr($eshb_slides_per_view_tablet); ?>"
    data-slides-per-view-mobile="<?php echo esc_attr($eshb_slides_per_view_mobile); ?>"
    data-slides-per-view-mobile-small="<?php echo esc_attr($eshb_slides_per_view_mobile_small); ?>"
    data-slides-to-scroll="<?php echo esc_attr($eshb_slides_to_scroll); ?>"
    data-space-between="<?php echo esc_attr($eshb_space_between); ?>"
    data-centered-slides="<?php echo esc_attr($eshb_centered_slides); ?>"
    data-gallery-nav="<?php echo esc_attr($eshb_gallery_nav); ?>"
    data-gallery-dots="<?php echo esc_attr($eshb_gallery_dots); ?>"
    data-loop="<?php echo esc_attr($eshb_loop); ?>"
    data-effect="<?php echo esc_attr($eshb_effect); ?>"
    data-speed="<?php echo esc_attr($eshb_speed); ?>">

    <div class="room_slider-inner-wrapper room_slider-inner-wrapper-<?php echo esc_attr($eshb_unique); ?>">            
        <div class="swiper rt_room_slider-<?php echo esc_attr($eshb_unique); ?> rt_room_slider <?php echo esc_attr( $eshb_sstyle )?> eshb-item-grid">
            <div class="swiper-wrapper">

                <?php 

                $eshb_hotel_core = new ESHB_Core();
                $eshb_hotel_view = new ESHB_View();

                $eshb_settings = get_option('eshb_settings');
                
                $eshb_string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';
                
                
                $eshb_args = array(
                    'post_type'      => 'eshb_accomodation',
                    'posts_per_page' => $per_page,	
                    'orderby' 		 => $eshb_room_orderby,
                    'order' 		 => $eshb_room_order,
                    'offset' 		 => $eshb_room_offset,							
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

                $eshb_i = 0;
                $eshb_animation_delay = 0.2;

                while($eshb_best_wp->have_posts()): $eshb_best_wp->the_post();

                    $eshb_animation_delay+=0.1;
                    $eshb_accomodation_id = get_the_ID();

                    if($eshb_is_related_post && $eshb_accomodation_id == $eshb_current_accomodation_id){
                        continue;
                    }

                    $eshb_accomodation_metaboxes = get_post_meta($eshb_accomodation_id, 'eshb_accomodation_metaboxes', true);
                    $eshb_accomodation_info_group = $eshb_accomodation_metaboxes['accomodation_info_group'];
                    $eshb_booking_url = get_the_permalink($eshb_accomodation_id);
                    $eshb_price = $eshb_hotel_core->get_eshb_min_price_html($eshb_accomodation_id);
                    $eshb_numeric_price = $eshb_hotel_core->get_eshb_min_price($eshb_accomodation_id);
                    $eshb_excerpt = $eshb_hotel_view->eshb_custom_excerpt(35, $eshb_accomodation_id);
                    $eshb_perodicity_string = apply_filters( 'eshb_perodicity_string_in_loop', $eshb_string_night, $eshb_accomodation_id, $eshb_settings);
                    include ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodation-slider/src/accomodation-slider/grid-styles/' . $eshb_sstyle .".php";  

                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </div>
    <?php if( !empty($eshb_gallery_dots == 'true' || $eshb_gallery_nav == 'true') ) : ?>
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
