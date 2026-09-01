<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">
    <el-row type="flex" class="bpa-mlc-head-wrap">
        <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
            <h1 class="bpa-page-heading"><?php esc_html_e('Custom Fields', 'bookingpress-appointment-booking'); ?></h1>
        </el-col>
        <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
            <div class="bpa-hw-right-btn-group">
                <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bpa_save_customize_settings('form_fields')" :disabled="is_disabled" >                    
                  <span class="bpa-btn__label"><?php esc_html_e('Save Changes', 'bookingpress-appointment-booking'); ?></span>
                  <div class="bpa-btn--loader__circles">                    
                      <div></div>
                      <div></div>
                      <div></div>
                  </div>
                </el-button>
                <?php do_action('bookingpress_add_customize_custom_fields_top_button'); ?>
            </div>
        </el-col>
    </el-row>
    <div class="bpa-back-loader-container" id="bpa-page-loading-loader">
        <div class="bpa-back-loader"></div>
    </div>
    <el-container class="bpa-customize-main-container" id="bpa-main-container">
        <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
            <div class="bpa-back-loader"></div>
        </div>
        <div class="bpa-customize-body-wrapper">                                                             
            <el-row type="flex">
                <el-col :xs="6" :sm="6" :md="6" :lg="4" :xl="4" id="bpa-fields-box">
                    <div class="bpa-customize-step-side-panel bpa-fs-controls-sidebar">
                        <div class="bpa-cmc--tab-menu">                                
                            <div class="bpa-cms-tm__body" v-if="customer_fields.length > 0">
                                <el-radio-group v-model="bookingpress_custom_field_active_tab">
                                    <el-radio-button label="form"><?php esc_html_e('Form', 'bookingpress-appointment-booking'); ?></el-radio-button>
                                    <el-radio-button label="customer"><?php esc_html_e('Customer', 'bookingpress-appointment-booking'); ?></el-radio-button>
                                </el-radio-group>
                            </div>
                        </div>
                        <div class="bpa-cs__items" id="bpa-input-fields" v-if="bookingpress_custom_field_active_tab == 'form'">
                            <div class="bpa-cs__item-sec-head bpa-restricted">
                                <h5><?php esc_html_e( 'Form Elements', 'bookingpress-appointment-booking' ); ?></h5>
                            </div>
                            <div data-type="single_line" onClick="BPASortable.add_item_to_form('single_line')" class="bpa-cs__item">
                                <span class="material-icons-round">short_text</span>
                                <p><?php esc_html_e( 'Text Field', 'bookingpress-appointment-booking' ); ?></p>
                            </div>
                            <div data-type="textarea" onClick="BPASortable.add_item_to_form('textarea')" class="bpa-cs__item">
                                <span class="material-icons-round">notes</span>
                                <p><?php esc_html_e( 'Textarea', 'bookingpress-appointment-booking' ); ?></p>
                            </div>
                            <div data-type="checkbox" onClick="BPASortable.add_item_to_form('checkbox')" class="bpa-cs__item">
                                <span class="material-icons-round">check_box</span>
                                <p><?php esc_html_e( 'Checkbox', 'bookingpress-appointment-booking' ); ?></p>
                            </div>
                            <div data-type="radio" onClick="BPASortable.add_item_to_form('radio')" class="bpa-cs__item">
                                <span class="material-icons-round">radio_button_checked</span>
                                <p><?php esc_html_e( 'Radio', 'bookingpress-appointment-booking' ); ?></p>
                            </div>
                            <div data-type="dropdown" onClick="BPASortable.add_item_to_form('dropdown')" class="bpa-cs__item">
                                <span class="material-icons-round">arrow_drop_down_circle</span>
                                <p><?php esc_html_e( 'Dropdown', 'bookingpress-appointment-booking' ); ?></p>
                            </div>
                            <div data-type="datepicker" onClick="BPASortable.add_item_to_form('datepicker')" class="bpa-cs__item">
                                <span class="material-icons-round">insert_invitation</span>
                                <p><?php esc_html_e( 'DatePicker', 'bookingpress-appointment-booking' ); ?></p>
                            </div>
                            <div data-type="file_upload" onClick="BPASortable.add_item_to_form('file_upload')" class="bpa-cs__item">
                                <span class="material-icons-round">upload</span>
                                <p><?php esc_html_e( 'File Upload', 'bookingpress-appointment-booking' ); ?></p>
                            </div>                            
                            <div data-type="password" onClick="BPASortable.add_item_to_form('password')" class="bpa-cs__item" :class="bpa_password_field_exists">
                                <span class="material-icons-round">lock</span>
                                <p><?php esc_html_e( 'Password', 'bookingpress-appointment-booking' ); ?></p>
                            </div>
                            <div data-type="repeater" onClick="BPASortable.add_item_to_form('repeater')" class="bpa-cs__item bpa-repeater-field" v-if="'undefined' != typeof is_bring_anyone_activated && 1 == is_bring_anyone_activated">
                                <span class="material-icons-round">repeat</span>
                                <p><?php esc_html_e( 'Repeater', 'bookingpress-appointment-booking' ); ?></p>
                            </div>
                        </div>
                        <div class="bpa-cs__items" id="bpa-column-fields" v-if="bookingpress_custom_field_active_tab == 'form'">
                            <div class="bpa-cs__item-sec-head bpa-restricted">
                                <h5><?php esc_html_e( 'Columns', 'bookingpress-appointment-booking' ); ?></h5>
                            </div>
                            <div data-type="column"  @click="BPASortable.add_item_to_form('column', '2col')" data-value="2col" class="bpa-cs__item">
                                <span class="bpa-cs__item-col-icon">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.19304 13.5042H11.5794C12.017 13.5042 12.3749 13.1462 12.3749 12.7087V3.95856C12.3749 3.52105 12.017 3.16309 11.5794 3.16309H9.19304C8.75553 3.16309 8.39757 3.52105 8.39757 3.95856V12.7087C8.39757 13.1462 8.75553 13.5042 9.19304 13.5042ZM4.42023 13.5042H6.80663C7.24414 13.5042 7.6021 13.1462 7.6021 12.7087V3.95856C7.6021 3.52105 7.24414 3.16309 6.80663 3.16309H4.42023C3.98272 3.16309 3.62476 3.52105 3.62476 3.95856V12.7087C3.62476 13.1462 3.98272 13.5042 4.42023 13.5042Z" fill="#727E95"/>
                                    </svg>
                                </span>
                                <p><?php esc_html_e( '2 Columns', 'bookingpress-appointment-booking' ); ?></p>
                            </div>
                            <div data-type="column"  @click="BPASortable.add_item_to_form('column', '3col')" data-value="3col" class="bpa-cs__item">
                                <span class="bpa-cs__item-col-icon">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.85276 13.3036H9.14691C9.5675 13.3036 9.91163 12.9595 9.91163 12.5389V4.12702C9.91163 3.70643 9.5675 3.3623 9.14691 3.3623H6.85276C6.43217 3.3623 6.08805 3.70643 6.08805 4.12702V12.5389C6.08805 12.9595 6.43217 13.3036 6.85276 13.3036ZM2.26447 13.3036H4.55862C4.97921 13.3036 5.32333 12.9595 5.32333 12.5389V4.12702C5.32333 3.70643 4.97921 3.3623 4.55862 3.3623H2.26447C1.84388 3.3623 1.49976 3.70643 1.49976 4.12702V12.5389C1.49976 12.9595 1.84388 13.3036 2.26447 13.3036ZM10.6763 4.12702V12.5389C10.6763 12.9595 11.0205 13.3036 11.4411 13.3036H13.7352C14.1558 13.3036 14.4999 12.9595 14.4999 12.5389V4.12702C14.4999 3.70643 14.1558 3.3623 13.7352 3.3623H11.4411C11.0205 3.3623 10.6763 3.70643 10.6763 4.12702Z" fill="#727E95"/>
                                    </svg>
                                </span>
                                <p><?php esc_html_e( '3 Columns', 'bookingpress-appointment-booking' ); ?></p>
                            </div>
                            <div data-type="column"  @click="BPASortable.add_item_to_form('column', '4col')" data-value="4col" class="bpa-cs__item">
                                <span class="bpa-cs__item-col-icon">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.0625 12.35V4.31717C1.0625 3.91553 1.32539 3.58691 1.6467 3.58691H3.39931C3.72063 3.58691 3.98352 3.91553 3.98352 4.31717V12.35C3.98352 12.7516 3.72063 13.0802 3.39931 13.0802H1.6467C1.32539 13.0802 1.0625 12.7516 1.0625 12.35Z" fill="#727E95"/>
                                        <path d="M4.71362 12.35V4.31717C4.71362 3.91553 4.97651 3.58691 5.29783 3.58691H7.05044C7.37175 3.58691 7.63464 3.91553 7.63464 4.31717V12.35C7.63464 12.7516 7.37175 13.0802 7.05044 13.0802H5.29783C4.97651 13.0802 4.71362 12.7516 4.71362 12.35Z" fill="#727E95"/>
                                        <path d="M8.36523 12.35V4.31717C8.36523 3.91553 8.62813 3.58691 8.94944 3.58691H10.702C11.0234 3.58691 11.2863 3.91553 11.2863 4.31717V12.35C11.2863 12.7516 11.0234 13.0802 10.702 13.0802H8.94944C8.62813 13.0802 8.36523 12.7516 8.36523 12.35Z" fill="#727E95"/>
                                        <path d="M12.0164 12.35V4.31717C12.0164 3.91553 12.2792 3.58691 12.6006 3.58691H14.3532C14.6745 3.58691 14.9374 3.91553 14.9374 4.31717V12.35C14.9374 12.7516 14.6745 13.0802 14.3532 13.0802H12.6006C12.2792 13.0802 12.0164 12.7516 12.0164 12.35Z" fill="#727E95"/>
                                    </svg>
                                </span>
                                <p><?php esc_html_e( '4 Columns', 'bookingpress-appointment-booking' ); ?></p>
                            </div>										
                        </div>
                        <div class="bpa-cs__items" id="bpa-customer-fields" v-if="bookingpress_custom_field_active_tab == 'customer' && customer_fields.length > 0">
                            <div :data-type="customer_fields_data.bookingpress_field_type" :data-customer-field-meta="customer_fields_data.bookingpress_field_meta_key" :data-customer-field-id="fskey" class="bpa-cs__item" onClick="BPASortable.bpa_add_customer_field_to_list(this)" :data-customer-field-type="customer_fields_data.bookingpress_field_type" :class="(customer_fields_data.is_droppable == false ? 'bpa-restricted' : '')" v-for="(customer_fields_data, fskey) in customer_fields">
                                <p>{{customer_fields_data.bookingpress_field_label}}</p>
                            </div>
                        </div>
                    </div>
                </el-col>
                <el-col :xs="18" :sm="18" :md="18" :lg="20" :xl="20">
                    <div class="bpa-customize-field-settings-body-container" id="bpa-custom-fields-settings">
                        <el-row id="bpa-draggable-container">
                            <el-col class="bpa-field-container bpa-field-wrapper-container" :class="[( field_settings_data.field_options.layout != '1col' ? 'bpa-field-col-parent-container' : 'bpa-field-outer-container' ), ( field_settings_data.field_type == 'Repeater' ? 'bpa-field-col-repeater-parent-container  bpa-cfs-item-card' : '' )]" :data-type="field_settings_data.field_type" :data-field-id="field_settings_data.id" :data-is-customer-field="field_settings_data.field_options.is_customer_field" :data-metakey="field_settings_data.meta_key" :data-id="fskey" :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="field_settings_data.field_options !== undefined" v-for="(field_settings_data, fskey) in field_settings_fields" :key="field_settings_data.id">
                                <template v-if="'Repeater' == field_settings_data.field_type" class="bpa-field-repeater-field-container">
                                    <el-col class="bpa-inner-field-container bpa-repeater-field-wrapper-container">
                                        <div class="bpa-cfs-ic__body">
                                            <div class="bpa-cfs-ic--head">
                                                <div class="bpa-cfs-ic--head__type-label">
                                                    <span class="material-icons-round">drag_indicator</span>
                                                    <p>{{ field_settings_data.field_type }}</p>
                                                </div>
                                                <div class="bpa-cfs-ic--head__field-controls">
                                                    <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                        <div slot="content">
                                                            <span><?php esc_html_e( 'Field Settings', 'bookingpress-appointment-booking' ); ?></span>
                                                        </div>
                                                        <el-popover placement="bottom-end" v-model="field_settings_data.is_edit">
                                                            <el-container class="bpa-field-settings-edit-container">
                                                                <div class="bpa-fs-item-settings-form-control-item">
                                                                    <label class="bpa-form-label"><?php esc_html_e( 'Label', 'bookingpress-appointment-booking' ); ?></label>
                                                                    <el-input class="bpa-form-control" v-model="field_settings_data.label"></el-input>
                                                                </div>      
                                                                <div class="bpa-customize--edit-label-popover--actions">
                                                                    <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="saveFieldSettings(fskey)"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                </div>
                                                            </el-container>
                                                            <el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
                                                                <span class="material-icons-round">settings</span>
                                                            </el-button>
                                                        </el-popover>
                                                    </el-tooltip>
                                                    <div class="bpa-cfs-ic--head__fc-actions" v-if="field_settings_data.is_default != 1 && check_repeater_inside_default_fields(field_settings_data)">
                                                        <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                            <div slot="content">
                                                                <span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
                                                            </div>
                                                            <el-button @click="deleteField(fskey)" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                                <span class="material-icons-round">delete</span>
                                                            </el-button>
                                                        </el-tooltip>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bpa-cfs-ic--body">
                                                <div class="bpa-cfs-ic--body__field-preview bpa-cfs-ic--body__repeater-field-preview" :class="( ('undefined' == typeof field_settings_data.field_options.inner_fields || 1 > field_settings_data.field_options.inner_fields.length) ? 'bpa-cfs-ic--body__repeater-field-wrapper' : '' )" :data-id="fskey" data-type="repeater" :data-repeater-id="field_settings_data.id">
                                                    <el-col class="bpa-inner-field-container bpa-field-wrapper-container" :class="[( fsrinner_data.field_options.layout == '2col' ? 'bpa-field-col-parent-container bpa-field-two-col-parent-container' : '' ), ( fsrinner_data.field_options.layout == '3col' ? 'bpa-field-col-parent-container bpa-field-three-col-parent-container' : '' ),( fsrinner_data.field_options.layout == '4col' ? 'bpa-field-col-parent-container bpa-field-four-col-parent-container' : '' )]" :xs="24" :sm="24" :md="24" :lg="24" :xl="24" :data-fkey="ifrskey" :data-fposition="fsrinner_data.field_position" :key="fsrinner_data.field_position" v-for="(fsrinner_data, ifrskey) in field_settings_data.field_options.inner_fields" :data-field-id="fsrinner_data.id">
                                                        <template v-if="fsrinner_data.field_options.layout == '1col'">
                                                            <div class="bpa-cfs-item-card">
                                                                <div class="bpa-cfs-ic__body">
                                                                    <div class="bpa-cfs-ic--head">
                                                                        <div class="bpa-cfs-ic--head__type-label">
                                                                            <span class="material-icons-round">drag_indicator</span>
                                                                            <p>{{ fsrinner_data.field_type }}</p>
                                                                        </div>
                                                                        <div class="bpa-cfs-ic--head__field-controls">
                                                                            <div class="bpa-cfs-ic--head__fc-swtich">
                                                                                <el-switch v-model="fsrinner_data.is_required" class="bpa-swtich-control" :disabled="fsrinner_data.field_name == 'username' || fsrinner_data.field_name == 'terms_and_conditions' || fsrinner_data.field_name == 'Password'  ? true :false"></el-switch>
                                                                                <label>Required</label>
                                                                            </div>                                                                            
                                                                            <div class="bpa-cfs-ic--head__fc-actions" v-if="(fsrinner_data.field_type == 'Checkbox' || fsrinner_data.field_type == 'Radio' || fsrinner_data.field_type == 'Dropdown' ) && 'false' == fsrinner_data.field_options.is_customer_field" >
                                                                                <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                                                    <div slot="content">
                                                                                        <span><?php esc_html_e( 'Field Options', 'bookingpress-appointment-booking' ); ?></span>
                                                                                    </div>
                                                                                    <el-popover width="550" placement="bottom-end" v-model="fsrinner_data.is_edit_values">
                                                                                        <el-container class="bpa-field-settings-edit-container bpa-field-values-edit-container">
                                                                                            <el-row type="flex" class="bpa-field-values-row-with-border">
                                                                                                <el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16" class="bpa-cs__heading">
                                                                                                    <h3><?php esc_html_e( 'Manage Options', 'bookingpress-appointment-booking' ); ?></h3>
                                                                                                </el-col>
                                                                                                <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8" class="bpa-cs__heading">
                                                                                                    <el-button style="top:50%;transform:translateY(-50%);" @click="bpaDisplayPresetValues(ifrskey,fskey)" class="bpa-btn bpa-btn__small bpa-btn--full-width"><?php esc_html_e( 'Preset Values', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                                                <el-col>
                                                                                            </el-row>
                                                                                            <el-row type="flex" class="bpa-field-values-row-with-border" v-if="fsrinner_data.enable_preset_fields == true" >
                                                                                                <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-cs__heading">
                                                                                                    <el-select placeholder="<?php esc_html_e( 'Select Preset Values', 'bookingpress-appointment-booking' ); ?>" class="bpa-form-control" v-model="fsrinner_data.preset_field_choice" popper-class="popover_select_control">
                                                                                                        <el-option v-for="item in bookingpress_preset_fields" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                                                                                    </el-select>
                                                                                                </el-col>&nbsp;
                                                                                                <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-cs__heading bpa-field-preset-values-btn-wrapper">
                                                                                                    <el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" :disabled="preset_btn_disable" @click="applyPresetFields(ifrskey,fskey)" :class="(is_display_preset_value_loader == '1') ? 'bpa-btn--is-loader' : ''">
                                                                                                    <span class="bpa-btn__label"><?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?></span>
                                                                                                        <div class="bpa-btn--loader__circles">				    
                                                                                                            <div></div>
                                                                                                            <div></div>
                                                                                                            <div></div>
                                                                                                        </div>
                                                                                                    </el-button>
                                                                                                    <el-button class="bpa-btn bpa-btn__medium bpa-field-values-cancel-preset-btn" @click="bpaHidePresetValues(ifrskey,fskey)"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                                                </el-col>
                                                                                            </el-row>
                                                                                            <el-row type="flex">
                                                                                                <el-col :xs="22" :sm="22" :md="22" :lg="22" :xl="22" class="bpa-cs__heading">
                                                                                                    <label class="bpa-form-label"><?php esc_html_e( 'Use Separate Value', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                </el-col>
                                                                                                <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading">
                                                                                                    <el-switch class="bpa-swtich-control" v-model="fsrinner_data.field_options.separate_value"></el-switch>
                                                                                                </el-col>
                                                                                            </el-row>
                                                                                            <el-row type="flex" class="bpa-field-values-row-no-top-padding bpa-field-values-row-with-border bpa-field-values-options-wrapper">
                                                                                                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                                                    <el-row type="flex" class="bpa-cs__heading bpa-cs__values_heading">
                                                                                                        <el-col class="bpa-cs__field_value_label_heading"><?php esc_html_e( 'Option Label', 'bookingpress-appointment-booking' ); ?></el-col>
                                                                                                        <el-col v-if="fsrinner_data.field_options.separate_value" class="bpa-cs__field_value_label_heading"><?php esc_html_e( 'Option Value', 'bookingpress-appointment-booking' ); ?></el-col>
                                                                                                        <el-col class="bpa-cs__field_value_icon">
                                                                                                            <el-button class="bpa-btn bpa-btn--icon-without-box" @click="bpaAddfieldValue(ifrskey,fskey)">
                                                                                                                <span class="material-icons-round">add_circle</span>
                                                                                                            </el-button>
                                                                                                        </el-col>
                                                                                                    </el-row>
                                                                                                    <el-row type="flex" class="bpa-cs__heading bpa-cs__values_items" v-for="(items, i_key) in fsrinner_data.field_values" :key="i_key">
                                                                                                        <el-col class="bpa-cs__field_value_label">
                                                                                                            <el-input class="bpa-form-field-value-input" v-model="items.label"></el-input>
                                                                                                        </el-col>
                                                                                                        <el-col v-if="fsrinner_data.field_options.separate_value" class="bpa-cs__field_value_label">
                                                                                                            <el-input class="bpa-form-field-value-input" v-model="items.value"></el-input>
                                                                                                        </el-col>
                                                                                                        <el-col class="bpa-cs__field_value_icon">
                                                                                                            <el-button class="bpa-btn bpa-btn--icon-without-box" @click="bpaRemovefieldValue(i_key, ifrskey, fskey)">
                                                                                                                <span class="material-icons-round">remove_circle</span>
                                                                                                            </el-button>
                                                                                                        </el-col>
                                                                                                    </el-row>
                                                                                                </el-col>
                                                                                            </el-row>
                                                                                            <el-row class="bpa-field-values-row-no-top-padding bpa-cs-field-values-btn-wrapper">
                                                                                                <div class="bpa-customize--edit-label-popover--actions">
                                                                                                    <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="closeFieldValueBtn(ifrskey, fskey)"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
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
                                                                                    <el-popover placement="bottom-end" v-model="fsrinner_data.is_edit">
                                                                                        <el-container class="bpa-field-settings-edit-container">
                                                                                            <div class="bpa-fs-item-settings-form-control-item">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Label', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" @input="BPAforceUpdate()" :disabled="'false' != fsrinner_data.field_options.is_customer_field" v-model="fsrinner_data.label"></el-input>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsrinner_data.field_type == 'Phone'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e('Set Custom Placeholder', 'bookingpress-appointment-booking'); ?></label>
                                                                                                <el-switch v-model="fsrinner_data.field_options.set_custom_placeholder" class="bpa-swtich-control"></el-switch>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsrinner_data.field_type != 'Checkbox' && fsrinner_data.field_type != 'Radio' && ( fsrinner_data.field_type != 'Phone' || (fsrinner_data.field_type == 'Phone' && fsrinner_data.field_options.set_custom_placeholder == true) ) && fsrinner_data.field_type != 'terms_and_conditions' ">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Placeholder', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control"  @input="BPAforceUpdate()" :disabled="'false' != fsrinner_data.field_options.is_customer_field" v-model="fsrinner_data.placeholder"></el-input>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsrinner_data.field_type == 'File'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Browse Button Label', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" v-model="fsrinner_data.field_options.browse_button_label"></el-input>
                                                                                            </div>  
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsrinner_data.field_type == 'Text' || fsrinner_data.field_type == 'Textarea'">
                                                                                                <el-row type="flex">
                                                                                                    <el-col :xs="11" :sm="11" :md="11" :lg="11" :xl="11" class="bpa-cs__heading">
                                                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Minimum', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                        <el-input-number type ="number" controls-position="right" :min="0" @input="BPAforceUpdate()"   class="bpa-form-control"  v-model="fsrinner_data.field_options.minimum" step-strictly></el-input-number>&nbsp;&nbsp;
                                                                                                    </el-col>
                                                                                                    <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading"></el-col>
                                                                                                    <el-col :xs="11" :sm="11" :md="11" :lg="11" :xl="11" class="bpa-cs__heading">
                                                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Maximum', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                        <el-input-number controls-position="right" :min="0" class="bpa-form-control"  @input="BPAforceUpdate()" v-model="fsrinner_data.field_options.maximum" step-strictly></el-input-number>
                                                                                                    </el-col>
                                                                                                </el-row>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Error Message', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" v-model="fsrinner_data.error_message"></el-input>
                                                                                            </div>
                                                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="fsrinner_data.field_type != 'Password'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Meta Key', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" :disabled="'false' != fsrinner_data.field_options.is_customer_field" v-model="fsrinner_data.meta_key"></el-input>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsrinner_data.field_type == 'Date'">
                                                                                                <el-row type="flex">
                                                                                                    <el-col :xs="20" :sm="20" :md="20" :lg="20" :xl="20" class="bpa-cs__heading">
                                                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Enable Time Picker', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                    </el-col>
                                                                                                    <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading">
                                                                                                        <el-switch :disabled="'false' != fsrinner_data.field_options.is_customer_field" class="bpa-swtich-control" v-model="fsrinner_data.field_options.enable_timepicker"></el-switch>
                                                                                                    </el-col>
                                                                                                </el-row>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsrinner_data.field_type == 'File'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'File Size Limit', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" v-model="fsrinner_data.field_options.max_file_size">
                                                                                                    <template slot="append">MB</template>
                                                                                                </el-input>
                                                                                                <el-description class="bpa-form-control">
                                                                                                    <el-description-item style="color:#ff0000"><?php echo esc_html__( 'PHP Maximum Upload Size', 'bookingpress-appointment-booking' ) . ' ' . ini_get( 'upload_max_filesize' ); // phpcs:ignore ?></el-description-item>
                                                                                                </el-description>
                                                                                            </div> 
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsrinner_data.field_type == 'File'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Allowed File Extension', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" v-model="fsrinner_data.field_options.allowed_file_ext"></el-input>
                                                                                                <el-description class="bpa-form-control">
                                                                                                    <el-description-item><?php esc_html_e( 'You should place comma separated list of file extensions.', 'bookingpress-appointment-booking' ); ?></el-description-item><br/>
                                                                                                    <el-description-item><?php esc_html_e( 'Leave blank for allow all file types.', 'bookingpress-appointment-booking' ); ?></el-description-item>
                                                                                                </el-description>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsrinner_data.field_type == 'File'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Invalid Field message', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" v-model="fsrinner_data.field_options.invalid_field_message"></el-input>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsrinner_data.field_type == 'File'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Attach file with email', 'bookingpress-appointment-booking'); ?></label>
                                                                                                <el-switch v-model="fsrinner_data.field_options.attach_with_email" class="bpa-swtich-control"></el-switch>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Visibility', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-radio @change="changeFieldVisibility(ifrskey, 'always', fskey)" class="bpa-form-label bpa-custom-radio--is-label" v-model="fsrinner_data.field_options.visibility" label="always"><?php esc_html_e( 'Always', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                                                <el-radio v-if="fsrinner_data.field_name != 'terms_and_conditions' && fsrinner_data.field_name != 'username' && fsrinner_data.field_name != 'Password'" @change="changeFieldVisibility(ifrskey, 'services', fskey)" class="bpa-form-label bpa-custom-radio--is-label" v-model="fsrinner_data.field_options.visibility" label="services"><?php esc_html_e( 'Show conditionally on specific service', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                                                <div v-if="fsrinner_data.field_options.visibility == 'services'">
                                                                                                    <label class="bpa-form-label"><?php esc_html_e( 'Select Services', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                    <el-select class="bpa-form-control" popper-class="popover_select_control" collapse-tags multiple v-model="fsrinner_data.field_options.selected_services">
                                                                                                        <el-option v-for="item in bookingpress_service_data" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                                                                                    </el-select>
                                                                                                    <label v-if="bookingpress_service_error == true" class="bpa-form-label bpa-form-label-validation-message"><?php esc_html_e( 'Please select at-least one service', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                    <label v-else>&nbsp;</label>
                                                                                                </div>
                                                                                                <el-radio @change="changeFieldVisibility(ifrskey, 'hidden', fskey)" class="bpa-form-label bpa-custom-radio--is-label" v-model="fsrinner_data.field_options.visibility" label="hidden"><?php esc_html_e( 'Hidden', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item">
                                                                                                    <label class="bpa-form-label"><?php esc_html_e( 'CSS Class', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                    <el-input class="bpa-form-control" v-model="fsrinner_data.css_class"></el-input>
                                                                                                </div>
                                                                                                <?php do_action('bookingpress_custom_field_setting_after'); ?>
                                                                                            <div class="bpa-customize--edit-label-popover--actions">
                                                                                                <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="saveFieldRepeterSettings(ifrskey, fskey)"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                                            </div>
                                                                                        </el-container>
                                                                                        <el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
                                                                                            <span class="material-icons-round">settings</span>
                                                                                        </el-button>
                                                                                    </el-popover>
                                                                                </el-tooltip>
                                                                            </div>
                                                                            <div class="bpa-cfs-ic--head__fc-actions" v-if="fsrinner_data.is_default != 1">
                                                                                <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                                                    <div slot="content">
                                                                                        <span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
                                                                                    </div>
                                                                                    <el-button @click="deleteRepeatInnerField(ifrskey,fskey)" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                                                        <span class="material-icons-round">delete</span>
                                                                                    </el-button>
                                                                                </el-tooltip>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="bpa-cfs-ic--body">
                                                                        <div class="bpa-cfs-ic--body__field-preview">
                                                                            <span class="bpa-form-label" v-text="fsrinner_data.label" v-if="fsrinner_data.field_type != 'terms_and_conditions'"></span>
                                                                            <el-input class="bpa-form-control" v-if='(fsrinner_data.field_type == "Text" || fsrinner_data.field_type == "Email" || fsrinner_data.field_type == "Phone")' :placeholder="fsrinner_data.placeholder"></el-input>

                                                                            <el-input class="bpa-form-control" v-if='fsrinner_data.field_type == "Textarea"' :placeholder="fsrinner_data.placeholder" type="textarea" :rows="3"></el-input>

                                                                            <template v-if='fsrinner_data.field_type == "terms_and_conditions"'>
                                                                                <el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" :label="fsrinner_data.label" :key="">
                                                                                <div v-html="fsrinner_data.label"></div></el-checkbox>
                                                                            </template>
                                                                            <template v-if='fsrinner_data.field_type == "Checkbox"'>
                                                                                <el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" v-if="keys < 5" v-for="(chk_data, keys) in fsrinner_data.field_values" :label="chk_data.label" :key="chk_data.value"><div v-html="chk_data.label"></div></el-checkbox>
                                                                            </template>
                                                                            <template v-if='fsrinner_data.field_type == "Radio"'>
                                                                                <el-radio class="bpa-form-label bpa-custom-radio--is-label" v-if="keys < 5" v-for="(chk_data, keys) in fsrinner_data.field_values" :label="chk_data.label" :key="chk_data.value">{{chk_data.label}}</el-radio>
                                                                            </template>
                                                                            <template v-if='fsrinner_data.field_type == "Dropdown"'>
                                                                                <el-select class="bpa-form-control" :placeholder="fsrinner_data.placeholder">
                                                                                    <el-option v-for="sel_data in fsrinner_data.field_values" :key="sel_data.value" :label="sel_data.label" :value="sel_data.value" ></el-option>
                                                                                </el-select>
                                                                            </template>
                                                                            <el-date-picker class="bpa-form-control bpa-form-control--date-picker" prefix-icon="" v-if='fsrinner_data.field_type == "Date"' :placeholder="fsrinner_data.placeholder" :type="fsrinner_data.enable_timepicker ? 'datetime' : 'date'"></el-date-picker>                                                        
                                                                            <el-upload v-if='fsrinner_data.field_type == "File"' multiple="false" limit="1" auto-upload="false">
                                                                                <label for="bpa-file-upload-three" class="bpa-form-control--file-upload">
                                                                                    <span class="bpa-fu__placeholder" v-html="fsrinner_data.placeholder"></span>
                                                                                    <span class="bpa-fu__btn" v-html="fsrinner_data.field_options.browse_button_label"></span>
                                                                                </label> 
                                                                            </el-upload>


                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template v-else>
                                                            <el-col class="bpa-inner-field-container bpa-two-col-container bpa-field-wrapper-container bpa-field-col-inner-parent-container" :xs="12" :sm="12" :md="12" :lg="12" :xl="12" :data-fkey="ifskey" :data-field-id="fsinner_data.id" :data-is-customer-field="fsinner_data.field_options.is_customer_field" :data-metakey="fsinner_data.meta_key" :data-type="fsinner_data.field_type" v-for="(fsinner_data, ifskey) in fsrinner_data.field_options.inner_fields" :data-id="ifskey" :key="fsinner_data.id" v-if="fsrinner_data.field_options !== undefined && fsrinner_data.field_options.inner_fields !== undefined" :class="( (fsinner_data.is_blank == true || fsinner_data.is_blank == 'true') ? 'inner-field-blank-container' : '')">
                                                            <div :data-ifskey="ifskey" :data-ifslabel="fsinner_data.label" :data-ifid="fsinner_data.id" v-if="( fsinner_data.is_blank != true && fsinner_data.is_blank != 'true' )" class="bpa-cfs-item-card">
                                                                <div class="bpa-cfs-ic__body" :data-inkey="ifskey" :data-ifid="fsinner_data.id">
                                                                    <div class="bpa-cfs-ic--head">
                                                                        <div class="bpa-cfs-ic--head__type-label">
                                                                            <span class="material-icons-round">drag_indicator</span>
                                                                            <p>{{ fsinner_data.field_type }}</p>
                                                                        </div>
                                                                        <div class="bpa-cfs-ic--head__field-controls">
                                                                            <div class="bpa-cfs-ic--head__fc-swtich">                                                                                
                                                                                <el-switch v-model="fsinner_data.is_required" class="bpa-swtich-control" :disabled="fsinner_data.field_name == 'username' || fsinner_data.field_name == 'terms_and_conditions' || fsinner_data.field_name == 'Password' ? true :false"></el-switch>
                                                                                <label>Required</label>
                                                                            </div>
                                                                            <div class="bpa-cfs-ic--head__fc-actions" v-if="(fsinner_data.field_type == 'Checkbox' || fsinner_data.field_type == 'Radio' || fsinner_data.field_type == 'Dropdown' ) && 'false' == fsinner_data.field_options.is_customer_field">
                                                                                <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                                                    <div slot="content">
                                                                                        <span><?php esc_html_e( 'Field Options', 'bookingpress-appointment-booking' ); ?></span>
                                                                                    </div>
                                                                                    <el-popover width="550" placement="bottom-end" v-model="fsinner_data.is_edit_values">
                                                                                        <el-container class="bpa-field-settings-edit-container bpa-field-values-edit-container">
                                                                                            <el-row type="flex" class="bpa-field-values-row-with-border">
                                                                                                <el-col :xs="18" :sm="18" :md="18" :lg="18" :xl="18" class="bpa-cs__heading">
                                                                                                    <h3><?php esc_html_e( 'Manage Options', 'bookingpress-appointment-booking' ); ?></h3>
                                                                                                </el-col>
                                                                                                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6" class="bpa-cs__heading">
                                                                                                    <el-button style="top:50%;transform:translateY(-50%);" @click="bpaDisplayPresetValues(ifskey, fskey, ifrskey)" class="bpa-btn bpa-btn__small bpa-btn--full-width"><?php esc_html_e( 'Preset Values', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                                                <el-col>
                                                                                            </el-row>
                                                                                            <el-row type="flex" class="bpa-field-values-row-with-border" v-if="fsinner_data.enable_preset_fields" >
                                                                                                <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-cs__heading">
                                                                                                    <el-select placeholder="<?php esc_html_e( 'Select Preset Values', 'bookingpress-appointment-booking' ); ?>" class="bpa-form-control" v-model="fsinner_data.preset_field_choice" popper-class="popover_select_control">
                                                                                                        <el-option v-for="item in bookingpress_preset_fields" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                                                                                    </el-select>
                                                                                                </el-col>&nbsp;
                                                                                                <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-cs__heading bpa-field-preset-values-btn-wrapper">
                                                                                                    <el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" :disabled="preset_btn_disable" @click="applyPresetFields(ifskey, fskey, ifrskey)" :class="(is_display_preset_value_loader == '1') ? 'bpa-btn--is-loader' : ''">
                                                                                                    <span class="bpa-btn__label"><?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?></span>
                                                                                                        <div class="bpa-btn--loader__circles">				    
                                                                                                            <div></div>
                                                                                                            <div></div>
                                                                                                            <div></div>
                                                                                                        </div>
                                                                                                    </el-button>
                                                                                                    <el-button class="bpa-btn bpa-btn__medium bpa-field-values-cancel-preset-btn" @click="bpaHidePresetValues(ifskey, fskey,ifrskey)"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                                                </el-col>
                                                                                            </el-row>
                                                                                            <el-row type="flex">
                                                                                                <el-col :xs="22" :sm="22" :md="22" :lg="22" :xl="22" class="bpa-cs__heading">
                                                                                                    <label class="bpa-form-label"><?php esc_html_e( 'Use Separate Value', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                </el-col>
                                                                                                <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading">
                                                                                                    <el-switch class="bpa-swtich-control" @input="BPAforceUpdate()" v-model="fsinner_data.field_options.separate_value"></el-switch>
                                                                                                </el-col>
                                                                                            </el-row>
                                                                                            <el-row type="flex" class="bpa-field-values-row-no-top-padding bpa-field-values-row-with-border bpa-field-values-options-wrapper">
                                                                                                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                                                    <el-row type="flex" class="bpa-cs__heading bpa-cs__values_heading">
                                                                                                        <el-col class="bpa-cs__field_value_label_heading"><?php esc_html_e( 'Option Label', 'bookingpress-appointment-booking' ); ?></el-col>
                                                                                                        <el-col v-if="fsinner_data.field_options.separate_value" class="bpa-cs__field_value_label_heading"><?php esc_html_e( 'Option Value', 'bookingpress-appointment-booking' ); ?></el-col>
                                                                                                        <el-col class="bpa-cs__field_value_icon">
                                                                                                            <el-button class="bpa-btn bpa-btn--icon-without-box" @click="bpaAddfieldValue(ifskey,fskey,ifrskey)">
                                                                                                                <span class="material-icons-round">add_circle</span>
                                                                                                            </el-button>
                                                                                                        </el-col>
                                                                                                    </el-row>
                                                                                                    <el-row type="flex" class="bpa-cs__heading bpa-cs__values_items" v-for="(items, i_key) in fsinner_data.field_values" :key="i_key">
                                                                                                        <el-col class="bpa-cs__field_value_label">
                                                                                                            <el-input class="bpa-form-field-value-input" @input="BPAforceUpdate()" v-model="items.label"></el-input>
                                                                                                        </el-col>
                                                                                                        <el-col v-if="fsinner_data.field_options.separate_value" class="bpa-cs__field_value_label">
                                                                                                            <el-input class="bpa-form-field-value-input" @input="BPAforceUpdate()" v-model="items.value"></el-input>
                                                                                                        </el-col>
                                                                                                        <el-col class="bpa-cs__field_value_icon">
                                                                                                            <el-button class="bpa-btn bpa-btn--icon-without-box" @click="bpaRemovefieldValue(i_key, ifskey, fskey,ifrskey)">
                                                                                                                <span class="material-icons-round">remove_circle</span>
                                                                                                            </el-button>
                                                                                                        </el-col>
                                                                                                    </el-row>
                                                                                                </el-col>
                                                                                            </el-row>
                                                                                            <el-row class="bpa-field-values-row-no-top-padding bpa-cs-field-values-btn-wrapper">
                                                                                                <div class="bpa-customize--edit-label-popover--actions">
                                                                                                    <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="closeFieldValueBtn(ifskey, fskey, ifrskey)"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                                                </div>
                                                                                            </el-row>
                                                                                        </el-container>
                                                                                        <el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
                                                                                            <span class="material-icons-round">rule</span>
                                                                                        </el-button>
                                                                                    </el-popover>
                                                                                </el-tooltip>   
                                                                            </div>
                                                                            <!-- <div class="bpa-cfs-ic--head__fc-actions">
                                                                                <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                                                    <div slot="content">
                                                                                        <span v-if="fsinner_data.is_required === true"><?php esc_html_e( 'Disable Required', 'bookingpress-appointment-booking' ); ?></span>
                                                                                        <span v-else><?php esc_html_e( 'Enable Required', 'bookingpress-appointment-booking' ); ?></span>
                                                                                    </div>
                                                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" :class="(fsinner_data.is_required === true) ? 'bpa-fs-required_fill_icon' : 'bpa-fs-required_icon'" type="text" @click="setInnerFieldRequired(ifskey, fsinner_data.id, fskey)" :disabled="fsinner_data.field_type == 'Email' ? true : false">
                                                                                        <span class="material-icons-round">emergency</span>
                                                                                    </el-button>
                                                                                </el-tooltip>
                                                                            </div> -->
                                                                            <div class="bpa-cfs-ic--head__fc-actions">
                                                                                <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                                                    <div slot="content">
                                                                                        <span><?php esc_html_e( 'Field Settings', 'bookingpress-appointment-booking' ); ?></span>
                                                                                    </div>                                                                                   
                                                                                    <el-popover placement="bottom-end" v-model="fsinner_data.is_edit">
                                                                                        <el-container class="bpa-field-settings-edit-container">
                                                                                            <div class="bpa-fs-item-settings-form-control-item">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Label', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" :disabled="'false' != fsinner_data.field_options.is_customer_field" @input="BPAforceUpdate()" v-model="fsinner_data.label"></el-input>
                                                                                            </div>                                                                    
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'Phone'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e('Set Custom Placeholder', 'bookingpress-appointment-booking'); ?></label> 
                                                                                                <el-switch v-model="fsinner_data.field_options.set_custom_placeholder" class="bpa-swtich-control"></el-switch>
                                                                                            </div>
                                                                                            
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type != 'Checkbox' && fsinner_data.field_type != 'Radio' && ( fsinner_data.field_type != 'Phone' || ( fsinner_data.field_type == 'Phone' && fsinner_data.field_options.set_custom_placeholder == true)) && fsinner_data.field_type != 'File' && fsinner_data.field_type !='terms_and_conditions' ">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Placeholder', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" :disabled="'false' != fsinner_data.field_options.is_customer_field" @input="BPAforceUpdate()" v-model="fsinner_data.placeholder"></el-input>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'File'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Browse Button Label', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" v-model="fsinner_data.field_options.browse_button_label">
                                                                                            </div>        
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'Text' || fsinner_data.field_type == 'Textarea'">
                                                                                                <el-row type="flex">
                                                                                                    <el-col :xs="11" :sm="11" :md="11" :lg="11" :xl="11" class="bpa-cs__heading">
                                                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Minimum', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                        <el-input-number :min="0" controls-position="right" class="bpa-form-control"  @input="BPAforceUpdate()"  v-model="fsinner_data.field_options.minimum" step-strictly></el-input-number>&nbsp;&nbsp;
                                                                                                    </el-col>
                                                                                                    <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading"></el-col>
                                                                                                    <el-col :xs="11" :sm="11" :md="11" :lg="11" :xl="11" class="bpa-cs__heading">
                                                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Maximum', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                        <el-input-number :min="0"  controls-position="right" class="bpa-form-control" @input="BPAforceUpdate()" v-model="fsinner_data.field_options.maximum" step-strictly></el-input-number>
                                                                                                    </el-col>
                                                                                                </el-row>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Error Message', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" v-model="fsinner_data.error_message"></el-input>
                                                                                            </div>
                                                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type != 'Password'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Meta Key', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" :disabled="'false' != fsinner_data.field_options.is_customer_field" v-model="fsinner_data.meta_key"></el-input>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'Date'">
                                                                                                <el-row type="flex">
                                                                                                    <el-col :xs="20" :sm="20" :md="20" :lg="20" :xl="20" class="bpa-cs__heading">
                                                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Enable Time Picker', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                    </el-col>
                                                                                                    <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading">                                                                                                                                                                                                               
                                                                                                        <el-switch :disabled="'false' != fsinner_data.field_options.is_customer_field" class="bpa-swtich-control" @change="BPAforceUpdate()" v-model="fsinner_data.field_options.enable_timepicker"></el-switch>
                                                                                                    </el-col>
                                                                                                </el-row>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'File'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'File Size Limit', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" v-model="fsinner_data.field_options.max_file_size">
                                                                                                    <template slot="append">MB</template>
                                                                                                </el-input>
                                                                                                <el-description class="bpa-form-control">
                                                                                                    <el-description-item style="color:#ff0000"><?php echo esc_html__( 'PHP Maximum Upload Size', 'bookingpress-appointment-booking' ) . ' ' . ini_get( 'upload_max_filesize' ); // phpcs:ignore ?></el-description-item>
                                                                                                </el-description>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'File'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Allowed File Extension', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" v-model="fsinner_data.field_options.allowed_file_ext"></el-input>
                                                                                                <el-description class="bpa-form-control">
                                                                                                    <el-description-item><?php esc_html_e( 'You should place comma separated list of file extensions.', 'bookingpress-appointment-booking' ); ?></el-description-item><br/>
                                                                                                    <el-description-item><?php esc_html_e( 'Leave blank for allow all file types.', 'bookingpress-appointment-booking' ); ?></el-description-item>
                                                                                                </el-description>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'File'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Invalid Field message', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" v-model="fsinner_data.field_options.invalid_field_message"></el-input>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type == 'File'">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Attach file with email', 'bookingpress-appointment-booking'); ?></label>
                                                                                                <el-switch v-model="fsinner_data.field_options.attach_with_email" class="bpa-swtich-control"></el-switch>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Visibility', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-radio @change="changeFieldVisibility(ifskey, 'always', fskey,ifrskey)" class="bpa-form-label bpa-custom-radio--is-label" v-model="fsinner_data.field_options.visibility" label="always"><?php esc_html_e( 'Always', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                                            <el-radio v-if="fsinner_data.field_type != 'terms_and_conditions' && fsinner_data.field_type != 'username' && fsinner_data.field_type != 'Password'" @change="changeFieldVisibility(ifskey, 'services', fskey,ifrskey)" class="bpa-form-label bpa-custom-radio--is-label" v-model="fsinner_data.field_options.visibility" label="services"><?php esc_html_e( 'Show conditionally on specific service', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                                                <div v-if="fsinner_data.field_options.visibility == 'services'">
                                                                                                    <label class="bpa-form-label"><?php esc_html_e( 'Select Services', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                    <el-select class="bpa-form-control" popper-class="popover_select_control" @input="BPAforceUpdate()" collapse-tags multiple v-model="fsinner_data.field_options.selected_services">
                                                                                                        <el-option v-for="item in bookingpress_service_data" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                                                                                    </el-select>
                                                                                                    <label v-if="bookingpress_service_error == true" class="bpa-form-label bpa-form-label-validation-message"><?php esc_html_e( 'Please select at-least one service', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                    <label v-else>&nbsp;</label>
                                                                                                </div>
                                                                                                <el-radio @change="changeFieldVisibility(ifskey, 'hidden', fskey,ifrskey)" class="bpa-form-label bpa-custom-radio--is-label" v-model="fsinner_data.field_options.visibility" label="hidden"><?php esc_html_e( 'Hidden', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                                            </div>
                                                                                            <div class="bpa-fs-item-settings-form-control-item">
                                                                                                <label class="bpa-form-label"><?php esc_html_e( 'CSS Class', 'bookingpress-appointment-booking' ); ?></label>
                                                                                                <el-input class="bpa-form-control" v-model="fsinner_data.css_class"></el-input>
                                                                                            </div>
                                                                                            <?php do_action('bookingpress_custom_field_setting_after'); ?>
                                                                                            <div class="bpa-customize--edit-label-popover--actions">
                                                                                                <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="saveFieldRepeterSettings(ifskey, fskey, ifrskey)"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                                            </div>
                                                                                        </el-container>
                                                                                        <el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
                                                                                            <span class="material-icons-round">settings</span>
                                                                                        </el-button>
                                                                                    </el-popover>
                                                                                </el-popover>    
                                                                            </div>
                                                                            <div class="bpa-cfs-ic--head__fc-actions" v-if="fsinner_data.is_default != 1">
                                                                                <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                                                    <div slot="content">
                                                                                        <span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
                                                                                    </div>
                                                                                    <el-button @click="deleteRepeatInnerColField(ifskey, fskey, ifrskey)" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                                                        <span class="material-icons-round">delete</span>
                                                                                    </el-button>
                                                                                </el-tooltip>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="bpa-cfs-ic--body">
                                                                        <div class="bpa-cfs-ic--body__field-preview">
                                                                            <span class="bpa-form-label" v-if="fsinner_data.field_type != 'terms_and_conditions'" v-text="fsinner_data.label"></span>
                                                                            <el-input class="bpa-form-control" v-if='(fsinner_data.field_type == "Text" || fsinner_data.field_type == "Email" || fsinner_data.field_type == "Phone")' :placeholder="fsinner_data.placeholder"></el-input>

                                                                            <el-input class="bpa-form-control" v-if='fsinner_data.field_type == "Password"' :placeholder="fsinner_data.placeholder" show-password></el-input>

                                                                            <el-input class="bpa-form-control" v-if='fsinner_data.field_type == "Textarea"' :placeholder="fsinner_data.placeholder" type="textarea" :rows="3"></el-input>
                                                                            <template v-if='fsinner_data.field_type == "terms_and_conditions"'>
                                                                                <el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" :label="fsinner_data.label" :key="">
                                                                                <div v-html="fsinner_data.label"></div></el-checkbox>
                                                                            </template>
                                                                            <template v-if='fsinner_data.field_type == "Checkbox"'>
                                                                                <el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" v-if="keys < 5" v-for="(chk_data, keys) in fsinner_data.field_values" :label="chk_data.label" :key="chk_data.value"><div v-html="chk_data.label"></div></el-checkbox>
                                                                            </template>
                                                                            <template v-if='fsinner_data.field_type == "Radio"'>
                                                                                <el-radio class="bpa-form-label bpa-custom-radio--is-label" v-if="keys < 5" v-for="(chk_data, keys) in fsinner_data.field_values" :label="chk_data.label" :key="chk_data.value">{{chk_data.label}}</el-radio>
                                                                            </template>
                                                                            <template v-if='fsinner_data.field_type == "Dropdown"'>
                                                                                <el-select class="bpa-form-control" :placeholder="fsinner_data.placeholder">
                                                                                    <el-option v-for="sel_data in fsinner_data.field_values" :key="sel_data.value" :label="sel_data.label" :value="sel_data.value" ></el-option>
                                                                                </el-select>
                                                                            </template>
                                                                            <el-date-picker class="bpa-form-control bpa-form-control--date-picker" prefix-icon="" v-if='fsinner_data.field_type == "Date"' :placeholder="fsinner_data.placeholder" :type="fsinner_data.enable_timepicker ? 'datetime' : 'date'"></el-date-picker>                                                        
                                                                            <el-upload v-if='fsinner_data.field_type == "File"' multiple="false" limit="1" auto-upload="false">
                                                                                <label for="bpa-file-upload-three" class="bpa-form-control--file-upload">
                                                                                    <span class="bpa-fu__placeholder" v-html="fsinner_data.placeholder"></span>
                                                                                    <span class="bpa-fu__btn" v-html="fsinner_data.field_options.browse_button_label"></span>
                                                                                </label> 
                                                                            </el-upload>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            </el-col>
                                                        </template>
                                                    </el-col>
                                                </div>
                                            </div>
                                        </div>
                                    </el-col>
                                </template>
                                <template v-else-if="field_settings_data.field_options.layout == '1col'">
                                    <div class="bpa-cfs-item-card">
                                        <div class="bpa-cfs-ic__body">
                                            <div class="bpa-cfs-ic--head">
                                                <div class="bpa-cfs-ic--head__type-label">
                                                    <span class="material-icons-round">drag_indicator</span>
                                                    <p>{{ field_settings_data.field_type }}</p>
                                                </div>
                                                <div class="bpa-cfs-ic--head__field-controls">
                                                    <div class="bpa-cfs-ic--head__fc-swtich">
                                                        <el-switch v-model="field_settings_fields[fskey].is_required" class="bpa-swtich-control" :disabled="field_settings_data.field_name == 'username' || field_settings_data.field_name == 'terms_and_conditions' || field_settings_data.field_name == 'Password'  ? true :false"></el-switch>
                                                        <label>Required</label>
                                                    </div>
                                                    <div class="bpa-cfs-ic--head__fc-actions" v-if="(field_settings_data.field_type == 'Checkbox' || field_settings_data.field_type == 'Radio' || field_settings_data.field_type == 'Dropdown' ) && 'false' == field_settings_data.field_options.is_customer_field" >
                                                        <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                            <div slot="content">
                                                                <span><?php esc_html_e( 'Field Options', 'bookingpress-appointment-booking' ); ?></span>
                                                            </div>
                                                            <el-popover width="550" placement="bottom-end" v-model="field_settings_data.is_edit_values">
                                                                <el-container class="bpa-field-settings-edit-container bpa-field-values-edit-container">
                                                                    <el-row type="flex" class="bpa-field-values-row-with-border">
                                                                        <el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16" class="bpa-cs__heading">
                                                                            <h3><?php esc_html_e( 'Manage Options', 'bookingpress-appointment-booking' ); ?></h3>
                                                                        </el-col>
                                                                        <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8" class="bpa-cs__heading">
                                                                            <el-button style="top:50%;transform:translateY(-50%);" @click="bpaDisplayPresetValues(fskey)" class="bpa-btn bpa-btn__small bpa-btn--full-width"><?php esc_html_e( 'Preset Values', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                        <el-col>
                                                                    </el-row>
                                                                    <el-row type="flex" class="bpa-field-values-row-with-border" v-if="field_settings_data.enable_preset_fields == true" >
                                                                        <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-cs__heading">
                                                                            <el-select placeholder="<?php esc_html_e( 'Select Preset Values', 'bookingpress-appointment-booking' ); ?>" class="bpa-form-control" v-model="field_settings_data.preset_field_choice" popper-class="popover_select_control">
                                                                                <el-option v-for="item in bookingpress_preset_fields" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                                                            </el-select>
                                                                        </el-col>&nbsp;
                                                                        <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-cs__heading bpa-field-preset-values-btn-wrapper">
                                                                            <el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" :disabled="preset_btn_disable" @click="applyPresetFields(fskey)" :class="(is_display_preset_value_loader == '1') ? 'bpa-btn--is-loader' : ''">
                                                                            <span class="bpa-btn__label"><?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?></span>
                                                                                <div class="bpa-btn--loader__circles">				    
                                                                                    <div></div>
                                                                                    <div></div>
                                                                                    <div></div>
                                                                                </div>
                                                                            </el-button>
                                                                            <el-button class="bpa-btn bpa-btn__medium bpa-field-values-cancel-preset-btn" @click="bpaHidePresetValues(fskey)"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                        </el-col>
                                                                    </el-row>
                                                                    <el-row type="flex">
                                                                        <el-col :xs="22" :sm="22" :md="22" :lg="22" :xl="22" class="bpa-cs__heading">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'Use Separate Value', 'bookingpress-appointment-booking' ); ?></label>
                                                                        </el-col>
                                                                        <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading">
                                                                            <el-switch class="bpa-swtich-control" v-model="field_settings_data.field_options.separate_value"></el-switch>
                                                                        </el-col>
                                                                    </el-row>
                                                                    <el-row type="flex" class="bpa-field-values-row-no-top-padding bpa-field-values-row-with-border bpa-field-values-options-wrapper">
                                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                            <el-row type="flex" class="bpa-cs__heading bpa-cs__values_heading">
                                                                                <el-col class="bpa-cs__field_value_label_heading"><?php esc_html_e( 'Option Label', 'bookingpress-appointment-booking' ); ?></el-col>
                                                                                <el-col v-if="field_settings_data.field_options.separate_value" class="bpa-cs__field_value_label_heading"><?php esc_html_e( 'Option Value', 'bookingpress-appointment-booking' ); ?></el-col>
                                                                                <el-col class="bpa-cs__field_value_icon">
                                                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" @click="bpaAddfieldValue(fskey)">
                                                                                        <span class="material-icons-round">add_circle</span>
                                                                                    </el-button>
                                                                                </el-col>
                                                                            </el-row>
                                                                            <el-row type="flex" class="bpa-cs__heading bpa-cs__values_items" v-for="(items, i_key) in field_settings_data.field_values" :key="i_key">
                                                                                <el-col class="bpa-cs__field_value_label">
                                                                                    <el-input class="bpa-form-field-value-input" v-model="items.label"></el-input>
                                                                                </el-col>
                                                                                <el-col v-if="field_settings_data.field_options.separate_value" class="bpa-cs__field_value_label">
                                                                                    <el-input class="bpa-form-field-value-input" v-model="items.value"></el-input>
                                                                                </el-col>
                                                                                <el-col class="bpa-cs__field_value_icon">
                                                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" @click="bpaRemovefieldValue(i_key, fskey)">
                                                                                        <span class="material-icons-round">remove_circle</span>
                                                                                    </el-button>
                                                                                </el-col>
                                                                            </el-row>
                                                                        </el-col>
                                                                    </el-row>
                                                                    <el-row class="bpa-field-values-row-no-top-padding bpa-cs-field-values-btn-wrapper">
                                                                        <div class="bpa-customize--edit-label-popover--actions">
                                                                            <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="closeFieldValueBtn(fskey)"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
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
                                                        <!-- <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                            <div slot="content">
                                                                <span v-if="field_settings_data.is_required === true"><?php esc_html_e( 'Disable Required', 'bookingpress-appointment-booking' ); ?></span>
                                                                <span v-else><?php esc_html_e( 'Enable Required', 'bookingpress-appointment-booking' ); ?></span>
                                                            </div>
                                                            <el-button class="bpa-btn bpa-btn--icon-without-box" :class="(field_settings_data.is_required === true) ? 'bpa-fs-required_fill_icon' : 'bpa-fs-required_icon'" type="text" @click="setFieldRequired(fskey,field_settings_data.id)" :disabled="field_settings_data.field_type == 'Email' ? true : false" >
                                                                <span class="material-icons-round">emergency</span>
                                                            </el-button>
                                                        </el-tooltip> -->
                                                    </div>
                                                    <div class="bpa-cfs-ic--head__fc-actions">
                                                        <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                            <div slot="content">
                                                                <span><?php esc_html_e( 'Field Settings', 'bookingpress-appointment-booking' ); ?></span>
                                                            </div>
                                                            <el-popover placement="bottom-end" v-model="field_settings_data.is_edit">
                                                                <el-container class="bpa-field-settings-edit-container">
                                                                    <div class="bpa-fs-item-settings-form-control-item">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Label', 'bookingpress-appointment-booking' ); ?></label>
                                                                        <el-input class="bpa-form-control" :disabled="'false' != field_settings_data.field_options.is_customer_field" v-model="field_settings_data.label"></el-input>
                                                                    </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type == 'Phone'">
                                                                        <label class="bpa-form-label"><?php esc_html_e('Set Custom Placeholder', 'bookingpress-appointment-booking'); ?></label>
                                                                        <el-switch v-model="field_settings_data.field_options.set_custom_placeholder" class="bpa-swtich-control"></el-switch>
                                                                    </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type != 'Checkbox' && field_settings_data.field_type != 'Radio' && ( field_settings_data.field_type != 'Phone' || (field_settings_data.field_type == 'Phone' && field_settings_data.field_options.set_custom_placeholder == true) ) && field_settings_data.field_type != 'terms_and_conditions' ">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Placeholder', 'bookingpress-appointment-booking' ); ?></label>
                                                                        <el-input class="bpa-form-control" :disabled="'false' != field_settings_data.field_options.is_customer_field" v-model="field_settings_data.placeholder"></el-input>
                                                                    </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type == 'File'">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Browse Button Label', 'bookingpress-appointment-booking' ); ?></label>
                                                                        <el-input class="bpa-form-control" :disabled="'false' != field_settings_data.field_options.is_customer_field" v-model="field_settings_data.field_options.browse_button_label"></el-input>
                                                                    </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type == 'Text' || field_settings_data.field_type == 'Textarea'">
                                                                        <el-row type="flex">
                                                                            <el-col :xs="11" :sm="11" :md="11" :lg="11" :xl="11" class="bpa-cs__heading">
                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Minimum', 'bookingpress-appointment-booking' ); ?></label>
                                                                                <el-input-number type ="number" controls-position="right" :min="0" @input="BPAforceUpdate()"   class="bpa-form-control"  v-model="field_settings_data.field_options.minimum" step-strictly></el-input-number>&nbsp;&nbsp;
                                                                            </el-col>
                                                                            <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading"></el-col>
                                                                            <el-col :xs="11" :sm="11" :md="11" :lg="11" :xl="11" class="bpa-cs__heading">
                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Maximum', 'bookingpress-appointment-booking' ); ?></label>
                                                                                <el-input-number controls-position="right" :min="0" class="bpa-form-control"  @input="BPAforceUpdate()" v-model="field_settings_data.field_options.maximum" step-strictly></el-input-number>
                                                                            </el-col>
                                                                        </el-row>
                                                                    </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Error Message', 'bookingpress-appointment-booking' ); ?></label>
                                                                        <el-input class="bpa-form-control" v-model="field_settings_data.error_message"></el-input>
                                                                    </div>
                                                                <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type != 'Password'">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Meta Key', 'bookingpress-appointment-booking' ); ?></label>
                                                                        <el-input class="bpa-form-control" :disabled="'false' != field_settings_data.field_options.is_customer_field" v-model="field_settings_data.meta_key"></el-input>
                                                                    </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type == 'Date'">
                                                                        <el-row type="flex">
                                                                            <el-col :xs="20" :sm="20" :md="20" :lg="20" :xl="20" class="bpa-cs__heading">
                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Enable Time Picker', 'bookingpress-appointment-booking' ); ?></label>
                                                                            </el-col>
                                                                            <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading">
                                                                                <el-switch :disabled="'false' != field_settings_data.field_options.is_customer_field" class="bpa-swtich-control" v-model="field_settings_data.field_options.enable_timepicker"></el-switch>
                                                                            </el-col>
                                                                        </el-row>
                                                                    </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type == 'File'">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'File Size Limit', 'bookingpress-appointment-booking' ); ?></label>
                                                                        <el-input class="bpa-form-control" v-model="field_settings_data.field_options.max_file_size">
                                                                            <template slot="append">MB</template>
                                                                        </el-input>
                                                                        <el-description class="bpa-form-control">
                                                                            <el-description-item style="color:#ff0000"><?php echo esc_html__( 'PHP Maximum Upload Size', 'bookingpress-appointment-booking' ) . ' ' . ini_get( 'upload_max_filesize' ); // phpcs:ignore ?></el-description-item>
                                                                        </el-description>
                                                                    </div> 
                                                                    <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type == 'File'">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Allowed File Extension', 'bookingpress-appointment-booking' ); ?></label>
                                                                        <el-input class="bpa-form-control" v-model="field_settings_data.field_options.allowed_file_ext"></el-input>
                                                                        <el-description class="bpa-form-control">
                                                                            <el-description-item><?php esc_html_e( 'You should place comma separated list of file extensions.', 'bookingpress-appointment-booking' ); ?></el-description-item><br/>
                                                                            <el-description-item><?php esc_html_e( 'Leave blank for allow all file types.', 'bookingpress-appointment-booking' ); ?></el-description-item>
                                                                        </el-description>
                                                                    </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type == 'File'">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Invalid Field message', 'bookingpress-appointment-booking' ); ?></label>
                                                                        <el-input class="bpa-form-control" v-model="field_settings_data.field_options.invalid_field_message"></el-input>
                                                                    </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type == 'File'">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Attach file with email', 'bookingpress-appointment-booking'); ?></label>
                                                                        <el-switch v-model="field_settings_data.field_options.attach_with_email" class="bpa-swtich-control"></el-switch>
                                                                    </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item">
                                                                        <label class="bpa-form-label"><?php esc_html_e( 'Visibility', 'bookingpress-appointment-booking' ); ?></label>
                                                                        <el-radio @change="changeFieldVisibility(fskey, 'always')" class="bpa-form-label bpa-custom-radio--is-label" v-model="field_settings_data.field_options.visibility" label="always"><?php esc_html_e( 'Always', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                    <el-radio v-if="field_settings_data.field_name != 'terms_and_conditions' && field_settings_data.field_name != 'username' && field_settings_data.field_name != 'Password'" @change="changeFieldVisibility(fskey, 'services')" class="bpa-form-label bpa-custom-radio--is-label" v-model="field_settings_data.field_options.visibility" label="services"><?php esc_html_e( 'Show conditionally on specific service', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                        <div v-if="field_settings_data.field_options.visibility == 'services'">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'Select Services', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-select class="bpa-form-control" popper-class="popover_select_control" collapse-tags multiple v-model="field_settings_data.field_options.selected_services">
                                                                                <el-option v-for="item in bookingpress_service_data" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                                                            </el-select>
                                                                            <label v-if="bookingpress_service_error == true" class="bpa-form-label bpa-form-label-validation-message"><?php esc_html_e( 'Please select at-least one service', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <label v-else>&nbsp;</label>
                                                                        </div>
                                                                        <el-radio @change="changeFieldVisibility(fskey, 'hidden')" class="bpa-form-label bpa-custom-radio--is-label" v-model="field_settings_data.field_options.visibility" label="hidden"><?php esc_html_e( 'Hidden', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                    </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'CSS Class', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-input class="bpa-form-control" v-model="field_settings_data.css_class"></el-input>
                                                                        </div>
                                                                        <?php do_action('bookingpress_custom_field_setting_after'); ?>
                                                                    <div class="bpa-customize--edit-label-popover--actions">
                                                                        <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="saveFieldSettings(fskey)"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                    </div>
                                                                </el-container>
                                                                <el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
                                                                    <span class="material-icons-round">settings</span>
                                                                </el-button>
                                                            </el-popover>
                                                        </el-tooltip>
                                                    </div>
                                                    <div class="bpa-cfs-ic--head__fc-actions" v-if="field_settings_data.is_default != 1">
                                                        <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                            <div slot="content">
                                                                <span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
                                                            </div>
                                                            <el-button @click="deleteField(fskey)" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                                <span class="material-icons-round">delete</span>
                                                            </el-button>
                                                        </el-tooltip>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bpa-cfs-ic--body">
                                                <div class="bpa-cfs-ic--body__field-preview">
                                                    <span class="bpa-form-label" v-text="field_settings_data.label" v-if="field_settings_data.field_type != 'terms_and_conditions'"></span>
                                                    <el-input class="bpa-form-control" v-if='(field_settings_data.field_type == "Text" || field_settings_data.field_type == "Email" || field_settings_data.field_type == "Phone")' :placeholder="field_settings_data.placeholder"></el-input>

                                                    <el-input class="bpa-form-control" v-if='field_settings_data.field_type == "Password"' :placeholder="field_settings_data.placeholder" show-password></el-input>

                                                    <el-input class="bpa-form-control" v-if='field_settings_data.field_type == "Textarea"' :placeholder="field_settings_data.placeholder" type="textarea" :rows="3"></el-input>
                                                    <template v-if='field_settings_data.field_type == "terms_and_conditions"'>
                                                        <el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" :label="field_settings_data.label" :key="">
                                                        <div v-html="field_settings_data.label"></div></el-checkbox>
                                                    </template>
                                                    <template v-if='field_settings_data.field_type == "Checkbox"'>
                                                        <el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" v-if="keys < 5" v-for="(chk_data, keys) in field_settings_data.field_values" :label="chk_data.label" :key="chk_data.value"><div v-html="chk_data.label"></div></el-checkbox>
                                                    </template>
                                                    <template v-if='field_settings_data.field_type == "Radio"'>
                                                        <el-radio class="bpa-form-label bpa-custom-radio--is-label" v-if="keys < 5" v-for="(chk_data, keys) in field_settings_data.field_values" :label="chk_data.label" :key="chk_data.value">{{chk_data.label}}</el-radio>
                                                    </template>
                                                    <template v-if='field_settings_data.field_type == "Dropdown"'>
                                                        <el-select class="bpa-form-control" :placeholder="field_settings_data.placeholder">
                                                            <el-option v-for="sel_data in field_settings_data.field_values" :key="sel_data.value" :label="sel_data.label" :value="sel_data.value" ></el-option>
                                                        </el-select>
                                                    </template>
                                                    <el-date-picker class="bpa-form-control bpa-form-control--date-picker" prefix-icon="" v-if='field_settings_data.field_type == "Date"' :placeholder="field_settings_data.placeholder" :type="field_settings_data.field_options.enable_timepicker ? 'datetime' : 'date'"></el-date-picker>                                                
                                                    <el-upload v-if='field_settings_data.field_type == "File"' multiple="false" limit="1" auto-upload="false">
                                                        <label for="bpa-file-upload-two" class="bpa-form-control--file-upload">
                                                            <span class="bpa-fu__placeholder" v-html="field_settings_data.placeholder"></span>
                                                            <span class="bpa-fu__btn" v-html="field_settings_data.field_options.browse_button_label"></span>
                                                        </label> 
                                                    </el-upload>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template :class="[( field_settings_data.field_options.layout == '2col' ? 'bpa-field-two-col-parent-container' : '' ), ( field_settings_data.field_options.layout == '3col' ? 'bpa-field-three-col-parent-container' : '' ),( field_settings_data.field_options.layout == '4col' ? 'bpa-field-four-col-parent-container' : '' )]" :data-fid="fskey" v-else>
                                    <el-col class="bpa-inner-field-container bpa-two-col-container bpa-field-wrapper-container" :xs="12" :sm="12" :md="12" :lg="12" :xl="12" :data-fkey="ifskey" :data-field-id="fsinner_data.id" :data-is-customer-field="fsinner_data.field_options.is_customer_field" :data-metakey="fsinner_data.meta_key" :data-type="fsinner_data.field_type" v-for="(fsinner_data, ifskey) in field_settings_data.field_options.inner_fields" :data-id="ifskey" :key="fsinner_data.id" v-if="field_settings_data.field_options !== undefined && field_settings_data.field_options.inner_fields !== undefined" :class="( (fsinner_data.is_blank == true || fsinner_data.is_blank == 'true') ? 'inner-field-blank-container' : '')">
                                        <div :data-ifskey="ifskey" :data-ifslabel="fsinner_data.label" :data-ifid="fsinner_data.id" v-if="( fsinner_data.is_blank != true && fsinner_data.is_blank != 'true' )" class="bpa-cfs-item-card">
                                            <div class="bpa-cfs-ic__body" :data-inkey="ifskey" :data-ifid="fsinner_data.id">
                                                <div class="bpa-cfs-ic--head">
                                                    <div class="bpa-cfs-ic--head__type-label">
                                                        <span class="material-icons-round">drag_indicator</span>
                                                        <p>{{ fsinner_data.field_type }}</p>
                                                    </div>
                                                    <div class="bpa-cfs-ic--head__field-controls">
                                                        <div class="bpa-cfs-ic--head__fc-swtich">
                                                            <el-switch v-model="field_settings_data.field_options.inner_fields[ifskey].is_required" class="bpa-swtich-control" :disabled="fsinner_data.field_name == 'username' || fsinner_data.field_name == 'terms_and_conditions' || fsinner_data.field_name == 'Password' ? true :false"></el-switch>
                                                            <label>Required</label>
                                                        </div>
                                                        <div class="bpa-cfs-ic--head__fc-actions" v-if="(fsinner_data.field_type == 'Checkbox' || fsinner_data.field_type == 'Radio' || fsinner_data.field_type == 'Dropdown' ) && 'false' == fsinner_data.field_options.is_customer_field">
                                                            <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                                <div slot="content">
                                                                    <span><?php esc_html_e( 'Field Options', 'bookingpress-appointment-booking' ); ?></span>
                                                                </div>
                                                                <el-popover width="550" placement="bottom-end" v-model="fsinner_data.is_edit_values">
                                                                    <el-container class="bpa-field-settings-edit-container bpa-field-values-edit-container">
                                                                        <el-row type="flex" class="bpa-field-values-row-with-border">
                                                                            <el-col :xs="18" :sm="18" :md="18" :lg="18" :xl="18" class="bpa-cs__heading">
                                                                                <h3><?php esc_html_e( 'Manage Options', 'bookingpress-appointment-booking' ); ?></h3>
                                                                            </el-col>
                                                                            <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6" class="bpa-cs__heading">
                                                                                <el-button style="top:50%;transform:translateY(-50%);" @click="bpaDisplayPresetValues(ifskey, fskey)" class="bpa-btn bpa-btn__small bpa-btn--full-width"><?php esc_html_e( 'Preset Values', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                            <el-col>
                                                                        </el-row>
                                                                        <el-row type="flex" class="bpa-field-values-row-with-border" v-if="fsinner_data.enable_preset_fields" >
                                                                            <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-cs__heading">
                                                                                <el-select placeholder="<?php esc_html_e( 'Select Preset Values', 'bookingpress-appointment-booking' ); ?>" class="bpa-form-control" v-model="fsinner_data.preset_field_choice" popper-class="popover_select_control">
                                                                                    <el-option v-for="item in bookingpress_preset_fields" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                                                                </el-select>
                                                                            </el-col>&nbsp;
                                                                            <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-cs__heading bpa-field-preset-values-btn-wrapper">
                                                                                <el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" :disabled="preset_btn_disable" @click="applyPresetFields(ifskey, fskey)" :class="(is_display_preset_value_loader == '1') ? 'bpa-btn--is-loader' : ''">
                                                                                <span class="bpa-btn__label"><?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?></span>
                                                                                    <div class="bpa-btn--loader__circles">				    
                                                                                        <div></div>
                                                                                        <div></div>
                                                                                        <div></div>
                                                                                    </div>
                                                                                </el-button>
                                                                                <el-button class="bpa-btn bpa-btn__medium bpa-field-values-cancel-preset-btn" @click="bpaHidePresetValues(ifskey, fskey)"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                            </el-col>
                                                                        </el-row>
                                                                        <el-row type="flex">
                                                                            <el-col :xs="22" :sm="22" :md="22" :lg="22" :xl="22" class="bpa-cs__heading">
                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Use Separate Value', 'bookingpress-appointment-booking' ); ?></label>
                                                                            </el-col>
                                                                            <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading">
                                                                                <el-switch class="bpa-swtich-control" @input="BPAforceUpdate()" v-model="fsinner_data.field_options.separate_value"></el-switch>
                                                                            </el-col>
                                                                        </el-row>
                                                                        <el-row type="flex" class="bpa-field-values-row-no-top-padding bpa-field-values-row-with-border bpa-field-values-options-wrapper">
                                                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                                                <el-row type="flex" class="bpa-cs__heading bpa-cs__values_heading">
                                                                                    <el-col class="bpa-cs__field_value_label_heading"><?php esc_html_e( 'Option Label', 'bookingpress-appointment-booking' ); ?></el-col>
                                                                                    <el-col v-if="fsinner_data.field_options.separate_value" class="bpa-cs__field_value_label_heading"><?php esc_html_e( 'Option Value', 'bookingpress-appointment-booking' ); ?></el-col>
                                                                                    <el-col class="bpa-cs__field_value_icon">
                                                                                        <el-button class="bpa-btn bpa-btn--icon-without-box" @click="bpaAddfieldValue(ifskey,fskey)">
                                                                                            <span class="material-icons-round">add_circle</span>
                                                                                        </el-button>
                                                                                    </el-col>
                                                                                </el-row>
                                                                                <el-row type="flex" class="bpa-cs__heading bpa-cs__values_items" v-for="(items, i_key) in fsinner_data.field_values" :key="i_key">
                                                                                    <el-col class="bpa-cs__field_value_label">
                                                                                        <el-input class="bpa-form-field-value-input" @input="BPAforceUpdate()" v-model="items.label"></el-input>
                                                                                    </el-col>
                                                                                    <el-col v-if="fsinner_data.field_options.separate_value" class="bpa-cs__field_value_label">
                                                                                        <el-input class="bpa-form-field-value-input" @input="BPAforceUpdate()" v-model="items.value"></el-input>
                                                                                    </el-col>
                                                                                    <el-col class="bpa-cs__field_value_icon">
                                                                                        <el-button class="bpa-btn bpa-btn--icon-without-box" @click="bpaRemovefieldValue(i_key, ifskey, fskey)">
                                                                                            <span class="material-icons-round">remove_circle</span>
                                                                                        </el-button>
                                                                                    </el-col>
                                                                                </el-row>
                                                                            </el-col>
                                                                        </el-row>
                                                                        <el-row class="bpa-field-values-row-no-top-padding bpa-cs-field-values-btn-wrapper">
                                                                            <div class="bpa-customize--edit-label-popover--actions">
                                                                                <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="closeFieldValueBtn(ifskey, fskey)"><?php esc_html_e( 'Ok', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                            </div>
                                                                        </el-row>
                                                                    </el-container>
                                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
                                                                        <span class="material-icons-round">rule</span>
                                                                    </el-button>
                                                                </el-popover>
                                                            </el-tooltip>   
                                                        </div>
                                                        <!-- <div class="bpa-cfs-ic--head__fc-actions">
                                                            <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                                <div slot="content">
                                                                    <span v-if="fsinner_data.is_required === true"><?php esc_html_e( 'Disable Required', 'bookingpress-appointment-booking' ); ?></span>
                                                                    <span v-else><?php esc_html_e( 'Enable Required', 'bookingpress-appointment-booking' ); ?></span>
                                                                </div>
                                                                <el-button class="bpa-btn bpa-btn--icon-without-box" :class="(fsinner_data.is_required === true) ? 'bpa-fs-required_fill_icon' : 'bpa-fs-required_icon'" type="text" @click="setInnerFieldRequired(ifskey, fsinner_data.id, fskey)" :disabled="fsinner_data.field_type == 'Email' ? true : false">
                                                                    <span class="material-icons-round">emergency</span>
                                                                </el-button>
                                                            </el-tooltip>
                                                        </div> -->
                                                        <div class="bpa-cfs-ic--head__fc-actions">
                                                            <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                                <div slot="content">
                                                                    <span><?php esc_html_e( 'Field Settings', 'bookingpress-appointment-booking' ); ?></span>
                                                                </div>
                                                                <el-popover placement="bottom-end" v-model="fsinner_data.is_edit">
                                                                    <el-container class="bpa-field-settings-edit-container">
                                                                        <div class="bpa-fs-item-settings-form-control-item">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'Label', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-input class="bpa-form-control" :disabled="'false' != fsinner_data.field_options.is_customer_field" @input="BPAforceUpdate()" v-model="fsinner_data.label"></el-input>
                                                                        </div>                                                                    
                                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'Phone'">
                                                                            <label class="bpa-form-label"><?php esc_html_e('Set Custom Placeholder', 'bookingpress-appointment-booking'); ?></label> 
                                                                            <el-switch v-model="fsinner_data.field_options.set_custom_placeholder" class="bpa-swtich-control"></el-switch>
                                                                        </div>
                                                                        
                                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type != 'Checkbox' && fsinner_data.field_type != 'Radio' && ( fsinner_data.field_type != 'Phone' || ( fsinner_data.field_type == 'Phone' && fsinner_data.field_options.set_custom_placeholder == true)) && fsinner_data.field_type !='terms_and_conditions' ">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'Placeholder', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-input class="bpa-form-control" :disabled="'false' != fsinner_data.field_options.is_customer_field" @input="BPAforceUpdate()" v-model="fsinner_data.placeholder"></el-input>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'File'">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'Browse Button Label', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-input class="bpa-form-control" :disabled="'false' != fsinner_data.field_options.is_customer_field" @input="BPAforceUpdate()" v-model="fsinner_data.field_options.browse_button_label"></el-input>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'Text' || fsinner_data.field_type == 'Textarea'">
                                                                            <el-row type="flex">
                                                                                <el-col :xs="11" :sm="11" :md="11" :lg="11" :xl="11" class="bpa-cs__heading">
                                                                                    <label class="bpa-form-label"><?php esc_html_e( 'Minimum', 'bookingpress-appointment-booking' ); ?></label>
                                                                                    <el-input-number :min="0" controls-position="right" class="bpa-form-control"  @input="BPAforceUpdate()"  v-model="fsinner_data.field_options.minimum" step-strictly></el-input-number>&nbsp;&nbsp;
                                                                                </el-col>
                                                                                <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading"></el-col>
                                                                                <el-col :xs="11" :sm="11" :md="11" :lg="11" :xl="11" class="bpa-cs__heading">
                                                                                    <label class="bpa-form-label"><?php esc_html_e( 'Maximum', 'bookingpress-appointment-booking' ); ?></label>
                                                                                    <el-input-number :min="0"  controls-position="right" class="bpa-form-control" @input="BPAforceUpdate()" v-model="fsinner_data.field_options.maximum" step-strictly></el-input-number>
                                                                                </el-col>
                                                                            </el-row>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'Error Message', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-input class="bpa-form-control" v-model="fsinner_data.error_message"></el-input>
                                                                        </div>
                                                                    <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type != 'Password'">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'Meta Key', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-input class="bpa-form-control" :disabled="'false' != fsinner_data.field_options.is_customer_field" v-model="fsinner_data.meta_key"></el-input>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'Date'">
                                                                            <el-row type="flex">
                                                                                <el-col :xs="20" :sm="20" :md="20" :lg="20" :xl="20" class="bpa-cs__heading">
                                                                                    <label class="bpa-form-label"><?php esc_html_e( 'Enable Time Picker', 'bookingpress-appointment-booking' ); ?></label>
                                                                                </el-col>
                                                                                <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl="2" class="bpa-cs__heading">
                                                                                    <el-switch :disabled="'false' != field_settings_data.field_options.is_customer_field" class="bpa-swtich-control" @change="BPAforceUpdate()" v-model="fsinner_data.field_options.enable_timepicker"></el-switch>
                                                                                </el-col>
                                                                            </el-row>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'File'">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'File Size Limit', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-input class="bpa-form-control" v-model="fsinner_data.field_options.max_file_size">
                                                                                <template slot="append">MB</template>
                                                                            </el-input>
                                                                            <el-description class="bpa-form-control">
                                                                                <el-description-item style="color:#ff0000"><?php echo esc_html__( 'PHP Maximum Upload Size', 'bookingpress-appointment-booking' ) . ' ' . ini_get( 'upload_max_filesize' ); // phpcs:ignore ?></el-description-item>
                                                                            </el-description>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'File'">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'Allowed File Extension', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-input class="bpa-form-control" v-model="fsinner_data.field_options.allowed_file_ext"></el-input>
                                                                            <el-description class="bpa-form-control">
                                                                                <el-description-item><?php esc_html_e( 'You should place comma separated list of file extensions.', 'bookingpress-appointment-booking' ); ?></el-description-item><br/>
                                                                                <el-description-item><?php esc_html_e( 'Leave blank for allow all file types.', 'bookingpress-appointment-booking' ); ?></el-description-item>
                                                                            </el-description>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="fsinner_data.field_type == 'File'">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'Invalid Field message', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-input class="bpa-form-control" v-model="fsinner_data.field_options.invalid_field_message"></el-input>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item" v-if="field_settings_data.field_type == 'File'">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'Attach file with email', 'bookingpress-appointment-booking'); ?></label>
                                                                            <el-switch v-model="fsinner_data.field_options.attach_with_email" class="bpa-swtich-control"></el-switch>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'Visibility', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-radio @change="changeFieldVisibility(ifskey, 'always', fskey)" class="bpa-form-label bpa-custom-radio--is-label" v-model="fsinner_data.field_options.visibility" label="always"><?php esc_html_e( 'Always', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                        <el-radio v-if="fsinner_data.field_type != 'terms_and_conditions' && fsinner_data.field_type != 'username' && fsinner_data.field_type != 'Password'" @change="changeFieldVisibility(ifskey, 'services', fskey)" class="bpa-form-label bpa-custom-radio--is-label" v-model="fsinner_data.field_options.visibility" label="services"><?php esc_html_e( 'Show conditionally on specific service', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                            <div v-if="fsinner_data.field_options.visibility == 'services'">
                                                                                <label class="bpa-form-label"><?php esc_html_e( 'Select Services', 'bookingpress-appointment-booking' ); ?></label>
                                                                                <el-select class="bpa-form-control" popper-class="popover_select_control" @input="BPAforceUpdate()" collapse-tags multiple v-model="fsinner_data.field_options.selected_services">
                                                                                    <el-option v-for="item in bookingpress_service_data" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                                                                </el-select>
                                                                                <label v-if="bookingpress_service_error == true" class="bpa-form-label bpa-form-label-validation-message"><?php esc_html_e( 'Please select at-least one service', 'bookingpress-appointment-booking' ); ?></label>
                                                                                <label v-else>&nbsp;</label>
                                                                            </div>
                                                                            <el-radio @change="changeFieldVisibility(ifskey, 'hidden', fskey)" class="bpa-form-label bpa-custom-radio--is-label" v-model="fsinner_data.field_options.visibility" label="hidden"><?php esc_html_e( 'Hidden', 'bookingpress-appointment-booking' ); ?></el-radio>
                                                                        </div>
                                                                        <div class="bpa-fs-item-settings-form-control-item">
                                                                            <label class="bpa-form-label"><?php esc_html_e( 'CSS Class', 'bookingpress-appointment-booking' ); ?></label>
                                                                            <el-input class="bpa-form-control" v-model="fsinner_data.css_class"></el-input>
                                                                        </div>
                                                                        <?php do_action('bookingpress_custom_field_setting_after'); ?>
                                                                        <div class="bpa-customize--edit-label-popover--actions">
                                                                            <el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="saveFieldSettings(ifskey, fskey)"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
                                                                        </div>
                                                                    </el-container>
                                                                    <el-button class="bpa-btn bpa-btn--icon-without-box" slot="reference">
                                                                        <span class="material-icons-round">settings</span>
                                                                    </el-button>
                                                                </el-popover>
                                                            </el-popover>    
                                                        </div>
                                                        <div class="bpa-cfs-ic--head__fc-actions" v-if="fsinner_data.is_default != 1">
                                                            <el-tooltip effect="dark" content="" placement="top" open-delay="0">
                                                                <div slot="content">
                                                                    <span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
                                                                </div>
                                                                <el-button @click="deleteInnerField(ifskey, fskey)" class="bpa-btn bpa-btn--icon-without-box __danger">
                                                                    <span class="material-icons-round">delete</span>
                                                                </el-button>
                                                            </el-tooltip>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bpa-cfs-ic--body">
                                                    <div class="bpa-cfs-ic--body__field-preview">
                                                        <span class="bpa-form-label" v-if="fsinner_data.field_type != 'terms_and_conditions'" v-text="fsinner_data.label"></span>
                                                        <el-input class="bpa-form-control" v-if='(fsinner_data.field_type == "Text" || fsinner_data.field_type == "Email" || fsinner_data.field_type == "Phone")' :placeholder="fsinner_data.placeholder"></el-input>

                                                        <el-input class="bpa-form-control" v-if='fsinner_data.field_type == "Password"' :placeholder="fsinner_data.placeholder" show-password></el-input>

                                                        <el-input class="bpa-form-control" v-if='fsinner_data.field_type == "Textarea"' :placeholder="fsinner_data.placeholder" type="textarea" :rows="3"></el-input>
                                                        <template v-if='fsinner_data.field_type == "terms_and_conditions"'>
                                                            <el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" :label="fsinner_data.label" :key="">
                                                            <div v-html="fsinner_data.label"></div></el-checkbox>
                                                        </template>
                                                        <template v-if='fsinner_data.field_type == "Checkbox"'>
                                                            <el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" v-if="keys < 5" v-for="(chk_data, keys) in fsinner_data.field_values" :label="chk_data.label" :key="chk_data.value"><div v-html="chk_data.label"></div></el-checkbox>
                                                        </template>
                                                        <template v-if='fsinner_data.field_type == "Radio"'>
                                                            <el-radio class="bpa-form-label bpa-custom-radio--is-label" v-if="keys < 5" v-for="(chk_data, keys) in fsinner_data.field_values" :label="chk_data.label" :key="chk_data.value">{{chk_data.label}}</el-radio>
                                                        </template>
                                                        <template v-if='fsinner_data.field_type == "Dropdown"'>
                                                            <el-select class="bpa-form-control" :placeholder="fsinner_data.placeholder">
                                                                <el-option v-for="sel_data in fsinner_data.field_values" :key="sel_data.value" :label="sel_data.label" :value="sel_data.value" ></el-option>
                                                            </el-select>
                                                        </template>
                                                        <el-date-picker class="bpa-form-control bpa-form-control--date-picker" prefix-icon="" v-if='fsinner_data.field_type == "Date"' :placeholder="fsinner_data.placeholder" :type="fsinner_data.enable_timepicker ? 'datetime' : 'date'"></el-date-picker>                                                        
                                                        <el-upload v-if='fsinner_data.field_type == "File"' multiple="false" limit="1" auto-upload="false">
                                                            <label for="bpa-file-upload-three" class="bpa-form-control--file-upload">
                                                                <span class="bpa-fu__placeholder" v-html="fsinner_data.placeholder"></span>
                                                                <span class="bpa-fu__btn" v-html="fsinner_data.field_options.browse_button_label"></span>
                                                            </label> 
                                                        </el-upload>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </el-col>
                                </template>
                            </el-col>
                        </el-row>
                    </div>
                </el-col>
            </el-row>                      
        </div>
    </el-container>    
</el-main>
<?php do_action('bookingpress_add_customize_custom_fields_view_after'); ?>