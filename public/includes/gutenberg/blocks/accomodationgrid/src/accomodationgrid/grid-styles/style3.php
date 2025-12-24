<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="grid-item relative text-center wow fadeInUp animated" data-wow-delay="<?php echo esc_attr( $animation_delay )?>s" style="<?php echo esc_attr($item_inline_style); ?>">
    <div class="relative overflow-hidden">
        <div class="p-0 px-3 abs fw-600 ms-3 mt-3 best-seller">
            <?php 
                if(isset($eshb_accomodation_metaboxes['is_best_seller']) && $eshb_accomodation_metaboxes['is_best_seller'] == true){
                    echo esc_html__('Best Seller', 'easy-hotel');
                };
            ?>
        </div>
        <?php 
            if(has_post_thumbnail($accomodation_id)) {
                $thumbnail_url = get_the_post_thumbnail_url( $accomodation_id, $thumbnail_size);
            } else {
                $thumbnail_url = ESHB_DIR_URL . 'public/assets/img/placeholder.png';
            }
        ?>
        <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="Thumbnail" class="thumbnail">
    </div>
    <div class="p-3 pb-1 w-100 text-center">
        <h4 class="mt-2 mb-0 p-title" style="<?php echo esc_attr($title_inline_style); ?>"><?php echo esc_html(get_the_title($accomodation_id)); ?></h4>
        <div class="text-center mb-3 capacities" style="<?php echo esc_attr( $capacities_wrapper_inline_style ); ?>">
            <?php 
                 if( ! empty( $accomodation_info_group ) && is_array($accomodation_info_group) && count($accomodation_info_group) > 0){
                    $x = 0;
                    foreach ( $accomodation_info_group as $group ) { 
                        $x++;
                        if($x >= 3) break;
                        ?>
                        <span class="mx-2 capacity" style="<?php echo esc_attr( $capacities_item_inline_style ); ?>">
                            <?php echo esc_html($group['info_title']); ?>
                        </span>
                    <?php }
                    ?>
                    <?php 
                if(!empty($numeric_price)){
                ?>
                    <span class="mx-2 capacity" style="<?php echo esc_attr($price_inline_style); ?>"><?php echo wp_kses_post($price); ?><span class="label pricing-perodicity" style="<?php echo esc_attr($price_periodicity_inline_style); ?>"> / <?php echo esc_html( eshb_get_translated_string($perodicity_string) );?></span></span>
                <?php 
                    } 
                ?>
                    
            <?php }
            do_action( 'eshb_after_capacities_info_html', $accomodation_id, $eshb_settings );
            ?>
        </div>
        
    </div>
    <a class="details-btn rts-btn btn-main w-100" href="<?php echo esc_url( $booking_url ); ?>" style="<?php echo esc_attr($button_inline_style); ?>"><?php echo esc_html( $btn_text )?></a>
</div>