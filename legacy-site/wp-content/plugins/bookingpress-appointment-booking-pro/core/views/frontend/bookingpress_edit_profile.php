<el-main class="bpa-frontend-main-container" id="bookingpress_edit_profile_form_<?php echo esc_html( $bookingpress_uniq_id ); ?>">	
	<div class="bpa-front-tabs--panel-body __bpa-is-active" >	
		<div class="bpa-front-default-card">		
			<div class="bpa-front-toast-notification --bpa-error" v-if="is_display_error == '1'">
				<div class="bpa-front-tn-body">
					<p>{{ is_error_msg }}</p>
					<a href="#" class="bpa-close-icon"><span class="material-icons-round">close</span></a>
				</div>
			</div> 
			<div class="bpa-front-toast-notification --bpa-success" v-if="is_display_success == '1'">
				<div class="bpa-front-tn-body">
					<p>{{ is_success_msg }}</p>
				</div>
			</div> 
			<div class="bpa-front-dc--body">			
				<el-row> 
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<div class="bpa-front-module-container bpa-front-module--basic-details">															
							<el-row> 
								<el-col>
									<el-form :model="edit_profile_form_data" :rules="bookingpress_customer_details_rule" ref="edit_profile_form_data">
										<div class="bpa-front-module--bd-form">
											<el-row class="bpa-bd-fields-row">
												<h4><?php esc_html_e( 'Edit Profile', 'bookingpress-appointment-booking' ); ?></h4>
											</row></br>	
											<el-row class="bpa-bd-fields-row">
												<el-col :class="[( customer_form_fields_data.field_options.layout == '2col' ? 'bpa-bd-fields--two-col-container' : '' ), ( customer_form_fields_data.field_options.layout == '3col' ? 'bpa-bd-fields--three-col-container' : '' ),( customer_form_fields_data.field_options.layout == '4col' ? 'bpa-bd-fields--four-col-container' : '' ),( customer_form_fields_data.field_options.layout != '1col' ? '' : '' )]" :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="customer_form_fields_data in customer_form_fields" v-if="customer_form_fields_data.is_hide != '1'"> 
													<template v-if="customer_form_fields_data.field_options.layout == '1col'">
														<div class="bpa-bdf--single-col-item">
															<el-form-item :prop="customer_form_fields_data.v_model_value" :ref="customer_form_fields_data.v_model_value" v-if="customer_form_fields_data.field_name != 'phone_number' && customer_form_fields_data.is_hide != '1'">
																<template #label> 
																	<span class="bpa-front-form-label">{{ customer_form_fields_data.label }}</span>		
																</template> 
																<el-input v-model="edit_profile_form_data[customer_form_fields_data['v_model_value']]" class="bpa-front-form-control" :type="(customer_form_fields_data.field_type == 'Email' ? 'email' : 'text')" v-if='(customer_form_fields_data.field_type == "Text" || customer_form_fields_data.field_type == "Email" || customer_form_fields_data.field_type == "Phone")' :placeholder="customer_form_fields_data.placeholder" :disabled="customer_form_fields_data.field_type == 'Email' ? true :false"></el-input>
																
																<div v-if='customer_form_fields_data.field_type == "Checkbox"'>																	
																	<el-checkbox v-model="edit_profile_form_data[customer_form_fields_data.meta_key+'_'+keys]" class="bpa-front-form-control--checkbox" v-for="(chk_data, keys) in customer_form_fields_data.field_values" :label="chk_data.label" :key="chk_data.value">{{chk_data.label}}</el-checkbox>
																</div>
																<div v-if='customer_form_fields_data.field_type == "Radio"'>
																	<el-radio v-model="edit_profile_form_data[customer_form_fields_data['v_model_value']]" v-for="(chk_data, keys) in customer_form_fields_data.field_values" :label="chk_data.label" class="bpa-front-form-control--radio" :key="chk_data.value">{{chk_data.label}}</el-radio>
																</div>
																<div v-if='customer_form_fields_data.field_type == "Dropdown"'>
																	<el-select v-model="edit_profile_form_data[customer_form_fields_data['v_model_value']]" class="bpa-front-form-control" :placeholder="customer_form_fields_data.placeholder">
																		<el-option v-for="sel_data in customer_form_fields_data.field_values" :key="sel_data.value" :label="sel_data.label" :value="sel_data.value" ></el-option>
																	</el-select>
																</div>
																<div v-if='customer_form_fields_data.field_type == "Date"'>
																	<el-date-picker v-model="edit_profile_form_data[customer_form_fields_data['v_model_value']]" class="bpa-front-form-control bpa-front-form-control--date-picker" :clearable="false" :placeholder="customer_form_fields_data.placeholder" :type="customer_form_fields_data.field_options.enable_timepicker ? 'datetime' : 'date'"></el-date-picker>
																</div>
																<div v-if='customer_form_fields_data.field_type == "Textarea"'>
																	<el-input v-model="edit_profile_form_data[customer_form_fields_data['v_model_value']]" class="bpa-front-form-control" :placeholder="customer_form_fields_data.placeholder" type="textarea" :rows="3"></el-input>
																</div>
																<div v-model="edit_profile_form_data[customer_form_fields_data['v_model_value']]" v-if='customer_form_fields_data.field_type == "File"' class="bpa-front-form-field--file-upload">
																	<label for="bpa-file-upload-two" class="bpa-front-form-control--file-upload">
																		<span class="bpa-fu__placeholder">{{ fsinner_data.placeholder }} </span>
																		<span class="bpa-fu__btn">{{ fsinner_data.field_options.browse_button_label}} </span>
																	</label>
																	<el-input id="bpa-file-upload-two" type="file" :placeholder="customer_form_fields_data.placeholder"></el-input>
																</div>
															</el-form-item>
														</div>													
														<div class="bpa-bdf--single-col-item">
															<el-form-item prop="customer_phone" ref="customer_phone" v-if="customer_form_fields_data.field_name == 'phone_number' && customer_form_fields_data.is_hide != '1'">
																<template #label>
																	<span class="bpa-front-form-label">{{ customer_form_fields_data.label }}</span>		
																</template> 
																	<vue-tel-input v-model="edit_profile_form_data['customer_phone']" class="bpa-front-form-control --bpa-country-dropdown" @country-changed="bookingpress_phone_country_change_func($event)" v-bind="bookingpress_tel_input_props" ref="bpa_tel_input_field">
																	<template v-slot:arrow-icon>
																		<span class="material-icons-round">keyboard_arrow_down</span>
																	</template>
																</vue-tel-input>
															</el-form-item>
														</div>
													</template>
													<template v-else>
														<div class="bpa-bdf--multi-col-item" v-if="fsinner_data.is_blank !== 'true'" v-for="(fsinner_data, ifskey) in customer_form_fields_data.field_options.inner_fields">
															<el-form-item :prop="fsinner_data.v_model_value" :ref="fsinner_data.v_model_value">
																<template #label>
																	<span class="bpa-front-form-label">{{ fsinner_data.label }}</span>
																</template>
																<el-input v-model="edit_profile_form_data[fsinner_data.v_model_value]" class="bpa-front-form-control" :type="(fsinner_data.field_type == 'Email' ? 'email' : 'text')" v-if='(fsinner_data.field_type == "Text" || fsinner_data.field_type == "Email" || fsinner_data.field_type == "Phone")' :placeholder="fsinner_data.placeholder"></el-input>
																<div v-if='fsinner_data.field_type == "Checkbox"'>
																	<el-checkbox v-model="edit_profile_form_data[fsinner_data.meta_key+'_'+ifkeys]" class="bpa-front-form-control--checkbox" v-for="(chk_data, ifkeys) in fsinner_data.field_values" :label="chk_data.label" :key="chk_data.value">{{chk_data.label}}</el-checkbox>
																</div>
																<div v-if='fsinner_data.field_type == "Radio"'>
																	<el-radio v-model="edit_profile_form_data[fsinner_data.v_model_value]" class="bpa-front-form-control--radio" v-for="(chk_data, ifkeys) in fsinner_data.field_values" :label="chk_data.label" :key="chk_data.value">{{chk_data.label}}</el-radio>
																</div>
																<div v-if='fsinner_data.field_type == "Dropdown"'>
																	<el-select v-model="edit_profile_form_data[fsinner_data.v_model_value]" class="bpa-front-form-control" :placeholder="fsinner_data.placeholder">
																		<el-option v-for="sel_data in fsinner_data.field_values" :key="sel_data.value" :label="sel_data.label" :value="sel_data.value" ></el-option>
																	</el-select>
																</div>
																<div v-if='fsinner_data.field_type == "Date"'>
																	<el-date-picker v-model="edit_profile_form_data[fsinner_data.v_model_value]" class="bpa-front-form-control bpa-front-form-control--date-picker" :placeholder="fsinner_data.placeholder" :type="fsinner_data.field_options.enable_timepicker ? 'datetime' : 'date'"></el-date-picker>
																</div>
																<div v-if='fsinner_data.field_type == "Textarea"'>
																	<el-input v-model="edit_profile_form_data[fsinner_data.v_model_value]" class="bpa-front-form-control" :placeholder="fsinner_data.placeholder" type="textarea" :rows="3"></el-input>
																</div>																	
																<div v-model="edit_profile_form_data[fsinner_data.v_model_value]" v-if='fsinner_data.field_type == "File"' class="bpa-front-form-field--file-upload">
																	<label for="bpa-file-upload" class="bpa-front-form-control--file-upload">
																		<span class="bpa-fu__placeholder">{{ fsinner_data.placeholder }}</span>
																		<span class="bpa-fu__btn">{{ fsinner_data.field_options.browse_button_label}}</span>
																	</label>
																	<el-input id="bpa-file-upload" type="file" :placeholder="fsinner_data.placeholder"></el-input>
																</div>
															</el-form-item>
														</div>
														<div class="bpa-bdf--multi-col-item--is-empty" v-else></div>
													</template>
												</el-col>  
											</el-row>			
											<el-row>
												<el-button class="bpa-front-btn bpa-front-btn--primary" :class="(is_profile_display_save_loader == '1') ? 'bpa-front-btn--is-loader' : ''" @click="bookingpress_edit_profile('edit_profile_form_data')" :disabled="is_profile_disabled" >					
													<span class="bpa-btn__label"><?php esc_html_e( 'Submit', 'bookingpress-appointment-booking' ); ?></span>
													<div class="bpa-front-btn--loader__circles">				    
														<div></div>
														<div></div>
														<div></div>
													</div>
												</el-button>
											</el-row>	
										</div>
									</el-form>
								</el-col>
							</el-row>
						</div>
					</el-col>
				</el-row>
			</div>
			<div class="bpa-front-dc--footer" :class="bookingpress_footer_dynamic_class">
				<el-row>
					<el-col>
						<div class="bpa-front-tabs--foot">						
						</div>
					</el-col>
				</el-row>
			</div>
		</div>
	</div>	
</el-main>
