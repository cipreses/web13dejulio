<?php
global $bookingpress_global_options,$BookingPress, $bookingpress_common_date_format;
$bookingpress_common_datetime_format = $bookingpress_common_date_format . ' HH:mm';
$bpa_global_opts = $bookingpress_global_options->bookingpress_global_options();
$bookingpress_allow_tag = json_decode($bpa_global_opts['allowed_html'], true);

$bookingpress_customize_settings_arr = array(
	'reschedule_title', 'reschedule_popup_title', 'reschedule_popup_description', 'reschedule_date_label', 'reschedule_time_label', 'reschedule_time_placeholder', 'reschedule_cancel_btn_label', 'reschedule_update_btn_label'
);
$bookingpress_customize_settings_details = $BookingPress->bookingpress_get_customize_settings($bookingpress_customize_settings_arr, 'booking_my_booking');
wp_nonce_field('bpa_wp_nonce');
foreach($bookingpress_customize_settings_details as $key => $value) {
   $bookingpress_customize_settings_details[$key] = stripslashes_deep($value);
}

$reschedule_popup_title = (isset($bookingpress_customize_settings_details['reschedule_popup_title']))?stripslashes_deep($bookingpress_customize_settings_details['reschedule_popup_title']):'';
$reschedule_title = (isset($bookingpress_customize_settings_details['reschedule_title']))?stripslashes_deep($bookingpress_customize_settings_details['reschedule_title']):'';
$reschedule_popup_description = (isset($bookingpress_customize_settings_details['reschedule_popup_description']))?stripslashes_deep($bookingpress_customize_settings_details['reschedule_popup_description']):'';
$reschedule_date_label = (isset($bookingpress_customize_settings_details['reschedule_date_label']))?stripslashes_deep($bookingpress_customize_settings_details['reschedule_date_label']):'';
$reschedule_time_label = (isset($bookingpress_customize_settings_details['reschedule_time_label']))?stripslashes_deep($bookingpress_customize_settings_details['reschedule_time_label']):'';
$reschedule_time_placeholder = (isset($bookingpress_customize_settings_details['reschedule_time_placeholder']))?stripslashes_deep($bookingpress_customize_settings_details['reschedule_time_placeholder']):'';
$reschedule_update_btn_label = (isset($bookingpress_customize_settings_details['reschedule_update_btn_label']))?stripslashes_deep($bookingpress_customize_settings_details['reschedule_update_btn_label']):'';

