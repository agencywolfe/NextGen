<?php if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );
/**
 * Config
 */

$ltb_cfg = array(

	'path'	=> plugin_dir_path(__DIR__),
	'base' 	=> plugin_basename(__DIR__),
	'url'	=> plugin_dir_url(__FILE__),
);

if ( !function_exists('ltb_load_plugin_textdomain')) {

	function ltb_load_plugin_textdomain() {

		load_plugin_textdomain( 'lt-booking', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}
}
add_action( 'plugins_loaded', 'ltb_load_plugin_textdomain' );

