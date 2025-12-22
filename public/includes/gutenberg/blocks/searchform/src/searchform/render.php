<?php
/**
 * PHP file to use when rendering the `easy-hotel/searchform` block on the front-end.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#render
 */


$attributes = $attributes ?? [];

// Helper function to ensure units
$ensure_unit = function( $value ) {
    if ( $value === '' || $value === null ) return '0px';
    if ( is_numeric( $value ) && $value != 0 ) return $value . 'px';
    return $value;
};

// Collect CSS variables
$vars = [];


if(!empty($attributes['customBackgroundColor'])) {
    $vars[] = '--eshb-scf-bg:' . esc_attr($attributes['customBackgroundColor']);
}else{
    $vars[] = '--eshb-scf-bg:' . 'initial';
}

if(!empty($attributes['customBackgroundColorHover'])) {
    $vars[] = '--eshb-scf-bg-hover:' . esc_attr($attributes['customBackgroundColorHover']);
}

$padding = $attributes['padding'] ?? [];
$vars[] = '--eshb-scf-pt:' . esc_attr( $ensure_unit( $padding['top'] ?? '0px' ) );
$vars[] = '--eshb-scf-pr:' . esc_attr( $ensure_unit( $padding['right'] ?? '0px' ) );
$vars[] = '--eshb-scf-pb:' . esc_attr( $ensure_unit( $padding['bottom'] ?? '0px' ) );
$vars[] = '--eshb-scf-pl:' . esc_attr( $ensure_unit( $padding['left'] ?? '0px' ) );

$border_radius = $attributes['borderRadius'] ?? []; error_log('$border_radius' . print_r($border_radius, true));
$vars[] = '--eshb-scf-bt:' . esc_attr( $ensure_unit( $border_radius['top'] ?? '0px' ) );
$vars[] = '--eshb-scf-br:' . esc_attr( $ensure_unit( $border_radius['right'] ?? '0px' ) );
$vars[] = '--eshb-scf-bl:' . esc_attr( $ensure_unit( $border_radius['bottom'] ?? '0px' ) );
$vars[] = '--eshb-scf-br:' . esc_attr( $ensure_unit( $border_radius['left'] ?? '0px' ) );

$fg_padding = $attributes['fieldGroupPadding'] ?? [];
$vars[] = '--eshb-scfgp-pt:' . esc_attr( $ensure_unit( $fg_padding['top'] ?? '0px' ) );
$vars[] = '--eshb-scfgp-pr:' . esc_attr( $ensure_unit( $fg_padding['right'] ?? '0px' ) );
$vars[] = '--eshb-scfgp-pb:' . esc_attr( $ensure_unit( $fg_padding['bottom'] ?? '0px' ) );
$vars[] = '--eshb-scfgp-pl:' . esc_attr( $ensure_unit( $fg_padding['left'] ?? '0px' ) );

$field_label_color = $attributes['fieldLabelColor'] ?? '';
if ( ! empty( $field_label_color ) ) {
    $vars[] = '--eshb-scf-field-label-color:' . esc_attr( $field_label_color );
}

$field_label_color_hover = $attributes['fieldLabelColorHover'] ?? '';
if ( ! empty( $field_label_color_hover ) ) {
    $vars[] = '--eshb-scf-field-label-color-hover:' . esc_attr( $field_label_color_hover );
}

$field_text_color = $attributes['fieldTextColor'] ?? '';    
if ( ! empty( $field_text_color ) ) {
    $vars[] = '--eshb-scf-field-text-color:' . esc_attr( $field_text_color );
}

$field_text_color_hover = $attributes['fieldTextColorHover'] ?? '';    
if ( ! empty( $field_text_color_hover ) ) {
    $vars[] = '--eshb-scf-field-text-color-hover:' . esc_attr( $field_text_color_hover );
}

$margin = $attributes['margin'] ?? [];
if ( ! empty( $margin ) ) {
    $vars[] = '--eshb-scf-mt:' . esc_attr( $ensure_unit( $margin['top'] ?? '0px' ) );
    $vars[] = '--eshb-scf-mr:' . esc_attr( $ensure_unit( $margin['right'] ?? '0px' ) );
    $vars[] = '--eshb-scf-mb:' . esc_attr( $ensure_unit( $margin['bottom'] ?? '0px' ) );
    $vars[] = '--eshb-scf-ml:' . esc_attr( $ensure_unit( $margin['left'] ?? '0px' ) );
}

$box_shadow = $attributes['boxShadow'] ?? '';
if ( ! empty( $box_shadow ) ) {
    $vars[] = '--eshb-scf-box-shadow:' . esc_attr( $box_shadow );
}

