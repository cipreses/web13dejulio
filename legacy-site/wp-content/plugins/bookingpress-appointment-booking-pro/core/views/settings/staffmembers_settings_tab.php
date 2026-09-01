<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body"  name ="staffmembers_settings" label="staffmembers" data-tab_name="staffmembers_settings">
	<span slot="label">
		<i class="material-icons-round">groups</i>
		<?php echo esc_html($bookingpress_plural_staffmember_name); ?>
	</span>
	<div class="bpa-general-settings-tabs--pb__card">
		<el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading __bpa-is-groupping">
			<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
				<h1 class="bpa-page-heading"><?php esc_html($bookingpress_plural_staffmember_name)." ".esc_html__('Settings', 'bookingpress-appointment-booking'); ?></h1>
			</el-col>
			<el-col :xs="24" :sm="24" :md="12" :lg="16" :xl="12">
				<div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">									
					<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('staffmembers_settings_form','staffmember_setting')" :disabled="is_disabled" >					
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
			<el-form :rules="staffmembers_settings" ref="staffmembers_settings_form" :model="staffmembers_settings_form" @submit.native.prevent>	
				<div class="bpa-gs__cb--item">
					<div class="bpa-gs__cb--item-heading">
						<h4 class="bpa-sec--sub-heading"><?php echo esc_html($bookingpress_singular_staffmember_name)." ".esc_html__('Settings', 'bookingpress-appointment-booking'); ?></h4>
					</div>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php echo esc_html__( 'Enable Any','bookingpress-appointment-booking').' '.esc_html( $bookingpress_singular_staffmember_name ).' '.esc_html__('option', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_staffmember_any_staff_options">								
								<el-tooltip effect="dark" content="" placement="top" v-if="hide_staffmember_selection == 'true' && hide_staffmember_selection != ''">
									<div slot="content">
										<span><?php esc_html_e( 'you have hidden the staff member selection at the frontend', 'bookingpress-appointment-booking' ); ?></span><br/>
										<span><?php esc_html_e( 'so you will not be able to turn this switch OFF.', 'bookingpress-appointment-booking' ); ?></span>
									</div>
									<el-switch class="bpa-swtich-control"  v-model="staffmembers_settings_form.bookingpress_staffmember_any_staff_options" disabled></el-switch>
								</el-tooltip>
								<el-switch class="bpa-swtich-control"  v-model="staffmembers_settings_form.bookingpress_staffmember_any_staff_options" v-else></el-switch>							
							</el-form-item>
						</el-col>
					</el-row>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" v-if="staffmembers_settings_form.bookingpress_staffmember_any_staff_options == true">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4><?php esc_html_e( 'Auto assignment rule', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_staffmember_auto_assign_rule">
								<el-select class="bpa-form-control" v-model="staffmembers_settings_form.bookingpress_staffmember_auto_assign_rule" popper-class="bpa-el-select--any-staff-rules">
									<el-option v-for="item in staffmember_auto_assign_rule_list" :key="item.text" :label="item.text"  :value="item.value"></el-option>					
								</el-select>					
							</el-form-item>	
						</el-col>
					</el-row>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php echo esc_html__( 'Allow','bookingpress-appointment-booking').' '.esc_html( $bookingpress_singular_staffmember_name ).' '.esc_html__('to Access wordpress admin panel', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_staffmember_access_admin">									
								<el-switch class="bpa-swtich-control"  v-model="staffmembers_settings_form.bookingpress_staffmember_access_admin"></el-switch>							
							</el-form-item>
						</el-col>
					</el-row>
				</div>
				<div class="bpa-gs__cb--item">
					<div class="bpa-gs__cb--item-heading">
						<h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Appointments', 'bookingpress-appointment-booking' ); ?></h4>
					</div>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Manage Appointments', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_edit_appointments">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_edit_appointments"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Delete Appointments', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>						
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_delete_appointments">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_delete_appointments"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>	
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Export Appointments', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_export_appointments">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_export_appointments"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>
				</div>
				<div class="bpa-gs__cb--item">
					<div class="bpa-gs__cb--item-heading">
						<h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Payments', 'bookingpress-appointment-booking' ); ?></h4>
					</div>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'View Payments', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_payments">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_payments"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Edit Payments', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_edit_payments">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_edit_payments"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Delete Payments', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_delete_payments">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_delete_payments"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>	
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Export Payments', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_export_payments">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_export_payments"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>
				</div>
				<div class="bpa-gs__cb--item">
					<div class="bpa-gs__cb--item-heading">
						<h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Customers', 'bookingpress-appointment-booking' ); ?></h4>
					</div>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'View Customers', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_customers">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_customers"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Manage Customers', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_edit_customers">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_edit_customers"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>	
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Delete Customers', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_delete_customers">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_delete_customers"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>						
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Export Customers', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_export_customers">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_export_customers"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>
				</div>
				<div class="bpa-gs__cb--item">
					<div class="bpa-gs__cb--item-heading">
						<h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Profile', 'bookingpress-appointment-booking' ); ?></h4>
					</div>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Manage Working Hours', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_edit_workhours">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_edit_workhours"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Manage Holiday', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_edit_daysoffs">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_edit_daysoffs"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Manage Special Days', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">
							<el-form-item prop="bookingpress_edit_daysoffs">
								<el-switch class="bpa-swtich-control" v-model="staffmembers_settings_form.bookingpress_edit_special_days"></el-switch>
							</el-form-item>
						</el-col>
					</el-row>
				</div>

				<div class="bpa-gs__cb--item bpa-gs_rename_staff_settings">
					<div class="bpa-gs__cb--item-heading">
						<h4 class="bpa-sec--sub-heading"><?php esc_html_e('Rename Staff Member Module', 'bookingpress-appointment-booking'); ?></h4>
					</div>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Singular Staff Member Label', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
							<el-form-item prop="">
								<el-input class="bpa-form-control" v-model="staffmembers_settings_form.bookingpress_staffmember_module_singular_name"></el-input>
								<label class="bpa-form-label bpa-gs_rename_staff_settings_example"><?php esc_html_e('For example, Doctor, Employee etc...','bookingpress-appointment-booking'); ?></label>
							</el-form-item>
						</el-col>
					</el-row>
					<el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">						
						<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
							<h4> <?php esc_html_e( 'Plural Staff Member Label', 'bookingpress-appointment-booking' ); ?></h4>
						</el-col>
						<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
							<el-form-item prop="">
								<el-input class="bpa-form-control" v-model="staffmembers_settings_form.bookingpress_staffmember_module_plural_name"></el-input>
								<label class="bpa-form-label bpa-gs_rename_staff_settings_example"><?php esc_html_e('For example, Doctors, Employees etc...','bookingpress-appointment-booking'); ?></label>
							</el-form-item>
						</el-col>
					</el-row>
				</div>

			</el-form>
		</div>
	</div>
</el-tab-pane>
