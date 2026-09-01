<?php
if ( ! class_exists( 'bookingpress_pro_myprofile' ) ) {
	class bookingpress_pro_myprofile Extends BookingPress_Core {
		function __construct() {
			add_action( 'bookingpress_myprofile_dynamic_view_load', array( $this, 'bookingpress_load_myprofile_view_func' ) );
			add_action( 'bookingpress_myprofile_dynamic_data_fields', array( $this, 'bookingpress_myprofile_dynamic_data_fields_func' ) );
			add_action( 'bookingpress_myprofile_dynamic_on_load_methods', array( $this, 'bookingpress_myprofile_dynamic_onload_methods_func' ) );
			add_action( 'bookingpress_myprofile_dynamic_vue_methods', array( $this, 'bookingpress_myprofile_dynamic_vue_methods_func' ) );
			add_action( 'bookingpress_myprofile_dynamic_helper_vars', array( $this, 'bookingpress_myprofile_dynamic_helper_vars_func' ) );

			add_action( 'wp_ajax_bookingpress_myprofile_update_staffmember_details', array( $this, 'bookingpress_update_myprofile_details_func' ) );
		}

		function bookingpress_update_myprofile_details_func() {
			global $wpdb, $BookingPress, $bookingpress_pro_staff_members, $tbl_bookingpress_staffmembers;
			$response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'update_my_profile_details', true, 'bpa_wp_nonce' );           
			if( preg_match( '/error/', $bpa_check_authorization ) ){
				$bpa_auth_error = explode( '^|^', $bpa_check_authorization );
				$bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-appointment-booking');

				$response['variant'] = 'error';
				$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking');
				$response['msg'] = $bpa_error_msg;

				wp_send_json( $response );
				die;
			}

			// Find bookingpress staffmember id
			$bookingpress_current_user_id  = get_current_user_id();
			$bookingpress_staffmember_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $bookingpress_current_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
			$bookingpress_staffmember_id   = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ? intval( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) : 0;

			if ( ! empty( $_POST ) ) { // phpcs:ignore
				$bookingpress_update_details = array(
					'bookingpress_staffmember_firstname' => !empty( $_POST['firstname'] ) ? sanitize_text_field( stripslashes_deep($_POST['firstname']) ) : '', // phpcs:ignore
					'bookingpress_staffmember_lastname'  => !empty( $_POST['lastname'] ) ? sanitize_text_field( stripslashes_deep($_POST['lastname']) ) : '', // phpcs:ignore
					'bookingpress_staffmember_email'     => !empty( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '', // phpcs:ignore
					'bookingpress_staffmember_phone'     => !empty( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '', // phpcs:ignore
					'bookingpress_staffmember_country_phone' => !empty( $_POST['phone_country'] ) ? sanitize_text_field( $_POST['phone_country'] ) : '', // phpcs:ignore
				);

				$wpdb->update( $tbl_bookingpress_staffmembers, $bookingpress_update_details, array( 'bookingpress_staffmember_id' => $bookingpress_staffmember_id ) );

				if( !empty( $_POST['note'] ) ) { // phpcs:ignore
					$bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'staffmember_note', sanitize_textarea_field( stripslashes_deep($_POST['note']) ) ); // phpcs:ignore
				} 

				$bookingpress_avatar_details   = array();
				$bookingpress_avatar_details[] = array(
					'name' => !empty( $_POST['avatar_name'] ) ? sanitize_text_field( $_POST['avatar_name'] ) : '', // phpcs:ignore
					'url'  => !empty( $_POST['avatar_url'] ) ? esc_url_raw( $_POST['avatar_url'] ) : '', // phpcs:ignore
				);
				$bookingpress_avatar_details   = maybe_serialize( $bookingpress_avatar_details );
				$bookingpress_pro_staff_members->update_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'staffmember_avatar_details', $bookingpress_avatar_details );

				$bookingpress_posted_data                   = $_POST; // phpcs:ignore
				$bookingpress_posted_data['staffmember_id'] = $bookingpress_staffmember_id;
				$response                                   = apply_filters( 'bookingpress_staff_members_save_external_details', $bookingpress_posted_data );

				$response['variant'] = 'success';
				$response['title']   = esc_html__( 'Success', 'bookingpress-appointment-booking' );
				$response['msg']     = esc_html__( 'Staff Members Updated Successfully', 'bookingpress-appointment-booking' );
			}

			echo wp_json_encode( $response );
			exit;
		}

		function bookingpress_load_myprofile_view_func() {
			$bookingpress_load_file_name = BOOKINGPRESS_PRO_VIEWS_DIR . '/staff_members/staffmember_edit_profile.php';
			require $bookingpress_load_file_name;
		}

		function bookingpress_myprofile_dynamic_data_fields_func() {
			global $wpdb, $BookingPress, $tbl_bookingpress_staffmembers, $bookingpress_pro_staff_members;
			$bookingpress_myprofile_data_fields_arr                           = array();
			$bookingpress_myprofile_data_fields_arr['is_return']              = 1;
			$bookingpress_myprofile_data_fields_arr['is_display_save_loader'] = 0;
			$bookingpress_myprofile_data_fields_arr['is_disabled']            = false;

			// Find bookingpress staffmember id
			$bookingpress_current_user_id            = get_current_user_id();
			$bookingpress_staffmember_data           = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_staffmembers} WHERE bookingpress_wpuser_id = %d", $bookingpress_current_user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
			$bookingpress_staffmember_id             = ! empty( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) ? intval( $bookingpress_staffmember_data['bookingpress_staffmember_id'] ) : 0;
			$bookingpress_staffmember_login_id       = $bookingpress_staffmember_data['bookingpress_staffmember_login'];
			$bookingpress_staffmember_firstname      = $bookingpress_staffmember_data['bookingpress_staffmember_firstname'];
			$bookingpress_staffmember_lastname       = $bookingpress_staffmember_data['bookingpress_staffmember_lastname'];
			$bookingpress_staffmember_email          = $bookingpress_staffmember_data['bookingpress_staffmember_email'];
			$bookingpress_staffmember_phone_country  = $bookingpress_staffmember_data['bookingpress_staffmember_country_phone'];
			$bookingpress_staffmember_phone          = $bookingpress_staffmember_data['bookingpress_staffmember_phone'];
			$bookingpress_staffmember_note           = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'staffmember_note' );
			$bookingpress_staffmember_avatar_details = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'staffmember_avatar_details' );

			$bookingpress_staffmember_avatar_name = $bookingpress_staffmember_avatar_url = '';
			if ( ! empty( $bookingpress_staffmember_avatar_details ) ) {
				$bookingpress_staffmember_avatar_details = maybe_unserialize( $bookingpress_staffmember_avatar_details );
				if ( ! empty( $bookingpress_staffmember_avatar_details[0]['url'] ) ) {
					$bookingpress_staffmember_avatar_url  = $bookingpress_staffmember_avatar_details[0]['url'];
					$bookingpress_staffmember_avatar_name = $bookingpress_staffmember_avatar_details[0]['name'];
				}
			}

			$bookingpress_phone_country_option                                      = $BookingPress->bookingpress_get_settings( 'default_phone_country_code', 'general_setting' );
			$bookingpress_myprofile_data_fields_arr['bookingpress_tel_input_props'] = array(
				'defaultCountry' => ( ! empty( $bookingpress_staffmember_phone_country ) ) ? $bookingpress_staffmember_phone_country : $bookingpress_phone_country_option,
			);

			$bookingpress_myprofile_data_fields_arr['edit_profile_details'] = array(
				'avatar_url'     => $bookingpress_staffmember_avatar_url,
				'avatar_name'    => $bookingpress_staffmember_avatar_name,
				'avatar_list'    => array(),
				'avatar_details' => $bookingpress_staffmember_avatar_details,
				'firstname'      => stripslashes_deep($bookingpress_staffmember_firstname),
				'lastname'       => stripslashes_deep($bookingpress_staffmember_lastname),
				'email'          => $bookingpress_staffmember_email,
				'phone'          => $bookingpress_staffmember_phone,
				'phone_country'  => $bookingpress_staffmember_phone_country,
				'note'           => stripslashes_deep($bookingpress_staffmember_note),				
			);

			$bookingpress_myprofile_data_fields_arr['staffShowFileList'] = false;
			$bookingpress_myprofile_data_fields_arr['is_mask_display'] = false;

			$bookingpress_myprofile_data_fields_arr = apply_filters( 'bookingpress_modify_edit_profile_fields', $bookingpress_myprofile_data_fields_arr );

			echo wp_json_encode( $bookingpress_myprofile_data_fields_arr );
		}

		function bookingpress_myprofile_dynamic_onload_methods_func() {

		}

		function bookingpress_myprofile_dynamic_vue_methods_func() {
			global $bookingpress_notification_duration, $BookingPress;
			?>
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
					if(file.type != 'image/jpeg' && file.type != 'image/png'){
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
					var upload_url = vm.edit_profile_details.avatar_url
					var upload_filename = vm.edit_profile_details.avatar_name
					var postData = { action:'bookingpress_remove_uploaded_file', upload_file_url: upload_url,_wpnonce:'<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>' };
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
					.then( function (response) {
						vm.edit_profile_details.avatar_url = ''
						vm.edit_profile_details.avatar_name = ''
						vm.$refs.avatarRef.clearFiles()
					}.bind(vm) )
					.catch( function (error) {
						console.log(error);
					});
				},
				bookingpress_upload_staff_member_avatar_func(response, file, fileList){
					const vm2 = this
					if(response != ''){
						vm2.edit_profile_details.avatar_url = response.upload_url
						vm2.edit_profile_details.avatar_name = response.upload_file_name
					}
				},
				bookingpress_image_upload_limit(files, fileList){
					const vm2 = this
						if(vm2.edit_profile_details.avatar_url != ''){
						vm2.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Multiple files not allowed', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					}
				},
				bookingpress_upload_staff_member_avatar_func(response, file, fileList){
					const vm2 = this
					if(response != ''){
						vm2.edit_profile_details.avatar_url = response.upload_url
						vm2.edit_profile_details.avatar_name = response.upload_file_name
					}
				},
				bookingpress_phone_country_change_func(bookingpress_country_obj){
					const vm = this					
					var bookingpress_selected_country = bookingpress_country_obj.iso2
					vm.edit_profile_details.phone_country = bookingpress_selected_country
				},
				bookingpress_save_staffmember_details(){
					const vm = this
					const vm2 = this
					vm.is_disabled = true
					vm.is_display_save_loader = '1'
					var postdata = vm.edit_profile_details					
					postdata.action = 'bookingpress_myprofile_update_staffmember_details'
					<?php do_action( 'bookingpress_save_staff_member' ); ?>
					postdata._wpnonce = '<?php echo esc_html( wp_create_nonce( 'bpa_wp_nonce' ) ); ?>';
					axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
					.then(function(response){
						vm.is_disabled = false
						vm.is_display_save_loader = '0'							
						vm.$notify({
							title: response.data.title,
							message: response.data.msg,
							type: response.data.variant,
							customClass: response.data.variant+'_notification',
						});
					}).catch(function(error){
						vm.is_disabled = false
						vm.is_display_loader = '0'
						console.log(error);
						vm.$notify({
							title: '<?php esc_html_e( 'Error', 'bookingpress-appointment-booking' ); ?>',
							message: '<?php esc_html_e( 'Something went wrong..', 'bookingpress-appointment-booking' ); ?>',
							type: 'error',
							customClass: 'error_notification',
						});
					});
				},
			<?php
				do_action( 'bookingpress_myprofile_dynamic_add_vue_methods_func' );
		}

		function bookingpress_myprofile_dynamic_helper_vars_func() {
			global $bookingpress_global_options;
			$bookingpress_options     = $bookingpress_global_options->bookingpress_global_options();
			$bookingpress_locale_lang = $bookingpress_options['locale'];
			?>
				var lang = ELEMENT.lang.<?php echo esc_html( $bookingpress_locale_lang ); ?>;
				ELEMENT.locale(lang)
			<?php
		}
	}
}

global $bookingpress_pro_myprofile;
$bookingpress_pro_myprofile = new bookingpress_pro_myprofile();
