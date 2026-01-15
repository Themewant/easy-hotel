<?php
/**
 * PHP file to use when rendering the `easy-hotel/accomodationgrid` block on the front-end.
 */


$attributes = $attributes ?? [];
$is_related_post = $attributes['is_related_post'] ?? false;
$style = $attributes['grid_style'] ?? '1';
$sstyle = 'style'.$style;
$per_page = $attributes['per_page'] ?? 10;
$slidesPerView = $attributes['slidesPerView'] ?? 1;
$slidesPerViewTablet = $attributes['slidesPerViewTablet'] ?? 1;
$slidesPerViewMobile = $attributes['slidesPerViewMobile'] ?? 1;
$slidesPerViewMobileSmall = $attributes['slidesPerViewMobileSmall'] ?? 1;

$slidesToScroll = $attributes['slidesToScroll'] ?? 1;
$spaceBetween = $attributes['spaceBetween'] ?? 10;
$autoplay = $attributes['autoplay'] ? 'true' : 'false';
$autoplaySpeed = $attributes['autoplaySpeed'] ?? 3000;
$pauseOnHover = $attributes['pauseOnHover'] ? 'true' : 'false';
$pauseOnInter = $attributes['pauseOnInter'] ? 'true' : 'false';

$centeredSlides = 'false';
$speed = $attributes['speed'] ?? 300;
$effect = $attributes['effect'] ?? 'slide';
$loop = $attributes['loop'] ? 'true' : 'false';

if ($autoplay == 'true') {
    $autoplay = 'autoplay: { ';
    $autoplay .= 'delay: ' . $autoplaySpeed;
    if ($pauseOnHover == 'true') {
        $autoplay .= ', pauseOnMouseEnter: true';
    } else {
        $autoplay .= ', pauseOnMouseEnter: false';
    }
    if ($pauseOnInter == 'true') {
        $autoplay .= ', disableOnInteraction: true';
    } else {
        $autoplay .= ', disableOnInteraction: false';
    }
    $autoplay .= ' }';
} else {
    $autoplay = 'autoplay: false';
}

$grid_style = $attributes['grid_style'] ?? 'default';
$grid_style = 'style'. $grid_style;
$btn_text  = $attributes['btn_text'] ?? 'Book Now';
$pricing_prefix = '';
$room_order = $attributes['room_order'] ?? 'ASC';
$room_orderby = $attributes['room_orderby'] ?? 'date';
$room_offset = $attributes['room_offset'] ?? 0;
$today_date = gmdate('Y-m-d'); // Get today's date
$cat = $attributes['category'] ?? '';
// Create a DateTime object from today's date

$date = new DateTime($today_date);

// Add one day
$date->modify('+1 day');

// Get the new date in 'Y-m-d' format
$tomorrow_date = $date->format('Y-m-d');

$start = new DateTime($today_date);
$end = new DateTime($tomorrow_date);

// Dynamic Styles Processing helper
$ensure_unit = function( $value ) {
    if ( $value === '' || $value === null ) return '0px';
    if ( is_numeric( $value ) && $value != 0 ) return $value . 'px';
    return $value;
};

// Helper for generating inline styles
$get_inline_styles = function( $style_map ) use ( $ensure_unit ) {
    $styles = [];
    foreach ( $style_map as $prop => $value ) {
        if ( $value !== '' && $value !== null && $value !== 'inherit' && $value !== '0px' ) {
            $styles[] = $prop . ':' . $value;
        }
    }
    return implode( ';', $styles );
};

$vars = [];

// Item Styles
$item_styles = [];
if ( ! empty( $attributes['itemBackgroundColor'] ) ) {
    $item_styles['background-color'] = $attributes['itemBackgroundColor'];
}
if ( ! empty( $attributes['itemBackgroundColorHover'] ) ) {
    $vars[] = '--eshb-agi-bg-hover:' . esc_attr( $attributes['itemBackgroundColorHover'] );
}
if ( ! empty( $attributes['itemBackgroundColorTwo'] ) ) {
    $item_styles['background-color'] = $attributes['itemBackgroundColorTwo'];
}
if ( ! empty( $attributes['itemBackgroundGradient'] ) ) {
    $item_styles['background'] = $attributes['itemBackgroundGradient'];
}
if ( ! empty( $attributes['itemBackgroundGradientHover'] ) ) {
    $vars[] = '--eshb-agi-bg-hover:' . esc_attr( $attributes['itemBackgroundGradientHover'] );
}

