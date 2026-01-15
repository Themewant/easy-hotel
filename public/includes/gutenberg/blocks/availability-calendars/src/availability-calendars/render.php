<?php
/**
 * PHP file to use when rendering the `easy-hotel/accomodationgrid` block on the front-end.
 */
$accomodation_id = get_the_ID();
$ESHB_View = new ESHB_View();
?>

<div <?php echo get_block_wrapper_attributes( [
    'class' => 'eshb-availability-calendars-block-wrapper'
] ); ?>>
    <?php $ESHB_View->eshb_get_availability_calendar_html($accomodation_id, '', false); ?>
</div>