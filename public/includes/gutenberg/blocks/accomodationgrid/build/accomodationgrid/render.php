<?php
/**
 * PHP file to use when rendering the `easy-hotel/accomodationgrid` block on the front-end.
 */


$attributes = $attributes ?? [];

$room_columns = $attributes['room_columns'] ?? 3;
$grid_style = $attributes['grid_style'] ?? 'default';
$grid_style = 'style'. $grid_style;
$btn_text  = $attributes['btn_text'] ?? 'Book Now';
$pricing_prefix = '';
$thumbnail_size = $attributes['thumbnail_size'] ?? 'large';
$per_page = $attributes['per_page'] ?? -1;
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

// Container inline style now only handles grid-specific properties
$container_inline_style = "grid-template-columns: repeat(" . esc_attr( $room_columns ) . ", 1fr);";
$container_inline_style .= "gap:" . esc_attr( $ensure_unit($attributes['itemGap'] ?? "20px") ) . ";";

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
$i_padding = $attributes['itemPadding'] ?? [];
if ( ! empty( $i_padding['top'] ) ) $item_styles['padding-top'] = $ensure_unit( $i_padding['top'] );
if ( ! empty( $i_padding['right'] ) ) $item_styles['padding-right'] = $ensure_unit( $i_padding['right'] );
if ( ! empty( $i_padding['bottom'] ) ) $item_styles['padding-bottom'] = $ensure_unit( $i_padding['bottom'] );
if ( ! empty( $i_padding['left'] ) ) $item_styles['padding-left'] = $ensure_unit( $i_padding['left'] );

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
$it_padding = $attributes['itemTitlePadding'] ?? [];
if ( ! empty( $it_padding['top'] ) ) $title_styles['padding-top'] = $ensure_unit( $it_padding['top'] );
if ( ! empty( $it_padding['right'] ) ) $title_styles['padding-right'] = $ensure_unit( $it_padding['right'] );
if ( ! empty( $it_padding['bottom'] ) ) $title_styles['padding-bottom'] = $ensure_unit( $it_padding['bottom'] );
if ( ! empty( $it_padding['left'] ) ) $title_styles['padding-left'] = $ensure_unit( $it_padding['left'] );

$it_margin = $attributes['itemTitleMargin'] ?? [];
if ( ! empty( $it_margin['top'] ) ) $title_styles['margin-top'] = $ensure_unit( $it_margin['top'] );
if ( ! empty( $it_margin['right'] ) ) $title_styles['margin-right'] = $ensure_unit( $it_margin['right'] );
if ( ! empty( $it_margin['bottom'] ) ) $title_styles['margin-bottom'] = $ensure_unit( $it_margin['bottom'] );
if ( ! empty( $it_margin['left'] ) ) $title_styles['margin-left'] = $ensure_unit( $it_margin['left'] );

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
$id_padding = $attributes['itemDescriptionPadding'] ?? [];
if ( ! empty( $id_padding['top'] ) ) $desc_styles['padding-top'] = $ensure_unit( $id_padding['top'] );
if ( ! empty( $id_padding['right'] ) ) $desc_styles['padding-right'] = $ensure_unit( $id_padding['right'] );
if ( ! empty( $id_padding['bottom'] ) ) $desc_styles['padding-bottom'] = $ensure_unit( $id_padding['bottom'] );
if ( ! empty( $id_padding['left'] ) ) $desc_styles['padding-left'] = $ensure_unit( $id_padding['left'] );

$id_margin = $attributes['itemDescriptionMargin'] ?? [];
if ( ! empty( $id_margin['top'] ) ) $desc_styles['margin-top'] = $ensure_unit( $id_margin['top'] );
if ( ! empty( $id_margin['right'] ) ) $desc_styles['margin-right'] = $ensure_unit( $id_margin['right'] );
if ( ! empty( $id_margin['bottom'] ) ) $desc_styles['margin-bottom'] = $ensure_unit( $id_margin['bottom'] );
if ( ! empty( $id_margin['left'] ) ) $desc_styles['margin-left'] = $ensure_unit( $id_margin['left'] );

