<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body" name ="general_settings" data-tab_name="general_settings">
	<span slot="label">
		<i class="material-icons-round">settings</i>
		<?php esc_html_e( 'General Settings', 'bookingpress-appointment-booking' ); ?>
	</span>
	<div class="bpa-general-settings-tabs--pb__card">
		<el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading __bpa-is-groupping">
			<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'General Settings', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
				<div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">		
					<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('general_setting_form','general_setting')" :disabled="is_disabled" >					
					  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
					  <div class="bpa-btn--loader__circles">				    
						  <div></div>
						  <div></div>
						  <div></div>
					  </div>
					</el-button>
				</div>
			</el-col>
		</el-row>
		<div class="bpa-gs--tabs-pb__content-body">						
			<el-form :rules="rules_general" ref="general_setting_form" :model="general_setting_form" @submit.native.prevent>
				<div class="bpa-gs__cb--item">
					<div class="bpa-gs__cb--item-heading">
						<h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Global Settings', 'bookingpress-appointment-booking' ); ?></h4>
					</div>
					<div class="bpa-gs__cb--item-body">
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4><?php esc_html_e( 'Default Service Duration', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">				
								<el-form-item prop="default_time_slot_step">
									<el-select class="bpa-form-control" v-model="general_setting_form.default_time_slot_step" 
										placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>"
										popper-class="bpa-el-select--is-with-navbar">
										<el-option v-for="item in default_timeslot_options" :key="item.text" :label="item.text" :value="item.value"></el-option>	
									</el-select>						
								</el-form-item>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4><?php esc_html_e( 'Default Time Slot Step', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="default_time_slot">
									<el-select class="bpa-form-control" v-model="general_setting_form.default_time_slot" 
										placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>"
										popper-class="bpa-el-select--is-with-navbar">
										<el-option v-for="item in default_timeslot_options" :key="item.text" :label="item.text" :value="item.value"></el-option>    
									</el-select>                        
								</el-form-item>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4><?php esc_html_e( 'Share Capacity between timeslots', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item>
									<el-switch class="bpa-swtich-control" v-model="general_setting_form.share_quanty_between_timeslots" :disabled="general_setting_form.show_time_as_per_service_duration==true" ></el-switch>
									</el-switch>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4><?php esc_html_e( 'Show time as per service duration', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item>
									<el-switch class="bpa-swtich-control" v-model="general_setting_form.show_time_as_per_service_duration" @change="bookingpress_change_show_time_as_service">
									</el-switch>    
								</el-form-item>
							</el-col>
						</el-row>
						<!-- timesolts grouping changes start -->
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4><?php esc_html_e( 'Timeslot Grouping Settings', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
                    	</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-timing-grouping-cls">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4><?php esc_html_e( 'Afternoon Start Time', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="bpa_afternoon_start_time">
									<el-select @change="bookingpress_timesolts_afternoon_grouping()" v-model="general_setting_form.bpa_afternoon_start_time" name ="afternoon_start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable popper-class="bpa-el-select--is-with-navbar"> 
										<span slot="prefix" class="material-icons-round">access_time</span>
										<el-option v-for="bpa_timesolts in timeslots_grouping_list"  :label="bpa_timesolts.formatted_start_time" :value="bpa_timesolts.start_time"></el-option >
									</el-select>
								</el-form-item>
							</el-col>
                    	</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-timing-grouping-cls">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4><?php esc_html_e( 'Evening Start Time', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
						
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="bpa_evening_start_time">
									<el-select @change="bookingpress_timesolts_evening_grouping()" v-model="general_setting_form.bpa_evening_start_time" name ="afternoon_start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable popper-class="bpa-el-select--is-with-navbar"> 
										<span slot="prefix" class="material-icons-round">access_time</span>
										<el-option v-for="bpa_timesolts in timeslots_grouping_list"  :label="bpa_timesolts.formatted_start_time" :value="bpa_timesolts.start_time" v-if="bpa_timesolts.start_time > general_setting_form.bpa_afternoon_start_time"></el-option >
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-timing-grouping-cls">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4><?php esc_html_e( 'Night Start Time', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="bpa_night_start_time">
									<el-select v-model="general_setting_form.bpa_night_start_time" name ="night_start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable> 
										<span slot="prefix" class="material-icons-round">access_time</span>
										<el-option v-for="bpa_timesolts in timeslots_grouping_list"  :label="bpa_timesolts.formatted_start_time" :value="bpa_timesolts.start_time" v-if="(bpa_timesolts.start_time > general_setting_form.bpa_evening_start_time && general_setting_form.bpa_evening_start_time != '')"></el-option >
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						<!-- timesolts grouping changes end -->
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Default Phone Country Code', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>							
							<el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08">								
								<el-radio v-model="general_setting_form.default_country_type" label="indentify_by_ip"><?php esc_html_e('Identify country code by user\'s IP address','bookingpress-appointment-booking'); ?></el-radio>
								<el-radio v-model="general_setting_form.default_country_type" label="fixed_country" @change="bookingpress_change_country_type"><?php esc_html_e('Default country as selected below','bookingpress-appointment-booking'); ?></el-radio>
							</el-col>										
							<!--												
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-row :gutter="24">
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item prop="default_phone_country_code">		
										<el-select class="bpa-form-control" filterable v-model="general_setting_form.default_phone_country_code"
											popper-class="bpa-el-select--is-with-navbar">
											<el-option value="auto_detect" label="<?php esc_html_e( 'Identify country code by user\'s IP address', 'bookingpress-appointment-booking' ); ?>"></el-option>
											<el-option v-for="countries in phone_countries_details" :value="countries.code" :label="countries.name">
												<span class="flag" :class="countries.code"></span> {{ countries.name }}
											</el-option>
										</el-select>
									</el-form-item>
									</el-col>
								</el-row>								
								<el-row type="flex" class="bpa-ns--sub-module__card--row">
									<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
										<h4> <?php esc_html_e('Payment Mode', 'bookingpress-appointment-booking'); ?></h4>
									</el-col>
									<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">
										<el-radio v-model="payment_setting_form.paypal_payment_mode" label="sandbox">Sandbox</el-radio>
										<el-radio v-model="payment_setting_form.paypal_payment_mode" label="live">Live</el-radio>
									</el-col>
								</el-row>								
							</el-col>
							-->
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="general_setting_form.default_country_type == 'fixed_country'">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left"></el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-right">
								<el-form-item prop="general_setting_phone_number" >
									<vue-tel-input v-model="general_setting_form.general_setting_phone_number" class="bpa-form-control --bpa-country-dropdown" @country-changed="bookingpress_general_tab_phone_country_change_func($event)" v-bind="bookingpress_tel_input_settings_props" ref="bpa_tel_input_settings_field">
										<template v-slot:arrow-icon>
											<span class="material-icons-round">keyboard_arrow_down</span>
										</template>
									</vue-tel-input>
								</el-form-item>
							</el-col>							
						</el-row>	
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Default items per page', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-row :gutter="24">
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="per_page_item">
											<el-select class="bpa-form-control" v-model="general_setting_form.per_page_item"
												popper-class="bpa-el-select--is-with-navbar">
												<el-option v-for="item in default_pagination" :key="item.text" :value="item.value"></el-option>
											</el-select>
										</el-form-item>	
									</el-col>
								</el-row>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Data Export Delimiter', 'bookingpress-appointment-booking' ); ?></h4>						
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08">
								<el-row :gutter="24">
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="bookingpress_export_delimeter">
											<el-select class="bpa-form-control" v-model="general_setting_form.bookingpress_export_delimeter" >
												<el-option v-for="item in search_delimiter_list" :key="item.value" :label="item.text" :value="item.value"></el-option>
											</el-select>                               
										</el-form-item>                                          
									</el-col>
								</el-row>		                 
							</el-col>				
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4><?php esc_html_e( 'Default Date Format', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="default_time_slot">
									<el-select class="bpa-form-control" v-model="general_setting_form.default_date_format" popper-class="bpa-el-select--is-with-navbar">
										<el-option label="<?php echo esc_html('F j, Y'); ?>" value="F j, Y"><?php echo esc_html('F j, Y'); ?></el-option>
										<el-option label="<?php echo esc_html('Y-m-d'); ?>" value="Y-m-d"><?php echo esc_html('Y-m-d'); ?></el-option>
										<el-option label="<?php echo esc_html('m/d/Y'); ?>" value="m/d/Y"><?php echo esc_html('m/d/Y'); ?></el-option>
										<el-option label="<?php echo esc_html('d/m/Y'); ?>" value="d/m/Y"><?php echo esc_html('d/m/Y'); ?></el-option>
										<el-option label="<?php echo esc_html('d.m.Y'); ?>" value="d.m.Y"><?php echo esc_html('d.m.Y'); ?></el-option>
										<el-option label="<?php echo esc_html('d-m-Y'); ?>" value="d-m-Y"><?php echo esc_html('d-m-Y'); ?></el-option>
									</el-select>                        
								</el-form-item>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4><?php esc_html_e( 'Default Time Format', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="default_time_slot">
									<el-select class="bpa-form-control" v-model="general_setting_form.default_time_format" popper-class="bpa-el-select--is-with-navbar">
										<el-option label="<?php esc_html_e('12 hour Format','bookingpress-appointment-booking'); ?>" value="g:i a"><?php esc_html_e('12 hour Format','bookingpress-appointment-booking'); ?></el-option>
										<el-option label="<?php esc_html_e('24 hour Format','bookingpress-appointment-booking'); ?>" value="H:i"><?php esc_html_e('24 hour Format','bookingpress-appointment-booking'); ?></el-option>
										<el-option label="<?php esc_html_e('Inherit From Wordpress','bookingpress-appointment-booking'); ?>" value="bookingpress-wp-inherit-time-format"><?php esc_html_e('Inherit From Wordpress','bookingpress-appointment-booking'); ?></el-option>
									</el-select>                        
								</el-form-item>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left --bpa-is-not-input-control">
								<h4><?php esc_html_e( 'Complete Payment Page Selection', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item>
									<el-select class="bpa-form-control" v-model="general_setting_form.complete_payment_page_id" popper-class="bpa-el-select--is-with-navbar">
										<el-option :label="pages.title" :value="''+pages.id" v-for="pages in complete_payment_pages">{{ pages.title }}</el-option>
									</el-select>                        
								</el-form-item>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Show booking-slots in client timezone', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item>
									<el-switch class="bpa-swtich-control" v-model="general_setting_form.show_bookingslots_in_client_timezone">
									</el-switch>	
								</el-form-item>
							</el-col>
						</el-row>	
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="general_setting_form.show_bookingslots_in_client_timezone == true">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<div class="bpa-toast-notification --bpa-timezone-note">
									<div class="bpa-front-tn-body">
										<span class="material-icons-round">info</span>
										<p><strong><?php esc_html_e('Note:', 'bookingpress-appointment-booking'); ?></strong> <?php esc_html_e('Please note that In the BookingPress, you will see all the date and time according to WordPress time zone ( WordPress -> Settings -> General -> Timezone ) on the backend and on the frontend, customers will see the date and time according to their System\'s time zone.', 'bookingpress-appointment-booking'); ?> </p>
									</div>
								</div>
							</el-col>
						</el-row>					
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Load JS &amp; CSS in all pages', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item>
									<el-switch class="bpa-swtich-control" v-model="general_setting_form.load_js_css_all_pages">
									</el-switch>	
								</el-form-item>
							</el-col>
						</el-row>						
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Help us improve BookingPress by sending anonymous usage stats', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item>
									<el-switch class="bpa-swtich-control" v-model="general_setting_form.anonymous_data">
									</el-switch>	
								</el-form-item>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Enable Debug Mode', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item>
									<el-switch class="bpa-swtich-control" v-model="general_setting_form.debug_mode">
									</el-switch>	
								</el-form-item>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'BookingPress Loader', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item>                               
									<div class="bpa_general-settings bpa-svg__icon">
										<el-radio-group v-model="general_setting_form.bpa_white_label_icon" @change ="bookingpress_change_white_label_icon(general_setting_form.bpa_white_label_icon)">
											<el-radio-button label="bpa_bookingpress_icon"> <?php echo "<img height='30' width='30' src=".BOOKINGPRESS_PRO_URL."/images/bookingpress_loader.svg>"; ?></el-radio-button>
											<el-radio-button label="bpa_square_icon"><?php echo "<img height='30' width='30' src=".BOOKINGPRESS_PRO_URL."/images/bpa_bouncing-squares.svg>"; ?></el-radio-button>
											<el-radio-button label="bpa_ripple_icon"><?php echo "<img height='30' width='30' src=".BOOKINGPRESS_PRO_URL."/images/bpa_ripples.svg>"; ?></el-radio-button>
										</el-radio-group>
									</div>
								</el-form-item>
							</el-col>
						<el-row>
					</div>
				</div>
				<div class="bpa-gs__cb--item">
					<div class="bpa-gs__cb--item-heading">
						<h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Appointment Settings', 'bookingpress-appointment-booking' ); ?></h4>
					</div>
					<div class="bpa-gs__cb--item-body">
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Default Appointment Status', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-row :gutter="24">
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="appointment_status">	
											<el-select class="bpa-form-control" v-model="general_setting_form.appointment_status"
												popper-class="bpa-el-select--is-with-navbar">
												<el-option v-for="item in default_appointment_staus" :label="item.text" :value="item.value"></el-option>
											</el-select>
										</el-form-item>
									</el-col>
								</el-row>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e("Appointment status paid with 'On site' payment method", "bookingpress-appointment-booking"); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="appointment_status">    
									<el-select class="bpa-form-control" v-model="general_setting_form.onsite_appointment_status"
										popper-class="bpa-el-select--is-with-navbar">
										<el-option v-for="item in default_appointment_staus" :label="item.text" :value="item.value"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						<!-- <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e("Appointment status when refunded past date appointment", "bookingpress-appointment-booking"); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="appointment_status">    
									<el-select class="bpa-form-control" v-model="general_setting_form.refund_past_appointment_status"
										popper-class="bpa-el-select--is-with-navbar">
										<el-option v-for="item in default_all_appointment_status" :label="item.text" :value="item.value"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row>
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e("Appointment status when refunded furure date appointment", "bookingpress-appointment-booking"); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="appointment_status">    
									<el-select class="bpa-form-control" v-model="general_setting_form.refund_future_appointment_status"
										popper-class="bpa-el-select--is-with-navbar">
										<el-option v-for="item in default_all_appointment_status" :label="item.text" :value="item.value"></el-option>
									</el-select>
								</el-form-item>
							</el-col>
						</el-row> -->
						<!--minimum time required for booking-->
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Minimum time required before booking', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">				
								<el-form-item prop="default_minimum_time_for_booking">
									<el-select class="bpa-form-control" v-model="general_setting_form.default_minimum_time_for_booking" 
										placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>"
										popper-class="bpa-el-select--is-with-navbar">
										<el-option v-for="item in default_minimum_time_options" :key="item.text" :label="item.text" :value="item.value"></el-option>	
									</el-select>						
								</el-form-item>
							</el-col>
						</el-row>
						<!--end-->
						<!--minimum time required for canceling-->
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Minimum time required before canceling', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">				
								<el-form-item prop="minimum_time_required_for_canceling">
									<el-select class="bpa-form-control" v-model="general_setting_form.default_minimum_time_for_canceling" 
										placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>"
										popper-class="bpa-el-select--is-with-navbar">
										<el-option v-for="item in default_minimum_time_options" :key="item.text" :label="item.text" :value="item.value"></el-option>	
									</el-select>						
								</el-form-item>
							</el-col>
						</el-row>
						<!--end-->
						<!--minimum time required before rescheduling-->
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Minimum time required before rescheduling', 'bookingpress-appointment-booking' ); ?></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">				
								<el-form-item prop="default_minimum_time_befor_rescheduling">
									<el-select class="bpa-form-control" v-model="general_setting_form.default_minimum_time_befor_rescheduling"placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>"
										popper-class="bpa-el-select--is-with-navbar">
										<el-option v-for="item in default_minimum_time_options" :key="item.text" :label="item.text" :value="item.value"></el-option>	
									</el-select>						
								</el-form-item>
							</el-col>
						</el-row>
						<!--end-->
						<!--period available for booking in advance-->
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4> <?php esc_html_e( 'Period available for booking in advance', 'bookingpress-appointment-booking' ); ?> ( <?php esc_html_e( 'Days', 'bookingpress-appointment-booking' ); ?> )</h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="period_available_for_booking">
									<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" :max="1095"  v-model="general_setting_form.period_available_for_booking" step-strictly></el-input-number>
								</el-form-item>	
							</el-col>
						</el-row>
						<!--end-->
						<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4><?php esc_html_e( 'Share timeslot between all services', 'bookingpress-appointment-booking' ); ?></h4>
								<label class="bpa-cb-il__desc"><?php esc_html_e('This option will get the highest priority over all the options while booking. Capacity will be ignored when this option is Enabled', 'bookingpress-appointment-booking'); ?></label>	
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-form-item prop="share_timeslot_between_services">
									<el-switch class="bpa-swtich-control" v-model="general_setting_form.share_timeslot_between_services">
									</el-switch>	
								</el-form-item>
							</el-col>
						</el-row>

						<el-row v-if="general_setting_form.share_timeslot_between_services" type="flex" class="bpa-gs--tabs-pb__cb-item-row">
							<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
								<h4></h4>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
								<el-row :gutter="24">
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="appointment_status">	
											<el-select class="bpa-form-control" v-model="general_setting_form.share_timeslot_between_services_type" popper-class="bpa-el-select--is-with-navbar">
												<el-option label="<?php esc_html_e( 'Share time across all services', 'bookingpress-appointment-booking' ); ?>" value="all_service"></el-option>
												<el-option label="<?php esc_html_e( 'Share time across category', 'bookingpress-appointment-booking' ); ?>" value="service_category"></el-option>
											</el-select>
										</el-form-item>
									</el-col>
								</el-row>
							</el-col>
						</el-row>

					</div>
				</div>
				<?php 
					do_action('bookingpress_add_general_setting_section');
				?>
			</el-form>	
		</div>
	</div>
</el-tab-pane>
