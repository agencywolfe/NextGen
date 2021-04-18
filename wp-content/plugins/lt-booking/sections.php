<?php if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );

/**
 * Shortcode output
 */

require_once LTB_PLUGIN_DIR . 'sections/step-01.php';
require_once LTB_PLUGIN_DIR . 'sections/step-02.php';
require_once LTB_PLUGIN_DIR . 'sections/step-03.php';
require_once LTB_PLUGIN_DIR . 'sections/step-04.php';
require_once LTB_PLUGIN_DIR . 'sections/step-05.php';

if ( !function_exists( 'lt_booking_shortcode' ) ) {

	function lt_booking_shortcode() {

		do_shortcode('[lt-booking-step-01]');
		echo do_shortcode('[lt-booking-step-02]');
		echo do_shortcode('[lt-booking-step-03]');
		echo do_shortcode('[lt-booking-step-04]');
		echo do_shortcode('[lt-booking-step-05]');
	}
}
add_shortcode( 'lt-booking', 'lt_booking_shortcode' );


