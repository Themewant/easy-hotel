<?php
/**
 * PHP file to use when rendering the `easy-hotel/accomodationgrid` block on the front-end.
 */


$attributes = $attributes ?? [];
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

$centeredSlides = $attributes['centeredSlides'] ? 'true' : 'false';
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


// // Item Styles
$item_styles = [];
$i_border_radius = $attributes['itemBorderRadius'] ?? [];
if ( ! empty( $i_border_radius['top'] ) ) $item_styles['border-top-left-radius'] = $ensure_unit( $i_border_radius['top'] );
if ( ! empty( $i_border_radius['right'] ) ) $item_styles['border-top-right-radius'] = $ensure_unit( $i_border_radius['right'] );
if ( ! empty( $i_border_radius['bottom'] ) ) $item_styles['border-bottom-left-radius'] = $ensure_unit( $i_border_radius['bottom'] );
if ( ! empty( $i_border_radius['left'] ) ) $item_styles['border-bottom-right-radius'] = $ensure_unit( $i_border_radius['left'] );

if(!empty($item_styles)) {
    $vars[] = '--eshb-acmglr-item-border-radius:' . implode(' ', $item_styles);
}

// Button Styles
if ( ! empty( $attributes['navBtnBgColor'] ) ) {
    $vars[] = '--eshb-acmglrnv-bg:' . esc_attr( $attributes['navBtnBgColor'] );
}
if ( ! empty( $attributes['navBtnBgColor'] ) ) {
    $vars[] = '--eshb-acmglrnv-bg-hover:' . esc_attr( $attributes['navBtnBgColor'] );
}
if ( ! empty( $attributes['navBtnColor'] ) ) {
    $vars[] = '--eshb-acmglrnv-color:' . esc_attr( $attributes['navBtnColor'] );
}
if ( ! empty( $attributes['navBtnColorHover'] ) ) {
    $vars[] = '--eshb-acmglrnv-color-hover:' . esc_attr( $attributes['navBtnColorHover'] );
}

$nextBtnBorderRadius = $attributes['nextBtnBorderRadius'] ?? [];
if ( ! empty( $nextBtnBorderRadius['top'] ) ) $nav_button_styles['border-top-left-radius'] = $ensure_unit( $nextBtnBorderRadius['top'] );
if ( ! empty( $nextBtnBorderRadius['right'] ) ) $nav_button_styles['border-top-right-radius'] = $ensure_unit( $nextBtnBorderRadius['right'] );
if ( ! empty( $nextBtnBorderRadius['bottom'] ) ) $nav_button_styles['border-bottom-left-radius'] = $ensure_unit( $nextBtnBorderRadius['bottom'] );
if ( ! empty( $nextBtnBorderRadius['left'] ) ) $nav_button_styles['border-bottom-right-radius'] = $ensure_unit( $nextBtnBorderRadius['left'] );

if(!empty($nav_button_styles)) {
    $vars[] = '--eshb-acmglrnv-next-btn-border-radius:' . implode(' ', $nav_button_styles);
}

$prevBtnBorderRadius = $attributes['prevBtnBorderRadius'] ?? [];
if ( ! empty( $prevBtnBorderRadius['top'] ) ) $nav_button_styles['border-top-left-radius'] = $ensure_unit( $prevBtnBorderRadius['top'] );
if ( ! empty( $prevBtnBorderRadius['right'] ) ) $nav_button_styles['border-top-right-radius'] = $ensure_unit( $prevBtnBorderRadius['right'] );
if ( ! empty( $prevBtnBorderRadius['bottom'] ) ) $nav_button_styles['border-bottom-left-radius'] = $ensure_unit( $prevBtnBorderRadius['bottom'] );
if ( ! empty( $prevBtnBorderRadius['left'] ) ) $nav_button_styles['border-bottom-right-radius'] = $ensure_unit( $prevBtnBorderRadius['left'] );

if(!empty($nav_button_styles)) {
    $vars[] = '--eshb-acmglrnv-prev-btn-border-radius:' . implode(' ', $nav_button_styles);
}

$navbtn_padding = $attributes['navBtnPadding'] ?? [];
if ( ! empty( $navbtn_padding['top'] ) ) $nav_button_styles['padding-top'] = $ensure_unit( $navbtn_padding['top'] );
if ( ! empty( $navbtn_padding['right'] ) ) $nav_button_styles['padding-right'] = $ensure_unit( $navbtn_padding['right'] );
if ( ! empty( $navbtn_padding['bottom'] ) ) $nav_button_styles['padding-bottom'] = $ensure_unit( $navbtn_padding['bottom'] );
if ( ! empty( $navbtn_padding['left'] ) ) $nav_button_styles['padding-left'] = $ensure_unit( $navbtn_padding['left'] );

if(!empty($navbtn_padding )) {
    $vars[] = '--eshb-acmglrnv-pd:' . implode(';', $nav_button_styles);
}

// Dots Styles
if ( ! empty( $attributes['dotsBgColor'] ) ) {
    $vars[] = '--eshb-acmglrnv-dots-bg:' . esc_attr( $attributes['dotsBgColor'] );
}
if ( ! empty( $attributes['dotsBgColorHover'] ) ) {
    $vars[] = '--eshb-acmglrnv-dots-bg-hover:' . esc_attr( $attributes['dotsBgColorHover'] );
}
if ( ! empty( $attributes['dotsColor'] ) ) {
    $vars[] = '--eshb-acmglrnv-dots-color:' . esc_attr( $attributes['dotsColor'] );
}
if ( ! empty( $attributes['dotsColorHover'] ) ) {
    $vars[] = '--eshb-acmglrnv-dots-color-hover:' . esc_attr( $attributes['dotsColorHover'] );
}

if ( ! empty( $attributes['dotsPadding'] ) ) {
    $vars[] = '--eshb-acmglrnv-dots-padding:' . implode(';', $attributes['dotsPadding']);
}

if ( ! empty( $attributes['dotsBorderRadius'] ) ) {
    $vars[] = '--eshb-acmglrnv-dots-border-radius:' . implode(' ', $attributes['dotsBorderRadius']);
}

if ( ! empty( $attributes['dotsSize'] ) ) {
    $vars[] = '--eshb-acmglrnv-dots-size:' . esc_attr( $ensure_unit($attributes['dotsSize']) );
}


$style_attr = '';
if ( ! empty( $vars ) ) {
    $style_attr .= implode( ';', $vars ) . ';';
}

$ESHB_View = new ESHB_View();
$accomodation_id = get_the_ID();
$unique          = wp_rand(2012, 35120);
$thumbnail_size  = 'full';
$galleryDots = true;
$galleryNav = true;
?>
<div 
    class="eshb-accomodation-gallery-block-wrapper eshb-accomodation-gallery-block-wrapper-<?php echo esc_attr($unique); ?>" 
    style="<?php echo esc_attr($style_attr); ?>"
    data-accomodation-id="<?php echo esc_attr($accomodation_id); ?>"
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
    data-speed="<?php echo esc_attr($speed); ?>"
    >
    <?php echo esc_html($ESHB_View->eshb_get_gallery_html($accomodation_id, $unique, $thumbnail_size, $galleryDots, $galleryNav)); ?>
</div>