$i_border_radius = $attributes['itemBorderRadius'] ?? [];
if ( ! empty( $i_border_radius['top'] ) ) $item_styles['border-top-left-radius'] = $ensure_unit( $i_border_radius['top'] );
if ( ! empty( $i_border_radius['right'] ) ) $item_styles['border-top-right-radius'] = $ensure_unit( $i_border_radius['right'] );
if ( ! empty( $i_border_radius['bottom'] ) ) $item_styles['border-bottom-left-radius'] = $ensure_unit( $i_border_radius['bottom'] );
if ( ! empty( $i_border_radius['left'] ) ) $item_styles['border-bottom-right-radius'] = $ensure_unit( $i_border_radius['left'] );

$item_inline_style = $get_inline_styles( $item_styles );

// Overlay Styles
// Overlay One Styles
$overlay_one_styles = [];
if ( ! empty( $attributes['itemOverlayBackgroundColor'] ) ) {
    $overlay_one_styles['background-color'] = $attributes['itemOverlayBackgroundColor'];
}
if ( ! empty( $attributes['itemOverlayBackgroundColorHover'] ) ) {
    $overlay_one_styles['--eshb-agi-overlay-bg-hover'] = $attributes['itemOverlayBackgroundColorHover'];
}
if ( ! empty( $attributes['itemOverlayBackgroundGradient'] ) ) {
    $overlay_one_styles['background'] = $attributes['itemOverlayBackgroundGradient'];
}
if ( ! empty( $attributes['itemOverlayBackgroundGradientHover'] ) ) {
    $overlay_one_styles['--eshb-agi-overlay-bg-hover'] = $attributes['itemOverlayBackgroundGradientHover'];
}
$overlay_inline_style = $get_inline_styles( $overlay_one_styles );

// Overlay Two Styles
$overlay_two_styles = [];
if ( ! empty( $attributes['itemOverlayBackgroundColorTwo'] ) ) {
    $overlay_two_styles['background-color'] = $attributes['itemOverlayBackgroundColorTwo'];
}
if ( ! empty( $attributes['itemOverlayBackgroundColorTwoHover'] ) ) {
    $overlay_two_styles['--eshb-agi-overlay-bg-two-hover'] = $attributes['itemOverlayBackgroundColorTwoHover'];
}
if ( ! empty( $attributes['itemOverlayBackgroundGradientTwo'] ) ) {
    $overlay_two_styles['background'] = $attributes['itemOverlayBackgroundGradientTwo'];
}
if ( ! empty( $attributes['itemOverlayBackgroundGradientTwoHover'] ) ) {
    $overlay_two_styles['--eshb-agi-overlay-bg-two-hover'] = $attributes['itemOverlayBackgroundGradientTwoHover'];
}
$overlay_two_inline_style = $get_inline_styles( $overlay_two_styles );

// Title Styles
$title_styles = [];
if ( ! empty( $attributes['itemTitleColor'] ) ) {
    $title_styles['color'] = $attributes['itemTitleColor'];
}
if ( ! empty( $attributes['itemTitleColorHover'] ) ) {
    $vars[] = '--eshb-agit-color-hover:' . esc_attr( $attributes['itemTitleColorHover'] );
}

