<?php
if ( ! class_exists( 'bookingpress_pro_import_export' ) ) {
	class bookingpress_pro_import_export Extends BookingPress_Core {

		function __construct() {

			if($this->bookingpress_import_export_requirement()){
				
				add_filter('bookingpress_general_settings_add_tab_filter', array($this, 'bookingpress_general_settings_add_tab_filter_func'),20);
				add_action( 'bookingpress_add_debug_log_outside', array($this, 'bookingpress_add_debug_log_outside_api_func'));			
				add_filter('bookingpress_modified_migration_active_module_list',array($this,'bookingpress_modified_migration_active_module_list_func'),10,1);
				add_filter('bookingpress_removed_customize_setting_export_keys',array($this,'bookingpress_removed_customize_setting_export_func'),10,1);
	
				/* Export Data Modified... */
				add_filter('bookingpress_modified_export_data_result',array($this,'bookingpress_modified_export_data_result_func'),10,5);
				add_filter('bookingpress_modified_export_total_records',array($this,'bookingpress_modified_export_total_records_func'),10,3);
	
				/* Export Pro Data */
				add_action('bookingpress_add_extra_export_data',array($this,'bookingpress_add_extra_export_data_func'),10,2);
	
				add_filter('bookingpress_add_wordpress_default_option_data',array($this,'bookingpress_add_wordpress_default_option_data_func'),10,2);
	
				add_filter('bookingpress_modified_import_data_process',array($this,'bookingpress_modified_import_data_process_func'),10,8);
	
				add_filter('bookingpress_modified_export_list',array($this,'bookingpress_modified_export_list_func'),10,1);

				add_filter('bookingpress_modified_addon_list_for_import_export',array($this,'bookingpress_modified_addon_list_for_import_export_func'),10,1);

			}

		}

		function bookingpress_modified_addon_list_for_import_export_func($all_addon_name_list){

            $bookingpress_all_module_and_addon_list = array(                
                'bookingpress_pro' => esc_html__( 'BookingPress Pro', 'bookingpress-appointment-booking' ),
                'staffmember_module' => esc_html__( 'Staff Member', 'bookingpress-appointment-booking' ),
                'multiple_quantity_module' => esc_html__( 'Multiple Quantity', 'bookingpress-appointment-booking' ),
                'deposit_payment_module' => esc_html__( 'Deposit Payment', 'bookingpress-appointment-booking' ),
                'coupons_module' => esc_html__( 'Coupon Management', 'bookingpress-appointment-booking' ),
                'service_extra_module' => esc_html__( 'Service Extra', 'bookingpress-appointment-booking' ),
                'multilanguage_addon' => esc_html__( 'Multi-Language', 'bookingpress-appointment-booking' ),
                'package_addon' => esc_html__( 'Service Package', 'bookingpress-appointment-booking' ),
                'location_addon' => esc_html__( 'Location', 'bookingpress-appointment-booking' ),
                'recurring_addon' => esc_html__( 'Recurring Appointments', 'bookingpress-appointment-booking' ),
                'tip_addon' => esc_html__( 'Tip', 'bookingpress-appointment-booking' ),                
                'discount_addon' => esc_html__( 'Advanced Discount', 'bookingpress-appointment-booking' ),
                'cart_addon' => esc_html__( 'Cart', 'bookingpress-appointment-booking' ),
                'waiting_list_addon' => esc_html__( 'Waiting List', 'bookingpress-appointment-booking' ),
                'sms_addon' => esc_html__( 'SMS', 'bookingpress-appointment-booking' ),
                'whatsapp_addon' => esc_html__( 'WhatsApp', 'bookingpress-appointment-booking' ),
                'custom_service_duration_addon' => esc_html__( 'Custom Service Duration', 'bookingpress-appointment-booking' ),
                'happy_hours_addon' => esc_html__( 'Happy Hours', 'bookingpress-appointment-booking' ),
                'tax_addon' => esc_html__( 'Tax', 'bookingpress-appointment-booking' ),
                'invoice_addon' => esc_html__( 'Invoice', 'bookingpress-appointment-booking' ),
                'google_calendar_addon' => esc_html__( 'Google Calendar', 'bookingpress-appointment-booking' ),
                'outlook_calendar_addon' => esc_html__( 'Outlook Calendar', 'bookingpress-appointment-booking' ),
                'zapier_addon' => esc_html__( 'Zapier', 'bookingpress-appointment-booking' ), 
                'mailchimp_addon' => esc_html__( 'Mailchimp', 'bookingpress-appointment-booking' ),
                'aweber_addon' => esc_html__( 'Aweber', 'bookingpress-appointment-booking' ),                
                'whatsapp_addon' => esc_html__( 'WhatsApp', 'bookingpress-appointment-booking' ),
                'conversion_tracking_addon' => esc_html__( 'Conversion Tracking', 'bookingpress-appointment-booking' ),
                'mobile_connect_addon' => esc_html__( 'Mobile Connect', 'bookingpress-appointment-booking' ),
		'bookingpress_ratings_and_review' => esc_html__( 'Rating & Review Addon', 'bookingpress-appointment-booking' ),
		'gift_card_addon' => esc_html__( 'Gift Card', 'bookingpress-appointment-booking' ),		
            );

			$all_addon_name_list = array_merge($all_addon_name_list,$bookingpress_all_module_and_addon_list);

			return $all_addon_name_list;
		}


        /**
         * Function for check migration tool addon requirement
         *
         * @return void
        */
        function bookingpress_import_export_requirement(){
            global $bookingpress_pro_version;
            $migration_tool_working = true;
            $bookingpress_version = get_option('bookingpress_version', true);
            if(is_plugin_active('bookingpress-appointment-booking/bookingpress-appointment-booking.php') && version_compare($bookingpress_version, '1.1', '>=')) {
                $migration_tool_working = true;
            }else{
                $migration_tool_working = false;
            }
            return $migration_tool_working;            
        } 

		function bookingpress_modified_export_list_func($bookingpress_export_list){
			global $bookingpress_import_export;
			$bookingpress_active_plugin_module_list = $bookingpress_import_export->bookingpress_active_plugin_module_list();

            if($bookingpress_active_plugin_module_list['staffmember_module']){
                if($bookingpress_active_plugin_module_list['location_addon']){
                    $staff_total_records = $bookingpress_import_export->export_item_total_records("staff_members");
                    $total_records = $bookingpress_import_export->export_item_total_records("location");
                    $bookingpress_export_list['services']['total_record'] = $bookingpress_export_list['services']['total_record'] + $staff_total_records + $total_records;
                    $bookingpress_export_list['services']['name'] = esc_html__('Services, Staff Members & Location','bookingpress-appointment-booking');
                }else{
                    $staff_total_records = $bookingpress_import_export->export_item_total_records("staff_members");                    
                    $bookingpress_export_list['services']['total_record'] = $bookingpress_export_list['services']['total_record'] + $staff_total_records;                    
                    $bookingpress_export_list['services']['name'] = esc_html__('Services & Staff Members','bookingpress-appointment-booking');
                }
            }
            if($bookingpress_active_plugin_module_list['package_addon']){
                $total_records = $bookingpress_import_export->export_item_total_records("packages");
                $bookingpress_export_list['packages'] = array('name'=>esc_html__('Packages','bookingpress-appointment-booking'),'related'=>array('services'),'child'=>array(),'required_parent'=>1,'total_record'=>$total_records);

                $bookingpress_export_list['appointments']['name'] = esc_html__('Appointments & Package Order','bookingpress-appointment-booking');
                $bookingpress_export_list['appointments']['related'][] = 'packages';                
            }                        
            if($bookingpress_active_plugin_module_list['multilanguage_addon']){
                $total_records = $bookingpress_import_export->export_item_total_records("multi_language_data");
                $bookingpress_export_list['multi_language_data'] = array('name'=>esc_html__('Multi Language Data','bookingpress-appointment-booking'),'related'=>array(),'child'=>array(),'required_parent'=>1,'total_record'=>$total_records);
            }

			return $bookingpress_export_list;

		}

        /**
         * Function for update customization settings
         *
        */
        public function bookingpress_update_multi_language_settings( $bookingpress_element_type, $bookingpress_ref_column_name, $bookingpress_element_ref_id = 0,$bookingpress_language_code = '',$bookingpress_translated_value = '' ){

            global $wpdb, $tbl_bookingpress_ml_translation;
            $bookingpress_check_record_existance = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_translation_id FROM `{$tbl_bookingpress_ml_translation}` WHERE bookingpress_element_type = %s AND bookingpress_ref_column_name = %s AND bookingpress_language_code = %s AND bookingpress_element_ref_id = %s", $bookingpress_element_type, $bookingpress_ref_column_name,$bookingpress_language_code,$bookingpress_element_ref_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_ml_translation is table name defined globally. False Positive alarm

            $bookingpress_check_record_existance = ($bookingpress_check_record_existance == 0 || $bookingpress_check_record_existance = '')?0:$bookingpress_check_record_existance;
            if($bookingpress_check_record_existance > 0){  
                /* If record not exists then update data. */              
                $bookingpress_update_data = array(
                    'bookingpress_translated_value'  => $bookingpress_translated_value,
                );
                $bpa_update_where_condition = array(
                    'bookingpress_translation_id' => $bookingpress_check_record_existance,
                );
                $bpa_update_affected_rows = $wpdb->update($tbl_bookingpress_ml_translation, $bookingpress_update_data, $bpa_update_where_condition);
                if ($bpa_update_affected_rows > 0 ) {
                    return 1;
                }
            }else{
                /* If record not exists then insert data. */
                $bookingpress_insert_data = array(
                    'bookingpress_element_type'       => $bookingpress_element_type,
                    'bookingpress_ref_column_name'    => $bookingpress_ref_column_name,
                    'bookingpress_language_code'      => $bookingpress_language_code,
                    'bookingpress_element_ref_id'     => $bookingpress_element_ref_id,
                    'bookingpress_translated_value'   => $bookingpress_translated_value,
                );
                $bookingpress_inserted_id = $wpdb->insert($tbl_bookingpress_ml_translation, $bookingpress_insert_data);
                if ($bookingpress_inserted_id > 0 ) {
                    return 1;
                }                
            }
            return 0;
        }

        /**
         * Function for existing staff members data reset
        */
        function bookingpress_reset_staff_membersdata($exists_staffmember_id){
			global $bookingpress_import_export;
            if($exists_staffmember_id != 0 && $exists_staffmember_id != ""){
                global $wpdb,$tbl_bookingpress_staffmembers_meta,$tbl_bookingpress_staff_member_workhours,$tbl_bookingpress_staffmembers_special_day,$tbl_bookingpress_staffmembers_daysoff,$tbl_bookingpress_locations_staff_workhours,$tbl_bookingpress_locations_staff_special_days;
                if(!empty($tbl_bookingpress_staffmembers_meta) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_staffmembers_meta)){
                    $wpdb->delete( $tbl_bookingpress_staffmembers_meta, array( 'bookingpress_staffmember_id' => $exists_staffmember_id ) );
                }
                if(!empty($tbl_bookingpress_staff_member_workhours) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_staff_member_workhours)){
                    $wpdb->delete( $tbl_bookingpress_staff_member_workhours, array( 'bookingpress_staffmember_id' => $exists_staffmember_id ) );
                } 
                if(!empty($tbl_bookingpress_staffmembers_special_day) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_staffmembers_special_day)){
                    $wpdb->delete( $tbl_bookingpress_staffmembers_special_day, array( 'bookingpress_staffmember_id' => $exists_staffmember_id ) );
                }  
                if(!empty($tbl_bookingpress_staffmembers_daysoff) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_staffmembers_daysoff)){
                    $wpdb->delete( $tbl_bookingpress_staffmembers_daysoff, array( 'bookingpress_staffmember_id' => $exists_staffmember_id ) );
                }
                if(!empty($tbl_bookingpress_locations_staff_workhours) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_locations_staff_workhours)){
                    $wpdb->delete( $tbl_bookingpress_locations_staff_workhours, array( 'bookingpress_staffmember_id' => $exists_staffmember_id ) );
                }
                if(!empty($tbl_bookingpress_locations_staff_special_days) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_locations_staff_special_days)){
                    $wpdb->delete( $tbl_bookingpress_locations_staff_special_days, array( 'bookingpress_staffmember_id' => $exists_staffmember_id ) );
                }                
            }
        }		

        /**
         * Create WP User If Not Exists
         *
        */
        function bookingpress_create_wp_user($staff_user_detail,$wp_user_meta='',$type = 'staff_members'){   
            global $BookingPress,$wpdb,$bookingpress_pro_staff_members;         
            if(!empty($staff_user_detail)){                                                   
                $user_email = $staff_user_detail['data']['user_email'];
                $username = $staff_user_detail['data']['user_login'];
                $password = $staff_user_detail['data']['user_pass'];               
                if(!empty($password)){
                    $password = stripslashes_deep($password);
                }
                $user = get_user_by('email', $user_email);
                if(empty($user)) {                    
                    $display_name = $staff_user_detail['data']['display_name'];
                    $user_nicename = $staff_user_detail['data']['user_nicename'];

                    if ( username_exists( $username ) ) {
                        $username = $user_email;
                    }
                    $user_data = array(
                        'user_login'    => $username, 
                        'user_pass'     => $password,
                        'user_email'    => $user_email, 
                        'display_name'  => $display_name,
                        'user_nicename'  => $user_nicename,
                    );
                    $user_id = wp_insert_user($user_data);
                    if($user_id){

                        $wpdb->update(
                            $wpdb->users,
                            array( 'user_pass' => $password),
                            array( 'ID' => $user_id )
                        );
                        $user = get_user_by('id', $user_id);
                        if($type == 'staff_members'){
                            if($user){
                                $user->add_role('bookingpress-staffmember');  
                                $user->remove_role('bookingpress-customer'); 
								$bookingpress_pro_staff_members->bookingpress_staffmember_assign_capability( $user_id );   
                            }
                        }else{
                            if($user){
                                $user->add_role('bookingpress-customer');  
                            }
                        }
                        if(isset($wp_user_meta['first_name'])){
                            $first_name = $wp_user_meta['first_name'];
                            add_user_meta($user_id,'first_name',$first_name);
                        }
                        if(isset($wp_user_meta['last_name'])){
                            $last_name = $wp_user_meta['last_name'];                    
                            add_user_meta($user_id,'last_name',$last_name);    
                        }    
                    }
                    return $user_id;
                }else{                    
                    if($user){
                        if($type == 'staff_members'){
                            $user->add_role('bookingpress-staffmember');
                            $user->remove_role('bookingpress-customer');
							$bookingpress_pro_staff_members->bookingpress_staffmember_assign_capability($user->ID); 
                        }else{
                            $user->add_role('bookingpress-customer');
                        }
                        return $user->ID;
                    }
                }
            }
            return 0;
        }

        function bookingpress_modified_import_data_process_func($bookingpress_import_detail_type_data,$bookingpress_import_data,$detail_import_detail_type,$detail_import_last_record,$limit,$export_key,$import_id,$detail_import_total_record){

			global $wpdb,$bookingpress_import_export;

			$is_complete = 0;
			$total_imported = 0;
            if($detail_import_detail_type == 'guests_data' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){

					global $tbl_bookingpress_guests_data;
					$limit = 100;
					$total_imported = 0;
					if(!empty($tbl_bookingpress_guests_data) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_guests_data)){
						if($detail_import_last_record == 0){
							$wpdb->query("TRUNCATE TABLE $tbl_bookingpress_guests_data"); // phpcs:ignore
						}
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_guests_data);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										$single_import_record[$key] = sanitize_text_field($import_data_v);
									}
								}
								if(!empty($single_import_record)){
									$wpdb->insert($tbl_bookingpress_guests_data, $single_import_record);
									$last_import_id = $wpdb->insert_id;                                                
								} 
								$total_imported++;                                           
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}

					}
                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'appointments' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){


					global $tbl_bookingpress_appointment_bookings,$tbl_bookingpress_entries,$tbl_bookingpress_payment_logs,$tbl_bookingpress_entries_meta,$tbl_bookingpress_appointment_meta;
					$limit = 40;
					if(!empty($tbl_bookingpress_appointment_bookings) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_appointment_bookings) && !empty($tbl_bookingpress_entries) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_entries) && !empty($tbl_bookingpress_payment_logs) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_payment_logs)){
						$bookingpress_all_upload_fields = array();
						global $tbl_bookingpress_form_fields;
						if(!empty($tbl_bookingpress_appointment_meta) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_appointment_meta)){
							$all_file_fields = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_type = %s ORDER BY bookingpress_field_position ASC", 'file'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm                                        
							if(!empty($all_file_fields)){
								foreach($all_file_fields as $al_field){
									$bookingpress_all_upload_fields[] = $al_field['bookingpress_field_meta_key'];
								}
							}
						}
						$get_all_payment_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_payment_logs);
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_appointment_bookings);
						$get_all_entry_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_entries);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;
										}else{
											$single_import_record[$key] = $import_data_v;
										}     
									}
								}
								if(!empty($single_import_record)){

									//if(isset($single_import_record['bookingpress_customer_id'])){
										$bookingpress_customer_id = $bookingpress_import_export->bookingpress_get_record_rel('customers',$export_key,$import_id,$single_import_record['bookingpress_customer_id']);
										$single_import_record['bookingpress_customer_id'] = $bookingpress_customer_id;
									//}
									if(isset($single_import_record['bookingpress_staff_member_id']) && $single_import_record['bookingpress_staff_member_id'] != 0){
										$bookingpress_staff_member_id = $bookingpress_import_export->bookingpress_get_record_rel('staff_members',$export_key,$import_id,$single_import_record['bookingpress_staff_member_id']);
										$single_import_record['bookingpress_staff_member_id'] = $bookingpress_staff_member_id;
									}
									//if(isset($single_import_record['bookingpress_service_id'])){
										$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$single_import_record['bookingpress_service_id']);
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
									//}
									if(isset($single_import_record['bookingpress_staff_member_details']) && !empty($single_import_record['bookingpress_staff_member_details'])){
										$bookingpress_staff_member_details = json_decode($single_import_record['bookingpress_staff_member_details'],true);
										if(!empty($bookingpress_staff_member_details) && is_array($bookingpress_staff_member_details)){
											if(isset($bookingpress_staff_member_details['bookingpress_staffmember_id'])){
												$bookingpress_staff_member_details['bookingpress_staffmember_id'] = $bookingpress_staff_member_id;
											}
											if(isset($bookingpress_staff_member_details['bookingpress_wpuser_id'])){
												$bookingpress_wpuser_id = $bookingpress_staff_member_details['bookingpress_wpuser_id'];
												$bookingpress_staff_member_id = $bookingpress_import_export->bookingpress_get_record_rel('staff_members_user',$export_key,$import_id,$bookingpress_wpuser_id);
												$bookingpress_staff_member_details['bookingpress_wpuser_id'] = $bookingpress_staff_member_id;
											}
											$single_import_record['bookingpress_staff_member_details'] = json_encode($bookingpress_staff_member_details,true);
										}
									}
									/*
									if(isset($single_import_record['bookingpress_package_id']) && !empty($single_import_record['bookingpress_package_id']) && $single_import_record['bookingpress_package_id'] != 0){                                                    
										$bookingpress_package_id = intval($single_import_record['bookingpress_package_id']);
										$single_import_record['bookingpress_package_id'] = $this->bookingpress_get_record_rel('packages',$export_key,$import_id,$bookingpress_package_id);
									}
									*/
									if(isset($single_import_record['bookingpress_applied_package_data']) && !empty($single_import_record['bookingpress_applied_package_data'])){
										$bookingpress_applied_package_data = json_decode($single_import_record['bookingpress_applied_package_data'],true);
										if(!empty($bookingpress_applied_package_data) && is_array($bookingpress_applied_package_data)){
											if(isset($bookingpress_applied_package_data['bookingpress_package_id'])){
												$bookingpress_package_id = intval($bookingpress_applied_package_data['bookingpress_package_id']);
												$bookingpress_applied_package_data['bookingpress_package_id'] = $bookingpress_import_export->bookingpress_get_record_rel('packages',$export_key,$import_id,$bookingpress_package_id);
											}
											if(isset($bookingpress_applied_package_data['bookingpress_login_user_id'])){
												$bookingpress_login_user_id = intval($bookingpress_applied_package_data['bookingpress_login_user_id']);
												$bookingpress_applied_package_data['bookingpress_login_user_id'] = $bookingpress_import_export->bookingpress_get_record_rel('customer_wp_user',$export_key,$import_id,$bookingpress_login_user_id);
											}
											if(isset($bookingpress_applied_package_data['bookingpress_service_id'])){
												$bookingpress_service_id = intval($bookingpress_applied_package_data['bookingpress_service_id']);
												$bookingpress_applied_package_data['bookingpress_service_id'] = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$bookingpress_service_id);
											}
										}
										$single_import_record['bookingpress_applied_package_data'] = json_encode($bookingpress_applied_package_data,true);
									}
									if(isset($single_import_record['bookingpress_coupon_details']) && !empty($single_import_record['bookingpress_coupon_details'])){
										$bookingpress_coupon_details = json_decode($single_import_record['bookingpress_coupon_details'],true);
										if(!empty($bookingpress_coupon_details) && is_array($bookingpress_coupon_details)){
											if(isset($bookingpress_coupon_details['bookingpress_coupon_id'])){
												$bookingpress_coupon_id = $bookingpress_import_export->bookingpress_get_record_rel('coupon',$export_key,$import_id,$bookingpress_coupon_details['bookingpress_coupon_id']);
												$bookingpress_coupon_details['bookingpress_coupon_id'] = $bookingpress_coupon_id;
											}
											if(isset($bookingpress_coupon_details['bookingpress_coupon_services']) && $bookingpress_coupon_details['bookingpress_coupon_services'] != '' && $bookingpress_coupon_details['bookingpress_coupon_services'] != 0){                      
												$bookingpress_coupon_services = $bookingpress_coupon_details['bookingpress_coupon_services'];
												$bookingpress_coupon_services_arr = explode(",",$bookingpress_coupon_services);
												$final_service_arr = array();
												if(!empty($bookingpress_coupon_services_arr)){
													foreach($bookingpress_coupon_services_arr as $serid){
														$final_service_arr[] = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$serid);
													}                                                            
												}
												if(!empty($final_service_arr)){
													$bookingpress_coupon_services['bookingpress_coupon_services'] = implode(',',$final_service_arr);
												}
											}
											$single_import_record['bookingpress_coupon_details'] = json_encode($bookingpress_coupon_details,true);
										}
									}
									if(isset($single_import_record['bookingpress_extra_service_details']) && !empty($single_import_record['bookingpress_extra_service_details'])){
										$bookingpress_extra_service_details = json_decode($single_import_record['bookingpress_extra_service_details'],true);
										if(is_array($bookingpress_extra_service_details) && !empty($bookingpress_extra_service_details)){
											foreach($bookingpress_extra_service_details as $key=>$servextra){
												if(isset($bookingpress_extra_service_details[$key]['bookingpress_extra_service_details']['bookingpress_extra_services_id']) && !empty($bookingpress_extra_service_details[$key]['bookingpress_extra_service_details']['bookingpress_extra_services_id'])){
													$bookingpress_extra_service_details[$key]['bookingpress_extra_service_details']['bookingpress_extra_services_id'] = $bookingpress_import_export->bookingpress_get_record_rel('extra_services',$export_key,$import_id,$bookingpress_extra_service_details[$key]['bookingpress_extra_service_details']['bookingpress_extra_services_id']);
													$bookingpress_extra_service_details[$key]['bookingpress_extra_service_details']['bookingpress_service_id'] = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$bookingpress_extra_service_details[$key]['bookingpress_extra_service_details']['bookingpress_service_id']);
												}
											}
											$single_import_record['bookingpress_extra_service_details'] = json_encode($bookingpress_extra_service_details,true);
										}
									}
									if(isset($single_import_record['bookingpress_offer_payment_discount_detail']) && !empty($single_import_record['bookingpress_offer_payment_discount_detail'])){
										$bookingpress_offer_payment_discount_detail = json_decode($single_import_record['bookingpress_offer_payment_discount_detail'],true);
										if(is_array($bookingpress_offer_payment_discount_detail) && !empty($bookingpress_offer_payment_discount_detail)){
												if(isset($bookingpress_offer_payment_discount_detail['bookingpress_discount_id']) && !empty($bookingpress_offer_payment_discount_detail['bookingpress_discount_id'])){
													$bookingpress_discount_id = intval($bookingpress_offer_payment_discount_detail['bookingpress_discount_id']);
													$bookingpress_offer_payment_discount_detail['bookingpress_discount_id'] = $bookingpress_import_export->bookingpress_get_record_rel('advanced_discount',$export_key,$import_id,$bookingpress_discount_id);
												}
											
											$single_import_record['bookingpress_extra_service_details'] = json_encode($bookingpress_extra_service_details,true);
										}
									}
									if(isset($single_import_record['bookingpress_location_id']) && !empty($single_import_record['bookingpress_location_id'])){
										$bookingpress_location_id = $bookingpress_import_export->bookingpress_get_record_rel('location',$export_key,$import_id,$single_import_record['bookingpress_location_id']);
										$single_import_record['bookingpress_location_id'] = $bookingpress_location_id;
									}

									


									$single_import_record = apply_filters( 'bookingpress_modified_import_appointment_data',$single_import_record,$bookingpress_import_data,$detail_import_detail_type,$export_key,$import_id);
									$appointment_insert_data = $single_import_record;

									$wpdb->insert($tbl_bookingpress_appointment_bookings, $single_import_record);
									$last_import_id = $wpdb->insert_id;
									
									if(!empty($tbl_bookingpress_appointment_meta) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_appointment_meta)){                                                    
										$appointmentbooking_metadata = (isset($import_record_data[$i]['meta_data']))?$import_record_data[$i]['meta_data']:'';
										if(!empty($appointmentbooking_metadata)){                                                        
											$get_all_appointment_meta_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_appointment_meta);
											foreach($appointmentbooking_metadata as $booking_meta){
												unset($booking_meta['bookingpress_appointment_meta_id']);
												unset($booking_meta['bookingpress_appointment_meta_created_date']);
												$bookingpress_package_meta_col = array();
												foreach($booking_meta as $mmkey=>$metaval ){
													if(in_array($mmkey,$get_all_appointment_meta_table_columns)){
														$import_data_v = $metaval;
														$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$mmkey);
														if($import_data_v == 'null' || is_null($import_data_v)){
															$bookingpress_package_meta_col[$mmkey] = NULL;
														}else{
															$bookingpress_package_meta_col[$mmkey] = $import_data_v;
														}     
													}
												}                                                            
												if(!empty($bookingpress_package_meta_col)){
													$bookingpress_appointment_meta_key = (isset($bookingpress_package_meta_col['bookingpress_appointment_meta_key']))?$bookingpress_package_meta_col['bookingpress_appointment_meta_key']:'';
													$bookingpress_appointment_meta_value = (isset($bookingpress_package_meta_col['bookingpress_appointment_meta_value']))?$bookingpress_package_meta_col['bookingpress_appointment_meta_value']:'';
													$bookingpress_appointment_meta_id = (isset($bookingpress_package_meta_col['bookingpress_appointment_meta_id']))?$bookingpress_package_meta_col['bookingpress_appointment_meta_id']:'';
													
													if($bookingpress_appointment_meta_key == 'bookingpress_happy_hour_data' && !empty($bookingpress_appointment_meta_value)){
														$bookingpress_appointment_meta_value_arr = json_decode($bookingpress_appointment_meta_value,true);
														if(!empty($bookingpress_appointment_meta_value_arr) && is_array($bookingpress_appointment_meta_value_arr) && isset($bookingpress_appointment_meta_value_arr['bookingpress_service_id'])){
															$happy_bookingpress_service_id = $bookingpress_appointment_meta_value_arr['bookingpress_service_id'];                                                                        
															$bookingpress_appointment_meta_value_arr['bookingpress_service_id'] = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$happy_bookingpress_service_id);
															$bookingpress_package_meta_col['bookingpress_appointment_meta_value'] = json_encode($bookingpress_appointment_meta_value_arr,true);
														}
													}                                                                
													$bookingpress_all_file_upload_list = array();
													if(!empty($bookingpress_all_upload_fields) && ($bookingpress_appointment_meta_key == 'appointment_form_fields_data' || $bookingpress_appointment_meta_key == 'appointment_details')){
														if($bookingpress_appointment_meta_key == 'appointment_details' || $bookingpress_appointment_meta_key == 'appointment_form_fields_data'){
															if(!empty($bookingpress_appointment_meta_value)){
																$bookingpress_appointment_meta_value_arr = json_decode($bookingpress_appointment_meta_value,true);
																if(!empty($bookingpress_appointment_meta_value_arr) && is_array($bookingpress_appointment_meta_value_arr) && isset($bookingpress_appointment_meta_value_arr['form_fields']) && !empty($bookingpress_appointment_meta_value_arr['form_fields'])){
																	$form_fields = $bookingpress_appointment_meta_value_arr['form_fields'];
																	if(is_array($form_fields)){
																		foreach($form_fields as $field_key=>$field_val){
																			if(in_array($field_key,$bookingpress_all_upload_fields) && !empty($field_val)){
																				$file_name = basename($field_val);
																				$bookingpress_all_file_upload_list[] = array(
																					'import_field' => $field_key,
																					'import_image_name' => $file_name,
																					'import_image_url' => $field_val,
																					'import_table_id' => $bookingpress_appointment_meta_id,
																				);  
																				$bookingpress_appointment_meta_value_arr['form_fields'][$field_key] = $bookingpress_import_export->bookingpress_new_file_url($file_name);
																			}
																		}
																	}
																	$bookingpress_package_meta_col['bookingpress_appointment_meta_value'] = json_encode($bookingpress_appointment_meta_value_arr,true);
																}
															}
														}
													}

													$bookingpress_package_meta_col = apply_filters( 'bookingpress_modified_import_appointment_meta_data',$bookingpress_package_meta_col,$bookingpress_import_data,$detail_import_detail_type,$appointment_insert_data,$export_key,$import_id);

													$wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_package_meta_col);
													$metadata_last_import_id = $wpdb->insert_id;
													if(!empty($bookingpress_all_file_upload_list)){
														foreach($bookingpress_all_file_upload_list as $atatchval){
															$bookingpress_import_export->bookingpress_set_attachment_records($tbl_bookingpress_appointment_meta,$atatchval['import_field'],$metadata_last_import_id,$import_id,$atatchval['import_image_name'],$atatchval['import_image_url'],$export_key);
														}
													}

												}                                                            
											}
										}
									}
									$appointment_payment_data = (isset($import_record_data[$i]['payment_data']))?$import_record_data[$i]['payment_data']:'';
									if(!empty($appointment_payment_data) && is_array($appointment_payment_data)){
										
										$bookingpress_payment_log_id = (isset($appointment_payment_data['bookingpress_payment_log_id']))?$appointment_payment_data['bookingpress_payment_log_id']:0;

										$bookingpress_check_record_existance = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_payment_log_id FROM `{$tbl_bookingpress_payment_logs}` WHERE 	bookingpress_payment_log_id = %d", $bookingpress_payment_log_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm 
										
										$bookingpress_check_record_existance = ($bookingpress_check_record_existance == 0 || $bookingpress_check_record_existance = '')?0:$bookingpress_check_record_existance;

										if($bookingpress_check_record_existance == 0){

											$appoitment_payment_records = array();
											foreach($appointment_payment_data as $key=>$value){                                                        
												if(in_array($key,$get_all_payment_table_columns)){
													$import_data_v = $value;
													$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
													if($import_data_v == 'null' || is_null($import_data_v)){
														$appoitment_payment_records[$key] = NULL;
													}else{
														$appoitment_payment_records[$key] = $import_data_v;
													}     
												}
											}                                  
											if(isset($appoitment_payment_records['bookingpress_customer_id'])){
												$appoitment_payment_records['bookingpress_customer_id'] = $single_import_record['bookingpress_customer_id']; 
											}
											if(isset($appoitment_payment_records['bookingpress_staffmember_id'])){
												$appoitment_payment_records['bookingpress_staffmember_id'] = $single_import_record['bookingpress_staffmember_id']; 
											}
											if(isset($appoitment_payment_records['bookingpress_service_id'])){
												$appoitment_payment_records['bookingpress_service_id'] = $single_import_record['bookingpress_service_id']; 
											}                                                      
											if(isset($appoitment_payment_records['bookingpress_staff_member_details'])){
												$appoitment_payment_records['bookingpress_staff_member_details'] = $single_import_record['bookingpress_staff_member_details']; 
											}
											if(isset($appoitment_payment_records['bookingpress_package_id'])){
												$appoitment_payment_records['bookingpress_package_id'] = $single_import_record['bookingpress_package_id']; 
											} 
											if(isset($appoitment_payment_records['bookingpress_applied_package_data'])){
												$appoitment_payment_records['bookingpress_applied_package_data'] = $single_import_record['bookingpress_applied_package_data']; 
											} 
											if(isset($appoitment_payment_records['bookingpress_coupon_details'])){
												$appoitment_payment_records['bookingpress_coupon_details'] = $single_import_record['bookingpress_coupon_details']; 
											} 
											if(isset($appoitment_payment_records['bookingpress_extra_service_details'])){
												$appoitment_payment_records['bookingpress_extra_service_details'] = $single_import_record['bookingpress_extra_service_details']; 
											} 
											if(isset($appoitment_payment_records['bookingpress_location_id'])){
												$appoitment_payment_records['bookingpress_location_id'] = $single_import_record['bookingpress_location_id']; 
											} 
											if(isset($appoitment_payment_records['bookingpress_offer_payment_discount_detail'])){
												$appoitment_payment_records['bookingpress_offer_payment_discount_detail'] = $single_import_record['bookingpress_offer_payment_discount_detail'];
											}                             
											
											$appoitment_payment_records = apply_filters( 'bookingpress_modified_import_payment_data',$appoitment_payment_records,$bookingpress_import_data,$detail_import_detail_type,$appointment_insert_data,$export_key,$import_id);

											$wpdb->insert($tbl_bookingpress_payment_logs, $appoitment_payment_records);
										}
									}

									$appointment_entry_data = (isset($import_record_data[$i]['entry_data']))?$import_record_data[$i]['entry_data']:'';
									if(!empty($appointment_entry_data) && is_array($appointment_entry_data)){

										$appointment_entry_record = array();
										foreach($appointment_entry_data as $key=>$value){                                                       
											if(in_array($key,$get_all_entry_table_columns)){
												$import_data_v = $value;
												$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
												if($import_data_v == 'null' || is_null($import_data_v)){
													$appointment_entry_record[$key] = NULL;
												}else{
													$appointment_entry_record[$key] = $import_data_v;
												}     
											}
										}          

										if(isset($appointment_entry_record['bookingpress_customer_id'])){
											$appointment_entry_record['bookingpress_customer_id'] = $single_import_record['bookingpress_customer_id']; 
										}
										if(isset($appointment_entry_record['bookingpress_package_id'])){
											$appointment_entry_record['bookingpress_package_id'] = $single_import_record['bookingpress_package_id']; 
										} 
										if(isset($appointment_entry_record['bookingpress_staff_member_id'])){
											$appointment_entry_record['bookingpress_staff_member_id'] = $single_import_record['bookingpress_staff_member_id']; 
										}
										if(isset($appointment_entry_record['bookingpress_service_id'])){
											$appointment_entry_record['bookingpress_service_id'] = $single_import_record['bookingpress_service_id']; 
										}
										if(isset($appointment_entry_record['bookingpress_staff_member_details'])){
											$appointment_entry_record['bookingpress_staff_member_details'] = $single_import_record['bookingpress_staff_member_details']; 
										}
										if(isset($appointment_entry_record['bookingpress_applied_package_data'])){
											$appointment_entry_record['bookingpress_applied_package_data'] = $single_import_record['bookingpress_applied_package_data']; 
										}  
										if(isset($appointment_entry_record['bookingpress_coupon_details'])){
											$appointment_entry_record['bookingpress_coupon_details'] = $single_import_record['bookingpress_coupon_details']; 
										}
										if(isset($appointment_entry_record['bookingpress_location_id'])){
											$appointment_entry_record['bookingpress_location_id'] = $single_import_record['bookingpress_location_id']; 
										}
										if(isset($appointment_entry_record['bookingpress_offer_payment_discount_detail'])){
											$appointment_entry_record['bookingpress_offer_payment_discount_detail'] = $single_import_record['bookingpress_offer_payment_discount_detail']; 
										}                   
										
										$appointment_entry_record = apply_filters( 'bookingpress_modified_import_entries_data',$appointment_entry_record,$bookingpress_import_data,$detail_import_detail_type,$appointment_insert_data,$export_key,$import_id);

										$wpdb->insert($tbl_bookingpress_entries, $appointment_entry_record);

										if(isset($appointment_entry_data['meta_data']) && !empty($appointment_entry_data['meta_data'])){
											if(!empty($tbl_bookingpress_entries_meta) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_entries_meta)){
												$get_all_entry_meta_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_entries_meta);
												foreach($appointment_entry_data['meta_data'] as $mkey=>$mvalue){
													$import_entrie_meta_data = array();
													if(!empty($mvalue)){
														foreach($mvalue as $mk=>$mv){
															if(in_array($mk,$get_all_entry_meta_table_columns)){
																$import_data_v = $mv;
																$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
																if($import_data_v == 'null' || is_null($import_data_v)){
																	$import_entrie_meta_data[$mk] = NULL;
																}else{
																	$import_entrie_meta_data[$mk] = $import_data_v;
																} 
															}
														}
														if(!empty($import_entrie_meta_data)){
															$wpdb->insert($tbl_bookingpress_entries_meta, $import_entrie_meta_data);
														}
													}
												}
											}
										}
									}                                                                                          
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                              
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                    


					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}					

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }			
            if($detail_import_detail_type == 'package_order' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){


					global $tbl_bookingpress_package_bookings,$tbl_bookingpress_entries,$tbl_bookingpress_payment_logs,$tbl_bookingpress_entries_meta,$tbl_bookingpress_package_bookings_meta;
					$limit = 20;
					if(!empty($tbl_bookingpress_package_bookings) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_package_bookings) && !empty($tbl_bookingpress_entries) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_entries) && !empty($tbl_bookingpress_payment_logs) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_payment_logs) && !empty($tbl_bookingpress_entries_meta) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_entries_meta)){
						
						$get_all_package_meta_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_package_bookings_meta);
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_package_bookings);
						$get_all_entry_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_entries);
						$get_all_payment_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_payment_logs);

						$bookingpress_all_upload_fields = array();
						global $tbl_bookingpress_form_fields;
						if(!empty($tbl_bookingpress_package_bookings_meta) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_package_bookings_meta)){
							$all_file_fields = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_type = %s ORDER BY bookingpress_field_position ASC", 'file'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
							if(!empty($all_file_fields)){
								foreach($all_file_fields as $al_field){
									$bookingpress_all_upload_fields[] = $al_field['bookingpress_field_meta_key'];
								}
							}
						}                                    

						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;
										}else{
											$single_import_record[$key] = $import_data_v;
										}     
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_customer_id = $bookingpress_import_export->bookingpress_get_record_rel('customers',$export_key,$import_id,$import_record_data[$i]['bookingpress_customer_id']);
									$bookingpress_login_user_id = $bookingpress_import_export->bookingpress_get_record_rel('customer_wp_user',$export_key,$import_id,$import_record_data[$i]['bookingpress_login_user_id']);
									$bookingpress_package_id = $bookingpress_import_export->bookingpress_get_record_rel('packages',$export_key,$import_id,$import_record_data[$i]['bookingpress_package_id']);
									
									$single_import_record['bookingpress_customer_id'] = $bookingpress_customer_id;
									$single_import_record['bookingpress_login_user_id'] = $bookingpress_login_user_id;
									$single_import_record['bookingpress_package_id'] = $bookingpress_package_id;

									

									$bookingpress_package_services = (isset($single_import_record['bookingpress_package_services']))?$single_import_record['bookingpress_package_services']:'';
									if(!empty($bookingpress_package_services)){                                                    
										$bookingpress_package_services = json_decode($bookingpress_package_services,true);
										if(is_array($bookingpress_package_services)){
											foreach($bookingpress_package_services as $key=>$value){
												if(isset($bookingpress_package_services[$key]['bookingpress_package_id'])){
													$bookingpress_package_services[$key]['bookingpress_package_id'] = $bookingpress_package_id;
												}
												if(isset($bookingpress_package_services[$key]['bookingpress_service_id'])){
													$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$bookingpress_package_services[$key]['bookingpress_service_id']);
													if($bookingpress_service_id != '' && $bookingpress_service_id != 0){
														$bookingpress_package_services[$key]['bookingpress_service_id'] = $bookingpress_service_id;
													}                                                                
												}                                                            
											}
											$single_import_record['bookingpress_package_services'] = json_encode($bookingpress_package_services,true);
										}
									}

									$wpdb->insert($tbl_bookingpress_package_bookings, $single_import_record);
									$last_import_id = $wpdb->insert_id;

									if(!empty($tbl_bookingpress_package_bookings_meta) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_package_bookings_meta)){
										
										$package_booking_meta_data = (isset($import_record_data[$i]['meta_data']))?$import_record_data[$i]['meta_data']:'';
										if(!empty($package_booking_meta_data)){
											foreach($package_booking_meta_data as $booking_meta){
												unset($booking_meta['bookingpress_package_bookings_meta_id']);
												unset($booking_meta['bookingpress_package_meta_created_date']);
												//$get_all_package_meta_table_columns
												$bookingpress_package_meta_col = array();
												foreach($booking_meta as $mmkey=>$metaval ){
													if(in_array($mmkey,$get_all_package_meta_table_columns)){
														$import_data_v = $metaval;
														$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$mmkey);
														if($import_data_v == 'null' || is_null($import_data_v)){
															$bookingpress_package_meta_col[$mmkey] = NULL;
														}else{
															$bookingpress_package_meta_col[$mmkey] = $import_data_v;
														}     
													}

												}
												if(!empty($bookingpress_package_meta_col)){

													$bookingpress_package_meta_key = $bookingpress_package_meta_col['bookingpress_package_meta_key'];
													$bookingpress_package_meta_value = $bookingpress_package_meta_col['bookingpress_package_meta_value'];
													$bookingpress_all_file_upload_list = array();
													if(!empty($bookingpress_all_upload_fields) && $bookingpress_package_meta_key == 'package_form_fields_data'){   
														if(!empty($bookingpress_package_meta_value)){
															$bookingpress_package_meta_value_arr = json_decode($bookingpress_package_meta_value,true);
															if(!empty($bookingpress_package_meta_value_arr) && is_array($bookingpress_package_meta_value_arr) && isset($bookingpress_package_meta_value_arr['form_fields']) && !empty($bookingpress_package_meta_value_arr['form_fields'])){
																$form_fields = $bookingpress_package_meta_value_arr['form_fields'];
																if(is_array($form_fields)){
																	foreach($form_fields as $field_key=>$field_val){
																		if(in_array($field_key,$bookingpress_all_upload_fields) && !empty($field_val)){
																			$file_name = basename($field_val);
																			$bookingpress_all_file_upload_list[] = array(
																				'import_field' => $field_key,
																				'import_image_name' => $file_name,
																				'import_image_url' => $field_val,
																				'import_table_id' => 0,
																			);  
																			$bookingpress_package_meta_value_arr['form_fields'][$field_key] = $bookingpress_import_export->bookingpress_new_file_url($file_name);
																		}
																	}
																}
																$bookingpress_package_meta_col['bookingpress_package_meta_value'] = json_encode($bookingpress_package_meta_value_arr,true);
															}
														}    
													}
													$wpdb->insert($tbl_bookingpress_package_bookings_meta, $bookingpress_package_meta_col);
													$metadata_last_import_id = $wpdb->insert_id;
													if(!empty($bookingpress_all_file_upload_list)){
														foreach($bookingpress_all_file_upload_list as $atatchval){
															$bookingpress_import_export->bookingpress_set_attachment_records($tbl_bookingpress_package_bookings_meta,$atatchval['import_field'],$metadata_last_import_id,$import_id,$atatchval['import_image_name'],$atatchval['import_image_url'],$export_key);
														}
													}                                                                

												}                                                            
											}
										}
									}

									$package_booking_meta_data = (isset($import_record_data[$i]['payment_data']))?$import_record_data[$i]['payment_data']:'';
									if(!empty($package_booking_meta_data) && is_array($package_booking_meta_data)){
										
										$package_payment_record = array();
										foreach($package_booking_meta_data as $key=>$value){                                                        
											if(in_array($key,$get_all_payment_table_columns)){
												$import_data_v = $value;
												$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
												if($import_data_v == 'null' || is_null($import_data_v)){
													$package_payment_record[$key] = NULL;
												}else{
													$package_payment_record[$key] = $import_data_v;
												}     
											}
										}                                  
										if(isset($package_payment_record['bookingpress_customer_id'])){
											$package_payment_record['bookingpress_customer_id'] = $single_import_record['bookingpress_customer_id']; 
										}
										if(isset($package_payment_record['bookingpress_package_id'])){
											$package_payment_record['bookingpress_package_id'] = $single_import_record['bookingpress_package_id']; 
										}  
										$wpdb->insert($tbl_bookingpress_payment_logs, $package_payment_record);
									} 

									$package_booking_entry_data = (isset($import_record_data[$i]['entry_data']))?$import_record_data[$i]['entry_data']:'';
									if(!empty($package_booking_entry_data) && is_array($package_booking_entry_data)){
										
										$package_entry_record = array();
										foreach($package_booking_entry_data as $key=>$value){                                                        
											if(in_array($key,$get_all_entry_table_columns)){
												$import_data_v = $value;
												$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
												if($import_data_v == 'null' || is_null($import_data_v)){
													$package_entry_record[$key] = NULL;
												}else{
													$package_entry_record[$key] = $import_data_v;
												}     
											}
										}          
										if(isset($package_entry_record['bookingpress_package_details']) && !empty($package_entry_record['bookingpress_package_details'])){
											$bookingpress_package_details = json_decode($package_entry_record['bookingpress_package_details'],true);
											if(is_array($bookingpress_package_details) && !empty($bookingpress_package_details) && isset($bookingpress_package_details['bookingpress_package_id'])){
												$bookingpress_package_details['bookingpress_package_id'] = $single_import_record['bookingpress_package_id'];
												$package_entry_record['bookingpress_package_details'] = json_encode($bookingpress_package_details,true);
											}
										}
										if(isset($package_entry_record['bookingpress_customer_id'])){
											$package_entry_record['bookingpress_customer_id'] = $package_entry_record['bookingpress_customer_id']; 
										}
										if(isset($package_entry_record['bookingpress_package_id'])){
											$package_entry_record['bookingpress_package_id'] = $package_entry_record['bookingpress_package_id']; 
										}  
										$wpdb->insert($tbl_bookingpress_entries, $package_entry_record);
										if(isset($package_booking_entry_data['meta_data']) && !empty($package_booking_entry_data['meta_data'])){
											$get_all_entry_meta_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_entries_meta);
											foreach($package_booking_entry_data['meta_data'] as $mkey=>$mvalue){
												$import_entrie_meta_data = array();
												if(!empty($mvalue)){
													foreach($mvalue as $mk=>$mv){
														if(in_array($mk,$get_all_entry_meta_table_columns)){
															$import_data_v = $mv;
															$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
															if($import_data_v == 'null' || is_null($import_data_v)){
																$import_entrie_meta_data[$mk] = NULL;
															}else{
																$import_entrie_meta_data[$mk] = $import_data_v;
															} 
														}
													}
													if(!empty($import_entrie_meta_data)){
														$wpdb->insert($tbl_bookingpress_entries_meta, $import_entrie_meta_data);
													}
												}
											}
										}
									}                                           
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                              
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'multi_language_data' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
					
					global $tbl_bookingpress_ml_translation;
					$limit = 20; 
					if(!empty($tbl_bookingpress_ml_translation) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_ml_translation)){
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$bookingpress_element_type  = (isset($import_record_data[$i]['bookingpress_element_type']))?sanitize_text_field($import_record_data[$i]['bookingpress_element_type']):'';
								$bookingpress_ref_column_name = (isset($import_record_data[$i]['bookingpress_ref_column_name']))?$import_record_data[$i]['bookingpress_ref_column_name']:'';
								$bookingpress_element_ref_id  = (isset($import_record_data[$i]['bookingpress_element_ref_id']))?sanitize_text_field($import_record_data[$i]['bookingpress_element_ref_id']):'';
								$bookingpress_language_code  = (isset($import_record_data[$i]['bookingpress_language_code']))?sanitize_text_field($import_record_data[$i]['bookingpress_language_code']):'';
								$bookingpress_translated_value  = (isset($import_record_data[$i]['bookingpress_translated_value']))?$import_record_data[$i]['bookingpress_translated_value']:'';                                                                                        
								$bookingpress_translated_value = $bookingpress_import_export->bookingpress_import_value_modified($bookingpress_translated_value,$detail_import_detail_type,'bookingpress_translated_value');
								$check_bookingpress_element_ref_id = intval($bookingpress_element_ref_id);

								if($check_bookingpress_element_ref_id != 0){
									$check_bookingpress_element_type = $bookingpress_element_type;
									if($check_bookingpress_element_type == 'happy_hours'){
										$check_bookingpress_element_type = 'service';
									}
									$bookingpress_element_ref_id = $bookingpress_import_export->bookingpress_get_record_language_rel($check_bookingpress_element_type,$export_key,$import_id,$bookingpress_element_ref_id);
									if(!empty($bookingpress_element_ref_id) && $bookingpress_element_ref_id != 0){
										$this->bookingpress_update_multi_language_settings($bookingpress_element_type, $bookingpress_ref_column_name, $bookingpress_element_ref_id,$bookingpress_language_code,$bookingpress_translated_value);
									}                                                
								}else{
									$this->bookingpress_update_multi_language_settings($bookingpress_element_type, $bookingpress_ref_column_name, $bookingpress_element_ref_id,$bookingpress_language_code,$bookingpress_translated_value);
								}                                                                                        
								$total_imported++;
							}
						}
						$total_imported = $total_imported + $detail_import_last_record;
						if($total_record <= $total_imported){
							$is_complete = 1;
						}
					}else{
						$not_import_data_reason = esc_html__('No Record Found.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;                                    
					}					

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }			
            if($detail_import_detail_type == 'package_images' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){

					global $tbl_bookingpress_package_images;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_package_images) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_package_images)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_package_images);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){

									$bookingpress_package_id = $bookingpress_import_export->bookingpress_get_record_rel('packages',$export_key,$import_id,$import_record_data[$i]['bookingpress_package_id']);                                                                                                
									if($bookingpress_package_id != '' && $bookingpress_package_id != 0){                                                    
																							
										$single_import_record['bookingpress_package_id'] = $bookingpress_package_id;                                                                                                       
										unset($single_import_record['bookingpress_package_service_id']);
										unset($single_import_record['bookingpress_package_service_created_date']);                                                    
										
										$file_name = $single_import_record['bookingpress_package_img_name'];
										$file_url = $single_import_record['bookingpress_package_img_url'];

										$bookingpress_import_export->bookingpress_set_attachment_records($tbl_bookingpress_package_images,'package_images',$bookingpress_package_id,$import_id,$file_name,$file_url,$export_key);
										$last_import_id = $wpdb->insert_id;                                                    

									}

								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 


                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'package_services' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){

					global $tbl_bookingpress_package_services;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_package_services) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_package_services)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_package_services);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_package_id = $bookingpress_import_export->bookingpress_get_record_rel('packages',$export_key,$import_id,$import_record_data[$i]['bookingpress_package_id']);
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									
									if($bookingpress_service_id != '' && $bookingpress_service_id != 0 && $bookingpress_package_id != '' && $bookingpress_package_id != 0){                                                    
										
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										$single_import_record['bookingpress_package_id'] = $bookingpress_package_id;                                                                                                       
										unset($single_import_record['bookingpress_package_service_id']);
										unset($single_import_record['bookingpress_package_service_created_date']);                                                    
										$wpdb->insert($tbl_bookingpress_package_services, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                    

									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 					


                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }			
            if($detail_import_detail_type == 'packages' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){

					global $tbl_bookingpress_packages;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_packages) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_packages)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_packages);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						$bookingpress_package_position = $bookingpress_import_export->bookingpress_get_max_rec_position_func($tbl_bookingpress_packages,'bookingpress_package_position');
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];                                                  
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_package_position++;
									$old_id = $import_record_data[$i]['bookingpress_package_id']; 
									$single_import_record['bookingpress_package_position'] = $bookingpress_package_position;                                                

									unset($single_import_record['bookingpress_package_id']);
									unset($single_import_record['bookingpress_package_created_date']);

									$wpdb->insert($tbl_bookingpress_packages, $single_import_record);
									$last_import_id = $wpdb->insert_id;
									$bookingpress_import_export->bookingpress_set_record_rel('packages',$export_key,$import_id,$old_id,$last_import_id,'package');
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}
                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'locations_staff_workhours' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){

					global $tbl_bookingpress_locations_staff_workhours;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_locations_staff_workhours) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_locations_staff_workhours)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_locations_staff_workhours);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_location_id = $bookingpress_import_export->bookingpress_get_record_rel('location',$export_key,$import_id,$import_record_data[$i]['bookingpress_location_id']);
									$bookingpress_staffmember_id = $bookingpress_import_export->bookingpress_get_record_rel('staff_members',$export_key,$import_id,$import_record_data[$i]['bookingpress_staffmember_id']);
									if($bookingpress_staffmember_id != '' && $bookingpress_staffmember_id != 0 && $bookingpress_location_id != '' && $bookingpress_location_id != 0){
										$single_import_record['bookingpress_staffmember_id'] = $bookingpress_staffmember_id;
										$single_import_record['bookingpress_location_id'] = $bookingpress_location_id;

										unset($single_import_record['bookingpress_location_staff_workhour_id']);

										$wpdb->insert($tbl_bookingpress_locations_staff_workhours, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                 
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 					

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }				
            if($detail_import_detail_type == 'locations_staff_special_days' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
					
					global $tbl_bookingpress_locations_staff_special_days;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_locations_staff_special_days) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_locations_staff_special_days)){

						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_locations_staff_special_days);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_location_id = $bookingpress_import_export->bookingpress_get_record_rel('location',$export_key,$import_id,$import_record_data[$i]['bookingpress_location_id']);
									$bookingpress_staffmember_id = $bookingpress_import_export->bookingpress_get_record_rel('staff_members',$export_key,$import_id,$import_record_data[$i]['bookingpress_staffmember_id']);
									if($bookingpress_staffmember_id != '' && $bookingpress_staffmember_id != 0 && $bookingpress_location_id != '' && $bookingpress_location_id != 0){                                                    
										
										$single_import_record['bookingpress_staffmember_id'] = $bookingpress_staffmember_id;
										$single_import_record['bookingpress_location_id'] = $bookingpress_location_id;
										
										$old_id = $single_import_record['bookingpress_location_staff_special_day_id'];
										unset($single_import_record['bookingpress_location_staff_special_day_id']);

										$wpdb->insert($tbl_bookingpress_locations_staff_special_days, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                    

									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 				

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'locations_service_workhours' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_locations_service_workhours;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_locations_service_workhours) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_locations_service_workhours)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_locations_service_workhours);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_location_id = $bookingpress_import_export->bookingpress_get_record_rel('location',$export_key,$import_id,$import_record_data[$i]['bookingpress_location_id']);
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									if($bookingpress_service_id != '' && $bookingpress_service_id != 0 && $bookingpress_location_id != '' && $bookingpress_location_id != 0){
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										$single_import_record['bookingpress_location_id'] = $bookingpress_location_id;

										unset($single_import_record['bookingpress_location_service_workhour_id']);

										$wpdb->insert($tbl_bookingpress_locations_service_workhours, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                 
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$is_complete = 2;
					}


                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'locations_service_staff_pricing_details' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_locations_service_staff_pricing_details;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_locations_service_staff_pricing_details) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_locations_service_staff_pricing_details)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_locations_service_staff_pricing_details);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_location_id = $bookingpress_import_export->bookingpress_get_record_rel('location',$export_key,$import_id,$import_record_data[$i]['bookingpress_location_id']);
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									$bookingpress_staffmember_id = $bookingpress_import_export->bookingpress_get_record_rel('staff_members',$export_key,$import_id,$import_record_data[$i]['bookingpress_staffmember_id']);

									if($bookingpress_service_id != '' && $bookingpress_service_id != 0 && $bookingpress_location_id != '' && $bookingpress_location_id != 0){                                                    
										
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										$single_import_record['bookingpress_location_id'] = $bookingpress_location_id;
										$single_import_record['bookingpress_staffmember_id'] = $bookingpress_staffmember_id;

										$old_id = $single_import_record['bookingpress_service_staff_pricing_id'];
										unset($single_import_record['bookingpress_service_staff_pricing_id']);
										unset($single_import_record['bookingpress_location_created_date']);
										
										$wpdb->insert($tbl_bookingpress_locations_service_staff_pricing_details, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                    

									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}
            }
            if($detail_import_detail_type == 'locations_service_special_days' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_locations_service_special_days;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_locations_service_special_days) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_locations_service_special_days)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_locations_service_special_days);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_location_id = $bookingpress_import_export->bookingpress_get_record_rel('location',$export_key,$import_id,$import_record_data[$i]['bookingpress_location_id']);
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									if($bookingpress_service_id != '' && $bookingpress_service_id != 0 && $bookingpress_location_id != '' && $bookingpress_location_id != 0){                                                    
										
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										$single_import_record['bookingpress_location_id'] = $bookingpress_location_id;
										$old_id = $single_import_record['bookingpress_location_service_special_day_id'];

										unset($single_import_record['bookingpress_location_service_special_day_id']);
										
										$wpdb->insert($tbl_bookingpress_locations_service_special_days, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                    

									}                                                
								} 
								$total_imported++;                                  
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}  

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'location' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_locations;
					$limit = 10;                            
					if(!empty($tbl_bookingpress_locations) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_locations)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_locations);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						$bookingpress_location_position = $bookingpress_import_export->bookingpress_get_max_rec_position_func($tbl_bookingpress_locations,'bookingpress_location_position');
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];                                                  
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){

									$bookingpress_location_position++;
									$old_id = $import_record_data[$i]['bookingpress_location_id'];                                                 
									$single_import_record['bookingpress_location_position'] = $bookingpress_location_position;
									
									$file_name = $single_import_record['bookingpress_location_img_name'];
									$file_url = $single_import_record['bookingpress_location_img_url']; 

									$single_import_record['bookingpress_location_img_name'] = '';
									$single_import_record['bookingpress_location_img_url'] = '';
									unset($single_import_record['bookingpress_location_id']);

									$wpdb->insert($tbl_bookingpress_locations, $single_import_record);
									$last_import_id = $wpdb->insert_id;
									$bookingpress_import_export->bookingpress_set_record_rel('location',$export_key,$import_id,$old_id,$last_import_id,'location');
									if(!empty($file_name) && !empty($file_url)){                                                    
										$bookingpress_import_export->bookingpress_set_attachment_records($tbl_bookingpress_locations,'bookingpress_location_id',$last_import_id,$import_id,$file_name,$file_url,$export_key);
									}
								}
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}  

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'custom_staffmembers_service_durations' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_custom_staffmembers_service_durations;
					$limit = 20;                            
					if(!empty($tbl_bookingpress_custom_staffmembers_service_durations) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_custom_staffmembers_service_durations)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_custom_staffmembers_service_durations);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_staffmember_id = $bookingpress_import_export->bookingpress_get_record_rel('staff_members',$export_key,$import_id,$import_record_data[$i]['bookingpress_staffmember_id']);
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									$bookingpress_custom_service_duration_id = $bookingpress_import_export->bookingpress_get_record_rel('custom_service_durations',$export_key,$import_id,$import_record_data[$i]['bookingpress_custom_service_duration_id']);

									if($bookingpress_staffmember_id != '' && $bookingpress_staffmember_id != 0 && $bookingpress_service_id != '' && $bookingpress_service_id != 0 && $bookingpress_custom_service_duration_id != ""){                                                    
										$single_import_record['bookingpress_staffmember_id'] = $bookingpress_staffmember_id;
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										$single_import_record['bookingpress_custom_service_duration_id'] = $bookingpress_custom_service_duration_id;

										$old_id = $single_import_record['bookingpress_staffmember_duration_id'];
										unset($single_import_record['bookingpress_staffmember_duration_id']);
										unset($single_import_record['bookingpress_staffmember_duration_created_date']);
										$wpdb->insert($tbl_bookingpress_custom_staffmembers_service_durations, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                    
										//$bookingpress_import_export->bookingpress_set_record_rel('staffmembers_services',$export_key,$import_id,$old_id,$last_import_id);
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'staffmembers_services' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_staffmembers_services;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_staffmembers_services) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_staffmembers_services)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_staffmembers_services);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_staffmember_id = $bookingpress_import_export->bookingpress_get_record_rel('staff_members',$export_key,$import_id,$import_record_data[$i]['bookingpress_staffmember_id']);
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									if($bookingpress_staffmember_id != '' && $bookingpress_staffmember_id != 0 && $bookingpress_service_id != '' && $bookingpress_service_id != 0){                                                    
										$single_import_record['bookingpress_staffmember_id'] = $bookingpress_staffmember_id;
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										$old_id = $single_import_record['bookingpress_staffmember_service_id'];
										unset($single_import_record['bookingpress_staffmember_service_id']);
										unset($single_import_record['bookingpress_created_date']);
										$wpdb->insert($tbl_bookingpress_staffmembers_services, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                    
										//$bookingpress_import_export->bookingpress_set_record_rel('staffmembers_services',$export_key,$import_id,$old_id,$last_import_id);
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}  

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'staffmembers_daysoff' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_staffmembers_daysoff;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_staffmembers_daysoff) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_staffmembers_daysoff)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_staffmembers_daysoff);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_staffmember_id = $bookingpress_import_export->bookingpress_get_record_rel('staff_members',$export_key,$import_id,$import_record_data[$i]['bookingpress_staffmember_id']);
									if($bookingpress_staffmember_id != '' && $bookingpress_staffmember_id != 0){                                                   
										$single_import_record['bookingpress_staffmember_id'] = $bookingpress_staffmember_id;
										$old_id = $single_import_record['bookingpress_staffmember_daysoff_id'];                                                    
										unset($single_import_record['bookingpress_staffmember_daysoff_id']);
										unset($single_import_record['bookingpress_staffmember_daysoff_created']);
										if($single_import_record['bookingpress_staffmember_daysoff_parent'] != 0){
											$single_import_record['bookingpress_staffmember_daysoff_parent'] = $bookingpress_import_export->bookingpress_get_record_rel('staffmembers_daysoff',$export_key,$import_id,$import_record_data[$i]['bookingpress_staffmember_daysoff_parent']);
										}                                                                                                        
										$wpdb->insert($tbl_bookingpress_staffmembers_daysoff, $single_import_record);
										$last_import_id = $wpdb->insert_id;
										if($single_import_record['bookingpress_staffmember_daysoff_parent'] == 0){    
											$bookingpress_import_export->bookingpress_set_record_rel('staffmembers_daysoff',$export_key,$import_id,$old_id,$last_import_id);
										}
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}					

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'staffmembers_special_day_breaks' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_staffmembers_special_day_breaks;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_staffmembers_special_day_breaks) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_staffmembers_special_day_breaks)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_staffmembers_special_day_breaks);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_special_day_id = $bookingpress_import_export->bookingpress_get_record_rel('staffmembers_special_day',$export_key,$import_id,$import_record_data[$i]['bookingpress_special_day_id']);
									if($bookingpress_special_day_id != '' && $bookingpress_special_day_id != 0){                                                    
										$single_import_record['bookingpress_special_day_id'] = $bookingpress_special_day_id;
										unset($single_import_record['bookingpress_staffmember_special_day_break_id']);
										$wpdb->insert($tbl_bookingpress_staffmembers_special_day_breaks, $single_import_record);
										$last_import_id = $wpdb->insert_id;
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}
                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'staffmembers_special_day' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										

					global $tbl_bookingpress_staffmembers_special_day;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_staffmembers_special_day) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_staffmembers_special_day)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_staffmembers_special_day);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_staffmember_id = $bookingpress_import_export->bookingpress_get_record_rel('staff_members',$export_key,$import_id,$import_record_data[$i]['bookingpress_staffmember_id']);
									if($bookingpress_staffmember_id != '' && $bookingpress_staffmember_id != 0){                                                    
										$single_import_record['bookingpress_staffmember_id'] = $bookingpress_staffmember_id;
										$old_id = $single_import_record['bookingpress_staffmember_special_day_id'];
										unset($single_import_record['bookingpress_staffmember_special_day_id']);
										
										$wpdb->insert($tbl_bookingpress_staffmembers_special_day, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                    
										$bookingpress_import_export->bookingpress_set_record_rel('staffmembers_special_day',$export_key,$import_id,$old_id,$last_import_id);
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}					

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'staff_member_workhours' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										

					global $tbl_bookingpress_staff_member_workhours;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_staff_member_workhours) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_staff_member_workhours)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_staff_member_workhours);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_staffmember_id = $bookingpress_import_export->bookingpress_get_record_rel('staff_members',$export_key,$import_id,$import_record_data[$i]['bookingpress_staffmember_id']);
									if($bookingpress_staffmember_id != '' && $bookingpress_staffmember_id != 0){                                                    
										$single_import_record['bookingpress_staffmember_id'] = $bookingpress_staffmember_id;
										unset($single_import_record['bookingpress_staffmember_workhours_id']);
										unset($single_import_record['bookingpress_staffmember_workhours_created_at']);
										$wpdb->insert($tbl_bookingpress_staff_member_workhours, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                 
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}				

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }			
            if($detail_import_detail_type == 'staffmembers_meta' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										

					global $tbl_bookingpress_staffmembers_meta;
					$limit = 30;                            
					if(!empty($tbl_bookingpress_staffmembers_meta) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_staffmembers_meta)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_staffmembers_meta);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null'){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){  
									
									$bookingpress_servicemeta_value = $single_import_record['bookingpress_staffmembermeta_value'];
									if($single_import_record['bookingpress_staffmembermeta_key'] == 'staffmember_avatar_details'){
										$single_import_record['bookingpress_staffmembermeta_value'] = '';                                                        
									}
									$old_id = $single_import_record['bookingpress_staffmembermeta_id'];                                                 
									$bookingpress_staffmember_id = $bookingpress_import_export->bookingpress_get_record_rel('staff_members',$export_key,$import_id,$import_record_data[$i]['bookingpress_staffmember_id']);
									if($bookingpress_staffmember_id != '' && $bookingpress_staffmember_id != 0){                                                    
										$single_import_record['bookingpress_staffmember_id'] = $bookingpress_staffmember_id;
										unset($single_import_record['bookingpress_staffmembermeta_id']);

										$wpdb->insert($tbl_bookingpress_staffmembers_meta, $single_import_record);
										$last_import_id = $wpdb->insert_id;
										if($single_import_record['bookingpress_staffmembermeta_key'] == 'staffmember_avatar_details' && !empty($bookingpress_servicemeta_value)){
											$bookingpress_servicemeta_value = unserialize($bookingpress_servicemeta_value);
											if(isset($bookingpress_servicemeta_value[0]['name']) && isset($bookingpress_servicemeta_value[0]['url'])){
												$file_name = $bookingpress_servicemeta_value[0]['name'];
												$file_url = $bookingpress_servicemeta_value[0]['url'];
												$bookingpress_import_export->bookingpress_set_attachment_records($tbl_bookingpress_staffmembers_meta,'bookingpress_staffmembermeta_id',$last_import_id,$import_id,$file_name,$file_url,$export_key);
											}
										}                                                    

									}                                                
								} 
								$total_imported++;                                          
							}
						}                                   
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}				

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'staff_members' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										

					global $tbl_bookingpress_staffmembers;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_staffmembers) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_staffmembers)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_staffmembers);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						$bookingpress_staffmember_position = $bookingpress_import_export->bookingpress_get_max_rec_position_func($tbl_bookingpress_staffmembers,'bookingpress_staffmember_position');

						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];                                                  
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}																
								if(!empty($single_import_record)){
									$bookingpress_staffmember_position++;
									$old_id = $import_record_data[$i]['bookingpress_staffmember_id']; 
									$single_import_record['bookingpress_staffmember_position'] = $bookingpress_staffmember_position;
									
									unset($single_import_record['bookingpress_staffmember_id']);
									unset($single_import_record['bookingpress_staffmember_created']);									

									$bookingpress_staffmember_email = (isset($single_import_record['bookingpress_staffmember_email']))?$single_import_record['bookingpress_staffmember_email']:'';                                                
									$exists_staffmember_id = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_staffmember_id FROM `{$tbl_bookingpress_staffmembers}` Where bookingpress_staffmember_email = %s and bookingpress_staffmember_status <> 4",$bookingpress_staffmember_email));  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is table name.

									$single_import_record = apply_filters( 'bookingpress_modified_staff_import_single_record',$single_import_record);

									if($exists_staffmember_id == 0 || empty($exists_staffmember_id)){
										$staff_user_detail = $import_record_data[$i]['wp_user'];
										if(!empty($staff_user_detail)){
											$wp_user_meta = $import_record_data[$i]['wp_user_meta'];
											$user_id = $this->bookingpress_create_wp_user($staff_user_detail,$wp_user_meta,'staff_members');
											$single_import_record['bookingpress_wpuser_id'] = $user_id;
										}else{
											$single_import_record['bookingpress_wpuser_id'] = 0;
										}
										unset($single_import_record['wp_user']);
										unset($single_import_record['wp_user_meta']);
										$wpdb->insert($tbl_bookingpress_staffmembers, $single_import_record);
										$last_import_id = $wpdb->insert_id;
									}else{
										$this->bookingpress_reset_staff_membersdata($exists_staffmember_id);
										$last_import_id = $exists_staffmember_id;
									}
									$bookingpress_import_export->bookingpress_set_record_rel('staff_members',$export_key,$import_id,$old_id,$last_import_id,'staff_members');
									if(isset($single_import_record['bookingpress_wpuser_id']) && $single_import_record['bookingpress_wpuser_id'] != 0){
										$bookingpress_import_export->bookingpress_set_record_rel('staff_members_user',$export_key,$import_id,$old_id,$single_import_record['bookingpress_wpuser_id'],'');
									}
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}				

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'custom_service_durations' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										

					global $tbl_bookingpress_custom_service_durations;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_custom_service_durations) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_custom_service_durations)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_custom_service_durations);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){                                                
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									if($bookingpress_service_id != '' && $bookingpress_service_id != 0){                                                    
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										$old_id = $single_import_record['bookingpress_custom_service_duration_id'];
										unset($single_import_record['bookingpress_custom_service_duration_id']);
										unset($single_import_record['bookingpress_custom_duration_created_date']);
										$wpdb->insert($tbl_bookingpress_custom_service_durations, $single_import_record);
										$last_import_id = $wpdb->insert_id;
										$bookingpress_import_export->bookingpress_set_record_rel('custom_service_durations',$export_key,$import_id,$old_id,$last_import_id);
									}                                                                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 			

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'advanced_discount' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										

					global $tbl_bookingpress_advanced_discount;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_advanced_discount) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_advanced_discount)){
						if($detail_import_last_record == 0){
							$wpdb->query("TRUNCATE TABLE $tbl_bookingpress_advanced_discount"); // phpcs:ignore
						}                                    
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_advanced_discount);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									if($single_import_record['bookingpress_discount_services'] != '' && $single_import_record['bookingpress_discount_services'] != 0){                      
										$bookingpress_discount_services = $single_import_record['bookingpress_discount_services'];
										$bookingpress_discount_services_arr = explode(",",$bookingpress_discount_services);
										$final_service_arr = array();
										if(!empty($bookingpress_discount_services_arr)){
											foreach($bookingpress_discount_services_arr as $serid){
												$final_service_arr[] = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$serid);
											}                                                            
										}
										if(!empty($final_service_arr)){
											$single_import_record['bookingpress_discount_services'] = implode(',',$final_service_arr);
										}
									}
									$old_id = $single_import_record['bookingpress_discount_id'];
									unset($single_import_record['bookingpress_discount_id']);
									unset($single_import_record['bookingpress_created']);
									$wpdb->insert($tbl_bookingpress_advanced_discount, $single_import_record);
									$last_import_id = $wpdb->insert_id;
									$bookingpress_import_export->bookingpress_set_record_rel('advanced_discount',$export_key,$import_id,$old_id,$last_import_id);
																					
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 			

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'coupon' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_coupons;
					$limit = 50;                        
					if(!empty($tbl_bookingpress_coupons) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_coupons)){

						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_coupons);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									if($single_import_record['bookingpress_coupon_services'] != '' && $single_import_record['bookingpress_coupon_services'] != 0){  

										$bookingpress_coupon_services = $single_import_record['bookingpress_coupon_services'];
										$bookingpress_coupon_services_arr = explode(",",$bookingpress_coupon_services);
										$final_service_arr = array();
										if(!empty($bookingpress_coupon_services_arr)){
											foreach($bookingpress_coupon_services_arr as $serid){
												$final_service_arr[] = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$serid);
											}                                                            
										}
										if(!empty($final_service_arr)){
											$single_import_record['bookingpress_coupon_services'] = implode(',',$final_service_arr);
										}

									}
									$old_id = $single_import_record['bookingpress_coupon_id'];
									unset($single_import_record['bookingpress_coupon_id']);
									unset($single_import_record['bookingpress_created']);

									$coupon_code = (isset($single_import_record['bookingpress_coupon_code']))?$single_import_record['bookingpress_coupon_code']:'';

									$coupon_exist = $wpdb->get_var( $wpdb->prepare( "SELECT bookingpress_coupon_id as total FROM {$tbl_bookingpress_coupons} WHERE bookingpress_coupon_code = %s", $coupon_code ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_coupons is a table name. false alarm
									if ( $coupon_exist > 0 ) {										
										$wpdb->delete( $tbl_bookingpress_coupons, array( 'bookingpress_coupon_id' => $coupon_exist ) );
										$wpdb->insert($tbl_bookingpress_coupons, $single_import_record);
										$last_import_id = $wpdb->insert_id;
									}else{
										$wpdb->insert($tbl_bookingpress_coupons, $single_import_record);
										$last_import_id = $wpdb->insert_id;    
									}
									$bookingpress_import_export->bookingpress_set_record_rel('coupon',$export_key,$import_id,$old_id,$last_import_id);
																					
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 			

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'happy_hours_service' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_happy_hours_service;
					$limit = 50;                           
					if(!empty($tbl_bookingpress_happy_hours_service) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_happy_hours_service)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_happy_hours_service);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									if($bookingpress_service_id != '' && $bookingpress_service_id != 0){                                                    
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										$old_id = $single_import_record['bookingpress_happy_hour_id'];
										unset($single_import_record['bookingpress_happy_hour_id']);
										unset($single_import_record['bookingpress_happy_hour_created_date']);
										$wpdb->insert($tbl_bookingpress_happy_hours_service, $single_import_record);
										$last_import_id = $wpdb->insert_id;
										$bookingpress_import_export->bookingpress_set_record_rel('happy_hours_service',$export_key,$import_id,$old_id,$last_import_id);
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}			

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'extra_services' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_extra_services;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_extra_services) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_extra_services)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_extra_services);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									if($bookingpress_service_id != '' && $bookingpress_service_id != 0){                                                    
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										$old_id = $single_import_record['bookingpress_extra_services_id'];
										unset($single_import_record['bookingpress_extra_services_id']);
										unset($single_import_record['bookingpress_extra_service_created_date']);
										$wpdb->insert($tbl_bookingpress_extra_services, $single_import_record);
										$last_import_id = $wpdb->insert_id;
										$bookingpress_import_export->bookingpress_set_record_rel('extra_services',$export_key,$import_id,$old_id,$last_import_id,'service_extra');
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 			

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'service_daysoff' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_service_daysoff;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_service_daysoff) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_service_daysoff)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_service_daysoff);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									if($bookingpress_service_id != '' && $bookingpress_service_id != 0){                                                    
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										$old_id = $single_import_record['bookingpress_service_daysoff_id'];
										unset($single_import_record['bookingpress_service_daysoff_id']);
										if($single_import_record['bookingpress_service_daysoff_parent'] != 0){
											$single_import_record['bookingpress_service_daysoff_parent'] = $bookingpress_import_export->bookingpress_get_record_rel('service_daysoff',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_daysoff_parent']);
										}
										$wpdb->insert($tbl_bookingpress_service_daysoff, $single_import_record);
										$last_import_id = $wpdb->insert_id;
										if($single_import_record['bookingpress_service_daysoff_parent'] == 0){                                                    
											$bookingpress_import_export->bookingpress_set_record_rel('service_daysoff',$export_key,$import_id,$old_id,$last_import_id);
										}
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}					

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'service_special_day_breaks' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_service_special_day_breaks;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_service_special_day_breaks) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_service_special_day_breaks)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_service_special_day_breaks);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_service_special_day_id = $bookingpress_import_export->bookingpress_get_record_rel('service_special_day',$export_key,$import_id,$import_record_data[$i]['bookingpress_special_day_id']);
									if($bookingpress_service_special_day_id != '' && $bookingpress_service_special_day_id != 0){
										
										$single_import_record['bookingpress_special_day_id'] = $bookingpress_service_special_day_id;
										unset($single_import_record['bookingpress_service_special_day_break_id']);
										$wpdb->insert($tbl_bookingpress_service_special_day_breaks, $single_import_record);
										$last_import_id = $wpdb->insert_id;

									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 					

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'service_special_day' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_service_special_day;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_service_special_day) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_service_special_day)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_service_special_day);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									if($bookingpress_service_id != '' && $bookingpress_service_id != 0){                                                    
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										$old_id = $single_import_record['bookingpress_service_special_day_id'];
										unset($single_import_record['bookingpress_service_special_day_id']);
										$wpdb->insert($tbl_bookingpress_service_special_day, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                    
										$bookingpress_import_export->bookingpress_set_record_rel('service_special_day',$export_key,$import_id,$old_id,$last_import_id);
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 					

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'service_workhours' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_service_workhours;
					$limit = 50;                            
					if(!empty($tbl_bookingpress_service_workhours) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_service_workhours)){
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_service_workhours);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										if($import_data_v == 'null' || is_null($import_data_v)){
											$single_import_record[$key] = NULL;    
										}else{
											$single_import_record[$key] = sanitize_text_field($import_data_v);
										}                                                    
									}
								}
								if(!empty($single_import_record)){
									$bookingpress_service_id = $bookingpress_import_export->bookingpress_get_record_rel('services',$export_key,$import_id,$import_record_data[$i]['bookingpress_service_id']);
									if($bookingpress_service_id != '' && $bookingpress_service_id != 0){                                                    
										$single_import_record['bookingpress_service_id'] = $bookingpress_service_id;
										unset($single_import_record['bookingpress_service_workhours_id']);
										unset($single_import_record['bookingpress_service_workhours_created_at']);
										$wpdb->insert($tbl_bookingpress_service_workhours, $single_import_record);
										$last_import_id = $wpdb->insert_id;                                                 
									}                                                
								} 
								$total_imported++;                                          
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}					

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'default_special_day_breaks' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_default_special_day_breaks;
					$limit = 100;
					if(!empty($tbl_bookingpress_default_special_day_breaks) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_default_special_day_breaks)){
						if($detail_import_last_record == 0){
							$wpdb->query("TRUNCATE TABLE $tbl_bookingpress_default_special_day_breaks"); // phpcs:ignore
						}
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_default_special_day_breaks);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										$single_import_record[$key] = sanitize_text_field($import_data_v);
									}
								}
								if(!empty($single_import_record)){
									$old_id = $single_import_record['bookingpress_special_day_id'];                                              
									unset($single_import_record['bookingpress_special_day_break_id']);
									$single_import_record['bookingpress_special_day_id'] = $bookingpress_import_export->bookingpress_get_record_rel('default_special_day',$export_key,$import_id,$old_id);
									$wpdb->insert($tbl_bookingpress_default_special_day_breaks, $single_import_record);
									$last_import_id = $wpdb->insert_id;                                                
								} 
								$total_imported++;                                           
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                                 
					}else{						
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					} 					

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }
            if($detail_import_detail_type == 'default_special_day' && isset($bookingpress_import_data[$detail_import_detail_type])){                                
                if(is_array($bookingpress_import_data[$detail_import_detail_type]) && count($bookingpress_import_data[$detail_import_detail_type]) > 0){
										
					global $tbl_bookingpress_default_special_day;
					$limit = 100;
					if(!empty($tbl_bookingpress_default_special_day) && $bookingpress_import_export->bookingpress_check_table_exists_func($tbl_bookingpress_default_special_day)){
						if($detail_import_last_record == 0){
							$wpdb->query("TRUNCATE TABLE $tbl_bookingpress_default_special_day"); // phpcs:ignore
						}
						$get_all_table_columns = $bookingpress_import_export->bookingpress_get_all_columns_func($tbl_bookingpress_default_special_day);
						$total_record = count($bookingpress_import_data[$detail_import_detail_type]);
						$import_record_data = $bookingpress_import_data[$detail_import_detail_type];
						$total_imported = 0;
						$new_limit = $limit + $detail_import_last_record;
						for($i=$detail_import_last_record; $i<$new_limit; $i++){
							if(isset($import_record_data[$i])){
								$single_import_record = array();
								foreach($import_record_data[$i] as $key=>$value){
									if(in_array($key,$get_all_table_columns)){
										$import_data_v = $import_record_data[$i][$key];
										$import_data_v = $bookingpress_import_export->bookingpress_import_value_modified($import_data_v,$detail_import_detail_type,$key);
										$single_import_record[$key] = sanitize_text_field($import_data_v);
									}
								}
								if(!empty($single_import_record)){
									$old_id = $single_import_record['bookingpress_special_day_id'];
									unset($single_import_record['bookingpress_special_day_id']);
									$wpdb->insert($tbl_bookingpress_default_special_day, $single_import_record);
									$last_import_id = $wpdb->insert_id;
									$bookingpress_import_export->bookingpress_set_record_rel('default_special_day',$export_key,$import_id,$old_id,$last_import_id);
								} 
								$total_imported++;                                           
							}
						}                                    
						$total_imported = $total_imported + $detail_import_last_record;                                    
						if($detail_import_total_record <= $total_imported){
							$is_complete = 1;
						}                                                               
					}else{
						$not_import_data_reason = esc_html__('Table Not Exists.','bookingpress-appointment-booking');
						$bookingpress_import_detail_type_data['not_import_data_reason'] = $not_import_data_reason;
						$is_complete = 2;
					}  					

                    $bookingpress_import_detail_type_data['is_complete'] = $is_complete;
                    $bookingpress_import_detail_type_data['limit'] = $limit;
                    $bookingpress_import_detail_type_data['total_imported'] = $total_imported;
                }else{
                    $bookingpress_import_detail_type_data['is_complete'] = 1;
                    $bookingpress_import_detail_type_data['limit'] = 1000;
					$bookingpress_import_detail_type_data['total_imported'] = 0;					
				}

            }

            return $bookingpress_import_detail_type_data;
        }


		function bookingpress_add_wordpress_default_option_data_func($all_option_data,$export_final_list){
			global $bookingpress_import_export,$BookingPress;
            $bookingpress_active_plugin_module_list = $bookingpress_import_export->bookingpress_active_plugin_module_list();

            if($bookingpress_active_plugin_module_list['cart_addon'] || $bookingpress_active_plugin_module_list['recurring_addon']){                 
                $bookingpress_cart_order_id = get_option('bookingpress_cart_order_id', true);
                if(empty($bookingpress_cart_order_id)){
                    $bookingpress_cart_order_id = 1;
                }else{
                    $bookingpress_cart_order_id = floatval($bookingpress_cart_order_id) + 1;
                }
                $all_option_data[] = array('key'=>'bookingpress_cart_order_id','value'=>$bookingpress_cart_order_id);
            }    
            if($bookingpress_active_plugin_module_list['invoice_addon']){       
                if(!empty($export_final_list)){
                    //if(in_array('settings',$export_final_list)){
                        $bookingpress_invoice_html_view = get_option('bookingpress_invoice_html_format');
                        if(!empty($bookingpress_invoice_html_view)){
                            $bookingpress_invoice_html_view = addslashes($bookingpress_invoice_html_view);
							$all_option_data[] = array('key'=>'bookingpress_invoice_html_format','value'=>$bookingpress_invoice_html_view);
                        }                        
                    //}
                }          
            }			
			return $all_option_data;
		}


        /**
         * Function for add extra export application data 
        */
        function bookingpress_add_extra_export_data_func($export_detail,$export_id){

            global $tbl_bookingpress_export_data_log_detail,$wpdb,$bookingpress_import_export;

			$bookingpress_active_plugin_module_list = $bookingpress_import_export->bookingpress_active_plugin_module_list();
            
			if($export_detail == 'settings'){

				$new_export_key = 'default_special_day';
				$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
				if($total_records > 0){
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                     
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);    
				}

				$new_export_key = 'default_special_day_breaks';
				$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
				if($total_records > 0){
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);    
				}

            }

			if($export_detail == 'appointments'){

				$new_export_key = 'guests_data';
				$total_records = (int)$bookingpress_import_export->export_item_total_records($new_export_key);
				if($total_records > 0){
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);    
				}
				if($bookingpress_active_plugin_module_list['package_addon']){
					
					$new_export_key = 'package_order';
					$total_records = (int)$bookingpress_import_export->export_item_total_records($new_export_key);
					if($total_records > 0){
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 0,                                                    
						);
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);    
					}                                    

				}				

			}

			if($export_detail == 'packages'){
				if($bookingpress_active_plugin_module_list['package_addon']){ 
					
					$new_export_key = 'package_images';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields); 

					$new_export_key = 'package_services';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);

				}
			}

			if($export_detail == 'services'){

				$services_total_records = $bookingpress_import_export->export_item_total_records('services');
				if($services_total_records > 0){
					
					$new_export_key = 'service_workhours';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);                            
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);

					global $tbl_bookingpress_service_daysoff;
					if(!empty($tbl_bookingpress_service_daysoff)){
						$new_export_key1 = 'service_daysoff';
						$total_records = $bookingpress_import_export->export_item_total_records($new_export_key1);
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key1,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 1,                                                    
						);
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);    
					}

					$new_export_key = 'service_special_day';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);

					$new_export_key = 'service_special_day_breaks';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
					if($total_records > 0){
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 1,                                                    
						);
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);    
					}
										
					if($bookingpress_active_plugin_module_list['custom_service_duration_addon']){ 

						$new_export_key = 'custom_service_durations';
						$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 1,                                                    
						);
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);

					}
					if($bookingpress_active_plugin_module_list['service_extra_module']){

						$new_export_key = 'extra_services';
						$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 1,                                                    
						);
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);

					}
					if($bookingpress_active_plugin_module_list['happy_hours_addon']){

						$new_export_key = 'happy_hours_service';
						$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 1,                                                    
						);
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);

					} 		
					
					if($bookingpress_active_plugin_module_list['staffmember_module']){ 

						$new_export_key = 'staff_members';
						$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 0,                                                    
						);                                
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields); 
						
						$new_export_key = 'staffmembers_daysoff';
						$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 1,                                                    
						);
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields); 

						$new_export_key = 'staffmembers_services';
						$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 1,                                                    
						); 
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields); 

						$new_export_key = 'staff_member_workhours';
						$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 1,                                                    
						);
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields); 

						$new_export_key = 'staffmembers_special_day';
						$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 1,                                                    
						);
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields); 

						$new_export_key = 'staffmembers_special_day_breaks';
						$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 1,                                                    
						);  
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields); 

						$new_export_key = 'staffmembers_meta';
						$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
						$bookingpress_db_fields = array(
							'export_id' => $export_id, 
							'export_detail_type' => $new_export_key,
							'export_detail_total_record' => $total_records,
							'export_detail_last_record' => 0,
							'export_detail_complete' => 0,
							'export_detail_record_hide' => 1,                                                    
						);                              
						$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);
						
						
						if($bookingpress_active_plugin_module_list['custom_service_duration_addon']){   

							$new_export_key = 'custom_staffmembers_service_durations';
							$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
							$bookingpress_db_fields = array(
								'export_id' => $export_id, 
								'export_detail_type' => $new_export_key,
								'export_detail_total_record' => $total_records,
								'export_detail_last_record' => 0,
								'export_detail_complete' => 0,
								'export_detail_record_hide' => 1,                                                    
							);							
							$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);

						} 						

					}
					



				}
				if($bookingpress_active_plugin_module_list['coupons_module']){                                
					$new_export_key = 'coupon';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);                            
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);
				}
				if($bookingpress_active_plugin_module_list['discount_addon']){
					$new_export_key = 'advanced_discount';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);                            
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);
				}
				
				if($bookingpress_active_plugin_module_list['location_addon']){ 

					$new_export_key = 'location';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                     
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);


					$new_export_key = 'locations_service_special_days';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields); 

					$new_export_key = 'locations_service_staff_pricing_details';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields); 

					$new_export_key = 'locations_service_workhours';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);
					
					$new_export_key = 'locations_staff_special_days';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);   
					
					$new_export_key = 'locations_staff_workhours';
					$total_records = $bookingpress_import_export->export_item_total_records($new_export_key);
					$bookingpress_db_fields = array(
						'export_id' => $export_id, 
						'export_detail_type' => $new_export_key,
						'export_detail_total_record' => $total_records,
						'export_detail_last_record' => 0,
						'export_detail_complete' => 0,
						'export_detail_record_hide' => 1,                                                    
					);
					$wpdb->insert($tbl_bookingpress_export_data_log_detail, $bookingpress_db_fields);  

				}				


			}



        }


		function bookingpress_modified_export_total_records_func($total_records,$type,$export_id = 0){

            global $wpdb,$tbl_bookingpress_settings,$tbl_bookingpress_customize_settings,$tbl_bookingpress_customers,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_services,$tbl_bookingpress_notifications,$tbl_bookingpress_form_fields,$tbl_bookingpress_staffmembers,$tbl_bookingpress_coupons,$tbl_bookingpress_advanced_discount,$tbl_bookingpress_servicesmeta,$tbl_bookingpress_default_daysoff,$tbl_bookingpress_default_special_day,$tbl_bookingpress_default_special_day_breaks,$tbl_bookingpress_default_workhours,$tbl_bookingpress_service_special_day,$tbl_bookingpress_service_special_day_breaks,$tbl_bookingpress_service_workhours,$tbl_bookingpress_service_daysoff,$bookingpress_import_export;

            if($type == "guests_data"){
                global $tbl_bookingpress_guests_data;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_guest_data_id) FROM `{$tbl_bookingpress_guests_data}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_guests_data is table name.
            }else if($type == "default_special_day"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_special_day_id) FROM `{$tbl_bookingpress_default_special_day}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day is table name.
            }else if($type == "default_special_day_breaks"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_special_day_break_id) FROM `{$tbl_bookingpress_default_special_day_breaks}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day_breaks is table name.
            }else if($type == "appointments"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_appointment_booking_id) FROM `{$tbl_bookingpress_appointment_bookings}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is table name.
            }else if($type == "services"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_service_id) FROM `{$tbl_bookingpress_services}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is table name.
            }else if($type == "service_daysoff"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_service_daysoff_id) FROM `{$tbl_bookingpress_service_daysoff}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_daysoff is table name.
            }else if($type == "service_special_day"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_service_special_day_id) FROM `{$tbl_bookingpress_service_special_day}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_special_day is table name.
            }else if($type == "service_special_day_breaks"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_service_special_day_break_id) FROM `{$tbl_bookingpress_service_special_day_breaks}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_special_day_breaks is table name.
            }else if($type == "service_workhours"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_service_workhours_id) FROM `{$tbl_bookingpress_service_workhours}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_workhours is table name.
            }else if($type == "bookingpress_servicesmeta"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_servicemeta_id) FROM `{$tbl_bookingpress_servicesmeta}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_servicesmeta is table name.
            }else if($type == "coupon"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_coupon_id) FROM `{$tbl_bookingpress_coupons}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_coupons is table name.
            }else if($type == "staff_members"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_staffmember_id) FROM `{$tbl_bookingpress_staffmembers}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is table name.
            }else if($type == "advanced_discount"){
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_discount_id) FROM `{$tbl_bookingpress_advanced_discount}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_advanced_discount is table name.
            }else if($type == "location"){
                global $tbl_bookingpress_locations;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_location_id) FROM `{$tbl_bookingpress_locations}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_locations is table name.                
            }else if($type == "custom_service_durations"){
                global $tbl_bookingpress_custom_service_durations;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_custom_service_duration_id) FROM `{$tbl_bookingpress_custom_service_durations}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_custom_service_durations is table name.
            }else if($type == "extra_services"){
                global $tbl_bookingpress_extra_services;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_extra_services_id) FROM `{$tbl_bookingpress_extra_services}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_extra_services is table name.
            }else if($type == "happy_hours_service"){
                global $tbl_bookingpress_happy_hours_service;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_happy_hour_id) FROM `{$tbl_bookingpress_happy_hours_service}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_happy_hours_service is table name.
            }else if($type == "packages"){
                global $tbl_bookingpress_packages;
                if(!empty($tbl_bookingpress_packages)){
                    $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_package_id) FROM `{$tbl_bookingpress_packages}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_packages is table name.
                }              
            }else if($type == "package_order"){
                global $tbl_bookingpress_package_bookings;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_package_booking_id) FROM `{$tbl_bookingpress_package_bookings}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_package_bookings is table name.
            }else if($type == "staffmembers_daysoff"){
                global $tbl_bookingpress_staffmembers_daysoff;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_staffmember_daysoff_id) FROM `{$tbl_bookingpress_staffmembers_daysoff}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_daysoff is table name.
            }else if($type == "staffmembers_services"){
                global $tbl_bookingpress_staffmembers_services;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_staffmember_service_id) FROM `{$tbl_bookingpress_staffmembers_services}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_services is table name.
            }else if($type == "staffmembers_special_day"){
                global $tbl_bookingpress_staffmembers_special_day;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_staffmember_special_day_id) FROM `{$tbl_bookingpress_staffmembers_special_day}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day is table name.
            }else if($type == "staffmembers_special_day_breaks"){
                global $tbl_bookingpress_staffmembers_special_day_breaks;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_staffmember_special_day_break_id) FROM `{$tbl_bookingpress_staffmembers_special_day_breaks}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day_breaks is table name.
            }else if($type == "staff_member_workhours"){
                global $tbl_bookingpress_staff_member_workhours;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_staffmember_workhours_id) FROM `{$tbl_bookingpress_staff_member_workhours}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staff_member_workhours is table name.
            }else if($type == "staffmembers_meta"){
                global $tbl_bookingpress_staffmembers_meta;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_staffmembermeta_id) FROM `{$tbl_bookingpress_staffmembers_meta}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_meta is table name.
            }else if($type == "custom_staffmembers_service_durations"){
                global $tbl_bookingpress_custom_staffmembers_service_durations;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_staffmember_duration_id) FROM `{$tbl_bookingpress_custom_staffmembers_service_durations}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_custom_staffmembers_service_durations is table name.
            }else if($type == "locations_service_special_days"){
                global $tbl_bookingpress_locations_service_special_days;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_location_service_special_day_id) FROM `{$tbl_bookingpress_locations_service_special_days}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_locations_service_special_days is table name.
            }else if($type == "locations_service_staff_pricing_details"){
                global $tbl_bookingpress_locations_service_staff_pricing_details;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_service_staff_pricing_id) FROM `{$tbl_bookingpress_locations_service_staff_pricing_details}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_locations_service_staff_pricing_details is table name.
            }else if($type == "locations_service_workhours"){
                global $tbl_bookingpress_locations_service_workhours;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_location_service_workhour_id) FROM `{$tbl_bookingpress_locations_service_workhours}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_locations_service_workhours is table name.
            }else if($type == "locations_staff_special_days"){
                global $tbl_bookingpress_locations_staff_special_days;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_location_staff_special_day_id) FROM `{$tbl_bookingpress_locations_staff_special_days}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_locations_staff_special_days is table name.
            }else if($type == "locations_staff_workhours"){
                global $tbl_bookingpress_locations_staff_workhours;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_location_staff_workhour_id) FROM `{$tbl_bookingpress_locations_staff_workhours}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_locations_staff_workhours is table name.
            }else if($type == "package_images"){
                global $tbl_bookingpress_package_images;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_package_img_id) FROM `{$tbl_bookingpress_package_images}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_package_images is table name.
            }else if($type == "package_services"){
                global $tbl_bookingpress_package_services;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_package_service_id) FROM `{$tbl_bookingpress_package_services}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_package_services is table name.
            }else if($type == "multi_language_data"){
                global $tbl_bookingpress_ml_translation;
                $total_records = $wpdb->get_var("SELECT COUNT(bookingpress_translation_id) FROM `{$tbl_bookingpress_ml_translation}`");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_ml_translation is table name.
            }else if($type == "multi_language_data" && $export_id != 0){
				
				global $tbl_bookingpress_ml_translation;
				$bookingpress_allowed_export_language = $this->bookingpress_allowed_export_language_data_type($export_id);
				$where_clause = '';
				if(!empty($bookingpress_allowed_export_language)){
					$settings_keys = $bookingpress_allowed_export_language; 
					$bookingpress_settings_key_placeholder  = '  bookingpress_element_type IN(';
					$bookingpress_settings_key_placeholder .= rtrim( str_repeat( '%s,', count( $settings_keys ) ), ',' );
					$bookingpress_settings_key_placeholder .= ')';
					array_unshift( $settings_keys, $bookingpress_settings_key_placeholder );
					$where_clause = call_user_func_array( array( $wpdb, 'prepare' ), $settings_keys );    
				}                            
				$total_records = $wpdb->get_var("SELECT COUNT(bookingpress_translation_id) FROM `{$tbl_bookingpress_ml_translation}` Where {$where_clause}");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_locations is table name.

			}

			return $total_records;
		}

        /**
         * Function for export multi-language data
         *
         */
        function get_common_export_multi_language_data_data($xml,$export_detail_last_record,$limit,$table_name,$field_order_by,$export_id = 0){
            global $wpdb;

            $bookingpress_allowed_export_language = $this->bookingpress_allowed_export_language_data_type($export_id);
            $where_clause = ' ';
            if(!empty($bookingpress_allowed_export_language)){
                $settings_keys = $bookingpress_allowed_export_language;  
                $bookingpress_settings_key_placeholder  = ' Where  bookingpress_element_type IN(';
                $bookingpress_settings_key_placeholder .= rtrim( str_repeat( '%s,', count( $settings_keys ) ), ',' );
                $bookingpress_settings_key_placeholder .= ')';
                array_unshift( $settings_keys, $bookingpress_settings_key_placeholder );
                $where_clause = call_user_func_array( array( $wpdb, 'prepare' ), $settings_keys );    
            }                        
            $bookingpress_all_data = $wpdb->get_results( "SELECT * FROM {$table_name} {$where_clause} ORDER BY {$field_order_by} ASC LIMIT  {$export_detail_last_record}, {$limit}",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $table_name is table name defined globally.            
            if(!empty($bookingpress_all_data)){ 
                $i = $export_detail_last_record;
                foreach($bookingpress_all_data as $setting_data){                    
                    $new_arr  = array();
                    foreach($setting_data as $key=>$setting_val){                        
                        $setting_val = (!empty($setting_val) && gettype($setting_val) === 'string')?addslashes($setting_val):$setting_val;
                        $new_arr[$key] = $setting_val;                        
                    }                    
                    unset($new_arr['bookingpress_translation_id']);
                    unset($new_arr['bookingpress_translation_created_date']);                    
                    if($i == 0){
                        $xml .= json_encode($new_arr).'';
                    }else{
                        $xml .= ','.json_encode($new_arr).'';
                    }
                    $i++;                                        
                }                
            }    
            return array('data'=>$xml,'query_res'=>$bookingpress_all_data);            
        } 		

        function bookingpress_allowed_export_language_data_type($export_id = 0){

            global $wpdb,$tbl_bookingpress_settings,$tbl_bookingpress_export_data_log,$tbl_bookingpress_customize_settings;
            $language_fetch_data = array('none');
            $bookingperss_export_data = $wpdb->get_row($wpdb->prepare("SELECT export_data FROM {$tbl_bookingpress_export_data_log}  WHERE export_id = %d Order by export_id DESC",$export_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm
            if(!empty($bookingperss_export_data)){
                $export_data = (isset($bookingperss_export_data['export_data']) && !empty($bookingperss_export_data['export_data']))?$bookingperss_export_data['export_data']:array('none');
                if(!empty($export_data)){
                    $export_data = json_decode($export_data,true);
                    if(in_array('notifications',$export_data)){
                        $language_fetch_data[] = 'manage_notification_customer';
                    }
                    if(in_array('services',$export_data)){
                        $language_fetch_data[] = 'service';
                        $language_fetch_data[] = 'service_extra';
                        $language_fetch_data[] = 'happy_hours';
                        $language_fetch_data[] = 'category'; 
                        $language_fetch_data[] = 'location';
                        $language_fetch_data[] = 'customer_custom_form_fields';
                        $language_fetch_data[] = 'custom_form_fields';                        
                    }
                    if(in_array('settings',$export_data)){                        
                        $settings_keys = $wpdb->get_row("SELECT GROUP_CONCAT(DISTINCT setting_type SEPARATOR ',') as all_setting_type FROM {$tbl_bookingpress_settings}",ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm
                        if(!empty($settings_keys)){
                            $settings_keys = explode(',',$settings_keys['all_setting_type']); 
                            $language_fetch_data = array_merge($language_fetch_data,$settings_keys);
                        }
                        $settings_keys = $wpdb->get_row("SELECT GROUP_CONCAT(DISTINCT bookingpress_setting_type SEPARATOR ',') as all_setting_type FROM {$tbl_bookingpress_customize_settings}",ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_export_data_log is a table name. false alarm
                        if(!empty($settings_keys)){
                            $settings_keys = explode(',',$settings_keys['all_setting_type']); 
                            $language_fetch_data = array_merge($language_fetch_data,$settings_keys);
                        }                        
                    }
                    if(in_array('packages',$export_data)){
                        $language_fetch_data[] = 'package';
                    }

                    $language_fetch_data = apply_filters( 'bookingpress_modified_export_language_fetch_data',$language_fetch_data,$export_data,$export_id);

                }
            }
            return $language_fetch_data;
        }		

        /**
         * Common Export Data
         *
         * @return void
        */        
        function get_common_export_data($xml,$export_detail_last_record,$limit,$table_name,$field_order_by,$field_remove_key = array('none')){
            global $wpdb;
            $bookingpress_all_data = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY {$field_order_by} ASC LIMIT  {$export_detail_last_record}, {$limit}",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $table_name is table name defined globally.            
            if(!is_array($field_remove_key)){
                $field_remove_key = array('none');
            }
            if(!empty($bookingpress_all_data)){ 
                $i = $export_detail_last_record;
                foreach($bookingpress_all_data as $setting_data){                    
                    $new_arr  = array();
                    foreach($setting_data as $key=>$setting_val){
                        if(!in_array($key,$field_remove_key)){
                            $setting_val = (!empty($setting_val) && gettype($setting_val) === 'string')?addslashes($setting_val):$setting_val;
                            $new_arr[$key] = $setting_val;
                        }
                    }
                    if(isset($new_arr['bookingpress_created_at'])){
                        unset($new_arr['bookingpress_created_at']);
                    }
                    if($i == 0){
                        $xml .= json_encode($new_arr).'';
                    }else{
                        $xml .= ','.json_encode($new_arr).'';
                    }
                    $i++;                                        
                }                
            }    
            return array('data'=>$xml,'query_res'=>$bookingpress_all_data);            
        } 

        function bookingpress_modified_export_data_result_func($bookingpress_export_detail_type_data,$export_detail_type,$export_detail_last_record,$limit,$export_id){  
			global $bookingpress_import_export;
			$bookingpress_active_plugin_module_list = $bookingpress_import_export->bookingpress_active_plugin_module_list();          
            if($export_detail_type == 'multi_language_data'){
				if($bookingpress_active_plugin_module_list['multilanguage_addon']){
					global $tbl_bookingpress_ml_translation;
					$limit = 50;
					$type_data = $this->get_common_export_multi_language_data_data("",$export_detail_last_record,$limit,$tbl_bookingpress_ml_translation,'bookingpress_translation_id',$export_id);
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;					
				}
            }
			if($export_detail_type == 'default_special_day'){
				global $tbl_bookingpress_default_special_day;
				$limit = 300;
				$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_default_special_day,'bookingpress_special_day_id',array('bookingpress_created_at'));
				if(!empty($type_data)){
					$type_data['limit'] = $limit;
				}
				return $type_data;					
			}
			if($export_detail_type == 'default_special_day_breaks'){
				global $tbl_bookingpress_default_special_day_breaks;
				$limit = 300;
				$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_default_special_day_breaks,'bookingpress_special_day_break_id',array('bookingpress_special_day_break_id'));
				if(!empty($type_data)){
					$type_data['limit'] = $limit;
				}
				return $type_data;					
			}	
			if($export_detail_type == 'service_daysoff'){
				global $tbl_bookingpress_service_daysoff;
				$limit = 300;
				$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_service_daysoff,'bookingpress_service_daysoff_id');
				if(!empty($type_data)){
					$type_data['limit'] = $limit;
				}
				return $type_data;
			}
			if($export_detail_type == 'service_special_day'){
				global $tbl_bookingpress_service_special_day;
				$limit = 300;
				$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_service_special_day,'bookingpress_service_special_day_id');
				if(!empty($type_data)){
					$type_data['limit'] = $limit;
				}
				return $type_data;
			}
			if($export_detail_type == 'service_special_day_breaks'){
				global $tbl_bookingpress_service_special_day_breaks;
				$limit = 300;
				$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_service_special_day_breaks,'bookingpress_service_special_day_break_id');
				if(!empty($type_data)){
					$type_data['limit'] = $limit;
				}
				return $type_data;
			}
			if($export_detail_type == 'service_workhours'){
				global $tbl_bookingpress_service_workhours;
				$limit = 150;
				$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_service_workhours,'bookingpress_service_workhours_id');
				if(!empty($type_data)){
					$type_data['limit'] = $limit;
				}
				return $type_data;				
			}
			if($export_detail_type == 'package_order'){
				
				$limit = 80;
				$type_data = $this->get_package_order_export_data("",$export_detail_last_record,$limit);
				if(!empty($type_data)){
					$type_data['limit'] = $limit;
				}
				return $type_data;				
			}
			if($export_detail_type == 'staff_members'){
				if($bookingpress_active_plugin_module_list['staffmember_module']){
					global $tbl_bookingpress_staffmembers;
					$limit = 80;
					$type_data = $this->get_staff_members_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_staffmembers,'bookingpress_staffmember_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}			
			}
			if($export_detail_type == 'staffmembers_services'){
				if($bookingpress_active_plugin_module_list['staffmember_module']){
					global $tbl_bookingpress_staffmembers_services;
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_staffmembers_services,'bookingpress_staffmember_service_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'staffmembers_special_day'){
				if($bookingpress_active_plugin_module_list['staffmember_module']){
					global $tbl_bookingpress_staffmembers_special_day;
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_staffmembers_special_day,'bookingpress_staffmember_special_day_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'staff_member_workhours'){
				if($bookingpress_active_plugin_module_list['staffmember_module']){
					global $tbl_bookingpress_staff_member_workhours;
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_staff_member_workhours,'bookingpress_staffmember_workhours_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'guests_data'){
				global $tbl_bookingpress_guests_data;
				if(!empty($tbl_bookingpress_guests_data)){
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_guests_data,'bookingpress_guest_data_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;	
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'packages'){
				global $tbl_bookingpress_packages;
				if($bookingpress_active_plugin_module_list['package_addon'] && !empty($tbl_bookingpress_packages)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_packages,'bookingpress_package_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'package_images'){
				global $tbl_bookingpress_package_images;
				if($bookingpress_active_plugin_module_list['package_addon'] && !empty($tbl_bookingpress_package_images)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_package_images,'bookingpress_package_img_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'package_services'){
				global $tbl_bookingpress_package_services;
				if($bookingpress_active_plugin_module_list['package_addon'] && !empty($tbl_bookingpress_package_services)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_package_services,'bookingpress_package_service_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'location'){
				global $tbl_bookingpress_locations;
				if($bookingpress_active_plugin_module_list['location_addon'] && !empty($tbl_bookingpress_locations)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_locations,'bookingpress_location_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'locations_service_special_days'){
				global $tbl_bookingpress_locations_service_special_days;
				if($bookingpress_active_plugin_module_list['location_addon'] && !empty($tbl_bookingpress_locations_service_special_days)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_locations_service_special_days,'bookingpress_location_service_special_day_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}			
			if($export_detail_type == 'locations_service_staff_pricing_details'){
				global $tbl_bookingpress_locations_service_staff_pricing_details;
				if($bookingpress_active_plugin_module_list['location_addon'] && !empty($tbl_bookingpress_locations_service_staff_pricing_details)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_locations_service_staff_pricing_details,'bookingpress_service_staff_pricing_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'locations_service_workhours'){
				global $tbl_bookingpress_locations_service_workhours;
				if($bookingpress_active_plugin_module_list['location_addon'] && !empty($tbl_bookingpress_locations_service_workhours)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_locations_service_workhours,'bookingpress_location_service_workhour_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'locations_staff_special_days'){
				global $tbl_bookingpress_locations_staff_special_days;
				if($bookingpress_active_plugin_module_list['location_addon'] && !empty($tbl_bookingpress_locations_staff_special_days)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_locations_staff_special_days,'bookingpress_location_staff_special_day_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'locations_staff_workhours'){
				global $tbl_bookingpress_locations_staff_workhours;
				if($bookingpress_active_plugin_module_list['location_addon'] && !empty($tbl_bookingpress_locations_staff_workhours)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_locations_staff_workhours,'bookingpress_location_staff_workhour_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'staffmembers_daysoff'){
				global $tbl_bookingpress_staffmembers_daysoff;
				if($bookingpress_active_plugin_module_list['staffmember_module'] && !empty($tbl_bookingpress_staffmembers_daysoff)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_staffmembers_daysoff,'bookingpress_staffmember_daysoff_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'custom_staffmembers_service_durations'){
				global $tbl_bookingpress_custom_staffmembers_service_durations;
				if($bookingpress_active_plugin_module_list['custom_service_duration_addon'] && !empty($tbl_bookingpress_custom_staffmembers_service_durations)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_custom_staffmembers_service_durations,'bookingpress_staffmember_duration_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}				
			}
			if($export_detail_type == 'staffmembers_meta'){
				global $tbl_bookingpress_staffmembers_meta;
				if($bookingpress_active_plugin_module_list['staffmember_module'] && !empty($tbl_bookingpress_staffmembers_meta)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_staffmembers_meta,'bookingpress_staffmembermeta_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}			
			if($export_detail_type == 'staffmembers_special_day_breaks'){
				global $tbl_bookingpress_staffmembers_special_day_breaks;
				if($bookingpress_active_plugin_module_list['staffmember_module'] && !empty($tbl_bookingpress_staffmembers_special_day_breaks)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_staffmembers_special_day_breaks,'bookingpress_staffmember_special_day_break_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'coupon'){
				global $tbl_bookingpress_coupons;
				if($bookingpress_active_plugin_module_list['coupons_module'] && !empty($tbl_bookingpress_coupons)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_coupons,'bookingpress_coupon_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}			
			if($export_detail_type == 'advanced_discount'){
				global $tbl_bookingpress_advanced_discount;
				if($bookingpress_active_plugin_module_list['discount_addon'] && !empty($tbl_bookingpress_advanced_discount)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_advanced_discount,'bookingpress_discount_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'custom_service_durations'){
				global $tbl_bookingpress_custom_service_durations;
				if($bookingpress_active_plugin_module_list['custom_service_duration_addon'] && !empty($tbl_bookingpress_custom_service_durations)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_custom_service_durations,'bookingpress_custom_service_duration_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'extra_services'){
				global $tbl_bookingpress_extra_services;
				if($bookingpress_active_plugin_module_list['service_extra_module'] && !empty($tbl_bookingpress_extra_services)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_extra_services,'bookingpress_extra_services_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}
			if($export_detail_type == 'happy_hours_service'){
				global $tbl_bookingpress_happy_hours_service;
				if($bookingpress_active_plugin_module_list['happy_hours_addon'] && !empty($tbl_bookingpress_happy_hours_service)){					
					$limit = 200;
					$type_data = $this->get_common_export_data("",$export_detail_last_record,$limit,$tbl_bookingpress_happy_hours_service,'bookingpress_happy_hour_id');
					if(!empty($type_data)){
						$type_data['limit'] = $limit;
					}
					return $type_data;
				}else{
					return array('data'=>'','query_res'=>'');
				}
			}


            return $bookingpress_export_detail_type_data;
        }

		function bookingpress_removed_customize_setting_export_func($settings_keys){
			$settings_keys[] = 'default_package_booking_page';
			return $settings_keys;
		}


        /**
            * Staff members Export Data
            *
            * @return void
        */        
        function get_staff_members_export_data($xml,$export_detail_last_record,$limit,$table_name,$field_order_by){
            global $wpdb;
            $bookingpress_all_data = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY {$field_order_by} ASC LIMIT  {$export_detail_last_record}, {$limit}",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $table_name is table name defined globally.            
            if(!empty($bookingpress_all_data)){
                $i = $export_detail_last_record;
                foreach($bookingpress_all_data as $setting_data){                    
                    $new_arr  = array();
                    foreach($setting_data as $key=>$setting_val){
                        $setting_val = (!empty($setting_val) && gettype($setting_val) === 'string')?addslashes($setting_val):$setting_val;
                        $new_arr[$key] = $setting_val;
                    }
                    if($setting_data['bookingpress_wpuser_id'] != 0){
                        $wp_user_detail = get_userdata($setting_data['bookingpress_wpuser_id']);
                        if(!empty($wp_user_detail)){
                            
                            $new_arr['wp_user'] = $wp_user_detail;                      
                            $user_id = $setting_data['bookingpress_wpuser_id'];
                            $user_firstname = get_user_meta( $user_id, 'first_name', true );                        
                            $user_lastname = get_user_meta( $user_id, 'last_name', true );  
                            $new_arr['wp_user_meta'] = array('first_name'=>$user_firstname,'last_name'=>$user_lastname);

                        }
                    }
                    if($i == 0){
                        $xml .= json_encode($new_arr).'';
                    }else{
                        $xml .= ','.json_encode($new_arr).'';
                    }
                    $i++; 
                }                
            }    
            return array('data'=>$xml,'query_res'=>$bookingpress_all_data);            
        }

        /**
         * Export Package Order Data
         *
        */
        function get_package_order_export_data($xml,$export_detail_last_record,$limit){
            global $wpdb,$tbl_bookingpress_package_bookings, $tbl_bookingpress_payment_logs,$tbl_bookingpress_entries,$tbl_bookingpress_entries_meta,$tbl_bookingpress_package_bookings_meta;
            $bookingpress_all_data = $wpdb->get_results( "SELECT * FROM {$tbl_bookingpress_package_bookings} ORDER BY bookingpress_package_booking_id ASC LIMIT  {$export_detail_last_record}, {$limit}",ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally.            
            if(!empty($bookingpress_all_data)){
                $i =  $export_detail_last_record;
                foreach($bookingpress_all_data as $setting_data){               
                    $new_arr  = array();
                    foreach($setting_data as $key=>$setting_val){
                        if($key != 'bookingpress_package_created_at'){
                            $setting_val = (!empty($setting_val) && gettype($setting_val) === 'string')?addslashes($setting_val):$setting_val;
                            $new_arr[$key] = $setting_val;
                        }
                    }
                    /* Add Package Meta Data */
                    $new_arr['meta_data'] = array();
                    $bookingpress_package_meta_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_package_bookings_meta} Where bookingpress_package_booking_id = %d order by bookingpress_package_bookings_meta_id DESC",$setting_data['bookingpress_package_booking_id']),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally.
                    $bpa_already_add_key = array('none');
                    if(!empty($bookingpress_package_meta_data)){
                        $package_bookingnew_meta = array();
                        foreach($bookingpress_package_meta_data as $app_m_key=>$app_m_val){    
                            $keyaaded = $bookingpress_package_meta_data[$app_m_key]['bookingpress_package_meta_key'];                        
                            if(!in_array($keyaaded,$bpa_already_add_key)){
                                $bpa_already_add_key[] = $keyaaded;
                                $bookingpress_package_meta_data[$app_m_key]['bookingpress_package_meta_value'] = (!empty($bookingpress_package_meta_data[$app_m_key]['bookingpress_package_meta_value']) && gettype($bookingpress_package_meta_data[$app_m_key]['bookingpress_package_meta_value']) === 'string')?addslashes($bookingpress_package_meta_data[$app_m_key]['bookingpress_package_meta_value']):$bookingpress_package_meta_data[$app_m_key]['bookingpress_package_meta_value'];                            
                                unset($bookingpress_package_meta_data[$app_m_key]['bookingpress_package_meta_created_date']);    
                                $package_bookingnew_meta[$app_m_key] = $bookingpress_package_meta_data[$app_m_key];
                            }                            
                        }
                        $new_arr['meta_data'] = $package_bookingnew_meta;
                    }
                    /* Entry Table Data Export For Appointments */
                    $new_arr['entry_data'] = array();
                    if($setting_data['bookingpress_entry_id'] != 0){
                        $bookingpress_entry_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_entries} Where bookingpress_entry_id = %d",$setting_data['bookingpress_entry_id']),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally.
                        if(!empty($bookingpress_entry_data)){
                            foreach($bookingpress_entry_data as $ent_key=>$ent_field_val){
                                $bookingpress_entry_data[$ent_key] = (!empty($bookingpress_entry_data[$ent_key]) && gettype($bookingpress_entry_data[$ent_key]) === 'string')?addslashes($bookingpress_entry_data[$ent_key]):$bookingpress_entry_data[$ent_key];                             
                            }
                            $bookingpress_entry_meta_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_entries_meta} Where bookingpress_entry_id = %d",$setting_data['bookingpress_entry_id']),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally.
                            foreach($bookingpress_entry_meta_data as $ent_meta_key=>$ent_meta_field_val){
                                $bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entry_meta_value'] = (!empty($bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entry_meta_value']) && gettype($bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entry_meta_value']) === 'string')?addslashes($bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entry_meta_value']):$bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entry_meta_value'];
                                unset($bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entry_meta_id']);
                                unset($bookingpress_entry_meta_data[$ent_meta_key]['bookingpress_entrymeta_created_date']);   
                            }
                            unset($bookingpress_entry_meta_data['bookingpress_created_at']);
                            $bookingpress_entry_data['meta_data'] = $bookingpress_entry_meta_data;    
                            $new_arr['entry_data'] = $bookingpress_entry_data;
                        }
                    }
                    /* Payment Table Data Export For Appointments */
                    $new_arr['payment_data'] = array();
                    if($setting_data['bookingpress_payment_id'] != 0){
                        $bookingpress_payment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_payment_logs} Where bookingpress_payment_log_id = %d",$setting_data['bookingpress_payment_id']),ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally.
                        if(!empty($bookingpress_payment_data)){
                            foreach($bookingpress_payment_data as $ent_key=>$ent_field_val){
                                $bookingpress_payment_data[$ent_key] = (!empty($bookingpress_payment_data[$ent_key]) && gettype($bookingpress_payment_data[$ent_key]) === 'string')?addslashes($bookingpress_payment_data[$ent_key]):$bookingpress_payment_data[$ent_key];                                
                            }
                            unset($new_arr['payment_data']['bookingpress_created_at']);
                            $new_arr['payment_data'] = $bookingpress_payment_data;
                        }
                    }
                    if($i == 0){
                        $xml .= json_encode($new_arr).'';
                    }else{
                        $xml .= ','.json_encode($new_arr).'';
                    } 
                    $i++;
                }                
            }    
            return array('data'=>$xml,'query_res'=>$bookingpress_all_data);            
        }

		/**
		 * BookingPress Addon Active List Pro
		*/
		function bookingpress_modified_migration_active_module_list_func($all_module_and_addon_list){

			if (! function_exists('is_plugin_active') ) {
                include ABSPATH . '/wp-admin/includes/plugin.php';
            }            
            global $bookingpress_pro_staff_members, $bookingpress_bring_anyone_with_you, $bookingpress_deposit_payment,$bookingpress_coupons,$bookingpress_service_extra;
			
            $bookingpress_pro = (is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php'))?1:0;
            if($bookingpress_pro){
                $bpa_is_staffmember_module_active = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
                $bpa_multiple_quantity = $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation();
                $deposit_payment_module = $bookingpress_deposit_payment->bookingpress_check_deposit_payment_module_activation();
                $coupons_module = $bookingpress_coupons->bookingpress_check_coupon_module_activation();
                $service_extra_module = $bookingpress_service_extra->bookingpress_check_service_extra_module_activation();    
            }else{
                $bpa_is_staffmember_module_active = 0;
                $bpa_multiple_quantity = 0;
                $deposit_payment_module = 0;
                $coupons_module = 0;
                $service_extra_module = 0;
            } 
            $multilanguage_addon = (is_plugin_active('bookingpress-multilanguage/bookingpress-multilanguage.php'))?1:0;
            $package_addon = (is_plugin_active('bookingpress-package/bookingpress-package.php'))?1:0;
            
            $location_addon = (is_plugin_active('bookingpress-location/bookingpress-location.php'))?1:0;
            $recurring_addon = (is_plugin_active('bookingpress-recurring-appointments/bookingpress-recurring-appointments.php'))?1:0;
            $tip_addon = (is_plugin_active('bookingpress-tip/bookingpress-tip.php'))?1:0;
            $discount_addon = (is_plugin_active('bookingpress-discount-addon/bookingpress-discount-addon.php'))?1:0;
            $waiting_list_addon = (is_plugin_active('bookingpress-waiting-list/bookingpress-waiting-list.php'))?1:0;
            $cart_addon = (is_plugin_active('bookingpress-cart/bookingpress-cart.php'))?1:0;
            $sms_addon = (is_plugin_active('bookingpress-sms/bookingpress-sms.php'))?1:0;
            $whatsapp_addon = (is_plugin_active('bookingpress-whatsapp/bookingpress-whatsapp.php'))?1:0;
            $discount_addon = (is_plugin_active('bookingpress-discount-addon/bookingpress-discount-addon.php'))?1:0;            
            $custom_service_duration_addon = (is_plugin_active('bookingpress-custom-service-duration/bookingpress-custom-service-duration.php'))?1:0;
            $happy_hours_addon = (is_plugin_active('bookingpress-happy-hours/bookingpress-happy-hours.php'))?1:0;
            $tax_addon = (is_plugin_active('bookingpress-tax/bookingpress-tax.php'))?1:0;
            $invoice_addon = (is_plugin_active('bookingpress-invoice/bookingpress-invoice.php'))?1:0;            
            $google_calendar_addon = (is_plugin_active('bookingpress-google-calendar/bookingpress-google-calendar.php'))?1:0;
            $outlook_calendar_addon = (is_plugin_active('bookingpress-outlook-calendar/bookingpress-outlook-calendar.php'))?1:0;
            $zapier_addon = (is_plugin_active('bookingpress-zapier/bookingpress-zapier.php'))?1:0;
            $mailchimp_addon = (is_plugin_active('bookingpress-mailchimp/bookingpress-mailchimp.php'))?1:0;
            $aweber_addon = (is_plugin_active('bookingpress-aweber/bookingpress-aweber.php'))?1:0;
            $whatsapp_addon = (is_plugin_active('bookingpress-whatsapp/bookingpress-whatsapp.php'))?1:0;
            $sms_addon = (is_plugin_active('bookingpress-sms/bookingpress-sms.php'))?1:0;
            $mobile_connect_addon = (is_plugin_active('bookingpress-mobile-connect/bookingpress-mobile-connect.php'))?1:0;
            $conversion_tracking_addon = (is_plugin_active('bookingpress-conversion-tracking/bookingpress-conversion-tracking.php'))?1:0;                        
            
            $all_module_and_addon_list_pro = array(                
                'bookingpress_pro' => $bookingpress_pro,
                'staffmember_module' => $bpa_is_staffmember_module_active,
                'multiple_quantity_module' => $bpa_multiple_quantity,
                'deposit_payment_module' => $deposit_payment_module,
                'coupons_module' => $coupons_module,
                'service_extra_module' => $service_extra_module,
                'multilanguage_addon' => $multilanguage_addon,
                'package_addon' => $package_addon,                
                'location_addon' => $location_addon,
                'recurring_addon' => $recurring_addon,
                'tip_addon' => $tip_addon,                
                'discount_addon' => $discount_addon,
                'cart_addon' => $cart_addon,
                'waiting_list_addon' => $waiting_list_addon,
                'sms_addon' => $sms_addon,
                'whatsapp_addon' => $whatsapp_addon,
                'discount_addon' => $discount_addon,
                'custom_service_duration_addon' => $custom_service_duration_addon,
                'happy_hours_addon' => $happy_hours_addon,
                'tax_addon' => $tax_addon,
                'invoice_addon' => $invoice_addon,
                'google_calendar_addon' => $google_calendar_addon,
                'outlook_calendar_addon' => $outlook_calendar_addon,
                'zapier_addon' => $zapier_addon, 
                'mailchimp_addon' => $mailchimp_addon,
                'aweber_addon' => $aweber_addon,                
                'whatsapp_addon' => $whatsapp_addon,
                'sms_addon' => $sms_addon,
                'conversion_tracking_addon' => $conversion_tracking_addon,
                'mobile_connect_addon' => $mobile_connect_addon,
            );

			$all_module_and_addon_list = array_merge($all_module_and_addon_list,$all_module_and_addon_list_pro);

			return $all_module_and_addon_list;
		}

        /**
         * Function for add pro setting tab view file
        */
        public function bookingpress_general_settings_add_tab_filter_func($bookingpress_file_url){
			$bookingpress_file_url[] = BOOKINGPRESS_PRO_VIEWS_DIR . '/importexport/import_export_tab.php';
			return $bookingpress_file_url;
        }

        /**
         * API Debug Log
         *
         * @return void
         */
        function bookingpress_add_debug_log_outside_api_func(){
            global $bookingpress_common_date_format;
        ?>
            <div class="bpa-gs__cb--item">
                <div class="bpa-gs__cb--item-heading">
                    <h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Import/Export Debug Logs', 'bookingpress-appointment-booking' ); ?></h4>
                </div>
                <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-row type="flex" class="bpa-debug-item__body">
                            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                                <h4> <?php esc_html_e( 'Export Logs', 'bookingpress-appointment-booking' ); ?></h4>
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                                <el-form-item>
                                    <el-switch class="bpa-swtich-control" v-model="debug_log_setting_form.migration_tool_debug_logs"></el-switch>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <el-row>
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <div class="bpa-debug-item__btns" v-if="debug_log_setting_form.migration_tool_debug_logs == true">
                                    <div class="bpa-di__btn-item">
                                        <el-button class="bpa-btn bpa-btn__small" @click="bookingpess_view_log('migration_tool_debug_logs', '', '<?php esc_html_e( 'Import/Export Debug Logs', 'bookingpress-appointment-booking' ); ?>')" ><?php esc_html_e( 'View log', 'bookingpress-appointment-booking' ); ?></el-button>
                                    </div>
                                    <div class="bpa-di__btn-item">
                                        <el-popover placement="bottom" width="450" trigger="click">
                                            <div class="bpa-dialog-download"> 
                                                <el-row type="flex">
                                                    <el-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="bpa-download-dropdown-label">			
                                                        <label for="start_time" class="el-form-item__label">
                                                            <span class="bpa-form-label"><?php esc_html_e( 'Select log duration to download', 'bookingpress-appointment-booking' ); ?></span>
                                                        </label>			
                                                    </el-col>			
                                                    <el-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">											
                                                        <el-select :popper-append-to-body="proper_body_class" v-model="select_download_log" class="bpa-form-control bpa-form-control__left-icon">	
                                                            <el-option v-for="download_option in log_download_default_option" :key="download_option.key" :label="download_option.key" :value="download_option.value"></el-option>
                                                        </el-select>										
                                                    </el-col>		
                                                </el-row>										
                                                <el-row v-if="select_download_log == 'custom'" class="bpa-download-datepicker">
                                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >											
                                                        <el-date-picker popper-class="bpa-el-select--is-with-modal" class="bpa-form-control--date-range-picker" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" v-model="download_log_daterange" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>" :clearable="false" value-format="yyyy-MM-dd" :picker-options="filter_pickerOptions"> </el-date-picker>
                                                    </el-col>
                                                </el-row>
                                                <el-row>													
                                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >										
                                                        <el-button class="bpa-btn bpa-btn--primary" :class="is_display_download_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="bookingpress_download_log('migration_tool_debug_logs', select_download_log, download_log_daterange)" :disabled="is_disabled" >
                                                            <span class="bpa-btn__label"><?php esc_html_e( 'Download', 'bookingpress-appointment-booking' ); ?></span>
                                                            <div class="bpa-btn--loader__circles">
                                                                <div></div>
                                                                <div></div>
                                                                <div></div>
                                                            </div>
                                                        </el-button>	
                                                    </el-col>
                                                </el-row>	
                                            </div>
                                            <el-button class="bpa-btn bpa-btn__small" slot="reference" ><?php esc_html_e( 'Download Log', 'bookingpress-appointment-booking' ); ?></el-button>
                                        </el-popover>	
                                    </div>
                                    <div class="bpa-di__btn-item">
                                        <el-popconfirm 
                                            confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                            cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                            icon="false" 
                                            title="<?php esc_html_e( 'Are you sure you want to clear debug logs?', 'bookingpress-appointment-booking' ); ?>"
                                            @confirm="bookingpess_clear_bebug_log('migration_tool_debug_logs')"
                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                            cancel-button-type="bpa-btn bpa-btn__small" >
                                            <el-button class="bpa-btn bpa-btn__small" slot="reference"><?php esc_html_e( 'Clear Log', 'bookingpress-appointment-booking' ); ?></el-button>
                                        </el-popconfirm>
                                    </div>
                                </div>
                            </el-col>
                        </el-row>
                    </el-col>
                </el-row>
                <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-row type="flex" class="bpa-debug-item__body">
                                <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                                    <h4> <?php esc_html_e( 'Import Logs', 'bookingpress-appointment-booking' ); ?></h4>
                                </el-col>
                                <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
                                    <el-form-item>
                                        <el-switch class="bpa-swtich-control" v-model="debug_log_setting_form.migration_tool_import_debug_logs"></el-switch>
                                    </el-form-item>
                                </el-col>
                        </el-row>
                        <el-row>
                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                <div class="bpa-debug-item__btns" v-if="debug_log_setting_form.migration_tool_import_debug_logs == true">
                                    <div class="bpa-di__btn-item">
                                        <el-button class="bpa-btn bpa-btn__small" @click="bookingpess_view_log('migration_tool_import_debug_logs', '', '<?php esc_html_e( 'Import/Export Debug Logs', 'bookingpress-appointment-booking' ); ?>')" ><?php esc_html_e( 'View log', 'bookingpress-appointment-booking' ); ?></el-button>
                                    </div>
                                    <div class="bpa-di__btn-item">
                                        <el-popover placement="bottom" width="450" trigger="click" >
                                            <div class="bpa-dialog-download"> 
                                                <el-row type="flex">
                                                    <el-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14" class="bpa-download-dropdown-label">			
                                                        <label for="start_time" class="el-form-item__label">
                                                            <span class="bpa-form-label"><?php esc_html_e( 'Select log duration to download', 'bookingpress-appointment-booking' ); ?></span>
                                                        </label>			
                                                    </el-col>			
                                                    <el-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">											
                                                        <el-select :popper-append-to-body="proper_body_class" v-model="select_download_log" class="bpa-form-control bpa-form-control__left-icon">	
                                                            <el-option v-for="download_option in log_download_default_option" :key="download_option.key" :label="download_option.key" :value="download_option.value"></el-option>
                                                        </el-select>										
                                                    </el-col>		
                                                </el-row>										
                                                <el-row v-if="select_download_log == 'custom'" class="bpa-download-datepicker">
                                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >											
                                                        <el-date-picker popper-class="bpa-el-select--is-with-modal" class="bpa-form-control--date-range-picker" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" v-model="download_log_daterange" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e('End date', 'bookingpress-appointment-booking'); ?>" :clearable="false" value-format="yyyy-MM-dd" :picker-options="filter_pickerOptions"> </el-date-picker>
                                                    </el-col>
                                                </el-row>
                                                <el-row>													
                                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >										
                                                        <el-button class="bpa-btn bpa-btn--primary" :class="is_display_download_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="bookingpress_download_log('migration_tool_import_debug_logs', select_download_log, download_log_daterange)" :disabled="is_disabled" >
                                                            <span class="bpa-btn__label"><?php esc_html_e( 'Download', 'bookingpress-appointment-booking' ); ?></span>
                                                            <div class="bpa-btn--loader__circles">
                                                                <div></div>
                                                                <div></div>
                                                                <div></div>
                                                            </div>
                                                        </el-button>	
                                                    </el-col>
                                                </el-row>	
                                            </div>
                                            <el-button class="bpa-btn bpa-btn__small" slot="reference" ><?php esc_html_e( 'Download Log', 'bookingpress-appointment-booking' ); ?></el-button>
                                        </el-popover>	
                                    </div>
                                    <div class="bpa-di__btn-item">
                                        <el-popconfirm 
                                            confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
                                            cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
                                            icon="false" 
                                            title="<?php esc_html_e( 'Are you sure you want to clear debug logs?', 'bookingpress-appointment-booking' ); ?>"
                                            @confirm="bookingpess_clear_bebug_log('migration_tool_import_debug_logs')"
                                            confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
                                            cancel-button-type="bpa-btn bpa-btn__small" >
                                            <el-button class="bpa-btn bpa-btn__small" slot="reference"><?php esc_html_e( 'Clear Log', 'bookingpress-appointment-booking' ); ?></el-button>
                                        </el-popconfirm>
                                    </div>
                                </div>
                            </el-col>
                        </el-row>
                    </el-col>
                </el-row>                
			</div>            
        <?php 
        }		

	}
}
global $bookingpress_pro_import_export;
$bookingpress_pro_import_export = new bookingpress_pro_import_export();
