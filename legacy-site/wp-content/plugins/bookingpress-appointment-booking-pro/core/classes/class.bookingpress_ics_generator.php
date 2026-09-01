<?php
/**
 * BookingPress ICS Generator Class
 * @since v1.2.1
 */
if ( ! class_exists( 'BookingPressPro_ICS' ) ) {
    
    /**
     * BookingPressPro_ICS
     */
    class BookingPressPro_ICS Extends BookingPress_Core{
        
        function __construct(){
            add_filter( 'bpa_add_timezone_parameters_for_ics_without_timezone', array( $this, 'bpa_generate_ics_without_timezone'), 10, 2 );
            add_filter( 'bpa_add_timezone_parameters_for_ics', array( $this, 'bpa_generate_ics_with_timzone'), 10, 2 );       
        }

        function bpa_generate_ics_without_timezone( $string, $appointment_data ){

            $string = $this->bpa_generate_ics_with_timzone( $string, $appointment_data, true );

            return $string;
        }
                
        /**
         * Function for generate ICS file with timezone details
         *
         * @param  mixed $string
         * @param  mixed $appointment_data
         * @param  mixed $skip_timezone
         * @return void
         */
        function bpa_generate_ics_with_timzone( $string, $appointment_data, $skip_timezone = false ){

            if( empty( $appointment_data ) ){
                return $string;
            }
            
            global $BookingPress, $bookingpress_appointment_bookings;

            $bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

            $bookingpress_timezone = $appointment_data['bookingpress_appointment_timezone'];

            $booking_timeslot_start = $appointment_data['bookingpress_appointment_date'] .' ' .$appointment_data['bookingpress_appointment_time'];
            $booking_timeslot_end = $appointment_data['bookingpress_appointment_date'] . ' ' . $appointment_data['bookingpress_appointment_end_time'];

            $booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
            $booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);

            $bookingpress_service_name = ! empty( $appointment_data['bookingpress_service_name'] ) ? sanitize_text_field( $appointment_data['bookingpress_service_name'] ) : '';

            $is_dst_timezone = !empty( $appointment_data['bookingpress_dst_timezone'] ) ? $appointment_data['bookingpress_dst_timezone'] : 0;

            if( !empty($bookingpress_timeslot_display_in_client_timezone) && ($bookingpress_timeslot_display_in_client_timezone == 'true') && false == $skip_timezone && $is_dst_timezone > 0 ){

                $client_timezone_offset = -1 * ( $bookingpress_timezone / 60 );
				$offset_minute = fmod( $client_timezone_offset, 1);
            
                $offset_minute = abs( $offset_minute );
            

				$hours = $client_timezone_offset - $offset_minute;
				
				$offset_minute = $offset_minute * 60;
				if( $hours < 0 ){

				} else {
					if( strlen( $hours ) === 1 ){
						$hours = '+0' . $hours;
					} else {
						$hours = '+' . $hours;
					}
				}

				if( strlen( $offset_minute ) == 1 ){
					$offset_minute = '0' . $offset_minute;
				}

				$timezone_offset = $hours.':' . $offset_minute;

                $client_timezone_string = !empty( $_COOKIE['bookingpress_client_timezone'] ) ? sanitize_text_field($_COOKIE['bookingpress_client_timezone']) : '';
                if( '' == $client_timezone_string ){
                    return $string;
                }

                $timezone_data = new DateTimeZone($client_timezone_string);
                $timezone_dtls = $timezone_data->getTransitions();
                
                if( empty( $timezone_dtls ) ){
                    return $string;
                }

                if( count( $timezone_dtls ) < 2 ){
                    return $string;
                }
                
                $timezone_current = array();
                $timezone_next = array();
                foreach( $timezone_dtls as $k => $timezone_detail ){
                    if( strtotime( $booking_timeslot_start ) < $timezone_detail['ts'] ){
                        if( empty( $timezone_next ) ){
                            $timezone_next[] = $timezone_detail;
                            $timezone_current[] = $timezone_dtls[ $k - 1 ];
                            break;
                        }
                    }
                }
                $timezone_dtls = array_merge( $timezone_current, $timezone_next );
                
                $curr_timezone_data = $timezone_dtls[0];
                $next_timezone_data = $timezone_dtls[1];

                $new_string  = "BEGIN:VCALENDAR\r\n";
                $new_string .= "VERSION:2.0\r\n";
                $new_string .= 'PRODID:BOOKINGPRESS APPOINTMENT BOOKING\\\\' . get_bloginfo('title') . "\r\n";
                $new_string .= "X-PUBLISHED-TTL:P1W\r\n";
                $new_string .= "BEGIN:VTIMEZONE\r\n";
                $new_string .= "TZID:" . $client_timezone_string . "\r\n";
                if( $curr_timezone_data['isdst'] ){
                    $new_string .= "BEGIN:DAYLIGHT\r\n";
                    $new_string .= "TZNAME:" . $curr_timezone_data['abbr'] . "\r\n";
                    $offsetfrom = $next_timezone_data['offset'] / ( 60 * 60 );
                    
                    if( $offsetfrom < 0 ){
                        if( $offsetfrom > -10 ){
                            $offsetfrom = '-0' . abs( $offsetfrom ) . '00';
                        } else {
                            $offsetfrom = $offsetfrom.'00';
                        }
                    } else {
                        if( $offsetfrom < 10 ){
                            $offsetfrom = '+0' . $offsetfrom . "00";
                        } else {
                            $offsetfrom = "+" . $offsetfrom . "00";
                        }
                    }

                    $offsetto = $curr_timezone_data['offset'] / ( 60 * 60 );
                    if( $offsetto < 0 ){
                        if( $offsetto > -10 ){
                            $offsetto = '-0' . abs( $offsetto ) . "00";
                        } else {
                            $offsetto = $offsetto . "00";
                        }
                    } else {
                        if( $offsetto < 10 ){
                            $offsetto = '+0' . $offsetto . "00";
                        } else {
                            $offsetto = "+" . $offsetto . "00";
                        }
                    }
                    $new_string .= "TZOFFSETFROM:" . $offsetfrom . "\r\n";
                    $new_string .= "TZOFFSETTO:" . $offsetto . "\r\n";
                    $new_string .= "DTSTART:" . date('Ymd', strtotime( $curr_timezone_data['time'] ) ) . 'T' . date('His', strtotime( $curr_timezone_data['time'] ) ) . "\r\n";
                    $new_string .= "END:DAYLIGHT\r\n";
                    $new_string .= "BEGIN:STANDARD\r\n";
                    $new_string .= "TZNAME:" . $next_timezone_data['abbr'] . "\r\n";
                    $new_string .= "TZOFFSETFROM:" . $offsetto . "\r\n";
                    $new_string .= "TZOFFSETTO:" . $offsetfrom . "\r\n";
                    $new_string .= "DTSTART:" . date('Ymd', strtotime( $next_timezone_data['time'] ) ) . 'T' . date('His', strtotime( $next_timezone_data['time'] ) ) . "\r\n";
                    $new_string .= "END:STANDARD\r\n";
                }

                $new_string .= "END:VTIMEZONE\r\n";

                $new_string .= "BEGIN:VEVENT\r\n";
                $new_string .= "DTSTAMP:". date( 'Ymd', current_time('timestamp') ) .'T' . date('His', strtotime( $booking_timeslot_start ) )."\r\n";
                $new_string .= 'UID:' . md5( current_time('timestamp') ) . "\r\n";
                $new_string .= "SEQUENCE:0\r\n";
                $new_string .= "TRANSP:OPAQUE\r\n";
                if( 'd' == $appointment_data['bookingpress_service_duration_unit'] ){
                    $duration_val = $appointment_data['bookingpress_service_duration_val'];
                    $new_string .= "DTSTART;VALUE=DATE:".date('Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . "\r\n";
                    $new_string .= "DTEND;VALUE=DATE:". date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] . '+' . $duration_val . ' days' )) . "\r\n";
                    
                } else {
                    $new_string .= "DTSTART;TZID=".$client_timezone_string.':'.date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) .'T' . date('His', strtotime( $booking_timeslot_start ) )."\r\n";
                    $new_string .= "DTEND;TZID=".$client_timezone_string.':'.date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) .'T' . date('His', strtotime( $booking_timeslot_end ) )."\r\n";
                }
                $new_string .= "SUMMARY:{$bookingpress_service_name}\r\n";
                $new_string .= "END:VEVENT\r\n";
                $new_string .= "END:VCALENDAR\r\n";
                
                return $new_string;
            } else {
                
                $current_dtime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( date( 'm/d/Y' ), 'g:i A' );

                if( 'd' == $appointment_data['bookingpress_service_duration_unit'] ){
                    $service_duration = $appointment_data['bookingpress_service_duration_val'];
                    
                    $string  = "BEGIN:VCALENDAR\r\n";
					$string .= "VERSION:2.0\r\n";
					$string .= 'PRODID:BOOKINGPRESS APPOINTMENT BOOKING\\\\' . get_bloginfo('title') . "\r\n";
					$string .= "X-PUBLISHED-TTL:P1W\r\n";
					$string .= "BEGIN:VEVENT\r\n";
					$string .= 'UID:' . md5( time() ) . "\r\n";
					$string .= "DTSTART;VALUE=DATE:". date('Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . "\r\n";
                    $string .= "DTEND;VALUE=DATE:" . date('Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] . '+' . $service_duration.' days' ) ) . "\r\n";
                    $string .= "SUMMARY:{$bookingpress_service_name}\r\n";
                    $string .= "CLASS:PUBLIC\r\n";
					$string .= "DTSTAMP:{$current_dtime}\r\n";
					$string .= "END:VEVENT\r\n";
					$string .= "END:VCALENDAR\r\n";

                    return $string;
                }
            }

            return $string;
        }

    }

    global $bookingpress_pro_ics;
    $bookingpress_pro_ics = new BookingPressPro_ICS();
}