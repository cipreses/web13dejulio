<?php

if ( ! class_exists( 'bookingpress_pro_addons' ) ) {
	class bookingpress_pro_addons Extends BookingPress_Core {
		function __construct() {
			add_action( 'bookingpress_addons_dynamic_view_load', array( $this, 'bookingpress_load_addons_view_func' ), 10 );
			add_action( 'bookingpress_addons_dynamic_data_fields', array( $this, 'bookingpress_addons_dynamic_data_fields_func' ), 10 );
			add_action( 'bookingpress_addons_dynamic_vue_methods', array( $this, 'bookingpress_addons_dynamic_vue_methods_func' ), 10 );
			add_action( 'bookingpress_addons_dynamic_on_load_methods', array( $this, 'bookingpress_addons_dynamic_onload_methods_func' ), 10 );
			add_action( 'bookingpress_addons_dynamic_helper_vars', array( $this, 'bookingpress_addons_dynamic_helper_vars_func' ) );

			add_action( 'wp_ajax_bookingpress_activate_default_module', array( $this, 'bookingpress_activate_default_module_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_deactivate_default_module', array( $this, 'bookingpress_deactivate_default_module_func' ), 10 );
			add_filter( 'bookingpress_module_configure_addon_filter', array( $this, 'bookingpress_module_configure_addon_filter_func' ), 10 );

			//add_action( 'wp_ajax_bookingpress_save_addon_configure_data', array( $this, 'bookingpress_save_addon_configure_data_func' ), 10 );
			add_action( 'wp_ajax_bookingpress_get_remote_addons_list', array( $this, 'bookingpress_get_remote_addons_list_func' ), 10 );

			add_action( 'wp_ajax_bookingpress_deactivate_plugin', array( $this, 'bookingpress_deactivate_plugin_func' ) );
			add_action( 'wp_ajax_bookingpress_activate_plugin', array( $this, 'bookingpress_activate_plugin_func' ) );
		}
		
		/**
		 * Function for activate plugin from addons page
		 *
		 * @return void
		 */
		function bookingpress_activate_plugin_func() {
			global $wpdb,$BookingPress;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'bpa_activate_plugin', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			
			$bookingpress_activate_plugin_name = ! empty( $_POST['activate_plugin_name'] ) ? sanitize_text_field( $_POST['activate_plugin_name'] ) : ''; // phpcs:ignore
			if ( ! empty( $bookingpress_activate_plugin_name ) ) {
				if ( $bookingpress_activate_plugin_name == 'bookingpress-woocommerce/bookingpress-woocommerce.php' ) {
					if ( class_exists( 'woocommerce' ) ) {
						activate_plugin( $bookingpress_activate_plugin_name );
						// Generate woocommerce product if module activated firsttime
						do_action( 'bookingpress_generate_default_product' );
						$response['variant'] = 'success';
						$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( 'Addon Activated Successfully', 'bookingpress-appointment-booking' );
					} else {
						$response['variant'] = 'error';
						$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( 'BookingPress WooCommerce payment gateway addon requires WooCommerce plugin installed and active', 'bookingpress-appointment-booking' );
					}
				} else {
					activate_plugin( $bookingpress_activate_plugin_name );
					$response['variant'] = 'success';
					$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']     = esc_html__( 'Addon Activated Successfully', 'bookingpress-appointment-booking' );
				}
			}

			echo wp_json_encode( $response );
			exit;
		}
		
		/**
		 * Function for deactivate plugin from addons page
		 *
		 * @return void
		 */
		function bookingpress_deactivate_plugin_func() {
			global $wpdb,$BookingPress;
			$response              = array();
			
			$bpa_check_authorization = $this->bpa_check_authentication( 'deactivate_plugin', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$bookingpress_deactivate_plugin_name = ! empty( $_POST['deactivate_plugin_name'] ) ? sanitize_text_field( $_POST['deactivate_plugin_name'] ) : ''; // phpcs:ignore
			if ( ! empty( $bookingpress_deactivate_plugin_name ) ) {
				deactivate_plugins( $bookingpress_deactivate_plugin_name );

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Addon Deactivated Successfully', 'bookingpress-appointment-booking' );
			}

			echo wp_json_encode( $response );
			exit;
		}
				
		/**
		 * Function for get addons list from remote
		 *
		 * @return void
		 */
		function bookingpress_get_remote_addons_list_func() {
			global $wpdb,$BookingPress;
			$response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'get_remote_addons_list', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}
			$response['addons_response'] = '';
			$response['css'] = '';

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

			if ( ! is_wp_error( $bookingpress_addons_res ) ) {
				$bookingpress_body_res = base64_decode( $bookingpress_addons_res['body'] );
				if ( ! empty( $bookingpress_body_res ) ) {
					$bookingpress_body_res = json_decode( $bookingpress_body_res, true );
					$bookingpress_addon_list_css = '';
					foreach ( $bookingpress_body_res as $bookingpress_body_key => $bookingpress_body_val ) {
						if ( is_plugin_active( $bookingpress_body_val['addon_installer'] ) ) {
							$bookingpress_body_res[ $bookingpress_body_key ]['addon_isactive'] = 1;
						} else {
							if ( ! file_exists( WP_PLUGIN_DIR . '/' . $bookingpress_body_val['addon_installer'] ) ) {
								$bookingpress_body_res[ $bookingpress_body_key ]['addon_isactive'] = 2;
							}
						}
						$bookingpress_horizontal_postion = isset($bookingpress_body_val['addon_icon_horizontal_position'])  ? $bookingpress_body_val['addon_icon_horizontal_position'] : 0;
                        $addon_icon_vertical_position = isset($bookingpress_body_val['addon_icon_vertical_position'])  ? $bookingpress_body_val['addon_icon_vertical_position'] : 0;
                        $addon_icon_slug = isset($bookingpress_body_val['addon_icon_slug'])  ? $bookingpress_body_val['addon_icon_slug'] : '';
                        $addon_icon_background = isset($bookingpress_body_val['addon_icon_background'])  ? $bookingpress_body_val['addon_icon_background'] : '';
                        $bookingpress_addon_list_css .= '
                            .bpa-addons-container .bpa-addon-item .bpa-ai-icon.'.$addon_icon_slug.'{
                                background-color: '.$addon_icon_background.';
                                background-position: '.$bookingpress_horizontal_postion.' '.$addon_icon_vertical_position.';
                            }';   
					}
					$bookingpress_addon_list_css .= '.bpa-addons-container .bpa-addon-item .bpa-ai-icon{
						background: url("https://bookingpressplugin.com/bpa_misc/icons/addon-icon-sprite.png?ver='.current_time('timestamp').'") 0 0 no-repeat var(--bpa-pt-main-green);
					}';

					$bookingpress_body_res = apply_filters( 'bookingpress_addon_list_data_filter', $bookingpress_body_res );

					$response['variant']         = 'success';
					$response['title']           = esc_html__( 'Success', 'bookingpress-appointment-booking' );
					$response['msg']             = esc_html__( 'Addons list fetched successfully', 'bookingpress-appointment-booking' );
					$response['addons_response'] = $bookingpress_body_res;
					$response['css']             = $bookingpress_addon_list_css; 
				}
			} else {
				$response['msg'] = $bookingpress_addons_res->get_error_message();
			}
			echo wp_json_encode( $response );
			exit;
		}
		
		
		/**
		 * Function for addons page onload vue methods
		 *
		 * @return void
		 */
		function bookingpress_addons_dynamic_onload_methods_func() {
			global $bookingpress_notification_duration;			
			?>
				const vm = this
				vm.bookingpress_get_remote_addons_list();
				var bookingpress_activate_module;
				bookingpress_activate_module = sessionStorage.getItem("bookingpress_activate_module");
				setTimeout(function() {					
					if(bookingpress_activate_module == 'bookingpress_staffmember_module') {
						sessionStorage.removeItem("bookingpress_activate_module");
						vm.$notify({
							title: '<?php esc_html_e( 'Warning', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Please assign at least one staff member to the service otherwise service will not display at the front end.', 'bookingpress-appointment-booking' ); ?>',
							type: 'warning',
							customClass: 'warning_notification',
							duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
						});				
					}
				},2000);
			<?php
		}

				
		/**
		 * Function for addons page vue helper variables
		 *
		 * @return void
		 */
		function bookingpress_addons_dynamic_helper_vars_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
			var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
			ELEMENT.locale(lang)
			<?php
			do_action( 'bookingpress_add_addons_dynamic_helper_vars' );
		}
		
		/**
		 * Function for deactivate default modules of pro version
		 *
		 * @return void
		 */
		function bookingpress_deactivate_default_module_func() {
			global $wpdb,$tbl_bookingpress_appointment_bookings;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'deactivate_default_module', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }
			
			$response['is_activated'] = '';

			if ( ! empty( $_POST['addon_key'] ) ) { // phpcs:ignore

				if ( ! empty( $_POST['addon_key'] ) && sanitize_text_field( $_POST['addon_key'] ) == 'bookingpress_staffmember_module' ) { // phpcs:ignore
					$bookingpress_current_date       = date( 'Y-m-d', strtotime( current_time( 'mysql' ) ) );
					$bookingpress_total_appointments = $wpdb->get_row( $wpdb->prepare( 'SELECT bookingpress_appointment_booking_id FROM ' . $tbl_bookingpress_appointment_bookings . ' WHERE bookingpress_staff_member_id != %s AND bookingpress_appointment_date >= %s AND ( bookingpress_appointment_status = %s OR bookingpress_appointment_status = %s ) order by bookingpress_appointment_booking_id DESC', '', $bookingpress_current_date, '1', '2' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

					if ( ! empty( $bookingpress_total_appointments ) ) {
						$response['variant'] = 'error';
						$response['title']   = esc_html__( 'Error', 'bookingpress-appointment-booking' );
						$response['msg']     = esc_html__( 'Sorry module is not deactivated because One or more appointment is already booked with the staff member.', 'bookingpress-appointment-booking' );
						echo wp_json_encode( $response );
						exit();
					}
				}

				$addon_key = sanitize_text_field( $_POST['addon_key'] ); // phpcs:ignore
				update_option( $addon_key, '' );
				$response['variant']      = 'success';
				$response['title']        = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']          = esc_html__( 'Module De-Activated successfully', 'bookingpress-appointment-booking' );
				$response['is_activated'] = 'true';

				do_action('bookingpress_after_deactive_module',$addon_key);

			}

			echo wp_json_encode( $response );
			exit();
		}
		
		/**
		 * Function for activate default modules of pro version
		 *
		 * @return void
		 */
		function bookingpress_activate_default_module_func() {
			global $wpdb;
			$response              = array();

			$bpa_check_authorization = $this->bpa_check_authentication( 'activate_default_module', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

			$response['is_activated'] = '';

			if ( ! empty( $_POST['addon_key'] ) ) { // phpcs:ignore

				$addon_key = sanitize_text_field( $_POST['addon_key'] ); // phpcs:ignore

				do_action('bookingpress_before_activate_bookingpress_module',$addon_key);

				update_option( $addon_key, 'true' );
				$response['variant']      = 'success';
				$response['title']        = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']          = esc_html__( 'Module activated successfully', 'bookingpress-appointment-booking' );
				$response['is_activated'] = 'true';

				do_action('bookingpress_after_activate_bookingpress_module', $addon_key);

			}

			echo wp_json_encode( $response );
			exit();
		}

		function bookingpress_module_configure_addon_filter_func( $data ) {

		}
				
		/**
		 * Function for addons page dynamic vue methods
		 *
		 * @return void
		 */
		function bookingpress_addons_dynamic_vue_methods_func() {
			global $bookingpress_notification_duration;
			?>
				bookingpress_activate_addon(activate_addon_key){
					const vm = this
					vm.is_display_activate_loader = activate_addon_key
					vm.is_disabled_activate = activate_addon_key
					var postdata = {}
					postdata.action = 'bookingpress_activate_default_module'
					postdata.addon_key = activate_addon_key
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then( function (response) {
						setTimeout(function(){
							vm.is_display_activate_loader = '0'
							vm.is_disabled_activate = false
							if(response.data.variant != 'error'){
								vm.addons_list[activate_addon_key].is_active = 'true'
								if(activate_addon_key == "bookingpress_coupon_module"){
									vm.coupon_module = 1
								} else if(activate_addon_key == "bookingpress_staffmember_module"){
									vm.staffmember_module = 1
								}
							}else{
								vm.addons_list[activate_addon_key].is_active = ''
								if(activate_addon_key == "bookingpress_coupon_module"){
									vm.coupon_module = 0
								} else if(activate_addon_key == "bookingpress_staffmember_module"){
									vm.staffmember_module = 0
								}
							}
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
							});					
							sessionStorage.setItem("bookingpress_activate_module",activate_addon_key);							
							
							if((document.getElementById("toplevel_page_bookingpress").getElementsByClassName(activate_addon_key)[0] != undefined  && document.getElementById("toplevel_page_bookingpress").getElementsByClassName(activate_addon_key)[0] != "undefined")){
								document.getElementById("toplevel_page_bookingpress").getElementsByClassName(activate_addon_key)[0].parentElement.style.display = 'block';
							}							
							<?php do_action( 'bookingpress_after_module_activate' ); ?>
						}, 2000);												
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},
				bookingpress_configure_redirection(redirect_url){
					const vm = this		
					if(redirect_url != '') {						
						window.location.href = redirect_url
					}						
				},
				bookingpress_deactivate_addon(deactivate_addon_key){
					const vm = this
					vm.is_display_deactivate_loader = deactivate_addon_key
					vm.is_disabled_deactivate = deactivate_addon_key
					var postdata = {}
					postdata.action = 'bookingpress_deactivate_default_module'
					postdata.addon_key = deactivate_addon_key
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then( function (response) {
						setTimeout(function(){
							vm.is_display_deactivate_loader = '0'
							vm.is_disabled_deactivate = false
							if(response.data.variant != 'error'){
								vm.addons_list[deactivate_addon_key].is_active = ''
								if(deactivate_addon_key == "bookingpress_coupon_module"){
									vm.coupon_module = 0
								} else if(deactivate_addon_key == "bookingpress_staffmember_module"){
									vm.staffmember_module = 0
								}
							}else{
								vm.addons_list[deactivate_addon_key].is_active = 'true'
								if(deactivate_addon_key == "bookingpress_coupon_module"){
									vm.coupon_module = 1
								} else if(deactivate_addon_key == "bookingpress_staffmember_module"){
									vm.staffmember_module = 1
								}
							}
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
							});
							
							if(document.getElementById("toplevel_page_bookingpress").getElementsByClassName(deactivate_addon_key)[0] != undefined && document.getElementById("toplevel_page_bookingpress").getElementsByClassName(deactivate_addon_key)[0] != "undefined") {
								document.getElementById("toplevel_page_bookingpress").getElementsByClassName(deactivate_addon_key)[0].parentElement.style.display = 'none';
							}
							<?php do_action( 'bookingpress_after_deactivating_module' ); ?>
						}, 2000);
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},					
				bookingpress_get_remote_addons_list(){
					const vm = this
					vm.is_display_loader = '1'
					var bookingpress_remote_addon_action = {
						action: 'bookingpress_get_remote_addons_list',
						_wpnonce: '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>',
					};
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( bookingpress_remote_addon_action ) )
					.then( function (response) {
						vm.is_display_loader = '0'
						vm.more_addons = response.data.addons_response
						var addon_css = response.data.css;
						var head = document.getElementsByTagName('head')[0];
						var s = document.createElement('style');
						s.setAttribute('type', 'text/css');
						if (s.styleSheet) {
							s.styleSheet.cssText = css;
						} else {
							s.appendChild(document.createTextNode(addon_css));
						}
						head.appendChild(s);						

					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},
				bookingpress_deactivate_plugin(bookingpress_deactivate_plugin){
					const vm = this
					vm.is_display_deactivate_loader = bookingpress_deactivate_plugin
					vm.is_disabled_deactivate = bookingpress_deactivate_plugin
					var postdata = {}
					postdata.action = 'bookingpress_deactivate_plugin'
					postdata.deactivate_plugin_name = bookingpress_deactivate_plugin
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then( function (response) {
						setTimeout(function(){
							vm.is_display_deactivate_loader = '0'
							vm.is_disabled_deactivate = false
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
							});
							vm.bookingpress_get_remote_addons_list();
						}, 2000);
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},
				bookingpress_activate_plugin(bookingpress_activate_plugin_name, activate_plugin_key){
					sessionStorage.setItem("last_visited_plugin_key", activate_plugin_key)
					const vm = this
					vm.is_display_activate_loader = bookingpress_activate_plugin_name
					vm.is_disabled_activate = bookingpress_activate_plugin_name
					var postdata = {}
					postdata.action = 'bookingpress_activate_plugin'
					postdata.activate_plugin_name = bookingpress_activate_plugin_name
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>'
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then( function (response) {
						setTimeout(function(){
							vm.is_display_activate_loader = '0'
							vm.is_disabled_activate = false
							if(response.data.variant == 'success') {
								
							}
							vm.$notify({
								title: response.data.title,
								message: response.data.msg,
								type: response.data.variant,
								customClass: response.data.variant+'_notification',
								duration:<?php echo intval( $bookingpress_notification_duration ); ?>,
							});
							vm.bookingpress_get_remote_addons_list();
						}, 2000);
					}.bind(this) )
					.catch( function (error) {
						console.log(error);
					});
				},
				bookingpress_open_addon_download_url(download_url) {
					window.open(download_url, '_blank');
				},
				
			<?php
			do_action( 'bookingpress_addons_add_dynamic_vue_methods' );
		}
		
		/**
		 * Function for load addons view file
		 *
		 * @return void
		 */
		function bookingpress_load_addons_view_func() {
			$bookingpress_addons_view_path = BOOKINGPRESS_PRO_VIEWS_DIR . '/addons/addons_list.php';
			require $bookingpress_addons_view_path;
		}
		
		/**
		 * Function for load addons page data variables
		 *
		 * @return void
		 */
		function bookingpress_addons_dynamic_data_fields_func() {
			global $bookingpress_slugs, $bookingpress_addons_vue_data_fields,$BookingPress;
			$bookingpress_setting_page_url = add_query_arg( 'page', $bookingpress_slugs->bookingpress_settings, admin_url() . 'admin.php?page=bookingpress' );

			$bookingpress_addons_list                           = array(				
				'bookingpress_staffmember_module'     => array(
					'name'                  => __( 'Staff Member', 'bookingpress-appointment-booking' ),
					'key'                   => 'bookingpress_staffmember_module',
					'img_url'               => BOOKINGPRESS_PRO_IMAGES_URL . '/dummy_70_70_image.png',
					'description'           => __( 'Enable staff option throughout your website', 'bookingpress-appointment-booking' ),
					'is_active'             => get_option( 'bookingpress_staffmember_module' ),
					'configure_url'         =>  add_query_arg('setting_page', 'staffmembers_settings', $bookingpress_setting_page_url),
					'documentation_url'     => 'https://www.bookingpressplugin.com/documents/staff-member/',
					'addon_is_configurable' => '1',
					'icon_slug'             => 'bpa_staff_module',
				),				
				'bookingpress_service_extra_module'   => array(
					'name'                  => __( 'Service Extra', 'bookingpress-appointment-booking' ),
					'key'                   => 'bookingpress_service_extra_module',
					'img_url'               => BOOKINGPRESS_PRO_IMAGES_URL . '/dummy_70_70_image.png',
					'description'           => __( 'Enable extras for your services', 'bookingpress-appointment-booking' ),
					'is_active'             => get_option( 'bookingpress_service_extra_module' ),
					'configure_url'         => '',
					'documentation_url'     => 'https://www.bookingpressplugin.com/documents/services/#extra-services',
					'addon_is_configurable' => '',
					'icon_slug'             => 'bpa_service_extra',
				),
				'bookingpress_coupon_module'          => array(
					'name'                  => __( 'Coupon Management', 'bookingpress-appointment-booking' ),
					'key'                   => 'bookingpress_coupon_module',
					'img_url'               => BOOKINGPRESS_PRO_IMAGES_URL . '/dummy_70_70_image.png',
					'description'           => __( 'Give discount coupons while booking an appointment', 'bookingpress-appointment-booking' ),
					'is_active'             => get_option( 'bookingpress_coupon_module' ),
					'configure_url'         => add_query_arg( 'page', $bookingpress_slugs->bookingpress_coupons, admin_url() . 'admin.php?page=bookingpress' ),
					'documentation_url'     => 'https://www.bookingpressplugin.com/documents/coupons/',
					'addon_is_configurable' => '',
					'icon_slug'             => 'bpa_coupon',
				),
				'bookingpress_deposit_payment_module' => array(
					'name'                  => __( 'Deposit Payment', 'bookingpress-appointment-booking' ),
					'key'                   => 'bookingpress_deposit_payment_module',
					'img_url'               => BOOKINGPRESS_PRO_IMAGES_URL . '/dummy_70_70_image.png',
					'description'           => __( 'Allow partial payments to be charged to a customer while booking an appointment', 'bookingpress-appointment-booking' ),
					'is_active'             => get_option( 'bookingpress_deposit_payment_module' ),
					'configure_url'         => add_query_arg('setting_page', 'payment_settings', $bookingpress_setting_page_url),
					'documentation_url'     => 'https://www.bookingpressplugin.com/documents/services/#deposit-payment',
					'addon_is_configurable' => '1',
					'icon_slug'             => 'bpa_deposit_module',
				),
				'bookingpress_bring_anyone_with_you_module' => array(
					'name'                  => __( 'Multiple Quantity', 'bookingpress-appointment-booking' ),
					'key'                   => 'bookingpress_bring_anyone_with_you_module',
					'img_url'               =>  BOOKINGPRESS_PRO_IMAGES_URL . '/dummy_70_70_image.png',
					'description'           => __( 'Allow customer to book an appointment for more than one person', 'bookingpress-appointment-booking' ),
					'is_active'             => get_option( 'bookingpress_bring_anyone_with_you_module' ),
					'configure_url'         => '',
					'documentation_url'     => 'https://www.bookingpressplugin.com/documents/appointments/',
					'addon_is_configurable' => '',
					'icon_slug'             => 'bpa_guest_module',
				),
			);
			$bookingpress_addons_vue_data_fields['addons_list'] = $bookingpress_addons_list;
			$bookingpress_addons_vue_data_fields['more_addons'] = array();

			$bookingpress_addons_vue_data_fields = apply_filters( 'bookingpress_modify_addons_data_variable', $bookingpress_addons_vue_data_fields );

			echo wp_json_encode( $bookingpress_addons_vue_data_fields );
		}		
	}

	global $bookingpress_pro_addons, $bookingpress_addons_vue_data_fields;
	$bookingpress_pro_addons = new bookingpress_pro_addons();

	$bookingpress_addons_vue_data_fields = array(
		'bulk_action'                              => 'bulk_action',
		'is_display_loader'                        => '0',
		'addon_configuration_modal'                => false,
		'addon_configure_popup_name'               => '',
		'addon_configure_popup_title'              => '',
		'bookingpress_addon_popup_form_field_html' => '',
		'is_disabled'                              => false,
		'is_display_save_loader'                   => '0',
		'is_display_activate_loader'               => '0',
		'is_disabled_activate'                     => false,
		'is_display_deactivate_loader'             => '0',
		'is_disabled_deactivate'                   => false,
	);
}
?>
