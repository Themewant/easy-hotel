<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	$eshb_i++; 
	$eshb_class = ($eshb_i %2 == 0) ? 'left-half' : 'right-half';
	
	if(has_post_thumbnail($eshb_accomodation_id)) {
		$eshb_thumbnail_url = get_the_post_thumbnail_url( $eshb_accomodation_id, 'full');
	} else {
		$eshb_thumbnail_url = ESHB_DIR_URL . 'public/assets/img/placeholder.png';
	}
				
	?>
	<div class="grid-item container-fluid position-relative half-fluid" style="background-size: cover; background-repeat: no-repeat; <?php echo esc_attr($eshb_item_inline_style); ?>">
		<div class="container" style="background-size: cover; background-repeat: no-repeat;">
		<div class="row" style="background-size: cover; background-repeat: no-repeat;">
			<!-- Image -->
			<div class="thumbnail-col col-lg-6 col-sm-12 col-12 position-lg-absolute <?php echo esc_attr( $eshb_class )?> h-100" style="background-size: cover; background-repeat: no-repeat;">
				<div class="image bgcustom" data-bgimage="url(<?php echo esc_url($eshb_thumbnail_url) ?>) center" style="background: url(<?php echo esc_url($eshb_thumbnail_url) ?>) center center / cover no-repeat;"></div>
			</div>
			<!-- Text -->
			<div class="contents-col col-lg-6 col-sm-12 col-12 py-5 pe-lg-5" style="background-size: cover; background-repeat: no-repeat;">
			
				<h3 class="p-title" style="<?php echo esc_attr($eshb_title_inline_style); ?>"><a href="<?php echo esc_url(get_the_permalink($eshb_accomodation_id)); ?>" style="color: inherit;"><?php echo esc_html(get_the_title($eshb_accomodation_id)); ?></a></h3>
				<div class="capacities fs-14 mb-3" style="background-size: cover; background-repeat: no-repeat;">
					<?php 
						if ( ! empty( $eshb_accomodation_info_group ) && is_array($eshb_accomodation_info_group) && count($eshb_accomodation_info_group) > 0) {
							$eshb_x = 0;
							foreach ( $eshb_accomodation_info_group as $eshb_group ) { 
								$eshb_x++;
								if($eshb_x >= 3) break;
								?>
								<span class="capacity me-4">
									<?php echo esc_html($eshb_group['info_title']); ?>
								</span>
							<?php }
						}
						do_action( 'eshb_after_capacities_info_html', $eshb_accomodation_id, $eshb_settings );
					?>
				</div>
				<p class="desc pe-lg-5" style="<?php echo esc_attr($eshb_desc_inline_style); ?>"><?php echo esc_html($eshb_excerpt) ?></p>
				<a class="details-btn rts-btn btn-main mt-2" href="<?php echo esc_url( $eshb_booking_url ); ?>" style="<?php echo esc_attr($eshb_button_inline_style); ?>"><?php echo esc_html( $eshb_btn_text ); ?></a>
			
			</div>
		</div>
		</div>
	</div>
	
		