?>
<el-main v-cloak class="bpa-appointment-reschedule_container" id="bookingpress_appointment_reschedule_form_<?php echo esc_html( $bookingpress_uniq_id ); ?>">                  
	<div  class="bpa-front-tmc__summary-content bpa-front-reschedule-content v-cloak-reschedule-hidden">				
				
		<div class="bpa-reschedule-shortcode-container">
			<div class="bpa-front-toast-notification bpa-reschedule-front-toast-notification --bpa-error" v-if="is_display_error == '1'">
				<div class="bpa-front-tn-body">
					<p>{{ is_error_msg }}</p>
				</div>
			</div>	 
			<div class="bpa-front-toast-notification bpa-reschedule-front-toast-notification --bpa-success --bpa-reschedule-success" v-if="is_display_success == '1'">
				<div class="bpa-front-tn-body">
					<p>{{ is_success_msg }}</p>
				</div>
			</div>		
			<div v-if="is_success_msg == ''" class="bpa-reschedule-form-data">
				<div class="bpa-front-reschedule-heading">
					<div class="bpa-front-module-heading"><?php echo esc_html($reschedule_popup_title); ?></div>
					<div class="bpa-front-cp-rd__desc"><?php echo esc_html($reschedule_popup_description); ?></div>
				</div>			
				<el-form @submit.native.prevent :model="bookingpress_mybooking_rescheduling_form" ref="bookingpress_mybooking_rescheduling_form" :rules="bookingpress_mybooking_rescheduling_form_rules">
					<template slot-scope="scope">								
						<div class="bpa-front-form-body-row">
							<el-row :gutter="32">
								<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
									<el-form-item prop="appointment_booked_date">
										<template #label>
											<span class="bpa-front-form-label"><?php echo esc_html($bookingpress_customize_settings_details['reschedule_date_label']); ?></span>
										</template>
										<el-date-picker popper-class="bpa-front-cp-reschedule-date-picker" class="bpa-front-form-control bpa-front-form-control--date-picker" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" v-model="appointment_customer_reschedule_booked_date" name="appointment_customer_reschedule_date" type="date" :picker-options="pickerOptions" :clearable="false" @change="select_date($event)" value-format="yyyy-MM-dd" ></el-date-picker>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" v-if="reschedule_appointment_service_duration_unit !='d'">
									<el-form-item prop="appointment_customer_reschedule_booked_time">
										<template #label>
											<span class="bpa-front-form-label"><?php echo esc_html($bookingpress_customize_settings_details['reschedule_time_label']); ?></span>
										</template>
										<div class="bpa-front-timeslotbox">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm-.22-13h-.06c-.4 0-.72.32-.72.72v4.72c0 .35.18.68.49.86l4.15 2.49c.34.2.78.1.98-.24.21-.34.1-.79-.25-.99l-3.87-2.3V7.72c0-.4-.32-.72-.72-.72z"/></svg>	
											<el-select :disabled="customer_reschedule_appointment_loader" class="bpa-front-form-control" Placeholder="<?php echo esc_html($bookingpress_customize_settings_details['reschedule_time_placeholder']); ?>" v-model="bookingpress_mybooking_rescheduling_form.appointment_customer_reschedule_booked_time"  filterable popper-class="bpa-el-select--is-with-modal bpa-custom-dropdown bpa-front-cp-reschedule-time-picker" @change="bookingpress_set_rescheduled_end_time($event,reschedule_appointment_time_slot)">
											<el-option-group :label="bookingpress_morning_text">
												<el-option v-for="appointment_time in reschedule_appointment_time_slot.morning_time" :label="appointment_time.formatted_start_time+' - '+appointment_time.formatted_end_time" :value="appointment_time.start_time" :disabled="(appointment_time.is_booked == 1 || appointment_time.disable_flag_timeslot == true || appointment_time.disable_flag_timeslot == 'true')">
												<span>{{ appointment_time.start_time | bookingpress_format_time }} <?php esc_html_e('to', 'bookingpress-appointment-booking'); ?> {{appointment_time.end_time | bookingpress_format_time}}</span>
												</el-option>
											</el-option-group>
											<el-option-group :label="bookingpress_afternoon_text">
												<el-option v-for="appointment_time in reschedule_appointment_time_slot.afternoon_time" :label="appointment_time.formatted_start_time+' - '+appointment_time.formatted_end_time" :value="appointment_time.start_time" :disabled="(appointment_time.is_booked == 1 || appointment_time.disable_flag_timeslot == true || appointment_time.disable_flag_timeslot == 'true')">
												<span>{{ appointment_time.start_time | bookingpress_format_time }} <?php esc_html_e('to', 'bookingpress-appointment-booking'); ?> {{appointment_time.end_time | bookingpress_format_time}}</span>
												</el-option>
											</el-option-group>
											<el-option-group :label="bookingpress_evening_text">
												<el-option v-for="appointment_time in reschedule_appointment_time_slot.evening_time" :label="appointment_time.formatted_start_time+' - '+appointment_time.formatted_end_time" :value="appointment_time.start_time" :disabled="(appointment_time.is_booked == 1 || appointment_time.disable_flag_timeslot == true || appointment_time.disable_flag_timeslot == 'true')">
												<span>{{ appointment_time.start_time | bookingpress_format_time }} <?php esc_html_e('to', 'bookingpress-appointment-booking'); ?> {{appointment_time.end_time | bookingpress_format_time}}</span>
												</el-option>
											</el-option-group>
											<el-option-group :label="bookingpress_night_text">
												<el-option v-for="appointment_time in reschedule_appointment_time_slot.night_time" :label="appointment_time.formatted_start_time+' - '+appointment_time.formatted_end_time" :value="appointment_time.start_time" :disabled="(appointment_time.is_booked == 1 || appointment_time.disable_flag_timeslot == true || appointment_time.disable_flag_timeslot == 'true')">
												<span>{{ appointment_time.start_time | bookingpress_format_time }} <?php esc_html_e('to', 'bookingpress-appointment-booking'); ?> {{appointment_time.end_time | bookingpress_format_time}}</span>
												</el-option>
											</el-option-group>
										</el-select>
										</div>
									</el-form-item>
								</el-col>
							</el-row>
						</div>
						<div class="bpa-front-reschedule-footer">						
							<el-button id="bookingpress_book_appointment_btn" class="bpa-front-btn bpa-front-btn__medium bpa-front-btn--primary" @click="RescheduleAppointmentShortcode(reschedule_apt_status)" :class="(is_rescheduled_loader == true || customer_reschedule_appointment_loader) ? 'bpa-front-btn--is-loader' : ''" :disabled="(is_rescheduled_loader || customer_reschedule_appointment_loader)">	
								<span class="bpa-btn__label"><?php echo esc_html($bookingpress_customize_settings_details['reschedule_update_btn_label']); ?></span>
								<div class="bpa-front-btn--loader__circles">				    
									<div></div>
									<div></div>
									<div></div>
								</div>
							</el-button>
						</div>					
					</template>
				</el-form>			
			</div>
		</div>
	</div>
</el-main>

