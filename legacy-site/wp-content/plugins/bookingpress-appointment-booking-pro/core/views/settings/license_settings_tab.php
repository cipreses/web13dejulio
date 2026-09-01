<?php
    global $bookingpress_ajaxurl;
    $license_key = get_option( 'bkp_license_key' );
	$license_status  = get_option( 'bkp_license_status' );

    $license_activation_data = get_option( 'bkp_license_data_activate_response' );

    $license_details = "";
    if($license_activation_data != "")
        $license_details = json_decode($license_activation_data);
    
    if($license_details != "")
    {
        $license_limit = $license_details->license_limit;
        $expires = date('F j,Y',strtotime($license_details->expires));
        if( 'January 1,1970' == $expires ){
                $expires = 'Never Expires';
        }
        $customer_name = $license_details->customer_name;
        $customer_email = $license_details->customer_email;

    } 

    $invalid_license_type = get_option( 'bkp_license_invalid_license_type' );
    $invalid_license = get_option( 'bkp_license_invalid_license_message ');
?>
<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body"  name ="license_settings" label="license" data-tab_name="license_settings">
    <span slot="label">
        <i class="material-icons-round">apartment</i>
        <?php esc_html_e('License', 'bookingpress-appointment-booking'); ?>
    </span>
    <div class="bpa-general-settings-tabs--pb__card">
        <el-row type="flex" class="bpa-mlc-head-wrap-settings bpa-gs-tabs--pb__heading">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-gs-tabs--pb__heading--left">
                <h1 class="bpa-page-heading"><?php esc_html_e('License Details', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>            
        </el-row>
        <div class="bpa-gs--tabs-pb__content-body">
            <el-form id="license_form" :rules="rules_company" ref="license_form" :model="license_form"  @submit.native.prevent>
                <div class="bpa-gs__cb--item">
                    
                <?php if( '' === $license_key || false === $license_key ) { ?>

                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('Select Your Package', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18" >                
                            <el-form-item prop="license_package">
                            <el-select class="bpa-form-control" v-model="license_form.license_package" popper-class="bpa-el-select--is-with-navbar">
										<el-option label="<?php echo esc_html('Standard'); ?>" value="4110"><?php echo esc_html('Standard'); ?></el-option>
										<el-option label="<?php echo esc_html('Professional'); ?>" value="4113"><?php echo esc_html('Professional'); ?></el-option>
										<el-option label="<?php echo esc_html('Developer'); ?>" value="4116"><?php echo esc_html('Developer'); ?></el-option>
									</el-select>         
                            </el-form-item>                             
                        </el-col>
                    </el-row>

                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-gs--license-key-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                            <h4> <?php esc_html_e('License key', 'bookingpress-appointment-booking'); ?></h4>                    
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18" >                
                            <el-form-item prop="license_key">
                                <el-input class="bpa-form-control" v-model="license_form.license_key" placeholder="<?php esc_html_e('Enter License Key', 'bookingpress-appointment-booking'); ?>"></el-input>        
                            </el-form-item>  
                            <div class="bpa-license-msg bpa-license-error-msg" v-if="license_form.error_message != ''">
                                {{ license_form.error_message }}                      
                            </div>
                            <div class="bpa-license-msg bpa-license-success-msg" v-if="license_form.success_message != ''">
                                {{ license_form.success_message }}
                            </div>
                        </el-col>
                    </el-row>
                    
                    <?php } else { ?>                  
                       
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-gs--license-key-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                                <h4> <?php esc_html_e('License key', 'bookingpress-appointment-booking'); ?></h4>                    
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18" >                
                                <?php echo $license_key; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </el-col>
                        </el-row>

                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row bpa-gs--license-key-activation-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                                <h4> <?php esc_html_e('No. of activation allowed', 'bookingpress-appointment-booking'); ?></h4>                    
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18" >                
                                    <?php echo $license_limit; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </el-col>
                        </el-row>

                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                                <h4> <?php esc_html_e('Expires', 'bookingpress-appointment-booking'); ?></h4>                    
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18" >                
                                    <?php echo $expires; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </el-col>
                        </el-row>

                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                                <h4> <?php esc_html_e('Customer Name', 'bookingpress-appointment-booking'); ?></h4>                    
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18" >                
                                    <?php echo $customer_name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </el-col>
                        </el-row>

                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                            <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                                <h4> <?php esc_html_e('Customer Email', 'bookingpress-appointment-booking'); ?></h4>                    
                            </el-col>
                            <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18" >                
                                    <?php echo $customer_email; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </el-col>
                        </el-row>

                    <?php } ?>


                    <?php if( $license_status !== false && $license_status == 'valid' ) { ?>
                    
                        <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                            &nbsp;
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18">
                            <el-form-item prop="register_license" >
                            <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="deactivateLicenseKey" :disabled="is_disabled" >                    
                                <span class="bpa-btn__label"><?php esc_html_e('Deactivate License', 'bookingpress-appointment-booking'); ?></span>
                                <div class="bpa-btn--loader__circles">                    
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </el-button>
                            </el-form-item>
                            <div class="bpa-license-msg bpa-license-error-msg" v-if="license_form.error_message != ''">
                                {{ license_form.error_message }}                      
                            </div>
                            <div class="bpa-license-msg bpa-license-success-msg" v-if="license_form.success_message != ''">
                                {{ license_form.success_message }}
                            </div>
                        </el-col>
                    </el-row>

                    <?php } else { ?>

                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                        <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                            &nbsp;
                        </el-col>
                        <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18">
                            <el-form-item prop="register_license" >
                            <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="activateLicenseKey" :disabled="is_disabled" >                    
                                <span class="bpa-btn__label"><?php esc_html_e('Activate License', 'bookingpress-appointment-booking'); ?></span>
                                <div class="bpa-btn--loader__circles">                    
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </el-button>
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <?php } ?> 
                    
                    <?php if( !('' === $invalid_license) && !(false === $invalid_license) && !('' === $invalid_license_type)) {
                        //echo "test";
                               
                    if( $invalid_license_type != "" && $invalid_license_type == "expired" ) { ?>
                    
                    <el-row type="flex" class="bpa-gs--tabs-pb__cb-item-row">
                    <el-col :xs="12" :sm="12" :md="12" :lg="6" :xl="6" class="bpa-gs__cb-item-left">
                        &nbsp;
                    </el-col>
                    <el-col :xs="12" :sm="12" :md="12" :lg="18" :xl="18">
                        <el-form-item prop="register_license" >
                        <?php echo $invalid_license; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <br><br>
                        <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="refreshLicenseKey" :disabled="is_disabled" >                    
                            <span class="bpa-btn__label"><?php esc_html_e('Refresh License', 'bookingpress-appointment-booking'); ?></span>
                            <div class="bpa-btn--loader__circles">                    
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </el-button>
                        </el-form-item>
                        <div class="bpa-license-msg bpa-license-error-msg" v-if="license_form.error_message != ''">
                            {{ license_form.error_message }}                      
                        </div>
                        <div class="bpa-license-msg bpa-license-success-msg" v-if="license_form.success_message != ''">
                            {{ license_form.success_message }}
                        </div>
                    </el-col>
                </el-row>

                <?php } ?>

                <?php } ?>
                
                </div>
            <el-form>        
        </div>        
    </div>
</el-tab-pane>
