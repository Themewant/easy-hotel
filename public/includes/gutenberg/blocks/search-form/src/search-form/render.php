<?php
/**
 * PHP file to use when rendering the `easy-hotel/searchform` block on the front-end.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#render
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $attributes is supplied by WordPress to the block render callback.
$eshb_attributes = $attributes ?? [];

// Helper function to ensure units
$eshb_ensure_unit = function( $value ) {
    if ( $value === '' || $value === null ) return '0px';
    if ( is_numeric( $value ) && $value != 0 ) return $value . 'px';
    return $value;
};

// Collect CSS variables
$eshb_vars = [];


if(!empty($eshb_attributes['customBackgroundColor'])) {
    $eshb_vars[] = '--eshb-scf-bg:' . esc_attr($eshb_attributes['customBackgroundColor']);
}else{
    $eshb_vars[] = '--eshb-scf-bg:' . 'initial';
}

if(!empty($eshb_attributes['customBackgroundColorHover'])) {
    $eshb_vars[] = '--eshb-scf-bg-hover:' . esc_attr($eshb_attributes['customBackgroundColorHover']);
}

$eshb_padding = $eshb_attributes['padding'] ?? [];
$eshb_vars[] = '--eshb-scf-pt:' . esc_attr( $eshb_ensure_unit( $eshb_padding['top'] ?? '0px' ) );
$eshb_vars[] = '--eshb-scf-pr:' . esc_attr( $eshb_ensure_unit( $eshb_padding['right'] ?? '0px' ) );
$eshb_vars[] = '--eshb-scf-pb:' . esc_attr( $eshb_ensure_unit( $eshb_padding['bottom'] ?? '0px' ) );
$eshb_vars[] = '--eshb-scf-pl:' . esc_attr( $eshb_ensure_unit( $eshb_padding['left'] ?? '0px' ) );

$eshb_border_radius = $eshb_attributes['borderRadius'] ?? [];
$eshb_vars[] = '--eshb-scf-bt:' . esc_attr( $eshb_ensure_unit( $eshb_border_radius['top'] ?? '0px' ) );
$eshb_vars[] = '--eshb-scf-br:' . esc_attr( $eshb_ensure_unit( $eshb_border_radius['right'] ?? '0px' ) );
$eshb_vars[] = '--eshb-scf-bl:' . esc_attr( $eshb_ensure_unit( $eshb_border_radius['bottom'] ?? '0px' ) );
$eshb_vars[] = '--eshb-scf-br:' . esc_attr( $eshb_ensure_unit( $eshb_border_radius['left'] ?? '0px' ) );

$eshb_fg_padding = $eshb_attributes['fieldGroupPadding'] ?? [];
$eshb_vars[] = '--eshb-scfgp-pt:' . esc_attr( $eshb_ensure_unit( $eshb_fg_padding['top'] ?? '0px' ) );
$eshb_vars[] = '--eshb-scfgp-pr:' . esc_attr( $eshb_ensure_unit( $eshb_fg_padding['right'] ?? '0px' ) );
$eshb_vars[] = '--eshb-scfgp-pb:' . esc_attr( $eshb_ensure_unit( $eshb_fg_padding['bottom'] ?? '0px' ) );
$eshb_vars[] = '--eshb-scfgp-pl:' . esc_attr( $eshb_ensure_unit( $eshb_fg_padding['left'] ?? '0px' ) );

$eshb_field_label_color = $eshb_attributes['fieldLabelColor'] ?? '';
if ( ! empty( $eshb_field_label_color ) ) {
    $eshb_vars[] = '--eshb-scf-field-label-color:' . esc_attr( $eshb_field_label_color );
}

$eshb_field_label_color_hover = $eshb_attributes['fieldLabelColorHover'] ?? '';
if ( ! empty( $eshb_field_label_color_hover ) ) {
    $eshb_vars[] = '--eshb-scf-field-label-color-hover:' . esc_attr( $eshb_field_label_color_hover );
}

$eshb_field_text_color = $eshb_attributes['fieldTextColor'] ?? '';    
if ( ! empty( $eshb_field_text_color ) ) {
    $eshb_vars[] = '--eshb-scf-field-text-color:' . esc_attr( $eshb_field_text_color );
}

$eshb_field_text_color_hover = $eshb_attributes['fieldTextColorHover'] ?? '';    
if ( ! empty( $eshb_field_text_color_hover ) ) {
    $eshb_vars[] = '--eshb-scf-field-text-color-hover:' . esc_attr( $eshb_field_text_color_hover );
}

$eshb_margin = $eshb_attributes['margin'] ?? [];
if ( ! empty( $eshb_margin ) ) {
    $eshb_vars[] = '--eshb-scf-mt:' . esc_attr( $eshb_ensure_unit( $eshb_margin['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-mr:' . esc_attr( $eshb_ensure_unit( $eshb_margin['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-mb:' . esc_attr( $eshb_ensure_unit( $eshb_margin['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-ml:' . esc_attr( $eshb_ensure_unit( $eshb_margin['left'] ?? '0px' ) );
}

$eshb_box_shadow = $eshb_attributes['boxShadow'] ?? '';
if ( ! empty( $eshb_box_shadow ) ) {
    $eshb_vars[] = '--eshb-scf-box-shadow:' . esc_attr( $eshb_box_shadow );
}

$eshb_box_shadow_hover = $eshb_attributes['boxShadowHover'] ?? '';
if ( ! empty( $eshb_box_shadow_hover ) ) {
    $eshb_vars[] = '--eshb-scf-box-shadow-hover:' . esc_attr( $eshb_box_shadow_hover );
}

$eshb_fl_typo = $eshb_attributes['fieldLabelTypography'] ?? [];
if ( ! empty( $eshb_fl_typo ) ) {
    $eshb_vars[] = '--eshb-scf-fl-fs:' . esc_attr( $eshb_fl_typo['fontSize'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-fl-fw:' . esc_attr( $eshb_fl_typo['fontWeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-fl-lh:' . esc_attr( $eshb_fl_typo['lineHeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-fl-tt:' . esc_attr( $eshb_fl_typo['textTransform'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-fl-ls:' . esc_attr( $eshb_fl_typo['letterSpacing'] ?? 'inherit' );
}

$eshb_ft_typo = $eshb_attributes['fieldTextTypography'] ?? [];
if ( ! empty( $eshb_ft_typo ) ) {
    $eshb_vars[] = '--eshb-scf-ft-fs:' . esc_attr( $eshb_ft_typo['fontSize'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-ft-fw:' . esc_attr( $eshb_ft_typo['fontWeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-ft-lh:' . esc_attr( $eshb_ft_typo['lineHeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-ft-tt:' . esc_attr( $eshb_ft_typo['textTransform'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-ft-ls:' . esc_attr( $eshb_ft_typo['letterSpacing'] ?? 'inherit' );
}

$eshb_pm_btn_bg_color = $eshb_attributes['plusMinusBtnBackgroundColor'] ?? '';
if ( ! empty( $eshb_pm_btn_bg_color ) ) {
    $eshb_vars[] = '--eshb-scf-pm-btn-bg-color:' . esc_attr( $eshb_pm_btn_bg_color );
}

$eshb_pm_btn_bg_color_hover = $eshb_attributes['plusMinusBtnBackgroundColorHover'] ?? '';
if ( ! empty( $eshb_pm_btn_bg_color_hover ) ) {
    $eshb_vars[] = '--eshb-scf-pm-btn-bg-color-hover:' . esc_attr( $eshb_pm_btn_bg_color_hover );
}

$eshb_pm_btn_text_color = $eshb_attributes['plusMinusBtnTextColor'] ?? '';
if ( ! empty( $eshb_pm_btn_text_color ) ) {
    $eshb_vars[] = '--eshb-scf-pm-btn-text-color:' . esc_attr( $eshb_pm_btn_text_color );
}

$eshb_pm_btn_text_color_hover = $eshb_attributes['plusMinusBtnTextColorHover'] ?? '';
if ( ! empty( $eshb_pm_btn_text_color_hover ) ) {
    $eshb_vars[] = '--eshb-scf-pm-btn-text-color-hover:' . esc_attr( $eshb_pm_btn_text_color_hover );
}

$eshb_pm_btn_typo = $eshb_attributes['plusMinusBtnTypography'] ?? [];
if ( ! empty( $eshb_pm_btn_typo ) ) {
    $eshb_vars[] = '--eshb-scf-pm-btn-fs:' . esc_attr( $eshb_pm_btn_typo['fontSize'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-pm-btn-fw:' . esc_attr( $eshb_pm_btn_typo['fontWeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-pm-btn-lh:' . esc_attr( $eshb_pm_btn_typo['lineHeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-pm-btn-tt:' . esc_attr( $eshb_pm_btn_typo['textTransform'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-pm-btn-ls:' . esc_attr( $eshb_pm_btn_typo['letterSpacing'] ?? 'inherit' );
}

$eshb_pm_btn_padding = $eshb_attributes['plusMinusBtnPadding'] ?? [];
if ( ! empty( $eshb_pm_btn_padding ) ) {
    $eshb_vars[] = '--eshb-scf-pm-btn-pt:' . esc_attr( $eshb_ensure_unit( $eshb_pm_btn_padding['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-pm-btn-pr:' . esc_attr( $eshb_ensure_unit( $eshb_pm_btn_padding['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-pm-btn-pb:' . esc_attr( $eshb_ensure_unit( $eshb_pm_btn_padding['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-pm-btn-pl:' . esc_attr( $eshb_ensure_unit( $eshb_pm_btn_padding['left'] ?? '0px' ) );
}


$eshb_submit_btn_bg_color = $eshb_attributes['submitBtnBackgroundColor'] ?? '';
if ( ! empty( $eshb_submit_btn_bg_color ) ) {
    $eshb_vars[] = '--eshb-scf-submit-btn-bg-color:' . esc_attr( $eshb_submit_btn_bg_color );
}

$eshb_submit_btn_bg_color_hover = $eshb_attributes['submitBtnBackgroundColorHover'] ?? '';
if ( ! empty( $eshb_submit_btn_bg_color_hover ) ) {
    $eshb_vars[] = '--eshb-scf-submit-btn-bg-color-hover:' . esc_attr( $eshb_submit_btn_bg_color_hover );
}

$eshb_submit_btn_text_color = $eshb_attributes['submitBtnTextColor'] ?? '';
if ( ! empty( $eshb_submit_btn_text_color ) ) {
    $eshb_vars[] = '--eshb-scf-submit-btn-text-color:' . esc_attr( $eshb_submit_btn_text_color );
}

$eshb_submit_btn_text_color_hover = $eshb_attributes['submitBtnTextColorHover'] ?? '';
if ( ! empty( $eshb_submit_btn_text_color_hover ) ) {
    $eshb_vars[] = '--eshb-scf-submit-btn-text-color-hover:' . esc_attr( $eshb_submit_btn_text_color_hover );
}

$eshb_submit_btn_typo = $eshb_attributes['submitBtnTypography'] ?? [];
if ( ! empty( $eshb_submit_btn_typo ) ) {
    $eshb_vars[] = '--eshb-scf-submit-btn-fs:' . esc_attr( $eshb_submit_btn_typo['fontSize'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-submit-btn-fw:' . esc_attr( $eshb_submit_btn_typo['fontWeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-submit-btn-lh:' . esc_attr( $eshb_submit_btn_typo['lineHeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-submit-btn-tt:' . esc_attr( $eshb_submit_btn_typo['textTransform'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-scf-submit-btn-ls:' . esc_attr( $eshb_submit_btn_typo['letterSpacing'] ?? 'inherit' );
}

$eshb_submit_btn_padding = $eshb_attributes['submitBtnPadding'] ?? [];
if ( ! empty( $eshb_submit_btn_padding ) ) {
    $eshb_vars[] = '--eshb-scf-submit-btn-pt:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_padding['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-submit-btn-pr:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_padding['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-submit-btn-pb:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_padding['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-submit-btn-pl:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_padding['left'] ?? '0px' ) );
}

$eshb_submit_btn_margin = $eshb_attributes['submitBtnMargin'] ?? [];
if ( ! empty( $eshb_submit_btn_margin ) ) {
    $eshb_vars[] = '--eshb-scf-submit-btn-mt:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_margin['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-submit-btn-mr:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_margin['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-submit-btn-mb:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_margin['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-scf-submit-btn-ml:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_margin['left'] ?? '0px' ) );
}


// Inject CSS variables into existing wrapper
$eshb_style_attr = '';
if ( $eshb_vars ) {
    $eshb_style_attr = implode( ';', $eshb_vars );
}

$eshb_calendar_icon_position_x_responsive = ['desktop' => [], 'tablet' => [], 'mobile' => []];
ESHB_Block_Helper::add_responsive_vars($eshb_attributes, $eshb_calendar_icon_position_x_responsive, 'calendarIconPositionX', 'right', [], false);

$eshb_calendar_icon_position_y_responsive = ['desktop' => [], 'tablet' => [], 'mobile' => []];
ESHB_Block_Helper::add_responsive_vars($eshb_attributes, $eshb_calendar_icon_position_y_responsive, 'calendarIconPositionY', 'top', [], false);

$eshb_style_handle = 'eshb-style';
$eshb_unique_id    = $eshb_attributes['blockId'];
$eshb_selector     = '.eshb-search-form-block-wrapper.' . $eshb_unique_id;

$eshb_full_responsive_css = '';
$eshb_full_responsive_css .= ESHB_Block_Helper::generate_responsive_css($eshb_selector . ' .eshb-search .eshb-search-form.eshb-has-calendar-icon .eshb-calendar-icon', $eshb_calendar_icon_position_x_responsive);
$eshb_full_responsive_css .= ESHB_Block_Helper::generate_responsive_css($eshb_selector . ' .eshb-search .eshb-search-form.eshb-has-calendar-icon .eshb-calendar-icon', $eshb_calendar_icon_position_y_responsive);

wp_enqueue_style( $eshb_style_handle );
ESHB_Block_Helper::add_custom_style( $eshb_style_handle, $eshb_selector, $eshb_full_responsive_css, []);   

?>
<div class="eshb-search-form-block-wrapper <?php echo esc_attr( $eshb_unique_id ); ?>" style="<?php echo esc_attr( $eshb_style_attr ); ?>"><?php echo do_shortcode( '[eshb_search_form]' ); ?></div>