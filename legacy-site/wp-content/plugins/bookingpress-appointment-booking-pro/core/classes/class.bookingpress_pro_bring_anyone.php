<?php
if ( ! class_exists( 'bookingpress_bring_anyone_with_you' ) ) {
	class bookingpress_bring_anyone_with_you Extends BookingPress_Core {
		function __construct() {

			if ( $this->bookingpress_check_bring_anyone_module_activation() ) {
				add_filter('bookingpress_frontend_apointment_form_add_dynamic_data',array($this ,'bookingpress_frontend_apointment_form_add_dynamic_data_func'));
				add_filter('bookingpress_customize_add_dynamic_data_fields',array($this,'bookingpress_customize_add_dynamic_data_fields_func'),10);
				add_filter('bookingpress_get_booking_form_customize_data_filter',array($this, 'bookingpress_get_booking_form_customize_data_filter_func'),10,1);
				add_action( 'bookingpress_set_additional_appointment_xhr_data', array( $this, 'bookingpress_set_bawy_appointment_xhr_data') );
				add_filter( 'bookingpress_modify_timeslot_data_for_bawy', array($this, 'bookingpress_calculate_bawy_for_timeslot'), 10, 2 );

				add_filter( 'bookingpress_fetch_bring_members', array( $this, 'bookingpress_set_total_bring_members') );
				add_filter('bookingpress_front_modify_cart_data_filter',array($this,'bookingpress_front_modify_cart_data_filter_func'),11);

				add_filter( 'bookingpress_dynamic_next_page_request_filter', array( $this, 'bookingpress_set_quantity'), 7, 1 );

				/* For repeater fields data added. */
				add_filter( 'bookingpress_add_pro_booking_form_methods', array( $this, 'bookingpress_repeat_form_fields'));

				/** For booking token update added */
				add_filter( 'bookingpress_add_pro_booking_form_methods', array( $this, 'bookingpress_quantity_methods') );

				if(is_plugin_active('bookingpress-multilanguage/bookingpress-multilanguage.php')) {
					add_filter('bookingpress_modified_language_translate_fields',array($this,'bookingpress_modified_language_translate_fields_func'),10);
                	add_filter('bookingpress_modified_customize_form_language_translate_fields',array($this,'bookingpress_modified_language_translate_fields_func'),10);
				}

				add_action( 'bookingpress_validate_booking_form', array( $this, 'bookingpress_validate_bring_anyone_details') );
			}

			add_action('bookingpress_after_entry_data_insert',array($this,'bookingpress_after_entry_data_insert_fun'),10,2);
			add_action( 'bookingpress_after_book_appointment', array( $this, 'bookingpress_after_book_appointment_fun' ), 10, 3 );
			
			/*  Function for removed repeater fields in custom data */
			add_filter('bookingpress_removed_repeater_data_in_custom_fields',array($this,'bookingpress_removed_repeater_data_in_custom_fields_func'),10,2);
			add_filter('bookingpress_get_appointment_guest_data',array($this,'bookingpress_get_appointment_guest_data_func'),10,2);
			add_action('bookingpress_backend_display_guest_data',array($this,'bookingpress_backend_display_guest_data_func'),10);
			add_action('bookingpress_my_booking_display_guest_data',array($this,'bookingpress_my_booking_display_guest_data_func'),10);
			add_action('bookingpress_after_deactive_module',array($this,'bookingpress_removed_repeater_filed_func'),10,1);

			//add_action('init',array($this,'bookingpress_removed_repeater_filed_func'));

		}
		
		/**
		 * function to validate the multiple quantity.
		 *
		 * @param  mixed $posted_data
		 * @return void
		 */
		function bookingpress_validate_bring_anyone_details( $posted_data ){

			global $wpdb, $bookingpress_services;
			if( !empty( $posted_data['appointment_data'] ) && empty( $posted_data['appointment_data']['cart_items'] ) ){
				/** Without Cart */
				$appointment_data = $posted_data['appointment_data'];

				$multiple_quantity_token = !empty( $appointment_data['multiple_quantity_token'] ) ? $appointment_data['multiple_quantity_token'] : '';

				if( !empty( $multiple_quantity_token ) ){
					$token_verification = base64_decode( $multiple_quantity_token );

					$start = substr($token_verification,0,13);
					$end = strrev( $start );

					$token_verification = str_replace( $start, '', $token_verification );
					$token_verification = str_replace( $end, '', $token_verification );

					$chosen_members = $token_verification;
					
					if( !empty( $appointment_data['bookingpress_selected_bring_members'] ) ){
						$selected_service_id = $appointment_data['selected_service'];
						$selected_members = $appointment_data['bookingpress_selected_bring_members'];

						if( $selected_members != $chosen_members ){
							$response['variant'] = 'error';
							$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
							$response['msg']     = esc_html__('Sorry! Booking can not be done as the selected number of person is malformed.', 'bookingpress-appointment-booking');
							wp_send_json($response);
						}

						$service_db_min_capacity = 1;
						$service_db_min_capacity = apply_filters( 'bookingpress_retrieve_min_capacity', $service_db_min_capacity, $selected_service_id );

						if( $chosen_members < $service_db_min_capacity ){
							$response['variant'] = 'error';
							$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking' );
							$response['msg'] = esc_html( 'Sorry! Booking can not be done as service maximum capacity is malformed.', 'bookingpress-appointment-booking' );
							wp_send_json($response);
						}

						$service_db_max_capacity = 1;
						$service_db_max_capacity = apply_filters( 'bookingpress_retrieve_capacity', $service_db_max_capacity, $selected_service_id );

						if( $selected_members > $service_db_max_capacity ){
							$response['variant'] = 'error';
							$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking' );
							$response['msg'] = esc_html( 'Sorry! Booking can not be done as the maximum capacity exceed for the number of selected members', 'bookingpress-appointment-booking' );
							wp_send_json($response);
						}
					}
				}

			} else if( !empty( $posted_data['appointment_data'] ) && !empty( $posted_data['appointment_data']['cart_items'] ) ) {
				$cart_items = $posted_data['appointment_data']['cart_items'];

				foreach( $cart_items as $cart_item ){

					$multiple_quantity_token = !empty( $cart_item['multiple_quantity_token'] ) ? $cart_item['multiple_quantity_token'] : '';
					
					if( !empty( $multiple_quantity_token ) ){
						$token_verification = base64_decode( $multiple_quantity_token );
	
						$start = substr($token_verification,0,13);
						$end = strrev( $start );
	
						$token_verification = str_replace( $start, '', $token_verification );
						$token_verification = str_replace( $end, '', $token_verification );
	
						$chosen_members = $token_verification;
						
						if( !empty( $cart_item['bookingpress_bring_anyone_selected_members'] ) ){
							$selected_service_id = $cart_item['bookingpress_service_id'];
							$selected_members = $cart_item['bookingpress_bring_anyone_selected_members'];
	
							if( $selected_members != $chosen_members ){
								$response['variant'] = 'error';
								$response['title']   = esc_html__('Error', 'bookingpress-appointment-booking');
								$response['msg']     = esc_html__('Sorry! Booking can not be done as the selected number of person is malformed.', 'bookingpress-appointment-booking');
								wp_send_json($response);
							}
	
							$service_db_min_capacity = 1;
							$service_db_min_capacity = apply_filters( 'bookingpress_retrieve_min_capacity', $service_db_min_capacity, $selected_service_id );
	
							if( $chosen_members < $service_db_min_capacity ){
								$response['variant'] = 'error';
								$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking' );
								$response['msg'] = esc_html( 'Sorry! Booking can not be done as service maximum capacity is malformed.', 'bookingpress-appointment-booking' );
								wp_send_json($response);
							}
	
							$service_db_max_capacity = 1;
							$service_db_max_capacity = apply_filters( 'bookingpress_retrieve_capacity', $service_db_max_capacity, $selected_service_id );
	
							if( $selected_members > $service_db_max_capacity ){
								$response['variant'] = 'error';
								$response['title'] = esc_html__( 'Error', 'bookingpress-appointment-booking' );
								$response['msg'] = esc_html( 'Sorry! Booking can not be done as the maximum capacity exceed for the number of selected members', 'bookingpress-appointment-booking' );
								wp_send_json($response);
							}
						}
					}
				}
			}

		}
		
		function bookingpress_get_fields_with_index(){
			global $wpdb,$tbl_bookingpress_form_fields;
			$bookingpress_form_fields = $wpdb->get_results("SELECT bookingpress_field_options,bookingpress_form_field_id,bookingpress_field_position FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_field_position ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
			$bookingpress_fields_with_order = array();
			if(!empty($bookingpress_form_fields)){				
				$i=1;
				foreach($bookingpress_form_fields as $key=>$filed){	
					$bookingpress_field_options = $filed['bookingpress_field_options'];
					$has_parent_field = true;
					if(!empty($bookingpress_field_options)){
						$bookingpress_field_options_arr = json_decode($bookingpress_field_options,true);
						if(is_array($bookingpress_field_options_arr)){
							if(isset($bookingpress_field_options_arr['parent_field']) && !empty($bookingpress_field_options_arr['parent_field'])){
								$has_parent_field = false;
							}							
						}	
					}
					if($has_parent_field){
						$bookingpress_fields_with_order[$filed['bookingpress_form_field_id']] = $filed['bookingpress_field_position']; 	
						$i++;
					}
				}

			}
			return $bookingpress_fields_with_order;
		}

		/**
		 * Function for removed repeater fileds
		 *
		 * @param  mixed $addon_key
		 * @return void
		*/
		function bookingpress_removed_repeater_filed_func($addon_key = ""){
			global $wpdb,$tbl_bookingpress_form_fields;
			if($addon_key == "bookingpress_bring_anyone_with_you_module"){

				//Get custom fields
				$bookingpress_form_fields = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_form_fields} ORDER BY bookingpress_field_position ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
				$bookingpress_form_fields_org = $bookingpress_form_fields;
				$bookingpress_new_filed_added = array();
				if(!empty($bookingpress_form_fields)){
					$bookingpress_fields_with_order = array();
					$i=1;
					foreach($bookingpress_form_fields as $key=>$filed){	
						$bookingpress_field_options = $filed['bookingpress_field_options'];
						$has_parent_field = true;
						if(!empty($bookingpress_field_options)){
							$bookingpress_field_options_arr = json_decode($bookingpress_field_options,true);
							if(is_array($bookingpress_field_options_arr)){
								if(isset($bookingpress_field_options_arr['parent_field']) && !empty($bookingpress_field_options_arr['parent_field'])){
									$has_parent_field = false;
								}							
							}	
						}
						if($has_parent_field){
							$bookingpress_fields_with_order[$filed['bookingpress_form_field_id']] = $filed['bookingpress_field_position']; 	
							$i++;
						}
					}
					foreach($bookingpress_form_fields as $key=>$filed){					
						if($filed['bookingpress_form_field_name'] == "Repeater"){
							$inner_field_id = array();
							$bookingpress_field_options = $filed['bookingpress_field_options'];
							if(!empty($bookingpress_field_options)){
								$bookingpress_repeater_field_options = json_decode($bookingpress_field_options,true);
								if(is_array($bookingpress_repeater_field_options) && !empty($bookingpress_repeater_field_options['inner_fields'])){

									foreach($bookingpress_repeater_field_options['inner_fields'] as $field_key=>$field_value){
										$is_blank = (isset($field_value['is_blank']))?$field_value['is_blank']:'';										
										if(($is_blank == 'false' || $is_blank == false) || $is_blank == ''){
											
											$id = (isset($field_value['id']))?$field_value['id']:'';
											$id = str_replace( 'inner_field_', '', $id);
											$inner_field_id[] = $id;

										}
									}																
								}							
							}
							$bookingpress_new_filed_added[] = array('repeater_id'=>$filed['bookingpress_form_field_id'],'inner_fields'=>$inner_field_id);
						}					
					}


					if(!empty($bookingpress_new_filed_added)){
						foreach($bookingpress_new_filed_added as $repeater_field){
							$repeater_id = $repeater_field['repeater_id'];
							$bookingpress_fields_with_order = $this->bookingpress_get_fields_with_index();	
							//print_r($bookingpress_fields_with_order); die;					
							$repeater_field_position = (isset($bookingpress_fields_with_order[$repeater_id]))?$bookingpress_fields_with_order[$repeater_id]:0;
							if($repeater_id){

								$wpdb->delete(
									$tbl_bookingpress_form_fields,
									array(
										'bookingpress_form_field_id' => $repeater_id
									)
								);							
								if(!empty($repeater_field['inner_fields'])){								
									foreach($repeater_field['inner_fields'] as $inner_filed_id){
										if($inner_filed_id){	
											$p = $repeater_field_position;									
											$repeater_inner_fields = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $inner_filed_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
											if(!empty($repeater_inner_fields)){
												if(!empty($repeater_inner_fields['bookingpress_field_options'])){
													$sub_bookingpress_field_options_arr = json_decode($repeater_inner_fields['bookingpress_field_options'],true);	
													//parent_field
													if(isset($sub_bookingpress_field_options_arr['parent_field'])){
														unset($sub_bookingpress_field_options_arr['parent_field']);
													}
													if(isset($sub_bookingpress_field_options_arr['innerIndex'])){
														unset($sub_bookingpress_field_options_arr['innerIndex']);													
													}
													
													$wpdb->update(
														$tbl_bookingpress_form_fields,
														array(
															'bookingpress_field_options' => wp_json_encode( $sub_bookingpress_field_options_arr ),
															'bookingpress_field_position' => (int)$p,
														),
														array(
															'bookingpress_form_field_id' => $inner_filed_id,
														)
													);
													
													$p++;
												}
											}
										}
										$p++;
									}

									/* Update all field index */
									$total_fields = count($repeater_field['inner_fields']);
									foreach($bookingpress_fields_with_order as $key=>$val){
										if($repeater_field_position > $val){

											$new_position = $val+$total_fields;																				
											$wpdb->update(
												$tbl_bookingpress_form_fields,
												array(												
													'bookingpress_field_position' => $new_position,
												),
												array(
													'bookingpress_form_field_id' => $key,
												)
											);										

										}
									}

								}							
							}
						}
					}

					/* Reset inner field index */
					$bookingpress_all_form_fields = $wpdb->get_results("SELECT bookingpress_field_position, bookingpress_field_options FROM {$tbl_bookingpress_form_fields} WHERE  bookingpress_field_type IN ('2_col','3_col','4_col')  ORDER BY bookingpress_field_position ASC", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm 
					if(!empty($bookingpress_all_form_fields)){
						foreach($bookingpress_all_form_fields as $field_val){
							
							$bookingpress_field_position = (isset($field_val['bookingpress_field_position']))?$field_val['bookingpress_field_position']:0;
							$bookingpress_field_options = (isset($field_val['bookingpress_field_options']))?$field_val['bookingpress_field_options']:'';
							if(!empty($bookingpress_field_options)){
								$bookingpress_field_options_new_arr = json_encode($bookingpress_field_options,true);
								if(!empty($bookingpress_field_options_new_arr['inner_fields'])){
									$inner_pos_data = 0.1;
									foreach($bookingpress_field_options_new_arr['inner_fields'] as $inner_sub_field){

										$is_blank = (isset($inner_sub_field['is_blank']))?$inner_sub_field['is_blank']:'';
										if($is_blank != 'true' && $is_blank != true){										
											$id = (isset($inner_sub_field['id']))?$inner_sub_field['id']:'';
											$id = str_replace( 'inner_field_', '', $id);										
											$has_inner_fields = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_field_options FROM `{$tbl_bookingpress_form_fields}` WHERE bookingpress_form_field_id = %d", $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
											if($has_inner_fields){
												$inner_sub_field_position = $bookingpress_field_position + $inner_pos_data;											
												$wpdb->update(
													$tbl_bookingpress_form_fields,
													array(												
														'bookingpress_field_position' => $inner_sub_field_position,
													),
													array(
														'bookingpress_form_field_id' => $id,
													)
												);											
												$inner_pos_data = $inner_pos_data + 0.1;
											}										
										}
									}
								}
							}
						}
					}

				}
			}
		}
		
		/**
		 * Function for get guest data in MyBooking Page
		 *
		 * @return void
		*/
		function bookingpress_my_booking_display_guest_data_func(){
			global $BookingPress;
			$bookingpress_guests_section_title = $BookingPress->bookingpress_get_customize_settings('guests_section_title', 'booking_my_booking');
			$bookingpress_guest_field_title = $BookingPress->bookingpress_get_customize_settings('guest_field_title', 'booking_my_booking');
		?>	
		<div v-if="0 < scope.row.bookingpress_guest_data.length">	
			<div v-for="guest_custom_fields_repeter in scope.row.bookingpress_guest_data" class="bpa-ma-vac--payment-details bpa-ma-vac--guest-details">
				<div class="bpa-ma-vac-sec-title bpa-ma-vac-sec-mtitle">{{guest_custom_fields_repeter.repeater_label}}</div>
				<el-row>
					<el-col v-for="guest_custom_fields in guest_custom_fields_repeter.repeater_data" :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<div class="bpa-ma-vac-sec-title bpa-ma-vac-sec-title-no-border">{{guest_custom_fields_repeter.repeater_label}} {{guest_custom_fields.guest_no}}</div>	
						<el-row class="bpa-guest-row">
							<el-col class="bpa-vac-bd__row bpa-vac-guest-bd__row" v-if="0 < guest_custom_fields.guest_data.length" :xs="24" :sm="24" :md="12" :lg="12" :xl="12" v-for="custom_fields in guest_custom_fields.guest_data">
								<div class="bpa-bd__item">
									<div class="bpa-item--label" v-html="custom_fields.label"></div>
									<div class="bpa-item--val" v-html="custom_fields.value"></div>
								</div>							
							</el-col>
						</el-row>
					</el-col>
				</el-row>			
			</div>
		</div>		
		<?php 
		}
				
		/**
		 * Function for get guest data in backend appointment list
		 *
		 * @return void
		 */
		function bookingpress_backend_display_guest_data_func(){			
		?>
		<div v-if="scope.row.bookingpress_guest_data != 'undefined' && scope.row.bookingpress_guest_data.length > 0">
			<div class="bpa-vac-body--custom-fields" v-for="guest_custom_fields_repeter in scope.row.bookingpress_guest_data">
				<h4 class="bpa-vac__sec-heading">{{guest_custom_fields_repeter.repeater_label}}</h4>
				<div class="bpa-cf__body">
					<el-row>
						<el-col v-for="guest_custom_fields in guest_custom_fields_repeter.repeater_data" :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<b class="bpa-guest-title">{{guest_custom_fields_repeter.repeater_label}} {{guest_custom_fields.guest_no}}</b>	
							<el-row class="bpa-guest-row">
								<el-col v-if="guest_custom_fields.guest_data != 'undefined' && guest_custom_fields.guest_data.length > 0" :xs="24" :sm="24" :md="12" :lg="12" :xl="12" v-for="custom_fields in guest_custom_fields.guest_data">
									<div class="bpa-bd__item">
										<div class="bpa-bd__item-head">
											<span v-html="custom_fields.label"></span>
										</div>
										<div class="bpa-bd__item-body">
											<h4 v-html="custom_fields.value"></h4>
										</div>
									</div>
								</el-col>
							</el-row>
						</el-col>
					</el-row>
				</div>
			</div>
		</div>	
		<?php 
		}

		/**
		 * Function for get guest data 
		 *
		 * @param  mixed $bookingpress_appointment_id
		 * @return void
		 */
		function bookingpress_get_appointment_guest_data_func($bookingpress_appointment_id,$is_my_booking = false){
			
			global $wpdb,$tbl_bookingpress_guests_data,$tbl_bookingpress_form_fields,$bookingpress_global_options,$BookingPressPro;
			$bookingpress_global_data = $bookingpress_global_options->bookingpress_global_options();
			$default_date_format = $bookingpress_global_data['wp_default_date_format'];           
			$default_time_format = $bookingpress_global_data['wp_default_time_format']; 
			$bookingpress_appointment_guest_data = array();
			$bookingpress_appointment_guest_key_data = array();
			$bookingpress_guest_details = $wpdb->get_results($wpdb->prepare( "SELECT * FROM ".$tbl_bookingpress_guests_data." Where bookingpress_guest_data_appointment_id = %d  Order By bookingpress_guest_data_id ASC", $bookingpress_appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_guests_data is a table name. false alarm
			if(!empty($bookingpress_guest_details)){
				foreach($bookingpress_guest_details as $key=>$guest_details){
					$bookingpress_appointment_guest_key_data[$guest_details['bookingpress_guest_data_repeater_id']][$guest_details['bookingpress_guest_data_guest_no']][$guest_details['bookingpress_guest_data_field_metakey']] = $guest_details['bookingpress_guest_data_field_metavalue'];					 
				}
			}			
			if(!empty($bookingpress_appointment_guest_key_data)){
				foreach($bookingpress_appointment_guest_key_data as $key_repeter=>$guest_data_repeater){
					$single_repeater_data = array();
					$bookingpress_form_field_repeater_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_field_label,bookingpress_form_field_id FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_id = %s", $key_repeter), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
					$guest_label = __('Guest', 'bookingpress-appointment-booking');
					if(!empty($bookingpress_form_field_repeater_data)){
						$guest_label = (isset($bookingpress_form_field_repeater_data['bookingpress_field_label']))?$bookingpress_form_field_repeater_data['bookingpress_field_label']:'';
						if(isset($bookingpress_form_field_repeater_data['bookingpress_form_field_id'])){
							if(is_plugin_active('bookingpress-multilanguage/bookingpress-multilanguage.php')) {
								if(method_exists( $BookingPressPro, 'bookingpress_pro_front_language_translation_func') ) {
									$guest_label = $BookingPressPro->bookingpress_pro_front_language_translation_func($guest_label,'custom_form_fields','bookingpress_field_label',$bookingpress_form_field_repeater_data['bookingpress_form_field_id']);							                
								}
							}
						}
					}
					foreach($guest_data_repeater as $key=>$guest_data){						
						$bookingpress_appointment_custom_meta_values = array();	
						foreach($guest_data as $meta_key=>$v4){

							$bookingpress_form_field_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_field_label,bookingpress_field_type,bookingpress_field_options,bookingpress_field_values FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_meta_key = %s AND bookingpress_field_type != %s AND bookingpress_field_type != %s AND bookingpress_field_type != %s", $meta_key, '2_col', '3_col', '4_col', 'repeater'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is table name.
							$bookingpress_field_label = !empty($bookingpress_form_field_data['bookingpress_field_label']) ? stripslashes_deep($bookingpress_form_field_data['bookingpress_field_label']) : '';
							if(!empty($bookingpress_field_label)){							
								$bookingpress_field_type = $bookingpress_form_field_data['bookingpress_field_type'];
								if( !empty($bookingpress_field_type) && 'checkbox' == $bookingpress_field_type ){
									$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => is_array($v4) ? implode(',', $v4) : '' );
								} elseif(!empty($bookingpress_field_type) && !empty($v4) && 'date' == $bookingpress_field_type ) {
									$bookingpress_field_options = json_decode($bookingpress_form_field_data['bookingpress_field_options'],true);
									if(!empty($bookingpress_field_options['enable_timepicker']) && $bookingpress_field_options['enable_timepicker'] == 'true') {
										$default_date_time_format = $default_date_format.' '.$default_time_format;
										$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => date($default_date_time_format,strtotime($v4)));
									} else {
										$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => date($default_date_format,strtotime($v4)));
									}
								} else if( !empty( $bookingpress_field_type ) && 'file' == $bookingpress_field_type ) {
									$file_name_data = explode( '/', $v4 );
									$file_name = end( $file_name_data );
									
									$bookingpress_appointment_custom_meta_values[] = array(
										'label' => $bookingpress_field_label,
										'value' => '<a href="' . esc_url( $v4 ) . '" target="_blank">'.$file_name.'</a>'
									);
								} else {
									$bookingpress_appointment_custom_meta_values[] = array('label' => $bookingpress_field_label, 'value' => $v4);
								}														
							}
	
						}
						if(!empty($bookingpress_appointment_custom_meta_values)){
							$single_repeater_data[] = array('guest_no'=>$key,'guest_data'=>$bookingpress_appointment_custom_meta_values);
						}							
					}
					if(!empty($single_repeater_data)){
						$bookingpress_appointment_guest_data[] = array('repeater_label'=>$guest_label,'repeater_data'=>$single_repeater_data);
					}						
				}
			}			
			return $bookingpress_appointment_guest_data;

		}

		/**
		 * Function for removed custom field repeter data
		 *
		 * @param  mixed $bookingpress_meta_value
		 * @param  mixed $bookingpress_appointment_id
		 * @return void
		 */
		function bookingpress_removed_repeater_data_in_custom_fields_func($bookingpress_meta_value, $bookingpress_appointment_id = 0){
			global $wpdb,$tbl_bookingpress_guests_data;

			$bookingpress_assigned_service_details = $wpdb->get_results($wpdb->prepare( "SELECT bookingpress_guest_data_field_metakey FROM ".$tbl_bookingpress_guests_data." Where bookingpress_guest_data_appointment_id = %d AND bookingpress_guest_data_guest_no = %d", $bookingpress_appointment_id,1), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason: $tbl_bookingpress_guests_data is a table name. false alarm
			if(!empty($bookingpress_assigned_service_details)){
				foreach($bookingpress_assigned_service_details as $bookingpress_guest_data){
					if(isset($bookingpress_meta_value[$bookingpress_guest_data['bookingpress_guest_data_field_metakey']])){
						unset($bookingpress_meta_value[$bookingpress_guest_data['bookingpress_guest_data_field_metakey']]);
					}	
				}
			}
			return $bookingpress_meta_value;
		}


		/**
		 * Function for add appointment meta data in guest table for repeater field
		 *
		 * @param  mixed $appointment_id
		 * @param  mixed $entry_id
		 * @param  mixed $payment_gateway_data
		 * @return void
		 */
		function bookingpress_after_book_appointment_fun( $appointment_id, $entry_id = '', $payment_gateway_data = array() ){

            global $tbl_bookingpress_entries,$wpdb,$tbl_bookingpress_appointment_meta,$BookingPress,$tbl_bookingpress_appointment_bookings;
            $tbl_bookingpress_entries_meta = $wpdb->prefix . 'bookingpress_entries_meta';
			if( empty( $appointment_id ) && empty($entry_id)){                
				return;
			}            
            $entry_id = $wpdb->get_var($wpdb->prepare("SELECT bookingpress_entry_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id)); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
            $entry_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_entry_meta_value FROM {$tbl_bookingpress_entries_meta} WHERE bookingpress_entry_id = %d AND bookingpress_entry_meta_key = 'bookingpress_repeater_data'", $entry_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries_meta is table name defined globally. False Positive alarm
			if(!empty($entry_data)){

				$tbl_bookingpress_guests_data = $wpdb->prefix . 'bookingpress_guests_data';
                $bookingpress_repeater_data = (isset($entry_data['bookingpress_entry_meta_value']))?$entry_data['bookingpress_entry_meta_value']:'';
                if(!empty($bookingpress_repeater_data)){
					$bookingpress_repeater_data_arr = json_decode($bookingpress_repeater_data,true);
					if(!empty($bookingpress_repeater_data_arr)){
						foreach($bookingpress_repeater_data_arr as $key=>$repeter_data){

							$bookingpress_db_fields = array(
								'bookingpress_guest_data_appointment_id' => $appointment_id,
								'bookingpress_guest_data_repeater_id' => (isset($repeter_data['repeater_id']))?$repeter_data['repeater_id']:0,
								'bookingpress_guest_data_guest_no' => (isset($repeter_data['guest_no']))?$repeter_data['guest_no']:0,
								'bookingpress_guest_data_field_metakey' => (isset($repeter_data['meta_key']))?$repeter_data['meta_key']:'',
								'bookingpress_guest_data_field_metavalue' => (isset($repeter_data['meta_value']))?$repeter_data['meta_value']:'',
							);
							$wpdb->insert($tbl_bookingpress_guests_data, $bookingpress_db_fields);							

						}
					}

				}

			}

		}

        /**
         * Function for add repeater entry meta data
         *
         * @return void
         */
        function bookingpress_after_entry_data_insert_fun($entry_id,$posted_data){
            global $wpdb,$tbl_bookingpress_form_fields;
            if(!$entry_id){
                return;
            }
			if(isset($posted_data['bookingpress_repeater_fields_key']) && !empty($posted_data['bookingpress_repeater_fields_key'])){

				$total_members = $posted_data['bookingpress_selected_bring_members'];
				$all_field_data = (isset($posted_data['form_fields']))?$posted_data['form_fields']:array();
				$bookingpress_repeater_fields_key = $posted_data['bookingpress_repeater_fields_key'];
				$bookingpress_repeater_data = array();
				if(!empty($bookingpress_repeater_fields_key)){
					foreach($bookingpress_repeater_fields_key as $repeter_key=>$repeter_data){

						$repeater_id = str_replace("repeater_","",$repeter_key);
						
						if(is_array($repeter_data) && !empty($repeter_data)){
							foreach($repeter_data as $meta_key=>$repeater_meta_key){
								$guest_no = 1;
								$field_meta_value = (isset($all_field_data[$repeater_meta_key]))?$all_field_data[$repeater_meta_key]:'';
								$single_repeater_data = array(
									'repeater_id' => $repeater_id,
									'guest_no'    => $guest_no,
									'meta_key'    => $meta_key,
									'meta_value'  => $field_meta_value,
								);
								$bookingpress_repeater_data[] = $single_repeater_data;
								for($i=1;$i<$total_members;$i++){
									$guest_no = $i+1;
									$repeater_meta_key_new = $repeater_meta_key.$i; 
									$field_meta_value = (isset($all_field_data[$repeater_meta_key_new]))?$all_field_data[$repeater_meta_key_new]:'';
									$single_repeater_data = array(
										'repeater_id' => $repeater_id,
										'guest_no'    => $guest_no,
										'meta_key'    => $meta_key,
										'meta_value'  => $field_meta_value,
									);
									$bookingpress_repeater_data[] = $single_repeater_data;
								}

							}	
						}
					}
				}

				if(!empty($bookingpress_repeater_data)){

                    $tbl_bookingpress_entries_meta = $wpdb->prefix . 'bookingpress_entries_meta';
                    $bookingpress_db_fields = array(
                        'bookingpress_entry_id' => $entry_id,                        
                        'bookingpress_entry_meta_key' => 'bookingpress_repeater_data',
                        'bookingpress_entry_meta_value' => json_encode($bookingpress_repeater_data),
                    );
                    $wpdb->insert($tbl_bookingpress_entries_meta, $bookingpress_db_fields);   					
				}

			}
        }

		function bookingpress_quantity_methods( $bookingpress_vue_methods_data ){

			$bookingpress_vue_methods_data .= '
				bookingpress_update_qty_token(){
					const vm = this;
					const myVar = Error().stack;
					if( /change/.test( myVar ) ){
						let members = vm.appointment_step_form_data.bookingpress_selected_bring_members;
						let uniqueId = vm.appointment_step_form_data.bookingpress_uniq_id;
						let uniqueId2 = uniqueId.split("").reverse().join("");
						let salt = `${uniqueId}${members}${uniqueId2}`;
						let token = btoa( salt );
						if( "undefined" != typeof vm.bookingpress_cart_addon && 1 == vm.bookingpress_cart_addon && "undefined" != vm.appointment_step_form_data.cart_item_edit_index && -1 < vm.appointment_step_form_data.cart_item_edit_index ){
							vm.appointment_step_form_data.cart_items[ vm.appointment_step_form_data.cart_item_edit_index ].multiple_quantity_token = token;
						} else {
							vm.appointment_step_form_data.multiple_quantity_token = token;
						}
					}
				},
			';

			return $bookingpress_vue_methods_data;
		}

		/**
		 * For repeate fields
		 *
		 * @param  mixed $bookingpress_vue_methods_data
		 * @return void
		 */
		function bookingpress_repeat_form_fields( $bookingpress_vue_methods_data ){

			$bookingpress_vue_methods_data .= '
				bookingpress_repeat_custom_form_fields(){
					const vm = this;
					let is_cart_active = ( "undefined" != typeof vm.bookingpress_cart_addon ) ? vm.bookingpress_cart_addon : false;
					if( false == is_cart_active ){
						vm.bookingpress_new_form_fields = {};
						vm.bookingpress_new_form_field_rules = {};
						vm.bookingpress_repeter_count = vm.appointment_step_form_data.bookingpress_selected_bring_members;
						let total_quantity = parseInt(vm.appointment_step_form_data.bookingpress_selected_bring_members);
						var customer_form_fields_final = vm.customer_form_fields;
						if(vm.customer_details_rule_original == ""){
							var customer_details_rule_final = vm.customer_details_rule;
							vm.customer_details_rule_original = JSON.parse(JSON.stringify(customer_details_rule_final));
						}
						vm.customer_details_rule = JSON.parse(JSON.stringify(vm.customer_details_rule_original));
						
						if(vm.customer_form_fields_original == ""){
							vm.customer_form_fields_original = JSON.parse(JSON.stringify(customer_form_fields_final));
						}
						setTimeout(function(){
							if(vm.$refs.appointment_step_form_data != undefined){
								vm.$refs.appointment_step_form_data.clearValidate();
							}
						}, 500);						
						let update_repeater_label = [];
						let n = vm.customer_form_fields_original.length;
						vm.new_repeater_fields = {};
						vm.customer_form_fields = JSON.parse(JSON.stringify(vm.customer_form_fields_original));

						if( 1 < total_quantity ){
							for( let r = 1; r < total_quantity; r++ ){	
								vm.customer_form_fields.forEach( (element,index) =>{
									if( "Custom" == element.field_type && "Repeater" == element.field_name ){
										let newKey = parseInt( index ) + r;
										let newObj = JSON.parse( JSON.stringify( element ) );
										vm.$set( vm.bookingpress_new_form_fields, newKey, newObj );

										var org_label = "";
										if(typeof element.org_label == "undefined"){																						
											vm.customer_form_fields[index]["org_label"] = element.label;
											org_label = element.label;
											vm.$set( vm.customer_form_fields[index], "label", `${org_label} 1` );
										}else{
											org_label = vm.customer_form_fields[index]["org_label"];
										}

										let new_model_value = vm.bookingpress_new_form_fields[newKey].v_model_value + r;
										let new_meta_key = vm.bookingpress_new_form_fields[newKey].meta_key + r;

										vm.$set( vm.bookingpress_new_form_fields[newKey], "v_model_value", new_model_value );
										vm.$set( vm.bookingpress_new_form_fields[newKey], "meta_key", new_meta_key );
										let newLabelKey = r + 1;										

										vm.$set( vm.bookingpress_new_form_fields[ newKey ], "label", `${org_label} ${newLabelKey}` );
									}
								});
							}
							
							if( 0 < Object.keys( vm.bookingpress_new_form_fields ).length ){
								let in_ind = 1;
								for( let newIndex in vm.bookingpress_new_form_fields ){
									let newElm = vm.bookingpress_new_form_fields[ newIndex ];
	
									newElm.field_options.inner_fields.forEach( ( elm, ind ) => {

										let oldKey = vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].v_model_value;
										let new_v_model = vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].v_model_value + ( in_ind );
										
										if( "2_col" == elm.field_type || "3_col" == elm.field_type || "4_col" == elm.field_type ) {
											/** Multi column */
											elm.field_options.inner_fields.forEach( (in_elm, in_index) => {

												let oldKey = vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index].v_model_value;
												let new_v_model = vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index].v_model_value + ( in_ind );

												if( "File" == elm.field_type ){
													let ref_name = vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index].bpa_ref_name + in_ind;
													let bpa_ref = vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index].bpa_action_data.bpa_ref + in_ind;
													let org_field_key = vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index].bpa_action_data.field_key;
													let field_key = org_field_key + in_ind;
	
													vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index], "bpa_ref_name", ref_name );
													vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index].bpa_action_data, "bpa_ref", bpa_ref );
													vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index].bpa_action_data, "field_key_org", org_field_key );
													vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index].bpa_action_data, "field_key", field_key );
												}

												if( "Checkbox" == in_elm.field_type ){	
													vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index], "v_model_value", new_v_model );
													vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index], "meta_key", new_v_model );
		
													vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index].field_values.forEach( (chk_elm, chk_ind) => {
														let old_key = `v_model_value_${oldKey}_${chk_ind}`;
														delete vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index][old_key];
		
														let newKey = `v_model_value_${new_v_model}_${chk_ind}`;
														vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ ind ].field_options.inner_fields[in_index], newKey, {} );
		
														let chk_elm_value = chk_elm.value;
		
														vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ ind ].field_options.inner_fields[in_index][ newKey ], `${new_v_model}_${chk_ind}`, `${new_v_model}_${chk_elm_value}` );
		
													});
													vm.$set( vm.appointment_step_form_data.form_fields, new_v_model, [] );
		
												} else {
													vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index], "v_model_value", new_v_model );
													vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index], "meta_key", new_v_model );
												}
												if( "undefined" != typeof vm.customer_details_rule[ oldKey ] ){
													vm.$set( vm.bookingpress_new_form_field_rules, vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_options.inner_fields[in_index].v_model_value, JSON.parse( JSON.stringify( vm.customer_details_rule[ oldKey ] ) ) );
												}
											});
										} else {

											if( "File" == elm.field_type ){
												let ref_name = vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].bpa_ref_name + in_ind;
												let bpa_ref = vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].bpa_action_data.bpa_ref + in_ind;
												let org_field_key = vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].bpa_action_data.field_key;
												let field_key = org_field_key + in_ind;

												vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind], "bpa_ref_name", ref_name );
												vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].bpa_action_data, "bpa_ref", bpa_ref );
												vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].bpa_action_data, "field_key_org", org_field_key );
												vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].bpa_action_data, "field_key", field_key );
											}

											if( "Checkbox" == elm.field_type ){	
												vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind], "v_model_value", new_v_model );
												vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind], "meta_key", new_v_model );
	
												vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].field_values.forEach( (chk_elm, chk_ind) => {
													let old_key = `v_model_value_${oldKey}_${chk_ind}`;
													delete vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind][old_key];
	
													let newKey = `v_model_value_${new_v_model}_${chk_ind}`;
													vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ ind ], newKey, {} );
	
													let chk_elm_value = chk_elm.value;
	
													vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ ind ][ newKey ], `${new_v_model}_${chk_ind}`, `${new_v_model}_${chk_elm_value}` );
	
												});
												vm.$set( vm.appointment_step_form_data.form_fields, new_v_model, [] );
	
											} else {
												vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind], "v_model_value", new_v_model );
												vm.$set( vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind], "meta_key", new_v_model );
											}
											if( "undefined" != typeof vm.customer_details_rule[ oldKey ] ){
												vm.$set( vm.bookingpress_new_form_field_rules, vm.bookingpress_new_form_fields[ newIndex ].field_options.inner_fields[ind].v_model_value, JSON.parse( JSON.stringify( vm.customer_details_rule[ oldKey ] ) ) );
											}
										}
									});
									in_ind++;
									vm.customer_form_fields.splice( newIndex, 0, newElm );
								}
								if( "" != vm.bookingpress_new_form_field_rules ){	
									
									let oldRuleObj = JSON.parse( JSON.stringify( vm.customer_details_rule ) );
									let newRuleObj = JSON.parse( JSON.stringify( vm.bookingpress_new_form_field_rules ) );
									let mergedRules = {...oldRuleObj, ...newRuleObj};
									vm.$set( vm, "customer_details_rule", mergedRules );
									setTimeout(function(){
										if(vm.$refs.appointment_step_form_data != undefined){
											vm.$refs.appointment_step_form_data.clearValidate();
										}
									}, 500);									
								}								
							}
						}
					}
					vm.$forceUpdate();
				},
				bookingpress_repeat_custom_form_fields_legacy(){
					const vm = this;
					let is_cart_active = ( "undefined" != typeof vm.bookingpress_cart_addon ) ? vm.bookingpress_cart_addon : false;
					if( false == is_cart_active ){
						vm.bookingpress_repeter_count = vm.appointment_step_form_data.bookingpress_selected_bring_members;
						let total_quantity = parseInt(vm.appointment_step_form_data.bookingpress_selected_bring_members);
						let form_fields_to_repeat = {};
						var customer_form_fields_final = vm.customer_form_fields;
						if(vm.customer_details_rule_original == ""){
							var customer_details_rule_final = vm.customer_details_rule;
							vm.customer_details_rule_original = customer_details_rule_final;
						}
						if(vm.customer_form_fields_original == ""){
							vm.customer_form_fields_original = customer_form_fields_final;
						}
						let n = vm.customer_form_fields_original.length;
						vm.new_repeater_fields = {};
						vm.customer_form_fields = vm.customer_form_fields_original;
						if(total_quantity > 1){
							let form_fields_update = JSON.parse(JSON.stringify(vm.customer_form_fields));
							let form_fields = JSON.parse(JSON.stringify(vm.customer_form_fields));
							let form_customer_details_rule = "";
							if(vm.customer_details_rule.length != 0){
								form_customer_details_rule = JSON.parse(JSON.stringify(vm.customer_details_rule));
							}
							var p = 0;						
							for( let f in form_fields ){							
								let fdetails = form_fields[f];
								if(fdetails.field_name == "Repeater"){
									let flabel = 2;
									let old_label = form_fields_update[f].label;
									if(typeof form_fields_update[f].label != "undefined"){
										vm.customer_form_fields[f].label = form_fields[f].label+" 1";
									}
									for( let r = 1; r < total_quantity; r++ ){
										let new_repeater_field = {...fdetails};																			
										if(typeof new_repeater_field.field_options != "undefined"){
											if(typeof new_repeater_field.field_options.inner_fields != "undefined"){												
												var has_check = true;
												if(typeof new_repeater_field.field_options.recent_added != "undefined"){
													if(new_repeater_field.field_options.recent_added == r){
														has_check = false;
													}												
												}
												if(new_repeater_field.field_options.inner_fields.length != 0 && has_check){
													if(typeof new_repeater_field.label != "undefined"){
														new_repeater_field.label = old_label+" "+(r+1);													
													}
													for( let first_lvl in new_repeater_field.field_options.inner_fields){													
														if(typeof new_repeater_field.field_options.inner_fields[first_lvl] != "undefined"){														
															
															if(typeof new_repeater_field.field_options.inner_fields[first_lvl].field_options != "undefined"){
																if(typeof new_repeater_field.field_options.inner_fields[first_lvl].field_options.layout != "undefined"){
																	if(new_repeater_field.field_options.inner_fields[first_lvl].field_options.layout != "2col" && new_repeater_field.field_options.inner_fields[first_lvl].field_options.layout != "3col" && new_repeater_field.field_options.inner_fields[first_lvl].field_options.layout != "4col"){
																		if(typeof new_repeater_field.field_options.inner_fields[first_lvl].v_model_value != "undefined"){
																			var v_model_value_key = new_repeater_field.field_options.inner_fields[first_lvl].v_model_value;

																			var field_type = new_repeater_field.field_options.inner_fields[first_lvl].field_type;
																			if(field_type == "File"){
																				new_repeater_field.field_options.inner_fields[first_lvl].bpa_ref_name = new_repeater_field.field_options.inner_fields[first_lvl].bpa_ref_name+r;
																				new_repeater_field.field_options.inner_fields[first_lvl].bpa_action_data.bpa_ref = new_repeater_field.field_options.inner_fields[first_lvl].bpa_action_data.bpa_ref+r;

																				new_repeater_field.field_options.inner_fields[first_lvl].bpa_action_data["field_key_org"] = new_repeater_field.field_options.inner_fields[first_lvl].bpa_action_data.field_key;
																				
																				new_repeater_field.field_options.inner_fields[first_lvl].bpa_action_data.field_key = new_repeater_field.field_options.inner_fields[first_lvl].bpa_action_data.field_key+r;

																				new_repeater_field.field_options.inner_fields[first_lvl].meta_key = new_repeater_field.field_options.inner_fields[first_lvl].meta_key+r;
																			}
																			if(field_type == "Checkbox"){
																				
																				var new_key = v_model_value_key + r;
																				console.log( vm.customer_details_rule[v_model_value_key] );
																				vm.appointment_step_form_data.form_fields[ new_key ] = vm.$data.appointment_step_form_data.form_fields[v_model_value_key];
																				
																				var checkbox_field_values = new_repeater_field.field_options.inner_fields[first_lvl].field_values;
																				if(checkbox_field_values.length != 0){
																					checkbox_field_values.forEach( ( checkbox_data, index) => {
																						var checkbox_value = checkbox_data.value;
																						var checkbox_field_key = new_key+"_"+index;
																						let old_key = `v_model_value_${v_model_value_key}_${index}`;
																						delete new_repeater_field.field_options.inner_fields[first_lvl][ old_key ];

																						var checkbox_field_val = new_key+"_"+checkbox_value;
																						new_repeater_field.field_options.inner_fields[first_lvl]["v_model_value_"+new_key+"_"+index] = {[checkbox_field_key]:checkbox_field_val};
																					});
																				}
																			}

																			new_repeater_field.field_options.inner_fields[first_lvl].v_model_value = new_repeater_field.field_options.inner_fields[first_lvl].v_model_value+r;
																			//new_repeater_field.field_options.inner_fields[first_lvl].meta_key = new_repeater_field.field_options.inner_fields[first_lvl].meta_key+r;
																			if(form_customer_details_rule != ""){
																				if(form_customer_details_rule[v_model_value_key] != "undefined"){
																					/* if( "Checkbox" == field_type ){
																						vm.$set( app.customer_details_rule, new_key, [{
																							required: true,
																							message: "Please check at least one checkbox",
																							type: "array",
																							trigger:"change"
																						}] );
																					} else { */
																						var nkey = v_model_value_key + r;
																						//vm.$set( vm.customer_details_rule, nkey, form_customer_details_rule[v_model_value_key] );
																						//vm.customer_details_rule[v_model_value_key+r] = vm.customer_details_rule[v_model_value_key];
																						//vm.$forceUpdate();
																					//}
																				}
																			}
																			
																		}
																	}else{
																		if(typeof new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields != "undefined"){																	
																			for( let second_lvl in new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields){																			
																				if(typeof new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].v_model_value != "undefined"){
																					var v_model_value_key = new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].v_model_value;
																					var field_type = new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].field_type;
																					if(field_type == "File"){
																						
																						new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].bpa_ref_name = new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].bpa_ref_name+r;

																						new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].bpa_action_data.bpa_ref = new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].bpa_action_data.bpa_ref+r;

																						new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].bpa_action_data["field_key_org"] = new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].bpa_action_data.field_key;

																						new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].bpa_action_data.field_key = new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].bpa_action_data.field_key+r;

																						new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].meta_key = new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].meta_key+r;
																					}
																					if(field_type == "Checkbox"){
																						var new_key = v_model_value_key+r;

																						vm.appointment_step_form_data.form_fields[ new_key ] = vm.$data.appointment_step_form_data.form_fields[v_model_value_key];
																						
																						var checkbox_field_values = new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].field_values;
																						if(checkbox_field_values.length != 0){	
																																													
																							checkbox_field_values.forEach( ( checkbox_data, index) => {
																								var checkbox_value = checkbox_data.value;
																								var checkbox_field_key = new_key+"_"+index;
																								var checkbox_field_val = new_key+"_"+checkbox_value;
																								new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl]["v_model_value_"+new_key+"_"+index] = {[checkbox_field_key]:checkbox_field_val};
																							});
																							
																						}
																					}
																					new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].v_model_value = new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].v_model_value+r;
																					//new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].meta_key = new_repeater_field.field_options.inner_fields[first_lvl].field_options.inner_fields[second_lvl].meta_key+r;																				
																					/* if(form_customer_details_rule != ""){																						
																						if(form_customer_details_rule[v_model_value_key] != "undefined"){
																							//vm.customer_details_rule[v_model_value_key+r] = vm.customer_details_rule[v_model_value_key];
																							vm.customer_details_rule[v_model_value_key+r][0] = {
																								required: true,
																								message: "Please enter customer name",
																								type: "array",
																								trigger:"change"
																							};
																						}
																					} */
																				}
																			}
																		}
																	}
																}
															}
														}
													}													
													var new_update_index = parseInt(f)+1;
													new_repeater_field.field_options["recent_added"] = r;
													form_fields_to_repeat[n] = new_repeater_field;
													n++;
												}
											}	
										}																			
									}								
								}
							}
							let new_fields_data = {...vm.customer_form_fields, ...form_fields_to_repeat };						
							let new_fields_data_arr = [];
							for( let i in new_fields_data ){
								new_fields_data_arr.push( new_fields_data[i] );
							}
							new_fields_data_arr = new_fields_data_arr.sort( (a,b) =>{
								return ( parseFloat( a.field_position ) < parseFloat( b.field_position ) ) ? -1 : 1;
							});
							vm.customer_form_fields = new_fields_data_arr;							
							vm.$forceUpdate();
						}						
					}					

				},
			';

			return $bookingpress_vue_methods_data;
		}

		function bookingpress_modified_language_translate_fields_func($bookingpress_all_language_translation_fields){

			$bookingpress_bring_anyone_language_translation_fields = array(                
				'bring_anyone_title' => array('field_type'=>'text','field_label'=>__('No. of Person title', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),               
				'number_of_person_title' => array('field_type'=>'text','field_label'=>__('Person title', 'bookingpress-appointment-booking'),'save_field_type'=>'booking_form'),    
			);  
			$bookingpress_all_language_translation_fields['customized_form_service_step_labels'] = array_merge($bookingpress_all_language_translation_fields['customized_form_service_step_labels'], $bookingpress_bring_anyone_language_translation_fields);
			return $bookingpress_all_language_translation_fields;
		}

		function bookingpress_set_quantity( $bookingpress_dynamic_next_page_request_filter ){

			$bookingpress_dynamic_next_page_request_filter .= '
				if( "summary" == next_tab && "summary" == vm.bookingpress_current_tab && bookingpress_is_validate == 0 ){
					if (typeof vm.appointment_step_form_data.cart_items == "undefined") {
						if( "" == vm.appointment_step_form_data.selected_service && "undefined" != typeof app.appointment_step_form_data.selected_service && "" != app.appointment_step_form_data.selected_service ){
							vm.appointment_step_form_data.selected_service = app.appointment_step_form_data.selected_service;
						}
						let selected_service_data = vm.bookingpress_all_services_data[ vm.appointment_step_form_data.selected_service ];
						if( "undefined" != typeof selected_service_data.enable_custom_service_duration && true == selected_service_data.enable_custom_service_duration ){

						} else {
							
							let total_payable_amount = vm.appointment_step_form_data.base_price_without_currency;

							let selected_no_person = vm.appointment_step_form_data.bookingpress_selected_bring_members || 1;

							let calcualted_person_price = parseFloat( total_payable_amount ) * parseInt( selected_no_person );

							vm.appointment_step_form_data.service_price_without_currency = calcualted_person_price;

							vm.appointment_step_form_data.selected_service_price = vm.bookingpress_price_with_currency_symbol( calcualted_person_price );

							vm.use_base_price_for_calculation = false;
						}
					}
				}';

			return $bookingpress_dynamic_next_page_request_filter;
		}
		
		/**
		 * Function for add additional post data to bring anyone with you ajax request
		 *
		 * @return void
		 */
		function bookingpress_set_bawy_appointment_xhr_data(){
			?>
			if( "undefined" != typeof vm.appointment_formdata.selected_bring_members ){
				postData.appointment_data_obj.bookingpress_selected_bring_members = vm.appointment_formdata.selected_bring_members;
			}
			<?php
		}
		
		/**
		 * Function for check bring anyone module active or not
		 *
		 * @return void
		 */
		function bookingpress_check_bring_anyone_module_activation() {
			$is_bring_anyone_with_you_activated = 0;
			$deposit_payment_addon_option_val   = get_option( 'bookingpress_bring_anyone_with_you_module' );
			if ( ! empty( $deposit_payment_addon_option_val ) && ( $deposit_payment_addon_option_val == 'true' ) ) {
				$is_bring_anyone_with_you_activated = 1;
			}
			return $is_bring_anyone_with_you_activated;
		}
				
		/**
		 * Function for add dynamic field to customize page
		 *
		 * @param  mixed $bookingpress_customize_vue_data_fields
		 * @return void
		 */
		function bookingpress_customize_add_dynamic_data_fields_func($bookingpress_customize_vue_data_fields) {
			$bookingpress_customize_vue_data_fields['sevice_container_data']['bring_anyone_title'] = '';
			//$bookingpress_customize_vue_data_fields['sevice_container_data']['number_of_guest_title'] = '';
			$bookingpress_customize_vue_data_fields['sevice_container_data']['number_of_person_title'] = '';			
				
			return $bookingpress_customize_vue_data_fields;
		}
		
		/**
		 * Function for add data variables for customize page
		 *
		 * @param  mixed $booking_form_settings
		 * @return void
		 */
		function bookingpress_get_booking_form_customize_data_filter_func($booking_form_settings){
			$booking_form_settings['service_container_data']['bring_anyone_title'] = __('Select Service Extras', 'bookingpress-appointment-booking');		
			//$booking_form_settings['service_container_data']['number_of_guest_title'] = __( 'Number of guests', 'bookingpress-appointment-booking' );		
			$booking_form_settings['service_container_data']['number_of_person_title'] = __( 'Person', 'bookingpress-appointment-booking');
			
			return $booking_form_settings;
		}
		
		/**
		 * Function for calculate bring anyone with you for timeslots
		 *
		 * @param  mixed $timeslot_data
		 * @param  mixed $slot_key
		 * @return void
		 */
		function bookingpress_calculate_bawy_for_timeslot( $timeslot_data, $slot_key ){

			$total_bawy = !empty( $_POST['appointment_data_obj']['bookingpress_selected_bring_members'] ) ? intval($_POST['appointment_data_obj']['bookingpress_selected_bring_members']) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
			if( !empty( $total_bawy ) && !empty($timeslot_data[$slot_key]) ){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
				if( $total_bawy > $timeslot_data[$slot_key]['max_capacity'] ){
					$timeslot_data[$slot_key]['is_booked'] = true;
					if( !empty( $timeslot_data[$slot_key]['reason_for_not_available'] ) ){
						$timeslot_data[$slot_key]['reason_for_not_available'][] = array( 'Total Number of person with guests ' . ( $total_bawy ) );
					} else {
						$timeslot_data[$slot_key]['reason_for_not_available'] = array( 'Total Number of person with guests ' . ( $total_bawy ) );
					}
				}
			}
			return $timeslot_data;

		}
		
		/**
		 * bookingpress_set_total_bring_members
		 *
		 * @param  mixed $total_members
		 * @return void
		 */
		function bookingpress_set_total_bring_members( $total_members ){

			$total_members = !empty( $_POST['appointment_data_obj']['bookingpress_selected_bring_members'] ) ? intval($_POST['appointment_data_obj']['bookingpress_selected_bring_members']) : $total_members; // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

			return $total_members;
		}
		
		/**
		 * Function for add bring anyone with you fields at frontend form
		 *
		 * @param  mixed $bookingpress_front_vue_data_fields
		 * @return void
		 */
		function bookingpress_frontend_apointment_form_add_dynamic_data_func($bookingpress_front_vue_data_fields){
			global $BookingPress;
			$service_extra_title = $BookingPress->bookingpress_get_customize_settings('bring_anyone_title', 'booking_form');
			//$number_of_guest_title = $BookingPress->bookingpress_get_customize_settings('number_of_guest_title', 'booking_form');
			$number_of_person_title = $BookingPress->bookingpress_get_customize_settings('number_of_person_title', 'booking_form');

			$bookingpress_front_vue_data_fields['bring_anyone_title'] = !empty($service_extra_title) ? stripslashes_deep($service_extra_title) : '';
			//$bookingpress_front_vue_data_fields['number_of_guest_title'] = !empty($number_of_guest_title) ? stripslashes_deep($number_of_guest_title) : '';
			$bookingpress_front_vue_data_fields['number_of_person_title'] = !empty($number_of_person_title) ? stripslashes_deep($number_of_person_title) : '';			

			return $bookingpress_front_vue_data_fields;
		}


		function bookingpress_front_modify_cart_data_filter_func($bookingpress_appointment_details) {
			
			if(!empty($bookingpress_appointment_details['bookingpress_selected_bring_members'])) {				
				$bookingpress_appointment_details['bookingpress_selected_bring_members'] =  intval($bookingpress_appointment_details['bookingpress_selected_bring_members'] );
			}	

			return $bookingpress_appointment_details;
		}
	}
}
global $bookingpress_bring_anyone_with_you;
$bookingpress_bring_anyone_with_you = new bookingpress_bring_anyone_with_you();