$it_typo = $attributes['itemTitleTypography'] ?? [];
if ( ! empty( $it_typo['fontSize'] ) ) $title_styles['font-size'] = $it_typo['fontSize'];
if ( ! empty( $it_typo['fontWeight'] ) ) $title_styles['font-weight'] = $it_typo['fontWeight'];
if ( ! empty( $it_typo['lineHeight'] ) ) $title_styles['line-height'] = $it_typo['lineHeight'];
if ( ! empty( $it_typo['textTransform'] ) ) $title_styles['text-transform'] = $it_typo['textTransform'];
if ( ! empty( $it_typo['letterSpacing'] ) ) $title_styles['letter-spacing'] = $it_typo['letterSpacing'];

$title_inline_style = $get_inline_styles( $title_styles );

// Description Styles
$desc_styles = [];
if ( ! empty( $attributes['itemDescriptionColor'] ) ) {
    $desc_styles['color'] = $attributes['itemDescriptionColor'];
}
if ( ! empty( $attributes['itemDescriptionColorHover'] ) ) {
    $vars[] = '--eshb-agid-color-hover:' . esc_attr( $attributes['itemDescriptionColorHover'] );
}

$id_typo = $attributes['itemDescriptionTypography'] ?? [];
if ( ! empty( $id_typo['fontSize'] ) ) $desc_styles['font-size'] = $id_typo['fontSize'];
if ( ! empty( $id_typo['fontWeight'] ) ) $desc_styles['font-weight'] = $id_typo['fontWeight'];
if ( ! empty( $id_typo['lineHeight'] ) ) $desc_styles['line-height'] = $id_typo['lineHeight'];
if ( ! empty( $id_typo['textTransform'] ) ) $desc_styles['text-transform'] = $id_typo['textTransform'];
if ( ! empty( $id_typo['letterSpacing'] ) ) $desc_styles['letter-spacing'] = $id_typo['letterSpacing'];

$desc_inline_style = $get_inline_styles( $desc_styles );

// Capacities Styles
$capacities_item_styles = [];
if ( ! empty( $attributes['capacitiesItemColor'] ) ) {
    $capacities_item_styles['color'] = $attributes['capacitiesItemColor'];
}
if ( ! empty( $attributes['capacitiesItemColorHover'] ) ) {
    $vars[] = '--eshb-agid-cap-item-color-hover:' . esc_attr( $attributes['capacitiesItemColorHover'] );
}


$ci_typo = $attributes['capacitiesItemTypography'] ?? [];
if ( ! empty( $ci_typo['fontSize'] ) ) $capacities_item_styles['font-size'] = $ci_typo['fontSize'];
if ( ! empty( $ci_typo['fontWeight'] ) ) $capacities_item_styles['font-weight'] = $ci_typo['fontWeight'];
if ( ! empty( $ci_typo['lineHeight'] ) ) $capacities_item_styles['line-height'] = $ci_typo['lineHeight'];
if ( ! empty( $ci_typo['textTransform'] ) ) $capacities_item_styles['text-transform'] = $ci_typo['textTransform'];
if ( ! empty( $ci_typo['letterSpacing'] ) ) $capacities_item_styles['letter-spacing'] = $ci_typo['letterSpacing'];

$capacities_item_inline_style = $get_inline_styles( $capacities_item_styles );

// Pricing Styles
$price_styles = [];
if ( ! empty( $attributes['itemPricingColor'] ) ) {
    $price_styles['color'] = $attributes['itemPricingColor'];
}
if ( ! empty( $attributes['itemPricingColorHover'] ) ) {
    $vars[] = '--eshb-agip-color-hover:' . esc_attr( $attributes['itemPricingColorHover'] );
}


$ip_typo = $attributes['itemPricingTypography'] ?? [];
if ( ! empty( $ip_typo['fontSize'] ) ) $price_styles['font-size'] = $ip_typo['fontSize'];
if ( ! empty( $ip_typo['fontWeight'] ) ) $price_styles['font-weight'] = $ip_typo['fontWeight'];
if ( ! empty( $ip_typo['lineHeight'] ) ) $price_styles['line-height'] = $ip_typo['lineHeight'];
if ( ! empty( $ip_typo['textTransform'] ) ) $price_styles['text-transform'] = $ip_typo['textTransform'];
if ( ! empty( $ip_typo['letterSpacing'] ) ) $price_styles['letter-spacing'] = $ip_typo['letterSpacing'];

