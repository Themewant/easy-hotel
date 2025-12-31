<?php
/**
 * PHP file to use when rendering the `easy-hotel/accomodationgrid` block on the front-end.
 */


$attributes = $attributes ?? [];

$spaceBetween = $attributes['spaceBetween'] ?? 10;


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

// Styles

if ( ! empty( $attributes['textColorHover'] ) ) {
    $vars[] = '--eshb-acmInfo-text-color-hover:' . esc_attr( $attributes['textColorHover'] );
}

if ( ! empty( $attributes['iconColorHover'] ) ) {
    $vars[] = '--eshb-acmInfo-icon-color-hover:' . esc_attr( $attributes['iconColorHover'] );
}


$list_styles = [];
if ( ! empty( $attributes['spaceBetween'] ) ) {
    $list_styles['column-gap'] = esc_attr( $attributes['spaceBetween'] );
}
$list_styles_inline = $get_inline_styles($list_styles);

$title_styles = [];
if ( ! empty( $attributes['textColor'] ) ) {
    $title_styles['color'] = esc_attr( $attributes['textColor'] );
}

if ( ! empty( $attributes['textSize'] ) ) {
    $title_styles['font-size'] = esc_attr( $ensure_unit($attributes['textSize']) );
}

$title_styles_inline = $get_inline_styles($title_styles);

$icon_styles = [];
$img_icon_styles = [];
if ( ! empty( $attributes['iconSpace'] ) ) {
    $icon_styles['margin-right'] = esc_attr( $attributes['iconSpace'] );
}
if ( ! empty( $attributes['iconColor'] ) ) {
    $icon_styles['color'] = esc_attr( $attributes['iconColor'] );
}

if ( ! empty( $attributes['iconSize'] ) ) {
    $icon_styles['font-size'] = esc_attr( $ensure_unit($attributes['iconSize']) );
    $img_icon_styles['height'] = esc_attr( $ensure_unit($attributes['iconSize']) );
    $img_icon_styles['width'] = esc_attr( $ensure_unit($attributes['iconSize']) );
}

$icon_styles_inline = $get_inline_styles($icon_styles);

$style_attr = '';
if ( ! empty( $vars ) ) {
    $style_attr .= implode( ';', $vars ) . ';';
}

$accomodation_id = get_the_ID();
$eshb_accomodation_metaboxes = get_post_meta($accomodation_id, 'eshb_accomodation_metaboxes', true);
$accomodation_info_group = !empty($eshb_accomodation_metaboxes['accomodation_info_group']) ? $eshb_accomodation_metaboxes['accomodation_info_group'] : array();

?>
<div class="eshb-accomodation-info-block-wrapper" style="<?php echo esc_attr($style_attr); ?>">
   <div class="basic-information-list" style="<?php echo esc_attr($list_styles_inline); ?>">
        <?php 
            if ( ! empty( $accomodation_info_group ) ) {
                foreach ( $accomodation_info_group as $group ) { ?>
                    <p class="info">
                        <?php 
                            if(!empty($group['info_icon'])){ ?>
                                <i class="info-icon <?php echo esc_html($group['info_icon']); ?>" style="<?php echo esc_attr($icon_styles_inline); ?>"></i>
                            <?php }

                            if(!empty($group['info_icon_img']['url'])){ 
                                $icon_img_url = $group['info_icon_img']['url'];
                                ?>
                                <img src="<?php echo esc_url($icon_img_url); ?>" alt="info Icon" class="info-icon" style="<?php echo esc_attr($img_icon_styles_inline); ?>">
                            <?php }
                        ?>
                        
                        <span class="info-title" style="<?php echo esc_attr($title_styles_inline); ?>"><?php echo esc_html($group['info_title']); ?></span>
                    </p>
                <?php }
            }
        ?>
    </div>
</div>

