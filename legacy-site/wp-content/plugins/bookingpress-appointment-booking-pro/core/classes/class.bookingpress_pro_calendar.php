<?php
$bookingpress_geoip_file = BOOKINGPRESS_PRO_LIBRARY_DIR . '/geoip/autoload.php';
require $bookingpress_geoip_file;
use GeoIp2\Database\Reader;

if ( ! class_exists( 'bookingpress_pro_calendar' ) ) {
	class bookingpress_pro_calendar Extends BookingPress_Core {
		function __construct() {
			add_filter( 'bookingpress_modify_calendar_view_file_path', array( $this, 'bookingpress_modify_calendar_file_path_func' ), 10 );
			add_filter( 'bookingpress_modify_calendar_data_fields', array( $this, 'bookingpress_modify_calendar_data_fields_func' ), 10 );
			add_filter( 'bookingpress_modify_calendar_appointment_class', array( $this, 'bookingpress_modify_calendar_appointment_class_func' ), 10, 2 );

			//Modify calendar loading data
			add_filter('bookingpress_modify_calendar_loading_data', array($this, 'bookingpress_modify_calendar_loading_data_func'));

			add_action('bookingpress_add_dynamic_vue_methods_for_calendar', array($this, 'bookingpress_add_dynamic_vue_methods_for_calendar_func'), 10);
			add_action('bookingpress_calendar_add_appointment_model_reset', array( $this, 'bookingpress_calendar_add_appointment_model_reset_callback' ) );

			add_action('bookingpress_calendar_reset_filter',array($this,'bookingpress_calendar_reset_filter_func'));
			
			add_filter('bookingpress_modify_calendar_appointment_details', array($this, 'bookingpress_modify_calendar_appointment_details_func'), 10, 2);

			add_filter('bookingpress_check_edit_is_appointment_already_booked', array($this, 'bookingpress_check_edit_is_appointment_already_booked_func'), 10, 2);

			add_filter('bookingpress_modify_popover_appointment_data', array($this, 'bookingpress_modify_popover_appointment_data_func'), 10);

			add_filter('bookingpress_modify_popover_appointment_data_query', array($this, 'bookingpress_modify_popover_appointment_data_query_func'), 10, 2);
		}
		
		function bookingpress_modify_popover_appointment_data_query_func($appointment_query_dynamic_arr, $posted_data){
			global $bookingpress_pro_staff_members, $wpdb, $BookingPressPro;
			$is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			if($is_staffmember_module_activated) {
				$staff_member_filter_val = (isset($posted_data['search_data']) && isset($posted_data['search_data']['selected_staff_member'])) ? $posted_data['search_data']['selected_staff_member'] : array();
				if(!empty($staff_member_filter_val)) {
					$bookingpress_search_staff_member_id  = implode(',', $staff_member_filter_val);
					$where_query = " AND (appointment.bookingpress_staff_member_id IN ({$bookingpress_search_staff_member_id}))";
					$appointment_query_dynamic_arr['where_query'] = $where_query;
				}
				if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {
					$bookingpress_user_id        = get_current_user_id();
					$bookingpress_staffmember_id = $bookingpress_pro_staff_members->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
					$where_query = " AND (bookingpress_staff_member_id = {$bookingpress_staffmember_id})";
					$appointment_query_dynamic_arr['where_query'] = $where_query;
				}
				$appointment_columns=',appointment.bookingpress_staff_first_name, appointment.bookingpress_staff_last_name, appointment.bookingpress_staff_email_address';
				$appointment_query_dynamic_arr['appointment_columns'] = $appointment_columns;
			}
			return $appointment_query_dynamic_arr;
		}

		function bookingpress_modify_popover_appointment_data_func($appointment_data)
		{
			if($appointment_data) {
				global $bookingpress_pro_staff_members, $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_staffmembers;
				$is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
				if($is_staffmember_module_activated) {
					if(!empty($appointment_data['bookingpress_popover_appointemnt_data'])) {
						foreach ($appointment_data['bookingpress_popover_appointemnt_data'] as $key => $appointment_userdata) {
							$staff_first_name = isset($appointment_userdata['bookingpress_staff_first_name']) ? $appointment_userdata['bookingpress_staff_first_name'] : '';
							$staff_last_name = isset($appointment_userdata['bookingpress_staff_last_name']) ? $appointment_userdata['bookingpress_staff_last_name'] : '';
							$staff_email = isset($appointment_userdata['bookingpress_staff_email_address']) ? $appointment_userdata['bookingpress_staff_email_address'] : '';
							$staff_display_name = !empty($staff_first_name) ? $staff_first_name : '';
							$staff_display_name .= !empty($staff_display_name) ? ' ' : '';
							$staff_display_name .= !empty($staff_last_name) ? $staff_last_name : '';
							$staff_display_name = empty($staff_display_name) && !empty($staff_email) ? $staff_email : $staff_display_name;
							$appointment_data['bookingpress_popover_appointemnt_data'][$key]['bookingpress_staff_displayname'] = $staff_display_name;
						}
					}
				}
			}
			return $appointment_data;
		}

		/**
		 * Function for check is editted appointment already booked or not
		 *
		 * @param  mixed $is_appointment_already_booked
		 * @param  mixed $bookingpress_appointment_id
		 * @return void
		 */
		function bookingpress_check_edit_is_appointment_already_booked_func($is_appointment_already_booked, $bookingpress_appointment_id){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_services, $tbl_bookingpress_staffmembers_services;

			$booked_appointment_details = !empty($_POST['appointment_data']) ? $_POST['appointment_data'] : array(); //phpcs:ignore
			$selected_bring_members = ! empty($booked_appointment_details['selected_bring_members']) ? intval($booked_appointment_details['selected_bring_members']) - 1 : 0;
			$total_required_slot = 1 + $selected_bring_members;

			if(!empty($booked_appointment_details)){
				
				$bookingpress_appointment_date       = $booked_appointment_details['appointment_booked_date'];
				$bookingpress_appointment_start_time = $booked_appointment_details['appointment_booked_time'];

				if(!empty($bookingpress_appointment_id)){
					
					$bookingpress_service_id = !empty($booked_appointment_details['appointment_selected_service']) ? intval($booked_appointment_details['appointment_selected_service']) : 0;
					$bookingpress_staff_id = !empty($booked_appointment_details['selected_staffmember']) ? intval($booked_appointment_details['selected_staffmember']) : 0;

					if(!empty($bookingpress_service_id)){
						//Get Service Max Capacity
						$bookingpress_max_capacity = $bookingpress_pro_services->bookingpress_get_service_max_capacity($bookingpress_service_id);
						$total_booked_appointment = 0;

						if(!empty($bookingpress_staff_id)){
							$bookingpress_get_staff_cap_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_service_capacity FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_staffmember_id = %d AND bookingpress_service_id = %d", $bookingpress_staff_id, $bookingpress_service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_services is table name defined globally. False Positive alarm

							if(!empty($bookingpress_get_staff_cap_data['bookingpress_service_capacity'])){
								$bookingpress_max_capacity = floatval($bookingpress_get_staff_cap_data['bookingpress_service_capacity']);
							}

							$total_booked_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT count(bookingpress_appointment_booking_id) as total_appointment,SUM(bookingpress_selected_extra_members - 1) as total_extra_members FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_service_id = %d AND bookingpress_staff_member_id = %d", $bookingpress_appointment_id, '2', '1', $bookingpress_appointment_date, $bookingpress_appointment_start_time, $bookingpress_service_id, $bookingpress_staff_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm							

							if(!empty($total_booked_appointment_data)) {
								$total_booked_appointment = $total_booked_appointment_data['total_appointment'] + $total_booked_appointment_data['total_extra_members'];
							}

						}else{
							$total_booked_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT count(bookingpress_appointment_booking_id) as total_appointment,SUM(bookingpress_selected_extra_members - 1) as total_extra_members FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_service_id = %d", $bookingpress_appointment_id, '2', '1', $bookingpress_appointment_date, $bookingpress_appointment_start_time, $bookingpress_service_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

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
			}

			return $is_appointment_already_booked;
		}
		
		/**
		 * Function for modify calendar appointment details listing
		 *
		 * @param  mixed $calendar_bookings_data
		 * @param  mixed $appointment_details
		 * @return void
		 */
		function bookingpress_modify_calendar_appointment_details_func($calendar_bookings_data, $appointment_details){
			global $BookingPress, $BookingPressPro;
			$use_event_title = ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) && !$BookingPressPro->bookingpress_check_capability( 'bookingpress_customers' ) ) ? true : false;
			if(!empty($appointment_details)) {
				$bookingpress_appointment_id = $appointment_details['bookingpress_appointment_booking_id'];
				foreach($calendar_bookings_data as $calendar_booking_key => $calendar_booking_val){
					if ( $use_event_title && $bookingpress_appointment_id == $calendar_booking_val['appointment_id']) {
						$bookingpress_event_slot_title = stripslashes_deep($appointment_details['bookingpress_service_name']);
						$calendar_bookings_data[$calendar_booking_key]['title'] = $bookingpress_event_slot_title;
					}
				}
			}
			if(!empty($appointment_details) && !empty($appointment_details['bookingpress_service_duration_unit']) && ($appointment_details['bookingpress_service_duration_unit'] == 'd') ){
				$bookingpress_service_duration = intval($appointment_details['bookingpress_service_duration_val']);
				$bookingpress_appointment_start_date = date('Y-m-d', strtotime($appointment_details['bookingpress_appointment_date']));
				$bookingpress_appointment_end_date = date('Y-m-d', strtotime("+{$bookingpress_service_duration} days", strtotime($bookingpress_appointment_start_date)));
				$bookingpress_appointment_id = $appointment_details['bookingpress_appointment_booking_id'];

				foreach($calendar_bookings_data as $calendar_booking_key => $calendar_booking_val){
					if($bookingpress_appointment_id == $calendar_booking_val['appointment_id']){						
						$calendar_bookings_data[$calendar_booking_key]['end'] = $bookingpress_appointment_end_date.' 00:00:00';
					}
				}
			}
			return $calendar_bookings_data;
		}
		
		/**
		 * Function for add execute code for reset the form
		 *
		 * @return void
		 */
		function bookingpress_calendar_reset_filter_func(){
			?>
			vm.search_data.selected_staff_member = '';
			vm.appointment_formdata.complete_payment_url_selection = 'do_nothing';
			vm.appointment_formdata.complete_payment_url_selected_method = [];
			<?php
		}
		
		/**
		 * Function for add execute code for reset the form
		 *
		 * @return void
		 */
		function bookingpress_calendar_add_appointment_model_reset_callback(){
			?>
			let appointment_meta_fields = vm.appointment_formdata.bookingpress_appointment_meta_fields_value;				
			for( let k in appointment_meta_fields ){
				let currentVal = appointment_meta_fields[k];
				if( "boolean" == typeof currentVal ){
					vm.appointment_formdata.bookingpress_appointment_meta_fields_value[k] = false;
				} else if( "string" == typeof currentVal ){
					vm.appointment_formdata.bookingpress_appointment_meta_fields_value[k] = "";
				} else if( "object" == typeof currentVal ){
					vm.appointment_formdata.bookingpress_appointment_meta_fields_value[k] = [];
				}
			}
			
			let appointment_form_fields  = vm.bookingpress_form_fields;
			for( let m in appointment_form_fields ){
				let currentval = appointment_form_fields[m];					
				if(currentval.bookingpress_field_type == 'file') {
					vm.bookingpress_form_fields[m]['bpa_file_list'] = [];
				}
			}				

			vm.appointment_formdata.selected_extra_services_ids = '';
			for(m in vm.bookingpress_loaded_extras) {
				for(i in vm.bookingpress_loaded_extras[m]) {
					vm.bookingpress_loaded_extras[m][i]['bookingpress_is_selected'] = false;
				}					
			}
			vm.appointment_formdata.total_amount = 0;
			vm.appointment_formdata.total_amount_with_currency = vm2.bookingpress_price_with_currency_symbol( 0 );	
			vm.appointment_formdata.subtotal = 0;
			vm.appointment_formdata.subtotal_with_currency = vm2.bookingpress_price_with_currency_symbol( 0 );			
			<?php
		}
		
		/**
		 * Function for add dynamic vue methods for calendar module
		 *
		 * @return void
		 */
		function bookingpress_add_dynamic_vue_methods_for_calendar_func(){
			global $BookingPress, $bookingpress_notification_duration;
			?>
				saveProAppointmentBooking(bookingAppointment){
					const vm = new Vue();
					const vm2 = this
					
					let is_timeslot_display = vm2.is_timeslot_display;
					if( '0' == is_timeslot_display ){
						vm2[bookingAppointment].appointment_booked_time = "00:00:00";
					}

					vm2.saveAppointmentBooking(bookingAppointment);
				},
				bookingpress_calendar_staffmember_filter(currentelement, value) {
					const vm = this;
					vm.bpa_display_calendar_loader = 1;
					let all_staff_target = document.querySelector( '.bpa-sf-item.bpa-sf-item__is-all-staff' );
					let staff_member_arr = [];
					if(value=='all') {
						let list_items = document.querySelectorAll( '.bpa-sf-item-data.__bpa-is-active' );
						if( list_items.length > 0 ){
							for( l = 0; l < list_items.length; l++ ){
								list_items[l].classList.remove("__bpa-is-active")
							}
						}
						if(currentelement.target.classList.contains('__bpa-is-active')) {
							all_staff_target.classList.remove('__bpa-is-active');
						}
						else {
							all_staff_target.classList.add('__bpa-is-active');
						}
					}
					else {
						all_staff_target.classList.remove('__bpa-is-active');
						if( document.querySelector(`.bpa-sf-item[value="${value}"]`).classList.contains('__bpa-is-active') ){
							document.querySelector(`.bpa-sf-item[value="${value}"]`).classList.remove( '__bpa-is-active' );
							all_staff_target.classList.add('__bpa-is-active');
						} else {
							let all_other_staffmembers = document.querySelectorAll(`.bpa-sf-item`);
							all_other_staffmembers.forEach( (element,index) => {
								element.classList.remove( '__bpa-is-active' );
							});
							document.querySelector(`.bpa-sf-item[value="${value}"]`).classList.add('__bpa-is-active');
							staff_member_arr = [value];
						}
					}
					vm.search_data.selected_staff_member = staff_member_arr;

					vm.loadCalendar(vm.activeView);
				},
				bookingpress_calendar_staffmember_filter_change(selected_val){
					const vm= this;
					vm.bpa_display_calendar_loader = 1;
					vm.search_data.selected_staff_member=[selected_val];
					vm.loadCalendar(vm.activeView);
				},
				bpa_move_staff_nav_next(){
					let element = document.getElementById( "bpa-sf-items-wrapper" );

					let scrollLeft = element.scrollLeft;

					element.scrollTo({
						left: ( scrollLeft + 150 ),
						behavior: "smooth",
					});
				},
				bpa_move_staff_nav_prev(){
					let element = document.getElementById( "bpa-sf-items-wrapper" );

					let scrollLeft = element.scrollLeft;

					element.scrollTo({
						left: ( scrollLeft - 150 ),
						behavior: "smooth",
					});
				},
            <?php
            do_action('bookingpress_customer_add_dynamic_vue_methods');
		}
		
		/**
		 * Function for modify calendar loading data
		 *
		 * @param  mixed $calendar_bookings_data
		 * @return void
		 */
		function bookingpress_modify_calendar_loading_data_func($calendar_bookings_data){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;
			if(!empty($calendar_bookings_data) && is_array($calendar_bookings_data) ){
				foreach($calendar_bookings_data as $k => $v){
					$bookingpress_appointment_id = $v['appointment_id'];

					$bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

					if(!empty($bookingpress_appointment_data['bookingpress_service_duration_unit']) && ($bookingpress_appointment_data['bookingpress_service_duration_unit'] == "d") ){
						$bookingpress_service_duration_val = $bookingpress_appointment_data['bookingpress_service_duration_val'];

						$bookingpress_appointment_end_date = date('Y-m-d H:i:s', strtotime($v['start']." +".$bookingpress_service_duration_val." days"));
						$calendar_bookings_data[$k]['end'] = $bookingpress_appointment_end_date;
					}
				}
			}
			return $calendar_bookings_data;
		}
		
		/**
		 * Function for modify calendar appointment class as per appointment status
		 *
		 * @param  mixed $bookingpress_appointment_class
		 * @param  mixed $bookingpress_appointment_status
		 * @return void
		 */
		function bookingpress_modify_calendar_appointment_class_func( $bookingpress_appointment_class, $bookingpress_appointment_status ) {
			if($bookingpress_appointment_status == '5'){
				$bookingpress_appointment_class .= ' bpa-cal-event-card--no-show';
			}else if($bookingpress_appointment_status == '6'){
				$bookingpress_appointment_class .= ' bpa-cal-event-card--completed';
			}
			return $bookingpress_appointment_class;
		}
		
		/**
		 * Function for modify calendar module data fields
		 *
		 * @param  mixed $bookingpress_calendar_vue_data_fields
		 * @return void
		 */
		function bookingpress_modify_calendar_data_fields_func( $bookingpress_calendar_vue_data_fields ) {
			global $wpdb, $BookingPressPro, $bookingpress_pro_staff_members, $BookingPress, $bookingpress_service_extra, $bookingpress_bring_anyone_with_you, $tbl_bookingpress_staffmembers, $bookingpress_coupons, $tbl_bookingpress_form_fields, $bookingpress_global_options, $bookingpress_pro_services, $tbl_bookingpress_extra_services, $tbl_bookingpress_staffmembers_services;

			$bookingpress_calendar_vue_data_fields['is_timeslot_display'] = '1';

			$bookingpress_calendar_vue_data_fields['is_staffmember_activated'] = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			
			$bookigpress_time_format_for_booking_form =  $BookingPress->bookingpress_get_customize_settings('bookigpress_time_format_for_booking_form','booking_form');
			$bookigpress_time_format_for_booking_form =  !empty($bookigpress_time_format_for_booking_form) ? $bookigpress_time_format_for_booking_form : '2';
			$bookingpress_calendar_vue_data_fields['bookigpress_time_format_for_booking_form'] = $bookigpress_time_format_for_booking_form;			

			//Add appointment data variables
			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			
			$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? $bookingpress_global_options_arr['bookingpress_staffmember_singular_name'] : esc_html_e('Staff Member', 'bookingpress-appointment-booking');

			$bookingpress_calendar_vue_data_fields['selected_staffmember'] = '';
			$bookingpress_calendar_vue_data_fields['rules']['selected_staffmember'] = array(
                array(
                    'required' => true,
                    'message'  => esc_html__('Please select', 'bookingpress-appointment-booking')." ".esc_html($bookingpress_singular_staffmember_name),
                    'trigger'  => 'change',
                ),
            );

			$bookingpress_calendar_vue_data_fields['bookingpress_extras_popover_modal'] = false;
			$bookingpress_calendar_vue_data_fields['bookingpress_service_extras'] = array();

			$bookingpress_calendar_vue_data_fields['is_tax_enable'] = (is_plugin_active('bookingpress-tax/bookingpress-tax.php'))?1:0;
			$bookingpress_calendar_vue_data_fields['is_custom_service_duration'] = (is_plugin_active('bookingpress-custom-service-duration/bookingpress-custom-service-duration.php'))?1:0;			

			$bookingpress_calendar_vue_data_fields['is_extras_enable'] = $bookingpress_service_extra->bookingpress_check_service_extra_module_activation();
			$bookingpress_calendar_vue_data_fields['is_staff_enable'] = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_calendar_vue_data_fields['is_bring_anyone_with_you_enable'] = $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation();
			$bookingpress_calendar_vue_data_fields['is_coupon_enable'] = $bookingpress_coupons->bookingpress_check_coupon_module_activation();
			$bookingpress_calendar_vue_data_fields['bookingpress_allow_coupon_code'] = $bookingpress_coupons->bookingpress_check_coupon_module_activation();

			$bookingpress_calendar_vue_data_fields['appointment_formdata']['bookingpress_staffmembers_lists'] = array();
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['bookingpress_bring_anyone_max_capacity'] = 0;
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['bookingpress_bring_anyone_min_capacity'] = 0;

			$bookingpress_calendar_vue_data_fields['appointment_formdata']['selected_extra_services'] = array();
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['selected_extra_services_ids'] = '';
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['selected_staffmember'] = '';
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['selected_bring_members'] = 0;

			$bookingpress_calendar_vue_data_fields['appointment_formdata']['subtotal'] = 0;
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['subtotal_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['extras_total'] = 0;
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['extras_total_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['tax_percentage'] = 0;
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['tax'] = 0;
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['tax_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);

			$bookingpress_price_setting_display_option = $BookingPress->bookingpress_get_settings('price_settings_and_display', 'payment_setting');
            $bookingpress_calendar_vue_data_fields['appointment_formdata']['tax_price_display_options'] = $bookingpress_price_setting_display_option;

            $bookingpress_tax_order_summary = $BookingPress->bookingpress_get_settings('display_tax_order_summary', 'payment_setting');
            $bookingpress_calendar_vue_data_fields['appointment_formdata']['display_tax_order_summary'] = $bookingpress_tax_order_summary;

            $bookingpress_tax_order_summary_text = $BookingPress->bookingpress_get_settings('included_tax_label', 'payment_setting');
            $bookingpress_calendar_vue_data_fields['appointment_formdata']['included_tax_label'] = stripslashes_deep($bookingpress_tax_order_summary_text);

			$bookingpress_calendar_vue_data_fields['appointment_formdata']['applied_coupon_code'] = '';
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['applied_coupon_details'] = array();
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['coupon_discounted_amount'] = 0;
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['coupon_discounted_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['total_amount'] = 0;
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['total_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);

			$bookingpress_calendar_vue_data_fields['appointment_formdata']['mark_as_paid'] = false;
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['complete_payment_url_selection'] = 'do_nothing';
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['complete_payment_url_selected_method'] = array();

			$bookingpress_calendar_vue_data_fields['coupon_apply_loader'] = 0;
			$bookingpress_calendar_vue_data_fields['coupon_code_msg'] = '';
			$bookingpress_calendar_vue_data_fields['bpa_coupon_apply_disabled'] = 0;
			$bookingpress_calendar_vue_data_fields['coupon_applied_status'] = '';

			//Get custom fields
			$bookingpress_form_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_is_default = %d AND bookingpress_is_customer_field = %d ORDER BY bookingpress_field_position ASC", 0, 0), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm

			$bookingpress_listing_fields_value = $bookingpress_appointment_meta_fields_value = array();
			if(!empty($bookingpress_form_fields)){
				foreach($bookingpress_form_fields as $k3 => $v3){
					
					$bookingpress_form_fields[$k3]['bookingpress_field_error_message']= stripslashes_deep($v3['bookingpress_field_error_message']);
					$bookingpress_form_fields[$k3]['bookingpress_field_label'] = stripslashes_deep($v3['bookingpress_field_label']);
					$bookingpress_form_fields[$k3]['bookingpress_field_placeholder'] = stripslashes_deep($v3['bookingpress_field_placeholder']);

					$bookingpress_field_meta_key = $v3['bookingpress_field_meta_key'];
					$bookingpress_field_options = json_decode($v3['bookingpress_field_options'], TRUE);
					$bookingpress_form_fields[$k3]['bookingpress_field_options'] = $bookingpress_field_options;
					if($v3['bookingpress_field_type'] == "checkbox"){
						$bookingpress_field_values = json_decode($v3['bookingpress_field_values'], TRUE);


						$temp_form_fields_data = array();
						$fmeta_key = $bookingpress_field_meta_key;

						foreach( $bookingpress_field_values as $k4 => $v4 ){
							$bookingpress_form_fields[$k3][ $fmeta_key] [ $k4 ] = '';	
						}

						$bookingpress_appointment_meta_fields_value[$fmeta_key] = array();
						
						$bookingpress_form_fields[$k3]['selected_services'] = (isset($bookingpress_field_options['selected_services']))?$bookingpress_field_options['selected_services']:'';
					}else{
						$bookingpress_form_fields[$k3]['selected_services'] = (isset($bookingpress_field_options['selected_services']))?$bookingpress_field_options['selected_services']:'';
						$bookingpress_appointment_meta_fields_value[$bookingpress_field_meta_key] = '';
						$bookingpress_listing_fields_value[$bookingpress_field_meta_key] = array(
							'label' => $v3['bookingpress_field_label'],
							'value' => '',
						);
					}
				}
			}

			if(!empty($bookingpress_form_fields)){
				foreach($bookingpress_form_fields as $k4 => $v4){
					if(($v4['bookingpress_form_field_name'] == "Repeater") || ($v4['bookingpress_form_field_name'] == "2 Col") || ($v4['bookingpress_form_field_name'] == "3 Col") || ($v4['bookingpress_form_field_name'] == "4 Col") ){
						unset($bookingpress_form_fields[$k4]);
					}
				}

				$bookingpress_form_fields = array_values($bookingpress_form_fields);
			}

			if( !empty( $bookingpress_form_fields ) ) {
				$bookingpress_temp_form_fields = [];
				$n5 = 0;
				foreach( $bookingpress_form_fields as $k5 => $v5 ){

					if( 'file' == $v5['bookingpress_field_type'] ){
						$action_url = admin_url('admin-ajax.php');
						$action_data = array(
							'action' => 'bpa_front_file_upload',
							'_wpnonce' => wp_create_nonce( 'bpa_file_upload_' . $v5['bookingpress_field_meta_key'] ),
							'field_key' => $v5['bookingpress_field_meta_key']
						);
						$v5['bpa_action_url'] = $action_url;
						$v5['bpa_ref_name'] = str_replace('_', '', $v5['bookingpress_field_meta_key']);
						$action_data['bpa_ref'] =$v5['bpa_ref_name'];
						$v5['bpa_file_list'] = array();
						$v5['bpa_action_data'] = $action_data;
						$action_data['bpa_accept_files'] = !empty( $v5['bookingpress_field_options']['allowed_file_ext'] ) ?  base64_encode( $v5['bookingpress_field_options']['allowed_file_ext'] ) : '';
					}

					if( ( ( $n5 + 1 ) % 3 ) == 0 ){
						$v5['is_separator'] = false;
						$bookingpress_temp_form_fields[] = $v5;
						$bookingpress_temp_form_fields[] = array(
							'is_separator' => true
						);
					} else {
						$v5['is_separator'] = false;
						$bookingpress_temp_form_fields[] = $v5;
					}
					$n5++;
				}
				$bookingpress_form_fields = $bookingpress_temp_form_fields;
			}

			$bookingpress_calendar_vue_data_fields['bookingpress_form_fields'] = $bookingpress_form_fields;
			$bookingpress_calendar_vue_data_fields['appointment_formdata']['bookingpress_appointment_meta_fields_value'] = $bookingpress_appointment_meta_fields_value;
			$bookingpress_calendar_vue_data_fields['bookingpress_listing_fields_value'] = $bookingpress_listing_fields_value;

			//Add Customer Data Variables
			$bookingpress_calendar_vue_data_fields['open_customer_modal'] = false;
			$bookingpress_options = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_country_list = $bookingpress_options['country_lists'];
			$bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');

			$bookingpress_calendar_vue_data_fields['phone_countries_details'] = json_decode($bookingpress_country_list);
			$bookingpress_calendar_vue_data_fields['loading'] = false;
			$bookingpress_calendar_vue_data_fields['customer'] = array(
				'avatar_url' => '',
				'avatar_name' => '',
				'avatar_list' => array(),
				'wp_user' => null,
				'firstname' => '',
				'lastname' => '',
				'email' => '',
				'phone' => '',
				'customer_phone_country' => $bookingpress_phone_country_option,
				'customer_phone_dial_code' => '',
				'note' => '',
				'update_id' => 0,
				'_wpnonce' => '',
				'password' => '',
			);
			
			$bpa_customer_form_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_is_customer_field = %d ORDER BY bookingpress_field_position ASC", 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
            $bpa_customer_fields = array();
            if( !empty( $bpa_customer_form_fields ) ){
                foreach( $bpa_customer_form_fields as $x => $cs_form_fields ){
                    //$bpa_customer_fields['field_id'] = $cs_form_fields['']   
                    $bpa_customer_fields[ $x ] = $cs_form_fields;
                    $bpa_customer_fields[ $x ]['bookingpress_field_values'] = json_decode( $cs_form_fields['bookingpress_field_values'], true );
                    $bpa_customer_fields[ $x ]['bookingpress_field_options'] = json_decode( $cs_form_fields['bookingpress_field_options'], true );
                    $bpa_customer_fields[ $x ]['bookingpress_field_key'] = '';//$cs_form_fields['bookingpress_field_meta_key'];
                    if( 'checkbox' == $cs_form_fields['bookingpress_field_type'] ){
                        $bpa_customer_fields[ $x ]['bookingpress_field_key'] = array();
                        foreach( $bpa_customer_fields[ $x ]['bookingpress_field_values'] as $chk_key => $chk_val ){
                            //$bpa_customer_fields[ $x ]['bookingpress_field_key'][ $chk_key ] = false;
							$bookingpress_calendar_vue_data_fields['customer']['bpa_customer_field'][ $cs_form_fields['bookingpress_field_meta_key'] . '_' . $chk_key ] = false;
                        }
                    } else {
						$bookingpress_calendar_vue_data_fields['customer']['bpa_customer_field'][$cs_form_fields['bookingpress_field_meta_key']] = $bpa_customer_fields[ $x ]['bookingpress_field_key'];
					}
                }
            }
            $bookingpress_calendar_vue_data_fields['bookingpress_customer_fields'] = $bpa_customer_fields;

			$bookingpress_custom_fields = $bookingpress_calendar_vue_data_fields['bookingpress_form_fields'];
			$bookingpress_custom_fields_validation_arr = array();
			if(!empty($bookingpress_custom_fields)){
				foreach($bookingpress_custom_fields as $custom_field_key => $custom_field_val){
					if(isset($custom_field_val['bookingpress_field_is_default']) && $custom_field_val['bookingpress_field_is_default'] == 0 ) {
						$bookingpress_field_meta_key = $custom_field_val['bookingpress_field_meta_key'];

						if(isset($custom_field_val['bookingpress_field_required']) && $custom_field_val['bookingpress_field_required'] == 1) {
							$bookingpress_field_err_msg = stripslashes_deep($custom_field_val['bookingpress_field_error_message']);						
							$bookingpress_field_err_msg = empty($bookingpress_field_err_msg) && !empty($custom_field_val['bookingpress_field_label']) ? stripslashes_deep($custom_field_val['bookingpress_field_label']).' '.__('is required','bookingpress-appointment-booking') : $bookingpress_field_err_msg;
							$bookingpress_custom_fields_validation_arr[$bookingpress_field_meta_key][] = array(
								'required' => 1,
								'message' => $bookingpress_field_err_msg,
								'trigger' => 'change'
							);					
						}
											
						if(!empty($custom_field_val['bookingpress_field_options']['minimum'])) {
							$bookingpress_custom_fields_validation_arr[ $bookingpress_field_meta_key][] = array( 
								'min' => intval($custom_field_val['bookingpress_field_options']['minimum']),
								'message'  => __('Minimum','bookingpress-appointment-booking').' '.$custom_field_val['bookingpress_field_options']['minimum'].' '.__('character required','bookingpress-appointment-booking'),
								'trigger'  => 'blur',
							);
						}
						if(!empty($custom_field_val['bookingpress_field_options']['maximum'])) {
							$bookingpress_custom_fields_validation_arr[$bookingpress_field_meta_key][] = array( 
								'max' => intval($custom_field_val['bookingpress_field_options']['maximum']),
								'message'  => __('Maximum','bookingpress-appointment-booking').' '.$custom_field_val['bookingpress_field_options']['maximum'].' '.__('character allowed','bookingpress-appointment-booking'),
								'trigger'  => 'blur',
							);
						}
					}
				}
			}

			$bookingpress_calendar_vue_data_fields['custom_field_rules'] = $bookingpress_custom_fields_validation_arr;

			$bookingpress_calendar_vue_data_fields['customer_detail_save'] = false;
			$bookingpress_calendar_vue_data_fields['wpUsersList'] = array();
			$bookingpress_calendar_vue_data_fields['savebtnloading'] = false;
			$bookingpress_calendar_vue_data_fields['customer_rules'] = array(
				'firstname' => array(
					array(
						'required' => true,
						'message'  => esc_html__('Please enter firstname', 'bookingpress-appointment-booking'),
						'trigger'  => 'blur',
					),
				),
				'lastname'  => array(
					array(
						'required' => true,
						'message'  => esc_html__('Please enter lastname', 'bookingpress-appointment-booking'),
						'trigger'  => 'blur',
					),
				),
				'email'     => array(
					array(
						'required' => true,
						'message'  => esc_html__('Please enter email address', 'bookingpress-appointment-booking'),
						'trigger'  => 'blur',
					),
					array(
						'type'    => 'email',
						'message' => esc_html__('Please enter valid email address', 'bookingpress-appointment-booking'),
						'trigger' => 'blur',
					),
				),
			);

			$bookingpress_calendar_vue_data_fields['cusShowFileList'] = false;
			$bookingpress_calendar_vue_data_fields['is_display_loader'] = '0';
			$bookingpress_calendar_vue_data_fields['bpa_display_calendar_loader'] = 0;
			$bookingpress_calendar_vue_data_fields['is_disabled'] = false;
			$bookingpress_calendar_vue_data_fields['is_display_save_loader'] = '0';
			$bookingpress_calendar_vue_data_fields['bookingpress_tel_input_props'] = array(
				'defaultCountry' => $bookingpress_phone_country_option,
				'validCharactersOnly' => true,
				'inputOptions' => array(
					'placeholder' => '',
				)
			);
			if ( ! empty( $bookingpress_phone_country_option ) && $bookingpress_phone_country_option == 'auto_detect' ) {
				// Get visitors ip address
				$bookingpress_ip_address = $BookingPressPro->boookingpress_get_visitor_ip();
				try {
					$bookingpress_country_reader = new Reader( BOOKINGPRESS_PRO_LIBRARY_DIR . '/geoip/inc/GeoLite2-Country.mmdb' );
					$bookingpress_country_record = $bookingpress_country_reader->country( $bookingpress_ip_address );
					if ( ! empty( $bookingpress_country_record->country ) ) {
						$bookingpress_country_name     = $bookingpress_country_record->country->name;
						$bookingpress_country_iso_code = $bookingpress_country_record->country->isoCode;
						$bookingpress_calendar_vue_data_fields['bookingpress_tel_input_props']['defaultCountry'] = $bookingpress_country_iso_code;
					}
				} catch ( Exception $e ) {
					$bookingpress_error_message = $e->getMessage();
				}
			}

			$bookingpress_calendar_vue_data_fields['wordpress_user_id'] = '';

			$bookingpress_loaded_services = $bookingpress_calendar_vue_data_fields['appointment_services_list'];
			$bookingpress_service_extras = $bookingpress_service_staffmembers = array();
			
			if(!empty($bookingpress_loaded_services)){
				foreach($bookingpress_loaded_services as $service_key => $service_val){
					$category_services = !empty($service_val['category_services']) ? $service_val['category_services'] : array();
					if(!empty($category_services)){
						foreach($category_services as $ser_key => $ser_val){
							$service_id = intval($ser_val['service_id']);
							if(!empty($service_id)){
								

								/** service max capacity */
								$service_max_capacity = $bookingpress_pro_services->bookingpress_get_service_max_capacity($service_id);
								
								if( empty( $service_max_capacity ) ){
									$service_max_capacity = 1;
								}
								$bookingpress_loaded_services[ $service_key ]['category_services'][ $ser_key ]['service_max_capacity'] = $service_max_capacity;

								$bookingpress_extra_services_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_extra_services} WHERE bookingpress_service_id = %d", $service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is a table name. false alarm

								if(!empty($bookingpress_extra_services_data)){
									foreach($bookingpress_extra_services_data as $extra_key => $extra_val){
										$bookingpress_extra_service_price_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($extra_val['bookingpress_extra_service_price']);

										$bookingpress_extra_services_data[$extra_key]['bookingpress_extra_service_price_with_currency'] = $bookingpress_extra_service_price_with_currency;
										$bookingpress_extra_services_data[$extra_key]['bookingpress_is_display_description'] = 0;

										$bookingpress_extra_services_data[$extra_key]['bookingpress_selected_qty'] = 1;
										$bookingpress_extra_services_data[$extra_key]['bookingpress_is_selected'] = false;

										$bookingpress_calendar_vue_data_fields['appointment_formdata']['selected_extra_services'][$extra_val['bookingpress_extra_services_id']] = $bookingpress_extra_services_data[$extra_key];
									}
								}

								$bookingpress_service_extras[$service_id] = $bookingpress_extra_services_data;


								//Get service staff members details
								$bookingpress_staffmembers_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers_services} WHERE bookingpress_service_id = %d", $service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
								if(!empty($bookingpress_staffmembers_details)){
									foreach($bookingpress_staffmembers_details as $bookingpress_staff_key => $bookingpress_staff_val){
										$bookingpress_staffmember_id = intval($bookingpress_staff_val['bookingpress_staffmember_id']);

										$bookingpress_staff_price_with_currency = $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_staff_val['bookingpress_service_price']);
										$bookingpress_staffmembers_details[$bookingpress_staff_key]['staff_price_with_currency'] = $bookingpress_staff_price_with_currency;

										//Get staff profile details
										$bookingpress_staff_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_staffmember_id = %d", $bookingpress_staffmember_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is table name.

										$bookingpress_staffmembers_details[$bookingpress_staff_key]['profile_details'] = $bookingpress_staff_details;
									}
								}

								$bookingpress_service_staffmembers[$service_id] = $bookingpress_staffmembers_details;
							}
						}
					}
				}
			}

			$bookingpress_calendar_vue_data_fields['appointment_services_list'] = $bookingpress_loaded_services;
			$bookingpress_calendar_vue_data_fields['bookingpress_loaded_extras'] = $bookingpress_service_extras;
			$bookingpress_calendar_vue_data_fields['bookingpress_loaded_staff'] = $bookingpress_service_staffmembers;

			$bookingpress_currency_separator = $BookingPress->bookingpress_get_settings('price_separator', 'payment_setting');
			$bookingpress_calendar_vue_data_fields['bookingpress_currency_separator'] = $bookingpress_currency_separator;			
			$bookingpress_decimal_points = $BookingPress->bookingpress_get_settings('price_number_of_decimals', 'payment_setting');
			$bookingpress_decimal_points = intval($bookingpress_decimal_points);
			$bookingpress_calendar_vue_data_fields['bookingpress_decimal_points'] = $bookingpress_decimal_points;

            $bookingpress_currency_name = $BookingPress->bookingpress_get_settings('payment_default_currency', 'payment_setting');
            $bookingpress_calendar_vue_data_fields['bookingpress_currency_name'] = $bookingpress_currency_name;
            $bookingpress_calendar_vue_data_fields['bookingpress_currency_symbol'] = $BookingPress->bookingpress_get_currency_symbol($bookingpress_currency_name);

            $bookingpress_price_symbol_position = $BookingPress->bookingpress_get_settings('price_symbol_position', 'payment_setting');
            $bookingpress_calendar_vue_data_fields['bookingpress_currency_symbol_position'] = $bookingpress_price_symbol_position;			

			$bookingpress_calendar_vue_data_fields['bookingpress_is_extra_enable'] = $bookingpress_service_extra->bookingpress_check_service_extra_module_activation();
			
			if(($bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation()) && (! $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ))  ){
				$bookingpress_calendar_vue_data_fields['bookingpress_calenar_filter_no_staff_class'] = '';
				$bookingpress_calendar_vue_data_fields['bookingpress_calenar_filter_class'] = 'bpa-fsc__addon-filter-belt';
			}else {
				$bookingpress_calendar_vue_data_fields['bookingpress_calenar_filter_no_staff_class'] = '__bpa-fsc-no-staff';
				$bookingpress_calendar_vue_data_fields['bookingpress_calenar_filter_class'] = '';//__bpa-fsc-is-location
			}
			return $bookingpress_calendar_vue_data_fields;
		}
		
		/**
		 * Function for modify calendar view file path 
		 *
		 * @param  mixed $bookingpress_calendar_view_path
		 * @return void
		 */
		function bookingpress_modify_calendar_file_path_func( $bookingpress_calendar_view_path ) {

			$bookingpress_calendar_view_path = BOOKINGPRESS_PRO_VIEWS_DIR . '/calendar/manage_calendar.php';
			return $bookingpress_calendar_view_path;
		}
	}
}

global $bookingpress_pro_calendar;
$bookingpress_pro_calendar = new bookingpress_pro_calendar();
