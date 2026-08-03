<?php
/**
 * PHP file to use when rendering the `easy-hotel/accomodationgrid` block on the front-end.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $attributes is supplied by WordPress to the block render callback.
$eshb_attributes = $attributes ?? [];
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

$eshb_centered_slides = $eshb_attributes['centeredSlides'] ? 'true' : 'false';
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


// // Item Styles
$eshb_item_styles = [];
$eshb_i_border_radius = $eshb_attributes['itemBorderRadius'] ?? [];
if ( ! empty( $eshb_i_border_radius['top'] ) ) $eshb_item_styles['border-top-left-radius'] = $eshb_ensure_unit( $eshb_i_border_radius['top'] );
if ( ! empty( $eshb_i_border_radius['right'] ) ) $eshb_item_styles['border-top-right-radius'] = $eshb_ensure_unit( $eshb_i_border_radius['right'] );
if ( ! empty( $eshb_i_border_radius['bottom'] ) ) $eshb_item_styles['border-bottom-left-radius'] = $eshb_ensure_unit( $eshb_i_border_radius['bottom'] );
if ( ! empty( $eshb_i_border_radius['left'] ) ) $eshb_item_styles['border-bottom-right-radius'] = $eshb_ensure_unit( $eshb_i_border_radius['left'] );

if(!empty($eshb_item_styles)) {
    $eshb_vars[] = '--eshb-acmglr-item-border-radius:' . implode(' ', $eshb_item_styles);
}

// Button Styles
if ( ! empty( $eshb_attributes['navBtnBgColor'] ) ) {
    $eshb_vars[] = '--eshb-acmglrnv-bg:' . esc_attr( $eshb_attributes['navBtnBgColor'] );
}
if ( ! empty( $eshb_attributes['navBtnBgColor'] ) ) {
    $eshb_vars[] = '--eshb-acmglrnv-bg-hover:' . esc_attr( $eshb_attributes['navBtnBgColor'] );
}
if ( ! empty( $eshb_attributes['navBtnColor'] ) ) {
    $eshb_vars[] = '--eshb-acmglrnv-color:' . esc_attr( $eshb_attributes['navBtnColor'] );
}
if ( ! empty( $eshb_attributes['navBtnColorHover'] ) ) {
    $eshb_vars[] = '--eshb-acmglrnv-color-hover:' . esc_attr( $eshb_attributes['navBtnColorHover'] );
}

$eshb_next_btn_border_radius = $eshb_attributes['nextBtnBorderRadius'] ?? [];
if ( ! empty( $eshb_next_btn_border_radius['top'] ) ) $eshb_nav_button_styles['border-top-left-radius'] = $eshb_ensure_unit( $eshb_next_btn_border_radius['top'] );
if ( ! empty( $eshb_next_btn_border_radius['right'] ) ) $eshb_nav_button_styles['border-top-right-radius'] = $eshb_ensure_unit( $eshb_next_btn_border_radius['right'] );
if ( ! empty( $eshb_next_btn_border_radius['bottom'] ) ) $eshb_nav_button_styles['border-bottom-left-radius'] = $eshb_ensure_unit( $eshb_next_btn_border_radius['bottom'] );
if ( ! empty( $eshb_next_btn_border_radius['left'] ) ) $eshb_nav_button_styles['border-bottom-right-radius'] = $eshb_ensure_unit( $eshb_next_btn_border_radius['left'] );

if(!empty($eshb_nav_button_styles)) {
    $eshb_vars[] = '--eshb-acmglrnv-next-btn-border-radius:' . implode(' ', $eshb_nav_button_styles);
}

$eshb_prev_btn_border_radius = $eshb_attributes['prevBtnBorderRadius'] ?? [];
if ( ! empty( $eshb_prev_btn_border_radius['top'] ) ) $eshb_nav_button_styles['border-top-left-radius'] = $eshb_ensure_unit( $eshb_prev_btn_border_radius['top'] );
if ( ! empty( $eshb_prev_btn_border_radius['right'] ) ) $eshb_nav_button_styles['border-top-right-radius'] = $eshb_ensure_unit( $eshb_prev_btn_border_radius['right'] );
if ( ! empty( $eshb_prev_btn_border_radius['bottom'] ) ) $eshb_nav_button_styles['border-bottom-left-radius'] = $eshb_ensure_unit( $eshb_prev_btn_border_radius['bottom'] );
if ( ! empty( $eshb_prev_btn_border_radius['left'] ) ) $eshb_nav_button_styles['border-bottom-right-radius'] = $eshb_ensure_unit( $eshb_prev_btn_border_radius['left'] );

if(!empty($eshb_nav_button_styles)) {
    $eshb_vars[] = '--eshb-acmglrnv-prev-btn-border-radius:' . implode(' ', $eshb_nav_button_styles);
}

$eshb_navbtn_padding = $eshb_attributes['navBtnPadding'] ?? [];
if ( ! empty( $eshb_navbtn_padding['top'] ) ) $eshb_nav_button_styles['padding-top'] = $eshb_ensure_unit( $eshb_navbtn_padding['top'] );
if ( ! empty( $eshb_navbtn_padding['right'] ) ) $eshb_nav_button_styles['padding-right'] = $eshb_ensure_unit( $eshb_navbtn_padding['right'] );
if ( ! empty( $eshb_navbtn_padding['bottom'] ) ) $eshb_nav_button_styles['padding-bottom'] = $eshb_ensure_unit( $eshb_navbtn_padding['bottom'] );
if ( ! empty( $eshb_navbtn_padding['left'] ) ) $eshb_nav_button_styles['padding-left'] = $eshb_ensure_unit( $eshb_navbtn_padding['left'] );

if(!empty($eshb_navbtn_padding )) {
    $eshb_vars[] = '--eshb-acmglrnv-pd:' . implode(';', $eshb_nav_button_styles);
}

// Dots Styles
if ( ! empty( $eshb_attributes['dotsBgColor'] ) ) {
    $eshb_vars[] = '--eshb-acmglrnv-dots-bg:' . esc_attr( $eshb_attributes['dotsBgColor'] );
}
if ( ! empty( $eshb_attributes['dotsBgColorHover'] ) ) {
    $eshb_vars[] = '--eshb-acmglrnv-dots-bg-hover:' . esc_attr( $eshb_attributes['dotsBgColorHover'] );
}
if ( ! empty( $eshb_attributes['dotsColor'] ) ) {
    $eshb_vars[] = '--eshb-acmglrnv-dots-color:' . esc_attr( $eshb_attributes['dotsColor'] );
}
if ( ! empty( $eshb_attributes['dotsColorHover'] ) ) {
    $eshb_vars[] = '--eshb-acmglrnv-dots-color-hover:' . esc_attr( $eshb_attributes['dotsColorHover'] );
}

if ( ! empty( $eshb_attributes['dotsPadding'] ) ) {
    $eshb_vars[] = '--eshb-acmglrnv-dots-padding:' . implode(';', $eshb_attributes['dotsPadding']);
}

if ( ! empty( $eshb_attributes['dotsBorderRadius'] ) ) {
    $eshb_vars[] = '--eshb-acmglrnv-dots-border-radius:' . implode(' ', $eshb_attributes['dotsBorderRadius']);
}

if ( ! empty( $eshb_attributes['dotsSize'] ) ) {
    $eshb_vars[] = '--eshb-acmglrnv-dots-size:' . esc_attr( $eshb_ensure_unit($eshb_attributes['dotsSize']) );
}


$eshb_style_attr = '';
if ( ! empty( $eshb_vars ) ) {
    $eshb_style_attr .= implode( ';', $eshb_vars ) . ';';
}

$ESHB_View = new ESHB_View();
$eshb_accomodation_id = get_the_ID();
$eshb_unique          = wp_rand(2012, 35120);
$eshb_thumbnail_size  = 'full';
$eshb_gallery_dots = true;
$eshb_gallery_nav = true;
?>
<div
    class="eshb-accomodation-gallery-block-wrapper eshb-accomodation-gallery-block-wrapper-<?php echo esc_attr($eshb_unique); ?>"
    style="<?php echo esc_attr($eshb_style_attr); ?>"
    data-accomodation-id="<?php echo esc_attr($eshb_accomodation_id); ?>"
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
    data-speed="<?php echo esc_attr($eshb_speed); ?>"
    >
    <?php echo esc_html($ESHB_View->eshb_get_gallery_html($eshb_accomodation_id, $eshb_unique, $eshb_thumbnail_size, $eshb_gallery_dots, $eshb_gallery_nav)); ?>
</div>
