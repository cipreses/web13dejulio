<?php
$bookingpress_geoip_file = BOOKINGPRESS_PRO_LIBRARY_DIR . '/geoip/autoload.php';
require $bookingpress_geoip_file;
use GeoIp2\Database\Reader;

if ( ! class_exists( 'bookingpress_pro_customize' ) ) {
	class bookingpress_pro_customize Extends BookingPress_Core {

		function __construct() {
			
			add_filter( 'bookingpress_modify_customize_view_file_path', array( $this, 'bookingpress_modify_customize_view_file_path_func' ) );
			add_filter( 'bookingpress_customize_add_dynamic_data_fields', array( $this, 'bookingpress_modify_customize_data_fields_func' ), 10 );
			add_filter( 'bookingpress_modify_field_data_before_load', array( $this, 'bookingpress_modify_form_field_data' ), 10, 2 );
			add_filter( 'bookingpress_modify_field_data_before_prepare', array( $this, 'bookingpress_modify_field_data_before_prepare_func' ), 10, 2 );
			add_filter( 'bookingpress_modify_form_field_data_before_save', array( $this, 'bookingpress_modify_form_field_data_before_save_func' ), 10, 2 );
			add_filter( 'bookingpress_arrange_form_fields_outside', array( $this, 'bookingpress_arrange_form_fields_func' ), 10, 2 );
			add_action( 'bookingpress_customize_dynamic_vue_methods', array( $this, 'bookingpress_pro_customize_dynamic_vue_methods' ) );
			add_action( 'bookingpress_delete_removed_fields', array( $this, 'bookingpress_delete_removed_field_func' ) );
			add_action( 'bookingpress_insert_inner_fields', array( $this, 'bookingpress_insert_inner_fields_func' ), 10, 3 );
			add_action( 'bookingpress_after_save_field_settings_method', array( $this, 'bookingpress_load_saved_field_settings' ) );
			add_action( 'wp_ajax_bpa_load_preset_field_data', array( $this, 'bpa_load_preset_field_data_func' ) );
			add_action( 'bookingpress_after_load_field_settings', array( $this, 'bookingpress_after_load_field_settings_func' ) );
			add_action( 'bookingpress_before_save_field_settings_method', array( $this, 'bookingpress_before_save_field_settings_method_func' ) );
			add_filter( 'bookingpress_get_my_booking_customize_data_filter', array( $this, 'bookingpress_get_my_booking_customize_data_filter_func' ) );

			add_filter( 'bookingpress_after_selecting_booking_service', array( $this, 'bookingpress_after_selecting_booking_service_func' ), 10, 1 );
			add_filter('bookingpress_get_booking_form_customize_data_filter',array($this, 'bookingpress_get_booking_form_customize_data_filter_func'),10,1);

			add_action('bookingpress_before_save_customize_form_settings',array($this,'bookingpress_before_save_customize_form_settings_func'));
			add_action('bookingpress_before_save_customize_booking_form',array($this,'bookingpress_before_save_customize_booking_form_func'));
			add_action('bookingpress_add_booking_form_customize_data',array($this,'bookingpress_add_booking_form_customize_data_func'));	
			
			add_filter( 'bookingpress_modify_loaded_form_data', array( $this, 'bookingpress_modify_bookingpress_form_sequance_data') );

			add_filter( 'bookingpress_rearrange_form_sequence_arr', array( $this, 'bookingpress_rearrange_form_sequence_arr_func') );
			
		}

		
		/**
		 * bpa function for customization data get
		 *
		 * @param  mixed $request_detail
		 * @return void
		 */
		function bookingpress_bpa_get_customer_fields($request_detail=array()){			
			global $BookingPress,$tbl_bookingpress_services,$wpdb,$BookingPressPro,$tbl_bookingpress_appointment_bookings,$bookingpress_global_options,$tbl_bookingpress_payment_logs,$tbl_bookingpress_form_fields,$tbl_bookingpress_customers;

			$result = array();						
			$result["customer_form_fields"] = array();	
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => $result));

			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){
				
				$user_id = isset($user_detail['user_id']) ? intval($user_detail['user_id']) : '';
				$bpa_login_customer_id = $user_id;	

				$bookingpress_form_fields  = $wpdb->get_results( $wpdb->prepare('SELECT * FROM ' . $tbl_bookingpress_form_fields . ' WHERE bookingpress_is_customer_field = %d ORDER BY bookingpress_field_position ASC',0), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
				$bookingpress_get_customer_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_wpuser_id =%d", $bpa_login_customer_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is table name defined globally. False Positive alarm						
				
				if(empty($bookingpress_get_customer_details)){
					$bookingpress_get_customer_details = array();
					$bookingpress_current_user_obj = get_user_by('id', $bpa_login_customer_id);
					$bookingpress_customer_email = ! empty($bookingpress_current_user_obj->data->user_email) ? $bookingpress_current_user_obj->data->user_email : '';
					$bookingpress_get_customer_details['bookingpress_user_firstname'] = stripslashes_deep(get_user_meta($bpa_login_customer_id, 'first_name', true));
					$bookingpress_get_customer_details['bookingpress_user_lastname'] = stripslashes_deep(get_user_meta($bpa_login_customer_id, 'last_name', true));
					$bookingpress_get_customer_details['bookingpress_user_email'] = stripslashes_deep( $bookingpress_customer_email );
				}


				$bookingpress_form_fields_error_msg_arr = $bookingpress_form_fields_new = array();
				foreach ( $bookingpress_form_fields as $bookingpress_form_field_key => $bookingpress_form_field_val ) {
					$bookingpress_field_options = ! empty( $bookingpress_form_field_val['bookingpress_field_options'] ) ? json_decode( $bookingpress_form_field_val['bookingpress_field_options'], true ) : '';
					$bookingpress_customer_field = ( ( ! empty( $bookingpress_field_options['used_for_user_information'] ) && $bookingpress_field_options['used_for_user_information'] == 'true' ) || $bookingpress_form_field_val['bookingpress_is_customer_field'] );
					if ( ( ! empty( $bookingpress_field_options['visibility'] ) && $bookingpress_field_options['visibility'] == 'always')) {

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

						if( 1 == $bookingpress_form_field_val['bookingpress_is_customer_field'] ){
							$bookingpress_field_setting_fields_tmp['is_hide'] = 0;
							$bookingpress_field_setting_fields_tmp['field_options']['layout'] = '1col';
						}
						if ( $bookingpress_form_field_val['bookingpress_field_required'] == '1' ) {
							$bookingpress_error_msg = !empty($bookingpress_field_setting_fields_tmp['error_message']) ? stripslashes_deep($bookingpress_field_setting_fields_tmp['error_message']) : '' ;		
							$bookingpress_error_msg = empty($bookingpress_error_msg) && !empty($bookingpress_form_field_val['bookingpress_field_label']) ?stripslashes_deep($bookingpress_form_field_val['bookingpress_field_label']).' '.__('is required','bookingpress-appointment-booking') : $bookingpress_error_msg;

							$bookingpress_field_setting_fields_tmp['all_error_message'][] = array(
								'type' => 'required',
								'message'  => $bookingpress_error_msg,
								'trigger'  => 'blur',
							);
							if( $bookingpress_v_model_value == 'appointment_terms_conditions') {									   
								$bookingpress_field_setting_fields_tmp['all_error_message'][] = array(
									'type' => 'required',
									'message'  => $bookingpress_error_msg,
									'trigger'  => 'change',
								);
							}

						}
						
						if(!empty($bookingpress_field_setting_fields_tmp['field_options']['minimum'])) {
							$bookingpress_field_setting_fields_tmp['all_error_message'][] = array( 
								'type' => 'min',
								'min' => intval($bookingpress_field_setting_fields_tmp['field_options']['minimum']),
								'message'  => __('Minimum','bookingpress-appointment-booking').' '.$bookingpress_field_setting_fields_tmp['field_options']['minimum'].' '.__('character required','bookingpress-appointment-booking'),
								'trigger'  => 'blur',
							);
						}
	
						if(!empty($bookingpress_field_setting_fields_tmp['field_options']['maximum'])) {
							$bookingpress_field_setting_fields_tmp['all_error_message'][] = array( 
								'type' => 'max',
								'max' => intval($bookingpress_field_setting_fields_tmp['field_options']['maximum']),
								'message'  => __('Maximum','bookingpress-appointment-booking').' '.$bookingpress_field_setting_fields_tmp['field_options']['maximum'].' '.__('character allowed','bookingpress-appointment-booking'),
								'trigger'  => 'blur',
							);
						}							

						array_push( $bookingpress_form_fields_new, $bookingpress_field_setting_fields_tmp );

						
					}
				}


				$result["customer_form_fields"] = $bookingpress_form_fields_new;																
				$response = array('status' => 1, 'message' => '', 'response' => array('result' => $result));

			}
		

			return $response;
		}
		

		/**
		 * bpa function for customization data get
		 *
		 * @param  mixed $request_detail
		 * @return void
		*/
		function bookingpress_bpa_get_customization_setting_func($request_detail=array()){

			global $BookingPress,$BookingPressPro,$bookingpress_pro_staff_members,$bookingpress_pro_appointment_bookings;			
			$response = array('status' => 0, 'message' => '', 'response' => array('result' => array()));

			if(class_exists('BookingPressPro') && method_exists( $BookingPressPro, 'bookingpress_bpa_check_valid_connection_callback_func') && $BookingPressPro->bookingpress_bpa_check_valid_connection_callback_func()){


				$related_services = $BookingPressPro->bookingpress_get_related_services_using_service_id(11);
				

				global $bookingpress_mobile_connect,$bookingpress_global_options,$wpdb,$tbl_bookingpress_settings,$tbl_bookingpress_customize_settings;
				$bookingpress_get_settings_type = array('message_setting');
				$bookingpress_settings_key_placeholder  = ' setting_type IN(';
				$bookingpress_settings_key_placeholder .= rtrim( str_repeat( '%s,', count( $bookingpress_get_settings_type ) ), ',' );
				$bookingpress_settings_key_placeholder .= ') ';
				array_unshift( $bookingpress_get_settings_type, $bookingpress_settings_key_placeholder );
				$where_clause = call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_get_settings_type );
				$bookingpress_all_general_settings = $wpdb->get_results( "SELECT setting_name,setting_value,setting_type FROM {$tbl_bookingpress_settings} WHERE {$where_clause} ORDER BY setting_type ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_settings is table name defined globally.

				$bookingpress_bpa_customization_setting = array();
				if( !empty( $bookingpress_all_general_settings ) ){
					foreach( $bookingpress_all_general_settings as $cs_key => $cs_value ){
						$general_type = $cs_value->setting_type;
						if( empty( $bookingpress_bpa_customization_setting[ $general_type ] ) ){
							$bookingpress_bpa_customization_setting[ $general_type ] = array();
						}	
						$bookingpress_setting_value = $cs_value->setting_value;
						$bookingpress_setting_value = apply_filters( 'bookingpress_modified_get_settings',$bookingpress_setting_value,$general_type,$cs_value->setting_name);						
						$bookingpress_bpa_customization_setting[ $general_type ][ $cs_value->setting_name ] = $bookingpress_setting_value;
					}
				}

				$bookingpress_not_get_customization_setting = array('after_booking_redirection','bookingpress_thankyou_msg','delete_account_content','bookingpress_package_failed_payment_msg','bookingpress_failed_payment_msg');				
				$bookingpress_settings_key_placeholder  = ' bookingpress_setting_name NOT IN(';
				$bookingpress_settings_key_placeholder .= rtrim( str_repeat( '%s,', count( $bookingpress_not_get_customization_setting ) ), ',' );
				$bookingpress_settings_key_placeholder .= ') ';
				array_unshift( $bookingpress_not_get_customization_setting, $bookingpress_settings_key_placeholder );
				$where_clause = call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_not_get_customization_setting );

				$bookingpress_bpa_allowed_package_booking_form = false;
				if(class_exists('bookingpress_mobile_connect') && method_exists( $bookingpress_mobile_connect, 'bookingpress_bpa_allowed_package_booking_form')) {
					$bookingpress_bpa_allowed_package_booking_form = $bookingpress_mobile_connect->bookingpress_bpa_allowed_package_booking_form();							
				}
				if(!$bookingpress_bpa_allowed_package_booking_form){						
					$bookingpress_not_get_customization_setting_type = array('package_booking_form');				
					$bookingpress_settings_key_placeholder  = ' AND bookingpress_setting_type NOT IN(';
					$bookingpress_settings_key_placeholder .= rtrim( str_repeat( '%s,', count( $bookingpress_not_get_customization_setting_type ) ), ',' );
					$bookingpress_settings_key_placeholder .= ') ';
					array_unshift( $bookingpress_not_get_customization_setting_type, $bookingpress_settings_key_placeholder );
					$where_clause.= call_user_func_array( array( $wpdb, 'prepare' ), $bookingpress_not_get_customization_setting_type );
				}
				
				$bookingpress_all_customize_settings = $wpdb->get_results( "SELECT bookingpress_setting_name,bookingpress_setting_value,bookingpress_setting_type FROM {$tbl_bookingpress_customize_settings}  WHERE {$where_clause} ORDER BY bookingpress_setting_type ASC" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customize_settings is table name defined globally.
				if( !empty( $bookingpress_all_customize_settings ) ){
					foreach( $bookingpress_all_customize_settings as $cs_key => $cs_value ){							
						$customize_type = $cs_value->bookingpress_setting_type;
						if( empty( $bookingpress_bpa_customization_setting[ $customize_type ] ) ){
							$bookingpress_bpa_customization_setting[ $customize_type ] = array();
						}
						$bookingpress_setting_value = $cs_value->bookingpress_setting_value;
						$bookingpress_setting_value = apply_filters( 'bookingpress_modified_get_customize_settings',$bookingpress_setting_value,$customize_type,$cs_value->bookingpress_setting_name);

						if(gettype($bookingpress_setting_value) === 'string' && is_array(json_decode($bookingpress_setting_value,true))){
							$bookingpress_setting_value = json_decode($bookingpress_setting_value,true);
						}

						$bookingpress_bpa_customization_setting[ $customize_type ][ $cs_value->bookingpress_setting_name ] = $bookingpress_setting_value;
					}
				}

				
				$bookingpress_customize_settings['service_title'] = (isset($bookingpress_bpa_customization_setting['booking_form']['service_title'] ))?$bookingpress_bpa_customization_setting['booking_form']['service_title']:'';				
				$bookingpress_customize_settings['datetime_title'] = (isset($bookingpress_bpa_customization_setting['booking_form']['datetime_title'] ))?$bookingpress_bpa_customization_setting['booking_form']['datetime_title']:'';
				$bookingpress_customize_settings['basic_details_title'] = (isset($bookingpress_bpa_customization_setting['booking_form']['basic_details_title'] ))?$bookingpress_bpa_customization_setting['booking_form']['basic_details_title']:'';
				$bookingpress_customize_settings['summary_title'] = (isset($bookingpress_bpa_customization_setting['booking_form']['summary_title'] ))?$bookingpress_bpa_customization_setting['booking_form']['summary_title']:'';
				$bookingpress_customize_settings['hide_category_service_selection'] = (isset($bookingpress_bpa_customization_setting['booking_form']['hide_category_service_selection'] ))?$bookingpress_bpa_customization_setting['booking_form']['hide_category_service_selection']:'';																

				$bookingpress_hide_category_service_selection = stripslashes_deep($bookingpress_customize_settings['hide_category_service_selection']);
				$bookingpress_is_loaded_from_share_url = false;
				if(!empty($_GET['s_id']) && (isset($_GET['allow_modify']) && $_GET['allow_modify'] == '0' ) ){
					$bookingpress_hide_category_service_selection = 'true';
					$bookingpress_is_loaded_from_share_url = true;
				}else if(!empty($_GET['s_id']) && (isset($_GET['allow_modify']) && $_GET['allow_modify'] == '1' ) ){
					$bookingpress_hide_category_service_selection = 'false';
					$bookingpress_is_loaded_from_share_url = true;
				}
				
				if( 'true' == $bookingpress_hide_category_service_selection && empty( $selected_service ) && true == $bookingpress_is_loaded_from_share_url ){
					$bookingpress_hide_category_service_selection = 'false';
				}
	
				$bookingpress_service_tab_name  = stripslashes_deep($bookingpress_customize_settings['service_title']);
				$bookingpress_datetime_tab_name = stripslashes_deep($bookingpress_customize_settings['datetime_title']);//
				$bookingpress_basic_details_tab_name  = stripslashes_deep($bookingpress_customize_settings['basic_details_title']);
				$bookingpress_summary_tab_name = stripslashes_deep($bookingpress_customize_settings['summary_title']);

				$no_service_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_service_selected_for_the_booking', 'message_setting');
				$no_appointment_date_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_appointment_date_selected_for_the_booking', 'message_setting');
				$no_appointment_time_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_appointment_time_selected_for_the_booking', 'message_setting');
				$no_payment_method_is_selected_for_the_booking = $BookingPress->bookingpress_get_settings('no_payment_method_is_selected_for_the_booking', 'message_setting');				
				$bookingpress_date_time_step_note = $BookingPress->bookingpress_get_customize_settings('date_time_step_note','booking_form');				
				$bookingpress_date_time_step_note = !empty($bookingpress_date_time_step_note) ? stripslashes_deep($bookingpress_date_time_step_note) : '';
				$bookingpress_front_vue_data_fields['date_time_step_note'] = $bookingpress_date_time_step_note;

				$bookingpress_summary_step_note = $BookingPress->bookingpress_get_customize_settings('summary_step_note','booking_form');
				$bookingpress_summary_step_note = !empty($bookingpress_summary_step_note) ? stripslashes_deep($bookingpress_summary_step_note) : '';
				$bookingpress_front_vue_data_fields['summary_step_note'] = $bookingpress_summary_step_note;
				$bpa_move_from_service_selection_step = true;

				$bookingpress_sidebar_steps_data = array(
					'service' => array(
						'tab_name' => $bookingpress_service_tab_name,
						'tab_value' => 'service',
						'tab_icon' => 'service',
						'next_tab_name' => 'datetime',
						'next_tab_label' => '',
						'previous_tab_name' => '',
						'validate_fields' => array(
							'selected_service',
						),
						'auto_focus_tab_callback' => array(),
						'validation_msg' => array(
							'selected_service' => stripslashes_deep($no_service_selected_for_the_booking),
						),
						'is_allow_navigate' => 1,
						'is_navigate_to_next' => $bpa_move_from_service_selection_step,
						'is_display_step' => 1,
						'sorting_key' => 'service_selection',
					),
					'datetime' => array(
						'tab_name' => $bookingpress_datetime_tab_name,
						'tab_value' => 'datetime',
						'tab_icon' => 'datetime',
						'next_tab_name' => 'basic_details',
						'previous_tab_name' => 'service',
						'auto_focus_tab_callback' => array(
							'bookingpress_disable_date' => array()
						),
						'validate_fields' => array(
							'selected_date',
							'selected_start_time',
						),
						'validation_msg' => array(
							'selected_date' => stripslashes_deep($no_appointment_date_selected_for_the_booking),
							'selected_start_time' => stripslashes_deep($no_appointment_time_selected_for_the_booking),
						),
						'is_allow_navigate' => 0,
						'is_display_step' => 1,
						'is_navigate_to_next' => false,
						'sorting_key' => 'datetime_selection',
					),
					'basic_details' => array(
						'tab_name' => $bookingpress_basic_details_tab_name,
						'tab_value' => 'basic_details',
						'tab_icon' => 'basic_details',
						'auto_focus_tab_callback' => array(),
						'next_tab_name' => 'summary',
						'previous_tab_name' => 'datetime',
						'validate_fields' => array(),
						'is_allow_navigate' => 0,
						'is_display_step' => 1,
						'is_navigate_to_next' => false,
						'sorting_key' => 'basic_details_selection',
					),
					'summary' => array(
						'tab_name' => $bookingpress_summary_tab_name,
						'tab_value' => 'summary',
						'tab_icon' => 'summary',
						'next_tab_name' => 'summary',
						'auto_focus_tab_callback' => array(),
						'previous_tab_name' => 'basic_details',
						'validate_fields' => array(),
						'is_allow_navigate' => 0,
						'is_display_step' => 1,
						'is_navigate_to_next' => false,
						'sorting_key' => 'summary_selection',
					),
				);												
				if($bookingpress_hide_category_service_selection == 'true'){
					$bookingpress_sidebar_steps_data['service']['is_display_step'] = 0;
				}				
				$bookingpress_form_sequence = (isset($bookingpress_bpa_customization_setting['booking_form']['bookingpress_form_sequance'] ))?$bookingpress_bpa_customization_setting['booking_form']['bookingpress_form_sequance']:'';				
				$bookingpress_form_sequence = (!is_array($bookingpress_form_sequence) && !empty($bookingpress_form_sequence))?json_decode($bookingpress_form_sequence, TRUE):$bookingpress_form_sequence;
				if( json_last_error() != JSON_ERROR_NONE || !is_array( $bookingpress_form_sequence ) ){
					$bookingpress_form_sequence = array( 'service_selection', 'staff_selection');
				}
				
				$bookingpress_bpa_customization_setting['appointment_step_form_data']['form_sequence'] = $bookingpress_form_sequence;
				$bookingpress_bpa_customization_setting['appointment_step_form_data']['is_waiting_list'] = "false";
				
				$bookingpress_is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();

				if($bookingpress_is_staffmember_module_activated ){

					$bookingpress_staffmember_title = $BookingPress->bookingpress_get_customize_settings('staffmember_title', 'booking_form');
					$bookingpress_staff_pos = array_search('staff_selection', $bookingpress_form_sequence);
					$bookingpress_service_pos = array_search('service_selection', $bookingpress_form_sequence);						
					$bookingpress_staff_err_msg = (isset($bookingpress_bpa_customization_setting['message_setting']['no_staffmember_selected_for_the_booking'] ))?$bookingpress_bpa_customization_setting['message_setting']['no_staffmember_selected_for_the_booking']:'';
					$bookingpress_staff_err_msg = !empty($bookingpress_staff_err_msg) ? stripslashes_deep($bookingpress_staff_err_msg) : esc_html__("Please select staff member", "bookingpress-appointment-booking");
					$bookingpress_sidebar_step_data = isset($bookingpress_sidebar_steps_data) ? $bookingpress_sidebar_steps_data : array();
					$bookingpress_new_sidebar_step_data = array(
						'tab_name' => $bookingpress_staffmember_title,
						'tab_value' => 'staffmembers',
						'tab_icon' => 'staffmembers',
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
					$bookingpress_sidebar_steps_data['staffmembers'] = $bookingpress_new_sidebar_step_data;
					$is_staff_visible = true;
					$bookingpress_hide_staff_selection = $BookingPress->bookingpress_get_customize_settings('hide_staffmember_selection','booking_form');
					if( "true" == $bookingpress_hide_staff_selection ){
						$bookingpress_sidebar_steps_data['staffmembers']['is_display_step'] = 0;
						$is_staff_visible = false;
					}	
					if( false == $is_staff_visible){
						$bookingpress_sidebar_steps_data['staffmembers']['is_display_step'] = 1;
						$is_staff_visible = true;
					}						
					if( false == $is_staff_visible ){						
						if( version_compare( PHP_VERSION, '7.3.0', '<') ){
							$bookingpress_sidebar_data_keys = array_keys( $bookingpress_sidebar_steps_data );
							$first_step_key = $bookingpress_sidebar_data_keys[0];
						} else {
							$first_step_key = array_key_first( $bookingpress_sidebar_steps_data );
						}							
						if( $first_step_key != 'staffmembers' ){
							$next_step_name = $bookingpress_sidebar_steps_data['staffmembers']['next_tab_name'];
							$bookingpress_sidebar_steps_data[$first_step_key]['next_tab_name'] = $next_step_name;
						}
					}
				}
				$bookingpress_bpa_customization_setting['bookingpress_sidebar_step_data'] = $bookingpress_sidebar_steps_data;
				$bookingpress_bpa_customization_setting = $bookingpress_pro_appointment_bookings->bookingpress_extra_data_for_sidebar_steps($bookingpress_bpa_customization_setting);
				$bookingpress_bpa_customization_setting = $bookingpress_pro_appointment_bookings->bookingpress_rearrange_sidebar_steps( $bookingpress_bpa_customization_setting );

				$bookingpress_bpa_customization_setting = apply_filters('bookingpress_bpa_modify_sidebar_step_data',$bookingpress_bpa_customization_setting);
				if(isset($bookingpress_bpa_customization_setting['bookingpress_sidebar_step_data']['location'])){
					$bookingpress_bpa_customization_setting['bookingpress_sidebar_step_data']['location']['tab_icon'] = '';
				}

				//echo json_encode($bookingpress_bpa_customization_setting['bookingpress_sidebar_step_data']); die;
				
				$response = array('status' => 1, 'message' => '', 'response' => array('result' => $bookingpress_bpa_customization_setting));

			}
			
			return $response; 
		}


		function bookingpress_modify_bookingpress_form_sequance_data( $bookingpress_form_data ){
			global $BookingPress;

			$booking_form_settings = $bookingpress_form_data['booking_form_settings'];

			$booking_form_sequence = json_decode( $booking_form_settings['bookingpress_form_sequance'], true );
			$update_form_sequence_in_db = false;
			if( !is_array( $booking_form_sequence ) ){
				$booking_form_sequence = array(
					'service_selection'
				);
				$update_form_sequence_in_db = true;
			}

			$bookingpress_form_data['booking_form_settings']['bpa_sequence_item_visibility'] = array();
			$bookingpress_form_data['booking_form_settings']['bpa_sequance_pos'] = array();
			$n = 0;

			if( count( $booking_form_sequence ) == 1 ){
				global $bookingpress_pro_staff_members;
				if( $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() && !in_array( 'staff_selection', $booking_form_sequence ) ){
					array_push( $booking_form_sequence, 'staff_selection' );
					//array_push( $bookingpress_form_data['booking_form_settings']['bookingpress_form_sequance'], 'staff_selection' );

					$bookingpress_form_data['booking_form_settings']['bookingpress_form_sequance'] = json_encode( $booking_form_sequence );
				}
			}

			if( true == $update_form_sequence_in_db ){

				global $wpdb, $tbl_bookingpress_customize_settings;
				$bookingpress_setting_key = 'bookingpress_form_sequance';
				
				$bookingpress_db_fields = array(
					'bookingpress_setting_name'  => $bookingpress_setting_key,
					'bookingpress_setting_value' => json_encode( $booking_form_sequence ),
					'bookingpress_setting_type'  => 'booking_form',
				);

				$is_setting_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_setting_id) as total FROM {$tbl_bookingpress_customize_settings} WHERE bookingpress_setting_name = %s AND bookingpress_setting_type = 'booking_form'", $bookingpress_setting_key) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_customize_settings is table name defined globally. False Positive alarm
				
				if ($is_setting_exists > 0 ) {
					$wpdb->update(
						$tbl_bookingpress_customize_settings,
						$bookingpress_db_fields,
						array(
						'bookingpress_setting_name' => $bookingpress_setting_key,
						'bookingpress_setting_type' => 'booking_form',
						)
					);
				} else {
					$wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);
				}
			}

			foreach( $booking_form_sequence as $key => $sequence ){
				$sequence_visibility = false;
				if( 'service_selection' == $sequence ){
					$sequence_visibility = true;
				}
				$sequence_visibility = apply_filters( 'bookingpress_'.$sequence.'_visibility', $sequence_visibility, $sequence);
				$bookingpress_form_data['booking_form_settings']['bpa_sequence_item_visibility'][ $sequence ] = $sequence_visibility;
				if( true == $sequence_visibility ){
					$bookingpress_form_data['booking_form_settings']['bpa_sequance_pos'][$sequence] = $n + 1;
					$n++;
				} else {
					$bookingpress_form_data['booking_form_settings']['bpa_sequance_pos'][$sequence] = -1;
				}
			}

			$default_time_format = $BookingPress->bookingpress_get_settings('default_time_format','general_setting');            			
            $default_time_format = !empty($default_time_format) ? $default_time_format : 'g:i a';
			
			if ( $default_time_format == 'bookingpress-wp-inherit-time-format' ){
				$bookingpress_form_data['booking_form_settings']['bookigpress_time_format_for_booking_form'] = 'bookingpress-wp-inherit-time-format';
                $bookingpress_form_data['booking_form_settings']['bookigpress_check_inherit_time_format'] = true;
			} else {
				$bookingpress_form_data['booking_form_settings']['bookigpress_check_inherit_time_format'] = false;
			}

			return $bookingpress_form_data;
		}
		
		/**
		 * Function for add customize data to booking form
		 *
		 * @return void
		 */
		function bookingpress_add_booking_form_customize_data_func() {
			?>
			vm2.basic_details_container_data = response.data.formdata.basic_details_container_data
			vm2.booking_form_settings.bookingpress_form_sequance = JSON.parse(vm2.booking_form_settings.bookingpress_form_sequance);
			if(typeof vm2.is_location_activated == 'undefined'){
				vm2.booking_form_settings.bookingpress_form_sequance.forEach(function(currentValue, index, arr){
					if(currentValue == 'location_selection'){
						vm2.booking_form_settings.bookingpress_form_sequance.splice(index, 1);
					}
				});
			}
			if(vm2.booking_form_settings.bookingpress_form_sequance['0'] != 'undefined' && vm2.booking_form_settings.bookingpress_form_sequance['0'] != 'service_selection' ) {
				vm2.hide_service_step_disabled = true;	
			} else {
				vm2.hide_service_step_disabled = false;	
			}
			<?php
		}
		
		/**
		 * Function for execute code brfore save customize booking form data
		 *
		 * @param  mixed $booking_form_settings
		 * @return void
		 */
		function bookingpress_before_save_customize_booking_form_func($booking_form_settings){
			global $bookingpress_global_options;			
			$bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
            		$bookingpress_allow_tag = json_decode($bookingpress_global_options_data['allowed_html'], true);
			$booking_form_settings['booking_form_settings']['bookingpress_thankyou_msg'] = !empty($_POST['bookingpress_thankyou_msg']) ? wp_kses($_POST['bookingpress_thankyou_msg'],$bookingpress_allow_tag) : '';  // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
			$booking_form_settings['booking_form_settings']['bookingpress_failed_payment_msg'] = !empty($_POST['bookingpress_failed_payment_msg']) ? wp_kses($_POST['bookingpress_failed_payment_msg'],$bookingpress_allow_tag) : '';  // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.			
			$booking_form_settings['basic_details_container_data'] = array();

			if( !empty( $booking_form_settings['booking_form_settings']['bookingpress_form_sequance'] ) ){
				$booking_form_settings['booking_form_settings']['bookingpress_form_sequance'] = json_encode( $booking_form_settings['booking_form_settings']['bookingpress_form_sequance'] );	
			}
			return $booking_form_settings;
		}
		
		/**
		 * Function for modify mybooking data variables for customize fields
		 *
		 * @param  mixed $bookingpress_my_booking_field_settings
		 * @return void
		 */
		function bookingpress_get_my_booking_customize_data_filter_func( $bookingpress_my_booking_field_settings ) {
			
			$bookingpress_my_booking_field_settings['appointment_reschedule_page'] = '';
			$bookingpress_my_booking_field_settings['allow_customer_edit_profile'] = false;
			$bookingpress_my_booking_field_settings['allow_customer_delete_profile'] = false;
			$bookingpress_my_booking_field_settings['allow_customer_reschedule_apt'] = false;
			$bookingpress_my_booking_field_settings['edit_profile_page']           = '';
			$bookingpress_my_booking_field_settings['login_form_title'] = '';
			$bookingpress_my_booking_field_settings['login_form_username_field_label'] = '';
			$bookingpress_my_booking_field_settings['login_form_password_field_label'] = '';
			$bookingpress_my_booking_field_settings['login_form_username_field_placeholder'] = '';
			$bookingpress_my_booking_field_settings['login_form_password_field_placeholder'] = '';
			$bookingpress_my_booking_field_settings['login_form_remember_me_field_label'] = '';
			$bookingpress_my_booking_field_settings['login_form_button_label'] = '';
			$bookingpress_my_booking_field_settings['forgot_password_link_label'] = '';
			$bookingpress_my_booking_field_settings['login_form_error_msg_label'] = '';
			$bookingpress_my_booking_field_settings['forgot_password_form_title'] = '';
			$bookingpress_my_booking_field_settings['forgot_password_form_button_label'] = '';
			$bookingpress_my_booking_field_settings['forgot_password_form_email_label'] = '';
			$bookingpress_my_booking_field_settings['forgot_password_email_placeholder_label'] = '';
			$bookingpress_my_booking_field_settings['forgot_password_form_error_msg_label'] = '';
			$bookingpress_my_booking_field_settings['forgot_password_form_success_msg_label'] = '';
			$bookingpress_my_booking_field_settings['login_form_username_required_field_label'] = '';
			$bookingpress_my_booking_field_settings['login_form_password_required_field_label'] = '';
			$bookingpress_my_booking_field_settings['forgot_password_form_email_required_field_label'] = '';
			$bookingpress_my_booking_field_settings['forgot_password_signin_link_label'] = '';
			$bookingpress_my_booking_field_settings['current_password_label'] = '';
			$bookingpress_my_booking_field_settings['new_password_label'] = '';
			$bookingpress_my_booking_field_settings['confirm_password_label'] = '';
			$bookingpress_my_booking_field_settings['current_password_placeholder'] = '';
			$bookingpress_my_booking_field_settings['new_password_placeholder'] = '';
			$bookingpress_my_booking_field_settings['confirm_password_placeholder'] = '';
			$bookingpress_my_booking_field_settings['update_password_btn_text'] = '';
			$bookingpress_my_booking_field_settings['edit_account_title'] = '';
			$bookingpress_my_booking_field_settings['change_password_title'] = '';
			$bookingpress_my_booking_field_settings['logout_title'] = '';
			$bookingpress_my_booking_field_settings['my_profile_title'] = '';
			$bookingpress_my_booking_field_settings['update_profile_btn'] = '';
			$bookingpress_my_booking_field_settings['update_profile_success_msg'] = '';
			$bookingpress_my_booking_field_settings['update_password_success_message'] = '';
			$bookingpress_my_booking_field_settings['update_password_error_message'] = '';
			$bookingpress_my_booking_field_settings['reschedule_title'] = '';
			$bookingpress_my_booking_field_settings['reschedule_popup_title'] = '';
			$bookingpress_my_booking_field_settings['reschedule_popup_description'] = '';
			$bookingpress_my_booking_field_settings['reschedule_date_label'] = '';
			$bookingpress_my_booking_field_settings['reschedule_time_label'] = '';
			$bookingpress_my_booking_field_settings['reschedule_time_placeholder'] = '';
			$bookingpress_my_booking_field_settings['reschedule_cancel_btn_label'] = '';
			$bookingpress_my_booking_field_settings['reschedule_update_btn_label'] = '';
			$bookingpress_my_booking_field_settings['reschedule_appointment_success_msg'] = '';
			$bookingpress_my_booking_field_settings['delete_account_heading_title'] = '';
			$bookingpress_my_booking_field_settings['delete_account_desc'] = '';
			$bookingpress_my_booking_field_settings['delete_account_button_title'] = '';
			$bookingpress_my_booking_field_settings['staff_main_heading'] = '';
			$bookingpress_my_booking_field_settings['booking_guest_title'] = '';
			$bookingpress_my_booking_field_settings['booking_extra_title'] = '';
			$bookingpress_my_booking_field_settings['booking_deposit_title'] = '';
			$bookingpress_my_booking_field_settings['booking_tax_title'] = '';
			$bookingpress_my_booking_field_settings['booking_coupon_title'] = '';				
			$bookingpress_my_booking_field_settings['old_password_error_msg'] = '';
			$bookingpress_my_booking_field_settings['new_password_error_msg'] = '';
			$bookingpress_my_booking_field_settings['confirm_password_error_msg'] = '';
			$bookingpress_my_booking_field_settings['paid_amount_text'] = '';
			$bookingpress_my_booking_field_settings['refund_amount_text'] = '';
			$bookingpress_my_booking_field_settings['refund_payment_gateway_text'] = '';
			$bookingpress_my_booking_field_settings['refund_apply_text'] = '';
			$bookingpress_my_booking_field_settings['refund_cancel_text'] = '';						
			return $bookingpress_my_booking_field_settings;
		}
		
		/**
		 * Function for modify customize view file path
		 *
		 * @param  mixed $bookingpress_load_file_name
		 * @return void
		 */
		function bookingpress_modify_customize_view_file_path_func( $bookingpress_load_file_name ) {
			$bookingpress_load_file_name = BOOKINGPRESS_PRO_VIEWS_DIR . '/customize/manage_form_customize.php';
            if(!empty($_REQUEST['action']) && !empty($_REQUEST['action'] == 'form_fields')) {
                $bookingpress_load_file_name = BOOKINGPRESS_PRO_VIEWS_DIR . '/customize/manage_form_field_customize.php';
            }
			return $bookingpress_load_file_name;
		}
		
		/**
		 * Function for add customize data variables to booking form
		 *
		 * @param  mixed $booking_form_settings
		 * @return void
		 */
		function bookingpress_get_booking_form_customize_data_filter_func($booking_form_settings) {
			$booking_form_settings['booking_form_settings']['bookingpress_form_sequance'] = json_encode( array( 'service_selection', 'staff_selection' ) );
			$booking_form_settings['booking_form_settings']['hide_service_duration'] = false;
			$booking_form_settings['booking_form_settings']['hide_service_price'] = false;
			$booking_form_settings['booking_form_settings']['hide_time_slot_grouping'] = false;			
			$booking_form_settings['booking_form_settings']['bookigpress_time_format_for_booking_form'] = '2';
			$booking_form_settings['booking_form_settings']['bookigpress_check_inherit_time_format'] = '';		
			$booking_form_settings['booking_form_settings']['redirection_mode'] = 'external_redirection';									
			$booking_form_settings['booking_form_settings']['bookingpress_thankyou_msg'] = '';			
			$booking_form_settings['booking_form_settings']['bookingpress_failed_payment_msg'] = '';		
			$booking_form_settings['booking_form_settings']['hide_capacity_text'] = false;
			$booking_form_settings['booking_form_settings']['hide_capacity_text_flag'] = false;
			$booking_form_settings['booking_form_settings']['book_appointment_day_text'] = 'd';
			
			$booking_form_settings['front_label_edit_data']['card_details_text'] = '';
			$booking_form_settings['front_label_edit_data']['card_name_text'] = '';
			$booking_form_settings['front_label_edit_data']['card_number_text'] = '';
			$booking_form_settings['front_label_edit_data']['expire_month_text'] = "'";			
			$booking_form_settings['front_label_edit_data']['expire_year_text'] = '';
			$booking_form_settings['front_label_edit_data']['cvv_text'] = '';
			
			$booking_form_settings['front_label_edit_data']['complete_payment_deposit_amt_title'] = '';
			$booking_form_settings['front_label_edit_data']['make_payment_button_title'] = '';
			
			$booking_form_settings['basic_details_container_data'] = array();

			$booking_form_settings['timeslot_container_data']['slot_left_text'] = '';
			$booking_form_settings['service_container_data']['cancel_button_title'] = '';
			$booking_form_settings['service_container_data']['continue_button_title'] = '';
			$booking_form_settings['summary_container_data']['subtotal_text'] = '';
			$booking_form_settings['summary_container_data']['service_extras_label'] = '';

			return $booking_form_settings;
		}
		
		/**
		 * Function for modify customize module data variables
		 *
		 * @param  mixed $bookingpress_customize_vue_data_fields
		 * @return void
		 */
		function bookingpress_modify_customize_data_fields_func( $bookingpress_customize_vue_data_fields ) {
			global $wpdb, $tbl_bookingpress_services,$bookingpress_pro_staff_members, $tbl_bookingpress_form_fields,$bookingpress_service_extra,$bookingpress_coupons,$bookingpress_deposit_payment,$bookingpress_bring_anyone_with_you,$BookingPressPro,$BookingPress;
			$bookingpress_customize_vue_data_fields['allow_customer_reschedule_apt'] = false;
			$bookingpress_customize_vue_data_fields['allow_customer_delete_profile'] = false;
			$bookingpress_customize_vue_data_fields['allow_customer_edit_profile']   = false;
			$bookingpress_customize_vue_data_fields['edit_profile_page']             = '';
			$bookingpress_customize_vue_data_fields['required_fill_icon']            = BOOKINGPRESS_PRO_IMAGES_URL . '/required-icon-fill.svg';
			$bookingpress_customize_vue_data_fields['required_icon']                 = BOOKINGPRESS_PRO_IMAGES_URL . '/required-icon.svg';
			$bookingpress_all_pages = get_pages();
			$bookingpress_all_pages = json_decode( json_encode( $bookingpress_all_pages ), true );
			$bookingpress_customize_vue_data_fields['bookingpress_all_global_pages'] = $bookingpress_all_pages;

			$getAllServices = $wpdb->get_results( "SELECT bookingpress_service_id, bookingpress_service_name FROM {$tbl_bookingpress_services} ORDER BY bookingpress_service_name ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
			$service_array = array();

			if ( ! empty( $getAllServices ) ) {
				foreach ( $getAllServices as $service_data ) {
					$service_array[] = array(
						'id'   => $service_data->bookingpress_service_id,
						'name' => $service_data->bookingpress_service_name,
					);
				}
			}

			$bookingpress_customize_vue_data_fields['bookingpress_service_data']  = $service_array;
			$bookingpress_customize_vue_data_fields['bookingpress_service_error'] = false;

			$bookingpress_customize_vue_data_fields['is_display_preset_value_loader'] = false;
			$bookingpress_customize_vue_data_fields['preset_btn_disable']             = false;

			$bookingpress_customize_vue_data_fields['bpa_sortable_data']  = array();
			$bookingpress_customize_vue_data_fields['bpa_deleted_fields'] = array();

			$bookingpress_preset_fields = array(
				array(
					'id'   => 'countries',
					'name' => esc_html__( 'Countries', 'bookingpress-appointment-booking' ),
				),
				array(
					'id'   => 'us_states',
					'name' => esc_html__( 'U.S. States', 'bookingpress-appointment-booking' ),
				),
				array(
					'id'   => 'us_states_abbr',
					'name' => esc_html__( 'U.S. State Abbreviations', 'bookingpress-appointment-booking' ),
				),
				array(
					'id'   => 'age_group',
					'name' => esc_html__( 'Age Group', 'bookingpress-appointment-booking' ),
				),
				array(
					'id'   => 'satisfaction',
					'name' => esc_html__( 'Satisfaction', 'bookingpress-appointment-booking' ),
				),
				array(
					'id'   => 'days',
					'name' => esc_html__( 'Days', 'bookingpress-appointment-booking' ),
				),
				array(
					'id'   => 'week_days',
					'name' => esc_html__( 'Week Days', 'bookingpress-appointment-booking' ),
				),
				array(
					'id'   => 'months',
					'name' => esc_html__( 'Months', 'bookingpress-appointment-booking' ),
				),
				array(
					'id'   => 'years',
					'name' => esc_html__( 'Years', 'bookingpress-appointment-booking' ),
				),
				array(
					'id'   => 'prefix',
					'name' => esc_html__( 'Prefix', 'bookingpress-appointment-booking' ),
				),
				array(
					'id'   => 'telephone_code',
					'name' => esc_html__( 'Telephone Country Code', 'bookingpress-appointment-booking' ),
				),
			);

			/** BookingPress Customer Fields */
			$customer_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_is_customer_field = %d", 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
			$bookingpress_customer_fields = array();
			if( !empty( $customer_fields ) ){
				$c = 0;
				foreach( $customer_fields as $k => $customer_field_data ){
					
					$customer_field_data['bookingpress_field_label'] = stripslashes_deep( $customer_field_data['bookingpress_field_label']);
					$customer_field_data['bookingpress_field_placeholder'] =stripslashes_deep( $customer_field_data['bookingpress_field_placeholder']);

					$bookingpress_customer_fields[ $c ] = $customer_field_data;
					$is_droppable = true;
					$bpa_cs_field_inside_form = $wpdb->get_var( $wpdb->prepare( "SELECT count(bookingpress_form_field_id) FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_is_customer_field = %d AND bookingpress_field_meta_key = %s", 0, $customer_field_data['bookingpress_field_meta_key'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
					if( 0 < $bpa_cs_field_inside_form ){
						$is_droppable = false;
					}
					$bookingpress_customer_fields[ $c ]['is_droppable'] = $is_droppable;
					$c++;
				}
			}
			
			$bookingpress_customize_vue_data_fields['customer_fields'] = $bookingpress_customer_fields;
			/** BookingPress Customer Fields */

			$bookingpress_customize_vue_data_fields['booking_form_settings']['hide_service_duration'] = false;
			$bookingpress_customize_vue_data_fields['booking_form_settings']['hide_service_price'] = false;
			$bookingpress_customize_vue_data_fields['booking_form_settings']['hide_capacity_text'] = false;
			$bookingpress_customize_vue_data_fields['booking_form_settings']['hide_capacity_text_flag'] = false;
			$bookingpress_customize_vue_data_fields['booking_form_settings']['bookigpress_time_format_for_booking_form'] = "2";	
			$bookingpress_customize_vue_data_fields['booking_form_settings']['bookigpress_check_inherit_time_format'] = "";			
			$bookingpress_customize_vue_data_fields['booking_form_settings']['hide_time_slot_grouping'] = false;
			$bookingpress_customize_vue_data_fields['booking_form_settings']['redirection_mode'] = 'external_redirection';
			$bookingpress_customize_vue_data_fields['bookingpress_preset_fields'] = $bookingpress_preset_fields;
			$bookingpress_customize_vue_data_fields['add_redirection_msg_modal'] = false;	
			$bookingpress_customize_vue_data_fields['bookigpress_redirection_msg'] = '';	
			$bookingpress_customize_vue_data_fields['booking_form_settings']['bookingpress_thankyou_msg'] = '';			
			$bookingpress_customize_vue_data_fields['booking_form_settings']['bookingpress_failed_payment_msg'] = '';		
			$bookingpress_customize_vue_data_fields['bookingpress_redirection_action_type'] = '';			
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['login_form_title'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['login_form_username_field_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['login_form_button_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['forgot_password_link_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['login_form_error_msg_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['forgot_password_form_title'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['forgot_password_form_button_label'] = '';			
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['forgot_password_form_email_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['forgot_password_signin_link_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['forgot_password_email_placeholder_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['forgot_password_form_error_msg_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['forgot_password_form_success_msg_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['login_form_username_required_field_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['login_form_password_required_field_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['forgot_password_form_email_required_field_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['login_form_username_field_placeholder'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['login_form_password_field_placeholder'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['login_form_remember_me_field_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['current_password_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['new_password_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['confirm_password_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['current_password_placeholder'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['new_password_placeholder'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['confirm_password_placeholder'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['update_password_btn_text'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['edit_account_title'] = '';
        	$bookingpress_customize_vue_data_fields['my_booking_field_settings']['change_password_title'] = '';
        	$bookingpress_customize_vue_data_fields['my_booking_field_settings']['logout_title'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['my_profile_title'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['update_profile_btn'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['update_profile_success_msg'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['update_password_success_message'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['update_password_error_message'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['reschedule_title'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['reschedule_popup_title'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['reschedule_popup_description'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['reschedule_date_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['reschedule_time_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['reschedule_time_placeholder'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['reschedule_cancel_btn_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['reschedule_update_btn_label'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['reschedule_appointment_success_msg'] = '';

			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['delete_account_heading_title'] = '';			
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['delete_account_desc'] = '';			
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['delete_account_button_title'] = '';

			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['staff_main_heading'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['booking_guest_title'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['booking_extra_title'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['booking_deposit_title'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['booking_tax_title'] = '';
			$bookingpress_customize_vue_data_fields['my_booking_field_settings']['booking_coupon_title'] = '';
			$bookingpress_customize_vue_data_fields['is_staffmember_activated'] = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_customize_vue_data_fields['is_servie_extra_activated'] =  $bookingpress_service_extra->bookingpress_check_service_extra_module_activation();
			$bookingpress_customize_vue_data_fields['is_bring_anyone_activated'] =  $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation();
			$bookingpress_customize_vue_data_fields['is_deposit_activated'] =  $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation();
			$bookingpress_customize_vue_data_fields['is_coupon_activated'] =  $bookingpress_coupons->bookingpress_check_coupon_module_activation();
			
			$bookingpress_customize_vue_data_fields['hide_service_step_disabled'] = false;
			
			$bookingpress_customize_vue_data_fields['front_label_edit_data']['card_details_text'] = '';
			$bookingpress_customize_vue_data_fields['front_label_edit_data']['card_name_text'] = '';
			$bookingpress_customize_vue_data_fields['front_label_edit_data']['card_number_text'] = '';
			$bookingpress_customize_vue_data_fields['front_label_edit_data']['expire_month_text'] = "'";			
			$bookingpress_customize_vue_data_fields['front_label_edit_data']['expire_year_text'] = '';
			$bookingpress_customize_vue_data_fields['front_label_edit_data']['cvv_text'] = '';

			$bookingpress_customize_vue_data_fields['front_label_edit_data']['complete_payment_deposit_amt_title'] = '';
			$bookingpress_customize_vue_data_fields['front_label_edit_data']['make_payment_button_title'] = '';

			$bookingpress_customize_vue_data_fields['bookingpress_custom_field_active_tab'] = 'form';			
			$bookingpress_customize_vue_data_fields['basic_details_container_data'] = array();

			$bookingpress_customize_vue_data_fields['timeslot_container_data']['slot_left_text'] = '';	

			$bookingpress_customize_vue_data_fields['service_container_data']['cancel_button_title'] = '';
			$bookingpress_customize_vue_data_fields['service_container_data']['continue_button_title'] = '';
			$bookingpress_customize_vue_data_fields['summary_container_data']['subtotal_text'] = '';

			/* check password field exists */
			$check_password_fields = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_field_type) FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_field_type = %s", 'password' )); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm

			if( !empty($check_password_fields)){
				$bookingpress_customize_vue_data_fields['bpa_password_field_exists'] = 'bpa-form-element-col__disabled';
			} else {

				$bookingpress_customize_vue_data_fields['bpa_password_field_exists'] = '';
			}
			
			$bookingpress_customize_vue_data_fields['booking_form_settings']['bpa_sequance_pos'] = array();

			$bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings( 'default_phone_country_code', 'general_setting' );			
			if ( ! empty( $bookingpress_phone_country_option ) && $bookingpress_phone_country_option == 'auto_detect' ) {
				// Get visitors ip address
				$bookingpress_ip_address = $BookingPressPro->boookingpress_get_visitor_ip();
				try {
					$bookingpress_country_reader = new Reader( BOOKINGPRESS_PRO_LIBRARY_DIR . '/geoip/inc/GeoLite2-Country.mmdb' );
					$bookingpress_country_record = $bookingpress_country_reader->country( $bookingpress_ip_address );
					if ( ! empty( $bookingpress_country_record->country ) ) {
						$bookingpress_country_name     = $bookingpress_country_record->country->name;
						$bookingpress_country_iso_code = $bookingpress_country_record->country->isoCode;
						$bookingpress_customize_vue_data_fields['bookingpress_tel_input_props']['defaultCountry'] = $bookingpress_country_iso_code;
					}
				} catch ( Exception $e ) {
					$bookingpress_error_message = $e->getMessage();
				}
			}

			$bookingpress_customize_vue_data_fields['booking_form_sequence'] = array('service_selection', 'staff_selection');

			$is_display_form_sequence = apply_filters( 'bookingpress_modify_form_sequence_flag', false );
			$bookingpress_customize_vue_data_fields['is_display_form_sequence'] = $is_display_form_sequence;

			$bookingpress_form_sequance_arr = array(
				'service' => array(
					'title' => 'service_title',
					'next_tab' => 'datetime_title',
					'previous_tab' => '',
					'name' => '1',
					'icon' => 'dns',
					'is_visible' => '1',
					'tab_name' => 'service_selection',
				),
				'datetime' => array(
					'title' => 'datetime_title',
					'next_tab' => 'basic_details_title',
					'previous_tab' => '1',
					'name' => '2',
					'icon' => 'date_range',
					'is_visible' => '1',
					'tab_name' => 'datetime_selection'
				),
				'basic' => array(
					'title' => 'basic_details_title',
					'next_tab' => 'summary_title',
					'previous_tab' => '1',
					'name' => '3',
					'icon' => 'article',
					'is_visible' => '1',
					'tab_name' => 'basic_details_selection'
				),
				'summary' => array(
					'title' => 'summary_title',
					'next_tab' => '',
					'previous_tab' => '1',
					'name' => '4',
					'icon' => 'assignment_turned_in',
					'is_visible' => '1',
					'tab_name' => 'summary_selection'
				),
			);

			$bookingpress_form_sequance_arr = apply_filters( 'bookingpress_modify_form_sequence_arr', $bookingpress_form_sequance_arr );

			$bookingpress_customize_vue_data_fields['bookingpress_form_sequance_arr'] = apply_filters( 'bookingpress_rearrange_form_sequence_arr', $bookingpress_form_sequance_arr );
			
			$hide_category_service_selection = $BookingPress->bookingpress_get_customize_settings('hide_category_service_selection','booking_form');

			if(!empty($hide_category_service_selection ) && $hide_category_service_selection == 'true') {
				$bookingpress_customize_vue_data_fields['bookingpress_form_sequance_arr']['service']['is_visible'] = '0';
			}

			$is_set_tab = $loop = 0;
			$bookingpress_form_sequance_arr = $bookingpress_customize_vue_data_fields['bookingpress_form_sequance_arr'];
			
			foreach($bookingpress_form_sequance_arr as $form_sequance_arr_key => $form_sequance_arr_val  ) {
				if($form_sequance_arr_val['is_visible'] == '1') {					
					$is_set_next_page = $max = 0;
					if($is_set_tab == 0) {
						$bookingpress_customize_vue_data_fields['formActiveTab'] = $form_sequance_arr_val['name'];
						$is_set_tab = 1;
					}						
					if($loop == 0) {
						$bookingpress_customize_vue_data_fields['bookingpress_form_sequance_arr'][$form_sequance_arr_key]['previous_tab'] = '';
					} else {
						$bookingpress_customize_vue_data_fields['bookingpress_form_sequance_arr'][$form_sequance_arr_key]['previous_tab'] = '1';
					}						
					if(!empty($form_sequance_arr_val['tab_name'])) {
						$searching_arr_index = array_search($form_sequance_arr_key, array_keys($bookingpress_form_sequance_arr));
						while(1 > $is_set_next_page && $max < 5 ) {
							$searching_arr_index++;
							$max++;	
							$searching_arr_data = array_slice($bookingpress_form_sequance_arr,$searching_arr_index,1);							
							if(!empty($searching_arr_data)) {
								$searching_arr_data_values = array_values($searching_arr_data);
								if(!empty($searching_arr_data_values[0]) && $searching_arr_data_values[0]['is_visible'] == '1') {
									$is_set_next_page = 1;
									$bookingpress_customize_vue_data_fields['bookingpress_form_sequance_arr'][$form_sequance_arr_key]['next_tab'] = $searching_arr_data_values[0]['title'];
								};
							}
						}
					}
					$loop++;					
				}
			}
			return $bookingpress_customize_vue_data_fields;
		}

		function bookingpress_rearrange_form_sequence_arr_func( $form_sequence_arr ){
			global $BookingPress;
			$bookingpress_form_sequence = $BookingPress->bookingpress_get_customize_settings('bookingpress_form_sequance', 'booking_form');
			$bookingpress_form_sequence = json_decode($bookingpress_form_sequence, TRUE);
			if( json_last_error() != JSON_ERROR_NONE || !is_array( $bookingpress_form_sequence ) ){
				$bookingpress_form_sequence = array( 'service_selection', 'staff_selection' );
			}
			
			$k = 1;
			$keys = array();

			$external_keys = array(
				'datetime_selection',
				'basic_details_selection',
				'summary_selection'
			);

			$external_keys = apply_filters( 'bookingpress_modify_form_sequence_for_rearrange', $external_keys );

			foreach( array_merge( $bookingpress_form_sequence, $external_keys ) as $sequence ){
				$keys[ $sequence ] = $k;
				$k++;
			}

			//$temp_form_sequence = $form_sequence_arr;
			uasort( $form_sequence_arr, function( $a, $b) use ($keys) {
				if( !empty( $a['tab_name'] ) && !empty( $b['tab_name']) && !empty( $keys[ $a['tab_name'] ] ) && !empty( $keys[ $b['tab_name'] ] )  ){
					//return $a[$bookingpress_form_sequance_arr];
					return $keys[ $a['tab_name'] ] > $keys[ $b['tab_name'] ] ? 1 : -1;
				} else {
					return -1;
				}
			} );
			
			$total_seq = count( $form_sequence_arr );
			$n = 0;
			foreach( $form_sequence_arr as $fk => $form_sequence ){
				$next= next( $form_sequence );

				if( $n == 0 ){
					$form_sequence_arr[ $fk ]['previous_tab'] = '';	
				}

				if( $total_seq < $n ){
					$form_sequence_arr[ $fk ]['next_tab'] = $next;
				}
				$n++;
			}

			return $form_sequence_arr;
		}
		
		/**
		 * Function for modify field data before fields prepare
		 *
		 * @param  mixed $db_fields_data
		 * @param  mixed $for_customer
		 * @return void
		 */
		function bookingpress_modify_field_data_before_prepare_func( $db_fields_data, $for_customer = false ) {

			$parent_field_keys = array();

			foreach ( $db_fields_data as $k => $val ) {

				/** Remove Customer Default Fields */

				$bookingpress_form_field_name = (isset($val['bookingpress_form_field_name']))?$val['bookingpress_form_field_name']:'';
				$bookingpress_field_options = (isset($val['bookingpress_field_options']))?$val['bookingpress_field_options']:'';
				if(($bookingpress_form_field_name == 'Repeater' || $bookingpress_form_field_name == '2 Col' || $bookingpress_form_field_name == '3 Col' || $bookingpress_form_field_name == '4 Col') && !empty($bookingpress_field_options)){
					$bookingpress_field_options_arr = json_decode($bookingpress_field_options,true);
					if(!empty($bookingpress_field_options_arr)){						
						$parent_field = (isset($bookingpress_field_options_arr['parent_field']))?$bookingpress_field_options_arr['parent_field']:'';
						if(!empty($parent_field)){
							unset( $db_fields_data[$k] );
							continue;		
						}
					}
				}
				if( !empty( $val['bookingpress_is_customer_field'] ) && false == $for_customer ){
					unset( $db_fields_data[$k] );
					continue;
				} else {					
					
					if ( preg_match( '/^(\d+)\.(\d+)$/', $val['bookingpress_field_position'], $matches ) ) {
						
						if ( isset( $matches[1] ) && '' !== $matches[1] ) {
							$key = intval( $matches[2] );

							$inner_field_options  = json_decode( $val['bookingpress_field_options'], true );
							
							$parent_field_id      = !empty( $inner_field_options['parent_field'] ) ? $inner_field_options['parent_field'] : '';
							
							$parent_key           = array_search( $parent_field_id, array_column( $db_fields_data, 'bookingpress_form_field_id' ) );
							$x = 0;
							foreach( $db_fields_data as $ink => $inv ){
								if( $parent_key == $x ){
									$parent_key = $ink;
									break;
								}
								$x++;
							}
							$parent_field_data    = $db_fields_data[ $parent_key ];
							$parent_field_options = json_decode( $parent_field_data['bookingpress_field_options'], true );

							if( !isset( $parent_field_options['inner_fields'] ) ){
								$parent_field_options['inner_fields'] = array();
							}
							
							foreach ( $parent_field_options['inner_fields'] as $inK => $inv ) {
								if ( $inv['id'] == 'inner_field_' . $val['bookingpress_form_field_id'] ) {

									if ( 'text' == $val['bookingpress_field_type'] ) {
										$inner_field_type = 'Text';
									} elseif ( 'textarea' == $val['bookingpress_field_type'] ) {
										$inner_field_type = 'Textarea';
									} elseif ( 'email' == $val['bookingpress_field_type'] ) {
										$inner_field_type = 'Email';
									} elseif ( 'dropdown' == $val['bookingpress_field_type'] ) {
										$inner_field_type = 'Dropdown';
									} elseif ( 'checkbox' == $val['bookingpress_field_type'] ) {
										$inner_field_type = 'Checkbox';
									} elseif ( 'radio' == $val['bookingpress_field_type'] ) {
										$inner_field_type = 'Radio';
									} elseif ( 'date' == $val['bookingpress_field_type'] ) {
										$inner_field_type = 'Date';
									} elseif ( 'file' == $val['bookingpress_field_type'] ) {
										$inner_field_type = 'File';
									} elseif ( 'phone' == $val['bookingpress_field_type'] ) {
										$inner_field_type = 'Phone';
									} elseif( 'terms_and_conditions' == $val['bookingpress_field_type']){
										$inner_field_type = 'terms_and_conditions';
									} elseif( 'password' == $val['bookingpress_field_type']){
										$inner_field_type = 'Password';
									} elseif ( '2_col' == $val['bookingpress_field_type'] || '3_col' == $val['bookingpress_field_type'] || '4_col' == $val['bookingpress_field_type'] ) {
										$inner_field_type = $val['bookingpress_field_type'];
									} else {
										$inner_field_type = 'Custom';
									}

									$parent_field_options['inner_fields'][ $inK ] = array(
										'error_message'  => stripslashes_deep($val['bookingpress_field_error_message']),
										'field_position' => $inK,
										'id'             => $val['bookingpress_form_field_id'],
										'is_edit'        => false,
										'is_hide'        => false,
										'is_default'     => $val['bookingpress_field_is_default'],
										'is_required'    => $val['bookingpress_field_required'],
										'is_delete'      => false,
										'placeholder'    => stripslashes_deep($val['bookingpress_field_placeholder']),
										'field_options'  => json_decode( $val['bookingpress_field_options'], true ),
										'field_name'     => $val['bookingpress_form_field_name'],
										'field_values'   => json_decode( $val['bookingpress_field_values'], true ),
										'field_type'     => $inner_field_type,
										'label'          => stripslashes_deep($val['bookingpress_field_label']),
										'is_blank'       => false,
										'meta_key'       => $val['bookingpress_field_meta_key'],
										'css_class'      => $val['bookingpress_field_css_class'],
									);
								}
							}
							$db_fields_data[ $parent_key ]['bookingpress_field_options'] = wp_json_encode( $parent_field_options );
							// unset( $db_fields_data[$k] );
							$parent_field_keys[] = $k;
						}
					}

					$bookingpress_field_options = json_decode( $val['bookingpress_field_options'], true );
					if ( isset( $bookingpress_field_options['used_for_user_information'] ) ) {
						$bookingpress_field_options['used_for_user_information'] = ( $bookingpress_field_options['used_for_user_information'] == 'true' ) ? true : false;
					} elseif ( isset( $val['bookingpress_field_is_default'] ) && $val['bookingpress_field_is_default'] == '1' ) {
						$bookingpress_field_options['used_for_user_information'] = true;
					} else {
						$bookingpress_field_options['used_for_user_information'] = false;
					}
					$db_fields_data[ $k ]['bookingpress_field_options'] = wp_json_encode( $bookingpress_field_options );
				}
			}
			
			if ( ! empty( $parent_field_keys ) ) {
				foreach ( $parent_field_keys as $parent_db_key ) {
					unset( $db_fields_data[ $parent_db_key ] );
				}
			}
			array_values( $db_fields_data );

			return $db_fields_data;
		}
		
		/**
		 * Function for mody form fields data
		 *
		 * @param  mixed $form_fields_data
		 * @param  mixed $db_field_options
		 * @return void
		 */
		function bookingpress_modify_form_field_data( $form_fields_data, $db_field_options ) {
			global $wpdb, $tbl_bookingpress_form_fields;
			if ( 'text' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Text';
			} elseif ( 'textarea' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Textarea';
			} elseif ( 'email' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Email';
			} elseif ( 'dropdown' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Dropdown';
			} elseif ( 'checkbox' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Checkbox';
			} elseif ( 'radio' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Radio';
			} elseif ( 'date' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Date';
			} elseif ( 'file' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'File';
			} elseif ( 'phone' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Phone';
			} elseif( 'terms_and_conditions' == $db_field_options['bookingpress_field_type'] ){
				$form_fields_data['field_type'] = 'terms_and_conditions';
			} elseif( 'password' == $db_field_options['bookingpress_field_type']){
				$form_fields_data['field_type'] = 'Password';
			} elseif ( '2_col' == $db_field_options['bookingpress_field_type'] || '3_col' == $db_field_options['bookingpress_field_type'] || '4_col' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = $db_field_options['bookingpress_field_type'];
			} elseif( 'repeater' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Repeater';
			} else {
				$form_fields_data['field_type'] = 'Custom';
			}
			if( $form_fields_data['field_type']  != 'Password' ){

			if ( empty( $db_field_options['bookingpress_field_meta_key'] ) ) {
				$form_fields_data['meta_key'] = strtolower( sanitize_text_field( str_replace( ' ', '_', $db_field_options['bookingpress_field_type'] ) ) ) . '_' . wp_generate_password( 6, false );
			} else {
				$form_fields_data['meta_key'] = $db_field_options['bookingpress_field_meta_key'];
				}
			}

			$form_fields_data['css_class'] = ! empty( $db_field_options['bookingpress_field_css_class'] ) ? $db_field_options['bookingpress_field_css_class'] : '';

			$form_fields_data['is_default']           = isset( $db_field_options['bookingpress_field_is_default'] ) ? (int) $db_field_options['bookingpress_field_is_default'] : 0;
			$form_fields_data['is_edit_values']       = false;
			$form_fields_data['enable_preset_fields'] = false;
			$form_fields_data['preset_field_choice']  = '';


			$form_field_values = array();

			if ( in_array( $db_field_options['bookingpress_field_type'], array( 'checkbox', 'radio', 'dropdown' ) ) ) {
				if ( empty( $db_field_options['bookingpress_field_values'] ) ) {
					$form_field_values = array(
						array(
							'value' => 'Option 1',
							'label' => 'Option 1',
						),
						array(
							'value' => 'Option 2',
							'label' => 'Option 2',
						),
					);
				} else {
					$db_options = json_decode( $db_field_options['bookingpress_field_values'], true );
					if ( empty( $db_options ) ) {
						$form_field_values = array(
							array(
								'value' => 'Option 1',
								'label' => 'Option 1',
							),
							array(
								'value' => 'Option 2',
								'label' => 'Option 2',
							),
						);
					} else {
						$form_field_values = $db_options;
					}
				}
			}

			$form_fields_data['field_values'] = $form_field_values;

			$db_field_options = $db_field_options['bookingpress_field_options'];


			if ( ! empty( $db_field_options ) ) {
				if ( ! is_array( $db_field_options ) ) {
					$form_fields_data['field_options'] = json_decode( $db_field_options, true );
					
					
					if ( empty( $form_fields_data['field_options'] ) ) {
						$form_fields_data['field_options'] = array(
							'layout'         => '1col',
							'inner_class'    => '1col',
							'separate_value' => false,
						);
					}
					
					if( '1' == $form_fields_data['is_default'] ){
						$form_fields_data['field_options']['is_customer_field'] = 'false';
					}
				} else {
					if( '1' == $form_fields_data['is_default'] ){
						$db_field_options['is_customer_field'] = 'false';
					}
					$form_fields_data['field_options'] = $db_field_options;
				}
			} else {
				$form_fields_data['field_options'] = array(
					'layout'         => '1col',
					'inner_class'    => '1col',
					'separate_value' => false,
				);
				if( '1' == $form_fields_data['is_default'] ){
					$form_fields_data['field_options']['is_customer_field'] = 'false';
				}
			}

			if ( ! isset( $form_fields_data['field_options']['visibility'] ) ) {
				$form_fields_data['field_options']['visibility'] = 'always';
			}

			if ( ! isset( $form_fields_data['field_options']['selected_services'] ) ) {
				$form_fields_data['field_options']['selected_services'] = array();
			}

			if ( ! isset( $form_fields_data['field_options']['separate_value'] ) ) {
				$form_fields_data['field_options']['separate_value'] = false;
			}
			if ( $form_fields_data['field_type'] == 'Text' || $form_fields_data['field_type'] == 'Textarea' ) {
				if ( ! isset( $form_fields_data['field_options']['minimum'] ) ) {
					$form_fields_data['field_options']['minimum'] = '';
				}

				if ( ! isset( $form_fields_data['field_options']['maximum'] ) ) {
					$form_fields_data['field_options']['maximum'] = '';
				}
			}

			if ( $form_fields_data['field_type'] == 'Date' ) {
				if ( ! isset( $form_fields_data['field_options']['enable_timepicker'] ) ) {
					$form_fields_data['field_options']['enable_timepicker'] = false;
				} else {
					if ( 'true' === $form_fields_data['field_options']['enable_timepicker'] || true === $form_fields_data['field_options']['enable_timepicker'] ) {
						$form_fields_data['field_options']['enable_timepicker'] = true;
					} else {
						$form_fields_data['field_options']['enable_timepicker'] = false;
					}
				}
			}
			if ( $form_fields_data['field_type'] == 'Phone' ) {
				if ( ! isset( $form_fields_data['field_options']['set_custom_placeholder'] ) ) {
					$form_fields_data['field_options']['set_custom_placeholder'] = false;
				} else {
					if ( 'true' === $form_fields_data['field_options']['set_custom_placeholder'] || true === $form_fields_data['field_options']['set_custom_placeholder'] ) {
						$form_fields_data['field_options']['set_custom_placeholder'] = true;
					} else {
						$form_fields_data['field_options']['set_custom_placeholder'] = false;
					}
				}
			}

			if ( $form_fields_data['field_type'] == 'File' ) {
				if ( ! isset( $form_fields_data['field_options']['allowed_file_ext'] ) ) {
					$form_fields_data['field_options']['allowed_file_ext'] = '';
				}

				if ( ! isset( $form_fields_data['field_options']['invalid_field_message'] ) ) {
					$form_fields_data['field_options']['invalid_field_message'] = esc_html__( 'Invalid file selected', 'bookingpress-appointment-booking' );
				}

				if ( ! isset( $form_fields_data['field_options']['max_file_size'] ) ) {
					$form_fields_data['field_options']['max_file_size'] = 2;
				}

				if( ! isset( $form_fields_data['field_options']['attach_with_email'] ) ){
					$form_fields_data['field_options']['attach_with_email'] = false;
				}

				if( ! isset( $form_fields_data['field_options']['browse_button_label'] ) ){
					$form_fields_data['field_options']['browse_button_label'] = esc_html__( 'Browse', 'bookingpress-appointment-booking' );
				}
			}

			

			if ( ! empty( $form_fields_data['field_options']['inner_fields'] ) ) {

				foreach ( $form_fields_data['field_options']['inner_fields'] as $k => $v ) {
					if ( isset( $v['is_edit'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['is_edit'] = ( $v['is_edit'] === 'true' ) ? true : false;
					}
					if ( isset( $v['is_required'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['is_required'] = ( $v['is_required'] == true ) ? true : false;
					}
					if ( isset( $v['is_edit_values'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['is_edit_values'] = ( $v['is_edit_values'] === 'true' ) ? true : false;
					}

					if(isset($v['field_options']['inner_fields']) && !empty($v['field_options']['inner_fields'])){
						foreach($v['field_options']['inner_fields'] as $kinner => $vinner){
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['inner_fields'][$kinner]['is_edit_values'] =  false;
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['inner_fields'][$kinner]['is_edit'] =  false;
							if ( isset( $vinner['is_required'] ) ) {
								$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['inner_fields'][$kinner]['is_required'] = ( $vinner['is_required'] == 'true' ) ? true : false;
							}
						}
					}

					if ( ! isset( $v['innerIndex'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['innerIndex'] = (int) $k;
					} else {
						$form_fields_data['field_options']['inner_fields'][ $k ]['innerIndex'] = (int) $v['innerIndex'];
					}

					

					if ( isset( $v['field_position'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['field_position'] = (int) $v['field_position'];
					}

					

					if ( isset( $v['id'] ) ) {
						
						if(!empty($v['id'])){
							$v['id'] = str_replace('inner_field_','',$v['id']);
						}
						$form_fields_data['field_options']['inner_fields'][ $k ]['id'] = (int) $v['id'];

					}
					

					if ( empty( $v['meta_key'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['meta_key'] = strtolower( str_replace( ' ', '_', $v['field_type'] ) ) . '_' . wp_generate_password( 6, false );
					}

					if ( empty( $v['css_class'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['css_class'] = '';
					}

					if ( $v['field_type'] == 'Text' || $v['field_type'] == 'Textarea' ) {
						if ( empty( $v['field_options']['minimum'] ) ) {
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['minimum'] = '';
						}

						if ( empty( $v['field_options']['maximum'] ) ) {
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['maximum'] = '';
						}
					}

					if ( $v['field_type'] == 'Date' ) {
						if ( empty( $v['field_options']['enable_timepicker'] ) ) {
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['enable_timepicker'] = false;
						} elseif ( 'true' === $v['field_options']['enable_timepicker'] || true === $v['field_options']['enable_timepicker'] ) {
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['enable_timepicker'] = true;
						} else {
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['enable_timepicker'] = false;
						}
					}

					if ( $v['field_type'] == 'File' ) {
						if ( empty( $v['field_options']['allowed_file_ext'] ) ) {
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['allowed_file_ext'] = '';
						}

						if ( empty( $v['field_options']['max_file_size'] ) ) {
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['max_file_size'] = 2;
						}

						if ( empty( $v['field_options']['invalid_field_message'] ) ) {
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['invalid_field_message'] = esc_html__( 'Invalid file selected', 'bookingpress-appointment-booking' );
						}

						if( empty( $v['field_options']['attach_with_email'] ) ){
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['attach_with_email'] = false;
						}

						if( empty( $v['field_options']['browse_button_label'] ) ){
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['browse_button_label'] = esc_html__( 'Browse', 'bookingpress-appointment-booking' );
						}

					}
					
					if($v['field_type'] == 'Phone' ) {
						if ( empty( $v['field_options']['set_custom_placeholder'] ) ) {
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['set_custom_placeholder'] = false;
						} elseif ( 'true' === $v['field_options']['set_custom_placeholder'] || true === $v['field_options']['set_custom_placeholder'] ) {
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['set_custom_placeholder'] = true;
						} else {
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['set_custom_placeholder'] = false;
						}
					}

					if( 'false' == $v['is_blank'] ){

						$inner_field_id = str_replace( 'inner_field_', '', $v['id'] );
						$is_field_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_form_field_id) as field_exists FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_id = %d", $inner_field_id) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name.

						if( 1 > $is_field_exists ){
							$form_fields_data['field_options']['inner_fields'][ $k ]['is_blank'] = 'true';
							$form_fields_data['field_options']['inner_fields'][ $k ]['id'] = $inner_field_id;
							$form_fields_data['field_options']['inner_fields'][ $k ]['field_options'] = array(
								'is_customer_field' => false
							);
						}
					}
				}
				$inner_fields = $form_fields_data['field_options']['inner_fields'];
				

				$new_inner_fields = $this->bookingpress_sort_inner_fields_before_save( $inner_fields );

				$form_fields_data['field_options']['inner_fields'] = $new_inner_fields;
			}

			$form_fields_data['default_value'] = '';

			$form_fields_data['is_delete'] = false;

			

			return $form_fields_data;
		}
		
		/**
		 * Function for sort inner fields before fields save
		 *
		 * @param  mixed $inner_fields
		 * @return void
		 */
		function bookingpress_sort_inner_fields_before_save( $inner_fields, $use_pos = false ) {
			if ( empty( $inner_fields ) ) {
				return $inner_fields;
			}

			$sort_field = ( true == $use_pos) ? 'field_position' : 'innerIndex';
			
			if ( version_compare( PHP_VERSION_ID, '7.0.0', '>=' ) ) {
				usort(
					$inner_fields,
					function ( $a, $b ) use ( $sort_field ) {
						if ( (isset($a[$sort_field]) && isset($b[$sort_field])) && '' !== $a[$sort_field] && '' !== $b[$sort_field] ) {
							return $a[$sort_field] <=> $b[$sort_field];
						}
					}
				);
			} else {
				usort(
					$inner_fields,
					function ( $a, $b ) use ( $sort_field ) {
						if ((isset($a[$sort_field]) && isset($b[$sort_field])) && '' !== $a[$sort_field] && '' !== $b[$sort_field] ) {
							return $a[$sort_field] < $b[$sort_field];
						}
					}
				);
			}

			return $inner_fields;
		}

		
		/**
		 * Function for insert inner fields details
		 *
		 * @param  mixed $bookingpress_db_fields
		 * @param  mixed $bookingpress_field_setting_key
		 * @param  mixed $parent_field_id
		 * @return void
		 */
		function bookingpress_insert_inner_fields_func( $bookingpress_db_fields, $bookingpress_field_setting_key, $parent_field_id ) {
			global $wpdb,$tbl_bookingpress_form_fields, $BookingPress,$bookingpress_is_repeter_fields;


			$bookingpress_parent_field_type = (isset($bookingpress_db_fields['bookingpress_field_type']))?$bookingpress_db_fields['bookingpress_field_type']:'';
			if ( empty( $_POST['field_settings'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
				return;
			}

			$field_settings = array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_POST['field_settings'] ); // phpcs:ignore

			$bookingpress_has_repeter_inner_fields = false;
			if ( '2_col' == $bookingpress_db_fields['bookingpress_field_type'] || '3_col' == $bookingpress_db_fields['bookingpress_field_type'] || '4_col' == $bookingpress_db_fields['bookingpress_field_type'] || 'repeater' == $bookingpress_db_fields['bookingpress_field_type'] ) {
				
				$posted_field    = $field_settings[ $bookingpress_field_setting_key ];
				$field_position  = $bookingpress_db_fields['bookingpress_field_position'];
				$field_options   = json_decode( $bookingpress_db_fields['bookingpress_field_options'], true );
				$inner_fields    = $field_options['inner_fields'];
				$db_inner_fields = array();
				$x               = 0.1;

				/* $posted_field['field_options']['inner_fields'] = $this->bookingpress_sort_inner_fields_before_save( $posted_field['field_options']['inner_fields'], true );
				if('repeater' == $bookingpress_db_fields['bookingpress_field_type']){
					$field_type = (isset($inner_fields[0]['field_type']))?$inner_fields[0]['field_type']:'';
					if($field_type == '2_col' || $field_type == '3_col' || $field_type == '4_col'){						

						//$bookingpress_has_repeter_inner_fields = true;
						$column_sub_fields = (isset($inner_fields[0]['field_options']['inner_fields']) && !empty($inner_fields[0]['field_options']['inner_fields']))?$inner_fields[0]['field_options']['inner_fields']:array();

						$bookingpress_col_db_fields = array(
							'bookingpress_form_field_name' => $inner_fields[0]['field_name'],
							'bookingpress_field_required'  => ( $inner_fields[0]['is_required'] == 'false' ) ? 0 : 1,
							'bookingpress_field_label'     => $inner_fields[0]['label'],
							'bookingpress_field_placeholder' => $inner_fields[0]['placeholder'],
							'bookingpress_field_error_message' => $inner_fields[0]['error_message'],
							'bookingpress_field_is_hide'   => ( $inner_fields[0]['is_hide'] == 'false' ) ? 0 : 1,
							'bookingpress_field_position'  => $inner_fields[0]['field_position'],
							'bookingpress_field_type'      => $field_type,						
						);		
						
						

						$get_parent_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $parent_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
						$parent_field_opts_temp     = json_decode( $get_parent_field_data->bookingpress_field_options, true );

						$inner_fields[0]['field_options']['parent_field'] = $parent_field_id;			
						$bpa_existing_inner_col_field_id = $inner_fields[0]['id'];
						$bpa_existing_inner_col_field_id = str_replace('inner_field_','',$bpa_existing_inner_col_field_id);

						$col_field_exist = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_form_field_id) as total FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_id = %d", $bpa_existing_inner_col_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm

						if ( $col_field_exist > 0 ) {
							
							$wpdb->update( $tbl_bookingpress_form_fields, $bookingpress_col_db_fields, array( 'bookingpress_form_field_id' => $bpa_existing_inner_col_field_id ) );

						} else {

							$wpdb->insert( $tbl_bookingpress_form_fields, $bookingpress_col_db_fields );
							$bpa_existing_inner_col_field_id = $wpdb->insert_id;

							if($bpa_existing_inner_col_field_id){

								$get_parent_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $parent_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
								$parent_field_opts     = json_decode( $get_parent_field_data->bookingpress_field_options, true );
	
								$parent_field_opts['inner_fields'][0]['id'] = 'inner_field_' . $bpa_existing_inner_col_field_id;
								$parent_field_opts['inner_fields'][0]['org_id'] = 'inner_field_' . $bpa_existing_inner_col_field_id;
								$parent_field_opts['inner_fields'][0]['field_options']['parent_field'] = $parent_field_id;


	
								$wpdb->update(
									$tbl_bookingpress_form_fields,
									array(
										'bookingpress_field_options' => wp_json_encode( $parent_field_opts ),
									),
									array(
										'bookingpress_form_field_id' => $parent_field_id,
									)
								);	
	
							}
						}						
						if(!empty($column_sub_fields)){
							
							//$parent_field_id = $bpa_existing_inner_col_field_id;							
							$column_sub_fields = $this->bookingpress_sort_inner_fields_before_save( $column_sub_fields, true );
							
							$db_inner_fields = array();
							$x = 0.1;
							foreach ( $column_sub_fields as $ik => $iv ) {

								
								if ( $iv['is_blank'] === 'false' || $iv['is_blank'] === false ) {

									$inner_field_id = str_replace( 'inner_field_', '', $iv['id'] );
									//if ( $column_sub_fields['field_options']['inner_fields'][ $ik ]['id'] == $inner_field_id ) {
										$posted_field['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $ik ]['field_position'] = (float) $field_position + $x;
										$posted_field['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $ik ]['temp_id']        = $inner_field_id;
										$db_inner_fields[] = $posted_field['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $ik ];



									//}
									$x = $x + 0.1;

								}
							}								
							if ( ! empty( $db_inner_fields ) ) {
								foreach ( $db_inner_fields as $k => $inner_field ) {

									$bookingpress_db_fields = array(
										'bookingpress_form_field_name' => $inner_field['field_name'],
										'bookingpress_field_required'  => ( $inner_field['is_required'] == 'false' ) ? 0 : 1,
										'bookingpress_field_label'     => $inner_field['label'],
										'bookingpress_field_placeholder' => $inner_field['placeholder'],
										'bookingpress_field_error_message' => $inner_field['error_message'],
										'bookingpress_field_is_hide'   => ( $inner_field['is_hide'] == 'false' ) ? 0 : 1,
										'bookingpress_field_position'  => $inner_field['field_position'],
									);									
			
									$inner_field['field_options']['parent_field'] = $bpa_existing_inner_col_field_id;
			
									$bpa_existing_inner_field_id = $inner_field['id'];
									$bpa_existing_inner_field_id = str_replace( 'inner_field_', '', $bpa_existing_inner_field_id );

									$temporary_id                = $inner_field['temp_id'];
									unset( $inner_field['temp_id'] );

									

									$bookingpress_db_fields = apply_filters( 'bookingpress_modify_form_field_data_before_save', $bookingpress_db_fields, $inner_field );
			
									//$bookingpress_db_fields
									$field_exist = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_form_field_id) as total FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_id = %d", $bpa_existing_inner_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm

									if ( $field_exist > 0 ) {
										
										
										$finalpost_data = (isset($posted_field['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $k ]))?$posted_field['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $k ]:'';

										if(isset($finalpost_data['is_required'])){
											$bookingpress_db_fields['bookingpress_field_required'] = ($finalpost_data['is_required'] == 'true')?1:0;
										}																				

										$bookingpress_is_repeter_fields[] = $bpa_existing_inner_field_id;
										$wpdb->update( $tbl_bookingpress_form_fields, $bookingpress_db_fields, array( 'bookingpress_form_field_id' => $bpa_existing_inner_field_id ) );

										$get_parent_field_data_upd = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $parent_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
										$parent_field_opts_upd     = json_decode( $get_parent_field_data_upd->bookingpress_field_options, true );	
																			
										if(isset($parent_field_opts_upd['inner_fields'][0]['field_options']['inner_fields'][$k]['field_options']) && !empty($bookingpress_db_fields['bookingpress_field_options'])){
											
											$parent_field_opts_upd['inner_fields'][0]['field_options']['inner_fields'][$k]['field_options'] = json_decode($bookingpress_db_fields['bookingpress_field_options'],true);											
											if(isset($finalpost_data['is_required'])){
												$parent_field_opts_upd['inner_fields'][0]['field_options']['inner_fields'][$k]['is_required'] = $finalpost_data['is_required'];												
											}	
											$wpdb->update(
												$tbl_bookingpress_form_fields,
												array(
													'bookingpress_field_options' => wp_json_encode( $parent_field_opts_upd ),
												),
												array(
													'bookingpress_form_field_id' => $parent_field_id,
												)
											);																						

										}
									} else {
			
										$wpdb->insert( $tbl_bookingpress_form_fields, $bookingpress_db_fields );
										$bpa_existing_inner_field_id = $wpdb->insert_id;

										$get_parent_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $parent_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm

										$parent_field_opts     = json_decode( $get_parent_field_data->bookingpress_field_options, true );
										$parent_field_opts['inner_fields'][0]['field_options']['inner_fields'][$k]['id']  = 'inner_field_' . $bpa_existing_inner_field_id;

										$wpdb->update(
											$tbl_bookingpress_form_fields,
											array(
												'bookingpress_field_options' => wp_json_encode( $parent_field_opts ),
											),
											array(
												'bookingpress_form_field_id' => $parent_field_id,
											)
										);

										

										/*
										foreach ( $parent_field_opts['inner_fields'][0]['field_options']['inner_fields'] as $ik => $iv ) {
											if ( $iv['id'] == 'inner_field_' . $temporary_id ) {
												$parent_field_opts['inner_fields'][0]['field_options']['inner_fields'][ $ik ]['id'] = 'inner_field_' . $bpa_existing_inner_field_id;
												$wpdb->update(
													$tbl_bookingpress_form_fields,
													array(
														'bookingpress_field_options' => wp_json_encode( $parent_field_opts ),
													),
													array(
														'bookingpress_form_field_id' => $parent_field_id,
													)
												);
											}
										}
										*//*

										$wpdb->update(
											$tbl_bookingpress_form_fields,
											array(
												'bookingpress_field_options' => wp_json_encode( $parent_field_opts['inner_fields'][0]['field_options'] ),
											),
											array(
												'bookingpress_form_field_id' => $bpa_existing_inner_col_field_id,
											)
										);
																			


									}
								}

							}

						}

						/* return '';
						exit; *//*
					}

					/*
					print_r();
					print_r($posted_field);
					die;
					*//*

				} */

				//if(!$bookingpress_has_repeter_inner_fields){


					
					//print_r($posted_field['field_options']['inner_fields']);

					foreach ( $inner_fields as $ik => $iv ) {
					
						
						if ( $iv['is_blank'] === 'false' || $iv['is_blank'] === false && ('2_col' != $iv['field_type'] && '3_col' != $iv['field_type'] && '4_col' != $iv['field_type']) ) {
							$org_inner_field_id = $iv['id'];
							$inner_field_id = str_replace( 'inner_field_', '', $iv['id'] );
							
							//echo $posted_field['field_options']['inner_fields'][ $ik ]['id'].' '.$inner_field_id;
							

							foreach ( $posted_field['field_options']['inner_fields'] as $ik2 => $iv2 ) {
								if($iv2['id'] == $inner_field_id || $iv2['id'] == $org_inner_field_id){

									$posted_field['field_options']['inner_fields'][ $ik2 ]['field_position'] = (float) $field_position + $x;
									$posted_field['field_options']['inner_fields'][ $ik2 ]['temp_id']        = $inner_field_id;
									$db_inner_fields[] = $posted_field['field_options']['inner_fields'][ $ik2 ];

								}
							}							

							/*
							if ( $posted_field['field_options']['inner_fields'][ $ik ]['id'] == $inner_field_id || $posted_field['field_options']['inner_fields'][ $ik ]['id'] == $org_inner_field_id ) {

								$posted_field['field_options']['inner_fields'][ $ik ]['field_position'] = (float) $field_position + $x;
								$posted_field['field_options']['inner_fields'][ $ik ]['temp_id']        = $inner_field_id;
								$db_inner_fields[] = $posted_field['field_options']['inner_fields'][ $ik ];

							}
							*/
							$x = $x + 0.1;
						} else if( '2_col' == $iv['field_type'] || '3_col' == $iv['field_type'] || '4_col' == $iv['field_type'] ){


							$column_sub_fields = !empty( $iv['field_options']['inner_fields'] ) ? $iv['field_options']['inner_fields'] : array();
							$columns_inner_data = $posted_field['field_options']['inner_fields'][ $ik ];
							if(!isset($columns_inner_data['inner_fields'])){
								if(isset($columns_inner_data['field_options']['inner_fields'])){
									$columns_inner_data['inner_fields'] = $columns_inner_data['field_options']['inner_fields'];
									unset($columns_inner_data['field_options']['inner_fields']);
								}	
							}
							$bookingpress_col_db_fields = array(
								'bookingpress_form_field_name' => $iv['field_name'],
								'bookingpress_field_required'  => ( $iv['is_required'] == 'false' ) ? 0 : 1,
								'bookingpress_field_label'     => $iv['label'],
								'bookingpress_field_placeholder' => $iv['placeholder'],
								'bookingpress_field_error_message' => $iv['error_message'],
								'bookingpress_field_is_hide'   => ( $iv['is_hide'] == 'false' ) ? 0 : 1,
								'bookingpress_field_position'  => $iv['field_position'],
								'bookingpress_field_type'      => $iv['field_type'],
								'bookingpress_field_options'   => json_encode($columns_inner_data,true),
							);
							$get_parent_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $parent_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
							$parent_field_opts_temp     = json_decode( $get_parent_field_data->bookingpress_field_options, true );							
							$iv['field_options']['parent_field'] = $parent_field_id;
							$bpa_existing_inner_col_field_id = $iv['id'];
							$bpa_existing_inner_col_field_id = str_replace('inner_field_','',$bpa_existing_inner_col_field_id);							
							$col_field_exist = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_form_field_id) as total FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_id = %d", $bpa_existing_inner_col_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
							if ( $col_field_exist > 0 ) {

								$columns_inner_data['field_options']['parent_field'] = $parent_field_id;
								$columns_inner_data['parent_field'] = $parent_field_id;
								$bookingpress_col_db_fields['bookingpress_field_options'] = json_encode($columns_inner_data,true);
								$wpdb->update( $tbl_bookingpress_form_fields, $bookingpress_col_db_fields, array( 'bookingpress_form_field_id' => $bpa_existing_inner_col_field_id ) );
								$get_parent_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $bpa_existing_inner_col_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
								$parent_col_field_opts     = json_decode( $get_parent_field_data->bookingpress_field_options,true);
								$parent_col_field_opts['parent_field'] = $parent_field_id;

								if(isset($parent_field_opts['inner_fields']) && !empty($parent_field_opts['inner_fields'])){									
									foreach($parent_field_opts['inner_fields'] as $p_col_key=>$p_inner_fields){
										if ( $p_inner_fields['is_blank'] === 'false' || $p_inner_fields['is_blank'] === false ) {																						
											$sub_field_id = (isset($parent_field_opts['inner_fields'][$p_col_key]['id']))?$parent_field_opts['inner_fields'][$p_col_key]['id']:'';
											if(!empty($sub_field_id)){
												$sub_field_id = str_replace('inner_field_','',$sub_field_id);
												$parent_field_opts['inner_fields'][$p_col_key]['id'] = 'inner_field_'.$sub_field_id;
											}
										}											
									}
									$wpdb->update(
										$tbl_bookingpress_form_fields,
										array('bookingpress_field_options' => wp_json_encode( $parent_col_field_opts )),
										array('bookingpress_form_field_id' => $bpa_existing_inner_col_field_id)
									);
								}

							} else {

								$columns_inner_data['field_options']['parent_field'] = $parent_field_id;
								$columns_inner_data['parent_field'] = $parent_field_id;
								$bookingpress_col_db_fields['bookingpress_field_options'] = json_encode($columns_inner_data,true);
								$wpdb->insert( $tbl_bookingpress_form_fields, $bookingpress_col_db_fields );
								$bpa_existing_inner_col_field_id = $wpdb->insert_id;
								if( $bpa_existing_inner_col_field_id ){

									$get_parent_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $parent_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm									
									$parent_field_opts     = json_decode( $get_parent_field_data->bookingpress_field_options, true );																		
									$parent_field_opts['inner_fields'][$ik]['id'] = 'inner_field_' . $bpa_existing_inner_col_field_id;
									$parent_field_opts['inner_fields'][$ik]['org_id'] = 'inner_field_' . $bpa_existing_inner_col_field_id;																		
									$wpdb->update(
										$tbl_bookingpress_form_fields,
										array(
											'bookingpress_field_options' => wp_json_encode( $parent_field_opts ),
										),
										array(
											'bookingpress_form_field_id' => $parent_field_id,
										)
									);

								}								
							}
							if(!empty($column_sub_fields)){							
															
								$column_sub_fields = $this->bookingpress_sort_inner_fields_before_save( $column_sub_fields, true );								
								$db_inner_fields = array();
								$x = 0.1;
								foreach ( $column_sub_fields as $sik => $siv ) {

									if ( $siv['is_blank'] === 'false' || $siv['is_blank'] === false ) {
										
										$inner_field_id = str_replace( 'inner_field_', '', $siv['id'] );										
										$posted_field['field_options']['inner_fields'][$ik]['field_options']['inner_fields'][ $sik ]['field_position'] = (float) $field_position + $x;
										$posted_field['field_options']['inner_fields'][$ik]['field_options']['inner_fields'][ $sik ]['temp_id']        = $inner_field_id;
										$db_inner_fields[] = $posted_field['field_options']['inner_fields'][$ik]['field_options']['inner_fields'][ $sik ];
										$x = $x + 0.1;
	
									}
								}								
								if ( ! empty( $db_inner_fields ) ) {
									foreach ( $db_inner_fields as $k => $inner_field ) {
	
										$bookingpress_db_fields = array(
											'bookingpress_form_field_name' => $inner_field['field_name'],
											'bookingpress_field_required'  => ( $inner_field['is_required'] == 'false' ) ? 0 : 1,
											'bookingpress_field_label'     => $inner_field['label'],
											'bookingpress_field_placeholder' => $inner_field['placeholder'],
											'bookingpress_field_error_message' => $inner_field['error_message'],
											'bookingpress_field_is_hide'   => ( $inner_field['is_hide'] == 'false' ) ? 0 : 1,
											'bookingpress_field_position'  => $inner_field['field_position'],
										);									
				
										$inner_field['field_options']['parent_field'] = $bpa_existing_inner_col_field_id;
				
										$bpa_existing_inner_field_id = $inner_field['id'];
										$bpa_existing_inner_field_id = str_replace( 'inner_field_', '', $bpa_existing_inner_field_id );
										$temporary_id                = $inner_field['temp_id'];
										unset( $inner_field['temp_id'] );
										$bookingpress_db_fields = apply_filters( 'bookingpress_modify_form_field_data_before_save', $bookingpress_db_fields, $inner_field );

										$field_exist = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_form_field_id) as total FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_id = %d", $bpa_existing_inner_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
	
										if ( $field_exist > 0 ) {
											
											
											$finalpost_data = (isset($posted_field['field_options']['inner_fields'][$ik]['field_options']['inner_fields'][ $k ]))?$posted_field['field_options']['inner_fields'][$ik]['field_options']['inner_fields'][ $k ]:'';
	
											if(isset($finalpost_data['is_required'])){
												$bookingpress_db_fields['bookingpress_field_required'] = ($finalpost_data['is_required'] == 'true')?1:0;
											}																				
	
											$bookingpress_is_repeter_fields[] = $bpa_existing_inner_field_id;
											$wpdb->update( $tbl_bookingpress_form_fields, $bookingpress_db_fields, array( 'bookingpress_form_field_id' => $bpa_existing_inner_field_id ) );
	
											$get_parent_field_data_upd = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $parent_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
											$parent_field_opts_upd     = json_decode( $get_parent_field_data_upd->bookingpress_field_options, true );	
																				
											if(isset($parent_field_opts_upd['inner_fields'][$ik]['field_options']['inner_fields'][$k]['field_options']) && !empty($bookingpress_db_fields['bookingpress_field_options'])){
												
												$parent_field_opts_upd['inner_fields'][$ik]['field_options']['inner_fields'][$k]['field_options'] = json_decode($bookingpress_db_fields['bookingpress_field_options'],true);											
												if(isset($finalpost_data['is_required'])){
													$parent_field_opts_upd['inner_fields'][$ik]['field_options']['inner_fields'][$k]['is_required'] = $finalpost_data['is_required'];																									
												}	
												if(!isset($parent_field_opts_upd['inner_fields'][$ik]['field_options']['inner_fields'][$k]['innerIndex'])){
													$parent_field_opts_upd['inner_fields'][$ik]['field_options']['inner_fields'][$k]['innerIndex'] = $k;
												}												
												$wpdb->update(
													$tbl_bookingpress_form_fields,
													array(
														'bookingpress_field_options' => wp_json_encode( $parent_field_opts_upd ),
													),
													array(
														'bookingpress_form_field_id' => $parent_field_id,
													)
												);																						
	
											}
										} else {
				
											$wpdb->insert( $tbl_bookingpress_form_fields, $bookingpress_db_fields );
											$bpa_existing_inner_field_id = $wpdb->insert_id;
											$get_parent_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $parent_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
											$parent_field_opts     = json_decode( $get_parent_field_data->bookingpress_field_options, true );
											$parent_field_opts['inner_fields'][$ik]['field_options']['inner_fields'][$k]['id']  = 'inner_field_' . $bpa_existing_inner_field_id;
											if(!isset($parent_field_opts['inner_fields'][$ik]['field_options']['inner_fields'][$k]['innerIndex'])){
												$parent_field_opts['inner_fields'][$ik]['field_options']['inner_fields'][$k]['innerIndex'] = $k;
											}											
											$wpdb->update(
												$tbl_bookingpress_form_fields,
												array(
													'bookingpress_field_options' => wp_json_encode( $parent_field_opts ),
												),
												array(
													'bookingpress_form_field_id' => $parent_field_id,
												)
											);											
											$parent_field_opts['inner_fields'][$ik]['field_options']['parent_field'] = $parent_field_id;
											$insert_col_data = $parent_field_opts['inner_fields'][$ik]['field_options'];
											$wpdb->update(
												$tbl_bookingpress_form_fields,
												array(
													'bookingpress_field_options' => wp_json_encode( $parent_field_opts['inner_fields'][$ik]['field_options'] ),
												),
												array(
													'bookingpress_form_field_id' => $bpa_existing_inner_col_field_id,
												)
											);

											/*
											foreach ( $parent_field_opts['inner_fields'][$ik]['field_options']['inner_fields'] as $ik => $iv ) {
												if ( $iv['id'] == 'inner_field_' . $temporary_id ) {
													$parent_field_opts['inner_fields'][$ik]['field_options']['inner_fields'][ $ik ]['id'] = 'inner_field_' . $bpa_existing_inner_field_id;
													$wpdb->update(
														$tbl_bookingpress_form_fields,
														array(
															'bookingpress_field_options' => wp_json_encode( $parent_field_opts ),
														),
														array(
															'bookingpress_form_field_id' => $parent_field_id,
														)
													);
												}
											}
											$wpdb->update(
												$tbl_bookingpress_form_fields,
												array(
													'bookingpress_field_options' => wp_json_encode( $parent_field_opts['inner_fields'][$ik]['field_options'] ),
												),
												array(
													'bookingpress_form_field_id' => $bpa_existing_inner_col_field_id,
												)
											);
											*/
																				
	
	
										}
									}
								}

								$db_inner_fields = array();
							}							
						}
					}
						
					

					if (!empty($db_inner_fields) && ('2_col' != $iv['field_type'] && '3_col' != $iv['field_type'] && '4_col' != $iv['field_type'])) {

						

						foreach ( $db_inner_fields as $k => $inner_field ) {
							$bookingpress_db_fields = array(
								'bookingpress_form_field_name' => $inner_field['field_name'],
								'bookingpress_field_required'  => ( $inner_field['is_required'] == 'false' ) ? 0 : 1,
								'bookingpress_field_label'     => $inner_field['label'],
								'bookingpress_field_placeholder' => $inner_field['placeholder'],
								'bookingpress_field_error_message' => $inner_field['error_message'],
								'bookingpress_field_is_hide'   => ( $inner_field['is_hide'] == 'false' ) ? 0 : 1,
								'bookingpress_field_position'  => ($inner_field['field_position'] == 0)?0.1:$inner_field['field_position'],
							);							
							$inner_field['field_options']['parent_field'] = $parent_field_id;
							$bpa_existing_inner_field_id = $inner_field['id'];
							$temporary_id                = $inner_field['temp_id'];
							unset( $inner_field['temp_id'] );
							$bookingpress_db_fields = apply_filters( 'bookingpress_modify_form_field_data_before_save', $bookingpress_db_fields, $inner_field );
							
							$field_exist = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_form_field_id) as total FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_id = %d", $bpa_existing_inner_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm							
							if ( $field_exist > 0 ) {
								
								$bookingpress_is_repeter_fields[] = $bpa_existing_inner_field_id;
								
								$wpdb->update( $tbl_bookingpress_form_fields, $bookingpress_db_fields, array( 'bookingpress_form_field_id' => $bpa_existing_inner_field_id ) );
								
								$get_parent_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_type,bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $parent_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
								if('repeater' == $get_parent_field_data->bookingpress_field_type){

									$parent_field_opts     = json_decode( $get_parent_field_data->bookingpress_field_options, true );									
									if(!empty($get_parent_field_data)){

										$new_inner_sub_field = array();
										/*
										foreach ( $parent_field_opts['inner_fields'] as $ik => $iv ) {
											if ( $iv['is_blank'] === 'false' || $iv['is_blank'] === false ) {
												$new_inner_sub_field[] = $iv;
											}
										}
										$parent_field_opts['inner_fields'] = $new_inner_sub_field;
										$wpdb->update(
											$tbl_bookingpress_form_fields,
											array(
												'bookingpress_field_options' => wp_json_encode( $parent_field_opts ),
											),
											array(
												'bookingpress_form_field_id' => $parent_field_id,
											)
										);
										*/

									}									
								}								
								
							} else {
	
								$wpdb->insert( $tbl_bookingpress_form_fields, $bookingpress_db_fields );
								$bpa_existing_inner_field_id = $wpdb->insert_id;
	
								$get_parent_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_type,bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $parent_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
								$parent_field_opts     = json_decode( $get_parent_field_data->bookingpress_field_options, true );
								if('repeater' == $get_parent_field_data->bookingpress_field_type){									
									/*
									$new_inner_sub_field = array();
									foreach ( $parent_field_opts['inner_fields'] as $ik => $iv ) {
										if ( $iv['is_blank'] === 'false' || $iv['is_blank'] === false ) {
											$new_inner_sub_field[] = $iv;
										}
									}
									$parent_field_opts['inner_fields'][$ik] = $new_inner_sub_field;									
									*/
								}

								//if('repeater' != $get_parent_field_data->bookingpress_field_type){
									
									foreach ( $parent_field_opts['inner_fields'] as $ik => $iv ) {
										if ( $iv['id'] == 'inner_field_' . $temporary_id ) {
											$parent_field_opts['inner_fields'][ $ik ]['id'] = 'inner_field_' . $bpa_existing_inner_field_id;
											$wpdb->update(
												$tbl_bookingpress_form_fields,
												array(
													'bookingpress_field_options' => wp_json_encode( $parent_field_opts ),
												),
												array(
													'bookingpress_form_field_id' => $parent_field_id,
												)
											);
										}
									}

								//}

							}
						}

						
					}

					
					
					if('repeater' == $bookingpress_parent_field_type){

						$get_parent_field_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $parent_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
						$parent_field_opts     = json_decode( $get_parent_field_data->bookingpress_field_options, true );
						if(isset($parent_field_opts['inner_fields']) && !empty($parent_field_opts['inner_fields'])){
							foreach($parent_field_opts['inner_fields'] as $key=>$repeater_inner_fields){
								
								if(isset($repeater_inner_fields['is_blank']) && !empty($repeater_inner_fields['is_blank']) && $repeater_inner_fields['is_blank'] === 'true' || $repeater_inner_fields['is_blank'] === true){
									unset($parent_field_opts['inner_fields'][$key]);
								}

								/*
								if($repeater_inner_fields['field_type'] == '2_col' || $repeater_inner_fields['field_type'] == '3_col' || $repeater_inner_fields['field_type'] == '4_col'){
									if(isset($repeater_inner_fields['id'])){

										$bpa_existing_inner_col_field_id = str_replace( 'inner_field_', '', $repeater_inner_fields['id'] );
										if(!empty($bpa_existing_inner_col_field_id)){
											$get_parent_field_data_col = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $bpa_existing_inner_col_field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
											if(!empty($get_parent_field_data_col)){
												$parent_col_field_opts     = json_decode( $get_parent_field_data_col->bookingpress_field_options, true );
												if(!isset($parent_col_field_opts['field_options']['parent_field'])){
													$parent_col_field_opts['field_options']['parent_field'] = $parent_field_id;
													$wpdb->update(
														$tbl_bookingpress_form_fields,
														array(
															'bookingpress_field_options' => wp_json_encode( $parent_col_field_opts ),
														),
														array(
															'bookingpress_form_field_id' => $bpa_existing_inner_col_field_id,
														)
													);													
												}
											}
										}

									}
								}
								*/
							}
							$wpdb->update(
								$tbl_bookingpress_form_fields,
								array(
									'bookingpress_field_options' => wp_json_encode( $parent_field_opts ),
								),
								array(
									'bookingpress_form_field_id' => $parent_field_id,
								)
							);

						}

					}
					
				//}

	

				//die;


			}
		}
		
		/**
		 * Function for modify form field data before save
		 *
		 * @param  mixed $form_fields_data
		 * @param  mixed $posted_field_settings
		 * @return void
		 */
		function bookingpress_modify_form_field_data_before_save_func( $form_fields_data, $posted_field_settings ) {

			$form_fields_data['bookingpress_field_is_default'] = ! empty( $posted_field_settings['is_default'] ) ? intval( $posted_field_settings['is_default'] ) : 0;
			$form_fields_data['bookingpress_field_type']       = ! empty( $posted_field_settings['field_type'] ) ? sanitize_text_field( strtolower( $posted_field_settings['field_type'] ) ) : 'custom';
			$form_fields_data['bookingpress_field_meta_key']   = ! empty( $posted_field_settings['meta_key'] ) ? sanitize_text_field( $posted_field_settings['meta_key'] ) : $form_fields_data['bookingpress_field_type'] . '_' . wp_generate_password( 6, false );
			$form_fields_data['bookingpress_field_css_class']  = ! empty( $posted_field_settings['css_class'] ) ? sanitize_text_field( $posted_field_settings['css_class'] ) : '';
			
			$field_options = ! empty( $posted_field_settings['field_options'] ) ? $posted_field_settings['field_options'] : array();

			if(!empty($posted_field_settings['field_name']) && $posted_field_settings['field_name']  == 'phone_number'){
				$bookingpress_set_custom_placeholder  = ! empty( $field_options['set_custom_placeholder'] ) ? $field_options['set_custom_placeholder']  : '';			
				$field_options['set_custom_placeholder'] = $bookingpress_set_custom_placeholder;
			}			

			$field_visibility = !empty($field_options['visibility']) ? $field_options['visibility'] : '';
			if ( 'services' != $field_visibility ) {
				$field_options['selected_services'] = array();
			}
			
			if ( isset( $field_options['separate_value'] ) && 'true' == $field_options['separate_value'] ) {
				$field_options['separate_value'] = true;
			} else {
				$field_options['separate_value'] = false;
			}

			if( empty( $posted_field_settings['field_values'] ) ){
				$posted_field_settings['field_values']= array();
			}


			if( !$field_options['separate_value'] ){
				$temp_field_values_data = array();
				$temp_field_values = $posted_field_settings['field_values'];
				$temp_field_values_data = $posted_field_settings['field_values'];
				foreach( $temp_field_values as $tfv_key => $tfv_val ){
					if( $tfv_val['label'] != $tfv_val['value'] ){
						$temp_field_values_data[$tfv_key]['value'] = $tfv_val['label'];
					}
				}
				$posted_field_settings['field_values'] = $temp_field_values_data;
			}
			$form_fields_data['bookingpress_field_values'] = ! empty( $posted_field_settings['field_values'] ) ? wp_json_encode( $posted_field_settings['field_values'] ) : wp_json_encode( array() );
			
			if ( ! empty( $field_options['inner_fields'] ) ) {

				if( 'repeater' == $form_fields_data['bookingpress_field_type'] ){
					
					foreach ( $field_options['inner_fields'] as $k => $in ) {
						if ( $in['is_blank'] === 'false' || $in['is_blank'] === false ) {

							$finner_key = array(
								'is_blank' => 'false',
								'id'       => 'inner_field_' . $in['id'],
								'innerIndex' => $in['field_position']
							);

							$field_options['inner_fields'][ $k ] = $finner_key;

						}
					}

					$field_options['inner_fields'] = $this->bookingpress_sort_inner_fields_before_save( $field_options['inner_fields'] );

					
				} else {
					foreach ( $field_options['inner_fields'] as $k => $in ) {
						if ( $in['is_blank'] === 'false' || $in['is_blank'] === false ) {
							$field_options['inner_fields'][ $k ] = array(
								'is_blank' => 'false',
								'id'       => 'inner_field_' . $in['id'],
							);
						}
					}
				}
			}

			

			if( isset( $field_options['attach_with_email'] ) && ( 'true' === $field_options['attach_with_email'] || true === $field_options['attach_with_email'] || 1 === $field_options['attach_with_email'] ) ){
				$field_options['attach_with_email'] = true;
			} else {
				$field_options['attach_with_email'] = false;
			}

			$form_fields_data['bookingpress_field_options'] = wp_json_encode( $field_options );

			return $form_fields_data;
		}
		
		/**
		 * Function for add customize form settings data to save request before data save
		 *
		 * @return void
		 */
		function bookingpress_before_save_customize_form_settings_func() {
			?>	const vm = this;
			    postData.bookingpress_thankyou_msg = vm.booking_form_settings.bookingpress_thankyou_msg;
				postData.bookingpress_failed_payment_msg = vm.booking_form_settings.bookingpress_failed_payment_msg;
				postData.basic_details_container_data = vm2.basic_details_container_data

			<?php
		}
		
		/**
		 * Function for before save field settings method
		 *
		 * @return void
		 */
		function bookingpress_before_save_field_settings_method_func() {
			?>
			if( app.bpa_sortable_data.length > 0 ){
				app.bpa_sortable_data.forEach( (element, index) => {
					if( element.el != null && "undefined" != typeof Sortable.get && Sortable.get( element.el ) != null ){
						Sortable.get( element.el ).destroy();
					}
				});
				app.bpa_sortable_data = [];
				app.$forceUpdate();
			}
			postData.deletedFields = app.bpa_deleted_fields;
			<?php
		}
		
		/**
		 * Function for dynamic add customize vue methods
		 *
		 * @return void
		 */
		function bookingpress_pro_customize_dynamic_vue_methods() {
			global $bookingpress_notification_duration;
			?>
			check_repeater_inside_default_fields(field_settings_data,isCol = false){				
				var is_default_field = true;				
				if(field_settings_data.field_type == "Repeater"){					
					if(typeof field_settings_data.field_options.inner_fields != "undefined"){
						if(field_settings_data.field_options.inner_fields.length > 0){
							for( let s in field_settings_data.field_options.inner_fields ){
								if(typeof field_settings_data.field_options.inner_fields[s].field_type != "undefined"){
									if(field_settings_data.field_options.inner_fields[s].field_type == '2_col' || field_settings_data.field_options.inner_fields[s].field_type == '3_col' || field_settings_data.field_options.inner_fields[s].field_type == '4_col'){
										if(typeof field_settings_data.field_options.inner_fields[s].field_options.inner_fields != "undefined"){
											for( let ss in field_settings_data.field_options.inner_fields[s].field_options.inner_fields){
												if(typeof field_settings_data.field_options.inner_fields[s].field_options.inner_fields[ss].is_default != "undefined"){
													if(field_settings_data.field_options.inner_fields[s].field_options.inner_fields[ss].is_default == 1 || field_settings_data.field_options.inner_fields[s].field_options.inner_fields[ss].is_default == '1'){
														is_default_field = false;
													}													
												}
											}
										}
									}else{
										if(typeof field_settings_data.field_options.inner_fields[s].is_default != "undefined"){
											if(field_settings_data.field_options.inner_fields[s].is_default == 1 || field_settings_data.field_options.inner_fields[s].is_default == '1'){
												is_default_field = false;
											}									
										}
									}
								}
							}
						}
					}
				}				
				return is_default_field;
			},			
			loadTinyMCE(){
				window.onload = function(){
					(function() {
						tinyMCE.init({ selector:'#bookigpress_redirection_msg' });
					})();
				}
			},
			change_sublevel_required(val,fskey,ifskey){
				alert(val);
			},
			saveFieldRepeterSettings( index, parent_key, hassub = 'yes' ){
				let field_settings = app.field_settings_fields[index];
				if( typeof parent_key != 'undefined' ){					
					if(hassub != 'yes'){						
						field_settings = app.field_settings_fields[parent_key].field_options.inner_fields[hassub].field_options.inner_fields[index];
					}else{
						field_settings = app.field_settings_fields[parent_key].field_options.inner_fields[index];
					}
					this.$forceUpdate();
				}
				let field_visibility = field_settings.field_options.visibility;
				if( 'services' == field_visibility ){
					let selected_services = field_settings.field_options.selected_services;
					if( selected_services.length < 1 ){
						app.bookingpress_service_error = true;
						return false;
					} else {
						app.bookingpress_service_error = false;
					}
				} else {
					app.bookingpress_service_error = false;
				}
				if( typeof parent_key != 'undefined' ){	
					if(hassub != 'yes'){												
						app.field_settings_fields[parent_key].field_options.inner_fields[hassub].field_options.inner_fields[index].is_edit = false;						
					}else{					
						app.field_settings_fields[parent_key].field_options.inner_fields[index].is_edit = false;
					}
				} else {
					app.field_settings_fields[index].is_edit = false;
				}
				this.$forceUpdate();
			},			
			deleteRepeatInnerColField( index, parent_key, ifrskey ){

				let deleted_field_id = app.field_settings_fields[ parent_key ].field_options.inner_fields[ifrskey].field_options.inner_fields[index].id;				
				let del_elm = document.querySelector(`.bpa-inner-field-container[data-field-id="${deleted_field_id}"]`);
				let is_customer_field = del_elm.getAttribute('data-is-customer-field') || false;

				if( 'false' != is_customer_field && false != is_customer_field ){
					let meta_key = del_elm.getAttribute('data-metakey');
					let source_item = document.querySelector(`.bpa-cs__item[data-customer-field-meta=${meta_key}]`);
					source_item.classList.remove('bpa-restricted');
				}
				if(deleted_field_id){
					deleted_field_id = deleted_field_id.replace('inner_field_','');
				}
				app.bpa_deleted_fields.push( deleted_field_id );				
				app.field_settings_fields[ parent_key ].field_options.inner_fields[ifrskey].field_options.inner_fields[index] = {
					'is_blank': true,
					'id': BPASortable.bpa_generate_field_id(),
					'field_options':{
						'is_customer_fields':false
					}
				};

				let blank_inner_fields = 0;
				let total_inner_fields = app.field_settings_fields[ parent_key ].field_options.inner_fields[ifrskey].field_options.inner_fields.length;

				app.field_settings_fields[ parent_key ].field_options.inner_fields[ifrskey].field_options.inner_fields.forEach( (element) => {
					if( element.is_blank == true || element.is_blank == 'true' ){
						blank_inner_fields++;
					}
				});

				/** remove blank row when delete all fields */
				if( blank_inner_fields == total_inner_fields ){

					document.querySelector( '.bpa-field-container[data-id="' + parent_key + '"]' ).replaceWith( '' );
					let deleted_parent_field_id = app.field_settings_fields[ parent_key ].id;
					app.bpa_deleted_fields.push( deleted_parent_field_id );
					delete app.field_settings_fields[ parent_key ];

					let updated_fields = app.field_settings_fields;
					let finalFields = [];
					let all_fields = document.getElementsByClassName('bpa-field-container');
					if( all_fields.length > 0 ){
						let i = 1;
						all_fields.forEach( (element, index) => {
							element.setAttribute( 'data-id', i );
							i++;
						});

						let x = 0;
						let f = 1;
						updated_fields.forEach( (element,index) => {
							finalFields[x] = element;
							finalFields[x].field_position = f;
							x++;
							f++;
						});
						app.field_settings_fields = finalFields;
					}

				} else {
					app.bpa_sortable_data.forEach( (element, index) => {
						/*
						if( element.el != null && Sortable.get( element.el ) != null ){
							Sortable.get( element.el ).destroy();
						}
						*/
					});
					
					app.bpa_sortable_data = [];

					app.$forceUpdate();
					setTimeout( function(){
						new BPASortable();
					},1000);
				}
				this.$forceUpdate();
				},	
			deleteRepeatInnerField( index, parent_key ){

				let deleted_field_id = app.field_settings_fields[ parent_key ].field_options.inner_fields[index].id;				
				let del_elm = document.querySelector(`.bpa-inner-field-container[data-field-id="${deleted_field_id}"]`);
				let is_customer_field = del_elm.getAttribute('data-is-customer-field') || false;

				if( 'false' != is_customer_field && false != is_customer_field ){
					let meta_key = del_elm.getAttribute('data-metakey');
					let source_item = document.querySelector(`.bpa-cs__item[data-customer-field-meta=${meta_key}]`);
					source_item.classList.remove('bpa-restricted');
				}
				if(deleted_field_id){
					if(parseInt(deleted_field_id) != 0){						
						//deleted_field_id = deleted_field_id.replace('inner_field_','');
					}					
				}
				app.bpa_deleted_fields.push( deleted_field_id );
				app.field_settings_fields[ parent_key ].field_options.inner_fields[index] = {
					'is_blank': true,
					'id': BPASortable.bpa_generate_field_id(),
					'field_options':{
						'is_customer_fields':false
					}
				};

				let blank_inner_fields = 0;
				let total_inner_fields = app.field_settings_fields[ parent_key ].field_options.inner_fields.length;

				app.field_settings_fields[ parent_key ].field_options.inner_fields.forEach( (element) => {
					if( element.is_blank == true || element.is_blank == 'true' ){
						blank_inner_fields++;
					}
				});

				/** remove blank row when delete all fields */
				if( blank_inner_fields == total_inner_fields ){
					document.querySelector( '.bpa-field-container[data-id="' + parent_key + '"]' ).replaceWith( '' );
					let deleted_parent_field_id = app.field_settings_fields[ parent_key ].id;
					app.bpa_deleted_fields.push( deleted_parent_field_id );
					delete app.field_settings_fields[ parent_key ];

					let updated_fields = app.field_settings_fields;
					let finalFields = [];
					let all_fields = document.getElementsByClassName('bpa-field-container');
					if( all_fields.length > 0 ){
						let i = 1;
						all_fields.forEach( (element, index) => {
							element.setAttribute( 'data-id', i );
							i++;
						});

						let x = 0;
						let f = 1;
						updated_fields.forEach( (element,index) => {
							finalFields[x] = element;
							finalFields[x].field_position = f;
							x++;
							f++;
						});
						app.field_settings_fields = finalFields;
					}
				} else {
					app.bpa_sortable_data.forEach( (element, index) => {
						/*
						if( element.el != null && Sortable.get( element.el ) != null ){
							Sortable.get( element.el ).destroy();
						}
						*/
					});
					
					app.bpa_sortable_data = [];

					app.$forceUpdate();
					setTimeout( function(){
						new BPASortable();
					},1000);
				}
				this.$forceUpdate();
			},			
			deleteInnerField( index, parent_key ){

				let deleted_field_id = app.field_settings_fields[ parent_key ].field_options.inner_fields[index].id;				

				let del_elm = document.querySelector(`.bpa-inner-field-container[data-field-id="${deleted_field_id}"]`);
				let is_customer_field = del_elm.getAttribute('data-is-customer-field') || false;
				
				if( 'false' != is_customer_field ){
					let meta_key = del_elm.getAttribute('data-metakey');
					let source_item = document.querySelector(`.bpa-cs__item[data-customer-field-meta=${meta_key}]`);
					source_item.classList.remove('bpa-restricted');
				}

				app.bpa_deleted_fields.push( deleted_field_id );
				app.field_settings_fields[ parent_key ].field_options.inner_fields[index] = {
					'is_blank': true,
					'id': BPASortable.bpa_generate_field_id(),
					'field_options':{
						'is_customer_fields':false
					}
				};
				
				let blank_inner_fields = 0;
				let total_inner_fields = app.field_settings_fields[ parent_key ].field_options.inner_fields.length;

				app.field_settings_fields[ parent_key ].field_options.inner_fields.forEach( (element) => {
					if( element.is_blank == true || element.is_blank == 'true' ){
						blank_inner_fields++;
					}
				});

				/** remove blank row when delete all fields */
				if( blank_inner_fields == total_inner_fields ){
					document.querySelector( '.bpa-field-container[data-id="' + parent_key + '"]' ).replaceWith( '' );
					let deleted_parent_field_id = app.field_settings_fields[ parent_key ].id;
					app.bpa_deleted_fields.push( deleted_parent_field_id );
					delete app.field_settings_fields[ parent_key ];

					let updated_fields = app.field_settings_fields;
					let finalFields = [];
					let all_fields = document.getElementsByClassName('bpa-field-container');
					if( all_fields.length > 0 ){
						let i = 1;
						all_fields.forEach( (element, index) => {
							element.setAttribute( 'data-id', i );
							i++;
						});

						let x = 0;
						let f = 1;
						updated_fields.forEach( (element,index) => {
							finalFields[x] = element;
							finalFields[x].field_position = f;
							x++;
							f++;
						});
						app.field_settings_fields = finalFields;
					}
				} else {
					app.bpa_sortable_data.forEach( (element, index) => {
						if( element.el != null && Sortable.get( element.el ) != null ){
							Sortable.get( element.el ).destroy();
						}
					});
					
					app.bpa_sortable_data = [];

					app.$forceUpdate();
					setTimeout( function(){
						new BPASortable();
					},1000);
				}

				this.$forceUpdate();
				
			},
			deleteField( index ){
				
				let del_elm = document.querySelector( '.bpa-field-container[data-id="' + index + '"]');
				let bpa_field_type = del_elm.getAttribute('data-type');

				if( bpa_field_type == 'Password' ){
					this.bpa_password_field_exists = '';
					if( null != document.querySelector( '#bpa-input-fields .bpa-cs__item[data-type="password"].bpa-form-element-col__disabled') ){
						document.querySelector( '#bpa-input-fields .bpa-cs__item[data-type="password"]').classList.remove('bpa-form-element-col__disabled');
					}
				}
				
				let is_customer_field = del_elm.getAttribute('data-is-customer-field') || false;
				
				if( 'false' != is_customer_field ){
					let meta_key = del_elm.getAttribute('data-metakey');
					let source_item = document.querySelector(`.bpa-cs__item[data-customer-field-meta=${meta_key}]`);
					if( null != source_item ){
						source_item.classList.remove('bpa-restricted');
					}
				}

				del_elm.replaceWith( '' );

				let deleted_field_id = app.field_settings_fields[index].id;
				app.bpa_deleted_fields.push( deleted_field_id );

				delete app.field_settings_fields[ index ];
				let updated_fields = app.field_settings_fields;
				let finalFields = [];
				let all_fields = document.getElementsByClassName('bpa-field-container');
				let field_index = index;
				
				if( all_fields.length > 0 ){
					let i = 0;
					all_fields.forEach( (element,index) => {
						element.setAttribute( 'data-id', i );
						i++;
					});

					let x = 0;
					updated_fields.forEach( (element, index ) => {
						finalFields[x] = element;
						finalFields[x].field_position = (index + 1);
						x++;
					});
				}
				this.field_settings_fields = finalFields;
				this.$forceUpdate();
			},
			setFieldRequired(index, field_id){
				let is_required = app.field_settings_fields[index].is_required;
				if( is_required === true ){
					app.field_settings_fields[index].is_required = false;
				} else {
					app.field_settings_fields[index].is_required = true;
				}
			},
			BPAforceUpdate(){
				this.$forceUpdate();
			},
			setInnerFieldRequired( index, field_id, parent_key ){
				let is_required = app.field_settings_fields[parent_key].field_options.inner_fields[index].is_required;
				if( is_required === true ){
					app.field_settings_fields[parent_key].field_options.inner_fields[index].is_required = false;
				} else {
					app.field_settings_fields[parent_key].field_options.inner_fields[index].is_required = true;
				}
				this.$forceUpdate();
			},
			changeFieldVisibility( index, value, parent_index = "", isCol = 'yes'  ){
				if( typeof parent_index != 'undefined' && parent_index !== "" ){
					if(isCol != 'yes'){
						app.field_settings_fields[parent_index].field_options.inner_fields[isCol].field_options.inner_fields[index].visibility = value;
					}else{
						app.field_settings_fields[parent_index].field_options.inner_fields[index].visibility = value;
					}					
				} else {
					app.field_settings_fields[index].field_options.visibility = value;
				}
				this.$forceUpdate();
			},
			saveFieldSettings( index, parent_key ){
				let field_settings = app.field_settings_fields[index];
				if( typeof parent_key != 'undefined' ){
					field_settings = app.field_settings_fields[parent_key].field_options.inner_fields[index];
					this.$forceUpdate();
				}
				let field_visibility = field_settings.field_options.visibility;
				if( 'services' == field_visibility ){
					let selected_services = field_settings.field_options.selected_services;
					if( selected_services.length < 1 ){
						app.bookingpress_service_error = true;
						return false;
					} else {
						app.bookingpress_service_error = false;
					}
				} else {
					app.bookingpress_service_error = false;
				}
				if( typeof parent_key != 'undefined' ){	
					app.field_settings_fields[parent_key].field_options.inner_fields[index].is_edit = false;
				} else {
					app.field_settings_fields[index].is_edit = false;
				}
				this.$forceUpdate();
			},
			bpaAddfieldValue( index, parent_index = "", isCol = 'yes' ){
				let field_settings;
				if( typeof parent_index != 'undefined' && parent_index !== "" ){
					if(isCol != 'yes'){
						field_settings = app.field_settings_fields[parent_index].field_options.inner_fields[isCol].field_options.inner_fields[index];						
					}else{
						field_settings = app.field_settings_fields[parent_index].field_options.inner_fields[index];
					}					
				} else {
					field_settings = app.field_settings_fields[index];
				}
				let field_values = field_settings.field_values;
				let last_field_values;
				if( field_values.length > 0 ){
					let field_value = field_values[ field_values.length - 1 ];
					last_field_values = {
						'label' : field_value.label,
						'value' : field_value.value
					};
				} else {
					last_field_values = {
						'label' : 'Option 1',
						'value' : 'Option 1'
					}
				}
				if( typeof parent_index != 'undefined' && parent_index !== "" ){
					if(isCol != 'yes'){
						app.field_settings_fields[parent_index].field_options.inner_fields[isCol].field_options.inner_fields[index].field_values.push( last_field_values );
					}else{
						app.field_settings_fields[parent_index].field_options.inner_fields[index].field_values.push( last_field_values );
					}					
				} else {
					app.field_settings_fields[index].field_values.push( last_field_values );
				}
				this.$forceUpdate();
			},
			bpaRemovefieldValue( key_index, field_index, parent_index = "", isCol = 'yes' ){
				let field_settings;
				if( typeof parent_index != 'undefined' && parent_index !== "" ){
					if(isCol != 'yes'){
						field_settings = app.field_settings_fields[parent_index].field_options.inner_fields[isCol].field_options.inner_fields[field_index];
					}else{
						field_settings = app.field_settings_fields[parent_index].field_options.inner_fields[field_index];
					}					
				} else {
					field_settings = app.field_settings_fields[field_index]
				}
				if( field_settings.field_values.length > 1 ){
					delete field_settings.field_values[key_index];
					let updated_field_values = [];
					let field_values = field_settings.field_values;
					let i = 0;
					field_values.forEach(function(element,index){
						updated_field_values[i] = element;
						i++;
					});
					if( typeof parent_index != 'undefined' && parent_index !== "" ){
						if(isCol != 'yes'){
							app.field_settings_fields[parent_index].field_options.inner_fields[isCol].field_options.inner_fields[field_index].field_values = updated_field_values;
						}else{
							app.field_settings_fields[parent_index].field_options.inner_fields[field_index].field_values = updated_field_values;
						}						
					} else {
						app.field_settings_fields[field_index].field_values = updated_field_values;
					}
				}
				this.$forceUpdate();
			},
			closeFieldValueBtn(index, parent_index = "", isCol = 'yes'){
				if( typeof parent_index != 'undefined' && parent_index !== "" ){
					if(isCol != 'yes'){
						app.field_settings_fields[parent_index].field_options.inner_fields[isCol].field_options.inner_fields[index].is_edit_values = false;
					}else{
						app.field_settings_fields[parent_index].field_options.inner_fields[index].is_edit_values = false;
					}					
				} else {
					app.field_settings_fields[index].is_edit_values = false;
				}
				this.$forceUpdate();
			},
			bpaDisplayPresetValues( index, parent_index = "", isCol = 'yes' ){
				if( typeof parent_index != 'undefined' && parent_index !== "" ){
					if(isCol != 'yes'){
						app.field_settings_fields[parent_index].field_options.inner_fields[isCol].field_options.inner_fields[index].enable_preset_fields = true;
					}else{
						app.field_settings_fields[parent_index].field_options.inner_fields[index].enable_preset_fields = true;
					}					
				} else {	
					app.field_settings_fields[index].enable_preset_fields = true;
				}
				this.$forceUpdate();
			},
			bpaHidePresetValues( index, parent_index = "", isCol = 'yes' ){
				if( typeof parent_index != 'undefined' && parent_index !== ""  ){
					if(isCol != 'yes'){
						app.field_settings_fields[parent_index].field_options.inner_fields[isCol].field_options.inner_fields[index].enable_preset_fields = false;
					}else{
						app.field_settings_fields[parent_index].field_options.inner_fields[index].enable_preset_fields = false;
					}					
				} else {
					app.field_settings_fields[index].enable_preset_fields = false;
				}
				this.$forceUpdate();
			},
			applyPresetFields( index, parent_index = "", isCol = 'yes' ){
				let preset_choice;
				if( typeof parent_index != 'undefined' && parent_index !== ""  ){
					if(isCol != 'yes'){
						preset_choice = app.field_settings_fields[parent_index].field_options.inner_fields[isCol].field_options.inner_fields[index].preset_field_choice;
					}else{
						preset_choice = app.field_settings_fields[parent_index].field_options.inner_fields[index].preset_field_choice;
					}					
				} else {
					preset_choice = app.field_settings_fields[index].preset_field_choice;
				}
				if( preset_choice == '' ){
					return false;
				}
				let postData = [];
				postData.preset_key = preset_choice;
				postData.action = 'bpa_load_preset_field_data';
				postData._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
				app.is_display_preset_value_loader = true;
				app.preset_btn_disable = true;
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) ).then(
					function( response ){
						if( response.data.variant == 'error' ){
							this.$notify({
								title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
								message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
								type: 'error',
								customClass: 'error_notification',
								duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
							});
						} else {
							let preset_values = response.data.preset_values;
							if( typeof parent_index != 'undefined' && parent_index !== ""){
								if(isCol != 'yes'){
									app.field_settings_fields[parent_index].field_options.inner_fields[isCol].field_options.inner_fields[index].field_values = preset_values;
								}else{
									app.field_settings_fields[parent_index].field_options.inner_fields[index].field_values = preset_values;
								}								
							} else {
								app.field_settings_fields[index].field_values = preset_values;
							}
						}
						app.$forceUpdate();
						app.is_display_preset_value_loader = false;
						app.preset_btn_disable = false;
					}.bind(this)
				).catch( function( error ){
					this.$notify({
						title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
						message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
					});
				});
				app.$forceUpdate();
				setTimeout(function(){
					app.is_display_preset_value_loader = false;
					app.preset_btn_disable = false;
				},3000);
			},		
			open_add_redirection_modal(type){
				const vm = this;
				vm.bookingpress_redirection_action_type = type;
				vm.add_redirection_msg_modal = true;
				setTimeout(function(){
					if(type == 'thankyou') {
						document.getElementById('bookigpress_redirection_msg').value = vm.booking_form_settings.bookingpress_thankyou_msg;
						if(null != tinyMCE.activeEditor) {
							tinyMCE.activeEditor.setContent(vm.booking_form_settings.bookingpress_thankyou_msg);
						}
					} else{						
						document.getElementById('bookigpress_redirection_msg').value = vm.booking_form_settings.bookingpress_failed_payment_msg;
						if(null != tinyMCE.activeEditor) {
							tinyMCE.activeEditor.setContent(vm.booking_form_settings.bookingpress_failed_payment_msg);
						}
					}				
				},100);
			},
			close_add_redirection_modal() {
				const vm = this;
				vm.bookingpress_redirection_action_type = '';
				vm.add_redirection_msg_modal = false;
				document.getElementById('bookigpress_redirection_msg').value = '';
			},
			bookingpress_save_redirection_msg() {
				const vm = this				
				const formData = new FormData(vm.$refs.redirection_message_form.$el);
				const data = {};
				for (let [key, val] of formData.entries()) {
					Object.assign(data, { [key]: val })
				}
				var bookigpress_redirection_msg = data.bookigpress_redirection_msg;
				if(null != tinyMCE.activeEditor) {
					var bookigpress_redirection_msg = tinymce.activeEditor.getContent();
				}
				if(vm.bookingpress_redirection_action_type == 'thankyou') {
					vm.booking_form_settings.bookingpress_thankyou_msg = bookigpress_redirection_msg;
				} else{
					vm.booking_form_settings.bookingpress_failed_payment_msg = bookigpress_redirection_msg;					
				}				
				vm.close_add_redirection_modal();
			},
			bookingpress_change_form_sequence(){
				const vm = this				
				if(vm.booking_form_settings.bookingpress_form_sequance['0'] != 'undefined' && vm.booking_form_settings.bookingpress_form_sequance['0'] != "service_selection" ){
					vm.booking_form_settings.hide_category_service_selection = false;
					vm.hide_service_step_disabled = true
					vm.bookingpress_form_sequance_arr['service']['is_visible'] = '1';
					
				}else{
					vm.hide_service_step_disabled = false
				}
				let booking_sequance_arr = {};
				for(m in vm.booking_form_settings.bookingpress_form_sequance) {
					for(i in vm.bookingpress_form_sequance_arr) {
						if(vm.bookingpress_form_sequance_arr[i]['tab_name'] != 'undefined' && vm.bookingpress_form_sequance_arr[i]['tab_name'] == vm.
						booking_form_settings.bookingpress_form_sequance[m]) {
							booking_sequance_arr[i] = Object.assign(vm.bookingpress_form_sequance_arr[i]);							
						}
					}
				}
				for(let i in vm.bookingpress_form_sequance_arr) {

					if( "undefined" == typeof booking_sequance_arr[i] ){
						booking_sequance_arr[i] = Object.assign(vm.bookingpress_form_sequance_arr[i]);
					}
				}

				vm.bookingpress_form_sequance_arr = booking_sequance_arr;				
				vm.bookingpress_after_change_position()
			},
			updateSequencePos: function(currentElement){
				const vm = this;
				var old_index = currentElement.oldIndex;
				var new_index = currentElement.newIndex;

				let new_sequence = [];
				/** move 'staff_selection' option to last if staff is not activated */
				let use_new_sequence = false;
				if( 0 == vm.is_staffmember_activated ){
					vm.booking_form_settings.bookingpress_form_sequance.forEach( ( element, index ) => {
						if( 'staff_selection' != element ){
							new_sequence.push( element );
						}
					});

					new_sequence.push( 'staff_selection' );
					use_new_sequence = true;
				}

				if( true == use_new_sequence ){
					vm.booking_form_settings.bookingpress_form_sequance = new_sequence;
				}
				
				if (new_index >= vm.booking_form_settings.bookingpress_form_sequance.length) {
					var k = new_index - vm.booking_form_settings.bookingpress_form_sequance.length + 1;
					while (k--) {
						vm.booking_form_settings.bookingpress_form_sequance.push(undefined);
					}
				}
				vm.booking_form_settings.bookingpress_form_sequance.splice(new_index, 0, vm.booking_form_settings.bookingpress_form_sequance.splice(old_index, 1)[0]);
				
				
				vm.booking_form_settings.bookingpress_form_sequance.forEach( (element,index) =>{
					vm.booking_form_settings.bpa_sequance_pos[ element ] = index + 1;
				})
				vm.bookingpress_change_form_sequence();								
            },
			bookingpress_after_change_position() {
				const vm = this;
				if(vm.booking_form_settings.hide_category_service_selection == true) {
					vm.bookingpress_form_sequance_arr['service']['is_visible'] = '0';					
					vm.bookingpress_form_sequance_arr['datetime']['previous_tab'] = '';
				} else {
					vm.bookingpress_form_sequance_arr['service']['is_visible'] = '1';
					vm.bookingpress_form_sequance_arr['datetime']['previous_tab'] = '1';
				}
				if(vm.is_staffmember_activated == 1) {
					if(vm.booking_form_settings.hide_staffmember_selection == true) {
						vm.bookingpress_form_sequance_arr['staffmembers']['is_visible'] = '0';
					} else {
						vm.bookingpress_form_sequance_arr['staffmembers']['is_visible'] = '1';					
					}


					let total_tabs = vm.bookingpress_form_sequance_arr.length;
					let m = 0;
					let form_seq_keys = Object.keys( vm.bookingpress_form_sequance_arr );
					for( let x in vm.bookingpress_form_sequance_arr ){
						
						if( m == 0 ){
							vm.bookingpress_form_sequance_arr[x]['previous_tab'] = '';
							vm.bookingpress_form_sequance_arr[x]['next_tab'] = vm.bookingpress_form_sequance_arr[ form_seq_keys[m+1] ]['title'];
						} else if( m == total_tabs ){
							vm.bookingpress_form_sequance_arr[x]['previous_tab'] = vm.bookingpress_form_sequance_arr[ form_seq_keys[m-1] ]['title'];
							vm.bookingpress_form_sequance_arr[x]['next_tab'] = '';
						} else {
							vm.bookingpress_form_sequance_arr[x]['previous_tab'] = vm.bookingpress_form_sequance_arr[ form_seq_keys[m-1] ]['title'];
							if( "undefined" != typeof vm.bookingpress_form_sequance_arr[ form_seq_keys[m+1] ] ){
								vm.bookingpress_form_sequance_arr[x]['next_tab'] = vm.bookingpress_form_sequance_arr[ form_seq_keys[m+1] ]['title'];
							}
						}
							m++;
					}
				}
					
				<?php
				do_action('bookingpress_after_change_customize_sequance');
				?>
				var is_set_tab = 0;
				for(m in vm.bookingpress_form_sequance_arr ) {
					if(vm.bookingpress_form_sequance_arr[m]['is_visible'] == '1') {						
						if(is_set_tab == 0) {
							vm.formActiveTab = vm.bookingpress_form_sequance_arr[m]['name'];
							is_set_tab = 1;
						}
					} 
				}
			},
			<?php
		}
		
		/**
		 * Function for delete removed field in customize Form Fields page
		 *
		 * @param  mixed $bookingpress_field_settings_data
		 * @return void
		 */
		function bookingpress_delete_removed_field_func( $bookingpress_field_settings_data ) {

			$deletedFields = ! empty( $_POST['deletedFields'] ) ? array_map( 'intval', $_POST['deletedFields'] ) : array();  // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

			global $wpdb, $BookingPress, $tbl_bookingpress_form_fields;

			if ( ! empty( $deletedFields ) ) {

				$field_ids = $deletedFields;

				$where_placeholder  = ' bookingpress_form_field_id IN(';
				$where_placeholder .= rtrim( str_repeat( '%d,', count( $field_ids ) ), ',' );
				$where_placeholder .= ')';

				array_unshift( $field_ids, $where_placeholder );

				$where_clause = call_user_func_array( array( $wpdb, 'prepare' ), $field_ids );

				/** Delete Appointment Booking Form Fields if the deleted field is referenced to the customer field */
				$bpa_get_fields = $wpdb->get_results( "SELECT bookingpress_field_options,bookingpress_field_type, bookingpress_field_meta_key FROM `{$tbl_bookingpress_form_fields}` WHERE {$where_clause}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name and $where_clause has already been passed through the $wpdb->prepare function.
				
				
				if( !empty( $bpa_get_fields ) ){
					foreach( $bpa_get_fields as $fk => $fval ){
						$field_metakey = $fval->bookingpress_field_meta_key;
						$field_type = $fval->bookingpress_field_type;
						if($field_type == "repeater"){
							$all_child_fields_id = array();
							$field_options = $fval->bookingpress_field_options;

							if(!empty($field_options)){
								$field_options_arr = json_decode($field_options,true);
								if(is_array($field_options_arr) && !empty($field_options_arr)){									
									if(isset($field_options_arr['inner_fields']) && !empty($field_options_arr['inner_fields'])){
										foreach($field_options_arr['inner_fields'] as $inner_fields){
											$delete_id = (isset($inner_fields['id']))?$inner_fields['id']:'';
											$delete_id = (!empty($delete_id))?str_replace('inner_field_','',$delete_id):'';
											if(!empty($delete_id)){
												$all_child_fields_id[] = $delete_id;
											}
											if(isset($inner_fields['field_options']['inner_fields']) && !empty($inner_fields['field_options']['inner_fields'])){
												foreach($inner_fields['field_options']['inner_fields'] as $inneropsubfields){
													$delete_id = (isset($inneropsubfields['id']))?$inneropsubfields['id']:'';
													$delete_id = (!empty($delete_id))?str_replace('inner_field_','',$delete_id):'';
													if(!empty($delete_id)){
														$all_child_fields_id[] = $delete_id;
													}
												}
											}
										}
									}

									if( !empty( $all_child_fields_id ) ){
										$new_child_field_ids = [];
										foreach( $all_child_fields_id as $child_key => $child_fid ){
											$search = array_search( $child_fid, array_column( $fields_data, 'id') );

											if( false !== $search ){
												unset( $all_child_fields_id[ $child_key ] );
											}
										}
									}
									
									if(!empty($all_child_fields_id)){

										$repeter_sub_field_ids = $all_child_fields_id;
										$where_repeter_placeholder  = ' bookingpress_form_field_id IN(';
										$where_repeter_placeholder .= rtrim( str_repeat( '%d,', count( $repeter_sub_field_ids ) ), ',' );
										$where_repeter_placeholder .= ')';						
										array_unshift( $repeter_sub_field_ids, $where_repeter_placeholder );						
										$where_clause_repeter = call_user_func_array( array( $wpdb, 'prepare' ), $repeter_sub_field_ids );
										$wpdb->query( "DELETE FROM {$tbl_bookingpress_form_fields} WHERE {$where_clause_repeter}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name and $where_clause has already been passed through the $wpdb->prepare function.
										
									}

								}
							}

						}
						$booking_form_field_id = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_form_field_id FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_field_meta_key = %s AND bookingpress_is_customer_field = %d", $field_metakey, 0 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
						if( 0 < $booking_form_field_id ){
							$wpdb->delete(
								$tbl_bookingpress_form_fields,
								array(
									'bookingpress_form_field_id' => $booking_form_field_id
								)
							);
						}
					}
				}
				/** Delete Appointment Booking Form Fields if the deleted field is referenced to the customer field */

				$wpdb->query( "DELETE FROM {$tbl_bookingpress_form_fields} WHERE {$where_clause}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name and $where_clause has already been passed through the $wpdb->prepare function.

				
			}

			if ( ! empty( $bookingpress_field_settings_data ) ) {
				foreach ( $bookingpress_field_settings_data as $field_data ) {
					$ftype = $field_data['field_type'];
					if ( in_array( $ftype, array( '2_col', '3_col', '4_col' ) ) ) {
						$totalInnerFields = (isset($field_data['field_options']['inner_fields']))?count( $field_data['field_options']['inner_fields'] ):0;
						$blankFields      = 0;
						foreach ( $field_data['field_options']['inner_fields'] as $inner_field_data ) {
							if ( true === $inner_field_data['is_blank'] || 'true' === $inner_field_data['is_blank'] || "true" === $inner_field_data['is_blank'] ) {
								$blankFields++;
							}
						}
						if ( $blankFields == $totalInnerFields ) {
							$wpdb->delete(
								$tbl_bookingpress_form_fields,
								array(
									'bookingpress_form_field_id' => $field_data['id'],
								)
							);
						}
					}
				}
			}

		}
		
		/**
		 * Function for execute code after load saved field settings
		 *
		 * @return void
		 */
		function bookingpress_load_saved_field_settings() {
			?>
			//setTimeout(function(){
				vm2.bookingpress_load_field_settings_data();
				app.$forceUpdate();
			//},1000);
			<?php
		}
		
		/**
		 * Function for after load field settings data
		 *
		 * @return void
		 */
		function bookingpress_after_load_field_settings_func() {
			?>
			setTimeout(function(){
				const bpaortable_obj = new BPASortable();
			},100);
			<?php
		}
		
		/**
		 * Function for load preset field data
		 *
		 * @return void
		 */
		function bpa_load_preset_field_data_func() {

			$preset_key = ! empty( $_REQUEST['preset_key'] ) ? sanitize_text_field( $_REQUEST['preset_key'] ) : '';

			$response = array();
			
			$bpa_check_authorization = $this->bpa_check_authentication( 'load_preset_field_settings_data', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			if ( empty( $preset_key ) ) {
				$response['msg'] = esc_html__( 'Please select preset option', 'bookingpress-appointment-booking' );
				echo wp_json_encode( $response );
				die;
			}

			$preset_values = array();
			if ( 'countries' == $preset_key ) {
				$countries = $this->get_countries();
				foreach ( $countries as $country ) {
					$preset_values[] = array(
						'label' => $country,
						'value' => $country,
					);
				}
			} elseif ( 'us_states' == $preset_key ) {
				$us_states = $this->get_us_states();
				foreach ( $us_states as $us_state ) {
					$preset_values[] = array(
						'label' => $us_state,
						'value' => $us_state,
					);
				}
			} elseif ( 'us_states_abbr' == $preset_key ) {
				$us_states = $this->get_us_states();
				foreach ( $us_states as $abbr => $us_state ) {
					$preset_values[] = array(
						'label' => $abbr,
						'value' => $abbr,
					);
				}
			} elseif ( 'age_group' == $preset_key ) {
				$preset_values = array(
					array(
						'label' => esc_html__( 'Under 18', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Under 18', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( '18-24', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( '18-24', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( '25-34', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( '25-34', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( '35-44', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( '35-44', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( '45-54', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( '45-54', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( '55-64', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( '55-64', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( '65 or above', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( '65 or above', 'bookingpress-appointment-booking' ),
					),
				);
			} elseif ( 'satisfaction' == $preset_key ) {
				$preset_values = array(
					array(
						'label' => esc_html__( 'Very Satisfied', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Very Satisfied', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Satisfied', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Satisfied', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Neutral', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Neutral', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Unsatisfied', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Unsatisfied', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Very Unsatisfied', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Very Unsatisfied', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'N/A', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'N/A', 'bookingpress-appointment-booking' ),
					),
				);
			} elseif ( 'days' == $preset_key ) {
				for ( $d = 1; $d <= 31; $d++ ) {
					$preset_values[] = array(
						'label' => $d,
						'value' => $d,
					);
				}
			} elseif ( 'week_days' == $preset_key ) {
				$preset_values = array(
					array(
						'label' => esc_html__( 'Sunday', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Sunday', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Monday', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Monday', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Tuesday', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Tuesday', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Wednesday', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Wednesday', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Thursday', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Thursday', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Friday', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Friday', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Saturday', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Saturday', 'bookingpress-appointment-booking' ),
					),
				);
			} elseif ( 'months' == $preset_key ) {
				$preset_values = array(
					array(
						'label' => esc_html__( 'January', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'January', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'February', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'February', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'March', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'March', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'April', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'April', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'May', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'May', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'June', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'June', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'July', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'July', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'August', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'August', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'September', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'September', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'October', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'October', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'November', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'November', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'December', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'December', 'bookingpress-appointment-booking' ),
					),
				);
			} elseif ( 'years' == $preset_key ) {
				$start = 1935;
				$end   = date( 'Y' );
				for ( $y = $start; $y <= $end; $y++ ) {
					$preset_values[] = array(
						'label' => $y,
						'value' => $y,
					);
				}
			} elseif ( 'prefix' == $preset_key ) {
				$preset_values = array(
					array(
						'label' => esc_html__( 'Mr', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Mr', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Mrs', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Mrs', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Ms', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Ms', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Miss', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Miss', 'bookingpress-appointment-booking' ),
					),
					array(
						'label' => esc_html__( 'Sr', 'bookingpress-appointment-booking' ),
						'value' => esc_html__( 'Sr', 'bookingpress-appointment-booking' ),
					),
				);
			} elseif ( 'telephone_code' == $preset_key ) {
				$country_codes = $this->get_country_codes();
				foreach ( $country_codes as $code => $country_name ) {
					$preset_values[] = array(
						'label' => $code,
						'value' => $code,
					);
				}
			}

			$response['variant']       = 'success';
			$response['title']         = esc_html__( 'Success', 'bookingpress-appointment-booking' );
			$response['preset_values'] = $preset_values;
			$response['msg']           = esc_html__( 'Preset Values Applied successfully', 'bookingpress-appointment-booking' );

			echo wp_json_encode( $response );

			die;
		}
		
		/**
		 * Function for arrange form fields
		 *
		 * @param  mixed $form_fields_data
		 * @param  mixed $db_field_options
		 * @return void
		 */
		function bookingpress_arrange_form_fields_func( $form_fields_data, $db_field_options ) {

			global $bookingpress_front_vue_data_fields;


			if ( 'text' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Text';
			} elseif ( 'textarea' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Textarea';
			} elseif ( 'email' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Email';
			} elseif ( 'dropdown' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Dropdown';
			} elseif ( 'checkbox' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Checkbox';
			} elseif ( 'radio' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Radio';
			} elseif ( 'date' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Date';
			} elseif ( 'file' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'File';
			} elseif ( 'phone' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = 'Phone';
			} elseif( 'terms_and_conditions' == $db_field_options['bookingpress_field_type']) {
				$form_fields_data['field_type'] = 'terms_and_conditions';
			} elseif( 'password' == $db_field_options['bookingpress_field_type']) {
				$form_fields_data['field_type'] = 'Password';
			} elseif ( '2_col' == $db_field_options['bookingpress_field_type'] || '3_col' == $db_field_options['bookingpress_field_type'] || '4_col' == $db_field_options['bookingpress_field_type'] ) {
				$form_fields_data['field_type'] = $db_field_options['bookingpress_field_type'];
			} else {
				$form_fields_data['field_type'] = 'Custom';
			}

			if ( empty( $db_field_options['bookingpress_field_meta_key'] ) ) {
				$form_fields_data['meta_key'] = strtolower( sanitize_text_field( str_replace( ' ', '_', $db_field_options['bookingpress_field_type'] ) ) ) . '_' . wp_generate_password( 6, false );
			} else {
				$form_fields_data['meta_key'] = $db_field_options['bookingpress_field_meta_key'];
			}

			
			if ( empty( $form_fields_data['v_model_value'] ) ) {
				$form_fields_data['v_model_value'] = $form_fields_data['meta_key'];
			} 
			
			if( $form_fields_data['field_type'] == 'Password'){
				$form_fields_data['v_model_value'] = 'customer_password';
			}
			

	 
			if( 'file' == $db_field_options['bookingpress_field_type'] && !is_admin() ){
				$action_url = admin_url('admin-ajax.php');// wp_nonce_url(  . '?action=bpa_front_file_upload', 'bpa_file_upload_' . $db_field_options['bookingpress_field_meta_key'] );

				$action_data = array(
					'action' => 'bpa_front_file_upload',
					'_wpnonce' => wp_create_nonce( 'bpa_file_upload_' . $db_field_options['bookingpress_field_meta_key'] ),
					'field_key' => $db_field_options['bookingpress_field_meta_key']
				);
				$form_fields_data['bpa_action_url'] = $action_url;
				$form_fields_data['bpa_ref_name'] = str_replace('_', '', $db_field_options['bookingpress_field_meta_key']);
				$action_data['bpa_ref'] =$form_fields_data['bpa_ref_name'];

				$file_field_options = json_decode( $db_field_options['bookingpress_field_options'], true );
				$action_data['file_size'] = $file_field_options['max_file_size'];
				$form_fields_data['bpa_action_data'] = $action_data;
				$action_data['bpa_accept_files'] = !empty( $file_field_options['allowed_file_ext'] ) ?  base64_encode( $file_field_options['allowed_file_ext'] ) : '';

			}

			$form_fields_data['css_class'] = ! empty( $db_field_options['bookingpress_field_css_class'] ) ? $db_field_options['bookingpress_field_css_class'] : '';

			$form_fields_data['is_default']           = isset( $db_field_options['bookingpress_field_is_default'] ) ? (int) $db_field_options['bookingpress_field_is_default'] : 0;
			$form_fields_data['is_edit_values']       = false;
			$form_fields_data['enable_preset_fields'] = false;
			$form_fields_data['preset_field_choice']  = '';

			$form_field_values = array();

			if ( in_array( $db_field_options['bookingpress_field_type'], array( 'checkbox', 'radio', 'dropdown' ) ) ) {
				if ( empty( $db_field_options['bookingpress_field_values'] ) ) {
					$form_field_values = array(
						array(
							'value' => 'Option 1',
							'label' => 'Option 1',
						),
						array(
							'value' => 'Option 2',
							'label' => 'Option 2',
						),
					);
				} else {
					$db_options = json_decode( $db_field_options['bookingpress_field_values'], true );
					if ( empty( $db_options ) ) {
						$form_field_values = array(
							array(
								'value' => 'Option 1',
								'label' => 'Option 1',
							),
							array(
								'value' => 'Option 2',
								'label' => 'Option 2',
							),
						);
					} else {
						$form_field_values = $db_options;
					}
				}
			}
			
			if ( 'checkbox' == $db_field_options['bookingpress_field_type'] ) {
				
				$temp_form_fields_data = array();
				$fmeta_key = $form_fields_data['meta_key'];
				foreach ( $form_field_values as $k => $v ) {
					$form_fields_data[ $fmeta_key] [ $k ] = '';	
				}
				$bookingpress_front_vue_data_fields['appointment_step_form_data'][$fmeta_key] = array();
			}

			$form_fields_data['field_values'] = $form_field_values;

			$field_db_options = $db_field_options['bookingpress_field_options'];

			if ( ! empty( $field_db_options ) ) {
				if ( ! is_array( $field_db_options ) ) {
					$form_fields_data['field_options'] = json_decode( $field_db_options, true );
					if ( empty( $form_fields_data['field_options'] ) ) {
						$form_fields_data['field_options'] = array(
							'layout'         => '1col',
							'inner_class'    => '1col',
							'separate_value' => false,
						);
					}
				} else {
					$form_fields_data['field_options'] = $field_db_options;
				}
			} else {
				$form_fields_data['field_options'] = array(
					'layout'         => '1col',
					'inner_class'    => '1col',
					'separate_value' => false,
				);
			}

			if ( ! isset( $form_fields_data['field_options']['visibility'] ) ) {
				$form_fields_data['field_options']['visibility'] = 'always';
			}

			if ( ! isset( $form_fields_data['field_options']['selected_services'] ) ) {
				$form_fields_data['field_options']['selected_services'] = array();
			}

			if ( ! isset( $form_fields_data['field_options']['separate_value'] ) ) {
				$form_fields_data['field_options']['separate_value'] = false;
			}
			if ( $form_fields_data['field_type'] == 'Text' || $form_fields_data['field_type'] == 'Textarea' ) {
				if ( ! isset( $form_fields_data['field_options']['minimum'] ) ) {
					$form_fields_data['field_options']['minimum'] = '';
				}

				if ( ! isset( $form_fields_data['field_options']['maximum'] ) ) {
					$form_fields_data['field_options']['maximum'] = '';
				}
			}

			if ( $form_fields_data['field_type'] == 'Date' ) {
				if ( ! isset( $form_fields_data['field_options']['enable_timepicker'] ) ) {
					$form_fields_data['field_options']['enable_timepicker'] = false;
				} else {
					if ( 'true' === $form_fields_data['field_options']['enable_timepicker'] || true === $form_fields_data['field_options']['enable_timepicker'] ) {
						$form_fields_data['field_options']['enable_timepicker'] = true;
					} else {
						$form_fields_data['field_options']['enable_timepicker'] = false;
					}
				}
			}

			if ( $form_fields_data['field_type'] == 'File' ) {
				if ( ! isset( $form_fields_data['field_options']['allowed_file_ext'] ) ) {
					$form_fields_data['field_options']['allowed_file_ext'] = '';
				}

				if ( ! isset( $form_fields_data['field_options']['invalid_field_message'] ) ) {
					$form_fields_data['field_options']['invalid_field_message'] = esc_html__( 'Invalid file selected', 'bookingpress-appointment-booking' );
				}

				if ( ! isset( $form_fields_data['field_options']['max_file_size'] ) ) {
					$form_fields_data['field_options']['max_file_size'] = 2;
				}

				if( ! isset( $form_fields_data['field_options']['attach_with_email'] ) ){
					$form_fields_data['field_options']['attach_with_email'] = false;
				}

				if( ! isset( $form_fields_data['field_options']['browse_button_label'] ) ){
					$form_fields_data['field_options']['browse_button_label'] = esc_html__( 'Browse', 'bookingpress-appointment-booking' );
				}

			}

			if ( ! empty( $form_fields_data['field_options']['inner_fields'] ) ) {

				foreach ( $form_fields_data['field_options']['inner_fields'] as $k => $v ) {

					if(isset($form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields']) && !empty($form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'])){

						foreach ( $form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'] as $kk => $vv ) {

							if ( isset($vv['is_blank']) && 'true' === $vv['is_blank'] ) {
								continue;
							}
							if ( isset( $vv['is_edit'] ) ) {
								$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['is_edit'] = ( $vv['is_edit'] === 'true' ) ? true : false;
							}
							if ( isset( $vv['is_edit_values'] ) ) {
								$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['is_edit_values'] = ( $vv['is_edit_values'] === 'true' ) ? true : false;
							}
							if ( ! isset( $vv['innerIndex'] ) ) {
								$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['innerIndex'] = (int) $kk;
							} else {
								$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['innerIndex'] = (int) $vv['innerIndex'];
							}
		
							if ( isset( $vv['field_position'] ) ) {
								$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['field_position'] = (int) $vv['field_position'];
							}
		
							if ( isset( $vv['id'] ) ) {
								$vv['id'] = str_replace('inner_field_','',$vv['id']);
								$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['id'] =  $vv['id'];
							}
		
							if ( empty( $vv['meta_key'] ) ) {
								$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['meta_key'] = strtolower( str_replace( ' ', '_', $vv['field_type'] ) ) . '_' . wp_generate_password( 6, false );
							}
		
							if ( empty( $vv['css_class'] ) ) {
								$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['css_class'] = '';
							}
		
							if ( $vv['field_type'] == 'Text' || $vv['field_type'] == 'Textarea' ) {
								if ( empty( $vv['field_options']['minimum'] ) ) {
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['field_options']['minimum'] = '';
								}
		
								if ( empty( $vv['field_options']['maximum'] ) ) {
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['field_options']['maximum'] = '';
								}
							}
		
							if ( $vv['field_type'] == 'Date' ) {
								if ( empty( $vv['field_options']['enable_timepicker'] ) ) {
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['field_options']['enable_timepicker'] = false;
								} elseif ( 'true' === $vv['field_options']['enable_timepicker'] || true === $vv['field_options']['enable_timepicker'] ) {
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['field_options']['enable_timepicker'] = true;
								} else {
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['field_options']['enable_timepicker'] = false;
								}
							}
		
							if ( $vv['field_type'] == 'File' ) {
								if ( empty( $vv['field_options']['allowed_file_ext'] ) ) {
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['field_options']['allowed_file_ext'] = '';
								}
		
								if ( empty( $vv['field_options']['max_file_size'] ) ) {
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['field_options']['max_file_size'] = 2;
								}
		
								if ( empty( $vv['field_options']['invalid_field_message'] ) ) {
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['field_options']['invalid_field_message'] = esc_html__( 'Invalid file selected', 'bookingpress-appointment-booking' );
								}
		
								if( empty( $vv['field_options']['attach_with_email'] ) ){
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['field_options']['attach_with_email'] = false;
								}
								
								if( empty( $vv['field_options']['browse_button_label'] ) ){
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['field_options']['browse_button_label'] = esc_html__( 'Browse', 'bookingpress-appointment-booking' );
								}
		
								$action_url = admin_url('admin-ajax.php');
								$action_data = array(
									'action' => 'bpa_front_file_upload',
									'_wpnonce' => wp_create_nonce( 'bpa_file_upload_' . $form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['meta_key'] ),
									'field_key' => $form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['meta_key']
								);
		
								$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][$kk]['bpa_action_url'] = $action_url;
								$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][$kk]['bpa_ref_name'] = str_replace('_', '', $form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['meta_key']);
		
								$action_data['bpa_ref'] = $form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][$kk]['bpa_ref_name'];
								
								$action_data['file_size'] = $form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][$kk]['field_options']['max_file_size'];
		
								$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][$kk]['bpa_action_data'] = $action_data;
								
							}
		
							if ( empty( $vv['v_model_value'] ) ) {						
								if(!empty($vv['is_default']) && $vv['is_default'] == 1) {							
									$bookingpress_v_model_value = '';
									if ($vv['field_name'] == 'fullname' ) {
										$bookingpress_v_model_value = 'customer_name';
									} elseif ($vv['field_name'] == 'firstname' ) {
										$bookingpress_v_model_value = 'customer_firstname';
									} elseif ($vv['field_name'] == 'lastname' ) {
										$bookingpress_v_model_value = 'customer_lastname';
									} elseif ($vv['field_name'] == 'email_address' ) {
										$bookingpress_v_model_value = 'customer_email';
									} elseif ($vv['field_name'] == 'phone_number' ) {
										$bookingpress_v_model_value = 'customer_phone';
									} elseif ($vv['field_name'] == 'note' ) {
										$bookingpress_v_model_value = 'appointment_note';
									} elseif($vv['field_name'] == 'terms_and_conditions'){
										$bookingpress_v_model_value = 'appointment_terms_conditions';
									} elseif($vv['field_name'] == 'username' ){
										$bookingpress_v_model_value = 'customer_username';
									}
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['v_model_value'] = $bookingpress_v_model_value;
								} else if( $vv['field_type'] == 'Password' ){
									$bookingpress_v_model_value = 'customer_password';
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['v_model_value'] = $bookingpress_v_model_value;
								} else {
									$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['v_model_value'] = $vv['meta_key'];
								}						
		
								if ( $vv['field_type'] == 'Checkbox' ) {
		
									foreach ( $vv['field_values'] as $ik => $vv ) {
										$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ][ 'v_model_value_' . $form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['v_model_value'] . '_' . $ik ] = array(
											$form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['v_model_value'] . '_' . $ik => $form_fields_data['field_options']['inner_fields'][0]['field_options']['inner_fields'][ $kk ]['v_model_value'] . '_' . $vv['value'],
										);
									}
								}
							}

						}

					}
					if ( isset($v['is_blank']) && 'true' === $v['is_blank'] ) {
						continue;
					}
					if ( isset( $v['is_edit'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['is_edit'] = ( $v['is_edit'] === 'true' ) ? true : false;
					}
					if ( isset( $v['is_edit_values'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['is_edit_values'] = ( $v['is_edit_values'] === 'true' ) ? true : false;
					}
					if ( ! isset( $v['innerIndex'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['innerIndex'] = (int) $k;
					} else {
						$form_fields_data['field_options']['inner_fields'][ $k ]['innerIndex'] = (int) $v['innerIndex'];
					}

					if ( isset( $v['field_position'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['field_position'] = (int) $v['field_position'];
					}

					if ( isset( $v['id'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['id'] = (int) $v['id'];
					}

					if ( empty( $v['meta_key'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['meta_key'] = strtolower( str_replace( ' ', '_', $v['field_type'] ) ) . '_' . wp_generate_password( 6, false );
					}

					if ( empty( $v['css_class'] ) ) {
						$form_fields_data['field_options']['inner_fields'][ $k ]['css_class'] = '';
					}

					if( !empty( $v['field_type'] ) ){
						if ( $v['field_type'] == 'Text' || $v['field_type'] == 'Textarea' ) {
							if ( empty( $v['field_options']['minimum'] ) ) {
								$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['minimum'] = '';
							}
	
							if ( empty( $v['field_options']['maximum'] ) ) {
								$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['maximum'] = '';
							}
						}
	
						if ( $v['field_type'] == 'Date' ) {
							if ( empty( $v['field_options']['enable_timepicker'] ) ) {
								$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['enable_timepicker'] = false;
							} elseif ( 'true' === $v['field_options']['enable_timepicker'] || true === $v['field_options']['enable_timepicker'] ) {
								$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['enable_timepicker'] = true;
							} else {
								$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['enable_timepicker'] = false;
							}
						}
	
						if ( $v['field_type'] == 'File' ) {
							if ( empty( $v['field_options']['allowed_file_ext'] ) ) {
								$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['allowed_file_ext'] = '';
							}
	
							if ( empty( $v['field_options']['max_file_size'] ) ) {
								$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['max_file_size'] = 2;
							}
	
							if ( empty( $v['field_options']['invalid_field_message'] ) ) {
								$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['invalid_field_message'] = esc_html__( 'Invalid file selected', 'bookingpress-appointment-booking' );
							}
	
							if( empty( $v['field_options']['attach_with_email'] ) ){
								$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['attach_with_email'] = false;
							}

							if( empty( $v['field_options']['browse_button_label'] ) ){
								$form_fields_data['field_options']['inner_fields'][ $k ]['field_options']['browse_button_label'] = esc_html__( 'Browse', 'bookingpress-appointment-booking' );
							}
	
							$action_url = admin_url('admin-ajax.php');
							$action_data = array(
								'action' => 'bpa_front_file_upload',
								'_wpnonce' => wp_create_nonce( 'bpa_file_upload_' . $form_fields_data['field_options']['inner_fields'][ $k ]['meta_key'] ),
								'field_key' => $form_fields_data['field_options']['inner_fields'][ $k ]['meta_key']
							);
	
							$form_fields_data['field_options']['inner_fields'][$k]['bpa_action_url'] = $action_url;
							$form_fields_data['field_options']['inner_fields'][$k]['bpa_ref_name'] = str_replace('_', '', $form_fields_data['field_options']['inner_fields'][ $k ]['meta_key']);
	
							$action_data['bpa_ref'] = $form_fields_data['field_options']['inner_fields'][$k]['bpa_ref_name'];
							
							$action_data['file_size'] = $form_fields_data['field_options']['inner_fields'][$k]['field_options']['max_file_size'];
	
							$form_fields_data['field_options']['inner_fields'][$k]['bpa_action_data'] = $action_data;
							
						}
					}

					if ( empty( $v['v_model_value'] ) ) {						
						if(!empty($v['is_default']) && $v['is_default'] == 1) {							
							$bookingpress_v_model_value = '';
							if ($v['field_name'] == 'fullname' ) {
								$bookingpress_v_model_value = 'customer_name';
							} elseif ($v['field_name'] == 'firstname' ) {
								$bookingpress_v_model_value = 'customer_firstname';
							} elseif ($v['field_name'] == 'lastname' ) {
								$bookingpress_v_model_value = 'customer_lastname';
							} elseif ($v['field_name'] == 'email_address' ) {
								$bookingpress_v_model_value = 'customer_email';
							} elseif ($v['field_name'] == 'phone_number' ) {
								$bookingpress_v_model_value = 'customer_phone';
							} elseif ($v['field_name'] == 'note' ) {
								$bookingpress_v_model_value = 'appointment_note';
							} elseif($v['field_name'] == 'terms_and_conditions'){
								$bookingpress_v_model_value = 'appointment_terms_conditions';
							} elseif($v['field_name'] == 'username' ){
								$bookingpress_v_model_value = 'customer_username';
							}
							$form_fields_data['field_options']['inner_fields'][ $k ]['v_model_value'] = $bookingpress_v_model_value;
						} else if( $v['field_type'] == 'Password' ){
							$bookingpress_v_model_value = 'customer_password';
							$form_fields_data['field_options']['inner_fields'][ $k ]['v_model_value'] = $bookingpress_v_model_value;
						} else {
							$form_fields_data['field_options']['inner_fields'][ $k ]['v_model_value'] = $v['meta_key'];
						}						

						if ( $v['field_type'] == 'Checkbox' ) {

							foreach ( $v['field_values'] as $ik => $v ) {
								$form_fields_data['field_options']['inner_fields'][ $k ][ 'v_model_value_' . $form_fields_data['field_options']['inner_fields'][ $k ]['v_model_value'] . '_' . $ik ] = array(
									$form_fields_data['field_options']['inner_fields'][ $k ]['v_model_value'] . '_' . $ik => $form_fields_data['field_options']['inner_fields'][ $k ]['v_model_value'] . '_' . $v['value'],
								);
							}
						}
					}
				}
				$inner_fields = $form_fields_data['field_options']['inner_fields'];

				$new_inner_fields = $this->bookingpress_sort_inner_fields_before_save( $inner_fields );

				$form_fields_data['field_options']['inner_fields'] = $new_inner_fields;
			}

			$form_fields_data['default_value'] = '';

			$form_fields_data['is_delete'] = false;


			if( isset( $form_fields_data['field_options']['visibility'] ) && 'hidden' == $form_fields_data['field_options']['visibility'] ){
				$form_fields_data['is_hide'] = 1;
			} else {
				$form_fields_data['is_hide'] = 0;
			}

			if ( $form_fields_data['field_name'] == 'Repeater' ) {
				if(!empty($form_fields_data['field_options']['inner_fields'])){
					$field_type_inner = isset($form_fields_data['field_options']['inner_fields'][0]['field_options']['layout'])?$form_fields_data['field_options']['inner_fields'][0]['field_options']['layout']:'';
					if($field_type_inner == "2col" || $field_type_inner == "3col" || $field_type_inner == "4col"){
						$form_fields_data['field_options']["layout"] = $field_type_inner;
						$form_fields_data['field_options']["inner_class"] = $field_type_inner;
					}
				}
			}

			return $form_fields_data;
		}
		
		/**
		 * Function for get countries list
		 *
		 * @return void
		 */
		function get_countries() {
			return apply_filters(
				'bpacountries',
				array(
					addslashes( esc_html__( 'Afghanistan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Albania', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Algeria', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'American Samoa', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Andorra', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Angola', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Anguilla', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Antarctica', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Antigua and Barbuda', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Argentina', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Armenia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Aruba', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Australia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Austria', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Azerbaijan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Bahamas', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Bahrain', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Bangladesh', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Barbados', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Belarus', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Belgium', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Belize', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Benin', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Bermuda', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Bhutan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Bolivia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Bosnia and Herzegovina', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Botswana', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Brazil', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Brunei', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Bulgaria', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Burkina Faso', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Burundi', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Cambodia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Cameroon', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Canada', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Cape Verde', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Cayman Islands', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Central African Republic', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Chad', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Chile', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'China', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Colombia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Comoros', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Congo', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Costa Rica', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Croatia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Cuba', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Cyprus', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Czech Republic', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Denmark', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Djibouti', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Dominica', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Dominican Republic', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'East Timor', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Ecuador', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Egypt', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'El Salvador', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Equatorial Guinea', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Eritrea', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Estonia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Ethiopia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Fiji', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Finland', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'France', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'French Guiana', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'French Polynesia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Gabon', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Gambia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Georgia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Germany', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Ghana', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Gibraltar', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Greece', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Greenland', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Grenada', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Guam', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Guatemala', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Guinea', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Guinea-Bissau', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Guyana', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Haiti', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Honduras', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Hong Kong', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Hungary', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Iceland', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'India', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Indonesia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Iran', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Iraq', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Ireland', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Israel', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Italy', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Jamaica', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Japan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Jordan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Kazakhstan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Kenya', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Kiribati', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'North Korea', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'South Korea', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Kuwait', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Kyrgyzstan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Laos', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Latvia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Lebanon', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Lesotho', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Liberia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Libya', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Liechtenstein', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Lithuania', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Luxembourg', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'North Macedonia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Madagascar', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Malawi', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Malaysia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Maldives', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Mali', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Malta', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Marshall Islands', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Mauritania', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Mauritius', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Mexico', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Micronesia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Moldova', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Monaco', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Mongolia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Montenegro', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Montserrat', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Morocco', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Mozambique', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Myanmar', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Namibia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Nauru', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Nepal', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Netherlands', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'New Zealand', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Nicaragua', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Niger', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Nigeria', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Norway', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Northern Mariana Islands', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Oman', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Pakistan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Palau', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Palestine', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Panama', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Papua New Guinea', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Paraguay', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Peru', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Philippines', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Poland', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Portugal', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Puerto Rico', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Qatar', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Romania', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Russia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Rwanda', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Saint Kitts and Nevis', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Saint Lucia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Saint Vincent and the Grenadines', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Samoa', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'San Marino', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Sao Tome and Principe', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Saudi Arabia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Senegal', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Serbia and Montenegro', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Seychelles', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Sierra Leone', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Singapore', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Slovakia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Slovenia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Solomon Islands', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Somalia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'South Africa', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Spain', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Sri Lanka', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Sudan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Suriname', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Swaziland', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Sweden', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Switzerland', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Syria', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Taiwan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Tajikistan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Tanzania', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Thailand', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Togo', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Tonga', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Trinidad and Tobago', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Tunisia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Turkey', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Turkmenistan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Tuvalu', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Uganda', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Ukraine', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'United Arab Emirates', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'United Kingdom', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'United States', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Uruguay', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Uzbekistan', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Vanuatu', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Vatican City', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Venezuela', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Vietnam', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Virgin Islands, British', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Virgin Islands, U.S.', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Yemen', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Zambia', 'bookingpress-appointment-booking' ) ),
					addslashes( esc_html__( 'Zimbabwe', 'bookingpress-appointment-booking' ) ),
				)
			);
		}
		
		/**
		 * Function for get US States
		 *
		 * @return void
		 */
		function get_us_states() {
			return apply_filters(
				'bpausstates',
				array(
					'AL' => 'Alabama',
					'AK' => 'Alaska',
					'AR' => 'Arkansas',
					'AZ' => 'Arizona',
					'CA' => 'California',
					'CO' => 'Colorado',
					'CT' => 'Connecticut',
					'DE' => 'Delaware',
					'FL' => 'Florida',
					'GA' => 'Georgia',
					'HI' => 'Hawaii',
					'ID' => 'Idaho',
					'IL' => 'Illinois',
					'IN' => 'Indiana',
					'IA' => 'Iowa',
					'KS' => 'Kansas',
					'KY' => 'Kentucky',
					'LA' => 'Louisiana',
					'ME' => 'Maine',
					'MD' => 'Maryland',
					'MA' => 'Massachusetts',
					'MI' => 'Michigan',
					'MN' => 'Minnesota',
					'MS' => 'Mississippi',
					'MO' => 'Missouri',
					'MT' => 'Montana',
					'NE' => 'Nebraska',
					'NV' => 'Nevada',
					'NH' => 'New Hampshire',
					'NJ' => 'New Jersey',
					'NM' => 'New Mexico',
					'NY' => 'New York',
					'NC' => 'North Carolina',
					'ND' => 'North Dakota',
					'OH' => 'Ohio',
					'OK' => 'Oklahoma',
					'OR' => 'Oregon',
					'PA' => 'Pennsylvania',
					'RI' => 'Rhode Island',
					'SC' => 'South Carolina',
					'SD' => 'South Dakota',
					'TN' => 'Tennessee',
					'TX' => 'Texas',
					'UT' => 'Utah',
					'VT' => 'Vermont',
					'VA' => 'Virginia',
					'WA' => 'Washington',
					'WV' => 'West Virginia',
					'WI' => 'Wisconsin',
					'WY' => 'Wyoming',
				)
			);
		}
		
		/**
		 * Function for get country codes
		 *
		 * @return void
		 */
		function get_country_codes() {

			return apply_filters(
				'bpacountrycodes',
				array(
					'+1'   => 'North America',
					'+269' => 'Mayotte, Comoros Is.',
					'+501' => 'Belize',
					'+690' => 'Tokelau',
					'+20'  => 'Egypt',
					'+27'  => 'South Africa',
					'+502' => 'Guatemala',
					'+691' => 'F.S. Micronesia',
					'+212' => 'Morocco',
					'+290' => 'Saint Helena',
					'+503' => 'El Salvador',
					'+692' => 'Marshall Islands',
					'+213' => 'Algeria',
					'+291' => 'Eritrea',
					'+504' => 'Honduras',
					'+7'   => 'Russia, Kazakhstan',
					'+216' => 'Tunisia',
					'+297' => 'Aruba',
					'+505' => 'Nicaragua',
					'+800' => 'Int\'l Freephone',
					'+218' => 'Libya',
					'+298' => 'F�roe Islands',
					'+506' => 'Costa Rica',
					'+81'  => 'Japan',
					'+220' => 'Gambia',
					'+299' => 'Greenland',
					'+507' => 'Panama',
					'+82'  => 'Korea (South)',
					'+221' => 'Senegal',
					'+30'  => 'Greece',
					'+508' => 'St Pierre & Miqu�lon',
					'+84'  => 'Viet Nam',
					'+222' => 'Mauritania',
					'+31'  => 'Netherlands',
					'+509' => 'Haiti',
					'+850' => 'DPR Korea (North)',
					'+223' => 'Mali',
					'+32'  => 'Belgium',
					'+51'  => 'Peru',
					'+224' => 'Guinea',
					'+33'  => 'France',
					'+52'  => 'Mexico',
					'+852' => 'Hong Kong',
					'+225' => 'Ivory Coast',
					'+34'  => 'Spain',
					'+53'  => 'Cuba',
					'+853' => 'Macau',
					'+226' => 'Burkina Faso',
					'+350' => 'Gibraltar',
					'+54'  => 'Argentina',
					'+855' => 'Cambodia',
					'+227' => 'Niger',
					'+351' => 'Portugal',
					'+55'  => 'Brazil',
					'+856' => 'Laos',
					'+228' => 'Togo',
					'+352' => 'Luxembourg',
					'+56'  => 'Chile',
					'+86'  => '(People\'s Rep.) China',
					'+229' => 'Benin',
					'+353' => 'Ireland',
					'+57'  => 'Colombia',
					'+870' => 'Inmarsat SNAC',
					'+230' => 'Mauritius',
					'+354' => 'Iceland',
					'+58'  => 'Venezuela',
					'+871' => 'Inmarsat (Atl-East)',
					'+231' => 'Liberia',
					'+355' => 'Albania',
					'+590' => 'Guadeloupe',
					'+872' => 'Inmarsat (Pacific)',
					'+232' => 'Sierra Leone',
					'+356' => 'Malta',
					'+591' => 'Bolivia',
					'+873' => 'Inmarsat (Indian O.)',
					'+233' => 'Ghana',
					'+357' => 'Cyprus',
					'+592' => 'Guyana',
					'+874' => 'Inmarsat (Atl-West)',
					'+234' => 'Nigeria',
					'+358' => 'Finland',
					'+593' => 'Ecuador',
					'+880' => 'Bangladesh',
					'+235' => 'Chad',
					'+359' => 'Bulgaria',
					'+594' => 'Guiana (French)',
					'+881' => 'Satellite services',
					'+236' => 'Central African Rep.',
					'+36'  => 'Hungary',
					'+595' => 'Paraguay',
					'+886' => 'Taiwan/"reserved"',
					'+237' => 'Cameroon',
					'+370' => 'Lithuania',
					'+596' => 'Martinique',
					'+90'  => 'Turkey',
					'+238' => 'Cape Verde',
					'+371' => 'Latvia',
					'+597' => 'Suriname',
					'+91'  => 'India',
					'+239' => 'S�o Tom� & Princip�',
					'+372' => 'Estonia',
					'+598' => 'Uruguay',
					'+92'  => 'Pakistan',
					'+240' => 'Equatorial Guinea',
					'+373' => 'Moldova',
					'+599' => 'Netherlands Antilles',
					'+93'  => 'Afghanistan',
					'+241' => 'Gabon',
					'+374' => 'Armenia',
					'+60'  => 'Malaysia',
					'+94'  => 'Sri Lanka',
					'+242' => 'Congo',
					'+375' => 'Belarus',
					'+61'  => 'Australia',
					'+95'  => 'Myanmar (Burma)',
					'+243' => 'Zaire',
					'+376' => 'Andorra',
					'+62'  => 'Indonesia',
					'+960' => 'Maldives',
					'+244' => 'Angola',
					'+377' => 'Monaco',
					'+63'  => 'Philippines',
					'+961' => 'Lebanon',
					'+245' => 'Guinea-Bissau',
					'+378' => 'San Marino',
					'+64'  => 'New Zealand',
					'+962' => 'Jordan',
					'+246' => 'Diego Garcia',
					'+379' => 'Vatican City (use +39)',
					'+65'  => 'Singapore',
					'+963' => 'Syria',
					'+247' => 'Ascension',
					'+380' => 'Ukraine',
					'+66'  => 'Thailand',
					'+964' => 'Iraq',
					'+248' => 'Seychelles',
					'+381' => 'Yugoslavia',
					'+670' => 'East Timor',
					'+965' => 'Kuwait',
					'+249' => 'Sudan',
					'+385' => 'Croatia',
					'+966' => 'Saudi Arabia',
					'+250' => 'Rwanda',
					'+386' => 'Slovenia',
					'+672' => 'Australian Ext. Terr.',
					'+967' => 'Yemen',
					'+251' => 'Ethiopia',
					'+387' => 'Bosnia - Herzegovina',
					'+673' => 'Brunei Darussalam',
					'+968' => 'Oman',
					'+252' => 'Somalia',
					'+389' => '(FYR) Macedonia',
					'+674' => 'Nauru',
					'+970' => 'Palestine',
					'+253' => 'Djibouti',
					'+39'  => 'Italy',
					'+675' => 'Papua New Guinea',
					'+971' => 'United Arab Emirates',
					'+254' => 'Kenya',
					'+40'  => 'Romania',
					'+676' => 'Tonga',
					'+972' => 'Israel',
					'+255' => 'Tanzania',
					'+41'  => 'Switzerland, (Liecht.)',
					'+677' => 'Solomon Islands',
					'+973' => 'Bahrain',
					'+256' => 'Uganda',
					'+678' => 'Vanuatu',
					'+974' => 'Qatar',
					'+257' => 'Burundi',
					'+420' => 'Czech Republic',
					'+679' => 'Fiji',
					'+975' => 'Bhutan',
					'+258' => 'Mozambique',
					'+421' => 'Slovakia',
					'+680' => 'Palau',
					'+976' => 'Mongolia',
					'+260' => 'Zambia',
					'+423' => 'Liechtenstein',
					'+681' => 'Wallis and Futuna',
					'+977' => 'Nepal',
					'+261' => 'Madagascar',
					'+43'  => 'Austria',
					'+682' => 'Cook Islands',
					'+98'  => 'Iran',
					'+262' => 'Reunion Island',
					'+44'  => 'United Kingdom',
					'+683' => 'Niue',
					'+992' => 'Tajikistan',
					'+263' => 'Zimbabwe',
					'+45'  => 'Denmark',
					'+684' => 'American Samoa',
					'+993' => 'Turkmenistan',
					'+264' => 'Namibia',
					'+46'  => 'Sweden',
					'+685' => 'Western Samoa',
					'+994' => 'Azerbaijan',
					'+265' => 'Malawi',
					'+47'  => 'Norway',
					'+686' => 'Kiribati',
					'+995' => 'Rep. of Georgia',
					'+266' => 'Lesotho',
					'+48'  => 'Poland',
					'+687' => 'New Caledonia',
					'+996' => 'Kyrgyz Republic',
					'+267' => 'Botswana',
					'+49'  => 'Germany',
					'+688' => 'Tuvalu',
					'+997' => 'Kazakhstan',
					'+268' => 'Swaziland',
					'+500' => 'Falkland Islands',
					'+689' => 'French Polynesia',
					'+998' => 'Uzbekistan',
				)
			);
		}
		
		/**
		 * Function for execute code after selecting service at frontend
		 *
		 * @param  mixed $bookingpress_after_selecting_booking_service_data
		 * @return void
		 */
		function bookingpress_after_selecting_booking_service_func( $bookingpress_after_selecting_booking_service_data ){
			$bookingpress_after_selecting_booking_service_data .= 'let selected_service = vm.appointment_step_form_data.selected_service;';
			$bookingpress_after_selecting_booking_service_data .= 'vm.customer_form_fields.forEach( (element,index) => {
				if( typeof element.field_options != "undefined" && element.field_options.visibility == "services" ){
					let field_services = element.field_options.selected_services;
					if( field_services.indexOf( selected_service.toString() ) < 0 ){
						vm.customer_form_fields[index].is_hide = 1;
					} else {
						vm.customer_form_fields[index].is_hide = 0;
					}
				} else if( element.field_type == "2_col" || element.field_type == "3_col" || element.field_type == "4_col" ){
					let total_inner_fields = element.field_options.inner_fields.length;
					let total_hidden_fields = 0;
					if( total_inner_fields > 0 ){
						element.field_options.inner_fields.forEach( (ielement,iindex) =>{
							if( ielement.is_blank == "true" ){
								total_hidden_fields++;
							} else {
								let field_visibility = ielement.field_options.visibility || "always";
								
								if( "services" == field_visibility ){
									let field_services = ielement.field_options.selected_services;
									if( field_services.indexOf( selected_service.toString() ) < 0 ){
										vm.customer_form_fields[index].field_options.inner_fields[iindex].is_hide = 1;
										total_hidden_fields++;
									} else {
										vm.customer_form_fields[index].field_options.inner_fields[iindex].is_hide = 0;
									}
								} else if( "hidden" == field_visibility ){
									vm.customer_form_fields[index].field_options.inner_fields[iindex].is_hide = 1;
									total_hidden_fields++;
								}
							}
						});
					}
					if( total_hidden_fields >= total_inner_fields ){
						vm.customer_form_fields[index].is_hide = 1;
					} else {
						vm.customer_form_fields[index].is_hide = 0;
					}
				} else if( element.field_name == "Repeater" ){
					let total_inner_fields = element.field_options.inner_fields.length;
					let total_hidden_fields = 0;
					if( total_inner_fields > 0 ){
						element.field_options.inner_fields.forEach( (ielement,iindex) =>{
							if( "2_col" == ielement.field_type || "3_col" == ielement.field_type || "4_col" == ielement.field_type ){
								ielement.field_options.inner_fields.forEach( (ielement,iindex) =>{
									if( ielement.is_blank == "true" ){
										total_hidden_fields++;
									} else {
										let field_visibility = ielement.field_options.visibility || "always";

										if( "services" == field_visibility ){
											let field_services = ielement.field_options.selected_services;
											if( field_services.indexOf( selected_service.toString() ) < 0 ){
												vm.customer_form_fields[index].field_options.inner_fields[iindex].is_hide = 1;
												total_hidden_fields++;
											} else {
												vm.customer_form_fields[index].field_options.inner_fields[iindex].is_hide = 0;
											}
										} else if( "hidden" == field_visibility ){
											vm.customer_form_fields[index].field_options.inner_fields[iindex].is_hide = 1;
											total_hidden_fields++;
										}
									}
								});
							} else {
								if( ielement.is_blank == "true" ){
									total_hidden_fields++;
								} else {
									let field_visibility = ielement.field_options.visibility || "always";
									
									if( "services" == field_visibility ){
										let field_services = ielement.field_options.selected_services;
										if( field_services.indexOf( selected_service.toString() ) < 0 ){
											vm.customer_form_fields[index].field_options.inner_fields[iindex].is_hide = 1;
											total_hidden_fields++;
										} else {
											vm.customer_form_fields[index].field_options.inner_fields[iindex].is_hide = 0;
										}
									} else if( "hidden" == field_visibility ){
										vm.customer_form_fields[index].field_options.inner_fields[iindex].is_hide = 1;
										total_hidden_fields++;
									}
								}
							}
						});
					}
					if( total_hidden_fields >= total_inner_fields ){
						vm.customer_form_fields[index].is_hide = 1;
					} else {
						vm.customer_form_fields[index].is_hide = 0;
					}
				}
				
			});';
			return $bookingpress_after_selecting_booking_service_data;
		}

	}
	global $bookingpress_pro_customize;
	$bookingpress_pro_customize = new bookingpress_pro_customize();
}

