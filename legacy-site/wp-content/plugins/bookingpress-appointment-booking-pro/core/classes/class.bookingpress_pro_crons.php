<?php
if ( ! class_exists( 'bookingpress_pro_crons' ) ) {
	class bookingpress_pro_crons Extends BookingPress_Core {
		function __construct() {
			// To add custom timings for wp cron
			add_filter( 'cron_schedules', array( $this, 'bookingpress_mycron_schedules' ) );

			add_action( 'init', array( $this, 'bookingpress_add_crons' ), 10 );

			add_action( 'bookingpress_before_appointment_approved_for_customer', array( $this, 'bookingpress_before_appointment_approved_for_customer' ) );
			add_action( 'bookingpress_before_appointment_pending_for_customer', array( $this, 'bookingpress_before_appointment_pending_for_customer' ) );
			add_action( 'bookingpress_after_appointment_approved_for_customer', array( $this, 'bookingpress_after_appointment_approved_for_customer' ) );
			add_action( 'bookingpress_after_appointment_pending_for_customer', array( $this, 'bookingpress_after_appointment_pending_for_customer' ) );
			add_action( 'bookingpress_after_appointment_canceled_for_customer', array( $this, 'bookingpress_after_appointment_canceled_for_customer' ) );
			add_action( 'bookingpress_after_appointment_rejected_for_customer', array( $this, 'bookingpress_after_appointment_rejected_for_customer' ) );

			add_action( 'bookingpress_before_appointment_approved_for_staffmember', array( $this, 'bookingpress_before_appointment_approved_for_staffmember' ) );
			add_action( 'bookingpress_before_appointment_pending_for_staffmember', array( $this, 'bookingpress_before_appointment_pending_for_staffmember' ) );
			add_action( 'bookingpress_after_appointment_approved_for_staffmember', array( $this, 'bookingpress_after_appointment_approved_for_staffmember' ) );
			add_action( 'bookingpress_after_appointment_pending_for_staffmember', array( $this, 'bookingpress_after_appointment_pending_for_staffmember' ) );
			add_action( 'bookingpress_after_appointment_canceled_for_staffmember', array( $this, 'bookingpress_after_appointment_canceled_for_staffmember' ) );
			add_action( 'bookingpress_after_appointment_rejected_for_staffmember', array( $this, 'bookingpress_after_appointment_rejected_for_staffmember' ) );

			add_action( 'wp', array( $this, 'bookingpress_execute_schedular') );
		}

		function bookingpress_execute_schedular(){

			if( isset( $_GET['bpa_action'] ) && 'bpa_send_scheduled_notifications' == $_GET['bpa_action'] ){
				/** email cron functions */
				$bookingpress_cron_hooks = $this->bookingpress_cron_hooks();
				foreach( $bookingpress_cron_hooks as $k => $v ){
					do_action( $v ); //Execute the cron functions immediately
				}

				do_action( 'bookingpress_force_send_scheduled_notifications');
			}
		}
				
		/**
		 * Function for add/change WordPress scheudle time
		 *
		 * @param  mixed $schedules
		 * @return void
		 */
		function bookingpress_mycron_schedules( $schedules ) {
			if ( ! isset( $schedules['5min'] ) ) {
				$schedules['5min'] = array(
					'interval' => 5 * 60,
					'display'  => __( 'Every 05 minutes', 'bookingpress-appointment-booking' ),
				);
			}
			return $schedules;
		}
		
		/**
		 * Function for add WordPress schedulers
		 *
		 * @return void
		 */
		function bookingpress_add_crons() {
			wp_get_schedules();
			$bookingpress_cron_hooks = $this->bookingpress_cron_hooks();
			foreach ( $bookingpress_cron_hooks as $k => $v ) {
				if ( ! wp_next_scheduled( $v ) ) {
					wp_schedule_event( time(), '5min', $v );
				}
			}
		}
		
		/**
		 * Function for add cron hooks
		 *
		 * @return void
		 */
		function bookingpress_cron_hooks() {
			$bookingpress_customer_cron_hooks_arr = array(
				'bookingpress_before_appointment_approved_for_customer',
				'bookingpress_before_appointment_pending_for_customer',
				/* 'bookingpress_before_appointment_canceled_for_customer',
				'bookingpress_before_appointment_rejected_for_customer', */
				'bookingpress_after_appointment_approved_for_customer',
				'bookingpress_after_appointment_pending_for_customer',
				'bookingpress_after_appointment_canceled_for_customer',
				'bookingpress_after_appointment_rejected_for_customer',
			);

			$bookingpress_staffmember_cron_hooks_arr = array(
				'bookingpress_before_appointment_approved_for_staffmember',
				'bookingpress_before_appointment_pending_for_staffmember',
				/* 'bookingpress_before_appointment_canceled_for_staffmember',
				'bookingpress_before_appointment_rejected_for_staffmember', */
				'bookingpress_after_appointment_approved_for_staffmember',
				'bookingpress_after_appointment_pending_for_staffmember',
				'bookingpress_after_appointment_canceled_for_staffmember',
				'bookingpress_after_appointment_rejected_for_staffmember',
			);

			$bookingpress_cron_hooks_arr = array_merge( $bookingpress_customer_cron_hooks_arr, $bookingpress_staffmember_cron_hooks_arr );

			return $bookingpress_cron_hooks_arr;
		}
		
		/**
		 * Function for get difference time in minutes
		 *
		 * @param  mixed $bookingpress_duration_val
		 * @param  mixed $bookingpress_duration_val_unit
		 * @return void
		 */
		function bookingpress_get_difference_time_in_minutes( $bookingpress_duration_val, $bookingpress_duration_val_unit ) {
			$bookingpress_difference_time = 60; // In minutes
			if ( $bookingpress_duration_val_unit == 'h' ) {
				$bookingpress_difference_time = 60 * $bookingpress_duration_val;
			} elseif ( $bookingpress_duration_val_unit == 'd' ) {
				$bookingpress_difference_time = 1440 * $bookingpress_duration_val;
			} elseif ( $bookingpress_duration_val_unit == 'w' ) {
				$bookingpress_difference_time = 10080 * $bookingpress_duration_val;
			} elseif ( $bookingpress_duration_val_unit == 'm' ) {
				$bookingpress_difference_time = 43800 * $bookingpress_duration_val;
			}

			return $bookingpress_difference_time;
		}
		
		/**
		 * Function for check cron email notification sent or not
		 *
		 * @param  mixed $bookingpress_email_notification_id
		 * @param  mixed $bookingpress_customer_id
		 * @param  mixed $bookingpress_email_address
		 * @param  mixed $bookingpress_appointment_id
		 * @param  mixed $bookingpress_appointment_date
		 * @param  mixed $bookingpress_appointment_time
		 * @param  mixed $bookingpress_appointment_status
		 * @param  mixed $bookingpress_hook_name
		 * @param  mixed $bookingpress_staffmember_id
		 * @param  mixed $bookingpress_staffmember_email
		 * @return void
		 */
		function bookingpress_check_cron_email_sent_or_not( $bookingpress_email_notification_id, $bookingpress_customer_id, $bookingpress_email_address, $bookingpress_appointment_id, $bookingpress_appointment_date, $bookingpress_appointment_time, $bookingpress_appointment_status, $bookingpress_hook_name, $bookingpress_staffmember_id = 0, $bookingpress_staffmember_email = '' ) {
			global $wpdb, $tbl_bookingpress_cron_email_notifications_logs;

			if(empty($bookingpress_staffmember_id)){
				$bookingpress_is_record_exists = $wpdb->get_var( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_cron_email_notifications_logs} WHERE bookingpress_email_notification_id = %d AND bookingpress_customer_id = %d AND bookingpress_email_address = %s AND bookingpress_appointment_id = %d AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_status = %s AND bookingpress_email_cron_hook_name = %s AND bookingpress_staffmember_email = %s", $bookingpress_email_notification_id, $bookingpress_customer_id, $bookingpress_email_address, $bookingpress_appointment_id, $bookingpress_appointment_date, $bookingpress_appointment_time, $bookingpress_appointment_status, $bookingpress_hook_name, $bookingpress_staffmember_email ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_cron_email_notifications_logs is a table name. false alarm
			}else if(!empty($bookingpress_staffmember_id)){
				$bookingpress_is_record_exists = $wpdb->get_var( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_cron_email_notifications_logs} WHERE bookingpress_email_notification_id = %d AND bookingpress_customer_id = %d AND bookingpress_email_address = %s AND bookingpress_appointment_id = %d AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_status = %s AND bookingpress_email_cron_hook_name = %s AND bookingpress_staffmember_id = %d AND bookingpress_staffmember_email = %s", $bookingpress_email_notification_id, $bookingpress_customer_id, $bookingpress_email_address, $bookingpress_appointment_id, $bookingpress_appointment_date, $bookingpress_appointment_time, $bookingpress_appointment_status, $bookingpress_hook_name, $bookingpress_staffmember_id, $bookingpress_staffmember_email ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_cron_email_notifications_logs is a table name. false alarm
			}

			return $bookingpress_is_record_exists;
		}

		/*
		 * Customer Cron Hooks
		 * ---------------------------
		 */


		/**
		 * Function for send notification to customer before apppiotment approved
		 *
		 * @return void
		 */
		function bookingpress_before_appointment_approved_for_customer() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs;
			
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_approved( 'customer', 'before' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '+' . $bookingpress_difference_time . ' minutes' ) );

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '1', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {

						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '1', 'bookingpress_before_appointment_approved_for_customer' );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );


						if ( ( $bookingpress_is_allow_service == 1 ) && ( $current_datetime <= $bookingpress_appointment_datetime && $current_datetime >= $bookingpress_notification_sending_time ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {

							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '1',
								'bookingpress_email_sending_date' => $current_date,									
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_before_appointment_approved_for_customer',
							);

							if( empty( $bookingpress_is_email_sent )  ){
								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_customer_email );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];								
								
								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'customer',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);
								
								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );
								
								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}
							
							do_action('bookingpress_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to customer before apppiotment pending
		 *
		 * @return void
		 */
		function bookingpress_before_appointment_pending_for_customer() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_pending( 'customer', 'before' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '+' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '2', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '2', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '2', 'bookingpress_before_appointment_pending_for_customer' );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $current_datetime <= $bookingpress_appointment_datetime && $current_datetime >= $bookingpress_notification_sending_time ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '2',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_before_appointment_pending_for_customer',
							);

							if(empty( $bookingpress_is_email_sent ) ){
								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_customer_email );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'customer',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to customer before apppiotment canceled
		 *
		 * @return void
		 */
		function bookingpress_before_appointment_canceled_for_customer() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_canceled( 'customer', 'before' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '3', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '3', 'bookingpress_before_appointment_canceled_for_customer' );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $current_time <= $bookingpress_appointment_time && $current_time >= $bookingpress_notification_sending_time ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '3',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_before_appointment_canceled_for_customer',
							);

							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_customer_email );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];							

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'customer',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to customer before apppiotment rejected
		 *
		 * @return void
		 */
		function bookingpress_before_appointment_rejected_for_customer() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_rejected( 'customer', 'before' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '4', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '4', 'bookingpress_before_appointment_rejected_for_customer' );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $current_time <= $bookingpress_appointment_time && $current_time >= $bookingpress_notification_sending_time ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {

							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '4',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,
								'bookingpress_email_cron_hook_name' => 'bookingpress_before_appointment_rejected_for_customer',
							);

							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_customer_email );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];								

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'customer',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to customer after apppiotment approved
		 *
		 * @return void
		 */
		function bookingpress_after_appointment_approved_for_customer() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_approved( 'customer', 'after' );

			$bookingpress_email_notification_send_data = array();


			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '-' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '1', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '1', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '1', 'bookingpress_after_appointment_approved_for_customer' );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}
						
						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $bookingpress_notification_sending_time <= $current_datetime ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {

							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '1',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,
								'bookingpress_email_cron_hook_name' => 'bookingpress_after_appointment_approved_for_customer',
							);

							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_customer_email );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];								

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'customer',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to customer after apppiotment pending
		 *
		 * @return void
		 */
		function bookingpress_after_appointment_pending_for_customer() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_pending( 'customer', 'after' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );
				
				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '-' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '2', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '2', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '2', 'bookingpress_after_appointment_pending_for_customer' );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $bookingpress_notification_sending_time <= $current_datetime ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '2',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_after_appointment_pending_for_customer',
							);

							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_customer_email );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'customer',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to customer after apppiotment canceled
		 *
		 * @return void
		 */
		function bookingpress_after_appointment_canceled_for_customer() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_canceled( 'customer', 'after' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '-' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '3', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '3', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '3', 'bookingpress_after_appointment_canceled_for_customer' );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $bookingpress_notification_sending_time <= $current_datetime ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '3',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_after_appointment_canceled_for_customer',
							);

							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_customer_email );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'customer',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to customer after apppiotment rejected
		 *
		 * @return void
		 */
		function bookingpress_after_appointment_rejected_for_customer() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_rejected( 'customer', 'after' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );
				
				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '-' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '4', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '4', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '4', 'bookingpress_after_appointment_rejected_for_customer' );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $bookingpress_notification_sending_time <= $current_datetime ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '4',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_after_appointment_rejected_for_customer',
							);

							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_customer_email );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'customer',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}


		/*
		 * Staff Members Cron Hooks
		 * ---------------------------
		 */
		

		/**
		 * Function for send notification to staffmember before apppiotment approved
		 *
		 * @return void
		 */
		function bookingpress_before_appointment_approved_for_staffmember() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_pro_staff_members;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_approved( 'employee', 'before' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '+' . $bookingpress_difference_time . ' minutes' ) );

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '1', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_staffmember_id = $v2['bookingpress_staff_member_id'];
						$bookingpress_staffmember_email = !empty($v2['bookingpress_staff_email_address']) ? $v2['bookingpress_staff_email_address'] : '';
						if( !$bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ){
							$bookingpress_staffmember_email = $BookingPress->bookingpress_get_settings('admin_email', 'notification_setting');
						}

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '1', 'bookingpress_before_appointment_approved_for_staffmember', $bookingpress_staffmember_id, $bookingpress_staffmember_email );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $current_datetime <= $bookingpress_appointment_datetime && $current_datetime >= $bookingpress_notification_sending_time ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
								'bookingpress_staffmember_email' => $bookingpress_staffmember_email,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '1',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_before_appointment_approved_for_staffmember',
							);

							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_cc_emails = array();
                    			$bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, $bookingpress_notification_name);

								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_staffmember_email, $bookingpress_cc_emails);
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];
								
								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'employee',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'staffmember_email' => $bookingpress_staffmember_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_staff_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to staffmember before apppiotment pending
		 *
		 * @return void
		 */
		function bookingpress_before_appointment_pending_for_staffmember() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_pro_staff_members;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_pending( 'employee', 'before' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '+' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '2', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '2', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_staffmember_id = $v2['bookingpress_staff_member_id'];
						$bookingpress_staffmember_email = !empty($v2['bookingpress_staff_email_address']) ? $v2['bookingpress_staff_email_address'] : '';
						if( !$bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ){
							$bookingpress_staffmember_email = $BookingPress->bookingpress_get_settings('admin_email', 'notification_setting');
						}

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '2', 'bookingpress_before_appointment_pending_for_staffmember', $bookingpress_staffmember_id, $bookingpress_staffmember_email );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $current_datetime <= $bookingpress_appointment_datetime && $current_datetime >= $bookingpress_notification_sending_time ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
								'bookingpress_staffmember_email' => $bookingpress_staffmember_email,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '2',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_before_appointment_pending_for_staffmember',
							);

							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_cc_emails = array();
                    			$bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, $bookingpress_notification_name);

								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_staffmember_email, $bookingpress_cc_emails );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];								

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'employee',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'staffmember_email' => $bookingpress_staffmember_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_staff_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to staffmember before apppiotment canceled
		 *
		 * @return void
		 */
		function bookingpress_before_appointment_canceled_for_staffmember() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_pro_staff_members;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_canceled( 'employee', 'before' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '+' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '3', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '3', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_staffmember_id = $v2['bookingpress_staff_member_id'];
						$bookingpress_staffmember_email = !empty($v2['bookingpress_staff_email_address']) ? $v2['bookingpress_staff_email_address'] : '';
						if( !$bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ){
							$bookingpress_staffmember_email = $BookingPress->bookingpress_get_settings('admin_email', 'notification_setting');
						}

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '3', 'bookingpress_before_appointment_canceled_for_staffmember', $bookingpress_staffmember_id, $bookingpress_staffmember_email );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $current_datetime <= $bookingpress_appointment_datetime && $current_datetime >= $bookingpress_notification_sending_time ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
								'bookingpress_staffmember_email' => $bookingpress_staffmember_email,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '3',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_before_appointment_canceled_for_staffmember',
							);

							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_cc_emails = array();
                    			$bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, $bookingpress_notification_name);

								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_staffmember_email, $bookingpress_cc_emails );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];								

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'employee',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'staffmember_email' => $bookingpress_staffmember_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_staff_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to staffmember before apppiotment rejected
		 *
		 * @return void
		 */
		function bookingpress_before_appointment_rejected_for_staffmember() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_pro_staff_members;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_canceled( 'employee', 'before' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '+' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '4', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '4', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_staffmember_id = $v2['bookingpress_staff_member_id'];
						$bookingpress_staffmember_email = !empty($v2['bookingpress_staff_email_address']) ? $v2['bookingpress_staff_email_address'] : '';
						if( !$bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ){
							$bookingpress_staffmember_email = $BookingPress->bookingpress_get_settings('admin_email', 'notification_setting');
						}

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '4', 'bookingpress_before_appointment_rejected_for_staffmember', $bookingpress_staffmember_id, $bookingpress_staffmember_email );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $current_datetime <= $bookingpress_appointment_datetime && $current_datetime >= $bookingpress_notification_sending_time ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
								'bookingpress_staffmember_email' => $bookingpress_staffmember_email,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '4',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_before_appointment_rejected_for_staffmember',
							);
							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_cc_emails = array();
                    			$bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, $bookingpress_notification_name);

								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_staffmember_email, $bookingpress_cc_emails );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];								

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'employee',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'staffmember_email' => $bookingpress_staffmember_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}
							
							do_action('bookingpress_staff_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to staffmember after apppiotment approved
		 *
		 * @return void
		 */
		function bookingpress_after_appointment_approved_for_staffmember() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_pro_staff_members;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_approved( 'employee', 'after' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '-' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '1', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '1', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_staffmember_id = $v2['bookingpress_staff_member_id'];
						$bookingpress_staffmember_email = !empty($v2['bookingpress_staff_email_address']) ? $v2['bookingpress_staff_email_address'] : '';
						if( !$bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ){
							$bookingpress_staffmember_email = $BookingPress->bookingpress_get_settings('admin_email', 'notification_setting');
						}

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '1', 'bookingpress_after_appointment_approved_for_staffmember', $bookingpress_staffmember_id, $bookingpress_staffmember_email );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $bookingpress_notification_sending_time <= $current_datetime ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
								'bookingpress_staffmember_email' => $bookingpress_staffmember_email,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '1',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_after_appointment_approved_for_staffmember',
							);
							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_cc_emails = array();
                    			$bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, $bookingpress_notification_name);

								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_staffmember_email, $bookingpress_cc_emails );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];							

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'employee',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'staffmember_email' => $bookingpress_staffmember_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_staff_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to staffmember after apppiotment pending
		 *
		 * @return void
		 */
		function bookingpress_after_appointment_pending_for_staffmember() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_pro_staff_members;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_pending( 'employee', 'after' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '-' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '2', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '2', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_staffmember_id = $v2['bookingpress_staff_member_id'];
						$bookingpress_staffmember_email = !empty($v2['bookingpress_staff_email_address']) ? $v2['bookingpress_staff_email_address'] : '';
						if( !$bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ){
							$bookingpress_staffmember_email = $BookingPress->bookingpress_get_settings('admin_email', 'notification_setting');
						}

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '2', 'bookingpress_after_appointment_pending_for_staffmember', $bookingpress_staffmember_id, $bookingpress_staffmember_email );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $bookingpress_notification_sending_time <= $current_datetime ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
								'bookingpress_staffmember_email' => $bookingpress_staffmember_email,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '2',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_after_appointment_pending_for_staffmember',
							);

							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_cc_emails = array();
                    			$bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, $bookingpress_notification_name);

								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_staffmember_email, $bookingpress_cc_emails );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];								

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'employee',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'staffmember_email' => $bookingpress_staffmember_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_staff_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to staffmember after apppiotment canceled
		 *
		 * @return void
		 */
		function bookingpress_after_appointment_canceled_for_staffmember() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_pro_staff_members;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_canceled( 'employee', 'after' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '-' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '3', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '3', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_staffmember_id = $v2['bookingpress_staff_member_id'];
						$bookingpress_staffmember_email = !empty($v2['bookingpress_staff_email_address']) ? $v2['bookingpress_staff_email_address'] : '';
						if( !$bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ){
							$bookingpress_staffmember_email = $BookingPress->bookingpress_get_settings('admin_email', 'notification_setting');
						}

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '3', 'bookingpress_after_appointment_canceled_for_staffmember', $bookingpress_staffmember_id, $bookingpress_staffmember_email );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $bookingpress_notification_sending_time <= $current_datetime ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {						
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
								'bookingpress_staffmember_email' => $bookingpress_staffmember_email,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '3',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_after_appointment_canceled_for_staffmember',
							);
							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_cc_emails = array();
                    			$bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, $bookingpress_notification_name);

								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_staffmember_email, $bookingpress_cc_emails );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'employee',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'staffmember_email' => $bookingpress_staffmember_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_staff_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}
		
		/**
		 * Function for send notification to staffmember after apppiotment rejected
		 *
		 * @return void
		 */
		function bookingpress_after_appointment_rejected_for_staffmember() {
			global $wpdb, $BookingPress, $bookingpress_pro_manage_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_email_notifications, $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_pro_staff_members;
			$bookingpress_custom_notification_list = $bookingpress_pro_manage_notifications->bookingpress_get_custom_notification_for_appointment_rejected( 'employee', 'after' );

			$bookingpress_email_notification_send_data = array();

			foreach ( $bookingpress_custom_notification_list as $k => $v ) {
				$bookingpress_notification_duration_val      = ! empty( $v['bookingpress_notification_duration_val'] ) ? $v['bookingpress_notification_duration_val'] : 1;
				$bookingpress_notification_duration_val_unit = ! empty( $v['bookingpress_notification_duration_unit'] ) ? $v['bookingpress_notification_duration_unit'] : 'h';

				$bookingpress_difference_time = $this->bookingpress_get_difference_time_in_minutes( $bookingpress_notification_duration_val, $bookingpress_notification_duration_val_unit );

				$bookingpress_notification_services_arr = ! empty( $v['bookingpress_notification_service'] ) ? explode( ',', $v['bookingpress_notification_service'] ) : array();

				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				$current_time = date( 'H:i', current_time( 'timestamp' ) );

				$notification_time = date( 'Y-m-d H:i:s', strtotime( $current_date . ' ' . $current_time . '-' . $bookingpress_difference_time . ' minutes' ) );

				//$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND bookingpress_appointment_date = %s", '4', $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_status = %s AND CONCAT( bookingpress_appointment_date, ' ', bookingpress_appointment_time ) <= %s", '4', $notification_time ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( ! empty( $bookingpress_appointments ) && is_array( $bookingpress_appointments ) ) {
					foreach ( $bookingpress_appointments as $k2 => $v2 ) {
						$bookingpress_appointment_time          = $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date( 'H:i', strtotime( '-' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_time ) ) );

						$bookingpress_notification_id   = ! empty( $v['bookingpress_notification_id'] ) ? $v['bookingpress_notification_id'] : 0;
						$bookingpress_notification_name = ! empty( $v['bookingpress_notification_name'] ) ? $v['bookingpress_notification_name'] : '';
						$bookingpress_appointment_id    = $v2['bookingpress_appointment_booking_id'];

						$bookingpress_customer_id      = $v2['bookingpress_customer_id'];
						$bookingpress_customer_email   = ! empty( $v2['bookingpress_customer_email'] ) ? $v2['bookingpress_customer_email'] : '';

						$bookingpress_staffmember_id = $v2['bookingpress_staff_member_id'];
						$bookingpress_staffmember_email = !empty($v2['bookingpress_staff_email_address']) ? $v2['bookingpress_staff_email_address'] : '';
						if( !$bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ){
							$bookingpress_staffmember_email = $BookingPress->bookingpress_get_settings('admin_email', 'notification_setting');
						}

						$bookingpress_is_email_sent = $this->bookingpress_check_cron_email_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $bookingpress_appointment_id, $v2['bookingpress_appointment_date'], $bookingpress_appointment_time, '4', 'bookingpress_after_appointment_rejected_for_staffmember', $bookingpress_staffmember_id, $bookingpress_staffmember_email );

						$bookingpress_service_id       = ! empty( $v2['bookingpress_service_id'] ) ? $v2['bookingpress_service_id'] : 0;
						$bookingpress_is_allow_service = 1;
						if ( ! empty( $bookingpress_notification_services_arr ) && is_array( $bookingpress_notification_services_arr ) && ! empty( $bookingpress_service_id ) ) {
							if ( in_array( $bookingpress_service_id, $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} elseif ( in_array( 'any', $bookingpress_notification_services_arr ) ) {
								$bookingpress_is_allow_service = 1;
							} else {
								$bookingpress_is_allow_service = 0;
							}
						}

						$current_datetime = date('Y-m-d H:i:s', current_time('timestamp') );
						$bookingpress_appointment_datetime = $v2['bookingpress_appointment_date'] .' ' . $v2['bookingpress_appointment_time'];
						$bookingpress_notification_sending_time = date('Y-m-d H:i:s', strtotime( '+' . $bookingpress_difference_time . ' minutes', strtotime( $bookingpress_appointment_datetime ) ) );

						if ( ( $bookingpress_is_allow_service == 1 ) && ( $bookingpress_notification_sending_time <= $current_datetime ) && ! empty( $bookingpress_notification_name ) && ! empty( $bookingpress_appointment_id ) && ! empty( $bookingpress_customer_email ) ) {
							
							$bookingpress_db_fields = array(
								'bookingpress_email_notification_id' => $bookingpress_notification_id,
								'bookingpress_customer_id' => $bookingpress_customer_id,
								'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
								'bookingpress_staffmember_email' => $bookingpress_staffmember_email,
								'bookingpress_email_address' => $bookingpress_customer_email,
								'bookingpress_appointment_id' => $bookingpress_appointment_id,
								'bookingpress_appointment_date' => $v2['bookingpress_appointment_date'],
								'bookingpress_appointment_time' => $bookingpress_appointment_time,
								'bookingpress_appointment_status' => '3',
								'bookingpress_email_sending_date' => $current_date,
								'bookingpress_email_sending_time' => $bookingpress_notification_sending_time,								
								'bookingpress_email_cron_hook_name' => 'bookingpress_after_appointment_rejected_for_staffmember',
							);
							if(empty( $bookingpress_is_email_sent )){
								$bookingpress_cc_emails = array();
                    			$bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, $bookingpress_notification_name);

								$bookingpress_email_send_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', $bookingpress_notification_name, $bookingpress_appointment_id, $bookingpress_staffmember_email, $bookingpress_cc_emails );
								$is_email_sent               = $bookingpress_email_send_res['is_mail_sent'];
								$email_configurations        = $bookingpress_email_send_res['configurations'];
								$email_error_response        = $bookingpress_email_send_res['error_response'];

								$bookingpress_db_fields['bookingpress_notification_type'] = 'email';
								$bookingpress_db_fields['bookingpress_email_is_sent'] = $is_email_sent;

								$bookingpress_email_posted_data = array(
									'template_type'     => 'employee',
									'notification_name' => $bookingpress_notification_name,
									'appointment_id'    => $bookingpress_appointment_id,
									'customer_email'    => $bookingpress_customer_email,
									'staffmember_email' => $bookingpress_staffmember_email,
									'template_details'  => $bookingpress_email_send_res['posted_data'],
								);

								$bookingpress_db_fields['bookingpress_email_posted_data']           = wp_json_encode( $bookingpress_email_posted_data );
								$bookingpress_db_fields['bookingpress_email_response']              = wp_json_encode( $email_error_response );
								$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );

								$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
								$bookingpress_last_inserted_id = $wpdb->insert_id;
							}

							do_action('bookingpress_staff_cron_external_notification', $bookingpress_appointment_id, $bookingpress_notification_name, $bookingpress_notification_id,$bookingpress_db_fields);
						}
					}
				}
			}
		}

	}
}
global $bookingpress_pro_crons;
$bookingpress_pro_crons = new bookingpress_pro_crons();
