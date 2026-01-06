<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="swiper-slide">
    <div class="grid-item wow fadeInUp animated" data-wow-delay="<?php echo esc_attr( $animation_delay )?>s" style="<?php echo esc_attr($item_inline_style); ?>">
        <div class="item-inner">
            
            <?php 
                if(has_post_thumbnail($accomodation_id)) {
                    $thumbnail_url = get_the_post_thumbnail_url( $accomodation_id, $thumbnail_size);
                } else {
                    $thumbnail_url = ESHB_DIR_URL . 'public/assets/img/placeholder.png';
                }
            ?>
            <img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="Thumbnail" class="thumbnail">

            <div class="pricing-info">
                <?php 
                if(!empty($numeric_price)){
                ?>
                    <div class="label">
                    <?php echo esc_html( $pricing_prefix ); ?>
                    </div>
                    <h3 class="price" style="<?php echo esc_attr($price_inline_style); ?>"><?php echo wp_kses_post($price); ?><span class="label pricing-perodicity" style="<?php echo esc_attr($price_periodicity_inline_style); ?>"> / <?php echo esc_html( eshb_get_translated_string($perodicity_string) );?></span></h3>
                <?php 
                    } 
                ?>
                
                <a class="details-btn" href="<?php echo esc_url( $booking_url ); ?>" style="<?php echo esc_attr($button_inline_style); ?>"><?php echo esc_html( $btn_text );?></a>
            </div>

            <div class="hover-bg-one" style="<?php echo esc_attr($overlay_inline_style); ?>"></div>

            <div class="details-info">
                <?php 
                    do_action( 'eshb_before_details_info_html', $accomodation_id, $eshb_settings );
                ?>
                <h3 class="p-title" style="<?php echo esc_attr($title_inline_style); ?>"><?php echo esc_html(get_the_title($accomodation_id)); ?></h3>
                <div class="capacities text-center" style="background-size: cover; background-repeat: no-repeat;">
                    <?php 
                    
                        $i = 0;
                        if( ! empty( $accomodation_info_group ) && is_array($accomodation_info_group) && count($accomodation_info_group) > 0){
                            foreach ($accomodation_info_group as $key => $group) { 
                                $i++;
                                if($i >= 3) break;
                                ?>
                                <span class="capacity" style="<?php echo esc_attr( $capacities_item_inline_style ); ?>">
                                    <?php echo esc_html($group['info_title']); ?>
                                </span>
                            <?php }
                        }
                        
                    ?>
                </div>
            </div>

            <div class="hover-bg-two" style="<?php echo esc_attr($overlay_two_inline_style); ?>"></div>
        </div>
    </div>
</div>