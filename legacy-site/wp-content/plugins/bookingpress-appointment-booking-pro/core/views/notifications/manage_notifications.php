<?php
	global $bookingpress_global_options;
	$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
	$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
	$bookingpress_plural_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) : esc_html_e('Staff Members', 'bookingpress-appointment-booking');
?>
<el-main class="bpa-email-notifications-container bpa--is-page-scrollable-tablet" id="all-page-main-container">		
	<el-container class="bpa-default-card">
		<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
			<div class="bpa-back-loader"></div>
		</div>
		<div id="bpa-main-container">
			<el-row type="flex" :gutter="40">
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="5">
					<div class="bpa-en-left">
						<div class="bpa-en-left__item">
							<div class="bpa-en-left__item-head">
                                <h4 class="bpa-page-heading"><?php esc_html_e('Default Notifications', 'bookingpress-appointment-booking'); ?></h4>
							</div>
							<div class="bpa-en-left__item-body">
								<div class="bpa-en-left_item-body--list">
                                    <div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'appointment_approved' ? '__bpa-is-active' : ''" ref="appointmentApproved" @click="bookingpress_select_email_notification('<?php echo addslashes( __('Appointment Approval Notification', 'bookingpress-appointment-booking') ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>', 'Appointment Approved', 'appointment_approved')">
										<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_approved'] == true || default_notification_status['employee']['appointment_approved'] == true " >circle</span>
										<span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                        <p><?php esc_html_e('On Approval', 'bookingpress-appointment-booking'); ?></p>
									</div>
                                    <div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'appointment_pending' ? '__bpa-is-active' : ''" ref="appointmentPending" @click="bookingpress_select_email_notification('<?php echo addslashes( __('Appointment Pending Notification', 'bookingpress-appointment-booking') ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>', 'Appointment Pending', 'appointment_pending')">
										<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_pending'] == true || default_notification_status['employee']['appointment_pending'] == true" >circle</span>
										<span class="material-icons-round --bpa-item-status" v-else>circle</span>
										<p><?php esc_html_e('On Pending', 'bookingpress-appointment-booking'); ?></p>
									</div>
                                    <div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'appointment_rejected' ? '__bpa-is-active' : ''" ref="appointmentRejected" @click="bookingpress_select_email_notification('<?php echo addslashes( __('Appointment Rejection Notification', 'bookingpress-appointment-booking') ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>', 'Appointment Rejected', 'appointment_rejected')">
									<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_rejected'] == true || default_notification_status['employee']['appointment_rejected'] == true">circle</span>
										<span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                        <p><?php esc_html_e('On Rejection', 'bookingpress-appointment-booking'); ?></p>
									</div>
                                    <div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'appointment_canceled' ? '__bpa-is-active' : ''" ref="appointmentCanceled" @click="bookingpress_select_email_notification('<?php echo addslashes( __('Appointment Cancellation Notification', 'bookingpress-appointment-booking') ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>', 'Appointment Canceled', 'appointment_canceled')">
										<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_canceled'] == true || default_notification_status['employee']['appointment_canceled'] == true">circle</span>
										<span class="material-icons-round --bpa-item-status" v-else>circle</span>
                                        <p><?php esc_html_e('On Cancellation', 'bookingpress-appointment-booking'); ?></p>
									</div>
									<div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'appointment_rescheduled' ? '__bpa-is-active' : ''" ref="appointmentRescheduled" @click="bookingpress_select_email_notification('<?php esc_html_e('Appointment Rescheduled Notification', 'bookingpress-appointment-booking'); ?>','Appointment Rescheduled', 'appointment_rescheduled')">
										<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['appointment_rescheduled'] == true || default_notification_status['employee']['appointment_rescheduled'] == true">circle</span>
										<span class="material-icons-round --bpa-item-status" v-else>circle</span>
										<p><?php esc_html_e( 'On Rescheduled', 'bookingpress-appointment-booking' ); ?></p>
									</div>
									<div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'share_appointment' ? '__bpa-is-active' : ''" ref="shareAppointment" @click='bookingpress_select_email_notification("<?php esc_html_e('Share Appointment URL Notification', 'bookingpress-appointment-booking'); ?>","Share Appointment URL", "share_appointment")'>
										<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['share_appointment'] == true || default_notification_status['employee']['share_appointment'] == true">circle</span>
										<span class="material-icons-round --bpa-item-status" v-else>circle</span>
										<p><?php esc_html_e( 'Share Appointment URL', 'bookingpress-appointment-booking' ); ?></p>
									</div>
									<div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'complete_payment_url' ? '__bpa-is-active' : ''" ref="completePaymentURL" @click='bookingpress_select_email_notification("<?php esc_html_e('Complete Payment URL Notification', 'bookingpress-appointment-booking'); ?>","Complete Payment URL", "complete_payment_url")'>
										<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['complete_payment_url'] == true || default_notification_status['employee']['complete_payment_url'] == true">circle</span>
										<span class="material-icons-round --bpa-item-status" v-else>circle</span>
										<p><?php esc_html_e( 'Complete Payment URL', 'bookingpress-appointment-booking' ); ?></p>
									</div>
									<div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == 'refund_payment' ? '__bpa-is-active' : ''" ref="refundpayment" @click='bookingpress_select_email_notification("<?php esc_html_e('Refund Payment Notification', 'bookingpress-appointment-booking'); ?>","Refund Payment", "refund_payment")'>
										<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer']['refund_payment'] == true || default_notification_status['employee']['refund_payment'] == true">circle</span>
										<span class="material-icons-round --bpa-item-status" v-else>circle</span>
										<p><?php esc_html_e( 'Refund Payment', 'bookingpress-appointment-booking' ); ?></p>
									</div>
									<?php do_action('bookingpress_add_default_notification_section');  ?>
								</div>
							</div>		
							<div class="bpa-en-left__item-head">
								<h4 class="bpa-page-heading"><?php esc_html_e( 'Custom Notifications', 'bookingpress-appointment-booking' ); ?></h4>
							</div>
							<div class="bpa-en-left__item-body">
								<div class="bpa-en-left_item-body--list">
									<div class="bpa-en-left_item-body--list__item" :class="bookingpress_active_email_notification == bookingpress_custom_notification.bookingpress_notification_name ? '__bpa-is-active' : ''" ref="bookingpress_custom_notification.bookingpress_notification_name" @click="bookingpress_get_custom_notification_data(bookingpress_custom_notification.bookingpress_notification_name,1,bookingpress_custom_notification.bookingpress_notification_id)" v-for="bookingpress_custom_notification in bookingpress_custom_notification_listing">		
										<span class="material-icons-round --bpa-item-status is-enabled" v-if="default_notification_status['customer'][bookingpress_custom_notification.bookingpress_notification_name] == true || default_notification_status['employee'][bookingpress_custom_notification.bookingpress_notification_name] == true">circle</span>
										<span class="material-icons-round --bpa-item-status" v-else>circle</span>										
										<p>{{ bookingpress_custom_notification.bookingpress_notification_name }}</p>							
									</div>
									<div class="bpa-en-left_item-body--list__add-btn">
										<el-button class="bpa-btn bpa-btn__medium bpa-btn__filled-light bpa-btn--full-width" @click="bookingpress_add_custom_email_notification(event)">
											<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
										</el-button>
									</div>
								</div>
							</div>
						</div>	
					</div>
				</el-col>
				<el-col :xs="18" :sm="18" :md="18" :lg="18" :xl="19">
					<el-row>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <el-row type="flex" class="bpa-mlc-head-wrap">
                                <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--left">
                                    <h1 class="bpa-page-heading" v-text="bookingpress_email_notification_edit_text"></h1>
                                </el-col>
                                <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-gs-tabs--pb__heading--right">
                                    <div class="bpa-hw-right-btn-group">
                                    <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bookingpress_add_email_notification_data" :disabled="is_disabled" >                    
                                            <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                                            <div class="bpa-btn--loader__circles">                    
                                                <div></div>
                                                <div></div>
                                                <div></div>
                                            </div>
                                        </el-button> 										
										<el-button class="bpa-btn" @click="edit_custom_notification_settings(event,bookingpress_email_notification_edit_text,bookingpress_notification_id)" v-if="bookingpress_email_notification_edit_text != '' && bookingpress_is_custom_email_notification == true && bookingpress_notification_id !=''">
											<?php esc_html_e('Edit options', 'bookingpress-appointment-booking'); ?>
										</el-button>										
										<el-popconfirm 
											cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
											confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
											icon="false" 
											title="<?php esc_html_e( 'Are you sure you want to delete this Notification?', 'bookingpress-appointment-booking' ); ?>" 
											@confirm="bookingpress_discard_custom_email_notification(bookingpress_email_notification_edit_text)" 
											confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
											cancel-button-type="bpa-btn bpa-btn__small" v-if="bookingpress_email_notification_edit_text != '' && bookingpress_is_custom_email_notification == true && bookingpress_notification_id !=''">
											<el-button type="text" slot="reference" class="bpa-btn bpa-en-delete-btn">
												<span class="material-icons-round">delete</span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?> 
											</el-button>
										</el-popconfirm>
										<?php do_action('bookingpress_manage_notification_setting_header_button'); ?>
                                    </div>
                                </el-col>
                            </el-row>
                        </el-col>              
                    </el-row>
					<el-row type="flex" :gutter="32">
                        <el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="18">                                                                
                            <div class="bpa-en-body-card">
                                <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
                                    <div class="bpa-back-loader"></div>
                                </div>
                                <el-row type="flex" class="bpa-en-body-card__content">
                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                        <el-row type="flex">
                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <el-tabs class="bpa-tabs  bpa-elm-tab-container" v-model="activeTabName" @tab-click="bookingpress_notification_change_tab">
                                                    <el-tab-pane name="customer">
                                                        <template #label>
                                                            <span> {{bookingpress_notification_customer_tab_title}}</span>
                                                        </template>
                                                    </el-tab-pane>
                                                    <el-tab-pane name="employee">
                                                        <template #label>
															<span v-if="bookingpress_active_email_notification == 'package_order'"><?php esc_html_e( 'To Admin', 'bookingpress-appointment-booking' ); ?></span>
															<span v-else>{{bookingpress_notification_admin_tab_title}}</span>
                                                        </template>
                                                    </el-tab-pane>
                                                </el-tabs>
                                            </el-col>
                                        </el-row>
                                        <el-row type="flex">
                                            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                <el-form class="bpa-en-body-card__content--form" id="email_notification_form" ref="email_notification_form" @submit.native.prevent>
                                                    <el-row>
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                            <div class="bpa-en-status--swtich-row" v-if="activeTabName == 'customer'">
                                                                <label class="bpa-form-label"><?php esc_html_e('Send Notification', 'bookingpress-appointment-booking'); ?></label>
                                                                <el-switch class="bpa-swtich-control" v-model="default_notification_status[activeTabName][bookingpress_active_email_notification]"></el-switch>
                                                            </div>
                                                            <div class="bpa-en-status--swtich-row" v-if="activeTabName == 'employee'">
                                                                <label class="bpa-form-label"><?php esc_html_e('Send Notification', 'bookingpress-appointment-booking'); ?></label>
                                                                <el-switch class="bpa-swtich-control" v-model="default_notification_status[activeTabName][bookingpress_active_email_notification]"></el-switch>
                                                            </div>
                                                        </el-col>
														<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="activeTabName == 'employee' && staffmember_module == 1 && bookingpress_active_email_notification != 'package_order'">
															<el-form-item>
																<template #label>
																	<span class="bpa-form-label"><?php esc_html_e( 'CC Email Address', 'bookingpress-appointment-booking' ); ?> ( <?php esc_html_e( 'Please enter comma-separated email addresses if you want to send notification to multiple email address', 'bookingpress-appointment-booking' ); ?> )</span>
																</template>
																<el-input class="bpa-form-control" placeholder="<?php esc_html_e( 'Enter CC Email Address', 'bookingpress-appointment-booking' ); ?>" v-model="bookingpress_notification_cc_email" ></el-input>
															</el-form-item>												
														</el-col>		
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                            <el-form-item>
                                                                <template #label>
                                                                    <span class="bpa-form-label"><?php esc_html_e('Email Subject', 'bookingpress-appointment-booking'); ?></span>
                                                                </template>
                                                                <el-input class="bpa-form-control" v-model="bookingpress_email_notification_subject" placeholder="<?php esc_html_e('Enter Subject', 'bookingpress-appointment-booking'); ?>"></el-input>
                                                            </el-form-item>
                                                        </el-col>
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                            <el-form-item>
                                                                <template #label>
                                                                    <span class="bpa-form-label"><?php esc_html_e('Email Message', 'bookingpress-appointment-booking'); ?></span>
                                                                </template>
                                                                <?php
                                                                $bookingpress_message_content_editor = array(
                                                                        'textarea_name' => 'bookingpress_email_notification_subject_message',
                                                                        'media_buttons' => false,
                                                                        'textarea_rows' => 10,
                                                                        'default_editor' => 'html',
                                                                        'editor_css' => '',
                                                                        'tinymce' => true,
                                                                );
                                                                wp_editor('', 'bookingpress_email_notification_subject_message', $bookingpress_message_content_editor);
                                                                ?>
                                                            </el-form-item>
															<span class="bpa-sm__field-helper-label"><?php esc_html_e('Allowed HTML tags <div>, <label>, <span>, <p>, <ul>, <li>, <tr>, <td>, <a>, <br>, <b>, <h1>, <h2>, <hr>', 'bookingpress-appointment-booking'); ?></span>
                                                        </el-col>
                                                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                                            <div class="bpa-toast-notification --bpa-warning">
                                                                <div class="bpa-front-tn-body">
                                                                    <span class="material-icons-round">info</span>
                                                                    <p><?php esc_html_e('Note', 'bookingpress-appointment-booking'); ?>: <?php esc_html_e('Please add <br /> in the email message to add a new line', 'bookingpress-appointment-booking'); ?>. <?php esc_html_e('Enter key will not be considered as new line', 'bookingpress-appointment-booking'); ?>.</p>
                                                                </div>
                                                            </div>
                                                        </el-col>														
														<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
															<div class="bpa-toast-notification --bpa-warning">
																<div class="bpa-front-tn-body">
																	<span class="material-icons-round">info</span>
																	<p><?php esc_html_e('Note', 'bookingpress-appointment-booking'); ?>: <?php echo sprintf( esc_html__( "Scheduled emails depends on WordPress' cron mechanism and it may not send notifications on accurate time due to it's limitations. If you want more accurate notifications for reminders, please follow the steps described %s here %s", 'bookingpress-appointment-booking'), '<a href="https://www.bookingpressplugin.com/documents/set-schedule-notifications-cronjob/" target="_blank">', '</a>' ); //phpcs:ignore ?>. </p>
																</div>
															</div>
														</el-col>
														<el-col id="bookingpress_attach_ics_file" :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="bookingpress_active_email_notification != 'share_appointment' && bookingpress_active_email_notification != 'package_order'">
															<el-form-item>
																<div class="bpa-en-status--swtich-row">
																	<label class="bpa-form-label"><?php esc_html_e( 'Attach ICS file with email', 'bookingpress-appointment-booking' ); ?></label>
																	<el-switch class="bpa-swtich-control" v-model="bookingpress_email_ics_attachment_status"></el-switch>
																</div>
															</el-form-item>
														</el-col>
														<?php do_action('bookingpress_add_email_notification_section');  ?>
													</el-row>
                                                </el-form>
                                            </el-col>
                                        </el-row>
                                    </el-col>
                                </el-row>                      
                            </div>
                        </el-col>
                        <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="6">
                            <div class="bpa-email-tags-container">
                                <div class="bpa-gs__cb--item-heading">
                                    <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Insert email placeholders', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                                <div class="bpa-gs__cb--item-tags-body">
                                    <div>
                                        <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Customer', 'bookingpress-appointment-booking'); ?></span>
                                        <span class="bpa-tags--item-body" v-for="item in bookingpress_customer_placeholders" @click="bookingpress_insert_placeholder(item.value); bookingpress_insert_sms_placeholder(item.value); bookingpress_insert_whatsapp_placeholder(item.value);" v-if="( (item.value == '%customer_cancel_appointment_link%' && (bookingpress_is_custom_email_notification == false && bookingpress_active_email_notification != 'appointment_rejected' && bookingpress_active_email_notification != 'appointment_canceled' && bookingpress_active_email_notification != 'package_order')) || (item.value == '%customer_cancel_appointment_link%' && (bookingpress_is_custom_email_notification == true && custom_email_notification_form.bookingpress_notification_event_action != 'appointment_rejected' && custom_email_notification_form.bookingpress_notification_event_action != 'appointment_canceled')) || (item.value != '%customer_cancel_appointment_link%') )">
											{{ item.name }}
										</span>
                                    </div>
                                </div>
                                <div class="bpa-gs__cb--item-tags-body" v-if="bookingpress_active_email_notification != 'package_order'">
                                    <div>
                                        <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Service', 'bookingpress-appointment-booking'); ?></span>
                                        <span class="bpa-tags--item-body" v-for="item in bookingpress_service_placeholders" @click="bookingpress_insert_placeholder(item.value); bookingpress_insert_sms_placeholder(item.value); bookingpress_insert_whatsapp_placeholder(item.value);">{{ item.name }}</span>
                                    </div>
                                </div>
                                <div class="bpa-gs__cb--item-tags-body">
                                    <div>
                                        <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Company', 'bookingpress-appointment-booking'); ?></span>
                                        <span class="bpa-tags--item-body" v-for="item in bookingpress_company_placeholders" @click="bookingpress_insert_placeholder(item.value); bookingpress_insert_sms_placeholder(item.value); bookingpress_insert_whatsapp_placeholder(item.value);">{{ item.name }}</span>
                                    </div>
                                </div>
								<div class="bpa-gs__cb--item-tags-body" v-if="staffmember_module == 1 && bookingpress_active_email_notification != 'package_order'">
                                    <div>
                                        <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Staf Member', 'bookingpress-appointment-booking'); ?></span>
                                        <span class="bpa-tags--item-body" v-for="item in bookingpress_staff_member_placeholders" @click="bookingpress_insert_placeholder(item.value); bookingpress_insert_sms_placeholder(item.value); bookingpress_insert_whatsapp_placeholder(item.value); ">{{ item.name }}</span>
                                    </div>
                                </div>
                                <div class="bpa-gs__cb--item-tags-body" v-if="bookingpress_active_email_notification != 'package_order'">
                                    <div>
                                        <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Appointment', 'bookingpress-appointment-booking'); ?></span>
                                        <span class="bpa-tags--item-body" v-for="item in bookingpress_appointment_placeholders" @click="bookingpress_insert_placeholder(item.value); bookingpress_insert_sms_placeholder(item.value); bookingpress_insert_whatsapp_placeholder(item.value);">{{ item.name }}</span>
                                    </div>
                                </div>
								<div class="bpa-gs__cb--item-tags-body" v-if="bookingpress_custom_fields_placeholders != ''">
                                    <div>
                                        <span class="bpa-tags--item-sub-heading"><?php esc_html_e('Custom Fields', 'bookingpress-appointment-booking'); ?></span>
                                        <span class="bpa-tags--item-body" v-for="item in bookingpress_custom_fields_placeholders" @click="bookingpress_insert_placeholder(item.value); bookingpress_insert_sms_placeholder(item.value); bookingpress_insert_whatsapp_placeholder(item.value);">{{ item.name }}</span>
                                    </div>
                                </div>
								<?php
									do_action('bookingpress_notification_external_message_plachoders');
								?>
                            </div>
                        </el-col>
                    </el-row>
				</el-col>
			</el-row>
		</div>	
	</el-container>	
