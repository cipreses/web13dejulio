<?php
	global $bookingpress_ajaxurl, $BookingPressPro, $bookingpress_common_date_format;
	$bpa_edit_daysoffs     = $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_daysoffs' );
	$bpa_edit_special_days = $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_special_days' );
	$bpa_edit_workhours = $BookingPressPro->bookingpress_check_capability( 'bookingpress_edit_workhours' );
?>
<el-main class="bpa-main-listing-card-container bpa-timesheet-card-container bpa--is-page-non-scrollable-mob" :class="(bookingpress_staff_customize_view == 1 ) ? 'bpa-main-list-card__is-staff-custom-view':''" id="all-page-main-container">
	<div class="bpa-form-row--parent" :class="bpa_sm_shift_management_cls">
		<?php if ( $bpa_edit_workhours==0 ) { ?>
			<div class="bpa-default-card bpa-timesheet--workinghours">
		<?php } ?>
		<el-row type="flex" class="bpa-mlc-head-wrap bpa-db-sec-heading">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Working Hours', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
				<div class="bpa-hw-right-btn-group">
					<?php
					if ( $bpa_edit_workhours ) {
						?>
						<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveStaffwhDetails()" :disabled="is_disabled">
							<span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
							<div class="bpa-btn--loader__circles">
								<div></div>
								<div></div>
								<div></div>
							</div>
						</el-button>
						<?php
					}
					?>
				</div>
			</el-col>
		</el-row>
		<?php if ( $bpa_edit_workhours==1 ) { ?>
			<div class="bpa-default-card bpa-timesheet--workinghours bpa-timesheet-edit_workinghours">
		<?php } ?>
			<?php do_action('bookingpress_add_staffmember_shift_management_content'); ?>
			<?php if($bpa_edit_workhours==1) {?>
				<div class="bpa-twh--body-content bpa-sm__working-hours" id="bpa-main-container-wh">
					<?php } else { ?>
						<div class="bpa-back-loader-container" id="bpa-page-loading-loader" >
							<div class="bpa-back-loader"></div>
						</div> 
					<div class="bpa-tc--content bpa-twh--body-content" id="bpa-main-container">
			<?php } ?>
			<?php if($bpa_edit_workhours==1) {?>
				<h3 class="bpa-wh__head-row-subheading"><?php esc_html_e( 'Configure Hours', 'bookingpress-appointment-booking' ); ?></h3>
				<div class="bpa-sm__wh-head-row">
					<div class="bpa-wh__head-row-title">
						<span class="bpa-form-label"><?php esc_html_e( 'Configure specific workhours', 'bookingpress-appointment-booking' ); ?></span>
					</div>
					<div class="bpa-wh__head-row-swtich">
						<el-switch class="bpa-swtich-control" v-model="bookingpress_configure_specific_workhour"></el-switch>
					</div>
				</div>
				<div class="bpa-sm__wh-items" v-if="bookingpress_configure_specific_workhour == true && display_staff_working_hours == true">  
					<div class="bpa-sm__wh-body-row" v-for="work_hours_day in work_hours_days_arr">
						<el-row class="bpa-sm__wh-item-row" :gutter="24" :id="'weekday_'+work_hours_day.day_key">
							<el-col :xs="24" :sm="18" :md="18" :lg="20" :xl="22">
								<el-row type="flex" class="bpa-sm__wh-body-left">
									<el-col :xs="24" :sm="8" :md="6" :lg="6" :xl="2">
										<span class="bpa-form-label" v-if="work_hours_day.day_name == 'Monday'"><?php esc_html_e('Monday', 'bookingpress-appointment-booking'); ?></span>
										<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Tuesday'"><?php esc_html_e('Tuesday', 'bookingpress-appointment-booking'); ?></span>
										<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Wednesday'"><?php esc_html_e('Wednesday', 'bookingpress-appointment-booking'); ?></span>
										<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Thursday'"><?php esc_html_e('Thursday', 'bookingpress-appointment-booking'); ?></span>
										<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Friday'"><?php esc_html_e('Friday', 'bookingpress-appointment-booking'); ?></span>
										<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Saturday'"><?php esc_html_e('Saturday', 'bookingpress-appointment-booking'); ?></span>
										<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Sunday'"><?php esc_html_e('Sunday', 'bookingpress-appointment-booking'); ?></span>
										<span v-else>{{ work_hours_day.day_name }}</span>
									</el-col>
									<el-col :xs="24" :sm="16" :md="18" :lg="18" :xl="22">
										<el-row :gutter="24">
											<el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">												
												<el-select v-model="workhours_timings[work_hours_day.day_name].start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>"
													@change="bookingpress_set_workhour_value($event,work_hours_day.day_name)" filterable>
													<span slot="prefix" class="material-icons-round">access_time</span>
													<el-option v-for="work_timings in work_hours_day.worktimes" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="work_timings.start_time != workhours_timings[work_hours_day.day_name].end_time || workhours_timings[work_hours_day.day_name].end_time == 'Off'"></el-option>
												</el-select>
											</el-col>
											<el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" v-if="workhours_timings[work_hours_day.day_name].start_time != 'Off'">
												<el-select v-model="workhours_timings[work_hours_day.day_name].end_time" class="bpa-form-control bpa-form-control__left-icon" 
													placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>"
													@change="bookingpress_check_workhour_value($event,work_hours_day.day_name)"  filterable>
													<span slot="prefix" class="material-icons-round">access_time</span>
													<el-option v-for="work_timings in work_hours_day.worktimes" :label="work_timings.formatted_end_time" :value="work_timings.end_time" v-if="(work_timings.end_time > workhours_timings[work_hours_day.day_name].start_time ||  work_timings.end_time == '24:00:00')"></el-option>				
												</el-select>
											</el-col>
										</el-row>
										<el-row  v-if="selected_break_timings[work_hours_day.day_name].length > 0 && workhours_timings[work_hours_day.day_name].start_time != 'Off'">
											<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
												<div class="bpa-break-hours-wrapper">
													<h4><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h4>
													<div class="bpa-bh--items">
														<div class="bpa-bh__item" v-for="(break_data,index) in work_hours_day.break_times">
															<p @click="edit_workhour_data(event,break_data.start_time, break_data.end_time, work_hours_day.day_name,index)">{{ break_data.formatted_start_time }} to {{ break_data.formatted_end_time }}</p>
															<span class="material-icons-round" slot="reference" @click="bookingpress_remove_workhour(break_data.start_time, break_data.end_time, work_hours_day.day_name)">close</span>
														</div>
													</div>
												</div>
											</el-col>
										</el-row>
									</el-col>
								</el-row>
							</el-col>
							<el-col :xs="24" :sm="6" :md="6" :lg="4" :xl="2" v-if="workhours_timings[work_hours_day.day_name].start_time != 'Off'">
								<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" :class="(break_selected_day == work_hours_day.day_name && open_add_break_modal == true) ? 'bpa-btn--primary' : ''" @click="open_add_break_modal_func(event, work_hours_day.day_name)">
									<?php esc_html_e( 'Add Break', 'bookingpress-appointment-booking' ); ?>
								</el-button>
							</el-col>
						</el-row>
					</div>
				</div>
				<?php do_action( 'bookingpress_staff_work_hour_content_outside') ?>
				<?php } else { ?>
					<div class="bpa-wh__items">
						<div class="bpa-wh__item">
							<h5><?php esc_html_e( 'Monday', 'bookingpress-appointment-booking' ); ?></h5>
							<div class="bpa-wh__item-body">
								<p v-if="monday_timings.workhours_start_time != 'Off' && monday_timings.workhours_end_time != 'Off'">{{ monday_timings.workhours_start_time+' - '+monday_timings.workhours_end_time }}</p>
								<p v-else><?php esc_html_e('Off','bookingpress-appointment-booking'); ?></p>
							</div>
							<div class="bpa-wh__item-breaks" v-if="monday_timings.break_times.length > 0">
								<h5><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h5>
								<p v-for="break_time_data in monday_timings.break_times">{{ break_time_data.start_time }} to {{ break_time_data.end_time }}</p>
							</div>
						</div>
						<div class="bpa-wh__item">
							<h5><?php esc_html_e( 'Tuesday', 'bookingpress-appointment-booking' ); ?></h5>
							<div class="bpa-wh__item-body">
								<p v-if="tuesday_timings.workhours_start_time != 'Off' && tuesday_timings.workhours_end_time != 'Off'">{{ tuesday_timings.workhours_start_time+' - '+tuesday_timings.workhours_end_time }}</p>
								<p v-else><?php esc_html_e('Off','bookingpress-appointment-booking'); ?></p>
							</div>
							<div class="bpa-wh__item-breaks" v-if="tuesday_timings.break_times.length > 0">
								<h5><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h5>
								<p v-for="break_time_data in tuesday_timings.break_times">{{ break_time_data.start_time }} to {{ break_time_data.end_time }}</p>
							</div>
						</div>
						<div class="bpa-wh__item">
							<h5><?php esc_html_e( 'Wednesday', 'bookingpress-appointment-booking' ); ?></h5>
							<div class="bpa-wh__item-body">
								<p v-if="wednesday_timings.workhours_start_time != 'Off' && wednesday_timings.workhours_end_time != 'Off'">{{ wednesday_timings.workhours_start_time+' - '+wednesday_timings.workhours_end_time }}</p>
								<p v-else><?php esc_html_e('Off','bookingpress-appointment-booking'); ?></p>
							</div>
							<div class="bpa-wh__item-breaks" v-if="wednesday_timings.break_times.length > 0">
								<h5><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h5>
								<p v-for="break_time_data in wednesday_timings.break_times">{{ break_time_data.start_time }} to {{ break_time_data.end_time }}</p>
							</div>
						</div>
						<div class="bpa-wh__item">
							<h5><?php esc_html_e( 'Thursday', 'bookingpress-appointment-booking' ); ?></h5>
							<div class="bpa-wh__item-body">
								<p v-if="thursday_timings.workhours_start_time != 'Off' && thursday_timings.workhours_end_time != 'Off'">{{ thursday_timings.workhours_start_time+' - '+thursday_timings.workhours_end_time }}</p>
								<p v-else><?php esc_html_e('Off','bookingpress-appointment-booking'); ?></p>
							</div>
							<div class="bpa-wh__item-breaks" v-if="thursday_timings.break_times.length > 0">
								<h5><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h5>
								<p v-for="break_time_data in thursday_timings.break_times">{{ break_time_data.start_time }} to {{ break_time_data.end_time }}</p>
							</div>
						</div>
						<div class="bpa-wh__item">
							<h5><?php esc_html_e( 'Friday', 'bookingpress-appointment-booking' ); ?></h5>
							<div class="bpa-wh__item-body">
								<p v-if="friday_timings.workhours_start_time != 'Off' && friday_timings.workhours_end_time != 'Off'">{{ friday_timings.workhours_start_time+' - '+friday_timings.workhours_end_time }}</p>
								<p v-else><?php esc_html_e('Off','bookingpress-appointment-booking'); ?></p>
							</div>
							<div class="bpa-wh__item-breaks" v-if="friday_timings.break_times.length > 0">
								<h5><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h5>
								<p v-for="break_time_data in friday_timings.break_times">{{ break_time_data.start_time }} to {{ break_time_data.end_time }}</p>
							</div>
						</div>
						<div class="bpa-wh__item">
							<h5><?php esc_html_e( 'Saturday', 'bookingpress-appointment-booking' ); ?></h5>
							<div class="bpa-wh__item-body">
								<p v-if="saturday_timings.workhours_start_time != 'Off' && saturday_timings.workhours_end_time != 'Off'">{{ saturday_timings.workhours_start_time+' - '+saturday_timings.workhours_end_time }}</p>
								<p v-else><?php esc_html_e('Off','bookingpress-appointment-booking'); ?></p>
							</div>
							<div class="bpa-wh__item-breaks" v-if="saturday_timings.break_times.length > 0">
								<h5><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h5>
								<p v-for="break_time_data in saturday_timings.break_times">{{ break_time_data.start_time }} to {{ break_time_data.end_time }}</p>
							</div>
						</div>
						<div class="bpa-wh__item">
							<h5><?php esc_html_e( 'Sunday', 'bookingpress-appointment-booking' ); ?></h5>
							<div class="bpa-wh__item-body">
								<p v-if="sunday_timings.workhours_start_time != 'Off' && sunday_timings.workhours_end_time != 'Off'">{{ sunday_timings.workhours_start_time+' - '+sunday_timings.workhours_end_time }}</p>
								<p v-else><?php esc_html_e('Off','bookingpress-appointment-booking'); ?></p>
							</div>
							<div class="bpa-wh__item-breaks" v-if="sunday_timings.break_times.length > 0">
								<h5><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h5>
								<p v-for="break_time_data in sunday_timings.break_times">{{ break_time_data.start_time }} to {{ break_time_data.end_time }}</p>
							</div>
						</div>
					</div>
				<?php }?>
			</div>
		</div>
		<div class="bpa-default-card bpa-timesheet--specialdays">
			<el-row type="flex" class="bpa-mlc-head-wrap">
				<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
					<h1 class="bpa-page-heading"><?php esc_html_e( 'Special Days', 'bookingpress-appointment-booking' ); ?></h1>
				</el-col>
				<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
					<?php if ( $bpa_edit_special_days ) { ?>
					<div class="bpa-hw-right-btn-group">
						<el-button class="bpa-btn" @click="open_special_days_func(event)">
							<span class="material-icons-round">add</span>
							<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</div>
					<?php } ?>
				</el-col>
			</el-row>
			<div class="bpa-tc--content bpa-sm__special-days-card" v-if="display_staff_working_hours == true">
				<div class="bpa-grid-list-container bpa-sm__doc-body">
					<el-row type="flex" v-if="bookingpress_staffmembers_specialdays_details.length == 0">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-data-empty-view">
								<div class="bpa-ev-left-vector">
									<picture>
										<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
										<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
									</picture>
								</div>				
								<div class="bpa-ev-right-content">					
									<h4><?php esc_html_e( 'No Special Days Available', 'bookingpress-appointment-booking' ); ?></h4>
								</div>				
							</div>
						</el-col>
					</el-row>
					<el-row class="bpa-assigned-service-body" v-if="bookingpress_staffmembers_specialdays_details.length > 0">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-card bpa-card__heading-row">
								<el-row type="flex">
									<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?></h4>
										</div>
									</el-col>
									<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Workhours', 'bookingpress-appointment-booking' ); ?></h4>
										</div>
									</el-col>
									<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h4>
										</div>
									</el-col>
									<el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
										<?php if ( $bpa_edit_special_days ) { ?>
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Action', 'bookingpress-appointment-booking' ); ?></h4>
										</div>
										<?php } ?>
									</el-col>
								</el-row>
							</div>
						</el-col>

						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="staffmember_special_day in bookingpress_staffmembers_specialdays_details">
							<div class="bpa-card bpa-card__body-row list-group-item">
								<el-row type="flex">
									<el-col :xs="24" :sm="8" :md="8" :lg="8" :xl="8">
										<div class="bpa-card__item">
										<h4 class="bpa-card__item__heading is--body-heading">{{ staffmember_special_day.special_day_formatted_start_date }} - {{ staffmember_special_day.special_day_formatted_end_date }}</h4>
										</div>
									</el-col>
									<el-col :xs="24" :sm="6" :md="6" :lg="6" :xl="6">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading is--body-heading">( {{staffmember_special_day.formatted_start_time}} - {{staffmember_special_day.formatted_end_time}} )</h4>
										</div>
									</el-col>
									<el-col :xs="24" :sm="6" :md="6" :lg="6" :xl="6">
										<div class="bpa-card__item">
											<span v-if="staffmember_special_day.special_day_workhour != undefined && staffmember_special_day.special_day_workhour != ''">	
												<h4 class="bpa-card__item__heading is--body-heading" v-for="special_day_workhours in staffmember_special_day.special_day_workhour" v-if="special_day_workhours.formatted_start_time != undefined && special_day_workhours.formatted_start_time != '' && special_day_workhours.formatted_end_time != undefined && special_day_workhours.formatted_end_time != '' && special_day_workhours.start_time != '' && special_day_workhours.end_time != ''"> 
												( {{ special_day_workhours.formatted_start_time }} - {{special_day_workhours.formatted_end_time}} )
												</h4>
											</span>	
											<span v-else>-</span>	
										</div>
									</el-col>
									<el-col :xs="24" :sm="4" :md="4" :lg="4" :xl="4">
										<?php if ( $bpa_edit_special_days ) { ?>
										<div>
											<el-tooltip effect="dark" content="" placement="top" open-delay="300">
												<div slot="content">
													<span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
												</div>
												<el-button class="bpa-btn bpa-btn--icon-without-box" @click="show_edit_special_day_div(staffmember_special_day.id, event)">
													<span class="material-icons-round">mode_edit</span>
												</el-button>
											</el-tooltip>										
											<el-tooltip effect="dark" content="" placement="top" open-delay="300">
												<div slot="content">
													<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
												</div>
												<el-popconfirm
													cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
													confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
													icon="false" 
													title="<?php esc_html_e( 'Are you sure you want to delete this Special days?', 'bookingpress-appointment-booking' ); ?>" 
													@confirm="bookingpress_delete_special_daysoff(staffmember_special_day.id)" 
													confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
													cancel-button-type="bpa-btn bpa-btn__small">
													<el-button slot="reference" type="text" class="bpa-btn bpa-btn--icon-without-box __danger">
														<span class="material-icons-round">delete</span>
													</el-button>
												</el-popconfirm>    
											</el-tooltip>
										</div>
										<?php } ?>
									</el-col>
								</el-row>
							</div>
						</el-col>
					</el-row>
				</div>
			</div>
			<?php do_action( 'bookingpress_staff_special_days_external_content'); ?>
		</div>
	</div>
	<div class="bpa-default-card bpa-timesheet--daysoff">
		<el-row type="flex" class="bpa-mlc-head-wrap">
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Holiday', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<?php if ( $bpa_edit_daysoffs ) { ?>
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
				<div class="bpa-hw-right-btn-group">
					<el-button class="bpa-btn" @click="open_days_off_modal_func(event)">
						<span class="material-icons-round">add</span>
						<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
					</el-button>
				</div>
			</el-col>
			<?php } ?>
		</el-row>
		<div class="bpa-tc--content bpa-sm__days-off-card">
			<div class="bpa-grid-list-container bpa-sm__doc-body">                
				<el-row type="flex" v-if="bookingpress_staffmembers_daysoff_details.length == 0 && bookingpress_staffmember_default_daysoff_details.length == 0">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<div class="bpa-data-empty-view">
							<div class="bpa-ev-left-vector">
								<picture>
									<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
									<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
								</picture>
							</div>				
							<div class="bpa-ev-right-content">					
								<h4><?php esc_html_e( 'No Holiday Available', 'bookingpress-appointment-booking' ); ?></h4>
							</div>				
						</div>
					</el-col>
				</el-row>
				<div class="bpa-ts-days-off__company-holiday-wrap" v-if="bookingpress_staffmember_default_daysoff_details.length != 0">
					<el-row>
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<h2 class="bpa-sec--sub-heading"><?php esc_html_e( 'Company Holiday', 'bookingpress-appointment-booking' ); ?></h2>
						</el-col>
					</el-row>
					<el-row class="bpa-assigned-service-body">                    
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-card bpa-card__heading-row">
								<el-row type="flex">
									<el-col :xs="24" :sm="10" :md="10" :lg="10" :xl="10">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?></h4>
										</div>
									</el-col>
									<el-col :xs="24" :sm="10" :md="10" :lg="10" :xl="10">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Holiday Name', 'bookingpress-appointment-booking' ); ?></h4>
										</div>
									</el-col>
									<el-col :xs="24" :sm="4" :md="2" :lg="4" :xl="4">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading"></h4>
										</div>
									</el-col>
								</el-row>
							</div>
						</el-col>
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="default_day_off in bookingpress_staffmember_default_daysoff_details">
							<div class="bpa-card bpa-card__body-row list-group-item">
								<el-row type="flex">
									<el-col :xs="24" :sm="10" :md="10" :lg="10" :xl="10">
										<div class="bpa-card__item --bpa-sm-is-legends-item">
											<span v-if="default_day_off.bookingpress_repeat == 0"></span>
											<span class="--bpa-is-legend-yearly" v-else></span>
											<h4 v-if="default_day_off.bookingpress_dayoff_date == default_day_off.bookingpress_dayoff_enddate" class="bpa-card__item__heading is--body-heading">{{ default_day_off.bookingpress_dayoff_date}}</h4>	
											<h4 v-else class="bpa-card__item__heading is--body-heading">{{ default_day_off.bookingpress_dayoff_date}} - {{ default_day_off.bookingpress_dayoff_enddate}}</h4>	
										</div>									
									</el-col>
									<el-col :xs="24" :sm="10" :md="10" :lg="10" :xl="10">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading is--body-heading">{{ default_day_off.bookingpress_name }}</h4>
										</div>
									</el-col>      
									<el-col :xs="24" :sm="4" :md="4" :lg="4" :xl="4">                          
									</el-col>	
								</el-row>
							</div>						
						</el-col>
					</el-row>
					<el-row>
						<div class="bpa-dc__staff--legends-area">
							<div class="bpa-la__item">
								<p><span></span><?php esc_html_e( 'Once Off', 'bookingpress-appointment-booking' ); ?></p>
							</div>
							<div class="bpa-la__item">
								<p><span></span><?php esc_html_e( 'Yearly', 'bookingpress-appointment-booking' ); ?></p>
							</div>
						</div>  
					</el-row>
				</div>
				<div v-if="bookingpress_staffmembers_daysoff_details.length != 0">
					<el-row  v-if="bookingpress_staffmember_default_daysoff_details.length != 0">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<h2 class="bpa-sec--sub-heading"><?php esc_html_e( 'Holiday', 'bookingpress-appointment-booking' ); ?></h2>
						</el-col>
					</el-row>
					<el-row class="bpa-assigned-service-body">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
							<div class="bpa-card bpa-card__heading-row">
								<el-row type="flex">
									<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?></h4>
										</div>
									</el-col>
									<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Holiday Name', 'bookingpress-appointment-booking' ); ?></h4>
										</div>
									</el-col>
									<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Repeat Holiday', 'bookingpress-appointment-booking' ); ?></h4>
										</div>
									</el-col>
									<el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
									<?php if ( $bpa_edit_daysoffs ) { ?>
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Action', 'bookingpress-appointment-booking' ); ?></h4>
										</div>
										<?php } ?>
									</el-col>
								</el-row>
							</div>
						</el-col>
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="staffmember_day_off in bookingpress_staffmembers_daysoff_details">
							<div class="bpa-card bpa-card__body-row list-group-item">
								<el-row type="flex">
									<el-col :xs="24" :sm="10" :md="10" :lg="10" :xl="10">
										<div class="bpa-card__item --bpa-sm-is-legends-item">
											<h4 v-if="staffmember_day_off.bookingpress_staffmember_daysoff_date == staffmember_day_off.bookingpress_staffmember_daysoff_enddate" class="bpa-card__item__heading is--body-heading">{{ staffmember_day_off.bookingpress_staffmember_daysoff_date | bookingpress_format_date }}</h4>
											<h4 v-else class="bpa-card__item__heading is--body-heading">{{ staffmember_day_off.bookingpress_staffmember_daysoff_date | bookingpress_format_date }} - {{ staffmember_day_off.bookingpress_staffmember_daysoff_enddate | bookingpress_format_date }}</h4>	
										</div>
									</el-col>
									<el-col :xs="24" :sm="10" :md="10" :lg="10" :xl="10">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading is--body-heading">{{ staffmember_day_off.bookingpress_staffmember_daysoff_name }}</h4>
										</div>
									</el-col>
									<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
										<div class="bpa-card__item">
											<h4 class="bpa-card__item__heading bpa--staff-repeat-holiday-label is--body-heading">{{ staffmember_day_off.dayoff_repeat_label }}</h4>
										</div>
									</el-col>
									<el-col :xs="24" :sm="4" :md="4" :lg="4" :xl="4">
										<?php if ( $bpa_edit_daysoffs ) { ?>
										<div>
											<el-tooltip effect="dark" content="" placement="top" open-delay="300">
												<div slot="content">
													<span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
												</div>
												<el-button class="bpa-btn bpa-btn--icon-without-box" @click="bookingpress_edit_daysoff(staffmember_day_off, event)">
													<span class="material-icons-round">mode_edit</span>
												</el-button>
											</el-tooltip>
											<el-tooltip effect="dark" content="" placement="top" open-delay="300">
												<div slot="content">
													<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
												</div>
												<el-popconfirm
													cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
													confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
													icon="false" 
													title="<?php esc_html_e( 'Are you sure you want to delete holiday?', 'bookingpress-appointment-booking' ); ?>" 
													@confirm="bookingpress_delete_daysoff(staffmember_day_off.bookingpress_staffmember_daysoff_id)" 
													confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
													cancel-button-type="bpa-btn bpa-btn__small">

													<el-button slot="reference" type="text" class="bpa-btn bpa-btn--icon-without-box __danger">
														<span class="material-icons-round">delete</span>
													</el-button>

												</el-popconfirm>    
											</el-tooltip>
										</div>
										<?php } ?>
									</el-col>
								</el-row>
							</div>					
						</el-col>
					</el-row>
				</div>	
			</div>
		</div>
	</div>  