$box_shadow_hover = $attributes['boxShadowHover'] ?? '';
if ( ! empty( $box_shadow_hover ) ) {
    $vars[] = '--eshb-scf-box-shadow-hover:' . esc_attr( $box_shadow_hover );
}

$fl_typo = $attributes['fieldLabelTypography'] ?? [];
if ( ! empty( $fl_typo ) ) {
    $vars[] = '--eshb-scf-fl-fs:' . esc_attr( $fl_typo['fontSize'] ?? 'inherit' );
    $vars[] = '--eshb-scf-fl-fw:' . esc_attr( $fl_typo['fontWeight'] ?? 'inherit' );
    $vars[] = '--eshb-scf-fl-lh:' . esc_attr( $fl_typo['lineHeight'] ?? 'inherit' );
    $vars[] = '--eshb-scf-fl-tt:' . esc_attr( $fl_typo['textTransform'] ?? 'inherit' );
    $vars[] = '--eshb-scf-fl-ls:' . esc_attr( $fl_typo['letterSpacing'] ?? 'inherit' );
}

$ft_typo = $attributes['fieldTextTypography'] ?? [];
if ( ! empty( $ft_typo ) ) {
    $vars[] = '--eshb-scf-ft-fs:' . esc_attr( $ft_typo['fontSize'] ?? 'inherit' );
    $vars[] = '--eshb-scf-ft-fw:' . esc_attr( $ft_typo['fontWeight'] ?? 'inherit' );
    $vars[] = '--eshb-scf-ft-lh:' . esc_attr( $ft_typo['lineHeight'] ?? 'inherit' );
    $vars[] = '--eshb-scf-ft-tt:' . esc_attr( $ft_typo['textTransform'] ?? 'inherit' );
    $vars[] = '--eshb-scf-ft-ls:' . esc_attr( $ft_typo['letterSpacing'] ?? 'inherit' );
}

$pm_btn_bg_color = $attributes['plusMinusBtnBackgroundColor'] ?? '';
if ( ! empty( $pm_btn_bg_color ) ) {
    $vars[] = '--eshb-scf-pm-btn-bg-color:' . esc_attr( $pm_btn_bg_color );
}

$pm_btn_text_color = $attributes['plusMinusBtnTextColor'] ?? '';
if ( ! empty( $pm_btn_text_color ) ) {
    $vars[] = '--eshb-scf-pm-btn-text-color:' . esc_attr( $pm_btn_text_color );
}

$pm_btn_typo = $attributes['plusMinusBtnTypography'] ?? [];
if ( ! empty( $pm_btn_typo ) ) {
    $vars[] = '--eshb-scf-pm-btn-fs:' . esc_attr( $pm_btn_typo['fontSize'] ?? 'inherit' );
    $vars[] = '--eshb-scf-pm-btn-fw:' . esc_attr( $pm_btn_typo['fontWeight'] ?? 'inherit' );
    $vars[] = '--eshb-scf-pm-btn-lh:' . esc_attr( $pm_btn_typo['lineHeight'] ?? 'inherit' );
    $vars[] = '--eshb-scf-pm-btn-tt:' . esc_attr( $pm_btn_typo['textTransform'] ?? 'inherit' );
    $vars[] = '--eshb-scf-pm-btn-ls:' . esc_attr( $pm_btn_typo['letterSpacing'] ?? 'inherit' );
}

$pm_btn_padding = $attributes['plusMinusBtnPadding'] ?? [];
if ( ! empty( $pm_btn_padding ) ) {
    $vars[] = '--eshb-scf-pm-btn-pt:' . esc_attr( $ensure_unit( $pm_btn_padding['top'] ?? '0px' ) );
    $vars[] = '--eshb-scf-pm-btn-pr:' . esc_attr( $ensure_unit( $pm_btn_padding['right'] ?? '0px' ) );
    $vars[] = '--eshb-scf-pm-btn-pb:' . esc_attr( $ensure_unit( $pm_btn_padding['bottom'] ?? '0px' ) );
    $vars[] = '--eshb-scf-pm-btn-pl:' . esc_attr( $ensure_unit( $pm_btn_padding['left'] ?? '0px' ) );
}

// Get shortcode output
$output = do_shortcode( '[eshb_search_form]' );

// Inject CSS variables into existing wrapper
if ( $vars ) {
    $style_attr = implode( ';', $vars );

    // Add style to first wrapper div with class eshb-search
    $output = preg_replace(
        '/(<div\b[^>]*\bclass\s*=\s*"[^"]*\beshb-search\b[^"]*"[^>]*)>/i',
        '$1 style="' . esc_attr( $style_attr ) . '">',
        $output,
        1
    );
}

echo $output;

