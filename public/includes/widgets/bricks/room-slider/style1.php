<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="swiper-slide wow fadeInUp animated">
	<div class="grid-item " data-wow-delay="<?php echo esc_attr( $animation_delay )?>s">
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
					<div class="label"><?php echo esc_html( $pricing_prefix ); ?></div>
					<h3 class="price"><?php echo wp_kses_post($price); ?><div class="label"> / <?php echo esc_html( eshb_get_translated_string($string_night) );?></div></h3>
				<?php 
					} 
				?>
				<a class="details-btn" href="<?php echo esc_url( $booking_url ); ?>"><?php echo esc_html( $btn_text ); ?></a>
			</div>

			<div class="hover-bg-one"></div>

			<div class="details-info">
				<?php
					do_action( 'eshb_before_details_info_html', $accomodation_id, $eshb_settings );
				?>

				<h3 class="title"><?php echo esc_html(get_the_title($accomodation_id)); ?></h3>
				<div class="capacities text-center">
					<?php 
						$i = 0;
						if( ! empty( $accomodation_info_group ) && is_array($accomodation_info_group) && count($accomodation_info_group) > 0){
							foreach ($accomodation_info_group as $key => $group) { 
								$i++;
								if($i >= 3) break;
								?>
								<span class="capacity">
									<?php echo esc_html($group['info_title']); ?>
								</span>
							<?php }
						}
						
					?>
					
				</div>
			</div>

			<div class="hover-bg-two"></div>
		</div>
	</div>
	<?php 
		if(isset($settings['show_excerpt']) && $settings['show_excerpt'] == 'yes'){ ?>
			<div class="desc"><?php echo esc_html($hotel_view->eshb_custom_excerpt($excerpt_length));?></div>
		<?php 
		}
		
		if(isset($settings['show_all_features_icons']) && $settings['show_all_features_icons'] == 'yes'){ ?>
				<div class="all-features text-center">
					<?php 
						foreach ($accomodation_info_group as $key => $group) { 
							?>
							<span class="feature">
								<i class="icon <?php echo esc_attr($group['info_icon']); ?>"></i>
								<?php echo esc_html($group['info_title']); ?>
							</span>
						<?php }
					?>
				</div>
		<?php }
	?>
</div>
		