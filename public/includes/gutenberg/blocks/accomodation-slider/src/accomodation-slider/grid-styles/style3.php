<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="swiper-slide">
    <div class="grid-item relative text-center wow fadeInUp animated" data-wow-delay="<?php echo esc_attr( $eshb_animation_delay )?>s" style="<?php echo esc_attr($eshb_item_inline_style); ?>">
        <div class="relative overflow-hidden">
            <div class="p-0 px-3 abs fw-600 ms-3 mt-3 best-seller">
                <?php 
                    if(isset($eshb_accomodation_metaboxes['is_best_seller']) && $eshb_accomodation_metaboxes['is_best_seller'] == true){
                        echo esc_html__('Best Seller', 'easy-hotel');
                    };
                ?>
            </div>
            <?php 
                if(has_post_thumbnail($eshb_accomodation_id)) {
                    $eshb_thumbnail_url = get_the_post_thumbnail_url( $eshb_accomodation_id, $eshb_thumbnail_size);
                } else {
                    $eshb_thumbnail_url = ESHB_DIR_URL . 'public/assets/img/placeholder.png';
                }
            ?>
            <img src="<?php echo esc_url( $eshb_thumbnail_url ); ?>" alt="Thumbnail" class="thumbnail">
        </div>
        <div class="p-3 pb-1 w-100 text-center">
            <h4 class="mt-2 mb-0 p-title" style="<?php echo esc_attr($eshb_title_inline_style); ?>"><?php echo esc_html(get_the_title($eshb_accomodation_id)); ?></h4>
            <div class="text-center mb-3 capacities">
                <?php 
                    if( ! empty( $eshb_accomodation_info_group ) && is_array($eshb_accomodation_info_group) && count($eshb_accomodation_info_group) > 0){
                        $eshb_x = 0;
                        foreach ( $eshb_accomodation_info_group as $eshb_group ) { 
                            $eshb_x++;
                            if($eshb_x >= 3) break;
                            ?>
                            <span class="mx-2 capacity" style="<?php echo esc_attr( $eshb_capacities_item_inline_style ); ?>">
                                <?php echo esc_html($eshb_group['info_title']); ?>
                            </span>
                        <?php }
                        ?>
                        <?php 
                    if(!empty($eshb_numeric_price)){
                    ?>
                        <span class="mx-2 capacity" style="<?php echo esc_attr($eshb_price_inline_style); ?>"><?php echo wp_kses_post($eshb_price); ?><span class="label pricing-perodicity" style="<?php echo esc_attr($eshb_price_periodicity_inline_style); ?>"> / <?php echo esc_html( eshb_get_translated_string($eshb_perodicity_string) );?></span></span>
                    <?php 
                        } 
                    ?>
                        
                <?php }
                do_action( 'eshb_after_capacities_info_html', $eshb_accomodation_id, $eshb_settings );
                ?>
            </div>
            
        </div>
        <a class="details-btn rts-btn btn-main w-100" href="<?php echo esc_url( $eshb_booking_url ); ?>" style="<?php echo esc_attr($eshb_button_inline_style); ?>"><?php echo esc_html( $eshb_btn_text )?></a>
    </div>
</div>