</el-main>

<el-dialog id="custom_notification_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-custom-notification" title="" :visible.sync="open_add_custom_notification_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display"> 
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<h1 class="bpa-page-heading" v-if="model_notification_type == 'edit'"><?php esc_html_e( 'Edit Notification', 'bookingpress-appointment-booking' ); ?></h1>
				<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Add Notification', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add_custom-notification">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form ref="custom_email_notification_form" :rules="rules" :model="custom_email_notification_form" label-position="top" @submit.native.prevent>
							<div class="bpa-form-body-row">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="bookingpress_custom_notification_name">										
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Notification name', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-input class="bpa-form-control" v-model="custom_email_notification_form.bookingpress_custom_notification_name" id="bookingpress_custom_notification_name" name="bookingpress_custom_notification_name" placeholder="<?php esc_html_e( 'Enter notification name', 'bookingpress-appointment-booking' ); ?>"></el-input>
										</el-form-item>
									</el-col>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="bookingpress_custom_notification_type">										
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Notification type', 'bookingpress-appointment-booking' ); ?></span>
											</template>

											<el-radio class="" @change="bookingpress_change_custom_notification_type()" v-model="custom_email_notification_form.bookingpress_custom_notification_type" label='action-trigger' border><?php esc_html_e( 'Action / Trigger notification', 'bookingpress-appointment-booking' ); ?></el-radio>

											<el-radio @change="bookingpress_change_custom_notification_type()" v-model="custom_email_notification_form.bookingpress_custom_notification_type" label='scheduled' border><?php esc_html_e( 'Scheduled notification', 'bookingpress-appointment-booking' ); ?></el-radio>

										</el-form-item>
									</el-col>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="custom_email_notification_form.bookingpress_custom_notification_type == 'scheduled'">
										<el-form-item prop="bookingpress_notification_scheduled_type">				
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Schedule', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-radio v-model="custom_email_notification_form.bookingpress_notification_scheduled_type" label='before' border><?php esc_html_e( 'Before', 'bookingpress-appointment-booking' ); ?></el-radio>
											<el-radio v-model="custom_email_notification_form.bookingpress_notification_scheduled_type" label='after' border><?php esc_html_e( 'After', 'bookingpress-appointment-booking' ); ?></el-radio>
										</el-form-item>	
									</el-col>
									<div class="bpa-cen-schedule-settings-row" v-if="custom_email_notification_form.bookingpress_custom_notification_type == 'scheduled'">
										<el-row :gutter="20" type="flex">
											<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="11">
												<el-form-item prop="bookingpress_notification_scheduled_type">				
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'Schedule', 'bookingpress-appointment-booking' ); ?></span>
													</template>
													<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" v-model="custom_email_notification_form.bookingpress_email_duration_val" id="bookingpress_email_duration_val" name="bookingpress_email_duration_val" step-strictly></el-input-number>
												</el-form-item>
											</el-col>
											<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="11" >
												<el-form-item prop="bookingpress_email_duration_unit">															
													<el-select class="bpa-form-control" v-model="custom_email_notification_form.bookingpress_email_duration_unit" popper-class="bpa-el-select--is-with-modal">
														<el-option key="h" label="<?php esc_html_e( 'Hours', 'bookingpress-appointment-booking' ); ?>" value="h"></el-option>
														<el-option key="d" label="<?php esc_html_e( 'Days', 'bookingpress-appointment-booking' ); ?>" value="d"></el-option>
														<el-option key="w" label="<?php esc_html_e( 'Weeks', 'bookingpress-appointment-booking' ); ?>" value="w"></el-option>
														<el-option key="m" label="<?php esc_html_e( 'Months', 'bookingpress-appointment-booking' ); ?>" value="m"></el-option>
													</el-select>
												</el-form-item>
											</el-col>
										</el-row>
									</div>
									<?php do_action('bookingpress_custom_notification_option_external_add'); ?>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="typeof custom_email_notification_form.bookingpress_custom_notification_option_external == 'undefined' || custom_email_notification_form.bookingpress_custom_notification_option_external == '' || custom_email_notification_form.bookingpress_custom_notification_type == 'action-trigger'">
										<el-form-item>
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-select class="bpa-form-control" v-model="custom_email_notification_form.bookingpress_notification_selected_service_name" multiple filterable collapse-tags placeholder="<?php esc_html_e( 'All Services', 'bookingpress-appointment-booking' ); ?>"
												popper-class="bpa-el-select--is-with-modal">
												<!-- <el-option key="<?php esc_html_e( 'Any Service', 'bookingpress-appointment-booking' ); ?>" label="<?php esc_html_e( 'Any Service', 'bookingpress-appointment-booking' ); ?>" value="any"></el-option> -->
												<el-option-group v-for="service_cat_data in bookingpress_notification_services_data" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
												<el-option v-for="service_data in service_cat_data.category_services" :key="service_data.service_id" :label="service_data.service_name" :value="service_data.service_id"></el-option>
												</el-option-group>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="typeof custom_email_notification_form.bookingpress_custom_notification_option_external == 'undefined' || custom_email_notification_form.bookingpress_custom_notification_option_external == '' || custom_email_notification_form.bookingpress_custom_notification_type == 'action-trigger'">
										<el-form-item prop="bookingpress_notification_event_action">
											<template #label>
												<span class="bpa-form-label" v-if="custom_email_notification_form.bookingpress_custom_notification_type == 'action-trigger'"><?php esc_html_e( 'Appointment status trigger', 'bookingpress-appointment-booking' ); ?></span>
												<span class="bpa-form-label" v-else><?php esc_html_e( 'Appointment Status', 'bookingpress-appointment-booking' ); ?></span>
											</template>											
											<el-select class="bpa-form-control" v-model="custom_email_notification_form.bookingpress_notification_event_action" popper-class="bpa-el-select--is-with-modal">
												<el-option key="appointment_approved" label="<?php esc_html_e( 'Approved', 'bookingpress-appointment-booking' ); ?>" value="appointment_approved"></el-option>
												<el-option key="appointment_pending" label="<?php esc_html_e( 'Pending', 'bookingpress-appointment-booking' ); ?>" value="appointment_pending"></el-option>
												<el-option key="appointment_canceled" label="<?php esc_html_e( 'Canceled', 'bookingpress-appointment-booking' ); ?>" value="appointment_canceled"></el-option>
												<el-option key="appointment_rejected" label="<?php esc_html_e( 'Rejected', 'bookingpress-appointment-booking' ); ?>" value="appointment_rejected"></el-option>
												<el-option v-if="custom_email_notification_form.bookingpress_custom_notification_type == 'action-trigger'" key="appointment_noshow" label="<?php esc_html_e( 'No-Show', 'bookingpress-appointment-booking' ); ?>" value="appointment_noshow"></el-option>
												<el-option v-if="custom_email_notification_form.bookingpress_custom_notification_type == 'action-trigger'" key="appointment_completed" label="<?php esc_html_e( 'Completed', 'bookingpress-appointment-booking' ); ?>" value="appointment_completed"></el-option>
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
			<el-button class="bpa-btn bpa-btn__small" @click="close_custom_notification_modal()"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="bookingpress_save_custom_email_notification_data()" v-if="model_notification_type == 'edit'"><?php esc_html_e( 'Update', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="bookingpress_save_custom_email_notification_data()" v-else><?php esc_html_e( 'Add', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>
<?php  do_action('bookingpress_add_manage_notification_view_bottom'); ?>