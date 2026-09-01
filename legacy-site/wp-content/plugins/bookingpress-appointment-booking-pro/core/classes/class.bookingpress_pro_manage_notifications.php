<?php
if ( ! class_exists( 'bookingpress_pro_manage_notifications' ) ) {
	class bookingpress_pro_manage_notifications Extends BookingPress_Core {
		function __construct() {
			add_filter( 'bookingpress_modify_notifications_view_file_path', array( $this, 'bookingpress_modify_notifications_file_path_func' ), 10 );
			add_filter( 'bookingpress_add_dynamic_notification_data_fields', array( $this, 'bookingpress_add_dynamic_notification_data_fields_func' ), 10 );
			add_action( 'bookingpress_add_dynamic_notifications_vue_methods', array( $this, 'bookingpress_add_dynamic_notifications_vue_methods_func' ), 10 );
			add_action( 'bookingpress_add_notification_dynamic_on_load_methods', array( $this, 'bookingpress_add_notification_dynamic_on_load_methods_func' ) );		

			add_action( 'wp_ajax_bookingpress_load_custom_notification_data', array( $this, 'bookingpress_load_custom_notification_data_func' ) );
			add_action( 'wp_ajax_bookingpress_delete_custom_notification', array( $this, 'bookingpress_delete_custom_notification_func' ), 10 );			
			add_action( 'wp_ajax_bookingpress_save_custom_notification', array( $this, 'bookingpress_save_custom_notification_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_get_custom_notification_data', array( $this, 'bookingpress_get_custom_notification_data_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_save_custom_notification_data', array( $this, 'bookingpress_save_custom_notification_data_func' ), 10 );
			add_filter( 'add_bookingpress_default_notification_status', array( $this, 'add_bookingpress_default_notification_status_func' ), 10, 2 );
			add_action ('bookingpress_email_notification_get_data',array($this,'bookingpress_email_notification_get_data_func'));
			add_action('bookingpress_add_email_notification_data',array($this,'bookingpress_add_email_notification_data_func'));
			add_filter('bookingpress_save_email_notification_data_filter',array($this,'bookingpress_save_email_notification_data_filter_func'),11,2);
		}
		function bookingpress_save_email_notification_data_filter_func($bookingpress_database_modify_data,$notification_data){
			$bookingpress_attach_ics_file      = ( ! empty($notification_data['bookingpress_email_ics_attachment_status']) && 'true' == $notification_data['bookingpress_email_ics_attachment_status'] ) ? 1 : 0;
			$bookingpress_database_modify_data['bookingpress_notification_attach_ics_file'] = $bookingpress_attach_ics_file;			
			return $bookingpress_database_modify_data;
		}

		function bookingpress_add_email_notification_data_func() {
			?>
			bookingpress_save_notification_data.bookingpress_email_ics_attachment_status = vm.bookingpress_email_ics_attachment_status	
			<?php
		}
		function bookingpress_email_notification_get_data_func(){
			?>
			if( 1 == bookingpress_return_notification_data.bookingpress_notification_attach_ics_file ){
				vm.bookingpress_email_ics_attachment_status = true;
			} else {
				vm.bookingpress_email_ics_attachment_status = false;
			}
			<?php
		}

		function bookingpress_modify_notifications_file_path_func( $bookingpress_notification_view_path ) {
			$bookingpress_notification_view_path = BOOKINGPRESS_PRO_VIEWS_DIR . '/notifications/manage_notifications.php';
			return $bookingpress_notification_view_path;
		}

		function bookingpress_add_dynamic_notification_data_fields_func( $bookingpress_notification_vue_methods_data ) {
			global $bookingpress_global_options,$BookingPress,$bookingpress_pro_staff_members;

			$bookingpress_services_details 		   = $BookingPress->get_bookingpress_service_data_group_with_category();
			$bookingpress_options                  = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_appointment_placeholders = json_decode( $bookingpress_options['appointment_placeholders'] );
			$bookingpress_staffmember_placeholders = json_decode( $bookingpress_options['staff_member_placeholders'] );			
			$bookingpress_service_placeholders 	   = json_decode( $bookingpress_options['service_placeholders'] );
			$bookingpress_custom_fields_placeholders = json_decode($bookingpress_options['custom_fields_placeholders']);
			$bookingpress_notification_vue_methods_data['options'] = array(
				array(
					'value' => 'Option1',
					'label' => 'Option1',
				),
				array(
					'value' => 'Option2',
					'label' => 'Option2',
				),
			);
			$default_notification_status = array(
				'customer' => array(
					'appointment_rescheduled' => false,
				),
				'employee' => array(
					'appointment_rescheduled' => false,
				),
			);
			$bookingpress_notification_vue_methods_data['rules'] = array(
				'bookingpress_custom_notification_name' => array(
					array(
						'required' => true,
						'message'  => esc_html__( 'Please enter name', 'bookingpress-appointment-booking' ),
						'trigger'  => 'blur',
					),
				),
			);		
			$bookingpress_notification_vue_methods_data['bookingpress_is_custom_email_notification']   = false;
			$bookingpress_notification_vue_methods_data['bookingpress_custom_email_notification_type'] = '1';
			$bookingpress_notification_vue_methods_data['bookingpress_custom_email_notification_text'] = '';
			$bookingpress_notification_vue_methods_data['bookingpress_notification_send_only'] 		   = false;			
			$bookingpress_notification_vue_methods_data['bookingpress_custom_notification_listing']	   = '';
			$bookingpress_notification_vue_methods_data['bookingpress_email_ics_attachment_status']    = 'false';			
			$bookingpress_notification_vue_methods_data['bookingpress_notification_services_data']     = $bookingpress_services_details;								
			$bookingpress_notification_vue_methods_data['bookingpress_appointment_placeholders']  	   = $bookingpress_appointment_placeholders;
			$bookingpress_notification_vue_methods_data['bookingpress_staff_member_placeholders'] 	   = $bookingpress_staffmember_placeholders;
			$bookingpress_notification_vue_methods_data['bookingpress_service_placeholders'] 		   = $bookingpress_service_placeholders;
			$bookingpress_notification_vue_methods_data['bookingpress_custom_fields_placeholders']     = $bookingpress_custom_fields_placeholders;
			$bookingpress_notification_vue_methods_data['open_add_custom_notification_modal']		   = false;
			$bookingpress_notification_vue_methods_data['custom_notification_modal_pos'] 			   = '0px';
			$bookingpress_notification_vue_methods_data['custom_notification_modal_pos_left'] 		   = '0px';			
			$bookingpress_notification_vue_methods_data['bookingpress_notification_id'] 			   = 0;
			$bookingpress_notification_vue_methods_data['model_notification_type']   = 'add';
			$bookingpress_notification_vue_methods_data['bookingpress_is_custom_email_notification']   = false;
			$bookingpress_notification_vue_methods_data['is_mask_display']                			   = false;
			$bookingpress_notification_vue_methods_data['default_notification_status']['customer']	   = array_merge( $bookingpress_notification_vue_methods_data['default_notification_status']['customer'], $default_notification_status['customer'] );
			$bookingpress_notification_vue_methods_data['default_notification_status']['employee'] 	   = array_merge( $bookingpress_notification_vue_methods_data['default_notification_status']['employee'], $default_notification_status['employee'] );	
			$bookingpress_notification_vue_methods_data['custom_email_notification_form'] = array(
				'bookingpress_custom_notification_name' => '',
				'bookingpress_notification_event_action' => 'appointment_approved',
				'bookingpress_custom_notification_type'	=> 'action-trigger',
				'bookingpress_notification_scheduled_type' => 'before',
				'bookingpress_email_duration_val' => '6',
				'bookingpress_email_duration_unit' => 'h',
				'bookingpress_notification_selected_service_name' =>'',				
			);	
			$bookingpress_notification_vue_methods_data['bookingpress_notification_customer_tab_title']  = esc_html__('To Customer', 'bookingpress-appointment-booking');	
			
			$bookingpress_notification_vue_methods_data['bookingpress_notification_admin_tab_title']  = esc_html__('To Admin', 'bookingpress-appointment-booking');	
			$is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_singular_staffmember_name = !empty($bookingpress_options['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_options['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
			
			if($is_staffmember_module_activated){
				$bookingpress_notification_vue_methods_data['bookingpress_notification_admin_tab_title'] = esc_html__( 'To', 'bookingpress-appointment-booking')." ".esc_html( $bookingpress_singular_staffmember_name );
			}
			return $bookingpress_notification_vue_methods_data;
		}

		function bookingpress_add_notification_dynamic_on_load_methods_func() { ?>
				this.bookingpress_load_custom_notification_data();
			<?php
		}

		function bookingpress_add_dynamic_notifications_vue_methods_func() {
			?>
				bookingpress_insert_sms_placeholder(selected_tag){
					const vm = this
					var bookingpress_sms_element = document.getElementById("bookingpress_sms_notification");
					if(document.getElementById("bookingpress_sms_notification") != null){
						var bookingpress_textarea_element = document.getElementById("bookingpress_sms_notification");
						var bookingpress_current_val = document.getElementById("bookingpress_sms_notification").value;
						var bookingpress_start_pos = bookingpress_textarea_element.selectionStart;
						var bookingpress_end_pos = bookingpress_textarea_element.selectionEnd;

						var bookingpress_before_string = bookingpress_current_val.substring(0, bookingpress_start_pos);
						var bookingpress_after_string = bookingpress_current_val.substring(bookingpress_end_pos, bookingpress_current_val.length);

						var bookingpress_new_appended_string = bookingpress_before_string + selected_tag + bookingpress_after_string;
						document.getElementById("bookingpress_sms_notification").value = bookingpress_new_appended_string;
					}
				},
				bookingpress_insert_whatsapp_placeholder(selected_tag){
					const vm = this
					var bookingpress_sms_element = document.getElementById("bookingpress_whatsapp_notification");
					<?php do_action( 'bookingpress_insert_whatsapp_placeholder_outside'); ?>
					if(document.getElementById("bookingpress_whatsapp_notification") != null){
						var bookingpress_textarea_element = document.getElementById("bookingpress_whatsapp_notification");
						var bookingpress_current_val = document.getElementById("bookingpress_whatsapp_notification").value;
						var bookingpress_start_pos = bookingpress_textarea_element.selectionStart;
						var bookingpress_end_pos = bookingpress_textarea_element.selectionEnd;

						var bookingpress_before_string = bookingpress_current_val.substring(0, bookingpress_start_pos);
						var bookingpress_after_string = bookingpress_current_val.substring(bookingpress_end_pos, bookingpress_current_val.length);

						var bookingpress_new_appended_string = bookingpress_before_string + selected_tag + bookingpress_after_string;
						document.getElementById("bookingpress_whatsapp_notification").value = bookingpress_new_appended_string;
					}
				},
				bookingpress_add_custom_email_notification(currentElement) {
					const vm = this
					var dialog_pos = currentElement.target.getBoundingClientRect();
					vm.custom_notification_modal_pos = (dialog_pos.top - 80)+'px'
					vm.custom_notification_modal_pos_left = -(dialog_pos.left + 670 )+'px';
					vm.bookingpress_is_custom_email_notification = true;
					vm.bookingpress_reset_custom_email_notification()
					vm.open_add_custom_notification_modal = true;
					vm.model_notification_type = 'add';

					if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
						vm.bpa_adjust_popup_position( currentElement, 'div#custom_notification_modal .bpa-dialog--add-custom-notification', '', 'right');
					}
				},
				bookingpress_reset_custom_email_notification() {
					const vm = this
					vm.bookingpress_custom_notification_old_name = '';
					vm.custom_email_notification_form.bookingpress_custom_notification_name = '';
					vm.custom_email_notification_form.bookingpress_custom_notification_type = 'action-trigger';
					vm.custom_email_notification_form.bookingpress_notification_event_action = 'appointment_approved';					
					vm.custom_email_notification_form.bookingpress_notification_scheduled_type	= 'before'		
					vm.custom_email_notification_form.bookingpress_notification_selected_service_name = '';
					vm.custom_email_notification_form.bookingpress_email_duration_val = 6;	
					vm.custom_email_notification_form.bookingpress_email_duration_unit = 'h';	
					<?php
					do_action( 'bookingpress_reset_custom_email_notification_data' );
					?>
				},
				bookingpress_discard_custom_email_notification(notification_name) {
					const vm = this					
					vm.bookingpress_is_custom_email_notification = true											
					var custom_notification_delet_data = {}
					custom_notification_delet_data.action = 'bookingpress_delete_custom_notification'
					custom_notification_delet_data.notification_name = notification_name		
					custom_notification_delet_data._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
					axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(custom_notification_delet_data))
					.then( function (response) {
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
						});
						vm.bookingpress_load_custom_notification_data();
						vm.bookingpress_select_email_notification('Appointment Approved', 'appointment_approved')												
					}.bind(this))
					.catch( function (error) {
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					});
				},	
				bookingpress_load_custom_notification_data() {
					const vm = this
					var custom_notification_data = {}
					custom_notification_data.action = 'bookingpress_load_custom_notification_data'
					custom_notification_data._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( custom_notification_data ) )
					.then(function(response){
						vm.bookingpress_custom_notification_listing = response.data;
					}).catch(function(error) {
						console.log(error)
					});
				},
				bookingpress_add_email_notification_data() {
					const vm = this;
					if(vm.bookingpress_is_custom_email_notification == true) {												
						vm.bookingpress_save_custom_notification();																				
					} else {
						vm.bookingpress_save_email_notification_data();
					}
				},
				bookingpress_save_custom_email_notification_data(){					
					const vm = this		
					vm.$refs['custom_email_notification_form'].validate((valid) => {								
						if(valid){	
							var bookingpress_save_notification_data = []
							bookingpress_save_notification_data.notification_id= vm.bookingpress_notification_id
							bookingpress_save_notification_data.bookingpress_custom_notification_old_name = vm.bookingpress_email_notification_edit_text
							if(vm.model_notification_type == 'add') {
								bookingpress_save_notification_data.bookingpress_custom_notification_old_name = '';
							}
							bookingpress_save_notification_data.action = 'bookingpress_save_custom_notification_data'
							bookingpress_save_notification_data.notification_data = vm.custom_email_notification_form
							bookingpress_save_notification_data._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
							axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(bookingpress_save_notification_data))
							.then( function (response) {
								vm.$notify({
									title: response.data.title,
									message: response.data.msg,
									type: response.data.variant,
									customClass: response.data.variant+'_notification',
								});
								if(response.data.variant != undefined  && response.data.variant == 'success' && response.data.notification_name != undefined ) {
									vm.bookingpress_get_custom_notification_data(response.data.notification_name);
									vm.close_custom_notification_modal();
									vm.bookingpress_get_all_default_notification_status();					
									vm.bookingpress_load_custom_notification_data();																		
									vm.bookingpress_active_email_notification = response.data.notification_name										
									vm.bookingpress_selected_default_notification = response.data.notification_name				
								}
							}.bind(this))
							.catch( function (error) {
								console.log(error);
								vm.$notify({
									title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
									message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
									type: 'error',
									customClass: 'error_notification',
								});
							});
						}
					});							
				},	
				bookingpress_save_custom_notification(){					
					const vm = this		
					vm.is_disabled = true
					vm.is_display_save_loader = '1'
                    tinyMCE.triggerSave()
					const formData = new FormData(vm.$refs.email_notification_form.$el);
					const data = {};
					for (let [key, val] of formData.entries()) {
						Object.assign(data, { [key]: val })
					}					
					let bookingpress_save_notification_data = []
					var bookingpress_email_notification_msg_data = data.bookingpress_email_notification_subject_message;											
					bookingpress_save_notification_data.notification_id = vm.bookingpress_notification_id;
					bookingpress_save_notification_data.notification_receiver = vm.activeTabName
					bookingpress_save_notification_data.notification_name = vm.bookingpress_email_notification_edit_text
					bookingpress_save_notification_data.notification_subject = vm.bookingpress_email_notification_subject
					bookingpress_save_notification_data.notification_msg = bookingpress_email_notification_msg_data
					bookingpress_save_notification_data.default_notification_status = vm.default_notification_status
					bookingpress_save_notification_data.selected_default_notification = vm.bookingpress_selected_default_notification					
					bookingpress_save_notification_data.action = 'bookingpress_save_custom_notification'										
					bookingpress_save_notification_data.bookingpress_is_custom_notification = 1;
					<?php
					do_action( 'bookingpress_add_email_notification_data' );
					?>
					bookingpress_save_notification_data._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
					axios.post(appoint_ajax_obj.ajax_url, Qs.stringify(bookingpress_save_notification_data))
					.then( function (response) {
						vm.is_disabled = false
						vm.is_display_save_loader = '0'
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
						});
						if(response.data.notification_name != undefined )	 {
							vm.bookingpress_get_custom_notification_data(response.data.notification_name);
						}
						vm.bookingpress_get_all_default_notification_status();					
						vm.bookingpress_load_custom_notification_data();
					}.bind(this))
					.catch( function (error) {
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					});										
				},	
				bookingpress_get_custom_notification_data(email_notification_key, is_custom_notification = 1, bookingpress_custom_notification_id = 0) {		
					const vm = this
					vm.bookingpress_is_custom_email_notification = true;																		
					vm.bookingpress_active_email_notification = email_notification_key										
					vm.bookingpress_selected_default_notification = email_notification_key				

					var bookingpress_get_notification_post_data = []
					bookingpress_get_notification_post_data.bookingpress_notification_receiver_type = vm.activeTabName
					bookingpress_get_notification_post_data.bookingpress_notification_name = email_notification_key

					bookingpress_get_notification_post_data.action = 'bookingpress_get_custom_notification_data'
					bookingpress_get_notification_post_data._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'

					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_get_notification_post_data ) )
					.then( function (response) {
						const bookingpress_return_notification_data = response.data.return_data
						if(response.data.variant == 'success' && bookingpress_return_notification_data.length != 0)
						{	
							vm.bookingpress_email_notification_edit_text = bookingpress_return_notification_data.bookingpress_notification_name;
							vm.bookingpress_email_notification_subject = bookingpress_return_notification_data.bookingpress_notification_subject;
							var bookingpress_email_notification_msg = bookingpress_return_notification_data.bookingpress_notification_message;							
							document.getElementById('bookingpress_email_notification_subject_message').value = bookingpress_email_notification_msg;							
							bookingpress_email_notification_msg = bookingpress_email_notification_msg == null ? '' : bookingpress_email_notification_msg;
							setTimeout(function(){
								if( null != tinyMCE.activeEditor ){
									tinyMCE.activeEditor.setContent(bookingpress_email_notification_msg);
								}
							},100);
							
							vm.bookingpress_custom_notification_type = bookingpress_return_notification_data.bookingpress_custom_notification_type;						
							vm.bookingpress_custom_notification_name = bookingpress_return_notification_data.bookingpress_notification_name
							vm.bookingpress_notification_id = bookingpress_return_notification_data.bookingpress_notification_id;
							vm.custom_email_notification_form.bookingpress_notification_event_action = bookingpress_return_notification_data.bookingpress_notification_event_action;
							<?php
							do_action( 'bookingpress_email_notification_get_data' );
							?>
						}
					}.bind(this))
					.catch( function (error) {
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					});
				},				
				
				bookingpress_notification_change_tab(){
					const vm = this
					vm.close_custom_notification_modal();
					if(vm.bookingpress_is_custom_email_notification == true) {										
						vm.bookingpress_get_custom_notification_data(vm.bookingpress_active_email_notification)
					} else {
						vm.bookingpress_get_notification_data(vm.bookingpress_active_email_notification,vm.bookingpress_selected_default_notification_db_name)
					}
				},	
				close_custom_notification_modal(){
					const vm = this
					vm.open_add_custom_notification_modal = false;
				},
				edit_custom_notification_settings(currentElement,email_notification_key,notification_id) {
					const vm = this
					var dialog_pos = currentElement.target.getBoundingClientRect();
					vm.custom_notification_modal_pos = (dialog_pos.top - 85)+'px'
					vm.custom_notification_modal_pos_left = (dialog_pos.left - 150 )+'px';
					vm.open_add_custom_notification_modal = true;

					if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
						vm.bpa_adjust_popup_position( currentElement, 'div#custom_notification_modal .bpa-dialog--add-custom-notification', '', 'right');
					}

					vm.bookingpress_notification_id = notification_id;	
					vm.model_notification_type ='edit';
					var bookingpress_get_notification_post_data = []
					bookingpress_get_notification_post_data.bookingpress_notification_name = email_notification_key
					bookingpress_get_notification_post_data.bookingpress_notification_receiver_type = vm.activeTabName
					bookingpress_get_notification_post_data.action = 'bookingpress_get_custom_notification_data'
					bookingpress_get_notification_post_data._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_get_notification_post_data ) )
					.then( function (response) {
						const bookingpress_return_notification_data = response.data.return_data
						if(response.data.variant == 'success' && bookingpress_return_notification_data.length != 0)
						{									
							vm.bookingpress_notification_id = bookingpress_return_notification_data.bookingpress_notification_id;
							vm.custom_email_notification_form.bookingpress_custom_notification_name = bookingpress_return_notification_data.bookingpress_notification_name
							vm.custom_email_notification_form.bookingpress_custom_notification_type = bookingpress_return_notification_data.bookingpress_custom_notification_type;
							vm.custom_email_notification_form.bookingpress_notification_event_action = bookingpress_return_notification_data.bookingpress_notification_event_action;
							vm.custom_email_notification_form.bookingpress_custom_email_notification_status = bookingpress_return_notification_data.bookingpress_notification_status;
							vm.custom_email_notification_form.bookingpress_custom_email_notification_appointment_status = bookingpress_return_notification_data.
							bookingpress_notification_appointment_status;						
							vm.custom_email_notification_form.bookingpress_notification_scheduled_type	= bookingpress_return_notification_data.bookingpress_notification_scheduled_type;
							vm.custom_email_notification_form.bookingpress_notification_selected_service_name = bookingpress_return_notification_data.bookingpress_notification_service;
							vm.custom_email_notification_form.bookingpress_notification_event_action= bookingpress_return_notification_data.bookingpress_notification_event_action;
							vm.custom_email_notification_form.bookingpress_email_duration_val = bookingpress_return_notification_data.bookingpress_notification_duration_val	
							vm.custom_email_notification_form.bookingpress_email_duration_unit = bookingpress_return_notification_data.bookingpress_notification_duration_unit	
							<?php do_action('bookingpress_edit_custom_notification_settings_outside'); ?>	
						}
					}.bind(this))
					.catch( function (error) {
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					});
				},
				bookingpress_change_custom_notification_type(){
					const vm = this;
					let bookingpress_custom_notification_type = vm.custom_email_notification_form.bookingpress_custom_notification_type;
					let bookingpress_notification_event_action = vm.custom_email_notification_form.bookingpress_notification_event_action;
					if(bookingpress_custom_notification_type == 'scheduled'){
						if(bookingpress_notification_event_action == 'appointment_noshow' || bookingpress_notification_event_action == 'appointment_completed'){
							vm.custom_email_notification_form.bookingpress_notification_event_action = 'appointment_approved';
						}
					}
				},	
				<?php
		}

		function bookingpress_save_custom_notification_func() {
			global $wpdb, $tbl_bookingpress_notifications,$BookingPress,$bookingpress_global_options;
			$response = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'save_custom_notification', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			$bookingpress_return_data = array();
			$bookingpress_return_data['return_data']            = array();
			$bookingpress_return_data['is_custom_notification'] = 1;
			$bookingpress_notification_id = ! empty( $_REQUEST['notification_id'] ) ? sanitize_text_field( $_REQUEST['notification_id'] ) : '';
			if ( ! empty( $_REQUEST ) && !empty($bookingpress_notification_id)) {
				$bookingpress_global_options_data = $bookingpress_global_options->bookingpress_global_options();
				$bookingpress_allow_tag = json_decode( $bookingpress_global_options_data['allowed_html'], true );
				$bookingpress_notification_receiver = ! empty( $_REQUEST['notification_receiver'] ) ? sanitize_text_field( $_REQUEST['notification_receiver'] ) : '';
				$bookingpress_notification_subject  = ! empty( $_REQUEST['notification_subject'] ) ? sanitize_text_field( $_REQUEST['notification_subject'] ) : '';
				$bookingpress_custom_notification   = ! empty( $_REQUEST['bookingpress_is_custom_notification'] ) ? sanitize_text_field( $_REQUEST['bookingpress_is_custom_notification'] ) : 0;
				$bookingpress_notification_msg     = ! empty( $_REQUEST['notification_msg'] ) ? wp_kses( $_REQUEST['notification_msg'], $bookingpress_allow_tag ) : '';
				$bookingpress_notification_msg = htmlspecialchars_decode(stripslashes_deep($bookingpress_notification_msg));
				$bookingpress_default_selected_notification = ! empty( $_REQUEST['selected_default_notification'] ) ? sanitize_text_field( $_REQUEST['selected_default_notification'] ) : '';
				$bookingpress_default_notification_status   = ! empty( $_REQUEST['default_notification_status'][ $bookingpress_notification_receiver ][ stripslashes_deep($bookingpress_default_selected_notification) ] ) ? sanitize_text_field( $_REQUEST['default_notification_status'][ $bookingpress_notification_receiver ][ stripslashes_deep($bookingpress_default_selected_notification) ] ) : '';
				$bookingpress_default_notification_status   = $bookingpress_default_notification_status == 'true' ? 1 : 0;
				$bookingpress_database_modify_data = array(
					'bookingpress_notification_receiver_type' => $bookingpress_notification_receiver,
					'bookingpress_notification_status'    => $bookingpress_default_notification_status,
					'bookingpress_notification_type'      => 'custom',
					'bookingpress_notification_subject'   => $bookingpress_notification_subject,
					'bookingpress_notification_message'   => $bookingpress_notification_msg,
					'bookingpress_updated_at'             => current_time( 'mysql' ),
					'bookingpress_notification_is_custom' => $bookingpress_custom_notification,
				);
				$bookingpress_database_modify_data = apply_filters( 'bookingpress_save_email_notification_data_filter', $bookingpress_database_modify_data, $_REQUEST );
				if ( !empty($bookingpress_notification_id) ) {										
					$bookingpress_modify_where_condition = array(
						'bookingpress_notification_id' => $bookingpress_notification_id,
					);
					$wpdb->update( $tbl_bookingpress_notifications, $bookingpress_database_modify_data, $bookingpress_modify_where_condition );
					$bookingpress_return_data['msg'] = esc_html__( 'Email notifications details update successfully.', 'bookingpress-appointment-booking' );

				} 
				$bookingpress_return_data['variant']                = 'success';
				$bookingpress_return_data['title']                  = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$bookingpress_return_data['return_data']            = array();
				$bookingpress_return_data['is_custom_notification'] = 1;

				do_action('bookingpress_after_save_email_notification_data',$_REQUEST);
			}
			echo wp_json_encode( $bookingpress_return_data );
			exit;
		}

		function bookingpress_load_custom_notification_data_func() {
			global $wpdb, $tbl_bookingpress_notifications;
			$bookingpress_return_data = array();
			
			$bpa_check_authorization = $this->bpa_check_authentication( 'load_custom_notifications', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_custom_notifications_data = array();
			$bookingpress_custom_notifications_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_notification_name,bookingpress_notification_id FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_type = %s AND bookingpress_notification_is_custom = %d GROUP BY bookingpress_notification_name ORDER BY bookingpress_created_at DESC", 'custom', 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm		
			if(!empty($bookingpress_custom_notifications_data) && is_array($bookingpress_custom_notifications_data)) {
				foreach($bookingpress_custom_notifications_data as $notification_id => $notification_data){
					$bookingpress_custom_notifications_data[$notification_id]['bookingpress_notification_name'] = isset($notification_data['bookingpress_notification_name']) ? stripslashes_deep($notification_data['bookingpress_notification_name']) : "";
				}
			}			
			echo json_encode( $bookingpress_custom_notifications_data );
			exit();
		}

		function add_bookingpress_default_notification_status_func( $bookingpress_default_notification_status_data, $bookingpres_default_notification_data ) {
			global $BookingPress;
			foreach ( $bookingpres_default_notification_data as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
					$bookingpress_notification_value         = ( $bookingpress_default_notification_val['bookingpress_notification_status'] == 1 ) ? true : false;
					$bookingpress_notification_receiver_type = $bookingpress_default_notification_val['bookingpress_notification_receiver_type'];

				switch ( $bookingpress_default_notification_val['bookingpress_notification_name'] ) {
					case 'Appointment Rescheduled':
						$bookingpress_default_notification_status_data[ $bookingpress_notification_receiver_type ]['appointment_rescheduled'] = $bookingpress_notification_value;
						break;
					case 'Complete Payment URL':
						$bookingpress_default_notification_status_data[ $bookingpress_notification_receiver_type ]['complete_payment_url'] = $bookingpress_notification_value;
						break;
					case 'Refund Payment':
						$bookingpress_default_notification_status_data[ $bookingpress_notification_receiver_type ]['refund_payment'] = $bookingpress_notification_value;
						break;
				}
			}
			$bookingpress_custom_notification_data = $this->bookingpress_get_custom_notification_status();
			if ( ! empty( $bookingpress_custom_notification_data ) ) {
				foreach ( $bookingpress_custom_notification_data as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
						$bookingpress_notification_value         = ( $bookingpress_default_notification_val['bookingpress_notification_status'] == 1 ) ? true : false;
						$bookingpress_notification_receiver_type = $bookingpress_default_notification_val['bookingpress_notification_receiver_type'];
						$bookingpress_notification_name          = $bookingpress_default_notification_val['bookingpress_notification_name'];
						$bookingpress_notification_name          = stripslashes_deep($bookingpress_notification_name);
						$bookingpress_default_notification_status_data[ $bookingpress_notification_receiver_type ][ $bookingpress_notification_name ] = $bookingpress_notification_value;
				}
			}
			return $bookingpress_default_notification_status_data;
		}

		function bookingpress_save_custom_notification_data_func() {

			global $wpdb, $tbl_bookingpress_notifications,$BookingPress;
			
			$response = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'save_custom_notification_data', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_return_data = array();
			$bookingpress_return_data['variant']                = 'error';
			$bookingpress_return_data['title']                  = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$bookingpress_return_data['msg']                    = esc_html__( 'Something went wrong..', 'bookingpress-appointment-booking' );
			$bookingpress_return_data['return_data']            = array();
			$bookingpress_return_data['is_custom_notification'] = 1;			
			$bookingpress_database_modify_data = array();
			$bookingpress_notification_id = ! empty( $_REQUEST['notification_id'] ) ? sanitize_text_field( $_REQUEST['notification_id'] ) : '';			

			$bookingpress_custom_notification_old_name = ! empty( $_REQUEST['bookingpress_custom_notification_old_name'] ) ? sanitize_text_field( $_REQUEST['bookingpress_custom_notification_old_name'] ) : '';	
			$bookingpress_custom_notification_name = ! empty( $_REQUEST['notification_data']['bookingpress_custom_notification_name'] ) ? sanitize_text_field( $_REQUEST['notification_data']['bookingpress_custom_notification_name'] ) : '';						

			if ( ! empty( $bookingpress_custom_notification_name ) && ( empty( $bookingpress_custom_notification_old_name) || $bookingpress_custom_notification_old_name != $bookingpress_custom_notification_name)) {
				$bookingpress_if_notification_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_notification_id) FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_type = %s", $bookingpress_custom_notification_name, 'custom' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
				if ( $bookingpress_if_notification_exists > 0 ) {
					$bookingpress_return_data['variant'] = 'error';
					$bookingpress_return_data['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
					$bookingpress_return_data['msg']     = esc_html__( 'Notification Name is already exist.', 'bookingpress-appointment-booking' );
					echo json_encode( $bookingpress_return_data );
					exit;
				}
			}
			if ( ! empty( $_REQUEST ) && !empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bookingpress_save_custom_notification_data' && !empty($bookingpress_custom_notification_name) ) {
				$bookingpress_custom_notification_type = ! empty( $_REQUEST['notification_data']['bookingpress_custom_notification_type'] ) ? sanitize_text_field( $_REQUEST['notification_data']['bookingpress_custom_notification_type'] ) : '';	
				$bookingpress_notification_event_action   = ! empty( $_REQUEST['notification_data']['bookingpress_notification_event_action'] ) ? sanitize_text_field( $_REQUEST['notification_data']['bookingpress_notification_event_action'] ) : 'appointment_approved';
				$bookingpress_notification_scheduled_type= ! empty( $_REQUEST['notification_data']['bookingpress_notification_scheduled_type'] ) ? sanitize_text_field( $_REQUEST['notification_data']['bookingpress_notification_scheduled_type'] ) : '';
				$bookingpress_notification_duration_val   = ! empty( $_REQUEST['notification_data']['bookingpress_email_duration_val'] ) ? sanitize_text_field( $_REQUEST['notification_data']['bookingpress_email_duration_val'] ) : '';
				$bookingpress_notification_duration_unit  = ! empty( $_REQUEST['notification_data']['bookingpress_email_duration_unit'] ) ? sanitize_text_field( $_REQUEST['notification_data']['bookingpress_email_duration_unit'] ) : '';				
				$bookingpress_service_ids = ! empty( $_REQUEST['notification_data']['bookingpress_notification_selected_service_name'] ) ? array_map( array( $BookingPress, 'appointment_sanatize_field' ), $_REQUEST['notification_data']['bookingpress_notification_selected_service_name'] ) : array();// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_REQUEST contains mixed array and will be sanitized using 'appointment_sanatize_field' function
				
				$bookingpress_database_modify_data['bookingpress_notification_service'] = implode( ',', $bookingpress_service_ids );					
				$bookingpress_database_modify_data['bookingpress_notification_name'] = $bookingpress_custom_notification_name;
				$bookingpress_database_modify_data['bookingpress_custom_notification_type']   = $bookingpress_custom_notification_type;
				$bookingpress_database_modify_data['bookingpress_notification_event_action']  = $bookingpress_notification_event_action;				
				$bookingpress_database_modify_data['bookingpress_notification_scheduled_type'] = $bookingpress_notification_scheduled_type;				
				$bookingpress_database_modify_data['bookingpress_notification_duration_unit']  = $bookingpress_notification_duration_unit;
				$bookingpress_database_modify_data['bookingpress_notification_duration_val']   = $bookingpress_notification_duration_val;					

				$bookingpress_database_modify_data = apply_filters('bookingpress_modify_custom_notification_data_before_update', $bookingpress_database_modify_data, $_REQUEST['notification_data'] );
				
				if(!empty($bookingpress_notification_id) && !empty($bookingpress_custom_notification_old_name)) {	
					$wpdb->update( $tbl_bookingpress_notifications, $bookingpress_database_modify_data,array( 'bookingpress_notification_name' => $bookingpress_custom_notification_old_name));															
					$bookingpress_return_data['notification_name'] 		=  stripslashes_deep($bookingpress_custom_notification_name);
					$bookingpress_return_data['msg']               		= esc_html__( 'Email notifications Update successfully.', 'bookingpress-appointment-booking' );
					$bookingpress_return_data['variant']           		= 'success';
					$bookingpress_return_data['title']              	= esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$bookingpress_return_data['return_data']            = array();
					$bookingpress_return_data['is_custom_notification'] = 1;
				} else {								
					$bookingpress_database_modify_data['bookingpress_created_at'] = current_time( 'mysql' );				
					$bookingpress_database_modify_data['bookingpress_notification_is_custom']   = 1;		
					$bookingpress_database_modify_data['bookingpress_notification_type']   = 'custom';		
					$bookingpress_database_modify_data['bookingpress_notification_receiver_type']   = 'customer';		
					$wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_database_modify_data );
					$bookingpress_database_modify_data['bookingpress_notification_receiver_type']   = 'employee';		
					$wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_database_modify_data );
					$bookingpress_return_data['notification_name'] 		= stripslashes_deep($bookingpress_custom_notification_name);
					$bookingpress_return_data['msg']               		= esc_html__( 'Email notifications create successfully.', 'bookingpress-appointment-booking' );
					$bookingpress_return_data['variant']           		= 'success';
					$bookingpress_return_data['title']              	= esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$bookingpress_return_data['return_data']            = array();
					$bookingpress_return_data['is_custom_notification'] = 1;
				}
			}	
			echo wp_json_encode( $bookingpress_return_data );
			exit;
		}

		function bookingpress_get_custom_notification_data_func() {
			global $wpdb, $tbl_bookingpress_notifications, $tbl_bookingpress_notification_services, $tbl_bookingpress_notification_events;
			$response = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'get_custom_notification_data', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_return_data = array();
			$bookingpress_return_data['variant']                = 'error';
			$bookingpress_return_data['title']                  = esc_html__( 'Error', 'bookingpress-appointment-booking' );
			$bookingpress_return_data['msg']                    = esc_html__( 'Something went wrong..', 'bookingpress-appointment-booking' );
			$bookingpress_return_data['return_data']            = array();
			$bookingpress_return_data['is_custom_notification'] = 0;

			if ( ! empty( $_REQUEST ) ) {
					$bookingpress_notification_receiver_type = !empty($_REQUEST['bookingpress_notification_receiver_type']) ? sanitize_text_field($_REQUEST['bookingpress_notification_receiver_type']) : '';
					$bookingpress_notification_name          = ! empty( $_REQUEST['bookingpress_notification_name'] ) ? sanitize_text_field( $_REQUEST['bookingpress_notification_name'] ) : '';

				if ( ! empty( $bookingpress_notification_receiver_type ) && ! empty( $bookingpress_notification_name ) ) {
					$bookingpress_if_notification_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(bookingpress_notification_id) FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_is_custom = %d AND bookingpress_notification_receiver_type = %s", $bookingpress_notification_name, 1, $bookingpress_notification_receiver_type ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

					if ( $bookingpress_if_notification_exists > 0 ) {
						$bookingpress_exist_record_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_is_custom = %d AND bookingpress_notification_receiver_type = %s", $bookingpress_notification_name, 1, $bookingpress_notification_receiver_type ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
						if ( ! empty( $bookingpress_exist_record_data['bookingpress_notification_service'] ) ) {
							$bookingpress_exist_record_data['bookingpress_notification_service'] = explode( ',', $bookingpress_exist_record_data['bookingpress_notification_service'] );
						}
						$bookingpress_exist_record_data          = apply_filters( 'bookingpress_get_notifiacation_data_filter', $bookingpress_exist_record_data );
						$bookingpress_exist_record_data['bookingpress_notification_name'] = stripslashes_deep($bookingpress_exist_record_data['bookingpress_notification_name']);
						$bookingpress_exist_record_data['bookingpress_notification_subject'] = stripslashes_deep($bookingpress_exist_record_data['bookingpress_notification_subject']);						
						$bookingpress_return_data['return_data'] = $bookingpress_exist_record_data;
						$bookingpress_return_data['msg']         = esc_html__( 'Data received successfully', 'bookingpress-appointment-booking' );
					} else {
						$bookingpress_return_data['msg'] = esc_html__( 'No data received', 'bookingpress-appointment-booking' );
					}
					$bookingpress_return_data['variant']                = 'success';
					$bookingpress_return_data['title']                  = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$bookingpress_return_data['is_custom_notification'] = 0;
				}

				$bookingpress_return_data = apply_filters( 'bookingpress_get_email_notification_data_modified',$bookingpress_return_data,$_REQUEST);
			}
			
			echo wp_json_encode( $bookingpress_return_data );
			exit;
		}

		function bookingpress_delete_custom_notification_func() {
			global $wpdb,$tbl_bookingpress_notifications;
			$response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'delete_custom_email_notification', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			if ( ! empty( $_REQUEST['notification_name'] ) ) {
				$delete_notification_name = ! empty( $_POST['notification_name'] ) ? sanitize_text_field( $_POST['notification_name'] ) : ''; // phpcs:ignore
				if ( ! empty( $delete_notification_name ) ) {
					$wpdb->delete( $tbl_bookingpress_notifications, array( 'bookingpress_notification_name' => $delete_notification_name ) );
					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Notification has been deleted successfully.', 'bookingpress-appointment-booking' );
				}
			}
			echo wp_json_encode( $response );
			exit();
		}

		function bookingpress_get_custom_notification_status() {
			global $wpdb,$tbl_bookingpress_notifications;
			$bookingpress_default_notifications_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_type = %s AND bookingpress_notification_is_custom = %d", 'custom', 1 ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
			return $bookingpress_default_notifications_data;
		}

		function bookingpress_get_custom_notification_for_appointment_approved( $receiver_type, $schedule_type ) {
			global $wpdb, $tbl_bookingpress_notifications;
			$bookingpress_custom_notifications_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_type = %s AND bookingpress_custom_notification_type = %s AND bookingpress_notification_receiver_type =%s AND bookingpress_notification_event_action = %s AND bookingpress_notification_scheduled_type = %s", 'custom', 'scheduled', $receiver_type, 'appointment_approved', $schedule_type ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

			return $bookingpress_custom_notifications_list;
		}


		function bookingpress_get_custom_notification_for_appointment_pending( $receiver_type, $schedule_type ) {
			global $wpdb, $tbl_bookingpress_notifications;
			$bookingpress_custom_notifications_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_type = %s AND bookingpress_custom_notification_type = %s AND bookingpress_notification_receiver_type =%s AND bookingpress_notification_event_action = %s AND bookingpress_notification_scheduled_type = %s", 'custom', 'scheduled', $receiver_type, 'appointment_pending', $schedule_type ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

			return $bookingpress_custom_notifications_list;
		}


		function bookingpress_get_custom_notification_for_appointment_canceled( $receiver_type, $schedule_type ) {
			global $wpdb, $tbl_bookingpress_notifications;
			$bookingpress_custom_notifications_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_type = %s AND bookingpress_custom_notification_type = %s AND bookingpress_notification_receiver_type =%s AND bookingpress_notification_event_action = %s AND bookingpress_notification_scheduled_type = %s", 'custom', 'scheduled', $receiver_type, 'appointment_canceled', $schedule_type ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

			return $bookingpress_custom_notifications_list;
		}

		function bookingpress_get_custom_notification_for_appointment_rejected( $receiver_type, $schedule_type ) {
			global $wpdb, $tbl_bookingpress_notifications;
			$bookingpress_custom_notifications_list = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_type = %s AND bookingpress_custom_notification_type = %s AND bookingpress_notification_receiver_type =%s AND bookingpress_notification_event_action = %s AND bookingpress_notification_scheduled_type = %s", 'custom', 'scheduled', $receiver_type, 'appointment_rejected', $schedule_type ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

			return $bookingpress_custom_notifications_list;
		}

	}
}
global $bookingpress_pro_manage_notifications;
$bookingpress_pro_manage_notifications = new bookingpress_pro_manage_notifications();
