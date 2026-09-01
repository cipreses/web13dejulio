<?php

if ( ! class_exists( 'bookingpress_pro_dashboard' ) ) {
	class bookingpress_pro_dashboard Extends BookingPress_Core {
		function __construct() {

			add_filter( 'bookingpress_modify_dashboard_view_file_path', array( $this, 'bookingpress_modify_dashboard_view_file_path_func' ) );
			add_filter( 'bookingpress_modify_dashboard_data_fields', array( $this, 'bookingpress_modify_dashboard_data_fields_func' ) );
			add_action( 'bookingpress_load_summary_dynamic_data', array( $this, 'bookingpress_load_summary_dynamic_data_func' ) );
			add_action( 'bookingpress_dashboard_modify_dynamic_vue_methods', array($this, 'bookingpress_dashboard_dynamic_vue_methods_func') );

			add_filter('bookinpress_is_appointment_book_for_change_status', array($this, 'bookinpress_is_appointment_book_for_change_status_func'), 10, 2);
		
			add_filter('bookingpress_appointment_add_view_field', array($this, 'bookingpress_appointment_add_view_field_func'), 10, 2);

			add_action( 'bookingpress_dashboard_add_appointment_model_reset', array($this, 'bookingpress_dashboard_add_appointment_model_reset_func') );	
			
			add_filter( 'bookingpress_check_payment_status', array($this,'bookingpress_check_payment_status_func'));
		}

		function bookingpress_check_payment_status_func( $payment_status_check ){
			global $tbl_bookingpress_payment_logs;
			
			$payment_status_check = "AND bookingpress_payment_status IN (1, 4) ) + ( SELECT IFNULL( SUM( bookingpress_paid_amount - bookingpress_refund_amount ), 0 ) FROM $tbl_bookingpress_payment_logs WHERE bookingpress_refund_type = 'partial' ) + (SELECT IFNULL(SUM( bookingpress_due_amount ), 0) FROM $tbl_bookingpress_payment_logs WHERE bookingpress_payment_status = '1' AND bookingpress_deposit_amount >'0' )"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

			return $payment_status_check;
		}

		function bookingpress_appointment_add_view_field_func($appointment,$get_appointment) {
			
			global $bookingpress_appointment;
			$service_duration        = esc_html($get_appointment['bookingpress_service_duration_val']);
			$service_duration_unit   = esc_html($get_appointment['bookingpress_service_duration_unit']);
			$bookingpress_appointment_date = $get_appointment['bookingpress_appointment_date'];
			$bookingpress_start_datetime = $get_appointment['bookingpress_appointment_date'].' '.$get_appointment['bookingpress_appointment_time'];
			$bookingpress_end_datetime = $get_appointment['bookingpress_appointment_date'].' '.$get_appointment['bookingpress_appointment_end_time'];

			if($service_duration_unit != 'd') {				
				if( !empty( $bookingpress_appointment ) && method_exists( $bookingpress_appointment, 'bookingpress_get_appointment_duration' ) ){
					$service_duration = $bookingpress_appointment->bookingpress_get_appointment_duration($bookingpress_start_datetime, $bookingpress_end_datetime);
				}
				else {
					$bookingpress_start_time = $get_appointment['bookingpress_appointment_time'];
					$bookingpress_tmp_start_time = new DateTime($bookingpress_appointment_date . ' ' .$bookingpress_start_time);
					$bpa_start_datetime = $bookingpress_appointment_date . ' ' .$bookingpress_start_time;
					if( 'h' == $service_duration_unit ){
						$service_duration_unit_label = 'hours';
					} else {
						$service_duration_unit_label = 'minutes';
					}

					$bpa_end_datetime = date( 'Y-m-d H:i:s', strtotime( $bpa_start_datetime . ' +'.$service_duration.' '.$service_duration_unit_label ));
					$bookingpress_tmp_end_time = new DateTime( $bpa_end_datetime );			

					$booking_date_interval = $bookingpress_tmp_start_time->diff($bookingpress_tmp_end_time);
					$bookingpress_minute = $booking_date_interval->format('%i');
					$bookingpress_hour = $booking_date_interval->format('%h');  
					$bookingpress_days = $booking_date_interval->format('%d');  
					$service_duration = '';

					if($bookingpress_minute > 0) {
						if( $bookingpress_minute == 1 ){
							$service_duration = $bookingpress_minute.' ' . esc_html__('Min', 'bookingpress-appointment-booking'); 
						}else{
							$service_duration = $bookingpress_minute.' ' . esc_html__('Mins', 'bookingpress-appointment-booking'); 
						}
					}
					if($bookingpress_hour > 0 ) {
						if( $bookingpress_hour == 1 ){
							$service_duration = $bookingpress_hour.' ' . esc_html__('Hour', 'bookingpress-appointment-booking').' '.$service_duration;
						}
						else{
							$service_duration = $bookingpress_hour.' ' . esc_html__('Hours', 'bookingpress-appointment-booking').' '.$service_duration;
						}
					}
					if($bookingpress_days == 1) {
						$service_duration = '24 ' . esc_html__('Hours', 'bookingpress-appointment-booking');
					}
				}
			} else {
				if( 1 == $service_duration ){
					$service_duration .= ' ' . esc_html__('Day', 'bookingpress-appointment-booking');
				} else {   
					$service_duration .= ' ' . esc_html__('Days', 'bookingpress-appointment-booking');
				}                        
			}
			$appointment['appointment_duration'] = $service_duration;
			return $appointment;
		}

		function bookinpress_is_appointment_book_for_change_status_func($is_appointment_already_booked, $booked_appointment_details){
			global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings, $bookingpress_pro_services, $tbl_bookingpress_staffmembers_services;

			$appointment_update_id  = ! empty($_REQUEST['update_appointment_id']) ? intval($_REQUEST['update_appointment_id']) : 0;

			$bookingpress_appointment_date       = $booked_appointment_details['bookingpress_appointment_date'];
			$bookingpress_appointment_start_time = $booked_appointment_details['bookingpress_appointment_time'];

			if(!empty($appointment_update_id)){
				$bookingpress_service_id = !empty($booked_appointment_details['bookingpress_service_id']) ? intval($booked_appointment_details['bookingpress_service_id']) : 0;
				$bookingpress_staff_id = !empty($booked_appointment_details['bookingpress_staff_member_id']) ? intval($booked_appointment_details['bookingpress_staff_member_id']) : 0;

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

						$total_booked_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_appointment,SUM(bookingpress_selected_extra_members - 1) as total_extra_members FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_service_id = %d AND bookingpress_staff_member_id = %d", $appointment_update_id, '2', '1', $bookingpress_appointment_date, $bookingpress_appointment_start_time, $bookingpress_service_id, $bookingpress_staff_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

						if(!empty($total_booked_appointment_data)) {
							$total_booked_appointment = $total_booked_appointment_data['total_appointment'] + $total_booked_appointment_data['total_extra_members'];
						}

					}else{
						$total_booked_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(bookingpress_appointment_booking_id) as total_appointment,SUM(bookingpress_selected_extra_members - 1) as total_extra_members FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id != %d AND (bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s) AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_service_id = %d", $appointment_update_id, '2', '1', $bookingpress_appointment_date, $bookingpress_appointment_start_time, $bookingpress_service_id),ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

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

		function bookingpress_dashboard_add_appointment_model_reset_func(){
		?>
			vm2.appointment_formdata.selected_extra_services_ids = '';
			for(m in vm2.bookingpress_loaded_extras) {
				for(i in vm2.bookingpress_loaded_extras[m]) {
					vm2.bookingpress_loaded_extras[m][i]['bookingpress_is_selected'] = false;
				}					
			}
			vm2.appointment_formdata.total_amount = 0;
			vm2.appointment_formdata.total_amount_with_currency = vm2.bookingpress_price_with_currency_symbol( 0 );	
			vm2.appointment_formdata.subtotal = 0;
			vm2.appointment_formdata.subtotal_with_currency = vm2.bookingpress_price_with_currency_symbol( 0 );
		<?php
		}

		function bookingpress_dashboard_dynamic_vue_methods_func(){
			global $bookingpress_notification_duration;
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
			bookingpress_close_extras_modal(){
				//Trigger body click for close extras popover
				//this.bookingpress_calculate_prices();
				this.bookingpress_admin_get_final_step_amount();
				document.body.click();
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
				if(typeof vm.appointment_formdata.tip_amount !== 'undefined'){
					var total_amount = vm.appointment_formdata.total_amount - vm.appointment_formdata.tip_amount;
					vm.appointment_formdata.total_amount = total_amount;
				}
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

						//vm.bookingpress_calculate_prices()
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
				vm.bookingpress_admin_get_final_step_amount();
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
                vm2.resetForm()
                vm2.open_customer_modal = true
            },
			get_wordpress_users() {
                const vm = new Vue()
                const vm2 = this
                var customer_action = { action:'bookingpress_get_wpuser',_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' }
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( customer_action ) )
                .then(function(response){
                    vm2.wpUsersList = response.data.users
                }).catch(function(error){
                    console.log(error)
                });
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
                if(file.type != 'image/jpeg' && file.type != 'image/png'){
                    vm2.$notify({
                        title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
                        message: '<?php esc_html_e('Please upload jpg/png file only', 'bookingpress-appointment-booking'); ?>',
                        type: 'error',
                        customClass: 'error_notification',
                        duration:<?php echo intval($bookingpress_notification_duration); ?>,
                    });
                    return false
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
                const vm = this;
                var bookingpress_selected_country = bookingpress_country_obj.iso2;
                vm.customer.customer_phone_country = bookingpress_selected_country;
				vm.customer.customer_phone_dial_code = bookingpress_country_obj.dialCode;
            },
			bookingpress_open_refund_model(currentElement,appointment_id,payment_id,currency_symbol,partial_refund) {
				const vm = this;				
				vm.reset_refund_confirm_model();				
				vm.refund_confirm_form.refund_currency = currency_symbol;
				vm.refund_confirm_form.allow_partial_refund = partial_refund;
				vm.refund_confirm_form.appointment_id = appointment_id;
				vm.refund_confirm_form.payment_id = payment_id;				
				var postData = { action:'bookingpress_get_refund_amount', bookingpress_appointment_id:appointment_id,bookingpress_payment_id :payment_id,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
				axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
				.then( function (response) {
					if(response.data.variant == "success"){
						vm.refund_confirm_form.refund_amount = response.data.refund_amount;
						vm.refund_confirm_form.default_refund_amount = response.data.default_refund_amount;						
					} else{											
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					}
				}).catch(function(error){
					console.log(error);
					vm.$notify({
						title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
						message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
						type: 'error',
						customClass: 'error_notification',
						duration:<?php echo intval($bookingpress_notification_duration); ?>,
					});
				});

				vm.refund_confirm_modal = true;
				if( typeof vm.bpa_adjust_popup_position != 'undefined' ){
					vm.bpa_adjust_popup_position( currentElement, 'div#refund_confirm_process .el-dialog.bpa-dialog--refund-process');
				}
			},
			close_refund_confirm_model(){
				const vm = this;
				vm.reset_refund_confirm_model();
				vm.refund_confirm_modal = false;
			},
			reset_refund_confirm_model() {
				const vm = this
				vm.refund_confirm_form.refund_type = 'full';
				vm.refund_confirm_form.refund_amount = 0;
				vm.refund_confirm_form.refund_reason = '';
				vm.refund_confirm_form.appointment_id = '';
				vm.refund_confirm_form.payment_id = '';
				vm.refund_confirm_form.default_refund_amount = 0;
				vm.refund_confirm_form.allow_partial_refund = 0;
				vm.refund_confirm_form.allow_refund = true;
				vm.is_display_refund_loader = '0';
				vm.is_refund_btn_disabled = false;
			},
			bookingpress_apply_for_refund(payment_id,appointment_id) {
				const vm = this
				vm.is_display_refund_loader = '1';
				vm.is_refund_btn_disabled = true;
				var is_error = false;
				var error_msg = false;		
				if(vm.refund_confirm_form.allow_refund == true) {
					if((vm.refund_confirm_form.refund_amount == '' || vm.refund_confirm_form.refund_amount == 0) && vm.refund_confirm_form.refund_type == 'partial') {
						error_msg =  '<?php esc_html_e('Refund amount should be more than zero', 'bookingpress-appointment-booking'); ?>';
						is_error = true
					}
					if(parseInt(vm.refund_confirm_form.refund_amount) > parseInt(vm.refund_confirm_form.default_refund_amount) && vm.refund_confirm_form.refund_type == 'partial' ) {					
						error_msg =  '<?php esc_html_e('Refund amount should not be more than paid the amount.', 'bookingpress-appointment-booking'); ?>';
						is_error = true
					} 
					if( is_error == true) {
						vm.$notify({
							title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
							message: error_msg,
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
						vm.is_display_refund_loader = '0';
						vm.is_refund_btn_disabled = false;
						return false;
					}
					var postData = { action:'bookingpress_apply_for_refund',bookingpress_refund_data:vm.refund_confirm_form,_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						if(response.data.variant == "success"){
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
							vm.close_refund_confirm_model();
							vm.loadAppointments();
						} else{											
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval($bookingpress_notification_duration); ?>,
							});
						}
						vm.is_display_refund_loader = '0';
						vm.is_refund_btn_disabled = false;
						
					}).catch(function(error){
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e('Error', 'bookingpress-appointment-booking'); ?>',
							message: '<?php esc_html_e('Something went wrong..', 'bookingpress-appointment-booking'); ?>',
							type: 'error',
							customClass: 'error_notification',
							duration:<?php echo intval($bookingpress_notification_duration); ?>,
						});
					});
				} else {
					vm.bookingpress_change_status(appointment_id,'3')
					vm.is_display_refund_loader = '0';
					vm.is_refund_btn_disabled = false;
					vm.close_refund_confirm_model();				
				}

			},
            isValidateZeroDecimal(evt){
                const vm = this                
                if (/[^0-9]+/.test(evt)){
                    vm.refund_confirm_form.refund_amount = evt.slice(0, -1);
                }
            },
			<?php
			do_action('bookingpress_dashboard_add_dynamic_vue_methods');
		}

		function bookingpress_modify_dashboard_data_fields_func( $bookingpress_dashboard_vue_data_fields ) {
			global $wpdb, $BookingPressPro, $bookingpress_pro_staff_members, $BookingPress, $bookingpress_service_extra, $bookingpress_bring_anyone_with_you, $tbl_bookingpress_staffmembers, $bookingpress_coupons, $tbl_bookingpress_form_fields, $tbl_bookingpress_customers, $bookingpress_global_options, $bookingpress_pro_services, $tbl_bookingpress_extra_services, $tbl_bookingpress_staffmembers_services;
			$is_staffmember_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_dashboard_vue_data_fields['is_staffmember_activated'] = $is_staffmember_activated;

			$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? $bookingpress_global_options_arr['bookingpress_staffmember_singular_name'] : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
			$bookingpress_dashboard_vue_data_fields['is_timeslot_display'] = '1';
			
			$bookigpress_time_format_for_booking_form =  $BookingPress->bookingpress_get_customize_settings('bookigpress_time_format_for_booking_form','booking_form');
			$bookigpress_time_format_for_booking_form =  !empty($bookigpress_time_format_for_booking_form) ? $bookigpress_time_format_for_booking_form : '2';
			$bookingpress_dashboard_vue_data_fields['bookigpress_time_format_for_booking_form'] = $bookigpress_time_format_for_booking_form;

			// Add appointment data variables
			$bookingpress_dashboard_vue_data_fields['selected_staffmember'] = '';
			$bookingpress_dashboard_vue_data_fields['rules']['selected_staffmember'] = array(
                array(
                    'required' => true,
                    'message'  => esc_html__('Please select', 'bookingpress-appointment-booking')." ".esc_html(stripslashes_deep($bookingpress_singular_staffmember_name)),
                    'trigger'  => 'change',
                ),
            );
			$bookingpress_dashboard_vue_data_fields['bookingpress_extras_popover_modal'] = false;
			$bookingpress_dashboard_vue_data_fields['bookingpress_service_extras'] = array();

			$bookingpress_dashboard_vue_data_fields['is_tax_enable'] = (is_plugin_active('bookingpress-tax/bookingpress-tax.php'))?1:0;
			$bookingpress_dashboard_vue_data_fields['is_custom_service_duration'] = (is_plugin_active('bookingpress-custom-service-duration/bookingpress-custom-service-duration.php'))?1:0;

			$bookingpress_dashboard_vue_data_fields['is_extras_enable'] = $bookingpress_service_extra->bookingpress_check_service_extra_module_activation();
			$bookingpress_dashboard_vue_data_fields['is_staff_enable'] = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
			$bookingpress_dashboard_vue_data_fields['is_bring_anyone_with_you_enable'] = $bookingpress_bring_anyone_with_you->bookingpress_check_bring_anyone_module_activation();
			$bookingpress_dashboard_vue_data_fields['is_coupon_enable'] = $bookingpress_coupons->bookingpress_check_coupon_module_activation();
			$bookingpress_dashboard_vue_data_fields['bookingpress_allow_coupon_code'] = $bookingpress_coupons->bookingpress_check_coupon_module_activation();

			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['bookingpress_staffmembers_lists'] = array();
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['bookingpress_bring_anyone_max_capacity'] = 0;
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['bookingpress_bring_anyone_min_capacity'] = 0;

			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['selected_extra_services'] = array();
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['selected_extra_services_ids'] = '';
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['selected_staffmember'] = '';
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['selected_bring_members'] = 0;

			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['subtotal'] = 0;
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['subtotal_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['extras_total'] = 0;
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['extras_total_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);
			
			$tax_percentage = $BookingPress->bookingpress_get_settings( 'tax_percentage', 'payment_setting' );	
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['tax_percentage'] = $tax_percentage;
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['tax'] = 0;
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['tax_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);
			$bookingpress_price_setting_display_option = $BookingPress->bookingpress_get_settings('price_settings_and_display', 'payment_setting');
            $bookingpress_dashboard_vue_data_fields['appointment_formdata']['tax_price_display_options'] = $bookingpress_price_setting_display_option;

            $bookingpress_tax_order_summary = $BookingPress->bookingpress_get_settings('display_tax_order_summary', 'payment_setting');
            $bookingpress_dashboard_vue_data_fields['appointment_formdata']['display_tax_order_summary'] = $bookingpress_tax_order_summary;

            $bookingpress_tax_order_summary_text = $BookingPress->bookingpress_get_settings('included_tax_label', 'payment_setting');
            $bookingpress_dashboard_vue_data_fields['appointment_formdata']['included_tax_label'] = stripslashes_deep($bookingpress_tax_order_summary_text);


			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['applied_coupon_code'] = '';
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['applied_coupon_details'] = array();
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['coupon_discounted_amount'] = 0;
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['coupon_discounted_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['total_amount'] = 0;
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['total_amount_with_currency'] = $BookingPress->bookingpress_price_formatter_with_currency_symbol(0);

			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['mark_as_paid'] = false;

			$bookingpress_dashboard_vue_data_fields['coupon_apply_loader'] = 0;
			$bookingpress_dashboard_vue_data_fields['coupon_code_msg'] = '';
			$bookingpress_dashboard_vue_data_fields['bpa_coupon_apply_disabled'] = 0;
			$bookingpress_dashboard_vue_data_fields['coupon_applied_status'] = '';

			//Get custom fields
			$bookingpress_form_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_is_default = %d AND bookingpress_is_customer_field = %d ORDER BY bookingpress_field_position ASC", 0, 0 ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm

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

			$bookingpress_dashboard_vue_data_fields['bookingpress_form_fields'] = $bookingpress_form_fields;
			$bookingpress_dashboard_vue_data_fields['appointment_formdata']['bookingpress_appointment_meta_fields_value'] = $bookingpress_appointment_meta_fields_value;
			$bookingpress_dashboard_vue_data_fields['bookingpress_listing_fields_value'] = $bookingpress_listing_fields_value;

			//Add Customer Data Variables
			$bookingpress_dashboard_vue_data_fields['open_customer_modal'] = false;
			$bookingpress_options = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_country_list = $bookingpress_options['country_lists'];
			$bookingpress_phone_country_option = $BookingPress->bookingpress_get_settings('default_phone_country_code', 'general_setting');
			
			$bookingpress_dashboard_vue_data_fields['customer'] = array(
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
							$bookingpress_dashboard_vue_data_fields['customer']['bpa_customer_field'][ $cs_form_fields['bookingpress_field_meta_key'] . '_' . $chk_key ] = false;
                        }
                    } else {
						$bookingpress_dashboard_vue_data_fields['customer']['bpa_customer_field'][$cs_form_fields['bookingpress_field_meta_key']] = $bpa_customer_fields[ $x ]['bookingpress_field_key'];
					}
                }
            }
            $bookingpress_dashboard_vue_data_fields['bookingpress_customer_fields'] = $bpa_customer_fields;

			$bookingpress_custom_fields = $bookingpress_dashboard_vue_data_fields['bookingpress_form_fields'];
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

			$bookingpress_dashboard_vue_data_fields['custom_field_rules'] = $bookingpress_custom_fields_validation_arr;

			$bookingpress_dashboard_vue_data_fields['phone_countries_details'] = json_decode($bookingpress_country_list);
			$bookingpress_dashboard_vue_data_fields['loading'] = false;

			$bookingpress_dashboard_vue_data_fields['customer_detail_save'] = false;
			$bookingpress_dashboard_vue_data_fields['wpUsersList'] = array();
			$bookingpress_dashboard_vue_data_fields['savebtnloading'] = false;


			$bookingpress_dashboard_vue_data_fields['cusShowFileList'] = false;
			$bookingpress_dashboard_vue_data_fields['is_display_loader'] = '0';
			$bookingpress_dashboard_vue_data_fields['is_disabled'] = false;
			$bookingpress_dashboard_vue_data_fields['is_display_save_loader'] = '0';
			$bookingpress_dashboard_vue_data_fields['bookingpress_tel_input_props'] = array(
				'defaultCountry' => $bookingpress_phone_country_option,
				'validCharactersOnly' => true,
			);

			$bookingpress_loaded_services = $bookingpress_dashboard_vue_data_fields['appointment_services_list'];
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

										$bookingpress_dashboard_vue_data_fields['appointment_formdata']['selected_extra_services'][$extra_val['bookingpress_extra_services_id']] = $bookingpress_extra_services_data[$extra_key];
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

			$bookingpress_dashboard_vue_data_fields['appointment_services_list'] = $bookingpress_loaded_services;
			$bookingpress_dashboard_vue_data_fields['bookingpress_loaded_extras'] = $bookingpress_service_extras;
			$bookingpress_dashboard_vue_data_fields['bookingpress_loaded_staff'] = $bookingpress_service_staffmembers;
			$bookingpress_dashboard_vue_data_fields['is_mask_display']          = false;
			$bookingpress_dashboard_vue_data_fields['is_refund_btn_disabled']   = false;
			$bookingpress_dashboard_vue_data_fields['is_display_refund_loader'] = '0';
			$bookingpress_dashboard_vue_data_fields['refund_confirm_modal'] 	= false;
			$bookingpress_dashboard_vue_data_fields['refund_confirm_form']['refund_type']   = 'full';
			$bookingpress_dashboard_vue_data_fields['refund_confirm_form']['refund_reason'] = '';
			$bookingpress_dashboard_vue_data_fields['refund_confirm_form']['allow_refund']  = true;
			$bookingpress_dashboard_vue_data_fields['refund_confirm_form']['refund_amount'] = '';
			$bookingpress_dashboard_vue_data_fields['refund_confirm_form']['allow_partial_refund'] = 0;
			$bookingpress_dashboard_vue_data_fields['rules_refund_confirm_form'] = array();

			$bookingpress_currency_separator = $BookingPress->bookingpress_get_settings('price_separator', 'payment_setting');
			$bookingpress_dashboard_vue_data_fields['bookingpress_currency_separator'] = $bookingpress_currency_separator;			
			$bookingpress_decimal_points = $BookingPress->bookingpress_get_settings('price_number_of_decimals', 'payment_setting');
			$bookingpress_decimal_points = intval($bookingpress_decimal_points);
			$bookingpress_dashboard_vue_data_fields['bookingpress_decimal_points'] = $bookingpress_decimal_points;

            $bookingpress_currency_name = $BookingPress->bookingpress_get_settings('payment_default_currency', 'payment_setting');
            $bookingpress_dashboard_vue_data_fields['bookingpress_currency_name'] = $bookingpress_currency_name;
            $bookingpress_dashboard_vue_data_fields['bookingpress_currency_symbol'] = $BookingPress->bookingpress_get_currency_symbol($bookingpress_currency_name);

            $bookingpress_price_symbol_position = $BookingPress->bookingpress_get_settings('price_symbol_position', 'payment_setting');
            $bookingpress_dashboard_vue_data_fields['bookingpress_currency_symbol_position'] = $bookingpress_price_symbol_position;

			return $bookingpress_dashboard_vue_data_fields;
		}

		function bookingpress_modify_dashboard_view_file_path_func() {
			$bookingpress_load_file_name = BOOKINGPRESS_PRO_VIEWS_DIR . '/dashboard/manage_dashboard.php';
			return $bookingpress_load_file_name;
		}

		function bookingpress_load_summary_dynamic_data_func() {
			?>
			if(response.data.total_staffmember != 'undefined' && vm2.summary_data.total_staffmember != 'undefined') {
				vm2.summary_data.total_staffmembers = response.data.total_staffmembers 
			}
			<?php
		}
	}
}

global $bookingpress_pro_dashboard;
$bookingpress_pro_dashboard = new bookingpress_pro_dashboard();
