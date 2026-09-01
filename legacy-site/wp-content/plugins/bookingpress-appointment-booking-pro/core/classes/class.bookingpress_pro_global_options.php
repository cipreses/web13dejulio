<?php
if ( ! class_exists( 'BookingPress_Pro_Global_Options' ) ) {
	class BookingPress_Pro_Global_Options Extends BookingPress_Core {
		function __construct() {
			add_filter( 'bookingpress_add_global_option_data', array( $this, 'bookingpress_add_global_option_data_func' ), 10 );
		}

		function bookingpress_add_global_option_data_func( $global_data ) {
			global $wpdb, $BookingPress, $tbl_bookingpress_form_fields;

			$bookingpress_global_appointment_status = !empty($global_data['appointment_status']) ? $global_data['appointment_status'] : array();
			if(!empty($bookingpress_global_appointment_status)){
				array_push($bookingpress_global_appointment_status, array('value' => '5', 'text' => esc_html__('No-Show', 'bookingpress-appointment-booking')));
				array_push($bookingpress_global_appointment_status, array('value' => '6', 'text' => esc_html__('Completed', 'bookingpress-appointment-booking')));
				$global_data['appointment_status'] = $bookingpress_global_appointment_status;
			}

			$bookingpress_global_payment_status = !empty($global_data['payment_status']) ? $global_data['payment_status'] : array();
			if(!empty($bookingpress_global_payment_status)){
				array_push($bookingpress_global_payment_status, array('value' => '3', 'text' => esc_html__('Refunded', 'bookingpress-appointment-booking')));
				array_push($bookingpress_global_payment_status, array('value' => '4', 'text' => esc_html__('Partially Paid', 'bookingpress-appointment-booking')));
				array_push($bookingpress_global_payment_status, array('value' => '5', 'text' => esc_html__('Refunded ( partial )', 'bookingpress-appointment-booking')));
				$global_data['payment_status'] = $bookingpress_global_payment_status;
			}

			$set_customfield_placeholder = wp_cache_get('set_customfield_placeholder');

			if($set_customfield_placeholder === false) {

			$bookingpress_custom_form_fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_is_default = %d AND bookingpress_is_customer_field = %d AND bookingpress_field_type != %s AND bookingpress_field_type != %s AND bookingpress_field_type != %s", 0,0,'2_col','3_col','4_col'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
			wp_cache_set('set_customfield_placeholder',$bookingpress_custom_form_fields);

			}
			else
			{
				$bookingpress_custom_form_fields = $set_customfield_placeholder;
			}

			$bookingpress_custom_field_metakeys = array();
			if(!empty($bookingpress_custom_form_fields)){
				foreach($bookingpress_custom_form_fields as $k => $v){					
					if($v['bookingpress_form_field_name'] != 'Password' ){
					$bookingpress_custom_field_metakeys[] = array(
						'value' => "%".$v['bookingpress_field_meta_key']."%",
						'name' => $v['bookingpress_field_label'],
					);					
					}
				}
			}

			$global_data['custom_fields_placeholders'] = wp_json_encode($bookingpress_custom_field_metakeys);
			
			$data = array(
				'staff_member_placeholders' => wp_json_encode(
					array(
						array(
							'value' => '%staff_email%',
							'name'  => '%staff_email%',
						),
						array(
							'value' => '%staff_first_name%',
							'name'  => '%staff_first_name%',
						),
						array(
							'value' => '%staff_full_name%',
							'name'  => '%staff_full_name%',
						),
						array(
							'value' => '%staff_last_name%',
							'name'  => '%staff_last_name%',
						),
						array(
							'value' => '%staff_phone%',
							'name'  => '%staff_phone%',
						),
					)
				),
				'appointment_placeholders'  => wp_json_encode(
					array(
						array(
							'value' => '%appointment_date%',
							'name'  => '%appointment_date%',
						),
						array(
							'value' => '%appointment_time%',
							'name'  => '%appointment_time%',
						),
						array(
							'value' => '%appointment_date_time%',
							'name'  => '%appointment_date_time%',
						),
						array(
							'value' => '%appointment_duration%',
							'name'  => '%appointment_duration%',
						),
						array(
							'value' => '%appointment_start_time%',
							'name'  => '%appointment_start_time%',
						),
						array(
							'value' => '%appointment_end_time%',
							'name'  => '%appointment_end_time%',
						),
						array(
							'value' => '%appointment_amount%',
							'name'  => '%appointment_amount%',
						),
						array(
							'value' => '%appointment_due_amount%',
							'name'  => '%appointment_due_amount%',
						),
						array(
							'value' => '%number_of_person%',
							'name'  => '%number_of_person%',
						),
						array(
							'value' => '%tax_amount%',
							'name'  => '%tax_amount%',
						),
						array(
							'value' => '%discount_amount%',
							'name'  => '%discount_amount%',
						),
						array(
							'value' => '%coupon_code%',
							'name'  => '%coupon_code%',
						),
						array(
							'value' => '%appointment_status%',
							'name'  => '%appointment_status%',
						),
						array(
							'value' => '%booking_id%',
							'name'  => '%booking_id%',
						),
						array(
							'value' => '%payment_method%',
							'name'  => '%payment_method%',
						),
						array(
							'value' => '%refund_amount%',
							'name'  => '%refund_amount%',
						),
						array(
							'value' => '%share_appointment_url%',
							'name'  => '%share_appointment_url%',
						),
						array(
							'value' => '%complete_payment_url%',
							'name'  => '%complete_payment_url%',
						),
						array(
							'value' => '%reschedule_appointment_url%',
							'name'  => '%reschedule_appointment_url%',
						),						
					)
				),
			);
			$global_data = array_merge( $global_data, $data );

			$global_data['staffmember_default_cap'] = array(
				'bookingpress',
				'bookingpress_calendar',
				'bookingpress_appointments',
				'bookingpress_payments',
				'bookingpress_customers',
				//'bookingpress_add_appointments',
				'bookingpress_edit_appointments',
				'bookingpress_delete_appointments',
				'bookingpress_export_appointments',
				//'bookingpress_add_customers',
				'bookingpress_edit_customers',
				'bookingpress_delete_customers',
				'bookingpress_export_customers',
				'bookingpress_edit_payments',
				'bookingpress_delete_payments',
				'bookingpress_export_payments',
				'bookingpress_edit_daysoffs',
				'bookingpress_edit_workhours',
				'bookingpress_edit_special_days',
				'bookingpress_timesheet',
				'bookingpress_myservices',
				'bookingpress_myprofile',
				'bookingpress_manage_calendar_integration',
			);

			$global_data['bookingpress_staffmember_singular_name'] = $global_data['general_settings']['staffmember_setting']['bookingpress_staffmember_module_singular_name'];
			$global_data['bookingpress_staffmember_plural_name'] = $global_data['general_settings']['staffmember_setting']['bookingpress_staffmember_module_plural_name']; 

			return $global_data;
		}
		
		 /*  Get Currency Name/Label From Currency Code For Authorize.Net */

        function bookingpress_authorize_net_currency_symbol() {
            $currency_symbol = array('USD','GBP','EUR');
            return $currency_symbol;
        }
		
		/**
		 * Function to return the payment gateways list that sending the
		 * amount multiplied by 100. For eg., if we need to make a
		 * transaction worth USD 3 then the payment gateway sends the
		 * data as 300 ( 3 * 100 ) to adjust the decimal points.
		 *
		 * @return void
		 */
		function bookingpress_payment_gateways_with_extra_calculations(){
			
			$payment_gateways_arr = array(
				'stripe',
				'razorpay',
				'paystack',
				'square',
			);

			return apply_filters( 'bookingpress_modify_payment_gateways_array_outside_with_extra_calculations', $payment_gateways_arr );

		}

		function bookingpress_payment_gateways_for_amount_field_name(){

			$payment_gateways_arr = array(
				'square' => array(
					'amount' => 'payment|approved_money|amount',
					'currency' => 'payment|approved_money|currency'
				),
				'paypal' => array(
					'amount' => 'mc_gross',
					'currency' => 'mc_currency',
				),
				'stripe' => array(
					'amount' => 'amount',
					'currency' => 'currency'
				),
				'razorpay' => array(
					'amount' => 'amount',
					'currency' => 'currency'
				),
				'paystack' => array(
					'amount' => 'data|amount',
					'currency' => 'data|currency'
				),
				'payfast' => array(
					'amount' => 'amount_gross',
					'currency' => false,
					'supported_currency' => 'ZAR'
				),
				'mollie' => array(
					'amount' => 'amount|value',
					'currency' => 'amount|currency'
				),
				'klarna' => array(
					'amount' => 'order_amount',
					'currency' => 'purchase_currency'
				),
				'payumoney' => array(
					'amount' => 'amount',
					'currency' => false,
					'supported_currency' => 'INR'
				),
				'authorize_net' => array(
					'amount' => 'amount',
					'currency' => false,
					'supported_currency' => ''
				),
				'braintree' => array(
					'amount' => 'amount',
					'currency' => 'currency'
				),
				'skrill' => array(
					'amount' => 'amount',
					'currency' => 'currency'
				),
				'paypalpro' => array(
					'amount' => 'AMT',
					'currency' => 'CURRENCY'
				),
				'paddle' => array(
					'amount' => 'sale_gross',
					'currency' => 'currency'
				),
			);

			return $payment_gateways_arr;
		}

		function bookingpress_allowed_refund_payment_gateway_list() {
			$payment_gateway_data = array();
			$payment_gateway_data['paypal'] = array(
				'full_status' => 1,
				'partial_status' => 1,
				'allow_days' => 180,
				'is_refund_support' => 1,
			);
			$payment_gateway_data = apply_filters('bookingpress_allowed_payment_gateway_for_refund',$payment_gateway_data);
			return $payment_gateway_data;
		}
	}
}
global $bookingpress_pro_global_options;
$bookingpress_pro_global_options = new BookingPress_Pro_Global_Options();
