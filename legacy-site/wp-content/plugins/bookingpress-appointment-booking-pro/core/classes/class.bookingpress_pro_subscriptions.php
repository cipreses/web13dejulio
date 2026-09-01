<?php
if ( ! class_exists( 'bookingpress_pro_subscriptions' ) ) {
	class bookingpress_pro_subscriptions Extends BookingPress_Core {
		function __construct() {
			// Add recurring details to entries table
			add_filter( 'bookingpress_modify_entry_data_before_insert', array( $this, 'bookingpress_add_recurring_details_to_entries' ), 10, 2 );

			// Add recurring details to common request modification function
			add_filter( 'bookingpress_add_modify_validate_submit_form_data', array( $this, 'bookingpress_add_recurring_details_to_modification_func' ), 10, 3 );
		}

		function bookingpress_subscription_get_completed_cycles( $bookingpress_subscription_id ) {
			global $wpdb, $tbl_bookingpress_subscription_details;
			$bookingpress_completed_subscription_cycles = 0;

			$bookingpress_subscription_details_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_subscription_details} WHERE bookingpress_subscription_id = %d ORDER BY bookingpress_subscription_detail_id DESC", $bookingpress_subscription_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_subscription_details is a table name. false alarm
			if ( ! empty( $bookingpress_subscription_details_data['bookingpress_subscription_completed_cycle'] ) ) {
				$bookingpress_completed_subscription_cycles = intval( $bookingpress_subscription_details_data['bookingpress_subscription_completed_cycle'] );
			}

			return $bookingpress_completed_subscription_cycles;
		}

		function bookingpress_subscription_get_total_cycles( $bookingpress_subscription_id ) {
			global $wpdb, $tbl_bookingpress_subscription_details;
			$bookingpress_total_subscription_cycles = '0';

			$bookingpress_subscription_details_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_subscription_details} WHERE bookingpress_subscription_id = %d ORDER BY bookingpress_subscription_detail_id ASC", $bookingpress_subscription_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_subscription_details is a table name. false alarm
			if ( ! empty( $bookingpress_subscription_details_data['bookingpress_subscription_total_cycle'] ) ) {
				$bookingpress_total_subscription_cycles = $bookingpress_subscription_details_data['bookingpress_subscription_total_cycle'];
			}

			return $bookingpress_total_subscription_cycles;
		}

		function bookingpress_is_recurring_payment( $bookingpress_subscription_id ) {
			global $wpdb, $tbl_bookingpress_subscription_details;
			$bookingpress_is_recurring = 0;

			$bookingpress_check_subs_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_subscription_detail_id) as total_payments FROM {$tbl_bookingpress_subscription_details} WHERE bookingpress_subscription_id = %d", $bookingpress_subscription_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_subscription_details is a table name. false alarm
			if ( $bookingpress_check_subs_count > 0 ) {
				$bookingpress_is_recurring = 1;
			}

			return $bookingpress_is_recurring;
		}

		function bookingpress_save_subscription_details( $bookingpress_subscription_details ) {
			global $wpdb, $tbl_bookingpress_subscription_details;

			$bookingpress_save_subscription_details_id = 0;
			$bookingpress_subs_details_arr             = array();

			$bookingpress_subscription_id = ! empty( $bookingpress_subscription_details['subscription_id'] ) ? $bookingpress_subscription_details['subscription_id'] : '';
			if ( ! empty( $bookingpress_subscription_id ) ) {
				$bookingpress_customer_id            = ! empty( $bookingpress_subscription_details['bookingpress_customer_id'] ) ? $bookingpress_subscription_details['bookingpress_customer_id'] : '';
				$bookingpress_entry_id               = ! empty( $bookingpress_subscription_details['bookingpress_entry_id'] ) ? $bookingpress_subscription_details['bookingpress_entry_id'] : 0;
				$bookingpress_appointment_booking_id = ! empty( $bookingpress_subscription_details['bookingpress_appointment_booking_id'] ) ? $bookingpress_subscription_details['bookingpress_appointment_booking_id'] : 0;
				$bookingpress_customer_email         = ! empty( $bookingpress_subscription_details['bookingpress_customer_email'] ) ? $bookingpress_subscription_details['bookingpress_customer_email'] : '';
				$bookingpress_transaction_id         = ! empty( $bookingpress_subscription_details['bookingpress_transaction_id'] ) ? $bookingpress_subscription_details['bookingpress_transaction_id'] : '';
				$bookingpress_total_cycle            = ! empty( $bookingpress_subscription_details['bookingpress_subscription_total_cycle'] ) ? $bookingpress_subscription_details['bookingpress_subscription_total_cycle'] : '0';
				$bookingpress_completed_cycle        = ! empty( $bookingpress_subscription_details['bookingpress_subscription_completed_cycle'] ) ? $bookingpress_subscription_details['bookingpress_subscription_completed_cycle'] : 0;

				$bookingpress_subs_details_arr['bookingpress_customer_id']                  = $bookingpress_customer_id;
				$bookingpress_subs_details_arr['bookingpress_entry_id']                     = $bookingpress_entry_id;
				$bookingpress_subs_details_arr['bookingpress_appointment_booking_id']       = $bookingpress_appointment_booking_id;
				$bookingpress_subs_details_arr['bookingpress_customer_email']               = $bookingpress_customer_email;
				$bookingpress_subs_details_arr['bookingpress_subscription_id']              = $bookingpress_subscription_id;
				$bookingpress_subs_details_arr['bookingpress_transaction_id']               = $bookingpress_transaction_id;
				$bookingpress_subs_details_arr['bookingpress_subscription_cycle_type']      = 'first_payment';
				$bookingpress_subs_details_arr['bookingpress_subscription_total_cycle']     = $bookingpress_total_cycle;
				$bookingpress_subs_details_arr['bookingpress_subscription_completed_cycle'] = $bookingpress_completed_cycle;

				$bookingpress_is_recurring = $this->bookingpress_is_recurring_payment( $bookingpress_subscription_id );
				if ( $bookingpress_is_recurring ) {
					$bookingpress_subs_details_arr['bookingpress_subscription_cycle_type'] = 'recurring_payment';
				}

				$wpdb->insert( $tbl_bookingpress_subscription_details, $bookingpress_subs_details_arr );
				$bookingpress_save_subscription_details_id = $wpdb->insert_id;
			}

			return $bookingpress_save_subscription_details_id;
		}

		function bookingpress_add_recurring_details_to_modification_func( $return_data, $payment_gateway, $posted_data ) {
			$bookingpress_recurring_details   = array();
			$return_data['recurring_details'] = $bookingpress_recurring_details;
			return $return_data;
		}

		function bookingpress_add_recurring_details_to_entries( $bookingpress_entry_details, $posted_data ) {
			$bookingpress_recurring_details = array();
			if ( ! empty( $bookingpress_recurring_details ) ) {
				$bookingpress_entry_details['bookingpress_recurring_details'] = $bookingpress_recurring_details;
			}
			return $bookingpress_entry_details;
		}
	}
}
global $bookingpress_pro_subscriptions;
$bookingpress_pro_subscriptions = new bookingpress_pro_subscriptions();
