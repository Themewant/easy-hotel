<?php
/**
 * PHP file to use when rendering the `easy-hotel/accomodationgrid` block on the front-end.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$accomodation_id = get_the_ID();
$ESHB_View       = new ESHB_View();
?>

<div <?php echo wp_kses_data( get_block_wrapper_attributes( array(
    'class' => 'eshb-check-in-out-times-block-wrapper',
) ) ); ?>>
    <?php $ESHB_View->eshb_get_eshb_check_in_out_times_html( false ); ?>
</div>
