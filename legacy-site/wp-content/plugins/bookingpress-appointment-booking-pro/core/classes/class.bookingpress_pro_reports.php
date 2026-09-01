<?php
if ( ! class_exists( 'bookingpress_pro_reports' ) ) {
	class bookingpress_pro_reports Extends BookingPress_Core {
		function __construct() {

			add_action( 'bookingpress_reports_dynamic_view_load', array( $this, 'bookingpress_load_reports_view_func' ) );
			add_action( 'bookingpress_reports_dynamic_vue_methods', array( $this, 'bookingpress_reports_vue_methods_func' ) );
			add_action( 'bookingpress_reports_dynamic_on_load_methods', array( $this, 'bookingpress_reports_on_load_methods_func' ),10 );
			add_action( 'bookingpress_reports_dynamic_data_fields', array( $this, 'bookingpress_reports_dynamic_data_fields_func' ) );
			add_action( 'bookingpress_reports_dynamic_helper_vars', array( $this, 'bookingpress_reports_dynamic_helper_vars_func' ) );

			add_action('wp_ajax_bookingpress_get_appointment_report_charts_data', array($this, 'bookingpress_get_appointment_report_charts_data_func'));
			add_action('wp_ajax_bookingpress_get_appointment_report_data', array( $this, 'bookingpress_get_appointment_report_func'));

			add_action('wp_ajax_bookingpress_get_revenue_report_charts_data', array($this, 'bookingpress_get_revenue_report_charts_data_func'));
			add_action('wp_ajax_bookingpress_get_revenue_report_data', array($this, 'bookingpress_get_revenue_report_data_func'));

			add_action('wp_ajax_bookingpress_get_customer_report_charts_data', array($this, 'bookingpress_get_customer_report_charts_data_func'));
			add_action('wp_ajax_bookingpress_get_customer_report_data', array($this, 'bookingpress_get_customer_report_data_func'));
		}

		function bookingpress_get_customer_report_charts_data_func(){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_customers;
			$response = array();
			
			$bpa_check_authorization = $this->bpa_check_authentication( 'get_customer_report_chart_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			
			$custom_filter_val   = isset( $_POST['custom_filter_val'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), (array) $_POST['custom_filter_val'] ) : array(); // phpcs:ignore

			$return_data               = array();
			$search_filter_dates       = array();
			$customer_search_query = '1=1';
			$payments_group_by     = 'bookingpress_payment_date_time';

			$customer_filter_start_val  = ! empty( $custom_filter_val[0] ) ? sanitize_text_field( $custom_filter_val[0] ) : date( 'Y-m-d' );
			$customer_filter_end_val  = ! empty( $custom_filter_val[1] ) ? sanitize_text_field( $custom_filter_val[1] ) : date( 'Y-m-d' );
			$customer_filter_start_val = date('Y-m-d', strtotime($customer_filter_start_val));
			$customer_filter_end_val = date('Y-m-d', strtotime($customer_filter_end_val));
			
			$selected_filter_val = "";
			$bookingpress_current_date = date('Y-m-d', current_time('timestamp'));
			if(!empty($customer_filter_start_val) && !empty($customer_filter_end_val) && $customer_filter_start_val == $customer_filter_end_val ){
				$bookingpress_current_datetime_obj = new DateTime($bookingpress_current_date);
                $bookingpress_match_datetimt_obj = new DateTime($customer_filter_start_val);
                $bookingpress_dates_interval = $bookingpress_current_datetime_obj->diff($bookingpress_match_datetimt_obj);
                if($bookingpress_dates_interval->days == 1 && $bookingpress_dates_interval->invert == 1){ //Yesterday condition
                    $customer_filter_start_val  = $customer_filter_end_val = date('Y-m-d', strtotime('-1 days', current_time('timestamp')));                
                    $start_time = strtotime('today');
                    $end_time   = strtotime('tomorrow', $start_time) - 1;

                    while ( $start_time <= $end_time ) {
                        array_push($search_filter_dates, date('H:i:s', $start_time));
                        $start_time = strtotime('+1 hour', $start_time);
                    }

                    $selected_filter_val = "yesterday";
                }else if($bookingpress_dates_interval->days == 1 && $bookingpress_dates_interval->invert == 0){ //Tomorrow condition
                    $customer_filter_start_val = $customer_filter_end_val = date('Y-m-d', strtotime('+1 days', current_time('timestamp')));                            
                    $start_time = strtotime('today');
                    $end_time   = strtotime('tomorrow', $start_time) - 1;

                    while ( $start_time <= $end_time ) {
                        array_push($search_filter_dates, date('H:i:s', $start_time));
                        $start_time = strtotime('+1 hour', $start_time);
                    }

                    $selected_filter_val = "tomorrow";
                }else{ // Today condition
                    $customer_filter_start_val = $customer_filter_end_val = date('Y-m-d', current_time('timestamp'));                         
                    $start_time = strtotime('today');
                    $end_time   = strtotime('tomorrow', $start_time) - 1;

                    while ( $start_time <= $end_time ) {
                        array_push($search_filter_dates, date('H:i:s', $start_time));
                        $start_time = strtotime('+1 hour', $start_time);
                    }

                    $selected_filter_val = "today";
                }
			}else{
				$bookingpress_tmp_end_val = date('Y-m-d', strtotime("+1 days", strtotime($customer_filter_end_val)));
				$bookingpress_get_all_dates = new DatePeriod(
					new DateTime( $customer_filter_start_val ),
					new DateInterval( 'P1D' ),
					new DateTime( $bookingpress_tmp_end_val )
				);
				foreach ( $bookingpress_get_all_dates as $date_key => $date_val ) {
					$search_date_val = $date_val->format( 'M d' );
					array_push( $search_filter_dates, $search_date_val );
				}
			}

			$customer_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $customer_filter_start_val . "' AND '" . $customer_filter_end_val . "')";

			$bookingpress_customers_appointment_data       = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_appointment_bookings . ' WHERE ' . $customer_search_query . ' ORDER by bookingpress_customer_id DESC', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			$bookingpress_customers_arr = $bookingpress_existing_customers_arr = $bookingpress_new_customers_arr = array();
			$bookingpress_existing_customer_count = $bookingpress_new_customer_count = 0;
			foreach($bookingpress_customers_appointment_data as $customer_appointment_key => $customer_appointment_val){
				$bookingpress_customer_id = intval($customer_appointment_val['bookingpress_customer_id']);
				if(!in_array($bookingpress_customer_id, $bookingpress_customers_arr)){
					//Get customer created_at date
					$bookingpress_customer_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_user_created FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id = %d", $bookingpress_customer_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is table name.

					$bookingpress_customer_created_date = !empty($bookingpress_customer_details['bookingpress_user_created']) ? date('Y-m-d', strtotime($bookingpress_customer_details['bookingpress_user_created'])) : date('Y-m-d', current_time('timestamp'));

					if(!empty($selected_filter_val) && ($selected_filter_val == "today" || $selected_filter_val == "yesterday" || $selected_filter_val == "tomorrow")){
						$bookingpress_customer_date_key = date('H:i:s', strtotime($customer_appointment_val['bookingpress_appointment_time']));
					}else{
						$bookingpress_customer_date_key = date('M d', strtotime($customer_appointment_val['bookingpress_appointment_date']));
					}

					if($bookingpress_customer_created_date >= $customer_filter_start_val && $bookingpress_customer_created_date <= $customer_filter_end_val){
						if(array_key_exists($bookingpress_customer_date_key, $bookingpress_new_customers_arr)){
							$bookingpress_new_customers_arr[$bookingpress_customer_date_key] = $bookingpress_new_customers_arr[$bookingpress_customer_date_key] + 1;
							$bookingpress_new_customer_count++;
						}else{
							$bookingpress_new_customers_arr[$bookingpress_customer_date_key] = 1;
							$bookingpress_new_customer_count++;
						}
					}else{
						if(array_key_exists($bookingpress_customer_date_key, $bookingpress_existing_customers_arr)){
							$bookingpress_existing_customers_arr[$bookingpress_customer_date_key] = $bookingpress_existing_customers_arr[$bookingpress_customer_date_key] + 1;
							$bookingpress_existing_customer_count++;
						}else{
							$bookingpress_existing_customers_arr[$bookingpress_customer_date_key] = 1;
							$bookingpress_existing_customer_count++;
						}
					}
					
					array_push($bookingpress_customers_arr, $bookingpress_customer_id);
				}
			}

			foreach($search_filter_dates as $filter_date_key => $filter_date_val){
				if(!array_key_exists($filter_date_val, $bookingpress_existing_customers_arr)){
					$bookingpress_existing_customers_arr[$filter_date_val] = 0;
				}

				if(!array_key_exists($filter_date_val, $bookingpress_new_customers_arr)){
					$bookingpress_new_customers_arr[$filter_date_val] = 0;
				}
			}

			$return_data['chart_x_axis_vals']  = $search_filter_dates;
			$return_data['existing_customers'] = $bookingpress_existing_customers_arr;
			$return_data['new_customers'] = $bookingpress_new_customers_arr;
			$return_data['existing_customers_count'] = $bookingpress_existing_customer_count;
			$return_data['new_customers_count'] = $bookingpress_new_customer_count;					

			echo wp_json_encode( $return_data );
			exit();
		}

		function bookingpress_get_customer_report_data_func(){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $bookingpress_global_options, $tbl_bookingpress_customers;
			$response = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'get_customer_report_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			
			$custom_filter_val         = ! empty( $_POST['custom_filter_val'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), (array) $_POST['custom_filter_val'] ) : array(); // phpcs:ignore

			$perpage                   = isset( $_POST['perpage'] ) ? intval( $_POST['perpage'] ) : 10; // phpcs:ignore
			$currentpage               = isset( $_POST['currentpage'] ) ? intval( $_POST['currentpage'] ) : 1; // phpcs:ignore
			$offset                    = ( ! empty( $currentpage ) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;

			$return_data               = $search_filter_dates  = array();
			$customer_search_query = '1=1';
			$bookingpress_customers_data = array();
			$bookingpress_search_data  = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function

			$customer_filter_start_val  = ! empty( $custom_filter_val[0] ) ? sanitize_text_field( $custom_filter_val[0] ) : date( 'Y-m-d' );
			$customer_filter_end_val    = ! empty( $custom_filter_val[1] ) ? sanitize_text_field( $custom_filter_val[1] ) : date( 'Y-m-d' );
			$customer_filter_start_val = date('Y-m-d', strtotime($customer_filter_start_val));
			$customer_filter_end_val = date('Y-m-d', strtotime($customer_filter_end_val));

			$bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_date_format    = $bookingpress_global_details['wp_default_date_format'] . '  ' . $bookingpress_global_details['wp_default_time_format'];
			$bookingpress_default_date_format = $bookingpress_global_details['wp_default_date_format'];
			$bookingpress_default_time_format = $bookingpress_global_details['wp_default_time_format'];

			$customer_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $customer_filter_start_val . "' AND '" . $customer_filter_end_val . "')";
			
			$bookingpress_customers_appointment_data       = $wpdb->get_results( 'SELECT bookingpress_customer_id FROM ' . $tbl_bookingpress_appointment_bookings . ' WHERE ' . $customer_search_query . ' ORDER by bookingpress_customer_id DESC', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm			


			$bookingpress_customers_arr = $bookingpress_existing_customers_arr = $bookingpress_new_customers_arr = array();
			foreach($bookingpress_customers_appointment_data as $customer_appointment_key => $customer_appointment_val){
				$bookingpress_customer_id = intval($customer_appointment_val['bookingpress_customer_id']);
				if(!in_array($bookingpress_customer_id, $bookingpress_customers_arr)){
					//Get customer created_at date
					$bookingpress_customer_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_user_created FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id = %d", $bookingpress_customer_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is table name.

					$bookingpress_customer_created_date = !empty($bookingpress_customer_details['bookingpress_user_created']) ? date('Y-m-d', strtotime($bookingpress_customer_details['bookingpress_user_created'])) : date('Y-m-d', current_time('timestamp'));

					if($bookingpress_customer_created_date >= $customer_filter_start_val && $bookingpress_customer_created_date <= $customer_filter_end_val){
						array_push($bookingpress_new_customers_arr, $bookingpress_customer_id);
					}else{
						array_push($bookingpress_existing_customers_arr, $bookingpress_customer_id);
					}
					
					array_push($bookingpress_customers_arr, $bookingpress_customer_id);
				}
			}

			$bookingpress_customers_data = array();
			if(!empty($bookingpress_customers_arr)){
				$counter = 1;
				foreach($bookingpress_customers_arr as $customer_key => $customer_val){
					$bookingpress_customer_id = intval($customer_val);

					//Get customer all details
					$bookingpress_customer_all_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id = %d", $bookingpress_customer_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is table name.
					
					if(!empty($bookingpress_customer_all_details)){
						$bookingpress_wpuser_id = $bookingpress_customer_all_details['bookingpress_wpuser_id'];
						$bookingpress_avatar_url = "";
						$bookingpress_get_existing_avatar_url = $BookingPress->get_bookingpress_customersmeta($bookingpress_customer_id, 'customer_avatar_details');
						$bookingpress_get_existing_avatar_url = ! empty($bookingpress_get_existing_avatar_url) ? maybe_unserialize($bookingpress_get_existing_avatar_url) : array();
						if (! empty($bookingpress_get_existing_avatar_url[0]['url']) ) {
							$bookingpress_avatar_url = $bookingpress_get_existing_avatar_url[0]['url'];
						} else {
							$bookingpress_avatar_url = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
						}
						$bookingpress_customer_tmp_details                       = array();
						$bookingpress_customer_tmp_details['id']                 = $counter;
						$bookingpress_customer_tmp_details['customer_id']        = intval($bookingpress_customer_id);
						$bookingpress_customer_tmp_details['customer_avatar']    = esc_url($bookingpress_avatar_url);
						$bookingpress_customer_tmp_details['customer_firstname'] = stripslashes_deep($bookingpress_customer_all_details['bookingpress_user_firstname']);
						$bookingpress_customer_tmp_details['customer_lastname']  = stripslashes_deep($bookingpress_customer_all_details['bookingpress_user_lastname']);
						$bookingpress_customer_tmp_details['customer_email']     = stripslashes_deep($bookingpress_customer_all_details['bookingpress_user_email']);
						$bookingpress_customer_tmp_details['customer_phone']     = esc_html($bookingpress_customer_all_details['bookingpress_user_phone']);

						// Fetch last appointment
						$last_appointment_data            = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_customer_id = %d ORDER BY bookingpress_appointment_booking_id DESC LIMIT 1", $bookingpress_customer_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
						$default_date_time_format         = get_option('date_format') . ' ' . get_option('time_format');
						$last_appointment_booked_datetime = ! empty($last_appointment_data['bookingpress_created_at']) ? date_i18n($bookingpress_date_format, strtotime($last_appointment_data['bookingpress_created_at'])) : '-';

						// Count total appointment
						$total_appointments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_customer_id = %d", $bookingpress_customer_all_details['bookingpress_customer_id'])); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

						$bookingpress_customer_tmp_details['customer_last_appointment']  = $last_appointment_booked_datetime;
						$bookingpress_customer_tmp_details['customer_total_appointment'] = $total_appointments;

						$bookingpress_customers_data[] = $bookingpress_customer_tmp_details;
						$counter++;
					}
				}
			}
			$bookingpress_total_customer_data = !empty($bookingpress_customers_data) ? count($bookingpress_customers_data) : 0;			
			$bookingpress_customers_data =  array_slice($bookingpress_customers_data,$offset,$perpage);						
			$return_data['items']      = $bookingpress_customers_data;
			$return_data['totalItems'] = $bookingpress_total_customer_data;
			echo wp_json_encode( $return_data );
			exit();
		}

		function bookingpress_get_revenue_report_charts_data_func(){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs;
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_revenue_report_chart_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			
			$custom_filter_val   = isset( $_POST['custom_filter_val'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), (array) $_POST['custom_filter_val'] ) : array();// phpcs:ignore

			$bookingpress_search_data  = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
			$return_data               = array();
			$search_filter_dates       = array();
			$payment_search_query = '1=1';
			$payments_group_by     = 'bookingpress_payment_date_time';

			$payment_gateway_filter = !empty($_POST['payment_gateway_filter']) ? sanitize_text_field($_POST['payment_gateway_filter']) : ''; // phpcs:ignore
			if( !empty($payment_gateway_filter) && $payment_gateway_filter != "all" ){
				$payment_search_query .= $wpdb->prepare( " AND bookingpress_payment_gateway = %s ", $payment_gateway_filter );
			}

			if ( ! empty( $bookingpress_search_data ) && ! empty( $bookingpress_search_data['service_name'] ) ) {
				$bookingpress_search_name       = $bookingpress_search_data['service_name'];
				$bookingpress_search_service_id = implode( ',', $bookingpress_search_name );
				$payment_search_query     .= " AND (bookingpress_service_id IN ({$bookingpress_search_service_id}))";
			}
			if ( ! empty( $bookingpress_search_data ) && ! empty( $bookingpress_search_data['staff_member_name'] ) ) {
				$bookingpress_search_name            = $bookingpress_search_data['staff_member_name'];
				$bookingpress_search_staff_member_id = implode( ',', $bookingpress_search_name );
				$payment_search_query          .= " AND (bookingpress_staff_member_id IN ({$bookingpress_search_staff_member_id}))";
			};

			
			$customer_filter_start_val  = ! empty( $custom_filter_val[0] ) ? sanitize_text_field( $custom_filter_val[0] ) : date( 'Y-m-d' );
			$customer_filter_end_val  = ! empty( $custom_filter_val[1] ) ? sanitize_text_field( $custom_filter_val[1] ) : date( 'Y-m-d' );
			$customer_filter_start_val = date('Y-m-d', strtotime($customer_filter_start_val));
			$customer_filter_end_val = date('Y-m-d', strtotime($customer_filter_end_val));

			$selected_filter_val = "";
			$bookingpress_current_date = date('Y-m-d', current_time('timestamp'));

			if(!empty($customer_filter_start_val) && !empty($customer_filter_end_val) && $customer_filter_start_val == $customer_filter_end_val ){
				$bookingpress_current_datetime_obj = new DateTime($bookingpress_current_date);
                $bookingpress_match_datetimt_obj = new DateTime($customer_filter_start_val);
                $bookingpress_dates_interval = $bookingpress_current_datetime_obj->diff($bookingpress_match_datetimt_obj);
                if($bookingpress_dates_interval->days == 1 && $bookingpress_dates_interval->invert == 1){ //Yesterday condition
                    $customer_filter_start_val  = $customer_filter_end_val = date('Y-m-d', strtotime('-1 days', current_time('timestamp')));                
                    $start_time = strtotime('today');
                    $end_time   = strtotime('tomorrow', $start_time) - 1;

                    while ( $start_time <= $end_time ) {
                        array_push($search_filter_dates, date('H:i:s', $start_time));
                        $start_time = strtotime('+1 hour', $start_time);
                    }

                    $selected_filter_val = "yesterday";
                }else if($bookingpress_dates_interval->days == 1 && $bookingpress_dates_interval->invert == 0){ //Tomorrow condition
                    $customer_filter_start_val = $customer_filter_end_val = date('Y-m-d', strtotime('+1 days', current_time('timestamp')));                            
                    $start_time = strtotime('today');
                    $end_time   = strtotime('tomorrow', $start_time) - 1;

                    while ( $start_time <= $end_time ) {
                        array_push($search_filter_dates, date('H:i:s', $start_time));
                        $start_time = strtotime('+1 hour', $start_time);
                    }

                    $selected_filter_val = "tomorrow";
                }else{ // Today condition
                    $customer_filter_start_val = $customer_filter_end_val = date('Y-m-d', current_time('timestamp'));                         
                    $start_time = strtotime('today');
                    $end_time   = strtotime('tomorrow', $start_time) - 1;

                    while ( $start_time <= $end_time ) {
                        array_push($search_filter_dates, date('H:i:s', $start_time));
                        $start_time = strtotime('+1 hour', $start_time);
                    }

                    $selected_filter_val = "today";
                }
			}else{
				$bookingpress_tmp_end_val = date('Y-m-d', strtotime("+1 days", strtotime($customer_filter_end_val)));
				$bookingpress_get_all_dates = new DatePeriod(
					new DateTime( $customer_filter_start_val ),
					new DateInterval( 'P1D' ),
					new DateTime( $bookingpress_tmp_end_val )
				);
				foreach ( $bookingpress_get_all_dates as $date_key => $date_val ) {
					$search_date_val = $date_val->format( 'M d' );
					array_push( $search_filter_dates, $search_date_val );
				}
			}
			
			$payment_search_query .= " AND (bookingpress_payment_date_time BETWEEN '" . $customer_filter_start_val . " 00:00:00' AND '" . $customer_filter_end_val . " 23:59:59')";

			$total_payments        = $wpdb->get_results( "SELECT SUM(bookingpress_paid_amount) as total, bookingpress_payment_date_time FROM {$tbl_bookingpress_payment_logs} WHERE {$payment_search_query} GROUP BY {$payments_group_by}", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

			$tmp_total_payments = array();

			if(!empty($selected_filter_val) && ($selected_filter_val == "today" || $selected_filter_val == "yesterday" || $selected_filter_val == "tomorrow") ){
				foreach ( $total_payments as $payment_key => $payment_val ) {
					$total_payments_records = (float) $payment_val['total'];

					$payment_date = date( 'H', strtotime( $payment_val['bookingpress_payment_date_time'] ) ).":00:00";

					if ( array_key_exists( $payment_date, $tmp_total_payments ) ) {
						$tmp_total_payments[ $payment_date ] = $tmp_total_payments[ $payment_date ] + $total_payments_records;
					} else {
						$tmp_total_payments[ $payment_date ] = $total_payments_records;
					}
				}
			}else{
				foreach ( $total_payments as $payment_key => $payment_val ) {
					$total_payments_records = (float) $payment_val['total'];

					$payment_date = date( 'M d', strtotime( $payment_val['bookingpress_payment_date_time'] ) );

					if ( array_key_exists( $payment_date, $tmp_total_payments ) ) {
						$tmp_total_payments[ $payment_date ] = $tmp_total_payments[ $payment_date ] + $total_payments_records;
					} else {
						$tmp_total_payments[ $payment_date ] = $total_payments_records;
					}
				}
			}

			$total_revenue_data = array();
			foreach ( $search_filter_dates as $filter_key => $filter_val ) {
                $total_revenue_vals = array_key_exists($filter_val, $tmp_total_payments) ? $tmp_total_payments[ $filter_val ] : 0;
                array_push($total_revenue_data, $total_revenue_vals);
            }

			//Get appointment stat data
			$bookingpress_total_paid_payments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_payment_log_id) as total_approved FROM {$tbl_bookingpress_payment_logs} WHERE {$payment_search_query} AND bookingpress_payment_status = %d", 1)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is table name.
			$bookingpress_total_pending_payments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_payment_log_id) as total_pending FROM {$tbl_bookingpress_payment_logs} WHERE {$payment_search_query} AND bookingpress_payment_status = %d", 2)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is table name.
			$bookingpress_total_refunded_payments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_payment_log_id) as total_refunded FROM {$tbl_bookingpress_payment_logs} WHERE {$payment_search_query} AND bookingpress_payment_status = %d", 3)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is table name.
			$bookingpress_total_partially_paid_payments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_payment_log_id) as total_partially_paid FROM {$tbl_bookingpress_payment_logs} WHERE {$payment_search_query} AND bookingpress_payment_status = %d", 4)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is table name.

			$bookingpress_payment_stat_data = array(
				'1' => !empty($bookingpress_total_paid_payments) ? $bookingpress_total_paid_payments : 0,
				'2' => !empty($bookingpress_total_pending_payments) ? $bookingpress_total_pending_payments : 0,
				'3' => !empty($bookingpress_total_refunded_payments) ? $bookingpress_total_refunded_payments : 0,
				'4' => !empty($bookingpress_total_partially_paid_payments) ? $bookingpress_total_partially_paid_payments : 0,
			);
			

			$return_data['total_revenue'] = $total_revenue_data;
			$return_data['chart_x_axis_vals']  = $search_filter_dates;
			$return_data['revenue_stat'] = $bookingpress_payment_stat_data;

			echo wp_json_encode( $return_data );
			exit();
		}

		function bookingpress_get_revenue_report_data_func(){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs,$bookingpress_global_options, $bookingpress_pro_payment;
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_revenue_report_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$perpage                   = isset( $_POST['perpage'] ) ? intval( $_POST['perpage'] ) : 10; // phpcs:ignore
			$currentpage               = isset( $_POST['currentpage'] ) ? intval( $_POST['currentpage'] ) : 1; // phpcs:ignore
			$offset                    = ( ! empty( $currentpage ) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
			
			$custom_filter_val         = ! empty( $_POST['revenue_custom_filter_val'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), (array) $_POST['revenue_custom_filter_val'] ) : array();// phpcs:ignore
			$return_data               = $search_filter_dates  = array();
			$payments_search_query = '1=1';
			
			$payment_gateway_filter = !empty($_POST['payment_gateway_filter']) ? sanitize_text_field($_POST['payment_gateway_filter']) : ''; // phpcs:ignore
			if( !empty($payment_gateway_filter) && $payment_gateway_filter != "all" ){
				$payments_search_query .= " AND bookingpress_payment_gateway = '".$payment_gateway_filter."'";
			}


			$appointments_group_by     = 'bookingpress_payment_date_time';
			$appointments = $bookingpress_total_payments_data = $bookingpress_payment_data = array();
			$bookingpress_search_data  = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function

			if ( ! empty( $bookingpress_search_data ) && ! empty( $bookingpress_search_data['service_name'] ) ) {
				$bookingpress_search_name       = $bookingpress_search_data['service_name'];
				$bookingpress_search_service_id = implode( ',', $bookingpress_search_name );
				$payments_search_query     .= " AND (bookingpress_service_id IN ({$bookingpress_search_service_id}))";
			}

			if ( ! empty( $bookingpress_search_data ) && ! empty( $bookingpress_search_data['staff_member_name'] ) ) {
				$bookingpress_search_name            = $bookingpress_search_data['staff_member_name'];
				$bookingpress_search_staff_member_id = implode( ',', $bookingpress_search_name );
				$payments_search_query          .= " AND (bookingpress_staff_member_id IN ({$bookingpress_search_staff_member_id}))";
			};

			$customer_filter_start_val  = ! empty( $custom_filter_val[0] ) ? sanitize_text_field( $custom_filter_val[0] ) : date( 'Y-m-d' );
			$customer_filter_end_val    = ! empty( $custom_filter_val[1] ) ? sanitize_text_field( $custom_filter_val[1] ) : date( 'Y-m-d' );
			$customer_filter_start_val = date('Y-m-d', strtotime($customer_filter_start_val));
			$customer_filter_end_val = date('Y-m-d', strtotime($customer_filter_end_val));

			$payments_search_query .= " AND (bookingpress_payment_date_time BETWEEN '" . $customer_filter_start_val . "' AND '" . $customer_filter_end_val . "')";
			$payments_search_query = apply_filters( 'bookingpress_payment_report_view_add_filter', $payments_search_query, $bookingpress_search_data );

			$bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_date_format    = $bookingpress_global_details['wp_default_date_format'] . '  ' . $bookingpress_global_details['wp_default_time_format'];
			$bookingpress_default_date_format = $bookingpress_global_details['wp_default_date_format'];
			$bookingpress_default_time_format = $bookingpress_global_details['wp_default_time_format'];

			$bookingpress_total_payments_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE {$payments_search_query} ", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			$bookingpress_payments_data       = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_payment_logs . ' WHERE ' . $payments_search_query . ' ORDER by bookingpress_payment_log_id DESC LIMIT ' . $offset . ',' . $perpage, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			$bookingpress_payment_status_arr = $bookingpress_global_details['payment_status'];

			if ( ! empty( $bookingpress_payments_data ) ) {
				foreach($bookingpress_payments_data as $k => $v){
					$payment_log_id = $v['bookingpress_payment_log_id'];
					$bookingpress_payment_details = $bookingpress_pro_payment->bookingpress_calculate_payment_details($payment_log_id);

					$bookingpress_tmp_payment_data = array();
					$bookingpress_tmp_payment_data['payment_date'] = date_i18n($bookingpress_default_date_format, strtotime($v['bookingpress_payment_date_time']));

					$bookingpress_customer_name = !empty($v['bookingpress_customer_firstname']) ? stripslashes_deep($v['bookingpress_customer_firstname'])." ".stripslashes_deep($v['bookingpress_customer_lastname']) : $v['bookingpress_customer_email'];
					$bookingpress_tmp_payment_data['payment_customer'] = $bookingpress_customer_name;

					$bookingpress_staffmember_name = !empty($v['bookingpress_staff_first_name']) ? $v['bookingpress_staff_first_name']." ".$v['bookingpress_staff_last_name'] : $v['bookingpress_staff_email_address'];

					$bookingpress_tmp_payment_data['staff_member_name'] = $bookingpress_staffmember_name;

					$bookingpress_tmp_payment_data['payment_service'] = stripslashes_deep($v['bookingpress_service_name']);

					$payment_gateway  = $v['bookingpress_payment_gateway'];
                    if ($payment_gateway == 'on-site' ) {
                        $payment_gateway = 'on site';
                    }

					$payment_gateway = apply_filters('bookingpress_selected_gateway_label_name', $payment_gateway, $payment_gateway);

					$bookingpress_tmp_payment_data['payment_gateway'] = $payment_gateway;

					$bookingpress_payment_status = $v['bookingpress_payment_status'];
					$bookingpress_payment_status_label = $bookingpress_payment_status;
					foreach($bookingpress_payment_status_arr as $payment_status_key => $payment_status_val){
                        if($payment_status_val['value'] == $bookingpress_payment_status){
                            $bookingpress_payment_status_label = $payment_status_val['text'];
                            break;
                        }
                    }

					$bookingpress_tmp_payment_data['payment_status'] = $bookingpress_payment_status;
					$bookingpress_tmp_payment_data['payment_status_label'] = $bookingpress_payment_status_label;

					$bookingpress_tmp_payment_data['is_cart'] = $bookingpress_payment_details['is_cart'];
					$bookingpress_tmp_payment_data['order_id'] = $bookingpress_payment_details['order_id'];
					
					if($bookingpress_payment_details['is_cart'] == '1'){
						$bookingpress_tmp_payment_data['staff_member_name'] = ' - ';
						$bookingpress_tmp_payment_data['payment_service'] = ' - ';
					}

					//Returns tax amount
					$bookingpress_tmp_payment_data['tax_amount'] = $bookingpress_payment_details['tax_amount'];
					$bookingpress_tmp_payment_data['tax_amount_with_currency'] = $bookingpress_payment_details['tax_amount_with_currency'];

					//Returns coupon details
					$bookingpress_tmp_payment_data['coupon_discount_amount'] = $bookingpress_payment_details['coupon_discount_amount'];
					$bookingpress_tmp_payment_data['coupon_discount_amount_with_currency'] = $bookingpress_payment_details['coupon_discount_amount_with_currency'];
					$bookingpress_tmp_payment_data['applied_coupon_code'] = $bookingpress_payment_details['applied_coupon_code'];


					$bookingpress_is_deposit_enable = $bookingpress_payment_details['is_deposit_enable'];
					$bookingpress_subtotal_amount = $bookingpress_payment_details['subtotal_amount'];

					if(!empty($bookingpress_payment_details)){
						$bookingpress_tmp_payment_data['is_deposit_enable'] = $bookingpress_is_deposit_enable;
					}

					$bookingpress_tmp_payment_data['deposit_amount'] = $bookingpress_payment_details['deposit_amount'];
					$bookingpress_tmp_payment_data['deposit_amount_with_currency'] = $bookingpress_payment_details['deposit_amount_with_currency'];

					$bookingpress_tmp_payment_data['due_amount'] = $bookingpress_payment_details['due_amount'];
					$bookingpress_tmp_payment_data['due_amount_with_currency'] = $bookingpress_payment_details['due_amount_with_currency'];

					$bookingpress_tmp_payment_data['subtotal_amount'] = $bookingpress_payment_details['subtotal_amount'];
					$bookingpress_tmp_payment_data['subtotal_amount_with_currency'] = $bookingpress_payment_details['subtotal_amount_with_currency'];

					$bookingpress_tmp_payment_data['payment_amount'] = $bookingpress_payment_details['payment_amount'];
					if($bookingpress_is_deposit_enable) {
						$bookingpress_tmp_payment_data['payment_numberic_amount'] = floatval($bookingpress_payment_details['deposit_amount']);
					}
					else {
						$bookingpress_tmp_payment_data['payment_numberic_amount'] = floatval($bookingpress_payment_details['total_amount']);
					}
					$bookingpress_tmp_payment_data['total_amount'] = $bookingpress_payment_details['total_amount'];
					$bookingpress_tmp_payment_data['total_amount_with_currency'] = $bookingpress_payment_details['total_amount_with_currency'];

					$bookingpress_tmp_payment_data = apply_filters('bookingpress_modified_payment_revenue_detail', $bookingpress_tmp_payment_data, $v);

					$bookingpress_payment_data[] = $bookingpress_tmp_payment_data;
				}
			}

			$return_data['items']      = $bookingpress_payment_data;
			$return_data['totalItems'] = count( $bookingpress_total_payments_data );
			echo wp_json_encode( $return_data );
			exit();
		}

		function bookingpress_get_appointment_report_func() {

			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs,$bookingpress_global_options, $bookingpress_pro_appointment, $bookingpress_appointment;
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_appointment_report_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			
			$perpage                   = isset( $_POST['perpage'] ) ? intval( $_POST['perpage'] ) : 10; // phpcs:ignore
			$currentpage               = isset( $_POST['currentpage'] ) ? intval( $_POST['currentpage'] ) : 1; // phpcs:ignore
			$offset                    = ( ! empty( $currentpage ) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
			$selected_filter_val       = ! empty( $_POST['selected_filter'] ) ? sanitize_text_field( $_POST['selected_filter'] ) : 'daily'; // phpcs:ignore
			$custom_filter_val         = ! empty( $_POST['custom_filter_val'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), (array) $_POST['custom_filter_val'] ) : array(); // phpcs:ignore
			$return_data               = $search_filter_dates  = array();
			$appointments_search_query = '1=1';
			$appointments_group_by     = 'bookingpress_appointment_date';
			$appointments = $bookingpress_total_appointments_data = $bookingpress_appointment_data = array();
			$bookingpress_search_data  = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function

			if ( ! empty( $bookingpress_search_data ) && ! empty( $bookingpress_search_data['service_name'] ) ) {
				$bookingpress_search_name       = $bookingpress_search_data['service_name'];
				$bookingpress_search_service_id = implode( ',', $bookingpress_search_name );
				$appointments_search_query     .= " AND (bookingpress_service_id IN ({$bookingpress_search_service_id}))";
			}

			if ( ! empty( $bookingpress_search_data ) && ! empty( $bookingpress_search_data['staff_member_name'] ) ) {
				$bookingpress_search_name            = $bookingpress_search_data['staff_member_name'];
				$bookingpress_search_staff_member_id = implode( ',', $bookingpress_search_name );
				$appointments_search_query          .= " AND (bookingpress_staff_member_id IN ({$bookingpress_search_staff_member_id}))";
			};

			$customer_filter_start_val  = ! empty( $custom_filter_val[0] ) ? sanitize_text_field( $custom_filter_val[0] ) : date( 'Y-m-d' );
			$customer_filter_end_val    = ! empty( $custom_filter_val[1] ) ? sanitize_text_field( $custom_filter_val[1] ) : date( 'Y-m-d' );
			$customer_filter_start_val = date('Y-m-d', strtotime($customer_filter_start_val));
			$customer_filter_end_val = date('Y-m-d', strtotime($customer_filter_end_val));

			$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $customer_filter_start_val . "' AND '" . $customer_filter_end_val . "')";
			$appointments_search_query = apply_filters( 'bookingpress_appointment_report_view_add_filter', $appointments_search_query, $bookingpress_search_data );

			$bookingpress_global_details = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_date_format    = $bookingpress_global_details['wp_default_date_format'] . '  ' . $bookingpress_global_details['wp_default_time_format'];
			$bookingpress_default_date_format = $bookingpress_global_details['wp_default_date_format'];
			$bookingpress_default_time_format = $bookingpress_global_details['wp_default_time_format'];

			$bookingpress_total_appointments_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} ", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			$bookingpress_appointments_data       = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_appointment_bookings . ' WHERE ' . $appointments_search_query . ' ORDER by bookingpress_appointment_booking_id DESC LIMIT ' . $offset . ',' . $perpage, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			$bookingpress_appointment_status_arr = $bookingpress_global_details['appointment_status'];

			if ( ! empty( $bookingpress_appointments_data ) ) {
				$counter = 1;
				foreach ( $bookingpress_appointments_data as $get_appointment ) {
					$bookingpress_tmp_appointment_data = array();
					$bookingpress_appointment_id = intval($get_appointment['bookingpress_appointment_booking_id']);
					$bookingpress_payment_id = intval($get_appointment['bookingpress_payment_id']);

					$bookingpress_appointment_details = $bookingpress_pro_appointment->bookingpress_calculated_appointment_details($bookingpress_appointment_id, $bookingpress_payment_id);

					$bookingpress_appointment_status = $get_appointment['bookingpress_appointment_status'];
					$bookingpress_tmp_appointment_data['appointment_status'] = $bookingpress_appointment_status;
					$bookingpress_appointment_status_label = $bookingpress_appointment_status;
					foreach($bookingpress_appointment_status_arr as $status_key => $status_val){
						if($bookingpress_appointment_status == $status_val['value']){
							$bookingpress_appointment_status_label = $status_val['text'];
							break;
						}
					}
					$bookingpress_tmp_appointment_data['appointment_status_label'] = $bookingpress_appointment_status_label;

					$bookingpress_tmp_appointment_data['booking_id'] = !empty($get_appointment['bookingpress_booking_id']) ? $get_appointment['bookingpress_booking_id'] : 1;

					$bookingpress_view_appointment_date = date_i18n($bookingpress_default_date_format, strtotime($get_appointment['bookingpress_appointment_date']));
					$bookingpress_view_appointment_time = date_i18n($bookingpress_default_time_format, strtotime($get_appointment['bookingpress_appointment_time']));

					$bookingpress_tmp_appointment_data['appointment_date'] = $bookingpress_view_appointment_date." ".$bookingpress_view_appointment_time;
					
					if(!empty($bookingpress_appointment_details)){
						$bookingpress_tmp_appointment_data['bookingpress_payment_status'] = $bookingpress_appointment_details['bookingpress_payment_status'];

						$bookingpress_tmp_appointment_data['bookingpress_subtotal_amt'] = $bookingpress_appointment_details['subtotal_amt'];
						$bookingpress_tmp_appointment_data['bookingpress_subtotal_amt_with_currency'] = $bookingpress_appointment_details['subtotal_amt_with_currency'];

						$bookingpress_tmp_appointment_data['bookingpress_deposit_amt'] = $bookingpress_appointment_details['deposit_price'];
						$bookingpress_tmp_appointment_data['bookingpress_deposit_amt_with_currency'] = $bookingpress_appointment_details['deposit_price_with_currency'];

						$bookingpress_tmp_appointment_data['bookingpress_tax_amt'] = $bookingpress_appointment_details['bookingpress_tax_amount'];
						$bookingpress_tmp_appointment_data['bookingpress_tax_amt_with_currency'] = $bookingpress_appointment_details['bookingpress_tax_amount_with_currency'];

						$bookingpress_tmp_appointment_data['bookingpress_applied_coupon_code'] = $bookingpress_appointment_details['applied_coupon'];
						$bookingpress_tmp_appointment_data['bookingpress_coupon_discount_amt'] = $bookingpress_appointment_details['coupon_discount_amt'];
						$bookingpress_tmp_appointment_data['bookingpress_coupon_discount_amt_with_currency'] = $bookingpress_appointment_details['coupon_discount_amt_with_currency'];

						$bookingpress_tmp_appointment_data['bookingpress_final_total_amt'] = $bookingpress_appointment_details['final_total_amount'];
						$bookingpress_tmp_appointment_data['bookingpress_final_total_amt_with_currency'] = $bookingpress_appointment_details['final_total_amount_with_currency'];

						$bookingpress_tmp_appointment_data['bookingpress_is_cart'] = $bookingpress_appointment_details['is_cart'];

						$bookingpress_tmp_appointment_data['bookingpress_is_deposit_enable'] = $bookingpress_appointment_details['is_deposit_enable'];
						if($bookingpress_appointment_details['is_deposit_enable']) {
							$bookingpress_tmp_appointment_data['payment_numberic_amount'] = floatval($bookingpress_appointment_details['deposit_price']);
						}
						else {
							$bookingpress_tmp_appointment_data['payment_numberic_amount'] = floatval($bookingpress_appointment_details['final_total_amount']);
						}
						$bookingpress_tmp_appointment_data['bookingpress_extra_service_data'] = $bookingpress_appointment_details['extra_services_details'];

						$bookingpress_tmp_appointment_data['bookingpress_selected_extra_members'] = $bookingpress_appointment_details['selected_extra_members'];

						$bookingpress_staff_firstname = !empty($bookingpress_appointment_details['staffmember_details']['staffmember_firstname']) ? $bookingpress_appointment_details['staffmember_details']['staffmember_firstname'] : '';

						$bookingpress_staff_lastname = !empty($bookingpress_appointment_details['staffmember_details']['staffmember_lastname']) ? $bookingpress_appointment_details['staffmember_details']['staffmember_lastname'] : '';

						$bookingpress_staff_email_address = !empty($bookingpress_appointment_details['staffmember_details']['staffmember_email_address']) ? $bookingpress_appointment_details['staffmember_details']['staffmember_email_address'] : '';

						$bookingpress_customer_firstname = !empty($bookingpress_appointment_details['customer_details']['customer_firstname']) ? $bookingpress_appointment_details['customer_details']['customer_firstname'] : '';
						$bookingpress_customer_lastname = !empty($bookingpress_appointment_details['customer_details']['customer_lastname']) ? $bookingpress_appointment_details['customer_details']['customer_lastname'] : '';
						$bookingpress_customer_email_address = !empty($bookingpress_appointment_details['customer_details']['customer_email_address']) ? $bookingpress_appointment_details['customer_details']['customer_email_address'] : '';

						$bookingpress_tmp_appointment_data['customer_name'] = !empty($bookingpress_customer_firstname) ? stripslashes_deep($bookingpress_customer_firstname)." ".stripslashes_deep($bookingpress_customer_lastname) : $bookingpress_customer_email_address;

						$bookingpress_tmp_appointment_data['staff_member_name'] = !empty($bookingpress_staff_firstname) ? $bookingpress_staff_firstname." ".$bookingpress_staff_lastname : $bookingpress_staff_email_address;

						$bookingpress_tmp_appointment_data['service_name'] = stripslashes_deep($get_appointment['bookingpress_service_name']);

						$service_duration             = esc_html($get_appointment['bookingpress_service_duration_val']);
						$service_duration_unit        = esc_html($get_appointment['bookingpress_service_duration_unit']);
						if($service_duration_unit != 'd') {
							if( !empty( $bookingpress_appointment ) && method_exists( $bookingpress_appointment, 'bookingpress_get_appointment_duration' ) ){
								$bookingpress_appointment_start_datetime = $get_appointment['bookingpress_appointment_date'].' '.$get_appointment['bookingpress_appointment_time'];
								$bookingpress_appointment_end_datetime = $get_appointment['bookingpress_appointment_date'].' '.$get_appointment['bookingpress_appointment_end_time'];
								$service_duration = $bookingpress_appointment->bookingpress_get_appointment_duration($bookingpress_appointment_start_datetime, $bookingpress_appointment_end_datetime);
							}
							else {
								if ($service_duration_unit == 'm' ) {
									$service_duration .= ' ' . esc_html__('Mins', 'bookingpress-appointment-booking');
								} else if ($service_duration_unit == 'h') {
									$service_duration .= ' ' . esc_html__('Hours', 'bookingpress-appointment-booking');
								} 
							}
						} else {
							if( 1 == $service_duration ){
								$service_duration .= ' ' . esc_html__('Day', 'bookingpress-appointment-booking');
							} else {   
								$service_duration .= ' ' . esc_html__('Days', 'bookingpress-appointment-booking');
							}                        
						}
						$bookingpress_tmp_appointment_data['appointment_duration'] = $service_duration;
					}

					$bookingpress_appointment_data[] = $bookingpress_tmp_appointment_data;
				}
			}
			
			$return_data['items']      = $bookingpress_appointment_data;
			$return_data['totalItems'] = count( $bookingpress_total_appointments_data );
			echo wp_json_encode( $return_data );
			exit();
		}

		function bookingpress_get_appointment_report_charts_data_func(){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs;
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_appointment_report_chart_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			
			$custom_filter_val   = isset( $_POST['custom_filter_val'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), (array) $_POST['custom_filter_val'] ) : array(); // phpcs:ignore

			$bookingpress_search_data  = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
			$return_data               = array();
			$search_filter_dates       = array();
			$appointments_search_query = '1=1';
			$appointments_group_by     = 'bookingpress_appointment_date';

			if ( ! empty( $bookingpress_search_data ) && ! empty( $bookingpress_search_data['service_name'] ) ) {
				$bookingpress_search_name       = $bookingpress_search_data['service_name'];
				$bookingpress_search_service_id = implode( ',', $bookingpress_search_name );
				$appointments_search_query     .= " AND (bookingpress_service_id IN ({$bookingpress_search_service_id}))";
			}
			if ( ! empty( $bookingpress_search_data ) && ! empty( $bookingpress_search_data['staff_member_name'] ) ) {
				$bookingpress_search_name            = $bookingpress_search_data['staff_member_name'];
				$bookingpress_search_staff_member_id = implode( ',', $bookingpress_search_name );
				$appointments_search_query          .= " AND (bookingpress_staff_member_id IN ({$bookingpress_search_staff_member_id}))";
			};

			
			$customer_filter_start_val  = ! empty( $custom_filter_val[0] ) ? sanitize_text_field( $custom_filter_val[0] ) : date( 'Y-m-d' );
			$customer_filter_end_val  = ! empty( $custom_filter_val[1] ) ? sanitize_text_field( $custom_filter_val[1] ) : date( 'Y-m-d' );
			$customer_filter_start_val = date('Y-m-d', strtotime($customer_filter_start_val));
			$customer_filter_end_val = date('Y-m-d', strtotime($customer_filter_end_val));
			
			$selected_filter_val = "";
			$bookingpress_current_date = date('Y-m-d', current_time('timestamp'));
			if(!empty($customer_filter_start_val) && !empty($customer_filter_end_val) && $customer_filter_start_val == $customer_filter_end_val ){
				$bookingpress_current_datetime_obj = new DateTime($bookingpress_current_date);
                $bookingpress_match_datetimt_obj = new DateTime($customer_filter_start_val);
                $bookingpress_dates_interval = $bookingpress_current_datetime_obj->diff($bookingpress_match_datetimt_obj);
                if($bookingpress_dates_interval->days == 1 && $bookingpress_dates_interval->invert == 1){ //Yesterday condition
                    $customer_filter_start_val  = $customer_filter_end_val = date('Y-m-d', strtotime('-1 days', current_time('timestamp')));                
                    $start_time = strtotime('today');
                    $end_time   = strtotime('tomorrow', $start_time) - 1;

                    while ( $start_time <= $end_time ) {
                        array_push($search_filter_dates, date('H:i:s', $start_time));
                        $start_time = strtotime('+1 hour', $start_time);
                    }

                    $selected_filter_val = "yesterday";
                }else if($bookingpress_dates_interval->days == 1 && $bookingpress_dates_interval->invert == 0){ //Tomorrow condition
                    $customer_filter_start_val = $customer_filter_end_val = date('Y-m-d', strtotime('+1 days', current_time('timestamp')));                            
                    $start_time = strtotime('today');
                    $end_time   = strtotime('tomorrow', $start_time) - 1;

                    while ( $start_time <= $end_time ) {
                        array_push($search_filter_dates, date('H:i:s', $start_time));
                        $start_time = strtotime('+1 hour', $start_time);
                    }

                    $selected_filter_val = "tomorrow";
                }else{ // Today condition
                    $customer_filter_start_val = $customer_filter_end_val = date('Y-m-d', current_time('timestamp'));                         
                    $start_time = strtotime('today');
                    $end_time   = strtotime('tomorrow', $start_time) - 1;

                    while ( $start_time <= $end_time ) {
                        array_push($search_filter_dates, date('H:i:s', $start_time));
                        $start_time = strtotime('+1 hour', $start_time);
                    }

                    $selected_filter_val = "today";
                }
			}else {

				$bookingpress_tmp_end_val = date('Y-m-d', strtotime("+1 days", strtotime($customer_filter_end_val)));
				$bookingpress_get_all_dates = new DatePeriod(
					new DateTime( $customer_filter_start_val ),
					new DateInterval( 'P1D' ),
					new DateTime( $bookingpress_tmp_end_val )
				);
				foreach ( $bookingpress_get_all_dates as $date_key => $date_val ) {
					$search_date_val = $date_val->format( 'M d' );
					array_push( $search_filter_dates, $search_date_val );
				}
			}


			$appointments_search_query .= " AND (bookingpress_appointment_date BETWEEN '" . $customer_filter_start_val . "' AND '" . $customer_filter_end_val . "')";

			$appointments_search_query = apply_filters( 'bookingpress_appointment_report_view_add_filter', $appointments_search_query, $bookingpress_search_data );

			$total_appointments        = $wpdb->get_results( "SELECT COUNT(bookingpress_appointment_booking_id) as total, bookingpress_appointment_date, bookingpress_appointment_time FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} GROUP BY {$appointments_group_by}", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			$tmp_total_appointments = array();

			if(!empty($selected_filter_val) && ($selected_filter_val == "today" || $selected_filter_val == "yesterday" || $selected_filter_val == "tomorrow") ){
				foreach ( $total_appointments as $appointment_key => $appointment_val ) {
					$total_appointments = (int) $appointment_val['total'];
					$bookingpress_appointment_time = $appointment_val['bookingpress_appointment_time'];
					
					if ( array_key_exists( $bookingpress_appointment_time, $tmp_total_appointments ) ) {
						$tmp_total_appointments[ $bookingpress_appointment_time ] = $tmp_total_appointments[ $bookingpress_appointment_time ] + $total_appointments;
					} else {
						$tmp_total_appointments[ $bookingpress_appointment_time ] = $total_appointments;
					}
				}
			}else{
				foreach ( $total_appointments as $appointment_key => $appointment_val ) {
					$total_appointments = (int) $appointment_val['total'];

					$appointment_date = date( 'M d', strtotime( $appointment_val['bookingpress_appointment_date'] ) );

					if ( array_key_exists( $appointment_date, $tmp_total_appointments ) ) {
						$tmp_total_appointments[ $appointment_date ] = $tmp_total_appointments[ $appointment_date ] + $total_appointments;
					} else {
						$tmp_total_appointments[ $appointment_date ] = $total_appointments;
					}
				}
			}

			//Get appointment stat data
			$bookingpress_total_approved_appointments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_approved FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} AND bookingpress_appointment_status = %d", 1)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
			$bookingpress_total_pending_appointments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_pending FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} AND bookingpress_appointment_status = %d", 2)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
			$bookingpress_total_cancelled_appointments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_cancelled FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} AND bookingpress_appointment_status = %d", 3)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
			$bookingpress_total_rejected_appointments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_rejected FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} AND bookingpress_appointment_status = %d", 4)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
			$bookingpress_total_noshow_appointments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_noshow FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} AND bookingpress_appointment_status = %d", 5)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
			$bookingpress_total_completed_appointments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_completed FROM {$tbl_bookingpress_appointment_bookings} WHERE {$appointments_search_query} AND bookingpress_appointment_status = %d", 6)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.

			$bookingpress_appointment_stat_data = array(
				'1' => !empty($bookingpress_total_approved_appointments) ? $bookingpress_total_approved_appointments : 0,
				'2' => !empty($bookingpress_total_pending_appointments) ? $bookingpress_total_pending_appointments : 0,
				'3' => !empty($bookingpress_total_cancelled_appointments) ? $bookingpress_total_cancelled_appointments : 0,
				'4' => !empty($bookingpress_total_rejected_appointments) ? $bookingpress_total_rejected_appointments : 0,
				'5' => !empty($bookingpress_total_noshow_appointments) ? $bookingpress_total_noshow_appointments : 0,
				'6' => !empty($bookingpress_total_completed_appointments) ? $bookingpress_total_completed_appointments : 0,
			);			

			$return_data['total_appointments'] = $tmp_total_appointments;
			$return_data['chart_x_axis_vals']  = $search_filter_dates;
			$return_data['appointment_stat'] = $bookingpress_appointment_stat_data;

			echo wp_json_encode( $return_data );
			exit();
		}

		function bookingpress_load_reports_view_func() {
			$bookingpress_load_file_name = BOOKINGPRESS_PRO_VIEWS_DIR . '/reports/manage_report.php';
			require $bookingpress_load_file_name;
		}

		function bookingpress_reports_vue_methods_func() {
			global $bookingpress_notification_duration,$bookingpress_global_options;
			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? $bookingpress_global_options_arr['bookingpress_staffmember_singular_name'] : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
			?>
			handle_appointment_size_change(val) {
				this.appointment_per_page = val
				this.load_appointment_data()
			},
			handle_current_change(val) {
				this.appointment_current_page = val;
				this.load_appointment_data()
			},
			change_appointment_CurrentPage(perPage) {
                var total_item = this.appointment_total_items;
                var recored_perpage = perPage;
                var select_page =  this.appointment_current_page;                
                var current_page = Math.ceil(total_item/recored_perpage);
                if(total_item <= recored_perpage ) {
                    current_page = 1;
                } else if(select_page >= current_page ) {
                    
                } else {
                    current_page = select_page;
                }
                return current_page;
            },
            change_appointment_PaginationSize(selectedPage) {     
                var total_recored_perpage = selectedPage;
                var current_page = this.change_appointment_CurrentPage(total_recored_perpage);                                        
                this.appointment_per_page = selectedPage;                    
                this.appointment_current_page = current_page;    
                this.load_appointment_data()
            },
			load_appointment_charts(){
				const vm = this
				var bookingpress_search_data = {'service_name': vm.appointment_search_service, 'staff_member_name':vm.appointment_search_staff };
				<?php do_action( 'bookingpress_modify_report_search_data_for_chart'); ?>
				var postData = { action:'bookingpress_get_appointment_report_charts_data', selected_filter: vm.currently_selected_filter, custom_filter_val: vm.custom_filter_val, search_data:bookingpress_search_data, _wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm.appointment_chart_x_axis_data = response.data.chart_x_axis_vals;                                                    
					vm.total_appointments = response.data.total_appointments;
					vm.appointment_stat = response.data.appointment_stat;

					if(bookingpress_appointments_chart != '' && bookingpress_appointments_chart != undefined){
						bookingpress_appointments_chart.destroy()
					}                        
					const ctx = document.getElementById('bookingpress_appointments_charts').getContext('2d');                    
					bookingpress_appointments_chart = new Chart(ctx, {
						type: 'bar',
						data: {
							labels: vm.appointment_chart_x_axis_data,
							datasets: [{
								label:'<?php esc_html_e( 'Total Appointment', 'bookingpress-appointment-booking' ); ?>',
								data: vm.total_appointments,
								backgroundColor: [
									'rgba(18, 212, 136, 0.3)',
								],
								borderColor: [
									'rgba(18, 212, 136, 1)',
								],
								borderWidth: 1
							}]
						},
						options: {
							scales: {
								y: {
									beginAtZero: true
								}
							},
							responsive: true,
							plugins:{
								legend: {
									position: 'top',
									onClick: null,
								},
								title: {
									display: true,
									text: '<?php esc_html_e( 'Appointments', 'bookingpress-appointment-booking' ); ?>'
								},								
								tooltip: {
									callbacks: {
										label: function(context) {
											let label = context.dataset.label || '';
											if (label) {
												label += ': ';
											}
											if (context.parsed.y !== null) {
												label += context.parsed.y;
											}
											return label;
										}
									}
								} 					               
							},                         
						}
					});
				}.bind(this) )
				.catch( function (error) {					
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
					});
				});					
			},
			load_appointment_data(){
				const vm = this
				var bookingpress_search_data = {'service_name': vm.appointment_search_service,'staff_member_name' :vm.search_staff_member_name};
				<?php do_action( 'bookingpress_modify_report_search_data'); ?>
				var postData = { action:'bookingpress_get_appointment_report_data', perpage:this.appointment_per_page, currentpage:this.appointment_current_page, custom_filter_val: vm.custom_filter_val,search_data:bookingpress_search_data,_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {              
					vm.items = response.data.items;
					vm.appointment_total_items = response.data.totalItems;                              
				}.bind(this) )
				.catch( function (error) {					
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
					});
				});					
			},
			select_appointment_report_filter(filter_val){
				const vm = this
				vm.currently_selected_filter = filter_val
				this.load_appointment_charts()
				this.load_appointment_data()
			},
			select_report_custom_date_filter(selected_value){
				const vm = this;
				vm.custom_filter_val[0] = vm.get_formatted_date(this.custom_filter_val[0])
				vm.custom_filter_val[1] = vm.get_formatted_date(this.custom_filter_val[1])
				vm.load_appointment_charts()
				vm.load_appointment_data()          
			},
			change_appointment_report_filter(){
				const vm = this;
				vm.load_appointment_charts()
				vm.load_appointment_data()              
			},		
			get_formatted_date(iso_date){

				if( true == /(\d{2})\T/.test( iso_date ) ){
					let date_time_arr = iso_date.split('T');
					return date_time_arr[0];
				}
				var __date = new Date(iso_date);
				var __year = __date.getFullYear();
				var __month = __date.getMonth()+1;
				var __day = __date.getDate();
				if (__day < 10) {
					__day = '0' + __day;
				}
				if (__month < 10) {
					__month = '0' + __month;
				}
				var formatted_date = __year+'-'+__month+'-'+__day;
				return formatted_date;
			},
			handle_revenue_size_change(val) {
				this.revenue_per_page = val
				this.load_revenue_data()
			},
			handle_revenue_current_change(val) {
				this.revenue_current_page = val;
				this.load_revenue_data()
			},
			change_revenue_CurrentPage(perPage) {
                var total_item = this.revenue_total_items;
                var recored_perpage = perPage;
                var select_page =  this.customer_current_page;                
                var current_page = Math.ceil(total_item/recored_perpage);
                if(total_item <= recored_perpage ) {
                    current_page = 1;
                } else if(select_page >= current_page ) {
                    
                } else {
                    current_page = select_page;
                }
                return current_page;
            },
            change_revenue_PaginationSize(selectedPage) {     
                var total_recored_perpage = selectedPage;
                var current_page = this.change_revenue_CurrentPage(total_recored_perpage);                                        
                this.revenue_per_page = selectedPage;                    
                this.revenue_current_page = current_page;    
                this.load_revenue_data()
            },
			load_revenue_charts(){
				const vm = this
				var postData = { action:'bookingpress_get_revenue_report_charts_data', custom_filter_val: vm.revenue_custom_filter_val, payment_gateway_filter: vm.revenue_payment_gateway_filter, _wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm.revenue_chart_x_axis_data = response.data.chart_x_axis_vals;                                                    
					vm.total_revenue = response.data.total_revenue;
					vm.revenue_stat = response.data.revenue_stat;

					var currency_symbol = vm.currency_symbol;

					if(bookingpress_revenue_charts != '' && bookingpress_revenue_charts != undefined){
						bookingpress_revenue_charts.destroy()
					}                        
					const ctx2 = document.getElementById('bookingpress_revenue_charts').getContext('2d');                    
					bookingpress_revenue_charts = new Chart(ctx2, {
						type: 'line',
						data: {
							labels: vm.revenue_chart_x_axis_data,
							datasets: [{
								label:'<?php esc_html_e( 'Total Revenue', 'bookingpress-appointment-booking' ); ?>',
								data: vm.total_revenue,
								borderColor: 'rgba(18, 212, 136, 1)',
							}]
						},
						options: {
							plugins:{
								legend: {
									position: 'top',
									onClick: null,
								},
								tooltip: {
									callbacks: {
										label: function(context) {
											let label = context.dataset.label || '';
											if (label) {
												label += ': '+currency_symbol;
											}
											if (context.parsed.y !== null) {
												label += context.parsed.y;
											}
											return label;
										}
									},
								},
							},
						},
					});
				}.bind(this) )
				.catch( function (error) {					
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
					});
				});					
			},
			load_revenue_data(){
				const vm = this
				var postData = { action:'bookingpress_get_revenue_report_data', perpage:this.revenue_per_page, currentpage:this.revenue_current_page, revenue_custom_filter_val: vm.revenue_custom_filter_val, payment_gateway_filter: vm.revenue_payment_gateway_filter, _wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {              
					vm.revenue_items = response.data.items;
					vm.revenue_total_items = response.data.totalItems;
				}.bind(this) )
				.catch( function (error) {					
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
					});
				});					
			},
			select_revenue_report_filter(filter_val){				
				const vm = this
				this.load_revenue_charts()
				this.load_revenue_data()
			},
			handle_customer_size_change(val) {
				this.customer_per_page = val
				this.load_customers_data()
			},
			handle_customer_current_change(val) {
				this.customer_current_page = val;
				this.load_customers_data()
			},
			changeCurrentPage(perPage) {
                var total_item = this.customer_total_items;
                var recored_perpage = perPage;
                var select_page =  this.customer_current_page;                
                var current_page = Math.ceil(total_item/recored_perpage);
                if(total_item <= recored_perpage ) {
                    current_page = 1;
                } else if(select_page >= current_page ) {
                    
                } else {
                    current_page = select_page;
                }
                return current_page;
            },
            change_customer_PaginationSize(selectedPage) {     
                var total_recored_perpage = selectedPage;
                var current_page = this.changeCurrentPage(total_recored_perpage);                                        
                this.customer_per_page = selectedPage;                    
                this.customer_current_page = current_page;    
                this.load_customers_data()
            },
			load_customer_charts(){
				const vm = this
				var postData = { action:'bookingpress_get_customer_report_charts_data', custom_filter_val: vm.customer_custom_filter_val, _wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm.existing_customers_count = response.data.existing_customers_count;
					vm.new_customers_count = response.data.new_customers_count;
					if(bookingpress_customers_charts != '' && bookingpress_customers_charts != undefined){
						bookingpress_customers_charts.destroy()
					}                        
					const ctx3 = document.getElementById('bookingpress_customers_charts').getContext('2d');                    
					bookingpress_customers_charts = new Chart(ctx3, {
						type: 'bar',
						data: {
							labels: response.data.chart_x_axis_vals,
							datasets: [
								{
									label:'<?php esc_html_e( 'Existing Customers', 'bookingpress-appointment-booking' ); ?>',
									data: response.data.existing_customers,
									backgroundColor: 'rgba(18, 212, 136, 0.3)',
									borderColor: 'rgba(18, 212, 136, 1)',
									borderWidth: 1
								},
								{
									label:'<?php esc_html_e( 'New Customers', 'bookingpress-appointment-booking' ); ?>',
									data: response.data.new_customers,
									backgroundColor: 'rgba(255, 205, 86, 0.2)',
									borderColor: 'rgba(255, 99, 132, 1)',
									borderWidth: 1
								}
							]
						},
						options: {
							responsive: true,
							legend: {
								position: 'right'
							},
							scales: {
								x: {
									stacked: true
								},
								y: {
									stacked: true
								}
							}
						},
					});
				}.bind(this) )
				.catch( function (error) {					
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
					});
				});					
			},
			load_customers_data(){
				const vm = this
				var postData = { action:'bookingpress_get_customer_report_data', perpage:this.customer_per_page, currentpage:this.customer_current_page, custom_filter_val: vm.customer_custom_filter_val, _wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {              
					vm.customer_items = response.data.items;
					vm.customer_total_items = parseInt(response.data.totalItems);
				}.bind(this) )
				.catch( function (error) {					
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
					});
				});	
			},
			select_customer_report_filter(){
				const vm = this
				this.load_customer_charts()
				this.load_customers_data()
			},
			<?php
		}

		function bookingpress_reports_on_load_methods_func() {
			global $bookingpress_global_options;
            $bookingpress_global_details  = $bookingpress_global_options->bookingpress_global_options();
			?>
				const vm = this;
				vm.bookingpress_picker_options = {
					shortcuts: [
						{
							text: '<?php esc_html_e('Today', 'bookingpress-appointment-booking'); ?>',
							onClick(picker) {
								const end = new Date();
								const start = new Date();
								picker.$emit('pick', [start, end]);
							}
						}, 
						{
							text: '<?php esc_html_e('Yesterday', 'bookingpress-appointment-booking'); ?>',
							onClick(picker) {
								var bookingpress_yesterday_date = new Date();
								bookingpress_yesterday_date.setDate(bookingpress_yesterday_date.getDate() - 1);
								const end = bookingpress_yesterday_date;
								const start = bookingpress_yesterday_date;
								picker.$emit('pick', [start, end]);
							}
						}, 
						{
							text: '<?php esc_html_e('Tomorrow', 'bookingpress-appointment-booking'); ?>',
							onClick(picker) {
								var bookingpress_tomorrow_date = new Date();
								bookingpress_tomorrow_date.setDate(bookingpress_tomorrow_date.getDate() + 1);
								const end = bookingpress_tomorrow_date;
								const start = bookingpress_tomorrow_date;
								picker.$emit('pick', [start, end]);
							}
						}, 
						{
							text: '<?php esc_html_e('This week', 'bookingpress-appointment-booking'); ?>',
							onClick(picker) {
								var bookingpress_date_obj = new Date();
								var first_date = (bookingpress_date_obj.getDate() + 1) - bookingpress_date_obj.getDay();
								var end_date = first_date + 6;
								var first_date_obj = new Date(bookingpress_date_obj);
								first_date_obj.setDate(first_date);
								var end_date_obj = new Date(bookingpress_date_obj);
								end_date_obj.setDate(end_date);
								picker.$emit('pick', [first_date_obj, end_date_obj]);
							}
						}, 
						{
							text: '<?php esc_html_e('Last week', 'bookingpress-appointment-booking'); ?>',
							onClick(picker) {
								var first_date_obj = new Date(moment().day(-7));
								var end_date_obj = new Date(moment().day(-1));
								picker.$emit('pick', [first_date_obj, end_date_obj]);
							}
						}, 
						{
							text: '<?php esc_html_e('This month', 'bookingpress-appointment-booking'); ?>',
							onClick(picker) {
								var bookingpress_current_month_obj = new Date();
								var bookingpress_firstday = new Date(bookingpress_current_month_obj.getFullYear(), bookingpress_current_month_obj.getMonth(), 1);
								var bookingpress_lastday = new Date(bookingpress_current_month_obj.getFullYear(), bookingpress_current_month_obj.getMonth() + 1, 0);
								const end = bookingpress_lastday;
								const start = bookingpress_firstday;
								picker.$emit('pick', [start, end]);
							}
						}, 
						{
							text: '<?php esc_html_e('Last month', 'bookingpress-appointment-booking'); ?>',
							onClick(picker) {
								var bookingpress_date_obj = new Date();
								var bookingpress_firstday_prv_month = new Date(bookingpress_date_obj.getFullYear(), bookingpress_date_obj.getMonth() - 1, 1);
								var bookingpress_lastday_prv_month = new Date(bookingpress_date_obj.getFullYear(), bookingpress_date_obj.getMonth(), 0);
								picker.$emit('pick', [bookingpress_firstday_prv_month, bookingpress_lastday_prv_month]);
							}
						},
						{
							text: '<?php esc_html_e('This year', 'bookingpress-appointment-booking'); ?>',
							onClick(picker) {
								var bookingress_date_obj = new Date();
								var bookingpress_first_day = new Date(bookingress_date_obj.getFullYear(), 0, 1);
								var bookingpress_last_day = new Date(bookingress_date_obj.getFullYear(), 11, 31);
								const end = bookingpress_last_day;
								const start = bookingpress_first_day;
								picker.$emit('pick', [start, end]);
							}
						},
					],
					'firstDayOfWeek': parseInt('<?php echo esc_html($bookingpress_global_details['start_of_week']); ?>')
				};
				vm.load_appointment_charts();
				vm.load_appointment_data();
				vm.load_revenue_charts();
				vm.load_revenue_data();
				vm.load_customer_charts();
				vm.load_customers_data();
				<?php
				do_action('bookingpress_add_report_dynamic_on_load_methods');
			
		}

		function bookingpress_reports_dynamic_data_fields_func() {

			global $wpdb, $bookingpress_report_vue_data_fields, $BookingPress, $bookingpress_global_options, $bookingpress_pro_staff_members, $tbl_bookingpress_services;
						
			$bookingpress_default_perpage_option = $BookingPress->bookingpress_get_settings('per_page_item', 'general_setting');
			$bookingpress_default_perpage_option = ! empty($bookingpress_default_perpage_option) ? $bookingpress_default_perpage_option : '20';
			
            $bookingpress_report_vue_data_fields['appointment_per_page']  = $bookingpress_default_perpage_option;
            $bookingpress_report_vue_data_fields['appointment_pagination_length'] = $bookingpress_default_perpage_option;			

			$bookingpress_report_vue_data_fields['revenue_per_page']  = $bookingpress_default_perpage_option;
            $bookingpress_report_vue_data_fields['revenue_pagination_length'] = $bookingpress_default_perpage_option;			

			$bookingpress_report_vue_data_fields['customer_per_page']  = $bookingpress_default_perpage_option;
            $bookingpress_report_vue_data_fields['customer_pagination_length'] = $bookingpress_default_perpage_option;						

			$bookingpress_report_vue_data_fields['items'] = array();
			$bookingpress_report_vue_data_fields['bookingpress_picker_options'] = array();

			$bookingpress_report_vue_data_fields['appointment_stat'] = array(
				'1' => 0,
				'2' => 0,
				'3' => 0,
				'4' => 0,
				'5' => 0,
				'6' => 0,
			);

			$bookingpress_report_vue_data_fields['revenue_items'] = array();
			$bookingpress_report_vue_data_fields['revenue_stat'] = array(
				'1' => 0,
				'2' => 0,
				'3' => 0,
				'4' => 0,
			);

			$bookingpress_report_vue_data_fields['customer_items'] = array();
			$bookingpress_report_vue_data_fields['customer_stat'] = array(
				'existing_customers' => 0,
				'new_customers' => 0,
			);
			
			$currency_name   = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
			$currency_name   = ! empty( $currency_name ) ? $currency_name : 'USD';
			$currency_symbol = $BookingPress->bookingpress_get_currency_symbol( $currency_name );
			$bookingpress_report_vue_data_fields['currency_symbol'] = $currency_symbol;

			//Get all services list			
            $bookingpress_services_details2    = $BookingPress->get_bookingpress_service_data_group_with_category();            		
			$bookingpress_report_vue_data_fields['appointment_service_filter_data'] = $bookingpress_services_details2;

			$is_staffmember_activated                                        = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_report_vue_data_fields['is_staffmember_activated'] = $is_staffmember_activated;
			if ( $is_staffmember_activated ) {
				$bookingpress_report_vue_data_fields['search_staff_member_list'] = $bookingpress_pro_staff_members->bookingpress_staffmember_search_list();
			}

			$bookingpress_report_vue_data_fields['appointment_search_service'] = '';
			$bookingpress_report_vue_data_fields['appointment_search_staff'] = '';


			$bookingpress_report_vue_data_fields = apply_filters( 'bookingpress_modify_report_data_fields', $bookingpress_report_vue_data_fields );

			$bookingpress_report_vue_data_fields['currently_selected_filter'] = 'this_week';
			$week_number  = date( 'W' );
			$current_year = date( 'Y' );
			$week_dates   = $BookingPress->get_weekstart_date_end_date( $week_number, $current_year );
			$week_start   = $week_dates['week_start'];
			$week_end     = $week_dates['week_end'];
			$bookingpress_report_vue_data_fields['custom_filter_val'] = array($week_start, $week_end);

			$bookingpress_report_vue_data_fields['revenue_custom_filter_val'] = array($week_start, $week_end);

			$bookingpress_report_vue_data_fields['customer_custom_filter_val'] = array($week_start, $week_end);

			$bookingpress_report_vue_data_fields['existing_customers_count'] = 0;
			$bookingpress_report_vue_data_fields['new_customers_count'] = 0;

			$bookingpress_report_vue_data_fields['revenue_payment_gateway_filter'] = 'all';

			$bookingpress_report_vue_data_fields['main_col_width'] = array(
				'xs' => 12,
				'sm' => 12,
				'md' => 12,
				'lg' => 12,
				'xl' => 12
			);
			$bookingpress_report_vue_data_fields['service_col_width'] = array(
				'xs' => 12,
				'sm' => 12,
				'md' => 12,
				'lg' => 12,
				'xl' => 12
			);
			$bookingpress_report_vue_data_fields['staff_col_width'] = array(
				'xs' => 12,
				'sm' => 12,
				'md' => 12,
				'lg' => 12,
				'xl' => 12
			);

			$bookingpress_report_vue_data_fields = apply_filters('bookingpress_modify_report_data_fields',$bookingpress_report_vue_data_fields);

			echo wp_json_encode( $bookingpress_report_vue_data_fields );
		}

		function bookingpress_reports_dynamic_helper_vars_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
				var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
				ELEMENT.locale(lang)

				var bookingpress_appointments_chart = ''
				var bookingpress_revenue_charts = ''
				var bookingpress_customers_charts = ''
			
			<?php
            	do_action('bookingpress_add_report_dynamic_helper_vars');
		}
	}
}

global $bookingpress_pro_reports,$bookingpress_report_vue_data_fields;
$bookingpress_pro_reports = new bookingpress_pro_reports();

$bookingpress_report_vue_data_fields = array(
	'pagination_val' => array(
		array(
			'text'  => '10',
			'value' => '10',
		),
		array(
			'text'  => '20',
			'value' => '20',
		),
		array(
			'text'  => '50',
			'value' => '50',
		),
		array(
			'text'  => '100',
			'value' => '100',
		),
		array(
			'text'  => '200',
			'value' => '200',
		),
		array(
			'text'  => '300',
			'value' => '300',
		),
		array(
			'text'  => '400',
			'value' => '400',
		),
		array(
			'text'  => '500',
			'value' => '500',
		),
	),
	'appointment_per_page' => 10,
	'appointment_total_items' => 0,
	'appointment_current_page' => 1,
	'appointment_pagination_length' => 10,
	'revenue_total_items' => 0,
	'revenue_current_page' => 1,
	'revenue_pagination_length' => 10,
	'revenue_per_page' => 10,
	'customer_total_items' => 0,
	'customer_current_page' => 1,
	'customer_pagination_length' => 10,
	'customer_per_page' => 10,
);