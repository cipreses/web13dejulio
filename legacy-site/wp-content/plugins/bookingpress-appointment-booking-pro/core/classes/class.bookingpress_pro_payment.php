<?php
if ( ! class_exists( 'bookingpress_pro_payment' ) ) {
	class bookingpress_pro_payment Extends BookingPress_Core {
		var $bookingpress_global_data;
		function __construct() {

			add_filter( 'bookingpress_modify_payment_data_fields', array( $this, 'bookingpress_modify_payment_data_fields_func' ), 10 );
			add_filter( 'bookingpress_modify_payment_view_file_path', array( $this, 'bookingpress_modify_payment_file_path_func' ), 10 );
			add_action( 'bookingpress_payment_dynamic_bulk_action', array( $this, 'bookingpress_payment_dynamic_bulk_action_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_pro_bulk_payment_logs_action', array( $this, 'bookingpress_pro_bulk_payment_logs_action_func' ), 10 );

			add_action( 'bookingpress_payment_add_dynamic_vue_methods', array( $this, 'bookingpress_payment_add_dynamic_vue_methods_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_export_payment_data', array( $this, 'bookingpress_export_payment_data_func' ), 10 );
			add_filter( 'bookingpress_modify_modal_payment_log_details', array( $this, 'bookingpress_modify_modal_payment_log_details_func' ), 10 );

			add_filter('bookingpress_modify_payments_listing_data', array($this, 'bookingpress_modify_payments_listing_data_func'), 10, 1);

			add_action('bookingpress_dynamic_add_onload_payment_methods', array($this, 'bookingpress_dynamic_add_onload_payment_methods_func'));

			add_action('bookingpress_payment_reset_filter',array($this,'bookingpress_payment_reset_filter_func'));
			
			add_action('bookingpress_update_payment_details_externally_after_update_status', array($this, 'bookingpress_update_payment_details_externally_after_update_status_func'));

			//Modify complete payment url content
			add_filter('bookingpress_modify_email_notification_content', array( $this, 'bookingpress_modify_email_content_func' ), 12, 4);

			//Send complete payment url notification
			add_action('bookingpress_after_add_appointment_from_backend', array($this, 'bookingpress_send_complete_payment_url_notification'), 11, 3);

			add_action('wp_ajax_bookingpress_send_complete_payment_link', array($this, 'bookingpress_send_complete_payment_link_func'));

			add_action('bookingpress_after_approve_appointment',array($this,'bookingpress_after_approve_appointment_func'),10);
		}

		function bookingpress_after_approve_appointment_func($payment_data) {

			global $tbl_bookingpress_appointment_bookings,$wpdb,$bookingpress_email_notifications;
			$bookingpress_payment_log_id = !empty($payment_data['bookingpress_payment_log_id']) ? intval($payment_data['bookingpress_payment_log_id']) : 0;
			$bookingpress_is_cart = !empty($payment_data['bookingpress_is_cart']) ? intval($payment_data['bookingpress_is_cart']) : 0;
			$bookingpress_order_id = !empty($payment_data['bookingpress_order_id']) ? intval($payment_data['bookingpress_order_id']) : 0;
			$bookingpress_customer_email = ! empty($payment_data['bookingpress_customer_email']) ? $payment_data['bookingpress_customer_email'] : '';

			if(!empty($bookingpress_order_id) && $bookingpress_is_cart  == 1 ) {
				$bookingpress_appointment_data = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_payment_id = %d AND bookingpress_order_id = %d", $bookingpress_payment_log_id,$bookingpress_order_id ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
				foreach($bookingpress_appointment_data as $key => $val) {
					$bookingpress_appointment_booking_id = !empty( $val['bookingpress_appointment_booking_id'] ) ? $val['bookingpress_appointment_booking_id'] : 0;
					if(!empty($bookingpress_appointment_booking_id)) {
						$wpdb->update($tbl_bookingpress_appointment_bookings, array( 'bookingpress_appointment_status' => '1' ), array( 'bookingpress_appointment_booking_id' => $bookingpress_appointment_booking_id ));                            
						$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Appointment Approved', $bookingpress_appointment_booking_id, $bookingpress_customer_email);
						do_action('bookingpress_after_change_appointment_status', $bookingpress_appointment_booking_id, '1');
					}
				}
			}

		}

		function bookingpress_send_complete_payment_link_func(){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications;
			$response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'send_payment_link', true, 'bpa_wp_nonce' );           
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
			$response['msg'] = esc_html__('Something went wrong while sending payment link', 'bookingpress-appointment-booking');

			$bookingpress_payment_log_id = !empty($_POST['payment_log_id']) ? intval($_POST['payment_log_id']) : 0; //phpcs:ignore
			$bookingpress_selected_options = !empty($_POST['selected_options']) ? $_POST['selected_options'] : array(); //phpcs:ignore
			if(!empty($bookingpress_payment_log_id) && !empty($bookingpress_selected_options) ){
				$bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_payment_id = %d", $bookingpress_payment_log_id), ARRAY_A); //phpcs:ignore

				$bookingpress_is_cart = !empty($bookingpress_appointment_details['bookingpress_is_cart']) ? intval($bookingpress_appointment_details['bookingpress_is_cart']) : 0;
				$bookingpress_order_id = 0;
				$bookingpress_appointment_id = $bookingpress_appointment_details['bookingpress_appointment_booking_id'];
				if($bookingpress_is_cart){
					$bookingpress_order_id = !empty($bookingpress_appointment_details['bookingpress_order_id']) ? intval($bookingpress_appointment_details['bookingpress_order_id']) : 0;

					if(!empty($bookingpress_order_id)){
						$bpa_uniq_token = uniqid("bpa", true);

						$wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_complete_payment_token' => $bpa_uniq_token), array('bookingpress_order_id' => $bookingpress_order_id));		
					}
				}else{
					$bpa_uniq_token = uniqid("bpa", true);

					$wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_complete_payment_token' => $bpa_uniq_token), array('bookingpress_appointment_booking_id' => $bookingpress_appointment_id));
				}

				if(in_array("email", $bookingpress_selected_options)){
					$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Complete Payment URL', $bookingpress_appointment_id, $bookingpress_appointment_details['bookingpress_customer_email']);
				}

				do_action('bookingpress_send_complete_payment_link_externally', $bookingpress_appointment_details, $bookingpress_selected_options);

				$response['variant'] = 'success';
				$response['title'] = esc_html__( 'Success', 'bookingpress-appointment-booking');
				$response['msg'] = esc_html__('Payment Link Send Successfully', 'bookingpress-appointment-booking');
			}else if(empty($bookingpress_selected_options)){
				$response['msg'] = esc_html__('Please select any method for send payment link', 'bookingpress-appointment-booking');
			}

			echo wp_json_encode($response);
			exit;
		}

		function bookingpress_send_complete_payment_url_notification($appointment_booking_id, $bookingpress_appointment_data, $entry_id){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications;
			$bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_customer_email FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_booking_id), ARRAY_A);//phpcs:ignore
			if(!empty($bookingpress_appointment_details['bookingpress_customer_email']) && !empty($_POST['appointment_data']['complete_payment_url_selection']) && $_POST['appointment_data']['complete_payment_url_selection'] == 'send_payment_link' && !empty($_POST['appointment_data']['complete_payment_url_selected_method'] && in_array('email',$_POST['appointment_data']['complete_payment_url_selected_method']))){ //phpcs:ignore
				$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification('Complete Payment URL', $appointment_booking_id, $bookingpress_appointment_details['bookingpress_customer_email']);
			}			
		}

		function bookingpress_generate_complete_payment_url($appointment_id, $payment_unique_token){
			global $BookingPress;
			$bookingpress_generated_complete_payment_url = "";
			if( !empty($appointment_id) && !empty($payment_unique_token) ){
				$bookingpress_page_id = $BookingPress->bookingpress_get_settings('complete_payment_page_id','general_setting');
				if(!empty($bookingpress_page_id)){
					$bookingpress_generated_complete_payment_url = get_permalink($bookingpress_page_id);
					$bookingpress_generated_complete_payment_url = add_query_arg('bkp_pay', $payment_unique_token, $bookingpress_generated_complete_payment_url);
				}
			}

			return $bookingpress_generated_complete_payment_url;
		}
		
		/**
		 * Function for modify email content of complete payment url
		 *
		 * @param  mixed $template_content
		 * @param  mixed $bookingpress_appointment_data
		 * @param  mixed $notification_name
		 * @return void
		 */
		function bookingpress_modify_email_content_func($template_content, $bookingpress_appointment_data, $notification_name = '',$template_type=''){
			global $BookingPress;
			$bookingpress_complete_payment_url = "";

			$bookingpress_appointment_id = intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']);
			$bookingpress_payment_uniq_token = !empty($bookingpress_appointment_data['bookingpress_complete_payment_token']) ? $bookingpress_appointment_data['bookingpress_complete_payment_token'] : '';

			if(!empty($bookingpress_payment_uniq_token)){
				$bookingpress_complete_payment_url = $this->bookingpress_generate_complete_payment_url($bookingpress_appointment_id, $bookingpress_payment_uniq_token);
			}

			$template_content = str_replace('%complete_payment_url%', $bookingpress_complete_payment_url, $template_content);

			return $template_content;
		}

		function bookingpress_update_payment_details_externally_after_update_status_func($bookingpress_posted_data){
			global $wpdb, $BookingPress, $tbl_bookingpress_payment_logs;
			$bookingpress_payment_log_id = !empty($bookingpress_posted_data['payment_log_id']) ? intval($bookingpress_posted_data['payment_log_id']) : 0;
			$bookingpress_payment_status = !empty($bookingpress_posted_data['payment_status']) ? sanitize_text_field($bookingpress_posted_data['payment_status']) : '';

			if( !empty($bookingpress_payment_log_id) && !empty($bookingpress_payment_status) && ($bookingpress_payment_status == 1) ){
				$wpdb->update($tbl_bookingpress_payment_logs, array('bookingpress_mark_as_paid' => 1), array('bookingpress_payment_log_id' => $bookingpress_payment_log_id));
			}
		}

		function bookingpress_payment_reset_filter_func(){
			?>
			vm.search_data.search_staff_member = '';
			<?php
		}

		function bookingpress_calculate_payment_details($payment_log_id){
			global $BookingPress, $wpdb, $tbl_bookingpress_payment_logs, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_staff_members, $bookingpress_global_options;

			$bookingpress_global_settings = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_default_date_format = $bookingpress_global_settings['wp_default_date_format'];
			$bookingpress_default_time_format = $bookingpress_global_settings['wp_default_time_format'];

			$payment_logs_data = array();

			$payment_log_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = %d", $payment_log_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

			$bookingpress_selected_currency = '';
			if(!empty($payment_log_details)){
				$bookingpress_payment_status = $payment_log_details['bookingpress_payment_status'];
				$bookingpress_selected_gateway = $payment_log_details['bookingpress_payment_gateway'];
				$bookingpress_tax_percentage = $payment_log_details['bookingpress_tax_percentage'];
				$bookingpress_tax_amount = floatval($payment_log_details['bookingpress_tax_amount']);
				$bookingpress_coupon_details = ( !empty( $payment_log_details['bookingpress_coupon_details'] )  ) ? json_decode($payment_log_details['bookingpress_coupon_details'], TRUE) : array();
				$bookingpress_coupon_discount_amount = floatval($payment_log_details['bookingpress_coupon_discount_amount']);
				$bookingpress_deposit_details = $payment_log_details['bookingpress_deposit_payment_details'];
				$bookingpress_deposit_amount = $payment_log_details['bookingpress_deposit_amount'];
				$bookingpress_paid_amount = $payment_log_details['bookingpress_paid_amount'];
				$bookingpress_due_amount = $payment_log_details['bookingpress_due_amount'];
				$bookingpress_total_amount = $payment_log_details['bookingpress_total_amount'];
				$bookingpress_is_cart = intval($payment_log_details['bookingpress_is_cart']);
				$bookingpress_order_id = intval($payment_log_details['bookingpress_order_id']);

				$bookingpress_currency_name = $payment_log_details['bookingpress_payment_currency'];
				$bookingpress_selected_currency = $BookingPress->bookingpress_get_currency_symbol($bookingpress_currency_name);

				$bookingpress_selected_gateway_label = $bookingpress_selected_gateway;
				$bookingpress_selected_gateway_label = apply_filters('bookingpress_selected_gateway_label_name', $bookingpress_selected_gateway_label, $bookingpress_selected_gateway);
				$payment_logs_data['selected_gateway'] = $bookingpress_selected_gateway;
				$payment_logs_data['selected_gateway_label'] = $bookingpress_selected_gateway_label;
				$payment_logs_data['payment_status'] = $bookingpress_payment_status;
				
				$payment_logs_data['is_cart'] = $bookingpress_is_cart;
				$payment_logs_data['order_id'] = $bookingpress_order_id;
				
				if($bookingpress_is_cart == 1){
					$payment_logs_data['staff_member_name'] = ' - ';
					$payment_logs_data['payment_service'] = ' - ';
					$payment_logs_data['appointment_date'] = ' - ';
				}

				//Returns tax amount
				$payment_logs_data['tax_amount'] = $bookingpress_tax_amount;
				$payment_logs_data['tax_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_tax_amount, $bookingpress_selected_currency);

				//Returns coupon details
				$payment_logs_data['coupon_discount_amount'] = $bookingpress_coupon_discount_amount;
				$payment_logs_data['coupon_discount_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_coupon_discount_amount, $bookingpress_selected_currency);
				$bookingpress_applied_coupon_code = "";
				if(!empty($bookingpress_coupon_details) && is_array($bookingpress_coupon_details) ){
					$bookingpress_applied_coupon_code = !empty($bookingpress_coupon_details['bookingpress_coupon_code']) ?$bookingpress_coupon_details['bookingpress_coupon_code'] : '';
					if(empty($bookingpress_applied_coupon_code) && !empty($bookingpress_coupon_details['coupon_data']['bookingpress_coupon_code'])){
						$bookingpress_applied_coupon_code = $bookingpress_coupon_details['coupon_data']['bookingpress_coupon_code'];
					}
				}
				$payment_logs_data['applied_coupon_code'] = $bookingpress_applied_coupon_code;


				$bookingpress_is_deposit_enable = 0;
				$bookingpress_subtotal_amount = 0;

				if(!empty($payment_log_details)){
					$bookingpress_appointment_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_payment_id = %d", $payment_log_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
					
					if(!empty($bookingpress_appointment_details) && is_array($bookingpress_appointment_details)){
						foreach($bookingpress_appointment_details as $k2 => $v2){							
							
							$bookingpress_appointment_details[$k2]['bookingpress_service_name'] = !empty($bookingpress_appointment_details[$k2]['bookingpress_service_name']) ? stripslashes_deep($bookingpress_appointment_details[$k2]['bookingpress_service_name']) :'';
							if(isset($v2['bookingpress_enable_custom_duration'])  && $v2['bookingpress_enable_custom_duration'] == 1) {
								if( !empty( $v2['bookingpress_staff_member_details'] ) && !empty( $v2['bookingpress_staff_member_price'] ) ){                 
									$v2['bookingpress_staff_member_price'] = $v2['bookingpress_service_price'];									
								}
							}

							if( !empty( $v2['bookingpress_staff_member_details'] ) && !empty( $v2['bookingpress_staff_member_price'] ) ){
								$v2['bookingpress_service_price'] = $v2['bookingpress_staff_member_price'];
							}

							$bookingpress_tmp_subtotal_amount = $bookingpress_service_price = $v2['bookingpress_service_price'];
							$bookingpress_service_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_service_price, $bookingpress_selected_currency);
							$bookingpress_appointment_details[$k2]['bookingpress_service_price_with_currency'] = $bookingpress_service_with_currency;

							$bookingpress_appointment_details[$k2]['bookingpress_appointment_date'] = date_i18n($bookingpress_default_date_format, strtotime($v2['bookingpress_appointment_date']));
							$bookingpress_appointment_details[$k2]['bookingpress_appointment_time'] = date($bookingpress_default_time_format, strtotime($v2['bookingpress_appointment_time']));
							$bookingpress_appointment_details[$k2]['bookingpress_appointment_end_time'] = date($bookingpress_default_time_format, strtotime($v2['bookingpress_appointment_end_time']));
							$bookingpress_appointment_details[$k2]['bookingpress_selected_extra_members'] = intval($bookingpress_appointment_details[$k2]['bookingpress_selected_extra_members']);

							//Check Deposit Applied or Not
							$bookingpress_appointment_details[$k2]['bookingpress_deposit_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_deposit_amount, $bookingpress_selected_currency);
							$bookingpress_deposit_details = !empty($v2['bookingpress_deposit_payment_details']) ? json_decode($v2['bookingpress_deposit_payment_details'], TRUE) : array();
							if(!empty($bookingpress_deposit_details) && !empty($bookingpress_deposit_details['deposit_selected_type']) && ($bookingpress_selected_gateway != 'on-site') ){
								$bookingpress_is_deposit_enable = $bookingpress_appointment_details[$k2]['is_deposit_applied'] = 1;
							}

							$bookingpress_staffmember_id = $v2['bookingpress_staff_member_id'];
							$bookingpress_staff_avatar_url = "";
							if(!empty($bookingpress_staffmember_id)){
								$bookingpress_staffmember_avatar = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta($bookingpress_staffmember_id, 'staffmember_avatar_details');
								$bookingpress_staffmember_avatar = !empty($bookingpress_staffmember_avatar) ? maybe_unserialize($bookingpress_staffmember_avatar) : array();
								if (!empty($bookingpress_staffmember_avatar[0]['url'])) {
									$bookingpress_staff_avatar_url = $bookingpress_staffmember_avatar[0]['url'];
								}else{
									$bookingpress_staff_avatar_url = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
								}

								$bookingpress_tmp_subtotal_amount = isset($v2['bookingpress_staff_member_price']) ? floatval($v2['bookingpress_staff_member_price']) : $bookingpress_tmp_subtotal_amount;
							}
							
							$bookingpress_appointment_details[$k2]['staff_avatar_url'] = $bookingpress_staff_avatar_url;

							$bookingpress_selected_bring_anyone_members = intval($v2['bookingpress_selected_extra_members']) - 1;
							if(!empty($bookingpress_selected_bring_anyone_members)){
								$bookingpress_tmp_subtotal_amount = $bookingpress_tmp_subtotal_amount + ($bookingpress_tmp_subtotal_amount * $bookingpress_selected_bring_anyone_members);
							}

							$bookingpress_extra_total = 0;
							$bookingpress_extra_service_details = !empty($v2['bookingpress_extra_service_details']) ? json_decode($v2['bookingpress_extra_service_details'], TRUE) : array();
							$bookingpress_extra_service_data = array();
							if(!empty($bookingpress_extra_service_details)){
								foreach($bookingpress_extra_service_details as $k3 => $v3){
									$bookingpress_extra_total = $bookingpress_extra_total + $v3['bookingpress_final_payable_price'];
									$bookingpress_extra_service_price = ($v3['bookingpress_extra_service_details']['bookingpress_extra_service_price']) * ($v3['bookingpress_selected_qty']);
									$bookingpress_extra_service_data[] = array(
										'selected_qty' => $v3['bookingpress_selected_qty'],
										'extra_name' => $v3['bookingpress_extra_service_details']['bookingpress_extra_service_name'],
										'extra_service_price' => $bookingpress_extra_service_price,
										'extra_service_price_with_currency' => $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_extra_service_price, $bookingpress_selected_currency),
									);
								}
							}
							$bookingpress_appointment_details[$k2]['extra_service_details'] = $bookingpress_extra_service_data;

							$bookingpress_tmp_subtotal_amount = $bookingpress_tmp_subtotal_amount + $bookingpress_extra_total;

							$bookingpress_tmp_subtotal_amount = apply_filters('bookingpress_modify_outside_sub_total_amount_appointment_details',$bookingpress_tmp_subtotal_amount, $bookingpress_appointment_details[$k2], $bookingpress_selected_currency);
							

							$bookingpress_subtotal_amount = $bookingpress_subtotal_amount + $bookingpress_tmp_subtotal_amount;
						}

						$payment_logs_data['appointment_details'] = $bookingpress_appointment_details;
					}
					$payment_logs_data['is_deposit_enable'] = $bookingpress_is_deposit_enable;
				}

				$bookingpress_price_display_setting = !empty($payment_log_details['bookingpress_price_display_setting']) ? $payment_log_details['bookingpress_price_display_setting'] : 'exclude_taxes';
				$payment_logs_data['price_display_setting'] = $bookingpress_price_display_setting;

				$bookingpress_tax_amount_in_order_summary = !empty($payment_log_details['bookingpress_display_tax_order_summary']) ? 'true' : 'false';
				$payment_logs_data['display_tax_amount_in_order_summary'] = $bookingpress_tax_amount_in_order_summary;

				$bookingpress_included_tax_label = !empty($payment_log_details['bookingpress_included_tax_label']) ? $payment_log_details['bookingpress_included_tax_label'] : '';
				$payment_logs_data['included_tax_label'] = $bookingpress_included_tax_label;
				
				if( !empty($bookingpress_price_display_setting) && $bookingpress_price_display_setting == "exclude_taxes" ){
					$bookingpress_final_total_amount = ($bookingpress_subtotal_amount + $bookingpress_tax_amount) - $bookingpress_coupon_discount_amount;	
				}else{
					$bookingpress_final_total_amount = $bookingpress_subtotal_amount - $bookingpress_coupon_discount_amount;
				}

				if($bookingpress_selected_gateway == "on-site"){
					//$bookingpress_paid_amount = $bookingpress_subtotal_amount;
					$bookingpress_deposit_amount = 0;
				}
				
				$currency_name   = $payment_log_details['bookingpress_payment_currency'];
				$currency_symbol = $BookingPress->bookingpress_get_currency_symbol($currency_name);

				$payment_logs_data['deposit_amount'] = $bookingpress_deposit_amount;
				$payment_logs_data['deposit_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_deposit_amount, $bookingpress_selected_currency);

				$bookingpress_due_amount = 0;
				if($bookingpress_deposit_amount != 0){
					$bookingpress_due_amount = $bookingpress_final_total_amount - $bookingpress_deposit_amount;
				}
				$payment_logs_data['due_amount'] = floatval($bookingpress_due_amount);
				$payment_logs_data['due_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_due_amount, $bookingpress_selected_currency);

				$payment_logs_data['subtotal_amount'] = $bookingpress_subtotal_amount;
				$payment_logs_data['subtotal_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_subtotal_amount, $bookingpress_selected_currency);


				$payment_logs_data['payment_numberic_amount'] = floatval($bookingpress_paid_amount);
				$payment_logs_data['payment_amount'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_paid_amount, $bookingpress_selected_currency);

				
				$payment_logs_data['total_amount'] = $bookingpress_final_total_amount;
				$payment_logs_data['total_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_final_total_amount, $bookingpress_selected_currency);
			}
			$payment_logs_data = apply_filters('bookingpress_modify_outside_total_amount',$payment_logs_data, $payment_log_id, $bookingpress_selected_currency);

			return $payment_logs_data;
		}

		function bookingpress_modify_payments_listing_data_func($payment_logs_data){
			global $BookingPress, $wpdb, $tbl_bookingpress_payment_logs, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_staff_members, $bookingpress_global_options,$bookingpress_pro_appointment;

			if(!empty($payment_logs_data) && is_array($payment_logs_data) ){
				$bookingpress_global_settings = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_default_date_format = $bookingpress_global_settings['wp_default_date_format'];

				foreach($payment_logs_data as $k => $v){
					$payment_log_id = $v['payment_log_id'];

					$payment_log_details = $this->bookingpress_calculate_payment_details($payment_log_id);

					$payment_logs_data[$k]['payment_gateway_label'] = $payment_log_details['selected_gateway_label'];
					$payment_logs_data[$k]['is_cart'] = $payment_log_details['is_cart'];
					$payment_logs_data[$k]['order_id'] = $payment_log_details['order_id'];
					
					if($payment_log_details['is_cart'] == '1'){
						$payment_logs_data[$k]['staff_member_name'] = ' - ';
						$payment_logs_data[$k]['payment_service'] = ' - ';
						$payment_logs_data[$k]['appointment_date'] = ' - ';
					}

					$payment_logs_data[$k]['price_display_setting'] = $payment_log_details['price_display_setting'];
					$payment_logs_data[$k]['display_tax_order_summary'] = $payment_log_details['display_tax_amount_in_order_summary'];
					$payment_logs_data[$k]['included_tax_label'] = $payment_log_details['included_tax_label'];

					//Returns tax amount
					$payment_logs_data[$k]['tax_amount'] = $payment_log_details['tax_amount'];
					$payment_logs_data[$k]['tax_amount_with_currency'] = $payment_log_details['tax_amount_with_currency'];

					//Returns coupon details
					$payment_logs_data[$k]['coupon_discount_amount'] = $payment_log_details['coupon_discount_amount'];
					$payment_logs_data[$k]['coupon_discount_amount_with_currency'] = $payment_log_details['coupon_discount_amount_with_currency'];
					$payment_logs_data[$k]['applied_coupon_code'] = $payment_log_details['applied_coupon_code'];


					$bookingpress_is_deposit_enable = $payment_log_details['is_deposit_enable'];
					$bookingpress_subtotal_amount = $payment_log_details['subtotal_amount'];

					if(!empty($payment_log_details)){
						$payment_logs_data[$k]['appointment_details'] = !empty($payment_log_details['appointment_details']) ? $payment_log_details['appointment_details'] : array();
						$payment_logs_data[$k]['is_deposit_enable'] = $bookingpress_is_deposit_enable;
					}

					$payment_logs_data[$k]['deposit_amount'] = $payment_log_details['deposit_amount'];
					$payment_logs_data[$k]['deposit_amount_with_currency'] = $payment_log_details['deposit_amount_with_currency'];

					$payment_logs_data[$k]['due_amount'] = $payment_log_details['due_amount'];
					$payment_logs_data[$k]['due_amount_with_currency'] = $payment_log_details['due_amount_with_currency'];

					$payment_logs_data[$k]['subtotal_amount'] = $payment_log_details['subtotal_amount'];
					$payment_logs_data[$k]['subtotal_amount_with_currency'] = $payment_log_details['subtotal_amount_with_currency'];

					$payment_logs_data[$k]['payment_amount'] = $payment_log_details['payment_amount'];
					$payment_logs_data[$k]['payment_numberic_amount'] = floatval($payment_log_details['payment_numberic_amount']);

					$payment_logs_data[$k]['total_amount'] = $payment_log_details['total_amount'];
					$payment_logs_data[$k]['total_amount_with_currency'] = $payment_log_details['total_amount_with_currency'];

					if(isset($payment_logs_data[$k]['is_cart']) && $payment_logs_data[$k]['is_cart'] == 0 && isset($payment_logs_data[$k]['appointment_details'][0]['bookingpress_appointment_booking_id'])) {
						$refund_data = $bookingpress_pro_appointment->bookingpress_allow_to_refund($payment_logs_data[$k]['appointment_details'][0],0,0);
						$payment_logs_data[$k]['payment_refund_status'] = $refund_data['allow_refund'];
						$payment_logs_data[$k]['payment_partial_refund'] = $refund_data['allow_partial'];
						$payment_logs_data[$k]['appointment_id'] = $payment_logs_data[$k]['appointment_details'][0]['bookingpress_appointment_booking_id'];
						$bookingpress_service_currecy = $payment_logs_data[$k]['appointment_details'][0]['bookingpress_service_currency'];
						$payment_logs_data[$k]['appointment_currency_symbol'] = $BookingPress->bookingpress_get_currency_symbol($bookingpress_service_currecy);
					}
					
				}
			}
			return $payment_logs_data;
		}

		function bookingpress_modify_modal_payment_log_details_func( $payment_log_data ) {
			global $wpdb, $bookingpress_coupons, $tbl_bookingpress_coupons, $BookingPress;
			if ( ! empty( $payment_log_data ) && is_array( $payment_log_data ) ) {
				$payment_log_id = ! empty( $payment_log_data['bookingpress_payment_log_id'] ) ? intval( $payment_log_data['bookingpress_payment_log_id'] ) : 0;
				if ( ! empty( $payment_log_id ) ) {

					$currency_name   = $payment_log_data['bookingpress_payment_currency'];
					$currency_symbol = $BookingPress->bookingpress_get_currency_symbol( $currency_name );

					$bookingpress_coupon_details         = ! empty( $payment_log_data['bookingpress_coupon_details'] ) ? json_decode( $payment_log_data['bookingpress_coupon_details'], ARRAY_A ) : array();
					$bookingpress_applied_coupon_code    = ! empty( $bookingpress_coupon_details['bookingpress_coupon_code'] ) ? $bookingpress_coupon_details['bookingpress_coupon_code'] : '';
					$bookingpress_coupon_discount_amount = ! empty( $bookingpress_coupon_details['bookingpress_coupon_discount'] ) ? $bookingpress_coupon_details['bookingpress_coupon_discount'] : '';
					if ( ! empty( $bookingpress_applied_coupon_code ) ) {
						if ( $bookingpress_coupon_details['bookingpress_coupon_discount_type'] == 'Percentage' ) {
							$bookingpress_coupon_discount_amount = $bookingpress_coupon_discount_amount . '%';
						} else {
							$bookingpress_coupon_discount_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $bookingpress_coupon_discount_amount, $currency_symbol );
						}
					}
					$bookingpress_tax_percentage = ! empty( $payment_log_data['bookingpress_tax_percentage'] ) ? sanitize_text_field( $payment_log_data['bookingpress_tax_percentage'] ) : '';
					$bookingpress_tax_amount     = ! empty( $payment_log_data['bookingpress_tax_amount'] ) ? sanitize_text_field( $payment_log_data['bookingpress_tax_amount'] ) : '';
					if ( ! empty( $bookingpress_tax_percentage ) ) {
						$bookingpress_tax_percentage = $bookingpress_tax_percentage . '%';
					}
					if ( ! empty( $bookingpress_tax_amount ) ) {
						$bookingpress_tax_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $bookingpress_tax_amount, $currency_symbol );
					}
					$payment_log_data['bookingpress_tax_precentage']         = $bookingpress_tax_percentage;
					$payment_log_data['bookingpress_tax_amount']             = $bookingpress_tax_amount;
					$payment_log_data['bookingpress_applied_coupon_code']    = $bookingpress_applied_coupon_code;
					$payment_log_data['bookingpress_coupon_discount_amount'] = $bookingpress_coupon_discount_amount;
				}
			}
			return $payment_log_data;
		}

		function bookingpress_modify_payment_data_fields_func( $bookingpress_payment_vue_data_fields ) {
			global $wpdb, $BookingPress, $bookingpress_pro_staff_members,$BookingPressPro,$bookingpress_global_options;

			$bookingpress_payment_vue_data_fields['bulk_options'] = array(
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
					'bulk_actions' => $bookingpress_payment_vue_data_fields['payment_status_data'],
				),
			);

			$bookingpress_payment_vue_data_fields['search_status_data'] = $bookingpress_payment_vue_data_fields['payment_status_data'];

			$bookingpress_payment_vue_data_fields['ExportPayment']             = false;
			$bookingpress_payment_vue_data_fields['is_export_button_loader']   = '0';
			$bookingpress_payment_vue_data_fields['is_export_button_disabled'] = false;
			$bookingpress_payment_vue_data_fields['is_mask_display']           = false;
			$bookingpress_payment_vue_data_fields['export_payment_top_pos']    = '210px';
			$bookingpress_payment_vue_data_fields['export_payment_right_pos']  = '80px';
			$bookingpress_payment_vue_data_fields['export_payment_left_pos']   = 'auto';
			$bookingpress_payment_vue_data_fields['is_refund_btn_disabled'] = false;
			$bookingpress_payment_vue_data_fields['is_display_refund_loader'] = '0';
			$bookingpress_payment_vue_data_fields['refund_confirm_modal'] = false;
			$bookingpress_payment_vue_data_fields['refund_confirm_form']['refund_type'] = 'full';
			$bookingpress_payment_vue_data_fields['refund_confirm_form']['refund_reason'] = '';
			$bookingpress_payment_vue_data_fields['refund_confirm_form']['refund_appointment_status'] = '3';
			$bookingpress_payment_vue_data_fields['refund_confirm_form']['refund_amount'] = '';
			$bookingpress_payment_vue_data_fields['refund_confirm_form']['allow_partial_refund'] = 0;
			$bookingpress_payment_vue_data_fields['rules_refund_confirm_form'] = array();

			$bookingpress_payment_vue_data_fields['payment_export_field_list'] = array(
				array(
					'name' => 'customer_first_name',
					'text' => __( 'Customer First Name', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'customer_last_name',
					'text' => __( 'Customer Last Name', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'customer_email',
					'text' => __( 'Customer Email', 'bookingpress-appointment-booking' ),
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
					'name' => 'transaction_id',
					'text' => __( 'Transaction Id', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'payer_email',
					'text' => __( 'Payer Email', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'payment_method',
					'text' => __( 'Payment Method', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'payment_date',
					'text' => __( 'Payment Date', 'bookingpress-appointment-booking' ),
				),
				array(
					'name' => 'payment_status',
					'text' => __( 'Payment Status', 'bookingpress-appointment-booking' ),
				),

			);

			$bookingpress_payment_vue_data_fields['export_checked_field'] = array(
				'customer_first_name',
				'customer_last_name',
				'customer_email',
				'service',
				'amount',
				'transaction_id',
				'payer_email',
				'payment_method',
				'payment_date',
				'payment_status',
			);

			$bookingpress_payment_vue_data_fields['is_staffmember_activated'] = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_payment_vue_data_fields['is_display_save_loader']    = '0';
			$bookingpress_payment_vue_data_fields['is_disabled']               = false;

			$bookingpress_customers_details                                 = $BookingPress->bookingpress_get_appointment_customer_list();
			$bookingpress_payment_vue_data_fields['bookingpress_customers'] = $bookingpress_customers_details;

			$bookingpress_services = $BookingPress->get_bookingpress_service_data_group_with_category();
			$bookingpress_payment_vue_data_fields['bookingpress_services'] = $bookingpress_services;

			$bookingpress_payment_deafult_currency = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
			$bookingpress_payment_deafult_currency = $BookingPress->bookingpress_get_currency_symbol( $bookingpress_payment_deafult_currency );

			$bookingpress_payment_vue_data_fields['payment_currency'] = $bookingpress_payment_deafult_currency;						

			$bookingpress_payment_vue_data_fields['is_send_button_loader'] = '0';
			$bookingpress_payment_vue_data_fields['is_send_button_disabled'] = false;

			$bookingpress_payment_vue_data_fields['bookingpress_complete_payment_link_send_options'] = array();

			$bookingpress_edit_payment = $bookingpress_delete_payment = $bookingpress_export_payment = 0;
			if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_payments' ) ) {
				$bookingpress_edit_payment = 1;
			}	
			if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_delete_payments' ) ) {
				$bookingpress_delete_payment = 1;
			}	
			if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_export_payments' ) ) {
				$bookingpress_export_payment = 1;
			}
			$bookingpress_payment_vue_data_fields['bookingpress_edit_payment'] = $bookingpress_edit_payment;
			$bookingpress_payment_vue_data_fields['bookingpress_delete_payment'] = $bookingpress_delete_payment;
			$bookingpress_payment_vue_data_fields['bookingpress_export_payment'] = $bookingpress_export_payment;
			
			$bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_payment_vue_data_fields['bookingpress_payment_appointment_status'] = $bookingpress_global_data['appointment_status'];
			return $bookingpress_payment_vue_data_fields;
		}

		function bookingpress_modify_payment_file_path_func( $bookingpress_payment_view_path ) {

			$bookingpress_payment_view_path = BOOKINGPRESS_PRO_VIEWS_DIR . '/payment/manage_payment.php';
			return $bookingpress_payment_view_path;
		}

		function bookingpress_payment_dynamic_bulk_action_func() {
			?>	
				const vm2 =  this;		
				var payment_logs_bulk_action = {
					action:'bookingpress_pro_bulk_payment_logs_action',
					payment_ids: this.multipleSelection,
					bulk_action: this.bulk_action,
					_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
				}
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( payment_logs_bulk_action ) )
				.then(function(response){					
					vm2.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
					});
					vm2.loadPayments()
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

		function bookingpress_pro_bulk_payment_logs_action_func() {
			global $wpdb,$BookingPress,$tbl_bookingpress_payment_logs;
			$response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'bulk_payment_actions', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_payment_status = ! empty( $_POST['bulk_action'] ) ? sanitize_text_field( $_POST['bulk_action'] ) : ''; // phpcs:ignore

			if ( ! empty( $bookingpress_payment_status ) && in_array( $bookingpress_payment_status, array( '2', '1','3','4','5') ) ) {
				$payment_ids = ! empty( $_POST['payment_ids'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['payment_ids'] ) : array(); // phpcs:ignore
				if ( ! empty( $payment_ids ) ) {
					foreach ( $payment_ids as $payment_key => $payment_val ) {
						if ( is_array( $payment_val ) ) {
							$payment_val = $payment_val['payment_log_id'];
						}
						if ( ! empty( $payment_val ) ) {
							$wpdb->update( $tbl_bookingpress_payment_logs, array( 'bookingpress_payment_status' => $bookingpress_payment_status ), array( 'bookingpress_payment_log_id' => $payment_val ) );
						}
					}
					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Payment status has been change successfully.', 'bookingpress-appointment-booking' );
				}
			}
			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_export_payment_data_func() {
			global $wpdb, $tbl_bookingpress_payment_logs, $BookingPress, $tbl_bookingpress_customers, $tbl_bookingpress_appointment_bookings,$bookingpress_global_options;

			$response = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'export_payment_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_export_field = ! empty( $_REQUEST['export_field'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['export_field'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function

			$bookingpress_search_query = ' WHERE 1=1';
			if ( ! empty( $_POST['search_data'] ) ) { // phpcs:ignore
				$search_data = array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['search_data'] ); // phpcs:ignore
				if ( ! empty( $search_data['search_range'] ) ) {
					$range_start_date           = date( 'Y-m-d', strtotime( $search_data['search_range'][0] ) ) . ' 00:00:00';
					$range_end_date             = date( 'Y-m-d', strtotime( $search_data['search_range'][1] ) ) . ' 23:59:59';
					$bookingpress_search_query .= " AND (bookingpress_payment_date_time BETWEEN '{$range_start_date}' AND '{$range_end_date}')";
				}
				if ( ! empty( $search_data['search_customer'] ) ) {
					$customer_id                = $search_data['search_customer'];
					$customer_id                = implode( ',', $customer_id );
					$bookingpress_search_query .= " AND (bookingpress_customer_id IN ({$customer_id}))";
				}
				if ( ! empty( $search_data['search_service'] ) ) {
					$service_id                 = $search_data['search_service'];
					$service_id                 = implode( ',', $service_id );
					$bookingpress_search_query .= " AND (bookingpress_service_id IN ({$service_id}))";
				}
				if ( ! empty( $search_data['search_status'] ) && $search_data['search_status'] != 'all' ) {
					$search_status              = $search_data['search_status'];
					$bookingpress_search_query .= " AND (bookingpress_payment_status = '{$search_status}')";
				}

				if ( ! empty( $search_data['search_staff_member'] ) ) {
					$bookingpress_search_name            = $search_data['search_staff_member'];
					$bookingpress_search_staff_member_id = implode( ',', $bookingpress_search_name );
					$bookingpress_search_query          .= " AND (bookingpress_staff_member_id IN ({$bookingpress_search_staff_member_id}))";
				}
			}
			$bookingpress_search_query = apply_filters( 'bookingpress_export_payment_data_filter', $bookingpress_search_query );

			$get_payment_logs  = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_payment_logs . ' ' . $bookingpress_search_query . ' ORDER BY bookingpress_payment_log_id DESC', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			$payment_logs_data = array();
			if ( ! empty( $get_payment_logs ) && ! empty( $bookingpress_export_field ) ) {

				$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_date_format        = $bookingpress_global_options_arr['wp_default_date_format'] . ' ' . $bookingpress_global_options_arr['wp_default_time_format'];

				$bookingpress_payment_status_arr = $bookingpress_global_options_arr['payment_status'];

				foreach ( $get_payment_logs as $payment_log_key => $payment_log_val ) {
					$payment = array();

					if ( in_array( 'customer_first_name', $bookingpress_export_field ) ) {
						$payment['Customer First Name'] = ! empty( $payment_log_val['bookingpress_customer_firstname'] ) ? '"' . sanitize_text_field( $payment_log_val['bookingpress_customer_firstname'] ) . '"' : '-';
					}
					if ( in_array( 'customer_last_name', $bookingpress_export_field ) ) {
						$payment['Customer Last Name'] = ! empty( $payment_log_val['bookingpress_customer_lastname'] ) ? '"' . sanitize_text_field( $payment_log_val['bookingpress_customer_lastname'] ) . '"' : '-';
					}
					if ( in_array( 'customer_email', $bookingpress_export_field ) ) {
						$payment['Customer Email'] = ! empty( $payment_log_val['bookingpress_customer_email'] ) ? '"' . sanitize_email( $payment_log_val['bookingpress_customer_email'] ) . '"' : '-';
					}
					if ( in_array( 'service', $bookingpress_export_field ) ) {
						$payment['Service'] = ! empty( $payment_log_val['bookingpress_service_name'] ) ? '"' . sanitize_text_field( $payment_log_val['bookingpress_service_name'] ) . '"' : '-';
					}
					if ( in_array( 'amount', $bookingpress_export_field ) ) {
						$currency_name     = $payment_log_val['bookingpress_payment_currency'];
						$currency_symbol   = $BookingPress->bookingpress_get_currency_symbol( $currency_name );
						$payment_amount    = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $payment_log_val['bookingpress_payment_amount'], $currency_symbol );
						$payment['Amount'] = ! empty( $payment_amount ) ? '"' . sanitize_text_field( $payment_amount ) . '"' : '-';
					}
					if ( in_array( 'transaction_id', $bookingpress_export_field ) ) {
						$payment['Transaction ID'] = ! empty( $payment_log_val['bookingpress_transaction_id'] ) ? '"' . sanitize_text_field( $payment_log_val['bookingpress_transaction_id'] ) . '"' : '-';
					}
					if ( in_array( 'payer_email', $bookingpress_export_field ) ) {
						$payment['Payer Email'] = ! empty( $payment_log_val['bookingpress_payer_email'] ) ? '"' . sanitize_email( $payment_log_val['bookingpress_payer_email'] ) . '"' : '-';
					}
					if ( in_array( 'payment_method', $bookingpress_export_field ) ) {
						$payment['Payment Method'] = ! empty( $payment_log_val['bookingpress_payment_gateway'] ) ? '"' . sanitize_text_field( $payment_log_val['bookingpress_payment_gateway'] ) . '"' : '-';
					}
					if ( in_array( 'payment_date', $bookingpress_export_field ) ) {
						$payment_date            = date( $bookingpress_date_format, strtotime( $payment_log_val['bookingpress_payment_date_time'] ) );
						$payment['Payment Date'] = ! empty( $payment_date ) ? '"' . sanitize_text_field( $payment_date ) . '"' : '-';
					}
					if ( in_array( 'payment_status', $bookingpress_export_field ) ) {
						$bookingpress_payment_status = !empty($payment_log_val['bookingpress_payment_status']) ? sanitize_text_field($payment_log_val['bookingpress_payment_status']) : '-';

						$bookingpress_payment_status_label = "";
						foreach($bookingpress_payment_status_arr as $bookingpress_payment_status_key => $bookingpress_payment_status_vals){
							if($bookingpress_payment_status_vals['value'] == $bookingpress_payment_status){
								$bookingpress_payment_status_label = $bookingpress_payment_status_vals['text'];
								break;
							}
						}
						
						$payment['Payment Status'] = ! empty( $bookingpress_payment_status_label ) ? '"' . sanitize_text_field( $bookingpress_payment_status_label ) . '"' : '-';
					}
					$payment_logs_data[] = $payment;
				}
			} else {
				$payment_logs_data = array();
			}
			$data = array();
			if ( ! empty( $payment_logs_data ) ) {
				array_push( $data, array_keys( $payment_logs_data[0] ) );
				foreach ( $payment_logs_data as $key => $value ) {
					array_push( $data, array_values( $value ) );
				}
			}
			$response['status'] = 'success';
			$response['data']   = $data;
			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_dynamic_add_onload_payment_methods_func(){
			?>
				
			<?php
		}

		function bookingpress_payment_add_dynamic_vue_methods_func() {
			global $BookingPress, $bookingpress_notification_duration;
			$bookingpress_export_delimeter = $BookingPress->bookingpress_get_settings( 'bookingpress_export_delimeter', 'general_setting' );
			?>
			Bookingpress_export_payment_data( currentElement ){
				const vm = this;
				vm.ExportPayment = true;

				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#payment_export_model .el-dialog.bpa-dialog--export-payments');
				}
			},			
			close_export_payment_model(){
				const vm = this;
				vm.ExportPayment = false;
				vm.export_checked_field = ['customer_first_name','customer_last_name'
				,'customer_email','service','amount','transaction_id','payer_email','payment_method','payment_date','payment_status'];
			},
			bookingpress_export_payments(){
				const vm = this;
				vm.is_export_button_loader = '1';						
				vm.is_export_button_disabled = true;
				var payment_export_data = {
					action:'bookingpress_export_payment_data',
					export_field: vm.export_checked_field,
					search_data : vm.search_data,
					_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
				}								
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( payment_export_data ) )
				.then(function(response) {																
					vm.is_export_button_loader = '0';						
					vm.is_export_button_disabled = false;
					vm.close_export_payment_model();								

					if(response.data.data != 'undefined') {
						var export_data;
						var csv = ''; 
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
						anchor.download = 'Bookingpress-export-payment.csv';
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
			bpa_send_complete_payment_link(payment_log_id){
				const vm = this
				vm.is_send_button_loader = '1';
				vm.is_send_button_disabled = true;
				var postdata = {
					action: 'bookingpress_send_complete_payment_link',
					payment_log_id: payment_log_id,
					selected_options: vm.bookingpress_complete_payment_link_send_options,
					_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>',
				};
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
				.then(function(response){
					vm.is_send_button_loader = '0';
					vm.is_send_button_disabled = false;
					vm.$notify({
						title: response.data.title,
						message: response.data.msg,
						type: response.data.variant,
						customClass: response.data.variant+'_notification',
						duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
					});
					if(response.data.variant == "success"){
						document.body.click();
					}
				}).catch(function(error){
					vm.is_send_button_loader = '0';
					vm.is_send_button_disabled = false;
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
						if(typeof response.data.is_past_appointment != 'undefined' && response.data.is_past_appointment == 1) {
							vm.refund_confirm_form.refund_appointment_status = '5';
						}
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
				vm.refund_confirm_form.refund_appointment_status = '3';
			},
			bookingpress_apply_for_refund(payment_id,appointment_id) {
				const vm = this
				vm.is_display_refund_loader = '1';
				vm.is_refund_btn_disabled = true;
				var is_error = false;
				var error_msg = false;
												
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
						vm.loadPaymentWithoutLoader()
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
			},
            isValidateZeroDecimal(evt){
                const vm = this                
                if (/[^0-9]+/.test(evt)){
                    vm.refund_confirm_form.refund_amount = evt.slice(0, -1);
                }
            },
			<?php
			do_action('bookingpress_pro_add_dynamic_vue_methods');
		}

	}
}

global $bookingpress_pro_payment;
$bookingpress_pro_payment = new bookingpress_pro_payment();