</el-main> 

<el-dialog id="days_off_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--days-off bpa-staff--days-off-dialog" title="" :visible.sync="days_off_add_modal" :close-on-press-escape="close_modal_on_esc" @close="closeStaffmemberDayoff" :modal="is_mask_display" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading" v-if="edit_staffmember_dayoff == ''"><?php esc_html_e( 'Add Holiday', 'bookingpress-appointment-booking' ); ?></h1>
				<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Holiday', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form ref="staffmember_dayoff_form" :rules="rules_dayoff" :model="staffmember_dayoff_form" label-position="top">
							<el-row>							
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item prop="dayoff_date">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Date:', 'bookingpress-appointment-booking' ); ?></span>
										</template>															
										<el-date-picker @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control bpa-form-control--date-range-picker"  v-model="staffmember_dayoff_form.dayoff_date_range" type="daterange" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" start-placeholder="<?php esc_html_e( 'Start Date', 'bookingpress-appointment-booking' ); ?>" end-placeholder="<?php esc_html_e( 'End Date', 'bookingpress-appointment-booking' ); ?>" :picker-options="pickerOptions" @change="change_days_off_date($event)" > </el-date-picker>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">									
									<el-form-item prop="dayoff_name">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Holiday Name:', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input class="bpa-form-control" v-model="staffmember_dayoff_form.dayoff_name" id="dayoff_name" name="dayoff_name" placeholder="<?php esc_html_e( 'Enter holiday name', 'bookingpress-appointment-booking' ); ?>"></el-input>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item>
										<el-checkbox v-model="staffmember_dayoff_form.dayoff_repeat"><span class="bpa-form-label"><?php esc_html_e( 'Repeat Holiday', 'bookingpress-appointment-booking' ); ?></span></el-checkbox>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly" v-if="true == staffmember_dayoff_form.dayoff_repeat">
									<el-form-item>
										<template #label>
                                            <span class="bpa-form-label"><?php esc_html_e( 'Repeat Every', 'bookingpress-appointment-booking'); ?></span>
                                        </template>
										<el-row class="bpa-fbr__sub-row">
											<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
												<el-input-number v-model="staffmember_dayoff_form.dayoff_repeat_frequency" class="bpa-form-control bpa-form-control--number" :min="1" :max="999" placeholder="<?php esc_html_e( 'Enter Holiday Repeat Frequency', 'bookingpress-appointment-booking'); ?>" ></el-input-number>
											</el-col>
											<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
												<el-select class="bpa-form-control" v-model="staffmember_dayoff_form.dayoff_repeat_freq_type" popper-class="bpa-se--holiday-repeat-frequency-type-dropdown">
													<el-option v-for="( rf_label, rf_value ) in staff_dayoff_repeat_frequency_type_opts" :label="rf_label" :value="rf_value"></el-option>
												</el-select>
											</el-col>
										</el-row>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly" v-if="true == staffmember_dayoff_form.dayoff_repeat">
									<el-form-item prop="dayoff_repeat_duration">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking'); ?></span>
										</template>
										<el-select class="bpa-form-control" v-model="staffmember_dayoff_form.dayoff_repeat_duration" popper-class="bpa-se--holiday-repeat-duration-type-dropdown">
											<el-option v-for="( rd_label, rd_value ) in repeat_duration_opts" :label="rd_label" :value="rd_value"></el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly" v-if="true == staffmember_dayoff_form.dayoff_repeat && 'no_of_times' == staffmember_dayoff_form.dayoff_repeat_duration">
									<el-form-item>
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Holiday Repeat Times', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input-number v-model="staffmember_dayoff_form.dayoff_repeat_times" class="bpa-form-control bpa-form-control--number" :min="1"></el-input-number>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly bpa-add-dayoff-col--repeat-date" v-if="true == staffmember_dayoff_form.dayoff_repeat && 'until' == staffmember_dayoff_form.dayoff_repeat_duration">
									<el-form-item prop="repeat_date">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Holiday Repeat Date', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-date-picker :picker-options="StaffholidayPickerOptions" class="bpa-form-control bpa-form-control--date-picker" v-model="staffmember_dayoff_form.dayoff_repeat_date"></el-date-picker>
									</el-form-item>
								</el-col>
							</el-row>
							<el-row class="bpa-do__repeat-yearly">
							</el-row>											
						</el-form>
					</el-col>
				</el-row>
			</div>
		</el-container>
	</div>
	<div class="bpa-dialog-footer">
		<div class="bpa-hw-right-btn-group">
			<el-button class="bpa-btn bpa-btn__small" @click="closeStaffmemberDayoff"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="bookingpress_add_daysoff('staffmember_dayoff_form')" :disabled="staffmember_dayoff_form.is_disabled"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>

