<?php

if ( ! class_exists( 'bookingpress_paypal' ) ) {
	class bookingpress_paypal Extends BookingPress_Core {
		var $bookingpress_payment_mode    = '';
		var $bookingpress_is_sandbox_mode = true;
		var $bookingpress_api_username    = '';
		var $bookingpress_api_password    = '';
		var $bookingpress_api_signature   = '';
		var $bookingpress_merchant_email  = '';
		var $bookingpress_gateway_status  = '';

		var $paypal_payment_method_type = '';
		var $paypal_client_id = '';
		var $paypal_client_secret = '';

		var $bookingpress_paypal = false;

		function __construct() {
			add_filter( 'bookingpress_paypal_submit_form_data', array( $this, 'bookingpress_submit_form_data' ), 10, 2 );
			add_action( 'wp', array( $this, 'bookingpress_payment_gateway_data' ) );

			//Filter for add payment gateway to revenue filter list
			add_filter('bookingpress_revenue_filter_payment_gateway_list_add', array($this, 'bookingpress_revenue_filter_payment_gateway_list_add_func'));

			add_filter('bookingpress_paypal_apply_refund', array($this, 'bookingpress_paypal_apply_refund_func'),10,2);

			/* Paypal Confirm popup method payment */
			add_action('wp_ajax_bookingpress_paypal_booking_payment_confirm', array($this, 'bookingpress_paypal_booking_payment_confirm'), 10);
			add_action('wp_ajax_nopriv_bookingpress_paypal_booking_payment_confirm', array($this, 'bookingpress_paypal_booking_payment_confirm'), 10);
			
			add_action('wp_ajax_bookingpress_paypal_booking_validate', array($this, 'bookingpress_paypal_booking_validate_func'), 10);
			add_action('wp_ajax_nopriv_bookingpress_paypal_booking_validate', array($this, 'bookingpress_paypal_booking_validate_func'), 10);

			add_filter('bookingpress_after_selecting_payment_method_booking_form',array($this,'bookingpress_after_selecting_payment_method_func'),10,1);
			add_filter('bookingpress_after_selecting_complete_payment_method_data',array($this,'bookingpress_after_selecting_complete_payment_method_data_func'),10,1);

			add_action('bookingpress_paypal_payment_button_html',array($this,'bookingpress_paypal_payment_button_html'),10);

			/* Booking Form Add new data */
			add_filter('bookingpress_frontend_apointment_form_add_dynamic_data', array($this, 'bookingpress_frontend_data_fields_for_paypal'), 10);
			
			/* Complete payment paypal impletement */
			add_action('wp_ajax_bookingpress_paypal_booking_validate_complete_payment', array($this, 'bookingpress_paypal_booking_validate_complete_payment_func'), 10);
			add_action('wp_ajax_nopriv_bookingpress_paypal_booking_validate_complete_payment', array($this, 'bookingpress_paypal_booking_validate_complete_payment_func'), 10);			
						
			//Hook for modify next page request selection request
			add_filter('bookingpress_dynamic_next_page_request_filter', array($this, 'bookingpress_dynamic_next_page_request_filter_func'), 10, 1);

		}		


		/**
		 * Function for execute code when next step trigger
		 *
		 * @param  mixed $bookingpress_dynamic_next_page_request_filter
		 * @return void
		 */
		function bookingpress_dynamic_next_page_request_filter_func($bookingpress_dynamic_next_page_request_filter){
			global $BookingPress;


			$paypal_payment_method_type = $BookingPress->bookingpress_get_settings( 'paypal_payment_method_type', 'payment_setting' );
			$paypal_client_id = $BookingPress->bookingpress_get_settings( 'paypal_client_id', 'payment_setting' );
			$paypal_client_secret = $BookingPress->bookingpress_get_settings( 'paypal_client_secret', 'payment_setting' );			
			if($paypal_payment_method_type == 'popup' && !empty($paypal_client_id) && !empty($paypal_client_secret)){				
				$bookingpress_dynamic_next_page_request_filter.='									
					var current_step_for_paypal = vm.bookingpress_current_tab;										
					if(("basic_details" == next_tab) || ("datetime" == next_tab)){	
											
						if(typeof vm.appointment_step_form_data.selected_payment_method != "undefined" && vm.appointment_step_form_data.selected_payment_method == ""){
							if(vm.show_paypal_popup_button == "true"){
								vm.show_paypal_popup_button = "false";
								var final_document_div = document.getElementById("paypal-button-container");	
								if(final_document_div){
									document.getElementById("paypal-button-container").innerHTML = "";
								}								
							}							
						}
					}
				';
			}
			
			return $bookingpress_dynamic_next_page_request_filter;
			
		}


		function bookingpress_paypal_booking_validate_complete_payment_func(){

            global $tbl_bookingpress_entries,$bookingpress_deposit_payment,$tbl_bookingpress_appointment_bookings, $wpdb, $BookingPress, $bookingpress_pro_payment, $tbl_bookingpress_payment_logs, $bookingpress_pro_payment_gateways;
            $response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
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


                    if($bookingpress_final_payable_amount != 0){



						$entry_id = ! empty( $bookingpress_final_payment_request_data['entry_id'] ) ? $bookingpress_final_payment_request_data['entry_id'] : 0;
						$bookingpress_is_cart = !empty($bookingpress_final_payment_request_data['is_cart']) ? 1 : 0;
						$currency_code                     = $bookingpress_final_payment_request_data['currency_code'];
						$bookingpress_final_payable_amount = isset( $bookingpress_final_payment_request_data['payable_amount'] ) ? $bookingpress_final_payment_request_data['payable_amount'] : 0;
					
						$bookingpress_service_name = ! empty( $bookingpress_final_payment_request_data['service_data']['bookingpress_service_name'] ) ? $bookingpress_final_payment_request_data['service_data']['bookingpress_service_name'] : __( 'Appointment Booking', 'bookingpress-appointment-booking' );

						if(!empty($entry_id)){

							$order_id = "";

							$bookingpress_payment_mode    = $BookingPress->bookingpress_get_settings('paypal_payment_mode', 'payment_setting');
							$paypal_client_id = $BookingPress->bookingpress_get_settings( 'paypal_client_id', 'payment_setting' );
							$paypal_client_secret = $BookingPress->bookingpress_get_settings( 'paypal_client_secret', 'payment_setting' );							
							
							$Sandbox = $bookingpress_payment_mode;
							$paypalClientID = $paypal_client_id;
							$paypalSecret = $paypal_client_secret;
							
							$token_url = 'https://api-m.paypal.com/v1/oauth2/token';
							$api_url = 'https://api-m.paypal.com/v2/checkout/orders';
							if ($Sandbox) {
								$token_url = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';
								$api_url = 'https://api-m.sandbox.paypal.com/v2/checkout/orders';			
							}						
							$request_args = array(
								'headers'     => array(
									'Authorization' => 'Basic ' . base64_encode($paypalClientID . ':' . $paypalSecret),
								),
								'body'        => array(
									'grant_type' => 'client_credentials',
								),
							);			
							$response_return = wp_remote_post($token_url, $request_args);
							if (is_wp_error($response_return)) {
								$error = $response_return->get_error_code() . ': ' . $response_return->get_error_message();
								$response['variant'] = 'error';
								$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
								$response['msg']     = $error;	
								wp_send_json( $response );
								die();					
							}
							
							$auth_response = json_decode(wp_remote_retrieve_body($response_return));
							if (empty($auth_response)) {
								wp_send_json( $response );
								die();									
							} else {				
								if (!empty($auth_response->access_token)) {
									$headers = array(
										'Content-Type' => 'application/json',
										'Authorization' => 'Bearer ' . $auth_response->access_token,
									);
									$body = array(
										'intent' => 'CAPTURE',												
										'purchase_units' => array(																					
											array(
												'reference_id'=> $entry_id.'|'.$bookingpress_is_cart,
												'description' => $bookingpress_service_name,
												'amount' => array(
													'currency_code' => ''.$currency_code, 
													'value' => $bookingpress_final_payable_amount, 
												),
											),
										),
									);				
									
								
									$response_return = wp_remote_post(
										$api_url,
										array(
											'method' => 'POST',
											'headers' => $headers,
											'body' => wp_json_encode($body),
										)
									);
					
					
									if (is_wp_error($response_return)) {
										$error_message = $response_return->get_error_message();
										$response['variant'] = 'error';
										$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
										$response['msg']     = "Something went wrong:";
										wp_send_json( $response );
										die();														
										
									} else {
										$response_body = wp_remote_retrieve_body($response_return);
										$order_data = json_decode($response_body, true);
										$order_id = (isset($order_data['id']))?$order_data['id']:'';						
									}
								}				
							}
							if(!empty($order_id)){
					
								$redirect_url = $bookingpress_final_payment_request_data['approved_appointment_url'];
								$bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
								if ( $bookingpress_appointment_status == '2' ) {
									$redirect_url = $bookingpress_final_payment_request_data['pending_appointment_url'];
								}

								$cancel_url = $bookingpress_final_payment_request_data['canceled_appointment_url'];					
								$booking_form_redirection_mode = !empty($bookingpress_final_payment_request_data['booking_form_redirection_mode']) ? $bookingpress_final_payment_request_data['booking_form_redirection_mode'] : 'external_redirection';
					
								$response['variant'] = 'success';
								$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
								$response['msg']     = esc_html__( 'Appointment succesfully created.', 'bookingpress-appointment-booking' );
								$response['order_id']  = $order_id;
								$response['paypal_success_url']  = $redirect_url;
								$response['paypal_cancel_url']  = $cancel_url;
								$response['paypal_booking_form_redirection_mode']  = $booking_form_redirection_mode;
					
							}	
					
						}						


					}

				}
            }
			
			
			echo wp_json_encode( $response );
			die();


		}



        function bookingpress_frontend_data_fields_for_paypal($bookingpress_front_vue_data_fields){
            global $BookingPress;            
			$bookingpress_front_vue_data_fields['show_paypal_popup_button'] = "false";
			$bookingpress_front_vue_data_fields['paypal_button_loader'] = "false";
			$bookingpress_front_vue_data_fields['paypal_success_url'] = "";
			$bookingpress_front_vue_data_fields['paypal_cancel_url'] = "";
			$bookingpress_front_vue_data_fields['paypal_booking_form_redirection_mode'] = "";
			return $bookingpress_front_vue_data_fields;
        }

		function bookingpress_paypal_payment_button_html(){
		?>
			<el-button v-if="paypal_button_loader != 'false'" class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--primary bpa-loader-button bpa-front-btn--is-loader">                
				<span class="bpa-btn__label">Test Button</span>
				<div class="bpa-front-btn--loader__circles">			    
					<div></div>
					<div></div>
					<div></div>
				</div>
			</el-button>		
			<div v-if="paypal_button_loader != 'true'" id="paypal-button-container"></div>
		<?php 
		}


		function bookingpress_after_selecting_complete_payment_method_data_func($bookingpress_after_selecting_payment_method_data){
			global $BookingPress;

			$paypal_data = $this->arm_init_paypal();
			$paypal_payment_method_type = $BookingPress->bookingpress_get_settings( 'paypal_payment_method_type', 'payment_setting' );
			$paypal_client_id = $BookingPress->bookingpress_get_settings( 'paypal_client_id', 'payment_setting' );
			$paypal_client_secret = $BookingPress->bookingpress_get_settings( 'paypal_client_secret', 'payment_setting' );

			if($paypal_payment_method_type == 'popup'){

				$bookingpress_after_selecting_payment_method_data.='
					var vm7 = this;
					if(payment_method == "paypal"){
						vm7.show_paypal_popup_button = "true";
					}else{
						vm7.show_paypal_popup_button = "false";
					}
				';

				if(empty($paypal_client_id)){					
					$client_id_error = esc_html__( 'Client ID is required.', 'bookingpress-appointment-booking' );
					$bookingpress_after_selecting_payment_method_data.='
						window.app.bookingpress_set_error_msg("'.$client_id_error.'");
					';		
				}else if(empty($paypal_client_secret)){
					$client_secret_error = esc_html__( 'Client secret is required.', 'bookingpress-appointment-booking' );
					$bookingpress_after_selecting_payment_method_data.='
						window.app.bookingpress_set_error_msg("'.$client_secret_error.'");
					';		
				}else{

					$booking_form_redirection_mode = $BookingPress->bookingpress_get_customize_settings('redirection_mode','booking_form');
					if(empty($bookingpress_redirection_mode)){
						$booking_form_redirection_mode = 'external_redirection';
					}

					$bookingpress_after_canceled_payment_page_id = $BookingPress->bookingpress_get_customize_settings( 'after_failed_payment_redirection', 'booking_form' );
					$cancel_url     = get_permalink( $bookingpress_after_canceled_payment_page_id );
					$redirect_url   = '';

					$bookingpress_after_selecting_payment_method_data.='						
						if(payment_method == "paypal"){
							vm7.show_paypal_popup_button = "true";
								var final_document_div = document.getElementById("paypal-button-container");	
								if(final_document_div){
									document.getElementById("paypal-button-container").innerHTML = "";
								}
								paypal.Buttons({
									async createOrder(data, actions) {
										var vm2 = this;											
										var bkp_wpnonce_pre = "'.wp_create_nonce( 'bpa_wp_nonce' ).'";
										var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");										
										if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null){
											bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
										}else {
											bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
										}
										var final_order_id = "";	
										var postData = { action: "bookingpress_paypal_booking_validate_complete_payment", _wpnonce: bkp_wpnonce_pre_fetch,complete_payment_data:JSON.stringify( app.appointment_step_form_data)}
										var final_data = "";
										try {
											const response = await axios.post(appoint_ajax_obj.ajax_url, Qs.stringify( postData ));											
											if(response.data.variant != "error") {												
												if(typeof response.data.order_id != "undefined" && response.data.order_id != ""){
													app.paypal_success_url = response.data.paypal_success_url;
													app.paypal_cancel_url = response.data.paypal_cancel_url;
													app.paypal_booking_form_redirection_mode = response.data.paypal_booking_form_redirection_mode;
													return response.data.order_id;												
												}	
											}else{
												window.app.bookingpress_set_error_msg(response.data.msg);
												return 0;
											}											
										} catch (error) {
											window.app.bookingpress_set_error_msg("Failed to create PayPal order");
											return 0;
										}			
									},																		
									onCancel: function(data) {
																			
									},
									onApprove: (data, actions) => {
										return actions.order.capture().then(function(orderData) {
											app.paypal_button_loader = "true";
											var bkp_wpnonce_pre = "'.wp_create_nonce( 'bpa_wp_nonce' ).'";
											var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");										
											if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null){
												bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
											}else {
												bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
											}

											var sca_confirm_booking_data = { action: "bookingpress_paypal_booking_payment_confirm", bookingpress_payment_res: orderData, _wpnonce: bkp_wpnonce_pre_fetch}											
											axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( sca_confirm_booking_data ) )
											.then(function(response) {
												setTimeout(function(){
													app.paypal_button_loader = "false";
												},500);												
												if(response.data.variant != "error") {
													window.location.href = app.paypal_success_url;													
												}else{
													window.app.bookingpress_set_error_msg(response.data.msg);
												}
											}).catch(function(error){
												setTimeout(function(){
													app.paypal_button_loader = "false";
												},500);												
												console.log(error);
											});
			
										});									
									},
									style: {
									  layout: "vertical",
									  color: "gold",									  
									  shape: "pill", 
									  label: "paypal", 
									  fundingicons: false, 
									}    
								}).render("#paypal-button-container").then(function() {
									setTimeout(function(){
									},1000);																		
								});
														

						}else{
							document.getElementById("paypal-button-container").innerHTML = "";
							vm7.show_paypal_popup_button = "false";							
						}
					';
				}


			}
			return $bookingpress_after_selecting_payment_method_data;
		}

		function bookingpress_after_selecting_payment_method_func($bookingpress_after_selecting_payment_method_data){			
			global $BookingPress;

			$paypal_data = $this->arm_init_paypal();
			$paypal_payment_method_type = $BookingPress->bookingpress_get_settings( 'paypal_payment_method_type', 'payment_setting' );
			$paypal_client_id = $BookingPress->bookingpress_get_settings( 'paypal_client_id', 'payment_setting' );
			$paypal_client_secret = $BookingPress->bookingpress_get_settings( 'paypal_client_secret', 'payment_setting' );

			if($paypal_payment_method_type == 'popup'){

				$bookingpress_after_selecting_payment_method_data.='
					var vm7 = this;
					if(payment_method == "paypal"){
						vm7.show_paypal_popup_button = "true";
					}else{
						vm7.show_paypal_popup_button = "false";
					}
				';

				if(empty($paypal_client_id)){					
					$client_id_error = esc_html__( 'Client ID is required.', 'bookingpress-appointment-booking' );
					$bookingpress_after_selecting_payment_method_data.='
						window.app.bookingpress_set_error_msg("'.$client_id_error.'");
					';		
				}else if(empty($paypal_client_secret)){
					$client_secret_error = esc_html__( 'Client secret is required.', 'bookingpress-appointment-booking' );
					$bookingpress_after_selecting_payment_method_data.='
						window.app.bookingpress_set_error_msg("'.$client_secret_error.'");
					';		
				}else{

					$booking_form_redirection_mode = $BookingPress->bookingpress_get_customize_settings('redirection_mode','booking_form');
					if(empty($bookingpress_redirection_mode)){
						$booking_form_redirection_mode = 'external_redirection';
					}

					$bookingpress_after_canceled_payment_page_id = $BookingPress->bookingpress_get_customize_settings( 'after_failed_payment_redirection', 'booking_form' );
					$cancel_url     = get_permalink( $bookingpress_after_canceled_payment_page_id );
					$redirect_url   = '';

					$bookingpress_after_selecting_payment_method_data.='						
						if(payment_method == "paypal"){
							vm7.show_paypal_popup_button = "true";
								var final_document_div = document.getElementById("paypal-button-container");	
								if(final_document_div){
									document.getElementById("paypal-button-container").innerHTML = "";
								}
								paypal.Buttons({
									async createOrder(data, actions) {
										var vm2 = this;											
										var bkp_wpnonce_pre = "'.wp_create_nonce( 'bpa_wp_nonce' ).'";
										var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");										
										if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null){
											bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
										}else {
											bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
										}

										await app.bookingpress_verify_total_payment_amount();

										var final_order_id = "";	
										var postData = { action: "bookingpress_paypal_booking_validate", _wpnonce: bkp_wpnonce_pre_fetch,appointment_data:JSON.stringify( app.appointment_step_form_data)}
										var final_data = "";
										try {
											const response = await axios.post(appoint_ajax_obj.ajax_url, Qs.stringify( postData ));											
											if(response.data.variant != "error") {												
												if(typeof response.data.order_id != "undefined" && response.data.order_id != ""){
													app.paypal_success_url = response.data.paypal_success_url;
													app.paypal_cancel_url = response.data.paypal_cancel_url;
													app.paypal_booking_form_redirection_mode = response.data.paypal_booking_form_redirection_mode;
													return response.data.order_id;												
												}	
											}else{
												window.app.bookingpress_set_error_msg(response.data.msg);
												return 0;
											}											
										} catch (error) {
											window.app.bookingpress_set_error_msg("Failed to create PayPal order");
											return 0;
										}			
									},																		
									onCancel: function(data) {
																			
									},
									onApprove: (data, actions) => {
										return actions.order.capture().then(function(orderData) {
											app.paypal_button_loader = "true";
											var bkp_wpnonce_pre = "'.wp_create_nonce( 'bpa_wp_nonce' ).'";
											var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");										
											if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null){
												bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
											}else {
												bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
											}

											var sca_confirm_booking_data = { action: "bookingpress_paypal_booking_payment_confirm", bookingpress_payment_res: orderData, _wpnonce: bkp_wpnonce_pre_fetch}											
											axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( sca_confirm_booking_data ) )
											.then(function(response) {
												setTimeout(function(){
													app.paypal_button_loader = "false";
												},500);													
												if(response.data.variant != "error") {												
													var bookingpress_redirection_mode = app.paypal_booking_form_redirection_mode;	
													if(bookingpress_redirection_mode == "in-built"){
														window.app.bookingpress_render_thankyou_content();
														var bookingpress_uniq_id = window.app.appointment_step_form_data.bookingpress_uniq_id;
														document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).style.display = "none";
														if(response.data.variant != "error"){
															document.getElementById("bpa-failed-screen-div").style.display = "none";
															document.getElementById("bpa-thankyou-screen-div").style.display = "block";
														}else{
															document.getElementById("bpa-failed-screen-div").style.display = "block";
															document.getElementById("bpa-thankyou-screen-div").style.display = "none";
														}
													}else{														
														window.location.href = app.paypal_success_url;
													}														
												}else{
													app.paypal_button_loader = "false";
													window.app.bookingpress_set_error_msg(response.data.msg);
												}
											}).catch(function(error){
												setTimeout(function(){
													app.paypal_button_loader = "false";
												},500);													
												console.log(error);
											});
			
										});									
									},
									style: {
									  layout: "vertical",
									  color: "gold", 
									  shape: "pill", 
									  label: "paypal", 
									  fundingicons: false, 
									}    
								}).render("#paypal-button-container").then(function() {
									setTimeout(function(){
									},1000);																		
								});
														

						}else{
							document.getElementById("paypal-button-container").innerHTML = "";
							vm7.show_paypal_popup_button = "false";							
						}
					';
				}


			}
			return $bookingpress_after_selecting_payment_method_data;
		}

		function bookingpress_paypal_booking_validate_func(){

            global  $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_services, $tbl_bookingpress_customer_bookings, $tbl_bookingpress_customers, $bookingpress_pro_payment_gateways, $bookingpress_debug_payment_log_id, $bookingpress_other_debug_log_id,$bookingpress_pro_appointment_bookings;
            $wpnonce               = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			$response              = array();

			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking data process starts', 'bookingpress_bookingform', $_REQUEST, $bookingpress_other_debug_log_id );

			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				echo wp_json_encode( $response );
				die();
			}
			$response['variant']       = 'error';
			$response['title']         = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']           = esc_html__( 'Something went wrong..', 'bookingpress-appointment-booking' );
			$response['is_redirect']   = 0;
			$response['redirect_data'] = '';
			$response['is_spam']       = 1;

			if( !empty( $_REQUEST['appointment_data'] ) && !is_array( $_REQUEST['appointment_data'] ) ){
				$_REQUEST['appointment_data'] = json_decode( stripslashes_deep( $_REQUEST['appointment_data'] ), true ); //phpcs:ignore
				$_REQUEST['appointment_data'] =  !empty($_REQUEST['appointment_data']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_REQUEST['appointment_data'] ) : array(); // phpcs:ignore				
				$_POST['appointment_data'] =  array_map( array( $BookingPress, 'appointment_sanatize_field'),  $_REQUEST['appointment_data'] ); //phpcs:ignore
			}
			
			$response = apply_filters( 'bookingpress_validate_spam_protection', $response, ( !empty( $_REQUEST['appointment_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['appointment_data'] ) : array() ) );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
			
			$booking_response = $bookingpress_pro_appointment_bookings->bookingpress_pro_before_book_appointment_func();
			
			if( !empty( $booking_response ) ){
				$booking_response_arr = json_decode( $booking_response, true );
				if(  !empty( $booking_response_arr['variant'] ) && 'error' == $booking_response_arr['variant'] ){
					if(!empty($booking_response_arr['msg'])) {
						$booking_response_arr['msg'] = stripslashes_deep(html_entity_decode($booking_response_arr['msg'],ENT_QUOTES));
					}				
					wp_send_json($booking_response_arr);
					die;
				}
			}
			
			$appointment_booked_successfully = $BookingPress->bookingpress_get_settings( 'appointment_booked_successfully', 'message_setting' );

			if ( ! empty( $_REQUEST ) && ! empty( $_REQUEST['appointment_data'] )  ) {
				$bookingpress_appointment_data            = array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['appointment_data'] );// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
				$bookingpress_payment_gateway             = ! empty( $bookingpress_appointment_data['selected_payment_method'] ) ? sanitize_text_field( $bookingpress_appointment_data['selected_payment_method'] ) : '';
				$bookingpress_appointment_on_site_enabled = ( sanitize_text_field( $bookingpress_appointment_data['selected_payment_method'] ) == 'onsite' ) ? 1 : 0;
				$payment_gateway                          = ( $bookingpress_appointment_on_site_enabled ) ? 'on-site' : $bookingpress_payment_gateway;
				$bookingpress_service_price = $bookingpress_total_price = 0;
				if(empty($bookingpress_appointment_data['cart_items'])){
					$bookingpress_service_price = (isset( $bookingpress_appointment_data['service_price_without_currency'] )) ? floatval( $bookingpress_appointment_data['service_price_without_currency'] ) : 0;
					$tip_amount = isset($bookingpress_appointment_data['tip_amount']) ? $bookingpress_appointment_data['tip_amount'] : 0;
					if ( $bookingpress_service_price == 0 && $tip_amount == 0) {
						$payment_gateway = ' - ';
					}
				}else{
					$bookingpress_service_price = !empty($bookingpress_appointment_data['bookingpress_cart_total']) ? $bookingpress_appointment_data['bookingpress_cart_total'] : 0;
				}

				$bookingpress_total_price = !empty($bookingpress_appointment_data['total_payable_amount']) ? $bookingpress_appointment_data['total_payable_amount'] : 0;
				$bookingpress_discount_amount = !empty($bookingpress_appointment_data['coupon_discount_amount']) ? floatval($bookingpress_appointment_data['coupon_discount_amount']) : 0;
				if($bookingpress_total_price == 0 && !empty($bookingpress_discount_amount)){
					$payment_gateway = " - ";
				}
				
				if(empty($payment_gateway)){
					$payment_gateway = apply_filters( 'bookingpress_check_for_modified_empty_payment_getway', $payment_gateway, $bookingpress_appointment_data );
				}
				
				$bookingpress_return_data = apply_filters( 'bookingpress_validate_submitted_booking_form', $payment_gateway, $bookingpress_appointment_data );
				do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking form modified data', 'bookingpress_bookingform', $bookingpress_return_data, $bookingpress_other_debug_log_id );

				$bookingpress_redirection_mode = !empty($bookingpress_return_data['booking_form_redirection_mode']) ? $bookingpress_return_data['booking_form_redirection_mode'] : 'external_redirection';
				
				$authorization_token = !empty( $bookingpress_appointment_data['authorized_token'] ) ? $bookingpress_appointment_data['authorized_token'] : '';
				
				$bookingpress_uniq_id = $bookingpress_appointment_data['bookingpress_uniq_id'];
				$authorization_time = $bookingpress_appointment_data['authorized_time'];

				$verification_token_key = 'bookingpress_verify_payment_token_' .  $bookingpress_uniq_id . '_' . $authorization_time;

				if( wp_hash( $verification_token_key ) != $authorization_token ){
					$bookingpress_invalid_token = esc_html__('Sorry! Appointment could not be processed', 'bookingpress-appointment-booking');

                    $response['variant']       = 'error';
                    $response['title']         = esc_html__('Error', 'bookingpress-appointment-booking');
                    $response['msg']           = $bookingpress_invalid_token;
                    $response['is_redirect']   = 0;
                    $response['reason']        = 'token mismatched ' . $authorization_token . ' --- ' . wp_hash( $verification_token ) . ' --- ' . $verification_token_key;
                    $response['redirect_data'] = '';
                    $response['is_spam']       = 0;
                    echo json_encode($response);
                    exit;
				}
							

				$bookingpress_total_payment_price = get_transient( $authorization_token );

				if( false !== $bookingpress_total_payment_price && $bookingpress_total_payment_price != $bookingpress_total_price ){
					$bookingpress_invalid_amount = esc_html__('Sorry! Appointment could not be processed', 'bookingpress-appointment-booking');

                    $response['variant']       = 'error';
                    $response['title']         = esc_html__('Error', 'bookingpress-appointment-booking');
                    $response['msg']           = $bookingpress_invalid_amount;
                    $response['is_redirect']   = 0;
					$response['bookingpress_total_payment_price'] = $bookingpress_total_payment_price;
					$response['bookingpress_total_price'] = $bookingpress_total_price;
                    $response['reason']        = 'price mismatched ' . $bpa_service_amount . ' --- ' . $bookingpress_service_price;
                    $response['redirect_data'] = '';
                    $response['is_spam']       = 0;
                    echo json_encode($response);
                    exit;
				}
				$entry_id = ! empty( $bookingpress_return_data['entry_id'] ) ? $bookingpress_return_data['entry_id'] : 0;
				$bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;
                $currency_code                     = $bookingpress_return_data['currency_code'];
                $bookingpress_final_payable_amount = isset( $bookingpress_return_data['payable_amount'] ) ? $bookingpress_return_data['payable_amount'] : 0;

				$bookingpress_service_name = ! empty( $bookingpress_return_data['service_data']['bookingpress_service_name'] ) ? $bookingpress_return_data['service_data']['bookingpress_service_name'] : __( 'Appointment Booking', 'bookingpress-appointment-booking' );

				if(!empty($entry_id)){

					$order_id = "";

					$paypal_data = $this->arm_init_paypal();
					$Sandbox = $this->bookingpress_is_sandbox_mode;
					$paypalClientID = $this->paypal_client_id;
					$paypalSecret = $this->paypal_client_secret;
					$token_url = 'https://api-m.paypal.com/v1/oauth2/token';
					$api_url = 'https://api-m.paypal.com/v2/checkout/orders';
					if ($Sandbox) {
						$token_url = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';
						$api_url = 'https://api-m.sandbox.paypal.com/v2/checkout/orders';			
					}						
					$request_args = array(
						'headers'     => array(
							'Authorization' => 'Basic ' . base64_encode($paypalClientID . ':' . $paypalSecret),
						),
						'body'        => array(
							'grant_type' => 'client_credentials',
						),
					);			
					$response_return = wp_remote_post($token_url, $request_args);
					if (is_wp_error($response_return)) {
						$error = $response_return->get_error_code() . ': ' . $response_return->get_error_message();
						$response['variant'] = 'error';
						$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
						$response['msg']     = $error;	
						wp_send_json( $response );
						die();					
					}
					
					$auth_response = json_decode(wp_remote_retrieve_body($response_return));
					if (empty($auth_response)) {
						wp_send_json( $response );
						die();									
					} else {				
						if (!empty($auth_response->access_token)) {
							$headers = array(
								'Content-Type' => 'application/json',
								'Authorization' => 'Bearer ' . $auth_response->access_token,
							);
							$body = array(
								'intent' => 'CAPTURE',												
								'purchase_units' => array(																					
									array(
										'reference_id'=> $entry_id.'|'.$bookingpress_is_cart,
										'description' => $bookingpress_service_name,
										'amount' => array(
											'currency_code' => ''.$currency_code, 
											'value' => $bookingpress_final_payable_amount, 
										),
									),
								),
							);				
							
						
							$response_return = wp_remote_post(
								$api_url,
								array(
									'method' => 'POST',
									'headers' => $headers,
									'body' => wp_json_encode($body),
								)
							);
		
		
							if (is_wp_error($response_return)) {
								$error_message = $response_return->get_error_message();
								$response['variant'] = 'error';
								$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
								$response['msg']     = "Something went wrong:";
								wp_send_json( $response );
								die();														
								
							} else {
								$response_body = wp_remote_retrieve_body($response_return);
								$order_data = json_decode($response_body, true);
								$order_id = (isset($order_data['id']))?$order_data['id']:'';						
							}
						}				
					}
					if(!empty($order_id)){

						$redirect_url = $bookingpress_return_data['approved_appointment_url'];
						$bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
						if ( $bookingpress_appointment_status == '2' ) {
							$redirect_url = $bookingpress_return_data['pending_appointment_url'];
						}
		
						$cancel_url = $bookingpress_return_data['canceled_appointment_url'];
		
						$booking_form_redirection_mode = !empty($bookingpress_return_data['booking_form_redirection_mode']) ? $bookingpress_return_data['booking_form_redirection_mode'] : 'external_redirection';

						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( 'Appointment successfully created.', 'bookingpress-appointment-booking' );
						$response['order_id']  = $order_id;
						$response['paypal_success_url']  = $redirect_url;
						$response['paypal_cancel_url']  = $cancel_url;
						$response['paypal_booking_form_redirection_mode']  = $booking_form_redirection_mode;

					}	

				}



			}			


			

		
			wp_send_json( $response );
			die();			

		}

		/**
		 * Add payment gateway to revenue filter list of Report module
		 *
		 * @param  mixed $bookingpress_revenue_filter_payment_gateway_list
		 * @return void
		 */
		function bookingpress_revenue_filter_payment_gateway_list_add_func($bookingpress_revenue_filter_payment_gateway_list){
			global $BookingPress;

			$bookingpress_is_paypal_enabled = $BookingPress->bookingpress_get_settings('paypal_payment', 'payment_setting');
			if($bookingpress_is_paypal_enabled == '1'){
				$bookingpress_revenue_filter_payment_gateway_list[] = array(
					'value' => 'paypal',
					'text' => esc_html__('PayPal', 'bookingpress-appointment-booking')
				);
			}

			return $bookingpress_revenue_filter_payment_gateway_list;
		}
		
		/**
		 * Initialize paypal configuration
		 *
		 * @return void
		 */
		function arm_init_paypal() {
			$PayPal = false;
			if ( file_exists( BOOKINGPRESS_PRO_LIBRARY_DIR . '/paypal/paypal.class.php' ) ) {
				require_once BOOKINGPRESS_PRO_LIBRARY_DIR . '/paypal/paypal.class.php';

				global $wpdb, $BookingPress;

				$this->bookingpress_payment_mode    = $BookingPress->bookingpress_get_settings( 'paypal_payment_mode', 'payment_setting' );
				$this->bookingpress_is_sandbox_mode = ( $this->bookingpress_payment_mode != 'live' ) ? true : false;
				$this->bookingpress_gateway_status  = $BookingPress->bookingpress_get_settings( 'paypal_payment', 'payment_setting' );
				$this->bookingpress_merchant_email  = $BookingPress->bookingpress_get_settings( 'paypal_merchant_email', 'payment_setting' );
				$this->bookingpress_api_username    = $BookingPress->bookingpress_get_settings( 'paypal_api_username', 'payment_setting' );
				$this->bookingpress_api_password    = $BookingPress->bookingpress_get_settings( 'paypal_api_password', 'payment_setting' );
				$this->bookingpress_api_signature   = $BookingPress->bookingpress_get_settings( 'paypal_api_signature', 'payment_setting' );

				$this->paypal_payment_method_type    = $BookingPress->bookingpress_get_settings( 'paypal_payment_method_type', 'payment_setting' );
				$this->paypal_client_id = $BookingPress->bookingpress_get_settings( 'paypal_client_id', 'payment_setting' );
				$this->paypal_client_secret = $BookingPress->bookingpress_get_settings( 'paypal_client_secret', 'payment_setting' );

				$PayPalConfig = array(
					'Sandbox'      => $this->bookingpress_is_sandbox_mode,
					'APIUsername'  => $this->bookingpress_api_username,
					'APIPassword'  => $this->bookingpress_api_password,
					'APISignature' => $this->bookingpress_api_signature,
				);

				$PayPal = new PayPal( $PayPalConfig );
			}

			return $PayPal;
		}
		
		function get_access_token($client_id, $client_secret) {


		
			return $response_data['access_token'];
		}

		public function validate_paypal_order($order_id){ 

			$paypal_data = $this->arm_init_paypal();

			$paypalAuthAPI = 'https://api-m.paypal.com/v1/oauth2/token';
			$paypalAPI = 'https://api-m.paypal.com/v2/checkout';

			$Sandbox = $this->bookingpress_is_sandbox_mode;
			$paypalClientID = $this->paypal_client_id;
			$paypalSecret = $this->paypal_client_secret;
			
			if ($Sandbox) {
				$paypalAuthAPI = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';
				$paypalAPI = 'https://api-m.sandbox.paypal.com/v2/checkout';
			}
			
			$request_args = array(
				'headers'     => array(
					'Authorization' => 'Basic ' . base64_encode($paypalClientID . ':' . $paypalSecret),
				),
				'body'        => array(
					'grant_type' => 'client_credentials',
				),
			);
			
			$response = wp_remote_post($paypalAuthAPI, $request_args);
			
			if (is_wp_error($response)) {
				throw new Exception('Error ' . $response->get_error_code() . ': ' . $response->get_error_message());
			}
			
			$auth_response = json_decode(wp_remote_retrieve_body($response));
			
			if (empty($auth_response)) {
				return false;
			} else {
				if (!empty($auth_response->access_token)) {
					$request_args = array(
						'headers'     => array(
							'Authorization' => 'Bearer ' . $auth_response->access_token,
						),
					);
			
					$response = wp_remote_get($paypalAPI . '/orders/' . $order_id, $request_args);
			
					if (is_wp_error($response)) {
						throw new Exception('Error ' . $response->get_error_code() . ': ' . $response->get_error_message());
					}
			
					$api_data = json_decode(wp_remote_retrieve_body($response), true);
			
					if (!empty($api_data['error'])) {
						throw new Exception('Error ' . $api_data['error'] . ': ' . $api_data['error_description']);
					}
			
					return !empty($api_data) ? $api_data : false;
				} else {
					return false;
				}
			}			

		} 

        function bookingpress_paypal_booking_payment_confirm(){

            global  $BookingPress,$bookingpress_debug_payment_log_id,$bookingpress_pro_payment_gateways;
            $wpnonce               = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			$response              = array();
			
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Sorry, payment is not successed with the paypal.', 'bookingpress-appointment-booking' );

			$bookingpress_payment_res = (isset($_POST['bookingpress_payment_res']))?$_POST['bookingpress_payment_res']:'';
			do_action( 'bookingpress_payment_log_entry', 'paypal', 'payment popup response data', 'bookingpress pro', $_POST, $bookingpress_debug_payment_log_id );
            if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not process due to security reason.', 'bookingpress-appointment-booking' );
				wp_send_json( $response );
				die();
			}
			$paypal_data = $this->arm_init_paypal();
			$order_id = (isset($bookingpress_payment_res['id']))?$bookingpress_payment_res['id']:'';
			$order = "";
			if(!empty($order_id)){
				try {  
					$order = $this->validate_paypal_order($order_id); 
				} catch(Exception $e) {  
					$api_error = $e->getMessage();  
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = $api_error;
					wp_send_json( $response );
					die(); 				
				}
	
				$reference_id = (isset($order['purchase_units'][0]['reference_id']))?$order['purchase_units'][0]['reference_id']:'';
				$order_status = (isset($order['status']))?$order['status']:'';
				$transaction_id  =  (isset($order['purchase_units'][0]['payments']['captures'][0]['id']))?$order['purchase_units'][0]['payments']['captures'][0]['id']:'';
				$payment_status = (isset($order['purchase_units'][0]['payments']['captures'][0]['status']))?$order['purchase_units'][0]['payments']['captures'][0]['status']:'';
				$amount = (isset($order['purchase_units'][0]['amount']['value']))?$order['purchase_units'][0]['amount']['value']:'';
				$currency_code = (isset($order['purchase_units'][0]['amount']['currency_code']))?$order['purchase_units'][0]['amount']['currency_code']:'';
	
				if(!empty($reference_id)){
	
					$custom_var_arr = explode('|', $reference_id);
					$entry_id = !empty($custom_var_arr[0]) ? intval($custom_var_arr[0]) : 0;
					$bookingpress_is_cart = !empty($custom_var_arr[1]) ? intval($custom_var_arr[1]):0;
	
					if(!empty($order_id) && $order_status == 'COMPLETED' && !empty($entry_id)){
	
						$bookingpress_webhook_data = array();
						$payer_email = (isset($order['payer']['email_address']))?$order['payer']['email_address']:'';
						$bookingpress_webhook_data['bookingpress_payer_email'] = $payer_email;
						$bookingpress_webhook_data['txn_id'] = $transaction_id;
						$bookingpress_webhook_data['amt'] = $amount;
						$bookingpress_webhook_data['amount'] = $amount;
						$bookingpress_webhook_data['currency'] = $currency_code;
	
						$payment_add_status = '1';
						if($payment_status == "PENDING"){
							$payment_add_status = '2';
						}
	
	
						$bookingpress_pro_payment_gateways->bookingpress_confirm_booking( (int)$entry_id, $bookingpress_webhook_data, $payment_add_status, 'txn_id', 'amt', 1, $bookingpress_is_cart,'currency');
	
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( 'Appointment succesfully created.', 'bookingpress-appointment-booking' );
	
						wp_send_json( $response );
						die();
					}
	
				}
	
			}
			echo json_encode($response);
			die;

        }

		/**
		 * Function for submit request to payment gateway.
		 *
		 * @param  mixed $return_response
		 * @param  mixed $bookingpress_return_data
		 * @return void
		 */
		function bookingpress_submit_form_data( $return_response, $bookingpress_return_data ) {
			if ( ! empty( $bookingpress_return_data ) ) {
				global $BookingPress,$bookingpress_debug_payment_log_id;

				$paypal_data = $this->arm_init_paypal();

				$entry_id                          = $bookingpress_return_data['entry_id'];
				$currency                          = $bookingpress_return_data['currency'];
				$currency_symbol                   = $BookingPress->bookingpress_get_currency_code( $currency );
				$bookingpress_final_payable_amount = isset( $bookingpress_return_data['payable_amount'] ) ? $bookingpress_return_data['payable_amount'] : 0;
				$customer_details                  = $bookingpress_return_data['customer_details'];
				$customer_email                    = ! empty( $customer_details['customer_email'] ) ? $customer_details['customer_email'] : '';

				$bookingpress_service_name = ! empty( $bookingpress_return_data['service_data']['bookingpress_service_name'] ) ? $bookingpress_return_data['service_data']['bookingpress_service_name'] : __( 'Appointment Booking', 'bookingpress-appointment-booking' );

				$bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;
				$custom_var = $entry_id."|".$bookingpress_is_cart;

				$sandbox = $this->bookingpress_is_sandbox_mode ? 'sandbox.' : '';

				$notify_url = $bookingpress_return_data['notify_url'];

				$redirect_url = $bookingpress_return_data['approved_appointment_url'];
				$bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
				if ( $bookingpress_appointment_status == '2' ) {
					$redirect_url = $bookingpress_return_data['pending_appointment_url'];
				}

				$cancel_url = $bookingpress_return_data['canceled_appointment_url'];

				$booking_form_redirection_mode = !empty($bookingpress_return_data['booking_form_redirection_mode']) ? $bookingpress_return_data['booking_form_redirection_mode'] : 'external_redirection';
		
				$cmd          = '_xclick';
				$paypal_form  = '<form name="_xclick" id="bookingpress_paypal_form" action="https://www.' . $sandbox . 'paypal.com/cgi-bin/webscr" method="post">';
				$paypal_form .= '<input type="hidden" name="cmd" value="' . $cmd . '" />';
				$paypal_form .= '<input type="hidden" name="amount" value="' . $bookingpress_final_payable_amount . '" />';
				$paypal_form .= '<input type="hidden" name="business" value="' . $this->bookingpress_merchant_email . '" />';
				$paypal_form .= '<input type="hidden" name="notify_url" value="' . $notify_url . '" />';
				$paypal_form .= '<input type="hidden" name="cancel_return" value="' . $cancel_url . '" />';
				$paypal_form .= '<input type="hidden" name="return" value="' . $redirect_url . '" />';
				$paypal_form .= '<input type="hidden" name="rm" value="2" />';
				$paypal_form .= '<input type="hidden" name="lc" value="en_US" />';
				$paypal_form .= '<input type="hidden" name="no_shipping" value="1" />';
				$paypal_form .= '<input type="hidden" name="custom" value="' . $custom_var . '" />';
				$paypal_form .= '<input type="hidden" name="on0" value="user_email" />';
				$paypal_form .= '<input type="hidden" name="os0" value="' . $customer_email . '" />';
				$paypal_form .= '<input type="hidden" name="currency_code" value="' . $currency_symbol . '" />';
				$paypal_form .= '<input type="hidden" name="page_style" value="primary" />';
				$paypal_form .= '<input type="hidden" name="charset" value="UTF-8" />';
				$paypal_form .= '<input type="hidden" name="item_name" value="' . $bookingpress_service_name . '" />';
				$paypal_form .= '<input type="hidden" name="item_number" value="1" />';
				$paypal_form .= '<input type="submit" value="Pay with PayPal!" />';
				$paypal_form .= '</form>';				
								
				do_action( 'bookingpress_payment_log_entry', 'paypal', 'payment form redirected data', 'bookingpress pro', $paypal_form, $bookingpress_debug_payment_log_id );

				$paypal_form .= '<script type="text/javascript">window.app.bookingpress_is_display_external_html = false; document.getElementById("bookingpress_paypal_form").submit();</script>';

				$return_response['variant']       = 'redirect';
				$return_response['title']         = '';
				$return_response['msg']           = '';
				$return_response['is_redirect']   = 1;
				$return_response['redirect_data'] = $paypal_form;
				if($booking_form_redirection_mode == "in-built"){
					$return_response['is_transaction_completed'] = 1;
				}
				$return_response['entry_id']      = $entry_id;
			}
			return $return_response;
		}
		
		/**
		 * Paypal webhook URL handle function
		 *
		 * @return void
		 */
		function bookingpress_payment_gateway_data() {
			global $wpdb, $BookingPress, $bookingpress_pro_payment_gateways, $bookingpress_debug_payment_log_id;
			if ( ! empty( $_REQUEST['bookingpress-listener'] ) && ( $_REQUEST['bookingpress-listener'] == 'bpa_pro_paypal_url' ) ) {
				$bookingpress_webhook_data = $_REQUEST;
				do_action( 'bookingpress_payment_log_entry', 'paypal', 'Paypal Webhook Data', 'bookingpress pro', $bookingpress_webhook_data, $bookingpress_debug_payment_log_id );
				if ( ! empty( $bookingpress_webhook_data ) && !empty($_POST['txn_id']) && ! empty( $bookingpress_webhook_data['custom'] ) ) { // phpcs:ignore
					$req = 'cmd=_notify-validate';
                    foreach ($_POST as $key => $value) { // phpcs:ignore
                        $value = urlencode(stripslashes($value));
                        $req .= "&$key=$value";
                    }

                    $request = new WP_Http();
                    /* For HTTP1.0 Request */
                    $requestArr = array(
                        "sslverify" => false,
                        "ssl" => true,
                        "body" => $req,
                        "timeout" => 20,
                    );
                    /* For HTTP1.1 Request */
                    $requestArr_1_1 = array(
                        "httpversion" => '1.1',
                        "sslverify" => false,
                        "ssl" => true,
                        "body" => $req,
                        "timeout" => 20,
                    );
                    $response = array();

                    $bookingpress_payment_mode    = $BookingPress->bookingpress_get_settings('paypal_payment_mode', 'payment_setting');
                    $bookingpress_is_sandbox_mode = ( $bookingpress_payment_mode != 'live' ) ? true : false;

                    if($bookingpress_is_sandbox_mode){
                        $url = "https://www.sandbox.paypal.com/cgi-bin/webscr/";
                        $response_1_1 = $request->post($url, $requestArr_1_1);

                        if (!is_wp_error($response_1_1) && $response_1_1['body'] == 'VERIFIED') {
                            $response = $response_1_1;
                        } else {
                            $response = $request->post($url, $requestArr);
                        }  
                    }else{
                        $url = "https://www.paypal.com/cgi-bin/webscr/";
                        $response_1_0 = $request->post($url, $requestArr);
                        if (!is_wp_error($response_1_0) && $response_1_0['body'] == 'VERIFIED') {
                            $response = $response_1_0;
                        } else {
                            $response = $request->post($url, $requestArr_1_1);
                        }
                    }

                    do_action('bookingpress_payment_log_entry', 'paypal', 'PayPal Webhook Verified Data', 'bookingpress pro', $response, $bookingpress_debug_payment_log_id);

					if (!is_wp_error($response) && $response['body'] == 'VERIFIED' && !empty($_POST['txn_type']) && ($_POST['txn_type'] == 'web_accept') ) { // phpcs:ignore
						$custom_var       = $bookingpress_webhook_data['custom'];
						$custom_var_arr = explode('|', $custom_var);
						$entry_id = !empty($custom_var_arr[0]) ? intval($custom_var_arr[0]) : 0;
						$bookingpress_is_cart = !empty($custom_var_arr[1]) ? intval($custom_var_arr[1]) : 0;
						$payment_status = ! empty( $bookingpress_webhook_data['payment_status'] ) ? strtolower(sanitize_text_field( $bookingpress_webhook_data['payment_status'] )) : '1';					
						
						if($payment_status == 'Completed'){
			                            $payment_status = '1';
			                        }else if($payment_status == 'Pending'){
			                            $payment_status = '2';
			                        }else{
			                            $payment_status = '1';
			                        }

						$payer_email    = ! empty( $bookingpress_webhook_data['payer_email'] ) ? sanitize_email( $bookingpress_webhook_data['payer_email'] ) : '';
						$bookingpress_webhook_data['bookingpress_payer_email'] = $payer_email;
						$bookingpress_webhook_data                             = array_map( array( $BookingPress, 'appointment_sanatize_field' ), $bookingpress_webhook_data );
						$payment_log_id                                        = $bookingpress_pro_payment_gateways->bookingpress_confirm_booking( $entry_id, $bookingpress_webhook_data, $payment_status, 'txn_id', '', 1, $bookingpress_is_cart );
					}
				}
			}
		}
		
		/**
		 * Function for get paypal access token
		 *
		*/
		function get_paypal_access_token() {
			$client_id = 'YOUR_CLIENT_ID';
			$client_secret = 'YOUR_CLIENT_SECRET';
		
			$response = wp_remote_post( 'https://api.sandbox.paypal.com/v1/oauth2/token', array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ),
					'Accept'        => 'application/json',
					'Accept-Language' => 'en_US',
				),
				'body'    => 'grant_type=client_credentials',
			) );
		
			if ( is_wp_error( $response ) ) {
				return false;
			}
		
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body );
		
			return $data->access_token;
		}

		/**
		 * bookingpress_paypal_apply_refund_func
		 *
		 * @return void
		 */
		function bookingpress_paypal_apply_refund_func($response,$bookingpress_refund_data) {
			global $bookingpress_debug_payment_log_id,$BookingPress;

		   $bookingpress_transaction_id = !empty($bookingpress_refund_data['bookingpress_transaction_id']) ? $bookingpress_refund_data['bookingpress_transaction_id'] :'';

		   
		


		   if(!empty($bookingpress_transaction_id ) && !empty($bookingpress_refund_data['refund_type'])) {						   
			   try{
					$payment_data = $this->arm_init_paypal();					
					$bookingpress_send_refund_data = array(
						'RTFields' => array(
							'TRANSACTIONID' => $bookingpress_transaction_id,
						),
					);
					$bookingpres_refund_type = $bookingpress_refund_data['refund_type'] ? $bookingpress_refund_data['refund_type'] : '';
					if($bookingpres_refund_type != 'full') {                    
						$bookingpres_refund_amount = $bookingpress_refund_data['refund_amount'] ? $bookingpress_refund_data['refund_amount'] : 0;
						$bookingpress_send_refund_data['RTFields']['AMT'] = ((float)$bookingpres_refund_amount);
						$bookingpress_send_refund_data['RTFields']['REFUNDTYPE'] = ucfirst($bookingpres_refund_type);
					}
					do_action( 'bookingpress_payment_log_entry', 'paypal', 'Paypal submited refund data', 'bookingpress pro', $bookingpress_send_refund_data, $bookingpress_debug_payment_log_id );

					$bookingpress_payment_mode    = $BookingPress->bookingpress_get_settings('paypal_payment_mode', 'payment_setting');
					$paypal_payment_method_type = $BookingPress->bookingpress_get_settings( 'paypal_payment_method_type', 'payment_setting' );
					$paypal_client_id = $BookingPress->bookingpress_get_settings( 'paypal_client_id', 'payment_setting' );
					$paypal_client_secret = $BookingPress->bookingpress_get_settings( 'paypal_client_secret', 'payment_setting' );

					if($paypal_payment_method_type == 'popup'){

							$Sandbox = $bookingpress_payment_mode;
							$paypalClientID = $paypal_client_id;
							$paypalSecret = $paypal_client_secret;
							
							$token_url = 'https://api-m.paypal.com/v1/oauth2/token';
							$api_url = 'https://api-m.paypal.com/v2/payments/captures/'.$bookingpress_transaction_id.'/refund';
							if ($Sandbox) {
								$token_url = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';
								$api_url = 'https://api-m.sandbox.paypal.com/v2/payments/captures/'.$bookingpress_transaction_id.'/refund';			
							}						
							$request_args = array(
								'headers'     => array(
									'Authorization' => 'Basic ' . base64_encode($paypalClientID . ':' . $paypalSecret),
								),
								'body'        => array(
									'grant_type' => 'client_credentials',
								),
							);			
							$response_return = wp_remote_post($token_url, $request_args);
							if (is_wp_error($response_return)) {
								$error = $response_return->get_error_code() . ': ' . $response_return->get_error_message();
								$response['variant'] = 'error';
								$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
								$response['msg']     = $error;	
								return $response;
												
							}
							$response_return = wp_remote_post($token_url, $request_args);
							if (is_wp_error($response_return)) {
								$error = $response_return->get_error_code() . ': ' . $response_return->get_error_message();
								$response['variant'] = 'error';
								$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
								$response['msg']     = $error;	
								return $response;
													
							}
							
							$auth_response = json_decode(wp_remote_retrieve_body($response_return));
							if (empty($auth_response)) {
								$error = esc_html__( 'Access Token Not Valid', 'bookingpress-appointment-booking' );
								$response['variant'] = 'error';
								$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
								$response['msg']     = $error;									
								return $response;														
							} else {
								if (!empty($auth_response->access_token)) {

									$bookingpress_payment_currency = (isset($bookingpress_refund_data['bookingpress_payment_currency']))?$bookingpress_refund_data['bookingpress_payment_currency']:'';
									$bookingpres_refund_amount = $bookingpress_refund_data['refund_amount'] ? $bookingpress_refund_data['refund_amount'] : 0;
									$headers = array(
										'Content-Type' => 'application/json',
										'Authorization' => 'Bearer ' . $auth_response->access_token,
									);
									$body = array(
										'amount' => array(
											'currency_code' => ''.$bookingpress_payment_currency, 
											'value' => $bookingpres_refund_amount, 
										),
									);				
																	
									$response_return = wp_remote_post(
										$api_url,
										array(
											'method' => 'POST',
											'headers' => $headers,
											'body' => wp_json_encode($body),
										)
									);		
									
									if (is_wp_error($response_return)) {
										$error_message = $response_return->get_error_message();
										$response['variant'] = 'error';
										$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
										$response['msg']     = "Something went wrong:";
										return $response;														
										
									} else {
										$response_body = wp_remote_retrieve_body($response_return);
										$order_data = json_decode($response_body, true);
										$refund_status = (isset($order_data['status']))?$order_data['status']:'';						
										if($refund_status == "COMPLETED"){
											$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
											$response['variant'] = 'success';
											$response['bookingpress_refund_response'] = !empty($order_data) ? $order_data : '';											
										}else{
											$message = (isset($order_data['message']))?$order_data['message']:'';
											if(empty($message)){
												$message = esc_html__('Sorry! refund could not be processed', 'bookingpress-appointment-booking');
											}
											$response['variant'] = 'error';
											$response['title']  = esc_html__( 'Error', 'bookingpress-appointment-booking' );
											$response['msg'] =  $message;				
										}
									}
								}else{
									$error = esc_html__( 'Access Token Not Valid', 'bookingpress-appointment-booking' );
									$response['variant'] = 'error';
									$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
									$response['msg']     = $error;									
									return $response;									
								}
							}				
					}else{
						$bookingpress_create_refund_response = $payment_data->RefundTransaction($bookingpress_send_refund_data);

						do_action( 'bookingpress_payment_log_entry', 'paypal', 'Paypal response of the refund', 'bookingpress pro', $bookingpress_create_refund_response, $bookingpress_debug_payment_log_id);
	
						if(!is_wp_error( $bookingpress_create_refund_response ) && !empty($bookingpress_create_refund_response['REFUNDTRANSACTIONID']) && !empty( $bookingpress_create_refund_response['ACK'] ) && 'success' == strtolower( $bookingpress_create_refund_response['ACK'] ) ) {
							$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
							$response['variant'] = 'success';
							$response['bookingpress_refund_response'] = !empty($bookingpress_create_refund_response) ? $bookingpress_create_refund_response : '';
						} else {
							$response['variant'] = 'error';
							$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
							$response['msg'] = !empty( $bookingpress_create_refund_response['L_LONGMESSAGE0'] ) ? esc_html__('Error Code', 'bookingpress-appointment-booking').':'.$bookingpress_create_refund_response['L_ERRORCODE0'] . ' '. $bookingpress_create_refund_response['L_LONGMESSAGE0'] : esc_html__('Sorry! refund could not be processed', 'bookingpress-appointment-booking');
						}

					}

					




			  } catch (Exception $e){
				   $error_message = $e->getMessage();
				   do_action( 'bookingpress_payment_log_entry', 'paypal', 'Paypal refund resoponse with error', 'bookingpress pro', $error_message, $bookingpress_debug_payment_log_id);                    
				   $response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				   $response['variant'] = 'error';
				   $response['msg'] = $error_message;
			  }
		   }            
		   return 	$response;
	   }
	}

	global $bookingpress_paypal;
	$bookingpress_paypal = new bookingpress_paypal();
}
