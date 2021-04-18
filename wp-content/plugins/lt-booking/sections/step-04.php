<?php if ( ! defined( 'ABSPATH' ) ) die( 'Forbidden' );

if ( !function_exists( 'lt_booking_step_04_shortcode' ) ) {

	function lt_booking_step_04_shortcode( $atts ) {

		$out = '';
		$num = '04';

		$id = $atts['id'];

		$config = fw_get_db_post_option( $id );			

		$out = ltb_section_start( $config, $num );

		$calendar = array();
		$calendar['dates'] = array();

		$date = new DateTime(date("Y-m-d"));

		// Generating table headers
		for ( $x = 0; $x <= 6; $x++ ) {

			$calendar['dates'][$x] = $date->format('d');
			$calendar['dates_headers'][$x] = date_i18n('l', $date->getTimestamp());			
			$calendar['week_num'][$x] = $date->format('N');
			$calendar['week_date'][$date->format('N')] = $date->format('Y-m-d');
			$calendar['week_date_reverse'][$date->format('Y-m-d')] = ($date->format('N') - 1);
			$calendar['dates_stamp'][$date->format('N')] = $date->format('Y-m-d');
			$calendar['dates_total'][$date->format('N')] = $date->format('d').' '.date_i18n('F', $date->getTimestamp()).' '.$date->format('Y');
			$date->modify('+1 day');
		}

		$calendar['week_date_end'] = $date->format('Y-m-d');
		$date->modify('-8 day');
		$calendar['week_date_start'] = $date->format('Y-m-d');

		// Looking for a earliest time
		$time = 2400;
		for ( $d = 0; $d <= 6; $d++ ) {

			$from = (int)(str_replace(':', '', $config['working-time-'.($d + 1)]['from']));
			if ( ($config['working-time-'.($d + 1)]['from'] == '00:00' OR $from > 0) AND $from < $time ) {

				$time = $from;
				$earliest = $d;
			}
		}

		// Generating all time periods array
		$today = date('Y-m-d');
		for ( $d = 0; $d <= 6; $d++ ) {

			$from = $config['working-time-'.($d + 1)]['from'];
			$to = $config['working-time-'.($d + 1)]['to'];

			$end_date = new DateTime($today . ' ' . esc_attr($to));
			$end_date->modify('+1 hour'); 
			$end_date = $end_date->format('Y-m-d H:i'); 

			if ( !empty($from) AND !empty($to) ) {

				$period = new DatePeriod(
					new DateTime($today . ' ' . esc_attr($config['working-time-'.($earliest + 1)]['from'])),
					new DateInterval('PT60M'),
					new DateTime($end_date)
				);

				foreach ($period as $key => $value) {

					$calendar['time'][$d][$value->format('Hi')] = array(

						'disabled'		=>	true,
						'datetime'		=>	$calendar['week_date'][($d + 1)].' '.$value->format('H:i'),
						'time'			=>	$value->format('H:i')
					);
				}

				// Activating working hours for each day
				$period = new DatePeriod(
					new DateTime($today . ' ' . esc_attr($from)),
					new DateInterval('PT60M'),
					new DateTime($end_date)
				);		

				foreach ($period as $key => $value) {

					$calendar['time'][$d][$value->format('Hi')]['disabled'] = false;				
				}						
			}

			// Exluding break time
			$from = $config['break-time-'.($d + 1)]['from'];
			$to = $config['break-time-'.($d + 1)]['to'];			
			if ( !empty($from) AND !empty($to) ) {

				$period = new DatePeriod(
					new DateTime($today . ' ' . esc_attr($from)),
					new DateInterval('PT60M'),
					new DateTime($today . ' ' . esc_attr($to))
				);		

				foreach ($period as $key => $value) {

					if ( isset($calendar['time'][$d][$value->format('Hi')]) ) {
					
						$calendar['time'][$d][$value->format('Hi')]['disabled'] = true;
					}
				}					
			}
		}

		// Excluding already booked periods
		$calendar['booked'] = array();

		if ( !empty($config['booking-slots']) ) {

			$query_args = array(
			  'post_type' => 'lt-booking', 
			  'date_query' => array(
			    'column' => 'post_date',
			    'after' => $calendar['week_date_start'],
			    'before' => $calendar['week_date_end'] 
			  ),
			  'post_status' => 'publish'
			);

			$query = new WP_Query( $query_args );
			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) {

					$query->the_post();

					if ( empty($calendar['booked'][date('Y-m-d H:i', get_post_time())]) ) {

						$calendar['booked'][date('Y-m-d H:i', get_post_time())] = 0;
					}

					$calendar['booked'][date('Y-m-d H:i', get_post_time())]++;

					if ( $calendar['booked'][date('Y-m-d H:i', get_post_time())] >= $config['booking-slots'] AND 
						 isset($calendar['time'][$calendar['week_date_reverse'][date('Y-m-d', get_post_time())]][date('Hi', get_post_time())]) ) {

						$calendar['time'][$calendar['week_date_reverse'][date('Y-m-d', get_post_time())]][date('Hi', get_post_time())]['disabled'] = true;
					}						
				}
			}
		}

		// Excluding today's passed time
		$today_day = date('N') - 1;
		$period = new DatePeriod(

			new DateTime($today),
			new DateInterval('PT60M'),
			new DateTime($today . ' ' . date_i18n('H:i'))
		);	

		foreach ($period as $key => $value) {

			if ( isset($calendar['time'][$today_day][$value->format('Hi')]) ) {

				$calendar['time'][$today_day][$value->format('Hi')]['disabled'] = true;
			}
		}

		// Excluding global break periods
		if ( !empty($config['break_periods']) ) {

			foreach ($config['break_periods'] as $breakInfo) {

				if ( !empty($breakInfo['breakFrom']) AND !empty($breakInfo['breakTo']) ) {

					$period = new DatePeriod(
						new DateTime(date_i18n('Y-m-d H:i', strtotime($breakInfo['breakFrom']))),
						new DateInterval('PT60M'),
						new DateTime(date_i18n('Y-m-d H:i', strtotime($breakInfo['breakTo'])))
					);

					foreach ($period as $key => $value) {

						foreach ( $calendar['time'] as $d => $days ) {

							foreach ( $days as $t => $times ) {

								if ( $times['datetime'] == $value->format('Y-m-d H:i') ) {

									$calendar['time'][$d][$t]['disabled'] = true;
								}
							}
						}
					}						
				}
			}
		}


		$out .= '<div class="ltb-calendar-nav"><a href="#" class="ltb-calendar-left"></a>'.date_i18n('F Y').'<a href="#" class="ltb-calendar-right"></a></div>';
		$out .= '<table class="ltb-calendar" cellspacing="0" cellpadding="0">';
			$out .= '<thead>';
				$out .= '<tr>';

				foreach ( $calendar['dates'] as $d => $day ) {

					$th_class = 'ltb-day';
					if ( $d == 0 ) {

						$th_class .= ' ltb-day-current';
					}

					if ( !empty($config['weekend']) AND $config['weekend'] == $calendar['week_num'][$d] ) {

						$th_class .= ' ltb-weekend';
					}

					$out .= '<th class="'.esc_attr($th_class).'"><span>';
						$out .= esc_html($day);
						$out .= '<span class="ltb-day">'.esc_html($calendar['dates_headers'][$d]).'</span>';
					$out .= '</span></th>';
				}

				$out .= '</tr>';
			$out .= '</thead>';

			$out .= '<tbody>';
			
				$out .= '<tr>';
				foreach ( $calendar['week_num'] as $d ) {

					if ( !empty($calendar['time'][($d - 1)]) ) {

						$out .= '<td>';

						foreach ( $calendar['time'][($d - 1)] as $time ) {

							$class = '';
							if ( !empty($time['disabled']) ) $class = ' disabled';

							$out .= '<span class="ltb-time'.esc_attr($class).'" data-time="'.esc_attr($time['time']).'" data-stamp="'.esc_attr(strtotime($calendar['dates_stamp'][$d].' '.$time['time'])).'" data-date="'.esc_attr($calendar['dates_total'][$d]).'">'.esc_html($time['time']).'</span>';
						}

						$out .= '</td>';
					}
						else {

						$out .= '<td class="ltb-closed">'.esc_html($config['closed-header']).'</td>';
					}
				}

				$out .= '</tr>';

			$out .= '</tbody>';

		$out .= '</table>';

		$out .= '<div class="ltb-calendar-descr">'.wp_kses_post(ltb_header_parse($config['calendar-descr'])).'</div>';

		$out .= ltb_section_end( $config, $num );

		return $out;
	}
}
add_shortcode( 'lt-booking-step-04', 'lt_booking_step_04_shortcode' );


