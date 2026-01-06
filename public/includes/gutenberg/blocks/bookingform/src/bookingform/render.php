<?php
/**
 * PHP file to use when rendering the `easy-hotel/bookingform` block on the front-end.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#render
 */


$attributes = $attributes ?? [];

$style = 'style-one';

if ( ! empty( $block->parsed_block['attrs']['className'] ) ) {
    $class_name = $block->parsed_block['attrs']['className'];

    if ( str_contains( $class_name, 'is-style-two' ) ) {
        $style = 'style-two';
    }
}

// $accomodation_id = !empty($attributes['accomodationId']) ? $attributes['accomodationId'] : get_the_ID();
$accomodation_id = get_the_ID();

// Helper function to ensure units
$ensure_unit = function( $value ) {
    if ( $value === '' || $value === null ) return '0px';
    if ( is_numeric( $value ) && $value != 0 ) return $value . 'px';
    return $value;
};

// Collect CSS variables
$vars = [];
$default_background_color = '#fff';
$default_background_color_hover = '#fff';
if($style == 'style-two') {
    $default_background_color = 'var(--eshb-dark-color)';
    $default_background_color_hover = 'var(--eshb-dark-color)';
}

if(!empty($attributes['customBackgroundColor'])) {
    $vars[] = '--eshb-bkf-bg:' . esc_attr($attributes['customBackgroundColor']);
}else{
    $vars[] = '--eshb-bkf-bg:' . $default_background_color;
}

if(!empty($attributes['customBackgroundColorHover'])) {
    $vars[] = '--eshb-bkf-bg-hover:' . esc_attr($attributes['customBackgroundColorHover']);
}else{
    $vars[] = '--eshb-bkf-bg-hover:' . $default_background_color_hover;
}

$padding = $attributes['padding'] ?? [];
$vars[] = '--eshb-bkf-pt:' . esc_attr( $ensure_unit( $padding['top'] ?? '35px' ) );
$vars[] = '--eshb-bkf-pr:' . esc_attr( $ensure_unit( $padding['right'] ?? '40px' ) );
$vars[] = '--eshb-bkf-pb:' . esc_attr( $ensure_unit( $padding['bottom'] ?? '35px' ) );
$vars[] = '--eshb-bkf-pl:' . esc_attr( $ensure_unit( $padding['left'] ?? '40px' ) );

$border_radius = $attributes['borderRadius'] ?? [];
$vars[] = '--eshb-bkf-bt:' . esc_attr( $ensure_unit( $border_radius['top'] ?? '0px' ) );
$vars[] = '--eshb-bkf-br:' . esc_attr( $ensure_unit( $border_radius['right'] ?? '0px' ) );
$vars[] = '--eshb-bkf-bl:' . esc_attr( $ensure_unit( $border_radius['bottom'] ?? '0px' ) );
$vars[] = '--eshb-bkf-br:' . esc_attr( $ensure_unit( $border_radius['left'] ?? '0px' ) );

$form_title_color = $attributes['formTitleColor'] ?? '';
if ( ! empty( $form_title_color ) ) {
    $vars[] = '--eshb-bkf-form-title-color:' . esc_attr( $form_title_color );
}

$form_title_color_hover = $attributes['formTitleColorHover'] ?? '';
if ( ! empty( $form_title_color_hover ) ) {
    $vars[] = '--eshb-bkf-form-title-color-hover:' . esc_attr( $form_title_color_hover );
}

$fg_padding = $attributes['fieldGroupPadding'] ?? [];
$vars[] = '--eshb-bkfgp-pt:' . esc_attr( $ensure_unit( $fg_padding['top'] ?? '0px' ) );
$vars[] = '--eshb-bkfgp-pr:' . esc_attr( $ensure_unit( $fg_padding['right'] ?? '0px' ) );
$vars[] = '--eshb-bkfgp-pb:' . esc_attr( $ensure_unit( $fg_padding['bottom'] ?? '0px' ) );
$vars[] = '--eshb-bkfgp-pl:' . esc_attr( $ensure_unit( $fg_padding['left'] ?? '0px' ) );

$fg_title_color = $attributes['groupTitleColor'] ?? '';
if ( ! empty( $fg_title_color ) ) {
    $vars[] = '--eshb-bkf-field-group-title-color:' . esc_attr( $fg_title_color );
}

$fg_title_color_hover = $attributes['groupTitleColorHover'] ?? '';
if ( ! empty( $fg_title_color_hover ) ) {
    $vars[] = '--eshb-bkf-field-group-title-color-hover:' . esc_attr( $fg_title_color_hover );
}