$price_inline_style = $get_inline_styles( $price_styles );

// Pricing Periodicity Styles
$price_periodicity_styles = [];
if ( ! empty( $attributes['itemPricingPerodicityColor'] ) ) {
    $price_periodicity_styles['color'] = $attributes['itemPricingPerodicityColor'];
}
if ( ! empty( $attributes['itemPricingPerodicityColorHover'] ) ) {
    $vars[] = '--eshb-agip-periodicity-color-hover:' . esc_attr( $attributes['itemPricingPerodicityColorHover'] );
}

$ipp_typo = $attributes['itemPricingPerodicityTypography'] ?? [];
if ( ! empty( $ipp_typo['fontSize'] ) ) $price_periodicity_styles['font-size'] = $ipp_typo['fontSize'];
if ( ! empty( $ipp_typo['fontWeight'] ) ) $price_periodicity_styles['font-weight'] = $ipp_typo['fontWeight'];
if ( ! empty( $ipp_typo['lineHeight'] ) ) $price_periodicity_styles['line-height'] = $ipp_typo['lineHeight'];
if ( ! empty( $ipp_typo['textTransform'] ) ) $price_periodicity_styles['text-transform'] = $ipp_typo['textTransform'];
if ( ! empty( $ipp_typo['letterSpacing'] ) ) $price_periodicity_styles['letter-spacing'] = $ipp_typo['letterSpacing'];

$price_periodicity_inline_style = $get_inline_styles( $price_periodicity_styles );

// Button Styles
$button_styles = [];
if ( ! empty( $attributes['itemButtonBackgroundColor'] ) ) {
    $button_styles['background-color'] = $attributes['itemButtonBackgroundColor'];
}
if ( ! empty( $attributes['itemButtonBackgroundColorHover'] ) ) {
    $vars[] = '--eshb-agib-bg-hover:' . esc_attr( $attributes['itemButtonBackgroundColorHover'] );
}
if ( ! empty( $attributes['itemButtonColor'] ) ) {
    $button_styles['color'] = $attributes['itemButtonColor'];
}
if ( ! empty( $attributes['itemButtonColorHover'] ) ) {
    $vars[] = '--eshb-agib-color-hover:' . esc_attr( $attributes['itemButtonColorHover'] );
}
if ( ! empty( $attributes['itemButtonBackgroundGradient'] ) ) {
    $button_styles['background'] = $attributes['itemButtonBackgroundGradient'];
}
if ( ! empty( $attributes['itemButtonBackgroundGradientHover'] ) ) {
    $vars[] = '--eshb-agib-bg-hover:' . esc_attr( $attributes['itemButtonBackgroundGradientHover'] );
}

$ib_typo = $attributes['itemButtonTypography'] ?? [];
if ( ! empty( $ib_typo['fontSize'] ) ) $button_styles['font-size'] = $ib_typo['fontSize'];
if ( ! empty( $ib_typo['fontWeight'] ) ) $button_styles['font-weight'] = $ib_typo['fontWeight'];
if ( ! empty( $ib_typo['lineHeight'] ) ) $button_styles['line-height'] = $ib_typo['lineHeight'];
if ( ! empty( $ib_typo['textTransform'] ) ) $button_styles['text-transform'] = $ib_typo['textTransform'];
if ( ! empty( $ib_typo['letterSpacing'] ) ) $button_styles['letter-spacing'] = $ib_typo['letterSpacing'];

$button_inline_style = $get_inline_styles( $button_styles );

$style_attr = '';
if ( ! empty( $vars ) ) {
    $style_attr .= implode( ';', $vars ) . ';';
}