$id_typo = $attributes['itemDescriptionTypography'] ?? [];
if ( ! empty( $id_typo['fontSize'] ) ) $desc_styles['font-size'] = $id_typo['fontSize'];
if ( ! empty( $id_typo['fontWeight'] ) ) $desc_styles['font-weight'] = $id_typo['fontWeight'];
if ( ! empty( $id_typo['lineHeight'] ) ) $desc_styles['line-height'] = $id_typo['lineHeight'];
if ( ! empty( $id_typo['textTransform'] ) ) $desc_styles['text-transform'] = $id_typo['textTransform'];
if ( ! empty( $id_typo['letterSpacing'] ) ) $desc_styles['letter-spacing'] = $id_typo['letterSpacing'];

$desc_inline_style = $get_inline_styles( $desc_styles );

// Capacities Styles
$capacities_wrapper_styles = [];
$cw_padding = $attributes['capacitiesWrapperPadding'] ?? [];
if ( ! empty( $cw_padding['top'] ) ) $capacities_wrapper_styles['padding-top'] = $ensure_unit( $cw_padding['top'] );
if ( ! empty( $cw_padding['right'] ) ) $capacities_wrapper_styles['padding-right'] = $ensure_unit( $cw_padding['right'] );
if ( ! empty( $cw_padding['bottom'] ) ) $capacities_wrapper_styles['padding-bottom'] = $ensure_unit( $cw_padding['bottom'] );
if ( ! empty( $cw_padding['left'] ) ) $capacities_wrapper_styles['padding-left'] = $ensure_unit( $cw_padding['left'] );

$cw_margin = $attributes['capacitiesWrapperMargin'] ?? [];
if ( ! empty( $cw_margin['top'] ) ) $capacities_wrapper_styles['margin-top'] = $ensure_unit( $cw_margin['top'] );
if ( ! empty( $cw_margin['right'] ) ) $capacities_wrapper_styles['margin-right'] = $ensure_unit( $cw_margin['right'] );
if ( ! empty( $cw_margin['bottom'] ) ) $capacities_wrapper_styles['margin-bottom'] = $ensure_unit( $cw_margin['bottom'] );
if ( ! empty( $cw_margin['left'] ) ) $capacities_wrapper_styles['margin-left'] = $ensure_unit( $cw_margin['left'] );

$capacities_wrapper_inline_style = $get_inline_styles( $capacities_wrapper_styles );

$capacities_item_styles = [];
if ( ! empty( $attributes['capacitiesItemColor'] ) ) {
    $capacities_item_styles['color'] = $attributes['capacitiesItemColor'];
}
if ( ! empty( $attributes['capacitiesItemColorHover'] ) ) {
    $vars[] = '--eshb-agid-cap-item-color-hover:' . esc_attr( $attributes['capacitiesItemColorHover'] );
}

$ci_padding = $attributes['capacitiesItemPadding'] ?? [];
if ( ! empty( $ci_padding['top'] ) ) $capacities_item_styles['padding-top'] = $ensure_unit( $ci_padding['top'] );
if ( ! empty( $ci_padding['right'] ) ) $capacities_item_styles['padding-right'] = $ensure_unit( $ci_padding['right'] );
if ( ! empty( $ci_padding['bottom'] ) ) $capacities_item_styles['padding-bottom'] = $ensure_unit( $ci_padding['bottom'] );
if ( ! empty( $ci_padding['left'] ) ) $capacities_item_styles['padding-left'] = $ensure_unit( $ci_padding['left'] );

$ci_margin = $attributes['capacitiesItemMargin'] ?? [];
if ( ! empty( $ci_margin['top'] ) ) $capacities_item_styles['margin-top'] = $ensure_unit( $ci_margin['top'] );
if ( ! empty( $ci_margin['right'] ) ) $capacities_item_styles['margin-right'] = $ensure_unit( $ci_margin['right'] );
if ( ! empty( $ci_margin['bottom'] ) ) $capacities_item_styles['margin-bottom'] = $ensure_unit( $ci_margin['bottom'] );
if ( ! empty( $ci_margin['left'] ) ) $capacities_item_styles['margin-left'] = $ensure_unit( $ci_margin['left'] );

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
$ip_padding = $attributes['itemPricingPadding'] ?? [];
if ( ! empty( $ip_padding['top'] ) ) $price_styles['padding-top'] = $ensure_unit( $ip_padding['top'] );
if ( ! empty( $ip_padding['right'] ) ) $price_styles['padding-right'] = $ensure_unit( $ip_padding['right'] );
if ( ! empty( $ip_padding['bottom'] ) ) $price_styles['padding-bottom'] = $ensure_unit( $ip_padding['bottom'] );
if ( ! empty( $ip_padding['left'] ) ) $price_styles['padding-left'] = $ensure_unit( $ip_padding['left'] );

