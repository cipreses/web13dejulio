<?php
if ( ! class_exists( 'bookingpress_pro_timesheet' ) ) {
	class bookingpress_pro_timesheet Extends BookingPress_Core {
		function __construct() {
			add_action( 'bookingpress_timesheet_dynamic_view_load', array( $this, 'bookingpress_load_timesheet_view_func' ) );
			add_action( 'bookingpress_timesheet_dynamic_data_fields', array( $this, 'bookingpress_timesheet_dynamic_data_fields_func' ) );
			add_action( 'bookingpress_timesheet_dynamic_on_load_methods', array( $this, 'bookingpress_timesheet_dynamic_onload_methods_func' ) );
			add_action( 'bookingpress_timesheet_dynamic_vue_methods', array( $this, 'bookingpress_timesheet_dynamic_vue_methods_func' ) );
			add_action( 'bookingpress_timesheet_dynamic_helper_vars', array( $this, 'bookingpress_timesheet_dynamic_helper_vars_func' ) );

			add_action( 'wp_ajax_bookingpress_timesheet_add_days_off', array( $this, 'bookingpress_timesheet_add_days_off_func' ) );
			add_action( 'wp_ajax_bookingpress_timesheet_get_days_off', array( $this, 'bookingpress_get_timesheet_daysoff_details_func' ) );
			add_action( 'wp_ajax_bookingpress_timesheet_delete_days_off', array( $this, 'bookingpress_delete_daysoff_func' ) );

			add_action( 'wp_ajax_bookingpress_timesheet_add_staffmember_special_day', array( $this,'bookingpress_timesheet_add_staffmember_special_day_func' ) );
			add_action( 'wp_ajax_bookingpress_timesheet_get_special_days', array( $this, 'bookingpress_timesheet_get_special_days_func' ) );
			add_action( 'wp_ajax_bookingpress_timesheet_delete_staffmember_special_day', array( $this, 'bookingpress_timesheet_delete_staffmember_special_day_func' ) );
			add_action( 'wp_ajax_bookingpress_validate_staff_member_special_day', array( $this, 'bookingpress_validate_staff_member_special_day_func' ) );	
			
			add_action('wp_ajax_bookingpress_get_timesheet_staffmember_workhour_data',array($this,'bookingpress_get_timesheet_staffmember_workhour_data_func'));
			add_action( 'wp_ajax_bookingpress_add_staff_workinghour', array( $this, 'bookingpress_add_staff_workinghour_func' ), 10 );
			
		}
		function bookingpress_validate_staff_member_special_day_func() {
			global $wpdb,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_staffmembers;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'timesheet_validate_staff_special_days', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			$bookingpress_current_user_id  = get_current_user_id();
			$bookingpress_staffmember_data = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers . ' WHERE bookingpress_wpuser_id = %d', $bookingpress_current_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
			$bookingpress_staffmember_id   = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ? intval( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) : 0;
			if ( ! empty( $_REQUEST['selected_date_range'] ) && ! empty( $bookingpress_staffmember_id ) ) { 
				$bookingpress_start_date         = date( 'Y-m-d', strtotime( sanitize_text_field( $_REQUEST['selected_date_range'][0] ) ) );  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated --Reason: data has been validated above
				$bookingpress_end_date           = date( 'Y-m-d', strtotime( sanitize_text_field( $_REQUEST['selected_date_range'][1] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated --Reason: data has been validated above
				$bookingpress_status             = array( '1', '2' );
				$total_appointments              = 0;
				$bookingpress_search_query_where = 'WHERE 1=1 ';
				if ( ! empty( $bookingpress_start_date ) && ! empty( $bookingpress_end_date ) && ! empty( $bookingpress_staffmember_id ) ) {
						$bookingpress_search_query_where .= " AND (bookingpress_appointment_date BETWEEN '{$bookingpress_start_date}' AND '{$bookingpress_end_date}') AND (bookingpress_staff_member_id = {$bookingpress_staffmember_id})";
				}
				if ( ! empty( $bookingpress_status ) && is_array( $bookingpress_status ) ) {
					$bookingpress_search_query_where .= ' AND (';
					$i                                = 0;
					foreach ( $bookingpress_status as $status_key => $status_value ) {
						if ( $i != 0 ) {
							$bookingpress_search_query_where .= ' OR';
						}
						$bookingpress_search_query_where .= " bookingpress_appointment_status ='{$status_value}'";
						$i++;
					}
					$bookingpress_search_query_where .= ' )';
				}
				$total_appointments = $wpdb->get_var( 'SELECT COUNT(bookingpress_appointment_booking_id) FROM ' . $tbl_bookingpress_appointment_bookings . ' ' . $bookingpress_search_query_where ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
				if ( $total_appointments > 0 ) {
					$response['variant'] = 'warnning';
					$response['title']   = esc_html__( 'Warning', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'one or more appointments are already booked this time duration with this staffmember still you want to add the Special day', 'bookingpress-appointment-booking' );
				} else {
					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'success', 'bookingpress-appointment-booking' );
					$response['msg']     = '';
				}
			}
			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_timesheet_delete_staffmember_special_day_func() {
			global $wpdb,$tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_staffmembers_special_day_breaks;
			$response            = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'timesheet_delete_staffmember_special_days', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			
			if ( ! empty( $_POST['delete_id'] ) ) { // phpcs:ignore
				$bookingpress_delete_id = ! empty( $_POST['delete_id'] ) ? intval( $_POST['delete_id'] ) : 0; // phpcs:ignore

				$wpdb->delete( $tbl_bookingpress_staffmembers_special_day, array( 'bookingpress_staffmember_special_day_id' => $bookingpress_delete_id ) );

				$wpdb->delete( $tbl_bookingpress_staffmembers_special_day_breaks, array( 'bookingpress_special_day_id' => $bookingpress_delete_id ) );

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Special Days Deleted Successfully', 'bookingpress-appointment-booking' );
			}

			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_timesheet_get_special_days_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_staffmembers_special_day_breaks,$bookingpress_global_options,$bookingpress_pro_staff_members;
			$response                = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'timesheet_get_special_days', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			$response['specialdays'] = array();
		
			// Find bookingpress staffmember id
			$bookingpress_current_user_id  = get_current_user_id();
			$bookingpress_staffmember_data = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers . ' WHERE bookingpress_wpuser_id = %d', $bookingpress_current_user_id ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
			$bookingpress_staffmember_id   = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ? intval( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) : 0;

			$bookingpress_staffmember_specialdays_details = array();

			if ( ! empty( $bookingpress_staffmember_id ) && ! empty( $_REQUEST['action'] ) && sanitize_text_field( $_REQUEST['action'] == 'bookingpress_timesheet_get_special_days' ) ) {
				$bookingpress_global_settings  = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_date_format      = $bookingpress_global_settings['wp_default_date_format'];
				$bookingpress_time_format      = $bookingpress_global_settings['wp_default_time_format'];
				$bookingpress_special_day      = array();
				$bookingpress_special_day_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers_special_day . ' WHERE bookingpress_staffmember_id = %d ', $bookingpress_staffmember_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day is a table name. false alarm
				if ( ! empty( $bookingpress_special_day_data ) ) {
					foreach ( $bookingpress_special_day_data as $special_day_key => $special_day ) {
						$special_day_arr        = $special_days_breaks = array();
						$special_day_start_date = ! empty( $special_day['bookingpress_special_day_start_date'] ) ? sanitize_text_field( $special_day['bookingpress_special_day_start_date'] ) : '';						
						$special_day_end_date   = ! empty( $special_day['bookingpress_special_day_end_date'] ) ? sanitize_text_field( $special_day['bookingpress_special_day_end_date'] ) : '';
						$special_day_service_id = ! empty( $special_day['bookingpress_special_day_service_id'] ) ? explode( ',', $special_day['bookingpress_special_day_service_id'] ) : '';

						$special_day_id                                      = ! empty( $special_day['bookingpress_staffmember_special_day_id'] ) ? intval( $special_day['bookingpress_staffmember_special_day_id'] ) : '';
						$special_day_arr['id']                               = $special_day_id;
						$special_day_arr['special_day_start_date']           =  date( 'Y-m-d', strtotime( $special_day_start_date ) );$special_day_start_date;
						$special_day_arr['special_day_formatted_start_date'] = date( $bookingpress_date_format, strtotime( sanitize_text_field( $special_day_start_date ) ) );
						$special_day_arr['special_day_end_date']             = date( 'Y-m-d', strtotime( $special_day_end_date ) );
						$special_day_arr['special_day_formatted_end_date']   = date( $bookingpress_date_format, strtotime( $special_day_end_date ) );
						$special_day_arr['start_time']                       = sanitize_text_field( $special_day['bookingpress_special_day_start_time'] );

						$special_day_arr['formatted_start_time'] = date( $bookingpress_time_format, strtotime( sanitize_text_field( $special_day['bookingpress_special_day_start_time'] ) ) );
						$special_day_arr['end_time']             = sanitize_text_field( $special_day['bookingpress_special_day_end_time'] );

						$special_day_arr['formatted_end_time']  = date( $bookingpress_time_format, strtotime( sanitize_text_field( $special_day['bookingpress_special_day_end_time'] ) ) )." ".($special_day['bookingpress_special_day_end_time'] == "24:00:00" ? esc_html__('Next Day', 'bookingpress-appointment-booking') : '' );
						$special_day_arr['special_day_service'] = $special_day_service_id;

						// Fetch all breaks associated with special day
						$bookingpress_special_days_break = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers_special_day_breaks . ' WHERE bookingpress_special_day_id = %d ', $special_day_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day_breaks is a table name. false alarm

						if ( ! empty( $bookingpress_special_days_break ) && is_array( $bookingpress_special_days_break ) ) {
							foreach ( $bookingpress_special_days_break as $k3 => $v3 ) {
								$break_start_time                                = ! empty( $v3['bookingpress_special_day_break_start_time'] ) ? sanitize_text_field( $v3['bookingpress_special_day_break_start_time'] ) : '';
								$break_end_time                                  = ! empty( $v3['bookingpress_special_day_break_end_time'] ) ? sanitize_text_field( $v3['bookingpress_special_day_break_end_time'] ) : '';
								$special_days_break_data                         = array();
								$special_days_break_data['id']                   = intval( $v3['bookingpress_staffmember_special_day_break_id'] );
								$special_days_break_data['start_time']           = $break_start_time;
								$special_days_break_data['end_time']             = $break_end_time;
								$special_days_break_data['formatted_start_time'] = date( $bookingpress_time_format, strtotime( $break_start_time ) );
								$special_days_break_data['formatted_end_time']   = date( $bookingpress_time_format, strtotime( $break_end_time ) );
								$special_days_breaks[]                           = $special_days_break_data;
							}
						}
						$special_day_arr['special_day_workhour'] = $special_days_breaks;
						$bookingpress_special_day[]              = $special_day_arr;
					}
				}

				$response['msg']                       = esc_html__( 'Staff member Special Day data retrieved successfully.', 'bookingpress-appointment-booking' );
				$response['special_day_data']          = $bookingpress_special_day;
				$response['disabled_special_day_data'] = '';
				$response['variant']                   = 'success';
				$response['title']                     = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			}

			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_timesheet_add_staffmember_special_day_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_staffmembers_special_day_breaks;

			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'timesheet_add_staffmember_special_days', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			
			// Find bookingpress staffmember id
			$bookingpress_current_user_id  = get_current_user_id();
			$bookingpress_staffmember_data = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers . ' WHERE bookingpress_wpuser_id = %d', $bookingpress_current_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
			$bookingpress_staffmember_id   = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ? intval( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) : 0;

			$bookingpress_specialday_id = ! empty( $_POST['specialday_updateid'] ) ? intval( $_POST['specialday_updateid'] ) : 0; // phpcs:ignore

			if ( ! empty( $_POST['specialdays_details'] ) && ! empty( $bookingpress_staffmember_id ) ) { // phpcs:ignore
				$special_day = ! empty( $_REQUEST['specialdays_details'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['specialdays_details']) : array();  //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST['specialdays_details'] has already been sanitized.

				$bookingpress_special_day_start_date = ! empty( $special_day['special_day_date'][0] ) ?  $special_day['special_day_date'][0]  : '';
				$bookingpress_special_day_end_date   = ! empty( $special_day['special_day_date'][1] ) ?  $special_day['special_day_date'][1]  : '';
				$special_day_selected_service        = ( ! empty( $special_day['special_day_service'] ) && is_array( $special_day['special_day_service'] ) ) ? implode( ',', $special_day['special_day_service'] ) : '';
				$special_day_workhour_arr            = ! empty( $special_day['special_day_workhour'] ) ? $special_day['special_day_workhour'] : array();

				$start_time = ! empty( $special_day['start_time'] ) ?  $special_day['start_time']  : '';
				$end_time   = ! empty( $special_day['end_time'] ) ?  $special_day['end_time']  : '';

				$args_special_day = array(
					'bookingpress_staffmember_id'         => $bookingpress_staffmember_id,
					'bookingpress_special_day_start_date' => $bookingpress_special_day_start_date,
					'bookingpress_special_day_end_date'   => $bookingpress_special_day_end_date,
					'bookingpress_special_day_start_time' => $start_time,
					'bookingpress_special_day_end_time'   => $end_time,
					'bookingpress_special_day_service_id' => $special_day_selected_service,
					'bookingpress_created_at'             => current_time( 'mysql' ),
				);

				if ( ! empty( $bookingpress_specialday_id ) ) {
					$wpdb->update( $tbl_bookingpress_staffmembers_special_day, $args_special_day, array( 'bookingpress_staffmember_special_day_id' => $bookingpress_specialday_id ) );
					$wpdb->delete( $tbl_bookingpress_staffmembers_special_day_breaks, array( 'bookingpress_special_day_id' => $bookingpress_specialday_id ) );
					$bookingpress_special_day_reference_id = $bookingpress_specialday_id;
				} else {
					$wpdb->insert( $tbl_bookingpress_staffmembers_special_day, $args_special_day );
					$bookingpress_special_day_reference_id = $wpdb->insert_id;
				}

				if ( ! empty( $special_day_workhour_arr ) ) {
					foreach ( $special_day_workhour_arr as $special_day_workhour_key => $special_day_workhour_val ) {
						$start_time         = ! empty( $special_day_workhour_val['start_time'] ) ?  $special_day_workhour_val['start_time']  : '';
						$end_time           = ! empty( $special_day_workhour_val['end_time'] ) ?  $special_day_workhour_val['end_time']  : '';
						$args_extra_details = array(
							'bookingpress_special_day_id' => intval( $bookingpress_special_day_reference_id ),
							'bookingpress_special_day_break_start_time' => $start_time,
							'bookingpress_special_day_break_end_time' => $end_time,
							'bookingpress_created_at'     => current_time( 'mysql' ),
						);

						if ( empty( $bookingpress_update_id ) ) {
							$wpdb->insert( $tbl_bookingpress_staffmembers_special_day_breaks, $args_extra_details );
						} else {
							$wpdb->update( $tbl_bookingpress_staffmembers_special_day_breaks, $args_extra_details, array( 'bookingpress_special_day__id' => $bookingpress_specialday_id ) );
						}
					}
				}

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Specialdays saved successfully', 'bookingpress-appointment-booking' );
			}
			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_delete_daysoff_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_daysoff;
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'delete_timesheet_daysoff', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			if ( ! empty( $_POST['delete_id'] ) ) { // phpcs:ignore
				$bookingpress_delete_id = ! empty( $_POST['delete_id'] ) ? intval( $_POST['delete_id'] ) : 0; // phpcs:ignore
				if ( ! empty( $bookingpress_delete_id ) ) {
					$wpdb->delete( $tbl_bookingpress_staffmembers_daysoff, array( 'bookingpress_staffmember_daysoff_id' => $bookingpress_delete_id ) );
					$wpdb->delete( $tbl_bookingpress_staffmembers_daysoff, array( 'bookingpress_staffmember_daysoff_parent' => $bookingpress_delete_id ) );
					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Days-off deleted successfully', 'bookingpress-appointment-booking' );
				}
			}

			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_get_timesheet_daysoff_details_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_daysoff,$bookingpress_global_options, $bookingpress_pro_staff_members,$BookingPressPro;
			$response            = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_timesheet_daysoff_details', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			
			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_date_format        = $bookingpress_global_options_arr['wp_default_date_format'];

			$bookingpress_staffmember_daysoff_details = array();

			// Find bookingpress staffmember id
			$bookingpress_current_user_id  = get_current_user_id();
			$bookingpress_staffmember_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $bookingpress_current_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
			$bookingpress_staffmember_id   = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ? intval( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) : 0;

			if ( ! empty( $bookingpress_staffmember_id ) ) {

				// Get days off details
				$bookingpress_staffmember_daysoff_details = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_daysoff} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_daysoff_parent = %d", $bookingpress_staffmember_id,0), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_daysoff is a table name. false alarm

				foreach ( $bookingpress_staffmember_daysoff_details as $key => $value ) {
					$bookingpress_staffmember_daysoff_details[ $key ]['bookingpress_staffmember_daysoff_formated_date'] = date( $bookingpress_date_format, strtotime( $value['bookingpress_staffmember_daysoff_date'] ) );
					$bookingpress_staffmember_daysoff_details[ $key ]['bookingpress_staffmember_daysoff_date'] = $value['bookingpress_staffmember_daysoff_date'];
					$bookingpress_staffmember_daysoff_details[ $key ]['bookingpress_staffmember_daysoff_name'] = stripslashes_deep($value['bookingpress_staffmember_daysoff_name']);

					$dayoff_label = esc_html__( 'Once Off', 'bookingpress-appointment-booking' );
					if( true == $value['bookingpress_staffmember_daysoff_repeat'] ){
						$repeat_frequency = $value['bookingpress_staffmember_daysoff_repeat_frequency'];
						$repeat_frequency_type = $value['bookingpress_staffmember_daysoff_repeat_frequency_type'];
						$repeat_duration = $value['bookingpress_staffmember_daysoff_repeat_duration'];
						$repeat_times = $value['bookingpress_staffmember_daysoff_repeat_times'];
						$repeat_date = $value['bookingpress_staffmember_daysoff_repeat_date'];
						$dayoff_label = $BookingPressPro->bookingpress_retrieve_daysoff_repeat_label( $repeat_duration, $repeat_frequency, $repeat_frequency_type, $repeat_times, $repeat_date );
					}

					$bookingpress_staffmember_daysoff_details[ $key ]['dayoff_repeat_label'] = $dayoff_label;
					
				}
			}

			$response['variant']         = 'success';
			$response['title']           = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['msg']             = esc_html__( 'Daysoff detailed retrieved successfully', 'bookingpress-appointment-booking' );
			$response['daysoff_details'] = $bookingpress_staffmember_daysoff_details;

			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_timesheet_add_days_off_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_daysoff;
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'timeslot_add_daysoff', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			if ( ! empty( $_POST['daysoff_details'] ) ) { // phpcs:ignore
				$bookingpress_dayoff_name   = ! empty( $_POST['daysoff_details']['dayoff_name'] ) ? sanitize_text_field( $_POST['daysoff_details']['dayoff_name'] ) : ''; // phpcs:ignore
				$bookingpress_dayoff_date   = ! empty( $_POST['daysoff_details']['dayoff_date'] ) ? sanitize_text_field( $_POST['daysoff_details']['dayoff_date'] ) : ''; // phpcs:ignore
				$bookingpress_dayoff_enddate  = ! empty( $_POST['daysoff_details']['dayoff_date_end'] ) ? sanitize_text_field( $_POST['daysoff_details']['dayoff_date_end'] ) : ''; // phpcs:ignore
				$bookingpress_dayoff_repeat = ( isset( $_POST['daysoff_details']['dayoff_repeat'] ) && ( $_POST['daysoff_details']['dayoff_repeat'] == 'true' ) ) ? 1 : 0; // phpcs:ignore

				$daysoff_repeat_frequency = !empty( $_POST['daysoff_details']['dayoff_repeat_frequency'] ) ? intval( $_POST['daysoff_details']['dayoff_repeat_frequency'] ) : 1; //phpcs:ignore
				$daysoff_repeat_frequency_type = !empty( $_POST['daysoff_details']['dayoff_repeat_freq_type'] ) ? sanitize_text_field( $_POST['daysoff_details']['dayoff_repeat_freq_type'] ) : 'year'; //phpcs:ignore
				$daysoff_repeat_duration = !empty( $_POST['daysoff_details']['dayoff_repeat_duration'] ) ? sanitize_text_field( $_POST['daysoff_details']['dayoff_repeat_duration'] ) : 'forever'; //phpcs:ignore
				$daysoff_repeat_times	 = !empty( $_POST['daysoff_details']['dayoff_repeat_times'] ) ? intval( $_POST['daysoff_details']['dayoff_repeat_times'] ) : 1; //phpcs:ignore
				$daysoff_repeat_date	 = !empty( $_POST['daysoff_details']['dayoff_repeat_date'] ) ? sanitize_text_field( $_POST['daysoff_details']['dayoff_repeat_date'] ) : date('Y-m-d', strtotime( '+1 year' ) ); //phpcs:ignore

				$daysoff_repeat_date = apply_filters( 'bookingpress_change_repeat_date_timezone_to_wp', $daysoff_repeat_date );

				if(empty($bookingpress_dayoff_enddate) || $bookingpress_dayoff_enddate == 'null'){
					$bookingpress_dayoff_enddate = $bookingpress_dayoff_date;
				}

                $bookingpress_child_holiday_dates = array();
                if($bookingpress_dayoff_date != $bookingpress_dayoff_enddate){  
					$bookingpress_dayoff_date = date( 'Y-m-d', strtotime( $bookingpress_dayoff_date ) );
					$bookingpress_dayoff_enddate = date( 'Y-m-d', strtotime( $bookingpress_dayoff_enddate ) );

                    $startDate = strtotime($bookingpress_dayoff_date)+86400;
                    $endDate = strtotime($bookingpress_dayoff_enddate);                 
                    for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
                        $date = date('Y-m-d', $currentDate);
                        $bookingpress_child_holiday_dates[] = $date;
                    }                    
                } 				

				// Find bookingpress staffmember id
				$bookingpress_current_user_id  = get_current_user_id();
				$bookingpress_staffmember_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $bookingpress_current_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
				$bookingpress_staffmember_id   = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ? intval( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) : 0;

				$bookingpress_insert_data = array(
					'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
					'bookingpress_staffmember_daysoff_name' => $bookingpress_dayoff_name,
					'bookingpress_staffmember_daysoff_date' => date( 'Y-m-d', strtotime( $bookingpress_dayoff_date ) ),
					'bookingpress_staffmember_daysoff_enddate' => date( 'Y-m-d', strtotime( $bookingpress_dayoff_enddate ) ),
					'bookingpress_staffmember_daysoff_repeat' => $bookingpress_dayoff_repeat,
					'bookingpress_staffmember_daysoff_repeat_frequency' => $daysoff_repeat_frequency,
					'bookingpress_staffmember_daysoff_repeat_frequency_type' => $daysoff_repeat_frequency_type,
					'bookingpress_staffmember_daysoff_repeat_duration' => $daysoff_repeat_duration,
					'bookingpress_staffmember_daysoff_repeat_times' => $daysoff_repeat_times,
					'bookingpress_staffmember_daysoff_repeat_date' => $daysoff_repeat_date,
				);

				$bookingpress_update_id = ! empty( $_POST['update_id'] ) ? intval( $_POST['update_id'] ) : 0; // phpcs:ignore

				if ( empty( $bookingpress_update_id ) ) {
					$wpdb->insert( $tbl_bookingpress_staffmembers_daysoff, $bookingpress_insert_data );
					if(!empty($bookingpress_child_holiday_dates)){
						$dayoff_parent_id = $wpdb->insert_id;
						foreach($bookingpress_child_holiday_dates as $holiday_date){

							$bookingpress_insert_data = array(
								'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
								'bookingpress_staffmember_daysoff_name' => $bookingpress_dayoff_name,
								'bookingpress_staffmember_daysoff_date' => $holiday_date,
								'bookingpress_staffmember_daysoff_enddate' => date( 'Y-m-d', strtotime( $bookingpress_dayoff_enddate ) ),
								'bookingpress_staffmember_daysoff_parent' => $dayoff_parent_id,
								'bookingpress_staffmember_daysoff_repeat' => $bookingpress_dayoff_repeat,
								'bookingpress_staffmember_daysoff_repeat_frequency' => $daysoff_repeat_frequency,
								'bookingpress_staffmember_daysoff_repeat_frequency_type' => $daysoff_repeat_frequency_type,
								'bookingpress_staffmember_daysoff_repeat_duration' => $daysoff_repeat_duration,
								'bookingpress_staffmember_daysoff_repeat_times' => $daysoff_repeat_times,
								'bookingpress_staffmember_daysoff_repeat_date' => $daysoff_repeat_date,
							);
							$wpdb->insert( $tbl_bookingpress_staffmembers_daysoff, $bookingpress_insert_data );

						}
					}

					$response['msg'] = esc_html__( 'Staff member holiday added successfully', 'bookingpress-appointment-booking' );
				} else {
					$wpdb->update( $tbl_bookingpress_staffmembers_daysoff, $bookingpress_insert_data, array( 'bookingpress_staffmember_daysoff_id' => $bookingpress_update_id ) );

					$dayoff_where_condition = array(
						'bookingpress_staffmember_daysoff_parent' => $bookingpress_update_id,
					);                        					          											
					$wpdb->delete($tbl_bookingpress_staffmembers_daysoff, $dayoff_where_condition);
					if(!empty($bookingpress_child_holiday_dates)){

						$dayoff_parent_id = $bookingpress_update_id;
						foreach($bookingpress_child_holiday_dates as $holiday_date){

							$bookingpress_insert_data = array(
								'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
								'bookingpress_staffmember_daysoff_name' => $bookingpress_dayoff_name,
								'bookingpress_staffmember_daysoff_date' => $holiday_date,
								'bookingpress_staffmember_daysoff_enddate' => date( 'Y-m-d', strtotime( $bookingpress_dayoff_enddate ) ),
								'bookingpress_staffmember_daysoff_parent' => $dayoff_parent_id,
								'bookingpress_staffmember_daysoff_repeat' => $bookingpress_dayoff_repeat,
								'bookingpress_staffmember_daysoff_repeat_frequency' => $daysoff_repeat_frequency,
								'bookingpress_staffmember_daysoff_repeat_frequency_type' => $daysoff_repeat_frequency_type,
								'bookingpress_staffmember_daysoff_repeat_duration' => $daysoff_repeat_duration,
								'bookingpress_staffmember_daysoff_repeat_times' => $daysoff_repeat_times,
								'bookingpress_staffmember_daysoff_repeat_date' => $daysoff_repeat_date,
							);
							$wpdb->insert( $tbl_bookingpress_staffmembers_daysoff, $bookingpress_insert_data );

						}
					}					

					$response['msg'] = esc_html__( 'Staff member holiday off updated successfully', 'bookingpress-appointment-booking' );
				}

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			}

			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_timesheet_dynamic_helper_vars_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
				var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
				ELEMENT.locale(lang)
			<?php
		}

		function bookingpress_timesheet_dynamic_vue_methods_func() {
			global $bookingpress_notification_duration;
			?>
			change_special_day_date(selected_value){
				const vm = this
				if(selected_value != null) {
					vm.staffmember_special_day_form.special_day_date[0] = vm.get_formatted_date(vm.staffmember_special_day_form.special_day_date[0])
					vm.staffmember_special_day_form.special_day_date[1] = vm.get_formatted_date(vm.staffmember_special_day_form.special_day_date[1])
				}
			},
			change_days_off_date(eventdata) {								
				
				var vm = this;
				vm.staffmember_dayoff_form.dayoff_date = this.get_formatted_date(eventdata[0]);
				vm.staffmember_dayoff_form.dayoff_date_end = this.get_formatted_date(eventdata[1]);								
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
			open_days_off_modal_func(currentElement){
				const vm = this
				vm.bookingpress_reset_daysoff_modal()
				vm.days_off_add_modal = true		
				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.days_off_modal_pos = (dialog_pos.top - 90)+'px'
				vm.days_off_modal_pos_right = '-'+(dialog_pos.right - 400)+'px';
				
				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#days_off_add_modal .el-dialog.bpa-dialog--days-off');
				}
			},
			bookingpress_reset_daysoff_modal() {
				const vm = this				
				vm.edit_staffmember_dayoff = '';
				vm.staffmember_dayoff_form.dayoff_date_end = '';
				vm.staffmember_dayoff_form.dayoff_date_range = '';
				vm.staffmember_dayoff_form.dayoff_name = '';
				vm.staffmember_dayoff_form.dayoff_date = '';
				vm.staffmember_dayoff_form.dayoff_repeat = '';
				vm.staffmember_dayoff_form.is_disabled = false;
				setTimeout(function(){
					vm.$refs['staffmember_dayoff_form'].clearValidate();
				},100); 
			},
			closeStaffmemberDayoff() {
				const vm = this;
				vm.bookingpress_reset_daysoff_modal()
				vm.days_off_add_modal = false;				
			},
			bookingpress_add_daysoff(staffmember_dayoff_form){
				const vm = this
				this.$refs[staffmember_dayoff_form].validate((valid) => {
					if (valid) {
						vm.staffmember_dayoff_form.is_disabled = true
						var is_exit = 0;
						if(vm.bookingpress_staffmembers_specialdays_details != '' ) {
							vm.bookingpress_staffmembers_specialdays_details.forEach(function(item, index, arr) {									
								


								if(vm.staffmember_dayoff_form.dayoff_date >= item.special_day_start_date && vm.staffmember_dayoff_form.dayoff_date <= item.special_day_end_date){
									vm.staffmember_dayoff_form.is_disabled = false
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_exit = 1;
								}
								if(vm.staffmember_dayoff_form.dayoff_date_end >= item.special_day_start_date && vm.staffmember_dayoff_form.dayoff_date_end <= item.special_day_end_date){
									vm.staffmember_dayoff_form.is_disabled = false
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_exit = 1;
								}
								if(item.special_day_start_date >= vm.staffmember_dayoff_form.dayoff_date && item.special_day_start_date <= vm.staffmember_dayoff_form.dayoff_date_end){
									vm.staffmember_dayoff_form.is_disabled = false
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_exit = 1;																				
								}
								if(item.special_day_end_date >= vm.staffmember_dayoff_form.dayoff_date && item.special_day_end_date <= vm.staffmember_dayoff_form.dayoff_date_end){
									vm.staffmember_dayoff_form.is_disabled = false
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_exit = 1;									
								}

							});
						}
						vm.bookingpress_staffmembers_daysoff_details.forEach(function(item,index,arr) {						

							if(vm.staffmember_dayoff_form.dayoff_date >= item.bookingpress_staffmember_daysoff_date && vm.staffmember_dayoff_form.dayoff_date <= item.bookingpress_staffmember_daysoff_enddate && vm.edit_staffmember_dayoff != item.bookingpress_staffmember_daysoff_id){
								vm.staffmember_dayoff_form.is_disabled = false
								vm.$notify({
									title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
									message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,									
								});
								is_exit = 1;
							}
							if(vm.staffmember_dayoff_form.dayoff_date_end >= item.bookingpress_staffmember_daysoff_date && vm.staffmember_dayoff_form.dayoff_date_end <= item.bookingpress_staffmember_daysoff_enddate && vm.edit_staffmember_dayoff != item.bookingpress_staffmember_daysoff_id){
								vm.staffmember_dayoff_form.is_disabled = false
								vm.$notify({
									title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
									message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,									
								});
								is_exit = 1;
							}
							if(item.bookingpress_staffmember_daysoff_date >= vm.staffmember_dayoff_form.dayoff_date && item.bookingpress_staffmember_daysoff_date <= vm.staffmember_dayoff_form.dayoff_date_end && vm.edit_staffmember_dayoff != item.bookingpress_staffmember_daysoff_id){
								vm.staffmember_dayoff_form.is_disabled = false
								vm.$notify({
									title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
									message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,									
								});
								is_exit = 1;
							}
							if(item.bookingpress_staffmember_daysoff_enddate >= vm.staffmember_dayoff_form.dayoff_date && item.bookingpress_staffmember_daysoff_enddate <= vm.staffmember_dayoff_form.dayoff_date_end && vm.edit_staffmember_dayoff != item.bookingpress_staffmember_daysoff_id){
								vm.staffmember_dayoff_form.is_disabled = false
								vm.$notify({
									title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
									message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,									
								});
								is_exit = 1;
							}
						});

						let daysoff_formdata = vm.staffmember_dayoff_form;
						
						if( 'undefined' != typeof daysoff_formdata.dayoff_repeat && true == daysoff_formdata.dayoff_repeat ){
							let repeat_frequency = daysoff_formdata.dayoff_repeat_frequency;
							let repeat_freq_type = daysoff_formdata.dayoff_repeat_freq_type;
							let daysoff_start_date = daysoff_formdata.dayoff_date;
							let daysoff_end_date = daysoff_formdata.dayoff_date_end;

							/** block if multiple days are selected & frequency set to days */

							let d1 = new Date( daysoff_start_date );
							let d2 = new Date( daysoff_end_date );

							let diff_in_time = d2.getTime() - d1.getTime();
							let diff_in_days = ( Math.round( diff_in_time / ( 1000 * 3600 * 24 ) ) ) + 1; /** +1 will includes the end date as well so we get the correct duration  */
							let diff_in_months = ( Math.round( diff_in_days / 30.44 ) % 12 );
			
							if( 'day' == repeat_freq_type && diff_in_days > repeat_frequency ){
								is_exit = 1;
								vm.staffmember_dayoff_form.is_disabled = false
								vm.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Holiday duration must be shorter than the repeat frequency', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
								});
							} else if( 'week' == repeat_freq_type && diff_in_days > ( repeat_frequency * 7 ) ){
								is_exit = 1;
								vm.staffmember_dayoff_form.is_disabled = false
								vm.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Holiday duration must be shorter than the repeat frequency', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
								});
							} else if( 'month' == repeat_freq_type && diff_in_months > repeat_frequency ){
								is_exit = 1;
								vm.staffmember_dayoff_form.is_disabled = false
								vm.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Holiday duration must be shorter than the repeat frequency', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
								});
							}
						}
						
						if(is_exit == 0) {
							var postdata = [];
							postdata.action = 'bookingpress_validate_staffmember_daysoff'							
							postdata.selected_date_range= vm.staffmember_dayoff_form.dayoff_date;
							postdata.selected_date_range_end = vm.staffmember_dayoff_form.dayoff_date_end;
							postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
							.then(function(response){
								if(response.data.variant != 'undefined' && response.data.variant == 'warnning') {														
									vm.staffmember_dayoff_form.is_disabled = false
									vm.$confirm(response.data.msg, 'Warning', {
									confirmButtonText: '<?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?>',
									cancelButtonText: '<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>',
									type: 'warning'
									}).then(() => {
										vm.bookingpress_save_staffmember_daysoff()
									});				
								}else if(response.data.variant != 'undefined' && response.data.variant  == 'success') {
									vm.bookingpress_save_staffmember_daysoff();
									
								}
							}).catch(function(error){
								console.log(error);
								vm.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
									type: 'error_notification',
								});
							});
						}
					} else {
						return false;
					}
				});        
			},
			bookingpress_save_staffmember_daysoff(){
				const vm = this;
				var postData = { action:'bookingpress_timesheet_add_days_off', daysoff_details: vm.staffmember_dayoff_form, update_id: vm.edit_staffmember_dayoff, _wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
					});
					vm.closeStaffmemberDayoff()
					vm.bookingpress_get_all_daysoff_details()
					
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				});
			},			
			bookingpress_get_all_daysoff_details(){
				const vm = this
				var postData = { action:'bookingpress_timesheet_get_days_off', _wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm.staffmember_dayoff_form.is_disabled = false
					if(response.data.variant == 'success'){
						vm.bookingpress_staffmembers_daysoff_details = response.data.daysoff_details
					}
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				});
			},
			bookingpress_delete_daysoff(delete_id){
				const vm = this
				var postData = { action:'bookingpress_timesheet_delete_days_off', delete_id: delete_id , _wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					if(response.data.variant == 'success'){
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
						});
						vm.bookingpress_get_all_daysoff_details()
					}
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				});
			},
			bookingpress_edit_daysoff(edit_details, currentElement){
				const vm = this
				vm.bookingpress_reset_daysoff_modal()
				vm.edit_staffmember_dayoff = edit_details.bookingpress_staffmember_daysoff_id;
				vm.staffmember_dayoff_form.dayoff_name = edit_details.bookingpress_staffmember_daysoff_name;
				vm.staffmember_dayoff_form.dayoff_date = edit_details.bookingpress_staffmember_daysoff_date;
				vm.staffmember_dayoff_form.dayoff_date_end = edit_details.bookingpress_staffmember_daysoff_enddate;
				vm.staffmember_dayoff_form.dayoff_date_range = [edit_details.bookingpress_staffmember_daysoff_date,edit_details.bookingpress_staffmember_daysoff_enddate];				
				vm.staffmember_dayoff_form.dayoff_repeat = (edit_details.bookingpress_staffmember_daysoff_repeat == "0") ? false : true ;

				vm.staffmember_dayoff_form.dayoff_repeat_frequency = edit_details.bookingpress_staffmember_daysoff_repeat_frequency;
				vm.staffmember_dayoff_form.dayoff_repeat_freq_type = edit_details.bookingpress_staffmember_daysoff_repeat_frequency_type;
				vm.staffmember_dayoff_form.dayoff_repeat_duration = edit_details.bookingpress_staffmember_daysoff_repeat_duration;
				vm.staffmember_dayoff_form.dayoff_repeate_times = edit_details.bookingpress_staffmember_daysoff_repeat_times;
				vm.staffmember_dayoff_form.dayoff_repeate_date = edit_details.bookingpress_staffmember_daysoff_repeat_date;

				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.days_off_modal_pos = (dialog_pos.top - 110)+'px'
				vm.days_off_modal_pos_right = '-'+(dialog_pos.right - 510)+'px';
				vm.days_off_add_modal = true;
				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#days_off_add_modal .el-dialog.bpa-dialog--days-off');
				}
			},
			close_special_days_func(){
				const vm = this
				vm.bookingpress_reset_specialdays_modal()
				vm.special_days_add_modal = false
			},
			open_special_days_func(currentElement){
				const vm = this                 				               
				vm.bookingpress_reset_specialdays_modal()
				vm.special_days_add_modal = true
				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.special_days_modal_pos = (dialog_pos.top - 90)+'px'
				vm.special_days_modal_pos_right = '-'+(dialog_pos.right - 400)+'px';
				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#special_days_add_modal .el-dialog.bpa-dialog--special-days');
				}
			},
			bookingpress_reset_specialdays_modal(){
				const vm = this				
				vm.edit_staffmember_special_day = ''
				vm.staffmember_special_day_form.special_day_date = [];                 
				vm.staffmember_special_day_form.start_time = '';
				vm.staffmember_special_day_form.end_time = '';
				vm.staffmember_special_day_form.special_day_service = '';
				vm.staffmember_special_day_form.is_disabled = false;
				vm.staffmember_special_day_form.special_day_workhour = [];
				setTimeout(function(){
					vm.$refs['staffmember_special_day_form'].clearValidate();
				},100);
			},
			bookingpress_add_special_day_period(){
				const vm = this;
				var ilength = parseInt(vm.staffmember_special_day_form.special_day_workhour.length) + 1;
				let WorkhourData = {};
				Object.assign(WorkhourData, {id: ilength})
				Object.assign(WorkhourData, {start_time: ''})
				Object.assign(WorkhourData, {end_time: ''})					
				vm.staffmember_special_day_form.special_day_workhour.push(WorkhourData)
			},
			bookingpress_remove_special_day_period(id){
				const vm = this
				vm.staffmember_special_day_form.special_day_workhour.forEach(function(item, index, arr)
				{
					if(id == item.id ){
						vm.staffmember_special_day_form.special_day_workhour.splice(index,1);
					}	
				})
			},
			bookingpress_get_staffmember_specialdays(){
				const vm = this
				var postData = { action:'bookingpress_timesheet_get_special_days', _wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm.bookingpress_get_staffmember_specialdays.is_disabled = false
					if(response.data.variant == 'success'){
						vm.bookingpress_staffmembers_specialdays_details = response.data.special_day_data            						
					}
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				});
			},
			addStaffmemberSpecialday(staffmember_special_day_form) {
				const vm = this;
				if( "undefined" == typeof vm.display_staff_working_hours || vm.display_staff_working_hours == true ){	
					this.$refs[staffmember_special_day_form].validate((valid) => {
						if (valid) {
							vm.staffmember_special_day_form.is_disabled = true
							var is_exit = 0;
							if(vm.bookingpress_staffmembers_daysoff_details != '') {
								vm.bookingpress_staffmembers_daysoff_details.forEach(function(item, index, arr)
								{	
																		

									if (item.bookingpress_staffmember_daysoff_date >= vm.staffmember_special_day_form.special_day_date[0] && item.bookingpress_staffmember_daysoff_date <= vm.staffmember_special_day_form.special_day_date[1] ) {
										vm.staffmember_special_day_form.is_disabled = false
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
										is_exit = 1;
									}
									if (item.bookingpress_staffmember_daysoff_enddate >= vm.staffmember_special_day_form.special_day_date[0] && item.bookingpress_staffmember_daysoff_enddate <= vm.staffmember_special_day_form.special_day_date[1] ) {									
										vm.staffmember_special_day_form.is_disabled = false
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
										is_exit = 1;
									}
									if (vm.staffmember_special_day_form.special_day_date[0] >= item.bookingpress_staffmember_daysoff_date && vm.staffmember_special_day_form.special_day_date[0] <= item.bookingpress_staffmember_daysoff_enddate) {
										vm.staffmember_special_day_form.is_disabled = false
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
										is_exit = 1;
									}
									if (vm.staffmember_special_day_form.special_day_date[1] >= item.bookingpress_staffmember_daysoff_date && vm.staffmember_special_day_form.special_day_date[1] <= item.bookingpress_staffmember_daysoff_enddate) {
										vm.staffmember_special_day_form.is_disabled = false
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
										is_exit = 1;
									}

								});
							}
							if(vm.staffmember_special_day_form.special_day_workhour != undefined && vm.staffmember_special_day_form.special_day_workhour != '' ) {
								vm.staffmember_special_day_form.special_day_workhour.forEach(function(item, index, arr){	                            
									if(is_exit == 0 && (item.start_time == '' || item.end_time == '')) {
										vm.staffmember_special_day_form.is_disabled = false
										is_exit = 1;
										vm.$notify({
											title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
											message: '<?php esc_html_e( 'Please Enter Start Time and End Time', 'bookingpress-appointment-booking' ); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
										});                                
									}
								});
							}	
							if(vm.bookingpress_staffmembers_specialdays_details != undefined && vm.bookingpress_staffmembers_specialdays_details != '' ) {
								vm.bookingpress_staffmembers_specialdays_details.forEach(function(item, index, arr) {								
									if((vm.staffmember_special_day_form.special_day_date[0] == item.special_day_start_date || vm.staffmember_special_day_form.special_day_date[0] == item.special_day_end_date || ( vm.staffmember_special_day_form.special_day_date[0] >= item.special_day_start_date && vm.staffmember_special_day_form.special_day_date[0] <= item.special_day_end_date ) || vm.staffmember_special_day_form.special_day_date[1] == item.special_day_end_date || vm.staffmember_special_day_form.special_day_date[1] == item.special_day_start_date || (vm.staffmember_special_day_form.special_day_date[1] >= item.special_day_start_date && vm.staffmember_special_day_form.special_day_date[1] <= item.special_day_end_date) || (vm.staffmember_special_day_form.special_day_date[0] <= item.special_day_start_date && vm.staffmember_special_day_form.special_day_date[1] >= item.special_day_end_date)) && vm.edit_staffmember_special_day != item.id  ) {
										vm.staffmember_special_day_form.is_disabled = false
										is_exit = 1;
										vm.$notify({
											title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
											message: '<?php esc_html_e( 'Special days already exists', 'bookingpress-appointment-booking' ); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
										});								
									}							
								});	
							}
							if(is_exit == 0) {
								var postdata = [];
								postdata.action = 'bookingpress_validate_staff_member_special_day'                                          
								postdata.selected_date_range= vm.staffmember_special_day_form.special_day_date;
								postdata.special_day_workhour= vm.staffmember_special_day_form.special_day_workhour;                                                       
								postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
								axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
								.then(function(response){
									if(response.data.variant != 'undefined' && response.data.variant == 'warnning') {                                                               
										vm.staffmember_special_day_form.is_disabled = false
										vm.$confirm(response.data.msg, 'Warning', {
										confirmButtonText:  '<?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?>',
										cancelButtonText:  '<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>',
										type: 'warning'
										}).then(() => {
											vm.bookingpress_add_staffmember_special_days()
										});             
									}else if(response.data.variant != 'undefined' && response.data.variant  == 'success') {
										vm.bookingpress_add_staffmember_special_days();   
									}
								}).catch(function(error){
									console.log(error);
									vm.$notify({
										title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
										message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
										type: 'error_notification',
									});
								});                               	
							}    
						} else {
							return false;
						}
					});
				} else {
					let update_from_panel = true;
					<?php do_action( 'bookingpress_save_staff_external_special_data' ); ?>
				}
			},
			bookingpress_add_staffmember_special_days() {
				const vm = this;
				var postdata = [];
				postdata.action = 'bookingpress_timesheet_add_staffmember_special_day'
				postdata.specialdays_details = vm.staffmember_special_day_form
				postdata.specialday_updateid = vm.edit_staffmember_special_day
				postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
				.then(function(response){
					vm.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
					});
					vm.bookingpress_get_staffmember_specialdays()
					vm.close_special_days_func()
				}).catch(function(error){
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error_notification',
					});
				});	

			},
			bookingpress_delete_special_daysoff(delete_id){
				const vm = this
				var postdata = [];
				postdata.action = 'bookingpress_timesheet_delete_staffmember_special_day'
				postdata.delete_id = delete_id
				postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
				.then(function(response){
					vm.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
					});
					vm.bookingpress_get_staffmember_specialdays()
				}).catch(function(error){
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error_notification',
					});
				});
			},
			show_edit_special_day_div(special_day_id, currentElement) {				
				var vm = this
				vm.bookingpress_reset_specialdays_modal()
				vm.bookingpress_staffmembers_specialdays_details.forEach(function(item, index, arr)
				{
					if (item.id == special_day_id) {
						vm.staffmember_special_day_form.special_day_date = [item.special_day_start_date,item.special_day_end_date]
						vm.staffmember_special_day_form.start_time = item.start_time
						vm.staffmember_special_day_form.end_time = item.end_time
						vm.staffmember_special_day_form.special_day_service = item.special_day_service							
						vm.staffmember_special_day_form.special_day_workhour = item.special_day_workhour
					}
					vm.edit_staffmember_special_day = special_day_id;
				})
				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.special_days_modal_pos = (dialog_pos.top - 100)+'px'
				vm.special_days_modal_pos_right = '-'+(dialog_pos.right - 550)+'px';
				vm.special_days_add_modal = true

				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#special_days_add_modal .el-dialog.bpa-dialog--special-days');
				}
			},
			bookingpress_set_workhour_value(worktime,work_hour_day) {
				const vm = this				
				if(vm.workhours_timings[work_hour_day].end_time == 'Off') {                    
					vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
						if(currentValue.day_name == work_hour_day) {
							currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
								if(currentValue2.start_time == worktime) {
									vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
								}
							});
						}
					});                
				} else if(worktime > vm.workhours_timings[work_hour_day].end_time ) {
					vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
						if(currentValue.day_name == work_hour_day) {                       
							currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
								if(currentValue2.start_time == worktime) {
									vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
								}
							});
						}
					});
				} else if(worktime != 'off' && vm.workhours_timings[work_hour_day].end_time == undefined) {
					vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
						if(currentValue.day_name == work_hour_day) {                       
							currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
								if(currentValue2.start_time == worktime) {
									vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
								}
							});
						}
					});
				}
			},
			bookingpress_get_default_workhours(){					
				const vm = this
				var postdata = [];
				postdata.action = 'bookingpress_get_default_work_hours_details';
				postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
				.then(function(response) {			
					vm.work_hours_days_arr = response.data.data
					response.data.data.forEach(function(currentValue, index, arr){
						vm.selected_break_timings[currentValue.day_name] = currentValue.break_times							
					});
					vm.workhours_timings = response.data.selected_workhours
					vm.default_break_timings = response.data.default_break_times
					if(vm.bookingpress_configure_specific_workhour) {
						vm.bookingpress_get_timesheet_staffmember_workhour_data()
					}
				}).catch(function(error){
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
					});
				});
			},
			bookingpress_get_timesheet_staffmember_workhour_data(){
				const vm2 = this
				var staff_members_action = { action: 'bookingpress_get_timesheet_staffmember_workhour_data',_wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' }
				<?php do_action( 'bookingpress_staff_shift_management_modify_xhr_postdata'); ?>
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( staff_members_action ) )
				.then(function(response){		
					if(response.data.variant != undefined && response.data.variant == 'success'){													
						var staff_member_workhour_details = response.data.edit_data;			
						vm2.bookingpress_configure_specific_workhour = staff_member_workhour_details.bookingpress_configure_specific_workhour;
						if(staff_member_workhour_details.workhours !== undefined && staff_member_workhour_details.workhours != '') {
							vm2.workhours_timings = staff_member_workhour_details.workhours;									
								staff_member_workhour_details.workhour_data.forEach(function(currentValue, index, arr){
								vm2.work_hours_days_arr.forEach(function(currentValue2, index2, arr2){										
									if(currentValue2.day_name == currentValue.day_name) {											
										vm2.work_hours_days_arr[index2]['break_times'] = currentValue.break_times							
									}
								});	
								vm2.selected_break_timings[currentValue.day_name] = currentValue.break_times							
							});		
						}
						<?php do_action( 'bookingpress_modify_staff_shift_management_xhr_response' ); ?>
					} else {		
						vm2.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
						});						
					}
				}).catch(function(error){
					console.log(error)
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
					});
				});
			},
			open_add_break_modal_func(currentElement, selected_day){
				const vm = this
				vm.reset_add_break_Form();
				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.break_modal_pos = (dialog_pos.top - 100)+'px'
				vm.break_modal_pos_right = '100px'								
				vm.break_selected_day = selected_day				
				vm.open_add_break_modal = true
				if( typeof this.bpa_adjust_popup_position != 'undefined' ){
					this.bpa_adjust_popup_position( currentElement, 'div#staffmember_breaks_add_modal .el-dialog.bpa-dialog--add-break');
				}
			},
			close_add_break_model() {
				const vm = this
				vm.$refs['break_timings'].resetFields()
				vm.reset_add_break_Form()					
				vm.open_add_break_modal = false;
			},
			reset_add_break_Form(){
				const vm = this
				vm.break_timings.start_time = ''
				vm.break_timings.end_time = ''
				vm.break_timings.edit_index = ''
				vm.is_edit_break = 0;
			},
			save_break_data(){
				const vm = this					
				var is_edit = 0;
				vm.$refs['break_timings'].validate((valid) => {                        
					if(valid) {    
						var update = 0;             
						if(vm.break_timings.start_time > vm.break_timings.end_time) {
							vm.$notify({
								title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
								message: '<?php esc_html_e('Start time is not greater than End time', 'bookingpress-appointment-booking'); ?>',
								type: 'error',
								customClass: 'error_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
						}else if(vm.break_timings.start_time == vm.break_timings.end_time) {                    
							vm.$notify({
								title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
								message: '<?php esc_html_e('Start time & End time are not same', 'bookingpress-appointment-booking'); ?>',
								type: 'error',
								customClass: 'error_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
						} else if(vm.selected_break_timings[vm.break_selected_day] != '' ) {                            
							vm.selected_break_timings[vm.break_selected_day].forEach(function(currentValue, index, arr) {
								if(is_edit == 0) {
									if(vm.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {    
										is_edit = 1;
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Please enter valid time for break', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});                
									} else if(currentValue['start_time'] == vm.break_timings.start_time && currentValue['end_time'] == 
										vm.break_timings.end_time && ( vm.break_timings.edit_index != index || vm.is_edit_break == 0 )) {                                        
										is_edit = 1;
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Break time already added', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
									}else if(((currentValue['start_time'] < vm.break_timings.start_time  && currentValue['end_time'] > vm.break_timings.start_time) || (currentValue['start_time'] < vm.break_timings.end_time  && currentValue['end_time'] > vm.break_timings.end_time) || (currentValue['start_time'] > vm.break_timings.start_time && currentValue['end_time'] <= vm.break_timings.end_time) || (currentValue['start_time'] >= vm.break_timings.start_time && currentValue['end_time'] < vm.break_timings.end_time)) && (vm.break_timings.edit_index != index || vm.is_edit_break == 0) )  {                                       
										is_edit = 1;
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Break time already added', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});                
									}                                                
								}    
							});
							if(is_edit == 0) {
								var formatted_start_time = formatted_end_time = '';                                 
								vm.default_break_timings.forEach(function(currentValue, index, arr) {
									if(currentValue.start_time == vm.break_timings.start_time) {
										formatted_start_time = currentValue.formatted_start_time;
									}
									if(currentValue.end_time == vm.break_timings.end_time) {
										formatted_end_time = currentValue.formatted_end_time;
									}
								});
								if(vm.break_selected_day != '' && vm.is_edit_break != 0) {
									vm.selected_break_timings[vm.break_selected_day].forEach(function(currentValue, index, arr) {
										if(index == vm.break_timings.edit_index) {
											currentValue.start_time = vm.break_timings.start_time;
											currentValue.end_time = vm.break_timings.end_time;
											currentValue.formatted_start_time = formatted_start_time;
											currentValue.formatted_end_time = formatted_end_time;
										}
									});   
								}else {
									vm.selected_break_timings[vm.break_selected_day].push({ start_time: vm.break_timings.start_time, end_time: vm.break_timings.end_time,formatted_start_time:formatted_start_time,formatted_end_time:formatted_end_time });                                    
								}
								vm.close_add_break_model()
							} 
						}  else {
							if(vm.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {
								vm.$notify({
									title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
									message: '<?php esc_html_e('Please enter valid time for break', 'bookingpress-appointment-booking'); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,
								});                
							}else{
								var formatted_start_time = formatted_end_time = '';									
								vm.default_break_timings.forEach(function(currentValue, index, arr) {
									if(currentValue.start_time == vm.break_timings.start_time) {
										formatted_start_time = currentValue.formatted_start_time;
									}
									if(currentValue.end_time == vm.break_timings.end_time) {
										formatted_end_time = currentValue.formatted_end_time;
									}
								});        
								vm.selected_break_timings[vm.break_selected_day].push({ start_time: vm.break_timings.start_time, end_time: vm.break_timings.end_time,formatted_start_time:formatted_start_time,formatted_end_time:formatted_end_time });
								vm.close_add_break_model();
							}
						}
					}
				})   				
			},
			bookingpress_remove_workhour(start_time, end_time, break_day){
				const vm = this		
				vm.selected_break_timings[break_day].forEach(function(currentValue, index, arr){
					if(currentValue.start_time == start_time && currentValue.end_time == end_time)
					{
						vm.selected_break_timings[break_day].splice(index, 1);
					}
				});					
			},
			saveStaffwhDetails(){
				const vm2 = this
				vm2.is_disabled = true
				vm2.is_display_save_loader = '1'
				var postdata = [];
				postdata.workhours_details = vm2.workhours_timings
				postdata.break_details = vm2.selected_break_timings
				postdata.action = 'bookingpress_add_staff_workinghour';
				postdata.bookingpress_configure_specific_workhour = vm2.bookingpress_configure_specific_workhour;
				<?php do_action( 'bookingpress_staff_workinghour_post_data') ?>
				postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
				.then(function(response){
					vm2.is_disabled = false
					vm2.is_display_save_loader = '0'							
					vm2.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
					});
					if (response.data.variant == 'success') {
					}
				}).catch(function(error){
					vm2.is_disabled = false
					vm2.is_display_loader = '0'
					console.log(error);
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
					});
				});
			},
			bookingpress_check_workhour_value(workhour_time,work_hour_day) {
				if(workhour_time == 'Off') {
					const vm = this
					vm.workhours_timings[work_hour_day].start_time = 'Off';
				}
			},		
			<?php
		}

		function bookingpress_timesheet_dynamic_onload_methods_func() {
			global $BookingPressPro;
			$bpa_edit_workhours = $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_workhours' );
			?>				
				const vm = this
				vm.bookingpress_get_staffmember_specialdays()
				<?php if($bpa_edit_workhours==1) { ?>
					vm.bookingpress_get_default_workhours()								
				<?php } ?>
			<?php
		}

		function bookingpress_load_timesheet_view_func() {
			$bookingpress_load_file_name = BOOKINGPRESS_PRO_VIEWS_DIR . '/staff_members/staffmember_timesheet.php';
			require $bookingpress_load_file_name;
		}


		function bookingpress_timesheet_dynamic_data_fields_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staff_member_workhours, $tbl_bookingpress_staffmembers_daysoff, $tbl_bookingpress_default_workhours,$tbl_bookingpress_default_daysoff,$bookingpress_global_options,$bookingpress_pro_staff_members, $BookingPressPro;
			$bookingpress_timesheet_data_fields_arr = array();
			$bookingpress_timesheet_data_fields_arr['is_readonly_input_fields'] = true;
			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_date_format        = $bookingpress_global_options_arr['wp_default_date_format'];
			$bookingpress_time_format        = $bookingpress_global_options_arr['wp_default_time_format'];			
			// Find bookingpress staffmember id
			$bookingpress_current_user_id  = get_current_user_id();
			$bookingpress_staffmember_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $bookingpress_current_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
			$bookingpress_staffmember_id   = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ? intval( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) : 0;
			
			// $bookingpress_get_default_workhours
			$bookingpress_days_arr = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
			$default_start_time = '00:00:00';
			$default_end_time   = '23:00:00';
			$step_duration_val  = 30;
			$bookingpress_workhours_data = array();
			$bookingpress_monday_times   = $bookingpress_tuesday_times = $bookingpress_wednesday_times = $bookingpress_thursday_times = $bookingpress_friday_times = $bookingpress_saturday_times = $bookingpress_sunday_times = array();

			foreach ( $bookingpress_days_arr as $days_key => $days_val ) {
				$selected_staffmembers_timings = $selected_timing_data = $bookingpress_breaks_arr = $bookingpress_get_staffmembers_breakhours = array();
				$selected_staffmembers_timings = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_workday_key = %s AND bookingpress_staffmember_workhours_is_break = %d AND bookingpress_staffmember_id = %d", ucfirst( $days_val ), 0, $bookingpress_staffmember_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is a table name. false alarm

				if(!empty($selected_staffmembers_timings)) {		
					$selected_start_time = esc_html($selected_staffmembers_timings['bookingpress_staffmember_workhours_start_time']);
					$selected_end_time   = esc_html($selected_staffmembers_timings['bookingpress_staffmember_workhours_end_time']);

					$bookingpress_get_staffmembers_breakhours = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_workday_key = %s AND bookingpress_staffmember_workhours_is_break = %d AND bookingpress_staffmember_id = %d", ucfirst( $days_val ), 1, $bookingpress_staffmember_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is a table name. false alarm

					if ( ! empty( $bookingpress_get_staffmembers_breakhours ) ) {
						foreach ( $bookingpress_get_staffmembers_breakhours as $break_workhour_key => $break_workhour_val ) {
							$bookingpress_breaks_arr[] = array(
								'start_time' => date( $bookingpress_time_format, strtotime(esc_html($break_workhour_val['bookingpress_staffmember_workhours_start_time']) ) ),
								'end_time'   => date( $bookingpress_time_format, strtotime( esc_html($break_workhour_val['bookingpress_staffmember_workhours_end_time']) )),
							);
						}
					}	
					
				} else {
					$selected_timing_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = 0", $days_val ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is a table name. false alarm
					$selected_start_time = !empty($selected_timing_data['bookingpress_start_time']) ? esc_html($selected_timing_data['bookingpress_start_time']) : '' ;
					$selected_end_time   = !empty($selected_timing_data['bookingpress_end_time']) ? esc_html( $selected_timing_data['bookingpress_end_time']) : '';					
					// Get breaks for current day and add to breaks array
					$bookingpress_get_break_workhours = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_workday_key = %s AND bookingpress_is_break = %d", $days_val, 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is a table name. false alarm					 
					if ( ! empty( $bookingpress_get_break_workhours ) ) {
						foreach ( $bookingpress_get_break_workhours as $break_workhour_key => $break_workhour_val ) {
							$bookingpress_breaks_arr[] = array(
								'start_time' => date( $bookingpress_time_format, strtotime( esc_html($break_workhour_val['bookingpress_start_time']))),
								'end_time'   => date( $bookingpress_time_format, strtotime( esc_html($break_workhour_val['bookingpress_end_time']))),
							);
						}
					}
				}
				if ( $selected_start_time == null ) {
					$selected_start_time = 'Off';
				} else {
					$selected_start_time = date( $bookingpress_time_format, strtotime($selected_start_time));
				}
				if ( $selected_end_time == null ) {
					$selected_end_time = 'Off';
				} else {
					$selected_end_time = date( $bookingpress_time_format, strtotime($selected_end_time));
				}
				if ( $days_val == 'monday' ) {
					$bookingpress_monday_times['workhours_start_time'] = $selected_start_time;
					$bookingpress_monday_times['workhours_end_time']   = $selected_end_time;
					$bookingpress_monday_times['break_times']          = $bookingpress_breaks_arr;
				} elseif ( $days_val == 'tuesday' ) {
					$bookingpress_tuesday_times['workhours_start_time'] = $selected_start_time;
					$bookingpress_tuesday_times['workhours_end_time']   = $selected_end_time;
					$bookingpress_tuesday_times['break_times']          = $bookingpress_breaks_arr;
				} elseif ( $days_val == 'wednesday' ) {
					$bookingpress_wednesday_times['workhours_start_time'] = $selected_start_time;
					$bookingpress_wednesday_times['workhours_end_time']   = $selected_end_time;
					$bookingpress_wednesday_times['break_times']          = $bookingpress_breaks_arr;
				} elseif ( $days_val == 'thursday' ) {
					$bookingpress_thursday_times['workhours_start_time'] = $selected_start_time;
					$bookingpress_thursday_times['workhours_end_time']   = $selected_end_time;
					$bookingpress_thursday_times['break_times']          = $bookingpress_breaks_arr;
				} elseif ( $days_val == 'friday' ) {
					$bookingpress_friday_times['workhours_start_time'] = $selected_start_time;
					$bookingpress_friday_times['workhours_end_time']   = $selected_end_time;
					$bookingpress_friday_times['break_times']          = $bookingpress_breaks_arr;
				} elseif ( $days_val == 'saturday' ) {
					$bookingpress_saturday_times['workhours_start_time'] = $selected_start_time;
					$bookingpress_saturday_times['workhours_end_time']   = $selected_end_time;
					$bookingpress_saturday_times['break_times']          = $bookingpress_breaks_arr;
				} elseif ( $days_val == 'sunday' ) {
					$bookingpress_sunday_times['workhours_start_time'] = $selected_start_time;
					$bookingpress_sunday_times['workhours_end_time']   = $selected_end_time;
					$bookingpress_sunday_times['break_times']          = $bookingpress_breaks_arr;
				}
			}

			$bookingpress_timesheet_data_fields_arr['monday_timings']    = $bookingpress_monday_times;
			$bookingpress_timesheet_data_fields_arr['tuesday_timings']   = $bookingpress_tuesday_times;
			$bookingpress_timesheet_data_fields_arr['wednesday_timings'] = $bookingpress_wednesday_times;
			$bookingpress_timesheet_data_fields_arr['thursday_timings']  = $bookingpress_thursday_times;
			$bookingpress_timesheet_data_fields_arr['friday_timings']    = $bookingpress_friday_times;
			$bookingpress_timesheet_data_fields_arr['saturday_timings']  = $bookingpress_saturday_times;
			$bookingpress_timesheet_data_fields_arr['sunday_timings']    = $bookingpress_sunday_times;

			$bookingpress_staffmember_daysoff_details = $bookingpress_staffmember_default_daysoff_details = array();

			if ( ! empty( $bookingpress_staffmember_id ) ) {

				// Get days off details
				$bookingpress_staffmember_daysoff_details = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_daysoff} WHERE bookingpress_staffmember_id = %d and bookingpress_staffmember_daysoff_parent = %d", $bookingpress_staffmember_id, 0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_daysoff is a table name. false alarm

				foreach ( $bookingpress_staffmember_daysoff_details as $key => $value ) {
					$bookingpress_staffmember_daysoff_details[ $key ]['bookingpress_staffmember_daysoff_formated_date'] = date( $bookingpress_date_format, strtotime( $value['bookingpress_staffmember_daysoff_date'] ) );
					$bookingpress_staffmember_daysoff_details[ $key ]['bookingpress_staffmember_daysoff_date'] = $value['bookingpress_staffmember_daysoff_date'];
					$bookingpress_staffmember_daysoff_enddate = $value['bookingpress_staffmember_daysoff_enddate'];
					if($bookingpress_staffmember_daysoff_enddate == null || $bookingpress_staffmember_daysoff_enddate == 'null' || empty($bookingpress_staffmember_daysoff_enddate)){
						$bookingpress_staffmember_daysoff_enddate = $value['bookingpress_staffmember_daysoff_date'];
					}
					$bookingpress_staffmember_daysoff_details[ $key ]['bookingpress_staffmember_daysoff_name'] = $value['bookingpress_staffmember_daysoff_name'] ? stripslashes_deep( $value['bookingpress_staffmember_daysoff_name']) : '';
					$bookingpress_staffmember_daysoff_details[ $key ]['bookingpress_staffmember_daysoff_enddate'] = $bookingpress_staffmember_daysoff_enddate;
										
					$dayoff_label = esc_html__( 'Once Off', 'bookingpress-appointment-booking' );
					if( true == $value['bookingpress_staffmember_daysoff_repeat'] ){
						$repeat_frequency = $value['bookingpress_staffmember_daysoff_repeat_frequency'];
						$repeat_frequency_type = $value['bookingpress_staffmember_daysoff_repeat_frequency_type'];
						$repeat_duration = $value['bookingpress_staffmember_daysoff_repeat_duration'];
						$repeat_times = $value['bookingpress_staffmember_daysoff_repeat_times'];
						$repeat_date = $value['bookingpress_staffmember_daysoff_repeat_date'];
						$dayoff_label = $BookingPressPro->bookingpress_retrieve_daysoff_repeat_label( $repeat_duration, $repeat_frequency, $repeat_frequency_type, $repeat_times, $repeat_date );
					}

					$bookingpress_staffmember_daysoff_details[ $key ]['dayoff_repeat_label'] = $dayoff_label;
				}
			}

			$bookingpress_timesheet_data_fields_arr['bookingpress_staffmembers_daysoff_details'] = $bookingpress_staffmember_daysoff_details;

			$bookingpress_timesheet_data_fields_arr['bookingpress_staffmembers_specialdays_details'] = array();

			$bookingpress_staffmember_default_daysoff_details = $wpdb->get_results( $wpdb->prepare( "SELECT `bookingpress_name`,`bookingpress_dayoff_date`,`bookingpress_dayoff_enddate`,`bookingpress_repeat` FROM {$tbl_bookingpress_default_daysoff} where `bookingpress_dayoff_parent` = %d",0), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_daysoff is a table name. false alarm

			foreach ( $bookingpress_staffmember_default_daysoff_details as $key => $value ) {

				$bookingpress_staffmember_daysoff_enddate = $value['bookingpress_dayoff_enddate'];
				if($bookingpress_staffmember_daysoff_enddate == null || $bookingpress_staffmember_daysoff_enddate == 'null' || empty($bookingpress_staffmember_daysoff_enddate)){
					$bookingpress_staffmember_daysoff_enddate = $value['bookingpress_dayoff_date'];
				}				
				$bookingpress_staffmember_default_daysoff_details[ $key ]['bookingpress_dayoff_date'] = date( $bookingpress_date_format, strtotime( $value['bookingpress_dayoff_date'] ) );
				$bookingpress_staffmember_default_daysoff_details[ $key ]['bookingpress_dayoff_enddate'] = date( $bookingpress_date_format, strtotime( $bookingpress_staffmember_daysoff_enddate ) );
				$bookingpress_staffmember_default_daysoff_details[ $key ]['bookingpress_name'] =  stripslashes_deep($value['bookingpress_name']  );
			}
			$bookingpress_timesheet_data_fields_arr['bookingpress_staffmember_default_daysoff_details'] = $bookingpress_staffmember_default_daysoff_details;

			// Days Off data variables
			$bookingpress_timesheet_data_fields_arr['days_off_add_modal']       = false;
			$bookingpress_timesheet_data_fields_arr['days_off_modal_pos']       = '0';
			$bookingpress_timesheet_data_fields_arr['days_off_modal_pos_right'] = '0';
			$bookingpress_timesheet_data_fields_arr['edit_staffmember_dayoff']  = '';
			$bookingpress_timesheet_data_fields_arr['rules_dayoff']             = array(
				'dayoff_name' => array(
					array(
						'required' => true,
						'message'  => __( 'Please enter name', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
				'dayoff_date' => array(
					array(
						'required' => true,
						'message'  => __( 'Please select date', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
			);

			$bookingpress_timesheet_data_fields_arr['staff_dayoff_repeat_frequency_type_opts'] = array(
				'day' => esc_html__( 'Days', 'bookingpress-appointment-booking' ),
				'week' => esc_html__( 'Week', 'bookingpress-appointment-booking' ),
				'month' => esc_html__( 'Month', 'bookingpress-appointment-booking' ),
				'year' => esc_html__( 'Year', 'bookingpress-appointment-booking' )
			);

			$bookingpress_timesheet_data_fields_arr['repeat_duration_opts'] = array(
				'forever' => esc_html__( 'Forever', 'bookingpress-appointment-booking'),
				'no_of_times' => esc_html__( 'Specific No. of Times', 'bookingpress-appointment-booking' ),
				'until' => esc_html__( 'Until', 'bookingpress-appointment-booking')
			);

			$bookingpress_timesheet_data_fields_arr['staff_daysoff_repeat_label'] = esc_html__( 'Once Off', 'bookingpress-appointment-booking' );
			$bookingpress_timesheet_data_fields_arr['staffmember_dayoff_form']  = array(
				'dayoff_name'   => '',
				'dayoff_date'   => '',
				'dayoff_date_end'   => '',
				'dayoff_date_range' => '',
				'dayoff_repeat' => false,
				'is_disabled' => false,
				'dayoff_repeat_frequency' => 1,
				'dayoff_repeat_freq_type' => 'year',
				'dayoff_repeat_duration' => 'forever',
				'dayoff_repeat_times' => 3,
				'dayoff_repeat_date' => date( 'Y-m-d', strtotime( '+1 year' ) )
			);	
			$bookingpress_timesheet_data_fields_arr['is_mask_display'] = false;

			// Special Days variables
			$bookingpress_timesheet_data_fields_arr['special_days_add_modal']       = false;
			$bookingpress_timesheet_data_fields_arr['special_days_modal_pos']       = '0';
			$bookingpress_timesheet_data_fields_arr['special_days_modal_pos_right'] = '0';
			$bookingpress_timesheet_data_fields_arr['edit_staffmember_special_day'] = '';
			$bookingpress_timesheet_data_fields_arr['rules_special_day']            = array(
				'special_day_date' => array(
					array(
						'required' => true,
						'message'  => __( 'Please select date', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
				'start_time'       => array(
					array(
						'required' => true,
						'message'  => __( 'Select start time', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
				'end_time'         => array(
					array(
						'required' => true,
						'message'  => __( 'Select end time', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
			);
			$bookingpress_timesheet_data_fields_arr['staffmember_special_day_form'] = array(
				'special_day_date'     => '',
				'special_day_service'  => '',
				'start_time'           => '',
				'end_time'             => '',
				'is_disabled'          => false,
				'special_day_workhour' => array(),
			);
			$bookingpress_timesheet_data_fields_arr['bookingpress_services_list'] = $BookingPress->get_bookingpress_service_data_group_with_category();
			$bookingpress_timesheet_data_fields_arr['bookingpress_staff_assign_services_list'] = array();
			if(!empty($bookingpress_staffmember_id)){
				$staffmember_assign_services = $bookingpress_pro_staff_members->bookingpress_get_staffmember_service($bookingpress_staffmember_id);
				if(!empty($staffmember_assign_services)){
					$bookingpress_timesheet_data_fields_arr['bookingpress_staff_assign_services_list'] = $bookingpress_pro_staff_members->get_bookingpress_service_data_group_with_category_for_staff($staffmember_assign_services);
				}
			}
			
			$bookingpress_timesheet_data_fields_arr['disabledOtherDates'] = '';			
			$bookingpress_timesheet_data_fields_arr['disabledDates'] = '';						
			$default_start_time    = '00:00:00';
			$default_end_time      = '23:55:00';
			$step_duration_val     = 05;
			
			$default_break_timings = array();
			$curr_time             = $tmp_start_time = date( 'H:i:s', strtotime( $default_start_time ) );
			$tmp_end_time          = date( 'H:i:s', strtotime( $default_end_time ) );

			do {

				$tmp_time_obj = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
				$end_time = $tmp_time_obj->format( 'H:i:s' );

				if($end_time == "00:00:00"){
					$end_time = "24:00:00";
				}
				

					$default_break_timings[] = array(
						'start_time'           => $curr_time,
						'formatted_start_time' => date( $bookingpress_global_options_arr['wp_default_time_format'], strtotime( $curr_time ) ),
						'end_time'             => $end_time,
						'formatted_end_time' => date( $bookingpress_global_options_arr['wp_default_time_format'], strtotime($end_time))." ".($end_time == "24:00:00" ? esc_html__('Next Day', 'bookingpress-appointment-booking') : '' ),
						
					);

					if($end_time == "24:00:00"){
						break;
					}


					$tmp_time_obj = new DateTime( $curr_time );
					$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
					$curr_time = $tmp_time_obj->format( 'H:i:s' );
			} while ( $curr_time <= $default_end_time );

			$bookingpress_timesheet_data_fields_arr['specialday_hour_list'] = $default_break_timings;

			$default_start_time     = '00:00:00';
			$default_end_time       = '23:25:00';
			$step_duration_val      = 05;
			$default_break_timings2 = array();
			$curr_time              = $tmp_start_time = date( 'H:i:s', strtotime( $default_start_time ) );
			$tmp_end_time           = date( 'H:i:s', strtotime( $default_end_time ) );
			do {
				$tmp_time_obj = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
				$end_time                 = $tmp_time_obj->format( 'H:i:s' );
				$default_break_timings2[] = array(
					'start_time'           => $curr_time,
					'formatted_start_time' => date( $bookingpress_global_options_arr['wp_default_time_format'], strtotime( $curr_time ) ),
					'end_time'             => $end_time,
					'formatted_end_time'   => date( $bookingpress_global_options_arr['wp_default_time_format'], strtotime( $end_time ) ),
				);
				$tmp_time_obj             = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
				$curr_time = $tmp_time_obj->format( 'H:i:s' );
			} while ( $curr_time <= $default_end_time );
			$bookingpress_timesheet_data_fields_arr['specialday_break_hour_list'] = $default_break_timings2;	
			$bookingpress_configure_specific_workhour = $this->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_configure_specific_workhour' );	$bookingpress_timesheet_data_fields_arr['bookingpress_configure_specific_workhour'] = !empty($bookingpress_configure_specific_workhour) &&  $bookingpress_configure_specific_workhour == 'true' ? true : false;		
			$bookingpress_timesheet_data_fields_arr['work_hours_days_arr']  = array();
			$bookingpress_timesheet_data_fields_arr['workhours_timings'] = array(
				'Monday'    => array(
					'start_time'       => '09:00:00',
					'end_time'         => '17:00:00',
					'break_start_time' => '',
					'break_end_time'   => '',
				),
				'Tuesday'   => array(
					'start_time'       => '09:00:00',
					'end_time'         => '17:00:00',
					'break_start_time' => '',
					'break_end_time'   => '',
				),
				'Wednesday' => array(
					'start_time'       => '09:00:00',
					'end_time'         => '17:00:00',
					'break_start_time' => '',
					'break_end_time'   => '',
				),
				'Thursday'  => array(
					'start_time'       => '09:00:00',
					'end_time'         => '17:00:00',
					'break_start_time' => '',
					'break_end_time'   => '',
				),
				'Friday'    => array(
					'start_time'       => '09:00:00',
					'end_time'         => '17:00:00',
					'break_start_time' => '',
					'break_end_time'   => '',
				),
				'Saturday'  => array(
					'start_time'       => 'Off',
					'end_time'         => 'Off',
					'break_start_time' => '',
					'break_end_time'   => '',
				),
				'Sunday'    => array(
					'start_time'       => 'Off',
					'end_time'         => 'Off',
					'break_start_time' => '',
					'break_end_time'   => '',
				),
			);
			$bookingpress_timesheet_data_fields_arr['default_break_timings']  = array();
			$bookingpress_timesheet_data_fields_arr['work_hours_days_arr']   = array();
			$bookingpress_timesheet_data_fields_arr['selected_break_timings']  = array(
				'Monday'    => array(),
				'Tuesday'   => array(),
				'Wednesday' => array(),
				'Thursday'  => array(),
				'Friday'    => array(),
				'Saturday'  => array(),
				'Sunday'    => array(),
			);
			$bookingpress_timesheet_data_fields_arr['break_selected_day']  = 'Monday';
			$bookingpress_timesheet_data_fields_arr['break_timings']  = array(
				'start_time' => '',
				'end_time'   => '',
			);
			$bookingpress_timesheet_data_fields_arr['open_add_break_modal'] = false;
			
			$bookingpress_timesheet_data_fields_arr['is_edit_break'] = '0';

			$bookingpress_timesheet_data_fields_arr['rules_add_break'] = array(
				'start_time' => array(
					array(
						'required' => true,
						'message'  => esc_html__( 'Please enter start time', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
				'end_time'   => array(
					array(
						'required' => true,
						'message'  => esc_html__( 'Please enter end time', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
			);
			$bookingpress_timesheet_data_fields_arr['is_display_save_loader']  = '0';
			$bookingpress_timesheet_data_fields_arr['is_disabled']  = false;
			$bookingpress_timesheet_data_fields_arr['is_display_loader'] = '0';

			$bookingpress_timesheet_data_fields_arr['display_staff_working_hours'] = true;

			$bookingpress_timesheet_data_fields_arr = apply_filters( 'bookingpress_modify_timesheet_data_fields', $bookingpress_timesheet_data_fields_arr);

			echo wp_json_encode( $bookingpress_timesheet_data_fields_arr );
		}
		function get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, $bookingpress_staffmember_metakey ) {
			global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta;
			$bookingpress_staffmembersmeta_value = '';

			$bookingpress_staffmembersmeta_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_meta} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmembermeta_key = %s", $bookingpress_staffmember_id, $bookingpress_staffmember_metakey ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_meta is a table name. false alarm

			if ( ! empty( $bookingpress_staffmembersmeta_details ) ) {
				$bookingpress_staffmembersmeta_value = $bookingpress_staffmembersmeta_details['bookingpress_staffmembermeta_value'];
			}
			return $bookingpress_staffmembersmeta_value;
		}
		function bookingpress_get_timesheet_staffmember_workhour_data_func(){
			global $wpdb, $tbl_bookingpress_staffmembers,$tbl_bookingpress_staff_member_workhours, $tbl_bookingpress_staffmembers_daysoff,$BookingPressPro,$bookingpress_global_options;
			$bookingpress_options           = $bookingpress_global_options->bookingpress_global_options();
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'timesheet_get_workhour_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$response['edit_data'] = array();
			$bookingpress_current_user_id  = get_current_user_id();
			$bookingpress_staffmember_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $bookingpress_current_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
			$bookingpress_staffmember_id   = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ? intval( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) : 0;

			if ( ! empty( $bookingpress_staffmember_id ) ) {
				// Get workhours details
				
				$bookingpress_staff_members_workhour_details = $bookingpress_staff_member_workhours = $bookingpress_workhours_data = array();

				$where_clause = $wpdb->prepare( 'bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_is_break = 0', $bookingpress_staffmember_id );
				$where_clause = apply_filters('bookingpress_modify_get_staff_workhour_where_clause', $where_clause, $_POST, $bookingpress_staffmember_id); // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

				$bookingpress_staff_member_workhours_details = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_staff_member_workhours} WHERE $where_clause",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is a table name. false alarm
				
				if ( ! empty( $bookingpress_staff_member_workhours_details ) ) {
					foreach ( $bookingpress_staff_member_workhours_details as $bookingpress_staff_member_workhour_key => $bookingpress_staff_member_workhour_val ) {
						$selected_start_time = $bookingpress_staff_member_workhour_val['bookingpress_staffmember_workhours_start_time'];
						$selected_end_time   = $bookingpress_staff_member_workhour_val['bookingpress_staffmember_workhours_end_time'];
						if ( $selected_start_time == null ) {
							$selected_start_time = 'Off';
						}
						if ( $selected_end_time == null ) {
							$selected_end_time = 'Off';
						}
						$bookingpress_staff_member_workhours[ $bookingpress_staff_member_workhour_val['bookingpress_staffmember_workday_key'] ] = array(
							'start_time' => $selected_start_time,
							'end_time'   => $selected_end_time,
						);
					}
					$bookingpress_break_time_details = array();
					$bookingpress_days_arr = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
					foreach ( $bookingpress_days_arr as $days_key => $days_val ) {
						$bookingpress_breaks_arr = array();
						$staff_break_where_clause = $wpdb->prepare( 'bookingpress_staffmember_workday_key = %s AND bookingpress_staffmember_workhours_is_break = 1 AND  bookingpress_staffmember_id = %d', $days_val, $bookingpress_staffmember_id );
						$staff_break_where_clause = apply_filters('bookingpress_modify_get_staff_break_workhour_where_clause', $staff_break_where_clause, $_POST, $bookingpress_staffmember_id, $days_val); // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
						$bookingpress_break_time_details = $wpdb->get_results( 'SELECT bookingpress_staffmember_workhours_start_time,bookingpress_staffmember_workhours_end_time FROM ' . $tbl_bookingpress_staff_member_workhours . ' WHERE '.$staff_break_where_clause, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is table name.
						if ( !empty($bookingpress_break_time_details)) {
							foreach($bookingpress_break_time_details as $key => $value) {
								$bookingpress_breaks_arr[] = array(
									'start_time' => $value['bookingpress_staffmember_workhours_start_time'],
									'formatted_start_time' => date( $bookingpress_options['wp_default_time_format'], strtotime( $value['bookingpress_staffmember_workhours_start_time'] ) ),
									'end_time'   => $value['bookingpress_staffmember_workhours_end_time'],
									'formatted_end_time'   => date( $bookingpress_options['wp_default_time_format'], strtotime( $value['bookingpress_staffmember_workhours_end_time'] ) ),								
								);
							}
						}
						$bookingpress_workhours_data[] = array(
							'day_name'    => ucfirst( $days_val ),
							'break_times' => $bookingpress_breaks_arr,
						);
					}
				}
				$bookingpress_configure_specific_workhour = $this->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_configure_specific_workhour' );						
				$bookingpress_staff_members_workhour_details['bookingpress_configure_specific_workhour'] = !empty($bookingpress_configure_specific_workhour) &&  $bookingpress_configure_specific_workhour == 'true' ? true : false;
				$bookingpress_staff_members_workhour_details['workhours']                                = $bookingpress_staff_member_workhours;
				$bookingpress_staff_members_workhour_details['workhour_data']                            = $bookingpress_workhours_data;

				$response['edit_data']                   = $bookingpress_staff_members_workhour_details;
				$response['msg']                         = esc_html__( 'Edit data retrieved successfully', 'bookingpress-appointment-booking' );
				$response['variant']                     = 'success';
				$response['title']                       = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			}			
			$response = apply_filters( 'bookingpress_modify_staff_shift_managment_data', $response );
			echo wp_json_encode( $response );
			exit();
		}
		function bookingpress_add_staff_workinghour_func() {
			global $wpdb, $BookingPress,$tbl_bookingpress_staffmembers,$tbl_bookingpress_staff_member_workhours,$tbl_bookingpress_staffmembers_daysoff,$bookingpress_global_options,$BookingPressPro,$tbl_bookingpress_staffmembers_services,$tbl_bookingpress_staffmembers_special_day,$tbl_bookingpress_staffmembers_special_day_breaks,$tbl_bookingpress_services,$bookingpress_services;
		
			$response = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'timesheet_add_workhour_details', true, 'bpa_wp_nonce' );
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');
				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;
				wp_send_json( $response );
				die;
			}
			$response['variant'] = 'error';
			$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
			$response['msg'] = '';
			$response['staffmember_id'] = '';
			
			if ( ! empty( $_REQUEST ) ) {
		
				$bookingpress_user_id  = get_current_user_id();
				$bookingpress_existing_staffmember_details = $wpdb->get_row( $wpdb->prepare( "SELECT `bookingpress_staffmember_id` FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d ORDER BY bookingpress_staffmember_id DESC", $bookingpress_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
				if ( ! empty( $bookingpress_existing_staffmember_details ) ) {
					$bookingpress_update_id = ! empty( $bookingpress_existing_staffmember_details['bookingpress_staffmember_id'] ) ? $bookingpress_existing_staffmember_details['bookingpress_staffmember_id'] : '';
				}
		
				// Save workhours details
				if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_workhours' ) ) {
					$bookingpress_configure_specific_workhour = ! empty( $_REQUEST['bookingpress_configure_specific_workhour'] ) ? sanitize_text_field( $_REQUEST['bookingpress_configure_specific_workhour'] ) : 'false';
					$this->update_bookingpress_staffmembersmeta( $bookingpress_update_id, 'bookingpress_configure_specific_workhour', $bookingpress_configure_specific_workhour );
	
					$bookingpress_delete_staff_workhours_where_condition = array(
						'bookingpress_staffmember_id' => $bookingpress_update_id,
						'bookingpress_staffmember_workhours_is_break' => 0,
					);
					$bookingpress_delete_staff_workhours_where_condition = apply_filters('bookingpress_delete_staff_workhours_where_condition_filter', $bookingpress_delete_staff_workhours_where_condition, $_REQUEST);
					$wpdb->delete( $tbl_bookingpress_staff_member_workhours, $bookingpress_delete_staff_workhours_where_condition );
	
					$bookingpress_delete_staff_workhours_break_where_condition = array(
						'bookingpress_staffmember_id' => $bookingpress_update_id,
						'bookingpress_staffmember_workhours_is_break' => 1,
					);
					$bookingpress_delete_staff_workhours_break_where_condition = apply_filters('bookingpress_delete_staff_workhours_break_where_condition_filter', $bookingpress_delete_staff_workhours_break_where_condition, $_REQUEST);
					$wpdb->delete( $tbl_bookingpress_staff_member_workhours, $bookingpress_delete_staff_workhours_break_where_condition );
						
		
					if ( ! empty( $bookingpress_configure_specific_workhour ) && $bookingpress_configure_specific_workhour == 'true' ) {							
	
						$bookingpress_workhour_days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
						foreach ( $bookingpress_workhour_days as $workhour_key => $workhour_val ) {
							$workhour_start_time = ! empty( $_REQUEST['workhours_details'][ $workhour_val ]['start_time'] ) ? sanitize_text_field( $_REQUEST['workhours_details'][ $workhour_val ]['start_time'] ) : '09:00:00';
							$workhour_end_time   = ! empty( $_REQUEST['workhours_details'][ $workhour_val ]['end_time'] ) ? sanitize_text_field( $_REQUEST['workhours_details'][ $workhour_val ]['end_time'] ) : '17:00:00';
	
							if ( $workhour_start_time == 'Off' ) {
								$workhour_start_time = null;
							}
							if ( $workhour_end_time == 'Off' ) {
								$workhour_end_time = null;
							}
							$bookingpress_db_fields = array(
								'bookingpress_staffmember_id' => $bookingpress_update_id,
								'bookingpress_staffmember_workday_key' => $workhour_val,
								'bookingpress_staffmember_workhours_start_time' => $workhour_start_time,
								'bookingpress_staffmember_workhours_end_time' => $workhour_end_time,
							);
							$bookingpress_db_fields = apply_filters('bookingpress_modify_staff_workhours_details', $bookingpress_db_fields, $_REQUEST);
							$wpdb->insert( $tbl_bookingpress_staff_member_workhours, $bookingpress_db_fields );
						}
	
						$bookingpress_break_days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
						foreach ( $bookingpress_break_days as $break_key => $break_val ) {
							$bookingpress_day_break_details = ! empty( $_REQUEST['break_details'][ $break_val ] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['break_details'][ $break_val ] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
							if ( ! empty( $bookingpress_day_break_details ) ) {
								foreach ( $bookingpress_day_break_details as $break_day_arr_key => $break_day_arr_val ) {
									$break_start_time       = $break_day_arr_val['start_time'];
									$break_end_time         = $break_day_arr_val['end_time'];
									$bookingpress_db_fields = array(
										'bookingpress_staffmember_id' => $bookingpress_update_id,
										'bookingpress_staffmember_workday_key' => $break_val,
										'bookingpress_staffmember_workhours_start_time' => $break_start_time,
										'bookingpress_staffmember_workhours_end_time' => $break_end_time,
										'bookingpress_staffmember_workhours_is_break' => 1,
									);
									$bookingpress_db_fields = apply_filters('bookingpress_modify_staff_workhours_details', $bookingpress_db_fields, $_REQUEST);
									$wpdb->insert( $tbl_bookingpress_staff_member_workhours, $bookingpress_db_fields );
								}
							}
						}
					}
					$response['staffmember_id'] = $bookingpress_update_id;
					$response['variant']        = 'success';
					$response['title']          = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']            = esc_html__( 'Working Hours data updated successfully.', 'bookingpress-appointment-booking' );					
				} 	
				$response = apply_filters( 'bookingpress_staff_members_save_external_details', $response );
			}
			echo wp_json_encode( $response );
			die();
		}
		function update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, $bookingpress_staffmember_metakey, $bookingpress_staffmember_metavalue ) {
			global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta;

			$bookingpress_exist_meta_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_staffmembermeta_id) as total FROM {$tbl_bookingpress_staffmembers_meta} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmembermeta_key = %s", $bookingpress_staffmember_id, $bookingpress_staffmember_metakey ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_meta is a table name. false alarm

			if ( $bookingpress_exist_meta_count > 0 ) {

				$bookingpress_exist_meta_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_meta} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmembermeta_key = %s", $bookingpress_staffmember_id, $bookingpress_staffmember_metakey ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_meta is a table name. false alarm
				$bookingpress_staffmembermeta_id = $bookingpress_exist_meta_details['bookingpress_staffmembermeta_id'];

				$bookingpress_staffmember_meta_details = array(
					'bookingpress_staffmember_id'        => $bookingpress_staffmember_id,
					'bookingpress_staffmembermeta_key'   => $bookingpress_staffmember_metakey,
					'bookingpress_staffmembermeta_value' => $bookingpress_staffmember_metavalue,
				);

				$bookingpress_update_where_condition = array(
					'bookingpress_staffmembermeta_id' => $bookingpress_staffmembermeta_id,
				);

				$wpdb->update( $tbl_bookingpress_staffmembers_meta, $bookingpress_staffmember_meta_details, $bookingpress_update_where_condition );
			} else {
				$bookingpress_staffmember_meta_details = array(
					'bookingpress_staffmember_id'        => $bookingpress_staffmember_id,
					'bookingpress_staffmembermeta_key'   => $bookingpress_staffmember_metakey,
					'bookingpress_staffmembermeta_value' => $bookingpress_staffmember_metavalue,
				);

				$wpdb->insert( $tbl_bookingpress_staffmembers_meta, $bookingpress_staffmember_meta_details );
			}
			return 1;
		}
	}
}
global $bookingpress_pro_timesheet;
$bookingpress_pro_timesheet = new bookingpress_pro_timesheet();
