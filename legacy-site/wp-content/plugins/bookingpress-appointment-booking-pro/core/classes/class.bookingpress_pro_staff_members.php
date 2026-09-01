<?php
$bookingpress_geoip_file = BOOKINGPRESS_PRO_LIBRARY_DIR . '/geoip/autoload.php';
require $bookingpress_geoip_file;
use GeoIp2\Database\Reader;

if ( ! class_exists( 'bookingpress_pro_staff_members' ) ) {
	class bookingpress_pro_staff_members Extends BookingPress_Core {
		function __construct() {
			if ( $this->bookingpress_check_staffmember_module_activation() ) {

				add_action( 'bookingpress_staff_members_dynamic_view_load', array( $this, 'bookingpress_load_staff_members_view_func' ) );
				add_action( 'bookingpress_staff_members_dynamic_vue_methods', array( $this, 'bookingpress_staff_members_vue_methods_func' ) );
				add_action( 'bookingpress_staff_members_dynamic_on_load_methods', array( $this, 'bookingpress_staff_members_on_load_methods_func' ) );
				add_action( 'bookingpress_staff_members_dynamic_data_fields', array( $this, 'bookingpress_staff_members_dynamic_data_fields_func' ) );
				add_action( 'bookingpress_staff_members_dynamic_helper_vars', array( $this, 'bookingpress_staff_members_dynamic_helper_vars_func' ) );

				add_action( 'wp_ajax_bookingpress_get_staffmembers', array( $this, 'bookingpress_get_staffmember_func' ) );
				add_action( 'wp_ajax_bookingpress_add_staff_member', array( $this, 'bookingpress_add_staff_member_func' ), 10 );
				add_action( 'wp_ajax_bookingpress_upload_staff_member_avatar', array( $this, 'bookingpress_upload_staff_member_avatar_func' ), 10 );
				add_action( 'wp_ajax_bookingpress_get_edit_staff_member', array( $this, 'bookingpress_edit_staff_member_func' ), 10 );
				add_action( 'wp_ajax_bookingpress_delete_staff_member', array( $this, 'bookingpress_delete_staff_member_func' ), 10 );

				add_action( 'wp_ajax_bookingpress_bulk_staff_member', array( $this, 'bookingpress_staff_member_bulk_action' ), 10 );

				add_action( 'wp_ajax_bookingpress_get_services_data', array( $this, 'bookingpress_get_assign_service_data' ), 10 );
				//add_action( 'wp_ajax_bookingpress_get_yearly_daysoff', array( $this, 'bookingpress_get_yearly_daysoff_func' ), 10 );
				//add_action( 'wp_ajax_bookingpress_get_staffmember_special_day_details', array( $this, 'bookingpress_get_staffmember_special_day_func' ), 10 );
				add_action( 'wp_ajax_bookingpress_get_staffmember_wpuser', array( $this, 'bookingpress_get_staffmember_wpuser_func' ), 10 );
				add_action( 'wp_ajax_bookingpress_export_staffmember_data', array( $this, 'bookingpress_export_staffmember_data_func' ), 10 );
				add_action( 'wp_ajax_bookingpress_validate_staffmember_daysoff', array( $this, 'bookingpress_validate_staffmember_daysoff_func' ), 10 );
				add_action( 'wp_ajax_bookingpress_validate_staffmember_special_day', array( $this, 'bookingpress_validate_staffmember_special_day_func' ), 10 );

				add_filter( 'bookingpress_dashboard_appointment_summary_data_filter', array( $this, 'bookingpress_dashboard_appointment_summary_data_filter_func' ) );
				add_filter( 'bookingpress_dashboard_payment_summary_data_filter', array( $this, 'bookingpress_dashboard_payment_summary_data_filter_func' ) );
				
				add_filter( 'bookingpress_modify_dashboard_data_fields', array( $this, 'bookingpress_modify_dashboard_data_fields_func' ), 10 );
				add_filter( 'bookingpress_dashboard_upcoming_appointments_data_filter', array( $this, 'bookingpress_dashboard_upcoming_appointments_data_filter_func' ) );				

				/*
				add_filter( 'bookingpress_appointment_chart_data_filter', array( $this, 'bookingpress_appointment_chart_data_filter_func' ) );
				add_filter( 'bookingpress_customer_chart_data_filter', array( $this, 'bookingpress_customer_chart_data_filter_func' ) );
				add_filter( 'bookingpress_payment_chart_data_filter', array( $this, 'bookingpress_payment_chart_data_filter_func' ) );
				*/

				add_filter( 'bookingpress_update_summary_data', array( $this, 'bookingpress_update_summary_data_func' ), 10, 3 );

				add_filter( 'bookingpress_calendar_add_view_filter', array( $this, 'bookingpress_calendar_add_view_filter_func' ), 10, 2 );
				add_filter( 'bookingpress_modify_calendar_data_fields', array( $this, 'bookingpress_modify_calendar_data_fields_func' ), 10 );

				add_filter( 'bookingpress_appointment_view_add_filter', array( $this, 'bookingpress_appointment_view_add_filter_func' ), 10, 2 );
				add_filter( 'bookingpress_modify_appointment_data_fields', array( $this, 'bookingpress_modify_appointment_data_fields_func' ), 10 );
				add_filter( 'bookingpress_appointment_add_view_field', array( $this, 'bookingpress_appointment_add_view_field_func' ), 10, 2 );
				add_filter( 'bookingpress_export_appointment_data_filter', array( $this, 'bookingpress_export_appointment_data_filter_func' ) );
				add_filter( 'bookingpress_appointment_customer_list_join_filter', array( $this, 'bookingpress_appointment_customer_list_join_filter_func' ) );
				add_filter( 'bookingpress_appointment_customer_list_filter', array( $this, 'bookingpress_appointment_customer_list_filter_func' ) );
				add_action( 'bookingpress_appointment_add_post_data', array( $this, 'bookingpress_appointment_add_post_data_func' ), 10 );

				add_filter( 'bookingpress_payment_add_filter', array( $this, 'bookingpress_payment_add_filter_func' ), 10, 2 );
				add_filter( 'bookingpress_modify_payment_data_fields', array( $this, 'bookingpress_modify_payment_data_fields_func' ), 10 );
				add_filter( 'bookingpress_payment_add_view_field', array( $this, 'bookingpress_payment_add_view_field_func' ), 10, 2 );

				add_filter( 'bookingpress_customer_view_add_filter', array( $this, 'bookingpress_customer_view_add_filter_func' ) );
				add_filter( 'bookingpress_customer_view_join_add_filter', array( $this, 'bookingpress_customer_view_join_add_filter_func' ) );
				add_filter( 'bookingpress_export_payment_data_filter', array( $this, 'bookingpress_export_payment_data_filter_func' ) );

				add_filter( 'bookingpress_customer_export_join_data_filter', array( $this, 'bookingpress_customer_export_join_data_filter_func' ) );
				add_filter( 'bookingpress_customer_export_data_filter', array( $this, 'bookingpress_customer_export_data_filter_func' ) );
				add_filter( 'bookingpress_search_customer_list_join_filter', array( $this, 'bookingpress_search_customer_list_join_filter_func' ) );
				add_filter( 'bookingpress_search_customer_list_filter', array( $this, 'bookingpress_search_customer_list_filter_func' ) );

				add_filter( 'bookingpress_add_dynamic_notification_data_fields', array( $this, 'bookingpress_add_dynamic_notification_data_fields_func' ) );
				add_filter( 'bookingpress_save_email_notification_data_filter', array( $this, 'bookingpress_save_email_notification_data_filter_func' ), 10, 2 );
				add_filter( 'bookingpress_get_notifiacation_data_filter', array( $this, 'bookingpress_get_notifiacation_data_filter_func' ) );
				add_filter( 'bookingpress_filter_admin_email_data', array( $this, 'bookingpress_filter_admin_email_data_func' ), 10, 3 );

				add_action( 'bookingpress_load_summary_dynamic_data', array( $this, 'bookingpress_load_summary_dynamic_data_func' ) );
				add_action( 'bookingpress_pro_calendar_add_post_data', array( $this, 'bookingpress_pro_calendar_add_post_data_func' ), 10 );
				add_action( 'bookingpress_add_email_notification_data', array( $this, 'bookingpress_add_email_notification_data_func' ) );
				add_action( 'bookingpress_email_notification_get_data', array( $this, 'bookingpress_email_notification_get_data_func' ) );

				add_action('wp_ajax_bookingpress_get_staffmember_workhour_data',array($this,'bookingpress_get_staffmember_workhour_data_func'));
				// hook for change staffmember status
				add_action( 'wp_ajax_bookingpress_change_staff_member', array( $this, 'bookingpress_change_staff_member_status_func' ), 10 );
				add_action( 'wp_ajax_bookingpress_format_staffmember_special_days_data', array( $this, 'bookingpress_format_staffmember_special_days_data_func' ), 10 );

				add_action('wp_ajax_bookingpress_format_staffmember_daysoff_data',array($this,'bookingpress_format_staffmember_daysoff_data_func'),10);

				add_action('bookingpress_dashboard_redirect_filter',array($this,'bookingpress_dashboard_redirect_filter_func'));

				add_filter('bookingpress_customize_add_dynamic_data_fields',array($this,'bookingpress_customize_add_dynamic_data_fields_func'),10);
				add_filter('bookingpress_get_booking_form_customize_data_filter',array($this, 'bookingpress_get_booking_form_customize_data_filter_func'),10,1);
				add_filter('bookingpress_before_save_customize_booking_form',array($this, 'bookingpress_before_save_customize_booking_form_func'),10,1);
				add_action('bookingpress_customize_dynamic_vue_methods', array( $this, 'bookingpress_dynamic_vue_methods_func' ));

				add_filter( 'bookingpress_retrieve_pro_modules_timeslots', array( $this, 'bookingpress_retrieve_staffmember_timings' ), 10, 6 );
				add_filter( 'bookingpress_retrieve_pro_modules_timeslots', array( $this, 'bookingpress_retrieve_staffmember_timings_for_anystaff'), 5, 6);
				add_filter( 'bookingpress_total_booked_appointment_where_clause', array( $this, 'bookingpress_total_booked_appointment_where_clause_function') );
				add_filter( 'bookingpress_retreive_timeslots_for_all_staffmembers', array( $this, 'bookingpress_retreive_timeslots_for_all_staffmembers_func'), 10, 7 );
				add_filter( 'bpa_calculat_anystaff_breakhours_data', array( $this, 'bookingpress_calculate_anystaff_breakhour_data'), 10, 2);

				add_filter( 'bookingpress_modified_get_settings', array( $this, 'bpa_enable_share_timeslot_for_any_staffmember_clause' ), 10, 3 );
				add_filter( 'bookingpress_set_available_staffs', array( $this, 'bookingpress_set_available_staffs_to_timeslot' ), 10, 6 );
				//add_filteR( 'bookingpress_modify_booked_appointment_data', array( $this, 'bookingpress_modify_booked_appointment_with_available_staffs'), 8, 4 );
				add_filter( 'bookingpress_dynamic_time_select_after', array( $this, 'bookingpress_assign_available_staffmember') );

				add_filter( 'bpa_calculate_staff_breakhours_data', array( $this, 'bpa_calculate_staff_breakhours_data_callback'), 10, 2);

				add_action( 'wp_ajax_bpa_assign_staffmember', array( $this, 'bpa_assign_staffmember_to_slots') );
				add_action( 'wp_ajax_nopriv_bpa_assign_staffmember', array( $this, 'bpa_assign_staffmember_to_slots') );

				add_filter( 'bookingpress_modify_bringanyone_details', array( $this, 'bookingpress_modify_bringanyone_details_func' ), 10, 2 );

				/** remove staff members data from the service category array if staff member is not set in the sidebar */
				add_filter( 'bookingpress_frontend_apointment_form_add_dynamic_data', array( $this, 'bookingpress_remove_staffmember_from_categories' ), 11,1 );

				add_filter( 'bookingpress_booked_appointment_where_clause', array( $this, 'bookingpress_booked_appointment_where_clause_staffmember'), 10, 2 );
				/** filter service wise staffmember list */
				add_filter('bookingpress_appointment_service_wise_staffmember_list',array($this,'bookingpress_appointment_service_wise_staffmember_list_func'));
				
				add_filter( 'bookingpress_disable_date_xhr_data', array( $this, 'bookingpress_set_selected_staffmember_id'),10 );

				add_action('bookingpress_add_service_validation',array($this,'bookingpress_add_service_validation_func'));

				//Format price after assign service to staffmember
				add_action('wp_ajax_bookingpress_format_assigned_service_amounts', array($this, 'bookingpress_format_assigned_service_amounts_func'));

				add_action( 'bookingpress_set_additional_appointment_xhr_data', array( $this, 'bookingpress_set_staffmember_appointment_xhr_data_func') );

				add_filter( 'bookingpress_check_available_timings_with_staffmember', array( $this, 'bookingpress_check_available_timings_with_staffmember_func' ), 10, 4 );

				add_filter( 'bookingpress_modify_disable_dates_with_staffmember', array( $this, 'bookingpress_modify_disable_dates_with_staffmember_func' ), 10, 3 );

				add_filter( 'bookingpress_disable_date_pre_xhr_data', array( $this, 'bookingpress_select_any_staffmember') );

				// return true if the staff member module is active
				add_filter( 'bookingpress_modify_form_sequence_flag', '__return_true' );

				add_action( 'bookingpress_form_sequence_list_item', array( $this, 'bookingpress_form_sequence_list_item_staffmember' ) );

				add_filter( 'bookingpress_front_booking_dynamic_on_load_methods', array( $this, 'bookingpress_change_current_tab_to_staffmember'), 11 );

				add_filter( 'bookingpress_modify_staffmember_id', array( $this, 'bookingpress_modify_staffmember_id'), 10, 2 );

				add_action('wp_ajax_bookingpress_position_staffmembers', array( $this, 'bookingpress_position_staffmembers_func' ));

				add_filter( 'bookingpress_staff_selection_visibility', '__return_true');

				add_filter( 'bookingpress_modify_form_sequence_arr', array( $this, 'bookingpress_modify_form_sequence_arr_func') );

				add_action( 'bookingpress_add_customize_booking_form_tab', array( $this, 'bookingpress_add_staffmember_step_for_customize_tab'));

				add_filter( 'bookingpress_set_staff_first_place', array( $this, 'bookingpress_check_staff_for_first_place' ), 10, 2 );

				add_filter('login_redirect',array($this,'bookingpress_after_login_redirect'),10,3);
				add_filter( 'bookingpress_modify_all_retrieved_services', array( $this, 'bookingpress_modify_services_with_staffmember'), 11, 4 );

				add_filter( 'bookingpress_modify_s_id_before_retrieving_service', array( $this, 'bookingpress_modify_s_id_before_retrieving_service_func' ), 10 );

				add_filter( 'bookingpress_check_flag_to_move_next_from_serfice', array( $this, 'bookingpress_check_flag_to_move_next_from_serfice'), 10, 2 );

				add_action( 'wp_ajax_bookingpress_retrieve_staffmember_shift_managment_data', array( $this, 'bookingpress_retrieve_staffmember_shift_managment_data_func' ), 10 );
				
				add_action( 'bookingpress_add_service_extra_section', array( $this, 'bookingpress_add_staffmember_section_for_services'), 13 );
				add_action( 'bookingpress_service_dialog_outside', array( $this, 'bookingpress_assign_staffmember_dialog') );

				add_action( 'bookingpress_add_dynamic_content_for_add_location_service_staff', array( $this, 'bookingpress_add_staffmember_selection_for_location_service') );

				add_action( 'bookingpress_location_assigned_service_dynamic_column', array( $this, 'bookingpress_location_assigned_service_staffmember_column'), 10 );

				add_action( 'bookingpress_location_assigned_service_dynamic_column_value', array( $this, 'bookingpress_location_assigned_service_staffmember_column_value'), 10);

				add_action( 'bookingpress_save_assigned_location_service_staff_for_add_location', array( $this, 'bookingpress_location_assign_staff_for_add_location' ) );

				add_action( 'bookingpress_save_assign_location_validation_outside', array( $this, 'bookingpress_location_validate_staff_add_edit_location') );

				add_action( 'bookingpress_reset_assign_service_dynamic_data_onclose', array( $this, 'bookingpress_location_reset_staff_member_model' ) );

				add_action( 'bookingpress_save_assigned_location_service_staff_for_edit_location', array( $this, 'bookingpress_location_assign_staff_for_edit_location') );


				add_action('bookingpress_add_appointment_model_reset',array($this,'bookingpress_add_appointment_model_reset_func'),11);
				
				add_action('bookingpress_change_backend_service', array($this, 'bookingpress_change_backend_service_func'));

				add_action( 'bookingpress_dashboard_add_appointment_model_reset', array($this, 'bookingpress_add_appointment_model_reset_func'),11);
				
				add_action('bookingpress_calendar_add_appointment_model_reset', array( $this, 'bookingpress_add_appointment_model_reset_func'),11);				

				add_action( 'bookingpress_calendar_filter_content', array( $this, 'bookingpress_calendar_filter_for_staffmember'), 11 );

				add_action( 'bookingpress_calendar_appointment_xhr_response', array($this, 'bookingpress_calendar_appointment_xhr_staffmember_response_func'));

				if(is_plugin_active('bookingpress-multilanguage/bookingpress-multilanguage.php')) {
					add_filter('bookingpress_modified_language_translate_fields',array($this,'bookingpress_modified_staff_language_translate_fields_func'),10);
                	add_filter('bookingpress_modified_customize_form_language_translate_fields',array($this,'bookingpress_modified_customize_form_language_translate_fields_func'),10);
					add_filter('bookingpress_modified_language_translate_fields_section',array($this,'bookingpress_modified_staff_language_translate_fields_section_func'),10);
				}

				add_action( 'bookingpress_modify_default_off_days', array( $this, 'bookingpress_staff_working_hours_daysoff' ), 5, 4 );
				add_action( 'bookingpress_modify_default_holidays', array( $this, 'bookingpress_retrieve_staff_holidays' ), 5, 4 );

				add_filter( 'bookingpress_disable_date_pre_xhr_data', array( $this, 'bookingpress_reset_staffmember_on_previous_step' ) );

			} else {
				add_filter( 'bookingpress_staff_selection_visibility', '__return_false');
			}
		}


		/**
		 * bpa function for assign staffmember
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_assign_staffmember_func($user_detail=array()){
			
			global $BookingPress,$tbl_bookingpress_services,$wpdb,$tbl_bookingpress_servicesmeta,$bookingpress_pro_staff_members,$tbl_bookingpress_categories,$BookingPressPro,$tbl_bookingpress_staffmembers,$tbl_bookingpress_staffmembers_services;	
			
			$result = array();		
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));

			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

				$slot_details = isset($user_detail['slot_details']) ? $user_detail['slot_details'] : '';
				$service_id = isset($user_detail['service_id']) ? $user_detail['service_id'] : '';
				$location_id = isset($user_detail['location_id']) ? $user_detail['location_id'] : '';
				$selected_bring_members = isset($user_detail['selected_bring_members']) ? $user_detail['selected_bring_members'] : '';
				$bookingpress_nonce = isset($user_detail['bookingpress_nonce']) ? $user_detail['bookingpress_nonce'] : '';				
				if(!empty($bookingpress_nonce)){
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}else{
					$bookingpress_nonce = wp_create_nonce('bpa_wp_nonce');
					$_REQUEST['_wpnonce'] = $bookingpress_nonce;
				}		
				$_REQUEST['slot_details'] = $slot_details;											
				if(!empty($service_id)){
					$_REQUEST['service_id'] = $service_id;
				}
				if(!empty($location_id)){
					$_REQUEST['location_id'] = $location_id;
				}
				if(!empty($selected_bring_members)){
					$_REQUEST['selected_bring_members'] = $selected_bring_members;
				}
				$_POST = $_REQUEST;
				$bookingpress_response = $this->bpa_assign_staffmember_to_slots(true);
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
		 * bpa function for get staff members list
		 *
		 * @param  mixed $user_detail
		 * @return void
		*/
		function bookingpress_bpa_get_staffmembers_func($user_detail=array()){
			
			global $BookingPress,$tbl_bookingpress_services,$wpdb,$tbl_bookingpress_servicesmeta,$bookingpress_pro_staff_members,$tbl_bookingpress_categories,$BookingPressPro,$tbl_bookingpress_staffmembers,$tbl_bookingpress_staffmembers_services;	
			
			$result = array();
			$result["total_records"] = 0;
			$result["staffmembers"] = array();			
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));

			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){

				$bookingpress_service_id = isset($user_detail['service_id']) ? intval($user_detail['service_id']) : '';
				$bookingpress_staffmember_id = isset($user_detail['staffmember_id']) ? intval($user_detail['staffmember_id']) : '';
				$bookingpress_location_id = isset($user_detail['location_id']) ? intval($user_detail['location_id']) : '';
				$perpage     = isset($user_detail['per_page']) ? intval($user_detail['per_page']) : 10;
				$currentpage = isset($user_detail['current_page']) ? intval($user_detail['current_page']) : 1;
				$offset      = (!empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;

				$where_clause = '';
				$join_query = "";

				$filter_pass_data = array('service_id'=>$bookingpress_service_id,'staffmember_id'=>$bookingpress_staffmember_id,'location_id'=>$bookingpress_location_id);

				if(!empty($bookingpress_staffmember_id) && $bookingpress_staffmember_id != 0){
					$where_clause .= $wpdb->prepare( ' AND bookingpress_staffmembers.bookingpress_staffmember_id = %d', $bookingpress_staffmember_id);
				}
				if(!empty($bookingpress_service_id) && $bookingpress_service_id != 0){
					//$where_clause .= $wpdb->prepare( ' AND bookingpress_staffmembers.bookingpress_staffmember_id = %d', $bookingpress_staffmember_id);
					$join_query .= " INNER JOIN {$tbl_bookingpress_staffmembers_services} as staffmembers_services ON staffmembers_services.bookingpress_staffmember_id =  bookingpress_staffmembers.bookingpress_staffmember_id ";
					$where_clause .= $wpdb->prepare( ' AND staffmembers_services.bookingpress_service_id = %d', $bookingpress_service_id);						
				}					
				$bookingpress_bpa_get_staff_extra_query = array('join_query'=>$join_query,'where_clause'=>$where_clause);
				$bookingpress_bpa_get_staff_extra_query = apply_filters( 'bookingpress_bpa_get_staff_where_clause', $bookingpress_bpa_get_staff_extra_query,$filter_pass_data);					
				$join_query = $bookingpress_bpa_get_staff_extra_query['join_query'];
				$where_clause = $bookingpress_bpa_get_staff_extra_query['where_clause'];

				$bookingpress_staffmember_position_field = 'bookingpress_staffmember_position';
				$bookingpress_staffmember_position_field = apply_filters( 'bookingpress_change_staffmembers_position_field', $bookingpress_staffmember_position_field );

				$bookingpress_staffmember_position_order = 'ASC';
				$bookingpress_staffmember_position_order = apply_filters( 'bookingpress_change_staffmembers_position_order', $bookingpress_staffmember_position_order );						

				$bookingpress_total_staffmembers = $wpdb->get_results("SELECT bookingpress_staffmembers.bookingpress_staffmember_id FROM ".$tbl_bookingpress_staffmembers." as bookingpress_staffmembers $join_query WHERE bookingpress_staffmember_status = 1 $where_clause GROUP BY bookingpress_staffmembers.bookingpress_staffmember_id ORDER BY {$bookingpress_staffmember_position_field} {$bookingpress_staffmember_position_order} "); // phpcs:ignore
								
				$bookingpress_get_staffmember_details = $wpdb->get_results("SELECT bookingpress_staffmembers.* FROM ".$tbl_bookingpress_staffmembers." as bookingpress_staffmembers $join_query WHERE bookingpress_staffmember_status = 1 $where_clause GROUP BY bookingpress_staffmembers.bookingpress_staffmember_id  ORDER BY {$bookingpress_staffmember_position_field} {$bookingpress_staffmember_position_order} LIMIT {$offset} , {$perpage} ", ARRAY_A); // phpcs:ignore 

				$bookingpress_staffmember_all_details = $bookingpress_staffmember_details = array();
				foreach($bookingpress_get_staffmember_details as $k => $v){					
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
					$bookingpress_assigned_service_details = $wpdb->get_results($wpdb->prepare( "SELECT bookingpress_service_id,bookingpress_service_price,bookingpress_service_capacity FROM ".$tbl_bookingpress_staffmembers_services." WHERE bookingpress_staffmember_id = %d", $bookingpress_staffmember_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm						
					if(!empty($bookingpress_assigned_service_details)){
						foreach($bookingpress_assigned_service_details as $k2 => $v2){
							$bookingpress_tmp_assigned_service[] = $v2['bookingpress_service_id'];
							$v['assigned_service_price_details'][$v2['bookingpress_service_id']] = array(
								'assigned_service_id' => $v2['bookingpress_service_id'],
								'assigned_service_price' => $v2['bookingpress_service_price'],
								'assigned_service_formatted_price' => $BookingPress->bookingpress_price_formatter_with_currency_symbol($v2['bookingpress_service_price']),
								'assigned_service_capacity' => $v2['bookingpress_service_capacity']
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
					unset($v['bookingpress_staffmember_created']);
					$bookingpress_staffmember_data = $v;
					$bookingpress_staffmember_data = apply_filters( 'bookingpress_bpa_staff_member_data', $bookingpress_staffmember_data,$filter_pass_data);
					$bookingpress_staffmember_all_details[$bookingpress_staffmember_id] = $bookingpress_staffmember_data;
				}					
				$result["total_records"] = (!empty($bookingpress_total_staffmembers))?count($bookingpress_total_staffmembers):0;
				$result["staffmembers"] = $bookingpress_staffmember_all_details;
				$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));	

			}
			
			return $response;
		}

		function bookingpress_reset_staffmember_on_previous_step( $pre_xhr_data ){

			$pre_xhr_data .= '			
			if( "datetime" != this.bookingpress_next_tab && "datetime" == this.bookingpress_current_tab && this.bookingpress_current_tab != this.bookingpress_next_tab && ( "undefined" != typeof this.appointment_step_form_data.any_staff_selected && 1 == this.appointment_step_form_data.any_staff_selected ) ){
				this.appointment_step_form_data.selected_staff_member_id = "";
				this.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = 0;				
				this.bookingpress_select_staffmember( "any_staff", 1 );
			}
			';

			return $pre_xhr_data;
		}

		function bookingpress_staff_working_hours_daysoff( $default_off_days, $selected_service, $selected_service_duration, $selected_staffmember ){

			global $wpdb, $tbl_bookingpress_staff_member_workhours;

			if( empty( $selected_staffmember ) || ( !empty( $default_off_days['skip_check'] ) && true == $default_off_days['skip_check'] ) ){

				/** First check if any staff member is selected */
				if( empty( $default_off_days['skip_check'] ) && empty( $selected_staffmember ) ){
					$appointment_data_obj = $_POST['appointment_data_obj']; //phpcs:ignore
					
					if( !empty( $appointment_data_obj['any_staff_selected'] ) && 1 == $appointment_data_obj['any_staff_selected']){
						$available_staffs = !empty( $appointment_data_obj['available_staffs'] ) ? $appointment_data_obj['available_staffs'] : array();

						if( empty( $available_staffs ) ){
							return $default_off_days;
						}

						$default_off_days['skip_check'] = true;

						$total_staffs = count( $available_staffs );

						$available_staffs_placeholder = 'AND bookingpress_staffmember_id IN (';
						$available_staffs_placeholder .= rtrim( str_repeat( '%s,', count( $available_staffs ) ), ',' );
						$available_staffs_placeholder .= ')';
						
						array_unshift( $available_staffs, $available_staffs_placeholder );

						$staff_query_where = call_user_func_array(array( $wpdb, 'prepare' ), $available_staffs );
						
						$staff_workdays = $wpdb->get_results( $wpdb->prepare( "SELECT LOWER( bookingpress_staffmember_workday_key ) AS bookingpress_staffmember_workday_key FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_workhours_is_break = %d AND ( bookingpress_staffmember_workhours_start_time IS NULL OR ( ABS( TIME_TO_SEC( TIMEDIFF( bookingpress_staffmember_workhours_start_time, ( CASE WHEN bookingpress_staffmember_workhours_end_time = '00:00:00' THEN '24:00:00' ELSE bookingpress_staffmember_workhours_end_time END ) ) ) DIV 60 ) < %d ) ) {$staff_query_where} GROUP BY {$tbl_bookingpress_staff_member_workhours}.bookingpress_staffmember_workday_key HAVING COUNT(bookingpress_staffmember_workday_key) = %d", 0, $selected_service_duration, $total_staffs ), ARRAY_A ); //phpcs:ignore
						
						if( empty( $staff_workdays ) ){
							return $default_off_days;
						}

						$total_off_days = count( $staff_workdays );
						$counter = 0;
						while( 0 < $total_off_days ){

							$default_off_days['off_days'][] = $staff_workdays[ $counter ]['bookingpress_staffmember_workday_key'];

							$total_off_days--;
							$counter++;
						}
					}
				}
				
				return $default_off_days;
			}

			//bookingpress_configure_specific_workhour
			$staff_working_hours = $this->get_bookingpress_staffmembersmeta( $selected_staffmember, 'bookingpress_configure_specific_workhour' );
			if( 'true' == $staff_working_hours ){

				$default_off_days['skip_check'] = true;

				$staff_workdays = $wpdb->get_results( $wpdb->prepare( "SELECT LOWER( bookingpress_staffmember_workday_key ) AS bookingpress_staffmember_workday_key FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_is_break = %d AND ( bookingpress_staffmember_workhours_start_time IS NULL OR ( ABS( TIME_TO_SEC( TIMEDIFF( bookingpress_staffmember_workhours_start_time, ( CASE WHEN bookingpress_staffmember_workhours_end_time = '00:00:00' THEN '24:00:00' ELSE bookingpress_staffmember_workhours_end_time END ) ) ) DIV 60 ) < %d ) ) ", $selected_staffmember, 0, $selected_service_duration ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staff_member_workhours is table name defined globally. False Positive alarm 

				if( empty( $staff_workdays ) ){
					return $default_off_days;
				}

				$total_off_days = count( $staff_workdays );
				$counter = 0;
				while( 0 < $total_off_days ){

					$default_off_days['off_days'][] = $staff_workdays[ $counter ]['bookingpress_staffmember_workday_key'];

					$total_off_days--;
					$counter++;
				}
			}
			

			return $default_off_days;
		}

		function bookingpress_retrieve_staff_holidays( $retrieve_default_holidays, $selected_service, $selected_service_duration, $selected_staffmember ){

			global $wpdb, $tbl_bookingpress_staffmembers_daysoff, $tbl_bookingpress_staffmembers_special_day, $BookingPress;

			$retrieve_daysoff = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_daysoff} WHERE bookingpress_staffmember_id = %d", $selected_staffmember ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_daysoff is table name defined globally. False Positive alarm 

			$get_period_available_for_booking = $BookingPress->bookingpress_get_settings('period_available_for_booking', 'general_setting');
			if( empty( $get_period_available_for_booking ) || !$BookingPress->bpa_is_pro_active() ){
                $get_period_available_for_booking = 365;
            }

			$bookingpress_start_date = date('Y-m-d', current_time('timestamp') );

			/** Modify get available time of booking if the service expiration time is set */
			$get_period_available_for_booking = apply_filters( 'bookingpress_modify_max_available_time_for_booking', $get_period_available_for_booking, $bookingpress_start_date, $selected_service );

			$bookingpress_end_date = date('Y-m-d', strtotime( '+' . $get_period_available_for_booking . ' days') );

			if( !empty( $retrieve_daysoff ) ){
				foreach( $retrieve_daysoff as $daysoff_details_val ){

					if( 0 == $daysoff_details_val['bookingpress_staffmember_daysoff_repeat' ] ){
						$retrieve_default_holidays['offdays'][] = array(
							'bookingpress_dayoff_date' => $daysoff_details_val['bookingpress_staffmember_daysoff_date'],
							'bookingpress_repeat' => $daysoff_details_val['bookingpress_staffmember_daysoff_repeat']
						);
					} else {
						$daysoff_start_date = date('Y-m-d', strtotime( $daysoff_details_val['bookingpress_staffmember_daysoff_date'] ) );
						if( $daysoff_start_date >= $bookingpress_end_date ){
							continue;
						}
						$daysoff_end_date = date( 'Y-m-d', strtotime( $daysoff_details_val['bookingpress_staffmember_daysoff_enddate'] ) );

						$bpa_do_frequency = !empty( $daysoff_details_val['bookingpress_staffmember_daysoff_repeat_frequency'] ) ? $daysoff_details_val['bookingpress_staffmember_daysoff_repeat_frequency'] : 1;
						$bpa_do_frequency_type = !empty( $daysoff_details_val['bookingpress_staffmember_daysoff_repeat_frequency_type'] ) ? $daysoff_details_val['bookingpress_staffmember_daysoff_repeat_frequency_type'] : 'yearly';

						if( 'week' == $bpa_do_frequency_type ){
							$bpa_do_frequency_type = 'weekly';
						} else if( 'month' == $bpa_do_frequency_type ){
							$bpa_do_frequency_type = 'monthly';
						} else if( 'day' == $bpa_do_frequency_type ){
							$bpa_do_frequency_type = 'daily';
						} else if( 'year' == $bpa_do_frequency_type ){
							$bpa_do_frequency_type = 'yearly';
						}

						$bpa_do_duration = $daysoff_details_val['bookingpress_staffmember_daysoff_repeat_duration'];

						if( 'until' == $bpa_do_duration && strtotime( $daysoff_start_date ) >= strtotime( $daysoff_details_val['bookingpress_staffmember_daysoff_repeat_date'] ) ){
							continue;
						}

						$bpa_do_repeat_obj = new BookingPress_Repeat_Holiday();
						
						$bpa_do_repeat_obj->startDate( new DateTime( $daysoff_start_date ) );
						$bpa_do_repeat_obj->freq( $bpa_do_frequency_type );

						$bpa_do_repeat_obj->interval( $bpa_do_frequency );

						if( 'forever' == $bpa_do_duration ){
							$bpa_do_repeat_obj->until( new DateTime( $bookingpress_end_date ) );
						} else if( 'no_of_times' == $bpa_do_duration ){
							$bpa_do_repeat_obj->count( $daysoff_details_val['bookingpress_staffmember_daysoff_repeat_times'] );
						} else if( 'until' == $bpa_do_duration ){
							$bpa_do_repeat_obj->until( new DateTime( $daysoff_details_val['bookingpress_staffmember_daysoff_repeat_date'] ) );
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
										$retrieve_default_holidays['offdays'][] = array(
											'bookingpress_dayoff_date' => $dt->format('Y-m-d'),
											'bookingpress_repeat' => false,
										);
									}
								} else {
									$retrieve_default_holidays['offdays'][] = array(
										'bookingpress_dayoff_date' => $off_days->format('Y-m-d'),
										'bookingpress_repeat' => false,
									);
								}
							}
						}
						
					}

					/*  */
				}
			}

			/** Exclude staff member special days if exists any */
			$retrieve_special_days = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_special_day_start_date,bookingpress_special_day_end_date,bookingpress_special_day_service_id FROM {$tbl_bookingpress_staffmembers_special_day} WHERE bookingpress_staffmember_id = %d AND TIMESTAMPDIFF( MINUTE,bookingpress_special_day_start_time,bookingpress_special_day_end_time ) >= %d", $selected_staffmember, $selected_service_duration ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_special_day is table name defined globally. False Positive alarm

			if( !empty( $retrieve_special_days ) ){
				foreach( $retrieve_special_days as $staff_sp_days ){

					/** Check if special days is associated for the one or more services.
					 * if the selected service not exists in the associated services then skip adding it in exclude array
					 */
					if( !empty( $staff_sp_days->bookingpress_special_day_service_id) ){
						if( preg_match( '/,/', $staff_sp_days->bookingpress_special_day_service_id) ){
							$staff_sp_days_services = explode( ',', $staff_sp_days->bookingpress_special_day_service_id );
							if( !in_array( $selected_service, $staff_sp_days_services ) ){
								continue;
							}
						} else if( preg_match( '/^[\d+]$/', $staff_sp_days->bookingpress_special_day_service_id ) && $staff_sp_days->bookingpress_special_day_service_id != $selected_service ) {
							continue;
						}
					}

					$staff_spday_start = date('Y-m-d', strtotime( $staff_sp_days->bookingpress_special_day_start_date ) );
					$staff_spday_end = date('Y-m-d', strtotime( $staff_sp_days->bookingpress_special_day_end_date ) );

					/** if the start and end date is same then push the date in the exclusion */
					if( $staff_spday_start == $staff_spday_end ){
						$retrieve_default_holidays['exclude_offdays'][] = date('Y-m-d H:i:s', strtotime( $staff_spday_start ) );
					} else {
						/** loop through the days and push the date into exclusion */

						$bpa_begin_date = new DateTime( $staff_spday_start );
						$bpa_end_date = new DateTime( date('Y-m-d', strtotime($staff_spday_end . '+1 day')) );
						
						$bpa_interval = DateInterval::createFromDateString('1 day');
						$period = new DatePeriod($bpa_begin_date, $bpa_interval, $bpa_end_date);

						foreach( $period as $dt ){
							$retrieve_default_holidays['exclude_offdays'][] = $dt->format( 'Y-m-d H:i:s' );
						}
					}
				}
			}

			return $retrieve_default_holidays;
		}

		/* function bookingpress_retrieve_staff_holidays( $retrieve_default_holidays, $selected_service, $selected_staffmember ){

			global $wpdb, $tbl_bookingpress_staffmembers_daysoff;

			if( empty( $selected_staffmember ) ){
				return $retrieve_default_holidays;
			}

			$retrieve_staff_holidays = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_daysoff_date, bookingpress_staffmember_daysoff_repeat FROM {$tbl_bookingpress_staffmembers_daysoff} WHERE bookingpress_staffmember_id = %d", $selected_staffmember ), ARRAY_A );

			
			if( !empty( $retrieve_staff_holidays ) ){
				//$retrieve_default_holidays = array();
				foreach( $retrieve_staff_holidays as $staff_holiday ){
					$retrieve_default_holidays[] = array(
						'bookingpress_dayoff_date' => date('Y-m-d H:i:s', strtotime( $staff_holiday['bookingpress_staffmember_daysoff_date'] ) ),
						'bookingpress_repeat' => $staff_holiday['bookingpress_staffmember_daysoff_repeat'],
					);
				}
			}

			return $retrieve_default_holidays;
		} */

		function bookingpress_modified_staff_language_translate_fields_func($bookingpress_all_language_translation_fields){
			
			$bookingpress_staff_language_translation_fields = array(                
				'customized_form_staff_member_step' => array(
					'staffmember_title' => array('field_type'=>'text','field_label'=>__('Staff step', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),
				)   
			);  
			$bokingpress_staff_member_service_step_labels = array(
				'staffmember_heading_title' => array('field_type'=>'text','field_label'=>__('Staff member title', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),
				'any_staff_title' => array('field_type'=>'text','field_label'=>__('Any staff title', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),
			);
			$bookingpress_all_language_translation_fields = array_merge($bookingpress_all_language_translation_fields,$bookingpress_staff_language_translation_fields);
			$bookingpress_all_language_translation_fields['customized_form_service_step_labels'] = array_merge($bookingpress_all_language_translation_fields['customized_form_service_step_labels'], $bokingpress_staff_member_service_step_labels);
			$bookingpress_coupon_message_translation_fields = array(                
				'no_staffmember_selected_for_the_booking' => array('field_type'=>'text','field_label'=>__('No staff member selected for the booking', 'bookingpress-appointment-booking'),'save_field_type'=>'message_setting'),
			);			
			$bookingpress_all_language_translation_fields['message_setting'] = array_merge($bookingpress_all_language_translation_fields['message_setting'], $bookingpress_coupon_message_translation_fields);
			return $bookingpress_all_language_translation_fields;
		}

		function bookingpress_modified_customize_form_language_translate_fields_func($bookingpress_all_language_translation_fields){
			$bookingpress_staff_language_translation_fields = array(                
				'customized_form_staff_member_step' => array(
					'staffmember_title' => array('field_type'=>'text','field_label'=>__('Staff step', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),
				)   
			);  
			$bokingpress_staff_member_service_step_labels = array(
				'staffmember_heading_title' => array('field_type'=>'text','field_label'=>__('Staff member title', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),
				'any_staff_title' => array('field_type'=>'text','field_label'=>__('Any staff title', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),
			);
			$pos = 5;
			$bookingpress_all_language_translation_fields = array_slice($bookingpress_all_language_translation_fields, 0, $pos)+$bookingpress_staff_language_translation_fields + array_slice($bookingpress_all_language_translation_fields, $pos);
			$bookingpress_all_language_translation_fields['customized_form_service_step_labels'] = array_merge($bookingpress_all_language_translation_fields['customized_form_service_step_labels'], $bokingpress_staff_member_service_step_labels);
			return $bookingpress_all_language_translation_fields;
		}

		function bookingpress_modified_staff_language_translate_fields_section_func($bookingpress_all_language_translation_fields_section){
			/* Function to add staff member step heading */
            $bookingpress_staff_step_section_added = array('customized_form_staff_member_step' => __('Staff step labels', 'bookingpress-appointment-booking') );
			$bookingpress_all_language_translation_fields_section = array_merge($bookingpress_all_language_translation_fields_section,$bookingpress_staff_step_section_added);
			return $bookingpress_all_language_translation_fields_section;
		}  

		function bookingpress_calendar_appointment_xhr_staffmember_response_func()
		{ 	?>
			vm.bpa_display_calendar_loader = 0;
            <?php
		}

		function bookingpress_calendar_filter_for_staffmember(){
			global $bookingpress_global_options, $BookingPressPro;
			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
			$bookingpress_plural_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) : esc_html_e('Staff Members', 'bookingpress-appointment-booking');
			if (! $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' )) {
			?>
				<div class="bpa-afb__staff-filter" v-if="is_staffmember_activated == 1 && current_screen_size != 'mobile'">
					<h4><?php echo esc_html($bookingpress_plural_staffmember_name); ?></h4>
					<div class="bpa-sf-items-wrapper" id="bpa-sf-items-wrapper">
						<div class="bpa-sf-item bpa-sf-item__is-all-staff __bpa-is-active" @click="bookingpress_calendar_staffmember_filter($event, 'all')">
							<div class="bpa-all-staff__thumb">
								<span class="material-icons-round">assignment_ind</span>
							</div>
							<h4><?php esc_html_e('All Staff', 'bookingpress-appointment-booking'); ?></h4>
						</div>
						<div v-for="item in search_staff_member_list" :key="item.value" :value="item.value" @click="bookingpress_calendar_staffmember_filter($event, item.value)" class="bpa-sf-item bpa-sf-item-data">
							<img :src="item.avatar" alt="">
							<h4>{{ item.text }}</h4>
						</div>
					</div>
					<div class="bpa-afb_staff-filter-nav-arrows">
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--icon-without-box" @click="bpa_move_staff_nav_prev()">
							<span class="material-icons-round">arrow_back_ios</span>
						</el-button>
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--icon-without-box" @click="bpa_move_staff_nav_next()">
							<span class="material-icons-round">arrow_forward_ios</span>
						</el-button>
					</div>
				</div>
				<div class="bpa-afb__staff-filter_mobile" v-else-if="is_staffmember_activated == 1">
					<el-select v-model="search_data_staff_id" class="bpa-form-control" filterable collapse-tags placeholder="<?php esc_html_e('Staff', 'bookingpress-appointment-booking'); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar" @change="bookingpress_calendar_staffmember_filter_change($event)">		
							<!--<el-option value="">< ?php esc_html_e('Staff', 'bookingpress-appointment-booking'); ?></el-option> -->
							<el-option v-for="item in search_staff_member_list" :label="item.text" :value="item.value"></el-option>
						</el-select>
				</div>
			<?php }
		}

		/**
		 * Code execute when backend service change
		 *
		 * @return void
		 */
		function bookingpress_change_backend_service_func(){
			?>
					if(typeof vm.appointment_formdata.selected_staffmember != "undefined"){    
						vm.appointment_formdata.selected_staffmember = '';
					}				
		<?php
		}

		function bookingpress_add_appointment_model_reset_func() {
		?>
			if(typeof vm2.appointment_formdata.selected_staffmember != "undefined"){
				vm2.appointment_formdata.selected_staffmember = '';
			}
		<?php
		}
	

		function bookingpress_assign_staffmember_dialog(){
			global $bookingpress_global_options;
			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
			$bookingpress_plural_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) : esc_html_e('Staff Members', 'bookingpress-appointment-booking');
			?>
			<el-dialog id="assign_staffmember_modal" :custom-class="typeof service.enable_custom_service_duration !== 'undefined' ? 'bpa-dialog bpa-dailog__small bpa-dialog--add-assign-staff bpa-dialog__is-custom-duration-addon-activated' :'bpa-dialog bpa-dailog__small bpa-dialog--add-assign-staff'" title="" :visible.sync="open_assign_staff_member_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc" > 
				<div class="bpa-dialog-heading">
					<el-row type="flex">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<h1 class="bpa-page-heading"><?php esc_html_e('Assign', 'bookingpress-appointment-booking'); ?> <?php echo esc_html($bookingpress_singular_staffmember_name); ?></h1>
						</el-col>
					</el-row>
				</div>
				<div class="bpa-dialog-body">
					<el-container class="bpa-grid-list-container bpa-add-categpry-container">
						<div class="bpa-form-row">
							<el-row>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form ref="assign_staff_member_form" label-position="top" @submit.native.prevent>
										<div class="bpa-form-body-row">
											<el-row>
												<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
													<el-form-item>
														<template #label>
															<span class="bpa-form-label"><?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?> <?php echo esc_html($bookingpress_singular_staffmember_name); ?></span>
														</template>
														<el-select v-model="assign_staff_member_details.assigned_staffmember_id" class="bpa-form-control" @change="bookingpress_set_staffmember_name($event)">
															<el-option :key="assign_staffmembers_data.staffmember_name" :label="assign_staffmembers_data.staffmember_name" :value="assign_staffmembers_data.staffmember_id" :data-staffmember_name="assign_staffmembers_data.staffmember_name" v-for="assign_staffmembers_data in assign_staffmembers"></el-option>
														</el-select>
													</el-form-item>
												</el-col>
												<?php
													do_action('bookingpress_add_dynamic_content_for_add_staff');
												?>
												<el-col v-if="is_bring_anyone_with_you_activated == 1" :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
													<el-form-item>
														<template #label>
															<span class="bpa-form-label"><?php esc_html_e( 'Min Capacity', 'bookingpress-appointment-booking' ); ?></span>
														</template>
														<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" :max="999" v-model="assign_staff_member_details.assigned_staffmember_min_capacity" step-strictly></el-input-number>
													</el-form-item> 
												</el-col>
												<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
													<el-form-item>
														<template #label>
															<span class="bpa-form-label"><?php esc_html_e( 'Max Capacity', 'bookingpress-appointment-booking' ); ?></span>
														</template>
														<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" :max="999" v-model="assign_staff_member_details.assigned_staffmember_max_capacity" step-strictly></el-input-number>
													</el-form-item> 
												</el-col>
												<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="typeof service.enable_custom_service_duration == 'undefined' || service.enable_custom_service_duration == false"> 
													<el-form-item>
														<template #label>
															<span class="bpa-form-label"><?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?>({{service_price_currency}})</span>
														</template>
														<el-input  v-model="assign_staff_member_details.assigned_staffmember_price" class="bpa-form-control" placeholder="0.00" ></el-input>
													</el-form-item> 
												</el-col>
												<?php
												do_action('bookingpress_add_custom_service_duration_field');
												?>							
											</el-row>
										</div>
									</el-form>
								</el-col>
							</el-row>
						</div>
					</el-container>
				</div>
				<div class="bpa-dialog-footer">
					<div class="bpa-hw-right-btn-group">
						<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="bookingpress_save_assign_staffmember_data()"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
						<el-button class="bpa-btn bpa-btn__small" @click="bookingpress_close_assign_staffmember_modal()"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
					</div>
				</div>
			</el-dialog>
			<?php
		}

		function bookingpress_location_reset_staff_member_model(){
			?>
				vm.assign_location_service_form.assign_service_staffmember = "";
			<?php
		}

		function bookingpress_location_validate_staff_add_edit_location(){
			global $bookingpress_global_options, $BookingPress, $bookingpress_notification_duration;

			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			
			$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? $bookingpress_global_options_arr['bookingpress_staffmember_singular_name'] : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
			?>
				if( "" == service_form.assign_service_staffmember || 0 == service_form.assign_service_staffmember ){
					vm.$notify({
						title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
						message: '<?php echo esc_html__('Please select', 'bookingpress-appointment-booking') . ' ' . esc_html($bookingpress_singular_staffmember_name); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
					return false;
				}

				let selected_staffmember_id = service_form.assign_service_staffmember;
				let selected_service_id = service_form.assign_service_id
				check_for_multiple_service = false;
				let already_added_staff_error = 0;
				
				if( 0 < vm.location_assigned_services.length ){
					for( let index in vm.location_assigned_services ){
						let element = vm.location_assigned_services[ index ];
						if( false == service_form.is_edit_location_service ){
							if( element.service_id == selected_service_id && element.staffmember_id == selected_staffmember_id ){
								already_added_staff_error++
								break;
							}
						} else {
							if( element.service_staff_location_id != service_form.edit_location_service_id && element.service_id == selected_service_id && element.staffmember_id == selected_staffmember_id ){
								already_added_staff_error++;
								break;
							}
						}
					}

					if( 0 < already_added_staff_error ){
						vm.$notify({
							title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
							message: '<?php echo esc_html__('Staff member already added to the location with same service', 'bookingpress-appointment-booking'); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
						return false;
					}
				}

				if( "true" != vm.staff_to_multiple_locations ){
					let location_id = vm.location.location_update_id;
					const d = await vm.bookingpress_check_staff_with_location( selected_staffmember_id, selected_service_id, location_id );
					if( false == d ){
						vm.$notify({
							title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
							message: '<?php echo esc_html__('Staff member already added to the different location', 'bookingpress-appointment-booking'); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
						return false;
					}
				}

			<?php
		}

		function bookingpress_location_assign_staff_for_add_location(){
			?>
				let assigned_staffmember = service_form.assign_service_staffmember;
				let assigned_staff_details = service_form.assigned_staffmember_list[ assigned_staffmember ];
				new_assigned_service.staffmember_id = assigned_staffmember;
				new_assigned_service.staffmember_name = assigned_staff_details.staffmember_name;
			<?php
		}

		function bookingpress_location_assign_staff_for_edit_location(){
			?>

			let assigned_staffmember = service_form.assign_service_staffmember;
			let assigned_staff_details = service_form.assigned_staffmember_list[ assigned_staffmember ];

			vm.location_assigned_services[ edit_location_index ].staffmember_id = assigned_staffmember;
			vm.location_assigned_services[ edit_location_index ].staffmember_name = assigned_staff_details.staffmember_name;

			vm.location_assigned_services[ edit_location_index ].staffmember_data = [{
				"staffmember_id" : assigned_staffmember,
				"staffmember_name": assigned_staff_details.staffmember_name
			}];

			<?php
		}

		function bookingpress_location_assigned_service_staffmember_column(){
			?>
				<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07">
					<div class="bpa-card__item">
						<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Staff Member', 'bookingpress-appointment-booking' ); ?></h4>
					</div>
				</el-col>
			<?php
		}

		function bookingpress_location_assigned_service_staffmember_column_value(){
			global $bookingpress_global_options;
			
			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			
			$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');

			$bookingpress_plural_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) : esc_html_e('Staff Members', 'bookingpress-appointment-booking');
			?>
				<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-if="assigned_service_details.multiple_staffs == true">
					<div class="bpa-card__item">
						<h4 class="bpa-card__item__heading is--body-heading" v-for="(staff_data,index) in assigned_service_details.staffmember_data" v-if="index < 2">
							{{staff_data.staffmember_name}}
							<el-popover placement="top-start" title="<?php echo esc_html($bookingpress_plural_staffmember_name); ?>" width="280" trigger="hover" popper-class="bpa-card-item-extra-popover" v-if="1 == index && 2 < assigned_service_details.staff_counter && 0 < assigned_service_details.staff_extra_counter" style="display:inline-block;width: auto">
								<div class="bpa-card-item-extra-content">
									<div class="bpa-cec__item" v-for="(inner_staff_data,inner_index) in assigned_service_details.staffmember_data" v-if="inner_index > 1">{{ inner_staff_data.staffmember_name }}</div>
								</div>
								<div slot="reference" class="bpa-card__item-extra-tooltip">
									<el-link class="bpa-iet__label">{{ assigned_service_details.staff_extra_counter }}</el-link>
								</div>
							</el-popover>
						</h4>
					</div>
				</el-col>
				<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-else>
					<div class="bpa-card__item">
						<h4 class="bpa-card__item__heading is--body-heading">{{ assigned_service_details.staffmember_name }}</h4>
					</div>
				</el-col>
			<?php
		}

		function bookingpress_add_staffmember_selection_for_location_service(){
			global $bookingpress_global_options, $BookingPress;

			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			
			$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
			
			$bookingpress_plural_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) : esc_html_e('Staff Members', 'bookingpress-appointment-booking');

			$bookingpress_allow_staff_to_service_multiple_location = $BookingPress->bookingpress_get_settings('allow_staffmember_to_serve_multiple_locations', 'general_setting');

			?>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<el-form-item>
						<template #label>
							<span class="bpa-form-label"><?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?> <?php echo ( $bookingpress_allow_staff_to_service_multiple_location == 'true' ) ? esc_html($bookingpress_plural_staffmember_name) : esc_html($bookingpress_singular_staffmember_name); //phpcs:ignore ?></span>
						</template>
						<el-select class="bpa-form-control" v-model="assign_location_service_form.assign_service_staffmember" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?> <?php echo ($bookingpress_allow_staff_to_service_multiple_location == 'true') ? esc_html($bookingpress_plural_staffmember_name) : esc_html($bookingpress_singular_staffmember_name); //phpcs:ignore ?>">
							<el-option :data-type="typeof staffmember_details.staffmember_id" :label="staffmember_details.staffmember_name" :value="staffmember_details.staffmember_id" v-for="(staffmember_details, key) in assign_location_service_form.assigned_staffmember_list" v-if="0 < staffmember_details.staffmember_id"></el-option>
						</el-select>
					</el-form-item>
				</el-col>
			<?php
		}

		function bookingpress_add_staffmember_section_for_services(){
			global $bookingpress_ajaxurl, $bookingpress_common_date_format, $bookingpress_global_options;
			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
			$bookingpress_plural_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) : esc_html_e('Staff Members', 'bookingpress-appointment-booking');

			?>
				<?php // Assign Staff Members section ?>
				<?php // ----------------------------------------------------------------- ?>
				<div class="bpa-form-row">
					<el-row>
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-db-sec-heading">
								<el-row type="flex" align="middle">
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<div class="bpa-db-sec-left">
											<h2 class="bpa-page-heading"><?php echo esc_html( $bookingpress_plural_staffmember_name ); ?></h2>
										</div>
									</el-col>
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<div class="bpa-hw-right-btn-group">
											<el-button class="bpa-btn bpa-btn__filled-light" @click="bookingpress_open_assign_staffmember_modal(event)">
												<span class="material-icons-round">add</span>
												<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
											</el-button>
										</div>
									</el-col>
								</el-row>
							</div>
							<div class="bpa-default-card bpa-db-card bpa-grid-list-container bpa-dc__staff--assigned-service">
								<el-row class="bpa-dc--sec-sub-head" v-if="total_assigned_staff_members != 0">
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<h2 class="bpa-sec--sub-heading">{{ total_assigned_staff_members }} <?php echo esc_html( $bookingpress_plural_staffmember_name ); ?></h2>
									</el-col>
								</el-row>
								<div class="bpa-as__body">
									<?php
										do_action('bookingpress_add_dynamic_section_before_staffmember_list');
									?>
									<el-row class="bpa-as__empty-view" type="flex" v-if="total_assigned_staff_members == 0">
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
											<div class="bpa-data-empty-view">
												<div class="bpa-ev-left-vector">
													<picture>
														<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
														<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
													</picture>
												</div>
												<div class="bpa-ev-right-content">
													<h4><?php esc_html_e( 'No', 'bookingpress-appointment-booking' ); ?> <?php echo esc_html($bookingpress_plural_staffmember_name); ?> <?php esc_html_e('Assigned', 'bookingpress-appointment-booking'); ?></h4>
												</div>				
											</div>
										</el-col>
									</el-row>
									<el-row v-else>
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
											<div class="bpa-card bpa-card__heading-row">
												<el-row type="flex">
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading"><?php echo esc_html($bookingpress_singular_staffmember_name); ?> <?php esc_html_e( 'Name', 'bookingpress-appointment-booking' ); ?></h4>
														</div>    
													</el-col>
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-if="typeof service.enable_custom_service_duration !== 'undefined' &&  service.enable_custom_service_duration == true ">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading"><?php echo esc_html($bookingpress_singular_staffmember_name); ?> <?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?></h4>
														</div>
													</el-col>
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading"><?php echo esc_html($bookingpress_singular_staffmember_name); ?> <?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?></h4>
														</div>
													</el-col>
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-if="is_bring_anyone_with_you_activated == 1">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading"><?php echo esc_html($bookingpress_singular_staffmember_name); ?> <?php esc_html_e( 'Min Capacity', 'bookingpress-appointment-booking' ); ?></h4>
														</div>
													</el-col>
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading"><?php echo esc_html($bookingpress_singular_staffmember_name); ?> <?php esc_html_e( 'Max Capacity', 'bookingpress-appointment-booking' ); ?></h4>
														</div>
													</el-col>
													<?php do_action('bookingpress_service_staff_extra_column_outside'); ?>
													<el-col :xs="03" :sm="03" :md="03" :lg="03" :xl="03">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Action', 'bookingpress-appointment-booking' ); ?></h4>
														</div>
													</el-col>
												</el-row>
											</div>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="(assign_staffmember_data, index) in assign_staff_member_list" v-show="( assign_staffmember_data.staff_display_row == true || assign_staffmember_data.staff_display_row == 'true')">
											<div class="bpa-card bpa-card__body-row list-group-item">
												<el-row type="flex">
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading is--body-heading">{{ assign_staffmember_data.staffmember_name }}</h4>
														</div>
													</el-col>
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-if="assign_staffmember_data.staffmember_custom_service != 'undefined' && assign_staffmember_data.staffmember_custom_service != '' && assign_staffmember_data.staffmember_custom_service != null && service.enable_custom_service_duration == true ">				
														<div class="bpa-card__item" v-for="(custom_service_duration_data, index) in assign_staffmember_data.staffmember_custom_service">
															<h4 class="bpa-card__item__heading is--body-heading">{{custom_service_duration_data.service_duration_text}}</h4>
														</div>
													</el-col>
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-else-if="typeof service.enable_custom_service_duration !== 'undefined' && service.enable_custom_service_duration == true && assign_staffmember_data.staff_duration_text != 'undefined'">				
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading is--body-heading"> {{assign_staffmember_data.staff_duration_text}} </h4>
														</div>
													</el-col>
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-if="assign_staffmember_data.staffmember_custom_service != 'undefined' && assign_staffmember_data.staffmember_custom_service != '' && assign_staffmember_data.staffmember_custom_service != null && service.enable_custom_service_duration == true">			
														<div class="bpa-card__item" v-for="(custom_service_duration_data, index) in assign_staffmember_data.staffmember_custom_service">
															<h4 class="bpa-card__item__heading is--body-heading">{{custom_service_duration_data.service_formatted_price}}</h4>
														</div>
													</el-col>
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-else>
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading is--body-heading">{{ assign_staffmember_data.staffmember_price_with_currency }}</h4>
														</div>
													</el-col>
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-if="is_bring_anyone_with_you_activated == 1">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading is--body-heading">{{ assign_staffmember_data.staffmember_min_capacity }}</h4>
														</div>
													</el-col>
													<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading is--body-heading">{{ assign_staffmember_data.staffmember_max_capacity }}</h4>
														</div>
													</el-col>
													<?php do_action('bookingpress_service_staff_extra_column_value_outside'); ?>
													<el-col :xs="03" :sm="03" :md="03" :lg="03" :xl="03">
														<div class="bpa-card__item">
															<el-button class="bpa-btn bpa-btn--icon-without-box" @click="bookingpress_edit_assigned_staffmember(index, event)">
																<span class="material-icons-round">mode_edit</span>
															</el-button>
																
															<el-button class="bpa-btn bpa-btn--icon-without-box __danger" @click="bookingpress_delete_assigned_staffmember(index)">
																<span class="material-icons-round">delete</span>
															</el-button>
														</div>
													</el-col>
												</el-row>
											</div>
										</el-col>
									</el-row>
								</div>
							</div>
						</el-col>
					</el-row>
				</div>
			<?php
		}

		function bookingpress_retrieve_staffmember_shift_managment_data_func() {
			global $wpdb, $tbl_bookingpress_staffmembers,$tbl_bookingpress_staff_member_workhours, $tbl_bookingpress_staffmembers_daysoff,$BookingPressPro,$bookingpress_global_options,$tbl_bookingpress_staffmembers_special_day,$bookingpress_settings,$tbl_bookingpress_staffmembers_special_day_breaks;

			$response                    = array();			
			$bpa_check_authorization = $this->bpa_check_authentication( 'get_staffmember_shift_managment', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');
				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			$response['workhours'] 	   = array();
			$response['workhour_data'] = array();
			$response['daysoff_data'] = array();
			$response['special_day_data'] = array();
			$response['disabled_special_day_data'] = array();
			$response['data']                = array();
			$response['selected_workhours']  = array();
			$response['default_break_times'] = array();

			$bookingpress_staffmember_id = !empty($_POST['staffmember_id']) ? intval($_POST['staffmember_id']) : 0; // phpcs:ignore
			$is_configure_specific_workhour = ! empty( $_REQUEST['is_configure_specific_workhour'] ) ? sanitize_text_field( $_REQUEST['is_configure_specific_workhour']) : '';
			$bookingpress_options  = $bookingpress_global_options->bookingpress_global_options();			
			if ( ! empty( $bookingpress_staffmember_id ) ) { // phpcs:ignore

				// Get workhours details								
				$bookingpress_staff_member_workhours = $bookingpress_workhours_data = array();

				if($is_configure_specific_workhour == 'true') {

					
						$where_clause = $wpdb->prepare( 'bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_is_break = 0', $bookingpress_staffmember_id );
						$where_clause = apply_filters('bookingpress_modify_get_staff_workhour_where_clause', $where_clause, $_POST, $bookingpress_staffmember_id); // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
						$bookingpress_staff_member_workhours_details = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_staff_member_workhours} WHERE $where_clause",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is a table name. false alarm
											
						if ( ! empty( $bookingpress_staff_member_workhours_details ) ) {
							foreach ( $bookingpress_staff_member_workhours_details as $bookingpress_staff_member_workhour_key => $bookingpress_staff_member_workhour_val ) {
								$selected_start_time = $bookingpress_staff_member_workhour_val['bookingpress_staffmember_workhours_start_time'];
								$selected_end_time   = $bookingpress_staff_member_workhour_val['bookingpress_staffmember_workhours_end_time'];
								if ( $selected_start_time == null ) {
									$selected_start_time = 'Off';
								}
								if ( $selected_end_time == null ) {
									$selected_end_time = 'Off';
								}
								$bookingpress_staff_member_workhours[ $bookingpress_staff_member_workhour_val['bookingpress_staffmember_workday_key'] ] = array(
									'start_time' => $selected_start_time,
									'end_time'   => $selected_end_time,
								);
							}
							$bookingpress_break_time_details = array();
							$bookingpress_days_arr = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
							foreach ( $bookingpress_days_arr as $days_key => $days_val ) {
								$bookingpress_breaks_arr = array();
								$staff_break_where_clause = $wpdb->prepare( 'bookingpress_staffmember_workday_key = %s AND bookingpress_staffmember_workhours_is_break = 1 AND  bookingpress_staffmember_id = %d', $days_val, $bookingpress_staffmember_id );
								$staff_break_where_clause = apply_filters('bookingpress_modify_get_staff_break_workhour_where_clause', $staff_break_where_clause, $_POST, $bookingpress_staffmember_id, $days_val); // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
								$bookingpress_break_time_details = $wpdb->get_results( 'SELECT bookingpress_staffmember_workhours_start_time,bookingpress_staffmember_workhours_end_time FROM ' . $tbl_bookingpress_staff_member_workhours . ' WHERE '.$staff_break_where_clause, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is table name.
								if ( !empty($bookingpress_break_time_details)) {
									foreach($bookingpress_break_time_details as $key => $value) {
										$bookingpress_breaks_arr[] = array(
											'start_time' => $value['bookingpress_staffmember_workhours_start_time'],
											'formatted_start_time' => date( $bookingpress_options['wp_default_time_format'], strtotime( $value['bookingpress_staffmember_workhours_start_time'] ) ),
											'end_time'   => $value['bookingpress_staffmember_workhours_end_time'],
											'formatted_end_time'   => date( $bookingpress_options['wp_default_time_format'], strtotime( $value['bookingpress_staffmember_workhours_end_time'] ) ),								
										);
									}
								}
								$bookingpress_workhours_data[] = array(
									'day_name'    => ucfirst( $days_val ),
									'break_times' => $bookingpress_breaks_arr,
								);
							}
						}
						$bookingpress_configure_specific_workhour = $this->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'bookingpress_configure_specific_workhour' );						
						$response['bookingpress_configure_specific_workhour'] = !empty($bookingpress_configure_specific_workhour) &&  $bookingpress_configure_specific_workhour == 'true' ? true : false;
										
					$response['workhours']      = $bookingpress_staff_member_workhours;
					$response['workhour_data']  = $bookingpress_workhours_data;
				} else  {
					$bookingpress_default_workhour_data = $bookingpress_settings->bookingpress_get_default_work_hours();
					$response['data']                = $bookingpress_default_workhour_data['data'];
					$response['selected_workhours']  = $bookingpress_default_workhour_data['selected_workhours'];
					$response['default_break_times'] = $bookingpress_default_workhour_data['default_break_times'];
				}

				// Get Daysoff detais
				$bookingpress_staff_member_id = ! empty( $_REQUEST['staffmember_id'] ) ? intval( $_REQUEST['staffmember_id'] ) : 0;
				$bookingpress_selected_year   = ! empty( $_REQUEST['selected_year'] ) ? sanitize_text_field( $_REQUEST['selected_year'] ) : date( 'Y' );
				$bookingpress_daysoff         = array();
				$bookingpress_date_format     = $bookingpress_options['wp_default_date_format'];
				$bookingpress_time_format     = $bookingpress_options['wp_default_time_format'];
				$bookingpress_staffmember_daysoff_details = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_daysoff} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_daysoff_parent = %d", $bookingpress_staff_member_id,0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_daysoff is a table name. false alarm

				if ( ! empty( $bookingpress_staffmember_daysoff_details ) ) {
					foreach ( $bookingpress_staffmember_daysoff_details as $day_off ) {

						$bookingpress_staffmember_daysoff_enddate = esc_html($day_off['bookingpress_staffmember_daysoff_enddate']);
						if($bookingpress_staffmember_daysoff_enddate == null || $bookingpress_staffmember_daysoff_enddate == 'null'){
							$bookingpress_staffmember_daysoff_enddate = esc_html($day_off['bookingpress_staffmember_daysoff_date']);
						}
												
						$day_off_arr                  = array();
						$day_off_arr['id']            = intval( $day_off['bookingpress_staffmember_daysoff_id'] );
						$day_off_arr['dayoff_name']   = sanitize_text_field( $day_off['bookingpress_staffmember_daysoff_name'] );
						$day_off_arr['dayoff_date']   = sanitize_text_field( $day_off['bookingpress_staffmember_daysoff_date'] );
						$day_off_arr['dayoff_date_end']   = $bookingpress_staffmember_daysoff_enddate;
						$day_off_arr['dayoff_formatted_date']   = date($bookingpress_date_format,strtotime($day_off['bookingpress_staffmember_daysoff_date']));
						$day_off_arr['dayoff_repeat'] = ! empty( $day_off['bookingpress_staffmember_daysoff_repeat'] ) ? true : false;

						$dayoff_label = esc_html__( 'Once Off', 'bookingpress-appointment-booking' );
						$day_off_arr['dayoff_repeat_label'] = $dayoff_label;
						if( true == $day_off_arr['dayoff_repeat'] ){
							$dayoff_label = esc_html__( 'Repeat Yearly', 'bookingpress-appointment-booking' );
							$repeat_frequency = $day_off['bookingpress_staffmember_daysoff_repeat_frequency'];
							$repeat_frequency_type = $day_off['bookingpress_staffmember_daysoff_repeat_frequency_type'];
							$repeat_duration = $day_off['bookingpress_staffmember_daysoff_repeat_duration'];
							$repeat_times = $day_off['bookingpress_staffmember_daysoff_repeat_times'];
							$repeat_date = $day_off['bookingpress_staffmember_daysoff_repeat_date'];
							$day_off_arr['dayoff_repeat_label'] = $BookingPressPro->bookingpress_retrieve_daysoff_repeat_label( $repeat_duration, $repeat_frequency, $repeat_frequency_type, $repeat_times, $repeat_date );
							$day_off_arr['repeat_frequency'] = $repeat_frequency;
							$day_off_arr['repeat_frequency_type'] = $repeat_frequency_type;
							$day_off_arr['repeat_duration'] = $repeat_duration;
							$day_off_arr['repeat_times'] = $repeat_times;
							$day_off_arr['repeat_date'] = $repeat_date;
						}
						

						$bookingpress_daysoff[]       = $day_off_arr;
					}
				}

				$response['daysoff_data'] = $bookingpress_daysoff;

				// Get Special Days details
				$bookingpress_special_day     = array();
				$bookingpress_special_day_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers_special_day . ' WHERE bookingpress_staffmember_id = %d ', $bookingpress_staff_member_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day is a table name. false alarm
				if ( ! empty( $bookingpress_special_day_data ) ) {
					foreach ( $bookingpress_special_day_data as $special_day_key => $special_day ) {
						$special_day_arr                                     = $special_days_breaks = array();
						$special_day_start_date                              = ! empty( $special_day['bookingpress_special_day_start_date'] ) ? sanitize_text_field( $special_day['bookingpress_special_day_start_date'] ) : '';
						$special_day_end_date                                = ! empty( $special_day['bookingpress_special_day_end_date'] ) ? sanitize_text_field( $special_day['bookingpress_special_day_end_date'] ) : '';
						$special_day_service_id                              = ! empty( $special_day['bookingpress_special_day_service_id'] ) ? explode( ',', $special_day['bookingpress_special_day_service_id'] ) : '';
						$special_day_id                                      = ! empty( $special_day['bookingpress_staffmember_special_day_id'] ) ? intval( $special_day['bookingpress_staffmember_special_day_id'] ) : '';
						$special_day_arr['id']                               = $special_day_id;
						$special_day_arr['special_day_start_date']           = date('Y-m-d',strtotime($special_day_start_date));
						$special_day_arr['special_day_formatted_start_date'] = date( $bookingpress_date_format, strtotime( $special_day_start_date ) );
						$special_day_arr['special_day_end_date']             = date('Y-m-d',strtotime($special_day_end_date));

						$special_day_arr['special_day_formatted_end_date'] = date( $bookingpress_date_format, strtotime( $special_day_end_date ) );
						$special_day_arr['start_time']                     = $special_day['bookingpress_special_day_start_time'];
						$special_day_arr['formatted_start_time']           = date( $bookingpress_time_format, strtotime( sanitize_text_field( $special_day['bookingpress_special_day_start_time'] ) ) );
						$special_day_arr['end_time']                       = $special_day['bookingpress_special_day_end_time'];
						$special_day_arr['formatted_end_time']             = date( $bookingpress_time_format, strtotime( sanitize_text_field( $special_day['bookingpress_special_day_end_time'] ) ) )." ".($special_day['bookingpress_special_day_end_time'] == "24:00:00" ? esc_html__('Next Day', 'bookingpress-appointment-booking') : '' );
						$special_day_arr['special_day_service']            = $special_day_service_id;

						// Fetch all breaks associated with special day
						$bookingpress_special_days_break = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers_special_day_breaks . ' WHERE bookingpress_special_day_id = %d ', $special_day_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day_breaks is a table name. false alarm
						if ( ! empty( $bookingpress_special_days_break ) && is_array( $bookingpress_special_days_break ) ) {
							foreach ( $bookingpress_special_days_break as $k3 => $v3 ) {
								$break_start_time                      = ! empty( $v3['bookingpress_special_day_break_start_time'] ) ? sanitize_text_field( $v3['bookingpress_special_day_break_start_time'] ) : '';
								$break_end_time                        = ! empty( $v3['bookingpress_special_day_break_end_time'] ) ? sanitize_text_field( $v3['bookingpress_special_day_break_end_time'] ) : '';
								$special_days_break_data               = array();
								$i                                     = 1;
								$special_days_break_data['id']         = $i;
								$special_days_break_data['start_time'] = $break_start_time;
								$special_days_break_data['end_time']   = $break_end_time;
								$special_days_break_data['formatted_start_time'] = date( $bookingpress_time_format, strtotime( $break_start_time ) );
								$special_days_break_data['formatted_end_time']   = date( $bookingpress_time_format, strtotime( $break_end_time ) );
								$i++;
								$special_days_breaks[] = $special_days_break_data;
							}
						}
						$special_day_arr['special_day_workhour'] = $special_days_breaks;
						$bookingpress_special_day[]              = $special_day_arr;
					}
				}

				$disabled_special_day_data             = $this->bookingpress_get_staffmember_special_days_dates();
				$response['special_day_data']          = $bookingpress_special_day;
				$response['disabled_special_day_data'] = $disabled_special_day_data;
				$response['msg']            = esc_html__( 'Staffmember shift managment data retrieved successfully', 'bookingpress-appointment-booking' );
				$response['variant']        = 'success';
				$response['title']          = esc_html__( 'Success', 'bookingpress-appointment-booking' );

				$staffmember_assign_services = $this->bookingpress_get_staffmember_service($bookingpress_staffmember_id);
				if(empty($staffmember_assign_services)){
					$staffmember_assign_services = array();
				}
				$response['staffmember_assign_service_ids']  = $staffmember_assign_services;
				$response['bookingpress_staff_assign_services_list'] = '';
				if(!empty($staffmember_assign_services)){
					$bookingpress_staff_assign_services_list = $this->get_bookingpress_service_data_group_with_category_for_staff($staffmember_assign_services);				
					$response['bookingpress_staff_assign_services_list'] = $bookingpress_staff_assign_services_list;
				}				
			}
			$response = apply_filters( 'bookingpress_modify_staff_shift_managment_data', $response );

			echo wp_json_encode($response);
			die;
		}

        public function get_bookingpress_service_data_group_with_category_for_staff($service_ids){

            global $wpdb, $tbl_bookingpress_categories, $tbl_bookingpress_services,$BookingPress;
            $bookingpress_currency_name   = $BookingPress->bookingpress_get_settings('payment_default_currency', 'payment_setting');
            $bookingpress_currency_symbol = ! empty($bookingpress_currency_name) ? $BookingPress->bookingpress_get_currency_symbol($bookingpress_currency_name) : '';
            $bookingpress_services_details   = array();
			$service_ids = implode(',',$service_ids);
            //Get all uncategorized services
            $bookingpress_get_uncategorized_services = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tbl_bookingpress_services WHERE bookingpress_category_id = %d AND bookingpress_service_id IN (".$service_ids.") ORDER BY bookingpress_service_id DESC", 0), ARRAY_A); //phpcs:ignore
            if(!empty($bookingpress_get_uncategorized_services)){
                foreach($bookingpress_get_uncategorized_services as $uncat_ser_key => $uncat_ser_val){
                    $bookingpress_service_price = $BookingPress->bookingpress_price_formatter_with_currency_symbol($uncat_ser_val['bookingpress_service_price'], $bookingpress_currency_symbol);

                    $bookingpress_tmp_services[] = array(
                        'service_id'                     => $uncat_ser_val['bookingpress_service_id'],
                        'service_name'                   => stripslashes_deep($uncat_ser_val['bookingpress_service_name']),
                        'service_price'                  => $bookingpress_service_price,
                        'service_price_without_currency' => $uncat_ser_val['bookingpress_service_price'],
                        'service_price_currency'         => $bookingpress_currency_symbol,
                        'service_duration'               => $uncat_ser_val['bookingpress_service_duration_val'],
                        'service_duration_unit'          => $uncat_ser_val['bookingpress_service_duration_unit'],
                    );
                }
                
                $bookingpress_services_details[] = array(
                    'category_id'       => 0,
                    'category_name'     => esc_html__('Uncategorized', 'bookingpress-appointment-booking'),
                    'category_services' => $bookingpress_tmp_services,  
                );
            }

            $bookingpress_service_categories = $wpdb->get_results('SELECT * FROM ' . $tbl_bookingpress_categories . ' WHERE bookingpress_category_id != 0 ORDER BY bookingpress_category_position ASC', ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False alarm
            foreach ( $bookingpress_service_categories as $bookingpress_service_cat_key => $bookingpress_service_cat_val ) {
                $bookingpress_cat_id       = $bookingpress_service_cat_val['bookingpress_category_id'];
                $bookingpress_tmp_services = array();
                $bookingpress_services     = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $tbl_bookingpress_services . ' WHERE bookingpress_category_id = %d AND bookingpress_service_id IN ('.$service_ids.')', $bookingpress_cat_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False alarm
                foreach ( $bookingpress_services as $bookingpress_service_key => $bookingpress_service_val ) {
                    $bookingpress_service_price = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_service_val['bookingpress_service_price'], $bookingpress_currency_symbol);

                    $bookingpress_tmp_services[] = array(
                     'service_id'                     => $bookingpress_service_val['bookingpress_service_id'],
                     'service_name'                   => stripslashes_deep($bookingpress_service_val['bookingpress_service_name']),
                     'service_price'                  => $bookingpress_service_price,
                     'service_price_without_currency' => $bookingpress_service_val['bookingpress_service_price'],
                     'service_price_currency'         => $bookingpress_currency_symbol,
                     'service_duration'               => $bookingpress_service_val['bookingpress_service_duration_val'],
                     'service_duration_unit'          => $bookingpress_service_val['bookingpress_service_duration_unit'],
                    );
                }

                if (! empty($bookingpress_tmp_services) ) {
                    $bookingpress_services_details[] = array(
                    'category_id'       => $bookingpress_cat_id,
                    'category_name'     => stripslashes_deep($bookingpress_service_cat_val['bookingpress_category_name']),
                    'category_services' => $bookingpress_tmp_services,
                    );
                }
            }

            return $bookingpress_services_details;
        }

		function  bookingpress_after_login_redirect($redirect_to, $request, $user) {
			global $BookingPress;

			if ( isset( $user->roles ) && is_array( $user->roles ) && isset( $user->caps ) && is_array( $user->caps )) {
				if ( in_array( 'bookingpress-staffmember', $user->roles ) && !in_array( 'administrator', $user->roles ) && in_array( 'bookingpress', $user->caps )) {
					$redirect_to =  esc_url( admin_url() . 'admin.php?page=bookingpress');
					$bookingpress_staffmember_access_admin = $BookingPress->bookingpress_get_settings( 'bookingpress_staffmember_access_admin', 'staffmember_setting' );
					if((!empty($_COOKIE['bookingpress_staffmember_view']) && $_COOKIE['bookingpress_staffmember_view'] == 'admin_view') && !empty($bookingpress_staffmember_access_admin) && $bookingpress_staffmember_access_admin == 'true') {
						$redirect_to = add_query_arg( 'staffmember_view','admin_view',$redirect_to);
					}
				}
			}
			return $redirect_to;
		}
		function bookingpress_check_flag_to_move_next_from_serfice( $move_to_next, $front_vue_data ){

			if( true == $move_to_next && !empty( $_GET['s_id'] ) && !empty( $_GET['sm_id'] ) ){
				$staff_member_id = intval( $_GET['sm_id'] );
				$staff_updated_id = apply_filters( 'bookingpress_modify_staffmember_id', $staff_member_id, $front_vue_data );
				if( $staff_member_id != $staff_updated_id ){
					$move_to_next = false;
				}
			}

			return $move_to_next;
		}

		function bookingpress_modify_s_id_before_retrieving_service_func( $s_id ){

			if( !empty( $_GET['sm_id'] ) ){
				$bookingpress_staff_id = intval( $_GET['sm_id'] );

				global $wpdb,$tbl_bookingpress_staffmembers_services;
				$is_staff_exists = wp_cache_get( 'bpa_is_staff_exsists_with_service_' . $s_id.'_'.$bookingpress_staff_id );
				if( false == $is_staff_exists ){
					$is_staff_exists = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_service_id = %d AND bookingpress_staffmember_id = %d", $s_id, $bookingpress_staff_id ) ); //phpcs:ignore
					wp_cache_set( 'bpa_is_staff_exsists_with_service_' . $s_id.'_'.$bookingpress_staff_id, $is_staff_exists );
				}
			
				if( empty( $is_staff_exists ) ){
					$s_id = '';
				}
			}

			return $s_id;
		}
		
		/**
		 * Function to hide services that doesn't have any staff member or staff member is disabled and assigned staff member to services
		 *
		 * @param  mixed $bpa_all_services
		 * @param  mixed $service
		 * @param  mixed $selected_service
		 * @param  mixed $bookingpress_category
		 * @return void
		 */
		function bookingpress_modify_services_with_staffmember( $bpa_all_services, $service, $selected_service, $bookingpress_category ){

			global $wpdb, $tbl_bookingpress_staffmembers_services, $tbl_bookingpress_staffmembers, $bookingpress_front_vue_data_fields;
			
			foreach( $bpa_all_services as $bskey => $bsvalue ){
				if( true == $bsvalue['is_visible'] ){
					$service_id = $bsvalue['bookingpress_service_id'];
					$service_staffmembers = $wpdb->get_results( $wpdb->prepare( "SELECT btss.* FROM {$tbl_bookingpress_staffmembers_services} btss LEFT JOIN {$tbl_bookingpress_staffmembers} bpst ON btss.bookingpress_staffmember_id=bpst.bookingpress_staffmember_id WHERE btss.bookingpress_service_id = %d AND bpst.bookingpress_staffmember_status = %d", $service_id, 1 ), ARRAY_A ); //phpcs:ignore 					
					
					if( empty( $service_staffmembers ) ){
						$bpa_all_services[ $bskey ]['is_visible'] = false;
						$bpa_all_services[ $bskey ]['is_disabled'] = true;
					} else {
						$bpa_service_staffdata = array();
						$bpa_associated_staff_member_details = array();
						foreach( $service_staffmembers as $sfdata ){
							$bpa_service_staffdata[] = $sfdata['bookingpress_staffmember_id'];
							if( empty( $bpa_associated_staff_member_details[ $sfdata['bookingpress_staffmember_id'] ] ) ){
								$bpa_associated_staff_member_details[ $sfdata['bookingpress_staffmember_id']] = array();
							}
							$bpa_associated_staff_member_details[$sfdata['bookingpress_staffmember_id']] = array(
								'bookingpress_service_price' => $sfdata['bookingpress_service_price'],
								'bookingpress_service_capacity' => $sfdata['bookingpress_service_capacity'],
								'bookingpress_min_service_capacity' => $sfdata['bookingpress_service_min_capacity']
							);
						}
						$bpa_all_services[ $bskey ]['assigned_staffmembers'] = $bpa_service_staffdata;
						$bpa_all_services[ $bskey ]['staff_member_details'] = $bpa_associated_staff_member_details;
					}
				}
			}

			return $bpa_all_services;
		}

		function bookingpress_check_staff_for_first_place( $is_first, $step_data ){

			$first_step_data = array_slice( $step_data, 0, 1 );
			$first_step_key = key( $first_step_data );

			if( 'staffmembers' == $first_step_key ){
				if( 1 == $first_step_data[ $first_step_key ]['is_first_step'] && 1 == $first_step_data[ $first_step_key ]['is_display_step'] ){
					$is_first = 1;
				}
			}


			return $is_first;
		}

		function bookingpress_add_staffmember_step_for_customize_tab(){
			?>
			<div class="bpa-cbf--preview-step" :style="{ 'background': selected_colorpicker_values.background_color,'border-color': selected_colorpicker_values.border_color }" v-if="current_element.name == 5">
				<div class="bpa-cbf--preview-step__body-content">
					<div class="bpa-cbf--preview--module-container __staffmember-module">
						<el-row>
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<div class="bpa-front-module-heading" v-text="service_container_data.staffmember_heading_title" :style="{ 'color': selected_colorpicker_values.label_title_color, 'font-size': selected_font_values.title_font_size+'px', 'font-family': selected_font_values.title_font_family}"></div>                                     
							</el-col>
						</el-row>
						<el-row :gutter="32">
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_staffmember == 'staff_1') ? ' __bpa-is-selected' : ''" @click="bpa_select_staffmember('staff_1')">
									<div class="bpa-front-si-card bpa-front-sm-card" :style="[bookingpress_shortcode_form.selected_staffmember == 'staff_1' ? { 'border-color': selected_colorpicker_values.primary_color } : { 'border-color': selected_colorpicker_values.border_color }]">
										<div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_staffmember == 'staff_1'">
											<span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_staffmember == 'staff_1' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
										</div>
										<div class="bpa-front-si-card__left bpa-front-sm-card__left">
											<div class="bpa-front-sm__default-img" :style="{'border-color': selected_colorpicker_values.border_color}">
												<svg :style="{'fill':selected_colorpicker_values.content_color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v1c0 .55.45 1 1 1h14c.55 0 1-.45 1-1v-1c0-2.66-5.33-4-8-4z"/></svg>
											</div>
										</div>
										<div class="bpa-front-si__card-body">
											<div class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.title_font_size+'px'}">Blaine Moon</div>
										</div>
									</div>
								</div>
							</el-col>
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_staffmember == 'staff_2') ? ' __bpa-is-selected' : ''" @click="bpa_select_staffmember('staff_2')">
									<div class="bpa-front-si-card bpa-front-sm-card" :style="[bookingpress_shortcode_form.selected_staffmember == 'staff_2' ? { 'border-color': selected_colorpicker_values.primary_color } : {'border-color': selected_colorpicker_values.border_color}]">
										<div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_staffmember == 'staff_2'">
											<span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_staffmember == 'staff_2' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
										</div>
										<div class="bpa-front-si-card__left bpa-front-sm-card__left">
											<div class="bpa-front-sm__default-img" :style="{'border-color': selected_colorpicker_values.border_color}">
												<svg :style="{'fill':selected_colorpicker_values.content_color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v1c0 .55.45 1 1 1h14c.55 0 1-.45 1-1v-1c0-2.66-5.33-4-8-4z"/></svg>
											</div>
										</div>
										<div class="bpa-front-si__card-body">
											<div class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.title_font_size+'px' }">Gary Williams</div>
										</div>
									</div>
								</div>
							</el-col>                                                            
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_staffmember == 'staff_3') ? ' __bpa-is-selected' : ''" @click="bpa_select_staffmember('staff_3')">
									<div class="bpa-front-si-card bpa-front-sm-card" :style="[bookingpress_shortcode_form.selected_staffmember == 'staff_3' ? { 'border-color': selected_colorpicker_values.primary_color } : {'border-color': selected_colorpicker_values.border_color}]">
										<div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_staffmember == 'staff_3'">
											<span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_staffmember == 'staff_3' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
										</div>
										<div class="bpa-front-si-card__left bpa-front-sm-card__left">
											<div class="bpa-front-sm__default-img" :style="{'border-color': selected_colorpicker_values.border_color}">
												<svg :style="{'fill':selected_colorpicker_values.content_color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v1c0 .55.45 1 1 1h14c.55 0 1-.45 1-1v-1c0-2.66-5.33-4-8-4z"/></svg>
											</div>
										</div>
										<div class="bpa-front-si__card-body">
											<div class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.title_font_size+'px' }">Gerardo Burton</div>
										</div>
									</div>
								</div>
							</el-col>
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="bpa-front-module--service-item" :class="(bookingpress_shortcode_form.selected_staffmember == 'staff_4') ? ' __bpa-is-selected' : ''" @click="bpa_select_staffmember('staff_4')">
									<div class="bpa-front-si-card bpa-front-sm-card" :style="[bookingpress_shortcode_form.selected_staffmember == 'staff_4' ? { 'border-color': selected_colorpicker_values.primary_color } : {'border-color': selected_colorpicker_values.border_color}]">
										<div class="bpa-front-si-card--checkmark-icon" v-if="bookingpress_shortcode_form.selected_staffmember == 'staff_4'">
											<span class="material-icons-round" :style="[bookingpress_shortcode_form.selected_staffmember == 'staff_4' ? { 'color': selected_colorpicker_values.primary_color } : { 'color': selected_colorpicker_values.content_color }]">check_circle</span>
										</div>
										<div class="bpa-front-si-card__left bpa-front-sm-card__left">
											<div class="bpa-front-sm__default-img" :style="{'border-color': selected_colorpicker_values.border_color}">
												<svg :style="{'fill':selected_colorpicker_values.content_color}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v1c0 .55.45 1 1 1h14c.55 0 1-.45 1-1v-1c0-2.66-5.33-4-8-4z"/></svg>
											</div>
										</div>
										<div class="bpa-front-si__card-body">
											<div class="bpa-front-si__card-body--heading" :style="{ 'color': selected_colorpicker_values.sub_title_color, 'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.title_font_size+'px'}">Harold Reed</div>
										</div>
									</div>
								</div>
							</el-col>
						</el-row>
					</div>
				</div>
				<div class="bpa-front-tabs--foot" :style="{'background': selected_colorpicker_values.background_color,'border-color':selected_colorpicker_values.border_color }">   
					<el-button class="bpa-btn bpa-btn--borderless" :style="{'color': selected_colorpicker_values.sub_title_color,'font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}" v-if="current_element.previous_tab != ''">
						<span class="material-icons-round">west</span>
						{{ booking_form_settings.goback_button_text }}
					</el-button>
					<el-button class="bpa-btn bpa-btn--primary bpa-btn--front-preview" :style="{ 'background': selected_colorpicker_values.primary_color, 'border-color': selected_colorpicker_values.primary_color, color: selected_colorpicker_values.price_button_text_color,'font-size': selected_font_values.sub_title_font_size+'px','font-family': selected_font_values.title_font_family,'font-size': selected_font_values.sub_title_font_size+'px'}" v-if="current_element.next_tab != ''">
						<span class="bpa--text-ellipsis">{{ booking_form_settings.next_button_text}} <strong>{{tab_container_data[current_element.next_tab] }}</strong></span>
						<span class="material-icons-round">east</span>
					</el-button>
				</div>
			</div>
			<?php
		}

		function bookingpress_modify_form_sequence_arr_func( $bookingpress_form_sequence_arr ){
			global $BookingPress;
			$bookingpress_form_sequence = $BookingPress->bookingpress_get_customize_settings('bookingpress_form_sequance', 'booking_form');
			$bookingpress_form_sequence = json_decode($bookingpress_form_sequence, true);
			if( json_last_error() != JSON_ERROR_NONE || !is_array( $bookingpress_form_sequence ) ){
				$bookingpress_form_sequence = array( 'service_selection', 'staff_selection' );
			}

			$bookingpress_staff_pos = array_search('staff_selection', $bookingpress_form_sequence);
			$bookingpress_service_pos = array_search('service_selection', $bookingpress_form_sequence);

			$bookingpress_sidebar_step_data = $bookingpress_form_sequence_arr;
			$bookingpress_hide_staff_selection = $BookingPress->bookingpress_get_customize_settings('hide_staffmember_selection','booking_form');
			$is_visisble = !empty($bookingpress_hide_staff_selection) && $bookingpress_hide_staff_selection == 'true' ? '0' : '1';

			if($bookingpress_staff_pos < $bookingpress_service_pos){
				$bookingpress_new_sidebar_step_data = array(
					'staffmembers' => array(
						'title' => 'staffmember_title',
						'next_tab' => 'datetime_title',
						'previous_tab' => '1',
						'name' => '5',
						'icon' => 'people',
						'is_visible' => $is_visisble,
						'tab_name' => 'staff_selection'
					),
				);
				foreach($bookingpress_sidebar_step_data as $k => $v){
					$bookingpress_new_sidebar_step_data[$k] = $v;
				}
			}else{
				$bookingpress_sidebar_step_data['service']['next_tab'] = 'staffmember_title';
				$bookingpress_new_sidebar_step_data['service'] = $bookingpress_sidebar_step_data['service'];									
				$bookingpress_new_sidebar_step_data['staffmembers'] = array(
					'title' => 'staffmember_title',
					'next_tab' => 'datetime_title',
					'previous_tab' => '1',
					'name' => '5',
					'icon' => 'people',
					'is_visible' => $is_visisble,
					'tab_name' => 'staff_selection'
				);
				$hide_category_service = $BookingPress->bookingpress_get_customize_settings('hide_category_service_selection','booking_form');				
				
				foreach($bookingpress_sidebar_step_data as $sidebar_step_data_key => $sidebar_step_data_val){
					if($sidebar_step_data_key != "service"){
						$bookingpress_new_sidebar_step_data[$sidebar_step_data_key] = $sidebar_step_data_val;
					}
				}
			}
			$bookingpress_form_sequence_arr = $bookingpress_new_sidebar_step_data;

			return $bookingpress_form_sequence_arr;
		}

		function bookingpress_position_staffmembers_func($old_position = '', $new_position = '') {

			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers;
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'manage_staffmemebr_position', true, 'bpa_wp_nonce' );
			
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			$old_position        = isset($_POST['old_position']) ? intval($_POST['old_position']) : $old_position; // phpcs:ignore WordPress.Security.NonceVerification
			$new_position        = isset($_POST['new_position']) ? intval($_POST['new_position']) : $new_position; // phpcs:ignore WordPress.Security.NonceVerification
			$response['variant'] = 'danger';
			$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
			$response['msg']     = esc_html__('Something went wrong..', 'bookingpress-appointment-booking');

			if (isset($old_position) && isset($new_position) ) {
				if ($new_position > $old_position ) {
					$staffmembers  = $wpdb->get_results( $wpdb->prepare( 'SELECT bookingpress_staffmember_position,bookingpress_staffmember_id FROM ' . $tbl_bookingpress_staffmembers . ' WHERE bookingpress_staffmember_position BETWEEN %d AND %d order by bookingpress_staffmember_position ASC', $old_position, $new_position ), ARRAY_A); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name
					foreach ( $staffmembers as $staffmember ) {
						$position = $stafffmember['bookingpress_staffmember_position'] - 1;
						$position = ( $stafffmember['bookingpress_staffmember_position'] == $old_position ) ? $new_position : $position;
						$args     = array(
							'bookingpress_staffmember_position' => $position,
						);
						$wpdb->update($tbl_bookingpress_staffmembers, $args, array( 'bookingpress_staffmember_id' => $stafffmember['bookingpress_staffmember_id'] ));
					}
				} else {
					$stafffmembers = $wpdb->get_results( $wpdb->prepare( 'SELECT bookingpress_staffmember_position,bookingpress_staffmember_id FROM ' . $tbl_bookingpress_staffmembers . ' WHERE bookingpress_staffmember_position BETWEEN %d AND %d order by bookingpress_staffmember_position ASC', $new_position, $old_position ), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_staffmembers is table name defined globally. False Positive alarm
					
					foreach ( $stafffmembers as $staffmember ) {
						$position = $staffmember['bookingpress_staffmember_position'] + 1;
						$position = ( $staffmember['bookingpress_staffmember_position'] == $old_position ) ? $new_position : $position;
						$args     = array(
						'bookingpress_staffmember_position' => $position,
						);
						$wpdb->update($tbl_bookingpress_staffmembers, $args, array( 'bookingpress_staffmember_id' => $staffmember['bookingpress_staffmember_id'] ));
					}
				}
				$response['variant'] = 'success';
				$response['title']   = esc_html__('Success', 'bookingpress-appointment-booking');
				$response['msg']     = esc_html__('staffmember position has been changed successfully.', 'bookingpress-appointment-booking');
			}

			if (isset($_POST['action']) && sanitize_text_field($_POST['action']) == 'bookingpress_position_staffmembers' ) { // phpcs:ignore WordPress.Security.NonceVerification
				wp_send_json($response);
			}
			return;
		}
		
		function bookingpress_modify_staffmember_id( $bookingpress_selected_staffmember_id, $bookingpress_front_vue_data_fields ){

			global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services;
			
			if( empty( $bookingpress_selected_staffmember_id )){
				return $bookingpress_selected_staffmember_id;
			}
			
			if( empty( $bookingpress_front_vue_data_fields ) || null == $bookingpress_front_vue_data_fields ){
				global $bookingpress_front_vue_data_fields;
			}
			
			$selected_service_id = !empty( $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] ) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] : '';
			
			if( !empty( $selected_service_id ) ){
				$is_staff_exists_key = 'bpa_is_staff_exsists_with_service_' . $selected_service_id . '_' . $bookingpress_selected_staffmember_id;
				/** Reputelog - need improvements as the query gets duplicating as this function loads multiple times */
				$is_staff_exists = $wpdb->get_row( $wpdb->prepare( "SELECT bpsr.bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers_services} bpsr LEFT JOIN {$tbl_bookingpress_staffmembers} bps ON bpsr.bookingpress_staffmember_id = bps.bookingpress_staffmember_id WHERE bpsr.bookingpress_service_id = %d AND bpsr.bookingpress_staffmember_id = %d AND bps.bookingpress_staffmember_status = %d", $selected_service_id, $bookingpress_selected_staffmember_id, 1 ) );//phpcs:ignore
				
				if( empty( $is_staff_exists ) ){
					$bookingpress_selected_staffmember_id = "";
				}
			} else {
				
				/** Reputelog - need improvements as the query gets duplicating as this function loads multiple times */

				//$is_staff_exists = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_status = %d", $bookingpress_selected_staffmember_id, 1) ); //phpcs:ignore 
				$is_staff_exists = wp_cache_get( 'bpa_is_staff_status_exsists_with_service_'.$bookingpress_selected_staffmember_id );
				if( false == $is_staff_exists ){
					$is_staff_exists = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_status = %d", $bookingpress_selected_staffmember_id, 1) ); //phpcs:ignore 
					wp_cache_set( 'bpa_is_staff_status_exsists_with_service_'.$bookingpress_selected_staffmember_id , $is_staff_exists);
				}

				if( empty( $is_staff_exists ) ){
					$bookingpress_selected_staffmember_id = "";
					$bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_url'] = "false";
				}

			
			}

			return $bookingpress_selected_staffmember_id;
		}

		function bookingpress_change_current_tab_to_staffmember( $bookingpress_dynamic_on_load_methods_data ){

			$bookingpress_dynamic_on_load_methods_data .= '
				
				if( typeof this.bookingpress_sidebar_step_data.staffmembers != "undefined" && 1 == this.bookingpress_sidebar_step_data.staffmembers.is_display_step ){
					let staff_member_tab_data = this.bookingpress_sidebar_step_data.staffmembers;
					let hide_staff_selection = ( this.appointment_step_form_data.hide_staff_selection != "false" );
					
					if( !hide_staff_selection && staff_member_tab_data.next_tab_name == this.bookingpress_current_tab && staff_member_tab_data.is_display_step == 1 ){
						this.bookingpress_current_tab = "staffmembers";
					}
					
					if( this.bookingpress_current_tab == "staffmembers" && ( this.appointment_step_form_data.selected_staff_member_id > 0 || staff_member_tab_data.is_display_step == 0 ) ){
						this.bookingpress_current_tab = staff_member_tab_data.next_tab_name;
						this.bookingpress_sidebar_step_data.staffmembers.is_navigate_to_next = true;
					}
				}
			';

			return $bookingpress_dynamic_on_load_methods_data;
		}

		function bookingpress_form_sequence_list_item_staffmember(){
			global $bookingpress_global_options;
			$bookingpress_global_options_arr       = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? $bookingpress_global_options_arr['bookingpress_staffmember_singular_name'] : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
			?>
				<div class="bpa-cfs__step-val" v-else-if="form_sequence == 'staff_selection'"><?php echo esc_html( stripslashes_deep($bookingpress_singular_staffmember_name) ); ?> <?php esc_html_e('Selection','bookingpress-appointment-booking'); ?></div>
			<?php
		}

		function bookingpress_select_any_staffmember( $bookingpress_disable_date_pre_xhr_data){

			$bookingpress_is_allow_modify = ( isset($_GET['allow_modify']) && $_GET['allow_modify'] == 0 ) ? 0 : '';
			$bookingpress_staff_loaded_from_url = !empty($_GET['sm_id']) ? intval($_GET['sm_id']) : 0;

			$bookingpress_staff_loaded_from_url = apply_filters( 'bookingpress_modify_staffmember_id', $bookingpress_staff_loaded_from_url, null );

			$bookingpress_disable_date_pre_xhr_data .= '

				let bpa_is_called = false;
				var bookingpress_is_allow_modify = "'.$bookingpress_is_allow_modify.'";
				var bookingpress_staff_loaded_from_url = "'.$bookingpress_staff_loaded_from_url.'";

				this.isLoadTimeLoader = "1";
                this.isLoadDateTimeCalendarLoad = "1";

				let staff_id = this.appointment_step_form_data.selected_staff_member_id;

				if( 0 == staff_id ){
					staff_id = "";
				}

                this.service_timing = "-3";
				
				if(  0 === this.bookingpress_sidebar_step_data["staffmembers"].is_display_step && "" == staff_id ){
					if(bookingpress_is_allow_modify == "0" && bookingpress_staff_loaded_from_url != 0){
						this.bookingpress_select_staffmember(bookingpress_staff_loaded_from_url, 0);
					}else{
						const d = await this.bookingpress_select_staffmember("any_staff", 1 );
					}
					bpa_is_called = true;
				}

				if( false == bpa_is_called && 1 == this.hide_category_service && "true" == this.appointment_step_form_data.hide_staff_selection && "" == staff_id ){
					if(bookingpress_is_allow_modify == "0" && bookingpress_staff_loaded_from_url != 0){
						this.bookingpress_select_staffmember(bookingpress_staff_loaded_from_url, 0);
					}else{
						const d = await this.bookingpress_select_staffmember("any_staff", 1);
					}
					bpa_is_called = true;
				}

				let form_sequence_first;
				if( "object" == typeof this.appointment_step_form_data.form_sequence ){
					form_sequence_first = this.appointment_step_form_data.form_sequence[0];
				} else {
					form_sequence_first = this.appointment_step_form_data.form_sequence;
				}
				if( false == bpa_is_called && 1 == this.hide_category_service && "service_selection" == form_sequence_first && "" == staff_id ){
					if(bookingpress_is_allow_modify == "0" && bookingpress_staff_loaded_from_url != 0){
						this.bookingpress_select_staffmember(bookingpress_staff_loaded_from_url, 0);
					}else{
						const d = await this.bookingpress_select_staffmember("any_staff", 1 );
					}
					bpa_is_called = true;
				}
				
				if( false == bpa_is_called && 1 != this.hide_category_service && "true" == this.appointment_step_form_data.hide_staff_selection && false == this.is_staff_member_set_from_url && "" == staff_id ){
					if(bookingpress_is_allow_modify == "0" && bookingpress_staff_loaded_from_url != 0){
						this.bookingpress_select_staffmember(bookingpress_staff_loaded_from_url, 0);
					}else{
						const d = await this.bookingpress_select_staffmember("any_staff", 1 );
					}
					bpa_is_called = true;
				}

				if( false == bpa_is_called && "" == staff_id && "true" == this.appointment_step_form_data.select_any_staffmember ){
					const d = await this.bookingpress_select_staffmember("any_staff", 1 );
					bpa_is_called = true;
				}
			';

			return $bookingpress_disable_date_pre_xhr_data;
		}

		function bookingpress_set_staffmember_appointment_xhr_data_func(){
			?>
				if( "" != bookingpress_appointment_form_data.selected_staffmember ){
					postData.staffmember_id = bookingpress_appointment_form_data.selected_staffmember;
					postData.appointment_data_obj.bookingpress_selected_staff_member_details = {
						selected_staff_member_id: bookingpress_appointment_form_data.selected_staffmember
					}
				}
			<?php
		}

		function bookingpress_check_available_timings_with_staffmember_func( $service_timings, $selected_service_id, $selected_date, $total_booked_appiontments ){
			
			$bookingpress_staffmember_id = !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

			if( empty( $bookingpress_staffmember_id ) && !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ){ // phpcs:ignore
				$bookingpress_staffmember_id = intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ); // phpcs:ignore
			}

			if( empty( $bookingpress_staffmember_id ) ){

				/** Check for any staff member */
				if( !empty( $_POST['appointment_data_obj']['any_staff_selected'] ) && 1 == $_POST['appointment_data_obj']['any_staff_selected'] && !empty( $_POST['appointment_data_obj']['available_staffs'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
					foreach( $service_timings as $key => $service_timing_data ){
						if( !empty( $service_timing_data['available_staffs']) && (1 == $service_timing_data['is_booked'] || ( !empty( $service_timing_data['is_booked_appointment'] ) && 1 == $service_timing_data['is_booked_appointment'])) ){
							$service_timings[ $key ]['is_booked'] = 0;
							$service_timings[ $key ]['is_booked_appointment'] = 0;
							$service_timings[ $key ]['total_booked'] = 0;
						}
					}
				}

				return $service_timings;
			}

			global $tbl_bookingpress_appointment_bookings, $wpdb, $bookingpress_services;

	
			$selected_duration_date = date('Y-m-d', strtotime( $selected_date . ' -7 days ' ) );

			$get_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE ( bookingpress_appointment_date = %s AND bookingpress_service_id != %d AND bookingpress_staff_member_id = %d AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) ) OR ( bookingpress_appointment_date >= %s AND bookingpress_service_duration_unit = %s AND bookingpress_service_id != %d AND bookingpress_staff_member_id = %d AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) )", $selected_date, $selected_service_id, $bookingpress_staffmember_id, '1', '2', $selected_duration_date, 'd', $selected_service_id, $bookingpress_staffmember_id, '1','2' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			$booked_appointment_details = array();

			if( !empty( $get_appointments ) ){
				foreach( $get_appointments as $appointment_data ){
					if( 'd' != $appointment_data->bookingpress_service_duration_unit ){
						$booked_appointment_details[] = array(
							'booked_service_id' => $appointment_data->bookingpress_service_id,
							'booked_appointment_date' => $appointment_data->bookingpress_appointment_date,
							'booked_appointment_duration_unit' => $appointment_data->bookingpress_service_duration_unit,
							'booked_appointment_start_time' => $appointment_data->bookingpress_appointment_time,
							'booked_appointment_end_time' => $appointment_data->bookingpress_appointment_end_time,
						);
					}
				}
			}

			if(!empty($service_timings) && !empty( $get_appointments ) ){
				foreach( $service_timings as $k => $service_timing_data ){

					$service_start_time = $service_timing_data['store_start_time'].':00';
					$service_end_time = $service_timing_data['store_end_time'].':00';
					
					foreach( $get_appointments as $get_appointment ){
						
						$appointment_start_time = $get_appointment->bookingpress_appointment_time;
						$appointment_end_time = $get_appointment->bookingpress_appointment_end_time;

						if( '00:00:00' == $appointment_start_time && '00:00:00' == $appointment_end_time && $get_appointment->bookingpress_service_duration_unit == 'd' ){
							if( $selected_date == $get_appointment->bookingpress_appointment_date ){
								unset( $service_timings[$k] );
								break;
							} else {
								$appointment_date = $get_appointment->bookingpress_appointment_date;
								$appointment_duration = $get_appointment->bookingpress_service_duration_val;

								$booked_date = date( 'Y-m-d', strtotime( $appointment_date . '+' . ( $appointment_duration - 1 ) . ' days' ) );
								if( $selected_date >= $appointment_date && $selected_date <= $booked_date ){
									unset( $service_timings[$k] );
									break;
								}
							}
						}

						$booking_service_id = $get_appointment->bookingpress_service_id;

						if( ($appointment_start_time >= $service_start_time && $appointment_end_time <= $service_end_time) || ( $appointment_start_time < $service_end_time && $appointment_end_time > $service_start_time ) ){
							unset( $service_timings[$k] );
						}
					}		

				}
			}
			
			return $service_timings;
		}

		function bookingpress_modify_disable_dates_with_staffmember_func( $bookingpress_disable_date, $bookingpress_selected_service, $month_check = '' ){
			$bookingpress_staffmember_id = !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

			if( empty( $bookingpress_staffmember_id ) && !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ){ // phpcs:ignore
				$bookingpress_staffmember_id = intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ); // phpcs:ignore
			}

			if( empty( $bookingpress_staffmember_id ) ){
				return $bookingpress_disable_date;
			}

			global $tbl_bookingpress_appointment_bookings, $wpdb;
			if( !empty( $month_check ) ){				
				$start_date = date('Y-m-d', strtotime( $month_check ) );
				$end_date = date( 'Y-m-d', strtotime( 'last day of this month', strtotime( $start_date ) ) );
			} else {
				$start_date = date('Y-m-d', current_time('timestamp') );
				$end_date = date( 'Y-m-d', strtotime( 'last day of this month', current_time( 'timestamp' ) ) );
			}

			$get_appointments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_staff_member_id = %d AND bookingpress_service_id != %d AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) AND bookingpress_appointment_date BETWEEN %s AND %s AND bookingpress_service_duration_unit = %s", $bookingpress_staffmember_id, $bookingpress_selected_service, '1', '2', $start_date, $end_date, 'd' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

			if( !empty( $get_appointments ) ){
				foreach( $get_appointments as $appointment_data ){
					$service_duration_val = $appointment_data->bookingpress_service_duration_val;
					
					if( 1 == $service_duration_val ){
						array_push( $bookingpress_disable_date, date( 'c', strtotime( $appointment_data->bookingpress_appointment_date ) ) );
					} else if( 1 < $service_duration_val ){
						$booking_start_date = $appointment_data->bookingpress_appointment_date;
						$booking_end_date = date('Y-m-d', strtotime($booking_start_date  . '+' . $service_duration_val . ' days' ) );
					
						$start_date = new DateTime( $booking_start_date );
						$end_date = new DateTime( $booking_end_date );
						
						$interval = DateInterval::createFromDateString('1 day');
						$period = new DatePeriod( $start_date, $interval, $end_date );

						foreach( $period as $dt ){
							$current_date = $dt->format("c");
							array_push( $bookingpress_disable_date, $current_date );
						}
					}
				}
			}

			return $bookingpress_disable_date;
		}

		function bookingpress_format_assigned_service_amounts_func(){
			global $wpdb, $bookingpress_global_options, $BookingPress;
			$response                    = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'format_assigned_service_amount', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			$response['assign_service_details'] = '';
			
			$bookingpress_assign_service_list = ! empty( $_POST['assigned_service_list'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['assigned_service_list']) : array(); //phpcs:ignore

			if(!empty($bookingpress_assign_service_list)){

				foreach($bookingpress_assign_service_list as $assign_service_list_key => $assign_service_list_val){
					$bookingpress_assign_service_list[$assign_service_list_key]['assign_service_formatted_price'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($assign_service_list_val['assign_service_price']);
					
					$bookingpress_assign_service_list[$assign_service_list_key]['assign_service_name'] = isset($assign_service_list_val['assign_service_name']) ? stripslashes_deep($assign_service_list_val['assign_service_name']) : ''; 

					if(!empty($assign_service_list_val['bookingpress_custom_durations_data'])) {						
						$staffmember_custom_service = $assign_service_list_val['bookingpress_custom_durations_data'];
						foreach($staffmember_custom_service as $key => $val) {																				
							$bookingpress_assign_service_list[$assign_service_list_key]['bookingpress_custom_durations_data'][$key]['staff_service_formatted_price'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol($val['staff_service_price']);
						}
					}
				}

				$bookingpress_assign_service_list = apply_filters( 'bookingpress_modify_staff_assign_service_list', $bookingpress_assign_service_list );
				

				$response['variant'] = 'success';
				$response['title'] = esc_html__('Success', 'bookingpress-appointment-booking');
				$response['msg'] = esc_html__('Assigned service formatted successfully', 'bookingpress-appointment-booking');
				$response['assign_service_details'] = $bookingpress_assign_service_list;
			}

			echo wp_json_encode($response);
			exit;
		}

		function bookingpress_set_selected_staffmember_id( $bookingpress_disable_date_xhr_data ){

			$bookingpress_disable_date_xhr_data .= '
				postData.staffmember_id = vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id;
			';

			return $bookingpress_disable_date_xhr_data;
		}

		function bookingpress_booked_appointment_where_clause_staffmember( $where_clause ){

			$bookingpress_staffmember_id = !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

			if( empty( $bookingpress_staffmember_id ) && !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ){ // phpcs:ignore
				$bookingpress_staffmember_id = intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ); // phpcs:ignore
			}

			if( !empty( $bookingpress_staffmember_id ) ){
				global $wpdb;

				$where_clause = preg_replace( '/(AND(\s+)bookingpress_service_id(\s+)\=(\s+)(\d+))/', '', $where_clause); //skip checking service id and fetch details for all staff's services.

				$where_clause .= $wpdb->prepare( "AND bookingpress_staff_member_id = %d", $bookingpress_staffmember_id );
			} else if( !empty( $_POST['appointment_data_obj']['any_staff_selected'] ) && ( true == $_POST['appointment_data_obj']['any_staff_selected'] || 1 == $_POST['appointment_data_obj']['any_staff_selected'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
				$where_clause = preg_replace( '/(AND(\s+)bookingpress_service_id(\s+)\=(\s+)(\d+))/', '', $where_clause); //skip checking service id and fetch details for all staff's services.
			}

			return $where_clause;
		}

		function bookingpress_remove_staffmember_from_categories( $bookingpress_front_vue_data_fields ) {
			global $BookingPress;
			
			if( !empty($bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']) && array_key_exists('staffmembers', $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data'] ) ){
				global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services, $tbl_bookingpress_services;

				$service_categories = $bookingpress_front_vue_data_fields['service_categories'];
				//$service_tmp_data = array();
				$service_cate_where_clause = '';
				if( !empty( $bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_url'] ) && 'true' == $bookingpress_front_vue_data_fields['is_bookingpress_staff_loaded_from_url'] ){
					//$service_cate_where_clause 
					$staff_member_id = !empty( $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_staff_member_id'] ) ? $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_staff_member_id'] : '';
					if( !empty( $staff_member_id ) ){
						$service_cate_where_clause = $wpdb->prepare( 'AND bpst.bookingpress_staffmember_id = %d', $staff_member_id );
					}
				}
				$ct = 0;

				$bkp_categories_services_data = array();
				foreach( $service_categories as $sc_key => $sc_data ){
					$bookingpress_category_id = $sc_data['bookingpress_category_id'];

					$bookingpress_staffmember_ids = $wpdb->get_results( $wpdb->prepare( "SELECT bpst.bookingpress_staffmember_id,bps.bookingpress_category_id FROM `{$tbl_bookingpress_staffmembers_services}` bpst LEFT JOIN `{$tbl_bookingpress_services}` bps ON bps.bookingpress_service_id = bpst.bookingpress_service_id WHERE bps.bookingpress_category_id = %d {$service_cate_where_clause} GROUP BY bpst.bookingpress_staffmember_id,bps.bookingpress_category_id", $bookingpress_category_id) );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services & $tbl_bookingpress_services are table name.

					if( !empty( $bookingpress_staffmember_ids ) ){
						$bookingpress_staff_member_ids = array();
						foreach( $bookingpress_staffmember_ids as $k => $v ){
							$bookingpress_staff_member_ids[] = $v->bookingpress_staffmember_id;
							//$service_tmp_data[ $bookingpress_category_id ][] = $v->bookingpress_staffmember_id;
						}
						if( !empty( $bookingpress_staff_member_ids ) ){
							$bookingpress_front_vue_data_fields['service_categories'][$sc_key]['bookingpress_staffmembers'] = $bookingpress_staff_member_ids;
							$bookingpress_front_vue_data_fields['service_categories'][$sc_key]['is_visible'] = true;
							
							if( $ct == 0 ){

								foreach( $bookingpress_front_vue_data_fields['all_services_data'] as $askey => $asval ){
									$as_service_id = $asval['bookingpress_service_id'];
									$as_service_obj = !empty( $bookingpress_front_vue_data_fields['bookingpress_all_services_data'][ $as_service_id ] ) ? $bookingpress_front_vue_data_fields['bookingpress_all_services_data'][ $as_service_id ] : $asval;

									if( (true == $as_service_obj['is_visible'] && false == $as_service_obj['is_disabled']) && $asval['bookingpress_category_id'] == $bookingpress_category_id ){
										$service_dt = $asval;

										$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_category'] = $bookingpress_category_id;
										$service_dt['bookingpress_staffmembers'] = $bookingpress_staff_member_ids;										
										$bkp_categories_services_data[] = $service_dt;
										$ct++;
									}
								}
							}
						} else {
							unset( $bookingpress_front_vue_data_fields['service_categories'][$sc_key] );//['is_visible'] = false;	
						}
					} else {
						//$bookingpress_front_vue_data_fields['service_categories'][$sc_key]['is_visible'] = false;
						unset( $bookingpress_front_vue_data_fields['service_categories'][$sc_key] );
					}
					$bookingpress_front_vue_data_fields[ 'services_data' ] = $bkp_categories_services_data;
				}

				$services_data_from_categories = $bookingpress_front_vue_data_fields['bpa_services_data_from_categories'];
				
				foreach( $services_data_from_categories as $category_id => $sc_data ){
					foreach( $sc_data as $k => $v ){
						$bookingpress_service_id = $v['bookingpress_service_id'];
						$get_staffmember_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_id FROM `{$tbl_bookingpress_staffmembers_services}` WHERE bookingpress_service_id = %d", $bookingpress_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
						$bookingpress_staff_ids = array();
						if( !empty( $get_staffmember_data ) ){
							foreach( $get_staffmember_data as $staffmember_data ){
								$bookingpress_staff_ids[] = $staffmember_data->bookingpress_staffmember_id;
							}
						}

						$bookingpress_front_vue_data_fields['bpa_services_data_from_categories'][ $category_id ][ $k ]['bookingpress_staffmembers'] = $bookingpress_staff_ids;
					}
				}

				$services_data_from_categories_ = $bookingpress_front_vue_data_fields['bpa_services_data_from_categories'];

				$validate_fields = array(
					'selected_staff_member_id'
				);

				$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['staffmembers']['validate_fields'] = $validate_fields;

				$bookingpress_staff_err_msg = $BookingPress->bookingpress_get_settings('no_staffmember_selected_for_the_booking','message_setting');
				$bookingpress_staff_err_msg = !empty($bookingpress_staff_err_msg) ? stripslashes_deep($bookingpress_staff_err_msg) : esc_html__("Please select staff member", "bookingpress-appointment-booking");
				$validate_msg = array(
					'selected_staff_member_id' => $bookingpress_staff_err_msg
				);

				$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['staffmembers']['validation_msg'] = $validate_msg;

			}
			
			$staffmember_heading_title = $BookingPress->bookingpress_get_customize_settings('staffmember_heading_title', 'booking_form');
			$bookingpress_front_vue_data_fields['staffmember_heading_title'] = !empty($staffmember_heading_title) ? stripslashes_deep($staffmember_heading_title) : 'Select Staffmember';
			$staffmember_any_staff_title = $BookingPress->bookingpress_get_customize_settings('any_staff_title', 'booking_form');
			$bookingpress_front_vue_data_fields['any_staff_title'] = !empty($staffmember_any_staff_title) ? stripslashes_deep($staffmember_any_staff_title) : 'Any Staff';

			
			if( !empty( $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_staff_member_id'] ) ){
				$all_services_data = $bookingpress_front_vue_data_fields['bookingpress_all_services_data'];
				foreach( $bookingpress_front_vue_data_fields['bookingpress_all_services_data'] as $service_id => $bpa_service_data ){
					if( empty( $bpa_service_data['assigned_staffmembers'] ) ){
						continue;
					}
					if( !in_array( $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_staff_member_id'], $bpa_service_data['assigned_staffmembers'] ) ){
						$bookingpress_front_vue_data_fields['bookingpress_all_services_data'][ $service_id ]['is_disabled'] = true;
						$bookingpress_front_vue_data_fields['bookingpress_all_services_data'][ $service_id ]['is_visible'] = false;
					}
				}
			}

			/** set selected service id to blank if the selected staff member id not assigned to selected service */
			if( !empty( $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] ) ){
				
				$bpa_selected_service_id = $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'];
				if( !empty( $_GET['s_id'] ) ){
					$bpa_selected_service_id = intval($_GET['s_id']);
				}
				
				$bpa_selected_staff_id = $bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_staff_member_id'];
				$bpa_all_services_data = $bookingpress_front_vue_data_fields['bookingpress_all_services_data'];
				
				if( !empty( $bpa_all_services_data[$bpa_selected_service_id] ) && !empty( $bpa_selected_staff_id ) && !in_array( $bpa_selected_staff_id, $bpa_all_services_data[$bpa_selected_service_id]['assigned_staffmembers'] ) ){
					$bookingpress_front_vue_data_fields['appointment_step_form_data']['selected_service'] = "";
					/** if auto navigate set to true then change it to false */
					if( 1 == $bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['service']['is_navigate_to_next'] ){
						$bookingpress_front_vue_data_fields['bookingpress_sidebar_step_data']['service']['is_navigate_to_next'] = 0;
					}
				}
			}
						
			return $bookingpress_front_vue_data_fields;
		}

		function bookingpress_modify_bringanyone_details_func( $bookingpress_bring_anyone_with_you_details, $selected_service_id ){

			global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services;

			if( !isset( $bookingpress_bring_anyone_with_you_details[ $selected_service_id] ) ){
				$bookingpress_bring_anyone_with_you_details[ $selected_service_id ] = array(
					'bookingpress_service_max_capacity' => 1,
					'bookingpress_service_id' => $selected_service_id
				);
			}

			$staffmember_capacity_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_id,bookingpress_service_capacity FROM `{$tbl_bookingpress_staffmembers_services}` WHERE bookingpress_service_id = %d", $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
			
			if( !empty( $staffmember_capacity_data ) ){
				if( !isset( $bookingpress_bring_anyone_with_you_details[ $selected_service_id ]['bookingpress_staffmember_service_capacity'] )) {
					$bookingpress_bring_anyone_with_you_details[ $selected_service_id ]['bookingpress_staffmember_service_capacity'] = array();
				}
				foreach( $staffmember_capacity_data as $capacity_details ){
					$staffmember_id = $capacity_details->bookingpress_staffmember_id;
					$staffmember_capacity = $capacity_details->bookingpress_service_capacity;
					$bookingpress_bring_anyone_with_you_details[ $selected_service_id ]['bookingpress_staffmember_service_capacity'][ $staffmember_id ] = $staffmember_capacity;
				}
			}

			return $bookingpress_bring_anyone_with_you_details;

		}

		function bookingpress_total_booked_appointment_where_clause_function( $where_clause ){

			$bookingpress_staffmember_id = !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

			if( empty( $bookingpress_staffmember_id ) ){
				return $where_clause;
			}

			global $wpdb;

			$where_clause = $wpdb->prepare( ' AND bookingpress_staff_member_id = %d ', $bookingpress_staffmember_id  );

			return $where_clause;
		}

		function bpa_assign_staffmember_to_slots($return_data = false){

			global $BookingPress, $wpdb, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_services, $tbl_bookingpress_appointment_bookings;
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

			$slot_details = !empty( $_POST['slot_details'] ) ? json_decode( stripslashes_deep( $_POST['slot_details'] ), true ) : array(); //phpcs:ignore

			$bookingpress_current_date = $slot_details['store_service_date'];

			$available_staffs = $slot_details['available_staffs'];

			$bookingpress_any_staff_selected_rule = $BookingPress->bookingpress_get_settings('bookingpress_staffmember_auto_assign_rule', 'staffmember_setting');

			$where_clause = " AND 1=1 ";
			if( true == $check_capacity ){
				$where_clause .= $wpdb->prepare( " AND bookingpress_service_capacity >= %d", $bring_capacity );
			}

			$available_staffs_placeholder = ' AND bookingpress_staff_member_id IN (';
			$available_staffs_placeholder .= rtrim( str_repeat( '%s,', count( $available_staffs ) ), ',' );
			$available_staffs_placeholder .= ')';
			
			array_unshift( $available_staffs, $available_staffs_placeholder );

			if( "least_assigned_by_day" == $bookingpress_any_staff_selected_rule || "most_assigned_by_day" == $bookingpress_any_staff_selected_rule ){
				$ordby = "ASC";
				$minmax = "min";
				if( "most_assigned_by_day" == $bookingpress_any_staff_selected_rule ){
					$ordby = "DESC";
					$minmax = "max";
				}

				if(!empty($bookingpress_selected_service_id)){
					$bookingpress_search_query       = 'WHERE 1=1 ';
					
					//$bookingpress_search_query_where = "AND (bookingpress_service_id = {$bookingpress_selected_service_id} ) ";

					$bookingpress_search_query_where = "AND ( bookingpress_appointment_date LIKE '{$bookingpress_current_date}' OR bookingpress_appointment_date IS NULL ) AND ( bookingpress_appointment_status IS NULL OR bookingpress_appointment_status = 1 OR bookingpress_appointment_status = 2 )";

					$bookingpress_search_query_where .= call_user_func_array(array( $wpdb, 'prepare' ), $available_staffs );

					$bookingpress_staff_wise_bookings = $wpdb->get_results( "SELECT bps.bookingpress_staffmember_id, bpa.bookingpress_appointment_date,SUM( ( CASE WHEN bpa.bookingpress_appointment_booking_id IS NOT NULL THEN 1 ELSE 0 END ) ) as booked_appointments FROM {$tbl_bookingpress_staffmembers} bps LEFT JOIN {$tbl_bookingpress_appointment_bookings} bpa ON bps.bookingpress_staffmember_id = bpa.bookingpress_staff_member_id {$bookingpress_search_query_where} GROUP BY bpa.bookingpress_staff_member_id" ); //phpcs:ignore

					$assigned_staffs = array();
					$assigned_staff_ids = array();
					foreach( $bookingpress_staff_wise_bookings as $staff_booking_details ){
						$staff_id = $staff_booking_details->bookingpress_staffmember_id;
						$staff_bookings = $staff_booking_details->booked_appointments;

						if( !in_array( $staff_id, $slot_details['available_staffs'] ) ){
							continue;
						}

						$assigned_staff_ids[] = $staff_id;
						$assigned_staffs[ $staff_id ] = $staff_bookings;
					}

					$diffs = array_diff( $slot_details['available_staffs'], $assigned_staff_ids );
					if( !empty( $diffs ) ){
						$staff_with_no_bookings = array_values( $diffs );
						foreach( $staff_with_no_bookings as $staff_id_ ){
							$assigned_staffs[ $staff_id_ ] = 0;
						}
					}

					
					$filter_appointment_staffmember = array_keys( $assigned_staffs, $minmax( $assigned_staffs ) );
					$filtered_staff_ids = array();
					foreach( $filter_appointment_staffmember as $fstaff_id ){
						$filtered_staff_ids[ $fstaff_id ] = $assigned_staffs[ $fstaff_id ];
					}

					if( count( $filter_appointment_staffmember ) > 0 ){
						$bookingpress_staffmember_id = array_rand( $filtered_staff_ids );
					} else {
						$bookingpress_staffmember_id = $filter_appointment_staffmember;
					}
				}

			} else if( 'least_assigned_by_week' == $bookingpress_any_staff_selected_rule || 'most_assigned_by_week' == $bookingpress_any_staff_selected_rule ){
				
				$start_of_week = get_option('start_of_week');
				$weekdays = array(
					'Sunday',
					'Monday',
					'Tuesday',
					'Wednesday',
					'Thursday',
					'Friday',
					'Saturday'
				);
				$weekdays_short = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );

				$current_day = date('D', strtotime( $bookingpress_current_date ) );

				if( $current_day == $weekdays_short[ $start_of_week ] ){
					$bookingpress_week_start_date = $bookingpress_current_date;
					$bookingpress_week_end_date = date( 'Y-m-d', strtotime( date('Y-m-d', strtotime( $bookingpress_week_start_date . '+1 week') ) . ' -1 day' ) );

				} else {
					$bookingpress_week_start_date = date('Y-m-d', strtotime( $bookingpress_current_date .' last '.$weekdays[$start_of_week] ) );
					$bookingpress_week_end_date = date( 'Y-m-d', strtotime( date('Y-m-d', strtotime( $bookingpress_week_start_date . '+1 week') ) . ' -1 day' ) );
				}
				
				$ordby = "ASC";
				$minmax = "min";
				if( "most_assigned_by_week" == $bookingpress_any_staff_selected_rule ){
					$ordby = "DESC";
					$minmax = "max";
				}

				$bookingpress_search_query       = 'WHERE 1=1 ';
				
				//$bookingpress_search_query_where .= "AND (bookingpress_service_id = {$bookingpress_selected_service_id} ) ";
				
				$bookingpress_search_query_where .= $wpdb->prepare( "AND ( bookingpress_appointment_date >= %s AND bookingpress_appointment_date <= %s ) AND ( bookingpress_appointment_status IS NULL OR bookingpress_appointment_status = 1 OR bookingpress_appointment_status = 2 )", $bookingpress_week_start_date, $bookingpress_week_end_date );

				$bookingpress_search_query_where .= call_user_func_array(array( $wpdb, 'prepare' ), $available_staffs );

				$bookingpress_staff_wise_bookings = $wpdb->get_results( "SELECT bps.bookingpress_staffmember_id, bpa.bookingpress_appointment_date,SUM( ( CASE WHEN bpa.bookingpress_appointment_booking_id IS NOT NULL THEN 1 ELSE 0 END ) ) as booked_appointments FROM {$tbl_bookingpress_staffmembers} bps LEFT JOIN {$tbl_bookingpress_appointment_bookings} bpa ON bps.bookingpress_staffmember_id = bpa.bookingpress_staff_member_id {$bookingpress_search_query_where} GROUP BY bps.bookingpress_staffmember_id" ); //phpcs:ignore

				$assigned_staffs = array();
				$assigned_staff_ids = array();
				foreach( $bookingpress_staff_wise_bookings as $staff_booking_details ){
					$staff_id = $staff_booking_details->bookingpress_staffmember_id;
					$staff_bookings = $staff_booking_details->booked_appointments;

					if( !in_array( $staff_id, $slot_details['available_staffs'] ) ){
						continue;
					}

					$assigned_staff_ids[] = $staff_id;
					$assigned_staffs[ $staff_id ] = $staff_bookings;
				}

				$diffs = array_diff( $slot_details['available_staffs'], $assigned_staff_ids );
				if( !empty( $diffs ) ){
					$staff_with_no_bookings = array_values( $diffs );
					foreach( $staff_with_no_bookings as $staff_id_ ){
						$assigned_staffs[ $staff_id_ ] = 0;
					}
				}

				
				$filter_appointment_staffmember = array_keys( $assigned_staffs, $minmax( $assigned_staffs ) );
				$filtered_staff_ids = array();
				foreach( $filter_appointment_staffmember as $fstaff_id ){
					$filtered_staff_ids[ $fstaff_id ] = $assigned_staffs[ $fstaff_id ];
				}

				if( count( $filter_appointment_staffmember ) > 0 ){
					$bookingpress_staffmember_id = array_rand( $filtered_staff_ids );
				} else {
					$bookingpress_staffmember_id = $filter_appointment_staffmember;
				}
				
				
			} else if( 'most_expensive' == $bookingpress_any_staff_selected_rule || 'least_expensive' == $bookingpress_any_staff_selected_rule ){

				$minmax = 'max';
				if( 'least_expensive' == $bookingpress_any_staff_selected_rule ){
					$minmax = 'min';
				}
				
				$available_staffs = $slot_details['available_staffs'];
				
				$available_staffs_placeholder = ' AND bpss.bookingpress_staffmember_id IN (';
				$available_staffs_placeholder .= rtrim( str_repeat( '%s,', count( $available_staffs ) ), ',' );
				$available_staffs_placeholder .= ')';
				
				array_unshift( $available_staffs, $available_staffs_placeholder );
				$where_clause .= call_user_func_array(array( $wpdb, 'prepare' ), $available_staffs );

				$bookingpress_assigned_staffmembers_details = $wpdb->get_results( $wpdb->prepare( "SELECT bpss.bookingpress_staffmember_id, bpss.bookingpress_service_price FROM {$tbl_bookingpress_staffmembers_services} bpss LEFT JOIN ". $tbl_bookingpress_staffmembers ." bpsf ON bpss.bookingpress_staffmember_id = bpsf.bookingpress_staffmember_id WHERE bpss.bookingpress_service_id = %d AND bpsf.bookingpress_staffmember_status = %d " . $where_clause, $bookingpress_selected_service_id, 1 ) ); // phpcs:ignore

				$staffwise_price_data = array();
				foreach( $bookingpress_assigned_staffmembers_details as $staff_price_details ){
					$staffwise_price_data[ $staff_price_details->bookingpress_staffmember_id ] = $staff_price_details->bookingpress_service_price;
				}

				$filter_appointment_staffmember = array_keys( $staffwise_price_data, $minmax( $staffwise_price_data ) );

				$filtered_staff_ids = array();
				foreach( $filter_appointment_staffmember as $fstaff_id ){
					$filtered_staff_ids[ $fstaff_id ] = $assigned_staffs[ $fstaff_id ];
				}

				if( count( $filter_appointment_staffmember ) > 0 ){
					$bookingpress_staffmember_id = array_rand( $filtered_staff_ids );
				} else {
					$bookingpress_staffmember_id = $filter_appointment_staffmember;
				}

			}

			$response['variant'] = 'success';
			$response['title'] = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['msg']     = esc_html__( 'Data retrieved successfully', 'bookingpress-appointment-booking' );
			$response['staffmember_id'] = $bookingpress_staffmember_id;
			if($return_data){
				return $response;
			}			
			echo wp_json_encode( $response );
			die;
		}

		function bpa_calculate_staff_breakhours_data_callback( $service_timing_arr, $staff_special_day_break_data ){

			$slot_start_time = $service_timing_arr['store_start_time'];
			$slot_end_time = $service_timing_arr['store_end_time'];

            foreach( $staff_special_day_break_data as $blocked_time ){
                $blocked_start_time = $blocked_time['start_time'];
                $blocked_end_time = $blocked_time['end_time'];

                if( ( $blocked_start_time >= $slot_start_time && $blocked_end_time <= $slot_end_time ) || ( $blocked_start_time < $slot_end_time && $blocked_end_time > $slot_start_time) ){
                    $service_timing_arr['is_blocked'] = true;
					$service_timing_arr['break_start_time'] = $blocked_start_time;
					$service_timing_arr['break_end_time'] = $blocked_end_time;
                }

            }

			return $service_timing_arr;
		}

		function bookingpress_assign_available_staffmember( $bookingpress_dynamic_time_select_after ){

			$bookingpress_any_staff_modify_xhr_request_data = '';
			$bookingpress_any_staff_modify_xhr_request_data = apply_filters( 'bookingpress_any_staff_modify_xhr_request_data', $bookingpress_any_staff_modify_xhr_request_data );

			$bookingpress_after_add_any_staff_member_price_data = '';
			$bookingpress_after_add_any_staff_member_price_data = apply_filters( 'bookingpress_after_add_any_staff_member_price_data', $bookingpress_after_add_any_staff_member_price_data );


			$bookingpress_dynamic_time_select_after .= '
				if( "undefined" != typeof time_details.available_staffs ){
					if( 1 < time_details.available_staffs.length ){
						let available_staff_id = time_details.available_staffs;

						var bkp_wpnonce_pre = "'.esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ).'";
						var bkp_wpnonce_pre_fetch = document.getElementById("_wpnonce");
						if(typeof bkp_wpnonce_pre_fetch=="undefined" || bkp_wpnonce_pre_fetch==null){
							bkp_wpnonce_pre_fetch = bkp_wpnonce_pre;
						} else {
							bkp_wpnonce_pre_fetch = bkp_wpnonce_pre_fetch.value;
						}
						
						let postData = {
							action: "bpa_assign_staffmember",
							slot_details: JSON.stringify( time_details ),
							service_id: vm.appointment_step_form_data.selected_service,
							_wpnonce: bkp_wpnonce_pre_fetch
						};

						'.$bookingpress_any_staff_modify_xhr_request_data.'

						if( "undefined" != vm.appointment_step_form_data.bookingpress_selected_bring_members && 0 < vm.appointment_step_form_data.bookingpress_selected_bring_members ){
							postData.selected_bring_members = vm.appointment_step_form_data.bookingpress_selected_bring_members;
						} else {
							postData.selected_bring_members = 1;
						}

						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
						.then( function (response) {
							
							if( "success" == response.data.variant ){
								vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = response.data.staffmember_id;
								vm.appointment_step_form_data.selected_staff_member_id = response.data.staffmember_id;
								vm.appointment_step_form_data.is_staff_exists = "1";

								let selected_service = vm.appointment_step_form_data.selected_service;
								let selected_service_data = vm.bookingpress_all_services_data[ selected_service ];
								let service_staff_details = selected_service_data.staff_member_details[ response.data.staffmember_id ];
								let selected_staff_price = service_staff_details.bookingpress_service_price;
								vm.appointment_step_form_data.service_price_without_currency = selected_staff_price;
								vm.appointment_step_form_data.base_price_without_currency = selected_staff_price;
								vm.appointment_step_form_data.selected_service_price = vm.bookingpress_price_with_currency_symbol( selected_staff_price );	
									
								'.$bookingpress_after_add_any_staff_member_price_data.'															
							}

							if( "undefined" != typeof vm.bookingpress_refresh_cart_details ){
								vm.bookingpress_cart_item_calculations();
								vm.bookingpress_refresh_cart_details( false, false, -1, true );
							}

						}
						.bind( this ) )
						.catch( function (error) {
							console.log(error);
						});

					} else {
						let available_staff_id = time_details.available_staffs[0];
						vm.appointment_step_form_data.bookingpress_selected_staff_member_details.selected_staff_member_id = available_staff_id;
						vm.appointment_step_form_data.bookingpress_selected_staff_member_details.staff_member_id = available_staff_id;
						vm.appointment_step_form_data.bookingpress_selected_staff_member_details.is_any_staff_option_selected = 1;
						vm.appointment_step_form_data.selected_staff_member_id = available_staff_id;

						let selected_service = vm.appointment_step_form_data.selected_service;
						let selected_service_data = vm.bookingpress_all_services_data[ selected_service ];
						let service_staff_details = selected_service_data.staff_member_details[ available_staff_id ];
						let selected_staff_price = service_staff_details.bookingpress_service_price;
						vm.appointment_step_form_data.service_price_without_currency = selected_staff_price;
						vm.appointment_step_form_data.base_price_without_currency = selected_staff_price;
						vm.appointment_step_form_data.selected_service_price = vm.bookingpress_price_with_currency_symbol( selected_staff_price );						
						
						'.$bookingpress_after_add_any_staff_member_price_data.'
					}
				}
			';

			return $bookingpress_dynamic_time_select_after;
		}

		function bookingpress_set_available_staffs_to_timeslot( $service_timing_arr, $selected_service, $staff_timings_data, $staff_unavailable_times, $staffwise_booking_data, $shared_quantity ){
			
			$slot_start_time = $service_timing_arr['store_start_time'];
			$slot_end_time = $service_timing_arr['store_end_time'];

			global $wpdb, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_staffmembers_special_day_breaks, $tbl_bookingpress_staff_member_workhours;

			$available_staffs = array();
			$blocked_staffs = array();
			/* echo "<pre>";
			print_r( $staff_timings_data );
			echo "</pre>"; */
			foreach( $staff_timings_data as $staff_id => $staff_times ){
				$staff_start_time = $staff_times['start_time'];
				$staff_end_time = $staff_times['end_time'];

				
				$is_block = false;
				if( !empty( $staff_unavailable_times[$staff_id] ) ){
					foreach( $staff_unavailable_times[$staff_id] as $blocked_time ){
						$blocked_start_time = $blocked_time['start_time'];
						$blocked_end_time = $blocked_time['end_time'];
						if($blocked_end_time == "00:00") {
							$blocked_end_time = "24:00";
						} 
						if( ( $blocked_start_time >= $slot_start_time && $blocked_end_time <= $slot_end_time ) || ( $blocked_start_time < $slot_end_time && $blocked_end_time > $slot_start_time) ){
							$is_block = true;
							$blocked_staffs[] = $staff_id;
							//break;
						}
					}
				}

				if( !$is_block && !empty( $staffwise_booking_data[ $staff_id ] ) ){

					if( 'true' != $shared_quantity ){
						foreach( $staffwise_booking_data[ $staff_id] as $staff_booked_data ){
							$staff_booked_time_start = $staff_booked_data['start_time'];
							$staff_booked_time_end = $staff_booked_data['end_time'];
							if($staff_booked_time_end == "00:00")  {
								$staff_booked_time_end = "24:00";
							}
							if( ( $staff_booked_time_start >= $slot_start_time && $staff_booked_time_end <= $slot_end_time ) || ( $staff_booked_time_start < $slot_end_time && $staff_booked_time_end > $slot_start_time) ){
								if( $staff_booked_time_start != $slot_start_time && $staff_booked_time_end != $slot_end_time ){
									$is_block = true;
									$blocked_staffs[] = $staff_id;
								}
							}
						}
					} else {
						
						
						foreach( $staffwise_booking_data[ $staff_id] as $staff_booked_data ){
							$staff_booked_time_start = $staff_booked_data['start_time'];
							$staff_booked_time_end = $staff_booked_data['end_time'];
							if($staff_booked_time_end == "00:00")  {
								$staff_booked_time_end = "24:00";
							}							
							if( ( $staff_booked_time_start >= $slot_start_time && $staff_booked_time_end <= $slot_end_time ) || ( $staff_booked_time_start < $slot_end_time && $staff_booked_time_end > $slot_start_time) ){
								if( empty( $staff_time_quantity[ $staff_id] ) ){
									$staff_time_quantity[ $staff_id] = $staff_booked_data['quantity'];
								} else {
									$staff_time_quantity[ $staff_id]++;
								}
								 
								$staff_max_capacity = $staff_booked_data['max_capacity'];
								$staff_quantity = $staff_time_quantity[ $staff_id];

								if( ( 1 + $staff_quantity ) > $staff_max_capacity ){
									$is_block = true;
									$blocked_staffs[] = $staff_id;
								}
							}
						}
					}
				}
				//echo $staff_id.' --- '  . $staff_start_time.' -- '. $slot_start_time.' -- '. $staff_end_time.' --- '.$slot_end_time.' ----<br/>';
				if( !$is_block && $staff_start_time <= $slot_start_time && $staff_end_time >= $slot_end_time ){
					$available_staffs[] = $staff_id;
				}

			}
			//echo ' <br/><br/>';
			
			if( empty( $available_staffs ) ){
				$service_timing_arr['disable_timeslot'] = true;
				$service_timing_arr['unavailable_staffs'] = $blocked_staffs;
			} else {
				$service_timing_arr['available_staffs'] = $available_staffs;
			}

			return $service_timing_arr;
		}

		function bpa_enable_share_timeslot_for_any_staffmember_clause( $return_setting_data, $setting_type, $setting_name ){

			if( !empty( $_POST['action'] ) && ( $_POST['action'] == 'bookingpress_front_get_timings' || $_POST['action'] == 'bookingpress_get_disable_date' ) && !empty( $_POST['appointment_data_obj']['any_staff_selected'] ) && 1 == $_POST['appointment_data_obj']['any_staff_selected'] && !empty( $_POST['appointment_data_obj']['available_staffs'] ) && 'general_setting' == $setting_type && 'share_timeslot_between_services' == $setting_name && 'true' != $return_setting_data ){ //phpcs:ignore
				$return_setting_data = 'true';
			}

			return $return_setting_data;
		}

		function bookingpress_calculate_anystaff_breakhour_data( $service_timing_arr, $anystaff_break_data ){

			$slot_start_time = $service_timing_arr['store_start_time'];
			$slot_end_time = $service_timing_arr['store_end_time'];

			if( !empty( $anystaff_break_data ) ){

				foreach( $anystaff_break_data as $blocked_time ){
					$blocked_start_time = $blocked_time['break_start_time'];
					$blocked_end_time = $blocked_time['break_end_time'];
					
					if( ( $blocked_start_time >= $slot_start_time && $blocked_end_time <= $slot_end_time ) || ( $blocked_start_time < $slot_end_time && $blocked_end_time > $slot_start_time) ){
						$service_timing_arr['is_blocked'] = true;
						$service_timing_arr['break_start_time'] = $blocked_start_time;
						$service_timing_arr['break_end_time'] = $blocked_end_time;
					}
				}

            }

			return $service_timing_arr;

		}

		function bookingpress_retreive_timeslots_for_all_staffmembers_func( $service_timings_data, $available_staffs, $selected_service_id, $selected_date, $minimum_time_required, $service_max_capacity, $bookingpress_show_time_as_per_service_duration ){			

			global $wpdb, $BookingPress, $BookingPressPro, $tbl_bookingpress_staff_member_workhours, $tbl_bookingpress_staffmembers_meta, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_staffmembers_daysoff, $tbl_bookingpress_services, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_default_workhours, $tbl_bookingpress_staffmembers_services, $tbl_bookingpress_staffmembers_special_day_breaks, $tbl_bookingpress_service_special_day, $tbl_bookingpress_service_special_day_breaks, $tbl_bookingpress_default_special_day, $tbl_bookingpress_default_special_day_breaks, $tbl_bookingpress_service_workhours, $bookingpress_services, $bookingpress_pro_services, $bookingpress_pro_appointment_bookings, $bookingpress_global_options, $bookingpress_bring_anyone_with_you;

			$booked_appointment_data = wp_cache_get( 'bpa_total_booked_appointment_' . $selected_date );
			
			$total_booked_data = array();
			if( !empty( $booked_appointment_data ) && 'empty_data' != $booked_appointment_data ){
				$total_booked_data = $booked_appointment_data;
			} else if( false === $booked_appointment_data ){

				$where_clause = apply_filters( 'bookingpress_booked_appointment_with_share_timeslot_where_clause_check', $where_clause,$selected_service_id);

				$where_clause .= $wpdb->prepare( ' AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s)', '1', '2' );

				$bpa_appointment_edit_id = !empty( $_POST['appointment_data_obj']['appointment_update_id'] ) ? intval( $_POST['appointment_data_obj']['appointment_update_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

				if( !empty( $bpa_appointment_edit_id ) ){
					$where_clause .= $wpdb->prepare( ' AND bookingpress_appointment_booking_id != %d', $bpa_appointment_edit_id );
				}

				$total_booked_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_date = %s $where_clause", $selected_date), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			}

			if( !function_exists('is_plugin_active') ){
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

			if( is_plugin_active( 'bookingpress-cart/bookingpress-cart.php') ){
				if( !empty( $_REQUEST['appointment_data_obj'] ) ){
					$appointment_data_obj = !is_array( $_REQUEST['appointment_data_obj'] ) ? json_decode( stripslashes_deep( $_REQUEST['appointment_data_obj'] ), true ) : $_REQUEST['appointment_data_obj']; // phpcs:ignore
				}

				if( !empty( $appointment_data_obj['cart_items'] ) ){
					$cart_items = $appointment_data_obj['cart_items'];
					$cart_edit_index = isset( $appointment_data_obj['cart_item_edit_index'] ) ? $appointment_data_obj['cart_item_edit_index'] : '';
					foreach( $cart_items as $ck => $cart_data ){
						if( $cart_data['bookingpress_store_selected_date'] == $selected_date && $cart_edit_index != $ck ){

							$cart_items[ $ck ]['bookingpress_staff_member_id'] = $cart_data['bookingpress_selected_staffmember'];
							$cart_items[ $ck ]['bookingpress_appointment_time'] = $cart_data['bookingpress_store_start_time'];
							$cart_items[ $ck ]['bookingpress_appointment_end_time'] = $cart_data['bookingpress_store_end_time'];
							$cart_items[ $ck ]['bookingpress_selected_extra_members'] = $cart_data['bookingpress_bring_anyone_selected_members'];
							array_push( $total_booked_data, $cart_items[ $ck ] );
						}
					}

				}
			}
			

			$has_buffer_before = false;
			$has_buffer_after = false;
			$buffer_before_time_db = $bookingpress_services->bookingpress_get_service_meta( $selected_service_id, 'before_buffer_time' );
			$buffer_after_time_db = $bookingpress_services->bookingpress_get_service_meta( $selected_service_id, 'after_buffer_time' );

			if( 0 < $buffer_before_time_db ){
				$has_buffer_before = true;
			}

			if( 0 < $buffer_after_time_db ){
				$has_buffer_after = true;
			}

			if( true == $has_buffer_before || true == $has_buffer_after ){
				/** Remove Filter for the buffer calculation after rendering the timeslot and calculate buffer in this function */
				remove_filter( 'bookingpress_modify_service_time_with_buffer', array( $bookingpress_pro_services, 'bookingpress_calculate_buffer_in_timeslot' ), 10 );
				remove_filter( 'bookingpress_modify_service_start_time', array( $bookingpress_pro_appointment_bookings, 'bookingpress_modify_service_start_time_func'), 10);
				remove_filter( 'bookingpress_modify_service_end_time', array( $bookingpress_pro_appointment_bookings, 'bookingpress_modify_service_end_time_func'), 10);
			}

			$shared_quantity = apply_filters('bookingpress_get_shared_capacity_data', 'true' );
			$is_bring_anyone_with_you_activated = $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation();

			$staffwise_booking_data = array();
			
			if( !empty( $total_booked_data ) ){
				foreach( $total_booked_data as $booking_details ){
					
					$booked_staffmember_id = $booking_details['bookingpress_staff_member_id'];
					$booked_staffmember_service = $booking_details['bookingpress_service_id'];

					if( $booked_staffmember_service != $selected_service_id ){
						$max_capacity = 0;
					} else {
						$max_capacity = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_service_capacity FROM `{$tbl_bookingpress_staffmembers_services}` WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $booked_staffmember_id, $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
					}

					if( !empty( $staffwise_booking_data[ $booked_staffmember_id ] ) ){
						//$staff_bkp_data = $staffwise_booking_data[ $booked_staffmember_id ];
						$booking_details_start_time = date('H:i', strtotime( $booking_details['bookingpress_appointment_time'] ) );
						$booking_details_end_time = date('H:i', strtotime( $booking_details['bookingpress_appointment_end_time'] ) );

						foreach( $staffwise_booking_data[ $booked_staffmember_id ] as $staff_book_key => $staff_book_data ){
							
							if( $staff_book_data['start_time'] == $booking_details_start_time && $staff_book_data['end_time'] == $booking_details_end_time ){
								$staffwise_booking_data[ $booked_staffmember_id ][ $staff_book_key ]['quantity'] += $booking_details['bookingpress_selected_extra_members'];
							} else {
								$bkp_staff_id_data = array(
									'start_time' => date('H:i', strtotime( $booking_details['bookingpress_appointment_time'] ) ),
									'end_time' => date('H:i', strtotime( $booking_details['bookingpress_appointment_end_time'] ) ),
									'quantity' => $booking_details['bookingpress_selected_extra_members'],
									'max_capacity' => $max_capacity
								);
								$staffwise_booking_data[ $booked_staffmember_id ][] = $bkp_staff_id_data;
							}

						}

						
					} else {
						
						$bkp_staff_id_data = array(
							'start_time' => date('H:i', strtotime( $booking_details['bookingpress_appointment_time'] ) ),
							'end_time' => date('H:i', strtotime( $booking_details['bookingpress_appointment_end_time'] ) ),
							'quantity' => $booking_details['bookingpress_selected_extra_members'],
							'max_capacity' => $max_capacity
						);
						$staffwise_booking_data[ $booked_staffmember_id ][] = $bkp_staff_id_data;
						
					}
				}
			}

			if( !empty( $staffwise_booking_data ) && ( true == $has_buffer_before || true == $has_buffer_after ) ){
				foreach( $staffwise_booking_data as $staff_id => $staff_booking_data ){
					foreach( $staff_booking_data as $staff_book_data ){
						if( true == $has_buffer_before ){
							$buffer_before_time_unit = $bookingpress_services->bookingpress_get_service_meta( $selected_service_id, 'before_buffer_time_unit' );

							if( 'h' == $buffer_before_time_unit ){
								$buffer_before_time = $buffer_before_time_db * 60;
							} else {
								$buffer_before_time = $buffer_before_time_db;
							}

							$buffer_start_time = date('H:i', strtotime( $staff_book_data['start_time'] . ' -'.$buffer_before_time.' minutes') );
							$buffer_end_time = $staff_book_data['start_time'];

							$staffwise_booking_data[ $staff_id ][] = array(
								'start_time' => $buffer_start_time,
								'end_time' => $buffer_end_time,
								'quantity' => $staff_book_data['quantity'],
								'max_capacity' => $staff_book_data['max_capacity']
							);

						}

						if( true == $has_buffer_after ){
							$buffer_after_time_unit = $bookingpress_services->bookingpress_get_service_meta( $selected_service_id, 'after_buffer_time_unit' );
							if( 'h' == $buffer_after_time_unit ){
								$buffer_after_time = $buffer_after_time_db * 60;
							} else {
								$buffer_after_time = $buffer_after_time_db;
							}

							$buffer_start_time = $staff_book_data['end_time'];
							$buffer_end_time = date('H:i', strtotime( $staff_book_data['end_time'] . ' +'.$buffer_after_time.' minutes') );

							$staffwise_booking_data[ $staff_id ][] = array(
								'start_time' => $buffer_start_time,
								'end_time' => $buffer_end_time,
								'quantity' => $staff_book_data['quantity'],
								'max_capacity' => $staff_book_data['max_capacity']
							);
						}
					}
				}
			}

			$bpa_current_date = date('Y-m-d', current_time('timestamp'));

			$display_slots_in_client_timezone = false;

			$bookingpress_timezone = isset($_POST['client_timezone_offset']) ? sanitize_text_field($_POST['client_timezone_offset']) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings('show_bookingslots_in_client_timezone', 'general_setting');

			$store_current_date = date('Y-m-d', current_time('timestamp'));
			$store_current_time = date('H:i', current_time('timestamp'));

			// 04May 2023 Changes
			$client_timezone_string = !empty($_COOKIE['bookingpress_client_timezone']) ? sanitize_text_field($_COOKIE['bookingpress_client_timezone']) : '';
			if ('true' == $bookingpress_timeslot_display_in_client_timezone && !empty($client_timezone_string)) {
				$client_timezone_offset = $BookingPress->bookingpress_convert_timezone_to_offset($client_timezone_string, $bookingpress_timezone);
				$wordpress_timezone_offset = $BookingPress->bookingpress_convert_timezone_to_offset(wp_timezone_string());
				if ($client_timezone_offset == $wordpress_timezone_offset) {
					$bookingpress_timeslot_display_in_client_timezone = 'false';
				}
			}
			// 04May 2023 Changes

			if (isset($bookingpress_timezone) && '' !== $bookingpress_timezone && !empty($bookingpress_timeslot_display_in_client_timezone) && ($bookingpress_timeslot_display_in_client_timezone == 'true')) {
				$display_slots_in_client_timezone = true;
			}

			if (strtotime($bpa_current_date) > strtotime($selected_date) && false == $display_slots_in_client_timezone) {
				return $service_timings_data;
			}

			$bookingpress_current_time = date('H:i', current_time('timestamp'));
			$bpa_current_datetime = date('Y-m-d H:i:s', current_time('timestamp'));

			$bookingpress_hide_already_booked_slot = $BookingPress->bookingpress_get_customize_settings('hide_already_booked_slot', 'booking_form');
			$bookingpress_hide_already_booked_slot = ($bookingpress_hide_already_booked_slot == 'true') ? 1 : 0;

			$current_day = !empty($selected_date) ? ucfirst(date('l', strtotime($selected_date))) : ucfirst(date('l', current_time('timestamp')));
			$current_date = !empty($selected_date) ? date('Y-m-d', strtotime($selected_date)) : date('Y-m-d', current_time('timestamp'));

			$bpa_current_time = date('H:i', current_time('timestamp'));

			$change_store_date = (!empty($_POST['bpa_change_store_date']) && 'true' == $_POST['bpa_change_store_date']) ? true : false; //phpcs:ignore

			$service_time_duration = $BookingPress->bookingpress_get_default_timeslot_data();
			$service_step_duration_val = $service_time_duration['default_timeslot'];

			if (! empty($selected_service_id) ) {
				$service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $selected_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_services is a table name
				if (! empty($service_data) ) {
					$service_time_duration      = esc_html($service_data['bookingpress_service_duration_val']);
					$service_time_duration_unit = esc_html($service_data['bookingpress_service_duration_unit']);
					if ($service_time_duration_unit == 'h' ) {
						$service_time_duration = $service_time_duration * 60;
					} elseif($service_time_duration_unit == 'd') {           
						$service_time_duration = $service_time_duration * 24 * 60;
					}
					$default_timeslot_step = $service_step_duration_val = $service_time_duration;
				}
			}

			$workhour_data = array();

			$bpa_fetch_updated_slots = false;
            if( isset( $_POST['bpa_fetch_data'] ) && 'true' == $_POST['bpa_fetch_data'] ){ //phpcs:ignore
                $bpa_fetch_updated_slots = true;
            }
			$service_step_duration_val = apply_filters( 'bookingpress_modify_service_timeslot', $service_step_duration_val, $selected_service_id, $service_time_duration_unit, $bpa_fetch_updated_slots );

			$bookingpress_show_time_as_per_service_duration = $BookingPress->bookingpress_get_settings( 'show_time_as_per_service_duration', 'general_setting' );
            if ( ! empty( $bookingpress_show_time_as_per_service_duration ) && $bookingpress_show_time_as_per_service_duration == 'false' ) {
                $bookingpress_default_time_slot = $BookingPress->bookingpress_get_settings( 'default_time_slot', 'general_setting' );
                $default_timeslot_step      = $bookingpress_default_time_slot;
            } else {
				$default_timeslot_step      = $service_step_duration_val;
			}

			$available_staff_ids = $available_staffs;

			$available_staffs_placeholder = 'AND bookingpress_staffmember_id IN (';
			$available_staffs_placeholder .= rtrim( str_repeat( '%s,', count( $available_staffs ) ), ',' );
			$available_staffs_placeholder .= ')';
			
			array_unshift( $available_staffs, $available_staffs_placeholder );

			$staff_query_where = call_user_func_array(array( $wpdb, 'prepare' ), $available_staffs );

			$start_time_data = array();
			$end_time_data = array();

			$staff_timings_data = array();
			$staff_unavailable_times = array();
			$break_hour_timings = array();

			$exclude_staff_special_days = array();
			$exclude_staff_workhours = array();
			$exclude_staff_general_hours = array();
			$exclude_service_special_days = array();
			$exclude_service_workhours = array();
			$exclude_default_special_days = array();

			$staff_with_holidays = array();

			/** Check for staff's holiday */
			$bpa_get_staff_holiday = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_id, bookingpress_staffmember_daysoff_date, bookingpress_staffmember_daysoff_repeat FROM {$tbl_bookingpress_staffmembers_daysoff} WHERE bookingpress_staffmember_daysoff_date = %s OR bookingpress_staffmember_daysoff_repeat = %d {$staff_query_where}", $selected_date, 1 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_daysoff is a table name.

			if( !empty( $bpa_get_staff_holiday ) ){
				foreach( $bpa_get_staff_holiday as $staff_holiday_data ){
					$hsf_id = $staff_holiday_data->bookingpress_staffmember_id;
					$hsf_date = $staff_holiday_data->bookingpress_staffmember_daysoff_date;
					$hsf_is_repeat = $staff_holiday_data->bookingpress_staffmember_daysoff_repeat;

					if( 1 == $hsf_is_repeat ){
						/** Check if the holiday is repeated and placed in the selected date */
						$current_date_without_year = date('m-d', strtotime( $selected_date ) );
						$holiday_date_without_year = date('m-d', strtotime( $hsf_date ) );
						if( $holiday_date_without_year == $current_date_without_year ){
							$staff_with_holidays[] = $hsf_id;	
						}
					} else {
						$staff_with_holidays[] = $hsf_id;
					}
				}
			}

			if( !empty( $staff_with_holidays ) ){
				foreach( $available_staff_ids as $key => $stf_id ){
					if( in_array( $stf_id, $staff_with_holidays ) ){
						unset( $available_staff_ids[ $key ] );
					}
				}
			}
			
			$available_staff_ids = array_values( $available_staff_ids );

			/** Check for location details */
			$bpa_staff_on_multiple_locations = $BookingPress->bookingpress_get_settings('allow_staffmember_to_serve_multiple_locations', 'general_setting');
			$bookingpress_location_id = !empty($_POST['appointment_data_obj']['selected_location']) ? intval($_POST['appointment_data_obj']['selected_location']) : 0; // phpcs:ignore
			if( is_plugin_active( 'bookingpress-location/bookingpress-location.php' ) && !empty( $bookingpress_location_id ) && 'true' == $bpa_staff_on_multiple_locations ){
				global $bookingpress_location_booking_form, $tbl_bookingpress_locations_service_workhours, $tbl_bookingpress_locations_staff_workhours, $tbl_bookingpress_locations_service_special_days, $tbl_bookingpress_locations_staff_special_days;

				$exclude_location_staff_workhours = array();
				remove_filter( 'bookingpress_retrieve_pro_modules_timeslots', array( $bookingpress_location_booking_form, 'bookingpress_retrieve_location_staffmember_timings_func' ), 6 );

				$loc_special_query_where = $wpdb->prepare( " AND ( bookingpress_location_staff_special_day_service_id IS NULL OR bookingpress_location_staff_special_day_service_id = %d OR bookingpress_location_staff_special_day_service_id LIKE %s OR bookingpress_location_staff_special_day_service_id LIKE %s OR bookingpress_location_staff_special_day_service_id LIKE %s )", $selected_service_id, "$selected_service_id,%", "%,$selected_service_id", "%,$selected_service_id,%" ); //phpcs:ignore

				/** Check for location staff's special day details */
				$bookingpress_location_staff_special_days = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_locations_staff_special_days} WHERE bookingpress_location_id = %d AND bookingpress_location_staff_special_day_has_break = %d AND bookingpress_location_staff_special_day_start_date <= %s AND bookingpress_location_staff_special_day_end_date >= %s {$staff_query_where} {$loc_special_query_where}", $bookingpress_location_id, 0, $selected_date, $selected_date ) ); //phpcs:ignore

				if( !empty( $bookingpress_location_staff_special_days ) ){
					foreach( $bookingpress_location_staff_special_days as $loc_staff_sd_data ){
						$loc_sp_staff_id = $loc_staff_sd_data->bookingpress_staffmember_id;
						
						$loc_sp_staff_start_time = $loc_staff_sd_data->bookingpress_location_staff_special_day_start_time;
						$loc_sp_staff_end_time = $loc_staff_sd_data->bookingpress_location_staff_special_day_end_time;

						if( in_array( $loc_sp_staff_id, $staff_with_holidays ) ){
							continue;
						}

						/** Check for special day breaks */
						$bookingpress_location_staff_special_day_breaks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_locations_staff_special_days} WHERE bookingpress_location_id = %d AND bookingpress_location_staff_special_day_has_break = %d AND bookingpress_location_staff_special_day_start_date <= %s AND bookingpress_location_staff_special_day_end_date >= %s AND bookingpress_staffmember_id = %d {$loc_special_query_where}", $bookingpress_location_id, 1, $selected_date, $selected_date, $loc_sp_staff_id ) ); //phpcs:ignore

						if( !empty( $bookingpress_location_staff_special_day_breaks ) ){
							foreach( $bookingpress_location_staff_special_day_breaks as $staff_loc_sp_break_timings ){
								$staff_unavailable_times[ $loc_sp_staff_id ][] = array(
									'start_time' => date('H:i', strtotime( $staff_loc_sp_break_timings->bookingpress_location_staff_special_day_break_start_time ) ),
									'end_time' => date('H:i', strtotime( $staff_loc_sp_break_timings->bookingpress_location_staff_special_day_break_end_time ) )
								);

							}
						}

						$loc_sp_staff_start_time = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_start_time_func( $loc_sp_staff_start_time, $selected_service_id );
						$start_time_data[] = $loc_sp_staff_start_time;
						$loc_sp_staff_end_time = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_end_time_func( $loc_sp_staff_end_time, $selected_service_id );
						$end_time_data[] = $loc_sp_staff_end_time;

						$staff_timings_data[ $loc_sp_staff_id ] = array(
							'start_time' => date('H:i', strtotime( $loc_sp_staff_start_time ) ),
							'end_time' => date('H:i', strtotime( $loc_sp_staff_end_time ) )
						);

						if( '00:00' == $staff_timings_data[ $loc_sp_staff_id ]['end_time'] ){
							$staff_timings_data[ $loc_sp_staff_id ]['end_time'] = '24:00';
						}

						$exclude_location_staff_workhours[] = $loc_sp_staff_id;
						$exclude_staff_special_days[] = $loc_sp_staff_id;
						$exclude_staff_general_hours[] = $loc_sp_staff_id;
						$exclude_staff_workhours[] = $loc_sp_staff_id;
						$exclude_staff_general_hours[] = $loc_sp_staff_id;
						$exclude_service_special_days[] = $loc_sp_staff_id;
						$exclude_service_workhours[] = $loc_sp_staff_id;
						$exclude_default_special_days[] = $loc_sp_staff_id;
					}
				}

				/** Staff member location wise working hour start */

				$bookingpress_location_staff_default_workhours = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_locations_staff_workhours} WHERE bookingpress_location_id = %d AND bookingpress_location_staff_workhour_is_break = 0 AND bookingpress_location_staff_workday_key = %s {$staff_query_where}", $bookingpress_location_id, $current_day  ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_locations_staff_workhours is table name defined globally.

				if( !empty( $bookingpress_location_staff_default_workhours ) ){
					foreach( $bookingpress_location_staff_default_workhours as $staff_location_working_data ){
						$loc_staffmember_id = $staff_location_working_data->bookingpress_staffmember_id;
						$loc_staff_start_time = $staff_location_working_data->bookingpress_location_staff_workhour_start_time;
						$loc_staff_end_time = $staff_location_working_data->bookingpress_location_staff_workhour_end_time;

						if( in_array( $loc_staffmember_id, $staff_with_holidays) || in_array( $loc_staffmember_id, $exclude_location_staff_workhours ) ){
							continue;
						}

						$exclude_staff_special_days[] = $loc_staffmember_id;
						$exclude_staff_general_hours[] = $loc_staffmember_id;
						$exclude_staff_workhours[] = $loc_staffmember_id;
						$exclude_staff_general_hours[] = $loc_staffmember_id;
						$exclude_service_special_days[] = $loc_staffmember_id;
						$exclude_service_workhours[] = $loc_staffmember_id;
						$exclude_default_special_days[] = $loc_staffmember_id;

						if( '' == $loc_staff_start_time || NULL == $loc_staff_start_time ){
							continue;
						}

						/** Check for working hour breaks */
						$bookingpress_location_staff_default_workhour_breaks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_locations_staff_workhours} WHERE bookingpress_location_id = %d AND bookingpress_location_staff_workhour_is_break = 1 AND bookingpress_location_staff_workday_key = %s AND bookingpress_staffmember_id = %d", $bookingpress_location_id, $current_day, $loc_staffmember_id  ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_locations_staff_workhours is table name defined globally.
						
						if( !empty( $bookingpress_location_staff_default_workhour_breaks ) ){
							foreach( $bookingpress_location_staff_default_workhour_breaks as $staff_break_workhour_data ){
								$sf_break_start_time = $staff_break_workhour_data->bookingpress_location_staff_workhour_start_time;
								$sf_break_end_time = $staff_break_workhour_data->bookingpress_location_staff_workhour_end_time;
	
								$staff_unavailable_times[ $loc_staffmember_id ][] = array(
									'start_time' => date('H:i', strtotime( $sf_break_start_time ) ),
									'end_time' => date('H:i', strtotime( $sf_break_end_time ) ),
								);
							}
						}

						$loc_staff_start_time = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_start_time_func( $loc_staff_start_time, $selected_service_id );
						$start_time_data[] = $loc_staff_start_time;
						$loc_staff_end_time = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_end_time_func( $loc_staff_end_time, $selected_service_id );
						$end_time_data[] = $loc_staff_end_time;

						$staff_timings_data[ $loc_staffmember_id ] = array(
							'start_time' => date('H:i', strtotime( $loc_staff_start_time ) ),
							'end_time' => date('H:i', strtotime( $loc_staff_end_time ) )
						);

						if( '00:00' == $staff_timings_data[ $loc_staffmember_id ]['end_time'] ){
							$staff_timings_data[ $loc_staffmember_id ]['end_time'] = '24:00';
						}


					}
				}

			}
			
			
			
			/** Special Day code for the staff member */
			$select_staff_special_day_start_end_time = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_special_day_id,bookingpress_staffmember_id, bookingpress_special_day_service_id, bookingpress_special_day_start_time as bpa_staff_start_time, bookingpress_special_day_end_time as bpa_staff_end_time FROM {$tbl_bookingpress_staffmembers_special_day} WHERE bookingpress_special_day_start_date <= %s AND bookingpress_special_day_start_time IS NOT NULL AND bookingpress_special_day_end_date >= %s {$staff_query_where}", $selected_date .' 00:00:00', $selected_date.' 00:00:00' ) ); //phpcs:ignore

			if( !empty( $select_staff_special_day_start_end_time ) ){
				foreach( $select_staff_special_day_start_end_time as $staff_special_day_timings ){

					$special_day_staff = $staff_special_day_timings->bookingpress_staffmember_id;					

					if( in_array( $special_day_staff, $staff_with_holidays ) || in_array( $special_day_staff, $exclude_staff_special_days) ){
						$exclude_staff_workhours[] = $special_day_staff;
						continue;
					}

					$staff_special_day_service_id = $staff_special_day_timings->bookingpress_special_day_service_id;
					$staff_special_day_id = $staff_special_day_timings->bookingpress_staffmember_special_day_id;
					

					if( empty( $staff_special_day_service_id ) ){

						$staff_special_day_start = $staff_special_day_timings->bpa_staff_start_time;
						$staff_special_day_end = $staff_special_day_timings->bpa_staff_end_time;

						$staff_timings_data[ $special_day_staff ] = array(
							'start_time' => date('H:i', strtotime( $staff_special_day_start ) ),
							'end_time' => date('H:i', strtotime( $staff_special_day_end ) ) 
						);

						if( '00:00' == $staff_timings_data[ $special_day_staff ]['end_time'] ){
							$staff_timings_data[ $special_day_staff ]['end_time'] = '24:00';
						}
					
						$exclude_staff_general_hours[] = $special_day_staff;
						$exclude_staff_workhours[] = $special_day_staff;
						$exclude_service_special_days[] = $special_day_staff;
						$exclude_service_workhours[] = $special_day_staff;
						$exclude_default_special_days[] = $special_day_staff;

						$start_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_start_time_func( $staff_special_day_start, $selected_service_id );
						$end_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_end_time_func( $staff_special_day_end, $selected_service_id );

						/** Check for special day breaks */
						$staff_special_day_breaks = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_special_day_break_start_time, bookingpress_special_day_break_end_time FROM {$tbl_bookingpress_staffmembers_special_day_breaks} WHERE bookingpress_special_day_id = %d", $staff_special_day_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day_breaks is a table name.

						if( !empty( $staff_special_day_breaks ) ){
							foreach( $staff_special_day_breaks as $staff_sp_break_timings ){
								$staff_unavailable_times[ $special_day_staff ][] = array(
									'start_time' => date('H:i', strtotime( $staff_sp_break_timings->bookingpress_special_day_break_start_time ) ),
									'end_time' => date('H:i', strtotime( $staff_sp_break_timings->bookingpress_special_day_break_end_time ) )									
								);

							}
						}

					} else {

						$avl_services = explode( ',', $staff_special_day_service_id );
						if( in_array( $selected_service_id, $avl_services ) ){
							$staff_special_day_start = $staff_special_day_timings->bpa_staff_start_time;
							$staff_special_day_end = $staff_special_day_timings->bpa_staff_end_time;

							$staff_timings_data[ $special_day_staff ] = array(
								'start_time' => date('H:i', strtotime( $staff_special_day_start ) ),
								'end_time' => date('H:i', strtotime( $staff_special_day_end ) )
							);

							if( '00:00' == $staff_timings_data[ $special_day_staff ]['end_time'] ){
								$staff_timings_data[ $special_day_staff ]['end_time'] = '24:00';
							}
						
							$exclude_staff_general_hours[] = $special_day_staff;
							$exclude_staff_workhours[] = $special_day_staff;
							$exclude_service_special_days[] = $special_day_staff;
							$exclude_service_workhours[] = $special_day_staff;
							$exclude_default_special_days[] = $special_day_staff;

							$start_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_start_time_func( $staff_special_day_start, $selected_service_id );
							$end_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_end_time_func( $staff_special_day_end, $selected_service_id );

							/** Check for special day breaks */
							$staff_special_day_breaks = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_special_day_break_start_time, bookingpress_special_day_break_end_time FROM {$tbl_bookingpress_staffmembers_special_day_breaks} WHERE bookingpress_special_day_id = %d", $staff_special_day_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day_breaks is a table name.

							if( !empty( $staff_special_day_breaks ) ){
								foreach( $staff_special_day_breaks as $staff_sp_break_timings ){
									$staff_unavailable_times[ $special_day_staff ][] = array(
										'start_time' => date('H:i', strtotime( $staff_sp_break_timings['start_time'] ) ),
										'end_time' => date('H:i', strtotime( $staff_sp_break_timings['end_time'] ) )
									);

								}
							}
						}

					}
				}
			}

			

			/** Working hour code for Staff member */
			$select_staff_working_start_end_time = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_id, bookingpress_staffmember_workhours_start_time as bpa_staff_start_time, bookingpress_staffmember_workhours_end_time as bpa_staff_end_time FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_workhours_is_break = %d AND bookingpress_staffmember_workday_key = %s {$staff_query_where}", 0, $current_day ) ); //phpcs:ignore

			if( !empty( $select_staff_working_start_end_time ) ){
				foreach( $select_staff_working_start_end_time as $staff_work_timings ){
					$staff_start_time = $staff_work_timings->bpa_staff_start_time;
					$staff_end_time = $staff_work_timings->bpa_staff_end_time;

					$staff_id = $staff_work_timings->bookingpress_staffmember_id;

					if( in_array( $staff_id, $exclude_staff_workhours) ){
						continue;
					}

					if( empty( $staff_start_time ) || in_array( $staff_id, $staff_with_holidays ) ){
						$exclude_staff_general_hours[] = $staff_id;
						if( empty( $staff_start_time ) ){
							$exclude_staff_workhours[] = $staff_id;
							$exclude_staff_general_hours[] = $staff_id;
							$exclude_service_special_days[] = $staff_id;
							$exclude_service_workhours[] = $staff_id;
							$exclude_default_special_days[] = $staff_id;
						}
						continue;
					}

					/** Check if break is there */
					$staff_workhours_breaks = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_workhours_start_time as bpa_staff_start_time, bookingpress_staffmember_workhours_end_time as bpa_staff_end_time FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_workhours_is_break = %d AND bookingpress_staffmember_workhours_start_time IS NOT NULL AND bookingpress_staffmember_workday_key = %s AND bookingpress_staffmember_id = %d", 1, $current_day, $staff_id ) ); //phpcs:ignore

					if( !empty( $staff_workhours_breaks ) ){
						foreach( $staff_workhours_breaks as $staff_break_workhour_data ){
							$sf_break_start_time = $staff_break_workhour_data->bpa_staff_start_time;
							$sf_break_end_time = $staff_break_workhour_data->bpa_staff_end_time;

							$staff_unavailable_times[ $staff_id ][] = array(
								'start_time' => date('H:i', strtotime( $sf_break_start_time ) ),
								'end_time' => date('H:i', strtotime( $sf_break_end_time ) ),
							);

						}
					}

					$start_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_start_time_func( $staff_start_time, $selected_service_id );
					$end_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_end_time_func( $staff_end_time, $selected_service_id );

					$staff_timings_data[ $staff_id ] = array(
						'start_time' => date('H:i', strtotime( $staff_start_time ) ),
						'end_time' => date('H:i', strtotime( $staff_end_time ) )
					);

					if( '00:00' == $staff_timings_data[ $staff_id ]['end_time'] ){
						$staff_timings_data[ $staff_id ]['end_time'] = '24:00';
					}
				
					$exclude_staff_general_hours[] = $staff_id;
					$exclude_service_special_days[] = $staff_id;
					$exclude_service_workhours[] = $staff_id;
					$exclude_default_special_days[] = $staff_id;

				}
			}

			/** Special Day code for the service */
			$service_special_days = $wpdb->get_results( $wpdb->prepare("SELECT bookingpress_service_special_day_id, bookingpress_special_day_start_time as bpa_staff_start_time, bookingpress_special_day_end_time as bpa_staff_end_time FROM {$tbl_bookingpress_service_special_day} WHERE bookingpress_special_day_start_date <= %s AND bookingpress_special_day_end_date >= %s AND bookingpress_special_day_start_time IS NOT NULL AND bookingpress_service_id = %d", $selected_date . ' 00:00:00', $selected_date .' 00:00:00', $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_special_day is a table name.

			if( !empty( $service_special_days ) ){
				foreach( $service_special_days as $sv_special_day_data ){
					$sv_sp_day_id = $sv_special_day_data->bookingpress_service_special_day_id;
					
					foreach( $available_staff_ids as $staff_id ){

						if( !in_array( $staff_id, $exclude_service_special_days ) ){

							/** Check for the break timings */
							$service_special_day_breaks = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_special_day_break_start_time, bookingpress_special_day_break_end_time FROM {$tbl_bookingpress_service_special_day_breaks} WHERE bookingpress_special_day_id = %d", $sv_sp_day_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_special_day_breaks is a table name.

							if( !empty( $service_special_day_breaks ) ){
								foreach( $service_special_day_breaks as $sv_sp_day_break_data ){
									$staff_unavailable_times[ $staff_id ][] = array(
										'start_time' => date('H:i', strtotime( $sv_sp_day_break_data->bookingpress_special_day_break_start_time ) ),
										'end_time' => date('H:i', strtotime( $sv_sp_day_break_data->bookingpress_special_day_break_end_time ) ),
									);

									$break_hour_timings[] = array(
										'break_start_time' => date('H:i', strtotime( $sv_sp_day_break_data->bookingpress_special_day_break_start_time ) ),
										'break_end_time' => date('H:i', strtotime( $sv_sp_day_break_data->bookingpress_special_day_break_end_time ) )
									);
								}
							}

							$staff_timings_data[ $staff_id ] = array(
								'start_time' => date( 'H:i', strtotime( $sv_special_day_data->bpa_staff_start_time ) ),
								'end_time' => date( 'H:i', strtotime( $sv_special_day_data->bpa_staff_end_time ) )
							);

							if( '00:00' == $staff_timings_data[ $staff_id ]['end_time'] ){
								$staff_timings_data[ $staff_id ]['end_time'] = '24:00';
							}

							$start_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_start_time_func( $sv_special_day_data->bpa_staff_start_time, $selected_service_id );
							$end_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_end_time_func( $sv_special_day_data->bpa_staff_end_time, $selected_service_id );

							$exclude_service_workhours[] = $staff_id;
							$exclude_default_special_days[] = $staff_id;
							$exclude_staff_general_hours[] = $staff_id;
						}
					}
				}
			}

			/** Working Hour code for the service */
			$service_workhours = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_service_workhours_start_time as bpa_staff_start_time, bookingpress_service_workhours_end_time as bpa_staff_end_time FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_workhours_is_break = %d AND bookingpress_service_workday_key = %s AND bookingpress_service_id = %d", 0, strtolower( $current_day ), $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is a table name.
		
			if( !empty( $service_workhours ) ){
				$sv_start_time = $service_workhours->bpa_staff_start_time;
				$sv_end_time = $service_workhours->bpa_staff_end_time;

				/** Check if break is there */
				$service_workhours_breaks = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_service_workhours_start_time as bpa_staff_start_time, bookingpress_service_workhours_end_time as bpa_staff_end_time FROM {$tbl_bookingpress_service_workhours} WHERE bookingpress_service_workhours_is_break = %d AND bookingpress_service_workhours_start_time IS NOT NULL AND bookingpress_service_workday_key = %s AND bookingpress_service_id = %d", 1, strtolower( $current_day ), $selected_service_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is a table name.
				
				foreach( $available_staff_ids as $staff_id ){


					if( empty( $sv_start_time ) || in_array( $staff_id, $exclude_service_workhours ) ){
						$exclude_default_special_days[] = $staff_id;
						$exclude_staff_general_hours[] = $staff_id;
						continue;
					}

					if( !in_array( $staff_id, $exclude_service_workhours ) ){

						if( !empty( $service_workhours_breaks ) ){
							foreach( $service_workhours_breaks as $service_wh_break_data ){

								$sv_break_start = $service_wh_break_data->bpa_staff_start_time;
								$sv_break_end = $service_wh_break_data->bpa_staff_end_time;
								
								$staff_unavailable_times[ $staff_id ][] = array(
									'start_time' => date('H:i', strtotime( $sv_break_start ) ),
									'end_time' => date('H:i', strtotime( $sv_break_end ) )
								);

								$break_hour_timings[] = array(
									'break_start_time' => date('H:i', strtotime( $sv_break_start ) ),
									'break_end_time' => date('H:i', strtotime( $sv_break_end ) )
								);
							}
						}

						$staff_timings_data[ $staff_id ] = array(
							'start_time' => date('H:i', strtotime( $sv_start_time ) ),
							'end_time' => date('H:i', strtotime( $sv_end_time ))
						);

						if( '00:00' == $staff_timings_data[ $staff_id ]['end_time'] ){
							$staff_timings_data[ $staff_id ]['end_time'] = '24:00';
						}

						$start_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_start_time_func( $sv_start_time, $selected_service_id );
						$end_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_end_time_func( $sv_end_time, $selected_service_id );

						$exclude_default_special_days[] = $staff_id;
						$exclude_staff_general_hours[] = $staff_id;
					}
				}			
			}
			
			/** Special Day code for the general */

			$default_special_days = $wpdb->get_results( $wpdb->prepare("SELECT bookingpress_special_day_id, bookingpress_special_day_start_time as bpa_staff_start_time, bookingpress_special_day_end_time as bpa_staff_end_time FROM {$tbl_bookingpress_default_special_day} WHERE bookingpress_special_day_start_date <= %s AND bookingpress_special_day_end_date >= %s AND bookingpress_special_day_start_time IS NOT NULL", $selected_date . ' 00:00:00', $selected_date .' 00:00:00' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day is a table name.

			if( !empty( $default_special_days ) ){
				foreach( $default_special_days as $default_special_day_data ){
					$default_sp_day_id = $default_special_day_data->bookingpress_special_day_id;
					
					foreach( $available_staff_ids as $staff_id ){

						if( !in_array( $staff_id, $exclude_default_special_days ) ){

							$start_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_start_time_func( $default_special_day_data->bpa_staff_start_time, $selected_service_id );
							$end_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_end_time_func( $default_special_day_data->bpa_staff_end_time, $selected_service_id );

							/** Check for the break timings */
							$default_special_day_breaks = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_special_day_break_start_time, bookingpress_special_day_break_end_time FROM {$tbl_bookingpress_default_special_day_breaks} WHERE bookingpress_special_day_id = %d", $default_sp_day_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day_breaks is a table name.

							if( !empty( $default_special_day_breaks ) ){
								foreach( $default_special_day_breaks as $sv_sp_day_break_data ){
									$staff_unavailable_times[ $staff_id ][] = array(
										'start_time' => date('H:i', strtotime( $sv_sp_day_break_data->bookingpress_special_day_break_start_time ) ),
										'end_time' => date('H:i', strtotime( $sv_sp_day_break_data->bookingpress_special_day_break_end_time ) ),
									);

									$break_hour_timings[] = array(
										'break_start_time' => date('H:i', strtotime( $sv_sp_day_break_data->bookingpress_special_day_break_start_time ) ),
										'break_end_time' => date('H:i', strtotime( $sv_sp_day_break_data->bookingpress_special_day_break_end_time ) )
									);
								}
							}

							$staff_timings_data[ $staff_id ] = array(
								'start_time' => date( 'H:i', strtotime( $default_special_day_data->bpa_staff_start_time ) ),
								'end_time' => date( 'H:i', strtotime( $default_special_day_data->bpa_staff_end_time ) )
							);
							if( '00:00' == $staff_timings_data[ $staff_id ]['end_time'] ){
								$staff_timings_data[ $staff_id ]['end_time'] = '24:00';
							}

							$exclude_staff_general_hours[] = $staff_id;
						}
					}
				}
			}			

			/** Working Hour code for the general */
			$default_workhours = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_start_time as bpa_staff_start_time, bookingpress_end_time as bpa_staff_end_time FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_is_break = %d AND bookingpress_workday_key = %s", 0, strtolower( $current_day ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is a table name.
			
			if( !empty( $default_workhours->bpa_staff_start_time ) ){

				/** Check if break is there */
				$default_workhours_breaks = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_start_time as bpa_staff_start_time, bookingpress_end_time as bpa_staff_end_time FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_is_break = %d AND bookingpress_start_time IS NOT NULL AND bookingpress_workday_key = %s", 1, strtolower( $current_day ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is a table name.

				foreach( $available_staff_ids as $staff_id ){

					if( !in_array( $staff_id, $exclude_staff_general_hours ) ){

						$start_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_start_time_func( $default_workhours->bpa_staff_start_time, $selected_service_id );
						$end_time_data[] = $bookingpress_pro_appointment_bookings->bookingpress_modify_service_end_time_func( $default_workhours->bpa_staff_end_time, $selected_service_id );
						
						if( !empty( $default_workhours_breaks ) ){
							foreach( $default_workhours_breaks as $key => $default_wh_data ){

								$default_break_start = $default_wh_data->bpa_staff_start_time;
								$default_break_end = $default_wh_data->bpa_staff_end_time;
								
								$staff_unavailable_times[ $staff_id ][] = array(
									'start_time' => date('H:i', strtotime( $default_break_start ) ),
									'end_time' => date('H:i', strtotime( $default_break_end ) )
								);

								$break_hour_timings[] = array(
									'break_start_time' => date('H:i', strtotime( $default_break_start ) ),
									'break_end_time' => date('H:i', strtotime( $default_break_end ) )
								);
							}
						}

						$staff_timings_data[ $staff_id ] = array(
							'start_time' => date('H:i', strtotime( $default_workhours->bpa_staff_start_time ) ),
							'end_time' => date('H:i', strtotime( $default_workhours->bpa_staff_end_time ) ),
						);

						if( '00:00' == $staff_timings_data[ $staff_id ]['end_time'] ){
							$staff_timings_data[ $staff_id ]['end_time'] = '24:00';
						}
						
					}
				}
			}

			if( empty( $available_staff_ids ) || empty( $start_time_data ) || empty( $end_time_data ) ){
				$service_timings_data['is_daysoff'] = true;
				return $service_timings_data;
			}

			/** Fetch direct option for share timeslots instead of getting it via function */
			$bookingpress_general_setting_options                    = $bookingpress_global_options->bookingpress_global_options();
            $general_settings = $bookingpress_general_setting_options['general_settings'];
			
			$share_timeslot = $general_settings['general_setting']['share_timeslot_between_services'];

			if( 'true' == $share_timeslot){

				if( !empty( $staffwise_booking_data ) ){
					foreach( $staffwise_booking_data as $staff_id => $staff_booking_data ) {
						foreach( $available_staff_ids as $av_staff_id ){
							if( $av_staff_id != $staff_id ){
								if( empty( $staff_unavailable_times[ $av_staff_id ] ) ){
									$staff_unavailable_times[ $av_staff_id ] = array();
								}
								foreach( $staff_booking_data as $bkp_data ){
									$bkp_new_data = $bkp_data;
									$bkp_new_data['quantity'] = 1;
									$bkp_new_data['max_capacity'] = 1;
									$staff_unavailable_times[ $av_staff_id ][] = $bkp_new_data;
								}
							} else {
								foreach( $staff_booking_data as $bkp_data ){
									if( $bkp_data['max_capacity'] <= $bkp_data['quantity'] ){
										if( empty( $staff_unavailable_times[ $av_staff_id ] ) ){
											$staff_unavailable_times[ $av_staff_id ] = array();
										}
										$staff_unavailable_times[ $av_staff_id ][] = $bkp_data;
									}
								}
							}
						}
					}
				}

			} else {

				foreach( $available_staff_ids as $staff_id ){
					
					if( !empty( $staffwise_booking_data[ $staff_id] ) ){
						foreach( $staffwise_booking_data[ $staff_id ] as $staff_booking_data ){
							if( $staff_booking_data['max_capacity'] <= $staff_booking_data['quantity'] ){
								if( empty( $staff_unavailable_times[ $staff_id ] ) ){
									$staff_unavailable_times[ $staff_id ] = array();
								}
								$staff_unavailable_times[ $staff_id ][] = $staff_booking_data;
							}
						}
					}
				}
			}

			/* add filter for the staff member */
			$staff_unavailable_times = apply_filters( 'bookingpress_check_unavailable_time_outside', $staff_unavailable_times, $selected_date, $available_staff_ids );

			$staffmember_current_time = $service_start_time = apply_filters('bookingpress_modify_service_start_time', date('H:i', strtotime( min( $start_time_data ) )), $selected_service_id);

            $staffmember_end_time = apply_filters('bookingpress_modify_service_end_time', date('H:i', strtotime( max($end_time_data ) ) ), $selected_service_id);

			if ('00:00' == $staffmember_end_time) {
                $staffmember_end_time = '24:00';
            }

			if ($service_start_time != null && $staffmember_end_time != null) {

				while ($staffmember_current_time <= $staffmember_end_time) {
                    if ($staffmember_current_time > $staffmember_end_time) {
                        break;
                    }

                    $service_tmp_date_time = $selected_date . ' ' . $staffmember_current_time;
                    $service_tmp_end_time = date('Y-m-d', (strtotime($selected_date . ' ' . $staffmember_current_time) + ($service_step_duration_val * 60)));

                    if ($service_tmp_end_time > $selected_date) {
                        if (1440 < $service_step_duration_val && $service_time_duration_unit != 'd') {
                            break;
                        }
                    }

                    $service_tmp_current_time = $staffmember_current_time;

                    if ($staffmember_current_time == '00:00') {
                        $staffmember_current_time = date('H:i', strtotime($staffmember_current_time) + ($service_step_duration_val * 60));
                    } else {
                        $service_tmp_time_obj = new DateTime($selected_date . ' ' . $staffmember_current_time);
                        $service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
                        $staffmember_current_time = $service_tmp_time_obj->format('H:i');
                        $service_current_date = $service_tmp_time_obj->format('Y-m-d');
                        if ($service_current_date > $selected_date) {
                            if ($staffmember_end_time == '24:00' && strtotime($service_current_date . ' ' . $staffmember_current_time) > strtotime($service_current_date . ' 00:00')) {
                                break;
                            }
                        }
                    }

                    if ($staffmember_current_time < $service_start_time || $staffmember_current_time == $service_start_time) {
                        $staffmember_current_time = $staffmember_end_time;
                    }

                    $break_start_time = '';
                    $break_end_time = '';
                    /** Staff member work hour break time logic start */

                    /* $staffmember_workhour_breaks_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_staffmember_workhours_start_time, bookingpress_staffmember_workhours_end_time FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_workday_key = %s AND bookingpress_staffmember_workhours_is_break = %d AND bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_start_time BETWEEN %s AND %s", ucfirst($current_day), 1, $bookingpress_selected_staffmember_id, $service_tmp_current_time, $staffmember_current_time)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is table name.

                    if (!empty($staffmember_workhour_breaks_data)) {
                        $break_start_time = date('H:i', strtotime($staffmember_workhour_breaks_data->bookingpress_staffmember_workhours_start_time));
                        $break_end_time = date('H:i', strtotime($staffmember_workhour_breaks_data->bookingpress_staffmember_workhours_end_time));
                        $staffmember_current_time = $break_start_time;
                    } */

                    /** Staff member work hour break time logic end */

                    $bookingpress_timediff_in_minutes = round(abs(strtotime($staffmember_current_time) - strtotime($service_tmp_current_time)) / 60, 2);
                    $is_booked_for_minimum = false;
                    if ('disabled' != $minimum_time_required) {
                        $bookingpress_slot_start_datetime = $selected_date . ' ' . $service_tmp_current_time . ':00';
                        $bookingpress_slot_start_time_timestamp = strtotime($bookingpress_slot_start_datetime);
                        $bookingpress_time_diff = round(abs(current_time('timestamp') - $bookingpress_slot_start_time_timestamp) / 60, 2);

                        if ($bookingpress_time_diff <= $minimum_time_required) {
                            $is_booked_for_minimum = true;
                        }
                    }

					//echo $service_tmp_current_time.' -- '. $staffmember_current_time.' ---<br/><br/>';

                    if ($break_start_time != $service_tmp_current_time && $bookingpress_timediff_in_minutes >= $service_step_duration_val && $staffmember_current_time <= $staffmember_end_time) {
                        if ($bpa_current_date == $selected_date) {
							if ($service_tmp_current_time > $bpa_current_time && !$is_booked_for_minimum) {
								
                                $service_timing_arr = array(
                                    'start_time' => $service_tmp_current_time,
                                    'end_time' => $staffmember_current_time,
                                    'break_start_time' => $break_start_time,
                                    'break_end_time' => $break_end_time,
                                    'store_start_time' => $service_tmp_current_time,
                                    'store_end_time' => $staffmember_current_time,
                                    'is_booked' => 0,
                                    'store_service_date' => $selected_date,
                                    'max_capacity' => $service_max_capacity,
                                    'total_booked' => 0,
                                );

								if( !empty( $break_hour_timings ) ){
									$service_timing_arr = apply_filters( 'bpa_calculat_anystaff_breakhours_data', $service_timing_arr, $break_hour_timings );
								}

								if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
									$staffmember_current_time = $service_timing_arr['break_end_time'];
									continue;
								}

								$service_timing_arr = apply_filters( 'bookingpress_set_available_staffs', $service_timing_arr, $selected_service_id, $staff_timings_data, $staff_unavailable_times, $staffwise_booking_data, $shared_quantity );
								

                                //$service_timing_arr = apply_filters( 'bookingpress_calculate_time_with_client_timezone', $service_timing_arr, $selected_date );

                                /** timeslot in client timezone */
								
                                if ($display_slots_in_client_timezone) {

                                    $booking_timeslot_start = $selected_date . ' ' . $service_tmp_current_time . ':00';
                                    $booking_timeslot_end = $selected_date . ' ' . $staffmember_current_time . ':00';

                                    $booking_timeslot_start = apply_filters('bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);
                                    $booking_timeslot_end = apply_filters('bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);

                                    $service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start));
                                    $service_timing_arr['end_time'] = date('H:i', strtotime($booking_timeslot_end));

                                    $booking_timeslot_start_date = date('Y-m-d', strtotime($booking_timeslot_start));

                                    if ($change_store_date) {

                                        $store_selected_date = apply_filters('bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone);

                                        $service_timing_arr['store_service_date'] = $store_selected_date;

                                        $store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
                                        if (strtotime($store_selection_datetime) < current_time('timestamp') || $store_selected_date != $selected_date) {
                                            continue;
                                        }
                                    }
                                    if ($selected_date < $booking_timeslot_start_date) {
                                        break;
                                    }
                                }
                                $workhour_data[] = $service_timing_arr;
                            } else {
                                $service_timings_data['is_daysoff'] = true;
                            }
                        } else {
                            if (!$is_booked_for_minimum) {
                                $service_timing_arr = array(
                                    'start_time' => $service_tmp_current_time,
                                    'end_time' => $staffmember_current_time,
                                    'break_start_time' => $break_start_time,
                                    'break_end_time' => $break_end_time,
                                    'store_start_time' => $service_tmp_current_time,
                                    'store_end_time' => $staffmember_current_time,
                                    'store_service_date' => $selected_date,
                                    'is_booked' => 0,
                                    'max_capacity' => $service_max_capacity,
                                    'total_booked' => 0,
                                );

								if( !empty( $break_hour_timings ) ){
									$service_timing_arr = apply_filters( 'bpa_calculat_anystaff_breakhours_data', $service_timing_arr, $break_hour_timings );
								}

								
								if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
									$staffmember_current_time = $service_timing_arr['break_end_time'];
									continue;
								}
								$service_timing_arr = apply_filters( 'bookingpress_set_available_staffs', $service_timing_arr, $selected_service_id, $staff_timings_data, $staff_unavailable_times, $staffwise_booking_data, $shared_quantity );
								/* echo "<pre>";
								print_r( $service_timing_arr );
								echo "</pre>"; */

                                if ($display_slots_in_client_timezone) {

                                    $booking_timeslot_start = $selected_date . ' ' . $service_tmp_current_time . ':00';
                                    $booking_timeslot_end = $selected_date . ' ' . $staffmember_current_time . ':00';

                                    $booking_timeslot_start = apply_filters('bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);
                                    $booking_timeslot_end = apply_filters('bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);

                                    $service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start));
                                    $service_timing_arr['end_time'] = date('H:i', strtotime($booking_timeslot_end));

                                    $booking_timeslot_start_date = date('Y-m-d', strtotime($booking_timeslot_start));

                                    if ($change_store_date) {

                                        $store_selected_date = apply_filters('bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone);

                                        $service_timing_arr['store_service_date'] = $store_selected_date;

                                        $store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
                                        if (strtotime($store_selection_datetime) < current_time('timestamp') || $store_selected_date != $selected_date) {
                                            continue;
                                        }
                                    }
                                    if ($selected_date < $booking_timeslot_start_date) {
                                        break;
                                    }
                                }
                                $workhour_data[] = $service_timing_arr;
                            }
                        }
                    } else {
                        if ($staffmember_current_time >= $staffmember_end_time) {
                            break;
                        }
                    }

                    if (!empty($break_end_time)) {
                        $staffmember_current_time = $break_end_time;
                    }

                    if ($staffmember_current_time == $staffmember_end_time) {
                        break;
                    }

                    if (!empty($default_timeslot_step) && $default_timeslot_step != $service_step_duration_val && empty($break_start_time)) {

                        $service_tmp_time_obj = new DateTime($selected_date . ' ' . $service_tmp_current_time);
                        $service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
                        $staffmember_current_time = $service_tmp_time_obj->format('H:i');

                        $service_current_date = $service_tmp_time_obj->format('Y-m-d');
                        if ($service_current_date > $selected_date) {
                            break;
                        }
                    }
                }
                if (empty($workhour_data)) {
                    $service_timings_data['is_daysoff'] = true;
                }
				
                $service_timings_data['service_timings'] = $workhour_data;
			}
			add_filter( 'bookingpress_remove_crossed_timeslot','__return_false' );

			return $service_timings_data;
		}

		function bookingpress_retrieve_staffmember_timings_for_anystaff( $service_timings_data, $selected_service_id, $selected_date, $minimum_time_required, $service_max_capacity, $bookingpress_show_time_as_per_service_duration ){
			
			if( !empty( $service_timings_data['service_timings'] ) || true == $service_timings_data['is_daysoff'] || empty( $selected_service_id ) ){
				return $service_timings_data;
			}

			/** Check if any staff member is selected */
			if( !empty( $_POST['appointment_data_obj']['any_staff_selected'] ) && 1 == $_POST['appointment_data_obj']['any_staff_selected'] && !empty( $_POST['appointment_data_obj']['available_staffs'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
				$available_staffs = $_POST['appointment_data_obj']['available_staffs']; // phpcs:ignore
				$service_timings_data = apply_filters( 'bookingpress_retreive_timeslots_for_all_staffmembers', $service_timings_data, $available_staffs, $selected_service_id, $selected_date, $minimum_time_required, $service_max_capacity, $bookingpress_show_time_as_per_service_duration );
				
			}
			return $service_timings_data;
		}

		function bookingpress_retrieve_staffmember_timings( $service_timings_data, $selected_service_id, $selected_date, $minimum_time_required, $service_max_capacity, $bookingpress_show_time_as_per_service_duration ){
			
			if( !empty( $service_timings_data['service_timings'] ) || true == $service_timings_data['is_daysoff'] || empty( $selected_service_id ) ){
				return $service_timings_data;
			}

			global $wpdb, $BookingPress, $BookingPressPro, $tbl_bookingpress_staff_member_workhours, $tbl_bookingpress_staffmembers_meta, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_staffmembers_daysoff, $tbl_bookingpress_services;

			$bpa_current_date = date('Y-m-d', current_time('timestamp'));

			$bookingpress_selected_staffmember_id = !empty( $_POST['staffmember_id'] ) ? intval( $_POST['staffmember_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
			
			
			if( empty( $bookingpress_selected_staffmember_id ) ){
				$bookingpress_selected_staffmember_id = !empty( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) ? intval( $_POST['bookingpress_selected_staffmember']['selected_staff_member_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
				
				if( empty( $bookingpress_selected_staffmember_id ) ){

					if( empty( $_POST['appointment_data_obj'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
						$_POST['appointment_data_obj'] = !empty( $_POST['appointment_data'] ) ? array_map( array($BookingPress, 'appointment_sanatize_field'), $_POST['appointment_data'] ) : array();  // phpcs:ignore
					}
					$bookingpress_selected_staffmember_id = !empty( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) ? intval( $_POST['appointment_data_obj']['bookingpress_selected_staff_member_details']['selected_staff_member_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
					if( empty( $bookingpress_selected_staffmember_id ) ){

						return $service_timings_data;
					}
				}
			}

			$display_slots_in_client_timezone = false;

			$bookingpress_timezone = isset($_POST['client_timezone_offset']) ? sanitize_text_field( $_POST['client_timezone_offset'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
			
			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );

			$store_current_date = date('Y-m-d', current_time('timestamp' ) );
			$store_current_time = date('H:i', current_time('timestamp' ) );
			
			// 04May 2023 Changes
			$client_timezone_string = !empty( $_COOKIE['bookingpress_client_timezone'] ) ? sanitize_text_field($_COOKIE['bookingpress_client_timezone']) : '';
            if( 'true' == $bookingpress_timeslot_display_in_client_timezone && !empty( $client_timezone_string ) ){
                $client_timezone_offset = $BookingPress->bookingpress_convert_timezone_to_offset( $client_timezone_string, $bookingpress_timezone );
                $wordpress_timezone_offset = $BookingPress->bookingpress_convert_timezone_to_offset( wp_timezone_string() );                
                if( $client_timezone_offset  == $wordpress_timezone_offset ){
                    $bookingpress_timeslot_display_in_client_timezone = 'false';
                }
            }
			// 04May 2023 Changes

			if( isset($bookingpress_timezone) && '' !== $bookingpress_timezone && !empty($bookingpress_timeslot_display_in_client_timezone) && ($bookingpress_timeslot_display_in_client_timezone == 'true')){
				$display_slots_in_client_timezone = true;
			}

			if( strtotime( $bpa_current_date ) > strtotime( $selected_date ) && false == $display_slots_in_client_timezone ){
                return $service_timings_data;
            }

			/** Check if the selected date is holiday for staff member */
			
			
			$bookingpress_current_time = date( 'H:i',current_time('timestamp'));
			$bpa_current_datetime = date( 'Y-m-d H:i:s',current_time('timestamp'));

			$bookingpress_hide_already_booked_slot = $BookingPress->bookingpress_get_customize_settings( 'hide_already_booked_slot', 'booking_form' );
			$bookingpress_hide_already_booked_slot = ( $bookingpress_hide_already_booked_slot == 'true' ) ? 1 : 0;

			$current_day  = ! empty( $selected_date ) ? ucfirst( date( 'l', strtotime( $selected_date ) ) ) : ucfirst( date( 'l', current_time( 'timestamp' ) ) );
			$current_date = ! empty($selected_date) ? date('Y-m-d', strtotime($selected_date)) : date('Y-m-d', current_time('timestamp'));

			$bpa_current_time = date( 'H:i',current_time('timestamp'));

			$change_store_date = ( !empty( $_POST['bpa_change_store_date'] ) && 'true' == $_POST['bpa_change_store_date'] ) ? true : false; //phpcs:ignore

			$service_time_duration     = $BookingPress->bookingpress_get_default_timeslot_data();
			$service_step_duration_val = $service_time_duration['default_timeslot'];
			
			if (! empty($selected_service_id) ) {
				$service_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $selected_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_services is a table name
				if (! empty($service_data) ) {
					$service_time_duration      = esc_html($service_data['bookingpress_service_duration_val']);
					$service_time_duration_unit = esc_html($service_data['bookingpress_service_duration_unit']);
					if ($service_time_duration_unit == 'h' ) {
						$service_time_duration = $service_time_duration * 60;
					} elseif($service_time_duration_unit == 'd') {           
						$service_time_duration = $service_time_duration * 24 * 60;
					}
					$default_timeslot_step = $service_step_duration_val = $service_time_duration;
				}
			}
			
			$bpa_fetch_updated_slots = false;
            if( isset( $_POST['bpa_fetch_data'] ) && 'true' == $_POST['bpa_fetch_data'] ){ //phpcs:ignore
                $bpa_fetch_updated_slots = true;
            }
			$service_step_duration_val = apply_filters( 'bookingpress_modify_service_timeslot', $service_step_duration_val, $selected_service_id, $service_time_duration_unit, $bpa_fetch_updated_slots );

			$bookingpress_show_time_as_per_service_duration = $BookingPress->bookingpress_get_settings( 'show_time_as_per_service_duration', 'general_setting' );
            if ( ! empty( $bookingpress_show_time_as_per_service_duration ) && $bookingpress_show_time_as_per_service_duration == 'false' ) {
                $bookingpress_default_time_slot = $BookingPress->bookingpress_get_settings( 'default_time_slot', 'general_setting' );
                $default_timeslot_step      = $bookingpress_default_time_slot;
            } else {
				$default_timeslot_step      = $service_step_duration_val;
			}

			/** Check for staff member holiday */
			$bpa_get_staff_holiday = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_id, bookingpress_staffmember_daysoff_date, bookingpress_staffmember_daysoff_repeat FROM {$tbl_bookingpress_staffmembers_daysoff} WHERE ( bookingpress_staffmember_daysoff_date = %s OR bookingpress_staffmember_daysoff_repeat = %d )AND bookingpress_staffmember_id = %d", $selected_date, 1, $bookingpress_selected_staffmember_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_daysoff is a table name.


			$is_holiday = false;
			if( !empty( $bpa_get_staff_holiday ) ){
				foreach( $bpa_get_staff_holiday as $staff_holiday_data ){
					$hsf_id = $staff_holiday_data->bookingpress_staffmember_id;
					$hsf_date = $staff_holiday_data->bookingpress_staffmember_daysoff_date;
					$hsf_is_repeat = $staff_holiday_data->bookingpress_staffmember_daysoff_repeat;

					if( 1 == $hsf_is_repeat ){
						/** Check if the holiday is repeated and placed in the selected date */
						$current_date_without_year = date('m-d', strtotime( $selected_date ) );
						$holiday_date_without_year = date('m-d', strtotime( $hsf_date ) );
						if( $holiday_date_without_year == $current_date_without_year ){
							if( empty( $workhour_data ) ){
								$service_timings_data['is_daysoff'] = true;
								$is_holiday = true;
								break;
							}
						}
					} else {
						if( empty( $workhour_data ) ){
							$service_timings_data['is_daysoff'] = true;
							$is_holiday = true;
							break;
						}
					}
				}
			}
			if( true == $is_holiday ){
				return $service_timings_data;
			}


			$workhour_data = array();

			/** Check for Staff Member Special Days */
			$bookingpress_staffmember__special_day_details = $BookingPressPro->bookingpress_get_staffmember_special_days(  $bookingpress_selected_staffmember_id, $selected_service_id, $selected_date );
			
			if( !empty( $bookingpress_staffmember__special_day_details ) ){
				
				$staffmember_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_staffmember__special_day_details['special_day_start_time'])), $selected_service_id );
				
				$staffmember_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_staffmember__special_day_details['special_day_end_time'])), $selected_service_id );

				if( '00:00' == $staffmember_end_time ){
					$staffmember_end_time = '24:00';
				}
				$staff_special_day_id = $bookingpress_staffmember__special_day_details['special_day_id'];

				$staff_special_day_break_data = $this->bookingpress_get_staffmember_special_days_break( $staff_special_day_id );

				if ($service_start_time != null && $staffmember_end_time != null ) {
					while ( $staffmember_current_time <= $staffmember_end_time ) {
						if ($staffmember_current_time > $staffmember_end_time ) {
							break;
						}

						$service_tmp_date_time = $selected_date .' '.$staffmember_current_time;
						$service_tmp_end_time = date( 'Y-m-d', ( strtotime($selected_date. ' ' . $staffmember_current_time ) + ( $service_step_duration_val * 60 ) ) );

						if( $service_tmp_end_time > $selected_date  ){
							if( 1440 < $service_step_duration_val && $service_time_duration_unit != 'd' ){
								break;
							}
						}

						$service_tmp_current_time = $staffmember_current_time;

						if ($staffmember_current_time == '00:00' ) {
							$staffmember_current_time = date('H:i', strtotime($staffmember_current_time) + ( $service_step_duration_val * 60 ));
						} else {
							$service_tmp_time_obj = new DateTime($selected_date . ' ' . $staffmember_current_time);
							$service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
							$staffmember_current_time = $service_tmp_time_obj->format('H:i');

							$service_current_date = $service_tmp_time_obj->format('Y-m-d');
                            if( $service_current_date > $selected_date ){
								if( $staffmember_end_time == '24:00' && strtotime($service_current_date.' '.$staffmember_current_time) > strtotime( $service_current_date . ' 00:00' ) ){
                                    break;
                                }
							}
						}

						$break_start_time      = '';
						$break_end_time        = '';

						if ($staffmember_current_time < $service_start_time || $staffmember_current_time == $service_start_time ) {
							$staffmember_current_time = $staffmember_end_time;
						}

						$bookingpress_timediff_in_minutes = round(abs(strtotime($staffmember_current_time) - strtotime($service_tmp_current_time)) / 60, 2);
						$is_already_booked = 0;
						$is_booked_for_minimum = false;
						if( 'disabled' != $minimum_time_required ){
							$bookingpress_slot_start_datetime       = $selected_date . ' ' . $service_tmp_current_time . ':00';
							$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
							$bookingpress_time_diff = round( abs( current_time('timestamp') - $bookingpress_slot_start_time_timestamp ) / 60, 2 );
							
							if( $bookingpress_time_diff <= $minimum_time_required ){
								$is_booked_for_minimum = true;
							}
						}
						
						if ($is_already_booked == 1 && $bookingpress_hide_already_booked_slot == 1 ) {
							continue;
						} else {
							if ($break_start_time != $service_tmp_current_time && $bookingpress_timediff_in_minutes >= $service_step_duration_val && $staffmember_current_time <= $staffmember_end_time ) {
								if ( $bpa_current_date == $selected_date ) {
									if ($service_tmp_current_time > $bpa_current_time && !$is_booked_for_minimum ) {

										$service_timing_arr = array(
											'start_time' => $service_tmp_current_time,
											'end_time'   => $staffmember_current_time,
											'break_start_time' => $break_start_time,
											'break_end_time' => $break_end_time,
											'store_start_time' => $service_tmp_current_time,
											'store_end_time' => $staffmember_current_time,
											'store_service_date' => $selected_date,
											'is_booked'  => $is_already_booked,
											'max_capacity' => $service_max_capacity,
											'total_booked' => 0
										);

										if( !empty( $staff_special_day_break_data ) ){
											$service_timing_arr = apply_filters( 'bpa_calculate_staff_breakhours_data', $service_timing_arr, $staff_special_day_break_data );
										}

										if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
											$staffmember_current_time = $service_timing_arr['break_end_time'];
											continue;
										}

										if( $display_slots_in_client_timezone ){

											$booking_timeslot_start = $selected_date.' '.$service_tmp_current_time.':00';
											$booking_timeslot_end = $selected_date .' '.$staffmember_current_time.':00';
											
											
											$booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
											$booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);
											
											$service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start) );
											$service_timing_arr['end_time'] = date('H:i', strtotime( $booking_timeslot_end ) );

											$booking_timeslot_start_date = date('Y-m-d', strtotime( $booking_timeslot_start ) );

											if( $change_store_date ) {

												$store_selected_date = apply_filters( 'bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone );
												
												$service_timing_arr['store_service_date'] = $store_selected_date;
												
												$store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
												if( strtotime( $store_selection_datetime ) < current_time('timestamp' ) || $store_selected_date != $selected_date ){
													continue;
												}
											}
											if( $selected_date < $booking_timeslot_start_date){
												break;
											}
										}
										$workhour_data[] = $service_timing_arr;
									} else {
										$service_timings_data['is_daysoff'] = true;
									}
								} else {
									if( !$is_booked_for_minimum ){
										$service_timing_arr = array(
											'start_time' => $service_tmp_current_time,
											'end_time'   => $staffmember_current_time,
											'break_start_time' => $break_start_time,
											'break_end_time' => $break_end_time,
											'store_start_time' => $service_tmp_current_time,
											'store_end_time' => $staffmember_current_time,
											'store_service_date' => $selected_date,
											'is_booked'  => $is_already_booked,
											'max_capacity' => $service_max_capacity,
											'total_booked' => 0
										);

										if( !empty( $staff_special_day_break_data ) ){
											$service_timing_arr = apply_filters( 'bpa_calculate_staff_breakhours_data', $service_timing_arr, $staff_special_day_break_data );
										}

										if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
											$staffmember_current_time = $service_timing_arr['break_end_time'];
											continue;
										}

										if( $display_slots_in_client_timezone ){

											$booking_timeslot_start = $selected_date.' '.$service_tmp_current_time.':00';
											$booking_timeslot_end = $selected_date .' '.$staffmember_current_time.':00';
											
											
											$booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
											$booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);
											
											$service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start) );
											$service_timing_arr['end_time'] = date('H:i', strtotime( $booking_timeslot_end ) );

											$booking_timeslot_start_date = date('Y-m-d', strtotime( $booking_timeslot_start ) );

											if( $change_store_date ) {

												$store_selected_date = apply_filters( 'bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone );
												
												$service_timing_arr['store_service_date'] = $store_selected_date;
												
												$store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
												if( strtotime( $store_selection_datetime ) < current_time('timestamp' ) || $store_selected_date != $selected_date ){
													continue;
												}
											}
											if( $selected_date < $booking_timeslot_start_date){
												break;
											}
										}
										$workhour_data[] = $service_timing_arr;
									}else {
										$service_timings_data['is_daysoff'] = true;
									}
								}
							} else {
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
						
						if(!empty($default_timeslot_step) && $default_timeslot_step != $service_step_duration_val && empty($break_start_time)){
							$service_tmp_time_obj = new DateTime($selected_date . ' ' . $service_tmp_current_time);
							$service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
							$staffmember_current_time = $service_tmp_time_obj->format('H:i');
							
							$service_current_date = $service_tmp_time_obj->format('Y-m-d');
							if( $service_current_date > $selected_date ){
								break;
							}
						}
					}
					if( empty( $workhour_data ) ){
						$service_timings_data['is_daysoff'] = true;
					}
					$service_timings_data['service_timings'] = $workhour_data;
					//die;
					return $service_timings_data;
				}
			}

			$is_staffmember_workhour_enable = $this->get_bookingpress_staffmembersmeta($bookingpress_selected_staffmember_id, 'bookingpress_configure_specific_workhour');

			if( "true" == $is_staffmember_workhour_enable ){
				$bookingpress_staffmember_workhours = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_is_break = 0 AND bookingpress_staffmember_workhours_start_time IS NOT NULL AND bookingpress_staffmember_workday_key = %s", $bookingpress_selected_staffmember_id, ucfirst($current_day)), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is a table name. false alarm

				$bpa_staff_workhour_breaks = array();

				//$staff_workhours_breaks
				$staff_workhours_breaks = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_workhours_start_time as bpa_staff_start_time, bookingpress_staffmember_workhours_end_time as bpa_staff_end_time FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_workhours_is_break = %d AND bookingpress_staffmember_workhours_start_time IS NOT NULL AND bookingpress_staffmember_workday_key = %s AND bookingpress_staffmember_id = %d", 1, ucfirst( $current_day ), $bookingpress_selected_staffmember_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is a table name.

				if( !empty( $staff_workhours_breaks ) ){
					foreach( $staff_workhours_breaks as $staff_break_workhour_data ){
						$sf_break_start_time = $staff_break_workhour_data->bpa_staff_start_time;
						$sf_break_end_time = $staff_break_workhour_data->bpa_staff_end_time;

						$bpa_staff_workhour_breaks[] = array(
							'start_time' => date('H:i', strtotime( $sf_break_start_time ) ),
							'end_time' => date('H:i', strtotime( $sf_break_end_time ) ),
						);
					}
				}

				if( !empty( $bookingpress_staffmember_workhours ) ){
					$staffmember_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_staffmember_workhours['bookingpress_staffmember_workhours_start_time'])), $selected_service_id );
					$staffmember_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_staffmember_workhours['bookingpress_staffmember_workhours_end_time'])), $selected_service_id );

					if( '00:00' == $staffmember_end_time ){
						$staffmember_end_time = '24:00';
					}
					if ($service_start_time != null && $staffmember_end_time != null ) {
						
						while ( $staffmember_current_time <= $staffmember_end_time ) {
							if ($staffmember_current_time > $staffmember_end_time ) {
								break;
							}

							$service_tmp_date_time = $selected_date .' '.$staffmember_current_time;
							$service_tmp_end_time = date( 'Y-m-d', ( strtotime($selected_date. ' ' . $staffmember_current_time ) + ( $service_step_duration_val * 60 ) ) );

							if( $service_tmp_end_time > $selected_date  ){
								if( 1440 < $service_step_duration_val && $service_time_duration_unit != 'd' ){
									break;
								}
							}

							$service_tmp_current_time = $staffmember_current_time;
							
							if ($staffmember_current_time == '00:00' ) {
								$staffmember_current_time = date('H:i', strtotime($staffmember_current_time) + ( $service_step_duration_val * 60 ));
							} else {
								$service_tmp_time_obj = new DateTime($selected_date .' ' . $staffmember_current_time);
								$service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
								$staffmember_current_time = $service_tmp_time_obj->format('H:i');
								$service_current_date = $service_tmp_time_obj->format('Y-m-d');
								if( $service_current_date > $selected_date ){
									if( $staffmember_end_time == '24:00' && strtotime($service_current_date.' '.$staffmember_current_time) > strtotime( $service_current_date . ' 00:00' ) ){
										break;
									}
								}
							}
	
							if ($staffmember_current_time < $service_start_time || $staffmember_current_time == $service_start_time ) {
								$staffmember_current_time = $staffmember_end_time;
							}

							$break_start_time = '';
							$break_end_time = '';
							/** Staff member work hour break time logic start */

							/* $staffmember_workhour_breaks_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_staffmember_workhours_start_time, bookingpress_staffmember_workhours_end_time FROM {$tbl_bookingpress_staff_member_workhours} WHERE bookingpress_staffmember_workday_key = %s AND bookingpress_staffmember_workhours_is_break = %d AND bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_start_time BETWEEN %s AND %s", ucfirst($current_day), 1, $bookingpress_selected_staffmember_id, $service_tmp_current_time, $staffmember_current_time)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is table name.

							if( !empty( $staffmember_workhour_breaks_data ) ){
								$break_start_time = date('H:i', strtotime( $staffmember_workhour_breaks_data->bookingpress_staffmember_workhours_start_time ) );
								$break_end_time = date('H:i', strtotime( $staffmember_workhour_breaks_data->bookingpress_staffmember_workhours_end_time ) );
								$staffmember_current_time = $break_start_time;
							} */

							/** Staff member work hour break time logic end */

							$bookingpress_timediff_in_minutes = round(abs(strtotime($staffmember_current_time) - strtotime($service_tmp_current_time)) / 60, 2);
							$is_booked_for_minimum = false;
							if( 'disabled' != $minimum_time_required ){
								$bookingpress_slot_start_datetime       = $selected_date . ' ' . $service_tmp_current_time . ':00';
								$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
								$bookingpress_time_diff = round( abs( current_time('timestamp') - $bookingpress_slot_start_time_timestamp ) / 60, 2 );
								
								if( $bookingpress_time_diff <= $minimum_time_required ){
									$is_booked_for_minimum = true;
								}
							}
							
							if ($break_start_time != $service_tmp_current_time && $bookingpress_timediff_in_minutes >= $service_step_duration_val && $staffmember_current_time <= $staffmember_end_time ) {
								if ($bpa_current_date == $selected_date ) {
									if ($service_tmp_current_time > $bpa_current_time && !$is_booked_for_minimum ) {

										$service_timing_arr = array(
											'start_time' => $service_tmp_current_time,
											'end_time'   => $staffmember_current_time,
											'break_start_time' => $break_start_time,
											'break_end_time' => $break_end_time,
											'store_start_time' => $service_tmp_current_time,
											'store_end_time' => $staffmember_current_time,
											'is_booked' => 0,
											'store_service_date' => $selected_date,
											'max_capacity' => $service_max_capacity,
											'total_booked' => 0
										);

										if( !empty( $bpa_staff_workhour_breaks ) ){
											$service_timing_arr = apply_filters( 'bpa_calculate_staff_breakhours_data', $service_timing_arr, $bpa_staff_workhour_breaks );
										}

										if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
											$staffmember_current_time = $service_timing_arr['break_end_time'];
											continue;
										}
										
										//$service_timing_arr = apply_filters( 'bookingpress_calculate_time_with_client_timezone', $service_timing_arr, $selected_date );

										/** timeslot in client timezone */
										if( $display_slots_in_client_timezone ){

											$booking_timeslot_start = $selected_date.' '.$service_tmp_current_time.':00';
											$booking_timeslot_end = $selected_date .' '.$staffmember_current_time.':00';
											
											
											$booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
											$booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);
											
											$service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start) );
											$service_timing_arr['end_time'] = date('H:i', strtotime( $booking_timeslot_end ) );

											$booking_timeslot_start_date = date('Y-m-d', strtotime( $booking_timeslot_start ) );

											if( $change_store_date ) {

												$store_selected_date = apply_filters( 'bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone );
												
												$service_timing_arr['store_service_date'] = $store_selected_date;
												
												$store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
												if( strtotime( $store_selection_datetime ) < current_time('timestamp' ) || $store_selected_date != $selected_date ){
													continue;
												}
											}
											if( $selected_date < $booking_timeslot_start_date){
												break;
											}
										}
										$workhour_data[] = $service_timing_arr;
									}else {
										$service_timings_data['is_daysoff'] = true;
									}
								} else {
									if( !$is_booked_for_minimum ){
										$service_timing_arr = array(
											'start_time' => $service_tmp_current_time,
											'end_time'   => $staffmember_current_time,
											'break_start_time' => $break_start_time,
											'break_end_time' => $break_end_time,
											'store_start_time' => $service_tmp_current_time,
											'store_end_time' => $staffmember_current_time,
											'store_service_date' => $selected_date,
											'is_booked' => 0,
											'max_capacity' => $service_max_capacity,
											'total_booked' => 0
										);
										
										if( !empty( $bpa_staff_workhour_breaks ) ){
											$service_timing_arr = apply_filters( 'bpa_calculate_staff_breakhours_data', $service_timing_arr, $bpa_staff_workhour_breaks );
										}

										if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
											$staffmember_current_time = $service_timing_arr['break_end_time'];
											continue;
										}

										if( $display_slots_in_client_timezone ){
	
											$booking_timeslot_start = $selected_date.' '.$service_tmp_current_time.':00';
											$booking_timeslot_end = $selected_date .' '.$staffmember_current_time.':00';
											
											
											$booking_timeslot_start = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_start, $bookingpress_timezone);	
											$booking_timeslot_end = apply_filters( 'bookingpress_appointment_change_to_client_timezone', $booking_timeslot_end, $bookingpress_timezone);
											
											$service_timing_arr['start_time'] = date('H:i', strtotime($booking_timeslot_start) );
											$service_timing_arr['end_time'] = date('H:i', strtotime( $booking_timeslot_end ) );
	
											$booking_timeslot_start_date = date('Y-m-d', strtotime( $booking_timeslot_start ) );

											if( $change_store_date ) {

												$store_selected_date = apply_filters( 'bookingpress_appointment_change_date_to_store_timezone', $selected_date, $service_timing_arr['start_time'], $bookingpress_timezone );
												
												$service_timing_arr['store_service_date'] = $store_selected_date;
												
												$store_selection_datetime = $store_selected_date . ' ' . $service_tmp_current_time;
												if( strtotime( $store_selection_datetime ) < current_time('timestamp' ) || $store_selected_date != $selected_date ){
													continue;
												}
											}
											if( $selected_date < $booking_timeslot_start_date){
												break;
											}
										}
										$workhour_data[] = $service_timing_arr;
									}
								}
							} else {
								if($staffmember_current_time >= $staffmember_end_time){
									break;
								}
							}

							if (! empty($break_end_time) ) {
								$staffmember_current_time = $break_end_time;
							}
			
							if ($staffmember_current_time == $staffmember_end_time ) {
								break;
							}

							if(!empty($default_timeslot_step) && $default_timeslot_step != $service_step_duration_val && empty($break_start_time)){

								$service_tmp_time_obj = new DateTime($selected_date . ' ' . $service_tmp_current_time);
								$service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
								$staffmember_current_time = $service_tmp_time_obj->format('H:i');
								
								$service_current_date = $service_tmp_time_obj->format('Y-m-d');
								if( $service_current_date > $selected_date ){
									break;
								}
							}
						}
						if( empty( $workhour_data ) ){
							$service_timings_data['is_daysoff'] = true;
						}
						$service_timings_data['service_timings'] = $workhour_data;

						return $service_timings_data;
					}
				} else {
					$service_timings_data['is_daysoff'] = true;
				}

			}
			
			return $service_timings_data;
		}

		function bookingpress_get_staffmember_special_days_break( $special_day_id ){

			$special_day_breaks_data = array();

			global $wpdb, $tbl_bookingpress_staffmembers_special_day_breaks;

			$get_special_day_breaks = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_special_day_break_start_time,bookingpress_special_day_break_end_time FROM {$tbl_bookingpress_staffmembers_special_day_breaks} WHERE bookingpress_special_day_id = %d", $special_day_id) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day_breaks is a table name.

			if( !empty( $get_special_day_breaks ) ){
				foreach( $get_special_day_breaks as $sp_day_break_data ){
					$special_day_breaks_data[] = array(
						'start_time' => date('H:i', strtotime( $sp_day_break_data->bookingpress_special_day_break_start_time ) ),
						'end_time' => date( 'H:i', strtotime( $sp_day_break_data->bookingpress_special_day_break_end_time ) ),
					);
				}
			}

			return $special_day_breaks_data;
		}
		
		function bookingpress_format_staffmember_special_days_data_func() {
			global $wpdb, $bookingpress_global_options,$BookingPress;
			$response                    = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'format_staff_special_days_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			
			$response['daysoff_details'] = '';
			
			$bookingpress_global_settings   = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_date_format       = $bookingpress_global_settings['wp_default_date_format'];
			$bookingpress_time_format       = $bookingpress_global_settings['wp_default_time_format'];

			$bookingpress_special_days_data = ! empty( $_POST['special_days_data'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['special_days_data']) : array(); //phpcs:ignore

			if ( ! empty( $bookingpress_special_days_data ) && is_array( $bookingpress_special_days_data ) ) {
				foreach ( $bookingpress_special_days_data as $k => $v ) {
					$bookingpress_special_days_data[ $k ]['special_day_formatted_start_date'] = date( $bookingpress_date_format, strtotime(  $v['special_day_start_date'] ) );
					$bookingpress_special_days_data[ $k ]['special_day_formatted_end_date']   = date( $bookingpress_date_format, strtotime(  $v['special_day_end_date'] ) ) ;
					$bookingpress_special_days_data[ $k ]['formatted_start_time']             = date( $bookingpress_time_format, strtotime(  $v['start_time'] ) ) ;
					$bookingpress_special_days_data[ $k ]['formatted_end_time']               = date( $bookingpress_time_format, strtotime(  $v['end_time'] ) ) ;
					if ( ! empty( $v['special_day_workhour'] ) ) {
						foreach ( $v['special_day_workhour'] as $k2 => $v2 ) {
							$bookingpress_special_days_data[ $k ]['special_day_workhour'][ $k2 ]['formatted_start_time'] = date( $bookingpress_time_format, strtotime(  $v2['start_time'] ) );
							$bookingpress_special_days_data[ $k ]['special_day_workhour'][ $k2 ]['formatted_end_time']   = date( $bookingpress_time_format, strtotime( $v2['end_time'] ) );
						}
					}
				}
				$response['variant']         = 'success';
				$response['title']           = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']             = esc_html__( 'Details formatted successfully', 'bookingpress-appointment-booking' );
				$response['daysoff_details'] = $bookingpress_special_days_data;
			}
			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_format_staffmember_daysoff_data_func() {
			global $wpdb, $bookingpress_global_options,$BookingPress;
			$response                    = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'format_staffmember_daysoff_data', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$response['variant']         = 'error';
			$response['title']           = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$response['msg']             = esc_html__( 'Something went wrong', 'bookingpress-appointment-booking' );
			$response['daysoff_details'] = '';
			$wpnonce                     = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bpa_verify_nonce_flag       = wp_verify_nonce( $wpnonce, 'bpa_wp_nonce' );
			if ( ! $bpa_verify_nonce_flag ) {
				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking' );
				echo wp_json_encode( $response );
				die();
			}
			$bookingpress_global_settings   = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_date_format       = $bookingpress_global_settings['wp_default_date_format'];

			$bookingpress_daysoff_data = ! empty( $_POST['daysoff_data'] ) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['daysoff_data']) : array();  //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST['daysoff_data'] has already been sanitized.

			if ( ! empty( $bookingpress_daysoff_data ) && is_array( $bookingpress_daysoff_data ) ) {
				foreach ( $bookingpress_daysoff_data as $k => $v ) {
					$bookingpress_daysoff_data[ $k ]['dayoff_formatted_date'] = date( $bookingpress_date_format, strtotime(  $v['dayoff_date'] ));					
					$bookingpress_daysoff_data[ $k ]['dayoff_repeat'] = $v['dayoff_repeat'] == 'true' ? true : false;
					$bookingpress_daysoff_data[ $k ]['dayoff_name'] = !empty($v['dayoff_name']) ? stripslashes_deep($v['dayoff_name']) : '';

				}
				$response['variant']         = 'success';
				$response['title']           = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']             = esc_html__( 'Details formatted successfully', 'bookingpress-appointment-booking' );
				$response['daysoff_details'] = $bookingpress_daysoff_data;
			}
			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_change_staff_member_status_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'change_staffmember_status', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

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
			$response['msg']     = esc_html__( 'Something went wrong', 'bookingpress-appointment-booking' );

			$bookingpress_staffmember_id     = ! empty( $_POST['staff_member_id'] ) ? intval( $_POST['staff_member_id'] ) : 0;
			$bookingpress_staffmember_status = ! empty( $_POST['status_val'] ) ? intval( $_POST['status_val'] ) : 0;

			if ( ! empty( $bookingpress_staffmember_id ) ) {
				$wpdb->update( $tbl_bookingpress_staffmembers, array( 'bookingpress_staffmember_status' => $bookingpress_staffmember_status ), array( 'bookingpress_staffmember_id' => $bookingpress_staffmember_id ) );

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Staff member status changed successfully', 'bookingpress-appointment-booking' );
			}

			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_load_staff_members_view_func( $bookingpress_load_file_name ) {
			$bookingpress_load_file_name = BOOKINGPRESS_PRO_VIEWS_DIR . '/staff_members/manage_staff_members.php';
			require $bookingpress_load_file_name;
		}

		function bookingpress_staff_members_vue_methods_func() {
			global $bookingpress_notification_duration,$BookingPress;
			$bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings( 'default_phone_country_code', 'general_setting' );
			$bookingpress_export_delimeter     = $BookingPress->bookingpress_get_settings( 'bookingpress_export_delimeter', 'general_setting' );
			?>
				bookingpress_row_classname(row, rowIndex){
					if(row.row.staffmember_status == 0){
						return 'bpa-table__is-row-disabled';
					}
				},
				async loadStaffmembers() {
					var bookingpress_module_type = bookingpress_dashboard_filter_start_date = bookingpress_dashboard_filter_end_date = staffmember_date_range = '';
                    bookingpress_module_type = sessionStorage.getItem("bookingpress_module_type");                
                    bookingpress_dashboard_filter_start_date = sessionStorage.getItem("bookingpress_dashboard_filter_start_date");
                    bookingpress_dashboard_filter_end_date = sessionStorage.getItem("bookingpress_dashboard_filter_end_date");
                    sessionStorage.removeItem("bookingpress_module_type");
                    sessionStorage.removeItem("bookingpress_dashboard_filter_start_date");
                    sessionStorage.removeItem("bookingpress_dashboard_filter_end_date");                    
                    if(bookingpress_module_type != '' && bookingpress_module_type == 'staffmember' && bookingpress_dashboard_filter_start_date != '' && bookingpress_dashboard_filter_end_date != '' ) {                        
                        staffmember_date_range = [bookingpress_dashboard_filter_start_date,bookingpress_dashboard_filter_end_date];                        
                    }  
					var bookingpress_search_data = { search_name: this.staff_member_search,search_date_range :staffmember_date_range }
					var postData = { action:'bookingpress_get_staffmembers',search_data: bookingpress_search_data, _wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						this.items = response.data.items;
						this.totalItems = response.data.total;
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},
				resetFilter(){
					const vm2 = this
					vm2.staff_member_search ='';
					vm2.loadStaffmembers()
				},
				handleSelectionChange(e, isChecked, staffmember_id) {
					const vm = this                                
					vm.bulk_action = 'bulk_action';
					if(isChecked){
						vm.multipleSelection.push(staffmember_id);
					}else{
						var removeIndex = vm.multipleSelection.indexOf(staffmember_id);
						if(removeIndex > -1){
							vm.multipleSelection.splice(removeIndex, 1);
						}
					}
				},
				closeBulkAction(){
					const vm = this
					vm.bulk_action = 'bulk_action';
					vm.multipleSelection = []
					vm.items.forEach(function(selectedVal, index, arr) {            
						selectedVal.selected = false;
					})
					vm.is_multiple_checked = false;
				}, 
				open_staff_member_modal_func(action = 'add'){
					const vm = this	
					vm.bookingpress_reset_staff_member_form()
					if(action == 'add') {						
						//vm.bookingpress_get_default_workhours()
						//vm.bookingpress_assigned_service_data();
						vm.staffmember_dayoff_arr = []
						vm.staffmember_special_day_arr =[]
					}
					vm.assign_service_form.assigned_service_list = [];
					vm.total_assigned_services = 0;
					vm.open_staff_member_modal = true					
				},
				close_staff_member_modal_func() {
					const vm = this	
					vm.bookingpress_reset_staff_member_form()
					vm.$refs['staff_members'].resetFields()
					vm.open_staff_member_modal = false
				},
				bookingpress_image_upload_limit(files, fileList){
					const vm2 = this
						if(vm2.staff_members.avatar_url != ''){
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Multiple files not allowed', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					}
				},
				bookingpress_image_upload_err(err, file, fileList){
					const vm2 = this
					var bookingpress_err_msg = '<?php esc_html_e( 'Something went wrong', 'bookingpress-appointment-booking' ); ?>';
					if(err != '' || err != undefined){
						bookingpress_err_msg = err
					}
					vm2.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: bookingpress_err_msg,
						type: 'error',
						customClass: 'error_notification',
					});
				},
				checkUploadedFile(file){
					const vm2 = this
					if(file.type != 'image/jpeg' && file.type != 'image/png' && file.type != 'image/webp'){
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Please upload jpg/png file only', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
						return false
					}else{
						var bpa_image_size = parseInt(file.size / 1000000);
						if(bpa_image_size > 1){
							vm2.$notify({
								title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
								message: '<?php esc_html_e('Please upload maximum 1 MB file only', 'bookingpress-appointment-booking'); ?>',
								type: 'error',
								customClass: 'error_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});                    
							return false
						}
					}
				},
				bookingpress_remove_staff_members_avatar() {
					const vm = this
					var upload_url = vm.staff_members.avatar_url
					var upload_filename = vm.staff_members.avatar_name
					var postData = { action:'bookingpress_remove_uploaded_file', upload_file_url: upload_url,_wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						vm.staff_members.avatar_url = ''
						vm.staff_members.avatar_name = ''
						vm.$refs.avatarRef.clearFiles()
					}.bind(vm) )
					.catch( function (error) {
						console.log(error);
					});
				},
				bookingpress_upload_staff_member_avatar_func(response, file, fileList){
					const vm2 = this
					if(response != ''){
						vm2.staff_members.avatar_url = response.upload_url
						vm2.staff_members.avatar_name = response.upload_file_name
					}
				},
				get_wordpress_users(query) {
					const vm = new Vue()
					const vm2 = this	
					if (query !== '') {
						vm2.bookingpress_loading = true;
						var staff_member_action = { action:'bookingpress_get_staffmember_wpuser',search_user_str:query,wordpress_user_id:vm2.wordpress_user_id,_wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' }
						axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( staff_member_action ) )
						.then(function(response){
							vm2.bookingpress_loading = false;
							vm2.wpUsersList = response.data.users;
						}).catch(function(error){
							console.log(error)
						});
					} else {
						vm2.wpUsersList = [];
					}									
				},
				bookingpress_get_staffmember_workhour_data(edit_id){
					const vm2 = this
					vm2.staff_members.update_id = edit_id
					var staff_members_action = { action: 'bookingpress_get_staffmember_workhour_data', edit_id: edit_id,_wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' }
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( staff_members_action ) )
					.then(function(response){			
						if(response.data.variant != undefined && response.data.variant == 'success'){													
							var staff_member_workhour_details = response.data.edit_data;			
							vm2.bookingpress_configure_specific_workhour = staff_member_workhour_details.bookingpress_configure_specific_workhour;
							if(staff_member_workhour_details.workhours !== undefined && staff_member_workhour_details.workhours != '') {
								vm2.workhours_timings = staff_member_workhour_details.workhours;									
								staff_member_workhour_details.workhour_data.forEach(function(currentValue, index, arr){
									vm2.work_hours_days_arr.forEach(function(currentValue2, index2, arr2){										
										if(currentValue2.day_name == currentValue.day_name) {											
											vm2.work_hours_days_arr[index2]['break_times'] = currentValue.break_times							
										}
									});	
									vm2.selected_break_timings[currentValue.day_name] = currentValue.break_times							
								});		
							}
						} else {		
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
							});						
						}
					}).catch(function(error){
						console.log(error)
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					});
				},
				saveStaffMembersDetails(){
					const vm2 = this
					vm2.$refs['staff_members'].validate((valid) => {
						if(valid){									
							vm2.is_disabled = true
							vm2.is_display_save_loader = '1'
							var postdata = vm2.staff_members;				
							postdata.service_details = vm2.assign_service_form
							postdata.action = 'bookingpress_add_staff_member';
							postdata.bookingpress_action = 'bookingpress_edit_staffmember';
							<?php do_action( 'bookingpress_save_staff_member' ); ?>
							postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
							.then(function(response){
								vm2.is_disabled = false
								vm2.is_display_save_loader = '0'							
								vm2.$notify({
									title: response.data.title,
									message: response.data.msg,
									type: response.data.variant,
									customClass: response.data.variant+'_notification',
								});
								if (response.data.variant == 'success') {
									vm2.close_staff_member_modal_func()																		
									vm2.staff_members.update_id = response.data.staff_member_id
									vm2.loadStaffmembers()
								}
							}).catch(function(error){
								vm2.is_disabled = false
								vm2.is_display_loader = '0'
								console.log(error);
								vm2.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
								});
							});
						}
					})
				},
				saveShiftManagementDetails(){
					const vm2 = this
					vm2.is_disabled = true
					vm2.is_display_save_loader = '1'
					var postdata = [];
					postdata.action = 'bookingpress_add_staff_member';
					postdata.update_id = vm2.staff_members.update_id;
					postdata.workhours_details = JSON.stringify( vm2.workhours_timings );
					postdata.break_details = JSON.stringify( vm2.selected_break_timings );
					postdata.dayoff_details = JSON.stringify( vm2.staffmember_dayoff_arr );
					postdata.special_day_details = JSON.stringify( vm2.staffmember_special_day_arr );
					postdata.bookingpress_action = 'bookingpress_shift_managment';
					<?php do_action( 'bookingpress_save_staff_member' ); ?>
					postdata.bookingpress_configure_specific_workhour = vm2.bookingpress_configure_specific_workhour
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then(function(response){
						vm2.is_disabled = false
						vm2.is_display_save_loader = '0'							
						vm2.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
						});
						if (response.data.variant == 'success') {
							vm2.open_shift_management_modal = false																		
							vm2.staff_members.update_id = response.data.staff_member_id
							vm2.loadStaffmembers()
						}
					}).catch(function(error){
						vm2.is_disabled = false
						vm2.is_display_loader = '0'
						console.log(error);
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					});
				},
				bookingpress_reset_staff_member_form(){
					const vm = this
					vm.staff_members.avatar_url = ''
					vm.staff_members.avatar_name = ''
					vm.staff_members.avatar_list = []
					vm.staff_members.wp_user = '';
					vm.staff_members.firstname = ''
					vm.staff_members.lastname = ''
					vm.staff_members.email = ''
					vm.staff_members.phone = ''
					vm.staff_members.staff_member_phone_country = '<?php echo esc_html( $bookingpress_phone_country_option ); ?>';
					vm.staff_members.panel_password = ''
					vm.staff_members.note = ''
					vm.staff_members.visibility = 'public'					
					vm.staff_members.update_id = 0
					vm.bookingpress_configure_specific_workhour = false;
					vm.assigned_services = []
					vm.multipleSelection_category = []
					vm.is_multiple_checked = []
					let empSpecialDayData = {};					
					Object.assign(empSpecialDayData, {special_day_date: ''})
					Object.assign(empSpecialDayData, {start_time: ''})
					Object.assign(empSpecialDayData, {end_time: ''})
					Object.assign(empSpecialDayData, {special_day_service: ''})
					Object.assign(empSpecialDayData, {special_day_workhour: []})					
					vm.staffmember_special_day_form = empSpecialDayData;
					vm.wordpress_user_id = '';
					vm.wpUsersList='';
					<?php do_action('bookingpress_add_staffmember_model_reset') ?>
				},
				editStaffMember(edit_id){
					const vm2 = this
					vm2.staff_members.update_id = edit_id
					//vm2.bookingpress_assigned_service_data();
					vm2.open_staff_member_modal_func('edit')					
					var staff_members_action = { action: 'bookingpress_get_edit_staff_member', edit_id: edit_id,_wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' }
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( staff_members_action ) )
					.then(function(response){			
						if(response.data.variant != undefined && response.data.variant == 'success'){						
							var edit_staff_members_details = response.data.edit_data;																					
							vm2.staff_members.update_id  = edit_id;
							if(edit_staff_members_details.basic_details != undefined) {
								if(edit_staff_members_details.bookingpress_wpuser_id != '' && edit_staff_members_details.bookingpress_wpuser_id != null) {	 
									vm2.staff_members.wp_user = parseInt(edit_staff_members_details.bookingpress_wpuser_id);		
								} else {							
									vm2.staff_members.wp_user = '';
								}
								vm2.wordpress_user_id = vm2.staff_members.wp_user;
								if( edit_staff_members_details.bookingpress_staffmember_status == 1 ) {
									vm2.staff_members.status = true;
								} else {
									vm2.staff_members.status = false;
								}
								vm2.staff_members.firstname = edit_staff_members_details.bookingpress_staffmember_firstname
								vm2.staff_members.lastname = edit_staff_members_details.bookingpress_staffmember_lastname
								vm2.staff_members.email = edit_staff_members_details.bookingpress_staffmember_email
								vm2.staff_members.phone = edit_staff_members_details.bookingpress_staffmember_phone
								vm2.staff_members.note = edit_staff_members_details.note								
								vm2.staff_members.visibility = edit_staff_members_details.visibility			
								vm2.staff_members.avatar_list = edit_staff_members_details.avatar_list
								vm2.staff_members.avatar_url = edit_staff_members_details.avatar_url
								vm2.staff_members.avatar_name = edit_staff_members_details.avatar_name
								vm2.bookingpress_tel_input_props.defaultCountry = edit_staff_members_details.bookingpress_staffmember_country_phone;
								vm2.$refs.bpa_tel_input_field._data.activeCountryCode = edit_staff_members_details.bookingpress_staffmember_country_phone;
								vm2.staff_members.staff_member_phone_country = edit_staff_members_details.bookingpress_staffmember_country_phone;
								vm2.bookingpress_configure_specific_workhour = edit_staff_members_details.bookingpress_configure_specific_workhour;						
								vm2.wpUsersList = edit_staff_members_details.wp_user_list


							}
							
							vm2.assign_service_form.assigned_service_list = response.data.assigned_service_data
							let counter = 0;
							response.data.assigned_service_data.forEach( (element, index) => {
								if( element.assign_service_display == true ){
									counter++;
								}
							});
							vm2.total_assigned_services = counter;

							<?php do_action( 'bookingpress_staff_member_edit_details_response' ); ?>
						} else {		
							vm2.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
							});						
						}
					}).catch(function(error){
						console.log(error)
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					});
				}, /*
				bookingpress_get_staffmember_special_day_details(selected_year = ''){					
					const vm = this
					var postdata = {}
					postdata.action = 'bookingpress_get_staffmember_special_day_details'					
					postdata.staffmember_id = vm.staff_members.update_id
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then(function(response){
						if(response.data.variant != 'error'){
							vm.staffmember_special_day_arr = response.data.special_day_data
						}else{
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: 'error_notification',
							});	
						}
					}).catch(function(error){
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error_notification',
						});
					});	
				}, */
				deleteStaffMember(delete_id){
					const vm2 = this
					var customer_action = { action: 'bookingpress_delete_staff_member', delete_id: delete_id,_wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' }
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
					.then(function(response){
						vm2.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
						});
						vm2.loadStaffmembers()
					}).catch(function(error){
						console.log(error)
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					});
				},				

				bulk_actions() {
					const vm = new Vue()
					const vm2 = this
					if(this.bulk_action == "bulk_action")
					{
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Please select any action.', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					}
					else
					{
						if(this.multipleSelection.length > 0 && this.bulk_action == "delete")
						{
							var stff_member_delete_data = {
								action: 'bookingpress_bulk_staff_member',
								delete_ids: this.multipleSelection,
								bulk_action: 'delete',
								_wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
							}
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( stff_member_delete_data ) )
							.then(function(response){
								vm2.$notify({
									title: response.data.title,
									message: response.data.msg,
									type: response.data.variant,
									customClass: response.data.variant+'_notification'
								});
								vm2.loadStaffmembers();
								vm2.multipleSelection = [];
								vm2.totalItems = vm2.items.length  
								vm2.is_multiple_checked = false;

							}).catch(function(error){
								console.log(error);
								vm2.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
								});
							});
						}
						else
						{
							vm2.$notify({
								title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
								message: '<?php esc_html_e( 'Please select one or more records.', 'bookingpress-appointment-booking' ); ?>',
								type: 'error',
								customClass: 'error_notification',
							});
						}
					}
				},
				bookingpress_assigned_service_data(){
					const vm = this
					var postdata = {};
					postdata.action = 'bookingpress_get_services_data';
					postdata.staff_member_id = vm.staff_members.update_id					
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then(function(response){
						if(response.data.variant != 'error'){
							vm.assign_service_form.assigned_service_list = response.data.assigned_service_data
							
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
				bookingpress_get_default_workhours(){					
					const vm = this
					var postdata = [];
					postdata.action = 'bookingpress_get_default_work_hours_details';
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify(postdata))
					.then(function(response) {			
						vm.work_hours_days_arr = response.data.data						
						response.data.data.forEach(function(currentValue, index, arr){
							vm.selected_break_timings[currentValue.day_name] = currentValue.break_times							
						});
						vm.workhours_timings = response.data.selected_workhours
						vm.default_break_timings = response.data.default_break_times
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
				open_add_break_modal_func(currentElement, selected_day){
					const vm = this
					vm.reset_add_break_Form();
					var dialog_pos = currentElement.target.getBoundingClientRect();
					vm.break_modal_pos = (dialog_pos.top - 100)+'px'
					vm.break_modal_pos_right = '100px'								
					vm.break_selected_day = selected_day				
					vm.open_add_break_modal = true
					if( typeof this.bpa_adjust_popup_position != 'undefined' ){
						this.bpa_adjust_popup_position( currentElement, 'div#staffmember_breaks_add_modal .el-dialog.bpa-dialog--add-break');
					}
				},
				close_add_break_model() {
					const vm = this
					vm.$refs['break_timings'].resetFields()
					vm.reset_add_break_Form()					
					vm.open_add_break_modal = false;
				},
				reset_add_break_Form(){
					const vm = this
					vm.break_timings.start_time = ''
					vm.break_timings.end_time = ''
					vm.break_timings.edit_index = ''
					vm.is_edit_break = 0;
				},
				bookingpress_set_workhour_value(worktime,work_hour_day) {
					const vm = this				
					if(vm.workhours_timings[work_hour_day].end_time == 'Off') {                    
						vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
							if(currentValue.day_name == work_hour_day) {
								currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
									if(currentValue2.start_time == worktime) {
										vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
									}
								});
							}
						});                
					} else if(worktime > vm.workhours_timings[work_hour_day].end_time ) {
						vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
							if(currentValue.day_name == work_hour_day) {                       
								currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
									if(currentValue2.start_time == worktime) {
										vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
									}
								});
							}
						});
					} else if(worktime != 'off' && vm.workhours_timings[work_hour_day].end_time == undefined) {
						vm.work_hours_days_arr.forEach(function(currentValue, index, arr){
							if(currentValue.day_name == work_hour_day) {                       
								currentValue.worktimes.forEach(function(currentValue2, index2, arr2){                                                    
									if(currentValue2.start_time == worktime) {
										vm.workhours_timings[work_hour_day].end_time = arr2[index2]['end_time'] ;
									}
								});
							}
						});
					}
				},
				edit_workhour_data(currentElement,break_start_time, break_end_time, day_name,index){
					const vm = this
					vm.reset_add_break_Form()
					var dialog_pos = currentElement.target.getBoundingClientRect();
					vm.break_modal_pos = (dialog_pos.top - 150)+'px'
					vm.break_modal_pos_right = '100px'
					vm.break_timings.start_time = break_start_time
					vm.break_timings.end_time = break_end_time
					vm.break_timings.edit_index = index
					vm.is_edit_break = 1;
					vm.open_add_break_modal = true						
					vm.break_selected_day = day_name
					if( typeof this.bpa_adjust_popup_position != 'undefined' ){
						this.bpa_adjust_popup_position( currentElement, 'div#staffmember_breaks_add_modal .el-dialog.bpa-dialog--add-break', 'bpa-bh__item');
					}
				},		
				save_break_data(){
					const vm = this
					var is_edit = 0;
					if( "undefined" == typeof vm.display_staff_working_hours || vm.display_staff_working_hours == true ){
						vm.$refs['break_timings'].validate((valid) => {                        
							if(valid) {    
								var update = 0;             
								if(vm.break_timings.start_time > vm.break_timings.end_time) {
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Start time is not greater than End time', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
								}else if(vm.break_timings.start_time == vm.break_timings.end_time) {                    
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Start time & End time are not same', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
								} else if(vm.selected_break_timings[vm.break_selected_day] != '' ) {                            
									vm.selected_break_timings[vm.break_selected_day].forEach(function(currentValue, index, arr) {
										if(is_edit == 0) {
											if(vm.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {    
												is_edit = 1;
												vm.$notify({
													title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
													message: '<?php esc_html_e('Please enter valid time for break', 'bookingpress-appointment-booking'); ?>',
													type: 'error',
													customClass: 'error_notification',
													duration:<?php echo intval($bookingpress_notification_duration); ?>,
												});                
											} else if(currentValue['start_time'] == vm.break_timings.start_time && currentValue['end_time'] == 
												vm.break_timings.end_time && ( vm.break_timings.edit_index != index || vm.is_edit_break == 0 )) {                                        
												is_edit = 1;
												vm.$notify({
													title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
													message: '<?php esc_html_e('Break time already added', 'bookingpress-appointment-booking'); ?>',
													type: 'error',
													customClass: 'error_notification',
													duration:<?php echo intval($bookingpress_notification_duration); ?>,
												});
											}else if(((currentValue['start_time'] < vm.break_timings.start_time  && currentValue['end_time'] > vm.break_timings.start_time) || (currentValue['start_time'] < vm.break_timings.end_time  && currentValue['end_time'] > vm.break_timings.end_time) || (currentValue['start_time'] > vm.break_timings.start_time && currentValue['end_time'] <= vm.break_timings.end_time) || (currentValue['start_time'] >= vm.break_timings.start_time && currentValue['end_time'] < vm.break_timings.end_time)) && (vm.break_timings.edit_index != index || vm.is_edit_break == 0) )  {                                       
												is_edit = 1;
												vm.$notify({
													title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
													message: '<?php esc_html_e('Break time already added', 'bookingpress-appointment-booking'); ?>',
													type: 'error',
													customClass: 'error_notification',
													duration:<?php echo intval($bookingpress_notification_duration); ?>,
												});                
											}                                                
										}    
									});
									if(is_edit == 0) {
										var formatted_start_time = formatted_end_time = '';                                 
										vm.default_break_timings.forEach(function(currentValue, index, arr) {
											if(currentValue.start_time == vm.break_timings.start_time) {
												formatted_start_time = currentValue.formatted_start_time;
											}
											if(currentValue.end_time == vm.break_timings.end_time) {
												formatted_end_time = currentValue.formatted_end_time;
											}
										});
										if(vm.break_selected_day != '' && vm.is_edit_break != 0) {
											vm.selected_break_timings[vm.break_selected_day].forEach(function(currentValue, index, arr) {
												if(index == vm.break_timings.edit_index) {
													currentValue.start_time = vm.break_timings.start_time;
													currentValue.end_time = vm.break_timings.end_time;
													currentValue.formatted_start_time = formatted_start_time;
													currentValue.formatted_end_time = formatted_end_time;
												}
											});   
										}else {
											vm.selected_break_timings[vm.break_selected_day].push({ start_time: vm.break_timings.start_time, end_time: vm.break_timings.end_time,formatted_start_time:formatted_start_time,formatted_end_time:formatted_end_time });                                    
										}
										vm.close_add_break_model()
									} 
								}  else {
									if(vm.workhours_timings[vm.break_selected_day].start_time > vm.break_timings.start_time || vm.workhours_timings[vm.break_selected_day].end_time < vm.break_timings.end_time) {
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Please enter valid time for break', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});                
									}else{
										var formatted_start_time = formatted_end_time = '';									
										vm.default_break_timings.forEach(function(currentValue, index, arr) {
											if(currentValue.start_time == vm.break_timings.start_time) {
												formatted_start_time = currentValue.formatted_start_time;
											}
											if(currentValue.end_time == vm.break_timings.end_time) {
												formatted_end_time = currentValue.formatted_end_time;
											}
										});        
										vm.selected_break_timings[vm.break_selected_day].push({ start_time: vm.break_timings.start_time, end_time: vm.break_timings.end_time,formatted_start_time:formatted_start_time,formatted_end_time:formatted_end_time });
										vm.close_add_break_model();
									}
								}
							}
						});
					} else {
						<?php do_action( 'bookingpress_save_external_staff_break_data' ); ?>
					}
				},
				bookingpress_remove_workhour(start_time, end_time, break_day){
					const vm = this
					if( "undefined" == typeof vm.selected_break_timings ){
						vm.selected_break_timings[break_day].forEach(function(currentValue, index, arr){
							if(currentValue.start_time == start_time && currentValue.end_time == end_time)
							{
								vm.selected_break_timings[break_day].splice(index, 1);
							}
						});
					} else {	

						<?php do_action('bookingpress_remove_workhours_break_times'); ?>
					}
				},
				bookingpress_check_workhour_value(workhour_time,work_hour_day) {	
					if(workhour_time == 'Off') {
						const vm = this
						vm.workhours_timings[work_hour_day].start_time = 'Off';
					}
				},				
				closeStaffmemberDayoff() {
					const vm = this;					
					vm.edit_staffmember_dayoff = '';
					vm.staffmember_dayoff_form.dayoff_name = '';
					vm.staffmember_dayoff_form.dayoff_date = '';
					vm.staffmember_dayoff_form.dayoff_date_range = '';
					vm.staffmember_dayoff_form.dayoff_date_end = '';
					vm.staffmember_dayoff_form.dayoff_repeat = false;

					vm.staffmember_dayoff_form.dayoff_repeat_frequency = '1';
					vm.staffmember_dayoff_form.dayoff_repeat_freq_type = 'year';
					vm.staffmember_dayoff_form.dayoff_repeat_duration = 'forever';
					vm.staffmember_dayoff_form.dayoff_repeat_times = '3';
					vm.staffmember_dayoff_form.dayoff_repeat_date = '<?php esc_html_e( date( 'Y-m-d', strtotime( '+1 year') ) ); ?>';

					vm.reset_staffmember_dayoff_form();
					vm.days_off_add_modal = false;
				},	
				bookingpress_after_select_staff_holiday(eventdata){
					var vm = this;
					vm.staffmember_dayoff_form.dayoff_date = eventdata[0];
					vm.staffmember_dayoff_form.dayoff_date_end = eventdata[1];					
				},				
				reset_staffmember_dayoff_form() {					
					const vm = this;
					setTimeout(function(){
						vm.$refs['staffmember_dayoff_form'].resetFields();
						vm.staffmember_dayoff_form.dayoff_name = '';
					},100);
					vm.edit_staffmember_dayoff = '';
					vm.staffmember_dayoff_form.dayoff_date = '';
					vm.staffmember_dayoff_form.dayoff_date_range = '';
					vm.staffmember_dayoff_form.dayoff_date_end = '';
					vm.staffmember_dayoff_form.dayoff_repeat = false;
				},
				addStaffmemberDayoff(staffmember_dayoff_form) {				
					const vm = this;
					
					this.$refs[staffmember_dayoff_form].validate((valid) => {
						if (valid && vm.disable_staff_holiday_btn == false) {							
							is_daysoff_exit = 0;						
							if(vm.staffmember_special_day_arr !='') {
								vm.staffmember_special_day_arr.forEach(function(item, index, arr) {									
																		
									if(vm.staffmember_dayoff_form.dayoff_date >= item.special_day_start_date && vm.staffmember_dayoff_form.dayoff_date <= item.special_day_end_date){
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
										is_daysoff_exit = 1;											
									}
									if(vm.staffmember_dayoff_form.dayoff_date_end >= item.special_day_start_date && vm.staffmember_dayoff_form.dayoff_date_end <= item.special_day_end_date){
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
										is_daysoff_exit = 1;
									}
									if(item.special_day_start_date >= vm.staffmember_dayoff_form.dayoff_date && item.special_day_start_date <= vm.staffmember_dayoff_form.dayoff_date_end){
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
										is_daysoff_exit = 1;											
									}
									if(item.special_day_end_date >= vm.staffmember_dayoff_form.dayoff_date && item.special_day_end_date <= vm.staffmember_dayoff_form.dayoff_date_end){
										vm.$notify({
											title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
											message: '<?php esc_html_e('Special day is already exists.', 'bookingpress-appointment-booking'); ?>',
											type: 'error',
											customClass: 'error_notification',
											duration:<?php echo intval($bookingpress_notification_duration); ?>,
										});
										is_daysoff_exit = 1;
									}
									
								});
							}
							vm.staffmember_dayoff_arr.forEach(function(item, index, arr) {

								if(vm.staffmember_dayoff_form.dayoff_date >= item.dayoff_date && vm.staffmember_dayoff_form.dayoff_date <= item.dayoff_date_end && vm.edit_staffmember_dayoff != item.id){
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_daysoff_exit = 1;									
								}								
								if(vm.staffmember_dayoff_form.dayoff_date_end >= item.dayoff_date && vm.staffmember_dayoff_form.dayoff_date_end <= item.dayoff_date_end && vm.edit_staffmember_dayoff != item.id){
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_daysoff_exit = 1;									
								}
								if(item.dayoff_date >= vm.staffmember_dayoff_form.dayoff_date && item.dayoff_date <= vm.staffmember_dayoff_form.dayoff_date_end && vm.edit_staffmember_dayoff != item.id){
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_daysoff_exit = 1;									
								}
								if(item.dayoff_date_end >= vm.staffmember_dayoff_form.dayoff_date && item.dayoff_date_end <= vm.staffmember_dayoff_form.dayoff_date_end && vm.edit_staffmember_dayoff != item.id){
									vm.$notify({
										title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
										message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval($bookingpress_notification_duration); ?>,
									});
									is_daysoff_exit = 1;
								}	
								
							});
							let daysoff_formdata = vm.staffmember_dayoff_form;

							if( 'undefined' != typeof daysoff_formdata.dayoff_repeat && true == daysoff_formdata.dayoff_repeat ){
								let repeat_frequency = daysoff_formdata.dayoff_repeat_frequency;
								let repeat_freq_type = daysoff_formdata.dayoff_repeat_freq_type;
								let daysoff_start_date = daysoff_formdata.dayoff_date;
								let daysoff_end_date = daysoff_formdata.dayoff_date_end;

								/** block if multiple days are selected & frequency set to days */

								let d1 = new Date( daysoff_start_date );
								let d2 = new Date( daysoff_end_date );

								let diff_in_time = d2.getTime() - d1.getTime();
								let diff_in_days = ( Math.round( diff_in_time / ( 1000 * 3600 * 24 ) ) ) + 1; /** +1 will includes the end date as well so we get the correct duration  */
								let diff_in_months = ( Math.round( diff_in_days / 30.44 ) % 12 );
				
								if( 'day' == repeat_freq_type && diff_in_days > repeat_frequency ){
									is_daysoff_exit = 1;
									vm.$notify({
										title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
										message: '<?php esc_html_e( 'Holiday duration must be shorter than the repeat frequency', 'bookingpress-appointment-booking' ); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
									});
								} else if( 'week' == repeat_freq_type && diff_in_days > ( repeat_frequency * 7 ) ){
									is_daysoff_exit = 1;
									vm.$notify({
										title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
										message: '<?php esc_html_e( 'Holiday duration must be shorter than the repeat frequency', 'bookingpress-appointment-booking' ); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
									});
								} else if( 'month' == repeat_freq_type && diff_in_months > repeat_frequency ){
									is_daysoff_exit = 1;
									vm.$notify({
										title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
										message: '<?php esc_html_e( 'Holiday duration must be shorter than the repeat frequency', 'bookingpress-appointment-booking' ); ?>',
										type: 'error',
										customClass: 'error_notification',
										duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
									});
								}
							}
							if(is_daysoff_exit == 0) {
								vm.disable_staff_holiday_btn = true;
								var postdata = [];
								postdata.action = 'bookingpress_validate_staffmember_daysoff'
								postdata.staffmember_id = vm.staff_members.update_id
								postdata.selected_date_range= vm.staffmember_dayoff_form.dayoff_date;
								postdata.selected_date_range_end = vm.staffmember_dayoff_form.dayoff_date_end;
								postdata.staff_form_data = vm.staffmember_dayoff_form;
								postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
								axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
								.then(function(response){
									vm.disable_staff_holiday_btn = false;
									vm.staff_daysoff_repeat_label = ( 'undefined' != typeof response.data.repeat_label ) ? response.data.repeat_label : '<?php esc_html_e( 'Once Off', 'bookingpress-appointment-booking') ?>';
									if(response.data.variant != 'undefined' && response.data.variant == 'warnning') {													
										vm.$confirm(response.data.msg, 'Warning', {
										confirmButtonText: '<?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?>',
										cancelButtonText: '<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>',
										type: 'warning'
										}).then(() => {
											if(vm.edit_staffmember_dayoff != '' ){
												vm.editStaffmemberDayoff();
											} else {
												vm.add_staffmember_daysoff();
											}
										});				
									}else if(response.data.variant != 'undefined' && response.data.variant  == 'success') {
										if(vm.edit_staffmember_dayoff != '' ){
											vm.editStaffmemberDayoff();
										} else {
											vm.add_staffmember_daysoff();
										}
									}
								}).catch(function(error){
									console.log(error);
									vm.$notify({
										title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
										message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
										type: 'error_notification',
									});
								});
							}
						} else {
							return false;
						}
					});
				},
				add_staffmember_daysoff(){
					const vm = this;
					var ilength = parseInt(vm.staffmember_dayoff_arr.length) + 1;
					let empDaysOffData = {};
					Object.assign(empDaysOffData, {id: ilength})
					Object.assign(empDaysOffData, {dayoff_date: vm.staffmember_dayoff_form.dayoff_date});
					Object.assign(empDaysOffData, {dayoff_date_end: vm.staffmember_dayoff_form.dayoff_date_end});
					Object.assign(empDaysOffData, {dayoff_name: vm.staffmember_dayoff_form.dayoff_name});
					Object.assign(empDaysOffData, {dayoff_repeat: vm.staffmember_dayoff_form.dayoff_repeat});
					Object.assign(empDaysOffData, {dayoff_repeat_label: '<?php esc_html_e( 'Once Off', 'bookingpress-appointment-booking' ); ?>'});

					if( true == empDaysOffData.dayoff_repeat ){
						Object.assign( empDaysOffData, {repeat_frequency: vm.staffmember_dayoff_form.dayoff_repeat_frequency });
						Object.assign( empDaysOffData, {repeat_frequency_type: vm.staffmember_dayoff_form.dayoff_repeat_freq_type });
						Object.assign( empDaysOffData, {repeat_duration: vm.staffmember_dayoff_form.dayoff_repeat_duration });
						Object.assign( empDaysOffData, {repeat_times: vm.staffmember_dayoff_form.dayoff_repeat_times });
						Object.assign( empDaysOffData, {repeat_date: vm.staffmember_dayoff_form.dayoff_repeat_date });
						console.log( vm.staff_daysoff_repeat_label );
						empDaysOffData.dayoff_repeat_label = vm.staff_daysoff_repeat_label;

					}

					console.log( empDaysOffData );

					vm.staffmember_dayoff_arr.push(empDaysOffData)
					vm.closeStaffmemberDayoff();				
					/* vm.bookingpress_staffmember_format_daysoff_time() */
				},
				bookingpress_staffmember_format_daysoff_time(){
					const vm = this
					var postdata = [];
					postdata.action = 'bookingpress_format_staffmember_daysoff_data'
					postdata.daysoff_data= vm.staffmember_dayoff_arr;
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then(function(response){
						if(response.data.variant == "success"){
							vm.staffmember_dayoff_arr = response.data.daysoff_details
						}
					}).catch(function(error){
						console.log(error);
					});
				},
				show_edit_dayoff_div(day_off_id, currentElement) {
					var vm = this
					vm.staffmember_dayoff_arr.forEach(function(item, index, arr){
						if (item.id == day_off_id) {
							vm.staffmember_dayoff_form.dayoff_name = item.dayoff_name
							vm.staffmember_dayoff_form.dayoff_date = item.dayoff_date
							vm.staffmember_dayoff_form.dayoff_date_end = item.dayoff_date_end
							vm.staffmember_dayoff_form.dayoff_date_range = [item.dayoff_date,item.dayoff_date_end];
							vm.staffmember_dayoff_form.dayoff_repeat = item.dayoff_repeat

							vm.staffmember_dayoff_form.dayoff_repeat_frequency = item.repeat_frequency;
							vm.staffmember_dayoff_form.dayoff_repeat_freq_type = item.repeat_frequency_type;
							vm.staffmember_dayoff_form.dayoff_repeat_duration = item.repeat_duration;
							vm.staffmember_dayoff_form.dayoff_repeat_times = item.repeat_times;
							vm.staffmember_dayoff_form.dayoff_repeat_date = item.repeat_date;
						}
						vm.edit_staffmember_dayoff = day_off_id;
					})
					var dialog_pos = currentElement.target.getBoundingClientRect();
					vm.days_off_modal_pos = (dialog_pos.top - 100)+'px'
					vm.days_off_modal_pos_right = '-'+(dialog_pos.right - 400)+'px';
					vm.days_off_add_modal = true

					if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
						vm.bpa_adjust_popup_position( currentElement, 'div#days_off_add_modal .el-dialog.bpa-dialog--days-off');
					}

				},
				delete_dayoff_div(day_off_id) {
					var vm = this
					vm.staffmember_dayoff_arr.forEach(function(item, index, arr)
					{
						if (item.id == day_off_id) {
							vm.staffmember_dayoff_arr.splice(index, 1);
						}
					})
				},				
				editStaffmemberDayoff() {					
					var vm = this
					var dayoff_id = vm.edit_staffmember_dayoff
					var dayoff_name = vm.staffmember_dayoff_form.dayoff_name
					var dayoff_date = vm.staffmember_dayoff_form.dayoff_date
					var dayoff_date_end = vm.staffmember_dayoff_form.dayoff_date_end
					var dayoff_repeat = vm.staffmember_dayoff_form.dayoff_repeat
					vm.staffmember_dayoff_arr.forEach(function(item, index, arr)
					{
						if(item.id == dayoff_id){
							item.dayoff_name = dayoff_name;
							item.dayoff_date = dayoff_date;
							item.dayoff_date_end = dayoff_date_end;
							item.dayoff_repeat = dayoff_repeat;
							if(dayoff_repeat){
								item.repeat_frequency = vm.staffmember_dayoff_form.dayoff_repeat_frequency;
								item.repeat_frequency_type = vm.staffmember_dayoff_form.dayoff_repeat_freq_type;
								item.repeat_duration = vm.staffmember_dayoff_form.dayoff_repeat_duration;
								item.repeat_times = vm.staffmember_dayoff_form.dayoff_repeat_times;
								item.repeat_date = vm.staffmember_dayoff_form.dayoff_repeat_date;
								item.dayoff_repeat_label = vm.staff_daysoff_repeat_label;
							}
						}
					});
					

					vm.closeStaffmemberDayoff();
					vm.bookingpress_staffmember_format_daysoff_time();
				}, /*
				bookingpress_yearly_off_details(selected_year = ''){
					const vm = this
					var selected_year_obj = (selected_year != '') ? new Date(selected_year) : new Date();
					var bookingpress_selected_year = selected_year_obj.getFullYear();
					var postdata = {}
					postdata.action = 'bookingpress_get_yearly_daysoff'
					postdata.selected_year = bookingpress_selected_year
					postdata.staffmember_id = vm.staff_members.update_id
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then(function(response){
						if(response.data.variant != 'error'){
							vm.staffmember_dayoff_arr = response.data.daysoff_data
						}else{
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: 'error_notification',
							});	
						}
					}).catch(function(error){
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error_notification',
						});
					});	
				}, */
				bookingpress_get_existing_user_details(bookingpress_selected_user_id){
					const vm = this
					var postData = { action:'bookingpress_get_existing_users_details', existing_user_id: bookingpress_selected_user_id, _wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.user_details != '' || response.data.user_details != undefined){
							vm.staff_members.firstname = response.data.user_details.user_firstname
							vm.staff_members.lastname = response.data.user_details.user_lastname
							vm.staff_members.email = response.data.user_details.user_email
						}
					}.bind(vm) )
					.catch( function (error) {
						console.log(error);
					});
				},
				canSelectRow(row,index) {
					return row.staffmember_bulk_action == false;
				},
				bookingpress_phone_country_change_func(bookingpress_country_obj){
					const vm = this					
					var bookingpress_selected_country = bookingpress_country_obj.iso2
					let exampleNumber = window.intlTelInputUtils.getExampleNumber( bookingpress_selected_country, true, 1 );                
					if( "" != exampleNumber ){
						vm.bookingpress_tel_input_props.inputOptions.placeholder = exampleNumber;
					}
					vm.staff_members.staff_member_phone_country = bookingpress_selected_country
					vm.staff_members.staff_member_dial_code = bookingpress_country_obj.dialCode;
				},
				isNumberValidate(evt, service_id) {				
					const vm = this
					const regex = /^(?!.*(,,|,\.|\.,|\.\.))[\d.,]+$/gm;
					let m;
					if((m = regex.exec(evt)) == null ) {	
						vm.assigned_services.forEach(function(selectedVal, index, arr) {			
							selectedVal.category_services.forEach(function(selectedVal1, index1, arr1) {
								if(selectedVal1.service_id == service_id ) {	
									selectedVal1.service_price_without_currency = '';
								}
							})
						})							
					}
				},
				Bookingpress_export_staffmember_data(currentElement){
					const vm = this;
					vm.ExportStaffmember = true;

					if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
						vm.bpa_adjust_popup_position( currentElement, 'div#staffmember_export_model .el-dialog.bpa-dialog--export-staffmembmers');
					}

				},
				close_export_staffmember_model(){
					const vm = this;
					vm.ExportStaffmember = false;
					vm.export_checked_field = ['first_name','last_name','email','phone','note','last_appointment','total_appointments','pending_appointments','assigned_services'];
				},
				bookingpress_export_staffmember(){
					const vm = this;	
					vm.is_export_button_disabled= true;
					vm.is_export_button_loader= '1';
					var bookingpress_search_data = { search_name: this.staff_member_search }
					var staffmember_export_data = {
						action:'bookingpress_export_staffmember_data',
						export_field: vm.export_checked_field,
						search_data : bookingpress_search_data,
						_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
					}								
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( staffmember_export_data ) )
					.then(function(response) {																		
						vm.is_export_button_disabled= false;
						vm.is_export_button_loader= '0';					
						vm.close_export_staffmember_model();									
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
							anchor.download = 'Bookingpress-export-staffmember.csv';					    
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
				delete_special_day_div(special_day_id){
					var vm = this
					vm.staffmember_special_day_arr.forEach(function(item, index, arr)
					{
						if (item.id == special_day_id) {
							vm.staffmember_special_day_arr.splice(index, 1);
						}
					})
				},
				show_edit_special_day_div(special_day_id, currentElement) {				
					const vm = this
					//vm.reset_staffmember_special_day();
					vm.staffmember_special_day_arr.forEach(function(item, index, arr)
					{
						if (item.id == special_day_id) {						
							vm.staffmember_special_day_form.special_day_date = [item.special_day_start_date,item.special_day_end_date]
							vm.staffmember_special_day_form.start_time = item.start_time
							vm.staffmember_special_day_form.end_time = item.end_time
							vm.staffmember_special_day_form.special_day_service = item.special_day_service														
							vm.staffmember_special_day_form.special_day_workhour = item.special_day_workhour
						
						}
						vm.edit_staffmember_special_day = special_day_id;
					})
					var dialog_pos = currentElement.target.getBoundingClientRect();
					vm.special_days_modal_pos = (dialog_pos.top - 100)+'px'
					vm.special_days_modal_pos_right = '-'+(dialog_pos.right - 420)+'px';
					vm.special_days_add_modal = true
					if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
						vm.bpa_adjust_popup_position( currentElement, 'div#special_days_add_modal .el-dialog.bpa-dialog--special-days');
					}
				},								
				closeStaffmemberSpecialday(){
					const vm = this;	
					vm.reset_staffmember_special_day();				
					vm.special_days_add_modal = false
				},
				reset_staffmember_special_day(){
					const vm = this;
					vm .edit_staffmember_special_day = ''
					vm.staffmember_special_day_form.special_day_date = [];					
					vm.staffmember_special_day_form.start_time = '';
					vm.staffmember_special_day_form.end_time = '';
					vm.staffmember_special_day_form.special_day_service = '';
					vm.staffmember_special_day_form.special_day_workhour = [];
					vm.disable_staff_special_day_btn = false;
					setTimeout(function(){
						vm.$refs['staffmember_special_day_form'].resetFields();
					},100);
				},
				bookingpress_add_special_day_period(){
					const vm = this;
					var ilength = 1;
					if( "undefined" != typeof vm.staffmember_special_day_form.special_day_workhour && vm.staffmember_special_day_form.special_day_workhour != '' ) {
						ilength = parseInt(vm.staffmember_special_day_form.special_day_workhour.length) + 1;
					}
					let WorkhourData = {};
					Object.assign(WorkhourData, {id: ilength})
					Object.assign(WorkhourData, {start_time: ''})
					Object.assign(WorkhourData, {end_time: ''})
					if( typeof vm.staffmember_special_day_form.special_day_workhour == "undefined"){
						vm.staffmember_special_day_form.special_day_workhour = []
					}
					vm.staffmember_special_day_form.special_day_workhour.push(WorkhourData)
				},
				bookingpress_remove_special_day_period(id){
					const vm = this
					vm.staffmember_special_day_form.special_day_workhour.forEach(function(item, index, arr)
					{
						if(id == item.id ){
							vm.staffmember_special_day_form.special_day_workhour.splice(index,1);
						}	
					})
				},
				add_staffmember_special_days(){
					const vm = this;
					var ilength = parseInt(vm.staffmember_special_day_arr.length) + 1;
					let empSpecialDayData = {};					
					Object.assign(empSpecialDayData, {id: ilength})
					Object.assign(empSpecialDayData, {special_day_start_date: vm.staffmember_special_day_form.special_day_date[0]})
					Object.assign(empSpecialDayData, {special_day_end_date: vm.staffmember_special_day_form.special_day_date[1]})
					Object.assign(empSpecialDayData, {start_time: vm.staffmember_special_day_form.start_time})
					Object.assign(empSpecialDayData, {end_time: vm.staffmember_special_day_form.end_time})
					Object.assign(empSpecialDayData, {special_day_service: vm.staffmember_special_day_form.special_day_service})
					Object.assign(empSpecialDayData, {special_day_workhour: vm.staffmember_special_day_form.special_day_workhour})					
					vm.staffmember_special_day_arr.push(empSpecialDayData)
					vm.closeStaffmemberSpecialday();
					vm.bookingpress_staffmember_format_special_day_time()
				},
				bookingpress_staffmember_format_special_day_time(){
					const vm = this
					var postdata = [];
					postdata.action = 'bookingpress_format_staffmember_special_days_data'
					postdata.special_days_data= vm.staffmember_special_day_arr;
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then(function(response){
						if(response.data.variant == "success"){
							vm.staffmember_special_day_arr = response.data.daysoff_details
						}
					}).catch(function(error){
						console.log(error);
					});
				},
				addStaffmemberSpecialday(staffmember_special_day_form) {
					const vm = this;
					if( "undefined" == typeof vm.display_staff_working_hours || vm.display_staff_working_hours == true ){	
						this.$refs[staffmember_special_day_form].validate((valid) => {
							if (valid) {
								vm.disable_staff_special_day_btn = true;
								var is_exit = 0;
								if(vm.staffmember_special_day_form.special_day_workhour!= undefined && vm.staffmember_special_day_form.special_day_workhour!= '') {
									vm.staffmember_special_day_form.special_day_workhour.forEach(function(item, index, arr){
										if(is_exit == 0 && (item.start_time == '' || item.end_time == '' || item.start_time == undefined || item.end_time == undefined)) {
											is_exit = 1;
											vm.$notify({
												title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
												message: '<?php esc_html_e( 'Please Enter Start Time and End Time', 'bookingpress-appointment-booking' ); ?>',
												type: 'error',
												customClass: 'error_notification',
												duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
											});                                
										}
									});
								} 
								if(vm.staffmember_special_day_arr != undefined && vm.staffmember_special_day_arr != '' ) {

									vm.staffmember_special_day_arr.forEach(function(item, index, arr) {

										if((vm.staffmember_special_day_form.special_day_date[0] == item.special_day_start_date || vm.staffmember_special_day_form.special_day_date[0] == item.special_day_end_date || ( vm.staffmember_special_day_form.special_day_date[0] >= item.special_day_start_date && vm.staffmember_special_day_form.special_day_date[0] <= item.special_day_end_date ) || vm.staffmember_special_day_form.special_day_date[1] == item.special_day_end_date || vm.staffmember_special_day_form.special_day_date[1] == item.special_day_start_date || (vm.staffmember_special_day_form.special_day_date[1] >= item.special_day_start_date && vm.staffmember_special_day_form.special_day_date[1] <= item.special_day_end_date) || (vm.staffmember_special_day_form.special_day_date[0] <= item.special_day_start_date && vm.staffmember_special_day_form.special_day_date[1] >= item.special_day_end_date) ) && vm.edit_staffmember_special_day != item.id && vm.edit_staffmember_special_day != item.id && is_exit == 0) {										
											is_exit = 0;
											if( vm.staffmember_special_day_form.special_day_service.length > 0 && item.special_day_service.length > 0) {
												item.special_day_service.forEach(function(item2,index2,arr2) {
													if( is_exit == 0 ) {
														if(vm.staffmember_special_day_form.special_day_service.includes(item2)) {
															is_exit = 1;
														}
													} 
												});	
											} else {
												is_exit = 1;
											}

											if(is_exit ==  1) {
												vm.$notify({
													title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
													message: '<?php esc_html_e( 'Special days already exists', 'bookingpress-appointment-booking' ); ?>',
													type: 'error',
													customClass: 'error_notification',
													duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
												});
												vm.disable_staff_special_day_btn = false;
											}
										}							
									});	
								}
								if(vm.staffmember_dayoff_arr != '') {
									vm.staffmember_dayoff_arr.forEach(function(item, index, arr){

										if (item.dayoff_date >= vm.staffmember_special_day_form.special_day_date[0] && item.dayoff_date <= vm.staffmember_special_day_form.special_day_date[1] ) {									
											vm.$notify({
												title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
												message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
												type: 'error',
												customClass: 'error_notification',
												duration:<?php echo intval($bookingpress_notification_duration); ?>,
											});
											is_exit = 1;									
										}
										if (item.dayoff_date_end >= vm.staffmember_special_day_form.special_day_date[0] && item.dayoff_date_end <= vm.staffmember_special_day_form.special_day_date[1] ) {									
											vm.$notify({
												title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
												message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
												type: 'error',
												customClass: 'error_notification',
												duration:<?php echo intval($bookingpress_notification_duration); ?>,
											});
											is_exit = 1;
										}
										if (vm.staffmember_special_day_form.special_day_date[0] >= item.dayoff_date && vm.staffmember_special_day_form.special_day_date[0] <= item.dayoff_date_end) {									
											vm.$notify({
												title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
												message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
												type: 'error',
												customClass: 'error_notification',
												duration:<?php echo intval($bookingpress_notification_duration); ?>,
											});
											is_exit = 1;
										}
										if (vm.staffmember_special_day_form.special_day_date[1] >= item.dayoff_date && vm.staffmember_special_day_form.special_day_date[1] <= item.dayoff_date_end) {									
											vm.$notify({
												title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
												message: '<?php esc_html_e('Holiday is already exists', 'bookingpress-appointment-booking'); ?>',
												type: 'error',
												customClass: 'error_notification',
												duration:<?php echo intval($bookingpress_notification_duration); ?>,
											});
											is_exit = 1;									
										}
									});
								}
								if(is_exit == 0) {
									var postdata = [];
									postdata.action = 'bookingpress_validate_staffmember_special_day'
									postdata.selected_date_range= vm.staffmember_special_day_form.special_day_date;
									postdata.special_day_workhour= vm.staffmember_special_day_form.special_day_workhour;														
									postdata.staffmember_id = vm.staff_members.update_id;								
									postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
									axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
									.then(function(response){
										if(response.data.variant != 'undefined' && response.data.variant == 'warnning') {
											vm.$confirm(response.data.msg, 'Warning', {
												confirmButtonText:  '<?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?>',
												cancelButtonText:  '<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>',
												type: 'warning',
												customClass: 'bpa_custom_warning_notification',
											}).then(() => {		
												if(vm.edit_staffmember_special_day != '' ){
													vm.editStaffmemberSpecialDay();
												} else {
													vm.add_staffmember_special_days();
												}
												vm.disable_staff_special_day_btn = false;
											});				
										}else if(response.data.variant != 'undefined' && response.data.variant  == 'success') {
											if(vm.edit_staffmember_special_day != '' ){
												vm.editStaffmemberSpecialDay();
											} else {
												vm.add_staffmember_special_days();
											}
											vm.disable_staff_special_day_btn = false;
										}
									}).catch(function(error){
										console.log(error);
										vm.$notify({
											title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
											message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
											type: 'error_notification',
										});
									});
								}	
							} else {
								return false;
							}
						});
					} else {
						<?php do_action( 'bookingpress_save_staff_external_special_data' ); ?>
					}
					
				},				
				editStaffmemberSpecialDay(){
					var vm = this
					var special_day_id = vm.edit_staffmember_special_day
					var special_day_date = vm.staffmember_special_day_form.special_day_date					
					var start_time = vm.staffmember_special_day_form.start_time					
					var end_time = vm.staffmember_special_day_form.end_time		
					var special_day_service = vm.staffmember_special_day_form.special_day_service			
					var special_day_workhour = vm.staffmember_special_day_form.special_day_workhour															
					vm.staffmember_special_day_arr.forEach(function(item, index, arr) {
						if(item.id == special_day_id) {	
							item.special_day_start_date = special_day_date[0]
							item.special_day_end_date = special_day_date[1]
							item.start_time = start_time
							item.end_time = end_time
							item.special_day_service = special_day_service
							item.special_day_workhour = special_day_workhour							
						}
					})
					vm.closeStaffmemberSpecialday();
					vm.bookingpress_staffmember_format_special_day_time()
				},
				open_assign_service_modal_func(currentElement){
					const vm = this
					vm.bookingpress_reset_assign_service_modal()
					var dialog_pos = currentElement.target.getBoundingClientRect();
					vm.assign_service_modal_pos = (dialog_pos.top - 90)+'px'
					vm.assign_service_modal_pos_right = '-'+(dialog_pos.right - 400)+'px';
					vm.open_assign_service_modal = true

					if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
						vm.bpa_adjust_popup_position( currentElement, '.el-dialog.bpa-dialog--add-assign-service');
					}

				},
				close_assign_service_modal_func(){
					const vm = this
					vm.open_assign_service_modal = false
				},
				bookingpress_reset_assign_service_modal(){
					const vm = this
					vm.assign_service_form.assign_service_id = '';
					vm.assign_service_form.assign_service_name = '';
					vm.assign_service_form.assign_service_price = '';
					vm.assign_service_form.assign_service_min_capacity = 1;
					vm.assign_service_form.assign_service_capacity = 1;
					vm.assign_service_form.is_service_edit = '0';
					vm.assign_service_form.bookingpress_custom_durations_data = [];
					<?php do_action( 'bookingpress_reset_assign_service_modal_outside' ); ?>

					vm.is_display_default_price_field = true
				},
				bookingpress_set_assign_service_name(selected_value){
					const vm = this
					vm.bookingpress_service_list.forEach(function(item,index,arr){
						item.category_services.forEach(function(item2 ,index1,arr2){							
							if(item2.service_id == selected_value ) {
								vm.assign_service_form.assign_service_name = item2.service_name;
								vm.assign_service_form.assign_service_price = item2.service_price_without_currency;
								vm.assign_service_form.assign_service_capacity = item2.service_max_capacity;
								vm.assign_service_form.assign_service_min_capacity = item2.service_min_capacity;
							}							
						});
					});
					<?php
					do_action('bookingpress_assign_custom_services');
					?>
				},
				bookingpress_save_assigned_service(){
					const vm = this
					var is_service_exist = 0;
					var is_service_edit = 0;
					var valid = true;
					var is_min_max_validation_msg = false;

					if(vm.assign_service_form.assign_service_name == "" || vm.assign_service_form.assign_service_price == '' || vm.assign_service_form.assign_service_capacity == undefined || vm.assign_service_form.assign_service_min_capacity == undefined ){
						valid = false;
					}

					if( vm.assign_service_form.assign_service_min_capacity > vm.assign_service_form.assign_service_capacity){
						valid = false;
						is_min_max_validation_msg = true;
					}

					if( valid == false ){
						if( is_min_max_validation_msg == true ){

							vm.$notify({
								title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
								message: '<?php esc_html_e( 'Service min capacity should not be greater than max capacity', 'bookingpress-appointment-booking' ); ?>',
								type: 'error',
								customClass: 'error_notification',
								duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
							});
						} else {

							vm.$notify({
								title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
								message: '<?php esc_html_e( 'Please select service and input service price...', 'bookingpress-appointment-booking' ); ?>',
								type: 'error',
								customClass: 'error_notification',
								duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
							});
						}
					} else {
						vm.assign_service_form.assigned_service_list.forEach(function(currentValue, index, arr){
							if(currentValue.assign_service_id == vm.assign_service_form.assign_service_id && vm.assign_service_form.is_service_edit == '0'){
								is_service_exist = 1
							}else if(currentValue.assign_service_id == vm.assign_service_form.assign_service_id && vm.assign_service_form.is_service_edit == '1'){
								is_service_edit = 1
							}
						});
						let close_assign_service_model = true;
						if(is_service_exist == 0){
							if(is_service_edit == 1){
								vm.assign_service_form.assigned_service_list.forEach(function(currentValue, index, arr){
									if(currentValue.assign_service_id == vm.assign_service_form.assign_service_id){
										vm.assign_service_form.assigned_service_list[index].assign_service_id = vm.assign_service_form.assign_service_id
										vm.assign_service_form.assigned_service_list[index].assign_service_name = vm.assign_service_form.assign_service_name
										vm.assign_service_form.assigned_service_list[index].assign_service_price = vm.assign_service_form.assign_service_price
										vm.assign_service_form.assigned_service_list[index].assign_service_capacity = vm.assign_service_form.assign_service_capacity
										vm.assign_service_form.assigned_service_list[index].assign_service_min_capacity = vm.assign_service_form.assign_service_min_capacity
										vm.assign_service_form.assigned_service_list[index].bookingpress_custom_durations_data = vm.assign_service_form.bookingpress_custom_durations_data
										vm.assign_service_form.assigned_service_list[index].assign_service_display = true;
										if(vm.assign_service_form.bookingpress_custom_durations_data != 'undefined' && vm.assign_service_form.bookingpress_custom_durations_data != '' && vm.assign_service_form.bookingpress_custom_durations_data != null) {
											vm.assign_service_form.bookingpress_custom_durations_data.forEach(function(item2,index2,arr2) {
												if(index2 == 0 ){
													vm.assign_service_form.assigned_service_list[index].assign_service_price = item2.staff_service_price
												}
											});
										}
										<?php do_action( 'bookingpress_modify_assign_service_form_for_edit_staffmember'); ?>
									}
								});
							}else{
								if(vm.assign_service_form.bookingpress_custom_durations_data != 'undefined' && vm.assign_service_form.bookingpress_custom_durations_data != '' && vm.assign_service_form.bookingpress_custom_durations_data != null) {
									vm.assign_service_form.bookingpress_custom_durations_data.forEach(function(item,index,arr) {
										if(index == 0 ){
											vm.assign_service_form.assign_service_price = item.staff_service_price
										}
									});
								}
								let bpa_assigned_service_data = {
									'assign_service_name': vm.assign_service_form.assign_service_name,
									'assign_service_price': vm.assign_service_form.assign_service_price,
									'assign_service_min_capacity': vm.assign_service_form.assign_service_min_capacity,
									'assign_service_capacity': vm.assign_service_form.assign_service_capacity,
									'assign_service_id': vm.assign_service_form.assign_service_id,
									'assign_service_display': true,
									'bookingpress_custom_durations_data' : vm.assign_service_form.bookingpress_custom_durations_data
								};
								<?php do_action( 'bookingpress_modify_assign_service_form_for_staffmember'); ?>
								vm.assign_service_form.assigned_service_list.push( bpa_assigned_service_data );
							}
							var bookingpress_format_assigned_service_amts = { action:'bookingpress_format_assigned_service_amounts', assigned_service_list : vm.assign_service_form.assigned_service_list, _wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' }
							axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_format_assigned_service_amts ) )
							.then(function(response) {
								vm.assign_service_form.assigned_service_list = response.data.assign_service_details;
								
								let counter = 0;
								response.data.assign_service_details.forEach( (element, index) => {
									if( element.assign_service_display == true || element.assign_service_display == 'true' ){
										counter++;
									}
								});
								vm.total_assigned_services = counter;

							}).catch(function(error){
								vm.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
								});
							});
							if( true == close_assign_service_model ){
								vm.close_assign_service_modal_func()
								vm.bookingpress_reset_assign_service_modal()
							}
						}else{
							vm.$notify({
								title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
								message: '<?php esc_html_e( 'Service already assigned to a staff member', 'bookingpress-appointment-booking' ); ?>',
								type: 'error',
								customClass: 'error_notification',
								duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
							});
						}
					}	
				},
				staffmember_service_price_validate(evt) {
					const vm = this
					const regex = /^(?!.*(,,|,\.|\.,|\.\.))[\d.,]+$/gm;
					let m;
					if((m = regex.exec(evt)) == null ) {
						vm.assign_service_form.assign_service_price = '';
					}
					var price_number_of_decimals = this.price_number_of_decimals;                
					if((evt != null && evt.indexOf(".")>-1 && (evt.split('.')[1].length > price_number_of_decimals))){
						vm.assign_service_form.assign_service_price = evt.slice(0, -1);
					}                
				},
				bookingpress_delete_assigned_service(delete_service_id){
					const vm = this
					vm.assign_service_form.assigned_service_list.forEach(function(currentValue, index, arr){
						if(delete_service_id == currentValue.assign_service_id){
							vm.assign_service_form.assigned_service_list.splice(index, 1);
						}
					});
				},
				bookingpress_edit_assigned_service(edit_assigned_service_id, currentElement){
					const vm = this
					vm.bookingpress_reset_assign_service_modal();
					
					var dialog_pos = currentElement.target.getBoundingClientRect();
					vm.assign_service_modal_pos = (dialog_pos.top - 110)+'px'
					vm.assign_service_modal_pos_right = '-'+(dialog_pos.right - 515)+'px';
					vm.open_assign_service_modal = true
					edit_assigned_service_id = ''+edit_assigned_service_id
					vm.assign_service_form.assigned_service_list.forEach(function(currentValue, index, arr){
						if(edit_assigned_service_id == currentValue.assign_service_id){
							vm.assign_service_form.assign_service_id = edit_assigned_service_id
							vm.assign_service_form.assign_service_name = currentValue.assign_service_name
							vm.assign_service_form.assign_service_price = currentValue.assign_service_price
							vm.assign_service_form.assign_service_min_capacity = currentValue.assign_service_min_capacity
							vm.assign_service_form.assign_service_capacity = currentValue.assign_service_capacity
							vm.assign_service_form.bookingpress_custom_durations_data = currentValue.bookingpress_custom_durations_data
							vm.assign_service_form.is_service_edit = '1'
							if(vm.assign_service_form.bookingpress_custom_durations_data !== 'undefined' && vm.assign_service_form.bookingpress_custom_durations_data != '' && vm.assign_service_form.bookingpress_custom_durations_data != null) {								
								vm.is_display_default_price_field = false;
							} else {
								vm.is_display_default_price_field = true;
							}
						}
					});

					if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
						vm.bpa_adjust_popup_position( currentElement, '.el-dialog.bpa-dialog--add-assign-service');
					}

				},
				open_shift_management_modal_func(){
					const vm = this	
					vm.bookingpress_reset_staff_member_form();					
					vm.open_shift_management_modal = true;
					<?php
						do_action('bookingpress_after_open_staff_shift_mgmt_modal');
					?>
				},
				bookingpress_open_shift_management_modal(edit_id, is_configure_specific_workhour = false){
					const vm = this
					vm.items.forEach(function(currentValue, index, arr){
						if(currentValue.staffmember_id == edit_id){
							vm.shift_mgmt_staff_name = currentValue.staffmember_firstname+" "+currentValue.staffmember_lastname;
						}
					});
					vm.open_shift_management_modal_func();
					vm.staff_members.update_id = edit_id;
					var selected_year = '';
					var selected_year_obj = (selected_year != '') ? new Date(selected_year) : new Date();
					var bookingpress_selected_year = selected_year_obj.getFullYear();
					var postdata = {}
					postdata.action = 'bookingpress_retrieve_staffmember_shift_managment_data'
					postdata.selected_year = bookingpress_selected_year
					postdata.is_configure_specific_workhour = is_configure_specific_workhour
					postdata.staffmember_id = vm.staff_members.update_id
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
					<?php do_action( 'bookingpress_staff_shift_management_modify_xhr_postdata'); ?>
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then(function(response){
						if(response.data.variant != undefined && response.data.variant == 'success'){
							/* Staffmember working hour data */
							if(is_configure_specific_workhour == 'true') {
								vm.bookingpress_configure_specific_workhour = response.data.bookingpress_configure_specific_workhour;
								if(response.data.workhours !== undefined && response.data.workhours != '') {
									vm.workhours_timings = response.data.workhours;									
									response.data.workhour_data.forEach(function(currentValue, index, arr){
										vm.work_hours_days_arr.forEach(function(currentValue2, index2, arr2){										
											if(currentValue2.day_name == currentValue.day_name) {											
												vm.work_hours_days_arr[index2]['break_times'] = currentValue.break_times							
											}
										});	
										vm.selected_break_timings[currentValue.day_name] = currentValue.break_times							
									});		
								}
							} else {
								vm.work_hours_days_arr = response.data.data		
								response.data.data.forEach(function(currentValue, index, arr){
									vm.selected_break_timings[currentValue.day_name] = currentValue.break_times							
								});
								vm.workhours_timings = response.data.selected_workhours
								vm.default_break_timings = response.data.default_break_times
							}
							/* Staffmember daysoff data */
							vm.staffmember_dayoff_arr = response.data.daysoff_data

							/* Staffmember specialday data */
							vm.staffmember_special_day_arr = response.data.special_day_data;
							if(typeof response.data.bookingpress_staff_assign_services_list != 'undefined' && response.data.bookingpress_staff_assign_services_list != ''){
								vm.bookingpress_staff_assign_services_list = response.data.bookingpress_staff_assign_services_list;
							}																
							<?php do_action( 'bookingpress_modify_staff_shift_management_xhr_response' ); ?>

						}else{
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: 'error_notification',
							});	
						}
					}).catch(function(error){
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error_notification',
						});
					});
				},
				bookingpress_close_shift_management_modal(){
					const vm = this
					vm.open_shift_management_modal = false
				},
				open_days_off_modal_func(currentElement){
					const vm = this
					vm.reset_staffmember_dayoff_form();
					vm.staffmember_dayoff_form.dayoff_name = '';
					var dialog_pos = currentElement.target.getBoundingClientRect();
					vm.days_off_modal_pos = (dialog_pos.top - 90)+'px'
					vm.days_off_modal_pos_right = '-'+(dialog_pos.right - 400)+'px';
					vm.days_off_add_modal = true;					
					if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
						vm.bpa_adjust_popup_position( currentElement, 'div#days_off_add_modal .el-dialog.bpa-dialog--days-off');
					}

				},
				open_special_days_func(currentElement){
					const vm = this
					vm.reset_staffmember_special_day();
					var dialog_pos = currentElement.target.getBoundingClientRect();
					vm.special_days_modal_pos = (dialog_pos.top - 100)+'px'
					vm.special_days_modal_pos_right = '-'+(dialog_pos.right - 400)+'px';
					vm.special_days_add_modal = true
					if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
						vm.bpa_adjust_popup_position( currentElement, 'div#special_days_add_modal .el-dialog.bpa-dialog--special-days');
					}
				},
				bookingpress_change_staffmember_status(staff_member_id, new_status_val){
					const vm = this
					var staff_members_action = { action: 'bookingpress_change_staff_member', staff_member_id: staff_member_id, status_val: new_status_val, _wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' }
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( staff_members_action ) )
					.then(function(response){
						if(response.data.variant != undefined && response.data.variant == 'success'){						
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
							});
							vm.loadStaffmembers()
						} else {
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
							});						
						}
					});
				},
				staffmemebr_daysoff_name_validation(value){
					const vm = this;
					vm.staffmember_dayoff_form.dayoff_name = value.trim();				
				},				
				updateStaffmemberPos: function(currentElement){

					var new_index = currentElement.newIndex;
					var old_index = currentElement.oldIndex;
					var staffmember_id = currentElement.item.dataset.staffmember_id;
					const vm = new Vue()
					const vm2 = this
					var postData = { action: 'bookingpress_position_staffmembers', old_position: old_index, new_position: new_index, currentPage : this.currentPage, perPage: this.perPage,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then(function(response){						

					}).catch(function(error){
						console.log(error);
						vm2.$notify({
							title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
							message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					});
				},	
				clearBulkAction(){
					const vm = this
					vm.bulk_action = 'bulk_action';
					vm.multipleSelection = []
					vm.items.forEach(function(selectedVal, index, arr) {            
						selectedVal.selected = false;
					})
					vm.is_multiple_checked = false;
				},
				selectAllStaffmembers(isChecked){
					const vm = this                
					if(isChecked)
					{    
						vm.items.forEach(function(selectedVal, index, arr) {
							if( selectedVal.staffmember_bulk_action == false) {                                   
								vm.multipleSelection.push(selectedVal.staffmember_id);
								selectedVal.selected = true;                                  
							}
						})                            
					}
					else
					{
						vm.clearBulkAction()
					}
				},
				
			<?php
			do_action( 'bookingpress_staff_member_external_vue_methods' );
		}

		function bookingpress_staff_members_on_load_methods_func() {
			?>	
				this.loadStaffmembers();
				this.bookingpress_get_default_workhours();
			<?php
			do_action( 'bookingpress_staff_member_external_onload_methods' );
		}

		function bookingpress_staff_members_dynamic_data_fields_func() {
			global $bookingpress_staff_member_vue_data_fields, $BookingPress,$BookingPressPro,$bookingpress_global_options, $bookingpress_pro_services, $bookingpress_bring_anyone_with_you;

			$bookingpress_pro_staff_members = new bookingpress_pro_staff_members();
			$bookingpress_options           = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_country_list      = $bookingpress_options['country_lists'];
			$bookingpress_pagination        = $bookingpress_options['pagination'];

			$bookingpress_staff_member_vue_data_fields['phone_countries_details'] = json_decode( $bookingpress_country_list );
			$bookingpress_staff_member_vue_data_fields['pagination_length']       = $bookingpress_pagination;

			$bookingpress_default_perpage_option                                = $BookingPress->bookingpress_get_settings( 'per_page_item', 'general_setting' );
			$bookingpress_staff_member_vue_data_fields['perPage']               = ! empty( $bookingpress_default_perpage_option ) ? $bookingpress_default_perpage_option : '10';
			$bookingpress_staff_member_vue_data_fields['pagination_length_val'] = ! empty( $bookingpress_default_perpage_option ) ? $bookingpress_default_perpage_option : '10';

			$bookingpress_staff_member_vue_data_fields['rules'] = array(
				'wp_user'   => array(
					array(
						'required' => true,
						'message'  => esc_html__( 'Please select Wordpress User', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
				'firstname' => array(
					array(
						'required' => true,
						'message'  => esc_html__( 'Please enter firstname', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
				'email'     => array(
					array(
						'required' => true,
						'message'  => esc_html__( 'Please enter email address', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
					array(
						'type'    => 'email',
						'message' => esc_html__( 'Please enter valid email address', 'bookingpress-appointment-booking' ),
						'trigger' => 'blur',
					),
				),
				'password'  => array(
					array(
						'required' => true,
						'message'  => esc_html__( 'Please Enter Password', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),

				),
			);

			$bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings( 'default_phone_country_code', 'general_setting' );

			$bookingpress_staff_member_vue_data_fields['bookingpress_tel_input_props']                = array(
				'defaultCountry' => $bookingpress_phone_country_option,
				'inputOptions' => array(
					'placeholder' => '',
				),
				'validCharactersOnly' => true,
			);	
			$bookingpress_staff_member_vue_data_fields['vue_tel_mode'] = 'international';
            $bookingpress_staff_member_vue_data_fields['vue_tel_auto_format'] = true;					
			
			if ( ! empty( $bookingpress_phone_country_option ) && $bookingpress_phone_country_option == 'auto_detect' ) {
				// Get visitors ip address
				$bookingpress_ip_address = $BookingPressPro->boookingpress_get_visitor_ip();
				try {
					$bookingpress_country_reader = new Reader( BOOKINGPRESS_PRO_LIBRARY_DIR . '/geoip/inc/GeoLite2-Country.mmdb' );
					$bookingpress_country_record = $bookingpress_country_reader->country( $bookingpress_ip_address );
					if ( ! empty( $bookingpress_country_record->country ) ) {
						$bookingpress_country_name     = $bookingpress_country_record->country->name;
						$bookingpress_country_iso_code = $bookingpress_country_record->country->isoCode;
						$bookingpress_staff_member_vue_data_fields['bookingpress_tel_input_props']['defaultCountry'] = $bookingpress_country_iso_code;
					}
				} catch ( Exception $e ) {
					$bookingpress_error_message = $e->getMessage();
				}
			}

			$bookingpress_staff_member_vue_data_fields['staff_members']['staff_member_phone_country'] = $bookingpress_phone_country_option;

			$bookingpress_staff_member_vue_data_fields['staff_dayoff_repeat_frequency_type_opts'] = array(
				'day' => esc_html__( 'Days', 'bookingpress-appointment-booking' ),
				'week' => esc_html__( 'Week', 'bookingpress-appointment-booking' ),
				'month' => esc_html__( 'Month', 'bookingpress-appointment-booking' ),
				'year' => esc_html__( 'Year', 'bookingpress-appointment-booking' )
			);

			$bookingpress_staff_member_vue_data_fields['repeat_duration_opts'] = array(
				'forever' => esc_html__( 'Forever', 'bookingpress-appointment-booking'),
				'no_of_times' => esc_html__( 'Specific No. of Times', 'bookingpress-appointment-booking' ),
				'until' => esc_html__( 'Until', 'bookingpress-appointment-booking')
			);

			$bookingpress_staff_member_vue_data_fields['staffmember_dayoff']      = array();
			$bookingpress_staff_member_vue_data_fields['days_off_year_filter']    = date( 'Y' );
			$bookingpress_staff_member_vue_data_fields['staff_daysoff_repeat_label'] = esc_html__( 'Once Off', 'bookingpress-appointment-booking' );
			$bookingpress_staff_member_vue_data_fields['staffmember_dayoff_form'] = array(
				'dayoff_name'   => '',
				'dayoff_date'   => '',
				'dayoff_date_end'   => '',
				'dayoff_date_range' => '',
				'dayoff_repeat' => false,
				'dayoff_repeat_frequency' => 1,
				'dayoff_repeat_freq_type' => 'year',
				'dayoff_repeat_duration' => 'forever',
				'dayoff_repeat_times' => 3,
				'dayoff_repeat_date' => date('Y-m-d', strtotime( '+1 year') )
			);
			$bookingpress_staff_member_vue_data_fields['rules_dayoff']            = array(
				'dayoff_name' => array(
					array(
						'required' => true,
						'message'  => __( 'Please enter name', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
				'dayoff_date' => array(
					array(
						'required' => true,
						'message'  => __( 'Please select date', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
			);

			$bookingpress_staff_member_vue_data_fields['staffmember_dayoff_arr']  = array();
			$bookingpress_staff_member_vue_data_fields['edit_staffmember_dayoff'] = '';

			$bookingpress_staff_member_vue_data_fields['staffmember_special_day_form'] = array(
				'special_day_date'     => '',
				'special_day_service'  => '',
				'start_time'           => '',
				'end_time'             => '',
				'special_day_workhour' => array(),
			);
			$bookingpress_staff_member_vue_data_fields['rules_special_day']            = array(
				'special_day_date' => array(
					array(
						'required' => true,
						'message'  => __( 'Please select date', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
				'start_time'       => array(
					array(
						'required' => true,
						'message'  => __( 'Select start time', 'bookingpress-appointment-booking' ),
						'trigger'  => 'change',
					),
				),
				'end_time'         => array(
					array(
						'required' => true,
						'message'  => __( 'Select end time', 'bookingpress-appointment-booking' ),
						'trigger'  => 'change',
					),
				),
			);

			$bookingpress_staff_member_vue_data_fields['edit_staffmember_special_day'] = '';
			$bookingpress_staff_member_vue_data_fields['staffmember_special_day_arr']  = array();
			$bookingpress_staff_member_vue_data_fields['bpa_staff_sm_shift_management_cls'] = array();
			$bookingpress_staff_member_vue_data_fields['add_staffmember_special_day']  = 0;
			$bookingpress_staff_member_vue_data_fields['bookingpress_services_list'] = $BookingPress->get_bookingpress_service_data_group_with_category();			
			$bookingpress_staff_member_vue_data_fields['bookingpress_staff_assign_services_list'] = $bookingpress_staff_member_vue_data_fields['bookingpress_services_list'];

			if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_export_staffmembers' ) ) {

				$bookingpress_staff_member_vue_data_fields['staffmember_export_field_list'] = array(
					array(
						'name' => 'first_name',
						'text' => __( 'First Name', 'bookingpress-appointment-booking' ),
					),
					array(
						'name' => 'last_name',
						'text' => __( 'Last Name', 'bookingpress-appointment-booking' ),
					),
					array(
						'name' => 'email',
						'text' => __( 'Email', 'bookingpress-appointment-booking' ),
					),
					array(
						'name' => 'phone',
						'text' => __( 'Phone', 'bookingpress-appointment-booking' ),
					),
					array(
						'name' => 'note',
						'text' => __( 'Note', 'bookingpress-appointment-booking' ),
					),
					array(
						'name' => 'assigned_services',
						'text' => __( 'Assigned Services', 'bookingpress-appointment-booking' ),
					),
				);
				$bookingpress_staff_member_vue_data_fields['export_checked_field']         = array( 'first_name', 'last_name', 'email', 'phone', 'note', 'assigned_services' );
				$bookingpress_staff_member_vue_data_fields['is_export_button_loader']      = '0';
				$bookingpress_staff_member_vue_data_fields['is_export_button_disabled']    = false;
				$bookingpress_staff_member_vue_data_fields['ExportStaffmember']            = false;
				$bookingpress_staff_member_vue_data_fields['is_mask_display']              = false;
				$bookingpress_staff_member_vue_data_fields['export_staffmember_top_pos']   = '210px';
				$bookingpress_staff_member_vue_data_fields['export_staffmember_right_pos'] = '80px';
				$bookingpress_staff_member_vue_data_fields['export_staffmember_left_pos']  = 'auto';				

			}
			$default_start_time    = '00:00:00';
			$default_end_time      = '23:55:00';
			$step_duration_val     = 05;
			$default_break_timings = array();
			$curr_time             = $tmp_start_time = date( 'H:i:s', strtotime( $default_start_time ) );
			$tmp_end_time          = date( 'H:i:s', strtotime( $default_end_time ) );

			do {
				$tmp_time_obj = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
				$end_time = $tmp_time_obj->format( 'H:i:s' );

					if($end_time == "00:00:00"){
						$end_time = "24:00:00";
					}

					$default_break_timings[] = array(
						'start_time'           => $curr_time,
						'formatted_start_time' => date( $bookingpress_options['wp_default_time_format'], strtotime( $curr_time ) ),
						'end_time'             => $end_time,
						'formatted_end_time' => date( $bookingpress_options['wp_default_time_format'], strtotime($end_time))." ".($end_time == "24:00:00" ? esc_html__('Next Day', 'bookingpress-appointment-booking') : '' ),
					);

					if($end_time == "24:00:00"){
						break;
					}

					$tmp_time_obj = new DateTime( $curr_time );
					$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
					$curr_time = $tmp_time_obj->format( 'H:i:s' );
			} while ( $curr_time <= $default_end_time );

			$bookingpress_staff_member_vue_data_fields['specialday_hour_list'] = $default_break_timings;
			$default_start_time     = '00:00:00';
			$default_end_time       = '23:25:00';
			$step_duration_val      = 05;
			$default_break_timings2 = array();
			$curr_time              = $tmp_start_time = date( 'H:i:s', strtotime( $default_start_time ) );
			$tmp_end_time           = date( 'H:i:s', strtotime( $default_end_time ) );
			do {
				$tmp_time_obj = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
				$end_time                 = $tmp_time_obj->format( 'H:i:s' );
				$default_break_timings2[] = array(
					'start_time'           => $curr_time,
					'formatted_start_time' => date( $bookingpress_options['wp_default_time_format'], strtotime( $curr_time ) ),
					'end_time'             => $end_time,
					'formatted_end_time'   => date( $bookingpress_options['wp_default_time_format'], strtotime( $end_time ) ),
				);
				$tmp_time_obj             = new DateTime( $curr_time );
				$tmp_time_obj->add( new DateInterval( 'PT' . $step_duration_val . 'M' ) );
				$curr_time = $tmp_time_obj->format( 'H:i:s' );
			} while ( $curr_time <= $default_end_time );
			$bookingpress_staff_member_vue_data_fields['specialday_break_hour_list'] = $default_break_timings2;
			$bookingpress_staff_member_vue_data_fields['is_display_default_price_field'] = true;
			$bookingpress_staff_member_vue_data_fields = apply_filters( 'bookingpress_staff_member_vue_dynamic_data_fields', $bookingpress_staff_member_vue_data_fields );
			$bookingpress_staff_member_vue_data_fields['disabledDates'] = '';
			$bookingpress_staff_member_vue_data_fields['is_bring_anyone_with_you_activated'] = $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation();

			$bookingpress_staff_member_vue_data_fields['open_assign_service_modal']      = false;
			$bookingpress_staff_member_vue_data_fields['assign_service_modal_pos']       = '250px';
			$bookingpress_staff_member_vue_data_fields['assign_service_modal_pos_right'] = '0px';
			$bookingpress_staff_member_vue_data_fields['assign_service_modal_pos_left']  = '0px';
			$bookingpress_staff_member_vue_data_fields['is_mask_display']                = false;

			$bookingpress_services_data = $BookingPress->get_bookingpress_service_data_group_with_category();
			if(!empty($bookingpress_services_data)){
				foreach($bookingpress_services_data as $k => $v){
					$bookingpress_category_services = !empty($v['category_services']) ? $v['category_services'] : array();
					if(!empty($bookingpress_category_services)){
						foreach($bookingpress_category_services as $k2 => $v2){
							$bookingpress_service_id = $v2['service_id'];
							$bookingpress_service_max_capacity = $bookingpress_pro_services->bookingpress_get_service_max_capacity($bookingpress_service_id);
							$bookingpress_services_data[$k]['category_services'][$k2]['service_max_capacity'] = $bookingpress_service_max_capacity;
							$bookingpress_service_min_capacity = $bookingpress_pro_services->bookingpress_get_service_min_capacity($bookingpress_service_id);
							$bookingpress_services_data[$k]['category_services'][$k2]['service_min_capacity'] = $bookingpress_service_min_capacity;
							$bookingpress_services_data[$k]['category_services'][$k2]['service_price_without_currency'] =  $v2['service_price_without_currency'];
						}
					}
				}
			}

			$bookingpress_staff_member_vue_data_fields['bookingpress_service_list'] = $bookingpress_services_data;
			$bookingpress_staff_member_vue_data_fields['assign_service_form']       = array(
				'assign_service_id'     => '',
				'assign_service_name'   => '',
				'assign_service_price'  => '',
				'assign_service_min_capacity' => 1,
				'assign_service_capacity' => 1,
				'is_service_edit'       => '0',
				'assigned_service_list' => array(),
				'assign_service_display' => true
			);

			$bookingpress_staff_member_vue_data_fields['total_assigned_services'] = 0;

			$bookingpress_payment_deafult_currency                              = $BookingPress->bookingpress_get_settings( 'payment_default_currency', 'payment_setting' );
			$bookingpress_payment_deafult_currency                              = $BookingPress->bookingpress_get_currency_symbol( $bookingpress_payment_deafult_currency );
			$bookingpress_staff_member_vue_data_fields['bookingpress_currency'] = $bookingpress_payment_deafult_currency;

			// Shift management data variables
			$bookingpress_staff_member_vue_data_fields['shift_mgmt_staff_name'] = '';
			$bookingpress_staff_member_vue_data_fields['open_shift_management_modal'] = false;

			// Days Off data variables
			$bookingpress_staff_member_vue_data_fields['days_off_add_modal']       = false;
			$bookingpress_staff_member_vue_data_fields['days_off_modal_pos']       = '0';
			$bookingpress_staff_member_vue_data_fields['days_off_modal_pos_right'] = '0';

			// Special Days variables
			$bookingpress_staff_member_vue_data_fields['special_days_add_modal']       = false;
			$bookingpress_staff_member_vue_data_fields['special_days_modal_pos']       = '0';
			$bookingpress_staff_member_vue_data_fields['special_days_modal_pos_right'] = '0';	
			$bookingpress_staff_member_vue_data_fields['disabledOtherDates'] = '';

			$bookingpress_staff_member_vue_data_fields['price_number_of_decimals'] = $BookingPress->bookingpress_get_settings('price_number_of_decimals', 'payment_setting');
			$bookingpress_staff_member_vue_data_fields['edit_index'] = 0;

			$bookingpress_staff_member_vue_data_fields['disable_staff_holiday_btn'] = false;
			$bookingpress_staff_member_vue_data_fields['disable_staff_special_day_btn'] = false;

			$bookingpress_staff_member_vue_data_fields['display_staff_working_hours'] = true;

			$bookingpress_staff_member_vue_data_fields = apply_filters('bookingpress_modify_staffmember_data_fields', $bookingpress_staff_member_vue_data_fields);

			echo wp_json_encode( $bookingpress_staff_member_vue_data_fields );
			
		}

		function bookingpress_staff_members_dynamic_helper_vars_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
				var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
				ELEMENT.locale(lang)
			<?php
		}

		function bookingpress_get_staffmember_func() {
			global $wpdb, $BookingPress,$BookingPressPro, $tbl_bookingpress_staffmembers, $tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings;

			$data = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'retrieve_staffmembers', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $data['variant'] = 'error';
                $data['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $data['msg'] = $bpa_error_msg;

                wp_send_json( $data );
                die;
            }
			$bookingpress_search_data  = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
			$bookingpress_search_query = '';

			if ( ! empty( $bookingpress_search_data['search_name'] ) ) {
				$bookingpress_search_staff_member_name = explode( ' ', $bookingpress_search_data['search_name'] );
				$bookingpress_search_query            .= ' AND (';
				$search_loop_counter                   = 1;
				foreach ( $bookingpress_search_staff_member_name as $bookingpress_search_staff_member_key => $bookingpress_search_staff_member_val ) {
					if ( $search_loop_counter > 1 ) {
						$bookingpress_search_query .= ' OR';
					}
					$bookingpress_search_query .= " (bookingpress_staffmember_login LIKE '%{$bookingpress_search_staff_member_val}%' OR bookingpress_staffmember_email LIKE '%{$bookingpress_search_staff_member_val}%' OR bookingpress_staffmember_firstname LIKE '%{$bookingpress_search_staff_member_val}%' OR bookingpress_staffmember_lastname LIKE '%{$bookingpress_search_staff_member_val}%')";

					$search_loop_counter++;
				}
				$bookingpress_search_query .= ' )';
			}
			if (! empty($bookingpress_search_data['search_date_range']) ) {
                $bookingpress_search_date         = $bookingpress_search_data['search_date_range'];
                $start_date                       = date('Y-m-d', strtotime($bookingpress_search_date[0]));
                $end_date                         = date('Y-m-d', strtotime($bookingpress_search_date[1]));
                $bookingpress_search_query .= " AND (bookingpress_staffmember_created BETWEEN '".$start_date." 00:00:00' AND '".$end_date." 23:59:59')";
            }
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$bookingpress_search_query  .= " AND (bookingpress_staffmember_id = '{$bookingpress_staffmember_id}')";
			}
		
			$total_staffmembers = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers . ' WHERE  bookingpress_staffmember_status != 4 ' . $bookingpress_search_query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
			$get_staffmembers   = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers . ' WHERE  bookingpress_staffmember_status != 4 ' . $bookingpress_search_query . ' ORDER by bookingpress_staffmember_position ASC',ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm

			$staffmembers = array();
			if ( ! empty( $get_staffmembers ) ) {
				$counter      = 1;
				$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
				foreach ( $get_staffmembers as $staffmember ) {
					$service_staffmember                                  = array();
					$service_staffmember['id']                            = $counter;
					$service_staffmember['staffmember_id']                = $staffmember['bookingpress_staffmember_id'];
					$service_staffmember['staffmember_firstname']         = sanitize_text_field( $staffmember['bookingpress_staffmember_firstname'] );
					$service_staffmember['staffmember_lastname']          = sanitize_text_field( $staffmember['bookingpress_staffmember_lastname'] );
					$service_staffmember['staffmember_email']             = sanitize_email( $staffmember['bookingpress_staffmember_email'] );
					$service_staffmember['staffmember_assigned_services'] = $this->bookingpress_get_staffmember_service( $staffmember['bookingpress_staffmember_id'], 1 );
					$service_staffmember['staffmember_phone']             = sanitize_text_field( $staffmember['bookingpress_staffmember_phone'] );
					$service_staffmember['staffmember_status']            = intval( $staffmember['bookingpress_staffmember_status'] );
					$bookingpress_avatar_url                              = '';
					$bookingpress_get_existing_avatar_url                 = $this->get_bookingpress_staffmembersmeta( $staffmember['bookingpress_staffmember_id'], 'staffmember_avatar_details' );
					$bookingpress_get_existing_avatar_url                 = ! empty( $bookingpress_get_existing_avatar_url ) ? maybe_unserialize( $bookingpress_get_existing_avatar_url ) : array();
					if ( ! empty( $bookingpress_get_existing_avatar_url[0]['url'] ) ) {
						$bookingpress_avatar_url = $bookingpress_get_existing_avatar_url[0]['url'];
					} else {
						$bookingpress_avatar_url = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
					}
					$service_staffmember['staffmember_avatar_url']  = $bookingpress_avatar_url;
					$bookingperss_appointments_data                 = '';
					$bookingperss_appointments_data                 = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_staff_member_id = %d AND bookingpress_appointment_date >= %s AND (bookingpress_appointment_status != '3' AND bookingpress_appointment_status != '4') ", $staffmember['bookingpress_staffmember_id'], $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
					$service_staffmember['staffmember_bulk_action'] = false;
					if ( ! empty( $bookingperss_appointments_data ) ) {
						$service_staffmember['staffmember_bulk_action'] = true;
					}


					$service_staffmember  = apply_filters('bookingpress_backend_modified_staffmember_list', $service_staffmember, $staffmember);

					//Get specific workhours enabled or not
					$bookingpress_configure_specific_workhour = $this->get_bookingpress_staffmembersmeta( $staffmember['bookingpress_staffmember_id'], 'bookingpress_configure_specific_workhour' );
					$service_staffmember['configure_specific_workhour'] = $bookingpress_configure_specific_workhour;
					$service_staffmember['selected'] = false;
					$staffmembers[] = $service_staffmember;
					$counter++;
				}
			}
			$data['items'] = $staffmembers;
			$data['total'] = count( $total_staffmembers );
			echo wp_json_encode( $data );
			exit;
		}

		function bookingpress_add_staff_member_func() {
			global $wpdb, $BookingPress,$tbl_bookingpress_staffmembers,$tbl_bookingpress_staff_member_workhours,$tbl_bookingpress_staffmembers_daysoff,$bookingpress_global_options,$BookingPressPro,$tbl_bookingpress_staffmembers_services,$tbl_bookingpress_staffmembers_special_day,$tbl_bookingpress_staffmembers_special_day_breaks,$tbl_bookingpress_services,$bookingpress_services;

			$response                   = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'add_staffmembers_details', true, 'bpa_wp_nonce' );
            
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
			$response['msg'] = '';

			$response['staffmember_id'] = '';
			$response['wpuser_id']      = '';

			if ( ! empty( $_REQUEST ) ) {

				$bookingpress_action  = ! empty( $_REQUEST['bookingpress_action'] ) ? ( sanitize_text_field( $_REQUEST['bookingpress_action'] ) ) : '';
				$bookingpress_existing_user_id = ! empty( $_REQUEST['wp_user'] ) ? trim( sanitize_text_field( $_REQUEST['wp_user'] ) ) : '';
				$bookingpress_firstname        = ! empty( $_REQUEST['firstname'] ) ? trim( stripslashes_deep(sanitize_text_field( $_REQUEST['firstname'] )) ) : '';
				$bookingpress_lastname         = ! empty( $_REQUEST['lastname'] ) ? trim( stripslashes_deep( sanitize_text_field( $_REQUEST['lastname'] )) ) : '';
				$bookingpress_email            = ! empty( $_REQUEST['email'] ) ? sanitize_email( $_REQUEST['email'] ) : '';
				$bookingpress_password         = ! empty( $_REQUEST['password'] ) ? $_REQUEST['password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_PEQUEST contains password

				
				if($bookingpress_action == 'bookingpress_edit_staffmember') {
					$is_service_exist = 0;
					$bookingpress_services_list = $wpdb->get_results( 'SELECT bookingpress_service_id FROM ' . $tbl_bookingpress_services,ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: 
					if(!empty($bookingpress_services_list))  {
						foreach($bookingpress_services_list as $key => $val) {					
							$bookingpress_service_id = $val['bookingpress_service_id'];
							$show_service_on_site = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'show_service_on_site');					
							if($show_service_on_site == true ) {
								$is_service_exist = $is_service_exist + 1;
							}
						}
					}				
					if(empty($_REQUEST['service_details']['assigned_service_list']) && $is_service_exist > 0 ) {					
						$response['msg'] = esc_html__( 'Please assign service to Staff member', 'bookingpress-appointment-booking' );
						echo wp_json_encode( $response );
						die();
					}
				}

				if ( strlen( $bookingpress_firstname ) > 255 ) {
					$response['msg'] = esc_html__( 'Firstname is too long...', 'bookingpress-appointment-booking' );
					echo wp_json_encode( $response );
					die();
				}

				if ( strlen( $bookingpress_lastname ) > 255 ) {
					$response['msg'] = esc_html__( 'Lastname is too long...', 'bookingpress-appointment-booking' );
					echo wp_json_encode( $response );
					die();
				}

				if ( strlen( $bookingpress_email ) > 255 ) {
					$response['msg'] = esc_html__( 'Email address is too long...', 'bookingpress-appointment-booking' );
					echo wp_json_encode( $response );
					die();
				}
				if ( $bookingpress_existing_user_id == 'add_new' && email_exists( $bookingpress_email ) ) {
					$response['msg'] = esc_html__( 'Email address is already exists', 'bookingpress-appointment-booking' );
					echo wp_json_encode( $response );
					die();
				}
				if ( $bookingpress_existing_user_id == 'add_new' && ! empty( $bookingpress_password ) ) {
					$wp_create_wp_user_id          = wp_create_user( $bookingpress_email, $bookingpress_password, $bookingpress_email );
					$bookingpress_existing_user_id = $wp_create_wp_user_id;
				}

				$bookingpress_phone         = ! empty( $_REQUEST['phone'] ) ? trim( sanitize_text_field( $_REQUEST['phone'] ) ) : '';
				$bookingpress_country_phone = ! empty( $_REQUEST['staff_member_phone_country'] ) ? trim( sanitize_text_field( $_REQUEST['staff_member_phone_country'] ) ) : '';
				$bookingpress_phone_dial_code = !empty($_REQUEST['staff_member_dial_code']) ? trim(sanitize_text_field($_REQUEST['staff_member_dial_code'])) : '';
				$bookingpress_note          = ! empty( $_REQUEST['note'] ) ? trim( sanitize_textarea_field( $_REQUEST['note'] ) ) : '';
				
				$bookingpress_update_id     = ! empty( $_REQUEST['update_id'] ) ? ( intval( $_REQUEST['update_id'] ) ) : 0;

				$bookingpress_status        = ! empty( $_REQUEST['status'] ) && $_REQUEST['status'] == 'false' && $bookingpress_update_id != 0 ? 0 : 1;
 
				$bookingpress_visibility    = ! empty( $_REQUEST['visibility'] ) ? sanitize_textarea_field( $_REQUEST['visibility'] )  : 'public';

				if( !empty($bookingpress_phone) && !empty( $bookingpress_phone_dial_code) ){

                    $customer_phone_pattern = '/(^\+'.$bookingpress_phone_dial_code.')/';
                    if( preg_match($customer_phone_pattern, $bookingpress_phone) ){
                        $bookingpress_phone = preg_replace( $customer_phone_pattern, '', $bookingpress_phone) ;
                    }
                }

				$booking_user_update_meta_details['first_name'] = $bookingpress_firstname;
				$booking_user_update_meta_details['last_name']  = $bookingpress_lastname;
				$booking_user_update_meta_details['staff_email'] = $bookingpress_email;

				if($bookingpress_action == 'bookingpress_edit_staffmember' ) {
					if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_basic_details' ) ) {
						if ( empty( $bookingpress_update_id ) ) {
							$staffmember_pos_data          = $wpdb->get_row('SELECT bookingpress_staffmember_position FROM ' . $tbl_bookingpress_staffmembers . ' ORDER BY bookingpress_staffmember_position DESC LIMIT 1', ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
							$staffmember_position = 0;						
							if (!empty($staffmember_pos_data) ) {
								$staffmember_position = $staffmember_pos_data['bookingpress_staffmember_position'] + 1;
							}					
							$bookingpress_staffmember_details = array(
								'bookingpress_staffmember_name' => ! empty( $bookingpress_firstname ) ? stripslashes_deep($bookingpress_firstname) : $bookingpress_email,
								'bookingpress_staffmember_firstname' => stripslashes_deep($bookingpress_firstname),
								'bookingpress_staffmember_lastname' => stripslashes_deep($bookingpress_lastname),
								'bookingpress_staffmember_phone' => $bookingpress_phone,
								'bookingpress_staffmember_country' => $bookingpress_country_phone,
								'bookingpress_staffmember_email' => $bookingpress_email,
								'bookingpress_staffmember_note' => stripslashes_deep($bookingpress_note),
								'bookingpress_staffmember_position' => $staffmember_position,
								'bookingpress_staffmember_status' => $bookingpress_status,
								'bookingpress_staffmember_visibility' => $bookingpress_visibility,
								'bookingpress_staffmember_country_dial_code' => $bookingpress_phone_dial_code,
							);

							$bookingpress_staffmember_details = $this->bookingpress_create_staffmember( $bookingpress_staffmember_details, $bookingpress_existing_user_id );

							if ( ! empty( $bookingpress_existing_user_id ) ) {
								do_action( 'bookingpress_user_update_meta', $bookingpress_existing_user_id, $booking_user_update_meta_details );
							}
							$userObj = new WP_User( $bookingpress_existing_user_id );
							$userObj->add_role( 'bookingpress-staffmember' );
							$this->bookingpress_staffmember_assign_capability( $bookingpress_existing_user_id );
							if ( is_array( $bookingpress_staffmember_details ) && isset( $bookingpress_staffmember_details['bookingpress_staffmember_id'] ) && isset( $bookingpress_staffmember_details['bookingpress_wpuser_id'] ) ) {

								$bookingpress_update_id        = $bookingpress_staffmember_details['bookingpress_staffmember_id'];
								$bookingpress_existing_user_id = $bookingpress_staffmember_details['bookingpress_wpuser_id'];
							}
						} else {
							$bookingpress_existing_staffmember_details = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_wpuser_id FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $bookingpress_update_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm

							if ( ! empty( $bookingpress_existing_staffmember_details ) && ! empty( $bookingpress_existing_user_id ) ) {
								$bookingpress_existing_wp_user_id = ! empty( $bookingpress_existing_staffmember_details['bookingpress_wpuser_id'] ) ? $bookingpress_existing_staffmember_details['bookingpress_wpuser_id'] : '';
								if ( $bookingpress_existing_wp_user_id != $bookingpress_existing_user_id ) {
									$userObj = new WP_User( $bookingpress_existing_wp_user_id );
									if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember', $bookingpress_existing_wp_user_id ) ) {
										$userObj->remove_role( 'bookingpress-staffmember' );
										$staffmembers_default_cap = $bookingpress_global_options->bookingpress_global_options();
										$staffmembers_default_cap = ! empty( $staffmembers_default_cap['staffmember_default_cap'] ) ? $staffmembers_default_cap['staffmember_default_cap'] : array();
										foreach ( $staffmembers_default_cap as $staffmembers_default_cap_key => $staffmembers_default_cap_val ) {
											if ( $userObj->has_cap( $staffmembers_default_cap_val ) ) {
												$userObj->remove_cap( $staffmembers_default_cap_val );
											}
										}
									}
								}
								if ( ! empty( $bookingpress_password ) ) {
									$update_data = array(
										'ID'        => $bookingpress_existing_user_id,
										'user_pass' => $bookingpress_password,
									);
									$user_ID     = wp_update_user( $update_data );
								}
								$userObj = new WP_User( $bookingpress_existing_user_id );
								if ( ! $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember', $bookingpress_existing_user_id ) ) {
									$userObj->add_role( 'bookingpress-staffmember' );
								}
								$this->bookingpress_staffmember_assign_capability( $bookingpress_existing_user_id );

								do_action( 'bookingpress_user_update_meta', $bookingpress_existing_user_id, $booking_user_update_meta_details );
								$bookingpress_update_fields          = array(
									'bookingpress_staffmember_login' => $bookingpress_email,
									'bookingpress_staffmember_firstname' => $bookingpress_firstname,
									'bookingpress_staffmember_lastname' => $bookingpress_lastname,
									'bookingpress_staffmember_email' => $bookingpress_email,
									'bookingpress_staffmember_phone' => $bookingpress_phone,
									'bookingpress_staffmember_country_phone' => $bookingpress_country_phone,
									'bookingpress_staffmember_status' => $bookingpress_status,
									'bookingpress_staffmember_country_dial_code' => $bookingpress_phone_dial_code,
									'bookingpress_wpuser_id' => $bookingpress_existing_user_id,
								);
								$bookingpress_update_where_condition = array(
									'bookingpress_staffmember_id' => $bookingpress_update_id,
								);
								$wpdb->update( $tbl_bookingpress_staffmembers, $bookingpress_update_fields, $bookingpress_update_where_condition );
								$this->update_bookingpress_staffmembersmeta( $bookingpress_update_id, 'staffmember_note', $bookingpress_note );
								$this->update_bookingpress_staffmembersmeta( $bookingpress_update_id, 'staffmember_visibility', $bookingpress_visibility );								
							}
						}

						$user_image_details = array();
						if ( ! empty( $_REQUEST['avatar_name'] ) && ! empty( $_REQUEST['avatar_url'] ) ) {
							$staffmember_img_url  = esc_url_raw( $_REQUEST['avatar_url'] );
							$staffmember_img_name = sanitize_file_name( $_REQUEST['avatar_name'] );

							$bookingpress_get_existing_avatar_details = $this->get_bookingpress_staffmembersmeta( $bookingpress_update_id, 'staffmember_avatar_details' );
							$bookingpress_get_existing_avatar_details = ! empty( $bookingpress_get_existing_avatar_details ) ? maybe_unserialize( $bookingpress_get_existing_avatar_details ) : array();
							$bookingpress_get_existing_avatar_url     = ! empty( $bookingpress_get_existing_avatar_details[0]['url'] ) ? $bookingpress_get_existing_avatar_details[0]['url'] : '';

							if ( $staffmember_img_url != $bookingpress_get_existing_avatar_url ) {
								global $BookingPress;
								$upload_dir                 = BOOKINGPRESS_UPLOAD_DIR . '/';
								$bookingpress_new_file_name = current_time( 'timestamp' ) . '_' . $staffmember_img_name;
								$upload_path                = $upload_dir . $bookingpress_new_file_name;
								$bookingpress_upload_res = new bookingpress_fileupload_class( $staffmember_img_url, true );
								$bookingpress_upload_res->check_cap          = true;
								$bookingpress_upload_res->check_nonce        = true;
								$bookingpress_upload_res->nonce_data         = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
								$bookingpress_upload_res->nonce_action       = 'bpa_wp_nonce';
								$bookingpress_upload_res->check_only_image   = true;
								$bookingpress_upload_res->check_specific_ext = false;
								$bookingpress_upload_res->allowed_ext        = array();
                                $upload_response = $bookingpress_upload_res->bookingpress_process_upload( $upload_path );
								//$bookingpress_upload_res    = $BookingPress->bookingpress_file_upload_function( $staffmember_img_url, $upload_path );

								if( true == $upload_response ){
									$user_image_new_url   = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpress_new_file_name;
									$user_image_details[] = array(
										'name' => $bookingpress_new_file_name,
										'url'  => $user_image_new_url,
									);

									$this->update_bookingpress_staffmembersmeta( $bookingpress_update_id, 'staffmember_avatar_details', maybe_serialize( $user_image_details ) );

									$bookingpress_file_name_arr = explode( '/', $staffmember_img_url );
									$bookingpress_file_name     = $bookingpress_file_name_arr[ count( $bookingpress_file_name_arr ) - 1 ];
									if( file_exists( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name ) ){
										@unlink( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name );
									}
									if ( ! empty( $bookingpress_get_existing_avatar_url ) ) {
										// Remove old image and upload new image
										$bookingpress_file_name_arr = explode( '/', $bookingpress_get_existing_avatar_url );
										$bookingpress_file_name     = $bookingpress_file_name_arr[ count( $bookingpress_file_name_arr ) - 1 ];
										if( file_exists( BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpress_file_name ) ){
											@unlink( BOOKINGPRESS_UPLOAD_DIR . '/' . $bookingpress_file_name );
										}
									}
								}

							}
						} else {
							$this->update_bookingpress_staffmembersmeta( $bookingpress_update_id, 'staffmember_avatar_details', maybe_serialize( $user_image_details ) );
						}
					}
				
					// save services setails

					if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_services' ) ) {
						$wpdb->delete( $tbl_bookingpress_staffmembers_services, array( 'bookingpress_staffmember_id' => $bookingpress_update_id ) );

						if ( ! empty( $_REQUEST['service_details']['assigned_service_list'] ) ) {
							$bookingpress_assigned_service_list = ! empty( $_REQUEST['service_details']['assigned_service_list'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['service_details']['assigned_service_list'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
							foreach ( $bookingpress_assigned_service_list as $bookingpress_service_key => $bookingpress_service_val ) {

								$bookingpress_db_fields = array(
									'bookingpress_staffmember_id' => intval( $bookingpress_update_id ),
									'bookingpress_service_id' => intval( $bookingpress_service_val['assign_service_id'] ),
									'bookingpress_service_price' => floatval( $bookingpress_service_val['assign_service_price'] ),
									'bookingpress_service_capacity' => intval($bookingpress_service_val['assign_service_capacity']),
									'bookingpress_service_min_capacity' => intval($bookingpress_service_val['assign_service_min_capacity']),
									'bookingpress_created_date' => current_time( 'mysql' ),
								);

								$wpdb->insert( $tbl_bookingpress_staffmembers_services, $bookingpress_db_fields );
							}
						}
					}	
				}	

				if( $bookingpress_action == 'bookingpress_shift_managment') {
					// Save workhours details
					if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_workhours' ) ) {
						$bookingpress_configure_specific_workhour = ! empty( $_REQUEST['bookingpress_configure_specific_workhour'] ) ? sanitize_text_field( $_REQUEST['bookingpress_configure_specific_workhour'] ) : 'false';
						$this->update_bookingpress_staffmembersmeta( $bookingpress_update_id, 'bookingpress_configure_specific_workhour', $bookingpress_configure_specific_workhour );

						$bookingpress_delete_staff_workhours_where_condition = array(
							'bookingpress_staffmember_id' => $bookingpress_update_id,
							'bookingpress_staffmember_workhours_is_break' => 0,
						);
						$bookingpress_delete_staff_workhours_where_condition = apply_filters('bookingpress_delete_staff_workhours_where_condition_filter', $bookingpress_delete_staff_workhours_where_condition, $_REQUEST);
						$wpdb->delete( $tbl_bookingpress_staff_member_workhours, $bookingpress_delete_staff_workhours_where_condition );

						$bookingpress_delete_staff_workhours_break_where_condition = array(
							'bookingpress_staffmember_id' => $bookingpress_update_id,
							'bookingpress_staffmember_workhours_is_break' => 1,
						);
						$bookingpress_delete_staff_workhours_break_where_condition = apply_filters('bookingpress_delete_staff_workhours_break_where_condition_filter', $bookingpress_delete_staff_workhours_break_where_condition, $_REQUEST);
						$wpdb->delete( $tbl_bookingpress_staff_member_workhours, $bookingpress_delete_staff_workhours_break_where_condition );
						

						if ( ! empty( $bookingpress_configure_specific_workhour ) && $bookingpress_configure_specific_workhour == 'true' ) {

							$_REQUEST['workhours_details'] = !empty($_REQUEST['workhours_details']) ? json_decode( stripslashes_deep( $_REQUEST['workhours_details'] ), true ) : array(); //phpcs:ignore

							$bookingpress_workhour_days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
							foreach ( $bookingpress_workhour_days as $workhour_key => $workhour_val ) {
								$workhour_start_time = ! empty( $_REQUEST['workhours_details'][ $workhour_val ]['start_time'] ) ? sanitize_text_field( $_REQUEST['workhours_details'][ $workhour_val ]['start_time'] ) : '09:00:00';
								$workhour_end_time   = ! empty( $_REQUEST['workhours_details'][ $workhour_val ]['end_time'] ) ? sanitize_text_field( $_REQUEST['workhours_details'][ $workhour_val ]['end_time'] ) : '17:00:00';

								if ( $workhour_start_time == 'Off' ) {
									$workhour_start_time = null;
								}
								if ( $workhour_end_time == 'Off' ) {
									$workhour_end_time = null;
								}
								$bookingpress_db_fields = array(
									'bookingpress_staffmember_id' => $bookingpress_update_id,
									'bookingpress_staffmember_workday_key' => $workhour_val,
									'bookingpress_staffmember_workhours_start_time' => $workhour_start_time,
									'bookingpress_staffmember_workhours_end_time' => $workhour_end_time,
								);
								$bookingpress_db_fields = apply_filters('bookingpress_modify_staff_workhours_details', $bookingpress_db_fields, $_REQUEST);
								$wpdb->insert( $tbl_bookingpress_staff_member_workhours, $bookingpress_db_fields );
							}

							$_REQUEST['break_details'] = !empty($_REQUEST['break_details']) ? json_decode( stripslashes_deep( $_REQUEST['break_details'] ), true ) : array(); //phpcs:ignore

							$bookingpress_break_days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
							foreach ( $bookingpress_break_days as $break_key => $break_val ) {
								$bookingpress_day_break_details = ! empty( $_REQUEST['break_details'][ $break_val ] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['break_details'][ $break_val ] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
								if ( ! empty( $bookingpress_day_break_details ) ) {
									foreach ( $bookingpress_day_break_details as $break_day_arr_key => $break_day_arr_val ) {
										$break_start_time       = $break_day_arr_val['start_time'];
										$break_end_time         = $break_day_arr_val['end_time'];
										$bookingpress_db_fields = array(
											'bookingpress_staffmember_id' => $bookingpress_update_id,
											'bookingpress_staffmember_workday_key' => $break_val,
											'bookingpress_staffmember_workhours_start_time' => $break_start_time,
											'bookingpress_staffmember_workhours_end_time' => $break_end_time,
											'bookingpress_staffmember_workhours_is_break' => 1,
										);
										$bookingpress_db_fields = apply_filters('bookingpress_modify_staff_workhours_details', $bookingpress_db_fields, $_REQUEST);
										$wpdb->insert( $tbl_bookingpress_staff_member_workhours, $bookingpress_db_fields );
									}
								}
							}
						}
					}

					/* save Staffmember day off*/
					if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_daysoffs' ) ) {
						$wpdb->delete( $tbl_bookingpress_staffmembers_daysoff, array( 'bookingpress_staffmember_id' => $bookingpress_update_id ) );
						if ( ! empty( $_REQUEST['dayoff_details'] ) ) {

							$_REQUEST['dayoff_details'] = !empty( $_REQUEST['dayoff_details']) ? json_decode( stripslashes_deep( $_REQUEST['dayoff_details'] ), true ) : array(); //phpcs:ignore

							$bpa_daysoff_opts = array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['dayoff_details'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
							
							foreach ( $bpa_daysoff_opts as $daysoff ) {
								$daysoff_date  = ! empty( $daysoff['dayoff_date'] ) ? $daysoff['dayoff_date'] : '';
								$dayoff_date_end  = ! empty( $daysoff['dayoff_date_end'] ) ? $daysoff['dayoff_date_end'] : '';
								$dayoff_name   = ! empty( $daysoff['dayoff_name'] ) ? $daysoff['dayoff_name'] : '';
								$dayoff_repeat = ( ! empty( $daysoff['dayoff_repeat'] ) && $daysoff['dayoff_repeat'] == true ) ? 1 : 0;

								if($dayoff_date_end == null || $dayoff_date_end == 'null' || empty($dayoff_date_end)){
									$dayoff_date_end = $daysoff_date;
								}

								$daysoff_repeat_frequency = !empty( $daysoff['repeat_frequency'] ) ? intval( $daysoff['repeat_frequency'] ) : 1;
								$daysoff_repeat_frequency_type = !empty( $daysoff['repeat_frequency_type'] ) ? sanitize_text_field( $daysoff['repeat_frequency_type'] ) : 'year';
								$daysoff_repeat_duration = !empty( $daysoff['repeat_duration'] ) ? sanitize_text_field( $daysoff['repeat_duration'] ) : 'forever';
								$daysoff_repeat_times	 = !empty( $daysoff['repeat_times'] ) ? intval( $daysoff['repeat_times'] ) : 1;
								$daysoff_repeat_date	 = !empty( $daysoff['repeat_date'] ) ? sanitize_text_field( $daysoff['repeat_date'] ) : date('Y-m-d', strtotime( '+1 year' ) );

								$daysoff_repeat_date = apply_filters( 'bookingpress_change_repeat_date_timezone_to_wp', $daysoff_repeat_date );

								$args = array(
									'bookingpress_staffmember_id' => $bookingpress_update_id,
									'bookingpress_staffmember_daysoff_name' => $dayoff_name,
									'bookingpress_staffmember_daysoff_date' => $daysoff_date,
									'bookingpress_staffmember_daysoff_enddate' => $dayoff_date_end,
									'bookingpress_staffmember_daysoff_repeat' => $dayoff_repeat,
									'bookingpress_staffmember_daysoff_repeat_frequency' => $daysoff_repeat_frequency,
									'bookingpress_staffmember_daysoff_repeat_frequency_type' => $daysoff_repeat_frequency_type,
									'bookingpress_staffmember_daysoff_repeat_duration' => $daysoff_repeat_duration,
									'bookingpress_staffmember_daysoff_repeat_times' => $daysoff_repeat_times,
									'bookingpress_staffmember_daysoff_repeat_date' => $daysoff_repeat_date,
									'bookingpress_staffmember_daysoff_created' => current_time( 'mysql' ),
								);

								$wpdb->insert( $tbl_bookingpress_staffmembers_daysoff, $args );

								$bookingpress_child_holiday_dates = array();
								if($daysoff_date != $dayoff_date_end){                    									
									$startDate = strtotime($daysoff_date)+86400;
									$endDate = strtotime($dayoff_date_end);                 
									for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
										$date = date('Y-m-d', $currentDate);
										$bookingpress_child_holiday_dates[] = $date;
									}                    
								} 
								if(!empty($bookingpress_child_holiday_dates)){
									$dayoff_parent_id = $wpdb->insert_id;
									foreach($bookingpress_child_holiday_dates as $holiday_date){
										$args          = array(
											'bookingpress_staffmember_id' => $bookingpress_update_id,
											'bookingpress_staffmember_daysoff_name' => $dayoff_name,
											'bookingpress_staffmember_daysoff_date' => $holiday_date,
											'bookingpress_staffmember_daysoff_enddate' => $dayoff_date_end,
											'bookingpress_staffmember_daysoff_parent' => $dayoff_parent_id,
											'bookingpress_staffmember_daysoff_repeat' => $dayoff_repeat,
											'bookingpress_staffmember_daysoff_repeat_frequency' => $daysoff_repeat_frequency,
											'bookingpress_staffmember_daysoff_repeat_frequency_type' => $daysoff_repeat_frequency_type,
											'bookingpress_staffmember_daysoff_repeat_duration' => $daysoff_repeat_duration,
											'bookingpress_staffmember_daysoff_repeat_times' => $daysoff_repeat_times,
											'bookingpress_staffmember_daysoff_repeat_date' => $daysoff_repeat_date,
											'bookingpress_staffmember_daysoff_created' => current_time( 'mysql' ),
										);
										$wpdb->insert( $tbl_bookingpress_staffmembers_daysoff, $args );										
									}
								}
							}
						}
					}

					/* save Staffmember special day*/

					if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_special_days' ) ) {
						$bookingpress_special_day_data = $wpdb->get_results( $wpdb->prepare( 'SELECT bookingpress_staffmember_special_day_id FROM ' . $tbl_bookingpress_staffmembers_special_day . ' WHERE bookingpress_staffmember_id = %d', $bookingpress_update_id ), ARRAY_A );   // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_special_days is a table name. false alarm
						$wpdb->delete( $tbl_bookingpress_staffmembers_special_day, array( 'bookingpress_staffmember_id' => $bookingpress_update_id ) );

						if ( ! empty( $bookingpress_special_day_data ) ) {
							foreach ( $bookingpress_special_day_data as $bookingpress_special_day_data_key => $bookingpress_special_day_data_value ) {
								$bookingpress_special_day_id = ! empty( $bookingpress_special_day_data_value['bookingpress_staffmember_special_day_id'] ) ? intval( $bookingpress_special_day_data_value['bookingpress_staffmember_special_day_id'] ) : 0;
								$wpdb->delete( $tbl_bookingpress_staffmembers_special_day_breaks, array( 'bookingpress_special_day_id' => $bookingpress_special_day_id ) );
							}
						}

						$_REQUEST['special_day_details'] = !empty( $_REQUEST['special_day_details']) ? json_decode( stripslashes_deep( $_REQUEST['special_day_details'] ), true ) : array(); //phpcs:ignore

						if ( ! empty( $_REQUEST['special_day_details'] ) && is_array( $_REQUEST['special_day_details'] ) ) {
							foreach ( $_REQUEST['special_day_details'] as $special_day ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason data is sanitized further
								$bookingpress_special_day_start_date = ! empty( $special_day['special_day_start_date'] ) ? sanitize_text_field( $special_day['special_day_start_date'] ) : '';
								$bookingpress_special_day_end_date   = ! empty( $special_day['special_day_end_date'] ) ? sanitize_text_field( $special_day['special_day_end_date'] ) : '';
								$special_day_selected_service        = ( ! empty( $special_day['special_day_service'] ) && is_array( $special_day['special_day_service'] ) ) ? implode( ',', $special_day['special_day_service'] ) : '';
								$special_day_workhour_arr            = ! empty( $special_day['special_day_workhour'] ) ? ( $special_day['special_day_workhour'] ) : array();
								$start_time                          = ! empty( $special_day['start_time'] ) ? sanitize_text_field( $special_day['start_time'] ) : '';
								$end_time                            = ! empty( $special_day['end_time'] ) ? sanitize_text_field( $special_day['end_time'] ) : '';
								$args_special_day                    = array(
									'bookingpress_staffmember_id' => $bookingpress_update_id,
									'bookingpress_special_day_start_date' => $bookingpress_special_day_start_date,
									'bookingpress_special_day_end_date' => $bookingpress_special_day_end_date,
									'bookingpress_special_day_start_time' => $start_time,
									'bookingpress_special_day_end_time' => $end_time,
									'bookingpress_special_day_service_id' => $special_day_selected_service,
									'bookingpress_created_at' => current_time( 'mysql' ),
								);
								$wpdb->insert( $tbl_bookingpress_staffmembers_special_day, $args_special_day );
								$bookingpress_special_day_reference_id = $wpdb->insert_id;

								if ( ! empty( $special_day_workhour_arr ) ) {
									foreach ( $special_day_workhour_arr as $special_day_workhour_key => $special_day_workhour_val ) {
										$start_time         = ! empty( $special_day_workhour_val['start_time'] ) ? sanitize_text_field( $special_day_workhour_val['start_time'] ) : '';
										$end_time           = ! empty( $special_day_workhour_val['end_time'] ) ? sanitize_text_field( $special_day_workhour_val['end_time'] ) : '';
										$args_extra_details = array(
											'bookingpress_special_day_id' => $bookingpress_special_day_reference_id,
											'bookingpress_special_day_break_start_time' => $start_time,
											'bookingpress_special_day_break_end_time' => $end_time,
											'bookingpress_created_at'                   => current_time( 'mysql' ),
										);
										$wpdb->insert( $tbl_bookingpress_staffmembers_special_day_breaks, $args_extra_details );
									}
								}
							}
						}
					}
					$response['wpuser_id']      = $bookingpress_existing_user_id;
					$response['staffmember_id'] = $bookingpress_update_id;
					$response['variant']        = 'success';
					$response['title']          = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']            = esc_html__( 'Shift management data updated successfully.', 'bookingpress-appointment-booking' );					
				} else{ 					
					if ( ! empty( $_REQUEST['update_id'] ) ) {
						$response['wpuser_id']      = $bookingpress_existing_user_id;
						$response['staffmember_id'] = $bookingpress_update_id;
						$response['variant']        = 'success';
						$response['title']          = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']            = esc_html__( 'Staff member has been updated succsssfully.', 'bookingpress-appointment-booking' );
					} else {
						$response['staffmember_id'] = $bookingpress_update_id;
						$response['wpuser_id']      = $bookingpress_existing_user_id;
						$response['variant']        = 'success';
						$response['title']          = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']            = esc_html__( 'Staff member has been added succsssfully.', 'bookingpress-appointment-booking' );
					}		
				}

				$response = apply_filters( 'bookingpress_staff_members_save_external_details', $response );
			}
			echo wp_json_encode( $response );
			die();
		}

		function bookingpress_filter_admin_email_data_func( $admin_email, $appointment_id, $notification_name ) {
			global $wpdb,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_staffmembers,$tbl_bookingpress_notifications;
			$admin_email = array();
			if ( ! empty( $appointment_id ) ) {
				$appointments_data                                 = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_staff_member_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
				
				if ( ! empty( $appointments_data['bookingpress_staff_member_id'] ) ) {
					$staffmember_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_staffmember_email FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $appointments_data['bookingpress_staff_member_id'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
					if ( ! empty( $staffmember_data['bookingpress_staffmember_email'] ) ) {
						$admin_email[] = $staffmember_data['bookingpress_staffmember_email'];
					}
				}
			}
			$admin_email = ! empty( $admin_email ) ? implode( ',', $admin_email ) : array();
			return $admin_email;
		}

		function bookingpress_get_notifiacation_data_filter_func( $bookingpress_exist_record_data ) {
			if ( isset( $bookingpress_exist_record_data['bookingpress_notification_cc_email'] ) ) {
				$bookingpress_exist_record_data['bookingpress_notification_cc_email'] = $bookingpress_exist_record_data['bookingpress_notification_cc_email'];
			}
			return $bookingpress_exist_record_data;
		}

		function bookingpress_email_notification_get_data_func() {
			?>
			if(bookingpress_return_notification_data.bookingpress_notification_cc_email != 'undefined') {
				vm.bookingpress_notification_cc_email = bookingpress_return_notification_data.bookingpress_notification_cc_email;
			}
			<?php
		}

		function bookingpress_add_email_notification_data_func() {
			?>
			if(vm.bookingpress_notification_cc_email != 'undefined') {
				bookingpress_save_notification_data.bookingpress_notification_cc_email = vm.bookingpress_notification_cc_email
			}
			<?php

		}

		function bookingpress_save_email_notification_data_filter_func( $bookingpress_database_modify_data, $notification_data ) {

			if ( ! empty( $notification_data['bookingpress_notification_cc_email'] ) ) {
				$bookingpress_database_modify_data['bookingpress_notification_cc_email'] = ! empty( $notification_data['bookingpress_notification_cc_email'] ) ? sanitize_text_field( $notification_data['bookingpress_notification_cc_email'] ) : '';
			} else{
				$bookingpress_database_modify_data['bookingpress_notification_cc_email'] = '';
			}
			return $bookingpress_database_modify_data;
		}

		function bookingpress_add_dynamic_notification_data_fields_func( $bookingpress_notification_vue_methods_data ) {

			$bookingpress_notification_vue_methods_data['bookingpress_notification_cc_email'] = '';
			return $bookingpress_notification_vue_methods_data;
		}

		function bookingpress_get_staffmember_service( $staffmember_id, $is_total = 0 ) {
			global $tbl_bookingpress_staffmembers_services,$wpdb;

			$bookingpress_staff_member_services_details = $bookingpress_staffmember_services = array();
			if ( ! empty( $staffmember_id ) ) {
				$bookingpress_staff_member_services_details = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_service_id FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_staffmember_id = %d", $staffmember_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm

				if ( ! empty( $bookingpress_staff_member_services_details ) ) {
					foreach ( $bookingpress_staff_member_services_details as $bookingpress_staff_member_services_key => $bookingpress_staff_member_services_val ) {
						array_push( $bookingpress_staffmember_services, $bookingpress_staff_member_services_val['bookingpress_service_id'] );
					}
				}
			}
			$bookingpress_staff_member_services_details = apply_filters( 'bookingpress_modify_staff_member_service_details', $bookingpress_staff_member_services_details, $staffmember_id );
			if ( $is_total ) {
				return count( $bookingpress_staff_member_services_details );
			}
			return $bookingpress_staffmember_services;
		}

		function bookingpress_dashboard_redirect_filter_func(){
			global $bookingpress_slugs;
			?>
			 else if(module == 'staffmember') {
				bookingpress_redirect_url ="<?php echo  add_query_arg('page', esc_html($bookingpress_slugs->bookingpress_staff_members), esc_url(admin_url() . 'admin.php?page=bookingpress')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason: data has been escaped properly ?>"    
			}
			<?php
		}

		function bookingpress_appointment_customer_list_join_filter_func( $bookingpress_join_query ) {
			$bookingpress_join_query = $this->bookingpress_customer_list_join_filter( $bookingpress_join_query );
			return $bookingpress_join_query;
		}

		function bookingpress_search_customer_list_join_filter_func( $bookingpress_join_query ) {
			$bookingpress_join_query = $this->bookingpress_customer_list_join_filter( $bookingpress_join_query );
			return $bookingpress_join_query;
		}

		function bookingpress_search_customer_list_filter_func( $bookingpress_search_query ) {
			$bookingpress_search_query = $this->bookingpress_customer_list_where_filter( $bookingpress_search_query );
			return $bookingpress_search_query;
		}

		function bookingpress_appointment_customer_list_filter_func( $bookingpress_search_query ) {
			$bookingpress_search_query = $this->bookingpress_customer_list_where_filter( $bookingpress_search_query );
			return $bookingpress_search_query;
		}

		function bookingpress_export_appointment_data_filter_func( $bookingpress_search_query ) {
			global $BookingPressPro;

			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$bookingpress_search_query  .= " AND (bookingpress_staff_member_id = '{$bookingpress_staffmember_id}')";
			}
			return $bookingpress_search_query;
		}

		function bookingpress_export_payment_data_filter_func( $bookingpress_search_query ) {
			global $BookingPressPro;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$bookingpress_search_query  .= " AND (bookingpress_staff_member_id = '{$bookingpress_staffmember_id}')";
			}
			return $bookingpress_search_query;
		}

		function bookingpress_customer_export_join_data_filter_func( $bookingpress_join_query ) {
			$bookingpress_join_query = $this->bookingpress_customer_list_join_filter( $bookingpress_join_query );
			return $bookingpress_join_query;
		}

		function bookingpress_customer_export_data_filter_func( $bookingpress_search_query ) {
			$bookingpress_search_query = $this->bookingpress_customer_list_where_filter( $bookingpress_search_query );
			return $bookingpress_search_query;
		}

		function bookingpress_customer_view_join_add_filter_func( $bookingpress_join_query ) {
			$bookingpress_join_query = $this->bookingpress_customer_list_join_filter( $bookingpress_join_query );
			return $bookingpress_join_query;
		}

		function bookingpress_customer_view_add_filter_func( $bookingpress_search_query ) {
			$bookingpress_search_query = $this->bookingpress_customer_list_where_filter( $bookingpress_search_query );
			return $bookingpress_search_query;
		}

		/* customer_data filter fuction on dropdown in backed */

		function bookingpress_customer_list_join_filter( $bookingpress_join_query ) {
			global $BookingPressPro,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_staffmembers;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$bookingpress_join_query    .= ' LEFT JOIN ' . $tbl_bookingpress_appointment_bookings . ' as ap ON cs.bookingpress_customer_id = ap.bookingpress_customer_id ';
			}
			return $bookingpress_join_query;
		}

		function bookingpress_customer_list_where_filter( $bookingpress_search_query ) {
			global $BookingPressPro;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$bookingpress_search_query  .= " AND ((ap.bookingpress_staff_member_id = {$bookingpress_staffmember_id}) OR (cs.bookingpress_created_by = {$bookingpress_user_id})) GROUP BY cs.bookingpress_customer_id ";
			}
			return $bookingpress_search_query;
		}

		/* over customer_data filter fuction on dropdown in backed */	

		function bookingpress_appointment_chart_data_filter_func( $bookingpress_search_query ) {
			global $BookingPressPro,$tbl_bookingpress_appointment_bookings;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );				
				$bookingpress_search_query  .= " AND (bookingpress_staff_member_id = '{$bookingpress_staffmember_id}')";
			}
			return $bookingpress_search_query;
		}

		function bookingpress_customer_chart_data_filter_func( $bookingpress_search_query ) {
			global $BookingPressPro;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );				
				$bookingpress_search_query  .= " AND (bookingpress_staff_member_id = '{$bookingpress_staffmember_id}')";

			}
			return $bookingpress_search_query;
		}
		
		function bookingpress_payment_chart_data_filter_func( $bookingpress_search_query ) {
			global $BookingPressPro;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$bookingpress_search_query  .= " AND (bookingpress_staff_member_id = '{$bookingpress_staffmember_id}')";
			}
			return $bookingpress_search_query;
		}

		function bookingpress_dashboard_upcoming_appointments_data_filter_func( $bookingpress_search_query ) {
			global $BookingPressPro;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$bookingpress_search_query  .= " AND (bookingpress_staff_member_id = '{$bookingpress_staffmember_id}')";
			}
			return $bookingpress_search_query;
		}

		function bookingpress_update_summary_data_func( $return_data, $bookingpress_start_date ,$bookingpress_end_date ) {
			global $BookingPressPro,$wpdb,$tbl_bookingpress_staffmembers;

			/* $total_customer = $return_data['total_customers'];
			if( !empty( $total_customer )){
				$total_customer = $return_data['total_customers'];    
			} else {
				$total_customer = 0;
			}
			$return_data['total_customers'] = $total_customer; */
			
			$bookingpress_start_date =  $bookingpress_start_date." 00:00:00";
			$bookingpress_end_date =  $bookingpress_end_date." 23:59:59";
			$total_staffmembers = 0;
			if ( ! $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$total_staffmembers = $wpdb->get_var($wpdb->prepare("SELECT COUNT(bookingpress_staffmember_id) FROM {$tbl_bookingpress_staffmembers} WHERE (bookingpress_staffmember_created BETWEEN %s AND %s)",$bookingpress_start_date,$bookingpress_end_date)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is table name.		
			}			
			$return_data['total_staffmembers'] = $total_staffmembers;
			return $return_data;
		}

		function bookingpress_modify_dashboard_data_fields_func( $bookingpress_dashboard_vue_data_fields ) {
			global $BookingPressPro;

			if ( ! $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_dashboard_vue_data_fields['summary_data']['total_staffmembers'] = 0;
			}
			return $bookingpress_dashboard_vue_data_fields;
		}

		function bookingpress_appointment_add_view_field_func( $appointment, $get_appointment ) {
			$staffmember_name = '';
			$staff_member_id  = ! empty( $get_appointment['bookingpress_staff_member_id'] ) ? intval( $get_appointment['bookingpress_staff_member_id'] ) : 0;
			if ( ! empty( $staff_member_id ) ) {
				$staffmember_name = $this->bookingpress_get_staffmembername_using_id( $staff_member_id );
			}
			$appointment['staff_member_name'] = $staffmember_name;
			return $appointment;
		}

		function bookingpress_payment_add_view_field_func( $payment, $payment_log_val ) {
			$staffmember_name = '';
			$staff_member_id  = ! empty( $payment_log_val['bookingpress_staff_member_id'] ) ? intval( $payment_log_val['bookingpress_staff_member_id'] ) : 0;
			if ( ! empty( $staff_member_id ) ) {
				$staffmember_name = $this->bookingpress_get_staffmembername_using_id( $staff_member_id );
			}
			$payment['staff_member_name'] = $staffmember_name;
			return $payment;
		}

		function bookingpress_get_staffmembername_using_id( $staff_member_id ) {
			global $wpdb,$tbl_bookingpress_staffmembers;
			$staffmember_name = '';
			if ( ! empty( $staff_member_id ) ) {
				$bpa_staffmember_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_staffmember_firstname,bookingpress_staffmember_lastname,bookingpress_staffmember_email FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $staff_member_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
					$staffmember_name = ! empty( $bpa_staffmember_data['bookingpress_staffmember_firstname'] ) ? sanitize_text_field( $bpa_staffmember_data['bookingpress_staffmember_firstname'] . ' ' . $bpa_staffmember_data['bookingpress_staffmember_lastname'] ) : sanitize_email( $bpa_staffmember_data['bookingpress_staffmember_email'] );
			}
			return $staffmember_name;
		}

		function bookingpress_modify_appointment_data_fields_func( $bookingpress_appointment_vue_data_fields ) {
			global $BookingPressPro;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				if ( ! empty( $bookingpress_appointment_vue_data_fields['appointment_services_data'] ) ) {
					$filter_staffmeber_services    = $this->bookingpress_get_staffmember_service( $bookingpress_staffmember_id );
					$bookingpress_services_details = $this->bookingpress_filter_staffmember_services( $bookingpress_appointment_vue_data_fields['appointment_services_data'], $filter_staffmeber_services );
						$bookingpress_appointment_vue_data_fields['appointment_services_data'] = $bookingpress_services_details;
				}
				if ( ! empty( $bookingpress_appointment_vue_data_fields['appointment_services_list'] ) ) {
					$filter_staffmeber_services    = $this->bookingpress_get_staffmember_service( $bookingpress_staffmember_id );
					$bookingpress_services_details = $this->bookingpress_filter_staffmember_services( $bookingpress_appointment_vue_data_fields['appointment_services_list'], $filter_staffmeber_services );
						$bookingpress_appointment_vue_data_fields['appointment_services_list'] = $bookingpress_services_details;
				}
			}
			$bookingpress_appointment_vue_data_fields['search_staff_member_list'] = $this->bookingpress_staffmember_search_list();
			$bookingpress_appointment_vue_data_fields['search_staff_member_name'] = '';
			return $bookingpress_appointment_vue_data_fields;
		}

		function bookingpress_modify_calendar_data_fields_func( $bookingpress_calendar_vue_data_fields ) {
			global $BookingPressPro;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				if ( ! empty( $bookingpress_calendar_vue_data_fields['appointment_services_data'] ) ) {
					$filter_staffmeber_services    = $this->bookingpress_get_staffmember_service( $bookingpress_staffmember_id );
					$bookingpress_services_details = $this->bookingpress_filter_staffmember_services( $bookingpress_calendar_vue_data_fields['appointment_services_data'], $filter_staffmeber_services );
					$bookingpress_calendar_vue_data_fields['appointment_services_data'] = $bookingpress_services_details;
				}
				if ( ! empty( $bookingpress_calendar_vue_data_fields['appointment_services_list'] ) ) {
					$filter_staffmeber_services    = $this->bookingpress_get_staffmember_service( $bookingpress_staffmember_id );
					$bookingpress_services_details = $this->bookingpress_filter_staffmember_services( $bookingpress_calendar_vue_data_fields['appointment_services_list'], $filter_staffmeber_services );
					$bookingpress_calendar_vue_data_fields['appointment_services_list'] = $bookingpress_services_details;
				}
			}
			$bookingpress_calendar_vue_data_fields['search_staff_member_list']             = $this->bookingpress_staffmember_search_list();
			$bookingpress_calendar_vue_data_fields['search_data']['selected_staff_member'] = array();
			$bookingpress_calendar_vue_data_fields['search_data_staff_id'] = '';
			return $bookingpress_calendar_vue_data_fields;
		}

		function bookingpress_filter_staffmember_services( $staffmember_total_services, $filter_services ) {
			$bookingpress_services_details = array();
			if ( ! empty( $staffmember_total_services ) && ! empty( $filter_services ) ) {
				$bookingpress_services_details = $staffmember_total_services;
				foreach ( $staffmember_total_services as $bookingpress_service_key => $bookingpress_service_val ) {
					if ( ! empty( $bookingpress_service_val['category_services'] ) && is_array( $bookingpress_service_val['category_services'] ) ) {
						foreach ( $bookingpress_service_val['category_services'] as $bookingpress_cat_service_key => $bookingpress_cat_service_val ) {
							if ( ! empty( $bookingpress_cat_service_val['service_id'] ) && ! in_array( $bookingpress_cat_service_val['service_id'], $filter_services ) ) {
								unset( $bookingpress_services_details[ $bookingpress_service_key ]['category_services'][ $bookingpress_cat_service_key ] );
							}
						}
					}
					if ( empty( $bookingpress_services_details[ $bookingpress_service_key ]['category_services'] ) ) {
						unset( $bookingpress_services_details[ $bookingpress_service_key ] );
					}
				}
			}
			$bookingpress_services_details = array_values( $bookingpress_services_details );
			return $bookingpress_services_details;
		}

		function bookingpress_modify_payment_data_fields_func( $bookingpress_payment_vue_data_fields ) {
			global $BookingPressPro;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				if ( ! empty( $bookingpress_payment_vue_data_fields['search_services_data'] ) ) {
					$filter_staffmeber_services                                   = $this->bookingpress_get_staffmember_service( $bookingpress_staffmember_id );
					$bookingpress_services_details                                = $this->bookingpress_filter_staffmember_services( $bookingpress_payment_vue_data_fields['search_services_data'], $filter_staffmeber_services );
					$bookingpress_payment_vue_data_fields['search_services_data'] = $bookingpress_services_details;
				}
			}
			$bookingpress_payment_vue_data_fields['search_staffmember_data']            = $this->bookingpress_staffmember_search_list();
			$bookingpress_payment_vue_data_fields['search']['search_staff_member_name'] = '';
			return $bookingpress_payment_vue_data_fields;
		}

		function bookingpress_staffmember_search_list() {

			global $wpdb,$tbl_bookingpress_staffmembers;
			$bookingpress_staff_member_details           = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_id,bookingpress_staffmember_firstname,bookingpress_staffmember_lastname,bookingpress_staffmember_email FROM {$tbl_bookingpress_staffmembers} WHERE  bookingpress_staffmember_status = %d", 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
			$bookingpress_staff_member_selection_details = array();
			$bookingpress_staff_member_name              = '';
			foreach ( $bookingpress_staff_member_details as $bookingpress_staff_member_key => $bookingpress_staff_member_val ) {
				$bookingpress_staff_member_name                = ( $bookingpress_staff_member_val['bookingpress_staffmember_firstname'] == '' && $bookingpress_staff_member_val['bookingpress_staffmember_lastname'] == '' ) ? $bookingpress_staff_member_val['bookingpress_staffmember_email'] : $bookingpress_staff_member_val['bookingpress_staffmember_firstname'] . ' ' . $bookingpress_staff_member_val['bookingpress_staffmember_lastname'];

				$bookingpress_staffmember_avatar = $this->get_bookingpress_staffmembersmeta($bookingpress_staff_member_val['bookingpress_staffmember_id'], 'staffmember_avatar_details');
				$bookingpress_staffmember_avatar = !empty($bookingpress_staffmember_avatar) ? maybe_unserialize($bookingpress_staffmember_avatar) : array();
				if (!empty($bookingpress_staffmember_avatar[0]['url'])) {
					$bookingpress_staff_avatar_url = $bookingpress_staffmember_avatar[0]['url'];
				}else{
					$bookingpress_staff_avatar_url = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
				}

				$bookingpress_staff_member_selection_details[] = array(
					'text'  => $bookingpress_staff_member_name,
					'value' => $bookingpress_staff_member_val['bookingpress_staffmember_id'],
					'avatar' => $bookingpress_staff_avatar_url,
				);
			}
			return $bookingpress_staff_member_selection_details;
		}

		function bookingpress_payment_add_filter_func( $bookingpress_search_query, $bookingpress_search_data ) {
			global $BookingPressPro;
			if ( ! empty( $bookingpress_search_data['search_staff_member'] ) ) {
				$bookingpress_search_name            = $bookingpress_search_data['search_staff_member'];
				$bookingpress_search_staff_member_id = implode( ',', $bookingpress_search_name );
				$bookingpress_search_query          .= " AND (bookingpress_staff_member_id IN ({$bookingpress_search_staff_member_id}))";
			};
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$bookingpress_search_query  .= " AND (bookingpress_staff_member_id = '{$bookingpress_staffmember_id}')";
			}
			return $bookingpress_search_query;
		}

		function bookingpress_appointment_view_add_filter_func( $bookingpress_search_query_where, $bookingpress_search_data ) {
			global $BookingPressPro;
			if ( ! empty( $bookingpress_search_data['staff_member_name'] ) ) {
				$bookingpress_search_name            = $bookingpress_search_data['staff_member_name'];
				$bookingpress_search_staff_member_id = implode( ',', $bookingpress_search_name );
				$bookingpress_search_query_where    .= " AND (bookingpress_staff_member_id IN ({$bookingpress_search_staff_member_id}))";
			};
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id             = get_current_user_id();
				$bookingpress_staffmember_id      = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$bookingpress_search_query_where .= " AND (bookingpress_staff_member_id = '{$bookingpress_staffmember_id}')";
			}
			return $bookingpress_search_query_where;
		}

		function bookingpress_calendar_add_view_filter_func( $bookingpress_search_query, $bookingpress_search_data ) {
			global $BookingPressPro;
			if ( ! empty( $bookingpress_search_data['selected_staff_member'] ) ) {
				$bookingpress_search_name            = $bookingpress_search_data['selected_staff_member'];
				$bookingpress_search_staff_member_id = implode( ',', $bookingpress_search_name );
				$bookingpress_search_query          .= " AND (bookingpress_staff_member_id IN ({$bookingpress_search_staff_member_id}))";
			};
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$bookingpress_search_query  .= " AND (bookingpress_staff_member_id = {$bookingpress_staffmember_id})";
			}
			return $bookingpress_search_query;
		}

		function bookingpress_dashboard_appointment_summary_data_filter_func( $appointments_search_query ) {
			global $BookingPressPro;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$appointments_search_query  .= " AND (bookingpress_staff_member_id = {$bookingpress_staffmember_id})";
			}
			return $appointments_search_query;
		}

		function bookingpress_dashboard_payment_summary_data_filter_func( $appointments_search_query ) {
			global $BookingPressPro;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$appointments_search_query  .= " AND (bookingpress_staff_member_id = {$bookingpress_staffmember_id})";
			}
			return $appointments_search_query;		
		}		

		function bookingpress_appointment_service_wise_staffmember_list_func($search_query){
			global $BookingPressPro;
			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
				$bookingpress_user_id        = get_current_user_id();
				$bookingpress_staffmember_id = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
				$search_query  .= " AND (bookingpress_staffmember_id = {$bookingpress_staffmember_id})";
			}
			return $search_query;		
		}

		function bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id ) {
			global $wpdb,$tbl_bookingpress_staffmembers;
			$bookingpress_staffmember_id = 0;
			if ( ! empty( $bookingpress_user_id ) ) {
				$bookingpress_existing_staffmember_details = $wpdb->get_row( $wpdb->prepare( "SELECT `bookingpress_staffmember_id` FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d ORDER BY bookingpress_staffmember_id DESC", $bookingpress_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
				if ( ! empty( $bookingpress_existing_staffmember_details ) ) {
					$bookingpress_staffmember_id = ! empty( $bookingpress_existing_staffmember_details['bookingpress_staffmember_id'] ) ? $bookingpress_existing_staffmember_details['bookingpress_staffmember_id'] : '';
				}
			}
			return $bookingpress_staffmember_id;
		}

		function bookingpress_current_login_staffmember_status() {
			global $wpdb,$tbl_bookingpress_staffmembers;
			$bookingpress_user_id            = get_current_user_id();
			$bookingpress_staffmember_status = 4;
			$staffmember_id                  = $this->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
			if ( ! empty( $staffmember_id ) ) {
				$bookingpress_staffmember_details = $wpdb->get_row( $wpdb->prepare( "SELECT `bookingpress_staffmember_status` FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $staffmember_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
				if ( ! empty( $bookingpress_staffmember_details ) ) {
					$bookingpress_staffmember_status = ! empty( $bookingpress_staffmember_details['bookingpress_staffmember_status'] ) ? $bookingpress_staffmember_details['bookingpress_staffmember_status'] : 4;
				}
			}
			return $bookingpress_staffmember_status;
		}
		function bookingpress_get_staffmember_upcomming_appointment_data($bookingpress_staffmember_id,$type='status') {	
			global $tbl_bookingpress_appointment_bookings,$wpdb,$bookingpress_global_options, $bookingpress_appointment;
			$bookingpress_staff_appointment_data = array();			
			$bookingpress_current_date = date('Y-m-d', current_time('timestamp'));
			$bookingpress_current_time = date('H:i:s', current_time('timestamp'));	
			$bookingpress_staffmember_appointment_data = $wpdb->get_row($wpdb->prepare( 'SELECT bookingpress_appointment_booking_id,bookingpress_service_name,bookingpress_appointment_date,bookingpress_customer_email,bookingpress_customer_name,bookingpress_customer_firstname,bookingpress_customer_lastname,bookingpress_appointment_time,bookingpress_appointment_end_time,bookingpress_booking_id,bookingpress_service_duration_val,bookingpress_service_duration_unit FROM ' . $tbl_bookingpress_appointment_bookings . ' WHERE  bookingpress_staff_member_id = %d AND bookingpress_appointment_date >= %s AND bookingpress_appointment_time > %s',$bookingpress_staffmember_id,$bookingpress_current_date,$bookingpress_current_time),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if(!empty($bookingpress_staffmember_appointment_data)) {

                $bookingpress_global_options_arr  = $bookingpress_global_options->bookingpress_global_options();
                $bookingpress_default_date_format = $bookingpress_global_options_arr['wp_default_date_format'];
                $bookingpress_default_time_format = $bookingpress_global_options_arr['wp_default_time_format'];
                $bookingpress_default_date_time_format = $bookingpress_default_date_format . ' ' . $bookingpress_default_time_format;;
				$appointment_date_time = $bookingpress_staffmember_appointment_data['bookingpress_appointment_date'] . ' ' . $bookingpress_staffmember_appointment_data['bookingpress_appointment_time'];
				$bookingpress_staff_appointment_data['appointment_date'] = date_i18n($bookingpress_default_date_time_format, strtotime($appointment_date_time));
				$bookingpress_customer_first_name = !empty($bookingpress_staffmember_appointment_data['bookingpress_customer_firstname']) ? stripslashes_deep($bookingpress_staffmember_appointment_data['bookingpress_customer_firstname']) :'';
				$bookingpress_customer_last_name = !empty($bookingpress_staffmember_appointment_data['bookingpress_customer_lastname']) ? stripslashes_deep($bookingpress_staffmember_appointment_data['bookingpress_customer_lastname']) :'';
				$bookingpress_staff_appointment_data['booking_id'] = !empty($bookingpress_staffmember_appointment_data['bookingpress_booking_id']) ? $bookingpress_staffmember_appointment_data['bookingpress_booking_id'] : '';
				$bookingpress_staff_appointment_data['customer_email'] = !empty($bookingpress_staffmember_appointment_data['bookingpress_customer_email']) ? stripslashes_deep($bookingpress_staffmember_appointment_data['bookingpress_customer_email']) : '';				
				$bookingpress_staff_appointment_data['customer_name'] = !empty($bookingpress_staffmember_appointment_data['bookingpress_customer_name']) ? stripslashes_deep($bookingpress_staffmember_appointment_data['bookingpress_customer_name']) : $bookingpress_customer_first_name.' '.$bookingpress_customer_last_name;
				$bookingpress_staff_appointment_data['service_name'] = !empty($bookingpress_staffmember_appointment_data['bookingpress_service_name']) ? stripslashes_deep($bookingpress_staffmember_appointment_data['bookingpress_service_name']) : '';
				$service_duration             = esc_html($bookingpress_staffmember_appointment_data['bookingpress_service_duration_val']);
				$service_duration_unit        = esc_html($bookingpress_staffmember_appointment_data['bookingpress_service_duration_unit']);
				if($service_duration_unit != 'd') {
					if( !empty( $bookingpress_appointment ) && method_exists( $bookingpress_appointment, 'bookingpress_get_appointment_duration' ) ){
						$bookingpress_appointment_start_datetime = $bookingpress_staffmember_appointment_data['bookingpress_appointment_date'].' '.$bookingpress_staffmember_appointment_data['bookingpress_appointment_time'];
					
					$bookingpress_appointment_end_datetime = $bookingpress_staffmember_appointment_data['bookingpress_appointment_date'].' '.$bookingpress_staffmember_appointment_data['bookingpress_appointment_end_time'];
					
						$service_duration = $bookingpress_appointment->bookingpress_get_appointment_duration($bookingpress_appointment_start_datetime, $bookingpress_appointment_end_datetime);
					}
					else {
						if ($service_duration_unit == 'm' ) {
							$service_duration .= ( $service_duration  == 1 ) ? ' ' .esc_html__('Min', 'bookingpress-appointment-booking') : ' ' . esc_html__('Mins', 'bookingpress-appointment-booking');					
						}
						else if ($service_duration_unit == 'h' ) {
							$service_duration .= ( $service_duration  == 1 ) ? ' ' .esc_html__('Hour', 'bookingpress-appointment-booking') : ' ' . esc_html__('Hours', 'bookingpress-appointment-booking');					
						}
					}
				} else {
					if( 1 == $service_duration ){
						$service_duration .= ' ' . esc_html__('Day', 'bookingpress-appointment-booking');
					} else {   
						$service_duration .= ' ' . esc_html__('Days', 'bookingpress-appointment-booking');
					}                        
				}
				$bookingpress_staff_appointment_data['appointment_duration'] = $service_duration;				
			}

			return $bookingpress_staff_appointment_data;
		}

		function bookingpress_pro_calendar_add_post_data_func() {
			?>
			postData.search_data.staff_member_name = this.search_staff_member_name;			
			<?php
		}

		function bookingpress_appointment_add_post_data_func() {
			?>
			bookingpress_search_data.staff_member_name = this.search_staff_member_name;			
			<?php
		}

		function bookingpress_load_summary_dynamic_data_func() {
			?>
			if(response.data.total_staffmember != 'undefined' && vm2.summary_data.total_staffmember != 'undefined') {
				vm2.summary_data.total_staffmembers = response.data.total_staffmembers 
			}
			<?php
		}

		function bookingpress_customize_add_dynamic_data_fields_func($bookingpress_customize_vue_data_fields){
			$bookingpress_customize_vue_data_fields['booking_form_settings']['bookingpress_form_sequance'] = '["service_selection","staff_selection"]';					
			$bookingpress_customize_vue_data_fields['booking_form_settings']['hide_staffmember_selection'] = false;						
			$bookingpress_customize_vue_data_fields['booking_form_settings']['bookingpress_staffmember_information'] = '2';					
			$bookingpress_customize_vue_data_fields['booking_form_settings']['hide_staffmember_price'] = 'service_selection';		
			$bookingpress_customize_vue_data_fields['tab_container_data']['staffmember_title'] = '';
			$bookingpress_customize_vue_data_fields['bookingpress_shortcode_form']['selected_staffmember'] = 'staff_1';
			$bookingpress_customize_vue_data_fields['service_container_data']['staffmember_heading_title'] = 'Select Staff Member';
			$bookingpress_customize_vue_data_fields['service_container_data']['any_staff_title'] = 'Any Staff';
			$bookingpress_customize_vue_data_fields['staffmember_container_data']['default_image_url'] = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
			return $bookingpress_customize_vue_data_fields;
		}

		function bookingpress_get_booking_form_customize_data_filter_func($booking_form_settings){
			$booking_form_settings['booking_form_settings']['bookingpress_form_sequance'] = '';						
			$booking_form_settings['booking_form_settings']['hide_staffmember_selection'] = '';		
			$booking_form_settings['booking_form_settings']['bookingpress_staffmember_information'] = '';												
			$booking_form_settings['booking_form_settings']['hide_staffmember_price'] = '';
			$booking_form_settings['tab_container_data']['staffmember_title'] = __('Staff', 'bookingpress-appointment-booking');
			$booking_form_settings['service_container_data']['any_staff_title'] = 'Any Staff';
			$booking_form_settings['service_container_data']['staffmember_heading_title'] = __('Select Staffmember','bookingpress-appointment-booking');
			return $booking_form_settings;
		}

		function bookingpress_dynamic_vue_methods_func() {
			?>
			bpa_select_staffmember(selected_staffmember){
				const vm = this
				vm.bookingpress_shortcode_form.selected_staffmember = selected_staffmember
			},
			bookingpress_change_hide_staff(event) {				
				const vm = this;
				if(event == true) {
					vm.formActiveTab = '1';
				}
			},
			<?php			
		}

		function bookingpress_before_save_customize_booking_form_func($bookingpress_form_settings_data){
			global $BookingPress;			
			if(!empty($bookingpress_form_settings_data['bookingpress_form_settings']['hide_staffmember_selection']) && $bookingpress_form_settings_data['bookingpress_form_settings']['hide_staffmember_selection'] ==  'true'){
				$BookingPress->bookingpress_update_settings('bookingpress_staffmember_any_staff_options','staffmember_setting','true');				
			}		
			return 	$bookingpress_form_settings_data;
		}

		function bookingpress_get_staffmember_wpuser_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_customers;
			$response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'get_staffmember_wpusers', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			$search_user_str = ! empty( $_REQUEST['search_user_str'] ) ? sanitize_text_field( $_REQUEST['search_user_str'] ) : '';
			$wordpress_user_id = ! empty( $_REQUEST['wordpress_user_id'] ) ? intval( $_REQUEST['wordpress_user_id'] ) : '';			
			if(!empty($search_user_str)) {
				$blog_id = get_current_blog_id();
				$blog_prefix = $wpdb->get_blog_prefix( $blog_id );
				/** Retrieve User ID of the users who has only one role but BookingPress Customer and BookingPress Staffmember */
				$args = array(
					'search' => '*'.$search_user_str.'*',
					'fields' => array( 'user_login','id' ),
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => $blog_prefix . 'capabilities',
							'value' => '^a:1:*',
							'compare' => 'REGEXP'
						),
						array(
							'key' => $blog_prefix . 'capabilities',
							'value' => 'bookingpress-customer',
							'compare' => 'NOT LIKE'
						),
						array(
							'key' => $blog_prefix . 'capabilities',
							'value' => 'bookingpress-staffmember',
							'compare' => 'NOT LIKE'
						),
					)
				);
			
				$wp_single_role_users = new WP_User_Query( $args );
				
				$wp_single_role_users_data = $wp_single_role_users->results;

				$include_users_from_search = array();
				if( !empty( $wp_single_role_users_data ) ){
					foreach( $wp_single_role_users_data as $skip_user_data ){
						$include_users_from_search[] = json_decode( json_encode( $skip_user_data ), true );
					}
				}

				
				$usertable = $wpdb->users;
				$user_meta_table = $wpdb->usermeta;
				$args2 = array(
					'search' => '*' . $search_user_str.'*',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => $blog_prefix . 'capabilities',
							'value' => '^a:1:*',
							'compare' => 'NOT REGEXP'
						),
						array(
							'key' => $blog_prefix . 'capabilities',
							'value' => 'bookingpress-staffmember',
							'compare' => 'NOT LIKE'
						),
					)
				);

				$wp_multiple_role_users = new WP_User_Query( $args2 );
				
				
				$wp_multiple_role_users_data = $wp_multiple_role_users->results;

				if( !empty( $wp_multiple_role_users_data ) ){
					foreach( $wp_multiple_role_users_data as $k => $multiple_user_data ){
						$user_roles = $multiple_user_data->roles;
						if( !in_array( 'administrator', $user_roles ) && in_array( 'bookingpress-customer', $user_roles ) ){
							continue;
						}
						$user_id = $multiple_user_data->ID;
						$include_users_from_search[] = array(
							'ID' => $user_id,
							'id' => $user_id,
							'user_login' => $multiple_user_data->user_login
						);
					}
				}
				

				$wpusers  = $include_users_from_search;

				
                $bookingpress_existing_user_data = $existing_users_data = array();
                if(!empty($wordpress_user_id)) {
                    $user_data = '';
                    $user_data = get_userdata($wordpress_user_id);                
                    if(!empty($user_data)) {        
                        $existing_users_data[] = array(
                            'value' => $user_data->ID,				
                            'label' => $user_data->user_login,
                        );                         
                    }                                
                }
                if (!empty($wpusers) ) {
                    foreach ( $wpusers as $wpuser ) {
						$user                  = array();
                        $user['value']         = $wpuser['id'];
                        $user['label']         = $wpuser['user_login'];
						
                        $existing_users_data[] = $user;
                    }
                }         
                $bookingpress_existing_user_data[] = array(
                    'category'     => esc_html__('Select Existing User', 'bookingpress-appointment-booking'),
                    'wp_user_data' => $existing_users_data,
                );
				$response['variant']               = 'success';
				$response['users']                 = $bookingpress_existing_user_data;
				$response['title']                 = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']                   = esc_html__( 'Staff Member Data.', 'bookingpress-appointment-booking' );
			}
			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_check_staffmember_module_activation() {
			$is_staffmember_module_activated = 0;
			$staffmember_addon_option_val    = get_option( 'bookingpress_staffmember_module' );
			if ( ! empty( $staffmember_addon_option_val ) && ( $staffmember_addon_option_val == 'true' ) ) {
				$is_staffmember_module_activated = 1;
			}
			return $is_staffmember_module_activated;
		}

		function get_bookingpress_staffmembersmeta_multiple( $bookingpress_staffmember_id, $bookingpress_staffmember_metakey ) {			
			global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta;
			$bookingpress_staffmembersmeta_value = array();
			if(is_array($bookingpress_staffmember_metakey)){

				$bookingpress_metaky_placeholder  = ' AND bookingpress_staffmembermeta_key IN(';
				$bookingpress_metaky_placeholder .= rtrim( str_repeat( '%s,', count( $bookingpress_staffmember_metakey ) ), ',' );
				$bookingpress_metaky_placeholder .= ')';
				array_unshift( $bookingpress_staffmember_metakey, $bookingpress_metaky_placeholder );
				$staffmeta_key_where_clause = call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_staffmember_metakey );
				$bookingpress_staffmembersmeta_details = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_staffmembermeta_key,bookingpress_staffmembermeta_value FROM {$tbl_bookingpress_staffmembers_meta} WHERE bookingpress_staffmember_id = %d".$staffmeta_key_where_clause, $bookingpress_staffmember_id ), ARRAY_A ); // phpcs:ignore
				if ( ! empty( $bookingpress_staffmembersmeta_details ) ) {
					//$bookingpress_staffmembersmeta_value = $bookingpress_staffmembersmeta_details['bookingpress_staffmembermeta_value'];
					foreach($bookingpress_staffmembersmeta_details as $bookingpress_staffmembersmeta_detail_single){
						$bookingpress_staffmembersmeta_value[$bookingpress_staffmembersmeta_detail_single['bookingpress_staffmembermeta_key']] = $bookingpress_staffmembersmeta_detail_single['bookingpress_staffmembermeta_value'];
					}
				}

			}
			return $bookingpress_staffmembersmeta_value;

		}

		function get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, $bookingpress_staffmember_metakey ) {
			global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta;
			$bookingpress_staffmembersmeta_value = '';

			$bookingpress_staffmembersmeta_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_meta} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmembermeta_key = %s", $bookingpress_staffmember_id, $bookingpress_staffmember_metakey ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_meta is a table name. false alarm

			if ( ! empty( $bookingpress_staffmembersmeta_details ) ) {
				$bookingpress_staffmembersmeta_value = $bookingpress_staffmembersmeta_details['bookingpress_staffmembermeta_value'];
			}
			return $bookingpress_staffmembersmeta_value;
		}

		function update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, $bookingpress_staffmember_metakey, $bookingpress_staffmember_metavalue ) {
			global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_staffmembers_meta;

			$bookingpress_exist_meta_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_staffmembermeta_id) as total FROM {$tbl_bookingpress_staffmembers_meta} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmembermeta_key = %s", $bookingpress_staffmember_id, $bookingpress_staffmember_metakey ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_meta is a table name. false alarm

			if ( $bookingpress_exist_meta_count > 0 ) {

				$bookingpress_exist_meta_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_meta} WHERE bookingpress_staffmember_id = %d AND bookingpress_staffmembermeta_key = %s", $bookingpress_staffmember_id, $bookingpress_staffmember_metakey ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_meta is a table name. false alarm
				$bookingpress_staffmembermeta_id = $bookingpress_exist_meta_details['bookingpress_staffmembermeta_id'];

				$bookingpress_staffmember_meta_details = array(
					'bookingpress_staffmember_id'        => $bookingpress_staffmember_id,
					'bookingpress_staffmembermeta_key'   => $bookingpress_staffmember_metakey,
					'bookingpress_staffmembermeta_value' => $bookingpress_staffmember_metavalue,
				);

				$bookingpress_update_where_condition = array(
					'bookingpress_staffmembermeta_id' => $bookingpress_staffmembermeta_id,
				);

				$wpdb->update( $tbl_bookingpress_staffmembers_meta, $bookingpress_staffmember_meta_details, $bookingpress_update_where_condition );
			} else {
				$bookingpress_staffmember_meta_details = array(
					'bookingpress_staffmember_id'        => $bookingpress_staffmember_id,
					'bookingpress_staffmembermeta_key'   => $bookingpress_staffmember_metakey,
					'bookingpress_staffmembermeta_value' => $bookingpress_staffmember_metavalue,
				);

				$wpdb->insert( $tbl_bookingpress_staffmembers_meta, $bookingpress_staffmember_meta_details );
			}
			return 1;
		}

		function bookingpress_create_staffmember( $bookingpress_staffmember_data, $bookingpress_existing_user_id = 0 ) {
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_staffmembers;
			$bookingpress_staffmember_id = $bookingpress_wpuser_id = 0;
			if ( ! empty( $bookingpress_staffmember_data ) ) {
				$bookingpress_staffmember_name      = $bookingpress_staffmember_data['bookingpress_staffmember_name'];
				$bookingpress_staffmember_position  = $bookingpress_staffmember_data['bookingpress_staffmember_position'];
				$bookingpress_staffmember_phone     = $bookingpress_staffmember_data['bookingpress_staffmember_phone'];
				$bookingpress_staffmember_firstname = $bookingpress_staffmember_data['bookingpress_staffmember_firstname'];
				$bookingpress_staffmember_lastname  = $bookingpress_staffmember_data['bookingpress_staffmember_lastname'];
				$bookingpress_staffmember_country   = $bookingpress_staffmember_data['bookingpress_staffmember_country'];
				$bookingpress_staffmember_email     = $bookingpress_staffmember_data['bookingpress_staffmember_email'];
				$bookingpress_staffmember_note      = $bookingpress_staffmember_data['bookingpress_staffmember_note'];
				$bookingpress_staffmember_visibility      = $bookingpress_staffmember_data['bookingpress_staffmember_visibility'];
				$bookingpress_staffmember_status    = $bookingpress_staffmember_data['bookingpress_staffmember_status'];
				$bookingpress_staffmember_country_dial_code = $bookingpress_staffmember_data['bookingpress_staffmember_country_dial_code'];

				if ( ! empty( $bookingpress_existing_user_id ) ) {
					$bookingpress_wpuser_id  = $bookingpress_existing_user_id;
						$staffmember_details = array(
							'bookingpress_wpuser_id' => $bookingpress_wpuser_id,
							'bookingpress_staffmember_position' => $bookingpress_staffmember_position,
							'bookingpress_staffmember_login' => $bookingpress_staffmember_email,
							'bookingpress_staffmember_status' => $bookingpress_staffmember_status,
							'bookingpress_staffmember_email' => $bookingpress_staffmember_email,
							'bookingpress_staffmember_firstname' => $bookingpress_staffmember_firstname,
							'bookingpress_staffmember_lastname' => $bookingpress_staffmember_lastname,
							'bookingpress_staffmember_phone' => $bookingpress_staffmember_phone,
							'bookingpress_staffmember_country_phone' => $bookingpress_staffmember_country,
							'bookingpress_staffmember_country_dial_code' => $bookingpress_staffmember_country_dial_code,
							'bookingpress_staffmember_created' => current_time( 'mysql' ),
						);
						$wpdb->insert( $tbl_bookingpress_staffmembers, $staffmember_details );
						$bookingpress_staffmember_id = $wpdb->insert_id;
				}
				if ( ! empty( $bookingpress_staffmember_id ) ) {
					$bookingpress_staffmember_note = ! empty( $bookingpress_staffmember_note ) ? $bookingpress_staffmember_note : '';
					$this->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'staffmember_note', $bookingpress_staffmember_note );
					$this->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'staffmember_visibility', $bookingpress_staffmember_visibility);					
				}
			}

			return array(
				'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
				'bookingpress_wpuser_id'      => $bookingpress_wpuser_id,
			);
		}

		function bookingpress_get_yearly_daysoff_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers_daysoff,$BookingPressPro,$bookingpress_global_options;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_staff_yearly_daysoff_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_staff_member_id = ! empty( $_REQUEST['staffmember_id'] ) ? intval( $_REQUEST['staffmember_id'] ) : 0;
			$bookingpress_selected_year   = ! empty( $_REQUEST['selected_year'] ) ? sanitize_text_field( $_REQUEST['selected_year'] ) : date( 'Y' );
			if ( ! empty( $bookingpress_staff_member_id ) ) {
				$bookingpress_daysoff                     = array();
				$bookingpress_global_settings = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_date_format     = $bookingpress_global_settings['wp_default_date_format'];

				$bookingpress_staffmember_daysoff_details = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_daysoff} WHERE bookingpress_staffmember_id = %d AND YEAR(bookingpress_staffmember_daysoff_date) = %s", $bookingpress_staff_member_id, $bookingpress_selected_year ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_daysoff is a table name. false alarm
				if ( ! empty( $bookingpress_staffmember_daysoff_details ) ) {
					foreach ( $bookingpress_staffmember_daysoff_details as $day_off ) {
						$day_off_arr                  = array();
						$day_off_arr['id']            = intval( $day_off['bookingpress_staffmember_daysoff_id'] );
						$day_off_arr['dayoff_name']   = sanitize_text_field( stripslashes_deep($day_off['bookingpress_staffmember_daysoff_name']) );
						$day_off_arr['dayoff_date']   = sanitize_text_field( $day_off['bookingpress_staffmember_daysoff_date'] );
						$day_off_arr['dayoff_formatted_date']   = date($bookingpress_date_format,strtotime($day_off['bookingpress_staffmember_daysoff_date']));
						$day_off_arr['dayoff_repeat'] = ! empty( $day_off['bookingpress_staffmember_daysoff_repeat'] ) ? true : false;
						$bookingpress_daysoff[]       = $day_off_arr;
					}
				}
				$response['msg']          = esc_html__( 'Daysoff data retrieved successfully.', 'bookingpress-appointment-booking' );
				$response['daysoff_data'] = $bookingpress_daysoff;
			} else {
				$response['msg']          = esc_html__( 'No daysoff data retrieved.', 'bookingpress-appointment-booking' );
				$response['daysoff_data'] = array();
			}
			$response['variant'] = 'success';
			$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			echo wp_json_encode( $response );
			exit();
		}

		function bookingpress_get_staffmember_special_day_func() {
			global $wpdb, $tbl_bookingpress_staffmembers_special_day, $bookingpress_global_options, $tbl_bookingpress_staffmembers_special_day_breaks;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_staff_special_days_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			
			$response['special_day_data']          = array();
			$response['disabled_special_day_data'] = array();
			$bookingpress_staff_member_id          = ! empty( $_REQUEST['staffmember_id'] ) ? intval( $_REQUEST['staffmember_id'] ) : 0;

			if ( ! empty( $bookingpress_staff_member_id ) && ! empty( $_REQUEST['action'] ) && sanitize_text_field( $_REQUEST['action'] == 'bookingpress_get_staffmember_special_day_details' ) ) {
				$bookingpress_global_settings = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_date_format     = $bookingpress_global_settings['wp_default_date_format'];
				$bookingpress_time_format     = $bookingpress_global_settings['wp_default_time_format'];
				$bookingpress_special_day     = array();

				$bookingpress_special_day_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers_special_day . ' WHERE bookingpress_staffmember_id = %d ', $bookingpress_staff_member_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day is a table name. false alarm
				if ( ! empty( $bookingpress_special_day_data ) ) {
					foreach ( $bookingpress_special_day_data as $special_day_key => $special_day ) {
						$special_day_arr                                     = $special_days_breaks = array();
						$special_day_start_date                              = ! empty( $special_day['bookingpress_special_day_start_date'] ) ? sanitize_text_field( $special_day['bookingpress_special_day_start_date'] ) : '';
						$special_day_end_date                                = ! empty( $special_day['bookingpress_special_day_end_date'] ) ? sanitize_text_field( $special_day['bookingpress_special_day_end_date'] ) : '';
						$special_day_service_id                              = ! empty( $special_day['bookingpress_special_day_service_id'] ) ? explode( ',', $special_day['bookingpress_special_day_service_id'] ) : '';
						$special_day_id                                      = ! empty( $special_day['bookingpress_staffmember_special_day_id'] ) ? intval( $special_day['bookingpress_staffmember_special_day_id'] ) : '';
						$special_day_arr['id']                               = $special_day_id;
						$special_day_arr['special_day_start_date']           = date('Y-m-d',strtotime($special_day_start_date));
						$special_day_arr['special_day_formatted_start_date'] = date( $bookingpress_date_format, strtotime( $special_day_start_date ) );
						$special_day_arr['special_day_end_date']             = date('Y-m-d',strtotime($special_day_end_date));

						$special_day_arr['special_day_formatted_end_date'] = date( $bookingpress_date_format, strtotime( $special_day_end_date ) );
						$special_day_arr['start_time']                     = $special_day['bookingpress_special_day_start_time'];
						$special_day_arr['formatted_start_time']           = date( $bookingpress_time_format, strtotime( sanitize_text_field( $special_day['bookingpress_special_day_start_time'] ) ) );
						$special_day_arr['end_time']                       = $special_day['bookingpress_special_day_end_time'];
						$special_day_arr['formatted_end_time']             = date( $bookingpress_time_format, strtotime( sanitize_text_field( $special_day['bookingpress_special_day_end_time'] ) ) );
						$special_day_arr['special_day_service']            = $special_day_service_id;

						// Fetch all breaks associated with special day
						$bookingpress_special_days_break = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers_special_day_breaks . ' WHERE bookingpress_special_day_id = %d ', $special_day_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day_breaks is a table name. false alarm
						if ( ! empty( $bookingpress_special_days_break ) && is_array( $bookingpress_special_days_break ) ) {
							foreach ( $bookingpress_special_days_break as $k3 => $v3 ) {
								$break_start_time                      = ! empty( $v3['bookingpress_special_day_break_start_time'] ) ? sanitize_text_field( $v3['bookingpress_special_day_break_start_time'] ) : '';
								$break_end_time                        = ! empty( $v3['bookingpress_special_day_break_end_time'] ) ? sanitize_text_field( $v3['bookingpress_special_day_break_end_time'] ) : '';
								$special_days_break_data               = array();
								$i                                     = 1;
								$special_days_break_data['id']         = $i;
								$special_days_break_data['start_time'] = $break_start_time;
								$special_days_break_data['end_time']   = $break_end_time;
								$special_days_break_data['formatted_start_time'] = date( $bookingpress_time_format, strtotime( $break_start_time ) );
								$special_days_break_data['formatted_end_time']   = date( $bookingpress_time_format, strtotime( $break_end_time ) );
								$i++;
								$special_days_breaks[] = $special_days_break_data;
							}
						}
						$special_day_arr['special_day_workhour'] = $special_days_breaks;
						$bookingpress_special_day[]              = $special_day_arr;
					}
				}
				$disabled_special_day_data             = $this->bookingpress_get_staffmember_special_days_dates();
				$response['msg']                       = esc_html__( 'Staff member Special Day data retrieved successfully.', 'bookingpress-appointment-booking' );
				$response['special_day_data']          = $bookingpress_special_day;
				$response['disabled_special_day_data'] = $disabled_special_day_data;
				$response['variant']                   = 'success';
				$response['title']                     = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			}
			echo wp_json_encode( $response );
			exit;
		}
		function bookingpress_get_staffmember_special_days_dates() {
			global $wpdb, $tbl_bookingpress_staffmembers_special_day;
			$disabled_date_arr          = array();
			$disable_added_special_days = $wpdb->get_results( 'SELECT bookingpress_special_day_start_date,bookingpress_special_day_end_date FROM ' . $tbl_bookingpress_staffmembers_special_day, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day is a table name. false alarm
			if ( ! empty( $disable_added_special_days ) ) {
				foreach ( $disable_added_special_days as $k => $v ) {
					$special_day_disable_date = $special_day_start_date = date( 'Y-m-d', strtotime( $v['bookingpress_special_day_start_date'] ) );
					$special_day_end_date     = date( 'Y-m-d', strtotime( $v['bookingpress_special_day_end_date'] ) );
					while ( $special_day_disable_date <= $special_day_end_date ) {
						array_push( $disabled_date_arr, $special_day_disable_date );
						$special_day_disable_date = date( 'Y-m-d', strtotime( '+1 days', strtotime( $special_day_disable_date ) ) );
					}
				}
			}
			return $disabled_date_arr;
		}

		function bookingpress_get_assign_service_data() {
			global $wpdb, $BookingPress,$tbl_bookingpress_staffmembers_services,$BookingPressPro;
			$response              = array();	

			if(!empty($_POST['action']) && $_POST['action'] == 'bookingpress_get_services_data' ) {  //phpcs:ignore
				$bpa_check_authorization = $this->bpa_check_authentication( 'get_staff_assigned_service_data', true, 'bpa_wp_nonce' );            
				if( preg_match( '/error/', $bpa_check_authorization ) ){
					$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
					$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

					$response['variant'] = 'error';
					$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
					$response['msg'] = $bpa_error_msg;

					wp_send_json( $response );
					die;
				}
			}

			$staffmember_id        = ! empty( $_REQUEST['staff_member_id'] ) ? intval( $_REQUEST['staff_member_id'] ) : '';
			$response['assigned_service_data'] = array();

			$bookingpress_staffmember_assigned_service_data = array();
			if ( ! empty( $staffmember_id ) ) {
				$bookingpress_staffmember_assigned_service_data_tmp = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_staffmember_id = %d", $staffmember_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is a table name. false alarm

				foreach ( $bookingpress_staffmember_assigned_service_data_tmp as $assigned_service_key => $assigned_service_val ) {
					$bookingpress_service_details = $BookingPress->get_service_by_id( $assigned_service_val['bookingpress_service_id'] );
					$service_data =  array(
						'assign_service_name'  => stripslashes_deep($bookingpress_service_details['bookingpress_service_name']),
						'assign_service_price' => $assigned_service_val['bookingpress_service_price'],
						'assign_service_formatted_price' => $BookingPress->bookingpress_price_formatter_with_currency_symbol($assigned_service_val['bookingpress_service_price']),
						'assign_service_min_capacity' => $assigned_service_val['bookingpress_service_min_capacity'],
						'assign_service_capacity' => $assigned_service_val['bookingpress_service_capacity'],
						'assign_service_id'    => $assigned_service_val['bookingpress_service_id'],
						'assign_service_display' => true
					);		
					$service_data = apply_filters('bookignpress_get_assigned_service_data_filter',$service_data, $staffmember_id);
					$bookingpress_staffmember_assigned_service_data[] = $service_data;
				}
			}
			$response['variant']               = 'success';
			$response['title']                 = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['msg']                   = esc_html__( 'Assigned service data retrieved successfully.', 'bookingpress-appointment-booking' );
			$response['assigned_service_data'] = $bookingpress_staffmember_assigned_service_data;

			if(!empty($_POST['action']) && $_POST['action'] == 'bookingpress_get_services_data' ) { //phpcs:ignore
				echo wp_json_encode( $response );
				exit();
			} else {
				return $bookingpress_staffmember_assigned_service_data;
			}
		}

		function bookingpress_staff_member_bulk_action() {

			global $BookingPress;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'bulk_staffmembers_actions', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			
			if ( ! empty( $_POST['bulk_action'] ) && sanitize_text_field( $_POST['bulk_action'] ) == 'delete' ) { // phpcs:ignore
				$delete_ids = ! empty( $_POST['delete_ids'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['delete_ids'] ) : array();// phpcs:ignore
				if ( ! empty( $delete_ids ) ) {
					foreach ( $delete_ids as $delete_key => $delete_val ) {
						$return = $this->bookingpress_delete_staff_member_func( $delete_val );

						if ( $return ) {
							$response['variant'] = 'success';
							$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
							$response['msg']     = esc_html__( 'Staff members has been deleted successfully.', 'bookingpress-appointment-booking' );
						} else {
							$bookingpress_error_msg = esc_html__( ' I am sorry', 'bookingpress-appointment-booking' ) . '! ' . esc_html__( 'This staff member can not be deleted because it has one or more appointments associated with it', 'bookingpress-appointment-booking' ) . '.';
							$response['variant']    = 'warning';
							$response['title']      = esc_html__( 'warning', 'bookingpress-appointment-booking' );
							$response['msg']        = $bookingpress_error_msg;
						}
					}
				}
			}
			echo wp_json_encode( $response );
			exit();
		}

		function bookingpress_delete_staff_member_func( $delete_id ) {

			global $wpdb, $tbl_bookingpress_staffmembers,$tbl_bookingpress_appointment_bookings,$bookingpress_global_options,$BookingPressPro,$tbl_bookingpress_staffmembers_services;
			$response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'delete_staffmember_details', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			if ( ! empty( $_POST['delete_id'] ) || intval( $delete_id ) ) { // phpcs:ignore
				$delete_staffmember_id = ! empty( $_POST['delete_id'] ) ? intval( $_POST['delete_id'] ) : intval( $delete_id ); // phpcs:ignore
				$current_date          = date( 'Y-m-d', current_time( 'timestamp' ) );
				if ( ! empty( $delete_staffmember_id ) ) {
					$bookingperss_appointments_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_staff_member_id = %d AND bookingpress_appointment_date >= %s AND (bookingpress_appointment_status != '3' AND bookingpress_appointment_status != '4') ", $delete_staffmember_id, $current_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

					if ( count( $bookingperss_appointments_data ) == 0 ) {
						$wpdb->update( $tbl_bookingpress_staffmembers, array( 'bookingpress_staffmember_status' => 4 ), array( 'bookingpress_staffmember_id' => $delete_staffmember_id ) );
						$bookingpress_existing_staffmember_details = $wpdb->get_row( $wpdb->prepare( "SELECT `bookingpress_wpuser_id`,`bookingpress_staffmember_position` FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $delete_staffmember_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
						$total_staffmembers = $wpdb->get_var('SELECT bookingpress_staffmember_id FROM ' . $tbl_bookingpress_staffmembers . ' order by bookingpress_staffmember_position DESC');// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_staffmembers is table name defined globally. False Positive alarm
						$new_position   = $total_staffmembers - 1;					
						if ($bookingpress_existing_staffmember_details['bookingpress_staffmember_position'] != $new_position ) {
							$this->bookingpress_position_staffmembers_func($bookingpress_existing_staffmember_details['bookingpress_staffmember_position'], $new_position);
						}
						$wpdb->delete( $tbl_bookingpress_staffmembers_services, array( 'bookingpress_staffmember_id' => $delete_staffmember_id ) );

						if ( ! empty( $bookingpress_existing_staffmember_details ) ) {
							$bookingpress_wp_user_id = ! empty( $bookingpress_existing_staffmember_details['bookingpress_wpuser_id'] ) ? $bookingpress_existing_staffmember_details['bookingpress_wpuser_id'] : '';
							if ( ! empty( $bookingpress_wp_user_id ) ) {
								$userObj = new WP_User( $bookingpress_wp_user_id );
								if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember', $bookingpress_wp_user_id ) ) {
									$userObj->remove_role( 'bookingpress-staffmember' );
									$staffmembers_default_cap = $bookingpress_global_options->bookingpress_global_options();
									$staffmembers_default_cap = ! empty( $staffmembers_default_cap['staffmember_default_cap'] ) ? $staffmembers_default_cap['staffmember_default_cap'] : array();
									foreach ( $staffmembers_default_cap as $staffmembers_default_cap_key => $staffmembers_default_cap_val ) {
										if ( $userObj->has_cap( $staffmembers_default_cap_val ) ) {
											$userObj->remove_cap( $staffmembers_default_cap_val );
										}
									}
								} else {
									$user_info  = get_userdata( $bookingpress_wp_user_id );
									$user_roles = $user_info->roles;
									if( in_array( 'bookingpress-staffmember', $user_roles ) && in_array( 'administrator', $user_roles ) ){
										$userObj->remove_role( 'bookingpress-staffmember' );
									}
								}
							}
						}
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( 'Staff member has been deleted successfully.', 'bookingpress-appointment-booking' );
						if ( ! empty( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_delete_staff_member' ) { // phpcs:ignore
							echo wp_json_encode( $response );
							exit();
						}
						return true;
					} else {
						$bookingpress_error_msg = esc_html__( ' I am sorry', 'bookingpress-appointment-booking' ) . '! ' . esc_html__( 'This staff member can not be deleted because it has one or more appointments associated with it', 'bookingpress-appointment-booking' ) . '.';
						$response['variant']    = 'warning';
						$response['title']      = esc_html__( 'warning', 'bookingpress-appointment-booking' );
						$response['msg']        = $bookingpress_error_msg;
						if ( ! empty( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'bookingpress_delete_staff_member' ) { // phpcs:ignore
								echo wp_json_encode( $response );
								exit();
						}
						return false;
					}
				}
			}
		}

		function bookingpress_edit_staff_member_func() {

			global $wpdb, $tbl_bookingpress_staffmembers,$tbl_bookingpress_staff_member_workhours, $tbl_bookingpress_staffmembers_daysoff,$BookingPressPro,$bookingpress_global_options;
			$bookingpress_options           = $bookingpress_global_options->bookingpress_global_options();
			
			$response = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'edit_staffmember_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$response['edit_data'] = array();
			$bookingpress_edit_id = $_REQUEST['staff_member_id'] = !empty($_POST['edit_id']) ? intval( $_POST['edit_id'] ) : 0;// phpcs:ignore
			if ( ! empty( $bookingpress_edit_id ) ) { // phpcs:ignore
				$bookingpress_edit_staff_members_details = array();
				$bookingpress_edit_staff_members_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d ORDER BY bookingpress_staffmember_id DESC", $bookingpress_edit_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm

				if ( ! empty( $bookingpress_edit_staff_members_details ) ) {
					$bookingpress_edit_staff_members_details['basic_details'] = 1;
					$bookingpress_wpuser_id                                   = $bookingpress_edit_staff_members_details['bookingpress_wpuser_id'];
					if ( ! empty( $bookingpress_wpuser_id ) ) {
						$bookingpress_edit_staff_members_details['bookingpress_wpuser_id'] = ! empty( get_user_by( 'ID', $bookingpress_wpuser_id ) ) ? $bookingpress_wpuser_id : '';
					} else {
						$bookingpress_edit_staff_members_details['bookingpress_wpuser_id'] = '';
					}
					$bookingpress_staffmember_note_data                     = $this->get_bookingpress_staffmembersmeta( $bookingpress_edit_id, 'staffmember_note' );
					$bookingpress_edit_staff_members_details['note']        = ! empty( $bookingpress_staffmember_note_data ) ? sanitize_text_field( stripslashes_Deep($bookingpress_staffmember_note_data) ) : '';
					$bookingpress_staffmember_visibility                    = $this->get_bookingpress_staffmembersmeta( $bookingpress_edit_id, 'staffmember_visibility' );
					$bookingpress_edit_staff_members_details['visibility']  = ! empty( $bookingpress_staffmember_visibility ) ? sanitize_text_field( $bookingpress_staffmember_visibility ) : 'public';				
					
					$bookingpress_get_existing_avatar_details               = $this->get_bookingpress_staffmembersmeta( $bookingpress_edit_id, 'staffmember_avatar_details' );
					$bookingpress_get_existing_avatar_details               = ! empty( $bookingpress_get_existing_avatar_details ) ? maybe_unserialize( $bookingpress_get_existing_avatar_details ) : array();
					$bookingpress_edit_staff_members_details['avatar_url']  = ! empty( $bookingpress_get_existing_avatar_details[0]['url'] ) ? esc_url_raw( $bookingpress_get_existing_avatar_details[0]['url'] ) : '';
					$bookingpress_edit_staff_members_details['avatar_name'] = ! empty( $bookingpress_get_existing_avatar_details[0]['name'] ) ? sanitize_text_field( $bookingpress_get_existing_avatar_details[0]['name'] ) : '';

					if(!empty($bookingpress_wpuser_id)) {
                        $user_data = '';                    
                        $user_data = get_userdata($bookingpress_wpuser_id);                    
                        if(!empty($user_data)) {                        
                            $bookingpress_existing_user_data[] = array(
                                'category' => __('Select Existing User','bookingpress-appointment-booking'),
                                'wp_user_data' => array(
                                    array(
                                        'value' => $user_data->ID,				
                                        'label' => $user_data->user_login,
                                    )
                                ),
                            );
                            $bookingpress_edit_staff_members_details['wp_user_list'] = $bookingpress_existing_user_data;                    
                        }
                    }    			
				}

				$bookingpress_edit_staff_members_details = apply_filters( 'bookingpress_staff_member_external_details', $bookingpress_edit_staff_members_details );
				
				$response['assigned_service_data']       = $this->bookingpress_get_assign_service_data();
				$response['edit_data']                   = $bookingpress_edit_staff_members_details;
				$response['msg']                         = esc_html__( 'Edit data retrieved successfully', 'bookingpress-appointment-booking' );
				$response['variant']                     = 'success';
				$response['title']                       = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			}
			echo wp_json_encode( $response );
			exit();
		}

		function bookingpress_get_staffmember_workhour_data_func(){
			global $wpdb, $tbl_bookingpress_staffmembers,$tbl_bookingpress_staff_member_workhours, $tbl_bookingpress_staffmembers_daysoff,$BookingPressPro,$bookingpress_global_options;
			$bookingpress_options           = $bookingpress_global_options->bookingpress_global_options();
			$response = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'get_staff_workhour_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$response['edit_data'] = array();

			if ( ! empty( $_POST['edit_id'] ) ) { // phpcs:ignore
				// Get workhours details
				$bookingpress_edit_id = !empty($_POST['edit_id']) ? intval($_POST['edit_id']) : ''; // phpcs:ignore

				$bookingpress_staff_member_workhours = $bookingpress_workhours_data = array();

				$where_clause = $wpdb->prepare( 'bookingpress_staffmember_id = %d AND bookingpress_staffmember_workhours_is_break = 0', $bookingpress_edit_id );
				$where_clause = apply_filters('bookingpress_modify_get_staff_workhour_where_clause', $where_clause, $_POST, $bookingpress_edit_id); // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

				$bookingpress_staff_member_workhours_details = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_staff_member_workhours} WHERE $where_clause",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is a table name. false alarm
				
				if ( ! empty( $bookingpress_staff_member_workhours_details ) ) {
					foreach ( $bookingpress_staff_member_workhours_details as $bookingpress_staff_member_workhour_key => $bookingpress_staff_member_workhour_val ) {
						$selected_start_time = $bookingpress_staff_member_workhour_val['bookingpress_staffmember_workhours_start_time'];
						$selected_end_time   = $bookingpress_staff_member_workhour_val['bookingpress_staffmember_workhours_end_time'];
						if ( $selected_start_time == null ) {
							$selected_start_time = 'Off';
						}
						if ( $selected_end_time == null ) {
							$selected_end_time = 'Off';
						}
						$bookingpress_staff_member_workhours[ $bookingpress_staff_member_workhour_val['bookingpress_staffmember_workday_key'] ] = array(
							'start_time' => $selected_start_time,
							'end_time'   => $selected_end_time,
						);
					}
					$bookingpress_break_time_details = array();
					$bookingpress_days_arr = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
					foreach ( $bookingpress_days_arr as $days_key => $days_val ) {
						$bookingpress_breaks_arr = array();
						$staff_break_where_clause = $wpdb->prepare( 'bookingpress_staffmember_workday_key = %s AND bookingpress_staffmember_workhours_is_break = 1 AND  bookingpress_staffmember_id = %d', $days_val, $bookingpress_edit_id );
						$staff_break_where_clause = apply_filters('bookingpress_modify_get_staff_break_workhour_where_clause', $staff_break_where_clause, $_POST, $bookingpress_edit_id, $days_val); // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
						$bookingpress_break_time_details = $wpdb->get_results( 'SELECT bookingpress_staffmember_workhours_start_time,bookingpress_staffmember_workhours_end_time FROM ' . $tbl_bookingpress_staff_member_workhours . ' WHERE '.$staff_break_where_clause, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is table name.
						if ( !empty($bookingpress_break_time_details)) {
							foreach($bookingpress_break_time_details as $key => $value) {
								$bookingpress_breaks_arr[] = array(
									'start_time' => $value['bookingpress_staffmember_workhours_start_time'],
									'formatted_start_time' => date( $bookingpress_options['wp_default_time_format'], strtotime( $value['bookingpress_staffmember_workhours_start_time'] ) ),
									'end_time'   => $value['bookingpress_staffmember_workhours_end_time'],
									'formatted_end_time'   => date( $bookingpress_options['wp_default_time_format'], strtotime( $value['bookingpress_staffmember_workhours_end_time'] ) ),								
								);
							}
						}
						$bookingpress_workhours_data[] = array(
							'day_name'    => ucfirst( $days_val ),
							'break_times' => $bookingpress_breaks_arr,
						);
					}
				}
				$bookingpress_configure_specific_workhour = $this->get_bookingpress_staffmembersmeta( $bookingpress_edit_id, 'bookingpress_configure_specific_workhour' );						
				$bookingpress_staff_members_workhour_details['bookingpress_configure_specific_workhour'] = !empty($bookingpress_configure_specific_workhour) &&  $bookingpress_configure_specific_workhour == 'true' ? true : false;
				$bookingpress_staff_members_workhour_details['workhours']                                = $bookingpress_staff_member_workhours;
				$bookingpress_staff_members_workhour_details['workhour_data']                            = $bookingpress_workhours_data;
				$response['edit_data']                   = $bookingpress_staff_members_workhour_details;
				$response['msg']                         = esc_html__( 'Edit data retrieved successfully', 'bookingpress-appointment-booking' );
				$response['variant']                     = 'success';
				$response['title']                       = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			}			
			echo wp_json_encode( $response );
			exit();
		}

		function bookingpress_upload_staff_member_avatar_func() {
			$bpa_check_authorization = $this->bpa_check_authentication( 'upload_staffmember_avatar', true, 'bookingpress_upload_staff_member_avatar' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			$return_data = array(
				'error'            => 0,
				'msg'              => '',
				'upload_url'       => '',
				'upload_file_name' => '',
			);

			$bookingpress_fileupload_obj = new bookingpress_fileupload_class( $_FILES['file'] );// phpcs:ignore

			if ( ! $bookingpress_fileupload_obj ) {
				$return_data['error'] = 1;
				$return_data['msg']   = $bookingpress_fileupload_obj->error_message;
			}

			$bookingpress_fileupload_obj->check_cap          = true;
			$bookingpress_fileupload_obj->check_nonce        = true;
			$bookingpress_fileupload_obj->nonce_data         = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
			$bookingpress_fileupload_obj->nonce_action       = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';
			$bookingpress_fileupload_obj->check_only_image   = true;
			$bookingpress_fileupload_obj->check_specific_ext = false;
			$bookingpress_fileupload_obj->allowed_ext        = array();

			$file_name                = current_time( 'timestamp' ) . '_' . sanitize_file_name( $_FILES['file']['name'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			$upload_dir               = BOOKINGPRESS_TMP_IMAGES_DIR . '/';
			$upload_url               = BOOKINGPRESS_TMP_IMAGES_URL . '/';
			$bookingpress_destination = $upload_dir . $file_name;

			$check_file = wp_check_filetype_and_ext( $bookingpress_destination, $file_name );
            
            if( empty( $check_file['ext'] ) ){
                $return_data['error'] = 1;
                $return_data['upload_error'] = $upload_file;
                $return_data['msg']   = esc_html__('Invalid file extension. Please select valid file', 'bookingpress-appointment-booking');
            } else {

				$upload_file = $bookingpress_fileupload_obj->bookingpress_process_upload( $bookingpress_destination );
				if ( $upload_file == false ) {
					$return_data['error'] = 1;
					$return_data['msg']   = ! empty( $upload_file->error_message ) ? $upload_file->error_message : esc_html__( 'Something went wrong while updating the file', 'bookingpress-appointment-booking' );
				} else {
					$return_data['error']            = 0;
					$return_data['msg']              = '';
					$return_data['upload_url']       = $upload_url . $file_name;
					$return_data['upload_file_name'] = $file_name; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				}
			}

			echo wp_json_encode( $return_data );
			exit();
		}

		function bookingpress_assign_capability( $bookingpress_staffmember_data, $role = 'bookingpress-staffmember' ) {
			global $bookingpress_global_options,$BookingPressPro;

			if ( ! empty( $bookingpress_staffmember_data ) ) {
				$args1                    = array(
					'role' => $role,
				);
				$total_staffmembers       = get_users( $args1 );
				$staffmembers_default_cap = $bookingpress_global_options->bookingpress_global_options();
				$staffmembers_default_cap = ! empty( $staffmembers_default_cap['staffmember_default_cap'] ) ? $staffmembers_default_cap['staffmember_default_cap'] : array();

				if ( ! empty( $total_staffmembers ) ) {
					foreach ( $total_staffmembers  as $staffmember_key => $staffmember_value ) {
						$user_id = $staffmember_value->ID;

						if ( ! $BookingPressPro->bookingpress_check_user_role( 'administrator', $user_id ) ) {
							$userObj = new WP_User( $user_id );
							foreach ( $staffmembers_default_cap as $staffmembers_default_cap_key => $staffmembers_default_cap_val ) {
								// Add Capabilities of the Staffmember Roles User
								if ( isset( $bookingpress_staffmember_data[ $staffmembers_default_cap_val ] ) && $bookingpress_staffmember_data
									[ $staffmembers_default_cap_val ] == 'true' ) {
									if ( ! $userObj->has_cap( $staffmembers_default_cap_val ) ) {
										$userObj->add_cap( $staffmembers_default_cap_val );
									}
								} else {
									if ( $userObj->has_cap( $staffmembers_default_cap_val ) ) {
										$userObj->remove_cap( $staffmembers_default_cap_val );
									}
								}
							}
						}
					}
				}
			}
		}

		function bookingpress_staffmember_assign_capability( $user_id ) {
			global $BookingPress,$bookingpress_global_options;
			$staffmembers_default_cap = $bookingpress_global_options->bookingpress_global_options();
			$staffmembers_default_cap = ! empty( $staffmembers_default_cap['staffmember_default_cap'] ) ? $staffmembers_default_cap['staffmember_default_cap'] : array();
			if ( ! empty( $user_id ) ) {
				$userObj = new WP_User( $user_id );
				foreach ( $staffmembers_default_cap as $staffmembers_default_cap_key => $staffmembers_default_cap_val ) {
					$bookingpress_staffmember_setting = $BookingPress->bookingpress_get_settings( $staffmembers_default_cap_val, 'staffmember_setting' );
					if ( $bookingpress_staffmember_setting == 'true' && ! $userObj->has_cap( $staffmembers_default_cap_val ) ) {
						$userObj->add_cap( $staffmembers_default_cap_val );
					}
				}
			}
		}

		function bookingpress_export_staffmember_data_func() {

			global $wpdb, $tbl_bookingpress_staffmembers, $tbl_bookingpress_appointment_bookings,$BookingPress,$bookingpress_global_options;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'export_staffmembers_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			

			$bookingpress_export_field = ! empty( $_REQUEST['export_field'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['export_field'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
			$bookingpress_search_data  = ! empty( $_REQUEST['search_data'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['search_data'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
			$bookingpress_search_query = '';

			if ( ! empty( $bookingpress_search_data['search_name'] ) ) {
				$bookingpress_search_staff_member_name = explode( ' ', $bookingpress_search_data['search_name'] );
				$bookingpress_search_query            .= ' AND (';
				$search_loop_counter                   = 1;
				foreach ( $bookingpress_search_staff_member_name as $bookingpress_search_staff_member_key => $bookingpress_search_staff_member_val ) {
					if ( $search_loop_counter > 1 ) {
						$bookingpress_search_query .= ' OR';
					}
					$bookingpress_search_query .= " (bookingpress_staffmember_login LIKE '%{$bookingpress_search_staff_member_val}%' OR bookingpress_staffmember_email LIKE '%{$bookingpress_search_staff_member_val}%' OR bookingpress_staffmember_firstname LIKE '%{$bookingpress_search_staff_member_val}%' OR bookingpress_staffmember_lastname LIKE '%{$bookingpress_search_staff_member_val}%')";

					$search_loop_counter++;
				}
				$bookingpress_search_query .= ' )';
			}

			$get_staffmembers = $wpdb->get_results( 'SELECT * FROM ' . $tbl_bookingpress_staffmembers . ' WHERE bookingpress_staffmember_status != 4 ' . $bookingpress_search_query . ' order by bookingpress_staffmember_position DESC', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm

			$bookingpress_staffmembers = array();

			if ( ! empty( $get_staffmembers ) && ! empty( $bookingpress_export_field ) ) {

				$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
				$default_date_time_format        = $bookingpress_global_options_arr['wp_default_date_format'] . ' ' . $bookingpress_global_options_arr['wp_default_time_format'];

				foreach ( $get_staffmembers as $staffmember ) {
					$bookingpress_staffmember_tmp_details = array();
					if ( in_array( 'first_name', $bookingpress_export_field ) ) {
						$bookingpress_staffmember_tmp_details['First Name'] = ! empty( $staffmember['bookingpress_staffmember_firstname'] ) ? '"' . sanitize_text_field( $staffmember['bookingpress_staffmember_firstname'] ) . '"' : '-';
					}
					if ( in_array( 'last_name', $bookingpress_export_field ) ) {
						$bookingpress_staffmember_tmp_details['Last Name'] = ! empty( $staffmember['bookingpress_staffmember_lastname'] ) ? '"' . sanitize_text_field( $staffmember['bookingpress_staffmember_lastname'] ) . '"' : '-';
					}
					if ( in_array( 'email', $bookingpress_export_field ) ) {
						$bookingpress_staffmember_tmp_details['Email'] = ! empty( $staffmember['bookingpress_staffmember_email'] ) ? '"' . sanitize_email( $staffmember['bookingpress_staffmember_email'] ) . '"' : '-';
					}
					if ( in_array( 'phone', $bookingpress_export_field ) ) {
						$bookingpress_staffmember_tmp_details['Phone'] = ! empty( $staffmember['bookingpress_staffmember_phone'] ) ? '"' . sanitize_text_field( $staffmember['bookingpress_staffmember_phone'] ) . '"' : '-';
					}
					if ( in_array( 'note', $bookingpress_export_field ) ) {
						$bookingpress_wpuser_id                       = $staffmember['bookingpress_wpuser_id'];
						$bookingpress_staffmember_note_data           = $this->get_bookingpress_staffmembersmeta( $staffmember['bookingpress_staffmember_id'], 'staffmember_note' );
						$bookingpress_staffmember_tmp_details['Note'] = ! empty( $bookingpress_staffmember_note_data ) ? '"' . sanitize_text_field( stripslashes_deep($bookingpress_staffmember_note_data) ) . '"' : '-';
					}
					if ( in_array( 'assigned_services', $bookingpress_export_field ) ) {
						$total_assigned_services                                   = $this->bookingpress_get_staffmember_service( $staffmember['bookingpress_staffmember_id'], 1 );
						$bookingpress_staffmember_tmp_details['Assigned Services'] = ! empty( $total_assigned_services ) ? '"' . $total_assigned_services . '"' : '0';
					}
					$bookingpress_staffmembers[] = $bookingpress_staffmember_tmp_details;
				}
			} else {
				$bookingpress_staffmembers = array();
			}
			$data = array();
			if ( ! empty( $bookingpress_staffmembers ) ) {
				array_push( $data, array_keys( $bookingpress_staffmembers[0] ) );
				foreach ( $bookingpress_staffmembers as $key => $value ) {
					array_push( $data, array_values( $value ) );
				}
			}
			$response['status'] = 'success';
			$response['data']   = $data;
			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_get_staffmember_last_appointment( $staffmember_id ) {
			global $wpdb,$tbl_bookingpress_appointment_bookings,$bookingpress_global_options;
			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			$default_date_time_format        = $bookingpress_global_options_arr['wp_default_date_format'] . ' ' . $bookingpress_global_options_arr['wp_default_time_format'];
			$last_appointment_data           = '';
			if ( ! empty( $staffmember_id ) ) {
				$last_appointment_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_created_at FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_staff_member_id = %d ORDER BY bookingpress_appointment_booking_id DESC LIMIT 1", $staffmember_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			}
			$last_appointment_booked_datetime = ! empty( $last_appointment_data['bookingpress_created_at'] ) ? date( $default_date_time_format, strtotime( $last_appointment_data['bookingpress_created_at'] ) ) : '-';
			return $last_appointment_booked_datetime;
		}

		function bookingpress_get_staffmember_total_appointment( $staffmember_id, $date = '', $status = array() ) {
			global $wpdb,$tbl_bookingpress_appointment_bookings;
			$total_appointments              = '';
			$bookingpress_search_query_where = 'WHERE 1=1 ';
			if ( ! empty( $date ) ) {
				$bookingpress_search_query_where .= " AND (bookingpress_appointment_date = '{$date}')";
			}
			if ( ! empty( $status ) && is_array( $status ) ) {
				$bookingpress_search_query_where .= ' AND (';
				$i                                = 0;
				foreach ( $status as $status_key => $status_value ) {
					if ( $i != 0 ) {
						$bookingpress_search_query_where .= ' OR';
					}
					$bookingpress_search_query_where .= " bookingpress_appointment_status ='{$status_value}'";
					$i++;
				}
				$bookingpress_search_query_where .= ' )';
			}
			if ( ! empty( $staffmember_id ) ) {
				$total_appointments = $wpdb->get_var( 'SELECT COUNT(bookingpress_appointment_booking_id) FROM ' . $tbl_bookingpress_appointment_bookings . ' ' . $bookingpress_search_query_where . ' AND bookingpress_staff_member_id = ' . $staffmember_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			}
			return $total_appointments;
		}
		function bookingpress_validate_staffmember_daysoff_func() {

			global $BookingPress,$wpdb, $tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_payment_logs,$tbl_bookingpress_customers,$bookingpress_global_options,$tbl_bookingpress_staffmembers, $BookingPressPro;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'validate_staff_daysoff_details', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_staffmember_id = ! empty( $_REQUEST['staffmember_id'] ) ? intval( $_REQUEST['staffmember_id'] ) : '';

			if(empty($bookingpress_staffmember_id )) {
				$bookingpress_current_user_id  = get_current_user_id();
				$bookingpress_staffmember_data = $wpdb->get_row( $wpdb->prepare( 'SELECT bookingpress_staffmember_id FROM ' . $tbl_bookingpress_staffmembers . ' WHERE bookingpress_wpuser_id = %d', $bookingpress_current_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
				$bookingpress_staffmember_id   = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ? intval( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) : 0;
			}

			if ( ! empty( $_REQUEST['selected_date_range'] ) && ! empty( $bookingpress_staffmember_id ) ) {
				$bookingpress_search_date = sanitize_text_field( $_REQUEST['selected_date_range'] );
				$bookingpress_date        = date( 'Y-m-d', strtotime( $bookingpress_search_date ) );

				$bookingpress_search_date_end = (isset($_REQUEST['selected_date_range_end']))?sanitize_text_field( $_REQUEST['selected_date_range_end'] ):'';
				$bookingpress_date_end        = date( 'Y-m-d', strtotime( $bookingpress_search_date_end ) );
				$bookingpress_status      = array( '1', '2' );

				if ( $bookingpress_date &&  $bookingpress_date == $bookingpress_date_end) {
					$total_appointments = $this->bookingpress_get_staffmember_total_appointment( $bookingpress_staffmember_id, $bookingpress_date, $bookingpress_status );
					if ( $total_appointments > 0 ) {
						$response['variant'] = 'warnning';
						$response['title']   = esc_html__( 'Warning', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( 'Appointment(s) are already booked during this time duration. Do you still want to continue?', 'bookingpress-appointment-booking' );
					} else {
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'success', 'bookingpress-appointment-booking' );
						$response['msg']     = '';
					}
				}else{
					if($bookingpress_date && $bookingpress_date_end){
						$startDate = strtotime($bookingpress_date);
						$endDate = strtotime($bookingpress_date_end);                 
						$bookingpress_child_holiday_dates = array();
						for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
							$date = date('Y-m-d', $currentDate);
							$bookingpress_child_holiday_dates[] = $date;
						} 
						if(!empty($bookingpress_child_holiday_dates)){
							$total_appointments = 0;
							foreach($bookingpress_child_holiday_dates as $holiday_date){
								$total_appointments = $this->bookingpress_get_staffmember_total_appointment( $bookingpress_staffmember_id, $holiday_date, $bookingpress_status );	
								if($total_appointments > 0){
									break;
								}
							}
							if ( $total_appointments > 0 ) {
								$response['variant'] = 'warnning';
								$response['title']   = esc_html__( 'Warning', 'bookingpress-appointment-booking' );
								$response['msg']     = esc_html__( 'Appointment(s) are already booked during this time duration. Do you still want to continue?', 'bookingpress-appointment-booking' );
							} else {
								$response['variant'] = 'success';
								$response['title']   = esc_html__( 'success', 'bookingpress-appointment-booking' );
								$response['msg']     = '';
							}							
						}						
					}
				}
			} else {
				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'success', 'bookingpress-appointment-booking' );
			}

			$staff_form_data = !empty( $_REQUEST['staff_form_data'] ) ? $_REQUEST['staff_form_data'] : array();
			$dayoff_label = esc_html__( 'Once Off', 'bookingpress-appointment-booking' );
			if( !empty( $staff_form_data['dayoff_repeat'] ) && true == $staff_form_data['dayoff_repeat'] ){
				$repeat_frequency = $staff_form_data['dayoff_repeat_frequency' ];
				$repeat_frequency_type = $staff_form_data[ 'dayoff_repeat_freq_type' ];
				$repeat_duration = $staff_form_data[ 'dayoff_repeat_duration' ];
				$repeat_times = $staff_form_data['dayoff_repeat_times'];
				$repeat_date = $staff_form_data['dayoff_repeat_date'];

				$repeat_date = apply_filters( 'bookingpress_change_repeat_date_timezone_to_wp', $repeat_date );

				$dayoff_label = $BookingPressPro->bookingpress_retrieve_daysoff_repeat_label( $repeat_duration, $repeat_frequency, $repeat_frequency_type, $repeat_times, $repeat_date );
			}


			$response['repeat_label'] = $dayoff_label;

			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_validate_staffmember_special_day_func() {
			global $wpdb,$tbl_bookingpress_appointment_bookings;

			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'validate_staffmember_special_days', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_staffmember_id = ! empty( $_REQUEST['staffmember_id'] ) ? intval( $_REQUEST['staffmember_id'] ) : '';
			if ( ! empty( $_REQUEST['selected_date_range'] ) && ! empty( $bookingpress_staffmember_id ) ) {
				$bookingpress_start_date         = date( 'Y-m-d', strtotime( sanitize_text_field( $_REQUEST['selected_date_range'][0] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated --Reason: data has been validated above
				$bookingpress_end_date           = date( 'Y-m-d', strtotime( sanitize_text_field( $_REQUEST['selected_date_range'][1] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated --Reason: data has been validated above
				$bookingpress_status             = array( '1', '2' );
				$total_appointments              = 0;
				$bookingpress_search_query_where = 'WHERE 1=1 ';
				if ( ! empty( $bookingpress_start_date ) && ! empty( $bookingpress_end_date ) && ! empty( $bookingpress_staffmember_id ) ) {
						$bookingpress_search_query_where .= " AND (bookingpress_appointment_date BETWEEN '{$bookingpress_start_date}' AND '{$bookingpress_end_date}') AND (bookingpress_staff_member_id = {$bookingpress_staffmember_id})";
				}
				if ( ! empty( $bookingpress_status ) && is_array( $bookingpress_status ) ) {
					$bookingpress_search_query_where .= ' AND (';
					$i                                = 0;
					foreach ( $bookingpress_status as $status_key => $status_value ) {
						if ( $i != 0 ) {
							$bookingpress_search_query_where .= ' OR';
						}
						$bookingpress_search_query_where .= " bookingpress_appointment_status ='{$status_value}'";
						$i++;
					}
					$bookingpress_search_query_where .= ' )';
				}

				$bookingpress_search_query_where = apply_filters( 'bookingpress_validate_staff_specia_days_where_caluse', $bookingpress_search_query_where);

				$total_appointments = $wpdb->get_var( 'SELECT COUNT(bookingpress_appointment_booking_id) FROM ' . $tbl_bookingpress_appointment_bookings . ' ' . $bookingpress_search_query_where ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
				if ( $total_appointments > 0 ) {
					$response['variant'] = 'warnning';
					$response['title']   = esc_html__( 'Warning', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'one or more appointments are already booked this time duration with this staffmember still you want to add the Special day', 'bookingpress-appointment-booking' );
				} else {
					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'success', 'bookingpress-appointment-booking' );
					$response['msg']     = '';
				}
			}
			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_add_service_validation_func() {
			global $wpdb,$tbl_bookingpress_staffmembers;			
			$bookingpress_staffmember_list = $wpdb->get_var('SELECT COUNT(bookingpress_staffmember_id) FROM ' . $tbl_bookingpress_staffmembers.' WHERE bookingpress_staffmember_status = 1'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_staffmembers is table name

			if(empty($_POST['bookingpress_assign_staffmember_data']) && $bookingpress_staffmember_list > 0)  { // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.				
				$response            = array();
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']     = esc_html__('Please assign staff member to service', 'bookingpress-appointment-booking');
                wp_send_json($response);
                die();				
			}
		}

	}

	global $bookingpress_pro_staff_members, $bookingpress_staff_member_vue_data_fields;
	$bookingpress_pro_staff_members = new bookingpress_pro_staff_members();

	$bookingpress_staff_member_vue_data_fields = array(
		'bulk_action'                              => 'bulk_action',
		'bulk_options'                             => array(
			array(
				'value' => 'bulk_action',
				'label' => __( 'Bulk Action', 'bookingpress-appointment-booking' ),
			),
			array(
				'value' => 'delete',
				'label' => __( 'Delete', 'bookingpress-appointment-booking' ),
			),
		),
		'items'                                    => array(),
		'totalItems'                               => 0,
		'currentPage'                              => 1,
		'is_display_loader'                        => '0',
		'multipleSelection'                        => array(),
		'open_staff_member_modal'                  => false,
		'is_disabled'                              => false,
		'is_display_save_loader'                   => '0',
		'staff_member_search'                      => '',
		'staff_members'                            => array(
			'avatar_url'                 => '',
			'avatar_name'                => '',
			'avatar_list'                => array(),
			'wp_user'                    => null,
			'firstname'                  => '',
			'lastname'                   => '',
			'email'                      => '',
			'phone'                      => '',
			'Password'                   => '',
			'staff_member_phone_country' => '',
			'staff_member_dial_code'     => '',
			'panel_password'             => '',
			'note'                       => '',
			'status'                     => false,
			'update_id'                  => 0,
			'_wpnonce'                   => '',
			'visibility'    			 => 'public', 
		),
		'staff_members_services'                   => array(),
		'staffShowFileList'                        => false,
		'wpUsersList'                              => array(),
		'pagination_val'                           => array(
			array(
				'text'  => '10',
				'value' => '10',
			),
			array(
				'text'  => '20',
				'value' => '20',
			),
			array(
				'text'  => '50',
				'value' => '50',
			),
			array(
				'text'  => '100',
				'value' => '100',
			),
			array(
				'text'  => '200',
				'value' => '200',
			),
			array(
				'text'  => '300',
				'value' => '300',
			),
			array(
				'text'  => '400',
				'value' => '400',
			),
			array(
				'text'  => '500',
				'value' => '500',
			),
		),
		'assigned_services'                        => array(),
		'bookingpress_configure_specific_workhour' => false,
		'workhours_timings'                        => array(
			'Monday'    => array(
				'start_time'       => '09:00:00',
				'end_time'         => '17:00:00',
				'break_start_time' => '',
				'break_end_time'   => '',
			),
			'Tuesday'   => array(
				'start_time'       => '09:00:00',
				'end_time'         => '17:00:00',
				'break_start_time' => '',
				'break_end_time'   => '',
			),
			'Wednesday' => array(
				'start_time'       => '09:00:00',
				'end_time'         => '17:00:00',
				'break_start_time' => '',
				'break_end_time'   => '',
			),
			'Thursday'  => array(
				'start_time'       => '09:00:00',
				'end_time'         => '17:00:00',
				'break_start_time' => '',
				'break_end_time'   => '',
			),
			'Friday'    => array(
				'start_time'       => '09:00:00',
				'end_time'         => '17:00:00',
				'break_start_time' => '',
				'break_end_time'   => '',
			),
			'Saturday'  => array(
				'start_time'       => 'Off',
				'end_time'         => 'Off',
				'break_start_time' => '',
				'break_end_time'   => '',
			),
			'Sunday'    => array(
				'start_time'       => 'Off',
				'end_time'         => 'Off',
				'break_start_time' => '',
				'break_end_time'   => '',
			),
		),
		'default_break_timings'                    => array(),
		'work_hours_days_arr'                      => array(),
		'selected_break_timings'                   => array(
			'Monday'    => array(),
			'Tuesday'   => array(),
			'Wednesday' => array(),
			'Thursday'  => array(),
			'Friday'    => array(),
			'Saturday'  => array(),
			'Sunday'    => array(),
		),
		'break_selected_day'                       => 'Monday',
		'break_timings'                            => array(
			'start_time' => '',
			'end_time'   => '',
		),
		'rules_add_break'                          => array(
			'start_time' => array(
				array(
					'required' => true,
					'message'  => esc_html__( 'Please enter start time', 'bookingpress-appointment-booking' ),
					'trigger'  => 'blur',
				),
			),
			'end_time'   => array(
				array(
					'required' => true,
					'message'  => esc_html__( 'Please enter end time', 'bookingpress-appointment-booking' ),
					'trigger'  => 'blur',
				),
			),
		),
		'open_add_break_modal'                     => false,
		'is_mask_display'                          => false,
		'break_modal_pos'                          => '254px',
		'break_modal_pos_right'                    => '',
		'checkAll'                                 => false,
		'is_multiple_checked'                      => array(),
		'checkedServices'                          => array(),
		'multipleSelection_category'               => array(),
		'staff_members_services_rules'             => array(),
		'is_edit_break'							   => '0',	
		'bookingpress_loading' 					   => false,
		'wordpress_user_id'						   => '',	
		'dragging'                   			   => false,
		'enabled'                    			   => true,
	);
}
?>
