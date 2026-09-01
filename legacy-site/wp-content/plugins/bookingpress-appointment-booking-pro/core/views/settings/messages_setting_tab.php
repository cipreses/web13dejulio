<el-tab-pane class="bpa-tabs--v_ls__tab--pane-body" name ="message_settings" label="messages" data-tab_name="message_settings">
    <span slot="label">
        <i class="material-icons-round">question_answer</i>
        <?php esc_html_e('Messages', 'bookingpress-appointment-booking'); ?>
    </span>
    <div class="bpa-general-settings-tabs--pb__card bpa-payment-settings-tabs--pb__card">
        <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
            <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('Message Settings', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="12">
                <div class="bpa-hw-right-btn-group bpa-gs-tabs--pb__btn-group">    
                    <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveSettingsData('message_setting_form','message_setting')" :disabled="is_disabled" >                    
                      <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                      <div class="bpa-btn--loader__circles">                    
                          <div></div>
                          <div></div>
                          <div></div>
                      </div>
                    </el-button>
                    <?php do_action('bookingpress_message_setting_header_button'); ?>
                </div>
            </el-col>
        </el-row>
        <div class="bpa-gs--tabs-pb__content-body">
            <div class="bpa-gs__cb--item">
                <el-form id="message_setting_form" :rules="rules_message" ref="message_setting_form" :model="message_setting_form"  @submit.native.prevent>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No service selected for the booking', 'bookingpress-appointment-booking'); ?></h4>                
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="no_service_selected_for_the_booking">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_service_selected_for_the_booking"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No appointment date selected for the booking', 'bookingpress-appointment-booking'); ?></h4>    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">                
                            <el-form-item prop="no_appointment_date_selected_for_the_booking">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_appointment_date_selected_for_the_booking"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No appointment time selected for the booking', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="no_appointment_time_selected_for_the_booking">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_appointment_time_selected_for_the_booking"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No payment method is selected for the booking', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" :gutter="64">                
                            <el-form-item prop="no_payment_method_is_selected_for_the_booking">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_payment_method_is_selected_for_the_booking"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Duplicate email address found', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="duplicate_email_address_found">
                            <el-input class="bpa-form-control" v-model="message_setting_form.duplicate_email_address_found"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Unsupported currency selected for the payment', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="unsupported_currecy_selected_for_the_payment">
                            <el-input class="bpa-form-control" v-model="message_setting_form.unsupported_currecy_selected_for_the_payment"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Time slot already booked', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" :gutter="64">                
                            <el-form-item prop="duplidate_appointment_time_slot_found">
                            <el-input class="bpa-form-control" v-model="message_setting_form.duplidate_appointment_time_slot_found"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No payment method available', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="no_payment_method_available">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_payment_method_available"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row> 
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" v-if="is_staffmember_activate == 1">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php echo esc_html__('No','bookingpress-appointment-booking')." ".esc_html($bookingpress_singular_staffmember_name)." ".esc_html__('selected for the booking', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="no_staffmember_selected_for_the_booking">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_staffmember_selected_for_the_booking"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>                 
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" v-if="is_coupon_activate == 1">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Coupon code is not valid', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="coupon_code_not_valid">
                            <el-input class="bpa-form-control" v-model="message_setting_form.coupon_code_not_valid"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" v-if="is_coupon_activate == 1">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Coupon code not allowed', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="coupon_code_not_allowed">
                            <el-input class="bpa-form-control" v-model="message_setting_form.coupon_code_not_allowed"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row> 
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" v-if="is_coupon_activate == 1">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Coupon code expired', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="coupon_code_expired">
                            <el-input class="bpa-form-control" v-model="message_setting_form.coupon_code_expired"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row> 
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" v-if="is_coupon_activate == 1">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Coupon code is not valid for selected service', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="coupon_code_not_valid_for_service">
                            <el-input class="bpa-form-control" v-model="message_setting_form.coupon_code_not_valid_for_service"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row> 
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" v-if="is_coupon_activate == 1">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Coupon code no longer available', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="coupon_code_no_longer_available">
                            <el-input class="bpa-form-control" v-model="message_setting_form.coupon_code_no_longer_available"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>         
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" v-if="is_coupon_activate == 1">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Coupon code does not exist', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="coupon_code_does_not_exist">
                            <el-input class="bpa-form-control" v-model="message_setting_form.coupon_code_does_not_exist"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Please fill all fields value of card details', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="bookingpress_card_details_error_msg">
                            <el-input class="bpa-form-control" v-model="message_setting_form.bookingpress_card_details_error_msg"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Payment token failure message', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="bookingpress_card_details_error_msg">
                            <el-input class="bpa-form-control" v-model="message_setting_form.payment_token_failure_message"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Payment already paid message', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="bookingpress_card_details_error_msg">
                            <el-input class="bpa-form-control" v-model="message_setting_form.payment_already_paid_message"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Complete payment success message', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="bookingpress_card_details_error_msg">
                            <el-input class="bpa-form-control" v-model="message_setting_form.complete_payment_success_message"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No timeslots available for booking', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="no_timeslots_available">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_timeslots_available"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Cancel Appointment Confirmation', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="cancel_appointment_confirmation">
                            <el-input class="bpa-form-control" v-model="message_setting_form.cancel_appointment_confirmation"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row>
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64">
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('No Appointment Available to Cancel', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="no_appointment_available_for_cancel">
                            <el-input class="bpa-form-control" v-model="message_setting_form.no_appointment_available_for_cancel"></el-input>        
                            </el-form-item>                        
                        </el-col>
                    </el-row> 
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row" :gutter="64" >
                        <el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Refund policy message', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" >                
                            <el-form-item prop="refund_policy_message">
                            <el-input class="bpa-form-control" v-model="message_setting_form.refund_policy_message"></el-input>        
                        </el-col>
                    </el-row>
                    <?php do_action('bookingpress_add_setting_msg_outside'); ?>
                <el-form>                    
            </div>            
        </div>            
    </div>
</el-tab-pane>