$fg_title_typo = $attributes['groupTitleTypography'] ?? [];
if ( ! empty( $fg_title_typo ) ) {
    $vars[] = '--eshb-bkf-field-group-title-fs:' . esc_attr( $fg_title_typo['fontSize'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-field-group-title-fw:' . esc_attr( $fg_title_typo['fontWeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-field-group-title-lh:' . esc_attr( $fg_title_typo['lineHeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-field-group-title-tt:' . esc_attr( $fg_title_typo['textTransform'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-field-group-title-ls:' . esc_attr( $fg_title_typo['letterSpacing'] ?? 'inherit' );
}


$field_label_color = $attributes['fieldLabelColor'] ?? '';
if ( ! empty( $field_label_color ) ) {
    $vars[] = '--eshb-bkf-field-label-color:' . esc_attr( $field_label_color );
}

$field_label_color_hover = $attributes['fieldLabelColorHover'] ?? '';
if ( ! empty( $field_label_color_hover ) ) {
    $vars[] = '--eshb-bkf-field-label-color-hover:' . esc_attr( $field_label_color_hover );
}

$field_text_color = $attributes['fieldTextColor'] ?? '';    
if ( ! empty( $field_text_color ) ) {
    $vars[] = '--eshb-bkf-field-text-color:' . esc_attr( $field_text_color );
}

$field_text_color_hover = $attributes['fieldTextColorHover'] ?? '';    
if ( ! empty( $field_text_color_hover ) ) {
    $vars[] = '--eshb-bkf-field-text-color-hover:' . esc_attr( $field_text_color_hover );
}

$field_border_radius = $attributes['fieldBorderRadius'] ?? [];
if ( ! empty( $field_border_radius ) ) {
    $vars[] = '--eshb-bkf-br-tl:' . esc_attr( $ensure_unit( $field_border_radius['top'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-br-tr:' . esc_attr( $ensure_unit( $field_border_radius['right'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-br-br:' . esc_attr( $ensure_unit( $field_border_radius['bottom'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-br-bl:' . esc_attr( $ensure_unit( $field_border_radius['left'] ?? '0px' ) );
}

$margin = $attributes['margin'] ?? [];
if ( ! empty( $margin ) ) {
    $vars[] = '--eshb-bkf-mt:' . esc_attr( $ensure_unit( $margin['top'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-mr:' . esc_attr( $ensure_unit( $margin['right'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-mb:' . esc_attr( $ensure_unit( $margin['bottom'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-ml:' . esc_attr( $ensure_unit( $margin['left'] ?? '0px' ) );
}

$box_shadow = $attributes['boxShadow'] ?? '';
if ( ! empty( $box_shadow ) ) {
    $vars[] = '--eshb-bkf-box-shadow:' . esc_attr( $box_shadow );
}

$box_shadow_hover = $attributes['boxShadowHover'] ?? '';
if ( ! empty( $box_shadow_hover ) ) {
    $vars[] = '--eshb-bkf-box-shadow-hover:' . esc_attr( $box_shadow_hover );
}

$fl_typo = $attributes['fieldLabelTypography'] ?? [];
if ( ! empty( $fl_typo ) ) {
    $vars[] = '--eshb-bkf-fl-fs:' . esc_attr( $fl_typo['fontSize'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-fl-fw:' . esc_attr( $fl_typo['fontWeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-fl-lh:' . esc_attr( $fl_typo['lineHeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-fl-tt:' . esc_attr( $fl_typo['textTransform'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-fl-ls:' . esc_attr( $fl_typo['letterSpacing'] ?? 'inherit' );
}


$ft_typo = $attributes['fieldTextTypography'] ?? [];
if ( ! empty( $ft_typo ) ) {
    $vars[] = '--eshb-bkf-ft-fs:' . esc_attr( $ft_typo['fontSize'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-ft-fw:' . esc_attr( $ft_typo['fontWeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-ft-lh:' . esc_attr( $ft_typo['lineHeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-ft-tt:' . esc_attr( $ft_typo['textTransform'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-ft-ls:' . esc_attr( $ft_typo['letterSpacing'] ?? 'inherit' );
}

$pm_btn_bg_color = $attributes['plusMinusBtnBackgroundColor'] ?? '';
if ( ! empty( $pm_btn_bg_color ) ) {
    $vars[] = '--eshb-bkf-pm-btn-bg-color:' . esc_attr( $pm_btn_bg_color );
}

$pm_btn_bg_color_hover = $attributes['plusMinusBtnBackgroundColorHover'] ?? '';
if ( ! empty( $pm_btn_bg_color_hover ) ) {
    $vars[] = '--eshb-bkf-pm-btn-bg-color-hover:' . esc_attr( $pm_btn_bg_color_hover );
}

$pm_btn_text_color = $attributes['plusMinusBtnTextColor'] ?? '';
if ( ! empty( $pm_btn_text_color ) ) {
    $vars[] = '--eshb-bkf-pm-btn-text-color:' . esc_attr( $pm_btn_text_color );
}

$pm_btn_text_color_hover = $attributes['plusMinusBtnTextColorHover'] ?? '';
if ( ! empty( $pm_btn_text_color_hover ) ) {
    $vars[] = '--eshb-bkf-pm-btn-text-color-hover:' . esc_attr( $pm_btn_text_color_hover );
}

$pm_btn_typo = $attributes['plusMinusBtnTypography'] ?? [];
if ( ! empty( $pm_btn_typo ) ) {
    $vars[] = '--eshb-bkf-pm-btn-fs:' . esc_attr( $pm_btn_typo['fontSize'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-pm-btn-fw:' . esc_attr( $pm_btn_typo['fontWeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-pm-btn-lh:' . esc_attr( $pm_btn_typo['lineHeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-pm-btn-tt:' . esc_attr( $pm_btn_typo['textTransform'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-pm-btn-ls:' . esc_attr( $pm_btn_typo['letterSpacing'] ?? 'inherit' );
}

$pm_btn_padding = $attributes['plusMinusBtnPadding'] ?? [];
if ( ! empty( $pm_btn_padding ) ) {
    $vars[] = '--eshb-bkf-pm-btn-pt:' . esc_attr( $ensure_unit( $pm_btn_padding['top'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-pm-btn-pr:' . esc_attr( $ensure_unit( $pm_btn_padding['right'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-pm-btn-pb:' . esc_attr( $ensure_unit( $pm_btn_padding['bottom'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-pm-btn-pl:' . esc_attr( $ensure_unit( $pm_btn_padding['left'] ?? '0px' ) );
}


$submit_btn_bg_color = $attributes['submitBtnBackgroundColor'] ?? '';
if ( ! empty( $submit_btn_bg_color ) ) {
    $vars[] = '--eshb-bkf-submit-btn-bg-color:' . esc_attr( $submit_btn_bg_color );
}

$submit_btn_bg_color_hover = $attributes['submitBtnBackgroundColorHover'] ?? '';
if ( ! empty( $submit_btn_bg_color_hover ) ) {
    $vars[] = '--eshb-bkf-submit-btn-bg-color-hover:' . esc_attr( $submit_btn_bg_color_hover );
}

$submit_btn_text_color = $attributes['submitBtnTextColor'] ?? '';
if ( ! empty( $submit_btn_text_color ) ) {
    $vars[] = '--eshb-bkf-submit-btn-text-color:' . esc_attr( $submit_btn_text_color );
}

$submit_btn_text_color_hover = $attributes['submitBtnTextColorHover'] ?? '';
if ( ! empty( $submit_btn_text_color_hover ) ) {
    $vars[] = '--eshb-bkf-submit-btn-text-color-hover:' . esc_attr( $submit_btn_text_color_hover );
}

$submit_btn_typo = $attributes['submitBtnTypography'] ?? [];
if ( ! empty( $submit_btn_typo ) ) {
    $vars[] = '--eshb-bkf-submit-btn-fs:' . esc_attr( $submit_btn_typo['fontSize'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-submit-btn-fw:' . esc_attr( $submit_btn_typo['fontWeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-submit-btn-lh:' . esc_attr( $submit_btn_typo['lineHeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-submit-btn-tt:' . esc_attr( $submit_btn_typo['textTransform'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-submit-btn-ls:' . esc_attr( $submit_btn_typo['letterSpacing'] ?? 'inherit' );
}

$submit_btn_padding = $attributes['submitBtnPadding'] ?? [];
if ( ! empty( $submit_btn_padding ) ) {
    $vars[] = '--eshb-bkf-submit-btn-pt:' . esc_attr( $ensure_unit( $submit_btn_padding['top'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-submit-btn-pr:' . esc_attr( $ensure_unit( $submit_btn_padding['right'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-submit-btn-pb:' . esc_attr( $ensure_unit( $submit_btn_padding['bottom'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-submit-btn-pl:' . esc_attr( $ensure_unit( $submit_btn_padding['left'] ?? '0px' ) );
}

$submit_btn_margin = $attributes['submitBtnMargin'] ?? [];
if ( ! empty( $submit_btn_margin ) ) {
    $vars[] = '--eshb-bkf-submit-btn-mt:' . esc_attr( $ensure_unit( $submit_btn_margin['top'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-submit-btn-mr:' . esc_attr( $ensure_unit( $submit_btn_margin['right'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-submit-btn-mb:' . esc_attr( $ensure_unit( $submit_btn_margin['bottom'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-submit-btn-ml:' . esc_attr( $ensure_unit( $submit_btn_margin['left'] ?? '0px' ) );
}

$submit_btn_border_radius = $attributes['submitBtnBorderRadius'] ?? [];
if ( ! empty( $submit_btn_border_radius ) ) {
    $vars[] = '--eshb-bkf-submit-btn-tr:' . esc_attr( $ensure_unit( $submit_btn_border_radius['top'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-submit-btn-tl:' . esc_attr( $ensure_unit( $submit_btn_border_radius['right'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-submit-btn-br:' . esc_attr( $ensure_unit( $submit_btn_border_radius['bottom'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-submit-btn-bl:' . esc_attr( $ensure_unit( $submit_btn_border_radius['left'] ?? '0px' ) );
}

// Extra Services
$extra_services_color = $attributes['extraServicesColor'] ?? '';
if ( ! empty( $extra_services_color ) ) {
    $vars[] = '--eshb-bkf-es-color:' . esc_attr( $extra_services_color );
}

$extra_services_color_hover = $attributes['extraServicesColorHover'] ?? '';
if ( ! empty( $extra_services_color_hover ) ) {
    $vars[] = '--eshb-bkf-es-color-hover:' . esc_attr( $extra_services_color_hover );
}

$es_typo = $attributes['extraServicesTypography'] ?? [];
if ( ! empty( $es_typo ) ) {
    $vars[] = '--eshb-bkf-es-fs:' . esc_attr( $es_typo['fontSize'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-es-fw:' . esc_attr( $es_typo['fontWeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-es-lh:' . esc_attr( $es_typo['lineHeight'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-es-tt:' . esc_attr( $es_typo['textTransform'] ?? 'inherit' );
    $vars[] = '--eshb-bkf-es-ls:' . esc_attr( $es_typo['letterSpacing'] ?? 'inherit' );
}

$extra_services_margin = $attributes['extraServicesMargin'] ?? [];
if ( ! empty( $extra_services_margin ) ) {
    $vars[] = '--eshb-bkf-es-mt:' . esc_attr( $ensure_unit( $extra_services_margin['top'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-es-mr:' . esc_attr( $ensure_unit( $extra_services_margin['right'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-es-mb:' . esc_attr( $ensure_unit( $extra_services_margin['bottom'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-es-ml:' . esc_attr( $ensure_unit( $extra_services_margin['left'] ?? '0px' ) );
}

$service_checkbox_border_radius = $attributes['serviceCheckboxBorderRadius'] ?? [];
if ( ! empty( $service_checkbox_border_radius ) ) {
    $vars[] = '--eshb-bkf-service-checkbox-br:' . esc_attr( $ensure_unit( $service_checkbox_border_radius['top'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-service-checkbox-br:' . esc_attr( $ensure_unit( $service_checkbox_border_radius['right'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-service-checkbox-br:' . esc_attr( $ensure_unit( $service_checkbox_border_radius['bottom'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-service-checkbox-br:' . esc_attr( $ensure_unit( $service_checkbox_border_radius['left'] ?? '0px' ) );
}

$service_qty_border_radius = $attributes['serviceQtyBorderRadius'] ?? [];
if ( ! empty( $service_qty_border_radius ) ) {
    $vars[] = '--eshb-bkf-service-qty-br:' . esc_attr( $ensure_unit( $service_qty_border_radius['top'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-service-qty-br:' . esc_attr( $ensure_unit( $service_qty_border_radius['right'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-service-qty-br:' . esc_attr( $ensure_unit( $service_qty_border_radius['bottom'] ?? '0px' ) );
    $vars[] = '--eshb-bkf-service-qty-br:' . esc_attr( $ensure_unit( $service_qty_border_radius['left'] ?? '0px' ) );
}

// Inject CSS variables into existing wrapper
$style_attr = '';
if ( $vars ) {
    $style_attr = implode( ';', $vars );
}

$ESHB_View = new ESHB_View();
?>

<div class="eshb-booking eshb-booking-form-block-wrapper" style="<?php echo esc_attr( $style_attr ); ?>">
    <?php $ESHB_View->eshb_get_booking_form_html( $accomodation_id, $style ); ?>
</div>