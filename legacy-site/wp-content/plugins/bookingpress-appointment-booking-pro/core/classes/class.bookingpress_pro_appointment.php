<?php
$bookingpress_geoip_file = BOOKINGPRESS_PRO_LIBRARY_DIR . '/geoip/autoload.php';
require $bookingpress_geoip_file;
use GeoIp2\Database\Reader;

if ( ! class_exists( 'bookingpress_pro_appointment' ) ) {
	class bookingpress_pro_appointment Extends BookingPress_Core {
		var $bookingpress_global_data;

		function __construct() {
			add_filter( 'bookingpress_modify_appointment_view_file_path', array( $this, 'bookingpress_modify_appointment_file_path_func' ), 10 );
			add_filter( 'bookingpress_modify_appointment_data_fields', array( $this, 'bookingpress_modify_appointment_data_fields_func' ), 10 );
			add_action( 'bookingpress_appointment_dynamic_bulk_action', array( $this, 'bookingpress_appointment_dynamic_bulk_action_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_pro_bulk_appointment_logs_action', array( $this, 'bookingpress_pro_bulk_appointment_logs_action_func' ), 10 );
			add_action( 'bookingpress_appointment_add_dynamic_vue_methods', array( $this, 'bookingpress_appointment_add_dynamic_vue_methods_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_export_appointment_data', array( $this, 'bookingpress_export_appointment_data_func' ), 10 );

			add_filter('bookingpress_modify_appointment_data', array($this, 'bookingpress_modify_appointment_data_func'), 10, 1);

			add_action('bookingpress_after_selecting_service_at_backend', array($this, 'bookingpress_after_selecting_service_at_backend_func'));
			add_action('wp_ajax_bookingpress_get_backend_service_extras', array($this, 'bookingpress_get_backend_service_extras_func'));

			add_action('wp_ajax_bookingpress_admin_appointment_recalculate_data', array($this, 'bookingpress_admin_appointment_recalculate_data_func'));

			add_filter('bookingpress_modify_backend_add_appointment_entry_data', array($this, 'bookingpress_modify_backend_add_appointment_entry_data_func'), 10, 2);
			add_filter('bookingpress_modify_appointment_booking_fields', array($this, 'bookingpress_modify_appointment_booking_fields_func'), 10, 3);
			add_filter('bookingpress_modify_payment_log_fields', array($this, 'bookingpress_modify_payment_log_fields_func'), 10, 2);

			add_action('bookingpress_edit_appointment_details', array($this, 'bookingpress_edit_appointment_details_func'));
			add_action('wp_ajax_bookingpress_get_appointment_meta_values', array($this, 'bookingpress_get_appointment_meta_values_func'));

			add_action('bookingpress_after_insert_entry_data_from_backend', array($this, 'bookingpress_after_insert_entry_data_from_backend_func'), 10, 2);
			add_action('bookingpress_after_add_appointment_from_backend', array($this, 'bookingpress_after_add_appointment_from_backend_func'), 10, 3);

			add_action('wp_ajax_bookingpress_get_customer_details', array($this, 'wp_ajax_bookingpress_get_customer_details_func'), 10);
			add_action( 'bookingpress_add_appointment_model_reset', array( $this, 'bookingpress_add_appointment_model_reset_callback') );
			add_action( 'wp_ajax_bookingpress_get_customer_form_field_values', array( $this, 'bookingpress_get_customer_form_field_values_func') );

			add_action('bookingpress_appointment_reset_filter',array($this,'bookingpress_appointment_reset_filter_func'));

			//Modify edit appointment data
			add_filter('bookingpress_modify_edit_appointment_data', array($this, 'bookingpress_modify_edit_appointment_data_func'));

			add_action('bookingpress_additional_disable_dates', array($this, 'bookingpress_after_get_backend_disable_dates_func'), 12);


			//After update appointment from backend
			add_action('bookingpress_after_update_appointment', array($this, 'bookingpress_after_update_appointment_func'), 10, 1);

			add_action('bookingpress_change_backend_service', array($this, 'bookingpress_change_backend_service_func'));

			add_action('bookingpress_additional_disable_dates', array($this, 'bookingpress_additional_disable_dates_func'));

			add_action('bookingpress_admin_panel_vue_methods', array($this, 'bookingpress_admin_panel_vue_methods_func'));

			add_action('bookingpress_modify_request_after_validation', array($this, 'bookingpress_modify_request_after_validation_func'));

			add_filter('bookingpress_filter_generated_share_url_externally', array($this, 'bookingpress_filter_generated_share_url_externally_func'), 10, 2);

			add_filter('bookingpress_appointment_add_view_field', array($this, 'bookingpress_appointment_add_view_field_func'), 10, 2);

			add_action('wp_ajax_bookingpress_get_refund_amount',array($this,'bookingpress_get_refund_amount_func'),10);

			add_action('wp_ajax_bookingpress_apply_for_refund',array($this,'bookingpress_apply_for_refund_func'),10);

			add_action('bookingpress_before_delete_appointment',array($this,'bookingpress_before_delete_appointment_func'),12);

			add_action('bookingpress_after_rescheduled_appointment', array($this,'bookingpress_after_rescheduled_appointment_func'));
			
		}

		function bookingpress_after_rescheduled_appointment_func( $rescheduled_appointment_id ){

			if( !empty(sanitize_text_field($_REQUEST['action'])) && sanitize_text_field($_REQUEST['action']) == 'bookingpress_save_appointment_booking' ){
				update_option('bookingpress_rescheduled_appointment_'.$rescheduled_appointment_id, '0');
			}
		}
		           
        /**
         * bookingpress_before_delete_appointment_func
         *
         * @param  mixed $appointment_id
         * @return void
         */
        function bookingpress_before_delete_appointment_func($appointment_id ) { 
            global $wpdb,$tbl_bookingpress_payment_logs,$tbl_bookingpress_appointment_bookings;
            $bookingperss_appointments_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_payment_id,bookingpress_is_cart,bookingpress_order_id FROM {$tbl_bookingpress_appointment_bookings}  WHERE bookingpress_appointment_booking_id = %d",$appointment_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
            if(!empty($bookingperss_appointments_data['bookingpress_is_cart']) && $bookingperss_appointments_data['bookingpress_is_cart'] == 1) {
                $bookingpress_order_id = !empty($bookingperss_appointments_data['bookingpress_order_id']) ? intval($bookingperss_appointments_data['bookingpress_order_id']) : 0;
				$bookingpress_payment_id = !empty($bookingperss_appointments_data['bookingpress_order_id']) ? intval($bookingperss_appointments_data['bookingpress_payment_id']) : 0;
                $bookingperss_cart_appointemnt_data = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings}  WHERE bookingpress_order_id = %d AND bookingpress_appointment_booking_id != %d ",$bookingpress_order_id,$appointment_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
                if($bookingperss_cart_appointemnt_data == 0) {
					$wpdb->delete($tbl_bookingpress_payment_logs, array( 'bookingpress_payment_log_id' => $bookingpress_payment_id ), array( '%d' ));
                }
            }
        }
		
		/**
		 * bookingpress_apply_for_refund_func
		 *
		 * @return void
		 */
		function bookingpress_apply_for_refund_func() {
			global $wpdb, $BookingPress, $BookingPressPro,$bookingpress_pro_payment_gateways;
			$response = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'apply_for_refund', true, 'bpa_wp_nonce' );           

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
			$response['title'] = esc_html__('Error', 'bookingpress-appointment-booking');
			$response['msg'] = esc_html__('Something went wrong while process with refund', 'bookingpress-appointment-booking');
			
			$bookingpress_refund_data = ! empty( $_REQUEST['bookingpress_refund_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['bookingpress_refund_data'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function


			if(!empty($bookingpress_refund_data)) {

				$response = $bookingpress_pro_payment_gateways->bookingpress_apply_for_refund($response,$bookingpress_refund_data);
			}

			wp_send_json( $response );
			die;
		}
		
		/**
		 * Function for get refund amount
		 *
		 * @return void
		 */
		function bookingpress_get_refund_amount_func() {
			global $wpdb, $BookingPress, $BookingPressPro;
			$response = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'get_refund_amount', true, 'bpa_wp_nonce' );           

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
			$response['title'] = esc_html__('Error', 'bookingpress-appointment-booking');
			$response['msg'] = esc_html__('Something went wrong', 'bookingpress-appointment-booking');
			
			$bookingpress_appointment_id = ! empty( $_REQUEST['bookingpress_appointment_id'] ) ? intval( $_REQUEST['bookingpress_appointment_id'] ) : '';
			$bookingpress_payment_id = ! empty( $_REQUEST['bookingpress_payment_id'] ) ? intval( $_REQUEST['bookingpress_payment_id'] ) : '';

			if(!empty($bookingpress_appointment_id) && !empty($bookingpress_payment_id)) {
				$refund_data = $this->bookingpress_calculate_refund_amount($bookingpress_payment_id,$bookingpress_appointment_id);				
				$response['refund_amount'] = $refund_data['refund_amount']; 
				$response['default_refund_amount'] = $refund_data['default_refund_amount']; 
				$response['is_past_appointment'] = $refund_data['is_past_appointment'];
				$response['variant'] = 'success';
                $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg'] = esc_html__('Data retrieved successfully', 'bookingpress-appointment-booking');				
            }
			echo wp_json_encode($response);
			exit;
		}
		
		/**
		 * bookingpress_calculate_refund_amount
		 *
		 * @param  mixed $bookingpress_payment_id
		 * @param  mixed $bookingpress_appointment_id
		 * @param  mixed $from
		 * @return void
		 */
		function bookingpress_calculate_refund_amount($bookingpress_payment_id,$bookingpress_appointment_id,$from = 0) {

			global $BookingPress,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$wpdb;
		
			$bookingpress_paid_amount = 0;
			$refund_data['refund_amount'] = 0;
			$refund_data['default_refund_amount'] = 0;
			$refund_data['is_past_appointment'] = 0;
			$refund_data['refund_type'] = '';
			$refund_data['appointment_id'] = $bookingpress_appointment_id;
			$bookingpress_refund_mode = $BookingPress->bookingpress_get_settings('bookingpress_refund_mode','payment_setting');
			$refund_data['refund_type'] = !empty($bookingpress_refund_mode ) ? $bookingpress_refund_mode : 'full';
			$refund_data['refund_gateway'] = !empty($bookingpress_refund_mode ) ? $bookingpress_refund_mode : 'full';
		
			/* get the appointment data */			
			$bookingpress_appointment_logs= $wpdb->get_row($wpdb->prepare("SELECT `bookingpress_payment_id`,`bookingpress_appointment_date`,`bookingpress_appointment_time`,`bookingpress_service_currency` FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			if(empty($bookingpress_payment_id)) {
				$bookingpress_payment_id = !empty($bookingpress_appointment_logs ['bookingpress_payment_id']) ? intval($bookingpress_appointment_logs ['bookingpress_payment_id']) : 0;
				$refund_data['payment_id'] = $bookingpress_payment_id;
			}

			/* get the payment log data */
			$bookingpress_appointment_payment_logs_data= $wpdb->get_row($wpdb->prepare("SELECT bookingpress_paid_amount,bookingpress_payment_gateway,bookingpress_payment_currency FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = %d", $bookingpress_payment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			
			/* refund amount changes filter */

			$refund_data['refund_amount'] = $bookingpress_paid_amount = apply_filters('bookingpress_modify_refund_data_amount', $bookingpress_appointment_payment_logs_data['bookingpress_paid_amount'], $bookingpress_payment_id);
			$refund_data['default_refund_amount'] = $bookingpress_appointment_payment_logs_data['bookingpress_paid_amount'];						
			$refund_data['refund_gateway'] = $bookingpress_appointment_payment_logs_data['bookingpress_payment_gateway'];
			$refund_data['refund_currency'] = $bookingpress_appointment_payment_logs_data['bookingpress_payment_currency'];

			if(!empty($bookingpress_appointment_logs['bookingpress_appointment_date'])) {
				$bookingpress_appointment_date = $bookingpress_appointment_logs['bookingpress_appointment_date'];
				$bookingpress_appointment_time = $bookingpress_appointment_logs['bookingpress_appointment_time'];
			}
			$bookingpress_from_time = current_time('timestamp');			
			$bookingpress_to_time = strtotime($bookingpress_appointment_date .' '. $bookingpress_appointment_time);

			if($bookingpress_to_time < $bookingpress_from_time) {
				$refund_data['is_past_appointment'] = 1;
			}

			if($from == 1 && $bookingpress_refund_mode == 'full') {
				return $refund_data;
			}

			$bookingpress_time_diff_for_refund = 0;
			if($bookingpress_to_time > $bookingpress_from_time ) {
				$bookingpress_time_diff_for_refund = round(abs($bookingpress_to_time - $bookingpress_from_time) / 60, 2);
			}
			if(!empty($bookingpress_paid_amount) && !empty($bookingpress_time_diff_for_refund)) {
				$bookingpress_partial_refund_rules = $BookingPress->bookingpress_get_settings('bookingpress_partial_refund_rules','payment_setting');
				if(!empty($bookingpress_partial_refund_rules) ) {
					$bookingpress_partial_refund_rules =  maybe_unserialize( $bookingpress_partial_refund_rules );					
					$rules_duration_arr = $rules_duration_amount = array();
					foreach($bookingpress_partial_refund_rules as $key => $value) {
						$rules_duration = intval($value['rules_duration']) * 60;
						$rules_duration_unit = esc_html($value['rules_duration_unit']);
						$rules_amount = intval($value['rules_amount']);
						$rules_amount_unit = esc_html($value['rules_amount_unit']);

						if($rules_amount_unit == 'percentage') {
							$rules_amount = $bookingpress_paid_amount * $rules_amount / 100 ;
						}

						if( $rules_duration_unit == 'd')  {
							$rules_duration = $rules_duration * 24;
						}
						$rules_duration_arr[] = $rules_duration;  
						$rules_duration_amount[$rules_duration] = $rules_amount;
					}				
					$findClosest = $this->bookingpress_find_closest_value($rules_duration_arr,$bookingpress_time_diff_for_refund);
					if(!empty($findClosest) && $findClosest <= $bookingpress_time_diff_for_refund ) {
						if(isset($rules_duration_amount[$findClosest]) && $rules_duration_amount[$findClosest] < $bookingpress_paid_amount){
							$refund_data['refund_amount'] = $bookingpress_paid_amount - $rules_duration_amount[$findClosest];
						} else {
							$refund_data['refund_amount'] = 0;
						}
					} else {	
						$refund_data['refund_amount'] = 0;						
					}
				}
			}		
			return $refund_data;
		}
		
		/**
		 * findClosest value in arr
		 *
		 * @param  mixed $values
		 * @param  mixed $match
		 * @return void
		 */
		function bookingpress_find_closest_value(array $values, $match) {
			$map = [];
			foreach ($values as $v) {
				if($v < $match ) {
					$map[$v] = abs($match - $v);
				}
			}
			$match = 0;
			if(!empty($map)) {
				$match = array_search(min($map), $map);
			}
			return $match;
		}
		
		/**
		 * Function for modify appointment page listing data
		 *
		 * @param  mixed $appointment
		 * @param  mixed $get_appointment
		 * @return void
		 */
		function bookingpress_appointment_add_view_field_func($appointment,$get_appointment) {
			global $BookingPress, $bookingpress_appointment;

			$service_duration        = esc_html($get_appointment['bookingpress_service_duration_val']);
			$service_duration_unit   = esc_html($get_appointment['bookingpress_service_duration_unit']);
			$bookingpress_appointment_date = $get_appointment['bookingpress_appointment_date'];

			$bookingpress_start_datetime = $bookingpress_appointment_date.' '.$get_appointment['bookingpress_appointment_time'];
			$bookingpress_end_datetime = $bookingpress_appointment_date.' '.$get_appointment['bookingpress_appointment_end_time'];
			if($service_duration_unit != 'd') {
				if( !empty( $bookingpress_appointment ) && method_exists( $bookingpress_appointment, 'bookingpress_get_appointment_duration' ) ){
					$service_duration = $bookingpress_appointment->bookingpress_get_appointment_duration($bookingpress_start_datetime, $bookingpress_end_datetime);
				}
				else {
					$bookingpress_start_time = $get_appointment['bookingpress_appointment_time'];
					$bookingpress_tmp_start_time = new DateTime($bookingpress_appointment_date . ' ' .$bookingpress_start_time);
					$bpa_start_datetime = $bookingpress_appointment_date . ' ' .$bookingpress_start_time;
					if( 'h' == $service_duration_unit ){
						$service_duration_unit_label = 'hours';
					} else {
						$service_duration_unit_label = 'minutes';
					}

					$bpa_end_datetime = date( 'Y-m-d H:i:s', strtotime( $bpa_start_datetime . ' +'.$service_duration.' '.$service_duration_unit_label ));
					$bookingpress_tmp_end_time = new DateTime( $bpa_end_datetime );
					
					$booking_date_interval = $bookingpress_tmp_start_time->diff($bookingpress_tmp_end_time);

					$bookingpress_minute = $booking_date_interval->format('%i');
					$bookingpress_hour = $booking_date_interval->format('%h');  
					$bookingpress_days = $booking_date_interval->format('%d');
					$service_duration = '';
					if($bookingpress_minute > 0) {
						$service_duration = $bookingpress_minute.' ' . esc_html__('Mins', 'bookingpress-appointment-booking'); 
					}
					if($bookingpress_hour > 0 ) {
						$service_duration = $bookingpress_hour.' ' . esc_html__('Hours', 'bookingpress-appointment-booking').' '.$service_duration;
					}
					if($bookingpress_days == 1) {
						$service_duration = '24 ' . esc_html__('Hours', 'bookingpress-appointment-booking');
					}		
				}
			} else {
				if( 1 == $service_duration ){
					$service_duration .= ' ' . esc_html__('Day', 'bookingpress-appointment-booking');
				} else {   
					$service_duration .= ' ' . esc_html__('Days', 'bookingpress-appointment-booking');
				}                        
			}

			$appointment['appointment_duration'] = $service_duration;
			$refund_data= $this->bookingpress_allow_to_refund($get_appointment,0,0);

			$appointment['appointment_refund_status'] = $refund_data['allow_refund'];
			$appointment['appointment_partial_refund'] = $refund_data['allow_partial'];

			$bookingpress_service_currecy = $get_appointment['bookingpress_service_currency'];
			$appointment['appointment_currency_symbol'] = $BookingPress->bookingpress_get_currency_symbol($bookingpress_service_currecy);
			
			return $appointment;
		}

		function bookingpress_allow_to_refund($appointment_data,$appointment_id = 0,$from = 0) {
			global $wpdb,$bookingpress_pro_global_options,$BookingPress,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs;

			$payment_amount_flag = 0;
			$payment_status_flag = 0;
			$payment_gateway_flag = 0;			
			$return['allow_refund'] = 0;
			$return['allow_partial'] = 0;

			/* if refund on cancellation option is disable  */
			$bookingpress_refund_on_cancellation = $BookingPress->bookingpress_get_settings('bookingpress_refund_on_cancellation', 'payment_setting');			
			if($from == 1 && ( empty($bookingpress_refund_on_cancellation) || (!empty($bookingpress_refund_on_cancellation) && $bookingpress_refund_on_cancellation != 'true'))) {				
				return $return;
			}

			if(empty($appointment_data) && !empty($appointment_id)) {
				$appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d AND bookingpress_is_cart = %d", $appointment_id,0), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			}
			// return cart appointment 
			if(isset($appointment_data['bookingpress_is_cart']) && $appointment_data['bookingpress_is_cart'] == 1 ) {
				return $return;
			}
						
			$bookingpress_is_not_allow_to_refund_check = apply_filters( 'bookingpress_is_not_allow_to_refund_check', false, $appointment_data);
			if($bookingpress_is_not_allow_to_refund_check){
				return $return;
			}
					
			if(!empty($appointment_data)) {
				$bookingpress_payment_id = !empty($appointment_data['bookingpress_payment_id']) ? intval($appointment_data['bookingpress_payment_id']) : 0;	

				$bookingpress_payment_logs_data= $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = %d", $bookingpress_payment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

				$bookingpress_payment_gateway = !empty($bookingpress_payment_logs_data['bookingpress_payment_gateway']) ? esc_html($bookingpress_payment_logs_data['bookingpress_payment_gateway']) : '';

				$bookingpress_payment_date = !empty($bookingpress_payment_logs_data['bookingpress_payment_date_time']) ? esc_html($bookingpress_payment_logs_data['bookingpress_payment_date_time']) : '';

				 /* check the payment status */

				$bookingpress_refund_on_partial = $BookingPress->bookingpress_get_settings('bookingpress_refund_on_partial', 'payment_setting');
				if(!empty($bookingpress_payment_logs_data['bookingpress_payment_status']) && $bookingpress_payment_logs_data['bookingpress_payment_status'] == 1) {
					$payment_status_flag = 1;
				} elseif(!empty($bookingpress_refund_on_partial) && $bookingpress_refund_on_partial == 'true' && !empty($bookingpress_payment_logs_data['bookingpress_payment_status']) && $bookingpress_payment_logs_data['bookingpress_payment_status'] == 4) {
					$payment_status_flag = 1;
				} elseif($from == 0 && !empty($bookingpress_payment_logs_data['bookingpress_payment_status']) && $bookingpress_payment_logs_data['bookingpress_payment_status'] == 4) {
					$payment_status_flag = 1;
				}

				/* check the payment amount */

				$bookingpress_paid_amount = 0;
				$bookingpress_paid_amount = !empty($bookingpress_payment_logs_data['bookingpress_paid_amount']) ? intval($bookingpress_payment_logs_data['bookingpress_paid_amount']) : 0;
				if($bookingpress_paid_amount > 0) {
					$payment_amount_flag = 1;
				}

				/* check the payment gateway allow */				
				if($payment_status_flag == 1 && $payment_amount_flag == 1) {
					$payment_gateway_data = $bookingpress_pro_global_options->bookingpress_allowed_refund_payment_gateway_list();				
					if(isset($payment_gateway_data[$bookingpress_payment_gateway])) {			
						$payment_g_data = $payment_gateway_data[$bookingpress_payment_gateway];
						$bookingpress_refund_mode = $BookingPress->bookingpress_get_settings('bookingpress_refund_mode', 'payment_setting');
						$return['allow_partial'] = isset($payment_g_data['partial_status']) ? $payment_g_data['partial_status'] : 0;
						if(!empty($payment_g_data['is_refund_support']) && $payment_g_data['is_refund_support'] == 1) {
							$payment_gateway_flag = 1;					
							if(!empty($payment_g_data['allow_days'])) {
								$current_time = current_time('timestamp');
								$current_time = date('Y-m-d',$current_time);
								$bookingpress_payment_date = date('Y-m-d',strtotime($bookingpress_payment_date));
								$date1=date_create($current_time);
								$date2=date_create($bookingpress_payment_date);
								$diff=date_diff($date1,$date2);
								$diff_days = $diff->d;
								if($payment_g_data['allow_days'] < $diff_days)	 {
									$payment_gateway_flag = 0;
								}							
							}
						}	
					}
				}
			}

			/* all status true then return 1 */
			if( $payment_status_flag = 1 && $payment_gateway_flag == 1 && $payment_amount_flag == 1) {
				$return['allow_refund'] = 1; 
			}			

			$return = apply_filters('bookingpress_modify_allow_refund_data',$return,$appointment_data);
			return $return;
		}
		
		/**
		 * Function for modify generated share URL
		 *
		 * @param  mixed $bpa_final_generated_url
		 * @param  mixed $bpa_share_url_form_data
		 * @return void
		 */
		function bookingpress_filter_generated_share_url_externally_func($bpa_final_generated_url, $bpa_share_url_form_data){
			if(!empty($bpa_share_url_form_data)){
				$bpa_selected_extras = !empty($_POST['selected_extras']) ? $_POST['selected_extras'] : array(); //phpcs:ignore
				
				$bpa_selected_staff_id = !empty($bpa_share_url_form_data['selected_staff_id']) ? intval($bpa_share_url_form_data['selected_staff_id']) : 0;
				if(!empty($bpa_selected_staff_id)){
					$bpa_final_generated_url = add_query_arg('sm_id', $bpa_selected_staff_id, $bpa_final_generated_url);
				}

				$bpa_selected_guests = !empty($bpa_share_url_form_data['selected_guests']) ? intval($bpa_share_url_form_data['selected_guests']) : 0;
				if(!empty($bpa_selected_guests)){
					$bpa_final_generated_url = add_query_arg('g_id', $bpa_selected_guests, $bpa_final_generated_url);
				}

				if(!empty($bpa_selected_extras)){
					$selected_extras_details = '';
					foreach($bpa_selected_extras as $extra_key => $extraval){
						if($extraval['bookingpress_is_selected'] == "true"){
							$selected_extras_details .= $extraval['bookingpress_extra_services_id']."|".$extraval['bookingpress_selected_qty']."~";
						}
					}

					if(!empty($selected_extras_details)){
						$bpa_final_generated_url = add_query_arg('se_id', $selected_extras_details, $bpa_final_generated_url);
					}
				}
			}
			return $bpa_final_generated_url;
		}
		
		/**
		 * Function for modify appointment request after validation
		 *
		 * @return void
		 */
		function bookingpress_modify_request_after_validation_func(){
			?>
				if(vm2.$refs['appointment_custom_formdata'] != undefined){
					vm2.$refs['appointment_custom_formdata'].validate((validCustomField) => {
						if(!validCustomField){
							valid = false;
						}
					});
				}
			<?php
		}
		
		/**
		 * Function for add common admin vue methods
		 *
		 * @return void
		 */
		function bookingpress_admin_panel_vue_methods_func(){
			?>
				bookingpress_get_backend_addition_disable_dates(postData){
					const vm = this
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) ).then( function( response2 ) {
						if(false == response2.data.prevent_next_month_check && response2.data.counter < 3 ){ /** Currently data will be checked for next 3 months */
							let disableDates = response2.data.days_off_disabled_dates;
							postData.days_off_disabled_dates = disableDates;
							let disableDates_arr = disableDates.split(',');
							let disableDates_formatted = [];
							disableDates_arr.forEach(function( date ){
								let formatted_date = vm.get_formatted_date( date );
								disableDates_formatted.push( formatted_date );
							});
							<?php do_action('bookingpress_after_addition_disable_dates_response'); ?>
							vm.pickerOptions.disabledDate = function(Time){
								let currentDate = new Date( Time );
								
								currentDate = vm.get_formatted_date( currentDate );
								
								var date = new Date();
								
								date.setDate(date.getDate()-1);
								
								var disable_past_date = Time.getTime() < date.getTime();

								if( disableDates_formatted.indexOf( currentDate ) > -1 ){
									return true;
								} else {
									return disable_past_date;
								}
							};

							postData.next_month = response2.data.next_month;
							postData.counter++;
							vm.bookingpress_get_backend_addition_disable_dates( postData );
						}
					});
				},
				BPACustomerFileUpload( response, file, fileList ){
					const vm = this;
					let ref = response.reference;
					if( response.error == 1 ){
						vm.$refs[ ref ][0].$options.parent.validateMessage = response.msg;
						vm.$refs[ ref ][0].$options.parent.validateState = "error";
						vm.$refs[ ref ][0].clearFiles();
					} else {
						vm.$refs[ ref ][0].$options.parent.validateMessage = "";
						vm.$refs[ ref ][0].$options.parent.validateState = "";
						let upload_file_name = response.upload_file_name;
						let upload_url = response.upload_url;
						vm.appointment_formdata[ response.file_ref ] = upload_url;
						vm.appointment_formdata.bookingpress_appointment_meta_fields_value[ response.file_ref ] = upload_url;
					}
				},
				BPACustomerFileUploadError(err, file, fileList){
					/** Need to handle error but currently no error is reaching to this function */
				},
				BPACustomerFileUploadRemove( file, fileList ){
					const vm = this;
					let response = file.response;
					vm.appointment_formdata[ response.file_ref ] = "";
					vm.appointment_formdata.bookingpress_appointment_meta_fields_value[ response.file_ref ] = "";

					let postData = {
						action:"bpa_remove_form_file",
						_wpnonce: "<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>",
						uploaded_file_name: response.upload_file_name
					};
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function( response ){
					}).catch( function( error ){
					});
				},
			<?php
		}
		
		/**
		 * Function for additional call for disable dates
		 *
		 * @return void
		 */
		function bookingpress_additional_disable_dates_func(){
			?>
				const vm2 = this;
				var bookingpress_appointment_date = vm2.appointment_formdata.appointment_booked_date;
				var bookingpress_moment_formatted_date = moment(bookingpress_appointment_date);
				bookingpress_appointment_date = bookingpress_moment_formatted_date.format('YYYY-MM-DD');
				if( false == response.data.prevent_next_month_check ){
					let postDataAction = "bookingpress_get_whole_day_appointments";
					if( true == response.data.check_for_multiple_days_event ){
						postDataAction = "bookingpress_get_whole_day_appointments_multiple_days";
					}

					var postData = { action: postDataAction, appointment_data_obj:bookingpress_appointment_form_data, days_off_disabled_dates: disableDates, service_id: bookingpress_appointment_form_data.appointment_selected_service, max_available_year: response.data.max_available_year, max_available_month:response.data.max_available_month,  selected_service:bookingpress_appointment_form_data.appointment_selected_service, selected_date:bookingpress_appointment_date, service_id:bookingpress_appointment_form_data.appointment_selected_service,_wpnonce:bookingpress_appointment_form_data._wpnonce, next_month: response.data.next_month, "counter": 1 };

					postData.appointment_data_obj = JSON.stringify( postData.appointment_data_obj );

					vm2.bookingpress_get_backend_addition_disable_dates(postData);
				}
			<?php
		}
		
		/**
		 * Code execute when backend service change
		 *
		 * @return void
		 */
		function bookingpress_change_backend_service_func(){
			?>
				var is_timeslot_disp = 1;
				vm.is_timeslot_display = '1';
				vm.appointment_formdata.appointment_booked_time = '';

				vm.appointment_services_list.forEach(function(currentValue, index, arr){
					if(currentValue.category_services.length > 0){
						currentValue.category_services.forEach(function(currentValue2, index2, arr2){
							if(currentValue2.service_id == vm.appointment_formdata.appointment_selected_service && currentValue2.service_duration_unit == 'd'){
								is_timeslot_disp = 0;
							}
						});
					}
				});

				if(is_timeslot_disp == 0){
					vm.is_timeslot_display = '0';
					vm.appointment_formdata.appointment_booked_time = '00:00:00';
				}
			<?php
		}
		
		/**
		 * Function for execute code after update appointment from backend
		 *
		 * @param  mixed $bookingpress_appointment_id
		 * @return void
		 */
		function bookingpress_after_update_appointment_func($bookingpress_appointment_id){
			global $wpdb, $BookingPress, $BookingPressPro, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $bookingpress_email_notifications, $BookingPress;
			
			$bpa_chk_rescheduled_appointment_id = get_option('bookingpress_rescheduled_appointment_'.$bookingpress_appointment_id ); //check rescheduled email sent or not

			if( isset($bpa_chk_rescheduled_appointment_id) && $bpa_chk_rescheduled_appointment_id === '0' ){

				$appointment_log_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
				//$BookingPress->bookingpress_write_response( $bookingpress_appointment_id );
				//$BookingPress->bookingpress_write_response( json_encode($appointment_log_data) );

				if ( ! empty( $appointment_log_data ) ) {
					$bookingpress_customer_data = $BookingPress->get_customer_details( $appointment_log_data['bookingpress_customer_id'] );
					$bookingpress_wpuser_id     = $bookingpress_customer_data['bookingpress_wpuser_id'];
					if (  ! empty( $bookingpress_customer_data ) ) {
						$bookingpress_customer_email = $bookingpress_customer_data['bookingpress_user_email'];
						// Send customer email notification
						$bookingpress_email_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', 'Appointment Rescheduled', $bookingpress_appointment_id, $bookingpress_customer_email );

						//$BookingPress->bookingpress_write_response( json_encode($bookingpress_email_res) );
						$is_email_sent          = $bookingpress_email_res['is_mail_sent'];

						//$BookingPress->bookingpress_write_response( $is_email_sent 	);
						// Send admin email notification

						$bookingpress_admin_emails = $BookingPress->bookingpress_get_settings( 'admin_email', 'notification_setting' );
						$bookingpress_admin_emails = apply_filters('bookingpress_filter_admin_email_data', $bookingpress_admin_emails, $bookingpress_appointment_id,'Appointment Rescheduled');
						if ( ! empty( $bookingpress_admin_emails ) ) {
							$bookingpress_cc_emails = array();
							$bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, 'Appointment Rescheduled');

							$bookingpress_admin_emails = explode( ',', $bookingpress_admin_emails );
							foreach ( $bookingpress_admin_emails as $admin_email_key => $admin_email_val ) {
								$bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', 'Appointment Rescheduled', $bookingpress_appointment_id, $admin_email_val, $bookingpress_cc_emails );
							}
						}
					}
					update_option('bookingpress_rescheduled_appointment_'.$bookingpress_appointment_id, 1); //rescheduled email sent update the option
				} 
			}

			$bookingpress_appointment_updated_data = !empty($_POST['appointment_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data']) : array(); // phpcs:ignore
			if(!empty($bookingpress_appointment_updated_data)){
				$bookingpress_appointment_payment_logs= $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_booking_ref = %d", $bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
				if(!empty($bookingpress_appointment_payment_logs)){
					$bookingpress_applied_coupon_details = !empty($bookingpress_appointment_updated_data['applied_coupon_details']) ? $bookingpress_appointment_updated_data['applied_coupon_details'] : array();
					if(!empty($bookingpress_applied_coupon_details)){
						$bookingpress_applied_coupon_details = array(
							'coupon_status' => "success",
							'msg' => 'Coupon applied successfully',
							'coupon_data' => $bookingpress_applied_coupon_details,
						);
					}
					$bookingpress_update_payment_logs_data = array(
						'bookingpress_coupon_details' => wp_json_encode($bookingpress_applied_coupon_details),
						'bookingpress_coupon_discount_amount' => $bookingpress_appointment_updated_data['coupon_discounted_amount'],
					);
					if($bookingpress_appointment_payment_logs['bookingpress_payment_gateway'] == "on-site" && $bookingpress_appointment_payment_logs['bookingpress_is_cart'] != 1 && isset($bookingpress_appointment_updated_data['total_amount'])){
						$bookingpress_update_payment_logs_data['bookingpress_paid_amount'] = $bookingpress_appointment_updated_data['total_amount'];

						$bookingpress_update_appointment_logs_data = array(
							'bookingpress_paid_amount' => $bookingpress_appointment_updated_data['total_amount'],
						);
						$wpdb->update($tbl_bookingpress_appointment_bookings, $bookingpress_update_appointment_logs_data, array( 'bookingpress_appointment_booking_id' => $bookingpress_appointment_id ));
					}					
					$bookingpress_update_payment_logs_data = apply_filters('bookingpress_admin_update_payment_after_edit_appointment',$bookingpress_update_payment_logs_data,$bookingpress_appointment_updated_data);													

					$affected_rows = $wpdb->update($tbl_bookingpress_payment_logs, $bookingpress_update_payment_logs_data, array('bookingpress_payment_log_id' => $bookingpress_appointment_payment_logs['bookingpress_payment_log_id']));
				}
			}
		}
		
		/**
		 * Execute code after get backend disable dates
		 *
		 * @return void
		 */
		function bookingpress_after_get_backend_disable_dates_func(){
			?>
				vm.bookingpress_admin_get_final_step_amount();
				if(vm.appointment_formdata.applied_coupon_code != ''){
					vm.bookingpress_apply_coupon_code();
				}
			<?php
		}
		
		/**
		 * Function for modify backend edit appointment data
		 *
		 * @param  mixed $edit_appointment_data
		 * @return void
		 */
		function bookingpress_modify_edit_appointment_data_func($edit_appointment_data){
			global $wpdb, $BookingPress, $bookingpress_pro_services, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $bookingpress_bring_anyone_with_you;

			if(!empty($edit_appointment_data)){
				$bookingpress_appointment_id = intval($edit_appointment_data['bookingpress_appointment_booking_id']);
				$bookingpress_payment_status = 1;

				if(!empty($bookingpress_appointment_id)){
					$bookingpress_payment_log_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_payment_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name.

					$bookingpress_payment_log_id = !empty($bookingpress_payment_log_details['bookingpress_payment_id']) ? intval($bookingpress_payment_log_details['bookingpress_payment_id']) : 0;

					if(!empty($bookingpress_payment_log_id)){
						$bookingpress_payment_log_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = %d", $bookingpress_payment_log_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name.

						$bookingpress_payment_status = !empty($bookingpress_payment_log_data['bookingpress_payment_status']) ? intval($bookingpress_payment_log_data['bookingpress_payment_status']) : 0;
					}
				}


				$bookingpress_selected_service_id = $edit_appointment_data['bookingpress_service_id'];
				$bookingpress_service_max_capacity = $bookingpress_pro_services->bookingpress_get_service_max_capacity($bookingpress_selected_service_id);

				$bookingpress_service_min_capacity = 1;
				if( $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation() == 1 ){

					$bookingpress_selected_service_id = $edit_appointment_data['bookingpress_service_id'];
					$bookingpress_service_min_capacity = $bookingpress_pro_services->bookingpress_get_service_min_capacity($bookingpress_selected_service_id);	
				}
				
				$bookingpress_selected_service_id = $edit_appointment_data['bookingpress_service_id'];
				$bookingpress_service_max_capacity = $bookingpress_pro_services->bookingpress_get_service_max_capacity($bookingpress_selected_service_id);

				$edit_appointment_data['bring_anyone_max_capacity'] = $bookingpress_service_max_capacity;
				$edit_appointment_data['bring_anyone_min_capacity'] = $bookingpress_service_min_capacity;
				$edit_appointment_data['bookingpress_payment_status'] = $bookingpress_payment_status;
			}

			return $edit_appointment_data;
		}
		
		/**
		 * Function for reset add appointment modal fields
		 *
		 * @return void
		 */
		function bookingpress_add_appointment_model_reset_callback(){
			?>
				let appointment_meta_fields = vm2.appointment_formdata.bookingpress_appointment_meta_fields_value;				
				for( let k in appointment_meta_fields ){
					let currentVal = appointment_meta_fields[k];
					if( "boolean" == typeof currentVal ){
						vm2.appointment_formdata.bookingpress_appointment_meta_fields_value[k] = false;
					} else if( "string" == typeof currentVal ){
						vm2.appointment_formdata.bookingpress_appointment_meta_fields_value[k] = "";
					} else if( "object" == typeof currentVal ){
						vm2.appointment_formdata.bookingpress_appointment_meta_fields_value[k] = [];
					}
				}

				vm2.appointment_formdata.complete_payment_url_selection = 'do_nothing';
				vm2.appointment_formdata.complete_payment_url_selected_method = [];
				
				let appointment_form_fields  = vm2.bookingpress_form_fields;
				for( let m in appointment_form_fields ){
					let currentval = appointment_form_fields[m];					
					if(currentval.bookingpress_field_type == 'file') {
						vm2.bookingpress_form_fields[m]['bpa_file_list'] = [];
					}
				}				
				vm2.appointment_formdata.bookingpress_currency_name	= vm2.appointment_formdata.bookingpress_currency_name_org; 			
				vm2.appointment_formdata.total_amount = 0;
				vm2.appointment_formdata.total_amount_with_currency = vm2.bookingpress_price_with_currency_symbol( 0 );	
				vm2.appointment_formdata.subtotal = 0;
				vm2.appointment_formdata.subtotal_with_currency = vm2.bookingpress_price_with_currency_symbol( 0 );	

			<?php
		}
				
		/**
		 * Function for appointment reset filter
		 *
		 * @return void
		 */
		function bookingpress_appointment_reset_filter_func(){
			?>
			vm.search_staff_member_name = '';
			vm.appointment_formdata.selected_staffmember = '';
			<?php
		}
		
		/**
		 * Function to retrieve selected customer form fields data
		 *
		 * @return void
		 */
		function bookingpress_get_customer_form_field_values_func(){
			global $wpdb, $BookingPress, $BookingPressPro, $tbl_bookingpress_customers, $tbl_bookingpress_customers_meta, $tbl_bookingpress_form_fields;

			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_appointment_customer_details', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			$response['variant'] = 'success';
			$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
			$response['msg'] = esc_html__('Data retrieved successfully', 'bookingpress-appointment-booking');

			$bookingpress_customer_id = !empty( $_REQUEST['customer_id'] ) ? intval( $_REQUEST['customer_id'] ) : 0;
			$bookingpress_customer_form_fields_data = array();
			$response['customer_form_fields'] = array();
			if( !empty( $bookingpress_customer_id ) ){
				$customer_form_fields = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_type,bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_is_customer_field = %d", 1) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm 
				if( !empty( $customer_form_fields ) ){
					foreach( $customer_form_fields as $field_metakey_dat ){
						$field_metakey = $field_metakey_dat->bookingpress_field_meta_key;
						$bookingpress_field_type = $field_metakey_dat->bookingpress_field_type;
						$bpa_customer_field_value = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_customersmeta_value FROM {$tbl_bookingpress_customers_meta} WHERE bookingpress_customersmeta_key = %s AND bookingpress_customer_id = %d", $field_metakey, $bookingpress_customer_id )); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers_meta is table name defined globally. False Positive alarm 
						$field_metavalue = $bpa_customer_field_value->bookingpress_customersmeta_value;
						$is_json = json_decode( $field_metavalue, true ) == NULL ? false : true;
						if( $is_json ){
							$field_metavalue = json_decode( $field_metavalue, true );
						}
						if($bookingpress_field_type == 'checkbox'){
							if(is_null($field_metavalue)){
								$field_metavalue = array();
							}
						}
						$bookingpress_customer_form_fields_data[ $field_metakey ] = $field_metavalue;
					}
				}
			}
			$response['customer_form_fields'] = $bookingpress_customer_form_fields_data;
			wp_send_json( $response );
			die;
		}
		
		/**
		 * Function for get customer details
		 *
		 * @return void
		 */
		function wp_ajax_bookingpress_get_customer_details_func(){
			global $wpdb, $BookingPress, $BookingPressPro;
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_appointment_customer_details', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			$response['variant'] = 'success';
			$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
			$response['msg'] = esc_html__('Data retrieved successfully', 'bookingpress-appointment-booking');
			$response['appointment_customers_details'] = array();
			$bookingpress_customer_id = ! empty( $_REQUEST['customer_id'] ) ? intval( $_REQUEST['customer_id'] ) : '';
			$bookingpress_appointment_customers_details = array();
			if(!empty($bookingpress_customer_id)) {                  
                $response['variant'] = 'success';
                $response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg'] = esc_html__('Data retrieved successfully', 'bookingpress-appointment-booking');
                $response['appointment_customers_details'] = array();
                $bookingpress_appointment_customers_details = $BookingPress->bookingpress_get_appointment_customer_list('',$bookingpress_customer_id);			
                $response['appointment_customers_details'] = $bookingpress_appointment_customers_details;
            }    
			$response['appointment_customers_details'] = $bookingpress_appointment_customers_details;
			echo wp_json_encode($response);
			exit;
		}
		
		/**
		 * Function for execute code after add appointment from backend
		 *
		 * @param  mixed $appointment_id
		 * @param  mixed $bookingpress_appointment_data
		 * @param  mixed $entry_id
		 * @return void
		 */
		function bookingpress_after_add_appointment_from_backend_func($appointment_id, $bookingpress_appointment_data, $entry_id){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_meta;
			$wpdb->update( $tbl_bookingpress_appointment_meta, array('bookingpress_appointment_id' => $appointment_id), array('bookingpress_entry_id' => $entry_id) );
		}
		
		/**
		 * Function for execute code after insert data into entries
		 *
		 * @param  mixed $entry_id
		 * @param  mixed $bookingpress_appointment_data
		 * @return void
		 */
		function bookingpress_after_insert_entry_data_from_backend_func($entry_id, $bookingpress_appointment_data){
			global $wpdb, $tbl_bookingpress_appointment_meta;
			$bookingpress_appointment_form_fields_data = array(
				'form_fields' => !empty($bookingpress_appointment_data['bookingpress_appointment_meta_fields_value']) ? $bookingpress_appointment_data['bookingpress_appointment_meta_fields_value'] : array(),
				'bookingpress_front_field_data' => !empty($bookingpress_appointment_data['bookingpress_appointment_meta_fields_value']) ? $bookingpress_appointment_data['bookingpress_appointment_meta_fields_value'] : array(),
			);

			$bookingpress_db_fields = array(
				'bookingpress_entry_id' => $entry_id,
				'bookingpress_appointment_id' => 0,
				'bookingpress_appointment_meta_value' => wp_json_encode($bookingpress_appointment_form_fields_data),
				'bookingpress_appointment_meta_key' => 'appointment_form_fields_data',
			);

			$wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_db_fields);
		}
		
		/**
		 * Function for get appointment meta values
		 *
		 * @return void
		 */
		function bookingpress_get_appointment_meta_values_func(){
			global $wpdb, $BookingPress, $BookingPressPro, $tbl_bookingpress_form_fields;

			$response = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'get_appointment_meta_value', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_appointment_booking_id = !empty($_POST['bookingpress_appointment_id']) ? intval($_POST['bookingpress_appointment_id']) : 0; // phpcs:ignore

			if(!empty($bookingpress_appointment_booking_id)){
				$bookingpress_form_field_value = $this->bookingpress_get_appointment_form_field_data($bookingpress_appointment_booking_id);
				$bookingpress_form_field_value = apply_filters('bookingpress_get_appointment_meta_value_filter',$bookingpress_form_field_value);
				$response['variant']            = 'success';
				$response['title']              = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']                = esc_html__( 'Custom fields retrieved successfully.', 'bookingpress-appointment-booking' );
				$response['custom_fields_values'] = $bookingpress_form_field_value;
			}

			echo wp_json_encode($response);
			exit;
		}
		
		/**
		 * Function for get edit appointment details
		 *
		 * @return void
		 */
		function bookingpress_edit_appointment_details_func(){
			?>
				const vm = this
				var bookingpress_appointment_booking_id = response.data.bookingpress_appointment_booking_id;

				var is_timeslot_disp = 1;
				vm.is_timeslot_display = '1';
				for( let index in vm.appointment_services_list ){
					let currentValue = vm.appointment_services_list[ index ];
					if(currentValue.category_services.length > 0){
						for( let index2 in currentValue.category_services ){
							let currentValue2 = currentValue.category_services[ index2 ];
							if( currentValue2.service_id == vm.appointment_formdata.appointment_selected_service && currentValue2.service_duration_unit == 'd'){
								is_timeslot_disp = 0;
							}
						}
					}
				}
				if(is_timeslot_disp == 0){
					vm.is_timeslot_display = '0';
					vm.appointment_formdata.appointment_booked_time = '00:00:00';
				}
				
				//Set edited extras value
				if(response.data.bookingpress_extra_service_details != "" && response.data.bookingpress_extra_service_details != null){
					vm2.appointment_formdata.selected_extra_services_ids = [];
					var bookingpress_extra_details = JSON.parse(response.data.bookingpress_extra_service_details);
					bookingpress_extra_details.forEach(function(currentValue, index, arr){
						vm2.appointment_formdata.selected_extra_services_ids.push(currentValue.bookingpress_extra_service_details.bookingpress_extra_services_id);
						vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service].forEach(function(currentValue2, index2, arr2){
							if(currentValue2.bookingpress_extra_services_id == currentValue.bookingpress_extra_service_details.bookingpress_extra_services_id){
								vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service][index2].bookingpress_is_selected = true;
								vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service][index2].bookingpress_selected_qty = parseInt(currentValue.bookingpress_selected_qty);
							}
						});
					});
				}
				
				//Set bring anyone with value
				var bring_anyone_max_cap = response.data.bring_anyone_max_capacity;
				vm2.appointment_formdata.bookingpress_bring_anyone_max_capacity = parseInt(bring_anyone_max_cap);
				if( vm2.is_bring_anyone_with_you_enable == 1 ){
					var bring_anyone_min_cap = response.data.bring_anyone_min_capacity;
					vm2.appointment_formdata.bookingpress_bring_anyone_min_capacity = parseInt(bring_anyone_min_cap);
				}

				vm2.appointment_formdata.selected_bring_members = parseInt(response.data.bookingpress_selected_extra_members);
				if(typeof response.data.bookingpress_staff_member_id != 'undefined' && response.data.bookingpress_staff_member_id != 0 && response.data.bookingpress_staff_member_id != '') {					
					let selected_staffmember = response.data.bookingpress_staff_member_id;
					if( "" != selected_staffmember ){						
						let selected_service = response.data.bookingpress_service_id;
						let selected_service_staffmember = vm.bookingpress_loaded_staff[ selected_service ];
						let selected_staff_capacity = 1;
						let selected_staff_min_capacity = 1;
						selected_service_staffmember.forEach(function( elm ){
							if( selected_staffmember == elm.bookingpress_staffmember_id ){
								selected_staff_capacity = elm.bookingpress_service_capacity;
								
								if( vm2.is_bring_anyone_with_you_enable == 1 ){
									
									selected_staff_min_capacity = elm.bookingpress_service_min_capacity;
									
								}
								return false;
							}
						});
						vm2.appointment_formdata.bookingpress_bring_anyone_max_capacity = parseInt(selected_staff_capacity);
						if( vm2.is_bring_anyone_with_you_enable == 1 ){
							vm2.appointment_formdata.bookingpress_bring_anyone_min_capacity = parseInt(selected_staff_min_capacity);
						}
					}
				}

				//Set Selected Staff Member
				if(response.data.bookingpress_staff_member_id == 0) {
					vm2.appointment_formdata.selected_staffmember = '';
				} else{ 
					vm2.appointment_formdata.selected_staffmember = response.data.bookingpress_staff_member_id;
				}

				//Set payment status
				vm2.bookingpress_payment_status = response.data.bookingpress_payment_status
				
				var bookingpress_order_id = response.data.bookingpress_order_id;
				var postData = { action:'bookingpress_get_appointment_meta_values', bookingpress_appointment_id: bookingpress_appointment_booking_id, bookingpress_order_id: bookingpress_order_id, _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (result) {
					if(result.data.custom_fields_values != ""){
						vm2.appointment_formdata.bookingpress_appointment_meta_fields_value = [];
						vm2.appointment_formdata.bookingpress_appointment_meta_fields_value = result.data.custom_fields_values;						
						vm2.bookingpress_form_fields.forEach( (element, index) => {
							let appointment_file_field_list = [];
							if( "file" == element.bookingpress_field_type ){
								let meta_key = element.bookingpress_field_meta_key;
								let file_upload_url = vm2.appointment_formdata.bookingpress_appointment_meta_fields_value[ meta_key ];
								let file_data = file_upload_url.split('/');
								let file_name = file_data[ file_data.length - 1 ];
								let file_obj = {
									name: file_name,
									url: file_upload_url,
									response:{
										file_ref: meta_key
									}
								};								
								appointment_file_field_list.push( file_obj );
								if(file_upload_url != '') {
									vm2.bookingpress_form_fields[index].bpa_file_list = appointment_file_field_list;
								} else {
									vm2.bookingpress_form_fields[index].bpa_file_list = [];
								}
							}
						});
					}
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				});

				<?php 
					do_action('bookingpress_reset_tax_for_admin_edit_appointment');
				?>				
				
				vm2.appointment_formdata.bookingpress_currency_name	= response.data.bookingpress_service_currency;
				if(response.data.bookingpress_coupon_details != ""){
					var bookingpress_applied_coupon_details = JSON.parse(response.data.bookingpress_coupon_details);
					
					if(bookingpress_applied_coupon_details != '' && null != bookingpress_applied_coupon_details ){
						bookingpress_coupon_code = bookingpress_applied_coupon_details.bookingpress_coupon_code;
						if(bookingpress_coupon_code == undefined){
							bookingpress_coupon_code = bookingpress_applied_coupon_details.coupon_data.bookingpress_coupon_code;
						}
						setTimeout(function(){
							vm2.appointment_formdata.applied_coupon_code = bookingpress_coupon_code;
							vm2.bookingpress_apply_coupon_code();
						}, 2000);
					}
				}
				vm2.bookingpress_admin_get_final_step_amount();
				
			<?php
		}
		
		/**
		 * Function for modify payment log details
		 *
		 * @param  mixed $payment_log_data
		 * @param  mixed $entry_data
		 * @return void
		 */
		function bookingpress_modify_payment_log_fields_func($payment_log_data, $entry_data){
			if(!empty($payment_log_data) && !empty($entry_data)){
				$payment_log_data['bookingpress_coupon_details'] = $entry_data['bookingpress_coupon_details'];
				$payment_log_data['bookingpress_coupon_discount_amount'] = $entry_data['bookingpress_coupon_discount_amount'];
				$payment_log_data['bookingpress_tax_percentage'] = $entry_data['bookingpress_tax_percentage'];
				$payment_log_data['bookingpress_tax_amount'] = $entry_data['bookingpress_tax_amount'];
				$payment_log_data['bookingpress_price_display_setting'] = $entry_data['bookingpress_price_display_setting'];
				$payment_log_data['bookingpress_display_tax_order_summary'] = $entry_data['bookingpress_display_tax_order_summary'];
				$payment_log_data['bookingpress_included_tax_label'] = $entry_data['bookingpress_included_tax_label'];
				$payment_log_data['bookingpress_staff_member_id'] = $entry_data['bookingpress_staff_member_id'];
				$payment_log_data['bookingpress_staff_member_price'] = $entry_data['bookingpress_staff_member_price'];
				$payment_log_data['bookingpress_staff_first_name'] = $entry_data['bookingpress_staff_first_name'];
				$payment_log_data['bookingpress_staff_last_name'] = $entry_data['bookingpress_staff_last_name'];
				$payment_log_data['bookingpress_staff_email_address'] = $entry_data['bookingpress_staff_email_address'];
				$payment_log_data['bookingpress_staff_member_details'] = $entry_data['bookingpress_staff_member_details'];
				$payment_log_data['bookingpress_paid_amount'] = $entry_data['bookingpress_paid_amount'];
				$payment_log_data['bookingpress_due_amount'] = $entry_data['bookingpress_due_amount'];
				$payment_log_data['bookingpress_total_amount'] = $entry_data['bookingpress_total_amount'];
				$payment_log_data['bookingpress_mark_as_paid'] = $entry_data['bookingpress_mark_as_paid'];
				$payment_log_data['bookingpress_complete_payment_url_selection'] = $entry_data['bookingpress_complete_payment_url_selection'];
				$payment_log_data['bookingpress_complete_payment_url_selection_method'] = $entry_data['bookingpress_complete_payment_url_selection_method'];
				$payment_log_data['bookingpress_complete_payment_token'] = $entry_data['bookingpress_complete_payment_token'];

				if($entry_data['bookingpress_mark_as_paid'] == 0){
					$payment_log_data['bookingpress_payment_status'] = 2;
				}
			}
			return $payment_log_data;
		}
		
		/**
		 * Function for modify appointment booking fields
		 *
		 * @param  mixed $appointment_booking_field
		 * @param  mixed $entry_data
		 * @param  mixed $bookingpress_appointment_data
		 * @return void
		 */
		function bookingpress_modify_appointment_booking_fields_func($appointment_booking_field, $entry_data, $bookingpress_appointment_data){
			global $wpdb, $tbl_bookingpress_appointment_meta, $BookingPress, $BookingPressPro, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_reschedule_history, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services;
			if(!empty($appointment_booking_field) && !empty($entry_data) ){
				$bookingpress_is_reschedule = 0;
				$bookingpress_appointment_id = !empty( $entry_data['bookingpress_appointment_booking_id'] ) ? $entry_data['bookingpress_appointment_booking_id'] : 0;
				$bookingpress_appointment_old_date = date('Y-m-d', strtotime($entry_data['bookingpress_appointment_date']));
				$bookingpress_appointment_old_start_time = date('H:i:s', strtotime($entry_data['bookingpress_appointment_time']));
				$bookingpress_appointment_old_end_time = date('H:i:s', strtotime($entry_data['bookingpress_appointment_end_time']));
				$bookingpress_appointment_new_date = date('Y-m-d',strtotime($entry_data['bookingpress_appointment_date']));
				$bookingpress_appointment_new_start_time = date('H:i:s',strtotime($entry_data['bookingpress_appointment_time']));
				$bookingpress_appointment_new_end_time = date('H:i:s', strtotime($entry_data['bookingpress_appointment_end_time']));


				if(!empty($bookingpress_appointment_id)){
					//Get bookingpress appointment details
					$bookingpress_existing_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name.

					if(!empty($bookingpress_existing_appointment_data)){
						$bookingpress_appointment_old_date = date('Y-m-d', strtotime($bookingpress_existing_appointment_data['bookingpress_appointment_date']));
						$bookingpress_appointment_old_start_time = date('H:i:s', strtotime($bookingpress_existing_appointment_data['bookingpress_appointment_time']));
						$bookingpress_appointment_old_end_time = date('H:i:s', strtotime($bookingpress_existing_appointment_data['bookingpress_appointment_end_time']));
					}

					$bookingpress_booking_start_timestamp = strtotime( $bookingpress_existing_appointment_data['bookingpress_appointment_date'] . ' ' . $bookingpress_existing_appointment_data['bookingpress_appointment_time'] );
					$is_past_time = current_time('timestamp') > $bookingpress_booking_start_timestamp;
					if($is_past_time==1) {
						do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Backend appointment Book for past time', 'bookingpress_admin_add_update_appointment', $is_past_time, $bookingpress_other_debug_log_id );
						$response['variant'] = 'error';
						$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
						$response['msg']     = esc_html__('Sorry, Appointment can not be rescheduled as the original time has been passed.', 'bookingpress-appointment-booking');
						echo wp_json_encode($response);
						exit();
					}
				}

				
				if(($bookingpress_appointment_new_date != $bookingpress_appointment_old_date) || ($bookingpress_appointment_new_start_time != $bookingpress_appointment_old_start_time) || ($bookingpress_appointment_new_end_time != $bookingpress_appointment_old_end_time)){
					$bookingpress_is_reschedule = 1;
				}
				if( current_time('timestamp') > strtotime($bookingpress_appointment_new_date . ' ' . $bookingpress_appointment_new_start_time) ) {
					$response['variant'] 	= 'error';
					$response['title']   	= esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']    	= esc_html__( 'Sorry, Appointment can not be rescheduled as the selected time has been passed.', 'bookingpress-appointment-booking' );
					$reschedule_appointment_data = array(
						'resch_appointment_id' => $reschedule_id,
						'current_logged_in_user' => get_current_user_id(),
						'is_past_time' => $is_past_time
					);
					do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Reschedule selected time is already passed', 'bookingpress_mybookings', $reschedule_appointment_data, $bookingpress_other_debug_log_id );
					$response['redirect_url'] = '';
					echo wp_json_encode( $response );
					die;
				}

				$appointment_booking_field['bookingpress_coupon_details'] = $entry_data['bookingpress_coupon_details'];
				$appointment_booking_field['bookingpress_coupon_discount_amount'] = $entry_data['bookingpress_coupon_discount_amount'];
				$appointment_booking_field['bookingpress_tax_percentage'] = $entry_data['bookingpress_tax_percentage'];
				$appointment_booking_field['bookingpress_tax_amount'] = $entry_data['bookingpress_tax_amount'];
				$appointment_booking_field['bookingpress_price_display_setting'] = $entry_data['bookingpress_price_display_setting'];
				$appointment_booking_field['bookingpress_display_tax_order_summary'] = $entry_data['bookingpress_display_tax_order_summary'];
				$appointment_booking_field['bookingpress_included_tax_label'] = $entry_data['bookingpress_included_tax_label'];
				$appointment_booking_field['bookingpress_selected_extra_members'] = $entry_data['bookingpress_selected_extra_members'];
				$appointment_booking_field['bookingpress_extra_service_details'] = $entry_data['bookingpress_extra_service_details'];
				$appointment_booking_field['bookingpress_staff_member_id'] = $entry_data['bookingpress_staff_member_id'];
				$appointment_booking_field['bookingpress_staff_member_price'] = $entry_data['bookingpress_staff_member_price'];
				$appointment_booking_field['bookingpress_staff_first_name'] = $entry_data['bookingpress_staff_first_name'];
				$appointment_booking_field['bookingpress_staff_last_name'] = $entry_data['bookingpress_staff_last_name'];
				$appointment_booking_field['bookingpress_staff_email_address'] = $entry_data['bookingpress_staff_email_address'];
				$appointment_booking_field['bookingpress_staff_member_details'] = $entry_data['bookingpress_staff_member_details'];
				$appointment_booking_field['bookingpress_paid_amount'] = $entry_data['bookingpress_paid_amount'];
				$appointment_booking_field['bookingpress_due_amount'] = $entry_data['bookingpress_due_amount'];
				$appointment_booking_field['bookingpress_total_amount'] = $entry_data['bookingpress_total_amount'];
				$appointment_booking_field['bookingpress_mark_as_paid'] = $entry_data['bookingpress_mark_as_paid'];
				$appointment_booking_field['bookingpress_complete_payment_url_selection'] = $entry_data['bookingpress_complete_payment_url_selection'];
				$appointment_booking_field['bookingpress_complete_payment_url_selection_method'] = $entry_data['bookingpress_complete_payment_url_selection_method'];
				$appointment_booking_field['bookingpress_complete_payment_token'] = $entry_data['bookingpress_complete_payment_token'];

				if($bookingpress_is_reschedule){
					$appointment_booking_field['bookingpress_is_reschedule'] = 1;

					//If appointment reschedule then insert rescheduled appointment history data
					$bookingpress_reschedule_appointment_history_data = array(
						'bookingpress_appointment_id' => $bookingpress_appointment_id,
						'bookingpress_appointment_original_date' => $bookingpress_appointment_old_date,
						'bookingpress_appointment_original_start_time' => $bookingpress_appointment_old_start_time,
						'bookingpress_appointment_original_end_time' => $bookingpress_appointment_old_end_time,
						'bookingpress_appointment_new_date' => $bookingpress_appointment_new_date,
						'bookingpress_appointment_new_start_time' => $bookingpress_appointment_new_start_time,
						'bookingpress_appointment_new_end_time' => $bookingpress_appointment_new_end_time,
						'bookingpress_reschedule_from' => 1,
						'bookingpress_wp_user_id' => get_current_user_id(),
						'bookingpress_customer_id' => $entry_data['bookingpress_customer_id'],
					);

					$wpdb->insert($tbl_bookingpress_reschedule_history, $bookingpress_reschedule_appointment_history_data);

					do_action( 'bookingpress_after_rescheduled_appointment', $bookingpress_appointment_id );
				}

				if(!empty($bookingpress_appointment_data)){
					$bookingpress_appointment_booking_id = $bookingpress_appointment_data['appointment_update_id'];
					$bookingpress_extra_services_details = array();
					if(!empty($bookingpress_appointment_data['selected_extra_services_ids'])){
						foreach($bookingpress_appointment_data['selected_extra_services_ids'] as $k => $v){
							$bookingpress_extra_services_details[] = array(
								"bookingpress_is_selected" => true,
								"bookingpress_selected_qty" => $bookingpress_appointment_data['bookingpress_selected_extra_details'][$v]['bookingpress_selected_qty'],
								"bookingpress_final_payable_price" => $bookingpress_appointment_data['selected_extra_services'][$v]['bookingpress_extra_service_price'] * $bookingpress_appointment_data['bookingpress_selected_extra_details'][$v]['bookingpress_selected_qty'],
								"bookingpress_extra_service_details" => $bookingpress_appointment_data['selected_extra_services'][$v],
							);
						}
					}
										
					$bookingpress_staffmember_id = $bookingpress_appointment_data['selected_staffmember'];
					$bookingpress_staffmember_firstname = $bookingpress_staffmember_lastname = $bookingpress_staffmember_email = "";
					$bookingpress_staffmember_details = array();
					$bookingpress_staffmember_price = 0;
					if(!empty($bookingpress_staffmember_id)){
						
						$bookingpress_staffmember_details = $wpdb->get_row( $wpdb->prepare( "SELECT bstf.*, bstfs.bookingpress_service_id, bstfs.bookingpress_service_price FROM $tbl_bookingpress_staffmembers bstf LEFT JOIN $tbl_bookingpress_staffmembers_services bstfs ON bstf.bookingpress_staffmember_id=bstfs.bookingpress_staffmember_id WHERE bstf.bookingpress_staffmember_id = %d AND bstfs.bookingpress_service_id = %d", $bookingpress_staffmember_id, $bookingpress_appointment_data['appointment_selected_service'] ) ); // phpcs:ignore
						
						if( !empty( $bookingpress_staffmember_details) ){
							$bookingpress_staffmember_price = $bookingpress_staffmember_details->bookingpress_service_price;
							$bookingpress_staffmember_firstname = $bookingpress_staffmember_details->bookingpress_staffmember_firstname;
							$bookingpress_staffmember_lastname = $bookingpress_staffmember_details->bookingpress_staffmember_lastname;
							$bookingpress_staffmember_email = $bookingpress_staffmember_details->bookingpress_staffmember_email;
						}
					}

					$bookingpress_applied_coupon_details = !empty($bookingpress_appointment_data['applied_coupon_details']) ? $bookingpress_appointment_data['applied_coupon_details'] : array();
					if(!empty($bookingpress_applied_coupon_details)){
						$bookingpress_applied_coupon_details = array(
							'coupon_status' => 'success',
							'msg' => 'Coupon applied successfully',
							'coupon_data' => $bookingpress_applied_coupon_details
						);
					}

					$appointment_booking_field['bookingpress_coupon_details'] = wp_json_encode($bookingpress_applied_coupon_details);
					$appointment_booking_field['bookingpress_coupon_discount_amount'] = $bookingpress_appointment_data['coupon_discounted_amount'];
					$appointment_booking_field['bookingpress_tax_percentage'] = $bookingpress_appointment_data['tax_percentage'];
					$appointment_booking_field['bookingpress_tax_amount'] = $bookingpress_appointment_data['tax'];
					$appointment_booking_field['bookingpress_selected_extra_members'] = $bookingpress_appointment_data['selected_bring_members'];
					$appointment_booking_field['bookingpress_extra_service_details'] = wp_json_encode($bookingpress_extra_services_details);
					$appointment_booking_field['bookingpress_staff_member_id'] = $bookingpress_staffmember_id;
					$appointment_booking_field['bookingpress_staff_member_price'] = $bookingpress_staffmember_price;
					$appointment_booking_field['bookingpress_staff_first_name'] = $bookingpress_staffmember_firstname;
					$appointment_booking_field['bookingpress_staff_last_name'] = $bookingpress_staffmember_lastname;
					$appointment_booking_field['bookingpress_staff_email_address'] = $bookingpress_staffmember_email;
					$appointment_booking_field['bookingpress_staff_member_details'] = json_encode( $bookingpress_staffmember_details );
					$appointment_booking_field['bookingpress_paid_amount'] = $entry_data['bookingpress_paid_amount'];
					$appointment_booking_field['bookingpress_due_amount'] = $entry_data['bookingpress_due_amount'];
					$appointment_booking_field['bookingpress_total_amount'] = $bookingpress_appointment_data['total_amount'];


					$bookingpress_appointment_form_fields_data = array(
						'form_fields' => !empty($bookingpress_appointment_data['bookingpress_appointment_meta_fields_value']) ? $bookingpress_appointment_data['bookingpress_appointment_meta_fields_value'] : array(),
						'bookingpress_front_field_data' => !empty($bookingpress_appointment_data['bookingpress_appointment_meta_fields_value']) ? $bookingpress_appointment_data['bookingpress_appointment_meta_fields_value'] : array(),
					);

					if(!empty($appointment_booking_field['bookingpress_is_cart']) && $appointment_booking_field['bookingpress_is_cart'] == 1){						
						
						$bookingpress_order_id = (isset($appointment_booking_field['bookingpress_order_id']))?$appointment_booking_field['bookingpress_order_id']:0;
						$get_form_fields_meta = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_order_id = %d", 'appointment_details', $bookingpress_order_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name. false alarm

						if( 1 > $get_form_fields_meta ){
							$wpdb->insert(
								$tbl_bookingpress_appointment_meta,
								array(
									'bookingpress_appointment_meta_key' => 'appointment_details',
									'bookingpress_appointment_meta_value' => wp_json_encode( $bookingpress_appointment_form_fields_data ),
									'bookingpress_order_id' => $bookingpress_order_id
								)
							);
						} else {
							$bookingpress_db_fields = array(
								'bookingpress_appointment_meta_value' => wp_json_encode($bookingpress_appointment_form_fields_data),
							);							
							$wpdb->update($tbl_bookingpress_appointment_meta, $bookingpress_db_fields, array('bookingpress_order_id' => $bookingpress_order_id, 'bookingpress_appointment_meta_key' => 'appointment_details'));
						}						
						
					}else{

						$get_form_fields_meta = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_appointment_id = %d", 'appointment_form_fields_data', $bookingpress_appointment_booking_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name. false alarm

						if( 1 > $get_form_fields_meta ){
							$wpdb->insert(
								$tbl_bookingpress_appointment_meta,
								array(
									'bookingpress_appointment_meta_key' => 'appointment_form_fields_data',
									'bookingpress_appointment_meta_value' => wp_json_encode( $bookingpress_appointment_form_fields_data ),
									'bookingpress_appointment_id' => $bookingpress_appointment_booking_id
								)
							);
						} else {
	
							$bookingpress_db_fields = array(
								'bookingpress_appointment_meta_value' => wp_json_encode($bookingpress_appointment_form_fields_data),
							);
							
							$wpdb->update($tbl_bookingpress_appointment_meta, $bookingpress_db_fields, array('bookingpress_appointment_id' => $bookingpress_appointment_booking_id, 'bookingpress_appointment_meta_key' => 'appointment_form_fields_data'));
						}
	


					}

					/** store last appointment data into meta to remove integration details */
					if( !empty( $bookingpress_existing_appointment_data ) ){

						$get_last_appointment_data = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_appointment_id = %d", '_bpa_last_appointment_data', $bookingpress_appointment_booking_id) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name. false alarm
						if( 1 > $get_last_appointment_data ){
							$wpdb->insert(
								$tbl_bookingpress_appointment_meta,
								array(
									'bookingpress_appointment_meta_key' => '_bpa_last_appointment_data',
									'bookingpress_appointment_meta_value' => wp_json_encode( $bookingpress_existing_appointment_data ),
									'bookingpress_appointment_id' => $bookingpress_appointment_booking_id
								)
							);
						} else {
							$bookingpress_db_fields = array(
								'bookingpress_appointment_meta_value' => wp_json_encode( $bookingpress_existing_appointment_data )
							);	
							$wpdb->update( $tbl_bookingpress_appointment_meta, $bookingpress_db_fields, array( 'bookingpress_appointment_id' => $bookingpress_appointment_booking_id, 'bookingpress_appointment_meta_key' => '_bpa_last_appointment_data' ) );
						}
					}
				}
			}	

			return $appointment_booking_field;
		}
		
		/**
		 * Function for modify add appointment entry data
		 *
		 * @param  mixed $bookingpress_entry_details
		 * @param  mixed $bookingpress_appointment_data
		 * @return void
		 */
		function bookingpress_modify_backend_add_appointment_entry_data_func($bookingpress_entry_details, $bookingpress_appointment_data){
			global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services;
			if(!empty($bookingpress_entry_details) && !empty($bookingpress_appointment_data) ){

				if(!empty($bookingpress_entry_details['bookingpress_service_duration_unit']) && $bookingpress_entry_details['bookingpress_service_duration_unit'] == 'd' ){
					$bookingpress_entry_details['bookingpress_appointment_end_time'] = '00:00:00';
				}

				$bookingpress_coupon_code = !empty($bookingpress_appointment_data['applied_coupon_code']) ? $bookingpress_appointment_data['applied_coupon_code'] : '';
				$bookingpress_applied_coupon_details = !empty($bookingpress_appointment_data['applied_coupon_details']) ? $bookingpress_appointment_data['applied_coupon_details'] : array();
				$bookingpress_coupon_discount_amount = !empty($bookingpress_appointment_data['coupon_discounted_amount']) ? floatval($bookingpress_appointment_data['coupon_discounted_amount']) : 0;

				$bookingpress_tax_percentage = !empty($bookingpress_appointment_data['tax_percentage']) ? floatval($bookingpress_appointment_data['tax_percentage']) : 0;
				$bookingpress_tax_amount = !empty($bookingpress_appointment_data['tax']) ? $bookingpress_appointment_data['tax'] : 0;
				$bookingpress_tax_price_display_options = !empty($bookingpress_appointment_data['tax_price_display_options']) ? $bookingpress_appointment_data['tax_price_display_options'] : 'exclude_taxes';
				$bookingpress_tax_order_summary = (!empty($bookingpress_appointment_data['display_tax_order_summary']) && $bookingpress_appointment_data['display_tax_order_summary'] == 'true') ? 1 : 0;
				$bookingpress_included_tax_label = !empty($bookingpress_appointment_data['included_tax_label']) ? $bookingpress_appointment_data['included_tax_label'] : '';

				$bookingpress_selected_extra_members = !empty($bookingpress_appointment_data['selected_bring_members']) ? intval($bookingpress_appointment_data['selected_bring_members']) : 1;

				$bookingpress_extra_services_details = array();
				if(!empty($bookingpress_appointment_data['selected_extra_services_ids'])){
					foreach($bookingpress_appointment_data['selected_extra_services_ids'] as $k => $v){
						$bookingpress_extra_services_details[] = array(
							"bookingpress_is_selected" => true,
							"bookingpress_selected_qty" => $bookingpress_appointment_data['bookingpress_selected_extra_details'][$v]['bookingpress_selected_qty'],
							"bookingpress_final_payable_price" => $bookingpress_appointment_data['selected_extra_services'][$v]['bookingpress_extra_service_price'] * $bookingpress_appointment_data['bookingpress_selected_extra_details'][$v]['bookingpress_selected_qty'],
							"bookingpress_extra_service_details" => $bookingpress_appointment_data['selected_extra_services'][$v],
						);
					}
				}

				$bookingpress_staffmember_id = !empty($bookingpress_appointment_data['selected_staffmember']) ? intval($bookingpress_appointment_data['selected_staffmember']) : 0;
				
				$bookingpress_staffmember_firstname = $bookingpress_staffmember_lastname = $bookingpress_staffmember_email = "";
				$bookingpress_staffmember_details = array();
				$bookingpress_staffmember_price = 0;
				if(!empty($bookingpress_staffmember_id)){
					$bookingpress_staffmember_details = $wpdb->get_row( $wpdb->prepare( "SELECT bstf.*, bstfs.bookingpress_service_id, bstfs.bookingpress_service_price FROM $tbl_bookingpress_staffmembers bstf LEFT JOIN $tbl_bookingpress_staffmembers_services bstfs ON bstf.bookingpress_staffmember_id=bstfs.bookingpress_staffmember_id WHERE bstf.bookingpress_staffmember_id = %d AND bstfs.bookingpress_service_id = %d", $bookingpress_staffmember_id, $bookingpress_appointment_data['appointment_selected_service'] ) ); // phpcs:ignore
						
					if( !empty( $bookingpress_staffmember_details) ){
						$bookingpress_staffmember_price = $bookingpress_staffmember_details->bookingpress_service_price;
						$bookingpress_staffmember_firstname = $bookingpress_staffmember_details->bookingpress_staffmember_firstname;
						$bookingpress_staffmember_lastname = $bookingpress_staffmember_details->bookingpress_staffmember_lastname;
						$bookingpress_staffmember_email = $bookingpress_staffmember_details->bookingpress_staffmember_email;
					}
				}

				$bookingpress_paid_amount = $bookingpress_due_amount = $bookingpress_appointment_data['total_amount'];
				$bookingpress_total_amount = $bookingpress_appointment_data['total_amount'];

				if($bookingpress_appointment_data['complete_payment_url_selection'] == "mark_as_paid"){
					$bookingpress_entry_details['bookingpress_mark_as_paid'] = 1;
				}else{
					$bookingpress_entry_details['bookingpress_mark_as_paid'] = 0;
				}

				$bookingpress_entry_details['bookingpress_complete_payment_url_selection'] = $bookingpress_appointment_data['complete_payment_url_selection'];
				$tmp_var = !empty($bookingpress_appointment_data['complete_payment_url_selected_method']) ? implode(',', $bookingpress_appointment_data['complete_payment_url_selected_method']) : '';
				$bookingpress_entry_details['bookingpress_complete_payment_url_selection_method'] = $tmp_var;
				if($bookingpress_appointment_data['complete_payment_url_selection'] == "send_payment_link"){
					$bookingpress_entry_details['bookingpress_complete_payment_token'] = uniqid("bpa", true);
				}
				$bookingpress_entry_details['bookingpress_coupon_details'] = wp_json_encode($bookingpress_applied_coupon_details);
				$bookingpress_entry_details['bookingpress_coupon_discount_amount'] = $bookingpress_coupon_discount_amount;
				$bookingpress_entry_details['bookingpress_tax_percentage'] = $bookingpress_tax_percentage;
				$bookingpress_entry_details['bookingpress_tax_amount'] = $bookingpress_tax_amount;
				$bookingpress_entry_details['bookingpress_price_display_setting'] = $bookingpress_tax_price_display_options;
				$bookingpress_entry_details['bookingpress_display_tax_order_summary'] = $bookingpress_tax_order_summary;
				$bookingpress_entry_details['bookingpress_included_tax_label'] = $bookingpress_included_tax_label;
				$bookingpress_entry_details['bookingpress_selected_extra_members'] = $bookingpress_selected_extra_members;
				$bookingpress_entry_details['bookingpress_extra_service_details'] = wp_json_encode($bookingpress_extra_services_details);
				$bookingpress_entry_details['bookingpress_staff_member_id'] = $bookingpress_staffmember_id;
				$bookingpress_entry_details['bookingpress_staff_member_price'] = $bookingpress_staffmember_price;
				$bookingpress_entry_details['bookingpress_staff_first_name'] = $bookingpress_staffmember_firstname;
				$bookingpress_entry_details['bookingpress_staff_last_name'] = $bookingpress_staffmember_lastname;
				$bookingpress_entry_details['bookingpress_staff_email_address'] = $bookingpress_staffmember_email;
				$bookingpress_entry_details['bookingpress_staff_member_details'] = wp_json_encode($bookingpress_staffmember_details);
				$bookingpress_entry_details['bookingpress_paid_amount'] = $bookingpress_paid_amount;
				$bookingpress_entry_details['bookingpress_due_amount'] = $bookingpress_due_amount;
				$bookingpress_entry_details['bookingpress_total_amount'] = $bookingpress_total_amount;
			}
			return $bookingpress_entry_details;
		}
		
		/**
		 * Function for admin appointment data recalculate
		 *
		 * @return void
		 */
		function bookingpress_admin_appointment_recalculate_data_func(){
			global $wpdb, $BookingPress, $tbl_bookingpress_extra_services, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services, $bookingpress_pro_services;

			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'admin_recalculate_appointment_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			
			if( !empty( $_POST['appointment_formdata'] ) && !is_array( $_POST['appointment_formdata'] ) ){ //phpcs:ignore
				$_POST['appointment_formdata'] = json_decode( stripslashes_deep( $_POST['appointment_formdata'] ), true ); //phpcs:ignore
				$_POST['appointment_formdata'] = $this->bookingpress_boolean_type_cast( $_POST['appointment_formdata'] ); //phpcs:ignore
				$_REQUEST['appointment_formdata'] = $_POST['appointment_formdata']; //phpcs:ignore
			}

			$bookingpress_appointment_formdata = !empty( $_POST['appointment_formdata'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_formdata']) : array(); // phpcs:ignore
			$bookingpress_appointment_extra_details = !empty( $_POST['appointment_extra_details'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_extra_details']) : array();  // phpcs:ignore
			$bookingpress_appointment_staff_details = !empty($_POST['appointment_staff_details']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_staff_details']) : array();  // phpcs:ignore

			if(!empty($bookingpress_appointment_formdata)){
				$bookingpress_selected_service = $bookingpress_appointment_formdata['appointment_selected_service'];
				$bookingpress_selected_service_details = $BookingPress->get_service_by_id($bookingpress_selected_service);

				$bookingpress_subtotal_price = !empty($bookingpress_selected_service_details['bookingpress_service_price']) ? $bookingpress_selected_service_details['bookingpress_service_price'] : 0;

				//If staff member selected then get staff member price
				$bookingpress_selected_staffmember = !empty($bookingpress_appointment_formdata['selected_staffmember']) ? $bookingpress_appointment_formdata['selected_staffmember'] : 0;
				if(!empty($bookingpress_selected_staffmember) && !empty($bookingpress_appointment_staff_details) ){
					//$bookingpress_staffmember_list = $bookingpress_appointment_formdata['bookingpress_staffmembers_lists'];
					foreach($bookingpress_appointment_staff_details as $k => $v){
						if($v['bookingpress_staffmember_id'] == $bookingpress_selected_staffmember){
							$bookingpress_subtotal_price = $v['bookingpress_service_price'];
							break;
						}
					}
				}

				$bookingpress_subtotal_price = apply_filters('bookingpress_admin_side_filter_custom_duration_data',$bookingpress_subtotal_price,$bookingpress_appointment_formdata,$bookingpress_selected_service_details);

				//Add bring anyone with you total
				$bookingpress_bring_anyone = intval($bookingpress_appointment_formdata['selected_bring_members']) - 1;
				
				if($bookingpress_bring_anyone > 0){
					$bookingpress_subtotal_price = $bookingpress_subtotal_price + ($bookingpress_subtotal_price * $bookingpress_bring_anyone);
				}

				$bookingpress_extras_price_total = 0;
				$bookingpress_selected_extra_services = $bookingpress_appointment_extra_details;
				if(!empty($bookingpress_selected_extra_services)){
					foreach($bookingpress_selected_extra_services as $k2 => $v2){
						if($v2['bookingpress_is_selected'] == "true"){
							$bookingpress_extras_price_total = $bookingpress_extras_price_total + ($v2['bookingpress_extra_service_price'] * $v2['bookingpress_selected_qty']);
						}
					}
				}
				
				$bookingpress_appointment_formdata['subtotal'] = $bookingpress_subtotal_price;
				$bookingpress_appointment_formdata['subtotal_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_subtotal_price);
				$bookingpress_appointment_formdata['extras_total'] = $bookingpress_extras_price_total;
				$bookingpress_appointment_formdata['extras_total_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_extras_price_total);

				$bookingpress_subtotal_price = $bookingpress_subtotal_price + $bookingpress_extras_price_total;

				$bookingpress_appointment_formdata = apply_filters('bookingpress_modify_backend_appointment_data', $bookingpress_appointment_formdata, $bookingpress_subtotal_price);
				$bookingpress_subtotal_price = apply_filters('bookingpress_modify_backend_subtotal_price', $bookingpress_subtotal_price, $bookingpress_appointment_formdata);

				$bookingpress_total_price = $bookingpress_subtotal_price;

				if(!empty($bookingpress_appointment_formdata['coupon_discounted_amount']) && !empty($bookingpress_appointment_formdata['applied_coupon_code']) ){
					$bookingpress_total_price = $bookingpress_total_price - $bookingpress_appointment_formdata['coupon_discounted_amount'];
				}

				$bookingpress_appointment_formdata['total_amount'] = $bookingpress_total_price;
				$bookingpress_appointment_formdata['total_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_total_price);
			}

			$bookingpress_return_data = array('appointment_formdata' => $bookingpress_appointment_formdata);
			echo wp_json_encode($bookingpress_return_data);
			exit;
		}
		
		/**
		 * Function for get backend service extras
		 *
		 * @return void
		 */
		function bookingpress_get_backend_service_extras_func(){
			global $wpdb, $BookingPress, $tbl_bookingpress_extra_services, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services, $bookingpress_pro_services;

			$response                       = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'get_backend_service_extras_details', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			$response['variant']            = 'success';
			$response['title']              = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['msg']                = esc_html__( 'Extra services data retrived successfully', 'bookingpress-appointment-booking' );
			$response['extra_service_data'] = array();
			$response['selected_extra_service_data'] = array();
			$response['staffmembers_list'] = array();
			$response['service_max_capacity'] = 0;

			$bookingpress_selected_service_id = !empty($_POST['service_id']) ? intval($_POST['service_id']) : 0; // phpcs:ignore
			if(!empty($bookingpress_selected_service_id)){
				$bookingpress_extra_services_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_service_id = %d", $bookingpress_selected_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is table name defined globally.

				if(!empty($bookingpress_extra_services_data)){
					$bookingpress_selected_extra_service_data = array();
					foreach($bookingpress_extra_services_data as $k => $v){
						$bookingpress_extra_services_data[$k]['bookingpress_extra_service_price_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($v['bookingpress_extra_service_price']);
						$bookingpress_extra_services_data[$k]['bookingpress_is_display_description'] = 0;

						$v['bookingpress_is_selected'] = false;
						$v['bookingpress_selected_qty'] = 0;
						$v['bookingpress_price_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($v['bookingpress_extra_service_price']);

						$v['bookingpress_is_display_description'] = 0;

						$bookingpress_selected_extra_service_data[$v['bookingpress_extra_services_id']] = $v;
					}
					$response['extra_service_data'] = $bookingpress_extra_services_data;
					$response['selected_extra_service_data'] = $bookingpress_selected_extra_service_data;
				}
				$search_query = '';
				$search_query = apply_filters('bookingpress_appointment_service_wise_staffmember_list',$search_query);
				$bookingpress_staffmembers_list = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_service_id = %d {$search_query}", $bookingpress_selected_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
				if(!empty($bookingpress_staffmembers_list) && is_array($bookingpress_staffmembers_list)){
					foreach($bookingpress_staffmembers_list as $k2 => $v2){
						$bookingpress_staffmember_id = intval($v2['bookingpress_staffmember_id']);
						$bookingpress_staffmember_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $bookingpress_staffmember_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is table name.

						$bookingpress_staffmembers_list[$k2]['staff_details'] = $bookingpress_staffmember_details;
					}
				}

				$response['staffmembers_list'] = $bookingpress_staffmembers_list;

				$bookingpress_service_max_capacity = $bookingpress_pro_services->bookingpress_get_service_max_capacity($bookingpress_selected_service_id);
				$response['service_max_capacity'] = $bookingpress_service_max_capacity;
			}

			echo wp_json_encode($response);
			exit;
		}
		
		/**
		 * Function for execute code after selecting service at backend
		 *
		 * @return void
		 */
		function bookingpress_after_selecting_service_at_backend_func(){
			?>
				if(vm.appointment_formdata.applied_coupon_code != ''){
					vm.bookingpress_apply_coupon_code();
				}
			<?php
		}
		
		/**
		 * Common function for get all details related to appointment
		 *
		 * @param  mixed $bookingpress_appointment_id
		 * @param  mixed $bookingpress_payment_id
		 * @return void
		 */
		function bookingpress_calculated_appointment_details($bookingpress_appointment_id, $bookingpress_payment_id){
			global $wpdb, $BookingPress, $BookingPressPro, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_staff_members, $bookingpress_global_options, $tbl_bookingpress_payment_logs, $tbl_bookingpress_form_fields, $bookingpress_pro_payment;

			$bookingpress_return_calculated_details = array();

			if( !empty($bookingpress_appointment_id) && !empty($bookingpress_payment_id) ){
				//Get payment related details for appointment
				$bookingpress_payment_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = %d", $bookingpress_payment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

				$bookingpress_selected_gateway = !empty($bookingpress_payment_details['bookingpress_payment_gateway']) ? $bookingpress_payment_details['bookingpress_payment_gateway'] : '';
				$bookingpress_selected_gateway = apply_filters('bookingpress_selected_gateway_label_name', $bookingpress_selected_gateway, $bookingpress_selected_gateway);
				$bookingpress_payment_status = !empty($bookingpress_payment_details['bookingpress_payment_status']) ? intval($bookingpress_payment_details['bookingpress_payment_status']) : 1;

				$bookingpress_return_calculated_details['bookingpress_payment_status'] = $bookingpress_payment_status;

				//---------------------------------------------------------------------------------------------------------

				//Get payment details
				$bookingpress_calculated_payment_details = $bookingpress_pro_payment->bookingpress_calculate_payment_details($bookingpress_payment_id);
				$bookingpress_return_calculated_details['bookingpress_selected_gateway'] = $bookingpress_selected_gateway;
				$bookingpress_return_calculated_details['bookingpress_selected_gateway_label'] = !empty($bookingpress_calculated_payment_details['selected_gateway_label']) ? $bookingpress_calculated_payment_details['selected_gateway_label'] : $bookingpress_selected_gateway;

				//Get appointment related details for appointment
				$bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

				if(!empty($bookingpress_appointment_details)){
					$bookingpress_is_cart = intval($bookingpress_appointment_details['bookingpress_is_cart']);
					$bookingpress_order_id = intval($bookingpress_appointment_details['bookingpress_order_id']);
					$bookingpress_currency_name = $bookingpress_appointment_details['bookingpress_service_currency'];
					$bookingpress_selected_currency = $BookingPress->bookingpress_get_currency_symbol($bookingpress_currency_name);
					$bookingpress_is_reschedule = $bookingpress_appointment_details['bookingpress_is_reschedule'];
					$bookingpress_return_calculated_details['is_rescheduled'] = $bookingpress_is_reschedule;

					$bookingpress_customer_id = $bookingpress_appointment_details['bookingpress_customer_id'];
					$bookingpress_customer_firstname = $bookingpress_appointment_details['bookingpress_customer_firstname'];
					$bookingpress_customer_lastname = $bookingpress_appointment_details['bookingpress_customer_lastname'];
					$bookingpress_customer_email_address = $bookingpress_appointment_details['bookingpress_customer_email'];
					$bookingpress_customer_phone_no = $bookingpress_appointment_details['bookingpress_customer_phone'];
					$bookingpress_customer_dialcode = $bookingpress_appointment_details['bookingpress_customer_phone_dial_code'];
					if(!empty($bookingpress_customer_dialcode)){
						$bookingpress_customer_phone_no = "+".$bookingpress_customer_dialcode." ".$bookingpress_customer_phone_no;
					}

					//Return other appointment details
					$bookingpress_return_calculated_details['is_cart'] = $bookingpress_is_cart;
					$bookingpress_return_calculated_details['cart_order_id'] = $bookingpress_order_id;
					$bookingpress_return_calculated_details['appointment_currency'] = $bookingpress_currency_name;
					$bookingpress_return_calculated_details['customer_details'] = array(
						'customer_id' => $bookingpress_customer_id,
						'customer_firstname' => $bookingpress_customer_firstname,
						'customer_lastname' => $bookingpress_customer_lastname,
						'customer_email_address' => $bookingpress_customer_email_address,
						'customer_phone_no' => $bookingpress_customer_phone_no,
						'customer_dialcode' => $bookingpress_customer_dialcode,
					);


					//Return calculated details
					$bookingpress_subtotal_amt = $bookingpress_service_price = floatval($bookingpress_appointment_details['bookingpress_service_price']);

					$bookingpress_staff_member_id = $bookingpress_appointment_details['bookingpress_staff_member_id'];
					$bookingpress_staffmember_firstname = $bookingpress_staffmember_lastname = $bookingpress_staffmember_email_address = $bookingpress_staff_avatar_url = "";
					if(!empty($bookingpress_staff_member_id)){
						$bookingpress_subtotal_amt = !empty($bookingpress_appointment_details['bookingpress_staff_member_price']) ? floatval($bookingpress_appointment_details['bookingpress_staff_member_price']) : $bookingpress_subtotal_amt;

						$bookingpress_staffmember_avatar = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta($bookingpress_staff_member_id, 'staffmember_avatar_details');
						$bookingpress_staffmember_avatar = !empty($bookingpress_staffmember_avatar) ? maybe_unserialize($bookingpress_staffmember_avatar) : array();
						if (!empty($bookingpress_staffmember_avatar[0]['url'])) {
							$bookingpress_staff_avatar_url = $bookingpress_staffmember_avatar[0]['url'];
						}else{
							$bookingpress_staff_avatar_url = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
						}

						$bookingpress_staffmember_firstname = $bookingpress_appointment_details['bookingpress_staff_first_name'];
						$bookingpress_staffmember_lastname = $bookingpress_appointment_details['bookingpress_staff_last_name'];
						$bookingpress_staffmember_email_address = $bookingpress_appointment_details['bookingpress_staff_email_address'];
					}
					$bookingpress_selected_bring_anyone_members = intval($bookingpress_appointment_details['bookingpress_selected_extra_members']) - 1;
					if(!empty($bookingpress_selected_bring_anyone_members)){				
						$bookingpress_subtotal_amt = $bookingpress_subtotal_amt + ($bookingpress_subtotal_amt * $bookingpress_selected_bring_anyone_members);
					}					
					$bookingpress_extra_total = 0;
					$bookingpress_extra_service_details = !empty($bookingpress_appointment_details['bookingpress_extra_service_details']) ? json_decode($bookingpress_appointment_details['bookingpress_extra_service_details'], TRUE) : array();
					$bookingpress_extra_service_data = array();
					if(!empty($bookingpress_extra_service_details)){
						foreach($bookingpress_extra_service_details as $k3 => $v3){
							$bookingpress_extra_total = $bookingpress_extra_total + $v3['bookingpress_final_payable_price'];
							$bookingpress_extra_service_price = ($v3['bookingpress_extra_service_details']['bookingpress_extra_service_price']) * ($v3['bookingpress_selected_qty']);
							$bookingpress_extra_service_data[] = array(
								'selected_qty' => $v3['bookingpress_selected_qty'],
								'extra_name' => $v3['bookingpress_extra_service_details']['bookingpress_extra_service_name'],
								'extra_service_duration' => $v3['bookingpress_extra_service_details']['bookingpress_extra_service_duration']." ".$v3['bookingpress_extra_service_details']['bookingpress_extra_service_duration_unit'],
								'extra_service_price' => $bookingpress_extra_service_price,
								'extra_service_price_with_currency' => $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_extra_service_price, $bookingpress_selected_currency),
							);
						}
					}

					$bookingpress_subtotal_amt = !empty($bookingpress_calculated_payment_details['subtotal_amount']) ? floatval($bookingpress_calculated_payment_details['subtotal_amount']) : $bookingpress_subtotal_amt + $bookingpress_extra_total;

					$bookingpress_is_deposit_enable = !empty($bookingpress_calculated_payment_details['is_deposit_enable']) ? $bookingpress_calculated_payment_details['is_deposit_enable'] : 0;
					$bookingpress_deposit_amount = !empty($bookingpress_calculated_payment_details['deposit_amount']) ? $bookingpress_calculated_payment_details['deposit_amount'] : 0;
					
					$bookingpress_return_calculated_details['bookingpress_service_price'] = $bookingpress_service_price;
					$bookingpress_return_calculated_details['staffmember_details'] = array(
						'staffmember_id' => $bookingpress_staff_member_id,
						'staffmember_firstname' => $bookingpress_staffmember_firstname,
						'staffmember_lastname' => $bookingpress_staffmember_lastname,
						'staffmember_email_address' => $bookingpress_staffmember_email_address,
						'staffmember_avatar' => $bookingpress_staff_avatar_url,
					);					

					$bookingpress_return_calculated_details['selected_extra_members'] = intval($bookingpress_appointment_details['bookingpress_selected_extra_members']);

					$bookingpress_return_calculated_details['extra_services_total'] = $bookingpress_extra_total;
					$bookingpress_return_calculated_details['extra_services_details'] = $bookingpress_extra_service_data;

					$bookingpress_return_calculated_details['subtotal_amt'] = $bookingpress_subtotal_amt;
					$bookingpress_return_calculated_details['subtotal_amt_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_subtotal_amt, $bookingpress_selected_currency);

					$bookingpress_return_calculated_details['is_deposit_enable'] = $bookingpress_is_deposit_enable;
					$bookingpress_return_calculated_details['deposit_price'] = $bookingpress_deposit_amount;
					$bookingpress_return_calculated_details['deposit_price_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_deposit_amount, $bookingpress_selected_currency);
				}

				$bookingpress_tax_amount = !empty($bookingpress_calculated_payment_details['tax_amount']) ? floatval($bookingpress_calculated_payment_details['tax_amount']) : 0;
				$bookingpress_tax_amount_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_tax_amount, $bookingpress_selected_currency);

				$bookingpress_return_calculated_details['bookingpress_tax_amount'] = $bookingpress_tax_amount;
				$bookingpress_return_calculated_details['bookingpress_tax_amount_with_currency'] = $bookingpress_tax_amount_with_currency;

				$bookingpress_return_calculated_details['price_display_setting'] = !empty($bookingpress_calculated_payment_details['price_display_setting']) ? $bookingpress_calculated_payment_details['price_display_setting'] : 'exclude_taxes';
				$bookingpress_return_calculated_details['display_tax_amount_in_order_summary'] = !empty($bookingpress_calculated_payment_details['display_tax_amount_in_order_summary']) ? $bookingpress_calculated_payment_details['display_tax_amount_in_order_summary'] : 'false';
				$bookingpress_return_calculated_details['included_tax_label'] = !empty($bookingpress_calculated_payment_details['included_tax_label']) ? $bookingpress_calculated_payment_details['included_tax_label'] : '';
				
				$bookingpress_applied_coupon_code = !empty($bookingpress_calculated_payment_details['applied_coupon_code']) ? $bookingpress_calculated_payment_details['applied_coupon_code'] : '';
				$bookingpress_applied_coupon_discount = !empty($bookingpress_calculated_payment_details['coupon_discount_amount']) ? floatval($bookingpress_calculated_payment_details['coupon_discount_amount']) : 0;

				$bookingpress_return_calculated_details['applied_coupon'] = $bookingpress_applied_coupon_code;
				$bookingpress_return_calculated_details['coupon_discount_amt'] = $bookingpress_applied_coupon_discount;
				$bookingpress_return_calculated_details['coupon_discount_amt_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_applied_coupon_discount, $bookingpress_selected_currency);

				//$bookingpress_final_total_amount = ($bookingpress_subtotal_amt + $bookingpress_tax_amount) - $bookingpress_applied_coupon_discount;
				$bookingpress_paid_total_amount = !empty($bookingpress_calculated_payment_details['payment_amount']) ? $bookingpress_calculated_payment_details['payment_amount'] : 0;
				$bookingpress_final_total_amount = !empty($bookingpress_calculated_payment_details['total_amount']) ? $bookingpress_calculated_payment_details['total_amount'] : 0;
				$bookingpress_return_calculated_details['paid_total_amount'] = $bookingpress_paid_total_amount;
				$bookingpress_return_calculated_details['final_total_amount'] = $bookingpress_final_total_amount;
				$bookingpress_return_calculated_details['final_total_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_final_total_amount, $bookingpress_selected_currency);

				$bookingpress_return_calculated_details['due_amount'] = !empty($bookingpress_calculated_payment_details['due_amount']) ? $bookingpress_calculated_payment_details['due_amount'] : '';
				$bookingpress_return_calculated_details['due_amount_with_currency'] = !empty($bookingpress_calculated_payment_details['due_amount_with_currency']) ? $bookingpress_calculated_payment_details['due_amount_with_currency'] : '';
				$bookingpress_return_calculated_details['payment_numberic_amount'] = isset($bookingpress_calculated_payment_details['payment_numberic_amount']) ? $bookingpress_calculated_payment_details['payment_numberic_amount'] : 0;
			}
			
			$bookingpress_return_calculated_details = apply_filters('bookingpress_return_calculated_details_modify_outside',$bookingpress_return_calculated_details, $bookingpress_calculated_payment_details, $bookingpress_appointment_id, $bookingpress_payment_id );
			return $bookingpress_return_calculated_details;
		}
				
		/**
		 * Function for modify appointment listing details
		 *
		 * @param  mixed $bookingpress_appointment_data
		 * @return void
		 */
		function bookingpress_modify_appointment_data_func($bookingpress_appointment_data){
			
			global $wpdb, $BookingPress, $BookingPressPro, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_staff_members, $bookingpress_global_options, $tbl_bookingpress_payment_logs, $tbl_bookingpress_form_fields;
			$bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();
			$default_date_format = $bookingpress_global_data['wp_default_date_format'];           
			$default_time_format = $bookingpress_global_data['wp_default_time_format'];  


			if(!empty($bookingpress_appointment_data) && is_array($bookingpress_appointment_data) ){

				foreach($bookingpress_appointment_data as $k => $v){
					$bookingpress_appointment_id = $v['appointment_id'];
					$bookingpress_payment_log_id = $v['payment_id'];

					$bookingpress_appointment_details = $this->bookingpress_calculated_appointment_details($bookingpress_appointment_id, $bookingpress_payment_log_id);
					if(!empty($bookingpress_appointment_details)){
						$bookingpress_appointment_data[$k]['is_rescheduled'] = $bookingpress_appointment_details['is_rescheduled'];
						$bookingpress_appointment_data[$k]['payment_method'] = $bookingpress_appointment_details['bookingpress_selected_gateway'];
						$bookingpress_appointment_data[$k]['payment_method_label'] = $bookingpress_appointment_details['bookingpress_selected_gateway_label'];
						
						$bookingpress_appointment_data[$k]['bookingpress_payment_status'] = $bookingpress_appointment_details['bookingpress_payment_status'];

						$bookingpress_appointment_data[$k]['bookingpress_subtotal_amt'] = $bookingpress_appointment_details['subtotal_amt'];
						$bookingpress_appointment_data[$k]['bookingpress_subtotal_amt_with_currency'] = $bookingpress_appointment_details['subtotal_amt_with_currency'];

						$bookingpress_appointment_data[$k]['bookingpress_deposit_amt'] = $bookingpress_appointment_details['deposit_price'];
						$bookingpress_appointment_data[$k]['bookingpress_deposit_amt_with_currency'] = $bookingpress_appointment_details['deposit_price_with_currency'];

						$bookingpress_appointment_data[$k]['bookingpress_tax_amt'] = $bookingpress_appointment_details['bookingpress_tax_amount'];
						$bookingpress_appointment_data[$k]['bookingpress_tax_amt_with_currency'] = $bookingpress_appointment_details['bookingpress_tax_amount_with_currency'];

						$bookingpress_appointment_data[$k]['appointment_payment'] = $bookingpress_appointment_details['paid_total_amount'];						

						$bookingpress_appointment_data[$k]['price_display_setting'] = $bookingpress_appointment_details['price_display_setting'];
						$bookingpress_appointment_data[$k]['display_tax_amount_in_order_summary'] = $bookingpress_appointment_details['display_tax_amount_in_order_summary'];
						$bookingpress_appointment_data[$k]['included_tax_label'] = $bookingpress_appointment_details['included_tax_label'];

						$bookingpress_appointment_data[$k]['bookingpress_applied_coupon_code'] = $bookingpress_appointment_details['applied_coupon'];
						$bookingpress_appointment_data[$k]['bookingpress_coupon_discount_amt'] = $bookingpress_appointment_details['coupon_discount_amt'];
						$bookingpress_appointment_data[$k]['bookingpress_coupon_discount_amt_with_currency'] = $bookingpress_appointment_details['coupon_discount_amt_with_currency'];

						$bookingpress_appointment_data[$k]['bookingpress_final_total_amt'] = $bookingpress_appointment_details['final_total_amount'];
						$bookingpress_appointment_data[$k]['bookingpress_final_total_amt_with_currency'] = $bookingpress_appointment_details['final_total_amount_with_currency'];

						$bookingpress_appointment_data[$k]['bookingpress_is_cart'] = $bookingpress_appointment_details['is_cart'];

						$bookingpress_appointment_data[$k]['bookingpress_is_deposit_enable'] = $bookingpress_appointment_details['is_deposit_enable'];

						$bookingpress_appointment_data[$k]['bookingpress_extra_service_data'] = $bookingpress_appointment_details['extra_services_details'];

						$bookingpress_appointment_data[$k]['bookingpress_selected_extra_members'] = intval($bookingpress_appointment_details['selected_extra_members']);

						$bookingpress_appointment_data[$k]['bookingpress_staff_firstname'] = !empty($bookingpress_appointment_details['staffmember_details']['staffmember_firstname']) ? $bookingpress_appointment_details['staffmember_details']['staffmember_firstname'] : '';
						$bookingpress_appointment_data[$k]['bookingpress_staff_lastname'] = !empty($bookingpress_appointment_details['staffmember_details']['staffmember_lastname']) ? $bookingpress_appointment_details['staffmember_details']['staffmember_lastname'] : '';
						$bookingpress_appointment_data[$k]['bookingpress_staff_email_address'] = !empty($bookingpress_appointment_details['staffmember_details']['staffmember_email_address']) ? $bookingpress_appointment_details['staffmember_details']['staffmember_email_address'] : '';
						$bookingpress_appointment_data[$k]['bookingpress_staff_avatar_url'] = !empty($bookingpress_appointment_details['staffmember_details']['staffmember_avatar']) ? $bookingpress_appointment_details['staffmember_details']['staffmember_avatar'] : '';

						$bookingpress_appointment_data[$k]['bookingpress_customer_id'] = !empty($bookingpress_appointment_details['customer_details']['customer_id']) ? $bookingpress_appointment_details['customer_details']['customer_id'] : '';
						$bookingpress_appointment_data[$k]['bookingpress_customer_firstname'] = !empty($bookingpress_appointment_details['customer_details']['customer_firstname']) ? $bookingpress_appointment_details['customer_details']['customer_firstname'] : '';
						$bookingpress_appointment_data[$k]['bookingpress_customer_lastname'] = !empty($bookingpress_appointment_details['customer_details']['customer_lastname']) ? $bookingpress_appointment_details['customer_details']['customer_lastname'] : '';
						$bookingpress_appointment_data[$k]['bookingpress_customer_email_address'] = !empty($bookingpress_appointment_details['customer_details']['customer_email_address']) ? $bookingpress_appointment_details['customer_details']['customer_email_address'] : '';
						$bookingpress_appointment_data[$k]['bookingpress_customer_phone_no'] = !empty($bookingpress_appointment_details['customer_details']['customer_phone_no']) ? $bookingpress_appointment_details['customer_details']['customer_phone_no'] : '';

						//Get custom fields value
						$bookingpress_meta_value = $this->bookingpress_get_appointment_form_field_data($bookingpress_appointment_id);
						$bookingpress_meta_value = apply_filters('bookingpress_removed_repeater_data_in_custom_fields', $bookingpress_meta_value, $bookingpress_appointment_id);

						$bookingpress_appointment_data[$k]['bookingpress_guest_data'] = apply_filters('bookingpress_get_appointment_guest_data', $bookingpress_appointment_id);
						
						//$bookingpress_meta_value = !empty($bookingpress_custom_fields_values['bookingpress_appointment_meta_value']) ? json_decode($bookingpress_custom_fields_values['bookingpress_appointment_meta_value'], TRUE) : array();
						//$bookingpress_meta_value = !empty($bookingpress_meta_value['form_fields']) ? $bookingpress_meta_value['form_fields'] : array();
						
						$bookingpress_appointment_custom_meta_values = array();
						if(!empty($bookingpress_meta_value)){
							foreach($bookingpress_meta_value as $k4 => $v4) {

								$bookingpress_form_field_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_field_label,bookingpress_field_type,bookingpress_field_options,bookingpress_field_values FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_meta_key = %s AND bookingpress_field_type != %s AND bookingpress_field_type != %s AND bookingpress_field_type != %s AND bookingpress_field_type != %s", $k4, '2_col', '3_col', '4_col', 'repeater'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.																
								$bookingpress_field_label = !empty($bookingpress_form_field_data['bookingpress_field_label']) ? stripslashes_deep($bookingpress_form_field_data['bookingpress_field_label']) : '';
								if(!empty($bookingpress_field_label)){
									$bookingpress_field_type = $bookingpress_form_field_data['bookingpress_field_type'];

									$bookingpress_field_options = json_decode($bookingpress_form_field_data['bookingpress_field_options'],true);

									if( !empty( $bookingpress_field_options['visibility'] ) && 'hidden' == $bookingpress_field_options['visibility'] ){
										continue;
									}

									if( (isset($bookingpress_field_options['visibility']) && 'services' == $bookingpress_field_options['visibility']) && !empty( $bookingpress_field_options['selected_services'])){
										if( in_array( $v['service_id'], $bookingpress_field_options['selected_services']) ){
											if( !empty($bookingpress_field_type) && 'checkbox' == $bookingpress_field_type ){
												$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => is_array($v4) ? implode(',', $v4) : '' );
											} elseif(!empty($bookingpress_field_type) && !empty($v4) && 'date' == $bookingpress_field_type ) {
												$bookingpress_field_options = json_decode($bookingpress_form_field_data['bookingpress_field_options'],true);
												if(!empty($bookingpress_field_options['enable_timepicker']) && $bookingpress_field_options['enable_timepicker'] == 'true') {
													$default_date_time_format = $default_date_format.' '.$default_time_format;
													$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => date($default_date_time_format,strtotime($v4)));
												} else {
													$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => date($default_date_format,strtotime($v4)));
												}
											} else if( !empty( $bookingpress_field_type ) && 'file' == $bookingpress_field_type ) {

												$file_name_data = explode( '/', $v4 );
												$file_name = end( $file_name_data );
												
												$bookingpress_appointment_custom_meta_values[] = array(
													'label' => $bookingpress_field_label,
													'value' => '<a href="' . esc_url( $v4 ) . '" target="_blank">'.$file_name.'</a>'
												);

											} else {
												$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => $v4);
											}
										}
									} else {
										if( !empty($bookingpress_field_type) && 'checkbox' == $bookingpress_field_type ){
											$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => is_array($v4) ? implode(',', $v4) : '' );
										} elseif(!empty($bookingpress_field_type) && !empty($v4) && 'date' == $bookingpress_field_type ) {
											$bookingpress_field_options = json_decode($bookingpress_form_field_data['bookingpress_field_options'],true);
											if(!empty($bookingpress_field_options['enable_timepicker']) && $bookingpress_field_options['enable_timepicker'] == 'true') {
												$default_date_time_format = $default_date_format.' '.$default_time_format;
												$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => date($default_date_time_format,strtotime($v4)));
											} else {
												$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => date($default_date_format,strtotime($v4)));
											}
										} else if( !empty( $bookingpress_field_type ) && 'file' == $bookingpress_field_type ) {

											$file_name_data = explode( '/', $v4 );
											$file_name = end( $file_name_data );
											
											$bookingpress_appointment_custom_meta_values[] = array(
												'label' => $bookingpress_field_label,
												'value' => '<a href="' . esc_url( $v4 ) . '" target="_blank">'.$file_name.'</a>'
											);

										} else {
											$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => $v4);
										}
									}
								}
							}
						}
						$bookingpress_appointment_data[$k]['custom_fields_values'] = $bookingpress_appointment_custom_meta_values;						
					}
				}
			}
			return $bookingpress_appointment_data;
		}
		
		/**
		 * Function for modify appointment data variables
		 *
		 * @param  mixed $bookingpress_appointment_vue_data_fields
		 * @return void
		 */
		function bookingpress_modify_appointment_data_fields_func( $bookingpress_appointment_vue_data_fields ) {
			global $wpdb, $BookingPressPro, $bookingpress_pro_staff_members, $BookingPress, $bookingpress_service_extra, $bookingpress_bring_anyone_with_you, $tbl_bookingpress_staffmembers, $bookingpress_coupons, $tbl_bookingpress_form_fields, $tbl_bookingpress_customers, $bookingpress_global_options, $tbl_bookingpress_extra_services, $tbl_bookingpress_staffmembers_services, $bookingpress_pro_services;

			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			
			$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? $bookingpress_global_options_arr['bookingpress_staffmember_singular_name'] : esc_html_e('Staff Member', 'bookingpress-appointment-booking');

			$bookingpress_appointment_vue_data_fields['bulk_options'] = array(
				array(
					'value'        => 'bulk_action',
					'label'        => __( 'Bulk Action', 'bookingpress-appointment-booking' ),
					'bulk_actions' => array(
						array(
							'value' => 'bulk_action',
							'text' => __('Bulk Action', 'bookingpress-appointment-booking'),
						),
						array(
							'value' => 'delete',
							'text' => __( 'Delete', 'bookingpress-appointment-booking' ),
						),
					),
				),
				array(
					'value'        => 'change_status',
					'label'        => __( 'Change Status', 'bookingpress-appointment-booking' ),
					'bulk_actions' => $bookingpress_appointment_vue_data_fields['appointment_status'],
				),
			);

			$bookingpress_appointment_vue_data_fields['selected_staffmember'] = '';
			$bookingpress_appointment_vue_data_fields['rules']['selected_staffmember'] = array(
                array(
                    'required' => true,
                    'message'  => esc_html__('Please select', 'bookingpress-appointment-booking')." ".esc_html($bookingpress_singular_staffmember_name),
                    'trigger'  => 'change',
                ),
            );

			$bookingpress_appointment_vue_data_fields['is_timeslot_display'] = '1';

			$bookingpress_appointment_vue_data_fields['ExportAppointment']            = false;
			$bookingpress_appointment_vue_data_fields['is_export_button_loader']      = '0';
			$bookingpress_appointment_vue_data_fields['is_export_button_disabled']    = false;
			$bookingpress_appointment_vue_data_fields['is_mask_display']              = false;
			$bookingpress_appointment_vue_data_fields['export_appointment_top_pos']   = '270px';
			$bookingpress_appointment_vue_data_fields['export_appointment_right_pos'] = '80px';
			$bookingpress_appointment_vue_data_fields['export_appointment_left_pos']  = 'auto';
			$export_appointment_field_list = array(
				array(
					'name' => 'customer_full_name',
					'text' => __( 'Customer Full Name', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'customer_email_address',
					'text' => __( 'Customer Email Address', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'customer_phone_number',
					'text' => __( 'Customer Phone number', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'staff_member_full_name',
					'text' => __( 'Staff Member Full Name', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'service',
					'text' => __( 'Service', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'amount',
					'text' => __( 'Amount', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'start_time',
					'text' => __( 'Start Time', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'end_time',
					'text' => __( 'End Time', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'note',
					'text' => __( 'Note', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'appointment_status',
					'text' => __( 'Appointment Status', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'appointment_created_date',
					'text' => __( 'Created Date', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'appointment_no_of_person',
					'text' => __( 'No. of Person', 'bookingpress-appointment-booking' ),
				),
			);



			if ( ! $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ) {
				unset( $export_appointment_field_list[3] );
			}
			$bookingpress_appointment_vue_data_fields['export_checked_field'] = array( 'customer_full_name', 'customer_email_address', 'customer_phone_number', 'staff_member_full_name', 'service', 'amount', 'start_time', 'end_time', 'note', 'appointment_status', 'appointment_created_date','appointment_no_of_person');

			/* Get all front form custom fields for export start */
			$bookingpress_all_export_custom_fields_key = array();
			$bookingpress_all_export_custom_fields_label = array();
			$bookingpress_all_form_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_name NOT IN ('terms_and_conditions','email_address','phone_number','note') AND bookingpress_field_type NOT IN ('2_col','3_col','4_col','password') AND bookingpress_is_customer_field = %d ORDER BY bookingpress_field_position ASC", 0 ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm 
			if(!empty($bookingpress_all_form_fields)){
				$disable_fields = array('2_col','password','3_col','4_col');
				foreach($bookingpress_all_form_fields as $bookingpress_custom_field_single){
					if($bookingpress_custom_field_single['bookingpress_field_is_hide'] != 1 && $bookingpress_custom_field_single['bookingpress_form_field_name'] != 'terms_and_conditions' && !in_array($bookingpress_custom_field_single['bookingpress_field_type'],$disable_fields)){
						$bookingpress_export_field_key = $bookingpress_custom_field_single['bookingpress_form_field_id'];						
						$bookingpress_export_field_key = $bookingpress_custom_field_single['bookingpress_field_meta_key'];						
						if( $bookingpress_custom_field_single['bookingpress_field_is_default'] == 1 ){
							if( $bookingpress_custom_field_single['bookingpress_form_field_name'] == 'firstname'){
								$bookingpress_export_field_key = 'customer_firstname';		
							}
							if( $bookingpress_custom_field_single['bookingpress_form_field_name'] == 'lastname'){
								$bookingpress_export_field_key = 'customer_lastname';		
							}
							if( $bookingpress_custom_field_single['bookingpress_form_field_name'] == 'email_address'){
								$bookingpress_export_field_key = 'customer_email';		
							}
							if( $bookingpress_custom_field_single['bookingpress_form_field_name'] == 'note'){
								$bookingpress_export_field_key = 'appointment_note';		
							}
							if( $bookingpress_custom_field_single['bookingpress_form_field_name'] == 'phone_number'){
								$bookingpress_export_field_key = 'customer_phone';		
							}
							if( $bookingpress_custom_field_single['bookingpress_form_field_name'] == 'fullname'){
								$bookingpress_export_field_key = 'customer_name';		
							}
							if( $bookingpress_custom_field_single['bookingpress_form_field_name'] == 'username'){
								$bookingpress_export_field_key = 'customer_username';		
							}
							if( $bookingpress_custom_field_single['bookingpress_form_field_name'] == 'terms_and_conditions'){
								$bookingpress_export_field_key = 'appointment_terms_conditions';		
							}							
						}						
						if($bookingpress_custom_field_single['bookingpress_field_is_default'] == 0){
							$bookingpress_get_field_id_using_meta = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(bookingpress_form_field_id) as total_record FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_meta_key = %s ORDER BY bookingpress_form_field_id ASC", $bookingpress_custom_field_single['bookingpress_field_meta_key'] ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm 
							if(!empty($bookingpress_get_field_id_using_meta)){
								if(isset($bookingpress_get_field_id_using_meta['total_record']) && $bookingpress_get_field_id_using_meta['total_record'] > 1){
									$bookingpress_export_field_key = $bookingpress_custom_field_single['bookingpress_field_meta_key'];									
								}
							}
						}
						$export_appointment_field_list[] = array(
							'name' => $bookingpress_export_field_key,
							'text' => (isset($bookingpress_custom_field_single['bookingpress_field_label']))?$bookingpress_custom_field_single['bookingpress_field_label']:'',							
						);
						$bookingpress_all_export_custom_fields_key[] = $bookingpress_export_field_key;
						$bookingpress_appointment_vue_data_fields['export_checked_field'][] = $bookingpress_export_field_key;
						$bookingpress_all_export_custom_fields_label[$bookingpress_export_field_key] = (isset($bookingpress_custom_field_single['bookingpress_field_label']))?$bookingpress_custom_field_single['bookingpress_field_label']:'';
					}
				}
			}			

			$bookingpress_appointment_vue_data_fields['bookingpress_all_export_custom_fields_key'] = $bookingpress_all_export_custom_fields_key;
			$bookingpress_appointment_vue_data_fields['bookingpress_all_export_custom_fields_label'] = $bookingpress_all_export_custom_fields_label;
			/* Get all front form custom fields for export over */

			$export_appointment_field_list = apply_filters('modify_export_appointment_field_list', $export_appointment_field_list);
			$bookingpress_appointment_vue_data_fields['export_field_list'] = $export_appointment_field_list;
			$bookingpress_appointment_vue_data_fields['export_checked_field'] = apply_filters( 'modify_export_appointment_data_checked_fields_outside', $bookingpress_appointment_vue_data_fields['export_checked_field']);

			$bookingpress_appointment_vue_data_fields['default_export_checked_field'] = $bookingpress_appointment_vue_data_fields['export_checked_field'];

			$bookingpress_appointment_vue_data_fields['is_staffmember_activated'] = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();

			$bookingpress_appointment_vue_data_fields['appointment_details_drawer'] = false;

			$bookingpress_appointment_vue_data_fields['appointment_details_drawer_direction'] = 'rtl';									

			$bookigpress_time_format_for_booking_form =  $BookingPress->bookingpress_get_customize_settings('bookigpress_time_format_for_booking_form','booking_form');
			$bookigpress_time_format_for_booking_form =  !empty($bookigpress_time_format_for_booking_form) ? $bookigpress_time_format_for_booking_form : '2';
			$bookingpress_appointment_vue_data_fields['bookigpress_time_format_for_booking_form'] = $bookigpress_time_format_for_booking_form;

			// Add appointment data variables
			$bookingpress_appointment_vue_data_fields['bookingpress_extras_popover_modal'] = false;
			$bookingpress_appointment_vue_data_fields['bookingpress_service_extras'] = array();
			
			$bookingpress_appointment_vue_data_fields['is_tax_enable'] = (is_plugin_active('bookingpress-tax/bookingpress-tax.php'))?1:0;
			$bookingpress_appointment_vue_data_fields['is_custom_service_duration'] = (is_plugin_active('bookingpress-custom-service-duration/bookingpress-custom-service-duration.php'))?1:0;

			$bookingpress_appointment_vue_data_fields['is_extras_enable'] = $bookingpress_service_extra->bookingpress_check_service_extra_module_activation();
			$bookingpress_appointment_vue_data_fields['is_staff_enable'] = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_appointment_vue_data_fields['is_bring_anyone_with_you_enable'] = $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation();
			$bookingpress_appointment_vue_data_fields['is_coupon_enable'] = $bookingpress_coupons->bookingpress_check_coupon_module_activation();
			$bookingpress_appointment_vue_data_fields['bookingpress_allow_coupon_code'] = $bookingpress_coupons->bookingpress_check_coupon_module_activation();
			
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['bookingpress_staffmembers_lists'] = array();
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['bookingpress_bring_anyone_max_capacity'] = 0;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['bookingpress_bring_anyone_min_capacity'] = 0;

			$bookingpress_appointment_vue_data_fields['appointment_formdata']['selected_extra_services'] = array();
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['selected_extra_services_ids'] = '';
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['selected_staffmember'] = '';
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['selected_bring_members'] = 1;

			$bookingpress_appointment_vue_data_fields['appointment_formdata']['subtotal'] = 0;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['subtotal_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['extras_total'] = 0;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['extras_total_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);
			
			
			$tax_percentage = $BookingPress->bookingpress_get_settings( 'tax_percentage', 'payment_setting' );			
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['tax_percentage'] = $tax_percentage;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['tax'] = 0;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['tax_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);
			$bookingpress_price_setting_display_option = $BookingPress->bookingpress_get_settings('price_settings_and_display', 'payment_setting');
            $bookingpress_appointment_vue_data_fields['appointment_formdata']['tax_price_display_options'] = $bookingpress_price_setting_display_option;

            $bookingpress_tax_order_summary = $BookingPress->bookingpress_get_settings('display_tax_order_summary', 'payment_setting');
            $bookingpress_appointment_vue_data_fields['appointment_formdata']['display_tax_order_summary'] = $bookingpress_tax_order_summary;

            $bookingpress_tax_order_summary_text = $BookingPress->bookingpress_get_settings('included_tax_label', 'payment_setting');
            $bookingpress_appointment_vue_data_fields['appointment_formdata']['included_tax_label'] = stripslashes_deep($bookingpress_tax_order_summary_text);

			$bookingpress_appointment_vue_data_fields['appointment_formdata']['tax_percentage_org'] = $tax_percentage;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['tax_price_display_options_org'] = $bookingpress_price_setting_display_option;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['display_tax_order_summary_org'] = $bookingpress_tax_order_summary;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['included_tax_label_org'] = $bookingpress_tax_order_summary_text;

			$bookingpress_appointment_vue_data_fields['appointment_formdata']['applied_coupon_code'] = '';
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['applied_coupon_details'] = array();
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['coupon_discounted_amount'] = 0;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['coupon_discounted_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['total_amount'] = 0;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['total_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);

			$bookingpress_appointment_vue_data_fields['appointment_formdata']['mark_as_paid'] = false;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['complete_payment_url_selection'] = 'do_nothing';
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['complete_payment_url_selected_method'] = array();

			$bookingpress_appointment_vue_data_fields['coupon_apply_loader'] = 0;
			$bookingpress_appointment_vue_data_fields['coupon_code_msg'] = '';
			$bookingpress_appointment_vue_data_fields['bpa_coupon_apply_disabled'] = 0;
			$bookingpress_appointment_vue_data_fields['coupon_applied_status'] = '';

			//Get custom fields
			$bookingpress_form_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_is_default = %d AND bookingpress_is_customer_field = %d ORDER BY bookingpress_field_position ASC", 0, 0 ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm

			$bookingpress_listing_fields_value = $bookingpress_appointment_meta_fields_value = array();
			if(!empty($bookingpress_form_fields)){
				foreach($bookingpress_form_fields as $k3 => $v3){
					$bookingpress_form_fields[$k3]['bookingpress_field_error_message']= stripslashes_deep($v3['bookingpress_field_error_message']);
					$bookingpress_form_fields[$k3]['bookingpress_field_label'] = stripslashes_deep($v3['bookingpress_field_label']);
					$bookingpress_form_fields[$k3]['bookingpress_field_placeholder'] = stripslashes_deep($v3['bookingpress_field_placeholder']);
					$bookingpress_field_meta_key = $v3['bookingpress_field_meta_key'];
					$bookingpress_field_options = json_decode($v3['bookingpress_field_options'], TRUE);
					$bookingpress_form_fields[$k3]['bookingpress_field_options'] = $bookingpress_field_options;
					if($v3['bookingpress_field_type'] == "checkbox"){
						$bookingpress_field_values = json_decode($v3['bookingpress_field_values'], TRUE);


						$temp_form_fields_data = array();
						$fmeta_key = $bookingpress_field_meta_key;

						foreach( $bookingpress_field_values as $k4 => $v4 ){
							$bookingpress_form_fields[$k3][ $fmeta_key] [ $k4 ] = '';	
						}

						$bookingpress_appointment_meta_fields_value[$fmeta_key] = array();
						
						$bookingpress_form_fields[$k3]['selected_services'] = (isset($bookingpress_field_options['selected_services']))?$bookingpress_field_options['selected_services']:'';
					}else{
						$bookingpress_form_fields[$k3]['selected_services'] = (isset($bookingpress_field_options['selected_services']))?$bookingpress_field_options['selected_services']:'';
						if( $v3['bookingpress_field_type'] != 'password'){
							
							$bookingpress_appointment_meta_fields_value[$bookingpress_field_meta_key] = '';
							$bookingpress_listing_fields_value[$bookingpress_field_meta_key] = array(
								'label' => $v3['bookingpress_field_label'],
								'value' => '',
							);
						}
					}
				}
			}

			if(!empty($bookingpress_form_fields)){
				foreach($bookingpress_form_fields as $k4 => $v4){									
					if(($v4['bookingpress_form_field_name'] == "Repeater") || ($v4['bookingpress_form_field_name'] == "2 Col") || ($v4['bookingpress_form_field_name'] == "3 Col") || ($v4['bookingpress_form_field_name'] == "4 Col") ){
						unset($bookingpress_form_fields[$k4]);
					}
				}

				$bookingpress_form_fields = array_values($bookingpress_form_fields);
			}

			if( !empty( $bookingpress_form_fields ) ) {
				$bookingpress_temp_form_fields = [];
				$n5 = 0;
				foreach( $bookingpress_form_fields as $k5 => $v5 ){

					if( 'file' == $v5['bookingpress_field_type'] ){
						$action_url = admin_url('admin-ajax.php');
						$action_data = array(
							'action' => 'bpa_front_file_upload',
							'_wpnonce' => wp_create_nonce( 'bpa_file_upload_' . $v5['bookingpress_field_meta_key'] ),
							'field_key' => $v5['bookingpress_field_meta_key']
						);
						$v5['bpa_action_url'] = $action_url;
						$v5['bpa_ref_name'] = str_replace('_', '', $v5['bookingpress_field_meta_key']);
						$action_data['bpa_ref'] =$v5['bpa_ref_name'];
						$v5['bpa_file_list'] = array();
						$v5['bpa_action_data'] = $action_data;
						$action_data['bpa_accept_files'] = !empty( $v5['bookingpress_field_options']['allowed_file_ext'] ) ?  base64_encode( $v5['bookingpress_field_options']['allowed_file_ext'] ) : '';
					}

					if( ( ( $n5 + 1 ) % 3 ) == 0 ){
						$v5['is_separator'] = false;
						$bookingpress_temp_form_fields[] = $v5;
						$bookingpress_temp_form_fields[] = array(
							'is_separator' => true
						);
					} else {
						$v5['is_separator'] = false;
						$bookingpress_temp_form_fields[] = $v5;
					}
					$n5++;
				}
				$bookingpress_form_fields = $bookingpress_temp_form_fields;
			}
			
			$bookingpress_appointment_vue_data_fields['bookingpress_form_fields'] = $bookingpress_form_fields;
			$bookingpress_appointment_vue_data_fields['appointment_formdata']['bookingpress_appointment_meta_fields_value'] = $bookingpress_appointment_meta_fields_value;
			$bookingpress_appointment_vue_data_fields['bookingpress_listing_fields_value'] = $bookingpress_listing_fields_value;

			//Add Customer Data Variables
			$bookingpress_appointment_vue_data_fields['open_customer_modal'] = false;
			$bookingpress_options = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_country_list = $bookingpress_options['country_lists'];
			$bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');
			
			$bookingpress_appointment_vue_data_fields['customer'] = array(
				'avatar_url' => '',
				'avatar_name' => '',
				'avatar_list' => array(),
				'wp_user' => null,
				'firstname' => '',
				'lastname' => '',
				'email' => '',
				'phone' => '',
				'customer_phone_country' => $bookingpress_phone_country_option,
				'customer_phone_dial_code' => '',
				'note' => '',
				'update_id' => 0,
				'_wpnonce' => '',
				'password' => '',
			);

			$bpa_customer_form_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_is_customer_field = %d ORDER BY bookingpress_field_position ASC", 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
			
            $bpa_customer_fields = array();
            if( !empty( $bpa_customer_form_fields ) ){
                foreach( $bpa_customer_form_fields as $x => $cs_form_fields ){
                    //$bpa_customer_fields['field_id'] = $cs_form_fields['']   
                    $bpa_customer_fields[ $x ] = $cs_form_fields;
                    $bpa_customer_fields[ $x ]['bookingpress_field_values'] = json_decode( $cs_form_fields['bookingpress_field_values'], true );
                    $bpa_customer_fields[ $x ]['bookingpress_field_options'] = json_decode( $cs_form_fields['bookingpress_field_options'], true );
                    $bpa_customer_fields[ $x ]['bookingpress_field_key'] = '';//$cs_form_fields['bookingpress_field_meta_key'];
                    if( 'checkbox' == $cs_form_fields['bookingpress_field_type'] ){
                        $bpa_customer_fields[ $x ]['bookingpress_field_key'] = array();
                        foreach( $bpa_customer_fields[ $x ]['bookingpress_field_values'] as $chk_key => $chk_val ){
                            //$bpa_customer_fields[ $x ]['bookingpress_field_key'][ $chk_key ] = false;
							$bookingpress_appointment_vue_data_fields['customer']['bpa_customer_field'][ $cs_form_fields['bookingpress_field_meta_key'] . '_' . $chk_key ] = false;
                        }
                    } else {
						$bookingpress_appointment_vue_data_fields['customer']['bpa_customer_field'][$cs_form_fields['bookingpress_field_meta_key']] = $bpa_customer_fields[ $x ]['bookingpress_field_key'];
					}
                }
            }
            $bookingpress_appointment_vue_data_fields['bookingpress_customer_fields'] = $bpa_customer_fields;

			$bookingpress_custom_fields = $bookingpress_appointment_vue_data_fields['bookingpress_form_fields'];
			$bookingpress_custom_fields_validation_arr = array();
			if(!empty($bookingpress_custom_fields)){
				foreach($bookingpress_custom_fields as $custom_field_key => $custom_field_val){
					
					if(isset($custom_field_val['bookingpress_field_is_default']) && $custom_field_val['bookingpress_field_is_default'] == 0 ) {

						$bookingpress_field_meta_key = $custom_field_val['bookingpress_field_meta_key'];
						
						if(isset($custom_field_val['bookingpress_field_required']) && $custom_field_val['bookingpress_field_required'] == 1) {
							$bookingpress_field_err_msg = stripslashes_deep($custom_field_val['bookingpress_field_error_message']);						
							$bookingpress_field_err_msg = empty($bookingpress_field_err_msg) && !empty($custom_field_val['bookingpress_field_label']) ? stripslashes_deep($custom_field_val['bookingpress_field_label']).' '.__('is required','bookingpress-appointment-booking') : $bookingpress_field_err_msg;
							$bookingpress_custom_fields_validation_arr[$bookingpress_field_meta_key][] = array(
								'required' => 1,
								'message' => $bookingpress_field_err_msg,
								'trigger' => 'change'
							);					
						}
											
						if(!empty($custom_field_val['bookingpress_field_options']['minimum'])) {
							$bookingpress_custom_fields_validation_arr[ $bookingpress_field_meta_key][] = array( 
								'min' => intval($custom_field_val['bookingpress_field_options']['minimum']),
								'message'  => __('Minimum','bookingpress-appointment-booking').' '.$custom_field_val['bookingpress_field_options']['minimum'].' '.__('character required','bookingpress-appointment-booking'),
								'trigger'  => 'blur',
							);
						}
						if(!empty($custom_field_val['bookingpress_field_options']['maximum'])) {
							$bookingpress_custom_fields_validation_arr[$bookingpress_field_meta_key][] = array( 
								'max' => intval($custom_field_val['bookingpress_field_options']['maximum']),
								'message'  => __('Maximum','bookingpress-appointment-booking').' '.$custom_field_val['bookingpress_field_options']['maximum'].' '.__('character allowed','bookingpress-appointment-booking'),
								'trigger'  => 'blur',
							);
						}
					}	
				}
				
			}
			
			$bookingpress_appointment_vue_data_fields['custom_field_rules'] = $bookingpress_custom_fields_validation_arr;

			$bookingpress_appointment_vue_data_fields['phone_countries_details'] = json_decode($bookingpress_country_list);
			$bookingpress_appointment_vue_data_fields['loading'] = false;

			$bookingpress_appointment_vue_data_fields['customer_detail_save'] = false;
			$bookingpress_appointment_vue_data_fields['wpUsersList'] = array();
			$bookingpress_appointment_vue_data_fields['savebtnloading'] = false;
			$bookingpress_appointment_vue_data_fields['customer_rules'] = array(
				'firstname' => array(
					array(
						'required' => true,
						'message'  => esc_html__('Please enter firstname', 'bookingpress-appointment-booking'),
						'trigger'  => 'blur',
					),
				),
				'lastname'  => array(
					array(
						'required' => true,
						'message'  => esc_html__('Please enter lastname', 'bookingpress-appointment-booking'),
						'trigger'  => 'blur',
					),
				),
				'email'     => array(
					array(
						'required' => true,
						'message'  => esc_html__('Please enter email address', 'bookingpress-appointment-booking'),
						'trigger'  => 'blur',
					),
					array(
						'type'    => 'email',
						'message' => esc_html__('Please enter valid email address', 'bookingpress-appointment-booking'),
						'trigger' => 'blur',
					),
				),
			);

			$bookingpress_appointment_vue_data_fields['cusShowFileList'] = false;
			$bookingpress_appointment_vue_data_fields['is_display_loader'] = '0';
			$bookingpress_appointment_vue_data_fields['is_disabled'] = false;
			$bookingpress_appointment_vue_data_fields['is_display_save_loader'] = '0';
			$bookingpress_appointment_vue_data_fields['is_refund_btn_disabled'] = false;
			$bookingpress_appointment_vue_data_fields['is_display_refund_loader'] = '0';
			
			$bookingpress_appointment_vue_data_fields['bookingpress_tel_input_props'] = array(
				'defaultCountry' => $bookingpress_phone_country_option,
				'validCharactersOnly' => true,
				'inputOptions' => array(
					'placeholder' => '',
				)
			);

			if ( ! empty( $bookingpress_phone_country_option ) && $bookingpress_phone_country_option == 'auto_detect' ) {
				// Get visitors ip address
				$bookingpress_ip_address = $BookingPressPro->boookingpress_get_visitor_ip();
				try {
					$bookingpress_country_reader = new Reader( BOOKINGPRESS_PRO_LIBRARY_DIR . '/geoip/inc/GeoLite2-Country.mmdb' );
					$bookingpress_country_record = $bookingpress_country_reader->country( $bookingpress_ip_address );
					if ( ! empty( $bookingpress_country_record->country ) ) {
						$bookingpress_country_name     = $bookingpress_country_record->country->name;
						$bookingpress_country_iso_code = $bookingpress_country_record->country->isoCode;
						$bookingpress_appointment_vue_data_fields['bookingpress_tel_input_props']['defaultCountry'] = $bookingpress_country_iso_code;
					}
				} catch ( Exception $e ) {
					$bookingpress_error_message = $e->getMessage();
				}
			}
			$bookingpress_appointment_vue_data_fields['boookingpress_loading'] = false;
			$bookingpress_appointment_vue_data_fields['wordpress_user_id'] = '';

			$bookingpress_appointment_vue_data_fields['bookingpress_payment_status'] = 0;		


			//PreLoaded Data of Service, Extras and Staff Members
			$bookingpress_loaded_services = $bookingpress_appointment_vue_data_fields['appointment_services_list'];
			$bookingpress_service_extras = $bookingpress_service_staffmembers = array();
			
			if(!empty($bookingpress_loaded_services)){
				foreach($bookingpress_loaded_services as $service_key => $service_val){
					$category_services = !empty($service_val['category_services']) ? $service_val['category_services'] : array();
					if(!empty($category_services)){
						foreach($category_services as $ser_key => $ser_val){
							$service_id = intval($ser_val['service_id']);
							if(!empty($service_id)){

								/** service max capacity */
								$service_max_capacity = $bookingpress_pro_services->bookingpress_get_service_max_capacity($service_id);
								
								if( empty( $service_max_capacity ) ){
									$service_max_capacity = 1;
								}

								/** service min capacity */
								if( $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation() == true ){
									
									$service_min_capacity = $bookingpress_pro_services->bookingpress_get_service_min_capacity($service_id);
								
									if( empty( $service_min_capacity ) ){
										$service_min_capacity = 1;
									}
									
									$bookingpress_loaded_services[ $service_key ]['category_services'][ $ser_key ]['service_min_capacity'] = $service_min_capacity;
								}

								$bookingpress_loaded_services[ $service_key ]['category_services'][ $ser_key ]['service_max_capacity'] = $service_max_capacity;

								$bookingpress_extra_services_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_service_id = %d", $service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is a table name. false alarm

								if(!empty($bookingpress_extra_services_data)){
									foreach($bookingpress_extra_services_data as $extra_key => $extra_val){
										$bookingpress_extra_service_price_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($extra_val['bookingpress_extra_service_price']);

										$bookingpress_extra_services_data[$extra_key]['bookingpress_extra_service_price_with_currency'] = $bookingpress_extra_service_price_with_currency;
										$bookingpress_extra_services_data[$extra_key]['bookingpress_is_display_description'] = 0;

										$bookingpress_extra_services_data[$extra_key]['bookingpress_selected_qty'] = 1;
										$bookingpress_extra_services_data[$extra_key]['bookingpress_is_selected'] = false;

										$bookingpress_appointment_vue_data_fields['appointment_formdata']['selected_extra_services'][$extra_val['bookingpress_extra_services_id']] = $bookingpress_extra_services_data[$extra_key];
									}
								}

								$bookingpress_service_extras[$service_id] = $bookingpress_extra_services_data;


								//Get service staff members details
								$bookingpress_staffmembers_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_service_id = %d", $service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
								if(!empty($bookingpress_staffmembers_details)){
									foreach($bookingpress_staffmembers_details as $bookingpress_staff_key => $bookingpress_staff_val){
										$bookingpress_staffmember_id = intval($bookingpress_staff_val['bookingpress_staffmember_id']);

										//Get staff profile details
										$bookingpress_staff_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $bookingpress_staffmember_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is table name.

										if( !empty( $bookingpress_staff_details ) && $bookingpress_staff_details['bookingpress_staffmember_status'] == 1){
											$bookingpress_staff_price_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_staff_val['bookingpress_service_price']);
											$bookingpress_staffmembers_details[$bookingpress_staff_key]['staff_price_with_currency'] = $bookingpress_staff_price_with_currency;

											$bookingpress_staffmembers_details[$bookingpress_staff_key]['profile_details'] = $bookingpress_staff_details;
										}else{
											continue;
										}
									}
								}

								$bookingpress_service_staffmembers[$service_id] = $bookingpress_staffmembers_details;
							}
						}
					}
				}
			}

			$bookingpress_appointment_vue_data_fields['appointment_services_list'] = $bookingpress_loaded_services;
			$bookingpress_appointment_vue_data_fields['bookingpress_loaded_extras'] = $bookingpress_service_extras;
			$bookingpress_appointment_vue_data_fields['bookingpress_loaded_staff'] = $bookingpress_service_staffmembers;
			
			$bookingpress_appointment_vue_data_fields['bookingpress_is_extra_enable'] = $bookingpress_service_extra->bookingpress_check_service_extra_module_activation();

			$bookingpress_appointment_vue_data_fields['share_url_form']['selected_staff_id'] = '';
			$bookingpress_appointment_vue_data_fields['share_url_form']['is_staff_login'] = 0;

			if(is_user_logged_in() && !current_user_can('administrator')){
				$bookingpress_logged_in_user_id = get_current_user_id();
				if(!empty($bookingpress_logged_in_user_id)){
					foreach($bookingpress_service_staffmembers as $service_staff_key => $service_staff_val){
						foreach($service_staff_val as $service_staff_tmp_key => $service_staff_tmp_val){
							if(!empty($service_staff_tmp_val['profile_details']) && ($service_staff_tmp_val['profile_details']['bookingpress_wpuser_id'] == $bookingpress_logged_in_user_id ) ){
								$bookingpress_appointment_vue_data_fields['share_url_form']['selected_staff_id'] = intval($service_staff_tmp_val['profile_details']['bookingpress_staffmember_id']);
								$bookingpress_appointment_vue_data_fields['share_url_form']['is_staff_login'] = 1;
								break;
							}
						}

						if(!empty($bookingpress_appointment_vue_data_fields['share_url_form']['selected_staff_id'])){
							break;
						}
					}
				}
			}

			$bookingpress_appointment_vue_data_fields['share_url_form']['selected_extras'] = array();
			$bookingpress_appointment_vue_data_fields['share_url_form']['selected_guests'] = 0;
			$bookingpress_appointment_vue_data_fields['share_url_form']['selected_guests_max_capacity'] = 0;

			$bookingpress_appointment_vue_data_fields['share_url_load_more'] = true;
			$bookingpress_appointment_vue_data_fields['share_url_load_less'] = false;

			$bookingpress_appointment_vue_data_fields['share_url_rules']['selected_staff_id'] = array(
				array(
					'required' => true,
					'message'  => __('Please select staffmember', 'bookingpress-appointment-booking'),
					'trigger'  => 'change',
				),
			);

			$bookingpress_manage_appointment = $bookingpress_delete_appointment = $bookingpress_export_appointments = $bookingpress_payments = $bookingpress_edit_customers = 0;
			if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_appointments' ) ) {
				$bookingpress_manage_appointment = 1;
			}
			if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_delete_appointments' ) ) {
				$bookingpress_delete_appointment = 1;
			}
			if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_export_appointments' ) ) {
				$bookingpress_export_appointments = 1;
			}
			if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_payments' ) ) {
				$bookingpress_payments = 1;
			}
			if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_customers' ) ) {
				$bookingpress_edit_customers = 1;
			}

			$bookingpress_appointment_vue_data_fields['bookingpress_manage_appointment'] = $bookingpress_manage_appointment;
			$bookingpress_appointment_vue_data_fields['bookingpress_delete_appointment'] = $bookingpress_delete_appointment;
			$bookingpress_appointment_vue_data_fields['bookingpress_export_appointments'] = $bookingpress_export_appointments;
			$bookingpress_appointment_vue_data_fields['bookingpress_payments'] = $bookingpress_payments;
			$bookingpress_appointment_vue_data_fields['bookingpress_edit_customers'] = $bookingpress_edit_customers;
			$bookingpress_appointment_vue_data_fields['refund_confirm_modal'] = false;
			$bookingpress_appointment_vue_data_fields['refund_confirm_form']['refund_type'] = 'full';
			$bookingpress_appointment_vue_data_fields['refund_confirm_form']['refund_reason'] = '';
			$bookingpress_appointment_vue_data_fields['refund_confirm_form']['allow_refund'] = true;
			$bookingpress_appointment_vue_data_fields['refund_confirm_form']['refund_amount'] = '';
			$bookingpress_appointment_vue_data_fields['refund_confirm_form']['allow_partial_refund'] = 0;
			$bookingpress_appointment_vue_data_fields['rules_refund_confirm_form'] = array();
			
			$bookingpress_currency_separator = $BookingPress->bookingpress_get_settings('price_separator', 'payment_setting');
			$bookingpress_appointment_vue_data_fields['bookingpress_currency_separator'] = $bookingpress_currency_separator;			
			$bookingpress_decimal_points = $BookingPress->bookingpress_get_settings('price_number_of_decimals', 'payment_setting');
			$bookingpress_decimal_points = intval($bookingpress_decimal_points);
			$bookingpress_appointment_vue_data_fields['bookingpress_decimal_points'] = $bookingpress_decimal_points;

            $bookingpress_currency_name = $BookingPress->bookingpress_get_settings('payment_default_currency', 'payment_setting');
            $bookingpress_appointment_vue_data_fields['bookingpress_currency_name'] = $bookingpress_currency_name;			
            $bookingpress_appointment_vue_data_fields['bookingpress_currency_symbol'] = $BookingPress->bookingpress_get_currency_symbol($bookingpress_currency_name);

			$bookingpress_appointment_vue_data_fields['bookingpress_currency_name_org'] = $bookingpress_currency_name;

            $bookingpress_price_symbol_position = $BookingPress->bookingpress_get_settings('price_symbol_position', 'payment_setting');
            $bookingpress_appointment_vue_data_fields['bookingpress_currency_symbol_position'] = $bookingpress_price_symbol_position;


			return $bookingpress_appointment_vue_data_fields;
		}
		
		/**
		 * Function for modify appointment view file path
		 *
		 * @param  mixed $bookingpress_appointment_view_path
		 * @return void
		 */
		function bookingpress_modify_appointment_file_path_func( $bookingpress_appointment_view_path ) {
			$bookingpress_appointment_view_path = BOOKINGPRESS_PRO_VIEWS_DIR . '/appointment/manage_appointment.php';
			return $bookingpress_appointment_view_path;
		}
		
		/**
		 * Function for appointment bulk actions
		 *
		 * @return void
		 */
		function bookingpress_appointment_dynamic_bulk_action_func() {
			?>	
				var appointment_logs_bulk_action = {
					action:'bookingpress_pro_bulk_appointment_logs_action',
					appointment_ids: this.multipleSelection,
					bulk_action: this.bulk_action,
					_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
				}
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_logs_bulk_action ) )
				.then(function(response){					
					vm2.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
					});			
					vm2.loadAppointments();
					vm2.multipleSelection = [];
					vm2.totalItems = vm2.items.length
				}).catch(function(error){
					console.log(error);
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
					});
				});			
			<?php
		}
		
		/**
		 * Callback function of bulk appointment action
		 *
		 * @return void
		 */
		function bookingpress_pro_bulk_appointment_logs_action_func() {
			global $BookingPress,$bookingpress_dashboard;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'bulk_appointment_actions', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_appointment_status = ! empty( $_POST['bulk_action'] ) ? sanitize_text_field( $_POST['bulk_action'] ) : ''; // phpcs:ignore
			if ( $bookingpress_appointment_status && in_array( $bookingpress_appointment_status, array( '2', '1', '3','4','5','6' ) ) ) {
				$appointment_ids = ! empty( $_POST['appointment_ids'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_ids'] ) : array(); // phpcs:ignore
				if ( ! empty( $appointment_ids ) ) {
					foreach ( $appointment_ids as $appointment_key => $appointment_val ) {
						if ( is_array( $appointment_val ) ) {
							$appointment_val = $appointment_val['appointment_id'];
						}
						if ( ! empty( $appointment_val ) ) {
							$return = $bookingpress_dashboard->bookingpress_change_upcoming_appointment_status( $appointment_val, $bookingpress_appointment_status );
						}
					}
					if ( $return ) {
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( 'Appointment status has been change successfully.', 'bookingpress-appointment-booking' );
					}
				}
			}
			echo wp_json_encode( $response );
			exit;
		}
		
		/**
		 * Callback function of export appointment data
		 *
		 * @return void
		 */
		function bookingpress_export_appointment_data_func() {
			global $BookingPress,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_customers,$wpdb,$BookingPressPro,$bookingpress_pro_staff_members,$bookingpress_global_options,$tbl_bookingpress_appointment_meta;
			$response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'export_appointment_details', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			$bookingpress_export_field       = ! empty( $_REQUEST['export_field'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['export_field'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
			$bookingpress_search_data        = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function			
			$bookingpress_custom_field_export_key       = ! empty( $_REQUEST['custom_field_export_key'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['custom_field_export_key'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function			
			$bookingpress_custom_field_export_label       = ! empty( $_REQUEST['custom_field_export_label'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['custom_field_export_label'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function	

			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			$default_date_time_format        = $bookingpress_global_options_arr['wp_default_date_format'] . ' ' . $bookingpress_global_options_arr['wp_default_time_format'];
			
			$bookingpress_export_custom_fields = array();
			if(!empty($bookingpress_export_field) && !empty($bookingpress_custom_field_export_key)){
				$bookingpress_export_custom_fields = array_intersect($bookingpress_export_field,$bookingpress_custom_field_export_key);
			}
			$bookingpress_search_query_where = 'WHERE 1=1 ';
			if ( ! empty( $bookingpress_export_field ) ) {
				if ( ! empty( $bookingpress_search_data ) ) {
					if ( ! empty( $bookingpress_search_data['search_appointment'] ) ) {
						$bookingpress_search_string = $bookingpress_search_data['search_appointment'];
						$bookingpress_search_result = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_customer_id FROM {$tbl_bookingpress_customers} WHERE bookingpress_user_firstname LIKE %s OR bookingpress_user_lastname LIKE %s OR bookingpress_user_login LIKE %s AND (bookingpress_user_type = 1 OR bookingpress_user_type = 2)", '%' . $bookingpress_search_string . '%', '%' . $bookingpress_search_string . '%', '%' . $bookingpress_search_string . '%' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm
						if ( ! empty( $bookingpress_search_result ) ) {
							$bookingpress_customer_ids = array();
							foreach ( $bookingpress_search_result as $item ) {
								$bookingpress_customer_ids[] = $item['bookingpress_customer_id'];
							}
							$bookingpress_search_user_id      = implode( ',', $bookingpress_customer_ids );
							$bookingpress_search_query_where .= "AND (bookingpress_customer_id IN ({$bookingpress_search_user_id}) OR bookingpress_staff_member_id IN ({$bookingpress_search_user_id}))";
						} else {
							$bookingpress_search_query_where .= "AND (bookingpress_service_name LIKE '%{$bookingpress_search_string}%')";
						}
					}
					if ( ! empty( $bookingpress_search_data['selected_date_range'] ) ) {
						$bookingpress_search_date         = $bookingpress_search_data['selected_date_range'];
						$start_date                       = date( 'Y-m-d', strtotime( $bookingpress_search_date[0] ) );
						$end_date                         = date( 'Y-m-d', strtotime( $bookingpress_search_date[1] ) );
						$bookingpress_search_query_where .= "AND (bookingpress_appointment_date BETWEEN '{$start_date}' AND '{$end_date}')";
					}
					if ( ! empty( $bookingpress_search_data['customer_name'] ) ) {
						$bookingpress_search_name         = $bookingpress_search_data['customer_name'];
						$bookingpress_search_customer_id  = implode( ',', $bookingpress_search_name );
						$bookingpress_search_query_where .= "AND (bookingpress_customer_id IN ({$bookingpress_search_customer_id}))";
					}
					if ( ! empty( $bookingpress_search_data['service_name'] ) ) {
						$bookingpress_search_name         = $bookingpress_search_data['service_name'];
						$bookingpress_search_service_id   = implode( ',', $bookingpress_search_name );
						$bookingpress_search_query_where .= "AND (bookingpress_service_id IN ({$bookingpress_search_service_id}))";
					}
					if ( ! empty( $bookingpress_search_data['appointment_status'] && $bookingpress_search_data['appointment_status'] != 'all' ) ) {
						$bookingpress_search_name         = $bookingpress_search_data['appointment_status'];
						$bookingpress_search_query_where .= "AND (bookingpress_appointment_status = '{$bookingpress_search_name}')";
					}
					if ( ! empty( $bookingpress_search_data['appointment_status'] && $bookingpress_search_data['appointment_status'] != 'all' ) ) {
						$bookingpress_search_name         = $bookingpress_search_data['appointment_status'];
						$bookingpress_search_query_where .= "AND (bookingpress_appointment_status = '{$bookingpress_search_name}')";
					}
					if ( ! empty( $bookingpress_search_data['staff_member_name'] ) ) {
						$bookingpress_search_name            = $bookingpress_search_data['staff_member_name'];
						$bookingpress_search_staff_member_id = implode( ',', $bookingpress_search_name );
						$bookingpress_search_query_where    .= " AND (bookingpress_staff_member_id IN ({$bookingpress_search_staff_member_id}))";
					}
					if(!empty( $bookingpress_search_data['search_appointment_id'])) {
						$bookingpress_search_id = $bookingpress_search_data['search_appointment_id'];
						$bookingpress_search_query_where .= "AND (bookingpress_booking_id = '{$bookingpress_search_id}')";
						
					}
				}
				$bookingpress_search_query_where = apply_filters( 'bookingpress_export_appointment_data_filter', $bookingpress_search_query_where );
				$bookingpress_custom_field_key_value = array();	
				$total_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} {$bookingpress_search_query_where} ORDER BY bookingpress_appointment_booking_id DESC" ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
				$appointments       = array();
				if ( ! empty( $total_appointments ) ) {
					$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
					$bookingpress_date_time_format   = $bookingpress_global_options_arr['wp_default_date_format'] . ' ' . $bookingpress_global_options_arr['wp_default_time_format'];
					$bookingpress_appointment_status_arr = $bookingpress_global_options_arr['appointment_status'];
					foreach ( $total_appointments as $get_appointment ) {
						$appointment    = array();
						$appointment_id = intval( $get_appointment['bookingpress_appointment_booking_id'] );
						if ( in_array( 'customer_full_name', $bookingpress_export_field ) ) {
							$customer_name                     =  !empty($get_appointment['bookingpress_customer_name']) ? stripslashes_deep($get_appointment['bookingpress_customer_name']) : stripslashes_deep($get_appointment['bookingpress_customer_firstname']).' '.stripslashes_deep($get_appointment['bookingpress_customer_lastname']);
							$appointment['Customer Full Name'] = ! empty( $customer_name ) ? '"' . $customer_name . '"' : '-';
						}

						if ( in_array( 'customer_email_address', $bookingpress_export_field ) ) {
							$appointment['Customer Email Address'] = sanitize_email( $get_appointment['bookingpress_customer_email'] );
						}

						if ( in_array( 'customer_phone_number', $bookingpress_export_field ) ) {
							$appointment['Customer Phone Number'] = ! empty( $get_appointment['bookingpress_customer_phone'] ) ? '"' . sanitize_text_field( $get_appointment['bookingpress_customer_phone'] ) . '"' : '-';
						}
						if ( $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ) {
							if ( in_array( 'staff_member_full_name', $bookingpress_export_field ) ) {					
								$staffmember_name = $get_appointment['bookingpress_staff_first_name'] .' '.$get_appointment['bookingpress_staff_last_name'];
								$appointment['Staff Member Full Name'] = ! empty( $staffmember_name ) ? '"' . $staffmember_name . '"' : '-';
							}
						}
						if ( in_array( 'appointment_created_date', $bookingpress_export_field ) ) {
							$appointment_date            = date( $default_date_time_format, strtotime( $get_appointment['bookingpress_created_at'] ) );
							$appointment['Created Date'] = ! empty( $appointment_date ) ? '"' . sanitize_text_field( $appointment_date ) . '"' : '-';
						}

						if ( in_array( 'appointment_no_of_person', $bookingpress_export_field ) ) {
							$appointment['No. of Person'] = !empty($get_appointment['bookingpress_selected_extra_members']) ? $get_appointment['bookingpress_selected_extra_members'] : '';
						}

						if ( in_array( 'service', $bookingpress_export_field ) ) {
							$appointment['Service'] = ! empty( $get_appointment['bookingpress_service_name'] ) ? '"' . stripslashes_deep(sanitize_text_field( $get_appointment['bookingpress_service_name'] )) . '"' : '-';
						}

						if ( in_array( 'amount', $bookingpress_export_field ) ) {
							$currency_name         = $get_appointment['bookingpress_service_currency'];
							$currency_symbol       = $BookingPress->bookingpress_get_currency_symbol( $currency_name );							
							$payment_amount        = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $get_appointment['bookingpress_total_amount'], $currency_symbol );
							$appointment['Amount'] = ! empty( $payment_amount ) ? '"' . $payment_amount . '"' : '-';
						}

						if ( in_array( 'start_time', $bookingpress_export_field ) ) {
							$appointment_date          = $get_appointment['bookingpress_appointment_date'] . ' ' . $get_appointment['bookingpress_appointment_time'];
							$appointment_start_time    = date( $bookingpress_date_time_format, strtotime( $appointment_date ) );
							$appointment['Start Time'] = ! empty( $appointment_start_time ) ? '"' . $appointment_start_time . '"' : '-';
						}
						if ( in_array( 'end_time', $bookingpress_export_field ) ) {
							$service_id              = intval( $get_appointment['bookingpress_service_id'] );
							$service_start_time      = sanitize_text_field( $get_appointment['bookingpress_appointment_time'] );
							$service_duration        = sanitize_text_field( $get_appointment['bookingpress_service_duration_val'] );
							$service_duration_unit   = sanitize_text_field( $get_appointment['bookingpress_service_duration_unit'] );							
							$appointment_date        = $get_appointment['bookingpress_appointment_date'] . ' ' . $get_appointment['bookingpress_appointment_end_time'];
							$appointment_end_time    = date( $bookingpress_date_time_format, strtotime( $appointment_date ) );
							$appointment['End Time'] = ! empty( $appointment_end_time ) ? '"' . $appointment_end_time . '"' : '-';
						}

						if ( in_array( 'note', $bookingpress_export_field ) ) {
							$appointment['Note'] = ! empty( $get_appointment['bookingpress_appointment_internal_note'] ) ? '"' . sanitize_textarea_field( $get_appointment['bookingpress_appointment_internal_note'] ) . '"' : '-';
						}

						if ( in_array( 'appointment_status', $bookingpress_export_field ) ) {
							$bookingpress_current_appointment_status = !empty($get_appointment['bookingpress_appointment_status']) ? sanitize_text_field($get_appointment['bookingpress_appointment_status']) : '';

							$bookingpress_appointment_status_label = "";
							foreach($bookingpress_appointment_status_arr as $bookingpress_appointment_status_key => $bookingpress_appointment_status_vals){
								if($bookingpress_appointment_status_vals['value'] == $bookingpress_current_appointment_status){
									$bookingpress_appointment_status_label = $bookingpress_appointment_status_vals['text'];
									break;
								}
							}

							$appointment['Appointment Status'] = ! empty( $bookingpress_appointment_status_label ) ? '"' . sanitize_text_field( $bookingpress_appointment_status_label ) . '"' : '-';
						}

						/* Get Custom Fields Export Data */
						if(!empty($bookingpress_export_custom_fields)){
							if(!empty($get_appointment['bookingpress_is_cart']) && $get_appointment['bookingpress_is_cart'] == 1 ) {
								$bookingpress_order_id = !empty($get_appointment['bookingpress_order_id']) ? intval($get_appointment['bookingpress_order_id']) : 0;    
								if(!empty($bookingpress_order_id)) {
									$bookingpress_appointment_meta_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value,bookingpress_appointment_meta_key FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_order_id = %d AND bookingpress_appointment_meta_key = %s ORDER BY bookingpress_appointment_meta_created_date DESC", $bookingpress_order_id,'appointment_details' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally. False Positive alarm 
								}
								$bookingpress_appointment_meta_data = !empty($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value']) ? json_decode($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value'],true) : array();                       
								$bookingpress_appointment_form_fields = !empty($bookingpress_appointment_meta_data['form_fields']) ? stripslashes_deep($bookingpress_appointment_meta_data['form_fields']) : array();    
							} else {                            
								$bookingpress_appointment_meta_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value,bookingpress_appointment_meta_key FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_id = %d AND bookingpress_appointment_meta_key = %s ORDER BY bookingpress_appointment_meta_created_date DESC", $appointment_id,'appointment_form_fields_data' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally. False Positive alarm 
								$bookingpress_appointment_meta_data = !empty($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value']) ? json_decode($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value'],true) : array();                  
								$bookingpress_appointment_form_fields = !empty($bookingpress_appointment_meta_data['form_fields']) ? stripslashes_deep($bookingpress_appointment_meta_data['form_fields']) : array();
							}																	
							foreach($bookingpress_export_custom_fields as $export_fields_key){								
								$bookingpress_custom_field_name = (isset($bookingpress_custom_field_export_label[$export_fields_key]))?$bookingpress_custom_field_export_label[$export_fields_key]:'';
								$bookingpress_custom_field_value = '';

								$appointment[$export_fields_key] = '';
								$bookingpress_custom_field_key_value[$export_fields_key] = $bookingpress_custom_field_name;

								$bookingpress_front_field_data_new = (isset($bookingpress_appointment_meta_data['form_fields']))?$bookingpress_appointment_meta_data['form_fields']:'';
								if(!empty($bookingpress_front_field_data_new) && is_array($bookingpress_front_field_data_new)){	
									foreach($bookingpress_front_field_data_new as $field_key=>$field_val){
										if($export_fields_key == $field_key){
											
											$bookingpress_custom_field_value = (isset($bookingpress_appointment_form_fields[$export_fields_key]))?$bookingpress_appointment_form_fields[$export_fields_key]:'';																				
											if(!empty($bookingpress_custom_field_value) && is_array($bookingpress_custom_field_value)){
												$bookingpress_custom_field_value = implode("   ",$bookingpress_custom_field_value);
											}
											$appointment[$export_fields_key] = '"' .$bookingpress_custom_field_value. '"';
											$bookingpress_custom_field_key_value[$export_fields_key] = '"' .$bookingpress_custom_field_name. '"';

										}
									}
								}

								if ( 'customer_firstname' == $export_fields_key && empty($appointment[$export_fields_key])) {

									$bookingpress_customer_firstname                     = (!empty($get_appointment['bookingpress_customer_firstname']))? stripslashes_deep($get_appointment['bookingpress_customer_firstname']):'';									
									$appointment[$export_fields_key] = $bookingpress_customer_firstname;
									$bookingpress_custom_field_key_value[$export_fields_key] = $bookingpress_custom_field_name;

								}
								if ( 'customer_lastname' == $export_fields_key && empty($appointment[$export_fields_key])) {

									$bookingpress_customer_lastname                     = (!empty($get_appointment['bookingpress_customer_lastname']))? stripslashes_deep($get_appointment['bookingpress_customer_lastname']):'';									
									$appointment[$export_fields_key] = $bookingpress_customer_lastname;
									$bookingpress_custom_field_key_value[$export_fields_key] = $bookingpress_custom_field_name;
																		
								}
								if ( 'customer_username' == $export_fields_key && empty($appointment[$export_fields_key])) {

									$bookingpress_username                     = (!empty($get_appointment['bookingpress_username']))? stripslashes_deep($get_appointment['bookingpress_username']):'';									
									$appointment[$export_fields_key] = $bookingpress_username;
									$bookingpress_custom_field_key_value[$export_fields_key] = $bookingpress_custom_field_name;
																		
								}																								

							}

							
						}
						$appointment = apply_filters('bookingpress_export_data_fields_data_outside', $appointment, $bookingpress_export_field, $get_appointment);

						$appointments[] = $appointment;
					}
				}
			} else {
				$appointments = array();
			}
			$data = array();
			if ( ! empty( $appointments ) ) {
				array_push( $data, array_keys( $appointments[0] ) );
				
				if(!empty($data[0]) && isset($data[0]) && !empty($bookingpress_custom_field_key_value)){
					foreach($data[0] as $dkey=>$dval){						
						if(isset($bookingpress_custom_field_key_value[$dval])){
							$data[0][$dkey] = $bookingpress_custom_field_key_value[$dval];
						}
					}
				}
				foreach ( $appointments as $key => $value ) {
					array_push( $data, array_values( $value ) );
				}
			}
			$response['status'] = 'success';
			$response['data']   = $data;
			echo wp_json_encode( $response );
			exit;
		}
				
		/**
		 * This function is used to get the appointment form field data.
		 * 
		 * @param  mixed $bookingpress_appointment_id
		 * @return void
		 */
		function bookingpress_get_appointment_form_field_data($bookingpress_appointment_id) {
            global $wpdb,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_appointment_meta;

            $bookingpress_appointment_form_fields = array();
            if(!empty($bookingpress_appointment_id)) {
                $bookingpress_appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE `bookingpress_appointment_booking_id` = %d ", $bookingpress_appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_appointment_bookings is a table name. false alarm.
                
                if(!empty($bookingpress_appointment_data)) {
                    if(!empty($bookingpress_appointment_data['bookingpress_is_cart']) && $bookingpress_appointment_data['bookingpress_is_cart'] == 1 ) {
                        $bookingpress_order_id = !empty($bookingpress_appointment_data['bookingpress_order_id']) ? intval($bookingpress_appointment_data['bookingpress_order_id']) : 0;
                        
                        if(!empty($bookingpress_order_id)) {
                            $bookingpress_appointment_meta_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value,bookingpress_appointment_meta_key FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_order_id = %d AND bookingpress_appointment_meta_key = %s ORDER BY bookingpress_appointment_meta_created_date DESC", $bookingpress_order_id,'appointment_details' ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_appointment_meta is a table name. false alarm.
                        }
                        $bookingpress_appointment_meta_data = !empty($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value']) ? json_decode($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value'],true) : array();                       
                        $bookingpress_appointment_form_fields = !empty($bookingpress_appointment_meta_data['form_fields']) ? stripslashes_deep($bookingpress_appointment_meta_data['form_fields']) : array();
                        
                    } else {                            
                        $bookingpress_appointment_meta_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value,bookingpress_appointment_meta_key FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_id = %d AND bookingpress_appointment_meta_key = %s ORDER BY bookingpress_appointment_meta_created_date DESC", $bookingpress_appointment_id,'appointment_form_fields_data' ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_appointment_meta is a table name. false alarm.

                        $bookingpress_appointment_meta_data = !empty($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value']) ? json_decode($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value'],true) : array();
                                      
                        $bookingpress_appointment_form_fields = !empty($bookingpress_appointment_meta_data['form_fields']) ? stripslashes_deep($bookingpress_appointment_meta_data['form_fields']) : array();
                    }
                }                
            }
			return $bookingpress_appointment_form_fields;
        }
		
		/**
		 * Function for add vue methods for appointment module
		 *
		 * @return void
		 */
		function bookingpress_appointment_add_dynamic_vue_methods_func() {
			global $BookingPress, $bookingpress_notification_duration;
			$bookingpress_export_delimeter = $BookingPress->bookingpress_get_settings( 'bookingpress_export_delimeter', 'general_setting' );
			?>
			bpa_enable_service_share(){
				const vm = this
				if(vm.share_url_form.selected_service_id != '' && vm.share_url_form.selected_page_wp_id!=''){
					vm.bookingpress_generate_share_url();
					if((vm.share_url_form.email_sharing == true && vm.share_url_form.sharing_email != '') || (vm.share_url_form.sms_sharing == true && vm.share_url_form.phone_number != '') || (vm.share_url_form.whatsapp_sharing == true && vm.share_url_form.phone_number != '') ){
						vm.is_share_button_disabled = false;
					}else{
						vm.is_share_button_disabled = true;
					}
				}else{
					vm.is_share_button_disabled = true;
					vm.bookingpress_generate_share_url();
				}
			},
			bpa_share_url_enable_load_more(){
				const vm = this
				let selected_service = vm.share_url_form.selected_service_id;
				var bpa_total_service_extras = vm.bookingpress_loaded_extras[selected_service].length;
				if(bpa_total_service_extras > 2){
					vm.share_url_load_less = true;
					vm.share_url_load_more = false;
				}
			},
			bpa_share_url_enable_load_less(){
				const vm = this
				vm.share_url_load_less = false;
				vm.share_url_load_more = true;
			},
			bpa_change_share_url_service(){
				const vm = this
				let selected_service = vm.share_url_form.selected_service_id;
				let services_lists = vm.appointment_services_list;
				let max_capacity = 0;
				let min_capacity = 0;
				for( let s in services_lists ){
					let categories = services_lists[ s ];
					let category_service_list = categories.category_services;
					category_service_list.forEach( function( services ){
						let service_id = services.service_id;
						if( service_id == selected_service ){
							max_capacity = ( "undefined" != typeof services.service_max_capacity ) ? services.service_max_capacity : 1;
							if( vm.is_bring_anyone_with_you_enable == 1 ){
								min_capacity = ( "undefined" != typeof services.service_min_capacity ) ? services.service_min_capacity : 1;
								
							}
							return false;
						}
					});
				}
				
				vm.share_url_form.selected_guests = 0;
				vm.share_url_form.selected_guests_max_capacity = parseInt(max_capacity);
				if( vm.is_bring_anyone_with_you_enable == 1 ){
					vm.share_url_form.selected_guests_min_capacity = parseInt(min_capacity);
					vm.share_url_form.selected_guests = parseInt(min_capacity);
				}
				<?php do_action( 'bookingpress_share_url_after_select_service'); ?>
				vm.bpa_enable_service_share();
			},
			bpa_change_share_url_staff(){
				const vm = this
				if( 1 == vm.is_bring_anyone_with_you_enable ){
					vm.share_url_form.selected_guests = "1";
					let selected_staffmember = vm.share_url_form.selected_staff_id;
					if( "" != selected_staffmember ){
						let selected_service = vm.share_url_form.selected_service_id;
						let selected_service_staffmember = vm.bookingpress_loaded_staff[ selected_service ];
						let selected_staff_capacity = 1;
						let selected_staff_min_capacity = 1;
						selected_service_staffmember.forEach(function( elm ){
							if( selected_staffmember == elm.bookingpress_staffmember_id ){
								selected_staff_capacity = elm.bookingpress_service_capacity;
								selected_staff_min_capacity = elm.bookingpress_service_min_capacity;
								return false;
							}
						});
						vm.share_url_form.selected_guests_max_capacity = parseInt(selected_staff_capacity);
						vm.share_url_form.selected_guests_min_capacity = parseInt(selected_staff_min_capacity);
						vm.share_url_form.selected_guests = parseInt(selected_staff_min_capacity);
					}
				}
				<?php do_action( 'bookingpress_share_url_after_select_staff' ); ?>
				vm.bookingpress_generate_share_url();
			},
			bookingpress_generate_share_url(){
				const vm = this
				var appointment_generate_url_details = {
					action:'bookingpress_generate_share_url',
					share_url_form_data: vm.share_url_form,
					selected_extras: vm.bookingpress_loaded_extras[vm.share_url_form.selected_service_id],
					_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
				}				
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_generate_url_details ) )
				.then(function(response) {			
					if(response.data.variant == "success"){
						//vm.bpa_enable_service_share();
						vm.share_url_form.generated_url = response.data.generated_url;
					}else{
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: 'error',
							customClass: 'error_notification',
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
			},
			Bookingpress_export_appointment_data(currentElement){
				const vm = this;
				vm.ExportAppointment = true;

				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#appointment_export_model .el-dialog.bpa-dialog--export-appointments');
				}
			},
			close_export_appointment_model(){
				const vm = this;
				vm.ExportAppointment = false;
				vm.export_checked_field = vm.default_export_checked_field;
			},
            saveProAppointmentBooking(bookingAppointment){
				const vm = new Vue();
				const vm2 = this
				
				let is_timeslot_display = vm2.is_timeslot_display;
				if( '0' == is_timeslot_display ){
					vm2[bookingAppointment].appointment_booked_time = "00:00:00";
				}

				vm2.saveAppointmentBooking(bookingAppointment);
			},
			bookingpress_export_appointment(){
				const vm = this;
				vm.is_export_button_loader = '1'
				vm.is_export_button_disabled = true;	

				var bookingpress_search_data = { 'search_appointment':vm.search_appointment,'selected_date_range': vm.appointment_date_range, 'customer_name': vm.search_customer_name,'service_name': vm.search_service_name,'appointment_status': vm.search_appointment_status,'appointment_status': vm.search_appointment_status,'staff_member_name' : vm.search_staff_member_name, 'search_appointment_id':vm.search_appointment_id };	

				<?php do_action('bpa_export_post_data_outside'); ?>		

				var appointment_export_data = {
					action:'bookingpress_export_appointment_data',
					export_field: vm.export_checked_field,
					search_data : bookingpress_search_data,
					custom_field_export_key: vm.bookingpress_all_export_custom_fields_key,
					custom_field_export_label: vm.bookingpress_all_export_custom_fields_label,
					_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
				}				
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_export_data ) )
				.then(function(response) {			
					vm.is_export_button_loader = '0';		
					vm.is_export_button_disabled = false;
					vm.close_export_appointment_model();
					if(response.data.data != 'undefined') {
						var export_data;
						var csv = ''; 
						var delimeter = ',';
						if(response.data.data != '') {
							export_data = response.data.data;						
							export_data.forEach(function(row){					    				
								csv += row.join('<?php echo esc_html( $bookingpress_export_delimeter ); ?>');
								   csv += "\n";
							});	 
						}		
						const anchor = document.createElement('a');
						anchor.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);	
						anchor.target = '_blank';
						anchor.download = 'Bookingpress-export-appointment.csv';					    
						anchor.click();
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
			viewAppointmentData(index, row){
				const vm = this
				var bookingpress_appointment_id = row.appointment_id
				vm.appointment_details_drawer = true
			},
			bookingpress_close_extras_modal(){
				//Trigger body click for close extras popover
				this.bookingpress_admin_get_final_step_amount();
				document.body.click();
			},
			bookingpress_calculate_prices(){
				const vm = this;
				var bookingpress_appointment_recalculate_data = {
					action:'bookingpress_admin_appointment_recalculate_data',
					appointment_formdata: JSON.stringify( vm.appointment_formdata ),
					appointment_extra_details: vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service],
					appointment_staff_details: vm.bookingpress_loaded_staff[vm.appointment_formdata.appointment_selected_service],
					_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
				}				
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_appointment_recalculate_data ) )
				.then(function(response) {
					vm.appointment_formdata.subtotal = response.data.appointment_formdata.subtotal;
					vm.appointment_formdata.subtotal_with_currency = response.data.appointment_formdata.subtotal_with_currency;
					vm.appointment_formdata.extras_total = response.data.appointment_formdata.extras_total;
					vm.appointment_formdata.extras_total_with_currency = response.data.appointment_formdata.extras_total_with_currency;
					if(response.data.appointment_formdata.tax != undefined){
						vm.appointment_formdata.tax_percentage = parseFloat(response.data.appointment_formdata.tax_percentage);
						vm.appointment_formdata.tax = parseFloat(response.data.appointment_formdata.tax);
						vm.appointment_formdata.tax_with_currency = response.data.appointment_formdata.tax_with_currency;
						vm.appointment_formdata.included_tax_label = response.data.appointment_formdata.included_tax_label;
					}
					vm.appointment_formdata.applied_coupon_code = response.data.appointment_formdata.applied_coupon_code;
					vm.appointment_formdata.coupon_discounted_amount = response.data.appointment_formdata.coupon_discounted_amount;
					vm.appointment_formdata.coupon_discounted_amount_with_currency = response.data.appointment_formdata.coupon_discounted_amount_with_currency;
					vm.appointment_formdata.total_amount = response.data.appointment_formdata.total_amount;
					vm.appointment_formdata.total_amount_with_currency = response.data.appointment_formdata.total_amount_with_currency;
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
			bookingpress_apply_coupon_code(){
				const vm = this;
				if(typeof vm.appointment_formdata.tip_amount !== 'undefined'){
					var total_amount = vm.appointment_formdata.total_amount - vm.appointment_formdata.tip_amount;
					vm.appointment_formdata.total_amount = total_amount;
				}				
				if(vm.bpa_coupon_apply_disabled == 0){
					vm.coupon_apply_loader = "1"
					var bookingpress_apply_coupon_data = {};
					bookingpress_apply_coupon_data.action = "bookingpress_apply_coupon_code_backend"
					bookingpress_apply_coupon_data.coupon_code = vm.appointment_formdata.applied_coupon_code
					bookingpress_apply_coupon_data.selected_service = vm.appointment_formdata.appointment_selected_service
					bookingpress_apply_coupon_data.payable_amount = vm.appointment_formdata.total_amount
					bookingpress_apply_coupon_data._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_apply_coupon_data ) )
					.then( function (response) {
						vm.coupon_apply_loader = "0"
						vm.coupon_applied_status = response.data.variant;
						if(response.data.variant == "error"){
							vm.coupon_code_msg = response.data.msg
						}else{
							vm.coupon_code_msg = response.data.msg
							vm.appointment_formdata.coupon_discounted_amount = response.data.discounted_amount;
							vm.appointment_formdata.coupon_discounted_amount_with_currency = response.data.discounted_amount_with_currency;
							vm.appointment_formdata.applied_coupon_details = response.data.coupon_data;
							vm.bpa_coupon_apply_disabled = 1;
						}					
						vm.bookingpress_admin_get_final_step_amount();

					}.bind(this) )
					.catch( function (error) {
						vm.bookingpress_set_error_msg(error)
					});
				}
			},
			bookingpress_change_bring_anyone(){
				const vm = this
				vm.bookingpress_appointment_get_disable_dates();
				vm.bookingpress_admin_get_final_step_amount();
				if(vm.appointment_formdata.applied_coupon_code != ''){
					vm.bookingpress_apply_coupon_code();
				}
			},
			bookingpress_change_staff(){
				const vm = this
				vm.bookingpress_set_bring_anyone_capacity();
				vm.bookingpress_appointment_get_disable_dates();
				if(typeof vm.appointment_formdata.selected_staffmember != "undefined"){
					var selected_staffmember = vm.appointment_formdata.selected_staffmember;
					if(vm.appointment_formdata.appointment_selected_service){
						<?php do_action( 'bookingpress_after_select_staff_backend') ?>
						var staff_member_details = vm.bookingpress_loaded_staff[vm.appointment_formdata.appointment_selected_service];							
						Object.entries(staff_member_details).forEach(entry => {
							const [key, value] = entry;							
							if(staff_member_details[key].bookingpress_staffmember_id == vm.appointment_formdata.selected_staffmember){

								if( vm.is_bring_anyone_with_you_enable == 1 ){
									
									vm.appointment_formdata.selected_bring_members = staff_member_details[key].bookingpress_service_min_capacity;
								}
								var total_amount = parseFloat(staff_member_details[key].bookingpress_service_price);																		
								//vm.appointment_formdata.tax = staff_member_details[key].tax_amount_without_currency;
								//vm.appointment_formdata.tax_with_currency = staff_member_details[key].tax_amount;									
								vm.appointment_formdata.service_price_without_currency = total_amount;									

							}
						});
					}				
				}

				if(typeof vm.appointment_formdata.custom_service_duration_value !== 'undefined'){
					vm.appointment_formdata.custom_service_duration_value = '';
				}				
				vm.bookingpress_admin_get_final_step_amount();
				
				if(vm.appointment_formdata.applied_coupon_code != ''){
					vm.bookingpress_apply_coupon_code();
				}
			},
			bookingpress_set_bring_anyone_capacity(){
				const vm = this;
				if( 1 == vm.is_bring_anyone_with_you_enable ){
					vm.appointment_formdata.selected_bring_members = 1;
					let selected_staffmember = vm.appointment_formdata.selected_staffmember;
					if( "" != selected_staffmember ){
						let selected_service = vm.appointment_formdata.appointment_selected_service;
						let selected_service_staffmember = vm.bookingpress_loaded_staff[ selected_service ];
						let selected_staff_capacity = 1;
						let selected_staff_min_capacity = 1;
						selected_service_staffmember.forEach(function( elm ){
							if( selected_staffmember == elm.bookingpress_staffmember_id ){
								selected_staff_capacity = elm.bookingpress_service_capacity;
								selected_staff_min_capacity = elm.bookingpress_service_min_capacity;
								return false;
							}
						});
						vm.appointment_formdata.bookingpress_bring_anyone_max_capacity = parseInt(selected_staff_capacity);
						vm.appointment_formdata.bookingpress_bring_anyone_min_capacity = parseInt(selected_staff_min_capacity);
					}
				}
			},
			bookingpress_remove_coupon_code(){
				const vm = this
				vm.appointment_formdata.applied_coupon_code = "";
				vm.coupon_code_msg = ""								
				vm.bpa_coupon_apply_disabled = 0;
				vm.coupon_applied_status = "error";
				vm.coupon_discounted_amount = "";				
				vm.appointment_formdata.coupon_discounted_amount = '';
				vm.appointment_formdata.coupon_discounted_amount_with_currency = '';
				vm.appointment_formdata.applied_coupon_details = [];				
				vm.bookingpress_admin_get_final_step_amount();

			},
			bookingpress_open_refund_model(currentElement,appointment_id,payment_id,currency_symbol,partial_refund) {
				const vm = this;				
				vm.reset_refund_confirm_model();				
				vm.refund_confirm_form.refund_currency = currency_symbol;
				vm.refund_confirm_form.allow_partial_refund = partial_refund;
				vm.refund_confirm_form.appointment_id = appointment_id;
				vm.refund_confirm_form.payment_id = payment_id;				
				var postData = { action:'bookingpress_get_refund_amount', bookingpress_appointment_id:appointment_id,bookingpress_payment_id :payment_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					if(response.data.variant == "success"){
						vm.refund_confirm_form.refund_amount = response.data.refund_amount;
						vm.refund_confirm_form.default_refund_amount = response.data.default_refund_amount;						
					} else{											
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					}
				}).catch(function(error){
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
						message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});

				vm.refund_confirm_modal = true;
				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#refund_confirm_process .el-dialog.bpa-dialog--refund-process');
				}
			},
			close_refund_confirm_model(){
				const vm = this;
				vm.reset_refund_confirm_model();
				vm.refund_confirm_modal = false;
			},
			reset_refund_confirm_model() {
				const vm = this
				vm.refund_confirm_form.refund_type = 'full';
				vm.refund_confirm_form.refund_amount = 0;
				vm.refund_confirm_form.refund_reason = '';
				vm.refund_confirm_form.appointment_id = '';
				vm.refund_confirm_form.payment_id = '';
				vm.refund_confirm_form.default_refund_amount = 0;
				vm.refund_confirm_form.allow_partial_refund = 0;
				vm.refund_confirm_form.allow_refund = true;
				vm.is_display_refund_loader = '0';
				vm.is_refund_btn_disabled = false;
			},
			bookingpress_apply_for_refund(payment_id,appointment_id) {
				const vm = this
				vm.is_display_refund_loader = '1';
				vm.is_refund_btn_disabled = true;
				var is_error = false;
				var error_msg = false;		
				if(vm.refund_confirm_form.allow_refund == true) {
					if((vm.refund_confirm_form.refund_amount == '' || vm.refund_confirm_form.refund_amount == 0) && vm.refund_confirm_form.refund_type == 'partial') {
						error_msg =  '<?php esc_html_e('Refund amount should be more than zero', 'bookingpress-appointment-booking'); ?>';
						is_error = true
					}
					if(parseInt(vm.refund_confirm_form.refund_amount) > parseInt(vm.refund_confirm_form.default_refund_amount) && vm.refund_confirm_form.refund_type == 'partial' ) {					
						error_msg =  '<?php esc_html_e('Refund amount should not be more than paid the amount.', 'bookingpress-appointment-booking'); ?>';
						is_error = true
					} 
					if( is_error == true) {
						vm.$notify({
							title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
							message: error_msg,
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
						vm.is_display_refund_loader = '0';
						vm.is_refund_btn_disabled = false;
						return false;
					}
					var postData = { action:'bookingpress_apply_for_refund',bookingpress_refund_data:vm.refund_confirm_form,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == "success"){
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
							vm.close_refund_confirm_model();
							vm.loadAppointmentsWithoutLoader();
						} else{											
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
						}
						vm.is_display_refund_loader = '0';
						vm.is_refund_btn_disabled = false;
						
					}).catch(function(error){
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
							message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					});
				} else {
					vm.bookingpress_change_status(appointment_id,'3')
					vm.is_display_refund_loader = '0';
					vm.is_refund_btn_disabled = false;
					vm.close_refund_confirm_model();				
				}

			},
            isValidateZeroDecimal(evt){
                const vm = this                
                if (/[^0-9]+/.test(evt)){
                    vm.refund_confirm_form.refund_amount = evt.slice(0, -1);
                }
            },
            <?php
            do_action('bookingpress_customer_add_dynamic_vue_methods');
		}
	}
}

global $bookingpress_pro_appointment;
$bookingpress_pro_appointment = new bookingpress_pro_appointment();
