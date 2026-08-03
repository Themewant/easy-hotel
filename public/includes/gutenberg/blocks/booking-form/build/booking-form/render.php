<?php
/**
 * PHP file to use when rendering the `easy-hotel/booking-form` block on the front-end.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#render
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $attributes is supplied by WordPress to the block render callback.
$eshb_attributes = $attributes ?? [];

$eshb_style = 'style-one';

if ( ! empty( $block->parsed_block['attrs']['className'] ) ) {
    $eshb_class_name = $block->parsed_block['attrs']['className'];

    if ( str_contains( $eshb_class_name, 'is-style-two' ) ) {
        $eshb_style = 'style-two';
    }
}
$posts = get_posts([
    'post_type'      => 'eshb_accommodation',
    'posts_per_page' => 1,
    'fields'         => 'ids',
]);

$eshb_first_accommodation_id = $posts[0] ?? 0;
$eshb_accomodation_id = !empty($eshb_attributes['accomodationId']) ? $eshb_attributes['accomodationId'] : $eshb_first_accommodation_id;

if(is_single() ) {
   $eshb_accomodation_id = get_the_ID();
}

// Helper function to ensure units
$eshb_ensure_unit = function( $value ) {
    if ( $value === '' || $value === null ) return '0px';
    if ( is_numeric( $value ) && $value != 0 ) return $value . 'px';
    return $value;
};

// Collect CSS variables
$eshb_vars = [];
$eshb_default_background_color = '#fff';
$eshb_default_background_color_hover = '#fff';
if($eshb_style == 'style-two') {
    $eshb_default_background_color = 'var(--eshb-dark-color)';
    $eshb_default_background_color_hover = 'var(--eshb-dark-color)';
}

if(!empty($eshb_attributes['customBackgroundColor'])) {
    $eshb_vars[] = '--eshb-bkf-bg:' . esc_attr($eshb_attributes['customBackgroundColor']);
}else{
    $eshb_vars[] = '--eshb-bkf-bg:' . $eshb_default_background_color;
}

if(!empty($eshb_attributes['customBackgroundColorHover'])) {
    $eshb_vars[] = '--eshb-bkf-bg-hover:' . esc_attr($eshb_attributes['customBackgroundColorHover']);
}else{
    $eshb_vars[] = '--eshb-bkf-bg-hover:' . $eshb_default_background_color_hover;
}

$eshb_padding = $eshb_attributes['padding'] ?? [];
$eshb_vars[] = '--eshb-bkf-pt:' . esc_attr( $eshb_ensure_unit( $eshb_padding['top'] ?? '35px' ) );
$eshb_vars[] = '--eshb-bkf-pr:' . esc_attr( $eshb_ensure_unit( $eshb_padding['right'] ?? '40px' ) );
$eshb_vars[] = '--eshb-bkf-pb:' . esc_attr( $eshb_ensure_unit( $eshb_padding['bottom'] ?? '35px' ) );
$eshb_vars[] = '--eshb-bkf-pl:' . esc_attr( $eshb_ensure_unit( $eshb_padding['left'] ?? '40px' ) );

$eshb_border_radius = $eshb_attributes['borderRadius'] ?? [];
$eshb_vars[] = '--eshb-bkf-bt:' . esc_attr( $eshb_ensure_unit( $eshb_border_radius['top'] ?? '0px' ) );
$eshb_vars[] = '--eshb-bkf-br:' . esc_attr( $eshb_ensure_unit( $eshb_border_radius['right'] ?? '0px' ) );
$eshb_vars[] = '--eshb-bkf-bl:' . esc_attr( $eshb_ensure_unit( $eshb_border_radius['bottom'] ?? '0px' ) );
$eshb_vars[] = '--eshb-bkf-br:' . esc_attr( $eshb_ensure_unit( $eshb_border_radius['left'] ?? '0px' ) );

$eshb_default_form_title_color = $eshb_style == 'style-two' ? 'var(--eshb-white-color)' : 'var(--eshb-dark-color)';
$eshb_form_title_color = !empty($eshb_attributes['formTitleColor']) ? $eshb_attributes['formTitleColor'] : $eshb_default_form_title_color;
if ( ! empty( $eshb_form_title_color ) ) {
    $eshb_vars[] = '--eshb-bkf-form-title-color:' . esc_attr( $eshb_form_title_color );
}

$eshb_form_title_color_hover = !empty($eshb_attributes['formTitleColorHover']) ? $eshb_attributes['formTitleColorHover'] : $eshb_default_form_title_color;
if ( ! empty( $eshb_form_title_color_hover ) ) {
    $eshb_vars[] = '--eshb-bkf-form-title-color-hover:' . esc_attr( $eshb_form_title_color_hover );
}

$eshb_fg_padding = $eshb_attributes['fieldGroupPadding'] ?? [];
$eshb_vars[] = '--eshb-bkfgp-pt:' . esc_attr( $eshb_ensure_unit( $eshb_fg_padding['top'] ?? '0px' ) );
$eshb_vars[] = '--eshb-bkfgp-pr:' . esc_attr( $eshb_ensure_unit( $eshb_fg_padding['right'] ?? '0px' ) );
$eshb_vars[] = '--eshb-bkfgp-pb:' . esc_attr( $eshb_ensure_unit( $eshb_fg_padding['bottom'] ?? '0px' ) );
$eshb_vars[] = '--eshb-bkfgp-pl:' . esc_attr( $eshb_ensure_unit( $eshb_fg_padding['left'] ?? '0px' ) );

$eshb_default_fg_title_color = $eshb_style == 'style-two' ? 'var(--eshb-white-color)' : 'var(--eshb-dark-color)';
$eshb_fg_title_color = $eshb_attributes['groupTitleColor'] ?? $eshb_default_fg_title_color;
if ( ! empty( $eshb_fg_title_color ) ) {
    $eshb_vars[] = '--eshb-bkf-field-group-title-color:' . esc_attr( $eshb_fg_title_color );
}

$eshb_fg_title_color_hover = $eshb_attributes['groupTitleColorHover'] ?? $eshb_default_fg_title_color;
if ( ! empty( $eshb_fg_title_color_hover ) ) {
    $eshb_vars[] = '--eshb-bkf-field-group-title-color-hover:' . esc_attr( $eshb_fg_title_color_hover );
}

$eshb_fg_title_typo = $eshb_attributes['groupTitleTypography'] ?? [];
if ( ! empty( $eshb_fg_title_typo ) ) {
    $eshb_vars[] = '--eshb-bkf-field-group-title-fs:' . esc_attr( $eshb_fg_title_typo['fontSize'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-field-group-title-fw:' . esc_attr( $eshb_fg_title_typo['fontWeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-field-group-title-lh:' . esc_attr( $eshb_fg_title_typo['lineHeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-field-group-title-tt:' . esc_attr( $eshb_fg_title_typo['textTransform'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-field-group-title-ls:' . esc_attr( $eshb_fg_title_typo['letterSpacing'] ?? 'inherit' );
}

$eshb_default_field_label_color = $eshb_style == 'style-two' ? 'var(--eshb-white-color)' : 'var(--eshb-dark-color)';
$eshb_field_label_color = $eshb_attributes['fieldLabelColor'] ?? $eshb_default_field_label_color;
if ( ! empty( $eshb_field_label_color ) ) {
    $eshb_vars[] = '--eshb-bkf-field-label-color:' . esc_attr( $eshb_field_label_color );
}

$eshb_field_label_color_hover = $eshb_attributes['fieldLabelColorHover'] ?? $eshb_default_field_label_color;
if ( ! empty( $eshb_field_label_color_hover ) ) {
    $eshb_vars[] = '--eshb-bkf-field-label-color-hover:' . esc_attr( $eshb_field_label_color_hover );
}

$eshb_field_text_color = $eshb_attributes['fieldTextColor'] ?? $eshb_default_field_label_color;    
if ( ! empty( $eshb_field_text_color ) ) {
    $eshb_vars[] = '--eshb-bkf-field-text-color:' . esc_attr( $eshb_field_text_color );
}

$eshb_field_text_color_hover = $eshb_attributes['fieldTextColorHover'] ?? $eshb_default_field_label_color;    
if ( ! empty( $eshb_field_text_color_hover ) ) {
    $eshb_vars[] = '--eshb-bkf-field-text-color-hover:' . esc_attr( $eshb_field_text_color_hover );
}

$eshb_field_border_radius = $eshb_attributes['fieldBorderRadius'] ?? [];
if ( ! empty( $eshb_field_border_radius ) ) {
    $eshb_vars[] = '--eshb-bkf-br-tl:' . esc_attr( $eshb_ensure_unit( $eshb_field_border_radius['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-br-tr:' . esc_attr( $eshb_ensure_unit( $eshb_field_border_radius['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-br-br:' . esc_attr( $eshb_ensure_unit( $eshb_field_border_radius['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-br-bl:' . esc_attr( $eshb_ensure_unit( $eshb_field_border_radius['left'] ?? '0px' ) );
}

$eshb_margin = $eshb_attributes['margin'] ?? [];
if ( ! empty( $eshb_margin ) ) {
    $eshb_vars[] = '--eshb-bkf-mt:' . esc_attr( $eshb_ensure_unit( $eshb_margin['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-mr:' . esc_attr( $eshb_ensure_unit( $eshb_margin['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-mb:' . esc_attr( $eshb_ensure_unit( $eshb_margin['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-ml:' . esc_attr( $eshb_ensure_unit( $eshb_margin['left'] ?? '0px' ) );
}

$eshb_box_shadow = $eshb_attributes['boxShadow'] ?? '';
if ( ! empty( $eshb_box_shadow ) ) {
    $eshb_vars[] = '--eshb-bkf-box-shadow:' . esc_attr( $eshb_box_shadow );
}

$eshb_box_shadow_hover = $eshb_attributes['boxShadowHover'] ?? '';
if ( ! empty( $eshb_box_shadow_hover ) ) {
    $eshb_vars[] = '--eshb-bkf-box-shadow-hover:' . esc_attr( $eshb_box_shadow_hover );
}

$eshb_fl_typo = $eshb_attributes['fieldLabelTypography'] ?? [];
if ( ! empty( $eshb_fl_typo ) ) {
    $eshb_vars[] = '--eshb-bkf-fl-fs:' . esc_attr( $eshb_fl_typo['fontSize'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-fl-fw:' . esc_attr( $eshb_fl_typo['fontWeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-fl-lh:' . esc_attr( $eshb_fl_typo['lineHeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-fl-tt:' . esc_attr( $eshb_fl_typo['textTransform'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-fl-ls:' . esc_attr( $eshb_fl_typo['letterSpacing'] ?? 'inherit' );
}


$eshb_ft_typo = $eshb_attributes['fieldTextTypography'] ?? [];
if ( ! empty( $eshb_ft_typo ) ) {
    $eshb_vars[] = '--eshb-bkf-ft-fs:' . esc_attr( $eshb_ft_typo['fontSize'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-ft-fw:' . esc_attr( $eshb_ft_typo['fontWeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-ft-lh:' . esc_attr( $eshb_ft_typo['lineHeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-ft-tt:' . esc_attr( $eshb_ft_typo['textTransform'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-ft-ls:' . esc_attr( $eshb_ft_typo['letterSpacing'] ?? 'inherit' );
}

$eshb_calendar_icon_color = $eshb_attributes['calendarIconColor'] ?? '';
if ( ! empty( $eshb_calendar_icon_color ) ) {
    $eshb_vars[] = '--eshb-bkf-calendar-icon-color:' . esc_attr( $eshb_calendar_icon_color );
}

$eshb_calendar_icon_color_hover = $eshb_attributes['calendarIconColorHover'] ?? '';
if ( ! empty( $eshb_calendar_icon_color_hover ) ) {
    $eshb_vars[] = '--eshb-bkf-calendar-icon-color-hover:' . esc_attr( $eshb_calendar_icon_color_hover );
}

$eshb_calendar_icon_size = $eshb_attributes['calendarIconSize'] ?? '';
if ( ! empty( $eshb_calendar_icon_size ) ) {
    $eshb_vars[] = '--eshb-bkf-calendar-icon-size:' . esc_attr( $eshb_calendar_icon_size ) . 'px';
}

$eshb_calendar_icon_position_x = $eshb_attributes['calendarIconPositionX'] ?? '';
if ( ! empty( $eshb_calendar_icon_position_x ) ) {
    $eshb_vars[] = '--eshb-bkf-calendar-icon-position-x:' . esc_attr( $eshb_calendar_icon_position_x ) . '%';
}

$eshb_calendar_icon_position_y = $eshb_attributes['calendarIconPositionY'] ?? '';
if ( ! empty( $eshb_calendar_icon_position_y ) ) {
    $eshb_vars[] = '--eshb-bkf-calendar-icon-position-y:' . esc_attr( $eshb_calendar_icon_position_y ) . '%';
}

$eshb_pm_btn_bg_color = $eshb_attributes['plusMinusBtnBackgroundColor'] ?? '';
if ( ! empty( $eshb_pm_btn_bg_color ) ) {
    $eshb_vars[] = '--eshb-bkf-pm-btn-bg-color:' . esc_attr( $eshb_pm_btn_bg_color );
}

$eshb_pm_btn_bg_color_hover = $eshb_attributes['plusMinusBtnBackgroundColorHover'] ?? '';
if ( ! empty( $eshb_pm_btn_bg_color_hover ) ) {
    $eshb_vars[] = '--eshb-bkf-pm-btn-bg-color-hover:' . esc_attr( $eshb_pm_btn_bg_color_hover );
}

$eshb_pm_btn_text_color = $eshb_attributes['plusMinusBtnTextColor'] ?? '';
if ( ! empty( $eshb_pm_btn_text_color ) ) {
    $eshb_vars[] = '--eshb-bkf-pm-btn-text-color:' . esc_attr( $eshb_pm_btn_text_color );
}

$eshb_pm_btn_text_color_hover = $eshb_attributes['plusMinusBtnTextColorHover'] ?? '';
if ( ! empty( $eshb_pm_btn_text_color_hover ) ) {
    $eshb_vars[] = '--eshb-bkf-pm-btn-text-color-hover:' . esc_attr( $eshb_pm_btn_text_color_hover );
}

$eshb_pm_btn_typo = $eshb_attributes['plusMinusBtnTypography'] ?? [];
if ( ! empty( $eshb_pm_btn_typo ) ) {
    $eshb_vars[] = '--eshb-bkf-pm-btn-fs:' . esc_attr( $eshb_pm_btn_typo['fontSize'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-pm-btn-fw:' . esc_attr( $eshb_pm_btn_typo['fontWeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-pm-btn-lh:' . esc_attr( $eshb_pm_btn_typo['lineHeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-pm-btn-tt:' . esc_attr( $eshb_pm_btn_typo['textTransform'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-pm-btn-ls:' . esc_attr( $eshb_pm_btn_typo['letterSpacing'] ?? 'inherit' );
}

$eshb_pm_btn_padding = $eshb_attributes['plusMinusBtnPadding'] ?? [];
if ( ! empty( $eshb_pm_btn_padding ) ) {
    $eshb_vars[] = '--eshb-bkf-pm-btn-pt:' . esc_attr( $eshb_ensure_unit( $eshb_pm_btn_padding['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-pm-btn-pr:' . esc_attr( $eshb_ensure_unit( $eshb_pm_btn_padding['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-pm-btn-pb:' . esc_attr( $eshb_ensure_unit( $eshb_pm_btn_padding['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-pm-btn-pl:' . esc_attr( $eshb_ensure_unit( $eshb_pm_btn_padding['left'] ?? '0px' ) );
}


$eshb_submit_btn_bg_color = $eshb_attributes['submitBtnBackgroundColor'] ?? '';
if ( ! empty( $eshb_submit_btn_bg_color ) ) {
    $eshb_vars[] = '--eshb-bkf-submit-btn-bg-color:' . esc_attr( $eshb_submit_btn_bg_color );
}

$eshb_submit_btn_bg_color_hover = $eshb_attributes['submitBtnBackgroundColorHover'] ?? '';
if ( ! empty( $eshb_submit_btn_bg_color_hover ) ) {
    $eshb_vars[] = '--eshb-bkf-submit-btn-bg-color-hover:' . esc_attr( $eshb_submit_btn_bg_color_hover );
}

$eshb_submit_btn_text_color = $eshb_attributes['submitBtnTextColor'] ?? '';
if ( ! empty( $eshb_submit_btn_text_color ) ) {
    $eshb_vars[] = '--eshb-bkf-submit-btn-text-color:' . esc_attr( $eshb_submit_btn_text_color );
}

$eshb_submit_btn_text_color_hover = $eshb_attributes['submitBtnTextColorHover'] ?? '';
if ( ! empty( $eshb_submit_btn_text_color_hover ) ) {
    $eshb_vars[] = '--eshb-bkf-submit-btn-text-color-hover:' . esc_attr( $eshb_submit_btn_text_color_hover );
}

$eshb_submit_btn_typo = $eshb_attributes['submitBtnTypography'] ?? [];
if ( ! empty( $eshb_submit_btn_typo ) ) {
    $eshb_vars[] = '--eshb-bkf-submit-btn-fs:' . esc_attr( $eshb_submit_btn_typo['fontSize'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-submit-btn-fw:' . esc_attr( $eshb_submit_btn_typo['fontWeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-submit-btn-lh:' . esc_attr( $eshb_submit_btn_typo['lineHeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-submit-btn-tt:' . esc_attr( $eshb_submit_btn_typo['textTransform'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-submit-btn-ls:' . esc_attr( $eshb_submit_btn_typo['letterSpacing'] ?? 'inherit' );
}

$eshb_submit_btn_padding = $eshb_attributes['submitBtnPadding'] ?? [];
if ( ! empty( $eshb_submit_btn_padding ) ) {
    $eshb_vars[] = '--eshb-bkf-submit-btn-pt:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_padding['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-submit-btn-pr:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_padding['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-submit-btn-pb:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_padding['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-submit-btn-pl:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_padding['left'] ?? '0px' ) );
}

$eshb_submit_btn_margin = $eshb_attributes['submitBtnMargin'] ?? [];
if ( ! empty( $eshb_submit_btn_margin ) ) {
    $eshb_vars[] = '--eshb-bkf-submit-btn-mt:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_margin['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-submit-btn-mr:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_margin['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-submit-btn-mb:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_margin['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-submit-btn-ml:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_margin['left'] ?? '0px' ) );
}

$eshb_submit_btn_border_radius = $eshb_attributes['submitBtnBorderRadius'] ?? [];
if ( ! empty( $eshb_submit_btn_border_radius ) ) {
    $eshb_vars[] = '--eshb-bkf-submit-btn-tr:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_border_radius['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-submit-btn-tl:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_border_radius['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-submit-btn-br:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_border_radius['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-submit-btn-bl:' . esc_attr( $eshb_ensure_unit( $eshb_submit_btn_border_radius['left'] ?? '0px' ) );
}

// Extra Services
$eshb_extra_services_color = $eshb_attributes['extraServicesColor'] ?? '';
if ( ! empty( $eshb_extra_services_color ) ) {
    $eshb_vars[] = '--eshb-bkf-es-color:' . esc_attr( $eshb_extra_services_color );
}

$eshb_extra_services_color_hover = $eshb_attributes['extraServicesColorHover'] ?? '';
if ( ! empty( $eshb_extra_services_color_hover ) ) {
    $eshb_vars[] = '--eshb-bkf-es-color-hover:' . esc_attr( $eshb_extra_services_color_hover );
}

$eshb_es_typo = $eshb_attributes['extraServicesTypography'] ?? [];
if ( ! empty( $eshb_es_typo ) ) {
    $eshb_vars[] = '--eshb-bkf-es-fs:' . esc_attr( $eshb_es_typo['fontSize'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-es-fw:' . esc_attr( $eshb_es_typo['fontWeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-es-lh:' . esc_attr( $eshb_es_typo['lineHeight'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-es-tt:' . esc_attr( $eshb_es_typo['textTransform'] ?? 'inherit' );
    $eshb_vars[] = '--eshb-bkf-es-ls:' . esc_attr( $eshb_es_typo['letterSpacing'] ?? 'inherit' );
}

$eshb_extra_services_margin = $eshb_attributes['extraServicesMargin'] ?? [];
if ( ! empty( $eshb_extra_services_margin ) ) {
    $eshb_vars[] = '--eshb-bkf-es-mt:' . esc_attr( $eshb_ensure_unit( $eshb_extra_services_margin['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-es-mr:' . esc_attr( $eshb_ensure_unit( $eshb_extra_services_margin['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-es-mb:' . esc_attr( $eshb_ensure_unit( $eshb_extra_services_margin['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-es-ml:' . esc_attr( $eshb_ensure_unit( $eshb_extra_services_margin['left'] ?? '0px' ) );
}

$eshb_service_checkbox_border_radius = $eshb_attributes['serviceCheckboxBorderRadius'] ?? [];
if ( ! empty( $eshb_service_checkbox_border_radius ) ) {
    $eshb_vars[] = '--eshb-bkf-service-checkbox-br:' . esc_attr( $eshb_ensure_unit( $eshb_service_checkbox_border_radius['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-service-checkbox-br:' . esc_attr( $eshb_ensure_unit( $eshb_service_checkbox_border_radius['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-service-checkbox-br:' . esc_attr( $eshb_ensure_unit( $eshb_service_checkbox_border_radius['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-service-checkbox-br:' . esc_attr( $eshb_ensure_unit( $eshb_service_checkbox_border_radius['left'] ?? '0px' ) );
}

$eshb_service_qty_border_radius = $eshb_attributes['serviceQtyBorderRadius'] ?? [];
if ( ! empty( $eshb_service_qty_border_radius ) ) {
    $eshb_vars[] = '--eshb-bkf-service-qty-br:' . esc_attr( $eshb_ensure_unit( $eshb_service_qty_border_radius['top'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-service-qty-br:' . esc_attr( $eshb_ensure_unit( $eshb_service_qty_border_radius['right'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-service-qty-br:' . esc_attr( $eshb_ensure_unit( $eshb_service_qty_border_radius['bottom'] ?? '0px' ) );
    $eshb_vars[] = '--eshb-bkf-service-qty-br:' . esc_attr( $eshb_ensure_unit( $eshb_service_qty_border_radius['left'] ?? '0px' ) );
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
$eshb_selector     = '.eshb-booking-form-block-wrapper.' . $eshb_unique_id;

$eshb_full_responsive_css = '';
$eshb_full_responsive_css .= ESHB_Block_Helper::generate_responsive_css($eshb_selector . ' .eshb-booking .eshb-booking-form.eshb-has-calendar-icon .eshb-calendar-icon', $eshb_calendar_icon_position_x_responsive);
$eshb_full_responsive_css .= ESHB_Block_Helper::generate_responsive_css($eshb_selector . ' .eshb-booking .eshb-booking-form.eshb-has-calendar-icon .eshb-calendar-icon', $eshb_calendar_icon_position_y_responsive);

wp_enqueue_style( $eshb_style_handle );
ESHB_Block_Helper::add_custom_style( $eshb_style_handle, $eshb_selector, $eshb_full_responsive_css, []);   


$ESHB_View = new ESHB_View();
?>

<div class="eshb-booking eshb-booking-form-block-wrapper <?php echo esc_attr( $eshb_unique_id ); ?>" style="<?php echo esc_attr( $eshb_style_attr ); ?>">
    <?php $ESHB_View->eshb_get_booking_form_html( $eshb_accomodation_id, $eshb_style ); ?>
</div>