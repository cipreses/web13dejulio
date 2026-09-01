<?php
if ( ! class_exists( 'bookingpress_pro_services' ) ) {
	class bookingpress_pro_services Extends BookingPress_Core {
		function __construct() {

			add_filter( 'bookingpress_modify_service_view_file_path', array( $this, 'bookingpress_modify_file_path_func' ), 10 );
			add_filter( 'bookingpress_modify_service_data_fields', array( $this, 'bookingpress_modify_service_data_fields_func' ), 10 );
			add_filter( 'bookingpress_after_add_update_service', array( $this, 'bookingpress_save_service_details' ), 10, 3 );
			add_action( 'bookingpress_add_service_dynamic_vue_methods', array( $this, 'bookingpress_add_service_dynamic_vue_methods_func' ), 10 );
			add_action( 'bookingpress_add_service_dynamic_on_load_methods', array( $this, 'bookingpress_add_service_dynamic_on_load_methods_func' ) );

			add_action( 'bookingpress_edit_service_more_vue_data', array( $this, 'bookingpress_edit_service_more_vue_data_func' ), 10 );
			//add_action( 'wp_ajax_bookingpress_get_service_workhour_details', array( $this, 'bookingpress_get_service_workhour_details_func' ), 10 );

			add_filter( 'bookingpress_add_new_category_option', array( $this, 'bookingpress_add_new_category_option_func' ), 10, 1 );

			add_action( 'bookingpress_after_reset_add_service_form', array( $this, 'bookingpress_after_reset_add_service_form_func' ), 10 );
			//add_action( 'bookingpress_after_open_add_service_model', array( $this, 'bookingpress_after_open_add_service_model_func' ), 10 );
			
			add_action( 'wp_ajax_bookingpress_get_service_special_day_details', array( $this, 'bookingpress_get_service_special_day_details_func' ) );

			add_action( 'wp_ajax_bookingpress_validate_service_special_days', array( $this, 'bookingpress_validate_service_special_days_func' ) );

			add_action( 'bookingpress_add_posted_data_for_save_service', array( $this, 'bookingpress_add_posted_data_for_save_service_func' ) );
			add_action( 'wp_ajax_bookingpress_get_staffmember_service_data', array( $this, 'bookingpress_get_staffmember_service_data_func' ) );

			add_action( 'wp_ajax_bookingpress_change_service_status', array( $this, 'bookingpress_change_service_status_func' ) );
			add_action( 'wp_ajax_bookingpress_save_shift_mgmt_details', array( $this, 'bookingpress_save_shift_mgmt_details_func' ) );

			add_filter( 'bookingpress_modify_frontend_return_timings_data', array( $this, 'bookingpress_modify_frontend_return_timings_data_func' ), 10, 3 );

			add_action( 'bookingpress_after_delete_service', array( $this, 'bookingpress_after_delete_service_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_format_service_special_days_data', array( $this, 'bookingpress_format_service_special_days_data_func' ) );

			add_filter('bookingpress_modify_servies_listing_data', array($this, 'bookingpress_modify_servies_listing_data_func'), 11, 3);

			add_filter( 'bookingpress_retrieve_pro_modules_timeslots', array( $this, 'bookingpress_retrieve_service_timings' ), 11, 6 );
			add_filter( 'bookingpress_retrieve_capacity', array( $this, 'bookingpress_get_service_capacity'), 10, 2 );
			add_filter( 'bookingpress_retrieve_min_capacity', array( $this, 'bookingpress_get_service_minimun_capacity'), 10, 2 );
			add_filter( 'bpa_calculate_service_breakhours_data', array( $this, 'bookingpress_calculate_service_break_data'), 10, 2 );


			//Duplicate other details of duplicated service
			add_action('bookingpress_duplicate_more_details', array($this, 'bookingpress_duplicate_more_details_func'), 10, 2);
			

			add_filter( 'bookingpress_get_shared_capacity_data', array( $this, 'bookingpress_get_shared_capacity_data_func') );
			add_action('wp_ajax_bookingpress_format_assigned_staffmember_service_amounts', array($this, 'bookingpress_format_assigned_staffmember_service_amounts_func'));
			

			add_action('bookingpress_after_close_add_service_form', array($this, 'bookingpress_after_close_add_service_form_func'), 10, 2);

			add_filter('bookingpress_modify_edit_service_data', array($this, 'bookingpress_modify_edit_service_data_func'), 10, 2);

			add_action('wp_ajax_bookingpress_retrieve_service_shift_managment', array($this, 'bookingpress_retrieve_service_shift_managment_func'));

			add_action( 'bookingpress_modify_default_off_days', array( $this, 'bookingpress_modify_default_workhours_days'), 6, 4 );

			add_action( 'bookingpress_modify_default_holidays', array( $this, 'bookingpress_modify_default_holidays_with_service_daysoff'), 5, 4 );
			add_action( 'bookingpress_modify_default_holidays', array( $this, 'bookingpress_modify_default_holidays_with_service_special_days'), 6, 4 );

			add_action( 'bookingpress_modify_max_available_time_for_booking', array( $this, 'bookingpress_modify_max_available_date_with_service_expiration'), 10, 3 );

			add_filter( 'bookingpress_buffer_calculations', array( $this, 'bookingpress_modify_service_timings_with_buffer_time' ), 10, 5 );

			add_action('bookingpress_add_service_validation', array( $this, 'bookingpress_add_service_validation_func'));

			add_action( 'wp_ajax_bookingpress_validate_service_daysoff', array( $this, 'bookingpress_validate_service_daysoff_func') );
			add_action( 'wp_ajax_bookingpress_format_service_daysoff_data', array( $this, 'bookingpress_format_service_daysoff_data_func') );
			
		}

		function bookingpress_format_service_daysoff_data_func(){
			global $wpdb, $bookingpress_global_options,$BookingPress;
			$response                    = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'format_service_daysoff_data', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$response['variant']         = 'error';
			$response['title']           = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']             = esc_html__( 'Something went wrong', 'bookingpress-appointment-booking' );
			$response['daysoff_details'] = '';
			$wpnonce                     = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag       = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				echo wp_json_encode( $response );
				die();
			}
			$bookingpress_global_settings   = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_date_format       = $bookingpress_global_settings['wp_default_date_format'];

			$bookingpress_daysoff_data = ! empty( $_POST['daysoff_data'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['daysoff_data']) : array();  //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST['daysoff_data'] has already been sanitized.

			if ( ! empty( $bookingpress_daysoff_data ) && is_array( $bookingpress_daysoff_data ) ) {
				foreach ( $bookingpress_daysoff_data as $k => $v ) {
					$bookingpress_daysoff_data[ $k ]['dayoff_formatted_date'] = date( $bookingpress_date_format, strtotime(  $v['dayoff_date'] ));					
					$bookingpress_daysoff_data[ $k ]['dayoff_repeat'] = $v['dayoff_repeat'] == 'true' ? true : false;
					$bookingpress_daysoff_data[ $k ]['dayoff_name'] = !empty($v['dayoff_name']) ? stripslashes_deep($v['dayoff_name']) : '';

				}
				$response['variant']         = 'success';
				$response['title']           = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']             = esc_html__( 'Details formatted successfully', 'bookingpress-appointment-booking' );
				$response['daysoff_details'] = $bookingpress_daysoff_data;
			}
			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_get_service_total_appointment( $bookingpress_service_id, $bookingpress_date, $bookingpress_status ){
			global $wpdb,$tbl_bookingpress_appointment_bookings;
			$total_appointments              = '';
			$bookingpress_search_query_where = 'WHERE 1=1 ';
			if ( ! empty( $date ) ) {
				$bookingpress_search_query_where .= " AND (bookingpress_appointment_date = '{$date}')";
			}
			if ( ! empty( $status ) && is_array( $status ) ) {
				$bookingpress_search_query_where .= ' AND (';
				$i                                = 0;
				foreach ( $status as $status_key => $status_value ) {
					if ( $i != 0 ) {
						$bookingpress_search_query_where .= ' OR';
					}
					$bookingpress_search_query_where .= " bookingpress_appointment_status ='{$status_value}'";
					$i++;
				}
				$bookingpress_search_query_where .= ' )';
			}
			if ( ! empty( $bookingpress_service_id ) ) {
				$total_appointments = $wpdb->get_var( 'SELECT COUNT(bookingpress_appointment_booking_id) FROM ' . $tbl_bookingpress_appointment_bookings . ' ' . $bookingpress_search_query_where . ' AND bookingpress_service_id = ' . $bookingpress_service_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			}
			return $total_appointments;
		}

		function bookingpress_validate_service_daysoff_func(){
			global $BookingPress,$wpdb, $tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$tbl_bookingpress_customers,$bookingpress_global_options, $BookingPressPro;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'validate_service_daysoff_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_service_id = ! empty( $_REQUEST['service_id'] ) ? intval( $_REQUEST['service_id'] ) : '';

			if ( ! empty( $_REQUEST['selected_date_range'] ) && ! empty( $bookingpress_service_id ) ) {
				$bookingpress_search_date = sanitize_text_field( $_REQUEST['selected_date_range'] );
				$bookingpress_date        = date( 'Y-m-d', strtotime( $bookingpress_search_date ) );

				$bookingpress_search_date_end = (isset($_REQUEST['selected_date_range_end']))?sanitize_text_field( $_REQUEST['selected_date_range_end'] ):'';
				$bookingpress_date_end        = date( 'Y-m-d', strtotime( $bookingpress_search_date_end ) );
				$bookingpress_status      = array( '1', '2' );

				if ( $bookingpress_date &&  $bookingpress_date == $bookingpress_date_end) {
					$total_appointments = $this->bookingpress_get_service_total_appointment( $bookingpress_service_id, $bookingpress_date, $bookingpress_status );
					if ( $total_appointments > 0 ) {
						$response['variant'] = 'warnning';
						$response['title']   = esc_html__( 'Warning', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( 'Appointment(s) are already booked during this time duration. Do you still want to continue?', 'bookingpress-appointment-booking' );
					} else {
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'success', 'bookingpress-appointment-booking' );
						$response['msg']     = '';
					}
				}else{
					if($bookingpress_date && $bookingpress_date_end){
						$startDate = strtotime($bookingpress_date);
						$endDate = strtotime($bookingpress_date_end);                 
						$bookingpress_child_holiday_dates = array();
						for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
							$date = date('Y-m-d', $currentDate);
							$bookingpress_child_holiday_dates[] = $date;
						} 
						if(!empty($bookingpress_child_holiday_dates)){
							$total_appointments = 0;
							foreach($bookingpress_child_holiday_dates as $holiday_date){
								$total_appointments = $this->bookingpress_get_service_total_appointment( $bookingpress_service_id, $holiday_date, $bookingpress_status );	
								if($total_appointments > 0){
									break;
								}
							}
							if ( $total_appointments > 0 ) {
								$response['variant'] = 'warnning';
								$response['title']   = esc_html__( 'Warning', 'bookingpress-appointment-booking' );
								$response['msg']     = esc_html__( 'Appointment(s) are already booked during this time duration. Do you still want to continue?', 'bookingpress-appointment-booking' );
							} else {
								$response['variant'] = 'success';
								$response['title']   = esc_html__( 'success', 'bookingpress-appointment-booking' );
								$response['msg']     = '';
							}							
						}						
					}
				}
			} else {
				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'success', 'bookingpress-appointment-booking' );
			}

			$sevice_form_data = $_REQUEST['service_form_data'];
			$dayoff_label = esc_html__( 'Once Off', 'bookingpress-appointment-booking' );
			if( true == $sevice_form_data['dayoff_repeat'] ){
				$repeat_frequency = $sevice_form_data['dayoff_repeat_frequency' ];
				$repeat_frequency_type = $sevice_form_data[ 'dayoff_repeat_freq_type' ];
				$repeat_duration = $sevice_form_data[ 'dayoff_repeat_duration' ];
				$repeat_times = $sevice_form_data['dayoff_repeat_times'];
				$repeat_date = $sevice_form_data['dayoff_repeat_date'];

				$repeat_date = apply_filters( 'bookingpress_change_repeat_date_timezone_to_wp', $repeat_date );

				$dayoff_label = $BookingPressPro->bookingpress_retrieve_daysoff_repeat_label( $repeat_duration, $repeat_frequency, $repeat_frequency_type, $repeat_times, $repeat_date );
			}

			$response['repeat_label'] = $dayoff_label;

			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_add_service_validation_func(){

			global $bookingpress_bring_anyone_with_you; 
			$bookingpress_bring_anyone_with_you_activate = $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation(); 
			if( $bookingpress_bring_anyone_with_you_activate == 1 ){

				if(!empty( $_POST['min_capacity'] && $_POST['max_capacity'] ) && $_POST['min_capacity'] > $_POST['max_capacity'] )  { // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.				
					$response            = array();
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = esc_html__('Service min capacity should not be greater than max capacity.', 'bookingpress-appointment-booking');
					wp_send_json($response);
					die();				
				}
			}
 
			if(!empty( $_POST['service_start_date'] && $_POST['service_expiration_date'] ) && $_POST['service_start_date'] > $_POST['service_expiration_date'] )  { // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
				$response            = array();
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('The service start date should not be earlier than the service expiration date.', 'bookingpress-appointment-booking');
                wp_send_json($response);
                die();

			}

			if( empty( $_POST['service_update_id']) && (!empty( $_POST['service_start_date']) ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

				
				$current_timestamp = strtotime( date('Y-m-d', current_time('timestamp') ) );
				$service_start_date = strtotime( sanitize_text_field($_POST['service_start_date']) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

				if( $service_start_date < $current_timestamp ){

					$response            = array();
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = esc_html__('The service start date should not be earlier than the current date.', 'bookingpress-appointment-booking');
					wp_send_json($response);
					die();
				}
			}

			if( empty( $_POST['service_update_id']) && (!empty( $_POST['service_expiration_date']))){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

				$current_timestamp = strtotime( date('Y-m-d', current_time('timestamp') ) );
				$service_expiration_date = strtotime( sanitize_text_field($_POST['service_expiration_date'])); // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

				if( $service_expiration_date < $current_timestamp ){

					$response            = array();
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = esc_html__('The service expiration date should not be earlier than the current date.', 'bookingpress-appointment-booking');
					wp_send_json($response);
					die();
				}
				
			}
		}
		/**
		 * bpa function for get services
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_get_services_func($user_detail=array()){
			global $BookingPress,$tbl_bookingpress_services,$wpdb,$tbl_bookingpress_servicesmeta,$bookingpress_pro_staff_members,$tbl_bookingpress_categories,$BookingPressPro;
			
			$result = array();
			$result["total_records"] = 0;
			$result["services"] = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));


			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){
			
				$bookingpress_service_id = isset($user_detail['service_id']) ? intval($user_detail['service_id']) : '';
				$bookingpress_category_id = isset($user_detail['category_id']) ? intval($user_detail['category_id']) : '';
				$bookingpress_staffmember_id = isset($user_detail['staffmember_id']) ? intval($user_detail['staffmember_id']) : '';
				$bookingpress_location_id = isset($user_detail['location_id']) ? intval($user_detail['location_id']) : '';
				$perpage     = isset($user_detail['per_page']) ? intval($user_detail['per_page']) : 10;
				$currentpage = isset($user_detail['current_page']) ? intval($user_detail['current_page']) : 1;
				$offset      = ( ! empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;

				$filter_pass_data = array('service_id'=>$bookingpress_service_id,'staffmember_id'=>$bookingpress_staffmember_id,'location_id'=>$bookingpress_location_id);

				$where_clause = '';
				$bookingpress_disable_serevice = $wpdb->get_row($wpdb->prepare("SELECT GROUP_CONCAT(bookingpress_service_id) disable_serevices FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_servicemeta_name = %s AND bookingpress_servicemeta_value = %s", 'show_service_on_site','false'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_servicesmeta is table name defined globally.
				if(!empty($bookingpress_disable_serevice)){
					$where_clause .= $wpdb->prepare( ' AND bookingpress_services.bookingpress_service_id NOT IN (%s)', $bookingpress_disable_serevice);
				}					
				if(!empty($bookingpress_service_id) && $bookingpress_service_id != 0){
					$where_clause .= $wpdb->prepare( ' AND bookingpress_services.bookingpress_service_id = %d', $bookingpress_service_id);
				}

				$join_query = " LEFT JOIN {$tbl_bookingpress_categories} as bookingpress_categories ON bookingpress_services.bookingpress_category_id = bookingpress_categories.bookingpress_category_id ";
				if(!empty($bookingpress_category_id) && $bookingpress_category_id != 0){
					$where_clause .= $wpdb->prepare( ' AND bookingpress_services.bookingpress_category_id = %d', $bookingpress_category_id);
				}

				$bpa_is_staffmember_module_active = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
				if($bpa_is_staffmember_module_active){						
					global $tbl_bookingpress_staffmembers_services;
					$join_query .= " INNER JOIN {$tbl_bookingpress_staffmembers_services} as staffmembers_services ON staffmembers_services.bookingpress_service_id = bookingpress_services.bookingpress_service_id ";
					if(!empty($bookingpress_staffmember_id) && $bookingpress_staffmember_id != 0){
						$where_clause .= $wpdb->prepare( ' AND staffmembers_services.bookingpress_staffmember_id = %d', $bookingpress_staffmember_id);
					}
				}
				$bookingpress_bpa_get_services_extra_query = array('join_query'=>$join_query,'where_clause'=>$where_clause);
				$bookingpress_bpa_get_services_extra_query = apply_filters( 'bookingpress_bpa_get_services_where_clause', $bookingpress_bpa_get_services_extra_query,$filter_pass_data);

				$join_query = $bookingpress_bpa_get_services_extra_query['join_query'];
				$where_clause = $bookingpress_bpa_get_services_extra_query['where_clause'];

				$bookingpress_total_services = $wpdb->get_results("SELECT COUNT(DISTINCT bookingpress_services.bookingpress_service_id) FROM {$tbl_bookingpress_services} as bookingpress_services $join_query WHERE 1 = 1 $where_clause GROUP BY bookingpress_services.bookingpress_service_id ORDER BY bookingpress_services.bookingpress_service_position ASC "); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $bookingpress_total_services is a table name. false alarm

				$bookingpress_all_services = $wpdb->get_results( "SELECT bookingpress_services.*, bookingpress_categories.bookingpress_category_name, bookingpress_categories.bookingpress_category_position FROM {$tbl_bookingpress_services} as bookingpress_services $join_query WHERE 1 = 1 $where_clause GROUP BY bookingpress_services.bookingpress_service_id ORDER BY bookingpress_services.bookingpress_service_position ASC LIMIT {$offset} , {$perpage}", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally.

				$bpa_all_services = array();
				if( !empty( $bookingpress_all_services ) ){

					foreach( $bookingpress_all_services as $skey => $svalue ){
						$svalue['is_visible'] = true;
						$svalue['is_visible_with_flag'] = true;
						$services_meta = $BookingPress->bookingpress_get_service_meta( $svalue['bookingpress_service_id'] );
						$svalue['services_meta'] = $services_meta;
						$service_price = $svalue['bookingpress_service_price'];
						$svalue['bookingpress_service_price'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $service_price );
						$svalue['service_price_without_currency'] = (float) $service_price;
						if((is_plugin_active('bookingpress-multilanguage/bookingpress-multilanguage.php'))){
							if(method_exists( $BookingPressPro, 'bookingpress_pro_front_language_translation_func') ) {
								if(!empty($svalue['bookingpress_service_name'])){
									$bookingpress_service_id = $svalue['bookingpress_service_id'];
									$svalue['bookingpress_service_name'] = $BookingPressPro->bookingpress_pro_front_language_translation_func($svalue['bookingpress_service_name'],'service','bookingpress_service_name',$bookingpress_service_id);
								} 
								if(!empty($svalue['bookingpress_service_description'])){
									$bookingpress_service_id = $svalue['bookingpress_service_id'];
									$svalue['bookingpress_service_description'] = $BookingPressPro->bookingpress_pro_front_language_translation_func($svalue['bookingpress_service_description'],'service','bookingpress_service_description',$bookingpress_service_id);
								} 									
								if(!empty($svalue['bookingpress_category_name'])){
									$bookingpress_category_id = $svalue['bookingpress_category_id'];
									$svalue['bookingpress_category_name'] = $BookingPressPro->bookingpress_pro_front_language_translation_func($svalue['bookingpress_category_name'],'category','bookingpress_category_name',$bookingpress_category_id);
								} 									                      
							}
						}
						$svalue['bookingpress_service_name'] = stripslashes_deep($svalue['bookingpress_service_name']);        							
						$svalue['bookingpress_service_duration_label'] = !empty( $bookingpress_duration_suffix_labels[ $svalue['bookingpress_service_duration_unit'] ] ) ? $bookingpress_duration_suffix_labels[ $svalue['bookingpress_service_duration_unit'] ] : $svalue['bookingpress_service_duration_unit'];							
						if ($bookingpress_display_service_description == 'false' ) {
							$svalue['display_read_more_less'] = 1;
							$default_service_description   = $svalue['bookingpress_service_description'];
							if (strlen($default_service_description) > 140 ) {
								$svalue['bookingpress_service_description_with_excerpt'] = substr($default_service_description, 0, 140);
								$svalue['display_details_more']                          = 0;
								$svalue['display_details_less']                          = 1;
							} else {
								$svalue['display_read_more_less'] = 0;
							}
						}
						if( !empty( $services_meta['service_image_details'] ) ){
							$svalue['use_placeholder'] = !empty( $services_meta['service_image_details'][0]['url'] ) ? false : true;
							$svalue['img_url'] = !empty( $services_meta['service_image_details'][0]['url'] ) ? $services_meta['service_image_details'][0]['url'] : BOOKINGPRESS_URL . '/images/placeholder-img.jpg';
						} else {
							$svalue['use_placeholder'] = true;
						}
						$bpa_all_services[$svalue['bookingpress_service_id']] = $svalue;
					}

				}
				$bpa_all_services = apply_filters( 'bookingpress_modify_all_retrieved_services', $bpa_all_services, $service, '', $bookingpress_category_id );
				
				$result["total_records"] = (!empty($bookingpress_total_services))?count($bookingpress_total_services):0;
				$result["services"] = $bpa_all_services;

				$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));										
			}
			
			
			return $response;

		}			

		function bookingpress_modify_service_timings_with_buffer_time( $service_timings, $total_booked_appointments, $selected_service_id, $shared_quantity, $booked_timing_keys ){

			global $BookingPress, $bookingpress_pro_staff_members, $bookingpress_services;

			if( empty( $total_booked_appointments ) ){
				return $service_timings;
			}



			$bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');		

			
			foreach( $total_booked_appointments as $booked_appointment_data ){

				$booked_service_id = $booked_appointment_data['bookingpress_service_id'];

				if( 'd' == $booked_appointment_data['bookingpress_service_duration_unit'] /* || ( $selected_service_id != $booked_service_id && 'true' != $bookingpress_shared_service_timeslot ) */ ){
					continue;
				}
				
				/** Check if booked appointment has buffer enabled either it's before or after */
				$bookingpress_service_buffertime_before = $bookingpress_services->bookingpress_get_service_meta( $booked_service_id, 'before_buffer_time' );
				$bookingpress_service_buffertime_before_unit = $bookingpress_services->bookingpress_get_service_meta( $booked_service_id, 'before_buffer_time_unit' );

				if( 'h' == $bookingpress_service_buffertime_before_unit ){
					$bookingpress_service_buffertime_before = $bookingpress_service_buffertime_before * 60;
				}
				$buffer_before_time = ( $bookingpress_service_buffertime_before > 0 ) ? $bookingpress_service_buffertime_before : 0;

				$bookingpress_service_buffertime_after = $bookingpress_services->bookingpress_get_service_meta( $booked_service_id, 'after_buffer_time' );
				$bookingpress_service_buffertime_after_unit = $bookingpress_services->bookingpress_get_service_meta( $booked_service_id, 'after_buffer_time_unit' );

				if( 'h' == $bookingpress_service_buffertime_after_unit ){
					$bookingpress_service_buffertime_after = $bookingpress_service_buffertime_after * 60;
				}
				$buffer_after_time = ( $bookingpress_service_buffertime_after > 0 ) ? $bookingpress_service_buffertime_after : 0;

				$total_buffer_time = $buffer_before_time + $buffer_after_time;

				if( 1 > $total_buffer_time ){
					continue;
				}

				$booked_start_time = date('H:i', strtotime( $booked_appointment_data['bookingpress_appointment_time'] ) );
				$booked_end_time = date('H:i', strtotime( $booked_appointment_data['bookingpress_appointment_end_time'] ) );

				$buffer_start_time = date('H:i', strtotime( $booked_start_time . ' -'.$total_buffer_time.' minutes' ) );
				$buffer_end_time = date('H:i', strtotime( $booked_end_time . ' +'.$total_buffer_time.' minutes' ) );
				if( '00:00' == $buffer_end_time ){
					$buffer_end_time = '24:00';
				}

				foreach( $service_timings as $slot_key => $timeslot_data ){
					$current_time_start = $timeslot_data['store_start_time'];
					$current_time_end = $timeslot_data['store_end_time'];
					
					if( ( !empty( $booked_timing_keys ) && in_array( $slot_key, $booked_timing_keys ) ) || ( $booked_start_time == $current_time_start && $booked_end_time == $current_time_end ) ){
						continue;
					}
					
					/** if shared capacity enabled */
					if( 'true' == $shared_quantity ){
						if( ( $buffer_start_time >= $current_time_start && $buffer_end_time <= $current_time_end ) || ( $buffer_start_time < $current_time_end && $buffer_end_time > $current_time_start) ){
							if( !empty( $timeslot_data['max_capacity'] ) &&  1 == $timeslot_data['max_capaciy'] ){
								unset( $service_timings[$slot_key] );
							} else {
								if( empty( $timeslot_data['is_reduced_capacity'] ) ){
									$service_timings[$slot_key]['total_booked'] += $booked_appointment_data['bookingpress_selected_extra_members'];
									$service_timings[ $slot_key ]['max_capacity'] -= 1;
								}
							}
						}
					} else {
						if( ( $buffer_start_time >= $current_time_start && $buffer_end_time <= $current_time_end ) || ( $buffer_start_time < $current_time_end && $buffer_end_time > $current_time_start) ){
							unset( $service_timings[$slot_key] );
						}
					}
				}

			}

			return $service_timings;
		}

		function bookingpress_modify_max_available_date_with_service_expiration( $get_period_available_for_booking, $bookingpress_selected_date, $selected_service ){
			global $wpdb,$tbl_bookingpress_services;
			
			$bookingpress_service_expiration_date = $wpdb->get_var( $wpdb->prepare( 'SELECT bookingpress_service_expiration_date FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_service_id = %d', $selected_service ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm

			if( !empty( $bookingpress_service_expiration_date ) ){
				$date1 = new DateTime( $bookingpress_selected_date );
				$date2 = new DateTime( $bookingpress_service_expiration_date );
				$interval = $date1->diff($date2);
				$interval_in_days = $interval->days;
				if( $interval_in_days >= 1 ){
					$get_period_available_for_booking = $interval_in_days;
				}
			}

			return $get_period_available_for_booking;
		}

		function bookingpress_modify_default_holidays_with_service_daysoff( $retrieve_default_holidays, $selected_service, $selected_service_duration, $selected_staffmember ){
			global $wpdb, $tbl_bookingpress_service_daysoff, $BookingPress;

			$retrieve_daysoff = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_service_daysoff} WHERE bookingpress_service_id = %d", $selected_service ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_service_daysoff is table name defined globally. False Positive alarm

			$get_period_available_for_booking = $BookingPress->bookingpress_get_settings('period_available_for_booking', 'general_setting');
			if( empty( $get_period_available_for_booking ) || !$BookingPress->bpa_is_pro_active() ){
                $get_period_available_for_booking = 365;
            }

			$bookingpress_start_date = date('Y-m-d', current_time('timestamp') );

			/** Modify get available time of booking if the service expiration time is set */
			$get_period_available_for_booking = apply_filters( 'bookingpress_modify_max_available_time_for_booking', $get_period_available_for_booking, $bookingpress_start_date, $selected_service );

			$bookingpress_end_date = date('Y-m-d', strtotime( '+' . $get_period_available_for_booking . ' days') );

			if( !empty( $retrieve_daysoff ) ){

				foreach( $retrieve_daysoff as $daysoff_details_val ){

					if( 0 == $daysoff_details_val['bookingpress_service_daysoff_repeat' ] ){
						$retrieve_default_holidays['offdays'][] = array(
							'bookingpress_dayoff_date' => $daysoff_details_val['bookingpress_service_daysoff_date'],
							'bookingpress_repeat' => $daysoff_details_val['bookingpress_service_daysoff_repeat']
						);
					} else {
						$daysoff_start_date = date('Y-m-d', strtotime( $daysoff_details_val['bookingpress_service_daysoff_date'] ) );
						if( $daysoff_start_date >= $bookingpress_end_date ){
							continue;
						}
						$daysoff_end_date = date( 'Y-m-d', strtotime( $daysoff_details_val['bookingpress_service_daysoff_enddate'] ) );

						$bpa_do_frequency = !empty( $daysoff_details_val['bookingpress_service_daysoff_repeat_frequency'] ) ? $daysoff_details_val['bookingpress_service_daysoff_repeat_frequency'] : 1;
						$bpa_do_frequency_type = !empty( $daysoff_details_val['bookingpress_service_daysoff_repeat_frequency_type'] ) ? $daysoff_details_val['bookingpress_service_daysoff_repeat_frequency_type'] : 'yearly';

						if( 'week' == $bpa_do_frequency_type ){
							$bpa_do_frequency_type = 'weekly';
						} else if( 'month' == $bpa_do_frequency_type ){
							$bpa_do_frequency_type = 'monthly';
						} else if( 'day' == $bpa_do_frequency_type ){
							$bpa_do_frequency_type = 'daily';
						} else if( 'year' == $bpa_do_frequency_type ){
							$bpa_do_frequency_type = 'yearly';
						}

						$bpa_do_duration = $daysoff_details_val['bookingpress_service_daysoff_repeat_duration'];

						if( 'until' == $bpa_do_duration && strtotime( $daysoff_start_date ) >= strtotime( $daysoff_details_val['bookingpress_service_daysoff_repeat_date'] ) ){
							continue;
						}

						$bpa_do_repeat_obj = new BookingPress_Repeat_Holiday();
						
						$bpa_do_repeat_obj->startDate( new DateTime( $daysoff_start_date ) );
						$bpa_do_repeat_obj->freq( $bpa_do_frequency_type );

						$bpa_do_repeat_obj->interval( $bpa_do_frequency );

						if( 'forever' == $bpa_do_duration ){
							$bpa_do_repeat_obj->until( new DateTime( $bookingpress_end_date ) );
						} else if( 'no_of_times' == $bpa_do_duration ){
							$bpa_do_repeat_obj->count( $daysoff_details_val['bookingpress_service_daysoff_repeat_times'] );
						} else if( 'until' == $bpa_do_duration ){
							$bpa_do_repeat_obj->until( new DateTime( $daysoff_details_val['bookingpress_service_daysoff_repeat_date'] ) );
						}

						$use_multiple_dates = false;
						$days_interval = 0;
						if( $daysoff_start_date != $daysoff_end_date ){
							$begin_date = new DateTime( $daysoff_start_date );
							$end_date = new DateTime( $daysoff_end_date );
							$interval = $begin_date->diff($end_date);
							if( !empty( $interval->d ) && 1 <= $interval->d ){
								$use_multiple_dates = true;
								$days_interval = $interval->d;
							}
						}

						$bpa_do_repeat_obj->generateOccurrences();

						$all_repeated_days = $bpa_do_repeat_obj->occurrences;

						if( !empty( $all_repeated_days ) ){
							foreach( $all_repeated_days as $off_days ){
	
								if( true == $use_multiple_dates ){
									$st_date = new DateTime( $off_days->format('Y-m-d' ) );
									$en_date = new DateTime( date('Y-m-d', strtotime( $off_days->format('Y-m-d' ) . ' +'.( $days_interval + 1).' days' ) ) );
	
									$interval = DateInterval::createFromDateString('1 day');
									$period = new DatePeriod($st_date, $interval, $en_date );
									foreach ($period as $dt) {
										$retrieve_default_holidays['offdays'][] = array(
											'bookingpress_dayoff_date' => $dt->format('Y-m-d'),
											'bookingpress_repeat' => false,
										);
									}
								} else {
									$retrieve_default_holidays['offdays'][] = array(
										'bookingpress_dayoff_date' => $off_days->format('Y-m-d'),
										'bookingpress_repeat' => false,
									);
								}
							}
						}
						
					}

					/*  */
				}
			}
			
			return $retrieve_default_holidays;
		}

		function bookingpress_modify_default_holidays_with_service_special_days( $retrieve_default_holidays, $selected_service, $selected_service_duration, $selected_staffmember ){

			

			global $wpdb, $tbl_bookingpress_service_special_day;

			$retrieve_service_special_days = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_special_day_start_date, bookingpress_special_day_end_date FROM {$tbl_bookingpress_service_special_day} WHERE bookingpress_service_id = %d AND TIMESTAMPDIFF( MINUTE, bookingpress_special_day_start_time, bookingpress_special_day_end_time ) >= %d", $selected_service, $selected_service_duration ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_service_special_day is table name defined globally. False Positive alarm 

			if( !empty( $retrieve_service_special_days ) ){
				$total_sp_day = count( $retrieve_service_special_days );
				$spday_counter = 0;

				while( 0 < $total_sp_day ){

					$sp_start_date = date('Y-m-d', strtotime( $retrieve_service_special_days[ $spday_counter ]['bookingpress_special_day_start_date'] ) );
					$sp_end_date = date('Y-m-d', strtotime( $retrieve_service_special_days[ $spday_counter ]['bookingpress_special_day_end_date'] ) );


					if( $sp_start_date == $sp_end_date && !in_array( $retrieve_service_special_days[ $spday_counter ]['bookingpress_special_day_start_date'], $retrieve_default_holidays['exclude_offdays'] ) ){
						$retrieve_default_holidays['exclude_offdays'][] = date('Y-m-d H:i:s', strtotime( $sp_start_date ) );
					} else {
						$bpa_begin_date = new DateTime( $sp_start_date );
						$bpa_end_date = new DateTime( date( 'Y-m-d', strtotime( $sp_end_date . '+1 day' ) ) );
						
						$bpa_interval = DateInterval::createFromDateString('1 day');
						$period = new DatePeriod($bpa_begin_date, $bpa_interval, $bpa_end_date);

						foreach( $period as $dt ){
							$dt_formatted = $dt->format( 'Y-m-d H:i:s');
							if( !in_array( $dt_formatted, $retrieve_default_holidays['exclude_offdays'] ) ){
								$retrieve_default_holidays['exclude_offdays'][] = $dt_formatted;
							}
						}
					}

					$total_sp_day--;
					$spday_counter++;
				}
			}

			return $retrieve_default_holidays;
		}

		function bookingpress_modify_default_workhours_days( $default_off_days, $selected_service, $selected_service_duration, $selected_staffmember ){
			
			global $wpdb, $bookingpress_services, $tbl_bookingpress_service_workhours;

			//bookingpress_configure_specific_service_workhour
			if( empty( $selected_service ) || ( !empty( $default_off_days ) && true == $default_off_days['skip_check'] ) ){
				return $default_off_days;
			}

			$enable_service_workinghours = $bookingpress_services->bookingpress_get_service_meta( $selected_service, 'bookingpress_configure_specific_service_workhour' );
			if( 'true' == $enable_service_workinghours ){
				$default_off_days['skip_check'] = true;

				$service_workdays = $wpdb->get_results( $wpdb->prepare( "SELECT LOWER( bookingpress_service_workday_key ) AS bookingpress_service_workday_key FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = %d AND ( bookingpress_service_workhours_start_time IS NULL OR ( ABS( TIME_TO_SEC( TIMEDIFF( bookingpress_service_workhours_start_time, ( CASE WHEN bookingpress_service_workhours_end_time = '00:00:00' THEN '24:00:00' ELSE bookingpress_service_workhours_end_time END ) ) ) DIV 60 ) < %d ) ) ", $selected_service, 0, $selected_service_duration ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_service_workhours is table name defined globally. False Positive alarm 

				if( !empty( $service_workdays ) ){
					$total_off_days = count( $service_workdays );
					$counter = 0;
					while( 0 < $total_off_days ){

						$default_off_days['off_days'][] = $service_workdays[ $counter ]['bookingpress_service_workday_key'];

						$total_off_days--;
						$counter++;
					}					
				}
			}

			return $default_off_days;

		}

		function bookingpress_retrieve_service_shift_managment_func() {
			global $wpdb, $bookingpress_global_options, $BookingPress,$tbl_bookingpress_service_workhours, $bookingpress_services,$bookingpress_settings,$tbl_bookingpress_service_special_day,$tbl_bookingpress_service_special_day_breaks, $tbl_bookingpress_service_daysoff, $BookingPressPro;

			$response                    = array();			
			$bpa_check_authorization = $this->bpa_check_authentication( 'get_service_shift_managment', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');
				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;
				wp_send_json( $response );
				die;
			}

			$response['workhour_service_data'] = array();
			$response['workhour_data']         = array();
			$response['special_day_data']      = array();
			$response['data']                  = array();
			$response['selected_workhours']    = array();
			$response['default_break_times']   = array();

			$service_id = ! empty( $_REQUEST['service_id'] ) ? intval( $_REQUEST['service_id'] ) : '';
			$is_special_workhour_configure = ! empty( $_REQUEST['is_special_workhour_configure'] ) ? sanitize_text_field( $_REQUEST['is_special_workhour_configure']) : '';
			$bookingpress_options = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_service_workhours = $bookingpress_workhours_data = array();

			if ( ! empty( $service_id ) ) {
				/* Reterive Working hours */
				
				if(!empty($is_special_workhour_configure) && $is_special_workhour_configure == 'true' )  {

					$where_clause = $wpdb->prepare( 'bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = 0', $service_id );
					$where_clause = apply_filters('bookingpress_modify_get_service_workhour_where_clause', $where_clause, $_POST, $service_id); //phpcs:ignore
					$bookingpress_service_workhours_details = $wpdb->get_results( "SELECT bookingpress_service_workhours_start_time,bookingpress_service_workhours_end_time,bookingpress_service_workday_key FROM `{$tbl_bookingpress_service_workhours}` WHERE $where_clause", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is a table name. false alarm

					if ( ! empty( $bookingpress_service_workhours_details ) ) {
						foreach ( $bookingpress_service_workhours_details as $bookingpress_service_workhour_key => $bookingpress_service_workhour_val ) {
							$selected_start_time = $bookingpress_service_workhour_val['bookingpress_service_workhours_start_time'];
							$selected_end_time   = $bookingpress_service_workhour_val['bookingpress_service_workhours_end_time'];
							if ( $selected_start_time == null ) {
								$selected_start_time = 'Off';
							}
							if ( $selected_end_time == null ) {
								$selected_end_time = 'Off';
							}
							$bookingpress_service_workhours[ $bookingpress_service_workhour_val['bookingpress_service_workday_key'] ] = array(
								'start_time' => $selected_start_time,
								'end_time'   => $selected_end_time,
							);
						}
						$bookingpress_break_time_details = array();
						$bookingpress_days_arr = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
						foreach ( $bookingpress_days_arr as $days_key => $days_val ) {
							$bookingpress_breaks_arr = array();
							$break_where_clause = $wpdb->prepare( 'bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = 1 AND bookingpress_service_workday_key = %s', $service_id, ucfirst($days_val) );
							$break_where_clause = apply_filters('bookingpress_modify_get_service_workhour_break_where_clause', $break_where_clause, $_POST, $service_id, ucfirst($days_val)); //phpcs:ignore

							$bookingpress_break_time_details = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_service_workhours_start_time,bookingpress_service_workhours_end_time FROM `{$tbl_bookingpress_service_workhours}` WHERE bookingpress_service_workday_key = %s  AND bookingpress_service_workhours_is_break = %d AND bookingpress_service_id = %d", ucfirst($days_val),1,$service_id ),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is a table name.
							
							if ( !empty($bookingpress_break_time_details)) {
								foreach($bookingpress_break_time_details as $key => $value) {
									$bookingpress_breaks_arr[] = array(
										'start_time' => $value['bookingpress_service_workhours_start_time'],
										'formatted_start_time' => date( $bookingpress_options['wp_default_time_format'], strtotime( $value['bookingpress_service_workhours_start_time'] ) ),
										'end_time'   => $value['bookingpress_service_workhours_end_time'],
										'formatted_end_time'   => date( $bookingpress_options['wp_default_time_format'], strtotime( $value['bookingpress_service_workhours_end_time'] ) ),								
									);
								}
							}
							$bookingpress_workhours_data[] = array(
								'day_name'    => ucfirst( $days_val ),
								'break_times' => $bookingpress_breaks_arr,
							);
						}
					}					
					$bookingpress_configure_specific_service_workhour_val = $bookingpress_services->bookingpress_get_service_meta( $service_id, 'bookingpress_configure_specific_service_workhour' );
					$response['workhour_service_data']        = $bookingpress_service_workhours;
					$response['workhour_data']                = $bookingpress_workhours_data;
					$response['configure_specific_workhours'] = !empty($bookingpress_configure_specific_service_workhour_val) &&  $bookingpress_configure_specific_service_workhour_val == 'true' ? true : false;

				} else {
					$bookingpress_default_workhour_data = $bookingpress_settings->bookingpress_get_default_work_hours();
					$response['data']                = $bookingpress_default_workhour_data['data'];
					$response['selected_workhours']  = $bookingpress_default_workhour_data['selected_workhours'];
					$response['default_break_times'] = $bookingpress_default_workhour_data['default_break_times'];
				}

				/** Retrieve service daysoff data start */
				// Get Daysoff detais
				
				$bookingpress_daysoff         = array();
				$bookingpress_date_format     = $bookingpress_options['wp_default_date_format'];
				$bookingpress_time_format     = $bookingpress_options['wp_default_time_format'];
				$bookingpress_service_daysoff_details = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_service_daysoff} WHERE bookingpress_service_id = %d AND bookingpress_service_daysoff_parent = %d", $service_id, 0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_daysoff is a table name. false alarm


				if ( ! empty( $bookingpress_service_daysoff_details ) ) {
					foreach ( $bookingpress_service_daysoff_details as $day_off ) {

						$bookingpress_service_daysoff_enddate = esc_html($day_off['bookingpress_service_daysoff_enddate']);
						if($bookingpress_service_daysoff_enddate == null || $bookingpress_service_daysoff_enddate == 'null'){
							$bookingpress_service_daysoff_enddate = esc_html($day_off['bookingpress_service_daysoff_date']);
						}
												
						$day_off_arr                  = array();
						$day_off_arr['id']            = intval( $day_off['bookingpress_service_daysoff_id'] );
						$day_off_arr['dayoff_name']   = sanitize_text_field( $day_off['bookingpress_service_daysoff_name'] );
						$day_off_arr['dayoff_date']   = sanitize_text_field( $day_off['bookingpress_service_daysoff_date'] );
						$day_off_arr['dayoff_date_end']   = $bookingpress_service_daysoff_enddate;
						$day_off_arr['dayoff_formatted_date']   = date($bookingpress_date_format,strtotime($day_off['bookingpress_service_daysoff_date']));
						$day_off_arr['dayoff_repeat'] = ! empty( $day_off['bookingpress_service_daysoff_repeat'] ) ? true : false;

						$dayoff_label = esc_html__( 'Once Off', 'bookingpress-appointment-booking' );
						$day_off_arr['dayoff_repeat_label'] = $dayoff_label;
						if( true == $day_off_arr['dayoff_repeat'] ){
							$dayoff_label = esc_html__( 'Repeat Yearly', 'bookingpress-appointment-booking' );
							$repeat_frequency = $day_off['bookingpress_service_daysoff_repeat_frequency'];
							$repeat_frequency_type = $day_off['bookingpress_service_daysoff_repeat_frequency_type'];
							$repeat_duration = $day_off['bookingpress_service_daysoff_repeat_duration'];
							$repeat_times = $day_off['bookingpress_service_daysoff_repeat_times'];
							$repeat_date = $day_off['bookingpress_service_daysoff_repeat_date'];
							$day_off_arr['dayoff_repeat_label'] = $BookingPressPro->bookingpress_retrieve_daysoff_repeat_label( $repeat_duration, $repeat_frequency, $repeat_frequency_type, $repeat_times, $repeat_date );
							$day_off_arr['repeat_frequency'] = $repeat_frequency;
							$day_off_arr['repeat_frequency_type'] = $repeat_frequency_type;
							$day_off_arr['repeat_duration'] = $repeat_duration;
							$day_off_arr['repeat_times'] = $repeat_times;
							$day_off_arr['repeat_date'] = $repeat_date;
						}
						

						$bookingpress_daysoff[]       = $day_off_arr;
					}
				}

				$response['daysoff_data'] = $bookingpress_daysoff;
				/** Retrieve service daysoff data end */

				/* retrieve special days data */
				$bookingpress_date_format     = $bookingpress_options['wp_default_date_format'];
				$bookingpress_time_format     = $bookingpress_options['wp_default_time_format'];
				
				$bookingpress_special_day      = array();
				$bookingpress_special_day_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_service_special_day . ' WHERE bookingpress_service_id = %d ', $service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_service_special_day is a table name. false alarm

				if ( ! empty( $bookingpress_special_day_data ) ) {
					foreach ( $bookingpress_special_day_data as $special_day_key => $special_day ) {
						$special_day_arr        = $special_days_breaks = array();
						$special_day_start_date = ! empty( $special_day['bookingpress_special_day_start_date'] ) ? sanitize_text_field( $special_day['bookingpress_special_day_start_date'] ) : '';
						$special_day_end_date   = ! empty( $special_day['bookingpress_special_day_end_date'] ) ? sanitize_text_field( $special_day['bookingpress_special_day_end_date'] ) : '';

						$special_day_id                                      = ! empty( $special_day['bookingpress_service_special_day_id'] ) ? intval( $special_day['bookingpress_service_special_day_id'] ) : '';
								$special_day_arr['id']                       = $special_day_id;
						$special_day_arr['special_day_start_date']           = $special_day_start_date;
						$special_day_arr['special_day_formatted_start_date'] = date( $bookingpress_date_format, strtotime( $special_day_start_date ) );
						$special_day_arr['special_day_end_date']             = $special_day_end_date;
						$special_day_arr['special_day_formatted_end_date']   = date( $bookingpress_date_format, strtotime( $special_day_end_date ) );
						$special_day_arr['start_time']                       = $special_day['bookingpress_special_day_start_time'];
						$special_day_arr['formatted_start_time']             = date( $bookingpress_time_format, strtotime( $special_day['bookingpress_special_day_start_time'] ) );
						$special_day_arr['end_time']                         = $special_day['bookingpress_special_day_end_time'];
						$special_day_arr['formatted_end_time']               = date( $bookingpress_time_format, strtotime( $special_day['bookingpress_special_day_end_time'] ) )." ".($special_day['bookingpress_special_day_end_time'] == "24:00:00" ? esc_html__('Next Day', 'bookingpress-appointment-booking') : '' );
						$special_day_arr['special_day_service']              = $service_id;

						// Fetch all breaks associated with special day
						$bookingpress_special_days_break = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_service_special_day_breaks . ' WHERE bookingpress_special_day_id = %d ', $special_day_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_service_special_day_breaks is a table name. false alarm
						if ( ! empty( $bookingpress_special_days_break ) && is_array( $bookingpress_special_days_break ) ) {
							foreach ( $bookingpress_special_days_break as $k3 => $v3 ) {
								$break_start_time                      = ! empty( $v3['bookingpress_special_day_break_start_time'] ) ? sanitize_text_field( $v3['bookingpress_special_day_break_start_time'] ) : '';
								$break_end_time                        = ! empty( $v3['bookingpress_special_day_break_end_time'] ) ? sanitize_text_field( $v3['bookingpress_special_day_break_end_time'] ) : '';
								$i                                     = 1;
								$special_days_break_data               = array();
								$special_days_break_data['id']         = $i;
								$special_days_break_data['start_time'] = $break_start_time;
								$special_days_break_data['end_time']   = $break_end_time;
								$special_days_break_data['formatted_start_time'] = date( $bookingpress_time_format, strtotime( $break_start_time ) );
								$special_days_break_data['formatted_end_time']   = date( $bookingpress_time_format, strtotime( $break_end_time ) );
								$special_days_breaks[]                           = $special_days_break_data;
								$i++;
							}
						}
						$special_day_arr['special_day_workhour'] = $special_days_breaks;
							$bookingpress_special_day[]          = $special_day_arr;
					}
				}
				$disabled_special_day_data             = $this->bookingpress_get_service_special_days_dates();								
				$response['special_day_data']          = $bookingpress_special_day;
				$response['disabled_special_day_data'] = $disabled_special_day_data;				
				$response['variant']                      = 'success';
				$response['title']                        = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']                          = esc_html__( 'Service Shift Managment data retrieve successfully', 'bookingpress-appointment-booking' );
			}

				$response = apply_filters( 'bookingpress_modify_service_shift_management_data', $response );
				echo wp_json_encode( $response );
				exit;
		}

		function bookingpress_after_close_add_service_form_func() {
			?>
			vm2.open_add_new_category_popup = false;
			<?php
		}

		function bookingpress_modify_edit_service_data_func($response,$service_id) {
				global $wpdb,$tbl_bookingpress_services;	
				$bookingpress_service_expiration_date = $wpdb->get_var( $wpdb->prepare( 'SELECT bookingpress_service_expiration_date FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_service_id = %d', $service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
				$bookingpress_service_start_date = $wpdb->get_var( $wpdb->prepare( 'SELECT bookingpress_service_start_date FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_service_id = %d', $service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
				$response['service_expiration_date'] = !empty($bookingpress_service_expiration_date) ? esc_html($bookingpress_service_expiration_date): '';
				$response['service_start_date'] = !empty($bookingpress_service_start_date) ? esc_html($bookingpress_service_start_date): '';

				$response['staffmember_service_data']  = $this->bookingpress_get_staffmember_service_data_func();		
				if( !isset( $response['minimum_time_required_before_booking'])){
					$response['minimum_time_required_before_booking'] = 'inherit';
				}
				if( !isset( $response['minimum_time_required_before_rescheduling'])){
					$response['minimum_time_required_before_rescheduling'] = 'inherit';
				}
				if( !isset( $response['minimum_time_required_before_cancelling'])){
					$response['minimum_time_required_before_cancelling'] = 'inherit';
				}	
				return $response;
		}

		function bookingpress_format_assigned_staffmember_service_amounts_func() {
			global $wpdb, $bookingpress_global_options, $BookingPress;
			$response                    = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'format_assigned_staff_service_amount', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			$response['assign_staff_member_list'] = '';

			$bookingpress_assign_staff_list = ! empty( $_POST['assign_staff_member_list'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['assign_staff_member_list']) : array(); //phpcs:ignore

			if(!empty($bookingpress_assign_staff_list)){
				foreach($bookingpress_assign_staff_list as $assign_service_list_key => $assign_service_list_val){
					$bookingpress_assign_staff_list[$assign_service_list_key]['staffmember_price_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($assign_service_list_val['staffmember_price']);

					$bookingpress_assign_staff_list[$assign_service_list_key]['staffmember_name'] = !empty($assign_service_list_val['staffmember_name']) ? stripslashes_deep($assign_service_list_val['staffmember_name']) : '';
					
					if(!empty($assign_service_list_val['staffmember_custom_service'])) {
						$staffmember_custom_service = $assign_service_list_val['staffmember_custom_service'];
						foreach($staffmember_custom_service as $key => $val) {																				
							$bookingpress_assign_staff_list[$assign_service_list_key]['staffmember_custom_service'][$key]['service_formatted_price'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($val['service_price']);
						}
					}
				}	

				$bookingpress_assign_staff_list = apply_filters( 'bookingpress_modify_staff_with_formatting', $bookingpress_assign_staff_list );

				$response['variant'] = 'success';
				$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
				$response['msg'] = esc_html__('Assigned staffmember service formatted successfully', 'bookingpress-appointment-booking');
				$response['assign_staff_member_list'] = $bookingpress_assign_staff_list;
			}

			echo wp_json_encode($response);
			exit;

			
		}

		function bookingpress_get_shared_capacity_data_func( $shared_quantity ){

			global $BookingPress;

			$shared_quantity = $BookingPress->bookingpress_get_settings( 'share_quanty_between_timeslots', 'general_setting');

			return $shared_quantity;
		}

		function bookingpress_duplicate_more_details_func($duplicated_service_id, $original_service_id){
			global $wpdb, $BookingPress, $tbl_bookingpress_services, $tbl_bookingpress_extra_services, $tbl_bookingpress_staffmembers_services, $bookingpress_services;

			//Duplicate all extra services
			$bookingpress_extra_services = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_service_id = %d", $original_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is table name.
			if(!empty($bookingpress_extra_services)){
				foreach($bookingpress_extra_services as $extra_service_key => $extra_service_val){
					$bookingpress_duplicate_extra_service_arr = array(
						'bookingpress_service_id' => $duplicated_service_id,
						'bookingpress_extra_service_name' => $extra_service_val['bookingpress_extra_service_name'],
						'bookingpress_extra_service_duration' => $extra_service_val['bookingpress_extra_service_duration'],
						'bookingpress_extra_service_duration_unit' => $extra_service_val['bookingpress_extra_service_duration_unit'],
						'bookingpress_extra_service_price' => $extra_service_val['bookingpress_extra_service_price'],
						'bookingpress_extra_service_max_quantity' => $extra_service_val['bookingpress_extra_service_max_quantity'],
						'bookingpress_service_description' => $extra_service_val['bookingpress_service_description'],
					);

					$wpdb->insert($tbl_bookingpress_extra_services, $bookingpress_duplicate_extra_service_arr);
					$duplicate_extra_service_id = $wpdb->insert_id;
					$orignal_extra_service_id = $extra_service_val['bookingpress_extra_services_id'];
					do_action('bookingpress_after_duplicate_service_extra', $duplicate_extra_service_id,$orignal_extra_service_id );
				}
			}

			//Duplicate all staff members details
			$bookingpress_assigned_staffmembers = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_service_id = %d", $original_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
			if(!empty($bookingpress_assigned_staffmembers)){
				foreach($bookingpress_assigned_staffmembers as $assigned_staff_key => $assigned_staff_val){
					$bookingpress_duplicate_staff_services = array(
						'bookingpress_staffmember_id' => $assigned_staff_val['bookingpress_staffmember_id'],
						'bookingpress_service_id' => $duplicated_service_id,
						'bookingpress_service_price' => $assigned_staff_val['bookingpress_service_price'],
						'bookingpress_service_capacity' => $assigned_staff_val['bookingpress_service_capacity'],
						'bookingpress_service_min_capacity' => $assigned_staff_val['bookingpress_service_min_capacity'],
					);

					$wpdb->insert($tbl_bookingpress_staffmembers_services, $bookingpress_duplicate_staff_services);
				}
			}

			//Duplicate service capacity
			$bookingpress_original_max_capacity = $bookingpress_services->bookingpress_get_service_meta( $original_service_id, 'max_capacity' );
			$bookingpress_services->bookingpress_add_service_meta( $duplicated_service_id, 'max_capacity', $bookingpress_original_max_capacity );

		}

		function bookingpress_calculate_service_break_data( $service_timing_arr, $service_break_data ){

			$slot_start_time = $service_timing_arr['store_start_time'];
			$slot_end_time = $service_timing_arr['store_end_time'];

			if( !empty( $service_break_data ) ){

				foreach( $service_break_data as $blocked_time ){
					$blocked_start_time = $blocked_time['break_start_time'];
					$blocked_end_time = $blocked_time['break_end_time'];
					
					if( ( $blocked_start_time >= $slot_start_time && $blocked_end_time <= $slot_end_time ) || ( $blocked_start_time < $slot_end_time && $blocked_end_time > $slot_start_time) ){
						$service_timing_arr['is_blocked'] = true;
						$service_timing_arr['break_start_time'] = $blocked_start_time;
						$service_timing_arr['break_end_time'] = $blocked_end_time;
					}
				}

            }

			return $service_timing_arr;
		}

		function bookingpress_retrieve_service_timings( $service_timings_data, $selected_service_id, $selected_date, $minimum_time_required, $service_max_capacity, $bookingpress_show_time_as_per_service_duration ){
			
			if( !empty( $service_timings_data['service_timings'] ) || true == $service_timings_data['is_daysoff'] || empty( $selected_service_id ) ){
				return $service_timings_data;
			}
			
			global $wpdb, $BookingPress, $BookingPressPro, $tbl_bookingpress_services, $tbl_bookingpress_service_workhours, $tbl_bookingpress_service_daysoff;

			$current_day  = ! empty( $selected_date ) ? ucfirst( date( 'l', strtotime( $selected_date ) ) ) : ucfirst( date( 'l', current_time( 'timestamp' ) ) );
			$current_date = ! empty($selected_date) ? date('Y-m-d', strtotime($selected_date)) : date('Y-m-d', current_time('timestamp'));
			
			$bookingpress_timezone = isset($_POST['client_timezone_offset']) ? sanitize_text_field( $_POST['client_timezone_offset'] ) : '';  // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
			
			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );
			$display_slots_in_client_timezone = false;
			
			// 04May 2023 Changes
			$client_timezone_string = !empty( $_COOKIE['bookingpress_client_timezone'] ) ? sanitize_text_field($_COOKIE['bookingpress_client_timezone']) : '';
            if( 'true' == $bookingpress_timeslot_display_in_client_timezone && !empty( $client_timezone_string ) ){
                $client_timezone_offset = $BookingPress->bookingpress_convert_timezone_to_offset( $client_timezone_string, $bookingpress_timezone );
                $wordpress_timezone_offset = $BookingPress->bookingpress_convert_timezone_to_offset( wp_timezone_string() );                
                if( $client_timezone_offset  == $wordpress_timezone_offset ){
                    $bookingpress_timeslot_display_in_client_timezone = 'false';
                }
            }
			// 04May 2023 Changes


			if( isset($bookingpress_timezone) && '' !== $bookingpress_timezone && !empty($bookingpress_timeslot_display_in_client_timezone) && ($bookingpress_timeslot_display_in_client_timezone == 'true')){	
				$display_slots_in_client_timezone = true;
			}

			$bookingpress_current_time = date( 'H:i',current_time('timestamp'));
			$bpa_current_datetime = date( 'Y-m-d H:i:s',current_time('timestamp'));

			$bpa_current_date = date('Y-m-d', current_time('timestamp'));

			if( strtotime( $bpa_current_date ) > strtotime( $selected_date ) && false == $display_slots_in_client_timezone ){
                return $service_timings_data;
            }

			$bookingpress_hide_already_booked_slot = $BookingPress->bookingpress_get_customize_settings( 'hide_already_booked_slot', 'booking_form' );
			$bookingpress_hide_already_booked_slot = ( $bookingpress_hide_already_booked_slot == 'true' ) ? 1 : 0;

			$change_store_date = ( !empty( $_POST['bpa_change_store_date'] ) && 'true' == $_POST['bpa_change_store_date'] ) ? true : false; //phpcs:ignore
			
			$bpa_current_time = date( 'H:i',current_time('timestamp'));

			$bookingpress_current_time_timestamp = current_time('timestamp');

			$service_time_duration     = $BookingPress->bookingpress_get_default_timeslot_data();
			$service_step_duration_val = $service_time_duration['default_timeslot'];
			
			if (! empty($selected_service_id) ) {
				$service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $selected_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_services is a table name
				if (! empty($service_data) ) {
					$service_time_duration      = esc_html($service_data['bookingpress_service_duration_val']);
					$service_time_duration_unit = esc_html($service_data['bookingpress_service_duration_unit']);
					if ($service_time_duration_unit == 'h' ) {
						$service_time_duration = $service_time_duration * 60;
					} elseif($service_time_duration_unit == 'd') {           
						$service_time_duration = $service_time_duration * 24 * 60;
					}
					$default_timeslot_step = $service_step_duration_val = $service_time_duration;
				}
			}

			$bpa_fetch_updated_slots = false;
            if( isset( $_POST['bpa_fetch_data'] ) && 'true' == $_POST['bpa_fetch_data'] ){ //phpcs:ignore 
                $bpa_fetch_updated_slots = true;
            }
			$service_step_duration_val = apply_filters( 'bookingpress_modify_service_timeslot', $service_step_duration_val, $selected_service_id, $service_time_duration_unit, $bpa_fetch_updated_slots );

			$bookingpress_show_time_as_per_service_duration = $BookingPress->bookingpress_get_settings( 'show_time_as_per_service_duration', 'general_setting' );
            if ( ! empty( $bookingpress_show_time_as_per_service_duration ) && $bookingpress_show_time_as_per_service_duration == 'false' ) {
                $bookingpress_default_time_slot = $BookingPress->bookingpress_get_settings( 'default_time_slot', 'general_setting' );
                $default_timeslot_step      = $bookingpress_default_time_slot;
            } else {
				$default_timeslot_step		= $service_step_duration_val;
			}

			$workhour_data = array();

			/** Service Holidays */
			$bpa_get_service_holiday = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_service_daysoff} WHERE ( bookingpress_service_daysoff_date = %s OR bookingpress_service_daysoff_repeat = %d )AND bookingpress_service_id = %d", $selected_date, 1, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_daysoff is a table name.

			$is_holiday = false;
			if( !empty( $bpa_get_service_holiday ) ){
				foreach( $bpa_get_service_holiday as $service_holiday_data ){
					$hsf_date = $service_holiday_data->bookingpress_service_daysoff_date;
					$hsf_is_repeat = $service_holiday_data->bookingpress_service_daysoff_repeat;

					if( 0 == $hsf_is_repeat && $hsf_date == $selected_date ){
						$is_holiday = true;
						break;	
					}
				}
			}
			if( true == $is_holiday ){
				return $service_timings_data;
			}


			/** Service Special Days */
			$bookingpress_special_day_details = $BookingPressPro->bookingpress_get_service_special_days($selected_service_id, $selected_date);
			
			if( !empty( $bookingpress_special_day_details ) ){
				
				$service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_special_day_details['special_day_start_time'])), $selected_service_id );
				$service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_special_day_details['special_day_end_time'])), $selected_service_id );
			
				
				if( '00:00' == $service_end_time ){
					$service_end_time = '24:00';
				}

				if ($service_start_time != null && $service_end_time != null ) {
					
					while ( $service_current_time <= $service_end_time ) {
						if ($service_current_time > $service_end_time ) {
							break;
						}						
						
						$service_tmp_date_time = $selected_date .' '.$service_current_time;
						$service_tmp_end_time = date( 'Y-m-d', ( strtotime($service_tmp_date_time) + ( $service_step_duration_val * 60 ) ) );
						if( $service_tmp_end_time > $selected_date  ){
							if( 1440 < $service_step_duration_val && $service_time_duration_unit != 'd' ){
								break;
							}
						}
						
						$service_tmp_current_time = $service_current_time;
						
						if ($service_current_time == '00:00' ) {
							$service_current_time = date('H:i', strtotime($service_current_time) + ( $service_step_duration_val * 60 ));
						} else {
							$service_tmp_time_obj = new DateTime($selected_date .' ' . $service_current_time);
							$service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
							$service_current_time = $service_tmp_time_obj->format('H:i');

							$service_current_date = $service_tmp_time_obj->format('Y-m-d');
                            if( $service_current_date > $selected_date ){
								if( $service_end_time == '24:00' && strtotime($service_current_date.' '.$service_current_time) > strtotime( $service_current_date . ' 00:00' ) ){
                                    break;
                                }
							}
						}

						$break_start_time      = '';
                        $break_end_time        = '';
						/** service special days break hour logic start */
						
						/** service special days break hour logic end */

						if ($service_current_time < $service_start_time || $service_current_time == $service_start_time ) {
							$service_current_time = $service_end_time;
						}
						
						$is_booked_for_minimum = false;
						if( 'disabled' != $minimum_time_required ){
							$bookingpress_slot_start_datetime       = $selected_date . ' ' . $service_tmp_current_time . ':00';
							$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
							$bookingpress_time_diff = round( abs( current_time('timestamp') - $bookingpress_slot_start_time_timestamp ) / 60, 2 );
							
							if( $bookingpress_time_diff <= $minimum_time_required ){
								$is_booked_for_minimum = true;
								//$booked_with_minimum_required++;
								//continue;
							}
						}

						$bookingpress_timediff_in_minutes = round(abs(strtotime($service_current_time) - strtotime($service_tmp_current_time)) / 60, 2);
						$is_already_booked = 0;
						if ($is_already_booked == 1 && $bookingpress_hide_already_booked_slot == 1 ) {
							continue;
						} else {
							if ($break_start_time != $service_tmp_current_time && $bookingpress_timediff_in_minutes >= $service_step_duration_val && $service_current_time <= $service_end_time ) {
								if ($bpa_current_date == $selected_date ) {
									if ($service_tmp_current_time > $bpa_current_time && !$is_booked_for_minimum ) {
										$service_timing_arr = array(
											'start_time' => $service_tmp_current_time,
											'end_time'   => $service_current_time,
											'break_start_time' => $break_start_time,
											'break_end_time' => $break_end_time,
											'store_start_time' => $service_tmp_current_time,
											'store_end_time' => $service_current_time,
											'store_service_date' => $selected_date,
											'is_booked'  => 0,
											'max_capacity' => $service_max_capacity,
											'total_booked' => 0
										);

										if( !empty( $bookingpress_special_day_details['special_day_breaks'] ) ){
											$service_timing_arr = apply_filters( 'bpa_calculate_service_breakhours_data', $service_timing_arr, $bookingpress_special_day_details['special_day_breaks'] );
										}

										if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
											$service_current_time = $service_timing_arr['break_end_time'];
											continue;
										}

										if( $display_slots_in_client_timezone ){

											$booking_timeslot_start = $selected_date.' '.$service_tmp_current_time.':00';
											$booking_timeslot_end = $selected_date .' '.$service_current_time.':00';
											
											
											$booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
											$booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);
											
											$service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start) );
											$service_timing_arr['end_time'] = date('H:i', strtotime( $booking_timeslot_end ) );

											$booking_timeslot_start_date = date('Y-m-d', strtotime( $booking_timeslot_start ) );

											if( $change_store_date ) {

												$store_selected_date = apply_filters( 'bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone );
												
												$service_timing_arr['store_service_date'] = $store_selected_date;
												
												$store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
												if( strtotime( $store_selection_datetime ) < current_time('timestamp' ) || $store_selected_date != $selected_date ){
													continue;
												}
											}

											if( $selected_date < $booking_timeslot_start_date){
												break;
											}
										}
										$workhour_data[] = $service_timing_arr;
									}else {
										$service_timings_data['is_daysoff'] = true;
									}
								} else {
									if( !$is_booked_for_minimum ){
										$service_timing_arr = array(
											'start_time' => $service_tmp_current_time,
											'end_time'   => $service_current_time,
											'break_start_time' => $break_start_time,
											'break_end_time' => $break_end_time,
											'store_start_time' => $service_tmp_current_time,
											'store_end_time' => $service_current_time,
											'store_service_date' => $selected_date,
											'is_booked'  => 0,
											'max_capacity' => $service_max_capacity,
											'total_booked' => 0
										);

										

										if( !empty( $bookingpress_special_day_details['special_day_breaks'] ) ){
											$service_timing_arr = apply_filters( 'bpa_calculate_service_breakhours_data', $service_timing_arr, $bookingpress_special_day_details['special_day_breaks'] );
										}

										if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
											$service_current_time = $service_timing_arr['break_end_time'];
											continue;
										}
										
										if( $display_slots_in_client_timezone ){

											$booking_timeslot_start = $selected_date.' '.$service_tmp_current_time.':00';
											$booking_timeslot_end = $selected_date .' '.$service_current_time.':00';
											
											
											$booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
											$booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);
											
											$service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start) );
											$service_timing_arr['end_time'] = date('H:i', strtotime( $booking_timeslot_end ) );

											$booking_timeslot_start_date = date('Y-m-d', strtotime( $booking_timeslot_start ) );

											if( $change_store_date ) {

												$store_selected_date = apply_filters( 'bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone );
												
												$service_timing_arr['store_service_date'] = $store_selected_date;
												
												$store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
												if( strtotime( $store_selection_datetime ) < current_time('timestamp' ) || $store_selected_date != $selected_date ){
													continue;
												}
											}
											if( $selected_date < $booking_timeslot_start_date){
												break;
											}
										}
										$workhour_data[] = $service_timing_arr;
									}else {
										$service_timings_data['is_daysoff'] = true;
									}
								}
							} else {
								if($service_current_time >= $service_end_time){
									break;
								}
							}
						}

						if (! empty($break_end_time) ) {
							$service_current_time = $break_end_time;
						}
		
						if ($service_current_time == $service_end_time ) {
							break;
						}

						if(!empty($default_timeslot_step) && $default_timeslot_step != $service_step_duration_val && empty($break_start_time)){
							$service_tmp_time_obj = new DateTime($selected_date . ' ' . $service_tmp_current_time);
							$service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
							$service_current_time = $service_tmp_time_obj->format('H:i');
							
							$service_current_date = $service_tmp_time_obj->format('Y-m-d');
							if( $service_current_date > $selected_date ){
								break;
							}
						}
						
					}
					if( empty( $workhour_data ) ){
						$service_timings_data['is_daysoff'] = true;
					}
					$service_timings_data['service_timings'] = $workhour_data;
					return $service_timings_data;
				}			
			}
			
			$bookingpress_service_default_workhours = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = 0 AND bookingpress_service_workday_key = %s", $selected_service_id, $current_day), ARRAY_A);  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is table name.

			$bookingpress_service_default_workhours = apply_filters('bookingpress_modify_service_default_workhours', $bookingpress_service_default_workhours, $selected_service_id, $current_day);
			
			if( !empty( $bookingpress_service_default_workhours ) ){

				if( empty( $bookingpress_service_default_workhours['bookingpress_service_workhours_start_time'] ) ){
					$service_timings_data['is_daysoff'] = true;
					return $service_timings_data;
				}

				$bpa_service_breakhours_data = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_service_workhours_start_time, bookingpress_service_workhours_end_time FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = 1 AND bookingpress_service_workday_key = %s", $selected_service_id, $current_day), ARRAY_A);  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is table name.

				$bpa_service_break_data = array();
				if( !empty( $bpa_service_breakhours_data ) ){
					foreach( $bpa_service_breakhours_data as $sv_break_data ){
						$bpa_service_break_data[] = array(
							'break_start_time' => date( 'H:i', strtotime( $sv_break_data['bookingpress_service_workhours_start_time'] ) ),
							'break_end_time' => date( 'H:i', strtotime( $sv_break_data['bookingpress_service_workhours_end_time'] ) ),
						);
					}
				}

				$service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_service_default_workhours['bookingpress_service_workhours_start_time'])), $selected_service_id );
				$service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_service_default_workhours['bookingpress_service_workhours_end_time'])), $selected_service_id );
				
				if( '00:00' == $service_end_time ){
					$service_end_time = '24:00';
				}
				
				if ($service_start_time != null && $service_end_time != null ) {
					
					while ( $service_current_time <= $service_end_time ) {
						
						if ($service_current_time > $service_end_time ) {
							break;
						}

						$service_tmp_date_time = $selected_date .' '.$service_current_time;
						$service_tmp_end_time = date( 'Y-m-d', ( strtotime($selected_date. ' ' . $service_current_time ) + ( $service_step_duration_val * 60 ) ) );

						if( $service_tmp_end_time > $selected_date  ){
							if( 1440 < $service_step_duration_val && $service_time_duration_unit != 'd' ){
								break;
							}
						}

						$service_tmp_current_time = $service_current_time;
						
						if ($service_current_time == '00:00'  ) {
							$service_current_time = date('H:i', strtotime($service_current_time) + ( $service_step_duration_val * 60 ));
						} else {
							$service_tmp_time_obj = new DateTime($selected_date . ' ' . $service_current_time);
							$service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
							$service_current_time = $service_tmp_time_obj->format('H:i');
							$service_current_date = $service_tmp_time_obj->format('Y-m-d');
                            if( $service_current_date > $selected_date ){
								if( $service_end_time == '24:00' && strtotime($service_current_date.' '.$service_current_time) > strtotime( $service_current_date . ' 00:00' ) ){
                                    break;
                                }
							}
						}

						$break_start_time = '';
						$break_end_time = '';
						/** service work hour break logic start here */

						/** service work hour break logic ends here */


						if ($service_current_time < $service_start_time || $service_current_time == $service_start_time ) {
							$service_current_time = $service_end_time;
						}
						
 						$bookingpress_timediff_in_minutes = round(abs(strtotime($service_current_time) - strtotime($service_tmp_current_time)) / 60, 2);

						$is_already_booked = 0;
						$is_booked_for_minimum = false;
						if( 'disabled' != $minimum_time_required ){
							$bookingpress_slot_start_datetime       = $selected_date . ' ' . $service_tmp_current_time . ':00';
							$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
							$bookingpress_time_diff = round( abs( $bookingpress_current_time_timestamp - $bookingpress_slot_start_time_timestamp ) / 60, 2 );
							if( $bookingpress_time_diff <= $minimum_time_required ){
								$is_booked_for_minimum = true;
							}
						}
						
						if ($break_start_time != $service_tmp_current_time && $bookingpress_timediff_in_minutes >= $service_step_duration_val && $service_current_time <= $service_end_time ) {

							if ($bpa_current_date == $current_date ) {
								if ($service_tmp_current_time > $bpa_current_time && !$is_booked_for_minimum ) {
									$service_timing_arr = array(
										'start_time' => $service_tmp_current_time,
										'end_time'   => $service_current_time,
										'break_start_time' => $break_start_time,
										'break_end_time' => $break_end_time,
										'store_start_time' => $service_tmp_current_time,
										'store_end_time' => $service_current_time,
										'store_service_date' => $selected_date,
										'is_booked'  => $is_already_booked,
										'max_capacity' => $service_max_capacity,
										'total_booked' => 0
									);

									if( !empty( $bpa_service_break_data ) ){
										$service_timing_arr = apply_filters( 'bpa_calculate_service_breakhours_data', $service_timing_arr, $bpa_service_break_data );
									}
									
									if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
										$service_current_time = $service_timing_arr['break_end_time'];
										continue;
									}

									if( $display_slots_in_client_timezone ){

										$booking_timeslot_start = $selected_date.' '.$service_tmp_current_time.':00';
										$booking_timeslot_end = $selected_date .' '.$service_current_time.':00';
										
										
										$booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
										$booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);
										
										$service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start) );
										$service_timing_arr['end_time'] = date('H:i', strtotime( $booking_timeslot_end ) );

										$booking_timeslot_start_date = date('Y-m-d', strtotime( $booking_timeslot_start ) );

										if( $change_store_date ) {

                                            $store_selected_date = apply_filters( 'bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone );
                                            
                                            $service_timing_arr['store_service_date'] = $store_selected_date;
                                            
                                            $store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
                                            if( strtotime( $store_selection_datetime ) < current_time('timestamp' ) || $store_selected_date != $selected_date ){
                                                continue;
                                            }
                                        }
										if( $selected_date < $booking_timeslot_start_date){
											break;
										}
									}
									$workhour_data[] = $service_timing_arr;
								} else {
									$service_timings_data['is_daysoff'] = true;
								}
							} else {
								
								if(  !$is_booked_for_minimum ){
									$service_timing_arr = array(
										'start_time' => $service_tmp_current_time,
										'end_time'   => $service_current_time,
										'break_start_time' => $break_start_time,
										'break_end_time' => $break_end_time,
										'store_start_time' => $service_tmp_current_time,
										'store_end_time' => $service_current_time,
										'store_service_date' => $selected_date,
										'is_booked'  => $is_already_booked,
										'max_capacity' => $service_max_capacity,
										'total_booked' => 0
									);

									if( !empty( $bpa_service_break_data ) ){
										$service_timing_arr = apply_filters( 'bpa_calculate_service_breakhours_data', $service_timing_arr, $bpa_service_break_data );
									}
									
									if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
										$service_current_time = $service_timing_arr['break_end_time'];
										continue;
									}

									if( $display_slots_in_client_timezone ){

										$booking_timeslot_start = $selected_date.' '.$service_tmp_current_time.':00';
										$booking_timeslot_end = $selected_date .' '.$service_current_time.':00';
										
										
										$booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
										$booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);
										
										$service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start) );
										$service_timing_arr['end_time'] = date('H:i', strtotime( $booking_timeslot_end ) );

										$booking_timeslot_start_date = date('Y-m-d', strtotime( $booking_timeslot_start ) );

										if( $change_store_date ) {

                                            $store_selected_date = apply_filters( 'bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone );
                                            
                                            $service_timing_arr['store_service_date'] = $store_selected_date;
                                            
                                            $store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
                                            if( strtotime( $store_selection_datetime ) < current_time('timestamp' ) || $store_selected_date != $selected_date ){
                                                continue;
                                            }
                                        }
										if( $selected_date < $booking_timeslot_start_date){
											break;
										}
									}
									$workhour_data[] = $service_timing_arr;
								} else {
									$service_timings_data['is_daysoff'] = true;
								}
							}
						} else {
							if($service_current_time >= $service_end_time){
								break;
							}
						}

						if (! empty($break_end_time) ) {
							$service_current_time = $break_end_time;
						}
		
						if ($service_current_time == $service_end_time ) {
							break;
						}

						if(!empty($default_timeslot_step) && $default_timeslot_step != $service_step_duration_val && empty($break_start_time)){

							$service_tmp_time_obj = new DateTime($selected_date . ' ' . $service_tmp_current_time);
							$service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
							$service_current_time = $service_tmp_time_obj->format('H:i');
							
							$service_current_date = $service_tmp_time_obj->format('Y-m-d');
							if( $service_current_date > $selected_date ){
								break;
							}
						}
					}
					if( empty( $workhour_data ) ){
						$service_timings_data['is_daysoff'] = true;
					}
					$service_timings_data['service_timings'] = $workhour_data;
					return $service_timings_data;
				}
			}

			return $service_timings_data;
		}

		function bookingpress_get_service_minimun_capacity( $min_service_capacity, $selected_service_id ){

			global $bookingpress_pro_staff_members, $wpdb, $tbl_bookingpress_staffmembers_services, $bookingpress_services, $BookingPress;

			$bpa_is_staffmember_module_active = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();

			if( empty( $selected_service_id ) && !empty( $_POST['appointment_data_obj']['selected_service'] ) ){ // phpcs:ignore
				$selected_service_id = intval( $_POST['appointment_data_obj']['selected_service'] ); // phpcs:ignore
			}

			$skip_checking_service_capacity = false;

			$skip_checking_service_capacity = apply_filters( 'bpa_skip_checking_capacity', $skip_checking_service_capacity, $selected_service_id );

			if( $bpa_is_staffmember_module_active && false == $skip_checking_service_capacity ){
				$staffmember_id = !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ? intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
				
				if( empty( $staffmember_id ) ){
					if( empty( $_POST['appointment_data_obj'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
						$_POST['appointment_data_obj'] = !empty( $_POST['appointment_data'] ) ? array_map( array($BookingPress, 'appointment_sanatize_field'), $_POST['appointment_data'] ) : array();  // phpcs:ignore
					}
					$staffmember_id = !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
				}
				if( !empty( $staffmember_id ) ){
					$staffmember_min_capacity = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_min_capacity FROM `{$tbl_bookingpress_staffmembers_services}` WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $staffmember_id, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
					
					if( !empty( $staffmember_min_capacity ) ){
						$min_service_capacity = $staffmember_min_capacity;
						$skip_checking_service_capacity = true;
					}
				}
			}

			if( false == $skip_checking_service_capacity ){
				/** Retrieve Service Max Capacity */
				$service_max_capacity = $bookingpress_services->bookingpress_get_service_meta( $selected_service_id, 'min_capacity' );
				if( !empty( $service_max_capacity ) ){
					$max_service_capacity = $service_max_capacity;
				}
			}

			return $min_service_capacity;
		}
		
		function bookingpress_get_service_capacity( $max_service_capacity, $selected_service_id ){
			global $bookingpress_pro_staff_members, $wpdb, $tbl_bookingpress_staffmembers_services, $bookingpress_services, $BookingPress;
			$bpa_is_staffmember_module_active = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();

			if( empty( $selected_service_id ) && !empty( $_POST['appointment_data_obj']['selected_service'] ) ){ // phpcs:ignore
				$selected_service_id = intval( $_POST['appointment_data_obj']['selected_service'] ); // phpcs:ignore
			}

			$skip_checking_service_capacity = false;

			$skip_checking_service_capacity = apply_filters( 'bpa_skip_checking_capacity', $skip_checking_service_capacity, $selected_service_id );
			
			if( $bpa_is_staffmember_module_active && false == $skip_checking_service_capacity ){
				$staffmember_id = !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ? intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
				
				if( empty( $staffmember_id ) ){
					if( empty( $_POST['appointment_data_obj'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
						$_POST['appointment_data_obj'] = !empty( $_POST['appointment_data'] ) ? array_map( array($BookingPress, 'appointment_sanatize_field'), $_POST['appointment_data'] ) : array();  // phpcs:ignore
					}
					$staffmember_id = !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
				}
				if( !empty( $staffmember_id ) ){
					$staffmember_capacity = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_capacity FROM `{$tbl_bookingpress_staffmembers_services}` WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $staffmember_id, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
					
					if( !empty( $staffmember_capacity ) ){
						$max_service_capacity = $staffmember_capacity;
						$skip_checking_service_capacity = true;
					}
				}
			}
			
			if( false == $skip_checking_service_capacity ){
				/** Retrieve Service Max Capacity */
				$service_max_capacity = $bookingpress_services->bookingpress_get_service_meta( $selected_service_id, 'max_capacity' );
				if( !empty( $service_max_capacity ) ){
					$max_service_capacity = $service_max_capacity;
				}
			}

			/** service capacity - priority should be applied to staffmember */
			return $max_service_capacity;
		}

		function bookingpress_modify_servies_listing_data_func($service_data, $posted_data, $total_services){
			global $wpdb, $BookingPress, $bookingpress_services, $tbl_bookingpress_extra_services, $bookingpress_service_extra;
			if(!empty($service_data['items'])){
                foreach ( $service_data['items'] as $key => $value) {
                    $bookingpress_service_id     = intval($value['service_id']);

					// Get service enable disable state value
                    $bookingpress_service_enabled            = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'show_service_on_site');
					$bookingpress_service_enabled            = ( empty($bookingpress_service_enabled) ) ? 'true' : $bookingpress_service_enabled;
                    $value['bookingpress_service_enabled'] = $bookingpress_service_enabled;
		    		$service_data['items'][$key] = $value;
		    		$service_duration            = esc_html($total_services[$key]['bookingpress_service_duration_val']);					
		    		$service_duration_unit       = esc_html($total_services[$key]['bookingpress_service_duration_unit']);
					
                    if($service_duration_unit == 'd' ) {
                        $service_duration .= ' ' . esc_html__('Days', 'bookingpress-appointment-booking');
						$service_data['items'][$key]['service_duration'] = $service_duration;
                    }

					//Get service extras
					$bookingpress_total_extras_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_service_id = %d", $bookingpress_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is table name.
					$bookingpress_extras_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_service_id = %d LIMIT 3", $bookingpress_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is table name.

					$bookingpress_extra_services_arr = array();

					if(!empty($bookingpress_extras_details)){
						foreach($bookingpress_extras_details as $bookingpress_extra_service_key => $bookingpress_extra_service_val){
							$bookingpress_extra_services_arr[] = array(
								'extra_service_name' => $bookingpress_extra_service_val['bookingpress_extra_service_name']
							);
						}
					}

					$service_data['items'][$key]['extra_services'] = $bookingpress_extra_services_arr;
					$service_data['items'][$key]['extra_services_total_counter'] = count($bookingpress_total_extras_details);
					$service_data['items'][$key]['extra_services_remain_counter'] = 0;
					$service_data['items'][$key]['is_extra_enable'] = $bookingpress_service_extra->bookingpress_check_service_extra_module_activation();

					//Is special workhour configure or not
					$bookingpress_is_special_workhour_configure_or_not = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'bookingpress_configure_specific_service_workhour');
					$service_data['items'][$key]['is_special_workhour_configure'] = $bookingpress_is_special_workhour_configure_or_not;

					if(count($bookingpress_total_extras_details) > 3){
						$service_data['items'][$key]['extra_services_remain_counter'] = intval($service_data['items'][$key]['extra_services_total_counter']) - 3;
					}
                }
			}

			return $service_data;
		}

		function bookingpress_modify_frontend_return_timings_data_func( $bookingpress_timings_data, $service_id, $selected_date ) {
			global $BookingPress, $bookingpress_services, $bookingpress_pro_services;

			$bookingpress_minimum_time_required_for_booking = $BookingPress->bookingpress_get_settings( 'default_minimum_time_for_booking', 'general_setting' );
			$bookingpress_minimum_time_required_for_booking = $bookingpress_services->bookingpress_get_service_meta( $service_id, 'minimum_time_required_before_booking' ); // Selected service meta value

			$bookingpress_max_capacity = $bookingpress_pro_services->bookingpress_get_service_max_capacity( $service_id );

			$bookingpress_current_time_timestamp = current_time( 'timestamp' );

			if ( ! empty( $bookingpress_timings_data['morning_time'] ) ) {
				foreach ( $bookingpress_timings_data['morning_time'] as $k => $v ) {
					$bookingpress_timeslot_start_time = $v['start_time'];
					$bookingpress_timeslot_end_time   = $v['end_time'];

					$bookingpress_is_compulsory_booked = 0;
					if ( $bookingpress_minimum_time_required_for_booking != 'disabled' ) {
						$bookingpress_slot_start_datetime       = $selected_date . ' ' . $v['start_time'] . ':00';
						$bookingpress_slot_end_datetime         = $selected_date . ' ' . $v['end_time'] . ':00';
						$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
						$bookingpress_slot_end_time_timestamp   = strtotime( $bookingpress_slot_end_datetime );

						$bookingpress_time_diff = round( abs( $bookingpress_current_time_timestamp - $bookingpress_slot_start_time_timestamp ) / 60, 2 );

						if ( $bookingpress_minimum_time_required_for_booking < 1440 ) {
							if ( $bookingpress_slot_start_time_timestamp < $bookingpress_current_time_timestamp ) {
								$bookingpress_is_compulsory_booked = 1;
							}

							if ( $bookingpress_time_diff <= $bookingpress_minimum_time_required_for_booking ) {
								$bookingpress_is_compulsory_booked = 1;
							}
						}
					}

					$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked( $service_id, $selected_date, $bookingpress_timeslot_start_time, $bookingpress_timeslot_end_time );
					if ( $bookingpress_total_booked_appointment < $bookingpress_max_capacity && $bookingpress_is_compulsory_booked == 0 ) {
						$bookingpress_timings_data['morning_time'][ $k ]['is_booked'] = 0;
					}
					$bookingpress_timings_data['morning_time'][ $k ]['total_booked_appointment'] = $bookingpress_total_booked_appointment;
					$bookingpress_timings_data['morning_time'][ $k ]['max_capacity']             = $bookingpress_max_capacity;
				}
			}

			if ( ! empty( $bookingpress_timings_data['afternoon_time'] ) ) {
				foreach ( $bookingpress_timings_data['afternoon_time'] as $k => $v ) {
					$bookingpress_timeslot_start_time = $v['start_time'];
					$bookingpress_timeslot_end_time   = $v['end_time'];

					$bookingpress_is_compulsory_booked = 0;
					if ( $bookingpress_minimum_time_required_for_booking != 'disabled' ) {
						$bookingpress_slot_start_datetime       = $selected_date . ' ' . $v['start_time'] . ':00';
						$bookingpress_slot_end_datetime         = $selected_date . ' ' . $v['end_time'] . ':00';
						$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
						$bookingpress_slot_end_time_timestamp   = strtotime( $bookingpress_slot_end_datetime );

						$bookingpress_time_diff = round( abs( $bookingpress_current_time_timestamp - $bookingpress_slot_start_time_timestamp ) / 60, 2 );

						if ( $bookingpress_minimum_time_required_for_booking < 1440 ) {
							if ( $bookingpress_slot_start_time_timestamp < $bookingpress_current_time_timestamp ) {
								$bookingpress_is_compulsory_booked = 1;
							}

							if ( $bookingpress_time_diff <= $bookingpress_minimum_time_required_for_booking ) {
								$bookingpress_is_compulsory_booked = 1;
							}
						}
					}

					$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked( $service_id, $selected_date, $bookingpress_timeslot_start_time, $bookingpress_timeslot_end_time );
					if ( $bookingpress_total_booked_appointment < $bookingpress_max_capacity && $bookingpress_is_compulsory_booked == 0 ) {
						$bookingpress_timings_data['afternoon_time'][ $k ]['is_booked'] = 0;
					}
					$bookingpress_timings_data['afternoon_time'][ $k ]['total_booked_appointment'] = $bookingpress_total_booked_appointment;
					$bookingpress_timings_data['afternoon_time'][ $k ]['max_capacity']             = $bookingpress_max_capacity;
				}
			}

			if ( ! empty( $bookingpress_timings_data['evening_time'] ) ) {
				foreach ( $bookingpress_timings_data['evening_time'] as $k => $v ) {
					$bookingpress_timeslot_start_time = $v['start_time'];
					$bookingpress_timeslot_end_time   = $v['end_time'];

					$bookingpress_is_compulsory_booked = 0;
					if ( $bookingpress_minimum_time_required_for_booking != 'disabled' ) {
						$bookingpress_slot_start_datetime       = $selected_date . ' ' . $v['start_time'] . ':00';
						$bookingpress_slot_end_datetime         = $selected_date . ' ' . $v['end_time'] . ':00';
						$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
						$bookingpress_slot_end_time_timestamp   = strtotime( $bookingpress_slot_end_datetime );

						$bookingpress_time_diff = round( abs( $bookingpress_current_time_timestamp - $bookingpress_slot_start_time_timestamp ) / 60, 2 );

						if ( $bookingpress_minimum_time_required_for_booking < 1440 ) {
							if ( $bookingpress_slot_start_time_timestamp < $bookingpress_current_time_timestamp ) {
								$bookingpress_is_compulsory_booked = 1;
							}

							if ( $bookingpress_time_diff <= $bookingpress_minimum_time_required_for_booking ) {
								$bookingpress_is_compulsory_booked = 1;
							}
						}
					}

					$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked( $service_id, $selected_date, $bookingpress_timeslot_start_time, $bookingpress_timeslot_end_time );
					if ( $bookingpress_total_booked_appointment < $bookingpress_max_capacity && $bookingpress_is_compulsory_booked == 0 ) {
						$bookingpress_timings_data['evening_time'][ $k ]['is_booked'] = 0;
					}
					$bookingpress_timings_data['evening_time'][ $k ]['total_booked_appointment'] = $bookingpress_total_booked_appointment;
					$bookingpress_timings_data['evening_time'][ $k ]['max_capacity']             = $bookingpress_max_capacity;
				}
			}

			if ( ! empty( $bookingpress_timings_data['night_time'] ) ) {
				foreach ( $bookingpress_timings_data['night_time'] as $k => $v ) {
					$bookingpress_timeslot_start_time = $v['start_time'];
					$bookingpress_timeslot_end_time   = $v['end_time'];

					$bookingpress_is_compulsory_booked = 0;
					if ( $bookingpress_minimum_time_required_for_booking != 'disabled' ) {
						$bookingpress_slot_start_datetime       = $selected_date . ' ' . $v['start_time'] . ':00';
						$bookingpress_slot_end_datetime         = $selected_date . ' ' . $v['end_time'] . ':00';
						$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
						$bookingpress_slot_end_time_timestamp   = strtotime( $bookingpress_slot_end_datetime );

						$bookingpress_time_diff = round( abs( $bookingpress_current_time_timestamp - $bookingpress_slot_start_time_timestamp ) / 60, 2 );

						if ( $bookingpress_minimum_time_required_for_booking < 1440 ) {
							if ( $bookingpress_slot_start_time_timestamp < $bookingpress_current_time_timestamp ) {
								$bookingpress_is_compulsory_booked = 1;
							}

							if ( $bookingpress_time_diff <= $bookingpress_minimum_time_required_for_booking ) {
								$bookingpress_is_compulsory_booked = 1;
							}
						}
					}

					$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked( $service_id, $selected_date, $bookingpress_timeslot_start_time, $bookingpress_timeslot_end_time );
					if ( $bookingpress_total_booked_appointment < $bookingpress_max_capacity && $v['is_compulsory_disable'] != 1 ) {
						$bookingpress_timings_data['night_time'][ $k ]['is_booked'] = 0;
					}
					$bookingpress_timings_data['night_time'][ $k ]['total_booked_appointment'] = $bookingpress_total_booked_appointment;
					$bookingpress_timings_data['night_time'][ $k ]['max_capacity']             = $bookingpress_max_capacity;
				}
			}

			return $bookingpress_timings_data;
		}

		function bookingpress_save_shift_mgmt_details_func() {
			global $wpdb, $BookingPress, $bookingpress_services, $tbl_bookingpress_service_workhours, $tbl_bookingpress_service_special_day,$tbl_bookingpress_service_special_day_breaks, $tbl_bookingpress_service_daysoff;
			$response = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'save_service_shift_mgmt_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			if( empty( $_POST['services_data'] ) ){ //phpcs:ignore
				$response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');
				wp_send_json( $response );
				die;
			}

			$posted_data = json_decode( stripslashes_deep( $_POST['services_data'] ), true ); // phpcs:ignore
			$_POST['special_day_details'] = !empty( $_POST['special_day_details']) ? json_decode(stripslashes_deep( $_POST['special_day_details'] ), true) : array(); //phpcs:ignore
			$posted_data['special_day_details'] =  array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['special_day_details']); //phpcs:ignore

			$service_id  = ! empty( $posted_data['service_update_id'] ) ? intval( $posted_data['service_update_id'] ) : 0; // phpcs:ignore
			if ( ! empty( $service_id ) && ! empty( $posted_data ) ) {
				/* save work hour data */
				$bookingpress_configure_specific_service_workhour = ! empty( $posted_data['bookingpress_configure_specific_service_workhour'] ) ? $posted_data['bookingpress_configure_specific_service_workhour'] : 'false';
				
				if( true === $bookingpress_configure_specific_service_workhour ){
					$bookingpress_configure_specific_service_workhour = 'true';
				}

				$bookingpress_services->bookingpress_add_service_meta( $service_id, 'bookingpress_configure_specific_service_workhour', $bookingpress_configure_specific_service_workhour );

				$bookingpress_service_workhour_delete_where_condition = array(
					'bookingpress_service_id' => $service_id,
					'bookingpress_service_workhours_is_break' => 0,
				);
				$bookingpress_service_workhour_delete_where_condition = apply_filters('bookingpress_service_workhour_delete_where_condition_filter', $bookingpress_service_workhour_delete_where_condition, $posted_data);
				$wpdb->delete($tbl_bookingpress_service_workhours, $bookingpress_service_workhour_delete_where_condition);

				$bookingpress_service_workhour_break_delete_where_condition = array(
					'bookingpress_service_id' => $service_id,
					'bookingpress_service_workhours_is_break' => 1,
				);
				$bookingpress_service_workhour_break_delete_where_condition = apply_filters('bookingpress_service_workhour_break_delete_where_condition_filter', $bookingpress_service_workhour_break_delete_where_condition, $posted_data);
				$wpdb->delete($tbl_bookingpress_service_workhours, $bookingpress_service_workhour_break_delete_where_condition);

				if ( ! empty( $bookingpress_configure_specific_service_workhour ) && $bookingpress_configure_specific_service_workhour == 'true' ) {

					$bookingpress_workhour_days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
					foreach ( $bookingpress_workhour_days as $workhour_key => $workhour_val ) {
						$workhour_start_time = ! empty( $posted_data['workhours_timings'][ $workhour_val ]['start_time'] ) ? $posted_data['workhours_timings'][ $workhour_val ]['start_time'] : '09:00:00';
						$workhour_end_time   = ! empty( $posted_data['workhours_timings'][ $workhour_val ]['end_time'] ) ? $posted_data['workhours_timings'][ $workhour_val ]['end_time'] : '17:00:00';

						if ( $workhour_start_time == 'Off' ) {
							$workhour_start_time = null;
						}
						if ( $workhour_end_time == 'Off' ) {
							$workhour_end_time = null;
						}
						$bookingpress_db_fields = array(
							'bookingpress_service_id' => $service_id,
							'bookingpress_service_workday_key' => $workhour_val,
							'bookingpress_service_workhours_start_time' => $workhour_start_time,
							'bookingpress_service_workhours_end_time' => $workhour_end_time,
						);

						//BookingPress Modify Service workhours data
						$bookingpress_db_fields = apply_filters('bookingpress_modify_service_workhours_details', $bookingpress_db_fields, $posted_data);

						$wpdb->insert( $tbl_bookingpress_service_workhours, $bookingpress_db_fields );
					}

					/* insert service special days data */

					$bookingpress_break_days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
					foreach ( $bookingpress_break_days as $break_key => $break_val ) {
						$bookingpress_day_break_details = ! empty( $posted_data['selected_break_timings'][ $break_val ] ) ? $posted_data['selected_break_timings'][ $break_val ] : array();
						if ( ! empty( $bookingpress_day_break_details ) ) {
							foreach ( $bookingpress_day_break_details as $break_day_arr_key => $break_day_arr_val ) {
								$break_start_time       = $break_day_arr_val['start_time'];
								$break_end_time         = $break_day_arr_val['end_time'];
								$bookingpress_db_fields = array(
									'bookingpress_service_id' => $service_id,
									'bookingpress_service_workday_key' => $break_val,
									'bookingpress_service_workhours_start_time' => $break_start_time,
									'bookingpress_service_workhours_end_time' => $break_end_time,
									'bookingpress_service_workhours_is_break' => 1,
								);
								$bookingpress_db_fields = apply_filters('bookingpress_modify_service_workhours_details', $bookingpress_db_fields, $posted_data);
								$wpdb->insert( $tbl_bookingpress_service_workhours, $bookingpress_db_fields );
							}
						}
					}
				}

				/** Service Special Days start */
				$bookingpress_special_day_data = $wpdb->get_results( $wpdb->prepare( 'SELECT bookingpress_service_special_day_id FROM ' . $tbl_bookingpress_service_special_day . ' WHERE bookingpress_service_id = %d', $service_id ), ARRAY_A );    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_service_special_day is a table name. false alarm

				$wpdb->delete( $tbl_bookingpress_service_special_day, array( 'bookingpress_service_id' => $service_id ) );

				if ( ! empty( $bookingpress_special_day_data ) ) {
					foreach ( $bookingpress_special_day_data as $bookingpress_special_day_data_key => $bookingpress_special_day_data_value ) {
						$bookingpress_special_day_id = ! empty( $bookingpress_special_day_data_value['bookingpress_service_special_day_id'] ) ? intval( $bookingpress_special_day_data_value['bookingpress_service_special_day_id'] ) : 0;
						$wpdb->delete( $tbl_bookingpress_service_special_day_breaks, array( 'bookingpress_special_day_id' => $bookingpress_special_day_id ) );
					}
				}
				if ( ! empty( $posted_data['special_day_details'] ) && is_array( $posted_data['special_day_details'] ) ) {
					foreach ( $posted_data['special_day_details'] as $special_day ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason: data will be sanitized further
						$bookingpress_special_day_start_date = ! empty( $special_day['special_day_start_date'] ) ? sanitize_text_field( $special_day['special_day_start_date'] ) : '';
						$bookingpress_special_day_end_date   = ! empty( $special_day['special_day_end_date'] ) ? sanitize_text_field( $special_day['special_day_end_date'] ) : '';
						$special_day_workhour_arr            = ! empty( $special_day['special_day_workhour'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $special_day['special_day_workhour'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason: data has been sanititzed properly
						$start_time                          = ! empty( $special_day['start_time'] ) ? sanitize_text_field( $special_day['start_time'] ) : '';
						$end_time                            = ! empty( $special_day['end_time'] ) ? sanitize_text_field( $special_day['end_time'] ) : '';

						$args_special_day = array(
							'bookingpress_service_id' => $service_id,
							'bookingpress_special_day_start_date' => $bookingpress_special_day_start_date,
							'bookingpress_special_day_end_date' => $bookingpress_special_day_end_date,
							'bookingpress_special_day_start_time' => $start_time,
							'bookingpress_special_day_end_time' => $end_time,
							'bookingpress_created_at' => current_time( 'mysql' ),
						);
						$wpdb->insert( $tbl_bookingpress_service_special_day, $args_special_day );
						$bookingpress_special_day_reference_id = $wpdb->insert_id;

						if ( ! empty( $special_day_workhour_arr ) ) {
							foreach ( $special_day_workhour_arr as $special_day_workhour_key => $special_day_workhour_val ) {
								$bookingpress_start_time = ! empty( $special_day_workhour_val['start_time'] ) ? sanitize_text_field( $special_day_workhour_val['start_time'] ) : '';
								$bookingpress_end_time   = ! empty( $special_day_workhour_val['end_time'] ) ? sanitize_text_field( $special_day_workhour_val['end_time'] ) : '';
								$args_extra_details      = array(
									'bookingpress_special_day_id' => $bookingpress_special_day_reference_id,
									'bookingpress_special_day_break_start_time' => $bookingpress_start_time,
									'bookingpress_special_day_break_end_time' => $bookingpress_end_time,
									'bookingpress_created_at' => current_time( 'mysql' ),
								);
								$wpdb->insert( $tbl_bookingpress_service_special_day_breaks, $args_extra_details );
							}
						}
					}
				}
				/** Service Special Days End */

				/** Service Days Off Start */
				$wpdb->delete( $tbl_bookingpress_service_daysoff, array( 'bookingpress_service_id' => $service_id ) );
				if( !empty($_REQUEST['daysoff_details'] ) ){

					
					$_REQUEST['daysoff_details'] = !empty( $_REQUEST['daysoff_details']) ? json_decode( stripslashes_deep( $_REQUEST['daysoff_details'] ), true ) : array(); //phpcs:ignore

					$bpa_daysoff_opts = array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['daysoff_details'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function

					foreach ( $bpa_daysoff_opts as $daysoff ) {
						$daysoff_date  = ! empty( $daysoff['dayoff_date'] ) ? $daysoff['dayoff_date'] : '';
						$dayoff_date_end  = ! empty( $daysoff['dayoff_date_end'] ) ? $daysoff['dayoff_date_end'] : '';
						$dayoff_name   = ! empty( $daysoff['dayoff_name'] ) ? $daysoff['dayoff_name'] : '';
						$dayoff_repeat = ( ! empty( $daysoff['dayoff_repeat'] ) && $daysoff['dayoff_repeat'] == true ) ? 1 : 0;

						if($dayoff_date_end == null || $dayoff_date_end == 'null' || empty($dayoff_date_end)){
							$dayoff_date_end = $daysoff_date;
						}

						$daysoff_repeat_frequency = !empty( $daysoff['repeat_frequency'] ) ? intval( $daysoff['repeat_frequency'] ) : 1;
						$daysoff_repeat_frequency_type = !empty( $daysoff['repeat_frequency_type'] ) ? sanitize_text_field( $daysoff['repeat_frequency_type'] ) : 'year';
						$daysoff_repeat_duration = !empty( $daysoff['repeat_duration'] ) ? sanitize_text_field( $daysoff['repeat_duration'] ) : 'forever';
						$daysoff_repeat_times	 = !empty( $daysoff['repeat_times'] ) ? intval( $daysoff['repeat_times'] ) : 1;
						$daysoff_repeat_date	 = !empty( $daysoff['repeat_date'] ) ? sanitize_text_field( $daysoff['repeat_date'] ) : date('Y-m-d', strtotime( '+1 year' ) );

						$daysoff_repeat_date = apply_filters( 'bookingpress_change_repeat_date_timezone_to_wp', $daysoff_repeat_date );

						$args = array(
							'bookingpress_service_id' => $service_id,
							'bookingpress_service_daysoff_name' => $dayoff_name,
							'bookingpress_service_daysoff_date' => $daysoff_date,
							'bookingpress_service_daysoff_enddate' => $dayoff_date_end,
							'bookingpress_service_daysoff_repeat' => $dayoff_repeat,
							'bookingpress_service_daysoff_repeat_frequency' => $daysoff_repeat_frequency,
							'bookingpress_service_daysoff_repeat_frequency_type' => $daysoff_repeat_frequency_type,
							'bookingpress_service_daysoff_repeat_duration' => $daysoff_repeat_duration,
							'bookingpress_service_daysoff_repeat_times' => $daysoff_repeat_times,
							'bookingpress_service_daysoff_repeat_date' => $daysoff_repeat_date,
							'bookingpress_service_daysoff_created' => current_time( 'mysql' ),
						);

						$wpdb->insert( $tbl_bookingpress_service_daysoff, $args );

						$bookingpress_child_holiday_dates = array();
						if($daysoff_date != $dayoff_date_end){                    									
							$startDate = strtotime($daysoff_date)+86400;
							$endDate = strtotime($dayoff_date_end);                 
							for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
								$date = date('Y-m-d', $currentDate);
								$bookingpress_child_holiday_dates[] = $date;
							}
						} 
						if(!empty($bookingpress_child_holiday_dates)){
							$dayoff_parent_id = $wpdb->insert_id;
							foreach($bookingpress_child_holiday_dates as $holiday_date){
								$args          = array(
									'bookingpress_service_id' => $service_id,
									'bookingpress_service_daysoff_name' => $dayoff_name,
									'bookingpress_service_daysoff_date' => $holiday_date,
									'bookingpress_service_daysoff_enddate' => $dayoff_date_end,
									'bookingpress_service_daysoff_parent' => $dayoff_parent_id,
									'bookingpress_service_daysoff_repeat' => $dayoff_repeat,
									'bookingpress_service_daysoff_repeat_frequency' => $daysoff_repeat_frequency,
									'bookingpress_service_daysoff_repeat_frequency_type' => $daysoff_repeat_frequency_type,
									'bookingpress_service_daysoff_repeat_duration' => $daysoff_repeat_duration,
									'bookingpress_service_daysoff_repeat_times' => $daysoff_repeat_times,
									'bookingpress_service_daysoff_repeat_date' => $daysoff_repeat_date,
									'bookingpress_service_daysoff_created' => current_time( 'mysql' ),
								);
								$wpdb->insert( $tbl_bookingpress_service_daysoff, $args );										
							}
						}
					}
				}
				/** Service Days Off End */

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Shift management data updated successfully...', 'bookingpress-appointment-booking' );
			}

			$response = apply_filters( 'bookingpress_save_external_service_shift_management_data', $response );

			echo wp_json_encode( $response );
			exit;
		}


		function bookingpress_change_service_status_func() {
			global $wpdb, $BookingPress, $bookingpress_services;
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'change_service_status', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			$bookingpress_service_id         = ! empty( $_POST['service_id'] ) ? intval( $_POST['service_id'] ) : 0; // phpcs:ignore
			$bookingpress_service_new_status = ! empty( $_POST['service_new_status'] ) ? sanitize_text_field( $_POST['service_new_status'] ) : 'true'; // phpcs:ignore

			if ( ! empty( $bookingpress_service_id ) && ! empty( $bookingpress_service_new_status ) ) {
				$bookingpress_services->bookingpress_add_service_meta( $bookingpress_service_id, 'show_service_on_site', $bookingpress_service_new_status );

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Service status changed successfully', 'bookingpress-appointment-booking' );
			}

			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_get_staffmember_service_data_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers_services, $tbl_bookingpress_staffmembers, $BookingPressPro;
			$response              = array();
			if(!empty($_POST['action']) && $_POST['action'] == 'bookingpress_get_staffmember_service_data') { //phpcs:ignore
				$bpa_check_authorization = $this->bpa_check_authentication( 'get_staffmember_services', true, 'bpa_wp_nonce' );            
				if( preg_match( '/error/', $bpa_check_authorization ) ){
					$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
					$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

					$response['variant'] = 'error';
					$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
					$response['msg'] = $bpa_error_msg;

					wp_send_json( $response );
					die;
				}
			}

			$bookingpress_service_id = ! empty( $_REQUEST['service_id'] ) ? intval( $_REQUEST['service_id'] ) : 0;
			$bookingpress_assigned_staffmembers_details = array();
			if ( ! empty( $bookingpress_service_id ) ) {
				// Get all staff members assigned with edited service
				$bookingpress_assigned_staffmembers_data    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_service_id = %d", $bookingpress_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm
				
				foreach ( $bookingpress_assigned_staffmembers_data as $bookingpress_staffmember_key => $bookingpress_staffmember_val ) {
					$bookingpress_staffmember_id    = $bookingpress_staffmember_val['bookingpress_staffmember_id'];
					$bookingpress_staffmember_price = $bookingpress_staffmember_val['bookingpress_service_price'];
					$bookingpress_staffmember_min_capacity = $bookingpress_staffmember_val['bookingpress_service_min_capacity'];
					$bookingpress_staffmember_max_capacity = $bookingpress_staffmember_val['bookingpress_service_capacity'];

					$bookingpress_staffmember_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $bookingpress_staffmember_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm

					$bookingpress_staffmember_fname = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_firstname'] ) ? $bookingpress_staffmember_data['bookingpress_staffmember_firstname'] : '';
					$bookingpress_staffmember_lname = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_lastname'] ) ? $bookingpress_staffmember_data['bookingpress_staffmember_lastname'] : '';

					$bookingpress_staffmember_name = $bookingpress_staffmember_fname . ' ' . $bookingpress_staffmember_lname;

					$bookingpress_assigned_staffmembers = array(
						'staffmember_name'  => $bookingpress_staffmember_name,
						'staffmember_price' => $bookingpress_staffmember_price,
						'staffmember_price_with_currency' => $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_staffmember_price),
						"staffmember_min_capacity" => $bookingpress_staffmember_min_capacity,
						'staffmember_max_capacity' => $bookingpress_staffmember_max_capacity,
						'staffmember_id'    => $bookingpress_staffmember_id,
						'staff_display_row' => true
					);
					$bookingpress_assigned_staffmembers_details[] = apply_filters('bookingpress_modify_staffmember_service_data',$bookingpress_assigned_staffmembers,$bookingpress_staffmember_val);
				}

				$response['msg']                      = esc_html__( 'Staff member data retrieved successfully.', 'bookingpress-appointment-booking' );
				$response['staffmember_service_data'] = $bookingpress_assigned_staffmembers_details;
			} else {
				$response['msg']                      = esc_html__( 'No staffmember sevice data retrieved.', 'bookingpress-appointment-booking' );
				$response['staffmember_service_data'] = array();
			}

			$response['variant'] = 'success';
			$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );

			if(!empty($_POST['action']) && $_POST['action'] == 'bookingpress_get_staffmember_service_data') { //phpcs:ignore
				echo wp_json_encode( $response );
				exit();
			} else {
				return $bookingpress_assigned_staffmembers_details;
			}

		}

		function bookingpress_add_posted_data_for_save_service_func() {
			?>
			postdata.bookingpress_assign_staffmember_data = vm2.assign_staff_member_list;
			
			<?php
		}


		function bookingpress_add_new_category_option_func( $category_option ) {
			$service_category_add   = array();
			$service_category_add[] = array(
				'id'             => '0',
				'category_id'    => 'add_new',
				'category_name'  => esc_html__( 'Add New', 'bookingpress-appointment-booking' ),
				'total_services' => '0',
			);
			return array_merge( $service_category_add, $category_option );
		}

		function bookingpress_edit_service_more_vue_data_func() {
			?>
			vm2.service.service_before_buffer_time = (response.data.before_buffer_time !== undefined) ? response.data.before_buffer_time : 0;
			vm2.service.service_before_buffer_time_unit = (response.data.before_buffer_time_unit !== undefined) ? response.data.before_buffer_time_unit : 'm';
			vm2.service.service_after_buffer_time = (response.data.after_buffer_time !== undefined) ? response.data.after_buffer_time : 0;
			vm2.service.service_after_buffer_time_unit = (response.data.after_buffer_time_unit !== undefined) ? response.data.after_buffer_time_unit : 'm';
			vm2.service.service_expiration_date = (response.data.service_expiration_date !== undefined) ? response.data.service_expiration_date : '';
			vm2.service.service_start_date = (response.data.service_start_date !== undefined) ? response.data.service_start_date : '';

			vm2.service.max_capacity = (response.data.max_capacity !== undefined) ? response.data.max_capacity : 1;
			vm2.service.min_capacity = (response.data.min_capacity !== undefined) ? response.data.min_capacity : 1;

			vm2.service.show_service_on_site = (typeof response.data.show_service_on_site != 'undefined' && response.data.show_service_on_site == 'true') ? true : typeof response.data.show_service_on_site == 'undefined' ? true : false;
			
			vm2.service.minimum_time_required_before_booking = (response.data.minimum_time_required_before_booking !== undefined) ? response.data.minimum_time_required_before_booking : '';
			
			vm2.service.minimum_time_required_before_booking_time_unit = (response.data.minimum_time_required_before_booking_time_unit !== undefined) ? response.data.minimum_time_required_before_booking_time_unit : '';

			vm2.service.minimum_time_required_before_rescheduling = (response.data.minimum_time_required_before_rescheduling !== undefined) ? response.data.minimum_time_required_before_rescheduling : '';

			vm2.service.minimum_time_required_before_rescheduling_time_unit = (response.data.minimum_time_required_before_rescheduling_time_unit !== undefined) ? response.data.minimum_time_required_before_rescheduling_time_unit : '';

			vm2.service.minimum_time_required_before_cancelling = (response.data.minimum_time_required_before_cancelling !== undefined) ? response.data.minimum_time_required_before_cancelling : '';

			vm2.service.minimum_time_required_before_cancelling_time_unit = (response.data.minimum_time_required_before_cancelling_time_unit !== undefined) ? response.data.minimum_time_required_before_cancelling_time_unit : '';
			/*
			if(response.data.service_image_details == '' || response.data.service_image_details === undefined){
				vm2.service.service_images_list = [];
			} else {
				vm2.service.service_images_list = response.data.service_image_details
			}
			*/
			vm2.service.woocommerce_selected_product = (response.data.bookingpress_woocommerce_product !== undefined) ? parseInt(response.data.bookingpress_woocommerce_product) : '';

			//vm2.get_staffmember_assigned_service();

			vm2.assign_staff_member_list = response.data.staffmember_service_data;

			let total_available_services = 0;
			vm2.assign_staff_member_list.forEach( (element,index) => {
				if( "undefined" == element.staff_display_row || element.staff_display_row == true ){
					total_available_services++;
				}
			});
			vm2.total_assigned_staff_members = total_available_services;


			<?php do_action('bookingpress_modify_assign_services_xhr_data'); ?>
			<?php
		}

		function bookingpress_get_service_special_day_details_func() {

			global $wpdb, $tbl_bookingpress_service_special_day,$tbl_bookingpress_service_special_day_breaks,$bookingpress_global_options;
			$response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'get_service_special_days', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			$bookingprress_service_id = ! empty( $_REQUEST['edit_id'] ) ? intval( $_REQUEST['edit_id'] ) : '';

			$response['special_day_data'] = array();
			$response['msg']              = esc_html__( 'Something went wrong.', 'bookingpress-appointment-booking' );
			$response['title']            = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['variant']          = 'error';

			if ( ! empty( $_REQUEST['action'] ) && sanitize_text_field( $_REQUEST['action'] == 'bookingpress_get_service_special_day_details' ) && ! empty( $bookingprress_service_id ) ) {
				$bookingpress_global_settings = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_date_format     = $bookingpress_global_settings['wp_default_date_format'];
				$bookingpress_time_format     = $bookingpress_global_settings['wp_default_time_format'];

				$bookingpress_special_day      = array();
				$bookingpress_special_day_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_service_special_day . ' WHERE bookingpress_service_id = %d ', $bookingprress_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_service_special_day is a table name. false alarm

				if ( ! empty( $bookingpress_special_day_data ) ) {

					foreach ( $bookingpress_special_day_data as $special_day_key => $special_day ) {

						$special_day_arr        = $special_days_breaks = array();
						$special_day_start_date = ! empty( $special_day['bookingpress_special_day_start_date'] ) ? sanitize_text_field( $special_day['bookingpress_special_day_start_date'] ) : '';
						$special_day_end_date   = ! empty( $special_day['bookingpress_special_day_end_date'] ) ? sanitize_text_field( $special_day['bookingpress_special_day_end_date'] ) : '';

						$special_day_id                                      = ! empty( $special_day['bookingpress_service_special_day_id'] ) ? intval( $special_day['bookingpress_service_special_day_id'] ) : '';
								$special_day_arr['id']                       = $special_day_id;
						$special_day_arr['special_day_start_date']           = $special_day_start_date;
						$special_day_arr['special_day_formatted_start_date'] = date( $bookingpress_date_format, strtotime( $special_day_start_date ) );
						$special_day_arr['special_day_end_date']             = $special_day_end_date;
						$special_day_arr['special_day_formatted_end_date']   = date( $bookingpress_date_format, strtotime( $special_day_end_date ) );
						$special_day_arr['start_time']                       = $special_day['bookingpress_special_day_start_time'];
						$special_day_arr['formatted_start_time']             = date( $bookingpress_time_format, strtotime( $special_day['bookingpress_special_day_start_time'] ) );
						$special_day_arr['end_time']                         = $special_day['bookingpress_special_day_end_time'];
						$special_day_arr['formatted_end_time']               = date( $bookingpress_time_format, strtotime( $special_day['bookingpress_special_day_end_time'] ) );
						$special_day_arr['special_day_service']              = $bookingprress_service_id;

						// Fetch all breaks associated with special day
						$bookingpress_special_days_break = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_service_special_day_breaks . ' WHERE bookingpress_special_day_id = %d ', $special_day_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_service_special_day_breaks is a table name. false alarm
						if ( ! empty( $bookingpress_special_days_break ) && is_array( $bookingpress_special_days_break ) ) {
							foreach ( $bookingpress_special_days_break as $k3 => $v3 ) {
								$break_start_time                      = ! empty( $v3['bookingpress_special_day_break_start_time'] ) ? sanitize_text_field( $v3['bookingpress_special_day_break_start_time'] ) : '';
								$break_end_time                        = ! empty( $v3['bookingpress_special_day_break_end_time'] ) ? sanitize_text_field( $v3['bookingpress_special_day_break_end_time'] ) : '';
								$i                                     = 1;
								$special_days_break_data               = array();
								$special_days_break_data['id']         = $i;
								$special_days_break_data['start_time'] = $break_start_time;
								$special_days_break_data['end_time']   = $break_end_time;
								$special_days_break_data['formatted_start_time'] = date( $bookingpress_time_format, strtotime( $break_start_time ) );
								$special_days_break_data['formatted_end_time']   = date( $bookingpress_time_format, strtotime( $break_end_time ) );
								$special_days_breaks[]                           = $special_days_break_data;
								$i++;
							}
						}
						$special_day_arr['special_day_workhour'] = $special_days_breaks;
							$bookingpress_special_day[]          = $special_day_arr;
					}
				}

				$disabled_special_day_data             = $this->bookingpress_get_service_special_days_dates();
				$response['msg']                       = esc_html__( 'Service Special Day data retrieved successfully.', 'bookingpress-appointment-booking' );
				$response['special_day_data']          = $bookingpress_special_day;
				$response['disabled_special_day_data'] = $disabled_special_day_data;
				$response['variant']                   = 'success';
				$response['title']                     = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			}
			echo wp_json_encode( $response );
			exit;
		}
		function bookingpress_get_service_special_days_dates() {
			global $wpdb, $tbl_bookingpress_service_special_day;
			$disabled_date_arr          = array();
			$disable_added_special_days = $wpdb->get_results( 'SELECT bookingpress_special_day_start_date,bookingpress_special_day_end_date FROM ' . $tbl_bookingpress_service_special_day, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_service_special_day is a table name. false alarm

			if ( ! empty( $disable_added_special_days ) ) {
				foreach ( $disable_added_special_days as $k => $v ) {
					$special_day_disable_date = $special_day_start_date = date( 'Y-m-d', strtotime( $v['bookingpress_special_day_start_date'] ) );
					$special_day_end_date     = date( 'Y-m-d', strtotime( $v['bookingpress_special_day_end_date'] ) );

					while ( $special_day_disable_date <= $special_day_end_date ) {
						array_push( $disabled_date_arr, $special_day_disable_date );
						$special_day_disable_date = date( 'Y-m-d', strtotime( '+1 days', strtotime( $special_day_disable_date ) ) );
					}
				}
			}

			return $disabled_date_arr;

		}
		/*
		function bookingpress_get_service_workhour_details_func() {

			global $wpdb, $tbl_bookingpress_service_workhours, $bookingpress_services,$bookingpress_global_options;
			$response = array();
			$response['workhour_service_data'] = array();
			$response['workhour_data']         = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_service_workhour_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$service_id = ! empty( $_REQUEST['edit_id'] ) ? intval( $_REQUEST['edit_id'] ) : '';
			// Get workhours details
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_service_workhours = $bookingpress_service_breaks = $bookingpress_workhours_data = array();

			if ( ! empty( $service_id ) ) {
				$where_clause = $wpdb->prepare( 'bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = 0', $service_id );
				$where_clause = apply_filters('bookingpress_modify_get_service_workhour_where_clause', $where_clause, $_POST, $service_id);

				$bookingpress_service_workhours_details = $wpdb->get_results( "SELECT bookingpress_service_workhours_start_time,bookingpress_service_workhours_end_time,bookingpress_service_workday_key FROM `{$tbl_bookingpress_service_workhours}` WHERE $where_clause", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is a table name. false alarm

				if ( ! empty( $bookingpress_service_workhours_details ) ) {
					foreach ( $bookingpress_service_workhours_details as $bookingpress_service_workhour_key => $bookingpress_service_workhour_val ) {
						$selected_start_time = $bookingpress_service_workhour_val['bookingpress_service_workhours_start_time'];
						$selected_end_time   = $bookingpress_service_workhour_val['bookingpress_service_workhours_end_time'];
						if ( $selected_start_time == null ) {
							$selected_start_time = 'Off';
						}
						if ( $selected_end_time == null ) {
							$selected_end_time = 'Off';
						}
						$bookingpress_service_workhours[ $bookingpress_service_workhour_val['bookingpress_service_workday_key'] ] = array(
							'start_time' => $selected_start_time,
							'end_time'   => $selected_end_time,
						);
					}
					$bookingpress_break_time_details = array();
					$bookingpress_days_arr = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
					foreach ( $bookingpress_days_arr as $days_key => $days_val ) {
						$bookingpress_breaks_arr = array();
						$break_where_clause = $wpdb->prepare( 'bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = 1 AND bookingpress_service_workday_key = %s', $service_id, ucfirst($days_val) );
						$break_where_clause = apply_filters('bookingpress_modify_get_service_workhour_break_where_clause', $break_where_clause, $_POST, $service_id, ucfirst($days_val));

						$bookingpress_break_time_details = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_service_workhours_start_time,bookingpress_service_workhours_end_time FROM `{$tbl_bookingpress_service_workhours}` WHERE bookingpress_service_workday_key = %s  AND bookingpress_service_workhours_is_break = %d AND bookingpress_service_id = %d", ucfirst($days_val),1,$service_id ),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is a table name.
						
						if ( !empty($bookingpress_break_time_details)) {
							foreach($bookingpress_break_time_details as $key => $value) {
								$bookingpress_breaks_arr[] = array(
									'start_time' => $value['bookingpress_service_workhours_start_time'],
									'formatted_start_time' => date( $bookingpress_options['wp_default_time_format'], strtotime( $value['bookingpress_service_workhours_start_time'] ) ),
									'end_time'   => $value['bookingpress_service_workhours_end_time'],
									'formatted_end_time'   => date( $bookingpress_options['wp_default_time_format'], strtotime( $value['bookingpress_service_workhours_end_time'] ) ),								
								);
							}
						}
						$bookingpress_workhours_data[] = array(
							'day_name'    => ucfirst( $days_val ),
							'break_times' => $bookingpress_breaks_arr,
						);
					}
				}					
				$bookingpress_configure_specific_service_workhour_val = $bookingpress_services->bookingpress_get_service_meta( $service_id, 'bookingpress_configure_specific_service_workhour' );				
				$response['variant']                      = 'success';
				$response['title']                        = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']                          = esc_html__( 'Service Workhour data get successfully', 'bookingpress-appointment-booking' );
				$response['workhour_service_data']        = $bookingpress_service_workhours;
				$response['workhour_data']                = $bookingpress_workhours_data;
				$response['configure_specific_workhours'] = !empty($bookingpress_configure_specific_service_workhour_val) &&  $bookingpress_configure_specific_service_workhour_val == 'true' ? true : false;
			}
			echo wp_json_encode( $response );
			exit;
		}
		*/

		function bookingpress_save_service_details( $response, $service_id, $posted_data ) {
			global $BookingPress, $bookingpress_services, $tbl_bookingpress_service_workhours, $wpdb, $tbl_bookingpress_service_special_days, $tbl_bookingpress_staffmembers_services,$tbl_bookingpress_services;
			if ( ! empty( $service_id ) && ! empty( $posted_data ) ) {
				$service_before_buffer_time = ! empty( $posted_data['service_before_buffer_time'] ) ? floatval( $posted_data['service_before_buffer_time'] ) : 0;
				$bookingpress_services->bookingpress_add_service_meta( $service_id, 'before_buffer_time', $service_before_buffer_time );
				$service_before_buffer_time_unit = ! empty( $posted_data['service_before_buffer_time_unit'] ) ? sanitize_text_field( $posted_data['service_before_buffer_time_unit'] ) : '';
				$bookingpress_services->bookingpress_add_service_meta( $service_id, 'before_buffer_time_unit', $service_before_buffer_time_unit );
				$service_after_buffer_time = ! empty( $posted_data['service_after_buffer_time'] ) ? floatval( $posted_data['service_after_buffer_time'] ) : 0;
				$bookingpress_services->bookingpress_add_service_meta( $service_id, 'after_buffer_time', $service_after_buffer_time );				
				$service_after_buffer_time_unit = ! empty( $posted_data['service_after_buffer_time_unit'] ) ? sanitize_text_field( $posted_data['service_after_buffer_time_unit'] ) : '';
				$bookingpress_services->bookingpress_add_service_meta( $service_id, 'after_buffer_time_unit', $service_after_buffer_time_unit );				

				$service_max_capacity = ! empty( $posted_data['max_capacity'] ) ? $posted_data['max_capacity'] : 1;
				if ( ! empty( $service_max_capacity ) ) {
					$bookingpress_services->bookingpress_add_service_meta( $service_id, 'max_capacity', $service_max_capacity );
				}

				$service_min_capacity = ! empty( $posted_data['min_capacity'] ) ? $posted_data['min_capacity'] : 1;
				if ( ! empty( $service_min_capacity ) ) {
					$bookingpress_services->bookingpress_add_service_meta( $service_id, 'min_capacity', $service_min_capacity );
				}

				$service_show_on_site = ! empty( $posted_data['show_service_on_site'] ) ? $posted_data['show_service_on_site'] : 'false';
				if ( ! empty( $service_show_on_site ) ) {
					$bookingpress_services->bookingpress_add_service_meta( $service_id, 'show_service_on_site', $service_show_on_site );
				}

				$min_time_before_booking = ! empty( $posted_data['minimum_time_required_before_booking'] ) ? $posted_data['minimum_time_required_before_booking'] : 0;
				if ( ! empty( $min_time_before_booking ) ) {
					$bookingpress_services->bookingpress_add_service_meta( $service_id, 'minimum_time_required_before_booking', $min_time_before_booking );

					$min_time_before_booking_unit = ! empty( $posted_data['minimum_time_required_before_booking_time_unit'] ) ? $posted_data['minimum_time_required_before_booking_time_unit'] : 'm';
					$bookingpress_services->bookingpress_add_service_meta( $service_id, 'minimum_time_required_before_booking_time_unit', $min_time_before_booking_unit );
				}

				$min_time_before_rescheduling = ! empty( $posted_data['minimum_time_required_before_rescheduling'] ) ? $posted_data['minimum_time_required_before_rescheduling'] : 0;
				if ( ! empty( $min_time_before_rescheduling ) ) {
					$bookingpress_services->bookingpress_add_service_meta( $service_id, 'minimum_time_required_before_rescheduling', $min_time_before_rescheduling );

					$minimum_time_required_before_rescheduling_time_unit = ! empty( $posted_data['minimum_time_required_before_rescheduling_time_unit'] ) ? $posted_data['minimum_time_required_before_rescheduling_time_unit'] : 'm';
					$bookingpress_services->bookingpress_add_service_meta( $service_id, 'minimum_time_required_before_rescheduling_time_unit', $minimum_time_required_before_rescheduling_time_unit );
				}

				$minimum_time_required_before_cancelling = ! empty( $posted_data['minimum_time_required_before_cancelling'] ) ? $posted_data['minimum_time_required_before_cancelling'] : 0;
				if ( ! empty( $minimum_time_required_before_cancelling ) ) {
					$bookingpress_services->bookingpress_add_service_meta( $service_id, 'minimum_time_required_before_cancelling', $minimum_time_required_before_cancelling );

					$minimum_time_required_before_cancelling_time_unit = ! empty( $posted_data['minimum_time_required_before_cancelling_time_unit'] ) ? $posted_data['minimum_time_required_before_cancelling_time_unit'] : 'm';
					$bookingpress_services->bookingpress_add_service_meta( $service_id, 'minimum_time_required_before_cancelling_time_unit', $minimum_time_required_before_cancelling_time_unit );
				}

				$service_gallery_images = ! empty( $posted_data['service_gallery_images'] ) ? maybe_serialize( $posted_data['service_gallery_images'] ) : '';
				$bookingpress_services->bookingpress_add_service_meta( $service_id, 'service_gallery_images', $service_gallery_images );

				// Save assigned staffmember data
				if ( ! empty( $posted_data['bookingpress_assign_staffmember_data'] ) ) {
					$wpdb->delete( $tbl_bookingpress_staffmembers_services, array( 'bookingpress_service_id' => $service_id ) );

					foreach ( $posted_data['bookingpress_assign_staffmember_data'] as $bookingpress_assign_staffmember_key => $bookingpress_assign_staffmember_val ) {
						$bookingpress_db_fields = array(
							'bookingpress_staffmember_id' => $bookingpress_assign_staffmember_val['staffmember_id'],
							'bookingpress_service_id'     => intval( $service_id ),
							'bookingpress_service_price'  => floatval( $bookingpress_assign_staffmember_val['staffmember_price'] ),
							'bookingpress_service_capacity' => !empty($bookingpress_assign_staffmember_val['staffmember_max_capacity']) ? $bookingpress_assign_staffmember_val['staffmember_max_capacity'] : 1,
							'bookingpress_service_min_capacity' => !empty($bookingpress_assign_staffmember_val['staffmember_min_capacity']) ? $bookingpress_assign_staffmember_val['staffmember_min_capacity'] : 1,
							'bookingpress_created_date'   => current_time( 'mysql' ),
						);
						$wpdb->insert( $tbl_bookingpress_staffmembers_services, $bookingpress_db_fields );
					}
				}
				if(isset($posted_data['service_expiration_date'])) {					
					$service_args = array(
						'bookingpress_service_expiration_date' => !empty($posted_data['service_expiration_date']) ? sanitize_text_field($posted_data['service_expiration_date']) : NULL,
					);
					$wpdb->update($tbl_bookingpress_services, $service_args, array( 'bookingpress_service_id' => $service_id ));					
				}
				if(isset($posted_data['service_start_date'])) {					
					$service_args = array(
						'bookingpress_service_start_date' => !empty($posted_data['service_start_date']) ? sanitize_text_field($posted_data['service_start_date']) : NULL,
					);
					$wpdb->update($tbl_bookingpress_services, $service_args, array( 'bookingpress_service_id' => $service_id ));					
				}
			}
			return $response;
		}

		function bookingpress_validate_service_special_days_func() {
			global $wpdb,$tbl_bookingpress_appointment_bookings;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'validate_service_special_days', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			$bookingpress_service_id = ! empty( $_REQUEST['service_id'] ) ? intval( $_REQUEST['service_id'] ) : 0;

			if ( ! empty( $_REQUEST['selected_date'] ) && ! empty( $bookingpress_service_id ) ) {
				$bookingpress_start_date         = date( 'Y-m-d', strtotime( sanitize_text_field( $_REQUEST['selected_date'][0] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated --Reason: data has been validated above
				$bookingpress_end_date           = date( 'Y-m-d', strtotime( sanitize_text_field( $_REQUEST['selected_date'][1] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated --Reason: data has been validated above
				$bookingpress_status             = array( '1', '2' );
				$total_appointments              = 0;
				$bookingpress_search_query_where = 'WHERE 1=1 ';
				if ( ! empty( $bookingpress_start_date ) && ! empty( $bookingpress_end_date ) && ! empty( $bookingpress_service_id ) ) {
						$bookingpress_search_query_where .= " AND (bookingpress_appointment_date BETWEEN '{$bookingpress_start_date}' AND '{$bookingpress_end_date}') AND (bookingpress_service_id = {$bookingpress_service_id})";
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

				$bookingpress_search_query_where = apply_filters( 'bookingpress_validate_service_specia_days_where_caluse', $bookingpress_search_query_where );

				$total_appointments = $wpdb->get_var( 'SELECT COUNT(bookingpress_appointment_booking_id) FROM ' . $tbl_bookingpress_appointment_bookings . ' ' . $bookingpress_search_query_where ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm				
				if ( $total_appointments > 0 ) {
					$response['variant'] = 'warnning';
					$response['title']   = esc_html__( 'Warning', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Appointment(s) are already booked during this time duration for the service. Do you still want to continue?', 'bookingpress-appointment-booking' );
				} else {
					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'success', 'bookingpress-appointment-booking' );
					$response['msg']     = '';
				}
			}
			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_get_total_assigned_service_appointment( $service_id, $date = '', $status = array() ) {
			global $wpdb,$tbl_bookingpress_appointment_bookings;

			$total_appointments              = '';
			$bookingpress_search_query_where = 'WHERE 1=1 ';
			if ( ! empty( $date ) ) {
				$bookingpress_search_query_where .= " AND (bookingpress_appointment_date = '{$date}')";
			}
			if ( ! empty( $status ) && is_array( $status ) ) {
				$bookingpress_search_query_where .= ' AND (';
				$i                                = 0;
				foreach ( $status as $status_key => $status_value ) {
					if ( $i != 0 ) {
						$bookingpress_search_query_where .= ' OR';
					}
					$bookingpress_search_query_where .= " bookingpress_appointment_status ='{$status_value}'";
					$i++;
				}
				$bookingpress_search_query_where .= ' )';
			}

			if ( ! empty( $service_id ) ) {

				$total_appointments = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} {$bookingpress_search_query_where} AND bookingpress_service_id = %d", $service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			}

			return $total_appointments;
		}

		function bookingpress_after_open_add_service_model_func() {
			?>
				//vm.bookingpress_get_default_workhours();		
			<?php
		}	

		function bookingpress_after_reset_add_service_form_func() {
			?>
				this.service.bookingpress_configure_specific_service_workhour = false;
				this.special_day_data_arr = [];
				this.special_day_form.special_day_date = '';
				this.special_day_form.start_time ='';
				this.special_day_form.end_time ='';
				this.special_day_form.special_day_workhour = [];
				this.assign_staff_member_list = [];
				this.total_assigned_staff_members = 0;
				this.service.deposit_type = 'percentage';
				this.service.deposit_amount= '100';								
				this.service.service_before_buffer_time      = 0;
				this.service.service_before_buffer_time_unit = 'm';
				this.service.service_after_buffer_time       = 0;
				this.service.service_after_buffer_time_unit  = 'm';
				this.service.max_capacity     		 = 1;
				this.service.show_service_on_site = 'true';	
				this.service.minimum_time_required_before_booking = 'inherit';
				this.service.minimum_time_required_before_rescheduling = 'inherit';
				this.service.minimum_time_required_before_cancelling = 'inherit';
				this.service.service_expiration_date = '';
				this.service.service_start_date = '';
			<?php
		}

		function bookingpress_modify_file_path_func( $bookingpress_service_view_path ) {
			$bookingpress_service_view_path = BOOKINGPRESS_PRO_VIEWS_DIR . '/services/manage_services.php';
			return $bookingpress_service_view_path;
		}

		function bookingpress_add_service_dynamic_vue_methods_func() {
			global $bookingpress_notification_duration;
			?>
			bookingpress_change_extra_duration(selected_duration){
				const vm = this
				if(selected_duration == 'm' && vm.service_extra_inputs_form.extra_service_duration > 1440){
					vm.service_extra_inputs_form.extra_service_duration = 1440;
				}else if(selected_duration == 'h' && vm.service_extra_inputs_form.extra_service_duration > 24){
					vm.service_extra_inputs_form.extra_service_duration = 24;
				}
			},
			check_category_type(event,currentElement) { 					
				const vm = this
				if(event == 'add_new'){
					vm.service_category.service_category_name = '';
					var dialog_pos = currentElement.target.getBoundingClientRect();
					vm.add_new_category_modal_pos_top = (dialog_pos.top - 160)+'px'
					vm.add_new_category_modal_pos_right = '-'+(dialog_pos.right - 1200)+'px';
					vm.open_add_new_category_popup = true;
				}
				return;
			},				
			save_add_newCategoryDetails(add_service_category) {
				this.$refs['service_category'].validate((valid) => {
					if (valid) {
						const vm = new Vue()
						const vm2 = this
						vm2.is_category_disabled = true
						vm2.is_category_display_save_loader = '1'
						var postdata = vm2.service_category;							
						postdata.action = 'bookingpress_add_categories';						
						postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
						.then(function(response){
							vm2.is_category_disabled = false
							vm2.is_category_display_save_loader = '0'
							vm2.open_add_new_category_popup = false																
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
							});
							if (response.data.variant == 'success') {										
								vm2.get_categories()
								vm2.loadSearchCategories()									
								vm2.loadServiceCategory()									
								vm2.service.service_category = response.data.category_id;																		
							}
						}).catch(function(error){
							console.log(error);
							vm2.$notify({
								title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
								message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
								type: 'error',
								customClass: 'error_notification',
							});
						});
					} else {
						return false;
					}
				});
			},
			before_close_modal_event(done) {					
				return false;
			},
			close_add_new_category_modal() {
				const vm = this
				vm.service.service_category = ''
				vm.open_add_new_category_popup = false
			}, 					
			bookingpress_delete_upload_image(file_name) {																						
				const vm = this					
				vm.service.service_gallery_images.forEach(function(item, index, arr) {
					if (item.upload_update_filename == file_name) {
						vm.service.service_gallery_images.splice(index,1);
					}
				});						
			},
			delete_dayoff_div(day_off_id) {
				var vm = this
				vm.service_dayoff_arr.forEach(function(item, index, arr)
				{
					if (item.id == day_off_id) {
						vm.service_dayoff_arr.splice(index, 1);
					}
				})
			},
			show_edit_dayoff_div(day_off_id, currentElement) {
				var vm = this
				vm.service_dayoff_arr.forEach(function(item, index, arr){
					if (item.id == day_off_id) {
						vm.service_dayoff_form.dayoff_name = item.dayoff_name
						vm.service_dayoff_form.dayoff_date = item.dayoff_date
						vm.service_dayoff_form.dayoff_date_end = item.dayoff_date_end
						vm.service_dayoff_form.dayoff_date_range = [item.dayoff_date,item.dayoff_date_end];
						vm.service_dayoff_form.dayoff_repeat = item.dayoff_repeat

						vm.service_dayoff_form.dayoff_repeat_frequency = item.repeat_frequency;
						vm.service_dayoff_form.dayoff_repeat_freq_type = item.repeat_frequency_type;
						vm.service_dayoff_form.dayoff_repeat_duration = item.repeat_duration;
						vm.service_dayoff_form.dayoff_repeat_times = item.repeat_times;
						vm.service_dayoff_form.dayoff_repeat_date = item.repeat_date;
					}
					vm.edit_service_dayoff = day_off_id;
				});
				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.days_off_modal_pos = (dialog_pos.top - 100)+'px'
				vm.days_off_modal_pos_right = '-'+(dialog_pos.right - 400)+'px';
				vm.days_off_add_modal = true

				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#days_off_add_modal .el-dialog.bpa-dialog--days-off');
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
						vm.service.selected_break_timings[currentValue.day_name] = currentValue.break_times							
					});
					vm.service.workhours_timings = response.data.selected_workhours
					vm.default_break_timings = response.data.default_break_times												
				}).catch(function(error){
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
					});
				});
			},/*	
			bookingpress_get_service_workhour() {
				const vm = this
				var postdata = [];
				postdata.action = 'bookingpress_get_service_workhour_details';
				postdata.edit_id = vm.service.service_update_id;
				<?php do_action('bookingpress_add_post_request_data_to_get_service_workhour'); ?>
				postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
				.then(function(response) {
					vm.service.bookingpress_configure_specific_service_workhour = response.data.configure_specific_workhours;
					if(response.data.workhour_service_data !== 'undefined' && response.data.workhour_service_data != '') {						
						vm.service.workhours_timings = response.data.workhour_service_data
						response.data.workhour_data.forEach(function(currentValue, index, arr){
							vm.work_hours_days_arr.forEach(function(currentValue2, index2, arr2){										
								if(currentValue2.day_name == currentValue.day_name) {											
									vm.work_hours_days_arr[index2]['break_times'] = currentValue.break_times							
								}
							});
							vm.service.selected_break_timings[currentValue.day_name] = currentValue.break_times							
						});
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
			},*/
			savebreakdata(){
				const vm = this
				var is_edit = 0;
				if( "undefined" == typeof vm.display_service_workhours || vm.display_service_workhours == true ){
					vm.$refs['break_timings'].validate((valid) => {
						if(valid) {	
							var update = 0;
							if(vm.break_timings.start_time > vm.break_timings.end_time) {
								vm.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Start time is not greater than End time', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
								});
							}else if(vm.break_timings.start_time == vm.break_timings.end_time) {					
								vm.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Start time & End time are not same', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
								});				
							} else if(vm.service.selected_break_timings[vm.break_selected_day] != '' ) {
								vm.service.selected_break_timings[vm.break_selected_day].forEach(function(currentValue, index, arr) {
									if(is_edit == 0) {
										 if(vm.service.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.service.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {
											is_edit = 1;
											vm.$notify({
												title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
												message: '<?php esc_html_e( 'Please enter valid time for break', 'bookingpress-appointment-booking' ); ?>',
												type: 'error',
												customClass: 'error_notification',
												duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
											});
										 }else if(currentValue['start_time'] == vm.break_timings.start_time && currentValue['end_time'] == vm.break_timings.end_time && (vm.break_timings.edit_index != index || vm.is_edit_break == 0) ) {
											is_edit = 1;
											vm.$notify({
												title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
												message: '<?php esc_html_e( 'Break time already added', 'bookingpress-appointment-booking' ); ?>',
												type: 'error',
												customClass: 'error_notification',
												duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
											});
										}else if(((currentValue['start_time'] < vm.break_timings.start_time  && currentValue['end_time'] > vm.break_timings.start_time) || (currentValue['start_time'] < vm.break_timings.end_time  && currentValue['end_time'] > vm.break_timings.end_time) || (currentValue['start_time'] > vm.break_timings.start_time && currentValue['end_time'] <= vm.break_timings.end_time) || (currentValue['start_time'] >= vm.break_timings.start_time && currentValue['end_time'] < vm.break_timings.end_time )) && (vm.break_timings.edit_index != index || vm.is_edit_break == 0)) {
											is_edit = 1;
											vm.$notify({
												title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
												message: '<?php esc_html_e( 'Break time already added', 'bookingpress-appointment-booking' ); ?>',
												type: 'error',
												customClass: 'error_notification',
												duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
											});
										}
									}	
								});							
								if( is_edit == 0 ) {											
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
										vm.service.selected_break_timings[vm.break_selected_day].forEach(function(currentValue, index, arr) {
											if(index == vm.break_timings.edit_index) {
												currentValue.start_time = vm.break_timings.start_time;
												currentValue.end_time = vm.break_timings.end_time;
												currentValue.formatted_start_time = formatted_start_time;
												currentValue.formatted_end_time = formatted_end_time;
											}
										});   
									}else {
										vm.service.selected_break_timings[vm.break_selected_day].push({ start_time: vm.break_timings.start_time, end_time: vm.break_timings.end_time,formatted_start_time:formatted_start_time,formatted_end_time:formatted_end_time });                                    
									}
									vm.close_add_break_model()
								}
							} else {
								if(vm.service.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.service.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {
									vm.$notify({
										title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
										message: '<?php esc_html_e( 'Please enter valid time for break', 'bookingpress-appointment-booking' ); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
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
									vm.service.selected_break_timings[vm.break_selected_day].push({ start_time:vm.break_timings.start_time,end_time: vm.break_timings.end_time, formatted_start_time: formatted_start_time,formatted_end_time:formatted_end_time });
	
									vm.close_add_break_model();
								}
							}
						}
					})
				} else {
					<?php do_action( 'bookingpress_save_external_break_data' ); ?>
				}
			},
			open_add_break_modal_func(currentElement, breakSelectedDay){
				var dialog_pos = currentElement.target.getBoundingClientRect();
				this.break_modal_pos = (dialog_pos.top + 30)+'px'
				this.break_modal_pos_right = (dialog_pos.right + 38)+'px';				
				this.is_edit_break= 0;
				this.resetaddbreakForm();
				this.open_add_break_modal = true
				this.break_selected_day = breakSelectedDay

				if( typeof this.bpa_adjust_popup_position != 'undefined' ){
					this.bpa_adjust_popup_position( currentElement, 'div#service_breaks_add_modal .el-dialog.bpa-dialog--add-break');
				}
			},
			close_add_break_model() {
				const vm = this
				vm.$refs['break_timings'].resetFields()
				vm.resetaddbreakForm()				
				vm.open_add_break_modal = false;
			},	
			resetaddbreakForm(){
				const vm = this
				vm.break_timings.start_time = ''
				vm.break_timings.end_time = ''
				vm.break_timings.edit_index = ''
			},			
			delete_breakhour(start_time, end_time, selected_day){
				const vm = this
				vm.service.selected_break_timings[selected_day].forEach(function(currentValue, index, arr){
					if(currentValue.start_time == start_time && currentValue.end_time == end_time)
					{
						vm.service.selected_break_timings[selected_day].splice(index, 1);
					}
				});
			},
			edit_workhour_data(currentElement,break_start_time, break_end_time, day_name,index){
				const vm = this
				vm.resetaddbreakForm()
				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.break_modal_pos = (dialog_pos.top - 18)+'px'
				vm.break_modal_pos_right = (dialog_pos.right + 80)+'px';				
				vm.break_timings.start_time = break_start_time
				vm.break_timings.end_time = break_end_time
				vm.break_timings.edit_index = index
				vm.is_edit_break = 1;
				vm.open_add_break_modal = true							
				vm.break_selected_day = day_name

				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#service_breaks_add_modal .el-dialog.bpa-dialog--add-break', 'bpa-bh__item');
				}
			},
			bookingpress_set_workhour_value(worktime,work_hour_day) {	
				const vm = this				
				if(vm.service.workhours_timings[work_hour_day].end_time == 'Off') {                    
					vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
						if(currentValue.day_name == work_hour_day) {
							currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
								if(currentValue2.start_time == worktime) {
									vm.service.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
								}
							});
						}
					});                
				} else if(worktime > vm.service.workhours_timings[work_hour_day].end_time ) {
					vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
						if(currentValue.day_name == work_hour_day) {                       
							currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
								if(currentValue2.start_time == worktime) {
									vm.service.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
								}
							});
						}
					});
				} else if(worktime != 'off' && vm.service.workhours_timings[work_hour_day].end_time == undefined) {
					vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
						if(currentValue.day_name == work_hour_day) {                       
							currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
								if(currentValue2.start_time == worktime) {
									vm.service.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
								}
							});
						}
					});
				}
			},
			bookingpress_check_workhour_value(workhour_time,work_hour_day) {	
				if(workhour_time == 'Off') {
					const vm = this
					vm.service.workhours_timings[work_hour_day].start_time = 'Off';
				}
			},

			bookingpress_remove_condition(condition_type, remove_index){
				const vm = this
				if(condition_type == "appointment_approved"){
					vm.bookingpress_approved_conditional_fields.splice(remove_index, 1)
				}else if(condition_type == "appointment_pending"){
					vm.bookingpress_pending_conditional_fields.splice(remove_index, 1)
				}else if(condition_type == "appointment_canceled"){
					vm.bookingpress_canceled_conditional_fields.splice(remove_index, 1)
				}
			},
			bookingpress_add_service_special_day_period(){
				const vm = this;
				var ilength = 1;

				if(vm.special_day_form.special_day_workhour != undefined && vm.special_day_form.special_day_workhour != ''){
					ilength = parseInt(vm.special_day_form.special_day_workhour.length) + 1;
				}
				let WorkhourData = {};
				Object.assign(WorkhourData, {id: ilength})
				Object.assign(WorkhourData, {start_times: ''})
				Object.assign(WorkhourData, {end_times: ''})
				vm.special_day_form.special_day_workhour.push(WorkhourData);				
			},
			delete_special_day_div(special_day_id){
				var vm = this
				vm.special_day_data_arr.forEach(function(item, index, arr)
				{
					if (item.id == special_day_id) {
						vm.special_day_data_arr.splice(index, 1);
					}
				})
			},
			show_edit_special_day_div(special_day_id,currentElement) {				
				const vm = this
				vm.special_day_data_arr.forEach(function(item, index, arr)
				{
					if (item.id == special_day_id) {
						vm.special_day_form.special_day_date = [item.special_day_start_date,item.special_day_end_date]
						vm.special_day_form.start_time = item.start_time
						vm.special_day_form.end_time = item.end_time
						vm.special_day_form.special_day_workhour = typeof(item.special_day_workhour) !== 'undefined' ? item.special_day_workhour : [];
					}
					vm.edit_special_day_id = special_day_id;
					vm.special_days_add_modal = true
				})
				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.special_days_modal_pos = (dialog_pos.top - 90)+'px'
				vm.special_days_modal_pos_right = '-'+(dialog_pos.right - 400)+'px';	
				
				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#special_days_add_modal .el-dialog.bpa-dialog--special-days');
				}
			},		
			bookingpress_remove_special_day_period(id){
				const vm = this									
				vm.special_day_form.special_day_workhour.forEach(function(item, index, arr)
				{
					if(id == item.id ){
						vm.special_day_form.special_day_workhour.splice(index,1);
					}	
				})
			},
			closeSpecialday(){
				const vm = this;
				vm .edit_special_day_id = ''
				vm.special_day_form.special_day_date = '';
				vm.special_day_form.start_time = '';
				vm.special_day_form.end_time = '';
				vm.special_day_form.special_day_workhour = [];
				vm.special_days_add_modal = false;
			},
			addSpecialday(special_day_form) {
				const vm = this;
				if( "undefined" == typeof vm.display_service_workhours || vm.display_service_workhours == true ){
					this.$refs[special_day_form].validate((valid) => {
						if (valid) {
							vm.disable_service_special_day_btn = true;
							var is_exit = 0;
							if(vm.special_day_form.special_day_workhour != undefined && vm.special_day_form.special_day_workhour != '') {
								vm.special_day_form.special_day_workhour.forEach(function(item, index, arr) {
									if(is_exit == 0 && (item.start_time == '' || item.end_time == '' || item.end_time == undefined || item.start_time == undefined)) {
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
							if(vm.special_day_data_arr != undefined && vm.special_day_data_arr != '' ) {
								vm.special_day_data_arr.forEach(function(item, index, arr) {								
									if((vm.special_day_form.special_day_date[0] == item.special_day_start_date || vm.special_day_form.special_day_date[0] == item.special_day_end_date || ( vm.special_day_form.special_day_date[0] >= item.special_day_start_date && vm.special_day_form.special_day_date[0] <= item.special_day_end_date ) || vm.special_day_form.special_day_date[1] == item.special_day_end_date || vm.special_day_form.special_day_date[1] == item.special_day_start_date || (vm.special_day_form.special_day_date[1] >= item.special_day_start_date && vm.special_day_form.special_day_date[1] <= item.special_day_end_date) || (vm.special_day_form.special_day_date[0] <= item.special_day_start_date && vm.special_day_form.special_day_date[1] >= item.special_day_end_date)) && vm.edit_special_day_id != item.id ) {									
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
							if(vm.service_dayoff_arr != '') {
								vm.service_dayoff_arr.forEach(function(item, index, arr){

									if (item.dayoff_date >= vm.special_day_form.special_day_date[0] && item.dayoff_date <= vm.special_day_form.special_day_date[1] ) {									
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
										is_exit = 1;									
									}
									if (item.dayoff_date_end >= vm.special_day_form.special_day_date[0] && item.dayoff_date_end <= vm.special_day_form.special_day_date[1] ) {									
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
										is_exit = 1;
									}
									if (vm.special_day_form.special_day_date[0] >= item.dayoff_date && vm.special_day_form.special_day_date[0] <= item.dayoff_date_end) {									
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
										is_exit = 1;
									}
									if (vm.special_day_form.special_day_date[1] >= item.dayoff_date && vm.special_day_form.special_day_date[1] <= item.dayoff_date_end) {									
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
							if(is_exit == 0) {
								var postdata = [];
								postdata.action = 'bookingpress_validate_service_special_days'
								postdata.service_id = vm.service.service_update_id
								postdata.selected_date= vm.special_day_form.special_day_date;
								postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
								axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
								.then(function(response){
									if(response.data.variant != 'undefined' && response.data.variant == 'warnning') {					
										vm.$confirm(response.data.msg, 'Warning', {
										confirmButtonText: '<?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?>',
										cancelButtonText: '<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>',
										type: 'warning'
										}).then(() => {		
											if(vm.edit_special_day_id != '' ){
												vm.edit_Special_days();
											} else {
												vm.add_special_days();
											}
										});				
									}else if(response.data.variant != 'undefined' && response.data.variant  == 'success') {
										if(vm.edit_special_day_id != '' ){
											vm.edit_Special_days();
										} else {
											vm.add_special_days();
										}
										vm.special_days_add_modal = false
									}
									vm.disable_service_special_day_btn = false;
								}).catch(function(error){
									console.log(error);
									vm.$notify({
										title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
										message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
										type: 'error_notification',
									});
								});	
							}
							vm.disable_service_special_day_btn = false;	
						} else {
							return false;
						}
					});
				} else {
					<?php do_action( 'bookingpress_save_external_special_data' ); ?>
				}
			},		
			add_special_days(){
				const vm = this;
				var ilength = parseInt(vm.special_day_data_arr.length) + 1;
				let empSpecialDayData = {};					
				Object.assign(empSpecialDayData, {id: ilength})
				Object.assign(empSpecialDayData, {special_day_start_date: vm.special_day_form.special_day_date[0]})
				Object.assign(empSpecialDayData, {special_day_end_date: vm.special_day_form.special_day_date[1]})
				Object.assign(empSpecialDayData, {start_time: vm.special_day_form.start_time})
				Object.assign(empSpecialDayData, {end_time: vm.special_day_form.end_time})
				Object.assign(empSpecialDayData, {special_day_workhour: vm.special_day_form.special_day_workhour})					
				vm.special_day_data_arr.push(empSpecialDayData)
				vm.bookingpress_service_format_special_day_time();
				vm.closeSpecialday();
			},
			bookingpress_service_format_special_day_time(){
				const vm = this
				var postdata = [];
				postdata.action = 'bookingpress_format_service_special_days_data'
				postdata.special_days_data= vm.special_day_data_arr;
				postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
				.then(function(response){
					if(response.data.variant == "success"){
						vm.special_day_data_arr = response.data.daysoff_details
					}
				}).catch(function(error){
					console.log(error);
				});
			},
			edit_Special_days(){
				var vm = this
				var special_day_id = vm.edit_special_day_id
				var special_day_date = vm.special_day_form.special_day_date
				var special_day_start_time = vm.special_day_form.start_time													
				var special_day_end_time = vm.special_day_form.end_time																					
				var special_day_workhour = vm.special_day_form.special_day_workhour				
				vm.special_day_data_arr.forEach(function(item, index, arr)
				{		
					if(item.id == special_day_id)
					{
						item.special_day_start_date = special_day_date[0]
						item.special_day_end_date = special_day_date[1]
						item.start_time = special_day_start_time
						item.end_time = special_day_end_time
						item.special_day_workhour = special_day_workhour							
					}
				})
				vm.closeSpecialday();
				vm.bookingpress_service_format_special_day_time();
			},				
			getSpecialdays(){
				const vm = this
				 var postdata = [];
				 postdata.edit_id = vm.service.service_update_id
				postdata.action = 'bookingpress_get_service_special_day_details';
				postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
				.then(function(response){
					vm.special_day_data_arr = response.data.special_day_data
				}).catch(function(error){
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
			bookingpress_open_assign_staffmember_modal(currentElement){
				const vm = this
				vm.assign_staff_member_details.assigned_staffmember_name = ''
				vm.assign_staff_member_details.assigned_staffmember_price = vm.service.service_price
				vm.assign_staff_member_details.assigned_staffmember_min_capacity = vm.service.min_capacity
				vm.assign_staff_member_details.assigned_staffmember_max_capacity = vm.service.max_capacity
				vm.assign_staff_member_details.assigned_staffmember_id = ''
				vm.assign_staff_member_details.assigned_staffmember_is_edit = 0
				vm.assign_staff_member_details.assigned_location_id = 0;
				<?php
				do_action('bookingpress_after_open_assign_staffmember_model');
				?>
				
				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.assign_staff_member_modal_pos = (dialog_pos.top - 90)+'px'
				vm.assign_staff_member_modal_pos_right = '-'+(dialog_pos.right - 400)+'px';
				vm.open_assign_staff_member_modal = true
				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#assign_staffmember_modal .el-dialog.bpa-dialog--add-assign-staff' );
				}

			},
			bookingpress_close_assign_staffmember_modal(){
				const vm = this
				vm.open_assign_staff_member_modal = false
			},
			bookingpress_save_assign_staffmember_data(){
				const vm = this
				var valid = true;
				var bpa_validate_min_max_msg = false;
				if (vm.assign_staff_member_details.assigned_staffmember_price === "" || vm.assign_staff_member_details.assigned_staffmember_id == '' || vm.assign_staff_member_details.assigned_staffmember_max_capacity == undefined || vm.assign_staff_member_details.assigned_staffmember_min_capacity == undefined ){
					valid = false;
				}
				if( vm.assign_staff_member_details.assigned_staffmember_min_capacity > vm.assign_staff_member_details.assigned_staffmember_max_capacity){
					valid = false;
					bpa_validate_min_max_msg = true;
				}
				<?php
				do_action('bookingpress_before_save_assign_staffmember_data');
				?>
				if(valid == false) {
					if( bpa_validate_min_max_msg == true ){

						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Assign staff member min capacity should not be greater than max capacity.', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
						});
						
					} else{

						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Please select staff member and input staff member price...', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
						});
					}
				}else{
					var is_exist = 0
					vm.assign_staff_member_list.forEach(function(currentValue, index, arr){
						if(currentValue.staffmember_id == vm.assign_staff_member_details.assigned_staffmember_id && vm.assign_staff_member_details.assigned_staffmember_is_edit == 0){
							is_exist = 1
						}
					});
					<?php do_action( 'bookingpress_before_validate_existing_staff_list' ); ?>
					if(is_exist == 1){
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Staff member already assigned to this service.', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
						});
					}else{
						if(vm.assign_staff_member_details.assigned_staffmember_is_edit == 1){
							vm.assign_staff_member_list.forEach(function(currentValue, index, arr){
								if(currentValue.staffmember_id == vm.assign_staff_member_details.assigned_staffmember_id){
									currentValue.staffmember_name = vm.assign_staff_member_details.assigned_staffmember_name
									currentValue.staffmember_price = vm.assign_staff_member_details.assigned_staffmember_price
									currentValue.staffmember_min_capacity = vm.assign_staff_member_details.assigned_staffmember_min_capacity
									currentValue.staffmember_max_capacity = vm.assign_staff_member_details.assigned_staffmember_max_capacity
									currentValue.staffmember_id = vm.assign_staff_member_details.assigned_staffmember_id
									currentValue.staffmember_location = vm.assign_staff_member_details.assigned_location_id
									if( "undefined" != typeof currentValue.enable_custom_service_duration && currentValue.enable_custom_service_duration == true ){	
										currentValue.staffmember_custom_service = vm.assign_staff_member_details.service_custom_duration
										if(currentValue.staffmember_custom_service != 'undefined' && currentValue.staffmember_custom_service != '' && currentValue.staffmember_custom_service != null) {
											currentValue.staffmember_custom_service.forEach(function(item2,index2,arr2) {
												if(index2 == 0 ){
													vm.assign_staff_member_details.assigned_staffmember_price = item2.service_price;
													currentValue.staffmember_price = item2.service_price;
												}
												vm.max_duration_time_options.forEach(function(item3,index3,arr3) {
													if( item3.value == item2.service_duration) {
														currentValue.staffmember_custom_service[index2]['service_duration_text'] = item3.text;
													}
												});
											});
										}
									}
								}								
							});
						}else{
							if(typeof vm.service.enable_custom_service_duration != 'undefined' && vm.service.enable_custom_service_duration == true) {

								let staff_display_row = true;
								if( "undefined" != typeof vm.staffmember_selected_location ){
									if( vm.assign_staff_member_details.assigned_location_id != vm.staffmember_selected_location ){
										staff_display_row = false;
									}
								}

								if(vm.assign_staff_member_details.service_custom_duration != '' ) {
									vm.assign_staff_member_details.service_custom_duration.forEach(function(item,index,arr) {
										if(index == 0 ){
											vm.assign_staff_member_details.assigned_staffmember_price = item.service_price;
										}
										vm.max_duration_time_options.forEach(function(item2,index2,arr2) {
											if( item2.value == item.service_duration) {
												vm.assign_staff_member_details.service_custom_duration[index]['service_duration_text'] = item2.text;
											}
										});
									});
								}
								vm.assign_staff_member_list.push({	
									staffmember_name: vm.assign_staff_member_details.assigned_staffmember_name,
									staffmember_price: vm.assign_staff_member_details.assigned_staffmember_price,
									staffmember_min_capacity: vm.assign_staff_member_details.assigned_staffmember_min_capacity,
									staffmember_max_capacity: vm.assign_staff_member_details.assigned_staffmember_max_capacity,
									staffmember_id: vm.assign_staff_member_details.assigned_staffmember_id,									
									staffmember_custom_service : vm.assign_staff_member_details.service_custom_duration,
									staff_display_row: staff_display_row
								})							
							} else {
								let staff_display_row = true;
								if( "undefined" != typeof vm.staffmember_selected_location ){
									if( "string" == typeof vm.assign_staff_member_details.assigned_location_id && vm.assign_staff_member_details.assigned_location_id != vm.staffmember_selected_location ){
										staff_display_row = false;
									}
								}
								vm.assign_staff_member_list.push({
									staffmember_name: vm.assign_staff_member_details.assigned_staffmember_name,
									staffmember_price: vm.assign_staff_member_details.assigned_staffmember_price,
									staffmember_min_capacity: vm.assign_staff_member_details.assigned_staffmember_min_capacity,
									staffmember_max_capacity: vm.assign_staff_member_details.assigned_staffmember_max_capacity,
									staffmember_id: vm.assign_staff_member_details.assigned_staffmember_id,
									staffmember_location: vm.assign_staff_member_details.assigned_location_id,
									staff_display_row: staff_display_row
								});	
							}	
						}
						vm.bookingpress_close_assign_staffmember_modal()
						vm.bookingpress_format_assign_staffmember_price()
					}
				}
			},
			bookingpress_format_assign_staffmember_price() {
				const vm = this	
				var bookingpress_format_assigned_service_amts = { action:'bookingpress_format_assigned_staffmember_service_amounts', assign_staff_member_list : vm.assign_staff_member_list, _wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' }
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_format_assigned_service_amts ) )
				.then(function(response) {
					vm.assign_staff_member_list = response.data.assign_staff_member_list;
					let total_assigned = 0;
					vm.assign_staff_member_list.forEach( (element,index) => {
						if( "true" == element.staff_display_row || true == element.staff_display_row ){
							total_assigned++;
						}
					});
					vm.total_assigned_staff_members = total_assigned;
					<?php do_action( 'bookingpress_format_assign_staffmember_xhr_response' ); ?>
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
			staffmember_service_price_validate(evt) {
				const vm = this
                const regex = /^(?!.*(,,|,\.|\.,|\.\.))[\d.,]+$/gm;
                let m;
                if((m = regex.exec(evt)) == null ) {
                    vm.assign_staff_member_details.assigned_staffmember_price = '';
                }
                var price_number_of_decimals = this.price_number_of_decimals;                
                if((evt != null && evt.indexOf(".")>-1 && (evt.split('.')[1].length > price_number_of_decimals))){
                    vm.assign_staff_member_details.assigned_staffmember_price = evt.slice(0, -1);
                }                
            },
			bookingpress_set_staffmember_name(selected_value){
				const vm = this
				vm.assign_staffmembers.forEach(function(item,index,arr){
					if(item.staffmember_id == selected_value ) {
						vm.assign_staff_member_details.assigned_staffmember_name = item.staffmember_name;
						return;
					}	
				});
				//vm.assign_staff_member_details.assigned_staffmember_name = selected_value.target.parentElement.dataset.staffmember_name
			},
			bookingpress_delete_assigned_staffmember(delete_index){
				const vm = this
				vm.assign_staff_member_list.forEach(function(currentValue, index, arr){
					if(index == delete_index){
						vm.assign_staff_member_list.splice(index, 1)
					}
				});
			},
			bookingpress_edit_assigned_staffmember(edit_index, currentElement){
				const vm = this
				vm.assign_staff_member_list.forEach(function(currentValue, index, arr){
					if(index == edit_index){
						vm.assign_staff_member_details.assigned_staffmember_name = currentValue.staffmember_name
						vm.assign_staff_member_details.assigned_staffmember_price = currentValue.staffmember_price
						vm.assign_staff_member_details.assigned_staffmember_min_capacity = currentValue.staffmember_min_capacity
						vm.assign_staff_member_details.assigned_staffmember_max_capacity = currentValue.staffmember_max_capacity
						vm.assign_staff_member_details.assigned_staffmember_id = currentValue.staffmember_id
						vm.assign_staff_member_details.assigned_location_id = currentValue.staffmember_location
						if(typeof vm.service.enable_custom_service_duration !== 'undefined' && vm.service.enable_custom_service_duration == true) {
							if(currentValue.staffmember_custom_service != 'undefined' && currentValue.staffmember_custom_service != '' && currentValue.staffmember_custom_service != null) {
								vm.assign_staff_member_details.service_custom_duration = currentValue.staffmember_custom_service;
							} else {
								vm.assign_staff_member_details.service_custom_duration = vm.service.service_custom_duration;
							}
						}
						vm.assign_staff_member_details.assigned_staffmember_is_edit = 1
						<?php do_action( 'bookingpress_service_edit_assigned_staffmember' ); ?>
					}
				});

				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.assign_staff_member_modal_pos = (dialog_pos.top - 90)+'px'
				vm.assign_staff_member_modal_pos_right = '-'+(dialog_pos.right - 420)+'px';
				vm.open_assign_staff_member_modal = true

				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#assign_staffmember_modal .el-dialog.bpa-dialog--add-assign-staff' );
				}
			},
			get_staffmember_assigned_service(){
				const vm2 = this;				
				var postdata = [];							
				postdata.service_id = vm2.service.service_update_id;
				postdata.action = 'bookingpress_get_staffmember_service_data';
				<?php
					do_action('bookingpress_modify_get_staffmember_assigend_service_request');
				?>
				postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
				.then(function(response){
					if (response.data.variant == 'success') {
						vm2.assign_staff_member_list = response.data.staffmember_service_data;
						vm2.total_assigned_staff_members = vm2.assign_staff_member_list.length;
						<?php do_action('bookingpress_modify_assign_services_xhr_data'); ?>
					}else{
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: response.data.msg,
							type: 'error',
							customClass: 'error_notification',
						});	
					}
				}).catch(function(error){
					console.log(error);
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
					});
				});
			},
			bookingpress_service_change_status(service_id, service_new_status){
				const vm2 = this
				var postdata = [];
				postdata.service_id = service_id;
				postdata.service_new_status = service_new_status
				postdata.action = 'bookingpress_change_service_status';						
				postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
				.then(function(response){
					if (response.data.variant == 'success') {										
						vm2.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
						});
						vm2.loadServices()
						vm2.loadSearchCategories()
						vm2.loadServiceCategory()
					}
				}).catch(function(error){
					console.log(error);
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
					});
				});
			},
			bookingpress_open_shift_management_modal(service_id, service_name, is_special_workhour_configure = 'false'){
				const vm = this				
				vm.service.service_update_id = service_id
				vm.open_shift_management_modal = true
				vm.shift_management_service_title = service_name
				vm.service.bookingpress_configure_specific_service_workhour = false

				var postdata = [];
				postdata.service_id = vm.service.service_update_id;
				postdata.is_special_workhour_configure = is_special_workhour_configure;
				postdata.action = 'bookingpress_retrieve_service_shift_managment';
				postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
				<?php do_action('booknigpress_modify_service_shift_management_xhr_data'); ?>
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
				.then(function(response){					
					if(is_special_workhour_configure == 'true') {

						vm.service.bookingpress_configure_specific_service_workhour = response.data.configure_specific_workhours;
						if(response.data.workhour_service_data !== 'undefined' && response.data.workhour_service_data != '') {
							vm.service.workhours_timings = response.data.workhour_service_data
							response.data.workhour_data.forEach(function(currentValue, index, arr){
								vm.work_hours_days_arr.forEach(function(currentValue2, index2, arr2){
									if(currentValue2.day_name == currentValue.day_name) {
										vm.work_hours_days_arr[index2]['break_times'] = currentValue.break_times							
									}
								});
								vm.service.selected_break_timings[currentValue.day_name] = currentValue.break_times
							});
						}
						
					} else {
						vm.work_hours_days_arr = response.data.data
						response.data.data.forEach(function(currentValue, index, arr){
							vm.service.selected_break_timings[currentValue.day_name] = currentValue.break_times
						});
						vm.service.workhours_timings = response.data.selected_workhours
						vm.default_break_timings = response.data.default_break_times
					}
					vm.special_day_data_arr = response.data.special_day_data;
					vm.service_dayoff_arr = response.data.daysoff_data;

					<?php do_action( 'bookingpress_modify_service_shift_management_xhr_response' ); ?>
				}).catch(function(error){
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
					});
				});
				
				<?php
					do_action('bookingpress_after_open_shift_mgmt_modal');
				?>
			},
			bookingpress_open_service_special_days_modal_func(currentElement){
				const vm = this
				vm.special_days_add_modal = true;
				setTimeout(function(){
					vm.$refs['special_day_form'].resetFields();
				}, 500);
				vm.special_day_form.special_day_date = '';
				vm.special_day_form.start_time = '';
				vm.special_day_form.end_time = '';
				vm.special_day_form.special_day_workhour = [];				
				vm.edit_special_day_id = 0;							
				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.special_days_modal_pos = (dialog_pos.top - 90)+'px'
				vm.special_days_modal_pos_right = '-'+(dialog_pos.right - 400)+'px';	
				
				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#special_days_add_modal .el-dialog.bpa-dialog--special-days');
				}
			},
			bookingpress_save_service_shift_mgmt_details(){
				const vm2 = this
				vm2.is_disabled = true
				vm2.is_display_save_loader = '1'
				vm2.savebtnloading = true

				let postdata = { action: 'bookingpress_save_shift_mgmt_details', _wpnonce:'<?php echo esc_html( wp_create_nonce('bpa_wp_nonce') ); ?>' };

				postdata.services_data = JSON.stringify( vm2.service );
				postdata.special_day_details = JSON.stringify( vm2.special_day_data_arr );
				postdata.daysoff_details = JSON.stringify( vm2.service_dayoff_arr );
				<?php do_action('bookingpress_modify_save_service_workhours_postdata'); ?>
				
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
				.then(function(response){
					vm2.is_disabled = false
					vm2.is_display_save_loader = '0'
					if(response.data.variant != 'error'){
						vm2.open_shift_management_modal = false;
					}
					vm2.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
						duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
					});
					vm2.savebtnloading = false
					if (response.data.variant == 'success') {
						vm2.service.service_update_id = response.data.service_id
						vm2.loadServices()
					}
				}).catch(function(error){
					console.log(error);
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
					});
				});
			},
			bookinpress_pro_upload_service(response, file, fileList){
				var upload_filename = file.response.upload_file_name
				var upload_url = file.response.upload_url
				this.service.service_images_list.push({name: upload_filename, url: upload_url})
			},
			deposit_amount_validate(evt){
                const regex = /^(?!.*(,,|,\.|\.,|\.\.))[\d.,]+$/gm;
                let m;
                if((m = regex.exec(evt)) == null ) {
                    this.service.deposit_amount = '';
                }
                var price_number_of_decimals = this.price_number_of_decimals;                
                if((evt != null && evt.indexOf(".")>-1 && (evt.split('.')[1].length > price_number_of_decimals))){
                    this.service.deposit_amount = evt.slice(0, -1);
                }                
            },
			addServiceDayoff( service_dayoff_form ){
				const vm = this;
					
				this.$refs[service_dayoff_form].validate((valid) => {
					if (valid && vm.disable_service_holiday_btn == false) {							
						is_daysoff_exit = 0;						
						if(vm.special_day_data_arr !='') {
							vm.special_day_data_arr.forEach(function(item, index, arr) {									
																	
								if(vm.service_dayoff_form.dayoff_date >= item.special_day_start_date && vm.service_dayoff_form.dayoff_date <= item.special_day_end_date){
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_daysoff_exit = 1;											
								}
								if(vm.service_dayoff_form.dayoff_date_end >= item.special_day_start_date && vm.service_dayoff_form.dayoff_date_end <= item.special_day_end_date){
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_daysoff_exit = 1;
								}
								if(item.special_day_start_date >= vm.service_dayoff_form.dayoff_date && item.special_day_start_date <= vm.service_dayoff_form.dayoff_date_end){
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_daysoff_exit = 1;											
								}
								if(item.special_day_end_date >= vm.service_dayoff_form.dayoff_date && item.special_day_end_date <= vm.service_dayoff_form.dayoff_date_end){
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_daysoff_exit = 1;
								}
								
							});
						}
						vm.service_dayoff_arr.forEach(function(item, index, arr) {

							if(vm.service_dayoff_form.dayoff_date >= item.dayoff_date && vm.service_dayoff_form.dayoff_date <= item.dayoff_date_end && vm.edit_service_dayoff != item.id){
								vm.$notify({
									title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
									message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,
								});
								is_daysoff_exit = 1;									
							}								
							if(vm.service_dayoff_form.dayoff_date_end >= item.dayoff_date && vm.service_dayoff_form.dayoff_date_end <= item.dayoff_date_end && vm.edit_service_dayoff != item.id){
								vm.$notify({
									title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
									message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,
								});
								is_daysoff_exit = 1;									
							}
							if(item.dayoff_date >= vm.service_dayoff_form.dayoff_date && item.dayoff_date <= vm.service_dayoff_form.dayoff_date_end && vm.edit_service_dayoff != item.id){
								vm.$notify({
									title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
									message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,
								});
								is_daysoff_exit = 1;									
							}
							if(item.dayoff_date_end >= vm.service_dayoff_form.dayoff_date && item.dayoff_date_end <= vm.service_dayoff_form.dayoff_date_end && vm.edit_service_dayoff != item.id){
								vm.$notify({
									title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
									message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval($bookingpress_notification_duration); ?>,
								});
								is_daysoff_exit = 1;
							}	
							
						});
						let daysoff_formdata = vm.service_dayoff_form;

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
								is_daysoff_exit = 1;
								vm.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Holiday duration must be shorter than the repeat frequency', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
								});
							} else if( 'week' == repeat_freq_type && diff_in_days > ( repeat_frequency * 7 ) ){
								is_daysoff_exit = 1;
								vm.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Holiday duration must be shorter than the repeat frequency', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
								});
							} else if( 'month' == repeat_freq_type && diff_in_months > repeat_frequency ){
								is_daysoff_exit = 1;
								vm.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Holiday duration must be shorter than the repeat frequency', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
									duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
								});
							}
						}
						if(is_daysoff_exit == 0) {
							vm.disable_service_holiday_btn = true;
							var postdata = [];
							postdata.action = 'bookingpress_validate_service_daysoff'
							postdata.service_id = vm.service.service_update_id
							postdata.selected_date_range= vm.service_dayoff_form.dayoff_date;
							postdata.selected_date_range_end = vm.service_dayoff_form.dayoff_date_end;
							postdata.service_form_data = vm.service_dayoff_form;
							postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
							.then(function(response){
								vm.disable_service_holiday_btn = false;
								vm.service_daysoff_repeat_label = ( 'undefined' != typeof response.data.repeat_label ) ? response.data.repeat_label : '<?php esc_html_e( 'Once Off', 'bookingpress-appointment-booking') ?>';
								if(response.data.variant != 'undefined' && response.data.variant == 'warnning') {													
									vm.$confirm(response.data.msg, 'Warning', {
									confirmButtonText: '<?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?>',
									cancelButtonText: '<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>',
									type: 'warning'
									}).then(() => {
										if(vm.edit_service_dayoff != '' ){
											vm.editserviceDayoff();
										} else {
											vm.add_service_daysoff();
										}
									});				
								}else if(response.data.variant != 'undefined' && response.data.variant  == 'success') {
									if(vm.edit_service_dayoff != '' ){
										vm.editserviceDayoff();
									} else {
										vm.add_service_daysoff();
									}
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
			add_service_daysoff(){
				const vm = this;
				var ilength = parseInt(vm.service_dayoff_arr.length) + 1;
				let empDaysOffData = {};
				Object.assign(empDaysOffData, {id: ilength})
				Object.assign(empDaysOffData, {dayoff_date: vm.service_dayoff_form.dayoff_date});
				Object.assign(empDaysOffData, {dayoff_date_end: vm.service_dayoff_form.dayoff_date_end});
				Object.assign(empDaysOffData, {dayoff_name: vm.service_dayoff_form.dayoff_name});
				Object.assign(empDaysOffData, {dayoff_repeat: vm.service_dayoff_form.dayoff_repeat});
				Object.assign(empDaysOffData, {dayoff_repeat_label: '<?php esc_html_e( 'Once Off', 'bookingpress-appointment-booking' ); ?>'});

				if( true == empDaysOffData.dayoff_repeat ){
					Object.assign( empDaysOffData, {repeat_frequency: vm.service_dayoff_form.dayoff_repeat_frequency });
					Object.assign( empDaysOffData, {repeat_frequency_type: vm.service_dayoff_form.dayoff_repeat_freq_type });
					Object.assign( empDaysOffData, {repeat_duration: vm.service_dayoff_form.dayoff_repeat_duration });
					Object.assign( empDaysOffData, {repeat_times: vm.service_dayoff_form.dayoff_repeat_times });
					Object.assign( empDaysOffData, {repeat_date: vm.service_dayoff_form.dayoff_repeat_date });
					empDaysOffData.dayoff_repeat_label = vm.service_daysoff_repeat_label;
				}

				vm.service_dayoff_arr.push(empDaysOffData);
				vm.close_service_dayoff();
			},
			editserviceDayoff(){
				var vm = this
				var dayoff_id = vm.edit_service_dayoff;
				var dayoff_name = vm.service_dayoff_form.dayoff_name;
				var dayoff_date = vm.service_dayoff_form.dayoff_date
				var dayoff_date_end = vm.service_dayoff_form.dayoff_date_end
				var dayoff_repeat = vm.service_dayoff_form.dayoff_repeat
				vm.service_dayoff_arr.forEach(function(item, index, arr)
				{
					if(item.id == dayoff_id){
						item.dayoff_name = dayoff_name;
						item.dayoff_date = dayoff_date;
						item.dayoff_date_end = dayoff_date_end;
						item.dayoff_repeat = dayoff_repeat;
						if(dayoff_repeat){
							item.repeat_frequency = vm.service_dayoff_form.dayoff_repeat_frequency;
							item.repeat_frequency_type = vm.service_dayoff_form.dayoff_repeat_freq_type;
							item.repeat_duration = vm.service_dayoff_form.dayoff_repeat_duration;
							item.repeat_times = vm.service_dayoff_form.dayoff_repeat_times;
							item.repeat_date = vm.service_dayoff_form.dayoff_repeat_date;
							item.dayoff_repeat_label = vm.service_daysoff_repeat_label;
						}
					}
				});
				

				vm.close_service_dayoff();
				vm.bookingpress_service_format_daysoff_time();
			},
			bookingpress_service_format_daysoff_time(){
					const vm = this
					var postdata = [];
					postdata.action = 'bookingpress_format_service_daysoff_data'
					postdata.daysoff_data= vm.service_dayoff_arr;
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then(function(response){
						if(response.data.variant == "success"){
							vm.service_dayoff_arr = response.data.daysoff_details
						}
					}).catch(function(error){
						console.log(error);
					});
				},
			close_service_dayoff() {
				const vm = this;					
				vm.edit_service_dayoff = '';
				vm.service_dayoff_form.dayoff_name = '';
				vm.service_dayoff_form.dayoff_date = '';
				vm.service_dayoff_form.dayoff_date_range = '';
				vm.service_dayoff_form.dayoff_date_end = '';
				vm.service_dayoff_form.dayoff_repeat = false;

				vm.service_dayoff_form.dayoff_repeat_frequency = '1';
				vm.service_dayoff_form.dayoff_repeat_freq_type = 'year';
				vm.service_dayoff_form.dayoff_repeat_duration = 'forever';
				vm.service_dayoff_form.dayoff_repeat_times = '3';
				vm.service_dayoff_form.dayoff_repeat_date = '<?php esc_html_e( date( 'Y-m-d', strtotime( '+1 year') ) ); ?>';

				vm.reset_service_dayoff_form();
				vm.days_off_add_modal = false;
			},	
			service_daysoff_name_validation(value){
				const vm = this;
				vm.service_dayoff_form.dayoff_name = value.trim();				
			},
			bookingpress_after_select_service_holiday(eventdata){
				var vm = this;
				vm.service_dayoff_form.dayoff_date = eventdata[0];
				vm.service_dayoff_form.dayoff_date_end = eventdata[1];					
			},
			reset_service_dayoff_form(){
				const vm = this;
				setTimeout(function(){
					vm.$refs['service_dayoff_form'].resetFields();
					vm.service_dayoff_form.dayoff_name = '';
				},100);
				vm.edit_service_dayoff = '';
				vm.service_dayoff_form.dayoff_date = '';
				vm.service_dayoff_form.dayoff_date_range = '';
				vm.service_dayoff_form.dayoff_date_end = '';
				vm.service_dayoff_form.dayoff_repeat = false;
			},
			open_service_days_off_modal_func( currentElement ){
				const vm = this;
				vm.reset_service_dayoff_form();
				vm.service_dayoff_form.dayoff_name = '';
				var dialog_pos = currentElement.target.getBoundingClientRect();
				vm.days_off_modal_pos = (dialog_pos.top - 90)+'px'
				vm.days_off_modal_pos_right = '-'+(dialog_pos.right - 400)+'px';
				vm.days_off_add_modal = true;					
				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#days_off_add_modal .el-dialog.bpa-dialog--days-off');
				}
				
			},
			<?php
			do_action( 'bookingpress_add_service_dynamic_external_vue_methods' );
		}

		function bookingpress_add_service_dynamic_on_load_methods_func() {
			?>
				this.bookingpress_get_default_workhours();
			<?php
		}

		function bookingpress_modify_service_data_fields_func( $bookingpress_services_vue_data_fields ) {
			global $wpdb, $BookingPress, $bookingpress_woocommerce,$bookingpress_deposit_payment,$bookingpress_service_extra, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers,$bookingpress_global_options, $bookingpress_bring_anyone_with_you;
			
			$bookingpress_options = $bookingpress_global_options->bookingpress_global_options();

			$bookingpress_services_vue_data_fields['service']['service_before_buffer_time']      = 0;
			$bookingpress_services_vue_data_fields['service']['service_before_buffer_time_unit'] = 'm';
			$bookingpress_services_vue_data_fields['service']['service_after_buffer_time']       = 0;
			$bookingpress_services_vue_data_fields['service']['service_after_buffer_time_unit']  = 'm';
			$bookingpress_services_vue_data_fields['service']['max_capacity']                    = 1;
			$bookingpress_services_vue_data_fields['service']['min_capacity']                    = 1;

			$bookingpress_services_vue_data_fields['service']['show_service_on_site'] = 'true';		

			// Service settings fields
			$bookingpress_services_vue_data_fields['service']['minimum_time_required_before_booking']      = 'inherit';
			$bookingpress_services_vue_data_fields['service']['minimum_time_required_before_rescheduling'] = 'inherit';
			$bookingpress_services_vue_data_fields['service']['minimum_time_required_before_cancelling']   = 'inherit';
			$bookingpress_services_vue_data_fields['service']['service_images_list']                       = array();
			$bookingpress_services_vue_data_fields['service']['service_expiration_date']                   = '';
			$bookingpress_services_vue_data_fields['service']['service_start_date']                        = '';

			$bookingpress_services_vue_data_fields['open_add_new_category_popup']     = false;
			$bookingpress_services_vue_data_fields['is_category_display_save_loader'] = '0';
			$bookingpress_services_vue_data_fields['is_category_disabled']            = false;
			$bookingpress_services_vue_data_fields['add_new_category_modal_pos_top'] = '320px' ;
			$bookingpress_services_vue_data_fields['add_new_category_modal_pos_right'] = '10px' ;

			
			$bookingpress_services_vue_data_fields['bpa_service_shift_management_external_class'] = 'bpa-dialog bpa-dialog--fullscreen bpa-dialog__shift-management bpa--is-page-scrollable-tablet';

			$bookingpress_service_categories_item[] = array(
				'value' => 'add_new',
				'label' => __( 'Add New', 'bookingpress-appointment-booking' ),
			);
			if ( ! empty( $bookingpress_services_vue_data_fields['serviceCatOptions'] ) && is_array( $bookingpress_services_vue_data_fields['serviceCatOptions'] ) ) {
				$bookingpress_services_vue_data_fields['serviceCatOptions'] = array_merge( $bookingpress_service_categories_item, $bookingpress_services_vue_data_fields['serviceCatOptions'] );
			} else {
				$bookingpress_services_vue_data_fields['serviceCatOptions'] = $bookingpress_service_categories_item;
			}
			$bookingpress_currency_name = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
			$currency_symbol            = ! empty( $bookingpress_currency_name ) ? $BookingPress->bookingpress_get_currency_symbol( $bookingpress_currency_name ) : '';
			$bookingpress_services_vue_data_fields['currency_symbol'] = $currency_symbol;

			$bookingpress_services_vue_data_fields['bookingpress_update_index']         = '';
			$bookingpress_services_vue_data_fields['is_deposit_payment_activated']      = $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation();
			$bookingpress_services_vue_data_fields['is_service_extra_module_activated'] = $bookingpress_service_extra->bookingpress_check_service_extra_module_activation();

			$bookingpress_services_vue_data_fields['is_staffmember_activated'] = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();

			$bookingpress_services_vue_data_fields['is_bring_anyone_with_you_activated'] = $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation();
 
			$bookingpress_services_vue_data_fields['default_minimum_time_options'] = array(
				array(
					'text'  => __( 'Disabled', 'bookingpress-appointment-booking' ),
					'value' => 'disabled',
				),
				array(
					'text'  => __( '5 min', 'bookingpress-appointment-booking' ),
					'value' => '5',
				),
				array(
					'text'  => __( '10 min', 'bookingpress-appointment-booking' ),
					'value' => '10',
				),
				array(
					'text'  => __( '12 min', 'bookingpress-appointment-booking' ),
					'value' => '12',
				),
				array(
					'text'  => __( '15 min', 'bookingpress-appointment-booking' ),
					'value' => '15',
				),
				array(
					'text'  => __( '20 min', 'bookingpress-appointment-booking' ),
					'value' => '20',
				),
				array(
					'text'  => __( '30 min', 'bookingpress-appointment-booking' ),
					'value' => '30',
				),
				array(
					'text'  => __( '45 min', 'bookingpress-appointment-booking' ),
					'value' => '45',
				),
				array(
					'text'  => __( '1 h', 'bookingpress-appointment-booking' ),
					'value' => '60',
				),
				array(
					'text'  => __( '1 h 30 min', 'bookingpress-appointment-booking' ),
					'value' => '90',
				),
				array(
					'text'  => __( '2 h', 'bookingpress-appointment-booking' ),
					'value' => '120',
				),
				array(
					'text'  => __( '3 h', 'bookingpress-appointment-booking' ),
					'value' => '180',
				),
				array(
					'text'  => __( '6 h', 'bookingpress-appointment-booking' ),
					'value' => '360',
				),
				array(
					'text'  => __( '7 h', 'bookingpress-appointment-booking' ),
					'value' => '420',
				),
				array(
					'text'  => __( '8 h', 'bookingpress-appointment-booking' ),
					'value' => '480',
				),
				array(
					'text'  => __( '9 h', 'bookingpress-appointment-booking' ),
					'value' => '540',
				),
				array(
					'text'  => __( '10 h', 'bookingpress-appointment-booking' ),
					'value' => '600',
				),
				array(
					'text'  => __( '11 h', 'bookingpress-appointment-booking' ),
					'value' => '660',
				),
				array(
					'text'  => __( '12 h', 'bookingpress-appointment-booking' ),
					'value' => '720',
				),
				array(
					'text'  => __( '1 day', 'bookingpress-appointment-booking' ),
					'value' => '1440',
				),
				array(
					'text'  => __( '2 days', 'bookingpress-appointment-booking' ),
					'value' => '2880',
				),
				array(
					'text'  => __( '3 days', 'bookingpress-appointment-booking' ),
					'value' => '4320',
				),
				array(
					'text'  => __( '4 days', 'bookingpress-appointment-booking' ),
					'value' => '5760',
				),
				array(
					'text'  => __( '5 days', 'bookingpress-appointment-booking' ),
					'value' => '7200',
				),
				array(
					'text'  => __( '6 days', 'bookingpress-appointment-booking' ),
					'value' => '8640',
				),
				array(
					'text'  => __( '1 week', 'bookingpress-appointment-booking' ),
					'value' => '10080',
				),
				array(
					'text'  => __( '2 week', 'bookingpress-appointment-booking' ),
					'value' => '20160',
				),
				array(
					'text'  => __( '3 week', 'bookingpress-appointment-booking' ),
					'value' => '30240',
				),
				array(
					'text'  => __( '4 week', 'bookingpress-appointment-booking' ),
					'value' => '40320',
				),
				array(
					'text'  => __( '6 month', 'bookingpress-appointment-booking' ),
					'value' => '262800',
				),
				array(
					'text'  => __( 'Inherit from general setting', 'bookingpress-appointment-booking' ),
					'value' => 'inherit',
				),
			);

			/* work hour */

			$bookingpress_services_vue_data_fields['work_hours_days_arr'] = array();

			$bookingpress_services_vue_data_fields['service']['workhours_timings']      = array(
				'Monday'    => array(
					'start_time' => '09:00:00',
					'end_time'   => '17:00:00',
				),
				'Tuesday'   => array(
					'start_time' => '09:00:00',
					'end_time'   => '17:00:00',
				),
				'Wednesday' => array(
					'start_time' => '09:00:00',
					'end_time'   => '17:00:00',
				),
				'Thursday'  => array(
					'start_time' => '09:00:00',
					'end_time'   => '17:00:00',
				),
				'Friday'    => array(
					'start_time' => '09:00:00',
					'end_time'   => '17:00:00',
				),
				'Saturday'  => array(
					'start_time' => 'Off',
					'end_time'   => 'Off',
				),
				'Sunday'    => array(
					'start_time' => 'Off',
					'end_time'   => 'Off',
				),
			);
			$bookingpress_services_vue_data_fields['service']['selected_break_timings'] = array(
				'Monday'    => array(),
				'Tuesday'   => array(),
				'Wednesday' => array(),
				'Thursday'  => array(),
				'Friday'    => array(),
				'Saturday'  => array(),
				'Sunday'    => array(),
			);
			$bookingpress_services_vue_data_fields['open_add_break_modal']              = false;
			$bookingpress_services_vue_data_fields['break_modal_pos']                   = '254px';
			$bookingpress_services_vue_data_fields['break_modal_pos_right']             = '';
			$bookingpress_services_vue_data_fields['default_break_timings']             = array();
			$bookingpress_services_vue_data_fields['break_selected_day']                = 'Monday';
			$bookingpress_services_vue_data_fields['break_timings']                     = array(
				'start_time'     => '',
				'end_time'       => '',
				'old_start_time' => '',
				'old_end_time'   => '',
			);
			$bookingpress_services_vue_data_fields['rules_add_break']                   = array(
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
			$bookingpress_services_vue_data_fields['is_mask_display']                   = false;
			$bookingpress_services_vue_data_fields['service']['bookingpress_configure_specific_service_workhour'] = false;

			$bookingpress_services_vue_data_fields['special_day_data_arr'] = array();
			$bookingpress_services_vue_data_fields['edit_special_day_id']  = '';
			$bookingpress_services_vue_data_fields['disable_service_special_day_btn']  = false;			

			$default_start_time    = '00:00:00';
			$default_end_time      = '23:55:00';
			$step_duration_val     = 05;
			$default_break_timings = array();
			$curr_time             = $tmp_start_time = date( 'H:i:s', strtotime( $default_start_time ) );
			$tmp_end_time          = date( 'H:i:s', strtotime( $default_end_time ) );
			do {

				$tmp_start_time = $default_start_time;
				$tmp_time_obj = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
				$end_time                = $tmp_time_obj->format( 'H:i:s' );

				if($end_time == "00:00:00"){
                    $end_time = "24:00:00";
                }

				$default_break_timings[] = array(
					'start_time'           => $curr_time,
					'formatted_start_time' => date( $bookingpress_options['wp_default_time_format'], strtotime( $curr_time ) ),
					'end_time'             => $end_time,
					'formatted_end_time' => date($bookingpress_options['wp_default_time_format'], strtotime($end_time))." ".($end_time == "24:00:00" ? esc_html__('Next Day', 'bookingpress-appointment-booking') : '' ),
					
				);

				if($end_time == "24:00:00"){
                    break;
                }

				$tmp_time_obj            = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
				$curr_time = $tmp_time_obj->format( 'H:i:s' );
			} while ( $curr_time <= $default_end_time );

			$bookingpress_services_vue_data_fields['specialday_hour_list'] = $default_break_timings;
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
					'formatted_start_time' => date( $bookingpress_options['wp_default_time_format'], strtotime( $curr_time ) ),
					'end_time'             => $end_time,
					'formatted_end_time'   => date( $bookingpress_options['wp_default_time_format'], strtotime( $end_time ) ),
				);
				$tmp_time_obj             = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
				$curr_time = $tmp_time_obj->format( 'H:i:s' );
			} while ( $curr_time <= $default_end_time );

			$bookingpress_services_vue_data_fields['specialday_break_hour_list'] = $default_break_timings2;
			$bookingpress_services_vue_data_fields['special_day_form']           = array(
				'special_day_date'     => '',
				'start_time'           => '',
				'end_time'             => '',
				'special_day_workhour' => array(),
			);
			$bookingpress_services_vue_data_fields['rules_special_day']          = array(
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
	
			// Assign Staff Member Data variables
			$bookingpress_services_vue_data_fields['open_assign_staff_member_modal']      = false;
			$bookingpress_services_vue_data_fields['assign_staff_member_modal_pos']       = '200';
			$bookingpress_services_vue_data_fields['assign_staff_member_modal_pos_right'] = '0';

			$bookingpress_staffmember_details = array();
			$bookingpress_staffmember_data    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_status = %d", 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
			foreach ( $bookingpress_staffmember_data as $bookingpress_staffmember_key => $bookingpress_staffmember_val ) {
				$bookingpress_staff_member_name = ( $bookingpress_staffmember_val['bookingpress_staffmember_firstname'] == '' && $bookingpress_staffmember_val['bookingpress_staffmember_lastname'] == '' ) ? $bookingpress_staffmember_val['bookingpress_staffmember_email'] : $bookingpress_staffmember_val['bookingpress_staffmember_firstname'] . ' ' . $bookingpress_staffmember_val['bookingpress_staffmember_lastname'];

				$bookingpress_staffmember_details[] = array(
					'staffmember_name' => $bookingpress_staff_member_name,
					'staffmember_id'   => $bookingpress_staffmember_val['bookingpress_staffmember_id'],
				);
			}
			$bookingpress_services_vue_data_fields['assign_staffmembers'] = $bookingpress_staffmember_details;

			$bookingpress_services_vue_data_fields['assign_staff_member_details'] = array(
				'assigned_staffmember_id'      => '',
				'assigned_staffmember_name'    => '',
				'assigned_staffmember_price'   => '',
				'assigned_staffmember_min_capacity' => 1,
				'assigned_staffmember_max_capacity' => 1,
				'assigned_staffmember_is_edit' => 0,
				'assigned_location_id' => 0,
			);
			$bookingpress_services_vue_data_fields['assign_staff_member_list']    = array();
			$bookingpress_services_vue_data_fields['total_assigned_staff_members'] = 0;

			$bookingpress_services_vue_data_fields['shift_management_service_title'] = '';
			$bookingpress_services_vue_data_fields['open_shift_management_modal'] = false;

			$bookingpress_services_vue_data_fields['special_days_add_modal']       = false;
			$bookingpress_services_vue_data_fields['special_days_modal_pos']       = '200';
			$bookingpress_services_vue_data_fields['special_days_modal_pos_right'] = '0';

			$bookingpress_services_vue_data_fields['bookingpress_advance_option_active_tab'] = 'service_settings';

			//$bookingpress_services_vue_data_fields['is_gallery_expand'] = 0;	
			
			$bookingpress_services_vue_data_fields['disabledOtherDates'] = '';
			$bookingpress_services_vue_data_fields['is_edit_break'] = 0;

			$bookingpress_services_vue_data_fields['rules']['service_before_buffer_time'] =  array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter before buffer time', 'bookingpress-appointment-booking'),
					'trigger'  => 'blur',
				),
			);
			$bookingpress_services_vue_data_fields['rules']['service_after_buffer_time'] =  array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter after buffer time', 'bookingpress-appointment-booking'),
					'trigger'  => 'blur',
				),
			);
			$bookingpress_services_vue_data_fields['rules']['max_capacity'] =  array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter max capacity', 'bookingpress-appointment-booking'),
					'trigger'  => 'blur',
				),
			);
			$bookingpress_services_vue_data_fields['rules']['max_capacity'] =  array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter max capacity', 'bookingpress-appointment-booking'),
					'trigger'  => 'blur',
				),
			);
			$bookingpress_services_vue_data_fields['rules']['min_capacity'] =  array(
				array(
					'required' => true,
					'message'  => esc_html__('Please enter min capacity', 'bookingpress-appointment-booking'),
					'trigger'  => 'blur',
				),
			);

			$bookingpress_services_vue_data_fields['display_service_workhours'] = true;

			$bookingpress_services_vue_data_fields['edit_index'] = 0;


			/** Service Holiday */

			$bookingpress_services_vue_data_fields['days_off_add_modal']       = false;
			$bookingpress_services_vue_data_fields['days_off_modal_pos']       = '0';
			$bookingpress_services_vue_data_fields['days_off_modal_pos_right'] = '0';
			
			$bookingpress_services_vue_data_fields['disable_service_holiday_btn'] = false;

			$bookingpress_services_vue_data_fields['service_dayoff_arr'] = array();
			$bookingpress_services_vue_data_fields['edit_service_dayoff'] = '';

			$bookingpress_services_vue_data_fields['service_dayoff_repeat_frequency_type_opts'] = array(
				'day' => esc_html__( 'Days', 'bookingpress-appointment-booking' ),
				'week' => esc_html__( 'Week', 'bookingpress-appointment-booking' ),
				'month' => esc_html__( 'Month', 'bookingpress-appointment-booking' ),
				'year' => esc_html__( 'Year', 'bookingpress-appointment-booking' )
			);

			$bookingpress_services_vue_data_fields['service_repeat_duration_opts'] = array(
				'forever' => esc_html__( 'Forever', 'bookingpress-appointment-booking'),
				'no_of_times' => esc_html__( 'Specific No. of Times', 'bookingpress-appointment-booking' ),
				'until' => esc_html__( 'Until', 'bookingpress-appointment-booking')
			);

			$bookingpress_services_vue_data_fields['service_dayoff']      = array();
			$bookingpress_services_vue_data_fields['days_off_year_filter']    = date( 'Y' );
			$bookingpress_services_vue_data_fields['service_daysoff_repeat_label'] = esc_html__( 'Once Off', 'bookingpress-appointment-booking' );
			$bookingpress_services_vue_data_fields['service_dayoff_form'] = array(
				'dayoff_name'   => '',
				'dayoff_date'   => '',
				'dayoff_date_end'   => '',
				'dayoff_date_range' => '',
				'dayoff_repeat' => false,
				'dayoff_repeat_frequency' => 1,
				'dayoff_repeat_freq_type' => 'year',
				'dayoff_repeat_duration' => 'forever',
				'dayoff_repeat_times' => 3,
				'dayoff_repeat_date' => date('Y-m-d', strtotime( '+1 year') )
			);
			$bookingpress_services_vue_data_fields['rules_dayoff']            = array(
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
			/** Service Holiday */

			return $bookingpress_services_vue_data_fields;
		}

		function bookingpress_get_service_max_capacity( $bookingpress_service_id ) {
			global $wpdb, $BookingPress, $tbl_bookingpress_services, $bookingpress_services;
			$bookingpress_max_capacity = 1;
			if ( ! empty( $bookingpress_service_id ) ) {
				$bookingpress_tmp_max_capacity = $bookingpress_services->bookingpress_get_service_meta( $bookingpress_service_id, 'max_capacity' );
				$bookingpress_max_capacity     = ! empty( $bookingpress_tmp_max_capacity ) ? $bookingpress_tmp_max_capacity : 1;
			}
			return $bookingpress_max_capacity;
		}

		function bookingpress_get_service_min_capacity( $bookingpress_service_id ) {
			global $wpdb, $BookingPress, $tbl_bookingpress_services, $bookingpress_services;
			$bookingpress_min_capacity = 1;
			if ( ! empty( $bookingpress_service_id ) ) {
				$bookingpress_tmp_min_capacity = $bookingpress_services->bookingpress_get_service_meta( $bookingpress_service_id, 'min_capacity' );
				$bookingpress_min_capacity     = ! empty( $bookingpress_tmp_min_capacity ) ? $bookingpress_tmp_min_capacity : 1;
			}
			return $bookingpress_min_capacity;
		}

		function bookingpress_after_delete_service_func( $service_id ) {
			global $wpdb,$tbl_bookingpress_staffmembers_services;
			$wpdb->delete( $tbl_bookingpress_staffmembers_services, array( 'bookingpress_service_id' => $service_id ), array( '%d' ) );
		}
		function bookingpress_format_service_special_days_data_func() {
			global $wpdb, $bookingpress_global_options,$BookingPress;
			$response                    = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'format_service_special_days', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			$response['daysoff_details'] = '';
			$bookingpress_global_settings   = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_date_format       = $bookingpress_global_settings['wp_default_date_format'];
			$bookingpress_time_format       = $bookingpress_global_settings['wp_default_time_format'];
			$bookingpress_special_days_data = ! empty( $_POST['special_days_data'] ) ? array_map(array($BookingPress,'appointment_sanatize_field'),$_POST['special_days_data']) : array(); //phpcs:ignore

			if ( ! empty( $bookingpress_special_days_data ) && is_array( $bookingpress_special_days_data ) ) {
				foreach ( $bookingpress_special_days_data as $k => $v ) {
					$bookingpress_special_days_data[ $k ]['special_day_formatted_start_date'] = date( $bookingpress_date_format, strtotime( $v['special_day_start_date'] ) );
					$bookingpress_special_days_data[ $k ]['special_day_formatted_end_date']   = date( $bookingpress_date_format, strtotime( $v['special_day_end_date'] ) );
					$bookingpress_special_days_data[ $k ]['formatted_start_time']             = date( $bookingpress_time_format, strtotime( $v['start_time'] ) );
					$bookingpress_special_days_data[ $k ]['formatted_end_time']               = date( $bookingpress_time_format, strtotime( $v['end_time'] ) );
					if ( ! empty( $v['special_day_workhour'] ) ) {
						foreach ( $v['special_day_workhour'] as $k2 => $v2 ) {
							$bookingpress_special_days_data[ $k ]['special_day_workhour'][ $k2 ]['formatted_start_time'] = date( $bookingpress_time_format, strtotime( $v2['start_time'] ) );
							$bookingpress_special_days_data[ $k ]['special_day_workhour'][ $k2 ]['formatted_end_time']   = date( $bookingpress_time_format, strtotime(  $v2['end_time'] ) );
						}
					}
				}
				$response['variant']         = 'success';
				$response['title']           = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']             = esc_html__( 'Details formatted successfully', 'bookingpress-appointment-booking' );
				$response['daysoff_details'] = $bookingpress_special_days_data;
			}
			echo wp_json_encode( $response );
			exit;
		}		
	}
}
global $bookingpress_pro_services;
$bookingpress_pro_services = new bookingpress_pro_services();