<el-dialog id="special_days_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--special-days" title="" :visible.sync="special_days_add_modal" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading" v-if="edit_staffmember_special_day == ''"><?php esc_html_e( 'Add Special Days', 'bookingpress-appointment-booking' ); ?></h1>
				<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Special Days', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form ref="staffmember_special_day_form" :rules="rules_special_day" :model="staffmember_special_day_form" label-position="top">
							<el-row>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item prop="special_day_date">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Date:', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-date-picker @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control bpa-form-control--date-range-picker" v-model="staffmember_special_day_form.special_day_date" type="daterange" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" :picker-options="disablePastDates" placeholder="<?php esc_html_e( 'Select Date', 'bookingpress-appointment-booking' ); ?>" @change="change_special_day_date($event)" range-separator="<?php esc_html_e( 'To', 'bookingpress-appointment-booking' ); ?>" :popper-append-to-body="false" start-placeholder="<?php esc_html_e( 'Start date', 'bookingpress-appointment-booking' ); ?>" end-placeholder="<?php esc_html_e( 'End date', 'bookingpress-appointment-booking' ); ?>">
										</el-date-picker>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item prop="special_day_service">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-select class="bpa-form-control"  v-model="staffmember_special_day_form.special_day_service" name="special_day_service" multiple filterable collapse-tags  placeholder="<?php esc_html_e( 'Applied for all assigned services', 'bookingpress-appointment-booking' ); ?>"
											popper-class="bpa-el-select--is-with-modal">
											<el-option-group v-if="(typeof bookingpress_staff_assign_services_list  != 'undefined' && bookingpress_staff_assign_services_list.length > 0)" v-for="service_cat_data in bookingpress_staff_assign_services_list" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
												<template v-if="service_data.service_id == 0" v-for="service_data in service_cat_data.category_services">
													<el-option :key="service_data.service_id" :label="service_data.service_name" :value="''" ></el-option>
												</template>
												<template v-else>
													<el-option :key="service_data.service_id" :label="service_data.service_name+' ('+service_data.service_price+' )'" :value="service_data.service_id"></el-option>
												</template>
											</el-option-group>
										</el-select>
									</el-form-item>
								</el-col>												
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-row type="flex" class="bpa-sd__time-selection">
										<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
											<el-form-item prop="start_time">												
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-select  v-model="staffmember_special_day_form.start_time" name="start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable> 
													<span slot="prefix" class="material-icons-round">access_time</span>
													<el-option v-for="work_timings in specialday_hour_list"  :label="work_timings.formatted_start_time" :value="work_timings.start_time" ></el-option >
												</el-select>
											</el-form-item>	
										</el-col>
										<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
											<el-form-item prop="end_time">
												<el-select v-model="staffmember_special_day_form.end_time" name="end_time" class="bpa-form-control bpa-form-control__left-icon"	placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>" filterable>
													<span slot="prefix" class="material-icons-round">access_time</span>
													<el-option v-for="work_timings in specialday_hour_list" :label="work_timings.formatted_end_time" :value="work_timings.end_time" v-if="
													work_timings.end_time > staffmember_special_day_form.start_time">
													</el-option>
												</el-select>
											</el-form-item>
										</el-col>
									</el-row>
								</el-col>
							</el-row>
							<el-row>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-sd__add-period-btn">
										<el-link class="bpa-sd__add-period-btn-link" @click="bookingpress_add_special_day_period()">
											<span class="material-icons-round">add_circle</span>
											<?php esc_html_e( 'Add Break', 'bookingpress-appointment-booking' ); ?>
										</el-link>
									</div>
								</el-col>
							</el-row>
							<el-row class="bpa-sd--add-period-row" v-for="special_day_workhours in staffmember_special_day_form.special_day_workhour">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-ts__item">
										<div class="bpa-ts__item-left">
											<el-row type="flex" class="bpa-sd__time-selection">
												<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
													<el-form-item>											
														<template #label>
															<span class="bpa-form-label"><?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?></span>
														</template>
														<el-select v-model="special_day_workhours.start_time" name ="start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable> 
															<span slot="prefix" class="material-icons-round">access_time</span>
															<el-option v-for="work_timings in specialday_break_hour_list" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="work_timings.start_time > staffmember_special_day_form.start_time && work_timings.start_time < staffmember_special_day_form.end_time"></el-option >
														</el-select>
													</el-form-item>	
												</el-col>
												<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
													<el-form-item>
														<el-select v-model="special_day_workhours.end_time" name ="end_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>" filterable>
															<span slot="prefix" class="material-icons-round">access_time</span>
															<el-option v-for="work_timings in specialday_break_hour_list" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="((work_timings.start_time > staffmember_special_day_form.start_time && work_timings.start_time < staffmember_special_day_form.end_time) && (work_timings.start_time > special_day_workhours.start_time))">
															</el-option>
														</el-select>
													</el-form-item>
												</el-col>
											</el-row>
										</div>
										<div class="bpa-ts__item-right">
											<div class="bpa-sd__add-period-btn">
												<el-link class="bpa-sd__add-period-btn-link"  @click="bookingpress_remove_special_day_period(special_day_workhours.id)">
													<span class="material-icons-round">remove_circle</span>
												</el-link>
											</div>
										</div>
									</div>
								</el-col>
							</el-row>
						</el-form>
					</el-col>
				</el-row>
			</div>
		</el-container>
	</div>
	<div class="bpa-dialog-footer">
		<div class="bpa-hw-right-btn-group">
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="addStaffmemberSpecialday('staffmember_special_day_form')" :disabled="staffmember_special_day_form.is_disabled"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small" @click="close_special_days_func"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>
<?php
if ( ! is_rtl() ) {
	?>
		<el-dialog id="staffmember_breaks_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-break" title="" :visible.sync="open_add_break_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display">
	<?php
} else {
	?>
		<el-dialog id="staffmember_breaks_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-break" title="" :visible.sync="open_add_break_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display">
	<?php
}
?>
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16" v-if="is_edit_break == '0'"> 
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Add Break', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16" v-else>
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Edit Break', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form :rules="rules_add_break"  ref="break_timings" :model="break_timings" label-position="top" @submit.native.prevent>
							<div class="bpa-form-body-row">
								<el-row :gutter="24">
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<el-form-item prop="start_time">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-select class="bpa-form-control bpa-form-control__left-icon" v-model="break_timings.start_time" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable>
												<span slot="prefix" class="material-icons-round">access_time</span>
												<el-option v-for="break_times in default_break_timings" :key="break_times.start_time" :label="break_times.formatted_start_time" :value="break_times.start_time" v-if="break_times.start_time > workhours_timings[break_selected_day].start_time && break_times.start_time < workhours_timings[break_selected_day].end_time"></el-option>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<el-form-item prop="end_time">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-select class="bpa-form-control bpa-form-control__left-icon"  v-model="break_timings.end_time" placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>" filterable>
												<span slot="prefix" class="material-icons-round">access_time</span>
												<el-option v-for="break_times in default_break_timings" :key="break_times.start_time" :label="break_times.formatted_start_time" :value="break_times.start_time"
												v-if="(break_times.start_time > workhours_timings[break_selected_day].start_time && break_times.start_time < workhours_timings[break_selected_day].end_time) && (break_times.start_time > break_timings.start_time)"
												></el-option>
											</el-select>
										</el-form-item>
									</el-col>
								</el-row>
							</div>
						</el-form>
					</el-col>
				</el-row>
			</div>
		</el-container>
	</div>
	<div class="bpa-dialog-footer">
		<div class="bpa-hw-right-btn-group">
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="save_break_data"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small" @click="close_add_break_model"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>