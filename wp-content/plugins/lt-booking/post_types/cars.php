<?php if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );
/*
	Cars
*/ 
$labels = array(
	'name'               => esc_html__( 'Cars', 'lt-booking' ),
	'singular_name'      => esc_html__( 'Car', 'lt-booking' ),
	'menu_name'          => esc_html__( 'Washing Cars', 'lt-booking' ),
	'name_admin_bar'     => esc_html__( 'Car', 'lt-booking' ),
	'add_new'            => esc_html__( 'Add New', 'lt-booking' ),
	'add_new_item'       => esc_html__( 'Add New Car', 'lt-booking' ),
	'new_item'           => esc_html__( 'New Car', 'lt-booking' ),
	'edit_item'          => esc_html__( 'Edit Car', 'lt-booking' ),
	'view_item'          => esc_html__( 'View Car', 'lt-booking' ),
	'all_items'          => esc_html__( 'Cars', 'lt-booking' ),
	'search_items'       => esc_html__( 'Search Cars', 'lt-booking' ),
	'parent_item_colon'  => esc_html__( 'Parent Car:', 'lt-booking' ),
	'not_found'          => esc_html__( 'No Cars found.', 'lt-booking' ),
	'not_found_in_trash' => esc_html__( 'No Cars found in Trash.', 'lt-booking' )
);

$args = array(
	'labels'             => $labels,
	'public'             => false,
	'publicly_queryable' => false,
	'show_ui'            => true,
	'show_in_menu'       => 'edit.php?post_type=lt-booking',
	'query_var'          => false,
	'rewrite'            => false,
	'capability_type'    => 'post',
	'has_archive'        => false,
	'hierarchical'       => false,
	'menu_position'      => 3,
	'supports'           => array( 'title')
);

register_post_type( 'lt-booking-car', $args );


add_filter(
	'fw_post_options:lt-booking-car', function() {

		$ltb_tariffs = ltbGetPosts('lt-booking-tariff');

		return array(

			'parent' => array(
				'title'   => '',
				'type'    => 'box',
				'options' => array(
					'image' => array(
						"label" => esc_html__("Image", 'lt-booking'),
						"type" => "upload"
					),
					'tariffs' => array(
					    'type'  => 'select-multiple',
					    'label' => esc_html__('Tariffs', 'lt-booking'),
					    'choices' => $ltb_tariffs,
					),
				),
			),
		);
	},
	100
);