$unique      = wp_rand(2012, 35120);
$galleryDots = true;
$galleryNav  = true;
$thumbnail_size = 'eshb_thumbnail';
$current_accomodation_id = get_the_ID();
if($is_related_post){
    $cat = wp_get_post_terms( $current_accomodation_id, 'eshb_category', array( 'fields' => 'slugs' ) ); // Get categories of the current post
}
?>
<div 
    class="eshb-accomodation-slider-block-wrap" 
    data-unique="<?php echo esc_attr($unique); ?>"  
    data-slides-per-view="<?php echo esc_attr($slidesPerView); ?>"
    data-slides-per-view-tablet="<?php echo esc_attr($slidesPerViewTablet); ?>"
    data-slides-per-view-mobile="<?php echo esc_attr($slidesPerViewMobile); ?>"
    data-slides-per-view-mobile-small="<?php echo esc_attr($slidesPerViewMobileSmall); ?>"
    data-slides-to-scroll="<?php echo esc_attr($slidesToScroll); ?>"
    data-space-between="<?php echo esc_attr($spaceBetween); ?>"
    data-centered-slides="<?php echo esc_attr($centeredSlides); ?>"
    data-gallery-nav="<?php echo esc_attr($galleryNav); ?>"
    data-gallery-dots="<?php echo esc_attr($galleryDots); ?>"
    data-loop="<?php echo esc_attr($loop); ?>"
    data-effect="<?php echo esc_attr($effect); ?>"
    data-speed="<?php echo esc_attr($speed); ?>">

    <div class="room_slider-inner-wrapper room_slider-inner-wrapper-<?php echo esc_attr($unique); ?>">            
        <div class="swiper rt_room_slider-<?php echo esc_attr($unique); ?> rt_room_slider <?php echo esc_attr( $sstyle )?> eshb-item-grid">
            <div class="swiper-wrapper">

                <?php 

                $hotel_core = new ESHB_Core();
                $hotel_view = new ESHB_View();

                $eshb_settings = get_option('eshb_settings');
                
                $string_night = isset($eshb_settings['string_night']) && !empty($eshb_settings['string_night']) ? $eshb_settings['string_night'] : 'night';
                
                
                $args = array(
                    'post_type'      => 'eshb_accomodation',
                    'posts_per_page' => $per_page,	
                    'orderby' 		 => $room_orderby,
                    'order' 		 => $room_order,
                    'offset' 		 => $room_offset,							
                );

                if(!empty($cat)){
                    $args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- necessary taxonomy filter, limited query
                        array(
                            'taxonomy' => 'eshb_category',
                            'field'    => 'slug', 
                            'terms'    => $cat 
                        ),
                    );
                }

                $best_wp = new WP_Query($args);	  

                $i = 0;
                $animation_delay = 0.2;

                while($best_wp->have_posts()): $best_wp->the_post();

                    $animation_delay+=0.1;
                    $accomodation_id = get_the_ID();

                    if($is_related_post && $accomodation_id == $current_accomodation_id){
                        continue;
                    }

                    $eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
                    $accomodation_info_group = $eshb_accomodation_metaboxes['accomodation_info_group'];
                    $booking_url = get_the_permalink($accomodation_id);
                    $price = $hotel_core->get_eshb_price_html('', '', $accomodation_id);
                    $numeric_price = $hotel_core->get_eshb_price('', '', $accomodation_id);
                    $excerpt = $hotel_view->eshb_custom_excerpt(35, $accomodation_id);
                    $perodicity_string = apply_filters( 'eshb_perodicity_string_in_loop', $string_night, $accomodation_id, $eshb_settings);
                    include ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodation-slider/src/accomodation-slider/grid-styles/' . $sstyle .".php";  

                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </div>
    <?php if( !empty($galleryDots == 'true' || $galleryNav == 'true') ) : ?>
            <div class="rt_room_slider-btn-wrapper rt_room_slider-btn-wrapper-<?php echo esc_attr($unique); ?>">
                <div class="swiper-pagination"></div>
                <!-- If we need navigation buttons -->
                <div class="nav-btn swiper-button-prev"></div>
                <div class="nav-btn swiper-button-next"></div>
                <!-- If we need scrollbar -->
                <div class="swiper-scrollbar"></div>
            </div>
    <?php endif; ?>
</div>
