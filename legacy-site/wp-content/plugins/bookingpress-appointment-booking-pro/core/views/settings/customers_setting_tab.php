
<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body" name ="customer_settings" label="customers" data-tab_name="customer_settings">
    <span slot="label">
        <i class="material-icons-round">supervisor_account</i>
        <?php esc_html_e('Customers', 'bookingpress-appointment-booking'); ?>
    </span>
    <div class="bpa-general-settings-tabs--pb__card bpa-payment-settings-tabs--pb__card">
        <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading __bpa-is-groupping">
            <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Customer Settings', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">    
                    <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('customer_setting_form','customer_setting')" :disabled="is_disabled" >                    
                      <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                      <div class="bpa-btn--loader__circles">                    
                          <div></div>
                          <div></div>
                          <div></div>
                      </div>
                    </el-button>
					<?php do_action('bookingpress_customer_setting_header_button'); ?>
                </div>
            </el-col>
        </el-row>
        <div class="bpa-gs--tabs-pb__content-body">
            <el-form id="customer_setting_form" ref="customer_setting_form" @submit.native.prevent>
                <div class="bpa-gs__cb--item">
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Create WordPress user upon appointment booking', 'bookingpress-appointment-booking'); ?></h4>
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-right">                
                            <el-form-item>
								<el-switch class="bpa-swtich-control" v-model="customer_setting_form.allow_wp_user_create"></el-switch>
                            </el-form-item>
                        </el-col>
                    </el-row>
					<div class="bpa-gs__cb--item-heading">
						<h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Custom Field Settings', 'bookingpress-appointment-booking' ); ?></h4>
					</div>
					<div class="bpa-gs__cb--item-body">
						<el-row type="flex">							
							<el-col :xs="24" :sm="24" :md="18" :lg="16" :xl="20">
								<div class="bpa-customer-field-settings-body-container" id="bpa-customer-custom-fields-settings">
									<el-row id="bpa-customer-draggable-container">
										<el-col class="bpa-customer-field-container bpa-field-wrapper-container bpa-field_outer-container bpa-customer-field-empty-container" v-if="customer_field_settings == '' || customer_field_settings.length < 1">
											<picture>
												<source srcset="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp'); ?>" type="image/webp">
												<img src="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png'); ?>">
											</picture>
											<h4><?php esc_html_e('No Customer Field added!', 'bookingpress-appointment-booking'); ?></h4>
										</el-col>
										<el-col class="bpa-customer-field-container bpa-field-wrapper-container bpa-field-outer-container" :data-field-id="customer_field_data.id" :data-metakey="customer_field_data.meta_key" :data-id="fskey" :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="customer_field_data.id !== undefined" v-for="(customer_field_data, fskey) in customer_field_settings" :key="customer_field_data.id">
											<div class="bpa-cfs-item-card">
												<div class="bpa-cfs-ic__body">
													<div class="bpa-cfs-ic--head">
														<div class="bpa-cfs-ic--head__type-label">
															<span class="material-icons-round">drag_indicator</span>
															<p>{{ customer_field_data.field_type }}</p>
														</div>
														<div class="bpa-cfs-ic--head__field-controls">
															<div class="bpa-cfs-ic--head__fc-actions" v-if="(customer_field_data.field_type == 'Checkbox' || customer_field_data.field_type == 'Radio' || customer_field_data.field_type == 'Dropdown' )">
																<el-tooltip effect="dark" content="" placement="top" open-delay="0">
																	<div slot="content">
																		<span><?php esc_html_e( 'Field Options', 'bookingpress-appointment-booking' ); ?></span>
																	</div>
																	<el-popover width="550" placement="bottom-end" v-model="customer_field_data.is_edit_values">
																		<el-container class="bpa-field-settings-edit-container bpa-field-values-edit-container">
																			<el-row type="flex" class="bpa-field-values-row-with-border">
																				<el-col :xs="18" :sm="18" :md="18" :lg="18" :xl="18" class="bpa-cs__heading">
																					<h3><?php esc_html_e( 'Manage Options', 'bookingpress-appointment-booking' ); ?></h3>
																				</el-col>
																				<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6" class="bpa-cs__heading">
																					<el-button style="top:50%;transform:translateY(-50%);" @click="bpaCustomerDisplayPresetValues(fskey)" class="bpa-btn bpa-btn__small bpa-btn--full-width"><?php esc_html_e( 'Preset Values', 'bookingpress-appointment-booking' ); ?></el-button>
																				<el-col>
																			</el-row>
																			<el-row type="flex" class="bpa-field-values-row-with-border" v-if="customer_field_data.enable_preset_fields == true" >
																				<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16" class="bpa-cs__heading">
																					<el-select placeholder="<?php esc_html_e( 'Select Preset Values', 'bookingpress-appointment-booking' ); ?>" class="bpa-form-control" v-model="customer_field_data.preset_field_choice" popper-class="popover_select_control">
																						<el-option v-for="item in bookingpress_preset_fields" :key="item.id" :label="item.name" :value="item.id"></el-option>
																					</el-select>
																				</el-col>&nbsp;
																				<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8" class="bpa-cs__heading bpa-field-preset-values-btn-wrapper">
																					<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" :disabled="preset_btn_disable" @click="applyCustomerPresetFields(fskey)" :class="(is_display_preset_value_loader == '1') ? 'bpa-btn--is-loader' : ''">
																					<span class="bpa-btn__label"><?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?></span>
																						<div class="bpa-btn--loader__circles">				    
																							<div></div>
																							<div></div>
																							<div></div>
																						</div>
																					</el-button>
																					<el-button class="bpa-btn bpa-btn__medium bpa-field-values-cancel-preset-btn" @click="bpaHideCustomerPresetValues(fskey)"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
																				</el-col>
																			</el-row>
																			<el-row type="flex">
																				<el-col :xs="22" :sm="22" :md="22" :lg="22" :xl="22" class="bpa-cs__heading">
																					<label class="bpa-form-label"><?php esc_html_e( 'Use Separate Value', 'bookingpress-appointment-booking' ); ?></label>
																				</el-col>
																				<el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading">
																					<el-switch class="bpa-swtich-control" v-model="customer_field_data.field_options.separate_value"></el-switch>
																				</el-col>
																			</el-row>
																			<el-row type="flex" class="bpa-field-values-row-no-top-padding bpa-field-values-row-with-border bpa-field-values-options-wrapper">
																				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																					<el-row type="flex" class="bpa-cs__heading bpa-cs__values_heading">
																						<el-col class="bpa-cs__field_value_label_heading"><?php esc_html_e( 'Option Label', 'bookingpress-appointment-booking' ); ?></el-col>
																						<el-col v-if="customer_field_data.field_options.separate_value == 'true' || customer_field_data.field_options.separate_value == true" class="bpa-cs__field_value_label_heading"><?php esc_html_e( 'Option Value', 'bookingpress-appointment-booking' ); ?></el-col>
																						<el-col class="bpa-cs__field_value_icon">
																							<el-button class="bpa-btn bpa-btn--icon-without-box" @click="bpaCustomerAddfieldValue(fskey)">
																								<span class="material-icons-round">add_circle</span>
																							</el-button>
																						</el-col>
																					</el-row>
																					<el-row type="flex" class="bpa-cs__heading bpa-cs__values_items" v-for="(items, i_key) in customer_field_data.field_values" :key="i_key">
																						<el-col class="bpa-cs__field_value_label">
																							<el-input class="bpa-form-field-value-input" v-model="items.label"></el-input>
																						</el-col>
																						<el-col v-if="customer_field_data.field_options.separate_value == 'true' || customer_field_data.field_options.separate_value == true" class="bpa-cs__field_value_label">
																							<el-input class="bpa-form-field-value-input" v-model="items.value"></el-input>
																						</el-col>
																						<el-col class="bpa-cs__field_value_icon">
																							<el-button class="bpa-btn bpa-btn--icon-without-box" @click="bpaCustomerRemovefieldValue(i_key, fskey)">
																								<span class="material-icons-round">remove_circle</span>
																							</el-button>
																						</el-col>
																					</el-row>
																				</el-col>
																			</el-row>
																			<el-row class="bpa-field-values-row-no-top-padding bpa-cs-field-values-btn-wrapper">
																				<div class="bpa-customize--edit-label-popover--actions">
																					<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="closeCustomerFieldValueBtn(fskey)"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
																				</div>
																			</el-row>
																		</el-container>
																		<el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
																			<span class="material-icons-round">rule</span>
																		</el-button>
																	</el-popover>
																</el-tooltip>
															</div>
															<div class="bpa-cfs-ic--head__fc-actions">
																<el-tooltip effect="dark" content="" placement="top" open-delay="0">
																	<div slot="content">
																		<span><?php esc_html_e( 'Field Settings', 'bookingpress-appointment-booking' ); ?></span>
																	</div>
																	<el-popover placement="bottom-end" v-model="customer_field_data.is_edit">
																		<el-container class="bpa-field-settings-edit-container">
																			<div class="bpa-fs-item-settings-form-control-item">
																				<label class="bpa-form-label"><?php esc_html_e( 'Label', 'bookingpress-appointment-booking' ); ?></label>
																				<el-input class="bpa-form-control" v-model="customer_field_data.label"></el-input>
																			</div>
																			<div class="bpa-fs-item-settings-form-control-item" v-if="customer_field_data.field_type != 'Checkbox' && customer_field_data.field_type != 'Radio' && customer_field_data.field_type != 'File'">
																				<label class="bpa-form-label"><?php esc_html_e( 'Placeholder', 'bookingpress-appointment-booking' ); ?></label>
																				<el-input class="bpa-form-control" v-model="customer_field_data.placeholder"></el-input>
																			</div>
																			<div class="bpa-fs-item-settings-form-control-item">
																				<label class="bpa-form-label"><?php esc_html_e( 'Meta Key', 'bookingpress-appointment-booking' ); ?></label>
																				<el-input class="bpa-form-control" v-model="customer_field_data.meta_key"></el-input>
																			</div>
																			<div class="bpa-fs-item-settings-form-control-item" v-if="customer_field_data.field_type == 'Date'">
																				<el-col :xs="18" :sm="18" :md="18" :lg="18" :xl="18" class="bpa-cs__heading">
																					<label class="bpa-form-label"><?php esc_html_e( 'Enable Time Picker', 'bookingpress-appointment-booking' ); ?></label>
																				</el-col>
																				<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6" class="bpa-cs__heading">
																					<el-switch class="bpa-swtich-control" v-model="customer_field_data.field_options.enable_timepicker"></el-switch>
																				</el-col>
																			</div>
																			<div class="bpa-customize--edit-label-popover--actions">
																				<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="saveCustomerFieldSettings(fskey)"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
																			</div>
																		</el-container>
																		<el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
																			<span class="material-icons-round">settings</span>
																		</el-button>
																	</el-popover>
																</el-tooltip>	
															</div>
															<div class="bpa-cfs-ic--head__fc-actions">
																<el-tooltip effect="dark" content="" placement="top" open-delay="0">
																	<div slot="content">
																		<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
																	</div>
																	<el-button @click="deleteCustomerField(fskey)" class="bpa-btn bpa-btn--icon-without-box __danger">
																		<span class="material-icons-round">delete</span>
																	</el-button>
																</el-tooltip>
															</div>
														</div>
													</div>
													<div class="bpa-cfs-ic--body">
														<div class="bpa-cfs-ic--body__field-preview">
															<span class="bpa-form-label" v-text="customer_field_data.label"></span>
															<el-input class="bpa-form-control" v-if='(customer_field_data.field_type == "Text" || customer_field_data.field_type == "Email" || customer_field_data.field_type == "Phone")' :placeholder="customer_field_data.placeholder"></el-input>
															<el-input class="bpa-form-control" v-if='customer_field_data.field_type == "Textarea"' :placeholder="customer_field_data.placeholder" type="textarea" :rows="3"></el-input>
															<template v-if='customer_field_data.field_type == "Checkbox"'>
																<el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" v-if="keys < 5" v-for="(chk_data, keys) in customer_field_data.field_values" :label="chk_data.label" :key="chk_data.value"><div v-html="chk_data.label"></div></el-checkbox>
															</template>
															<template v-if='customer_field_data.field_type == "Radio"'>
																<el-radio class="bpa-form-label bpa-custom-radio--is-label" v-if="keys < 5" v-for="(chk_data, keys) in customer_field_data.field_values" :label="chk_data.label" :key="chk_data.value">{{chk_data.label}}</el-radio>
															</template>
															<template v-if='customer_field_data.field_type == "Dropdown"'>
																<el-select class="bpa-form-control" :placeholder="customer_field_data.placeholder">
																	<el-option v-for="sel_data in customer_field_data.field_values" :key="sel_data.value" :label="sel_data.label" :value="sel_data.value" ></el-option>
																</el-select>
															</template>
															<el-date-picker class="bpa-form-control" prefix-icon="" v-if='customer_field_data.field_type == "Date"' :placeholder="customer_field_data.placeholder" :type="customer_field_data.field_options.enable_timepicker ? 'datetime' : 'date'"></el-date-picker>
														</div>
													</div>
												</div>
											</div>
										</el-col>
									</el-row>
								</div>
							</el-col>
							<el-col :xs="24" :sm="24" :md="6" :lg="8" :xl="4" id="bpa-fields-box">
								<div class="bpa-customer-step-side-panel bpa-fs-controls-sidebar">
									<div class="bpa-cs__heading">
										<h4><?php esc_html_e( 'Form Elements', 'bookingpress-appointment-booking' ); ?></h4>
									</div>
									<div class="bpa-cs__items" id="bpa-input-fields">
										<div data-type="single_line" onClick="BPACSortable.add_item_to_form('single_line')" class="bpa-cs__item">
											<span class="material-icons-round">short_text</span>
											<p><?php esc_html_e( 'Text Field', 'bookingpress-appointment-booking' ); ?></p>
										</div>
										<div data-type="textarea" onClick="BPACSortable.add_item_to_form('textarea')" class="bpa-cs__item">
											<span class="material-icons-round">notes</span>
											<p><?php esc_html_e( 'Textarea', 'bookingpress-appointment-booking' ); ?></p>
										</div>
										<div data-type="checkbox" onClick="BPACSortable.add_item_to_form('checkbox')" class="bpa-cs__item">
											<span class="material-icons-round">check_box</span>
											<p><?php esc_html_e( 'Checkbox', 'bookingpress-appointment-booking' ); ?></p>
										</div>
										<div data-type="radio" onClick="BPACSortable.add_item_to_form('radio')" class="bpa-cs__item">
											<span class="material-icons-round">radio_button_checked</span>
											<p><?php esc_html_e( 'Radio', 'bookingpress-appointment-booking' ); ?></p>
										</div>
										<div data-type="dropdown" onClick="BPACSortable.add_item_to_form('dropdown')" class="bpa-cs__item">
											<span class="material-icons-round">arrow_drop_down_circle</span>
											<p><?php esc_html_e( 'Dropdown', 'bookingpress-appointment-booking' ); ?></p>
										</div>
										<div data-type="datepicker" onClick="BPACSortable.add_item_to_form('datepicker')" class="bpa-cs__item">
											<span class="material-icons-round">insert_invitation</span>
											<p><?php esc_html_e( 'DatePicker', 'bookingpress-appointment-booking' ); ?></p>
										</div>
									</div>
								</div>
							</el-col>
						</el-row>
					</div>
				</div>
			<el-form>
		</div>
	</div>
</el-tab-pane>