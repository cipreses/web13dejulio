<?php
if ( ! class_exists( 'bookingpress_pro_email_notifications' ) ) {
	class bookingpress_pro_email_notifications Extends BookingPress_Core {

		var $bookingpress_global_data;

		function __construct() {
			// global $bookingpress_global_options;
			// $this->bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();

			add_filter( 'bookingpress_modify_email_content_filter', array( $this, 'bookingpress_modify_email_content_filter_func' ), 10, 2 );

			add_filter( 'bookingpress_modify_email_notification_data', array( $this, 'bookingpress_modify_email_notification_data_func' ), 10, 5 );

			add_filter( 'bookingpress_email_notification_attachment', array( $this, 'bookingpress_attach_ics_file_with_email' ), 10, 6 );

			add_filter('bookingpress_add_cc_email_address', array($this, 'bookingpress_add_cc_email_address_func'), 10, 2);

			add_filter('bookingpress_send_all_custom_email_notifications', array($this, 'bookingpress_send_all_custom_email_notifications_func'), 10, 4);

			add_filter('bookingpress_get_email_notiication_reply_to_data', array($this, 'bookingpress_get_email_notiication_reply_to_data_func'), 10, 2);


			add_action('bookingpress_after_change_appointment_status',array($this,'bookingpress_after_change_appointment_status_func'),10,2);			
			add_filter('bookingpress_modify_send_email_notification_type',array($this,'bookingpress_modified_status_notification_for_other_intergartion_func'),10,2);			
			
			add_action('bookingpress_after_add_appointment_from_backend',array($this,'bookingpress_after_add_appointment_from_backend_func'),11,3);			
		}
				
		/**
		 * Function for complete & No-Show notification send when backend appointment create 
		 *
		 * @param  mixed $inserted_booking_id
		 * @param  mixed $bookingpress_appointment_data
		 * @param  mixed $entry_id
		 * @return void
		 */
		function bookingpress_after_add_appointment_from_backend_func($inserted_booking_id, $bookingpress_appointment_data, $entry_id) {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications;
			$inserted_booking_id = (int)$inserted_booking_id;
			$bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_customer_id,bookingpress_appointment_status FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $inserted_booking_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if(!empty($bookingpress_appointment_data)) {
				$bookingpress_appointment_status = $bookingpress_appointment_data['bookingpress_appointment_status'];
				if($bookingpress_appointment_status == 5 || $bookingpress_appointment_status == 6){

					$bookingpress_booked_appointment_customer_id = $bookingpress_appointment_data['bookingpress_customer_id'];
					$bookingpress_customer_data = $BookingPress->get_customer_details($bookingpress_booked_appointment_customer_id);
					$customer_email             = (isset($bookingpress_customer_data['bookingpress_user_email'])) ? $bookingpress_customer_data['bookingpress_user_email'] : '';	
					$appointment_status_type = '';
					if($bookingpress_appointment_status == 5){
						$appointment_status_type = 'Appointment No-Show';
					}else{
						$appointment_status_type = 'Appointment Completed';
					}				
					$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( $appointment_status_type, $inserted_booking_id, $customer_email );
					
				}
			}
		}

		/**
		 * Function for complete & No-show notification
		 *
		 * @param  mixed $bookingpress_notification_type
		 * @param  mixed $appointment_new_status
		 * @return void
		 */
		function bookingpress_modified_status_notification_for_other_intergartion_func($bookingpress_notification_type,$appointment_new_status){
			if($appointment_new_status == 5){
				$bookingpress_notification_type = 'Appointment No-Show';
			}
			if($appointment_new_status == 6){
				$bookingpress_notification_type = 'Appointment Completed';
			}
			return $bookingpress_notification_type;
		}
		
		/**
		 * Function for complete & No-show notification
		 *
		 * @param  mixed $appointment_id
		 * @param  mixed $appointment_new_status
		 * @return void
		 */
		function bookingpress_after_change_appointment_status_func($appointment_id, $appointment_new_status){

			if($appointment_new_status == 5 || $appointment_new_status == 6){

				global $wpdb,$BookingPress, $bookingpress_email_notifications,$tbl_bookingpress_appointment_bookings;
				$booked_appointment_details = $wpdb->get_row($wpdb->prepare('SELECT bookingpress_customer_id FROM ' . $tbl_bookingpress_appointment_bookings . ' WHERE bookingpress_appointment_booking_id = %d', $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
				$bookingpress_booked_appointment_customer_id = $booked_appointment_details['bookingpress_customer_id'];
                $bookingpress_customer_data = $BookingPress->get_customer_details($bookingpress_booked_appointment_customer_id);
                $customer_email             = ! empty($bookingpress_customer_data['bookingpress_user_email']) ? $bookingpress_customer_data['bookingpress_user_email'] : '';	
				
					

				$appointment_status_type = '';
				if($appointment_new_status == 5){
					$appointment_status_type = 'Appointment No-Show';
				}else{
					$appointment_status_type = 'Appointment Completed';
				}				
				$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( $appointment_status_type, $appointment_id, $customer_email );

			}
		}

		function bookingpress_get_email_notiication_reply_to_data_func($reply_to_data, $appointment_id)
		{
			if($appointment_id) {
				global $bookingpress_pro_staff_members, $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_staffmembers;
				$is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
				if($is_staffmember_module_activated) {
					$appointment_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_staff_member_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A);  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
					$bookingpress_staffmember_id = !empty($appointment_data['bookingpress_staff_member_id'])  ? intval($appointment_data['bookingpress_staff_member_id']) : 0 ;
					if(!empty($bookingpress_staffmember_id)) {
						$bpa_staffmember_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_staffmember_firstname,bookingpress_staffmember_lastname,bookingpress_staffmember_email FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $bookingpress_staffmember_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
						$staff_firstname = isset($bpa_staffmember_data['bookingpress_staffmember_firstname']) ? sanitize_text_field($bpa_staffmember_data['bookingpress_staffmember_firstname']): '';
						$staff_lastname = isset($bpa_staffmember_data['bookingpress_staffmember_lastname']) ? sanitize_text_field($bpa_staffmember_data['bookingpress_staffmember_lastname']): '';
						$staff_email = isset($bpa_staffmember_data['bookingpress_staffmember_email']) ? $bpa_staffmember_data['bookingpress_staffmember_email']: '';
						$bpa_staffmember_name='';
						if(!empty($staff_firstname)) {
                            $bpa_staffmember_name=$staff_firstname.' ';
                        }
                        if(!empty($staff_lastname)){
                            $bpa_staffmember_name.=$staff_lastname;
                        }
						$reply_to_data = array(
							'bookingpress_email_reply_to_name' => $bpa_staffmember_name, 
							'bookingpress_email_reply_to_email'=> $staff_email
						);
					}
				}
			}
			return $reply_to_data;
		}
		function bookingpress_send_all_custom_email_notifications_func($bookingpress_email_notification_arr,$template_type,$inserted_booking_id,$notification_from = 'email' ) {

			global $wpdb,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_notifications;
			$bookingpress_default_notification_arr           = array(
				'Appointment Approved'  => 'appointment_approved',
				'Appointment Pending'   => 'appointment_pending',
				'Appointment Canceled'  => 'appointment_canceled',
				'Appointment Rejected'  => 'appointment_rejected',
				'Appointment No-Show'  => 'appointment_noshow',
				'Appointment Completed'  => 'appointment_completed',
			);
			$notification_name = !empty($bookingpress_email_notification_arr[0]) ? $bookingpress_email_notification_arr[0] : '';			

			if ( !empty( $notification_name) && !is_array( $notification_name) && ! empty($bookingpress_default_notification_arr[ $notification_name ])) {

				$notification_event_action = $bookingpress_default_notification_arr[$notification_name];

				$bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_service_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id =%d", $inserted_booking_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
				$bookingpress_service_id = ! empty( $bookingpress_appointment_data['bookingpress_service_id'] ) ? intval( $bookingpress_appointment_data
				['bookingpress_service_id'] ) : '';

				$bookingpress_email_data = array();
				if($notification_from == 'email') {
					$bookingpress_email_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_notification_name,bookingpress_notification_service FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_receiver_type = %s AND bookingpress_notification_status = %d AND bookingpress_notification_type = %s AND bookingpress_notification_event_action = %s AND bookingpress_custom_notification_type = %s ORDER BY bookingpress_notification_id DESC", $template_type, 1, 'custom', $notification_event_action,'action-trigger' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm					
				} else {
					$bookingpress_email_data = apply_filters('bookingpress_modify_email_notification_data_for_extrnal_notification',$bookingpress_email_data,$notification_from,$template_type,$notification_event_action);					
				}
				if(!empty($bookingpress_email_data)) {
					foreach ( $bookingpress_email_data as $bookingpress_email_data_key => $bookingpress_email_data_val ) {
						if ( ! empty( $bookingpress_email_data_val['bookingpress_notification_service'] ) ) {
							$bookingpress_notification_service_arr = explode( ',', $bookingpress_email_data_val['bookingpress_notification_service'] );
							if ( ! empty( $bookingpress_notification_service_arr ) && ( in_array( $bookingpress_service_id, $bookingpress_notification_service_arr ) || in_array( 'any', $bookingpress_notification_service_arr )) ) {
								$bookingpress_email_notification_arr[] = $bookingpress_email_data_val['bookingpress_notification_name'];
							}							
						} else {
							$bookingpress_email_notification_arr[] = $bookingpress_email_data_val['bookingpress_notification_name'];
						}
					}
				}
			}
			return $bookingpress_email_notification_arr;
		}

		function bookingpress_add_cc_email_address_func($bookingpress_cc_emails, $email_notification_name){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_staffmembers, $tbl_bookingpress_notifications;
			if(!empty($email_notification_name)){
				$email_notification_data = $wpdb->get_row( $wpdb->prepare( "SELECT `bookingpress_notification_cc_email` FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_receiver_type = 'employee' ORDER BY bookingpress_notification_id DESC", $email_notification_name ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is table name.

				if ( ! empty( $email_notification_data['bookingpress_notification_cc_email'] ) ) {
					$bookingpress_cc_emails = explode( ',', $email_notification_data['bookingpress_notification_cc_email'] );
				}
			}
			return $bookingpress_cc_emails;
		}

		function bookingpress_modify_email_content_filter_func( $template_content, $bookingpress_appointment_data ) {
			
			global $BookingPress,$wpdb,$tbl_bookingpress_customers,$bookingpress_pro_staff_members,$bookingpress_global_options, $tbl_bookingpress_appointment_meta,$bookingpress_pro_appointment,$tbl_bookingpress_form_fields,$tbl_bookingpress_payment_logs, $BookingPressPro;

			$bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();
			$default_date_format      = $bookingpress_global_data['wp_default_date_format'];
			$default_time_format      = $bookingpress_global_data['wp_default_time_format'];			
			$bookingpress_appointment_status_arr = $bookingpress_global_data['appointment_status'];
			

			if(!empty($bookingpress_appointment_data)) {

				/* replacing the advanced appointment data */
				$bookingpress_appointment_id = !empty($bookingpress_appointment_data['bookingpress_appointment_booking_id']) ? intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']) : 0;				
				$bookingpress_payment_log_id = !empty($bookingpress_appointment_data['bookingpress_payment_id']) ? intval($bookingpress_appointment_data['bookingpress_payment_id']) : 0;			
				
				
				$bookingpress_appointment_date   = ! empty( $bookingpress_appointment_data['bookingpress_appointment_date'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_date'] ) : '';
				$bookingpress_appointment_start_time = ! empty( $bookingpress_appointment_data['bookingpress_appointment_time'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_time'] ) : '';
				$bookingpress_appointment_end_time = ! empty( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) : '';
				$bookingpress_appointment_duration = ! empty( $bookingpress_appointment_data['bookingpress_service_duration_val'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_service_duration_val'] ) : '';
				$bookingpress_appointment_details = $bookingpress_pro_appointment->bookingpress_calculated_appointment_details($bookingpress_appointment_id, $bookingpress_payment_log_id);

				if('d' != $bookingpress_appointment_data['bookingpress_service_duration_unit']) {
					$bookingpress_tmp_start_time = new DateTime($bookingpress_appointment_start_time);
					$bookingpress_tmp_end_time = new DateTime($bookingpress_appointment_end_time);
					$booking_date_interval = $bookingpress_tmp_start_time->diff($bookingpress_tmp_end_time);
					$bookingpress_minute = $booking_date_interval->format('%i');
					$bookingpress_hour = $booking_date_interval->format('%h');  
					$bookingpress_days = $booking_date_interval->format('%d');
					$bookingpress_appointment_duration = '';

					if($bookingpress_minute > 0) {
						$bookingpress_appointment_duration = $bookingpress_minute.' ' . esc_html__('Minutes', 'bookingpress-appointment-booking'); 
					}
					if($bookingpress_hour > 0 ) {
						$bookingpress_appointment_duration = $bookingpress_hour.' ' . esc_html__('Hours', 'bookingpress-appointment-booking').' '.$bookingpress_appointment_duration;
					}
					if($bookingpress_days == 1) {
						$bookingpress_appointment_duration = '24 ' . esc_html__('Hours', 'bookingpress-appointment-booking');
					}
					
				}else{
					$bookingpress_appointment_duration .= ' ' . esc_html__( 'Days', 'bookingpress-appointment-booking' ); 
				}				
                $bookingpress_appointment_number_of_person = !empty($bookingpress_appointment_data['bookingpress_selected_extra_members']) ? $bookingpress_appointment_data['bookingpress_selected_extra_members'] : '';
				$bookingpress_appointment_amount = !empty($bookingpress_appointment_details['final_total_amount_with_currency']) ? $bookingpress_appointment_details['final_total_amount_with_currency'] : '-';
				$bookingpress_appointment_date       = date_i18n( $default_date_format, strtotime( $bookingpress_appointment_date ) );
				$bookingpress_appointment_start_time = date( $default_time_format, strtotime( $bookingpress_appointment_start_time ) );
				$bookingpress_appointment_date_time  = $bookingpress_appointment_date . ' ' . $bookingpress_appointment_start_time;
				$bookingpress_appointment_end_time =  date( $default_time_format, strtotime( $bookingpress_appointment_end_time ) );

				$bookingpress_appointment_status= !empty( $bookingpress_appointment_data['bookingpress_appointment_status'] ) ? intval( $bookingpress_appointment_data['bookingpress_appointment_status'] ) : '-';
				foreach($bookingpress_appointment_status_arr as $bookingpress_appointment_status_key => $bookingpress_appointment_status_vals){
					if($bookingpress_appointment_status_vals['value'] == $bookingpress_appointment_status){
						$bookingpress_appointment_status = $bookingpress_appointment_status_vals['text'];
						break;
					}
				}				
				$log_data = array();
                if (!empty($bookingpress_payment_log_id) && $bookingpress_payment_log_id != 0) {
                    $log_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_refund_amount,bookingpress_payment_currency FROM " . $tbl_bookingpress_payment_logs . " WHERE `bookingpress_payment_log_id`= %d",$bookingpress_payment_log_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
                }
                $bookingpress_refund_amount = !empty($log_data['bookingpress_refund_amount']) ? $log_data['bookingpress_refund_amount'] : '';
				$bookingpress_currency = !empty($log_data['bookingpress_payment_currency']) ? esc_html($log_data['bookingpress_payment_currency']) : '';
                $bookingpress_currency_symbol = $BookingPress->bookingpress_get_currency_symbol($bookingpress_currency);
                $bookingpress_refund_amount = ! empty($bookingpress_refund_amount) ? $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_refund_amount, $bookingpress_currency_symbol) : 0;	

				$bookingpress_extra_serive_details_arr = !empty($bookingpress_appointment_data['bookingpress_extra_service_details']) ? json_decode( $bookingpress_appointment_data['bookingpress_extra_service_details'],true) : array();


				if(!empty( $bookingpress_extra_serive_details_arr)){

					$bookingpress_service_extra_content = "<table border='1' cellpadding='10' cellspacing='0' style='border-color:#ccc'>";
					foreach( $bookingpress_extra_serive_details_arr as $extra_service_key=>$extra_service_val ){

						$bookingpress_extra_service_name = !empty($extra_service_val['bookingpress_extra_service_details']['bookingpress_extra_service_name']) ? esc_html($extra_service_val['bookingpress_extra_service_details']['bookingpress_extra_service_name']) : '';

						if(!empty($extra_service_val['bookingpress_extra_service_details']['bookingpress_extra_service_name']) && !empty($extra_service_val['bookingpress_extra_service_details']['bookingpress_extra_services_id'])){
							$bookingpress_extra_service_name =  $BookingPressPro->bookingpress_pro_front_language_translation_func($extra_service_val['bookingpress_extra_service_details']['bookingpress_extra_service_name'],'service_extra','bookingpress_extra_service_name',$extra_service_val['bookingpress_extra_service_details']['bookingpress_extra_services_id']);  
						}

						$bookingpress_extra_service_qty = !empty($extra_service_val['bookingpress_selected_qty']) ? intval($extra_service_val['bookingpress_selected_qty']) : '';
						$bookingpress_extra_service_price = !empty($extra_service_val['bookingpress_final_payable_price']) ? floatval($extra_service_val['bookingpress_final_payable_price']) : '';  
						$bookingpress_service_price_with_currency = ! empty($bookingpress_extra_service_price) ? $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_extra_service_price, $bookingpress_currency_symbol) : 0;	
							
							$bookingpress_service_extra_content .= "<tr>";
								$bookingpress_service_extra_content .= "<td>".$bookingpress_extra_service_name." </td>";
								$bookingpress_service_extra_content .= "<td>".$bookingpress_extra_service_qty."</td>";
								$bookingpress_service_extra_content .= "<td>".$bookingpress_service_price_with_currency."</td>";
							$bookingpress_service_extra_content .= "</tr>";
					 }
					 $bookingpress_service_extra_content .= "</table>";

				} else {
					$bookingpress_service_extra_content = '';
				}

                
				$template_content  = str_replace( '%appointment_date_time%', $bookingpress_appointment_date_time, $template_content );
				$template_content  = str_replace( '%appointment_duration%', $bookingpress_appointment_duration, $template_content );
				$template_content  = str_replace( '%appointment_start_time%', $bookingpress_appointment_start_time, $template_content );
				$template_content  = str_replace( '%appointment_end_time%', $bookingpress_appointment_end_time, $template_content );
				$template_content  = str_replace( '%appointment_amount%', $bookingpress_appointment_amount, $template_content );
				$template_content  = str_replace( '%appointment_status%', $bookingpress_appointment_status, $template_content );
				$template_content  = str_replace( '%number_of_person%', $bookingpress_appointment_number_of_person, $template_content );
				$template_content = str_replace('%refund_amount%',$bookingpress_refund_amount,$template_content);
				$template_content = str_replace('%service_extras%',$bookingpress_service_extra_content,$template_content);

				/* deposit amount */
				$bookingpress_deposit_amount = !empty($bookingpress_appointment_details['deposit_price_with_currency']) ? $bookingpress_appointment_details['deposit_price_with_currency'] : 0;
				$template_content = str_replace('%deposit_amount%', $bookingpress_deposit_amount, $template_content);

				$bookingpress_due_amount = !empty($bookingpress_appointment_details['due_amount_with_currency']) ? $bookingpress_appointment_details['due_amount_with_currency'] : '-';
				$template_content = str_replace('%appointment_due_amount%', $bookingpress_due_amount, $template_content);

				$bookingpress_tax_amount = !empty($bookingpress_appointment_details['bookingpress_tax_amount_with_currency']) ? $bookingpress_appointment_details['bookingpress_tax_amount_with_currency'] : '-';
				$bookingpress_discount_amount = !empty($bookingpress_appointment_details['coupon_discount_amt_with_currency']) ? $bookingpress_appointment_details['coupon_discount_amt_with_currency'] : '-';

				$bookingpress_coupon_code = !empty( $bookingpress_appointment_details['applied_coupon'] ) ? $bookingpress_appointment_details['applied_coupon'] : '-';

				$template_content = str_replace('%tax_amount%', $bookingpress_tax_amount, $template_content);
				$template_content = str_replace('%discount_amount%', $bookingpress_discount_amount, $template_content);
				$template_content = str_replace('%coupon_code%', $bookingpress_coupon_code, $template_content);

				/***** replacing the advanced appointment data *****/

				/* replacing the staffmember data */

				$is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
				$bookingpress_staffmember_email = $bookingpress_staffmember_firstname = $bookingpress_staffmember_fullname = $bookingpress_staffmember_lastname =
				$bookingpress_staffmember_phone = '';

				if ( $is_staffmember_module_activated ) {
					$bookingpress_staffmember_data = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'],true) : array();
					$bookingpress_staffmember_firstname = !empty($bookingpress_staffmember_data['bookingpress_staffmember_firstname']) ? esc_html($bookingpress_staffmember_data['bookingpress_staffmember_firstname']) : '';
					$bookingpress_staffmember_lastname = !empty($bookingpress_staffmember_data['bookingpress_staffmember_lastname']) ? esc_html($bookingpress_staffmember_data['bookingpress_staffmember_lastname']) : '';
					$bookingpress_staffmember_email = !empty($bookingpress_staffmember_data['bookingpress_staffmember_email']) ? esc_html($bookingpress_staffmember_data['bookingpress_staffmember_email']) : '';
					$bookingpress_staffmember_fullname = $bookingpress_staffmember_firstname.' '.$bookingpress_staffmember_lastname;
					$bookingpress_staffmember_phone = !empty($bookingpress_staffmember_data['bookingpress_staffmember_phone']) ? esc_html($bookingpress_staffmember_data['bookingpress_staffmember_phone']) : '';                                                          
					if(!empty($bookingpress_staffmember_data['bookingpress_staffmember_country_dial_code'])) {						
						$bookingpress_staffmember_phone = "+".$bookingpress_staffmember_data['bookingpress_staffmember_country_dial_code']." ".$bookingpress_staffmember_phone;
					}					
				}

				$template_content = str_replace( '%staff_member_email%', $bookingpress_staffmember_email, $template_content );
				$template_content = str_replace( '%staff_member_first_name%', $bookingpress_staffmember_firstname, $template_content );
				$template_content = str_replace( '%staff_member_full_name%', $bookingpress_staffmember_fullname, $template_content );
				$template_content = str_replace( '%staff_member_last_name%', $bookingpress_staffmember_lastname, $template_content );
				$template_content = str_replace( '%staff_member_phone%', $bookingpress_staffmember_phone, $template_content );
				
				$template_content = str_replace( '%staff_email%', $bookingpress_staffmember_email, $template_content );
				$template_content = str_replace( '%staff_first_name%', $bookingpress_staffmember_firstname, $template_content );
				$template_content = str_replace( '%staff_full_name%', $bookingpress_staffmember_fullname, $template_content );
				$template_content = str_replace( '%staff_last_name%', $bookingpress_staffmember_lastname, $template_content );
				$template_content = str_replace( '%staff_phone%', $bookingpress_staffmember_phone, $template_content );

				/***** replacing the staffmember data *****/

				/***** replacing the customer field data *****/

				$bookingpress_appointment_custom_fields_meta_values = array();
				$bookingpress_appointment_custom_fields_meta_values = $bookingpress_pro_appointment->bookingpress_get_appointment_form_field_data($bookingpress_appointment_id);
				if(!empty($bookingpress_appointment_custom_fields_meta_values)){
					foreach($bookingpress_appointment_custom_fields_meta_values as $k2 => $v2) {
						$bookingpress_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_type,bookingpress_field_options,bookingpress_field_values FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_meta_key = %s AND bookingpress_is_customer_field = %d", $k2,0) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
						//Replace all custom fields values
						if(!empty($bookingpress_field_data) && $bookingpress_field_data->bookingpress_field_type == 'date' && !empty($bookingpress_field_data->bookingpress_field_options) && !empty($v2)) {
							$bookingpress_field_options = json_decode($bookingpress_field_data->bookingpress_field_options,true);
							if(!empty($bookingpress_field_options['enable_timepicker']) && $bookingpress_field_options['enable_timepicker'] == 'true') {
								$default_date_time_format = $default_date_format.' '.$default_time_format;  
								$v2 = date($default_date_time_format,strtotime($v2));
							} else {
								$v2 = date($default_date_format,strtotime($v2));
							}
						}
						if( is_array( $v2 ) ){
							$v2 = implode( ',', $v2 );
						}
						
						$template_content       = str_replace( '%'.$k2.'%', $v2, $template_content);
					}

					$bookingpress_existing_custom_fields = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_is_default = %d",0), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
					if(!empty($bookingpress_existing_custom_fields)){
						foreach($bookingpress_existing_custom_fields as $k3 => $v3){
							if(!array_key_exists($v3['bookingpress_field_meta_key'], $bookingpress_appointment_custom_fields_meta_values)){
								$template_content       = str_replace( '%'.$v3['bookingpress_field_meta_key'].'%', '', $template_content);
							}
						}
					}
				}else{
					$bookingpress_existing_custom_fields = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_is_default =%d",0), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
					if(!empty($bookingpress_existing_custom_fields)){
						foreach($bookingpress_existing_custom_fields as $k3 => $v3){
							$template_content       = str_replace( '%'.$v3['bookingpress_field_meta_key'].'%', '', $template_content);
						}
					}
				}


				/* Reschedule Start Here */				
				
				$bpa_unique_id = !empty($bookingpress_appointment_data['bookingpress_appointment_token']) ? $bookingpress_appointment_data['bookingpress_appointment_token'] : '';
				if(empty($bpa_unique_id)) {
					global $tbl_bookingpress_appointment_bookings;
					$bpa_unique_id = $BookingPress->bookingpress_generate_token();                        					
					$wpdb->update($tbl_bookingpress_appointment_bookings,array('bookingpress_appointment_token' => $bpa_unique_id),array('bookingpress_appointment_booking_id' => $bookingpress_appointment_id));
				}				
				$appointment_reschedule_page = $BookingPress->bookingpress_get_customize_settings('appointment_reschedule_page','booking_my_booking');
				if(!empty($appointment_reschedule_page)){
					$bookingpress_appointment_reschedule_confirmation_url = get_permalink($appointment_reschedule_page);                       
				}
				$bookingpress_reschedule_appointment_link = !empty($bookingpress_appointment_reschedule_confirmation_url) ? $bookingpress_appointment_reschedule_confirmation_url :BOOKINGPRESS_HOME_URL;
				$bookingpress_reschedule_appointment_link    = add_query_arg('appointment_id', base64_encode($bookingpress_appointment_data['bookingpress_appointment_booking_id']), $bookingpress_reschedule_appointment_link);
				$bookingpress_reschedule_appointment_link = add_query_arg( 'reschedule_token',$bpa_unique_id, $bookingpress_reschedule_appointment_link );
				$template_content = str_replace('%reschedule_appointment_url%', $bookingpress_reschedule_appointment_link, $template_content);

				/* Reschedule Over Here */


			}			

			return $template_content;
		}

		function bookingpress_handle_custom_email_notification( $cron_time ) {
			global $bookingpress_notifications,$tbl_bookingpress_customers,$wpdb,$bookingpress_email_notifications,$BookingPress;
			$bookingpress_total_custom_notification = array();
			$bookingpress_total_custom_notification = $this->bookingpress_get_all_custom_email_notification_details();
			if ( ! empty( $bookingpress_total_custom_notification ) ) {
				foreach ( $bookingpress_total_custom_notification as $bookingpress_total_custom_notification_key => $bookingpress_total_custom_notification_value ) {

					$bookingpress_notification_id = ! empty( $bookingpress_total_custom_notification_value['bookingpress_notification_id'] ) ? intval( $bookingpress_total_custom_notification_value['bookingpress_notification_id'] ) : '';

					$bookingpress_notification_receiver_type  = ! empty( $bookingpress_total_custom_notification_value['bookingpress_notification_receiver_type'] ) ? sanitize_text_field( $bookingpress_total_custom_notification_value['bookingpress_notification_receiver_type'] ) : '';
					$bookingpress_notification_scheduler_type = ! empty( $bookingpress_total_custom_notification_value['bookingpress_notification_scheduled_type'] ) ? sanitize_text_field( $bookingpress_total_custom_notification_value['bookingpress_notification_scheduled_type'] ) : '';
					$bookingpress_notification_name           = ! empty( $bookingpress_total_custom_notification_value['bookingpress_notification_name'] ) ? sanitize_text_field( $bookingpress_total_custom_notification_value['bookingpress_notification_name'] ) : '';
					$bookingpress_notification_send_only_this = ! empty( $bookingpress_total_custom_notification_value['bookingpress_notification_send_only_this'] ) ? $bookingpress_total_custom_notification_value['bookingpress_notification_send_only_this'] : '';
					$bookingpress_notification_services       = '';

					if ( ! empty( $bookingpress_notification_send_only_this ) && $bookingpress_notification_send_only_this == 1 ) {
						$bookingpress_notification_services = ! empty( $bookingpress_total_custom_notification_value['bookingpress_notification_service'] ) ? $bookingpress_total_custom_notification_value['bookingpress_notification_service'] : '';
					}
					if ( ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_notification_receiver_type ) && ! empty( $bookingpress_notification_scheduler_type ) ) {
						$bookingpress_total_appointments = array();

						if ( $bookingpress_notification_scheduler_type == 'on_the_same_day' ) {
							$bookingpress_notification_duration_time = ! empty( $bookingpress_total_custom_notification_value['bookingpress_notification_duration_time'] ) ? sanitize_text_field( $bookingpress_total_custom_notification_value['bookingpress_notification_duration_time'] ) : '';
							$bookingpress_notification_duration_time = date( 'H:i:s', strtotime( $bookingpress_notification_duration_time ) );
							if ( ! empty( $bookingpress_notification_duration_time ) && $bookingpress_notification_duration_time == $cron_time ) {
								$time_duration                   = '';
								$time_unit                       = '';
								$appointment_date                = date( 'Y-m-d', strtotime( current_time( 'mysql' ) ) );
								$bookingpress_total_appointments = $this->bookingpress_get_all_appointments( $time_duration, $time_unit, $bookingpress_notification_scheduler_type, $bookingpress_notification_services, $appointment_date );
							}
						} elseif ( $bookingpress_notification_scheduler_type == 'after' || $bookingpress_notification_scheduler_type == 'before' ) {
							$bookingpress_notification_duration_val  = ! empty( $bookingpress_total_custom_notification_value['bookingpress_notification_duration_val'] ) ? sanitize_text_field( $bookingpress_total_custom_notification_value['bookingpress_notification_duration_val'] ) : '';
							$bookingpress_notification_duration_unit = ! empty( $bookingpress_total_custom_notification_value['bookingpress_notification_duration_unit'] ) ? sanitize_text_field( $bookingpress_total_custom_notification_value['bookingpress_notification_duration_unit'] ) : '';

							$bookingpress_notification_duration_unit_val = '';
							if ( ! empty( $bookingpress_notification_duration_unit ) ) {
								switch ( strtolower( $bookingpress_notification_duration_unit ) ) {
									case 'h':
										$bookingpress_notification_duration_unit_val = 'HOUR';
										break;
									case 'd':
										$bookingpress_notification_duration_unit_val = 'DAY';
										break;
									case 'w':
										$bookingpress_notification_duration_unit_val = 'WEEK';
										break;
									case 'month':
										$bookingpress_notification_duration_unit_val = 'MONTH';
										break;
								}
								$bookingpress_total_appointments = $this->bookingpress_get_all_appointments( $bookingpress_notification_duration_val, $bookingpress_notification_duration_unit_val, $bookingpress_notification_scheduler_type, $bookingpress_notification_services );
							}
						}
						if ( ! empty( $bookingpress_total_appointments ) ) {
							foreach ( $bookingpress_total_appointments as $bookingpress_total_appointments_key => $bookingpress_total_appointments_val ) {
								$bookingpress_appointment_id = ! empty( $bookingpress_total_appointments_val['bookingpress_appointment_booking_id'] ) ? intval( $bookingpress_total_appointments_val['bookingpress_appointment_booking_id'] ) : '';

								if ( ! empty( $bookingpress_appointment_id ) ) {
									$bookingpress_customer_id = ! empty( $bookingpress_total_appointments_val['bookingpress_customer_id'] ) ? intval( $bookingpress_total_appointments_val['bookingpress_customer_id'] ) : '';
									if ( $bookingpress_notification_receiver_type == 'employee' ) {
										$bookingpress_admin_emails = $BookingPress->bookingpress_get_settings( 'admin_email', 'notification_setting' );
										if ( ! empty( $bookingpress_admin_emails ) ) {
											$bookingpress_admin_emails = explode( ',', $bookingpress_admin_emails );
											foreach ( $bookingpress_admin_emails as $admin_email_key => $admin_email_val ) {
												$is_email_sent = $this->bookingpress_get_send_custom_notification_entry_by_email( $admin_email_val, $bookingpress_appointment_id, $bookingpress_notification_id );
												if ( empty( $is_email_sent ) ) {
													$bookingpress_email_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', $bookingpress_notification_name, $bookingpress_appointment_id, $admin_email_val );
													$mail_sent              = $bookingpress_email_res['is_mail_sent'];
													if ( $mail_sent ) {
														 $this->bookingpress_add_custom_notification_entry_by_email( $admin_email_val, $bookingpress_appointment_id, $bookingpress_notification_id, 'success' );
													} else {
														$this->bookingpress_add_custom_notification_entry_by_email( $admin_email_val, $bookingpress_appointment_id, $bookingpress_notification_id, 'failed' );
													}
												}
											}
										}
									} elseif ( ! empty( $bookingpress_customer_id ) && $bookingpress_notification_receiver_type == 'customer' ) {
										$customer_email_details      = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_user_email FROM {$tbl_bookingpress_customers} WHERE bookingpress_customer_id = %d", $bookingpress_customer_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm
										$bookingpress_customer_email = ! empty( $customer_email_details['bookingpress_user_email'] ) ? $customer_email_details['bookingpress_user_email'] : '';

										$is_email_sent = $this->bookingpress_get_send_custom_notification_entry_by_email( $bookingpress_customer_email, $bookingpress_appointment_id, $bookingpress_notification_id );

										if ( ! empty( $bookingpress_customer_email ) && empty( $is_email_sent ) ) {
											$bookingpress_email_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_customer_email );
											$mail_sent              = $bookingpress_email_res['is_mail_sent'];
											if ( $mail_sent ) {
												$this->bookingpress_add_custom_notification_entry_by_email( $bookingpress_customer_email, $bookingpress_appointment_id, $bookingpress_notification_id, 'success' );
											} else {
												$this->bookingpress_add_custom_notification_entry_by_email( $bookingpress_customer_email, $bookingpress_appointment_id, $bookingpress_notification_id, 'failed' );
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		function bookingpress_get_all_appointments( $time_duration = '', $time_unit = '', $scheduled_type = '', $bookingpress_notification_services = '', $appointment_date = '' ) {
			global $tbl_bookingpress_appointment_bookings,$wpdb;
			$get_total_appointments = array();

			if ( ! empty( $scheduled_type ) && $scheduled_type == 'on_the_same_day' && ! empty( $appointment_date ) ) {
					$bookingpress_status             = array( 'pending', 'approved' );
					$bookingpress_search_query_where = ' WHERE 1=1 ';
				if ( ! empty( $bookingpress_notification_services ) ) {
					$bookingpress_search_query_where .= " AND (bookingpress_appointment_booking_id IN ({$bookingpress_notification_services}))";
				}
					$bookingpress_search_query_where .= " AND (bookingpress_appointment_status IN ('2','1'))";
					$bookingpress_search_query_where .= " AND (bookingpress_appointment_date = '{$appointment_date}')";
					$get_total_appointments           = $wpdb->get_results( 'SELECT *  FROM ' . $tbl_bookingpress_appointment_bookings . ' ' . $bookingpress_search_query_where, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			} else {
				if ( ! empty( $time_duration ) && ! empty( $time_unit ) ) {
					$bookingpress_status             = array( 'pending', 'approved' );
					$bookingpress_search_query_where = ' WHERE 1=1 ';
					if ( ! empty( $bookingpress_notification_services ) ) {
						$bookingpress_search_query_where .= " AND (bookingpress_appointment_id IN ({$bookingpress_notification_services}))";
					}
					if ( $scheduled_type == 'before' ) {
						$scheduled_type = '+';
					} elseif ( $scheduled_type == 'after' ) {
						$scheduled_type = '-';
					}
					if ( $time_unit == 'DAY' || $time_unit == 'WEEK' || $time_unit == 'MONTH' ) {
						$bookingpress_search_query_where .= " AND bookingpress_appointment_date = (CURRENT_DATE {$scheduled_type} INTERVAL {$time_duration} {$time_unit})";
					} elseif ( $time_unit == 'HOUR' ) {
						$date     = date( 'Y-m-d H:i:s', strtotime( $scheduled_type . $time_duration . 'hours', current_time( 'timestamp' ) ) );
						$bpa_date = date( 'Y-m-d', strtotime( $date ) );
						$bpa_time = date( 'H:i:00', strtotime( $date ) );
						// $bookingpress_search_query_where .= " AND ((bookingpress_appointment_time BETWEEN CURRENT_TIME {$scheduled_type} INTERVAL {$time_duration} {$time_unit} AND CURRENT_TIME) || CURRENT_TIME {$scheduled_type} INTERVAL {$time_duration} {$time_unit} = bookingpress_appointment_time) AND ( bookingpress_appointment_date = {$bpa_date})";
						$bookingpress_search_query_where .= " AND bookingpress_appointment_time <= '{$bpa_time}' AND bookingpress_appointment_date = '{$bpa_date}'";
					}
					$bookingpress_search_query_where .= " AND (bookingpress_appointment_status IN ('2','1'))";
					$get_total_appointments           = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_appointment_bookings . ' ' . $bookingpress_search_query_where, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
				}
			}
			return $get_total_appointments;
		}

		function bookingpress_get_all_custom_email_notification_details() {
			global $wpdb, $tbl_bookingpress_notifications;
			$bookingpress_notification_type         = 'custom';
			$bookingpress_total_custom_notification = array();
			$bookingpress_total_custom_notification = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_type = %s AND bookingpress_notification_is_custom = %d AND bookingpress_notification_status = %d", $bookingpress_notification_type, 1, 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

			return $bookingpress_total_custom_notification;
		}

		function bookingpress_get_send_custom_notification_entry_by_email( $user_email, $appointment_id, $notification_id ) {
			global $wpdb,$tbl_bookingpress_custom_send_notifications;
			$bookingpress_usermeta_details = array();
			if ( ! empty( $user_email ) && ! empty( $appointment_id ) ) {
				$bookingpress_usermeta_details = $wpdb->get_row( $wpdb->prepare( "SELECT notification_id FROM {$tbl_bookingpress_custom_send_notifications} WHERE bookingpress_user_email = %s AND bookingpress_appointment_id = %d AND bookingpress_notification_id = %d AND bookingpress_send_notification_status = %s", $user_email, $appointment_id, $notification_id, 'success' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_custom_send_notifications is a table name. false alarm
			}
			return $bookingpress_usermeta_details;
		}

		function bookingpress_add_custom_notification_entry_by_email( $user_email, $appointment_id, $notification_id, $notification_status ) {
			global $wpdb,$tbl_bookingpress_custom_send_notifications;
			$bookingpress_inserted_id = '';
			if ( ! empty( $user_email ) && ! empty( $appointment_id ) && ! empty( $notification_id ) ) {
					// If record not exists hen insert data.
				$bookingpress_insert_data = array(
					'bookingpress_user_email'      => $user_email,
					'bookingpress_appointment_id'  => intval( $appointment_id ),
					'bookingpress_notification_id' => intval( $notification_id ),
					'bookingpress_send_notification_status' => $notification_status,
				);
				$bookingpress_inserted_id = $wpdb->insert( $tbl_bookingpress_custom_send_notifications, $bookingpress_insert_data );
			}
			return $bookingpress_inserted_id;
		}

		function bookingpress_modify_email_notification_data_func( $email_notification_data, $template_type, $notification_name, $bookingpress_appointment_data,$notification_from = 'email' ) {

			if($notification_from == 'email') {
				return $email_notification_data;
			}

			global $wpdb,$tbl_bookingpress_notifications;
			$bpa_arr           = array(
				'Appointment Approved'  => 'appointment_approved',
				'Appointment Pending'   => 'appointment_pending',
				'Appointment Canceled'  => 'appointment_canceled',
				'Appointment Rejected'  => 'appointment_rejected',
			);

			$notification_type = !empty($email_notification_data['notification_type']) ? $email_notification_data['notification_type'] : 'default';

			if ( ! empty( $template_type ) && ! empty( $notification_name ) ) {
				if ( ! empty( $bpa_arr[ $notification_name ] ) ) {
					$notification_event_action = $bpa_arr[ $notification_name ];
					$service_id = ! empty( $bookingpress_appointment_data['bookingpress_service_id'] ) ? intval( $bookingpress_appointment_data['bookingpress_service_id'] ) : '';
					if($notification_from == 'email') {
						$bookingpress_email_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_notification_name,bookingpress_notification_service FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_receiver_type = %s AND bookingpress_notification_status = %d AND bookingpress_notification_type = %s AND bookingpress_notification_event_action = %s AND bookingpress_custom_notification_type = %s ORDER BY bookingpress_notification_id DESC", $template_type, 1, 'custom', $notification_event_action,'action-trigger' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
					} else {
						$bookingpress_email_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_notification_name,bookingpress_notification_service FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_receiver_type = %s AND bookingpress_notification_type = %s AND bookingpress_notification_event_action = %s AND bookingpress_custom_notification_type = %s ORDER BY bookingpress_notification_id DESC", $template_type,'custom', $notification_event_action,'action-trigger' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

						$bookingpress_email_data = apply_filters('bookingpress_send_all_custom_email_notifications',$bookingpress_email_data,$notification_from,$template_type,$notification_event_action);
					}

					if(empty($bookingpress_email_data)) {
						return $email_notification_data;
					}

					foreach ( $bookingpress_email_data as $bookingpress_email_data_key => $bookingpress_email_data_val ) {
						if ( ! empty( $bookingpress_email_data_val['bookingpress_notification_service'] ) ) {
							$bookingpress_notification_service_arr = explode( ',', $bookingpress_email_data_val['bookingpress_notification_service'] );
							if ( ! empty( $bookingpress_notification_service_arr ) && ( in_array( $service_id, $bookingpress_notification_service_arr ) || in_array( 'any', $bookingpress_notification_service_arr )) ) {
								$notification_type = 'custom';
								$notification_name = ! empty( $bookingpress_email_data_val['bookingpress_notification_name'] ) ? sanitize_text_field( $bookingpress_email_data_val['bookingpress_notification_name'] ) : '';
								break;
							}
						} else {
							$notification_type = 'custom';
							$notification_name = ! empty( $bookingpress_email_data_val['bookingpress_notification_name'] ) ? sanitize_text_field( $bookingpress_email_data_val['bookingpress_notification_name'] ) : '';
							break;
						}
					}
				}
			}
			$email_notification_data = array(
				'notification_type' => $notification_type,
				'notification_name' => $notification_name,
			);
			return $email_notification_data;
		}

		function bookingpress_attach_ics_file_with_email( $attachments, $email_template_details, $appointment_id, $template_type, $notification_name, $appointment_data ) {

			global $wpdb, $tbl_bookingpress_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_appointment_bookings, $BookingPress;

			if ( empty( $appointment_data ) ) {
				$appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
				if ( empty( $appointment_data ) ) {
					return $attachments;
				}
			}

			$bookingpress_email_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_receiver_type = %s AND bookingpress_notification_status = %d", $notification_name, $template_type, 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

			if ( ! empty( $bookingpress_email_data['bookingpress_notification_attach_ics_file'] ) && 1 == $bookingpress_email_data['bookingpress_notification_attach_ics_file'] ) {

				$service_id              = intval( $appointment_data['bookingpress_service_id'] );
				$bookingpress_start_time = sanitize_text_field( $appointment_data['bookingpress_appointment_time'] );
				$bookingpress_end_time        = sanitize_text_field( $appointment_data['bookingpress_appointment_end_time'] );

				$bookingpress_appointment_date_temp = $appointment_data['bookingpress_appointment_date'];
				if ($bookingpress_end_time === '24:00:00') {
					$bookingpress_appointment_date_temp = date('Y-m-d', strtotime($appointment_data['bookingpress_appointment_date'] . ' +1 day'));
					$bookingpress_end_time = '00:00:00';
				}

				$bookingpress_start_time = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_start_time ) );
				$bookingpress_end_time = date( 'Ymd', strtotime( $bookingpress_appointment_date_temp ) ) . 'T' . date( 'His', strtotime( $bookingpress_end_time ) );

				$user_timezone             = wp_timezone_string();
				$bookingpress_service_name = ! empty( $appointment_data['bookingpress_service_name'] ) ? stripslashes_deep($appointment_data['bookingpress_service_name']) : '';

				$booking_stime = $bookingpress_pro_appointment_bookings->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], $bookingpress_start_time );
				$booking_etime = $bookingpress_pro_appointment_bookings->bookingpress_convert_date_time_to_utc( $bookingpress_appointment_date_temp, $bookingpress_end_time  );
				$current_dtime = $bookingpress_pro_appointment_bookings->bookingpress_convert_date_time_to_utc( date( 'm/d/Y' ), 'g:i A' );

				$string  = "BEGIN:VCALENDAR\r\n";
				$string .= "VERSION:2.0\r\n";
				$string .= 'PRODID:BOOKINGPRESS APPOINTMENT BOOKING\\\\' . get_bloginfo('title') . "\r\n";
				$string .= "X-PUBLISHED-TTL:P1W\r\n";
				$string .= "BEGIN:VEVENT\r\n";
				$string .= 'UID:' . md5( time() ) . "\r\n";
				$string .= 'DTSTART:' . $booking_stime . "\r\n";
				$string .= "SEQUENCE:0\r\n";
				$string .= "TRANSP:OPAQUE\r\n";
				$string .= "DTEND:{$booking_etime}\r\n";
				$string .= "SUMMARY:{$bookingpress_service_name}\r\n";
				$string .= "CLASS:PUBLIC\r\n";
				$string .= "DTSTAMP:{$current_dtime}\r\n";
				$string .= "END:VEVENT\r\n";
				$string .= "END:VCALENDAR\r\n";

				if( 'customer' == $template_type ){
					$string  = apply_filters( 'bpa_add_timezone_parameters_for_ics', $string, $appointment_data );
				} else if( 'employee' == $template_type ) {
					$string  = apply_filters( 'bpa_add_timezone_parameters_for_ics_without_timezone', $string, $appointment_data );
				}

				$file_name = 'bookingpress_appointment.ics';				

				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}

				$destination = BOOKINGPRESS_PRO_UPLOAD_DIR . '/' . $file_name;

				WP_Filesystem();
				global $wp_filesystem;

				if ( ! $wp_filesystem->put_contents( $destination, $string, 0777 ) ) {
					return $attachments;
				}

				$attachments[] = $destination;

			}

			return $attachments;
		}
	}

	global $bookingpress_pro_email_notifications;
	$bookingpress_pro_email_notifications = new bookingpress_pro_email_notifications();
}
