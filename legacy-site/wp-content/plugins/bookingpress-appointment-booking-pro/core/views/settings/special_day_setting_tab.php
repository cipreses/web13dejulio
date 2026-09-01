<?php
	global $bookingpress_common_date_format;
?>
<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body" name ="specialday_settings"  label="hours-days-off" data-tab_name="specialday_settings">
	<span slot="label">
		<i class="material-icons-round">today</i>
		<?php esc_html_e( 'Special days', 'bookingpress-appointment-booking' ); ?>
	</span>
	<div class="bpa-general-settings-tabs--pb__card bpa-special-hours-tab--pb__card">
		<el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
			<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Special days', 'bookingpress-appointment-booking' ); ?></h1>				
			</el-col>
			<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
				<div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">									
					<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveEmployeeSpecialdays()" :disabled="is_disabled" >					
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
			<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<el-form class="bpa-sh-form--add-items" ref="special_day_form" :rules="rules_special_day" :model="special_day_form" label-position="top">
						<div class="bpa-gs__cb--item">
							<el-row :gutter="20" type="flex" class="bpa-sd__form-row">
								<el-col :xs="12" :sm="12" :md="12" :lg="10" :xl="10">													
									<el-form-item prop="special_day_date" class="bpa-form-item__date-range-col">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-date-picker @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control bpa-form-control--date-range-picker" v-model="special_day_form.special_day_date" type="daterange" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" value-format="yyyy-MM-dd" :picker-options="disablePastDates" placeholder="<?php esc_html_e( 'Select Date', 'bookingpress-appointment-booking' ); ?>" range-separator=" - " :append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-date-range-picker-widget-wrapper" start-placeholder="<?php esc_html_e( 'Start date', 'bookingpress-appointment-booking' ); ?>" end-placeholder="<?php esc_html_e( 'End date', 'bookingpress-appointment-booking' ); ?>">
										</el-date-picker>
									</el-form-item>
								</el-col>												
								<el-col :xs="12" :sm="12" :md="12" :lg="10" :xl="10">
									<el-row :gutter="24" type="flex" class="bpa-sd__time-selection">
										<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
											<el-form-item prop="start_time">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-select v-model="special_day_form.start_time" class="bpa-form-control bpa-form-control__left-icon" filterable placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>"> 
													<span slot="prefix" class="material-icons-round">access_time</span>
													<el-option v-for="work_timings in specialday_hour_list" :label="work_timings.formatted_start_time" :value="work_timings.start_time" ></el-option >
												</el-select>
											</el-form-item>	
										</el-col>
										<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
											<el-form-item prop="end_time">
												<el-select v-model="special_day_form.end_time" class="bpa-form-control bpa-form-control__left-icon"	filterable placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>">
													<span slot="prefix" class="material-icons-round">access_time</span>
													<el-option v-for="work_timings in specialday_hour_list" :label="work_timings.formatted_end_time" :value="work_timings.end_time" v-if="work_timings.end_time > special_day_form.start_time">
													</el-option>
												</el-select>
											</el-form-item>
										</el-col>
									</el-row>
								</el-col>
								<el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
									<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="closeSpecialday()" ><?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?></el-button>
								</el-col>
								<el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
									<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="addSpecialday('special_day_form')" ><?php esc_html_e( 'Add', 'bookingpress-appointment-booking' ); ?></el-button>
								</el-col>
							</el-row>
							<el-row :gutter="20">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-sd__add-period-btn">
										<el-link class="bpa-sd__add-period-btn-link" @click="bookingpress_add_special_day_period()">
											<span class="material-icons-round">add_circle</span>
											<?php esc_html_e( 'Add Break', 'bookingpress-appointment-booking' ); ?>
										</el-link>
									</div>								
								</el-col>
							</el-row>										
							<el-row class="bpa-sd--add-period-row" v-for="special_day_workhours in special_day_form.special_day_workhour">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-ts__item">
										<div class="bpa-ts__item-left">
											<el-row :gutter="24" type="flex" class="bpa-sd__time-selection">
												<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
													<el-form-item prop="start_time">											
														<template #label>
															<span class="bpa-form-label"><?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?></span>
														</template>
														<el-select v-model="special_day_workhours.start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable> 
															<span slot="prefix" class="material-icons-round">access_time</span>
															<el-option v-for="work_timings in specialday_break_hour_list" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="work_timings.start_time > special_day_form.start_time && work_timings.start_time < special_day_form.end_time"></el-option>
														</el-select>
													</el-form-item>
												</el-col>
												<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
													<el-form-item prop="end_time">
														<el-select v-model="special_day_workhours.end_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>" filterable>
															<span slot="prefix" class="material-icons-round">access_time</span>
															<el-option v-for="work_timings in specialday_break_hour_list" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="((work_timings.start_time > special_day_form.start_time && work_timings.start_time < special_day_form.end_time) && (work_timings.end_time > special_day_workhours.start_time))">
															</el-option>
														</el-select>
													</el-form-item>
												</el-col>
											</el-row>
										</div>
										<div class="bpa-ts__item-right">
											<div class="bpa-sd__add-period-btn">
												<el-link class="bpa-sd__add-period-btn-link" @click="bookingpress_remove_special_day_period(special_day_workhours.id)">
													<span class="material-icons-round">remove_circle</span>
												</el-link>
											</div>
										</div>
									</div>
								</el-col>
							</el-row>
						</div>
					</el-form>
				</el-col>
			</el-row>	
			<!-- === -->
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-grid-list-container bpa-dc__staff--assigned-service">
						<div class="bpa-as__body">
							<el-row class="bpa-assigned-service-body" v-if="special_day_data_arr.length > 0">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-card bpa-card__heading-row">
										<el-row type="flex">
											<el-col :xs="7" :sm="7" :md="7" :lg="7" :xl="7">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
											<el-col :xs="7" :sm="7" :md="7" :lg="7" :xl="7">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Workhours', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
											<el-col :xs="7" :sm="7" :md="6" :lg="7" :xl="7">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
											<el-col :xs="2" :sm="2" :md="3" :lg="2" :xl="2">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Action', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
										</el-row>
									</div>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="special_day_data in special_day_data_arr">
									<div class="bpa-card bpa-card__body-row list-group-item">
										<el-row type="flex">
											<el-col :xs="7" :sm="7" :md="7" :lg="7" :xl="7">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading">{{ special_day_data.special_day_formatted_start_date }} - {{ special_day_data.special_day_formatted_end_date }}</h4>
												</div>
											</el-col>
											<el-col :xs="7" :sm="7" :md="7" :lg="7" :xl="7">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading">( {{special_day_data.formatted_start_time}} - {{special_day_data.formatted_end_time}} )</h4>
												</div>
											</el-col>	
											<el-col :xs="7" :sm="7" :md="6" :lg="7" :xl="7">
												<div class="bpa-card__item">									
													<span v-if="special_day_data.special_day_workhour != undefined && special_day_data.special_day_workhour != ''">
														<h4 class="bpa-card__item__heading is--body-heading" v-if="special_day_workhours.formatted_start_time != undefined && special_day_workhours.formatted_start_time != '' && special_day_workhours.formatted_end_time != undefined && special_day_workhours.formatted_end_time != '' && special_day_workhours.start_time != '' && special_day_workhours.end_time != ''"  v-for="special_day_workhours in special_day_data.special_day_workhour">
														( {{ special_day_workhours.formatted_start_time }} - {{special_day_workhours.formatted_end_time}} )
														</h4>
													</span>	
													<span v-else>-</span>
												</div>
											</el-col>
											<el-col :xs="2" :sm="2" :md="3" :lg="2" :xl="2">
												<div>
													<el-tooltip effect="dark" content="" placement="top" open-delay="300">
														<div slot="content">
															<span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
														</div>
														<el-button class="bpa-btn bpa-btn--icon-without-box" @click="show_edit_special_day_div(special_day_data.id)">
															<span class="material-icons-round">mode_edit</span>
														</el-button>
													</el-tooltip>
													<el-tooltip effect="dark" content="" placement="top" open-delay="300">
														<div slot="content">
															<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
														</div>
														<el-button class="bpa-btn bpa-btn--icon-without-box __danger" @click="delete_special_day_div(special_day_data.id)">
															<span class="material-icons-round">delete</span>
														</el-button>
													</el-tooltip>
												</div>
											</el-col>
										</el-row>
									</div>
								</el-col>
							</el-row>
						</div>
					</div>
				</el-col>
			</el-row>	
		</div>	
	</div>
</el-tab-pane>

<?php
