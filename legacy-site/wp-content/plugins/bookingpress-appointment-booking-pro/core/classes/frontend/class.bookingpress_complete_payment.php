<?php

if ( ! class_exists( 'bookingpress_pro_complete_payment' ) ) {
	class bookingpress_pro_complete_payment Extends BookingPress_Core {
        function __construct() {
            add_shortcode('bookingpress_complete_payment', array( $this, 'bookingpress_complete_payment_form' ));

            //Dynamic Data Fields
            add_filter('bookingpress_complete_payment_dynamic_data_fields', array($this, 'bookingpress_complete_payment_dynamic_data_fields_func'), 10, 1);

            add_action('wp_ajax_bookingpress_final_complete_payment', array($this, 'bookingpress_complete_payment_func'));
            add_action('wp_ajax_nopriv_bookingpress_final_complete_payment', array($this, 'bookingpress_complete_payment_func'));
        }
        

		/**
		 * bpa function for get complete payment data
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_complete_payment_request_func($user_detail=array()){			
			global $BookingPress,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings, $bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$bookingpress_email_notifications;			
			$result = array();
            $default_error_message   = esc_html__( 'No Data Found.', 'bookingpress-appointment-booking' );
			$response = array('status' => 0, 'message' => $default_error_message, 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

                $bookingpress_nonce = isset($user_detail['bookingpress_nonce']) ? sanitize_text_field($user_detail['bookingpress_nonce']) : '';
                $appointment_details = isset($user_detail['appointment_details']) ? $user_detail['appointment_details'] : '';

				if(!empty($bookingpress_nonce)){
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}else{
					$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}
				$_REQUEST['_wpnonce'] = $bookingpress_nonce;				
				if(!empty($appointment_details)){
					$_REQUEST['complete_payment_data'] = $appointment_details;
                    $_POST = $_REQUEST;                    
					$bookingpress_response = $this->bookingpress_complete_payment_func(true);
					$bookingpress_check_response = (isset($bookingpress_response['variant']))?$bookingpress_response['variant']:'';
					if($bookingpress_check_response == 'error'){					
						$message = (isset($bookingpress_response['msg']))?$bookingpress_response['msg']:'';
						$response = array('status' => 0, 'message' => $message, 'response' => array('result' => $result));					
					}else{
						$result = $bookingpress_response;
						$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));					
					}
				}
			}
			return $response;
		}

		/**
		 * bpa function for get complete payment data
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_get_complete_payment_data_func($user_detail=array()){			
			global $BookingPress,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings, $bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$bookingpress_email_notifications;			
			$result = array();
            $default_error_message   = esc_html__( 'No Data Found.', 'bookingpress-appointment-booking' );
			$response = array('status' => 0, 'message' => $default_error_message, 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){
				
				$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
				$_REQUEST['_wpnonce'] = $bookingpress_nonce;													
				$bookingpress_payment_token = isset($user_detail['bookingpress_payment_token']) ? $user_detail['bookingpress_payment_token'] : '';

				if(!empty($bookingpress_payment_token)){
					$_GET['bkp_pay'] = $bookingpress_payment_token;
                    $_POST = $_REQUEST;
                    $bookingpress_dynamic_data_fields = '';
					$bookingpress_response = $this->bookingpress_complete_payment_dynamic_data_fields_func($bookingpress_dynamic_data_fields,true);
					$bookingpress_check_response = (isset($bookingpress_response['variant']))?$bookingpress_response['variant']:'';
					if($bookingpress_check_response == 'error'){					
						$message = (isset($bookingpress_response['msg']))?$bookingpress_response['msg']:'';
						$response = array('status' => 0, 'message' => $message, 'response' => array('result' => $result));					
					}else{
						$result = $bookingpress_response;
						$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));					
					}
				}

			}
			return $response;
		}

        /**
         * Function for complete payment at frontend [bookingpress_complete_payment] shortcode
         *
         * @return void
         */
        function bookingpress_complete_payment_func($return_data = false){
            global $tbl_bookingpress_entries,$bookingpress_deposit_payment,$tbl_bookingpress_appointment_bookings, $wpdb, $BookingPress, $bookingpress_pro_payment, $tbl_bookingpress_payment_logs, $bookingpress_pro_payment_gateways;
            $response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
                if($return_data){
                    return $response;
                }
				echo wp_json_encode( $response );
				die();
			}

            $response['variant'] = '';
            $response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
            $response['msg'] = esc_html__('Something went wrong while completing the payment', 'bookingpress-appointment-booking');
            
            if( !empty( $_POST['complete_payment_data'] ) && !is_array( $_POST['complete_payment_data'] ) ){
                $_POST['complete_payment_data'] = json_decode( stripslashes_deep( $_POST['complete_payment_data'] ), true ); //phpcs:ignore
                $_POST['complete_payment_data'] =  !empty($_POST['complete_payment_data']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['complete_payment_data'] ) : array(); // phpcs:ignore		
            }

            $bookingpress_final_payment_data = !empty($_POST['complete_payment_data']) ? array_map( array( $BookingPress, 'appointment_sanatize_field'), $_POST['complete_payment_data'] ) : ''; //phpcs:ignore
            if(!empty($bookingpress_final_payment_data['appointment_id'])){
                $bookingpress_appointment_id = intval($bookingpress_final_payment_data['appointment_id']);
                $bookingpress_payment_id = intval($bookingpress_final_payment_data['payment_id']);

                if(!empty($bookingpress_payment_id)){
                    $bookingpress_payment_details = $bookingpress_pro_payment->bookingpress_calculate_payment_details($bookingpress_payment_id);
                    $bookingpress_service_name = "";
                    $bookingpress_is_cart = !empty($bookingpress_payment_details['is_cart']) ? 1 : 0;
                    if(!$bookingpress_is_cart){
                        $bookingpress_service_name = $bookingpress_payment_details['appointment_details'][0]['bookingpress_service_name'];
                    }
                    $bookingpress_final_payable_amount = !empty($bookingpress_payment_details['due_amount']) ? $bookingpress_payment_details['due_amount'] : 0;
                    if(empty($bookingpress_final_payable_amount) && !empty($bookingpress_payment_details['total_amount'])){
                        $bookingpress_final_payable_amount = $bookingpress_payment_details['total_amount'];
                    }

                    $bookingpress_applied_coupon_code = !empty($bookingpress_final_payment_data['coupon_code']) ? $bookingpress_final_payment_data['coupon_code'] : '';
                    $bookingpress_is_new_coupon_apply = 0;
                    if(!empty($bookingpress_applied_coupon_code)){
                        $bookingpress_is_new_coupon_apply = 1;
                        $bookingpress_final_payable_amount = $bookingpress_final_payment_data['total_payable_amount'];
                    }

                    $bookingpress_tip_amount = !empty($bookingpress_final_payment_data['tip_amount']) ? $bookingpress_final_payment_data['tip_amount'] : '';
                    if(!empty($bookingpress_tip_amount)){
                        $bookingpress_final_payable_amount = $bookingpress_final_payment_data['total_payable_amount'];
                    }

                    $bookingpress_applied_gift_code = (isset($bookingpress_final_payment_data['gift_card_code']) && !empty($bookingpress_final_payment_data['gift_card_code'])) ? $bookingpress_final_payment_data['gift_card_code'] : '';
                    if(!empty($bookingpress_applied_gift_code)){
                        $bookingpress_final_payable_amount = $bookingpress_final_payment_data['total_payable_amount'];
                    }
                    
                    $bookingpress_final_payable_amount = number_format($bookingpress_final_payable_amount, 2);
                    $bookingpress_final_payable_amount = str_replace(',', '', $bookingpress_final_payable_amount);
                    $bookingpress_final_payable_amount = floatval($bookingpress_final_payable_amount);

                    $payment_gateway = $bookingpress_final_payment_data['selected_payment_method'];

                    $bookingpress_notify_url   = BOOKINGPRESS_HOME_URL . '/?bookingpress-listener=bpa_pro_' . $payment_gateway . '_url';

                    $bookingpress_currency_name   = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
                    $bookingpress_currency_code = $BookingPress->bookingpress_get_currency_code( $bookingpress_currency_name );

                    $bookingpress_after_canceled_payment_page_id = $BookingPress->bookingpress_get_customize_settings( 'after_failed_payment_redirection', 'booking_form' );
				    $bookingpress_after_canceled_payment_url     = get_permalink( $bookingpress_after_canceled_payment_page_id );

                    $bpa_complete_payment_page_id = $BookingPress->bookingpress_get_settings('complete_payment_page_id', 'general_setting');
                    $bookingpress_complete_payment_page_url = get_permalink( $bpa_complete_payment_page_id );
                    $bookingpress_complete_payment_page_url = add_query_arg('bpa_complete_payment', 1, $bookingpress_complete_payment_page_url);

                    if(!empty($bookingpress_appointment_id)){

                        $bookingpress_entry_data = array();
                        $bookingpress_get_appointment_record = $wpdb->get_row($wpdb->prepare( "SELECT bookingpress_entry_id,bookingpress_service_name FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d",$bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm 

                        if(isset($bookingpress_final_payment_data['applied_coupon_res']) && !empty($bookingpress_final_payment_data['applied_coupon_res'])){
                            $bookingpress_applied_coupon_details = array(
                                'coupon_status' => $bookingpress_final_payment_data['applied_coupon_res']['coupon_status'],
                                'msg' => $bookingpress_final_payment_data['applied_coupon_res']['msg'],
                                'coupon_data' => $bookingpress_final_payment_data['applied_coupon_res']['coupon_data'],
                            );            
                            $bookingpress_entry_data['bookingpress_coupon_details'] = wp_json_encode($bookingpress_applied_coupon_details);
                            $bookingpress_entry_data['bookingpress_coupon_discount_amount'] = $bookingpress_final_payment_data['coupon_discount_amount'];    
                        }  
                        
                        $bookingpress_entry_data['bookingpress_paid_amount'] = $bookingpress_final_payable_amount;
                        $bookingpress_entry_data['bookingpress_total_amount'] = $bookingpress_final_payable_amount;                    
                        $bookingpress_entry_data['bookingpress_payment_gateway'] = $bookingpress_final_payment_data['selected_payment_method'];
                        
                        $bookingpress_entry_id = $bookingpress_get_appointment_record['bookingpress_entry_id'];                                                                        
                        $wpdb->update($tbl_bookingpress_entries, 
                            $bookingpress_entry_data, 
                            array('bookingpress_entry_id' => $bookingpress_entry_id)
                        );                       

                    }


                    $bookingpress_final_payment_request_data = array(
                        'service_data' => array(
                            'bookingpress_service_name' => $bookingpress_service_name
                        ),
                        'payable_amount' => floatval($bookingpress_final_payable_amount),
                        'customer_details' => array(
                            'customer_firstname' => $bookingpress_final_payment_data['form_fields']['customer_firstname'],
                            'customer_lastname' => $bookingpress_final_payment_data['form_fields']['customer_lastname'],
                            'customer_email' => $bookingpress_final_payment_data['form_fields']['customer_email'],
                            'customer_username' => $bookingpress_final_payment_data['form_fields']['customer_email'],
                        ),
                        'currency' => $bookingpress_currency_name,
                        'currency_code' => $bookingpress_currency_code,
                        'card_details' => array(
                            'card_holder_name' => $bookingpress_final_payment_data['card_holder_name'],
                            'card_number' => $bookingpress_final_payment_data['card_number'],
                            'expire_month' => $bookingpress_final_payment_data['expire_month'],
                            'expire_year' => $bookingpress_final_payment_data['expire_year'],
                            'cvv' => $bookingpress_final_payment_data['cvv'],
                        ),
                        'entry_id' => $bookingpress_appointment_id,
                        'booking_form_redirection_mode' => '',
                        'approved_appointment_url' => $bookingpress_complete_payment_page_url,
                        'canceled_appointment_url' => $bookingpress_after_canceled_payment_url,
                        'pending_appointment_url' => $bookingpress_complete_payment_page_url,
                        'notify_url' => $bookingpress_notify_url,
                        'recurring_details' => '',
                    );


                    if(!empty($bookingpress_appointment_id)){

                        global $tbl_bookingpress_appointment_meta;

                        $submit_payment_gateway_id = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_appointment_id = %d", 'submit_payment_gateway', $bookingpress_appointment_id) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name. false alarm
                        if( 1 > $submit_payment_gateway_id ){
                            $wpdb->insert(
                                $tbl_bookingpress_appointment_meta,
                                array(
                                    'bookingpress_appointment_meta_key' => 'submit_payment_gateway',
                                    'bookingpress_appointment_meta_value' => $payment_gateway,
                                    'bookingpress_appointment_id' => $bookingpress_appointment_id
                                )
                            );
                        } else {
                            $bookingpress_db_fields = array(
                                'bookingpress_appointment_meta_value' => $payment_gateway
                            );	
                            $wpdb->update( $tbl_bookingpress_appointment_meta, $bookingpress_db_fields, array( 'bookingpress_appointment_id' => $bookingpress_appointment_id, 'bookingpress_appointment_meta_key' => 'submit_payment_gateway' ) );
                        }

                    }

                    if($bookingpress_final_payable_amount == 0){
                        $bookingpress_payment_gateway_data = array('bookingpress_payment_gateway' => $payment_gateway);
                        $bookingpress_pro_payment_gateways->bookingpress_confirm_booking($bookingpress_appointment_id, $bookingpress_payment_gateway_data, '1', '', '', 1);
                        $wpdb->update($tbl_bookingpress_payment_logs, array('bookingpress_payment_gateway' => $payment_gateway), array('bookingpress_payment_log_id' => $bookingpress_payment_id));
                        $bookingpress_success_payment_message = $BookingPress->bookingpress_get_settings('complete_payment_success_message', 'message_setting');
                        do_action('bookingpress_modify_insert_data_zero_amount', $bookingpress_payment_id, $bookingpress_appointment_id, $bookingpress_final_payment_data);
                        $response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                        $response['msg'] = $bookingpress_success_payment_message;
                    }else{
                        if( 'manual' == $payment_gateway || '' == $payment_gateway ){
                            $no_payment_method_is_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_payment_method_is_selected_for_the_booking', 'message_setting');
                            $response['variant'] = 'error';
                            $response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
                            $response['msg'] = $no_payment_method_is_selected_for_the_booking;
                        } else {
                            if($payment_gateway != 'on-site'){
                                $response = apply_filters( 'bookingpress_' . $payment_gateway . '_submit_form_data', $response, $bookingpress_final_payment_request_data );
                                if(!empty($response['variant']) && $response['variant'] != 'error' ){                                                                        
                                    if($response['variant'] != 'redirect' ){
                                        $wpdb->update($tbl_bookingpress_payment_logs, array('bookingpress_payment_gateway' => $payment_gateway), array('bookingpress_payment_log_id' => $bookingpress_payment_id));                                        
                                    }                                                                        
                                    do_action('bookingpress_modify_insert_data', $bookingpress_payment_id, $bookingpress_appointment_id, $bookingpress_final_payment_data);
                                    $bookingpress_success_payment_message = $BookingPress->bookingpress_get_settings('complete_payment_success_message', 'message_setting');
                                    $response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
                                    $response['msg'] = $bookingpress_success_payment_message;
                                }
                            }
                        }
                    }
                }
            }
            if($return_data){
                return $response;
            }            
            echo wp_json_encode($response);
            die;
        }
        
        /**
         * Complete payment shortcode vue data fields
         *
         * @param  mixed $bookingpress_dynamic_data_fields
         * @return void
         */
        function bookingpress_complete_payment_dynamic_data_fields_func($bookingpress_dynamic_data_fields,$return_data = false){
            global $wpdb, $BookingPress, $bookingpress_front_vue_data_fields, $tbl_bookingpress_customers, $tbl_bookingpress_categories, $tbl_bookingpress_services, $tbl_bookingpress_servicesmeta, $tbl_bookingpress_form_fields, $bookingpress_global_options, $bookingpress_coupons, $bookingpress_deposit_payment, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_extra_services, $tbl_bookingpress_payment_logs, $bookingpress_pro_payment, $BookingPressPro;

            $bookingpress_complete_payment_data_vars['complete_payment_success_msg'] = '';
            $bookingpress_complete_payment_data_vars['payment_already_paid_msg'] = '';

            $bookingpress_complete_payment_data_vars['is_display_complete_payment_error'] = '0';
            $bookingpress_complete_payment_data_vars['is_complete_payment_error_msg'] = '';

			$bookingpress_complete_payment_data_vars['show_paypal_popup_button'] = "false";
            $bookingpress_complete_payment_data_vars['paypal_button_loader']= "false";
            
			$bookingpress_complete_payment_data_vars['paypal_success_url'] = "";
			$bookingpress_complete_payment_data_vars['paypal_cancel_url'] = "";
			$bookingpress_complete_payment_data_vars['paypal_booking_form_redirection_mode'] = "";

            $bookingpress_payment_already_paid_message = $BookingPress->bookingpress_get_settings('payment_already_paid_message', 'message_setting');

            $on_site_payment = $BookingPress->bookingpress_get_settings('on_site_payment', 'payment_setting');
            $paypal_payment  = $BookingPress->bookingpress_get_settings('paypal_payment', 'payment_setting');

            $bookingpress_complete_payment_data_vars['on_site_payment'] = $on_site_payment;
            $bookingpress_complete_payment_data_vars['paypal_payment']  = $paypal_payment;

            $bookingpress_total_configure_gateways = 0;
            $bookingpress_is_only_onsite_enabled   = 0;
            if (( $on_site_payment == 'true' || $on_site_payment == '1' ) && ( $paypal_payment == 'true' || $paypal_payment == '1' ) ) {
                $bookingpress_total_configure_gateways = 2;
                $bookingpress_is_only_onsite_enabled   = 0;
            } elseif (( $on_site_payment == 'true' || $on_site_payment == '1' ) && ( $paypal_payment == 'false' || empty($paypal_payment) ) ) {
                $bookingpress_total_configure_gateways = 1;
                $bookingpress_is_only_onsite_enabled   = 1;
            } elseif (( $on_site_payment == 'false' || empty($on_site_payment) ) && ( $paypal_payment == 'true' || $paypal_payment == '1' ) ) {
                $bookingpress_total_configure_gateways = 1;
                $bookingpress_is_only_onsite_enabled   = 0;
            }

            if(empty($bookingpress_complete_payment_data_vars['appointment_step_form_data'])){
                $bookingpress_complete_payment_data_vars['appointment_step_form_data'] = array();
            }
            
            $bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_payment_method'] = '';
            $bookingpress_complete_payment_data_vars['appointment_step_form_data']['is_complete_payment'] = "yes";

            $bookingpress_complete_payment_data_vars['total_configure_gateways'] = $bookingpress_total_configure_gateways;
            $bookingpress_complete_payment_data_vars['is_only_onsite_enabled']   = $bookingpress_is_only_onsite_enabled;

            if ($bookingpress_is_only_onsite_enabled == 1 ) {
                $bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_payment_method'] = 'on-site';
            }

            if ($bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_payment_method'] == '' && ( $paypal_payment == 'true' ) ) {
                $bookingpress_complete_payment_data_vars['paypal_payment'] = 'paypal';
            }

            if(!empty($_GET['bkp_pay'])){
                $bookingpress_other_gateways_tmp_data = array(
                    'bookingpress_activate_payment_gateway_counter' => $bookingpress_total_configure_gateways,
                );
                $bookingpress_other_gateways_tmp_data = apply_filters('bookingpress_frontend_apointment_form_add_dynamic_data', $bookingpress_other_gateways_tmp_data);

                foreach($bookingpress_other_gateways_tmp_data as $tmp_key => $tmp_val){
                    $bookingpress_complete_payment_data_vars[$tmp_key] = $tmp_val;    
                }
            }

            $bookigpress_time_format_for_booking_form =  $BookingPress->bookingpress_get_customize_settings('bookigpress_time_format_for_booking_form','booking_form');
            $bookigpress_time_format_for_booking_form =  !empty($bookigpress_time_format_for_booking_form) ? $bookigpress_time_format_for_booking_form : '2';
            $bookingpress_complete_payment_data_vars['bookigpress_time_format_for_booking_form'] = $bookigpress_time_format_for_booking_form;

            $bookingpress_complete_payment_data_vars['appointment_step_form_data']['form_fields'] = array(
                'customer_email' => '',
                'customer_name' => '',
                'customer_firstname' => '',
                'customer_lastname' => '',  
            );

            $bookingpress_complete_payment_data_vars['is_coupon_activated'] = $bookingpress_coupons->bookingpress_check_coupon_module_activation();
            $bookingpress_complete_payment_data_vars['bookingpress_allow_coupon_code'] = $bookingpress_coupons->bookingpress_check_coupon_module_activation();
            $bookingpress_complete_payment_data_vars['bookingpress_is_deposit_payment_activate'] = $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation();
            $bookingpress_complete_payment_data_vars['is_tax_activated']    = '';


            $bookingpress_complete_payment_data_vars['isLoadBookingLoader'] = '0';
            $bookingpress_complete_payment_data_vars['isBookingDisabled'] = false;
            if($bookingpress_total_configure_gateways == 0){
                $bookingpress_complete_payment_data_vars['isBookingDisabled'] = true;
            }

            $bookingpress_subtotal_text = $BookingPress->bookingpress_get_customize_settings( 'subtotal_text', 'booking_form' );
            $bookingpress_subtotal_text = !empty($bookingpress_subtotal_text) ? stripslashes_deep($bookingpress_subtotal_text) : esc_html__('Subtotal', 'bookingpress-appointment-booking');
            $bookingpress_complete_payment_data_vars['subtotal_text'] = $bookingpress_subtotal_text;
            
            
            $bookingpress_complete_payment_data_vars['appointment_step_form_data']['coupon_code']          = '';
			$bookingpress_complete_payment_data_vars['appointment_step_form_data']['total_payable_amount'] = '';
			$bookingpress_complete_payment_data_vars['appointment_step_form_data']['total_payable_amount_with_currency'] = '';

			$bookingpress_complete_payment_data_vars['appointment_step_form_data']['card_holder_name'] = '';
			$bookingpress_complete_payment_data_vars['appointment_step_form_data']['card_number']      = '';
			$bookingpress_complete_payment_data_vars['appointment_step_form_data']['expire_month']     = '';
			$bookingpress_complete_payment_data_vars['appointment_step_form_data']['expire_year']      = '';
			$bookingpress_complete_payment_data_vars['appointment_step_form_data']['cvv']              = '';

			$bookingpress_complete_payment_data_vars['coupon_code_msg']           = '';
			$bookingpress_complete_payment_data_vars['coupon_applied_status']     = 'error';
			$bookingpress_complete_payment_data_vars['coupon_discounted_amount']  = 0;
			$bookingpress_complete_payment_data_vars['coupon_apply_loader']       = '0';
			$bookingpress_complete_payment_data_vars['bpa_coupon_apply_disabled'] = 0;

            $coupon_code_title = $BookingPress->bookingpress_get_customize_settings('coupon_code_title', 'booking_form');
			$coupon_code_field_title = $BookingPress->bookingpress_get_customize_settings('coupon_code_field_title', 'booking_form');
			$coupon_apply_button_label = $BookingPress->bookingpress_get_customize_settings('coupon_apply_button_label', 'booking_form');
			$couon_applied_title = $BookingPress->bookingpress_get_customize_settings('couon_applied_title', 'booking_form');
			$bookingpress_complete_payment_data_vars['coupon_code_title'] = !empty($coupon_code_title) ? stripslashes_deep($coupon_code_title) : '';			
			$bookingpress_complete_payment_data_vars['coupon_code_field_title'] = !empty($coupon_code_field_title) ? stripslashes_deep($coupon_code_field_title) : '';
			$bookingpress_complete_payment_data_vars['coupon_apply_button_label'] = !empty($coupon_apply_button_label) ? stripslashes_deep($coupon_apply_button_label) : '';
			$bookingpress_complete_payment_data_vars['couon_applied_title'] = !empty($couon_applied_title) ? stripslashes_deep($couon_applied_title) : '';


            $deposit_paying_amount_title = $BookingPress->bookingpress_get_customize_settings('deposit_paying_amount_title', 'booking_form');
			$deposit_heading_title = $BookingPress->bookingpress_get_customize_settings('deposit_heading_title', 'booking_form');
			$deposit_remaining_amount_title = $BookingPress->bookingpress_get_customize_settings('deposit_remaining_amount_title', 'booking_form');
			$deposit_title = $BookingPress->bookingpress_get_customize_settings('deposit_title', 'booking_form');
			$full_payment_title = $BookingPress->bookingpress_get_customize_settings('full_payment_title', 'booking_form');						
            $bookingpress_complete_payment_data_vars['deposit_paying_amount_title'] = !empty($deposit_paying_amount_title) ? stripslashes_deep($deposit_paying_amount_title) : '';		
			$bookingpress_complete_payment_data_vars['deposit_heading_title'] = !empty($deposit_heading_title) ? stripslashes_deep($deposit_heading_title) : '';			
			$bookingpress_complete_payment_data_vars['deposit_remaining_amount_title'] = !empty($deposit_remaining_amount_title) ? stripslashes_deep($deposit_remaining_amount_title) : '';
			$bookingpress_complete_payment_data_vars['deposit_title'] = !empty($deposit_title) ? stripslashes_deep($deposit_title) : '';
			$bookingpress_complete_payment_data_vars['full_payment_title'] = !empty($full_payment_title) ? stripslashes_deep($full_payment_title) : '';

            //Load data calculations
            $bookingpress_payment_token = !empty($_GET['bkp_pay']) ? esc_html($_GET['bkp_pay']) : ''; //phpcs:ignore
			
			$bookingpress_payment_request_exists = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_complete_payment_token = %s", $bookingpress_payment_token)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
            $bookingpress_appointment_details = array();
			if($bookingpress_payment_request_exists > 0){
                $bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_complete_payment_token = %s", $bookingpress_payment_token), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

                $bookingpress_is_cart = !empty($bookingpress_appointment_details['bookingpress_is_cart']) ? intval($bookingpress_appointment_details['bookingpress_is_cart']) : 0;

                $bookingpress_order_id = !empty($bookingpress_appointment_details['bookingpress_order_id']) ? intval($bookingpress_appointment_details['bookingpress_order_id']) : 0;

                $bookingpress_payment_id = !empty($bookingpress_appointment_details['bookingpress_payment_id']) ? intval($bookingpress_appointment_details['bookingpress_payment_id']) : 0;

                $bookingpress_calculated_payment_details = $bookingpress_pro_payment->bookingpress_calculate_payment_details($bookingpress_payment_id);
            
                $bookingpress_complete_payment_data_vars['appointment_step_form_data']['is_cart'] = $bookingpress_is_cart;
                $bookingpress_complete_payment_data_vars['appointment_step_form_data']['cart_order_id'] = $bookingpress_order_id;
                $bookingpress_complete_payment_data_vars['appointment_step_form_data']['appointment_id'] = $bookingpress_appointment_details['bookingpress_appointment_booking_id'];
                $bookingpress_complete_payment_data_vars['appointment_step_form_data']['payment_id'] = $bookingpress_appointment_details['bookingpress_payment_id'];

				$bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();
                $bookingpress_time_format = $bookingpress_global_data['wp_default_time_format'];
                $bpa_wp_default_time_format = apply_filters('bookingpress_change_time_slot_format',$bookingpress_time_format);

				$bookingpress_selected_currency = $bookingpress_appointment_details['bookingpress_service_currency'];
				$bookingpress_selected_currency = $BookingPress->bookingpress_get_currency_symbol($bookingpress_selected_currency);
				$bookingpress_selected_service_name = $bookingpress_appointment_details['bookingpress_service_name'];
				$bookingpress_selected_service_price = $bookingpress_appointment_details['bookingpress_service_price'];
				$bookingpress_selected_date = $bookingpress_appointment_details['bookingpress_appointment_date'];
				$bookingpress_selected_start_time = $bookingpress_appointment_details['bookingpress_appointment_time'];
				$bookingpress_selected_end_time = $bookingpress_appointment_details['bookingpress_appointment_end_time'];
				$bookingpress_customer_email = $bookingpress_appointment_details['bookingpress_customer_email'];
				$bookingpress_customer_name = $bookingpress_appointment_details['bookingpress_customer_name'];
				$bookingpress_customer_firstname = $bookingpress_appointment_details['bookingpress_customer_firstname'];
				$bookingpress_customer_lastname = $bookingpress_appointment_details['bookingpress_customer_lastname'];
				$bookingpress_total_payable_amount = $bookingpress_appointment_details['bookingpress_paid_amount'];
				
                $bookingpress_formatted_start_time = date_i18n($bpa_wp_default_time_format, strtotime($bookingpress_selected_start_time));
                $bookingpress_formatted_end_time   = date_i18n($bpa_wp_default_time_format, strtotime($bookingpress_selected_end_time));

                if($bookigpress_time_format_for_booking_form == '1' || $bookigpress_time_format_for_booking_form == '2') {
                    $bookingpress_formatted_start_end_time = sprintf( esc_html__( '%1$s to %2$s', 'bookingpress-appointment-booking' ),$bookingpress_formatted_start_time, $bookingpress_formatted_end_time);
                }
                if($bookigpress_time_format_for_booking_form == '5' || $bookigpress_time_format_for_booking_form == '6'){
                    $bookingpress_formatted_start_end_time = sprintf( esc_html__( '%1$s - %2$s', 'bookingpress-appointment-booking' ),$bookingpress_formatted_start_time, $bookingpress_formatted_end_time);                            
                }
                if($bookigpress_time_format_for_booking_form == '3' || $bookigpress_time_format_for_booking_form == '4'){
                    $bookingpress_formatted_start_end_time = sprintf( esc_html__( '%1$s', 'bookingpress-appointment-booking' ),$bookingpress_formatted_start_time);                            
                }
                if($bookigpress_time_format_for_booking_form == 'bookingpress-wp-inherit-time-format' || $bookigpress_time_format_for_booking_form == ''){
                    $bookingpress_formatted_start_end_time = sprintf( esc_html__( '%1$s - %2$s', 'bookingpress-appointment-booking' ),$bookingpress_formatted_start_time, $bookingpress_formatted_end_time);           
                }   

                $bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_service_name'] = $bookingpress_selected_service_name;
				$bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_date'] = $bookingpress_selected_date;
				$bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_start_time'] = $bookingpress_selected_start_time;
				$bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_end_time'] = $bookingpress_selected_end_time;
				$bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_formatted_start_time'] = date($bookingpress_time_format, strtotime($bookingpress_selected_start_time));
				$bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_formatted_end_time'] = date($bookingpress_time_format, strtotime($bookingpress_selected_end_time));
                $bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_formatted_start_end_time'] = $bookingpress_formatted_start_end_time;

				$bookingpress_complete_payment_data_vars['appointment_step_form_data']['customer_email'] = $bookingpress_customer_email;
				$bookingpress_complete_payment_data_vars['appointment_step_form_data']['customer_firstname'] = $bookingpress_customer_firstname;
				$bookingpress_complete_payment_data_vars['appointment_step_form_data']['customer_lastname'] = $bookingpress_customer_lastname;
				$bookingpress_complete_payment_data_vars['appointment_step_form_data']['form_fields']['customer_firstname'] = $bookingpress_customer_firstname;
				$bookingpress_complete_payment_data_vars['appointment_step_form_data']['form_fields']['customer_lastname'] = $bookingpress_customer_lastname;
				$bookingpress_complete_payment_data_vars['appointment_step_form_data']['form_fields']['customer_email'] = $bookingpress_customer_email;
				

                $bookingpress_payment_method = "on-site";
                if(!empty($bookingpress_payment_id)){
                    $bookingpress_payment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_payment_gateway FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_payment_log_id = %d", $bookingpress_payment_id), ARRAY_A);//phpcs:ignore

                    if(!empty($bookingpress_payment_details['bookingpress_payment_gateway'])){
                        $bookingpress_payment_method = $bookingpress_payment_details['bookingpress_payment_gateway'];
                    }
                }

                $bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_payment_method'] = $bookingpress_payment_method;

                $bookingpress_complete_payment_data_vars['cart_items'] = array();
                $bookingpress_complete_payment_data_vars['is_cart_enabled'] = '0';
                
                if(!empty($bookingpress_appointment_details['bookingpress_tax_amount'])){
                    $bookingpress_complete_payment_data_vars['is_tax_activated'] = '1';
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['tax_amount'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_appointment_details['bookingpress_tax_amount'], $bookingpress_selected_currency);
                    $bookingpress_complete_payment_data_vars['bookingpress_appointment_tax_display'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_appointment_details['bookingpress_tax_amount'], $bookingpress_selected_currency);
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['tax_amount_without_currency'] = $bookingpress_appointment_details['bookingpress_tax_amount'];
                }

                $bookingpress_is_group_order = apply_filters('bookingpress_check_is_group_order_for_complete_payment',false, $bookingpress_appointment_details);
                $bookingpress_is_recurring = (isset($bookingpress_appointment_details['bookingpress_is_recurring'])) ? intval($bookingpress_appointment_details['bookingpress_is_recurring']) : 0;
                $bookingpress_complete_payment_data_vars['bookingpress_is_recurring'] = $bookingpress_is_recurring;
                
                if($bookingpress_is_cart || $bookingpress_is_group_order){
                    $bookingpress_complete_payment_data_vars['is_cart_enabled'] = '1';

                    $bookingpress_deposit_details = !empty($bookingpress_appointment_details['bookingpress_deposit_payment_details']) ? json_decode($bookingpress_appointment_details['bookingpress_deposit_payment_details'], TRUE) : array();
                    $bookingpress_deposit_selected_type = !empty($bookingpress_deposit_details['deposit_selected_type']) ? $bookingpress_deposit_details['deposit_selected_type'] : 'percentage';

                    $bookingpress_deposit_amount = !empty($bookingpress_calculated_payment_details['deposit_amount']) ? $bookingpress_calculated_payment_details['deposit_amount'] : 0;
                    $bookingpress_deposit_amount_with_currency = !empty($bookingpress_calculated_payment_details['deposit_amount_with_currency']) ? $bookingpress_calculated_payment_details['deposit_amount_with_currency'] : 0;

                    $bookingpress_due_amount = !empty($bookingpress_calculated_payment_details['due_amount']) ? $bookingpress_calculated_payment_details['due_amount'] : 0;
                    $bookingpress_due_amount_with_currency = !empty($bookingpress_calculated_payment_details['due_amount_with_currency']) ? $bookingpress_calculated_payment_details['due_amount_with_currency'] : 0;

                    $bookingpress_subtotal_amount = !empty($bookingpress_calculated_payment_details['subtotal_amount']) ? $bookingpress_calculated_payment_details['subtotal_amount'] : 0;
                    $bookingpress_subtotal_amount_with_currency = !empty($bookingpress_calculated_payment_details['subtotal_amount_with_currency']) ? $bookingpress_calculated_payment_details['subtotal_amount_with_currency'] : 0;

                    $bookingpress_total_amount = !empty($bookingpress_calculated_payment_details['total_amount']) ? $bookingpress_calculated_payment_details['total_amount'] : 0;
                    $bookingpress_total_amount_with_currency = !empty($bookingpress_calculated_payment_details['total_amount_with_currency']) ? $bookingpress_calculated_payment_details['total_amount_with_currency'] : 0;

                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['deposit_payment_type'] = $bookingpress_deposit_selected_type;
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['deposit_payment_amount'] = $bookingpress_deposit_amount;
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['bookingpress_deposit_amt'] = $bookingpress_deposit_amount_with_currency;
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['service_price_without_currency'] = $bookingpress_subtotal_amount;
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_service_price'] = $bookingpress_subtotal_amount_with_currency;
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['total_payable_amount'] = !empty($bookingpress_due_amount) ? $bookingpress_due_amount : $bookingpress_total_amount ;
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['total_payable_amount_with_currency'] = !empty($bookingpress_due_amount) ? $bookingpress_due_amount_with_currency : $bookingpress_total_amount_with_currency ;

                    
                    //If coupon applied then display multiple appointment details
                    $bookingpress_cart_appointment_details = $bookingpress_calculated_payment_details['appointment_details'];
                    foreach($bookingpress_cart_appointment_details as $bookingpress_cart_appointment_key => $bookingpress_cart_appointment_val){
                        $bookingpress_cart_appointment_val['bookingpress_service_name'] = $BookingPressPro->bookingpress_pro_front_language_translation_func($bookingpress_cart_appointment_val['bookingpress_service_name'],'service','bookingpress_service_name',$bookingpress_cart_appointment_val['bookingpress_service_id']);  

                        $bookingpress_cart_formatted_start_time = date_i18n($bpa_wp_default_time_format, strtotime($bookingpress_cart_appointment_val['bookingpress_appointment_time']));
                        $bookingpress_cart_formatted_end_time   = date_i18n($bpa_wp_default_time_format, strtotime($bookingpress_cart_appointment_val['bookingpress_appointment_end_time']));

                        if($bookigpress_time_format_for_booking_form == '1' || $bookigpress_time_format_for_booking_form == '2') {
                            $bookingpress_cart_formatted_start_end_time = sprintf( esc_html__( '%1$s to %2$s', 'bookingpress-appointment-booking' ),$bookingpress_cart_formatted_start_time, $bookingpress_cart_formatted_end_time);
                        }
                        if($bookigpress_time_format_for_booking_form == '5' || $bookigpress_time_format_for_booking_form == '6'){
                            $bookingpress_cart_formatted_start_end_time = sprintf( esc_html__( '%1$s - %2$s', 'bookingpress-appointment-booking' ),$bookingpress_cart_formatted_start_time, $bookingpress_cart_formatted_end_time);                            
                        }
                        if($bookigpress_time_format_for_booking_form == '3' || $bookigpress_time_format_for_booking_form == '4'){
                            $bookingpress_cart_formatted_start_end_time = sprintf( esc_html__( '%1$s', 'bookingpress-appointment-booking' ),$bookingpress_cart_formatted_start_time);                            
                        }
                        if($bookigpress_time_format_for_booking_form == 'bookingpress-wp-inherit-time-format' || $bookigpress_time_format_for_booking_form == ''){
                            $bookingpress_cart_formatted_start_end_time = sprintf( esc_html__( '%1$s - %2$s', 'bookingpress-appointment-booking' ),$bookingpress_cart_formatted_start_time, $bookingpress_cart_formatted_end_time);           
                        }  

                        $bookingpress_complete_payment_data_vars['cart_items'][] = array(
                            'bookingpress_service_name' => $bookingpress_cart_appointment_val['bookingpress_service_name'],
                            'bookingpress_selected_date' => $bookingpress_cart_appointment_val['bookingpress_appointment_date'],
                            'bookingpress_selected_start_time' => $bookingpress_cart_appointment_val['bookingpress_appointment_time'],
                            'bookingpress_selected_end_time' => $bookingpress_cart_appointment_val['bookingpress_appointment_end_time'],
                            'bookingpress_selected_start_end_time' => $bookingpress_cart_formatted_start_end_time,
                        );
                    }

                }else{
                    //If staffmember selected then apply staffmember price
                    if(!empty($bookingpress_appointment_details['bookingpress_staff_member_id'])){
                        $bookingpress_selected_service_price = $bookingpress_appointment_details['bookingpress_staff_member_price'];
                    }
                    if(!empty($bookingpress_appointment_details['bookingpress_enable_custom_duration']) && $bookingpress_appointment_details['bookingpress_enable_custom_duration'] == 1){
                        $bookingpress_selected_service_price = $bookingpress_appointment_details['bookingpress_service_price'];
                    }

                    //Calculate service extra price
                    $bookingpress_extra_service_price_arr = array();
                    if(!empty($bookingpress_appointment_details['bookingpress_extra_service_details'])){
                        $bookingpress_extra_service_details = json_decode($bookingpress_appointment_details['bookingpress_extra_service_details'], TRUE);
                        $bookingpress_extra_service_details = array_map( array( $BookingPress, 'appointment_sanatize_field'), $bookingpress_extra_service_details ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function

                        if( is_array($bookingpress_extra_service_details) && !empty($bookingpress_extra_service_details) ){
                            foreach($bookingpress_extra_service_details as $k => $v){
                                if($v['bookingpress_is_selected'] == "true" || $v['bookingpress_is_selected'] == "1"){
                                    $bookingpress_extra_service_id = intval($k);
                                    //$bookingpress_extra_service_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_extra_services_id = %d", $bookingpress_extra_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is a table name. false alarm

                                    $bookingpress_extra_service_details = $v['bookingpress_extra_service_details'];

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
                    }

                    //Add bring anyone module price
                    if(!empty($bookingpress_appointment_details['bookingpress_selected_extra_members'])){
                        $bookingpress_selected_service_price = ($bookingpress_selected_service_price * intval($bookingpress_appointment_details['bookingpress_selected_extra_members']));
                    }


                    if ( ! empty( $bookingpress_extra_service_price_arr ) && is_array( $bookingpress_extra_service_price_arr ) ) {
                        foreach ( $bookingpress_extra_service_price_arr as $k => $v ) {
                            $bookingpress_selected_service_price = $bookingpress_selected_service_price + $v;
                        }
                    }

                    if(!empty($bookingpress_appointment_details['bookingpress_tax_amount'])){
                        $bookingpress_complete_payment_data_vars['is_tax_activated'] = '1';
                        $bookingpress_complete_payment_data_vars['appointment_step_form_data']['tax_amount'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_appointment_details['bookingpress_tax_amount'], $bookingpress_selected_currency);
                    }

                    if(!empty($bookingpress_appointment_details['bookingpress_deposit_amount'])){

                        $bookingpress_due_amount = !empty($bookingpress_calculated_payment_details['due_amount']) ? $bookingpress_calculated_payment_details['due_amount'] : 0;
                        $bookingpress_due_amount_with_currency = !empty($bookingpress_calculated_payment_details['due_amount_with_currency']) ? $bookingpress_calculated_payment_details['due_amount_with_currency'] : 0;
        

                        $bookingpress_deposit_details = !empty($bookingpress_appointment_details['bookingpress_deposit_payment_details']) ? json_decode($bookingpress_appointment_details['bookingpress_deposit_payment_details'], TRUE) : array();
                        if(!empty($bookingpress_deposit_details['deposit_amount'])){
                            $bookingpress_deposit_selected_type = $bookingpress_deposit_details['deposit_selected_type'];
                            $bookingpress_deposit_amount = floatval($bookingpress_deposit_details['deposit_amount']);
                            $bookingpress_due_amount = floatval($bookingpress_due_amount);
                            if(!empty($bookingpress_due_amount)){
                                $bookingpress_total_payable_amount = $bookingpress_due_amount;                                
                            }

                            $bookingpress_deposit_tmp_total = $bookingpress_deposit_amount + $bookingpress_due_amount;

                            $bookingpress_complete_payment_data_vars['appointment_step_form_data']['deposit_payment_type'] = $bookingpress_deposit_selected_type;
                            $bookingpress_complete_payment_data_vars['appointment_step_form_data']['deposit_payment_amount'] = $bookingpress_deposit_amount;
                            $bookingpress_complete_payment_data_vars['appointment_step_form_data']['bookingpress_deposit_amt'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_deposit_amount, $bookingpress_selected_currency);
                        }
                    }

                    $bookingpress_complete_payment_data_vars['is_coupon_already_applied'] = '0';

                    if(!empty($bookingpress_appointment_details['bookingpress_coupon_discount_amount'])){
                        $bookingpress_applied_coupon_amount = floatval($bookingpress_appointment_details['bookingpress_coupon_discount_amount']);
                        $bookingpress_applied_coupon_data = !empty($bookingpress_appointment_details['bookingpress_coupon_details']) ? json_decode($bookingpress_appointment_details['bookingpress_coupon_details'], TRUE) : '';
                        if(!empty($bookingpress_applied_coupon_data)){
                            $bookingpress_complete_payment_data_vars['coupon_applied_status'] = "success";
                            if(!empty($bookingpress_applied_coupon_data['coupon_data'])){
                                $bookingpress_complete_payment_data_vars['appointment_step_form_data']['coupon_code'] = $bookingpress_applied_coupon_data['coupon_data']['bookingpress_coupon_code'];
                            }else{
                                $bookingpress_complete_payment_data_vars['appointment_step_form_data']['coupon_code'] = $bookingpress_applied_coupon_data['bookingpress_coupon_code'];
                            }

                            $bookingpress_complete_payment_data_vars['is_coupon_already_applied'] = '1';
                            
                            $bookingpress_complete_payment_data_vars['coupon_discounted_amount'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_applied_coupon_amount, $bookingpress_selected_currency);
                            $bookingpress_complete_payment_data_vars['appointment_step_form_data']['coupon_discount_amount_with_currecny'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_applied_coupon_amount, $bookingpress_selected_currency);
                        }
                    }

                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['service_price_without_currency'] = $bookingpress_selected_service_price;
                    $bookingpress_selected_service_price_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_selected_service_price, $bookingpress_selected_currency);
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_service_price'] = $bookingpress_selected_service_price_with_currency;

                    $bookingpress_total_payable_amount_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_total_payable_amount, $bookingpress_selected_currency);
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['total_payable_amount'] = $bookingpress_total_payable_amount;
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['total_payable_amount_with_currency'] = $bookingpress_total_payable_amount_with_currency;

                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_service'] = $bookingpress_appointment_details['bookingpress_service_id'];
                }
            }else{
                $bookingpress_complete_payment_data_vars['payment_already_paid_msg'] = $bookingpress_payment_already_paid_message;
            }

            $bookingpress_complete_payment_data_vars = apply_filters('modify_complate_payment_data_after_entry_create',$bookingpress_complete_payment_data_vars, $bookingpress_appointment_details);

            if($return_data){
                return $bookingpress_complete_payment_data_vars;
            }
            $bookingpress_dynamic_data_fields = wp_json_encode($bookingpress_complete_payment_data_vars);

            return $bookingpress_dynamic_data_fields;
        }
        
        /**
         * Callback function of [bookingpress_complete_payment] shortcode
         *
         * @param  mixed $atts
         * @param  mixed $content
         * @param  mixed $tag
         * @return void
         */
        function bookingpress_complete_payment_form($atts, $content, $tag){
            global $wpdb, $BookingPress, $bookingpress_global_options;

            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1);
            $BookingPress->bookingpress_load_booking_form_custom_css();

            $bookingpress_uniq_id = uniqid();
            $bookingpress_vue_root_element_id = '#bookingpress_booking_form_' . $bookingpress_uniq_id;

            $bookingpress_script_return_data = '';
            $bookingpress_nonce = esc_html(wp_create_nonce('bpa_wp_nonce'));

            $bookingpress_customize_settings = $BookingPress->bookingpress_get_customize_settings(
                array(
                    'service_title',
                    'datetime_title',
                    'basic_details_title',
                    'summary_title',
                    'category_title',
                    'service_heading_title',
                    'timeslot_text',
                    'summary_content_text',
                    'service_duration_label',
                    'service_price_label',
                    'paypal_text',
                    'locally_text',
                    'total_amount_text',
                    'service_text',
                    'customer_text',
                    'date_time_text',
                    'payment_method_text',
                    'morning_text',
                    'afternoon_text',
                    'evening_text',
                    'night_text',
                    'goback_button_text',
                    'next_button_text',
                    'book_appointment_btn_text',
                    'book_appointment_hours_text',
                    'book_appointment_min_text',
                    'book_appointment_day_text',
                    'booking_form_tabs_position',
                    'hide_category_service_selection',                    
                    'title_font_family',
                    'content_font_family',
                    'display_service_description',
                    'all_category_title',
                    'complete_payment_deposit_amt_title',
                    'make_payment_button_title',
                    'appointment_details'
                ),
                'booking_form'
            );

            $bookingpress_fourth_tab_name = stripslashes_deep($bookingpress_customize_settings['summary_title']);
            $bookingpress_book_appointment_btn_text = stripslashes_deep($bookingpress_customize_settings['book_appointment_btn_text']);
            $bookingpress_summary_content_text = stripslashes_deep($bookingpress_customize_settings['summary_content_text']);
            $bookingpress_service_text = stripslashes_deep($bookingpress_customize_settings['service_text']);
            $bookingpress_date_time_text = stripslashes_deep($bookingpress_customize_settings['date_time_text']);
            $bookingpress_customer_text = stripslashes_deep($bookingpress_customize_settings['customer_text']);
            $bookingpress_total_amount_text = stripslashes_deep($bookingpress_customize_settings['total_amount_text']);
            $bookingpress_book_appointment_btn_text = stripslashes_deep($bookingpress_customize_settings['book_appointment_btn_text']);
            $bookingpress_book_appointment_hours_label = stripslashes_deep($bookingpress_customize_settings['book_appointment_hours_text']);
            $bookingpress_book_appointment_min_label = stripslashes_deep($bookingpress_customize_settings['book_appointment_min_text']);
            $bookingpress_book_appointment_day_label = stripslashes_deep($bookingpress_customize_settings['book_appointment_day_text']);
            $bookingpress_payment_method_text = stripslashes_deep($bookingpress_customize_settings['payment_method_text']);
            $bookingpress_locally_text = stripslashes_deep($bookingpress_customize_settings['locally_text']);
            $bookingpress_paypal_text = stripslashes_deep($bookingpress_customize_settings['paypal_text']);
            $bookingpress_appointment_details_title_text = stripslashes_deep($bookingpress_customize_settings['appointment_details']);

            $complete_payment_deposit_title = stripslashes_deep($bookingpress_customize_settings['complete_payment_deposit_amt_title']);
            $make_payment_button_title = stripslashes_deep($bookingpress_customize_settings['make_payment_button_title']);

            $bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_default_date_format = $BookingPress->bookingpress_check_common_date_format($bookingpress_global_options_arr['wp_default_date_format']);
            if(!empty($bookingpress_default_date_format)){
                $bookingpress_default_date_format = strtoupper($bookingpress_default_date_format);
            }
            $bookingpress_formatted_timeslot = $bookingpress_global_options_arr['bpa_time_format_for_timeslot'];

            $bookingpress_payment_token = !empty($_GET['bkp_pay']) ? esc_html($_GET['bkp_pay']) : ''; //phpcs:ignore

            $bookingpress_dynamic_data_fields = '';
            $bookingpress_dynamic_data_fields = apply_filters('bookingpress_complete_payment_dynamic_data_fields', $bookingpress_dynamic_data_fields);
            
            
            $bookingpress_after_selecting_payment_method_data = '';
            $bookingpress_after_selecting_complete_payment_method_data = '';
            $bpa_complete_payment = (isset($_REQUEST['bpa_complete_payment']))?sanitize_text_field($_REQUEST['bpa_complete_payment']):'';
            if(empty($bpa_complete_payment)){                
                $bookingpress_after_selecting_payment_method_data = apply_filters('bookingpress_after_selecting_payment_method', $bookingpress_after_selecting_payment_method_data);
                $bookingpress_after_selecting_complete_payment_method_data = apply_filters('bookingpress_after_selecting_complete_payment_method_data', $bookingpress_after_selecting_complete_payment_method_data);
            }
            
            $bookingpress_add_complete_payment_method_data = '';
            $bookingpress_add_complete_payment_method_data = apply_filters('bookingpress_add_complete_payment_method', $bookingpress_add_complete_payment_method_data);


            ob_start();
            $bookingpress_complete_payment_shortcode_file = BOOKINGPRESS_PRO_VIEWS_DIR . '/frontend/appointment_booking_payment.php';
            include $bookingpress_complete_payment_shortcode_file;
            $content .= ob_get_clean();

            $bookingpress_script_return_data .= 'app = new Vue({ 
				el: "' . $bookingpress_vue_root_element_id . '",
				data(){
					var bookingpress_return_data = ' . $bookingpress_dynamic_data_fields . ';
                    bookingpress_return_data["is_booking_form_empty_loader"] = "1";
                    bookingpress_return_data["appointment_step_form_data"]["bookingpress_uniq_id"] = "' . $bookingpress_uniq_id . '";
					var bookingpress_captcha_key = "bookingpress_captcha_' . $bookingpress_uniq_id . '";
					bookingpress_return_data["appointment_step_form_data"][bookingpress_captcha_key] = "";

					return bookingpress_return_data;
				},
                filters: {
					bookingpress_format_date: function(value){
						var default_date_format = "' . $bookingpress_default_date_format . '";
						return moment(String(value)).format(default_date_format)
					},
					bookingpress_format_time: function(value){
						var default_time_format = "' . $bookingpress_formatted_timeslot . '";
						return moment(String(value), "HH:mm:ss").format(default_time_format)
					}
				},
                beforeCreate(){
					this.is_booking_form_empty_loader = "1";
				},
				created(){
					this.bookingpress_load_complete_payment_form();
				},
				mounted(){
                    this.loadSpamProtection();
                    this.expirationDate();
                    this.select_payment_method(this.appointment_step_form_data.selected_payment_method);
				},
				methods: {
                    bookingpress_load_complete_payment_form(){
                        const vm = this;
                        setTimeout(function(){
                            vm.is_booking_form_empty_loader = "0";
                            setTimeout(function(){
                                var total_payment_div_count = document.querySelectorAll(".bpa-front-module--pm-body__item").length;
                                if(total_payment_div_count == 1){
                                    var total_payment_div = document.querySelector(".bpa-front-module--pm-body__item");
                                    if( null != total_payment_div && "undefined" != typeof total_payment_div) {
                                        total_payment_div.click();
                                    }
                                }
                                if(document.getElementById("bpa-front-tabs") != null){
                                    document.getElementById("bpa-front-tabs").style.display = "flex";
                                }
                                if(document.getElementById("bpa-front-data-empty-view") != null){
                                    document.getElementById("bpa-front-data-empty-view").style.display = "flex";
                                }
                                if(document.getElementById("bpa-complete-payment-message") != null){
                                    document.getElementById("bpa-complete-payment-message").style.visibility = "visible";
                                }
                                if(document.getElementById("bpa-payment-already-completed-message") != null){
                                    document.getElementById("bpa-payment-already-completed-message").style.visibility = "visible";
                                }
                            }, 500);
                        }, 2000);
                    },
					generateSpamCaptcha(){
						const vm = this;
                        var bkp_wpnonce_pre = "' . $bookingpress_nonce . '";
                        var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                        if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                        {
                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                        }
                        else {
                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                        }
						var postData = { action: "bookingpress_generate_spam_captcha", _wpnonce:bkp_wpnonce_pre_fetch };
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
						.then( function (response) {
							if(response.variant != "error" && (response.data.captcha_val != "" && response.data.captcha_val != undefined)){
								
							}else{
                                var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                                if(typeof bkp_wpnonce_pre_fetch!="undefined" && bkp_wpnonce_pre_fetch!=null && response.data.updated_nonce!="")
                                {
                                    document.getElementById("_wpnonce").value = response.data.updated_nonce;
                                } else {
                                    vm.$notify({
                                        title: response.data.title,
                                        message: response.data.msg,
                                        type: response.data.variant,
                                        customClass: "error_notification"
                                    });
                                }
							}
						}.bind(this) )
						.catch( function (error) {
							console.log(error);
						});
					},
					loadSpamProtection(){
						const vm = this;
						vm.generateSpamCaptcha();
					},
                    select_payment_method(payment_method){
                        const vm = this;
                        vm.appointment_step_form_data.selected_payment_method = payment_method;
                        var bookingpress_allowed_payment_gateways_for_card_fields = [];
                        ' . $bookingpress_after_selecting_payment_method_data . '
                        '.$bookingpress_after_selecting_complete_payment_method_data.'
                        if(bookingpress_allowed_payment_gateways_for_card_fields.includes(payment_method)){
                            vm.is_display_card_option = 1;
                        }else{
                            vm.is_display_card_option = 0;
                            vm.isBookingDisabled = false
                        }
                    },
                    bookingpress_set_complete_payment_error_msg( error_msg ){
                        const vm = this;
                        let container = vm.$el;
                        let pos = 0;
                        if( null != container ){
                            pos = container.getBoundingClientRect().top + window.scrollY;
                        }
                        vm.is_display_complete_payment_error = "1";
                        vm.is_complete_payment_error_msg = error_msg;
                        window.scrollTo({
                            top: pos,
                            behavior: "smooth",
                        });
                        setTimeout(function(){
                            vm.bookingpress_remove_complete_payment_error_msg();
                        },3000);
                    },
                    bookingpress_remove_complete_payment_error_msg(){
                        const vm = this;
                        vm.is_display_complete_payment_error = "0";
                        vm.is_complete_payment_error_msg = "";
                    },
                    inputFormat() {
                        let text = this.appointment_step_form_data.card_number.split(" ").join("");
                        /* this.cardVdid is not formated in 4 spaces */
                        this.cardVadid = text;
                        if (text.length > 0) {
                            /* regExp 4 in 4 number add an space between */
                            text = text.match(new RegExp(/.{1,4}/, "g")).join(" ")
                                                            /* accept only numbers */
                                .replace(new RegExp(/[^\d]/, "ig"), " ");
                        }
                        /* this.appointment_step_form_data.card_number is formated on 4 spaces */
                        this.appointment_step_form_data.card_number = text;
                        /* after formatd they callback cardType for choose a type of the card */
                        this.GetCardType(this.cardVadid)
                    },
                    /* loop for the next 9 years for expire data on credit card */
                    expirationDate() {
                        let yearNow = new Date().getFullYear();
                        for (let i = yearNow; i < yearNow + this.timeToExpire; i++) {
                            this.years.push({ year: i });
                        }
                    },
                    validCreditCard(value) {
                        let inputValidate = document.getElementById("cardNumber");
                         /* luhn algorithm */
                        let numCheck = 0,
                            bEven = false;
                        value = value.toString().replace(new RegExp(/\D/g, ""));
                        for (let n = value.length - 1; n >= 0; n--) {
                            let cDigit = value.charAt(n),
                                digit = parseInt(cDigit, 10);

                            if (bEven && (digit *= 2) > 9) digit -= 9;
                            numCheck += digit;
                            bEven = !bEven;
                        }
                        let len = value.length;
                        /* true: return valid number */
                        /* this.cardType return true if have an valid number on regx array */
                        if (numCheck % 10 === 0 && len === 16 && this.cardType) {
                            inputValidate.classList.remove("notValid");
                            inputValidate.classList.add("valid");
                            this.isBookingDisabled = false;
                        }
                        /* false: return not valid number */
                        else if (!(numCheck % 10 === 0) && len === 16) {
                            inputValidate.classList.remove("valid");
                            inputValidate.classList.add("notValid");
                            this.isBookingDisabled = true;
                            /* if not have number on input */
                        } else {
                            inputValidate.classList.remove("valid");
                            inputValidate.classList.remove("notValid");
                            this.isBookingDisabled = false;
                        }

                    },
                    /* get the name of the card name  */
                    GetCardType(number) {
                        this.regx.forEach((item) => {
                            if (number.match(item.re) != null) {
                                this.cardType = item.logo;
                                /* cClass add a class with the name of cardName to manipulate with css */
                                this.cClass = item.name.toLowerCase();
                            } else if (!number) {
                                this.cardType = "";
                                this.cClass = "";
                            }
                        });
                        /* after choose a cardtype return the number for the luhn algorithm  */
                        this.validCreditCard(number);
                    },
                    /* mouse down on btn */
                    mouseDw() {
                        this.btnClassName = "btn__active";
                    },
                    /* mouse up on btn */
                    mouseUp() {
                        this.btnClassName = "";
                    },
                    blr() {
                        let cr = document.getElementsByClassName("card--credit__card")[0];
                        if( null != cr && "undefined" != typeof cr.classList ){
                            cr.classList.remove("cvv-active");
                        }
                    },
                    bookingpress_complete_payment(){
                        const vm = this;
                        
                        vm.isLoadBookingLoader = "1";
                        vm.isBookingDisabled = true;
                        
                        var bookingpress_postdata = { action: "bookingpress_final_complete_payment", _wpnonce: "'.$bookingpress_nonce.'" };
                        bookingpress_postdata.complete_payment_data = JSON.stringify( vm.appointment_step_form_data );
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_postdata ) )
                        .then( function (response){
                            vm.isLoadBookingLoader = "0";
                            vm.isBookingDisabled = false;
                            var bookingpress_uniq_id = vm.appointment_step_form_data.bookingpress_uniq_id;
                            if(response.data.variant != "error"){
                                if(response.data.variant == "redirect"){
                                    document.body.innerHTML += response.data.redirect_data;
                                    var scripts = document.querySelectorAll("script");
                                    var text = scripts[scripts.length - 1].textContent;
                                    eval(text);
                                }else if(response.data.variant == "redirect_url" && typeof response.data.is_transaction_completed != "undefined" && response.data.is_transaction_completed == "1"){
                                    vm.complete_payment_success_msg = response.data.msg;
                                }else if(response.data.variant == "redirect_url"){
                                    window.location.href = response.data.redirect_data
                                }else{
                                    vm.complete_payment_success_msg = response.data.msg;
                                }
                                setTimeout(function(){
                                    if(document.getElementById("bpa-complete-payment-message") != null){
                                        document.getElementById("bpa-complete-payment-message").style.visibility = "visible";
                                    }
                                }, 1000);
                            }else{
                                let error_msg = response.data.msg;
                                vm.bookingpress_set_complete_payment_error_msg( error_msg );
                            }
                        }
                        .bind( this ) )
                        .catch( function (error) {
                            console.log(error);
                        });
                    },
                    bookingpress_recalculate_payable_amount(){
                        const vm = this;
                        var bookingpress_recalculate_data = {};
                        bookingpress_recalculate_data.action = "bookingpress_recalculate_appointment_data";
                        bookingpress_recalculate_data.appointment_details = JSON.stringify( vm.appointment_step_form_data );

                        var bkp_wpnonce_pre = "' . $bookingpress_nonce . '";
                        var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                        if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                        {
                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                        }
                        else {
                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                        }

                        bookingpress_recalculate_data._wpnonce = bkp_wpnonce_pre_fetch;
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_recalculate_data ) )
                        .then( function (response) {
                            vm.appointment_step_form_data = response.data.appointment_data
                        }.bind(this) )
                        .catch( function (error) {
                            vm.bookingpress_set_error_msg(error)
                        });
                    },
                    bookingpress_complete_payment_apply_coupon(){
                        const vm = this;
                        vm.coupon_apply_loader = "1";
                        var bookingpress_apply_coupon_data = {};
                        bookingpress_apply_coupon_data.action = "bookingpress_apply_coupon_code";
                        bookingpress_apply_coupon_data.appointment_details = JSON.stringify( vm.appointment_step_form_data );

                        var bkp_wpnonce_pre = "' . $bookingpress_nonce . '";
                        var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
                        if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
                        {
                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
                        }
                        else {
                            bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
                        }

                        bookingpress_apply_coupon_data._wpnonce = bkp_wpnonce_pre_fetch;
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_apply_coupon_data ) )
                        .then( function (response) {
                            vm.coupon_apply_loader = "0";
                            vm.coupon_applied_status = response.data.variant;
                            if(response.data.variant == "error"){
                                vm.coupon_code_msg = response.data.msg;
                            }else{
                                vm.coupon_code_msg = response.data.msg;
                                vm.coupon_discounted_amount = "-"+response.data.discounted_amount;
                                vm.bpa_coupon_apply_disabled = 1;
                                vm.bookingpress_recalculate_payable_amount();
                            }
                        }.bind(this) )
                        .catch( function (error) {
                            vm.bookingpress_set_error_msg(error);
                        });
                    },
                    bookingpress_remove_coupon_code(){
                        const vm = this;
                        vm.appointment_step_form_data.coupon_code = "";
                        vm.coupon_code_msg = "";
                        vm.bookingpress_recalculate_payable_amount();
                        vm.bpa_coupon_apply_disabled = 0;
                        vm.coupon_applied_status = "error";
                        vm.coupon_discounted_amount = "";
                    },
                    ' . $bookingpress_add_complete_payment_method_data . '
				},
			});';

            $bpa_script_data = "
            var app;
            window.addEventListener('DOMContentLoaded', function() {
                {$bookingpress_script_return_data}
            });";

            wp_add_inline_script('bookingpress_elements_locale', $bpa_script_data, 'after');

            $bookingpress_custom_css = $BookingPress->bookingpress_get_customize_settings('custom_css', 'booking_form');
            wp_add_inline_style( 'bookingpress_front_custom_css', $bookingpress_custom_css, 'after' );

            return do_shortcode( $content );
        }
    }

    global $bookingpress_pro_complete_payment;
	$bookingpress_pro_complete_payment = new bookingpress_pro_complete_payment();
}