<?php
if ( ! class_exists( 'bookingpress_deposit_payment' ) ) {
	class bookingpress_deposit_payment Extends BookingPress_Core {
		function __construct() {
			if ( $this->bookingpress_check_deposit_payment_module_activation() ) {
				add_filter( 'bookingpress_modify_service_data_fields', array( $this, 'bookingpress_modify_service_data_fields_func' ) );
				add_filter( 'bookingpress_after_add_update_service', array( $this, 'bookingpress_save_service_details' ), 10, 3 );
				add_action( 'bookingpress_edit_service_more_vue_data', array( $this, 'bookingpress_edit_service_more_vue_data_func' ) );

				//After selecting service change service deposit price at frontside
				add_filter('bookingpress_after_selecting_booking_service', array($this, 'bookingpress_after_selecting_booking_service_func'), 11, 1);
				add_action('wp_ajax_bookingpress_get_deposit_amount', array($this, 'bookingpress_get_deposit_amount_func'));
				add_action('wp_ajax_nopriv_bookingpress_get_deposit_amount', array($this, 'bookingpress_get_deposit_amount_func'));

				add_filter('bookingpress_customize_add_dynamic_data_fields',array($this,'bookingpress_customize_add_dynamic_data_fields_func'),10);
                add_filter('bookingpress_get_booking_form_customize_data_filter',array($this, 'bookingpress_get_booking_form_customize_data_filter_func'),10,1);
				
				add_filter('bookingpress_frontend_apointment_form_add_dynamic_data',array($this, 'bookingpress_frontend_apointment_form_add_dynamic_data_func'),10,1);
				
				add_action('bookingpress_payment_settings_section',array($this,'bookingpress_add_payment_settings_section_func'),11);
				add_filter('bookingpress_add_setting_dynamic_data_fields',array($this,'bookingpress_add_setting_dynamic_data_fields_func'));
				add_filter( 'bookingpress_add_global_option_data', array( $this, 'bookingpress_add_global_option_data_func' ), 11 );

				if(is_plugin_active('bookingpress-multilanguage/bookingpress-multilanguage.php')) {
					add_filter('bookingpress_modified_language_translate_fields',array($this,'bookingpress_modified_language_translate_fields_func'),10);
                	add_filter('bookingpress_modified_customize_form_language_translate_fields',array($this,'bookingpress_modified_language_translate_fields_func'),10);
				}
            }
			add_action('bookingpress_before_activate_bookingpress_module',array($this,'bookingpress_before_activate_bookingpress_module_func'));
        }

		function bookingpress_modified_language_translate_fields_func($bookingpress_all_language_translation_fields){

			$bookingpress_deposite_payment_language_translation_fields = array(                
				'deposit_heading_title' => array('field_type'=>'text','field_label'=>__('Deposit heading title', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),               
				'deposit_paying_amount_title' => array('field_type'=>'text','field_label'=>__('Deposit payment amount title', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),    
				'deposit_remaining_amount_title' => array('field_type'=>'text','field_label'=>__('Deposit remaining amount title', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),      
				'deposit_title' => array('field_type'=>'text','field_label'=>__('Deposit title', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),    
				'full_payment_title' => array('field_type'=>'text','field_label'=>__('Full payment title', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),    
			);  
			$bookingpress_all_language_translation_fields['customized_form_summary_step_labels'] = array_merge($bookingpress_all_language_translation_fields['customized_form_summary_step_labels'], $bookingpress_deposite_payment_language_translation_fields);
			return $bookingpress_all_language_translation_fields;
		}

		/**
		 * bookingpress_add_global_option_data_func
		 *
		 * @param  mixed $global_data
		 * @return void
		 */
		function bookingpress_add_global_option_data_func($global_data){

			$deposite_module_activate = $this->bookingpress_check_deposit_payment_module_activation();
			if( $deposite_module_activate == 1){

				$bookingpress_email_appointment_placeholders = json_decode($global_data['appointment_placeholders'], TRUE);
				$bookingpress_email_appointment_placeholders[] = array(
					'value' => '%deposit_amount%',
					'name' => '%deposit_amount%',
				);
				$global_data['appointment_placeholders'] = wp_json_encode($bookingpress_email_appointment_placeholders);
			}
			
            return $global_data;
        }
        function bookingpress_add_setting_dynamic_data_fields_func($bookingpress_dynamic_setting_data_fields) {            
            $bookingpress_dynamic_setting_data_fields['payment_setting_form']['bookingpress_allow_customer_to_pay'] = 'deposit_or_full_price';
            return $bookingpress_dynamic_setting_data_fields;            
        }
        
        function bookingpress_add_payment_settings_section_func() {
            ?>
            <div class="bpa-gs__cb--item">
                <div class="bpa-gs__cb--item-heading">
                    <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Deposit Payment Settings', 'bookingpress-appointment-booking'); ?></h4>
                </div>
                <div class="bpa-gs__cb--item-body">
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">                        
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Allow Customers To Pay', 'bookingpress-appointment-booking'); ?></h4>
                        </el-col>
						<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16" class="bpa-gs__cb-item-right bpa-modal-radio-controls">
							<el-radio v-model="payment_setting_form.bookingpress_allow_customer_to_pay" label="deposit_or_full_price"><?php esc_html_e( 'Deposit Only', 'bookingpress-appointment-booking' ); ?></el-radio>
							<el-radio v-model="payment_setting_form.bookingpress_allow_customer_to_pay" label="allow_customer_to_pay_full_amount"><?php esc_html_e( 'Allow Customer To pay Full Amount', 'bookingpress-appointment-booking' ); ?></el-radio>
						</el-col>                      
                    </el-row>
                </div>                                                            
            </div>    
            <?php
        }

		function bookingpress_frontend_apointment_form_add_dynamic_data_func($bookingpress_front_vue_data_fields){
			global $BookingPress;
			$deposit_paying_amount_title = $BookingPress->bookingpress_get_customize_settings('deposit_paying_amount_title', 'booking_form');
			$deposit_heading_title = $BookingPress->bookingpress_get_customize_settings('deposit_heading_title', 'booking_form');
			$deposit_remaining_amount_title = $BookingPress->bookingpress_get_customize_settings('deposit_remaining_amount_title', 'booking_form');
			$deposit_title = $BookingPress->bookingpress_get_customize_settings('deposit_title', 'booking_form');
			$full_payment_title = $BookingPress->bookingpress_get_customize_settings('full_payment_title', 'booking_form');						

			$bookingpress_front_vue_data_fields['deposit_paying_amount_title'] = !empty($deposit_paying_amount_title) ? stripslashes_deep($deposit_paying_amount_title) : '';		
			$bookingpress_front_vue_data_fields['deposit_heading_title'] = !empty($deposit_heading_title) ? stripslashes_deep($deposit_heading_title) : '';			
			$bookingpress_front_vue_data_fields['deposit_remaining_amount_title'] = !empty($deposit_remaining_amount_title) ? stripslashes_deep($deposit_remaining_amount_title) : '';
			$bookingpress_front_vue_data_fields['deposit_title'] = !empty($deposit_title) ? stripslashes_deep($deposit_title) : '';
			$bookingpress_front_vue_data_fields['full_payment_title'] = !empty($full_payment_title) ? stripslashes_deep($full_payment_title) : '';
			return $bookingpress_front_vue_data_fields;
		}
			
		function bookingpress_customize_add_dynamic_data_fields_func($bookingpress_customize_vue_data_fields) {
            $bookingpress_customize_vue_data_fields['summary_container_data']['deposit_paying_amount_title'] = '';
            $bookingpress_customize_vue_data_fields['summary_container_data']['deposit_remaining_amount_title'] = '';
            $bookingpress_customize_vue_data_fields['summary_container_data']['deposit_heading_title'] = '';
			$bookingpress_customize_vue_data_fields['summary_container_data']['deposit_title'] = '';
			$bookingpress_customize_vue_data_fields['summary_container_data']['full_payment_title'] = '';			

			return $bookingpress_customize_vue_data_fields;
		}

		function bookingpress_get_booking_form_customize_data_filter_func($booking_form_settings){
            $booking_form_settings['summary_container_data']['deposit_paying_amount_title'] = __('Deposit(Paying Now)','bookingpress-appointment-booking');
            $booking_form_settings['summary_container_data']['deposit_remaining_amount_title'] = __('Remaining Amount', 'bookingpress-appointment-booking');
            $booking_form_settings['summary_container_data']['deposit_heading_title'] = __('Deposit Payment', 'bookingpress-appointment-booking');			
			$booking_form_settings['summary_container_data']['deposit_title'] = __('Deposit', 'bookingpress-appointment-booking');			
			$booking_form_settings['summary_container_data']['full_payment_title'] = __('Full Payment', 'bookingpress-appointment-booking');			
			return $booking_form_settings;
		}
		function bookingpress_get_deposit_amount_func(){
			global $wpdb, $BookingPress, $bookingpress_services;
			$response              = array();
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_verify_nonce_flag = wp_verify_nonce($wpnonce, 'bpa_wp_nonce');
            if (! $bpa_verify_nonce_flag ) {
                $response['variant']      = 'error';
                $response['title']        = esc_html__('Error', 'bookingpress-appointment-booking');
                $response['msg']          = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-appointment-booking');
                echo wp_json_encode($response);
                die();
            }
            $response['variant']    = 'success';
            $response['title']      = '';
            $response['msg']        = '';
			
			if( !empty( $_POST['appointment_data'] ) && !is_array( $_POST['appointment_data'] ) ){
				$_POST['appointment_data'] = json_decode( stripslashes_deep( $_POST['appointment_data'] ), true ); //phpcs:ignore
				$_POST['appointment_data'] =  !empty($_POST['appointment_data']) ? array_map(array($this,'bookingpress_boolean_type_cast'), $_POST['appointment_data'] ) : array(); // phpcs:ignore   
			}

			$bookingpress_deposit_type = "";
			$bookingpress_deposit_val = "";
			$bookingpress_appointment_data = !empty($_POST['appointment_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['appointment_data']) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason: $_POST['appointment_data'] has already been sanitized
			if(!empty($bookingpress_appointment_data)){
				$bookingpress_selected_service = !empty($bookingpress_appointment_data['selected_service']) ? intval($bookingpress_appointment_data['selected_service']) : 0;
				if(!empty($bookingpress_selected_service)){
					$bookingpress_deposit_type = $bookingpress_services->bookingpress_get_service_meta($bookingpress_selected_service, 'deposit_type');
					$bookingpress_deposit_val = $bookingpress_services->bookingpress_get_service_meta($bookingpress_selected_service, 'deposit_amount');
				}
			}
			
			$response['deposit_type'] = $bookingpress_deposit_type;
			$response['deposit_val'] = floatval($bookingpress_deposit_val);

			echo wp_json_encode($response);
			exit;
		}

		function bookingpress_after_selecting_booking_service_func($bookingpress_after_selecting_booking_service_data){
			$bookingpress_nonce             = wp_create_nonce( 'bpa_wp_nonce' );

			$bookingpress_after_selecting_booking_service_data .= '
				let current_service_data = vm.bookingpress_all_services_data[selected_service];				
				if( "undefined" != typeof current_service_data && "undefined" != typeof current_service_data.services_meta  ){
					let current_service_meta = current_service_data.services_meta;
					if( "undefined" != typeof current_service_meta.deposit_amount ){
						vm.appointment_step_form_data.deposit_payment_amount = current_service_meta.deposit_amount;
						vm.appointment_step_form_data.deposit_payment_type = current_service_meta.deposit_type;
						if( "percentage" == current_service_meta.deposit_type ){
							vm.appointment_step_form_data.deposit_payment_amount_percentage = current_service_meta.deposit_amount;
						}
					}
				}
			';
			return $bookingpress_after_selecting_booking_service_data;
		}
		
		function bookingpress_edit_service_more_vue_data_func() {
			?>	
			vm2.service.deposit_type = (response.data.deposit_type !== undefined) ? response.data.deposit_type : 'percentage';
			vm2.service.deposit_amount = (response.data.deposit_amount !== undefined) ? response.data.deposit_amount : '100';				
			<?php
		}

		function bookingpress_save_service_details( $response, $service_id, $posted_data ) {
			global $bookingpress_services;
			if ( ! empty( $service_id ) && ! empty( $posted_data ) ) {
				$service_deposit_type = ! empty( $posted_data['deposit_type'] ) ? $posted_data['deposit_type'] : 'fixed';
				if ( ! empty( $service_deposit_type ) ) {
					$bookingpress_services->bookingpress_add_service_meta( $service_id, 'deposit_type', $service_deposit_type );
				}
				$service_deposit_amount = ! empty( $posted_data['deposit_amount'] ) ? $posted_data['deposit_amount'] : 0;

				if(($service_deposit_type == "fixed" && $service_deposit_amount > $posted_data['service_price']) || ($service_deposit_type == "percentage" && $service_deposit_amount > 100)){
					$response['variant'] = 'error';
					$response['title'] = esc_html__('Error', 'bookingpress-appointment-booking');
					$response['msg'] = esc_html__('Deposit amount must be less than or equal to service price', 'bookingpress-appointment-booking');
				}else{
					$bookingpress_services->bookingpress_add_service_meta( $service_id, 'deposit_amount', $service_deposit_amount );
				}
				
			}
			return $response;
		}

		function bookingpress_check_deposit_payment_module_activation() {
			$is_deposit_payment_module_activated = 0;
			$deposit_payment_addon_option_val    = get_option( 'bookingpress_deposit_payment_module' );
			if ( ! empty( $deposit_payment_addon_option_val ) && ( $deposit_payment_addon_option_val == 'true' ) ) {
				$is_deposit_payment_module_activated = 1;
			}
			return $is_deposit_payment_module_activated;
		}

		function bookingpress_modify_service_data_fields_func( $bookingpress_services_vue_data_fields ) {

			$bookingpress_services_vue_data_fields['service']['deposit_type']   = 'fixed';
			$bookingpress_services_vue_data_fields['service']['deposit_amount'] = '';
			return $bookingpress_services_vue_data_fields;
		}

		function bookingpress_before_activate_bookingpress_module_func($addon_key) {
			global $wpdb,$tbl_bookingpress_services,$bookingpress_services;
			if($addon_key == 'bookingpress_deposit_payment_module' ) {
				$bookingpress_services_data = $wpdb->get_results("SELECT bookingpress_service_id FROM ".$tbl_bookingpress_services,ARRAY_A); //phpcs:ignore
				if(!empty($bookingpress_services_data)) {
					foreach($bookingpress_services_data as $key => $val) {
						$bookingpress_service_id = !empty($val['bookingpress_service_id']) ? intval($val['bookingpress_service_id']) : 0 ;
						if(!empty($bookingpress_service_id)) {
							$bookingpress_deposit_type = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'deposit_type');
							$bookingpress_deposit_val = $bookingpress_services->bookingpress_get_service_meta($bookingpress_service_id, 'deposit_amount');
							if($bookingpress_deposit_type == '' && $bookingpress_deposit_val == '') {
								$bookingpress_services->bookingpress_add_service_meta($bookingpress_service_id,'deposit_type','percentage');
								$bookingpress_services->bookingpress_add_service_meta($bookingpress_service_id, 'deposit_amount',100);
							}
						}
					}
				}
			}			
		}
	}

	global $bookingpress_deposit_payment;
	$bookingpress_deposit_payment = new bookingpress_deposit_payment();
}

