<?php
$bookingpress_geoip_file = BOOKINGPRESS_PRO_LIBRARY_DIR . '/geoip/autoload.php';
require $bookingpress_geoip_file;
use GeoIp2\Database\Reader;

if ( ! class_exists( 'bookingpress_pro_appointment_bookings' )  && class_exists('BookingPress_Core')) {
	class bookingpress_pro_appointment_bookings Extends BookingPress_Core{
		function __construct() {
			/*
			 Booking Form Shortcode Hooks */
			// ----------------------------------------------------------------------------------------------------

				// Hook for change booking form shortcode view URL
				add_filter( 'bookingpress_change_booking_shortcode_file_url', array( $this, 'bookingpress_load_booking_shortcode_view_file' ), 10, 1 );

				/** Filter for modify all services new array */
				add_filter( 'bookingpress_modify_all_retrieved_services', array( $this, 'bookingpress_front_add_modify_all_services_data' ), 10, 4 );

				/** Filter to modify service data on selection of category */
				add_filter( 'bookingpress_modify_select_service_category', array( $this, 'bookingpress_modify_select_service_category_func'), 10, 1 );

				/** Filter to modify default service id if it's set to 0 or empty */
				add_filter( 'bookingpress_modify_default_servide_id', array( $this, 'bookingpress_update_default_service_id'), 10, 2);

				// Hook for add dynamic data variables for booking form shrotcode
				add_filter( 'bookingpress_frontend_apointment_form_add_dynamic_data', array( $this, 'bookingpress_frontend_add_appointment_data_variables' ), 10, 1 );

				// Hook for execute onload method for booking form shortcode
				add_filter( 'bookingpress_front_booking_dynamic_on_load_methods', array( $this, 'bookingpress_add_appointment_booking_on_load_methods_func' ), 9, 1 );

				// Hook for add dynmiac methods for booking form shortcode
				add_filter( 'bookingpress_add_appointment_booking_vue_methods', array( $this, 'bookingpress_add_appointment_booking_vue_methods_func' ), 10, 1 );

				add_filter( 'bookingpress_any_staff_modify_legacy_flag', array( $this, 'bookingpress_any_staff_modify_legacy_flag_func'), 10 );

				// Hook for add validation code at front end when book appointment button clicks
				//add_action('wp_ajax_bookingpress_pro_before_book_appointment', array($this, 'bookingpress_pro_before_book_appointment_func'));
				//add_action('wp_ajax_nopriv_bookingpress_pro_before_book_appointment', array($this, 'bookingpress_pro_before_book_appointment_func'));

				// New hook for book appointment button
				add_action( 'wp_ajax_bookingpress_book_appointment_booking', array( $this, 'bookingpress_book_front_appointment_func' ) );
				add_action( 'wp_ajax_nopriv_bookingpress_book_appointment_booking', array( $this, 'bookingpress_book_front_appointment_func' ) );

				// Hook for add dynamic to get timings request at frontend booking form shortcode
				add_filter( 'bookingpress_dynamic_add_params_for_timeslot_request', array( $this, 'bookingpress_dynamic_add_params_for_timeslot_request_method' ) );

				// Hook for modify service timings
				add_filter( 'bookingpress_modify_service_timings_filter', array( $this, 'bookingpress_modify_service_timings_filter_func' ), 10, 4 );
				add_filter( 'bookingpress_modify_service_return_timings_filter', array( $this, 'bookingpress_modify_service_return_timings_filter_func' ), 10, 5 );

				// Hook for modify service start time
				add_filter( 'bookingpress_modify_service_start_time', array( $this, 'bookingpress_modify_service_start_time_func' ), 10, 2 );

				// Hook for modify service end time
				add_filter( 'bookingpress_modify_service_end_time', array( $this, 'bookingpress_modify_service_end_time_func' ), 10, 2 );

				// Hook for modify default workhour start time
				add_filter('bookingpress_modify_default_workhour_start_time', array($this, 'bookingpress_modify_default_workhour_start_time_func'), 10, 2);

				// Hook for modify default workhour end time
				add_filter('bookingpress_modify_default_workhour_end_time', array($this, 'bookingpress_modify_default_workhour_end_time_func'), 10, 2);

				// Hook for modify default breakhours timings
				add_action('bookingpress_modify_default_break_timings', array($this, 'bookingpress_modify_default_break_timings_func'), 10, 6);

				// Hook for modify front shortcode data from outside for booking form shortcode
				add_action( 'bookingpress_add_dynamic_details_booking_shortcode', array( $this, 'bookingpress_add_dynamic_details_booking_shortcode_func' ), 10, 3 );

				// Hook for all extra price of service for booking form shortcode
				add_action( 'wp_ajax_bookingpress_calculate_service_addons_price', array( $this, 'bookingpress_calculate_service_addons_price_func' ) );
				add_action( 'wp_ajax_nopriv_bookingpress_calculate_service_addons_price', array( $this, 'bookingpress_calculate_service_addons_price_func' ) );

				// Hook for execute vue code before selecting service for booking form shortcode
				add_filter( 'bookingpress_before_selecting_booking_service', array( $this, 'bookingpress_before_selecting_booking_service_func' ), 10, 1 );

				// Hook for execute vue code after selecting service for booking form shortcode
				add_filter( 'bookingpress_after_selecting_booking_service', array( $this, 'bookingpress_after_selecting_booking_service_func' ), 10, 1 );

				// Hook for change calendar dates as per selected service
				add_action( 'wp_ajax_bookingpress_change_front_calendar_dates', array( $this, 'bookingpress_change_front_calendar_dates_func' ) );
				add_action( 'wp_ajax_nopriv_bookingpress_change_front_calendar_dates', array( $this, 'bookingpress_change_front_calendar_dates_func' ) );

				//Hook for check some code before first step change
				add_filter('bookingpress_add_data_for_first_step_on_next_page', array($this, 'bookingpress_add_data_for_first_step_on_next_page_func'));

				//Hook for check on click of back button
				add_filter('bookingpress_add_data_for_previous_page', array($this, 'bookingpress_add_data_for_previous_page_func'));

				//Hook for get service max capacity
				add_action( 'wp_ajax_bookingpress_get_service_max_capacity', array( $this, 'bookingpress_get_service_max_capacity_func' ) );
				add_action( 'wp_ajax_nopriv_bookingpress_get_service_max_capacity', array( $this, 'bookingpress_get_service_max_capacity_func' ) );

				//Hook for modify service data on category selection
				add_filter('bookingpress_modify_service_data_on_category_selection', array($this, 'bookingpress_modify_service_data_on_category_selection_func'), 10, 3);

				//Hook for modify next page request selection request
				add_filter('bookingpress_dynamic_next_page_request_filter', array($this, 'bookingpress_dynamic_next_page_request_filter_func'), 10, 1);

				//hook for modify the summary page content
				add_filter('bookingpress_change_on_summary_steps_filter', array($this, 'bookingpress_change_on_summary_steps_filter_func'), 10, 1);

				//Hook for get any staff member id if 'any staff' option selected
				add_action( 'wp_ajax_bookingpress_get_any_staffmember_id', array( $this, 'bookingpress_get_any_staffmember_id_func' ) );
				add_action( 'wp_ajax_nopriv_bookingpress_get_any_staffmember_id', array( $this, 'bookingpress_get_any_staffmember_id_func' ) );

				//After selecting payment method at front side
				add_filter('bookingpress_after_selecting_payment_method', array($this, 'bookingpress_after_selecting_payment_method_func'), 10, 1);

				//Add dates to disable date if service duration is days
				add_filter('bookingpress_modify_disable_dates', array($this, 'bookingpress_modify_disable_dates_func'), 10, 4);

				//Modify dates to disable date if service and staff has their own working hours enabled
				add_filter('bookingpress_modify_working_hours', array($this, 'bookingpress_modify_working_hours_func'), 10, 3);

				//Get service categories as per selection of staff member
				add_action('wp_ajax_bookingpress_get_service_cat_details', array($this, 'bookingpress_get_service_cat_details_func'));
				add_action('wp_ajax_nopriv_bookingpress_get_service_cat_details', array($this, 'bookingpress_get_service_cat_details_func'));

				//Hook for modify shortcode content of thank you page
				add_action('wp_ajax_bookingpress_render_thankyou_content', array($this, 'bookingpress_render_thankyou_content_func'));
				add_action('wp_ajax_nopriv_bookingpress_render_thankyou_content', array($this, 'bookingpress_render_thankyou_content_func'));

				//Hook to modify post data for disable date XHR request
				add_filter( 'bookingpress_disable_date_xhr_data', array( $this, 'bookingpress_disable_date_xhr_data_func') );

				add_shortcode('bookingpress_retry_payment', array( $this, 'bookingpress_retry_payment_btn_func' ));

				//Hook to check minimum time requirement
				add_filter( 'bookingpress_retrieve_minimum_required_time', array( $this, 'bookingpress_retrieve_minimum_required_time_func' ), 10, 2 );

				add_action('wp_ajax_bookingpress_set_clients_timezone', array($this, 'bookingpress_set_clients_timezone_func'), 10);
				add_action('wp_ajax_nopriv_bookingpress_set_clients_timezone', array($this, 'bookingpress_set_clients_timezone_func'), 10);

			// ----------------------------------------------------------------------------------------------------

			/*
			 My Bookings Shortcode Hooks */
			// ----------------------------------------------------------------------------------------------------

				// Hook for add dynamic data variables for mybookings shortcode
				add_filter( 'bookingpress_front_appointment_add_dynamic_data', array( $this, 'bookingpress_front_appointment_add_dynamic_data_func' ), 10, 1 );
				add_action('bookingpress_dynamic_add_onload_myappointment_methods', array($this, 'bookingpress_dynamic_add_onload_myappointment_methods_func'));

				// Hook for add vue methods for my bookings
				add_action( 'bookingpress_front_appointment_add_vue_method', array( $this, 'bookingpress_front_appointment_add_vue_method_func' ), 10, 1 );

				//Modify my appointments loading data
				add_filter('bookingpress_modify_my_appointments_data', array($this, 'bookingpress_modify_my_appointments_data_func'), 10, 1);

				// Hook for change my appointments shortcode file url
				add_filter( 'bookingpress_change_my_appointmens_shortcode_file_url', array( $this, 'bookingpress_change_my_appointmens_shortcode_file_url_func' ) );

				// Hook for reschedule appointment from my bookings
				add_action( 'wp_ajax_bookingpress_reschedule_book_appointment', array( $this, 'bookingpress_reschedule_book_appointment_func' ), 10 );


				add_action( 'wp_ajax_bookingpress_reschedule_book_appointment_direct', array( $this, 'bookingpress_reschedule_book_appointment_direct_func' ), 10 );
				add_action( 'wp_ajax_nopriv_bookingpress_reschedule_book_appointment_direct', array( $this, 'bookingpress_reschedule_book_appointment_direct_func' ), 10 );

				// Hook for set appointment timeslot when apponitment reschedule from my bookings
				add_action( 'wp_ajax_bookingpress_reschedule_set_appointment_time_slot', array( $this, 'bookingpress_set_appointment_time_slot_func' ), 10 );
				add_action( 'wp_ajax_nopriv_bookingpress_reschedule_set_appointment_time_slot', array( $this, 'bookingpress_set_appointment_time_slot_func' ), 10 );

				// Hook for get service from appointment id for reschedule appointment from my bookings
				add_action( 'wp_ajax_my_appointment_get_service_id_from_appointment_id', array( $this, 'bookingpress_my_appointment_get_service_id_from_appointment_id_func' ), 10 );
				add_action( 'wp_ajax_nopriv_my_appointment_get_service_id_from_appointment_id', array( $this, 'bookingpress_my_appointment_get_service_id_from_appointment_id_func' ), 10 );

				add_action('bookingpress_delete_customer_log',array($this,'bookingpress_delete_customer_log_func'),10,2);

				add_action('wp_ajax_nopriv_bookingpress_login_customer_account', array($this, 'bookingpress_login_customer_account_func'), 10);

				add_action('wp_ajax_nopriv_bookingpress_forgot_password_account', array($this, 'bookingpress_forgot_password_account_func'), 10);

				add_filter('bookingpress_check_rescheduled_is_appointment_already_booked',array($this,'bookingpress_check_rescheduled_is_appointment_already_booked_func'),10,2);

			// ----------------------------------------------------------------------------------------------------

			/** Bookingpress Edit Profile */

			add_action( 'wp_ajax_bookingpress_update_profile', array( $this, 'bookingpress_update_profile_func' ) );
			add_action( 'wp_ajax_bookingpress_get_edit_profile_data', array( $this, 'bookingpress_get_edit_profile_data_func' ) );
			add_action('wp_ajax_bookingpress_update_password', array($this, 'bookingpress_update_password_func'));

			//Update session value after login
			add_action('set_logged_in_cookie', array($this, 'bookingpress_update_cookie'));

			add_action( 'bookingpress_modify_form_fields_msg_array', array( $this, 'bookingpress_modify_form_fields_msg_array_callback'), 10);
			add_filter('bookingpress_modify_form_fields_rules_arr', array($this, 'bookingpress_modify_form_fields_rules_arr_func'), 10, 2);			
			
			//Set clients timezone
			add_filter('bookingpress_change_timezone_filter', array($this, 'bookingpress_change_timezone_filter_func'), 10, 1);

			/** Front-end File Upload actions start */
			add_action( 'wp_ajax_bpa_front_file_upload', array( $this, 'bookingpress_basic_form_file_upload') );
			add_action( 'wp_ajax_nopriv_bpa_front_file_upload', array( $this, 'bookingpress_basic_form_file_upload') );
			
			add_action( 'wp_ajax_bpa_remove_form_file', array( $this, 'bookingpress_basic_form_file_remove') );
			add_action( 'wp_ajax_nopriv_bpa_remove_form_file', array( $this, 'bookingpress_basic_form_file_remove') );
			
			add_action( 'bookingpress_after_book_appointment', array( $this, 'bookingpress_remove_uploaded_file_from_temp' ), 10, 3 );
			add_filter( 'bookingpress_email_notification_attachment', array( $this, 'bookingpress_attach_uploaded_image_to_email' ), 10, 6 );
			/** Front-end File Upload actions end */

			/** Convert Appointment Date Time to Client Timezone */
			add_filter( 'bookingpress_appointment_change_to_client_timezone', array( $this, 'bookingpress_appointment_change_to_client_timezone_func'), 10, 3 );

			/** Convert Selected  Date from Client Timezone to Server Time */
			add_filter( 'bookingpress_appointment_change_date_to_store_timezone', array( $this, 'bookingpress_appointment_change_date_to_store_timezone_func'), 10, 3 );

			add_filter( 'bookingpress_modify_current_date', array( $this, 'bookingpress_set_current_date_to_client_timezone') );

			//Modify booking id shortcode data
			add_filter('bookingpress_modify_booking_id_shortcode_data', array($this, 'bookingpress_modify_booking_id_shortcode_data_func'), 10, 2);

			//Modify customer details shortcode data
			add_filter('bookingpress_modify_customer_details_shortcode_data', array($this, 'bookingpress_modify_customer_details_shortcode_data_func'), 10, 2);

			//Modify datetime shortcode details
			add_filter('bookingpress_modify_datetime_shortcode_data', array($this, 'bookingpress_modify_datetime_shortcode_data_func'), 10, 2);

			//Modify service name shortcode details
			add_filter('bookingpress_modify_service_shortcode_details', array($this, 'bookingpress_modify_service_shortcode_details_func'), 10, 2);

			add_action( 'wp_ajax_bookingpress_get_whole_day_appointments_multiple_days', array( $this, 'bookingpress_get_whole_day_appointment_multipe_days_func' ) );
            add_action( 'wp_ajax_nopriv_bookingpress_get_whole_day_appointments_multiple_days', array( $this, 'bookingpress_get_whole_day_appointment_multipe_days_func' ) );
			add_action('bookingpress_activate_my_booking_tab_data',array($this,'bookingpress_activate_my_booking_tab_data_func'));

			add_filter('bookingpress_modify_field_data_before_prepare',array($this,'bookingpress_modify_field_data_before_prepare_func'),11);

			add_filter('bookingpress_add_appointment_step_form_data_filter',array($this,'bookingpress_add_appointment_step_form_data_filter_func'),10,2);

			add_filter( 'bookingpress_disable_date_pre_xhr_data', array( $this, 'bookingpress_add_client_timezone' ), 10 );

			/** Hook to add few more data into sidebar steps */
			add_filter( 'bookingpress_frontend_apointment_form_add_dynamic_data', array( $this, 'bookingpress_extra_data_for_sidebar_steps' ), 20 );

			/** Filter to set service max capacity according to staff member when staff is after the service */
			add_filter( 'bookingpress_before_selecting_booking_service', array( $this, 'bookingpress_set_service_max_capacity' ), 11, 1 );

			/** Filter to remove disabled service on load */
			add_filter( 'bookingpress_remove_disabled_services', array( $this, 'bookingpress_remove_disabled_services_func'), 10);

			add_filter( 'bookingpress_modify_service_duration_label', array( $this, 'bookingpress_set_day_unit_duration_label') );

			add_filter( 'bookingpress_frontend_apointment_form_add_dynamic_data', array( $this, 'bookingpress_rearrange_sidebar_steps'), 100 );
			
			add_filter( 'bookingpress_refund_process_before_cancel_appointment', array( $this, 'bookingpress_refund_process_before_cancel_appointment'),10,2 );

			/* Filter for add the cancel appointment extra content */
			add_filter( 'bookingpress_add_cancel_appointment_extra_content', array( $this, 'bookingpress_add_cancel_appointment_extra_content_func'),10,2);

			add_filter( 'bookingpress_modify_cancel_appointment_flag', array( $this, 'bookingpress_modify_cancel_appointment_flag_func'),10,2);			

			//add_action('wp_ajax_bookingpress_front_get_refund_amount',array($this,'bookingpress_front_get_refund_amount_func'),10);

			add_filter( 'bookingpress_get_multiple_days_disable_dates', array( $this, 'bookingpress_get_multiple_days_disable_dates_func' ), 10, 5);

			add_filter( 'bookingpress_modify_disable_date_data', array( $this, 'bookingpress_get_single_day_disable_dates_func'), 10, 4 );

			add_filter( 'bookingpress_disable_multiple_days_xhr_response', array( $this,'bookingpress_disable_multiple_days_xhr_response_func' ), 10 );

			add_filter( 'bookingpress_modify_select_step_category', array( $this, 'bookingpress_share_url_category_displays') );

			add_action( 'init', array( $this, 'bookingpress_unset_cart_cookie') );

			add_action( 'bpa_is_display_emtpy_view', array( $this, 'bookingpress_set_empty_view'), 100, 2 );

			add_filter( 'bookingpress_check_available_timeslot_manual_block', array( $this, 'bookingpress_check_available_timeslot_manual_block_func'), 11, 2 );

			add_filter( 'bookingpress_disable_date_vue_data_modify', array( $this, 'bookingpress_disable_date_vue_data_modify_func'));

			add_filter( 'bookingpress_check_pro_version_booked_appointment', array( $this, 'bookingpress_check_is_booked_appointment'), 10, 8);

			add_filter( 'bookingpress_booked_appointment_with_share_timeslot_where_clause', array( $this, 'bookingpress_booked_appointment_with_share_timeslot_where_clause_func'), 10, 1 );

			add_filter( 'bpa_password_field_validation_outside', array( $this,'bpa_password_field_validation_arr'));

			add_filter('bookingpress_front_check_password_validation', array( $this,'bookingpress_front_check_password_validation_arr' ));

			add_action( 'wp_ajax_nopriv_bookingpress_password_validation_check', array( $this,'bookingpress_password_validation_arr'));

			add_filter('bookingpress_front_check_password_with_username_validation', array( $this,'bookingpress_username_with_password_validation_func'));

			add_action( 'wp_ajax_bookingpress_get_disable_date', array( $this, 'bookingpress_get_disable_dates_for_days_services'), 6 );
			add_action( 'wp_ajax_nopriv_bookingpress_get_disable_date', array( $this, 'bookingpress_get_disable_dates_for_days_services'), 6 );

			add_filter('bookingpress_selected_gateway_label_name', array( $this, 'bookingpress_selected_gateway_label_name_func'), 10, 2 );

			add_action( 'init', array( $this, 'bookingpress_generate_ics_with_appointment_data_detail') );

			/*  Appointment Reschedule Shortcode Added */
			add_shortcode('bookingpress_appointment_reschedule', array($this, 'bookingpress_appointment_reschedule_func'));
			add_action('bookingpress_reschedule_appointment_dynamic_data_fields', array( $this, 'bookingpress_reschedule_appointment_dynamic_data_fields_func' ),10,2);
			add_action('bookingpress_reschedule_appointment_dynamic_vue_methods', array( $this, 'bookingpress_reschedule_appointment_dynamic_vue_methods_func' ));
			add_action('bookingpress_reschedule_appointment_dynamic_helper_vars', array( $this, 'bookingpress_reschedule_appointment_dynamic_helper_vars_func' ));

			add_action('bookingpress_reschedule_appointment_dynamic_on_load_methods', array( $this, 'bookingpress_reschedule_appointment_dynamic_on_load_methods_func' ),10,2);

		}

		/**
		 * Function for reschedule appointment 
		*/
		function bookingpress_reschedule_book_appointment_direct_func(){
			
			global $tbl_bookingpress_appointment_bookings,$wpdb;
			$response              = array();
			$response['variant']      = 'error';
			$response['title']        = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']          = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
			$response['redirect_url'] = '';			
			$wpnonce               = isset( $_REQUEST['reschedule_save_wpnonce'] ) ? sanitize_text_field( $_REQUEST['reschedule_save_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant']      = 'error';
				$response['title']        = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']          = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				$response['redirect_url'] = '';
				if($return_data){
					return $response;
				}
				echo wp_json_encode( $response );
				die();
			}

			$reschedule_token = (isset($_REQUEST['reschedule_token']))?sanitize_text_field($_REQUEST['reschedule_token']):'';
			$resche_apt_id = (isset($_REQUEST['resche_apt_id']))?intval($_REQUEST['resche_apt_id']):'';

			if(empty($reschedule_token) || empty($resche_apt_id)){
				$response['variant']      = 'error';
				$response['title']        = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']          = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				$response['redirect_url'] = '';
				if($return_data){
					return $response;
				}
				echo wp_json_encode( $response );
				die();				
			}

			$bookingpress_appointment_booking_id = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d AND bookingpress_appointment_token = %s", $resche_apt_id,$reschedule_token)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $bookingpress_appointment_log_data is table name defined globally. False Positive alarm
			if(empty($bookingpress_appointment_booking_id)){
				$response['variant']      = 'error';
				$response['title']        = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']          = esc_html__( 'Sorry, Your rescedule token not valid.', 'bookingpress-appointment-booking' );
				$response['redirect_url'] = '';
				if($return_data){
					return $response;
				}
				echo wp_json_encode( $response );
				die();				
			}

			$this->bookingpress_reschedule_book_appointment_func(false,"",$reschedule_token);

			echo wp_json_encode( $response );
			die();	
		}


        /**
         * bookingpress_reschedule_appointment_dynamic_helper_vars_func
         *
         * @return void
         */
        function bookingpress_reschedule_appointment_dynamic_helper_vars_func()
        {
            global $bookingpress_global_options;
            $bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_locale_lang = $bookingpress_options['locale'];
            ?>
            var lang = ELEMENT.lang.<?php echo esc_html($bookingpress_locale_lang); ?>;
            ELEMENT.locale(lang);
            <?php
        }  

        /**
         * bookingpress_reschedule_page_dynamic_data_fields_func
         *
         * @return void
        */
        function bookingpress_reschedule_appointment_dynamic_data_fields_func($bookingpress_appointment_data,$reschedule_appointment_id = 0){

            global $wpdb,$bookingpress_appointment_reschedule, $BookingPress, $bookingpress_global_options,$tbl_bookingpress_services;
           
            $bookingpress_appointment_reschedule = array();

			$bookingpress_global_details     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_start_of_week = intval($bookingpress_global_details['start_of_week']);

			$bookingpress_appointment_reschedule['is_cancel_appointment_loader'] = '0';
            $bookingpress_appointment_reschedule['is_cancel_button_disabled'] = false;
            $bookingpress_appointment_reschedule['bookingpress_cancel_variant'] = '';
            $bookingpress_appointment_reschedule['bookingpress_cancel_msg'] = '';

			$start_of_week = esc_html($bookingpress_global_details['start_of_week']);

			$bookingpress_appointment_reschedule['pickerOptions']['firstDayOfWeek'] = $start_of_week;

			/** My Appointment Rescheduling Form */
			$bookingpress_appointment_reschedule['bookingpress_mybooking_rescheduling_form'] = array(
				'appointment_customer_reschedule_booked_time' => ''
			);
			$bookingpress_appointment_reschedule['bookingpress_mybooking_rescheduling_form_rules'] = array(
				'appointment_customer_reschedule_booked_time' => array(
					array(
						'required' => true,
						'message' => esc_html__( 'Please select rescheduling time', 'bookingpress-appointment-booking' ),
						'trigger' => 'change'
					)
				)
			);

			/** My Appointment Rescheduling Form */
			$bookingpress_appointment_reschedule['customer_reschedule_appointment_loader']  = false;
			$bookingpress_appointment_reschedule['reschedule_appointment_id']                   = '';
			$bookingpress_appointment_reschedule['appointment_customer_reschedule_booked_date'] = date( 'Y-m-d', current_time( 'timestamp' ) );
			$bookingpress_appointment_reschedule['appointment_customer_reschedule_booked_time'] = '';
			$bookingpress_appointment_reschedule['appointment_customer_reschedule_end_time'] = '';
			$bookingpress_appointment_reschedule['reschedule_appointment_time_slot']            = array();
			$bookingpress_appointment_reschedule['reschedule_appointment_service_duration'] = '';

			$bookingpress_appointment_reschedule['open_customer_reschedule_appointment_modal']  = false;
			$bookingpress_appointment_reschedule['customer_reschedule_appointment_loader']  = false;
			$bookingpress_appointment_reschedule['reschedule_appointment_id']                   = '';
			$bookingpress_appointment_reschedule['appointment_customer_reschedule_booked_date'] = date( 'Y-m-d', current_time( 'timestamp' ) );
			$bookingpress_appointment_reschedule['appointment_customer_reschedule_booked_time'] = '';
			$bookingpress_appointment_reschedule['appointment_customer_reschedule_end_time'] = '';
			$bookingpress_appointment_reschedule['reschedule_appointment_time_slot']            = array();
			$bookingpress_appointment_reschedule['reschedule_service_id']                       = '';
			$bookingpress_appointment_reschedule['reschedule_staff_id']                       = '';
			$bookingpress_appointment_reschedule['reschedule_apt_status']                       = '';
			$bookingpress_appointment_reschedule['is_rescheduled_loader']                      = 'true';



			//$bookingpress_appointment_reschedule["site_locale"] = $bookingpress_site_current_language;

			$bookingpress_appointment_reschedule = apply_filters( 'bookingpress_my_appointment_modify_data_for_rescheduling', $bookingpress_appointment_reschedule, $reschedule_appointment_id );
            $bookingpress_appointment_reschedule = apply_filters('bookingpress_reschedule_appointment_add_dynamic_data', $bookingpress_appointment_reschedule);

			
			$bookingpress_morning_text = $BookingPress->bookingpress_get_customize_settings('morning_text', 'booking_form');
            $bookingpress_morning_text = !empty($bookingpress_morning_text) ? stripslashes_deep($bookingpress_morning_text) : '';
            if (empty($bookingpress_morning_text) ) {
                $bookingpress_morning_text = esc_html__('Morning', 'bookingpress-appointment-booking');
            }
            $bookingpress_afternoon_text = $BookingPress->bookingpress_get_customize_settings('afternoon_text', 'booking_form');
            $bookingpress_afternoon_text = !empty($bookingpress_afternoon_text) ? stripslashes_deep($bookingpress_afternoon_text) : '';
            if (empty($bookingpress_afternoon_text) ) {
                $bookingpress_afternoon_text = esc_html__('Afternoon', 'bookingpress-appointment-booking');
            }
            $bookingpress_evening_text = $BookingPress->bookingpress_get_customize_settings('evening_text', 'booking_form');
            $bookingpress_evening_text = !empty($bookingpress_evening_text) ? stripslashes_deep($bookingpress_evening_text) : '';
            if (empty($bookingpress_evening_text) ) {
                $bookingpress_evening_text = esc_html__('Evening', 'bookingpress-appointment-booking');
            }
            $bookingpress_night_text = $BookingPress->bookingpress_get_customize_settings('night_text', 'booking_form');
            $bookingpress_night_text = !empty($bookingpress_night_text) ? stripslashes_deep($bookingpress_night_text) : '';
            if (empty($bookingpress_night_text) ) {
                $bookingpress_night_text = esc_html__('Night', 'bookingpress-appointment-booking');
            }    
            $bookingpress_appointment_reschedule['bookingpress_morning_text'] = $bookingpress_morning_text;
            $bookingpress_appointment_reschedule['bookingpress_afternoon_text'] = $bookingpress_afternoon_text;
            $bookingpress_appointment_reschedule['bookingpress_evening_text'] = $bookingpress_evening_text;
            $bookingpress_appointment_reschedule['bookingpress_night_text'] = $bookingpress_night_text;  


			$service_id = !empty( $bookingpress_appointment_data['	bookingpress_service_id'] ) ? sanitize_text_field( $bookingpress_appointment_data['	bookingpress_service_id'] ) : 0;

			$staff_member_id = !empty( $bookingpress_appointment_data['	bookingpress_staff_member_id'] ) ? sanitize_text_field( $bookingpress_appointment_data['	bookingpress_staff_member_id'] ) : 0;

			$bookingpress_appointment_date = !empty( $bookingpress_appointment_data['bookingpress_appointment_date'] ) ? sanitize_text_field( $bookingpress_appointment_data['bookingpress_appointment_date'] ) : date('Y-m-d', current_time('timestamp') );
			$bookingpress_appointment_time = !empty( $bookingpress_appointment_data['bookingpress_appointment_time'] ) ? sanitize_text_field( $bookingpress_appointment_data['bookingpress_appointment_time'] ) : '';
			$bookingpress_appointment_end_time = !empty( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) ? sanitize_text_field( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) : '';

			$bookingpress_extra_service_details = !empty( $bookingpress_appointment_data['bookingpress_extra_service_details'] ) ? json_decode( $bookingpress_appointment_data['bookingpress_extra_service_details'] ) : array();

			$bookingpress_service_bawy = !empty( $bookingpress_appointment_data['bookingpress_selected_extra_members'] ) ? intval( $bookingpress_appointment_data['bookingpress_selected_extra_members'] ) : 0;

			$bookingpress_service_expiration_date = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_expiration_date FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			$bookingpress_service_start_date = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_start_date FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			if( !empty( $bookingpress_extra_service_details ) ){
				$service_extras_data = array();
				foreach( $bookingpress_extra_service_details as $service_extras ){
					if( true == $service_extras->bookingpress_is_selected ){
						$service_extras_details = $service_extras->bookingpress_extra_service_details;
						$selected_service_extra_id = $service_extras_details->bookingpress_extra_services_id;
						$selected_service_extra_qty = $service_extras->bookingpress_selected_qty;
						$service_extras_data[ $selected_service_extra_id ]['bookingpress_is_selected'] = true;
						$service_extras_data[ $selected_service_extra_id ]['bookingpress_selected_qty'] = $selected_service_extra_qty;
					}
				}
				if( !empty( $service_extras_data ) ){
					$bookingpress_appointment_reschedule['bookingpress_selected_extra_details'] = $service_extras_data;
				}
			}		
			if( !empty( $bookingpress_service_bawy ) ){
				$bookingpress_appointment_reschedule['bookingpress_selected_bring_members'] = $bookingpress_service_bawy;
			}					
			
			$bookingpress_appointment_reschedule['service_id'] = $service_id;
			$bookingpress_appointment_reschedule['staff_id'] = $staff_member_id;
			$bookingpress_appointment_reschedule['appointment_date'] = $bookingpress_appointment_date;
			$bookingpress_appointment_reschedule['appointment_time'] = date('H:i', strtotime( $bookingpress_appointment_time ) );
			$bookingpress_appointment_reschedule['appointment_end_time'] = date('H:i', strtotime( $bookingpress_appointment_end_time ) );
			$bookingpress_appointment_reschedule['appointment_update_id'] = intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']);
			$bookingpress_appointment_reschedule['bookingpress_service_expiration_date'] = !empty($bookingpress_service_expiration_date) ? esc_html($bookingpress_service_expiration_date) : '';
			$bookingpress_appointment_reschedule['bookingpress_service_start_date'] = !empty($bookingpress_service_start_date) ? esc_html($bookingpress_service_start_date) : '';

			$bookingpress_nonce = esc_html(wp_create_nonce('bpa_wp_nonce'));
			$bookingpress_appointment_reschedule['bookingpress_created_nonce'] = $bookingpress_nonce;
			
			$bookingpress_appointment_reschedule['is_display_error'] = "0";
			$bookingpress_appointment_reschedule['is_error_msg'] = "";
			$bookingpress_appointment_reschedule['is_display_success'] = "0";
			$bookingpress_appointment_reschedule['is_success_msg'] = "";			
			$bookingpress_appointment_reschedule['customer_reschedule_appointment_loader'] = true;
			$reschedule_token = (isset($_GET['reschedule_token']))?sanitize_text_field($_GET['reschedule_token']):'';	
			$bookingpress_appointment_reschedule['reschedule_token'] = $reschedule_token;
			
			$bookingpress_appointment_reschedule['service_id'] = $service_id;
			$bookingpress_appointment_reschedule['staff_id'] = $staff_member_id;
			$bookingpress_appointment_reschedule['appointment_date'] = $bookingpress_appointment_date;
			$bookingpress_appointment_reschedule['appointment_time'] = date('H:i', strtotime( $bookingpress_appointment_time ) );
			$bookingpress_appointment_reschedule['appointment_end_time'] = date('H:i', strtotime( $bookingpress_appointment_end_time ) );
			$bookingpress_appointment_reschedule['appointment_update_id'] = intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']);
			$bookingpress_appointment_reschedule['bookingpress_service_expiration_date'] = !empty($bookingpress_service_expiration_date) ? esc_html($bookingpress_service_expiration_date) : '';
			$bookingpress_appointment_reschedule['bookingpress_service_start_date'] = !empty($bookingpress_service_start_date) ? esc_html($bookingpress_service_start_date) : '';		

			$reschedule_appointment_service_duration_unit = (isset($bookingpress_appointment_data['bookingpress_service_duration_unit']))?$bookingpress_appointment_data['bookingpress_service_duration_unit']:'';
			$bookingpress_appointment_reschedule['reschedule_appointment_service_duration_unit']  = $reschedule_appointment_service_duration_unit;

			$bookingpress_appointment_reschedule['booking_cal_rec_maxdate'] = '';

            echo json_encode($bookingpress_appointment_reschedule);
        }  

		/**
		 * Reschedule Appointment on load method
		*/
		function bookingpress_reschedule_appointment_dynamic_on_load_methods_func($bookingpress_appointment_data,$reschedule_appointment_id = 0){
		?>
			this.open_customer_reschedule_shortcode_func(<?php echo intval($reschedule_appointment_id); ?>,'');
		<?php 
		}

        /**
         * bookingpress_reschedule_appointment_dynamic_vue_methods_func
         *
         * @return void
         */
        function bookingpress_reschedule_appointment_dynamic_vue_methods_func() {
            global $bookingpress_notification_duration,$bookingpress_global_options;

			$bookingpress_global_details     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_start_of_week = intval($bookingpress_global_details['start_of_week']);
			global $BookingPress;
            $bookingpress_max_days_for_booking = $BookingPress->bookingpress_get_settings( 'period_available_for_booking', 'general_setting' );			
        ?>		
				bookingpress_set_success_msg(success_msg){
					const vm = this;
					vm.bookingpress_reset_error_success_msg();
					vm.is_display_success = "1";
					vm.is_success_msg = success_msg;					
				},
				RescheduleAppointmentShortcode(apt_status){	
					const vm = this;
					this.$refs['bookingpress_mybooking_rescheduling_form'].validate((valid) => {
                        if (valid) {   
							vm.is_rescheduled_loader = true;						
							var postData = 
							{ 
								action:'bookingpress_reschedule_book_appointment_direct', 
								reschedule_token: this.reschedule_token,
								resche_service_id: this.reschedule_service_id,
								resche_staff_id: this.reschedule_staff_id,
								resche_date: this.appointment_customer_reschedule_booked_date,
								resche_time: this.bookingpress_mybooking_rescheduling_form.appointment_customer_reschedule_booked_time,
								resche_end_time: this.appointment_customer_reschedule_end_time,
								resche_apt_status: apt_status,
								resche_apt_id: this.reschedule_appointment_id,
								reschedule_save_wpnonce:vm.bookingpress_created_nonce,
								bookingpress_uniq_id:vm.bookingpress_uniq_id,  
							};
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
							.then( function (response) 
							{	
								vm.is_rescheduled_loader = false;
								if(response.data.variant == 'success' && response.data.update_data > 0){																		
									vm.bookingpress_set_success_msg(response.data.msg);
								}else{
									if(response.data.msg != undefined) {
										vm.bookingpress_set_error_msg(response.data.msg);
									}
									setTimeout(function(){
										vm.is_display_error = "0";
										vm.is_error_msg = "";
									},3000);
								}
							}.bind(this) )
							.catch( function (error) {
								vm.bookingpress_set_error_msg(error)
							});
						}
					});	
			},
			bookingpress_set_time_slot(service_id)
				{
					const vm = this;
					var service_id = service_id;
					var selected_appointment_date = this.appointment_customer_reschedule_booked_date;
					var postData = 
					{ 
						action:'bookingpress_front_get_timings', 
						service_id : service_id,
						selected_date : selected_appointment_date,
						bookingpress_selected_staffmember: {
							selected_staff_member_id: vm.reschedule_staff_id
						},
						_wpnonce: vm.bookingpress_created_nonce,						
					};
					postData.appointment_data_obj = {
						bookingpress_uniq_id:vm.bookingpress_uniq_id, 
					};						
					<?php
					do_action('bookingpress_modify_rescheduled_front_timing_xhr'); 
					?>					
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) 
					{
						if(response.data != undefined || response.data != [])
						{								
							vm.reschedule_appointment_time_slot = response.data;
						}
					}
					.bind(this) )
					.catch( function (error) {
						console.log(error);
					});					
			},		
			select_date(selected_value) 
			{
				const vm = this;
				vm.bookingpress_mybooking_rescheduling_form.appointment_customer_reschedule_booked_time = '';
				vm.bookingpress_set_time_slot(vm.reschedule_service_id);
			},
			bookingpress_set_rescheduled_end_time(event,time_slot_data) {
					const vm = this;
					if(event != '' && time_slot_data != '') {
						for (let x in time_slot_data) {							
							var slot_data_arr = time_slot_data[x];
							for(let y in slot_data_arr) {
								var time_slot_data_arr = slot_data_arr[y];								
								if(time_slot_data_arr.store_end_time != undefined && time_slot_data_arr.store_end_time != undefined && time_slot_data_arr.store_start_time == event) {   
									vm.appointment_customer_reschedule_end_time = time_slot_data_arr.store_end_time;
								}                                                    
							}                      
						}
					}
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
			open_customer_reschedule_shortcode_func(apt_id,service_duration){
					const vm = this;

					vm.booking_cal_rec_maxdate = new Date( Date.now() + ( 3600 * 1000 * ( 24 * <?php echo $bookingpress_max_days_for_booking; //phpcs:ignore ?>) ) );
					
					vm.reschedule_appointment_service_duration = service_duration;
					this.reschedule_appointment_id = apt_id;
					var reschedule_apt_id = apt_id;
					var postData = 
					{ 
						action:'my_appointment_get_service_id_from_appointment_id',
						appointment_id: apt_id,
						get_service_id_data_nonce: vm.bookingpress_created_nonce
					}; 
					
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == 'success'){

							vm.is_rescheduled_loader = false;											
							vm.reschedule_service_id = response.data.service_id;
							vm.reschedule_staff_id = response.data.staff_id;
							vm.appointment_customer_reschedule_booked_date = response.data.appointment_date;
							let appointment_time = response.data.appointment_time;
							let appointment_end_time = response.data.appointment_end_time;
							let bookingpress_service_expiration_date = response.data.bookingpress_service_expiration_date;
							let bookingpress_service_start_date = response.data.bookingpress_service_start_date;
							vm.bookingpress_mybooking_rescheduling_form.appointment_customer_reschedule_booked_time = appointment_time;							
							vm.appointment_customer_reschedule_end_time = appointment_end_time;

							let disablePostData = { action: "bookingpress_get_disable_date", is_rescheduling_event: true, service_id: vm.reschedule_service_id, selected_service: vm.reschedule_service_id, selected_date: vm.appointment_customer_reschedule_booked_date, _wpnonce:vm.bookingpress_created_nonce};
							disablePostData.appointment_data_obj = {
								appointment_update_id:response.data.appointment_update_id,
								bookingpress_selected_staff_member_details: {
									selected_staff_member_id: vm.reschedule_staff_id
								},
								bookingpress_uniq_id:vm.bookingpress_uniq_id, 
							};
							<?php do_action('bookingpress_modify_rescheduled_appointment_xhr_data'); ?>

							if( "undefined" != response.data.bookingpress_selected_extra_details ){
								disablePostData.appointment_data_obj.bookingpress_selected_extra_details = response.data.bookingpress_selected_extra_details;
							}							
							if( "undefined" != response.data.bookingpress_selected_bring_members ){
								disablePostData.appointment_data_obj.bookingpress_selected_bring_members = response.data.bookingpress_selected_bring_members;
							}
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( disablePostData ) )
							.then( function( response ){
								if( response.data.variant == "success" ){									
									let disableDates = response.data.days_off_disabled_dates;
									let disableDates_arr = disableDates.split(',');
									let disableDates_formatted = [];
									disableDates_arr.forEach(function( date ){
										let formatted_date = vm.get_formatted_date( date );
										disableDates_formatted.push( formatted_date );
									});
									vm.pickerOptions.disabledDate = function(Time){
										let currentDate = new Date( Time );										
										currentDate = vm.get_formatted_date( currentDate );										
										var date = new Date();
										date.setDate(date.getDate()-1);										
										if(typeof bookingpress_service_expiration_date !== "undefined" && bookingpress_service_expiration_date != '') {
											var currentDate2 = min_date = vm.get_formatted_date( bookingpress_service_expiration_date);
											
											/* When Period available for booking in advance - days is less than service expiration date then set limit for that*/
											if(bookingpress_service_expiration_date > vm.get_formatted_date(vm.booking_cal_rec_maxdate)){
												min_date = vm.get_formatted_date(vm.booking_cal_rec_maxdate);
											}
											/* When Period available for booking in advance - days is less than service expiration date then set limit for that*/
											if(currentDate > min_date) {
												return true;
											}
										}										
										if(Time>vm.booking_cal_rec_maxdate && typeof bookingpress_service_expiration_date !== "undefined" && bookingpress_service_expiration_date == '' ) {
											return true;
										}
										if( typeof bookingpress_service_start_date != "undefined" && bookingpress_service_start_date != ''){
											var currentDate3 = vm.get_formatted_date( bookingpress_service_start_date );
											if( currentDate < currentDate3 ){
												return true;
											}
										}										
										var disable_past_date = Time.getTime() < date.getTime();
										if( disableDates_formatted.indexOf( currentDate ) > -1 ){
											return true;
										}  else {
											return disable_past_date;
										}
									};
									vm.pickerOptions.firstDayOfWeek = parseInt('<?php echo esc_html($bookingpress_start_of_week); ?>');
									let timeSlot = response.data.front_timings;
									vm.reschedule_appointment_time_slot = timeSlot;
									setTimeout(function(){
										vm.customer_reschedule_appointment_loader = false;
									},300);

									if( false == response.data.prevent_next_month_check ){
										let postDataAction = "bookingpress_get_whole_day_appointments";
										if( true == response.data.check_for_multiple_days_event ){
											postDataAction = "bookingpress_get_whole_day_appointments_multiple_days";
										}
										var postData = { action: postDataAction,days_off_disabled_dates: disableDates, service_id: vm.reschedule_service_id, max_available_year: response.data.max_available_year, max_available_month:response.data.max_available_month,  selected_service:vm.reschedule_service_id, selected_date:vm.appointment_customer_reschedule_booked_date, service_id:vm.reschedule_service_id,_wpnonce:vm.bookingpress_created_nonce, next_month: response.data.next_month,bookingpress_service_expiration_date:bookingpress_service_expiration_date, "counter": 1 };
										vm.bookingpress_mybooking_retrieve_daysoff_for_booked_appointment( postData );
									}
								}
							}
							.bind(this) )
							.catch( function( error ) {
								vm.bookingpress_set_error_msg( error );
								vm.customer_reschedule_appointment_loader = false;
								vm.is_rescheduled_loader = false;
							});
						}
						/*
						setTimeout(function(){
							vm.customer_reschedule_appointment_loader = false;
						},600);						
						*/
					}
					.bind(this) )
					.catch( function (error) {
						vm.customer_reschedule_appointment_loader = false;
						vm.bookingpress_set_error_msg(error)
					});					
				},	
				bookingpress_reset_error_success_msg(){
					const vm = this;
					vm.is_display_error = "0";
					vm.is_error_msg = '';
					vm.is_display_success = "0";
					vm.is_success_msg = '';
				},						
				bookingpress_set_error_msg(error_msg){
					const vm = this;
					vm.bookingpress_reset_error_success_msg();
					vm.is_display_error = "1";
					vm.is_error_msg = error_msg;
					let pos = 0;
					let container = vm.$el;
					if( null != container ){
						pos = container.getBoundingClientRect().top + window.scrollY;
					}
				},
				bookingpress_set_success_msg(success_msg){
					const vm = this;
					vm.bookingpress_reset_error_success_msg();
					vm.is_display_success = "1";
					vm.is_success_msg = success_msg;
				},
				bookingpress_mybooking_retrieve_daysoff_for_booked_appointment( postData ){
					const vm = this;
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) ).then( function( response ) {
						let bookingpress_service_expiration_date = postData.bookingpress_service_expiration_date;						
						if(false == response.data.prevent_next_month_check && response.data.counter < 3 ){ /** Currently data will be checked for next 3 months */
							let disableDates = response.data.days_off_disabled_dates;
							let disableDates_arr = disableDates.split(',');

							let disableDates_formatted = [];
							disableDates_arr.forEach(function( date ){
								let formatted_date = vm.get_formatted_date( date );
								disableDates_formatted.push( formatted_date );
							});
							vm.pickerOptions.disabledDate = function(Time){
								let currentDate = new Date( Time );
								
								currentDate = vm.get_formatted_date( currentDate );
								
								var date = new Date();

								date.setDate(date.getDate()-1);								
								var disable_past_date = Time.getTime() < date.getTime();
								if(typeof bookingpress_service_expiration_date !== "undefined" && bookingpress_service_expiration_date != '') {
									var currentDate2 = min_date = vm.get_formatted_date( bookingpress_service_expiration_date);
									/* When Period available for booking in advance - days is less than service expiration date then set limit for that*/
									if(bookingpress_service_expiration_date > vm.get_formatted_date(vm.booking_cal_rec_maxdate)){
										min_date = vm.get_formatted_date(vm.booking_cal_rec_maxdate);
									}
									/* When Period available for booking in advance - days is less than service expiration date then set limit for that*/
									if(currentDate > min_date) {
										return true;
									}
								}
								/* Period available for booking in advance - days */
								if(Time>vm.booking_cal_rec_maxdate && typeof bookingpress_service_expiration_date !== "undefined" && bookingpress_service_expiration_date == '' ) {
									return true;
								}
								/* Period available for booking in advance - days */
								if( disableDates_formatted.indexOf( currentDate ) > -1 ){
									return true;
								} else {
									return disable_past_date;
								}
							};
							postData.next_month = response.data.next_month;
							postData.counter++;
							vm.bookingpress_mybooking_retrieve_daysoff_for_booked_appointment( postData );
						}
					});
				},

            <?php            
        }  


        /**
         * Function for reschedule appointment
         *
        */
        function bookingpress_appointment_reschedule_func($atts, $content, $tag)  {

            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries,$bookingpress_global_options,$bookingpress_services,$tbl_bookingpress_services,$bookingpress_uniq_id;
            $BookingPress->set_front_css(1);
            $BookingPress->set_front_js(1); 
            $BookingPress->bookingpress_load_booking_form_custom_css();
            $bookingpress_uniq_id = uniqid();
            $appointment_id = 0;

			
			$bookingpress_global_details  = $bookingpress_global_options->bookingpress_global_options();              
			$bpa_time_format_for_timeslot = $bookingpress_global_details['bpa_time_format_for_timeslot'];

			$bookingpress_appointment_reschedule = array();
			$bkp_ap_id = 0;

			$bookingpress_appointment_data = array();

            if(!empty($_GET['appointment_id']) && !empty($_GET['reschedule_token'])){

                $bkp_ap_id = intval(base64_decode($_GET['appointment_id'])); //phpcs:ignore
                $reschedule_token = sanitize_text_field($_GET['reschedule_token']);	

                $bookingpress_appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d",$bkp_ap_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

				$allow_rescheduling = false;
				if(!empty($bookingpress_appointment_data)){

									
					$allow_rescheduling = true;
					$bookingpress_service_duration_unit = (isset($bookingpress_appointment_data['bookingpress_service_duration_unit']))?$bookingpress_appointment_data['bookingpress_service_duration_unit']:'';

					if( 'd' == $bookingpress_service_duration_unit ){
						if( $bookingpress_appointment_data['bookingpress_appointment_date'] <= date('Y-m-d', current_time('timestamp') ) ){
							$allow_rescheduling = false;							
						}						
					}else{
						$appointment_datetime = $bookingpress_appointment_data['bookingpress_appointment_date'] .' '. $bookingpress_appointment_data['bookingpress_appointment_time'];
						$current_datetime = date( 'Y-m-d H:i:s', current_time('timestamp') );						
						if( $appointment_datetime <= $current_datetime ){
							$allow_rescheduling = false;							
						}						
					}

					$bookingpress_min_time_before_reschedule = $BookingPress->bookingpress_get_settings('default_minimum_time_befor_rescheduling', 'general_setting');

					//$bookingpress_min_time_before_reschedule = 'disabled';
					$bookingpress_service_id = (isset($bookingpress_appointment_data['bookingpress_service_id']))?$bookingpress_appointment_data['bookingpress_service_id']:0;
					//Check service level minimum time required before reschedule
					$bookingpress_service_min_time_require_before_reschedule = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'minimum_time_required_before_rescheduling');
					if($bookingpress_service_min_time_require_before_reschedule == 'disabled'){
						$bookingpress_min_time_before_reschedule = 'disabled';
					}else if($bookingpress_service_min_time_require_before_reschedule != 'inherit'){
						$bookingpress_min_time_before_reschedule = $bookingpress_service_min_time_require_before_reschedule;
					}

					/* Check minimum reschedule time */
					if($allow_rescheduling && $bookingpress_min_time_before_reschedule != 'disabled'){
						$bookingpress_from_time = current_time('timestamp');
						$bookingpress_to_time = strtotime($bookingpress_appointment_data['bookingpress_appointment_date'] .' '. $bookingpress_appointment_data['bookingpress_appointment_time']);
						$bookingpress_time_diff_for_cancel = round(abs($bookingpress_to_time - $bookingpress_from_time) / 60, 2);
						if($bookingpress_time_diff_for_cancel < $bookingpress_min_time_before_reschedule){
							$allow_rescheduling = false;
						}
					}
				}



				$bookingpress_reschedule_not_allowed_msg = esc_html__('Reschedule appointment not allowed.', 'bookingpress-appointment-booking');
				if($allow_rescheduling){

					$service_id = !empty($bookingpress_appointment_data['bookingpress_service_id']) ? intval($bookingpress_appointment_data['bookingpress_service_id']) : 0;
					$staff_member_id = !empty($bookingpress_appointment_data['bookingpress_staff_member_id']) ? intval($bookingpress_appointment_data['bookingpress_staff_member_id']) : 0;

					if(!empty($bookingpress_appointment_data) && $bookingpress_appointment_data['bookingpress_appointment_status'] != '3') {

						$bookingpress_global_details     = $bookingpress_global_options->bookingpress_global_options();
						$bookingpress_default_date_time_format = $bookingpress_global_details['wp_default_date_format'] . ' ' . $bookingpress_global_details
						['wp_default_time_format'];
	
						$booked_appointment_datetime = esc_html($bookingpress_appointment_data['bookingpress_appointment_date']) . ' ' . esc_html($bookingpress_appointment_data['bookingpress_appointment_time']);
						$bpa_appointment_booking_id = '#'.esc_html($bookingpress_appointment_data['bookingpress_booking_id']);
						$bpa_appointment_service_name = stripslashes_deep(esc_html($bookingpress_appointment_data['bookingpress_service_name']));
						$booked_appointment_datetime = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booked_appointment_datetime, $bookingpress_appointment_data['bookingpress_appointment_timezone'], $bookingpress_appointment_data );
						$bpa_appointment_date_time = date($bookingpress_default_date_time_format, strtotime($booked_appointment_datetime));

						ob_start();
						$bookingpress_my_appointments_file_url = BOOKINGPRESS_PRO_VIEWS_DIR . '/frontend/reschedule_appointments.php';
						$bookingpress_my_appointments_file_url = apply_filters('bookingpress_change_reschedule_appointmens_shortcode_file_url', $bookingpress_my_appointments_file_url);
						include $bookingpress_my_appointments_file_url;
						$content .= ob_get_clean();
                  
					} else {
						$content .=' 
						<el-main class="bpa-appointment-reschedule_container">                  
							<div class="bpa-front-tmc__summary-content bpa-front-reschedule-content v-cloak-reschedule-hidden"><div class="bpa-reschedule-not-allowed-msg">'.$bookingpress_reschedule_not_allowed_msg.'</div></div>
						</el-main>	
						';
					} 


				}else{
					$content .=' 
					<el-main class="bpa-appointment-reschedule_container">                  
						<div class="bpa-front-tmc__summary-content bpa-front-reschedule-content v-cloak-reschedule-hidden"><div class="bpa-reschedule-not-allowed-msg">'.$bookingpress_reschedule_not_allowed_msg.'</div></div>
					</el-main>	
					';					
				}                

				$bookingpress_cancel_token = '';
                add_action(
                    'wp_footer',
                    function () use ( &$bookingpress_uniq_id,$bkp_ap_id,$bookingpress_cancel_token,$bookingpress_appointment_data ) {
                        global $bookingpress_global_options ,$BookingPress;
                        $requested_module                = 'reschedule_appointment';                        
                        $bookingpress_global_details     = $bookingpress_global_options->bookingpress_global_options();
						$bookingpress_formatted_timeslot = $bookingpress_global_details['bpa_time_format_for_timeslot'];
						$wp_default_date_format = $bookingpress_global_details['wp_default_date_format'];

                        ?>
                        <script>							
                            window.addEventListener('DOMContentLoaded', function() {								
                                <?php do_action('bookingpress_' . $requested_module . '_dynamic_helper_vars'); ?>
                                var app = new Vue({
                                        el: '#bookingpress_appointment_reschedule_form_<?php echo esc_html($bookingpress_uniq_id); ?>',
                                        directives: { <?php do_action('bookingpress_' . $requested_module . '_dynamic_directives'); ?> },
                                        components: { <?php do_action('bookingpress_' . $requested_module . '_dynamic_components'); ?> },
                                    data() {
                                        var bookingpress_return_data = <?php do_action('bookingpress_' . $requested_module . '_dynamic_data_fields',$bookingpress_appointment_data,$bkp_ap_id); ?>;
                                        bookingpress_return_data['bookingpress_load_reschedule_frm'] = '0';
										bookingpress_return_data['bookingpress_uniq_id'] = '<?php echo esc_html($bookingpress_uniq_id); ?>';
                                        bookingpress_return_data['bookingpress_appointment_id'] = '<?php echo intval($bkp_ap_id); ?>';
                                        bookingpress_return_data['bookingpress_cancel_token'] = '<?php echo esc_html($bookingpress_cancel_token); ?>';
                                        return bookingpress_return_data;
                                    },
                                    filters:{

                                    },
                                    beforeCreate(){
                                        this.is_front_appointment_empty_loader = '1';
                                    },
                                    created(){
                                    },
									
									filters: {
										bookingpress_format_date: function(value){
                                        	var default_date_format = '<?php echo esc_html($wp_default_date_format); ?>';
                                        	<?php $bookingpress_site_current_lang_moment_locale = get_locale(); ?>
                                        	return moment(String(value)).locale('<?php echo esc_html($bookingpress_site_current_lang_moment_locale); ?>').format(default_date_format);
										},
										bookingpress_format_time: function(value){
											var default_time_format = '<?php echo esc_html($bookingpress_formatted_timeslot); ?>';
											<?php $bookingpress_site_current_lang_moment_locale = get_locale(); ?>
											return moment(String(value), "HH:mm:ss").locale('<?php echo esc_html($bookingpress_site_current_lang_moment_locale); ?>').format(default_time_format)
										}										                      
									},																		
                                    mounted() { 										
                                     <?php do_action('bookingpress_' . $requested_module . '_dynamic_on_load_methods',$bookingpress_appointment_data,$bkp_ap_id); ?>
                                    },
                                    methods: {
                                    <?php do_action('bookingpress_' . $requested_module . '_dynamic_vue_methods'); ?>
                                    },
                                });
                            });
                        </script>
                        <?php
                    },
                    100
                );
            }
            return do_shortcode($content);
        }

        /**
         * bpa function for reschedule appointment
         *
         * @return void
        */
		public function bookingpress_bpa_get_reschedule_appointment_request_func($user_detail = array()){

			global $BookingPress,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$bookingpress_appointment_bookings,$bookingpress_front_vue_data_fields;
			$result = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

				$user_detail = !empty($user_detail) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $user_detail) : array();
				$service_id = (isset($user_detail['service_id']))?$user_detail['service_id']:'';
				$staffmember_id = (isset($user_detail['staffmember_id']))?$user_detail['staffmember_id']:'';
				$date = (isset($user_detail['date']))?$user_detail['date']:'';
				$start_time = (isset($user_detail['start_time']))?$user_detail['start_time']:'';
				$end_time = (isset($user_detail['end_time']))?$user_detail['end_time']:'';
				$appointment_status = (isset($user_detail['appointment_status']))?$user_detail['appointment_status']:'';
				$appointment_id = (isset($user_detail['appointment_id']))?$user_detail['appointment_id']:'';
				$user_id = (isset($user_detail['user_id']))?$user_detail['user_id']:'';

				$_REQUEST['resche_service_id'] = $service_id;
				$_REQUEST['resche_staff_id'] = $staffmember_id;
				$_REQUEST['resche_date'] = $date;
				$_REQUEST['resche_time'] = $start_time;
				$_REQUEST['resche_end_time'] = $end_time;
				$_REQUEST['resche_apt_status'] = $appointment_status;
				$_REQUEST['resche_apt_id'] = $appointment_id;
				$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
				$_REQUEST['reschedule_save_wpnonce'] = $bookingpress_nonce;
				$_POST = $_REQUEST;
				$bookingpress_response = $this->bookingpress_reschedule_book_appointment_func(true,$user_id);				
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

        /**
         * bpa function for reschedule appointment
         *
         * @return void
        */
		public function bookingpress_bpa_get_reschedule_appointment_data_func($user_detail = array()){

			global $BookingPress,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$bookingpress_appointment_bookings,$bookingpress_front_vue_data_fields;
			$result = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

				$user_detail = !empty($user_detail) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $user_detail) : array();

				$uploaded_file_name = (isset($user_detail['uploaded_file_name']))?$user_detail['uploaded_file_name']:'';
				$_REQUEST['uploaded_file_name'] = $uploaded_file_name;
				$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
				$_REQUEST['get_service_id_data_nonce'] = $bookingpress_nonce;
				$_POST = $_REQUEST;

				$bookingpress_response = $this->bookingpress_my_appointment_get_service_id_from_appointment_id_func(true);				
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

        /**
         * bpa function for remove file
         *
         * @return void
        */
		public function bookingpress_bpa_remove_field_file_upload_func($user_detail = array()){

			global $BookingPress,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$bookingpress_appointment_bookings,$bookingpress_front_vue_data_fields;
			$result = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

				$user_detail = !empty($user_detail) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $user_detail) : array();
				$uploaded_file_name = (isset($user_detail['uploaded_file_name']))?$user_detail['uploaded_file_name']:'';
				$_REQUEST['uploaded_file_name'] = $uploaded_file_name;
				$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
				$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				$_POST = $_REQUEST;
				$bookingpress_response = $this->bookingpress_basic_form_file_remove(true);				
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


        /**
         * bpa function for upload file 
         *
         * @return void
        */
		public function bookingpress_bpa_field_file_upload_func($user_detail = array()){

			global $BookingPress,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$bookingpress_appointment_bookings,$bookingpress_front_vue_data_fields;
			$result = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

				$user_detail = !empty($user_detail) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $user_detail) : array();
				$field_key = (isset($user_detail['field_key']))?$user_detail['field_key']:'';
				$bpa_ref = (isset($user_detail['bpa_ref']))?$user_detail['bpa_ref']:'';
				$file_size = (isset($user_detail['file_size']))?$user_detail['file_size']:'';
				$_REQUEST['field_key'] = $field_key;
				$_REQUEST['bpa_ref'] = $bpa_ref;
				$_REQUEST['file_size'] = $file_size; 
				$bookingpress_nonce = wp_create_nonce('bpa_file_upload_'.$field_key);
				$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				$_POST = $_REQUEST;
				$bookingpress_response = $this->bookingpress_basic_form_file_upload(true);
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

        /**
         * bpa function for appointment booking
         *
         * @return void
        */
		public function bookingpress_bpa_appointment_booking_func($user_detail = array()){

			global $BookingPress,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$bookingpress_appointment_bookings;
			$result = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

				$user_detail = !empty($user_detail) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $user_detail) : array();
				$appointment_details = isset($user_detail['appointment_details']) ? $user_detail['appointment_details'] : '';
				$bookingpress_nonce = isset($user_detail['bookingpress_nonce']) ? $user_detail['bookingpress_nonce'] : '';
				if(!empty($bookingpress_nonce)){
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}else{
					$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;					
				}				
				$_REQUEST['appointment_data'] = $appointment_details;
				$_POST = $_REQUEST;
				$bookingpress_response = $this->bookingpress_book_front_appointment_func(true);

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

		/**
		 * bpa function for cancel appointment
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_cancel_appointment_func($user_detail=array()){			
			global $BookingPress,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings, $bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$bookingpress_email_notifications;			
			$result = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){
				
				$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
				$_REQUEST['_wpnonce'] = $bookingpress_nonce;													
				$appointment_id = isset($user_detail['appointment_id']) ? intval($user_detail['appointment_id']) : '';
				$user_id = isset($user_detail['user_id']) ? intval($user_detail['user_id']) : '';	
				if(!empty($appointment_id)){
					$_REQUEST['cancel_id'] = $appointment_id;
					$bookingpress_response = $bookingpress_appointment_bookings->bookingpress_cancel_appointment(true,$user_id);;
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


		function bookingpress_generate_ics_with_appointment_data_detail(){

            if ( ! empty( $_GET['page'] ) && 'bookingpress_download' == $_GET['page'] && ! empty( $_GET['action'] ) && 'generate_ics_with_appointment_data' == $_GET['action'] ) {

                $nonce = ! empty( $_GET['state'] ) ? sanitize_text_field( $_GET['state'] ) : '';                
                if ( ! wp_verify_nonce( $nonce, 'bookingpress_calendar_ics' ) ) {
					return false;
				}				
				$order_id = (isset($_GET['order_id']))?intval($_GET['order_id']):0;
				$appointment_id = (isset($_GET['appointment_id']))?intval($_GET['appointment_id']):0;
				if ( empty( $order_id ) && empty( $appointment_id ) && $order_id == 0 && $appointment_id == 0 ) {
					return false;
				}

				global $wpdb,$tbl_bookingpress_entries, $tbl_bookingpress_appointment_bookings, $BookingPress, $bookingpress_appointment_bookings;
				if($appointment_id != 0){
					$get_all_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$tbl_bookingpress_appointment_bookings}` WHERE bookingpress_appointment_booking_id = %d", $appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.
				}else{
					$get_all_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$tbl_bookingpress_appointment_bookings}` WHERE bookingpress_order_id = %d", $order_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.					
				}

                if( !empty( $get_all_appointments ) ){
                    $calendar_vevent = '';                    
                    foreach( $get_all_appointments as $appointment_data ){
                        
                        $service_id              = intval( $appointment_data['bookingpress_service_id'] );
						$bookingpress_start_time = $service_start_time = sanitize_text_field( $appointment_data['bookingpress_appointment_time'] );
						$bookingpress_end_time   = sanitize_text_field( $appointment_data['bookingpress_appointment_end_time'] );	
						
						$bookingpress_appointment_date_temp = $appointment_data['bookingpress_appointment_date'];
						if ($bookingpress_end_time === '24:00:00') {
							$bookingpress_appointment_date_temp = date('Y-m-d', strtotime($appointment_data['bookingpress_appointment_date'] . ' +1 day'));
							$bookingpress_end_time = '00:00:00';
						}                        
						$bookingpress_start_time = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_start_time ) );                        
						//$bookingpress_end_time = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_end_time ) );                        
						$bookingpress_end_time = date( 'Ymd', strtotime( $bookingpress_appointment_date_temp ) ) . 'T' . date( 'His', strtotime( $bookingpress_end_time ) );                        
                        //echo $bookingpress_start_time .' ---- ' . $bookingpress_end_time .' - --<br/>';

						$user_timezone             = wp_timezone_string();
						$bookingpress_service_name = ! empty( $appointment_data['bookingpress_service_name'] ) ? sanitize_text_field( $appointment_data['bookingpress_service_name'] ) : '';     
                        $booking_stime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], $bookingpress_start_time );
                        //$booking_etime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date] ,  $bookingpress_end_time);
                        $booking_etime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $bookingpress_appointment_date_temp,  $bookingpress_end_time);
                        $current_dtime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( date( 'm/d/Y' ), 'g:i A' );

                        /* For Day Service Fixed Issue */
                        $bookingpress_service_duration_unit = (isset($appointment_data['bookingpress_service_duration_unit']))?$appointment_data['bookingpress_service_duration_unit']:'';
                        if($bookingpress_service_duration_unit == 'd'){                            
                            $bookingpress_service_duration_val = (isset($appointment_data['bookingpress_service_duration_val']))?$appointment_data['bookingpress_service_duration_val']:'';
                            if($bookingpress_service_duration_val > 1){
                                $booking_stime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], '00:00:00' );
                                $bookingpress_appointment_date_temp = date('Y-m-d',strtotime($bookingpress_appointment_date_temp. ' + '.$bookingpress_service_duration_val.' days'));
                                $booking_etime = $bookingpress_appointment_bookings->bookingpress_convert_date_time_to_utc( $bookingpress_appointment_date_temp,  '24:00:00');
                            }
                        }

                        $calendar_vevent .= "BEGIN:VEVENT\r\n";
                        $calendar_vevent .= 'UID:' . md5( $service_start_time ) . "\r\n";
                        $calendar_vevent .= 'DTSTART:' . $booking_stime . "\r\n";
                        $calendar_vevent .= "SEQUENCE:0\r\n";
                        $calendar_vevent .= "TRANSP:OPAQUE\r\n";
                        $calendar_vevent .= "DTEND:{$booking_etime}\r\n";
                        $calendar_vevent .= "SUMMARY:{$bookingpress_service_name}\r\n";
                        $calendar_vevent .= "CLASS:PUBLIC\r\n";
                        $calendar_vevent .= "DTSTAMP:{$current_dtime}\r\n";
                        $calendar_vevent .= "END:VEVENT\r\n";

                    }

                    $string  = "BEGIN:VCALENDAR\r\n";
                    $string .= "VERSION:2.0\r\n";
                    $string .= 'PRODID:BOOKINGPRESS APPOINTMENT BOOKING\\\\' . get_bloginfo('title') . "\r\n";
                    $string .= "X-PUBLISHED-TTL:P1W\r\n";
                    $string .= $calendar_vevent;
                    $string .= "END:VCALENDAR\r\n";

                    header( 'Content-Type: text/calendar; charset=utf-8' );
					header( 'Content-Disposition: attachment; filename="cal.ics"' );

					echo $string; //phpcs:ignore
                }
                die;



			}
		}

		/**
		 * bpa function for get thankyou page detail
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_get_thankyou_detail_func($user_detail=array()){
			
			global $BookingPress,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$bookingpress_global_options;

			$thankyou_appointments = array();
			$calendar_link = array();
			$result = array('appointment_detail' => $thankyou_appointments,'calendar_link' => $calendar_link);
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

				$thankyou_appointments = array();
				$user_detail = !empty($user_detail) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $user_detail) : array();
				$entry_id = isset($user_detail['entry_id']) ? intval($user_detail['entry_id']) : '';
				$order_id = isset($user_detail['order_id']) ? intval($user_detail['order_id']) : 0;
                $bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
                $bookingpress_default_date_time_format = $bookingpress_global_options_arr['wp_default_date_format'] . ' ' . $bookingpress_global_options_arr['wp_default_time_format'];
				$appointment_data = array();

				if(!empty($entry_id)){

					$appointment_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_entry_id = %d", $entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

					if(!empty($appointment_data)){
						
						$appointment_id = isset($appointment_data[0]['bookingpress_appointment_booking_id'])?$appointment_data[0]['bookingpress_appointment_booking_id']:'';
						$bookingpress_wp_nonce_data = esc_html( wp_create_nonce( 'bookingpress_calendar_ics' ) );
						$calendar_link['google_calendar'] = esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_appointment_data&state=' . esc_html( $bookingpress_wp_nonce_data) . '&appointment_id='.$appointment_id.'&selectedCalendar=google_calendar';
						$calendar_link['yahoo_calendar'] = esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_appointment_data&state=' . esc_html( $bookingpress_wp_nonce_data) . '&appointment_id='.$appointment_id.'&selectedCalendar=yahoo_calendar';
						$calendar_link['outlook_calendar'] = esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_appointment_data&state=' . esc_html( $bookingpress_wp_nonce_data) . '&appointment_id='.$appointment_id.'&selectedCalendar=outlook_calendar';
						$calendar_link['ical_calendar'] = esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_appointment_data&state=' . esc_html( $bookingpress_wp_nonce_data) . '&appointment_id='.$appointment_id.'&selectedCalendar=ical_calendar';

					}

				}else{
					if(!empty($order_id)){

						$appointment_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d", $order_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
						if(!empty($appointment_data)){

							$bookingpress_wp_nonce_data = esc_html( wp_create_nonce( 'bookingpress_calendar_ics' ) );
							$calendar_link['google_calendar'] = esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_appointment_data&state=' . esc_html( $bookingpress_wp_nonce_data ) . '&order_id='.$order_id.'&selectedCalendar=google_calendar';						
							$calendar_link['yahoo_calendar'] = esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_appointment_data&state=' . esc_html( $bookingpress_wp_nonce_data ) . '&order_id='.$order_id.'&selectedCalendar=yahoo_calendar';						
							$calendar_link['outlook_calendar'] = esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_appointment_data&state=' . esc_html( $bookingpress_wp_nonce_data ) . '&order_id='.$order_id.'&selectedCalendar=outlook_calendar';						
							$calendar_link['ical_calendar'] = esc_url( get_home_url() ) . '?page=bookingpress_download&action=generate_ics_with_appointment_data&state=' . esc_html( $bookingpress_wp_nonce_data ) . '&order_id='.$order_id.'&selectedCalendar=ical_calendar';

						}

					}					
				}

				if(!empty($appointment_data)){
					foreach($appointment_data as $appointment_data_key => $appointment_data_val){

						$booked_appointment_datetime = esc_html($appointment_data_val['bookingpress_appointment_date']) . ' ' . esc_html($appointment_data_val['bookingpress_appointment_time']);
						$booked_appointment_datetime = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booked_appointment_datetime, $appointment_data_val['bookingpress_dst_timezone'], $appointment_data_val );
						                      
						$booked_appointment_date = date($bookingpress_default_date_time_format, strtotime($booked_appointment_datetime));
						$customer_firstname = ! empty($appointment_data_val['bookingpress_customer_firstname']) ? $appointment_data_val['bookingpress_customer_firstname'] : '';
						$customer_lastname  = ! empty($appointment_data_val['bookingpress_customer_lastname']) ? $appointment_data_val['bookingpress_customer_lastname'] : '';
						$customer_email     = ! empty($appointment_data_val['bookingpress_customer_email']) ? $appointment_data_val['bookingpress_customer_email'] : '';                
						$customer_fullname = !empty($appointment_data_val['bookingpress_customer_name']) ? $appointment_data_val['bookingpress_customer_name'] : ($customer_firstname . ' ' . $customer_lastname);
						if(empty($appointment_data_val['bookingpress_customer_name']) && empty($customer_firstname) && empty($customer_lastname) ) {
							$customer_fullname = $customer_email;
						}
						$single_appointment = array();
						$single_appointment['bookingpress_service_name'] = $appointment_data_val['bookingpress_service_name'];
						$single_appointment['bookingpress_date_time'] = $booked_appointment_date;
						$single_appointment['customer_fullname'] = $customer_fullname;
						$thankyou_appointments[] = $single_appointment;

					}	
				}

				$result = array('appointment_detail' => $thankyou_appointments,'calendar_link' => $calendar_link);
				$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));


			}
			return $response;
		}


		/**
		 * bpa function for get front timing
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_get_timings_func($user_detail=array()){
			
			global $BookingPress,$tbl_bookingpress_services,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$bookingpress_global_options,$tbl_bookingpress_payment_logs,$tbl_bookingpress_form_fields,$tbl_bookingpress_customers, $bookingpress_appointment_bookings,$bookingpress_services,$bookingpress_appointment_bookings;

			$result = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){
				
				$user_detail = !empty($user_detail) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $user_detail) : array();
				$service_id = isset($user_detail['service_id']) ? intval($user_detail['service_id']) : '';
				$staffmember_id = isset($user_detail['staffmember_id']) ? intval($user_detail['staffmember_id']) : '';
				$selected_date = isset($user_detail['selected_date']) ? sanitize_text_field($user_detail['selected_date']) : '';
				$quantity = isset($user_detail['quantity']) ? intval($user_detail['quantity']) : '';
				$this->bookingpress_bpa_set_disable_date_data_func($user_detail);				
				$_POST = $_REQUEST;
				
				$bookingpress_bpa_timings = $bookingpress_appointment_bookings->bookingpress_retrieve_timeslots( $selected_date , true);
				$bookingpress_check_response = (isset($bookingpress_bpa_timings['variant']))?$bookingpress_bpa_timings['variant']:'';
				if($bookingpress_check_response == 'error'){					
					$message = (isset($bookingpress_bpa_timings['msg']))?$bookingpress_bpa_timings['msg']:'';
					$response = array('status' => 0, 'message' => $message, 'response' => array('result' => $result));					
				}else{
					$result = $bookingpress_bpa_timings;
					$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));					
				}

			}

			return $response;
		}

		
		/**
		 *  bpa function for set_disable_date_data_func
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_set_disable_date_data_func($user_detail=array()){
			global $BookingPressPro,$BookingPress,$bookingpress_pro_staff_members;
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){
				
				$bpa_is_staffmember_module_active = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();

				$user_id = isset($user_detail['user_id']) ? intval($user_detail['user_id']) : '';
				$service_id = isset($user_detail['service_id']) ? intval($user_detail['service_id']) : '';
				$staffmember_id = isset($user_detail['staffmember_id']) ? intval($user_detail['staffmember_id']) : '';
				$selected_date = isset($user_detail['selected_date']) ? sanitize_text_field($user_detail['selected_date']) : '';
				$quantity = isset($user_detail['quantity']) ? intval($user_detail['quantity']) : '';
				$enable_custom_service_duration = isset($user_detail['enable_custom_service_duration']) ? sanitize_text_field($user_detail['enable_custom_service_duration']) : '';
				$custom_service_duration_value = isset($user_detail['custom_service_duration_value']) ? sanitize_text_field($user_detail['custom_service_duration_value']) : '';
				$cart_items = isset($user_detail['cart_items']) ? $user_detail['cart_items'] : array();
				$any_staff_option = isset($user_detail['any_staff_option']) ? $user_detail['any_staff_option'] : '';
				//$recurring_appointment_detail = isset($user_detail['recurring_appointment_detail']) ? $user_detail['recurring_appointment_detail'] : '';
				
				
				$service_duration_unit = isset($user_detail['service_duration_unit']) ? sanitize_text_field($user_detail['service_duration_unit']) : '';
				$service_duration = isset($user_detail['service_duration']) ? sanitize_text_field($user_detail['service_duration']) : '';
				$location_id = isset($user_detail['location_id']) ? intval($user_detail['location_id']) : '';
				
				$bookingpress_nonce = isset($user_detail['bookingpress_nonce']) ? sanitize_text_field($user_detail['bookingpress_nonce']) : '';
				$extra_services = isset($user_detail['extra_services']) ? $user_detail['extra_services'] : '';
				$disabled_dates = isset($user_detail['disabled_dates']) ? $user_detail['disabled_dates'] : '';
				$client_timezone_offset = isset($user_detail['client_timezone_offset']) ? $user_detail['client_timezone_offset'] : '';
				$enable_recurring_appointments = isset($user_detail['enable_recurring_appointments']) ? $user_detail['enable_recurring_appointments'] : '';				
				$recurring_detail = isset($user_detail['recurring_detail']) ? $user_detail['recurring_detail'] : '';
				$recurring_appointments = isset($user_detail['recurring_appointments']) ? $user_detail['recurring_appointments'] : '';
				$recurring_edit_index = isset($user_detail['recurring_edit_index']) ? $user_detail['recurring_edit_index'] : '';
								
				$bpa_login_customer_id = $user_id;
				if(!empty($bookingpress_nonce)){
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}else{
					$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;					
				}
				
				if(!empty($service_id)){
					$_REQUEST['service_id'] = $service_id;
					$_REQUEST['selected_service'] = $service_id;
				}
				if(!empty($selected_date)){
					$_REQUEST['selected_date'] = $selected_date;
				}
				if($bpa_is_staffmember_module_active){
					if(!empty($staffmember_id)){
						$_REQUEST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] = $staffmember_id;
						$_REQUEST['appointment_data_obj']['selected_staff_member_id'] = $staffmember_id;
					}	
					if($any_staff_option == 'true'){
						$_REQUEST['appointment_data_obj']['select_any_staffmember'] = 'true';
					}
				}
				if(!empty($quantity)){
					$_REQUEST['appointment_data_obj']['bookingpress_selected_bring_members'] = $quantity;
				}
				if(isset($user_detail['custom_service_duration_value'])){
					$_REQUEST['custom_service_duration_value'] = $quantity;
				}
				if(isset($user_detail['enable_custom_service_duration'])){
					$_REQUEST['enable_custom_service_duration'] = $enable_custom_service_duration;
				}

				$_REQUEST['action'] = 'bookingpress_get_disable_date';
				if(!empty($service_duration_unit)){
					$_REQUEST['appointment_data_obj']['selected_service_duration_unit']  = $service_duration_unit;
				}
				if(!empty($service_duration)){
					$_REQUEST['appointment_data_obj']['selected_service_duration']  = $service_duration;
				}
				if(!empty($location_id)){
					$_REQUEST['appointment_data_obj']['selected_location'] = $location_id;
				}								
				if(!empty($enable_custom_service_duration)){
					$_REQUEST['appointment_data_obj']['enable_custom_service_duration'] = $enable_custom_service_duration;
				}
				if(!empty($custom_service_duration_value)){
					$_REQUEST['appointment_data_obj']['custom_service_duration_value'] = $custom_service_duration_value;
				}
				if(!empty($disabled_dates)){
					$_REQUEST['disabled_dates'] = $disabled_dates;
				}
				if(!empty($extra_services)){
					$_REQUEST['appointment_data_obj']['bookingpress_selected_extra_details'] = $extra_services;
				}
				if(!empty($enable_recurring_appointments)){
					$_REQUEST['appointment_data_obj']['is_recurring_appointments'] = "true";
					$_REQUEST['appointment_data_obj']['recurring_form_data'] = $recurring_detail;
				}
				if(!empty($location_id)){
					$_REQUEST['appointment_data_obj']['selected_location'] = $location_id;
					$_REQUEST['bookingpress_location_id'] = $location_id;
				}
				if(!empty($client_timezone_offset)){
					$_REQUEST['client_timezone_offset'] = $client_timezone_offset;
				}
				if(!empty($recurring_appointments)){
					$_REQUEST['recurring_appointments'] = $recurring_appointments;
					if($recurring_edit_index != ''){
						$_REQUEST['recurring_edit_index'] = $recurring_edit_index;
					}
				}

				
			}
		}

		/**
		 * bpa function for get next month disable date
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_get_disable_date_for_month_func($user_detail=array()){
			
			global $BookingPress,$tbl_bookingpress_services,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$bookingpress_global_options,$tbl_bookingpress_payment_logs,$tbl_bookingpress_form_fields,$tbl_bookingpress_customers, $bookingpress_appointment_bookings,$bookingpress_services;

			$result = array();			
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){								

				$user_detail = !empty($user_detail) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $user_detail) : array();
				$service_id = isset($user_detail['service_id']) ? intval($user_detail['service_id']) : '';
				$staffmember_id = isset($user_detail['staffmember_id']) ? intval($user_detail['staffmember_id']) : '';
				$selected_date = isset($user_detail['selected_date']) ? sanitize_text_field($user_detail['selected_date']) : '';
				$quantity = isset($user_detail['quantity']) ? intval($user_detail['quantity']) : '';
				
				$max_available_year = isset($user_detail['max_available_year']) ? $user_detail['max_available_year'] : '';
				$max_available_month = isset($user_detail['max_available_month']) ? $user_detail['max_available_month'] : '';
				$next_month = isset($user_detail['next_month']) ? $user_detail['next_month'] : '';
				$next_year = isset($user_detail['next_year']) ? $user_detail['next_year'] : '';
				$counter = isset($user_detail['counter']) ? $user_detail['counter'] : 1;

				$this->bookingpress_bpa_set_disable_date_data_func($user_detail);

				if(!empty($max_available_year)){
					$_REQUEST['max_available_year'] = $max_available_year;
				}
				if(!empty($max_available_month)){
					$_REQUEST['max_available_month'] = $max_available_month;
				}
				if(!empty($next_month)){
					$_REQUEST['next_month'] = $next_month;
				}				
				if(!empty($next_year)){
					$_REQUEST['next_year'] = $next_year;
				}				
				if(!empty($counter)){
					$_REQUEST['counter'] = $counter;
				}
				$_POST = $_REQUEST;							
				
				if($service_duration_unit == 'd'){
					$bookingpress_get_disable_date_response = $this->bookingpress_get_whole_day_appointment_multipe_days_func(true);
				}else{
					$bookingpress_get_disable_date_response = $bookingpress_appointment_bookings->bookingpress_get_whole_day_appointments_func(true);
				}
				$bookingpress_check_response = (isset($bookingpress_get_disable_date_response['variant']))?$bookingpress_get_disable_date_response['variant']:'';
				if($bookingpress_check_response == 'error'){					
					$message = (isset($bookingpress_get_disable_date_response['msg']))?$bookingpress_get_disable_date_response['msg']:'';
					$response = array('status' => 0, 'message' => $message, 'response' => array('result' => $result));					
				}else{
					$result = $bookingpress_get_disable_date_response;
					$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));					
				}

			}

			return $response;
		}


		/**
		 * bpa function for get current month disable date
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_get_disable_date_func($user_detail=array()){
			
			global $BookingPress,$tbl_bookingpress_services,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$bookingpress_global_options,$tbl_bookingpress_payment_logs,$tbl_bookingpress_form_fields,$tbl_bookingpress_customers, $bookingpress_appointment_bookings,$bookingpress_services,$bookingpress_pro_staff_members;
			$result = array();						
			//$result["customer_form_fields"] = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));

			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){
				
				$user_detail = ! empty($user_detail) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $user_detail) : array();				
				$user_id = isset($user_detail['user_id']) ? intval($user_detail['user_id']) : '';
				$service_id = isset($user_detail['service_id']) ? intval($user_detail['service_id']) : '';
				$staffmember_id = isset($user_detail['staffmember_id']) ? intval($user_detail['staffmember_id']) : '';
				$selected_date = isset($user_detail['selected_date']) ? sanitize_text_field($user_detail['selected_date']) : '';
				$quantity = isset($user_detail['quantity']) ? intval($user_detail['quantity']) : '';
				$enable_custom_service_duration = isset($user_detail['enable_custom_service_duration']) ? sanitize_text_field($user_detail['enable_custom_service_duration']) : '';
				$custom_service_duration_value = isset($user_detail['custom_service_duration_value']) ? sanitize_text_field($user_detail['custom_service_duration_value']) : '';
				$cart_items = isset($user_detail['cart_items']) ? $user_detail['cart_items'] : array();
				$bpa_login_customer_id = $user_id;
				$this->bookingpress_bpa_set_disable_date_data_func($user_detail);

				/*
				if(!empty($service_id)){
					$_REQUEST['service_id'] = $service_id;
					$_REQUEST['selected_service'] = $service_id;
				}
				if(!empty($selected_date)){
					$_REQUEST['selected_date'] = $selected_date;
				}
				if($bpa_is_staffmember_module_active){
					if(!empty($staffmember_id)){
						$_REQUEST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] = $staffmember_id;
						$_REQUEST['appointment_data_obj']['selected_staff_member_id'] = $staffmember_id;
					}	
					if($any_staff_option == 'true'){
						$_REQUEST['appointment_data_obj']['select_any_staffmember'] = 'true';
					}
				}
				if(!empty($quantity)){
					$_REQUEST['appointment_data_obj']['bookingpress_selected_bring_members'] = $quantity;
				}
				if(isset($user_detail['custom_service_duration_value'])){
					$_REQUEST['custom_service_duration_value'] = $quantity;
				}
				if(isset($user_detail['enable_custom_service_duration'])){
					$_REQUEST['enable_custom_service_duration'] = $enable_custom_service_duration;
				}
				if(!empty($bookingpress_nonce)){
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}else{
					$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;					
				}
				$_REQUEST['action'] = 'bookingpress_get_disable_date';
				if(!empty($service_duration_unit)){
					$_REQUEST['appointment_data_obj']['selected_service_duration_unit']  = $service_duration_unit;
				}
				if(!empty($service_duration)){
					$_REQUEST['appointment_data_obj']['selected_service_duration']  = $service_duration;
				}
				if(!empty($location_id)){
					$_REQUEST['appointment_data_obj']['selected_location'] = $location_id;
				}								
				if(!empty($enable_custom_service_duration)){
					$_REQUEST['appointment_data_obj']['enable_custom_service_duration'] = $enable_custom_service_duration;
				}
				if(!empty($custom_service_duration_value)){
					$_REQUEST['appointment_data_obj']['custom_service_duration_value'] = $custom_service_duration_value;
				}
				if(!empty($disabled_dates)){
					$_REQUEST['disabled_dates'] = $disabled_dates;
				}
				if(!empty($extra_services)){
					$_REQUEST['appointment_data_obj']['bookingpress_selected_extra_details'] = $extra_services;
				}
				if(!empty($enable_recurring_appointments)){
					$_REQUEST['appointment_data_obj']['is_recurring_appointments'] = "true";
					$_REQUEST['appointment_data_obj']['recurring_form_data'] = $recurring_detail;
				}
				if(!empty($location_id)){
					$_REQUEST['appointment_data_obj']['selected_location'] = $location_id;
				}
				if(!empty($client_timezone_offset)){
					$_REQUEST['client_timezone_offset'] = $client_timezone_offset;
				}
				*/
				$_POST = $_REQUEST;

				$bookingpress_get_disable_date_response = array();			
				$waiting_list_active = is_plugin_active('bookingpress-waiting-list/bookingpress-waiting-list.php');
				$waiting_list_service = false;
				if($waiting_list_active){
					$bookingpress_waiting_list_max_slot = $bookingpress_services->bookingpress_get_service_meta($service_id, 'waiting_list_max_slot');
					if( !empty( $bookingpress_waiting_list_max_slot ) ){
						$waiting_list_service = true;
					}
				}							
				if('true' == $enable_custom_service_duration && empty($custom_service_duration_value) && is_plugin_active('bookingpress-custom-service-duration/bookingpress-custom-service-duration.php') && class_exists('bookingpress_custom_service_duration')){					
					global $bookingpress_custom_service_duration;
					if (method_exists( $bookingpress_custom_service_duration, 'bookingpress_get_disable_dates_with_custom_service_duration')) {	
						$bookingpress_get_disable_date_response = $bookingpress_custom_service_duration->bookingpress_get_disable_dates_with_custom_service_duration(true);
					}					
				}else if($waiting_list_service && class_exists('bookingpress_waiting_list')){
					global $bookingpress_waiting_list;
					if (method_exists( $bookingpress_waiting_list, 'bookingpress_get_disable_date_func_with_waiting_list')) {
						$bookingpress_get_disable_date_response = $bookingpress_waiting_list->bookingpress_get_disable_date_func_with_waiting_list(true);
					}
				}else if($service_duration_unit == 'd'){
					$bookingpress_get_disable_date_response = $this->bookingpress_get_disable_dates_for_days_services(true);
				}else{
					$bookingpress_get_disable_date_response = $bookingpress_appointment_bookings->bookingpress_get_disable_date_func_optimized(true);
				}
				$bookingpress_check_response = (isset($bookingpress_get_disable_date_response['variant']))?$bookingpress_get_disable_date_response['variant']:'';
				if($bookingpress_check_response == 'error'){					
					$message = (isset($bookingpress_get_disable_date_response['msg']))?$bookingpress_get_disable_date_response['msg']:'';
					$response = array('status' => 0, 'message' => $message, 'response' => array('result' => $result));					
				}else{
					$result = $bookingpress_get_disable_date_response;

					$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));					
				}




			}

			return $response;
		}


		/**
		 * bpa function for get user appointment list
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_get_edit_profile_fields_func($user_detail=array()){
			
			global $BookingPress,$tbl_bookingpress_services,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$bookingpress_global_options,$tbl_bookingpress_payment_logs,$tbl_bookingpress_form_fields,$tbl_bookingpress_customers;

			$result = array();						
			$result["customer_form_fields"] = array();				

			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));

			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

				$user_id = isset($user_detail['user_id']) ? intval($user_detail['user_id']) : '';
				$bpa_login_customer_id = $user_id;					
				if($user_id){

					$bookingpress_get_customer_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id =%d", $bpa_login_customer_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm						
					if(empty($bookingpress_get_customer_details)){
						$bookingpress_get_customer_details = array();
						$bookingpress_current_user_obj = get_user_by('id', $bpa_login_customer_id);
						$bookingpress_customer_email = ! empty($bookingpress_current_user_obj->data->user_email) ? $bookingpress_current_user_obj->data->user_email : '';
						$bookingpress_get_customer_details['bookingpress_user_firstname'] = stripslashes_deep(get_user_meta($bpa_login_customer_id, 'first_name', true));
						$bookingpress_get_customer_details['bookingpress_user_lastname'] = stripslashes_deep(get_user_meta($bpa_login_customer_id, 'last_name', true));
						$bookingpress_get_customer_details['bookingpress_user_email'] = stripslashes_deep( $bookingpress_customer_email );
					}
					
					$bookingpress_current_user_id      = ! empty( $bookingpress_get_customer_details['bookingpress_customer_id'] ) ? $bookingpress_get_customer_details['bookingpress_customer_id'] : 0;
					$bookingpress_form_fields  = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_form_fields . ' ORDER BY bookingpress_is_customer_field,bookingpress_field_position ASC', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
					$bookingpress_form_fields_error_msg_arr = $bookingpress_form_fields_new = array();
					foreach ( $bookingpress_form_fields as $bookingpress_form_field_key => $bookingpress_form_field_val ) {

						$bookingpress_field_options = ! empty( $bookingpress_form_field_val['bookingpress_field_options'] ) ? json_decode( $bookingpress_form_field_val['bookingpress_field_options'], true ) : '';			
						$bookingpress_customer_field = ( ( ! empty( $bookingpress_field_options['used_for_user_information'] ) && $bookingpress_field_options['used_for_user_information'] == 'true' ) || $bookingpress_form_field_val['bookingpress_is_customer_field'] );			
						if ( ( $bookingpress_customer_field && ! empty( $bookingpress_field_options['visibility'] ) && $bookingpress_field_options['visibility'] == 'always' && $bookingpress_form_field_val['bookingpress_form_field_name'] != 'note' && $bookingpress_form_field_val['bookingpress_form_field_name'] != 'fullname' ) ) {
		
							$bookingpress_v_model_value = '';
							if ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'firstname' ) {
								$bookingpress_v_model_value = 'customer_firstname';
							} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'lastname' ) {
								$bookingpress_v_model_value = 'customer_lastname';
							} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'email_address' ) {
								$bookingpress_v_model_value = 'customer_email';
							} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'phone_number' ) {
								$bookingpress_v_model_value = 'customer_phone';
							} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'username' ) {
								$bookingpress_v_model_value = 'customer_username';
							} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'terms_and_conditions' ) {
								$bookingpress_v_model_value = 'appointment_terms_conditions';
							}

							$bookingpress_field_type = '';
							if ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'firstname' ) {
								$bookingpress_field_type = 'Text';
							} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'lastname' ) {
								$bookingpress_field_type = 'Text';
							} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'email_address' ) {
								$bookingpress_field_type = 'Email';
							} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'username' ) {
								$bookingpress_field_type = 'Text';
							} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'phone_number' ) {
								$bookingpress_field_type = 'Dropdown';
							} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'terms_and_conditions' ) {
								$bookingpress_field_type = 'terms_and_conditions';
							} else {
								$bookingpress_field_type = $bookingpress_form_field_val['bookingpress_field_type'];
							}
		
							if( 1 == $bookingpress_form_field_val['bookingpress_is_customer_field'] ){
								$bookingpress_field_type = ucfirst( $bookingpress_field_type );
							}
							$bookingpress_field_setting_fields_tmp                   = array();
							$bookingpress_field_setting_fields_tmp['id']             = intval( $bookingpress_form_field_val['bookingpress_form_field_id'] );
							$bookingpress_field_setting_fields_tmp['field_name']     = $bookingpress_form_field_val['bookingpress_form_field_name'];
							$bookingpress_field_setting_fields_tmp['field_type']     = $bookingpress_field_type;
							$bookingpress_field_setting_fields_tmp['is_edit']        = false;
							$bookingpress_field_setting_fields_tmp['is_required']    = ( $bookingpress_form_field_val['bookingpress_field_required'] == 0 ) ? false : true;
							$bookingpress_field_setting_fields_tmp['label']          = stripslashes_deep($bookingpress_form_field_val['bookingpress_field_label']);
							$bookingpress_field_setting_fields_tmp['placeholder']    = stripslashes_deep( $bookingpress_form_field_val['bookingpress_field_placeholder']);
							$bookingpress_field_setting_fields_tmp['error_message']  = stripslashes_deep($bookingpress_form_field_val['bookingpress_field_error_message']);
							$bookingpress_field_setting_fields_tmp['is_hide']        = ( $bookingpress_form_field_val['bookingpress_field_is_hide'] == 0 ) ? false : true;
							$bookingpress_field_setting_fields_tmp['field_position'] = floatval( $bookingpress_form_field_val['bookingpress_field_position'] );
							$bookingpress_field_setting_fields_tmp['v_model_value']  = $bookingpress_v_model_value;						
							unset($bookingpress_field_setting_fields_tmp['is_edit']);
							$bookingpress_field_setting_fields_tmp = apply_filters( 'bookingpress_arrange_form_fields_outside', $bookingpress_field_setting_fields_tmp, $bookingpress_form_field_val );
							if( 1 == $bookingpress_form_field_val['bookingpress_is_customer_field'] ){
								$bookingpress_field_setting_fields_tmp['is_hide'] = 0;
								$bookingpress_field_setting_fields_tmp['field_options']['layout'] = '1col';
							}							
							$bpa_get_customer_field_type = (isset($bookingpress_field_setting_fields_tmp['field_type']))?$bookingpress_field_setting_fields_tmp['field_type']:'';
							$key = (isset($bookingpress_field_setting_fields_tmp['v_model_value']))?$bookingpress_field_setting_fields_tmp['v_model_value']:'';

							$bookingpress_field_setting_fields_tmp['value'] = '';
							if ( $key == 'customer_firstname' ) {
								$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_firstname'] ) ? stripslashes_deep($bookingpress_get_customer_details['bookingpress_user_firstname']) : '';
							} elseif ( $key == 'customer_lastname' ) {
								$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_lastname'] ) ? stripslashes_deep($bookingpress_get_customer_details['bookingpress_user_lastname']) : '';
							} elseif ( $key == 'customer_phone' ) {
								$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_phone'] ) ? $bookingpress_get_customer_details['bookingpress_user_phone'] : '';
							} elseif ( $key == 'customer_email' ) {
								$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_email'] ) ? $bookingpress_get_customer_details['bookingpress_user_email'] : '';
							} elseif ( $key == 'customer_username' ) {
								$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_name'] ) ? $bookingpress_get_customer_details['bookingpress_user_name'] : '';
							} elseif ( $key == 'customer_phone_country' ) {
								$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_country_phone'] ) ? $bookingpress_get_customer_details['bookingpress_user_country_phone'] : '';
							} else {
								$bookingpress_field_value = $BookingPress->get_bookingpress_customersmeta( $bookingpress_current_user_id, $key );
							}
							$bookingpress_field_setting_fields_tmp['value'] = stripslashes_deep($bookingpress_field_value);
															
							if( 'Checkbox' == $bpa_get_customer_field_type ){						
								$field_values = json_decode( $bookingpress_field_value, true );					
								if(empty($field_values)){
									$field_values = array();
								}
								$bookingpress_field_setting_fields_tmp['value'] = $field_values;
							}								


							array_push( $bookingpress_form_fields_new, $bookingpress_field_setting_fields_tmp );

							if ( $bookingpress_form_field_val['bookingpress_field_required'] == '1' ) {
								$bookingpress_error_msg = !empty($bookingpress_field_setting_fields_tmp['error_message']) ? stripslashes_deep($bookingpress_field_setting_fields_tmp['error_message']) : '' ;
								$bookingpress_error_msg = empty($bookingpress_error_msg) && !empty($bookingpress_form_field_val['bookingpress_field_label']) ?stripslashes_deep($bookingpress_form_field_val['bookingpress_field_label']).' '.__('is required','bookingpress-appointment-booking') : $bookingpress_error_msg;		
								$bookingpress_field_setting_fields_tmp['all_error_message'][] = array(
									'type' => 'required',
									'message'  => $bookingpress_error_msg,
								);
								if( $bookingpress_v_model_value == 'appointment_terms_conditions') {
									$bookingpress_field_setting_fields_tmp['all_error_message'][] = array(
										'type' => 'required',
										'message'  => $bookingpress_error_msg,										
									);
								}
							}						
							if(!empty($bookingpress_field_setting_fields_tmp['field_options']['minimum'])) {
								$bookingpress_field_setting_fields_tmp['all_error_message'][] = array( 
									'type' => 'min',
									'min' => intval($bookingpress_field_setting_fields_tmp['field_options']['minimum']),
									'message'  => __('Minimum','bookingpress-appointment-booking').' '.$bookingpress_field_setting_fields_tmp['field_options']['minimum'].' '.__('character required','bookingpress-appointment-booking'),									
								);
							}
							if(!empty($bookingpress_field_setting_fields_tmp['field_options']['maximum'])) {
								$bookingpress_field_setting_fields_tmp['all_error_message'][] = array( 
									'type' => 'max',
									'max' => intval($bookingpress_field_setting_fields_tmp['field_options']['maximum']),
									'message'  => __('Maximum','bookingpress-appointment-booking').' '.$bookingpress_field_setting_fields_tmp['field_options']['maximum'].' '.__('character allowed','bookingpress-appointment-booking'),									
								);
							}

						}
					}
					
					$result["customer_form_fields"] = $bookingpress_form_fields_new;																
					$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));

				}
			
			}

			return $response;
		}

		/**
		 * bpa function for get user appointment list
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_get_customer_appointments($user_detail=array()){
			
			global $BookingPress,$wpdb,$BookingPressPro,$bookingpress_appointment_bookings;

			$result = array();			
			$result["items"] = array();
			$result["customer_details"] = array();
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));
			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

				$bookingpress_nonce = isset($user_detail['bookingpress_nonce']) ? sanitize_text_field($user_detail['bookingpress_nonce']) : '';
				$user_id = isset($user_detail['user_id']) ? intval($user_detail['user_id']) : '';
				$search_filter = isset($user_detail['search_filter']) ? $user_detail['search_filter'] : '';				
				$perpage     = isset($user_detail['per_page']) ? intval($user_detail['per_page']) : 10;
				$currentpage = isset($user_detail['current_page']) ? intval($user_detail['current_page']) : 1;
				$offset      = (!empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
				$bpa_login_customer_id = $user_id;
				if(!empty($bookingpress_nonce)){
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}else{
					$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}
                $_REQUEST['perpage'] = $perpage;
                $_REQUEST['currentpage'] = $currentpage;				
                if(!empty($search_filter)){
                    $_REQUEST['search_data'] = $search_filter;
                }                               
                $_POST = $_REQUEST;
                $bookingpress_response = $bookingpress_appointment_bookings->bookingpress_get_customer_appointments_func(true,$user_id);
				$bookingpress_check_response = (isset($bookingpress_response['variant']))?$bookingpress_response['variant']:'';
				if($bookingpress_check_response == 'error'){					
					$message = (isset($bookingpress_response['msg']))?$bookingpress_response['msg']:'';
					$response = array('status' => 0, 'message' => $message, 'response' => array('result' => $result));					
				}else{
					$result = $bookingpress_response;
                    $items = (isset($bookingpress_response['items']))?$bookingpress_response['items']:array();					
                    if(!empty($items)){
                        foreach($items as $k => $item_val){								
							foreach($items[$k] as $newkey => $newval){																	
                            	if(gettype($newval) === 'string' && is_array(json_decode($newval,true))){
                                	$items[$k][$newkey] = json_decode($newval,true);
                            	}
							}
                        }
                        $result['items'] = $items;
                    } 					
					$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));					
				}
			}			
			return $response;
		}

		function bookingpress_selected_gateway_label_name_func($payment_gateway_label, $payment_gateway){
			global $BookingPress;
			$payment_gateway_label_temp = $payment_gateway_label;
			if(!empty($payment_gateway) && ($payment_gateway == 'on-site' || $payment_gateway == 'on site') ) {
                $payment_gateway_label = $BookingPress->bookingpress_get_customize_settings('locally_text','booking_form');
            } elseif(!empty($payment_gateway) && $payment_gateway != 'manual') {
				$payment_gateway_label = $BookingPress->bookingpress_get_customize_settings($payment_gateway.'_text','booking_form');
                if(empty($payment_gateway_label)) {
					$payment_gateway_label = $payment_gateway_label_temp;
				}
			}  
			return $payment_gateway_label;
		}

		function bookingpress_get_disable_dates_for_days_services($return_data=false){
			$start_ms = microtime( true );
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs;
            $response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');

            if (! $bpa_verify_nonce_flag ) {
                $response['variant']      = 'error';
                $response['title']        = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']          = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                $response['redirect_url'] = '';
				if($return_data){
					return $response;	
				}
                wp_send_json($response);
                die();
            }

            $response['variant']    = 'error';
            $response['title']      = 'Error';
            $response['msg']        = 'Something went wrong....';

            //$consider_selected_date = false;

            if( !empty( $_POST['appointment_data_obj'] ) && !is_array( $_POST['appointment_data_obj'] ) ){
                $_POST['appointment_data_obj'] = json_decode( stripslashes_deep( $_POST['appointment_data_obj'] ), true ); //phpcs:ignore
                $_REQUEST['appointment_data_obj'] = $_POST['appointment_data_obj'] =  !empty($_POST['appointment_data_obj']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['appointment_data_obj'] ) : array(); // phpcs:ignore
            }

            $bookingpress_appointment_data = !empty($_POST['appointment_data_obj']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data_obj'] ) : array(); // phpcs:ignore
			
			$selected_service_duration_unit = (isset($bookingpress_appointment_data['selected_service_duration_unit']))?$bookingpress_appointment_data['selected_service_duration_unit']:'';
			
			if( 'd' == $selected_service_duration_unit){
				global $bookingpress_appointment_bookings;
				/** remove action registered from the lite version */
				remove_action( 'wp_ajax_bookingpress_get_disable_date', array( $bookingpress_appointment_bookings, 'bookingpress_get_disable_date_func' ) );
				remove_action( 'wp_ajax_nopriv_bookingpress_get_disable_date', array( $bookingpress_appointment_bookings, 'bookingpress_get_disable_date_func' ) );

				$bookingpress_selected_date = !empty($_REQUEST['selected_date']) ? sanitize_text_field($_REQUEST['selected_date']) : '';

				$selected_service_duration_unit = $bookingpress_appointment_data['selected_service_duration_unit'];
				$selected_service_duration = $bookingpress_appointment_data['selected_service_duration'];

				if(!empty($bookingpress_selected_date)){
					$bookingpress_selected_date = date('Y-m-d', strtotime($bookingpress_selected_date));
				}
				
				if( "NaN-NaN-NaN" == $bookingpress_selected_date || '1970-01-01' == $bookingpress_selected_date || !preg_match( '/(\d{4}\-\d{2}\-\d{2})/', $bookingpress_selected_date ) ){
					$bookingpress_selected_date = date('Y-m-d', current_time('timestamp') );
				}
				
				$bookingpress_selected_service = !empty($_REQUEST['selected_service']) ? intval($_REQUEST['selected_service']) : '';
			
				if(empty($bookingpress_selected_service)){
					$bookingpress_selected_service = $bookingpress_appointment_data['selected_service'];
				}

				/** get maximum period available from booking */
				$get_period_available_for_booking = $BookingPress->bookingpress_get_settings('period_available_for_booking', 'general_setting');

				
				if( !empty( $bookingpress_selected_service ) ){
					
					$response['prevent_next_month_check'] = false;
					
					$bookingpress_start_date = date('Y-m-d', current_time('timestamp') );
					
					/** Modify get available time of booking if the service expiration time is set */
					$get_period_available_for_booking = apply_filters( 'bookingpress_modify_max_available_time_for_booking', $get_period_available_for_booking, $bookingpress_start_date, $bookingpress_selected_service );

					$bookingpress_start_date_with_time = date('Y-m-d H:i:s', current_time( 'timestamp') );

					/** apply filter to modify start date. in case of Minimum Time Required Booking */
					$bookingpress_start_date = apply_filters( 'bookingpress_modify_disable_date_start_date', $bookingpress_start_date, $bookingpress_selected_service, $bookingpress_start_date_with_time );

					$bookingpress_temp_end_date = date('Y-m-d', strtotime('last day of this month', strtotime( $bookingpress_start_date )));

					$bookingpress_end_date = date('Y-m-d', strtotime( '+' . $get_period_available_for_booking . ' days') );

					$next_month = date( 'm', strtotime( $bookingpress_temp_end_date . '+1 day' ) );
					$next_year = date( 'Y', strtotime( $bookingpress_temp_end_date . '+1 day' ) );
					
					$bookingpress_selected_staffmember_id = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? intval($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) : '';

					/** Get the default days off in the above limit */
					//$bpa_retrieves_default_disabled_dates = $BookingPress->bookingpress_retrieve_off_days( $bookingpress_start_date, ( $get_period_available_for_booking + 1 ), $bookingpress_selected_service, '', $bookingpress_selected_staffmember_id );

					/** loop through each days until the limit has been reached
					 * for lite - it'll check for the next 365 days
					 * for pro - it'll check up to the X number of days defined in the settings
					 */
					$bpa_begin_date = new DateTime( $bookingpress_start_date );
					$bpa_end_date = new DateTime( date('Y-m-d', strtotime($bookingpress_end_date . '+1 day')) );
					
					$bpa_interval = DateInterval::createFromDateString('1 day');
					$period = new DatePeriod($bpa_begin_date, $bpa_interval, $bpa_end_date);

					//$bookingpress_selected_date = $bookingpress_end_date;
					$front_timings_data = array();

					$count = 0;
					$stop_date = '';

					$multiple_day_response = array();
					$multiple_day_response = apply_filters( 'bookingpress_get_multiple_days_disable_dates', $multiple_day_response, $bookingpress_selected_date, $bookingpress_selected_service, $bookingpress_appointment_data );
					
					if($return_data){
						return $multiple_day_response;	
					}
					echo wp_json_encode( $multiple_day_response );
					die;
				}

			} 


		}

		function bookingpress_username_with_password_validation_func( $bookingpress_password_check_for_username ){

			$bookingpress_password_check_for_username .='
				if( vm.appointment_step_form_data.bpa_user_email_already_exists == true && ( "undefined" != typeof vm.appointment_step_form_data.form_fields["customer_email"] && vm.appointment_step_form_data.form_fields["customer_email"] == "") ){

					vm.appointment_step_form_data.bpa_password_already_exists = false;
					vm.customer_details_rule.customer_password[0].required = true;
					vm.appointment_step_form_data.bpa_user_email_already_exists = false;
				}

			';

			return $bookingpress_password_check_for_username;
		}

		function bookingpress_password_validation_arr(){

			global $tbl_bookingpress_form_fields, $wpdb, $bookingpress_front_vue_data_fields; 

			$response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant']      = 'error';
                $response['title']        = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']          = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                $response['redirect_url'] = '';
                return wp_json_encode($response);
                
            }
			$bpa_email = !empty( $_REQUEST['_email']) ? sanitize_email($_REQUEST['_email']) : '';

			$check_password_field = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_field_type) FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_field_type = %s AND bookingpress_field_is_hide = %d", 'password',0)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm 
			
			if( !empty($check_password_field) && (empty( $bookingpress_front_vue_data_fields['appointment_step_form_data']['bpa_user_email_already_exists'] ) || $bookingpress_front_vue_data_fields['appointment_step_form_data']['bpa_user_email_already_exists'] == false) && !empty($bpa_email) ){

				$response['bpa_password_already_exists'] = false;				

				if( $bookingpress_is_wp_user_exist = get_user_by('email', $bpa_email) ){
					if (empty($bookingpress_is_wp_user_exist) ) {
						$response['bpa_password_already_exists'] = false;
						echo json_encode( $response );
					} else {
						$response['bpa_password_already_exists'] = true;
						echo json_encode( $response );
					}
				}
			}

			die;
		}

		function bookingpress_front_check_password_validation_arr( $bookingpress_password_check_data ){

			$bookingpress_password_check_data .='
				if( "undefined" != typeof vm.appointment_step_form_data.form_fields.customer_password ){
					if( "undefined" != typeof response.data.bpa_password_already_exists ){
						if( response.data.bpa_password_already_exists == true ){
							vm.appointment_step_form_data.bpa_password_already_exists = true;
							vm.customer_details_rule.customer_password[0].required = false;
							vm.appointment_step_form_data.bpa_user_email_already_exists = true;
							vm.appointment_step_form_data.form_fields["customer_password"] = "";
						}
					} else {
						vm.appointment_step_form_data.bpa_password_already_exists = false;
						vm.customer_details_rule.customer_password[0].required = true;
						vm.appointment_step_form_data.bpa_user_email_already_exists = false;
					}
				}
			';

			return $bookingpress_password_check_data;
		}

		function bpa_password_field_validation_arr( $response ){

			global $tbl_bookingpress_form_fields, $wpdb, $email_already_exists_flag, $bookingpress_front_vue_data_fields; 

				$check_password_field = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_field_type) FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_field_type = %s AND bookingpress_field_is_hide = %d ", 'password',0)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm 
				
				if( !empty($check_password_field) && ( empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['bpa_user_email_already_exists']) || $bookingpress_front_vue_data_fields['appointment_step_form_data']['bpa_user_email_already_exists'] == false ) ){

					$response['bpa_password_already_exists'] = false;

					$bpa_username = !empty( $_REQUEST['_username']) ? sanitize_text_field($_REQUEST['_username']) : '';
					if( $bookingpress_is_wp_user_exist = get_user_by('login', $bpa_username) ){

						if (empty($bookingpress_is_wp_user_exist) ) {
							$response['bpa_password_already_exists'] = false;
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['bpa_user_email_already_exists'] = false;
						} else {
							$response['bpa_password_already_exists'] = true;
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['bpa_user_email_already_exists'] = true;
						}

					}
				}

			return $response;
		}

		function bookingpress_booked_appointment_with_share_timeslot_where_clause_func( $where_clause ){
			global $BookingPress;
			$bookingpress_share_timeslot_between_services_type = $BookingPress->bookingpress_get_settings('share_timeslot_between_services_type', 'general_setting');			
			if($bookingpress_share_timeslot_between_services_type == 'service_category'){
				$bookingpress_related_services = array();
				$bookingpress_selected_category = (isset($_POST['appointment_data_obj']['selected_category']))?intval($_POST['appointment_data_obj']['selected_category']):''; //phpcs:ignore
				$bookingpress_all_related_category_service = (isset($_POST['appointment_data_obj']['related_category_service']))?$_POST['appointment_data_obj']['related_category_service']:''; //phpcs:ignore
				if(!empty($bookingpress_selected_category) && !empty($bookingpress_all_related_category_service)){
					$bookingpress_related_services = isset($bookingpress_all_related_category_service[$bookingpress_selected_category])?$bookingpress_all_related_category_service[$bookingpress_selected_category]:array();
				}
				if(empty($bookingpress_related_services)){
					$selected_service = (isset($_POST['appointment_data_obj']['selected_service']))?intval($_POST['appointment_data_obj']['selected_service']):''; //phpcs:ignore
					$bookingpress_related_services = array($selected_service);					
				}
				if(!empty($bookingpress_related_services)){
					$bookingpress_related_services_ids = implode(",",$bookingpress_related_services);					
					if(!empty($bookingpress_related_services_ids)){						
						$where_clause.= ' AND bookingpress_service_id IN ('.$bookingpress_related_services_ids.') ';
					}					
				}
			}
			return $where_clause;
		}


		function bookingpress_check_is_booked_appointment( $bpa_external_validation_check, $appointment_service_id, $appointment_selected_date, $appointment_start_time, $appointment_end_time, $appointment_id, $prevent_double_booking = false, $posted_data = array() ){
			
			global $wpdb, $BookingPress,$tbl_bookingpress_appointment_bookings,$bookingpress_bring_anyone_with_you;

			$bookingpress_selected_staffmember_id = !empty( $posted_data['appointment_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? sanitize_text_field($posted_data['appointment_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id']) : 0;

			$where_clause = '';
			$bookingpress_service_max_capacity = 1;
			$bookingpress_service_max_capacity = apply_filters( 'bookingpress_retrieve_capacity', $bookingpress_service_max_capacity, $appointment_service_id );

			$bookingpress_selected_extra_members = !empty( $posted_data['appointment_data_obj']['bookingpress_selected_bring_members'] ) ? intval( $posted_data['appointment_data_obj']['bookingpress_selected_bring_members'] ) : 0;

			$wpdb_select_col = 'COUNT(bookingpress_appointment_booking_id) AS total';
			$wpdb_select_var = 'get_var';
			if( true == $prevent_double_booking ){
				$wpdb_select_col = '*';
				$wpdb_select_var = 'get_results';
			}

			$appointment_etime_check = $appointment_end_time;

			if($appointment_etime_check == "00:00"){
				$appointment_etime_check = "24:00:00";
			}

			$where_clause = '';

			$bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');
			
			if( 'true' != $bookingpress_shared_service_timeslot ){
				$where_clause = $wpdb->prepare( ' AND bookingpress_service_id = %d ', $appointment_service_id );
				$where_clause = apply_filters( 'bookingpress_booked_appointment_where_clause', $where_clause );
			}else{
                $where_clause = '';
                $where_clause.= apply_filters( 'bookingpress_booked_appointment_with_share_timeslot_where_clause_check', $where_clause,$appointment_service_id);				
			}

			$where_clause .= $wpdb->prepare( ' AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s)', '1', '2' );

			$shared_quantity = apply_filters('bookingpress_get_shared_capacity_data', 'true' );

			$total_booked_appiontments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s $where_clause", $appointment_selected_date), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			$prevent_booking = false;

			$booked_appointment_data_count = 0;
			if( !empty( $total_booked_appiontments ) ){
				$appointment_prevent_reason = array(
					'message' => 'Duplicate or Crossed appointment exists with pending or approved status .',
					'data' => $total_booked_appiontments
				);
				
				$bookingpress_appointment_booked_slot_data = array();
				$service_capacity = $bookingpress_service_max_capacity;
				foreach( $total_booked_appiontments as $booked_appointment_data ){
					$total_guests = 0;
					$booked_appointment_start_time = $booked_appointment_data['bookingpress_appointment_time'];
					$booked_appointment_end_time = $booked_appointment_data['bookingpress_appointment_end_time'];

					if( '00:00:00' == $booked_appointment_end_time ){
						$booked_appointment_end_time =  '24:00:00';
					}
					
					$current_time_start = date( 'H:i:s', strtotime( $appointment_start_time ) );
					$current_time_end = date('H:i:s', strtotime($appointment_end_time) );
					

					if( ( $booked_appointment_start_time >= $current_time_start && $booked_appointment_end_time <= $current_time_end ) || ( $booked_appointment_start_time < $current_time_end && $booked_appointment_end_time > $current_time_start) ){
						
						$booked_appointment_data_count++;
						$capacity_count = 1;	
						
						$extra_members = !empty($booked_appointment_data['bookingpress_selected_extra_members'] ) ? intval($booked_appointment_data['bookingpress_selected_extra_members']) : 0;
						$extra_members = $extra_members - 1;
						if( isset($booked_appointment_data['bookingpress_selected_extra_members']) && $booked_appointment_data['bookingpress_selected_extra_members'] > 0 ){
							$capacity_count += $extra_members;
							$total_guests = $extra_members;
						}
						
						if( 'true' == $shared_quantity ){

							$bookingpress_reduce_capacity = true;
							if(!empty($bookingpress_appointment_booked_slot_data)){
								foreach($bookingpress_appointment_booked_slot_data as $booked_slot){
									$start_time = $booked_slot['start_time'];
									$end_time = $booked_slot['end_time'];
									$block_cap_count = $booked_slot['capacity_count'];
									if($booked_appointment_start_time > $start_time && $booked_appointment_start_time >= $end_time){
										if($capacity_count > $block_cap_count){
											$final_capacity_count = $capacity_count - $block_cap_count;
											$capacity_count = $capacity_count+$final_capacity_count;
										}else{
											$bookingpress_reduce_capacity = false;
										} 																				
									}
								}
							}							
							
							$bookingpress_appointment_booked_slot_data[] = array('start_time' => $booked_appointment_start_time,'end_time' => $booked_appointment_end_time,'capacity_count'=>$capacity_count);
							if($bookingpress_reduce_capacity){
								$service_capacity  -= $capacity_count;
								if( $service_capacity < 0 ){
									$service_capacity = 0;
								}	
							}

							if( 0 == $service_capacity ){
								$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to capacity exceed';
								$appointment_prevent_reason['data'] = $booked_appointment_data;
								$prevent_booking = true;
								break;
							}
						} else {
							if( $booked_appointment_start_time == $current_time_start && $booked_appointment_end_time == $current_time_end ){
								$service_capacity -= $capacity_count;
								if( 0 == $service_capacity ){

									$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to capacity exceed with shared quantity option is enabled';
									$appointment_prevent_reason['data'] = $booked_appointment_data;
									$prevent_booking = true;
									break;
								}

							} else {
								$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to appointment time get crossed and capacity is not sharing';
								$appointment_prevent_reason['data'] = $booked_appointment_data;
								$prevent_booking = true;
								break;
							}
						}

						if( $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation() ){
							$total_bawy = $bookingpress_selected_extra_members;
							if( $total_bawy > $service_capacity ){
								$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to extra member capacity exceed: Total Members - ' . $total_bawy . ' & capacity ' . $service_capacity;
								$appointment_prevent_reason['data'] = $booked_appointment_data;
								$prevent_booking = true;
								break;
							}
						}


						if( 'true' == $bookingpress_shared_service_timeslot && 0 == $service_capacity ){
							$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to shared timeslot is enabled and capacity exceed';
							$appointment_prevent_reason['data'] = $booked_appointment_data;
							$prevent_booking = true;
							break;
						}
					}
				}

				if( true == $prevent_double_booking && true == $prevent_booking ){
					$bpa_external_validation_check['prevent_validation_process'] = true;
					$bpa_external_validation_check[ 'response' ] = $appointment_prevent_reason;
			
					return $bpa_external_validation_check;
				}
			}
			
			/** Check for multiple days event */
			$selected_service_duration_unit = $posted_data['appointment_data']['selected_service_duration_unit'];
			if( !empty( $selected_service_duration_unit ) && 'd' == $selected_service_duration_unit ){
				$selected_service_duration = $posted_data['appointment_data']['selected_service_duration'];
				
				if( 1 < $selected_service_duration ){
					if( !isset( $is_appointment_exists ) ){
						if( true == $prevent_double_booking ){
							$is_appointment_exists = array();
						} else {
							$is_appointment_exists = 0;
						}
					}
					$check_duration = $selected_service_duration - 1;
					$check_before_date = date( 'Y-m-d', strtotime( $appointment_selected_date . '-' . $check_duration . ' days' ) );
					$check_after_date = date( 'Y-m-d', strtotime( $appointment_selected_date . '+' . $check_duration . ' days' ) );

					if( 'true' == $bookingpress_shared_service_timeslot ){

					} else {
						$multiple_days_where_clause = '';
						if( !empty( $bookingpress_selected_staffmember_id ) ){
							$multiple_days_where_clause .= $wpdb->prepare( "AND bookingpress_staff_member_id = %d", $bookingpress_selected_staffmember_id );
						}
						
						$is_multiple_days_appointment = $wpdb->$wpdb_select_var( $wpdb->prepare( "SELECT {$wpdb_select_col} FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = %d AND bookingpress_appointment_date BETWEEN %s AND %s AND ( bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s ) {$multiple_days_where_clause} ", $appointment_service_id, $check_before_date, $check_after_date, '1', '2' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

						if( true == $prevent_double_booking ){
							$is_appointment_exists = $is_appointment_exists + $is_multiple_days_appointment; //combine appointments
						} else {
							$is_appointment_exists += $is_multiple_days_appointment;
						}
					}
				}

				if( !empty( $is_appointment_exists ) ){
					if( true != $prevent_double_booking && $is_appointment_exists > $bookingpress_service_max_capacity ){
						$prevent_booking = true;
					} else if( true == $prevent_double_booking && count( $is_appointment_exists ) > $bookingpress_service_max_capacity ){
						$prevent_booking = true;
					}
				}
			}
			
			/** Overbookings check with custom service duration */
			if( false == $prevent_booking && empty( $total_booked_appiontments ) && 'd' == $posted_data['appointment_data']['selected_service_duration_unit'] ){

				$check_start_date = date('Y-m-d', strtotime( '-31 days', strtotime( $appointment_selected_date ) ));
				$check_end_date = date('Y-m-d', strtotime( '+'.($selected_service_duration - 1).' days', strtotime( $appointment_selected_date) ) );

				
				$bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');
			
				if( 'true' != $bookingpress_shared_service_timeslot ){
					$where_clause = $wpdb->prepare( ' AND bookingpress_service_id = %d ', $appointment_service_id );
					$where_clause = apply_filters( 'bookingpress_booked_appointment_where_clause', $where_clause );
				}else{
					$where_clause = '';
					$where_clause.= apply_filters( 'bookingpress_booked_appointment_with_share_timeslot_where_clause_check', $where_clause,$appointment_service_id);				
				}

				$where_clause .= $wpdb->prepare( ' AND bookingpress_service_duration_unit = %s', 'd' );

				$where_clause .= $wpdb->prepare( ' AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s)', '1', '2' );

				$forward_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date >= %s AND bookingpress_appointment_date <= %s {$where_clause} ORDER BY bookingpress_appointment_date ASC", $appointment_selected_date, $check_end_date), ARRAY_A ); // phpcs:ignore

				

				if( !empty( $forward_appointments ) ){

					$total_booked_appiontments = array_merge( $total_booked_appiontments, $forward_appointments );
					foreach( $total_booked_appiontments as $booked_appointment_data ){
						$total_guests = 0;

						$booked_appointment_duration = $booked_appointment_data['bookingpress_service_duration_val'];
						$booked_appointment_start_date = $booked_appointment_data['bookingpress_appointment_date'];
						$booked_appointment_end_date = date('Y-m-d', strtotime( '+'.($booked_appointment_duration - 1).' days', strtotime( $booked_appointment_data['bookingpress_appointment_date'] ) ) );
						
						$appointment_selected_end_date = date('Y-m-d', strtotime( '+' . ($selected_service_duration - 1) . ' days', strtotime( $appointment_selected_date ) ) );

						if( ($appointment_selected_date >= $booked_appointment_start_date && $appointment_selected_date <= $booked_appointment_end_date) || ( $appointment_selected_date < $booked_appointment_start_date && $appointment_selected_end_date >= $booked_appointment_start_date ) ){
							$booked_appointment_data_count++;
							$capacity_count = 1;

							$extra_members = !empty($booked_appointment_data['bookingpress_selected_extra_members'] ) ? intval($booked_appointment_data['bookingpress_selected_extra_members']) : 0;
							$extra_members = $extra_members - 1;

							if( isset($booked_appointment_data['bookingpress_selected_extra_members']) && $booked_appointment_data['bookingpress_selected_extra_members'] > 0 ){
								$capacity_count += $extra_members;
								$total_guests = $extra_members;
							}

							if( 'true' == $shared_quantity ){
								$service_capacity  -= $capacity_count;
								if( $service_capacity < 0 ){
									$service_capacity = 0;
								}
				
								if( 0 == $service_capacity ){
									$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to capacity exceed';
									$appointment_prevent_reason['data'] = $booked_appointment_data;
									$prevent_booking = true;
									break;
								}
							} else {
								
								$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to appointment date get crossed and capacity is not sharing';
								$appointment_prevent_reason['data'] = $booked_appointment_data;
								$prevent_booking = true;
								break;
							
							}
				
							if( $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation() ){
								$total_bawy = $bookingpress_selected_extra_members;
								if( $total_bawy > $service_capacity ){
									$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to extra member capacity exceed: Total Members - ' . $total_bawy . ' & capacity ' . $service_capacity;
									$appointment_prevent_reason['data'] = $booked_appointment_data;
									$prevent_booking = true;
									break;
								}
							}
				
				
							if( 'true' == $bookingpress_shared_service_timeslot && 0 == $service_capacity ){
								$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to shared timeslot is enabled and capacity exceed';
								$appointment_prevent_reason['data'] = $booked_appointment_data;
								$prevent_booking = true;
								break;
							}
						}
					}
				}

				
				if( $prevent_booking == false  ){
					$backward_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date >= %s AND bookingpress_appointment_date <= %s {$where_clause} ORDER BY bookingpress_appointment_date DESC", $check_start_date, $appointment_selected_date ), ARRAY_A ); //phpcs:ignore
										
					if( !empty( $backward_appointments )){

						$total_booked_appiontments = array_merge( $total_booked_appiontments, $backward_appointments );
						foreach( $total_booked_appiontments as $booked_appointment_data ){
							$total_guests = 0;
	
							$booked_appointment_duration = $booked_appointment_data['bookingpress_service_duration_val'];
							$booked_appointment_start_date = $booked_appointment_data['bookingpress_appointment_date'];
							$booked_appointment_end_date = date('Y-m-d', strtotime( '+'.($booked_appointment_duration - 1).' days', strtotime( $booked_appointment_data['bookingpress_appointment_date'] ) ) );
							
							$appointment_selected_end_date = date('Y-m-d', strtotime( '+' . ($selected_service_duration - 1) . ' days', strtotime( $appointment_selected_date ) ) );
	
							if( ($appointment_selected_date >= $booked_appointment_start_date && $appointment_selected_date <= $booked_appointment_end_date) || ( $appointment_selected_date < $booked_appointment_start_date && $appointment_selected_end_date >= $booked_appointment_start_date ) ){
								$booked_appointment_data_count++;
								$capacity_count = 1;
	
								$extra_members = !empty($booked_appointment_data['bookingpress_selected_extra_members'] ) ? intval($booked_appointment_data['bookingpress_selected_extra_members']) : 0;
								$extra_members = $extra_members - 1;
	
								if( isset($booked_appointment_data['bookingpress_selected_extra_members']) && $booked_appointment_data['bookingpress_selected_extra_members'] > 0 ){
									$capacity_count += $extra_members;
									$total_guests = $extra_members;
								}
	
								if( 'true' == $shared_quantity ){
									$service_capacity  -= $capacity_count;
									if( $service_capacity < 0 ){
										$service_capacity = 0;
									}
					
									if( 0 == $service_capacity ){
										$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to capacity exceed';
										$appointment_prevent_reason['data'] = $booked_appointment_data;
										$prevent_booking = true;
										break;
									}
								} else {
									
									$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to appointment date get crossed and capacity is not sharing';
									$appointment_prevent_reason['data'] = $booked_appointment_data;
									$prevent_booking = true;
									break;
								
								}
					
								if( $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation() ){
									$total_bawy = $bookingpress_selected_extra_members;
									if( $total_bawy > $service_capacity ){
										$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to extra member capacity exceed: Total Members - ' . $total_bawy . ' & capacity ' . $service_capacity;
										$appointment_prevent_reason['data'] = $booked_appointment_data;
										$prevent_booking = true;
										break;
									}
								}
					
					
								if( 'true' == $bookingpress_shared_service_timeslot && 0 == $service_capacity ){
									$appointment_prevent_reason['message'] = 'Appointment Already Exists. Reason: Prevented appointment due to shared timeslot is enabled and capacity exceed';
									$appointment_prevent_reason['data'] = $booked_appointment_data;
									$prevent_booking = true;
									break;
								}
							}
						}
					}
				}
			}
			/** Overbookings check with custom service duration */

			$bpa_check_duplidate_appointment_time_slot = apply_filters( 'bookingpress_modify_check_duplidate_appointment_time_slot', true, $posted_data);
			$duplidate_appointment_time_slot_found = $BookingPress->bookingpress_get_settings('duplidate_appointment_time_slot_found', 'message_setting');
			if( true == $prevent_booking ){
				
				if( true == $prevent_double_booking ){
					
					if( isset($is_appointment_exists)){
						$is_appointment_exists = count( $is_appointment_exists );
					} else {
						$is_appointment_exists = count( $total_booked_appiontments );
					}
					
					if( $is_appointment_exists >= $bookingpress_service_max_capacity && $bpa_check_duplidate_appointment_time_slot ){
						$bpa_external_validation_check['prevent_validation_process'] = true;
						$bpa_external_validation_check['response'] = $is_appointment_exists;
					}
				} else {
					
					if( !isset( $is_appointment_exists ) || 0 === $is_appointment_exists ){
						$is_appointment_exists = count( $total_booked_appiontments );
					}
					if ( $is_appointment_exists >= $bookingpress_service_max_capacity && $bpa_check_duplidate_appointment_time_slot ) {
						$response['error_type']			  = 'error';
						$response['variant']              = 'error';
						$response['title']                = 'Error';
						$response['msg']                  = esc_html( $duplidate_appointment_time_slot_found );
						$bpa_external_validation_check['prevent_validation_process'] = true;
						$bpa_external_validation_check[ 'response' ] = wp_json_encode( $response );
					}
				}	
			}
			
			return $bpa_external_validation_check;
		}

		function bookingpress_disable_date_vue_data_modify_func( $bookingpress_disable_date_vue_data ){
			
			$bookingpress_disable_date_vue_data = '/* Changes to add disable dates class start */
				if( "undefined" != typeof response.data.booking_before_block_dates ){
					var booking_before_block_dates = response.data.booking_before_block_dates.split(",");
					if( booking_before_block_dates.length != "" && booking_before_block_dates.length != 0){
						for( let d in booking_before_block_dates ){
							let booking_before_block_date = booking_before_block_dates[d];
							vm.bookingpress_booking_before_block_date.push( booking_before_block_date );
						}    
					}
				}
			/* Changes to add disable dates class end */';
			
			return $bookingpress_disable_date_vue_data;
		}

		function bookingpress_check_available_timeslot_manual_block_func( $block_date, $check_date ){

			if( false == $block_date ){
                return $block_date;
            }

			global $wpdb, $BookingPress, $BookingPressPro;

			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

			if('false' == $bookingpress_timeslot_display_in_client_timezone)
			{
				$block_date = true;
				return $block_date;
			}
			
			
			$client_timezone_string = !empty( $_COOKIE['bookingpress_client_timezone'] ) ? sanitize_text_field($_COOKIE['bookingpress_client_timezone']) : '';			

            if( 'true' == $bookingpress_timeslot_display_in_client_timezone && !empty( $client_timezone_string ) ){
				$bookingpress_timezone = isset($_POST['client_timezone_offset']) ? sanitize_text_field( $_POST['client_timezone_offset'] ) : '';  // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
                $client_timezone_offset = $BookingPress->bookingpress_convert_timezone_to_offset( $client_timezone_string, $bookingpress_timezone );
                $wordpress_timezone_offset = $BookingPress->bookingpress_convert_timezone_to_offset( wp_timezone_string() );                
                if( $client_timezone_offset != $wordpress_timezone_offset ){
					$block_date = false;
					return $block_date;
                } else {
					$block_date = true;
					return $block_date;
				}
            }

			return $block_date;

		}

		function bookingpress_set_empty_view( $display_empty_view, $bpa_all_services ){

			$total_services = count( $bpa_all_services );
			
			$total_hidden_services = 0;
			
			foreach( $bpa_all_services as $sr_val ){
				if( false == $sr_val['is_visible'] || true == $sr_val['is_disabled']){
					$total_hidden_services++;
				}
			}

			if( $total_hidden_services == $total_services ){
				return true;
			}

			return $display_empty_view;
		}

		function bookingpress_unset_cart_cookie(){
			if( !empty( $_SESSION['bookingpress_remove_cart_cookie'] ) && true == $_SESSION['bookingpress_remove_cart_cookie'] && !empty( $_COOKIE['bookingpress_cart_id']) ){
				setcookie("bookingpress_cart_id", "", time()-(3600), "/");
				unset( $_SESSION['bookingpress_remove_cart_cookie'] );
			}
		}


		function bookingpress_get_multiple_days_disable_dates_func( $response, $bookingpress_selected_date, $bookingpress_selected_service, $bookingpress_appointment_data, $whole_day = false ){
			if( !empty( $bookingpress_selected_service ) ){
				global $BookingPress, $wpdb, $tbl_bookingpress_appointment_bookings, $bookingpress_bring_anyone_with_you;

				$bookingpress_selected_staffmember_id = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? intval($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) : '';
				
				$bookingpress_disable_date = $BookingPress->bookingpress_get_default_dayoff_dates('','',$bookingpress_selected_service,$bookingpress_selected_staffmember_id);
				
				$bookingpress_disable_date = apply_filters('bookingpress_modify_disable_dates', $bookingpress_disable_date, $bookingpress_selected_service, $bookingpress_selected_date, $bookingpress_appointment_data);
				
				
				$bookingpress_start_date = date('Y-m-d', current_time('timestamp'));
                
				if( true == $whole_day && !empty( $bookingpress_selected_date ) ){
					$bookingpress_start_date = $bookingpress_selected_date;
				}

                $bookingpress_end_date = date('Y-m-d', strtotime('last day of this month', strtotime( $bookingpress_start_date )));
                
                $next_month = date( 'm', strtotime( $bookingpress_end_date . '+1 day' ) );
                $next_year = date( 'Y', strtotime( $bookingpress_end_date . '+1 day' ) );
                
                $bookingpress_total_booked_appointment_where_clause = '';
				
				$bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');
				
				if( 'true' != $bookingpress_shared_service_timeslot ){
					$bookingpress_total_booked_appointment_where_clause .= $wpdb->prepare( ' AND bookingpress_service_id = %d ', $bookingpress_selected_service );
					$bookingpress_total_booked_appointment_where_clause = apply_filters( 'bookingpress_total_booked_appointment_where_clause', $bookingpress_total_booked_appointment_where_clause );
				} else {
					$bookingpress_total_booked_appointment_where_clause = apply_filters( 'bookingpress_booked_appointment_with_share_timeslot_where_clause_check', $bookingpress_total_booked_appointment_where_clause,$bookingpress_selected_service);
				}

				$max_service_capacity = 1;
                $max_service_capacity = apply_filters( 'bookingpress_retrieve_capacity', $max_service_capacity, $bookingpress_selected_service );

				$bookingpress_total_appointment = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_appointment_date,bookingpress_service_duration_val,bookingpress_service_duration_unit,SUM(bookingpress_selected_extra_members) as bookingpress_total_person FROM " . $tbl_bookingpress_appointment_bookings . " WHERE (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) AND bookingpress_appointment_date BETWEEN %s AND %s ".$bookingpress_total_booked_appointment_where_clause . ' GROUP BY bookingpress_appointment_date','1','2',$bookingpress_start_date, $bookingpress_end_date), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
				$bookingpress_total_appointment = apply_filters( 'bookingpress_modify_total_booked_appointment', $bookingpress_total_appointment, $bookingpress_appointment_data);
				
				$bookingpress_booking_before_block_date = array();
				$bookingpress_booking_real_block_date = array();
				$multiple_days_event = array();

				if( !empty( $bookingpress_total_appointment ) ){
					foreach( $bookingpress_total_appointment as $key => $value ){
						$booked_appointment_date = $value['bookingpress_appointment_date'];		
						$service_duration_val = $value['bookingpress_service_duration_val'];
						$service_duration_unit = $value['bookingpress_service_duration_unit'];
						if( $service_duration_unit != 'd' ){
							$service_duration_val = $bookingpress_appointment_data['selected_service_duration'];
						}
						
						$service_duration_val_before = $service_duration_val;
						$service_duration_val_before = apply_filters('bookingpress_modify_service_duration_value_before',$service_duration_val_before, $bookingpress_selected_service,$bookingpress_appointment_data);
						

						if( empty( $multiple_days_event[ $booked_appointment_date ] ) ){
							$multiple_days_event[ $booked_appointment_date ] = !empty( $value['bookingpress_total_person'] ) ? $value['bookingpress_total_person'] : 1;
						} else {
							if( !empty( $value['bookingpress_total_person'] ) ){
								$multiple_days_event[ $booked_appointment_date ] += $value['bookingpress_total_person'];
							} else {
								$multiple_days_event[ $booked_appointment_date ]++;
							}
						}
						$bookingpress_booking_real_block_date[] = $booked_appointment_date;
						for( $d = 1; $d < $service_duration_val; $d++ ){
							$booked_day_plus = date( 'Y-m-d', strtotime( $booked_appointment_date . '+' . $d . ' days' ));
							$bookingpress_booking_real_block_date[] = $booked_day_plus;
							if( empty( $multiple_days_event[ $booked_day_plus ] ) ){
								$multiple_days_event[ $booked_day_plus ]  = !empty( $value['bookingpress_total_person'] ) ? $value['bookingpress_total_person'] : 1;
							} else {
								$multiple_days_event[ $booked_day_plus ]++;
							}
						}
						
						for( $dm = $service_duration_val_before - 1; $dm > 0; $dm-- ){
							$booked_day_minus = date( 'Y-m-d', strtotime( $booked_appointment_date . '-' . $dm . ' days' ));
							$bookingpress_booking_before_block_date[] = $booked_day_minus;
							if( empty( $multiple_days_event[ $booked_day_minus ] ) ){
								$multiple_days_event[ $booked_day_minus ]  = !empty( $value['bookingpress_total_person'] ) ? $value['bookingpress_total_person'] : 1;
							} else {
								if( !empty( $value['bookingpress_total_person'] ) ){
									$multiple_days_event[ $booked_day_minus ] +=  $value['bookingpress_total_person'];
								} else {
									$multiple_days_event[ $booked_day_minus ]++;
								}
							}
						}
					}
				}

				if(!empty($bookingpress_booking_before_block_date) && !empty($bookingpress_booking_real_block_date)){

					$bookingpress_common_date_result = array_intersect($bookingpress_booking_before_block_date, $bookingpress_booking_real_block_date);
					if(!empty($bookingpress_common_date_result)){
						$bookingpress_booking_before_block_date = array_diff($bookingpress_booking_before_block_date,$bookingpress_common_date_result);
					}

				}

				$total_bring_person = $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation();
				
				$attributes = array();
                if( !empty( $multiple_days_event ) ){
                    $bookingpress_slot_left_text = $BookingPress->bookingpress_get_customize_settings('slot_left_text','booking_form');
                    $bookingpress_slot_left_text = !empty($bookingpress_slot_left_text) ? stripslashes_deep($bookingpress_slot_left_text) : esc_html__('Slots left', 'bookingpress-appointment-booking');
					if( true == $total_bring_person ){
						$total_person = $bookingpress_appointment_data['bookingpress_selected_bring_members'];
					}
					
                    foreach( $multiple_days_event as $md_date => $md_cap ){
                        if( $md_cap >= $max_service_capacity || ( $total_person + $md_cap ) > $max_service_capacity ){
                            $bookingpress_disable_date[] = $md_date;
                        }
                        $remaining_capacity = ( $max_service_capacity - $md_cap );
                        $attributes[ $md_date ] = ( ($remaining_capacity < 0 ) ? 0 : $remaining_capacity )  .' '. $bookingpress_slot_left_text;
                    }
                }
				
				
				$bookingpress_disable_date = apply_filters( 'bookingpress_modify_disable_dates_with_staffmember', $bookingpress_disable_date, $bookingpress_selected_service);
				
                $bookingpress_selected_date = $BookingPress->bookingpress_select_date_before_load($bookingpress_selected_date,$bookingpress_disable_date);
				
                $bookingpress_disable_date = array_unique( $bookingpress_disable_date );
				
                if( !empty( $single_disable_date ) ){
					$bookingpress_disable_date = array_merge( $bookingpress_disable_date, $single_disable_date );
                }
				
                $bpa_disable_dates = array();
                foreach( $bookingpress_disable_date as $disable_date ){
					$bpa_disable_dates[] = date('Y-m-d H:i:s', strtotime( $disable_date ) );
                }
                
                $response['variant']    = 'success';
                $response['title']      = 'Success';
                $response['msg']        = 'Data reterive successfully';                                            
				$response['days_off_disabled_dates']  =  implode(',',$bookingpress_disable_date );
                $response['days_off_disabled_dates_string']  =  implode(',',$bpa_disable_dates );
                $response['selected_date']  = date('Y-m-d', strtotime($bookingpress_selected_date));
                $response['next_month'] = $next_month;
                $response['vcal_attributes'] = $attributes;
                $response['max_capacity_capacity'] = $max_service_capacity;
                $response['next_year'] = $next_year;
                $response['msg']        = 'Data reterive successfully';
				$response['prevent_next_month_check'] = false;
				$response['check_for_multiple_days_event'] = true;
				$response['booking_before_block_dates']  =  implode(',',$bookingpress_booking_before_block_date );

			}

			return $response;
		}

		function bookingpress_get_single_day_disable_dates_func( $response ){

			global $BookingPress;
			if( !empty( $_POST['appointment_data_obj'] ) && !is_array( $_POST['appointment_data_obj'] ) ){ //phpcs:ignore
				$_POST['appointment_data_obj'] = json_decode( stripslashes_deep( $_POST['appointment_data_obj'] ), true ); //phpcs:ignore
				$_POST['appointment_data_obj'] =  !empty($_POST['appointment_data_obj']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['appointment_data_obj'] ) : array(); // phpcs:ignore
				$_REQUEST['appointment_data_obj']  =  array_map( array( $BookingPress, 'appointment_sanatize_field'), $_POST['appointment_data_obj'] ); //phpcs:ignore
			}
			 
			$bookingpress_appointment_data = !empty($_POST['appointment_data_obj']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data_obj'] ) : array(); // phpcs:ignore
			$bookingpress_selected_date = !empty($_REQUEST['selected_date']) ? sanitize_text_field($_REQUEST['selected_date']) : '';

			if( "NaN-NaN-NaN" == $bookingpress_selected_date || '1970-01-01' == $bookingpress_selected_date || !preg_match('/(\d{4}\-\d{2}\-\d{2})/', $bookingpress_selected_date ) ){
                $bookingpress_selected_date = date('Y-m-d', current_time('timestamp') );
            }

			$bookingpress_selected_service= !empty($_REQUEST['selected_service']) ? intval($_REQUEST['selected_service']) : '';

            if(empty($bookingpress_selected_service)){
                $bookingpress_selected_service = $bookingpress_appointment_data['selected_service'];
            }

            if(empty($bookingpress_appointment_data['selected_service_duration_unit']) || empty($bookingpress_appointment_data['selected_service_duration']) ){
                $bookingpress_service_data = $BookingPress->get_service_by_id($bookingpress_selected_service);
                if(!empty($bookingpress_service_data['bookingpress_service_duration_unit'])){
                    $bookingpress_appointment_data['selected_service_duration_unit'] = $bookingpress_service_data['bookingpress_service_duration_unit'];
                    $bookingpress_appointment_data['selected_service_duration'] = intval($bookingpress_service_data['bookingpress_service_duration_val']);
                }
            }

            if(empty($bookingpress_selected_date)){
                $bookingpress_selected_date = !empty( $bookingpress_appointment_data['selected_date'] ) ? $bookingpress_appointment_data['selected_date'] : date('Y-m-d', current_time('timestamp') );
            }

            if( "NaN-NaN-NaN" == $bookingpress_selected_date || '1970-01-01' == $bookingpress_selected_date || !preg_match('/(\d{4}\-\d{2}\-\d{2})/', $bookingpress_selected_date ) ){
                $bookingpress_selected_date = date('Y-m-d', current_time('timestamp') );
            }

            $bookingpress_selected_staffmember_id = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? intval($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) : '';

			if( !empty( $bookingpress_appointment_data['selected_service_duration_unit'] ) && 'd' == $bookingpress_appointment_data['selected_service_duration_unit'] && 1 == $bookingpress_appointment_data['selected_service_duration'] ){
				$response = apply_filters( 'bookingpress_get_multiple_days_disable_dates', $response, $bookingpress_selected_date, $bookingpress_selected_service, $bookingpress_appointment_data );
			}

			return $response;
		}

		function bookingpress_disable_multiple_days_xhr_response_func( $bookingpress_disable_multiple_days_event_xhr_resp ){

			$bookingpress_disable_multiple_days_event_xhr_resp .= '
				if( postData.action == "bookingpress_get_whole_day_appointments_multiple_days" && "undefined" != typeof response.data.booking_before_block_dates ){
					
					var booking_before_block_dates = response.data.booking_before_block_dates.split(",");
					if( booking_before_block_dates.length != "" && booking_before_block_dates.length != 0){    
						for( let d in booking_before_block_dates ){
							let booking_before_block_date = booking_before_block_dates[d];
							vm.bookingpress_booking_before_block_date.push( booking_before_block_date );
						}    
					}

				}			
				if( postData.action == "bookingpress_get_whole_day_appointments_multiple_days" && "undefined" != typeof response.data.vcal_attributes ){

					let vcal_attributes = response.data.vcal_attributes;
                    vm.bookingpress_select_multi_day_range();    
					if( vcal_attributes.length != "" ){
						let vcal_attr_data = [];
						let vcal_attr_data_current = {};
						let k = 1;
						for( let vcal_date in vcal_attributes ){
							let vcal_data = vcal_attributes[ vcal_date ];
							vcal_attr_data_current[ vcal_date ] = vcal_data;
							let vcal_attr_obj = {
								key: k,
								dates: vcal_date,
								customData:{
									title: vcal_data
								}
							};
							vcal_attr_data.push( vcal_attr_obj );
							k++;
						}

						if( vm.v_calendar_attributes.length > 0 ){
							vm.v_calendar_attributes = vm.v_calendar_attributes.concat( vcal_attr_data );
						} else {
							vm.v_calendar_attributes = vcal_attr_data;
						}
						
						if( "" != vm.v_calendar_attributes_current ){
							let joined_cal_attr_current = { ...vm.v_calendar_attributes_current, ...vcal_attr_data_current };
							vm.v_calendar_attributes_current = joined_cal_attr_current;
						} else {
							vm.v_calendar_attributes_current = vcal_attr_data_current;
						}
					}
				}
			';

			return $bookingpress_disable_multiple_days_event_xhr_resp;
		}

		function bookingpress_modify_cancel_appointment_flag_func($allow_cancel_appointment,$bookingpress_appointment_log_data) {
			global $BookingPress,$bookingpress_services;
			
			if($allow_cancel_appointment == true){
				$bookingpress_min_time_before_cancel = $BookingPress->bookingpress_get_settings('default_minimum_time_for_canceling', 'general_setting');
				//Check service level minimum time required before cancel
				$bookingpress_service_id = $bookingpress_appointment_log_data['bookingpress_service_id'];
				$bookingpress_appointment_date = $bookingpress_appointment_log_data['bookingpress_appointment_date'];
				$bookingpress_appointment_time = $bookingpress_appointment_log_data['bookingpress_appointment_time'];
				$bookingpress_appointment_datetime = $bookingpress_appointment_date." ".$bookingpress_appointment_time;                        

				$bookingpress_service_min_time_require_before_cancel = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'minimum_time_required_before_cancelling');

				if(!empty($bookingpress_service_min_time_require_before_cancel)){
					if($bookingpress_service_min_time_require_before_cancel == 'disabled'){
						$bookingpress_min_time_before_cancel = 'disabled';
					}else if($bookingpress_service_min_time_require_before_cancel != 'inherit'){
						$bookingpress_min_time_before_cancel = $bookingpress_service_min_time_require_before_cancel;
					}
				}

				//Check minimum cancel time
				if($allow_cancel_appointment && !empty($bookingpress_min_time_before_cancel) && $bookingpress_min_time_before_cancel != 'disabled'){
					$bookingpress_from_time = current_time('timestamp');
					$bookingpress_to_time = strtotime($bookingpress_appointment_datetime);
					$bookingpress_time_diff_for_cancel = round(abs($bookingpress_to_time - $bookingpress_from_time) / 60, 2);
					if($bookingpress_time_diff_for_cancel < $bookingpress_min_time_before_cancel){
						$allow_cancel_appointment = false;
					}
				}
			}

			return $allow_cancel_appointment;
		}
				
		/**
		 * bookingpress_add_cancel_appointment_extra_content_func
		 *
		 * @param  mixed $content
		 * @return void
		 */
		function bookingpress_add_cancel_appointment_extra_content_func($content,$appointment_id) {
			global $bookingpress_pro_appointment,$BookingPress;

			if(!empty($appointment_id)) {
				$refund_data = $bookingpress_pro_appointment->bookingpress_allow_to_refund(array(),$appointment_id,1);
				if(!empty($refund_data['allow_refund']) && $refund_data['allow_refund'] == 1) {
					$bpa_refund_amount_text = $BookingPress->bookingpress_get_customize_settings('refund_amount_text', 'booking_my_booking');
					$bpa_refund_payment_gateway_text = $BookingPress->bookingpress_get_customize_settings('refund_payment_gateway_text', 'booking_my_booking');
					$bpa_payment_policy_msg = $BookingPress->bookingpress_get_settings('refund_policy_message','message_setting');

					$bpa_refund_amount_text = !empty($bpa_refund_amount_text) ? esc_html($bpa_refund_amount_text) : '';
					$bpa_refund_payment_gateway_text = !empty($bpa_refund_payment_gateway_text) ? esc_html($bpa_refund_payment_gateway_text) : '';
					$bpa_payment_policy_msg = !empty($bpa_payment_policy_msg) ? esc_html($bpa_payment_policy_msg) : '';

					$bookingpress_refund_data = $bookingpress_pro_appointment->bookingpress_calculate_refund_amount(array(),$appointment_id,1);
					$bpa_payment_gateway = !empty($bookingpress_refund_data['refund_gateway']) ? $bookingpress_refund_data['refund_gateway'] :'';
					$bpa_payment_method_text = $BookingPress->bookingpress_get_customize_settings($bpa_payment_gateway.'_text', 'booking_form');
					$bpa_payment_method_text = !empty($bpa_payment_method_text) ? $bpa_payment_method_text : $bpa_payment_gateway;
					$currency_symbol = $BookingPress->bookingpress_get_currency_symbol($bookingpress_refund_data['refund_currency']);
					// $paid_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_refund_data['default_refund_amount'], $currency_symbol);
					$bpa_refund_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_refund_data['refund_amount'], $currency_symbol);				
					$content .= '
					<div class="bpa-front-refund-confirmation-content">
		                        <div class="bpa-front-rcc__desc">'.$bpa_payment_policy_msg.'</div>
		                        <div class="bpa-front-rcc__body">
		                            <div class="bpa-front-rcc-body__item">
		                                <div class="bpa-front-rcc-item__label">'.$bpa_refund_payment_gateway_text.':</div>
		                                <div class="bpa-front-rcc-item__val">'.$bpa_payment_method_text.'</div>
		                            </div>
		                            <div class="bpa-front-rcc-body__item">
		                                <div class="bpa-front-rcc-item__label">'.$bpa_refund_amount_text.':</div>
		                                <div class="bpa-front-rcc-item__val">'.$bpa_refund_amount.'</div>
		                            </div>
		                        </div>
		                    </div>';
				}
			}

			return $content;
		}
		
		/**
		 * bookingpress_refund_process_before_cancel_appointment
		 *
		 * @param  mixed $appointment_id
		 * @return void
		 */
		function bookingpress_refund_process_before_cancel_appointment($response,$appointment_id) {
			global $bookingpress_pro_appointment,$bookingpress_pro_payment_gateways;
			$refund_data = $bookingpress_pro_appointment->bookingpress_allow_to_refund(array(),$appointment_id,1);
			if(!empty($refund_data['allow_refund']) && $refund_data['allow_refund'] == 1) {
				$bookingpress_refund_data = $bookingpress_pro_appointment->bookingpress_calculate_refund_amount(0,$appointment_id,1);
				if(!empty($bookingpress_refund_data['refund_type'])) {
					$bookingpress_refund_data['refund_amount'] = $bookingpress_refund_data['refund_type'] == 'full' ? $bookingpress_refund_data['default_refund_amount'] : $bookingpress_refund_data['refund_amount'];
					if($bookingpress_refund_data['refund_amount'] > 0) {
						$response_data = $bookingpress_pro_payment_gateways->bookingpress_apply_for_refund($response,$bookingpress_refund_data,1);
						if(isset($response_data['variant']) && $response_data['variant'] == 'success') {
							$response['variant'] = 'success';
						} else {
							$response['variant'] = 'error';
							$response['title'] = esc_html__('Error', 'bookingpress-appointment-booking');
							$response['msg'] = esc_html__('Something went wrong, please try again later', 'bookingpress-appointment-booking');	
						}
					} else{
						$response['variant'] == 'success';
					} 
				}
			} else {
				$response['variant'] == 'success';
			}			
			return $response;
		}
		
		/**
		 * Filter to add/edit service duration labels
		 *
		 * @param  mixed $bookingpress_duration_suffix_labels
		 * @return void
		 */
		function bookingpress_set_day_unit_duration_label( $bookingpress_duration_suffix_labels ){
			global $BookingPress;

			$day_unit_text = $BookingPress->bookingpress_get_customize_settings('book_appointment_day_text', 'booking_form'); 

			$bookingpress_duration_suffix_labels['d'] = !empty( $day_unit_text ) ? $day_unit_text : esc_html__('d', 'bookingpress-appointment-booking');

			return $bookingpress_duration_suffix_labels;
		}
		
		/**
		 * Filter for check rescheduled appointment already booked or not
		 *
		 * @param  mixed $is_appointment_already_booked
		 * @param  mixed $reschedule_id
		 * @return void
		 */
		function bookingpress_check_rescheduled_is_appointment_already_booked_func($is_appointment_already_booked,$reschedule_id){

			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_services, $tbl_bookingpress_staffmembers_services;

			if(!empty($reschedule_id)){
				
				$bookingpress_service_id = !empty( $_POST['resche_service_id'] ) ? intval( $_POST['resche_service_id'] ) : 0; //phpcs:ignore
				$bookingpress_staff_id = !empty( $_POST['resche_staff_id'] ) ? intval( $_POST['resche_staff_id'] ) : 0; //phpcs:ignore
				$bookingpress_appointment_date = !empty( $_POST['resche_date'] ) ? date( 'Y-m-d', strtotime( sanitize_text_field( $_POST['resche_date'] ) ) ) : ''; //phpcs:ignore
				$bookingpress_appointment_start_time  = !empty( $_POST['resche_time'] ) ? date( 'H:i:s', strtotime( sanitize_text_field( $_POST['resche_time'] ) ) ) : ''; //phpcs:ignore

				$booked_appointment_details = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_selected_extra_members FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $reschedule_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$bookingpress_selected_extra_members = !empty($booked_appointment_details['bookingpress_selected_extra_members']) ? intval($booked_appointment_details['bookingpress_selected_extra_members']) - 1 : 0;

				$total_required_slot =  1 + $bookingpress_selected_extra_members;				

				if(!empty($bookingpress_service_id)){
					//Get Service Max Capacity
					$bookingpress_max_capacity = $bookingpress_pro_services->bookingpress_get_service_max_capacity($bookingpress_service_id);
					$total_booked_appointment = 0;

					if(!empty($bookingpress_staff_id)){
						$bookingpress_get_staff_cap_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_service_capacity FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $bookingpress_staff_id, $bookingpress_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_services is table name defined globally. False Positive alarm
						
						if(!empty($bookingpress_get_staff_cap_data['bookingpress_service_capacity'])){
							$bookingpress_max_capacity = floatval($bookingpress_get_staff_cap_data['bookingpress_service_capacity']);
						}

						$total_booked_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_appointment,SUM(bookingpress_selected_extra_members - 1) as total_extra_members FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_service_id = %d AND bookingpress_staff_member_id = %d", $reschedule_id, '2', '1', $bookingpress_appointment_date, $bookingpress_appointment_start_time, $bookingpress_service_id, $bookingpress_staff_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

						if(!empty($total_booked_appointment_data)) {
							$total_booked_appointment = $total_booked_appointment_data['total_appointment'] + $total_booked_appointment_data['total_extra_members'];
						}

					}else{

						$total_booked_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_appointment,SUM(bookingpress_selected_extra_members - 1) as total_extra_members FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_service_id = %d", $reschedule_id, '2', '1', $bookingpress_appointment_date, $bookingpress_appointment_start_time, $bookingpress_service_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

						if(!empty($total_booked_appointment_data)) {
							$total_booked_appointment = $total_booked_appointment_data['total_appointment'] + $total_booked_appointment_data['total_extra_members'];
						}
					}

					if( $total_booked_appointment < $bookingpress_max_capacity) {
						$total_available_slot = $bookingpress_max_capacity - $total_booked_appointment;	
						if(	$total_required_slot > $total_available_slot ) {
							$is_appointment_already_booked = 1;
						} else {
							$is_appointment_already_booked = 0;
						}
					} else {
						$is_appointment_already_booked = 1;
					}
				}
			}

			return $is_appointment_already_booked;

		}
		
		/**
		 * Modify frontend appointment step form data
		 *
		 * @param  mixed $appointment_step_form_data
		 * @param  mixed $bookingpress_form_field_tmp_data
		 * @return void
		 */
		function bookingpress_add_appointment_step_form_data_filter_func($appointment_step_form_data,$bookingpress_form_field_tmp_data) {
			
			if(!empty($bookingpress_form_field_tmp_data['field_type']) && ($bookingpress_form_field_tmp_data['field_type'] == '2_col' || $bookingpress_form_field_tmp_data['field_type'] == '3_col' || $bookingpress_form_field_tmp_data['field_type'] == '4_col' )) {
				$bookingpress_inner_fields = $bookingpress_form_field_tmp_data['field_options']['inner_fields'];                    
				if(!empty($bookingpress_inner_fields)) {
					foreach($bookingpress_inner_fields as $k => $v) {
						if(!empty($v['v_model_value']) && !empty($v['field_options']['visibility']) && $v['field_options']['visibility'] != 'hidden') {
							$appointment_step_form_data[$v['v_model_value']] = '';
							if( 'appointment_terms_conditions' == $v['v_model_value'] ){
								$appointment_step_form_data[$v['v_model_value']] = array();
							}
						}
					}    
				}    
			}
			return $appointment_step_form_data;
		}
		
		/**
		 * Modify customize settings data for backend customize screen
		 *
		 * @param  mixed $bookingpress_form_fields
		 * @return void
		 */
		function bookingpress_modify_field_data_before_prepare_func($bookingpress_form_fields) {			
			foreach ( $bookingpress_form_fields as $k => $val ) {				
				$bookingpress_form_fields[$k]['bookingpress_field_is_hide'] = 0;	
				$bookingpress_field_options = !empty($val['bookingpress_field_options']) ? json_decode($val['bookingpress_field_options'],true) : array();
				if(!empty($bookingpress_field_options) && isset($bookingpress_field_options['visibility']) && $bookingpress_field_options['visibility'] == 'hidden' && isset($val['bookingpress_form_field_name']) &&  $val['bookingpress_form_field_name'] != '4 Col' &&  $val['bookingpress_form_field_name'] != '2 Col' && $val['bookingpress_form_field_name'] != '3 Col' ) {
					$bookingpress_form_fields[$k]['bookingpress_field_is_hide'] = 1;
				}
			}
			return $bookingpress_form_fields;
		}
		
		/**
		 * Modify [bookingpress_appointment_service] shortcode details
		 *
		 * @param  mixed $appointment_data
		 * @param  mixed $appointment_id
		 * @return void
		 */
		function bookingpress_modify_service_shortcode_details_func($appointment_data, $appointment_id){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries, $tbl_bookingpress_payment_logs;
			if(!empty($appointment_id) ){
				//Get appointment details
                //$bpa_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_rec FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

				$bpa_appointment_data = wp_cache_get( 'bookingpress_record_count_cart_order_'.$appointment_id );
                if( false == $bpa_appointment_data ){
                    $bpa_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_rec FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    wp_cache_set( 'bookingpress_record_count_cart_order_'.$appointment_id , $bpa_appointment_data);
                }

				if($bpa_appointment_data['total_rec'] > 1){
					//Get appointment details
                	//$bpa_appointment_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm


					$bpa_appointment_data = wp_cache_get( 'bookingpress_appointments_records_cart_order_'.$appointment_id );
					if( false == $bpa_appointment_data ){
						$bpa_appointment_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
						wp_cache_set( 'bookingpress_appointments_records_cart_order_'.$appointment_id , $bpa_appointment_data);
					}

				}else{
					//Get appointment details
                	//$bpa_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

					$bpa_appointment_data = wp_cache_get( 'bookingpress_records_cart_order_'.$appointment_id );
					if( false == $bpa_appointment_data ){
						$bpa_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
						wp_cache_set( 'bookingpress_records_cart_order_'.$appointment_id , $bpa_appointment_data);
					}


				}
				$appointment_data = !empty($bpa_appointment_data) ? $bpa_appointment_data : $appointment_data;
			}
			return $appointment_data;
		}
		
		/**
		 * Modify [bookingpress_appointment_datetime] shortcode details
		 *
		 * @param  mixed $appointment_data
		 * @param  mixed $appointment_id
		 * @return void
		 */
		function bookingpress_modify_datetime_shortcode_data_func($appointment_data, $appointment_id){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries, $tbl_bookingpress_payment_logs;

			if(!empty($appointment_id) ){
				//Get appointment details

				$bpa_appointment_data = array();
                //$bpa_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_rec FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

				$bpa_appointment_data = wp_cache_get( 'bookingpress_record_count_cart_order_'.$appointment_id );
                if( false == $bpa_appointment_data ){
                    $bpa_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_rec FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
                    wp_cache_set( 'bookingpress_record_count_cart_order_'.$appointment_id , $bpa_appointment_data);
                }

				if($bpa_appointment_data['total_rec'] > 1){
					//Get appointment details
                	//$bpa_appointment_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

					$bpa_appointment_data = wp_cache_get( 'bookingpress_appointments_records_cart_order_'.$appointment_id );
					if( false == $bpa_appointment_data ){
						$bpa_appointment_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
						wp_cache_set( 'bookingpress_appointments_records_cart_order_'.$appointment_id , $bpa_appointment_data);
					}


				}else{
					//Get appointment details
                	//$bpa_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

					$bpa_appointment_data = wp_cache_get( 'bookingpress_records_cart_order_'.$appointment_id );
					if( false == $bpa_appointment_data ){
						$bpa_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
						wp_cache_set( 'bookingpress_records_cart_order_'.$appointment_id , $bpa_appointment_data);
					}

				}
				
				$appointment_data = !empty($bpa_appointment_data) ? $bpa_appointment_data : $appointment_data;
			}
			return $appointment_data;
		}
		
		/**
		 * Modify [bookingpress_appointment_customername] shortcode details
		 *
		 * @param  mixed $appointment_data
		 * @param  mixed $appointment_id
		 * @return void
		 */
		function bookingpress_modify_customer_details_shortcode_data_func($appointment_data, $appointment_id){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries, $tbl_bookingpress_payment_logs;

			if(!empty($appointment_id)){
				//Get appointment details
                //$bpa_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

				$bpa_appointment_data = wp_cache_get( 'bookingpress_records_cart_order_'.$appointment_id );
					if( false == $bpa_appointment_data ){
						$bpa_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d AND bookingpress_is_cart = %d", $appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
						wp_cache_set( 'bookingpress_records_cart_order_'.$appointment_id , $bpa_appointment_data);
					}

				$appointment_data = !empty($bpa_appointment_data) ? $bpa_appointment_data : $appointment_data;
			} 
			return $appointment_data;
		}
		
		/**
		 * Execute code when frontend mybookings tab change.
		 *
		 * @return void
		 */
		function bookingpress_activate_my_booking_tab_data_func() {
			?>
			vm.bookingpress_reset_error_success_msg();
			<?php
		}
		
		/**
		 * Modify [booking_id] shortcode details
		 *
		 * @param  mixed $bookingpress_booking_id
		 * @param  mixed $appointment_id
		 * @return void
		 */
		function bookingpress_modify_booking_id_shortcode_data_func($bookingpress_booking_id, $appointment_id){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries, $tbl_bookingpress_payment_logs;
			
			if(!empty($_GET['is_cart']) && !empty($appointment_id) ){
				//Get appointment details

				//$bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

				$bookingpress_appointment_details = wp_cache_get( 'bpa_get_booking_id_bookingpress_appointment_details_id_'.$appointment_id );
                if( false == $bookingpress_appointment_details ){
			
					$bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
				
					wp_cache_set( 'bpa_get_booking_id_bookingpress_appointment_details_id_'.$appointment_id , $bookingpress_appointment_details);

				}

				
                

				$bookingpress_booking_id = !empty($bookingpress_appointment_details['bookingpress_booking_id']) ? $bookingpress_appointment_details['bookingpress_booking_id'] : $bookingpress_booking_id;
			}

			$bookingpress_order_id = !empty($_COOKIE['bookingpress_cart_id']) ? base64_decode($_COOKIE['bookingpress_cart_id']) : ''; // phpcs:ignore
			
            if(!empty($bookingpress_order_id)){

				//$bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d", $bookingpress_order_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

				$bookingpress_appointment_details = wp_cache_get( 'bpa_get_booking_id_bookingpress_appointment_details_id_'.$appointment_id );
                if( false == $bookingpress_appointment_details ){
			
					$bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_order_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
				
					wp_cache_set( 'bpa_get_booking_id_bookingpress_appointment_details_id_'.$appointment_id , $bookingpress_appointment_details);

				}


				$bookingpress_booking_id = !empty($bookingpress_appointment_details['bookingpress_booking_id']) ? $bookingpress_appointment_details['bookingpress_booking_id'] : $bookingpress_booking_id;
				
				$_SESSION['bookingpress_remove_cart_cookie'] = true;

            }
			
			return $bookingpress_booking_id;
		}
		
		/**
		 * Modify current date which loads when Booking Form shortcode loaded.
		 *
		 * @param  mixed $bookingpress_date
		 * @return void
		 */
		function bookingpress_set_current_date_to_client_timezone( $bookingpress_date ){

			global $BookingPress;

			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

			if( !empty( $bookingpress_timeslot_display_in_client_timezone ) && 'true' == $bookingpress_timeslot_display_in_client_timezone ){
				$bookingpress_date = '';
			}


			return $bookingpress_date;
		}
		
		/**
		 * Convert Selected  Date from Client Timezone to Server Time
		 *
		 * @param  mixed $selected_appointment_date
		 * @param  mixed $booked_appointment_starttime
		 * @param  mixed $client_timezone_offset
		 * @return void
		 */
		function bookingpress_appointment_change_date_to_store_timezone_func( $selected_appointment_date, $booked_appointment_starttime, $client_timezone_offset ){

			if( '' == $client_timezone_offset){
				return $selected_appointment_date;
			}

			global $BookingPress, $bookingpress_global_options;

			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

			if( !empty( $bookingpress_timeslot_display_in_client_timezone ) && 'true' == $bookingpress_timeslot_display_in_client_timezone ){
				
				/** Retrieve WordPress Time zone Offset */
				$bookingpress_options = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_timezone_offset = $bookingpress_options['bookingpress_timezone_offset'];
		
				$wp_timestring = wp_timezone_string();
				
				if( 'UTC' == $wp_timestring ){
					$wp_timestring = '+00:00';
				}  else if( array_key_exists( $wp_timestring, $bookingpress_timezone_offset ) ){
					$wp_timezone_data = new DateTimeZone($wp_timestring);
					$wp_timezone_dtls = $wp_timezone_data->getTransitions();
					$wp_timezone_current = array();
					foreach( $wp_timezone_dtls as $k => $wp_timezone_detail ){
						if( current_time( 'timestamp' ) < $wp_timezone_detail['ts'] ){
							$wp_timezone_current[] = $wp_timezone_dtls[ $k - 1 ];
						}
					}
					
					$wp_curr_timezone_data = !empty( $wp_timezone_current[0] ) ? $wp_timezone_current[0] : array( 'offset' => '' );
					
					$wp_offset =  ( $wp_curr_timezone_data['offset'] !== '' ) ? ( $wp_curr_timezone_data['offset'] / ( 60 * 60 ) ) : $bookingpress_timezone_offset[ $wp_timestring ];
		
					if( $wp_curr_timezone_data['offset'] !== '' ){
		
						if( $wp_offset < 0 ){
							if( $wp_offset > -10 ){
								$wp_offset = '-0' . abs( $wp_offset ) . ":00";
							} else {
								$wp_offset = $wp_offset . ":00";
							}
						} else {
							if( $wp_offset < 10 ){
								$wp_offset = '+0' . $wp_offset . ":00";
							} else {
								$wp_offset = "+" . $wp_offset . ":00";
							}
						}
					}
					$wp_timestring = $wp_offset;
					
				}

				$formatted_date = date('Y-m-d H:i:s', strtotime( $selected_appointment_date . ' ' . $booked_appointment_starttime ) );
				
				$client_timezone_offset = -1 * ( $client_timezone_offset / 60 );
				$offset_minute = fmod( $client_timezone_offset, 1);
				
				$offset_minute = abs( $offset_minute );
			
				$hours = $client_timezone_offset - $offset_minute;
				
				$offset_minute = $offset_minute * 60;
				if( $hours < 0 ){

				} else {
					if( strlen( $hours ) === 1 ){
						$hours = '+0' . $hours;
					} else {
						$hours = '+' . $hours;
					}
				}

				if( strlen( $offset_minute ) == 1 ){
					$offset_minute = '0' . $offset_minute;
				}

				$timezone_offset = $hours.':' . $offset_minute;

				$timezone_utc_format = $selected_appointment_date.'T'.$booked_appointment_starttime.$timezone_offset;

				$appointment_date = new DateTime( $timezone_utc_format );
				
				$appointment_date->setTimezone( new \DateTimeZone( $wp_timestring ) );

				$selected_appointment_date = $appointment_date->format( 'Y-m-d');

			}

			return $selected_appointment_date;
		}
		
		/**
		 * Convert Appointment Date Time to Client Timezone
		 *
		 * @param  mixed $booked_appointment_datetime
		 * @param  mixed $client_timezone_offset
		 * @return void
		 */
		function bookingpress_appointment_change_to_client_timezone_func( $booked_appointment_datetime, $client_timezone_offset, $entry_details = array() ){
			
			if( '' === $client_timezone_offset ){
				return $booked_appointment_datetime;
			}
			
			global $BookingPress, $bookingpress_global_options;

			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

			if( !empty( $bookingpress_timeslot_display_in_client_timezone ) && 'true' == $bookingpress_timeslot_display_in_client_timezone ){

				/** Retrieve WordPress Time zone Offset */
				$bookingpress_options = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_timezone_offset = $bookingpress_options['bookingpress_timezone_offset'];

				$wp_timestring = wp_timezone_string();
				
				if( 'UTC' == $wp_timestring ){
					$wp_timestring = '+00:00';
				}  else if( array_key_exists( $wp_timestring, $bookingpress_timezone_offset ) ){
					
					$wp_timezone_data = new DateTimeZone($wp_timestring);
					$wp_timezone_dtls = $wp_timezone_data->getTransitions();
					$wp_timezone_current = array();
					foreach( $wp_timezone_dtls as $k => $wp_timezone_detail ){
						if( current_time( 'timestamp' ) < $wp_timezone_detail['ts'] ){
							$wp_timezone_current[] = $wp_timezone_dtls[ $k - 1 ];
						}
					}
					
					$wp_curr_timezone_data = !empty( $wp_timezone_current[0] ) ? $wp_timezone_current[0] : array( 'offset' => '' );
					
					$wp_offset = ( $wp_curr_timezone_data['offset'] !== "" ) ? ( $wp_curr_timezone_data['offset'] / ( 60 * 60 ) ) : $bookingpress_timezone_offset[ $wp_timestring ];

					if( $wp_curr_timezone_data['offset'] !== '' ){

						if( $wp_offset < 0 ){
							if( $wp_offset > -10 ){
								$wp_offset = '-0' . abs( $wp_offset ) . ":00";
							} else {
								$wp_offset = $wp_offset . ":00";
							}
						} else {
							if( $wp_offset < 10 ){
								$wp_offset = '+0' . $wp_offset . ":00";
							} else {
								$wp_offset = "+" . $wp_offset . ":00";
							}
						}
					}
					$wp_timestring = $wp_offset;
					
				}
				
				$formatted_date = date('Y-m-d', strtotime( $booked_appointment_datetime ) );
				$formatted_time = date('H:i:s', strtotime( $booked_appointment_datetime ) );

				$is_dst = !empty( $_POST['appointment_data_obj']['client_dst_timezone'] )  ? $_POST['appointment_data_obj']['client_dst_timezone'] : ( !empty( $_POST['client_dst_timezone'] ) ? $_POST['client_dst_timezone'] : 0 ); //phpcs:ignore

				$client_timezone_string = !empty( $_COOKIE['bookingpress_client_timezone'] ) ? sanitize_text_field($_COOKIE['bookingpress_client_timezone']) : '';
				
				if( empty( $client_timezone_string ) ){
					return $booked_appointment_datetime;
				}

				$timezone_data = new DateTimeZone($client_timezone_string);
				
                $timezone_dtls = $timezone_data->getTransitions();
				
				if( empty( $timezone_dtls ) ){
                    return $booked_appointment_datetime;
                }

				$timezone_current = array();
				$timezone_next = array();

                if( count( $timezone_dtls ) == 1 ){
					$timezone_current = $timezone_dtls[0];
				} else {
					foreach( $timezone_dtls as $k => $timezone_detail ){
						if( strtotime( $booked_appointment_datetime ) < $timezone_detail['ts'] ){
							if( empty( $timezone_next ) ){
								$timezone_next[] = $timezone_detail;
								$timezone_current[] = $timezone_dtls[ $k - 1 ];
								break;
							}
						}
					}
                }
				
				if( !empty( $timezone_current ) && !empty( $timezone_next) ){
					$timezone_dtls = array_merge( $timezone_current, $timezone_next );
				}
				
                $curr_timezone_data = !empty( $timezone_dtls[0] ) ? $timezone_dtls[0] : array( 'offset' => '' );
                $next_timezone_data = !empty( $timezone_dtls[1] ) ? $timezone_dtls[1] : array( 'offset' => '' );
				
				$offset = ( '' !== $curr_timezone_data['offset'] ) ? ( $curr_timezone_data['offset'] / ( 60 * 60 ) ) : $client_timezone_offset;
				$offset_int = $offset;
				if( $curr_timezone_data['offset'] !== '' ){

					if( $offset < 0 ){
						if( $offset > -10 ){
							$offset = '-0' . abs( $offset ) . ":00";
						} else {
							$offset = $offset . ":00";
						}
					} else {
						if( $offset < 10 ){
							$offset = '+0' . $offset . ":00";
						} else {
							$offset = "+" . $offset . ":00";
						}
					}
				}

				
				$timezone_utc_format = $formatted_date.'T'.$formatted_time.$wp_timestring;
				
				$client_timezone_offset = -1 * ( $offset_int / 60 );
				
				$offset_minute = fmod( $offset_int, 1);
				$offset_minute = abs( $offset_minute );
				
				$hours = $client_timezone_offset - $offset_minute;
				
				$offset_minute = $offset_minute * 60;
				
				if( $hours < 0 ){
					if( strlen( $hours ) == 1 ){
						$hours = str_replace( '-', '-0', $hours );
					}
				} else {
					if( strlen( $hours ) === 1 ){
						$hours = '+0' . $hours;
					} else {
						$hours = '+' . $hours;
					}
				}

				if( strlen( $offset_minute ) == 1 ){
					$offset_minute = '0' . $offset_minute;
				}
				
				
				$timezone_offset = $hours.':' . $offset_minute;
				
				$appointment_date = new DateTime( $timezone_utc_format );
				
				try{
					 /** set timezone with client timezone string */
					$appointment_date->setTimezone( new \DateTimeZone( $client_timezone_string ) );
				} catch (Exception $e){
					
					/** If timezone string is not a valid timezone then set timezone according to the timezone offset */
					$appointment_date->setTimezone( new \DateTimeZone( $timezone_offset ) );
				}
				
				$booked_appointment_datetime = $appointment_date->format( 'Y-m-d H:i:s');
				
			}

			return $booked_appointment_datetime;
		}
		
		/**
		 * Set client timezone in frontend booking form
		 *
		 * @param  mixed $bookingpress_timezone
		 * @return void
		 */
		function bookingpress_change_timezone_filter_func($bookingpress_timezone){
			global $BookingPress;
			$bookingpress_show_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

			$bookingpress_client_timezone = !empty($_COOKIE['bookingpress_client_timezone']) ? sanitize_text_field( $_COOKIE['bookingpress_client_timezone'] ) : '';

			if(!empty($bookingpress_client_timezone) && !empty($bookingpress_show_in_client_timezone) && ($bookingpress_show_in_client_timezone == 'true')){
				$bookingpress_timezone = $bookingpress_client_timezone;
			}

			return $bookingpress_timezone;
		}

		/**
         * Function for background call with multiple days
         *
         * @return void
         */
        function bookingpress_get_whole_day_appointment_multipe_days_func($return_data = false){
			global $BookingPress, $wpdb, $tbl_bookingpress_appointment_bookings;

			$month_check = !empty( $_POST['next_month'] ) ? intval( $_POST['next_month'] ) : date('m', current_time('timestamp') ); // phpcs:ignore
			$bookingpress_selected_service = !empty( $_POST['selected_service'] ) ? intval( $_POST['selected_service'] ) : ''; // phpcs:ignore
			
			if( !empty( $_POST['appointment_data_obj'] ) && !is_array( $_POST['appointment_data_obj'] ) ){ //phpcs:ignore
				$_POST['appointment_data_obj'] = json_decode( stripslashes_deep( $_POST['appointment_data_obj'] ), true ); //phpcs:ignore
				$_POST['appointment_data_obj'] =  !empty($_POST['appointment_data_obj']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['appointment_data_obj'] ) : array(); // phpcs:ignore
			}

			$bookingpress_disabled_dates = !empty($_POST['days_off_disabled_dates']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), explode(',',$_POST['days_off_disabled_dates']) ) : array(); // phpcs:ignore
            $daysoff_dates = $bookingpress_disabled_dates;

			$service_duration = !empty( $_POST['appointment_data_obj']['selected_service_duration'] ) ? intval( $_POST['appointment_data_obj']['selected_service_duration'] ) : ''; // phpcs:ignore
			$service_duration_unit = !empty( $_POST['appointment_data_obj']['selected_service_duration_unit'] ) ? sanitize_text_field( $_POST['appointment_data_obj']['selected_service_duration_unit'] ) : ''; // phpcs:ignore

			$response = array();

			if( $month_check < 10 ){
				$month_check = "0" . $month_check;
			}

			$current_month = date( 'm', current_time( 'timestamp') );
			if( $current_month > $month_check ){
				$first_date_of_month = date( 'Y', strtotime( '+1 year') ) . '-' . $month_check . '-01';
			} else {	
				$first_date_of_month = date('Y', current_time('timestamp') ) . '-' . $month_check . '-01';
			}
			$bookingpress_selected_date = $first_date_of_month;
            $last_date_of_month = date('Y-m-t', strtotime( $first_date_of_month ) );

			$start_date = new DateTime( $first_date_of_month );
            $end_date = new DateTime( $last_date_of_month );

			$interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod( $start_date, $interval, $end_date );
			$bookingpress_total_booked_appointment_where_clause = '';
			$bookingpress_total_booked_appointment_where_clause = apply_filters( 'bookingpress_total_booked_appointment_where_clause', $bookingpress_total_booked_appointment_where_clause );
			
			/* $disable_date_for_month = array(); */
			foreach( $period as $dt ){
				$current_date = $dt->format("Y-m-d H:i:s");
				$date_t = date('c', strtotime( $current_date ) );
				/* if( !in_array( $date_t, $daysoff_dates ) ){ */
					$current_selected_date = $dt->format( 'Y-m-d' );
					$bookingpress_total_appointment = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_service_duration_val,bookingpress_appointment_date FROM " . $tbl_bookingpress_appointment_bookings . " WHERE (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) AND bookingpress_service_id= %d AND bookingpress_appointment_date = %s ".$bookingpress_total_booked_appointment_where_clause." GROUP BY bookingpress_appointment_date",'1','2',$bookingpress_selected_service,$current_selected_date), ARRAY_A); // phpcs:ignore
					
					if( !empty( $bookingpress_total_appointment ) ){
						$service_duration_val = $bookingpress_total_appointment['bookingpress_service_duration_val'];
						$bookingpress_appointment_date =  $bookingpress_total_appointment['bookingpress_appointment_date'];

						$daysoff_dates[] = date('c', strtotime( $bookingpress_appointment_date));
						for( $d = 1; $d < $service_duration_val; $d++ ){
							$daysoff_dates[] = date( 'c', strtotime( $bookingpress_appointment_date . '+' . $d . ' days' ));
						}

						for( $dm = $service_duration_val - 1; $dm > 0; $dm-- ){
							$daysoff_dates[] = date( 'c', strtotime( $bookingpress_appointment_date . '-' . $dm . ' days'));
						}
					}
				/* } */
			}

			$max_available_month = !empty( $_POST['max_available_month'] ) ? sanitize_text_field( $_POST['max_available_month'] ) : ''; // phpcs:ignore
			$response['prevent_next_month_check']  = false;
			if( !empty( $max_available_month ) && $max_available_month == $month_check && $_POST['max_available_year'] < date('Y', current_time('timestamp') )){ // phpcs:ignore
                $response['prevent_next_month_check']  = true;
            }

			$response[ 'days_off_disabled_dates' ] = implode( ',', $daysoff_dates );
            $response['next_month'] = date( 'm', strtotime( $first_date_of_month . '+1 month') );

			$booking_appointment_data_obj_data = (isset($_POST['appointment_data_obj']))?$_POST['appointment_data_obj']:array(); //phpcs:ignore
			$response = apply_filters( 'bookingpress_get_multiple_days_disable_dates', $response, $bookingpress_selected_date, $bookingpress_selected_service, $booking_appointment_data_obj_data, true ); 

			$response = array_merge( $_POST, $response ); // phpcs:ignore
			if($return_data){
				return $response;
			}
            echo json_encode( $response );

            die;
        }
		
		/**
		 * Function for set client timezone
		 *
		 * @return void
		 */
		function bookingpress_set_clients_timezone_func(){
			global $BookingPress;

			$response              = array();
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg'] = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );

			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				echo wp_json_encode( $response );
				die();
			}

			$bookingpress_client_timezone = !empty($_POST['clients_timezone']) ? sanitize_text_field( $_POST['clients_timezone'] ) : '';
			if(!empty($bookingpress_client_timezone)){
				setcookie("bookingpress_client_timezone", $bookingpress_client_timezone, time() + (86400 * 30), "/");

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg'] = esc_html__( 'Client timezone set successfully', 'bookingpress-appointment-booking' );
			}

			echo wp_json_encode($response);
			exit;
		}
		
		/**
		 * Modify form fields validation rules
		 *
		 * @param  mixed $bookingpress_form_fields_error_msg_arr
		 * @param  mixed $bookingpress_field_setting_fields_tmp
		 * @return void
		 */
		function bookingpress_modify_form_fields_rules_arr_func($bookingpress_form_fields_error_msg_arr,$bookingpress_field_setting_fields_tmp ) {

			if(!empty($bookingpress_field_setting_fields_tmp['field_name']) && ( $bookingpress_field_setting_fields_tmp['field_name'] == 'Repeater')) {  

				$bookingpress_inner_fields_main = (isset($bookingpress_field_setting_fields_tmp['field_options']['inner_fields']))?$bookingpress_field_setting_fields_tmp['field_options']['inner_fields']:array();				
				if(!empty($bookingpress_inner_fields_main)){

					foreach($bookingpress_inner_fields_main as $key=>$bookingpress_inner_fields){

						

						$bookingpress_check_inner_field_type = (isset($bookingpress_inner_fields_main[$key]['field_type']))?$bookingpress_inner_fields_main[$key]['field_type']:''; 										
						if($bookingpress_check_inner_field_type == '2_col' || $bookingpress_check_inner_field_type == '3_col' || $bookingpress_check_inner_field_type == '4_col'){
							$bookingpress_inner_fields = (isset($bookingpress_field_setting_fields_tmp['field_options']['inner_fields'][$key]['field_options']['inner_fields']))?$bookingpress_field_setting_fields_tmp['field_options']['inner_fields'][$key]['field_options']['inner_fields']:array();
							if(!empty($bookingpress_inner_fields)) {
								foreach($bookingpress_inner_fields  as $k => $v) {						
	
									if(!empty($v['is_required']) && ($v['is_required'] == 1 || $v['is_required'] == "true")) {
										$bookingpress_v_model_value = $v['v_model_value']; 
										if ($bookingpress_v_model_value == 'customer_email' ) {
											$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
												array(
												'required' => true,
												'message'  => stripslashes_deep($v['error_message']),
												'trigger'  => 'blur',
												),
												array(
												'type'    => 'email',
												'message' => esc_html__('Please enter valid email address', 'bookingpress-appointment-booking'),
												'trigger' => 'blur',
												),
											);
											if(isset($bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message']) && $bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] == '' ) {
												$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] = !empty($v['label']) ?  stripslashes_deep($v['label']).' '.__('is required','bookingpress-appointment-booking') : '';
											}
			
										} else {
											$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
												array( 
													'required' => true,
													'message'  => !empty($v['error_message']) ? stripslashes_deep($v['error_message']) : '',
													'trigger'  => 'blur',
												)
											);
											if( $bookingpress_v_model_value == 'appointment_terms_conditions'){
												$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
													array(
													'required' => true,
													'message'  => stripslashes_deep($v['error_message']),
													'trigger'  => 'change',
													),
												);
											}
											if( $v['field_type'] == 'Dropdown' ){
												$bookingpress_meta_key = $v['meta_key'];
												$bookingpress_v_model_value = $v['v_model_value'];
												if( $bookingpress_v_model_value == $bookingpress_meta_key){
													$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array(
														'required' => true,
														'message'  => stripslashes_deep($bookingpress_field_setting_fields_tmp['error_message']),
														'trigger'  => 'change',
													);
												}
											}
											if(isset($bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message']) && $bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] == '') {
												$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] = !empty($v['label']) ?  stripslashes_deep($v['label']).' '.__('is required','bookingpress-appointment-booking') : '';
											}																
											if(!empty($v['field_options']['minimum'])) {
												$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array( 
													'min' => intval($v['field_options']['minimum']),
													'message'  => __('Minimum','bookingpress-appointment-booking').' '.$v['field_options']['minimum'].' '.__('character required','bookingpress-appointment-booking'),
													'trigger'  => 'blur',
												);
											}
											if(!empty($v['field_options']['maximum'])) {
												$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array( 
													'max' => intval($v['field_options']['maximum']),
													'message'  => __('Maximum','bookingpress-appointment-booking').' '.$v['field_options']['maximum'].' '.__('character allowed','bookingpress-appointment-booking'),
													'trigger'  => 'blur',
												);
											}
											
										}     							
									}
								}    
							}							
						}else{
							
							$v = $bookingpress_inner_fields;
							$k = $key;
							if(!empty($v['is_required']) && ($v['is_required'] == 1 || $v['is_required'] == "true")) {
								$bookingpress_v_model_value = $v['v_model_value']; 
								if ($bookingpress_v_model_value == 'customer_email' ) {
									$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
										array(
										'required' => true,
										'message'  => stripslashes_deep($v['error_message']),
										'trigger'  => 'blur',
										),
										array(
										'type'    => 'email',
										'message' => esc_html__('Please enter valid email address', 'bookingpress-appointment-booking'),
										'trigger' => 'blur',
										),
									);
									if(isset($bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message']) && $bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] == '' ) {
										$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] = !empty($v['label']) ?  stripslashes_deep($v['label']).' '.__('is required','bookingpress-appointment-booking') : '';
									}
	
								} else {
									$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
										array( 
											'required' => true,
											'message'  => !empty($v['error_message']) ? stripslashes_deep($v['error_message']) : '',
											'trigger'  => 'blur',
										)
									);
									if( $bookingpress_v_model_value == 'appointment_terms_conditions'){
										$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
											array(
											'required' => true,
											'message'  => stripslashes_deep($v['error_message']),
											'trigger'  => 'change',
											),
										);
									}
									if( $v['field_type'] == 'Dropdown' ){
										$bookingpress_meta_key = $v['meta_key'];
										$bookingpress_v_model_value = $v['v_model_value'];
										if( $bookingpress_v_model_value == $bookingpress_meta_key){
											$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array(
												'required' => true,
												'message'  => stripslashes_deep($bookingpress_field_setting_fields_tmp['error_message']),
												'trigger'  => 'change',
											);
										}
									}
									if(isset($bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message']) && $bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] == '') {
										$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] = !empty($v['label']) ?  stripslashes_deep($v['label']).' '.__('is required','bookingpress-appointment-booking') : '';
									}																
									if(!empty($v['field_options']['minimum'])) {
										$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array( 
											'min' => intval($v['field_options']['minimum']),
											'message'  => __('Minimum','bookingpress-appointment-booking').' '.$v['field_options']['minimum'].' '.__('character required','bookingpress-appointment-booking'),
											'trigger'  => 'blur',
										);
									}
									if(!empty($v['field_options']['maximum'])) {
										$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array( 
											'max' => intval($v['field_options']['maximum']),
											'message'  => __('Maximum','bookingpress-appointment-booking').' '.$v['field_options']['maximum'].' '.__('character allowed','bookingpress-appointment-booking'),
											'trigger'  => 'blur',
										);
									}
									
								}     							
							}

						}

					}
				}
				 				

			}else if(!empty($bookingpress_field_setting_fields_tmp['field_type']) && ( $bookingpress_field_setting_fields_tmp['field_type'] == '2_col' || $bookingpress_field_setting_fields_tmp['field_type']  == '3_col' || $bookingpress_field_setting_fields_tmp['field_type']  == '4_col')) {  
				$bookingpress_inner_fields = $bookingpress_field_setting_fields_tmp['field_options']['inner_fields'];
				if(!empty($bookingpress_inner_fields)) {
					foreach($bookingpress_inner_fields  as $k => $v) {
						if(!empty($v['is_required']) && $v['is_required'] == 1) {
							$bookingpress_v_model_value = $v['v_model_value']; 
							if ($bookingpress_v_model_value == 'customer_email' ) {
								$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
									array(
									'required' => true,
									'message'  => stripslashes_deep($v['error_message']),
									'trigger'  => 'blur',
									),
									array(
									'type'    => 'email',
									'message' => esc_html__('Please enter valid email address', 'bookingpress-appointment-booking'),
									'trigger' => 'blur',
									),
								);
								if(isset($bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message']) && $bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] == '' ) {
									$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] = !empty($v['label']) ?  stripslashes_deep($v['label']).' '.__('is required','bookingpress-appointment-booking') : '';
								}

							} else {
								$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
									array( 
										'required' => true,
										'message'  => !empty($v['error_message']) ? stripslashes_deep($v['error_message']) : '',
										'trigger'  => 'blur',
									)
								);
								if( $bookingpress_v_model_value == 'appointment_terms_conditions'){
									$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ] = array(
										array(
										'required' => true,
										'message'  => stripslashes_deep($v['error_message']),
										'trigger'  => 'change',
										),
									);
								}
								if( $v['field_type'] == 'Dropdown' ){
									$bookingpress_meta_key = $v['meta_key'];
									$bookingpress_v_model_value = $v['v_model_value'];
									if( $bookingpress_v_model_value == $bookingpress_meta_key){
										$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array(
											'required' => true,
											'message'  => stripslashes_deep($bookingpress_field_setting_fields_tmp['error_message']),
											'trigger'  => 'change',
										);
									}
								}
								if(isset($bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message']) && $bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] == '') {
									$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][0]['message'] = !empty($v['label']) ?  stripslashes_deep($v['label']).' '.__('is required','bookingpress-appointment-booking') : '';
								}																
								if(!empty($v['field_options']['minimum'])) {
									$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array( 
										'min' => intval($v['field_options']['minimum']),
										'message'  => __('Minimum','bookingpress-appointment-booking').' '.$v['field_options']['minimum'].' '.__('character required','bookingpress-appointment-booking'),
										'trigger'  => 'blur',
									);
								}
								if(!empty($v['field_options']['maximum'])) {
									$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array( 
										'max' => intval($v['field_options']['maximum']),
										'message'  => __('Maximum','bookingpress-appointment-booking').' '.$v['field_options']['maximum'].' '.__('character allowed','bookingpress-appointment-booking'),
										'trigger'  => 'blur',
									);
								}
								
							}     							
						}
					}    
				}    
			} elseif(!empty($bookingpress_field_setting_fields_tmp['field_type'])) {
				if( $bookingpress_field_setting_fields_tmp['field_type'] == 'Password' ){
					$bookingpress_v_model_value = $bookingpress_field_setting_fields_tmp['v_model_value'];
					if( $bookingpress_v_model_value == 'customer_password'){
							$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array(
								'required' => true,
								'message'  => stripslashes_deep($bookingpress_field_setting_fields_tmp['error_message']),
								'trigger'  => 'blur',
							);
						
					}
				}

				if( $bookingpress_field_setting_fields_tmp['field_type'] == 'Dropdown' ){
					$bookingpress_meta_key = $bookingpress_field_setting_fields_tmp['meta_key'];
					$bookingpress_v_model_value = $bookingpress_field_setting_fields_tmp['v_model_value'];
					if( $bookingpress_v_model_value == $bookingpress_meta_key){
						$bookingpress_form_fields_error_msg_arr[ $bookingpress_v_model_value ][] = array(
							'required' => true,
							'message'  => stripslashes_deep($bookingpress_field_setting_fields_tmp['error_message']),
							'trigger'  => 'change',
						);
					}
				}

				if(!empty($bookingpress_field_setting_fields_tmp['field_options']['minimum'])) {
					$bookingpress_form_fields_error_msg_arr[ $bookingpress_field_setting_fields_tmp['v_model_value']][] = array( 
						'min' => intval($bookingpress_field_setting_fields_tmp['field_options']['minimum']),
						'message'  => __('Minimum','bookingpress-appointment-booking').' '.$bookingpress_field_setting_fields_tmp['field_options']['minimum'].' '.__('character required','bookingpress-appointment-booking'),
						'trigger'  => 'blur',
					);
				}
				if(!empty($bookingpress_field_setting_fields_tmp['field_options']['maximum'])) {
					$bookingpress_form_fields_error_msg_arr[ $bookingpress_field_setting_fields_tmp['v_model_value']][] = array( 
						'max' => intval($bookingpress_field_setting_fields_tmp['field_options']['maximum']),
						'message'  => __('Maximum','bookingpress-appointment-booking').' '.$bookingpress_field_setting_fields_tmp['field_options']['maximum'].' '.__('character allowed','bookingpress-appointment-booking'),
						'trigger'  => 'blur',
					);
				}
			}
			return $bookingpress_form_fields_error_msg_arr;
		}
		
		/**
		 * Modify form fields validation trigger methods
		 *
		 * @param  mixed $bookingpress_form_fields_error_msg_arr
		 * @return void
		 */
		function bookingpress_modify_form_fields_msg_array_callback( $bookingpress_form_fields_error_msg_arr){
			global $wpdb, $tbl_bookingpress_form_fields;						
			foreach( $bookingpress_form_fields_error_msg_arr as $bpa_field_meta_key => $bpa_err_data ){
				$field_type = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_field_type FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_meta_key = %s", $bpa_field_meta_key ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.	
				if( !empty( $field_type ) && 'checkbox' == $field_type ){
					$bookingpress_form_fields_error_msg_arr[$bpa_field_meta_key][0]['type'] = 'array';
					$bookingpress_form_fields_error_msg_arr[$bpa_field_meta_key][0]['trigger'] = 'change';
				}
			}

			return $bookingpress_form_fields_error_msg_arr;
		}
		
		/**
		 * Forgot password request callback function
		 *
		 * @return void
		 */
		function bookingpress_forgot_password_account_func(){
			global $BookingPress;

			$bookingpress_forgot_password_err_msg = $BookingPress->bookingpress_get_customize_settings('forgot_password_form_error_msg_label', 'booking_my_booking');
			$bookingpress_forgot_password_success_msg = $BookingPress->bookingpress_get_customize_settings('forgot_password_form_success_msg_label', 'booking_my_booking');

			$response              = array();
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg'] = stripslashes_deep($bookingpress_forgot_password_err_msg);

			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				echo wp_json_encode( $response );
				die();
			}

			$bookingpress_forgot_pass_email = !empty($_POST['forgot_pass_email_address']) ? sanitize_email($_POST['forgot_pass_email_address']) : '';
			if(!empty($bookingpress_forgot_pass_email)){
				$return  = $this->bookingpress_send_forgotpassword_email($bookingpress_forgot_pass_email);
				if($return){
					$response['variant'] = 'success';
					$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
					$response['msg'] = stripslashes_deep($bookingpress_forgot_password_success_msg);
				}
			}

			echo wp_json_encode($response);
			exit;
		}
		
		/**
		 * Update session value once user logged-in from BookingPress
		 *
		 * @param  mixed $logged_in_cookie
		 * @return void
		 */
		function bookingpress_update_cookie($logged_in_cookie){
			$_COOKIE[LOGGED_IN_COOKIE] = $logged_in_cookie;
		}
		
		/**
		 * My Bookings Customer Account Login request callback function
		 *
		 * @return void
		 */
		function bookingpress_login_customer_account_func($return_data = false){
			global $BookingPress;

			$bookingpress_login_err_msg = $BookingPress->bookingpress_get_customize_settings('login_form_error_msg_label', 'booking_my_booking');

			$response              = array();
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg'] = stripslashes_deep($bookingpress_login_err_msg);
			$response['new_nonce'] = '';
			$response['is_bookingpress_staffmember'] = 0;

			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				if($return_data){
					return $response;
				}
				echo wp_json_encode( $response );
				die();
			}

			$bookingpress_login_email = !empty($_POST['login_email_address']) ? sanitize_text_field($_POST['login_email_address']) : '';
			$bookingpress_login_pass = !empty($_POST['login_password']) ? $_POST['login_password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason: contains password and no need sanitize
			$bookingpress_remember_me = !empty($_POST['is_remember']) ? true : false;

			if(!empty($bookingpress_login_email) && !empty($bookingpress_login_pass)){
				$bookingpress_login_arr = array(
					'user_login' => $bookingpress_login_email,
					'user_password' => $bookingpress_login_pass,
					'remember' => $bookingpress_remember_me
				);

				$bookingpress_user_signin = wp_signon($bookingpress_login_arr);

				if ( isset( $bookingpress_user_signin->roles ) && is_array( $bookingpress_user_signin->roles ) && isset( $bookingpress_user_signin->caps ) && is_array( $bookingpress_user_signin->caps )) {
					if ( in_array( 'bookingpress-staffmember', $bookingpress_user_signin->roles ) && !in_array( 'administrator', $bookingpress_user_signin->roles ) && in_array( 'bookingpress', $bookingpress_user_signin->caps )) {
						$redirect_to =  esc_url( admin_url() . 'admin.php?page=bookingpress');
						$bookingpress_staffmember_access_admin = $BookingPress->bookingpress_get_settings( 'bookingpress_staffmember_access_admin', 'staffmember_setting' );
						if((!empty($_COOKIE['bookingpress_staffmember_view']) && $_COOKIE['bookingpress_staffmember_view'] == 'admin_view') && !empty($bookingpress_staffmember_access_admin) && $bookingpress_staffmember_access_admin == 'true') {
							$redirect_to = add_query_arg( 'staffmember_view','admin_view',$redirect_to);
						}
						$response['is_bookingpress_staffmember'] = 1;
						$response['staff_redirect_to'] = $redirect_to;
					}
				}
				
				if(!is_wp_error($bookingpress_user_signin)){
					if($return_data){
						$response['variant'] = 'success';
						$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
						$response['msg'] = esc_html__('Login Successfully', 'bookingpress-appointment-booking');
						$response['user_id'] =  $bookingpress_user_signin->ID;
						$response['new_nonce'] = wp_create_nonce('bpa_wp_nonce');												
						return $response;
					}					
					wp_set_current_user( $bookingpress_user_signin->ID );				
					$response['variant'] = 'success';
					$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
					$response['msg'] = esc_html__('Login Successfully', 'bookingpress-appointment-booking');
					$response['current_logged_id'] =  wp_get_current_user();
					$response['new_nonce'] = wp_create_nonce('bpa_wp_nonce');
				}
			}

			echo wp_json_encode($response);
			exit;
		}
		
		/**
		 * Customer password update callback function
		 *
		 * @return void
		 */
		function bookingpress_update_password_func($return_data = false,$user_id = ""){
			global $BookingPress;

			$bookingpress_update_password_success_msg = $BookingPress->bookingpress_get_customize_settings('update_password_success_message', 'booking_my_booking');
			$bookingpress_update_password_error_msg = $BookingPress->bookingpress_get_customize_settings('update_password_error_message', 'booking_my_booking');

			$response              = array();
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg'] = stripslashes_deep($bookingpress_update_password_error_msg);

			$wpnonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				if($return_data){
					return $response;
				}
				echo wp_json_encode( $response );
				die();
			}

			$bookingpress_current_password = !empty($_POST['current_password']) ? sanitize_text_field($_POST['current_password']) : '';
			$bookingpress_new_password = !empty($_POST['new_password']) ? sanitize_text_field($_POST['new_password']) : '';
			$bookingpress_confirm_password = !empty($_POST['confirm_password']) ? sanitize_text_field($_POST['confirm_password']) : '';

			if(!empty($bookingpress_current_password) && !empty($bookingpress_new_password) && !empty($bookingpress_confirm_password) && ($bookingpress_new_password == $bookingpress_confirm_password) ){

				if(!empty($user_id)){
					$bookingpress_current_user = get_user_by('id', $user_id);
				}else{
					$bookingpress_current_user = wp_get_current_user();
				}
				if(!empty($bookingpress_current_user) && isset($bookingpress_current_user->ID) && !empty($bookingpress_current_user->ID)){
					$bookingpress_current_user_pass = $bookingpress_current_user->user_pass;
					if(wp_check_password($bookingpress_current_password, $bookingpress_current_user_pass, $bookingpress_current_user->ID)){
						wp_set_password($bookingpress_new_password, $bookingpress_current_user->ID);
						wp_set_auth_cookie($bookingpress_current_user->ID);
						wp_set_current_user($bookingpress_current_user->ID);
						do_action('wp_login', $bookingpress_current_user->user_login, $bookingpress_current_user);
						$response['variant'] = 'success';
						$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
						$response['msg'] = stripslashes_deep($bookingpress_update_password_success_msg);
					}	
				}

			}
			if($return_data){
				return $response;
			}
			echo wp_json_encode($response);
			exit;
		}

		
		/**
		 * Modify My Bookings Shortcode data
		 *
		 * @param  mixed $data
		 * @return void
		 */
		function bookingpress_modify_my_appointments_data_func($data){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $bookingpress_pro_staff_members, $bookingpress_global_options, $bookingpress_pro_payment, $bookingpress_services,$bookingpress_pro_appointment;
			if(!empty($data['items'])){
				$bookingpress_appointments_data = $data['items'];

				$bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_payment_statuses = $bookingpress_global_data['payment_status'];
				
				$allow_customer_reschedule_apt = $BookingPress->bookingpress_get_customize_settings( 'allow_customer_reschedule_apt', 'booking_my_booking' );
				$allow_customer_cancel_apt = $BookingPress->bookingpress_get_customize_settings('allow_to_cancel_appointment', 'booking_my_booking');            

				$bookingpress_date_format = $bookingpress_global_data['wp_default_date_format'];
                $bookingpress_time_format = $bookingpress_global_data['wp_default_time_format'];

				foreach($bookingpress_appointments_data as $k => $v){


					$bookingpress_min_time_before_cancel = $BookingPress->bookingpress_get_settings('default_minimum_time_for_canceling', 'general_setting');
					$bookingpress_min_time_before_reschedule = $BookingPress->bookingpress_get_settings('default_minimum_time_befor_rescheduling', 'general_setting');


					$bookingpress_appointment_id = intval($v['bookingpress_appointment_booking_id']);
					$bookingpress_payment_id = intval($v['bookingpress_payment_id']);
					$bookingpress_is_cart = intval($v['bookingpress_is_cart']);
					$bookingpress_order_id = intval($v['bookingpress_order_id']);
					$bookingpress_staffmember_id = intval($v['bookingpress_staff_member_id']);
					$bookingpress_subtotal_amt = floatval($v['bookingpress_service_price']);
					$bookingpress_service_id = intval($v['bookingpress_service_id']);
					/* $bookingpress_is_rescheduled = intval($v['bookingpress_is_reschedule']); */

					//Check service level minimum time required before cancel
					$bookingpress_service_min_time_require_before_cancel = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'minimum_time_required_before_cancelling');
					if($bookingpress_service_min_time_require_before_cancel == 'disabled'){
						$bookingpress_min_time_before_cancel = 'disabled';
					}else if($bookingpress_service_min_time_require_before_cancel != 'inherit'){
						$bookingpress_min_time_before_cancel = $bookingpress_service_min_time_require_before_cancel;
					}

					//Check service level minimum time required before reschedule
					$bookingpress_service_min_time_require_before_reschedule = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'minimum_time_required_before_rescheduling');
					if($bookingpress_service_min_time_require_before_reschedule == 'disabled'){
						$bookingpress_min_time_before_reschedule = 'disabled';
					}else if($bookingpress_service_min_time_require_before_reschedule != 'inherit'){
						$bookingpress_min_time_before_reschedule = $bookingpress_service_min_time_require_before_reschedule;
					}

					$bookingpress_staff_first_name = $bookingpress_staff_last_name = $bookingpress_staff_email_address = $bookingpress_staff_avatar_url = "";

					$bookingpress_selected_bring_anyone_members = 0;

					//Get appointment details
					$bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

					if(!empty($bookingpress_staffmember_id)){
						$bookingpress_subtotal_amt = !empty($bookingpress_appointment_details['bookingpress_staff_member_price']) ? $bookingpress_appointment_details['bookingpress_staff_member_price'] : $bookingpress_subtotal_amt;
						$bookingpress_staff_first_name = $bookingpress_appointment_details['bookingpress_staff_first_name'];
						$bookingpress_staff_last_name = $bookingpress_appointment_details['bookingpress_staff_last_name'];
						$bookingpress_staff_email_address = $bookingpress_appointment_details['bookingpress_staff_email_address'];

						$bookingpress_get_existing_avatar_url = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'staffmember_avatar_details' );
						$bookingpress_get_existing_avatar_url = !empty( $bookingpress_get_existing_avatar_url ) ? maybe_unserialize( $bookingpress_get_existing_avatar_url ) : array();
						if ( ! empty( $bookingpress_get_existing_avatar_url[0]['url'] ) ) {
							$bookingpress_staff_avatar_url = $bookingpress_get_existing_avatar_url[0]['url'];
						} else {
							$bookingpress_staff_avatar_url = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
						}
					}

					if(!empty($bookingpress_appointment_details['bookingpress_selected_extra_members'])){
						$bookingpress_appointment_details['bookingpress_selected_extra_members'] = intval($bookingpress_appointment_details['bookingpress_selected_extra_members'])  - 1;
						$bookingpress_selected_bring_anyone_members = intval($bookingpress_appointment_details['bookingpress_selected_extra_members']);
						$bookingpress_subtotal_amt = $bookingpress_subtotal_amt + ($bookingpress_subtotal_amt * $bookingpress_selected_bring_anyone_members);
						$bookingpress_selected_bring_anyone_members++;
					}

					$bookingpress_extra_service_details_arr = array();
					if(!empty($bookingpress_appointment_details['bookingpress_extra_service_details'])){
						$bookingpress_tmp_extra_service_data = json_decode($bookingpress_appointment_details['bookingpress_extra_service_details'], TRUE);
						$bookingpress_tmp_extra_service_data = apply_filters('bookingpress_modify_my_appointment_extra_service_data', $bookingpress_tmp_extra_service_data);
						foreach($bookingpress_tmp_extra_service_data as $k2 => $v2){
							$extra_service_total_price = floatval($v2['bookingpress_extra_service_details']['bookingpress_extra_service_price']) * intval($v2['bookingpress_selected_qty']);
							$bookingpress_extra_service_details_arr[] = array(
								'extra_service_name' => $v2['bookingpress_extra_service_details']['bookingpress_extra_service_name'],
								'extra_service_duration' => $v2['bookingpress_extra_service_details']['bookingpress_extra_service_duration']." ".$v2['bookingpress_extra_service_details']['bookingpress_extra_service_duration_unit'],
								'extra_service_selected_qty' => $v2['bookingpress_selected_qty'],
								'extra_service_total_price' => $extra_service_total_price,
								'extra_service_total_price_with_currency' => $BookingPress->bookingpress_price_formatter_with_currency_symbol($extra_service_total_price),
							);

							$bookingpress_subtotal_amt = $bookingpress_subtotal_amt + $extra_service_total_price;
						}
					}

					//Get payment logs details
					$bookingpress_payment_log_details = $bookingpress_pro_payment->bookingpress_calculate_payment_details($bookingpress_payment_id);
					$bookingpress_deposit_amt = 0;
					$bookingpress_tax_amt = !empty($bookingpress_payment_log_details['tax_amount']) ? $bookingpress_payment_log_details['tax_amount'] : 0;
					$bookingpress_coupon_discount_amt = !empty($bookingpress_payment_log_details['coupon_discount_amount']) ? $bookingpress_payment_log_details['coupon_discount_amount'] : 0;
					
					$bookingpress_selected_payment_method = $bookingpress_payment_log_details['selected_gateway'];
					if($bookingpress_selected_payment_method != 'on-site'){
						$bookingpress_deposit_amt = !empty($bookingpress_payment_log_details['deposit_amount']) ? $bookingpress_payment_log_details['deposit_amount'] : 0;
					}

					if($bookingpress_is_cart == 1){
						$bookingpress_appointments_data[$k]['bookingpress_payment_method'] = $bookingpress_payment_log_details['selected_gateway'];
						
						$bookingpress_payment_status = $bookingpress_payment_log_details['payment_status'];
						$bookingpress_status_label = $bookingpress_payment_status;
						foreach($bookingpress_payment_statuses as $k2 => $v2){
							if($v2['value'] == $bookingpress_payment_status){
								$bookingpress_status_label = $v2['text'];
							}
						}
						$bookingpress_appointments_data[$k]['bookingpress_payment_status'] = $bookingpress_payment_status;
						$bookingpress_appointments_data[$k]['bookingpress_payment_status_label'] = $bookingpress_status_label;
					}

					$bookingpress_tax_amount = !empty($bookingpress_payment_log_details['tax_amount']) ? floatval($bookingpress_payment_log_details['tax_amount']) : 0;
					$bookingpress_coupon_discount_amt = !empty($bookingpress_payment_log_details['coupon_discount_amount']) ? floatval($bookingpress_payment_log_details['coupon_discount_amount']) : 0;

					$bookingpress_total_amt = $bookingpress_payment_log_details['total_amount'];

					$appointment_service_duration_unit = $bookingpress_appointment_details['bookingpress_service_duration_unit'];


					$hide_action_wrapper = false;
					$allow_rescheduling = true;
					$allow_cancel_appointment = true;

					if($allow_customer_cancel_apt == 'false' && $allow_customer_reschedule_apt == 'false') {
						$hide_action_wrapper = true;
					}

					if( 'd' == $appointment_service_duration_unit ){
						if( $v['bookingpress_appointment_date'] <= date('Y-m-d', current_time('timestamp') ) ){
							$allow_rescheduling = false;
							$allow_cancel_appointment = false;
						}
					} else {
						$appointment_datetime = $v['bookingpress_appointment_date'] .' '. $v['bookingpress_appointment_time'];
						$current_datetime = date( 'Y-m-d H:i:s', current_time('timestamp') );
						
						if( $appointment_datetime <= $current_datetime ){
							$allow_rescheduling = false;
							$allow_cancel_appointment = false;
						}
					}

					//Check minimum cancel time
					if($allow_cancel_appointment && $bookingpress_min_time_before_cancel != 'disabled'){
						$bookingpress_from_time = current_time('timestamp');
						$bookingpress_to_time = strtotime($v['bookingpress_appointment_date'] .' '. $v['bookingpress_appointment_time']);
						$bookingpress_time_diff_for_cancel = round(abs($bookingpress_to_time - $bookingpress_from_time) / 60, 2);
						if($bookingpress_time_diff_for_cancel < $bookingpress_min_time_before_cancel){
							$allow_cancel_appointment = false;
						}
					}

					//Check minimum reschedule time
					if($allow_rescheduling && $bookingpress_min_time_before_reschedule != 'disabled'){
						$bookingpress_from_time = current_time('timestamp');
						$bookingpress_to_time = strtotime($v['bookingpress_appointment_date'] .' '. $v['bookingpress_appointment_time']);
						$bookingpress_time_diff_for_cancel = round(abs($bookingpress_to_time - $bookingpress_from_time) / 60, 2);

						if($bookingpress_time_diff_for_cancel < $bookingpress_min_time_before_reschedule){
							$allow_rescheduling = false;
						}
					}
					
					if( '4' == $v['bookingpress_appointment_status'] || '3' == $v['bookingpress_appointment_status'] ){
						$allow_rescheduling = false;
						$allow_cancel_appointment = false;
					}
					
					if( !$allow_rescheduling && !$allow_cancel_appointment ){
						$hide_action_wrapper = true;
					}else if($bookingpress_min_time_before_cancel != 'disabled' && !$allow_cancel_appointment ){
						$hide_action_wrapper = true;
					}


					global $bookingpress_invoice;
					if( !empty( $bookingpress_invoice ) && method_exists( $bookingpress_invoice, 'is_addon_activated') && $bookingpress_invoice->is_addon_activated() && $hide_action_wrapper == true ){
						$hide_action_wrapper = false;
					}

					if( $hide_action_wrapper && $allow_rescheduling && $allow_customer_reschedule_apt == 'true'){						
						$hide_action_wrapper = false;
					}

					if( (!empty( $bookingpress_invoice ) && method_exists( $bookingpress_invoice, 'is_addon_activated') &&
					$bookingpress_invoice->is_addon_activated())) {						
						$hide_action_wrapper = false;
					}

					$bookingpress_service_duration_unit  = $bookingpress_appointments_data[$k]['bookingpress_service_duration_unit'];
					if( 'd' != $bookingpress_service_duration_unit ){
						$bpa_service_duration = $bookingpress_appointments_data[$k]['bookingpress_service_duration_val'];
						if( 'h' == $bookingpress_service_duration_unit ){
							$bpa_service_duration = $bpa_service_duration * 60;
						}
					}

					$service_start_time = $v['bookingpress_appointment_time'];
					$service_end_time   = $v['bookingpress_appointment_end_time'];
					//$bpa_service_duration;

					$service_duration = $bookingpress_appointments_data[$k]['bookingpress_service_duration_val'];
					
					if( 'd' != $bookingpress_service_duration_unit ){
						$bookingpress_tmp_start_time = new DateTime($service_start_time);
						$bookingpress_tmp_end_time = new DateTime($service_end_time);
						$booking_date_interval = $bookingpress_tmp_start_time->diff($bookingpress_tmp_end_time);
						$bookingpress_minute = $booking_date_interval->format('%i');
						$bookingpress_hour = $booking_date_interval->format('%h');
						$bookingpress_days = $booking_date_interval->format('%d');
						$service_duration = '';
						
						if($bookingpress_minute > 0) {
							$display_formatted_time = true;
							if( $bookingpress_minute == 1 ){
								$service_duration = $bookingpress_minute.' ' . esc_html__('Min', 'bookingpress-appointment-booking'); 
							}else{
								$service_duration = $bookingpress_minute.' ' . esc_html__('Mins', 'bookingpress-appointment-booking'); 
							}
						}
						
						if($bookingpress_hour > 0 ) {
							$display_formatted_time = true;
							if($bookingpress_hour == 1){
								$service_duration = $bookingpress_hour.' ' . esc_html__('Hour', 'bookingpress-appointment-booking').' '.$service_duration;
							}else{
								$service_duration = $bookingpress_hour.' ' . esc_html__('Hours', 'bookingpress-appointment-booking').' '.$service_duration;
							}
						}

						if($bookingpress_days == 1) {
							$service_duration = '24 ' . esc_html__('Hours', 'bookingpress-appointment-booking');
						}
						
					} else {
						if( 1 == $bookingpress_service_duration_unit ){
							$display_formatted_time = true;
							$service_duration .= ' ' . esc_html__('Day', 'bookingpress-appointment-booking');
						} else {   
							$display_formatted_time = true;
							$service_duration .= ' ' . esc_html__('Days', 'bookingpress-appointment-booking');
						}       
					}
					$formatted_time = $service_duration;

					$bookingpress_appointments_data[$k]['staff_first_name'] = $bookingpress_staff_first_name;
					$bookingpress_appointments_data[$k]['staff_last_name'] = $bookingpress_staff_last_name;
					$bookingpress_appointments_data[$k]['staff_email_address'] = $bookingpress_staff_email_address;
					$bookingpress_appointments_data[$k]['staff_avatar_url'] = $bookingpress_staff_avatar_url;
					$bookingpress_appointments_data[$k]['selected_extra_members'] = $bookingpress_selected_bring_anyone_members;
					$bookingpress_appointments_data[$k]['extras_details'] = $bookingpress_extra_service_details_arr;
					$bookingpress_appointments_data[$k]['deposit_amt'] = $bookingpress_deposit_amt;
					$bookingpress_appointments_data[$k]['deposit_amt_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_deposit_amt);
					$bookingpress_appointments_data[$k]['tax_amt'] = $bookingpress_tax_amount;
					$bookingpress_appointments_data[$k]['tax_amt_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_tax_amount);
					$bookingpress_appointments_data[$k]['coupon_discount_amt'] = $bookingpress_coupon_discount_amt;
					$bookingpress_appointments_data[$k]['coupon_discount_amt_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_coupon_discount_amt);
					$bookingpress_appointments_data[$k]['total_amt'] = $bookingpress_total_amt;
					$bookingpress_appointments_data[$k]['total_amt_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_total_amt);
					$bookingpress_appointments_data[$k]['is_cart'] = $bookingpress_is_cart;
					$bookingpress_appointments_data[$k]['is_deposit'] = $bookingpress_payment_log_details['is_deposit_enable'];
					$bookingpress_appointments_data[$k]['allow_rescheduling'] = $allow_rescheduling;
					$bookingpress_appointments_data[$k]['allow_cancelling'] = $allow_cancel_appointment;
					$bookingpress_appointments_data[$k]['hide_action_wrapper'] = $hide_action_wrapper;
					$bookingpress_appointments_data[$k]['display_formatted_time'] = $display_formatted_time;
					$bookingpress_appointments_data[$k]['formatted_time'] = $formatted_time;
					$bookingpress_appointments_data[$k]['bookingpress_guest_data'] = apply_filters('bookingpress_get_appointment_guest_data', $bookingpress_appointment_id);										

					$bookingpress_appointments_data[$k] = apply_filters('bookingpress_modify_my_appointments_data_externally', $bookingpress_appointments_data[$k]);
					$bookingpress_timezone = $bookingpress_appointments_data[$k]['bookingpress_appointment_timezone'];
					$booking_timeslot_start = $bookingpress_appointments_data[$k]['bookingpress_appointment_date'].' '.$bookingpress_appointments_data[$k]['bookingpress_appointment_time'];$booking_timeslot_end = $bookingpress_appointments_data[$k]['bookingpress_appointment_date'] .' '.$bookingpress_appointments_data[$k]['bookingpress_appointment_end_time'];

					$booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
					$booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);
					$formatted_date = date('Y-m-d', strtotime( $booking_timeslot_start ) );
					$formatted_end_date = date( 'Y-m-d', strtotime( $booking_timeslot_end . '+' . ( $bookingpress_appointments_data[$k]['bookingpress_service_duration_val'] - 1 ) . ' days' ) );

					$booking_timeslot_start = date('H:i:s', strtotime( $booking_timeslot_start ) );
					$booking_timeslot_end = date('H:i:s', strtotime( $booking_timeslot_end ) );
					$bookingpress_appointments_data[$k]['bookingpress_appointment_formatted_date'] = date_i18n($bookingpress_date_format,strtotime($formatted_date));
					$bookingpress_appointments_data[$k]['bookingpress_appointment_formatted_end_date'] = date_i18n($bookingpress_date_format,strtotime($formatted_end_date));
					$bookingpress_appointments_data[$k]['bookingpress_appointment_formatted_start_time'] = date($bookingpress_time_format,strtotime($booking_timeslot_start));
					$bookingpress_appointments_data[$k]['bookingpress_appointment_formatted_end_time'] = date($bookingpress_time_format,strtotime($booking_timeslot_end));

					$refund_data= $bookingpress_pro_appointment->bookingpress_allow_to_refund($v,0,1);
					$bookingpress_appointments_data[$k]['appointment_refund_status'] = $refund_data['allow_refund'];
					$bookingpress_refund_data = $bookingpress_pro_appointment->bookingpress_calculate_refund_amount($bookingpress_payment_id,$bookingpress_appointment_id,1);
					$currency_symbol = $BookingPress->bookingpress_get_currency_symbol($bookingpress_refund_data['refund_currency']);              
					$bookingpress_paid_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_refund_data['default_refund_amount'], $currency_symbol);
					$bookingpress_refund_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_refund_data['refund_amount'], $currency_symbol);
					$bookingpress_appointments_data[$k]['refund_amount'] = $bookingpress_refund_amount; 
					$bookingpress_appointments_data[$k]['default_refund_amount'] = $bookingpress_paid_amount; 
					$bookingpress_appointments_data[$k]['is_past_appointment'] = $bookingpress_refund_data['is_past_appointment'];

					$bookingpress_appointments_data[$k]['bookingpress_payment_status_class'] = apply_filters( 'bookingpress_modify_appointment_status_cls', $bookingpress_appointments_data[$k]['bookingpress_payment_status_class'], $bookingpress_appointments_data[$k] );
				}
				$data['items'] = $bookingpress_appointments_data;
			}

			return $data;
		}

		
		/**
		 * Callback function of [bookingpress_retry_payment] shortcode
		 *
		 * @return void
		 */
		function bookingpress_retry_payment_btn_func(){
			$content = "<button class='bpa-front-btn bpa-front-btn--primary' onclick='bookingpress_retry_payment()'>Retry Payment</button>";

			$bookingpress_inline_script = 'function bookingpress_retry_payment(){ 
				var bookingpress_uniq_id = window.app.appointment_step_form_data.bookingpress_uniq_id;
				document.getElementById("bpa-failed-screen-div").style.display = "none";
				document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).style.display = "block";
			}';
			wp_add_inline_script('bookingpress_elements_locale', $bookingpress_inline_script);
			return do_shortcode($content);
		}
		
		/**
		 * Render thank you content when redirection method set to in-built
		 *
		 * @return void
		 */
		function bookingpress_render_thankyou_content_func(){
			global $BookingPress, $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries, $bookingpress_appointment_bookings;
			$response              = array();
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );

			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				echo wp_json_encode( $response );
				die();
			}

			$bookingpress_uniq_id = !empty($_POST['bookingpress_uniq_id']) ? sanitize_text_field( $_POST['bookingpress_uniq_id'] ) : '';
			$appointment_id = 0;
			if(!empty($bookingpress_uniq_id)){
				$bookingpress_cart_id = !empty($_COOKIE['bookingpress_cart_id']) ? intval( base64_decode($_COOKIE['bookingpress_cart_id']) ) : 0; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				if(!empty($bookingpress_cart_id)){
					$appointment_id = $bookingpress_cart_id;
				}else{
					$bookingpress_cookie_name = $bookingpress_uniq_id."_appointment_data";
					if(!empty($_COOKIE[$bookingpress_cookie_name])){
						$bookingpress_cookie_value = $_COOKIE[$bookingpress_cookie_name]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						$bookingpress_entry_id = base64_decode($bookingpress_cookie_value);
						$appointment_id = $bookingpress_entry_id;
					}
				}
			}

			$bookingpress_thankyou_redirect_content = $BookingPress->bookingpress_get_customize_settings('bookingpress_thankyou_msg', 'booking_form');
			$bookingpress_thankyou_redirect_content = stripslashes($bookingpress_thankyou_redirect_content);
			$bookingpress_thankyou_redirect_content = do_shortcode($bookingpress_thankyou_redirect_content, true);

			$bookingpress_failed_redirect_content = $BookingPress->bookingpress_get_customize_settings('bookingpress_failed_payment_msg', 'booking_form');
			$bookingpress_failed_redirect_content = stripslashes($bookingpress_failed_redirect_content);
			$bookingpress_failed_redirect_content = do_shortcode($bookingpress_failed_redirect_content, true);

			$bpa_calendar_details = $bookingpress_appointment_bookings->bookingpress_get_appointment_details_for_calendar_func($appointment_id,true);
            
            $bpa_google_data = $bpa_calendar_details['google_calendar_link'];
            $bpa_yahoo_data =  $bpa_calendar_details['yahoo_calendar_link'];
            
            $bpa_google_link = "https://www.google.com/calendar/render?action=TEMPLATE&text=$bpa_google_data";
            $bpa_yahoo_link = "http://calendar.yahoo.com/?v=60&view=d&type=20&title=$bpa_yahoo_data";

			$response['variant'] = 'success';
			$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
			$response['thankyou_content'] = $bookingpress_thankyou_redirect_content;
			$response['bpa_google_cal_link'] = $bpa_google_link;
			$response['bpa_yahoo_cal_link'] = $bpa_yahoo_link;
			$response['failed_content'] = $bookingpress_failed_redirect_content;
			$response['appointment_id'] = $appointment_id;

			echo wp_json_encode($response);
			exit;
		}
		
		
		/**
		 * Modify disable data ajax request data
		 *
		 * @param  mixed $bookingpress_disable_date_xhr_data
		 * @return void
		 */
		function bookingpress_disable_date_xhr_data_func( $bookingpress_disable_date_xhr_data ){

			$bookingpress_disable_date_xhr_data .= '
				if( vm.bookingpress_is_extra_enable == 1 ){
					postData.service_extra_details = vm.appointment_step_form_data.bookingpress_selected_extra_details;
				};
			';

			return $bookingpress_disable_date_xhr_data;
		}
		
		/**
		 * Get service categories as per selection of staffmember
		 *
		 * @return void
		 */
		function bookingpress_get_service_cat_details_func(){
			global $BookingPress, $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services, $tbl_bookingpress_servicesmeta, $tbl_bookingpress_extra_services, $tbl_bookingpress_categories;
			$response              = array();
			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['service_categories_data'] = array();
			$response['first_cat_id'] = 0;

			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				echo wp_json_encode( $response );
				die();
			}

			$response['msg'] = esc_html__('Something went wrong while get staff member services', 'bookingpress-appointment-booking');

			$bookingpress_selected_staffmember_id = !empty($_POST['staffmember_id']) ? intval($_POST['staffmember_id']) : 0;
			if(!empty($bookingpress_selected_staffmember_id)){
				$response['variant'] = 'success';
				$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
				$response['msg'] = esc_html__('Services retireved successfully', 'bookingpress-appointment-booking');

				$bookingpress_staffmember_services_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_staffmember_id = %d GROUP BY bookingpress_service_id", $bookingpress_selected_staffmember_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm

				if(!empty($bookingpress_staffmember_services_data)){
					$bookingpress_services_data = $bookingpress_service_categories_data = array();
					foreach($bookingpress_staffmember_services_data as $k => $v){
						$bookingpress_staff_service_id = intval($v['bookingpress_service_id']);

						//SELECT wp_bookingpress_services.bookingpress_category_id, wp_bookingpress_categories.bookingpress_category_position FROM `wp_bookingpress_services` LEFT JOIN wp_bookingpress_categories ON wp_bookingpress_categories.bookingpress_category_id = wp_bookingpress_services.bookingpress_category_id GROUP BY wp_bookingpress_services.bookingpress_category_id;

						$bookingpress_categories_data = $wpdb->get_row($wpdb->prepare("SELECT services.bookingpress_category_id, categories.bookingpress_category_position FROM {$tbl_bookingpress_services} as services LEFT JOIN {$tbl_bookingpress_categories} as categories ON categories.bookingpress_category_id = services.bookingpress_category_id WHERE bookingpress_service_id = %d GROUP BY services.bookingpress_category_id", $bookingpress_staff_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm

						if(!empty($bookingpress_categories_data)){
							$bookingpress_category_id = $bookingpress_categories_data['bookingpress_category_id'];
							$bookingpress_category_pos = $bookingpress_categories_data['bookingpress_category_position'];

							if(!array_key_exists($bookingpress_category_pos, $bookingpress_service_categories_data)){
								$bookingpress_service_categories_data[$bookingpress_category_pos] = $bookingpress_category_id;
							}
						}
					}

					ksort($bookingpress_service_categories_data);

					$bookingpress_service_categories_details = array();
					$bookingpress_first_category_id = 0;
					if(!empty($bookingpress_service_categories_data)){
						if( version_compare( PHP_VERSION, '7.3.0', '<') ){
							$bookingpress_keys = array_keys( $bookingpress_service_categories_data );
							$bookingpress_first_key = $bookingpress_keys[0];
						} else {
							$bookingpress_first_key = array_key_first($bookingpress_service_categories_data);
						}
						$bookingpress_first_category_id = $bookingpress_service_categories_data[$bookingpress_first_key];

						foreach($bookingpress_service_categories_data as $k4 => $v4){
							$bookingpress_service_categories_details[] = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_categories} WHERE bookingpress_category_id = %d", $v4), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_categories is table name.
						}
					}

					$response['service_categories_data'] = $bookingpress_service_categories_details;
					$response['first_cat_id'] = $bookingpress_first_category_id;
				}
			}

			echo wp_json_encode($response);
			exit;
		}
		
		/**
		 * Modify dates to disable date if service and staff has their own working hours enabled
		 *
		 * @param  mixed $break_days
		 * @param  mixed $bookingpress_selected_service
		 * @param  mixed $bookingpress_selected_staffmember_id
		 * @return void
		 */
		function bookingpress_modify_working_hours_func($break_days, $bookingpress_selected_service,$bookingpress_selected_staffmember_id)
		{
			global $wpdb, $BookingPress, $tbl_bookingpress_staff_member_workhours, $bookingpress_services, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers_daysoff, $tbl_bookingpress_service_special_day, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_default_special_day, $tbl_bookingpress_servicesmeta,$tbl_bookingpress_service_workhours;


			if($bookingpress_pro_staff_members-> bookingpress_check_staffmember_module_activation() && !empty($bookingpress_selected_staffmember_id)){
				$bookingpress_is_staff_workhour_enable = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta($bookingpress_selected_staffmember_id, 'bookingpress_configure_specific_workhour');

				//if staffmember workhour enable then consider it
				if($bookingpress_is_staff_workhour_enable){

					$bookingpress_new_disable_dates_arr = array();
					$bookingpress_staffmember_working_days = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_is_break = %d", $bookingpress_selected_staffmember_id, 0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is table name defined globally. False alarm

					if( !empty( $bookingpress_staffmember_working_days ) ){
						$is_staffmember_monday_break             = 0;
						$is_staffmember_tuesday_break            = 0;
						$is_staffmember_wednesday_break          = 0;
						$is_staffmember_thursday_break           = 0;
						$is_staffmember_friday_break             = 0;
						$is_staffmember_saturday_break           = 0;
						$is_staffmember_sunday_break             = 0;

						foreach( $bookingpress_staffmember_working_days as $staffmember_workhour_key => $staffmember_workhour_val ){
							$bookingpress_staffmember_start_time = $staffmember_workhour_val['bookingpress_staffmember_workhours_start_time'];
							$bookingpress_staffmember_end_time   = $staffmember_workhour_val['bookingpress_staffmember_workhours_end_time'];

							if( 'monday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_monday_break = 1;
							} else if( 'tuesday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_tuesday_break = 1;
							} else if( 'wednesday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_wednesday_break = 1;
							} else if( 'thursday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_thursday_break = 1;
							} else if( 'friday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_friday_break = 1;
							} else if( 'saturday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_saturday_break = 1;
							} else if( 'sunday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_sunday_break = 1;
							}
						}

						$break_days['monday'] = $is_staffmember_monday_break;
						$break_days['tuesday'] = $is_staffmember_tuesday_break;
						$break_days['wednesday'] = $is_staffmember_wednesday_break;
						$break_days['thursday'] = $is_staffmember_thursday_break;
						$break_days['friday'] = $is_staffmember_friday_break;
						$break_days['saturday'] = $is_staffmember_saturday_break;
						$break_days['sunday'] = $is_staffmember_sunday_break;

						return $break_days;
					}
				}
			}

			if( !empty($bookingpress_selected_service)){

					// Get service working hours days
					$bookingpress_service_workhour_enable = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_servicemeta_value FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d AND bookingpress_servicemeta_name = 'bookingpress_configure_specific_service_workhour'", $bookingpress_selected_service ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_servicesmeta is table name defined globally. False Positive alarm

					//if service workhour enable then consider it
					if(is_array($bookingpress_service_workhour_enable) && isset($bookingpress_service_workhour_enable['bookingpress_servicemeta_value']) && $bookingpress_service_workhour_enable['bookingpress_servicemeta_value']){
						$bookingpress_new_disable_dates_arr = array();
						$bookingpress_staffmember_working_days = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = %d", $bookingpress_selected_service, 0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is table name defined globally. False alarm

						if( !empty( $bookingpress_staffmember_working_days ) ){
							$is_staffmember_monday_break             = 0;
							$is_staffmember_tuesday_break            = 0;
							$is_staffmember_wednesday_break          = 0;
							$is_staffmember_thursday_break           = 0;
							$is_staffmember_friday_break             = 0;
							$is_staffmember_saturday_break           = 0;
							$is_staffmember_sunday_break             = 0;

							foreach( $bookingpress_staffmember_working_days as $staffmember_workhour_key => $staffmember_workhour_val ){
								$bookingpress_staffmember_start_time = $staffmember_workhour_val['bookingpress_service_workhours_start_time'];
								$bookingpress_staffmember_end_time   = $staffmember_workhour_val['bookingpress_service_workhours_end_time'];

								if( 'monday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_monday_break = 1;
								} else if( 'tuesday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_tuesday_break = 1;
								} else if( 'wednesday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_wednesday_break = 1;
								} else if( 'thursday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_thursday_break = 1;
								} else if( 'friday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_friday_break = 1;
								} else if( 'saturday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_saturday_break = 1;
								} else if( 'sunday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_sunday_break = 1;
								}
							}

				            $break_days['monday'] = $is_staffmember_monday_break;
				            $break_days['tuesday'] = $is_staffmember_tuesday_break;
				            $break_days['wednesday'] = $is_staffmember_wednesday_break;
				            $break_days['thursday'] = $is_staffmember_thursday_break;
				            $break_days['friday'] = $is_staffmember_friday_break;
				            $break_days['saturday'] = $is_staffmember_saturday_break;
				            $break_days['sunday'] = $is_staffmember_sunday_break;


				            return $break_days;
						}
					}
			}


			return $break_days;
		}
		
		/**
		 * Add dates to disable date if service duration is days
		 *
		 * @param  mixed $response
		 * @param  mixed $bookingpress_selected_service
		 * @param  mixed $bookingpress_selected_date
		 * @param  mixed $bookingpress_appointment_data
		 * @return void
		 */
		function bookingpress_modify_disable_dates_func($response, $bookingpress_selected_service, $bookingpress_selected_date, $bookingpress_appointment_data){

			global $wpdb, $BookingPress, $tbl_bookingpress_staff_member_workhours, $bookingpress_services, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers_daysoff, $tbl_bookingpress_service_special_day, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_default_special_day, $tbl_bookingpress_servicesmeta,$tbl_bookingpress_service_workhours, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_service_daysoff, $tbl_bookingpress_default_daysoff;

			$bookingpress_disable_dates = $response;

			/** Disable General level repeat holidays */
			$retrieve_daysoff = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_default_daysoff} WHERE bookingpress_repeat = %d AND bookingpress_dayoff_parent = %d", 1, 0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table name defined globally. False Positive alarm
			
			$get_period_available_for_booking = $BookingPress->bookingpress_get_settings('period_available_for_booking', 'general_setting');
			if( empty( $get_period_available_for_booking ) || !$BookingPress->bpa_is_pro_active() ){
                $get_period_available_for_booking = 365;
            }

			$bookingpress_start_date = date('Y-m-d', current_time('timestamp') );

			/** Modify get available time of booking if the service expiration time is set */
			$get_period_available_for_booking = apply_filters( 'bookingpress_modify_max_available_time_for_booking', $get_period_available_for_booking, $bookingpress_start_date, $bookingpress_selected_service );

			$bookingpress_end_date = date('Y-m-d', strtotime( '+' . $get_period_available_for_booking . ' days') );

			if( !empty( $retrieve_daysoff ) ){
				foreach( $retrieve_daysoff as $daysoff_details_val ) {
					$daysoff_start_date = date('Y-m-d', strtotime( $daysoff_details_val['bookingpress_dayoff_date'] ) );

					if( $daysoff_start_date >= $bookingpress_end_date ){
						continue;
					}

					/** set the end date same as the start date if it's empty */
					if( empty( $daysoff_details_val['bookingpress_dayoff_enddate'] ) ){
						$daysoff_details_val['bookingpress_dayoff_enddate'] = $daysoff_details_val['bookingpress_dayoff_date'];
					}

					$daysoff_end_date = date( 'Y-m-d', strtotime( $daysoff_details_val['bookingpress_dayoff_enddate'] ) );

					$bpa_do_frequency = !empty( $daysoff_details_val['bookingpress_dayoff_repeat_frequency'] ) ? $daysoff_details_val['bookingpress_dayoff_repeat_frequency'] : 1;
					$bpa_do_frequency_type = !empty( $daysoff_details_val['bookingpress_dayoff_repeat_frequency_type'] ) ? $daysoff_details_val['bookingpress_dayoff_repeat_frequency_type'] : 'yearly';

					if( 'week' == $bpa_do_frequency_type ){
						$bpa_do_frequency_type = 'weekly';
					} else if( 'month' == $bpa_do_frequency_type ){
						$bpa_do_frequency_type = 'monthly';
					} else if( 'day' == $bpa_do_frequency_type ){
						$bpa_do_frequency_type = 'daily';
					} else if( 'year' == $bpa_do_frequency_type ){
						$bpa_do_frequency_type = 'yearly';
					}

					$bpa_do_duration = $daysoff_details_val['bookingpress_dayoff_repeat_duration'];

					if( 'until' == $bpa_do_duration && strtotime( $daysoff_start_date ) >= strtotime( $daysoff_details_val['bookingpress_dayoff_repeat_date'] ) ){
						continue;
					}

					$bpa_do_repeat_obj = new BookingPress_Repeat_Holiday();
					
					$bpa_do_repeat_obj->startDate( new DateTime( $daysoff_start_date ) );
					$bpa_do_repeat_obj->freq( $bpa_do_frequency_type );

					$bpa_do_repeat_obj->interval( $bpa_do_frequency );

					if( 'forever' == $bpa_do_duration ){
						$bpa_do_repeat_obj->until( new DateTime( $bookingpress_end_date ) );
					} else if( 'no_of_times' == $bpa_do_duration ){
						$bpa_do_repeat_obj->count( $daysoff_details_val['bookingpress_dayoff_repeat_times'] );
					} else if( 'until' == $bpa_do_duration ){
						$bpa_do_repeat_obj->until( new DateTime( $daysoff_details_val['bookingpress_dayoff_repeat_date'] ) );
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
									array_push( $bookingpress_disable_dates, $dt->format('c') );
								}
							} else {
								array_push( $bookingpress_disable_dates, $off_days->format('c') );
							}
						}
					}
					
				}
			}

			
			$bookingpress_new_disable_dates_arr = array();

			$bookingpress_selected_staffmember_id = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? intval($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) : 0;

			if($bookingpress_selected_staffmember_id == 0 && isset($bookingpress_appointment_data['bookingpress_selected_staffmember'])){
				$bookingpress_selected_staffmember_id = !empty($bookingpress_appointment_data['bookingpress_selected_staffmember'])?intval($bookingpress_appointment_data['bookingpress_selected_staffmember']):0;
			}

			//Allow default special days dates
			$bookingpress_default_special_days = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_default_special_day}" , ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day is table name defined globally. False alarm
			if(!empty($bookingpress_default_special_days) && is_array($bookingpress_default_special_days)){
				foreach($bookingpress_default_special_days as $k => $v){
					$bookingpress_start_date = date('c', strtotime($v['bookingpress_special_day_start_date']));
					$bookingpress_end_date = date('c', strtotime($v['bookingpress_special_day_end_date']));

					foreach($bookingpress_disable_dates as $k2 => $v2){
						if($v2 >= $bookingpress_start_date && $v2 <= $bookingpress_end_date){
							unset($bookingpress_disable_dates[$k2]);
						}
					}
				}
			}

			//Disable service level holidays
			$retrieve_daysoff = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_service_daysoff} WHERE bookingpress_service_id = %d", $bookingpress_selected_service ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_service_daysoff is table name defined globally. False Positive alarm
			if( !empty( $retrieve_daysoff ) ){

				$get_period_available_for_booking = $BookingPress->bookingpress_get_settings('period_available_for_booking', 'general_setting');
				if( empty( $get_period_available_for_booking ) || !$BookingPress->bpa_is_pro_active() ){
					$get_period_available_for_booking = 365;
				}

				$bookingpress_start_date = date('Y-m-d', current_time('timestamp') );

				/** Modify get available time of booking if the service expiration time is set */
				$get_period_available_for_booking = apply_filters( 'bookingpress_modify_max_available_time_for_booking', $get_period_available_for_booking, $bookingpress_start_date, $bookingpress_selected_service );

				$bookingpress_end_date = date('Y-m-d', strtotime( '+' . $get_period_available_for_booking . ' days') );

				foreach( $retrieve_daysoff as $daysoff_details_val ){
					if( 0 == $daysoff_details_val['bookingpress_service_daysoff_repeat' ] ){
						array_push( $bookingpress_disable_dates, date('c', strtotime( $daysoff_details_val['bookingpress_service_daysoff_date'] ) ) );
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
										array_push( $bookingpress_disable_dates, $dt->format('c') );
									}
								} else {
									array_push( $bookingpress_disable_dates, $off_days->format('c') );
								}
							}
						}
					}
				}
			}

			//Allow service level special days dates
			$bookingpress_service_special_days = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_service_special_day} WHERE bookingpress_service_id = %d", $bookingpress_selected_service), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day is table name defined globally. False alarm
			if(!empty($bookingpress_service_special_days) && is_array($bookingpress_service_special_days)){
				foreach($bookingpress_service_special_days as $k => $v){
					$bookingpress_start_date = date('c', strtotime($v['bookingpress_special_day_start_date']));
					$bookingpress_end_date = date('c', strtotime($v['bookingpress_special_day_end_date']));

					foreach($bookingpress_disable_dates as $k2 => $v2){
						if($v2 >= $bookingpress_start_date && $v2 <= $bookingpress_end_date){
							unset($bookingpress_disable_dates[$k2]);
						}
					}
				}
			}
			
			if($bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() && !empty($bookingpress_selected_staffmember_id)){

				/** If staff member working hours is off then also add that date to disable date */
				
				$is_staffmember_workhour_enable = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta($bookingpress_selected_staffmember_id, 'bookingpress_configure_specific_workhour');

				if( "true" == $is_staffmember_workhour_enable ){
					global $tbl_bookingpress_staff_member_workhours;
					$bookingpress_disabled_workhours = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_workday_key FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_start_time IS NULL", $bookingpress_selected_staffmember_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is a table name. false alarm
					
					$bpa_staffmember_disable_day = array();
					if( !empty( $bookingpress_disabled_workhours ) ){
						foreach( $bookingpress_disabled_workhours as $disable_dates ){
							$bpa_staffmember_disable_day[] = $disable_dates->bookingpress_staffmember_workday_key;
						}
						
						$bookingpress_max_days_for_booking          = $BookingPress->bookingpress_get_settings( 'period_available_for_booking', 'general_setting' );
						
						$current_site_date = date('Y-m-d', current_time( 'timestamp') );
						$max_avaialble_date = date('Y-m-d', strtotime( '+' . $bookingpress_max_days_for_booking . ' days' ) );

						$start_date = new DateTime( $current_site_date );
						$end_date = new DateTime( $max_avaialble_date );

						$interval = DateInterval::createFromDateString('1 day');
						$period = new DatePeriod( $start_date, $interval, $end_date );
						
						foreach( $period as $dt ){
							$current_date = $dt->format("c");
							$current_day_name = $dt->format('l');
							if( !in_array( $current_date, $bookingpress_disable_dates ) && in_array( $current_day_name, $bpa_staffmember_disable_day ) ){
								array_push( $bookingpress_disable_dates, $current_date );
							}
						}

					}					
				}
				

				// If staff member has any days off added then also add that date to disable dates
				$bookingpress_staffmember_daysoff = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_daysoff} WHERE bookingpress_staffmember_id = %d", $bookingpress_selected_staffmember_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_daysoff is table name defined globally. False alarm
				if(!empty($bookingpress_staffmember_daysoff) && is_array($bookingpress_staffmember_daysoff) ){

					$get_period_available_for_booking = $BookingPress->bookingpress_get_settings('period_available_for_booking', 'general_setting');
					if( empty( $get_period_available_for_booking ) || !$BookingPress->bpa_is_pro_active() ){
						$get_period_available_for_booking = 365;
					}

					$bookingpress_start_date = date('Y-m-d', current_time('timestamp') );

					/** Modify get available time of booking if the service expiration time is set */
					$get_period_available_for_booking = apply_filters( 'bookingpress_modify_max_available_time_for_booking', $get_period_available_for_booking, $bookingpress_start_date, $bookingpress_selected_service );

					$bookingpress_end_date = date('Y-m-d', strtotime( '+' . $get_period_available_for_booking . ' days') );


					foreach($bookingpress_staffmember_daysoff as $k => $staff_daysoff){
						
						$bookingpress_daysoff_date = $staff_daysoff['bookingpress_staffmember_daysoff_date'];
						$bookingpress_tmp_daysoff_date = date('c', strtotime($bookingpress_daysoff_date));
						$dayoff_year = date('Y', strtotime($bookingpress_daysoff_date));
						$default_year = date('Y', current_time('timestamp'));

						if (empty($staff_daysoff['bookingpress_staffmember_daysoff_repeat']) && !in_array($bookingpress_tmp_daysoff_date, $bookingpress_new_disable_dates_arr) ) {
							array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_daysoff_date);
						}else{
							$daysoff_start_date = date('Y-m-d', strtotime( $staff_daysoff['bookingpress_staffmember_daysoff_date'] ) );
							if( $daysoff_start_date >= $bookingpress_end_date ){
								continue;
							}
							$daysoff_end_date = date( 'Y-m-d', strtotime( $staff_daysoff['bookingpress_staffmember_daysoff_enddate'] ) );

							$bpa_do_frequency = !empty( $staff_daysoff['bookingpress_staffmember_daysoff_repeat_frequency'] ) ? $staff_daysoff['bookingpress_staffmember_daysoff_repeat_frequency'] : 1;
							$bpa_do_frequency_type = !empty( $staff_daysoff['bookingpress_staffmember_daysoff_repeat_frequency_type'] ) ? $staff_daysoff['bookingpress_staffmember_daysoff_repeat_frequency_type'] : 'yearly';

							if( 'week' == $bpa_do_frequency_type ){
								$bpa_do_frequency_type = 'weekly';
							} else if( 'month' == $bpa_do_frequency_type ){
								$bpa_do_frequency_type = 'monthly';
							} else if( 'day' == $bpa_do_frequency_type ){
								$bpa_do_frequency_type = 'daily';
							} else if( 'year' == $bpa_do_frequency_type ){
								$bpa_do_frequency_type = 'yearly';
							}

							$bpa_do_duration = $staff_daysoff['bookingpress_staffmember_daysoff_repeat_duration'];

							if( 'until' == $bpa_do_duration && strtotime( $daysoff_start_date ) >= strtotime( $staff_daysoff['bookingpress_staffmember_daysoff_repeat_date'] ) ){
								continue;
							}

							$bpa_do_repeat_obj = new BookingPress_Repeat_Holiday();
							
							$bpa_do_repeat_obj->startDate( new DateTime( $daysoff_start_date ) );
							$bpa_do_repeat_obj->freq( $bpa_do_frequency_type );

							$bpa_do_repeat_obj->interval( $bpa_do_frequency );

							if( 'forever' == $bpa_do_duration ){
								$bpa_do_repeat_obj->until( new DateTime( $bookingpress_end_date ) );
							} else if( 'no_of_times' == $bpa_do_duration ){
								$bpa_do_repeat_obj->count( $staff_daysoff['bookingpress_staffmember_daysoff_repeat_times'] );
							} else if( 'until' == $bpa_do_duration ){
								$bpa_do_repeat_obj->until( new DateTime( $staff_daysoff['bookingpress_staffmember_daysoff_repeat_date'] ) );
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
											array_push( $bookingpress_disable_dates, $dt->format('c') );
										}
									} else {
										array_push( $bookingpress_disable_dates, $off_days->format('c') );
									}
								}
							}
						}
					}
				}

				if( !empty( $bookingpress_new_disable_dates_arr ) ){
					$bookingpress_disable_dates = array_merge($bookingpress_disable_dates,$bookingpress_new_disable_dates_arr);
				}
				
				//Check if any special day added or not
				$bookingpress_staff_special_days = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_special_day} WHERE bookingpress_staffmember_id = %d AND (bookingpress_special_day_service_id LIKE %s OR bookingpress_special_day_service_id = '')", $bookingpress_selected_staffmember_id, '%'.$bookingpress_selected_service.'%'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day is table name defined globally. False alarm

				if(!empty($bookingpress_staff_special_days) && is_array($bookingpress_staff_special_days)){
					foreach($bookingpress_staff_special_days as $k3 => $v3){
						$bookingpress_special_day_start_date = date('c', strtotime($v3['bookingpress_special_day_start_date']));
						$bookingpress_special_day_end_date = date('c', strtotime($v3['bookingpress_special_day_end_date']));

						foreach($bookingpress_disable_dates as $k4 => $v4){
						if($v4 >= $bookingpress_special_day_start_date && $v4 <= $bookingpress_special_day_end_date){
								unset($bookingpress_disable_dates[$k4]);
							}
						}
					}
				}				

				/** disable date if another service is booked on the date and the selected service unit is in days */

				if( !empty( $bookingpress_appointment_data['selected_service_duration_unit'] ) && 'd' == $bookingpress_appointment_data['selected_service_duration_unit']  ){

					$first_date_of_month = date('Y-m', current_time('timestamp') ) . '-01';
					$get_period_available_for_booking = $BookingPress->bookingpress_get_settings('period_available_for_booking', 'general_setting');
					
					$last_date_of_month = date('Y-m-t', strtotime( $first_date_of_month . '+' . $get_period_available_for_booking . ' days' ) );
					
					$start_date = new DateTime( $first_date_of_month );
					$end_date = new DateTime( $last_date_of_month );
					
					$interval = DateInterval::createFromDateString('1 day');
					$period = new DatePeriod( $start_date, $interval, $end_date );

					foreach( $period as $dt ){
						$current_date = $dt->format("Y-m-d H:i:s");
						$date_t = date('c', strtotime( $current_date ) );
						
						if( !in_array( $date_t, $bookingpress_disable_dates ) ){
							$current_sel_date = $dt->format('Y-m-d');
							$get_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s AND bookingpress_staff_member_id = %d AND bookingpress_service_id != %d AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s)", $current_sel_date, $bookingpress_selected_staffmember_id, $bookingpress_selected_service, '1', '2' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
							if( !empty( $get_appointments ) ){
								foreach( $get_appointments as $appointment_dt ){

									$service_duration = $appointment_dt->bookingpress_service_duration_val;
									$service_duration_unit = $appointment_dt->bookingpress_service_duration_unit;
									if( 1 < $service_duration && $service_duration_unit == 'd' ){		
										$tmp_start_date = $current_sel_date;
										$tmp_end_date = date('Y-m-d', strtotime( $current_sel_date . '+' . $service_duration . ' days' ) );
										
										$tstart_date = new DateTime( $tmp_start_date );
										$tend_date = new DateTime( $tmp_end_date );
										
										$tinterval = DateInterval::createFromDateString('1 day');
										$tperiod = new DatePeriod( $tstart_date, $tinterval, $tend_date );
										
										foreach( $tperiod as $tdt ){
											$tcurrent_date = $tdt->format("c");
											array_push( $bookingpress_disable_dates, $tcurrent_date );
										}
									} else {
										array_push( $bookingpress_disable_dates, $date_t );
									}
								}
							}
						}
					}
				}				
			}

			//Get service minimum time required and disabled dates if minimium time is greater than or equal to 24 hours
			//---------------------------------------------------------------------------
			//$bookingpress_minimum_time_required_for_booking = $bookingpress_services->bookingpress_get_service_meta( $bookingpress_selected_service, 'minimum_time_required_before_booking' ); // Selected service meta value
			$bookingpress_minimum_time_required_for_booking = 'disabled';
            $bookingpress_minimum_time_required_for_booking = apply_filters( 'bookingpress_retrieve_minimum_required_time', $bookingpress_minimum_time_required_for_booking, $bookingpress_selected_service );

			
			if ( $bookingpress_minimum_time_required_for_booking != 'disabled' && $bookingpress_minimum_time_required_for_booking >= 1440 ) {
				$bookingpress_total_days = intval( $bookingpress_minimum_time_required_for_booking ) / 1440;

				/** reputelog - need to confirm this change with every aspects */
				$booking_date_timestamp = strtotime( $bookingpress_selected_date . ' 23:59:59' );
				
				$bookingpress_current_date           = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
				$bookingpress_current_date_timestamp = strtotime( $bookingpress_current_date );
				
				if ( $booking_date_timestamp == $bookingpress_current_date_timestamp ) {
					if(!in_array($booking_date_timestamp, $bookingpress_new_disable_dates_arr)){
						array_push( $bookingpress_new_disable_dates_arr, date( 'c', $booking_date_timestamp ) );
					}					
					for ( $i = 1; $i <= $bookingpress_total_days; $i++ ) {
						$bookingpress_next_date = date( 'c', strtotime( '+' . $i . 'days', $bookingpress_current_date_timestamp ) );
						if(!in_array($bookingpress_next_date, $bookingpress_new_disable_dates_arr)){
							if( !empty( $bookingpress_appointment_data['selected_service_duration_unit'] ) && 'd' == $bookingpress_appointment_data['selected_service_duration_unit']  ){
								$bookingpress_next_date = date( 'Y-m-d', strtotime( '+' . $i . 'days', $bookingpress_current_date_timestamp ) ).'T00:00:00+00:00';
							}
							array_push( $bookingpress_new_disable_dates_arr, $bookingpress_next_date );
						}
					}
				} else {

					/* New Logic For Not Disable Day Issue When Back Button Press */										
					$bookingpress_date_diff_in_minutes = round( abs( $booking_date_timestamp - $bookingpress_current_date_timestamp ) / 60, 2 );					
					if ( $bookingpress_date_diff_in_minutes <= $bookingpress_minimum_time_required_for_booking ) {
						if(!in_array($booking_date_timestamp, $bookingpress_new_disable_dates_arr)){	
							if( !empty( $bookingpress_appointment_data['selected_service_duration_unit'] ) && 'd' == $bookingpress_appointment_data['selected_service_duration_unit']  ){
								array_push( $bookingpress_new_disable_dates_arr, date( 'Y-m-d', $booking_date_timestamp ).'T00:00:00+00:00' );
							}else{
								array_push( $bookingpress_new_disable_dates_arr, date( 'c', $booking_date_timestamp ) );
							}							
						}																		
						for ( $i = 1; $i < $bookingpress_total_days; $i++ ) {
							$bookingpress_next_date = date( 'c', strtotime( '+' . $i . 'days', $bookingpress_current_date_timestamp ) );
							if(!in_array($bookingpress_next_date, $bookingpress_new_disable_dates_arr)){
								if( !empty( $bookingpress_appointment_data['selected_service_duration_unit'] ) && 'd' == $bookingpress_appointment_data['selected_service_duration_unit']  ){
									$bookingpress_next_date = date( 'Y-m-d', strtotime( '+' . $i . 'days', $bookingpress_current_date_timestamp ) ).'T00:00:00+00:00';
								}
								array_push( $bookingpress_new_disable_dates_arr, $bookingpress_next_date );
							}
						}
					}else{
						/* New Logic For Not Disable Days In Day Service When Back Button Press */												
						$booking_date_timestamp_new = strtotime(date( 'Y-m-d', $bookingpress_current_date_timestamp ). ' 23:59:59');
						if(!in_array($booking_date_timestamp_new, $bookingpress_new_disable_dates_arr)){	
							if( !empty( $bookingpress_appointment_data['selected_service_duration_unit'] ) && 'd' == $bookingpress_appointment_data['selected_service_duration_unit']  ){
								array_push( $bookingpress_new_disable_dates_arr, date( 'Y-m-d', $bookingpress_current_date_timestamp ).'T00:00:00+00:00' );
							}else{
								array_push( $bookingpress_new_disable_dates_arr, date( 'c', $bookingpress_current_date_timestamp ) );
								array_push( $bookingpress_new_disable_dates_arr, date( 'Y-m-d', $bookingpress_current_date_timestamp ).'T00:00:00+00:00' );
							}							
						}
						for ( $i = 1; $i < $bookingpress_total_days; $i++ ) {
							$bookingpress_next_date = date( 'c', strtotime( '+' . $i . 'days', $bookingpress_current_date_timestamp ) );
							if(!in_array($bookingpress_next_date, $bookingpress_new_disable_dates_arr)){
								if( !empty( $bookingpress_appointment_data['selected_service_duration_unit'] ) && 'd' == $bookingpress_appointment_data['selected_service_duration_unit']  ){
									$bookingpress_next_date = date( 'Y-m-d', strtotime( '+' . $i . 'days', $bookingpress_current_date_timestamp ) ).'T00:00:00+00:00';
									array_push( $bookingpress_new_disable_dates_arr, $bookingpress_next_date );
								}
								array_push( $bookingpress_new_disable_dates_arr, $bookingpress_next_date );
							}
						}						

					}					
				}				
				if( !empty( $bookingpress_new_disable_dates_arr ) ){
					$bookingpress_disable_dates =  array_merge( $bookingpress_disable_dates, $bookingpress_new_disable_dates_arr );
				}
			}
			//---------------------------------------------------------------------------

			/** Disable dates for multiple days event */
			if( !empty( $bookingpress_appointment_data['selected_service_duration_unit'] ) && 'd' == $bookingpress_appointment_data['selected_service_duration_unit'] && 1 < $bookingpress_appointment_data['selected_service_duration'] ){
				$service_duration_val = $bookingpress_appointment_data['selected_service_duration'] - 1;
				$multiple_day_event_disable_dates = array();
				foreach( $bookingpress_disable_dates as $disable_dates ){
					$offday = date('Y-m-d',strtotime($disable_dates) );
					for( $do = $service_duration_val; $do > 0; $do-- ){
						$multiple_day_event_disable_dates[] = date( 'c', strtotime( $disable_dates . '-' . $do . ' days' ));
					}
				}
				if( !empty( $multiple_day_event_disable_dates ) ){
					$bookingpress_disable_dates = array_merge( $bookingpress_disable_dates, $multiple_day_event_disable_dates );
				}
			}
			
			return $bookingpress_disable_dates;

		}



		function bookingpress_modify_disable_dates_func_24jul2022_backup_dimple($response, $bookingpress_selected_service, $bookingpress_selected_date, $bookingpress_appointment_data){
			global $wpdb, $BookingPress, $tbl_bookingpress_staff_member_workhours, $bookingpress_services, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers_daysoff, $tbl_bookingpress_service_special_day, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_default_special_day, $tbl_bookingpress_servicesmeta,$tbl_bookingpress_service_workhours;

			$bookingpress_disable_dates = explode(',', $response['days_off_disabled_dates']);
			$bookingpress_new_disable_dates_arr = array();

			$bookingpress_selected_staffmember_id = !empty($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? intval($bookingpress_appointment_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) : 0;

			//Allow default special days dates
			$bookingpress_default_special_days = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_default_special_day}", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day is table name defined globally. False alarm
			if(!empty($bookingpress_default_special_days) && is_array($bookingpress_default_special_days)){
				foreach($bookingpress_default_special_days as $k => $v){
					$bookingpress_start_date = date('c', strtotime($v['bookingpress_special_day_start_date']));
					$bookingpress_end_date = date('c', strtotime($v['bookingpress_special_day_end_date']));

					foreach($bookingpress_disable_dates as $k2 => $v2){
						if($v2 <= $bookingpress_start_date && $v2 >= $bookingpress_end_date){
							unset($bookingpress_disable_dates[$k2]);
						}
					}
				}
			}

			$bookingpress_service_data = $BookingPress->get_service_by_id($bookingpress_selected_service);





			if( !empty($bookingpress_selected_service) && !empty($bookingpress_selected_date) ){

					// Get service working hours days
					$bookingpress_service_workhour_enable = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_servicemeta_value FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d AND bookingpress_servicemeta_name = 'bookingpress_configure_specific_service_workhour'", $bookingpress_selected_service ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_servicesmeta is table name defined globally. False Positive alarm

					//if service workhour enable then consider it
					if(is_array($bookingpress_service_workhour_enable) && isset($bookingpress_service_workhour_enable['bookingpress_servicemeta_value']) && $bookingpress_service_workhour_enable['bookingpress_servicemeta_value']){
						$bookingpress_new_disable_dates_arr = array();
						$bookingpress_staffmember_working_days = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = %d", $bookingpress_selected_service, 0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is table name defined globally. False alarm

						if( !empty( $bookingpress_staffmember_working_days ) ){
							$is_staffmember_monday_break             = 0;
							$is_staffmember_tuesday_break            = 0;
							$is_staffmember_wednesday_break          = 0;
							$is_staffmember_thursday_break           = 0;
							$is_staffmember_friday_break             = 0;
							$is_staffmember_saturday_break           = 0;
							$is_staffmember_sunday_break             = 0;

							foreach( $bookingpress_staffmember_working_days as $staffmember_workhour_key => $staffmember_workhour_val ){
								$bookingpress_staffmember_start_time = $staffmember_workhour_val['bookingpress_service_workhours_start_time'];
								$bookingpress_staffmember_end_time   = $staffmember_workhour_val['bookingpress_service_workhours_end_time'];

								if( 'monday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_monday_break = 1;
								} else if( 'tuesday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_tuesday_break = 1;
								} else if( 'wednesday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_wednesday_break = 1;
								} else if( 'thursday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_thursday_break = 1;
								} else if( 'friday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_friday_break = 1;
								} else if( 'saturday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_saturday_break = 1;
								} else if( 'sunday' == strtolower( $staffmember_workhour_val['bookingpress_service_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_sunday_break = 1;
								}
							}

							$default_year            = date('Y', current_time('timestamp'));

							$calendar_start_date = $calendar_next_date = date('Y-m-d', current_time('timestamp'));
							$calendar_end_date   = date('Y-m-d', strtotime('+1 year', current_time('timestamp')));
							for ( $i = 1; $i <= 730; $i++ ) {
								$current_day_name = date('l', strtotime($calendar_next_date));
								if ($current_day_name == 'Monday' && $is_staffmember_monday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Tuesday' && $is_staffmember_tuesday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Wednesday' && $is_staffmember_wednesday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Thursday' && $is_staffmember_thursday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Friday' && $is_staffmember_friday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Saturday' && $is_staffmember_saturday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Sunday' && $is_staffmember_sunday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								}

								$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
							}
						}
					}	

					if(empty($bookingpress_new_disable_dates_arr)){
						$bookingpress_new_disable_dates_arr = $bookingpress_disable_dates; //If staff member has no default week offs then consider default daysoff
					}


				// If service has any sepcial days add and if that date added as disabled date then remove that date
				$bookingpress_service_special_days = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_service_special_day} WHERE bookingpress_service_id = %d", $bookingpress_selected_service), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_special_day is table name defined globally. False alarm
				if(!empty($bookingpress_service_special_days) && is_array($bookingpress_service_special_days) ){
					foreach($bookingpress_service_special_days as $k => $v){
						$bookingpress_start_date = date('c', strtotime($v['bookingpress_special_day_start_date']));
						$bookingpress_end_date = date('c', strtotime($v['bookingpress_special_day_end_date']));

						foreach($bookingpress_disable_dates as $k2 => $v2){
							if($v2 <= $bookingpress_start_date && $v2 >= $bookingpress_end_date){
								unset($bookingpress_disable_dates[$k2]);
							}
						}
					}
				}
				
                
				//If service duration is set to 'Days' then disable dates
                if(!empty($bookingpress_service_data['bookingpress_service_duration_unit']) && $bookingpress_service_data['bookingpress_service_duration_unit'] == "d" ){
                    $bookingpress_service_duration = $bookingpress_service_data['bookingpress_service_duration_val'];

					foreach($bookingpress_disable_dates as $k => $v){
						$bookingpress_tmp_date = date('Y-m-d', strtotime($v));
						$bookingpress_tmp_disable_date = date('c', strtotime($bookingpress_tmp_date));
						if(!in_array($bookingpress_tmp_disable_date, $bookingpress_new_disable_dates_arr)){
							array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_disable_date);
						}
						for($i=1; $i<=$bookingpress_service_duration; $i++){
							$bookingpress_new_tmp_disable_date = date('c', strtotime($bookingpress_tmp_date." - ".$i." days"));
							if(!in_array($bookingpress_new_tmp_disable_date, $bookingpress_new_disable_dates_arr)){
								array_push($bookingpress_new_disable_dates_arr, $bookingpress_new_tmp_disable_date);
							}
						}
					}
                }
				
				//If any items added to cart then also add that date as disable dates
				if(!empty($bookingpress_appointment_data['cart_items'])){
					foreach($bookingpress_appointment_data['cart_items'] as $k => $v){
						$bookingpress_service_duration_unit = $v['bookingpress_service_duration_unit'];
						$bookingpress_service_duration_val = $v['bookingpress_service_duration_val'];

						if($bookingpress_service_duration_unit == "d"){
							$bookingpress_tmp_date = date('Y-m-d', strtotime($v['bookingpress_selected_date']));

							for($i=1; $i<=$bookingpress_service_duration_val; $i++){
								$bookingpress_new_tmp_disable_date = date('c', strtotime($bookingpress_tmp_date." - ".$i." days"));
								if(!in_array($bookingpress_new_tmp_disable_date, $bookingpress_new_disable_dates_arr)){
									array_push($bookingpress_new_disable_dates_arr, $bookingpress_new_tmp_disable_date);
								}
							}	
						}
					}
				}
			}

			//---------------------------------------------------------------------------


			//Get staff member working days and add week off days dates
			//---------------------------------------------------------------------------

				if($bookingpress_pro_staff_members-> bookingpress_check_staffmember_module_activation() && !empty($bookingpress_selected_staffmember_id)){
					$bookingpress_is_staff_workhour_enable = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta($bookingpress_selected_staffmember_id, 'bookingpress_configure_specific_workhour');

					//if staffmember workhour enable then consider it
					if($bookingpress_is_staff_workhour_enable){
						$bookingpress_new_disable_dates_arr = array();
						$bookingpress_staffmember_working_days = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_is_break = %d", $bookingpress_selected_staffmember_id, 0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is table name defined globally. False alarm

						if( !empty( $bookingpress_staffmember_working_days ) ){
							$is_staffmember_monday_break             = 0;
							$is_staffmember_tuesday_break            = 0;
							$is_staffmember_wednesday_break          = 0;
							$is_staffmember_thursday_break           = 0;
							$is_staffmember_friday_break             = 0;
							$is_staffmember_saturday_break           = 0;
							$is_staffmember_sunday_break             = 0;

							foreach( $bookingpress_staffmember_working_days as $staffmember_workhour_key => $staffmember_workhour_val ){
								$bookingpress_staffmember_start_time = $staffmember_workhour_val['bookingpress_staffmember_workhours_start_time'];
								$bookingpress_staffmember_end_time   = $staffmember_workhour_val['bookingpress_staffmember_workhours_end_time'];

								if( 'monday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_monday_break = 1;
								} else if( 'tuesday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_tuesday_break = 1;
								} else if( 'wednesday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_wednesday_break = 1;
								} else if( 'thursday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_thursday_break = 1;
								} else if( 'friday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_friday_break = 1;
								} else if( 'saturday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_saturday_break = 1;
								} else if( 'sunday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
									$is_staffmember_sunday_break = 1;
								}
							}

							$default_year            = date('Y', current_time('timestamp'));

							$calendar_start_date = $calendar_next_date = date('Y-m-d', current_time('timestamp'));
							$calendar_end_date   = date('Y-m-d', strtotime('+1 year', current_time('timestamp')));
							for ( $i = 1; $i <= 730; $i++ ) {
								$current_day_name = date('l', strtotime($calendar_next_date));
								if ($current_day_name == 'Monday' && $is_staffmember_monday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Tuesday' && $is_staffmember_tuesday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Wednesday' && $is_staffmember_wednesday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Thursday' && $is_staffmember_thursday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Friday' && $is_staffmember_friday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Saturday' && $is_staffmember_saturday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								} elseif ($current_day_name == 'Sunday' && $is_staffmember_sunday_break == 1 ) {
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									$bookingpress_tmp_date = date('c', strtotime($daysoff_tmp_date));
									if(!in_array($bookingpress_tmp_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_date);
									}
								}

								$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
							}
						}
					}

					// If staff member has any days off added then also add that date to disable dates
					$bookingpress_staffmember_daysoff = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_daysoff} WHERE bookingpress_staffmember_id = %d", $bookingpress_selected_staffmember_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_daysoff is table name defined globally. False alarm
					if(!empty($bookingpress_staffmember_daysoff) && is_array($bookingpress_staffmember_daysoff) ){
						foreach($bookingpress_staffmember_daysoff as $k => $v){
							$bookingpress_daysoff_date = $v['bookingpress_staffmember_daysoff_date'];
							$bookingpress_tmp_daysoff_date = date('c', strtotime($bookingpress_daysoff_date));
							$dayoff_year = date('Y', strtotime($bookingpress_daysoff_date));
							$default_year = date('Y', current_time('timestamp'));

							if (empty($v['bookingpress_staffmember_daysoff_repeat']) && !in_array($bookingpress_tmp_daysoff_date, $bookingpress_new_disable_dates_arr) ) {
								array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_daysoff_date);
							}else{
								for($i = $default_year; $i <= 2035; $i++){
									$daysoff_new_date_month = $i . '-' . date('m-d', strtotime($bookingpress_daysoff_date));
									$daysoff_new_date_month_tmp = date('c', strtotime($daysoff_new_date_month));
									if(!in_array($daysoff_new_date_month_tmp, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $daysoff_new_date_month_tmp);
									}
								}
							}
						}
					}

					if(empty($bookingpress_new_disable_dates_arr)){
						$bookingpress_new_disable_dates_arr = $bookingpress_disable_dates; //If staff member has no default week offs then consider default daysoff
					}

					//Check if any special day added or not
					$bookingpress_staff_special_days = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_special_day} WHERE bookingpress_staffmember_id = %d AND (bookingpress_special_day_service_id LIKE %s OR bookingpress_special_day_service_id = '')", $bookingpress_selected_staffmember_id, '%'.$bookingpress_selected_service.'%'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day is table name defined globally. False alarm

					if(!empty($bookingpress_staff_special_days) && is_array($bookingpress_staff_special_days)){
						foreach($bookingpress_staff_special_days as $k3 => $v3){
							$bookingpress_special_day_start_date = date('c', strtotime($v3['bookingpress_special_day_start_date']));
							$bookingpress_special_day_end_date = date('c', strtotime($v3['bookingpress_special_day_end_date']));

							foreach($bookingpress_new_disable_dates_arr as $k4 => $v4){
							if($v4 >= $bookingpress_special_day_start_date && $v4 <= $bookingpress_special_day_end_date){
									unset($bookingpress_new_disable_dates_arr[$k4]);
								}
							}
						}
					}


					//If service duration is set to 'Days' then disable dates
					if(!empty($bookingpress_service_data['bookingpress_service_duration_unit']) && $bookingpress_service_data['bookingpress_service_duration_unit'] == "d" ){
						$bookingpress_service_duration = $bookingpress_service_data['bookingpress_service_duration_val'];

						foreach($bookingpress_new_disable_dates_arr as $k => $v){
							$bookingpress_tmp_date = date('Y-m-d', strtotime($v));
							$bookingpress_tmp_disable_date = date('c', strtotime($bookingpress_tmp_date));
							if(!in_array($bookingpress_tmp_disable_date, $bookingpress_new_disable_dates_arr)){
								array_push($bookingpress_new_disable_dates_arr, $bookingpress_tmp_disable_date);
							}
							for($i=1; $i<=$bookingpress_service_duration; $i++){
								$bookingpress_new_tmp_disable_date = date('c', strtotime($bookingpress_tmp_date." - ".$i." days"));
								if(!in_array($bookingpress_new_tmp_disable_date, $bookingpress_new_disable_dates_arr)){
									array_push($bookingpress_new_disable_dates_arr, $bookingpress_new_tmp_disable_date);
								}
							}
						}
					}

					//If any items added to cart then also add that date as disable dates
					if(!empty($bookingpress_appointment_data['cart_items'])){
						foreach($bookingpress_appointment_data['cart_items'] as $k => $v){
							$bookingpress_service_duration_unit = $v['bookingpress_service_duration_unit'];
							$bookingpress_service_duration_val = $v['bookingpress_service_duration_val'];

							if($bookingpress_service_duration_unit == "d"){
								$bookingpress_tmp_date = date('Y-m-d', strtotime($v['bookingpress_selected_date']));

								if(!in_array(date('c', strtotime($bookingpress_tmp_date)), $bookingpress_new_disable_dates_arr)){
									array_push($bookingpress_new_disable_dates_arr, date('c', strtotime($bookingpress_tmp_date)));
								}

								for($i=1; $i<=$bookingpress_service_duration_val; $i++){
									$bookingpress_new_tmp_disable_date = date('c', strtotime($bookingpress_tmp_date." - ".$i." days"));
									if(!in_array($bookingpress_new_tmp_disable_date, $bookingpress_new_disable_dates_arr)){
										array_push($bookingpress_new_disable_dates_arr, $bookingpress_new_tmp_disable_date);
									}
								}	
							}
						}
					}

				}
			//---------------------------------------------------------------------------

			
			//Get service minimum time required and disabled dates if minimium time is greater than or equal to 24 hours
			//---------------------------------------------------------------------------
				$bookingpress_minimum_time_required_for_booking = $bookingpress_services->bookingpress_get_service_meta( $bookingpress_selected_service, 'minimum_time_required_before_booking' ); // Selected service meta value

				if ( $bookingpress_minimum_time_required_for_booking != 'disabled' && $bookingpress_minimum_time_required_for_booking >= 1440 ) {
					$bookingpress_total_days = intval( $bookingpress_minimum_time_required_for_booking / 1440 );

					$booking_date_timestamp = strtotime( $bookingpress_selected_date );

					$bookingpress_current_date           = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
					$bookingpress_current_date_timestamp = strtotime( $bookingpress_current_date );

					if ( $booking_date_timestamp == $bookingpress_current_date_timestamp ) {
						if(!in_array($booking_date_timestamp, $bookingpress_new_disable_dates_arr)){
							array_push( $bookingpress_new_disable_dates_arr, date( 'c', $booking_date_timestamp ) );
						}
						for ( $i = 1; $i <= $bookingpress_total_days; $i++ ) {
							$bookingpress_next_date = date( 'c', strtotime( '+' . $i . 'days', $bookingpress_current_date_timestamp ) );
							if(!in_array($bookingpress_next_date, $bookingpress_new_disable_dates_arr)){
								array_push( $bookingpress_new_disable_dates_arr, $bookingpress_next_date );
							}
						}
					} else {
						$bookingpress_date_diff_in_minutes = round( abs( $booking_date_timestamp - $bookingpress_current_date_timestamp ) / 60, 2 );

						if ( $bookingpress_date_diff_in_minutes <= $bookingpress_minimum_time_required_for_booking ) {
							if(!in_array($booking_date_timestamp, $bookingpress_new_disable_dates_arr)){
								array_push( $bookingpress_new_disable_dates_arr, date( 'c', $booking_date_timestamp ) );
							}
							for ( $i = 1; $i < $bookingpress_total_days; $i++ ) {
								$bookingpress_next_date = date( 'c', strtotime( '+' . $i . 'days', $bookingpress_current_date_timestamp ) );
								if(!in_array($bookingpress_next_date, $bookingpress_new_disable_dates_arr)){
									array_push( $bookingpress_new_disable_dates_arr, $bookingpress_next_date );
								}
							}
						}
					}
				}
			//---------------------------------------------------------------------------

			if(!empty($bookingpress_new_disable_dates_arr)){
				$response['days_off_disabled_dates'] = implode(',', $bookingpress_new_disable_dates_arr);
			}

			return $response;
		}

		
		/**
		 * Method for execute after selecting payment method at front side.
		 *
		 * @param  mixed $bookingpress_after_selecting_payment_method_data
		 * @return void
		 */
		function bookingpress_after_selecting_payment_method_func($bookingpress_after_selecting_payment_method_data){
			if(empty($_GET['bkp_pay'])){
				$bookingpress_after_selecting_payment_method_data .= 'vm.bookingpress_get_final_step_amount();';
			}
			return $bookingpress_after_selecting_payment_method_data;
		}
		
		/**
		 * Get staff member id when any staff option selected at frontend
		 *
		 * @return void
		 */
		function bookingpress_get_any_staffmember_id_func(){
			global $BookingPress, $wpdb, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services, $tbl_bookingpress_appointment_bookings;
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

			$response['variant'] = 'error';
			$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Something went wrong while processing with request', 'bookingpress-appointment-booking' );
			$response['staffmember_id'] = 0;

			$check_capacity = false;
			$bring_capacity = 1;
			if( !empty( $_POST['selected_bring_members'] ) && 1 < $_POST['selected_bring_members'] ){
				$check_capacity = true;
				$bring_capacity = intval( $_POST['selected_bring_members'] );
			}

			$bookingpress_selected_service_id = !empty($_POST['service_id']) ? intval($_POST['service_id']) : 0;

			if($bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ){
				$bookingpress_staffmember_id = 0;

				$bookingpress_current_date = date('Y-m-d', current_time('timestamp'));
				//$bookingpress_week_start_date = date('Y-m-d', strtotime("-3 days", strtotime($bookingpress_current_date)));
				$bookingpress_week_start_date = $bookingpress_current_date;
				$bookingpress_week_end_date = date( 'Y-m-d', strtotime( "+1 week", strtotime( $bookingpress_current_date ) ) );

				$bookingpress_any_staff_selected_rule = $BookingPress->bookingpress_get_settings('bookingpress_staffmember_auto_assign_rule', 'staffmember_setting');
				
				$where_clause = " AND 1=1 ";
				if( true == $check_capacity ){
					$where_clause .= $wpdb->prepare( " AND bookingpress_service_capacity >= %d", $bring_capacity );
				}
				
				if( "least_assigned_by_day" == $bookingpress_any_staff_selected_rule || "most_assigned_by_day" == $bookingpress_any_staff_selected_rule ){

					$ordby = "ASC";
					$minmax = "min";
					if( "most_assigned_by_day" == $bookingpress_any_staff_selected_rule ){
						$ordby = "DESC";
						$minmax = "max";
					}
					
					if(!empty($bookingpress_selected_service_id)){

						$bookingpress_search_query       = 'WHERE 1=1 ';
						$bookingpress_search_query_where = "AND (bookingpress_service_id = {$bookingpress_selected_service_id} ) ";
						$bookingpress_search_query_where .= "AND ( bookingpress_appointment_date LIKE '{$bookingpress_current_date}' OR bookingpress_appointment_date IS NULL ) AND ( bookingpress_appointment_status IS NULL OR bookingpress_appointment_status = 1 OR bookingpress_appointment_status = 2 )";

						$bookingpress_total_appointments = $wpdb->get_var("SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} {$bookingpress_search_query} {$bookingpress_search_query_where} ORDER BY bookingpress_appointment_date $ordby"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
						
						if($bookingpress_total_appointments == 0){
							
							$bookingpress_assigned_service_details = $wpdb->get_results($wpdb->prepare( "SELECT * FROM ".$tbl_bookingpress_staffmembers_services." bpss LEFT JOIN ".$tbl_bookingpress_staffmembers." bpsf ON bpss.bookingpress_staffmember_id=bpsf.bookingpress_staffmember_id WHERE bpss.bookingpress_service_id = %d AND bpsf.bookingpress_staffmember_status = %d " . $where_clause, $bookingpress_selected_service_id, 1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm
							
							if(!empty($bookingpress_assigned_service_details)){
								$staff_member_ids = array();
								foreach($bookingpress_assigned_service_details as $k2 => $v2){
									$bookingpress_staffmember_id =  $v2['bookingpress_staffmember_id'];
									$total_booked_appointment = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( bookingpress_appointment_booking_id ) as total_booked_appointment FROM ".$tbl_bookingpress_appointment_bookings." WHERE bookingpress_appointment_date LIKE '{$bookingpress_current_date}' AND bookingpress_staff_member_id = %d", $bookingpress_staffmember_id ) ); //phpcs:ignore
									$staff_member_ids[ $bookingpress_staffmember_id ] = $total_booked_appointment;
								}
								
								$filter_appointment_staffmember = array_keys( $staff_member_ids, $minmax( $staff_member_ids ) );

								if( count( $filter_appointment_staffmember ) > 0 ){
									$bookingpress_staffmember_id = array_rand( $staff_member_ids );
								} else {
									$bookingpress_staffmember_id = $filter_appointment_staffmember;
								}
							}
						} else {
							// FETCH ALL STAFF MEMBER'S COUNT FOR BOOKED APPOINTMENT NOT ONLY BOOKED ONES
							$bookingpress_is_staffmember_assigned = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(bpss.bookingpress_staffmember_service_id) as total FROM ".$tbl_bookingpress_staffmembers_services." bpss LEFT JOIN ". $tbl_bookingpress_staffmembers ." bpsf ON bpss.bookingpress_staffmember_id=bpsf.bookingpress_staffmember_id WHERE bpss.bookingpress_service_id = %d AND bpsf.bookingpress_staffmember_status = %d" . $where_clause, $bookingpress_selected_service_id, 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm	
							if($bookingpress_is_staffmember_assigned['total'] != 0 && $bookingpress_is_staffmember_assigned['total'] > 1){
								$bookingpress_assigned_service_details = $wpdb->get_results($wpdb->prepare( "SELECT bpsfs.* FROM ".$tbl_bookingpress_staffmembers_services." bpsfs LEFT JOIN ".$tbl_bookingpress_staffmembers." bpsf ON bpsfs.bookingpress_staffmember_id=bpsf.bookingpress_staffmember_id WHERE bpsfs.bookingpress_service_id = %d AND bpsf.bookingpress_staffmember_status = %d " . $where_clause, $bookingpress_selected_service_id, 1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm
								if(!empty($bookingpress_assigned_service_details))
								{
									$bookingpress_staff_counter = array();
									foreach($bookingpress_assigned_service_details as $k2 => $v2){
										$current_staffmember_id = $v2["bookingpress_staffmember_id"];
										$bookingpress_least_assigned_staff_details = $wpdb->get_row( $wpdb->prepare( "SELECT count(bpa.bookingpress_appointment_booking_id) as total_booked_appointment FROM {$tbl_bookingpress_appointment_bookings} as bpa WHERE bpa.bookingpress_staff_member_id = %d AND ( bpa.bookingpress_appointment_date LIKE %s OR bpa.bookingpress_appointment_date IS NULL ) AND ( bpa.bookingpress_appointment_status IS NULL OR bpa.bookingpress_appointment_status = %d OR bpa.bookingpress_appointment_status = %d ) ", $current_staffmember_id, $bookingpress_current_date, 1, 2 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers & $tbl_bookingpress_staffmembers are table name.
										$bookingpress_staff_counter[$current_staffmember_id] = $bookingpress_least_assigned_staff_details['total_booked_appointment'];
									}
									if(is_array($bookingpress_staff_counter) && count($bookingpress_staff_counter)> 0 ) 
									{
										$min = $minmax($bookingpress_staff_counter);
										$index = array_search($min, $bookingpress_staff_counter);
										$bookingpress_staffmember_id =  $index;
									}
								}
							} else if( $bookingpress_is_staffmember_assigned['total'] == 1 ) {
								$bookingpress_is_staffmember_assigned = $wpdb->get_row($wpdb->prepare( "SELECT bpss.bookingpress_staffmember_id FROM ".$tbl_bookingpress_staffmembers_services." bpss LEFT JOIN ". $tbl_bookingpress_staffmembers ." bpsf ON bpss.bookingpress_staffmember_id = bpsf.bookingpress_staffmember_id WHERE bookingpress_service_id = %d AND bpsf.bookingpress_staffmember_status = %d" . $where_clause, $bookingpress_selected_service_id, 1 ), ARRAY_A); // phpcs:ignore
								$bookingpress_staffmember_id =  $bookingpress_is_staffmember_assigned['bookingpress_staffmember_id'];
							}
						}
					} else {
						$bookingpress_least_assigned_staff_details = $wpdb->get_row( $wpdb->prepare( "SELECT SUM( ( CASE WHEN bpa.bookingpress_appointment_booking_id IS NOT NULL THEN 1 ELSE 0 END ) ) as total_booked_appointment, bps.bookingpress_staffmember_id as bookingpress_staff_member_id, bpa.bookingpress_appointment_status FROM {$tbl_bookingpress_appointment_bookings} bpa RIGHT JOIN {$tbl_bookingpress_staffmembers} bps ON bpa.bookingpress_staff_member_id = bps.bookingpress_staffmember_id RIGHT JOIN {$tbl_bookingpress_staffmembers_services} bpss ON bps.bookingpress_staffmember_id = bpss.bookingpress_staffmember_id WHERE ( bpa.bookingpress_appointment_date LIKE %s OR bpa.bookingpress_appointment_date IS NULL ) AND ( bpa.bookingpress_appointment_status IS NULL OR bpa.bookingpress_appointment_status = %d OR bpa.bookingpress_appointment_status = %d ) AND bps.bookingpress_staffmember_id != 0 AND bps.bookingpress_staffmember_status = %d GROUP BY bpa.bookingpress_staff_member_id ORDER BY total_booked_appointment $ordby", $bookingpress_current_date, 1, 2, 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers & $tbl_bookingpress_staffmembers are table name.


						if(!empty($bookingpress_least_assigned_staff_details)){
							$bookingpress_staffmember_id = $bookingpress_least_assigned_staff_details['bookingpress_staff_member_id'];
						} else {
							/** Fetch all staff members with assigned services */
							$bookingpress_all_staffmembers = $wpdb->get_results( $wpdb->prepare( "SELECT bsf.bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers} bsf RIGHT JOIN {$tbl_bookingpress_staffmembers_services} bpss ON bsf.bookingpress_staffmember_id=bpss.bookingpress_staffmember_id WHERE bsf.bookingpress_staffmember_status = %d {$where_clause} GROUP BY bsf.bookingpress_staffmember_id", 1)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers & $tbl_bookingpress_staffmembers_services  is a table name. false alarm

							$all_staff_ids = array();
							foreach( $bookingpress_all_staffmembers as $bpa_staffmembers ){
								$all_staff_ids[] = $bpa_staffmembers->bookingpress_staffmember_id;
							}
							
							if( !empty( $all_staff_ids ) ){
								if( count( $all_staff_ids ) == 1 ){
									$bookingpress_staffmember_id = $all_staff_ids[0];
								} else {
									$min = $minmax($all_staff_ids);
									$index = array_search($min, $all_staff_ids);
									$bookingpress_staffmember_id =  $all_staff_ids[ $index ];
								}
							}
						}
					}
				}else if( "least_assigned_by_week" == $bookingpress_any_staff_selected_rule || "most_assigned_by_week" == $bookingpress_any_staff_selected_rule ){

					$ordby = "ASC";
					$minmax = "min";
					if( "most_assigned_by_week" == $bookingpress_any_staff_selected_rule ){
						$ordby = "DESC";
						$minmax = "max";
					}

					if(!empty($bookingpress_selected_service_id)){

						$bookingpress_search_query       = 'WHERE 1=1 ';
						$bookingpress_search_query_where .= "AND (bookingpress_service_id = {$bookingpress_selected_service_id} ) ";
						$bookingpress_search_query_where .= $wpdb->prepare( "AND ( bookingpress_appointment_date >= %s AND bookingpress_appointment_date <= %s ) AND ( bookingpress_appointment_status IS NULL OR bookingpress_appointment_status = 1 OR bookingpress_appointment_status = 2 )", $bookingpress_week_start_date, $bookingpress_week_end_date );

						$bookingpress_total_appointments = $wpdb->get_var("SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} {$bookingpress_search_query} {$bookingpress_search_query_where} ORDER BY bookingpress_appointment_date $ordby"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
						
						if($bookingpress_total_appointments == 0){
							$bookingpress_assigned_service_details = $wpdb->get_results($wpdb->prepare( "SELECT * FROM ".$tbl_bookingpress_staffmembers_services." bpss LEFT JOIN ".$tbl_bookingpress_staffmembers." bpsf ON bpss.bookingpress_staffmember_id = bpsf.bookingpress_staffmember_id WHERE bpss.bookingpress_service_id = %d AND bpsf.bookingpress_staffmember_status = %d" . $where_clause, $bookingpress_selected_service_id, 1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm
							if(!empty($bookingpress_assigned_service_details)){
								foreach($bookingpress_assigned_service_details as $k2 => $v2){
									$bookingpress_staffmember_id =  $v2['bookingpress_staffmember_id'];
								}
							}
						} else {
							// FETCH ALL STAFF MEMBER'S COUNT FOR BOOKED APPOINTMENT NOT ONLY BOOKED ONES
							$bookingpress_is_staffmember_assigned = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(bookingpress_staffmember_service_id) as total FROM ".$tbl_bookingpress_staffmembers_services." bpss LEFT JOIN ".$tbl_bookingpress_staffmembers." bpsf ON bpss.bookingpress_staffmember_id=bpsf.bookingpress_staffmember_id WHERE bpss.bookingpress_service_id = %d AND bpsf.bookingpress_staffmember_status = %d" . $where_clause, $bookingpress_selected_service_id, 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm

							if($bookingpress_is_staffmember_assigned['total'] != 0 && $bookingpress_is_staffmember_assigned['total'] > 1){
								$bookingpress_assigned_service_details = $wpdb->get_results($wpdb->prepare( "SELECT * FROM ".$tbl_bookingpress_staffmembers_services." bpss LEFT JOIN ". $tbl_bookingpress_staffmembers ." bpsf ON bpss.bookingpress_staffmember_id=bpsf.bookingpress_staffmember_id WHERE bpss.bookingpress_service_id = %d AND bpsf.bookingpress_staffmember_status = %d", $bookingpress_selected_service_id, 1 ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm

								if(!empty($bookingpress_assigned_service_details)){
									$bookingpress_staff_counter = array();
									foreach($bookingpress_assigned_service_details as $k2 => $v2){
										$current_staffmember_id = $v2["bookingpress_staffmember_id"];
										$bookingpress_least_assigned_staff_details = $wpdb->get_row( $wpdb->prepare( "SELECT count(bpa.bookingpress_appointment_booking_id) as total_booked_appointment FROM {$tbl_bookingpress_appointment_bookings} as bpa WHERE bpa.bookingpress_staff_member_id = %d AND ( bpa.bookingpress_appointment_date >= %s AND bpa.bookingpress_appointment_date <= %s ) AND ( bpa.bookingpress_appointment_status IS NULL OR bpa.bookingpress_appointment_status = %d OR bpa.bookingpress_appointment_status = %d )  ", $current_staffmember_id, $bookingpress_week_start_date, $bookingpress_week_start_date, 1, 2 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers & $tbl_bookingpress_staffmembers are table name.
										$bookingpress_staff_counter[$current_staffmember_id] = $bookingpress_least_assigned_staff_details['total_booked_appointment'];
									}

									if(is_array($bookingpress_staff_counter) && count($bookingpress_staff_counter)> 0 ) {
										$min = $minmax($bookingpress_staff_counter);
										$index = array_search($min, $bookingpress_staff_counter);
										$bookingpress_staffmember_id =  $index;
									}
								}
							} else if( $bookingpress_is_staffmember_assigned['total'] == 1 ) {
								$bookingpress_is_staffmember_assigned = $wpdb->get_row($wpdb->prepare( "SELECT bpss.bookingpress_staffmember_id FROM ".$tbl_bookingpress_staffmembers_services." bpss LEFT JOIN ". $tbl_bookingpress_staffmembers ." bpsf ON bpss.bookingpress_staffmember_id = bpsf.bookingpress_staffmember_id WHERE bpss.bookingpress_service_id = %d AND bpsf.bookingpress_staffmember_status = %d" . $where_clause, $bookingpress_selected_service_id, 1 ), ARRAY_A); // phpcs:ignore
								$bookingpress_staffmember_id =  $bookingpress_is_staffmember_assigned['bookingpress_staffmember_id'];
							}
						}
					} else {
						
						$bookingpress_least_weekly_assigned_staff_details = $wpdb->get_row( $wpdb->prepare( "SELECT SUM( ( CASE WHEN bpa.bookingpress_appointment_booking_id IS NOT NULL THEN 1 ELSE 0 END ) ) as total_booked_appointment, bps.bookingpress_staffmember_id as bookingpress_staff_member_id, bpa.bookingpress_appointment_status FROM {$tbl_bookingpress_appointment_bookings} bpa RIGHT JOIN {$tbl_bookingpress_staffmembers} bps ON bpa.bookingpress_staff_member_id = bps.bookingpress_staffmember_id WHERE ( ( bpa.bookingpress_appointment_date >= %s AND bpa.bookingpress_appointment_date <= %s ) OR bpa.bookingpress_appointment_date IS NULL ) AND ( bpa.bookingpress_appointment_status IS NULL OR bpa.bookingpress_appointment_status = %d OR bpa.bookingpress_appointment_status = %d ) AND bps.bookingpress_staffmember_id != 0 AND bps.bookingpress_staffmember_status = %d GROUP BY bpa.bookingpress_staff_member_id ORDER BY total_booked_appointment ASC", $bookingpress_week_start_date, $bookingpress_week_end_date, 1, 2, 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers & $tbl_bookingpress_appointment_bookings are table name.
					}
					if(!empty($bookingpress_least_weekly_assigned_staff_details)){
						$bookingpress_staffmember_id = $bookingpress_least_weekly_assigned_staff_details['bookingpress_staff_member_id'];
					}
				} else if($bookingpress_any_staff_selected_rule == "most_expensive"){
					if(!empty($bookingpress_selected_service_id)){
						$bookingpress_assigned_staffmembers_details = $wpdb->get_row( $wpdb->prepare( "SELECT bpss.bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers_services} bpss LEFT JOIN ". $tbl_bookingpress_staffmembers ." bpsf ON bpss.bookingpress_staffmember_id = bpsf.bookingpress_staffmember_id WHERE bpss.bookingpress_service_id = %d AND bpsf.bookingpress_staffmember_status = %d "  . $where_clause . " ORDER BY bookingpress_service_price DESC", $bookingpress_selected_service_id, 1 ), ARRAY_A ); // phpcs:ignore
					}else{
						$bookingpress_assigned_staffmembers_details = $wpdb->get_row( $wpdb->prepare( "SELECT bpss.bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers_services} bpss LEFT JOIN ". $tbl_bookingpress_staffmembers ." bpsf ON bpss.bookingpress_staffmember_id = bpsf.bookingpress_staffmember_id WHERE bpsf.bookingpress_staffmember_status = %d ORDER BY bookingpress_service_price DESC", 1), ARRAY_A); // phpcs:ignore
					}
					if(!empty($bookingpress_assigned_staffmembers_details)){
						$bookingpress_staffmember_id = $bookingpress_assigned_staffmembers_details['bookingpress_staffmember_id'];
					}
				}else if($bookingpress_any_staff_selected_rule == "least_expensive"){
					if(!empty($bookingpress_selected_service_id)){
						$bookingpress_assigned_staffmembers_details = $wpdb->get_row($wpdb->prepare("SELECT bpss.bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers_services} bpss LEFT JOIN ". $tbl_bookingpress_staffmembers ." bpsf ON bpss.bookingpress_staffmember_id = bpsf.bookingpress_staffmember_id WHERE bpss.bookingpress_service_id = %d AND bpsf.bookingpress_staffmember_status = %d " . $where_clause . " ORDER BY bookingpress_service_price ASC", $bookingpress_selected_service_id, 1), ARRAY_A); // phpcs:ignore
					}else{
						$bookingpress_assigned_staffmembers_details = $wpdb->get_row($wpdb->prepare("SELECT bpss.bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers_services} bpss LEFT JOIN ". $tbl_bookingpress_staffmembers ." bpsf ON bpss.bookingpress_staffmember_id = bpsf.bookingpress_staffmember_id WHERE bpsf.bookingpress_staffmember_status = %d {$where_clause} ORDER BY bookingpress_service_price ASC", 1), ARRAY_A); // phpcs:ignore
					}
					if(!empty($bookingpress_assigned_staffmembers_details)){
						$bookingpress_staffmember_id = $bookingpress_assigned_staffmembers_details['bookingpress_staffmember_id'];
					}
				}
			
				$response['variant'] = 'success';
				$response['title'] = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Data retrieved successfully', 'bookingpress-appointment-booking' );
				$response['staffmember_id'] = $bookingpress_staffmember_id;
			}
			echo wp_json_encode($response);
			exit;
		}

		function bookingpress_change_on_summary_steps_filter_func($bookingpress_change_on_summary_steps_filter){

			$bookingpress_change_on_summary_steps_filter .= '
				
				vm.bookingpress_activate_payment_gateway_total_counter = vm.bookingpress_activate_payment_gateway_counter;
				if( vm.paypal_payment == "paypal" ){
					let total_counter_payment_gateway = vm.bookingpress_activate_payment_gateway_total_counter + 1;
					vm.bookingpress_activate_payment_gateway_total_counter = total_counter_payment_gateway;
				}
			';
			return $bookingpress_change_on_summary_steps_filter;
		}
		
				
		/**
		 * Function for execute code when next step trigger
		 *
		 * @param  mixed $bookingpress_dynamic_next_page_request_filter
		 * @return void
		 */
		function bookingpress_dynamic_next_page_request_filter_func($bookingpress_dynamic_next_page_request_filter){
			global $BookingPress;
			$bookingpress_selected_staff_from_url = !empty($_GET['sm_id']) ? intval($_GET['sm_id']) : 0;

			$bookingpress_dynamic_next_page_request_filter .= '

				if((vm.bookingpress_current_tab == "basic_details")){					
					if(vm.bookingpress_has_password_field == "1"){
						if(typeof vm.appointment_step_form_data.form_fields.customer_email != "undefined" && vm.appointment_step_form_data.form_fields.customer_email != ""){						
							vm.bpa_check_password_validation(vm.appointment_step_form_data.form_fields.customer_email);
						}						
						if(typeof vm.appointment_step_form_data.form_fields.customer_username != "undefined" && vm.appointment_step_form_data.form_fields.customer_username != ""){							
							vm.bpa_check_password_validation(vm.appointment_step_form_data.form_fields.customer_username);
						}
					}
					if( (vm.bookingpress_is_extra_enable == "1" ) ){
						
						let bpa_selected_service_extra_count = 0;
						for(var extra_key in vm.appointment_step_form_data.bookingpress_selected_extra_details){
								
							if(vm.appointment_step_form_data.bookingpress_selected_extra_details[extra_key].bookingpress_is_selected == true){

								let service_extra_price = vm.appointment_step_form_data.bookingpress_selected_extra_details[extra_key].bookingpress_extra_price;
								let service_extra_qty = vm.appointment_step_form_data.bookingpress_selected_extra_details[extra_key].bookingpress_selected_qty;								
								if( service_extra_qty != ""){
									if(typeof vm.appointment_step_form_data.bookingpress_selected_extra_details[extra_key]["bookingpress_extra_price_org"] != "undefined"){
										service_extra_price = vm.appointment_step_form_data.bookingpress_selected_extra_details[extra_key]["bookingpress_extra_price_org"];
									}									
									let bpa_final_extra_price = service_extra_price * service_extra_qty;									
									vm.appointment_step_form_data.bookingpress_selected_extra_details[extra_key]["bookingpress_extra_price_org"] = service_extra_price;
									vm.appointment_step_form_data.bookingpress_selected_extra_details[extra_key].bookingpress_extra_price = vm.bookingpress_price_with_currency_symbol(bpa_final_extra_price);
								}	
								bpa_selected_service_extra_count++;
							}
						}
						vm.appointment_step_form_data.bookingpress_selected_extra_service_count = bpa_selected_service_extra_count;
					}
				}
			';
			/** Display loader on service page when any staff selected and current tab is service */			
			$bookingpress_dynamic_next_page_request_filter .= '
			if( ( previous_tab == "staffmembers" || ( typeof vm.is_staff_first_step != "undefined" && vm.is_staff_first_step == 1 )) && "service" == current_tab && "true" == vm.appointment_step_form_data.select_any_staffmember && 0 == vm.appointment_step_form_data.selected_staff_member_id ){
				vm.isLoadServiceLoader = "1";
				vm.bookingpress_select_staffmember("any_staff", 1 );
			}
			';

			$bookingpress_default_select_all_category = $BookingPress->bookingpress_get_customize_settings('default_select_all_category','booking_form');
			if(!empty($bookingpress_default_select_all_category ) && $bookingpress_default_select_all_category == 'true') {
				$bookingpress_form_sequence = $BookingPress->bookingpress_get_customize_settings('bookingpress_form_sequance', 'booking_form');
				if(!empty($bookingpress_form_sequence)){					
					$bookingpress_form_sequence = json_decode($bookingpress_form_sequence,true);
					if(isset($bookingpress_form_sequence[0]) && $bookingpress_form_sequence[0] != 'service_selection'){
						$bookingpress_dynamic_next_page_request_filter .= '
						if("service" == current_tab){
							
							if(typeof vm.appointment_step_form_data.selected_service  != "undefined" && vm.appointment_step_form_data.selected_service == ""){
								vm.appointment_step_form_data.selected_category = 0;
								vm.bpa_select_category( "" );
							}					
						}
						';
					}
				}
			}

			$bookingpress_dynamic_next_page_request_filter .= '
				if((vm.bookingpress_is_extra_enable == "0" || vm.bookingpress_service_extras.length == 0 || vm.appointment_step_form_data.is_extra_service_exists == "0") && (vm.is_bring_anyone_with_you_activated == "0" || vm.bookingpress_bring_anyone_with_you_details.length == "0" || parseInt(vm.appointment_step_form_data.service_max_capacity) == "") && (vm.is_staffmember_activated == "0" || vm.appointment_step_form_data.is_staff_exists == "0" || vm.appointment_step_form_data.form_sequence == "staff_selection")){
					vm.bookingpress_open_extras_drawer = "false";
				}
				
				var bpa_selected_staff_from_url = "'.$bookingpress_selected_staff_from_url.'";
				
				for(var extra_key in vm.appointment_step_form_data.bookingpress_selected_extra_details){
					if(vm.appointment_step_form_data.bookingpress_selected_extra_details[extra_key].bookingpress_is_selected == "true"){
						vm.appointment_step_form_data.bookingpress_selected_extra_details[extra_key].bookingpress_is_selected = true;
					}
				}

				var bpa_selected_staff_id = vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id;

				if( "summary" == vm.bookingpress_current_tab && "summary" == next_tab && bookingpress_is_validate == 0 ){
					/* vm.bookingpress_calculate_service_addons_price(vm.appointment_step_form_data.selected_service); */
					vm.bookingpress_get_final_step_amount();
					/* vm.bookingpress_recalculate_payable_amount(); */
				}
			';
			return $bookingpress_dynamic_next_page_request_filter;
		}
		
		/**
		 * Get customer edit profile data callback function
		 *
		 * @return void
		 */
		function bookingpress_get_edit_profile_data_func() {

			global $BookingPress,$wpdb,$tbl_bookingpress_customers_meta,$tbl_bookingpress_customers, $tbl_bookingpress_form_fields;
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
			$bpa_login_customer_id             = get_current_user_id();
			$bookingpress_get_customer_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id =%d", $bpa_login_customer_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
			$bookingpress_current_user_id      = ! empty( $bookingpress_get_customer_details['bookingpress_customer_id'] ) ? $bookingpress_get_customer_details['bookingpress_customer_id'] : 0;
			$edit_profile_field_data           = ! empty( $_REQUEST['edit_profile_field_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['edit_profile_field_data'] ) : array();  //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST['edit_profile_field_data'] has already been sanitized.
			$bookingpress_form_fields          = $bookingpress_field_data = array();
			if ( ! empty( $bookingpress_current_user_id ) && ! empty( $edit_profile_field_data ) ) {
				foreach ( $edit_profile_field_data as $key => $value ) {
					if ( $key == 'customer_firstname' ) {
						$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_firstname'] ) ? stripslashes_deep($bookingpress_get_customer_details['bookingpress_user_firstname']) : '';
					} elseif ( $key == 'customer_lastname' ) {
						$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_lastname'] ) ? stripslashes_deep($bookingpress_get_customer_details['bookingpress_user_lastname']) : '';
					} elseif ( $key == 'customer_phone' ) {
						$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_phone'] ) ? $bookingpress_get_customer_details['bookingpress_user_phone'] : '';
					} elseif ( $key == 'customer_email' ) {
						$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_email'] ) ? $bookingpress_get_customer_details['bookingpress_user_email'] : '';
					} elseif ( $key == 'customer_username' ) {
						$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_name'] ) ? $bookingpress_get_customer_details['bookingpress_user_name'] : '';
					} elseif ( $key == 'customer_phone_country' ) {
						$bookingpress_field_value = ! empty( $bookingpress_get_customer_details['bookingpress_user_country_phone'] ) ? $bookingpress_get_customer_details['bookingpress_user_country_phone'] : '';
					} else {
						$bookingpress_field_value = $BookingPress->get_bookingpress_customersmeta( $bookingpress_current_user_id, $key );
					}

					$bookingpress_form_fields[ $key ] = stripslashes_deep($bookingpress_field_value);

					$bpa_get_customer_field_type = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_field_type FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_field_meta_key = %s AND bookingpress_is_customer_field = %d", $key, 1 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
					if( 'checkbox' == $bpa_get_customer_field_type ){						
						$field_values = json_decode( $bookingpress_field_value, true );					
						if(empty($field_values)){
							$field_values = array();
						}
						$bookingpress_form_fields[ $key ] = $field_values;
					}
				}
			} else {
				$bookingpress_current_user_obj = wp_get_current_user();
				$bookingpress_form_fields = $edit_profile_field_data;
				$bookingpress_customer_email = ! empty($bookingpress_current_user_obj->data->user_email) ? $bookingpress_current_user_obj->data->user_email : '';
				if(isset($edit_profile_field_data['customer_firstname'])) {
					$bookingpress_form_fields['customer_firstname'] = stripslashes_deep(get_user_meta($bpa_login_customer_id, 'first_name', true));
				}
				if(isset($edit_profile_field_data['customer_lastname'])) {
					$bookingpress_form_fields['customer_lastname'] = stripslashes_deep(get_user_meta($bpa_login_customer_id, 'last_name', true));
				}
				if(isset($edit_profile_field_data['customer_email'])) {
					$bookingpress_form_fields['customer_email'] = stripslashes_deep( $bookingpress_customer_email );
				}
			}
			$bookingpress_field_data['edit_profile_field_data'] = $bookingpress_form_fields;
			echo wp_json_encode( $bookingpress_field_data );
			exit;
		}
		
		/**
		 * Update profile callback function
		 *
		 * @return void
		 */
		function bookingpress_update_profile_func() {
			global $BookingPress,$wpdb,$tbl_bookingpress_customers,$bookingpress_customers;

			$bookingpress_update_profile_success_msg = $BookingPress->bookingpress_get_customize_settings('update_profile_success_msg', 'booking_my_booking');

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
			$response['variant']               = 'error';
			$response['title']                 = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']                   = esc_html__( 'Something went wrong..', 'bookingpress-appointment-booking' );
			$bpa_login_customer_id             = get_current_user_id();
			$bookingpress_get_customer_details = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_customer_id FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id =%d", $bpa_login_customer_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm

			$bookingpress_current_user_id = ! empty( $bookingpress_get_customer_details['bookingpress_customer_id'] ) ? $bookingpress_get_customer_details['bookingpress_customer_id'] : 0;
			$edit_profile_field_data      = ! empty( $_REQUEST['edit_profile_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['edit_profile_data'] ) : array();  //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST['edit_profile_data'] has already been sanitized.
			if ( ! empty( $bookingpress_current_user_id ) && ! empty( $edit_profile_field_data ) ) {
				$customer_update_details = array();
				foreach ( $edit_profile_field_data as $key => $value ) {
					if ( $key != 'customer_email' ) {
						if ( $key == 'customer_firstname' ) {
							$customer_update_details['bookingpress_user_firstname'] = $value;
						} elseif ( $key == 'customer_lastname' ) {
							$customer_update_details['bookingpress_user_lastname'] = $value;
						} elseif ( $key == 'customer_phone' ) {
							$customer_update_details['bookingpress_user_phone'] = $value;
						} elseif ( $key == 'customer_phone_country' ) {
							$customer_update_details['bookingpress_user_country_phone'] = $value;
						} else {
							$BookingPress->update_bookingpress_customersmeta( $bookingpress_current_user_id, $key, $value );
						}
					}
				}
				$customer_update_where_condition = array(
					'bookingpress_customer_id' => $bookingpress_current_user_id,
				);
				if ( ! empty( $customer_update_details ) ) {
					$wpdb->update( $tbl_bookingpress_customers, $customer_update_details, $customer_update_where_condition );
				}
				do_action('bookingpress_front_after_edit_customer',$bookingpress_current_user_id);
				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = stripslashes_deep($bookingpress_update_profile_success_msg);
			} else {
				$bookingpress_customer_details = array();
				if(!empty($edit_profile_field_data)) {
					$bookingpress_firstname = !empty($edit_profile_field_data['customer_firstname']) ? $edit_profile_field_data['customer_firstname'] : '';
					$bookingpress_email = !empty($edit_profile_field_data['customer_email']) ? $edit_profile_field_data['customer_email'] : '';					
					$bookingpress_customer_details = array(
						'bookingpress_customer_name'      => !empty($bookingpress_firstname) ? $bookingpress_firstname : $bookingpress_email,
						'bookingpress_customer_phone'     => !empty($edit_profile_field_data['customer_phone']) ? $edit_profile_field_data['customer_phone'] : '',
						'bookingpress_customer_firstname' => $bookingpress_firstname,
						'bookingpress_customer_lastname'  => !empty($edit_profile_field_data['customer_lastname']) ? $edit_profile_field_data['customer_lastname'] : '',
						'bookingpress_customer_country'   => !empty($edit_profile_field_data['customer_phone_country']) ? strtoupper($edit_profile_field_data['customer_phone_country']) : '',
						'bookingpress_customer_email'     => $bookingpress_email,
						'bookingpress_customer_note'      => '',
					);
					$bookingpress_user_details = $bookingpress_customers->bookingpress_create_customer($bookingpress_customer_details, $bpa_login_customer_id,2,1);
					$bookingpress_current_user_id  = !empty($bookingpress_user_details['bookingpress_customer_id']) ? $bookingpress_user_details['bookingpress_customer_id'] : 0;
					if(!empty($bookingpress_current_user_id)) {
						foreach ( $edit_profile_field_data as $key => $value ) {
							if ( $key != 'customer_email' && $key != 'customer_firstname'  && $key != 'customer_lastname' && $key != 'customer_phone'  && $key != 'bookingpress_user_country_phone' ) {							
								$BookingPress->update_bookingpress_customersmeta( $bookingpress_current_user_id, $key, $value );							
							}
						}
					}
				}
				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = stripslashes_deep($bookingpress_update_profile_success_msg);

			}
			echo wp_json_encode( $response );
			exit;
		}
		
		/**
		 * Modify service timings at frontend
		 *
		 * @param  mixed $return_data
		 * @param  mixed $selected_service_id
		 * @param  mixed $selected_date
		 * @param  mixed $posted_data
		 * @param  mixed $max_capacity
		 * @return void
		 */
		function bookingpress_modify_service_return_timings_filter_func($return_data, $selected_service_id, $selected_date, $posted_data, $max_capacity){
			global $BookingPress, $bookingpress_pro_services, $bookingpress_pro_staff_members, $wpdb, $tbl_bookingpress_payment_logs, $bookingpress_services, $tbl_bookingpress_appointment_bookings;
			
			$bookingpress_timezone = !empty($posted_data['bookingpress_timezone']) ? $posted_data['bookingpress_timezone'] : '';
			
			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

			if(!empty($bookingpress_timezone) && !empty($bookingpress_timeslot_display_in_client_timezone) && ($bookingpress_timeslot_display_in_client_timezone == 'true')){
				$bookingpress_current_time = date('H:i');
				$bpa_current_datetime = date( 'Y-m-d H:i:s');
			}else{
				$bookingpress_current_time = date( 'H:i',current_time('timestamp'));
				$bpa_current_datetime = date( 'Y-m-d H:i:s',current_time('timestamp'));
			}

			$bookingpress_selected_staffmember_id = !empty( $posted_data['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ? $posted_data['bookingpress_selected_staffmember']['selected_staff_member_id'] : 0;
			
			$bookingpress_morning_time = $return_data['morning_time'];
			$bookingpress_afternoon_time = $return_data['afternoon_time'];
			$bookingpress_evening_time = $return_data['evening_time'];
			$bookingpress_night_time = $return_data['night_time'];

			$bookingpress_service_max_capacity = $max_capacity;//$bookingpress_pro_services->bookingpress_get_service_max_capacity($selected_service_id);

			$bookingpress_is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();

			if( is_array($bookingpress_morning_time) && !empty($bookingpress_morning_time) ){
				foreach($bookingpress_morning_time as $k => $v){
					$bookingpress_start_time = $v['start_time'];
					$bpa_start_datetime = $selected_date. ' ' . $v['start_time'] . ':00';
					$bookingpress_end_time = $v['end_time'];
					
					if( $bookingpress_is_staffmember_module_activated && 0 < $bookingpress_selected_staffmember_id ){
						$bookingpress_total_booked_appointment = 0;
						$bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');
						if( $bookingpress_shared_service_timeslot == 'true' ){
							$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);
						
							$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							
							if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment != $bookingpress_service_max_capacity){
								$v['is_booked'] = 0;
							}
						} else {
							if( 1 < $bookingpress_service_max_capacity ){
								$service_start_time = $v['start_time'].':00';
								$service_end_time = $v['end_time'].':00';
								$is_appointment_booked  = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d AND bookingpress_staff_member_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id, $bookingpress_selected_staffmember_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
							} else {
								$is_appointment_booked = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_payment_log_id) as total FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_staff_member_id = %d AND bookingpress_service_id = %d AND ((bookingpress_appointment_start_time >= %s AND bookingpress_appointment_start_time < %s) OR (bookingpress_appointment_start_time < %s AND bookingpress_appointment_end_time > %s ) )", "%{$selected_date}%", $bookingpress_selected_staffmember_id, $selected_service_id, $bookingpress_start_time, $bookingpress_end_time, $bookingpress_end_time, $bookingpress_start_time ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is table name.
							}
								
							if( $is_appointment_booked > 0 ){
								$v['total_booked_appointment'] = $is_appointment_booked;
								$v['max_capacity'] = $bookingpress_service_max_capacity;
								if( 1 < $bookingpress_service_max_capacity && $is_appointment_booked < $bookingpress_service_max_capacity ){
									$v['is_booked'] = 0;
								} else {
									$v['is_booked'] = 1;
								}
							} else {
								
								if( 1 < $bookingpress_service_max_capacity && 1 == $v['is_booked'] ){
									$service_start_time = $v['start_time'].':00';
									$service_end_time = $v['end_time'].':00';
									$current_service_booked_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
									$v['total_booked_appointment'] = $current_service_booked_count;
									$v['max_capacity'] = $bookingpress_service_max_capacity;
									if( 1 > $current_service_booked_count || $current_service_booked_count < $bookingpress_service_max_capacity  ){
										$v['is_booked'] = 0;
									}
								} else {

									$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);		
									$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
									$v['max_capacity'] = $bookingpress_service_max_capacity;
									if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment < $bookingpress_service_max_capacity){
										$v['is_booked'] = 0;
									}
								}
							}
						}
					} else {

						if( 1 < $bookingpress_service_max_capacity && 1 == $v['is_booked'] ){
							$service_start_time = $v['start_time'].':00';
							$service_end_time = $v['end_time'].':00';

							$current_service_booked_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.

							$v['total_booked_appointment'] = $current_service_booked_count;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							if( 1 > $current_service_booked_count || $current_service_booked_count < $bookingpress_service_max_capacity  ){
								$v['is_booked'] = 0;
							}
						} else {
							$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);
							
							$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							
							if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment < $bookingpress_service_max_capacity){
								$v['is_booked'] = 0;
							}
						}
					}

					$bookingpress_morning_time[$k] = $v;
				}
				$return_data['morning_time'] = $bookingpress_morning_time;
			}


			if( is_array($bookingpress_afternoon_time) && !empty($bookingpress_afternoon_time) ){
				foreach($bookingpress_afternoon_time as $k => $v){
					$bookingpress_start_time = $v['start_time'];
					$bpa_start_datetime = $selected_date. ' ' . $v['start_time'] . ':00';
					$bookingpress_end_time = $v['end_time'];

					if( $bookingpress_is_staffmember_module_activated && 0 < $bookingpress_selected_staffmember_id ){
						$bookingpress_total_booked_appointment = 0;
						$bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');
						if( $bookingpress_shared_service_timeslot == 'true' ){
							$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);
						
							$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							
							if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment != $bookingpress_service_max_capacity){
								$v['is_booked'] = 0;
							}
						} else {
							if( 1 < $bookingpress_service_max_capacity ){
								$service_start_time = $v['start_time'].':00';
								$service_end_time = $v['end_time'].':00';
								$is_appointment_booked  = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d AND bookingpress_staff_member_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id, $bookingpress_selected_staffmember_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
							} else {
								$is_appointment_booked = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_payment_log_id) as total FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_staff_member_id = %d AND bookingpress_service_id = %d AND ((bookingpress_appointment_start_time >= %s AND bookingpress_appointment_start_time < %s) OR (bookingpress_appointment_start_time < %s AND bookingpress_appointment_end_time > %s ) )", "%{$selected_date}%", $bookingpress_selected_staffmember_id, $selected_service_id, $bookingpress_start_time, $bookingpress_end_time, $bookingpress_end_time, $bookingpress_start_time ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is table name.
							}
							
							if( $is_appointment_booked > 0 ){
								$v['total_booked_appointment'] = $is_appointment_booked;
								$v['max_capacity'] = $bookingpress_service_max_capacity;
								if( 1 < $bookingpress_service_max_capacity && $is_appointment_booked < $bookingpress_service_max_capacity ){
									$v['is_booked'] = 0;
								} else {
									$v['is_booked'] = 1;
								}
							} else {

								if( 1 < $bookingpress_service_max_capacity && 1 == $v['is_booked'] ){
									$service_start_time = $v['start_time'].':00';
									$service_end_time = $v['end_time'].':00';
		
									$current_service_booked_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
		
									$v['total_booked_appointment'] = $current_service_booked_count;
									$v['max_capacity'] = $bookingpress_service_max_capacity;
									if( 1 > $current_service_booked_count || $current_service_booked_count < $bookingpress_service_max_capacity  ){
										$v['is_booked'] = 0;
									}
								} else {
									$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);		
									$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
									$v['max_capacity'] = $bookingpress_service_max_capacity;
									
									if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment != $bookingpress_service_max_capacity){
										$v['is_booked'] = 0;
									}
								}
							}
						}
					} else {

						if( 1 < $bookingpress_service_max_capacity && 1 == $v['is_booked'] ){
							$service_start_time = $v['start_time'].':00';
							$service_end_time = $v['end_time'].':00';

							$current_service_booked_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
							$v['total_booked_appointment'] = $current_service_booked_count;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							if( 1 > $current_service_booked_count || $current_service_booked_count < $bookingpress_service_max_capacity ){
								$v['is_booked'] = 0;
							}
						} else {

							$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);
							
							$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							
							if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment != $bookingpress_service_max_capacity){
								$v['is_booked'] = 0;
							}
						}
					}

					$bookingpress_afternoon_time[$k] = $v;
				}
				
				$return_data['afternoon_time'] = $bookingpress_afternoon_time;
			}


			if( is_array($bookingpress_evening_time) && !empty($bookingpress_evening_time) ){
				foreach($bookingpress_evening_time as $k => $v){
					$bookingpress_start_time = $v['start_time'];
					$bookingpress_end_time = $v['end_time'];

					if( $bookingpress_is_staffmember_module_activated && 0 < $bookingpress_selected_staffmember_id ){
						$bookingpress_total_booked_appointment = 0;
						$bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');
						if( $bookingpress_shared_service_timeslot == 'true' ){
							$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);
						
							$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							
							if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment != $bookingpress_service_max_capacity){
								$v['is_booked'] = 0;
							}
						} else {

							if( 1 < $bookingpress_service_max_capacity ){
								$service_start_time = $v['start_time'].':00';
								$service_end_time = $v['end_time'].':00';
								$is_appointment_booked  = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d AND bookingpress_staff_member_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id, $bookingpress_selected_staffmember_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
							} else {
								$is_appointment_booked = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_payment_log_id) as total FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_staff_member_id = %d AND bookingpress_service_id = %d AND ((bookingpress_appointment_start_time >= %s AND bookingpress_appointment_start_time < %s) OR (bookingpress_appointment_start_time < %s AND bookingpress_appointment_end_time > %s ) )", "%{$selected_date}%", $bookingpress_selected_staffmember_id, $selected_service_id, $bookingpress_start_time, $bookingpress_end_time, $bookingpress_end_time, $bookingpress_start_time ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
							}

							if( $is_appointment_booked > 0 ){
								$v['total_booked_appointment'] = $is_appointment_booked;
								$v['max_capacity'] = $bookingpress_service_max_capacity;
								if( 1 < $bookingpress_service_max_capacity && $is_appointment_booked < $bookingpress_service_max_capacity ){
									$v['is_booked'] = 0;
								} else {
									$v['is_booked'] = 1;
								}
							} else {
								if( 1 < $bookingpress_service_max_capacity && 1 == $v['is_booked'] ){
									$service_start_time = $v['start_time'].':00';
									$service_end_time = $v['end_time'].':00';
		
									$current_service_booked_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
		
									$v['total_booked_appointment'] = $current_service_booked_count;
									$v['max_capacity'] = $bookingpress_service_max_capacity;
									if( 1 > $current_service_booked_count || $current_service_booked_count < $bookingpress_service_max_capacity  ){
										$v['is_booked'] = 0;
									}
								} else {
									$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);		
									$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
									$v['max_capacity'] = $bookingpress_service_max_capacity;
									
									if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment != $bookingpress_service_max_capacity){
										$v['is_booked'] = 0;
									}
								}
							}
						}
					} else {	

						if( 1 < $bookingpress_service_max_capacity && 1 == $v['is_booked'] ){
							
							$service_start_time = $v['start_time'].':00';
							$service_end_time = $v['end_time'].':00';

							$current_service_booked_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.

							$v['total_booked_appointment'] = $current_service_booked_count;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							if( 1 > $current_service_booked_count || $current_service_booked_count < $bookingpress_service_max_capacity ){
								$v['is_booked'] = 0;
							}
						} else {

							$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);
							
							$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							
							if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment != $bookingpress_service_max_capacity){
								$v['is_booked'] = 0;
							}
						}
					}

					$bookingpress_evening_time[$k] = $v;
				}

				$return_data['evening_time'] = $bookingpress_evening_time;
			}


			if( is_array($bookingpress_night_time) && !empty($bookingpress_night_time) ){
				foreach($bookingpress_night_time as $k => $v){
					$bookingpress_start_time = $v['start_time'];
					$bookingpress_end_time = $v['end_time'];
					
					if( $bookingpress_is_staffmember_module_activated && 0 < $bookingpress_selected_staffmember_id ){
						$bookingpress_total_booked_appointment = 0;
						$bookingpress_shared_service_timeslot = $BookingPress->bookingpress_get_settings('share_timeslot_between_services', 'general_setting');
						if( $bookingpress_shared_service_timeslot == 'true' ){
							$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);
						
							$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							
							if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment != $bookingpress_service_max_capacity){
								$v['is_booked'] = 0;
							}
						} else {
							if( 1 < $bookingpress_service_max_capacity ){
								$service_start_time = $v['start_time'].':00';
								$service_end_time = $v['end_time'].':00';
								$is_appointment_booked  = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d AND bookingpress_staff_member_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id, $bookingpress_selected_staffmember_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
							} else {
								$is_appointment_booked = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_payment_log_id) as total FROM {$tbl_bookingpress_payment_logs} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_staff_member_id = %d AND bookingpress_service_id = %d AND ((bookingpress_appointment_start_time >= %s AND bookingpress_appointment_start_time < %s) OR (bookingpress_appointment_start_time < %s AND bookingpress_appointment_end_time > %s ) )", "%{$selected_date}%", $bookingpress_selected_staffmember_id, $selected_service_id, $bookingpress_start_time, $bookingpress_end_time, $bookingpress_end_time, $bookingpress_start_time ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is table name.
							}
							if( $is_appointment_booked > 0 ){
								$v['total_booked_appointment'] = $is_appointment_booked;
								$v['max_capacity'] = $bookingpress_service_max_capacity;
								if( 1 < $bookingpress_service_max_capacity && $is_appointment_booked < $bookingpress_service_max_capacity ){
									$v['is_booked'] = 0;
								} else {
									$v['is_booked'] = 1;
								}
							} else {
								if( 1 < $bookingpress_service_max_capacity && 1 == $v['is_booked'] ){
									$service_start_time = $v['start_time'].':00';
									$service_end_time = $v['end_time'].':00';
		
									$current_service_booked_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
		
									$v['total_booked_appointment'] = $current_service_booked_count;
									$v['max_capacity'] = $bookingpress_service_max_capacity;
									if( 1 > $current_service_booked_count || $current_service_booked_count < $bookingpress_service_max_capacity  ){
										$v['is_booked'] = 0;
									}
								} else {
									$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);		
									$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
									$v['max_capacity'] = $bookingpress_service_max_capacity;
									
									if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment != $bookingpress_service_max_capacity){
										$v['is_booked'] = 0;
									}
								}
							}
						}
					} else {

						if( 1 < $bookingpress_service_max_capacity && 1 == $v['is_booked'] ){
							$service_start_time = $v['start_time'].':00';
							$service_end_time = $v['end_time'].':00';

							$current_service_booked_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date LIKE %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_end_time = %s AND bookingpress_service_id = %d", '%'.$selected_date.'%', $service_start_time, $service_end_time, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.

							$v['total_booked_appointment'] = $current_service_booked_count;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							if( 1 > $current_service_booked_count || $current_service_booked_count < $bookingpress_service_max_capacity ){
								$v['is_booked'] = 0;
							}
						} else {
							$bookingpress_total_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $selected_date, $bookingpress_start_time, $bookingpress_end_time);
							
							$v['total_booked_appointment'] = $bookingpress_total_booked_appointment;
							$v['max_capacity'] = $bookingpress_service_max_capacity;
							
							if( $bpa_start_datetime > $bpa_current_datetime && $bookingpress_total_booked_appointment != $bookingpress_service_max_capacity){
								$v['is_booked'] = 0;
							}
						}
					}

					$bookingpress_night_time[$k] = $v;
				}

				$return_data['night_time'] = $bookingpress_night_time;
			}

			/** service buffer time start */
			$bookingpress_service_buffertime_before = $bookingpress_services->bookingpress_get_service_meta( $selected_service_id, 'before_buffer_time' );
			$bookingpress_service_buffertime_before_unit = $bookingpress_services->bookingpress_get_service_meta( $selected_service_id, 'before_buffer_time_unit' );

			$bookingpress_service_buffertime_after = $bookingpress_services->bookingpress_get_service_meta( $selected_service_id, 'after_buffer_time' );
			$bookingpress_service_buffertime_after_unit = $bookingpress_services->bookingpress_get_service_meta( $selected_service_id, 'after_buffer_time_unit' );

			$service_timings_data = array_merge(
				$return_data['morning_time'],
				$return_data['afternoon_time'],
				$return_data['evening_time'],
				$return_data['night_time']
			);

			if( 0 < $bookingpress_service_buffertime_before ){
				
				$buffer_time_before = $bookingpress_service_buffertime_before . ' minutes';
				$time_before = 0;
				if( 'h' == $bookingpress_service_buffertime_before_unit ){
					$buffer_time_in_min = $bookingpress_service_buffertime_before * 60;
					$buffer_time_before = $buffer_time_in_min . ' minutes';
				}

				$service_start_time_slot = array();
				$service_end_time_slot = array();
				
				$total_service_timings = count( $service_timings_data );
				$service_start_time_slot = $service_timings_data[0];
				$service_end_time_slot = end( $service_timings_data );

				$service_booked_slots = array();
				$bfr = 0;
				$tmp_x = 1;
				foreach( $service_timings_data as $service_key => $service_selected_timings ){
					$service_start_time = $service_selected_timings['start_time'];
					$service_end_time = $service_selected_timings['end_time'];
					$is_service_booked = $service_selected_timings['is_booked'];
					$total_booked_appointment = $service_selected_timings['total_booked_appointment'];
					$max_capacity = $service_selected_timings['max_capacity'];
					
					if( 1 == $is_service_booked && $total_booked_appointment == $max_capacity ){
						
						$buffer_time_start = date( 'H:i', strtotime( '-' . $buffer_time_before, strtotime( $service_start_time ) ) );
						$buffer_time_end = date( 'H:i', strtotime( '+' . $buffer_time_before, strtotime( $buffer_time_start ) ) );
						
						for( $n = $bfr - 1; $n > 0 ; $n-- ){
							$current_service_arr = $service_timings_data[$n];
							$current_service_start_time = $current_service_arr['start_time'];
							$current_service_end_time = $current_service_arr['end_time'];
							if( $buffer_time_start <= $current_service_start_time ){
								
								$service_timings_data[ $n ]['is_booked'] = 1;
								$service_timings_data[ $n ]['total_booked_appointment'] = $service_timings_data[ $n ]['max_capacity'];
								$service_timings_data[ $n ]['buffer_reserve'] = 1;
								array_push( $service_booked_slots, $service_timings_data[$n] );
							} else if( $buffer_time_start >= $current_service_start_time && $buffer_time_start < $current_service_end_time ) {
								$service_timings_data[ $n ]['is_booked'] = 1;
								$service_timings_data[ $n ]['total_booked_appointment'] = $service_timings_data[ $n ]['max_capacity'];
								$service_timings_data[ $n ]['buffer_reserve'] = 1;
								array_push( $service_booked_slots, $service_timings_data[$n] );
							}

						}

						$x = $bfr + 1;
						$next_service_start_time = $service_timings_data[ $bfr + 1 ]['start_time'];
						$next_buffer_end_time = date('H:i', strtotime('+' . $buffer_time_before, strtotime( $next_service_start_time ) ) );
						if( 0 < $bookingpress_service_buffertime_after ){
							$buffer_time_after = ( $bookingpress_service_buffertime_after + $bookingpress_service_buffertime_before ) . ' minutes';
							if ('h' == $bookingpress_service_buffertime_after_unit) {
								$buffer_time_in_min =  ( $bookingpress_service_buffertime_after + $bookingpress_service_buffertime_before ) * 60;
								$buffer_time_after = $buffer_time_in_min . ' minutes';
							}
							$next_buffer_end_time = date('H:i', strtotime('+' . $buffer_time_after, strtotime( $next_service_start_time ) ) );
						}
						for( $i = $bfr + 1; $i < $total_service_timings; $i++ ){
							$future_service_start_time = $service_timings_data[ $i ]['start_time'];
							$future_service_end_time = $service_timings_data[ $i ]['end_time'];
							
							if( $next_buffer_end_time >= $future_service_end_time ){
								
								$service_timings_data[ $i ]['is_booked'] = 1;
								$service_timings_data[ $i ]['total_booked_appointment'] = $service_timings_data[ $i ]['max_capacity'];
								$service_timings_data[ $i ]['buffer_reserve'] = 1;
								array_push( $service_booked_slots, $service_timings_data[ $i ] );
							} else if( $next_buffer_end_time > $future_service_start_time && $next_buffer_end_time <= $future_service_end_time ){
								$service_timings_data[ $i ]['is_booked'] = 1;
								$service_timings_data[ $i ]['total_booked_appointment'] = $service_timings_data[ $i ]['max_capacity'];
								$service_timings_data[ $i ]['buffer_reserve'] = 1;
								array_push( $service_booked_slots, $service_timings_data[ $i ] );
							}
						}
					} else {
						if( 0 < $total_booked_appointment && $total_booked_appointment < $max_capacity && $max_capacity > 0 ){
							$buffer_time_start = date( 'H:i', strtotime( '-' . $buffer_time_before, strtotime( $service_start_time ) ) );
							$buffer_time_end = date( 'H:i', strtotime( '+' . $buffer_time_before, strtotime( $buffer_time_start ) ) );
							
							$booking_timings = $service_start_time . '__' . $service_end_time;
							for( $t = 0; $t < $total_booked_appointment; $t++ ){	
								for( $n = 0; $n < $bfr; $n++ ){
									if( !isset( $service_timings_data[$n]['booked_for_capacity'] ) ){
										$service_timings_data[$n]['booked_for_capacity'] = 0;
									}
									$current_service_arr = $service_timings_data[ $n ];
									$current_service_start_time = $current_service_arr['start_time'];
									$current_service_end_time = $current_service_arr['end_time'];
									
									if( ( $buffer_time_start >= $current_service_start_time && $buffer_time_start <= $current_service_end_time ) || ($buffer_time_start <= $current_service_start_time && $buffer_time_end >= $current_service_start_time ) ){
										$service_timings_data[$n]['booked_for_capacity']++;
										if( !empty( $service_booked_slots ) ) {
											$matched_key = array_search( $current_service_start_time, array_column( $service_booked_slots, 'start_time' ) );
											if( false !== $matched_key ){
												$service_booked_slots[ $matched_key ] = $service_timings_data[ $n ];
											} else {
												$service_booked_slots[] = $service_timings_data[$n];
											}
										} else {
											$service_booked_slots[] = $service_timings_data[$n];
										}
									}
								}
							}

							 $x = $bfr;
							$next_service_start_time = $service_timings_data[ $bfr ]['end_time'];
							$next_buffer_end_time = date('H:i', strtotime('+' . $buffer_time_before, strtotime( $next_service_start_time ) ) );
							
							if( 0 < $bookingpress_service_buffertime_after ){
								$buffer_time_after = ( $bookingpress_service_buffertime_after + $bookingpress_service_buffertime_before ) . ' minutes';
								if ('h' == $bookingpress_service_buffertime_after_unit) {
									$buffer_time_in_min =  ( $bookingpress_service_buffertime_after + $bookingpress_service_buffertime_before ) * 60;
									$buffer_time_after = $buffer_time_in_min . ' minutes';
								}
								$next_buffer_end_time = date('H:i', strtotime('+' . $buffer_time_after, strtotime( $next_service_start_time ) ) );
								for( $t = 0; $t < $total_booked_appointment; $t++ ){
									for( $n = $bfr + 1; $n < $total_service_timings; $n++ ){
										if( !isset( $service_timings_data[$n]['booked_for_capacity'] ) ){
											$service_timings_data[$n]['booked_for_capacity'] = 0;
										}
										$current_service_arr = $service_timings_data[ $n ];
										$current_service_start_time = $current_service_arr['start_time'];
										$current_service_end_time = $current_service_arr['end_time'];
										
										if( ( $next_buffer_end_time > $current_service_start_time && $next_buffer_end_time <= $current_service_end_time ) || ( $next_buffer_end_time >= $current_service_end_time ) ){
				
											$service_timings_data[$n]['booked_for_capacity']++;
											if( !empty( $service_booked_slots ) ) {
												$matched_key = array_search( $current_service_start_time, array_column( $service_booked_slots, 'start_time' ) );
												if( false !== $matched_key ){
													$service_booked_slots[ $matched_key ] = $service_timings_data[ $n ];
												} else {
													$service_booked_slots[] = $service_timings_data[$n];
												}
											} else {
												$service_booked_slots[] = $service_timings_data[$n];
											}
										}
									}
								}
							}
							
							$tmp_x++;
						}
					}
					$bfr++;
				}

				if( !empty( $service_booked_slots ) ){
					foreach( $service_booked_slots as $k => $booked_slots ){
						if( !empty( $return_data['morning_time'] ) ){
							foreach( $return_data['morning_time'] as $mk => $morning_slots ){
								if( $morning_slots['start_time'] == $booked_slots['start_time'] && $morning_slots['end_time'] == $booked_slots['end_time'] ){
									if( !empty( $booked_slots['booked_for_capacity'] ) ){
										$return_data['morning_time'][ $mk ]['total_booked_appointment'] += $booked_slots['booked_for_capacity'];
										if( $return_data['morning_time'][ $mk ]['total_booked_appointment'] == $return_data['morning_time'][ $mk ]['max_capacity']){
											$return_data['morning_time'][ $mk ]['is_booked'] = 1;
										}
									} else {
										$return_data['morning_time'][ $mk ]['is_booked'] = 1;
									}
								}
							}
						}

						if( !empty( $return_data['afternoon_time'] ) ){
							foreach( $return_data['afternoon_time'] as $mk => $morning_slots ){
								if( $morning_slots['start_time'] == $booked_slots['start_time'] && $morning_slots['end_time'] == $booked_slots['end_time'] ){
									if( !empty( $booked_slots['booked_for_capacity'] ) ){
										$return_data['afternoon_time'][ $mk ]['total_booked_appointment'] += $booked_slots['booked_for_capacity'];
										if( $return_data['afternoon_time'][ $mk ]['total_booked_appointment'] == $return_data['afternoon_time'][ $mk ]['max_capacity']){
											$return_data['afternoon_time'][ $mk ]['is_booked'] = 1;
										}
									} else {
										$return_data['afternoon_time'][ $mk ]['is_booked'] = 1;
									}
								}
							}
						}

						if( !empty( $return_data['evening_time'] ) ){
							foreach( $return_data['evening_time'] as $mk => $morning_slots ){
								if( $morning_slots['start_time'] == $booked_slots['start_time'] && $morning_slots['end_time'] == $booked_slots['end_time'] ){
									if( !empty( $booked_slots['booked_for_capacity'] ) ){
										$return_data['evening_time'][ $mk ]['total_booked_appointment'] += $booked_slots['booked_for_capacity'];
										if( $return_data['evening_time'][ $mk ]['total_booked_appointment'] == $return_data['evening_time'][ $mk ]['max_capacity']){
											$return_data['evening_time'][ $mk ]['is_booked'] = 1;
										}
									} else {
										$return_data['evening_time'][ $mk ]['is_booked'] = 1;
									}
								}
							}
						}

						if( !empty( $return_data['night_time'] ) ){
							foreach( $return_data['night_time'] as $mk => $morning_slots ){
								if( $morning_slots['start_time'] == $booked_slots['start_time'] && $morning_slots['end_time'] == $booked_slots['end_time'] ){
									if( !empty( $booked_slots['booked_for_capacity'] ) ){
										$return_data['night_time'][ $mk ]['total_booked_appointment'] += $booked_slots['booked_for_capacity'];
										if( $return_data['night_time'][ $mk ]['total_booked_appointment'] == $return_data['night_time'][ $mk ]['max_capacity']){
											$return_data['night_time'][ $mk ]['is_booked'] = 1;
										}
									} else {
										$return_data['night_time'][ $mk ]['is_booked'] = 1;
									}
								}
							}
						}
					}
				}
			}

			if( 0 < $bookingpress_service_buffertime_after && $bookingpress_service_buffertime_before < 1 ){
				$buffer_time_after = $bookingpress_service_buffertime_after . ' minutes';
				$time_before = 0;
				if ('h' == $bookingpress_service_buffertime_after_unit) {
					$buffer_time_in_min = $bookingpress_service_buffertime_after * 60;
					$buffer_time_after = $buffer_time_in_min . ' minutes';
				}

				$service_start_time_slot = array();
				$service_end_time_slot = array();
				
				$total_service_timings = count($service_timings_data);
				$service_start_time_slot = $service_timings_data[0];
				$service_end_time_slot = end($service_timings_data);

				$service_booked_slots = array();
				$bfr = 0;
				
				foreach ($service_timings_data as $service_key => $service_selected_timings) {
					$service_start_time = $service_selected_timings['start_time'];
					$service_end_time = $service_selected_timings['end_time'];
					$is_service_booked = $service_selected_timings['is_booked'];
					$total_booked_appointment = $service_selected_timings['total_booked_appointment'];
					$max_capacity = $service_selected_timings['max_capacity'];

					if ($bfr == 0 || $total_service_timings == ($bfr + 1)) {
						$bfr++;
						continue;
					}

					if (1 == $is_service_booked && $total_booked_appointment == $max_capacity ) {
						
						$buffer_time_end = date( 'H:i', strtotime( '+' . $buffer_time_after, strtotime( $service_end_time ) ) );
						$x = $bfr + 1;
						$next_service_start_time = $service_timings_data[ $x ]['start_time'];
						
						for( $i = $x; $i < $total_service_timings; $i++ ){
							$future_service_start_time = $service_timings_data[ $i ][ 'start_time' ];
							$future_service_end_time = $service_timings_data[ $i ][ 'end_time' ];
							$is_service_in_buffer = !empty($service_timings_data[ $x ]['buffer_reserver']) ? true : false; 
							if( $is_service_in_buffer ){
								continue;
							}

							if( $buffer_time_end > $future_service_start_time ){
								$service_timings_data[$i]['is_booked'] = 1;
								$service_timings_data[$i]['total_booked_appointment'] = $service_timings_data[$i]['max_capacity'];
								array_push($service_booked_slots, $service_timings_data[$i]);
							}

						}

					} else {
						if( 0 < $total_booked_appointment && $total_booked_appointment < $max_capacity && $max_capacity > 0 ){
							$buffer_time_end = date( 'H:i', strtotime( '+' . $buffer_time_after, strtotime( $service_end_time ) ) );
							
							for( $x = 0; $x < $total_booked_appointment; $x++ ){
								for( $n = $bfr + 1; $n < $total_service_timings; $n++ ){
									if( !isset( $service_timings_data[$n]['booked_for_capacity'] ) ){
										$service_timings_data[$n]['booked_for_capacity'] = 0;
									}

									$current_service_arr = $service_timings_data[ $n ];
									$current_service_start_time = $current_service_arr['start_time'];
									$current_service_end_time = $current_service_arr['end_time'];

									if( ( $buffer_time_end > $current_service_start_time && $buffer_time_end <= $current_service_end_time ) || ( $buffer_time_end >= $current_service_end_time ) ){
										$service_timings_data[$n]['booked_for_capacity']++;
										if( !empty( $service_booked_slots ) ) {
											$matched_key = array_search( $current_service_start_time, array_column( $service_booked_slots, 'start_time' ) );
											if( false !== $matched_key ){
												$service_booked_slots[ $matched_key ] = $service_timings_data[ $n ];
											} else {
												$service_booked_slots[] = $service_timings_data[$n];
											}
										} else {
											$service_booked_slots[] = $service_timings_data[$n];
										}
									}
								}
							}
						}
					}
					$bfr++;
				}

				if (!empty($service_booked_slots)) {
					foreach ($service_booked_slots as $k => $booked_slots) {
						if (!empty($return_data['morning_time'])) {
							foreach ($return_data['morning_time'] as $mk => $morning_slots) {
								if ($morning_slots['start_time'] == $booked_slots['start_time'] && $morning_slots['end_time'] == $booked_slots['end_time']) {
									if( !empty( $booked_slots['booked_for_capacity'] ) ){
										$return_data['morning_time'][ $mk ]['total_booked_appointment'] += $booked_slots['booked_for_capacity'];
										if( $return_data['morning_time'][ $mk ]['total_booked_appointment'] == $return_data['morning_time'][ $mk ]['max_capacity']){
											$return_data['morning_time'][ $mk ]['is_booked'] = 1;
										}
									} else {
										$return_data['morning_time'][$mk]['is_booked'] = 1;
									}
								}
							}
						}
			
						if (!empty($return_data['afternoon_time'])) {
							foreach ($return_data['afternoon_time'] as $mk => $morning_slots) {
								if ($morning_slots['start_time'] == $booked_slots['start_time'] && $morning_slots['end_time'] == $booked_slots['end_time']) {
									if( !empty( $booked_slots['booked_for_capacity'] ) ){
										$return_data['afternoon_time'][ $mk ]['total_booked_appointment'] += $booked_slots['booked_for_capacity'];
										if( $return_data['afternoon_time'][ $mk ]['total_booked_appointment'] == $return_data['afternoon_time'][ $mk ]['max_capacity']){
											$return_data['afternoon_time'][ $mk ]['is_booked'] = 1;
										}
									} else {
										$return_data['afternoon_time'][$mk]['is_booked'] = 1;
									}
								}
							}
						}
			
						if (!empty($return_data['evening_time'])) {
							foreach ($return_data['evening_time'] as $mk => $morning_slots) {
								if ($morning_slots['start_time'] == $booked_slots['start_time'] && $morning_slots['end_time'] == $booked_slots['end_time']) {
									if( !empty( $booked_slots['booked_for_capacity'] ) ){
										$return_data['evening_time'][ $mk ]['total_booked_appointment'] += $booked_slots['booked_for_capacity'];
										if( $return_data['evening_time'][ $mk ]['total_booked_appointment'] == $return_data['evening_time'][ $mk ]['max_capacity']){
											$return_data['evening_time'][ $mk ]['is_booked'] = 1;
										}
									} else {
										$return_data['evening_time'][$mk]['is_booked'] = 1;
									}
								}
							}
						}
			
						if (!empty($return_data['night_time'])) {
							foreach ($return_data['night_time'] as $mk => $morning_slots) {
								if ($morning_slots['start_time'] == $booked_slots['start_time'] && $morning_slots['end_time'] == $booked_slots['end_time']) {
									if( !empty( $booked_slots['booked_for_capacity'] ) ){
										$return_data['night_time'][ $mk ]['total_booked_appointment'] += $booked_slots['booked_for_capacity'];
										if( $return_data['night_time'][ $mk ]['total_booked_appointment'] == $return_data['night_time'][ $mk ]['max_capacity']){
											$return_data['night_time'][ $mk ]['is_booked'] = 1;
										}
									} else {
										$return_data['night_time'][$mk]['is_booked'] = 1;
									}
								}
							}
						}
					}
				}
			}
			/** service buffer time end */

			return $return_data;
		}
		
		/**
		 * Modify service start time at frontend
		 *
		 * @param  mixed $start_time
		 * @param  mixed $service_id
		 * @return void
		 */
		function bookingpress_modify_service_start_time_func( $start_time, $service_id ){

			if( !empty( $service_id ) ){
				global $bookingpress_services;
				$bookingpress_service_buffertime_before = $bookingpress_services->bookingpress_get_service_meta( $service_id, 'before_buffer_time' );
				if( empty( $bookingpress_service_buffertime_before ) || 1 > $bookingpress_service_buffertime_before ){
					return $start_time;
				}
				$bookingpress_service_buffertime_before_unit = $bookingpress_services->bookingpress_get_service_meta( $service_id, 'before_buffer_time_unit' );


				$buffer_time_before = $bookingpress_service_buffertime_before . ' minutes';
				$time_before = 0;
				if( 'h' == $bookingpress_service_buffertime_before_unit ){
					$buffer_time_in_min = $bookingpress_service_buffertime_before * 60;
					$buffer_time_before = $buffer_time_in_min . ' minutes';
				}

				$start_time = date( 'H:i', strtotime( '+' . $buffer_time_before, strtotime( $start_time ) ) );
			}
			return $start_time;
		}
				
		/**
		 * Modify service end time at frontend
		 *
		 * @param  mixed $end_time
		 * @param  mixed $service_id
		 * @return void
		 */
		function bookingpress_modify_service_end_time_func( $end_time, $service_id ){
			if( !empty( $service_id ) ){
				global $bookingpress_services;
				$bookingpress_service_buffertime_after = $bookingpress_services->bookingpress_get_service_meta( $service_id, 'after_buffer_time' );
				if( empty( $bookingpress_service_buffertime_after ) || 1 > $bookingpress_service_buffertime_after ){
					return $end_time;
				}
				$bookingpress_service_buffertime_after_unit = $bookingpress_services->bookingpress_get_service_meta( $service_id, 'after_buffer_time_unit' );

				$buffer_time_after = $bookingpress_service_buffertime_after . ' minutes';
				$time_before = 0;
				if( 'h' == $bookingpress_service_buffertime_after_unit ){
					$buffer_time_in_min = $bookingpress_service_buffertime_after * 60;
					$buffer_time_after = $buffer_time_in_min . ' minutes';
				}

				$end_time = date( 'H:i', strtotime( '-' . $buffer_time_after, strtotime( $end_time ) ) );
			}
			return $end_time;
		}
		
		/**
		 * Modify service data as per category selection
		 *
		 * @param  mixed $service_data
		 * @param  mixed $selected_category_id
		 * @param  mixed $bookingpress_posted_data
		 * @return void
		 */
		function bookingpress_modify_service_data_on_category_selection_func($service_data, $selected_category_id, $bookingpress_posted_data){
			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers_services, $bookingpress_pro_staff_members;
			if(!empty($service_data)){

				$bookingpress_is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();

				if($bookingpress_is_staffmember_module_activated){
					foreach($service_data as $k => $v){
						$bookingpress_service_id = $v['bookingpress_service_id'];

						$bookingpress_is_staffmember_assigned = $wpdb->get_row($wpdb->prepare( "SELECT COUNT(bookingpress_staffmember_service_id) as total FROM ".$tbl_bookingpress_staffmembers_services." WHERE bookingpress_service_id = %d", $bookingpress_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm	

						if($bookingpress_is_staffmember_assigned['total'] == 0){
							unset($service_data[$k]);
						}
					}
				}

				$bookingpress_form_sequence = $BookingPress->bookingpress_get_customize_settings('bookingpress_form_sequance', 'booking_form');
				if($bookingpress_form_sequence == "staff_selection" && $bookingpress_is_staffmember_module_activated){
					$bookingpress_selected_staffmember_id = !empty($bookingpress_posted_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) ? intval($bookingpress_posted_data['bookingpress_selected_staff_member_details']['selected_staff_member_id']) : 0;

					foreach($service_data as $k => $v){
						$bookingpress_service_id = $v['bookingpress_service_id'];
						$bookingpress_staffmember_services = $wpdb->get_row($wpdb->prepare( "SELECT * FROM ".$tbl_bookingpress_staffmembers_services." WHERE bookingpress_service_id = %d AND bookingpress_staffmember_id = %d", $bookingpress_service_id, $bookingpress_selected_staffmember_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm

						if(empty($bookingpress_staffmember_services)){
							unset($service_data[$k]);
						}
					}
				}

			}
			return $service_data;
		}
		
		/**
		 * Get service max capacity
		 *
		 * @return void
		 */
		function bookingpress_get_service_max_capacity_func(){
			global $wpdb, $BookingPress, $bookingpress_services;

			$response = array();
			$bookingpress_wp_nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

			$bpa_verify_nonce_flag = wp_verify_nonce( $bookingpress_wp_nonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				echo wp_json_encode( $response );
				die();
			}

			$response['variant'] = 'error';
			$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg'] = esc_html__( 'Something went wrong', 'bookingpress-appointment-booking' );
			$response['max_capacity'] = 1;

			$bookingpress_service_id = !empty($_POST['service_id']) ? intval($_POST['service_id']) : 0;
			if(!empty($bookingpress_service_id)){
				$bookingpress_max_capacity = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'max_capacity');

				$response['max_capacity'] = intval($bookingpress_max_capacity);
				$response['variant'] = 'success';
				$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
				$response['msg'] = esc_html__('Max capacity data retrieved successfully', 'bookingpress-appointment-booking');
			}

			echo wp_json_encode($response);
			exit;
		}
		
		/**
		 * Function for execute code when previous step action trigger
		 *
		 * @param  mixed $bookingpress_add_data_for_previous_page
		 * @return void
		 */
		function bookingpress_add_data_for_previous_page_func($bookingpress_add_data_for_previous_page){
			$bookingpress_add_data_for_previous_page .= 'vm.bookingpress_open_extras_drawer = "false";';
			//$bookingpress_add_data_for_previous_page .= 'vm.bookingpress_open_extras_drawer = "true";';
			return $bookingpress_add_data_for_previous_page;
		}
		
		/**
		 * Function for check some code before first step change
		 *
		 * @param  mixed $bookingpress_add_data_for_first_step_on_next_page
		 * @return void
		 */
		function bookingpress_add_data_for_first_step_on_next_page_func($bookingpress_add_data_for_first_step_on_next_page){
			global $bookingpress_pro_staff_members;
			if($bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation()){
				$bookingpress_add_data_for_first_step_on_next_page .= 'if(vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id == ""){
					vm.bookingpress_set_error_msg("' . esc_html__('Please select staff member to proceed further', 'bookingpress-appointment-booking') . '");
					vm.current_selected_tab_id = 1;
					return false;
				}';
			}else{
				$bookingpress_add_data_for_first_step_on_next_page .= 'if(vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id == ""){
					vm.bookingpress_open_extras_drawer = "false";
				}';
			}
			return $bookingpress_add_data_for_first_step_on_next_page;
		}
		
		/**
		 * Function for check minimum time required
		 *
		 * @param  mixed $minimum_time_required
		 * @param  mixed $bookingpress_service_id
		 * @return void
		 */
		function bookingpress_retrieve_minimum_required_time_func( $minimum_time_required, $bookingpress_service_id ){

			global $wpdb, $BookingPress, $bookingpress_services,$bookingpress_appointment_bookings;
			$bookingpress_all_service_data = array();
			$bookingpress_lite_version = get_option('bookingpress_version');
			if( version_compare( $bookingpress_lite_version, '1.0.79', '>' ) ){
				$bookingpress_all_service_data = (isset($bookingpress_appointment_bookings->bookingpress_all_service_data))?$bookingpress_appointment_bookings->bookingpress_all_service_data:array();
			}	
			if($bookingpress_service_id && !empty($bookingpress_all_service_data) && isset($bookingpress_all_service_data[$bookingpress_service_id]['services_meta']['minimum_time_required_before_booking'])){
				$bookingpress_minimum_time_required_for_booking = $bookingpress_all_service_data[$bookingpress_service_id]['services_meta']['minimum_time_required_before_booking'];				
			}else{
				$bookingpress_minimum_time_required_for_booking = $bookingpress_services->bookingpress_get_service_meta( $bookingpress_service_id, 'minimum_time_required_before_booking' );
			}

			if( 'disabled' != $bookingpress_minimum_time_required_for_booking ){
				$minimum_time_required = $bookingpress_minimum_time_required_for_booking;
			}
			
			if( 'inherit' == $minimum_time_required || '' == $minimum_time_required ){
				$minimum_time_required = $BookingPress->bookingpress_get_settings( 'default_minimum_time_for_booking', 'general_setting' );
			}

			return $minimum_time_required;
		}
		
		/**
		 * Change frontend shortcode calendar dates as per service selection
		 *
		 * @return void
		 */
		function bookingpress_change_front_calendar_dates_func() {
			global $wpdb, $BookingPress, $bookingpress_services;

			$response = array();

			$bookingpress_wp_nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

			$bpa_verify_nonce_flag = wp_verify_nonce( $bookingpress_wp_nonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				echo wp_json_encode( $response );
				die();
			}

			$response['variant']            = 'error';
			$response['title']              = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']                = esc_html__( 'Something went wrong', 'bookingpress-appointment-booking' );
			$response['disabled_dates']     = array();
			$response['next_selected_date'] = '';

			$bookingpress_service_id = ! empty( $_POST['service_obj']['selected_service'] ) ? intval( $_POST['service_obj']['selected_service'] ) : 0;
			if ( ! empty( $bookingpress_service_id ) ) {
				$default_daysoff_details = $BookingPress->bookingpress_get_default_dayoff_dates();

				$bookingpress_next_date = $booking_date = ! empty( $_POST['service_obj']['selected_date'] ) ? date( 'Y-m-d H:i:s', strtotime( sanitize_text_field( $_POST['service_obj']['selected_date'] ) ) ) : date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
				$booking_date_timestamp = strtotime( $booking_date );

				$bookingpress_minimum_time_required_for_booking = $bookingpress_services->bookingpress_get_service_meta( $bookingpress_service_id, 'minimum_time_required_before_booking' ); // Selected service meta value

				if ( $bookingpress_minimum_time_required_for_booking != 'disabled' && $bookingpress_minimum_time_required_for_booking >= 1440 ) {

					$bookingpress_total_days = intval( $bookingpress_minimum_time_required_for_booking / 1440 );

					$bookingpress_current_date           = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
					$bookingpress_current_date_timestamp = strtotime( $bookingpress_current_date );

					if ( $booking_date_timestamp == $bookingpress_current_date_timestamp ) {
						array_push( $default_daysoff_details, date( 'c', $booking_date_timestamp ) );
						for ( $i = 1; $i <= $bookingpress_total_days; $i++ ) {
							$bookingpress_next_date = date( 'c', strtotime( '+' . $i . 'days', $bookingpress_current_date_timestamp ) );
							array_push( $default_daysoff_details, $bookingpress_next_date );
						}
					} else {
						$bookingpress_date_diff_in_minutes = round( abs( $booking_date_timestamp - $bookingpress_current_date_timestamp ) / 60, 2 );

						if ( $bookingpress_date_diff_in_minutes <= $bookingpress_minimum_time_required_for_booking ) {
							array_push( $default_daysoff_details, date( 'c', $booking_date_timestamp ) );
							for ( $i = 1; $i < $bookingpress_total_days; $i++ ) {
								$bookingpress_next_date = date( 'c', strtotime( '+' . $i . 'days', $bookingpress_current_date_timestamp ) );
								array_push( $default_daysoff_details, $bookingpress_next_date );
							}
						}
					}

					$bookingpress_next_date = date( 'Y-m-d', strtotime( '+' . $bookingpress_total_days . ' days', $booking_date_timestamp ) );
					
				}

				$default_daysoff_details = implode( ',', $default_daysoff_details );

				$response['variant']            = 'success';
				$response['title']              = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']                = esc_html__( 'Disabled dates retrieved successfully', 'bookingpress-appointment-booking' );
				$response['disabled_dates']     = $default_daysoff_details;
				$response['next_selected_date'] = $bookingpress_next_date;
			}

			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_remove_disabled_services_func( $all_service_data ){

			global $wpdb, $tbl_bookingpress_servicesmeta,$BookingPress,$bookingpress_services,$bookingpress_appointment_bookings;
			$bookingpress_all_service_data = array();
			$bookingpress_lite_version = get_option('bookingpress_version');
			if( version_compare( $bookingpress_lite_version, '1.0.79', '>' ) ){
				$bookingpress_all_service_data = (isset($bookingpress_appointment_bookings->bookingpress_all_service_data))?$bookingpress_appointment_bookings->bookingpress_all_service_data:array();			
			}	
			foreach( $all_service_data as $sk => $service_data ){
				$service_id = $service_data['bookingpress_service_id'];

				$is_disable_service = "";
				if($service_id && !empty($bookingpress_all_service_data) && isset($bookingpress_all_service_data[$service_id]['services_meta']['show_service_on_site'])){
					$show_service_on_site  = $bookingpress_all_service_data[$service_id]['services_meta']['show_service_on_site'];
					if($show_service_on_site != 1 && $show_service_on_site != '1' && $show_service_on_site != 'true'){
						$is_disable_service = $service_id;
					}
				  }else{
					$is_disable_service = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_id FROM {$tbl_bookingpress_servicesmeta} WHERE bookingpress_service_id = %d AND bookingpress_servicemeta_name = %s AND bookingpress_servicemeta_value = %s", $service_id, 'show_service_on_site', 'false' )); //phpcs:ignore
				  }												

				$bookingpress_service_expiration_date = !empty($service_data['bookingpress_service_expiration_date']) ? $service_data['bookingpress_service_expiration_date'] : '';
				$booking_date_timestamp = strtotime( $bookingpress_service_expiration_date);
				$minimum_time_required  = 'disabled';
				$minimum_time_required  = apply_filters( 'bookingpress_retrieve_minimum_required_time', $minimum_time_required, $service_id );
				if ( !empty($bookingpress_service_expiration_date) && $minimum_time_required != 'disabled' && ( $minimum_time_required >= 1440) ) {
					$bookingpress_total_days 			 = intval( $minimum_time_required ) / 1440;						
					$bookingpress_current_date           = date( 'Y-m-d',strtotime( '+' . $bookingpress_total_days . 'days', current_time( 'timestamp' )) );
					$bookingpress_current_date_timestamp = strtotime( $bookingpress_current_date );
					if ( $booking_date_timestamp <  $bookingpress_current_date_timestamp ) {				
						unset( $all_service_data[$sk]);
					}					
				}
				if( $service_id == $is_disable_service || ( !empty($service_data['bookingpress_service_expiration_date'] ) && ( $service_data['bookingpress_service_expiration_date']) < date('Y-m-d',current_time('timestamp')))){					
					unset( $all_service_data[$sk]);
				} elseif(empty($service_data['bookingpress_service_expiration_date'])) {
					$bookingpress_max_days_for_booking          = $BookingPress->bookingpress_get_settings( 'period_available_for_booking', 'general_setting' );
					$all_service_data[$sk]['bookingpress_service_expiration_date'] = date('Y-m-d',strtotime('+'.$bookingpress_max_days_for_booking.' days'));
				}
			}
			return array_values( $all_service_data );
		}
		
		/**
		 * bookingpress_set_service_max_capacity
		 *
		 * @param  mixed $bookingpress_before_selecting_booking_service_data
		 * @return void
		 */
		function bookingpress_set_service_max_capacity( $bookingpress_before_selecting_booking_service_data ){

			
			$bookingpress_before_selecting_booking_service_data .= '
			
			if( selected_service_id != "" ){

					let sidebar_step_data = vm.bookingpress_sidebar_step_data;
					/** Check if staff member is enabled, visible and after service step */
					if( vm.is_staffmember_activated == 1){
						if( sidebar_step_data.staffmembers.is_first_step == 0 ){
							let max_capacity = 1;
							let min_capacity = 1;
							let staffmember_details = vm.bookingpress_staffmembers_details;
							let staff_capacities = [];
							let staff_min_capacities = [];
							staffmember_details.forEach( (element,index) =>{
								if( "undefined" == typeof element.assigned_service_price_details[ selected_service_id ] ){
									return true;
								}
								let staff_services = element.assigned_service_price_details[ selected_service_id ];
								let staff_capacity = staff_services.assigned_service_capacity;
								staff_capacities.push( parseInt(staff_capacity) );

								if( vm.is_bring_anyone_with_you_activated == 1  ){
									let staff_min_capacity = staff_services.assigned_service_min_capacity;
									staff_min_capacities.push( parseInt(staff_min_capacity) );	
								}
							});
							
							max_capacity = Math.max.apply(null, staff_capacities);
							vm.appointment_step_form_data.service_max_capacity = parseInt(max_capacity);
							if( vm.is_bring_anyone_with_you_activated == 1 ){
								min_capacity = Math.min.apply(null, staff_min_capacities);
								vm.appointment_step_form_data.service_min_capacity = parseInt(min_capacity);
								vm.appointment_step_form_data.bookingpress_selected_bring_members = vm.appointment_step_form_data.service_min_capacity;
								
								let members = vm.appointment_step_form_data.bookingpress_selected_bring_members;
								let uniqueId = vm.appointment_step_form_data.bookingpress_uniq_id;
								let uniqueId2 = uniqueId.split("").reverse().join("");

								let salt = `${uniqueId}${members}${uniqueId2}`;
								let token = btoa( salt );
								vm.appointment_step_form_data.multiple_quantity_token = token;

								vm.bookingpress_update_staffmember_data( vm.appointment_step_form_data.bookingpress_selected_bring_members, selected_service_id );
								vm.bookingpress_repeat_custom_form_fields();
							}
						} else {
							let staff_id = vm.appointment_step_form_data.selected_staff_member_id;
							if( "" != staff_id ){
								let staff_details;
								vm.bookingpress_staffmembers_details.forEach( (element,index) => {
									if( element.bookingpress_staffmember_id == staff_id ){
										staff_details = element;
										return false;
									}
								});
								if( staff_details != "" ){
									let staff_service_data = staff_details.assigned_service_price_details[ selected_service_id ];
									let staff_service_capacity = staff_service_data.assigned_service_capacity;
									let staff_service_min_capacity = staff_service_data.assigned_service_min_capacity;
									vm.appointment_step_form_data.service_max_capacity = parseInt(staff_service_capacity);
									vm.appointment_step_form_data.service_min_capacity = parseInt(staff_service_min_capacity);

									if( vm.is_bring_anyone_with_you_activated == 1  ){
										vm.appointment_step_form_data.bookingpress_selected_bring_members = vm.appointment_step_form_data.service_min_capacity;

										let members = vm.appointment_step_form_data.bookingpress_selected_bring_members;
										let uniqueId = vm.appointment_step_form_data.bookingpress_uniq_id;
										let uniqueId2 = uniqueId.split("").reverse().join("");

										let salt = `${uniqueId}${members}${uniqueId2}`;
										let token = btoa( salt );
										vm.appointment_step_form_data.multiple_quantity_token = token;

										vm.bookingpress_update_staffmember_data( vm.appointment_step_form_data.bookingpress_selected_bring_members, selected_service_id );
										vm.bookingpress_repeat_custom_form_fields();
									}
								}
							}
						}
						
					} else if( 1 != vm.is_staffmember_activated ){
						/* vm.all_services_data.forEach( (element, index) => {
							if( "" == element.service_max_capacity ){
								element.service_max_capacity = 1;
							}
							if( element.bookingpress_service_id == selected_service_id ){
								vm.appointment_step_form_data.service_max_capacity = parseInt( element.service_max_capacity );
								return false;
							}
						}); */
						vm.appointment_step_form_data.service_max_capacity = vm.bookingpress_all_services_data[ selected_service_id ].services_meta.max_capacity;

						if( vm.is_bring_anyone_with_you_activated == 1  ){

							vm.appointment_step_form_data.service_min_capacity = vm.bookingpress_all_services_data[ selected_service_id ].services_meta.min_capacity;
							vm.appointment_step_form_data.bookingpress_selected_bring_members = vm.appointment_step_form_data.service_min_capacity;

							let members = vm.appointment_step_form_data.bookingpress_selected_bring_members;
							let uniqueId = vm.appointment_step_form_data.bookingpress_uniq_id;
							let uniqueId2 = uniqueId.split("").reverse().join("");

							let salt = `${uniqueId}${members}${uniqueId2}`;
							let token = btoa( salt );
							vm.appointment_step_form_data.multiple_quantity_token = token;

							vm.bookingpress_update_staffmember_data( vm.appointment_step_form_data.bookingpress_selected_bring_members );
							vm.bookingpress_repeat_custom_form_fields();
						}
					}

					/** Check if service max capacity > 1 and quantity feature is enabled */
					if( vm.is_bring_anyone_with_you_activated == 1 && 1 < vm.appointment_step_form_data.service_max_capacity /*&& this.is_service_loaded_from_url != "1" /*  && "true" == is_move_to_next */ ){
						is_move_to_next = "false";
						vm.bookingpress_open_extras_drawer = "true";
						vm.isServiceLoadTimeLoader = "1";
					}
				}
			';

			return $bookingpress_before_selecting_booking_service_data;
		}
		
		/**
		 * Function for execute code before select any service
		 *
		 * @param  mixed $bookingpress_before_selecting_booking_service_data
		 * @return void
		 */
		function bookingpress_before_selecting_booking_service_func($bookingpress_before_selecting_booking_service_data){
			$bookingpress_before_selecting_booking_service_data .= '
				if( "undefined" != typeof vm.appointment_step_form_data.bookingpress_selected_staff_member_details.is_any_staff_option_selected && 1 == vm.appointment_step_form_data.bookingpress_selected_staff_member_details.is_any_staff_option_selected ) {
					vm.appointment_step_form_data.bookingpress_selected_staff_member_details.is_any_staff_option_selected = 0;
				}
				
				if( "undefined" != typeof vm.appointment_step_form_data.selected_staff_member_id && "" != vm.appointment_step_form_data.selected_staff_member_id ){
					let current_selected_tab = "service";
					let sidebar_step_data = vm.bookingpress_sidebar_step_data;
					let sidebar_keys = Object.keys( sidebar_step_data );
					let current_tab_pos = sidebar_keys.indexOf( "service" );

					let selected_tab_pos = sidebar_keys.indexOf( "staffmembers" );
					if( "undefined" == typeof vm.appointment_step_form_data.bookingpress_is_load_staff_from_share_url || vm.appointment_step_form_data.bookingpress_is_load_staff_from_share_url == "0" ){
						if( selected_tab_pos > current_tab_pos ){
							vm.appointment_step_form_data.selected_staff_member_id = "";
							vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = "";
							vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = "";
						}
					}
				}
			';	

			/** service extra related */
			$bookingpress_before_selecting_booking_service_data .= '
			let is_drawer_opened = "false";
			let use_drawer_flag = false;
			/* vm.appointment_step_form_data.is_extra_service_exists = "0";
			if( vm.bookingpress_is_extra_enable == 1){
				use_drawer_flag = true;
				for( let i in vm.appointment_step_form_data.bookingpress_selected_extra_details ){
					vm.appointment_step_form_data.bookingpress_selected_extra_details[i].bookingpress_is_selected = false;
					vm.appointment_step_form_data.bookingpress_selected_extra_details[i].bookingpress_selected_qty = 1;
				}
				for( let n in vm.all_services_data ){
					let element = vm.all_services_data[n];
					if( element.bookingpress_service_id == selected_service_id && element.extra_service_counter > 0 ){
						vm.bookingpress_open_extras_drawer = "true";
						vm.appointment_step_form_data.is_extra_service_exists = "1";
						is_drawer_opened = "true";
						vm.isServiceLoadTimeLoader = "1";
					}
				}
			} */';

			/** Staff Member Related */
			$bookingpress_before_selecting_booking_service_data .= '
			
			if( vm.is_staffmember_activated == 1 && vm.appointment_step_form_data.hide_staff_selection == "false" && vm.appointment_step_form_data.form_sequence[0] != "staff_selection" ){
				for( let index in vm.bookingpress_staffmembers_details ){
					let currentValue = vm.bookingpress_staffmembers_details[index];
					
					vm.bookingpress_staffmembers_details[index].is_display_staff = false;
					
					if("undefined" != typeof currentValue.assigned_service_price_details && "undefined" != typeof currentValue.assigned_service_price_details[selected_service_id] && "public" == vm.bookingpress_staffmembers_details[index].staffmember_visibility ){
						vm.bookingpress_staffmembers_details[index].is_display_staff = true;
					}
				}

				if( "" == vm.appointment_step_form_data.selected_staff_member_id || 0 == vm.appointment_step_form_data.selected_staff_member_id ){
					let next_tabname = vm.bookingpress_sidebar_step_data.staffmembers.next_tab_name;

					if( "datetime" == next_tabname && 1 == vm.bookingpress_sidebar_step_data[ next_tabname ].is_allow_navigate ){
						vm.bookingpress_sidebar_step_data[ next_tabname ].is_allow_navigate = 0;
					}
				}

				/* let is_staff_exists = 0;
				let total_staff = 0;
				let total_private_staff = 0;
				let deselect_staff = false;
				let staff_id = vm.appointment_step_form_data.selected_staff_member_id;
				let available_staffs = [];
				vm.bookingpress_staffmembers_details.forEach(function(currentValue, index, arr){
					if(currentValue.assigned_service_details.includes(selected_service_id)){
						if( currentValue.staffmember_visibility == "private" ){
							total_private_staff++;
						}
						total_staff++;
						is_staff_exists = 1;
						available_staffs.push( currentValue.bookingpress_staffmember_id );
					}
				});
				
				if( available_staffs.length > 0 && staff_id > 0 && !available_staffs.includes( staff_id ) ){
					vm.appointment_step_form_data.selected_staff_member_id = "";
					vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = "";
					vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = "";
				}
				
				if( 1 == is_staff_exists ){
					if( total_staff == total_private_staff ){
						await vm.bookingpress_select_staffmember("any_staff", 1);
						is_staff_exists = 0;
						vm.appointment_step_form_data.is_staff_exists = 0;
						vm.appointment_step_form_data.hide_staff_selection = "true";
					} else {
						vm.isServiceLoadTimeLoader = "1";
						is_drawer_opened = "true";
						vm.appointment_step_form_data.is_staff_exists = 1;
					}
				} */
			}';

			/** jump to next page if drawer is not opened */
			$bookingpress_before_selecting_booking_service_data .= '
				if( "false" == is_move_to_next && "false" == is_drawer_opened ){
					is_move_to_next = "true";
				}

				if( "true" == is_move_to_next && "d" == vm.appointment_step_form_data.selected_service_duration_unit ){
					if( 0 == vm.bookingpress_sidebar_step_data.service.is_display_step && "datetime" == vm.bookingpress_current_tab ){
						is_move_to_next = false;
					}
				}
			';

			return $bookingpress_before_selecting_booking_service_data;
		}
		
		/**
		 * Function for execute code after select service
		 *
		 * @param  mixed $bookingpress_after_selecting_booking_service_data
		 * @return void
		 */
		function bookingpress_after_selecting_booking_service_func( $bookingpress_after_selecting_booking_service_data ) {
			$bookingpress_after_selecting_booking_service_data .= 'vm.service_advance_see_less = "0";';
			//$bookingpress_after_selecting_booking_service_data .= 'vm.bookingpress_calculate_service_addons_price(vm.appointment_step_form_data.selected_service);';

			//$bookingpress_after_selecting_booking_service_data .= 'vm.bookingpress_modify_front_dates_as_service();';

			//$bookingpress_after_selecting_booking_service_data .= 'vm.bookingpress_get_service_capacity();';

			$bookingpress_after_selecting_booking_service_data .= '
				if( /*vm.bookingpress_is_extra_enable == "1" ||*/ vm.is_bring_anyone_with_you_activated == "1" || vm.is_staffmember_activated == "1" ){
					var is_extra_exists = 0;
					var service_max_length = parseInt(vm.appointment_step_form_data.service_max_capacity);
					var is_staff_exists = 0;

					/*vm.bookingpress_service_extras.forEach(function(currentValue, index, arr){
						if(currentValue.bookingpress_service_id == vm.appointment_step_form_data.selected_service){
							is_extra_exists = 1;
							vm.appointment_step_form_data.is_extra_service_exists = 1;
						}
					});*/

					var bpa_tmp_selected_service = vm.appointment_step_form_data.selected_service.toString();
					vm.bookingpress_staffmembers_details.forEach(function(currentValue, index, arr){
						if(currentValue.assigned_service_details.includes(bpa_tmp_selected_service)){
							is_staff_exists = 1;
						}
					});

					if(is_staff_exists == 1){
						vm.appointment_step_form_data.is_staff_exists = 1;
						if(vm.appointment_step_form_data.hide_staff_selection == "true" && vm.is_staff_first_step != "1" && (typeof vm.appointment_step_form_data.custom_service_duration_value == "undefined" || ( vm.appointment_step_form_data.custom_service_duration_value != "undefined" && vm.appointment_step_form_data.custom_service_duration_value == "") ) ){
							/* vm.bookingpress_select_staffmember("any_staff", 1); */
						}
					}					
					
					/* if( (vm.bookingpress_is_extra_enable == "1" && is_extra_exists == 1) || (vm.is_bring_anyone_with_you_activated == "1" && service_max_length >= 1) || ( vm.appointment_step_form_data.hide_staff_selection != "true" && vm.is_staffmember_activated == "1" && ( vm.is_any_staff_option_enable == 1 || is_staff_exists == 1 ) ) || (vm.appointment_step_form_data.bookingpress_is_load_staff_from_share_url == 1) ){ */
					
					if( (vm.bookingpress_is_extra_enable == "1" && is_extra_exists == 1) || (vm.is_bring_anyone_with_you_activated == "1" && service_max_length > 1) ){
						vm.bookingpress_open_extras_drawer = "false";
						vm.bookingpress_open_extras_drawer = "true";
						vm.isServiceLoadTimeLoader = "0";
					} else{
						if((is_staff_exists == 1 && vm.appointment_step_form_data.hide_staff_selection == "true" && vm.bookingpress_current_tab == "datetime") || (vm.appointment_step_form_data.selected_service_duration_unit == "d" && ( ( vm.bookingpress_is_extra_enable == "1" && is_extra_exists == 0) || (vm.is_bring_anyone_with_you_activated == "1" && service_max_length == 1) ))) {
							/* If staffmember selection step hide and staff member selection is in drawer then do not need to execute next step navigation function */
						}else{
							/*if(vm.bookingpress_current_tab != "datetime") {
								vm.bookingpress_step_navigation(vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].next_tab_name, vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].next_tab_name, vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].previous_tab_name)
							}*/
							
						}
					}';

					if(wp_is_mobile()){
						$bookingpress_after_selecting_booking_service_data.= 'if(typeof vm.appointment_step_form_data.selected_service  != "undefined" && vm.appointment_step_form_data.selected_service != "" && vm.bookingpress_open_extras_drawer){
							var selected_service_div = document.getElementById("bap-service-"+vm.appointment_step_form_data.selected_service);							
							if(selected_service_div && typeof selected_service_div != "undefined"){
								setTimeout(function(){
									selected_service_div.scrollIntoView({behavior:"smooth"});
								},200);							
							}
						}';	
					}

				$bookingpress_after_selecting_booking_service_data.= '}
				if(vm.is_coupon_activated == "1" && vm.appointment_step_form_data.coupon_code != ""){
					vm.bookingpress_remove_coupon_code();
				}
			';

			return $bookingpress_after_selecting_booking_service_data;
		}
		
		/**
		 * Function for calculate service addons price
		 *
		 * @return void
		 */
		function bookingpress_calculate_service_addons_price_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_services, $tbl_bookingpress_staffmembers_services, $tbl_bookingpress_extra_services, $tbl_bookingpress_staff_member_workhours, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_service_workhours, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_service_special_day;

			$response = array();

			$bookingpress_wp_nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';

			$bpa_verify_nonce_flag = wp_verify_nonce( $bookingpress_wp_nonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				echo wp_json_encode( $response );
				die();
			}

			$response['variant']                      = 'error';
			$response['title']                        = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']                          = esc_html__( 'Something went wrong', 'bookingpress-appointment-booking' );
			$response['selected_service_total_price'] = '';
			$response['price_without_currency']       = '';
			$response['price_with_currency']          = '';
			$response['tax_amount']                   = '';
			$response['tax_amount_without_currency']  = '';
			
			$response['is_tax_calculated']            = 0;

			$bookingpress_service_id = ! empty( $_POST['selected_service_obj']['selected_service'] ) ? intval( $_POST['selected_service_obj']['selected_service'] ) : 0;

			if ( ! empty( $bookingpress_service_id ) ) {
				$bookingpress_service_details   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $bookingpress_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
				$bookingpress_tmp_service_price = $bookingpress_service_price = ! empty( $bookingpress_service_details['bookingpress_service_price'] ) ? floatval( $bookingpress_service_details['bookingpress_service_price'] ) : 0;

				// If staffmember has assigned this service then select that staffmember price
				$bookingpress_staffmember_id = ! empty( $_POST['selected_service_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['selected_service_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : 0;
				if ( ! empty( $bookingpress_staffmember_id ) ) {
					$bookingpress_staffmember_assigned_service_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $bookingpress_staffmember_id, $bookingpress_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm

					if ( ! empty( $bookingpress_staffmember_assigned_service_details ) ) {
						$bookingpress_tmp_service_price = $bookingpress_service_price = ! empty( $bookingpress_staffmember_assigned_service_details['bookingpress_service_price'] ) ? floatval( $bookingpress_staffmember_assigned_service_details['bookingpress_service_price'] ) : 0;
					}

					// get Staffmember working days
					// --------------------------------------------------------------------------
					$bookingpress_staffmember_working_days = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_is_break = %d", $bookingpress_staffmember_id, 0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is table name.

					$bpa_staffmember_special_days = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_special_day_id,bookingpress_special_day_service_id,bookingpress_special_day_start_date,bookingpress_special_day_end_date FROM {$tbl_bookingpress_staffmembers_special_day} WHERE bookingpress_staffmember_id = %d", $bookingpress_staffmember_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day is table name.
					
					$staff_member_special_days = array();
					if( !empty( $bpa_staffmember_special_days ) && count( $bpa_staffmember_special_days ) ){
						foreach( $bpa_staffmember_special_days as $bpa_staff_sp_days ){
							$bpa_staff_sp_day_services = $bpa_staff_sp_days['bookingpress_special_day_service_id'];
							if( empty( $bpa_staff_sp_day_services ) ){
								$bpa_staff_sp_start_date = date('Y-m-d', strtotime( $bpa_staff_sp_days['bookingpress_special_day_start_date'] ) );
								$bpa_staff_sp_end_date = date( 'Y-m-d', strtotime( $bpa_staff_sp_days['bookingpress_special_day_end_date'] ) );

								$bpa_staff_sp_date_start = date_create( $bpa_staff_sp_start_date );
								$bpa_staff_sp_date_end = date_create( $bpa_staff_sp_end_date );

								$days_diff = date_diff( $bpa_staff_sp_date_start, $bpa_staff_sp_date_end );
								$days_diff = $days_diff->days;

								if( $days_diff > 0 ){
									for( $d = 0; $d <= $days_diff; $d++ ){
										if( 0 == $d ){
											$current_date = $bpa_staff_sp_start_date;
										}
										
										$staff_member_special_days[] = $current_date;

										$current_date = date( 'Y-m-d', strtotime( $current_date. '+1 days') );
									}
								}
							} else {
								$bpa_staff_sp_day_service_ids = explode( ',', $bpa_staff_sp_day_services );
								if( !empty( $bookingpress_service_id ) && in_array( $bookingpress_service_id, $bpa_staff_sp_day_service_ids ) ){
									$bpa_staff_sp_start_date = date('Y-m-d', strtotime( $bpa_staff_sp_days['bookingpress_special_day_start_date'] ) );
									$bpa_staff_sp_end_date = date( 'Y-m-d', strtotime( $bpa_staff_sp_days['bookingpress_special_day_end_date'] ) );

									$bpa_staff_sp_date_start = date_create( $bpa_staff_sp_start_date );
									$bpa_staff_sp_date_end = date_create( $bpa_staff_sp_end_date );

									$days_diff = date_diff( $bpa_staff_sp_date_start, $bpa_staff_sp_date_end );
									$days_diff = $days_diff->days;

									if( $days_diff > 0 ){
										for( $d = 0; $d <= $days_diff; $d++ ){
											if( 0 == $d ){
												$current_date = $bpa_staff_sp_start_date;
											}
											
											$staff_member_special_days[] = $current_date;

											$current_date = date( 'Y-m-d', strtotime( $current_date. '+1 days') );
										}
									}
								}
							}
						}
					}
					
					if( !empty( $bookingpress_staffmember_working_days ) ){
						$is_staffmember_monday_break             = 0;
						$is_staffmember_tuesday_break            = 0;
						$is_staffmember_wednesday_break          = 0;
						$is_staffmember_thursday_break           = 0;
						$is_staffmember_friday_break             = 0;
						$is_staffmember_saturday_break           = 0;
						$is_staffmember_sunday_break             = 0;

						foreach( $bookingpress_staffmember_working_days as $staffmember_workhour_key => $staffmember_workhour_val ){
							$bookingpress_staffmember_start_time = $staffmember_workhour_val['bookingpress_staffmember_workhours_start_time'];
							$bookingpress_staffmember_end_time   = $staffmember_workhour_val['bookingpress_staffmember_workhours_end_time'];

							if( 'monday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_monday_break = 1;
							} else if( 'tuesday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_tuesday_break = 1;
							} else if( 'wednesday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_wednesday_break = 1;
							} else if( 'thursday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_thursday_break = 1;
							} else if( 'friday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_friday_break = 1;
							} else if( 'saturday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_saturday_break = 1;
							} else if( 'sunday' == strtolower( $staffmember_workhour_val['bookingpress_staffmember_workday_key'] ) && ( null == $bookingpress_staffmember_start_time || null == $bookingpress_staffmember_end_time ) ){
								$is_staffmember_sunday_break = 1;
							}
						}

						$default_year            = date('Y', current_time('timestamp'));
						$staffmember_default_daysoff_details = array();

						$calendar_start_date = $calendar_next_date = date('Y-m-d', current_time('timestamp'));
						$calendar_end_date   = date('Y-m-d', strtotime('+1 year', current_time('timestamp')));
						for ( $i = 1; $i <= 730; $i++ ) {
							$current_day_name = date('l', strtotime($calendar_next_date));
							
							if ($current_day_name == 'Monday' && $is_staffmember_monday_break == 1 ) {
								if( !empty( $staff_member_special_days ) && in_array( $calendar_next_date, $staff_member_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($staffmember_default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} elseif ($current_day_name == 'Tuesday' && $is_staffmember_tuesday_break == 1 ) {
								if( !empty( $staff_member_special_days ) && in_array( $calendar_next_date, $staff_member_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($staffmember_default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} elseif ($current_day_name == 'Wednesday' && $is_staffmember_wednesday_break == 1 ) {
								if( !empty( $staff_member_special_days ) && in_array( $calendar_next_date, $staff_member_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($staffmember_default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} elseif ($current_day_name == 'Thursday' && $is_staffmember_thursday_break == 1 ) {
								if( !empty( $staff_member_special_days ) && in_array( $calendar_next_date, $staff_member_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($staffmember_default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} elseif ($current_day_name == 'Friday' && $is_staffmember_friday_break == 1 ) {
								if( !empty( $staff_member_special_days ) && in_array( $calendar_next_date, $staff_member_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($staffmember_default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} elseif ($current_day_name == 'Saturday' && $is_staffmember_saturday_break == 1 ) {
								if( !empty( $staff_member_special_days ) && in_array( $calendar_next_date, $staff_member_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($staffmember_default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} elseif ($current_day_name == 'Sunday' && $is_staffmember_sunday_break == 1 ) {
								if( !empty( $staff_member_special_days ) && in_array( $calendar_next_date, $staff_member_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($staffmember_default_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							}

							$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
						}

						$response['bookingpress_daysoff_dates'] = implode( ',' , $staffmember_default_daysoff_details );
					} else {
						$service_specific_hours_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$tbl_bookingpress_service_workhours}` WHERE bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = 0", $bookingpress_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is table name.

						$service_special_days_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_special_day_start_date, bookingpress_special_day_end_date FROM `{$tbl_bookingpress_service_special_day}` WHERE bookingpress_service_id = %d", $bookingpress_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_special_day is table name.

						$bpa_service_special_days = array();
						if( !empty( $service_special_days_data ) && count( $service_special_days_data ) > 0 ){
							foreach( $service_special_days_data as $bpa_service_sp_days ){
								$bpa_service_sp_start_date = date( 'Y-m-d', strtotime( $bpa_service_sp_days['bookingpress_special_day_start_date'] ) );
								$bpa_service_sp_end_date = date( 'Y-m-d', strtotime( $bpa_service_sp_days['bookingpress_special_day_end_date'] ) );

								$bpa_service_sp_date_start = date_create( $bpa_service_sp_start_date );
								$bpa_service_sp_date_end = date_create( $bpa_service_sp_end_date );

								$days_diff = date_diff( $bpa_service_sp_date_start, $bpa_service_sp_date_end );
								$days_diff = $days_diff->days;

								if( $days_diff > 0 ){
									for( $d = 0; $d <= $days_diff; $d++ ){
										if( 0 == $d ){
											$current_date = $bpa_service_sp_start_date;
										}
										
										$bpa_service_special_days[] = $current_date;
					
										$current_date = date( 'Y-m-d', strtotime( $current_date . '+1 days') );
									}
								}
							}
						}

						if( !empty( $service_specific_hours_data ) ){
							$is_service_monday_break             = 0;
							$is_service_tuesday_break            = 0;
							$is_service_wednesday_break          = 0;
							$is_service_thursday_break           = 0;
							$is_service_friday_break             = 0;
							$is_service_saturday_break           = 0;
							$is_service_sunday_break             = 0;
							foreach( $service_specific_hours_data as $service_workhour_data ){
								$bookingpress_start_time = $service_workhour_data['bookingpress_service_workhours_start_time'];
								$bookingpress_end_time = $service_workhour_data['bookingpress_service_workhours_end_time'];
	
								if( 'monday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
									$is_service_monday_break = 1;
								} else if( 'tuesday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
									$is_service_tuesday_break = 1;
								} else if( 'wednesday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
									$is_service_wednesday_break = 1;
								} else if( 'thursday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
									$is_service_thursday_break = 1;
								} else if( 'friday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
									$is_service_friday_break = 1;
								} else if( 'saturday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
									$is_service_saturday_break = 1;
								} else if( 'sunday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
									$is_service_sunday_break = 1;
								}
							}
							$default_year            = date('Y', current_time('timestamp'));
	
							$service_daysoff_details = array();
							$calendar_start_date = $calendar_next_date = date('Y-m-d', current_time('timestamp'));
							$calendar_end_date   = date('Y-m-d', strtotime('+1 year', current_time('timestamp')));
	
							for ( $i = 1; $i <= 730; $i++ ) {
								$current_day_name = date('l', strtotime($calendar_next_date));
								if( 'Monday' == $current_day_name && 1 == $is_service_monday_break ){
									if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
										$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
										continue;
									}
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
								} else if( 'Tuesday' == $current_day_name && 1 == $is_service_tuesday_break ){
									if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
										$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
										continue;
									}
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
								} else if( 'Wednesday' == $current_day_name && 1 == $is_service_wednesday_break ){
									if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
										$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
										continue;
									}
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
								} else if( 'Thursday' == $current_day_name && 1 == $is_service_thursday_break ){
									if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
										$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
										continue;
									}
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
								} else if( 'Friday' == $current_day_name && 1 == $is_service_friday_break ){
									if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
										$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
										continue;
									}
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
								} else if( 'Saturday' == $current_day_name && 1 == $is_service_saturday_break ){
									if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
										$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
										continue;
									}
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
								} else if( 'Sunday' == $current_day_name && 1 == $is_service_sunday_break ){
									if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
										$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
										continue;
									}
									$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
									array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
								}
								$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
							}
							$response['bookingpress_daysoff_dates'] = implode( ',' , $service_daysoff_details );
						} else {
							$response['bookingpress_daysoff_dates'] = implode( ',', $BookingPress->bookingpress_get_default_dayoff_dates() );	
						}
					}
				} else {
					$service_specific_hours_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$tbl_bookingpress_service_workhours}` WHERE bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = 0", $bookingpress_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is table name.

					$service_special_days_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_special_day_start_date, bookingpress_special_day_end_date FROM `{$tbl_bookingpress_service_special_day}` WHERE bookingpress_service_id = %d", $bookingpress_service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_special_day is table name.

					$bpa_service_special_days = array();
					if( !empty( $service_special_days_data ) && count( $service_special_days_data ) > 0 ){
						foreach( $service_special_days_data as $bpa_service_sp_days ){
							$bpa_service_sp_start_date = date( 'Y-m-d', strtotime( $bpa_service_sp_days['bookingpress_special_day_start_date'] ) );
							$bpa_service_sp_end_date = date( 'Y-m-d', strtotime( $bpa_service_sp_days['bookingpress_special_day_end_date'] ) );

							$bpa_service_sp_date_start = date_create( $bpa_service_sp_start_date );
							$bpa_service_sp_date_end = date_create( $bpa_service_sp_end_date );

							$days_diff = date_diff( $bpa_service_sp_date_start, $bpa_service_sp_date_end );
							$days_diff = $days_diff->days;

							if( $days_diff > 0 ){
								for( $d = 0; $d <= $days_diff; $d++ ){
									if( 0 == $d ){
										$current_date = $bpa_service_sp_start_date;
									}
									
									$bpa_service_special_days[] = $current_date;
				
									$current_date = date( 'Y-m-d', strtotime( $current_date . '+1 days') );
								}
							}
						}
					}

					$is_service_monday_break             = 0;
					$is_service_tuesday_break            = 0;
					$is_service_wednesday_break          = 0;
					$is_service_thursday_break           = 0;
					$is_service_friday_break             = 0;
					$is_service_saturday_break           = 0;
					$is_service_sunday_break             = 0;

					if( !empty( $service_specific_hours_data ) ){
						foreach( $service_specific_hours_data as $service_workhour_data ){
							$bookingpress_start_time = $service_workhour_data['bookingpress_service_workhours_start_time'];
							$bookingpress_end_time = $service_workhour_data['bookingpress_service_workhours_end_time'];

							if( 'monday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
								$is_service_monday_break = 1;
							} else if( 'tuesday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
								$is_service_tuesday_break = 1;
							} else if( 'wednesday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
								$is_service_wednesday_break = 1;
							} else if( 'thursday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
								$is_service_thursday_break = 1;
							} else if( 'friday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
								$is_service_friday_break = 1;
							} else if( 'saturday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
								$is_service_saturday_break = 1;
							} else if( 'sunday' == strtolower($service_workhour_data['bookingpress_service_workday_key']) && ( null == $bookingpress_start_time || null == $bookingpress_end_time ) ){
								$is_service_sunday_break = 1;
							}
						}
						$default_year            = date('Y', current_time('timestamp'));

						$service_daysoff_details = array();
						$calendar_start_date = $calendar_next_date = date('Y-m-d', current_time('timestamp'));
						$calendar_end_date   = date('Y-m-d', strtotime('+1 year', current_time('timestamp')));

						for ( $i = 1; $i <= 730; $i++ ) {
							$current_day_name = date('l', strtotime($calendar_next_date));
							if( 'Monday' == $current_day_name && 1 == $is_service_monday_break ){
								if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} else if( 'Tuesday' == $current_day_name && 1 == $is_service_tuesday_break ){
								if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} else if( 'Wednesday' == $current_day_name && 1 == $is_service_wednesday_break ){
								if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} else if( 'Thursday' == $current_day_name && 1 == $is_service_thursday_break ){
								if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} else if( 'Friday' == $current_day_name && 1 == $is_service_friday_break ){
								if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} else if( 'Saturday' == $current_day_name && 1 == $is_service_saturday_break ){
								if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							} else if( 'Sunday' == $current_day_name && 1 == $is_service_sunday_break ){
								if( !empty( $bpa_service_special_days ) && in_array( $calendar_next_date, $bpa_service_special_days ) ){
									$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
									continue;
								}
								$daysoff_tmp_date = date('Y-m-d', strtotime($calendar_next_date));
								array_push($service_daysoff_details, date('c', strtotime($daysoff_tmp_date)));
							}
							$calendar_next_date = date('Y-m-d', strtotime($calendar_next_date . ' +1 days'));
						}
						$response['bookingpress_daysoff_dates'] = implode( ',' , $service_daysoff_details );
						//$response['bookingpress_daysoff_dates'] = implode( ',', $BookingPress->bookingpress_get_default_dayoff_dates() );
					} else {
						$response['bookingpress_daysoff_dates'] = implode( ',', $BookingPress->bookingpress_get_default_dayoff_dates() );
					}
				}

				$bookingpress_appointment_details = $_POST['selected_service_obj']; //phpcs:ignore
				$bookingpress_tmp_service_price = $bookingpress_service_price = apply_filters('bookingpress_modify_recalculate_amount_before_calculation',$bookingpress_tmp_service_price,$bookingpress_appointment_details);

				// Calculate selected extra service prices
				// -------------------------------------------------------------------------------------------------------------
				$bookingpress_extra_service_price_arr = array();
				$bookingpress_extra_service_details = !empty($_POST['selected_service_obj']['bookingpress_selected_extra_details']) ? array_map( array( $BookingPress, 'appointment_sanatize_field'), $_POST['selected_service_obj']['bookingpress_selected_extra_details'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
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

				// Calculate Bring anyone with you module price
				$bookingpress_bring_anyone_module_price_arr = array();
				$bookingpress_selected_members              = ! empty( $_POST['selected_service_obj']['bookingpress_selected_bring_members'] ) ? intval( $_POST['selected_service_obj']['bookingpress_selected_bring_members'] ) - 1 : 0;

				if ( $bookingpress_selected_members > 0 ) {
					$bookingpress_bring_anyone_with_you_price = $bookingpress_service_price * $bookingpress_selected_members;

					array_push( $bookingpress_bring_anyone_module_price_arr, $bookingpress_bring_anyone_with_you_price );
				}

				// Add extra service price to final price
				if ( ! empty( $bookingpress_extra_service_price_arr ) && is_array( $bookingpress_extra_service_price_arr ) ) {
					foreach ( $bookingpress_extra_service_price_arr as $k => $v ) {
						$bookingpress_service_price     = $bookingpress_service_price + $v;
						$bookingpress_tmp_service_price = $bookingpress_tmp_service_price + $v;
					}
				}

				// Add bring anyone with you price to final price
				if ( ! empty( $bookingpress_bring_anyone_module_price_arr ) && is_array( $bookingpress_bring_anyone_module_price_arr ) ) {
					foreach ( $bookingpress_bring_anyone_module_price_arr as $k2 => $v2 ) {
						$bookingpress_service_price     = $bookingpress_service_price + $v2;
						$bookingpress_tmp_service_price = $bookingpress_tmp_service_price + $v2;
					}
				}

				// Calculate subtotal price of service
				$bookingpress_tmp_service_price           = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $bookingpress_service_price );
				$response['selected_service_total_price'] = $bookingpress_tmp_service_price;

				// Add tax to final price
				$bookingpress_tax_percentage = ! empty( $_POST['selected_service_obj']['tax_percentage'] ) ? floatval( $_POST['selected_service_obj']['tax_percentage'] ) : 0;
				if ( ! empty( $bookingpress_tax_percentage ) ) {
					$bookingpress_tax_price_display_options = !empty($_POST['selected_service_obj']['tax_price_display_options']) ? sanitize_text_field($_POST['selected_service_obj']['tax_price_display_options']) : 'exclude_taxes'; // phpcs:ignore

					$bookingpress_tax_amount = 0;
					
					if($bookingpress_tax_price_display_options == "include_taxes"){
						$bookingpress_tax_amount = ($bookingpress_service_price * $bookingpress_tax_percentage) / (100+$bookingpress_tax_percentage);
						$response['tax_included_amount'] = $bookingpress_service_price;
					}else{
						$bookingpress_tax_amount    = $bookingpress_service_price * ( $bookingpress_tax_percentage / 100 );
						$bookingpress_service_price = $bookingpress_service_price + $bookingpress_tax_amount;
						
						$response['tax_excluded_amount'] = $bookingpress_service_price;
					}
					$response['tax_amount_without_currency'] = $bookingpress_tax_amount;
					$bookingpress_tax_amount = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $bookingpress_tax_amount );
					$response['tax_amount']        = $bookingpress_tax_amount;
					$response['is_tax_calculated'] = 1;
					$response['tax_included_amount'] = $bookingpress_service_price;
				}

				$bookingpress_price_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $bookingpress_service_price );

				$response['variant']                = 'success';
				$response['title']                  = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']                    = esc_html__( 'Price calculated successfully', 'bookingpress-appointment-booking' );
				$response['price_without_currency'] = $bookingpress_service_price;
				$response['price_with_currency']    = $bookingpress_price_with_currency;
			}

			echo wp_json_encode( $response );
			exit;
		}
		
		/**
		 * Modify front shortcode data from outside of booking form shortcode
		 *
		 * @param  mixed $bookingpress_uniq_id
		 * @param  mixed $bookingpress_class_vars_val_arr
		 * @param  mixed $booking_form_shortcode_args
		 * @return void
		 */
		function bookingpress_add_dynamic_details_booking_shortcode_func( $bookingpress_uniq_id, $bookingpress_class_vars_val_arr, $booking_form_shortcode_args ) {
			global $wpdb, $BookingPress, $BookingPressPro, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services;

			$bookingpress_staffmember_id = ! empty( $_GET['bpstaffmember_id'] ) ? intval( $_GET['bpstaffmember_id'] ) : 0;
			if ( $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() && ! empty( $bookingpress_staffmember_id ) ) {
				
			}
		}
		
		/**
		 * Modify default workhour start time
		 *
		 * @param  mixed $bookingpress_default_start_time
		 * @param  mixed $selected_date
		 * @return void
		 */
		function bookingpress_modify_default_workhour_start_time_func($bookingpress_default_start_time, $selected_date){
			global $wpdb, $BookingPress, $tbl_bookingpress_default_special_day, $tbl_bookingpress_default_special_day_breaks;

			$selected_date = date('Y-m-d H:i:s', strtotime($selected_date));

			$bookingpress_default_special_days = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_special_day_start_time FROM {$tbl_bookingpress_default_special_day} WHERE bookingpress_special_day_start_date <= %s AND bookingpress_special_day_end_date >= %s", $selected_date, $selected_date), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day is a table name. false alarm

			$bookingpress_default_start_time = !empty($bookingpress_default_special_days['bookingpress_special_day_start_time']) ? $bookingpress_default_special_days['bookingpress_special_day_start_time'] : $bookingpress_default_start_time;

			return $bookingpress_default_start_time;
		}
		
		/**
		 * Modify default workhour end time
		 *
		 * @param  mixed $bookingpress_default_end_time
		 * @param  mixed $selected_date
		 * @return void
		 */
		function bookingpress_modify_default_workhour_end_time_func($bookingpress_default_end_time, $selected_date){
			global $wpdb, $BookingPress, $tbl_bookingpress_default_special_day, $tbl_bookingpress_default_special_day_breaks;

			$selected_date = date('Y-m-d H:i:s', strtotime($selected_date));

			$bookingpress_default_special_days = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_special_day_end_time FROM {$tbl_bookingpress_default_special_day} WHERE bookingpress_special_day_start_date <= %s AND bookingpress_special_day_end_date >= %s", $selected_date, $selected_date), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day is a table name. false alarm

			$bookingpress_default_end_time = !empty($bookingpress_default_special_days['bookingpress_special_day_end_time']) ? $bookingpress_default_special_days['bookingpress_special_day_end_time'] : $bookingpress_default_end_time;

			return $bookingpress_default_end_time;
		}
		
		/**
		 * Modify default break timings
		 *
		 * @param  mixed $break_timings
		 * @param  mixed $break_start_time
		 * @param  mixed $break_end_time
		 * @param  mixed $service_tmp_current_time
		 * @param  mixed $service_current_time
		 * @param  mixed $selected_date
		 * @return void
		 */
		function bookingpress_modify_default_break_timings_func($break_timings, $break_start_time, $break_end_time, $service_tmp_current_time, $service_current_time, $selected_date){
			global $wpdb, $BookingPress, $tbl_bookingpress_default_special_day, $tbl_bookingpress_default_special_day_breaks;
			$selected_date = date('Y-m-d H:i:s', strtotime($selected_date));

			$bookingpress_default_special_days = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_default_special_day} WHERE bookingpress_special_day_start_date <= %s AND bookingpress_special_day_end_date >= %s", $selected_date, $selected_date), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day is a table name. false alarm

			if(!empty($bookingpress_default_special_days)){
				$bookingpress_special_day_id = intval($bookingpress_default_special_days['bookingpress_special_day_id']);
				$bookingpress_special_day_breaks = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_default_special_day_breaks} WHERE bookingpress_special_day_id = %d AND bookingpress_special_day_break_start_time <= %s AND bookingpress_special_day_break_end_time >= %s", $bookingpress_special_day_id, date('H:i:s', strtotime($service_tmp_current_time)), date('H:i:s', strtotime($service_current_time))), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day_breaks is table name.

				if(!empty($bookingpress_special_day_breaks)){
					$break_timings['break_start_time'] = $bookingpress_special_day_breaks['bookingpress_special_day_break_start_time'];
					$break_timings['break_end_time'] = $bookingpress_special_day_breaks['bookingpress_special_day_break_end_time'];
				}
			}

			return $break_timings;
		}
		
		/**
		 * Modify service timings
		 *
		 * @param  mixed $service_timings
		 * @param  mixed $selected_service_id
		 * @param  mixed $selected_date
		 * @param  mixed $posted_data
		 * @return void
		 */
		function bookingpress_modify_service_timings_filter_func( $service_timings, $selected_service_id, $selected_date, $posted_data ) {
			
			global $wpdb, $BookingPress, $BookingPressPro, $bookingpress_services, $tbl_bookingpress_service_workhours, $tbl_bookingpress_services, $tbl_bookingpress_service_special_day, $tbl_bookingpress_service_special_day_breaks, $bookingpress_pro_staff_members, $tbl_bookingpress_staff_member_workhours, $tbl_bookingpress_staffmembers_meta, $bookingpress_pro_services, $tbl_bookingpress_appointment_bookings;
			
			//If service workhour enable then consider service workhours
			$bookingpress_is_service_specific_workhours = $bookingpress_services->bookingpress_get_service_meta($selected_service_id, 'bookingpress_configure_specific_service_workhour');

			$bookingpress_timezone                            = ! empty( $posted_data['bookingpress_timezone'] ) ? $posted_data['bookingpress_timezone'] : '';
			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );
			if ( ! empty( $bookingpress_timezone ) && ! empty( $bookingpress_timeslot_display_in_client_timezone ) && ( $bookingpress_timeslot_display_in_client_timezone == 'true' ) && ($bookingpress_is_service_specific_workhours != "true") ) {
				$service_timings = $BookingPress->bookingpress_get_service_available_time( $selected_service_id, $selected_date, $bookingpress_timezone );
			}
			
			if(!empty($bookingpress_timezone) && !empty($bookingpress_timeslot_display_in_client_timezone) && ($bookingpress_timeslot_display_in_client_timezone == 'true')){
				date_default_timezone_set($bookingpress_timezone);
			}

			$bpa_is_staffmember_module_active = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_default_time_slot = $BookingPress->bookingpress_get_settings( 'default_time_slot', 'general_setting' );
			$default_timeslot_step      = $bookingpress_default_time_slot;
			if( $bpa_is_staffmember_module_active ){
				$bookingpress_selected_staffmember_id = !empty($posted_data['bookingpress_selected_staffmember']['selected_staff_member_id']) ? intval($posted_data['bookingpress_selected_staffmember']['selected_staff_member_id']) : 0;
				if(!empty($bookingpress_selected_staffmember_id)){
					$is_staffmember_workhour_enable = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta($bookingpress_selected_staffmember_id, 'bookingpress_configure_specific_workhour');
					if($is_staffmember_workhour_enable == "true"){ //If staff member module workhour enable then applied those workhours
						$bookingpress_hide_already_booked_slot = $BookingPress->bookingpress_get_customize_settings( 'hide_already_booked_slot', 'booking_form' );
						$bookingpress_hide_already_booked_slot = ( $bookingpress_hide_already_booked_slot == 'true' ) ? 1 : 0;

						$bookingpress_hide_already_booked_slot = apply_filters( 'bookingpress_change_hide_already_booked_slot_for_service', $bookingpress_hide_already_booked_slot, $selected_service_id);
						
						$current_day  = ! empty( $selected_date ) ? ucfirst( date( 'l', strtotime( $selected_date ) ) ) : ucfirst( date( 'l', current_time( 'timestamp' ) ) );
						$current_date = ! empty($selected_date) ? date('Y-m-d', strtotime($selected_date)) : date('Y-m-d', current_time('timestamp'));
	
						$bpa_current_date = date('Y-m-d', strtotime(current_time('mysql')));
						
						if(!empty($bookingpress_timezone) && !empty($bookingpress_timeslot_display_in_client_timezone) && ($bookingpress_timeslot_display_in_client_timezone == 'true')){
							$bpa_current_time = date('H:i');
						}else{
							$bpa_current_time = date( 'H:i',strtotime(current_time('mysql')));
						}
	
						$service_time_duration     = $BookingPress->bookingpress_get_default_timeslot_data();
						$service_step_duration_val = $service_time_duration['default_timeslot'];
						if (! empty($selected_service_id) ) {
							// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
							$service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $selected_service_id), ARRAY_A);
							if (! empty($service_data) ) {
								$service_time_duration      = esc_html($service_data['bookingpress_service_duration_val']);
								$service_time_duration_unit = esc_html($service_data['bookingpress_service_duration_unit']);
								if ($service_time_duration_unit == 'h' ) {
									$service_time_duration = $service_time_duration * 60;
								}
								$service_step_duration_val = $service_time_duration;
							}
						}

						$bookingpress_show_time_as_per_service_duration = $BookingPress->bookingpress_get_settings( 'show_time_as_per_service_duration', 'general_setting' );

						if ( ! empty( $bookingpress_show_time_as_per_service_duration ) && $bookingpress_show_time_as_per_service_duration == 'false' ) {
							$bookingpress_default_time_slot = $BookingPress->bookingpress_get_settings( 'default_time_slot', 'general_setting' );
							$default_timeslot_step      = $bookingpress_default_time_slot;
							$time_unit                      = 'm';
							if ( $service_time_duration >= 60 ) {
								$default_timeslot_step = ( $service_time_duration / 60 );
								$time_unit                 = 'h';
							}
						}

						$bpa_fetch_updated_slots = false;
						if( isset( $_POST['bpa_fetch_data'] ) && 'true' == $_POST['bpa_fetch_data'] ){ //phpcs:ignore
							$bpa_fetch_updated_slots = true;
						}

						$service_step_duration_val = apply_filters( 'bookingpress_modify_service_timeslot', $service_step_duration_val, $selected_service_id, $service_time_duration_unit, $bpa_fetch_updated_slots );
	
						$already_booked_time_arr = $workhour_data = $break_hour_arr = array();

						$bookingpress_staffmember__special_day_details = $BookingPressPro->bookingpress_get_staffmember_special_days(  $bookingpress_selected_staffmember_id, $selected_service_id, $current_date );
						
						$bookingpress_staffmember_workhours = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_is_break = 0 AND bookingpress_staffmember_workday_key = %s", $bookingpress_selected_staffmember_id, ucfirst($current_day)), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is a table name. false alarm

						
						if( !empty( $bookingpress_staffmember__special_day_details ) || !empty($bookingpress_staffmember_workhours)){
							if(!empty($bookingpress_staffmember__special_day_details)){
								$staffmember_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_staffmember__special_day_details['special_day_start_time'])), $selected_service_id );
								$staffmember_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_staffmember__special_day_details['special_day_end_time'])), $selected_service_id );
							}else{
								$staffmember_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_staffmember_workhours['bookingpress_staffmember_workhours_start_time'])), $selected_service_id );
								$staffmember_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_staffmember_workhours['bookingpress_staffmember_workhours_end_time'])), $selected_service_id );
							}

							if ($service_start_time != null && $staffmember_end_time != null ) {
								while ( $staffmember_current_time <= $staffmember_end_time ) {

									if ($staffmember_current_time > $staffmember_end_time ) {
										break;
									}
			
									$service_tmp_current_time = $staffmember_current_time;
			
									if ($staffmember_current_time == '00:00' ) {
										$staffmember_current_time = date('H:i', strtotime($staffmember_current_time) + ( $service_step_duration_val * 60 ));
									} else {
										$service_tmp_time_obj = new DateTime($staffmember_current_time);
										$service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
										$staffmember_current_time = $service_tmp_time_obj->format('H:i');
									}
			
									$break_start_time      = '';
									$break_end_time        = '';
									
									// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
									$check_break_existance = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_staffmember_workhours_id) as total FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_workday_key = %s AND bookingpress_staffmember_workhours_is_break = 1 AND (bookingpress_staffmember_workhours_start_time BETWEEN %s AND %s)", $bookingpress_selected_staffmember_id, ucfirst($current_day), $service_tmp_current_time, $staffmember_current_time));
									
									if ($check_break_existance > 0 ) {
										// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
										$get_break_workhours = $wpdb->get_row($wpdb->prepare("SELECT TIMEDIFF(bookingpress_staffmember_workhours_end_time, bookingpress_staffmember_workhours_start_time) as time_diff, bookingpress_staffmember_workhours_start_time, bookingpress_staffmember_workhours_end_time FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workday_key = %s AND bookingpress_staffmember_workhours_is_break = 1 AND (bookingpress_staffmember_workhours_start_time BETWEEN %s AND %s )", $bookingpress_selected_staffmember_id, ucfirst($current_day), $service_tmp_current_time, $staffmember_current_time), ARRAY_A);
										$time_difference     = date('H:i', strtotime($get_break_workhours['time_diff']));
			
										$break_start_time     = date('H:i', strtotime($get_break_workhours['bookingpress_staffmember_workhours_start_time']));
										$break_end_time       = date('H:i', strtotime($get_break_workhours['bookingpress_staffmember_workhours_end_time']));
										$staffmember_current_time = $break_start_time;
									}

									if( !empty( $bookingpress_staffmember__special_day_details ) ){
										global $tbl_bookingpress_staffmembers_special_day_breaks, $tbl_bookingpress_staffmembers_special_day;
										
										$get_staffmember_special_day_break = $wpdb->get_row( $wpdb->prepare( "SELECT bssdb.bookingpress_special_day_break_start_time,bssdb.bookingpress_special_day_break_end_time FROM `{$tbl_bookingpress_staffmembers_special_day_breaks}` bssdb LEFT JOIN `{$tbl_bookingpress_staffmembers_special_day}` bssdw ON bssdb.bookingpress_special_day_id = bssdw.bookingpress_staffmember_special_day_id WHERE bssdw.bookingpress_staffmember_id = %d AND bssdw.bookingpress_special_day_service_id = %d", $bookingpress_selected_staffmember_id, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day_breaks & $tbl_bookingress_staffmembers_special_day are table name.
										$bookingpress_special_break_start_time = date('H:i', strtotime( $get_staffmember_special_day_break->bookingpress_special_day_break_start_time ) );
										$bookingpress_special_break_end_time = date('H:i', strtotime( $get_staffmember_special_day_break->bookingpress_special_day_break_end_time ) );
										if( $service_tmp_current_time >= $bookingpress_special_break_start_time && $staffmember_current_time <= $bookingpress_special_break_end_time ){
											$break_start_time = $bookingpress_special_break_start_time;
											$break_end_time = $bookingpress_special_break_end_time;
											$staffmember_current_time = $break_start_time;
										}
										
									}
			
									$is_appointment_booked = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $current_date, $service_tmp_current_time, $staffmember_current_time);
									$is_already_booked = ( $is_appointment_booked > 0 ) ? 1 : 0;
									if ($is_already_booked == 1 ) {
										$bookingpress_is_cancelled = $BookingPress->bookingpress_is_appointment_cancelled_or_rejected($selected_service_id, $current_date, $service_tmp_current_time, $staffmember_current_time);
										if($bookingpress_is_cancelled){
											$is_already_booked = 0;
										}
									}
			
									if ($staffmember_current_time < $service_start_time || $staffmember_current_time == $service_start_time ) {
										$staffmember_current_time = $staffmember_end_time;
									}
	
									$bookingpress_timediff_in_minutes = round(abs(strtotime($staffmember_current_time) - strtotime($service_tmp_current_time)) / 60, 2);
			
									if ($is_already_booked == 1 && $bookingpress_hide_already_booked_slot == 1 ) {
										continue;
									} else {
										if ($break_start_time != $service_tmp_current_time && $bookingpress_timediff_in_minutes >= $service_step_duration_val && $staffmember_current_time <= $staffmember_end_time ) {
											if ($bpa_current_date == $current_date ) {

												if ($service_tmp_current_time > $bpa_current_time ) {
													$workhour_data[] = array(
														'start_time' => $service_tmp_current_time,
														'end_time'   => $staffmember_current_time,
														'break_start_time' => $break_start_time,
														'break_end_time' => $break_end_time,
														'is_booked'  => $is_already_booked,
													);
												} else {
													/* $workhour_data[] = array(
														'start_time' => $service_tmp_current_time,
														'end_time'   => $staffmember_current_time,
														'break_start_time' => $break_start_time,
														'break_end_time' => $break_end_time,
														'is_booked'  => 1,
													); */
												}
											} else {
												$workhour_data[] = array(
													'start_time'       => $service_tmp_current_time,
													'end_time'         => $staffmember_current_time,
													'break_start_time' => $break_start_time,
													'break_end_time'   => $break_end_time,
													'is_booked'        => $is_already_booked,
												);
											}
										}else{
											if($staffmember_current_time >= $staffmember_end_time){
												break;
											}
										}
									}
			
									if (! empty($break_end_time) ) {
										$staffmember_current_time = $break_end_time;
									}
			
									if ($staffmember_current_time == $staffmember_end_time ) {
										break;
									}
								}
							}
						}

						if(!empty($default_timeslot_step) && $default_timeslot_step != $service_step_duration_val && empty($break_start_time)){
							$service_tmp_time_obj = new DateTime($service_tmp_current_time);
							$service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
							$service_current_time = $service_tmp_time_obj->format('H:i');
						}
	
						$service_timings = $workhour_data;
					}else{
						// If staff member workhour not enable then service workhour applied
						if($bookingpress_is_service_specific_workhours == "true"){
							$bookingpress_hide_already_booked_slot = $BookingPress->bookingpress_get_customize_settings( 'hide_already_booked_slot', 'booking_form' );
							$bookingpress_hide_already_booked_slot = ( $bookingpress_hide_already_booked_slot == 'true' ) ? 1 : 0;

							$current_day  = ! empty( $selected_date ) ? ucfirst( date( 'l', strtotime( $selected_date ) ) ) : ucfirst( date( 'l', current_time( 'timestamp' ) ) );
							$current_date = ! empty($selected_date) ? date('Y-m-d', strtotime($selected_date)) : date('Y-m-d', current_time('timestamp'));

							$bpa_current_date = date('Y-m-d', strtotime(current_time('mysql')));

							if(!empty($bookingpress_timezone) && !empty($bookingpress_timeslot_display_in_client_timezone) && ($bookingpress_timeslot_display_in_client_timezone == 'true')){
								$bpa_current_time = date('H:i');
							}else{
								$bpa_current_time = date( 'H:i',strtotime(current_time('mysql')));
							}

							$service_time_duration     = $BookingPress->bookingpress_get_default_timeslot_data();
							$service_step_duration_val = $service_time_duration['default_timeslot'];
							if (! empty($selected_service_id) ) {
								// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
								$service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $selected_service_id), ARRAY_A);
								if (! empty($service_data) ) {
									$service_time_duration      = esc_html($service_data['bookingpress_service_duration_val']);
									$service_time_duration_unit = esc_html($service_data['bookingpress_service_duration_unit']);
									if ($service_time_duration_unit == 'h' ) {
										$service_time_duration = $service_time_duration * 60;
									}
									$service_step_duration_val = $service_time_duration;
								}
							}

							$bookingpress_show_time_as_per_service_duration = $BookingPress->bookingpress_get_settings( 'show_time_as_per_service_duration', 'general_setting' );

							if ( ! empty( $bookingpress_show_time_as_per_service_duration ) && $bookingpress_show_time_as_per_service_duration == 'false' ) {
								$bookingpress_default_time_slot = $BookingPress->bookingpress_get_settings( 'default_time_slot', 'general_setting' );
								$default_timeslot_step      = $bookingpress_default_time_slot;
								$time_unit                      = 'm';
								if ( $service_time_duration >= 60 ) {
									$default_timeslot_step = ( $service_time_duration / 60 );
									$time_unit                 = 'h';
								}
							}
							
							$bpa_fetch_updated_slots = false;
							if( isset( $_POST['bpa_fetch_data'] ) && 'true' == $_POST['bpa_fetch_data'] ){ //phpcs:ignore
								$bpa_fetch_updated_slots = true;
							}
							$service_step_duration_val = apply_filters( 'bookingpress_modify_service_timeslot', $service_step_duration_val, $selected_service_id, $service_time_duration_unit, $bpa_fetch_updated_slots );

							$already_booked_time_arr = $workhour_data = $break_hour_arr = array();

							$bookingpress_special_day_details = $BookingPressPro->bookingpress_get_service_special_days($selected_service_id, $current_date);
							$bookingpress_service_default_workhours = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = 0 AND bookingpress_service_workday_key = %s", $selected_service_id, $current_day), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is a table name. false alarm

							if(!empty($bookingpress_special_day_details) || !empty($bookingpress_service_default_workhours)){
								if(!empty($bookingpress_special_day_details)){
									$service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_special_day_details['special_day_start_time'])), $selected_service_id );
									$service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_special_day_details['special_day_end_time'])), $selected_service_id );
								}else{
									$service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_service_default_workhours['bookingpress_service_workhours_start_time'])), $selected_service_id );
									$service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_service_default_workhours['bookingpress_service_workhours_end_time'])), $selected_service_id );
								}

								if ($service_start_time != null && $service_end_time != null ) {
									while ( $service_current_time <= $service_end_time ) {
										if ($service_current_time > $service_end_time ) {
											break;
										}

										$service_tmp_current_time = $service_current_time;

										if ($service_current_time == '00:00' ) {
											$service_current_time = date('H:i', strtotime($service_current_time) + ( $service_step_duration_val * 60 ));
										} else {
											$service_tmp_time_obj = new DateTime($service_current_time);
											$service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
											$service_current_time = $service_tmp_time_obj->format('H:i');
										}

										$break_start_time      = '';
										$break_end_time        = '';
										if(!empty($bookingpress_special_day_details)){
											$bookingpress_special_day_break_data = !empty($bookingpress_special_day_details['special_day_breaks']) ? $bookingpress_special_day_details['special_day_breaks'] : array();
											if(!empty($bookingpress_special_day_break_data) && is_array($bookingpress_special_day_break_data)){
												foreach($bookingpress_special_day_break_data as $k2 => $v2){
													if($v2['break_start_time'] >= $service_tmp_current_time && $v2['break_start_time'] <= $service_current_time){
														$break_start_time = $v2['break_start_time'];
														$break_end_time = $v2['break_end_time'];
														$service_current_time = $break_start_time;
													}
												}
											}
										}else{
											// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
											$check_break_existance = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_service_workhours_id) as total FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workday_key = %s AND bookingpress_service_workhours_is_break = 1 AND (bookingpress_service_workhours_start_time BETWEEN %s AND %s)", $selected_service_id, $current_day, $service_tmp_current_time, $service_current_time));

											if ($check_break_existance > 0 ) {
												// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
												$get_break_workhours = $wpdb->get_row($wpdb->prepare("SELECT TIMEDIFF(bookingpress_service_workhours_end_time, bookingpress_service_workhours_start_time) as time_diff, bookingpress_service_workhours_start_time, bookingpress_service_workhours_end_time FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workday_key = %s AND bookingpress_service_workhours_is_break = 1 AND (bookingpress_service_workhours_start_time BETWEEN %s AND %s )", $selected_service_id, $current_day, $service_tmp_current_time, $service_current_time), ARRAY_A);
												$time_difference     = date('H:i', strtotime($get_break_workhours['time_diff']));

												$break_start_time     = date('H:i', strtotime($get_break_workhours['bookingpress_service_workhours_start_time']));
												$break_end_time       = date('H:i', strtotime($get_break_workhours['bookingpress_service_workhours_end_time']));
												$service_current_time = $break_start_time;
											}
										}

										$is_appointment_booked = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $current_date, $service_tmp_current_time, $service_current_time);
										$is_already_booked = ( $is_appointment_booked > 0 ) ? 1 : 0;
										if ($is_already_booked == 1 ) {
											$bookingpress_is_cancelled = $BookingPress->bookingpress_is_appointment_cancelled_or_rejected($selected_service_id, $current_date, $service_tmp_current_time, $service_current_time);
											if($bookingpress_is_cancelled){
												$is_already_booked = 0;
											}
										}

										if ($service_current_time < $service_start_time || $service_current_time == $service_start_time ) {
											$service_current_time = $service_end_time;
										}

										$bookingpress_timediff_in_minutes = round(abs(strtotime($service_current_time) - strtotime($service_tmp_current_time)) / 60, 2);

										if ($is_already_booked == 1 && $bookingpress_hide_already_booked_slot == 1 ) {
											continue;
										} else {
											if ($break_start_time != $service_tmp_current_time && $bookingpress_timediff_in_minutes >= $service_step_duration_val && $service_current_time <= $service_end_time ) {
												if ($bpa_current_date == $current_date ) {
													if ($service_tmp_current_time > $bpa_current_time ) {
														$workhour_data[] = array(
															'start_time' => $service_tmp_current_time,
															'end_time'   => $service_current_time,
															'break_start_time' => $break_start_time,
															'break_end_time' => $break_end_time,
															'is_booked'  => $is_already_booked,
														);
													} else {
														/* $workhour_data[] = array(
															'start_time' => $service_tmp_current_time,
															'end_time'   => $service_current_time,
															'break_start_time' => $break_start_time,
															'break_end_time' => $break_end_time,
															'is_booked'  => 1,
														); */
													}
												} else {
													$workhour_data[] = array(
														'start_time'       => $service_tmp_current_time,
														'end_time'         => $service_current_time,
														'break_start_time' => $break_start_time,
														'break_end_time'   => $break_end_time,
														'is_booked'        => $is_already_booked,
													);
												}
											}else{
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

										if($default_timeslot_step != $service_step_duration_val && empty($break_start_time)){
											$service_tmp_time_obj = new DateTime($service_tmp_current_time);
											$service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
											$service_current_time = $service_tmp_time_obj->format('H:i');
										}
									}
								}
							}

							$service_timings = $workhour_data;
						}

					}
				}else{
					// If staff member workhour not enable then service workhour applied
					if($bookingpress_is_service_specific_workhours == "true"){
						$bookingpress_hide_already_booked_slot = $BookingPress->bookingpress_get_customize_settings( 'hide_already_booked_slot', 'booking_form' );
						$bookingpress_hide_already_booked_slot = ( $bookingpress_hide_already_booked_slot == 'true' ) ? 1 : 0;

						$current_day  = ! empty( $selected_date ) ? ucfirst( date( 'l', strtotime( $selected_date ) ) ) : ucfirst( date( 'l', current_time( 'timestamp' ) ) );
						$current_date = ! empty($selected_date) ? date('Y-m-d', strtotime($selected_date)) : date('Y-m-d', current_time('timestamp'));

						$bpa_current_date = date('Y-m-d', strtotime(current_time('mysql')));

						if(!empty($bookingpress_timezone) && !empty($bookingpress_timeslot_display_in_client_timezone) && ($bookingpress_timeslot_display_in_client_timezone == 'true')){
							$bpa_current_time = date('H:i');
						}else{
							$bpa_current_time = date( 'H:i',strtotime(current_time('mysql')));
						}

						$service_time_duration     = $BookingPress->bookingpress_get_default_timeslot_data();
						$service_step_duration_val = $service_time_duration['default_timeslot'];
						if (! empty($selected_service_id) ) {
							// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
							$service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $selected_service_id), ARRAY_A);
							if (! empty($service_data) ) {
								$service_time_duration      = esc_html($service_data['bookingpress_service_duration_val']);
								$service_time_duration_unit = esc_html($service_data['bookingpress_service_duration_unit']);
								if ($service_time_duration_unit == 'h' ) {
									$service_time_duration = $service_time_duration * 60;
								}
								$service_step_duration_val = $service_time_duration;
							}
						}

						$bookingpress_show_time_as_per_service_duration = $BookingPress->bookingpress_get_settings( 'show_time_as_per_service_duration', 'general_setting' );

						if ( ! empty( $bookingpress_show_time_as_per_service_duration ) && $bookingpress_show_time_as_per_service_duration == 'false' ) {
							$bookingpress_default_time_slot = $BookingPress->bookingpress_get_settings( 'default_time_slot', 'general_setting' );
							$default_timeslot_step      = $bookingpress_default_time_slot;
							$time_unit                      = 'm';
							if ( $service_time_duration >= 60 ) {
								$default_timeslot_step = ( $service_time_duration / 60 );
								$time_unit                 = 'h';
							}
						}
						
						$bpa_fetch_updated_slots = false;
						if( isset( $_POST['bpa_fetch_data'] ) && 'true' == $_POST['bpa_fetch_data'] ){ //phpcs:ignore
							$bpa_fetch_updated_slots = true;
						}
						$service_step_duration_val = apply_filters( 'bookingpress_modify_service_timeslot', $service_step_duration_val, $selected_service_id, $service_time_duration_unit, $bpa_fetch_updated_slots );

						$already_booked_time_arr = $workhour_data = $break_hour_arr = array();

						$bookingpress_special_day_details = $BookingPressPro->bookingpress_get_service_special_days($selected_service_id, $current_date);
						$bookingpress_service_default_workhours = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = 0 AND bookingpress_service_workday_key = %s", $selected_service_id, $current_day), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is a table name. false alarm

						if(!empty($bookingpress_special_day_details) || !empty($bookingpress_service_default_workhours)){
							if(!empty($bookingpress_special_day_details)){
								$service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_special_day_details['special_day_start_time'])), $selected_service_id );
								$service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_special_day_details['special_day_end_time'])), $selected_service_id );
							}else{
								$service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_service_default_workhours['bookingpress_service_workhours_start_time'])), $selected_service_id );
								$service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_service_default_workhours['bookingpress_service_workhours_end_time'])), $selected_service_id );
							}

							if ($service_start_time != null && $service_end_time != null ) {
								while ( $service_current_time <= $service_end_time ) {
									if ($service_current_time > $service_end_time ) {
										break;
									}

									$service_tmp_current_time = $service_current_time;

									if ($service_current_time == '00:00' ) {
										$service_current_time = date('H:i', strtotime($service_current_time) + ( $service_step_duration_val * 60 ));
									} else {
										$service_tmp_time_obj = new DateTime($service_current_time);
										$service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
										$service_current_time = $service_tmp_time_obj->format('H:i');
									}

									$break_start_time      = '';
									$break_end_time        = '';
									if(!empty($bookingpress_special_day_details)){
										$bookingpress_special_day_break_data = !empty($bookingpress_special_day_details['special_day_breaks']) ? $bookingpress_special_day_details['special_day_breaks'] : array();
										if(!empty($bookingpress_special_day_break_data) && is_array($bookingpress_special_day_break_data)){
											foreach($bookingpress_special_day_break_data as $k2 => $v2){
												if($v2['break_start_time'] >= $service_tmp_current_time && $v2['break_start_time'] <= $service_current_time){
													$break_start_time = $v2['break_start_time'];
													$break_end_time = $v2['break_end_time'];
													$service_current_time = $break_start_time;
												}
											}
										}
									}else{
										// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
										$check_break_existance = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_service_workhours_id) as total FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workday_key = %s AND bookingpress_service_workhours_is_break = 1 AND (bookingpress_service_workhours_start_time BETWEEN %s AND %s)", $selected_service_id, $current_day, $service_tmp_current_time, $service_current_time));

										if ($check_break_existance > 0 ) {
											// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
											$get_break_workhours = $wpdb->get_row($wpdb->prepare("SELECT TIMEDIFF(bookingpress_service_workhours_end_time, bookingpress_service_workhours_start_time) as time_diff, bookingpress_service_workhours_start_time, bookingpress_service_workhours_end_time FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workday_key = %s AND bookingpress_service_workhours_is_break = 1 AND (bookingpress_service_workhours_start_time BETWEEN %s AND %s )", $selected_service_id, $current_day, $service_tmp_current_time, $service_current_time), ARRAY_A);
											$time_difference     = date('H:i', strtotime($get_break_workhours['time_diff']));

											$break_start_time     = date('H:i', strtotime($get_break_workhours['bookingpress_service_workhours_start_time']));
											$break_end_time       = date('H:i', strtotime($get_break_workhours['bookingpress_service_workhours_end_time']));
											$service_current_time = $break_start_time;
										}
									}

									$is_appointment_booked = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $current_date, $service_tmp_current_time, $service_current_time);
									$is_already_booked = ( $is_appointment_booked > 0 ) ? 1 : 0;
									if ($is_already_booked == 1 ) {
										$bookingpress_is_cancelled = $BookingPress->bookingpress_is_appointment_cancelled_or_rejected($selected_service_id, $current_date, $service_tmp_current_time, $service_current_time);
										if($bookingpress_is_cancelled){
											$is_already_booked = 0;
										}
									}

									if ($service_current_time < $service_start_time || $service_current_time == $service_start_time ) {
										$service_current_time = $service_end_time;
									}

									$bookingpress_timediff_in_minutes = round(abs(strtotime($service_current_time) - strtotime($service_tmp_current_time)) / 60, 2);

									if ($is_already_booked == 1 && $bookingpress_hide_already_booked_slot == 1 ) {
										continue;
									} else {
										if ($break_start_time != $service_tmp_current_time && $bookingpress_timediff_in_minutes >= $service_step_duration_val && $service_current_time <= $service_end_time ) {
											if ($bpa_current_date == $current_date ) {
												if ($service_tmp_current_time > $bpa_current_time ) {
													$workhour_data[] = array(
														'start_time' => $service_tmp_current_time,
														'end_time'   => $service_current_time,
														'break_start_time' => $break_start_time,
														'break_end_time' => $break_end_time,
														'is_booked'  => $is_already_booked,
													);
												} else {
													/* $workhour_data[] = array(
														'start_time' => $service_tmp_current_time,
														'end_time'   => $service_current_time,
														'break_start_time' => $break_start_time,
														'break_end_time' => $break_end_time,
														'is_booked'  => 1,
													); */
												}
											} else {
												$workhour_data[] = array(
													'start_time'       => $service_tmp_current_time,
													'end_time'         => $service_current_time,
													'break_start_time' => $break_start_time,
													'break_end_time'   => $break_end_time,
													'is_booked'        => $is_already_booked,
												);
											}
										}else{
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

									if($default_timeslot_step != $service_step_duration_val && empty($break_start_time)){
										$service_tmp_time_obj = new DateTime($service_tmp_current_time);
										$service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
										$service_current_time = $service_tmp_time_obj->format('H:i');
									}
								}
							}
						}

						$service_timings = $workhour_data;
					}

				}
			}else{
				
				if($bookingpress_is_service_specific_workhours == "true"){

					$bookingpress_hide_already_booked_slot = $BookingPress->bookingpress_get_customize_settings( 'hide_already_booked_slot', 'booking_form' );
					$bookingpress_hide_already_booked_slot = ( $bookingpress_hide_already_booked_slot == 'true' ) ? 1 : 0;
					$bookingpress_hide_already_booked_slot = apply_filters( 'bookingpress_change_hide_already_booked_slot_for_service', $bookingpress_hide_already_booked_slot, $selected_service_id);

					$current_day  = ! empty( $selected_date ) ? ucfirst( date( 'l', strtotime( $selected_date ) ) ) : ucfirst( date( 'l', current_time( 'timestamp' ) ) );
					$current_date = ! empty($selected_date) ? date('Y-m-d', strtotime($selected_date)) : date('Y-m-d', current_time('timestamp'));

					$bpa_current_date = date('Y-m-d', strtotime(current_time('mysql')));

					if(!empty($bookingpress_timezone) && !empty($bookingpress_timeslot_display_in_client_timezone) && ($bookingpress_timeslot_display_in_client_timezone == 'true')){
						$bpa_current_time = date('H:i');
					}else{
						$bpa_current_time = date( 'H:i',strtotime(current_time('mysql')));
					}
					
					$service_time_duration     = $BookingPress->bookingpress_get_default_timeslot_data();
					$service_step_duration_val = $service_time_duration['default_timeslot'];
					if (! empty($selected_service_id) ) {
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
						$service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $selected_service_id), ARRAY_A);
						if (! empty($service_data) ) {
							$service_time_duration      = esc_html($service_data['bookingpress_service_duration_val']);
							$service_time_duration_unit = esc_html($service_data['bookingpress_service_duration_unit']);
							if ($service_time_duration_unit == 'h' ) {
								$service_time_duration = $service_time_duration * 60;
							}
							$service_step_duration_val = $service_time_duration;
						}
					}

					$bookingpress_show_time_as_per_service_duration = $BookingPress->bookingpress_get_settings( 'show_time_as_per_service_duration', 'general_setting' );
					if ( ! empty( $bookingpress_show_time_as_per_service_duration ) && $bookingpress_show_time_as_per_service_duration == 'false' ) {
						$time_unit                      = 'm';
						if ( $service_time_duration >= 60 ) {
							$default_timeslot_step = ( $service_time_duration / 60 );
							$time_unit                 = 'h';
						}
					}

					$bpa_fetch_updated_slots = false;
					if( isset( $_POST['bpa_fetch_data'] ) && 'true' == $_POST['bpa_fetch_data'] ){ //phpcs:ignore
						$bpa_fetch_updated_slots = true;
					}
					
					$service_step_duration_val = apply_filters( 'bookingpress_modify_service_timeslot', $service_step_duration_val, $selected_service_id, $service_time_duration_unit, $bpa_fetch_updated_slots );

					$already_booked_time_arr = $workhour_data = $break_hour_arr = array();

					$bookingpress_special_day_details = $BookingPressPro->bookingpress_get_service_special_days($selected_service_id, $current_date);
					$bookingpress_service_default_workhours = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workhours_is_break = 0 AND bookingpress_service_workday_key = %s", $selected_service_id, $current_day), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is a table name. false alarm

					if(!empty($bookingpress_special_day_details) || !empty($bookingpress_service_default_workhours)){
						if(!empty($bookingpress_special_day_details)){
							$service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_special_day_details['special_day_start_time'])), $selected_service_id );
							$service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_special_day_details['special_day_end_time'])), $selected_service_id );
						}else{
							$service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_service_default_workhours['bookingpress_service_workhours_start_time'])), $selected_service_id );
							$service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_service_default_workhours['bookingpress_service_workhours_end_time'])), $selected_service_id );
						}

						if ($service_start_time != null && $service_end_time != null ) {
							while ( $service_current_time <= $service_end_time ) {
								if ($service_current_time > $service_end_time ) {
									break;
								}
		
								$service_tmp_current_time = $service_current_time;
		
								if ($service_current_time == '00:00' ) {
									$service_current_time = date('H:i', strtotime($service_current_time) + ( $service_step_duration_val * 60 ));
								} else {
									$service_tmp_time_obj = new DateTime($service_current_time);
									$service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
									$service_current_time = $service_tmp_time_obj->format('H:i');
								}
		
								$break_start_time      = '';
								$break_end_time        = '';
								if(!empty($bookingpress_special_day_details)){
									$bookingpress_special_day_break_data = !empty($bookingpress_special_day_details['special_day_breaks']) ? $bookingpress_special_day_details['special_day_breaks'] : array();
									if(!empty($bookingpress_special_day_break_data) && is_array($bookingpress_special_day_break_data)){
										foreach($bookingpress_special_day_break_data as $k2 => $v2){
											if($v2['break_start_time'] >= $service_tmp_current_time && $v2['break_start_time'] <= $service_current_time){
												$break_start_time = $v2['break_start_time'];
												$break_end_time = $v2['break_end_time'];
												$service_current_time = $break_start_time;
											}
										}
									}
								}else{
									// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
									$check_break_existance = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_service_workhours_id) as total FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workday_key = %s AND bookingpress_service_workhours_is_break = 1 AND (bookingpress_service_workhours_start_time BETWEEN %s AND %s)", $selected_service_id, $current_day, $service_tmp_current_time, $service_current_time));
			
									if ($check_break_existance > 0 ) {
										// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is table name defined globally. False Positive alarm
										$get_break_workhours = $wpdb->get_row($wpdb->prepare("SELECT TIMEDIFF(bookingpress_service_workhours_end_time, bookingpress_service_workhours_start_time) as time_diff, bookingpress_service_workhours_start_time, bookingpress_service_workhours_end_time FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_id = %d AND bookingpress_service_workday_key = %s AND bookingpress_service_workhours_is_break = 1 AND (bookingpress_service_workhours_start_time BETWEEN %s AND %s )", $selected_service_id, $current_day, $service_tmp_current_time, $service_current_time), ARRAY_A);
										$time_difference     = date('H:i', strtotime($get_break_workhours['time_diff']));
			
										$break_start_time     = date('H:i', strtotime($get_break_workhours['bookingpress_service_workhours_start_time']));
										$break_end_time       = date('H:i', strtotime($get_break_workhours['bookingpress_service_workhours_end_time']));
										$service_current_time = $break_start_time;
									}
								}
		
								$is_appointment_booked = $BookingPress->bookingpress_is_appointment_booked($selected_service_id, $current_date, $service_tmp_current_time, $service_current_time);
								$is_already_booked = ( $is_appointment_booked > 0 ) ? 1 : 0;
								if ($is_already_booked == 1 ) {
									$bookingpress_is_cancelled = $BookingPress->bookingpress_is_appointment_cancelled_or_rejected($selected_service_id, $current_date, $service_tmp_current_time, $service_current_time);
									if($bookingpress_is_cancelled){
										$is_already_booked = 0;
									}
								}
		
								if ($service_current_time < $service_start_time || $service_current_time == $service_start_time ) {
									$service_current_time = $service_end_time;
								}

								$bookingpress_timediff_in_minutes = round(abs(strtotime($service_current_time) - strtotime($service_tmp_current_time)) / 60, 2);
		
								if ($is_already_booked == 1 && $bookingpress_hide_already_booked_slot == 1 ) {
									continue;
								} else {
									if ($break_start_time != $service_tmp_current_time && $bookingpress_timediff_in_minutes >= $service_step_duration_val && $service_current_time <= $service_end_time ) {
										if ($bpa_current_date == $current_date ) {
											if ($service_tmp_current_time > $bpa_current_time ) {
												$workhour_data[] = array(
													'start_time' => $service_tmp_current_time,
													'end_time'   => $service_current_time,
													'break_start_time' => $break_start_time,
													'break_end_time' => $break_end_time,
													'is_booked'  => $is_already_booked,
												);
											} else {
												/* $workhour_data[] = array(
													'start_time' => $service_tmp_current_time,
													'end_time'   => $service_current_time,
													'break_start_time' => $break_start_time,
													'break_end_time' => $break_end_time,
													'is_booked'  => 1,
												); */
											}
										} else {
											$workhour_data[] = array(
												'start_time'       => $service_tmp_current_time,
												'end_time'         => $service_current_time,
												'break_start_time' => $break_start_time,
												'break_end_time'   => $break_end_time,
												'is_booked'        => $is_already_booked,
											);
										}
									}else{
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
								
								if($default_timeslot_step != $service_step_duration_val && empty($break_start_time)){
									$service_tmp_time_obj = new DateTime($service_tmp_current_time);
									
									$service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
									$service_current_time = $service_tmp_time_obj->format('H:i');
								}
							}
						}
					}

					$service_timings = $workhour_data;
				}
			}
			
			return $service_timings;
		}

		
		/**
		 * Function for add dynamic data to get timings request at frontend booking form shortcode
		 *
		 * @param  mixed $bookingpress_dynamic_add_params_for_timeslot_request
		 * @return void
		 */
		function bookingpress_dynamic_add_params_for_timeslot_request_method( $bookingpress_dynamic_add_params_for_timeslot_request ) {
			$bookingpress_dynamic_add_params_for_timeslot_request .= 'postData.bookingpress_timezone = vm.bookingpress_timezone;';
			$bookingpress_dynamic_add_params_for_timeslot_request .= 'postData.bookingpress_selected_staffmember = vm.appointment_step_form_data.bookingpress_selected_staff_member_details;';

			$bookingpress_dynamic_add_params_for_timeslot_request .= 'if( vm.appointment_step_form_data.selected_service_duration_unit == "d" ){
				let elem = document.querySelector(".bpa-front-module--date-and-time.__sm");
				let computed_style = getComputedStyle( elem );
				
				if( "none" != computed_style.display && "undefined" != typeof postData && "bookingpress_front_get_timings" == postData.action && "datetime" == vm.bookingpress_current_tab ){
					vm.bookingpress_step_navigation(vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].next_tab_name, vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].next_tab_name, vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].previous_tab_name)
				}
			}';

			return $bookingpress_dynamic_add_params_for_timeslot_request;
		}
		
		/**
		 * Function for get service id from appointment id
		 *
		 * @return void
		 */
		function bookingpress_my_appointment_get_service_id_from_appointment_id_func($return_data = false) {
			global $wpdb ,$tbl_bookingpress_appointment_bookings, $bookingpress_other_debug_log_id,$tbl_bookingpress_services;

			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Posted data for get service from appointment for reschedule', 'bookingpress_mybookings', $_REQUEST, $bookingpress_other_debug_log_id );

			$service_id_wpnonce = isset( $_REQUEST['get_service_id_data_nonce'] ) ? sanitize_text_field( $_REQUEST['get_service_id_data_nonce'] ) : '';

			$bpa_service_id_nonce_flag = wp_verify_nonce( $service_id_wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_service_id_nonce_flag ) {
				$response['variant']      = 'error';
				$response['title']        = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']          = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				$response['redirect_url'] = '';
				if($return_data){
					return $response;
				}
				echo wp_json_encode( $response );
				die();
			}
			$reschedule_appointment_id = isset( $_REQUEST['appointment_id'] ) ? sanitize_text_field( $_REQUEST['appointment_id'] ) : '';
			if ( isset( $reschedule_appointment_id ) ) {
				$bookingpress_appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_booking_id,bookingpress_service_id, bookingpress_appointment_time, bookingpress_appointment_date, bookingpress_staff_member_id,bookingpress_appointment_end_time, bookingpress_extra_service_details,bookingpress_selected_extra_members FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $reschedule_appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				$service_id = !empty($bookingpress_appointment_data['bookingpress_service_id']) ? intval($bookingpress_appointment_data['bookingpress_service_id']) : 0;
				$staff_member_id = !empty($bookingpress_appointment_data['bookingpress_staff_member_id']) ? intval($bookingpress_appointment_data['bookingpress_staff_member_id']) : 0;
				$bookingpress_appointment_date = !empty( $bookingpress_appointment_data['bookingpress_appointment_date'] ) ? sanitize_text_field( $bookingpress_appointment_data['bookingpress_appointment_date'] ) : date('Y-m-d', current_time('timestamp') );
				$bookingpress_appointment_time = !empty( $bookingpress_appointment_data['bookingpress_appointment_time'] ) ? sanitize_text_field( $bookingpress_appointment_data['bookingpress_appointment_time'] ) : '';
				$bookingpress_appointment_end_time = !empty( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) ? sanitize_text_field( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) : '';

				$bookingpress_extra_service_details = !empty( $bookingpress_appointment_data['bookingpress_extra_service_details'] ) ? json_decode( $bookingpress_appointment_data['bookingpress_extra_service_details'] ) : array();

				$bookingpress_service_bawy = !empty( $bookingpress_appointment_data['bookingpress_selected_extra_members'] ) ? intval( $bookingpress_appointment_data['bookingpress_selected_extra_members'] ) : 0;

				$bookingpress_service_expiration_date = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_expiration_date FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				/* Service start date */
				$bookingpress_service_start_date = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_start_date FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

				if ( isset( $service_id ) ) {
					$response['variant']    = 'success';
					$response['title']      = '';
					$response['msg']        = '';
					$response['error_type'] = '';
					$response['service_id'] = $service_id;
					$response['staff_id'] = $staff_member_id;
					$response['appointment_date'] = $bookingpress_appointment_date;
					$response['appointment_time'] = date('H:i', strtotime( $bookingpress_appointment_time ) );
					$response['appointment_end_time'] = date('H:i', strtotime( $bookingpress_appointment_end_time ) );
					$response['appointment_update_id'] = intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']);
					$response['bookingpress_service_expiration_date'] = !empty($bookingpress_service_expiration_date) ? esc_html($bookingpress_service_expiration_date) : '';
					$response['bookingpress_service_start_date'] = !empty($bookingpress_service_start_date) ? esc_html($bookingpress_service_start_date) : '';

					if( !empty( $bookingpress_extra_service_details ) ){
						$service_extras_data = array();
						foreach( $bookingpress_extra_service_details as $service_extras ){
							if( true == $service_extras->bookingpress_is_selected ){
								$service_extras_details = $service_extras->bookingpress_extra_service_details;
								$selected_service_extra_id = $service_extras_details->bookingpress_extra_services_id;
								$selected_service_extra_qty = $service_extras->bookingpress_selected_qty;
								$service_extras_data[ $selected_service_extra_id ]['bookingpress_is_selected'] = true;
								$service_extras_data[ $selected_service_extra_id ]['bookingpress_selected_qty'] = $selected_service_extra_qty;
							}
						}
						if( !empty( $service_extras_data ) ){
							$response['bookingpress_selected_extra_details'] = $service_extras_data;
						}
					}

					if( !empty( $bookingpress_service_bawy ) ){
						$response['bookingpress_selected_bring_members'] = $bookingpress_service_bawy;
					}

					$response = apply_filters( 'bookingpress_my_appointment_modify_data_for_rescheduling', $response, $reschedule_appointment_id );
					if($return_data){
						return $response;
					}					
					echo wp_json_encode( $response );
					wp_die();
				}
			}
		}
		
		/**
		 * Function for delete logs data
		 *
		 * @param  mixed $customer_data
		 * @param  mixed $posted_data
		 * @return void
		 */
		function bookingpress_delete_customer_log_func($customer_data,$posted_data) {			
			global $bookingpress_other_debug_log_id;
			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Posted data for delete customer account', 'bookingpress_mybookings', $posted_data, $bookingpress_other_debug_log_id );

			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Customer account delete data', 'bookingpress_mybookings', $bookingpress_customer_rows, $customer_data );
		}

		/**
		 * Callback function of reschedule appointment
		 *
		 * @return void
		 */
		function bookingpress_reschedule_book_appointment_func($return_data = false,$user_id = "",$reachedule_token = "") {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs,$tbl_bookingpress_customers,$bookingpress_email_notifications, $bookingpress_other_debug_log_id, $tbl_bookingpress_reschedule_history,$tbl_bookingpress_appointment_meta, $bookingpress_services;

			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Posted data for reschedule appointment', 'bookingpress_mybookings', $_REQUEST, $bookingpress_other_debug_log_id );

			$response              = array();
			$wpnonce               = isset( $_REQUEST['reschedule_save_wpnonce'] ) ? sanitize_text_field( $_REQUEST['reschedule_save_wpnonce'] ) : '';
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant']      = 'error';
				$response['title']        = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']          = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				$response['redirect_url'] = '';
				if($return_data){
					return $response;
				}
				echo wp_json_encode( $response );
				die();
			}

			// Reschedule appointment
			$reschedule_id             = !empty( $_REQUEST['resche_apt_id'] ) ? intval( $_REQUEST['resche_apt_id'] ) : 0;	
			$appointment_log_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $reschedule_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			$bpa_is_valid_user = $this->bookingpress_check_user_connection_with_appointment( $reschedule_id,$user_id );

			if( false == $bpa_is_valid_user && empty($reachedule_token)){
				$response['variant'] 	= 'error';
				$response['title']   	= esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']    	= esc_html__( 'Sorry, You are not allowed to reschedule this appointment', 'bookingpress-appointment-booking' );
				$reschedule_appointment_data = array(
					'resch_appointment_id' => $reschedule_id,
					'current_logged_in_user' => get_current_user_id()
				);
				do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Restricted rescheduling due to logged in user is not associated with the appointment', 'bookingpress_mybookings', $reschedule_appointment_data, $bookingpress_other_debug_log_id );
				$response['redirect_url'] = '';
				if($return_data){
					return $response;
				}				
				echo wp_json_encode( $response );
				die;
			}

			$allow_customer_reschedule_apt = $BookingPress->bookingpress_get_customize_settings( 'allow_customer_reschedule_apt', 'booking_my_booking' );
			
			if( 'false' == $allow_customer_reschedule_apt ){
				$response['variant'] 	= 'error';
				$response['title']   	= esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']    	= esc_html__( 'Sorry, You are not allowed to reschedule appointments', 'bookingpress-appointment-booking' );
				$reschedule_appointment_data = array(
					'resch_appointment_id' => $reschedule_id,
					'current_logged_in_user' => get_current_user_id()
				);
				do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Restricted rescheduling in respect to customization option', 'bookingpress_mybookings', $reschedule_appointment_data, $bookingpress_other_debug_log_id );
				$response['redirect_url'] = '';
				if($return_data){
					return $response;
				}				
				echo wp_json_encode( $response );
				die;
			}

			$appointment_service_id    = !empty( $_POST['resche_service_id'] ) ? intval( $_POST['resche_service_id'] ) : 0;

			$bookingpress_min_time_before_reschedule = $BookingPress->bookingpress_get_settings('default_minimum_time_befor_rescheduling', 'general_setting');
			$bookingpress_service_min_time_require_before_reschedule = $bookingpress_services->bookingpress_get_service_meta($appointment_service_id, 'minimum_time_required_before_rescheduling');
			if($bookingpress_service_min_time_require_before_reschedule == 'disabled'){
				$bookingpress_min_time_before_reschedule = 'disabled';
			}else if($bookingpress_service_min_time_require_before_reschedule != 'inherit'){
				$bookingpress_min_time_before_reschedule = $bookingpress_service_min_time_require_before_reschedule;
			}

			$allow_rescheduling = true;
			if( $bookingpress_min_time_before_reschedule != 'disabled'){
				$bookingpress_from_time = current_time('timestamp');
				$bookingpress_to_time = strtotime($appointment_log_data['bookingpress_appointment_date'] .' '. $appointment_log_data['bookingpress_appointment_time']);
				$bookingpress_time_diff_for_cancel = round(abs($bookingpress_to_time - $bookingpress_from_time) / 60, 2);

				if($bookingpress_time_diff_for_cancel < $bookingpress_min_time_before_reschedule){
					$allow_rescheduling = false;
				} else if( $bookingpress_from_time > $bookingpress_to_time ){
					$allow_rescheduling = false;
				}
			}

			$bookingpress_booking_timestamp = strtotime( $appointment_log_data['bookingpress_appointment_date'] . ' ' . $appointment_log_data['bookingpress_appointment_time'] );
			$is_past_time = current_time('timestamp') > $bookingpress_booking_timestamp;
			if( 1 == $is_past_time ) {
				$response['variant'] 	= 'error';
				$response['title']   	= esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']    	= esc_html__( 'Sorry, Appointment can not be rescheduled as the original time has been passed.', 'bookingpress-appointment-booking' );
				$reschedule_appointment_data = array(
					'resch_appointment_id' => $reschedule_id,
					'current_logged_in_user' => get_current_user_id(),
					'is_past_time' => $is_past_time
				);
				do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Reschedule selected time is already passed', 'bookingpress_mybookings', $reschedule_appointment_data, $bookingpress_other_debug_log_id );
				$response['redirect_url'] = '';
				if($return_data){
					return $response;
				}				
				echo wp_json_encode( $response );
				die;
			}

			if( false == $allow_rescheduling ){
				$response['variant'] 	= 'error';
				$response['title']   	= esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']    	= esc_html__( 'Sorry, minimum time for rescheduling appointment has been passed.', 'bookingpress-appointment-booking' );
				$reschedule_appointment_data = array(
					'resch_appointment_id' => $reschedule_id,
					'current_logged_in_user' => get_current_user_id()
				);
				do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Restricted rescheduling as minimum time required before rescheduling is over', 'bookingpress_mybookings', $reschedule_appointment_data, $bookingpress_other_debug_log_id );
				$response['redirect_url'] = '';
				if($return_data){
					return $response;
				}				
				echo wp_json_encode( $response );
				die;
			}

			$appointment_staff_id      = !empty( $_POST['resche_staff_id'] ) ? intval( $_POST['resche_staff_id'] ) : 0;
			$appointment_selected_date = !empty( $_POST['resche_date'] ) ? date( 'Y-m-d', strtotime( sanitize_text_field( $_POST['resche_date'] ) ) ) : '';
			$appointment_start_time    = !empty( $_POST['resche_time'] ) ? date( 'H:i:s', strtotime( sanitize_text_field( $_POST['resche_time'] ) ) ) : '';
			$appointment_end_time = '';
			if(!empty($_POST['resche_end_time']) && $_POST['resche_end_time']  != '24:00' ) {
				$appointment_end_time = date( 'H:i:s', strtotime( sanitize_text_field( $_POST['resche_end_time'] ) ) );
			} else if(!empty($_POST['resche_end_time']) && $_POST['resche_end_time'] == '24:00') {
				$appointment_end_time = '24:00:00';
			}
			if(empty($appointment_end_time)) {
				$bookingpress_timeslots = $BookingPress->bookingpress_get_service_end_time($appointment_service_id, $appointment_start_time);
				$appointment_end_time = !empty($bookingpress_timeslots['service_end_time']) ? $bookingpress_timeslots['service_end_time'] : '';
				if(!empty($appointment_end_time)){
					$appointment_end_time = date("H:i:s", strtotime($appointment_end_time));
				}
			}

			if( current_time('timestamp') > strtotime( $appointment_selected_date . ' ' . $appointment_start_time ) ) {
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
				if($return_data){
					return $response;
				}				
				echo wp_json_encode( $response );
				die;
			}
			
			do_action('bookingpress_reschedule_appointment_extra_validation');

			$bookingpress_uniq_id               = isset( $_REQUEST['bookingpress_uniq_id'] ) ? sanitize_text_field( $_REQUEST['bookingpress_uniq_id'] ) : '';
			$appointment_selected_date           = isset( $_REQUEST['resche_date'] ) ? sanitize_text_field( $_REQUEST['resche_date'] ) : '';
			$bpa_front_timings_key = 'bpa_front_timings_' .$bookingpress_uniq_id.'_'.$appointment_selected_date;
			
			$bookingpress_version = get_option('bookingpress_version');
			if( version_compare( $bookingpress_version, '1.0.82', '<' ) ){
				$bpa_front_timings_data = get_transient( $bpa_front_timings_key );
			} else {
				$bpa_front_timings_data = $this->bookingpress_get_transient( $bpa_front_timings_key );
			}			
			

			$bookingpress_service_duration_unit = (isset($appointment_log_data['bookingpress_service_duration_unit']))?$appointment_log_data['bookingpress_service_duration_unit']:''; 
			if($bookingpress_service_duration_unit != 'd'){

				if(empty($bpa_front_timings_data)){
					$response['variant'] 	= 'error';
					$response['title']   	= esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']    	= esc_html__( 'Selected date & time is not valid.', 'bookingpress-appointment-booking' );
					echo wp_json_encode( $response );
					die;								
				}else{
	
					$timings = array_values( $bpa_front_timings_data );
					$appointment_start_time_compare =  date('H:i',strtotime($appointment_start_time));
					$appointment_end_time_compare =  date('H:i',strtotime($appointment_end_time));
	
					$time_slot_start_key = array_search($appointment_start_time_compare, array_column( $timings, 'store_start_time' ) );
					$time_slot_end_key = array_search( $appointment_end_time_compare, array_column( $timings, 'store_end_time' ) );
					$bookingpress_service_duration_unit = (isset($appointment_log_data['bookingpress_service_duration_unit']))?$appointment_log_data['bookingpress_service_duration_unit']:'';
	
					if( ( trim($time_slot_start_key) === '' || trim($time_slot_end_key) === '' || trim($time_slot_end_key) != trim($time_slot_start_key) ) && 'd' != $bookingpress_service_duration_unit){
						$response['tstart_key' ] = $time_slot_start_key;
						$response['tend_key' ] = $time_slot_end_key;
						$response['variant']              = 'error';
						$response['title']                = 'Error';
						$response['msg']                  = esc_html__("Sorry, Booking can not be done as booking time is different than selected timeslot", "bookingpress-appointment-booking");
						echo wp_json_encode($response);
						die;
					}
	
				}				

			}
			


			$reschedule_apt_update_data       = array(
				'bookingpress_appointment_date'   => $appointment_selected_date,
				'bookingpress_appointment_time'   => $appointment_start_time,
				'bookingpress_appointment_end_time' => $appointment_end_time,
				'bookingpress_is_reschedule'      => 1,
			);
			$reschedule_apt_update_data_where = array(
				'bookingpress_appointment_booking_id' => !empty( $_REQUEST['resche_apt_id'] ) ? intval( $_REQUEST['resche_apt_id'] ) : 0,
			);
			$is_appointment_exists            = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) as total FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = %d AND bookingpress_appointment_date = %s AND bookingpress_appointment_time LIKE %s AND (bookingpress_appointment_status = '1' OR bookingpress_appointment_status = '2')", $appointment_service_id, $appointment_selected_date, $appointment_start_time ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			$is_appointment_exists = apply_filters('bookingpress_check_rescheduled_is_appointment_already_booked',$is_appointment_exists,$reschedule_id);		

			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Reschedule appointment already exists', 'bookingpress_mybookings', $is_appointment_exists, $bookingpress_other_debug_log_id );

			if ( $is_appointment_exists > 0 ) {
				$duplidate_appointment_time_slot_found = $BookingPress->bookingpress_get_settings('duplidate_appointment_time_slot_found', 'message_setting');
				$booking_already_exists_error_msg = $duplidate_appointment_time_slot_found;
				$response['variant']              = 'error';
				$response['title']                = 'Error';
				$response['msg']                  = $booking_already_exists_error_msg;
				if($return_data){
					return $response;
				}				
				echo wp_json_encode( $response );
				exit();
			} else {
				

				//BookingPress Reschedule History
				if(!empty($reschedule_id)){
					$bookingpress_logged_in_user_id = get_current_user_id();
					$bookingpress_customer_id = $appointment_log_data['bookingpress_customer_id'];

					$bookingpress_appointment_original_date = $appointment_log_data['bookingpress_appointment_date'];
					$bookingpress_appointment_original_start_time = $appointment_log_data['bookingpress_appointment_time'];
					$bookingpress_appointment_original_end_time = $appointment_log_data['bookingpress_appointment_end_time'];

					$bookingpress_appointment_new_date = $appointment_selected_date;
					$bookingpress_appointment_new_start_time = $appointment_start_time;
					$bookingpress_appointment_new_end_time = $appointment_end_time;

					$bookingpress_reschedule_from = 2; //which means frontend
					
					$bookingpress_reschedule_history_data = array(
						'bookingpress_appointment_id' => $reschedule_id,
						'bookingpress_appointment_original_date' => $bookingpress_appointment_original_date,
						'bookingpress_appointment_original_start_time' => $bookingpress_appointment_original_start_time,
						'bookingpress_appointment_original_end_time' => $bookingpress_appointment_original_end_time,
						'bookingpress_appointment_new_date' => $bookingpress_appointment_new_date,
						'bookingpress_appointment_new_start_time' => $bookingpress_appointment_new_start_time,
						'bookingpress_appointment_new_end_time' => $bookingpress_appointment_new_end_time,
						'bookingpress_reschedule_from' => $bookingpress_reschedule_from,
						'bookingpress_wp_user_id' => $bookingpress_logged_in_user_id,
						'bookingpress_customer_id' => $bookingpress_customer_id,
					);

					if( !empty( $appointment_log_data ) ) {
						
						$get_last_appointment_data = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_appointment_id = %d", '_bpa_last_appointment_data', $reschedule_id) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name. false alarm

						if( 1 > $get_last_appointment_data ){
							$wpdb->insert(
								$tbl_bookingpress_appointment_meta,
								array(
									'bookingpress_appointment_meta_key' => '_bpa_last_appointment_data',
									'bookingpress_appointment_meta_value' => wp_json_encode( $appointment_log_data ),
									'bookingpress_appointment_id' => $reschedule_id
								)
							);
						} else {
							$bookingpress_db_fields = array(
								'bookingpress_appointment_meta_value' => wp_json_encode( $appointment_log_data )
							);	
							$wpdb->update( $tbl_bookingpress_appointment_meta, $bookingpress_db_fields, array( 'bookingpress_appointment_id' => $reschedule_id, 'bookingpress_appointment_meta_key' => '_bpa_last_appointment_data' ) );
						}
					}

					$wpdb->insert($tbl_bookingpress_reschedule_history, $bookingpress_reschedule_history_data);
				}
				
				$update = $wpdb->update( $tbl_bookingpress_appointment_bookings, $reschedule_apt_update_data, $reschedule_apt_update_data_where, array( '%s', '%s' ), array( '%d' ) );
				if ( $update > 0 ) {

					$bookingpress_reschedule_appointment_success_msg = $BookingPress->bookingpress_get_customize_settings('reschedule_appointment_success_msg', 'booking_my_booking');
					
					if ( ! empty( $reschedule_id ) ) {
						
						do_action( 'bookingpress_after_rescheduled_appointment', $reschedule_id );

						$appointment_log_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $reschedule_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
						if ( ! empty( $appointment_log_data ) ) {
							$bookingpress_customer_data = $BookingPress->get_customer_details( $appointment_log_data['bookingpress_customer_id'] );
							$bookingpress_wpuser_id     = $bookingpress_customer_data['bookingpress_wpuser_id'];
							if ( ! empty( $bookingpress_wpuser_id ) && ! empty( $bookingpress_customer_data ) ) {
								$bookingpress_customer_email = $bookingpress_customer_data['bookingpress_user_email'];
								// Send customer email notification
								$bookingpress_email_res = $bookingpress_email_notifications->bookingpress_send_email_notification( 'customer', 'Appointment Rescheduled', $reschedule_id, $bookingpress_customer_email );
								$is_email_sent          = $bookingpress_email_res['is_mail_sent'];
								// Send admin email notification

								$bookingpress_admin_emails = $BookingPress->bookingpress_get_settings( 'admin_email', 'notification_setting' );
								$bookingpress_admin_emails = apply_filters('bookingpress_filter_admin_email_data', $bookingpress_admin_emails, $reschedule_id,'Appointment Rescheduled');
								if ( ! empty( $bookingpress_admin_emails ) ) {
									$bookingpress_cc_emails = array();
									$bookingpress_cc_emails = apply_filters('bookingpress_add_cc_email_address', $bookingpress_cc_emails, 'Appointment Rescheduled');

									$bookingpress_admin_emails = explode( ',', $bookingpress_admin_emails );
									foreach ( $bookingpress_admin_emails as $admin_email_key => $admin_email_val ) {
										$bookingpress_email_notifications->bookingpress_send_email_notification( 'employee', 'Appointment Rescheduled', $reschedule_id, $admin_email_val, $bookingpress_cc_emails );
									}
								}
							}
						}						
					}

					$response['variant']     = 'success';
					$response['title']       = esc_html__('Success', 'bookingpress-appointment-booking');
					$response['msg']         = stripslashes_deep($bookingpress_reschedule_appointment_success_msg);
					$response['update_data'] = $update;
					if($return_data){
						return $response;
					}					
					echo wp_json_encode( $response );
					wp_die();
				}
			}

		}

				
		/**
		 * Function for set appointment time when appointment reschedule from frontend
		 *
		 * @return void
		 */
		function bookingpress_set_appointment_time_slot_func() {
			global $wpdb, $tbl_bookingpress_services, $BookingPress, $bookingpress_other_debug_log_id;

			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Reschedule appointment get time details', 'bookingpress_mybookings', $_REQUEST, $bookingpress_other_debug_log_id );

			if ( isset( $_REQUEST['action'] ) && sanitize_text_field( $_REQUEST['action'] ) == 'bookingpress_set_appointment_time_slot' ) {
				$wpnonce               = isset( $_REQUEST['reschedule_apt_wpnonce'] ) ? sanitize_text_field( $_REQUEST['reschedule_apt_wpnonce'] ) : '';
				$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
				if ( ! $bpa_verify_nonce_flag ) {
					$response            = array();
					$response['variant'] = 'error';
					$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
					echo wp_json_encode( $response );
					die();
				}
			}
			$bookingpress_service_id    = isset( $_REQUEST['service_id'] ) ? intval( $_REQUEST['service_id'] ) : '';
			$bookingpress_selected_date = isset( $_REQUEST['selected_date'] ) ? sanitize_text_field( $_REQUEST['selected_date'] ) : '';

			if ( ! empty( $bookingpress_service_id ) && ! empty( $bookingpress_selected_date ) ) {
				$appointment_time_slot = $BookingPress->bookingpress_get_service_available_time( $bookingpress_service_id, $bookingpress_selected_date );
				
				do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Reschedule appointment timeslot details', 'bookingpress_mybookings', $appointment_time_slot, $bookingpress_other_debug_log_id );

				$bookingpress_service_slot_details = $BookingPress->bookingpress_get_daily_timeslots( $appointment_time_slot );
				do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Reschedule appointment serviceslot details', 'bookingpress_mybookings', $bookingpress_service_slot_details, $bookingpress_other_debug_log_id );

				echo wp_json_encode( $bookingpress_service_slot_details );
				exit;
			}
		}
		
		/**
		 * Add dynamic data variables for my bookings shortcode
		 *
		 * @param  mixed $bookingpress_front_appointment_vue_data_fields
		 * @return void
		 */
		function bookingpress_front_appointment_add_dynamic_data_func( $bookingpress_front_appointment_vue_data_fields ) {
	
			global $wpdb, $BookingPress, $tbl_bookingpress_form_fields, $tbl_bookingpress_customers, $tbl_bookingpress_form_fields, $bookingpress_pro_staff_members,$bookingpress_bring_anyone_with_you;
			// reschedule customer appointment data variables

			$bookingpress_delete_customer_Account = $BookingPress->bookingpress_get_customize_settings( 'allow_customer_delete_profile', 'booking_my_booking' );
			$bookingpress_delete_customer_Account = !empty($bookingpress_delete_customer_Account) && $bookingpress_delete_customer_Account == 'true' ? true :false;
			$bookingpress_edit_profile = $BookingPress->bookingpress_get_customize_settings( 'allow_customer_edit_profile', 'booking_my_booking' );
			$bookingpress_edit_profile = !empty($bookingpress_edit_profile) && $bookingpress_edit_profile == 'true' ? 1 :0;

			$bookingpress_reschedule_customer_apt = $BookingPress->bookingpress_get_customize_settings( 'allow_customer_reschedule_apt', 'booking_my_booking' );
			$bookingpress_reschedule_customer_apt = !empty($bookingpress_reschedule_customer_apt) && $bookingpress_reschedule_customer_apt == 'true' ? 1 :0;
			$bookingpress_front_appointment_vue_data_fields['is_bring_anyone_with_you_enable'] = $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation();

			$bookingpress_front_appointment_vue_data_fields['open_customer_reschedule_appointment_modal']  = false;
			$bookingpress_front_appointment_vue_data_fields['customer_reschedule_appointment_loader']  = false;
			$bookingpress_front_appointment_vue_data_fields['reschedule_appointment_id']                   = '';
			$bookingpress_front_appointment_vue_data_fields['appointment_customer_reschedule_booked_date'] = date( 'Y-m-d', current_time( 'timestamp' ) );
			$bookingpress_front_appointment_vue_data_fields['appointment_customer_reschedule_booked_time'] = '';
			$bookingpress_front_appointment_vue_data_fields['appointment_customer_reschedule_end_time'] = '';
			$bookingpress_front_appointment_vue_data_fields['reschedule_appointment_time_slot']            = array();
			$bookingpress_front_appointment_vue_data_fields['reschedule_service_id']                       = '';
			$bookingpress_front_appointment_vue_data_fields['reschedule_staff_id']                       = '';
			$bookingpress_front_appointment_vue_data_fields['reschedule_apt_status']                       = '';
			$bookingpress_front_appointment_vue_data_fields['isLoadTimeLoader']                            = '0';
			$bookingpress_front_appointment_vue_data_fields['isServiceLoadTimeLoader']                     = '0';
			$bookingpress_front_appointment_vue_data_fields['allow_customer_reschedule_apt']               = $bookingpress_reschedule_customer_apt;
			$bookingpress_front_appointment_vue_data_fields['allow_customer_delete_profile']               = $bookingpress_delete_customer_Account;
			$bookingpress_front_appointment_vue_data_fields['allow_customer_edit_profile']                 = $bookingpress_edit_profile;
			$bookingpress_front_appointment_vue_data_fields['login_form_title'] = $BookingPress->bookingpress_get_customize_settings( 'login_form_title', 'booking_my_booking' );
			$bookingpress_front_appointment_vue_data_fields['login_form_username_field_label'] = $BookingPress->bookingpress_get_customize_settings( 'login_form_username_field_label', 'booking_my_booking' );
			$bookingpress_front_appointment_vue_data_fields['login_form_password_field_label'] = $BookingPress->bookingpress_get_customize_settings( 'login_form_password_field_label', 'booking_my_booking' );
			$bookingpress_front_appointment_vue_data_fields['login_form_button_label'] =$BookingPress->bookingpress_get_customize_settings( 'login_form_button_label', 'booking_my_booking' );
			$bookingpress_front_appointment_vue_data_fields['forgot_password_link_label'] = $BookingPress->bookingpress_get_customize_settings( 'forgot_password_link_label', 'booking_my_booking' );
			$bookingpress_front_appointment_vue_data_fields['login_form_error_msg_label'] = $BookingPress->bookingpress_get_customize_settings( 'login_form_error_msg_label', 'booking_my_booking' );
			$bookingpress_front_appointment_vue_data_fields['forgot_password_form_title'] = $BookingPress->bookingpress_get_customize_settings( 'forgot_password_form_title', 'booking_my_booking' );
			$bookingpress_front_appointment_vue_data_fields['forgot_password_form_email_label'] = $BookingPress->bookingpress_get_customize_settings( 'forgot_password_form_email_label', 'booking_my_booking' );
			$bookingpress_front_appointment_vue_data_fields['forgot_password_form_error_msg_label'] =$BookingPress->bookingpress_get_customize_settings( 'forgot_password_form_error_msg_label', 'booking_my_booking' );
			$bookingpress_front_appointment_vue_data_fields['forgot_password_form_success_msg_label'] = $BookingPress->bookingpress_get_customize_settings( 'forgot_password_form_success_msg_label', 'booking_my_booking' );			
			$bookingpress_front_appointment_vue_data_fields['forgot_password_form_button_label'] = $BookingPress->bookingpress_get_customize_settings( 'forgot_password_form_button_label', 'booking_my_booking' );						
			$login_form_username_required_field_label =$BookingPress->bookingpress_get_customize_settings( 'login_form_username_required_field_label', 'booking_my_booking' );
			$login_form_password_required_field_label = $BookingPress->bookingpress_get_customize_settings( 'login_form_password_required_field_label', 'booking_my_booking' );			
			$forgot_password_form_email_required_field_label = $BookingPress->bookingpress_get_customize_settings( 'forgot_password_form_email_required_field_label', 'booking_my_booking' );

			$bookingpress_morning_text = $BookingPress->bookingpress_get_customize_settings('morning_text', 'booking_form');
            $bookingpress_morning_text = !empty($bookingpress_morning_text) ? stripslashes_deep($bookingpress_morning_text) : '';
            if (empty($bookingpress_morning_text) ) {
                $bookingpress_morning_text = esc_html__('Morning', 'bookingpress-appointment-booking');
            }
            $bookingpress_afternoon_text = $BookingPress->bookingpress_get_customize_settings('afternoon_text', 'booking_form');
            $bookingpress_afternoon_text = !empty($bookingpress_afternoon_text) ? stripslashes_deep($bookingpress_afternoon_text) : '';
            if (empty($bookingpress_afternoon_text) ) {
                $bookingpress_afternoon_text = esc_html__('Afternoon', 'bookingpress-appointment-booking');
            }
            $bookingpress_evening_text = $BookingPress->bookingpress_get_customize_settings('evening_text', 'booking_form');
            $bookingpress_evening_text = !empty($bookingpress_evening_text) ? stripslashes_deep($bookingpress_evening_text) : '';
            if (empty($bookingpress_evening_text) ) {
                $bookingpress_evening_text = esc_html__('Evening', 'bookingpress-appointment-booking');
            }
            $bookingpress_night_text = $BookingPress->bookingpress_get_customize_settings('night_text', 'booking_form');
            $bookingpress_night_text = !empty($bookingpress_night_text) ? stripslashes_deep($bookingpress_night_text) : '';
            if (empty($bookingpress_night_text) ) {
                $bookingpress_night_text = esc_html__('Night', 'bookingpress-appointment-booking');
            }    
            $bookingpress_front_appointment_vue_data_fields['bookingpress_morning_text'] = $bookingpress_morning_text;
            $bookingpress_front_appointment_vue_data_fields['bookingpress_afternoon_text'] = $bookingpress_afternoon_text;
            $bookingpress_front_appointment_vue_data_fields['bookingpress_evening_text'] = $bookingpress_evening_text;
            $bookingpress_front_appointment_vue_data_fields['bookingpress_night_text'] = $bookingpress_night_text;        

			$login_form_username_required_field_label = !empty($login_form_username_required_field_label) ? stripslashes_deep($login_form_username_required_field_label) : $login_form_username_required_field_label;
			$login_form_password_required_field_label = !empty($login_form_password_required_field_label) ? stripslashes_deep($login_form_password_required_field_label) : $login_form_password_required_field_label;
			$forgot_password_form_email_required_field_label = !empty($forgot_password_form_email_required_field_label) ? stripslashes_deep($forgot_password_form_email_required_field_label) : $forgot_password_form_email_required_field_label;

			$bookingpress_profile_redirect_url = $BookingPress->bookingpress_get_customize_settings( 'edit_profile_page', 'booking_my_booking' );
			$bookingpress_profile_redirect_url = ! empty( $bookingpress_profile_redirect_url ) ? get_permalink( $bookingpress_profile_redirect_url ) : '';
			$bookingpress_profile_redirect_url = ! empty( $bookingpress_profile_redirect_url ) ? $bookingpress_profile_redirect_url : BOOKINGPRESS_HOME_URL;
			$bookingpress_front_appointment_vue_data_fields['bookingpress_edit_profile_url'] = $bookingpress_profile_redirect_url;

			/* login form */
			$bookingpress_front_appointment_vue_data_fields['bookingpress_login_form'] = array(
				'bookingpress_username' => '',
				'bookingpress_password' => '',
			);
			$bookingpress_front_appointment_vue_data_fields['bookingpress_login_form_rules'] = array(
				'bookingpress_username' => array(
					array(
						'required' => true,
						'message'  => $login_form_username_required_field_label,
						'trigger'  => 'change',
					),
				),
				'bookingpress_password' => array(
					array(
						'required' => true,
						'message'  => $login_form_password_required_field_label,
						'trigger'  => 'change',
					),
				),				
			);
			/* Forgot password form */
			$bookingpress_front_appointment_vue_data_fields['bookingpress_forgot_password_form'] = array(
				'bookingpress_email' => '',
			);
			$bookingpress_front_appointment_vue_data_fields['bookingpress_forgot_password_form_rules'] = array(
				'bookingpress_email' => array(
					array(
						'required' => true,
						'message'  => $forgot_password_form_email_required_field_label,
						'trigger'  => 'change',
					),
				),
			);

			/** My Appointment Rescheduling Form */
			$bookingpress_front_appointment_vue_data_fields['bookingpress_mybooking_rescheduling_form'] = array(
				'appointment_customer_reschedule_booked_time' => ''
			);
			$bookingpress_front_appointment_vue_data_fields['bookingpress_mybooking_rescheduling_form_rules'] = array(
				'appointment_customer_reschedule_booked_time' => array(
					array(
						'required' => true,
						'message' => esc_html__( 'Please select rescheduling time', 'bookingpress-appointment-booking' ),
						'trigger' => 'change'
					)
				)
			);
			/** My Appointment Rescheduling Form */
			$bookingpress_front_appointment_vue_data_fields['bookingpress_show_forgot_password_form'] = '0';	
			$bookingpress_front_appointment_vue_data_fields['bookingpress_show_login_form'] = '1';	
			$bookingpress_front_appointment_vue_data_fields['is_bookingpress_login_loader'] = false;	
			$bookingpress_front_appointment_vue_data_fields['is_login_btn_disabled'] = false;	
			$bookingpress_front_appointment_vue_data_fields['is_display_error'] = '0';                                    
			$bookingpress_front_appointment_vue_data_fields['is_error_msg'] = "";
			$bookingpress_front_appointment_vue_data_fields['is_display_success'] = '0';                                    
			$bookingpress_front_appointment_vue_data_fields["is_success_msg"] = "";
			$bookingpress_front_appointment_vue_data_fields['bookingpress_customer_panel_form'] = '';

			$bookingpress_front_appointment_vue_data_fields['bookingpress_reschedule_drawer'] = false;
			$bookingpress_front_appointment_vue_data_fields['bookingpress_reschedule_drawer_direction'] = 'btt';
			$bookingpress_front_appointment_vue_data_fields['is_rescheduled_loader'] = false;
			$bookingpress_front_appointment_vue_data_fields['bookingpress_refund_confirm_modal'] = false;
			$bookingpress_front_appointment_vue_data_fields['refund_confirm_form']['refund_amount'] = 0;
			$bookingpress_front_appointment_vue_data_fields['refund_confirm_form']['default_refund_amount'] = 0;
			$bookingpress_front_appointment_vue_data_fields['refund_confirm_form']['refund_error_variant'] = '';
			$bookingpress_front_appointment_vue_data_fields['refund_confirm_form']['refund_error_msg'] = '';
			$bookingpress_front_appointment_vue_data_fields['is_display_refund_loader'] = '0';
			$bookingpress_front_appointment_vue_data_fields['is_refund_btn_disabled'] = false;
			$bookingpress_front_appointment_vue_data_fields['close_modal_on_esc'] = false;
			$bookingpress_front_appointment_vue_data_fields['is_mask_display'] = false;

			$bpa_login_customer_id             = get_current_user_id();
			$bookingpress_get_customer_details = $wpdb->get_row( $wpdb->prepare( 'SELECT bookingpress_user_country_phone FROM ' . $tbl_bookingpress_customers . ' WHERE bookingpress_wpuser_id =%d', $bpa_login_customer_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm
			$bookingpress_user_country_phone   = ! empty( $bookingpress_get_customer_details['bookingpress_user_country_phone'] ) ? $bookingpress_get_customer_details['bookingpress_user_country_phone'] : 0;

			$bookingpress_form_fields = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_form_fields . ' ORDER BY bookingpress_field_position ASC', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
			
			foreach ( $bookingpress_form_fields as $bookingpress_form_field_key => $bookingpress_form_field_val ) {
				$bookingpress_v_model_value = '';
				if ( $bookingpress_form_field_val['bookingpress_form_field_name'] != 'note' && $bookingpress_form_field_val['bookingpress_form_field_name'] != 'fullname' ) {
					if ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'firstname' ) {
						$bookingpress_v_model_value = $bookingpress_field_list['customer_firstname'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
						$bookingpress_v_model_value = 'customer_firstname';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'lastname' ) {
						$bookingpress_v_model_value = $bookingpress_field_list['customer_lastname'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
						$bookingpress_v_model_value = 'customer_lastname';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'email_address' ) {
						$bookingpress_v_model_value = $bookingpress_field_list['customer_email'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
						$bookingpress_v_model_value = 'customer_email';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'phone_number' ) {
						$bookingpress_v_model_value = $bookingpress_field_list['customer_phone'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
						$bookingpress_v_model_value = 'customer_phone';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'username' ) {
						$bookingpress_v_model_value = $bookingpress_field_list['customer_username'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
						$bookingpress_v_model_value = 'customer_username';
					} else {
						$bookingpress_v_model_value = $bookingpress_form_field_val['bookingpress_field_meta_key'];
					}
					$bookingpress_front_appointment_vue_data_fields['edit_profile_form_data'][ $bookingpress_v_model_value ] = '';
				}
			}
			$bookingpress_front_appointment_vue_data_fields['is_display_success']                               = 0;
			$bookingpress_front_appointment_vue_data_fields['is_success_msg']                                   = 0;
			$bookingpress_front_appointment_vue_data_fields['bookingpress_footer_dynamic_class']                = '';
			$bookingpress_front_appointment_vue_data_fields['bookingpress_container_dynamic_class']             = '';
			$bookingpress_front_appointment_vue_data_fields['is_profile_display_save_loader']                   = '0';
			$bookingpress_front_appointment_vue_data_fields['is_profile_disabled']                              = false;
			$bookingpress_front_appointment_vue_data_fields['is_change_password_display_loader']                = '0';
			$bookingpress_front_appointment_vue_data_fields['edit_profile_form_data']['customer_phone_country'] = 'us';

			$bpa_phone_number_field_detail = wp_cache_get( 'bookingpress_phone_field_data' );
			if ( false === $bpa_phone_number_field_detail ) {
				$bookingpress_customer_phone_number_placeholder_value = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_placeholder FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_name = %s", 'phone_number' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name defined globally. False alarm
				wp_cache_set( 'bookingpress_phone_field_data', $bookingpress_customer_phone_number_placeholder_value );
			} else {
				$bookingpress_customer_phone_number_placeholder_value = $bpa_phone_number_field_detail;
			}
			$bookingpress_customer_phone_number_placeholder_value = ! empty( $bookingpress_customer_phone_number_placeholder_value['bookingpress_field_placeholder'] ) ? $bookingpress_customer_phone_number_placeholder_value['bookingpress_field_placeholder'] : __( 'Enter phone number', 'bookingpress-appointment-booking' );

			$bookingpress_front_appointment_vue_data_fields['bookingpress_tel_input_props'] = array(
				'defaultCountry' => $bookingpress_user_country_phone,
				'inputOptions'   => array(
					'placeholder' => $bookingpress_customer_phone_number_placeholder_value,
				),
			);

			$bookingpress_form_fields               = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_form_fields . ' ORDER BY bookingpress_is_customer_field,bookingpress_field_position ASC', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
				
			$bookingpress_form_fields_error_msg_arr = $bookingpress_form_fields_new = array();
			foreach ( $bookingpress_form_fields as $bookingpress_form_field_key => $bookingpress_form_field_val ) {
				$bookingpress_field_options = ! empty( $bookingpress_form_field_val['bookingpress_field_options'] ) ? json_decode( $bookingpress_form_field_val['bookingpress_field_options'], true ) : '';

				$bookingpress_customer_field = ( ( ! empty( $bookingpress_field_options['used_for_user_information'] ) && $bookingpress_field_options['used_for_user_information'] == 'true' ) || $bookingpress_form_field_val['bookingpress_is_customer_field'] );

				if ( ( $bookingpress_customer_field && ! empty( $bookingpress_field_options['visibility'] ) && $bookingpress_field_options['visibility'] == 'always' && $bookingpress_form_field_val['bookingpress_form_field_name'] != 'note' && $bookingpress_form_field_val['bookingpress_form_field_name'] != 'fullname' ) ) {

					$bookingpress_v_model_value = '';
					if ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'firstname' ) {
						$bookingpress_v_model_value = 'customer_firstname';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'lastname' ) {
						$bookingpress_v_model_value = 'customer_lastname';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'email_address' ) {
						$bookingpress_v_model_value = 'customer_email';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'phone_number' ) {
						$bookingpress_v_model_value = 'customer_phone';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'username' ) {
						$bookingpress_v_model_value = 'customer_username';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'terms_and_conditions' ) {
						$bookingpress_v_model_value = 'appointment_terms_conditions';
					}

					$bookingpress_field_type = '';
					if ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'firstname' ) {
						$bookingpress_field_type = 'Text';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'lastname' ) {
						$bookingpress_field_type = 'Text';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'email_address' ) {
						$bookingpress_field_type = 'Email';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'username' ) {
						$bookingpress_field_type = 'Text';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'phone_number' ) {
						$bookingpress_field_type = 'Dropdown';
					} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'terms_and_conditions' ) {
						$bookingpress_field_type = 'terms_and_conditions';
					} else {
						$bookingpress_field_type = $bookingpress_form_field_val['bookingpress_field_type'];
					}

					if( 1 == $bookingpress_form_field_val['bookingpress_is_customer_field'] ){
						$bookingpress_field_type = ucfirst( $bookingpress_field_type );
					}

					$bookingpress_field_setting_fields_tmp                   = array();
					$bookingpress_field_setting_fields_tmp['id']             = intval( $bookingpress_form_field_val['bookingpress_form_field_id'] );
					$bookingpress_field_setting_fields_tmp['field_name']     = $bookingpress_form_field_val['bookingpress_form_field_name'];
					$bookingpress_field_setting_fields_tmp['field_type']     = $bookingpress_field_type;
					$bookingpress_field_setting_fields_tmp['is_edit']        = false;
					$bookingpress_field_setting_fields_tmp['is_required']    = ( $bookingpress_form_field_val['bookingpress_field_required'] == 0 ) ? false : true;
					$bookingpress_field_setting_fields_tmp['label']          = stripslashes_deep($bookingpress_form_field_val['bookingpress_field_label']);
					$bookingpress_field_setting_fields_tmp['placeholder']    = stripslashes_deep( $bookingpress_form_field_val['bookingpress_field_placeholder']);
					$bookingpress_field_setting_fields_tmp['error_message']  = stripslashes_deep($bookingpress_form_field_val['bookingpress_field_error_message']);
					$bookingpress_field_setting_fields_tmp['is_hide']        = ( $bookingpress_form_field_val['bookingpress_field_is_hide'] == 0 ) ? false : true;
					$bookingpress_field_setting_fields_tmp['field_position'] = floatval( $bookingpress_form_field_val['bookingpress_field_position'] );
					$bookingpress_field_setting_fields_tmp['v_model_value']  = $bookingpress_v_model_value;
				
					$bookingpress_field_setting_fields_tmp = apply_filters( 'bookingpress_arrange_form_fields_outside', $bookingpress_field_setting_fields_tmp, $bookingpress_form_field_val );

					if( 1 == $bookingpress_form_field_val['bookingpress_is_customer_field'] ){
						$bookingpress_field_setting_fields_tmp['is_hide'] = 0;
						$bookingpress_field_setting_fields_tmp['field_options']['layout'] = '1col';
					}

					array_push( $bookingpress_form_fields_new, $bookingpress_field_setting_fields_tmp );


					if ( $bookingpress_form_field_val['bookingpress_field_required'] == '1' ) {
						$bookingpress_error_msg = !empty($bookingpress_field_setting_fields_tmp['error_message']) ? stripslashes_deep($bookingpress_field_setting_fields_tmp['error_message']) : '' ;

						$bookingpress_error_msg = empty($bookingpress_error_msg) && !empty($bookingpress_form_field_val['bookingpress_field_label']) ?stripslashes_deep($bookingpress_form_field_val['bookingpress_field_label']).' '.__('is required','bookingpress-appointment-booking') : $bookingpress_error_msg ;

						$bookingpress_form_fields_error_msg_arr[ $bookingpress_field_setting_fields_tmp['v_model_value'] ][] = array(
							'required' => true,
							'message'  => $bookingpress_error_msg,
							'trigger'  => 'blur',
						);

						if( $bookingpress_v_model_value == 'appointment_terms_conditions') {
                               
							$bookingpress_form_fields_error_msg_arr[ $bookingpress_field_setting_fields_tmp['v_model_value'] ][] = array(
								'required' => true,
								'message'  => $bookingpress_error_msg,
								'trigger'  => 'change',
							);
                        }
					}
					
					if(!empty($bookingpress_field_setting_fields_tmp['field_options']['minimum'])) {
						$bookingpress_form_fields_error_msg_arr[ $bookingpress_field_setting_fields_tmp['v_model_value']][] = array( 
							'min' => intval($bookingpress_field_setting_fields_tmp['field_options']['minimum']),
							'message'  => __('Minimum','bookingpress-appointment-booking').' '.$bookingpress_field_setting_fields_tmp['field_options']['minimum'].' '.__('character required','bookingpress-appointment-booking'),
							'trigger'  => 'blur',
						);
					}

					if(!empty($bookingpress_field_setting_fields_tmp['field_options']['maximum'])) {
						$bookingpress_form_fields_error_msg_arr[$bookingpress_field_setting_fields_tmp['v_model_value']][] = array( 
							'max' => intval($bookingpress_field_setting_fields_tmp['field_options']['maximum']),
							'message'  => __('Maximum','bookingpress-appointment-booking').' '.$bookingpress_field_setting_fields_tmp['field_options']['maximum'].' '.__('character allowed','bookingpress-appointment-booking'),
							'trigger'  => 'blur',
						);
					}
				}
			}
		
			$bookingpress_front_appointment_vue_data_fields['bookingpress_customer_details_rule'] = $bookingpress_form_fields_error_msg_arr;
			$bookingpress_front_appointment_vue_data_fields['customer_form_fields'] = $bookingpress_form_fields_new;

			$bookingpress_front_appointment_vue_data_fields['bookingpress_change_password_form']['bookingpress_current_password'] = '';
			$bookingpress_front_appointment_vue_data_fields['bookingpress_change_password_form']['bookingpress_new_password'] = '';
			$bookingpress_front_appointment_vue_data_fields['bookingpress_change_password_form']['bookingpress_confirm_password'] = '';

			$bookingpress_customize_settings_arr = array(
				'login_form_error_msg_label', 'forgot_password_form_error_msg_label', 'forgot_password_form_success_msg_label', 'login_form_username_required_field_label', 'login_form_password_required_field_label', 'forgot_password_form_email_required_field_label','new_password_error_msg','old_password_error_msg','confirm_password_error_msg'
			);
			$bookingpress_get_customize_details = $BookingPress->bookingpress_get_customize_settings($bookingpress_customize_settings_arr, 'booking_my_booking');
			foreach($bookingpress_get_customize_details as $key => $value) {
				$bookingpress_get_customize_details[$key] = stripslashes_deep($value );
			}
			
			$bookingpress_front_appointment_vue_data_fields['bookingpress_change_password_form_rule'] = array(
				'bookingpress_current_password' => array(
					'required' => true,
					'message' => !empty($bookingpress_get_customize_details['old_password_error_msg']) ? $bookingpress_get_customize_details['old_password_error_msg'] :'',
					'trigger' => 'blur',
				),
				'bookingpress_new_password' => array(
					'required' => true,
					'message' => !empty($bookingpress_get_customize_details['new_password_error_msg']) ? $bookingpress_get_customize_details['new_password_error_msg'] :'',
					'trigger' => 'blur',
				),
				'bookingpress_confirm_password' => array(
					'required' => true,
					'message' => !empty($bookingpress_get_customize_details['confirm_password_error_msg']) ? $bookingpress_get_customize_details['confirm_password_error_msg'] :'',
					'trigger' => 'blur',
				),
			);

			$bookingpress_front_appointment_vue_data_fields['bookingpress_login_form'] = array(
				'bookingpress_login_email' => '',
				'bookingpress_login_pass' => '',
				'bookingpress_is_remember' => '',
			);
			$bookingpress_front_appointment_vue_data_fields['bookingpress_login_rules'] = array(
				'bookingpress_login_email' => array(
					'required' => true,
					'message' => $bookingpress_get_customize_details['login_form_username_required_field_label'],
					'trigger' => 'blur',
				),
				'bookingpress_login_pass' => array(
					'required' => true,
					'message' => $bookingpress_get_customize_details['login_form_password_required_field_label'],
					'trigger' => 'blur',
				),
			);
			$bookingpress_front_appointment_vue_data_fields['bookingpress_login_loader'] = '0';
			
			$bookingpress_front_appointment_vue_data_fields['bookingpress_forgot_password_form'] = array(
				'bookingpress_forgot_password_email' => '',
			);
			$bookingpress_front_appointment_vue_data_fields['bookingpress_forgot_password_rules'] = array(
				'bookingpress_forgot_password_email' => array(
					'required' => true,
					'message' => $bookingpress_get_customize_details['forgot_password_form_email_required_field_label'],
					'trigger' => 'blur',
				),
			);
			$bookingpress_front_appointment_vue_data_fields['bookingpress_forgot_password_loader'] = '0';

			/** if staffmember is enabled or not */
			$bookingpress_is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_front_appointment_vue_data_fields['bookingpress_is_staff_module_active'] = $bookingpress_is_staffmember_module_activated;			

			$bookingpress_hide_staffmember_selection = $BookingPress->bookingpress_get_customize_settings('hide_staffmember_selection', 'booking_form');
			$bookingpress_hide_staffmember_selection = !empty($bookingpress_hide_staffmember_selection) && $bookingpress_hide_staffmember_selection == 'true' ? 1 : 0;
			$bookingpress_front_appointment_vue_data_fields['bookingpress_hide_staffmember_selection'] = $bookingpress_hide_staffmember_selection;

			$bookingpress_front_appointment_vue_data_fields['bookingpress_refund_appointment_drawer'] = false;
			$bookingpress_front_appointment_vue_data_fields['bookingpress_refund_drawer_direction'] = "btt";

			$bookingpress_max_days_for_booking = $BookingPress->bookingpress_get_settings( 'period_available_for_booking', 'general_setting' );
			$bookingpress_front_appointment_vue_data_fields['booking_cal_rec_maxdate'] = '';

			return $bookingpress_front_appointment_vue_data_fields;
		}
		
		/**
		 * Function for methods on load of mybookings shortcode
		 *
		 * @return void
		 */
		function bookingpress_dynamic_add_onload_myappointment_methods_func(){
			global $BookingPress;
            $bookingpress_max_days_for_booking = $BookingPress->bookingpress_get_settings( 'period_available_for_booking', 'general_setting' );
			?>
				const vm = this;
			<?php
				if(is_user_logged_in()){
					?>	
						vm.bookingpress_customer_panel_form = 'my_appointment';
						vm.load_edit_profile_data();
						
					<?php
				}else{
					?>
						setTimeout(function(){
							vm.bookingpress_customer_panel_form = 'login'
						}, 1000);
					<?php
				}
			?>
				var bkp_wpnonce_pre = "";
				var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
				if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
				{
					bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
				}
				else 
				{
					bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
					var postData = { action: "bookingpress_generate_spam_captcha", _wpnonce:bkp_wpnonce_pre_fetch };
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.variant=="error"){
							var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
							if(typeof bkp_wpnonce_pre_fetch!="undefined" && bkp_wpnonce_pre_fetch!=null && response.data.updated_nonce!="")
							{
								document.getElementById("_wpnonce").value = response.data.updated_nonce;
							}
						}
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				}
				vm.booking_cal_rec_maxdate = new Date( Date.now() + ( 3600 * 1000 * ( 24 * <?php echo $bookingpress_max_days_for_booking; //phpcs:ignore ?>) ) );
		<?php	
		}

				
		/**
		 * Function for add vue methods for my bookings shortcode
		 *
		 * @return void
		 */
		function bookingpress_front_appointment_add_vue_method_func() {
			global $bookingpress_global_options;
			$bookingpress_delete_customer_account_id = get_current_user_id();
			$bookingpress_forgot_pass_nonce = $bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
			//$bookingpress_forgot_pass_nonce = wp_create_nonce('bpa_forgot_pass_wp_nonce');
			$bookingpress_global_details     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_start_of_week = intval($bookingpress_global_details['start_of_week']);

			?>
				bookingpress_forgot_password(){
					const vm = this;
					vm.$refs['bookingpress_forgot_password_form'].validate((valid) => {
						if(valid){
							vm.bookingpress_forgot_password_loader = '1';

							var bkp_wpnonce_pre = "<?php echo esc_html( $bookingpress_forgot_pass_nonce ); ?>";
							var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
							if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
							{
								bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
							}
							else {
								bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
							}

							var forgotPassFormData = { action:"bookingpress_forgot_password_account", forgot_pass_email_address: vm.bookingpress_forgot_password_form.bookingpress_forgot_password_email, _wpnonce:bkp_wpnonce_pre_fetch };
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( forgotPassFormData ) )
							.then( function (response) {
								vm.bookingpress_forgot_password_loader = '0';
								if(response.data.variant == 'error'){
									vm.bookingpress_set_error_msg(response.data.msg);
								}else{
									vm.bookingpress_set_success_msg(response.data.msg);
								}
							}.bind(this) )
							.catch( function (error) {                    
								console.log(error);
							});
						}
					});
				},
				bookingpress_customer_login(){
					const vm = this;
					vm.$refs['bookingpress_login_frm'].validate((valid) => {
						if(valid){
							vm.bookingpress_login_loader = '1';
							var bkp_wpnonce_pre = "<?php echo esc_html( $bookingpress_forgot_pass_nonce ); ?>";
							var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
							if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
							{
								bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
							}
							else {
								bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
							}
							var loginFormData = { action:"bookingpress_login_customer_account", login_email_address: vm.bookingpress_login_form.bookingpress_login_email, login_password: vm.bookingpress_login_form.bookingpress_login_pass, is_remember: vm.bookingpress_login_form.bookingpress_is_remember, _wpnonce:bkp_wpnonce_pre_fetch };
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( loginFormData ) )
							.then( function (response) {								
								vm.bookingpress_login_loader = '0';
								if(response.data.variant == 'error'){
									vm.bookingpress_set_error_msg(response.data.msg);
								}else{
									if(typeof response.data.is_bookingpress_staffmember != 'undefined' && response.data.is_bookingpress_staffmember == 1) {
										window.location.href = response.data.staff_redirect_to;
									} else {
										vm.bookingpress_is_user_logged_in = 1;
										vm.bookingpress_customer_panel_form = 'my_appointment';
										vm.bookingpress_created_nonce = response.data.new_nonce;
										if(typeof bkp_wpnonce_pre_fetch!="undefined" && bkp_wpnonce_pre_fetch!=null)
										{
											document.getElementById("_wpnonce").value = response.data.new_nonce;
										}
										vm.loadFrontAppointments();
										vm.load_edit_profile_data();
									}
								}
							}.bind(this) )
							.catch( function (error) {                    
								console.log(error);
							});			
						}
					});
					return false;
				},
				bookingpress_change_password(){
					const vm = this;
					vm.bookingpress_reset_error_success_msg();
					vm.$refs['bookingpress_change_password_form'].validate((valid) => {
						if(valid){
							if(vm.bookingpress_change_password_form.bookingpress_new_password != vm.bookingpress_change_password_form.bookingpress_confirm_password){
								vm.bookingpress_set_error_msg('<?php esc_html_e('New password and confirm password must be same', 'bookingpress-appointment-booking'); ?>');
							}else{
								vm.is_change_password_display_loader = "1";
								var postData = { action:"bookingpress_update_password", current_password: vm.bookingpress_change_password_form.bookingpress_current_password, new_password: vm.bookingpress_change_password_form.bookingpress_new_password, confirm_password: vm.bookingpress_change_password_form.bookingpress_confirm_password, _wpnonce:vm.bookingpress_created_nonce };
								axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
								.then( function (response) {
									vm.is_change_password_display_loader = "0";									
									if(response.data.variant == "success"){
										vm.bookingpress_set_success_msg(response.data.msg);
									}else if(response.data.variant == "error"){
										vm.bookingpress_set_error_msg(response.data.msg);
									}
								}.bind(this) )
								.catch( function (error) {
									vm.bookingpress_set_error_msg(error);
								});		
							}
						}	
					});	
				},
				load_edit_profile_data(){
					const vm = this;	
					var postData = { action:"bookingpress_get_edit_profile_data", edit_profile_field_data: vm.edit_profile_form_data, _wpnonce:vm.bookingpress_created_nonce };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {	
						vm.edit_profile_form_data = response.data.edit_profile_field_data;				   				   
					}.bind(this) )
					.catch( function (error) {                    
						console.log(error);
					});	
				},
				bookingpress_edit_profile(customer_details_rule){
					const vm = this;		
					vm.bookingpress_reset_error_success_msg();
					this.$refs[customer_details_rule].validate((valid) => {
						if (valid) {
							vm.is_profile_disabled = true;
							vm.is_profile_display_save_loader = "1";				
							var postData = { action:"bookingpress_update_profile", edit_profile_data: vm.edit_profile_form_data, _wpnonce:vm.bookingpress_created_nonce };
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
							.then( function (response) {
								vm.is_profile_disabled = false;
								vm.is_profile_display_save_loader = "0";									
								if(response.data.variant == "success"){
									vm.bookingpress_set_success_msg(response.data.msg);
								}else if(response.data.variant == "error"){
									vm.bookingpress_set_error_msg(response.data.msg);
								}
							}.bind(this) )
							.catch( function (error) {
								vm.bookingpress_set_error_msg(error);
							});		
						}		
					});	
				},
				bookingpress_reset_error_success_msg(){
					const vm = this;
					vm.is_display_error = "0";
					vm.is_error_msg = '';
					vm.is_display_success = "0";
					vm.is_success_msg = '';
				},
				bookingpress_set_error_msg(error_msg){
					const vm = this;
					vm.bookingpress_reset_error_success_msg();
					vm.is_display_error = "1";
					vm.is_error_msg = error_msg;
					let pos = 0;
					let container = vm.$el;
					if( null != container ){
						pos = container.getBoundingClientRect().top + window.scrollY;
					}
					window.scrollTo({
						top: pos,
						behavior: "smooth",
					});
				},
				bookingpress_set_success_msg(success_msg){
					const vm = this;
					vm.bookingpress_reset_error_success_msg();
					vm.is_display_success = "1";
					vm.is_success_msg = success_msg;
					window.scrollTo({
						top: 0,
						behavior: "smooth",
					});
				},
				bookingpress_open_reschedule(){
					const vm = this;
					vm.bookingpress_reschedule_drawer = true;
				},
				bookingpress_close_reschedule(){
					const vm = this;
					vm.bookingpress_reschedule_drawer = false;	
				},
				is_display_forgot_pass(){
					const vm = this;
					vm.is_forgot_password_click = 1;
				},
				is_display_login_form(){
					const vm = this;
					vm.is_forgot_password_click = 0;
				},
				bookingpress_form_action(action){
					const vm = this;			
					vm.is_display_success = '0';
					vm.is_success_msg = '';
					vm.is_display_error = '0';
					vm.is_error_msg = '';						
					if(action == 'login')  {						
						vm.bookingpress_customer_panel_form = 'login';
					} else if(action == 'forgot_password')  {
						vm.bookingpress_customer_panel_form = 'forgot_password';
					}						
				},
				select_date(selected_value) 
				{
					const vm = this;
					vm.bookingpress_mybooking_rescheduling_form.appointment_customer_reschedule_booked_time = '';
					vm.bookingpress_set_time_slot(vm.reschedule_service_id);
				},
				open_customer_reschedule_apt_modal_func(currentElement, apt_id,service_duration)
				{
					const vm = this;

					vm.reschedule_appointment_service_duration = service_duration;
					this.reschedule_appointment_id = apt_id;
					if(vm.current_screen_size == 'mobile'){
						this.bookingpress_reschedule_drawer = true;
					}else{
						this.open_customer_reschedule_appointment_modal = true;
					}
					var reschedule_apt_id = apt_id;
					var postData = 
					{ 
						action:'my_appointment_get_service_id_from_appointment_id',
						appointment_id: apt_id,
						get_service_id_data_nonce: vm.bookingpress_created_nonce
					}; 
					vm.customer_reschedule_appointment_loader = true;
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) 
					{
						if(response.data.variant == 'success')
						{	
						
							vm.reschedule_service_id = response.data.service_id;
							vm.reschedule_staff_id = response.data.staff_id;
							vm.appointment_customer_reschedule_booked_date = response.data.appointment_date;
							let appointment_time = response.data.appointment_time;
							let appointment_end_time = response.data.appointment_end_time;
							let bookingpress_service_expiration_date = response.data.bookingpress_service_expiration_date;
							let bookingpress_service_start_date = response.data.bookingpress_service_start_date;
							vm.bookingpress_mybooking_rescheduling_form.appointment_customer_reschedule_booked_time = appointment_time;							
							vm.appointment_customer_reschedule_end_time = appointment_end_time;

							let disablePostData = { action: "bookingpress_get_disable_date", is_rescheduling_event: true, service_id: vm.reschedule_service_id, selected_service: vm.reschedule_service_id, selected_date: vm.appointment_customer_reschedule_booked_date, _wpnonce:vm.bookingpress_created_nonce};
							disablePostData.appointment_data_obj = {
								appointment_update_id:response.data.appointment_update_id,
								bookingpress_selected_staff_member_details: {
									selected_staff_member_id: vm.reschedule_staff_id
								},
								bookingpress_form_token:vm.bookingpress_uniq_id, 
							};
							<?php do_action('bookingpress_modify_rescheduled_appointment_xhr_data'); ?>

							if( "undefined" != response.data.bookingpress_selected_extra_details ){
								disablePostData.appointment_data_obj.bookingpress_selected_extra_details = response.data.bookingpress_selected_extra_details;
							}
							
							if( "undefined" != response.data.bookingpress_selected_bring_members ){
								disablePostData.appointment_data_obj.bookingpress_selected_bring_members = response.data.bookingpress_selected_bring_members;
							}
							
							disablePostData.appointment_data_obj.bookingpress_form_token = vm.bookingpress_uniq_id;
							

							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( disablePostData ) )
							.then( function( response ){
								if( response.data.variant == "success" ){

									let disableDates = response.data.days_off_disabled_dates;
									let disableDates_arr = disableDates.split(',');
									let disableDates_formatted = [];
									disableDates_arr.forEach(function( date ){
										let formatted_date = vm.get_formatted_date( date );
										disableDates_formatted.push( formatted_date );
									});
									vm.pickerOptions.disabledDate = function(Time){
										let currentDate = new Date( Time );
										
										currentDate = vm.get_formatted_date( currentDate );
										
										var date = new Date();
										
										date.setDate(date.getDate()-1);
										if(typeof bookingpress_service_expiration_date !== "undefined" && bookingpress_service_expiration_date != '') {
											var currentDate2 = min_date = vm.get_formatted_date( bookingpress_service_expiration_date);
											
											/* When Period available for booking in advance - days is less than service expiration date then set limit for that*/
											if(bookingpress_service_expiration_date > vm.get_formatted_date(vm.booking_cal_rec_maxdate)){
												min_date = vm.get_formatted_date(vm.booking_cal_rec_maxdate);
											}
											/* When Period available for booking in advance - days is less than service expiration date then set limit for that*/
											if(currentDate > min_date) {
												return true;
											}
										}
										/* Period available for booking in advance - days */
										if(Time>vm.booking_cal_rec_maxdate && typeof bookingpress_service_expiration_date !== "undefined" && bookingpress_service_expiration_date == '' ) {
											return true;
										}
										/* Period available for booking in advance - days */

										if( typeof bookingpress_service_start_date != "undefined" && bookingpress_service_start_date != ''){
											var currentDate3 = vm.get_formatted_date( bookingpress_service_start_date );
											if( currentDate < currentDate3 ){
												return true;
											}
										}
										
										var disable_past_date = Time.getTime() < date.getTime();
										if( disableDates_formatted.indexOf( currentDate ) > -1 ){
											return true;
										}  else {
											return disable_past_date;
										}
									};
									vm.pickerOptions.firstDayOfWeek = parseInt('<?php echo esc_html($bookingpress_start_of_week); ?>');
									let timeSlot = response.data.front_timings;
									vm.reschedule_appointment_time_slot = timeSlot;

									if( false == response.data.prevent_next_month_check ){
										let postDataAction = "bookingpress_get_whole_day_appointments";
										if( true == response.data.check_for_multiple_days_event ){
											postDataAction = "bookingpress_get_whole_day_appointments_multiple_days";
										}
										var postData = { action: postDataAction,days_off_disabled_dates: disableDates, service_id: vm.reschedule_service_id, max_available_year: response.data.max_available_year, max_available_month:response.data.max_available_month,  selected_service:vm.reschedule_service_id, selected_date:vm.appointment_customer_reschedule_booked_date, service_id:vm.reschedule_service_id,_wpnonce:vm.bookingpress_created_nonce, next_month: response.data.next_month,bookingpress_service_expiration_date:bookingpress_service_expiration_date, "counter": 1 };
										vm.bookingpress_mybooking_retrieve_daysoff_for_booked_appointment( postData );
									}
								}
							}
							.bind(this) )
							.catch( function( error ) {
								vm.bookingpress_set_error_msg( error );
							});
						}
						
						setTimeout(function(){
							vm.customer_reschedule_appointment_loader = false;
						},600);						
					}
					.bind(this) )
					.catch( function (error) {
						vm.customer_reschedule_appointment_loader = false;
						vm.bookingpress_set_error_msg(error)
					});
					vm.bookingpress_hide_drawer_overlay("bpa-front-cp-reschedule-mob-drawer");
				},
				bookingpress_mybooking_retrieve_daysoff_for_booked_appointment( postData ){
					const vm = this;
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) ).then( function( response ) {
						let bookingpress_service_expiration_date = postData.bookingpress_service_expiration_date;						
						if(false == response.data.prevent_next_month_check && response.data.counter < 3 ){ /** Currently data will be checked for next 3 months */
							let disableDates = response.data.days_off_disabled_dates;
							let disableDates_arr = disableDates.split(',');

							let disableDates_formatted = [];
							disableDates_arr.forEach(function( date ){
								let formatted_date = vm.get_formatted_date( date );
								disableDates_formatted.push( formatted_date );
							});
							vm.pickerOptions.disabledDate = function(Time){
								let currentDate = new Date( Time );
								
								currentDate = vm.get_formatted_date( currentDate );
								
								var date = new Date();

								date.setDate(date.getDate()-1);								
								var disable_past_date = Time.getTime() < date.getTime();
								if(typeof bookingpress_service_expiration_date !== "undefined" && bookingpress_service_expiration_date != '') {
									var currentDate2 = min_date = vm.get_formatted_date( bookingpress_service_expiration_date);
									/* When Period available for booking in advance - days is less than service expiration date then set limit for that*/
									if(bookingpress_service_expiration_date > vm.get_formatted_date(vm.booking_cal_rec_maxdate)){
										min_date = vm.get_formatted_date(vm.booking_cal_rec_maxdate);
									}
									/* When Period available for booking in advance - days is less than service expiration date then set limit for that*/
									if(currentDate > min_date) {
										return true;
									}
								}
								/* Period available for booking in advance - days */
								if(Time>vm.booking_cal_rec_maxdate && typeof bookingpress_service_expiration_date !== "undefined" && bookingpress_service_expiration_date == '' ) {
									return true;
								}
								/* Period available for booking in advance - days */
								if( disableDates_formatted.indexOf( currentDate ) > -1 ){
									return true;
								} else {
									return disable_past_date;
								}
							};
							postData.next_month = response.data.next_month;
							postData.counter++;
							vm.bookingpress_mybooking_retrieve_daysoff_for_booked_appointment( postData );
						}
					});
				},
				bookingpress_set_time_slot(service_id)
				{
					const vm = this;
					var service_id = service_id;
					var selected_appointment_date = this.appointment_customer_reschedule_booked_date;
					var postData = 
					{ 
						action:'bookingpress_front_get_timings', 
						service_id : service_id,
						selected_date : selected_appointment_date,
						bookingpress_selected_staffmember: {
							selected_staff_member_id: vm.reschedule_staff_id
						},
						bookingpress_form_token:vm.bookingpress_uniq_id, 
						_wpnonce: vm.bookingpress_created_nonce,						
					};
					postData.appointment_data_obj = {
						bookingpress_form_token:vm.bookingpress_uniq_id, 
					};					
					<?php
					do_action('bookingpress_modify_rescheduled_front_timing_xhr'); 
					?>					
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) 
					{
						if(response.data != undefined || response.data != [])
						{								
							vm.reschedule_appointment_time_slot = response.data;
						}
					}
					.bind(this) )
					.catch( function (error) {
						console.log(error);
					});					
				},				
				RescheduleAppointment(apt_status)
				{	
					const vm = this;
					this.$refs['bookingpress_mybooking_rescheduling_form'].validate((valid) => {
                        if (valid) {   
							vm.is_rescheduled_loader = true;							
							var postData = 
							{ 
								action:'bookingpress_reschedule_book_appointment', 
								resche_service_id: this.reschedule_service_id,
								resche_staff_id: this.reschedule_staff_id,
								resche_date: this.appointment_customer_reschedule_booked_date,
								resche_time: this.bookingpress_mybooking_rescheduling_form.appointment_customer_reschedule_booked_time,
								resche_end_time: this.appointment_customer_reschedule_end_time,
								resche_apt_status: apt_status,
								resche_apt_id: this.reschedule_appointment_id,
								bookingpress_uniq_id:vm.bookingpress_uniq_id,
								reschedule_save_wpnonce:vm.bookingpress_created_nonce 
							};
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
							.then( function (response) 
							{	
								vm.is_rescheduled_loader = false;
								if(response.data.variant == 'success' && response.data.update_data > 0){									
									this.open_customer_reschedule_appointment_modal = false;									
									this.loadFrontAppointments();
									vm.bookingpress_set_success_msg(response.data.msg);
									vm.bookingpress_close_reschedule();
									setTimeout(function(){
										vm.is_display_success = "0";
										vm.is_success_msg = "";
									},3000);
								}else{
									this.open_customer_reschedule_appointment_modal = false;
									if(response.data.msg != undefined) {
										vm.bookingpress_set_error_msg(response.data.msg);
									}
									setTimeout(function(){
										vm.is_display_error = "0";
										vm.is_error_msg = "";
									},3000);
								}
							}.bind(this) )
							.catch( function (error) {
								vm.bookingpress_set_error_msg(error)
							});
						}
					});	
				},
				bookingpress_set_rescheduled_end_time(event,time_slot_data) {
					const vm = this;
					if(event != '' && time_slot_data != '') {
						for (let x in time_slot_data) {							
							var slot_data_arr = time_slot_data[x];
							for(let y in slot_data_arr) {
								var time_slot_data_arr = slot_data_arr[y];								
								if(time_slot_data_arr.store_end_time != undefined && time_slot_data_arr.store_end_time != undefined && time_slot_data_arr.store_start_time == event) {   
									vm.appointment_customer_reschedule_end_time = time_slot_data_arr.store_end_time;
								}                                                    
							}                      
						}
					}
				},
				bookingpress_close_reschedule_popup(){
					let is_scroll_locked = document.body.classList.contains('el-popup-parent--hidden') || false;
					if( is_scroll_locked ){
						document.body.classList.remove('el-popup-parent--hidden');
					}
				},
				bookingress_redirect_profile_page(redirect_url){
					window.location.href = redirect_url;
				},
				bookingpress_show_forgot_password(){
					const vm = this;
					vm.bookingpress_reset_forgot_password_form();			
					vm.bookingpress_show_login_form = '0';
					vm.bookingpress_show_forgot_password_form = '1';

				},	
				bookingpress_show_login(){
					const vm = this;
					vm.bookingpress_reset_login_form();				
					vm.bookingpress_show_forgot_password_form = '0';
					vm.bookingpress_show_login_form = '1';					
				},	
				bookingpress_reset_login_form(){
					const vm = this;
					vm.is_display_success = '0';
					vm.is_success_msg = '';	
					vm.is_display_error = '0';
					vm.is_error_msg = '';	
					vm.bookingpress_login_form.bookingpress_username = '';
					vm.bookingpress_login_form.bookingpress_password = '';
				},
				bookingpress_reset_forgot_password_form(){
					const vm = this;
					vm.is_display_success = '0';
					vm.is_success_msg = '';
					vm.is_display_error = '0';
					vm.is_error_msg = '';	
					vm.bookingpress_forgot_password_form.bookingpress_email = '';
				},
				get_formatted_datetime(iso_date) {			
					var __date = new Date(iso_date);
					var hour = __date.getHours();
					var minute = __date.getMinutes();
					var second = __date.getSeconds();

					if (minute < 10) {
						minute = "0" + minute;
					}
					if (second < 10) {
						second = "0" + second;
					}
					var formatted_time = hour + ":" + minute + ":" + second;				
					var __year = __date.getFullYear();
					var __month = __date.getMonth()+1;
					var __day = __date.getDate();
					if (__day < 10) {
						__day = "0" + __day;
					}
					if (__month < 10) {
						__month = "0" + __month;
					}

					var formatted_date = __year+"-"+__month+"-"+__day;
					return formatted_date+" "+formatted_time; 
				},
				bookingpress_get_customer_formatted_datetime(event,field_meta_key,is_enabled_time) {
					if(event != null){
						if(is_enabled_time == true) {
							this.edit_profile_form_data[field_meta_key] = this.get_formatted_datetime(event);
						} else {
							this.edit_profile_form_data[field_meta_key] = this.get_formatted_date(event);
						}
					}
				},
				refundAppointment( currentElement, appointment_id, payment_id,refund_amount,default_refund_amount,mode = 'row_btn' ){
					const vm = this;
					let btn;
					if( 'row_btn' == mode ){
						btn	 = document.querySelector( `[data-btnid="${appointment_id}"]` );
					} else if( 'expanded' == mode ){
						btn = document.querySelector( `[data-expand-id="${appointment_id}"]`);
					} else {
						btn	 = document.querySelector( `[data-btnid="${appointment_id}"]` );
					}

					if( "undefined" == typeof btn ){
						return false;
					}

					let posTop = Math.round( btn.getBoundingClientRect().top );
					let posLeft = Math.round( btn.getBoundingClientRect().left );

					let popupObj = document.querySelector('.bpa-dialog--refund-appointments');
					if( null != popupObj ){
						let posTopStyle = posTop + 40;
						let posLeftStyle = posLeft - 210;

						if( 'expanded' == mode ){
							posTopStyle = posTop + 50;
							posLeftStyle = posLeft - 130;
						}

						popupObj.style.top = posTopStyle + 'px';
						<?php
							if(is_rtl()){
								?>
									posLeftStyle = posLeft + 196;
									popupObj.style.right = posLeftStyle + 'px';
								<?php
							}
							else{
								?>
									popupObj.style.left = posLeftStyle + 'px';
								<?php
							}
						?>
					}

					vm.reset_refund_confirm_model();
					vm.refund_confirm_form.appointment_id = appointment_id;
					vm.refund_confirm_form.payment_id = payment_id;
					vm.refund_confirm_form.refund_amount = refund_amount;
					vm.refund_confirm_form.default_refund_amount = default_refund_amount;					
					vm.bookingpress_refund_confirm_modal = true;
				},
				reset_refund_confirm_model() {
					const vm = this;
					vm.refund_confirm_form.refund_amount = 0;
					vm.refund_confirm_form.default_refund_amount = 0;
					vm.refund_confirm_form.payment_method = '';					
					vm.refund_confirm_form.refund_error_variant = '';
					vm.refund_confirm_form.refund_error_msg = '';
					vm.is_display_refund_loader = '0';
					vm.is_refund_btn_disabled = false;
				},
				close_refund_confirm_model(){
					const vm = this;
					vm.reset_refund_confirm_model();
					vm.bookingpress_refund_confirm_modal = false;
					document.body.classList.remove("el-popup-parent--hidden");
				},
				bookingpress_apply_for_refund(appointment_id) {
					const vm = this;
					vm.is_display_refund_loader = '1';
					vm.is_refund_btn_disabled = true;
					var bkp_wpnonce_pre = vm.bookingpress_created_nonce;
					var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
					if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
					{
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
					}
					else {
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
					}

					var appointment_cancel_data = { action: 'bookingpress_cancel_appointment', cancel_id: appointment_id, _wpnonce: bkp_wpnonce_pre_fetch };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( appointment_cancel_data ) )
					.then(function(response){		
						vm.is_display_refund_loader = '0';
						vm.is_refund_btn_disabled = false;
						if(response.data.variant != 'error'){
							window.location.href = response.data.redirect_url;
						}else{
							vm.refund_confirm_form.refund_error_variant = response.data.variant;
							vm.refund_confirm_form.refund_error_msg = response.data.msg;
						}
					}).catch(function(error){
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
							message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					});
				},
				bookingpress_open_refund_drawer( appointment_id, payment_id,refund_amount,default_refund_amount ){
					const vm = this;
					vm.refund_confirm_form.appointment_id = appointment_id;
					vm.refund_confirm_form.payment_id = payment_id;
					vm.refund_confirm_form.refund_amount = refund_amount;
					vm.refund_confirm_form.default_refund_amount = default_refund_amount;
					vm.bookingpress_refund_appointment_drawer = true;
					vm.bookingpress_hide_drawer_overlay("bpa-front-cp-refund-mob-drawer");
				}, 
				bookingpress_close_refund_drawer(){
					const vm = this;
					vm.bookingpress_refund_appointment_drawer = false;
				},
			<?php
			do_action('bookingpress_pro_add_customer_panel_dynamic_methods');
		}

				
		/**
		 * Main function of book appointment at [bookingpress_form] shortcode
		 *
		 * @return void
		 */
		function bookingpress_book_front_appointment_func($return_data = false) {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_services, $tbl_bookingpress_customer_bookings, $tbl_bookingpress_customers, $bookingpress_pro_payment_gateways, $bookingpress_debug_payment_log_id, $bookingpress_other_debug_log_id;
			
			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking data process starts', 'bookingpress_bookingform', $_REQUEST, $bookingpress_other_debug_log_id );

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
			
			$booking_response = $this->bookingpress_pro_before_book_appointment_func();
			
			if( !empty( $booking_response ) ){
				$booking_response_arr = json_decode( $booking_response, true );
				if(  !empty( $booking_response_arr['variant'] ) && 'error' == $booking_response_arr['variant'] ){
					if(!empty($booking_response_arr['msg'])) {
						$booking_response_arr['msg'] = stripslashes_deep(html_entity_decode($booking_response_arr['msg'],ENT_QUOTES));
					}
					if($return_data){						
						return $booking_response_arr;
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
					if($return_data){
						return $response;
					}
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
					if($return_data){
						return $response;
					}
                    echo json_encode($response);
                    exit;
				}
				
				if ( $payment_gateway == 'on-site' && $bookingpress_service_price > 0 ) {
					$entry_id = ! empty( $bookingpress_return_data['entry_id'] ) ? $bookingpress_return_data['entry_id'] : 0;
					$bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;
					$bookingpress_appointment_status = $BookingPress->bookingpress_get_settings('onsite_appointment_status', 'general_setting');

					if($bookingpress_appointment_status ==  '1' ) {               
                        $bookingpress_pro_payment_gateways->bookingpress_confirm_booking($entry_id, array(), '1', '', '', 1, $bookingpress_is_cart);
                        $bookingpress_redirect_url = $bookingpress_return_data['approved_appointment_url'];
                    } else {                    
                        $bookingpress_pro_payment_gateways->bookingpress_confirm_booking($entry_id, array(), '2', '', '', 1, $bookingpress_is_cart);
                        $bookingpress_redirect_url = $bookingpress_return_data['pending_appointment_url'];
                    }

					if ( ! empty( $bookingpress_redirect_url ) ) {
						$response['variant']       = 'redirect_url';
						$response['title']         = '';
						$response['msg']           = '';
						$response['is_redirect']   = 1;
						$response['redirect_data'] = $bookingpress_redirect_url;
						if($bookingpress_redirection_mode == "in-built"){
							$response['is_transaction_completed'] = 1;
						}
					} else {
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']     = $appointment_booked_successfully;
					}
				} elseif( ($bookingpress_service_price == 0 && $bookingpress_total_price > 0 && isset($bookingpress_appointment_data['tip_amount']) && !empty($bookingpress_appointment_data['tip_amount']))){
					if($payment_gateway == 'on-site'){
						$entry_id = ! empty( $bookingpress_return_data['entry_id'] ) ? $bookingpress_return_data['entry_id'] : 0;
						$bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;
						$bookingpress_appointment_status = $BookingPress->bookingpress_get_settings('onsite_appointment_status', 'general_setting');

						if($bookingpress_appointment_status ==  '1' ) {               
							$bookingpress_pro_payment_gateways->bookingpress_confirm_booking($entry_id, array(), '1', '', '', 1, $bookingpress_is_cart);
							$bookingpress_redirect_url = $bookingpress_return_data['approved_appointment_url'];
						} else {                    
							$bookingpress_pro_payment_gateways->bookingpress_confirm_booking($entry_id, array(), '2', '', '', 1, $bookingpress_is_cart);
							$bookingpress_redirect_url = $bookingpress_return_data['pending_appointment_url'];
						}
						if ( ! empty( $bookingpress_redirect_url ) ) {
							$response['variant']       = 'redirect_url';
							$response['title']         = '';
							$response['msg']           = '';
							$response['is_redirect']   = 1;
							$response['redirect_data'] = $bookingpress_redirect_url;
							if($bookingpress_redirection_mode == "in-built"){
								$response['is_transaction_completed'] = 1;
							}
						} else {
							$response['variant'] = 'success';
							$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
							$response['msg']     = $appointment_booked_successfully;
						}
					}
					else {
						$response = apply_filters( 'bookingpress_' . $payment_gateway . '_submit_form_data', $response, $bookingpress_return_data );
					}
					
					/* echo "<br> response";
					print_r($response); */
				} 
				elseif ( ($bookingpress_service_price === 0 || $bookingpress_total_price === 0 ) ) {
					$entry_id = ! empty( $bookingpress_return_data['entry_id'] ) ? $bookingpress_return_data['entry_id'] : 0;
					$bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;
					$bookingpress_pro_payment_gateways->bookingpress_confirm_booking( $entry_id, array(), '1', '', '', 1, $bookingpress_is_cart);

					$redirect_url                    = $bookingpress_return_data['approved_appointment_url'];
					$bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
					if ( $bookingpress_appointment_status == '2' ) {
						$redirect_url = $bookingpress_return_data['pending_appointment_url'];
					}

					$bookingpress_redirect_url = $redirect_url;
					if ( ! empty( $bookingpress_redirect_url ) ) {
						$response['variant']       = 'redirect_url';
						$response['title']         = '';
						$response['msg']           = '';
						$response['is_redirect']   = 1;
						$response['redirect_data'] = $bookingpress_redirect_url;
						if($bookingpress_redirection_mode == "in-built"){
							$response['is_transaction_completed'] = 1;
						}
					} else {
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']     = $appointment_booked_successfully;
					}
				} else {
					$response = apply_filters( 'bookingpress_' . $payment_gateway . '_submit_form_data', $response, $bookingpress_return_data );
				}

				do_action( 'bookinpgress_after_front_book_appointment', array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['appointment_data'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function

			}

			if( !empty( $_SESSION['cart_timings'] ) ){
				$_SESSION['cart_timings'] = array();
			}
			$_SESSION['disable_dates'] = array();
			$_SESSION['front_timings'] = array();

			/** Delete transient if the appointment processed successfully */
			delete_transient( $authorization_token );
			if($return_data){

				$entry_id = !empty( $bookingpress_return_data['entry_id'] ) ? $bookingpress_return_data['entry_id'] : 0;
				$bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;

				$response['entry_id'] = '';
				$response['appointment_book'] = 0;
				$response['order_id'] = '';
				$response['is_cart'] = 0;
				$response['thankyou_page_data'] = array();
				$response['redirect_data'] = array();
				$is_card_payment =  apply_filters( 'bookingpress_check_payment_gateway_support_card_payment', 0, $payment_gateway );				
				$response['appointment_book'] = ($is_card_payment || ($payment_gateway == 'on-site' && $bookingpress_service_price > 0 ) || ($bookingpress_service_price === 0 || $bookingpress_total_price === 0 ))?1:0;				
				$response['entry_id'] = (!$bookingpress_is_cart)?$entry_id:'';
				$response['is_cart'] = $bookingpress_is_cart;
				$response['order_id'] = ($bookingpress_is_cart)?$entry_id:'';
				if($response['appointment_book']){
					$thankyou_data = $this->bookingpress_bpa_get_thankyou_detail_func(array('entry_id'=>$response['entry_id'],'order_id'=>$response['order_id']));
					if($thankyou_data['status'] == 1){
						$thankyou_detail = (isset($thankyou_data['response']['result']['appointment_detail']))?$thankyou_data['response']['result']['appointment_detail']:'';
						if(!empty($thankyou_detail)){
							$response['thankyou_page_data'] = $thankyou_detail;
						}
					}
				}else{
					$response['redirect_data'] = $bookingpress_return_data;
				}								
				return $response;
			}
			echo wp_json_encode( $response );
			exit;
		}
		
		/**
		 * Function before appointment book at frontend
		 *
		 * @return void
		 */
		function bookingpress_pro_before_book_appointment_func(){
			global $wpdb, $tbl_bookingpress_coupons, $BookingPress, $bookingpress_coupons, $bookingpress_other_debug_log_id, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs,$tbl_bookingpress_customers, $bookingpress_pro_services,$bookingpress_payment_gateways, $tbl_bookingpress_form_fields;

			$response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant']      = 'error';
                $response['title']        = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']          = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                return wp_json_encode($response);
            }
            $response['variant']    = 'success';
            $response['title']      = '';
            $response['msg']        = '';
            $response['error_type'] = '';
			
			if( !empty( $_POST['appointment_data'] ) && !is_array( $_POST['appointment_data'] ) ){
				$_POST['appointment_data'] = json_decode( stripslashes_deep( $_POST['appointment_data'] ), true ); //phpcs:ignore
				$_POST['appointment_data'] =  !empty($_POST['appointment_data']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['appointment_data'] ) : array(); // phpcs:ignore				
			}
			

			$posted_data = !empty($_POST) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST) : array();
			$bookingpress_version = get_option('bookingpress_version');
			if( !empty( $bookingpress_version ) && version_compare( $bookingpress_version, '1.0.66', '<' ) && (session_id() == '' OR session_status() === PHP_SESSION_NONE) ) {
				session_start();
			}

			$bookingpress_form_token = !empty( $posted_data['appointment_data']['bookingpress_form_token'] ) ? sanitize_text_field( $posted_data['appointment_data']['bookingpress_form_token'] ) : sanitize_text_field( $posted_data['appointment_data']['bookingpress_uniq_id'] ) ;

			
			$appointment_selected_date = (isset($posted_data['appointment_data']['selected_date']) && !empty($posted_data['appointment_data']['selected_date']))?date('Y-m-d', strtotime(sanitize_text_field($posted_data['appointment_data']['selected_date']))):'';
			

            $bpa_front_timings_key = 'bpa_front_timings_' .$bookingpress_form_token.'_'.$appointment_selected_date;
			if( version_compare( $bookingpress_version, '1.0.82', '<' ) ){
				$bpa_front_timings_data = get_transient( $bpa_front_timings_key );
			} else {
				$bpa_front_timings_data = $this->bookingpress_get_transient( $bpa_front_timings_key );
			}

            if(is_plugin_active('bookingpress-cart/bookingpress-cart.php')){
                $bookingpress_cart_version = get_option( 'bookingpress_cart_module' );
                if( version_compare( $bookingpress_cart_version, '2.2', '<' ) ){
                    session_start();						
					$bpa_front_timings_data = $_SESSION['front_timings']; 
                }
            }

			if( empty( $bpa_front_timings_data ) ){
				$bpa_front_timings_data = array();
			}

			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking validation posted data', 'bookingpress_bookingform', array_merge( $bpa_front_timings_data, $_REQUEST ), $bookingpress_other_debug_log_id );

			$no_service_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_service_selected_for_the_booking', 'message_setting');
			$no_appointment_date_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_appointment_date_selected_for_the_booking', 'message_setting');
			$no_appointment_time_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_appointment_time_selected_for_the_booking', 'message_setting');
			$no_payment_method_is_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_payment_method_is_selected_for_the_booking', 'message_setting');
			$duplicate_email_address_found = $BookingPress->bookingpress_get_settings('duplicate_email_address_found', 'message_setting');
			$unsupported_currecy_selected_for_the_payment = $BookingPress->bookingpress_get_settings('unsupported_currecy_selected_for_the_payment', 'message_setting');
			$duplidate_appointment_time_slot_found = $BookingPress->bookingpress_get_settings('duplidate_appointment_time_slot_found', 'message_setting');

			$all_fields = $wpdb->get_results( $wpdb->prepare("SELECT bookingpress_field_meta_key,bookingpress_field_error_message,bookingpress_form_field_name,bookingpress_field_is_default,bookingpress_field_options,bookingpress_field_type FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_required = %d", 1) );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm

			wp_cache_set( 'bookingpress_validate_form_fields_data_' . $bookingpress_form_token, $all_fields, '', HOUR_IN_SECONDS );
			
			/* $all_required_fields = array(); */
			$service_visibility_field_arr = array();
			$field_validation_message = array();
			if ( ! empty( $all_fields ) ) {
				$is_required_validation = false;
				foreach ( $all_fields as $field_data ) {
					
					if( 'password' == $field_data->bookingpress_field_type && is_user_logged_in() ){
						continue;
					}

					$field_error_msg = $field_data->bookingpress_field_error_message;

					$field_options = json_decode($field_data->bookingpress_field_options, true);
					$bookingpress_selected_service = $field_options['selected_services'];   
					if( $field_options['visibility'] == 'always' &&  $field_data->bookingpress_form_field_name != '2 Col' && $field_data->bookingpress_form_field_name != '3 Col' && $field_data->bookingpress_form_field_name != '4 Col' ){
						$bpa_visible_field_key = '';
						if( $field_data->bookingpress_field_is_default == 1 ){
							if( $field_data->bookingpress_form_field_name == 'firstname'){
								$bpa_visible_field_key = 'customer_firstname';		
							}
							if( $field_data->bookingpress_form_field_name == 'lastname'){
								$bpa_visible_field_key = 'customer_lastname';		
							}
							if( $field_data->bookingpress_form_field_name == 'email_address'){
								$bpa_visible_field_key = 'customer_email';		
							}
							if( $field_data->bookingpress_form_field_name == 'note'){
								$bpa_visible_field_key = 'appointment_note';		
							}
							if( $field_data->bookingpress_form_field_name == 'phone_number'){
								$bpa_visible_field_key = 'customer_phone';		
							}
							if( $field_data->bookingpress_form_field_name == 'fullname'){
								$bpa_visible_field_key = 'customer_name';		
							}
							if( $field_data->bookingpress_form_field_name == 'username'){
								$bpa_visible_field_key = 'customer_username';		
							}
							if( $field_data->bookingpress_form_field_name == 'terms_and_conditions'){
								$bpa_visible_field_key = 'appointment_terms_conditions';		
							}
						} else {
							$bpa_visible_field_key = $field_data->bookingpress_field_meta_key;
						}

						if( 'password' == $field_data->bookingpress_field_type && empty( $posted_data['appointment_data']['form_fields'][$bpa_visible_field_key] ) ){
							continue;
						}

						$val = $posted_data['appointment_data']['form_fields'][ $bpa_visible_field_key ];

						if( $bpa_visible_field_key == 'appointment_terms_conditions'){

							if( empty($val[0])){
								$is_required_validation = true;
								$field_validation_message[] = $field_error_msg;
							}
						} else {
							if( '' === $val ){
								$is_required_validation = true;
								$field_validation_message[] = $field_error_msg;
							}
						}
					}

					if( $field_options['visibility'] == 'services' &&  $field_data->bookingpress_form_field_name != '2 Col' && $field_data->bookingpress_form_field_name != '3 Col' && $field_data->bookingpress_form_field_name != '4 Col' ){
						$bookingpress_field_meta_key_val = $field_data->bookingpress_field_meta_key;

						if( $field_data->bookingpress_field_is_default == 1 ){
							if( $field_data->bookingpress_form_field_name == 'firstname'){
								$bookingpress_field_meta_key_val = 'customer_firstname';		
							}
							if( $field_data->bookingpress_form_field_name == 'lastname'){
								$bookingpress_field_meta_key_val = 'customer_lastname';		
							}
							if( $field_data->bookingpress_form_field_name == 'email_address'){
								$bookingpress_field_meta_key_val = 'customer_email';		
							}
							if( $field_data->bookingpress_form_field_name == 'note'){
								$bookingpress_field_meta_key_val = 'appointment_note';		
							}
							if( $field_data->bookingpress_form_field_name == 'phone_number'){
								$bookingpress_field_meta_key_val = 'customer_phone';		
							}
							if( $field_data->bookingpress_form_field_name == 'fullname'){
								$bookingpress_field_meta_key_val = 'customer_name';		
							}
							if( $field_data->bookingpress_form_field_name == 'username'){
								$bookingpress_field_meta_key_val = 'customer_username';		
							}
							if( $field_data->bookingpress_form_field_name == 'terms_and_conditions'){
								$bookingpress_field_meta_key_val = 'appointment_terms_conditions';		
							}
						} else {
							$bookingpress_field_meta_key_val = $field_data->bookingpress_field_meta_key;
						}
						$service_visibility_field_arr[$bookingpress_field_meta_key_val] = $bookingpress_selected_service;
					}
				}

			}
			

			if( true == $is_required_validation ){
				$response['variant'] = 'error';
				$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
				$response['msg']     = !empty($field_validation_message) ? implode(',', $field_validation_message) : array();
				return wp_json_encode($response);	
			}
			if( !empty( $posted_data ) ){
				if (empty($posted_data['appointment_data']['selected_service']) ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = $no_service_selected_for_the_booking;
					return wp_json_encode($response);
				}

				if (empty($posted_data['appointment_data']['selected_date']) ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = $no_appointment_date_selected_for_the_booking;
					return wp_json_encode($response);
				}

				if ((empty($posted_data['appointment_data']['selected_start_time']) || empty($posted_data['appointment_data']['selected_end_time'])) && $posted_data['appointment_data']['selected_service_duration_unit'] != 'd') {
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = $no_appointment_time_selected_for_the_booking;
					return wp_json_encode($response);
				}
				
				$bookingpress_service_price = isset($_REQUEST['appointment_data']['service_price_without_currency']) ? floatval($_REQUEST['appointment_data']['service_price_without_currency']) : 0;

				$bookingpress_total_amount = isset($_REQUEST['appointment_data']['total_payable_amount']) ? floatval($_REQUEST['appointment_data']['total_payable_amount']) : 0;
				$bpa_check_payment_method = apply_filters( 'bookingpress_modify_check_payment_method', true, $posted_data );
				if (empty($posted_data['appointment_data']['selected_payment_method']) && $bookingpress_service_price > 0 && $bookingpress_total_amount > 0 && $bpa_check_payment_method) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = $no_payment_method_is_selected_for_the_booking;
					return wp_json_encode($response);
				}

				$bookingpress_fullname  = ! empty($posted_data['appointment_data']['customer_name']) ? trim(sanitize_text_field($posted_data['appointment_data']['customer_name'])) : '';
				$bookingpress_firstname = ! empty($posted_data['appointment_data']['customer_firstname']) ? trim(sanitize_text_field($posted_data['appointment_data']['customer_firstname'])) : '';
				$bookingpress_lastname  = ! empty($posted_data['appointment_data']['customer_lastname']) ? trim(sanitize_text_field($posted_data['appointment_data']['customer_lastname'])) : '';
				$bookingpress_email     = ! empty($posted_data['appointment_data']['customer_email']) ? sanitize_email($posted_data['appointment_data']['customer_email']) : '';

				if (strlen($bookingpress_fullname) > 255 ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = esc_html__('Fullname is too long...', 'bookingpress-appointment-booking');
					return wp_json_encode($response);
				}
				if (strlen($bookingpress_firstname) > 255 ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = esc_html__('Firstname is too long...', 'bookingpress-appointment-booking');
					return wp_json_encode($response);
				}
				if (strlen($bookingpress_lastname) > 255 ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = esc_html__('Lastname is too long...', 'bookingpress-appointment-booking');
					return wp_json_encode($response);
				}
				if (strlen($bookingpress_email) > 255 ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = esc_html__('Email address is too long...', 'bookingpress-appointment-booking');
					return wp_json_encode($response);
				}

				$bookingpress_selected_payment_method = sanitize_text_field($posted_data['appointment_data']['selected_payment_method']);
				$bookingpress_currency_name           = $BookingPress->bookingpress_get_settings('payment_default_currency', 'payment_setting');
				$bookingpress_paypal_currency = $bookingpress_payment_gateways->bookingpress_paypal_supported_currency_list();            				
				$bookingpress_is_support = 1;
				if ($bookingpress_selected_payment_method == 'paypal' && !in_array($bookingpress_currency_name,$bookingpress_paypal_currency ) ) {
					$bookingpress_is_support = 0;
				} else {					
					$bookingpress_is_support = apply_filters('bookingpress_pro_validate_currency_before_book_appointment',$bookingpress_is_support,$bookingpress_selected_payment_method,$bookingpress_currency_name);
				}
				if($bookingpress_is_support == 0){
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = esc_html($unsupported_currecy_selected_for_the_payment);
					return wp_json_encode($response);
				}

				//Validate coupon code if coupon applied
				$coupon_code                   = ! empty( $posted_data['appointment_data']['coupon_code'] ) ? $posted_data['appointment_data']['coupon_code'] : '';
				$bookingpress_selected_service = ! empty( $posted_data['appointment_data']['selected_service'] ) ? $posted_data['appointment_data']['selected_service'] : 0;
				if ( ! empty( $coupon_code ) ) {
					$bookingpress_applied_coupon_response = $bookingpress_coupons->bookingpress_apply_coupon_code( $coupon_code, $bookingpress_selected_service );
					if ( is_array( $bookingpress_applied_coupon_response ) && ! empty( $bookingpress_applied_coupon_response['coupon_status'] ) && ( $bookingpress_applied_coupon_response['coupon_status'] == 'error' ) ) {
						$response['variant'] = 'error';
						$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
						$response['msg']     = $bookingpress_applied_coupon_response['msg'];
						return wp_json_encode( $response );
					}
				}
			} else {
				if (empty($posted_data['appointment_data']['selected_payment_method']) ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = $no_payment_method_is_selected_for_the_booking;
					return wp_json_encode($response);
				}
			}

			do_action('bookingpress_validate_booking_form', $posted_data);
			
			$bookingpress_validate_only_booking_form = apply_filters( 'bookingpress_validate_only_booking_form','',$posted_data );
			if($bookingpress_validate_only_booking_form){
				return $bookingpress_validate_only_booking_form;
			}

			if(!empty($posted_data) && empty($posted_data['appointment_data']['cart_items']) ){
				$bookingpress_service_price = isset($_REQUEST['appointment_data']['service_price_without_currency']) ? floatval($_REQUEST['appointment_data']['service_price_without_currency']) : 0;

				/* serivce extra validation start */
				if( !empty( $posted_data['appointment_data']['selected_service']) && !empty($posted_data['appointment_data']['bookingpress_selected_extra_details']) ){
					foreach( $posted_data['appointment_data']['bookingpress_selected_extra_details'] as $key=>$val ){
						if($val['bookingpress_is_selected'] == 'true'){
							if($val['bookingpress_service_id'] != $posted_data['appointment_data']['selected_service']){
								$response['variant'] = 'error';
								$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
								$response['msg']     = "selected extras not associated with selected service";
								return wp_json_encode($response);
							}
						}
					}
				}
				/* serivce extra validation end */

				$appointment_service_id    = intval($posted_data['appointment_data']['selected_service']);
				$appointment_selected_date = date('Y-m-d', strtotime(sanitize_text_field($posted_data['appointment_data']['selected_date'])));
				$appointment_start_time    = date('H:i:s', strtotime(sanitize_text_field($posted_data['appointment_data']['selected_start_time'])));
				$appointment_end_time      = date('H:i:s', strtotime(sanitize_text_field($posted_data['appointment_data']['selected_end_time'])));

				/** Check if the selected date is over the available book time */
				$get_period_available_for_booking = $BookingPress->bookingpress_get_settings('period_available_for_booking', 'general_setting');
				$bpa_current_date_check = date( 'Y-m-d', current_time('timestamp') );
				$date1 = new DateTime( $bpa_current_date_check );
				$date2 = new DateTime( $appointment_selected_date );
				
				$interval = $date1->diff($date2);
				$interval_in_days = $interval->days;
				if( !empty( $interval_in_days ) && $interval_in_days > $get_period_available_for_booking ){
					$response['variant'] = 'error';
					$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$response['msg'] = esc_html__( "Sorry, Booking can not be done as the selected date is after the maximum available date", "bookingpress-appointment-booking" );
					//Sorry, Booking can not be done as minimum required time before booking is already passed
					return wp_json_encode( $response );
				}

				$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

				if( !empty( $bookingpress_timeslot_display_in_client_timezone ) && 'true' == $bookingpress_timeslot_display_in_client_timezone ){
					
					$client_offset = !empty($posted_data['appointment_data']['client_offset']) ? sanitize_text_field( $posted_data['appointment_data']['client_offset'] ) : '';
					

					$appointment_selected_date = !empty( $posted_data['appointment_data']['store_selected_date'] ) ? $posted_data['appointment_data']['store_selected_date'] : $appointment_selected_date;
					$appointment_start_time = !empty( $posted_data['appointment_data']['store_start_time'] ) ? sanitize_text_field( $posted_data['appointment_data']['store_start_time'] ) : $appointment_start_time;
					$appointment_end_time = !empty( $posted_data['appointment_data']['store_end_time'] ) ? sanitize_text_field( $posted_data['appointment_data']['store_end_time'] ) : $appointment_end_time;
					
				}				
				
				$bookingpress_selected_staffmember_id = 0;
				$bookingpress_selected_staffmember_id = sanitize_text_field($posted_data['appointment_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id']);

				// If payment gateway is disable then return error
				if ($bookingpress_selected_payment_method == 'on-site' && $bookingpress_service_price > 0 ) {
					$on_site_payment = $BookingPress->bookingpress_get_settings('on_site_payment', 'payment_setting');
					if (empty($on_site_payment) || ( $on_site_payment == 'false' ) ) {
						$response['variant'] = 'error';
						$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
						$response['msg']     = __('On-site payment gateway is not active', 'bookingpress-appointment-booking') . '.';
						return wp_json_encode($response);
					}
				} elseif ($bookingpress_selected_payment_method == 'paypal' && $bookingpress_service_price > 0 ) {
					$paypal_payment = $BookingPress->bookingpress_get_settings('paypal_payment', 'payment_setting');
					if (empty($paypal_payment) || ( $paypal_payment == 'false' ) ) {
						$response['variant'] = 'error';
						$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
						$response['msg']     = __('PayPal payment gateway is not active', 'bookingpress-appointment-booking') . '.';
						return wp_json_encode($response);
					}

					if ($bookingpress_service_price < floatval('0.1') ) {
						$response['variant'] = 'error';
						$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
						$response['msg']     = esc_html__('Paypal supports minimum amount 0.1', 'bookingpress-appointment-booking');
						return wp_json_encode($response);
					}
				}

				if( !empty( $service_visibility_field_arr ) ){
					$is_visible_field_validation = false;
					foreach( $service_visibility_field_arr as $visible_field_key => $visible_field_value ){
						if( empty( $posted_data['appointment_data']['form_fields'][ $visible_field_key ] ) && in_array( $posted_data['appointment_data']['selected_service'], $visible_field_value ) ){
							$is_visible_field_validation = true;
							$field_validation_message[] = $field_error_msg;
						}
					}

					if( true == $is_visible_field_validation ){
						$response['variant'] = 'error';
						$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
						$response['msg']     = !empty($field_validation_message) ? implode(',', $field_validation_message) : array();
						return wp_json_encode($response);
					}
				}

				// If selected date is day off then display error.
				$bookingpress_search_query              = preg_quote($appointment_selected_date, '~');
				$bookingpress_get_default_daysoff_dates = $BookingPress->bookingpress_get_default_dayoff_dates( '', '', $appointment_service_id, $bookingpress_selected_staffmember_id );

				$bookingpress_appointment_data = !empty($_POST['appointment_data']) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data'] ) : array(); // phpcs:ignore
				$bookingpress_get_default_daysoff_dates = apply_filters('bookingpress_modify_disable_dates', $bookingpress_get_default_daysoff_dates, $appointment_service_id, date( 'Y-m-d', current_time('timestamp') ), $bookingpress_appointment_data);

				$bookingpress_get_default_daysoff_dates = apply_filters( 'bookingpress_modify_disable_dates_with_staffmember', $bookingpress_get_default_daysoff_dates, $appointment_service_id);
				

				//$bookingpress_get_default_daysoff_dates = $_SESSION['disable_dates']; // Dimple changes 26jul2022 Need to check with Azhar why selected added to the array
				$bookingpress_search_date               = preg_grep('~' . $bookingpress_search_query . '~', $bookingpress_get_default_daysoff_dates);

				if (! empty($bookingpress_search_date) ) {
					$booking_dayoff_msg     = esc_html__('Selected date is off day', 'bookingpress-appointment-booking');
					$booking_dayoff_msg    .= '. ' . esc_html__('So please select new date', 'bookingpress-appointment-booking') . '.';
					$response['error_type'] = 'dayoff';
					$response['variant']    = 'error';
					$response['title']      = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']        = $booking_dayoff_msg;
					return wp_json_encode($response);
				}

				/** Double Confirmation for the already booked appointment with the same as well as crossed date & time start */
				$is_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($appointment_service_id, $appointment_selected_date, $appointment_start_time, $appointment_end_time, 0, false, $posted_data );
				
				if( !empty( $is_booked_appointment ) ){
					$response = json_decode( $is_booked_appointment );
					
					return wp_json_encode($response);
				}
				/** Double Confirmation for the already booked appointment with the same as well as crossed date & time end */


				// Minimum time required before booking Dimple changes 26thJuly2022 
				// NEED TO CHECK THIS WITH TIMEZONE SETTINGS // reputelog need to check by Gaurav

				$minimum_time_required = ''; 
				$minimum_time_required = 'disabled'; // get here timings
            	$minimum_time_required = apply_filters( 'bookingpress_retrieve_minimum_required_time', $minimum_time_required, $appointment_service_id );				

				if( 'disabled' != $minimum_time_required ){
					$bookingpress_slot_start_datetime       = $appointment_selected_date . ' ' . $appointment_start_time;
					$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
					$bookingpress_time_diff = round( abs( current_time('timestamp') - $bookingpress_slot_start_time_timestamp ) / 60, 2 );					
					//echo $bookingpress_time_diff; exit;
					/*  Add Day Service Validation For minimum time required */					
					$bookingpress_service_duration_unit = isset($bookingpress_appointment_data['selected_service_duration_unit'])?$bookingpress_appointment_data['selected_service_duration_unit']:'';										
					if($bookingpress_service_duration_unit == 'd'){
						if($minimum_time_required >= 1440){
							$bookingpress_time_diff = round( abs( strtotime(date('Y-m-d')) - $bookingpress_slot_start_time_timestamp ) / 60, 2 )+5;
						}																			
					}
					if( $bookingpress_time_diff <= $minimum_time_required ){
						// display error here
						$response['variant']              = 'error';
						$response['title']                = 'Error';
						$response['msg']                  = esc_html__("Sorry, Booking can not be done as minimum required time before booking is already passed", "bookingpress-appointment-booking");
						return wp_json_encode($response);
					}
				}

				// double confirm the timings
				if( !empty( $bookingpress_version ) && version_compare( $bookingpress_version, '1.0.66', '<' ) ){
					$timings = array_values( $_SESSION['front_timings'] );
				} else {
					$timings = array_values( $bpa_front_timings_data );
				}

				$response['timings'] = $timings;
				
				$appointment_start_time = date('H:i',strtotime($appointment_start_time));
				$appointment_end_time = ($appointment_end_time != '00:00:00' && $appointment_end_time != '24:00' ) ? date('H:i',strtotime($appointment_end_time)) : '24:00';
				
				$response['appointment_start_time'] = $appointment_start_time;
				$response['appointment_end_time'] = $appointment_end_time;

				$time_slot_start_key = array_search($appointment_start_time, array_column( $timings, 'store_start_time' ) );
				$time_slot_end_key = array_search( $appointment_end_time, array_column( $timings, 'store_end_time' ) );

				if( ( trim($time_slot_start_key) === '' || trim($time_slot_end_key) === '' || trim($time_slot_end_key) != trim($time_slot_start_key) ) && 'd' != $posted_data['appointment_data']['selected_service_duration_unit'] ){
					$response['tstart_key' ] = $time_slot_start_key;
					$response['tend_key' ] = $time_slot_end_key;
					$response['variant']              = 'error';
					$response['title']                = 'Error';
					$response['msg']                  = esc_html__("Sorry, Booking can not be done as booking time is different than selected timeslot", "bookingpress-appointment-booking");
					return wp_json_encode($response);
				}

				// check Tax
				global $bookingpress_pro_payment_gateways;
				$check_tax_and_deposit = $bookingpress_pro_payment_gateways->bookingpress_recalculate_appointment_data_func($posted_data);

				$validation_response = json_decode($check_tax_and_deposit,true);

				$confirmed_data = $validation_response["appointment_data"]["appointment_data"]; 
				$confirmed_tax_amount = !empty( $confirmed_data["tax_amount"] ) ? $confirmed_data["tax_amount"] : 0;
				$old_tax_amount = !empty( $posted_data['appointment_data']['tax_amount'] ) ? $posted_data['appointment_data']['tax_amount'] : 0;

				if($confirmed_tax_amount != $old_tax_amount)
				{
						$response['variant']              = 'error';
						$response['title']                = 'Error';
						$response['msg']                  = esc_html__("Sorry, Booking can not be done as tax amount is incorrect", "bookingpress-appointment-booking");
						return wp_json_encode($response);
				}

				// check Deposit
				// I am not getting deposit amount at all.. so need to revalidate once I get it from posted data
				
			} else if(!empty($posted_data) && !empty($posted_data['appointment_data']['cart_items']) ) {
				$cart_items = $posted_data['appointment_data']['cart_items'];
				$bookingpress_service_price = 0;
				foreach( $cart_items as $cindex => $cart_item ){

					$bookingpress_service_price = $bookingpress_service_price + $cart_item['service_price_without_currency'];
					
					$appointment_service_id = intval( $cart_item['bookingpress_service_id'] );
					$appointment_selected_date = $cart_item['bookingpress_selected_date'];
					$appointment_start_time = $cart_item['bookingpress_selected_start_time'];
					$appointment_end_time = $cart_item['bookingpress_selected_end_time'];

					$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

					/* serivce extra validation start */
					if( (!empty( $cart_item['bookingpress_service_id'])) && (!empty($cart_item['service_extras']) && !empty($cart_item['bookingpress_selected_extras_details']))  ){
						foreach( $cart_item['service_extras'] as $key=>$val ) {	
							foreach( $cart_item['bookingpress_selected_extras_details'] as $selected_extra_service_key=>$selected_extra_serivce_val ){
								if($selected_extra_serivce_val['extra_service_id'] == $val['bookingpress_extra_services_id']){
									if($val['bookingpress_service_id'] != $cart_item['bookingpress_service_id']){
										$response['variant'] = 'error';
										$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
										$response['msg']     = "selected extras not associated with selected service";
										return wp_json_encode($response);
										
									} 
								}
							}
						}
					}
					/* serivce extra validation end */

					if( !empty( $service_visibility_field_arr ) ){
						$is_visible_field_validation = false;
						foreach( $service_visibility_field_arr as $visible_field_key => $visible_field_value ){
							if( empty( $posted_data['appointment_data']['form_fields'][ $visible_field_key ] ) && in_array( $appointment_service_id, $visible_field_value ) ){
								$is_visible_field_validation = true;
								$field_validation_message[] = $field_error_msg;
							}
						}
	
						if( true == $is_visible_field_validation ){
							$response['variant'] = 'error';
							$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
							$response['msg']     = !empty($field_validation_message) ? implode(',', $field_validation_message) : array();
							return wp_json_encode($response);
						}
					}

					if( !empty( $bookingpress_timeslot_display_in_client_timezone ) && 'true' == $bookingpress_timeslot_display_in_client_timezone ){
						$appointment_selected_date = !empty( $cart_item['bookingpress_store_selected_date'] ) ? $cart_item['bookingpress_store_selected_date'] : $appointment_selected_date;
						$appointment_start_time = !empty( $cart_item['bookingpress_store_start_time'] ) ? sanitize_text_field( $cart_item['bookingpress_store_start_time'] ) : $appointment_start_time;
						$appointment_end_time = !empty( $cart_item['bookingpress_store_end_time'] ) ? sanitize_text_field( $cart_item['bookingpress_store_end_time'] ) : $appointment_end_time;
					}

					$bookingpress_selected_staffmember_id = 0;
					$bookingpress_selected_staffmember_id = sanitize_text_field($cart_item['bookingpress_selected_staffmember']);

					/** If selected date for the cart item is in the day off then display error. */
					$bookingpress_search_query              = preg_quote($appointment_selected_date, '~');
					$bookingpress_get_default_daysoff_dates = $BookingPress->bookingpress_get_default_dayoff_dates();
					//$bookingpress_get_default_daysoff_dates = $_SESSION['disable_dates']; // Dimple changes 26jul2022 Need to check with Azhar why selected added to the array

					$bookingpress_get_default_daysoff_dates = apply_filters('bookingpress_modify_disable_dates', $bookingpress_get_default_daysoff_dates, $appointment_service_id, date( 'Y-m-d', current_time('timestamp') ), $cart_item);

					$bookingpress_get_default_daysoff_dates = apply_filters( 'bookingpress_modify_disable_dates_with_staffmember', $bookingpress_get_default_daysoff_dates, $appointment_service_id);

					$bookingpress_search_date               = preg_grep('~' . $bookingpress_search_query . '~', $bookingpress_get_default_daysoff_dates);

					if (! empty($bookingpress_search_date) ) {
						$booking_dayoff_msg     = $appointment_selected_date . ' ' .esc_html__('is off day', 'bookingpress-appointment-booking');
						$booking_dayoff_msg    .= '. ' . esc_html__('So please select new date', 'bookingpress-appointment-booking') . '.';
						$response['error_type'] = 'dayoff';
						$response['variant']    = 'error';
						$response['title']      = esc_html__('Error', 'bookingpress-appointment-booking');
						$response['msg']        = $booking_dayoff_msg;
						return wp_json_encode($response);
					}

										
					if(isset($cart_item['bookingpress_selected_staffmember']) && $cart_item['bookingpress_selected_staffmember']){						
						$_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] = $cart_item['bookingpress_selected_staffmember'];
					}

					// This is to double confirm the time slot is already booked. I think this logic should be called from the function $BookingPress->bookingpress_is_appointment_booked. Need to check with Azhar Dimple changes 26jul2022
					$is_booked_appointment = $BookingPress->bookingpress_is_appointment_booked($appointment_service_id, $appointment_selected_date, $appointment_start_time, $appointment_end_time, 0, false, $posted_data );
				
					if( !empty( $is_booked_appointment ) ){
						$response = json_decode( $is_booked_appointment );

						return wp_json_encode($response);
					}

					$minimum_time_required = ''; 
					$minimum_time_required = 'disabled'; // get here timings
					$minimum_time_required = apply_filters( 'bookingpress_retrieve_minimum_required_time', $minimum_time_required, $appointment_service_id );

					if( 'disabled' != $minimum_time_required ){
						$bookingpress_slot_start_datetime       = $appointment_selected_date . ' ' . $appointment_start_time;
						$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
						$bookingpress_time_diff = round( abs( current_time('timestamp') - $bookingpress_slot_start_time_timestamp ) / 60, 2 );					
						//echo $bookingpress_time_diff; exit;
						/*  Add Day Service Validation For minimum time required */
						$bookingpress_service_duration_unit = isset($cart_item['bookingpress_service_duration_unit'])?$cart_item['bookingpress_service_duration_unit']:'';
						if($bookingpress_service_duration_unit == 'd'){	
							if($minimum_time_required >= 1440){
								$bookingpress_time_diff = round( abs( strtotime(date('Y-m-d')) - $bookingpress_slot_start_time_timestamp ) / 60, 2 )+5;							
							}
						}

						if( $bookingpress_time_diff <= $minimum_time_required ){
							// display error here
							$response['variant']              = 'error';
							$response['title']                = 'Error';
							$response['msg']                  = esc_html__("Sorry, Booking can not be done as minimum required time before booking is already passed", "bookingpress-appointment-booking");
							return wp_json_encode($response);
						}
					}
					
					// double confirm the timings
					if( !empty( $bookingpress_version ) && version_compare( $bookingpress_version, '1.0.66', '<' ) && !empty( $_SESSION['cart_timings'] ) ){
						$timings = array_values($_SESSION['cart_timings'][$cindex]);
					} else {
						$bpa_cart_front_timings_key = 'bpa_cart_front_timings_' .$bookingpress_form_token;
						
						$cart_timings = get_transient( $bpa_cart_front_timings_key );
						if(is_plugin_active('bookingpress-cart/bookingpress-cart.php')){
							$bookingpress_cart_version = get_option( 'bookingpress_cart_module' );
							if( version_compare( $bookingpress_cart_version, '2.2', '<' ) ){
								session_start();
								if(isset($_SESSION['cart_timings'])){
									$cart_timings =   $_SESSION['cart_timings'];
								}								
							} else if( version_compare( $bookingpress_cart_version, '2.6', '>' ) ){
								$cart_timings = $this->bookingpress_get_transient( $bpa_cart_front_timings_key );
							}
						}	
						if(isset($cart_timings[ $cindex ]) && is_array($cart_timings[ $cindex ])){
							$timings = array_values( $cart_timings[ $cindex ] );
						}else{
							$timings = array();
						}
					}
					$appointment_end_time = $appointment_end_time != '24:00' ? date('H:i',strtotime($appointment_end_time)) : '24:00';
					$time_slot_start_key = array_search( date('H:i',strtotime($appointment_start_time)), array_column( $timings, 'store_start_time' ) );
					$time_slot_end_key = array_search($appointment_end_time, array_column( $timings, 'store_end_time' ) );

					$bookingpress_service_duration_unit = (isset($cart_item['bookingpress_service_duration_unit']))?$cart_item['bookingpress_service_duration_unit']:'';
					if((trim($time_slot_start_key) === '' || trim($time_slot_end_key) === '' || trim($time_slot_end_key) != trim($time_slot_start_key) ) && 'd' != $bookingpress_service_duration_unit){
						$response['timings' ]			  = $timings;
						$response['variant']              = 'error';
						$response['title']                = 'Error';
						$response['msg']                  = esc_html__("Sorry, Booking can not be done as booking time is different than selected timeslot", "bookingpress-appointment-booking");
						return wp_json_encode($response);
					}
				}
			} else{
				if (empty($posted_data['appointment_data']['selected_payment_method']) ) {
					$response['variant'] = 'error';
					$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg']     = $no_payment_method_is_selected_for_the_booking;
					return wp_json_encode($response);
				}
			}

			
			return wp_json_encode($response);
		}
		
		/**
		 * Function for convert client datetime to server datetime
		 *
		 * @param  mixed $datetime
		 * @param  mixed $client_offset
		 * @return void
		 */
		function bookingpress_convert_client_datetime_to_server( $datetime = '', $client_offset = '' ){
			if( '' == $datetime || '' == $client_offset ){
				return false;
			}

			$store_time_string = wp_timezone_string();

			if( preg_match( '/\:/', $store_time_string ) ){
				$client_timezone_offset = -1 * ( $client_offset / 60 );
				$offset_minute = fmod( $client_timezone_offset, 1);
				
				$offset_minute = abs( $offset_minute );
			
				$hours = $client_timezone_offset - $offset_minute;

				if( $hours < 0 ){

				} else {
					if( strlen( $hours ) == 0 ){
						$hours = '+0' . $hours;
					} else {
						$hours = '+' . $hours;
					}
				}

				if( strlen( $offset_minute ) == 1 ){
					$offset_minute = '0' . $offset_minute;
				}
			}
			// $store_time_string;
		}

		function bookingpress_any_staff_modify_legacy_flag_func( $bookingpress_any_staff_modify_legacy_flag ){

			if( !function_exists('is_plugin_active') ){
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

			if( is_plugin_active( 'bookingpress-recurring-appointments/bookingpress-recurring-appointments.php' ) ){

				$bookingpress_any_staff_modify_legacy_flag .= '
					if( "undefined" != typeof vm.appointment_step_form_data.is_recurring_appointments && true == vm.appointment_step_form_data.is_recurring_appointments && selected_staffmember_id == "any_staff" && false == use_legacy_any_staff ){
						use_legacy_any_staff = true;
					}
				';

			}

			return $bookingpress_any_staff_modify_legacy_flag;

		}
		
		/**
		 * Function for add dynamic vue methods for bookingpress_form shortcode
		 *
		 * @param  mixed $bookingpress_vue_methods_data
		 * @return void
		 */
		function bookingpress_add_appointment_booking_vue_methods_func( $bookingpress_vue_methods_data ) {
			global $wpdb, $BookingPress;
			$bookingpress_create_nonce      = wp_create_nonce( 'bpa_wp_nonce' );

			$bookingpress_redirection_mode = $BookingPress->bookingpress_get_customize_settings('redirection_mode','booking_form');
			if(empty($bookingpress_redirection_mode)){
				$bookingpress_redirection_mode = 'external_redirection';
			}

			$bookingpress_after_selecting_service_addons = '';
			$bookingpress_after_selecting_service_addons = apply_filters('bookingpress_after_selecting_service_addons', $bookingpress_after_selecting_service_addons);

			$bookingpress_before_book_appointment_data = '';
            $bookingpress_before_book_appointment_data = apply_filters('bookingpress_before_book_appointment', $bookingpress_before_book_appointment_data);

			$bookingpress_reset_custom_duration_data = '';
            $bookingpress_reset_custom_duration_data = apply_filters('bookingpress_reset_custom_duration_data', $bookingpress_reset_custom_duration_data);

			$bookingpress_any_staff_modify_legacy_flag = '';
			$bookingpress_any_staff_modify_legacy_flag = apply_filters( 'bookingpress_any_staff_modify_legacy_flag', $bookingpress_any_staff_modify_legacy_flag );

			$bookingpress_any_staff_modify_xhr_request_data = '';
			$bookingpress_any_staff_modify_xhr_request_data = apply_filters( 'bookingpress_any_staff_modify_xhr_request_data', $bookingpress_any_staff_modify_xhr_request_data );

			$bookingpress_after_selecting_staffmember = '';
			$bookingpress_after_selecting_staffmember = apply_filters('bookingpress_after_selecting_staffmember', $bookingpress_after_selecting_staffmember);

			$bookingpress_after_selecting_anystaffmember = '';
			$bookingpress_after_selecting_anystaffmember = apply_filters('bookingpress_after_selecting_anystaffmember', $bookingpress_after_selecting_anystaffmember);

			$bookingpress_after_change_service_extras = '';
			$bookingpress_after_change_service_extras = apply_filters( 'bookingpress_after_change_service_extras', $bookingpress_after_change_service_extras );

			$bookingpress_after_change_service_quantity = '';
			$bookingpress_after_change_service_quantity = apply_filters( 'bookingpress_after_change_service_quantity', $bookingpress_after_change_service_quantity );

			//tip related filter add 
			$bookingpress_total_amount_payable_modify_outside = '';
			$bookingpress_total_amount_payable_modify_outside = apply_filters( 'bookingpress_total_amount_modify_outside_arr', $bookingpress_total_amount_payable_modify_outside );

			/* Recurring related filter */
			$bookingpress_sub_total_amount_payable_modify_outside = '';
			$bookingpress_sub_total_amount_payable_modify_outside = apply_filters( 'bookingpress_sub_total_amount_payable_modify_outside', $bookingpress_sub_total_amount_payable_modify_outside );

			/* Package Addon Related Filter */			
			$bookingpress_get_final_step_amount_after = '';
			$bookingpress_get_final_step_amount_after = apply_filters( 'bookingpress_get_final_step_amount_after', $bookingpress_get_final_step_amount_after );


			$bookingpress_calculate_total_after_apply_diposit_outside = '';
			$bookingpress_calculate_total_after_apply_diposit_outside = apply_filters( 'bookingpress_calculate_total_after_apply_diposit_outside', $bookingpress_calculate_total_after_apply_diposit_outside );


			$bookingpress_day_select_before = '';
			$bookingpress_day_select_before = apply_filters( 'bookingpress_day_select_before', $bookingpress_day_select_before );

			$bookingpress_day_select_after = '';
			$bookingpress_day_select_after = apply_filters( 'bookingpress_day_select_after', $bookingpress_day_select_after );

			$bookingpress_is_selected_staff_from_url = !empty($_GET['sm_id']) ? intval($_GET['sm_id']) : 0;


			
			$bookingpress_default_select_all_category_data = '';
			$bookingpress_default_select_all_category = $BookingPress->bookingpress_get_customize_settings('default_select_all_category','booking_form');
			if(!empty($bookingpress_default_select_all_category ) && $bookingpress_default_select_all_category == 'true') {                
				$bookingpress_after_selecting_anystaffmember.= '  vm.bpa_select_category( "" );  ';
			}			

			$bookingpress_vue_methods_data .= '
            bookingpress_phone_country_open_repeater( vmodel ){
                const vm = this;
                if( "" != vmodel ){
                    let elm = document.querySelector(`div[data-tel-id="${vmodel}"]`);
                    if( null != elm ){
                        let parent = vm.bookingpress_get_parents( elm, ".bpa-field-main-col" );
                        if( 0 < parent.length && null != parent[0] ){							
							parent[0].classList.add("bpa-active-col");
							let parent_upper = vm.bookingpress_get_parents( parent[0], ".el-col" );
							if( 0 < parent_upper.length && null != parent_upper[0] ){
								//parent_upper[0].classList.add("bpa-active-col");
							}                            
                        }
                    }
                }
            },
            bookingpress_phone_country_close_repeater( vmodel ){
                const vm = this;
                if( "" != vmodel ){
                    let elm = document.querySelector(`div[data-tel-id="${vmodel}"]`);
                    if( null != elm ){
                        let parent = vm.bookingpress_get_parents( elm, ".bpa-active-col" );
                        if( 0 < parent.length && null != parent[0] ){							
							parent[0].classList.remove("bpa-active-col");
                        }
                    }
                }
            },
				bpa_check_space_validation(bpa_input_value,v_modal_data){	
					var vm = this;
					if(bpa_input_value.trim() == ""){
						vm.appointment_step_form_data["form_fields"][v_modal_data] = "";
						return "";
					}
					return bpa_input_value;
				},
				bpa_check_password_validation( bpa_email_value ) {

					
					const vm = this;
					if( "undefined" == typeof vm.appointment_step_form_data.form_fields.customer_password || vm.bpa_check_user_login == 1 ){
						return;
					}


					var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";

					var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
					if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
					{
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
					}
					else {
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
					}
					var postData = { action: "bookingpress_password_validation_check", _wpnonce:bkp_wpnonce_pre_fetch, _email: bpa_email_value };
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {

						if( "undefined" != typeof response.data.bpa_password_already_exists ){
							if( response.data.bpa_password_already_exists == true ){
								if(this.$refs.appointment_step_form_data){
									this.$refs.appointment_step_form_data.clearValidate("customer_password");
								}
								vm.appointment_step_form_data.bpa_password_already_exists = true;
								vm.customer_details_rule.customer_password[0].required = false;
								vm.appointment_step_form_data.bpa_user_email_already_exists = true;
								vm.appointment_step_form_data.form_fields["customer_password"] = "";
							}
						} else {
							vm.appointment_step_form_data.bpa_password_already_exists = false;
							vm.customer_details_rule.customer_password[0].required = true;
							vm.appointment_step_form_data.bpa_user_email_already_exists = false;
						}
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},

				bookingpress_day_click(day){
					const vm = this;
					let disable_dates = vm.v_calendar_disable_dates;
					let max_available_date = vm.booking_cal_maxdate;
					'.$bookingpress_day_select_before.';
					
					let max_date_value = false;

					if( max_available_date instanceof Date ){
						max_date_value = ( max_available_date < ( new Date( day.id ) ) );
					} else {
						max_date_value = ( max_available_date < day.id );
					}

					if( disable_dates.includes( day.id + " 00:00:00" ) || disable_dates.includes( day.id ) || max_date_value || (day.date < vm.jsCurrentDateFormatted && false == day.isToday) ){
						return false;
					}
					
					vm.appointment_step_form_data.selected_date = day.id;
					vm.bookingpress_select_multi_day_range(day);	
					'.$bookingpress_day_select_after.'	
					vm.get_date_timings( day.id );
				},
				bookingpress_get_final_step_amount() {
					const vm = this;
					var payment_method = vm.appointment_step_form_data.selected_payment_method;

					var total_payable_amount = vm.appointment_step_form_data.service_price_without_currency;
					var tax_amount = vm.appointment_step_form_data.tax_amount_without_currency;
					if( "" == tax_amount ){
						tax_amount = 0;
					}
					let total_payable_amount_without_tax = parseFloat(total_payable_amount);
					if(typeof tax_amount != "undefined"){
						total_payable_amount = parseFloat(total_payable_amount) + parseFloat(tax_amount);
					}

					let is_cart_addon = false;
					if (typeof vm.appointment_step_form_data.cart_items != "undefined") 
					{
						/* total_payable_amount = vm.appointment_step_form_data.bookingpress_cart_total; */
						total_payable_amount = vm.appointment_step_form_data.bookingpress_cart_total;
						total_payable_amount_without_tax = parseFloat(total_payable_amount);
						if( typeof tax_amount != "undefined" ){
							total_payable_amount_without_tax = parseFloat( total_payable_amount ) - parseFloat( tax_amount );
						}
						/* console.trace( "INSIDE CART CONDITION ===>>> " + vm.appointment_step_form_data.bookingpress_cart_total ); */
						is_cart_addon = true;
					}

					'.$bookingpress_sub_total_amount_payable_modify_outside.'						

					var coupon_code = vm.appointment_step_form_data.coupon_code;
					var selected_service = vm.appointment_step_form_data.selected_service;
					var selected_staff_member_id = vm.appointment_step_form_data.selected_staff_member_id;		
					
					vm.appointment_step_form_data.total_payable_amount_with_currency = vm.bookingpress_price_with_currency_symbol( total_payable_amount );
					if( 0 == total_payable_amount ){
						vm.appointment_step_form_data.total_payable_amount = total_payable_amount;
					} else {
						vm.appointment_step_form_data.total_payable_amount = vm.bookingpress_price_with_currency_symbol( total_payable_amount, true );
					}

					var subtotal_price = total_payable_amount;
					
					/* apply coupon */
					if( 1 == vm.is_coupon_activated ){
						if(vm.appointment_step_form_data.coupon_code != ""){
							if(typeof vm.appointment_step_form_data.coupon_discount_amount != "undefined"){

								let f_payable_amount = total_payable_amount - vm.appointment_step_form_data.coupon_discount_amount;
								if( 0 == f_payable_amount ){
									vm.appointment_step_form_data.total_payable_amount = f_payable_amount;
								} else {
									vm.appointment_step_form_data.total_payable_amount = vm.bookingpress_price_with_currency_symbol( f_payable_amount, true );
								}
								
								vm.appointment_step_form_data.total_payable_amount_with_currency = vm.bookingpress_price_with_currency_symbol( vm.appointment_step_form_data.total_payable_amount );
								
								subtotal_price = total_payable_amount - vm.appointment_step_form_data.coupon_discount_amount;
							}
						} else {
							vm.appointment_step_form_data.total_payable_amount_with_currency = vm.bookingpress_price_with_currency_symbol( total_payable_amount );
							if( 0 == total_payable_amount ){
								vm.appointment_step_form_data.total_payable_amount = total_payable_amount;
							} else {
								vm.appointment_step_form_data.total_payable_amount = vm.bookingpress_price_with_currency_symbol( total_payable_amount, true );
							}

							subtotal_price = total_payable_amount;
						}
					}
					
					'.$bookingpress_total_amount_payable_modify_outside.'
										

					/* If deposit payment module enabled then calculate deposit amount */
					var deposit_method = vm.appointment_step_form_data.bookingpress_deposit_payment_method;
					var deposit_type = vm.appointment_step_form_data.deposit_payment_type;
					var deposit_value = vm.appointment_step_form_data.deposit_payment_amount;
					var bookingpress_deposit_amt = 0;
					var bookingpress_deposit_due_amt = 0;
					if(payment_method != "" && 1 == vm.bookingpress_is_deposit_payment_activate){ 

						var bookingpress_check_deposit_avaliable = false;
						if( 1 == is_cart_addon ){
							if (typeof vm.appointment_step_form_data.cart_items != "undefined"){
								var cart_items_temp_data = vm.appointment_step_form_data.cart_items;
								if(cart_items_temp_data != "" && cart_items_temp_data.length != 0){                        
									cart_items_temp_data.forEach(function(currentValue, index, arr){
										if (typeof cart_items_temp_data[index].bookingpress_deposit_due_amount != "undefined"){
											if(parseFloat(cart_items_temp_data[index].bookingpress_deposit_due_amount) > 0){
												bookingpress_check_deposit_avaliable = true;
											}												
										}    
									});    
								}
							}
						}else{							
							if(typeof vm.appointment_step_form_data.deposit_payment_type != undefined && typeof vm.appointment_step_form_data.deposit_payment_amount_percentage != undefined){
								if(vm.appointment_step_form_data.deposit_payment_type == "percentage" && vm.appointment_step_form_data.deposit_payment_amount_percentage == 100){
									bookingpress_check_deposit_avaliable = false;
								}else{
									bookingpress_check_deposit_avaliable = true;
								}
							}							
						}

						if(bookingpress_check_deposit_avaliable && payment_method != "on-site" && (typeof vm.appointment_step_form_data.bookingpress_package_applied_data == "undefined" || (typeof vm.appointment_step_form_data.bookingpress_package_applied_data != "undefined") && vm.appointment_step_form_data.bookingpress_package_applied_data == "") && (typeof vm.appointment_step_form_data.gift_card_data == "undefined" || (typeof vm.appointment_step_form_data.gift_card_data != "undefined") && vm.appointment_step_form_data.gift_card_data == "")){
							
							if(deposit_method == "deposit_or_full_price"){
								
								//if( true == is_cart_addon ){
									//subtotal_price = total_payable_amount_without_tax;
									/* vm.bookingpress_price_with_currency_symbol( total_payable_amount_without_tax, true ); */
								//}

								if(deposit_type == "percentage"){
									bookingpress_deposit_amt = total_payable_amount_without_tax * ( parseFloat(deposit_value) / 100);
									bookingpress_deposit_amt = bookingpress_deposit_amt;
									/* vm.bookingpress_price_with_currency_symbol( bookingpress_deposit_amt, true ); */
									bookingpress_deposit_due_amt = subtotal_price - bookingpress_deposit_amt;

								} else if(deposit_type == "fixed") {
									bookingpress_deposit_amt = deposit_value;
									bookingpress_deposit_due_amt = subtotal_price - bookingpress_deposit_amt;
								}
							} else if(deposit_method == "allow_customer_to_pay_full_amount") {
								bookingpress_deposit_amt = subtotal_price;
								bookingpress_deposit_due_amt = subtotal_price - bookingpress_deposit_amt;
							}

							let total_payable_amount_temp = bookingpress_deposit_amt;
							if(typeof vm.appointment_step_form_data.tip_amount != undefined && vm.appointment_step_form_data.tip_amount> 0 && vm.appointment_step_form_data.bookingpress_deposit_total != undefined && vm.appointment_step_form_data.bookingpress_deposit_payment_method == "deposit_or_full_price"){
								total_payable_amount_temp = parseFloat(vm.appointment_step_form_data.bookingpress_deposit_total) + parseFloat(vm.appointment_step_form_data.tip_amount);
							}

							vm.appointment_step_form_data.bookingpress_deposit_amt = vm.bookingpress_price_with_currency_symbol( bookingpress_deposit_amt );
							vm.appointment_step_form_data.bookingpress_deposit_amt_without_currency = vm.bookingpress_price_with_currency_symbol( bookingpress_deposit_amt, true );
							vm.appointment_step_form_data.bookingpress_deposit_due_amt = vm.bookingpress_price_with_currency_symbol( bookingpress_deposit_due_amt );
							vm.appointment_step_form_data.bookingpress_deposit_due_amt_without_currency = bookingpress_deposit_due_amt;
							vm.appointment_step_form_data.total_payable_amount_with_currency = vm.bookingpress_price_with_currency_symbol( total_payable_amount_temp );
							if( 0 == total_payable_amount_temp ){
								vm.appointment_step_form_data.total_payable_amount = total_payable_amount_temp;
							} else {
								vm.appointment_step_form_data.total_payable_amount = vm.bookingpress_price_with_currency_symbol( total_payable_amount_temp, true );
							}

							/* 26 April 2023 changes */
							if( 1 == is_cart_addon ){
																
								if( "allow_customer_to_pay_full_amount" == deposit_method ){
									vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bookingpress_deposit_due_amt + tax_amount;
									vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( bookingpress_deposit_due_amt + tax_amount );
								} else {

									if( 1 == vm.is_tax_activated ){
										/* let tax_method = vm.appointment_step_form_data.tax_price_display_options; */

										/* if( "exclude_taxes" == tax_method ){ */
											let bpa_deposit_due_amount_total = ( parseFloat( total_payable_amount ) - parseFloat( vm.appointment_step_form_data.bookingpress_deposit_total ) );	
											
											if( 1 == vm.is_coupon_activated){
												let coupon_discount = vm.appointment_step_form_data.coupon_discount_amount;
												vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bpa_deposit_due_amount_total - coupon_discount;
												vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( vm.appointment_step_form_data.bookingpress_deposit_due_amount_total );
											} else {
												vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bpa_deposit_due_amount_total;
												vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( bpa_deposit_due_amount_total );
											}
									} else {	

										let bpa_deposit_due_amount_total = ( parseFloat( total_payable_amount ) - parseFloat( vm.appointment_step_form_data.bookingpress_deposit_total ) );

										if( 1 == vm.is_coupon_activated){

											let coupon_discount = vm.appointment_step_form_data.coupon_discount_amount;
											vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bpa_deposit_due_amount_total - coupon_discount;
											vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( vm.appointment_step_form_data.bookingpress_deposit_due_amount_total );

										}else{

											vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = bpa_deposit_due_amount_total;
											vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol( bpa_deposit_due_amount_total );

										}

									}
								}
							}
							/* 26 April 2023 changes */

							'.$bookingpress_calculate_total_after_apply_diposit_outside.'
							
						}
						else
						{							
							vm.appointment_step_form_data.bookingpress_deposit_amt = vm.bookingpress_price_with_currency_symbol( bookingpress_deposit_amt );
							vm.appointment_step_form_data.bookingpress_deposit_amt_without_currency = bookingpress_deposit_amt;
							vm.appointment_step_form_data.bookingpress_deposit_due_amt = vm.bookingpress_price_with_currency_symbol( bookingpress_deposit_due_amt );
							vm.appointment_step_form_data.bookingpress_deposit_due_amt_without_currency = bookingpress_deposit_due_amt;
							vm.appointment_step_form_data.total_payable_amount_with_currency = vm.bookingpress_price_with_currency_symbol( subtotal_price );
							if( 0 == subtotal_price ){
								vm.appointment_step_form_data.total_payable_amount = subtotal_price;
							} else {
								vm.appointment_step_form_data.total_payable_amount = vm.bookingpress_price_with_currency_symbol( subtotal_price, true );
							}
							if(payment_method != "on-site" && (typeof vm.appointment_step_form_data.bookingpress_package_applied_data == "undefined" || (typeof vm.appointment_step_form_data.bookingpress_package_applied_data != "undefined") && vm.appointment_step_form_data.bookingpress_package_applied_data == "")){			
								var bookingpress_is_recurring_appointment_active = false;         
								if(typeof vm.appointment_step_form_data.recurring_appointments != "undefined"){
									if(vm.appointment_step_form_data.is_recurring_appointments == true || vm.appointment_step_form_data.is_recurring_appointments == "true"){
										bookingpress_is_recurring_appointment_active = true;
									} 
								}
								if( 1 == is_cart_addon){									
									vm.appointment_step_form_data.bookingpress_deposit_total = total_payable_amount;
									vm.appointment_step_form_data.bookingpress_deposit_total_with_currency = vm.bookingpress_price_with_currency_symbol( total_payable_amount );									
								}								
								if(bookingpress_is_recurring_appointment_active){										
									vm.appointment_step_form_data.bookingpress_deposit_total = subtotal_price;
									vm.appointment_step_form_data.bookingpress_deposit_total_with_currency = vm.bookingpress_price_with_currency_symbol( subtotal_price );		
									vm.appointment_step_form_data.bookingpress_deposit_due_amount_total = 0;
									vm.appointment_step_form_data.bookingpress_deposit_due_amount_total_with_currency = vm.bookingpress_price_with_currency_symbol(0);																
								}

							}

						}
					}					
					'.$bookingpress_get_final_step_amount_after.'
					if( "undefined" == typeof vm.prevent_verification_on_load || false == vm.prevent_verification_on_load ){
						this.bookingpress_verify_total_payment_amount();
					}
				},
				bookingpress_verify_total_payment_amount_v2(){
					const vm = this;

					var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
					var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
					if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
					{
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
					}
					else {
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
					}

					let bpk_payable_data = {
						action: "bookingpress_pre_booking_verify_details",
						booking_token: vm.appointment_step_form_data.bookingpress_uniq_id,
						booking_data: JSON.stringify( vm.appointment_step_form_data ),
						_wpnonce: bkp_wpnonce_pre_fetch
					};

					return axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bpk_payable_data ) )
					.then( function (response) {
						if( "undefined" != typeof response.data.verification_token ){
							vm.appointment_step_form_data.authorized_token = response.data.verification_token;
							vm.appointment_step_form_data.authorized_time = response.data.verification_time;
						}
					}.bind(this) )
					.catch( function (error) {
						
					});
				},
				bookingpress_verify_total_payment_amount(){
					const vm = this;

					var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
					var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
					if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
					{
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
					}
					else {
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
					}

					vm.bkp_processing_token = true;

					let bpk_payable_data = {
						action: "bookingpress_pre_booking_verify_details",
						booking_token: vm.appointment_step_form_data.bookingpress_uniq_id,
						booking_data: JSON.stringify( vm.appointment_step_form_data ),
						_wpnonce: bkp_wpnonce_pre_fetch
					};

					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bpk_payable_data ) )
					.then( function (response) {
						if( "undefined" != typeof response.data.verification_token ){
							vm.appointment_step_form_data.authorized_token = response.data.verification_token;
							vm.appointment_step_form_data.authorized_time = response.data.verification_time;

							vm.bkp_processing_token = false;
						}
						if( vm.bkp_process_booking == true ){
							vm.bookingpress_process_to_book_appointment();
						}
					}.bind(this) )
					.catch( function (error) {
						
					});
				},			
				bookingpress_recalculate_payable_amount(){
					return false;
					const vm = this;
					var bookingpress_recalculate_data = {};
					bookingpress_recalculate_data.action = "bookingpress_recalculate_appointment_data";
					bookingpress_recalculate_data.appointment_details = JSON.stringify( vm.appointment_step_form_data );

					var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
					var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
					if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
					{
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
					}
					else {
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
					}

					bookingpress_recalculate_data._wpnonce = bkp_wpnonce_pre_fetch;
					if( "undefined" != typeof vm.bookingpress_timezone_offset ){
						bookingpress_recalculate_data.client_timezone_offset = vm.bookingpress_timezone_offset;
					}
					if( "undefined" != typeof vm.bookingpress_dst_timezone ){
						bookingpress_recalculate_data.client_dst_timezone = vm.bookingpress_dst_timezone;
					}
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_recalculate_data ) )
					.then( function (response) {
						vm.appointment_step_form_data = response.data.appointment_data
					}.bind(this) )
					.catch( function (error) {
						vm.bookingpress_set_error_msg(error)
					});
				},
				bookingpress_apply_coupon_code(final_call = false){
					const vm = this;
					vm.coupon_apply_loader = "1";
					var bookingpress_apply_coupon_data = {};
					bookingpress_apply_coupon_data.action = "bookingpress_apply_coupon_code";
					bookingpress_apply_coupon_data.appointment_details = JSON.stringify( vm.appointment_step_form_data );

					var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
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
							vm.appointment_step_form_data.coupon_discount_amount = 0;

							/* for the coupon code error display */
							if( document.querySelector(".bpa-bs__coupon-validation.--is-error") != null ){
								document.querySelector(".bpa-bs__coupon-validation.--is-error").style.display = "flex";
							}

						}else{
							vm.coupon_code_msg = response.data.msg;
							vm.coupon_discounted_amount = "-" + response.data.discounted_amount;
							vm.bpa_coupon_apply_disabled = 1;
							vm.appointment_step_form_data.applied_coupon_res = { "coupon_data": response.data.coupon_data };
						}
						
						if(response.data.coupon_discount_amount > 0 )
						{
							vm.appointment_step_form_data.coupon_discount_amount = response.data.coupon_discount_amount;
							vm.appointment_step_form_data.coupon_discount_amount_with_currecny = response.data.coupon_discount_amount_with_currecny;
							vm.appointment_step_form_data.total_payable_amount_with_currency = response.data.total_payable_amount_with_currency;
							if( 0 == response.data.total_payable_amount ){
								vm.appointment_step_form_data.total_payable_amount = response.data.total_payable_amount;
							} else {
								vm.appointment_step_form_data.total_payable_amount = vm.bookingpress_price_with_currency_symbol( response.data.total_payable_amount, true );
							}
						}
						vm.bookingpress_get_final_step_amount();
					
					}.bind(this) )
					.catch( function (error) {
						vm.bookingpress_set_error_msg(error);
					});
				},
				bookingpress_remove_coupon_code(final_call = false){
					const vm = this;
					vm.appointment_step_form_data.coupon_code = "";
					vm.coupon_code_msg = "";
					/* vm.bookingpress_recalculate_payable_amount() */
					vm.bpa_coupon_apply_disabled = 0;
					vm.coupon_applied_status = "error";
					vm.coupon_discounted_amount = "";
					vm.appointment_step_form_data.coupon_discount_amount = 0;
					
					vm.bookingpress_get_final_step_amount();
				},
				bookingpress_render_thankyou_content(){
					const vm = this;
					var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
					var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
					if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
					{
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
					}
					else {
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
					}
					var postData = { action:"bookingpress_render_thankyou_content", bookingpress_uniq_id: vm.appointment_step_form_data.bookingpress_uniq_id, _wpnonce:bkp_wpnonce_pre_fetch };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == "success"){
							var bookingpress_appointment_id = response.data.appointment_id;
							document.getElementById("bpa-thankyou-screen-div").innerHTML = response.data.thankyou_content;
							document.getElementById("bpa-failed-screen-div").innerHTML = response.data.failed_content;
							wp.hooks.doAction("bpa_calendar_js_init", bookingpress_appointment_id);
							if( null != document.getElementById("bookingpress_google_calendar") ){
								document.getElementById("bookingpress_google_calendar").setAttribute( "href", response.data.bpa_google_cal_link );
							}
							if( null != document.getElementById("bookingpress_yahoo_calendar") ) {
								document.getElementById("bookingpress_yahoo_calendar").setAttribute( "href", response.data.bpa_yahoo_cal_link );
							}
						}
					}.bind(this) )
					.catch( function (error) {
						vm.bookingpress_set_error_msg(error);
					});
				},
				checkBeforeBookProAppointment(){
					const vm = this;
					
					setTimeout(function(){
						var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
						var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
						if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
						{
							bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
						}
						else {
							bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
						}

						var postData = { action:"bookingpress_pro_before_book_appointment", _wpnonce:bkp_wpnonce_pre_fetch };
						postData.appointment_data = JSON.stringify( vm.appointment_step_form_data );
												
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
						.then( function (response) {
							if(response.data.variant == "error"){
								vm.bookingpress_set_error_msg(response.data.msg);
								if(response.data.error_type == "dayoff"){
									vm.service_timing = [];
								}
								vm.isLoadBookingLoader = "0";
								vm.isBookingDisabled = false;
							}else{
								vm.bookingpress_remove_error_msg();
								vm.bookingpress_process_to_book_appointment();
							}
						}.bind(this) )
						.catch( function (error) {
							vm.bookingpress_set_error_msg(error);
						});
					},1500);
				},
				bookingpress_book_appointment(){
					const vm2 = this;
					vm2.isLoadBookingLoader = "1";
					vm2.isBookingDisabled = true;

					/* for the coupon code error hide start */
					if( 1 == vm2.is_coupon_activated ){
						if( document.querySelector(".bpa-bs__coupon-validation.--is-error") != null  ){
							document.querySelector(".bpa-bs__coupon-validation.--is-error").style.display = "none";
						}
					}
					/* for the coupon code error hide end */

					vm2.bookingpress_process_to_book_appointment(); 
				},
				async bookingpress_process_to_book_appointment(){
					const vm2 = this;

					if( "undefined" != typeof vm2.bkp_processing_token && true == vm2.bkp_processing_token ){
						vm2.bkp_process_booking = true;
						return false;
					}

					if(vm2.is_display_error != "1"){
						/* vm2.appointment_step_form_data.service_timing = vm2.service_timing */
						var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
						var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
						if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
						{
							bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
						}
						else {
							bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
						}
						
						var postData = { action:"bookingpress_book_appointment_booking", _wpnonce:bkp_wpnonce_pre_fetch };
						postData.appointment_data = JSON.stringify( vm2.appointment_step_form_data );

						' . $bookingpress_before_book_appointment_data . '

						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
						.then( function (response) {
							vm2.bkp_process_booking = false;
							vm2.isLoadBookingLoader = "0";
							vm2.isBookingDisabled = false;
							var bookingpress_redirection_mode = "'.$bookingpress_redirection_mode.'";
							if(bookingpress_redirection_mode == "external_redirection"){
								if(response.data.variant == "redirect"){

									vm2.bookingpress_external_html = response.data.redirect_data;
									setTimeout(function(){
										var scripts = document.getElementById("bpa-external-script").querySelectorAll("script");
										if(scripts.length > 0){
											var text = scripts[scripts.length - 1].textContent;
											eval(text);
										}
									},50);
									vm2.bookingpress_remove_error_msg();
								}else if(response.data.variant == "redirect_url"){
									vm2.bookingpress_remove_error_msg();
									window.location.href = response.data.redirect_data;
								}else if(response.data.variant == "error"){
									vm2.bookingpress_set_error_msg(response.data.msg);
								}else{
									vm2.bookingpress_remove_error_msg();
								}
								if(response.data.error_type == "dayoff"){
									vm2.service_timing = [];
								}
							}else{
								var bookingpress_uniq_id = vm2.appointment_step_form_data.bookingpress_uniq_id;
								if( "undefined" != typeof wp.hooks ){
									wp.hooks.doAction("bookingpress_after_book_appointment");
								}
								if(response.data.variant != "error"){
									vm2.bookingpress_render_thankyou_content();
									vm2.bookingpress_remove_error_msg();
									if(response.data.variant == "redirect"){
										vm2.bookingpress_external_html = response.data.redirect_data;
										setTimeout(function(){
											var scripts = document.getElementById("bpa-external-script").querySelectorAll("script");
											if(scripts.length > 0){
												var text = scripts[scripts.length - 1].textContent;
												eval(text);
											}
										},50);
										vm2.bookingpress_remove_error_msg();
									}else if(response.data.variant == "redirect_url" && typeof response.data.is_transaction_completed != "undefined" && response.data.is_transaction_completed == "1"){
										vm2.bookingpress_remove_error_msg();
										document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).style.display = "none";
										document.getElementById("bpa-failed-screen-div").style.display = "none";
										document.getElementById("bpa-thankyou-screen-div").style.display = "block";
									}else if(response.data.variant == "redirect_url" && typeof response.data.is_transaction_completed != "undefined" && response.data.is_transaction_completed == "0"){
										vm2.bookingpress_remove_error_msg();
										document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).style.display = "none";
										document.getElementById("bpa-failed-screen-div").style.display = "block";
										document.getElementById("bpa-thankyou-screen-div").style.display = "none";
									}else if(response.data.variant == "redirect_url" && typeof response.data.is_transaction_completed == "undefined"){
										vm2.bookingpress_remove_error_msg();
										window.location.href = response.data.redirect_data;
									}else{
										vm2.appointment_step_form_data.is_transaction_completed = 1;
										document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).style.display = "none";
										document.getElementById("bpa-failed-screen-div").style.display = "none";
										document.getElementById("bpa-thankyou-screen-div").style.display = "block";
									}
								}else{
									vm2.appointment_step_form_data.is_transaction_completed = "";
									vm2.bookingpress_set_error_msg(response.data.msg);
									if(response.data.error_type == "dayoff"){
										vm2.service_timing = [];
									}
								}
							}
						}.bind(this) )
						.catch( function (error) {

							let errorText = ("undefined" != typeof error.response.statusText ) ? error.response.statusText : error;
							vm2.bookingpress_set_error_msg( error.response.status + " " + errorText);
						
							vm2.isLoadBookingLoader = "0";
							vm2.isBookingDisabled = false;
						});
					}else{
						vm2.isLoadBookingLoader = "0";
						vm2.isBookingDisabled = false;
					}
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
					this.GetCardType(this.cardVadid);
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
						cr.classList.remove("cvv-active")
					}
				},
				async bookingpress_set_timezone(){
					const vm = this;
					var bookingpress_timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
					let clientTimezoneOffset = new Date().getTimezoneOffset(); /**  get client timezone offset in minutes from UTC 0. If client is in UTC -10:00 timezone, then this line will return 600 minutes. If timezone is in daylight saving timezone then it will return 540 minutes ( 09:00 hours ) */
					let client_timezone_offset = -1 * ( clientTimezoneOffset / 60 ); /** converted minutes into hours - returns -2 */
					let offset_minute = client_timezone_offset % 1;  /** Retrieve remaining minutes in case if the minutes falls with decimal numbers */
					
					let final_offset = clientTimezoneOffset; /* hours + "" + minute; /** concate the hours and minutes */
					vm.bookingpress_timezone = bookingpress_timezone;
					vm.bookingpress_timezone_offset = final_offset;
					
					vm.appointment_step_form_data.selected_date = vm.get_formatted_date( new Date() );

					let is_dst_time = 0;

					let current_datetime = new Date();
					let jan1 = new Date( current_datetime.getFullYear(), 0, 1, 0, 0, 0, 0 );
					let temp = jan1.toGMTString();
					let jan2 = new Date( temp.substring(0, temp.lastIndexOf(" ") - 1 ) );
					let std_tz_offset = ( jan1 - jan2 ) / ( 1000 * 60 * 60 );

					let june1 = new Date(current_datetime.getFullYear(), 6, 1, 0, 0, 0, 0);
					temp = june1.toGMTString();
					let june2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
					let daylight_time_offset = (june1 - june2) / (1000 * 60 * 60);
					if( std_tz_offset != daylight_time_offset ){
						is_dst_time = 1;
					}

					vm.bookingpress_dst_timezone = is_dst_time;

					var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
					var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
					if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
					{
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
					}
					else {
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
					}
					var bookingpress_postdata = { action: "bookingpress_set_clients_timezone", clients_timezone: bookingpress_timezone, _wpnonce: bkp_wpnonce_pre_fetch };
					return axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_postdata ) )
					.then( function (response){
					}
					.bind( this ) )
					.catch( function (error) {
						console.log(error);
					});
				},
				bookingpress_calculate_service_addons_price(service_id, service_name = "", service_price = "", service_price_without_currency = ""){},
				bookingpress_calculate_service_addons_price_legacy(service_id, service_name = "", service_price = "", service_price_without_currency = ""){
					const vm = this;
					vm.appointment_step_form_data.selected_service = service_id;

					var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
					var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
					if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
					{
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
					}
					else {
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
					}
					if(service_name != "" && service_price != "" && service_price_without_currency != ""){
						vm.appointment_step_form_data.selected_service_name = service_name;
						vm.appointment_step_form_data.selected_service_price = service_price;
						vm.appointment_step_form_data.service_price_without_currency = service_price_without_currency;
					}
					'.$bookingpress_after_selecting_service_addons.'
					var postData = {
						action: "bookingpress_calculate_service_addons_price",
						selected_service_obj: vm.appointment_step_form_data,
						_wpnonce: bkp_wpnonce_pre_fetch
					};
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response)
					{
						if(response.data.variant == "success"){
							if(vm.appointment_step_form_data.selected_service == service_id){
								vm.appointment_step_form_data.selected_service_price = response.data.selected_service_total_price;
								vm.appointment_step_form_data.total_payable_amount = response.data.price_without_currency;
								vm.appointment_step_form_data.total_payable_amount_with_currency = response.data.price_with_currency;
								vm.appointment_step_form_data.service_price_without_currency = response.data.price_without_currency;
								if(response.data.is_tax_calculated == 1){
									vm.appointment_step_form_data.tax_amount = response.data.tax_amount;
									if(typeof vm.appointment_step_form_data.tax_amount_without_currency != "undefined") {										
										vm.appointment_step_form_data.tax_amount_without_currency = response.data.tax_amount_without_currency;
									}
								}
							}
						}
					}
					.bind( this ) )
					.catch( function (error) {
						console.log(error);
					});
				},
				bookingpress_modify_front_dates_as_service(){
					const vm = this;
					var service_id = vm.appointment_step_form_data.selected_service;
					if(vm.is_loaded_service_disabled_dates !== "undefined" && vm.is_loaded_service_disabled_dates != service_id){
						vm.appointment_step_form_data.selected_date = vm.appointment_step_form_data.default_selected_date;
						var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
						var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
						if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
						{
							bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
						}
						else {
							bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
						}
						var postData = {
							action: "bookingpress_change_front_calendar_dates",
							service_obj: vm.appointment_step_form_data,
							_wpnonce: bkp_wpnonce_pre
						};
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
						.then( function (response)
						{
							if(response.data.variant == "success"){
								/* vm.days_off_disabled_dates = response.data.disabled_dates */
								vm.appointment_step_form_data.selected_date = response.data.next_selected_date;
								vm.is_loaded_service_disabled_dates = service_id;
							}
						}
						.bind( this ) )
						.catch( function (error) {
							console.log(error);
						});
					}
				},
				bookingpress_hide_show_extra_description(bookingpress_extra_service_id){
					const vm = this;
					let selected_service_data = vm.bookingpress_all_services_data[ vm.appointment_step_form_data.selected_service ].service_extras;
					let selected_extra = selected_service_data[ bookingpress_extra_service_id ];
					if( "undefined" != typeof selected_extra ){
						if( "0" == selected_extra.bookingpress_is_display_description ){
							selected_extra.bookingpress_is_display_description = "1";
						} else {
							selected_extra.bookingpress_is_display_description = "0";
						}
					}
				},
				bookingpress_select_any_staffmember(){
					const vm = this;
					let step_data = vm.bookingpress_sidebar_step_data["staffmembers"];
					vm.appointment_step_form_data.select_any_staffmember = "true";
					vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = 0;
					vm.appointment_step_form_data.selected_staff_member_id = 0;
					vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = "";
					
					if( "service" == step_data.next_tab_name && vm.bookingpress_sidebar_step_data[step_data.next_tab_name].is_display_step == 1 ){
						vm.appointment_step_form_data.selected_service = "";
					}
					
					for( let x in vm.bpasortedServices ){
						let elm = vm.bpasortedServices[x];
						if( false == elm.is_disabled ){
							vm.bpasortedServices[x].is_visible = true;
						} else {
							vm.bpasortedServices[x].is_visible = false;
						}
					}

					vm.bookingpress_step_navigation(step_data.next_tab_name, step_data.next_tab_name, step_data.previous_tab_name, 0);
				},
				async bookingpress_select_staffmember(selected_staffmember_id, is_any_staff_option_selected = 0){
					const vm = this;
					
					'.$bookingpress_reset_custom_duration_data.'
					var bookingpress_is_selected_staff_from_url = "'.$bookingpress_is_selected_staff_from_url.'";

					vm.v_calendar_disable_dates = [];

					if( "undefined" != typeof vm.bookingpress_disabled_staffmember && vm.bookingpress_disabled_staffmember.indexOf( selected_staffmember_id ) > -1 ){
						return false;
					}
					if(typeof vm.appointment_step_form_data.cart_items == "undefined"){						
						vm.appointment_step_form_data.selected_date = "";
						vm.appointment_step_form_data.selected_start_time = "";
						vm.appointment_step_form_data.selected_end_time = "";
					}

					let service_id = vm.appointment_step_form_data.selected_service;
					let service_data = vm.bookingpress_all_services_data[ service_id ];

					let use_legacy_any_staff = false;
					if( selected_staffmember_id == "any_staff" && "undefined" != typeof service_data && "undefined" != typeof service_data.bookingpress_service_duration_unit && "d" == service_data.bookingpress_service_duration_unit ){
						use_legacy_any_staff = true;
					}

					vm.appointment_step_form_data.any_staff_selected = 0;

					'.$bookingpress_any_staff_modify_legacy_flag.'

					if( true == use_legacy_any_staff ){
						return await vm.bookingpress_select_staffmember_legacy( selected_staffmember_id, is_any_staff_option_selected );
					} else if(selected_staffmember_id == "any_staff" ){
						vm.appointment_step_form_data.any_staff_selected = 1;
						if( "" != vm.appointment_step_form_data.selected_service ){

							vm.isLoadServiceLoader = "0";
							
							let assigned_staffs = [];
							service_data.assigned_staffmembers.forEach( function(staff_id){
								assigned_staffs.push( staff_id );
							});
							if( 1 == assigned_staffs.length ){
								vm.appointment_step_form_data.any_staff_selected = 0;
								let selected_staffmember_id = service_data.assigned_staffmembers[0];

								/*Added for applying selected staff memebr price */
								let service_staff_details = service_data.staff_member_details[ selected_staffmember_id ];
								let selected_staff_price = service_staff_details.bookingpress_service_price;
								vm.appointment_step_form_data.service_price_without_currency = selected_staff_price;
								vm.appointment_step_form_data.base_price_without_currency = selected_staff_price;
								vm.appointment_step_form_data.selected_service_price = vm.bookingpress_price_with_currency_symbol( selected_staff_price );
								/*Added for applying selected staff memebr price */
								
								vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = selected_staffmember_id;
								vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = selected_staffmember_id;
								vm.appointment_step_form_data.bookingpress_selected_staff_member_details.is_any_staff_option_selected = is_any_staff_option_selected;
								vm.appointment_step_form_data.selected_staff_member_id = selected_staffmember_id;
								vm.hide_capacity_text_flag = false;
							} else {
								vm.appointment_step_form_data.available_staffs = assigned_staffs;
								vm.hide_capacity_text_flag = true;
							}
						} else {
							if( "service" == vm.bookingpress_current_tab  ){
								let f = 0;
								vm.appointment_step_form_data.selected_category = "-1";
								vm.appointment_step_form_data.selected_service = "";
								let first_service_category = "";

								let assigned_staffs = [];

								for (let x in vm.bpasortedServices) {
									let elm = vm.bpasortedServices[x];
									if( "undefined" != typeof elm.assigned_staffmembers && false == elm.is_disabled ){
										elm.assigned_staffmembers.forEach( function(staff_id){
											assigned_staffs.push( staff_id );
										});
										vm.bpasortedServices[x].is_visible = true;
										vm.bpasortedServices[x].hide_for_staff = false;
										if ("" == first_service_category) {
											first_service_category = elm.bookingpress_category_id;
										}
									} else {
										vm.bpasortedServices[x].is_visible = false;
										vm.bpasortedServices[x].hide_for_staff = true;
									}
								}

								vm.appointment_step_form_data.available_staffs = assigned_staffs;
								vm.hide_capacity_text_flag = true;

								let hidden_category_for_staff = [];
								for (let ci in vm.service_categories) {
									let current_category = vm.service_categories[ci];
									let category_id = current_category.bookingpress_category_id;
									let category_staff = current_category.bookingpress_staffmembers;

									/* if (category_staff.indexOf(response.data.staffmember_id.toString()) < 0) {
										hidden_category_for_staff.push(category_id);
									} */
								}

								if ("" != first_service_category) {
									for (let c in vm.bookingpress_all_categories) {
										vm.bookingpress_all_categories[c].is_visible = true;
										let current_category = vm.bookingpress_all_categories[c];
										if (current_category.category_id == first_service_category) {
											vm.bpa_select_category(current_category.category_id, current_category.category_name);
										} else {
											if (hidden_category_for_staff.indexOf(current_category.category_id.toString()) > -1) {
												vm.bookingpress_all_categories[c].is_visible = false;
											}
										}
									}
								}
		
								vm.isLoadServiceLoader = "0";
							}

							vm.hide_capacity_text_flag = false;
						}
					} else {
						vm.hide_capacity_text_flag = false;
						vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = selected_staffmember_id;
						vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = selected_staffmember_id;
						vm.appointment_step_form_data.bookingpress_selected_staff_member_details.is_any_staff_option_selected = is_any_staff_option_selected;
						vm.appointment_step_form_data.selected_staff_member_id = selected_staffmember_id;

						vm.appointment_step_form_data.available_staffs = [];
						
						if( vm.is_staff_first_step == 1 ){
							vm.appointment_step_form_data.selected_category = "-1";
							vm.appointment_step_form_data.selected_service = "";
							let visible_service_ids = [];
							let first_service_category = "";
							for( let x in vm.bpasortedServices ){
								let elm = vm.bpasortedServices[x];
								if( "undefined" != typeof elm.assigned_staffmembers && -1 < elm.assigned_staffmembers.indexOf( selected_staffmember_id ) && false == elm.is_disabled ){
									vm.bpasortedServices[x].is_visible = true;
									vm.bpasortedServices[x].hide_for_staff = false;
									if( "" == first_service_category ){
										first_service_category = elm.bookingpress_category_id;
									}
									vm.appointment_step_form_data.base_price_without_currency = vm.bpasortedServices[x].staff_member_details[ selected_staffmember_id ].bookingpress_service_price;
									vm.bpasortedServices[x].service_price_without_currency = vm.bpasortedServices[x].staff_member_details[ selected_staffmember_id ].bookingpress_service_price;
									let selected_staffprice = vm.bookingpress_price_with_currency_symbol( vm.bpasortedServices[x].staff_member_details[ selected_staffmember_id ].bookingpress_service_price );
									vm.bpasortedServices[x].bookingpress_service_price = selected_staffprice;
									visible_service_ids.push( elm.bookingpress_service_id );
								} else {
									vm.bpasortedServices[x].is_visible = false;
									vm.bpasortedServices[x].hide_for_staff = true;
								}
							}

							let hidden_category_for_staff = [];
							for( let ci in vm.service_categories ){
								let current_category = vm.service_categories[ci];
								let category_id = current_category.bookingpress_category_id;
								let category_staff = current_category.bookingpress_staffmembers;

								if( category_staff.indexOf( selected_staffmember_id.toString() ) < 0 ){
									hidden_category_for_staff.push( category_id );
								}
							}


							if( "" != first_service_category ){
								for( let c in vm.bookingpress_all_categories ){
									let current_category = vm.bookingpress_all_categories[c];
									vm.bookingpress_all_categories[c].is_visible = true;
									if( current_category.category_id == first_service_category ){
										vm.bpa_select_category( current_category.category_id, current_category.category_name );
									} else {
										if( hidden_category_for_staff.indexOf( current_category.category_id.toString() ) > -1 ){
											vm.bookingpress_all_categories[c].is_visible = false;
										}
									}
								}
							}

						} else {
							if( "" != vm.appointment_step_form_data.selected_service ){
								let selected_service = vm.appointment_step_form_data.selected_service;
								let selected_service_data = vm.bookingpress_all_services_data[ selected_service ];
								
								if( vm.is_bring_anyone_with_you_activated == 1 ){
									let service_min_capacity = selected_service_data.staff_member_details[ selected_staffmember_id].bookingpress_min_service_capacity;
									if( service_min_capacity != "undefined" && vm.appointment_step_form_data.bookingpress_selected_bring_members < service_min_capacity ){
										vm.appointment_step_form_data.bookingpress_selected_bring_members = service_min_capacity;

										let members = vm.appointment_step_form_data.bookingpress_selected_bring_members;
										let uniqueId = vm.appointment_step_form_data.bookingpress_uniq_id;
										let uniqueId2 = uniqueId.split("").reverse().join("");

										let salt = `${uniqueId}${members}${uniqueId2}`;
										let token = btoa( salt );
										vm.appointment_step_form_data.multiple_quantity_token = token;

										vm.bookingpress_update_staffmember_data( vm.appointment_step_form_data.bookingpress_selected_bring_members );
									} 
								}
								let service_staff_details = selected_service_data.staff_member_details[ selected_staffmember_id ];
								let selected_staff_price = service_staff_details.bookingpress_service_price;
								vm.appointment_step_form_data.service_price_without_currency = selected_staff_price;
								vm.appointment_step_form_data.base_price_without_currency = selected_staff_price;
								vm.appointment_step_form_data.selected_service_price = vm.bookingpress_price_with_currency_symbol( selected_staff_price );
							}
						}

						let step_data = "staffmembers";
						let f = 0;	

						if( "staffmembers" == vm.bookingpress_current_tab ){
							vm.bookingpress_step_navigation(vm.bookingpress_sidebar_step_data[step_data].next_tab_name, vm.bookingpress_sidebar_step_data[step_data].next_tab_name, vm.bookingpress_sidebar_step_data[step_data].previous_tab_name, 1);
						} else {
							if( 1 == vm.is_bring_anyone_with_you_activated ){
								vm.bookingpress_show_bring_anyone_on_staffselection( selected_staffmember_id );
							}
						}
					}
					'.$bookingpress_after_selecting_staffmember.'
				},
				async bookingpress_select_staffmember_legacy(selected_staffmember_id, is_any_staff_option_selected = 0){
					const vm = this;
					'.$bookingpress_reset_custom_duration_data.'
					var bookingpress_is_selected_staff_from_url = "'.$bookingpress_is_selected_staff_from_url.'";

					vm.v_calendar_disable_dates = [];

					if( "undefined" != typeof vm.bookingpress_disabled_staffmember && vm.bookingpress_disabled_staffmember.indexOf( selected_staffmember_id ) > -1 ){
						return false;
					}
					if(typeof vm.appointment_step_form_data.cart_items == "undefined"){						
						vm.appointment_step_form_data.selected_date = "";
						vm.appointment_step_form_data.selected_start_time = "";
						vm.appointment_step_form_data.selected_end_time = "";
					}
					if(selected_staffmember_id == "any_staff" ){
						vm.appointment_step_form_data.bookingpress_selected_staff_member_details.is_any_staff_option_selected = is_any_staff_option_selected;
						var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
						var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
						if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
						{
							bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
						}
						else {
							bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
						}
						var postData = {
							action: "bookingpress_get_any_staffmember_id",
							service_id: vm.appointment_step_form_data.selected_service,
							_wpnonce: bkp_wpnonce_pre_fetch
						};
						'.$bookingpress_any_staff_modify_xhr_request_data.'
						if( "undefined" != vm.appointment_step_form_data.bookingpress_selected_bring_members && 0 < vm.appointment_step_form_data.bookingpress_selected_bring_members ){
							postData.selected_bring_members = vm.appointment_step_form_data.bookingpress_selected_bring_members;
							if( vm.is_bring_anyone_with_you_activated == 1  ){
								vm.bookingpress_repeat_custom_form_fields();
							}
						} else {
							postData.selected_bring_members = 1;
						}
						return axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
						.then( function (response)
						{
							if(response.data.variant == "success"){
								if( 1 > response.data.staffmember_id ){
									for( let s in vm.bookingpress_staffmembers_details ){
										let staffmember_details = vm.bookingpress_staffmembers_details[s];
										let staffmember_selected_services = staffmember_details.assigned_service_details.includes(vm.appointment_step_form_data.selected_service);
										if( staffmember_selected_services ){
											let staffmember_id = staffmember_details.bookingpress_staffmember_id;
											if( "undefined" != typeof vm.bookingpress_disabled_staffmember &&  vm.bookingpress_disabled_staffmember.indexOf( staffmember_id ) < 0 ){
												response.data.staffmember_id = staffmember_id;
											}
										}
									}
								}
								
								vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = response.data.staffmember_id;
								vm.appointment_step_form_data.selected_staff_member_id = response.data.staffmember_id;
								vm.appointment_step_form_data.is_staff_exists = "1";

								let set_service_visibility = false;

								if( "" != vm.appointment_step_form_data.selected_service ){
									let selected_service = vm.appointment_step_form_data.selected_service;
									let selected_service_data = vm.bookingpress_all_services_data[ selected_service ];
									let service_staff_details = selected_service_data.staff_member_details[ response.data.staffmember_id ];
									let selected_staff_price = service_staff_details.bookingpress_service_price;
									vm.appointment_step_form_data.service_price_without_currency = selected_staff_price;
									vm.appointment_step_form_data.base_price_without_currency = selected_staff_price;
									vm.appointment_step_form_data.selected_service_price = vm.bookingpress_price_with_currency_symbol( selected_staff_price );
								} else {
									if( "staffmembers" == vm.bookingpress_current_tab && "" != vm.appointment_step_form_data.selected_staff_member_id ){
										set_service_visibility = true;
									}
								}
								
								if( "service" == vm.bookingpress_current_tab || true == set_service_visibility ){
									let f = 0;
									
									vm.appointment_step_form_data.selected_category = "-1";
									vm.appointment_step_form_data.selected_service = "";
									let first_service_category = "";
									for( let x in vm.bpasortedServices ){
										let elm = vm.bpasortedServices[x];
										if( "undefined" != typeof elm.assigned_staffmembers && -1 < elm.assigned_staffmembers.indexOf( response.data.staffmember_id.toString() ) && false == elm.is_disabled ){
											vm.bpasortedServices[x].is_visible = true;
											vm.bpasortedServices[x].hide_for_staff = false;
											if( "" == first_service_category ){
												first_service_category = elm.bookingpress_category_id;
											}
											vm.appointment_step_form_data.base_price_without_currency = vm.bpasortedServices[x].staff_member_details[ response.data.staffmember_id ].bookingpress_service_price;
											vm.bpasortedServices[x].service_price_without_currency = vm.bpasortedServices[x].staff_member_details[ response.data.staffmember_id ].bookingpress_service_price;
											let selected_staffprice = vm.bookingpress_price_with_currency_symbol( vm.bpasortedServices[x].staff_member_details[ response.data.staffmember_id ].bookingpress_service_price );
											vm.bpasortedServices[x].bookingpress_service_price = selected_staffprice;
										} else {
											vm.bpasortedServices[x].is_visible = false;
											vm.bpasortedServices[x].hide_for_staff = true;
										}
									}
									if( "" != first_service_category ){
										for( let c of vm.service_categories ){
											if( c.bookingpress_category_id == first_service_category ){
												vm.bpa_select_category( c.bookingpress_category_id, c.bookingpress_category_name );
												break;
											}
										}
									}


									let hidden_category_for_staff = [];
									for( let ci in vm.service_categories ){
										let current_category = vm.service_categories[ci];
										let category_id = current_category.bookingpress_category_id;
										let category_staff = current_category.bookingpress_staffmembers;

										if( category_staff.indexOf( response.data.staffmember_id.toString() ) < 0 ){
											hidden_category_for_staff.push( category_id );
										}
									}

									if( "" != first_service_category ){
										for( let c in vm.bookingpress_all_categories ){
											vm.bookingpress_all_categories[c].is_visible = true;
											let current_category = vm.bookingpress_all_categories[c];
											if( current_category.category_id == first_service_category ){
												vm.bpa_select_category( current_category.category_id, current_category.category_name );
											} else {
												if( hidden_category_for_staff.indexOf( current_category.category_id.toString() ) > -1 ){
													vm.bookingpress_all_categories[c].is_visible = false;
												}
											}
										}
									}

									vm.isLoadServiceLoader = "0";
									/* vm.bookingpress_step_navigation(vm.bookingpress_sidebar_step_data[step_data].next_tab_name, vm.bookingpress_sidebar_step_data[step_data].next_tab_name, vm.bookingpress_sidebar_step_data[step_data].previous_tab_name, 1); */
								} else {
									if( 1 == vm.is_bring_anyone_with_you_activated ){
										vm.bookingpress_show_bring_anyone_on_staffselection( response.data.staffmember_id );
									}
								}
								'. $bookingpress_after_selecting_anystaffmember .'
							}
						}
						.bind( this ) )
						.catch( function (error) {
							console.log(error);
						});
					}else{
						vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = selected_staffmember_id;
						vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = selected_staffmember_id;
						vm.appointment_step_form_data.bookingpress_selected_staff_member_details.is_any_staff_option_selected = is_any_staff_option_selected;
						vm.appointment_step_form_data.selected_staff_member_id = selected_staffmember_id;
						
						if( vm.is_staff_first_step == 1 ){
							vm.appointment_step_form_data.selected_category = "-1";
							vm.appointment_step_form_data.selected_service = "";
							let visible_service_ids = [];
							let first_service_category = "";
							for( let x in vm.bpasortedServices ){
								let elm = vm.bpasortedServices[x];
								if( "undefined" != typeof elm.assigned_staffmembers && -1 < elm.assigned_staffmembers.indexOf( selected_staffmember_id ) && false == elm.is_disabled ){
									vm.bpasortedServices[x].is_visible = true;
									vm.bpasortedServices[x].hide_for_staff = false;
									if( "" == first_service_category ){
										first_service_category = elm.bookingpress_category_id;
									}
									vm.appointment_step_form_data.base_price_without_currency = vm.bpasortedServices[x].staff_member_details[ selected_staffmember_id ].bookingpress_service_price;
									vm.bpasortedServices[x].service_price_without_currency = vm.bpasortedServices[x].staff_member_details[ selected_staffmember_id ].bookingpress_service_price;
									let selected_staffprice = vm.bookingpress_price_with_currency_symbol( vm.bpasortedServices[x].staff_member_details[ selected_staffmember_id ].bookingpress_service_price );
									vm.bpasortedServices[x].bookingpress_service_price = selected_staffprice;
									visible_service_ids.push( elm.bookingpress_service_id );
								} else {
									vm.bpasortedServices[x].is_visible = false;
									vm.bpasortedServices[x].hide_for_staff = true;
								}
							}

							let hidden_category_for_staff = [];
							for( let ci in vm.service_categories ){
								let current_category = vm.service_categories[ci];
								let category_id = current_category.bookingpress_category_id;
								let category_staff = current_category.bookingpress_staffmembers;

								if( category_staff.indexOf( selected_staffmember_id.toString() ) < 0 ){
									hidden_category_for_staff.push( category_id );
								}
							}


							if( "" != first_service_category ){
								for( let c in vm.bookingpress_all_categories ){
									let current_category = vm.bookingpress_all_categories[c];
									vm.bookingpress_all_categories[c].is_visible = true;
									if( current_category.category_id == first_service_category ){
										vm.bpa_select_category( current_category.category_id, current_category.category_name );
									} else {
										if( hidden_category_for_staff.indexOf( current_category.category_id.toString() ) > -1 ){
											vm.bookingpress_all_categories[c].is_visible = false;
										}
									}
								}
							}

						} else {
							if( "" != vm.appointment_step_form_data.selected_service ){
								let selected_service = vm.appointment_step_form_data.selected_service;
								let selected_service_data = vm.bookingpress_all_services_data[ selected_service ];
								let service_staff_details = selected_service_data.staff_member_details[ selected_staffmember_id ];
								let selected_staff_price = service_staff_details.bookingpress_service_price;
								vm.appointment_step_form_data.service_price_without_currency = selected_staff_price;
								vm.appointment_step_form_data.base_price_without_currency = selected_staff_price;
								vm.appointment_step_form_data.selected_service_price = vm.bookingpress_price_with_currency_symbol( selected_staff_price );
							}
						}

						let step_data = "staffmembers";
						let f = 0;	

						if( "staffmembers" == vm.bookingpress_current_tab ){
							vm.bookingpress_step_navigation(vm.bookingpress_sidebar_step_data[step_data].next_tab_name, vm.bookingpress_sidebar_step_data[step_data].next_tab_name, vm.bookingpress_sidebar_step_data[step_data].previous_tab_name, 1);
						} else {
							if( 1 == vm.is_bring_anyone_with_you_activated ){
								vm.bookingpress_show_bring_anyone_on_staffselection( selected_staffmember_id );
							}
						}
					}
					'.$bookingpress_after_selecting_staffmember.'
				},
				bookingpress_show_bring_anyone_on_staffselection( selected_staffmember_id ){
					const vm = this;
					/** Enable Bring Any one if the service has only 1 capacity but the selected staff has more that 1 capacity  */
					let is_bring_anone_displayed = document.querySelectorAll(".--bpa-sao-guest-module");
					if( "" == selected_staffmember_id || 1 > selected_staffmember_id ){
						return false;
					}
					let selected_service = vm.appointment_step_form_data.selected_service;
					if( "" == selected_service ){
						return false;
					}
					let staffmember_details = vm.bookingpress_staffmembers_details;
						
					for( let s in staffmember_details ){
						let current_staffmember = staffmember_details[s];
						let staffmember_id = current_staffmember.bookingpress_staffmember_id;
						if( staffmember_id == selected_staffmember_id ){
							let assigned_service_price_list = current_staffmember.assigned_service_price_details;
							let max_capacity = assigned_service_price_list[ selected_service ].assigned_service_capacity;
							let min_capacity = assigned_service_price_list[ selected_service ].assigned_service_min_capacity;

							if( "undefined" == typeof vm.bookingpress_bring_anyone_with_you_details[selected_service] ){
								vm.bookingpress_bring_anyone_with_you_details[selected_service] = {
									"bookingpress_service_id": selected_service,
									"bookingpress_service_max_capacity": parseInt(max_capacity),
									"bookingpress_service_min_capacity": parseInt(min_capacity),
								};
							} else {
								vm.bookingpress_bring_anyone_with_you_details[selected_service].bookingpress_service_max_capacity = parseInt(max_capacity);
								vm.bookingpress_bring_anyone_with_you_details[selected_service].bookingpress_service_min_capacity = parseInt(min_capacity);
								
							}
							vm.appointment_step_form_data.service_max_capacity = parseInt(max_capacity);
							vm.appointment_step_form_data.service_min_capacity = parseInt(min_capacity);
							
						}
					}
				},
				bookingpress_close_extra_drawer(){
					const vm = this;
					
					let selected_service = vm.appointment_step_form_data.selected_service;

					vm.appointment_step_form_data.selected_service = "";
					vm.appointment_step_form_data.selected_service_name = "";
					vm.appointment_step_form_data.selected_service_price = "";
					vm.appointment_step_form_data.service_price_without_currency = "";

					if( "" != selected_service ){
						let selected_service_data = vm.bookingpress_all_services_data[ selected_service ];
						let service_extras = ( "undefined" != typeof selected_service_data.service_extras ) ? selected_service_data.service_extras : false;
						if( false != service_extras ){
							for( let se in service_extras ){
								vm.appointment_step_form_data.bookingpress_selected_extra_details[ se ].bookingpress_is_selected = false;
								vm.appointment_step_form_data.bookingpress_selected_extra_details[ se ].bookingpress_selected_qty = 1;
							}
						}
					}
					vm.bookingpress_open_extras_drawer = "false";
					/** reset bring anyone details */
					if( 1 == vm.is_bring_anyone_with_you_activated ){
						vm.appointment_step_form_data.bookingpress_selected_bring_members = 1;

						let members = vm.appointment_step_form_data.bookingpress_selected_bring_members;
						let uniqueId = vm.appointment_step_form_data.bookingpress_uniq_id;
						let uniqueId2 = uniqueId.split("").reverse().join("");

						let salt = `${uniqueId}${members}${uniqueId2}`;
						let token = btoa( salt );
						vm.appointment_step_form_data.multiple_quantity_token = token;
					}
				},
				bookingpress_get_service_capacity(){
					var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
					var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
					if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
					{
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
					}
					else {
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
					}
					const vm = this;
					var postData = {
						action: "bookingpress_get_service_max_capacity",
						service_id: vm.appointment_step_form_data.selected_service,
						_wpnonce: bkp_wpnonce_pre_fetch
					};
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response)
					{
						if(response.data.variant == "success"){
							vm.appointment_step_form_data.service_max_capacity = response.data.max_capacity
						}
					}
					.bind( this ) )
					.catch( function (error) {
						console.log(error);
					});
				},
				bookingpress_service_advance_see_less(){
					const vm = this;
					vm.service_advance_see_less = "1";
					vm.bookingpress_close_extra_drawer();
				},
				bookingpress_load_more_extras(){
					const vm = this;
					if(vm.is_load_more_extras == "0"){
						vm.is_load_more_extras = "1";
					}else{
						vm.is_load_more_extras = "0";
					}
				},
				bpa_focus_select_elm( $el ){
					const vm = this;
					setTimeout(function(){
						let elm = $el.querySelector(".el-input.is-focus");// .el-input.is-focus");
						let parentNode = vm.BPAGetParents( elm, ".bpa-bd-fields--sel-container" );
						if( parentNode.length > 0 ){
							let firstParent = parentNode[0];
							firstParent.classList.add( "bpa-sel--focus" );
						}
					},100);
				},
				bpa_remove_focus_select_elm($el){
					let elm = document.querySelector( ".bpa-sel--focus" );
				},
				bookingpress_selectpicker_set_position( flag ){		
					const vm = this;			
					if( true == flag ){	
						let is_admin_bar_visible = ( document.getElementById("wpadminbar") != null && document.getElementById("wpadminbar").getBoundingClientRect().width > 0 && document.getElementById("wpadminbar").getBoundingClientRect().height > 0 ) ? true : false;
						if( document.querySelector(".bpa-focused-select") != null &&  is_admin_bar_visible ) {
							setTimeout(function(){
								let top_pos = document.querySelector(".bpa-focused-select").style.top;
								top_pos = parseInt( top_pos.replace("px","") );
								document.querySelector(".bpa-focused-select").style.top = ( top_pos + 32 ) + "px";
							},10);
						}

						if( document.querySelector(".bpa-focused-select") != null ) {

							setTimeout(function(){
								const allselectcontainer = document.querySelectorAll(".bpa-bd-fields--sel-container");
								if(typeof allselectcontainer != "undefined"){
									var has_added = false;
									allselectcontainer.forEach((selcontItem) => {
										var questyle = selcontItem.querySelector(".bpa-focused-select");
										if(typeof questyle != "undefined"){
											const display = window.getComputedStyle(questyle).display;
											if(display != "none" && !has_added){
												selcontItem.classList.add( "bpa-sel--focus" );	
												has_added = true;
											}
										}
									});                    
								}	
							},100);							

						}						
					}
					if( false == flag ){
						if( document.querySelector(".bpa-bd-fields--sel-container") != null ) {
							let elm = document.querySelector(".bpa-bd-fields--sel-container")
							const range_inputs = document.querySelectorAll(".bpa-bd-fields--sel-container");
							for (const range_input of range_inputs) {
								range_input.classList.remove( "bpa-sel--focus" );
							}							
						}
					}
					
				},
				bookingpress_set_datepicker_position( event ){
					let popperElm = document.querySelector(".bpa-custom-datepicker");
					if( popperElm != null ){
						let is_admin_bar_visible = ( document.getElementById("wpadminbar") != null && document.getElementById("wpadminbar").getBoundingClientRect().width > 0 && document.getElementById("wpadminbar").getBoundingClientRect().height > 0 ) ? true : false;
						if( is_admin_bar_visible ){
							setTimeout(function(){
								let top_pos = popperElm.style.top;
								top_pos = parseInt( top_pos.replace("px","") );
								popperElm.style.top = ( top_pos + 32 ) + "px";
							},10);
						}
					}
				},
				bookingpress_get_service_categories_from_staffmembers(){
					const vm = this;
					var bkp_wpnonce_pre = "' . $bookingpress_create_nonce . '";
					var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
					if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null)
					{
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
					}
					else {
						bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
					}
					var postData = {
						action: "bookingpress_get_service_cat_details",
						staffmember_id: vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id,
						_wpnonce: bkp_wpnonce_pre_fetch
					};
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response)
					{
						if(response.data.variant == "success"){
							vm.service_categories = response.data.service_categories_data;
							var bookingpress_first_cat_id = response.data.first_cat_id;
							vm.services_data = [];
							setTimeout(function(){
								vm.selectStepCategory(bookingpress_first_cat_id);
							}, 500);
						}
					}
					.bind( this ) )
					.catch( function (error) {
						console.log(error);
					});
				},
				bookingpress_update_staffmember_data( number_of_guests, selected_service_id = "" ){
					const vm = this;
					
					vm.bookingpress_disabled_staffmember = [];
					let selected_service = vm.appointment_step_form_data.selected_service;

					if( selected_service == "" || ( (selected_service_id != "") && (selected_service != selected_service_id) ) ){
						selected_service = selected_service_id;
					}
					
					let guests_count = number_of_guests - 1;
					let staffmember_details = vm.bookingpress_staffmembers_details;
					
					for( let s in staffmember_details ){
						vm.bookingpress_staffmembers_details[s].is_display_staff_with_flag = true;
						let current_staffmember = staffmember_details[s];
						let staffmember_id = current_staffmember.bookingpress_staffmember_id;
						let assigned_service_price_list = current_staffmember.assigned_service_price_details;
						
						if( "undefined" != typeof assigned_service_price_list[ selected_service ] ){
							
							let max_capacity = assigned_service_price_list[ selected_service ].assigned_service_capacity;
							if( max_capacity < (guests_count + 1) ){
								if( "undefined" == typeof vm.bookingpress_disabled_staffmember ){
									vm.bookingpress_disabled_staffmember = [];
								}
								vm.bookingpress_disabled_staffmember.push( staffmember_id );
								vm.bookingpress_staffmembers_details[s].is_display_staff_with_flag = false;
								if( vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id == staffmember_id ){
									vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = "";
									vm.appointment_step_form_data.selected_staff_member_id = "";
									vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = "";
								}
							}

							if( vm.is_bring_anyone_with_you_activated == "1" ){
								
								let min_capacity = assigned_service_price_list[ selected_service ].assigned_service_min_capacity;
								if( min_capacity > (guests_count + 1) ){

									if( "undefined" == typeof vm.bookingpress_disabled_staffmember ){
										vm.bookingpress_disabled_staffmember = [];
									}

									vm.bookingpress_disabled_staffmember.push( staffmember_id );
									vm.bookingpress_staffmembers_details[s].is_display_staff_with_flag = false;
									if( vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id == staffmember_id ){
										vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = "";
										vm.appointment_step_form_data.selected_staff_member_id = "";
										vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = "";
									}
								}
								
							}
						}
					}
				},
				bookingpress_close_extra_drawer_on_mouseup(){
					const vm = this;
					window.addEventListener( "mouseup", function(e){
						let elem = e.target;
						let parentNode = vm.BPAGetParents( elem, ".bpa-fm--service__advance-options" );
						let parentNodeBawy = vm.BPAGetParents( elem, ".bpa-fm--service__advance-options-popper" );
						let is_mob = false;

						let bpa_parent_container = document.querySelector( ".bpa-frontend-main-container" );
						let is_visible = true;
						if( null != bpa_parent_container && 1 > bpa_parent_container.offsetWidth ){
							is_visible = false;							
						}
						
						if( parentNode.length < 1 && parentNodeBawy.length < 1 && "true" == vm.bookingpress_open_extras_drawer && "service" == vm.bookingpress_current_tab && "true" == vm.bookingpress_open_extras_drawer && true == is_visible ){
							let mob_extra = document.querySelector( ".bpa-fm--service__advance-options.--bpa-is-mob" );
							if( mob_extra == null ){
								vm.bookingpress_close_extra_drawer();
							} else {
								let mob_pos = mob_extra.getBoundingClientRect();
								if( mob_pos.width == 0 && mob_pos.height == 0 ){
									vm.bookingpress_close_extra_drawer();
								}
							}
						}
					});
				},
				BPAGetParents( elem, selector ){
					if (!Element.prototype.matches) {
						Element.prototype.matches = Element.prototype.matchesSelector ||
							Element.prototype.mozMatchesSelector ||
							Element.prototype.msMatchesSelector ||
							Element.prototype.oMatchesSelector ||
							Element.prototype.webkitMatchesSelector ||
							function(s) {
								var matches = (this.document || this.ownerDocument).querySelectorAll(s),
									i = matches.length;
								while (--i >= 0 && matches.item(i) !== this) {}
								return i > -1;
							};
					}
				
					var parents = [];
				
					for (; elem && elem !== document; elem = elem.parentNode) {
						if (selector) {
							if (elem.matches(selector)) {
								parents.push(elem);
							}
							continue;
						}
						parents.push(elem);
					}
				
					return parents;
				},				
				bookingpress_get_formatted_datetime(event,field_meta_key,is_time_enabled) {
					if(event != null){
						if(is_time_enabled == true) {
							this.appointment_step_form_data["form_fields"][field_meta_key] = this.get_formatted_datetime(event);
						} else {
							this.appointment_step_form_data["form_fields"][field_meta_key] = this.get_formatted_date(event);
						}
					}
				},
				BPACustomerFileUpload(response, file, fileList){
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
						vm.appointment_step_form_data[ response.file_ref ] = upload_url;
						vm.appointment_step_form_data.form_fields[ response.file_ref ] = upload_url;
					}
				},
				BPACustomerFileUploadError(err, file, fileList){
					/** Need to handle error but currently no error is reaching to this function */
					if( file.status == "fail" ){
						console.log( err );
					}
				},
				BPACustomerFileUploadRemove( file, fileList ){
					const vm = this;
					let response = file.response;
					vm.appointment_step_form_data[ response.file_ref ] = "";
					vm.appointment_step_form_data.form_fields[ response.file_ref ] = "";

					let postData = {
						action:"bpa_remove_form_file",
						_wpnonce: "'.wp_create_nonce( 'bpa_wp_nonce' ).'",
						uploaded_file_name: response.upload_file_name
					};
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function( response ){
					}).catch( function( error ){
					});
				},
				BPAConvertBytesToMB( bytes){
					return (bytes / (1024 * 1024)).toFixed(0);
				},
				bookingpress_reset_selected_timeslot_data(){
					const vm = this;
					vm.appointment_step_form_data.selected_start_time = "";
				},
				bookingpress_reset_custom_duration_data() {
					const vm = this;					
					'.$bookingpress_reset_custom_duration_data.';
				},
				bookingpress_step_navigator( current_tab, next_tab, previous_tab ){
					let vm = this;
					let is_strict_validate = false;

					let current_selected_tab = vm.bookingpress_current_tab;
					let sidebar_step_data = vm.bookingpress_sidebar_step_data;
					let sidebar_keys = Object.keys( sidebar_step_data );
					
					let current_tab_pos = sidebar_keys.indexOf( current_selected_tab ); /** Current Tab Position */
					let selected_tab_pos = sidebar_keys.indexOf( current_tab ); /** Clicked Tab Position */

					if( selected_tab_pos < current_tab_pos ){
						vm.bookingpress_step_navigation( current_tab, next_tab, previous_tab, 0 );
					} else {
						vm.bookingpress_step_navigation( current_tab, next_tab, previous_tab );
					}
				},
				bookingpress_change_service_extras_event( is_checked ){
					const vm = this;
					'.$bookingpress_after_change_service_extras.'
				},
				bookingpress_change_service_extras_qty( is_checked ){
					const vm = this;
					'.$bookingpress_after_change_service_quantity.'
					vm.$forceUpdate();
				},
				';

			$bookingpress_vue_methods_data .= apply_filters('bookingpress_add_pro_booking_form_methods', $bookingpress_vue_methods_data);

			return $bookingpress_vue_methods_data;
		}

		function bookingpress_add_client_timezone( $bookingpress_disable_date_pre_xhr_data ){

			global $BookingPress;
			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );
			
			if( "true" == $bookingpress_timeslot_display_in_client_timezone ){
				//$bookingpress_dynamic_on_load_methods_data .= 'this.bookingpress_set_timezone();';
				$bookingpress_disable_date_pre_xhr_data .= ' await this.bookingpress_set_timezone(); ';
			}

			$bookingpress_max_days_for_booking  = $BookingPress->bookingpress_get_settings( 'period_available_for_booking', 'general_setting' );
			
			/** set the service expiration limit */
			$bookingpress_disable_date_pre_xhr_data .= '
			let selected_sid = this.appointment_step_form_data.selected_service;

			if(selected_sid != "" && "undefined" != typeof this.bookingpress_all_services_data[ selected_sid ].bookingpress_service_expiration_date && "" != this.bookingpress_all_services_data[ selected_sid ].bookingpress_service_expiration_date && null != this.bookingpress_all_services_data[ selected_sid ].bookingpress_service_expiration_date ) {
				this.booking_cal_maxdate = this.bookingpress_all_services_data[ selected_sid ].bookingpress_service_expiration_date;
				const currentDate = new Date();
				const targetDate = new Date(this.bookingpress_all_services_data[ selected_sid ].bookingpress_service_expiration_date);
				let diff_in_time = targetDate.getTime() - currentDate.getTime();
				let diff_in_days = ( Math.round( diff_in_time / ( 1000 * 3600 * 24 ) ) ) + 1;
				const bookingpress_period_available_for_booking = '.$bookingpress_max_days_for_booking.';
				if(bookingpress_period_available_for_booking < diff_in_days){
					/*If max period avilable is less than service expiry date then set limit for the Max period avilable days*/
					this.booking_cal_maxdate = new Date( Date.now() + ( 3600 * 1000 * ( 24 * '.$bookingpress_max_days_for_booking.' ) ) );
				}
			}';
			return $bookingpress_disable_date_pre_xhr_data;
		}
		
		
		/**
		 * Function for add onload vue methods for bookingpress_form shortcode
		 *
		 * @param  mixed $bookingpress_dynamic_on_load_methods_data
		 * @return void
		 */
		function bookingpress_add_appointment_booking_on_load_methods_func( $bookingpress_dynamic_on_load_methods_data ) {
			global $BookingPress, $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_entries, $tbl_bookingpress_categories, $tbl_bookingpress_services, $bookingpress_deposit_payment;

			$bookingpress_dynamic_on_load_methods_data .= 'const vm = this;';

			$bookingpress_dynamic_on_load_methods_data .= 'this.expirationDate();';
			/* $bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );
			
			if( "true" == $bookingpress_timeslot_display_in_client_timezone ){
				$bookingpress_dynamic_on_load_methods_data .= 'this.bookingpress_set_timezone();';
			} */

			/* // If staff member exists and add as step and hide staff member step enable then any staff selected automatically
			$bookingpress_dynamic_on_load_methods_data .= '
			
			
			'; */

			$bookingpress_max_days_for_booking          = $BookingPress->bookingpress_get_settings( 'period_available_for_booking', 'general_setting' );
			//$bookingpress_dynamic_on_load_methods_data .= 'this.booking_cal_maxdate = new Date().addDays(' . $bookingpress_max_days_for_booking . ');';
			//new Date( Date.now() + ( 3600 * 1000 * (24 * 365) ) )
			$bookingpress_dynamic_on_load_methods_data .= 'this.booking_cal_maxdate = new Date( Date.now() + ( 3600 * 1000 * ( 24 * '.$bookingpress_max_days_for_booking.' ) ) )';

			/** hide staff member step if staff member id is passed through URL */
			$bookingpress_allow_modify_false_check = (isset($_GET['allow_modify']) && $_GET['allow_modify'] == 0) ? 'allow_modify_false' : '';
			$bookingpress_is_staff_load_from_share_url = !empty($_GET['sm_id']) ? 1 : 0;
			$bookingpress_is_staff_load_from_url_param = ( 0 == $bookingpress_is_staff_load_from_share_url && !empty( $_GET['bpstaffmember_id'] ) ) ? 1 : 0;
			$bookingpress_is_extras_load_from_share_url = !empty($_GET['se_id']) ? 1 : 0;
			$bookingpress_is_guest_load_from_share_url = !empty($_GET['g_id']) ? 1 : 0;

			$bookingpress_selected_guest_from_url = !empty($_GET['g_id']) ? intval($_GET['g_id']) : 0;

			$bookingpress_selected_extras_from_url = !empty($_GET['se_id']) ? sanitize_text_field($_GET['se_id']) : 0;
			$bookingpress_selected_extras_details = explode('~', $bookingpress_selected_extras_from_url);
			$bookingpress_selected_extras_details_new = array();
			if(!empty($bookingpress_selected_extras_details)){
				foreach($bookingpress_selected_extras_details as $extra_detail_key => $extra_detail_val){
					if(!empty($extra_detail_val)){
						$bookingpress_extra_tmp_val = explode('|', $extra_detail_val);
						$bookingpress_selected_extras_details_new[$bookingpress_extra_tmp_val[0]] = $bookingpress_extra_tmp_val[1];
					}
				}
			}

			$bookingpress_selected_extras_details_new = wp_json_encode($bookingpress_selected_extras_details_new);

			$bookingpress_share_url_selected_staff_id = !empty($_GET['sm_id']) ? intval($_GET['sm_id']) : 0;
			$bookingpress_is_service_load_from_url = (isset($_GET['s_id']) || isset($_GET['bpservice_id'])) ? 1 : 0;

			$bookingpress_share_url_selected_staff_id = apply_filters( 'bookingpress_modify_staffmember_id', $bookingpress_share_url_selected_staff_id, null );
			

			$bookingpress_dynamic_on_load_methods_data .= '
				var bpa_is_service_loaded_from_url = "'.$bookingpress_is_service_load_from_url.'";
				var bookingpress_is_staff_load_from_share_url = "'.$bookingpress_is_staff_load_from_share_url.'";
				var bookingpress_is_staff_load_from_url_param = "'.$bookingpress_is_staff_load_from_url_param.'";

				let is_staff_first = false;
				if( typeof vm.appointment_step_form_data.form_sequence == "object" ){
					if( vm.appointment_step_form_data.form_sequence[0] == "staff_selection" ){
						is_staff_first = true;
					}
				} else {
					if( vm.appointment_step_form_data.form_sequence == "staff_selection" ){
						is_staff_first = true;
					}
				}
				if( "undefined" == typeof vm.is_staff_member_set_from_url ){
					vm.is_staff_member_set_from_url = false;
				}

				if( vm.is_staffmember_activated == 1 && vm.appointment_step_form_data.hide_staff_selection == "true" && true == is_staff_first &&  vm.is_staff_member_set_from_url == false && false == bookingpress_is_staff_load_from_share_url && false == bookingpress_is_staff_load_from_url_param ){
					vm.bookingpress_select_staffmember("any_staff", 1);
				}

				if(bpa_is_service_loaded_from_url == "1"){
					vm.appointment_step_form_data.bookingpress_is_load_staff_from_share_url = "0";
					var bookingpress_allow_modify_false_check = "'.$bookingpress_allow_modify_false_check.'";
					
					
					var bookingpress_is_extras_load_from_share_url = "'.$bookingpress_is_extras_load_from_share_url.'";
					var bookingpress_guests_load_from_share_url = "'.$bookingpress_is_guest_load_from_share_url.'";
					var bookingpress_selected_guest_from_url = "'.$bookingpress_selected_guest_from_url.'";
					var bookingpress_selected_extra_details = '.$bookingpress_selected_extras_details_new.';
					
					if( vm.is_staff_member_set_from_url == true ){
						let selectedStaffmember = String(vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id);
						if( "0" == vm.is_service_loaded_from_url || false == vm.is_service_loaded_from_url ){
							vm.bookingpress_select_staffmember( selectedStaffmember, 0 );
						}else if( bookingpress_is_staff_load_from_share_url == "1" ){
							let selectedStaffmember = String(vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id);
							vm.bookingpress_select_staffmember( selectedStaffmember, 0 );
						}
					}/*  else if ( false == vm.is_staff_member_set_from_url && vm.is_service_loaded_from_url ){
						vm.is_service_loaded_from_url = 0;
					} */

					if((bookingpress_is_staff_load_from_share_url == "1" || bookingpress_is_extras_load_from_share_url == "1" || bookingpress_guests_load_from_share_url == "1") && vm.appointment_step_form_data.form_sequence != "staff_selection" ){
						vm.appointment_step_form_data.bookingpress_is_load_staff_from_share_url = "1";
					}
					
					var bookingpress_selected_service_id = vm.appointment_step_form_data.selected_service;
					let selectedStaffmember = String(vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id); 
					
					if(bookingpress_is_staff_load_from_share_url == "1"){
						vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = "'.$bookingpress_share_url_selected_staff_id.'";
						vm.appointment_step_form_data.selected_staff_member_id = "'.$bookingpress_share_url_selected_staff_id.'";
						
					}

					if(bookingpress_selected_service_id != ""){
						var bookingpress_selected_service_name = "";
						var bookingpress_selected_service_price = "";
						var bookingpress_selected_service_price_without_currency = "";
						var bookingpress_selected_service_duration = "";
						var bookingpress_selected_service_duration_unit = "";

						for( let index in vm.bookingpress_all_services_data ){

							let currentValue = vm.bookingpress_all_services_data[ index ];

							if(currentValue.bookingpress_service_id == bookingpress_selected_service_id){
								bookingpress_selected_service_name = currentValue.bookingpress_service_name;
								bookingpress_selected_service_price = currentValue.bookingpress_service_price;
								bookingpress_selected_service_price_without_currency = currentValue.service_price_without_currency;
								bookingpress_selected_service_duration = currentValue.bookingpress_service_duration_val;
								bookingpress_selected_service_duration_unit = currentValue.bookingpress_service_duration_unit
							}
						};

						/* 

						if((bookingpress_is_staff_load_from_share_url == "0" && vm.is_bring_anyone_with_you_activated == "0" && vm.bookingpress_is_extra_enable == "0" && bookingpress_allow_modify_false_check == "") || (bookingpress_is_staff_load_from_share_url == "0" && (vm.is_bring_anyone_with_you_activated == "1" || vm.bookingpress_is_extra_enable == "1") && bookingpress_allow_modify_false_check == "") && bookingpress_is_staff_load_from_share_url == "1" ){
							vm.bookingpress_step_navigation(vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].next_tab_name, vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].next_tab_name, vm.bookingpress_sidebar_step_data[vm.bookingpress_current_tab].previous_tab_name)
						}*/
						/*
						Select staff member retrieved from URL 
						if(bookingpress_is_staff_load_from_share_url == "1" && selectedStaffmember != ""){
							vm.appointment_step_form_data.is_staff_exists = "1";
							vm.bookingpress_select_staffmember( selectedStaffmember, 0 );
						}*/

						/*Select bring anyone with you members */
						
						if(vm.is_bring_anyone_with_you_activated == "1" && bookingpress_guests_load_from_share_url == "1" && bookingpress_selected_guest_from_url != ""){
							
							vm.selectDate(bookingpress_selected_service_id, bookingpress_selected_service_name, bookingpress_selected_service_price, bookingpress_selected_service_price_without_currency, "true",bookingpress_selected_service_duration,bookingpress_selected_service_duration_unit);

							vm.appointment_step_form_data.bookingpress_selected_bring_members = parseInt(bookingpress_selected_guest_from_url);

							let members = vm.appointment_step_form_data.bookingpress_selected_bring_members;
							let uniqueId = vm.appointment_step_form_data.bookingpress_uniq_id;
							let uniqueId2 = uniqueId.split("").reverse().join("");

							let salt = `${uniqueId}${members}${uniqueId2}`;
							let token = btoa( salt );
							vm.appointment_step_form_data.multiple_quantity_token = token;

							vm.bookingpress_update_staffmember_data(vm.appointment_step_form_data.bookingpress_selected_bring_members);
						}

						/* Select extras retrieved from url */
						if(vm.bookingpress_is_extra_enable == "1"){
							vm.bookingpress_service_extras.forEach(function(currentValue, index, arr){
								if(currentValue.bookingpress_extra_services_id in bookingpress_selected_extra_details){
									vm.appointment_step_form_data.bookingpress_selected_extra_details[currentValue.bookingpress_extra_services_id].bookingpress_is_selected = true;
									vm.appointment_step_form_data.bookingpress_selected_extra_details[currentValue.bookingpress_extra_services_id].bookingpress_selected_qty = parseInt(bookingpress_selected_extra_details[currentValue.bookingpress_extra_services_id]);
									vm.appointment_step_form_data.is_extra_service_exists = "1";
									vm.bookingpress_open_extras_drawer = "true";
								}
							});
						}
					}
				}else{
					if(vm.appointment_step_form_data.selected_service != ""){
						var bookingpress_selected_service_id = vm.appointment_step_form_data.selected_service;
						var bookingpress_selected_service_name = "";
						var bookingpress_selected_service_price = "";
						var bookingpress_selected_service_price_without_currency = "";
						var bookingpress_selected_service_duration = "";
						var bookingpress_selected_service_duration_unit = "";

						let currentValue = vm.bookingpress_all_services_data[ vm.appointment_step_form_data.selected_service ];
					
						bookingpress_selected_service_name = currentValue.bookingpress_service_name;
						if(vm.is_staffmember_activated == "1" || vm.is_any_staff_option_enable == "1"){
							bookingpress_selected_service_price = currentValue.bookingpress_staffmember_price || currentValue.bookingpress_service_price;
							bookingpress_selected_service_price_without_currency = currentValue.bookingpress_staffmember_price_without_currency || currentValue.service_price_without_currency;
						}else{
							bookingpress_selected_service_price = currentValue.bookingpress_service_price;
							bookingpress_selected_service_price_without_currency = currentValue.service_price_without_currency;
						}
						bookingpress_selected_service_duration = currentValue.bookingpress_service_duration_val;
						bookingpress_selected_service_duration_unit = currentValue.bookingpress_service_duration_unit
					
						

						let service_max_capacity = vm.appointment_step_form_data.service_max_capacity;
						if( 1 == vm.is_staffmember_activated ){
						} else {
							service_max_capacity = vm.bookingpress_all_services_data[ bookingpress_selected_service_id ].services_meta.max_capacity;
						}
						
						if( vm.bookingpress_is_extra_enable == "1" || ( vm.is_bring_anyone_with_you_activated == "1" && 1 < service_max_capacity ) ){
							vm.selectDate( bookingpress_selected_service_id, bookingpress_selected_service_name, bookingpress_selected_service_price, bookingpress_selected_service_price_without_currency, false );
						}

					}else{
						
						let selectedStaffmember;
						
						if( vm.is_staff_member_set_from_url == true || bookingpress_is_staff_load_from_share_url == "1"  ){
							
							selectedStaffmember = String(vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id);
							
							if( "0" == vm.is_service_loaded_from_url || false == vm.is_service_loaded_from_url ){
								vm.bookingpress_select_staffmember( selectedStaffmember, 0 );
							}else if( bookingpress_is_staff_load_from_share_url == "1" ){
								selectedStaffmember = String(vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id);
								vm.bookingpress_select_staffmember( selectedStaffmember, 0 );
							}
						}
					}
				}
			';

			if( !empty($_REQUEST['is_success']) && $_REQUEST['is_success'] == 2 ){
				$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_render_thankyou_content();';
				$bookingpress_dynamic_on_load_methods_data .= 'var bookingpress_uniq_id = vm.appointment_step_form_data.bookingpress_uniq_id;';
				$bookingpress_dynamic_on_load_methods_data .= 'document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).style.display = "none";';
				$bookingpress_dynamic_on_load_methods_data .= 'document.getElementById("bpa-thankyou-screen-div").style.display = "none";';
				$bookingpress_dynamic_on_load_methods_data .= 'document.getElementById("bpa-failed-screen-div").style.display = "block";';

				$bookingpress_nonce             = wp_create_nonce( 'bpa_wp_nonce' );

				$bookingpress_dynamic_on_load_methods_data .= '
				for(const step_data in this.bookingpress_sidebar_step_data){
					if(step_data != "summary"){
						vm.bookingpress_step_navigation(vm.bookingpress_sidebar_step_data[step_data].next_tab_name, vm.bookingpress_sidebar_step_data[step_data].next_tab_name, vm.bookingpress_sidebar_step_data[step_data].previous_tab_name, 0);
					}
				}';


				//$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_disable_date();';
				$bookingpress_dynamic_on_load_methods_data .= 'vm.get_date_timings();';
				$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_calculate_service_addons_price(vm.appointment_step_form_data.selected_service);';
				$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_modify_front_dates_as_service();';
				$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_get_service_capacity();';

				//$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_disable_date();';

				//Methods trigger for second step
				//----------------------------------------------------------------------------------------------
				if( !empty($_REQUEST['is_cart']) && ($_REQUEST['is_cart'] == 1) ){
					$bookingpress_dynamic_on_load_methods_data .= 'vm.get_date_timings();';
					$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_calculate_service_addons_price(vm.appointment_step_form_data.selected_service);';
					$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_get_service_capacity();';
				}

				if($bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation()){
					$bookingpress_dynamic_on_load_methods_data .= '
						var postData = { action:"bookingpress_get_deposit_amount",_wpnonce:"' . $bookingpress_nonce . '" };
						postData.appointment_data = JSON.stringify( vm.appointment_step_form_data );
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
						.then( function (response) {
							if(response.data.variant == "success"){
								vm.appointment_step_form_data.deposit_payment_type = response.data.deposit_type;
								vm.appointment_step_form_data.deposit_payment_amount = response.data.deposit_val;
							}
						}.bind(this) )
						.catch( function (error) {
							vm.bookingpress_set_error_msg(error)
						});';
				}

				//$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_disable_date();';
				//----------------------------------------------------------------------------------------------

				$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_recalculate_payable_amount();';
				
				if( !empty($_REQUEST['is_cart']) && ($_REQUEST['is_cart'] == 1) ){
					$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_refresh_cart_details();';

					$bookingpress_dynamic_on_load_methods_data .= 'setTimeout(function(){
						vm.bookingpress_recalculate_payable_amount();
					}, 1000);';
				}

				//----------------------------------------------------------------------------------------------

				//Methods trigger for final step
				$bookingpress_dynamic_on_load_methods_data .= '
					if(vm.appointment_step_form_data.coupon_code != ""){
						vm.bookingpress_apply_coupon_code();
					}
				';
			}else if( !empty($_REQUEST['is_success']) && $_REQUEST['is_success'] == 1 ){
				$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_render_thankyou_content();';
				$bookingpress_dynamic_on_load_methods_data .= 'var bookingpress_uniq_id = vm.appointment_step_form_data.bookingpress_uniq_id;';
				$bookingpress_dynamic_on_load_methods_data .= 'document.getElementById("bookingpress_booking_form_"+bookingpress_uniq_id).style.display = "none";';
				$bookingpress_dynamic_on_load_methods_data .= 'document.getElementById("bpa-thankyou-screen-div").style.display = "block";';
				$bookingpress_dynamic_on_load_methods_data .= 'document.getElementById("bpa-failed-screen-div").style.display = "none";';
			}

			$bookingpress_dynamic_on_load_methods_data .= 'vm.bookingpress_close_extra_drawer_on_mouseup();';

			if(!empty($_GET['bkp_pay'])){
				$bookingpress_dynamic_on_load_methods_data .= '';
			}
			
			return $bookingpress_dynamic_on_load_methods_data;
		}
		
		/**
		 * Function to rearrange sidebar steps data
		 *
		 * @param  mixed $bookingpress_front_vue_data_fields
		 * @return void
		 */
		function bookingpress_rearrange_sidebar_steps( $bookingpress_front_vue_data_fields ){
			global $BookingPress;
			
			$sidebar_step_data = isset($bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']) ? $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] : array();
			
			if( empty( $sidebar_step_data ) ){
				return $bookingpress_front_vue_data_fields;
			}

			$external_keys = array(
				'datetime_selection',
				'basic_details_selection',
				'summary_selection'
			);

			$external_keys = apply_filters( 'bookingpress_modify_form_sequence_for_rearrange', $external_keys );

			$cart_enabled = false;
			if( !empty( $sidebar_step_data['cart'] ) && !array_key_exists( 'sorting_key', $sidebar_step_data['cart'] ) ){
				$sidebar_step_data['cart']['sorting_key'] = 'cart_selection';
				$cart_enabled = true;
			}

			if( $cart_enabled && !in_array( 'cart_selection', $external_keys ) ){

				$datetime_pos = array_search( 'datetime_selection', $external_keys );
				array_splice( $external_keys, ($datetime_pos + 1), 0, 'cart_selection' );
			}

			$bookingpress_form_sequence = $BookingPress->bookingpress_get_customize_settings('bookingpress_form_sequance', 'booking_form');
			$bookingpress_form_sequence = json_decode($bookingpress_form_sequence, TRUE);
			$k = 0;
			$keys = array();
			
			foreach( array_merge( $bookingpress_form_sequence, $external_keys ) as $sequence ){
				$keys[ $sequence ] = $k;
				$k++;
			}
			
			uasort( $sidebar_step_data, function( $a, $b) use ($keys) {
				if( !empty( $a['sorting_key'] ) && !empty( $b['sorting_key']) && !empty( $keys[ $a['sorting_key'] ] )  ){
					//return $a[$bookingpress_form_sequance_arr];
					return $keys[ $a['sorting_key'] ] > $keys[ $b['sorting_key'] ] ? 1 : -1;
				} else {
					return -1;
				}
			} );
			
			$total_seq = count( $sidebar_step_data );
			//echo "<style>svg{ width:20px; height:20px; }</style>";
			
			$sidebar_step_data_keys = array_keys( $sidebar_step_data );
			
			$updated_sidebar_step_data = array();
			$n = 0;

			/** reset next and previous tab values */
			foreach( $sidebar_step_data as $fk => $fs ){
				$next = $n + 1;
				$prev = $n - 1;
				if( !empty( $sidebar_step_data_keys[ $next ] ) ){
					$sidebar_step_data[ $fk ]['next_tab_name'] = $sidebar_step_data_keys[ $next ];
				}
				if( !empty( $sidebar_step_data_keys[ $prev ] ) ){
					$sidebar_step_data[ $fk ]['previous_tab_name'] = $sidebar_step_data_keys[ $prev ];
				}
				if( $n == 0 ){
					$sidebar_step_data[ $fk ]['previous_tab_name'] = '';
					$sidebar_step_data[ $fk ]['is_first_step'] = 1;
				}
				
				$n++;
			}

			

			/** Reset first step data */
			$n1 = 0;
			foreach( $sidebar_step_data as $fk => $fs ){
				if( empty( $fs['is_display_step'] ) || 0  == $fs['is_display_step'] ){
					if( 1 == $fs['is_first_step'] ){
						$sidebar_step_data[ $fk]['is_first_step']  = 0;
					}
					continue;
				}
				if( $n1 == 0 ){
					$sidebar_step_data[ $fk ]['previous_tab_name'] = '';
					$sidebar_step_data[ $fk ]['is_first_step'] = 1;
					$bookingpress_front_vue_data_fields['bookingpress_current_tab'] = $sidebar_step_data[ $fk ]['tab_value'];
					
					/* break; */
				} else {
					$sidebar_step_data[ $fk ]['is_first_step'] = 0;
				}
				$n1++;
			}

			

			/** Reset next step data if it's hidden */
			$n2 = 0;
			foreach( $sidebar_step_data as $fk => $fs ){
				$next = $n2 + 1;
				$prev = $n2 - 1;
				$next_val = next( $sidebar_step_data );
				if( empty( $next_val )){
					break;
				}
				$sidebar_step_data[ $fk ]['next_tab_name'] = $this->bookingpress_get_next_visible_step_key( $fk, $next, $sidebar_step_data_keys, $sidebar_step_data, $n2 );

				$n2++;
			}


			

			/** Reset previous step data if it's hidden */
			$temp_sidebar_data = array_reverse( $sidebar_step_data );
			$n3 = 0;
			$temp_sidebar_step_keys = array_reverse( $sidebar_step_data_keys );
			$total_steps = count( $temp_sidebar_step_keys );
			foreach( $temp_sidebar_data as $tfk => $tfs ){
				$prev = $n3 + 1;
				$next = $n3 - 1;
				$prev_val = next( $temp_sidebar_data );
				if( empty( $prev_val ) ){
					break;
				}
				$temp_sidebar_data[ $tfk ]['previous_tab_name'] = $this->bookingpress_get_previous_visible_step_key( $tfk, $prev, $temp_sidebar_step_keys, $temp_sidebar_data, $n3, $total_steps );

				$n3++;
			}
			
			$sidebar_step_data = array_reverse( $temp_sidebar_data );
			
			$bookingpress_front_vue_data_fields['is_staff_first_step'] = apply_filters( 'bookingpress_set_staff_first_place', 0, $sidebar_step_data );
			if( 1 == $bookingpress_front_vue_data_fields['is_staff_first_step'] ){
				if( !empty( $_GET['bpstaffmember_id'] ) ){
					//$sidebar_step_data['service']['auto_focus_tab_callback']['bookingpress_select_staffmember'] = array( $_GET['bpstaffmember_id'], 0 ); //phpcs:ignore

					$sidebar_step_data['staffmembers']['auto_focus_tab_callback']['bookingpress_select_staffmember'] = array( intval($_GET['bpstaffmember_id']), 0 );
				}
			}

			if( !empty( $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] ) ){
				global $bookingpress_appointment_bookings;
				$bpa_selected_service_id = $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'];
				if( !empty( $_GET['s_id'] ) ){
					$bpa_selected_service_id = intval($_GET['s_id']);
				} else if( !empty( $_GET['bpservice_id'] ) ){
					$bpa_selected_service_id = intval($_GET['bpservice_id']);
				}

				$bpa_all_services_data = $bookingpress_front_vue_data_fields['bookingpress_all_services_data'];

				$bpa_reset_selected_service_data = false;

				if( empty( $bpa_all_services_data[ $bpa_selected_service_id ] ) ){
					$bpa_reset_selected_service_data = true;
				} else {
					$selected_service_data= $bpa_all_services_data[ $bpa_selected_service_id ];
					if( empty( $selected_service_data['is_visible'] ) || false == $selected_service_data['is_visible'] ){
						$bpa_reset_selected_service_data = true;
					} else if( !empty( $selected_service_data['is_disabled'] ) && true == $selected_service_data['is_disabled'] ){
						$bpa_reset_selected_service_data = true;
					}					
				}

				if( true == $bpa_reset_selected_service_data ){
					$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] = '';
					if( 1 == $sidebar_step_data['service']['is_navigate_to_next'] ){
						$sidebar_step_data['service']['is_navigate_to_next'] = 0;
					}
					
					/** If service step is hidden then enable it as the service is not selected */
					if( 0 == $sidebar_step_data['service']['is_display_step'] ){
						$sidebar_step_data['service']['is_display_step'] = 1;
					}

					if( 1 == $bookingpress_appointment_bookings->bookingpress_hide_category_service ){
						$bookingpress_appointment_bookings->bookingpress_hide_category_service = 0;	
					}
				}
			}

			

			$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] = $sidebar_step_data;


			/** reset selected category id based on selected service */
			$selected_service = !empty( $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] ) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] : '';
			$default_selected_category = !empty( $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_category'] ) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_category'] : '';
			$selected_staffmember_id = (!empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_staff_member_id']))?$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_staff_member_id']:'';
			
			if( !empty( $selected_service ) ){
				$selected_services_data = $bookingpress_front_vue_data_fields['bookingpress_all_services_data'][ $selected_service ];

				$selected_service_category = $selected_services_data['bookingpress_category_id'];

				if( $selected_service_category != $default_selected_category ){
					$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_category'] = $selected_service_category;
				}
			} else if( empty( $selected_service ) && !empty( $selected_staffmember_id ) ){
				$staffmember_services = array();
				foreach( $bookingpress_front_vue_data_fields['bookingpress_all_services_data'] as $ser_id => $ser_data ){
					if( !empty( $ser_data['assigned_staffmembers'] ) && in_array( $selected_staffmember_id, $ser_data['assigned_staffmembers'] ) ){
						$staffmember_services[ $ser_id ] = $ser_data;
					}
				}
				
				if( !empty( $staffmember_services ) ){
					$first_staff_service_category = $staffmember_services[ array_key_first( $staffmember_services ) ]['bookingpress_category_id'];
					$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_category'] = $first_staff_service_category;
					$staff_member_services = array_keys( $staffmember_services );
					
					$bpa_all_categories = $bookingpress_front_vue_data_fields['bookingpress_all_categories'];
					
					foreach( $bpa_all_categories as $cat_id => $cat_details ){
						$service_ids = $cat_details['service_ids'];
						if( 0 === $cat_id ){
							continue;
						}
						foreach( $service_ids as $n => $cat_s_id ){
							if( !in_array( $cat_s_id, $staff_member_services ) ){
								unset( $bpa_all_categories[ $cat_id ]['service_ids'][$n]  );
								$bpa_all_categories[ $cat_id ]['total_services']--;
							}

							if( 1 > $bpa_all_categories[ $cat_id ]['total_services'] ){
								unset( $bpa_all_categories[ $cat_id ] );
							}
						}							
					}
				
					$bookingpress_front_vue_data_fields['bookingpress_all_categories'] = $bpa_all_categories;
				}
			}
			
            $bookingpress_default_select_all_category = $BookingPress->bookingpress_get_customize_settings('default_select_all_category','booking_form');
            if(!empty($bookingpress_default_select_all_category ) && $bookingpress_default_select_all_category == 'true') {
                if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_category'])){
					$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_category'] = 0;
				}				
            }			

			return $bookingpress_front_vue_data_fields;
		}

		function bookingpress_get_next_visible_step_key( $current_key, $next_key, $sidebar_step_data_keys, $sidebar_step_data, $n2 ){

			if( !empty( $sidebar_step_data[ $sidebar_step_data_keys[ $next_key ] ] ) && 1 == $sidebar_step_data[ $sidebar_step_data_keys[ $next_key ] ]['is_display_step'] ) {
				return $sidebar_step_data_keys[ $next_key ];
			} else {
				
				$new_next_key = $sidebar_step_data_keys[ $next_key + 1 ];

				return $this->bookingpress_get_next_visible_step_key( $current_key, $next_key + 1, $sidebar_step_data_keys, $sidebar_step_data, $n2 + 1 );
			}
		}

		function bookingpress_get_previous_visible_step_key( $current_key, $prev_key, $sidebar_step_data_keys, $temp_sidebar_data, $n3, $total_steps ){
			if( $total_steps <= $n3 || !isset( $sidebar_step_data_keys[ $prev_key ] ) ){
				return '';
			}
			
			if( !empty( $temp_sidebar_data[ $sidebar_step_data_keys[ $prev_key ] ] ) && 1 == $temp_sidebar_data[ $sidebar_step_data_keys[ $prev_key ] ]['is_display_step'] ){
				return $sidebar_step_data_keys[ $prev_key ];
			} else {
				return  $this->bookingpress_get_previous_visible_step_key( $current_key, $prev_key + 1, $sidebar_step_data_keys, $temp_sidebar_data, $n3 + 1, $total_steps );
			}
		}

		function bookingpress_get_next_key( $step_data, $next_key, $sidebar_step_data_keys, $current_key ){
			$next_key_value = '';
			
			$next_key_index = array_search( $next_key, $sidebar_step_data_keys );
			
			if( '' !== $next_key_index ){
				$step_updated_data = array_slice( $step_data, $next_key_index, count( $step_data ) );
				
				foreach( $step_updated_data as $step_key => $step_value ){
					if( $step_key == $current_key ){
						continue;
					}
					if( 1 == $step_value['is_display_step'] ){
						$next_key_value = $step_key;
						break;
					}
				}
			}

			return $next_key_value;
		}

		function bookingpress_get_prev_key( $step_data, $prev_key, $sidebar_step_data_keys ){
			$prev_key_index = array_search( $prev_key, $sidebar_step_data_keys );
			$prev_key_value =  '';
			if( '' !== $prev_key_index ){
				$step_updated_data = array_reverse( array_slice( $step_data, 0, $prev_key_index ) );
				foreach( $step_updated_data as $step_key => $step_value ){
					if( 1 == $step_value['is_display_step'] ){
						$prev_key_value = $step_key;
						break;
					}
				}
			}

			return $prev_key_value;
		}
		
		/**
		 * Function to add flag to define first step flag.
		 *
		 * @param  mixed $bookingpress_front_vue_data_fields
		 * @return void
		 */
		function bookingpress_extra_data_for_sidebar_steps( $bookingpress_front_vue_data_fields ){

			$sidebar_step_data = isset($bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']) ? $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] : array();
			
			$step_counter = 0;
			$n = 0;
			$skip_steps_checking = false;
			foreach( $sidebar_step_data as $key => $step_data ){
				if( (!isset( $step_data['is_display_step'] ) || ( isset( $step_data['is_display_step'] ) && 1 == $step_data['is_display_step'] )) && $step_counter >= 0 && false == $skip_steps_checking  ){
					$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'][$key]['is_first_step'] = 1;
					$skip_steps_checking = true;
					/** Set services auto focus function if staff is before step */
					if( "staffmembers" == $key ){
						$next_tab = $sidebar_step_data[$key]['next_tab_name'];
						$prev_tab = $sidebar_step_data[$key]['previous_tab_name'];
						$is_staff_loaded_from_url = $bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_url'];
						if( "service" == $next_tab || "staffmembers" == $prev_tab || "" == $prev_tab ){
							
							if( "true" == $is_staff_loaded_from_url ){
								//$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['service'];
								$staffmember_id = $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_staff_member_details']['staff_member_id'];
								
								$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['service']['auto_focus_tab_callback']['bookingpress_select_staffmember'] = array(
									(String)$staffmember_id,
									0
								);
							}
						}
					}
				} else {
					$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'][$key]['is_first_step'] = 0;
				}
				$step_counter++;
			}

			$sidebar_step_data = isset($bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']) ? $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] : array();
			
			$sc = 0;
			foreach( $sidebar_step_data as $step_name => $step_data ){
				$is_first_step = $step_data['is_first_step'];
				$previous_tab_name = $step_data['previous_tab_name'];
				$is_display_step = isset($step_data['is_display_step']) ? $step_data['is_display_step'] : 1;

				if( 1 == $is_display_step && 0 < $sc && $is_first_step == 0 ){
					/** Check if previous set tab is visible and if not, then check it's previous tab until we got the visible step */
					
					$previous_visible_step_data = $this->bookingpress_check_previous_visible_step_data( $sidebar_step_data, $previous_tab_name );
					if( null != $previous_visible_step_data ){
						$sidebar_step_data[$step_name]['previous_tab_name'] = $previous_visible_step_data['tab_value'];
					}
					break;
				}
				$sc++;
			}

			
			$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] = $sidebar_step_data;
		

			return $bookingpress_front_vue_data_fields;
		}
		
		/**
		 * Check if the tab is visible or not
		 *
		 * @param  mixed $sidebar_step_data - complete sidebar step data
		 * @param  mixed $step_key - tab key to check if it's visible or not
		 * @return void
		 */
		function bookingpress_check_previous_visible_step_data( $sidebar_step_data, $step_key ){
			
			$step_data = !empty( $sidebar_step_data[ $step_key ] ) ? $sidebar_step_data[ $step_key ] : array();
			if( empty( $step_data ) ){
				return null;
			}
			$is_visible_step = $step_data['is_display_step'] || 0;
			if( 0 == $is_visible_step ){
				return $this->bookingpress_check_previous_visible_step_data( $sidebar_step_data, $step_data['previous_tab_name'] );
			} else {
				return $sidebar_step_data[ $step_key ];
			}
		}
		
		/**
		 * Function to modify all services array ( set disabled flag for services which are disabled or expired )
		 *
		 * @param  mixed $bpa_all_services
		 * @param  mixed $service
		 * @param  mixed $selected_service
		 * @param  mixed $bookingpress_category
		 * @return void
		 */
		function bookingpress_front_add_modify_all_services_data( $bpa_all_services, $service, $selected_service, $bookingpress_category ){
			global $BookingPress;
			$day_text = $BookingPress->bookingpress_get_customize_settings('book_appointment_day_text', 'booking_form');
			$day_text = !empty($day_text) ? stripslashes_deep($day_text) : esc_html__('d', 'bookingpress-appointment-booking');

			foreach( $bpa_all_services as $bpskey => $bpsvalue ){
				$smetas = $bpsvalue['services_meta'];
				$bpa_all_services[ $bpskey ]['is_disabled'] = false;
				if( isset( $smetas['show_service_on_site'] ) && empty( $smetas['show_service_on_site']) ){
					$bpa_all_services[ $bpskey ]['is_disabled'] = true;
				}

				$minimum_time_required  = 'disabled';
				$minimum_time_required  = apply_filters( 'bookingpress_retrieve_minimum_required_time', $minimum_time_required, $bpsvalue['bookingpress_service_id'] );

				if ( !empty($bpsvalue['bookingpress_service_expiration_date'] ) && $minimum_time_required != 'disabled' && ( $minimum_time_required >= 1440) ) {

					$bookingpress_service_expiration_date = !empty($bpsvalue['bookingpress_service_expiration_date']) ? $bpsvalue['bookingpress_service_expiration_date'] : '';
					$booking_date_end_timestamp = strtotime( $bookingpress_service_expiration_date);

					$bookingpress_total_days 			 = intval( $minimum_time_required ) / 1440;						
					$bookingpress_current_date           = date( 'Y-m-d',strtotime( '+' . $bookingpress_total_days . 'days', current_time( 'timestamp' )) );
					$bookingpress_current_date_timestamp = strtotime( $bookingpress_current_date );

					if( $booking_date_end_timestamp <= $bookingpress_current_date_timestamp ){
						$bpa_all_services[ $bpskey ]['is_disabled'] = true;
					}
					
				} else if( !empty( $bpsvalue['bookingpress_service_expiration_date'] ) ){

					$expiration_date = strtotime( $bpsvalue['bookingpress_service_expiration_date'] );
					$current_timestamp = strtotime( date('Y-m-d', current_time('timestamp') ) );

					if( $current_timestamp > $expiration_date ){
						$bpa_all_services[ $bpskey ]['is_disabled'] = true;
					}
				}

				/* Service start date changes to hide/show the service */

				if ( !empty($bpsvalue['bookingpress_service_start_date'] ) && $minimum_time_required != 'disabled' && ( $minimum_time_required >= 1440) ) {

					$bookingpress_service_start_date = !empty($bpsvalue['bookingpress_service_start_date']) ? $bpsvalue['bookingpress_service_start_date'] : '';
					$booking_date_start_timestamp = strtotime( $bookingpress_service_start_date);

					$bookingpress_total_days 			 = intval( $minimum_time_required ) / 1440;						
					$bookingpress_current_date           = date( 'Y-m-d',strtotime( '+' . $bookingpress_total_days . 'days', current_time( 'timestamp' )) );
					$bookingpress_current_date_timestamp = strtotime( $bookingpress_current_date );
					
					if( $booking_date_start_timestamp > $bookingpress_current_date_timestamp ){
						$bpa_all_services[ $bpskey ]['is_disabled'] = true;
					}
					
				} else if( !empty( $bpsvalue['bookingpress_service_start_date'] ) ){

					$start_date = strtotime( $bpsvalue['bookingpress_service_start_date'] );
					$current_timestamp = strtotime( date('Y-m-d', current_time('timestamp') ) );

					if( $current_timestamp < $start_date ){
						$bpa_all_services[ $bpskey ]['is_disabled'] = true;
					}
				}

				if(!empty($bpsvalue['bookingpress_service_duration_unit']) && 'd' == $bpsvalue['bookingpress_service_duration_unit']) {
					$bpa_all_services[ $bpskey ]['bookingpress_service_duration_label'] = $day_text;
				}
			}

			return $bpa_all_services;
		}
		
		/**
		 * hide disabled services or services that is not assigned to staff member ( if staff member is enabled ) when selecting category
		 *
		 * @param  mixed $bookingpress_modify_select_service_category
		 * @return void
		 */
		function bookingpress_modify_select_service_category_func( $bookingpress_modify_select_service_category ){

			$bookingpress_modify_select_service_category .= '
				if( ("undefined" != typeof current_service.is_disabled && true == current_service.is_disabled) || ( "undefined" != current_service.hide_for_staff && true == current_service.hide_for_staff )  ){
					current_service.is_visible = false;
				}
			';

			return $bookingpress_modify_select_service_category;
		}
		
		/**
		 * update default selected service id if the service id is 0, empty or disabled
		 *
		 * @param  int $default_service_id
		 * @param  array $bookingpress_front_vue_data
		 * 
		 * @return int $default_service_id
		 */
		function bookingpress_update_default_service_id( $default_service_id, $bookingpress_front_vue_data ){

			$all_service_data = $bookingpress_front_vue_data['bookingpress_all_services_data'];

			$skip_checking = false;
			if( !empty( $_GET['s_id'] ) ){
				$skip_checking = true;
			}
			
			if( !empty( $default_service_id ) ){
				if( empty( $all_service_data[ $default_service_id ] ) || false == $all_service_data[ $default_service_id ] ){
					foreach( $all_service_data as $service_id => $service_data ){
						if( $service_id == $default_service_id ){
							continue;
						}
						if( false == $skip_checking && ( empty( $service_data['is_disabled'] ) || false == $service_data['is_disabled'] ) ){
							$default_service_id = $service_id;
							break;
						}
					}
				}
				return $default_service_id;
			} else {
				foreach( $all_service_data as $service_id => $service_data ){
					if( false == $skip_checking && empty( $service_data['is_disabled'] ) || false == $service_data['is_disabled'] ){
						$default_service_id = $service_id;
						break;
					}
				}
			}

			return $default_service_id;
		}
		
		/**
		 * Function for add data variables of bookingpress_form shortcode
		 *
		 * @param  mixed $bookingpress_front_vue_data_fields
		 * @return void
		 */
		function bookingpress_frontend_add_appointment_data_variables( $bookingpress_front_vue_data_fields ) {
			global $BookingPress, $BookingPressPro, $bookingpress_coupons, $tbl_bookingpress_form_fields, $wpdb, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services, $tbl_bookingpress_extra_services, $bookingpress_bring_anyone_with_you, $bookingpress_services, $bookingpress_service_extra, $tbl_bookingpress_services, $bookingpress_deposit_payment, $tbl_bookingpress_entries, $tbl_bookingpress_categories, $bookingpress_pro_version,$bookingpress_appointment_bookings;

			$bookingpress_minimum_time_required_before_booking = $BookingPress->bookingpress_get_settings( 'default_minimum_time_for_booking', 'general_setting' );
			$bookingpress_front_vue_data_fields['bookingpress_default_minimum_time_required_for_booking'] = $bookingpress_minimum_time_required_before_booking;

			$bookingpress_front_vue_data_fields['bookingpress_timezone'] = '';
			$bookingpress_front_vue_data_fields['bookingpress_timezone_offset'] = '';
			
			$bookingpress_front_vue_data_fields['bookingpress_pro_version'] = $bookingpress_pro_version;
			

			$bookingpress_front_vue_data_fields['bookingpress_booking_before_block_date'] = array();

			$bookingpress_front_vue_data_fields['is_coupon_activated'] = $bookingpress_coupons->bookingpress_check_coupon_module_activation();
			$bookingpress_front_vue_data_fields['bookingpress_allow_coupon_code'] = $bookingpress_coupons->bookingpress_check_coupon_module_activation();
			$bookingpress_front_vue_data_fields['is_tax_activated']    = '';

			$is_additional_module_activated = false;
			$bookingpress_front_vue_data_fields['is_additional_module_activated'] = $is_additional_module_activated = apply_filters('bookingpress_modify_total_payable_amount_value_front',$is_additional_module_activated);

			if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_name'])) {
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_name'] = !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_name']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_name'] : '';
			}			
			if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_username'])){
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_username'] = !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_username']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_username'] : '';
			}
			if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_firstname'])) {
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_firstname'] = !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_firstname']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_firstname'] : '';
			}
			if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_lastname'])) {
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_lastname'] = !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_lastname']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_lastname'] : '';
			}
			if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_email'])) {
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_email'] = !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_email']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_email'] : '';
			}
			if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone'])) {
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_phone'] = !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone'] : '';
			}
			if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone_country'])) {
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_phone_country'] = !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone_country']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone_country'] : '';
			}
			if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['appointment_note'])) {
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['appointment_note'] = !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['appointment_note']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['appointment_note'] : '';
			}
			if(isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['appointment_terms_conditions'])) {
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['appointment_terms_conditions'] = !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['appointment_terms_conditions']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['appointment_terms_conditions'] : array();
			}

			$all_external_fields = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_meta_key,bookingpress_form_field_name FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_type NOT IN ('2_col', '3_col', '4_col', 'repeater') AND bookingpress_form_field_name NOT IN ('2 Col', '3 Col', '4 Col', 'Repeater') AND bookingpress_field_is_default != %d", 1) ); // phpcs:ignore
			if( !empty( $all_external_fields ) ){
				foreach( $all_external_fields as $external_field_data ){
					$field_name = $external_field_data->bookingpress_form_field_name;
					$field_metakey = $external_field_data->bookingpress_field_meta_key;
					if( $field_name == 'Password'){
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_password'] = '';
					} else {
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'][ $field_metakey ] = '';
					}
				}
			}
			
			$bookingpress_front_vue_data_fields['bookingpress_disabled_staffmember'] = array();

			$bookingpress_front_vue_data_fields['appointment_step_form_data']['coupon_code']          = '';
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['total_payable_amount'] = '';
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['total_payable_amount_with_currency'] = '';

			$bookingpress_front_vue_data_fields['appointment_step_form_data']['card_holder_name'] = '';
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['card_number']      = '';
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['expire_month']     = '';
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['expire_year']      = '';
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['cvv']              = '';

			$bookingpress_front_vue_data_fields['coupon_code_msg']           = '';
			$bookingpress_front_vue_data_fields['coupon_applied_status']     = 'error';
			$bookingpress_front_vue_data_fields['coupon_discounted_amount']  = 0;
			$bookingpress_front_vue_data_fields['coupon_apply_loader']       = '0';
			$bookingpress_front_vue_data_fields['bpa_coupon_apply_disabled'] = 0;

			$bookingpress_front_vue_data_fields['isLoadServiceLoader'] = '0';

			$bookingpress_front_vue_data_fields['appointment_step_form_data']['bpa_password_already_exists'] = false;
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['bpa_user_email_already_exists'] = false;
			$bookingpress_front_vue_data_fields['bookingpress_activate_payment_gateway_total_counter'] = 0;

			//$bookingpress_form_fields = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_field_position ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm

			$bookingpress_form_fields = wp_cache_get( 'bpa_appointment_booking_form_fields_data_');
				if( false == $bookingpress_form_fields )
                {
                    $bookingpress_form_fields = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_field_position ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
                    wp_cache_set( 'bpa_appointment_booking_form_fields_data_', $bookingpress_form_fields);
                }

			$bookingpress_form_fields_error_msg_arr = $bookingpress_form_fields_new = $bookingpress_field_list = array();
			foreach ( $bookingpress_form_fields as $bookingpress_form_field_key => $bookingpress_form_field_val ) {
				$bookingpress_v_model_value = '';
				if ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'fullname' ) {
					$bookingpress_field_list['customer_name'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
				} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'firstname' ) {
					$bookingpress_field_list['customer_firstname'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
				} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'lastname' ) {
					$bookingpress_field_list['customer_lastname'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
				} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'email_address' ) {
					$bookingpress_field_list['customer_email'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
				} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'phone_number' ) {
					$bookingpress_field_list['customer_phone'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
				} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'note' ) {
					$bookingpress_field_list['appointment_note'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
				} elseif ( $bookingpress_form_field_val['bookingpress_form_field_name'] == 'username' ) {
					$bookingpress_field_list['customer_username'] = $bookingpress_form_field_val['bookingpress_form_field_id'];
				} else {
					/* $bookingpress_field_list[ $bookingpress_form_field_val['bookingpress_form_field_name'] ] = $bookingpress_form_field_val['bookingpress_form_field_id']; */
					$bookingpress_field_list[$bookingpress_form_field_val['bookingpress_field_meta_key']] = $bookingpress_form_field_val['bookingpress_form_field_id'];
				}
			}


			$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_front_field_data'] = $bookingpress_field_list;

			// Data Fields For Card Fields
			// -----------------------------------------------------------------------------------------------------------
			$bookingpress_front_vue_data_fields['is_display_card_option'] = 0;

			$bookingpress_front_vue_data_fields['months'] = array(
				array( 'month' => '01' ),
				array( 'month' => '02' ),
				array( 'month' => '03' ),
				array( 'month' => '04' ),
				array( 'month' => '05' ),
				array( 'month' => '06' ),
				array( 'month' => '07' ),
				array( 'month' => '08' ),
				array( 'month' => '09' ),
				array( 'month' => '10' ),
				array( 'month' => '11' ),
				array( 'month' => '12' ),
			);			

			$bookingpress_front_vue_data_fields['years']        = array();
			$bookingpress_front_vue_data_fields['timeToExpire'] = 9;
			$bookingpress_front_vue_data_fields['cardVadid']    = '';
			$bookingpress_front_vue_data_fields['cardType']     = '';
			$bookingpress_front_vue_data_fields['cClass']       = '';
			$bookingpress_front_vue_data_fields['cardHolder']   = '';
			$bookingpress_front_vue_data_fields['regx']         = array(
				array(
					'name' => 'Visa',
					'logo' => 'https://seeklogo.com/images/V/visa-logo-CF29426B98-seeklogo.com.png',
					're'   => '^4',
				),
				array(
					'name' => 'Hipercard',
					'logo' => 'https://cdn.worldvectorlogo.com/logos/hipercard.svg',
					're'   => '/^(606282\d{10}(\d{3})?)|(3841\d{15})$/',
				),
				array(
					'name' => 'MasterCard',
					'logo' => 'https://logodownload.org/wp-content/uploads/2014/07/mastercard-logo-novo-3.png',
					're'   => '/^(5[1-5]|677189)|^(222[1-9]|2[3-6]\d{2}|27[0-1]\d|2720)/',
				),
				array(
					'name' => 'Discover',
					'logo' => 'https://i.pinimg.com/originals/b3/d7/85/b3d7853a11dcc8c424866915ddd4d3e3.png',
					're'   => '/^(6011|65|64[4-9]|622)/',
				),
				array(
					'name' => 'Elo',
					'logo' => 'https://seeklogo.com/images/E/elo-logo-0B17407ECC-seeklogo.com.png',
					're'   => '/^(4011(78|79)|43(1274|8935)|45(1416|7393|763(1|2))|50(4175|6699|67[0-7][0-9]|9000)|627780|63(6297|6368)|650(03([^4])|04([0-9])|05(0|1)|4(0[5-9]|3[0-9]|8[5-9]|9[0-9])|5([0-2][0-9]|3[0-8])|9([2-6][0-9]|7[0-8])|541|700|720|901)|651652|655000|655021)/',
				),
				array(
					'name' => 'American Express',
					'logo' => 'https://ccard-generator.com/assets/images/cardmedium/american-express.png',
					're'   => '/^3[47]\d{13,14}$/',
				),
			);

			// -----------------------------------------------------------------------------------------------------------

			/* Phone number field placeholder integration */

            $bookingpress_form_fields = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_field_placeholder,bookingpress_field_options FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_name = %s", 'phone_number' ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
            $bookingpress_phone_number_field_placeholder = ! empty( $bookingpress_form_fields['bookingpress_field_placeholder'] ) ? $bookingpress_form_fields['bookingpress_field_placeholder']: '';
            $bookingpress_field_options = ! empty( $bookingpress_form_fields['bookingpress_field_options'] ) ? json_decode( $bookingpress_form_fields['bookingpress_field_options'], true ) : '';
            $bookingpress_set_custom_field = (!empty( $bookingpress_field_options['set_custom_placeholder']) && $bookingpress_field_options['set_custom_placeholder'] == 'true') ? 'true' : 'false' ;
            $bookingpress_front_vue_data_fields['bookingpress_phone_default_placeholder'] = $bookingpress_set_custom_field;

			$bookingpress_front_vue_data_fields['bookingpress_tel_input_props']['inputOptions'] = array(
				'placeholder' => $bookingpress_phone_number_field_placeholder
			);

			if ( ! empty( $bookingpress_front_vue_data_fields['bookingpress_tel_input_props']['defaultCountry'] ) && $bookingpress_front_vue_data_fields['bookingpress_tel_input_props']['defaultCountry'] == 'auto_detect' ) {
				// Get visitors ip address
				$bookingpress_ip_address = $BookingPressPro->boookingpress_get_visitor_ip();
				try {
					$bookingpress_country_reader = new Reader( BOOKINGPRESS_PRO_LIBRARY_DIR . '/geoip/inc/GeoLite2-Country.mmdb' );
					$bookingpress_country_record = $bookingpress_country_reader->country( $bookingpress_ip_address );
					if ( ! empty( $bookingpress_country_record->country ) ) {
						$bookingpress_country_name     = $bookingpress_country_record->country->name;
						$bookingpress_country_iso_code = $bookingpress_country_record->country->isoCode;
						$bookingpress_front_vue_data_fields['bookingpress_tel_input_props']['defaultCountry'] = $bookingpress_country_iso_code;
					}
				} catch ( Exception $e ) {
					$bookingpress_error_message = $e->getMessage();
				}
			}

			$bookingpress_all_services_data_with_meta = (isset($bookingpress_front_vue_data_fields['bookingpress_all_services_data']))?$bookingpress_front_vue_data_fields['bookingpress_all_services_data']:array();

			/** Phone number field placeholder integration **/

			$bookingpress_front_vue_data_fields['bookingpress_current_datetime'] = current_time( 'mysql' );
			$bookingpress_is_deposit_payment_activate = $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation();

			$bookingpress_front_vue_data_fields['bookingpress_is_deposit_payment_activate'] = $bookingpress_is_deposit_payment_activate;
			$bookingpress_deposit_payment_method = "allow_customer_to_pay_full_amount";
			if(!empty($BookingPress->bookingpress_get_settings( 'bookingpress_allow_customer_to_pay', 'payment_setting' ))){
				$bookingpress_deposit_payment_method = $BookingPress->bookingpress_get_settings( 'bookingpress_allow_customer_to_pay', 'payment_setting' );
			}
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_deposit_payment_method'] = $bookingpress_deposit_payment_method;
			$bookingpress_front_vue_data_fields['bookingpress_deposit_payment_method'] = $bookingpress_deposit_payment_method;
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_type'] = '';
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_amount'] = '';
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_amount_percentage'] = '';
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_formatted_amount'] = '';

			/* Deposit payment integration */

			if($bookingpress_is_deposit_payment_activate && !empty($bookingpress_front_vue_data_fields['services_data'])){
				foreach($bookingpress_front_vue_data_fields['services_data'] as $k => $v){
					$bookingpress_service_id = intval($v['bookingpress_service_id']);
					$bookingpress_deposit_type = "";
					if($bookingpress_service_id && !empty($bookingpress_all_services_data_with_meta) && isset($bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['deposit_type'])){
						$bookingpress_deposit_type = $bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['deposit_type'];
					}else{
						$bookingpress_deposit_type = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'deposit_type');
					}
					if( ($bookingpress_deposit_payment_method == "deposit_or_full_price" || $bookingpress_deposit_payment_method == "allow_customer_to_pay_full_amount") && !empty($bookingpress_deposit_type)){
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_type'] = $bookingpress_deposit_type;
						$bookingpress_deposit_amount = "";
						if($bookingpress_service_id && !empty($bookingpress_all_services_data_with_meta) && isset($bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['deposit_amount'])){
							$bookingpress_deposit_amount = $bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['deposit_amount'];
						}else{
							$bookingpress_deposit_amount = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'deposit_amount');
						}						
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_amount'] = $bookingpress_deposit_amount;
						if($bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_type'] != "percentage"){
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_formatted_amount'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_amount']);
						}else{
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_amount_percentage'] = $bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_amount'];
						}
					}
				}
			}
			/** Deposit payment integration **/

			/* Service extra integration */
			$bookingpress_check_service_extra_module_activation = $bookingpress_service_extra->bookingpress_check_service_extra_module_activation();
			$bookingpress_front_vue_data_fields['bookingpress_is_extra_enable'] = $bookingpress_check_service_extra_module_activation;
			$bookingpress_selected_extra_details = $bookingpress_service_extras = array();			
			if($bookingpress_check_service_extra_module_activation){

				$bookingpress_all_service_extra_data = $bookingpress_service_extra->bookingpress_get_all_service_extras();				
				if(isset($bookingpress_all_service_extra_data['bookingpress_selected_extra_details'])){
					$bookingpress_selected_extra_details = $bookingpress_all_service_extra_data['bookingpress_selected_extra_details'];
				}
				if(isset($bookingpress_all_service_extra_data['bookingpress_service_extras'])){
					$bookingpress_service_extras = $bookingpress_all_service_extra_data['bookingpress_service_extras'];
				}

			}
			
			$bookingpress_front_vue_data_fields['bookingpress_service_extras'] = $bookingpress_service_extras;
			
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_extra_details'] = $bookingpress_selected_extra_details;
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_extra_service_count'] = 0;
			
			/** Service extra integration **/

			/* Bring anyone with you integration */
			$bookingpress_is_bring_anyone_with_you_activated = $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation();
			$bookingpress_front_vue_data_fields['is_bring_anyone_with_you_activated'] = $bookingpress_is_bring_anyone_with_you_activated;

			$bookingpress_bring_anyone_with_you_details = array();
			if ( $bookingpress_is_bring_anyone_with_you_activated ) {
				$bookingpress_services_details = $wpdb->get_results("SELECT * FROM ".$tbl_bookingpress_services, ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
				foreach ( $bookingpress_services_details as $k => $v ) {
					$bookingpress_service_id = intval( $v['bookingpress_service_id'] );					
					$bookingpress_service_max_capacity   = '';				
					if($bookingpress_service_id && !empty($bookingpress_all_services_data_with_meta) && isset($bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['max_capacity'])){
						$bookingpress_service_max_capacity  = $bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['max_capacity'];					
					}else{
						$bookingpress_service_max_capacity = $bookingpress_services->bookingpress_get_service_meta( $bookingpress_service_id, 'max_capacity' );
					}
					$bookingpress_service_max_capacity = ! empty( $bookingpress_service_max_capacity ) ? $bookingpress_service_max_capacity : 0;

					/* min capacity */
					$bookingpress_service_min_capacity   = '';
					if($bookingpress_service_id && !empty($bookingpress_all_services_data_with_meta) && isset($bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['min_capacity'])){
						$bookingpress_service_min_capacity  = $bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['min_capacity'];
					}else{
						$bookingpress_service_min_capacity = $bookingpress_services->bookingpress_get_service_meta( $bookingpress_service_id, 'min_capacity' );
					}
					$bookingpress_service_min_capacity = ! empty( $bookingpress_service_min_capacity ) ? $bookingpress_service_min_capacity : 0;
					
					//if($bookingpress_service_max_capacity > 0){
						$bookingpress_bring_anyone_with_you_details[$bookingpress_service_id]['bookingpress_service_min_capacity'] = intval( $bookingpress_service_min_capacity );
						$bookingpress_bring_anyone_with_you_details[$bookingpress_service_id]['bookingpress_service_max_capacity'] = intval( $bookingpress_service_max_capacity );
						$bookingpress_bring_anyone_with_you_details[$bookingpress_service_id]['bookingpress_service_id'] = $bookingpress_service_id;
					//}
					$bookingpress_bring_anyone_with_you_details = apply_filters( 'bookingpress_modify_bringanyone_details', $bookingpress_bring_anyone_with_you_details, $bookingpress_service_id );
				}
			}

			$bookingpress_front_vue_data_fields['bookingpress_bring_anyone_with_you_details'] = $bookingpress_bring_anyone_with_you_details;
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_bring_members'] = 1;
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['service_max_capacity'] = 1;
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['service_min_capacity'] = 1;

			/** Bring anyone with you integration **/

			// Staff members integration options

			$bookingpress_is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_front_vue_data_fields['is_staffmember_activated'] = $bookingpress_is_staffmember_module_activated;

			$bookingpress_selected_staffmember_id = 0;
			$bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_url'] = "false";
			$bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_share_url'] = "false";
			if(!empty($_REQUEST['bpstaffmember_id']) || !empty($_REQUEST['sm_id']) ){
				if(!empty($_REQUEST['bpstaffmember_id'])){
					$bookingpress_selected_staffmember_id = intval($_REQUEST['bpstaffmember_id']);
					$bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_url'] = "true";
				}else if(!empty($_REQUEST['sm_id'])){
					$bookingpress_selected_staffmember_id = intval($_REQUEST['sm_id']);
					$bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_url'] = "true";
					$bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_share_url'] = "true";
				}
			}

			/** Check if selected staffmember is exists or not. If exists then check if it's  */
			$bookingpress_sm_id_before = $bookingpress_selected_staffmember_id;
			$bookingpress_selected_staffmember_id = apply_filters( 'bookingpress_modify_staffmember_id', $bookingpress_selected_staffmember_id, $bookingpress_front_vue_data_fields );
			
			if( !empty( $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] ) && $bookingpress_sm_id_before != $bookingpress_selected_staffmember_id && !empty( $bookingpress_selected_staffmember_id ) ){
				$bookingpress_selected_staffmember_id = $bookingpress_sm_id_before;
				$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] = '';
				if( 1 != $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['service']['is_display_step'] ){
					$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['service']['is_display_step'] = 1;
					$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['service']['is_navigate_to_next'] = 0;
					$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['service']['is_allow_navigate'] = 1;
				}
				
				$bookingpress_appointment_bookings->bookingpress_is_service_load_from_url = false;
				$bookingpress_appointment_bookings->bookingpress_hide_category_service = false;
			}
			
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] = $bookingpress_selected_staffmember_id;
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_staff_member_details']['staff_member_id'] = $bookingpress_selected_staffmember_id;
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_staff_member_details']['select_any_staffmember'] = "false";
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_staff_member_id'] = $bookingpress_selected_staffmember_id;
			
			$bookingpress_front_vue_data_fields['is_any_staff_option_enable'] = 0;
			$bookingpress_is_anystaff_option_enable = $BookingPress->bookingpress_get_settings( 'bookingpress_staffmember_any_staff_options', 'staffmember_setting' );
			if($bookingpress_is_anystaff_option_enable == "true"){
				$bookingpress_front_vue_data_fields['is_any_staff_option_enable'] = 1;
			}

			/* Get Serivce Max Capacity */
			$bookingpress_all_services_data = !empty($bookingpress_front_vue_data_fields['all_services_data']) ? $bookingpress_front_vue_data_fields['all_services_data'] : array();
			
			foreach( $bookingpress_all_services_data as $bookingpress_service_data_key => $bookingpress_service_data_val ){
				$bookingpress_service_id = $bookingpress_service_data_val['bookingpress_service_id'];
                $bookingpress_max_capacity   = '';				
                if($bookingpress_service_id && !empty($bookingpress_all_services_data_with_meta) && isset($bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['max_capacity'])){
                    $bookingpress_max_capacity  = $bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['max_capacity'];					
                }else{
					$bookingpress_max_capacity = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'max_capacity');
				}				
				$bookingpress_all_services_data[ $bookingpress_service_data_key ]['service_max_capacity'] = $bookingpress_max_capacity;
			}
			/** Get Serivce Max Capacity **/

			/* Remove the disable services */

			$bpa_services_data_from_categories = !empty($bookingpress_front_vue_data_fields['bpa_services_data_from_categories']) ? $bookingpress_front_vue_data_fields['bpa_services_data_from_categories'] : array();
			$bookingpress_services_data = !empty($bookingpress_front_vue_data_fields['services_data']) ? $bookingpress_front_vue_data_fields['services_data'] : array();
			$bookingpress_service_categories = !empty($bookingpress_front_vue_data_fields['service_categories']) ? $bookingpress_front_vue_data_fields['service_categories'] : array();
			

			$bookingpress_remove_service_data = $bookingpress_remove_category_data  = array();
			$bpa_services_data_from_categories_data = $bpa_services_data_from_categories;
			
			$bpa_staff_memebers_ids_with_status = array();
			foreach($bpa_services_data_from_categories as $bpa_key => $bpa_value) {				
				if(is_array($bpa_value)) {					
					$bookingpress_category_id = '';
					foreach($bpa_value as $key => $value) {
						$bookingpress_category_id = !empty($value['bookingpress_category_id']) ? intval($value['bookingpress_category_id']) : 0; 
						$bookingpress_service_id = !empty($value['bookingpress_service_id']) ? intval($value['bookingpress_service_id']) : 0;
						$show_service_on_site = "";
						if($bookingpress_service_id && !empty($bookingpress_all_services_data_with_meta) && isset($bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['show_service_on_site'])){
							$show_service_on_site  = $bookingpress_all_services_data_with_meta[$bookingpress_service_id]['services_meta']['show_service_on_site'];					
							$show_service_on_site = ($show_service_on_site == 1 || $show_service_on_site == '1' || $show_service_on_site == 'true')?'true':'false';
						}else{
							$show_service_on_site = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'show_service_on_site');
						}																	
						if($bookingpress_is_staffmember_module_activated) {
							$bookingpress_is_staffmember_assigned = 0;
							$bookingpress_is_staffmember_data = $wpdb->get_results($wpdb->prepare( "SELECT bookingpress_staffmember_service_id,bookingpress_staffmember_id FROM ".$tbl_bookingpress_staffmembers_services." WHERE bookingpress_service_id = %d", $bookingpress_service_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm							

							foreach($bookingpress_is_staffmember_data as $keys => $vals) {
								$staffmember_id = !empty($vals['bookingpress_staffmember_id']) ? intval($vals['bookingpress_staffmember_id']) : 0;
								if(!empty($staffmember_id) && $bookingpress_is_staffmember_assigned == 0) {

									if(isset($bpa_staff_memebers_ids_with_status[$staffmember_id])){
										$bookingpress_staffmember_status = $bpa_staff_memebers_ids_with_status[$staffmember_id];
									}else{
										$bookingpress_staffmember_status = $wpdb->get_var($wpdb->prepare( "SELECT bookingpress_staffmember_status FROM ".$tbl_bookingpress_staffmembers." WHERE bookingpress_staffmember_id = %d", $staffmember_id)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $$tbl_bookingpress_staffmembers is a table name. false alarm
										$bpa_staff_memebers_ids_with_status[$staffmember_id] = $bookingpress_staffmember_status;
									}									
									if( $bookingpress_staffmember_status != '' && $bookingpress_staffmember_status == "1" ) {							
										$bookingpress_is_staffmember_assigned = 1;
									}
								}
							}	
							if($show_service_on_site == 'false' || empty($bookingpress_is_staffmember_assigned)) {
								array_push($bookingpress_remove_service_data,$bookingpress_service_id);
								unset($bpa_services_data_from_categories_data[$bpa_key][$key]);
							}
						} else {
							if($show_service_on_site == 'false') {
								array_push($bookingpress_remove_service_data,$bookingpress_service_id);
								unset($bpa_services_data_from_categories_data[$bpa_key][$key]);
							}
						}
					}					
					if(empty($bpa_services_data_from_categories_data[$bpa_key]) && !empty($bookingpress_category_id)){
						array_push($bookingpress_remove_category_data,$bookingpress_category_id);					
					}
					$bpa_services_data_from_categories_data[$bpa_key] = array_values($bpa_services_data_from_categories_data[$bpa_key]);
				}
			}	
			if(!empty($bookingpress_service_categories) && !empty($bookingpress_remove_category_data)) {
				foreach($bookingpress_service_categories as $category_key => $category_val) {					
					if(in_array($category_val['bookingpress_category_id'],$bookingpress_remove_category_data)) {
						unset($bookingpress_service_categories[$category_key]);						
					}
				}
				$bookingpress_service_categories = array_values($bookingpress_service_categories);
			}
			
			if(!empty($bookingpress_services_data)) {
				foreach($bookingpress_services_data as $service_key => $service_val) {					
					if(in_array($service_val['bookingpress_service_id'],$bookingpress_remove_service_data)) {
						unset($bookingpress_services_data[$service_key]);
					}					
				}
				$bookingpress_services_data = array_values($bookingpress_services_data);
			}
			
			if(!empty($bookingpress_all_services_data))	{
				foreach($bookingpress_all_services_data as $k => $v) {					
					if(in_array($v['bookingpress_service_id'],$bookingpress_remove_service_data)) {
						unset($bookingpress_all_services_data[$k]);
					}					
				}

			}
			
			$service_default_category = 0;
		
			foreach( $bpa_services_data_from_categories_data as $category_key => $category_data ) {							
				if(is_array($category_data)) {						
					foreach($category_data as $key => $value) {						
						if( $service_default_category == $value['bookingpress_category_id']) {
							$bookingpress_services_data = $category_data;
						}	
						break;	
					}
				}
			}
			
			$selected_service = isset($bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] : '';
			
			$bookingpress_front_vue_data_fields['services_data'] = array_values($bookingpress_services_data);
			
			$bookingpress_front_vue_data_fields['bpa_services_data_from_categories'] = $bpa_services_data_from_categories_data;
			$bookingpress_front_vue_data_fields['service_categories'] = $bookingpress_service_categories;			
			$bookingpress_front_vue_data_fields['all_services_data'] = array_values($bookingpress_all_services_data);			

			/** Remove the disable services */

			/* Staffmember module integration */
			$bookingpress_staffmember_all_details = $bookingpress_staffmember_details = array();
			if($bookingpress_is_staffmember_module_activated){

				$bookingpress_staffmember_position_field = 'bookingpress_staffmember_position';
				$bookingpress_staffmember_position_field = apply_filters( 'bookingpress_change_staffmembers_position_field', $bookingpress_staffmember_position_field );

				$bookingpress_staffmember_position_order = 'ASC';
				$bookingpress_staffmember_position_order = apply_filters( 'bookingpress_change_staffmembers_position_order', $bookingpress_staffmember_position_order );				

				$bookingpress_staffmember_details = $wpdb->get_results("SELECT * FROM ".$tbl_bookingpress_staffmembers." WHERE bookingpress_staffmember_status = 1 ORDER BY {$bookingpress_staffmember_position_field} {$bookingpress_staffmember_position_order} ", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm

				foreach($bookingpress_staffmember_details as $k => $v){
					
					$v['assigned_service_details'] = array();
					$v['assigned_service_price_details'] = array();
					$bookingpress_staffmember_id = intval($v['bookingpress_staffmember_id']);

					$bookingpress_staffmember_get_meta_keys = array('staffmember_visibility','staffmember_avatar_details');
					$bookingpress_staffmember_metadatas = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta_multiple($bookingpress_staffmember_id,$bookingpress_staffmember_get_meta_keys);
					

					$bookingpress_staffmember_visibility = (isset($bookingpress_staffmember_metadatas['staffmember_visibility']))?$bookingpress_staffmember_metadatas['staffmember_visibility']:'';

					$bookingpress_display_staff = true;
					if(empty($bookingpress_staffmember_visibility)){
						$bookingpress_staffmember_visibility = "public";
					}

					if( 'public' != $bookingpress_staffmember_visibility ){
						$bookingpress_display_staff = false;
					}

					$v['staffmember_visibility'] = $bookingpress_staffmember_visibility;

					//Get staffmember avatar details
					
					$bookingpress_staffmember_avatar_details = (isset($bookingpress_staffmember_metadatas['staffmember_avatar_details']))?$bookingpress_staffmember_metadatas['staffmember_avatar_details']:'';
					$bookingpress_staffmember_avatar_url = '';
					if(!empty($bookingpress_staffmember_avatar_details)){
						$bookingpress_staffmember_avatar_details = maybe_unserialize($bookingpress_staffmember_avatar_details);
						$bookingpress_staffmember_avatar_url = !empty($bookingpress_staffmember_avatar_details[0]['url']) ? $bookingpress_staffmember_avatar_details[0]['url'] : '';
					}

					$v['staffmember_avatar_url'] = $bookingpress_staffmember_avatar_url;

					$bookingpress_tmp_assigned_service = array();
					$bookingpress_assigned_service_details = $wpdb->get_results($wpdb->prepare( "SELECT * FROM ".$tbl_bookingpress_staffmembers_services." WHERE bookingpress_staffmember_id = %d", $bookingpress_staffmember_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm
					if(!empty($bookingpress_assigned_service_details)){
						foreach($bookingpress_assigned_service_details as $k2 => $v2){
							$bookingpress_tmp_assigned_service[] = $v2['bookingpress_service_id'];
							$v['assigned_service_price_details'][$v2['bookingpress_service_id']] = array(
								'assigned_service_id' => $v2['bookingpress_service_id'],
								'assigned_service_price' => $v2['bookingpress_service_price'],
								'assigned_service_formatted_price' => $BookingPress->bookingpress_price_formatter_with_currency_symbol($v2['bookingpress_service_price']),
								'assigned_service_capacity' => $v2['bookingpress_service_capacity'],
								'assigned_service_min_capacity' => $v2['bookingpress_service_min_capacity'],
							);
						}
					} else {
						$bookingpress_display_staff = false;
					}
					if( !empty( $selected_service ) && !in_array( $selected_service, $bookingpress_tmp_assigned_service) ){
						$bookingpress_display_staff = false;
					}
					$v['assigned_service_details'] = $bookingpress_tmp_assigned_service;

					$bookingpress_staffmember_information_setting_val = $BookingPress->bookingpress_get_customize_settings('bookingpress_staffmember_information', 'booking_form');
					$bookingpress_staffmember_information_setting_val = !empty($bookingpress_staffmember_information_setting_val) ? intval($bookingpress_staffmember_information_setting_val) : 1;
					$v['staffmember_information_rule'] = $bookingpress_staffmember_information_setting_val;

					$v['is_display_staff'] = $bookingpress_display_staff;
					$v['is_display_staff_with_flag'] = $bookingpress_display_staff;

					$bookingpress_staffmember_all_details[] = $v;
				}
			}

			$bookingpress_front_vue_data_fields['bookingpress_staffmembers_details'] = $bookingpress_staffmember_all_details;
			$bookingpress_front_vue_data_fields['bpa_all_staff_details'] = $bookingpress_staffmember_all_details;

			/** Staffmember module integration **/

			$bookingpress_front_vue_data_fields['bookingpress_open_extras_drawer'] = "false";
			$bookingpress_front_vue_data_fields['bookingpress_is_staffmember_loaded'] = "false";

			$bookingpress_front_vue_data_fields['appointment_step_form_data']['is_extra_service_exists'] = 0;
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['is_staff_exists'] = 0;

			//Mobile responsive data variables
			$bookingpress_front_vue_data_fields['service_advance_see_less'] = 0;
			$bookingpress_front_vue_data_fields['load_more_extras'] = 0;			

			$bookingpress_extras_counter = array();
			if(!empty($bookingpress_front_vue_data_fields['bookingpress_service_extras'])){
				foreach($bookingpress_front_vue_data_fields['bookingpress_service_extras'] as $k => $v){
					if(array_key_exists($v['bookingpress_service_id'], $bookingpress_extras_counter)){
						$bookingpress_extras_counter[$v['bookingpress_service_id']]['counter'] = $bookingpress_extras_counter[$v['bookingpress_service_id']]['counter'] + 1;
						$bookingpress_front_vue_data_fields['bookingpress_service_extras'][$k]['bookingpress_extra_counter'] = $bookingpress_extras_counter[$v['bookingpress_service_id']]['counter'];
					}else{
						$bookingpress_extras_counter[$v['bookingpress_service_id']] = array(
							'counter' => 1,
						);
						$bookingpress_front_vue_data_fields['bookingpress_service_extras'][$k]['bookingpress_extra_counter'] = 1;
					}
					
				}
			}

			if(!empty($bookingpress_front_vue_data_fields['all_services_data']) && $bookingpress_check_service_extra_module_activation){
				foreach($bookingpress_front_vue_data_fields['all_services_data'] as $k => $v){
					if(isset($bookingpress_extras_counter[$v['bookingpress_service_id']]['counter'])){
						$bookingpress_front_vue_data_fields['all_services_data'][$k]['extra_service_counter'] = intval($bookingpress_extras_counter[$v['bookingpress_service_id']]['counter']);						
					}else{
						$bookingpress_front_vue_data_fields['all_services_data'][$k]['extra_service_counter'] = 0;
					}
				}
			}

			$bookingpress_front_vue_data_fields['is_load_more_extras'] = '0';

			$bookingpress_all_checkbox_fields = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_type = %s AND bookingpress_is_customer_field = %d", 'checkbox', 0 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
			
			if( !empty( $bookingpress_all_checkbox_fields ) ){
				foreach( $bookingpress_all_checkbox_fields as $bpa_checkbox_field_data ){
					$bpa_checkbox_meta_key = $bpa_checkbox_field_data->bookingpress_field_meta_key;
					$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'][ $bpa_checkbox_meta_key ] = array();
				}
			}

			$bookingpress_front_vue_data_fields = apply_filters( 'bookingpress_filter_frontend_vue_data_fields', $bookingpress_front_vue_data_fields );
			
			$bookingpress_hide_service_duration = $BookingPress->bookingpress_get_customize_settings('hide_service_duration', 'booking_form');
			$bookingpress_hide_service_price = $BookingPress->bookingpress_get_customize_settings('hide_service_price', 'booking_form');
			$bookingpress_hide_time_slot_grouping = $BookingPress->bookingpress_get_customize_settings('hide_time_slot_grouping', 'booking_form');			
			$bookingpress_card_details_text = $BookingPress->bookingpress_get_customize_settings('card_details_text', 'booking_form');
			$bookingpress_card_name_text = $BookingPress->bookingpress_get_customize_settings('card_name_text', 'booking_form');
			$bookingpress_card_number_text = $BookingPress->bookingpress_get_customize_settings('card_number_text', 'booking_form');
			$bookingpress_expire_month_text = $BookingPress->bookingpress_get_customize_settings('expire_month_text', 'booking_form');
			$bookingpress_expire_year_text = $BookingPress->bookingpress_get_customize_settings('expire_year_text', 'booking_form');
			$bookingpress_cvv_text = $BookingPress->bookingpress_get_customize_settings('cvv_text', 'booking_form');
			$bookingpress_hide_capacity_text = $BookingPress->bookingpress_get_customize_settings( 'hide_capacity_text', 'booking_form');
			$bookigpress_time_format_for_booking_form =  $BookingPress->bookingpress_get_customize_settings('bookigpress_time_format_for_booking_form','booking_form');
			$bookingpress_hide_staff_selection = $BookingPress->bookingpress_get_customize_settings('hide_staffmember_selection','booking_form');
			$bookingpress_staffmember_title = $BookingPress->bookingpress_get_customize_settings('staffmember_title', 'booking_form');
			$bookingpress_form_sequence = $BookingPress->bookingpress_get_customize_settings('bookingpress_form_sequance', 'booking_form');
			$bookingpress_slot_left_text = $BookingPress->bookingpress_get_customize_settings('slot_left_text','booking_form');
			$bookingpress_cancel_button_title = $BookingPress->bookingpress_get_customize_settings('cancel_button_title','booking_form');
			$bookingpress_continue_button_title = $BookingPress->bookingpress_get_customize_settings('continue_button_title','booking_form');
			$bookingpress_subtotal_text = $BookingPress->bookingpress_get_customize_settings( 'subtotal_text', 'booking_form' );
			
			$bookingpress_hide_service_duration = !empty($bookingpress_hide_service_duration) && $bookingpress_hide_service_duration == 'true'  ? true : false;
			$bookingpress_hide_service_price = !empty($bookingpress_hide_service_price) && $bookingpress_hide_service_price == 'true'  ? true : false;
			$bookingpress_hide_time_slot_grouping = !empty($bookingpress_hide_time_slot_grouping) && $bookingpress_hide_time_slot_grouping == 'true'  ? true : false;	
			$bookingpress_card_details_text = !empty($bookingpress_card_details_text) ? stripslashes_deep( $bookingpress_card_details_text) : '';
			$bookingpress_card_name_text = !empty($bookingpress_card_name_text) ? stripslashes_deep( $bookingpress_card_name_text) : '';
			$bookingpress_card_number_text = !empty($bookingpress_card_number_text) ? stripslashes_deep( $bookingpress_card_number_text) : '';			
			$bookingpress_expire_month_text = !empty($bookingpress_expire_month_text) ? stripslashes_deep( $bookingpress_expire_month_text) : '';
			$bookingpress_expire_year_text = !empty($bookingpress_expire_year_text) ? stripslashes_deep( $bookingpress_expire_year_text) : '';
			$bookingpress_cvv_text = !empty($bookingpress_cvv_text) ? stripslashes_deep( $bookingpress_cvv_text) : '';
			$bookingpress_hide_capacity_text = !empty( $bookingpress_hide_capacity_text ) && 'true' == $bookingpress_hide_capacity_text ? true : false;
			$bookigpress_time_format_for_booking_form =  !empty($bookigpress_time_format_for_booking_form) ? $bookigpress_time_format_for_booking_form : '2';
			$bookingpress_staffmember_title = !empty($bookingpress_staffmember_title) ? stripslashes_deep($bookingpress_staffmember_title) : '';
            $bookingpress_slot_left_text = !empty($bookingpress_slot_left_text) ? stripslashes_deep($bookingpress_slot_left_text) : esc_html__('Slots left', 'bookingpress-appointment-booking');
			$bookingpress_cancel_button_title = !empty($bookingpress_cancel_button_title) ? stripslashes_deep($bookingpress_cancel_button_title) : esc_html__('Cancel', 'bookingpress-appointment-booking');
			$bookingpress_continue_button_title = !empty($bookingpress_continue_button_title) ? stripslashes_deep($bookingpress_continue_button_title) : esc_html__('Continue', 'bookingpress-appointment-booking');
			$bookingpress_subtotal_text = !empty($bookingpress_subtotal_text) ? stripslashes_deep($bookingpress_subtotal_text) : esc_html__('Subtotal', 'bookingpress-appointment-booking');

			$bookingpress_front_vue_data_fields['card_details_text'] = $bookingpress_card_details_text;
			$bookingpress_front_vue_data_fields['card_name_text'] = $bookingpress_card_name_text;
			$bookingpress_front_vue_data_fields['card_number_text'] = $bookingpress_card_number_text;
			$bookingpress_front_vue_data_fields['expire_month_text'] = $bookingpress_expire_month_text;
			$bookingpress_front_vue_data_fields['expire_year_text'] = $bookingpress_expire_year_text;
			$bookingpress_front_vue_data_fields['cvv_text'] = $bookingpress_cvv_text;
			$bookingpress_front_vue_data_fields['hide_service_duration'] = $bookingpress_hide_service_duration;
			$bookingpress_front_vue_data_fields['hide_service_price'] = $bookingpress_hide_service_price;
			$bookingpress_front_vue_data_fields['hide_time_slot_grouping'] = $bookingpress_hide_time_slot_grouping;			
			$bookingpress_front_vue_data_fields['hide_capacity_text'] = $bookingpress_hide_capacity_text;
			$bookingpress_front_vue_data_fields['hide_capacity_text_flag'] = false;
			$bookingpress_front_vue_data_fields['bookigpress_time_format_for_booking_form'] = $bookigpress_time_format_for_booking_form;			
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['hide_staff_selection'] = $bookingpress_hide_staff_selection;
			$bookingpress_front_vue_data_fields['slot_left_text'] = $bookingpress_slot_left_text;
			$bookingpress_form_sequence = json_decode($bookingpress_form_sequence, TRUE);
			if( json_last_error() != JSON_ERROR_NONE || !is_array( $bookingpress_form_sequence ) ){
				$bookingpress_form_sequence = array( 'service_selection', 'staff_selection');
			}
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_sequence'] = $bookingpress_form_sequence;
			$bookingpress_front_vue_data_fields['cancel_button_title'] = $bookingpress_cancel_button_title;
			$bookingpress_front_vue_data_fields['continue_button_title'] = $bookingpress_continue_button_title;
			$bookingpress_front_vue_data_fields['subtotal_text'] = $bookingpress_subtotal_text;		
			$bookingpress_front_vue_data_fields['is_staff_first_step'] = 0;
			
			if($bookingpress_is_staffmember_module_activated ){
				
				$bookingpress_staff_pos = array_search('staff_selection', $bookingpress_form_sequence);
				$bookingpress_service_pos = array_search('service_selection', $bookingpress_form_sequence);

				$bookingpress_staff_err_msg = $BookingPress->bookingpress_get_settings('no_staffmember_selected_for_the_booking','message_setting');
				$bookingpress_staff_err_msg = !empty($bookingpress_staff_err_msg) ? stripslashes_deep($bookingpress_staff_err_msg) : esc_html__("Please select staff member", "bookingpress-appointment-booking");

				$bookingpress_sidebar_step_data = isset($bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']) ? $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] : array();

				$bookingpress_new_sidebar_step_data = array(
					'tab_name' => $bookingpress_staffmember_title,
					'tab_value' => 'staffmembers',
					'tab_icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V18c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-1.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05.02.01.03.03.04.04 1.14.83 1.93 1.94 1.93 3.41V18c0 .35-.07.69-.18 1H22c.55 0 1-.45 1-1v-1.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
					'next_tab_name' => 'datetime',
					'previous_tab_name' => 'service',
					'validate_fields' => array(
						'selected_staff_member_id'
					),
					'validation_msg' => array(
						'selected_staff_member_id' => $bookingpress_staff_err_msg
					),
					'is_allow_navigate' => 0,
					'is_navigate_to_next' => false,
					'auto_focus_tab_callback' => array(),
					'is_display_step' => 1,
					'sorting_key' => 'staff_selection'
				);

				$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['staffmembers'] = $bookingpress_new_sidebar_step_data;

				$is_staff_visible = true;
				if( "true" == $bookingpress_hide_staff_selection ){
					$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['staffmembers']['is_display_step'] = 0;
					$is_staff_visible = false;
				}

				$is_staff_loaded_from_url = $bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_url'];
				$is_staff_loaded_front_share_url = $bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_share_url'];
				
				if( "true" == $is_staff_loaded_from_url && !empty( $bookingpress_selected_staffmember_id ) ){
					if( "true" == $is_staff_loaded_front_share_url ){
						if( isset( $_GET['allow_modify'] ) && $_GET['allow_modify'] == 1 ){
							$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['staffmembers']['is_display_step'] = 1;
							$is_staff_visible = true;
						} else {
							$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['staffmembers']['is_display_step'] = 0;
							$is_staff_visible = false;
						}
					} else {
						if( "true" == $bookingpress_hide_staff_selection ){
							$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['staffmembers']['is_display_step'] = 0;
							$is_staff_visible = false;
						} else {
							$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['staffmembers']['is_display_step'] = 1;
							$is_staff_visible = true;
						}
					}
				}

				if( false == $is_staff_visible && 'true' == $is_staff_loaded_from_url && empty( $bookingpress_selected_staffmember_id ) ){
					$bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_url'] = "false";
					$bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_share_url'] = "false";
					$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['staffmembers']['is_display_step'] = 1;
					$bookingpress_front_vue_data_fields['appointment_step_form_data']['hide_staff_selection'] = "false";
					$is_staff_visible = true;
				}

				if( false == $is_staff_visible ){
					/** change previous tab next parameter to staff member's next tab */
					if( version_compare( PHP_VERSION, '7.3.0', '<') ){
						$bookingpress_sidebar_data_keys = array_keys( $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] );
						$first_step_key = $bookingpress_sidebar_data_keys[0];
					} else {
						$first_step_key = array_key_first( $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] );
					}

					/** Check if staff member is not on the first step  */
					if( $first_step_key != 'staffmembers' ){
						$next_step_name = $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['staffmembers']['next_tab_name'];
						$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'][$first_step_key]['next_tab_name'] = $next_step_name;
					}
				}
				
			}
			
			$bookingpress_redirection_mode = $BookingPress->bookingpress_get_customize_settings('redirection_mode','booking_form');
			if(empty($bookingpress_redirection_mode)){
				$bookingpress_redirection_mode = 'external_redirection';
			}
			$bookingpress_front_vue_data_fields['appointment_step_form_data']['booking_form_redirection_mode'] = $bookingpress_redirection_mode;
			$bookingpress_thankyou_redirect_content = $BookingPress->bookingpress_get_customize_settings('bookingpress_thankyou_msg', 'booking_form');
			$bookingpress_thankyou_redirect_content = do_shortcode($bookingpress_thankyou_redirect_content, true);
			$bookingpress_front_vue_data_fields['inbuilt_redirection_thankyou_content'] = $bookingpress_thankyou_redirect_content;

			$bookingpress_failed_redirect_content = $BookingPress->bookingpress_get_customize_settings('bookingpress_failed_payment_msg', 'booking_form');
			$bookingpress_failed_redirect_content = do_shortcode($bookingpress_failed_redirect_content, true);
			$bookingpress_front_vue_data_fields['inbuilt_redirection_failed_content'] = $bookingpress_failed_redirect_content;

			$bookingpress_front_vue_data_fields['appointment_step_form_data']['is_transaction_completed'] = '';


			//If transaction retry then load all the details
			if( !empty($_REQUEST['is_success']) && $_REQUEST['is_success'] == 2 ){
				$bookingpress_last_request_uniq_id = !empty($_COOKIE['bookingpress_last_request_id']) ? intval( $_COOKIE['bookingpress_last_request_id'] ) : '';
				$bookingpress_referer_url = !empty($_COOKIE['bookingpress_referer_url']) ? esc_url_raw( $_COOKIE['bookingpress_referer_url'] ) : BOOKINGPRESS_HOME_URL;
				$bookingpress_cookie_data = !empty($_COOKIE[$bookingpress_last_request_uniq_id."_appointment_data"]) ? sanitize_text_field( base64_decode($_COOKIE[$bookingpress_last_request_uniq_id."_appointment_data"]) ) : 0; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				
				$bookingpress_cart_id = !empty($_COOKIE['bookingpress_cart_id']) ? intval( base64_decode($_COOKIE['bookingpress_cart_id']) ) : 0; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				if( (!empty($bookingpress_last_request_uniq_id) && !empty($bookingpress_referer_url) && !empty($bookingpress_cookie_data)) || (!empty($bookingpress_last_request_uniq_id) && !empty($bookingpress_referer_url) && !empty($bookingpress_cart_id)) ){
					$bookingpress_entry_id = $bookingpress_cookie_data;
					$bookingpress_entry_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d", $bookingpress_entry_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm

					if(!empty($bookingpress_entry_data)){
						$bookingpress_service_id = intval($bookingpress_entry_data['bookingpress_service_id']);
						$bookingpress_service_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_category_id FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $bookingpress_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
						$bookingpress_category_id = !empty($bookingpress_service_details['bookingpress_category_id']) ? $bookingpress_service_details['bookingpress_category_id'] : 0;

						$bookingpress_service_name = $bookingpress_entry_data['bookingpress_service_name'];
						$bookingpress_service_price = $bookingpress_entry_data['bookingpress_service_price'];
						$bookingpress_service_price_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_service_price);
						$bookingpress_service_duration_val = $bookingpress_entry_data['bookingpress_service_duration_val'];
						$bookingpress_service_duration_unit = $bookingpress_entry_data['bookingpress_service_duration_unit'];
						$bookingpress_selected_payment_gateway = $bookingpress_entry_data['bookingpress_payment_gateway'];
						$bookingpress_appointment_date = $bookingpress_entry_data['bookingpress_appointment_date'];
						$bookingpress_appointment_time = date('H:i', strtotime($bookingpress_entry_data['bookingpress_appointment_time']));
						$bookingpress_appointment_end_time = date('H:i', strtotime($bookingpress_entry_data['bookingpress_appointment_end_time']));
						$bookingpress_appointment_note = $bookingpress_entry_data['bookingpress_appointment_internal_note'];

						$bookingpress_customer_name = $bookingpress_entry_data['bookingpress_customer_name'];
						$bookingpress_customer_phone = $bookingpress_entry_data['bookingpress_customer_phone'];
						$bookingpress_customer_firstname = $bookingpress_entry_data['bookingpress_customer_firstname'];
						$bookingpress_customer_lastname = $bookingpress_entry_data['bookingpress_customer_lastname'];
						$bookingpress_customer_country = $bookingpress_entry_data['bookingpress_customer_country'];
						$bookingpress_customer_email = $bookingpress_entry_data['bookingpress_customer_email'];

						if(!empty($bookingpress_category_id)){
							$bookingpress_category_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_categories} WHERE bookingpress_category_id = %d", $bookingpress_category_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_categories is a table name. false alarm
							$bookingpress_category_name = $bookingpress_category_details['bookingpress_category_name'];

							$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_category'] = $bookingpress_category_id;
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_cat_name'] = $bookingpress_category_name;
						}

						$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] = $bookingpress_service_id;
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service_name'] = $bookingpress_service_name;
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service_price'] = $bookingpress_service_price_with_currency;
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['service_price_without_currency'] = $bookingpress_service_price;
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service_duration'] = $bookingpress_service_duration_val;
						$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service_duration_unit'] = $bookingpress_service_duration_unit;

						$bookingpress_selected_staffmember_id = $bookingpress_entry_data['bookingpress_staff_member_id'];
						if(!empty($bookingpress_selected_staffmember_id)){
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] = intval($bookingpress_selected_staffmember_id);
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_staff_member_details']['staff_member_id'] = intval($bookingpress_selected_staffmember_id);
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_staff_member_details']['select_any_staffmember'] = "false";
							$bookingpress_front_vue_data_fields['bookingpress_open_extras_drawer'] = "true";
						}

						$bookingpress_bring_members = intval($bookingpress_entry_data['bookingpress_selected_extra_members']);
						if(!empty($bookingpress_bring_members)){
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_bring_members'] = intval($bookingpress_bring_members);
							$bookingpress_front_vue_data_fields['bookingpress_open_extras_drawer'] = "true";
						}

						$bookingpress_selected_extra_services_details = !empty($bookingpress_entry_data['bookingpress_extra_service_details']) ? json_decode($bookingpress_entry_data['bookingpress_extra_service_details'], TRUE) : array();
						if(!empty($bookingpress_selected_extra_services_details)){
							$bookingpress_selected_extra_service_ids = array();
							foreach($bookingpress_selected_extra_services_details as $k => $v){
								$bookingpress_selected_extra_service_ids[$v['bookingpress_extra_service_details']['bookingpress_extra_services_id']] = array(
									'bookingpress_is_selected' => ($v['bookingpress_is_selected'] == "true") ? true : false,
									'bookingpress_selected_qty' => intval($v['bookingpress_selected_qty'])
								);
							}

							if(!empty($bookingpress_selected_extra_service_ids)){
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_extra_details'] = $bookingpress_selected_extra_service_ids;
								$bookingpress_front_vue_data_fields['bookingpress_open_extras_drawer'] = "true";
							}
						}
						
						if(!empty($bookingpress_appointment_date)){
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_date'] = date('Y-m-d', strtotime($bookingpress_appointment_date));
						}

						if(!empty($bookingpress_appointment_time) && !empty($bookingpress_appointment_end_time)){
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_start_time'] = $bookingpress_appointment_time;
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_end_time'] = $bookingpress_appointment_end_time;
						}

						// Set appointment form details
						$bookingpress_front_field_data = !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_front_field_data']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_front_field_data'] : array();
						if(!empty($bookingpress_front_field_data)){
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_name'] = $bookingpress_customer_name;
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_name'] = $bookingpress_customer_name;

							$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_firstname'] = $bookingpress_customer_firstname;
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_firstname'] = $bookingpress_customer_firstname;

							$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_lastname'] = $bookingpress_customer_lastname;
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_lastname'] = $bookingpress_customer_lastname;

							$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_email'] = $bookingpress_customer_email;
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_email'] = $bookingpress_customer_email;
							
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_phone'] = $bookingpress_customer_phone;
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone'] = $bookingpress_customer_phone;

							$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_phone_country'] = strtolower($bookingpress_customer_country);
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone_country'] = strtolower($bookingpress_customer_country);

							$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['appointment_note'] = $bookingpress_appointment_note;
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['appointment_note'] = $bookingpress_appointment_note;
						}

						$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_payment_method'] = $bookingpress_entry_data['bookingpress_payment_gateway'];

						$bookingpress_front_vue_data_fields['appointment_step_form_data']['deposit_payment_type'] = 'allow_customer_to_pay_full_amount';

						$bookingpress_coupon_details = !empty($bookingpress_entry_data['bookingpress_coupon_details']) ? json_decode($bookingpress_entry_data['bookingpress_coupon_details'], TRUE) : '';
						$bookingpress_applied_coupon_code = !empty($bookingpress_coupon_details['coupon_data']['bookingpress_coupon_code']) ? $bookingpress_coupon_details['coupon_data']['bookingpress_coupon_code'] : '';
						if(!empty($bookingpress_applied_coupon_code)){
							$bookingpress_front_vue_data_fields['appointment_step_form_data']['coupon_code'] = $bookingpress_applied_coupon_code;
						}
					}

					if(!empty($bookingpress_cart_id)){
						$bookingpress_entry_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_order_id = %d", $bookingpress_cart_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
						
						if(!empty($bookingpress_entry_data)){
							$bookingpress_appointment_note = $bookingpress_entry_data['bookingpress_appointment_internal_note'];
							$bookingpress_customer_name = $bookingpress_entry_data['bookingpress_customer_name'];
							$bookingpress_customer_phone = $bookingpress_entry_data['bookingpress_customer_phone'];
							$bookingpress_customer_firstname = $bookingpress_entry_data['bookingpress_customer_firstname'];
							$bookingpress_customer_lastname = $bookingpress_entry_data['bookingpress_customer_lastname'];
							$bookingpress_customer_country = $bookingpress_entry_data['bookingpress_customer_country'];
							$bookingpress_customer_email = $bookingpress_entry_data['bookingpress_customer_email'];

							$bookingpress_front_field_data = !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_front_field_data']) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_front_field_data'] : array();
							if(!empty($bookingpress_front_field_data)){
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_name'] = $bookingpress_customer_name;
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_name'] = $bookingpress_customer_name;

								$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_firstname'] = $bookingpress_customer_firstname;
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_firstname'] = $bookingpress_customer_firstname;

								$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_lastname'] = $bookingpress_customer_lastname;
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_lastname'] = $bookingpress_customer_lastname;

								$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_email'] = $bookingpress_customer_email;
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_email'] = $bookingpress_customer_email;
								
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_phone'] = $bookingpress_customer_phone;
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone'] = $bookingpress_customer_phone;

								$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['customer_phone_country'] = strtolower($bookingpress_customer_country);
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['customer_phone_country'] = strtolower($bookingpress_customer_country);

								$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['appointment_note'] = $bookingpress_appointment_note;
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['appointment_note'] = $bookingpress_appointment_note;
							}

							$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_payment_method'] = $bookingpress_entry_data['bookingpress_payment_gateway'];

							$bookingpress_coupon_details = !empty($bookingpress_entry_data['bookingpress_coupon_details']) ? json_decode($bookingpress_entry_data['bookingpress_coupon_details'], TRUE) : '';
							$bookingpress_applied_coupon_code = !empty($bookingpress_coupon_details['coupon_data']['bookingpress_coupon_code']) ? $bookingpress_coupon_details['coupon_data']['bookingpress_coupon_code'] : '';
							if(!empty($bookingpress_applied_coupon_code)){
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['coupon_code'] = $bookingpress_applied_coupon_code;
							}

							$bookingpress_cart_session_data = !empty($_SESSION['bookingpress_cart_'.$bookingpress_last_request_uniq_id.'_data']) ? $_SESSION['bookingpress_cart_'.$bookingpress_last_request_uniq_id.'_data'] : '';
							$bookingpress_cart_items = !empty($bookingpress_cart_session_data) ? json_decode($bookingpress_cart_session_data, TRUE) : array();

							$bookingpress_front_vue_data_fields['appointment_step_form_data']['cart_items'] = $bookingpress_cart_items;
						}
					}
				}
			}
			
			$bookingpress_front_vue_data_fields['services_data'] = array_values($bookingpress_front_vue_data_fields['services_data']);

			/** Set customer form field data */
			$bpa_form_fields = !empty( $bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'] ) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'] : array();

			

			if( is_user_logged_in() && !empty( $bpa_form_fields ) ){

				global $tbl_bookingpress_customers, $tbl_bookingpress_customers_meta;

				$current_user_id = get_current_user_id();

				$bpa_is_user_customer = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_customer_id FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id = %d ORDER BY bookingpress_customer_id DESC", $current_user_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm 

				if( !empty( $bpa_is_user_customer ) ){
					$current_user_customer_id = $bpa_is_user_customer->bookingpress_customer_id;
					$bpa_form_field_keys = array_keys( $bpa_form_fields );
					$bpa_excluded_keys = array( 'customer_name', 'customer_firstname', 'customer_lastname', 'customer_email', 'customer_phone', 'customer_phone_country', 'appointment_note');
					foreach( $bpa_form_field_keys as $bpa_field_key ){
						if( !in_array( $bpa_field_key, $bpa_excluded_keys ) ){
							$is_customer_field = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( bookingpress_form_field_id ) FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_meta_key = %s AND bookingpress_is_customer_field = %d", $bpa_field_key, 1) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm 
	
							if( 1 == $is_customer_field ){
								$meta_value = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_customersmeta_value FROM {$tbl_bookingpress_customers_meta} WHERE bookingpress_customersmeta_key = %s AND bookingpress_customer_id = %d", $bpa_field_key, $current_user_customer_id )); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customers_meta is table name defined globally. False Positive alarm 

								if( !empty( $meta_value ) ){
									$customer_meta_value = $meta_value->bookingpress_customersmeta_value;
									$is_json = json_decode( $customer_meta_value, true ) == NULL ? false : true;

									if( $is_json  ){
										$customer_meta_value = json_decode( $customer_meta_value, true );
									}
									$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'][ $bpa_field_key ] = $customer_meta_value;
								}
							}
						}
					}
				}
			}
						
			$bkp_allow_modify = isset($_REQUEST['allow_modify']) ? intval($_REQUEST['allow_modify']) : 0;
			
			$is_bookingpress_staff_loaded_from_url = $bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_url'];
			
			if($is_bookingpress_staff_loaded_from_url  == "true" && !empty($bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id']) && !empty($bookingpress_front_vue_data_fields['bookingpress_staffmembers_details']) && $bkp_allow_modify == 0 ) {
			
				$selected_staff_member_id = $bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_selected_staff_member_details']['selected_staff_member_id'];
			
				$bookingpress_staffmembers_details = $bookingpress_front_vue_data_fields['bookingpress_staffmembers_details'];

				$services_data = $bookingpress_front_vue_data_fields['services_data'];			

				foreach( $bookingpress_front_vue_data_fields['bookingpress_all_services_data'] as $bpa_service_id => $bpa_service_data ){
					
					if( !empty( $bpa_service_data['staff_member_details'] ) && !empty( $bpa_service_data['staff_member_details'][$selected_staff_member_id ] ) ){
						$bpa_new_service_price = $bpa_service_data['staff_member_details'][$selected_staff_member_id ]['bookingpress_service_price'];
						$bookingpress_front_vue_data_fields['bookingpress_all_services_data'][$bpa_service_id]['bookingpress_service_price'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $bpa_new_service_price );

						$bookingpress_front_vue_data_fields['bookingpress_all_services_data'][$bpa_service_id]['service_price_without_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol( $bpa_new_service_price, '', false);
					}
				}
				
				if( !empty( $staff_wise_services ) && !empty( $staff_wise_services[ $selected_staff_member_id ] ) ){
					$selected_staff_services = $staff_wise_services[ $selected_staff_member_id ];
					/** Deprecated loop start */
					foreach( $services_data as $skey => $sval ){
						$service_id = $sval['bookingpress_service_id'];
						if( in_array( $service_id, $selected_staff_services ) ){
							$services_data[ $skey ]['is_visible'] = true;
						} else {
							$services_data[ $skey ]['is_visible'] = false;
						}
					}
					$bookingpress_front_vue_data_fields['services_data'] = $services_data;
					/** Deprecated loop end */

					
					$all_service_data = $bookingpress_front_vue_data_fields['all_services_data'];
					foreach( $all_service_data as $askey => $asval ){
						$service_id = $asval['bookingpress_service_id'];
						if( in_array( $service_id, $selected_staff_services ) ){
							$all_service_data[ $askey ]['is_visible'] = true;
						} else {
							$all_service_data[ $askey ]['is_visible'] = false;
						}
					}
					$bookingpress_front_vue_data_fields['all_services_data'] = $all_service_data;
				}

				if( !empty( $_GET['s_id'] ) ){
					$service_step_data = $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['service'];
					if( 1 == $service_step_data['is_navigate_to_next'] && 1 == $service_step_data['is_display_step'] && empty( $_GET['allow_modify'] ) ){
						$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['service']['is_display_step'] = 0;
					}
				}
			}

			$bookingpress_front_vue_data_fields['bkp_process_booking'] = false;
			$bookingpress_front_vue_data_fields['bkp_processing_token'] = false;

			$bookingpress_front_vue_data_fields['customer_form_fields_original'] = "";
			$bookingpress_front_vue_data_fields['customer_details_rule_original'] = "";
			$bookingpress_front_vue_data_fields['customer_form_temp_data'] = "";

			$bookingpress_all_front_form_fields = $bookingpress_appointment_bookings->bookingpress_form_fields_new;
			$bookingpress_repeater_fields_key = array();
			if(!empty($bookingpress_all_front_form_fields)){
				foreach($bookingpress_all_front_form_fields as $key=>$single_field){
					$single_repeater_field_key = array();
					$field_name = (isset($single_field['field_name']))?$single_field['field_name']:'';
					if($field_name == "Repeater"){
						$layout = (isset($single_field['field_options']['layout']))?$single_field['field_options']['layout']:'';
						if($layout == '1col'){
							if(isset($single_field['field_options']['inner_fields']) && !empty($single_field['field_options']['inner_fields'])){
								foreach($single_field['field_options']['inner_fields'] as $inner_fields){
									$v_model_value = (isset($inner_fields['v_model_value']))?$inner_fields['v_model_value']:'';
									$meta_key = (isset($inner_fields['meta_key']))?$inner_fields['meta_key']:'';
									if(!empty($meta_key)){
										$single_repeater_field_key[''.$meta_key] = $v_model_value;
									}
								}
							}
						}else{
							if(isset($single_field['field_options']['inner_fields'][0]['field_options']['inner_fields']) && !empty($single_field['field_options']['inner_fields'][0]['field_options']['inner_fields'])){
								foreach($single_field['field_options']['inner_fields'][0]['field_options']['inner_fields'] as $inner_fields){									
									$meta_key = (isset($inner_fields['meta_key']))?$inner_fields['meta_key']:'';
									$v_model_value = (isset($inner_fields['v_model_value']))?$inner_fields['v_model_value']:'';
									if(!empty($meta_key)){
										$single_repeater_field_key[''.$meta_key] = $v_model_value;
									}
								}
							}
						}
						if(!empty($single_repeater_field_key)){
							$repeater_id = (isset($single_field['id']))?$single_field['id']:'';
							$repeater_id = 'repeater_'.$repeater_id;
							$bookingpress_repeater_fields_key[$repeater_id] = $single_repeater_field_key;
						}
					}
				}
			}			
			/* Modified Fields By Passing URL Start */
			
			$bookingpress_has_password_field = false;
			$bookingpress_check_password_fields = false;
			if(!empty($_GET)){
				$bookingpress_form_request_data = !empty($_GET)?array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_GET):array();  // phpcs:ignore
				$form_fields = !empty( $bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'] ) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'] : array();
				if(!empty($bookingpress_all_front_form_fields)){				
					$bookingpress_not_add_field = array('2_col','3_col','4_col','terms_and_conditions','File','Password');
					foreach($bookingpress_all_front_form_fields as $field_key=>$field_value){										
						$inner_fields = (isset($field_value['field_options']['inner_fields']))?$field_value['field_options']['inner_fields']:array();						
						$field_type = (isset($field_value['field_type']))?$field_value['field_type']:'';
						$v_model_value = (isset($field_value['v_model_value']))?$field_value['v_model_value']:'';
						$meta_key = (isset($field_value['meta_key']))?$field_value['meta_key']:'';
						$field_name = (isset($field_value['field_name']))?$field_value['field_name']:'';
						if($field_type == 'Password'){
							$bookingpress_check_password_fields = true;
						}						
						if(!empty($v_model_value) && isset($bookingpress_form_request_data[$meta_key]) && isset($form_fields[$v_model_value]) && !empty($field_type) && !in_array($field_type,$bookingpress_not_add_field)){							
							if($field_name == 'username'){
								if(!is_user_logged_in()){									
									$bookingpress_field_val = sanitize_text_field($bookingpress_form_request_data[$meta_key]);
									$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'][$v_model_value] = $bookingpress_field_val;																			
									$bookingpress_has_password_field = true;																		
								}
							}else if($field_type == 'Checkbox') {
								$bookingpress_field_val = sanitize_text_field($bookingpress_form_request_data[$meta_key]);
								if(!empty($bookingpress_field_val)){
									$bookingpress_field_val = explode(",",$bookingpress_field_val);
									$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'][$v_model_value] = $bookingpress_field_val;	
								}
							}else{
								$bookingpress_field_val = sanitize_text_field($bookingpress_form_request_data[$meta_key]);
								$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'][$v_model_value] = $bookingpress_field_val;							
								if($field_type == 'Email'){
									if(!is_user_logged_in()){
										$bookingpress_has_password_field = true;										
									}
								}								
							}
						}
						if(!empty($inner_fields)){
							foreach($inner_fields as $inner_field_key=>$inner_field_value){
								$sub_inner_fields = (isset($inner_field_value['field_options']['inner_fields']))?$inner_field_value['field_options']['inner_fields']:array();
								$field_type = (isset($inner_field_value['field_type']))?$inner_field_value['field_type']:'';
								$v_model_value = (isset($inner_field_value['v_model_value']))?$inner_field_value['v_model_value']:'';
								$meta_key = (isset($inner_field_value['meta_key']))?$inner_field_value['meta_key']:'';	
								$field_name = (isset($inner_field_value['field_name']))?$inner_field_value['field_name']:'';
								if($field_type == 'Password'){
									$bookingpress_check_password_fields = true;
								}
								if(!empty($v_model_value) && isset($bookingpress_form_request_data[$meta_key]) && isset($form_fields[$v_model_value]) && !empty($field_type) && !in_array($field_type,$bookingpress_not_add_field)){							
									if($field_name == 'username'){
										$bookingpress_update_email_username_field = true;
										if(!is_user_logged_in()){											
											$bookingpress_has_password_field = true;																						
											$bookingpress_field_val = sanitize_text_field($bookingpress_form_request_data[$meta_key]);
											$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'][$v_model_value] = $bookingpress_field_val;
											$bookingpress_update_username_field_value = $bookingpress_field_val;
										}
									}else if($field_type == 'Checkbox') {
										$bookingpress_field_val = sanitize_text_field($bookingpress_form_request_data[$meta_key]);
										if(!empty($bookingpress_field_val)){
											$bookingpress_field_val = explode(",",$bookingpress_field_val);
											$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'][$v_model_value] = $bookingpress_field_val;	
										}
									}else{										
										$bookingpress_field_val = sanitize_text_field($bookingpress_form_request_data[$meta_key]);
										$bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields'][$v_model_value] = $bookingpress_field_val;							
										if($field_type == 'Email'){
											if(!is_user_logged_in()){
												$bookingpress_has_password_field = true;
												$bookingpress_update_email_field_value = $bookingpress_field_val;
											}
										}										
									}
								}								
								
							}
						}
					}
				}
			}
			$bookingpress_front_vue_data_fields['bookingpress_has_password_field'] = ($bookingpress_check_password_fields && $bookingpress_has_password_field)?'1':'';

			/* Modified Fields By Passing URL Over */

			$bookingpress_front_vue_data_fields['appointment_step_form_data']['bookingpress_repeater_fields_key'] = $bookingpress_repeater_fields_key;

			return $bookingpress_front_vue_data_fields;
		}

		function bookingpress_share_url_category_displays( $bookingpress_modify_select_step_category ){

			$bookingpress_modify_select_step_category .= '
				
				if( vm.is_bookingpress_staff_loaded_from_url == "true" && "" != vm.appointment_step_form_data.selected_staff_member_id ){
					if( 0 == selected_cat_id  ){
						let staff_member_details = current_service.bookingpress_staffmembers;
						if( staff_member_details.includes( vm.appointment_step_form_data.selected_staff_member_id.toString() ) ){
							current_service.is_visible = true;
						} else {
							current_service.is_visible = false;
						}
					} else {
						vm.services_data.forEach( ( current_service, index) => {
							let staff_member_details = current_service.bookingpress_staffmembers;
							if( staff_member_details.includes( vm.appointment_step_form_data.selected_staff_member_id.toString() ) ){
								vm.services_data[index].is_visible = true;
							} else {
								vm.services_data[index].is_visible = false;
							}
						});
					}
				}
			';
				
			return $bookingpress_modify_select_step_category;
		}
		
		/**
		 * Change path of view file of bookingpress_form shortcode
		 *
		 * @param  mixed $bookingpress_shortcode_file_url
		 * @return void
		 */
		function bookingpress_load_booking_shortcode_view_file( $bookingpress_shortcode_file_url ) {
			$bookingpress_shortcode_file_url = BOOKINGPRESS_PRO_VIEWS_DIR . '/frontend/appointment_booking_form.php';
			
			return $bookingpress_shortcode_file_url;
		}
		
		/**
		 * Function for change timezone offset to string
		 *
		 * @param  mixed $offset
		 * @return void
		 */
		function bookingpress_convert_offset_to_string( $offset ) {

			if ( ! preg_match( '/(\d+)\:(\d+)/', $offset ) ) {
				return $offset;
			}

			list($hours, $minutes) = explode( ':', $offset );

			$seconds = $hours * 60 * 60 + $minutes * 60;

			$tz = timezone_name_from_abbr( '', $seconds, 1 );

			if ( $tz === false ) {
				$tz = timezone_name_from_abbr( '', $seconds, 0 );
			}

			return $tz;

		}

				
		/**
		 * Function for convert date time to UTC
		 *
		 * @param  mixed $date
		 * @param  mixed $time
		 * @param  mixed $formated
		 * @return void
		 */
		function bookingpress_convert_date_time_to_utc( $date, $time, $formated = false ) {
			if ( empty( $date ) ) {
				$date = date( 'm/d/Y' );
			}

			if ( empty( $time ) ) {
				$time = date( 'g:i A' );
			}
			
			$bookingpress_time = date( 'm/d/Y', strtotime( $date ) ) . ' ' . date( 'g:i A', strtotime( $time ) );

			$tz_from = wp_timezone_string();
			$tz_to   = 'UTC';
			if ( $formated ) {
				$format = 'Y-m-d\TH:i:s\Z';
			} else {
				$format = 'Ymd\THis\Z';
			}

			$start_dt = new DateTime( $bookingpress_time, new DateTimeZone( $tz_from ) );
			$start_dt->setTimeZone( new DateTimeZone( $tz_to ) );
			$bookingpress_time = $start_dt->format( $format );

			return $bookingpress_time;

		}
		
		/**
		 * Function for download ICS file
		 *
		 * @return void
		 */
		function bookingpress_download_ics_file() {

			if ( ( ( !empty( $_GET['page'] ) && 'bookingpress_download' == $_GET['page'] ) || ( !empty( $_GET['bpa_page'] ) && 'bookingpress_download' == $_GET['bpa_page'] )) && ! empty( $_GET['action'] ) && 'generate_ics' == $_GET['action'] ) {

				$nonce = ! empty( $_GET['state'] ) ? sanitize_text_field( $_GET['state'] ) : '';
				if ( ! wp_verify_nonce( $nonce, 'bookingpress_calendar_ics' ) ) {
					return false;
				}

				if ( empty( $_GET['appointment_id'] ) ) {
					return false;
				}

				$appointment_id = intval( $_GET['appointment_id'] );

				global $wpdb,$tbl_bookingpress_entries, $tbl_bookingpress_appointment_bookings, $BookingPress;
				// $appointment_id = base64_decode( $_REQUEST['appointment_id'] );
				$bookingpress_entry_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_entries} WHERE bookingpress_entry_id = %d", $appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm

				if ( ! empty( $bookingpress_entry_details ) ) {
					$bookingpress_service_id         = $bookingpress_entry_details['bookingpress_service_id'];
					$bookingpress_appointment_date   = $bookingpress_entry_details['bookingpress_appointment_date'];
					$bookingpress_appointment_time   = $bookingpress_entry_details['bookingpress_appointment_time'];
					$bookingpress_appointment_status = $bookingpress_entry_details['bookingpress_appointment_status'];

					$appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_service_id = %d AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_status = %s", $bookingpress_service_id, $bookingpress_appointment_date, $bookingpress_appointment_time, $bookingpress_appointment_status ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

					if ( ! empty( $appointment_data ) ) {
						$service_id              = intval( $appointment_data['bookingpress_service_id'] );
						$bookingpress_start_time = sanitize_text_field( $appointment_data['bookingpress_appointment_time'] );
						$bookingpress_end_time   = sanitize_text_field( $appointment_data['bookingpress_appointment_end_time'] );	
						
						$bookingpress_appointment_date_temp = $appointment_data['bookingpress_appointment_date'];
						if ($bookingpress_end_time === '24:00:00') {
							$bookingpress_appointment_date_temp = date('Y-m-d', strtotime($appointment_data['bookingpress_appointment_date'] . ' +1 day'));
							$bookingpress_end_time = '00:00:00';
						}

						$bookingpress_start_time = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_start_time ) );

						//$bookingpress_end_time = date( 'Ymd', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . 'T' . date( 'His', strtotime( $bookingpress_end_time ) );

						$bookingpress_end_time = date( 'Ymd', strtotime( $bookingpress_appointment_date_temp ) ) . 'T' . date( 'His', strtotime( $bookingpress_end_time ) );

						$user_timezone             = wp_timezone_string();
						$bookingpress_service_name = ! empty( $appointment_data['bookingpress_service_name'] ) ? sanitize_text_field( $appointment_data['bookingpress_service_name'] ) : '';
					}

					/*
					 $booking_stime = date( 'm/d/Y', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . ' ' . date('g:i A', strtotime($bookingpress_start_time) );
					$booking_etime = date( 'm/d/Y', strtotime( $appointment_data['bookingpress_appointment_date'] ) ) . ' ' . date('g:i A', strtotime($service_end_time['service_end_time']) );
					 */

					$booking_stime = $this->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date'], $bookingpress_start_time );
					//$booking_etime = $this->bookingpress_convert_date_time_to_utc( $appointment_data['bookingpress_appointment_date] ,  $bookingpress_end_time);
					$booking_etime = $this->bookingpress_convert_date_time_to_utc( $bookingpress_appointment_date_temp,  $bookingpress_end_time);
					$current_dtime = $this->bookingpress_convert_date_time_to_utc( date( 'm/d/Y' ), 'g:i A' );

					$string  = "BEGIN:VCALENDAR\n";
					$string .= "VERSION:2.0\n";
					$string .= 'PRODID:' . BOOKINGPRESS_PRO_URL . "\n";
					$string .= "X-PUBLISHED-TTL:P1W\n";
					$string .= "BEGIN:VEVENT\n";
					$string .= 'UID:' . md5( time() ) . "\n";
					$string .= 'DTSTART:' . $booking_stime . "Z\n";
					$string .= "SEQUENCE:0\n";
					$string .= "TRANSP:OPAQUE\n";
					$string .= "DTEND:{$booking_etime}Z\n";
					$string .= "SUMMARY:{$bookingpress_service_name}\n";
					$string .= "CLASS:PUBLIC\n";
					$string .= "DTSTAMP:{$current_dtime}\n";
					$string .= "END:VEVENT\n";
					$string .= "END:VCALENDAR\n";

					header( 'Content-Type: text/calendar; charset=utf-8' );
					header( 'Content-Disposition: attachment; filename="cal.ics"' );

					echo $string; //phpcs:ignore
				}

				die;

			}
		}
		
		/**
		 * Function for change view file path of my_bookings shortcode
		 *
		 * @param  mixed $bookingpress_my_appointments_file_url
		 * @return void
		 */
		function bookingpress_change_my_appointmens_shortcode_file_url_func( $bookingpress_my_appointments_file_url ) {
			$bookingpress_my_appointments_file_url = BOOKINGPRESS_PRO_VIEWS_DIR . '/frontend/appointment_my_appointments.php';
			return $bookingpress_my_appointments_file_url;
		}
			
		/**
		 * Function for send forgot password email notification
		 *
		 * @param  mixed $bookingpress_email
		 * @return void
		 */
		function bookingpress_send_forgotpassword_email($bookingpress_email){
			
			global $BookingPress,$wpdb;	
			$user_data = "";	
			if ( empty( $bookingpress_email ) ) {
				return false;
			} else if ( strpos( $bookingpress_email, '@' ) ) {
				$user_data = get_user_by( 'email', trim( $bookingpress_email ) );
				if ( empty( $user_data ) )
					return false;
			} else {
				$login = trim($bookingpress_email);
				$user_data = get_user_by('login', $login);				
				if ( !$user_data ) 			
					return false;
			}	

			do_action('lostpassword_post');
			
			// redefining user_login ensures we return the right case in the email
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;

			do_action('retreive_password', $user_login);  // Misspelled and deprecated
			do_action('retrieve_password', $user_login);
		
			$allow = apply_filters('allow_password_reset', true, $user_data->ID);

			if ( ! $allow )
				return false;
			else if ( is_wp_error($allow) )
				return false;
			
			$key = get_password_reset_key($user_data);
			
			$message = esc_html__('Someone requested that the password be reset for the following account:', 'bookingpress-appointment-booking') . "\r\n\r\n";
			$message .= network_home_url( '/' ) . "\r\n\r\n";
			/* translators: 1. Username */
			$message .= sprintf(esc_html__('Username: %s', 'bookingpress-appointment-booking'), $user_login) . "\r\n\r\n";
			$message .= esc_html__('If this was a mistake, just ignore this email and nothing will happen.', 'bookingpress-appointment-booking') . "\r\n\r\n";
			$message .= esc_html__('To reset your password, visit the following address:', 'bookingpress-appointment-booking') . "\r\n\r\n";

			$bookingpress_password_reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');

			$message .= $bookingpress_password_reset_link."\r\n";

			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);	
			/* translators: 1. Site Name */
			$title = sprintf( esc_html__('[%s] Password Reset', 'bookingpress-appointment-booking'), $blogname );	
			$title = apply_filters('retrieve_password_title', $title);
			$message = apply_filters('retrieve_password_message', $message, $key);	
			wp_mail($user_email, $title, $message);
			return true;
		}

		function bookingpress_attach_uploaded_image_to_email( $attachments, $email_template_details, $appointment_id, $template_type, $notification_name, $bookingpress_apppointment_data ){
			
			if( empty( $appointment_id ) ){
				return $attachments;
			}

			global $wpdb, $tbl_bookingpress_appointment_meta, $tbl_bookingpress_form_fields;

			$appointment_form_fields = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_appointment_id = %d", 'appointment_form_fields_data', $appointment_id ) );//phpcs:ignore

			if( empty( $appointment_form_fields ) ){
				return $attachments;
			}

			$appointment_form_fields = json_decode( $appointment_form_fields->bookingpress_appointment_meta_value, true );

			$form_fields_data = $appointment_form_fields['form_fields'];

			$file_upload_fields = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_options,bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_type = %s AND bookingpress_is_customer_field = %d", 'file', 0 ) ); //phpcs:ignore

			if( !empty( $file_upload_fields ) ){
				foreach( $file_upload_fields as $file_field_data ){
					$file_field_opts = json_decode( $file_field_data->bookingpress_field_options );

					if( !empty( $file_field_opts->attach_with_email ) && ( true == $file_field_opts->attach_with_email || 'true' == $file_field_opts->attach_with_email || 1 == $file_field_opts->attach_with_email ) ){
						$field_metakey = $file_field_data->bookingpress_field_meta_key;

						if( !empty( $form_fields_data[ $field_metakey ] ) ){
							$file_upload_url = $form_fields_data[ $field_metakey ];
							
							$file_upload_data = explode( '/', $file_upload_url );
							
							$file_upload_name = end( $file_upload_data );
							
							$file_upload_dest = BOOKINGPRESS_UPLOAD_DIR . '/' . $file_upload_name;
							$allowed_file_types = $file_field_opts->allowed_file_ext;

							$file_upload_cls = new bookingpress_fileupload_class( $file_upload_url, true );
							if( !empty( $allowed_file_types ) ){
								$allowed_file_types = explode(',', $allowed_file_types );
								$file_upload_cls->check_specific_ext = true;
								$file_upload_cls->allowed_ext = $allowed_file_types;
							}
							
							$file_upload_cls->manage_junks = true;
							if( $file_upload_cls->bookingpress_process_upload( $file_upload_dest ) ){
								
								if( file_exists( $file_upload_dest ) ){
									$attachments[] = $file_upload_dest;
								}
							}
						}
					}
				}
			}


			return $attachments;
		}

		function bookingpress_remove_uploaded_file_from_temp( $appointment_id, $entry_id = '', $payment_gateway_data = array() ){

			if( empty( $appointment_id ) ){
				return;
			}

			global $wpdb,$tbl_bookingpress_appointment_bookings, $tbl_bookingpress_appointment_meta, $tbl_bookingpress_form_fields;

			$file_upload_field = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_type = %s AND bookingpress_is_customer_field = %d", 'file', 0 ) ); //phpcs:ignore

			if( empty( $file_upload_field ) ){
				return;
			}

			$file_upload_keys = array();
			foreach( $file_upload_field as $file_upload_field_data ){
				$file_upload_keys[] = $file_upload_field_data->bookingpress_field_meta_key;
			}

			$appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ), ARRAY_A ); //phpcs:ignore 
            if( empty( $appointment_data ) ){
                return;
            }

			$appointment_form_fields = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_meta_key = %s AND bookingpress_appointment_id = %d", 'appointment_form_fields_data', $appointment_id ) ); //phpcs:ignore

			if( empty( $appointment_form_fields ) ){
				return;
			}

			$appointment_form_fields = json_decode( $appointment_form_fields->bookingpress_appointment_meta_value, true );

			$form_fields_data = $appointment_form_fields['form_fields'];

			$file_upload_paths = array();
			foreach( $file_upload_keys as $fkey ){
				if( !empty( $form_fields_data[ $fkey ] ) ){
					$file_upload_url = $form_fields_data[ $fkey ];

					$file_upload_data = explode( '/', $file_upload_url );
					$file_upload_name = end( $file_upload_data );

					$file_upload_path = BOOKINGPRESS_PRO_FORM_FILE_DIR . '/' . $file_upload_name;
					$file_upload_paths[] = $file_upload_path;
					
				}
				
			}

			if( empty( $file_upload_paths ) ){
				return;
			}

			if( !file_exists( $file_upload_path ) ){
				return;
			}

			$bpa_junk_files = get_option('bpa_remove_junk_files');
			$bpa_junk_files = json_decode( $bpa_junk_files, true );
			if( !empty( $bpa_junk_files ) && is_array( $bpa_junk_files ) ){
				foreach( $bpa_junk_files as $uploaded_img_val => $junk_file ){
					$junk_file_data = explode( '<|>', $junk_file );
					if( in_array( $junk_file_data[1], $file_upload_paths ) ){
						unset($bpa_junk_files[$uploaded_img_val]);
					}
				}

				$bpa_updated_junk_files = $bpa_junk_files;
				update_option('bpa_remove_junk_files', json_encode( $bpa_updated_junk_files ) );
			}
		}

		function bookingpress_basic_form_file_remove($return_data = false){
			$response = array();
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

			$file_name = !empty( $_REQUEST['uploaded_file_name'] ) ? sanitize_file_name( $_REQUEST['uploaded_file_name'] ) : '';
			
			if( empty( $file_name ) ){
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );				
				$response['msg'] = esc_html__( 'Sorry, File not found', 'bookingpress-appointment-booking' );
				if($return_data){
					return $response;
				}				
				echo wp_json_encode( $response );
				die;
			}

			$file_destination = BOOKINGPRESS_PRO_FORM_FILE_DIR . '/' . $file_name;

			if( file_exists( $file_destination ) ){
				@unlink( $file_destination ); //phpcs:ignore
			}

			/** Remove it from junk file */
			$bpa_junk_files = get_option('bpa_remove_junk_files');
			$bpa_junk_files = json_decode( $bpa_junk_files, true );
			if( !empty( $bpa_junk_files ) && is_array( $bpa_junk_files ) ){
				foreach( $bpa_junk_files as $uploaded_img_val => $junk_file ){
					$junk_file_data = explode( '<|>', $junk_file );
					if( $file_destination == $junk_file_data[1] ){
						unset($bpa_junk_files[$uploaded_img_val]);
					}
				}

				$bpa_updated_junk_files = $bpa_junk_files;
				update_option('bpa_remove_junk_files', json_encode( $arf_updated_junk_files ) );
			}

			$response['variant'] = 'success';
			$response['title'] = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['msg'] = esc_html__( 'File removed successfully', 'bookingpress-appointment-booking' );
			if($return_data){
				return $response;
			}			
			echo wp_json_encode( $response );
			die;
		}

		function bookingpress_basic_form_file_upload($return_upload_data = false){
			global $wpdb, $BookingPress, $bookingpress_other_debug_log_id, $tbl_bookingpress_form_fields;

			do_action( 'bookingpress_other_debug_log_entry', 'appointment_debug_logs', 'Booking data process starts', 'bookingpress_bookingform', $_REQUEST, $bookingpress_other_debug_log_id );

			$response              = array();
			$wpnonce               = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$file_key			   = isset( $_REQUEST['field_key'] ) ? sanitize_text_field( $_REQUEST['field_key'] ) :'';
			$file_upload_field_key = $file_key;
			if(isset($_REQUEST['field_key_org']) && !empty($_REQUEST['field_key_org'])){
				$file_upload_field_key = sanitize_text_field($_REQUEST['field_key_org']);
			}
			$bpa_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'bpa_file_upload_' . $file_upload_field_key );			
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				if($return_upload_data){
					return $response;
				}
				echo wp_json_encode( $response );
				die();
			}


			$file_upload_field = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_type = %s AND bookingpress_field_meta_key = %s", 'file', $file_upload_field_key ) ); //phpcs:ignore
			
			if( empty( $file_upload_field ) || empty( $_FILES[ $file_key ]) ){
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Something went wrong while processing file upload.', 'bookingpress-appointment-booking' );
				if($return_upload_data){
					return $response;
				}				
				echo wp_json_encode( $response );
				die();
			}

			$return_data = array(
                'error'            => 0,
                'msg'              => '',
                'upload_url'       => '',
                'upload_file_name' => '',
				'reference'		   => sanitize_text_field( $_POST['bpa_ref'] ) //phpcs:ignore
            );

			$field_options = json_decode($file_upload_field->bookingpress_field_options);

			$allowed_file_types = $field_options->allowed_file_ext;
			
			$file_obj = new bookingpress_fileupload_class( $_FILES[ $file_key ] ); //phpcs:ignore
			
			if( !empty( $allowed_file_types ) ){
				$allowed_file_types = explode(',', $allowed_file_types );
				$file_obj->check_specific_ext = true;
				$file_obj->allowed_ext = $allowed_file_types;
			}
			$file_obj->check_file_size = true;
			$file_obj->max_file_size = $field_options->max_file_size . 'MB';

			$file_obj->manage_junks = true;

			$file_name                = ( isset($_FILES[ $file_key ]['name']) ? current_time('timestamp') . '_' . sanitize_file_name($_FILES[ $file_key ]['name']) : '' );

			$file_name				  = sanitize_file_name( $file_name );
			
			$upload_dir               = BOOKINGPRESS_PRO_FORM_FILE_DIR . '/';
			$upload_url               = BOOKINGPRESS_PRO_FORM_FILE_URL . '/';

			$bookingpress_destination = $upload_dir . $file_name;

			if( !$file_obj->bookingpress_process_upload($bookingpress_destination) ){
				
				$return_data['error'] = 1;
                $return_data['msg']   = ! empty($file_obj->error_message) ? $file_obj->error_message : esc_html__('Something went wrong while updating the file', 'bookingpress-appointment-booking');
			} else {
				$return_data['error']            = 0;
                $return_data['msg']              = '';
                $return_data['upload_url']       = $upload_url . $file_name;
				$return_data['file_ref']		 = $file_key;
                $return_data['upload_file_name'] = $file_name;
				$return_data['upload_path']		 = $upload_dir . $file_name;
			}
			if($return_upload_data){
				return $return_data;
			}
			echo json_encode( $return_data );
			die;
		}
	}

	global $bookingpress_pro_appointment_bookings;
	$bookingpress_pro_appointment_bookings = new bookingpress_pro_appointment_bookings();
}
