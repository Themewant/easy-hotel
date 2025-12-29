<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	$i++; 
	$class = ($i %2 == 0) ? 'left-half' : 'right-half';
	
	if(has_post_thumbnail($accomodation_id)) {
		$thumbnail_url = get_the_post_thumbnail_url( $accomodation_id, 'full');
	} else {
		$thumbnail_url = ESHB_DIR_URL . 'public/assets/img/placeholder.png';
	}
				
	?>
	<div class="swiper-slide">
		<div class="grid-item container-fluid position-relative half-fluid" style="background-size: cover; background-repeat: no-repeat; <?php echo esc_attr($item_inline_style); ?>">
		<div class="container" style="background-size: cover; background-repeat: no-repeat;">
		<div class="row" style="background-size: cover; background-repeat: no-repeat;">
			<!-- Image -->
			<div class="thumbnail-col col-lg-6 col-sm-12 col-12 position-lg-absolute <?php echo esc_attr( $class )?> h-100" style="background-size: cover; background-repeat: no-repeat;">
				<div class="image bgcustom" data-bgimage="url(<?php echo esc_url($thumbnail_url) ?>) center" style="background: url(<?php echo esc_url($thumbnail_url) ?>) center center / cover no-repeat;"></div>
			</div>
			<!-- Text -->
			<div class="contents-col col-lg-6 col-sm-12 col-12 py-5 pe-lg-5" style="background-size: cover; background-repeat: no-repeat;">
			
				<h3 class="p-title" style="<?php echo esc_attr($title_inline_style); ?>"><a href="<?php echo esc_url(get_the_permalink($accomodation_id)); ?>" style="color: inherit;"><?php echo esc_html(get_the_title($accomodation_id)); ?></a></h3>
				<div class="capacities fs-14 mb-3" style="background-size: cover; background-repeat: no-repeat;">
					<?php 
						if ( ! empty( $accomodation_info_group ) && is_array($accomodation_info_group) && count($accomodation_info_group) > 0) {
							$x = 0;
							foreach ( $accomodation_info_group as $group ) { 
								$x++;
								if($x >= 3) break;
								?>
								<span class="capacity me-4">
									<?php echo esc_html($group['info_title']); ?>
								</span>
							<?php }
						}
						do_action( 'eshb_after_capacities_info_html', $accomodation_id, $eshb_settings );
					?>
				</div>
				<p class="desc pe-lg-5" style="<?php echo esc_attr($desc_inline_style); ?>"><?php echo esc_html($excerpt) ?></p>
				<a class="details-btn rts-btn btn-main mt-2" href="<?php echo esc_url( $booking_url ); ?>" style="<?php echo esc_attr($button_inline_style); ?>"><?php echo esc_html( $btn_text ); ?></a>
			
			</div>
		</div>
		</div>
		</div>
	</div>
	
		