<?php
/**
 * PHP file to use when rendering the `easy-hotel/accomodationgrid` block on the front-end.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$eshb_accomodation_id = get_the_ID();
$ESHB_View       = new ESHB_View();
?>

<div <?php echo wp_kses_data( get_block_wrapper_attributes( array(
    'class' => 'eshb-availability-calendars-block-wrapper',
) ) ); ?>>
    <?php $ESHB_View->eshb_get_availability_calendar_html( $eshb_accomodation_id, '', false ); ?>
</div>
