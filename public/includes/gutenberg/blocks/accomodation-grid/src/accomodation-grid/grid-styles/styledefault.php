<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="grid-item wow fadeInUp animated" data-wow-delay="<?php echo esc_attr( $eshb_animation_delay )?>s" style="<?php echo esc_attr($eshb_item_inline_style); ?>">
    <div class="item-inner">
        <?php 
            if(has_post_thumbnail($eshb_accomodation_id)) {
                $eshb_thumbnail_url = get_the_post_thumbnail_url( $eshb_accomodation_id, $eshb_thumbnail_size);
            } else {
                $eshb_thumbnail_url = ESHB_DIR_URL . 'public/assets/img/placeholder.png';
            }
        ?>
        <img src="<?php echo esc_url( $eshb_thumbnail_url ); ?>" alt="Thumbnail" class="thumbnail">

        <div class="pricing-info">
            <?php 
            if(!empty($eshb_numeric_price)){
            ?>
                <div class="label">
                <?php echo esc_html( $eshb_pricing_prefix ); ?>
                </div>
                <h3 class="price" style="<?php echo esc_attr($eshb_price_inline_style); ?>"><?php echo wp_kses_post($eshb_price); ?><span class="label pricing-perodicity" style="<?php echo esc_attr($eshb_price_periodicity_inline_style); ?>"> / <?php echo esc_html( eshb_get_translated_string($eshb_perodicity_string) );?></span></h3>
            <?php 
                } 
            ?>
            <a class="details-btn" href="<?php echo esc_url( $eshb_booking_url ); ?>" style="<?php echo esc_attr($eshb_button_inline_style); ?>"><?php echo esc_html( $eshb_btn_text ); ?></a>
        </div>

        <div class="hover-bg-one" style="<?php echo esc_attr($eshb_overlay_inline_style); ?>"></div>

        <div class="details-info">
            <?php 
                do_action( 'eshb_before_details_info_html', $eshb_accomodation_id, $eshb_settings );
            ?>
            <h3 class="p-title" style="<?php echo esc_attr($eshb_title_inline_style); ?>"><?php echo wp_kses_post(get_the_title($eshb_accomodation_id)); ?></h3>
            <div class="capacities text-center" style="background-size: cover; background-repeat: no-repeat; <?php echo esc_attr( $eshb_capacities_wrapper_inline_style ); ?>">
                <?php 
                    $eshb_i = 0;
                    if( ! empty( $eshb_accomodation_info_group ) && is_array($eshb_accomodation_info_group) && count($eshb_accomodation_info_group) > 0){
                        foreach ($eshb_accomodation_info_group as $eshb_key => $eshb_group) { 
                            $eshb_i++;
                            if($eshb_i >= 3) break;
                            ?>
                            <span class="capacity" style="<?php echo esc_attr( $eshb_capacities_item_inline_style ); ?>">
                                <?php echo esc_html($eshb_group['info_title']); ?>
                            </span>
                        <?php }
                    }
                    
                ?>
            </div>
        </div>

        <div class="hover-bg-two" style="<?php echo esc_attr($eshb_overlay_two_inline_style); ?>"></div>
    </div>
</div>