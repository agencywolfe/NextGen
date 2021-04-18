<?php if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );

if ( !function_exists( 'lt_booking_step_05_shortcode' ) ) {

	function lt_booking_step_05_shortcode( $atts ) {

		$out = '';
		$num = '05';

		$id = $atts['id'];

		$config = fw_get_db_post_option( $id );		

		$out = ltb_section_start( $config, $num );

		$out .= '<div class="row row-centered">';

			$out .= '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">';

				$out .= ltb_the_total_grid($config);

			$out .= '</div>';

			$out .= '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">';

				$out .= ltb_the_form($config, $id);

			$out .= '</div>';

		$out .= '</div>';

		$out .= ltb_section_end( $config, $num );

		return $out;
	}
}
add_shortcode( 'lt-booking-step-05', 'lt_booking_step_05_shortcode' );


