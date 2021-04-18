<?php if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );

if ( !function_exists( 'lt_booking_step_02_shortcode' ) ) {

	function lt_booking_step_02_shortcode( $atts ) {

		$out = '';
		$num = '02';

		$id = $atts['id'];

		$config = fw_get_db_post_option( $id );	

		$info = ltbGetTarrifsIds( $config );

		$out = ltb_section_start( $config, $num );

		if ( !empty($info['ids']) ) {

			$query_args = array(

				'nopaging'                  => true,
				'post_type' => 'lt-booking-tariff',
				'post_status' => 'publish',
				'orderby' => 'publish_date',
				'order' => 'DESC',
				'post__in' => $info['ids'],
				'post_per_page' => 100,
			);

			$query = new WP_Query( $query_args );

			if ( $query->have_posts() ) {

				$out .= '<div class="row row-center">';

				while ( $query->have_posts() ) {

					$query->the_post();

					$btn_class = ' btn-main color-hover-black';

					$item = fw_get_db_post_option( get_The_ID() );

					$list = explode("\n", wp_kses_post(ltb_header_parse($item['text'])));
					$class = '';

					if ( !empty($item['vip']) ) { 

						$class = ' vip';
						$btn_class = ' btn-main color-hover-white';
					}

					$bg = '';
					if ( !empty($item['vip-bg']) ) {

						$bg = ' style="background-image: url('.esc_url($item['vip-bg']['url']).')"';
					}

					$out .= '
						<div class="col-lg-3 col-md-6 col-ms-12 col-xs-12 ltb-tariff-wrapper ltb-tariff-id-'.esc_attr(get_The_ID()).'">
							<div data-mh="ltx-tariff-item" class="tariff-item item layout-default'.$class.'"'.$bg.'>
								<div class="ltx-header-wrapper">
									<h5 class="header">'.esc_html(get_the_title()).'</h5>
									<div class="price">'.ltb_the_price_full($item['price'], $config).'</div>
								</div>';
								
								$out .= '<ul class="ltx-tariff-list"><li>'. implode('</li><li>', $list) .'</li></ul>';

								$out .= '<ul class="ltx-tariff-icons">
									<li><span class="ltx-icon far fa-clock"></span>'.esc_html($item['time']).' min</li>
								</ul>
								<div>
									<a href="#" class="btn btn-lg'.esc_attr($btn_class).'" 
										data-id="'.esc_attr(get_The_ID()).'" 
										data-title="'.esc_attr(get_the_title()).'" 
										data-time="'.esc_attr($item['time']).'" 
										data-price="'.esc_attr($item['price']).'" 
										data-selected="'.esc_html($config['tariff-selected']).'" 
										data-unselected="'.esc_html($config['tariff-unselected']).'">
											<span class="ltx-btn-inner">
												<span class="ltx-txt">'.esc_html($config['tariff-unselected']).'</span>
												<span class="ltx-btn-after"></span>
											</span>
									</a>
								</div>
							</div>
						</div>';
				}

				$out .= '</div>';
			}
		}

		$out .= ltb_section_end( $config, $num );

		return $out;
	}
}
add_shortcode( 'lt-booking-step-02', 'lt_booking_step_02_shortcode' );


