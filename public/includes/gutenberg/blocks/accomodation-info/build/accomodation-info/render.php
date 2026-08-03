<?php
/**
 * PHP file to use when rendering the `easy-hotel/accomodationgrid` block on the front-end.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $attributes is supplied by WordPress to the block render callback.
$eshb_attributes = $attributes ?? [];


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

// Styles

if ( ! empty( $eshb_attributes['textColorHover'] ) ) {
    $eshb_vars[] = '--eshb-acmInfo-text-color-hover:' . esc_attr( $eshb_attributes['textColorHover'] );
}

if ( ! empty( $eshb_attributes['iconColorHover'] ) ) {
    $eshb_vars[] = '--eshb-acmInfo-icon-color-hover:' . esc_attr( $eshb_attributes['iconColorHover'] );
}


$eshb_list_styles = [];
if ( ! empty( $eshb_attributes['spaceBetween'] ) ) {
    $eshb_list_styles['column-gap'] = esc_attr( $eshb_ensure_unit($eshb_attributes['spaceBetween']) );
}
$eshb_list_styles_inline = $eshb_get_inline_styles($eshb_list_styles);

$eshb_title_styles = [];
if ( ! empty( $eshb_attributes['textColor'] ) ) {
    $eshb_title_styles['color'] = esc_attr( $eshb_attributes['textColor'] );
}

if ( ! empty( $eshb_attributes['textSize'] ) ) {
    $eshb_title_styles['font-size'] = esc_attr( $eshb_ensure_unit($eshb_attributes['textSize']) );
}

$eshb_title_styles_inline = $eshb_get_inline_styles($eshb_title_styles);

$eshb_icon_styles = [];
$eshb_img_icon_styles = [];
if ( ! empty( $eshb_attributes['iconSpace'] ) ) {
    $eshb_icon_styles['margin-right'] = esc_attr( $eshb_attributes['iconSpace'] );
}
if ( ! empty( $eshb_attributes['iconColor'] ) ) {
    $eshb_icon_styles['color'] = esc_attr( $eshb_attributes['iconColor'] );
}

if ( ! empty( $eshb_attributes['iconSize'] ) ) {
    $eshb_icon_styles['font-size'] = esc_attr( $eshb_ensure_unit($eshb_attributes['iconSize']) );
    $eshb_img_icon_styles['height'] = esc_attr( $eshb_ensure_unit($eshb_attributes['iconSize']) );
    $eshb_img_icon_styles['width'] = esc_attr( $eshb_ensure_unit($eshb_attributes['iconSize']) );
}

$eshb_icon_styles_inline = $eshb_get_inline_styles($eshb_icon_styles);

$eshb_style_attr = '';
if ( ! empty( $eshb_vars ) ) {
    $eshb_style_attr .= implode( ';', $eshb_vars ) . ';';
}

$eshb_accomodation_id = get_the_ID();
$eshb_accomodation_metaboxes = get_post_meta($eshb_accomodation_id, 'eshb_accomodation_metaboxes', true);
$eshb_accomodation_info_group = !empty($eshb_accomodation_metaboxes['accomodation_info_group']) ? $eshb_accomodation_metaboxes['accomodation_info_group'] : array();

?>
<div class="eshb-accomodation-info-block-wrapper" style="<?php echo esc_attr($eshb_style_attr); ?>">
   <div class="basic-information-list" style="<?php echo esc_attr($eshb_list_styles_inline); ?>">
        <?php 
            if ( ! empty( $eshb_accomodation_info_group ) ) {
                foreach ( $eshb_accomodation_info_group as $eshb_group ) { ?>
                    <p class="info">
                        <?php 
                            if(!empty($eshb_group['info_icon'])){ ?>
                                <i class="info-icon <?php echo esc_html($eshb_group['info_icon']); ?>" style="<?php echo esc_attr($eshb_icon_styles_inline); ?>"></i>
                            <?php }

                            if(!empty($eshb_group['info_icon_img']['url'])){ 
                                $eshb_icon_img_url = $eshb_group['info_icon_img']['url'];
                                ?>
                                <img src="<?php echo esc_url($eshb_icon_img_url); ?>" alt="info Icon" class="info-icon" style="<?php echo esc_attr($eshb_img_icon_styles_inline); ?>">
                            <?php }
                        ?>
                        
                        <span class="info-title" style="<?php echo esc_attr($eshb_title_styles_inline); ?>"><?php echo esc_html($eshb_group['info_title']); ?></span>
                    </p>
                <?php }
            }
        ?>
    </div>
</div>

