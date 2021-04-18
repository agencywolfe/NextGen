<?php if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );

if ( !function_exists( 'lt_booking_step_03_shortcode' ) ) {

	function lt_booking_step_03_shortcode( $atts ) {

		$out = '';
		$num = '03';

		$id = $atts['id'];

		$config = fw_get_db_post_option( $id );	

		if ( !empty($config['step-03-hidden']) ) {

			return false;
		}


		if ( !empty($config['services']) ) {

			$query_args = array(

				'nopaging'                  => true,
				'post_type' => 'lt-booking-service',
				'post_status' => 'publish',
				'post__in' => $config['services'],
			);

			$query = new WP_Query( $query_args );

			if ( $query->have_posts() ) {

				$out = ltb_section_start( $config, $num );

				$out .= '<div class="row row-center">';

				while ( $query->have_posts() ) {

					$query->the_post();

					$item = fw_get_db_post_option( get_The_ID() );
		
					$out .= '
						<div class="col-lg-4 col-md-6 col-sm-6">
							<div data-mh="ltb-services-item" class="ltb-service" data-price="'.esc_attr($item['price']).'" data-time="'.esc_attr($item['time']).'" data-title="'.esc_attr(get_the_title()).'" data-id="'.esc_attr(get_The_ID()).'">';

								if ( !empty($item['icon']['icon-class']) ) {

									$out .= '<div class="ltb-service-icon">
										<span class="ltx-icon '. esc_attr( $item['icon']['icon-class'] ) .'"></span>
									</div>';
								}
									else
								if ( !empty($item['icon']['url']) ){

									$out .= '<div class="ltb-service-icon img">
										<img src="'.esc_url($item['icon']['url']).'" alt="Service">
									</div>';
								}

								$out .= '<div class="ltb-descr">';
									
									$out .= '<h6 class="header">'.esc_html(get_the_title()).'</h6>';
									
									if ( !empty($item['text']) ) {

										$out .= '<p class="descr">'.wp_kses_post($item['text']).'</p>';
									}

									$out .= '<ul class="ltb-service-icons">
										<li><span class="ltx-icon fas fa-history"></span>'.esc_html($item['time']).' '.esc_html($config['minutes']).'</li>
										<li><span class="ltx-icon fas fa-wallet"></span>'.ltb_the_price( $item['price'], $config ).'</li>
									</ul>';
								$out .='</div>
							</div>
						</div>';
				}

				$out .= '</div>';

				$out .= ltb_section_end( $config, $num );
			}
		}

		return $out;
	}
}
add_shortcode( 'lt-booking-step-03', 'lt_booking_step_03_shortcode' );


