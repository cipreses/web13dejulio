<?php
if ( ! class_exists( 'BookingPressPro' ) ) {
	class BookingPressPro {

		public $is_pro_reinstall = false;

		function __construct() {
			global $wp, $wpdb, $BookingPressPro, $tbl_bookingpress_extra_services, $tbl_bookingpress_staff_member_workhours, $tbl_bookingpress_staffmembers_daysoff, $tbl_bookingpress_coupons,$tbl_bookingpress_cron_email_notifications_logs,$tbl_bookingpress_staffmembers,$tbl_bookingpress_staffmembers_meta,$tbl_bookingpress_staffmembers_services,$tbl_bookingpress_subscription_details,$tbl_bookingpress_staffmembers_special_day,$tbl_bookingpress_staffmembers_special_day_breaks, $tbl_bookingpress_debug_integration_logs,$tbl_bookingpress_default_special_day, $tbl_bookingpress_service_workhours,$tbl_bookingpress_service_special_day, $tbl_bookingpress_default_special_day_breaks,$tbl_bookingpress_service_special_day_breaks, $tbl_bookingpress_appointment_meta, $tbl_bookingpress_reschedule_history, $tbl_bookingpress_guests_data, $tbl_bookingpress_service_daysoff;

			$tbl_bookingpress_extra_services                  = $wpdb->prefix . 'bookingpress_extra_services';
			$tbl_bookingpress_staff_member_workhours          = $wpdb->prefix . 'bookingpress_staff_member_workhours';
			$tbl_bookingpress_staffmembers_daysoff            = $wpdb->prefix . 'bookingpress_staffmembers_daysoff';
			$tbl_bookingpress_coupons                         = $wpdb->prefix . 'bookingpress_coupons';
			$tbl_bookingpress_cron_email_notifications_logs   = $wpdb->prefix . 'bookingpress_cron_email_notification_logs';
			$tbl_bookingpress_staffmembers                    = $wpdb->prefix . 'bookingpress_staffmembers';
			$tbl_bookingpress_staffmembers_meta               = $wpdb->prefix . 'bookingpress_staffmembers_meta';
			$tbl_bookingpress_staffmembers_services           = $wpdb->prefix . 'bookingpress_staffmembers_services';
			$tbl_bookingpress_subscription_details            = $wpdb->prefix . 'bookingpress_subscription_details';
			$tbl_bookingpress_staffmembers_special_day        = $wpdb->prefix . 'bookingpress_staffmembers_special_day';
			$tbl_bookingpress_staffmembers_special_day_breaks = $wpdb->prefix . 'bookingpress_staffmembers_special_day_breaks';
			$tbl_bookingpress_debug_integration_logs          = $wpdb->prefix . 'bookingpress_debug_integration_logs';
			$tbl_bookingpress_default_special_day             = $wpdb->prefix . 'bookingpress_default_special_day';
			$tbl_bookingpress_service_workhours               = $wpdb->prefix . 'bookingpress_service_workhours';
			$tbl_bookingpress_service_special_day             = $wpdb->prefix . 'bookingpress_service_special_day';
			$tbl_bookingpress_service_special_day_breaks      = $wpdb->prefix . 'bookingpress_service_special_day_breaks';
			$tbl_bookingpress_default_special_day_breaks      = $wpdb->prefix . 'bookingpress_default_special_day_breaks';
			$tbl_bookingpress_appointment_meta                = $wpdb->prefix . 'bookingpress_appointment_meta';
			$tbl_bookingpress_reschedule_history              = $wpdb->prefix . 'bookingpress_reschedule_history';
			$tbl_bookingpress_guests_data					  = $wpdb->prefix . 'bookingpress_guests_data';
			$tbl_bookingpress_service_daysoff				  = $wpdb->prefix . 'bookingpress_service_daysoff';

			register_activation_hook( BOOKINGPRESS_DIR_PRO . '/bookingpress-appointment-booking-pro.php', array( 'BookingPressPro', 'install' ) );
			register_uninstall_hook( BOOKINGPRESS_DIR_PRO . '/bookingpress-appointment-booking-pro.php', array( 'BookingPressPro', 'uninstall' ) );

			add_filter( 'bookingpress_modify_header_content', array( $this, 'bookingpress_modify_header_content_func' ),10,2 );

			add_action( 'init', array( $this, 'bookingpress_modify_header_content_func' ),10,2 );

			add_action( 'admin_notices', array( $this, 'bookingpress_pro_admin_notices' ) );

			add_action('bookingpress_modify_readmore_link', array($this, 'bookingpress_modify_readmore_link_func'));

			add_action( 'admin_enqueue_scripts', array( $this, 'set_css' ), 11 );
			add_action( 'wp_head', array( $this, 'set_front_css' ), 1 );

			add_action( 'admin_enqueue_scripts', array( $this, 'set_js' ), 11 );

			add_action( 'admin_menu', array( $this, 'bookingpress_pro_menu' ), 27 );

			add_action( 'wp_ajax_bookingpress_dismisss_pro_admin_notice',array($this,'bookingpress_dismisss_pro_admin_notice_func'));

			add_action( 'wp_ajax_bookingpress_dismiss_licence_notice',array($this,'bookingpress_dismiss_licence_notice_func'));			

			add_action( 'wp_ajax_bookingpress_dismiss_license_expiry_notice', array( $this, 'bookingpress_dismiss_license_expiry_notice_func') );

			add_action( 'wp_ajax_bookingpress_dismiss_cron_notice', array( $this, 'bookingpress_dismiss_cron_notice_func') );

			add_action( 'bookingpress_admin_vue_data_variables_script', array( $this, 'bookingpress_admin_vue_data_variable_script_func' ) );

			add_action( 'bookingpress_admin_vue_on_load_script', array( $this, 'bookingpress_admin_vue_on_load_script_func' ), 10 );

			add_action( 'bookingpress_integration_log_entry', array( $this, 'bookingpress_write_integration_logs' ), 10, 6 );

			add_action( 'init', array( $this, 'bookingpress_pro_page_slugs' ) );

			add_filter( 'bookingpress_modify_debug_log_data', array( $this, 'bookingpress_modify_debug_log_data_func' ), 10, 2 );
			add_filter( 'bookingpress_modify_download_debug_log_query', array( $this, 'bookingpress_modify_download_debug_log_query_func' ), 10, 3 );
			add_action( 'bookingpress_delete_debug_log_from_outside', array( $this, 'bookingpress_clear_debug_payment_log_func' ), 10 );
			//add_action( 'wp_ajax_bookingpress_clear_integration_log', array( $this, 'bookingpress_clear_integration_log_func' ), 10 );

			add_action( 'init', array( $this, 'bookingpress_logout_func' ) );

			// add_filter('bookingpress_modify_default_daysoff_details', array($this, 'bookingpress_modify_default_daysoff_details_func'), 10, 3);

			add_filter( 'bookingpress_modify_service_time', array( $this, 'bookingpress_modify_service_time_func' ), 10, 6 );

			add_action('bookingpress_add_frontend_js', array($this, 'bookingpress_add_more_front_js'));

			add_filter('bookingpress_change_time_slot_format',array($this,'bookingpress_change_time_slot_format_func'));

			add_action('bookingpress_admin_view_filter',array($this,'bookingpress_admin_view_filter_func'));

			add_action('admin_init',array($this,'bookingpress_pro_upgrade_data'));

			add_filter( 'bookingpress_retrieve_pro_modules_timeslots', array( $this, 'bookingpress_get_default_special_days'), 12, 6 );
			add_filter( 'bpa_calculate_default_sp_day_workdata', array( $this, 'bookingpress_calculate_default_spday_workdata'), 10, 2 );
			
			add_filter('bookingpress_generate_my_booking_customize_css',array($this,'bookingpress_generate_my_booking_customize_css_func'),10,2);

			add_filter('bookingpress_generate_booking_form_customize_css',array($this,'bookingpress_generate_booking_form_customize_css_func'),10,2);
			add_action('bookingpress_add_frontend_css',array($this,'bookingpress_generate_booking_form_customize_css_loader_func'));

			//Wizard hooks
			add_filter('bookingpress_wizard_dynamic_view_load', array( $this, 'bookingpress_load_wizard_view_func'), 10);
			add_action('bookingpress_wizard_dynamic_vue_methods', array( $this, 'bookingpress_wizard_vue_methods_func'));
			add_action('bookingpress_wizard_dynamic_on_load_methods', array( $this, 'bookingpress_wizard_on_load_methods_func'));
			add_action('bookingpress_wizard_dynamic_data_fields', array( $this, 'bookingpress_wizard_dynamic_data_fields_func'));
			add_action('bookingpress_wizard_dynamic_helper_vars', array( $this, 'bookingpress_wizard_dynamic_helper_vars_func'));

			//Save wizard settings
			add_action('wp_ajax_bookingpress_save_wizard_settings', array($this, 'bookingpress_save_wizard_settings_func'));
			
			add_action('wp_ajax_bookingpress_skip_wizard', array($this, 'bookingpress_skip_wizard_func'));

			add_action('wp_ajax_bookingpress_license_verification', array($this, 'bookingpress_license_verification_func'));

			add_action( 'bookingpress_admin_panel_vue_methods', array( $this, 'bookingpress_admin_common_vue_methods') );

			add_action('user_register', array($this,'bookingpress_pro_add_capabilities_to_new_user'));
			add_action('set_user_role', array($this, 'bookingpress_pro_assign_caps_on_role_change'), 10, 3);

			add_filter('bookingpress_modify_capability_data', array($this, 'bookingpress_modify_capability_data_func'), 10, 1);

			//Allow staffmember to login and access wp-admin when woocommerce installed
			add_filter('woocommerce_prevent_admin_access', array($this, 'woocommerce_prevent_admin_access_func'));
			
			add_action( 'bookingpress_pro_reactivate_plugin', array( $this, 'bookingpress_attempt_installing_columns_on_reactivation') );
			add_action( 'admin_init', array( $this, 'bookingpress_attempt_installing_columns') );
			
			add_action( 'bpa_add_extra_tab_outside_func', array( $this,'bpa_add_extra_tab_outside_func_arr'));

			add_filter( 'bookingpress_booked_appointment_with_share_timeslot_where_clause_check', array( $this, 'bookingpress_booked_appointment_with_share_timeslot_where_clause_check_func'), 10, 2);

			add_action( 'bookingpress_page_admin_notices', array( $this, 'bookingpress_admin_license_notices') );

			add_filter( 'bookingpress_modify_disable_date_start_date', array( $this, 'bookingpress_modify_start_date_with_minimum_time'), 10, 3 );

			add_filter( 'bookingpress_modify_default_holidays', array( $this, 'bookingpress_modify_default_holidays_with_general_special_days'), 7, 4 );

			add_filter( 'plugin_action_links_bookingpress-appointment-booking/bookingpress-appointment-booking.php', array( $this, 'bookingpress_remove_deactivation_link')  );

		}

		function bookingpress_remove_deactivation_link( $links ){

			if( isset( $links['deactivate'] ) ){
				unset( $links['deactivate'] );
			}

			$required_note = esc_html__( 'Required by BookingPress - Premium', 'bookingpress-appointment-booking' );

			array_unshift( $links, '<label class="bookingpress-pro-notice-plugin-label">'.$required_note.'</label>' );

			return $links;
		}

		function bookingpress_modify_start_date_with_minimum_time( $bookingpress_start_date, $selected_service, $start_date_with_time ){

			global $wpdb, $tbl_bookingpress_services, $bookingpress_services, $BookingPress;

			$min_time_req_service = $bookingpress_services->bookingpress_get_service_meta( $selected_service, 'minimum_time_required_before_booking');
			
			if( 'disabled' == $min_time_req_service ){
				return $bookingpress_start_date;
			}

			if( 'inherit' == $min_time_req_service || empty( $min_time_req_service ) ){
				
				$def_min_time_for_booking = $BookingPress->bookingpress_get_settings( 'default_minimum_time_for_booking', 'general_setting' );
				if( 'disabled' == $def_min_time_for_booking ){
					return $bookingpress_start_date;
				}

			} else {
				$def_min_time_for_booking = $min_time_req_service;
			}

			$min_time_with_date = date( 'Y-m-d H:i:s', strtotime( $start_date_with_time . ' +' . $def_min_time_for_booking . ' minutes' ) );

			if( date('Y-m-d', strtotime( $min_time_with_date ) ) == $bookingpress_start_date ){
				/** Time calculation with the working hours */
			} else {
				$bookingpress_start_date = $min_time_with_date;
			}

			return $bookingpress_start_date;
		}

		function bookingpress_admin_license_notices(){
			global $BookingPress, $bookingpress_pro_settings;

			if( !current_user_can( 'administrator' ) ){
				return;
			}

			if( ( isset($_REQUEST['page']) && ($_REQUEST['page'] == 'bookingpress_lite_wizard' || $_REQUEST['page'] == 'bookingpress_wizard' || $_REQUEST['page'] == 'bookingpress_growth_tools') )){
				return;
			}

			$is_license_activated = $bookingpress_pro_settings->bpa_validate_license_key();
			if( !empty( $is_license_activated ) ){
				?>
				<div class="bpa-pg-warning-belt-box" v-if="is_licence_activated != ''">
					<p class="bpa-wbb__desc">
						<span class="material-icons-round bpa-wbb__desc-icon">warning</span>
						<span class="bpa-wbb__desc-content"><?php echo $is_license_activated;//phpcs:ignore ?></span>
					</p>
					<span class="bpa-uwb-close-icon material-icons-round" @click="bookingpress_close_licence_notice">close</span>
				</div>
				<?php
			} else {
				//$bookingpress_pro_settings
				$is_license_expired = $bookingpress_pro_settings->bpa_validate_license_expiry();
				if( true == $is_license_expired ){
					?>
					<div class="bpa-pg-warning-belt-box bpa-error" v-if="is_license_expired == true">
						<p class="bpa-wbb__desc">
							<span class="material-icons-round bpa-wbb__desc-icon">warning</span>
							<span class="bpa-wbb__desc-content"><?php echo sprintf( esc_html__('It seems that your BookingPress license has expired. To continue receiving the updates and technical support, please renew your license from %s', 'bookingpress-appointment-booking'), '<a href="https://www.bookingpressplugin.com/manage-licenses" target="_blank">'.esc_html__('here', 'bookingpress-appointment-booking').'</a>'  ); //phpcs:ignore ?></span>
						</p>
						<span class="bpa-uwb-close-icon material-icons-round" @click="bookingpress_close_license_expiry_notice">close</span>
					</div>
					<?php
				}
			}

			$check_wp_cron_status = $bookingpress_pro_settings->bpa_check_wp_cron_status();

			if( false == $check_wp_cron_status ){
				$disabled_feature_lists = array(
					esc_html__( 'Scheduled Email Notifications', 'bookingpress-appointment-booking')
				);
				$disabled_feature_lists = apply_filters( 'bookingpress_disabled_features_with_cron', $disabled_feature_lists);
				$wp_cron_notification_msg = sprintf( esc_html__( 'It seems that WordPress default cron has been disabled on your website and the following feature(s) will not work properly. Please enable WordPress default cron or set up %s, so that the BookingPress feature(s) works smoothly.', 'bookingpress-appointment-booking'), '<a href="https://www.bookingpressplugin.com/documents/set-schedule-notifications-cronjob/" target="_blank">'.esc_html__('external cron', 'bookingpress-appointment-booking').'</a>') ?> <?php //phpcs:ignore ?>
				<div class="bpa-pg-warning-belt-box bpa-error" v-if="is_display_cron_notice == true">
					<p class="bpa-wbb__desc">
						<span class="material-icons-round bpa-wbb__desc-icon">warning</span>
						<span class="bpa-wbb__desc-content"><?php echo $wp_cron_notification_msg; //phpcs:ignore ?></span>
					</p>
					<?php
						if( !empty( $disabled_feature_lists ) ){
							echo '<ul class="bpa-pg-warning-lists">';
								foreach( $disabled_feature_lists as $list_item ){
									echo '<li>'. esc_html($list_item) . '</li>';
								}
							echo '</ul>';
						}
					?>
					<span class="bpa-pg-warning-belt-footer">
						<span class="bpa-pg-warning-belt-footer-link" @click="bookingpress_hide_cron_notice_for_later"><?php esc_html_e('Show me later', 'bookingpress-appointment-booking'); ?></span>
						<span class="bpa-pg-warning-belt-footer-link" @click="bookingpress_hide_cron_notice_forever"><?php esc_html_e('Already set external cron', 'bookingpress-appointment-booking'); ?></span>
					</span>
				</div>
				<?php
			}
		}

		function bookingpress_booked_appointment_with_share_timeslot_where_clause_check_func($where_clause,$service_id = 0){
			global $BookingPress,$tbl_bookingpress_services,$wpdb;
			$bookingpress_share_timeslot_between_services_type = $BookingPress->bookingpress_get_settings('share_timeslot_between_services_type', 'general_setting');			
			
			if($bookingpress_share_timeslot_between_services_type == 'service_category'){
				if(!isset($_POST['appointment_data_obj']) && isset($_POST['appointment_data'])){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
					$_POST['appointment_data_obj'] = $_POST['appointment_data']; // phpcs:ignore
				}
				if(isset($_POST['appointment_data_obj']) && isset($_POST['appointment_data_obj']['selected_category']) && isset($_POST['appointment_data_obj']['related_category_service'])){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
					
					$bookingpress_related_services = array();
					$bookingpress_selected_category = (isset($_POST['appointment_data_obj']['selected_category']))? intval($_POST['appointment_data_obj']['selected_category'] ):''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
					$bookingpress_all_related_category_service = (isset($_POST['appointment_data_obj']['related_category_service']))?$_POST['appointment_data_obj'] ['related_category_service']:''; //phpcs:ignore

					
					
					if($bookingpress_selected_category == 0){
						$selected_service = (isset($_POST['appointment_data_obj']['selected_service']))? intval($_POST['appointment_data_obj']['selected_service']):''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
						if(!empty($bookingpress_all_related_category_service) && !empty($selected_service)){
							foreach ($bookingpress_all_related_category_service as $category_id => $category_wise_services) {
								if(!empty($category_wise_services) && is_array($category_wise_services)){
									$found = array_search($selected_service, $category_wise_services);
									if ($found !== false) {
										$bookingpress_selected_category = $category_id;
										break; // Stop searching after finding the value
									} 
								}
							}
						}
					}

					$bpa_fetch_all_services = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_service_id FROM {$tbl_bookingpress_services} WHERE bookingpress_category_id = %d", $bookingpress_selected_category ), ARRAY_A );

					if( !empty( $bpa_fetch_all_services ) ){
						foreach( $bpa_fetch_all_services as $services_data ){
							array_push( $bookingpress_related_services, $services_data['bookingpress_service_id'] );
						}
					}
					
					if(empty($bookingpress_related_services)){
						$selected_service = (isset($_POST['appointment_data_obj']['selected_service']))? intval($_POST['appointment_data_obj']['selected_service']):''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
						//$bookingpress_related_services = array($selected_service);					
					}
					if(!empty($bookingpress_related_services)){
						$bookingpress_related_services_ids = implode(",",$bookingpress_related_services);
						if(!empty($bookingpress_related_services_ids)){
							$where_clause.= ' AND bookingpress_service_id IN ('.$bookingpress_related_services_ids.') ';
						}						
					}

				}else{

					if($service_id){
						
						$bookingpress_related_services = array();
						$bookingpress_service_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_category_id FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d ", $service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm 
						$bookingpress_category_id = (isset($bookingpress_service_data['bookingpress_category_id']))?$bookingpress_service_data['bookingpress_category_id']:'';
						if((!empty($bookingpress_category_id) || $bookingpress_category_id == 0) && $bookingpress_category_id != ''){
							$bookingpress_service_data = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_service_id FROM {$tbl_bookingpress_services} WHERE bookingpress_category_id = %d ", $bookingpress_category_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm 	
							foreach($bookingpress_service_data as $serv_data){
								$bookingpress_related_services[] = $serv_data['bookingpress_service_id'];
							}
						}
						if(empty($bookingpress_related_services)){
							//$bookingpress_related_services[] = $service_id;
						}
						if(!empty($bookingpress_related_services)){
							$bookingpress_related_services_ids = implode(",",$bookingpress_related_services);
							if(!empty($bookingpress_related_services_ids)){
								$where_clause.= ' AND bookingpress_service_id IN ('.$bookingpress_related_services_ids.') ';
							}							
						}

					}

				}
			}
			return $where_clause;
		}		
		
		public function bookingpress_get_related_services_using_service_id($service_id = ""){
			global $wpdb,$tbl_bookingpress_services; 
			$bookingpress_related_services = array();
			if(!empty($service_id)){
				$bookingpress_service_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_category_id FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d ", $service_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm 
				$bookingpress_category_id = (isset($bookingpress_service_data['bookingpress_category_id']))?$bookingpress_service_data['bookingpress_category_id']:'';
				if((!empty($bookingpress_category_id) || $bookingpress_category_id == 0) && $bookingpress_category_id != ''){
					$bookingpress_service_data = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_service_id FROM {$tbl_bookingpress_services} WHERE bookingpress_category_id = %d ", $bookingpress_category_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm 	
					foreach($bookingpress_service_data as $serv_data){
						$bookingpress_related_services[] = $serv_data['bookingpress_service_id'];
					}
				}				
			}
			return $bookingpress_related_services;
		}

		/**
		 * bpa function for check valid callback.
		 *
		 * @return void
		*/
		public function bookingpress_bpa_check_valid_connection_callback_func(){
			$bookingpress_is_valid = 0;
			if (! function_exists('is_plugin_active') ) {
                include ABSPATH . '/wp-admin/includes/plugin.php';
            }			
			if(is_plugin_active('bookingpress-mobile-connect/bookingpress-mobile-connect.php')){
				global $bookingpress_mobile_connect_api;
				if (class_exists('bookingpress_mobile_connect_api') && method_exists( $bookingpress_mobile_connect_api, 'bookingpress_api_authorization')) {					
					$bookingpress_is_valid_data = $bookingpress_mobile_connect_api->bookingpress_api_authorization(array(),true);
					$bookingpress_is_valid = (isset($bookingpress_is_valid_data['status']))?$bookingpress_is_valid_data['status']:0;
				}
			}
			return $bookingpress_is_valid;
		}
		
		public function bookingpress_pro_front_language_translation_func($language_translation_str,$bookingpress_element_type,  $bp_translation_str_ref, $bp_translation_str_ref_id, $bp_translation_lang=''){
								
			$language_translation_str = apply_filters( 'bookingpress_add_language_translate_data',$language_translation_str,$bookingpress_element_type,  $bp_translation_str_ref, $bp_translation_str_ref_id, $bp_translation_lang); 			
			return $language_translation_str;
		}

		function bpa_add_extra_tab_outside_func_arr(){ ?>

			if( bpa_get_page == 'bookingpress_settings'){
				var selected_tab_name = sessionStorage.getItem("current_tabname");
				if( selected_tab_name == 'customer_settings'){
					vm.openNeedHelper('list_customer_settings', 'customer_settings', 'Customer Settings');
					vm.bpa_fab_floating_btn = 0;

 				} else if( selected_tab_name == 'license_settings' ){
					vm.openNeedHelper("list_license_settings", "license_settings", "License");
					vm.bpa_fab_floating_btn = 0;

				} else if( selected_tab_name == 'specialday_settings' ){
					vm.openNeedHelper('list_special_days_settings', 'special_days_settings', 'Special Days Settings');
					vm.bpa_fab_floating_btn = 0;

				} else if( selected_tab_name == 'staffmembers_settings' ){
					vm.openNeedHelper('list_staffmembers_settings', 'staffmembers_settings', 'Staff Members Settings');
					vm.bpa_fab_floating_btn = 0;
				} 
			} else if( bpa_get_page == 'bookingpress_staff_members'){
				vm.openNeedHelper('list_staff_members_settings', 'staff_members_settings', 'Staff Members');
				vm.bpa_fab_floating_btn = 0;

			} else if( bpa_get_page == 'bookingpress_reports'){
				vm.openNeedHelper('list_reports', 'reports', 'Reports');
				vm.bpa_fab_floating_btn = 0;

			} else if( bpa_get_page == 'bookingpress_coupons'){
				vm.openNeedHelper('list_coupons', 'coupons', 'Coupons');
				vm.bpa_fab_floating_btn = 0;

			} else if( bpa_get_page == 'bookingpress_myprofile' || bpa_get_page == 'bookingpress_timesheet' ){
				vm.openNeedHelper('list_staff_members_settings', 'staff_members_settings', 'Staff Members');
				vm.bpa_fab_floating_btn = 0;
			}else if( bpa_get_page == 'bookingpress_myservices'){
				vm.openNeedHelper('list_services', 'services', 'Services');
                vm.bpa_fab_floating_btn = 0;
			}
			
		<?php }

		function bookingpress_attempt_installing_columns(){
			
			$is_reattempt = get_option( 'bookingpress_reattempt_installer' );

			if( true == $is_reattempt ){
				global $BookingPressPro;
				$BookingPressPro->bookingpress_install_pro_plugin_data();
			}

		}

		function bookingpress_attempt_installing_columns_on_reactivation(){

			/** Check to re-attempt installing columns */

			//$BookingPressPro->bookingpress_install_pro_plugin_data();

			global $tbl_bookingpress_appointment_bookings,$BookingPressPro;

			if( empty( $tbl_bookingpress_appointment_bookings ) ){
				update_option('bookingpress_reattempt_installer', true);
			} else {
				/** get update_option for the installed columns and set condition based on the missing column  */
				$bpa_missing_columns = get_option( 'bookingpress_missing_columns' );
				$bpa_missing_columns = json_decode( $bpa_missing_columns, true );
				if( !empty( $bpa_missing_columns ) ){
					$BookingPressPro->bookingpress_install_pro_plugin_data();
				}
			}
		}

		function woocommerce_prevent_admin_access_func($prevent_access){
			if(!current_user_can('administrator')){
				global $wpdb, $tbl_bookingpress_staffmembers;
				$get_logged_in_user_id = get_current_user_id();

				$bookingpress_is_staff_exists = $wpdb->get_var($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $get_logged_in_user_id)); // phpcs:ignore

				if( !empty($bookingpress_is_staff_exists) && $bookingpress_is_staff_exists > 0 ){
					$prevent_access = false;
				}
			}
			return $prevent_access;
		}

		function bookingpress_modify_capability_data_func($bpa_caps){
			if(empty($bpa_caps['bookingpress_staff_members'])){
				$bpa_caps['bookingpress_staff_members'] = array();	
			}

			if(empty($bpa_caps['bookingpress_coupons'])){
				$bpa_caps['bookingpress_coupons'] = array();	
			}

			if(empty($bpa_caps['bookingpress_reports'])){
				$bpa_caps['bookingpress_reports'] = array();	
			}

			if(empty($bpa_caps['bookingpress_timesheet'])){
				$bpa_caps['bookingpress_timesheet'] = array();
			}

			if(empty($bpa_caps['bookingpress_myprofile'])){
				$bpa_caps['bookingpress_myprofile'] = array();
			}

			$bpa_caps['bookingpress'][] = 'get_appointment_meta_value';
			$bpa_caps['bookingpress'][] = 'admin_recalculate_appointment_details';
			$bpa_caps['bookingpress'][] = 'apply_coupon_code_backend';
			$bpa_caps['bookingpress'][] = 'export_appointment_details';
			$bpa_caps['bookingpress'][] = 'get_backend_service_extras_details';
			$bpa_caps['bookingpress'][] = 'get_appointment_customer_details';

			$bpa_caps['bookingpress_calendar'][] = 'get_appointment_meta_value';
			$bpa_caps['bookingpress_calendar'][] = 'admin_recalculate_appointment_details';
			$bpa_caps['bookingpress_calendar'][] = 'apply_coupon_code_backend';
			$bpa_caps['bookingpress_calendar'][] = 'export_appointment_details';
			$bpa_caps['bookingpress_calendar'][] = 'get_backend_service_extras_details';
			$bpa_caps['bookingpress_calendar'][] = 'get_appointment_customer_details';

			$bpa_caps['bookingpress_appointments'][] = 'get_appointment_meta_value';
			$bpa_caps['bookingpress_appointments'][] = 'admin_recalculate_appointment_details';
			$bpa_caps['bookingpress_appointments'][] = 'apply_coupon_code_backend';
			$bpa_caps['bookingpress_appointments'][] = 'bulk_appointment_actions';
			$bpa_caps['bookingpress_appointments'][] = 'export_appointment_details';
			$bpa_caps['bookingpress_appointments'][] = 'get_backend_service_extras_details';
			$bpa_caps['bookingpress_appointments'][] = 'get_appointment_customer_details';
			$bpa_caps['bookingpress_appointments'][] = 'get_refund_amount';
			$bpa_caps['bookingpress_appointments'][] = 'apply_for_refund';			

			$bpa_caps['bookingpress_payments'][] = 'bulk_payment_actions';
			$bpa_caps['bookingpress_payments'][] = 'export_payment_details';

			$bpa_caps['bookingpress_services'][] = 'get_staffmember_services';
			$bpa_caps['bookingpress_services'][] = 'get_extra_services_data';
			$bpa_caps['bookingpress_services'][] = 'get_service_special_days';
			$bpa_caps['bookingpress_services'][] = 'get_service_workhour_details';
			$bpa_caps['bookingpress_services'][] = 'save_service_shift_mgmt_details';
			$bpa_caps['bookingpress_services'][] = 'format_service_extra_amount';
			$bpa_caps['bookingpress_services'][] = 'validate_service_special_days';
			$bpa_caps['bookingpress_services'][] = 'change_service_status';
			$bpa_caps['bookingpress_services'][] = 'format_service_special_days';
			$bpa_caps['bookingpress_services'][] = 'format_assigned_staff_service_amount';
			$bpa_caps['bookingpress_services'][] = 'get_service_shift_managment';
			$bpa_caps['bookingpress_services'][] = 'get_service_days_off';
			$bpa_caps['bookingpress_services'][] = 'validate_service_daysoff_details';
			$bpa_caps['bookingpress_services'][] = 'format_service_daysoff_data';

			$bpa_caps['bookingpress_staff_members'][] = 'retrieve_staffmembers';
			$bpa_caps['bookingpress_staff_members'][] = 'retrieve_workhours';
			$bpa_caps['bookingpress_staff_members'][] = 'change_staffmember_status';
			$bpa_caps['bookingpress_staff_members'][] = 'get_staff_assigned_service_data';
			$bpa_caps['bookingpress_staff_members'][] = 'edit_staffmember_details';
			$bpa_caps['bookingpress_staff_members'][] = 'get_staff_yearly_daysoff_details';
			$bpa_caps['bookingpress_staff_members'][] = 'get_staff_special_days_details';
			$bpa_caps['bookingpress_staff_members'][] = 'get_staff_workhour_details';
			$bpa_caps['bookingpress_staff_members'][] = 'validate_staff_daysoff_details';
			$bpa_caps['bookingpress_staff_members'][] = 'format_staffmember_daysoff_data';
			$bpa_caps['bookingpress_staff_members'][] = 'validate_staffmember_special_days';
			$bpa_caps['bookingpress_staff_members'][] = 'format_staff_special_days_details';
			$bpa_caps['bookingpress_staff_members'][] = 'add_staffmembers_details';
			$bpa_caps['bookingpress_staff_members'][] = 'export_staffmembers_details';
			$bpa_caps['bookingpress_staff_members'][] = 'bulk_staffmembers_actions';
			$bpa_caps['bookingpress_staff_members'][] = 'upload_staffmember_avatar';
			$bpa_caps['bookingpress_staff_members'][] = 'delete_staffmember_details';
			$bpa_caps['bookingpress_staff_members'][] = 'get_staffmember_wpusers';
			$bpa_caps['bookingpress_staff_members'][] = 'format_assigned_service_amount';
			$bpa_caps['bookingpress_staff_members'][] = 'update_my_profile_details';
			$bpa_caps['bookingpress_staff_members'][] = 'timeslot_add_daysoff';
			$bpa_caps['bookingpress_staff_members'][] = 'get_timesheet_daysoff_details';
			$bpa_caps['bookingpress_staff_members'][] = 'delete_timesheet_daysoff';
			$bpa_caps['bookingpress_staff_members'][] = 'timesheet_add_staffmember_special_days';
			$bpa_caps['bookingpress_staff_members'][] = 'timesheet_get_special_days';
			$bpa_caps['bookingpress_staff_members'][] = 'timesheet_get_workhour_details';
			$bpa_caps['bookingpress_staff_members'][] = 'timesheet_add_workhour_details';
			$bpa_caps['bookingpress_staff_members'][] = 'timesheet_delete_staffmember_special_days';
			$bpa_caps['bookingpress_staff_members'][] = 'timesheet_validate_staff_special_days';
			$bpa_caps['bookingpress_staff_members'][] = 'manage_staffmemebr_position';
			$bpa_caps['bookingpress_staff_members'][] = 'get_staffmember_shift_managment';			

			$bpa_caps['bookingpress_coupons'][] = 'get_coupon_details';
			$bpa_caps['bookingpress_coupons'][] = 'change_coupon_status';
			$bpa_caps['bookingpress_coupons'][] = 'get_edit_coupon_details';
			$bpa_caps['bookingpress_coupons'][] = 'save_coupon_details';
			$bpa_caps['bookingpress_coupons'][] = 'bulk_coupon_actions';
			$bpa_caps['bookingpress_coupons'][] = 'delete_coupons';

			$bpa_caps['bookingpress_reports'][] = 'get_appointment_report_chart_details';
			$bpa_caps['bookingpress_reports'][] = 'get_appointment_report_details';
			$bpa_caps['bookingpress_reports'][] = 'get_revenue_report_chart_details';
			$bpa_caps['bookingpress_reports'][] = 'get_revenue_report_details';
			$bpa_caps['bookingpress_reports'][] = 'get_customer_report_chart_details';
			$bpa_caps['bookingpress_reports'][] = 'get_customer_report_details';

			$bpa_caps['bookingpress_addons'][] = 'deactivate_default_module';
			$bpa_caps['bookingpress_addons'][] = 'activate_default_module';
			$bpa_caps['bookingpress_addons'][] = 'bpa_activate_plugin';
			$bpa_caps['bookingpress_addons'][] = 'deactivate_plugin';
			$bpa_caps['bookingpress_addons'][] = 'get_remote_addons_list';

			$bpa_caps['bookingpress_notifications'][] = 'load_custom_notifications';
			$bpa_caps['bookingpress_notifications'][] = 'save_custom_notification_data';
			$bpa_caps['bookingpress_notifications'][] = 'get_custom_notification_data';
			$bpa_caps['bookingpress_notifications'][] = 'delete_custom_email_notification';
			$bpa_caps['bookingpress_notifications'][] = 'save_custom_notification';
			
			$bpa_caps['bookingpress_customize'][] = 'load_preset_field_settings_data';

			$bpa_caps['bookingpress_customers'][] = 'export_customer_details';

			$bpa_caps['bookingpress_payments'][] = 'save_manual_payment_details';
			$bpa_caps['bookingpress_payments'][] = 'send_payment_link';

			$bpa_caps['bookingpress_settings'][] = 'save_settings_default_special_days';
			$bpa_caps['bookingpress_settings'][] = 'get_settings_default_special_days';
			$bpa_caps['bookingpress_settings'][] = 'validate_settings_special_days';
			$bpa_caps['bookingpress_settings'][] = 'format_settings_special_days';
			$bpa_caps['bookingpress_settings'][] = 'verify_settings_customer_field_meta_key';
			$bpa_caps['bookingpress_settings'][] = 'check_settings_field_deletion';
			$bpa_caps['bookingpress_settings'][] = 'validate_activate_license';
			$bpa_caps['bookingpress_settings'][] = 'deactivate_license_key';
			$bpa_caps['bookingpress_settings'][] = 'refresh_license_key';
			$bpa_caps['bookingpress_settings'][] = 'upload_company_icon';

			$bpa_caps['bookingpress'][] = 'save_wizard_settings_data';
			$bpa_caps['bookingpress'][] = 'skip_wizard_settings';
			$bpa_caps['bookingpress'][] = 'license_verification';

			$bpa_caps['bookingpress_timesheet'][] = 'timeslot_add_daysoff';
			$bpa_caps['bookingpress_timesheet'][] = 'get_timesheet_daysoff_details';
			$bpa_caps['bookingpress_timesheet'][] = 'delete_timesheet_daysoff';
			$bpa_caps['bookingpress_timesheet'][] = 'timesheet_add_staffmember_special_days';
			$bpa_caps['bookingpress_timesheet'][] = 'timesheet_get_special_days';
			$bpa_caps['bookingpress_timesheet'][] = 'timesheet_get_workhour_details';
			$bpa_caps['bookingpress_timesheet'][] = 'timesheet_add_workhour_details';
			$bpa_caps['bookingpress_timesheet'][] = 'timesheet_delete_staffmember_special_days';
			$bpa_caps['bookingpress_timesheet'][] = 'timesheet_validate_staff_special_days';
			$bpa_caps['bookingpress_timesheet'][] = 'validate_staff_daysoff_details';
			$bpa_caps['bookingpress_timesheet'][] = 'upload_staffmember_avatar';
			$bpa_caps['bookingpress_timesheet'][] = 'retrieve_workhours';
			
			$bpa_caps['bookingpress_myprofile'][] = 'update_my_profile_details';
			$bpa_caps['bookingpress_myprofile'][] = 'upload_staffmember_avatar';

			return $bpa_caps;
		}

		function bookingpress_retrieve_daysoff_repeat_label( $repeat_duration = 'forever', $repeat_frequency = 1, $repeat_frequency_type = 'year', $repeat_times = 1, $repeat_date = "" ){
			$dayoff_label = esc_html__( "Once Off", 'bookingpress-appointment-booking' );
			if( 'forever' == $repeat_duration ){
				if( 1 == $repeat_frequency ){
					if( 'day' == $repeat_frequency_type ){
						$dayoff_label = esc_html__( 'Repeat Daily', 'bookingpress-appointment-booking' );
					} else if( 'week' == $repeat_frequency_type ){
						$dayoff_label = esc_html__( 'Repeat Weekly', 'bookingpress-appointment-booking' );
					} else if( 'month' == $repeat_frequency_type ){
						$dayoff_label = esc_html__( 'Repeat Monthly', 'bookingpress-appointment-booking' );
					} else if( 'year' == $repeat_frequency_type ){
						$dayoff_label = esc_html__( 'Repeat Yearly', 'bookingpress-appointment-booking' );
					}
				} else {
					if( 'day' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Every %1$s Days', 'bookingpress-appointment-booking'), $repeat_frequency );
					} else if( 'week' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Every %1$s Weeks', 'bookingpress-appointment-booking'), $repeat_frequency );
					} else if( 'month' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Every %1$s Months', 'bookingpress-appointment-booking'), $repeat_frequency );
					} else if( 'year' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Every %1$s Years', 'bookingpress-appointment-booking'), $repeat_frequency );
					}
				}
			} else if( 'no_of_times' == $repeat_duration ){
				if( 1 == $repeat_frequency ){
					if( 'day' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Daily for %1$s times', 'bookingpress-appointment-booking'), $repeat_times );
					} else if( 'week' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Weekly for %1$s times', 'bookingpress-appointment-booking'), $repeat_times );
					} else if( 'month' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Monthy for %1$s times', 'bookingpress-appointment-booking'), $repeat_times );
					} else if ( 'year' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Yearly for %1$s times', 'bookingpress-appointment-booking'), $repeat_times );
					}
				} else {
					if( 'day' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Every %1$s Days for %2$s times', 'bookingpress-appointment-booking'), $repeat_frequency, $repeat_times );
					} else if( 'week' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Every %1$s Weeks for %2$s times', 'bookingpress-appointment-booking'), $repeat_frequency, $repeat_times );
					} else if( 'month' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Every %1$s Months for %2$s times', 'bookingpress-appointment-booking'), $repeat_frequency, $repeat_times );
					} else if ( 'year' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Every %1$s Years for %2$s times', 'bookingpress-appointment-booking'), $repeat_frequency, $repeat_times );
					}
				}
			} else if( 'until' == $repeat_duration ){
				if( 1 == $repeat_frequency ){
					if( 'day' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Daily until %1$s', 'bookingpress-appointment-booking'), date_i18n( get_option('date_format'), strtotime( $repeat_date ) ) );
					} else if( 'week' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Weekly until %1$s', 'bookingpress-appointment-booking'), date_i18n( get_option('date_format'), strtotime( $repeat_date ) ) );
					} else if( 'month' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Monthly until %1$s', 'bookingpress-appointment-booking'), date_i18n( get_option('date_format'), strtotime( $repeat_date ) ) );
					} else if ( 'year' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Yearly until %1$s', 'bookingpress-appointment-booking'), date_i18n( get_option('date_format'), strtotime( $repeat_date ) ) );
					}
				} else {
					if( 'day' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Every %1$s Days until %2$s', 'bookingpress-appointment-booking'), $repeat_frequency, date_i18n( get_option('date_format'), strtotime( $repeat_date ) ) );
					} else if( 'week' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Every %1$s Weeks until %2$s', 'bookingpress-appointment-booking'), $repeat_frequency, date_i18n( get_option('date_format'), strtotime( $repeat_date ) ) );
					} else if( 'month' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Monthly %1$s until %2$s', 'bookingpress-appointment-booking'), $repeat_frequency, date_i18n( get_option('date_format'), strtotime( $repeat_date ) ) );
					} else if ( 'year' == $repeat_frequency_type ){
						$dayoff_label = sprintf( esc_html__( 'Repeat Yearly %1$s until %2$s', 'bookingpress-appointment-booking'), $repeat_frequency, date_i18n( get_option('date_format'), strtotime( $repeat_date ) ) );
					}
				}
			}

			return $dayoff_label;
		}

		function bookingpress_pro_admin_notices() {
			if( !function_exists('is_plugin_active') ){
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$bookingpress_addon_url = 'https://bookingpressplugin.com/bpa_misc/addons_list.php';

			$bookingpress_addons_res = wp_remote_post(
				$bookingpress_addon_url,
				array(
					'method'    => 'POST',
					'timeout'   => 45,
					'body'      => array(
						'bookingpress_addon_list' => 1,
					),
				)
			);

			$addon_list_arr = array(
				'bookingpress_cart_module' => '1.0',
				'bookingpress_custom_service_duration' => '1.0',
				'bookingpress-invoice' => '1.0',
				'bookingpress_sms_gateway' => '1.0',
				'bookingpress_whatsapp_gateway' => '1.0',
			);
			if ( ! is_wp_error( $bookingpress_addons_res ) ) {
				$bookingpress_body_res = base64_decode( $bookingpress_addons_res['body'] );
				if ( ! empty( $bookingpress_body_res ) ) {
					$bookingpress_body_res = json_decode( $bookingpress_body_res, true );
					foreach($bookingpress_body_res as $key => $val) {
						$addon_list_arr[$val['addon_key']] = $val['addon_version'];
					}
				}
			}

			$bookingpress_cart_version = get_option( 'bookingpress_cart_module' );
			$bookingpress_custom_duration_version = get_option( 'bookingpress_custom_service_duration_version' );
			$bookingpress_tax_version = get_option( 'bookingpress_tax_module' );
			$bookingpress_invoice_version = get_option( 'bookingpress_invoice_version');
			$bookingpress_sms_version = get_option( 'bookingpress_sms_gateway');
			$bookingpress_whatsapp_version = get_option( 'bookingpress_whatsapp_gateway');

			$addon_notice = '';

			if(is_plugin_active('bookingpress-cart/bookingpress-cart.php')){
				if( version_compare( $bookingpress_cart_version, '2.2', '<' ) ){
					$addon_notice = esc_html__('BookingPress Pro Plugin Requires to update the BookingPress Cart Plugin to version 2.2 or higher.', 'bookingpress-appointment-booking');
				}
			}

			if( file_exists(WP_PLUGIN_DIR . '/bookingpress-cart/bookingpress-cart.php') && !empty($bookingpress_cart_version) && version_compare($bookingpress_cart_version,'1.8','<') && version_compare($addon_list_arr['bookingpress_cart_module'],'1.7','>')){
				$addon_notice = esc_html__('BookingPress - Cart Add-on version 1.8', 'bookingpress-appointment-booking');
			}
			if( file_exists(WP_PLUGIN_DIR . '/bookingpress-custom-service-duration/bookingpress-custom-service-duration.php') && !empty($bookingpress_custom_duration_version) && version_compare($bookingpress_custom_duration_version,'1.2','<') && version_compare($addon_list_arr['bookingpress_custom_service_duration'],'1.1','>')){
				$addon_notice .= !empty($addon_notice) ? ',' : '';
				$addon_notice .= esc_html__('BookingPress - Custom Service Duration Add-on version 1.2', 'bookingpress-appointment-booking');
			}
			if( file_exists(WP_PLUGIN_DIR . '/bookingpress-invoice/bookingpress-invoice.php') && !empty($bookingpress_invoice_version) && version_compare($bookingpress_invoice_version,'1.5','<') && version_compare($addon_list_arr['bookingpress-invoice'],'1.4','>')){
				$addon_notice .= !empty($addon_notice) ? ',' : '';
				$addon_notice .= esc_html__('BookingPress - Invoice Add-on version 1.5', 'bookingpress-appointment-booking');
			}
			if( file_exists(WP_PLUGIN_DIR . '/bookingpress-tax/bookingpress-tax.php') && !empty($bookingpress_tax_version) && version_compare($bookingpress_tax_version,'1.2','<') && version_compare($addon_list_arr['bookingpress_tax_module'],'1.1','>') ){
				$addon_notice .= !empty($addon_notice) ? ',' : '';
				$addon_notice .= esc_html__('BookingPress - Tax Add-on version 1.2', 'bookingpress-appointment-booking');
			}

			if( file_exists(WP_PLUGIN_DIR . '/bookingpress-whatsapp/bookingpress-whatsapp.php') && !empty($bookingpress_whatsapp_version) && version_compare($bookingpress_whatsapp_version,'1.3','<') && version_compare($addon_list_arr['bookingpress_whatsapp_gateway'],'1.2','>')){
				$addon_notice .= !empty($addon_notice) ? ',' : '';
				$addon_notice .= esc_html__('BookingPress - Whatsapp Add-on version 1.3', 'bookingpress-appointment-booking');
			}

			if( file_exists(WP_PLUGIN_DIR . '/bookingpress-sms/bookingpress-sms.php') && !empty($bookingpress_sms_version) && version_compare($bookingpress_sms_version,'1.4','<') && version_compare($addon_list_arr['bookingpress_sms_gateway'],'1.3','>') ){
				$addon_notice .= !empty($addon_notice) ? ',' : '';
				$addon_notice .= esc_html__('BookingPress - Sms Add-on version 1.4', 'bookingpress-appointment-booking');
			}

			if($addon_notice != '') {
				$addon_notice = esc_html__('One or more plugins need to be updated to its latest version.', 'bookingpress-appointment-booking').'( '.$addon_notice.' ).';
				echo "<div class='notice notice-error is-dismissible'><p>" .$addon_notice. "</p></div>";  //phpcs:ignore
			}
			$bookingpress_bring_anyone_changes_notice = get_option('bookingpress_bring_anyone_changes_notice');
			if($bookingpress_bring_anyone_changes_notice !='' && $bookingpress_bring_anyone_changes_notice == 1) {
				$addon_warning_notice = esc_html__('BookingPress "Bring anyone with you" module is now became "Quantity" module. Please refer the changes at', 'bookingpress-appointment-booking').' <a href="https://www.bookingpressplugin.com/documents/appointments/#add_quantity" rel="noopener" target="_blank">https://www.bookingpressplugin.com/documents/appointments/#add_quantity</a>';
				echo '<div class="notice notice-warning is-dismissible bpa_bring_anyone_error_notice" data-bookingpress_confirm="'.__('Are you sure you have confirm the changes?', 'bookingpress-appointment-booking').'"><p>'.$addon_warning_notice. '</p></div>'; //phpcs:ignore
			}
		}

		function bookingpress_dismisss_pro_admin_notice_func() {
			update_option('bookingpress_bring_anyone_changes_notice', 0);
		}

		function bookingpress_pro_assign_caps_on_role_change($user_id, $role, $old_roles){
			global $BookingPressPro;
			if(!empty($user_id) && $role == "administrator"){
				$bookingpress_pro_roles = $BookingPressPro->bookingpress_pro_capabilities();
				$userObj = new WP_User($user_id);
                foreach ($bookingpress_pro_roles as $bookingpress_role => $bookingpress_role_desc) {
                    $userObj->add_cap($bookingpress_role);
                }
                unset($bookingpress_role);
                unset($bookingpress_roles);
                unset($bookingpress_role_desc);
			}
		}

		function bookingpress_pro_add_capabilities_to_new_user($user_id){
			global $BookingPressPro;
			if(!empty($user_id) && user_can($user_id, 'administrator')){
				$bookingpress_pro_roles = $BookingPressPro->bookingpress_pro_capabilities();
				$userObj = new WP_User($user_id);
                foreach ($bookingpress_pro_roles as $bookingpress_role => $bookingpress_role_desc) {
                    $userObj->add_cap($bookingpress_role);
                }
                unset($bookingpress_role);
                unset($bookingpress_roles);
                unset($bookingpress_role_desc);
			}
		}

		function bookingpress_license_verification_func(){
			global $wpdb, $BookingPress;
			$response              = array();

			$posted_license_key = isset( $_REQUEST['bookingpress_license_key'] ) ? sanitize_text_field( $_REQUEST['bookingpress_license_key'] ) : '';
			$posted_license_package = isset( $_REQUEST['license_package'] ) ? sanitize_text_field( $_REQUEST['license_package'] ) : '';
			
			//$posted_license_package = '4534'; // STRIPE ADDON PRODUCT ID

			// retrieve the license from the database
			//$license = trim( get_option( 'edd_sample_license_key' ) );

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $posted_license_key,
				'item_id'  => $posted_license_package,
				//'item_name'  => urlencode( BOOKINGPRESS_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( BOOKINGPRESS_STORE_URL, array( 'timeout' => 15, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				$message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.', 'bookingpress-appointment-booking' );

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
				$license_data_string = wp_remote_retrieve_body( $response );

				if ( false === $license_data->success ) {

					switch( $license_data->error ) {

						case 'expired' :

							$message = sprintf(
								/* translators: Expiry date of license */
								__( 'Your license key expired on %s.', 'bookingpress-appointment-booking' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;

						case 'revoked' :

							$message = __( 'Your license key has been disabled.', 'bookingpress-appointment-booking' );
							break;

						case 'missing' :

							$message = __( 'Invalid license.', 'bookingpress-appointment-booking' );
							break;

						case 'invalid' :
						case 'site_inactive' :

							$message = __( 'Your license is not active for this URL.', 'bookingpress-appointment-booking' );
							break;

						case 'item_name_mismatch' :

							$message = __('This appears to be an invalid license key for your selected package.' , 'bookingpress-appointment-booking');
							break;
						
						case 'invalid_item_id' :

								$message = __('This appears to be an invalid license key for your selected package.' , 'bookingpress-appointment-booking');
								break;

						case 'no_activations_left':

							$message = __( 'Your license key has reached its activation limit.', 'bookingpress-appointment-booking' );
							break;

						default :

							$message = __( 'An error occurred, please try again.', 'bookingpress-appointment-booking' );
							break;
					}

				}

			}

			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {
				//$base_url = admin_url( 'plugins.php?page=' . EDD_SAMPLE_PLUGIN_LICENSE_PAGE );
				//$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

				//wp_redirect( $redirect );
				//exit();

				$response['variant'] = 'error';
				$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( $message, 'bookingpress-appointment-booking' ); //phpcs:ignore
				echo wp_json_encode( $response );
				die();
			}

			// $license_data->license will be either "valid" or "invalid"

			if($license_data->license === "valid")
			{
				update_option( 'bkp_license_key', $posted_license_key );
				update_option( 'bkp_license_package', $posted_license_package );
				update_option( 'bkp_license_status', $license_data->license );
			}
			
			update_option( 'bkp_license_data_activate_response', $license_data_string );
			update_option( 'bkp_license_status', $license_data->license );
			//wp_redirect( admin_url( 'plugins.php?page=' . EDD_SAMPLE_PLUGIN_LICENSE_PAGE ) );
			//exit();

			$response['variant']        = 'success';
			$response['title']          = esc_html__('Success', 'bookingpress-appointment-booking');
			$response['msg']            = esc_html__('License verified successfully', 'bookingpress-appointment-booking');

			echo wp_json_encode($response);
			exit;
		}

		function bookingpress_skip_wizard_func(){
			global $wpdb, $BookingPress;
			$response              = array();
			
			update_option('bookingpress_wizard_complete', 1);
			update_option('bookingpress_lite_wizard_complete', 1);

			$response['variant']        = 'success';
			$response['title']          = esc_html__('Success', 'bookingpress-appointment-booking');
			$response['msg']            = esc_html__('Wizard skipped successfully', 'bookingpress-appointment-booking');

			echo wp_json_encode($response);
			exit;
		}

		function bookingpress_save_wizard_settings_func(){
			global $wpdb, $BookingPress, $bookingpress_pro_staff_members, $tbl_bookingpress_default_workhours, $tbl_bookingpress_staffmembers, $tbl_bookingpress_services, $tbl_bookingpress_staffmembers_services, $tbl_bookingpress_customize_settings;
			
			$response              = array();

			$bookingpress_wizard_data = !empty($_POST['wizard_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['wizard_data']) : array(); //phpcs:ignore
			$bookingpress_inserted_staff_ids = array();

			if(!empty($bookingpress_wizard_data)){
				$bookingpress_company_fields_data = !empty($bookingpress_wizard_data['company_fields_data']) ? $bookingpress_wizard_data['company_fields_data'] : array();
				$bookingpress_booking_options = !empty($bookingpress_wizard_data['booking_options']) ? $bookingpress_wizard_data['booking_options'] : array();
				$bookingpress_staff_options = !empty($bookingpress_wizard_data['staffmember_options']) ? $bookingpress_wizard_data['staffmember_options'] : array();
				$bookingpress_service_options = !empty($bookingpress_wizard_data['service_options']) ? $bookingpress_wizard_data['service_options'] : array();
				$bookingpress_styling_options = !empty($bookingpress_wizard_data['styling_options']) ? $bookingpress_wizard_data['styling_options'] : array();

				if(!empty($bookingpress_company_fields_data)){
					$bookingpress_logo = $bookingpress_company_fields_data['logo_img'];
					$bookingpress_logo_url = $bookingpress_company_fields_data['logo'];

					if( !empty($bookingpress_company_fields_data['logo_img']) && !empty($bookingpress_company_fields_data['logo_img']) ){
						$bookingpress_upload_image_name = $bookingpress_logo;

						$upload_dir                 = BOOKINGPRESS_UPLOAD_DIR . '/';
						$bookingpress_new_file_name = current_time('timestamp') . '_' . $bookingpress_upload_image_name;
						$upload_path                = $upload_dir . $bookingpress_new_file_name;
						$bookingpress_upload_res    = $BookingPress->bookingpress_file_upload_function($bookingpress_logo_url, $upload_path);

						$bookingpress_file_name_arr = explode('/', $bookingpress_logo_url);
						$bookingpress_file_name     = $bookingpress_file_name_arr[ count($bookingpress_file_name_arr) - 1 ];
						if( file_exists( BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name ) ){
							@unlink(BOOKINGPRESS_TMP_IMAGES_DIR . '/' . $bookingpress_file_name);
						}

						$bookingpress_logo_url = BOOKINGPRESS_UPLOAD_URL . '/' . $bookingpress_new_file_name;
					}

					$BookingPress->bookingpress_update_settings('company_name', 'company_setting', $bookingpress_company_fields_data['company_name']);
					$BookingPress->bookingpress_update_settings('company_address', 'company_setting', $bookingpress_company_fields_data['address']);
					$BookingPress->bookingpress_update_settings('company_phone_country', 'company_setting', $bookingpress_company_fields_data['country']);
					$BookingPress->bookingpress_update_settings('company_phone_number', 'company_setting', $bookingpress_company_fields_data['phone_no']);
					$BookingPress->bookingpress_update_settings('company_website', 'company_setting', $bookingpress_company_fields_data['website']);
					$BookingPress->bookingpress_update_settings('company_avatar_img', 'company_setting', $bookingpress_logo);
					$BookingPress->bookingpress_update_settings('company_avatar_url', 'company_setting', $bookingpress_logo_url);
					$BookingPress->bookingpress_update_settings('default_phone_country_code', 'general_setting', $bookingpress_company_fields_data['country']);
					$BookingPress->bookingpress_update_settings('default_date_format', 'general_setting', $bookingpress_company_fields_data['date_format']);
					$BookingPress->bookingpress_update_settings('default_time_format', 'general_setting', $bookingpress_company_fields_data['time_format']);

					$bookingpress_anonymous_data = !empty($bookingpress_company_fields_data['anonymous_usage']) ? $bookingpress_company_fields_data['anonymous_usage'] : 'false';
                    if($bookingpress_anonymous_data == "true"){
                        $BookingPress->bookingpress_update_settings('anonymous_data', 'general_setting', 'true');
                    }else{
                        $BookingPress->bookingpress_update_settings('anonymous_data', 'general_setting', 'false');
                    }
				}

				if(!empty($bookingpress_booking_options)){
					$bookingpress_days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
					foreach($bookingpress_days as $day_key => $day_val){
						$bookingpress_start_time = $bookingpress_booking_options[$day_val]['start_time'];
                        $bookingpress_end_time = $bookingpress_booking_options[$day_val]['end_time'];

						if($bookingpress_start_time == "off"){
                            $bookingpress_start_time = $bookingpress_end_time = null;
                        }else if($bookingpress_start_time == "24:00:00" || $bookingpress_end_time == "24:00:00"){
                            if($bookingpress_start_time == "24:00:00"){
                                $bookingpress_start_time = "00:00:00";
                            }

                            if($bookingpress_end_time == "24:00:00"){
                                $bookingpress_end_time = "00:00:00";
                            }
                        }

						$bookingpress_insert_workhour_data = array(
							'bookingpress_workday_key' => $day_val,
							'bookingpress_start_time'  => $bookingpress_start_time,
							'bookingpress_end_time'    => $bookingpress_end_time,
							'bookingpress_is_break'    => 0,
							'bookingpress_created_at'  => current_time('mysql'),
						);

						$wpdb->update($tbl_bookingpress_default_workhours, $bookingpress_insert_workhour_data, array('bookingpress_workday_key' => $day_val));
					}
					
					
					$bookingpress_payment_currency = $bookingpress_booking_options['currency'];
					$BookingPress->bookingpress_update_settings('payment_default_currency', 'payment_setting', $bookingpress_payment_currency);

					//These options use to enable plugin. So code remaining
					$bookingpress_enable_invoice = $bookingpress_booking_options['enable_invoice']; //returns 'true' if enable
					$bookingpress_enable_tax = $bookingpress_booking_options['enable_tax']; //returns 'true' if enable

					$bookingpress_allow_discount = $bookingpress_booking_options['allow_discount']; //returns 'true' if enable
					if($bookingpress_allow_discount == 'true'){
						update_option('bookingpress_coupon_module', 'true');
					}


					//auto install both the plugins for license activated wizard
					

					//code ends here

				}

				if(!empty($bookingpress_staff_options)){
					$bookingpress_allow_edit_appointment = $bookingpress_staff_options['allow_edit_delete_appointments'];
					$bookingpress_allow_customer_handling = $bookingpress_staff_options['allow_customer_handling'];
					$bookingpress_allow_view_payments = $bookingpress_staff_options['allow_view_payments'];

					if($bookingpress_allow_edit_appointment == "true"){
						$BookingPress->bookingpress_update_settings('bookingpress_edit_appointments', 'staffmember_setting', 'true');
						$BookingPress->bookingpress_update_settings('bookingpress_delete_appointments', 'staffmember_setting', 'true');
					}

					if($bookingpress_allow_customer_handling == "true"){
						$BookingPress->bookingpress_update_settings('bookingpress_customers', 'staffmember_setting', 'true');
						$BookingPress->bookingpress_update_settings('bookingpress_edit_customers', 'staffmember_setting', 'true');
					}

					if($bookingpress_allow_view_payments == "true"){
						$BookingPress->bookingpress_update_settings('bookingpress_payments', 'staffmember_setting', 'true');
					}

					$bookingpress_phone_country = !empty($bookingpress_company_fields_data['country']) ? $bookingpress_company_fields_data['country'] : 'us';
					$bookingpress_staffmember_fields_data = $bookingpress_staff_options['staffmember_fields_details'];
					if(!empty($bookingpress_staffmember_fields_data)){
						foreach($bookingpress_staffmember_fields_data as $staff_key => $staff_val){
							$staff_firstname = $staff_val['firstname'];
							$staff_lastname = $staff_val['lastname'];
							$staff_email = $staff_val['email'];
							$staff_phone = $staff_val['phone_number'];

							if(empty($staff_email)){
								continue;
							}else{
								$bookingpress_existing_wp_user = get_user_by('email', $staff_email);
								$bookingpress_existing_wp_user_id = !empty($bookingpress_existing_wp_user->ID) ? $bookingpress_existing_wp_user->ID : 0;

								if(empty($bookingpress_existing_wp_user_id)){
									$bookingpress_user_pass = wp_generate_password(12, false);
									$bookingpress_existing_wp_user_id = wp_create_user($staff_email, $bookingpress_user_pass, $staff_email);

									wp_new_user_notification($bookingpress_existing_wp_user_id);
								}

								$bookingpress_staffmember_details = array(
									'bookingpress_wpuser_id' => $bookingpress_existing_wp_user_id,
									'bookingpress_staffmember_login' => $staff_email,
									'bookingpress_staffmember_firstname' => $staff_firstname,
									'bookingpress_staffmember_lastname' => $staff_lastname,
									'bookingpress_staffmember_phone' => $staff_phone,
									'bookingpress_staffmember_email' => $staff_email,
									'bookingpress_staffmember_status' => 1,
									'bookingpress_staffmember_created' => current_time( 'mysql' )
								);

								$wpdb->insert( $tbl_bookingpress_staffmembers, $bookingpress_staffmember_details );
								$bookingpress_staffmember_id = $wpdb->insert_id;

								array_push($bookingpress_inserted_staff_ids, $bookingpress_staffmember_id);

								if(!empty($bookingpress_staffmember_id)){
									$bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'staffmember_visibility', 'public');
								}
							}
						}
					}
				}

				if(!empty($bookingpress_service_options)){
					$bookingpress_service_fields_data = !empty($bookingpress_service_options['service_fields_details']) ? $bookingpress_service_options['service_fields_details'] : array();

					if(!empty($bookingpress_service_fields_data)){
						$service_position = 0;
						foreach($bookingpress_service_fields_data as $service_key => $service_val){
							$service_name = $service_val['service_name'];
							$service_price = floatval($service_val['price']);
							$duration_val = $service_val['duration_val'];
							$duration_unit = $service_val['duration_unit'];
							$service_description = $service_val['description'];

							if(!empty($service_name) && !empty($service_price) && !empty($duration_val) && !empty($duration_unit)){
								
								$bookingpress_service_data = array(
									'bookingpress_category_id' => 0,
									'bookingpress_service_name' => $service_name,
									'bookingpress_service_price' => $service_price,
									'bookingpress_service_duration_val' => $duration_val,
									'bookingpress_service_duration_unit' => $duration_unit,
									'bookingpress_service_description' => $service_description,
									'bookingpress_service_position' => $service_position,
									'bookingpress_servicedate_created' => current_time('mysql'),
								);

								$wpdb->insert($tbl_bookingpress_services, $bookingpress_service_data);
								$bookingpress_service_id = $wpdb->insert_id;

								if(!empty($bookingpress_inserted_staff_ids)){
									foreach($bookingpress_inserted_staff_ids as $staff_key => $staff_val){
										$staff_services_data = array(
											'bookingpress_staffmember_id' => $staff_val,
											'bookingpress_service_id' => $bookingpress_service_id,
											'bookingpress_service_price' => $service_price,
											'bookingpress_service_capacity' => 1,
											'bookingpress_service_min_capacity' => 1,
										);

										$wpdb->insert($tbl_bookingpress_staffmembers_services, $staff_services_data);
									}
								}
							}
							$service_position++;
						}
					}
				}

				if(!empty($bookingpress_styling_options)){
					$bookingpress_selected_fonts = !empty($bookingpress_styling_options['font_family']) ? $bookingpress_styling_options['font_family'] : '';
					if(!empty($bookingpress_selected_fonts)){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'title_font_family',
							'bookingpress_setting_value' => $bookingpress_selected_fonts,
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'title_font_family','bookingpress_setting_type' => 'booking_form') );
					}
					
					$bookingpress_primary_color = !empty($bookingpress_styling_options['primary_color']) ? $bookingpress_styling_options['primary_color'] : '';
					if(!empty($bookingpress_primary_color)){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'primary_color',
							'bookingpress_setting_value' => $bookingpress_primary_color,
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'primary_color','bookingpress_setting_type' => 'booking_form') );
					}

					$bookingpress_primary_background_color = !empty($bookingpress_styling_options['primary_background_color']) ? $bookingpress_styling_options['primary_background_color'] : '#e2faf1';
					$bookingpress_customize_fields = array(
						'bookingpress_setting_name' => 'primary_background_color',
						'bookingpress_setting_value' => $bookingpress_primary_background_color,
						'bookingpress_setting_type' => 'booking_form',
					);
					$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'primary_background_color','bookingpress_setting_type' => 'booking_form') );
					

					$bookingpress_title_color = !empty($bookingpress_styling_options['title_color']) ? $bookingpress_styling_options['title_color'] : '';
					if(!empty($bookingpress_title_color)){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'label_title_color',
							'bookingpress_setting_value' => $bookingpress_title_color,
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'label_title_color','bookingpress_setting_type' => 'booking_form') );
					}

					$bookingpress_subtitle_color = !empty($bookingpress_styling_options['subtitle_color']) ? $bookingpress_styling_options['subtitle_color'] : '';
					if(!empty($bookingpress_subtitle_color)){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'sub_title_color',
							'bookingpress_setting_value' => $bookingpress_subtitle_color,
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'sub_title_color','bookingpress_setting_type' => 'booking_form') );
					}

					$bookingpress_content_color = !empty($bookingpress_styling_options['content_color']) ? $bookingpress_styling_options['content_color'] : '';
					if(!empty($bookingpress_content_color)){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'content_color',
							'bookingpress_setting_value' => $bookingpress_content_color,
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'content_color','bookingpress_setting_type' => 'booking_form') );
					}
					
					$booking_form_sequence = !empty($bookingpress_styling_options['booking_form_sequence']) ? $bookingpress_styling_options['booking_form_sequence'] : '';
					if($booking_form_sequence == "service"){
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'bookingpress_form_sequance',
							'bookingpress_setting_value' => json_encode( array('service_selection','staff_selection') ),
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'bookingpress_form_sequance','bookingpress_setting_type' => 'booking_form') );
					}else{
						$bookingpress_customize_fields = array(
							'bookingpress_setting_name' => 'bookingpress_form_sequance',
							'bookingpress_setting_value' => json_encode( array( 'staff_selection', 'service_selection' ) ),
							'bookingpress_setting_type' => 'booking_form',
						);
						$wpdb->update($tbl_bookingpress_customize_settings, $bookingpress_customize_fields, array( 'bookingpress_setting_name' => 'bookingpress_form_sequance','bookingpress_setting_type' => 'booking_form') );
					}

					$bookingpress_background_color = $BookingPress->bookingpress_get_customize_settings('background_color', 'booking_form');
					$bookingpress_footer_background_color = $BookingPress->bookingpress_get_customize_settings('footer_background_color', 'booking_form');
					$bookingpress_primary_color = $BookingPress->bookingpress_get_customize_settings('primary_color', 'booking_form');
					$bookingpress_content_color = $BookingPress->bookingpress_get_customize_settings('content_color', 'booking_form');
					$bookingpress_label_title_color = $BookingPress->bookingpress_get_customize_settings('label_title_color', 'booking_form');
					$bookingpress_title_font_family = $BookingPress->bookingpress_get_customize_settings('title_font_family', 'booking_form');        
					$bookingpress_sub_title_color = $BookingPress->bookingpress_get_customize_settings('sub_title_color', 'booking_form');
					$bookingpress_price_button_text_color = $BookingPress->bookingpress_get_customize_settings('price_button_text_color', 'booking_form');    

					$bookingpress_background_color = !empty($bookingpress_background_color) ? $bookingpress_background_color : '#fff';
					$bookingpress_footer_background_color = !empty($bookingpress_footer_background_color) ? $bookingpress_footer_background_color : '#f4f7fb';
					$bookingpress_primary_color = !empty($bookingpress_primary_color) ? $bookingpress_primary_color : '#12D488';
					$bookingpress_content_color = !empty($bookingpress_content_color) ? $bookingpress_content_color : '#727E95';
					$bookingpress_label_title_color = !empty($bookingpress_label_title_color) ? $bookingpress_label_title_color : '#202C45';
					$bookingpress_title_font_family = !empty($bookingpress_title_font_family) ? $bookingpress_title_font_family : '';    
					$bookingpress_sub_title_color = !empty($bookingpress_sub_title_color) ? $bookingpress_sub_title_color : '#535D71';
					$bookingpress_price_button_text_color = !empty($bookingpress_price_button_text_color) ? $bookingpress_price_button_text_color : '#fff';

					$bookingpress_custom_data_arr['action'][] = 'bookingpress_save_my_booking_settings';
					$bookingpress_custom_data_arr['action'][] = 'bookingpress_save_booking_form_settings';                       
					$my_booking_form = array(
						'background_color' => $bookingpress_background_color,
						'row_background_color' => $bookingpress_footer_background_color,
						'primary_color' => $bookingpress_primary_color,
						'content_color' => $bookingpress_content_color,
						'label_title_color' => $bookingpress_label_title_color,
						'title_font_family' => $bookingpress_title_font_family,        
						'sub_title_color'   => $bookingpress_sub_title_color,
						'price_button_text_color' => $bookingpress_price_button_text_color,
					);      

					$booking_form = array(
						'background_color' => $bookingpress_background_color,
						'footer_background_color' => $bookingpress_footer_background_color,
						'primary_color' => $bookingpress_primary_color,
						'primary_background_color'=> $bookingpress_primary_background_color,
						'label_title_color' => $bookingpress_label_title_color,
						'title_font_family' => $bookingpress_title_font_family,                
						'content_color' => $bookingpress_content_color,                
						'price_button_text_color' => $bookingpress_price_button_text_color,
						'sub_title_color' => $bookingpress_sub_title_color,
					);

					$bookingpress_custom_data_arr['booking_form'] = $booking_form;
					$bookingpress_custom_data_arr['my_booking_form'] = $my_booking_form;
					$BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);
				}

				update_option('bookingpress_wizard_complete', 1);
				update_option('bookingpress_lite_wizard_complete', 1);

				$response['variant']        = 'success';
                $response['title']          = esc_html__('Success', 'bookingpress-appointment-booking');
                $response['msg']            = esc_html__('Data saved successfully', 'bookingpress-appointment-booking');
			}
			
			echo wp_json_encode($response);
			exit;
		}

		function bookingpress_wizard_dynamic_helper_vars_func(){
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
				var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
				ELEMENT.locale(lang)
			<?php
		}

		function bookingpress_wizard_dynamic_data_fields_func(){
			global $bookingpress_wizard_vue_data_fields, $bookingpress_pro_staff_members, $bookingpress_global_options,$BookingPress;

			$bookingpress_wizard_vue_data_fields = array();
			$bookingpress_wizard_vue_data_fields['staffmember_module'] = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();

			$bookingpress_wizard_vue_data_fields['bookingpress_active_tab'] = 'company_settings';
			$bookingpress_wizard_vue_data_fields['bookingpress_last_step_disabled'] = true;
			$bookingpress_wizard_vue_data_fields['bookingpress_disabled_tabs'] = true;
			
			$bookingpress_options                    = $bookingpress_global_options->bookingpress_global_options();

            $bookingpress_country_list               = $bookingpress_options['country_lists'];
			$bookingpress_wizard_vue_data_fields['phone_countries_details'] = json_decode($bookingpress_country_list);
			$bookingpress_wizard_vue_data_fields['open_license_activation_modal'] = false;

			$bookingpress_timezones_list = $this->bookingpress_wp_timezone_lists();
			$bookingpress_wizard_vue_data_fields['timezone_lists'] = $bookingpress_timezones_list;

			$bookingpress_wizard_vue_data_fields['comShowFileList'] = false;

			$bookingpress_default_time_format = $bookingpress_options['wp_default_time_format'];

			$bookingpress_workhours_arr = array();
			$bookingpress_workhours_arr[] = array(
				'start_time' => 'off',
				'end_time' => 'off',
				'formatted_start_time' => esc_html__('Off', 'bookingpress-appointment-booking'),
				'formatted_end_time' => esc_html__('Off', 'bookingpress-appointment-booking'),
			);
			$default_start_time      = '00:00:00';
			$default_end_time        = '23:55:00';
			$step_duration_val       = 5;
			do{
				$tmp_start_time = $default_start_time;

				$tmp_time_obj = new DateTime($tmp_start_time);
				$tmp_time_obj->add(new DateInterval('PT' . $step_duration_val . 'M'));
				$tmp_end_time = $tmp_time_obj->format('H:i:s');
				
				if($tmp_end_time == "00:00:00"){
					$tmp_end_time = "24:00:00";
				}

				$bookingpress_workhours_arr[] = array(
					'start_time' => $tmp_start_time,
					'end_time' => $tmp_end_time,
					'formatted_start_time' => date($bookingpress_default_time_format, strtotime($tmp_start_time)),
					'formatted_end_time' => date($bookingpress_default_time_format, strtotime($tmp_end_time))." ".($tmp_end_time == "24:00:00" ? esc_html__('Next Day', 'bookingpress-appointment-booking') : '' ),
				);

				if($tmp_end_time == "24:00:00"){
					break;
				}

				$default_start_time = $tmp_end_time;
			}while($default_start_time <= $default_end_time);
			
			$bookingpress_wizard_vue_data_fields['working_hours_arr'] = $bookingpress_workhours_arr;

			$bookingpress_countries_currency_details = json_decode($bookingpress_options['countries_json_details']);
			$bookingpress_wizard_vue_data_fields['bookingpress_currency_options'] = $bookingpress_countries_currency_details;

			$bookingpress_inherit_fonts_list = array('Inherit Fonts',);
            $bookingpress_default_fonts_list = $bookingpress_global_options->bookingpress_get_default_fonts();
            $bookingpress_google_fonts_list  = $bookingpress_global_options->bookingpress_get_google_fonts();
			$bookingpress_fonts_list         = array(
                array(
                    'label'   => __('Inherit Fonts', 'bookingpress-appointment-booking'),
                    'options' => $bookingpress_inherit_fonts_list,
                ),
                array(
                    'label'   => __('Default Fonts', 'bookingpress-appointment-booking'),
                    'options' => $bookingpress_default_fonts_list,
                ),
                array(
                    'label'   => __('Google Fonts', 'bookingpress-appointment-booking'),
                    'options' => $bookingpress_google_fonts_list,
                ),
            );
			$bookingpress_wizard_vue_data_fields['fonts_list'] = $bookingpress_fonts_list;

			$bookingpress_wizard_vue_data_fields['final_step_loader'] = '1';
			
			$default_phone_country_code = $BookingPress->bookingpress_get_settings('default_phone_country_code','general_setting');

			$bookingpress_default_timezone = wp_timezone_string();
			$bookingpress_default_timezone_offset = get_option('gmt_offset');
			
			$bookingpress_default_timezone_offset = str_replace(".25", ":15", $bookingpress_default_timezone_offset);
			$bookingpress_default_timezone_offset = str_replace(".5", ":30", $bookingpress_default_timezone_offset);
			$bookingpress_default_timezone_offset = str_replace(".75", ":45", $bookingpress_default_timezone_offset);

			
			$bookingpress_timezone_pattern = '/\+\d{2,2}+:\d{2,2}/i';
			$bookingpress_minus_timezone_pattern = '/\-\d{2,2}+:\d{2,2}/i';
			if(preg_match($bookingpress_timezone_pattern, $bookingpress_default_timezone)){
				$bookingpress_default_timezone = "UTC+".$bookingpress_default_timezone_offset;
			}else if(preg_match($bookingpress_minus_timezone_pattern, $bookingpress_default_timezone)){
				$bookingpress_default_timezone = "UTC".$bookingpress_default_timezone_offset;
			}else if($bookingpress_default_timezone == "UTC"){
				$bookingpress_default_timezone = "UTC";
			}

			$bookingpress_wizard_vue_data_fields['wizard_steps_data'] = array(
				'company_fields_data' => array(
					'company_name' => '',
					'address' => '',
					'time_format' => 'g:i a',
					'date_format' => 'F j, Y',
					'country' => $default_phone_country_code,
					'phone_no' => '',
					'timezone' => $bookingpress_default_timezone,
					'website' => '',
					'logo' => '',
					'logo_img' => '',
					'logo_list' => '',
					'multiple_staff' => 'yes',
					'anonymous_usage' => true,
				),
				'booking_options' => array(
					'monday' => array(
						'start_time' => '09:00:00',
						'end_time' => '17:00:00',
					),
					'tuesday' => array(
						'start_time' => '09:00:00',
						'end_time' => '17:00:00',
					),
					'wednesday' => array(
						'start_time' => '09:00:00',
						'end_time' => '17:00:00',
					),
					'thursday' => array(
						'start_time' => '09:00:00',
						'end_time' => '17:00:00',
					),
					'friday' => array(
						'start_time' => '09:00:00',
						'end_time' => '17:00:00',
					),
					'saturday' => array(
						'start_time' => 'off',
						'end_time' => 'off',
					),
					'sunday' => array(
						'start_time' => 'off',
						'end_time' => 'off',
					),
					'currency' => 'USD',
					'enable_invoice' => false,
					'enable_tax' => false,
					'allow_discount' => false,
				),
				'staffmember_options' => array(
					'allow_edit_delete_appointments' => false,
					'allow_customer_handling' => false,
					'allow_view_payments' => false,
					'staffmember_details' => array(
						array(
							'firstname_label' => esc_html__('First Name', 'bookingpress-appointment-booking'), 
							'lastname_label' => esc_html__('Last Name', 'bookingpress-appointment-booking'), 
							'email_label' => esc_html__('Email', 'bookingpress-appointment-booking'), 
							'phone_number_label' => esc_html__('Phone Number', 'bookingpress-appointment-booking'), 
						),
					),
					'staffmember_fields_details' => array(
						array(
							'firstname' => '',
							'lastname' => '',
							'email' => '',
							'phone_number' => '',
						),
					),
				),
				'service_options' => array(
					'service_details' => array(
						array(
							'service_name_label' => esc_html__('Service Name', 'bookingpress-appointment-booking'),
							'price_label' => esc_html__('Price', 'bookingpress-appointment-booking'),
							'duration_label' => esc_html__('Duration', 'bookingpress-appointment-booking'),
							'description_label' => esc_html__('Description', 'bookingpress-appointment-booking'),
						),
					),
					'service_fields_details' => array(
						array(
							'service_name' => '',
							'price' => '',
							'duration_val' => '30',
							'duration_unit' => 'm',
							'description' => '',
						),
					),
				),
				'styling_options' => array(
					'font_family' => 'Poppins',
					'primary_color' => '#12D488',
					'title_color' => '#202C45',
					'subtitle_color' => '#535D71',
					'content_color' => '#727E95',
					'booking_form_sequence' => 'service',
					'primary_background_color' => '#e2faf1',
				),
				'bookingpress_license_key' => '',
				'bookingpress_license_is_valid' => 'false',
				'bookingpress_err_msg' => '',
				'bookingpress_success_msg' => '',
			);

			echo wp_json_encode($bookingpress_wizard_vue_data_fields);
		}

		function bookingpress_wizard_on_load_methods_func(){
			?>
				document.body.classList.add('bpa-fullscreen-wizard-setup-container');
			<?php
		}

		function bookingpress_wizard_vue_methods_func(){
			global $bookingpress_notification_duration;
			?>
			bookingpress_change_invoice(current_val){
				const vm = this
				if(current_val == true && vm.wizard_steps_data.bookingpress_license_is_valid == 'false'){
					vm.open_license_activation_model();
				}
			},
			bookingpress_change_tax(current_val){
				const vm = this
				if(current_val == true && vm.wizard_steps_data.bookingpress_license_is_valid == 'false'){
					vm.open_license_activation_model();
				}
			},
			bookingpress_change_discount(current_val){
				const vm = this
				if(current_val == true && vm.wizard_steps_data.bookingpress_license_is_valid == 'false'){
					vm.open_license_activation_model();
				}
			},
			bookingpress_license_verification(){
				const vm = this
				var postData = [];
				postData.action = 'bookingpress_license_verification'
				postData.bookingpress_license_key = vm.wizard_steps_data.bookingpress_license_key
				postData.license_package = vm.wizard_steps_data.license_package				
				postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					vm.wizard_steps_data.bookingpress_err_msg = "";
					vm.wizard_steps_data.bookingpress_success_msg = "";
					if(response.data.variant != 'error'){
						vm.wizard_steps_data.bookingpress_license_is_valid = 'true';
						vm.wizard_steps_data.bookingpress_success_msg = response.data.msg;
					}else{
						vm.wizard_steps_data.bookingpress_license_is_valid = 'false';
						vm.wizard_steps_data.bookingpress_err_msg = response.data.msg;
					}
				}.bind(this) )
				.catch( function (error) {                    
					vm.$notify({
						title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
						message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});
			},
			open_license_activation_model(){
				const vm = this;
				vm.open_license_activation_modal = true;
			},
			close_wizard_modal_on_esc(){
				const vm = this;
				vm.open_license_activation_modal = false;
			},
			bookingpress_upload_company_avatar_func(response, file, fileList){
                const vm2 = this
                if(response != ''){
                    vm2.wizard_steps_data.company_fields_data.logo = response.upload_url
                    vm2.wizard_steps_data.company_fields_data.logo_img = response.upload_file_name
                }
            },
            bookingpress_company_avatar_upload_limit(files, fileList){
                const vm2 = this
                if(files.length >= 1){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Multiple files not allowed', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                }
            },
            checkUploadedFile(file){
                const vm2 = this
                if(file.type != 'image/jpeg' && file.type != 'image/png' && file.type != 'image/webp'){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Please upload jpg/png file only', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    return false
                }
            },
            bookingpress_company_avatar_upload_err(err, file, fileList){
                const vm2 = this
                var bookingpress_err_msg = '<?php esc_html_e('Something went wrong', 'bookingpress-appointment-booking'); ?>';
                if(err != '' || err != undefined){
                    bookingpress_err_msg = err
                }
                vm2.$notify({
                    title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                    message: bookingpress_err_msg,
                    type: 'error',
                    customClass: 'error_notification',
                    duration:<?php echo intval($bookingpress_notification_duration); ?>,
                });
            },
            bookingpress_remove_company_avatar(){
                const vm = this
                var upload_url = vm.wizard_steps_data.company_fields_data.logo                     
                var upload_filename = vm.wizard_steps_data.company_fields_data.logo_img 

                var postData = { action:'bookingpress_remove_uploaded_file',upload_file_url: upload_url,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {                    
                    vm.wizard_steps_data.company_fields_data.logo = ''
                    vm.wizard_steps_data.company_fields_data.logo_img = ''
                    vm.$refs.avatarRef.clearFiles()
                }.bind(vm) )
                .catch( function (error) {
                    console.log(error);
                });
            },
			bpa_add_staff(){
				const vm = this
				var bookingpress_default_obj = vm.wizard_steps_data.staffmember_options.staffmember_details[0];
				vm.wizard_steps_data.staffmember_options.staffmember_details.push(bookingpress_default_obj);
				vm.wizard_steps_data.staffmember_options.staffmember_fields_details.push({firstname: '', lastname: '', email: '', phone_number: ''});
			},
			bpa_remove_staff(remove_index){
				const vm = this
				vm.wizard_steps_data.staffmember_options.staffmember_details.splice(remove_index, 1);
			},
			bookingpress_add_service(){
				const vm = this
				var bookingpress_default_obj = vm.wizard_steps_data.service_options.service_details[0];
				vm.wizard_steps_data.service_options.service_details.push(bookingpress_default_obj);
				vm.wizard_steps_data.service_options.service_fields_details.push({service_name: '', price: '', duration_val: '30', duration_unit: 'm', description: ''});
			},
			bpa_remove_service(remove_index){
				const vm = this
				vm.wizard_steps_data.service_options.service_details.splice(remove_index, 1)
			},
			bookingpress_previous_tab(current_tab){
				const vm = this
				if(current_tab == "booking_options"){
					vm.bookingpress_active_tab = 'company_settings';
				}else if(current_tab == "staff_options"){
					vm.bookingpress_active_tab = 'booking_options';
				}else if(current_tab == "service_options"){
					if(vm.wizard_steps_data.company_fields_data.multiple_staff == "yes"){
						vm.bookingpress_active_tab = 'staff_options';
					}else{
						vm.bookingpress_active_tab = 'booking_options';
					}
				}else if(current_tab == "styling_options"){
					vm.bookingpress_active_tab = 'service_options';
				}else if(current_tab == "final_step"){
					vm.bookingpress_active_tab = 'styling_options';
				}
			},
			bookingpress_next_tab(current_tab){
				const vm = this
				if(current_tab == "company_settings"){
					vm.bookingpress_active_tab = 'booking_options';
				}else if(current_tab == "booking_options"){
					if(vm.wizard_steps_data.company_fields_data.multiple_staff == "yes"){
						vm.bookingpress_active_tab = 'staff_options';
					}else{
						vm.bookingpress_active_tab = 'service_options';
					}
				}else if(current_tab == "staff_options"){
					vm.bookingpress_active_tab = 'service_options';
				}else if(current_tab == "service_options"){
					vm.bookingpress_active_tab = 'styling_options';
				}else if(current_tab == "styling_options"){
					vm.bookingpress_last_step_disabled = false;
					vm.bookingpress_active_tab = 'final_step';
					vm.final_step_loader = '1';
					var postData = [];
					postData.action = 'bookingpress_save_wizard_settings'
					postData.wizard_data = vm.wizard_steps_data
					postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant != 'error'){
							vm.final_step_loader = '0';
						}else{
							console.log(response.data.msg);
						}
					}.bind(this) )
					.catch( function (error) {                    
						vm.$notify({
							title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
							message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					});
				}
			},
			bookingpress_copy_content(copy_data){
				const vm = this;
				var bookingpress_selected_placholder = copy_data;
				var bookingpress_dummy_elem = document.createElement("textarea");
				document.body.appendChild(bookingpress_dummy_elem);
				bookingpress_dummy_elem.value = bookingpress_selected_placholder;
				bookingpress_dummy_elem.select();
				document.execCommand("copy");
				document.body.removeChild(bookingpress_dummy_elem);
				vm.$notify(
				{ 
					title: '<?php esc_html_e('Success', 'bookingpress-appointment-booking'); ?>',
					message: '<?php echo esc_html_e('Text copied sucessfully.','bookingpress-appointment-booking'); ?>',
					type: 'success',
					customClass: 'success_notification',
					duration:<?php echo intval($bookingpress_notification_duration); ?>,
				});
			},
			bookingpress_skip_wizard(){
				var postData = [];
				postData.action = 'bookingpress_skip_wizard'
				postData._wpnonce = '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>'
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					if(response.data.variant != 'error'){
						window.location.href = '<?php echo esc_url_raw( admin_url() . 'admin.php?page=bookingpress' ); ?>';
					}else{
						console.log(response.data.msg);
					}
				}.bind(this) )
				.catch( function (error) {                    
					vm.$notify({
						title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
						message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});
			},
			bookingpress_generate_alpha_color(selected_color){
                const vm = this
                var opacity_color = Math.round(Math.min(Math.max(0.12 || 1, 0), 1) * 255);
                var primary_background_color = selected_color+(opacity_color.toString(16).toUpperCase())
                vm.wizard_steps_data.styling_options.primary_background_color = primary_background_color
            },
			<?php
		}

		function bookingpress_load_wizard_view_func(){
			$bookingpress_load_file_name = BOOKINGPRESS_PRO_VIEWS_DIR . '/wizard/manage_wizard.php';
			require $bookingpress_load_file_name;
		}

		function bookingpress_add_more_front_js(){
			wp_enqueue_script('wp-hooks');
		}

		function bookingpress_get_appointment_meta($appointment_meta_key, $bookingpress_appointment_id = 0, $bookingpress_order_id = 0){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_meta;
			$bookingpress_appointment_value = "";
			if(!empty($appointment_meta_key)){
				if(!empty($bookingpress_order_id)){
					$bookingpress_appointment_value = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_order_id = %d AND bookingpress_appointment_meta_key = %s", $bookingpress_order_id, $appointment_meta_key), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name.
				}else if(!empty($bookingpress_appointment_id)){
					$bookingpress_appointment_value = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_id = %d AND bookingpress_appointment_meta_key = %s", $bookingpress_appointment_id, $appointment_meta_key), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name.
				}
			}

			return $bookingpress_appointment_value;
		}

		/** used at multiple locations */
		function bookingpress_modify_service_time_func( $workhour_data, $current_date, $current_day, $service_id, $selected_date, $bookingpress_timezone ) {
			global $BookingPress, $bookingpress_services, $bookingpress_pro_services;
			$bookingpress_minimum_time_required_for_booking = $BookingPress->bookingpress_get_settings( 'default_minimum_time_for_booking', 'general_setting' ); // Default general settings value
			$bookingpress_minimum_time_required_for_booking = $bookingpress_services->bookingpress_get_service_meta( $service_id, 'minimum_time_required_before_booking' ); // Selected service meta value

			$bookingpress_max_capacity = $bookingpress_pro_services->bookingpress_get_service_max_capacity( $service_id );

			$bookingpress_current_time_timestamp = current_time( 'timestamp' );

			foreach ( $workhour_data as $k => $v ) {
				if ( $bookingpress_minimum_time_required_for_booking != 'disabled' && ! empty( $bookingpress_minimum_time_required_for_booking ) ) {
					$bookingpress_slot_start_datetime = $selected_date . ' ' . $v['start_time'] . ':00';
					$bookingpress_slot_end_datetime   = $selected_date . ' ' . $v['end_time'] . ':00';

					$bookingpress_slot_start_time_timestamp = strtotime( $bookingpress_slot_start_datetime );
					$bookingpress_slot_end_time_timestamp   = strtotime( $bookingpress_slot_end_datetime );

					$bookingpress_time_diff = round( abs( $bookingpress_current_time_timestamp - $bookingpress_slot_start_time_timestamp ) / 60, 2 );

					if ( $bookingpress_minimum_time_required_for_booking < 1440 ) {
						if ( $bookingpress_slot_start_time_timestamp < $bookingpress_current_time_timestamp ) {
							$workhour_data[ $k ]['is_booked'] = 1;
						}

						if ( $bookingpress_time_diff <= $bookingpress_minimum_time_required_for_booking ) {
							$workhour_data[ $k ]['is_booked'] = 1;
						}
					}
				}

				$workhour_data[ $k ]['max_capacity'] = $bookingpress_max_capacity;
			}

			return $workhour_data;
		}

		function bookingpress_modify_default_daysoff_details_func( $default_daysoff_details, $booking_date, $booking_time ) {
			global $wpdb, $bookingpress_services, $BookingPress;

			// If minimum time required for booking
			$bookingpress_minimum_time_required_for_booking = $BookingPress->bookingpress_get_settings( 'default_minimum_time_for_booking', 'general_setting' );

			$booking_date           = ! empty( $booking_date ) ? $booking_date : date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
			$booking_date_timestamp = strtotime( $booking_date );

			if ( $bookingpress_minimum_time_required_for_booking != 'disabled' && $bookingpress_minimum_time_required_for_booking >= 1440 ) {
				$bookingpress_total_days = $bookingpress_minimum_time_required_for_booking / 1440;

				// 1440 minutes = 1day
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
					}
				}
			}

			return $default_daysoff_details;
		}


		function bookingpress_logout_func() {
			if ( ! empty( $_REQUEST['bookingpress_action'] ) && ( $_REQUEST['bookingpress_action'] == 'bookingpress_logout' ) ) {
				wp_destroy_current_session();
				wp_clear_auth_cookie();
				wp_logout();
			}
		}

		function bookingpress_clear_debug_payment_log_func( $posted_data ) {
			global $wpdb, $BookingPress, $tbl_bookingpress_other_debug_logs,$tbl_bookingpress_debug_integration_logs;
			$integration_debug_log_arr = $this->bookingpress_get_integration_debug_log_arr();
			if ( ! empty( $posted_data ) ) {
				$bookingpress_view_log_selector = ! empty( $posted_data['bookingpress_debug_log_selector'] ) ? sanitize_text_field( $posted_data['bookingpress_debug_log_selector'] ) : '';
				if ( $bookingpress_view_log_selector == 'appointment_debug_logs' || $bookingpress_view_log_selector == 'email_notification_debug_logs' ) {
					$wpdb->delete( $tbl_bookingpress_other_debug_logs, array( 'bookingpress_other_log_type' => $bookingpress_view_log_selector ) );
				} elseif(!empty($bookingpress_view_log_selector) && in_array($bookingpress_view_log_selector,$integration_debug_log_arr)) {
					$wpdb->delete( $tbl_bookingpress_debug_integration_logs, array( 'bookingpress_integration_type' => $bookingpress_view_log_selector ) );				
				}
			}
		}

		function bookingpress_modify_download_debug_log_query_func( $bookingpress_debug_log_query, $bookingpress_view_log_selector, $bookingpress_posted_data ) {
			global $wpdb, $BookingPress, $tbl_bookingpress_other_debug_logs,$tbl_bookingpress_debug_integration_logs;

			$bookingpress_debug_payment_log_where_cond = '';
			$bookingpress_selected_download_duration   = ! empty( $bookingpress_posted_data['bookingpress_selected_download_duration'] ) ? sanitize_text_field( $bookingpress_posted_data['bookingpress_selected_download_duration'] ) : 'all';
			$integration_debug_log_arr = $this->bookingpress_get_integration_debug_log_arr();

			if ( ! empty( $bookingpress_posted_data['bookingpress_selected_download_custom_duration'] ) && $bookingpress_selected_download_duration == 'custom' ) {
				$bookingpress_start_date                   = date( 'Y-m-d 00:00:00', strtotime( sanitize_text_field( $bookingpress_posted_data['bookingpress_selected_download_custom_duration'][0] ) ) );
				$bookingpress_end_date                     = date( 'Y-m-d 23:59:59', strtotime( sanitize_text_field( $bookingpress_posted_data['bookingpress_selected_download_custom_duration'][1] ) ) );
				if(!empty($bookingpress_view_log_selector) && ($bookingpress_view_log_selector == 'appointment_debug_logs' || $bookingpress_view_log_selector == 'email_notification_debug_logs')) {
					$bookingpress_debug_payment_log_where_cond = " AND (bookingpress_other_log_added_date >= '" . $bookingpress_start_date . "' AND bookingpress_other_log_added_date <= '" . $bookingpress_end_date . "')";
				} elseif(!empty($bookingpress_view_log_selector) && in_array($bookingpress_view_log_selector,$integration_debug_log_arr)) {
					$bookingpress_debug_payment_log_where_cond = " AND (bookingpress_integration_log_added_date >= '" . $bookingpress_start_date . "' AND bookingpress_integration_log_added_date <= '" . $bookingpress_end_date . "')";
				}
			} elseif ( ! empty( $bookingpress_selected_download_duration ) && $bookingpress_selected_download_duration != 'custom' && $bookingpress_selected_download_duration != 'all') {
				if(!empty($bookingpress_view_log_selector) && ($bookingpress_view_log_selector == 'appointment_debug_logs' || $bookingpress_view_log_selector == 'email_notification_debug_logs')) {
					$bookingpress_last_selected_days           = date( 'Y-m-d', strtotime( '-' . $bookingpress_selected_download_duration . ' days' ) );
					$bookingpress_debug_payment_log_where_cond = " AND (bookingpress_other_log_added_date >= '" . $bookingpress_last_selected_days . "')";
				} elseif(!empty($bookingpress_view_log_selector) && in_array($bookingpress_view_log_selector,$integration_debug_log_arr)) {
					$bookingpress_last_selected_days           = date( 'Y-m-d', strtotime( '-' . $bookingpress_selected_download_duration . ' days' ) );
					$bookingpress_debug_payment_log_where_cond = " AND (bookingpress_integration_log_added_date >= '" . $bookingpress_last_selected_days . "')";
				}
			}						
			if ( $bookingpress_view_log_selector == 'appointment_debug_logs' ) {
				$bookingpress_debug_log_query = 'SELECT * FROM `' . $tbl_bookingpress_other_debug_logs . "` WHERE `bookingpress_other_log_type` = 'appointment_debug_logs'" . $bookingpress_debug_payment_log_where_cond . ' ORDER BY bookingpress_other_log_id DESC';
			} elseif ( $bookingpress_view_log_selector == 'email_notification_debug_logs' ) {
				$bookingpress_debug_log_query = 'SELECT * FROM `' . $tbl_bookingpress_other_debug_logs . "` WHERE `bookingpress_other_log_type` = 'email_notification_debug_logs'" . $bookingpress_debug_payment_log_where_cond . ' ORDER BY bookingpress_other_log_id DESC';
			} elseif (!empty($bookingpress_view_log_selector) && in_array($bookingpress_view_log_selector,$integration_debug_log_arr) ) {
				$bookingpress_debug_log_query = 'SELECT * FROM `' . $tbl_bookingpress_debug_integration_logs . "` WHERE `bookingpress_integration_type` = '".$bookingpress_view_log_selector."'". $bookingpress_debug_payment_log_where_cond . ' ORDER BY bookingpress_integration_log_id DESC';
			} 

			return $bookingpress_debug_log_query;
		}

		function bookingpress_modify_debug_log_data_func( $debug_log_data, $posted_data ) {
			global $wpdb, $BookingPress, $tbl_bookingpress_other_debug_logs,$tbl_bookingpress_debug_integration_logs;
			$integration_debug_log_arr = $this->bookingpress_get_integration_debug_log_arr();
			$bookingpress_debug_log_selector = !empty($posted_data['bookingpress_debug_log_selector']) ? sanitize_text_field($posted_data['bookingpress_debug_log_selector']) : '';
			$perpage     = isset($_POST['perpage']) ? intval($_POST['perpage']) : 20; //phpcs:ignore
            $currentpage = isset($_POST['currentpage']) ? intval($_POST['currentpage']) : 1; //phpcs:ignore
            $offset      = ( ! empty($currentpage) && $currentpage > 1 ) ? ( ( $currentpage - 1 ) * $perpage ) : 0;
	
			if ( ! empty( $bookingpress_debug_log_selector ) && $bookingpress_debug_log_selector == 'appointment_debug_logs' ) {

				$bookingpress_total_appointment_debug_logs_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_other_log_id FROM {$tbl_bookingpress_other_debug_logs} WHERE bookingpress_other_log_type = %s ORDER BY bookingpress_other_log_id DESC", 'appointment_debug_logs' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_other_debug_logs is a table name. false alarm

				$bookingpress_appointment_debug_logs_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_other_log_id,bookingpress_other_log_event,bookingpress_other_log_raw_data,bookingpress_other_log_added_date FROM {$tbl_bookingpress_other_debug_logs} WHERE bookingpress_other_log_type = %s ORDER BY bookingpress_other_log_id DESC LIMIT %d, %d", 'appointment_debug_logs',$offset , $perpage ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_other_debug_logs is a table name. false alarm

				$bookingpress_debug_log_data = array();
				$bookingpress_date_format    = get_option( 'date_format' );

				foreach ( $bookingpress_appointment_debug_logs_data as $bookingpress_debug_log_key => $bookingpress_debug_log_val ) {
					$bookingpress_debug_log_data[] = array(
						'payment_debug_log_id'         => $bookingpress_debug_log_val['bookingpress_other_log_id'],
						'payment_debug_log_name'       => $bookingpress_debug_log_val['bookingpress_other_log_event'],
						'payment_debug_log_data'       => stripslashes_deep($bookingpress_debug_log_val['bookingpress_other_log_raw_data']),
						'payment_debug_log_added_date' => date( $bookingpress_date_format, strtotime( $bookingpress_debug_log_val['bookingpress_other_log_added_date'] ) ),
					);
				}

				$debug_log_data['items'] = $bookingpress_debug_log_data;
				$debug_log_data['total'] = count($bookingpress_total_appointment_debug_logs_data);

			} elseif ( ! empty( $bookingpress_debug_log_selector ) && $bookingpress_debug_log_selector == 'email_notification_debug_logs' ) {
				$bookingpress_email_total_debug_logs_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_other_log_id FROM {$tbl_bookingpress_other_debug_logs} WHERE bookingpress_other_log_type = %s ORDER BY bookingpress_other_log_id DESC", 'email_notification_debug_logs' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_other_debug_logs is a table name. false alarm

				$bookingpress_email_debug_logs_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_other_log_id,bookingpress_other_log_event,bookingpress_other_log_raw_data,bookingpress_other_log_added_date FROM {$tbl_bookingpress_other_debug_logs} WHERE bookingpress_other_log_type = %s ORDER BY bookingpress_other_log_id DESC LIMIT %d, %d", 'email_notification_debug_logs',$offset , $perpage ) , ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_other_debug_logs is a table name. false alarm
				$bookingpress_debug_log_data = array();
				$bookingpress_date_format    = get_option( 'date_format' );
				foreach ( $bookingpress_email_debug_logs_data as $bookingpress_debug_log_key => $bookingpress_debug_log_val ) {
					$bookingpress_debug_log_data[] = array(
						'payment_debug_log_id'         => $bookingpress_debug_log_val['bookingpress_other_log_id'],
						'payment_debug_log_name'       => $bookingpress_debug_log_val['bookingpress_other_log_event'],
						'payment_debug_log_data'       => stripslashes_deep($bookingpress_debug_log_val['bookingpress_other_log_raw_data']),
						'payment_debug_log_added_date' => date( $bookingpress_date_format, strtotime( $bookingpress_debug_log_val['bookingpress_other_log_added_date'] ) ),
					);
				}
				$debug_log_data['items'] = $bookingpress_debug_log_data;
				$debug_log_data['total'] = count( $bookingpress_email_total_debug_logs_data );

			} elseif(! empty( $bookingpress_debug_log_selector ) && in_array($bookingpress_debug_log_selector,$integration_debug_log_arr) ) {

				$bookingpress_integration_debug_logs_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_integration_log_id FROM {$tbl_bookingpress_debug_integration_logs} WHERE bookingpress_integration_type = %s ORDER BY bookingpress_integration_log_id DESC", $bookingpress_debug_log_selector ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_debug_integration_logs is a table name. false alarm

				$bookingpress_integration_debug_log_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_integration_log_id,bookingpress_integration_event,bookingpress_integration_raw_data,bookingpress_integration_raw_data,bookingpress_integration_log_added_date FROM {$tbl_bookingpress_debug_integration_logs} WHERE bookingpress_integration_type = %s ORDER BY bookingpress_integration_log_id DESC LIMIT %d, %d", $bookingpress_debug_log_selector,$offset,$perpage), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_debug_integration_logs is a table name. false alarm

				$bookingpress_debug_log_data = array();
				$bookingpress_date_format    = get_option( 'date_format' );
				foreach ( $bookingpress_integration_debug_log_data as $bookingpress_debug_log_key => $bookingpress_debug_log_val ) {
					$bookingpress_debug_log_data[] = array(
						'payment_debug_log_id'         => $bookingpress_debug_log_val['bookingpress_integration_log_id'],
						'payment_debug_log_name'       => $bookingpress_debug_log_val['bookingpress_integration_event'],
						'payment_debug_log_data'       => stripslashes_deep($bookingpress_debug_log_val['bookingpress_integration_raw_data']),
						'payment_debug_log_added_date' => date( $bookingpress_date_format, strtotime( $bookingpress_debug_log_val['bookingpress_integration_log_added_date'] ) ),
					);
				}

				$debug_log_data['items'] = $bookingpress_debug_log_data;
				$debug_log_data['total'] = count( $bookingpress_integration_debug_logs_data );
			}

			return $debug_log_data;
		}

		function bookingpress_get_integration_debug_log_arr() {
			$bookingpress_integration_log_arr = array('whatsapp_debug_logs','google_calendar_debug_logs','sms_debug_logs','zoom_debug_logs','outlook_calendar_debug_logs','mailchimp_debug_logs','zapier_debug_logs');
			$bookingpress_integration_log_arr = apply_filters('bookingpress_integration_debug_log_arr_filter',$bookingpress_integration_log_arr);
			return $bookingpress_integration_log_arr;			
		}

		function bookingpress_admin_vue_on_load_script_func() {
			?>					
				if(document.getElementById("toplevel_page_bookingpress") != null){
					var bookingpress_coupon_menu = document.getElementById("toplevel_page_bookingpress").getElementsByClassName("bookingpress_coupon_module")[0];
					if(this.coupon_module == '0' && bookingpress_coupon_menu != undefined){
						bookingpress_coupon_menu.parentElement.style.display = 'none';
					}	
					var bookingpress_staffmember_menu = document.getElementById("toplevel_page_bookingpress").getElementsByClassName("bookingpress_staffmember_module")[0];
					if(this.staffmember_module == '0' && bookingpress_staffmember_menu != undefined){
						bookingpress_staffmember_menu.parentElement.style.display = 'none';
					}
				}	
			<?php
		}

		function bookingpress_admin_vue_data_variable_script_func() {
			global $bookingpress_coupons,$bookingpress_pro_staff_members,$bookingpress_global_options,$BookingPressPro,$BookingPress,$bookingpress_pro_settings;
			$is_coupon_module_activated      = $bookingpress_coupons->bookingpress_check_coupon_module_activation();
			$is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_global_details     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_start_of_week 	 = intval($bookingpress_global_details['start_of_week']);									
			$bookingpress_staff_customize_view = 0;
			$bookingpress_staffmember_view = !empty($_REQUEST['staffmember_view']) && ( sanitize_text_field($_REQUEST['staffmember_view'])== 'admin_view' || sanitize_text_field($_REQUEST['staffmember_view']) == 'customize_view') ? sanitize_text_field($_REQUEST['staffmember_view']) : '';

			$bookingpress_staffmember_access_admin = $BookingPress->bookingpress_get_settings( 'bookingpress_staffmember_access_admin', 'staffmember_setting' );

			if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' )) {
				if(empty($_COOKIE['bookingpress_staffmember_view']) || (!empty($_COOKIE['bookingpress_staffmember_view']) && $_COOKIE['bookingpress_staffmember_view'] == 'customize_view') || (!empty($bookingpress_staffmember_view) && $bookingpress_staffmember_view == 'customize_view')) {
					$bookingpress_staff_customize_view = 1;
				}
				if($bookingpress_staffmember_view == 'admin_view') {
					$bookingpress_staff_customize_view = 0;
				}
				if($bookingpress_staff_customize_view == 0 && $bookingpress_staffmember_access_admin == 'false') {
					$bookingpress_staff_customize_view = 1;
				}
			}
			$is_licence_activated = $bookingpress_pro_settings->bpa_validate_license_key();
			$bookingpress_licence_notice = get_option('bookingpress_dismiss_notice');
			
			if(!empty($bookingpress_licence_notice)) {				
				$current_time = current_time('timestamp');
				if($current_time < $bookingpress_licence_notice ) {
					$is_licence_activated = '';
				}
			}
			$is_license_expired = false;
			if( empty( $is_license_activated ) ){
				$is_license_expired = ( true == $bookingpress_pro_settings->bpa_validate_license_expiry() ) ? true : false;
			}

			$is_display_cron_notice = !($bookingpress_pro_settings->bpa_check_wp_cron_status());

			?>
				bookingpress_return_data['coupon_module'] = '<?php echo esc_html( $is_coupon_module_activated ); ?>';				
				bookingpress_return_data['staffmember_module'] = '<?php echo esc_html( $is_staffmember_module_activated ); ?>';

				bookingpress_return_data['datepicker_disabled_dates'] = {
					disabledDate(time){
						var currentDate = new Date();
						var modifyCurrentDate = new Date(currentDate);
						modifyCurrentDate.setDate(modifyCurrentDate.getDate() - 1);
						currentDate = modifyCurrentDate.getTime();
						return currentDate > time.getTime();
					},
					'firstDayOfWeek': parseInt('<?php echo esc_html($bookingpress_start_of_week) ?>')
				}
				bookingpress_return_data['bpa_toggle_active'] = 0;
				bookingpress_return_data['bookingpress_staff_customize_view'] = '<?php echo esc_html($bookingpress_staff_customize_view); ?>';
				bookingpress_return_data['staffmember_customize_notification_model'] = false;
				bookingpress_return_data['is_licence_activated'] = '<?php  echo $is_licence_activated //phpcs:ignore ?>';
				bookingpress_return_data['is_license_expired'] = '<?php echo $is_license_expired; //phpcs:ignore ?>';
				bookingpress_return_data['is_display_cron_notice'] = '<?php echo $is_display_cron_notice; //phpcs:ignore ?>';
				bookingpress_return_data['bpa_sm_shift_management_cls'] = '';
				bookingpress_return_data['bpa_service_shift_management_full_width_title_cls'] = '';
				bookingpress_return_data['holidayPickerOptions'] = {
					disabledDate(Time) {
						var dd = String(Time.getDate()).padStart(2, '0');
						var mm = String(Time.getMonth() + 1).padStart(2, '0'); //January is 0!
						var yyyy = Time.getFullYear();
						var time = yyyy+ '-' + mm + '-' + dd;
						var date = new Date();
						
						let selected_date = new Date( bookingpress_return_data.days_off_form.selected_date );
						if( "" != bookingpress_return_data.days_off_form.selected_end_date ){
							selected_date = new Date( bookingpress_return_data.days_off_form.selected_end_date );
						}
						return ( Time.getTime() < selected_date.getTime() );
					}
				};
				bookingpress_return_data['ServiceholidayPickerOptions'] = {
					disabledDate(Time) {
						var dd = String(Time.getDate()).padStart(2, '0');
						var mm = String(Time.getMonth() + 1).padStart(2, '0'); //January is 0!
						var yyyy = Time.getFullYear();
						var time = yyyy+ '-' + mm + '-' + dd;
						var date = new Date();
						
						let selected_date = new Date( bookingpress_return_data.service_dayoff_form.dayoff_date );
						if( "" != bookingpress_return_data.service_dayoff_form.dayoff_date_end ){
							selected_date = new Date( bookingpress_return_data.service_dayoff_form.dayoff_date_end );
						}
						return ( Time.getTime() < selected_date.getTime() );
					}
				};
				bookingpress_return_data['StaffholidayPickerOptions'] = {
					disabledDate(Time) {
						var dd = String(Time.getDate()).padStart(2, '0');
						var mm = String(Time.getMonth() + 1).padStart(2, '0'); //January is 0!
						var yyyy = Time.getFullYear();
						var time = yyyy+ '-' + mm + '-' + dd;
						var date = new Date();
						
						let selected_date = new Date( bookingpress_return_data.staffmember_dayoff_form.dayoff_date );
						if( "" != bookingpress_return_data.staffmember_dayoff_form.dayoff_date_end ){
							selected_date = new Date( bookingpress_return_data.staffmember_dayoff_form.dayoff_date_end );
						}
						return ( Time.getTime() < selected_date.getTime() );
					}
				};
			<?php
		}

		function bookingpress_pro_capabilities() {
			$cap = array(
				'bookingpress_staff_members'               => esc_html__( 'Staff Members', 'bookingpress-appointment-booking' ),
				'bookingpress_addons'                      => esc_html__( 'Add-ons', 'bookingpress-appointment-booking' ),
				'bookingpress_coupons'                     => esc_html__( 'Coupon Management', 'bookingpress-appointment-booking' ),
				//'bookingpress_add_appointments'            => esc_html__( 'Add Appointment', 'bookingpress-appointment-booking' ),
				'bookingpress_edit_appointments'           => esc_html__( 'Edit Appointment', 'bookingpress-appointment-booking' ),
				'bookingpress_delete_appointments'         => esc_html__( 'Delete Appointment', 'bookingpress-appointment-booking' ),
				'bookingpress_export_appointments'         => esc_html__( 'Export Appointment', 'bookingpress-appointment-booking' ),
				//'bookingpress_add_customers'               => esc_html__( 'Add Customer', 'bookingpress-appointment-booking' ),
				'bookingpress_edit_customers'              => esc_html__( 'Edit Customer', 'bookingpress-appointment-booking' ),
				'bookingpress_delete_customers'            => esc_html__( 'Delete Customer', 'bookingpress-appointment-booking' ),
				'bookingpress_export_customers'            => esc_html__( 'Export Customer', 'bookingpress-appointment-booking' ),
				'bookingpress_delete_payments'             => esc_html__( 'Delete Payment', 'bookingpress-appointment-booking' ),
				'bookingpress_edit_payments'               => esc_html__( 'Delete Payment', 'bookingpress-appointment-booking' ),
				'bookingpress_export_payments'             => esc_html__( 'Export Payment', 'bookingpress-appointment-booking' ),
				'bookingpress_edit_basic_details'          => esc_html__( 'Edit Staff member basic details', 'bookingpress-appointment-booking' ),
				'bookingpress_edit_services'               => esc_html__( 'Edit Staff member Services', 'bookingpress-appointment-booking' ),
				'bookingpress_edit_workhours'              => esc_html__( 'Edit Staffmember Workhour', 'bookingpress-appointment-booking' ),
				'bookingpress_edit_daysoffs'               => esc_html__( 'Edit Staffmember Daysoff', 'bookingpress-appointment-booking' ),
				'bookingpress_edit_workhours'			   => esc_html__( 'Edit Staffmember Working Hours', 'bookingpress-appointment-booking' ),
				'bookingpress_add_staffmembers'            => esc_html__( 'Add Staffmember', 'bookingpress-appointment-booking' ),
				'bookingpress_delete_staffmembers'         => esc_html__( 'Delete Staffmember', 'bookingpress-appointment-booking' ),
				'bookingpress_export_staffmembers'         => esc_html__( 'Export Staffmember', 'bookingpress-appointment-booking' ),
				'bookingpress_edit_special_days'           => esc_html__( 'Edit Staff member Special Days', 'bookingpress-appointment-booking' ),
				'bookingpress_manage_calendar_integration' => esc_html__( 'Manage Calendar Integration', 'bookingpress-appointment-booking' ),
				'bookingpress_reports'                     => esc_html__( 'Reports', 'bookingpress-appointment-booking' ),
				'bookingpress_license'                     => esc_html__( 'License', 'bookingpress-appointment-booking' ),
			);

			return $cap;
		}

		function bookingpress_pro_page_slugs() {
			global $bookingpress_slugs;

			$bookingpress_wizard_slug = ($bookingpress_slugs->bookingpress)."_wizard";
			$bookingpress_slugs->$bookingpress_wizard_slug = 'bookingpress_wizard';

			$bookingpress_slugs->bookingpress_staff_members = 'bookingpress_staff_members';
			$bookingpress_slugs->bookingpress_addons        = 'bookingpress_addons';
			$bookingpress_slugs->bookingpress_coupons       = 'bookingpress_coupons';
			$bookingpress_slugs->bookingpress_reports       = 'bookingpress_reports';
			$bookingpress_slugs->bookingpress_timesheet     = 'bookingpress_timesheet';
			$bookingpress_slugs->bookingpress_myprofile     = 'bookingpress_myprofile';
			$bookingpress_slugs->bookingpress_myservices    = 'bookingpress_myservices';
			$bookingpress_slugs->bookingpress_license      	= 'bookingpress_settings&setting_page=license_settings';
			$bookingpress_slugs->bookingpress_growth_tools  = 'bookingpress_growth_tools';

		}

		function bookingpress_pro_menu() {
			global $bookingpress_slugs, $BookingPress, $bookingpress_coupons,$bookingpress_pro_staff_members;

			$bookingpress_staffmember_plural_name = $BookingPress->bookingpress_get_settings('bookingpress_staffmember_module_plural_name', 'staffmember_setting');

			remove_submenu_page( $bookingpress_slugs->bookingpress, $bookingpress_slugs->bookingpress_calendar );
			remove_submenu_page( $bookingpress_slugs->bookingpress, $bookingpress_slugs->bookingpress_appointments );
			remove_submenu_page( $bookingpress_slugs->bookingpress, $bookingpress_slugs->bookingpress_payments );
			remove_submenu_page( $bookingpress_slugs->bookingpress, $bookingpress_slugs->bookingpress_customers );
			remove_submenu_page( $bookingpress_slugs->bookingpress, $bookingpress_slugs->bookingpress_services );
			remove_submenu_page( $bookingpress_slugs->bookingpress, $bookingpress_slugs->bookingpress_notifications );
			remove_submenu_page( $bookingpress_slugs->bookingpress, $bookingpress_slugs->bookingpress_customize );
			remove_submenu_page( $bookingpress_slugs->bookingpress, $bookingpress_slugs->bookingpress_settings );
			remove_menu_page( $bookingpress_slugs->bookingpress_lite_wizard );
			remove_menu_page( $bookingpress_slugs->bookingpress );
			remove_submenu_page( $bookingpress_slugs->bookingpress, $bookingpress_slugs->bookingpress."&upgrade_action=upgrade_to_pro" );

			$place = $BookingPress->get_free_menu_position( 26.1, 0.3 );

			if ( $this->bookingpress_check_user_role( 'bookingpress-staffmember' ) && ( ! $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() || $bookingpress_pro_staff_members->bookingpress_current_login_staffmember_status() != 1 ) ) {
				// wp_die(__('Sorry, you are not allowed to access this page','bookingpress-appointment-booking'));
			} else {

				$bookingpress_is_wizard_complete = get_option('bookingpress_wizard_complete');
				$bookingpress_is_download_lite_automatically = get_option('bookingpress_lite_download_automatic');

				if((empty($bookingpress_is_wizard_complete) || $bookingpress_is_wizard_complete == 0) && ($bookingpress_is_download_lite_automatically == 1) ){
					$bookingpress_menu_hook = add_menu_page( esc_html__( 'BookingPress', 'bookingpress-appointment-booking' ), esc_html__( 'BookingPress', 'bookingpress-appointment-booking' ), 'bookingpress', $bookingpress_slugs->bookingpress."_wizard", array( $BookingPress, 'route' ), BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png', $place );
				}else{
					$bookingpress_menu_hook = add_menu_page( esc_html__( 'BookingPress', 'bookingpress-appointment-booking' ), esc_html__( 'BookingPress', 'bookingpress-appointment-booking' ), 'bookingpress', $bookingpress_slugs->bookingpress, array( $BookingPress, 'route' ), BOOKINGPRESS_IMAGES_URL . '/bookingpress_menu_icon.png', $place );
				}

				//add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Wizard', 'bookingpress-appointment-booking' ), __( 'Wizard', 'bookingpress-appointment-booking' ), 'bookingpress', $bookingpress_slugs->bookingpress."_wizard", array( $BookingPress, 'route' ) );

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Calendar', 'bookingpress-appointment-booking' ), __( 'Calendar', 'bookingpress-appointment-booking' ), 'bookingpress_calendar', $bookingpress_slugs->bookingpress_calendar, array( $BookingPress, 'route' ) );

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Appointments', 'bookingpress-appointment-booking' ), __( 'Appointments', 'bookingpress-appointment-booking' ), 'bookingpress_appointments', $bookingpress_slugs->bookingpress_appointments, array( $BookingPress, 'route' ) );

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Payments', 'bookingpress-appointment-booking' ), __( 'Payments', 'bookingpress-appointment-booking' ), 'bookingpress_payments', $bookingpress_slugs->bookingpress_payments, array( $BookingPress, 'route' ) );

				do_action('bookingpress_add_specific_menu_after_payment_menu', $bookingpress_slugs);

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Customers', 'bookingpress-appointment-booking' ), __( 'Customers', 'bookingpress-appointment-booking' ), 'bookingpress_customers', $bookingpress_slugs->bookingpress_customers, array( $BookingPress, 'route' ) );

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Services', 'bookingpress-appointment-booking' ), __( 'Services', 'bookingpress-appointment-booking' ), 'bookingpress_services', $bookingpress_slugs->bookingpress_services, array( $BookingPress, 'route' ) );

				do_action('bookingpress_add_specific_menu', $bookingpress_slugs);

				if ( current_user_can( 'bookingpress_coupons' )  && $bookingpress_coupons->bookingpress_check_coupon_module_activation() ) {
					add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Discounts', 'bookingpress-appointment-booking' ), "<span class='bookingpress_coupon_module'>" . __( 'Discounts', 'bookingpress-appointment-booking' ) . '</span>', 'bookingpress_coupons', $bookingpress_slugs->bookingpress_coupons, array( $BookingPress, 'route' ) );
				}

				if ( current_user_can( 'bookingpress_staff_members' )  && $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ) {
					add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Staff Members', 'bookingpress-appointment-booking' ), "<span class='bookingpress_staffmember_module'>" . esc_html(stripslashes_deep($bookingpress_staffmember_plural_name)) . '</span>', 'bookingpress_staff_members', $bookingpress_slugs->bookingpress_staff_members, array( $BookingPress, 'route' ) );
				}

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Notifications', 'bookingpress-appointment-booking' ), __( 'Notifications', 'bookingpress-appointment-booking' ), 'bookingpress_notifications', $bookingpress_slugs->bookingpress_notifications, array( $BookingPress, 'route' ) );

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Customize', 'bookingpress-appointment-booking' ), __( 'Customize', 'bookingpress-appointment-booking' ), 'bookingpress_customize', $bookingpress_slugs->bookingpress_customize, array( $BookingPress, 'route' ) );

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Settings', 'bookingpress-appointment-booking' ), __( 'Settings', 'bookingpress-appointment-booking' ), 'bookingpress_settings', $bookingpress_slugs->bookingpress_settings, array( $BookingPress, 'route' ) );

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Reports', 'bookingpress-appointment-booking' ), __( 'Reports', 'bookingpress-appointment-booking' ), 'bookingpress_reports', $bookingpress_slugs->bookingpress_reports, array( $BookingPress, 'route' ) );

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'License', 'bookingpress-appointment-booking' ), __( 'License', 'bookingpress-appointment-booking' ), 'bookingpress_license', $bookingpress_slugs->bookingpress_license, array( $BookingPress, 'route' ) );

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Add-ons', 'bookingpress-appointment-booking' ), __( 'Add-ons', 'bookingpress-appointment-booking' ), 'bookingpress_addons', $bookingpress_slugs->bookingpress_addons, array( $BookingPress, 'route' ) );

				add_submenu_page( $bookingpress_slugs->bookingpress, __( 'Growth Plugins', 'bookingpress-appointment-booking' ), __( 'Growth Plugins', 'bookingpress-appointment-booking' ), 'bookingpress_growth_tools', $bookingpress_slugs->bookingpress_growth_tools, array( $BookingPress, 'route' ) );

				if ( ! current_user_can( 'administrator' ) ) {
					add_submenu_page( $bookingpress_slugs->bookingpress, __( 'TimeSheet', 'bookingpress-appointment-booking' ), "<span class='bookingpress_staffmember_module'>" . __( 'TimeSheet', 'bookingpress-appointment-booking' ) . '</span>', 'bookingpress_timesheet', $bookingpress_slugs->bookingpress_timesheet, array( $BookingPress, 'route' ) );

					add_submenu_page( $bookingpress_slugs->bookingpress, __( 'My Profile', 'bookingpress-appointment-booking' ), "<span class='bookingpress_staffmember_module'>" . __( 'My Profile', 'bookingpress-appointment-booking' ) . '</span>', 'bookingpress_myprofile', $bookingpress_slugs->bookingpress_myprofile, array( $BookingPress, 'route' ) );

					add_submenu_page( $bookingpress_slugs->bookingpress, __( 'My Services', 'bookingpress-appointment-booking' ), "<span class='bookingpress_staffmember_module'>" . __( 'My Services', 'bookingpress-appointment-booking' ) . '</span>', 'bookingpress_myservices', $bookingpress_slugs->bookingpress_myservices, array( $BookingPress, 'route' ) );
				}
			}
		}

		function set_js() {

			global $bookingpress_global_options,$bookingpress_slugs;
			
			wp_register_script('bookingpress_pro_admin_custom_js', BOOKINGPRESS_PRO_URL . '/js/bookingpress_pro_admin_custom.js', array(), BOOKINGPRESS_PRO_VERSION);
			wp_enqueue_script('bookingpress_pro_admin_custom_js');			

			if ( isset( $_REQUEST['page'] ) && in_array( sanitize_text_field( $_REQUEST['page'] ), (array) $bookingpress_slugs ) ) {

				if ( ! empty( $_REQUEST['page'] ) && ( sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_staff_members' ) ) {
					wp_enqueue_script( 'bookingpress_tel_input_js' );
					wp_enqueue_script('bookingpress_tel_utils_js');
				}
				
				/* Add JS file only for plugin pages. */
				if (isset($_REQUEST['page']) && ( sanitize_text_field($_REQUEST['page']) == 'bookingpress_staff_members' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					wp_enqueue_script('bookingpress_sortable_js');
					wp_enqueue_script('bookingpress_draggable_js');
				}

				if ( ! empty( $_REQUEST['page'] ) && 'bookingpress_customize' == $_REQUEST['page'] ) {
					wp_dequeue_script( 'bookingpress_sortable_js' );
					wp_dequeue_script( 'bookingpress_draggable_js' );

					wp_enqueue_script( 'Sortable', BOOKINGPRESS_PRO_URL . '/js/Sortable.min.js', array(), BOOKINGPRESS_PRO_VERSION );
					wp_enqueue_script( 'bpa-sortable', BOOKINGPRESS_PRO_URL . '/js/bpa-sortable.js', array( 'Sortable', 'wp-hooks', 'wp-i18n' ), BOOKINGPRESS_PRO_VERSION );
				}

				if ( ! empty( $_REQUEST['page'] ) && ( sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_reports' ) ) {
					wp_register_script( 'bookingpress_charts_js', BOOKINGPRESS_URL . '/js/bookingpress_chart.min.js', array(), BOOKINGPRESS_VERSION );
					wp_enqueue_script( 'bookingpress_charts_js' );
				}

				if( !empty( $_REQUEST['page'] ) && ( sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_settings' ) ){
					wp_enqueue_script( 'Sortable', BOOKINGPRESS_PRO_URL . '/js/Sortable.min.js', array(), BOOKINGPRESS_PRO_VERSION );
					wp_enqueue_script( 'bpa-customer-sortable', BOOKINGPRESS_PRO_URL . '/js/bpa-customer-sortable.js', array(), BOOKINGPRESS_PRO_VERSION );
				}

				if( !empty( $_REQUEST['page'] ) && ( sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_customize' ) ){
					wp_enqueue_script('bookingpress_draggable_js');
				}
			}
		}
		function set_css() {

			global $bookingpress_slugs;
			wp_register_style(
				'bookingpress_pro_admin_css',
				BOOKINGPRESS_PRO_URL . '/css/bookingpress_pro_admin.css',
				array(),
				BOOKINGPRESS_PRO_VERSION
			);

			wp_register_style(
				'bookingpress_pro_admin_rtl_css',
				BOOKINGPRESS_PRO_URL . '/css/bookingpress_pro_admin_rtl.css',
				array(),
				BOOKINGPRESS_PRO_VERSION
			);
						
			if ( isset( $_REQUEST['page'] ) && in_array( sanitize_text_field( $_REQUEST['page'] ), (array) $bookingpress_slugs ) ) {
				wp_enqueue_style( 'bookingpress_pro_admin_css' );
				if (is_rtl() ) { wp_enqueue_style( 'bookingpress_pro_admin_rtl_css' ); }

				if ( ! empty( $_REQUEST['page'] ) && ( sanitize_text_field( $_REQUEST['page'] ) == 'bookingpress_staff_members' ) ) {
					wp_enqueue_style( 'bookingpress_tel_input' );
				}
			}
		}

		function set_front_css( $force_enqueue = 0 ) {
			global $BookingPress;
			$bookingress_load_js_css_all_pages = $BookingPress->bookingpress_get_settings( 'load_js_css_all_pages', 'general_setting' );

			wp_register_style( 'bookingpress_pro_front_css', BOOKINGPRESS_PRO_URL . '/css/bookingpress_pro_front.css', array( 'bookingpress_front_css' ), BOOKINGPRESS_PRO_VERSION );

			wp_register_style( 'bookingpress_pro_front_rtl_css', BOOKINGPRESS_PRO_URL . '/css/bookingpress_pro_front_rtl.css', array( 'bookingpress_front_css' ), BOOKINGPRESS_PRO_VERSION );

			if ( $BookingPress->bookingpress_is_front_page() || ( $bookingress_load_js_css_all_pages == 'true' ) || ( $force_enqueue == 1 ) ) {
				wp_enqueue_style( 'bookingpress_pro_front_css' );
				if (is_rtl() ) {
					wp_enqueue_style( 'bookingpress_pro_front_rtl_css' );
				}
			}
		}

		function bookingpress_modify_readmore_link_func(){
			?>
				var selected_tab = sessionStorage.getItem("current_tabname");
				if(selected_tab == "customer_settings"){
					read_more_link = "https://www.bookingpressplugin.com/documents/customer-settings/";
				}else if(selected_tab == "specialday_settings"){
					read_more_link = "https://www.bookingpressplugin.com/documents/special-days-settings/";
				}else if(selected_tab == "staffmembers_settings"){
					read_more_link = "https://www.bookingpressplugin.com/documents/staff-member-settings/";
				}

				if(bpa_requested_module == "staff_members"){
					read_more_link = "https://www.bookingpressplugin.com/documents/staff-member/";
				}else if(bpa_requested_module == "coupons"){
					read_more_link = "https://www.bookingpressplugin.com/documents/coupons/";
				}else if(bpa_requested_module == "reports"){
					read_more_link = "https://www.bookingpressplugin.com/documents/reports/";
				}else if(bpa_requested_module == "wizard"){
					read_more_link = "https://www.bookingpressplugin.com/documents/installing-updating-bookingpress/";
				}else if(bpa_requested_module == "myprofile"){
					read_more_link = "https://www.bookingpressplugin.com/documents/staff-member-settings/";
				}
			<?php
		}

		function remove_admin_bar_style_backend() {
			echo '<style>body.admin-bar #wpcontent #wpadminbar { display:none !important; }.bpa-header-navbar{top:0px !important;} html.wp-toolbar{padding-top:0px !important;} .bpa-header-navbar .bpa-navbar-nav ul{ top: 88px !important; }</style>';
		}
		function bookingpress_body_unique_class_staff_panel($classes) {
			$classes .= ' bpa-admin-bar--hidden';
			return $classes;
		}
		function bookingpress_modify_header_content_func( $bookingpress_header_file_url,$from_header = 0 ) {
			global $bookingpress_coupons,$bookingpress_slugs, $bookingpress_pro_staff_members,$BookingPress;
			$bookingpress_new_slugs = new stdClass();
			$bookingpress_new_slugs->bookingpress_staff_members = 'bookingpress_staff_members';
			$bookingpress_new_slugs->bookingpress_addons        = 'bookingpress_addons';
			$bookingpress_new_slugs->bookingpress_coupons       = 'bookingpress_coupons';
			$bookingpress_new_slugs->bookingpress_reports       = 'bookingpress_reports';
			$bookingpress_new_slugs->bookingpress_timesheet     = 'bookingpress_timesheet';
			$bookingpress_new_slugs->bookingpress_myprofile     = 'bookingpress_myprofile';
			$bookingpress_new_slugs->bookingpress_myservices    = 'bookingpress_myservices';
			
			if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'bookingpress_coupons' && ! $bookingpress_coupons->
				bookingpress_check_coupon_module_activation() ) {
				wp_redirect( add_query_arg( 'page', $bookingpress_slugs->bookingpress_addons, esc_url( admin_url() . 'admin.php?page=bookingpress' ) ) );
				exit;
			} elseif ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'bookingpress_staff_members' && ! $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ) {
				wp_redirect( add_query_arg( 'page', $bookingpress_slugs->bookingpress_addons, esc_url( admin_url() . 'admin.php?page=bookingpress' ) ) );
				exit;
			} else {
				if ( $this->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) {

					$bookingpress_staffmember_access_admin = $BookingPress->bookingpress_get_settings( 'bookingpress_staffmember_access_admin', 'staffmember_setting' );
					$bookingpress_staffmember_view = !empty($_REQUEST['staffmember_view']) && ( sanitize_text_field($_REQUEST['staffmember_view']) == 'admin_view' || sanitize_text_field($_REQUEST['staffmember_view']) == 'customize_view') ? sanitize_text_field($_REQUEST['staffmember_view']) : '';

					if($from_header == 0) {
						if(empty($_COOKIE['bookingpress_staffmember_view']) && empty($bookingpress_staffmember_view)) {
							setcookie("bookingpress_staffmember_view",'customize_view',time()+(86400 * 365 *10), "/");
						} elseif (!empty($bookingpress_staffmember_view) && ( $bookingpress_staffmember_view == 'admin_view' || $bookingpress_staffmember_view == 'customize_view')) {
							setcookie("bookingpress_staffmember_view", $bookingpress_staffmember_view,time()+(86400 * 365 *10), "/");
						}
					}

					if(!empty($bookingpress_staffmember_access_admin ) && $bookingpress_staffmember_access_admin == 'true' && (!empty($_COOKIE['bookingpress_staffmember_view']) && ( $_COOKIE['bookingpress_staffmember_view'] == 'admin_view' && $bookingpress_staffmember_view != 'customize_view') || $bookingpress_staffmember_view == 'admin_view')) {

						$bookingpress_header_file_url = BOOKINGPRESS_PRO_VIEWS_DIR . '/bookingpress_pro_staffmember_header.php';	
					} else {
						$bookingpress_header_file_url = BOOKINGPRESS_PRO_VIEWS_DIR . '/bookingpress_pro_staffmember_customize_view.php';
						if ( ! empty( $_REQUEST['page'] ) && (in_array( $_REQUEST['page'], (array) $bookingpress_slugs ) || in_array($_REQUEST['page'], (array) $bookingpress_new_slugs))) {
							add_filter( 'admin_body_class', array( $this, 'bookingpress_dynamic_class_for_customize_view' ) );
						}
					}

					if ( ! empty( $_REQUEST['page'] ) && (in_array( $_REQUEST['page'], (array) $bookingpress_slugs ) || in_array($_REQUEST['page'], (array) $bookingpress_new_slugs))) {
						add_filter( 'admin_head', array( $this, 'remove_admin_bar_style_backend' ) );
						remove_all_filters( 'show_admin_bar' );		
						add_filter( 'show_admin_bar', '__return_false' );
						add_filter( 'admin_body_class',array($this,'bookingpress_body_unique_class_staff_panel'));		
					}					
					
				} else {
					$bookingpress_header_file_url = BOOKINGPRESS_PRO_VIEWS_DIR . '/bookingpress_pro_header.php';
				}
			}			
			
			return $bookingpress_header_file_url;
		}

		function bookingpress_dynamic_class_for_customize_view($classes) {
			$classes .= ' __bpa-is-staff-customize-view-active';
			return $classes;
		}
		function bookingpress_update_general_settings_pro( $setting_key, $setting_value, $setting_type ) {
			global $wpdb, $tbl_bookingpress_settings;
			if ( ! empty( $setting_key ) ) {
				$bookingpress_check_record_existance = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(setting_id) FROM {$tbl_bookingpress_settings} WHERE setting_name = %s AND setting_type = %s", $setting_key, $setting_type ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_settings is a table name. false alarm
				if ( $bookingpress_check_record_existance > 0 ) {
					// If record already exists then update data.
					$bookingpress_update_data   = array(
						'setting_value' => ( ! empty( $setting_value ) && gettype( $setting_value ) === 'boolean' ) ? $setting_value : sanitize_text_field( $setting_value ),
						'setting_type'  => $setting_type,
						'updated_at'    => current_time( 'mysql' ),
					);
					$arm_update_where_condition = array(
						'setting_name' => $setting_key,
						'setting_type' => $setting_type,
					);
					$arm_update_affected_rows   = $wpdb->update( $tbl_bookingpress_settings, $bookingpress_update_data, $arm_update_where_condition );
					if ( $arm_update_affected_rows > 0 ) {
						wp_cache_delete( $setting_key );
						wp_cache_set( $setting_key, $setting_value );
					}
				} else {
					// If record not exists then insert data.
						$bookingpress_insert_data = array(
							'setting_name'  => $setting_key,
							'setting_value' => ( ! empty( $setting_value ) && gettype( $setting_value ) === 'boolean' ) ? $setting_value : sanitize_text_field( $setting_value ),
							'setting_type'  => $setting_type,
							'updated_at'    => current_time( 'mysql' ),
						);

						$bookingpress_inserted_id = $wpdb->insert( $tbl_bookingpress_settings, $bookingpress_insert_data );
						if ( $bookingpress_inserted_id > 0 ) {
							wp_cache_delete( $setting_key );
							wp_cache_set( $setting_key, $setting_value );

						}
				}
			}
		}
		public function install_bookingpress_lite() {
			global $wpdb;
			if ( ! file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking/bookingpress-appointment-booking.php' ) ) {
				if ( ! function_exists( 'plugins_api' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				}
				$response = plugins_api(
					'plugin_information',
					array(
						'slug'   => 'bookingpress-appointment-booking',
						'fields' => array(
							'sections' => false,
							'versions' => true,
						),
					)
				);
				if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
					if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
						require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
					}
					$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
					$source   = ! empty( $response->download_link ) ? $response->download_link : '';

					$check_lite_existance = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s", '%bookingpress_pro_exist_on_uninstall_lite%' ) );

					update_option( 'bpa_last_query_'. date('Y-m-d H:i:s'), $wpdb->last_query );

					if( !empty( $check_lite_existance ) ) {
						update_option( 'bpa_force_reinstallation_pro_'. date( 'Y-m-d H:i:s' ), json_encode( $check_lite_existance ) );
						$this->is_pro_reinstall = true;
					}

					update_option('bookingpress_lite_download_automatic', 1);

					if ( ! empty( $source ) ) {
						if ( $upgrader->install( $source ) === true ) {
							activate_plugin( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' );
							return true;
						}
					}
				}
				else
				{
					$source_url = 'https://bookingpressplugin.com/bpa_misc/bkp_lite_plugin_install_api.php';
					$get_custom_response = wp_remote_get( $source_url, array( 'method' => 'GET') );
					if(!is_wp_error($get_custom_response))
					{
						$get_custom_response_body = json_decode(wp_remote_retrieve_body($get_custom_response));
						if(is_object($get_custom_response_body) && !empty($get_custom_response_body))
						{
							if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
								require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
							}
							$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
							$source   = !empty( $get_custom_response_body->download_link ) ? $get_custom_response_body->download_link : '';

							$check_lite_existance = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE %s", '%bookingpress_pro_exist_on_uninstall_lite%' ) );

							update_option( 'bpa_last_query_'. date('Y-m-d H:i:s'), $wpdb->last_query );

							if( !empty( $check_lite_existance ) ) {
								update_option( 'bpa_force_reinstallation_pro_'. date( 'Y-m-d H:i:s' ), json_encode( $check_lite_existance ) );
								$this->is_pro_reinstall = true;
							}
							
							
							update_option('bookingpress_lite_download_automatic', 1);

							if ( ! empty( $source ) ) {
								if ( $upgrader->install( $source ) === true ) {
									activate_plugin( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' );
									return true;
								}
							}
						}
					}
				}
			}
		}

		public static function update_bookingpress_lite() {
			if ( file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking/bookingpress-appointment-booking.php' ) ) {
				if ( ! function_exists( 'plugins_api' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				}
				$response                         = plugins_api(
					'plugin_information',
					array(
						'slug'   => 'bookingpress-appointment-booking',
						'fields' => array(
							'sections' => false,
							'versions' => true,
						),
					)
				);
				$bookingpress_plugin_data         = get_plugin_data( WP_PLUGIN_DIR . '/bookingpress-appointment-booking/bookingpress-appointment-booking.php' );
				$bookingpress_plugin_data_version = $bookingpress_plugin_data['Version'];
				if ( ! is_wp_error( $response ) ) {
					$latest_version = ! empty( $response->version ) ? $response->version : '';
				}
				if ( ! empty( $latest_version ) && version_compare( $bookingpress_plugin_data_version, $latest_version, '<' ) ) {
					if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
						if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
							require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
						}
						$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
						$source   = 'bookingpress-appointment-booking/bookingpress-appointment-booking.php';

						if ( ! empty( $source ) ) {
							if ( $upgrader->upgrade( $source ) === true ) {
								activate_plugin( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' );
								return true;
							}
						}
					}
				}
				else if( is_wp_error( $response ) ) 
				{
					$source_url = 'https://bookingpressplugin.com/bpa_misc/bkp_lite_plugin_install_api.php';
					$get_custom_response = wp_remote_get( $source_url, array( 'method' => 'GET') );
					if(!is_wp_error($get_custom_response))
					{
						$get_custom_response_body = json_decode(wp_remote_retrieve_body($get_custom_response));
						$latest_version = ! empty( $get_custom_response_body->version ) ? $get_custom_response_body->version : '';
						if(is_object($get_custom_response_body) && !empty($get_custom_response_body) && !empty( $latest_version ) && version_compare( $bookingpress_plugin_data_version, $latest_version, '<' ) )
						{
							if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
								require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
							}
							$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
							$source   = !empty( $get_custom_response_body->download_link ) ? $get_custom_response_body->download_link : '';
							
							if ( ! empty( $source ) ) {
								if ( $upgrader->upgrade( $source ) === true ) {
									activate_plugin( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' );
									return true;
								}
							}
						}
					}
				}
			} else {
				global $BookingPressPro;
				$BookingPressPro->install_bookingpress_lite();
			}
		}

		public static function install() {
			global $BookingPress, $BookingPressPro, $bookingpress_pro_version;

			$_version = get_option( 'bookingpress_pro_version' );
			
			$BookingPressPro->update_bookingpress_lite();

			if ( file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking/bookingpress-appointment-booking.php' ) && ! is_plugin_active( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' ) ) {
				activate_plugin( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' );
			}

			update_option( 'bpa_is_pro_resinstall', $BookingPressPro->is_pro_reinstall );

			if ( (empty( $_version ) || $_version == '') || true == $BookingPressPro->is_pro_reinstall ) {
				$BookingPressPro->install_bookingpress_lite();

				update_option( 'bookingpress_pro_version', $bookingpress_pro_version );
				update_option( 'bookingpress_pro_plugin_activated', 1 );

				$BookingPressPro->bookingpress_install_pro_plugin_data();

			} else {
				if ( ! file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking/bookingpress-appointment-booking.php' ) ) {
					echo "<div class='notice notice-error'><p>" . esc_html__( 'BookingPress Lite plugin is required to activate the BookingPress Premium plugin.', 'bookingpress-appointment-booking' ) . '</p></div>';
					exit;
				}
				do_action( 'bookingpress_pro_reactivate_plugin' );
			}

			$args  = array(
				'role'   => 'administrator',
				'fields' => 'id',
			);
			$users = get_users( $args );

			if ( count( $users ) > 0 ) {
				foreach ( $users as $key => $user_id ) {
					$bookingpressroles = $BookingPressPro->bookingpress_pro_capabilities();
					$userObj           = new WP_User( $user_id );
					foreach ( $bookingpressroles as $bookingpressrole => $bookingpress_roledescription ) {
						$userObj->add_cap( $bookingpressrole );
					}
					unset( $bookingpressrole );
					unset( $bookingpressroles );
					unset( $bookingpress_roledescription );
				}
			}
		}

		function bookingpress_install_pro_plugin_data(){
			global $tbl_bookingpress_notifications, $tbl_bookingpress_extra_services, $tbl_bookingpress_staff_member_workhours,$tbl_bookingpress_staffmembers_daysoff, $tbl_bookingpress_coupons, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_payment_logs, $tbl_bookingpress_entries,$tbl_bookingpress_cron_email_notifications_logs,$tbl_bookingpress_staffmembers,$tbl_bookingpress_staffmembers_meta,$tbl_bookingpress_staffmembers_services, $tbl_bookingpress_subscription_details,$tbl_bookingpress_staffmembers_special_day,$tbl_bookingpress_staffmembers_special_day_breaks, $tbl_bookingpress_debug_integration_logs,$bookingpress_default_special_day, $tbl_bookingpress_other_debug_logs, $tbl_bookingpress_default_special_day, $tbl_bookingpress_service_workhours,$tbl_bookingpress_service_special_day, $tbl_bookingpress_form_fields, $tbl_bookingpress_default_special_day_breaks,$tbl_bookingpress_service_special_day_breaks, $tbl_bookingpress_appointment_meta, $tbl_bookingpress_reschedule_history, $tbl_bookingpress_customize_settings,$tbl_bookingpress_services, $tbl_bookingpress_guests_data, $BookingPress, $BookingPressPro, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_service_daysoff;

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			@set_time_limit( 0 );
			global $wpdb, $bookingpress_pro_version;

			$charset_collate = '';
			if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty( $wpdb->charset ) ) {
					$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
				}
				if ( ! empty( $wpdb->collate ) ) {
					$charset_collate .= " COLLATE $wpdb->collate";
				}
			}

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_extra_services}`(
				`bookingpress_extra_services_id` int(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_service_id` int(11) NOT NULL,
				`bookingpress_extra_service_name` varchar(255) NOT NULL,
				`bookingpress_extra_service_duration` int(11) NOT NULL,
				`bookingpress_extra_service_duration_unit` varchar(1) NOT NULL,
				`bookingpress_extra_service_price` float NOT NULL,
				`bookingpress_extra_service_max_quantity` int(11) DEFAULT 1,
				`bookingpress_service_description` TEXT DEFAULT NULL,
				`bookingpress_extra_service_created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_extra_services_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_extra_services ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_staff_member_workhours}`(
				`bookingpress_staffmember_workhours_id` bigint(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_staffmember_id` smallint(6) NOT NULL ,
				`bookingpress_staffmember_workday_key` varchar(11) NOT NULL,
				`bookingpress_staffmember_workhours_start_time` time DEFAULT NULL,
				`bookingpress_staffmember_workhours_end_time` time DEFAULT NULL,
				`bookingpress_staffmember_workhours_is_break` TINYINT(1) DEFAULT 0,
				`bookingpress_staffmember_workhours_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_staffmember_workhours_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_staff_member_workhours ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_staffmembers_daysoff}`(
				`bookingpress_staffmember_daysoff_id` bigint(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_staffmember_id` smallint(6) NOT NULL,
				`bookingpress_staffmember_daysoff_name` varchar(100) NOT NULL,
				`bookingpress_staffmember_daysoff_date` date NOT NULL,
				`bookingpress_staffmember_daysoff_enddate` date DEFAULT NULL,
				`bookingpress_staffmember_daysoff_parent` int(11) NOT NULL DEFAULT 0,				
				`bookingpress_staffmember_daysoff_repeat` int(1) DEFAULT 0,
				`bookingpress_staffmember_daysoff_repeat_frequency` int(11) DEFAULT 1,
				`bookingpress_staffmember_daysoff_repeat_frequency_type` VARCHAR(10) DEFAULT 'year',
				`bookingpress_staffmember_daysoff_repeat_duration` VARCHAR( 15 ) DEFAULT 'forever',
				`bookingpress_staffmember_daysoff_repeat_times` int(11) DEFAULT 1,
				`bookingpress_staffmember_daysoff_repeat_date` DATE NOT NULL,
				`bookingpress_staffmember_daysoff_created` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_staffmember_daysoff_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_staffmembers_daysoff ] = dbDelta( $sql_table );
			
			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_coupons}`(
				`bookingpress_coupon_id` bigint(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_coupon_title` varchar(255) DEFAULT NULL,
				`bookingpress_coupon_code` varchar(255) NOT NULL,
				`bookingpress_coupon_discount` double,
				`bookingpress_coupon_discount_type` varchar(50) DEFAULT NULL,
				`bookingpress_coupon_period_type` varchar(50) DEFAULT NULL,
				`bookingpress_coupon_start_date` datetime DEFAULT NULL,
				`bookingpress_coupon_end_date` datetime DEFAULT NULL,
				`bookingpress_coupon_services` text DEFAULT NULL,
				`bookingpress_coupon_allowed_uses` int(11) DEFAULT 0,
				`bookingpress_coupon_used` int(11) DEFAULT 0,
				`bookingpress_coupon_status` int(1) DEFAULT 1,
				`bookingpress_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_coupon_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_coupons ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_cron_email_notifications_logs}`(
				`bookingpress_notification_id` int(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_email_notification_id` int(11) NOT NULL,
				`bookingpress_customer_id` int(11) NOT NULL,
				`bookingpress_staffmember_id` int(11) DEFAULT 0,
				`bookingpress_staffmember_email` varchar(255) NOT NULL DEFAULT '',
				`bookingpress_email_address` varchar(255) DEFAULT NULL,
				`bookingpress_appointment_id` int(11) NOT NULL,
				`bookingpress_appointment_date` DATE NOT NULL,
				`bookingpress_appointment_time` TIME NOT NULL,
				`bookingpress_appointment_status` smallint(1) DEFAULT 1,
				`bookingpress_email_sending_date` DATE NOT NULL,
				`bookingpress_email_sending_time` TIME NOT NULL,
				`bookingpress_email_is_sent` TINYINT(1) DEFAULT 0,
				`bookingpress_notification_type` varchar(20) DEFAULT 'email',
				`bookingpress_email_cron_hook_name` varchar(255) DEFAULT NULL,					
				`bookingpress_email_posted_data` TEXT DEFAULT NULL,
				`bookingpress_email_response` TEXT DEFAULT NULL,
				`bookingpress_email_sending_configuration` TEXT DEFAULT NULL,
				`bookingpress_created` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_notification_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_cron_email_notifications_logs ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_staffmembers}`(
				`bookingpress_staffmember_id` INT(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_wpuser_id` INT(11) DEFAULT NULL,
				`bookingpress_staffmember_position` INT(11) NOT NULL,
				`bookingpress_staffmember_login` VARCHAR(60) NOT NULL DEFAULT '',
				`bookingpress_staffmember_status` INT(1) NOT NULL,
				`bookingpress_staffmember_firstname` VARCHAR(255) NOT NULL,
				`bookingpress_staffmember_lastname` VARCHAR(255) NOT NULL,
				`bookingpress_staffmember_email` VARCHAR(255) NOT NULL,
				`bookingpress_staffmember_phone` VARCHAR(63) DEFAULT NULL,
				`bookingpress_staffmember_country_phone` VARCHAR(60) DEFAULT NULL,
				`bookingpress_staffmember_country_dial_code` VARCHAR(5) DEFAULT NULL,
				`bookingpress_staffmember_created` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_staffmember_id`)
			) {$charset_collate};";

			$bookingpress_dbtbl_create[ $tbl_bookingpress_staffmembers ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_staffmembers_meta}`(
				`bookingpress_staffmembermeta_id` int(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_staffmember_id` int(11) NOT NULL,
				`bookingpress_staffmembermeta_key` varchar(255) NOT NULL,
				`bookingpress_staffmembermeta_value` LONGTEXT DEFAULT NULL,
				`bookingpress_staffmembermeta_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_staffmembermeta_id`)
			) {$charset_collate};";

			$bookingpress_dbtbl_create[ $tbl_bookingpress_staffmembers_meta ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_staffmembers_services}`(
				`bookingpress_staffmember_service_id` int(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_staffmember_id` int(11) NOT NULL,
				`bookingpress_service_id` int(11) NOT NULL,
				`bookingpress_service_price` float NOT NULL,
				`bookingpress_service_capacity` int(11) DEFAULT 1,
				`bookingpress_service_min_capacity` int(11) DEFAULT 1,
				`bookingpress_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_staffmember_service_id`)
			) {$charset_collate};";

			$bookingpress_dbtbl_create[ $tbl_bookingpress_staffmembers_services ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_subscription_details}`(
				`bookingpress_subscription_detail_id` int(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_customer_id` bigint(11) DEFAULT 0,
				`bookingpress_entry_id` bigint(11) DEFAULT 0,
				`bookingpress_appointment_booking_id` bigint(11) DEFAULT 0,
				`bookingpress_customer_email` varchar(255) DEFAULT NULL,
				`bookingpress_subscription_id` varchar(50) DEFAULT NULL,
				`bookingpress_transaction_id` varchar(50) DEFAULT NULL,
				`bookingpress_subscription_cycle_type` varchar(30) DEFAULT NULL,
				`bookingpress_subscription_total_cycle` varchar(10) DEFAULT NULL,
				`bookingpress_subscription_completed_cycle` varchar(10) DEFAULT NULL,
				`bookingpress_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_subscription_detail_id`)
			) {$charset_collate};";

			$bookingpress_dbtbl_create[ $tbl_bookingpress_subscription_details ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_staffmembers_special_day}`(
				`bookingpress_staffmember_special_day_id` smallint NOT NULL AUTO_INCREMENT,
				`bookingpress_staffmember_id` smallint(6) NOT NULL,
				`bookingpress_special_day_service_id` VARCHAR (255) DEFAULT NULL,
				`bookingpress_special_day_start_date` datetime DEFAULT NULL,
				`bookingpress_special_day_end_date` datetime DEFAULT NULL,
				`bookingpress_special_day_start_time` time DEFAULT NULL,
				`bookingpress_special_day_end_time` time DEFAULT NULL,
				`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_staffmember_special_day_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_staffmembers_special_day ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_staffmembers_special_day_breaks}`(
				`bookingpress_staffmember_special_day_break_id` smallint NOT NULL AUTO_INCREMENT,
				`bookingpress_special_day_id` smallint NOT NULL,
				`bookingpress_special_day_break_start_time` time DEFAULT NULL,
				`bookingpress_special_day_break_end_time` time DEFAULT NULL,					
				`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_staffmember_special_day_break_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_staffmembers_special_day_breaks ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_debug_integration_logs}`(
				`bookingpress_integration_log_id` int(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_integration_log_ref_id` int(11) NOT NULL,
				`bookingpress_integration_type` varchar(255) DEFAULT NULL,
				`bookingpress_integration_type_title` varchar(255) DEFAULT NULL,
				`bookingpress_integration_event` varchar(255) DEFAULT NULL,
				`bookingpress_integration_event_from` varchar(255) DEFAULT NULL,
				`bookingpress_integration_raw_data` TEXT DEFAULT NULL,		
				`bookingpress_integration_log_added_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_integration_log_id`)
			) {$charset_collate};";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_debug_integration_logs ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_default_special_day}`(
				`bookingpress_special_day_id` smallint NOT NULL AUTO_INCREMENT,					
				`bookingpress_special_day_start_date` datetime DEFAULT NULL,
				`bookingpress_special_day_end_date` datetime DEFAULT NULL,
				`bookingpress_special_day_start_time` time DEFAULT NULL,
				`bookingpress_special_day_end_time` time DEFAULT NULL,
				`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_special_day_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_default_special_day ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_default_special_day_breaks}`(
				`bookingpress_special_day_break_id` smallint NOT NULL AUTO_INCREMENT,
				`bookingpress_special_day_id` smallint NOT NULL,
				`bookingpress_special_day_break_start_time` time DEFAULT NULL,
				`bookingpress_special_day_break_end_time` time DEFAULT NULL,
				`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_special_day_break_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_default_special_day_breaks ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_service_special_day}`(
				`bookingpress_service_special_day_id` smallint NOT NULL AUTO_INCREMENT,
				`bookingpress_service_id` smallint(6) NOT NULL,
				`bookingpress_special_day_start_date` datetime DEFAULT NULL,
				`bookingpress_special_day_end_date` datetime DEFAULT NULL,
				`bookingpress_special_day_start_time` time DEFAULT NULL,
				`bookingpress_special_day_end_time` time DEFAULT NULL,
				`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_service_special_day_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_service_special_day ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_service_special_day_breaks}`(
				`bookingpress_service_special_day_break_id` smallint NOT NULL AUTO_INCREMENT,
				`bookingpress_special_day_id` smallint NOT NULL,
				`bookingpress_special_day_break_start_time` time DEFAULT NULL,
				`bookingpress_special_day_break_end_time` time DEFAULT NULL,					
				`bookingpress_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_service_special_day_break_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_service_special_day_breaks ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_service_workhours}`(
				`bookingpress_service_workhours_id` bigint(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_service_id` smallint(6) NOT NULL ,
				`bookingpress_service_workday_key` varchar(11) NOT NULL,
				`bookingpress_service_workhours_start_time` time DEFAULT NULL,
				`bookingpress_service_workhours_end_time` time DEFAULT NULL,
				`bookingpress_service_workhours_is_break` TINYINT(1) DEFAULT 0,
				`bookingpress_service_workhours_created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_service_workhours_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_service_workhours ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_appointment_meta}`(
				`bookingpress_appointment_meta_id` bigint(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_entry_id` bigint(11) DEFAULT 0,
				`bookingpress_order_id` bigint(11) DEFAULT 0,
				`bookingpress_appointment_id` bigint(11) DEFAULT 0,
				`bookingpress_appointment_meta_key` varchar(255) NOT NULL,
				`bookingpress_appointment_meta_value` TEXT DEFAULT NULL,
				`bookingpress_appointment_meta_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_appointment_meta_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_appointment_meta ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_reschedule_history}`(
				`bookingpress_reschedule_id` bigint(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_appointment_id` bigint(11) DEFAULT 0,
				`bookingpress_appointment_original_date` DATE NOT NULL,
				`bookingpress_appointment_original_start_time` TIME NOT NULL,
				`bookingpress_appointment_original_end_time` TIME NOT NULL,
				`bookingpress_appointment_new_date` DATE NOT NULL,
				`bookingpress_appointment_new_start_time` TIME NOT NULL,
				`bookingpress_appointment_new_end_time` TIME NOT NULL,
				`bookingpress_reschedule_from` smallint DEFAULT 1,
				`bookingpress_wp_user_id` bigint(11) DEFAULT 0,
				`bookingpress_customer_id` bigint(11) DEFAULT 0,
				`bookingpress_appointment_reschedule_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_reschedule_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_reschedule_history ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_guests_data}`(
				`bookingpress_guest_data_id` bigint(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_guest_data_appointment_id` bigint(11) NOT NULL DEFAULT 0,        
				`bookingpress_guest_data_repeater_id` bigint(11) NOT NULL DEFAULT 0,
				`bookingpress_guest_data_guest_no` bigint(11) NOT NULL DEFAULT 0,
				`bookingpress_guest_data_field_metakey` VARCHAR(255) NOT NULL,
				`bookingpress_guest_data_field_metavalue` TEXT DEFAULT NULL,
				`bookingpress_guest_data_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_guest_data_id`)
			) {$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_guests_data ] = dbDelta( $sql_table );

			$sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_service_daysoff}`(
				`bookingpress_service_daysoff_id` bigint(11) NOT NULL AUTO_INCREMENT,
				`bookingpress_service_id` bigint(11) NOT NULL,
				`bookingpress_service_daysoff_name` varchar(100) NOT NULL,
				`bookingpress_service_daysoff_date` date NOT NULL,
				`bookingpress_service_daysoff_enddate` date DEFAULT NULL,
				`bookingpress_service_daysoff_parent` int(11) NOT NULL DEFAULT 0,
				`bookingpress_service_daysoff_repeat` int(1) DEFAULT 0,
				`bookingpress_service_daysoff_repeat_frequency` int(11) DEFAULT 1,
				`bookingpress_service_daysoff_repeat_frequency_type` VARCHAR(10) DEFAULT 'year',
				`bookingpress_service_daysoff_repeat_duration` VARCHAR( 15 ) DEFAULT 'forever',
				`bookingpress_service_daysoff_repeat_times` int(11) DEFAULT 1,
				`bookingpress_service_daysoff_repeat_date` DATE NOT NULL,
				`bookingpress_service_daysoff_created` timestamp DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`bookingpress_service_daysoff_id`)
			){$charset_collate}";
			$bookingpress_dbtbl_create[ $tbl_bookingpress_service_daysoff ] = dbDelta( $sql_table );
			
			$post_table = $wpdb->posts;
			$tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
		
			$post_author = get_current_user_id();
			$bookingpress_reschedule_content= '<div class="wp-block-bookingpress-reschedule">[bookingpress_appointment_reschedule]</div>';
			$bookingpress_reschedule_page_details = array(
				'post_title'   => esc_html__('Appointment reschedule', 'bookingpress-appointment-booking'),
				'post_name'    => 'appointment-reschedule',
				'post_content' => $bookingpress_reschedule_content,
				'post_status'  => 'publish',
				'post_parent'  => 0,
				'post_author'  => 1,
				'post_type'    => 'page',
				'post_author'   => $post_author,
				'post_date'     => current_time( 'mysql' ),
				'post_date_gmt' => current_time( 'mysql', 1 ),
			);
			
			$wpdb->insert( $post_table, $bookingpress_reschedule_page_details );
			$bookingpress_reschedule_post_id = $wpdb->insert_id;
		
			$current_guid = get_post_field( 'guid', $bookingpress_reschedule_post_id );
			$where = array( 'ID' => $bookingpress_reschedule_post_id );
			if( '' === $current_guid ){
				$wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $bookingpress_reschedule_post_id ) ), $where );
			}
			
			$bookingpress_reschedule_url = get_permalink($bookingpress_reschedule_post_id);
			if (! empty($bookingpress_reschedule_url) ) {
				$bookingpress_my_booking_customize_setting['appointment_reschedule_page'] = $bookingpress_reschedule_post_id;                
				foreach($bookingpress_my_booking_customize_setting as $key => $val){
					$bookingpress_bd_data = array(
						'bookingpress_setting_name' => $key,
						'bookingpress_setting_value' => $val,
						'bookingpress_setting_type' => 'booking_my_booking',
					);
					$wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_bd_data);        
				}             
			} 



			$db_error_details = array();
			$bpa_missing_columns = array();

			$bookingpress_add_notification_type_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_custom_notification_type'", DB_NAME, $tbl_bookingpress_notifications ) );
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
			}
			if ( empty( $bookingpress_add_notification_type_col ) ) {
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_notifications}` ADD `bookingpress_custom_notification_type` VARCHAR(60) DEFAULT '' AFTER `bookingpress_notification_type`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
					$bpa_missing_columns[] = 'bookingpress_custom_notification_type|tbl_bookingpress_notifications';
				}
			}
			$bookingpress_add_notification_service_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_notification_service'", DB_NAME, $tbl_bookingpress_notifications ) );
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
			}
			if ( empty( $bookingpress_add_notification_service_col ) ) {
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_notifications}` ADD `bookingpress_notification_service` TEXT DEFAULT NULL AFTER `bookingpress_custom_notification_type`" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
					$bpa_missing_columns[] = 'bookingpress_notification_service|tbl_bookingpress_notifications';
				}
			}

			$bookingpress_add_notification_scheduled_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_notification_scheduled_type'", DB_NAME, $tbl_bookingpress_notifications ) );
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
			}
			if ( empty( $bookingpress_add_notification_scheduled_col ) ) {
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_notifications}` ADD `bookingpress_notification_scheduled_type` VARCHAR(255) DEFAULT '' AFTER `bookingpress_notification_service`" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
					$bpa_missing_columns[] = 'bookingpress_notification_scheduled_type|tbl_bookingpress_notifications';
				}
			}

			$bookingpress_add_notification_duration_val_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_notification_duration_val'", DB_NAME, $tbl_bookingpress_notifications ) );
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
			}
			if ( empty( $bookingpress_add_notification_duration_val_col ) ) {
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_notifications}` ADD `bookingpress_notification_duration_val` INT(11) NOT NULL DEFAULT 0 AFTER `bookingpress_notification_scheduled_type`" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
					$bpa_missing_columns[] = 'bookingpress_notification_duration_val|tbl_bookingpress_notifications';
				}
			}
			$bookingpress_add_notification_duration_unit_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_notification_duration_unit'", DB_NAME, $tbl_bookingpress_notifications ) );
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
			}
			if ( empty( $bookingpress_add_notification_duration_unit_col ) ) {
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_notifications}` ADD `bookingpress_notification_duration_unit` VARCHAR(60) DEFAULT '' AFTER `bookingpress_notification_duration_val`" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
					$bpa_missing_columns[] = 'bookingpress_notification_duration_unit|tbl_bookingpress_notifications';
				}
			}

			$bookingpress_add_notification_duration_time_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_notification_duration_time'", DB_NAME, $tbl_bookingpress_notifications ) );
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
			}
			if ( empty( $bookingpress_add_notification_duration_time_col ) ) {
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_notifications}` ADD `bookingpress_notification_duration_time` VARCHAR(60) DEFAULT '' AFTER `bookingpress_notification_duration_val`" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
					$bpa_missing_columns[] = 'bookingpress_notification_duration_time|tbl_bookingpress_notifications';
				}
			}

			$bookingpress_add_cc_email_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_notification_cc_email'", DB_NAME, $tbl_bookingpress_notifications ) );
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
			}
			if ( empty( $bookingpress_add_cc_email_col ) ) {
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_notifications}` ADD `bookingpress_notification_cc_email` VARCHAR(255) DEFAULT '' AFTER `bookingpress_notification_duration_time`" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
					$bpa_missing_columns[] = 'bookingpress_notification_cc_email|tbl_bookingpress_notifications';
				}
			}
			
			$bookingpress_notification_attach_ics_file_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_notification_attach_ics_file'", DB_NAME, $tbl_bookingpress_notifications ) );
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
			}
			if ( empty( $bookingpress_notification_attach_ics_file_col ) ) {
				$wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_notification_attach_ics_file INT(1) DEFAULT 0 AFTER bookingpress_notification_duration_unit");// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
					$bpa_missing_columns[] = 'bookingpress_notification_attach_ics_file|tbl_bookingpress_notifications';
				}
			}	

			// Update total amount field
			//=====================================================================================================================================

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_total_amount float DEFAULT 0 AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_total_amount|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_total_amount float DEFAULT 0 AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_total_amount|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_total_amount float DEFAULT 0 AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_total_amount|tbl_bookingpress_payment_logs';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_refund_initiate_from smallint(1) DEFAULT 0 AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_refund_initiate_from|tbl_bookingpress_payment_logs';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_refund_type varchar(20) DEFAULT NULL AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_refund_type|tbl_bookingpress_payment_logs';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_refund_amount float DEFAULT 0 AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_refund_amount|tbl_bookingpress_payment_logs';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_refund_reason TEXT DEFAULT NULL AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_refund_reason|tbl_bookingpress_payment_logs';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_refund_response TEXT DEFAULT NULL AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_refund_response|tbl_bookingpress_payment_logs';
			}

			//=====================================================================================================================================

				// Update bookingpress_is_reschedule field
			//=====================================================================================================================================
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_is_reschedule smallint(1) DEFAULT 0 AFTER bookingpress_total_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_is_reschedule|tbl_bookingpress_appointment_bookings';
			}
			//=====================================================================================================================================

			// Update coupon fields
			//=====================================================================================================================================

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_coupon_details text DEFAULT NULL AFTER bookingpress_appointment_status" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_coupon_details|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_coupon_discount_amount float DEFAULT 0 AFTER bookingpress_coupon_details" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_coupon_discount_amount|tbl_bookingpress_entries';
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_coupon_details text DEFAULT NULL AFTER bookingpress_appointment_status" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_coupon_details|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_coupon_discount_amount float DEFAULT 0 AFTER bookingpress_coupon_details" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_coupon_discount_amount|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_dst_timezone TINYINT NULL DEFAULT '0' AFTER `bookingpress_appointment_timezone`" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_dst_timezone|tbl_bookingpress_appointment_bookings';
			}
			
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_coupon_details text DEFAULT NULL AFTER bookingpress_additional_info" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_coupon_details|tbl_bookingpress_payment_logs';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_coupon_discount_amount float DEFAULT 0 AFTER bookingpress_coupon_details" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_coupon_discount_amount|tbl_bookingpress_payment_logs';
			}

			//=====================================================================================================================================
			//Add service Expiration date column
			// ====================================================================================================================================
			$bookingpress_service_expiration_date_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_service_expiration_date'", DB_NAME, $tbl_bookingpress_services ) );
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
			}
			if ( empty( $bookingpress_service_expiration_date_col ) ) {
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_services}` ADD `bookingpress_service_expiration_date` DATE DEFAULT NUll AFTER `bookingpress_service_position`" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
					$bpa_missing_columns[] = 'bookingpress_service_expiration_date|tbl_bookingpress_services';
				}
			}

			//=====================================================================================================================================
			//Add service start date column
			// ====================================================================================================================================
			$bookingpress_service_start_date_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_service_start_date'", DB_NAME, $tbl_bookingpress_services ) );
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
			}
			if ( empty( $bookingpress_service_expiration_date_col ) ) {
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_services}` ADD `bookingpress_service_start_date` DATE DEFAULT NUll AFTER `bookingpress_service_expiration_date`" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
					$bpa_missing_columns[] = 'bookingpress_service_start_date|tbl_bookingpress_services';
				}
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_tax_percentage float DEFAULT 0 AFTER bookingpress_coupon_discount_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_tax_percentage|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_tax_amount float DEFAULT 0 AFTER bookingpress_tax_percentage" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_tax_amount|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD `bookingpress_dst_timezone` TINYINT NULL DEFAULT '0' AFTER `bookingpress_customer_timezone`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_dst_timezone|tbl_bookingpress_entries';
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_tax_percentage float DEFAULT 0 AFTER bookingpress_coupon_discount_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_tax_percentage|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_tax_amount float DEFAULT 0 AFTER bookingpress_tax_percentage" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_tax_amount|tbl_bookingpress_appointment_bookings';
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_tax_percentage float DEFAULT 0 AFTER bookingpress_coupon_discount_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_tax_percentage|tbl_bookingpress_payment_logs';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_tax_amount float DEFAULT 0 AFTER bookingpress_tax_percentage" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_tax_amount|tbl_bookingpress_payment_logs';
			}

			$wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD `bookingpress_price_display_setting` varchar(20) DEFAULT 'exclude_taxes' AFTER bookingpress_tax_amount"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_price_display_setting|tbl_bookingpress_entries';
			}
			$wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD `bookingpress_display_tax_order_summary` smallint(6) DEFAULT 1 AFTER bookingpress_price_display_setting"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_display_tax_order_summary|tbl_bookingpress_entries';
			}
			$wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD `bookingpress_included_tax_label` varchar(255) DEFAULT NULL AFTER bookingpress_display_tax_order_summary"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_included_tax_label|tbl_bookingpress_entries';
			}

			$wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD `bookingpress_price_display_setting` varchar(20) DEFAULT 'exclude_taxes' AFTER bookingpress_tax_amount"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_price_display_setting|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD `bookingpress_display_tax_order_summary` smallint(6) DEFAULT 1 AFTER bookingpress_price_display_setting"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_display_tax_order_summary|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD `bookingpress_included_tax_label` varchar(255) DEFAULT NULL AFTER bookingpress_display_tax_order_summary"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_included_tax_label|tbl_bookingpress_appointment_bookings';
			}

			$wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD `bookingpress_price_display_setting` varchar(20) DEFAULT 'exclude_taxes' AFTER bookingpress_tax_amount"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_price_display_setting|tbl_bookingpress_payment_logs';
			}
			$wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD `bookingpress_display_tax_order_summary` smallint(6) DEFAULT 1 AFTER bookingpress_price_display_setting"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_display_tax_order_summary|tbl_bookingpress_payment_logs';
			}
			$wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD `bookingpress_included_tax_label` varchar(255) DEFAULT NULL AFTER bookingpress_display_tax_order_summary"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_included_tax_label|tbl_bookingpress_payment_logs';
			}

			//=====================================================================================================================================

			// Update recurring fields
			//=====================================================================================================================================

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_recurring_details text DEFAULT NULL AFTER bookingpress_tax_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_recurring_details|tbl_bookingpress_entries';
			}

			//=====================================================================================================================================


			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} ADD bookingpress_field_type varchar(20) NOT NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_field_type|tbl_bookingpress_form_fields';
			}
			
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} ADD bookingpress_field_options text" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_field_options|tbl_bookingpress_form_fields';
			}

			// for checkbox/radio/select picker
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} ADD bookingpress_field_values text" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_field_values|tbl_bookingpress_form_fields';
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} ADD bookingpress_field_meta_key varchar(50) NOT NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_field_meta_key|tbl_bookingpress_form_fields';
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} ADD bookingpress_field_css_class varchar(50) NOT NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_field_css_class|tbl_bookingpress_form_fields';
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} ADD bookingpress_is_customer_field tinyint(1) DEFAULT 0"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_is_customer_field|tbl_bookingpress_form_fields';
			}


			// Update deposit payment module fields
			//=====================================================================================================================================

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_deposit_payment_details TEXT DEFAULT NULL AFTER bookingpress_tax_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_deposit_payment_details|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_deposit_amount float DEFAULT 0 AFTER bookingpress_deposit_payment_details" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_deposit_amount|tbl_bookingpress_entries';
			}
			
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_deposit_payment_details TEXT DEFAULT NULL AFTER bookingpress_tax_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_deposit_payment_details|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_deposit_amount float DEFAULT 0 AFTER bookingpress_deposit_payment_details" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_deposit_amount|tbl_bookingpress_appointment_bookings';
			}
			
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_deposit_payment_details TEXT DEFAULT NULL AFTER bookingpress_tax_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_deposit_payment_details|tbl_bookingpress_payment_logs';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_deposit_amount float DEFAULT 0 AFTER bookingpress_deposit_payment_details" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_deposit_amount|tbl_bookingpress_payment_logs';
			}

			//=====================================================================================================================================
			

			// Update Bring Guest with you module field
			//=====================================================================================================================================

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_selected_extra_members smallint(6) DEFAULT 1 AFTER bookingpress_deposit_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_selected_extra_members|tbl_bookingpress_entries';
			}
			
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_selected_extra_members smallint(6) DEFAULT 1 AFTER bookingpress_deposit_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_selected_extra_members|tbl_bookingpress_appointment_bookings';
			}
			//=====================================================================================================================================
			
			// Update extra services module field
			//=====================================================================================================================================
			
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_extra_service_details TEXT DEFAULT NULL AFTER bookingpress_selected_extra_members" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_extra_service_details|tbl_bookingpress_entries';
			}
			
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_extra_service_details TEXT DEFAULT NULL AFTER bookingpress_selected_extra_members" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_extra_service_details|tbl_bookingpress_appointment_bookings';
			}
			//=====================================================================================================================================

			// Update staff members module fields
			//=====================================================================================================================================

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_staff_member_id int(11) DEFAULT 0 AFTER bookingpress_extra_service_details" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_member_id|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_staff_member_price float DEFAULT 0 AFTER bookingpress_staff_member_id" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_member_price|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_staff_member_details TEXT DEFAULT NULL AFTER bookingpress_staff_member_price" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_member_details|tbl_bookingpress_entries';
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_staff_member_price float DEFAULT 0 AFTER bookingpress_staff_member_id" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_member_price|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_staff_member_details TEXT DEFAULT NULL AFTER bookingpress_staff_member_price" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_member_details|tbl_bookingpress_appointment_bookings';
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_staff_member_price float DEFAULT 0 AFTER bookingpress_staff_member_id" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_member_price|tbl_bookingpress_payment_logs';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_staff_member_details TEXT DEFAULT NULL AFTER bookingpress_staff_member_price" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_member_details|tbl_bookingpress_payment_logs';
			}
			
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_staff_first_name varchar(100) DEFAULT NULL AFTER bookingpress_staff_member_price" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_first_name|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_staff_first_name varchar(100) DEFAULT NULL AFTER bookingpress_staff_member_price" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_first_name|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_staff_first_name varchar(100) DEFAULT NULL AFTER bookingpress_staff_member_price" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_first_name|tbl_bookingpress_payment_logs';
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_staff_last_name varchar(100) DEFAULT NULL AFTER bookingpress_staff_first_name" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_last_name|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_staff_last_name varchar(100) DEFAULT NULL AFTER bookingpress_staff_first_name" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_last_name|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_staff_last_name varchar(100) DEFAULT NULL AFTER bookingpress_staff_first_name" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_last_name|tbl_bookingpress_payment_logs';
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_staff_email_address varchar(100) DEFAULT NULL AFTER bookingpress_staff_last_name" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_email_address|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_staff_email_address varchar(100) DEFAULT NULL AFTER bookingpress_staff_last_name" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_email_address|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_staff_email_address varchar(100) DEFAULT NULL AFTER bookingpress_staff_last_name" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_staff_email_address|tbl_bookingpress_payment_logs';
			}

			//=====================================================================================================================================

			// Update order_id field
			//=====================================================================================================================================

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_order_id int(11) DEFAULT 0 AFTER bookingpress_entry_id" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_order_id|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_order_id int(11) DEFAULT 0 AFTER bookingpress_appointment_booking_id" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_order_id|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_order_id int(11) DEFAULT 0 AFTER bookingpress_payment_log_id" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_order_id|tbl_bookingpress_payment_logs';
			}

			//=====================================================================================================================================

			// Update is_cart field
			//=====================================================================================================================================

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_is_cart smallint(1) DEFAULT 0 AFTER bookingpress_order_id" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_is_cart|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_is_cart smallint(1) DEFAULT 0 AFTER bookingpress_order_id" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_is_cart|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_is_cart smallint(1) DEFAULT 0 AFTER bookingpress_order_id" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_is_cart|tbl_bookingpress_payment_logs';
			}

			//=====================================================================================================================================


			// Update mark_as_paid field value
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_mark_as_paid smallint(1) DEFAULT 1 AFTER bookingpress_total_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_mark_as_paid|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_mark_as_paid smallint(1) DEFAULT 1 AFTER bookingpress_total_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_mark_as_paid|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_mark_as_paid smallint(1) DEFAULT 1 AFTER bookingpress_total_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_mark_as_paid|tbl_bookingpress_payment_logs';
			}

			//ADD column password field 
			//====================================================================================================================================
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_password varchar(255) DEFAULT NULL AFTER bookingpress_username" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_password|tbl_bookingpress_entries';
			}

			// Add columns for complete payment url
			//=====================================================================================================================================
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_complete_payment_url_selection varchar(20) DEFAULT NULL AFTER bookingpress_mark_as_paid" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_complete_payment_url_selection|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_complete_payment_url_selection varchar(20) DEFAULT NULL AFTER bookingpress_mark_as_paid" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_complete_payment_url_selection|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_complete_payment_url_selection varchar(20) DEFAULT NULL AFTER bookingpress_mark_as_paid" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_complete_payment_url_selection|tbl_bookingpress_payment_logs';
			}
			
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_complete_payment_url_selection_method varchar(20) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_complete_payment_url_selection_method|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_complete_payment_url_selection_method varchar(20) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_complete_payment_url_selection_method|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_complete_payment_url_selection_method varchar(20) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_complete_payment_url_selection_method|tbl_bookingpress_payment_logs';
			}

			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_complete_payment_token varchar(255) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection_method" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_complete_payment_token|tbl_bookingpress_entries';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_complete_payment_token varchar(255) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection_method" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_complete_payment_token|tbl_bookingpress_appointment_bookings';
			}
			$wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_complete_payment_token varchar(255) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection_method" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_complete_payment_token|tbl_bookingpress_payment_logs';
			}


			// =========================================================================================================================

			// update Holiday table columns with default data
			// =========================================================================================================================
			$wpdb->query("ALTER TABLE {$tbl_bookingpress_default_daysoff} ADD `bookingpress_dayoff_repeat_frequency` INT(11) NOT NULL DEFAULT '1' AFTER `bookingpress_repeat`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table nam defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_dayoff_repeat_frequency|tbl_bookingpress_default_daysoff';
			}

			$wpdb->query("ALTER TABLE {$tbl_bookingpress_default_daysoff} ADD `bookingpress_dayoff_repeat_frequency_type`VARCHAR(10) NOT NULL DEFAULT 'year' AFTER `bookingpress_dayoff_repeat_frequency`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table nam defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_dayoff_repeat_frequency_type|tbl_bookingpress_default_daysoff';
			}

			$wpdb->query("ALTER TABLE {$tbl_bookingpress_default_daysoff} ADD `bookingpress_dayoff_repeat_duration` VARCHAR(15) NOT NULL DEFAULT 'forever' AFTER `bookingpress_dayoff_repeat_frequency_type`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table nam defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_dayoff_repeat_duration|tbl_bookingpress_default_daysoff';
			}

			$wpdb->query("ALTER TABLE {$tbl_bookingpress_default_daysoff} ADD `bookingpress_dayoff_repeat_times` INT(11) NOT NULL DEFAULT '1' AFTER `bookingpress_dayoff_repeat_duration`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table nam defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_dayoff_repeat_times|tbl_bookingpress_default_daysoff';
			}

			$wpdb->query("ALTER TABLE {$tbl_bookingpress_default_daysoff} ADD `bookingpress_dayoff_repeat_date` DATE NOT NULL AFTER `bookingpress_dayoff_repeat_times`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table nam defined globally. False Positive alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				$bpa_missing_columns[] = 'bookingpress_dayoff_repeat_date|tbl_bookingpress_default_daysoff';
			}


			if( empty( $db_error_details ) ){
				update_option( 'bookingpress_missing_columns', json_encode( $bpa_missing_columns ) );
				update_option( 'bookingpress_reattempt_installer', false );
			} else {
				/** Stop further execution until all data get installed */
				update_option( 'bpa_db_error_details', $db_error_details );
				update_option( 'bpa_db_error_details_attempted_' . date('Y-m-d H:i:s', current_time('timestamp') ), $db_error_details );
				update_option( 'bookingpress_missing_columns', json_encode( $bpa_missing_columns ) );
				update_option( 'bookingpress_missing_columns_attempted_' . date('Y-m-d H:i:s', current_time( 'timestamp' ) ), json_encode( $bpa_missing_columns ) );
				update_option( 'bookingpress_reattempt_installer', true );
				return;
			}

			// ====================================================================================================================================

			// Update Company Icon Fields Data
			//=====================================================================================================================================
			$BookingPress->bookingpress_update_settings('company_icon_img', 'company_setting', '');
			$BookingPress->bookingpress_update_settings('company_icon_url', 'company_setting', '');
			$BookingPress->bookingpress_update_settings('company_icon_list', 'company_setting', '');
			//=====================================================================================================================================

			// Update tax fields
			//=====================================================================================================================================

			$BookingPress->bookingpress_update_settings('price_settings_and_display', 'payment_setting', 'exclude_taxes');
			$BookingPress->bookingpress_update_settings('display_tax_order_summary', 'payment_setting', 'true');

			$bookingpress_included_tax_label = "(".esc_html__('Inc. GST', 'bookingpress-appointment-booking').")";
			$BookingPress->bookingpress_update_settings('included_tax_label', 'payment_setting', $bookingpress_included_tax_label);

			// Update Message Setting Data
			//=====================================================================================================================================
			$BookingPress->bookingpress_update_settings('payment_token_failure_message', 'message_setting' , __('Payment token incorrect or mismatch', 'bookingpress-appointment-booking'));
			$BookingPress->bookingpress_update_settings('payment_already_paid_message', 'message_setting' , __('Payment already completed', 'bookingpress-appointment-booking'));
			$BookingPress->bookingpress_update_settings('complete_payment_success_message', 'message_setting' , __('Payment completed successfully', 'bookingpress-appointment-booking'));
			//=====================================================================================================================================

			// Update customize labels
			//=====================================================================================================================================

			$bookingpress_db_fields = array(
				'bookingpress_setting_name'  => 'complete_payment_deposit_amt_title',
				'bookingpress_setting_value' => __('Deposit Paid','bookingpress-appointment-booking'),
				'bookingpress_setting_type'  => 'booking_form',
			);        
			$wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);

			$bookingpress_db_fields = array(
				'bookingpress_setting_name'  => 'make_payment_button_title',
				'bookingpress_setting_value' => __('Make Payment','bookingpress-appointment-booking'),
				'bookingpress_setting_type'  => 'booking_form',
			);        
			$wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);

			//=====================================================================================================================================

			// Add complete payment page
			//=====================================================================================================================================

			$bookingpress_complete_payment_content = '[bookingpress_complete_payment]';
			$bookingpress_complete_payment_details = array(
				'post_title'   => esc_html__('Complete Payment', 'bookingpress-appointment-booking'),
				'post_name'    => 'bookingpress-complete-payment',
				'post_content' => $bookingpress_complete_payment_content,
				'post_status'  => 'publish',
				'post_parent'  => 0,
				'post_author'  => 1,
				'post_type'    => 'page',
			);
			$bookingpress_post_id = wp_insert_post($bookingpress_complete_payment_details);
			$BookingPress->bookingpress_update_settings('complete_payment_page_id', 'general_setting', $bookingpress_post_id);

			//=====================================================================================================================================

			/* update setting for all category default select  */
			$BookingPress->bookingpress_update_settings('share_timeslot_between_services_type', 'general_setting', 'all_service');
			
			//=====================================================================================================================================

			/** update pre-defined field's type */
			$all_fields = $wpdb->get_results( "SELECT bookingpress_form_field_id, bookingpress_form_field_name,bookingpress_field_is_hide FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_form_field_id ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
			if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
				$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
			}

			if ( ! empty( $all_fields ) ) {
				foreach ( $all_fields as $field_data ) {
					$bookingpress_field_id   = $field_data->bookingpress_form_field_id;
					$bookingpress_field_name = $field_data->bookingpress_form_field_name;
					$bookingpress_field_is_hide =$field_data->bookingpress_field_is_hide;
					$bookingpress_visibility = !empty($bookingpress_field_is_hide) && $bookingpress_field_is_hide == '1' ? 'hidden' : 'always';
					
					$bookingpress_field_options = array(
						'layout' => '1col',														
						'used_for_user_information' => 'true',							
						'separate_value' => false,
						'visibility' => $bookingpress_visibility,							
						'minimum'=> '',
						'maximum'=> '',
						'selected_services' => [],							 
					);

					$field_type = 'text';
					if ( 'email_address' == $bookingpress_field_name ) {
						$field_type = 'email';
						unset($bookingpress_field_options['minimum']);
						unset($bookingpress_field_options['maximum']);
					} elseif ( 'phone_number' == $bookingpress_field_name ) {
						$field_type = 'phone';
						unset($bookingpress_field_options['minimum']);
						unset($bookingpress_field_options['maximum']);
					} elseif ( 'note' == $bookingpress_field_name ) {
						$field_type = 'textarea';
					} elseif( 'terms_and_conditions' == $bookingpress_field_name){
						$field_type = 'terms_and_conditions';
						unset($bookingpress_field_options['minimum']);
						unset($bookingpress_field_options['maximum']);
					} elseif( 'username' == $bookingpress_field_name ){
						$field_type = 'text';
					}

					$field_meta_key = $field_type . '_' . wp_generate_password( 6, false );

					$wpdb->update(
						$tbl_bookingpress_form_fields,
						array(
							'bookingpress_field_type' => $field_type,
							'bookingpress_field_meta_key' => $field_meta_key,
							'bookingpress_field_options' => wp_json_encode($bookingpress_field_options),
						),
						array(
							'bookingpress_form_field_id' => $bookingpress_field_id,
						)
					);
				}
			}

			$bookingpress_default_general_setting_form_options_pro = array(
				'service_buffer_time'                => 'false',
				'default_minimum_time_for_booking'   => 'disabled',
				'default_minimum_time_for_canceling' => 'disabled',
				'default_minimum_time_befor_rescheduling' => 'disabled',
				'period_available_for_booking'       => '365',
				'default_country_type'				 => 'fixed_country',
				'share_quanty_between_timeslots'     => 'false',
			);
			foreach ( $bookingpress_default_general_setting_form_options_pro as $key => $value ) {
				$BookingPressPro->bookingpress_update_general_settings_pro( $key, $value, 'general_setting' );
			}

			$bookingpress_default_notifications_name_arr = array( 'Appointment Rescheduled', 'Complete Payment URL','Refund Payment' );

			$bookingpress_default_notifications_message_arr        = array(
				'Appointment Rescheduled' => __('Dear %customer_full_name%,<br>You have successfully scheduled appointment.<br>Thank you for choosing us,<br>%company_name%','bookingpress-appointment-booking'), //phpcs:ignore
				'Complete Payment URL'    => __('Hi<br/>Please complete your payment with following URL: <br/>%complete_payment_url%<br/>Thanks,<br/>%company_name%','bookingpress-appointment-booking'), //phpcs:ignore
				'Refund Payment'    => __('Dear %customer_first_name% %customer_last_name%,<br /> Your appointment %booking_id% has been canceled successfully, and the refund is initiated for the same from our end.<br /> You should expect the refund to your original payment method within 3 to 5 working days. <br />Thanks,<br />%company_name%','bookingpress-appointment-booking'), //phpcs:ignore
			);

			foreach ( $bookingpress_default_notifications_name_arr as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
				$bookingpress_customer_notification_data = array(
					'bookingpress_notification_name'   => $bookingpress_default_notification_val,
					'bookingpress_notification_receiver_type' => 'customer',
					'bookingpress_notification_status' => 1,
					'bookingpress_notification_type'   => 'default',
					'bookingpress_notification_subject' => $bookingpress_default_notification_val,
					'bookingpress_notification_message' => $bookingpress_default_notifications_message_arr[ $bookingpress_default_notification_val ],
					'bookingpress_created_at'          => current_time( 'mysql' ),
				);

				$wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_customer_notification_data );
			}
			$bookingpress_default_notifications_arr2 = array(
				'Appointment Rescheduled' => __('Hi administrator,<br>You have one confirmed %service_name% appointment. The appointment is added to your schedule.<br>Thank you,<br>%company_name%','bookingpress-appointment-booking'), //phpcs:ignore
				'Complete Payment URL'    => __('Hi administrator,<br/>Following payment URL is shared with customer. <br/>%complete_payment_url%<br/>Thank you,<br>%company_name%','bookingpress-appointment-booking'), //phpcs:ignore
				'Refund Payment'    => __('Dear Administrator,<br /> The appointment %booking_id% has been canceled successfully, and the refund is initiated for the same from our end.<br />Thanks,<br />%company_name%','bookingpress-appointment-booking'), //phpcs:ignore
			);
			foreach ( $bookingpress_default_notifications_name_arr as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
				$bookingpress_employee_notification_data = array(
					'bookingpress_notification_name'   => $bookingpress_default_notification_val,
					'bookingpress_notification_receiver_type' => 'employee',
					'bookingpress_notification_status' => 1,
					'bookingpress_notification_type'   => 'default',
					'bookingpress_notification_subject' => $bookingpress_default_notification_val,
					'bookingpress_notification_message' => $bookingpress_default_notifications_arr2[ $bookingpress_default_notification_val ],
					'bookingpress_created_at'          => current_time( 'mysql' ),
				);

				$wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_employee_notification_data );
			}

			/* Add default scheduled Notifications */			
			$bookingpress_scheduled_notification_arr = array( 'Appointment Reminder','Appointment Follow Up');
			$bookingpress_default_customer_notifications_arr2 = array(
				'Appointment Reminder' => __('Dear %customer_full_name%,<br> We would like to remind you that you have booked %service_name% on %appointment_date% at %appointment_time%. We are waiting for you at %company_address%.</br>Thank you for choosing our company,</br>%company_name%','bookingpress-appointment-booking'), //phpcs:ignore
				'Appointment Follow Up' => __('Dear %customer_full_name%, <br> Thank you for choosing %company_name%. We hope you were satisfied with your %service_name%.<br>Thank you and we look forward to seeing you again soon,<br>%company_name%','bookingpress-appointment-booking'), //phpcs:ignore
			);
			
			$bookingpress_default_employee_notifications_arr2 = array(
				'Appointment Reminder' => '',
				'Appointment Follow Up' => '',
			);
			
			foreach ( $bookingpress_scheduled_notification_arr as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
			
				$bookingpress_notification_scheduled_type = !empty($bookingpress_default_notification_val == 'Appointment Follow Up') ? 'after' : 'before';
			
				$bookingpress_employee_notification_data = array(
					'bookingpress_notification_name'   => $bookingpress_default_notification_val,
					'bookingpress_notification_receiver_type' => 'customer',
					'bookingpress_notification_status' => 0,
					'bookingpress_notification_is_custom' => 1,
					'bookingpress_notification_type'   => 'custom',
					'bookingpress_custom_notification_type' => 'scheduled',
					'bookingpress_notification_scheduled_type' => $bookingpress_notification_scheduled_type,
					'bookingpress_notification_duration_val' => 24,
					'bookingpress_notification_attach_ics_file' => 1,
					'bookingpress_notification_duration_unit' => 'h',
					'bookingpress_notification_event_action' => 'appointment_approved',
					'bookingpress_notification_subject' => $bookingpress_default_notification_val,
					'bookingpress_notification_message' => $bookingpress_default_customer_notifications_arr2[ $bookingpress_default_notification_val ],
					'bookingpress_created_at'  => current_time( 'mysql' ),
				);
				$wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_employee_notification_data );
				$bookingpress_employee_notification_data = array(
					'bookingpress_notification_name'   => $bookingpress_default_notification_val,
					'bookingpress_notification_receiver_type' => 'employee',
					'bookingpress_notification_status' => 0,
					'bookingpress_notification_is_custom' => 1,
					'bookingpress_notification_type' => 'custom',
					'bookingpress_custom_notification_type' => 'scheduled',
					'bookingpress_notification_attach_ics_file' => 1,
					'bookingpress_notification_scheduled_type'   => $bookingpress_notification_scheduled_type,
					'bookingpress_notification_duration_val'   => 24,
					'bookingpress_notification_duration_unit'   => 'h',
					'bookingpress_notification_event_action'   => 'appointment_approved',
					'bookingpress_notification_subject' => $bookingpress_default_notification_val,
					'bookingpress_notification_message' => $bookingpress_default_employee_notifications_arr2[ $bookingpress_default_notification_val ],
					'bookingpress_created_at'          => current_time( 'mysql' ),
				);
				$wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_employee_notification_data );
			}

			$BookingPressPro->bookingpres_install_default_customize_settings();

			$BookingPressPro->bookingpress_add_user_role_and_capabilities();

			$BookingPressPro->bookingpress_install_default_general_settings_data();

			$BookingPressPro->bookingpress_update_service_advance_options();

			/* Plugin Action Hook After Install Process */
			do_action( 'bookingpress_after_pro_activation_hook' );
			do_action( 'bookingpress_after_pro_install' );
			
			add_option('bookingpress_pro_install_date',current_time('mysql'));

			$check_db_permission = $BookingPressPro->bookingpress_pro_check_db_permission();
			if( $check_db_permission && false == $BookingPressPro->is_pro_reinstall ){
				//appointment meta table index
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_appointment_meta}` ADD INDEX `bookingpress_appointment_id-appointment_meta_key` (`bookingpress_appointment_id`, `bookingpress_appointment_meta_key`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_meta is a table name.
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				}

				//staff member table index
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_staffmembers}` ADD INDEX `bookingpress_wpuser_id` (`bookingpress_wpuser_id`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name.
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				}

				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_staffmembers}` ADD INDEX `bookingpress_staffmember_status-staffmember_id` (`bookingpress_staffmember_status`, `bookingpress_staffmember_id`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name.
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				}

				//staff member meta table index
				$wpdb->query( "ALTER TABLE `{$tbl_bookingpress_staffmembers_meta}` ADD INDEX `bookingpress_staffmember_id-staffmembermeta_key` (`bookingpress_staffmember_id`, `bookingpress_staffmembermeta_key`);" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_meta is a table name.
				if( !empty( $wpdb->last_error ) && !preg_match('/^(Duplicate column name)/', $wpdb->last_error) ){
					$db_error_details[ $wpdb->last_query ] = $wpdb->last_error;
				}
			}			
		}

		function bookingpress_update_service_advance_options(){
			global $wpdb, $tbl_bookingpress_services, $bookingpress_services;

			//Get all service and install default advance option value
			$bookingpress_services_details = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_services}", ARRAY_A); //phpcs:ignore
			if(!empty($bookingpress_services_details)){
				foreach($bookingpress_services_details as $service_key => $service_val){
					$bookingpress_services->bookingpress_add_service_meta($service_val['bookingpress_service_id'], 'minimum_time_required_before_booking', 'inherit');
					$bookingpress_services->bookingpress_add_service_meta($service_val['bookingpress_service_id'], 'minimum_time_required_before_booking_time_unit', 'm');
					$bookingpress_services->bookingpress_add_service_meta($service_val['bookingpress_service_id'], 'minimum_time_required_before_rescheduling', 'inherit');
					$bookingpress_services->bookingpress_add_service_meta($service_val['bookingpress_service_id'], 'minimum_time_required_before_rescheduling_time_unit', 'm');
					$bookingpress_services->bookingpress_add_service_meta($service_val['bookingpress_service_id'], 'minimum_time_required_before_cancelling', 'inherit');
					$bookingpress_services->bookingpress_add_service_meta($service_val['bookingpress_service_id'], 'minimum_time_required_before_cancelling_time_unit', 'm');
				}
			}
		}

		function bookingpress_pro_check_db_permission()
		{
			global $wpdb;
            $results = $wpdb->get_results("SHOW GRANTS FOR CURRENT_USER;");
            $allowed_index = 0;
            foreach($results as $result)
            {
                if(is_object($result))
                {
                    foreach($result as $res)
                    {
                        $result_data = stripslashes_deep($res);
                    }
                }
                else {
                    $result_data = stripslashes_deep($result);
                }
                if( (strpos($result_data, "ALL PRIVILEGES") !== false || strpos($result_data, "INDEX") !== false) && (strpos($result_data, "ON *.*") || strpos($result_data, "`".DB_NAME."`") ) )
                {
                    $allowed_index = 1;
                    break;
                }
            }
            return $allowed_index;
		
		}
		
		function bookingpress_pro_upgrade_data()
        {	
            global $BookingPress,$bookingpress_pro_version;
            $bookingpress_pro_old_version = get_option('bookingpress_pro_version', true);			
            if (version_compare($bookingpress_pro_old_version, '3.7', '<') ) {
				$bookingpress_load_pro_upgrade_file = BOOKINGPRESS_PRO_VIEWS_DIR . '/upgrade_latest_pro_data.php';
                include $bookingpress_load_pro_upgrade_file;
                $BookingPress->bookingpress_send_anonymous_data_cron();
            }
        }

		function bookingpress_add_user_role_and_capabilities() {
			global $wp_roles;
			$role_name  = 'BookingPress Staffmember';
			$role_slug  = sanitize_title( $role_name );
			$basic_caps = array(
				$role_slug => true,
				'read'     => true,
				'level_0'  => true,
			);

			$wp_roles->add_role( $role_slug, $role_name, $basic_caps );
		}

		function bookingpres_install_default_customize_settings() {
			global $wpdb,$tbl_bookingpress_customize_settings;			
			$bookigpress_time_format_for_booking_form = 6;
			$wp_default_time_format    = get_option('time_format');            
            if ($wp_default_time_format == 'g:i a' ) {
                $bookigpress_time_format_for_booking_form = 6;
            } elseif ($wp_default_time_format == 'H:i' ) {				
				$bookigpress_time_format_for_booking_form = 5;
			}
			$booking_form = array(
				'hide_service_duration' => 'false',
				'hide_service_price'    => 'false',
				'hide_time_slot_grouping' => 'false',
				'bookigpress_time_format_for_booking_form' => $bookigpress_time_format_for_booking_form,
				'redirection_mode'  => 'external_redirection',
				'hide_staffmember_selection' => 'false',
				'hide_capacity_text' => 'false',
				'hide_capacity_text_flag' => 'false',
				'bookingpress_staffmember_information' => '2',
				'hide_staffmember_price' => 'false',
				'bookingpress_form_sequance' => '["service_selection","staff_selection"]',
				'staffmember_title'=> __('Staff', 'bookingpress-appointment-booking'),
				'staffmember_heading_title' => __('Select Staff Member', 'bookingpress-appointment-booking'),
				'service_extra_title' => __('Select Service Extras', 'bookingpress-appointment-booking'),
				'bring_anyone_title' => __('No. of Person','bookingpress-appointment-booking'),
				'cart_title' => __('Cart Items','bookingpress-appointment-booking'),
				'cart_heading_title' => __('My Cart Items','bookingpress-appointment-booking'),
				'cart_item_title' => __('Items','bookingpress-appointment-booking'),
				'cart_add_service_button_label' => __('Add Services','bookingpress-appointment-booking'),
				'cart_total_amount_title' => __('Cart Total','bookingpress-appointment-booking'),
				'deposit_paying_amount_title' => __('Deposit(Paying Now)','bookingpress-appointment-booking'),
				'deposit_heading_title' => __('Deposit Payment','bookingpress-appointment-booking'),								
				'deposit_remaining_amount_title' => __('Remaining Amount','bookingpress-appointment-booking'),
				'coupon_code_title' => __('Have a coupon code ?','bookingpress-appointment-booking'),
				'coupon_code_field_title' => __('Enter your coupon code','bookingpress-appointment-booking'),
				'coupon_apply_button_label' => __('Apply','bookingpress-appointment-booking'),
				'couon_applied_title' => __('Coupon Applied','bookingpress-appointment-booking'),
				'authorize_net_text'  => __('Credit Card', 'bookingpress-appointment-booking'),
				'mollie_text'  => __('Credit Card', 'bookingpress-appointment-booking'),
				'stripe_text'  => __('Credit Card', 'bookingpress-appointment-booking'),
				'twocheckout_text'  => __('2 Checkout', 'bookingpress-appointment-booking'),
				'razorpay_text'  => __('Razorpay', 'bookingpress-appointment-booking'),
				'paystack_text'  => __('Credit Card', 'bookingpress-appointment-booking'),
				'payumoney_text'  => __('PayUMoney', 'bookingpress-appointment-booking'),
				'payfast_text'  => __('Payfast', 'bookingpress-appointment-booking'),
				'square_text'  => __('Credit Card', 'bookingpress-appointment-booking'),
				'skrill_text'  => __('Skrill', 'bookingpress-appointment-booking'),
				'worldpay_text'  => __('Credit Card', 'bookingpress-appointment-booking'),
				'pagseguro_text'  => __('Pagseguro', 'bookingpress-appointment-booking'),
				'paypalpro_text'  => __('Credit Card', 'bookingpress-appointment-booking'),
				'woocommerce_text'  => __('WooCommerce', 'bookingpress-appointment-booking'),
				'braintree_text'  => __('Credit Card', 'bookingpress-appointment-booking'),
				'card_details_text'  => __('Card Details', 'bookingpress-appointment-booking'),
				'card_name_text'	=> __('Name on card', 'bookingpress-appointment-booking'),
				'card_number_text'  => __('Card Number', 'bookingpress-appointment-booking'),
				'expire_month_text'  => __('Expire Month', 'bookingpress-appointment-booking'),
				'expire_year_text'  => __('Expire Year', 'bookingpress-appointment-booking'),
				'cvv_text'  => __('CVV', 'bookingpress-appointment-booking'),
				'enable_google_captcha'	=> 'false',
				'slot_left_text' => __('Slots left', 'bookingpress-appointment-booking'),
				'cancel_button_title' => __('Cancel', 'bookingpress-appointment-booking'),
				'continue_button_title' => __('Continue', 'bookingpress-appointment-booking'),
				'subtotal_text'	=> __('Subtotal', 'bookingpress-appointment-booking'),
				'deposit_title'	=> __('Deposit', 'bookingpress-appointment-booking'),
				'full_payment_title' => __('Full Payment','bookingpress-appointment-booking'),
				'number_of_person_title' => __( 'Persons', 'bookingpress-appointment-booking' ),				
				'any_staff_title' => __('Any Staff','bookingpress-appointment-booking'),
				'book_appointment_day_text' => 'd',
				'service_extras_label'            => __('Extras','bookingpress-appointment-booking'),
			);
			
			$bookingpress_is_lite_downloaded_automatic = get_option('bookingpress_lite_download_automatic');
			if($bookingpress_is_lite_downloaded_automatic == 1){
				$booking_form['redirection_mode'] = 'in-built';
			}

			foreach($booking_form as $key => $value) {
				$bookingpress_customize_settings_db_fields = array(
					'bookingpress_setting_name'  => $key,
					'bookingpress_setting_value' => $value,
					'bookingpress_setting_type'  => 'booking_form',
				);
				$wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
			}	
			$booking_form = array(
				'login_form_title' => esc_html__( 'Please Login', 'bookingpress-appointment-booking' ),
				'login_form_username_field_label' => esc_html__( 'Username', 'bookingpress-appointment-booking' ),
				'login_form_password_field_label' => esc_html__( 'Password', 'bookingpress-appointment-booking' ),
				'login_form_password_required_field_label' => esc_html__( 'Please enter password', 'bookingpress-appointment-booking' ),
				'login_form_username_required_field_label' => esc_html__( 'Please enter username', 'bookingpress-appointment-booking' ),				
				'login_form_button_label' => esc_html__( 'LOGIN', 'bookingpress-appointment-booking' ),
				'forgot_password_link_label' => esc_html__( 'Lost Your Password', 'bookingpress-appointment-booking' ),
				'login_form_error_msg_label' => esc_html__( 'The Username/ Password you entered is invalid.', 'bookingpress-appointment-booking' ),
				'forgot_password_form_title' => esc_html__( 'Forgot Password', 'bookingpress-appointment-booking' ),
				'forgot_password_form_button_label' => esc_html__( 'Submit', 'bookingpress-appointment-booking' ),
				'forgot_password_form_email_label' => esc_html__( 'Username OR Email Address', 'bookingpress-appointment-booking' ),
				'forgot_password_form_email_required_field_label' => esc_html__( 'Please enter email address', 'bookingpress-appointment-booking' ),
				'forgot_password_form_error_msg_label' => esc_html__( 'There is no user registered with that email address/Username.', 'bookingpress-appointment-booking' ),
				'forgot_password_form_success_msg_label' => esc_html__( 'We have sent you a password reset link, Please check your mail.', 'bookingpress-appointment-booking' ),
				'forgot_password_email_placeholder_label' => esc_html__( 'Enter email address', 'bookingpress-appointment-booking' ),
				'forgot_password_signin_link_label' => esc_html__( 'Sign In', 'bookingpress-appointment-booking' ),
				'login_form_username_field_placeholder' => esc_html__('Enter your email address', 'bookingpress-appointment-booking'),
				'login_form_password_field_placeholder' => esc_html__('Enter your password', 'bookingpress-appointment-booking'),
				'login_form_remember_me_field_label' => esc_html__('Remember Me', 'bookingpress-appointment-booking'),
				'current_password_label' => esc_html__('Enter Current Password', 'bookingpress-appointment-booking'),
				'new_password_label' => esc_html__('Enter New Password', 'bookingpress-appointment-booking'),
				'confirm_password_label' => esc_html__('Confirm Password', 'bookingpress-appointment-booking'),
				'current_password_placeholder' => esc_html__('Enter Current Password', 'bookingpress-appointment-booking'),
				'new_password_placeholder' => esc_html__('Enter new password', 'bookingpress-appointment-booking'),
				'confirm_password_placeholder' => esc_html__('Enter confirm password', 'bookingpress-appointment-booking'),
				'update_password_btn_text' => esc_html__('Update Password', 'bookingpress-appointment-booking'),
				'edit_account_title' => esc_html__('Edit Account', 'bookingpress-appointment-booking'),
				'change_password_title' => esc_html__('Change Password', 'bookingpress-appointment-booking'),
				'logout_title' => esc_html__('Logout', 'bookingpress-appointment-booking'),
				'my_profile_title' => esc_html__('My Profile', 'bookingpress-appointment-booking'),
				'update_profile_btn' => esc_html__('Update Profile', 'bookingpress-appointment-booking'),
				'update_profile_success_msg' => esc_html__('Profile Updated Successfully', 'bookingpress-appointment-booking'),
				'update_password_success_message' => esc_html__('Password Change Successfully', 'bookingpress-appointment-booking'),
				'update_password_error_message' => esc_html__('Something went wrong while updating password', 'bookingpress-appointment-booking'),
				'old_password_error_msg' => esc_html__('Please enter old password', 'bookingpress-appointment-booking'),
				'new_password_error_msg' => esc_html__('Please enter new password', 'bookingpress-appointment-booking'),
				'confirm_password_error_msg' => esc_html__('Please enter confirm password', 'bookingpress-appointment-booking'),
				'reschedule_title' => esc_html__('Reschedule', 'bookingpress-appointment-booking'),
				'reschedule_popup_title' => esc_html__('Reschedule Appointment', 'bookingpress-appointment-booking'),
				'reschedule_popup_description' => esc_html__('To reschedule your appointment, select an available date time from the calendar', 'bookingpress-appointment-booking'),
				'reschedule_date_label' => esc_html__('Date', 'bookingpress-appointment-booking'),
				'reschedule_time_label' => esc_html__('Time', 'bookingpress-appointment-booking'),
				'reschedule_time_placeholder' => esc_html__('Select Time', 'bookingpress-appointment-booking'),
				'reschedule_cancel_btn_label' => esc_html__('Cancel', 'bookingpress-appointment-booking'),
				'reschedule_update_btn_label' => esc_html__('Update', 'bookingpress-appointment-booking'),
				'reschedule_appointment_success_msg' => esc_html__('Appointment Rescheduled Successfully', 'bookingpress-appointment-booking'),
				'delete_account_heading_title' => esc_html__('Delete Your Account', 'bookingpress-appointment-booking'),
				'delete_account_desc' => esc_html__('This will delete your all data & appointments from bookingpress', 'bookingpress-appointment-booking'),
				'delete_account_button_title' => esc_html__('Delete Account', 'bookingpress-appointment-booking'),
				'invoice_button_label'	=> esc_html__('Download Invoice', 'bookingpress-appointment-booking'),
				'staff_main_heading'	=> esc_html__('Staff', 'bookingpress-appointment-booking'),
				'booking_guest_title'	=> esc_html__('No. of Person', 'bookingpress-appointment-booking'),
				'booking_extra_title'	=> esc_html__('Extras', 'bookingpress-appointment-booking'),
				'booking_deposit_title'	=> esc_html__('Deposit', 'bookingpress-appointment-booking'),
				'booking_tax_title'	=> esc_html__('Tax', 'bookingpress-appointment-booking'),
				'booking_coupon_title'	=> esc_html__('Coupon', 'bookingpress-appointment-booking'),
				'allow_customer_edit_profile' => 'true',
				'allow_customer_reschedule_apt' => 'false',
				'paid_amount_text' => esc_html__('Paid Amount', 'bookingpress-appointment-booking'),
				'refund_amount_text' => esc_html__('Refund Amount', 'bookingpress-appointment-booking'),
				'refund_payment_gateway_text' => esc_html__('Payment Method', 'bookingpress-appointment-booking'),
				'refund_apply_text' => esc_html__('Apply', 'bookingpress-appointment-booking'),
				'refund_cancel_text' => esc_html__('Cancel', 'bookingpress-appointment-booking'),
			);			

			foreach($booking_form as $key => $value) {
				$bookingpress_customize_settings_db_fields = array(
					'bookingpress_setting_name'  => $key,
					'bookingpress_setting_value' => $value,
					'bookingpress_setting_type'  => 'booking_my_booking',
				);
				$wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
			}		
		}

		function bookingpress_install_default_general_settings_data() {
			global $BookingPress;
			$bookingpress_install_default_general_settings_data = array(
				'bookingpress_export_delimeter' => ',',
				'bpa_white_label_icon' => 'bpa_bookingpress_icon',
			);

			$bookingpress_install_default_staffmember_settings_data = array(
				'bookingpress'              => 'true',
				'bookingpress_calendar'     => 'true',
				'bookingpress_appointments' => 'true',
				'bookingpress_payments'            => 'false',
				'bookingpress_customers'           => 'false',
				'bookingpress_staff_members'       => 'false',
				'bookingpress_edit_appointments'   => 'false',
				'bookingpress_delete_appointments' => 'false',
				'bookingpress_export_appointments' => 'false',
				'bookingpress_edit_customers'      => 'false',
				'bookingpress_delete_customers'    => 'false',
				'bookingpress_export_customers'    => 'false',
				'bookingpress_edit_payments'       => 'false',
				'bookingpress_delete_payments'     => 'false',
				'bookingpress_export_payments'     => 'false',
				'bookingpress_edit_basic_details'  => 'false',
				'bookingpress_edit_daysoffs'       => 'false',
				'bookingpress_edit_workhours'      => 'false',
				'bookingpress_edit_special_days'   => 'false',
				'bookingpress_timesheet'    => 'true',
				'bookingpress_myservices'   => 'true',
				'bookingpress_myprofile'    => 'true',
				'bookingpress_manage_calendar_integration' => 'true',
				'bookingpress_staffmember_any_staff_options' => 'false',
				'bookingpress_staffmember_access_admin' => 'false',
				'bookingpress_staffmember_auto_assign_rule' => 'least_assigned_by_day',
				'bookingpress_staffmember_module_singular_name' => 'Staff Member',
				'bookingpress_staffmember_module_plural_name' => 'Staff Members',
			);		
			$bookingpress_payment_default_settings_data = array(
				'authorize_net_payment' => 'false',
				'pagseguro_payment'     => 'false',
				'stripe_payment'      	=> 'false',
				'twocheckout_payment'   => 'false',
				'mollie_payment'        => 'false',
				'razorpay_payment'      => 'false',
				'paystack_payment'      => 'false',
				'payumoney_payment'     => 'false',
				'payfast_payment'       => 'false',
				'square_payment'        => 'false',
				'skrill_payment'        => 'false',
				'worldpay_payment'      => 'false',
				'paypalpro_payment'     => 'false',
				'braintree_payment'     => 'false',
				'authorize_net_payment_mode' => 'sandbox',
				'pagseguro_payment_mode'     => 'sandbox',
				'stripe_payment_mode'      	 => 'sandbox',				
				'twocheckout_payment_mode'   => 'sandbox',
				'mollie_payment_mode'        => 'sandbox',
				'razorpay_payment_mode'      => 'sandbox',
				'paystack_payment_mode'      => 'sandbox',
				'payumoney_payment_mode'     => 'sandbox',
				'payfast_payment_mode'       => 'sandbox',
				'square_payment_mode'        => 'sandbox',
				'skrill_payment_mode'        => 'sandbox',
				'worldpay_payment_mode'      => 'sandbox',
				'paypalpro_payment_mode'     => 'sandbox',
				'braintree_payment_mode'     => 'sandbox',
				'stripe_payment_method'		 => 'sca_popup',
				'tax_percentage'			=> 0,
				'bookingpress_allow_customer_to_pay' => 'deposit_or_full_price',				
				'bookingpress_refund_on_cancellation' => 'false',
				'bookingpress_refund_mode' => 'full',
				'bookingpress_refund_on_partial' => 'false',
				'bookingpress_partial_refund_rules' => '',
			);
			$bookingpress_google_captch_settings = array(
				'google_captcha_language'   => 'en',
            	'google_captcha_failed_msg' => 'Google reCAPTCHA is Invalid or Expired. Please reload page and try again',
			);			
			$bookingpress_notification_settings = array(
				'bookingpress_selected_sms_gateway'   => 'select_sms_gateway',
				'bookingpress_selected_whatsapp_gateway' => 'select_whatsapp_gateway',
			);

			$bookingpress_zapier_settings = array(
				'bookingpress_zapier_customer_trigger_field'   => '',
				'bookingpress_zapier_appointment_trigger_field' => '',
			);
			$bookingpress_optin_settings = array(
				'mailchimp_status'   => '0',				
				'mailchimp_selected_field' => '',
			);
			$bookingpress_message_settings = array(
				'no_staffmember_selected_for_the_booking'   => __('Please select staff member','bookingpress-appointment-booking'),
				'coupon_code_not_valid' => __( 'Coupon code is not valid', 'bookingpress-appointment-booking' ),
				'coupon_code_not_allowed' => __( 'Coupon code not allowed', 'bookingpress-appointment-booking' ),
				'coupon_code_expired' => __( 'Coupon code expired', 'bookingpress-appointment-booking' ),
				'coupon_code_not_valid_for_service' => __( 'Coupon code is not valid for selected service', 'bookingpress-appointment-booking' ),
				'coupon_code_no_longer_available' => __( 'Coupon code no longer available', 'bookingpress-appointment-booking' ),
				'coupon_code_does_not_exist' => __( 'Coupon code does not exist', 'bookingpress-appointment-booking' ),				
				'bookingpress_card_details_error_msg' => __('Please fill all fields value of card details', 'bookingpress-appointment-booking'),
				'refund_policy_message'	=> __('Refund policy message','bookingpress-appointment-booking'),
			);
			

			$bookingpress_install_default_data                      = array(
				'staffmember_setting' => $bookingpress_install_default_staffmember_settings_data,
				'general_setting'     => $bookingpress_install_default_general_settings_data,
				'payment_setting'	  => $bookingpress_payment_default_settings_data,
				'google_captcha_setting' => $bookingpress_google_captch_settings,
				'notification_setting'	 => $bookingpress_notification_settings,
				'zapier_setting'         => $bookingpress_zapier_settings,
				'mailchimp_setting'		 => $bookingpress_optin_settings,
				'message_setting'		 => $bookingpress_message_settings,
			);
			foreach ( $bookingpress_install_default_data as $bookingpress_default_data_key => $bookingpress_default_data_val ) {
				$bookingpress_setting_type = $bookingpress_default_data_key;
				foreach ( $bookingpress_default_data_val as $bookingpress_default_data_val_key => $bookingpress_default_data_val2 ) {
					$BookingPress->bookingpress_update_settings( $bookingpress_default_data_val_key, $bookingpress_setting_type, $bookingpress_default_data_val2 );
				}
			}
		}

		public function bookingpress_get_selected_service_staff_members( $service_id = 0 ) {
			global $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_customers;
			$bookingpress_service_staff_members = array();
			if ( $service_id != 0 ) {
				$service_data                   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_services} WHERE bookingpress_service_id = %d", $service_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
				$bookingpress_staff_member_data = ! empty( $service_data['bookingpress_service_employees'] ) ? maybe_unserialize( $service_data['bookingpress_service_employees'] ) : array();
				if ( ! empty( $bookingpress_staff_member_data ) ) {
					foreach ( $bookingpress_staff_member_data as $staff_member_key => $staff_member_val ) {
						$bookingpress_staff_members_details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress__id = %d", $staff_member_val ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm
						if ( ! empty( $bookingpress_staff_members_details ) ) {
							$bookingpress_service_staff_members[] = $bookingpress_staff_members_details;
						}
					}
				}
			} else {
				$bookingpress_all_staff_members     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_customers} WHERE bookingpress_user_type = %d AND bookingpress_user_status = %d", 1, 1 ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_customers is a table name. false alarm
				$bookingpress_service_staff_members = ! empty( $bookingpress_all_staff_members ) ? $bookingpress_all_staff_members : array();
			}
			return $bookingpress_service_staff_members;
		}

		public function bookingpress_get_service_special_days($service_id, $selected_date){
			global $wpdb, $tbl_bookingpress_services, $BookingPress, $tbl_bookingpress_service_special_day, $tbl_bookingpress_service_special_day_breaks;
			$bookingpress_special_days = array();

			$selected_date = date('Y-m-d H:i:s', strtotime($selected_date));

			if(!empty($service_id)){
				$bookingpress_special_day_workhours = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_service_special_day} WHERE bookingpress_service_id = %d AND bookingpress_special_day_start_date <= %s AND bookingpress_special_day_end_date >= %s", $service_id, $selected_date, $selected_date), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_special_day is a table name. false alarm

				if(!empty($bookingpress_special_day_workhours)){
					$bookingpress_special_day_workhour_id = intval($bookingpress_special_day_workhours['bookingpress_service_special_day_id']);
					$bookingpress_special_days['special_day_start_time'] = $bookingpress_special_day_workhours['bookingpress_special_day_start_time'];
					$bookingpress_special_days['special_day_end_time'] = $bookingpress_special_day_workhours['bookingpress_special_day_end_time'];
					$bookingpress_special_days['special_day_breaks'] = array();

					$bookingpress_special_day_breaks_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_service_special_day_breaks} WHERE bookingpress_special_day_id = %d", $bookingpress_special_day_workhour_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_service_special_day_breaks is a table name. false alarm
					if(!empty($bookingpress_special_day_breaks_data)){
						foreach($bookingpress_special_day_breaks_data as $k => $v){
							$bookingpress_tmp_breaks = array();
							$bookingpress_tmp_breaks['break_start_time'] = date( 'H:i', strtotime( $v['bookingpress_special_day_break_start_time'] ) );
							$bookingpress_tmp_breaks['break_end_time'] = date( 'H:i', strtotime( $v['bookingpress_special_day_break_end_time'] ) );

							$bookingpress_special_days['special_day_breaks'][] = $bookingpress_tmp_breaks;
						}
					}
				}
			}

			return $bookingpress_special_days;
		}

		public function bookingpress_get_staffmember_special_days( $staffmember_id, $service_id, $selected_date ){
			global $wpdb, $tbl_bookingpress_staffmembers_special_day, $BookingPress, $tbl_bookingpress_staffmembers_special_day_breaks;

			$bookingpress_staffmember_special_days = array();
			
			if( empty( $staffmember_id ) || empty( $service_id ) ){
				return $bookingpress_staffmember_special_days;
			}

			$selected_date = date('Y-m-d H:i:s', strtotime( $selected_date ) );

			$bookingpress_staffmember_special_day_workhours = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_staffmember_special_day_id,bookingpress_special_day_service_id,bookingpress_special_day_start_time,bookingpress_special_day_end_time FROM {$tbl_bookingpress_staffmembers_special_day} WHERE bookingpress_staffmember_id = %d AND bookingpress_special_day_start_date <= %s AND bookingpress_special_day_end_date >= %s", $staffmember_id, $selected_date, $selected_date ), ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers_special_day is a table name.
				
			if( !empty( $bookingpress_staffmember_special_day_workhours ) ){
				foreach($bookingpress_staffmember_special_day_workhours as $key => $value ) {
					$bpa_service_ids = $value['bookingpress_special_day_service_id'];
					if( empty( $bpa_service_ids ) ){
						$bookingpress_staffmember_special_day_id = intval( $value['bookingpress_staffmember_special_day_id'] );
						$bookingpress_staffmember_special_days['special_day_id'] = $bookingpress_staffmember_special_day_id;
						$bookingpress_staffmember_special_days['special_day_start_time'] = $value['bookingpress_special_day_start_time'];
						$bookingpress_staffmember_special_days['special_day_end_time'] = $value['bookingpress_special_day_end_time'];
					} else {
						$bpa_service_id_arr = explode( ',', $bpa_service_ids );
						if( in_array( $service_id, $bpa_service_id_arr ) ){
							$bookingpress_staffmember_special_day_id = intval( $value['bookingpress_staffmember_special_day_id'] );
							$bookingpress_staffmember_special_days['special_day_id'] = $bookingpress_staffmember_special_day_id;
							$bookingpress_staffmember_special_days['special_day_start_time'] = $value['bookingpress_special_day_start_time'];
							$bookingpress_staffmember_special_days['special_day_end_time'] = $value['bookingpress_special_day_end_time'];
						}
					}
				}
			}

			return $bookingpress_staffmember_special_days;

		}

		function bookingpress_modify_default_holidays_with_general_special_days( $retrieve_default_holidays, $selected_service, $selected_service_duration, $selected_staffmember ){
			global $wpdb, $tbl_bookingpress_default_special_day;

			$retrieve_default_special_days = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_special_day_start_date, bookingpress_special_day_end_date FROM {$tbl_bookingpress_default_special_day} WHERE TIMESTAMPDIFF( MINUTE, bookingpress_special_day_start_time, bookingpress_special_day_end_time ) >= %d", $selected_service_duration ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_special_day is table name defined globally. False Positive alarm  

			if( !empty( $retrieve_default_special_days ) ){
				$total_sp_day = count( $retrieve_default_special_days );
				$spday_counter = 0;

				while( 0 < $total_sp_day ){

					$sp_start_date = date('Y-m-d', strtotime( $retrieve_default_special_days[ $spday_counter ]['bookingpress_special_day_start_date'] ) );
					$sp_end_date = date('Y-m-d', strtotime( $retrieve_default_special_days[ $spday_counter ]['bookingpress_special_day_end_date'] ) );


					if( $sp_start_date == $sp_end_date && !in_array( $retrieve_default_special_days[ $spday_counter ]['bookingpress_special_day_start_date'], $retrieve_default_holidays['exclude_offdays'] ) ){
						$retrieve_default_holidays['exclude_offdays'][] = date('Y-m-d H:i:s', strtotime( $sp_start_date ) );
					} else {
						$bpa_begin_date = new DateTime( $sp_start_date );
						$bpa_end_date = new DateTime( date( 'Y-m-d', strtotime( $sp_end_date . '+1 day' ) ) );
						
						$bpa_interval = DateInterval::createFromDateString('1 day');
						$period = new DatePeriod($bpa_begin_date, $bpa_interval, $bpa_end_date);

						foreach( $period as $dt ){
							$dt_formatted = $dt->format( 'Y-m-d H:i:s');
							if( !in_array( $dt_formatted, $retrieve_default_holidays['exclude_offdays'] ) ){
								$retrieve_default_holidays['exclude_offdays'][] = $dt_formatted;
							}
						}
					}

					$total_sp_day--;
					$spday_counter++;
				}
			}

			return $retrieve_default_holidays;
		}

		function bookingpress_calculate_default_spday_workdata( $service_timing_arr, $default_break_data ){
			$slot_start_time = $service_timing_arr['store_start_time'];
			$slot_end_time = $service_timing_arr['store_end_time'];

            foreach( $default_break_data as $blocked_time ){
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

		public function bookingpress_get_default_special_days( $service_timings_data, $selected_service_id, $selected_date, $minimum_time_required, $service_max_capacity, $bookingpress_show_time_as_per_service_duration ){
			
			if( !empty( $service_timings_data['service_timings'] ) || true == $service_timings_data['is_daysoff'] || empty( $selected_service_id ) ){
				return $service_timings_data;
			}

			global $wpdb, $BookingPress, $BookingPressPro, $tbl_bookingpress_default_special_day, $tbl_bookingpress_default_special_day_breaks, $tbl_bookingpress_services;

			$bookingpress_default_special_days = array();

			$current_day  = ! empty( $selected_date ) ? ucfirst( date( 'l', strtotime( $selected_date ) ) ) : ucfirst( date( 'l', current_time( 'timestamp' ) ) );
			$current_date = ! empty($selected_date) ? date('Y-m-d', strtotime($selected_date)) : date('Y-m-d', current_time('timestamp'));
			
			$bookingpress_timezone = isset($_POST['client_timezone_offset']) ? sanitize_text_field( $_POST['client_timezone_offset'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
			
			$bookingpress_timeslot_display_in_client_timezone = $BookingPress->bookingpress_get_settings( 'show_bookingslots_in_client_timezone', 'general_setting' );
			$display_slots_in_client_timezone = false;
			
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

			$bookingpress_current_time = date( 'H:i',current_time('timestamp'));
			$bpa_current_datetime = date( 'Y-m-d H:i:s',current_time('timestamp'));

			$bpa_current_date = date('Y-m-d', current_time('timestamp'));

			if( strtotime( $bpa_current_date ) > strtotime( $selected_date ) && false == $display_slots_in_client_timezone ){
                return $service_timings_data;
            }

			$bookingpress_hide_already_booked_slot = $BookingPress->bookingpress_get_customize_settings( 'hide_already_booked_slot', 'booking_form' );
			$bookingpress_hide_already_booked_slot = ( $bookingpress_hide_already_booked_slot == 'true' ) ? 1 : 0;

			$bpa_current_time = date( 'H:i',current_time('timestamp'));

			$change_store_date = ( !empty( $_POST['bpa_change_store_date'] ) && 'true' == $_POST['bpa_change_store_date'] ) ? true : false; //phpcs:ignore
			
			$bookingpress_current_time_timestamp = current_time('timestamp');

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
				$default_timeslot_step		= $service_step_duration_val;
			}			

			$workhour_data = array();

			$selected_date_formatted = date('Y-m-d H:i:s', strtotime( $selected_date ) );

			$bookingpress_default_special_day_workhours = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_special_day_id, bookingpress_special_day_start_time, bookingpress_special_day_end_time FROM {$tbl_bookingpress_default_special_day} WHERE bookingpress_special_day_start_date <= %s AND bookingpress_special_day_end_date >= %s", $selected_date_formatted, $selected_date_formatted ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day is a table name.
			
			if( !empty( $bookingpress_default_special_day_workhours ) ){
				$service_current_time = $service_start_time = apply_filters( 'bookingpress_modify_service_start_time', date('H:i', strtotime($bookingpress_default_special_day_workhours['bookingpress_special_day_start_time'])), $selected_service_id );
				
				$service_end_time     = apply_filters( 'bookingpress_modify_service_end_time', date('H:i', strtotime($bookingpress_default_special_day_workhours['bookingpress_special_day_end_time'])), $selected_service_id );

				if( '00:00' == $service_end_time ){
					$service_end_time = '24:00';
				}

				$special_days_id = $bookingpress_default_special_day_workhours['bookingpress_special_day_id'];

				$bpa_default_sp_day_breakhours = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_special_day_break_start_time,bookingpress_special_day_break_end_time FROM {$tbl_bookingpress_default_special_day_breaks} WHERE bookingpress_special_day_id = %d", $special_days_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_special_day_breaks is a table name.

				$default_sp_day_breakhours_data = array();
				if( !empty( $bpa_default_sp_day_breakhours ) ){
					foreach( $bpa_default_sp_day_breakhours as $default_sp_breakdata ){
						$default_sp_day_breakhours_data[] = array(
							'start_time' => date('H:i', strtotime( $default_sp_breakdata->bookingpress_special_day_break_start_time ) ),
							'end_time' => date( 'H:i', strtotime( $default_sp_breakdata->bookingpress_special_day_break_end_time ) ),
						);
					}
				}

				if ($service_start_time != null && $service_end_time != null ) {
					while ( $service_current_time <= $service_end_time ) {
						

						if ($service_current_time > $service_end_time ) {
							break;
						}

						$service_tmp_date_time = $selected_date .' '.$service_current_time;
						$service_tmp_end_time = date( 'Y-m-d', ( strtotime($selected_date. ' ' . $service_current_time ) + ( $service_step_duration_val * 60 ) ) );

						if( $service_tmp_end_time > $selected_date  ){
							if( 1440 < $service_step_duration_val && $service_time_duration_unit != 'd' ){
								break;
							}
						}

						$service_tmp_current_time = $service_current_time;

						if ($service_current_time == '00:00' ) {
							$service_current_time = date('H:i', strtotime($service_current_time) + ( $service_step_duration_val * 60 ));
						} else {
							$service_tmp_time_obj = new DateTime($selected_date . ' ' . $service_current_time);
							$service_tmp_time_obj->add(new DateInterval('PT' . $service_step_duration_val . 'M'));
							$service_current_time = $service_tmp_time_obj->format('H:i');

							$service_current_date = $service_tmp_time_obj->format('Y-m-d');
                            if( $service_current_date > $selected_date ){
								if( $service_end_time == '24:00' && strtotime($service_current_date.' '.$service_current_time) > strtotime( $service_current_date . ' 00:00' ) ){
                                    break;
                                }
							}
						}

						$break_start_time = '';
						$break_end_time = '';
						/** General Special Days Break Hours Start */


						/** General Special Days Break Hours End */

						if ($service_current_time < $service_start_time || $service_current_time == $service_start_time ) {
							$service_current_time = $service_end_time;
						}
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

						$bookingpress_timediff_in_minutes = round(abs(strtotime($service_current_time) - strtotime($service_tmp_current_time)) / 60, 2);

						if ($is_already_booked == 1 && $bookingpress_hide_already_booked_slot == 1 ) {
							continue;
						} else {

							if ($break_start_time != $service_tmp_current_time && $bookingpress_timediff_in_minutes >= $service_step_duration_val && $service_current_time <= $service_end_time ) {
								
								if ($bpa_current_date == $selected_date ) {
									if ($service_tmp_current_time > $bpa_current_time && !$is_booked_for_minimum) {
										$service_timing_arr = array(
											'start_time' => $service_tmp_current_time,
											'end_time'   => $service_current_time,
											'break_start_time' => $break_start_time,
											'break_end_time' => $break_end_time,
											'store_start_time' => $service_tmp_current_time,
											'store_end_time' => $service_current_time,
											'store_service_date' => $selected_date,
											'is_booked'  => 0,
											'max_capacity' => $service_max_capacity,
											'total_booked' => 0
										);
										if( !empty( $default_sp_day_breakhours_data ) ){
											$service_timing_arr = apply_filters( 'bpa_calculate_default_sp_day_workdata', $service_timing_arr, $default_sp_day_breakhours_data );
										}
										
										if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
											$service_current_time = $service_timing_arr['break_end_time'];
											continue;
										}
										if( $display_slots_in_client_timezone ){

											$booking_timeslot_start = $selected_date.' '.$service_tmp_current_time.':00';
											$booking_timeslot_end = $selected_date .' '.$service_current_time.':00';
											
											
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
											'end_time'   => $service_current_time,
											'break_start_time' => $break_start_time,
											'break_end_time' => $break_end_time,
											'store_start_time' => $service_tmp_current_time,
											'store_end_time' => $service_current_time,
											'store_service_date' => $selected_date,
											'is_booked'  => 0,
											'max_capacity' => $service_max_capacity,
											'total_booked' => 0
										);
										if( !empty( $default_sp_day_breakhours_data ) ){
											$service_timing_arr = apply_filters( 'bpa_calculate_default_sp_day_workdata', $service_timing_arr, $default_sp_day_breakhours_data );
										}
										
										if( !empty( $service_timing_arr['is_blocked'] ) && true == $service_timing_arr['is_blocked'] ){
											$service_current_time = $service_timing_arr['break_end_time'];
											continue;
										}
										if( $display_slots_in_client_timezone ){
	
											$booking_timeslot_start = $selected_date.' '.$service_tmp_current_time . ':00';
											$booking_timeslot_end = $selected_date .' '.$service_current_time .':00';
											
											
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

						if(!empty($default_timeslot_step) && $default_timeslot_step != $service_step_duration_val && empty($break_start_time)){

							$service_tmp_time_obj = new DateTime($selected_date . ' ' . $service_tmp_current_time);
							$service_tmp_time_obj->add(new DateInterval('PT' . $default_timeslot_step . 'M'));
							$service_current_time = $service_tmp_time_obj->format('H:i');
							
							$service_current_date = $service_tmp_time_obj->format('Y-m-d');
							if( $service_current_date > $selected_date ){
								break;
							}
						}
					}
					$service_timings_data['service_timings'] = $workhour_data;	
					if( empty( $workhour_data ) ){
						$service_timings_data['is_daysoff'] = true;
					}									
					return $service_timings_data;
				}
			}

			return $service_timings_data;
		}

		public static function uninstall() {
			global $BookingPressPro,$wp,$BookingPress,$wpdb, $tbl_bookingpress_extra_services, $tbl_bookingpress_staff_member_workhours,$tbl_bookingpress_staffmembers_meta,$tbl_bookingpress_staffmembers_meta,$bl_bookingpress_staffmembers_special_days_extra_details, $tbl_bookingpress_subscription_details, $tbl_bookingpress_staffmembers_special_day, $tbl_bookingpress_staffmembers_daysoff,$tbl_bookingpress_coupons,$tbl_bookingpress_cron_email_notifications_logs,$tbl_bookingpress_staffmembers,$tbl_bookingpress_staffmembers_services, $tbl_bookingpress_debug_integration_logs,$tbl_bookingpress_service_workhours,$tbl_bookingpress_service_special_day,$tbl_bookingpress_default_special_day,$tbl_bookingpress_staffmembers_special_day_breaks, $tbl_bookingpress_form_fields,$tbl_bookingpress_service_special_day_breaks,$tbl_bookingpress_default_special_day_breaks,$tbl_bookingpress_appointment_meta, $tbl_bookingpress_reschedule_history, $tbl_bookingpress_guests_data;

			$wpdb->query( 'DELETE FROM `' . $wpdb->options . "` WHERE  `option_name` LIKE  '%bookingpress_pro\_%'" );
			$bookingpress_tables = array(
				$tbl_bookingpress_extra_services,
				$tbl_bookingpress_staff_member_workhours,
				$tbl_bookingpress_staffmembers_daysoff,
				$tbl_bookingpress_staffmembers,
				$tbl_bookingpress_staffmembers_meta,
				$tbl_bookingpress_staffmembers_special_day_breaks,
				$tbl_bookingpress_subscription_details,
				$tbl_bookingpress_staffmembers_special_day,
				$tbl_bookingpress_coupons,
				$tbl_bookingpress_cron_email_notifications_logs,
				$tbl_bookingpress_staffmembers_services,
				$tbl_bookingpress_debug_integration_logs,
				$tbl_bookingpress_default_special_day,
				$tbl_bookingpress_service_workhours,
				$tbl_bookingpress_service_special_day,
				$tbl_bookingpress_service_special_day_breaks,
				$tbl_bookingpress_default_special_day_breaks,
				$tbl_bookingpress_appointment_meta,
				$tbl_bookingpress_reschedule_history,
				$tbl_bookingpress_guests_data
			);

			foreach ( $bookingpress_tables as $table ) {
				$wpdb->query( "DROP TABLE IF EXISTS $table" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			delete_option('bkp_license_key');
			delete_option('bkp_license_package');
			delete_option('bkp_license_status');
			delete_option('bkp_license_data_activate_response');
			delete_option('bkp_license_status');

			
			$bpa_field_table_exists_table = $wpdb->get_var( $wpdb->prepare( "SELECT EXISTS ( SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE %s ) AS table_nm", $tbl_bookingpress_form_fields ) );
			if( 0 < $bpa_field_table_exists_table ){
				$get_column = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$tbl_bookingpress_form_fields} LIKE %s", 'bookingpress_field_type' ) ); // phpcs:ignore
				if( 'bookingpress_field_type' == $get_column ){
					$wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} DROP COLUMN bookingpress_field_type" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
				}
			
				$get_column = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$tbl_bookingpress_form_fields} LIKE %s", 'bookingpress_field_options' ) ); // phpcs:ignore
				if( 'bookingpress_field_options' == $get_column ){
					$wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} DROP COLUMN bookingpress_field_options" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
				}
				
				$get_column = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$tbl_bookingpress_form_fields} LIKE %s", 'bookingpress_field_values' ) ); // phpcs:ignore
				if( 'bookingpress_field_values' == $get_column ){
					$wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} DROP COLUMN bookingpress_field_values" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
				}
				
				$get_column = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$tbl_bookingpress_form_fields} LIKE %s", 'bookingpress_field_meta_key' ) ); // phpcs:ignore
				if( 'bookingpress_field_meta_key' == $get_column ){
					$wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} DROP COLUMN bookingpress_field_meta_key" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
				}
				
				$get_column = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$tbl_bookingpress_form_fields} LIKE %s", 'bookingpress_field_css_class' ) ); // phpcs:ignore
				if( 'bookingpress_field_css_class' == $get_column ){
					$wpdb->query( "ALTER TABLE {$tbl_bookingpress_form_fields} DROP COLUMN bookingpress_field_css_class" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm
				}
			}

			// remove  admin capability

			$args = array(
				'role'   => 'administrator',
				'fields' => 'id',
			);

			$users = get_users( $args );

			if ( count( $users ) > 0 ) {
				foreach ( $users as $key => $user_id ) {
					$bookingpressroles = $BookingPressPro->bookingpress_pro_capabilities();
					$userObj           = new WP_User( $user_id );

					foreach ( $bookingpressroles as $bookingpressrole => $value ) {
						$userObj->remove_cap( $bookingpressrole, true );
					}
				}
			}

			// remove  staffmember capability

			$args = array(
				'role'   => 'bookingpress-staffmember',
				'fields' => 'id',
			);

			$users = get_users( $args );

			if ( count( $users ) > 0 ) {
				foreach ( $users as $key => $user_id ) {
					$bookingpress_cap_pro  = $BookingPressPro->bookingpress_pro_capabilities();
					if( !empty( $BookingPress ) && method_exists( $BookingPress, 'bookingpress_capabilities' ) ){
						$bookingpress_cap_lite = $BookingPress->bookingpress_capabilities();	
						$bookingpress_cap_pro = array_merge_recursive( $bookingpress_cap_pro, $bookingpress_cap_lite );
					}

					$userObj = new WP_User( $user_id );
					$userObj->remove_role( 'bookingpress-staffmember' );
					foreach ( $bookingpress_cap_pro as $bookingpress_capability => $value ) {
						$userObj->remove_cap( $bookingpress_capability, true );
					}
				}
			}

			// remove role
			$wp_roles = new WP_Roles();
			$wp_roles->remove_role( 'bookingpress-staffmember' );
		}

		public function bookingpress_check_capability( $capability ) {
			global $bookingpress_pro_staff_members;
			$return = false;
			if ( ! empty( $capability ) ) {
				$user_id    = get_current_user_id();
				$user_info  = get_userdata( $user_id );
				$user_roles = $user_info->roles;
				if ( in_array( 'bookingpress-staffmember', $user_roles ) && ! in_array( 'administrator', $user_roles ) ) {
					if ( current_user_can( $capability ) && $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ) {
						$return = true;
					}
				} else {
					if ( current_user_can( $capability ) ) {
						$return = true;
					}
				}
			}
			return $return;
		}

		public function bookingpress_check_user_role( $role = 'bookingpress-staffmember', $bookingpress_user_id = 0 ) {
			$user_id = 0;
			if ( ! empty( $bookingpress_user_id ) ) {
				$user_id = $bookingpress_user_id;
			} elseif ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			}
			if ( ! empty( $user_id ) ) {
				$user_info  = get_userdata( $user_id );
				$user_roles = $user_info->roles;
				if( $role == 'administrator' && in_array( $role, $user_roles ) ){
						return true;
				}
				if ( in_array( $role, $user_roles ) && !in_array( 'administrator', $user_roles ) ) {
					return true;
				}
			}
			return false;
		}

		public function bookingpress_write_integration_logs( $bookingpress_integration_type, $bookingpress_integration_type_title = '', $bookingpress_integration_event = '', $bookingpress_integration_event_from = '', $bookingpress_integration_raw_data = '', $bookingpress_ref_id = 0 ) {
			global $wpdb, $BookingPress, $bookingpress_debug_integration_log_id, $tbl_bookingpress_debug_integration_logs;

			$bookingpress_active_gateway = false;

			if(is_array($bookingpress_integration_raw_data)){
                $bookingpress_integration_raw_data['backtrace_summary'] = wp_debug_backtrace_summary( null, 0, false );
            }else{
                $bookingpress_integration_raw_data .= " | Backtrace Summary ==> ".wp_json_encode(wp_debug_backtrace_summary( null, 0, false ));
            }

			$bookingpress_active_gateway = $BookingPress->bookingpress_get_settings( $bookingpress_integration_type, 'debug_log_setting' );

			$inserted_id = 0;
			if ( $bookingpress_active_gateway == 'true' ) {
				if ( $bookingpress_ref_id == null ) {
					$bookingpress_ref_id = 0;
				}

				$bookingpress_database_log_data = array(
					'bookingpress_integration_log_ref_id' => sanitize_text_field( $bookingpress_ref_id ),
					'bookingpress_integration_type'       => sanitize_text_field( $bookingpress_integration_type ),
					'bookingpress_integration_type_title' => sanitize_text_field( $bookingpress_integration_type_title ),
					'bookingpress_integration_event'      => sanitize_text_field( $bookingpress_integration_event ),
					'bookingpress_integration_event_from' => sanitize_text_field( $bookingpress_integration_event_from ),
					'bookingpress_integration_raw_data'   => wp_json_encode( stripslashes_deep( $bookingpress_integration_raw_data ) ),
					'bookingpress_integration_log_added_date' => current_time( 'mysql' ),
				);

				$wpdb->insert( $tbl_bookingpress_debug_integration_logs, $bookingpress_database_log_data );
				$inserted_id = $wpdb->insert_id;
				if ( empty( $bookingpress_ref_id ) ) {
					$bookingpress_ref_id = $inserted_id;
				}
			}
			$bookingpress_debug_integration_log_id = $bookingpress_ref_id;
			return $inserted_id;
		}

		function bookingpress_get_total_workinghour_by_date( $start_date, $end_date ) {
			global $BookingPress,$wpdb,$tbl_bookingpress_default_workhours,$tbl_bookingpress_default_special_day;
			$bookingpress_total_workhour = 0;
			if ( ! empty( $start_date ) && ! empty( $end_date ) ) {
				$default_daysoff_details         = $BookingPress->bookingpress_get_default_dayoff_dates();
				$bookingpress_workhours_data_arr = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_is_break = %d", 0 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is a table name. false alarm

				$bookingpress_workhours_break_data_arr = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_default_workhours} WHERE bookingpress_is_break = %d", 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_default_workhours is a table name. false alarm

				$bookingpress_workhours_data = $bookingpress_workhours_break_data = array();

				if ( ! empty( $bookingpress_workhours_break_data_arr ) ) {
					foreach ( $bookingpress_workhours_break_data_arr as $bookingpress_workhours_break_data_key => $bookingpress_workhours_break_data_val ) {
						$break_total_time         = 0;
						$bookingpress_start_time  = sanitize_text_field( $bookingpress_workhours_break_data_val['bookingpress_start_time'] );
						$bookingpress_end_time    = sanitize_text_field( $bookingpress_workhours_break_data_val['bookingpress_end_time'] );
						$bookingpress_workday_key = sanitize_text_field( $bookingpress_workhours_break_data_val['bookingpress_workday_key'] );
						$break_total_time         = $workingHours = ( strtotime( $bookingpress_end_time ) - strtotime( $bookingpress_start_time ) ) / 3600 * 60;
						$bookingpress_workhours_break_data[ $bookingpress_workday_key ] = $break_total_time;
					}
				}
				if ( ! empty( $bookingpress_workhours_data_arr ) ) {
					foreach ( $bookingpress_workhours_data_arr as $bookingpress_workhours_data_key => $bookingpress_workhours_data_val ) {
						$bookingpress_workday_key = sanitize_text_field( $bookingpress_workhours_data_val['bookingpress_workday_key'] );
						$total_workhour           = 0;
						$break_time               = ! empty( $bookingpress_workhours_break_data[ $bookingpress_workday_key ] ) ? $bookingpress_workhours_break_data[ $bookingpress_workday_key ] : 0;
						$bookingpress_start_time  = sanitize_text_field( $bookingpress_workhours_data_val['bookingpress_start_time'] );
						$bookingpress_end_time    = sanitize_text_field( $bookingpress_workhours_data_val['bookingpress_end_time'] );
						$time                     = ( strtotime( $bookingpress_end_time ) - strtotime( $bookingpress_start_time ) ) / 3600 * 60;
						$total_workhour           = $time - $break_time;
						$bookingpress_workhours_data[ $bookingpress_workday_key ] = $total_workhour;

					}
				}
				if ( ! empty( $default_daysoff_details ) ) {
					$default_daysoff_details = array_map(
						function( $date ) {
							return date( 'Y-m-d', strtotime( $date ) );
						},
						$default_daysoff_details
					);
				}
				$end_date = date( 'Y-m-d', strtotime( '+1 days' . $end_date ) );
				do {
					if ( ! in_array( $start_date, $default_daysoff_details ) ) {
						$day_name                     = strtolower( date( 'l', strtotime( $start_date ) ) );
						$bookingpress_total_workhour += $bookingpress_workhours_data[ $day_name ];
					}
					$start_date = date( 'Y-m-d', strtotime( '+1 days' . $start_date ) );
				} while ( $start_date != $end_date );

			}
			return $bookingpress_total_workhour;
		}


		function boookingpress_get_visitor_ip() {
			if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
				$_SERVER['REMOTE_ADDR']    = ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? sanitize_text_field( $_SERVER['HTTP_CF_CONNECTING_IP'] ) : '';
				$_SERVER['HTTP_CLIENT_IP'] = ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? sanitize_text_field( $_SERVER['HTTP_CF_CONNECTING_IP'] ) : '';
			}

			$client  = ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ? sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] ) : '';
			$forward = ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] ) : '';
			$remote  = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';

			if ( filter_var( $client, FILTER_VALIDATE_IP ) ) {
				$ip = $client;
			} elseif ( filter_var( $forward, FILTER_VALIDATE_IP ) ) {
				$ip = $forward;
			} else {
				$ip = $remote;
			}

			return $ip;
		}

		function bookingpress_change_time_slot_format_func( $bookingpress_default_time_format ) {
			global $BookingPress;

			$bookigpress_time_format_for_booking_form =  $BookingPress->bookingpress_get_customize_settings('bookigpress_time_format_for_booking_form','booking_form');
			$default_time_format =  $BookingPress->bookingpress_get_settings('default_time_format','general_setting');            			
			
			if($default_time_format == 'bookingpress-wp-inherit-time-format') {
				$bookingpress_default_time_format = get_option('time_format');
			}
			else{
				if($bookigpress_time_format_for_booking_form == '1' || $bookigpress_time_format_for_booking_form == '3' || $bookigpress_time_format_for_booking_form == '5') {
					if($bookingpress_default_time_format == 'g:i a') {
						$bookingpress_default_time_format = 'g:i';
					}
				}
				elseif($bookigpress_time_format_for_booking_form == 'bookingpress-wp-inherit-time-format') {
					$bookingpress_default_time_format = get_option('time_format');
				} 
				else {
					if($bookigpress_time_format_for_booking_form == '2' || $bookigpress_time_format_for_booking_form == '4' || $bookigpress_time_format_for_booking_form == '6')  {
						if($bookingpress_default_time_format == 'h:i a') {
							$bookingpress_default_time_format = 'h:i a';
						} else {
							$bookingpress_default_time_format = 'g:i a';
						}
					}	
				}
			}
			return $bookingpress_default_time_format;
		}
		function bookingpress_admin_view_filter_func(){			
			global $bookingpress_global_options;
			$bookingpress_global_details  = $bookingpress_global_options->bookingpress_global_options();
			$bpa_time_format_for_timeslot = $bookingpress_global_details['bpa_time_format_for_timeslot'];

            $bookingpress_default_date_time_format = $bookingpress_global_details['wp_default_date_format'];
            
            $bookingpress_default_date_format = 'MMMM D, YYYY';
            if ($bookingpress_default_date_time_format == 'F j, Y' ) {
                $bookingpress_default_date_format = 'MMMM D, YYYY';
            } elseif ($bookingpress_default_date_time_format == 'Y-m-d' ) {
                $bookingpress_default_date_format = 'YYYY-MM-DD';
            } elseif ($bookingpress_default_date_time_format == 'm/d/Y' ) {
                $bookingpress_default_date_format = 'MM/DD/YYYY';
            } elseif($bookingpress_default_date_time_format == 'd/m/Y') {
                $bookingpress_default_date_format = 'DD/MM/YYYY';
            } elseif ($bookingpress_default_date_time_format == 'd.m.Y') {
                $bookingpress_default_date_format = 'DD.MM.YYYY';
            } elseif ($bookingpress_default_date_time_format == 'd-m-Y') {
                $bookingpress_default_date_format = 'DD-MM-YYYY';
            }

			?>
			bookingpress_customize_format_time: function(value, selected_format){
				var default_time_format = '<?php echo esc_html($bpa_time_format_for_timeslot); ?>';												
				if(selected_format == '1' || selected_format == '3' || selected_format == '5') {
					if(default_time_format == 'hh:mm a') {
						default_time_format = 'hh:mm'; 
					} else if(default_time_format == 'HH:mm' ) {
						default_time_format = 'HH:mm';
					} 
				}
				else {
					if(default_time_format == 'hh:mm a') {
						default_time_format = 'hh:mm a';
					} else if(default_time_format == 'HH:mm') {
						default_time_format = 'HH:mm';
					}
				}
				return moment(String(value), "HH:mm:ss").format(default_time_format)
			},
			bookingpress_format_date: function(value){
				var default_date_format = '<?php echo esc_html($bookingpress_default_date_format); ?>';
				<?php $bookingpress_site_current_lang_moment_locale = get_locale(); ?>
				return moment(String(value)).locale('<?php echo esc_html($bookingpress_site_current_lang_moment_locale); ?>').format(default_date_format);
			}			
			<?php
		}

		
		function bookingpress_generate_my_booking_customize_css_func($bookingpress_customize_css_content,$bookingpress_custom_data_arr) {

			$shortcode_background_color = $bookingpress_custom_data_arr['my_booking_form']['background_color'];
			$raw_background_color       = $bookingpress_custom_data_arr['my_booking_form']['row_background_color'];
			$primary_color              = $bookingpress_custom_data_arr['my_booking_form']['primary_color'];
			$border_color               = $bookingpress_custom_data_arr['my_booking_form']['border_color'];			
			$label_title_color          = $bookingpress_custom_data_arr['my_booking_form']['label_title_color'];
			$content_color              = $bookingpress_custom_data_arr['my_booking_form']['content_color'];
			$sub_title_color            = $bookingpress_custom_data_arr['my_booking_form']['sub_title_color'];
			$title_font_size            = '18px';
			$title_font_family          = $bookingpress_custom_data_arr['my_booking_form']['title_font_family'];
			$title_font_family          =  $title_font_family == 'Inherit Fonts' ? 'inherit' : $title_font_family;
			$price_button_color         = $bookingpress_custom_data_arr['my_booking_form']['price_button_text_color'];  
			$content_font_size          = '14px';
			$sub_title_font_size        = '16px';
			$hex                        = $primary_color;
			list($r, $g, $b)            = sscanf($hex, '#%02x%02x%02x');					
			$box_shadow_color           = "0 4px 8px rgba($r,$g,$b,0.06), 0 8px 16px rgba($r,$g,$b,0.16)";

$bookingpress_customize_css_content.='
.bpa-front-customer-panel-login-container,
.bpa-front-cp-reschedule-dialog,
.bpa-front-cp-card .bpa-front-cp-left-sidebar,
.bpa-front-cp-reschedule-date-picker,
.bpa-custom-dropdown.el-select-dropdown,
.bpa-front-cp-reschedule-mob-drawer,
.bpa-custom-datepicker,
.bpa-custom-datepicker .el-picker-panel__footer,
.bpa-front-form-control--radio .el-radio__inner::after,
.bpa-custom-datepicker .el-time-panel,
.bpa-dialog--refund-appointments,
.bpa-front-cp-refund-mob-drawer,
.bpa-front-cp-reschedule-time-dropdown{
	background-color: '.$shortcode_background_color.' !important;
}
.bpa-custom-dropdown .el-select-dropdown__item.hover,
.bpa-custom-dropdown .el-select-dropdown__item:hover,
.bpa-front-cp-reschedule-time-dropdown .el-select-dropdown__item.hover, 
.bpa-front-cp-reschedule-time-dropdown .el-select-dropdown__item:hover{
	background-color: '.$raw_background_color.' !important;
}
.bpa-cp-ls__tab-menu .bpa-tm__item .bpa-tm__item-icon svg,
.bpa-front__ar-icons svg.bpa-front-appointment-cart-icon,
.bpa-front__ar-icons .bpa-front-ari__deposit-icon svg,
.bpa-cp-ma-table.el-table td.el-table__cell .bpa-ma-date-time-details .bpa-ma-dt__time-val svg,
.bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card .bpa-front-taw__reschedule-icon span svg path.bpa-front-res-icon__path,
.bpa-cp-ls__personal-details .bpa-cp-avatar__default-img svg  {
	fill:'.$content_color.' !important;
}
.bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card .bpa-front-taw__reschedule-icon span svg {
	fill: unset !important;
}
.bpa-cp-ls__tab-menu .bpa-tm__item.__bpa-is-active .bpa-tm__item-icon svg{
	fill: var(--bpa-cl-white) !important;
}
.bpa-front-ma-table-actions-wrap .bpa-front-ma-taw__card .bpa-front-taw__reschedule-icon:hover span svg path.bpa-front-res-icon__path{
	fill: var(--bpa-cl-white) !important;
}
.bpa-front-form-control.el-input .el-icon-view:before,
.bpa-front-form-control--date-picker .el-input__prefix .el-input__icon::before{
	background-color:'.$content_color.' !important;
}
.bpa-front-cp-card .bpa-front-cp-left-sidebar,
.bpa-front-cp-delete-account-belt,
.bpa-front-dialog-footer,
.bpa-front-customer-panel-login-container,
.bpa-cp-ls__tab-menu .bpa-tm__item .bpa-tm__item-icon,
.bpa-custom-datepicker,
.bpa-custom-dropdown,
.bpa-front-form-control--radio .el-radio__inner,
.bpa-front-form-control .el-textarea__inner,
.bpa-front-form-control--checkbox .el-checkbox__inner:hover,
.bpa-custom-checkbox--is-label .el-checkbox__inner,
.bpa-front-form-control--checkbox .el-checkbox__inner,
.bpa-front-form-control--radio .el-radio__inner,
.bpa-front-cp-delete-account-belt .bpa-front-dab__right .bpa-front-btn,
.bpa-custom-datepicker .el-time-panel,
.bpa-custom-datepicker .el-time-panel__content::after, 
.bpa-custom-datepicker .el-time-panel__content::before,
.bpa-custom-datepicker .el-time-panel__footer,
.bpa-cp-ls__personal-details .bpa-cp-avatar__default-img,
.bpa-dialog--refund-appointments,
.bpa-front-cp-reschedule-time-dropdown {
    border-color: ' . $border_color . ' !important;
}
.bpa-front-dab__right .bpa-front-btn:hover,
.bpa-front-btn--danger, .el-button--bpa-front-btn.bpa-front-btn--danger{
	border-color: var(--bpa-sc-danger) !important;
}
.bpa-custom-dropdown .el-select-group__wrap:not(:last-of-type)::after,
.bpa-front-cp-reschedule-time-dropdown .el-select-group__wrap:not(:last-of-type)::after{
	background-color: ' . $border_color . ' !important;
}
.bpa-custom-datepicker .el-picker-panel__footer{
	border-top-color:'.$border_color.' !important;
}
.bpa-custom-datepicker .el-date-picker__time-header,
.bpa-dialog--refund-appointments .bpa-front-rcr__item .bpa-front-rcr__item-val,
.bpa-front-cp-refund-mob-drawer .bpa-front-rcr__item .bpa-front-rcr__item-val{
	border-bottom-color:'.$border_color.' !important;
}
.bpa-cp-ls__tab-menu .bpa-tm__item.__bpa-is-active span,
.el-checkbox__input.is-checked .el-checkbox__inner,
.el-date-table td.current:not(.disabled) span,
.bpa-front-btn--primary:focus,
.el-date-picker.has-time .el-picker-panel__footer .el-button--default,
.el-date-picker.has-time .el-picker-panel__footer .el-button--default:focus,
.el-date-picker.has-time .el-picker-panel__footer .el-button--default:hover,
.el-date-picker.has-time .el-time-panel__btn.confirm,
.el-radio__input.is-checked .el-radio__inner,
.bpa-cp-ls__tab-menu .bpa-tm__item.__bpa-is-active .bpa-tm__item-icon
{
	background-color:'.$primary_color.' !important;
}
.el-date-picker.has-time .el-picker-panel__footer .el-button--default,
.el-date-picker.has-time .el-time-panel__btn.confirm {
	color: ' . $price_button_color . ' !important;
}
.bpa-front-form-control--radio .el-radio__input.is-checked+.el-radio__label,
.bpa-front-form-control--checkbox .el-checkbox__input.is-checked + .el-checkbox__label,
.bpa-custom-dropdown .el-select-dropdown__item.selected,
.bpa-front-cp-reschedule-time-dropdown .el-select-dropdown__item.selected {
	color:'.$primary_color.' !important;
}
.bpa-cp-ls__tab-menu .bpa-tm__item.__bpa-is-active span,
.el-checkbox__input.is-checked .el-checkbox__inner,
.bpa-front-btn--primary:focus,
.el-date-picker.has-time .el-picker-panel__footer .el-button--default,
.el-date-picker.has-time .el-picker-panel__footer .el-button--default:focus,
.el-date-picker.has-time .el-picker-panel__footer .el-button--default:hover,
.el-date-picker.has-time .el-time-panel__btn.confirm,
.el-radio__inner:hover,
.bpa-front-form-control--checkbox .el-checkbox__inner:hover,
.el-radio__input.is-checked .el-radio__inner,
.bpa-cp-ls__tab-menu .bpa-tm__item.__bpa-is-active .bpa-tm__item-icon {
	border-color:'.$primary_color.' !important;
}
.bpa-cp-ls__tab-menu .bpa-tm__item.__bpa-is-active span,
.bpa-front-btn--primary:focus,
.bpa-cp-ls__tab-menu .bpa-tm__item.__bpa-is-active .bpa-tm__item-icon {
	box-shadow:'.$box_shadow_color.' !important;
}
.bpa-cp-ls__tab-menu .bpa-tm__item.__bpa-is-active span {
	color:var(--bpa-cl-white) !important;
}';	

$bookingpress_customize_css_content .= '
.bpa-cp-ls__personal-details .bpa-cp-pd__content,
.bpa-cp-ls__tab-menu .bpa-tm__item,
.el-form-item__label span,
.bpa-front-form-label,
.bpa-front-cp-my-appointment .bpa-ma-staff-name,
.bpa-front-cp-rd__desc,
.el-date-picker.has-time button.el-time-panel__btn,
.el-date-picker.has-time button.el-button--mini,
.el-date-picker__time-header .el-input .el-input__inner,
.bpa-front-form-control--radio .el-radio__label,
.bpa-front-form-control--checkbox .el-checkbox__label,
.bpa-front-dab-left__title,
.bpa-front-dab-left__desc,
.bpa-front-toast-notification.--bpa-success p,
.bpa-vac__extra__col .bpa-ec--title,
.bpa-vac__extra__col .bpa-ec--price,
.bpa-front-toast-notification.--bpa-error p,
.bpa-custom-dropdown .el-select-dropdown__item span,
.bpa-custom-dropdown .el-select-group__title,
.bpa-front-form-control .el-textarea__inner,
.bpa-dialog--refund-appointments .bpa-front-rcr__item .bpa-front-rcr__item-label,
.bpa-dialog--refund-appointments .bpa-front-rcr__item .bpa-front-rcr__item-val,
.bpa-front-cp-refund-mob-drawer .bpa-front-rcr__item .bpa-front-rcr__item-label,
.bpa-front-cp-refund-mob-drawer .bpa-front-rcr__item .bpa-front-rcr__item-val,
.bpa-dialog--refund-appointments .bpa-front-ra__error-msg,
.bpa-front-cp-reschedule-time-dropdown .el-select-group__title,
.bpa-front-cp-reschedule-time-dropdown .el-select-dropdown__item
{
	font-family: ' . $title_font_family . ' !important;
}';
$bookingpress_customize_css_content .= '
.el-date-picker.has-time .el-time-spinner__item.active:not(.disabled),
.bpa-front-form-control .el-textarea__inner,
.bpa-dialog--refund-appointments .bpa-front-rcr__item .bpa-front-rcr__item-val,
.bpa-front-cp-refund-mob-drawer .bpa-front-rcr__item .bpa-front-rcr__item-val
{
	color: ' . $label_title_color . ' !important;
}';

$bookingpress_customize_css_content.='
.bpa-cp-ls__tab-menu .bpa-tm__item span,
.bpa-cp-ls__tab-menu .bpa-tm__item,
.bpa-front-form-label,
.el-form-item__label span,
.bpa-vac__extra__col .bpa-ec--title,
.bpa-vac__extra__col .bpa-ec--price,
.bpa-front-dab__left .bpa-front-dab-left__title,
.bpa-frontend-main-container button.el-button:not(:hover):not(:active):not(.has-text-color),
.bpa-front-is-deposit-payment-val,
.bpa-front-cp-rd__desc,
.el-date-picker.has-time .el-time-spinner__item,
.el-date-picker.has-time button.el-button--mini,
.el-date-picker.has-time button.el-time-panel__btn,
.bpa-custom-dropdown .el-select-dropdown__item,
.bpa-frontend-main-container .bpa-front-cp__login-btn-group .bpa-front-btn--borderless:hover,
.bpa-front-cp-reschedule-time-dropdown .el-select-dropdown__item{
	color: ' . $sub_title_color . ' !important;
}';

$bookingpress_customize_css_content.='
.bpa-cp-ls__personal-details .bpa-cp-pd__content,
.bpa-front-dab__left .bpa-front-dab-left__desc,
.bpa-front-btn:hover,
.bpa-front-form-control--radio .el-radio__label,
.bpa-front-form-control--checkbox .el-checkbox__label,
.bpa-custom-dropdown .el-select-group__title,
.bpa-front-form-control.el-select .el-input .el-select__caret,
.bpa-frontend-main-container .bpa-front-cp__login-btn-group .bpa-front-btn--borderless:not(:hover):not(:active):not(.has-text-color),
.bpa-dialog--refund-appointments .bpa-front-rcr__item .bpa-front-rcr__item-label,
.bpa-front-cp-refund-mob-drawer .bpa-front-rcr__item .bpa-front-rcr__item-label,
.bpa-front-cp-reschedule-time-dropdown .el-select-group__title
{
	color: ' . $content_color . ' !important;
}
.bpa-front-timeslotbox svg {
	fill: '.$content_color.' !important;
}';
$bookingpress_customize_css_content .= '               
@media (min-width: 1200px) {
	.bpa-cp-pd__title,
	.bpa-front-dab__left .bpa-front-dab-left__title {
		font-size:'. $sub_title_font_size . ' !important;
	} 	
	.bpa-cp-ls__personal-details .bpa-cp-pd__content,
	.bpa-cp-ls__tab-menu .bpa-tm__item span,
	.bpa-cp-ls__tab-menu .bpa-tm__item,
	.bpa-front-form-label,
	.bpa-front-dab__left .bpa-front-dab-left__desc,
	.bpa-front-cp-rd__desc {
		font-size:'. $content_font_size . ' !important;	
	}
	.bpa-vac__extra__col .bpa-ec--title,
	.bpa-vac__extra__col .bpa-ec--price,
	.bpa-front-is-deposit-payment-val {
		font-size:13px !important;	
	}
}';

			return $bookingpress_customize_css_content;
		}

		function bookingpress_generate_booking_form_customize_css_loader_func() {
			global $BookingPress;

			$bookingpress_get_white_label_svg = $BookingPress->bookingpress_get_settings('bpa_white_label_icon', 'general_setting');
			$bookingpress_primary_color = $BookingPress->bookingpress_get_customize_settings('primary_color', 'booking_form');
			
			if( $bookingpress_get_white_label_svg == 'bpa_square_icon'){

				$bpa_square_icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
									<rect fill="{bpa_square_icon_svg_color}" stroke="{bpa_square_icon_svg_color}" stroke-width="15" width="30" height="30" x="25" y="50">
										<animate attributeName="opacity" calcMode="spline" dur="1.4" values="1;0;1;"
											keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.4"></animate>
									</rect>
									<rect fill="{bpa_square_icon_svg_color}" stroke="{bpa_square_icon_svg_color}" stroke-width="15" width="30" height="30" x="85" y="50">
										<animate attributeName="opacity" calcMode="spline" dur="1.4" values="1;0;1;"
											keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.2"></animate>
									</rect>
									<rect fill="{bpa_square_icon_svg_color}" stroke="{bpa_square_icon_svg_color}" stroke-width="15" width="30" height="30" x="145" y="50">
										<animate attributeName="opacity" calcMode="spline" dur="1.4" values="1;0;1;"
											keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="0"></animate>
									</rect></svg>';

				$bpa_square_icon_svg = base64_encode(str_replace('{bpa_square_icon_svg_color}', $bookingpress_primary_color, $bpa_square_icon_svg ));

				$bookingpress_customize_css_content ='.bpa-frontend-main-container .bpa-front-loader svg { display: none; } .bpa-frontend-main-container .bpa-front-loader{ background: url(data:image/svg+xml;base64,'.$bpa_square_icon_svg.') no-repeat left top; background-size: 100%;}';


			}else if( $bookingpress_get_white_label_svg == 'bpa_ripple_icon'){

				$bpa_ripple_icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; display: block; shape-rendering: auto;" width="350px" height="350px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
				<circle cx="50" cy="50" r="0" fill="none" stroke="{bpa_ripple_icon_svg_color}" stroke-width="6">
				  <animate attributeName="r" repeatCount="indefinite" dur="1.5" values="0;36" keyTimes="0;1" keySplines="0 0.2 0.8 1" calcMode="spline" begin="0s"></animate>
				  <animate attributeName="opacity" repeatCount="indefinite" dur="1.5" values="1;0" keyTimes="0;1" keySplines="0.2 0 0.8 1" calcMode="spline" begin="0s"></animate>
				</circle><circle cx="50" cy="50" r="0" fill="none" stroke="{bpa_ripple_icon_svg_color}" stroke-width="6">
				  <animate attributeName="r" repeatCount="indefinite" dur="1.5" values="0;36" keyTimes="0;1" keySplines="0 0.2 0.8 1" calcMode="spline" begin="-0.5263157894736842s"></animate>
				  <animate attributeName="opacity" repeatCount="indefinite" dur="1.5" values="1;0" keyTimes="0;1" keySplines="0.2 0 0.8 1" calcMode="spline" begin="-0.5263157894736842s"></animate>
				</circle>
				</svg>';

				$bpa_ripple_icon_svg = base64_encode(str_replace('{bpa_ripple_icon_svg_color}', $bookingpress_primary_color, $bpa_ripple_icon_svg ));

				$bookingpress_customize_css_content ='.bpa-frontend-main-container .bpa-front-loader svg { display: none; } .bpa-frontend-main-container .bpa-front-loader{ background: url(data:image/svg+xml;base64,'.$bpa_ripple_icon_svg.') no-repeat left top; background-size: 100%; }';

			} 
			if( !empty( $bookingpress_customize_css_content )){

				wp_add_inline_style( 'bookingpress_front_custom_css', $bookingpress_customize_css_content, 'after' );
				wp_add_inline_style( 'bookingpress_front_mybookings_custom_css', $bookingpress_customize_css_content, 'after');
			}
		}

		function bookingpress_generate_booking_form_customize_css_func($bookingpress_customize_css_content,$bookingpress_custom_data_arr) {

			$shortcode_background_color        = $bookingpress_custom_data_arr['booking_form']['background_color'];
			$shortcode_footer_background_color = $bookingpress_custom_data_arr['booking_form']['footer_background_color'];
			$primary_color                     = $bookingpress_custom_data_arr['booking_form']['primary_color'];
			$border_color                     = $bookingpress_custom_data_arr['booking_form']['border_color'];
			$primary_alpha_color               = $bookingpress_custom_data_arr['booking_form']['primary_background_color'];
			$title_label_color                 = $bookingpress_custom_data_arr['booking_form']['label_title_color'];
			$title_font_size                   = '18px';
			$title_font_family                 = $bookingpress_custom_data_arr['booking_form']['title_font_family'];
			$title_font_family          	   = $title_font_family == 'Inherit Fonts' ? 'inherit' : $title_font_family;
			$content_color                     = $bookingpress_custom_data_arr['booking_form']['content_color'];
			$price_button_text_content_color   = $bookingpress_custom_data_arr['booking_form']['price_button_text_color'];                        
			$sub_title_font_size               = '16px';
			$content_font_size                 = '14px';
			$sub_title_color                   = $bookingpress_custom_data_arr['booking_form']['sub_title_color'];
			$hex                               = $primary_color;
			list($r, $g, $b)                   = sscanf($hex, '#%02x%02x%02x');
			$box_shadow_color                  = "0 4px 8px rgba($r,$g,$b,0.06), 0 8px 16px rgba($r,$g,$b,0.16)";

$bookingpress_customize_css_content.='
.bpa-fm--service__advance-options,
.bpa-fm--service__advance-options-popper,
.bpa-custom-duration-dropdown,
.bpa-front-form-control--radio .el-radio__inner::after,
.bpa-custom-dropdown.el-select-dropdown,
.bpa-custom-datepicker,
.bpa-front-form-control--file-upload,
.bpa-front-form-control--file-upload .bpa-fu__btn,
.bpa-front-complete-payment-container,
.bpa-custom-datepicker .el-picker-panel__footer,
.bpa-custom-datepicker .el-time-panel,
.bpa-cart-item--sm .bpa-body__extras-wrap,
.bpa-front-module--cart .bpa-cart-item--sm .bpa-bew__action-btns .bpa-front-btn,
.bpa-fm--service__advance-options.--bpa-is-mob,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sei__left .bpa-sei__left-checkbox .bpa-front-form-control--checkbox .el-checkbox__inner,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-service-extra__item .bpa-sei__right .bpa-front-form-control input{
	background-color: ' . $shortcode_background_color . ' !important;
}
.bpa-front-btn--primary:focus{
	box-shadow: ' . $box_shadow_color.' !important;
}
.bpa-front-form-control--number .el-input-number__decrease, 
.bpa-front-form-control--number .el-input-number__increase,
.bpa-fm--service__advance-options-popper .el-select-dropdown__item.hover, 
.bpa-fm--service__advance-options-popper .el-select-dropdown__item:hover,
.bpa-custom-duration-dropdown .el-select-dropdown__item.hover,
.bpa-custom-duration-dropdown .el-select-dropdown__item:hover,
.bpa-custom-dropdown .el-select-dropdown__item.hover,
.bpa-custom-dropdown .el-select-dropdown__item:hover,
.bpa-front--dt__custom-duration-card .bpa-front-cdc__left,
.bpa-front-refund-confirmation-content .bpa-front-rcc__body,
.bpa-front-module--cart .bpa-front-module-heading .bpa-fmc--head-counter,
.bpa-ci__service-actions .bpa-ci__sa-wrap,
.bpa-cart__item-expand-view,
.bpa-cart-item--sm .bpa-ci__body{
	background-color: ' . $shortcode_footer_background_color . ' !important;
}
.bpa-service-extra__item .bpa-sei__body,
.bpa-sao--footer,
.bpa-front-sm--col .bpa-front-sm-card,
.bpa-front-form-control--file-upload,
.bpa-front-form-control--file-upload .bpa-fu__btn,
.bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-is-total-row,
.bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-is-coupon-applied,
.bpa-front--dt__custom-duration-is-full,
.bpa-front--dt__custom-duration-card,
.bpa-front-form-control--radio .el-radio__inner,
.el-select-dropdown.el-popper.bpa-custom-dropdown.bpa-custom-duration-dropdown,
.bpa-front-complete-payment-container,
.bpa-front-form-control.--bpa-country-dropdown .vti__dropdown,
.bpa-custom-datepicker,
.bpa-custom-dropdown,
.bpa-fm--service__advance-options-popper,
.bpa-custom-datepicker .el-time-panel,
.bpa-custom-datepicker .el-time-panel__content::after, 
.bpa-custom-datepicker .el-time-panel__content::before,
.bpa-custom-datepicker .el-time-panel__footer,
.bpa-front-sm-card .bpa-front-sm-card__left .bpa-front-sm__default-img,
.bpa-front-refund-confirmation-content .bpa-front-rcc__body,
.bpa-cart__item .bpa-ci__service-brief .bpa-sb--left .bpa-front-si__default-img,
.bpa-front-module--cart .bpa-front-module-heading .bpa-fmc--head-counter,
.bpa-fmc--cart-items-wrap .bpa-cart__item,
.bpa-front-module--cart .bpa-fmc--right-btn .bpa-front-btn,
.bpa-cart__item .bpa-cart__item-body,
.bpa-ci__service-actions .bpa-ci__sa-wrap,
.bpa-cart-exw__title,
.bpa-cart-iev__head .bpa-cart-iev__h-item,
.bpa-front-module--cart .bpa-cart-item--sm .bpa-bew__action-btns .bpa-front-btn,
.bpa-cart-item--sm,
.bpa-cart-total-wrap-sm{
	border-color:'.$border_color.' !important;
}
.bpa-custom-datepicker .el-picker-panel__footer{
	border-top-color:'.$border_color.' !important;
}
.bpa-custom-datepicker .el-date-picker__time-header{
	border-bottom-color:'.$border_color.' !important;
}
.bpa-custom-dropdown .el-select-group__wrap::after,
.bpa-front-rcc__body .bpa-front-rcc-body__item:first-child::after{
	background-color:'.$border_color.' !important;
}
.bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-is-total-row .bpa-bs-ai__item.--bpa-is-total-price,
.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__total-label.--bpa-is-total-price,
.bpa-front-form-control--radio .el-radio__input.is-checked+.el-radio__label,
.bpa-fm--service__advance-options-popper .el-select-dropdown__item.selected,
.bpa-custom-duration-dropdown .el-select-dropdown__item.selected,
.bpa-custom-dropdown .el-select-dropdown__item.selected,
.bpa-cart__item-total .bpa-cit__item.--bpa-is-item-amt,
.el-date-table td.today:not(.current),
.el-date-table td.today:not(.current) span,
.el-date-picker__header-label:hover,
.el-picker-panel__content .el-date-table td:not(.current):not(.today) span:hover,
.el-picker-panel__content .el-date-table td:not(.next-month):not(.prev-month):not(.today):not(.current) span:hover
{
	color: ' . $primary_color . ' !important;
}
.bpa-front-sm--col.--bpa-sm-is-any-staff-col .bpa-front-sm-card {
	background-color: '.$primary_alpha_color.' !important;
}
.el-date-picker.has-time .el-picker-panel__footer .el-button--default,
.el-date-picker.has-time .el-picker-panel__footer .el-button--default:focus,
.el-date-picker.has-time .el-picker-panel__footer .el-button--default:hover,
.el-date-picker.has-time .el-time-panel__btn.confirm,
.el-date-table td.current:not(.disabled) span,
.bpa-front-form-control--file-upload .bpa-fu__btn:hover,
.bpa-front--dt__calendar.bpa-front-v-cal__is-only-days .vc-day .bpa-multi__day-select,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sei__left .bpa-sei__left-checkbox .bpa-front-form-control--checkbox .el-checkbox__input.is-checked .el-checkbox__inner{
	background-color:'.$primary_color.' !important;
}
.bpa-front-form-control--date-picker .el-input__prefix .el-input__icon::before{
	background-color:'.$content_color.' !important;
}
.bpa-front-cdc__left svg,
.bpa-front-module--cart .bpa-fmc--right-btn .bpa-front-btn svg,
.bpa-cart__item .bpa-ci__service-actions .bpa-front-btn--icon-without-box span svg,
.bpa-front-form-control.--bpa-country-dropdown .vti__dropdown .vti__selection svg,
.bpa-sei__left .bpa-sei__left-body .bpa-se--options .bpa-se-o__item svg,
.bpa-front-module--date-and-time.__sm .bpa-front--dt__ts-sm-back-btn .bpa-front-btn span svg,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sao__module-head .bpa-sao__mh--btn .el-link--inner svg, 
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-service-extra__load-more .bpa-se__lm--btn .el-link--inner svg, 
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sei__left .bpa-sei__left-body .bpa-sei__vm--btn .el-link--inner svg,
.bpa-front-sm-card .bpa-front-sm-card__left .bpa-front-sm__default-img svg,
.bpa-cart__item .bpa-ci__service-brief .bpa-sb--left .bpa-front-si__default-img svg,
.bpa-ci__service-brief .bpa-ci__expand-icon path,
.bpa-cart-item--sm .bpa-ci__head-options-row .bpa-hl__service-duration svg path,
.bpa-cart-item--sm .bpa-bew__action-btns .bpa-front-btn svg{
	fill: '.$content_color.' !important;
}
.bpa-cart-item--sm .bpa-bew__head-icon svg path{
	stroke: '.$content_color.' !important;
}
.bpa-front-form-control.el-input .el-icon-view:before,
.bpa-front-form-control--date-picker .el-input__prefix .el-input__icon::before{
	background-color:'.$content_color.' !important;
}
.bpa-front-cpc__vector .bpa-front-cpc__vector-item{
	fill:'.$primary_color.' !important;
}
.bpa-cart-item--sm .bpa-bew__action-btns .bpa-front-btn:hover svg,
.bpa-cart__item .bpa-ci__service-actions .bpa-front-btn--icon-without-box:hover span svg {
	fill: var(--bpa-cl-white) !important;
}
.el-date-picker.has-time .el-picker-panel__footer .el-button--default,
.el-date-picker.has-time .el-picker-panel__footer .el-button--default:focus,
.el-date-picker.has-time .el-picker-panel__footer .el-button--default:hover,
.el-date-picker.has-time .el-time-panel__btn.confirm,
.bpa-front-form-control--file-upload .bpa-fu__btn:hover,
.bpa-front-module--staff .bpa-front-sm--col.__bpa-is-selected .bpa-front-sm-card{
	border-color:'.$primary_color.' !important;
}';

$bookingpress_customize_css_content.='				
.bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-is-coupon-applied .bpa-bs-ai__item span,
.bpa-front-btn--primary span,
.el-date-table td.current:not(.disabled) span,
.el-date-picker.has-time .el-picker-panel__footer .el-button--default,
.el-date-picker.has-time .el-time-panel__btn.confirm,
.bpa-front-form-control--file-upload .bpa-fu__btn:hover{
	color: ' . $price_button_text_content_color . ' !important;
}';

$bookingpress_customize_css_content.='
.bpa-front-tabs .bpa-front-sec--sub-heading,
.bpa-sei__left .bpa-sei__left-body .bpa-se--heading,			
.bpa-sei__left .bpa-sei__left-body .bpa-se--options .bpa-se-o__item,
.bpa-sei__body .bpa-sei__body-desc,
.bpa-sao__module-row.--bpa-sao-staff-selection .bpa-staff__item .bpa-si__body .bpa-si__body-title,
.bpa-sao__module-row.--bpa-sao-staff-selection .bpa-staff__item .bpa-si__body .bpa-si__body-price-val,
.bpa-front-module--booking-summary .bpa-fm--bs-amount-item .bpa-bs-ai__item,
.bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-fm--bs__coupon-module-textbox .bpa-front-form-label,
.bpa-front-module--booking-summary .bpa-fm--bs-amount-item .bpa-bs-ai__item,
.bpa-fm--bs__deposit-payment-module .bpa-bs__dpm-title,
.bpa-front-form-control--radio .el-radio__label,
.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__total-label,
.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__label,
.bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-is-coupon-applied .bpa-bs-ai__item span,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sao__module-head .bpa-sao__mh--btn,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-service-extra__load-more .bpa-se__lm--btn,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sei__left .bpa-sei__left-body .bpa-sei__vm--btn,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sao__module-head .bpa-sao__module-head-title,
.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__sub-label,
.bpa-fm--service__advance-options-popper .el-select-dropdown__item span,
.bpa-custom-duration-dropdown .el-select-dropdown__item span,
.bpa-custom-dropdown .el-select-dropdown__item span,
.bpa-cart__item .bpa-ci__service-brief .bpa-sb--right .bpa-sbr__title,
.bpa-cart__item .bpa-ci__service-col-val,
.bpa-ci__service-brief .bpa-sb--right .bpa-sb__options .bpa-sbo__item,
.bpa-cart__item-total .bpa-cit__item,
.bpa-front-btn--primary strong,
.bpa-fmc--head-counter,
.bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-bs__coupon-validation.--is-error p,
.bpa-front-toast-notification.--bpa-error p,
.bpa-front-sm-card .bpa-front-sm-card__body .bpa-front-cb__title,
.bpa-front-sm-card .bpa-front-sm-card__body .bpa-front-cb__item,
.bpa-front-btn,
.el-date-picker.has-time button.el-time-panel__btn,
.el-date-picker.has-time button.el-button--mini,
.el-date-picker__header-label,
.el-picker-panel__content .el-date-table th,
.el-picker-panel__content .el-date-table td span,
.el-date-picker.has-time .el-time-spinner__item,
.el-date-picker__time-header .el-input .el-input__inner,
.bpa-front-cdf__title,
.bpa-front--dt__custom-duration-is-full .bpa-front--dt-ts__sub-heading,
.bpa-front--dt__custom-duration-is-full .bpa-front-cdf__desc,
.bpa-front-cdc__right .bpa-front-cdc__right-title,
.bpa-li-col__body .bpa-li-col__title,
.bpa-li-col__body .bpa-li-col__address,
.bpa-front-form-control--file-upload .bpa-fu__btn,
.bpa-front-complete-payment-container .bpa-front-cpc__head,
.bpa-front-form-field--file-upload .el-upload-list__item-name,
.bpa-front-cancel-confirmation-container .bpa-front-refund-confirmation-content .bpa-front-rcc__desc,
.bpa-front-rcc__body .bpa-front-rcc-body__item .bpa-front-rcc-item__label,
.bpa-front-rcc__body .bpa-front-rcc-body__item .bpa-front-rcc-item__val,
.bpa-cart-iev__h-item .bpa-cart-iev__hi-label,
.bpa-cart-iev__h-item .bpa-cart-iev__hi-val,
.bpa-cart-exw__title,
.bpa-cart-exi__left .bpa-cart-exi__name, 
.bpa-cart-exi__right .bpa-cart-exi__price,
.bpa-cart-exi__left .bpa-cart-exi-left__opts .bpa-cart-exi__duration, 
.bpa-cart-exi__left .bpa-cart-exi-left__opts .bpa-cart-exi__qty,
.bpa-cart-item--sm .bpa-ci__head-service-row .bpa-hl__service-title,
.bpa-cart-item--sm .bpa-ci__head-service-row .bpa-hl__service-price,
.bpa-cart-item--sm .bpa-ci__head-options-row .bpa-hl__service-dt-val,
.bpa-cart-item--sm .bpa-ci__head-options-row .bpa-hl__service-duration,
.bpa-cart-item--sm .bpa-bo__item .bpa-boi__left,
.bpa-cart-item--sm .bpa-bo__item .bpa-boi__right,
.bpa-cart-item--sm .bpa-bew__head-title,
.bpa-cart-item--sm .bpa-bew-bi__extra-service-title,
.bpa-cart-item--sm .bpa-bew-bi__right .bpa-bew-bi-extra-price,
.bpa-cart__item-body .bpa-ci__service-time .bpa-front-bs__waiting-note .bpa-front-wn--label,
.bpa-front-module--booking-summary .bpa-front-summary-content__lg .bpa-front-module--bs-summary-content-item .bpa-front-bs__waiting-note .bpa-front-wn--label,
.bpa-cart-items-wrap--sm .bpa-hl__service-dt-val .bpa-front-bs__waiting-note .bpa-front-wn--label,
.bpa-front-module--booking-summary .bpa-front-summary-content__sm .bpa-front-module--bs-summary-content-item .bpa-front-bs__waiting-note .bpa-front-wn--label{
	font-family: ' . $title_font_family . ' !important;
}';

$bookingpress_customize_css_content.='
.bpa-front-tabs .bpa-front-sec--sub-heading,
.bpa-sei__left .bpa-sei__left-body .bpa-se--heading,
.bpa-sao__module-row.--bpa-sao-staff-selection .bpa-staff__item .bpa-si__body .bpa-si__body-price-val,
.bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-is-total-row .bpa-bs-ai__item,
.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__total-label,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sao__module-head .bpa-sao__module-head-title,
.bpa-cart__item .bpa-ci__service-brief .bpa-sb--right .bpa-sbr__title,
.bpa-cart__item-total .bpa-cit__item,
.bpa-front-sm-card .bpa-front-sm-card__body .bpa-front-cb__title,
.el-date-picker.has-time .el-time-spinner__item.active:not(.disabled),
.el-date-picker__header-label,
.el-picker-panel__content .el-date-table td:not(.next-month):not(.prev-month):not(.today):not(.current) span,
.el-date-picker__time-header .el-input .el-input__inner,
.bpa-front--dt__custom-duration-is-full .bpa-front--dt-ts__sub-heading,
.bpa-front-cdc__right-title .bpa-front-cdc__price-val,
.bpa-li-col__body .bpa-li-col__title,
.bpa-front-complete-payment-container .bpa-front-cpc__head,
.bpa-front-form-field--file-upload .el-upload-list__item-name,
.bpa-front-rcc__body .bpa-front-rcc-body__item .bpa-front-rcc-item__val,
.bpa-cart-iev__h-item .bpa-cart-iev__hi-val,
.bpa-cart-exw__title,
.bpa-cart-item--sm .bpa-ci__head-service-row .bpa-hl__service-title,
.bpa-cart-item--sm .bpa-bo__item .bpa-boi__right,
.bpa-cart-item--sm .bpa-bew__head-title,
.bpa-cart__item-body .bpa-ci__service-time .bpa-front-bs__waiting-note .bpa-front-wn--label,
.bpa-front-module--booking-summary .bpa-front-summary-content__lg .bpa-front-module--bs-summary-content-item .bpa-front-bs__waiting-note .bpa-front-wn--label,
.bpa-cart-items-wrap--sm .bpa-hl__service-dt-val .bpa-front-bs__waiting-note .bpa-front-wn--label,
.bpa-front-module--booking-summary .bpa-front-summary-content__sm .bpa-front-module--bs-summary-content-item .bpa-front-bs__waiting-note .bpa-front-wn--label{
	color: ' . $title_label_color . ' !important;
}';
$bookingpress_customize_css_content.='
.bpa-sei__left .bpa-sei__left-body .bpa-se--options .bpa-se-o__item,
.bpa-sei__left .bpa-sei__left-body .bpa-se--options .bpa-se-o__item span,
.bpa-sei__body .bpa-sei__body-desc,
.bpa-sao__module-row.--bpa-sao-staff-selection .bpa-staff__item .bpa-si__body .bpa-si__body-title,
.bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-fm--bs__coupon-module-textbox .bpa-front-form-label,
.bpa-front-module--booking-summary .bpa-fm--bs-amount-item:not(.bpa-is-coupon-applied):not(.bpa-is-total-row) .bpa-bs-ai__item,
.bpa-fm--bs__deposit-payment-module .bpa-bs__dpm-title,
.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__label,
.bpa-cart__item .bpa-ci__service-col-val,
.bpa-ci__service-brief .bpa-sb--right .bpa-sb__options .bpa-sbo__item,
.bpa-frontend-main-container button.el-button:not(:hover):not(:active):not(.has-text-color),
.bpa-fmc--head-counter,
.bpa-front-sm-card .bpa-front-sm-card__body .bpa-front-cb__item,
.el-date-picker.has-time .el-time-spinner__item,
.el-picker-panel__content .el-date-table th,
.el-date-picker.has-time .el-time-spinner__item,
.el-date-picker.has-time button.el-button--mini,
.el-date-picker.has-time button.el-time-panel__btn,
.bpa-front-cdf__title,
.bpa-front--dt__custom-duration-is-full .bpa-front-cdf__desc,
.bpa-li-col__body .bpa-li-col__address,
.bpa-front-form-control--number .el-input-number__decrease i, 
.bpa-front-form-control--number .el-input-number__increase i,
.bpa-fm--service__advance-options-popper .el-select-dropdown__item,
.bpa-custom-duration-dropdown .el-select-dropdown__item,
.bpa-custom-dropdown .el-select-dropdown__item,
.bpa-front-form-control--file-upload .bpa-fu__btn,
.bpa-front-module--booking-summary .bpa-fm--bs-amount-item:not(.bpa-is-total-row) .bpa-bs-ai__item,
.bpa-front-rcc__body .bpa-front-rcc-body__item .bpa-front-rcc-item__label,
.bpa-cart-iev__h-item .bpa-cart-iev__hi-label,
.bpa-cart-exi__left .bpa-cart-exi__name, 
.bpa-cart-exi__right .bpa-cart-exi__price,
.bpa-cart-exi__left .bpa-cart-exi-left__opts .bpa-cart-exi__duration, 
.bpa-cart-exi__left .bpa-cart-exi-left__opts .bpa-cart-exi__qty,
.bpa-cart-item--sm .bpa-ci__head-service-row .bpa-hl__service-price,
.bpa-cart-item--sm .bpa-ci__head-options-row .bpa-hl__service-dt-val,
.bpa-cart-item--sm .bpa-ci__head-options-row .bpa-hl__service-duration,
.bpa-cart-item--sm .bpa-bo__item .bpa-boi__left,
.bpa-cart-item--sm .bpa-bew-bi__extra-qty,
.bpa-cart-item--sm .bpa-bew-bi__extra-duration,
.bpa-cart-item--sm .bpa-bew-bi__right .bpa-bew-bi-extra-price,
.bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-is-total-row .bpa-fm-tr__tax-included-label,
.bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .--bpa-is-dpm-total-item .bpa-fm-tr__tax-included-label{
	color: ' . $sub_title_color . ' !important;
}';
$bookingpress_customize_css_content.='
.bpa-front-form-control--radio .el-radio__label,
.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__sub-label,
.el-picker-panel .el-icon-arrow-left::before,
.el-picker-panel .el-icon-arrow-right::before,
.el-picker-panel .el-icon-d-arrow-left::before,
.el-picker-panel .el-icon-d-arrow-right::before,
.bpa-front-cdc__right .bpa-front-cdc__right-title,
.bpa-cart__item-total-deposit .bpa-cit__item-deposit-label,
.bpa-front-form-control.el-select .el-input .el-select__caret,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sao__module-head .bpa-sao__mh--btn,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sei__left .bpa-sei__left-body .bpa-sei__vm--btn,
.bpa-fm--service__advance-options.--bpa-is-mob .bpa-service-extra__load-more .bpa-se__lm--btn,
.bpa-custom-dropdown .el-select-group__title,
.bpa-front-form-field--file-upload .el-upload-list__item-name [class^=el-icon],
.bpa-front-form-field--file-upload .el-upload-list__item .el-icon-close,
.bpa-frontend-main-container .bpa-front-module--cart .bpa-fmc--right-btn .bpa-front-btn:hover,
.bpa-frontend-main-container .bpa-front-module--cart .bpa-fmc--right-btn .bpa-front-btn:focus,
.bpa-front-cancel-confirmation-container .bpa-front-refund-confirmation-content .bpa-front-rcc__desc,
.bpa-ci__service-price .bpa-ci__service-deposit-price-value .bpa-ci__service-full-price-value,
.bpa-cart-item--sm .bpa-bew-bi__extra-service-title,
.bpa-front--dt__calendar .vc-day.is-today .bpa_day-select .vc-day-content,
.bpa-repeter-parent-col-comman .bpa-front-form-label{
	color: ' . $content_color . ' !important;
}';

$bookingpress_customize_css_content.='
.bpa-front-module--booking-summary .bpa-fm--bs-amount-item .bpa-bs-ai__item.bpa-is-ca__price:not(.bpa-is-tip__price){
	color: var(--bpa-sc-danger) !important;
}';


$bookingpress_customize_css_content.='	
.bpa-front-reschedule-content,.bpa-front-cp-reschedule-date-picker,.bpa-front-cp-reschedule-time-dropdown{
	background-color: '.$shortcode_background_color.' !important;
}
.bpa-front-reschedule-content .bpa-front-module-heading{
	color: ' . $title_label_color . ' !important;
} 			
.bpa-front-reschedule-content .bpa-front-cp-rd__desc
{
	font-family: ' . $title_font_family . ' !important;
	color: ' . $sub_title_color . ' !important;
}
.bpa-front-reschedule-content .bpa-front-form-label,
.el-form-item__label span{
	color: ' . $sub_title_color . ' !important;
}
.bpa-front-reschedule-content .bpa-front-reschedule-footer .bpa-front-btn--primary, .bpa-front-reschedule-content .bpa-front-reschedule-footer .bpa-front-btn--primary:focus{
	background-color:'.$primary_color.' !important;
}
.bpa-front-reschedule-content .bpa-front-form-control input{
	color: ' . $title_label_color . ' !important;
	font-family: ' . $title_font_family . ' !important;  
}		
';

$bookingpress_customize_css_content .= '               
@media (min-width: 1200px) {
	.bpa-front-tabs .bpa-front-sec--sub-heading {
		font-size:'. $title_font_size . ' !important;
	}	
	.bpa-sei__left .bpa-sei__left-body .bpa-se--heading,
	.bpa-front-btn:not(.bpa-fm--bs__coupon-module-textbox .bpa-front-btn),
	.bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-is-total-row .bpa-bs-ai__item,
	.bpa-front-module--booking-summary .bpa-front-module--bs-amount-details .bpa-is-total-row .bpa-bs-ai__item.--bpa-is-total-price,
	.bpa-fm--bs__deposit-payment-module .bpa-bs__dpm-title,
	.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__total-label,
	.bpa-cart__item .bpa-ci__service-brief .bpa-sb--right .bpa-sbr__title,
	.bpa-cart__item-total .bpa-cit__item,
	.bpa-front-sm-card .bpa-front-sm-card__body .bpa-front-cb__title,	
	.bpa-front--dt__custom-duration-is-full .bpa-front--dt-ts__sub-heading,
	.bpa-front-cancel-confirmation-container .bpa-front-refund-confirmation-content .bpa-front-rcc__desc,
	.bpa-front-rcc__body .bpa-front-rcc-body__item .bpa-front-rcc-item__label,
	.bpa-front-rcc__body .bpa-front-rcc-body__item .bpa-front-rcc-item__val,
	.bpa-cart-exw__title
	{
		font-size:'. $sub_title_font_size . ' !important;
	}	
	.bpa-sei__left .bpa-sei__left-body .bpa-se--options .bpa-se-o__item,
	.bpa-sei__body .bpa-sei__body-desc,
	.bpa-sao__module-row.--bpa-sao-staff-selection .bpa-staff__item .bpa-si__body .bpa-si__body-title,
	.bpa-sao__module-row.--bpa-sao-staff-selection .bpa-staff__item .bpa-si__body .bpa-si__body-price-val,
	.bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-fm--bs__coupon-module-textbox .bpa-front-form-label,
	.bpa-front-module--booking-summary .bpa-fm--bs-amount-item .bpa-bs-ai__item,
	.bpa-front-form-control--radio .el-radio__label,	
	.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__label,
	.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__sub-label,
	.bpa-cart__item .bpa-ci__service-col-val,
	.bpa-ci__service-brief .bpa-sb--right .bpa-sb__options .bpa-sbo__item,
	.bpa-front-sm-card .bpa-front-sm-card__body .bpa-front-cb__item,
	.bpa-fm--bs__coupon-module-textbox .bpa-front-btn,
	.bpa-front-cdf__title,
	.bpa-front-cdc__right .bpa-front-cdc__right-title,
	.bpa-li-col__body .bpa-li-col__address,
	.bpa-cart-iev__h-item .bpa-cart-iev__hi-label,
	.bpa-cart-iev__h-item .bpa-cart-iev__hi-val,
	.bpa-cart-item--sm .bpa-ci__head-options-row .bpa-hl__service-dt-val,
	.bpa-cart-item--sm .bpa-ci__head-options-row .bpa-hl__service-duration,
	.bpa-cart-item--sm .bpa-bo__item .bpa-boi__right,
	.bpa-cart-item--sm .bpa-bew__head-title{
		font-size:'. $content_font_size . ' !important;	
	}    
	.bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-is-coupon-applied .bpa-bs-ai__item span,
	.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sao__module-head .bpa-sao__mh--btn,
	.bpa-front--dt__custom-duration-is-full .bpa-front-cdf__desc,
	.bpa-cart-exi__left .bpa-cart-exi__name, 
	.bpa-cart-exi__right .bpa-cart-exi__price,
	.bpa-cart-exi__left .bpa-cart-exi-left__opts .bpa-cart-exi__duration, 
	.bpa-cart-exi__left .bpa-cart-exi-left__opts .bpa-cart-exi__qty,
	.bpa-cart-item--sm .bpa-hl__service-price .bpa-ci__service-full-price-value,
	.bpa-cart-item--sm .bpa-bo__item .bpa-boi__left,
	.bpa-cart-item--sm .bpa-bew-bi__extra-service-title,
	.bpa-cart-item--sm .bpa-bew-bi__right .bpa-bew-bi-extra-price,
	.bpa-cart__item-body .bpa-ci__service-time .bpa-front-bs__waiting-note .bpa-front-wn--label,
	.bpa-front-module--booking-summary .bpa-front-summary-content__lg .bpa-front-module--bs-summary-content-item .bpa-front-bs__waiting-note .bpa-front-wn--label,
	.bpa-cart-items-wrap--sm .bpa-hl__service-dt-val .bpa-front-bs__waiting-note .bpa-front-wn--label,
	.bpa-front-module--booking-summary .bpa-front-summary-content__sm .bpa-front-module--bs-summary-content-item .bpa-front-bs__waiting-note .bpa-front-wn--label{
		font-size: 13px !important;
	}
	.bpa-li-col__body .bpa-li-col__title {
		font-size: 15px !important;
	}
}
@media (max-width: 1024px) {
	.bpa-front-tabs .bpa-front-sec--sub-heading{
		font-size: ' . $sub_title_font_size . ' !important;
	}
	.bpa-sei__left .bpa-sei__left-body .bpa-se--heading{
		font-size: 15px !important; 
	}
	.bpa-sei__left .bpa-sei__left-body .bpa-se--options .bpa-se-o__item,
	.bpa-sei__body .bpa-sei__body-desc{
		font-size:'. $content_font_size . ' !important;
	}
}
@media (max-width: 576px) {                               
	.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sao__module-head .bpa-sao__module-head-title,
	.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sei__left .bpa-sei__left-body .bpa-se--options .bpa-se-o__item,
	.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sei__left .bpa-sei__left-body .bpa-sei__vm--btn,
	.bpa-fm--service__advance-options.--bpa-is-mob .bpa-service-extra__load-more .bpa-se__lm--btn,
	.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sei__body .bpa-sei__body-desc,
	.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sao__module-head .bpa-sao__mh--btn,
	.bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-is-coupon-applied .bpa-bs-ai__item span {
		font-size:13px !important;
	}
	.bpa-fm--service__advance-options.--bpa-is-mob .bpa-front-sec--sub-heading,
	.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__total-label {
		font-size:15px !important;
	}
	.bpa-fm--service__advance-options.--bpa-is-mob .bpa-sei__left .bpa-sei__left-body .bpa-se--heading,
	.bpa-fm--bs__deposit-payment-module .bpa-dpm__item .bpa-dpm-item__sub-label,
	.bpa-front-module--booking-summary .bpa-is-coupon-module-enable .bpa-fm--bs__coupon-module-textbox .bpa-front-form-label,
	.bpa-front-tabs .bpa-front-form-control input,
	.bpa-front-module--booking-summary .bpa-fm--bs-amount-item .bpa-bs-ai__item
	{
		font-size:'. $content_font_size . ' !important;
	}	
}';
			return $bookingpress_customize_css_content;

		}


		function bookingpress_wp_timezone_lists(){
			static $mo_loaded = false, $locale_loaded = null;

			$selected_zone = wp_timezone_string();
			$locale = "";
 
			$continents = array( 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific' );
		
			// Load translations for continents and cities.
			if ( ! $mo_loaded || $locale !== $locale_loaded ) {
				$locale_loaded = $locale ? $locale : get_locale();
				$mofile        = WP_LANG_DIR . '/continents-cities-' . $locale_loaded . '.mo';
				unload_textdomain( 'continents-cities' );
				load_textdomain( 'continents-cities', $mofile );
				$mo_loaded = true;
			}
		
			$zonen = array();
			foreach ( timezone_identifiers_list() as $zone ) {
				$zone = explode( '/', $zone );
				if ( ! in_array( $zone[0], $continents, true ) ) {
					continue;
				}
		
				// This determines what gets set and translated - we don't translate Etc/* strings here, they are done later.
				$exists    = array(
					0 => ( isset( $zone[0] ) && $zone[0] ),
					1 => ( isset( $zone[1] ) && $zone[1] ),
					2 => ( isset( $zone[2] ) && $zone[2] ),
				);
				$exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
				$exists[4] = ( $exists[1] && $exists[3] );
				$exists[5] = ( $exists[2] && $exists[3] );
		
				// phpcs:disable WordPress.WP.I18n.LowLevelTranslationFunction,WordPress.WP.I18n.NonSingularStringLiteralText
				$zonen[] = array(
					'continent'   => ( $exists[0] ? $zone[0] : '' ),
					'city'        => ( $exists[1] ? $zone[1] : '' ),
					'subcity'     => ( $exists[2] ? $zone[2] : '' ),
					't_continent' => ( $exists[3] ? translate( str_replace( '_', ' ', $zone[0] ), 'continents-cities' ) : '' ),
					't_city'      => ( $exists[4] ? translate( str_replace( '_', ' ', $zone[1] ), 'continents-cities' ) : '' ),
					't_subcity'   => ( $exists[5] ? translate( str_replace( '_', ' ', $zone[2] ), 'continents-cities' ) : '' ),
				);
				// phpcs:enable
			}
			usort( $zonen, '_wp_timezone_choice_usort_callback' );

			$structure = array();
			$timezones_list_arr = array();
		
			foreach ( $zonen as $key => $zone ) {
				// Build value in an array to join later.
				$value = array( $zone['continent'] );
		
				if ( empty( $zone['city'] ) ) {
					// It's at the continent level (generally won't happen).
					$display = $zone['t_continent'];
				} else {
					// It's inside a continent group.
		
					// Continent optgroup.
					if ( ! isset( $zonen[ $key - 1 ] ) || $zonen[ $key - 1 ]['continent'] !== $zone['continent'] ) {
						$label       = $zone['t_continent'];
						$timezones_list_arr[$label] = array();
					}
		
					// Add the city to the value.
					$value[] = $zone['city'];
		
					$display = $zone['t_city'];
					if ( ! empty( $zone['subcity'] ) ) {
						// Add the subcity to the value.
						$value[]  = $zone['subcity'];
						$display .= ' - ' . $zone['t_subcity'];
					}
				}
		
				// Build the value.
				$value    = implode( '/', $value );
				$timezones_list_arr[$zone['t_continent']][] = $value;
			}

			// Do UTC.
			$timezones_list_arr['UTC'] = array('UTC');
		
			// Do manual UTC offsets.
			$timezones_list_arr['Manual Offsets'] = array();
			$offset_range = array(-12,-11.5,-11,-10.5,-10,-9.5,-9,-8.5,-8,-7.5,-7,-6.5,-6,-5.5,-5,-4.5,-4,-3.5,-3,-2.5,-2,-1.5,-1,-0.5,0,0.5,1,1.5,2,2.5,3,3.5,4,4.5,5,5.5,5.75,6,6.5,7,7.5,8,8.5,8.75,9,9.5,10,10.5,11,11.5,12,12.75,13,13.75,14);
			foreach ( $offset_range as $offset ) {
				if ( 0 <= $offset ) {
					$offset_name = '+' . $offset;
				} else {
					$offset_name = (string) $offset;
				}
		
				$offset_value = $offset_name;
				$offset_name  = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $offset_name );
				$offset_name  = 'UTC' . $offset_name;
				$offset_value = 'UTC' . $offset_value;

				$timezones_list_arr['Manual Offsets'][] = esc_attr($offset_name);
			}
		
			return $timezones_list_arr;
		}

		function bookingpress_admin_common_vue_methods(){
			global $bookingpress_notification_duration;
		?>
			//Add New Function For Currency
			bookingpress_price_with_currency_symbol( price_amount, ignore_symbol = false ){
				const vm = this;
				if( "String" == typeof price_amount ){
					price_amount = parseFloat( price_amount );
				}
				
				let currency_separator = vm.bookingpress_currency_separator;
				let decimal_points = vm.bookingpress_decimal_points;

				if( "comma-dot" == currency_separator ){
					price_amount = vm.bookingpress_number_format( price_amount, decimal_points, ".", "," );
				} else if( "dot-comma" == currency_separator ){
					price_amount = vm.bookingpress_number_format( price_amount, decimal_points, ",", "." );
				} else if( "space-dot" == currency_separator ){
					price_amount = vm.bookingpress_number_format( price_amount, decimal_points, ".", " " );
				} else if( "space-comma" == currency_separator ){
					price_amount = vm.bookingpress_number_format( price_amount, decimal_points, ",", " " );
				} else if( "custom" == currency_separator ){
					let custom_comma_separator = vm.bookingpress_custom_comma_separator;
					let custom_thousand_separator = vm.bookingpress_custom_thousand_separator;
					price_amount = vm.bookingpress_number_format( price_amount, decimal_points, custom_comma_separator, custom_thousand_separator );
				}

				if( true == ignore_symbol ){
					return price_amount;
				}

				let currency_symbol = vm.bookingpress_currency_symbol;
				let currency_symbol_pos = vm.bookingpress_currency_symbol_position;

				if( "before" == currency_symbol_pos ){
					price_amount = currency_symbol + price_amount;
				} else if( "before_with_space" == currency_symbol_pos ){
					price_amount = currency_symbol + " " + price_amount;
				} else if( "after" == currency_symbol_pos ){
					price_amount = price_amount + currency_symbol;
				} else if( "after_with_space" == currency_symbol_pos ){
					price_amount = price_amount + " " + currency_symbol;
				}

				return price_amount;

			},			
			bookingpress_number_format( number, decimals, decPoint, thousandsSep ){
				number = (number + "").replace(/[^0-9+\-Ee.]/g, "")
				const n = !isFinite(+number) ? 0 : +number
				const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
				const sep = (typeof thousandsSep === "undefined") ? "," : thousandsSep
				const dec = (typeof decPoint === "undefined") ? "." : decPoint
				let s = ""
				const toFixedFix = function (n, prec) {
					if (("" + n).indexOf("e") === -1) {
					return +(Math.round(n + "e+" + prec) + "e-" + prec)
					} else {
					const arr = ("" + n).split("e")
					let sig = ""
					if (+arr[1] + prec > 0) {
						sig = "+"
					}
					return (+(Math.round(+arr[0] + "e" + sig + (+arr[1] + prec)) + "e-" + prec)).toFixed(prec)
					}
				}
				// @todo: for IE parseFloat(0.55).toFixed(0) = 0;
				s = (prec ? toFixedFix(n, prec).toString() : "" + Math.round(n)).split(".")
				if (s[0].length > 3) {
					s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
				}
				if ((s[1] || "").length < prec) {
					s[1] = s[1] || ""
					s[1] += new Array(prec - s[1].length + 1).join("0")
				}
				return s.join(dec)
			},

			bookingpress_admin_get_final_step_amount(){

                const vm = this;
				var total_amount = vm.appointment_formdata.service_price_without_currency;
				if(typeof vm.appointment_services_list != "undefined"){
					let selected_service = vm.appointment_formdata.appointment_selected_service;
					let services_lists = vm.appointment_services_list;
					let max_capacity = 0;
					services_lists.forEach( function( categories ){
						let category_service_list = categories.category_services;
						for( let index in category_service_list ) { 
							let services = category_service_list[index];
							let service_id = services.service_id;
							if( service_id == selected_service ){
								total_amount = parseFloat(services.service_price_without_currency);
								vm.appointment_formdata.service_price_without_currency = total_amount;
							}
						}
					});				
				}
				if(vm.is_staff_enable){					
					if(typeof vm.appointment_formdata.selected_staffmember != "undefined"){
						var selected_staffmember = vm.appointment_formdata.selected_staffmember;
						if(vm.appointment_formdata.appointment_selected_service){
							var staff_member_details = vm.bookingpress_loaded_staff[vm.appointment_formdata.appointment_selected_service];							
							Object.entries(staff_member_details).forEach(entry => {
								const [key, value] = entry;							
								if(staff_member_details[key].bookingpress_staffmember_id == vm.appointment_formdata.selected_staffmember){
									total_amount = parseFloat(staff_member_details[key].bookingpress_service_price);																											
									vm.appointment_formdata.service_price_without_currency = total_amount;									
								}
							});
						}				
					}	
				}

				
				if(vm.is_custom_service_duration){	
					if(typeof vm.appointment_formdata.enable_custom_service_duration !== 'undefined' && vm.appointment_formdata.enable_custom_service_duration == true){
						if(typeof vm.appointment_formdata.custom_service_duration_value !== 'undefined' && vm.appointment_formdata.custom_service_duration_value != ''){						
							if(typeof vm.bookingpress_custom_service_durations_slot !== 'undefined'){														
								vm.bookingpress_custom_service_durations_slot.forEach(function(item,index,arr){
									if(item.value == vm.appointment_formdata.custom_service_duration_value) {
										if(item.service_duration_unit == 'd'){	
											total_amount = item.real_price;
											vm.appointment_formdata.service_price_without_currency = total_amount;  
										}else{
											/*total_amount = item.service_price_without_currency;*/
											total_amount = item.real_price;
											vm.appointment_formdata.service_price_without_currency = total_amount;  
										}                  
									}
								});
							}
						}
					}
				}
				
				<?php do_action('bookingpress_admin_calculate_total_after_custom_duration_price');  ?>

				var subtotal_price = total_amount;	
				var selected_bring_members = parseInt(vm.appointment_formdata.selected_bring_members);
				total_amount = total_amount * selected_bring_members;

				/* Added Service Extras Calculations */
				if(vm.is_extras_enable){	
					var bookingpress_extras_price_total = 0;
					if(typeof vm.appointment_formdata.appointment_selected_service != "undefined"){
						if(typeof vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service] != "undefined"){
							if(vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service] != ""){
								let appointment_extra_details = vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service];
								Object.entries(appointment_extra_details).forEach(entry => {
									const [key, value] = entry;							
									if(value.bookingpress_is_selected){									
										bookingpress_extras_price_total = bookingpress_extras_price_total + (parseFloat(value.bookingpress_extra_service_price) * parseInt(value.bookingpress_selected_qty));
									}
								});
							}
						}	
					}
					vm.appointment_formdata.extras_total = bookingpress_extras_price_total;
					vm.appointment_formdata.extras_total_with_currency = vm.bookingpress_price_with_currency_symbol( bookingpress_extras_price_total );
					total_amount = total_amount + bookingpress_extras_price_total;
				}
				
				<?php do_action('bookingpress_admin_calculate_subtotal_price');  ?>

				/* New tax calculation Start Here */
				var tax_amount = 0;
				if(vm.is_tax_enable){	
					if(vm.appointment_formdata.tax_percentage != ''){
						var tax_percentage = parseFloat(vm.appointment_formdata.tax_percentage);					
						if(vm.appointment_formdata.tax_price_display_options == "include_taxes"){
							tax_amount = (total_amount * tax_percentage) / (100+tax_percentage);
						}else{
							tax_amount = total_amount * ( tax_percentage / 100 );
							total_amount = total_amount + tax_amount;
						}
					}									
				}
				vm.appointment_formdata.tax = tax_amount; 
				vm.appointment_formdata.tax_with_currency = vm.bookingpress_price_with_currency_symbol( tax_amount );
				/* New tax calculation Over Here */
				
				vm.appointment_formdata.total_amount_with_currency = vm.bookingpress_price_with_currency_symbol( total_amount );
        		vm.appointment_formdata.total_amount = total_amount;											
				if(vm.is_coupon_enable == 1){
					if(vm.bpa_coupon_apply_disabled == 1){						
						if(typeof vm.appointment_formdata.coupon_discounted_amount != "undefined"){	
							total_amount = total_amount - vm.appointment_formdata.coupon_discounted_amount;													
							vm.appointment_formdata.total_amount = total_amount;                        
                        	vm.appointment_formdata.total_amount_with_currency = vm.bookingpress_price_with_currency_symbol( total_amount );                                                															
						}
					}else{
						vm.appointment_formdata.total_amount_with_currency = vm.bookingpress_price_with_currency_symbol( total_amount );
                    	vm.appointment_formdata.total_amount = total_amount;                    	
					}	
				}
				vm.appointment_formdata.subtotal_with_currency = vm.bookingpress_price_with_currency_symbol( subtotal_price );
        		vm.appointment_formdata.subtotal = subtotal_price;
				if(typeof vm.appointment_formdata.tip_amount !== 'undefined'){
					if(vm.appointment_formdata.tip_amount){
						var tip_amount = parseFloat(vm.appointment_formdata.tip_amount);
						total_amount = total_amount + tip_amount;
						vm.appointment_formdata.total_amount_with_currency = vm.bookingpress_price_with_currency_symbol( total_amount );
                    	vm.appointment_formdata.total_amount = total_amount;						
					}
				}

				<?php do_action('bookingpress_admin_modified_final_total_amount_data');  ?>
				

			},			
			bookingpress_calculate_prices(){
				const vm = this;
				var bookingpress_appointment_recalculate_data = {
					action:'bookingpress_admin_appointment_recalculate_data',
					appointment_formdata: JSON.stringify( vm.appointment_formdata ),
					appointment_extra_details: vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service],
					appointment_staff_details: vm.bookingpress_loaded_staff[vm.appointment_formdata.appointment_selected_service],
					_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
				}				
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_appointment_recalculate_data ) )
				.then(function(response) {
					vm.appointment_formdata.subtotal = response.data.appointment_formdata.subtotal;
					vm.appointment_formdata.subtotal_with_currency = response.data.appointment_formdata.subtotal_with_currency;
					vm.appointment_formdata.extras_total = response.data.appointment_formdata.extras_total;
					vm.appointment_formdata.extras_total_with_currency = response.data.appointment_formdata.extras_total_with_currency;
					if(response.data.appointment_formdata.tax != undefined){
						vm.appointment_formdata.tax_percentage = parseFloat(response.data.appointment_formdata.tax_percentage);
						vm.appointment_formdata.tax = parseFloat(response.data.appointment_formdata.tax);
						vm.appointment_formdata.tax_with_currency = response.data.appointment_formdata.tax_with_currency;
						vm.appointment_formdata.included_tax_label = response.data.appointment_formdata.included_tax_label;
					}
					vm.appointment_formdata.applied_coupon_code = response.data.appointment_formdata.applied_coupon_code;
					vm.appointment_formdata.coupon_discounted_amount = response.data.appointment_formdata.coupon_discounted_amount;
					vm.appointment_formdata.coupon_discounted_amount_with_currency = response.data.appointment_formdata.coupon_discounted_amount_with_currency;
					vm.appointment_formdata.total_amount = response.data.appointment_formdata.total_amount;
					vm.appointment_formdata.total_amount_with_currency = response.data.appointment_formdata.total_amount_with_currency;
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
			bookingpress_apply_coupon_code(){
				const vm = this;
				<?php 
					do_action('bookingpress_admin_appointment_before_apply_coupon_code');
				?>
				if(vm.bpa_coupon_apply_disabled == 0){					
					vm.coupon_apply_loader = "1"
					var bookingpress_apply_coupon_data = {};
					bookingpress_apply_coupon_data.action = "bookingpress_apply_coupon_code_backend"
					bookingpress_apply_coupon_data.coupon_code = vm.appointment_formdata.applied_coupon_code
					bookingpress_apply_coupon_data.selected_service = vm.appointment_formdata.appointment_selected_service
					bookingpress_apply_coupon_data.payable_amount = vm.appointment_formdata.total_amount
					bookingpress_apply_coupon_data._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_apply_coupon_data ) )
					.then( function (response) {
						vm.coupon_apply_loader = "0"
						vm.coupon_applied_status = response.data.variant;
						if(response.data.variant == "error"){
							vm.coupon_code_msg = response.data.msg
						}else{
							vm.coupon_code_msg = response.data.msg
							vm.appointment_formdata.coupon_discounted_amount = response.data.discounted_amount;
							vm.appointment_formdata.coupon_discounted_amount_with_currency = response.data.discounted_amount_with_currency
							vm.appointment_formdata.applied_coupon_details = response.data.coupon_data
							vm.bpa_coupon_apply_disabled = 1
						}

						//vm.bookingpress_calculate_prices();
						vm.bookingpress_admin_get_final_step_amount();
					}.bind(this) )
					.catch( function (error) {
						vm.bookingpress_set_error_msg(error)
					});
				}
			},
			bookingpress_remove_coupon_code(){
				const vm = this
				//vm.appointment_formdata.applied_coupon_code = ""
				vm.coupon_code_msg = ""
				//vm.bookingpress_calculate_prices()
				vm.bpa_coupon_apply_disabled = 0
				vm.coupon_applied_status = "error"
				vm.coupon_discounted_amount = "";
				vm.coupon_discounted_amount = "";				
				vm.appointment_formdata.coupon_discounted_amount = '';
				vm.appointment_formdata.coupon_discounted_amount_with_currency = '';
				vm.appointment_formdata.applied_coupon_details = [];					
				vm.bookingpress_admin_get_final_step_amount();
			},
			bookingpress_close_extras_modal(){
				//Trigger body click for close extras popover
				//this.bookingpress_calculate_prices();
				this.bookingpress_admin_get_final_step_amount();
				document.body.click();
			},
			bookingpress_toggle_extra_address(extra_service_id, new_status){
				const vm = this;
				vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service].forEach(function(currentValue, index, arr){
					if(currentValue.bookingpress_extra_services_id == extra_service_id){
						vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service][index]['bookingpress_is_display_description'] = parseInt(new_status);
					}
				});
			},
			bookingpress_add_extras(){
				const vm = this;
				if(vm.appointment_formdata.selected_extra_services_ids == ""){
					vm.appointment_formdata.selected_extra_services_ids = [];
				}
				vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service].forEach(function(currentValue, index, arr){
					if(currentValue.bookingpress_is_selected == true){
						if(!vm.appointment_formdata.selected_extra_services_ids.includes(currentValue.bookingpress_extra_services_id)){
							vm.appointment_formdata.selected_extra_services_ids.push(currentValue.bookingpress_extra_services_id);
						}
					}else{
						vm.appointment_formdata.selected_extra_services_ids.forEach(function(currentValue2, index2, arr2){
							if(currentValue.bookingpress_extra_services_id == currentValue2){
								vm.appointment_formdata.selected_extra_services_ids.splice(index2, 1);
							}
						});
					}
				});
				vm.bookingpress_appointment_get_disable_dates();
				//vm.bookingpress_calculate_prices();
				vm.bookingpress_admin_get_final_step_amount();
				if(vm.appointment_formdata.applied_coupon_code != ''){
					vm.bookingpress_apply_coupon_code();
				}
				<?php do_action('bookingress_backend_after_add_service_extra');?>
				vm.bookingpress_close_extras_modal();
			},
			bookingpress_change_bring_anyone(){
				const vm = this
				vm.bookingpress_appointment_get_disable_dates();
				//vm.bookingpress_calculate_prices();
				vm.bookingpress_admin_get_final_step_amount();
				if(vm.appointment_formdata.applied_coupon_code != ''){
					vm.bookingpress_apply_coupon_code();
				}
			},
			bookingpress_change_staff(){
				const vm = this
				vm.bookingpress_set_bring_anyone_capacity();
				vm.bookingpress_appointment_get_disable_dates();
				//vm.bookingpress_calculate_prices();
				vm.bookingpress_admin_get_final_step_amount();
				if(vm.appointment_formdata.applied_coupon_code != ''){
					vm.bookingpress_apply_coupon_code();
				}
			},
			bookingpress_set_bring_anyone_capacity(){
				const vm = this;
				if( 1 == vm.is_bring_anyone_with_you_enable ){
					vm.appointment_formdata.selected_bring_members = "0";
					let selected_staffmember = vm.appointment_formdata.selected_staffmember;
					if( "" != selected_staffmember ){
						let selected_service = vm.appointment_formdata.appointment_selected_service;
						let selected_service_staffmember = vm.bookingpress_loaded_staff[ selected_service ];
						let selected_staff_capacity = 1;
						let selected_staff_min_capacity = 1;
						selected_service_staffmember.forEach(function( elm ){
							if( selected_staffmember == elm.bookingpress_staffmember_id ){
								selected_staff_capacity = elm.bookingpress_service_capacity;
								selected_staff_min_capacity = elm.bookingpress_service_min_capacity;
								return false;
							}
						});
						vm.appointment_formdata.bookingpress_bring_anyone_max_capacity = parseInt(selected_staff_capacity);
						vm.appointment_formdata.bookingpress_bring_anyone_min_capacity = parseInt(selected_staff_min_capacity);
					}
				}
			},
			bookingpress_remove_extras(remove_extra_service_id){
				const vm = this;
				vm.appointment_formdata.selected_extra_services_ids.forEach(function(currentValue, index, arr){
					if(remove_extra_service_id == currentValue){
						vm.appointment_formdata.selected_extra_services_ids.splice(index, 1);
						vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service].forEach(function(currentValue2, index2, arr2){
							if(currentValue2.bookingpress_extra_services_id == remove_extra_service_id){
								vm.bookingpress_loaded_extras[vm.appointment_formdata.appointment_selected_service][index2].bookingpress_is_selected = false;
							}
						});
					}
				});
				vm.bookingpress_appointment_get_disable_dates();
				//vm.bookingpress_calculate_prices();
				vm.bookingpress_admin_get_final_step_amount();
				if(vm.appointment_formdata.applied_coupon_code != ''){
					vm.bookingpress_apply_coupon_code();
				}
				<?php do_action('bookingress_backend_after_remove_service_extra');?>
			},			
			bookingpress_select_customer(bookingpress_selected_customer){
				const vm = this;
				if(bookingpress_selected_customer == "add_new"){
					vm.open_add_customer_modal();
				} else {
					vm.bookingpress_retrieve_custom_field_values( bookingpress_selected_customer );
				}
				<?php do_action('bookingress_backend_after_select_customer'); ?>
			},
			bookingpress_retrieve_custom_field_values( selected_customer_id ){
				const vm = this;
				let postData =  {action: "bookingpress_get_customer_form_field_values", customer_id: selected_customer_id, _wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce')); ?>'};
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function(response){
					if( response.data.variant == 'success' ){
						let customer_form_fields = response.data.customer_form_fields;
						for( let field_key in customer_form_fields ){
							let field_value = customer_form_fields[ field_key ];
							if( 'undefined' != typeof vm.appointment_formdata.bookingpress_appointment_meta_fields_value[ field_key ] ){
								vm.appointment_formdata.bookingpress_appointment_meta_fields_value[ field_key ] = field_value;
							}
						}
					}
				}).catch( function(error){
					console.log( error );
				})
			},
			bookingpress_get_customers_details(selected_customer_id = ""){
				const vm = this
				var customer_details_action = { action: 'bookingpress_get_customer_details',customer_id:selected_customer_id, _wpnonce: '<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_details_action ) )
				.then(function(response){
					vm.appointment_customers_list = response.data.appointment_customers_details;
					if(selected_customer_id != ""){
						setTimeout(function(){
							vm.appointment_formdata.appointment_selected_customer = ''+selected_customer_id;
						}, 500);
					}
				}).catch(function(error){
					console.log(error)
				});				
			},
			open_add_customer_modal(){                
                const vm2 = this
				vm2.wpUsersList = [];
                vm2.resetCustomerForm()
                //vm2.get_wordpress_users()
                vm2.open_customer_modal = true
            },
			get_wordpress_users(query) {
                const vm = new Vue()
                const vm2 = this	
                if (query !== '') {
                    vm2.boookingpress_loading = true;                    
                    var customer_action = { action:'bookingpress_get_wpuser',search_user_str:query,wordpress_user_id:vm2.wordpress_user_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }                    
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
                    .then(function(response){
						vm2.boookingpress_loading = false;						
                        vm2.wpUsersList = response.data.users
                    }).catch(function(error){
                        console.log(error)
                    });
                } else {
                    vm2.wpUsersList = [];
                }	
            },
			resetCustomerForm() {                        
                const vm2 = this                
                vm2.customer.update_id = 0;
                vm2.customer.wp_user = '';
                vm2.customer.firstname = '';
                vm2.customer.lastname = '';
                vm2.customer.email = '';
                vm2.customer.phone = '';
                vm2.customer.note = '';
                vm2.customer.password = '';
                vm2.customer.avatar_list = [];
                vm2.customer.avatar_url = '';
                vm2.customer.avatar_name = '';
                vm2.customer.customer_phone_country = vm2.bookingpress_tel_input_props.defaultCountry;
				vm2.wordpress_user_id ='';
                vm2._wpnonce = '<?php wp_create_nonce('bpa_wp_nonce'); ?>';
                <?php do_action('bookingpress_reset_customer_fields_data') ?>
            },
			closeCustomerModal() {
                const vm2 = this
                vm2.$refs['customer'].resetFields()
                vm2.open_customer_modal = false
                vm2.resetCustomerForm()
				vm2.appointment_formdata.appointment_selected_customer = '';
            },
			bookingpress_upload_customer_avatar_func(response, file, fileList){
                const vm2 = this
                if(response != ''){
                    vm2.customer.avatar_url = response.upload_url
                    vm2.customer.avatar_name = response.upload_file_name
                }
            },
            bookingpress_image_upload_limit(files, fileList){
                const vm2 = this
                    if(vm2.customer.avatar_url != ''){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Multiple files not allowed', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                }
            },
            bookingpress_image_upload_err(err, file, fileList){
                const vm2 = this
                var bookingpress_err_msg = '<?php esc_html_e('Something went wrong', 'bookingpress-appointment-booking'); ?>';
                if(err != '' || err != undefined){
                    bookingpress_err_msg = err
                }
                vm2.$notify({
                    title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                    message: bookingpress_err_msg,
                    type: 'error',
                    customClass: 'error_notification',
                    duration:<?php echo intval($bookingpress_notification_duration); ?>,
                });
            },
            checkUploadedFile(file){
                const vm2 = this
                if(file.type != 'image/jpeg' && file.type != 'image/png' && file.type != 'image/webp'){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Please upload jpg/png file only', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
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
            bookingpress_remove_customer_avatar() {
                const vm = this
                var upload_url = vm.customer.avatar_url
                var upload_filename = vm.customer.avatar_name
                var postData = { action:'bookingpress_remove_uploaded_file', upload_file_url: upload_url,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                .then( function (response) {
                    vm.customer.avatar_url = ''
                    vm.customer.avatar_name = ''
                    vm.$refs.avatarRef.clearFiles()
                }.bind(vm) )
                .catch( function (error) {
                    console.log(error);
                });
            },
			saveCustomerDetails(){
                const vm2 = this
                vm2.$refs['customer'].validate((valid) => {
                    if(valid){
                        vm2.is_disabled = true
                        vm2.is_display_save_loader = '1'
                        var postdata = vm2.customer;
                        postdata.action = 'bookingpress_add_customer';
						postdata._wpnonce ='<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>';
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
                        .then(function(response){
                            vm2.is_disabled = false
                            vm2.is_display_save_loader = '0'                            
                            vm2.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                            if (response.data.variant == 'success') {
                                vm2.open_customer_modal = false
                                vm2.customer.update_id = response.data.customer_id
								vm2.bookingpress_get_customers_details(response.data.customer_id);
                            }
                            vm2.savebtnloading = false
                        }).catch(function(error){
                            vm2.is_disabled = false
                            vm2.is_display_loader = '0'
                            console.log(error);
                            vm2.$notify({
                                title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                                message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
                                type: 'error',
                                customClass: 'error_notification',
                                duration:<?php echo intval($bookingpress_notification_duration); ?>,
                            });
                        });
                    }
                })
            },
			bookingpress_phone_country_change_func(bookingpress_country_obj){
                const vm = this
                var bookingpress_selected_country = bookingpress_country_obj.iso2
				let exampleNumber = window.intlTelInputUtils.getExampleNumber( bookingpress_selected_country, true, 1 );
                if( '' != exampleNumber ){
                    vm.bookingpress_tel_input_props.inputOptions.placeholder = exampleNumber;
                }
                vm.customer.customer_phone_country = bookingpress_selected_country
				vm.customer.customer_phone_dial_code = bookingpress_country_obj.dialCode;
            },
			bookingpress_get_existing_user_details(bookingpress_selected_user_id){
                const vm = this
                if(bookingpress_selected_user_id != 'add_new') {
                    var postData = { action:'bookingpress_get_existing_users_details', existing_user_id: bookingpress_selected_user_id, _wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then( function (response) {
                        if(response.data.user_details != '' || response.data.user_details != undefined){
                            vm.customer.firstname = response.data.user_details.user_firstname
                            vm.customer.lastname = response.data.user_details.user_lastname
                            vm.customer.email = response.data.user_details.user_email
                        }
                    }.bind(vm) )
                    .catch( function (error) {
                        console.log(error);
                    });
                }
            },
			bookingpress_custom_field_date_change(event,field_meta_value,is_time_enabled) {
				/*if(event != null){
					if(is_time_enabled == 'true' ) {
						this.appointment_formdata.bookingpress_appointment_meta_fields_value[field_meta_value] = this.get_formatted_datetime(event)
					} else {
						this.appointment_formdata.bookingpress_appointment_meta_fields_value[field_meta_value] = this.get_formatted_date(event)
					}
				}*/
			},
			bpa_get_customer_formatted_date(event,field_meta_value,is_time_enabled){
				/*if(event != null){
					if(is_time_enabled == 'true' ) {
						this.customer['bpa_customer_field'][field_meta_value] = this.get_formatted_datetime(event)
					} else {
						this.customer['bpa_customer_field'][field_meta_value] = this.get_formatted_date(event)
					}
				}*/
			},
			bpa_staffmemeber_toogle_menu() {
				const vm = this;
				if(vm.bpa_toggle_active == 0) {
					vm.bpa_toggle_active = 1;
				} else {
					vm.bpa_toggle_active = 0;
				}
			},
			bpa_staffmember_open_admin_view() {
				var url = new URL(window.location.href);
				url.searchParams.set('staffmember_view', 'admin_view');
				window.location.href = url;
			},
			bookingpress_staffmember_logout(url) {
				window.location.href = url;
			},
			bookingpress_open_upcomming_appointment_model(currentElement){
				const vm = this;
				vm.staffmember_customize_notification_model = true;
				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#staffmember_customize_notification_model .el-dialog.bpa-dialog--staff-upcoming-appointment');
				}
			},
			bookingpress_close_licence_notice(){
				const vm = this
				var bookingpress_request_data = {};
				bookingpress_request_data.action = "bookingpress_dismiss_licence_notice"
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_request_data ) )
				.then( function (response) {
					vm.is_licence_activated = '';
				}.bind(this) )
				.catch( function (error) {
					console.log(error);
				});
			},
			bookingpress_close_license_expiry_notice(){
				const vm = this;
				var bookingpress_request_data = {};
				bookingpress_request_data.action = "bookingpress_dismiss_license_expiry_notice";
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_request_data ) )
				.then( function( response ) {
					vm.is_license_expired = false;
				}.bind( this ) )
				.catch( function(error){
					console.log( error );
				});
			},
			bookingpress_hide_cron_notice_for_later(){
				const vm = this;
				var bookingpress_request_data = {};
				bookingpress_request_data.action = "bookingpress_dismiss_cron_notice";
				bookingpress_request_data.type = 'later';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_request_data ) )
				.then( function( response ) {
					vm.is_display_cron_notice = false;
				}.bind( this ) )
				.catch( function(error){
					console.log( error );
				});
			},
			bookingpress_hide_cron_notice_forever(){
				const vm = this;
				var bookingpress_request_data = {};
				bookingpress_request_data.action = "bookingpress_dismiss_cron_notice";
				bookingpress_request_data.type = 'forever';
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_request_data ) )
				.then( function( response ) {
					vm.is_display_cron_notice = false;
				}.bind( this ) )
				.catch( function(error){
					console.log( error );
				});
			},
			<?php
		}

		function bookingpress_dismiss_cron_notice_func(){
			$cron_dismiss_type = !empty( $_REQUEST['type'] ) ? sanitize_text_field( $_REQUEST['type'] ) : 'later';

			update_option( 'bookingpress_dismiss_cron_notice_type', $cron_dismiss_type );
			if( 'later' == $cron_dismiss_type ){
				$bookingpress_dismiss_date = strtotime('+7 days',current_time( 'timestamp'));			
				update_option( 'bookingpress_dismiss_cron_notice', $bookingpress_dismiss_date );
			}
		}

		function bookingpress_dismiss_licence_notice_func() {			
			$bookingpress_dismiss_date = strtotime('+7 days',current_time( 'timestamp'));			
			update_option('bookingpress_dismiss_notice',$bookingpress_dismiss_date);
		}

		function bookingpress_dismiss_license_expiry_notice_func(){
			$bookingpress_dismiss_date = strtotime( '+7 days', current_time('timestamp') );
			update_option('bkp_dismiss_license_expiry_notice',$bookingpress_dismiss_date);
		}
		function bookingpress_replace_calendar_appointment_data($template_content,$bookingpress_appointment_data,$bpa_other_bookings = array()) {
    
			global $wpdb, $BookingPress, $bookingpress_spam_protection,$bookingpress_global_options,$tbl_bookingpress_categories,$tbl_bookingpress_services,$tbl_bookingpress_appointment_bookings,$tbl_bookingpress_customers,$bookingpress_pro_staff_members,$bookingpress_pro_appointment,$tbl_bookingpress_form_fields,$tbl_bookingpress_payment_logs;

			$global_data = $bookingpress_global_options->bookingpress_global_options();
			$default_time_format = $global_data['wp_default_time_format'];
			$default_date_format = $global_data['wp_default_date_format'];
			$bookingpress_appointment_status_arr = $global_data['appointment_status'];		
			$bookingpress_appointment_id       = !empty( $bookingpress_appointment_data['bookingpress_appointment_booking_id'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_booking_id'] ) : '';
			$bookingpress_appointment_date       = !empty( $bookingpress_appointment_data['bookingpress_appointment_date'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_date'] ) : '';
			$bookingpress_appointment_start_time = !empty( $bookingpress_appointment_data['bookingpress_appointment_time'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_time'] ) : '';
			$bookingpress_appointment_end_time   = !empty( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_end_time'] ) : '';
			$bookingpress_appointment_service_id   = !empty( $bookingpress_appointment_data['bookingpress_service_id'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_service_id'] ) : '';
			$bookingpress_staff_member_id = !empty( $bookingpress_appointment_data['bookingpress_staff_member_id'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_staff_member_id'] ) : '';
			$bookingpress_service_id = !empty( $bookingpress_appointment_data['bookingpress_service_id'] ) ? intval($bookingpress_appointment_data['bookingpress_service_id'] ) : '' ;
			$bookingpress_service_duration = $bookingpress_appointment_data['bookingpress_service_duration_val'];

			if( 'm' == $bookingpress_appointment_data['bookingpress_service_duration_unit'] ){
				$bookingpress_service_duration .= ' ' . esc_html__( 'Mins', 'bookingpress-appointment-booking' );
			} elseif('d' == $bookingpress_appointment_data['bookingpress_service_duration_unit']) {
				$bookingpress_service_duration .= ' ' . esc_html__( 'Days', 'bookingpress-appointment-booking' ); 
			} else {
				$bookingpress_service_duration .= ' ' . esc_html__( 'Hours', 'bookingpress-appointment-booking' ); 
			}
			$bookingpress_category_name = '';
			if(!empty($bookingpress_service_id)) {
				$bookingpress_service_data= $wpdb->get_row( $wpdb->prepare ("SELECT bookingpress_category_id FROM " . $tbl_bookingpress_services." WHERE bookingpress_service_id = %d ",$bookingpress_service_id), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
				$bookingpress_category_id = !empty($bookingpress_service_data['bookingpress_category_id']) ? intval($bookingpress_service_data['bookingpress_category_id']) : 0;                   
				if($bookingpress_category_id == 0 ) {
					$bookingpress_category_name = esc_html__('Uncategorized', 'bookingpress-appointment-booking');
				} else {                        
					$categories= $wpdb->get_row($wpdb->prepare( "SELECT bookingpress_category_name FROM " . $tbl_bookingpress_categories." WHERE bookingpress_category_id = %d",$bookingpress_category_id), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
					$bookingpress_category_name = !empty($categories['bookingpress_category_name']) ? esc_html($categories['bookingpress_category_name']): '';
				}  				
			} 

			$is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_staffmember_email = $bookingpress_staffmember_firstname = $bookingpress_staffmember_fullname = $bookingpress_staffmember_lastname =
			$bookingpress_staffmember_phone = '';		
			if ( $is_staffmember_module_activated ) {
				$bookingpress_staffmember_data = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'],true) : array();
				$bookingpress_staffmember_firstname = !empty($bookingpress_staffmember_data['bookingpress_staffmember_firstname']) ? esc_html($bookingpress_staffmember_data['bookingpress_staffmember_firstname']) : '';
				$bookingpress_staffmember_lastname = !empty($bookingpress_staffmember_data['bookingpress_staffmember_lastname']) ? esc_html($bookingpress_staffmember_data['bookingpress_staffmember_lastname']) : '';
				$bookingpress_staffmember_email = !empty($bookingpress_staffmember_data['bookingpress_staffmember_email']) ? esc_html($bookingpress_staffmember_data['bookingpress_staffmember_email']) : '';
				$bookingpress_staffmember_fullname = $bookingpress_staffmember_firstname.' '.$bookingpress_staffmember_lastname;
				$bookingpress_staffmember_phone = !empty($bookingpress_staffmember_data['bookingpress_staffmember_phone']) ? esc_html($bookingpress_staffmember_data['bookingpress_staffmember_phone']) : '';                                                          
				if(!empty($bookingpress_staffmember_data['bookingpress_staffmember_country_dial_code'])) {					
					$bookingpress_staffmember_phone = "+".$bookingpress_staffmember_data['bookingpress_staffmember_country_dial_code']." ".$bookingpress_staffmember_phone;
				}					
			}

			if(!empty($bpa_other_bookings)) {		
				$bookingpress_ap_booking_id =  $bookingpress_ap_customer_email = $bookingpress_ap_customer_firstname = $bookingpress_ap_customer_lastname = $bookingpress_ap_customer_fullname = $bookingpress_ap_customer_phone = $bookingpress_ap_customer_note = $bookingpress_ap_service_name = $bookingpress_ap_service_price = $bookingpress_ap_service_extra = $bookingpress_ap_status = $bookingpress_ap_amount =  $bookingpress_ap_discount_amount = $bookingpress_ap_coupon_code = $bookingpress_ap_tax_amount = $bookingpress_ap_due_amount = $custom_field_data = $bookingpress_ap_number_of_person = $bookingpress_ap_payment_method = array(); 
		
				foreach($bpa_other_bookings as $key => $value) {
					$bookingpress_appointment_data = $value;
					if(!empty($bookingpress_appointment_data)) {
						/* replacing the appointment data */
						$bookingpress_booking_id   = !empty( $bookingpress_appointment_data['bookingpress_booking_id'] ) ? sanitize_text_field( $bookingpress_appointment_data['bookingpress_booking_id'] ) : '';
						
						$bookingpress_customer_id       = !empty( $bookingpress_appointment_data['bookingpress_customer_id'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_customer_id'] ) : '';
						$bookingpress_customer_email = !empty( $bookingpress_appointment_data['bookingpress_customer_email'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_customer_email'] ) : '';
						$bookingpress_customer_firstname   = !empty( $bookingpress_appointment_data['bookingpress_customer_firstname'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_customer_firstname'] ) : '';
						$bookingpress_customer_lastname   = !empty( $bookingpress_appointment_data['bookingpress_customer_lastname'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_customer_lastname'] ) : '';
						$bookingpress_customer_fullname   = !empty( $bookingpress_appointment_data['bookingpress_customer_name'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_customer_name'] ) : '';
						$bookingpress_customer_phone   = !empty( $bookingpress_appointment_data['bookingpress_customer_phone'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_customer_phone'] ) : '';
						$bookingpress_customer_note   = !empty( $bookingpress_appointment_data['bookingpress_appointment_internal_note'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_appointment_internal_note'] ) : '';
						$bookingpress_number_of_person = !empty( $bookingpress_appointment_data['bookingpress_selected_extra_members'] ) ? esc_html( $bookingpress_appointment_data['bookingpress_selected_extra_members'] ) : '';

						$bookingpress_appointment_booking_id   = !empty( $bookingpress_appointment_data['bookingpress_appointment_booking_id'] ) ? intval( $bookingpress_appointment_data['bookingpress_appointment_booking_id'] ) : '';

						$log_data = array();
						if (!empty($bookingpress_appointment_booking_id) && $bookingpress_appointment_booking_id != 0) {
							$log_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_payment_gateway FROM " . $tbl_bookingpress_payment_logs . " WHERE `bookingpress_appointment_booking_ref`= %d",$bookingpress_appointment_booking_id),ARRAY_A); //phpcs:ignore
						}
						$bookingpress_payment_method = !empty($log_data['bookingpress_payment_gateway']) ? $log_data['bookingpress_payment_gateway'] : '';
						if(!empty($bookingpress_payment_method) && $bookingpress_payment_method == 'on-site' ) {
							$bookingpress_payment_method = $BookingPress->bookingpress_get_customize_settings('locally_text','booking_form');
						} elseif(!empty($bookingpress_payment_method) && $bookingpress_payment_method != 'manual') {
							$bookingpress_payment_method = $BookingPress->bookingpress_get_customize_settings($bookingpress_payment_method.'_text','booking_form');
						}

						if(!empty($bookingpress_appointment_data['bookingpress_customer_phone_dial_code'])){
							$bookingpress_customer_phone = "+".$bookingpress_appointment_data['bookingpress_customer_phone_dial_code']." ".$bookingpress_customer_phone;
						}
						$bookingpress_appointment_status = !empty($bookingpress_appointment_data['bookingpress_appointment_status']) ? intval($bookingpress_appointment_data['bookingpress_appointment_status']) : '' ;		
						if(!empty($bookingpress_appointment_status) && $bookingpress_appointment_status != 3 && $bookingpress_appointment_status != 4)  {            
							$bpa_unique_id = !empty($bookingpress_appointment_data['bookingpress_appointment_token']) ? $bookingpress_appointment_data['bookingpress_appointment_token'] : '';
							if(empty($bpa_unique_id)) {
								$bpa_unique_id = $BookingPress->bookingpress_generate_token();                        
								$wpdb->update($tbl_bookingpress_appointment_bookings,array('bookingpress_appointment_token' => $bpa_unique_id),array('bookingpress_appointment_booking_id' => $bookingpress_appointment_booking_id));
							}
							$appointment_cancellation_confirmation = $BookingPress->bookingpress_get_customize_settings('appointment_cancellation_confirmation','booking_my_booking');                    
							if(!empty($appointment_cancellation_confirmation)){
								$bookingpress_appointment_cancellation_confirmation_url = get_permalink($appointment_cancellation_confirmation);                       
							}
							$bookingpress_cancel_appointment_link = !empty($bookingpress_appointment_cancellation_confirmation_url) ? $bookingpress_appointment_cancellation_confirmation_url :BOOKINGPRESS_HOME_URL;
							$bookingpress_cancel_appointment_link    = add_query_arg('appointment_id', base64_encode($bookingpress_appointment_booking_id), $bookingpress_cancel_appointment_link);
							$bookingpress_cancel_appointment_link = add_query_arg( 'cancel_token',$bpa_unique_id, $bookingpress_cancel_appointment_link );
							$bookingpress_cancel_ap_link[] = $bookingpress_cancel_appointment_link;
						}
						$bookingpress_service_name = !empty($bookingpress_appointment_data['bookingpress_service_name']) ? esc_html($bookingpress_appointment_data['bookingpress_service_name']) : '';	

						$bookingpress_currency = !empty($bookingpress_appointment_data['bookingpress_service_currency']) ? esc_html($bookingpress_appointment_data['bookingpress_service_currency']) : '';                
						$bookingpress_currency_symbol = $BookingPress->bookingpress_get_currency_symbol($bookingpress_currency);
						
						$bookingpress_extra_serive_details_arr = !empty($bookingpress_appointment_data['bookingpress_extra_service_details']) ? json_decode( $bookingpress_appointment_data['bookingpress_extra_service_details'],true) : array();
						
						if(!empty( $bookingpress_extra_serive_details_arr)){
							$bookingpress_service_extra_content = "<table border='1' cellpadding='10' cellspacing='0' style='border-color:#ccc'>";
							foreach( $bookingpress_extra_serive_details_arr as $extra_service_key=>$extra_service_val ){

								$bookingpress_extra_service_name = !empty($extra_service_val['bookingpress_extra_service_details']['bookingpress_extra_service_name']) ? esc_html($extra_service_val['bookingpress_extra_service_details']['bookingpress_extra_service_name']) : '';

								$bookingpress_extra_service_qty = !empty($extra_service_val['bookingpress_selected_qty']) ? intval($extra_service_val['bookingpress_selected_qty']) : '';

								$bookingpress_extra_service_price = !empty($extra_service_val['bookingpress_final_payable_price'])?floatval($extra_service_val['bookingpress_final_payable_price']) :''; 

								$bookingpress_service_price_with_currency = ! empty($bookingpress_extra_service_price) ? $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_extra_service_price, $bookingpress_currency_symbol) : 0;	
									
									$bookingpress_service_extra_content .= "<tr>";
										$bookingpress_service_extra_content .= "<td>".$bookingpress_extra_service_name." </td>";
										$bookingpress_service_extra_content .= "<td>".$bookingpress_extra_service_qty."</td>";
										$bookingpress_service_extra_content .= "<td>".$bookingpress_service_price_with_currency."</td>";
									$bookingpress_service_extra_content .= "</tr>";
							}
							$bookingpress_service_extra_content .= "</table>";

						} else {
							$bookingpress_service_extra_content = '';
						}
						$bookingpress_service_price = ! empty($bookingpress_appointment_data['bookingpress_service_price']) ? $BookingPress->bookingpress_price_formatter_with_currency_symbol($bookingpress_appointment_data['bookingpress_service_price'], $bookingpress_currency_symbol) : 0;
						$bookingpress_appointment_id = !empty($bookingpress_appointment_data['bookingpress_appointment_booking_id']) ? intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']) : 0;				
						$bookingpress_payment_log_id = !empty($bookingpress_appointment_data['bookingpress_payment_id']) ? intval($bookingpress_appointment_data['bookingpress_payment_id']) : 0;
						$bookingpress_appointment_details = $bookingpress_pro_appointment->bookingpress_calculated_appointment_details($bookingpress_appointment_id, $bookingpress_payment_log_id);	
						$bookingpress_appointment_amount = !empty($bookingpress_appointment_details['final_total_amount_with_currency']) ? $bookingpress_appointment_details['final_total_amount_with_currency'] : '-';		
						foreach($bookingpress_appointment_status_arr as $bookingpress_appointment_status_key => $bookingpress_appointment_status_vals){
							if($bookingpress_appointment_status_vals['value'] == $bookingpress_appointment_status){
								$bookingpress_appointment_status = $bookingpress_appointment_status_vals['text'];
								break;
							}
						}

						$bookingpress_deposit_amount = !empty($bookingpress_appointment_details['deposit_price_with_currency']) ? $bookingpress_appointment_details['deposit_price_with_currency'] : 0;

						$bookingpress_due_amount = !empty($bookingpress_appointment_details['due_amount_with_currency']) ? $bookingpress_appointment_details['due_amount_with_currency'] : '-';
						$bookingpress_tax_amount = !empty($bookingpress_appointment_details['bookingpress_tax_amount_with_currency']) ? $bookingpress_appointment_details['bookingpress_tax_amount_with_currency'] : '-';
						$bookingpress_discount_amount = !empty($bookingpress_appointment_details['coupon_discount_amt_with_currency']) ? $bookingpress_appointment_details['coupon_discount_amt_with_currency'] : '-';		
						$bookingpress_coupon_code = !empty($bookingpress_appointment_details['applied_coupon']) ? $bookingpress_appointment_details['applied_coupon'] : '-';

						$bookingpress_appointment_custom_fields_meta_values = $bookingpress_pro_appointment->bookingpress_get_appointment_form_field_data($bookingpress_appointment_id);

						$bookingpress_custom_form_fields = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_field_type,bookingpress_field_options,bookingpress_field_values,bookingpress_field_meta_key FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_is_default = %d AND bookingpress_is_customer_field = %d AND bookingpress_field_type != %s AND bookingpress_field_type != %s AND bookingpress_field_type != %s", 0,0,'2_col','3_col','4_col'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm

						if(!empty($bookingpress_custom_form_fields)){
							foreach($bookingpress_custom_form_fields as $k2 => $v2) {						
								if(!empty($v2['bookingpress_field_values']) && !empty($bookingpress_appointment_custom_fields_meta_values[$v2['bookingpress_field_meta_key']])) {

									if($v2['bookingpress_field_type'] == 'date' && !empty($v2['bookingpress_field_options'])) {
										$bookingpress_field_options = json_decode($v2['bookingpress_field_options'],true);

										if(!empty($bookingpress_field_options['enable_timepicker']) && $bookingpress_field_options['enable_timepicker'] == 'true') {
											$default_date_time_format = $default_date_format.' '.$default_time_format;  
											$bookingpress_appointment_custom_fields_meta_values[$v2['bookingpress_field_meta_key']]= date($default_date_time_format,strtotime($bookingpress_appointment_custom_fields_meta_values[$v2['bookingpress_field_meta_key']]));
										} else {
											$bookingpress_appointment_custom_fields_meta_values[$v2['bookingpress_field_meta_key']] = date($default_date_format,strtotime($bookingpress_appointment_custom_fields_meta_values[$v2['bookingpress_field_meta_key']]));
										}
									}
									if( is_array( $bookingpress_appointment_custom_fields_meta_values[$v2['bookingpress_field_meta_key']] ) ){
										$bookingpress_appointment_custom_fields_meta_values[$v2['bookingpress_field_meta_key']] = implode( ',', $bookingpress_appointment_custom_fields_meta_values[$v2['bookingpress_field_meta_key']]);
									}	
									$custom_field_data[$v2['bookingpress_field_meta_key']][] = $bookingpress_appointment_custom_fields_meta_values[$v2['bookingpress_field_meta_key']];
								}else{
									$custom_field_data[$v2['bookingpress_field_meta_key']][] = '';
								}
							}		
						}

						$bookingpress_ap_booking_id[] = "#".$bookingpress_booking_id;                
						$bookingpress_ap_customer_email[] = $bookingpress_customer_email;
						$bookingpress_ap_customer_firstname[] = $bookingpress_customer_firstname;
						$bookingpress_ap_customer_fullname[] = $bookingpress_customer_fullname;
						$bookingpress_ap_customer_lastname[] = $bookingpress_customer_lastname;
						$bookingpress_ap_customer_phone[] = $bookingpress_customer_phone;
						$bookingpress_ap_customer_note[] = $bookingpress_customer_note;
						$bookingpress_ap_service_name[] = $bookingpress_service_name;
						$bookingpress_ap_service_price[] = $bookingpress_service_price;
						$bookingpress_ap_amount[] = $bookingpress_appointment_amount;
						$bookingpress_ap_status[] = $bookingpress_appointment_status;
						$bookingpress_ap_tax_amount[] = $bookingpress_tax_amount;
						$bookingpress_ap_discount_amount[] = $bookingpress_discount_amount;
						$bookingpress_ap_coupon_code[] = $bookingpress_coupon_code;
						$bookingpress_ap_deposit_amount[] = $bookingpress_deposit_amount;
						$bookingpress_ap_due_amount[] = $bookingpress_due_amount;
						$bookingpress_ap_number_of_person[] = $bookingpress_number_of_person;
						$bookingpress_ap_payment_method[] = $bookingpress_payment_method;
						$bookingpress_ap_service_extra[] = $bookingpress_service_extra_content;
						
					}
				}
				
				$bookingpress_appointment_s_time = $bookingpress_appointment_start_time;
				$bookingpress_appointment_e_time =  $bookingpress_appointment_end_time;
				$bookingpress_appointment_start_time = date($default_time_format, strtotime($bookingpress_appointment_start_time));
				$bookingpress_appointment_end_time = date($default_time_format, strtotime($bookingpress_appointment_end_time));
				$bookingpress_appointment_date = date_i18n($default_date_format, strtotime($bookingpress_appointment_date));

				$bookingpress_appointment_duration = $bookingpress_service_duration;
				if('d' != $bookingpress_appointment_data['bookingpress_service_duration_unit']) {					
					$bookingpress_tmp_start_time = new DateTime($bookingpress_appointment_s_time);
					$bookingpress_tmp_end_time = new DateTime($bookingpress_appointment_e_time);
					$booking_date_interval = $bookingpress_tmp_start_time->diff($bookingpress_tmp_end_time);
					$bookingpress_minute = $booking_date_interval->format('%i');
					$bookingpress_hour = $booking_date_interval->format('%h');  
					$bookingpress_days = $booking_date_interval->format('%d');
					$bookingpress_appointment_duration = '';
					if($bookingpress_minute > 0) {
						$bookingpress_appointment_duration = $bookingpress_minute.' ' . esc_html__('Mins', 'bookingpress-appointment-booking'); 
					}
					if($bookingpress_hour > 0 ) {
						$bookingpress_appointment_duration = $bookingpress_hour.' ' . esc_html__('Hours', 'bookingpress-appointment-booking').' '.$bookingpress_appointment_duration;
					}
					if($bookingpress_days == 1) {
						$bookingpress_appointment_duration = '24 ' . esc_html__('Hours', 'bookingpress-appointment-booking');
					}
				}

				$company_name    = esc_html($BookingPress->bookingpress_get_settings('company_name', 'company_setting'));
				$company_address = esc_html($BookingPress->bookingpress_get_settings('company_address', 'company_setting'));
				$company_phone   = esc_html($BookingPress->bookingpress_get_settings('company_phone_number', 'company_setting'));
				$company_website = $BookingPress->bookingpress_get_settings('company_website', 'company_setting');
		
				$template_content = str_replace('%booking_id%', implode(',',$bookingpress_ap_booking_id), $template_content);
				$template_content = str_replace('%appointment_date%', $bookingpress_appointment_date, $template_content);
				$template_content = str_replace('%appointment_time%',$bookingpress_appointment_start_time,$template_content);
				$template_content = str_replace('%customer_email%', implode(',',$bookingpress_ap_customer_email), $template_content);
				$template_content = str_replace('%customer_first_name%', implode(',',$bookingpress_ap_customer_firstname), $template_content);
				$template_content = str_replace('%customer_full_name%', implode(',',$bookingpress_ap_customer_fullname), $template_content);
				$template_content = str_replace('%customer_last_name%', implode(',',$bookingpress_ap_customer_lastname), $template_content);
				$template_content = str_replace('%customer_phone%', implode(',',$bookingpress_ap_customer_phone), $template_content);
				$template_content = str_replace('%customer_note%', implode(',',$bookingpress_ap_customer_note), $template_content);       
				$template_content = str_replace('%customer_cancel_appointment_link%', implode(',',$bookingpress_cancel_ap_link), $template_content);
				$template_content = str_replace('%service_name%', implode(',',$bookingpress_ap_service_name), $template_content);
				$template_content = str_replace('%service_extras%', implode(',',$bookingpress_ap_service_extra), $template_content);
				$template_content = str_replace('%service_amount%', implode(',',$bookingpress_ap_service_price), $template_content);				
				$template_content = str_replace('%appointment_amount%', implode(',',$bookingpress_ap_amount), $template_content );
				$template_content = str_replace('%appointment_status%', implode(',',$bookingpress_ap_status), $template_content );	
				$template_content = str_replace('%appointment_due_amount%',implode(',',$bookingpress_ap_due_amount), $template_content);
				$template_content = str_replace('%tax_amount%',implode(',',$bookingpress_ap_tax_amount), $template_content);	
				$template_content = str_replace('%number_of_person%',implode(',',$bookingpress_ap_number_of_person), $template_content);
				$template_content = str_replace('%payment_method%',implode(',',$bookingpress_ap_payment_method), $template_content);				
				$template_content = str_replace('%discount_amount%', implode(',',$bookingpress_ap_discount_amount), $template_content);
				$template_content = str_replace('%coupon_code%', implode(',', $bookingpress_ap_coupon_code),$template_content);
				$template_content = str_replace('%deposit_amount', implode(',',$bookingpress_ap_deposit_amount),$template_content);

				$template_content = str_replace('%service_duration%', $bookingpress_service_duration, $template_content);			
				$template_content = str_replace('%category_name%', $bookingpress_category_name, $template_content );
				$template_content = str_replace('%company_address%', $company_address, $template_content);
				$template_content = str_replace('%company_name%', $company_name, $template_content);
				$template_content = str_replace('%company_phone%', $company_phone, $template_content);
				$template_content = str_replace('%company_website%', $company_website, $template_content);				
				$template_content = str_replace('%appointment_date_time%', $bookingpress_appointment_date.'  '.$bookingpress_appointment_start_time, $template_content );
				$template_content = str_replace('%appointment_duration%', $bookingpress_appointment_duration, $template_content );
				$template_content = str_replace('%appointment_start_time%',$bookingpress_appointment_start_time, $template_content );
				$template_content = str_replace('%appointment_end_time%',$bookingpress_appointment_end_time, $template_content );
				$template_content = str_replace('%staff_member_email%', $bookingpress_staffmember_email, $template_content );
				$template_content = str_replace('%staff_member_first_name%', $bookingpress_staffmember_firstname, $template_content );
				$template_content = str_replace('%staff_member_full_name%', $bookingpress_staffmember_fullname, $template_content );
				$template_content = str_replace('%staff_member_last_name%', $bookingpress_staffmember_lastname, $template_content );
				$template_content = str_replace('%staff_member_phone%', $bookingpress_staffmember_phone, $template_content );				
				$template_content = str_replace('%staff_email%',$bookingpress_staffmember_email, $template_content );
				$template_content = str_replace('%staff_first_name%',$bookingpress_staffmember_firstname, $template_content );
				$template_content = str_replace('%staff_full_name%',$bookingpress_staffmember_fullname, $template_content );
				$template_content = str_replace('%staff_last_name%',$bookingpress_staffmember_lastname, $template_content );
				$template_content = str_replace('%staff_phone%', $bookingpress_staffmember_phone, $template_content );							

				foreach($custom_field_data as $k => $v ) {
					$template_content = str_replace( '%'.$k.'%', implode(',',$v), $template_content );					
				}	
			}
			
			$template_content = apply_filters( 'bookingpress_modify_email_content_filter', $template_content, $bookingpress_appointment_data);

			return $template_content;
		}	
		
				
				
		/**
		 * bpa_check_staff_authenticity_for_appointment
		 *
		 * @param  mixed $appointment_update_id
		 * @param  mixed $action
		 * @return void
		 */
		function bpa_check_staff_authenticity_for_appointment( $appointment_update_id, $action ){
			global $bookingpress_pro_staff_members;
			$return = array(
				'variant' => 'success',
				'response' => true,
				'message' => ''
			);
			$current_logged_in_user = get_current_user_id();

			if( empty( $current_logged_in_user ) || empty( $action )){
				$return = array(
					'variant' => 'error',
					'response' => false,
					'message' => esc_html__( 'Sorry, you are not allowed to perform this action.', 'bookingpress-appointment-booking')
				);
			} else if ( $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() ){
				$user_obj = new WP_User( $current_logged_in_user );
				$roles = $user_obj->roles;
				if( empty( $roles ) ){
					$return = array(
						'variant' => 'error',
						'response' => false,
						'message' => esc_html__( 'Sorry, you are not allowed to perform this action.', 'bookingpress-appointment-booking')
					);
				} else {
					if( in_array( 'administrator', $roles ) ){
						return $return;
					}else if( in_array( 'bookingpress-staffmember', $roles ) ){
						global $wpdb, $tbl_bookingpress_appointment_bookings, $tbl_bookingpress_staffmembers, $BookingPress;

						$get_staff_id = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d AND bookingpress_staffmember_status = %d ORDER BY bookingpress_staffmember_created DESC", $current_logged_in_user, 1) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm

						if( empty( $get_staff_id ) ){
							$return = array(
								'variant' => 'error',
								'response' => false,
								'message' => esc_html__( 'Sorry, you are not allowed to perform this action.', 'bookingpress-appointment-booking')
							);

							return  $return;
						}	

						$staff_member_id = $get_staff_id->bookingpress_staffmember_id;
						
						$is_staff_matched = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_appointment_booking_id) FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d AND bookingpress_staff_member_id = %d", $appointment_update_id, $staff_member_id ) );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

						
						if( 1 > $is_staff_matched  ){
							$return = array(
								'variant' => 'error',
								'response' => false,
								'message' => esc_html__( 'Sorry, you are not allowed to perform this action.', 'bookingpress-appointment-booking')
							);
							
							return  $return;
						}
						
						if( 'change_appointment_status' == $action ){
							$allow_capabilities = $BookingPress->bookingpress_get_settings( 'bookingpress_edit_appointments', 'staffmember_setting');
							if( false == $allow_capabilities || 'false' == $allow_capabilities ){
								$return = array(
									'variant' => 'error',
									'response' => false,
									'message' => esc_html__( 'Sorry, you are not allowed to perform this action.', 'bookingpress-appointment-booking')
								);
								
								return  $return;
							}
						}
					
					} else {
						$return = array(
							'variant' => 'error',
							'response' => false,
							'message' => esc_html__( 'Sorry, you are not allowed to perform this action.', 'bookingpress-appointment-booking')
						);
					}
				}
			}

			return  $return;
		}
	}
}
global $BookingPressPro, $bookingpress_debug_integration_log_id, $bookingpress_other_debug_log_id;
$BookingPressPro                       = new BookingPressPro();
$bookingpress_debug_integration_log_id = 0;
$bookingpress_other_debug_log_id       = 0;
