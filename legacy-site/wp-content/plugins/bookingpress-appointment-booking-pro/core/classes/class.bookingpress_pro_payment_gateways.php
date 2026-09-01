<?php
if ( ! class_exists( 'bookingpress_pro_payment_gateways' ) ) {
	class bookingpress_pro_payment_gateways Extends BookingPress_Core {
		function __construct() {
			add_action( 'wp_ajax_bookingpress_recalculate_appointment_data', array( $this, 'bookingpress_recalculate_appointment_data_func' ), 10 );
			add_action( 'wp_ajax_nopriv_bookingpress_recalculate_appointment_data', array( $this, 'bookingpress_recalculate_appointment_data_func' ), 10 );

			add_filter( 'bookingpress_validate_submitted_booking_form', array( $this, 'bookingpress_validate_submitted_booking_form_func' ), 10, 2 );

			//Filter for add payment gateway to revenue filter list
			add_filter('bookingpress_revenue_filter_payment_gateway_list_add', array($this, 'bookingpress_revenue_filter_payment_gateway_list_add_func'));

			add_action( 'wp_ajax_bookingpress_pre_booking_verify_details', array( $this, 'bookingpress_pre_booking_verify_details_callback') );
			add_action( 'wp_ajax_nopriv_bookingpress_pre_booking_verify_details', array( $this, 'bookingpress_pre_booking_verify_details_callback') );

			add_filter( 'bookingpress_adjust_paid_amount', array( $this, 'bookingpress_adjust_paid_amount_function'), 10, 2 );

			add_filter( 'bookingpress_retrieve_payment_amount_currency_from_payment_data', array( $this, 'bookingpress_payment_amount_from_rawdata'), 10, 4);
		}

		
		/**
		 * bpa function for bpa_pre_booking_verify_details 
		 *
		 * @return void
		*/
		function bookingpress_bpa_pre_booking_verify_details_func($user_detail = array()){
			global $BookingPress,$wpdb,$BookingPressPro;	
			$result = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){
				
				$bookingpress_nonce = isset($user_detail['bookingpress_nonce']) ? $user_detail['bookingpress_nonce'] : '';
				if(!empty($bookingpress_nonce)){
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}else{
					$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}
				$appointment_details = isset($user_detail['appointment_details']) ? $user_detail['appointment_details'] : '';			
				$booking_token = isset($user_detail['booking_token']) ? $user_detail['booking_token'] : '';
				if(!empty($appointment_details)){
					$_REQUEST['booking_data'] = $appointment_details;
					$_REQUEST['booking_token'] = $booking_token;
					$_POST = $_REQUEST;
				}
				$bookingpress_response = $this->bookingpress_pre_booking_verify_details_callback(true);
				$bookingpress_check_response = (isset($bookingpress_response['variant']))?$bookingpress_response['variant']:'';
				if($bookingpress_check_response == 'error'){
					$message = (isset($bookingpress_response['msg']))?$bookingpress_response['msg']:'';
					$response = array('status' => 0, 'message' => $message, 'response' => array('result' => $result));
				}else{
					$result = $bookingpress_response;
					$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));				
				}
			}
			return $response;			
		}

		function bookingpress_revenue_filter_payment_gateway_list_add_func($bookingpress_revenue_filter_payment_gateway_list){
			global $BookingPress;

			$bookingpress_is_onsite_enabled = $BookingPress->bookingpress_get_settings('on_site_payment', 'payment_setting');
			if($bookingpress_is_onsite_enabled == '1' || $bookingpress_is_onsite_enabled == 'true' ){
				$bookingpress_revenue_filter_payment_gateway_list[] = array(
					'value' => 'on-site',
					'text' => esc_html__('On Site', 'bookingpress-appointment-booking')
				);
			} else {
				unset($bookingpress_revenue_filter_payment_gateway_list['on-site']);
			}

			return $bookingpress_revenue_filter_payment_gateway_list;
		}

		function bookingpress_validate_submitted_booking_form_func( $payment_gateway, $posted_data ) {
			global $BookingPress, $wpdb, $tbl_bookingpress_entries, $bookingpress_debug_payment_log_id, $bookingpress_coupons, $tbl_bookingpress_appointment_meta, $tbl_bookingpress_extra_services, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers, $bookingpress_deposit_payment, $tbl_bookingpress_staffmembers_services, $bookingpress_other_debug_log_id;

			$return_data = array(
				'service_data'     => array(),
				'payable_amount'   => 0,
				'customer_details' => array(),
				'currency'         => '',
			);

			$bookingpress_appointment_data = $posted_data;

			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );


			$return_data                   = apply_filters( 'bookingpress_before_modify_validate_submit_form_data', $return_data );

			/* Add new variable for added recurring appointment */
			$bookingpress_add_single_entry = apply_filters( 'bookingpress_add_single_appointment_data',true,$posted_data);

			if ( ! empty( $posted_data ) && ! empty( $payment_gateway ) && empty($bookingpress_appointment_data['cart_items']) && $bookingpress_add_single_entry ) {
				$bookingpress_selected_service_id     = sanitize_text_field( $bookingpress_appointment_data['selected_service'] );
				$bookingpress_appointment_booked_date = sanitize_text_field( $bookingpress_appointment_data['selected_date'] );
				$bookingpress_selected_start_time     = sanitize_text_field( $bookingpress_appointment_data['selected_start_time'] );
				$bookingpress_selected_end_time       = sanitize_text_field($bookingpress_appointment_data['selected_end_time']);
				if( !empty( $bookingpress_timeslot_display_in_client_timezone ) && 'true' == $bookingpress_timeslot_display_in_client_timezone ){
					$bookingpress_appointment_booked_date = !empty( $bookingpress_appointment_data['store_selected_date'] ) ? sanitize_text_field( $bookingpress_appointment_data['store_selected_date'] ) : $bookingpress_appointment_booked_date;

					$bookingpress_selected_start_time = !empty( $bookingpress_appointment_data['store_start_time'] ) ? sanitize_text_field( $bookingpress_appointment_data['store_start_time'] ) : $bookingpress_selected_start_time;

					$bookingpress_selected_end_time = !empty( $bookingpress_appointment_data['store_end_time'] ) ? sanitize_text_field( $bookingpress_appointment_data['store_end_time'] ) : $bookingpress_selected_end_time;

					//$bookingpress_appointment_data['bookingpress_customer_timezone'] = $bookingpress_appointment_data['client_offset'];

				}

				$bookingpress_internal_note = '';
				if( isset ( $bookingpress_appointment_data['appointment_note'] ) ){

					$bookingpress_internal_note           = !empty( $bookingpress_appointment_data['appointment_note'] ) ? sanitize_textarea_field( $bookingpress_appointment_data['appointment_note'] ) : $bookingpress_appointment_data['form_fields']['appointment_note'];
				}

				$service_data                         = $BookingPress->get_service_by_id( $bookingpress_selected_service_id );
				$bookingpress_service_price = $service_data['bookingpress_service_price'];
				$service_duration_vals                = $BookingPress->bookingpress_get_service_end_time( $bookingpress_selected_service_id, $bookingpress_selected_start_time );
				$service_data['service_start_time']   = sanitize_text_field( $service_duration_vals['service_start_time'] );
				$service_data['service_end_time']     = sanitize_text_field( $service_duration_vals['service_end_time'] );
				$return_data['service_data']          = $service_data;

				$bookingpress_currency_name   = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
				$return_data['currency']      = $bookingpress_currency_name;
				$return_data['currency_code'] = $BookingPress->bookingpress_get_currency_code( $bookingpress_currency_name );

				$__payable_amount              = $bookingpress_appointment_data['total_payable_amount'];
				$bookingpress_due_amount = 0;

				if ( $__payable_amount == 0 ) {
					$payment_gateway = ' - ';
				}

				//echo "Payable amount ===>".$__payable_amount ;
				$customer_email     = !empty($bookingpress_appointment_data['form_fields']['customer_email']) ? $bookingpress_appointment_data['form_fields']['customer_email'] : $bookingpress_appointment_data['customer_email'];
				$customer_full_name  = !empty( $bookingpress_appointment_data['form_fields']['customer_name'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_name'] ) : (!empty( $bookingpress_appointment_data['customer_name'] ) ? sanitize_text_field($bookingpress_appointment_data['customer_name'] ) : '');
				$customer_username  = !empty( $bookingpress_appointment_data['form_fields']['customer_username'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_username'] ) : (!empty( $bookingpress_appointment_data['customer_username'] ) ? sanitize_text_field($bookingpress_appointment_data['customer_username'] ) : '');

				$customer_password  = !empty( $bookingpress_appointment_data['form_fields']['customer_password'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_password'] ) : (!empty( $bookingpress_appointment_data['customer_password'] ) ? sanitize_text_field($bookingpress_appointment_data['customer_password'] ) : '');

				$customer_firstname = !empty( $bookingpress_appointment_data['form_fields']['customer_firstname'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_firstname'] ) : (!empty($bookingpress_appointment_data['customer_firstname']) ? sanitize_text_field($bookingpress_appointment_data['customer_firstname'] ) : '');
				$customer_lastname  = !empty( $bookingpress_appointment_data['form_fields']['customer_lastname'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_lastname'] ) : (!empty($bookingpress_appointment_data['customer_lastname']) ? sanitize_text_field($bookingpress_appointment_data['customer_lastname'] ) : '');
				$customer_phone     = !empty( $bookingpress_appointment_data['form_fields']['customer_phone'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_phone'] ) : ( !empty($bookingpress_appointment_data['customer_phone']) ? sanitize_text_field($bookingpress_appointment_data['customer_phone'] ) : '' );
				$customer_country   = !empty( $bookingpress_appointment_data['form_fields']['customer_phone_country'] ) ? sanitize_text_field( $bookingpress_appointment_data['form_fields']['customer_phone_country'] ) : ( !empty($bookingpress_appointment_data['customer_phone_country']) ? sanitize_text_field($bookingpress_appointment_data['customer_phone_country'] ) : '');
				$customer_phone_dial_code = !empty($bookingpress_appointment_data['customer_phone_dial_code']) ? $bookingpress_appointment_data['customer_phone_dial_code'] : '';
				$customer_timezone = !empty($bookingpress_appointment_data['bookingpress_customer_timezone']) ? $bookingpress_appointment_data['bookingpress_customer_timezone'] : wp_timezone_string();

				$customer_dst_timezone = !empty( $bookingpress_appointment_data['client_dst_timezone'] ) ? intval( $bookingpress_appointment_data['client_dst_timezone'] ) : 0;

				if( !empty($customer_phone) && !empty( $customer_phone_dial_code) ){

                    $customer_phone_pattern = '/(^\+'.$customer_phone_dial_code.')/';
                    if( preg_match($customer_phone_pattern, $customer_phone) ){
                        $customer_phone = preg_replace( $customer_phone_pattern, '', $customer_phone) ;
                    }
                }

				$return_data['customer_details'] = array(
					'customer_firstname' => $customer_firstname,
					'customer_lastname'  => $customer_lastname,
					'customer_email'     => $customer_email,
					'customer_username'  => !empty($customer_username) ? $customer_username : $customer_full_name,
					'customer_phone'     => $customer_phone,
				);

				$return_data['card_details'] = array(
					'card_holder_name' => $bookingpress_appointment_data['card_holder_name'],
					'card_number'      => $bookingpress_appointment_data['card_number'],
					'expire_month'     => $bookingpress_appointment_data['expire_month'],
					'expire_year'      => $bookingpress_appointment_data['expire_year'],
					'cvv'              => $bookingpress_appointment_data['cvv'],
				);

				$bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );

				if ( $payment_gateway == 'on-site' ) {
					$bookingpress_appointment_status = $BookingPress->bookingpress_get_settings('onsite_appointment_status', 'general_setting');
				}

				$bookingpress_customer_id = get_current_user_id();

				$bookingpress_deposit_selected_type = "";
				$bookingpress_deposit_selected_amount = 0;
				$bookingpress_deposit_details = array();
				if($payment_gateway != "on-site" && $payment_gateway != " - " && $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation() && !empty($bookingpress_appointment_data['bookingpress_deposit_payment_method']) && ($bookingpress_appointment_data['bookingpress_deposit_payment_method'] == "deposit_or_full_price") ){
					$bookingpress_deposit_selected_type = !empty($bookingpress_appointment_data['deposit_payment_type']) ? $bookingpress_appointment_data['deposit_payment_type'] : '';
					$bookingpress_deposit_selected_amount = !empty($bookingpress_appointment_data['bookingpress_deposit_amt_without_currency']) ? floatval($bookingpress_appointment_data['bookingpress_deposit_amt_without_currency']) : 0;
					$bookingpress_due_amount = !empty($bookingpress_appointment_data['bookingpress_deposit_due_amt_without_currency']) ? floatval($bookingpress_appointment_data['bookingpress_deposit_due_amt_without_currency']) : 0;

					if(!empty($bookingpress_deposit_selected_amount)){
						$__payable_amount = $bookingpress_deposit_selected_amount;
					}
					
					$bookingpress_deposit_details = array(
						'deposit_selected_type' => $bookingpress_deposit_selected_type,
						'deposit_amount' => $bookingpress_deposit_selected_amount,
						'deposit_due_amount' => $bookingpress_due_amount,
					);
				}

				$return_data['payable_amount'] = (float) $__payable_amount;

				//echo "<br>Payable amount 2===>".$return_data['payable_amount'] ;

				// Apply coupon if coupon module enabled
				$bookingpress_coupon_code         = ! empty( $bookingpress_appointment_data['coupon_code'] ) ? $bookingpress_appointment_data['coupon_code'] : '';
				$discounted_amount                = !empty($bookingpress_appointment_data['coupon_discount_amount']) ? floatval($bookingpress_appointment_data['coupon_discount_amount']) : 0;
				$bookingpress_is_coupon_applied   = 0;
				$bookingpress_applied_coupon_data = array();

				if ( $bookingpress_coupons->bookingpress_check_coupon_module_activation() && ! empty( $bookingpress_coupon_code )) {
					$bookingpress_applied_coupon_data = ! empty( $bookingpress_appointment_data['applied_coupon_res'] ) ? $bookingpress_appointment_data['applied_coupon_res'] : array();
					$bookingpress_applied_coupon_data['coupon_discount_amount'] = $discounted_amount;
					$bookingpress_is_coupon_applied = 1;
				}

				$bookingpress_selected_extra_members = !empty($bookingpress_appointment_data['bookingpress_selected_bring_members']) ? $bookingpress_appointment_data['bookingpress_selected_bring_members'] : 1;

				$bookingpress_extra_services = !empty($bookingpress_appointment_data['bookingpress_selected_extra_details']) ? $bookingpress_appointment_data['bookingpress_selected_extra_details'] : array();
				$bookingpress_extra_services_db_details = array();

				if(!empty($bookingpress_extra_services)){
					foreach($bookingpress_extra_services as $k => $v){
						if($v['bookingpress_is_selected'] == "true"){
							$bookingpress_extra_service_id = intval($k);
							$bookingpress_extra_service_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_extra_services_id = %d", $bookingpress_extra_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is a table name. false alarm

							if(!empty($bookingpress_extra_service_details)){
								$bookingpress_extra_service_price = ! empty( $bookingpress_extra_service_details['bookingpress_extra_service_price'] ) ? floatval( $bookingpress_extra_service_details['bookingpress_extra_service_price'] ) : 0;

								$bookingpress_selected_qty = !empty($v['bookingpress_selected_qty']) ? intval($v['bookingpress_selected_qty']) : 1;

								if(!empty($bookingpress_selected_qty)){
									$bookingpress_final_price = $bookingpress_extra_service_price * $bookingpress_selected_qty;
									$v['bookingpress_final_payable_price'] = $bookingpress_final_price;
									$v['bookingpress_extra_service_details'] = $bookingpress_extra_service_details;
									array_push($bookingpress_extra_services_db_details, $v);
								}
							}
						}
					}
				}
	
				$bookingpress_selected_staffmember = 0;
				$bookingpress_is_any_staff_selected = 0;
				$bookingpress_staff_member_firstname = "";
				$bookingpress_staff_member_lastname = "";
				$bookingpress_staff_member_email_address = "";
				$bookingpress_staffmember_price = 0;
				$bookingpress_staffmember_details = array();

				if($bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation()){
					$bookingpress_selected_staffmember = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? $bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id'] : 0;
					$bookingpress_is_any_staff_selected = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['is_any_staff_option_selected']) ? 1 : 0;
					if(!empty($bookingpress_selected_staffmember)){
						$bookingpress_staffmember_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $bookingpress_selected_staffmember), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is table name.
						$bookingpress_staff_member_firstname = !empty($bookingpress_staffmember_details['bookingpress_staffmember_firstname']) ? $bookingpress_staffmember_details['bookingpress_staffmember_firstname'] : '';
						$bookingpress_staff_member_lastname = !empty($bookingpress_staffmember_details['bookingpress_staffmember_lastname']) ? $bookingpress_staffmember_details['bookingpress_staffmember_lastname'] : '';
						$bookingpress_staff_member_email_address = !empty($bookingpress_staffmember_details['bookingpress_staffmember_email']) ? $bookingpress_staffmember_details['bookingpress_staffmember_email'] : '';

						$bookingpress_staffmember_details['is_any_staff_selected'] = $bookingpress_is_any_staff_selected;

						//Fetch staff member price
						$bookingpress_staffmember_price_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $bookingpress_selected_staffmember, $bookingpress_selected_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
						$bookingpress_staffmember_price = !empty($bookingpress_staffmember_price_details['bookingpress_service_price']) ? floatval($bookingpress_staffmember_price_details['bookingpress_service_price']) : 0;
					}
				}
				$bookingpress_total_amount = $bookingpress_appointment_data['total_payable_amount'];

				// Insert data into entries table.
				$bookingpress_entry_details = array(
					'bookingpress_customer_id'                    => $bookingpress_customer_id,
					'bookingpress_order_id'                       => 0,
					'bookingpress_customer_name'                  => $customer_full_name,
					'bookingpress_username'                       => $customer_username,
					'bookingpress_password'                       => wp_hash_password( $customer_password ),
					'bookingpress_customer_phone'                 => $customer_phone,
					'bookingpress_customer_firstname'             => $customer_firstname,
					'bookingpress_customer_lastname'              => $customer_lastname,
					'bookingpress_customer_country'               => $customer_country,
					'bookingpress_customer_phone_dial_code'       => $customer_phone_dial_code,
					'bookingpress_customer_email'                 => $customer_email,
					'bookingpress_customer_timezone'              => $customer_timezone,
					'bookingpress_dst_timezone'					  => $customer_dst_timezone,
					'bookingpress_service_id'                     => $bookingpress_selected_service_id,
					'bookingpress_service_name'                   => $service_data['bookingpress_service_name'],
					'bookingpress_service_price'                  => $bookingpress_service_price,
					'bookingpress_service_currency'               => $bookingpress_currency_name,
					'bookingpress_service_duration_val'           => $service_data['bookingpress_service_duration_val'],
					'bookingpress_service_duration_unit'          => $service_data['bookingpress_service_duration_unit'],
					'bookingpress_payment_gateway'                => $payment_gateway,
					'bookingpress_appointment_date'               => $bookingpress_appointment_booked_date,
					'bookingpress_appointment_time'               => $bookingpress_selected_start_time,
					'bookingpress_appointment_end_time'  		  => $bookingpress_selected_end_time,
					'bookingpress_appointment_internal_note'      => $bookingpress_internal_note,
					'bookingpress_appointment_send_notifications' => 1,
					'bookingpress_appointment_status'             => $bookingpress_appointment_status,
					'bookingpress_coupon_details'                 => wp_json_encode( $bookingpress_applied_coupon_data ),
					'bookingpress_coupon_discount_amount'         => $discounted_amount,
					'bookingpress_deposit_payment_details'        => wp_json_encode( $bookingpress_deposit_details ),
					'bookingpress_deposit_amount'                 => $bookingpress_deposit_selected_amount,
					'bookingpress_selected_extra_members'         => $bookingpress_selected_extra_members,
					'bookingpress_extra_service_details'          => wp_json_encode( $bookingpress_extra_services_db_details ),
					'bookingpress_staff_member_id'                => $bookingpress_selected_staffmember,
					'bookingpress_staff_member_price'             => $bookingpress_staffmember_price,
					'bookingpress_staff_first_name'               => $bookingpress_staff_member_firstname,
					'bookingpress_staff_last_name'                => $bookingpress_staff_member_lastname,
					'bookingpress_staff_email_address'            => $bookingpress_staff_member_email_address,
					'bookingpress_staff_member_details'           => wp_json_encode($bookingpress_staffmember_details),
					'bookingpress_paid_amount'                    => $__payable_amount,
					'bookingpress_due_amount'                     => $bookingpress_due_amount,
					'bookingpress_total_amount'                   => $bookingpress_total_amount,
					'bookingpress_created_at'                     => current_time( 'mysql' ),
				);
				
				$bookingpress_entry_details = apply_filters( 'bookingpress_modify_entry_data_before_insert', $bookingpress_entry_details, $posted_data );
				do_action( 'bookingpress_payment_log_entry', $payment_gateway, 'submit appointment form front', 'bookingpress pro', $bookingpress_entry_details, $bookingpress_debug_payment_log_id );

				$wpdb->insert( $tbl_bookingpress_entries, $bookingpress_entry_details );
				$entry_id = $wpdb->insert_id;

				do_action( 'bookingpress_after_entry_data_insert', $entry_id,$posted_data);

				$return_data['entry_id'] = $entry_id;
				$return_data['booking_form_redirection_mode'] = $posted_data['booking_form_redirection_mode'];

				$bookingpress_uniq_id = $posted_data['bookingpress_uniq_id'];
				$bookingpress_cookie_name = $bookingpress_uniq_id."_appointment_data";
				$bookingpress_cookie_value = $entry_id;

				$bookingpress_cookie_exists = !empty($_COOKIE[$bookingpress_cookie_name]) ? 1 : 0;
				if($bookingpress_cookie_exists){
					setcookie($bookingpress_cookie_name, "", time()-3600, "/");
					setcookie("bookingpress_last_request_id", "", time()-3600, "/");
					setcookie("bookingpress_referer_url", "", time() - 3600, "/");
				}

				$bookingpress_referer_url = (wp_get_referer()) ? wp_get_referer() : BOOKINGPRESS_HOME_URL;
				$bookingpress_encoded_value = base64_encode($entry_id);
				setcookie($bookingpress_cookie_name, $bookingpress_encoded_value, time()+(86400), "/");
				setcookie("bookingpress_last_request_id", $bookingpress_uniq_id, time()+(86400), "/");
				setcookie("bookingpress_referer_url", $bookingpress_referer_url, time()+(86400), "/");

				$bookingpress_entry_hash = md5($entry_id);
				
				$bookingpress_after_approved_payment_page_id = $BookingPress->bookingpress_get_customize_settings( 'after_booking_redirection', 'booking_form' );
				$bookingpress_after_approved_payment_url     = get_permalink( $bookingpress_after_approved_payment_page_id );

				$bookingpress_after_canceled_payment_page_id = $BookingPress->bookingpress_get_customize_settings( 'after_failed_payment_redirection', 'booking_form' );
				$bookingpress_after_canceled_payment_url     = get_permalink( $bookingpress_after_canceled_payment_page_id );
				
				if( !empty($posted_data['booking_form_redirection_mode']) && $posted_data['booking_form_redirection_mode'] == "in-built" ){
					$bookingpress_approved_appointment_url = $bookingpress_canceled_appointment_url = $bookingpress_referer_url;
					$bookingpress_approved_appointment_url = add_query_arg('is_success', 1, $bookingpress_after_approved_payment_url);
					$bookingpress_approved_appointment_url = add_query_arg('appointment_id', base64_encode($entry_id), $bookingpress_approved_appointment_url);
					$bookingpress_approved_appointment_url = add_query_arg( 'bp_tp_nonce', wp_create_nonce( 'bpa_nonce_url-'.$bookingpress_entry_hash ), $bookingpress_approved_appointment_url );

					$bookingpress_canceled_appointment_url = add_query_arg('is_success', 2, $bookingpress_canceled_appointment_url);
					$bookingpress_canceled_appointment_url = add_query_arg('appointment_id', base64_encode($entry_id), $bookingpress_canceled_appointment_url);
					$bookingpress_canceled_appointment_url = add_query_arg( 'bp_tp_nonce', wp_create_nonce( 'bpa_nonce_url-'.$bookingpress_entry_hash ), $bookingpress_canceled_appointment_url );

					$return_data['approved_appointment_url'] = $bookingpress_approved_appointment_url;
					$return_data['pending_appointment_url'] = $return_data['approved_appointment_url'];
					$return_data['canceled_appointment_url'] = $bookingpress_canceled_appointment_url;
				}else{
					$bookingpress_after_approved_payment_url = ! empty( $bookingpress_after_approved_payment_url ) ? $bookingpress_after_approved_payment_url : BOOKINGPRESS_HOME_URL;
					$bookingpress_after_approved_payment_url = add_query_arg('appointment_id', base64_encode($entry_id), $bookingpress_after_approved_payment_url);
					$bookingpress_after_approved_payment_url = add_query_arg( 'bp_tp_nonce', wp_create_nonce( 'bpa_nonce_url-'.$bookingpress_entry_hash ), $bookingpress_after_approved_payment_url );
					$return_data['approved_appointment_url'] = $bookingpress_after_approved_payment_url;

					$bookingpress_after_canceled_payment_url = ! empty( $bookingpress_after_canceled_payment_url ) ? $bookingpress_after_canceled_payment_url : BOOKINGPRESS_HOME_URL;
					$bookingpress_after_canceled_payment_url = add_query_arg('appointment_id', base64_encode($entry_id), $bookingpress_after_canceled_payment_url);
					$return_data['canceled_appointment_url'] = $bookingpress_after_canceled_payment_url;
					
					$return_data['pending_appointment_url'] = $return_data['approved_appointment_url'];
				}

				$bookingpress_notify_url   = BOOKINGPRESS_HOME_URL . '/?bookingpress-listener=bpa_pro_' . $payment_gateway . '_url';
				$return_data['notify_url'] = $bookingpress_notify_url;
				
				$return_data = apply_filters( 'bookingpress_add_modify_validate_submit_form_data', $return_data, $payment_gateway, $posted_data );

				//Enter data in appointment meta table
				//------------------------------
				$bookingpress_appointment_service_data = array(
					'service_id' => $posted_data['selected_service'],
					'service_name' => $posted_data['selected_service_name'],
					'service_price' => $posted_data['selected_service_price'],
					'service_price_without_currency' => $posted_data['service_price_without_currency'],
					'extra_service_details' => !empty($posted_data['bookingpress_selected_extra_details']) ? $posted_data['bookingpress_selected_extra_details'] : array(),
					'selected_bring_members' => !empty($posted_data['bookingpress_selected_bring_members']) ? $posted_data['bookingpress_selected_bring_members'] : 1,
					'selected_service_max_capacity' => !empty($posted_data['service_max_capacity']) ? $posted_data['service_max_capacity'] : 1,
					'selected_staffmember_details' => !empty($posted_data['bookingpress_selected_staff_member_details']) ? $posted_data['bookingpress_selected_staff_member_details'] : array(),
					'is_extra_service_exists' => !empty($posted_data['is_extra_service_exists']) ? $posted_data['is_extra_service_exists'] : 0,
					'is_staff_exists' => !empty($posted_data['is_staff_exists']) ? $posted_data['is_staff_exists'] : 0,
				);
				$bookingpress_db_fields = array(
					'bookingpress_entry_id' => $entry_id,
					'bookingpress_appointment_id' => 0,
					'bookingpress_appointment_meta_key' => 'appointment_service_data',
					'bookingpress_appointment_meta_value' => wp_json_encode($bookingpress_appointment_service_data),
				);
				$wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_db_fields);

				do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Appointment meta service data', 'bookingpress_submit_booking_request', $bookingpress_db_fields, $bookingpress_other_debug_log_id );

				//------------------------------
				$bookingpress_appointment_timeslot_data = array(
					'timeslot_data' => !empty($posted_data['service_timing']) ? $posted_data['service_timing'] : array(),
					'selected_date' => !empty($posted_data['selected_date']) ? $posted_data['selected_date'] : '',
					'selected_start_time' => !empty($posted_data['selected_start_time']) ? $posted_data['selected_start_time'] : '',
					'selected_end_time' => !empty($posted_data['selected_end_time']) ? $posted_data['selected_end_time'] : '',
					'selected_service_max_capacity' => !empty($posted_data['service_max_capacity']) ? $posted_data['service_max_capacity'] : 1,
				);
				$bookingpress_db_fields = array(
					'bookingpress_entry_id' => $entry_id,
					'bookingpress_appointment_id' => 0,
					'bookingpress_appointment_meta_key' => 'appointment_timeslot_data',
					'bookingpress_appointment_meta_value' => wp_json_encode($bookingpress_appointment_timeslot_data),
				);
				$wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_db_fields);

				do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Appointment meta timeslot data', 'bookingpress_submit_booking_request', $bookingpress_db_fields, $bookingpress_other_debug_log_id );

				//------------------------------
				$form_fields_save_data = !empty($posted_data['form_fields']) ? $posted_data['form_fields'] : array(); 				
				$bookingpress_repeater_fields_key = !empty($posted_data['bookingpress_repeater_fields_key']) ? $posted_data['bookingpress_repeater_fields_key'] : array();
				//$form_fields_save_data = apply_filters('bookingpress_removed_repeater_data_in_fields', $form_fields_save_data, $posted_data);
				
				$bookingpress_appointment_form_fields_data = array(
					'form_fields' => $form_fields_save_data,
					'bookingpress_front_field_data' => !empty($posted_data['bookingpress_front_field_data']) ? $posted_data['bookingpress_front_field_data'] : array(),
				);

				$bookingpress_db_fields = array(
					'bookingpress_entry_id' => $entry_id,
					'bookingpress_appointment_id' => 0,
					'bookingpress_appointment_meta_key' => 'appointment_form_fields_data',
					'bookingpress_appointment_meta_value' => wp_json_encode($bookingpress_appointment_form_fields_data),
				);
				$wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_db_fields);

				do_action('bookingpress_after_insert_entry_data_from_frontend',$entry_id,$bookingpress_appointment_data);

				do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Appointment meta form fields data', 'bookingpress_submit_booking_request', $bookingpress_db_fields, $bookingpress_other_debug_log_id );
			}else{
				$return_data = apply_filters('bookingpress_modify_appointment_return_data', $bookingpress_appointment_data, $payment_gateway, $posted_data);
			}

			$return_data = apply_filters( 'bookingpress_after_modify_validate_submit_form_data', $return_data );

			return $return_data;
		}

		function bookingpress_adjust_paid_amount_function( $payment_amount, $payment_gateway ){

			global $bookingpress_pro_global_options;
			
			$bpa_payment_gateways = $bookingpress_pro_global_options->bookingpress_payment_gateways_with_extra_calculations();
			
			if( in_array( $payment_gateway, $bpa_payment_gateways ) ){
				return $payment_amount / 100;
			}
			
			return $payment_amount;
			
		}
		
		function bookingpress_payment_amount_from_rawdata( $paid_amount, $payment_gateway_data, $payment_gateway, $check_for_currency = false ){
			
			global $bookingpress_pro_global_options;

			$bpa_payment_gateways = $bookingpress_pro_global_options->bookingpress_payment_gateways_for_amount_field_name();

			$payment_field_id = $bpa_payment_gateways[ $payment_gateway ];

			if( true == $check_for_currency ){
				$currency_field_data = $payment_field_id['currency'];
				if( false === $currency_field_data ){
					$supported_currencies = $payment_field_id['supported_currency'];
					if( !is_array( $supported_currencies ) ){
						$paid_amount = $supported_currencies;
					}
				} else {
					if( preg_match( '/\|/', $currency_field_data ) ){
						$inner_keys = explode( '|', $currency_field_data );
						
						$paid_amount = $this->filter_array_data( $payment_gateway_data, $inner_keys );
					} else {
						$paid_amount = $payment_gateway_data[ $currency_field_data ];
					}
				}
			} else {
				$amount_field_data = $payment_field_id['amount'];
				if( preg_match( '/\|/', $amount_field_data ) ){
					
					$inner_keys = explode( '|', $amount_field_data );
					
					$paid_amount = $this->filter_array_data( $payment_gateway_data, $inner_keys );
					

				} else {
					$paid_amount = $payment_gateway_data[ $amount_field_data ];
				}
			}

			return $paid_amount;
		}

		function filter_array_data( $payment_gateway_data, $inner_keys, $is_recurrsive = false, $next_param_key = '' ){

			$total_count = count( $inner_keys );
			$x = 0;
			foreach( $inner_keys as $key_data ){
				$next_key = next( $inner_keys );
				if( '' != $next_key ){
					if( false == $is_recurrsive ){
						return $this->filter_array_data( $payment_gateway_data[ $key_data ], $inner_keys, true, $next_key );
					} else {
						return $this->filter_array_data( $payment_gateway_data[ $next_param_key ], $inner_keys, true, $next_key );
					}
				} else {
					return $payment_gateway_data[ $next_param_key ];
				}
				$x++;
			}
		
			return $payment_gateway_data;
		}

		public function bookingpress_confirm_booking( $entry_id, $payment_gateway_data, $payment_status, $transaction_id_field = '', $payment_amount_field = '', $is_front = 2, $is_cart_order = 0, $payment_currency_field = '' ) {

			global $wpdb, $BookingPress, $tbl_bookingpress_entries, $tbl_bookingpress_customers, $bookingpress_email_notifications, $bookingpress_debug_payment_log_id, $bookingpress_customers, $bookingpress_coupons, $tbl_bookingpress_appointment_meta, $tbl_bookingpress_appointment_bookings, $bookingpress_other_debug_log_id, $tbl_bookingpress_payment_logs,$bookingpress_dashboard, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers_services, $bookingpress_bring_anyone_with_you, $bookingpress_services, $tbl_bookingpress_double_bookings, $bookingpress_pro_global_options;

			$bookingpress_confirm_booking_received_data = array(
				'entry_id' => $entry_id,
				'payment_gateway_data' => wp_json_encode($payment_gateway_data),
				'payment_status' => $payment_status,
				'transaction_id_field' => $transaction_id_field,
				'payment_amount_field' => $payment_amount_field,
				'currency_amount_field' => $payment_currency_field,
				'is_front' => $is_front,
				'is_cart_order' => $is_cart_order,
			);
			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking form confirm booking data', 'bookingpress_complete_appointment', $bookingpress_confirm_booking_received_data, $bookingpress_other_debug_log_id );

			$bookingpress_before_check_package_add = apply_filters('bookingpress_before_appointment_confirm_booking_check_package_booking','',$entry_id,$payment_gateway_data,$payment_status,$transaction_id_field,$payment_amount_field,$is_front,$is_cart_order,$payment_currency_field);
			if($bookingpress_before_check_package_add){
				return $bookingpress_before_check_package_add;
			}

			$bookingpress_before_appointment_add = apply_filters('bookingpress_before_appointment_confirm_booking',false,$entry_id,$payment_gateway_data,$payment_status,$transaction_id_field,$payment_amount_field,$is_front,$is_cart_order);
			if($bookingpress_before_appointment_add){
				return 0;
			}
			$bookingpress_is_appointment_exists = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d AND bookingpress_complete_payment_token != ''", $entry_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm			

			if($bookingpress_is_appointment_exists > 0){
				$bookingpress_get_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_entry_id,bookingpress_appointment_booking_id, bookingpress_is_cart, bookingpress_order_id,bookingpress_customer_email,bookingpress_payment_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d AND bookingpress_complete_payment_token != ''", $entry_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_complete_payment_gateway = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_id = %d AND bookingpress_appointment_meta_key = %s", $entry_id,'submit_payment_gateway')); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name.

				$bookingpress_customer_email = !empty($bookingpress_get_appointment_details['bookingpress_customer_email']) ? ($bookingpress_get_appointment_details['bookingpress_customer_email']) : '';

				$bookingpress_is_cart = !empty($bookingpress_get_appointment_details['bookingpress_is_cart']) ? intval($bookingpress_get_appointment_details['bookingpress_is_cart']) : 0;
				$bookingpress_order_id = !empty($bookingpress_get_appointment_details['bookingpress_order_id']) ? intval($bookingpress_get_appointment_details['bookingpress_order_id']) : 0;
				$bookingpress_entry_id = !empty($bookingpress_get_appointment_details['bookingpress_entry_id']) ? intval($bookingpress_get_appointment_details['bookingpress_entry_id']) : 0;

				$transaction_id = ( ! empty( $transaction_id_field ) && ! empty( $payment_gateway_data[ $transaction_id_field ] ) ) ? $payment_gateway_data[ $transaction_id_field ] : '';
				
				$bookingpress_ap_status = 1;
				
				$selected_payment_method = !empty($_POST['complete_payment_data']['selected_payment_method']) ? sanitize_text_field($_POST['complete_payment_data']['selected_payment_method']) : ''; //phpcs:ignore
				$payment_gateway = $payment_gateway_name  = !empty($payment_gateway_data['bookingpress_payment_gateway']) ? $payment_gateway_data['bookingpress_payment_gateway'] : $selected_payment_method;
				if(empty($payment_gateway)) {
					$bookingpress_payment_id = !empty($bookingpress_get_appointment_details['bookingpress_payment_id']) ? intval($bookingpress_get_appointment_details['bookingpress_payment_id']) : 0;
					$bookingpress_payment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_payment_gateway FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = %d", $bookingpress_payment_id), ARRAY_A);//phpcs:ignore
                    if(!empty($bookingpress_payment_details['bookingpress_payment_gateway'])){
                        $payment_gateway = $payment_gateway_name  =  $bookingpress_payment_details['bookingpress_payment_gateway'];
                    }					
				}				
				$bookingpress_ap_status = $BookingPress->bookingpress_get_settings('appointment_status', 'general_setting');				
				if (!empty($payment_gateway) && $payment_gateway == 'on-site' ) {
					$bookingpress_ap_status = $BookingPress->bookingpress_get_settings('onsite_appointment_status', 'general_setting');
				}
				
				
				$bookingpress_email_notification_type = '';
				if ( $bookingpress_ap_status == '2' ) {
					$bookingpress_email_notification_type = 'Appointment Pending';
				} elseif ( $bookingpress_ap_status == '1' ) {
					$bookingpress_email_notification_type = 'Appointment Approved';
				} elseif ( $bookingpress_ap_status == '3' ) {
					$bookingpress_email_notification_type = 'Appointment Canceled';
				} elseif ( $bookingpress_ap_status == '4' ) {
					$bookingpress_email_notification_type = 'Appointment Rejected';
				}

				//Store Coupon Data in appointment & Payment Log Start
				$bookingpress_payment_log_update_data = array();
				$bookingpress_appointment_booking_update_data = array();
				if($bookingpress_entry_id){

					$entry_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_coupon_details,bookingpress_coupon_discount_amount FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d", $bookingpress_entry_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm

					if ( ! empty( $entry_data ) ) { 
																	
						$bookingpress_coupon_details                 = $entry_data['bookingpress_coupon_details'];
						$bookingpress_coupon_discounted_amount       = $entry_data['bookingpress_coupon_discount_amount'];

						$bookingpress_payment_log_update_data = array(
							'bookingpress_coupon_details'          => $bookingpress_coupon_details,
							'bookingpress_coupon_discount_amount'  => $bookingpress_coupon_discounted_amount,
						);						
						$bookingpress_appointment_booking_update_data = array(						
							'bookingpress_coupon_details'          => $bookingpress_coupon_details,
							'bookingpress_coupon_discount_amount'  => $bookingpress_coupon_discounted_amount,							
						);						
						
					}
				}

				if(!empty($bookingpress_complete_payment_gateway)){
					$payment_gateway_name = $bookingpress_complete_payment_gateway;
				}

				//Store Coupon Data in appointment & Payment Log Over
				$bookingpress_is_group_order = apply_filters('bookingpress_check_is_group_order_for_complete_payment_update',false, $entry_id,$bookingpress_order_id);

				if($bookingpress_is_cart || $bookingpress_is_group_order){		
					
					$bookingpress_appointment_booking_update_data['bookingpress_complete_payment_token'] = '';
					$bookingpress_appointment_booking_update_data['bookingpress_appointment_status'] = $bookingpress_ap_status;										
					$wpdb->update($tbl_bookingpress_appointment_bookings, $bookingpress_appointment_booking_update_data, array('bookingpress_order_id' => $bookingpress_order_id) );	
					
					$bookingpress_payment_log_update_data['bookingpress_complete_payment_token'] = '';
					$bookingpress_payment_log_update_data['bookingpress_payment_status'] = 1;
					$bookingpress_payment_log_update_data['bookingpress_transaction_id'] = $transaction_id;
					$bookingpress_payment_log_update_data['bookingpress_payment_gateway'] = $payment_gateway_name;
					$bookingpress_payment_log_update_data['bookingpress_created_at'] = current_time('mysql');
					$wpdb->update($tbl_bookingpress_payment_logs, $bookingpress_payment_log_update_data, array('bookingpress_order_id' => $bookingpress_order_id) );

					$bookingpress_inserted_appointment_ids = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_appointment_booking_id,bookingpress_customer_email FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d", $bookingpress_order_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
					foreach($bookingpress_inserted_appointment_ids as $k2 => $v2){
						$entry_id = $v2['bookingpress_appointment_booking_id'];
						$bookingpress_customer_email = !empty($v2['bookingpress_customer_email']) ? $v2['bookingpress_customer_email'] : '';
						do_action('bookingpress_after_change_appointment_status', $entry_id, $bookingpress_ap_status);
						$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( $bookingpress_email_notification_type, $entry_id,$bookingpress_customer_email );
					}
				}else{


					$bookingpress_appointment_booking_update_data['bookingpress_complete_payment_token'] = '';
					$bookingpress_appointment_booking_update_data['bookingpress_appointment_status'] = $bookingpress_ap_status;	
					$wpdb->update($tbl_bookingpress_appointment_bookings, $bookingpress_appointment_booking_update_data, array('bookingpress_appointment_booking_id' => $entry_id) );
					$bookingpress_payment_log_update_data['bookingpress_complete_payment_token'] = '';
					$bookingpress_payment_log_update_data['bookingpress_payment_status'] = 1;
					$bookingpress_payment_log_update_data['bookingpress_transaction_id'] = $transaction_id;
					$bookingpress_payment_log_update_data['bookingpress_payment_gateway'] = $payment_gateway_name;
					$bookingpress_payment_log_update_data['bookingpress_created_at'] = current_time('mysql');

					$wpdb->update($tbl_bookingpress_payment_logs, $bookingpress_payment_log_update_data, array('bookingpress_appointment_booking_ref' => $entry_id) );					

					do_action('bookingpress_after_change_appointment_status', $entry_id, $bookingpress_ap_status);
					$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( $bookingpress_email_notification_type, $entry_id, $bookingpress_customer_email );					
				}
				return 0;
			}

			$transaction_id = ( ! empty( $transaction_id_field ) && ! empty( $payment_gateway_data[ $transaction_id_field ] ) ) ? $payment_gateway_data[ $transaction_id_field ] : '';

			if(!empty($transaction_id)){
				//Check received transaction id already exists or not
				$bookingpress_exist_transaction_count = $wpdb->get_var($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_transaction_id = %s", $transaction_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

				if($bookingpress_exist_transaction_count > 0){
					do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Transaction '.$transaction_id.' already exists', 'bookingpress_complete_appointment', $bookingpress_exist_transaction_count, $bookingpress_other_debug_log_id );
					return 0;
				}
			}

			if ( ! empty( $entry_id ) && empty($is_cart_order) ) {

				$entry_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d", $entry_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm

				if ( ! empty( $entry_data ) ) {
					$bookingpress_entry_user_id                  = $entry_data['bookingpress_customer_id'];
					$bookingpress_customer_name                  = $entry_data['bookingpress_customer_name'];
					$bookingpress_customer_username              = $entry_data['bookingpress_username'];
					$bookingpress_customer_phone                 = $entry_data['bookingpress_customer_phone'];
					$bookingpress_customer_firstname             = $entry_data['bookingpress_customer_firstname'];
					$bookingpress_customer_lastname              = $entry_data['bookingpress_customer_lastname'];
					$bookingpress_customer_country               = $entry_data['bookingpress_customer_country'];
					$bookingpress_customer_phone_dial_code       = $entry_data['bookingpress_customer_phone_dial_code'];
					$bookingpress_customer_email                 = $entry_data['bookingpress_customer_email'];
					$bookingpress_customer_timezone				 = $entry_data['bookingpress_customer_timezone'];
					$bookingpress_customer_dst_timezone			 = $entry_data['bookingpress_dst_timezone'];
					$bookingpress_service_id                     = $entry_data['bookingpress_service_id'];
					$bookingpress_service_name                   = $entry_data['bookingpress_service_name'];
					$bookingpress_service_price                  = $entry_data['bookingpress_service_price'];
					$bookingpress_service_currency               = $entry_data['bookingpress_service_currency'];
					$bookingpress_service_duration_val           = $entry_data['bookingpress_service_duration_val'];
					$bookingpress_service_duration_unit          = $entry_data['bookingpress_service_duration_unit'];
					$bookingpress_payment_gateway                = $entry_data['bookingpress_payment_gateway'];
					$bookingpress_appointment_date               = $entry_data['bookingpress_appointment_date'];
					$bookingpress_appointment_time               = $entry_data['bookingpress_appointment_time'];
					$bookingpress_appointment_end_time           = $entry_data['bookingpress_appointment_end_time'];
					$bookingpress_appointment_internal_note      = $entry_data['bookingpress_appointment_internal_note'];
					$bookingpress_appointment_send_notifications = $entry_data['bookingpress_appointment_send_notifications'];
					$bookingpress_appointment_status             = $entry_data['bookingpress_appointment_status'];
					$bookingpress_coupon_details                 = $entry_data['bookingpress_coupon_details'];
					$bookingpress_coupon_discounted_amount       = $entry_data['bookingpress_coupon_discount_amount'];
					$bookingpress_deposit_payment_details        = $entry_data['bookingpress_deposit_payment_details'];
					$bookingpress_deposit_amount                 = $entry_data['bookingpress_deposit_amount'];
					$bookingpress_selected_extra_members         = $entry_data['bookingpress_selected_extra_members'];
					$bookingpress_extra_service_details          = $entry_data['bookingpress_extra_service_details'];
					$bookingpress_staff_member_id                = $entry_data['bookingpress_staff_member_id'];
					$bookingpress_staff_member_price             = $entry_data['bookingpress_staff_member_price'];
					$bookingpress_staff_first_name               = $entry_data['bookingpress_staff_first_name'];
					$bookingpress_staff_last_name                = $entry_data['bookingpress_staff_last_name'];
					$bookingpress_staff_email_address            = $entry_data['bookingpress_staff_email_address'];
					$bookingpress_staff_member_details           = $entry_data['bookingpress_staff_member_details'];
					$bookingpress_paid_amount                    = $entry_data['bookingpress_paid_amount'];
					$bookingpress_due_amount                     = $entry_data['bookingpress_due_amount'];
					$bookingpress_total_amount                   = $entry_data['bookingpress_total_amount'];
					$bookingpress_tax_percentage                 = $entry_data['bookingpress_tax_percentage'];
					$bookingpress_tax_amount                     = $entry_data['bookingpress_tax_amount'];
					$bookingpress_price_display_setting          = $entry_data['bookingpress_price_display_setting'];
					$bookingpress_display_tax_order_summary      = $entry_data['bookingpress_display_tax_order_summary'];
					$bookingpress_included_tax_label             = $entry_data['bookingpress_included_tax_label'];
					$bookingpress_complete_payment_token         = $entry_data['bookingpress_complete_payment_token'];
					$bookingpress_complete_payment_url_selection         = $entry_data['bookingpress_complete_payment_url_selection'];
					$bookingpress_complete_payment_url_selection_method         = $entry_data['bookingpress_complete_payment_url_selection_method'];

					$payable_amount = ( ! empty( $payment_amount_field ) && ! empty( $payment_gateway_data[ $payment_amount_field ] ) ) ? $payment_gateway_data[ $payment_amount_field ] : $bookingpress_paid_amount;

					$bookingpress_customer_id = $bookingpress_wpuser_id = $bookingpress_is_customer_create = 0;
					$bookingpress_customer_details = $bookingpress_customers->bookingpress_create_customer( $entry_data, $bookingpress_entry_user_id, $is_front );					
					if ( ! empty( $bookingpress_customer_details ) ) {
						$bookingpress_customer_id = $bookingpress_customer_details['bookingpress_customer_id'];
						$bookingpress_wpuser_id   = $bookingpress_customer_details['bookingpress_wpuser_id'];
						$bookingpress_is_customer_create = !empty($bookingpress_customer_details['bookingpress_is_customer_create']) ? $bookingpress_customer_details['bookingpress_is_customer_create'] : 0;
					}

					$get_form_fields_meta = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_entry_id = %d", 'appointment_form_fields_data', $entry_id ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name. false alarm
					if( !empty( $get_form_fields_meta )){

						$get_form_fields_meta = !empty( $get_form_fields_meta ) ? json_decode( $get_form_fields_meta, true ) : array();
					}

					$bpa_appointemnt_data_form_fields = !empty( $_REQUEST['appointment_data']['form_fields'] ) ? $_REQUEST['appointment_data']['form_fields'] : $get_form_fields_meta['form_fields'];


					if ( ! empty( $bpa_appointemnt_data_form_fields ) && ! empty( $bookingpress_customer_id ) ) {
						$this->bookingpress_insert_customer_field_data( $bookingpress_customer_id, array_map( array( $BookingPress, 'appointment_sanatize_field' ), $bpa_appointemnt_data_form_fields ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST['appointment_data']['form_fields'] has already been sanitized.
					}

					$appointment_booking_fields = array(
						'bookingpress_entry_id'                      => $entry_id,
						'bookingpress_payment_id'                    => 0,
						'bookingpress_customer_id'                   => $bookingpress_customer_id,
						'bookingpress_customer_name'      			 => $bookingpress_customer_name, 
						'bookingpress_username'                      => $bookingpress_customer_username,
						'bookingpress_customer_firstname' 			 => $bookingpress_customer_firstname,
						'bookingpress_customer_lastname'  			 => $bookingpress_customer_lastname,
						'bookingpress_customer_phone'     			 => $bookingpress_customer_phone,
						'bookingpress_customer_country'   			 => $bookingpress_customer_country,
						'bookingpress_customer_phone_dial_code'      => $bookingpress_customer_phone_dial_code,
						'bookingpress_customer_email'     			 => $bookingpress_customer_email, 
						'bookingpress_service_id'                    => $bookingpress_service_id,
						'bookingpress_service_name'                  => $bookingpress_service_name,
						'bookingpress_service_price'                 => $bookingpress_service_price,
						'bookingpress_service_currency'              => $bookingpress_service_currency,
						'bookingpress_service_duration_val'          => $bookingpress_service_duration_val,
						'bookingpress_service_duration_unit'         => $bookingpress_service_duration_unit,
						'bookingpress_appointment_date'              => $bookingpress_appointment_date,
						'bookingpress_appointment_time'              => $bookingpress_appointment_time,
						'bookingpress_appointment_end_time'          => $bookingpress_appointment_end_time,
						'bookingpress_appointment_internal_note'     => $bookingpress_appointment_internal_note,
						'bookingpress_appointment_send_notification' => $bookingpress_appointment_send_notifications,
						'bookingpress_appointment_status'            => $bookingpress_appointment_status,
						'bookingpress_appointment_timezone'			 => $bookingpress_customer_timezone,
						'bookingpress_dst_timezone'				     => $bookingpress_customer_dst_timezone,
						'bookingpress_coupon_details'                => $bookingpress_coupon_details,
						'bookingpress_coupon_discount_amount'        => $bookingpress_coupon_discounted_amount,
						'bookingpress_tax_percentage'                => $bookingpress_tax_percentage,
						'bookingpress_tax_amount'                    => $bookingpress_tax_amount,
						'bookingpress_price_display_setting'         => $bookingpress_price_display_setting,
						'bookingpress_display_tax_order_summary'     => $bookingpress_display_tax_order_summary,
						'bookingpress_included_tax_label'            => $bookingpress_included_tax_label,
						'bookingpress_deposit_payment_details'       => $bookingpress_deposit_payment_details,
						'bookingpress_deposit_amount'                => $bookingpress_deposit_amount,
						'bookingpress_complete_payment_url_selection'	=> $bookingpress_complete_payment_url_selection,
						'bookingpress_complete_payment_url_selection_method' => $bookingpress_complete_payment_url_selection_method,
						'bookingpress_complete_payment_token'        => $bookingpress_complete_payment_token,
						'bookingpress_selected_extra_members'        => $bookingpress_selected_extra_members,
						'bookingpress_extra_service_details'         => $bookingpress_extra_service_details,
						'bookingpress_staff_member_id'               => $bookingpress_staff_member_id,
						'bookingpress_staff_member_price'            => $bookingpress_staff_member_price,
						'bookingpress_staff_first_name'               => $bookingpress_staff_first_name,
						'bookingpress_staff_last_name'                => $bookingpress_staff_last_name,
						'bookingpress_staff_email_address'           => $bookingpress_staff_email_address,
						'bookingpress_staff_member_details'          => $bookingpress_staff_member_details,
						'bookingpress_paid_amount'                   => $bookingpress_paid_amount,
						'bookingpress_due_amount'                    => $bookingpress_due_amount,
						'bookingpress_total_amount'                  => $bookingpress_total_amount,
						'bookingpress_created_at'         			 => current_time('mysql'),
					);

					$appointment_booking_fields = apply_filters( 'bookingpress_modify_appointment_booking_fields_before_insert', $appointment_booking_fields, $entry_data );

					/** Validate again before confirming the payment start */

					/** Check for the paid amount and the received amount */
					if( !empty( $payment_gateway_data ) && 'woocommerce' != $bookingpress_payment_gateway ){

						if( 'stripe' == strtolower( trim( $bookingpress_payment_gateway ) ) && empty( $payment_amount_field  ) ){
							$payment_amount_field = 'amount';
						} else if( 'paypal' == strtolower( trim( $bookingpress_payment_gateway ) ) && empty( $payment_amount_field ) ){
							$payment_amount_field = 'mc_gross';
						}

						if( !empty( $payment_amount_field ) && !preg_match( '/\|/', $payment_amount_field ) ){
							$paid_amount = !empty( $payment_gateway_data[ $payment_amount_field ] ) ? $payment_gateway_data[ $payment_amount_field ] : 0;
						} else {
							$paid_amount = apply_filters( 'bookingpress_retrieve_payment_amount_currency_from_payment_data', 0, $payment_gateway_data, $bookingpress_payment_gateway, false );
						}
						$paid_amount = apply_filters( 'bookingpress_adjust_paid_amount', $paid_amount, strtolower( $bookingpress_payment_gateway ) );

						if( floatval( $bookingpress_paid_amount ) != floatval( $paid_amount ) ){

                            $suspicious_data = wp_json_encode(
                                array(
                                    'paid_amount_entries' => $bookingpress_paid_amount,
                                    'paid_amount_payment' => $paid_amount
                                )
                            );

                            status_header( 400, 'Amount Mismatched' );
                            http_response_code( 400 );
                            do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'prevent suspicious payment due to amount mismatched', 'bookingpress', $suspicious_data, $bookingpress_debug_payment_log_id);
                            die;
                        }

						/** Check for the currency received from the payment gateway data*/
						if( !empty( $payment_currency_field ) && !empty( $payment_gateway_data[ $payment_currency_field ] ) && !preg_match( '/\|/', $payment_currency_field ) ){

							if( strtolower( trim( $payment_gateway_data[ $payment_currency_field ] ) ) != strtolower( trim( $bookingpress_service_currency ) ) ){
								$suspicious_data = wp_json_encode(
									array(
										'service_currency' => $bookingpress_service_currency,
										'paid_in_currency' => $payment_gateway_data[ $payment_currency_field ]
									)
								);
	
								status_header( 400, 'currency Mismatched' );
								http_response_code( 400 );
								do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'prevent suspicious payment due to currency mismatched', 'bookingpress', $suspicious_data, $bookingpress_debug_payment_log_id);
								die;
							}

						} else {
							
							$payment_currency = apply_filters('bookingpress_retrieve_payment_amount_currency_from_payment_data', '', $payment_gateway_data, $bookingpress_payment_gateway, true );

							if( !empty( $payment_currency ) && strtolower( trim( $payment_currency ) ) != strtolower( trim( $bookingpress_service_currency ) ) ){
								$suspicious_data = wp_json_encode(
									array(
										'service_currency' => $bookingpress_service_currency,
										'paid_in_currency' => $payment_currency
									)
								);
	
								status_header( 400, 'currency Mismatched' );
								http_response_code( 400 );
								do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'prevent suspicious payment due to currency mismatched', 'bookingpress', $suspicious_data, $bookingpress_debug_payment_log_id);
								die;
							}
						}
					}
					/** Check for the paid amount and the received amount */

					/** Check if the entry already exists in the double booking table */
					$double_book_entry_id = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_double_booking_id) FROM $tbl_bookingpress_double_bookings WHERE bookingpress_entry_id = %d", $entry_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_double_bookings is table name defined globally. False Positive alarm 

					if( 0 < $double_book_entry_id ){
						status_header( 409 );
						do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'duplicate appointment data already exists in double booking table', 'bookingpress', $double_book_entry_id, $bookingpress_debug_payment_log_id);
						die;
					}
                    
                    $appointment_sid = $appointment_booking_fields['bookingpress_service_id'];
                    $appointment_date = $appointment_booking_fields['bookingpress_appointment_date'];
                    $appointment_stime = $appointment_booking_fields['bookingpress_appointment_time'];
                    $appointment_etime = $appointment_booking_fields['bookingpress_appointment_end_time'];

					$posted_data = array();

					$posted_data['appointment_data']['selected_service_duration'] = $bookingpress_service_duration_val;
					$posted_data['appointment_data']['selected_service_duration_unit'] = $bookingpress_service_duration_unit;

					if( !empty( $bookingpress_staff_member_id ) ){
						$_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] = $bookingpress_staff_member_id;
						$posted_data['appointment_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] = $bookingpress_staff_member_id;
					}
					
					$_POST['appointment_data_obj']['bookingpress_selected_bring_members'] = $bookingpress_selected_extra_members;

					$prevent_booking = $BookingPress->bookingpress_is_appointment_booked( $appointment_sid, $appointment_date, $appointment_stime, $appointment_etime, 0, true, $posted_data );

					if( !empty( $prevent_booking ) && !empty( $prevent_booking['prevent_validation_process'] ) && true == $prevent_booking['prevent_validation_process'] ){
						
						$payer_email = ! empty($payment_gateway_data['payer_email']) ? $payment_gateway_data['payer_email'] : '';
						
						$appointment_prevent_reason = $prevent_booking['response'];
						
						$bookingpress_double_booking_prevent_reasone = array(
							'bookingpress_entry_id' => $entry_id,
							'bookingpress_double_booking_reason' => wp_json_encode( $appointment_prevent_reason ),
							'bookingpress_payment_gateway' => $bookingpress_payment_gateway,
							'bookingpress_payer_email' => $payer_email,
							'bookingpress_transaction_id' => $transaction_id,
							'bookingpress_payment_date_time' => current_time('mysql'),
							'bookingpress_payment_status'  => $payment_status,
							'bookingpress_payment_amount'  => $payable_amount,
							'bookingpress_payment_currency' => $bookingpress_service_currency,
							'bookingpress_payment_type'    => '',
							'bookingpress_payment_response' => '',
							'bookingpress_additional_info' => wp_json_encode( $payment_gateway_data ),
							'bookingpress_request_raw_data' => wp_json_encode( $_REQUEST ),
						);
						
						$wpdb->insert( $tbl_bookingpress_double_bookings, $bookingpress_double_booking_prevent_reasone );
						$duplicate_event_id = $wpdb->insert_id;

						/** start refund process */

						/** Preparing variable to store refund related data into the double booking table */
						$double_booking_update_db_data = array(
							'bookingpress_refund_reason' => 'Crossed or Duplicate booking occurred with appointment data: ' . wp_json_encode( $appointment_prevent_reason )
						);
						/** Check if the payment gateway supports the refund */
						$bpa_payment_gateway_data = $bookingpress_pro_global_options->bookingpress_allowed_refund_payment_gateway_list();

						$bookingpress_refund_response_data['variant'] = 'error';
						$bookingpress_refund_response_data['title'] = esc_html__('Error', 'bookingpress-appointment-booking');
						$bookingpress_refund_response_data['msg'] = esc_html__('Something went wrong while processing the double booking refund', 'bookingpress-appointment-booking');

						$is_refund_supported = 0;
						$is_refund_processed = 0;

						$payment_currency = $BookingPress->bookingpress_get_settings('payment_default_currency','payment_setting');

						if( !empty( $bpa_payment_gateway_data[ $bookingpress_payment_gateway ] ) && 1 == $bpa_payment_gateway_data[ $bookingpress_payment_gateway ]['is_refund_support'] ){

							$double_booking_update_db_data['bookingpress_is_refund_supported'] = 1;

							$is_refund_supported = 1;
							
							$bookingpress_send_refund_data = array(
								'bookingpress_transaction_id' => $transaction_id,
								'refund_type' => 'full', //passing parameter of refund type to full
								'refund_reason' => 'Crossed or Duplicate booking occurred with another appointment',
								'refund_amount' => $payable_amount,
								'default_refund_amount' => $payable_amount,
								'bookingpress_payment_currency' => $payment_currency
							);
							
							if( 'stripe' == $bookingpress_payment_gateway ){
								$bookingpress_send_refund_data['reason'] = 'duplicate';
							}

							$bookingpress_refund_response_data = apply_filters('bookingpress_'.$bookingpress_payment_gateway.'_apply_refund',$bookingpress_refund_response_data,$bookingpress_send_refund_data);	

							$double_booking_update_db_data['bookingpress_refund_response'] = wp_json_encode( $bookingpress_refund_response_data );

							if(!empty($bookingpress_refund_response_data['variant']) && $bookingpress_refund_response_data['variant'] == 'success' ) {
								/** If refund successfully processed */
								$is_refund_processed = 1;
								$bookingpress_refund_response_data['msg']   = esc_html__( 'Refund successfully initiated', 'bookingpress-appointment-booking' );
								$double_booking_update_db_data['bookingpress_is_refunded'] = 1;
								//$this->bookingpress_after_refund_success($bookingpress_refund_response_data,$bookingpress_refund_data,$appointment_data,$refund_intiate_from );
							} else {
								/** if refund not processed and error occurred */
								$double_booking_update_db_data['bookingpress_is_refunded'] = 0;
							}

						} else {
							/** Refund is not supported by the payment gateway */
							$bookingpress_refund_response_data['bookingpress_refund_response'] = 'Refund is not supported by payment gateway ' . $bookingpress_payment_gateway;
							$double_booking_update_db_data['bookingpress_is_refund_supported'] = 0;
							$double_booking_update_db_data['bookingpress_refund_response'] = $bookingpress_refund_response_data;
							$double_booking_update_db_data['bookingpress_is_refunded'] = 0;
						}

						$wpdb->update(
							$tbl_bookingpress_double_bookings,
							$double_booking_update_db_data,
							array(
								'bookingpress_double_booking_id' => $duplicate_event_id
							)
						);

						/** sending email code to customer for refund related start */

						$date_format = $BookingPress->bookingpress_get_settings( 'default_date_format', 'general_setting');
						$time_format = $BookingPress->bookingpress_get_settings( 'default_time_format', 'general_setting');

						
                        $payment_currency = $BookingPress->bookingpress_get_currency_symbol( $payment_currency );

						$customer_email_subject = __('Appointment Cancellation Notification', 'bookingpress-appointment-booking');
						$customer_email_message = __("Dear %customer_first_name% %customer_last_name%,<br/><br/>This is regarding your recent payment and appointment scheduling with the below details which is not successfully booked, despite your payment being processed due to unforeseen technical glitches.<br/><br/>%service_name% - %appointment_date_time% - %appointment_amount%<br/><br/>We will process the refund for the full amount of your payment and it will be credited back to your account within the next 4-7 business days. We truly regret any inconvenience this may have caused you and understand the importance of having a smooth and hassle-free experience with our services.<br/><br/>Best regards,<br/>%company_name%<br/>%company_address%<br/>%company_phone%<br/>%company_website%", 'bookingpress-appointment-booking'); //phpcs:ignore

						$customer_email_message = str_replace( '%customer_first_name%', $bookingpress_customer_firstname, $customer_email_message );
						$customer_email_message = str_replace( '%customer_last_name%', $bookingpress_customer_lastname, $customer_email_message );
						
						$customer_email_message = str_replace( '%service_name%', $bookingpress_service_name, $customer_email_message );

						$customer_appointment_date = date( $date_format, strtotime( $bookingpress_appointment_date ) );
						
						$customer_appointment_time = date( $time_format, strtotime( $bookingpress_appointment_time ) );
						$customer_appointment_end_time = date( $time_format, strtotime( $bookingpress_appointment_end_time ) );

						$customer_email_message = str_replace( '%appointment_date_time%', $customer_appointment_date.' ' . $customer_appointment_time . ' to ' . $customer_appointment_end_time, $customer_email_message );
						$customer_email_message = str_replace( '%appointment_amount%', $bookingpress_paid_amount . ' ' . $payment_currency, $customer_email_message );

						$company_name = $BookingPress->bookingpress_get_settings('company_name', 'company_setting');
						$company_address = $BookingPress->bookingpress_get_settings('company_address', 'company_setting');
						$company_phone = $BookingPress->bookingpress_get_settings('company_phone', 'company_setting');
						$company_website = $BookingPress->bookingpress_get_settings('company_website', 'company_setting');

						$customer_email_message = str_replace( '%company_name%', $company_name, $customer_email_message );
						$customer_email_message = str_replace( '%company_address%', $company_address, $customer_email_message );
						$customer_email_message = str_replace( '%company_phone%', $company_phone, $customer_email_message );
						$customer_email_message = str_replace( '%company_website%', $company_website, $customer_email_message );

						$from_name = $BookingPress->bookingpress_get_settings('sender_name', 'notification_setting');
						$from_email = $BookingPress->bookingpress_get_settings('sender_email', 'notification_setting');

						$reply_to_name = $BookingPress->bookingpress_get_settings('sender_name', 'notification_setting');
						$reply_to = $BookingPress->bookingpress_get_settings('sender_email', 'notification_setting');

						$bookingpress_email_notifications->bookingpress_send_custom_email_notifications( $bookingpress_customer_email, stripslashes_deep( $customer_email_subject ), stripslashes_deep( $customer_email_message ), stripslashes_deep( $from_name ), $from_email, $reply_to, stripslashes_deep( $reply_to_name ) );

						/** sending email code to customer for refund related end */


						/** sending email code to admin/staffmember for refund related start */

						$admin_email_subject = __('Appointment cancelled due to conflict', 'bookingpress-appointment-booking');
						if( 1 == $is_refund_processed ){
							$admin_email_content = __('Dear Administrator,<br/>One of the recently booked appointment has conflict with another appointment with booking ID - %bookingpress_appointment_id% on our booking platform. So, the system has automatically initiated a refund for the payment of %appointment_amount% to avoid any double bookings.<br/><br/>Below are the appointment and payment details for your reference:<br/>Appointment Date: %appointment_date%<br/>Appointment Time: %appointment_time%<br/>Payment ID: %transaction_id%<br/>Payment Amount: %appointment_amount%<br/>Payment Gateway:%bookingpress_payment_gateway%<br/>First name & Last name: %customer_first_name% %customer_last_name%,<br/>Customer Full Name: %customer_full_name%<br/>Customer Email: %customer_email%<br/>Customer Phone: %customer_phone%<br/><br/>Best regards,<br/>%company_name%<br/>%company_address%<br/>%company_phone%<br/>%company_website%', "bookingpress-appointment-booking"); //phpcs:ignore
						} else {
							$admin_email_content = __('Dear Administrator,<br/>One of the recently booked appointment has conflict with another appointment with booking ID - %bookingpress_appointment_id% on our booking platform. So, an immediate action is required to handle the refund process for their payment.<br/><br/>Below are the appointment and payment details for your reference:<br/>Appointment Date: %appointment_date%<br/>Appointment Time: %appointment_time%<br/>Payment ID: %transaction_id%<br/>Payment Amount: %appointment_amount%<br/>Payment Gateway:%bookingpress_payment_gateway%<br/>First name & Last name: %customer_first_name% %customer_last_name%,<br/>Customer Full Name: %customer_full_name%<br/>Customer Email: %customer_email%<br/>Customer Phone: %customer_phone%<br/><br/>Best regards,<br/>%company_name%<br/>%company_address%<br/>%company_phone%<br/>%company_website%', "bookingpress-appointment-booking"); //phpcs:ignore
						}

						$admin_email_content = str_replace( '%customer_first_name%', $bookingpress_customer_firstname, $admin_email_content );
						$admin_email_content = str_replace( '%customer_last_name%', $bookingpress_customer_lastname, $admin_email_content );

						$admin_email_content = str_replace( '%customer_full_name%', $bookingpress_customer_firstname . ' ' . $bookingpress_customer_lastname, $admin_email_content  );
						$admin_email_content = str_replace( '%customer_email%', $bookingpress_customer_email, $admin_email_content );
						$admin_email_content = str_replace( '%customer_phone%', $bookingpress_customer_phone, $admin_email_content );
						
						$admin_email_content = str_replace( '%service_name%', $bookingpress_service_name, $admin_email_content );

						$bkp_appointment_date = date( $date_format, strtotime( $bookingpress_appointment_date ) );
						$bkp_appointment_time = date( $time_format, strtotime( $bookingpress_appointment_time ) );
						$bkp_appointment_end_time = date( $time_format, strtotime( $bookingpress_appointment_end_time ) );

						$admin_email_content = str_replace( '%appointment_date_time%', $bkp_appointment_date.' '. $bkp_appointment_time . ' to ' .$bkp_appointment_end_time, $admin_email_content );
						$admin_email_content = str_replace( '%appointment_date%', $bkp_appointment_date, $admin_email_content );
						$admin_email_content = str_replace( '%appointment_time%', $bkp_appointment_time . ' to ' . $bkp_appointment_end_time, $admin_email_content );
						$admin_email_content = str_replace( '%appointment_amount%', $bookingpress_paid_amount . ' ' . $payment_currency, $admin_email_content );

						$company_name = $BookingPress->bookingpress_get_settings('company_name', 'company_setting');
						$company_address = $BookingPress->bookingpress_get_settings('company_address', 'company_setting');
						$company_phone = $BookingPress->bookingpress_get_settings('company_phone', 'company_setting');
						$company_website = $BookingPress->bookingpress_get_settings('company_website', 'company_setting');

						$admin_email_content = str_replace( '%company_name%', $company_name, $admin_email_content );
						$admin_email_content = str_replace( '%company_address%', $company_address, $admin_email_content );
						$admin_email_content = str_replace( '%company_phone%', $company_phone, $admin_email_content );
						$admin_email_content = str_replace( '%company_website%', $company_website, $admin_email_content );

						$conflicted_appointment = $appointment_prevent_reason['data'];

						$conflicted_appointment_booking_id = $conflicted_appointment['bookingpress_booking_id'];
						$conflicted_appointment_date = date( $date_format, strtotime( $conflicted_appointment['bookingpress_appointment_date'] ) );
						$conflicted_appointment_time = date( $time_format, strtotime( $conflicted_appointment['bookingpress_appointment_time'] ) ) . '-' . date( $time_format, strtotime( $conflicted_appointment['bookingpress_appointment_end_time'] ) );
						$conflicted_appointment_service = $conflicted_appointment['bookingpress_service_name'];

						$admin_email_content = str_replace( '%bookingpress_appointment_id%', '#'.$conflicted_appointment_booking_id, $admin_email_content );
						$admin_email_content = str_replace( '%conflicted_appointment_service%', $conflicted_appointment_service, $admin_email_content );
						$admin_email_content = str_replace( '%conflicted_appointment_date%', $conflicted_appointment_date, $admin_email_content );
						$admin_email_content = str_replace( '%conflicted_appointment_time%', $conflicted_appointment_time, $admin_email_content );

						$admin_email_content = str_replace( '%bookingpress_payment_gateway%', $bookingpress_payment_gateway, $admin_email_content );
						$admin_email_content = str_replace( '%transaction_id%', $transaction_id, $admin_email_content );

						$bookingpress_admin_email = $BookingPress->bookingpress_get_settings('admin_email', 'notification_setting');

						$from_name = $BookingPress->bookingpress_get_settings('sender_name', 'notification_setting');
						$from_email = $BookingPress->bookingpress_get_settings('sender_email', 'notification_setting');

						$reply_to_name = $BookingPress->bookingpress_get_settings('sender_name', 'notification_setting');
						$reply_to = $BookingPress->bookingpress_get_settings('sender_email', 'notification_setting');

						$bookingpress_email_notifications->bookingpress_send_custom_email_notifications( $bookingpress_admin_email, stripslashes_deep( $admin_email_subject ), stripslashes_deep( $admin_email_content ), stripslashes_deep( $from_name ), $from_email, $reply_to, stripslashes_deep( $reply_to_name ) );


						/** sending email code to admin/staffmember for refund related end */

						/** end refund process */


						status_header( 409, $appointment_prevent_reason['message'] );
						do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'prevent duplicate appointment', 'bookingpress', $duplicate_event_id, $bookingpress_debug_payment_log_id);
						die;
					}

                    /** Validate again before confirming the payment end */

					do_action( 'bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'before insert appointment', 'bookingpress pro', $appointment_booking_fields, $bookingpress_debug_payment_log_id );

					$inserted_booking_id = $BookingPress->bookingpress_insert_appointment_logs( $appointment_booking_fields );
				
					//Update appointment id in appointment_meta table
					$wpdb->update( $tbl_bookingpress_appointment_meta, array('bookingpress_appointment_id' => $inserted_booking_id), array('bookingpress_entry_id' => $entry_id) );

					// Update coupon usage counter if coupon code use
					if ( ! empty( $bookingpress_coupon_details ) ) {
						$bookingpress_coupon_data = json_decode( $bookingpress_coupon_details, true );
						if ( ! empty( $bookingpress_coupon_data ) && is_array( $bookingpress_coupon_data ) ) {
							$coupon_id = $bookingpress_coupon_data['coupon_data']['bookingpress_coupon_id'];
							$bookingpress_coupons->bookingpress_update_coupon_usage_counter( $coupon_id );
						}
					}

					if ( ! empty( $inserted_booking_id ) ) {
						$service_time_details = $BookingPress->bookingpress_get_service_end_time( $bookingpress_service_id, $bookingpress_appointment_time, $bookingpress_service_duration_val, $bookingpress_service_duration_unit );						
						$service_start_time   = $service_time_details['service_start_time'];
						$service_end_time     = $service_time_details['service_end_time'];

						$payer_email = ! empty( $payment_gateway_data['payer_email'] ) ? $payment_gateway_data['payer_email'] : $bookingpress_customer_email;

						//$bookingpress_last_invoice_id = $BookingPress->bookingpress_get_settings( 'bookingpress_last_invoice_id', 'invoice_setting' );
						global $tbl_bookingpress_settings;
						$bookingpress_last_invoice_id = $wpdb->get_var( $wpdb->prepare("SELECT setting_value FROM $tbl_bookingpress_settings WHERE setting_name = %s AND setting_type = %s", 'bookingpress_last_invoice_id', 'invoice_setting' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_settings is a table name. false alarm

						$bookingpress_last_invoice_id++;
						
						$BookingPress->bookingpress_update_settings( 'bookingpress_last_invoice_id', 'invoice_setting', $bookingpress_last_invoice_id );

						$bookingpress_last_invoice_id = apply_filters('bookingpress_modify_invoice_id_externally', $bookingpress_last_invoice_id);

						if($bookingpress_payment_gateway == "on-site"){
							$payment_status =  2;
						}

						$payment_log_data = array(
							'bookingpress_invoice_id'              => $bookingpress_last_invoice_id,
							'bookingpress_appointment_booking_ref' => $inserted_booking_id,
							'bookingpress_customer_id'             => $bookingpress_customer_id,
							'bookingpress_customer_name'           => $bookingpress_customer_name,  
							'bookingpress_username'                => $bookingpress_customer_username,
							'bookingpress_customer_firstname'      => $bookingpress_customer_firstname,
							'bookingpress_customer_lastname'       => $bookingpress_customer_lastname,
							'bookingpress_customer_phone'          => $bookingpress_customer_phone,
							'bookingpress_customer_country'        => $bookingpress_customer_country,
							'bookingpress_customer_phone_dial_code' => $bookingpress_customer_phone_dial_code,
							'bookingpress_customer_email'          => $bookingpress_customer_email,
							'bookingpress_service_id'              => $bookingpress_service_id,
							'bookingpress_service_name'            => $bookingpress_service_name,
							'bookingpress_service_price'           => $bookingpress_service_price,
							'bookingpress_payment_currency'        => $bookingpress_service_currency,
							'bookingpress_service_duration_val'    => $bookingpress_service_duration_val,
							'bookingpress_service_duration_unit'   => $bookingpress_service_duration_unit,
							'bookingpress_appointment_date'        => $bookingpress_appointment_date,
							'bookingpress_appointment_start_time'  => $bookingpress_appointment_time,
							'bookingpress_appointment_end_time'    => $bookingpress_appointment_end_time,
							'bookingpress_payment_gateway'         => $bookingpress_payment_gateway,
							'bookingpress_payer_email'             => $payer_email,
							'bookingpress_transaction_id'          => $transaction_id,
							'bookingpress_payment_date_time'       => current_time( 'mysql' ),
							'bookingpress_payment_status'          => $payment_status,
							'bookingpress_payment_amount'          => $payable_amount,
							'bookingpress_payment_currency'        => $bookingpress_service_currency,
							'bookingpress_payment_type'            => '',
							'bookingpress_payment_response'        => '',
							'bookingpress_additional_info'         => '',
							'bookingpress_coupon_details'          => $bookingpress_coupon_details,
							'bookingpress_coupon_discount_amount'  => $bookingpress_coupon_discounted_amount,
							'bookingpress_tax_percentage'          => $bookingpress_tax_percentage,
							'bookingpress_tax_amount'              => $bookingpress_tax_amount,
							'bookingpress_price_display_setting'   => $bookingpress_price_display_setting,
							'bookingpress_display_tax_order_summary' => $bookingpress_display_tax_order_summary,
							'bookingpress_included_tax_label'      => $bookingpress_included_tax_label,
							'bookingpress_deposit_payment_details' => $bookingpress_deposit_payment_details,
							'bookingpress_deposit_amount'          => $bookingpress_deposit_amount,
							'bookingpress_staff_member_id'         => $bookingpress_staff_member_id,
							'bookingpress_staff_member_price'      => $bookingpress_staff_member_price,
							'bookingpress_staff_first_name'        => $bookingpress_staff_first_name,
							'bookingpress_staff_last_name'         => $bookingpress_staff_last_name,
							'bookingpress_staff_email_address'     => $bookingpress_staff_email_address,
							'bookingpress_staff_member_details'    => $bookingpress_staff_member_details,
							'bookingpress_paid_amount'             => $bookingpress_paid_amount,
							'bookingpress_due_amount'              => $bookingpress_due_amount,
							'bookingpress_total_amount'            => $bookingpress_total_amount,
							'bookingpress_created_at'              => current_time( 'mysql' ),
						);

						/* Condition add if payment done with deposit then payment status consider as '4' */
						/* Please make sure that only deposit has been payed. Skip this if the 100% deposit has been paid */
						//----------------------------------------------
						$bookingpress_deposit_payment_details = !empty( $bookingpress_deposit_payment_details ) ? json_decode($bookingpress_deposit_payment_details, TRUE) : array();
						if(!empty($bookingpress_deposit_payment_details) && 0 < $bookingpress_due_amount ){
							$payment_log_data['bookingpress_payment_status'] = 4;
							$payment_log_data['bookingpress_mark_as_paid'] = 0;
						}
						//----------------------------------------------

						$payment_log_data = apply_filters( 'bookingpress_modify_payment_log_fields_before_insert', $payment_log_data, $entry_data );

						do_action( 'bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'before insert payment', 'bookingpress pro', $payment_log_data, $bookingpress_debug_payment_log_id );

						$payment_log_id = $BookingPress->bookingpress_insert_payment_logs( $payment_log_data );
						if(!empty($payment_log_id)){
                            $wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_payment_id' => $payment_log_id), array('bookingpress_appointment_booking_id' => $inserted_booking_id));
							$wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_booking_id' => $bookingpress_last_invoice_id), array('bookingpress_appointment_booking_id' => $inserted_booking_id));
                        }

						$bookingpress_email_notification_type = '';
						if ( $bookingpress_appointment_status == '2' ) {
							$bookingpress_email_notification_type = 'Appointment Pending';
						} elseif ( $bookingpress_appointment_status == '1' ) {
							$bookingpress_email_notification_type = 'Appointment Approved';
						} elseif ( $bookingpress_appointment_status == '3' ) {
							$bookingpress_email_notification_type = 'Appointment Canceled';
						} elseif ( $bookingpress_appointment_status == '4' ) {
							$bookingpress_email_notification_type = 'Appointment Rejected';
						}

						do_action('bookingpress_after_add_appointment_from_backend', $inserted_booking_id, array(), $entry_id);

						$bookingpress_email_notification_type = apply_filters('bookingpress_modify_send_email_notification_type',$bookingpress_email_notification_type,$bookingpress_appointment_status);
						do_action( 'bookingpress_after_book_appointment', $inserted_booking_id, $entry_id, $payment_gateway_data );
						if($bookingpress_is_customer_create == 1 && !empty($bookingpress_customer_id)) {
							do_action( 'bookingpress_after_create_new_customer',$bookingpress_customer_id);
						}
						if($bookingpress_is_customer_create == 0 && !empty($bookingpress_customer_id)) {
							do_action( 'bookingpress_after_update_customer_data',$bookingpress_customer_id);
						} 
						$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( $bookingpress_email_notification_type, $inserted_booking_id, $bookingpress_customer_email );
						return $payment_log_id;
					}
				}
			}else if(!empty($entry_id) && !empty($is_cart_order) ){
				$entry_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_order_id = %d", $entry_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm

				if ( ! empty( $entry_data ) ) {
					$bookingpress_inserted_appointment_ids = array();
					$total_entry_data = count( $entry_data );
					$prevented_entry_data = 0;
					$bookingpress_customer_id = $bookingpress_wpuser_id = $bookingpress_is_customer_create = 0;
					foreach($entry_data as $k => $v){
						$bookingpress_entry_id                       = $v['bookingpress_entry_id'];
						$bookingpress_order_id                       = $v['bookingpress_order_id'];
						$bookingpress_entry_user_id                  = $v['bookingpress_customer_id'];
						$bookingpress_customer_name                  = $v['bookingpress_customer_name'];
						$bookingpress_customer_phone                 = $v['bookingpress_customer_phone'];
						$bookingpress_customer_firstname             = $v['bookingpress_customer_firstname'];
						$bookingpress_customer_lastname              = $v['bookingpress_customer_lastname'];
						$bookingpress_customer_country               = $v['bookingpress_customer_country'];
						$bookingpress_customer_phone_dial_code       = $v['bookingpress_customer_phone_dial_code'];
						$bookingpress_customer_email                 = $v['bookingpress_customer_email'];
						$bookingpress_customer_timezone              = $v['bookingpress_customer_timezone'];
						$bookingpress_dst_timezone				     = $v['bookingpress_dst_timezone'];
						$bookingpress_service_id                     = $v['bookingpress_service_id'];
						$bookingpress_service_name                   = $v['bookingpress_service_name'];
						$bookingpress_service_price                  = $v['bookingpress_service_price'];
						$bookingpress_service_currency               = $v['bookingpress_service_currency'];
						$bookingpress_service_duration_val           = $v['bookingpress_service_duration_val'];
						$bookingpress_service_duration_unit          = $v['bookingpress_service_duration_unit'];
						$bookingpress_payment_gateway                = $v['bookingpress_payment_gateway'];
						$bookingpress_appointment_date               = $v['bookingpress_appointment_date'];
						$bookingpress_appointment_time               = $v['bookingpress_appointment_time'];
						$bookingpress_appointment_end_time           = $v['bookingpress_appointment_end_time'];
						$bookingpress_appointment_internal_note      = $v['bookingpress_appointment_internal_note'];
						$bookingpress_appointment_send_notifications = $v['bookingpress_appointment_send_notifications'];
						$bookingpress_appointment_status             = $v['bookingpress_appointment_status'];
						$bookingpress_coupon_details                 = $v['bookingpress_coupon_details'];
						$bookingpress_coupon_discounted_amount       = $v['bookingpress_coupon_discount_amount'];
						$bookingpress_deposit_payment_details        = $v['bookingpress_deposit_payment_details'];
						$bookingpress_deposit_amount                 = $v['bookingpress_deposit_amount'];
						$bookingpress_selected_extra_members         = $v['bookingpress_selected_extra_members'];
						$bookingpress_extra_service_details          = $v['bookingpress_extra_service_details'];
						$bookingpress_staff_member_id                = $v['bookingpress_staff_member_id'];
						$bookingpress_staff_member_price             = $v['bookingpress_staff_member_price'];
						$bookingpress_staff_first_name               = $v['bookingpress_staff_first_name'];
						$bookingpress_staff_last_name                = $v['bookingpress_staff_last_name'];
						$bookingpress_staff_email_address            = $v['bookingpress_staff_email_address'];
						$bookingpress_staff_member_details           = $v['bookingpress_staff_member_details'];
						$bookingpress_paid_amount                    = $v['bookingpress_paid_amount'];
						$bookingpress_due_amount                     = $v['bookingpress_due_amount'];
						$bookingpress_total_amount                   = $v['bookingpress_total_amount'];
						$bookingpress_tax_percentage                 = $v['bookingpress_tax_percentage'];
						$bookingpress_tax_amount                     = $v['bookingpress_tax_amount'];
						$bookingpress_price_display_setting          = $v['bookingpress_price_display_setting'];
						$bookingpress_display_tax_order_summary      = $v['bookingpress_display_tax_order_summary'];
						$bookingpress_included_tax_label             = $v['bookingpress_included_tax_label'];

						$payable_amount = ( ! empty( $payment_amount_field ) && ! empty( $payment_gateway_data[ $payment_amount_field ] ) ) ? $payment_gateway_data[ $payment_amount_field ] : $bookingpress_paid_amount;

						/** Check for the paid amount and the received amount */
						if( !empty( $payment_gateway_data ) && 'woocommerce' != $bookingpress_payment_gateway ){

							if( 'stripe' == strtolower( trim( $bookingpress_payment_gateway ) ) && empty( $payment_amount_field  ) ){
								$payment_amount_field = 'amount';
							} else if( 'paypal' == strtolower( trim( $bookingpress_payment_gateway ) ) && empty( $payment_amount_field ) ){
								$payment_amount_field = 'mc_gross';
							}

							if( !empty( $payment_amount_field ) && !preg_match( '/\|/', $payment_amount_field ) ){	
								$paid_amount = !empty( $payment_gateway_data[ $payment_amount_field ] ) ? $payment_gateway_data[ $payment_amount_field ] : 0;
							} else {
								$paid_amount = apply_filters( 'bookingpress_retrieve_payment_amount_currency_from_payment_data', 0, $payment_gateway_data, $bookingpress_payment_gateway, false );
							}
							$paid_amount = apply_filters( 'bookingpress_adjust_paid_amount', $paid_amount, strtolower( $bookingpress_payment_gateway ) );

							if( floatval( $bookingpress_paid_amount ) != floatval( $paid_amount ) ){

								$suspicious_data = wp_json_encode(
									array(
										'paid_amount_entries' => $bookingpress_paid_amount,
										'paid_amount_payment' => $paid_amount
									)
								);

								status_header( 400, 'Amount Mismatched' );
								http_response_code( 400 );
								do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'prevent suspicious payment due to amount mismatched', 'bookingpress', $suspicious_data, $bookingpress_debug_payment_log_id);
								die;
							}

							/** Check for the currency received from the payment gateway data*/
							if( !empty( $payment_currency_field ) && !empty( $payment_gateway_data[ $payment_currency_field ] ) && !preg_match( '/\|/', $payment_currency_field ) ){

								if( strtolower( trim( $payment_gateway_data[ $payment_currency_field ] ) ) != strtolower( trim( $bookingpress_service_currency ) ) ){
									$suspicious_data = wp_json_encode(
										array(
											'service_currency' => $bookingpress_service_currency,
											'paid_in_currency' => $payment_gateway_data[ $payment_currency_field ]
										)
									);
		
									status_header( 400, 'currency Mismatched' );
									http_response_code( 400 );
									do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'prevent suspicious payment due to currency mismatched', 'bookingpress', $suspicious_data, $bookingpress_debug_payment_log_id);
									die;
								}

							} else {
								
								$payment_currency = apply_filters('bookingpress_retrieve_payment_amount_currency_from_payment_data', '', $payment_gateway_data, $bookingpress_payment_gateway, true );

								if( !empty( $payment_currency ) && strtolower( trim( $payment_currency ) ) != strtolower( trim( $bookingpress_service_currency ) ) ){
									$suspicious_data = wp_json_encode(
										array(
											'service_currency' => $bookingpress_service_currency,
											'paid_in_currency' => $payment_currency
										)
									);
		
									status_header( 400, 'currency Mismatched' );
									http_response_code( 400 );
									do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'prevent suspicious payment due to currency mismatched', 'bookingpress', $suspicious_data, $bookingpress_debug_payment_log_id);
									die;
								}
							}
						}
						/** Check for the paid amount and the received amount */

						/** Check if the entry already exists in the double booking table */
						$double_book_entry_id = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_double_booking_id) FROM $tbl_bookingpress_double_bookings WHERE bookingpress_entry_id = %d", $v['bookingpress_entry_id'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_double_bookings is table name defined globally. False Positive alarm 

						if( 0 < $double_book_entry_id ){
							//status_header( 409 );
							do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'duplicate appointment data already exists in double booking table', 'bookingpress', $v['bookingpress_entry_id'], $bookingpress_debug_payment_log_id);
							continue;
						}
						
						$appointment_sid = $v['bookingpress_service_id'];
						$appointment_date = $v['bookingpress_appointment_date'];
						$appointment_stime = $v['bookingpress_appointment_time'];
						$appointment_etime = $v['bookingpress_appointment_end_time'];

						$posted_data = array();

						$posted_data['appointment_data']['selected_service_duration'] = $bookingpress_service_duration_val;
						$posted_data['appointment_data']['selected_service_duration_unit'] = $bookingpress_service_duration_unit;

						if( !empty( $bookingpress_staff_member_id ) ){
							$_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] = $bookingpress_staff_member_id;
							$posted_data['appointment_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] = $bookingpress_staff_member_id;
						}
						
						$_POST['appointment_data_obj']['bookingpress_selected_bring_members'] = $bookingpress_selected_extra_members;

						$prevent_booking = $BookingPress->bookingpress_is_appointment_booked( $appointment_sid, $appointment_date, $appointment_stime, $appointment_etime, 0, true, $posted_data );

						if( !empty( $prevent_booking ) && true == $prevent_booking['prevent_validation_process'] ){
							
							$payer_email = ! empty($payment_gateway_data['payer_email']) ? $payment_gateway_data['payer_email'] : '';
							
							$appointment_prevent_reason = $prevent_booking['response'];

							$bookingpress_double_booking_prevent_reasone = array(
								'bookingpress_entry_id' => $v['bookingpress_entry_id'],
								'bookingpress_double_booking_reason' => wp_json_encode( $appointment_prevent_reason ),
								'bookingpress_payment_gateway' => $bookingpress_payment_gateway,
								'bookingpress_payer_email' => $payer_email,
								'bookingpress_transaction_id' => $transaction_id,
								'bookingpress_payment_date_time' => current_time('mysql'),
								'bookingpress_payment_status'  => $payment_status,
								'bookingpress_payment_amount'  => $payable_amount,
								'bookingpress_payment_currency' => $bookingpress_service_currency,
								'bookingpress_payment_type'    => '',
								'bookingpress_payment_response' => '',
								'bookingpress_additional_info' => wp_json_encode( $payment_gateway_data ),
								'bookingpress_request_raw_data' => wp_json_encode( $_REQUEST ),
							);
							
							$wpdb->insert( $tbl_bookingpress_double_bookings, $bookingpress_double_booking_prevent_reasone );
							$duplicate_event_id = $wpdb->insert_id;

							//status_header( 409, $appointment_prevent_reason['message'] );
							$prevented_entry_data++;
							do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'prevent duplicate appointment', 'bookingpress', $duplicate_event_id, $bookingpress_debug_payment_log_id);
							continue;
						}

						/** Validate again before confirming the payment end */

						$bookingpress_customer_details = $bookingpress_customers->bookingpress_create_customer( $v, $bookingpress_entry_user_id, $is_front, 0, $bookingpress_customer_timezone );
						if ( ! empty( $bookingpress_customer_details ) ) {
							$bookingpress_customer_id = $bookingpress_customer_details['bookingpress_customer_id'];
							$bookingpress_wpuser_id   = $bookingpress_customer_details['bookingpress_wpuser_id'];
							$bookingpress_is_customer_create = !empty($bookingpress_customer_details['bookingpress_is_customer_create']) ? $bookingpress_customer_details['bookingpress_is_customer_create'] : 0;
						}

						$get_form_fields_meta = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_order_id = %d", 'appointment_details', $entry_id ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name. false alarm

						if( !empty( $get_form_fields_meta )){
							$get_form_fields_meta = !empty( $get_form_fields_meta ) ? json_decode( $get_form_fields_meta, true ) : array();
						}

						$bpa_appointemnt_data_form_fields = !empty( $_REQUEST['appointment_data']['form_fields'] ) ? $_REQUEST['appointment_data']['form_fields'] : $get_form_fields_meta['form_fields'];

						if ( ! empty( $bpa_appointemnt_data_form_fields ) && ! empty( $bookingpress_customer_id ) ) {
							$this->bookingpress_insert_customer_field_data( $bookingpress_customer_id, array_map( array( $BookingPress, 'appointment_sanatize_field'), $bpa_appointemnt_data_form_fields ) ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST['appointment_data']['form_fields'] has already been sanitized.
						}

						$appointment_booking_fields = array(
							'bookingpress_entry_id'                      => $bookingpress_entry_id,
							'bookingpress_order_id'                      => $bookingpress_order_id,
							'bookingpress_is_cart'                       => 1,
							'bookingpress_payment_id'                    => 0,
							'bookingpress_customer_id'                   => $bookingpress_customer_id,
							'bookingpress_customer_name'      			 => $bookingpress_customer_name, 
							'bookingpress_customer_firstname' 			 => $bookingpress_customer_firstname,
							'bookingpress_customer_lastname'  			 => $bookingpress_customer_lastname,
							'bookingpress_customer_phone'     			 => $bookingpress_customer_phone,
							'bookingpress_customer_country'   			 => $bookingpress_customer_country,
							'bookingpress_customer_phone_dial_code'      => $bookingpress_customer_phone_dial_code,
							'bookingpress_customer_email'     			 => $bookingpress_customer_email, 
							'bookingpress_service_id'                    => $bookingpress_service_id,
							'bookingpress_service_name'                  => $bookingpress_service_name,
							'bookingpress_service_price'                 => $bookingpress_service_price,
							'bookingpress_service_currency'              => $bookingpress_service_currency,
							'bookingpress_service_duration_val'          => $bookingpress_service_duration_val,
							'bookingpress_service_duration_unit'         => $bookingpress_service_duration_unit,
							'bookingpress_appointment_date'              => $bookingpress_appointment_date,
							'bookingpress_appointment_time'              => $bookingpress_appointment_time,
							'bookingpress_appointment_end_time'          => $bookingpress_appointment_end_time,
							'bookingpress_appointment_internal_note'     => $bookingpress_appointment_internal_note,
							'bookingpress_appointment_send_notification' => $bookingpress_appointment_send_notifications,
							'bookingpress_appointment_status'            => $bookingpress_appointment_status,
							'bookingpress_appointment_timezone'			 => $bookingpress_customer_timezone,
							'bookingpress_dst_timezone'				     => $bookingpress_dst_timezone,
							'bookingpress_coupon_details'                => $bookingpress_coupon_details,
							'bookingpress_coupon_discount_amount'        => $bookingpress_coupon_discounted_amount,
							'bookingpress_tax_percentage'                => $bookingpress_tax_percentage,
							'bookingpress_tax_amount'                    => $bookingpress_tax_amount,
							'bookingpress_price_display_setting'         => $bookingpress_price_display_setting,
							'bookingpress_display_tax_order_summary'     => $bookingpress_display_tax_order_summary,
							'bookingpress_included_tax_label'            => $bookingpress_included_tax_label,
							'bookingpress_deposit_payment_details'       => $bookingpress_deposit_payment_details,
							'bookingpress_deposit_amount'                => $bookingpress_deposit_amount,
							'bookingpress_selected_extra_members'        => $bookingpress_selected_extra_members,
							'bookingpress_extra_service_details'         => $bookingpress_extra_service_details,
							'bookingpress_staff_member_id'               => $bookingpress_staff_member_id,
							'bookingpress_staff_member_price'            => $bookingpress_staff_member_price,
							'bookingpress_staff_first_name'               => $bookingpress_staff_first_name,
							'bookingpress_staff_last_name'                => $bookingpress_staff_last_name,
							'bookingpress_staff_email_address'           => $bookingpress_staff_email_address,
							'bookingpress_staff_member_details'          => $bookingpress_staff_member_details,
							'bookingpress_paid_amount'                   => $bookingpress_paid_amount,
							'bookingpress_due_amount'                    => $bookingpress_due_amount,
							'bookingpress_total_amount'                  => $bookingpress_total_amount,
							'bookingpress_created_at'         			 => current_time('mysql'),
						);

						$appointment_booking_fields = apply_filters( 'bookingpress_modify_appointment_booking_fields_before_insert', $appointment_booking_fields, $v );

						do_action( 'bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'before insert appointment', 'bookingpress pro', $appointment_booking_fields, $bookingpress_debug_payment_log_id );

						$inserted_booking_id = $BookingPress->bookingpress_insert_appointment_logs( $appointment_booking_fields );
						array_push($bookingpress_inserted_appointment_ids, $inserted_booking_id);

						//Update appointment id in appointment_meta table
						$wpdb->update( $tbl_bookingpress_appointment_meta, array('bookingpress_appointment_id' => $inserted_booking_id), array('bookingpress_entry_id' => $v['bookingpress_entry_id']) );
						
					}

					if( 0 < $prevented_entry_data && $total_entry_data == $prevented_entry_data ){
						status_header( 409, 'Cart Entry Prevented due to double booking data' );
						do_action('bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'prevent duplicate appointment', 'bookingpress', $entry_data, $bookingpress_debug_payment_log_id);
						die;
					}
					// Update coupon usage counter if coupon code use
					if ( ! empty( $bookingpress_coupon_details ) ) {
						$bookingpress_coupon_data = json_decode( $bookingpress_coupon_details, true );
						if ( ! empty( $bookingpress_coupon_data ) && is_array( $bookingpress_coupon_data ) ) {
							$coupon_id = !empty($bookingpress_coupon_data['coupon_data']['bookingpress_coupon_id']) ? $bookingpress_coupon_data['coupon_data']['bookingpress_coupon_id'] :'';
							$coupon_id =( $coupon_id == '' && !empty($bookingpress_coupon_data['bookingpress_coupon_id'])) ? $bookingpress_coupon_data['bookingpress_coupon_id'] : $coupon_id;
							if($coupon_id != '') {
								$bookingpress_coupons->bookingpress_update_coupon_usage_counter( $coupon_id );
							}
						}
					}

					if ( ! empty( $bookingpress_inserted_appointment_ids ) ) {
						$entry_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_order_id = %d", $entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm

						$bookingpress_cart_version = get_option( 'bookingpress_cart_module' );
												
						$bookingpress_multiple_appointment_payment_order_detail = apply_filters( 'bookingpress_multiple_appointment_payment_order_detail',false, $entry_details);

						if(($bookingpress_multiple_appointment_payment_order_detail) || (file_exists(WP_PLUGIN_DIR . '/bookingpress-cart/bookingpress-cart.php') && !empty($bookingpress_cart_version) && version_compare($bookingpress_cart_version,'1.6','>'))){
							$entry_total_data = $wpdb->get_row($wpdb->prepare("SELECT SUM(bookingpress_deposit_amount) as total_deposit, bookingpress_paid_amount as total_paid, SUM(bookingpress_due_amount) as total_due, bookingpress_total_amount as total_amt FROM {$tbl_bookingpress_entries} WHERE bookingpress_order_id = %d", $entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is table name.

						} else {
							$entry_total_data = $wpdb->get_row($wpdb->prepare("SELECT SUM(bookingpress_deposit_amount) as total_deposit, SUM(bookingpress_paid_amount) as total_paid, SUM(bookingpress_due_amount) as total_due, SUM(bookingpress_total_amount) as total_amt FROM {$tbl_bookingpress_entries} WHERE bookingpress_order_id = %d", $entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is table name.							

						}									

						$bookingpress_deposit_amount = !empty($entry_total_data['total_deposit']) ? $entry_total_data['total_deposit'] : 0;
						$bookingpress_paid_amount = !empty($entry_total_data['total_paid']) ? $entry_total_data['total_paid'] : 0;
						$bookingpress_due_amount = !empty($entry_total_data['total_due']) ? $entry_total_data['total_due'] : 0;
						$bookingpress_total_amount = !empty($entry_total_data['total_amt']) ? $entry_total_data['total_amt'] : 0;

						$payer_email = ! empty( $payment_gateway_data['payer_email'] ) ? $payment_gateway_data['payer_email'] : $bookingpress_customer_email;

						//$bookingpress_last_invoice_id = $BookingPress->bookingpress_get_settings( 'bookingpress_last_invoice_id', 'invoice_setting' );
						global $tbl_bookingpress_settings;
						$bookingpress_last_invoice_id = $wpdb->get_var( $wpdb->prepare("SELECT setting_value FROM $tbl_bookingpress_settings WHERE setting_name = %s AND setting_type = %s", 'bookingpress_last_invoice_id', 'invoice_setting' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_settings is a table name. false alarm

						$bookingpress_last_invoice_id++;
						$BookingPress->bookingpress_update_settings( 'bookingpress_last_invoice_id', 'invoice_setting', $bookingpress_last_invoice_id );

						$bookingpress_last_invoice_id = apply_filters('bookingpress_modify_invoice_id_externally', $bookingpress_last_invoice_id);

						if($entry_details['bookingpress_payment_gateway'] == "on-site"){
							$payment_status =  2;
						}

						$payment_log_data = array(
							'bookingpress_order_id'                => $entry_id,
							'bookingpress_is_cart'                 => 1,
							'bookingpress_invoice_id'              => $bookingpress_last_invoice_id,
							'bookingpress_appointment_booking_ref' => 0,
							'bookingpress_customer_id'             => $bookingpress_customer_id,
							'bookingpress_customer_name'           => $entry_details['bookingpress_customer_name'],
							'bookingpress_customer_firstname'      => $entry_details['bookingpress_customer_firstname'],
							'bookingpress_customer_lastname'       => $entry_details['bookingpress_customer_lastname'],
							'bookingpress_customer_phone'          => $entry_details['bookingpress_customer_phone'],
							'bookingpress_customer_country'        => $entry_details['bookingpress_customer_country'],
							'bookingpress_customer_phone_dial_code' => $entry_details['bookingpress_customer_phone_dial_code'],
							'bookingpress_customer_email'          => $entry_details['bookingpress_customer_email'],
							'bookingpress_payment_currency'        => $entry_details['bookingpress_service_currency'],
							'bookingpress_payment_gateway'         => $entry_details['bookingpress_payment_gateway'],
							'bookingpress_payer_email'             => $payer_email,
							'bookingpress_transaction_id'          => $transaction_id,
							'bookingpress_payment_date_time'       => current_time( 'mysql' ),
							'bookingpress_payment_status'          => $payment_status,
							'bookingpress_payment_amount'          => $bookingpress_paid_amount,
							'bookingpress_payment_currency'        => $entry_details['bookingpress_service_currency'],
							'bookingpress_coupon_details'          => $entry_details['bookingpress_coupon_details'],
							'bookingpress_coupon_discount_amount'  => $entry_details['bookingpress_coupon_discount_amount'],
							'bookingpress_tax_percentage'          => $entry_details['bookingpress_tax_percentage'],
							'bookingpress_tax_amount'              => $entry_details['bookingpress_tax_amount'],
							'bookingpress_price_display_setting'     => $bookingpress_price_display_setting,
							'bookingpress_display_tax_order_summary' => $bookingpress_display_tax_order_summary,
							'bookingpress_included_tax_label'        => $bookingpress_included_tax_label,
							'bookingpress_deposit_amount'          => $bookingpress_deposit_amount,
							'bookingpress_paid_amount'             => $bookingpress_paid_amount,
							'bookingpress_due_amount'              => $bookingpress_due_amount,
							'bookingpress_total_amount'            => $bookingpress_total_amount,
							'bookingpress_created_at'              => current_time( 'mysql' ),
						);

						/* Condition add if payment done with deposit then payment status consider as '4' */
						//----------------------------------------------
						$bookingpress_deposit_payment_details = !empty( $entry_details['bookingpress_deposit_payment_details'] ) ? json_decode($entry_details['bookingpress_deposit_payment_details'], TRUE) : array() ;
						if(!empty($bookingpress_deposit_payment_details) && 0 < $bookingpress_due_amount ){
							$payment_log_data['bookingpress_payment_status'] = 4;
							$payment_log_data['bookingpress_mark_as_paid'] = 0;
						}
						//----------------------------------------------

						$payment_log_data = apply_filters( 'bookingpress_modify_payment_log_fields_before_insert', $payment_log_data, $v );

						do_action( 'bookingpress_payment_log_entry', $bookingpress_payment_gateway, 'before insert payment', 'bookingpress pro', $payment_log_data, $bookingpress_debug_payment_log_id );

						$payment_log_id = $BookingPress->bookingpress_insert_payment_logs( $payment_log_data );
						if(!empty($payment_log_id)){
							foreach($bookingpress_inserted_appointment_ids as $k2 => $v2){
								$wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_payment_id' => $payment_log_id), array('bookingpress_appointment_booking_id' => $v2));
								$wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_booking_id' => $bookingpress_last_invoice_id), array('bookingpress_appointment_booking_id' => $v2));
							}
						}

						$bookingpress_email_notification_type = '';
						if ( $bookingpress_appointment_status == '2' ) {
							$bookingpress_email_notification_type = 'Appointment Pending';
						} elseif ( $bookingpress_appointment_status == '1' ) {
							$bookingpress_email_notification_type = 'Appointment Approved';
						} elseif ( $bookingpress_appointment_status == '3' ) {
							$bookingpress_email_notification_type = 'Appointment Canceled';
						} elseif ( $bookingpress_appointment_status == '4' ) {
							$bookingpress_email_notification_type = 'Appointment Rejected';
						}
						$bookingpress_email_notification_type = apply_filters('bookingpress_modify_send_email_notification_type',$bookingpress_email_notification_type,$bookingpress_appointment_status);
						$bookingpress_send_only_first_appointment_notification = "";
						foreach($bookingpress_inserted_appointment_ids as $k2 => $v2){
							do_action( 'bookingpress_after_book_appointment', $v2, $entry_id, $payment_gateway_data );
							if(empty($bookingpress_send_only_first_appointment_notification)){
								$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( $bookingpress_email_notification_type, $v2, $bookingpress_customer_email );
							}							
							$bookingpress_send_only_first_appointment_notification = apply_filters('bookingpress_send_only_first_appointment_notification','',$v2,$payment_log_data);
						}
						if($bookingpress_is_customer_create == 1 && !empty($bookingpress_customer_id)) {
							do_action( 'bookingpress_after_create_new_customer',$bookingpress_customer_id);
						}
						if($bookingpress_is_customer_create == 0 && !empty($bookingpress_customer_id)) {
							do_action( 'bookingpress_after_update_customer_data',$bookingpress_customer_id);
						} 
						do_action('bookingpress_after_add_group_appointment',$entry_id);
					}
				}
			}

			return 0;
		}

		function bookingpress_pre_booking_verify_details_callback($return_data = false){
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			$response              = array();

			if ( !$bpa_verify_nonce_flag) {
				$response                     = array();
				$response['variant']          = 'error';
				$response['title']            = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']              = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				$response['appointment_data'] = array();
				if($return_data){
					return $response;
				}
				echo wp_json_encode( $response );
				die();
			}

			
			if( !empty( $_POST['booking_data'] ) && !is_array( $_POST['booking_data']) ){
				$_POST['booking_data'] = !empty( $_POST['booking_data'] ) ? json_decode( stripslashes_deep( $_POST['booking_data'] ), true ) : array(); //phpcs:ignore
				$_POST['booking_data'] =  !empty($_POST['booking_data']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['booking_data'] ) : array(); // phpcs:ignore
			}

			global $BookingPress;
			
			$bookingpress_appointment_details = !empty( $_POST['booking_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['booking_data'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function

			$booking_token = !empty( $_POST['booking_token'] ) ? sanitize_text_field( $_POST['booking_token'] ) : '';
			$booking_time = current_time('timestamp');
			if( empty( $booking_token ) || $booking_token != $bookingpress_appointment_details['bookingpress_uniq_id'] ){
				$response                     = array();
				$response['variant']          = 'error';
				$response['title']            = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']              = esc_html__( 'Sorry, Your request can not be processed.', 'bookingpress-appointment-booking' );
				$response['appointment_data'] = array();
				if($return_data){
					return $response;
				}				
				echo wp_json_encode( $response );				
				die();
			}

			$final_payable_amount = $bookingpress_appointment_details['total_payable_amount'];
			
			$bookingpress_payable_amount_key = 'bookingpress_verify_payment_token_' . $booking_token . '_' . $booking_time;
			
			$bookingpress_payable_amount_key = wp_hash( $bookingpress_payable_amount_key );

			$token_expiration = HOUR_IN_SECONDS * 24;

			set_transient( $bookingpress_payable_amount_key, $final_payable_amount, $token_expiration );

			$response = array(
					'verification_token' => $bookingpress_payable_amount_key,
					'verification_time' => $booking_time
			);
			if($return_data){
				return $response;
			}			
			echo wp_json_encode($response);
			die;

		}

		function bookingpress_recalculate_appointment_data_func( $bookingpress_appointment_data = array() ) {
			global $wpdb, $tbl_bookingpress_coupons, $BookingPress, $bookingpress_coupons, $tbl_bookingpress_services, $bookingpress_deposit_payment, $bookingpress_services, $tbl_bookingpress_staffmembers_services, $bookingpress_pro_staff_members, $tbl_bookingpress_extra_services,$tbl_bookingpress_form_fields, $bookingpress_global_options;
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			$response              = array();
			

			if ( empty($bookingpress_appointment_data) && !$bpa_verify_nonce_flag) {
				$response                     = array();
				$response['variant']          = 'error';
				$response['title']            = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']              = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				$response['appointment_data'] = array();
				echo wp_json_encode( $response );
				die();
			}
			if( !empty( $_POST['appointment_details'] ) && !is_array( $_POST['appointment_details'] ) ){
				$_POST['appointment_details'] = !empty( $_POST['appointment_details'] ) ? json_decode( stripslashes_deep( $_POST['appointment_details'] ), true ) : array(); //phpcs:ignore
				$_POST['appointment_details'] =  !empty($_POST['appointment_details']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['appointment_details'] ) : array(); // phpcs:ignore
			}
			$bookingpress_appointment_details = !empty( $_POST['appointment_details'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_details'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
			if( empty($bookingpress_appointment_details) && !empty($bookingpress_appointment_data) ){
				$bookingpress_appointment_details = array_map( array( $BookingPress, 'appointment_sanatize_field' ), $bookingpress_appointment_data );
			}
			
			$response            = array();
			$response['variant'] = 'success';
			$response['title']   = __( 'Success', 'bookingpress-appointment-booking' );
			$response['msg']     = __( 'Data re-calculated successfully...', 'bookingpress-appointment-booking' );

			if ( ! empty( $bookingpress_appointment_details ) && empty($bookingpress_appointment_details['cart_items']) ) {
				$payment_gateway = !empty($bookingpress_appointment_details['selected_payment_method']) ? $bookingpress_appointment_details['selected_payment_method'] : '';
				$total_payable_amount = $final_payable_amount = ! empty( $bookingpress_appointment_details['service_price_without_currency'] ) ? floatval( $bookingpress_appointment_details['service_price_without_currency'] ) : 0;
				$coupon_code          = ! empty( $bookingpress_appointment_details['coupon_code'] ) ? sanitize_text_field( $bookingpress_appointment_details['coupon_code'] ) : '';
				$selected_service     = ! empty( $bookingpress_appointment_details['selected_service'] ) ? intval( $bookingpress_appointment_details['selected_service'] ) : 0;
				
				if ( ! empty( $selected_service ) ) {
					// Get service data
					$services_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $selected_service ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm

					$total_payable_amount = $final_payable_amount = ! empty( $services_data['bookingpress_service_price'] ) ? $services_data['bookingpress_service_price'] : 0;
				}

				

				//If staff member selected then use that staff member price
				$bookingpress_selected_staffmember = !empty($bookingpress_appointment_details['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? intval($bookingpress_appointment_details['bookingpress_selected_staff_member_details']['selected_staff_member_id']) : 0;
				if(!empty($bookingpress_selected_staffmember) && $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ){
					$bookingpress_staffmember_assigned_service_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $bookingpress_selected_staffmember, $selected_service ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm

					if ( ! empty( $bookingpress_staffmember_assigned_service_details ) && isset($bookingpress_staffmember_assigned_service_details['bookingpress_service_price']) ) {
						$total_payable_amount = $final_payable_amount = floatval( $bookingpress_staffmember_assigned_service_details['bookingpress_service_price'] );
					}
				}
				$total_payable_amount = $final_payable_amount = apply_filters( 'bookingpress_modify_recalculate_amount_before_calculation', $final_payable_amount, $bookingpress_appointment_details );
				$bookingpress_appointment_details['bookingpress_custom_service_duration_price'] = $total_payable_amount;
				// -------------------------------------------------------------------------------------------------------------

				// Calculate Bring anyone with you module price
				$bookingpress_bring_anyone_module_price_arr = array();
				$bookingpress_selected_members              = ! empty( $bookingpress_appointment_details['bookingpress_selected_bring_members'] ) ? intval( $bookingpress_appointment_details['bookingpress_selected_bring_members'] ) - 1 : 0;

				if( !empty($bookingpress_selected_members )) {
					$bookingpress_selected_members = $bookingpress_selected_members + 1;
					$total_payable_amount = $final_payable_amount = $bookingpress_bring_anyone_with_you_price = $final_payable_amount * $bookingpress_selected_members;
					array_push( $bookingpress_bring_anyone_module_price_arr, $bookingpress_bring_anyone_with_you_price );
					$bookingpress_appointment_details['bookingpress_selected_bring_members'] = $bookingpress_selected_members;
				} else {
					$bookingpress_appointment_details['bookingpress_selected_bring_members'] = 1;
				}

				// -------------------------------------------------------------------------------------------------------------

				// Calculate selected extra service prices
				// -------------------------------------------------------------------------------------------------------------
				$bookingpress_extra_service_price_arr = array();
				$bookingpress_extra_service_details = !empty($bookingpress_appointment_details['bookingpress_selected_extra_details']) ? array_map( array( $BookingPress, 'appointment_sanatize_field'), $bookingpress_appointment_details['bookingpress_selected_extra_details'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
				if( is_array($bookingpress_extra_service_details) && !empty($bookingpress_extra_service_details) ){
					foreach($bookingpress_extra_service_details as $k => $v){
						if($v['bookingpress_is_selected'] == "true"){
							$bookingpress_extra_service_id = intval($k);
							$bookingpress_extra_service_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_extra_services_id = %d", $bookingpress_extra_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is a table name. false alarm

							if(!empty($bookingpress_extra_service_details)){
								$bookingpress_extra_service_price = ! empty( $bookingpress_extra_service_details['bookingpress_extra_service_price'] ) ? floatval( $bookingpress_extra_service_details['bookingpress_extra_service_price'] ) : 0;

								$bookingpress_selected_qty = !empty($v['bookingpress_selected_qty']) ? intval($v['bookingpress_selected_qty']) : 1;

								if(!empty($bookingpress_selected_qty)){
									$bookingpress_final_price = $bookingpress_extra_service_price * $bookingpress_selected_qty;

									array_push($bookingpress_extra_service_price_arr, $bookingpress_final_price);
								}
							}
						}
					}
				}


				// -------------------------------------------------------------------------------------------------------------
				// Add extra service price to final price
				if ( ! empty( $bookingpress_extra_service_price_arr ) && is_array( $bookingpress_extra_service_price_arr ) ) {
					foreach ( $bookingpress_extra_service_price_arr as $k => $v ) {
						$total_payable_amount = $final_payable_amount = $final_payable_amount + $v;
					}
				}

				// -------------------------------------------------------------------------------------------------------------

				//If deposit payment module enabled then calculate deposit amount
				$bookingpress_deposit_amt = $bookingpress_deposit_due_amt = 0;
				if(!empty($payment_gateway) && $payment_gateway != "on-site" && $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation() && !empty($bookingpress_appointment_details['bookingpress_deposit_payment_method']) && ($bookingpress_appointment_details['bookingpress_deposit_payment_method'] == "deposit_or_full_price") && empty($bookingpress_appointment_details['cart_items']) ){
					$bookingpress_deposit_payment_method = $BookingPress->bookingpress_get_settings( 'bookingpress_allow_customer_to_pay', 'payment_setting' );
					$bookingpress_deposit_type = $bookingpress_services->bookingpress_get_service_meta($selected_service, 'deposit_type');
					$bookingpress_deposit_amount = $bookingpress_services->bookingpress_get_service_meta($selected_service, 'deposit_amount');

					if($bookingpress_deposit_type == "percentage"){
						$bookingpress_deposit_amt = $total_payable_amount * ($bookingpress_deposit_amount / 100);
						$bookingpress_deposit_due_amt = $total_payable_amount - $bookingpress_deposit_amt;
						
						$bookingpress_appointment_details['deposit_payment_type'] = 'percentage';
						$bookingpress_appointment_details['deposit_payment_amount_percentage'] = $bookingpress_deposit_amount;
					}else{
						$bookingpress_deposit_amt = $bookingpress_deposit_amount;
						$bookingpress_deposit_due_amt = $total_payable_amount - $bookingpress_deposit_amt;
						$bookingpress_appointment_details['deposit_payment_type'] = 'fixed';
						$bookingpress_appointment_details['deposit_payment_amount'] = $bookingpress_deposit_amount;
					}

					$bookingpress_appointment_details['bookingpress_deposit_amt'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_deposit_amt);
					$bookingpress_appointment_details['bookingpress_deposit_amt_without_currency'] = $bookingpress_deposit_amt;
					$bookingpress_appointment_details['bookingpress_deposit_due_amt'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_deposit_due_amt);
					$bookingpress_appointment_details['bookingpress_deposit_due_amt_without_currency'] = $bookingpress_deposit_due_amt;
					$total_payable_amount = $bookingpress_deposit_amt;
				}

				$bookingpress_appointment_details['selected_service_price'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($final_payable_amount);


				$bookingpress_appointment_details['service_price_without_currency'] = floatval( $final_payable_amount );

				$bookingpress_appointment_details = apply_filters( 'bookingpress_modify_recalculate_appointment_details', $bookingpress_appointment_details, $final_payable_amount );
				$final_payable_amount = apply_filters( 'bookingpress_modify_recalculate_amount', $final_payable_amount, $bookingpress_appointment_details );

				if( "" === $bookingpress_deposit_amt ){
					$total_payable_amount = $final_payable_amount;
				}

				if ( $bookingpress_coupons->bookingpress_check_coupon_module_activation() && ! empty( $coupon_code ) ) {
					$bookingpress_applied_coupon_response = $bookingpress_coupons->bookingpress_apply_coupon_code( $coupon_code, $selected_service );
					if ( is_array( $bookingpress_applied_coupon_response ) && ! empty( $bookingpress_applied_coupon_response['coupon_status'] ) && ( $bookingpress_applied_coupon_response['coupon_status'] == 'success' ) ) {
						$coupon_data = ! empty( $bookingpress_applied_coupon_response['coupon_data'] ) ? $bookingpress_applied_coupon_response['coupon_data'] : array();

						$bookingpress_after_discount_amounts = $bookingpress_coupons->bookingpress_calculate_bookingpress_coupon_amount( $coupon_code, $final_payable_amount );
						if ( is_array( $bookingpress_after_discount_amounts ) && ! empty( $bookingpress_after_discount_amounts ) ) {
							$total_payable_amount = $final_payable_amount = ! empty( $bookingpress_after_discount_amounts['final_payable_amount'] ) ? floatval( $bookingpress_after_discount_amounts['final_payable_amount'] ) : 0;
							if(!empty($bookingpress_deposit_amt)){
								$bookingpress_deposit_due_amt = $final_payable_amount = $final_payable_amount - $bookingpress_deposit_amt;
								$bookingpress_appointment_details['bookingpress_deposit_due_amt'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($final_payable_amount);
								$bookingpress_appointment_details['bookingpress_deposit_due_amt_without_currency'] = $final_payable_amount;
							}
							$discounted_amount = ! empty( $bookingpress_after_discount_amounts['discounted_amount'] ) ? floatval( $bookingpress_after_discount_amounts['discounted_amount'] ) : 0;
							$bookingpress_appointment_details['coupon_discount_amount'] = $discounted_amount;
							$bookingpress_appointment_details['coupon_discount_amount_with_currecny'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($discounted_amount);
						}
					}
					$bookingpress_appointment_details['applied_coupon_res'] = $bookingpress_applied_coupon_response;
				}

				$bookingpress_service_price = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $total_payable_amount );
				$bookingpress_appointment_details['total_payable_amount_with_currency'] = $bookingpress_service_price;
				$bookingpress_appointment_details['total_payable_amount'] = $total_payable_amount;
			}

			$bookingpress_appointment_details['bpa_final_payable_amount'] = $final_payable_amount;

			$bookingpress_appointment_details = apply_filters('bookingpress_modify_calculated_appointment_details', $bookingpress_appointment_details);

			

			//Format frontend timings
			if(!empty($bookingpress_appointment_details['cart_items'])){
				$bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_time_format = $bookingpress_global_data['wp_default_time_format'];
				
				foreach( $bookingpress_appointment_details['cart_items'] as $cart_items_key => $cart_items_val ){
					$bookingpress_selected_start_time = $cart_items_val['bookingpress_selected_start_time'];
					$bookingpress_selected_end_time = $cart_items_val['bookingpress_selected_end_time'];
					$bookingpress_appointment_details['cart_items'][$cart_items_key]['formatted_start_time'] = date($bookingpress_time_format, strtotime($bookingpress_selected_start_time));
					$bookingpress_appointment_details['cart_items'][$cart_items_key]['formatted_end_time'] = date($bookingpress_time_format, strtotime($bookingpress_selected_end_time));
				}
			}
			
			/** redefine checkbox fields */
			$bookingpress_all_checkbox_fields = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_type = %s AND bookingpress_is_customer_field = %d", 'checkbox', 0 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
			
			if( !empty( $bookingpress_all_checkbox_fields ) ){
				foreach( $bookingpress_all_checkbox_fields as $bpa_checkbox_field_data ){
					$bpa_checkbox_meta_key = $bpa_checkbox_field_data->bookingpress_field_meta_key;
					if( empty( $bookingpress_appointment_details['form_fields'][ $bpa_checkbox_meta_key ] ) ) {
						$bookingpress_appointment_details['form_fields'][ $bpa_checkbox_meta_key ] = array();
					}
				}
			}

			$response['appointment_data'] = $bookingpress_appointment_details;

			if(!empty($bookingpress_appointment_data)){
				return wp_json_encode($response);
			}else{
				echo wp_json_encode( $response );
			}
			exit;
		}


		function bookingpress_get_final_service_amount( $bookingpress_payble_amount, $coupon_discount_amount = 0, $coupon_tax_amount = 0 ) {
			if ( ! empty( $bookingpress_payble_amount ) ) {
				if ( ! empty( $coupon_tax_amount ) ) {
					$bookingpress_payble_amount = $bookingpress_payble_amount - $coupon_tax_amount;
				}
				if ( $coupon_discount_amount ) {
					$bookingpress_payble_amount = $bookingpress_payble_amount + $coupon_discount_amount;
				}
			}
			return $bookingpress_payble_amount;
		}

		function bookingpress_insert_customer_field_data( $bookingpress_customer_id, $appointment_field_data ) {
			global $BookingPress,$tbl_bookingpress_form_fields,$wpdb;
			$bookingpress_form_fields = $wpdb->get_results( $wpdb->prepare('SELECT * FROM ' . $tbl_bookingpress_form_fields . ' WHERE bookingpress_is_customer_field = %d ', 0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm          
			$bookingpress_field_list  = array();

			foreach ( $bookingpress_form_fields as $bookingpress_form_field_key => $bookingpress_form_field_val ) {
				$bookingpress_field_options = ! empty( $bookingpress_form_field_val['bookingpress_field_options'] ) ? json_decode( $bookingpress_form_field_val['bookingpress_field_options'], true ) : '';
				$bookingpress_default_field = array( 'customer_firstname', 'customer_lastname', 'customer_phone', 'customer_phone_country', 'appointment_note' );

				$bookingpress_update_customer_meta = ( ( ! empty( $bookingpress_field_options['used_for_user_information'] ) && $bookingpress_field_options['used_for_user_information'] == 'true' ) || ( ! empty( $bookingpress_field_options['is_customer_field'] ) && $bookingpress_field_options['is_customer_field'] == 'true' ) );

				if ( $bookingpress_update_customer_meta && ( $bookingpress_form_field_val['bookingpress_field_is_default'] != '1' || $bookingpress_form_field_val['bookingpress_field_is_default'] == '1' && $bookingpress_form_field_val['bookingpress_form_field_name'] == 'fullname' ) ) {

					if ( $bookingpress_form_field_val['bookingpress_field_is_default'] == '1' && $bookingpress_form_field_val['bookingpress_form_field_name'] == 'fullname' ) {
						$bookingpress_field_list[] = 'customer_fullname';
					} else {						
						$bookingpress_field_list[] = $bookingpress_form_field_val['bookingpress_field_meta_key'];
					}
				}
			}

			if ( ! empty( $appointment_field_data ) && ! empty( $bookingpress_customer_id ) ) {
				foreach ( $appointment_field_data as $key => $value ) {
					if ( in_array( $key, $bookingpress_field_list ) ) {
						$field_update[] = $key;
						$BookingPress->update_bookingpress_customersmeta( $bookingpress_customer_id, $key, $value );
					}
				}
			}
		}

		function bookingpress_apply_for_refund($response,$bookingpress_refund_data ,$refund_intiate_from = 0) {			

			global $BookingPress,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$wpdb,$bookingpress_pro_appointment;
			$bookingpress_payment_id = !empty($bookingpress_refund_data['payment_id']) ? $bookingpress_refund_data['payment_id'] : 0;
			$bookingpress_appointment_id = !empty($bookingpress_refund_data['appointment_id']) ? $bookingpress_refund_data['appointment_id'] : 0;

			if(!empty($bookingpress_appointment_id)) {
				$appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm			
				$bookingpress_allow_to_refund = $bookingpress_pro_appointment->bookingpress_allow_to_refund($appointment_data,0,0);
			}
			if(isset($bookingpress_allow_to_refund['allow_refund']) &&  $bookingpress_allow_to_refund['allow_refund'] > 0) {
				if(!empty($bookingpress_payment_id)) {
					/* get the payment log data */
					$bookingpress_appointment_payment_logs_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = %d", $bookingpress_payment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
					$payment_gateway = !empty($bookingpress_appointment_payment_logs_data['bookingpress_payment_gateway']) ? $bookingpress_appointment_payment_logs_data['bookingpress_payment_gateway'] : '';
				}
				$bookingpress_refund_data = array_merge($bookingpress_refund_data,$bookingpress_appointment_payment_logs_data);
				$bookingpress_send_refund_data = apply_filters('bookingpress_modify_refund_data_before_refund',$bookingpress_refund_data);

				if(!empty($payment_gateway)) {
					$response = apply_filters('bookingpress_'.$payment_gateway.'_apply_refund',$response,$bookingpress_send_refund_data);	
					if(!empty($response['variant']) && $response['variant'] == 'success' ) {
						$response['msg']   = esc_html__( 'Refund successfully initiated', 'bookingpress-appointment-booking' );						
						$this->bookingpress_after_refund_success($response,$bookingpress_refund_data,$appointment_data,$refund_intiate_from );
					}
				}
			}

			return $response;
		}

		function bookingpress_after_refund_success($response,$bookingpress_refund_data,$appointment_data,$refund_intiate_from ) {
			global $tbl_bookingpress_appointment_bookings,$wpdb,$BookingPress,$bookingpress_email_notifications,$tbl_bookingpress_payment_logs;

			if(!empty($bookingpress_refund_data) && !empty($appointment_data)) {				

				$bookingpress_appointment_date = !empty($appointment_data['bookingpress_appointment_date']) ? $appointment_data['bookingpress_appointment_date']:'';
				$bookingpress_appointment_time = !empty($appointment_data['bookingpress_appointment_time']) ? $appointment_data['bookingpress_appointment_time']:'';
				$bookingpress_customer_email = !empty($appointment_data['bookingpress_customer_email']) ? $appointment_data['bookingpress_customer_email']:'';
				$bookingpress_appointment_id = !empty($appointment_data['bookingpress_appointment_booking_id']) ? intval($appointment_data['bookingpress_appointment_booking_id']) :'';
				$bookingpress_payment_log_id = !empty($bookingpress_refund_data['bookingpress_payment_log_id']) ? intval($bookingpress_refund_data['bookingpress_payment_log_id']):'';
				$bookingpress_deposit_amount = !empty($bookingpress_refund_data['bookingpress_deposit_amount']) ? intval($bookingpress_refund_data['bookingpress_deposit_amount']):'';
				$bookingpress_refund_reason = !empty($bookingpress_refund_data['refund_reason']) ? sanitize_text_field( $bookingpress_refund_data['refund_reason']):'';
				$refund_appointment_status = !empty($bookingpress_refund_data['refund_appointment_status']) ? sanitize_text_field( $bookingpress_refund_data['refund_appointment_status']): '';

				$bookingpress_from_time = current_time('timestamp');
				$bookingpress_to_time = strtotime($bookingpress_appointment_date .' '. $bookingpress_appointment_time);				
				
				/* change the payment data */
				$bookingpress_payment_status = $bookingpress_deposit_amount > 0 ? 5 : 3;				
				$bookingpres_refund_type = isset($bookingpress_refund_data['refund_type']) ? $bookingpress_refund_data['refund_type'] : '';
		                if($bookingpres_refund_type != 'full') {                    
		                    $bookingpress_refund_amount = $bookingpress_refund_data['refund_amount'] ? $bookingpress_refund_data['refund_amount'] : 0;
		                } else {
					$bookingpress_refund_amount = $bookingpress_refund_data['bookingpress_paid_amount'] ? $bookingpress_refund_data['bookingpress_paid_amount'] : 0;			
					$bookingpress_refund_amount = apply_filters('bookingpress_modify_refund_data_amount', $bookingpress_refund_amount, $bookingpress_payment_log_id);
				}
				$bookingpress_refund_response = $response['bookingpress_refund_response'] ? maybe_serialize( $response['bookingpress_refund_response'] ) : '';
				$wpdb->update($tbl_bookingpress_payment_logs, array('bookingpress_payment_status' => $bookingpress_payment_status,'bookingpress_refund_reason' => $bookingpress_refund_reason,'bookingpress_refund_initiate_from' => $refund_intiate_from,'bookingpress_refund_amount' => $bookingpress_refund_amount,'bookingpress_refund_type' => $bookingpres_refund_type,'bookingpress_refund_response' => $bookingpress_refund_response), array('bookingpress_payment_log_id' => $bookingpress_payment_log_id));			
				
				/* send the refund email notification */

				if(!empty($bookingpress_customer_email) && !empty($bookingpress_appointment_id)) {
					$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( 'Refund Payment', $bookingpress_appointment_id, $bookingpress_customer_email );
					do_action('bookingpress_after_refund_appointment',$bookingpress_appointment_id);
				}				

				if($refund_intiate_from == 0) {

					if(empty($refund_appointment_status)) {
						if($bookingpress_to_time > $bookingpress_from_time ) {
							$bookingpress_ap_status = '3';
						} else {
							$bookingpress_ap_status = '5';
						}
					} else {
						$bookingpress_ap_status = $refund_appointment_status;
					}
					$bookingpress_email_notification_type = '';
					if ( $bookingpress_ap_status == '2' ) {
						$bookingpress_email_notification_type = 'Appointment Pending';
					} elseif ( $bookingpress_ap_status == '1' ) {
						$bookingpress_email_notification_type = 'Appointment Approved';
					} elseif ( $bookingpress_ap_status == '3' ) {
						$bookingpress_email_notification_type = 'Appointment Canceled';
					} elseif ( $bookingpress_ap_status == '4' ) {
						$bookingpress_email_notification_type = 'Appointment Rejected';
					}
									
					/* change the appointment status */
					if(!empty($bookingpress_ap_status) && !empty($bookingpress_appointment_id)) {
						$wpdb->update($tbl_bookingpress_appointment_bookings, array('bookingpress_appointment_status' => $bookingpress_ap_status), array('bookingpress_appointment_booking_id' => $bookingpress_appointment_id) );	
						do_action('bookingpress_after_change_appointment_status', $bookingpress_appointment_id, $bookingpress_ap_status);
					}

					/* send the email notification on change appointment status */
					if(!empty($bookingpress_email_notification_type) && !empty($bookingpress_customer_email) && !empty($bookingpress_appointment_id)) {
						$bookingpress_email_notifications->bookingpress_send_after_payment_log_entry_email_notification( $bookingpress_email_notification_type, $bookingpress_appointment_id, $bookingpress_customer_email );
					}
				}	
			}
		}
	}
}

global $bookingpress_pro_payment_gateways;
$bookingpress_pro_payment_gateways = new bookingpress_pro_payment_gateways();