$ip_margin = $attributes['itemPricingMargin'] ?? [];
if ( ! empty( $ip_margin['top'] ) ) $price_styles['margin-top'] = $ensure_unit( $ip_margin['top'] );
if ( ! empty( $ip_margin['right'] ) ) $price_styles['margin-right'] = $ensure_unit( $ip_margin['right'] );
if ( ! empty( $ip_margin['bottom'] ) ) $price_styles['margin-bottom'] = $ensure_unit( $ip_margin['bottom'] );
if ( ! empty( $ip_margin['left'] ) ) $price_styles['margin-left'] = $ensure_unit( $ip_margin['left'] );

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
$ib_padding = $attributes['itemButtonPadding'] ?? [];
if ( ! empty( $ib_padding['top'] ) ) $button_styles['padding-top'] = $ensure_unit( $ib_padding['top'] );
if ( ! empty( $ib_padding['right'] ) ) $button_styles['padding-right'] = $ensure_unit( $ib_padding['right'] );
if ( ! empty( $ib_padding['bottom'] ) ) $button_styles['padding-bottom'] = $ensure_unit( $ib_padding['bottom'] );
if ( ! empty( $ib_padding['left'] ) ) $button_styles['padding-left'] = $ensure_unit( $ib_padding['left'] );

$ib_margin = $attributes['itemButtonMargin'] ?? [];
if ( ! empty( $ib_margin['top'] ) ) $button_styles['margin-top'] = $ensure_unit( $ib_margin['top'] );
if ( ! empty( $ib_margin['right'] ) ) $button_styles['margin-right'] = $ensure_unit( $ib_margin['right'] );
if ( ! empty( $ib_margin['bottom'] ) ) $button_styles['margin-bottom'] = $ensure_unit( $ib_margin['bottom'] );
if ( ! empty( $ib_margin['left'] ) ) $button_styles['margin-left'] = $ensure_unit( $ib_margin['left'] );

$ib_typo = $attributes['itemButtonTypography'] ?? [];
if ( ! empty( $ib_typo['fontSize'] ) ) $button_styles['font-size'] = $ib_typo['fontSize'];
if ( ! empty( $ib_typo['fontWeight'] ) ) $button_styles['font-weight'] = $ib_typo['fontWeight'];
if ( ! empty( $ib_typo['lineHeight'] ) ) $button_styles['line-height'] = $ib_typo['lineHeight'];
if ( ! empty( $ib_typo['textTransform'] ) ) $button_styles['text-transform'] = $ib_typo['textTransform'];
if ( ! empty( $ib_typo['letterSpacing'] ) ) $button_styles['letter-spacing'] = $ib_typo['letterSpacing'];

$button_inline_style = $get_inline_styles( $button_styles );

$style_attr = $container_inline_style;
if ( ! empty( $vars ) ) {
    $style_attr .= implode( ';', $vars ) . ';';
}


?>
<div class="eshb-accomodation-grid-block-wrap room-grid-wrap">
    <div class="room-grid eshb-item-grid <?php echo esc_attr($grid_style); ?>" style="<?php echo esc_attr( $style_attr ); ?>">

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
                $eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
                $accomodation_info_group = $eshb_accomodation_metaboxes['accomodation_info_group'];
                $booking_url = get_the_permalink($accomodation_id);
                $price = $hotel_core->get_eshb_price_html('', '', $accomodation_id);
                $numeric_price = $hotel_core->get_eshb_price('', '', $accomodation_id);
                $excerpt = $hotel_view->eshb_custom_excerpt(35, $accomodation_id);
                $perodicity_string = apply_filters( 'eshb_perodicity_string_in_loop', $string_night, $accomodation_id, $eshb_settings);
                include ESHB_PL_PATH . 'public/includes/gutenberg/blocks/accomodationgrid/src/accomodationgrid/grid-styles/' . $grid_style .".php";  

            endwhile;
            wp_reset_postdata();
            ?>
        
    </div>
</div>
