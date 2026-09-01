<?php
	global $bookingpress_ajaxurl, $bookingpress_common_date_format, $BookingPressPro, $bookingpress_global_options;

	$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
	$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
	$bookingpress_plural_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) : esc_html_e('Staff Members', 'bookingpress-appointment-booking');

?>
<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-non-scrollable-mob" :class="(bookingpress_staff_customize_view == 1 ) ? 'bpa-main-list-card__is-staff-custom-view':''" id="all-page-main-container">
	<el-row type="flex" class="bpa-mlc-head-wrap">
		<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
			<h1 class="bpa-page-heading"><?php esc_html_e( 'Manage Payments', 'bookingpress-appointment-booking' ); ?></h1>
		</el-col>
	</el-row>
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>
	<div id="bpa-main-container">
		<div class="bpa-table-filter">
			<el-row type="flex" :gutter="32">				
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
                    <span class="bpa-form-label"><?php esc_html_e('Transaction Date', 'bookingpress-appointment-booking'); ?></span>
                    <el-date-picker @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control bpa-form-control--date-range-picker" format="<?php echo esc_html($bookingpress_common_date_format); ?>" v-model="search_data.search_range" type="daterange" 
                    start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e( 'End Date', 'bookingpress-appointment-booking'); ?>" value-format="yyyy-MM-dd" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-date-range-picker-widget-wrapper" range-separator=" - " :picker-options="filter_pickerOptions" ></el-date-picker>
				</el-col>				
				<?php if ( ! $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) { ?>		
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6" v-if="is_staffmember_activated == 1">
					<span class="bpa-form-label"><?php esc_html_e('Select', 'bookingpress-appointment-booking'); ?><?php echo " ".esc_html($bookingpress_plural_staffmember_name); ?></span>
					<el-select class="bpa-form-control" v-model="search_data.search_staff_member" multiple filterable collapse-tags 
						placeholder="<?php esc_html_e('Select', 'bookingpress-appointment-booking'); ?><?php echo " ".esc_html($bookingpress_plural_staffmember_name); ?>"
						:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
						<el-option v-for="item in search_staffmember_data" :key="item.value" :label="item.text" :value="item.value"></el-option>
					</el-select>
				</el-col>
			<?php } ?>
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
					<span class="bpa-form-label"><?php esc_html_e( 'Customer Name', 'bookingpress-appointment-booking' ); ?></span>
					<el-select class="bpa-form-control" v-model="search_data.search_customer" multiple filterable collapse-tags placeholder="<?php esc_html_e( 'Start typing to fetch Customer', 'bookingpress-appointment-booking' ); ?>" remote reserve-keyword :remote-method="bookingpress_get_search_customer_list" :loading="boookingpress_loading" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
						<el-option v-for="item in search_customer_data" :key="item.value" :label="item.text" :value="item.value"></el-option>
					</el-select>
				</el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
					<span class="bpa-form-label"><?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?></span>
					<el-select class="bpa-form-control" v-model="search_data.search_service" multiple filterable collapse-tags 
						placeholder="<?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?>"
						:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">		
						<el-option-group v-for="item in search_services_data" :key="item.category_name" :label="item.category_name">
							<el-option v-for="cat_services in item.category_services" :key="cat_services.service_id" :label="cat_services.service_name" :value="cat_services.service_id"></el-option>
						</el-option-group>
					</el-select>
				</el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
					<span class="bpa-form-label"><?php esc_html_e( 'Payment Status', 'bookingpress-appointment-booking' ); ?></span>
					<el-select class="bpa-form-control" v-model="search_data.search_status" filterable 
						placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>"
						:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">		
						<el-option label="<?php esc_html_e('All', 'bookingpress-appointment-booking'); ?>" value="all"></el-option>
                        <el-option v-for="item_data in payment_status_data" :key="item_data.text" :label="item_data.text" :value="item_data.value"></el-option>
					</el-select>
				</el-col>				
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
					<div class="bpa-tf-btn-group">
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
							<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadPayments">
							<?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?>
						</el-button>											
						<el-button class="bpa-btn bpa-btn--secondary bpa-btn__medium bpa-btn--full-width" @click="Bookingpress_export_payment_data" v-if="bookingpress_export_payment == 1">
							<span class="material-icons-round">open_in_new</span><?php esc_html_e( 'Export', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</div>
				</el-col>
			</el-row>
		</div>
		<el-row type="flex" v-if="items.length == 0">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<div class="bpa-data-empty-view">
					<div class="bpa-ev-left-vector">
						<picture>
							<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
							<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
						</picture>
					</div>				
					<div class="bpa-ev-right-content">					
						<h4><?php esc_html_e( 'No Record Found!', 'bookingpress-appointment-booking' ); ?></h4>
					</div>				
				</div>
			</el-col>
		</el-row>

		<el-row v-if="items.length > 0">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<el-container class="bpa-table-container --bpa-is-payments-screen">
					<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
						<div class="bpa-back-loader"></div>
					</div>
					<div class="bpa-tc__wrapper" v-if="current_screen_size == 'desktop'">
						<el-table ref="multipleTable" :data="items" class="bpa-manage-payment-items" @selection-change="handleSelectionChange" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
							<el-table-column type="expand">
								<template slot-scope="scope">
									<div class="bpa-view-payment-card">
										<div class="bpa-vpc--head">
											<div class="bpa-vpc--head__left">
												<h2><?php esc_html_e('Payment Details', 'bookingpress-appointment-booking'); ?></h2>
											</div>
											<div class="bpa-hw-right-btn-group bpa-vpc--head__right" v-if="scope.row.payment_refund_status == 1">
												<el-button class="bpa-btn bpa-btn__refund" @click="bookingpress_open_refund_model(event,scope.row.appointment_id,scope.row.payment_log_id,scope.row.appointment_currency_symbol,scope.row.payment_partial_refund)">
													<svg viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
														<path d="M12.1471 5.67946H15.6684V1.96777" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M16.56 9.18573C16.56 13.2612 13.2555 16.5639 9.18001 16.5639C5.1045 16.5639 1.79999 13.2594 1.79999 9.18384C1.79999 5.10834 5.1045 1.80005 9.18001 1.80005C11.9869 1.80005 14.4299 3.36654 15.6759 5.67009" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M7.54754 10.7106C7.54754 11.6117 8.27894 12.3431 9.18 12.3431C10.0811 12.3431 10.8125 11.6117 10.8125 10.7106C10.8125 9.80957 10.0811 9.07816 9.17812 9.07816C8.27517 9.07816 7.54565 8.34676 7.54565 7.4457C7.54565 6.54464 8.27706 5.81323 9.18 5.81323C10.0811 5.81323 10.8144 6.54464 10.8144 7.4457" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M9.17999 13.6685V12.3452" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M9.17999 5.81135V4.48804" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
													</svg> 
													<?php esc_html_e( 'Refund', 'bookingpress-appointment-booking' ); ?>
												</el-button>
												<?php 
													do_action('bookingpress_add_dynamic_buttons_for_view_payments');
												?>
											</div>
										</div>
										<div class="bpa-vpc--body">
											<div class="bpa-vpc__customer-summary-wrapper">
												<div class="bpa-vpc-csw__item">
													<div class="bpa-csw--customer-info">
														<div class="bpa-ci--avatar">
															<img :src="scope.row.customer_avatar" alt="">
														</div>
														<div class="bpa-ci--body">
															<h4 v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</h4>
															<p>{{ scope.row.customer_email }}</p>
														</div>
													</div>
												</div>
												<div class="bpa-vpc-csw__item">
													<h4><?php esc_html_e('Mode', 'bookingpress-appointment-booking'); ?></h4>
													<p>{{ scope.row.payment_gateway_label }}</p>
												</div>
												<div class="bpa-vpc-csw__item">
													<h4><?php esc_html_e('Transaction ID', 'bookingpress-appointment-booking'); ?></h4>
													<p>{{ scope.row.transaction_id }}</p>
												</div>
												<div class="bpa-vpc-csw__item">
													<h4><?php esc_html_e('Status', 'bookingpress-appointment-booking'); ?></h4>
													<p :class="[(scope.row.payment_status == '1') ? 'bpa-cl-pt-main-green' : '', (scope.row.payment_status == '2') ? 'bpa-cl-sc-warning' : '', (scope.row.payment_status == '4') ? 'bpa-cl-pt-blue' : '', (scope.row.payment_status == '3') ? 'bpa-cl-danger' : '']">{{ scope.row.payment_status_label }}</p>
												</div>
											</div>
											<div v-if="((scope.row.is_package_purchase == 'undefined') || (scope.row.is_package_purchase != 'undefined' && scope.row.is_package_purchase != '1')) && ((scope.row.is_gift_card_purchase == 'undefined') || (scope.row.is_gift_card_purchase != 'undefined' && scope.row.is_gift_card_purchase != '1'))" class="bpa-vpc__appointment-details">
												<h4 class="bpa-vac__sec-heading"><?php esc_html_e('Appointment Details', 'bookingpress-appointment-booking'); ?></h4>
												<div class="bpa-vac-ap--items">
													<div class="bpa-ap-item__head">
														<div class="bpa-ih--item">
															<h4><?php esc_html_e('Service', 'bookingpress-appointment-booking'); ?></h4>
														</div>
														<div class="bpa-ih--item">
															<h4><?php esc_html_e('Date', 'bookingpress-appointment-booking'); ?></h4>
														</div>
														<div class="bpa-ih--item">
															<h4><?php esc_html_e('Time', 'bookingpress-appointment-booking'); ?></h4>
														</div>
														<div class="bpa-ih--item">
															<h4><?php esc_html_e('Price', 'bookingpress-appointment-booking'); ?></h4>
														</div>
														<div class="bpa-ih--item" v-show="scope.row.is_deposit_enable == '1'">
															<h4><?php esc_html_e('Deposit', 'bookingpress-appointment-booking'); ?></h4>
														</div>
														<div class="bpa-ih--item" v-show="is_staffmember_activated == '1'">
															<h4><?php echo esc_html($bookingpress_singular_staffmember_name); ?></h4>
														</div>
													</div>
													<div class="bpa-ap-item__body">
														<div class="bpa-ib--item-card" v-for="appointment_details in scope.row.appointment_details">
															<div class="bpa-ib--item">
																<p>{{ appointment_details.bookingpress_service_name }}</p>
																<div class="bpa-ap__service-extras" v-if="appointment_details.extra_service_details.length > 0">
																	<p v-for="appointment_extra_details in appointment_details.extra_service_details">
																		{{ appointment_extra_details.selected_qty }} x {{ appointment_extra_details.extra_name }}
																	</p>
																</div>
																<div class="bpa-ap__service-extras bpa-ap__pack-title" v-if="appointment_details.bookingpress_purchase_type == 3 && appointment_details.bookingpress_package_discount_amount_with_currency != 'undefined' && appointment_details.bookingpress_package_discount_amount_with_currency != 0 && appointment_details.bookingpress_applied_package_data_arr.bookingpress_package_name != 'undefined'">
																	<p>{{ appointment_details.bookingpress_applied_package_data_arr.bookingpress_package_name }}</p> 
																</div>
															</div>
															<div class="bpa-ib--item">
																<p>{{ appointment_details.bookingpress_appointment_date }}</p>
															</div>
															<div class="bpa-ib--item">
																<p v-if="scope.row.appointment_start_time != ''">{{ appointment_details.bookingpress_appointment_time }} <?php esc_html_e('to', 'bookingpress-appointment-booking'); ?> {{ appointment_details.bookingpress_appointment_end_time }}</p>
															</div>
															<div class="bpa-ib--item">
																<div class="bpa-ib__amount-row">
																	<div class="bpa-ar__body">
																		<p>{{ appointment_details.bookingpress_service_price_with_currency }}</p>
																		<div class="bpa-ap__service-extras-price" v-if="appointment_details.extra_service_details.length > 0">
																			<p v-for="appointment_extra_details in appointment_details.extra_service_details">
																				{{ appointment_extra_details.extra_service_price_with_currency }}
																			</p>
																		</div>
																		<div class="bpa-ap__service-extras-price bpa-ap__pack-price" v-if="appointment_details.bookingpress_purchase_type == 3 && appointment_details.bookingpress_package_discount_amount_with_currency != 'undefined' && appointment_details.bookingpress_package_discount_amount_with_currency != 0">
																			<p> - {{ appointment_details.bookingpress_package_discount_amount_with_currency }}</p>
																		</div>																		
																	</div>
																	<div class="bpa-ar__icons">
																		<el-tooltip content="<?php esc_html_e('Total Persons', 'bookingpress-appointment-booking'); ?>" placement="top">
																			<p v-if="appointment_details.bookingpress_selected_extra_members > 1">
																				<span class="material-icons-round">account_circle</span>
																				{{ appointment_details.bookingpress_selected_extra_members }} 
																			</p>
																		</el-tooltip>
																	</div>
																</div>
															</div>
															<div class="bpa-ib--item" v-if="appointment_details.is_deposit_applied == '1'">
																<p>{{ appointment_details.bookingpress_deposit_amount_with_currency }}</p>
															</div>
															<div class="bpa-ib--item" v-show="is_staffmember_activated == '1'" >
																<div class="bpa-ib-item__staff-info" v-show="appointment_details.bookingpress_staff_member_id != '0'">
																	<img :src="appointment_details.staff_avatar_url" alt="">
																	<p>{{ appointment_details.bookingpress_staff_first_name }} {{ appointment_details.bookingpress_staff_last_name }}</p>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="bpa-vpc__payment-summary-wapper">
												<h4 class="bpa-vac__sec-heading"><?php esc_html_e('Payment Summary', 'bookingpress-appointment-booking'); ?></h4>
												<div class="bpa-psw--body">
													<div class="bpa-psw--body-row">
														<div class="bpa-psw__item">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Subtotal', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>{{ scope.row.subtotal_amount_with_currency }}</p>
															</div>
														</div>
														<?php do_action('bookingpress_payment_page_after_subtotal'); ?>
														<div class="bpa-psw__item" v-if="scope.row.is_deposit_enable == '1'">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Deposit', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>{{ scope.row.payment_amount }}</p>
															</div>
														</div>
														<div class="bpa-psw__item" v-if="scope.row.is_deposit_enable == '1' && scope.row.payment_status == '4'">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Due Amount', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount bookingpress_due_amount">
																<p>{{ scope.row.due_amount_with_currency }}</p>
															</div>
														</div>
													</div>
													<div class="bpa-psw--body-row bpa-psw--body-row__tax-item" :class="( (scope.row.price_display_setting != 'include_taxes' || (scope.row.price_display_setting == 'include_taxes' && scope.row.display_tax_order_summary == 'true' ) ) && scope.row.applied_coupon_code == '') ? 'bpa-psw--body-row__tax-item-excluded' : ''" v-if="scope.row.tax_amount != ''">
														<div class="bpa-psw__item" v-if="(scope.row.tax_amount != '') && (scope.row.price_display_setting != 'include_taxes' || (scope.row.price_display_setting == 'include_taxes' && scope.row.display_tax_order_summary == 'true' ))">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Tax', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>+{{ scope.row.tax_amount_with_currency }}</p>
															</div>
														</div>
													</div>
													<div class="bpa-psw--body-row" v-if="scope.row.applied_coupon_code != ''">													
														<div class="bpa-psw__item --bpa-is-coupon-item" v-if="scope.row.applied_coupon_code != ''">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Coupon', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>-{{ scope.row.coupon_discount_amount_with_currency }}</p>
															</div>
														</div>
													</div>
													<?php do_action('bookingpress_modify_payment_managepayment_section') ?>
													<div class="bpa-psw--body-row --bpa-is-total-row">
														<div class="bpa-psw__item">
															<div class="bpa-psw__item-title">
																<p>
																	<?php esc_html_e('Balance Amount', 'bookingpress-appointment-booking'); ?>
																	<span class="bpa-psw-ba__included-tax-label" v-if="scope.row.price_display_setting == 'include_taxes'">{{ scope.row.included_tax_label }}</span>
																</p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>{{ scope.row.total_amount_with_currency }}</p>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</template>
							</el-table-column>
							<el-table-column type="selection"></el-table-column>
							<el-table-column prop="payment_date" min-width="80" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
							<el-table-column prop="payment_customer" min-width="100" label="<?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
							<?php if ( ! $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) { ?>
							<el-table-column prop="staff_member_name" min-width="100" label="<?php echo esc_html($bookingpress_singular_staffmember_name); ?>" sortable v-if="is_staffmember_activated == 1"></el-table-column>
							<?php } ?>
							<el-table-column prop="payment_service" min-width="100" label="<?php 
							$bookingpress_payment_transaction_item_label = esc_html__( 'Service', 'bookingpress-appointment-booking' ); 
							$bookingpress_payment_transaction_item_label = apply_filters('bookingpress_payment_transaction_item_label',$bookingpress_payment_transaction_item_label);
							echo esc_html($bookingpress_payment_transaction_item_label);														 
							?>" sortable></el-table-column>
							<el-table-column prop="payment_gateway" min-width="70" label="<?php esc_html_e( 'Method', 'bookingpress-appointment-booking' ); ?>">
								<template slot-scope="scope">
									<div class="bpa-mpg__body">
										<p> {{scope.row.payment_gateway_label}}</p>
										<span v-if="scope.row.payment_gateway == 'manual'">(<?php esc_html_e('paid by admin', 'bookingpress-appointment-booking'); ?>)</span>
									</div>
								</template>
							</el-table-column>
							<el-table-column prop="payment_status" min-width="80" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
								<template slot-scope="scope">					
									<div class="bpa-table-status-dropdown-wrapper" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-loader-active' : ''" v-if="bookingpress_edit_payment == 1">
										<div class="bpa-tsd--loader" v-if="scope.row.change_status_loader == 1" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-active' : ''">
											<div class="bpa-btn--loader__circles">
												<div></div>
												<div></div>
												<div></div>
											</div>
										</div>
										<el-select class="bpa-form-control" :class="((scope.row.payment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.payment_status == '3' ? 'bpa-appointment-status--rejected' : '') || (scope.row.payment_status == '4' ? 'bpa-appointment-status--approved' : '') || (scope.row.payment_status == '1' ? 'bpa-appointment-status--completed' : '') || (scope.row.payment_status == '5' ? 'bpa-appointment-status--refund-partial' : '')" v-model="scope.row.payment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-payment-status-dropdown-popper" @change="bookingpress_change_status(scope.row.payment_log_id, $event)">
											<el-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
												<el-option v-for="item in payment_status_data" :key="item.value" :label="item.text" :value="item.value"></el-option>
											</el-option-group>
										</el-select>
									</div>
									<el-tag class="bpa-front-pill " :class="((scope.row.payment_status == '2') ? '--warning' : '') || (scope.row.payment_status == '3' ? '--rejected' : '') || (scope.row.payment_status == '4' ? '--approved' : '') || (scope.row.payment_status == '1' ? '--completed' : '')" v-else>{{ scope.row.payment_status_label }}</el-tag>
								</template>
							</el-table-column>
							<el-table-column prop="payment_amount" min-width="100" label="<?php esc_html_e( 'Amount', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="payment_numberic_amount">
								<template slot-scope="scope">
									<div class="bpa-mpi__amount-row">
										<div class="bpa-mpi__ar-body">
											<!-- <span class="bpa-mpi__amount" v-if="scope.row.is_deposit_enable == '1' && scope.row.payment_status == '4'">{{ scope.row.deposit_amount_with_currency }}</span> -->
											<span class="bpa-mpi__amount">{{ scope.row.payment_amount }}</span>
											<span v-if="scope.row.is_deposit_enable == 1 && scope.row.payment_status == '4'" class="bpa-is-deposit-payment-val"><?php esc_html_e('of', 'bookingpress-appointment-booking'); ?> {{ scope.row.total_amount_with_currency }}</span>
										</div>
										<div class="bpa-mpi__ar-icons">
											<?php do_action('bookingpress_backend_payment_list_type_icons'); ?>
											<el-tooltip content="<?php esc_html_e('Cart Transaction', 'bookingpress-appointment-booking'); ?>" placement="top" v-if="scope.row.is_cart == '1'">
												<span class="material-icons-round">shopping_cart</span>
											</el-tooltip>
											<el-tooltip content="<?php esc_html_e('Deposit', 'bookingpress-appointment-booking'); ?>" placement="top" v-if="scope.row.is_deposit_enable == '1'">
												<span class="bpa-apc__deposit-icon">
													<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M16.9596 12.2237C16.8902 12.0273 16.746 11.8662 16.5583 11.7756C16.3706 11.685 16.1548 11.6723 15.9578 11.7402L13.7872 12.4116C13.2376 12.9125 13.0288 12.7838 9.00068 12.7838C8.90842 12.7838 8.81994 12.7471 8.75471 12.6819C8.68947 12.6167 8.65282 12.5282 8.65282 12.4359C8.65282 12.3437 8.68947 12.2552 8.75471 12.1899C8.81994 12.1247 8.90842 12.0881 9.00068 12.0881C13.1749 12.0881 13.0323 12.1681 13.3384 11.862C13.4551 11.7331 13.5206 11.5661 13.5228 11.3923C13.5228 11.2078 13.4495 11.0309 13.319 10.9004C13.1886 10.7699 13.0116 10.6966 12.8271 10.6966H9.10504C8.62152 10.6966 8.21801 10.0009 6.80919 10.0009H4.47856V13.6256L4.92729 13.8795C6.20362 14.6153 7.63128 15.0496 9.10115 15.149C10.571 15.2485 12.0442 15.0106 13.408 14.4535L16.5387 13.1908C16.7162 13.1104 16.8576 12.967 16.9354 12.7883C17.0132 12.6096 17.0218 12.4084 16.9596 12.2237ZM1 14.523H3.78285V9.30521H1V14.523ZM2.0714 12.9994C2.09103 12.9518 2.12099 12.9092 2.1591 12.8746C2.19722 12.84 2.24255 12.8142 2.29181 12.7993C2.34107 12.7843 2.39304 12.7805 2.44398 12.788C2.49491 12.7956 2.54353 12.8143 2.58633 12.8429C2.62913 12.8716 2.66504 12.9093 2.69147 12.9535C2.71791 12.9977 2.7342 13.0472 2.73919 13.0984C2.74417 13.1497 2.73771 13.2014 2.72028 13.2499C2.70285 13.2983 2.67489 13.3423 2.6384 13.3786C2.58145 13.4353 2.50661 13.4705 2.42662 13.4783C2.34663 13.4861 2.26641 13.4659 2.1996 13.4213C2.13279 13.3766 2.08351 13.3102 2.06014 13.2333C2.03677 13.1564 2.04074 13.0737 2.0714 12.9994ZM11.4357 8.95736C12.1237 8.95736 12.7962 8.75334 13.3683 8.37112C13.9403 7.98889 14.3862 7.44561 14.6494 6.80999C14.9127 6.17437 14.9816 5.47494 14.8474 4.80017C14.7132 4.1254 14.3819 3.50558 13.8954 3.01909C13.4089 2.53261 12.7891 2.20131 12.1143 2.06709C11.4395 1.93286 10.7401 2.00175 10.1045 2.26504C9.46886 2.52832 8.92558 2.97417 8.54336 3.54622C8.16113 4.11827 7.95711 4.79081 7.95711 5.4788C7.95711 6.40137 8.3236 7.28616 8.97596 7.93851C9.62831 8.59087 10.5131 8.95736 11.4357 8.95736ZM11.7835 5.82666H11.0878C10.811 5.82666 10.5456 5.71671 10.3499 5.521C10.1542 5.3253 10.0442 5.05986 10.0442 4.78309C10.0442 4.50632 10.1542 4.24088 10.3499 4.04518C10.5456 3.84947 10.811 3.73952 11.0878 3.73952V3.39167C11.0878 3.29941 11.1245 3.21093 11.1897 3.1457C11.2549 3.08046 11.3434 3.04381 11.4357 3.04381C11.5279 3.04381 11.6164 3.08046 11.6816 3.1457C11.7469 3.21093 11.7835 3.29941 11.7835 3.39167V3.73952H12.4792C12.5715 3.73952 12.66 3.77617 12.7252 3.84141C12.7904 3.90664 12.8271 3.99512 12.8271 4.08738C12.8271 4.17964 12.7904 4.26812 12.7252 4.33335C12.66 4.39859 12.5715 4.43524 12.4792 4.43524H11.0878C10.9956 4.43524 10.9071 4.47188 10.8418 4.53712C10.7766 4.60236 10.74 4.69083 10.74 4.78309C10.74 4.87535 10.7766 4.96383 10.8418 5.02906C10.9071 5.0943 10.9956 5.13095 11.0878 5.13095H11.7835C12.0603 5.13095 12.3257 5.24089 12.5214 5.4366C12.7171 5.63231 12.8271 5.89774 12.8271 6.17451C12.8271 6.45128 12.7171 6.71672 12.5214 6.91243C12.3257 7.10813 12.0603 7.21808 11.7835 7.21808V7.56594C11.7835 7.65819 11.7469 7.74667 11.6816 7.81191C11.6164 7.87714 11.5279 7.91379 11.4357 7.91379C11.3434 7.91379 11.2549 7.87714 11.1897 7.81191C11.1245 7.74667 11.0878 7.65819 11.0878 7.56594V7.21808H10.3921C10.2998 7.21808 10.2114 7.18143 10.1461 7.1162C10.0809 7.05096 10.0442 6.96248 10.0442 6.87022C10.0442 6.77797 10.0809 6.68949 10.1461 6.62425C10.2114 6.55902 10.2998 6.52237 10.3921 6.52237H11.7835C11.8758 6.52237 11.9643 6.48572 12.0295 6.42049C12.0947 6.35525 12.1314 6.26677 12.1314 6.17451C12.1314 6.08226 12.0947 5.99378 12.0295 5.92854C11.9643 5.86331 11.8758 5.82666 11.7835 5.82666Z" />
													</svg>
												</span>
											</el-tooltip>
										</div>
									</div>
								</template>
							</el-table-column>
							<el-table-column prop="appointment_date" label="<?php esc_html_e( 'Appointment On', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="appointment_date">
								<template slot-scope="scope">
									<label>{{ scope.row.appointment_date }}</label>								
										<div class="bpa-table-actions-wrap" v-if="bookingpress_delete_payment == 1 || ( bookingpress_edit_payment == 1 && (scope.row.payment_status == '4') || (scope.row.appointment_status == '2' && scope.row.payment_status == '2') || (scope.row.is_cart == '1' && scope.row.payment_status == '2'))">
											<div class="bpa-table-actions">									
												<el-tooltip effect="dark" content="" placement="top" open-delay="300" v-if="bookingpress_edit_payment == 1 && (scope.row.payment_status == '4') || (scope.row.appointment_status == '2' && scope.row.payment_status == '2') || (scope.row.is_cart == '1' && scope.row.payment_status == '2')">
													<div slot="content">
														<span><?php esc_html_e( 'Send Payment Link', 'bookingpress-appointment-booking' ); ?></span>
													</div>
													<el-popover placement="bottom" trigger="click" popper-class="bpa-dialog bpa-dailog__small bpa-dialog--add-category bpa-complete-payment-dialog">
														<div class="bpa-dialog-heading">
															<el-row type="flex">
																<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																	<h1 class="bpa-page-heading"><?php esc_html_e( 'Share Payment Link', 'bookingpress-appointment-booking' ); ?></h1>
																</el-col>
															</el-row>
														</div>
														<div class="bpa-dialog-body">
															<el-container class="bpa-grid-list-container bpa-add-categpry-container">
																<div class="bpa-form-row">				
																	<el-row>
																		<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																			<el-form label-position="top">
																				<div class="bpa-form-body-row bpa-dsu__checkbox-row">
																					<el-row>
																						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																							<el-form-item>
																								<template #label>
																									<span class="bpa-form-label"><?php echo esc_html__('Share With', 'bookingpress-appointment-booking'); ?></span>
																								</template>
																								<el-checkbox-group v-model="bookingpress_complete_payment_link_send_options">
																									<el-checkbox class="bpa-front-label bpa-custom-checkbox--is-label" label="email"><?php esc_html_e( 'Through Email', 'bookingpress-appointment-booking' ); ?></el-checkbox>
																									<?php
																										do_action('bookingpress_add_more_complete_payment_link_option');
																									?>
																								</el-checkbox-group>
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
																<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" @click="bpa_send_complete_payment_link(scope.row.payment_log_id)" :class="(is_send_button_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_send_button_disabled">
																	<span class="bpa-btn__label"><?php esc_html_e( 'Send Link', 'bookingpress-appointment-booking' ); ?></span>
																	<div class="bpa-btn--loader__circles">				    
																		<div></div>
																		<div></div>
																		<div></div>
																	</div>
																</el-button>
															</div>
														</div>
														<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __secondary">
															<span class="material-icons-round">link</span>
														</el-button>
													</el-popover>
												</el-tooltip>
	
												<el-tooltip effect="dark" content="" placement="top" open-delay="300">
													<div slot="content">
														<span><?php esc_html_e( 'Approve', 'bookingpress-appointment-booking' ); ?></span>
													</div>
													<el-popconfirm 
														confirm-button-text='<?php esc_html_e( 'Approve', 'bookingpress-appointment-booking' ); ?>' 
														cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
														icon="false" 
														title="<?php esc_html_e( 'Are you sure you want to approve the Payment (Appointment will automatically be approved)?', 'bookingpress-appointment-booking' ); ?>" 
														@confirm="bpa_approve_appointment(scope.row.payment_log_id)" 
														confirm-button-type="bpa-btn bpa-btn__small bpa-btn--primary" 
														cancel-button-type="bpa-btn bpa-btn__small"
														v-if="scope.row.appointment_status == '2'">
														<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __secondary">
															<span class="material-icons-round">done</span>
														</el-button>
													</el-popconfirm>
												</el-tooltip>
												<el-tooltip effect="dark" content="" placement="top" open-delay="300" v-if="bookingpress_delete_payment == 1">
													<div slot="content">
														<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
													</div>
													<el-popconfirm 
														confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
														cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
														icon="false" 
														title="<?php esc_html_e( 'Are you sure you want to delete this payment transaction?', 'bookingpress-appointment-booking' ); ?>" 
														@confirm="deletePaymentLog(scope.row.payment_log_id)" 
														confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
														cancel-button-type="bpa-btn bpa-btn__small">
														<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
															<span class="material-icons-round">delete</span>
														</el-button>
													</el-popconfirm>
												</el-tooltip>
												<?php											
												$bookingpress_add_dynamic_action_btn_content = "";
												$bookingpress_add_dynamic_action_btn_content = apply_filters( 'bookingpress_payment_list_add_action_button', $bookingpress_add_dynamic_action_btn_content);
												echo $bookingpress_add_dynamic_action_btn_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												?>
											</div>
										</div>
								</template>
							</el-table-column>
						</el-table>
					</div>
					<div class="bpa-tc__wrapper" v-if="current_screen_size == 'tablet'">
						<el-table ref="multipleTable" :data="items" class="bpa-manage-payment-items" @selection-change="handleSelectionChange" @row-click="bookingpress_full_row_clickable" @expand-change="bookingpress_row_expand">
							<el-table-column type="expand">
								<template slot-scope="scope">
									<div class="bpa-view-payment-card">
										<div class="bpa-vpc--head">
											<div class="bpa-vpc--head__left">
												<h2><?php esc_html_e('Payment Details', 'bookingpress-appointment-booking'); ?></h2>
											</div>
											<div class="bpa-hw-right-btn-group bpa-vpc--head__right" v-if="scope.row.payment_refund_status == 1">
												<el-button class="bpa-btn bpa-btn__refund" @click="bookingpress_open_refund_model(event,scope.row.appointment_id,scope.row.payment_log_id,scope.row.appointment_currency_symbol,scope.row.payment_partial_refund)">
													<svg viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
														<path d="M12.1471 5.67946H15.6684V1.96777" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M16.56 9.18573C16.56 13.2612 13.2555 16.5639 9.18001 16.5639C5.1045 16.5639 1.79999 13.2594 1.79999 9.18384C1.79999 5.10834 5.1045 1.80005 9.18001 1.80005C11.9869 1.80005 14.4299 3.36654 15.6759 5.67009" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M7.54754 10.7106C7.54754 11.6117 8.27894 12.3431 9.18 12.3431C10.0811 12.3431 10.8125 11.6117 10.8125 10.7106C10.8125 9.80957 10.0811 9.07816 9.17812 9.07816C8.27517 9.07816 7.54565 8.34676 7.54565 7.4457C7.54565 6.54464 8.27706 5.81323 9.18 5.81323C10.0811 5.81323 10.8144 6.54464 10.8144 7.4457" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M9.17999 13.6685V12.3452" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M9.17999 5.81135V4.48804" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
													</svg> 
													<?php esc_html_e( 'Refund', 'bookingpress-appointment-booking' ); ?>
												</el-button>
												<?php 
													do_action('bookingpress_add_dynamic_buttons_for_view_payments');
												?>
											</div>
										</div>
										<div class="bpa-vpc--body">
											<div class="bpa-vpc__customer-summary-wrapper">
												<div class="bpa-vpc-csw__item">
													<div class="bpa-csw--customer-info">
														<div class="bpa-ci--avatar">
															<img :src="scope.row.customer_avatar" alt="">
														</div>
														<div class="bpa-ci--body">
															<h4 v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</h4>
															<p>{{ scope.row.customer_email }}</p>
														</div>
													</div>
												</div>
												<div class="bpa-vpc-csw__item">
													<h4><?php esc_html_e('Mode', 'bookingpress-appointment-booking'); ?></h4>
													<p>{{ scope.row.payment_gateway_label }}</p>
												</div>
												<div class="bpa-vpc-csw__item">
													<h4><?php esc_html_e('Transaction ID', 'bookingpress-appointment-booking'); ?></h4>
													<p>{{ scope.row.transaction_id }}</p>
												</div>
												<div class="bpa-vpc-csw__item">
													<h4><?php esc_html_e('Status', 'bookingpress-appointment-booking'); ?></h4>
													<p :class="[(scope.row.payment_status == '1') ? 'bpa-cl-pt-main-green' : '', (scope.row.payment_status == '2') ? 'bpa-cl-sc-warning' : '', (scope.row.payment_status == '4') ? 'bpa-cl-pt-blue' : '', (scope.row.payment_status == '3') ? 'bpa-cl-danger' : '']">{{ scope.row.payment_status_label }}</p>
												</div>
											</div>
											<div v-if="(scope.row.is_package_purchase == 'undefined') || (scope.row.is_package_purchase != 'undefined' && scope.row.is_package_purchase != '1')" class="bpa-vpc__appointment-details">
												<h4 class="bpa-vac__sec-heading"><?php esc_html_e('Appointment Details', 'bookingpress-appointment-booking'); ?></h4>
												<div class="bpa-vac-ap--items">
													<div class="bpa-ap-item__head">
														<div class="bpa-ih--item">
															<h4><?php esc_html_e('Service', 'bookingpress-appointment-booking'); ?></h4>
														</div>
														<div class="bpa-ih--item">
															<h4><?php esc_html_e('Date', 'bookingpress-appointment-booking'); ?></h4>
														</div>
														<div class="bpa-ih--item">
															<h4><?php esc_html_e('Time', 'bookingpress-appointment-booking'); ?></h4>
														</div>
														<div class="bpa-ih--item">
															<h4><?php esc_html_e('Price', 'bookingpress-appointment-booking'); ?></h4>
														</div>
														<div class="bpa-ih--item" v-show="scope.row.is_deposit_enable == '1'">
															<h4><?php esc_html_e('Deposit', 'bookingpress-appointment-booking'); ?></h4>
														</div>
														<div class="bpa-ih--item" v-show="is_staffmember_activated == '1'">
															<h4><?php echo esc_html($bookingpress_singular_staffmember_name); ?></h4>
														</div>
													</div>
													<div class="bpa-ap-item__body">
														<div class="bpa-ib--item-card" v-for="appointment_details in scope.row.appointment_details">
															<div class="bpa-ib--item">
																<p>{{ appointment_details.bookingpress_service_name }}</p>
																<div class="bpa-ap__service-extras" v-if="appointment_details.extra_service_details.length > 0">
																	<p v-for="appointment_extra_details in appointment_details.extra_service_details">
																		x {{ appointment_extra_details.selected_qty }} {{ appointment_extra_details.extra_name }}
																	</p>
																</div>
																<div class="bpa-ap__service-extras bpa-ap__pack-title" v-if="appointment_details.bookingpress_purchase_type == 3 && appointment_details.bookingpress_package_discount_amount_with_currency != 'undefined' && appointment_details.bookingpress_package_discount_amount_with_currency != 0 && appointment_details.bookingpress_applied_package_data_arr.bookingpress_package_name != 'undefined'">
																	<p>{{ appointment_details.bookingpress_applied_package_data_arr.bookingpress_package_name }}</p> 
																</div>																
															</div>
															<div class="bpa-ib--item">
																<p>{{ appointment_details.bookingpress_appointment_date }}</p>
															</div>
															<div class="bpa-ib--item">
																<p v-if="scope.row.appointment_start_time != ''">{{ appointment_details.bookingpress_appointment_time }} <?php esc_html_e('to', 'bookingpress-appointment-booking'); ?> {{ appointment_details.bookingpress_appointment_end_time }}</p>
															</div>
															<div class="bpa-ib--item">
																<div class="bpa-ib__amount-row">
																	<div class="bpa-ar__body">
																		<p>{{ appointment_details.bookingpress_service_price_with_currency }}</p>
																		<div class="bpa-ap__service-extras-price" v-if="appointment_details.extra_service_details.length > 0">
																			<p v-for="appointment_extra_details in appointment_details.extra_service_details">
																				{{ appointment_extra_details.extra_service_price_with_currency }}
																			</p>
																		</div>
																		<div class="bpa-ap__service-extras-price bpa-ap__pack-price" v-if="appointment_details.bookingpress_purchase_type == 3 && appointment_details.bookingpress_package_discount_amount_with_currency != 'undefined' && appointment_details.bookingpress_package_discount_amount_with_currency != 0">
																			<p> - {{ appointment_details.bookingpress_package_discount_amount_with_currency }}</p>
																		</div>																			
																	</div>
																	<div class="bpa-ar__icons">
																		<el-tooltip content="<?php esc_html_e('Total Persons', 'bookingpress-appointment-booking'); ?>" placement="top">
																			<p v-if="appointment_details.bookingpress_selected_extra_members > 1">
																				<span class="material-icons-round">account_circle</span>
																				{{ appointment_details.bookingpress_selected_extra_members }} 
																			</p>
																		</el-tooltip>
																	</div>
																</div>
															</div>
															<div class="bpa-ib--item" v-if="appointment_details.is_deposit_applied == '1'">
																<p>{{ appointment_details.bookingpress_deposit_amount_with_currency }}</p>
															</div>
															<div class="bpa-ib--item" v-show="is_staffmember_activated == '1'">
																<div class="bpa-ib-item__staff-info" v-show="appointment_details.bookingpress_staff_member_id != '0'">
																	<img :src="appointment_details.staff_avatar_url" alt="">
																	<p>{{ appointment_details.bookingpress_staff_first_name }} {{ appointment_details.bookingpress_staff_last_name }}</p>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="bpa-vpc__payment-summary-wapper">
												<h4 class="bpa-vac__sec-heading"><?php esc_html_e('Payment Summary', 'bookingpress-appointment-booking'); ?></h4>
												<div class="bpa-psw--body">
													<div class="bpa-psw--body-row">
														<div class="bpa-psw__item">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Subtotal', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>{{ scope.row.subtotal_amount_with_currency }}</p>
															</div>
														</div>														
														<?php do_action('bookingpress_payment_page_after_subtotal'); ?>
														<div class="bpa-psw__item" v-if="scope.row.is_deposit_enable == '1'">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Deposit', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>{{ scope.row.payment_amount }}</p>
															</div>
														</div>
														<div class="bpa-psw__item" v-if="scope.row.is_deposit_enable == '1' && scope.row.payment_status == '4'">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Due Amount', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount bookingpress_due_amount">
																<p>{{ scope.row.due_amount_with_currency }}</p>
															</div>
														</div>
													</div>
													<div class="bpa-psw--body-row bpa-psw--body-row__tax-item" :class="( (scope.row.price_display_setting != 'include_taxes' || (scope.row.price_display_setting == 'include_taxes' && scope.row.display_tax_order_summary == 'true' ) ) && scope.row.applied_coupon_code == '') ? 'bpa-psw--body-row__tax-item-excluded' : ''" v-if="scope.row.tax_amount != ''">
														<div class="bpa-psw__item" v-if="(scope.row.tax_amount != '') && (scope.row.price_display_setting != 'include_taxes' || (scope.row.price_display_setting == 'include_taxes' && scope.row.display_tax_order_summary == 'true' ))">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Tax', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>+{{ scope.row.tax_amount_with_currency }}</p>
															</div>
														</div>
													</div>
													<div class="bpa-psw--body-row" v-if="scope.row.applied_coupon_code != ''">													
														<div class="bpa-psw__item --bpa-is-coupon-item" v-if="scope.row.applied_coupon_code != ''">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Coupon', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>-{{ scope.row.coupon_discount_amount_with_currency }}</p>
															</div>
														</div>
													</div>
													<?php do_action('bookingpress_modify_payment_managepayment_section') ?>
													<div class="bpa-psw--body-row --bpa-is-total-row">
														<div class="bpa-psw__item">
															<div class="bpa-psw__item-title">
																<p>
																	<?php esc_html_e('Balance Amount', 'bookingpress-appointment-booking'); ?>
																	<span class="bpa-psw-ba__included-tax-label" v-if="scope.row.price_display_setting == 'include_taxes'">{{ scope.row.included_tax_label }}</span>
																</p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>{{ scope.row.total_amount_with_currency }}</p>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</template>
							</el-table-column>
							<el-table-column type="selection"></el-table-column>
							<el-table-column prop="payment_date" min-width="70" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>							
							<el-table-column prop="payment_service" min-width="100" label="<?php
								$bookingpress_payment_transaction_item_label = esc_html__( 'Service', 'bookingpress-appointment-booking' ); 
								$bookingpress_payment_transaction_item_label = apply_filters('bookingpress_payment_transaction_item_label',$bookingpress_payment_transaction_item_label);
								echo esc_html($bookingpress_payment_transaction_item_label);								
							?>" sortable></el-table-column>							
							<el-table-column prop="payment_status" min-width="90" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
								<template slot-scope="scope">					
									<div class="bpa-table-status-dropdown-wrapper" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-loader-active' : ''" v-if="bookingpress_edit_payment == 1">
										<div class="bpa-tsd--loader" v-if="scope.row.change_status_loader == 1" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-active' : ''">
											<div class="bpa-btn--loader__circles">
												<div></div>
												<div></div>
												<div></div>
											</div>
										</div>
										<el-select class="bpa-form-control" :class="((scope.row.payment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.payment_status == '3' ? 'bpa-appointment-status--rejected' : '') || (scope.row.payment_status == '4' ? 'bpa-appointment-status--approved' : '') || (scope.row.payment_status == '1' ? 'bpa-appointment-status--completed' : '') || (scope.row.payment_status == '5' ? 'bpa-appointment-status--refund-partial' : '')" v-model="scope.row.payment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-payment-status-dropdown-popper" @change="bookingpress_change_status(scope.row.payment_log_id, $event)">
											<el-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
												<el-option v-for="item in payment_status_data" :key="item.value" :label="item.text" :value="item.value"></el-option>
											</el-option-group>
										</el-select>
									</div>
									<el-tag class="bpa-front-pill " :class="((scope.row.payment_status == '2') ? '--warning' : '') || (scope.row.payment_status == '3' ? '--rejected' : '') || (scope.row.payment_status == '4' ? '--approved' : '') || (scope.row.payment_status == '1' ? '--completed' : '')" v-else>{{ scope.row.payment_status_label }}</el-tag>
								</template>
							</el-table-column>
							<el-table-column prop="payment_amount" min-width="70" label="<?php esc_html_e( 'Amount', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="payment_numberic_amount">
								<template slot-scope="scope">
									<div class="bpa-mpi__amount-row">
										<div class="bpa-mpi__ar-body">
											<!-- <span class="bpa-mpi__amount" v-if="scope.row.is_deposit_enable == '1' && scope.row.payment_status == '4'">{{ scope.row.deposit_amount_with_currency }}</span> -->
											<span class="bpa-mpi__amount">{{ scope.row.payment_amount }}</span>
											<span v-if="scope.row.is_deposit_enable == 1 && scope.row.payment_status == '4'" class="bpa-is-deposit-payment-val"><?php esc_html_e('of', 'bookingpress-appointment-booking'); ?> {{ scope.row.total_amount_with_currency }}</span>
										</div>
										<div class="bpa-mpi__ar-icons">
											<?php do_action('bookingpress_backend_payment_list_type_icons'); ?>
											<el-tooltip content="<?php esc_html_e('Cart Transaction', 'bookingpress-appointment-booking'); ?>" placement="top" v-if="scope.row.is_cart == '1'">
												<span class="material-icons-round">shopping_cart</span>
											</el-tooltip>
											<el-tooltip content="<?php esc_html_e('Deposit', 'bookingpress-appointment-booking'); ?>" placement="top" v-if="scope.row.is_deposit_enable == '1'">
												<span class="bpa-apc__deposit-icon">
													<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M16.9596 12.2237C16.8902 12.0273 16.746 11.8662 16.5583 11.7756C16.3706 11.685 16.1548 11.6723 15.9578 11.7402L13.7872 12.4116C13.2376 12.9125 13.0288 12.7838 9.00068 12.7838C8.90842 12.7838 8.81994 12.7471 8.75471 12.6819C8.68947 12.6167 8.65282 12.5282 8.65282 12.4359C8.65282 12.3437 8.68947 12.2552 8.75471 12.1899C8.81994 12.1247 8.90842 12.0881 9.00068 12.0881C13.1749 12.0881 13.0323 12.1681 13.3384 11.862C13.4551 11.7331 13.5206 11.5661 13.5228 11.3923C13.5228 11.2078 13.4495 11.0309 13.319 10.9004C13.1886 10.7699 13.0116 10.6966 12.8271 10.6966H9.10504C8.62152 10.6966 8.21801 10.0009 6.80919 10.0009H4.47856V13.6256L4.92729 13.8795C6.20362 14.6153 7.63128 15.0496 9.10115 15.149C10.571 15.2485 12.0442 15.0106 13.408 14.4535L16.5387 13.1908C16.7162 13.1104 16.8576 12.967 16.9354 12.7883C17.0132 12.6096 17.0218 12.4084 16.9596 12.2237ZM1 14.523H3.78285V9.30521H1V14.523ZM2.0714 12.9994C2.09103 12.9518 2.12099 12.9092 2.1591 12.8746C2.19722 12.84 2.24255 12.8142 2.29181 12.7993C2.34107 12.7843 2.39304 12.7805 2.44398 12.788C2.49491 12.7956 2.54353 12.8143 2.58633 12.8429C2.62913 12.8716 2.66504 12.9093 2.69147 12.9535C2.71791 12.9977 2.7342 13.0472 2.73919 13.0984C2.74417 13.1497 2.73771 13.2014 2.72028 13.2499C2.70285 13.2983 2.67489 13.3423 2.6384 13.3786C2.58145 13.4353 2.50661 13.4705 2.42662 13.4783C2.34663 13.4861 2.26641 13.4659 2.1996 13.4213C2.13279 13.3766 2.08351 13.3102 2.06014 13.2333C2.03677 13.1564 2.04074 13.0737 2.0714 12.9994ZM11.4357 8.95736C12.1237 8.95736 12.7962 8.75334 13.3683 8.37112C13.9403 7.98889 14.3862 7.44561 14.6494 6.80999C14.9127 6.17437 14.9816 5.47494 14.8474 4.80017C14.7132 4.1254 14.3819 3.50558 13.8954 3.01909C13.4089 2.53261 12.7891 2.20131 12.1143 2.06709C11.4395 1.93286 10.7401 2.00175 10.1045 2.26504C9.46886 2.52832 8.92558 2.97417 8.54336 3.54622C8.16113 4.11827 7.95711 4.79081 7.95711 5.4788C7.95711 6.40137 8.3236 7.28616 8.97596 7.93851C9.62831 8.59087 10.5131 8.95736 11.4357 8.95736ZM11.7835 5.82666H11.0878C10.811 5.82666 10.5456 5.71671 10.3499 5.521C10.1542 5.3253 10.0442 5.05986 10.0442 4.78309C10.0442 4.50632 10.1542 4.24088 10.3499 4.04518C10.5456 3.84947 10.811 3.73952 11.0878 3.73952V3.39167C11.0878 3.29941 11.1245 3.21093 11.1897 3.1457C11.2549 3.08046 11.3434 3.04381 11.4357 3.04381C11.5279 3.04381 11.6164 3.08046 11.6816 3.1457C11.7469 3.21093 11.7835 3.29941 11.7835 3.39167V3.73952H12.4792C12.5715 3.73952 12.66 3.77617 12.7252 3.84141C12.7904 3.90664 12.8271 3.99512 12.8271 4.08738C12.8271 4.17964 12.7904 4.26812 12.7252 4.33335C12.66 4.39859 12.5715 4.43524 12.4792 4.43524H11.0878C10.9956 4.43524 10.9071 4.47188 10.8418 4.53712C10.7766 4.60236 10.74 4.69083 10.74 4.78309C10.74 4.87535 10.7766 4.96383 10.8418 5.02906C10.9071 5.0943 10.9956 5.13095 11.0878 5.13095H11.7835C12.0603 5.13095 12.3257 5.24089 12.5214 5.4366C12.7171 5.63231 12.8271 5.89774 12.8271 6.17451C12.8271 6.45128 12.7171 6.71672 12.5214 6.91243C12.3257 7.10813 12.0603 7.21808 11.7835 7.21808V7.56594C11.7835 7.65819 11.7469 7.74667 11.6816 7.81191C11.6164 7.87714 11.5279 7.91379 11.4357 7.91379C11.3434 7.91379 11.2549 7.87714 11.1897 7.81191C11.1245 7.74667 11.0878 7.65819 11.0878 7.56594V7.21808H10.3921C10.2998 7.21808 10.2114 7.18143 10.1461 7.1162C10.0809 7.05096 10.0442 6.96248 10.0442 6.87022C10.0442 6.77797 10.0809 6.68949 10.1461 6.62425C10.2114 6.55902 10.2998 6.52237 10.3921 6.52237H11.7835C11.8758 6.52237 11.9643 6.48572 12.0295 6.42049C12.0947 6.35525 12.1314 6.26677 12.1314 6.17451C12.1314 6.08226 12.0947 5.99378 12.0295 5.92854C11.9643 5.86331 11.8758 5.82666 11.7835 5.82666Z" />
													</svg>
												</span>
											</el-tooltip>
										</div>
									</div>
									<div class="bpa-table-actions-wrap" v-if="bookingpress_delete_payment == 1 || ( bookingpress_edit_payment == 1 && (scope.row.payment_status == '4') || (scope.row.appointment_status == '2' && scope.row.payment_status == '2') || (scope.row.is_cart == '1' && scope.row.payment_status == '2'))">
										<div class="bpa-table-actions">									
											<el-tooltip effect="dark" content="" placement="top" open-delay="300" v-if="bookingpress_edit_payment == 1 && (scope.row.payment_status == '4') || (scope.row.appointment_status == '2' && scope.row.payment_status == '2') || (scope.row.is_cart == '1' && scope.row.payment_status == '2')">
												<div slot="content">
													<span><?php esc_html_e( 'Send Payment Link', 'bookingpress-appointment-booking' ); ?></span>
												</div>
												<el-popover placement="bottom" trigger="click" popper-class="bpa-dialog bpa-dailog__small bpa-dialog--add-category bpa-complete-payment-dialog">
													<div class="bpa-dialog-heading">
														<el-row type="flex">
															<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																<h1 class="bpa-page-heading"><?php esc_html_e( 'Share Payment Link', 'bookingpress-appointment-booking' ); ?></h1>
															</el-col>
														</el-row>
													</div>
													<div class="bpa-dialog-body">
														<el-container class="bpa-grid-list-container bpa-add-categpry-container">
															<div class="bpa-form-row">				
																<el-row>
																	<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																		<el-form label-position="top">
																			<div class="bpa-form-body-row bpa-dsu__checkbox-row">
																				<el-row>
																					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																						<el-form-item>
																							<template #label>
																								<span class="bpa-form-label"><?php echo esc_html__('Share With', 'bookingpress-appointment-booking'); ?></span>
																							</template>
																							<el-checkbox-group v-model="bookingpress_complete_payment_link_send_options">
																								<el-checkbox class="bpa-front-label bpa-custom-checkbox--is-label" label="email"><?php esc_html_e( 'Through Email', 'bookingpress-appointment-booking' ); ?></el-checkbox>
																								<?php
																									do_action('bookingpress_add_more_complete_payment_link_option');
																								?>
																							</el-checkbox-group>
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
															<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" @click="bpa_send_complete_payment_link(scope.row.payment_log_id)" :class="(is_send_button_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_send_button_disabled">
																<span class="bpa-btn__label"><?php esc_html_e( 'Send Link', 'bookingpress-appointment-booking' ); ?></span>
																<div class="bpa-btn--loader__circles">				    
																	<div></div>
																	<div></div>
																	<div></div>
																</div>
															</el-button>
														</div>
													</div>
													<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __secondary">
														<span class="material-icons-round">link</span>
													</el-button>
												</el-popover>
											</el-tooltip>

											<el-tooltip effect="dark" content="" placement="top" open-delay="300">
												<div slot="content">
													<span><?php esc_html_e( 'Approve', 'bookingpress-appointment-booking' ); ?></span>
												</div>
												<el-popconfirm 
													confirm-button-text='<?php esc_html_e( 'Approve', 'bookingpress-appointment-booking' ); ?>' 
													cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
													icon="false" 
													title="<?php esc_html_e( 'Are you sure you want to approve the Payment (Appointment will automatically be approved)?', 'bookingpress-appointment-booking' ); ?>" 
													@confirm="bpa_approve_appointment(scope.row.payment_log_id)" 
													confirm-button-type="bpa-btn bpa-btn__small bpa-btn--primary" 
													cancel-button-type="bpa-btn bpa-btn__small"
													v-if="scope.row.appointment_status == '2'">
													<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __secondary">
														<span class="material-icons-round">done</span>
													</el-button>
												</el-popconfirm>
											</el-tooltip>
											<el-tooltip effect="dark" content="" placement="top" open-delay="300" v-if="bookingpress_delete_payment == 1">
												<div slot="content">
													<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
												</div>
												<el-popconfirm 
													confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
													cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
													icon="false" 
													title="<?php esc_html_e( 'Are you sure you want to delete this payment transaction?', 'bookingpress-appointment-booking' ); ?>" 
													@confirm="deletePaymentLog(scope.row.payment_log_id)" 
													confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
													cancel-button-type="bpa-btn bpa-btn__small">
													<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
														<span class="material-icons-round">delete</span>
													</el-button>
												</el-popconfirm>
											</el-tooltip>
											<?php											
											$bookingpress_add_dynamic_action_btn_content = "";
											$bookingpress_add_dynamic_action_btn_content = apply_filters( 'bookingpress_payment_list_add_action_button', $bookingpress_add_dynamic_action_btn_content);
											echo $bookingpress_add_dynamic_action_btn_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											?>
										</div>
									</div>
								</template>
							</el-table-column>
						</el-table>
					</div>
					<div class="bpa-tc__wrapper bpa-manage-payments-container--sm" v-if="current_screen_size == 'mobile'">
						<el-table ref="multipleTable" :data="items" class="bpa-manage-payment-items" @selection-change="handleSelectionChange" @row-click="bookingpress_full_row_clickable" :show-header="false" @expand-change="bookingpress_row_expand">
							<el-table-column type="expand">
								<template slot-scope="scope">
									<div class="bpa-view-payment-card">
										<div class="bpa-vpc--head">
											<div class="bpa-vpc--head__left">
												<h2><?php esc_html_e('Payment Details', 'bookingpress-appointment-booking'); ?></h2>
												<p :class="[(scope.row.payment_status == '1') ? 'bpa-cl-pt-main-green' : '', (scope.row.payment_status == '2') ? 'bpa-cl-sc-warning' : '', (scope.row.payment_status == '4') ? 'bpa-cl-pt-blue' : '', (scope.row.payment_status == '3') ? 'bpa-cl-danger' : '']">{{ scope.row.payment_status_label }}</p>
											</div>
											<div class="bpa-hw-right-btn-group bpa-vpc--head__right" v-if="scope.row.payment_refund_status == 1">
												<el-button class="bpa-btn bpa-btn__refund" @click="bookingpress_open_refund_model(event,scope.row.appointment_id,scope.row.payment_log_id,scope.row.appointment_currency_symbol,scope.row.payment_partial_refund)">
													<svg viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
														<path d="M12.1471 5.67946H15.6684V1.96777" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M16.56 9.18573C16.56 13.2612 13.2555 16.5639 9.18001 16.5639C5.1045 16.5639 1.79999 13.2594 1.79999 9.18384C1.79999 5.10834 5.1045 1.80005 9.18001 1.80005C11.9869 1.80005 14.4299 3.36654 15.6759 5.67009" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M7.54754 10.7106C7.54754 11.6117 8.27894 12.3431 9.18 12.3431C10.0811 12.3431 10.8125 11.6117 10.8125 10.7106C10.8125 9.80957 10.0811 9.07816 9.17812 9.07816C8.27517 9.07816 7.54565 8.34676 7.54565 7.4457C7.54565 6.54464 8.27706 5.81323 9.18 5.81323C10.0811 5.81323 10.8144 6.54464 10.8144 7.4457" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M9.17999 13.6685V12.3452" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
														<path d="M9.17999 5.81135V4.48804" stroke="#727E95" stroke-width="1.50805" stroke-miterlimit="10"/>
													</svg> 
													<?php esc_html_e( 'Refund', 'bookingpress-appointment-booking' ); ?>
												</el-button>
												<?php 
													do_action('bookingpress_add_dynamic_buttons_for_view_payments');
												?>
											</div>
										</div>
										<div class="bpa-vpc--body">
											<div class="bpa-csw--customer-info">
												<div class="bpa-ci--avatar">
													<img :src="scope.row.customer_avatar" alt="">
												</div>
												<div class="bpa-ci--body">
													<h4 v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</h4>
													<p>{{ scope.row.customer_email }}</p>
												</div>
											</div>
											<div class="bpa-vpc__customer-summary-wrapper">
												<div class="bpa-vpc-csw__item">
													<h4><?php esc_html_e('Mode', 'bookingpress-appointment-booking'); ?></h4>
													<p>{{ scope.row.payment_gateway_label }}</p>
												</div>
												<div class="bpa-vpc-csw__item">
													<h4><?php esc_html_e('Transaction ID', 'bookingpress-appointment-booking'); ?></h4>
													<p>{{ scope.row.transaction_id }}</p>
												</div>
											</div>
											<div v-if="(scope.row.is_package_purchase == 'undefined') || (scope.row.is_package_purchase != 'undefined' && scope.row.is_package_purchase != '1')" class="bpa-vpc__appointment-details">
												<h4 class="bpa-vac__sec-heading"><?php esc_html_e('Appointment Details', 'bookingpress-appointment-booking'); ?></h4>
												<div class="bpa-vac-ap--items__sm">													
													<div class="bpa-ap-item__body">
														<div class="bpa-ib--item-card" v-for="appointment_details in scope.row.appointment_details">
															<div class="bpa-ib--item-head__sm">
																<div class="bpa-ih-left__sm">
																	<p>{{ appointment_details.bookingpress_service_name }}</p>
																</div>
																<div class="bpa-ih-right__sm">
																	<p>{{ appointment_details.bookingpress_service_price_with_currency }}</p>
																	<p class="bpa-ih-guest-counter--sm" v-if="appointment_details.bookingpress_selected_extra_members > 1">
																		<span class="material-icons-round">account_circle</span>
																		{{ appointment_details.bookingpress_selected_extra_members }} 
																	</p>
																</div>
															</div>
															
															<div class="bpa-ap__service-extras--sm" v-if="appointment_details.extra_service_details.length > 0">
																<div class="bpa-se__item--sm" v-for="appointment_extra_details in appointment_details.extra_service_details">
																	<p>x {{ appointment_extra_details.selected_qty }} {{ appointment_extra_details.extra_name }}</p>
																	<p>{{ appointment_extra_details.extra_service_price_with_currency }}</p>
																</div>
															</div>
															<div class="bpa-ap__service-extras--sm" v-if="appointment_details.bookingpress_purchase_type == 3 && appointment_details.bookingpress_package_discount_amount_with_currency != 'undefined' && appointment_details.bookingpress_package_discount_amount_with_currency != 0 && appointment_details.bookingpress_applied_package_data_arr.bookingpress_package_name != 'undefined'">
																<div class="bpa-se__item--sm">
																	<p>{{ appointment_details.bookingpress_applied_package_data_arr.bookingpress_package_name }}</p>
																	<p> - {{ appointment_details.bookingpress_package_discount_amount_with_currency }}</p>
																</div>
															</div>															
															<div class="bpa-ib--datetime__sm">
																<div class="bpa-dt__item">
																	<span class="material-icons-round">calendar_today</span>
																	<p>{{ appointment_details.bookingpress_appointment_date }}</p>
																</div>
																<div class="bpa-dt__item">
																	<span class="material-icons-round">access_time</span>
																	<p v-if="scope.row.appointment_start_time != ''">{{ appointment_details.bookingpress_appointment_time }} <?php esc_html_e('to', 'bookingpress-appointment-booking'); ?> {{ appointment_details.bookingpress_appointment_end_time }}</p>
																</div>
															</div>

															<!-- <div class="bpa-ib--item" v-if="appointment_details.is_deposit_applied == '1'">
																<p>{{ appointment_details.bookingpress_deposit_amount_with_currency }}</p>
															</div> -->

															<div class="bpa-ib-item__staff-info" v-show="appointment_details.bookingpress_staff_member_id != '0'">
																<img :src="appointment_details.staff_avatar_url" alt="">
																<p>{{ appointment_details.bookingpress_staff_first_name }} {{ appointment_details.bookingpress_staff_last_name }}</p>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="bpa-vpc__payment-summary-wapper">
												<h4 class="bpa-vac__sec-heading"><?php esc_html_e('Payment Summary', 'bookingpress-appointment-booking'); ?></h4>
												<div class="bpa-psw--body">
													<div class="bpa-psw--body-row">
														<div class="bpa-psw__item">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Subtotal', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>{{ scope.row.subtotal_amount_with_currency }}</p>
															</div>
														</div>
														<?php do_action('bookingpress_payment_page_after_subtotal'); ?>
														<div class="bpa-psw__item" v-if="scope.row.is_deposit_enable == '1'">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Deposit', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>{{ scope.row.payment_amount }}</p>
															</div>
														</div>
														<div class="bpa-psw__item" v-if="scope.row.is_deposit_enable == '1' && scope.row.payment_status == '4'">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Due Amount', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount bookingpress_due_amount">
																<p>{{ scope.row.due_amount_with_currency }}</p>
															</div>
														</div>
													</div>
													<div class="bpa-psw--body-row bpa-psw--body-row__tax-item" :class="( (scope.row.price_display_setting != 'include_taxes' || (scope.row.price_display_setting == 'include_taxes' && scope.row.display_tax_order_summary == 'true' ) ) && scope.row.applied_coupon_code == '') ? 'bpa-psw--body-row__tax-item-excluded' : ''" v-if="scope.row.tax_amount != ''">
														<div class="bpa-psw__item" v-if="(scope.row.tax_amount != '') && (scope.row.price_display_setting != 'include_taxes' || (scope.row.price_display_setting == 'include_taxes' && scope.row.display_tax_order_summary == 'true' ))">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Tax', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>+{{ scope.row.tax_amount_with_currency }}</p>
															</div>
														</div>
													</div>
													<div class="bpa-psw--body-row" v-if="scope.row.applied_coupon_code != ''">													
														<div class="bpa-psw__item --bpa-is-coupon-item" v-if="scope.row.applied_coupon_code != ''">
															<div class="bpa-psw__item-title">
																<p><?php esc_html_e('Coupon', 'bookingpress-appointment-booking'); ?></p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>-{{ scope.row.coupon_discount_amount_with_currency }}</p>
															</div>
														</div>
													</div>
													<?php do_action('bookingpress_modify_payment_managepayment_section') ?>
													<div class="bpa-psw--body-row --bpa-is-total-row">
														<div class="bpa-psw__item">
															<div class="bpa-psw__item-title">
																<p>
																	<?php esc_html_e('Balance Amount', 'bookingpress-appointment-booking'); ?>
																	<span class="bpa-psw-ba__included-tax-label" v-if="scope.row.price_display_setting == 'include_taxes'">{{ scope.row.included_tax_label }}</span>
																</p>
															</div>
															<div class="bpa-psw__item-amount">
																<p>{{ scope.row.total_amount_with_currency }}</p>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</template>
							</el-table-column>
							<el-table-column type="selection"></el-table-column>
							<el-table-column>
								<template slot-scope="scope">
									<div class="bpa-mpay-item__mob">
										<div class="bpa-mpay-item--head">
											<div class="bpa-mpay-head__left">
												<h4>{{ scope.row.payment_service }}</h4>
												<span>{{ scope.row.payment_date }}</span>
											</div>
											<div class="bpa-mpay-head__right">
												<div class="bpa-mpi__amount-row">
													<div class="bpa-mpi__ar-body">
														<!-- <span class="bpa-mpi__amount" v-if="scope.row.is_deposit_enable == '1' && scope.row.payment_status == '4'">{{ scope.row.deposit_amount_with_currency }}</span> -->
														<span class="bpa-mpi__amount">{{ scope.row.payment_amount }}</span>
														<span v-if="scope.row.is_deposit_enable == 1 && scope.row.payment_status == '4'" class="bpa-is-deposit-payment-val"><?php esc_html_e('of', 'bookingpress-appointment-booking'); ?> {{ scope.row.total_amount_with_currency }}</span>
													</div>
													<div class="bpa-mpi__ar-icons">
														<?php do_action('bookingpress_backend_payment_list_type_icons'); ?>
														<el-tooltip content="<?php esc_html_e('Cart Transaction', 'bookingpress-appointment-booking'); ?>" placement="top" v-if="scope.row.is_cart == '1'">
															<span class="material-icons-round">shopping_cart</span>
														</el-tooltip>
														<el-tooltip content="<?php esc_html_e('Deposit', 'bookingpress-appointment-booking'); ?>" placement="top" v-if="scope.row.is_deposit_enable == '1'">
															<span class="bpa-apc__deposit-icon">
																<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<path d="M16.9596 12.2237C16.8902 12.0273 16.746 11.8662 16.5583 11.7756C16.3706 11.685 16.1548 11.6723 15.9578 11.7402L13.7872 12.4116C13.2376 12.9125 13.0288 12.7838 9.00068 12.7838C8.90842 12.7838 8.81994 12.7471 8.75471 12.6819C8.68947 12.6167 8.65282 12.5282 8.65282 12.4359C8.65282 12.3437 8.68947 12.2552 8.75471 12.1899C8.81994 12.1247 8.90842 12.0881 9.00068 12.0881C13.1749 12.0881 13.0323 12.1681 13.3384 11.862C13.4551 11.7331 13.5206 11.5661 13.5228 11.3923C13.5228 11.2078 13.4495 11.0309 13.319 10.9004C13.1886 10.7699 13.0116 10.6966 12.8271 10.6966H9.10504C8.62152 10.6966 8.21801 10.0009 6.80919 10.0009H4.47856V13.6256L4.92729 13.8795C6.20362 14.6153 7.63128 15.0496 9.10115 15.149C10.571 15.2485 12.0442 15.0106 13.408 14.4535L16.5387 13.1908C16.7162 13.1104 16.8576 12.967 16.9354 12.7883C17.0132 12.6096 17.0218 12.4084 16.9596 12.2237ZM1 14.523H3.78285V9.30521H1V14.523ZM2.0714 12.9994C2.09103 12.9518 2.12099 12.9092 2.1591 12.8746C2.19722 12.84 2.24255 12.8142 2.29181 12.7993C2.34107 12.7843 2.39304 12.7805 2.44398 12.788C2.49491 12.7956 2.54353 12.8143 2.58633 12.8429C2.62913 12.8716 2.66504 12.9093 2.69147 12.9535C2.71791 12.9977 2.7342 13.0472 2.73919 13.0984C2.74417 13.1497 2.73771 13.2014 2.72028 13.2499C2.70285 13.2983 2.67489 13.3423 2.6384 13.3786C2.58145 13.4353 2.50661 13.4705 2.42662 13.4783C2.34663 13.4861 2.26641 13.4659 2.1996 13.4213C2.13279 13.3766 2.08351 13.3102 2.06014 13.2333C2.03677 13.1564 2.04074 13.0737 2.0714 12.9994ZM11.4357 8.95736C12.1237 8.95736 12.7962 8.75334 13.3683 8.37112C13.9403 7.98889 14.3862 7.44561 14.6494 6.80999C14.9127 6.17437 14.9816 5.47494 14.8474 4.80017C14.7132 4.1254 14.3819 3.50558 13.8954 3.01909C13.4089 2.53261 12.7891 2.20131 12.1143 2.06709C11.4395 1.93286 10.7401 2.00175 10.1045 2.26504C9.46886 2.52832 8.92558 2.97417 8.54336 3.54622C8.16113 4.11827 7.95711 4.79081 7.95711 5.4788C7.95711 6.40137 8.3236 7.28616 8.97596 7.93851C9.62831 8.59087 10.5131 8.95736 11.4357 8.95736ZM11.7835 5.82666H11.0878C10.811 5.82666 10.5456 5.71671 10.3499 5.521C10.1542 5.3253 10.0442 5.05986 10.0442 4.78309C10.0442 4.50632 10.1542 4.24088 10.3499 4.04518C10.5456 3.84947 10.811 3.73952 11.0878 3.73952V3.39167C11.0878 3.29941 11.1245 3.21093 11.1897 3.1457C11.2549 3.08046 11.3434 3.04381 11.4357 3.04381C11.5279 3.04381 11.6164 3.08046 11.6816 3.1457C11.7469 3.21093 11.7835 3.29941 11.7835 3.39167V3.73952H12.4792C12.5715 3.73952 12.66 3.77617 12.7252 3.84141C12.7904 3.90664 12.8271 3.99512 12.8271 4.08738C12.8271 4.17964 12.7904 4.26812 12.7252 4.33335C12.66 4.39859 12.5715 4.43524 12.4792 4.43524H11.0878C10.9956 4.43524 10.9071 4.47188 10.8418 4.53712C10.7766 4.60236 10.74 4.69083 10.74 4.78309C10.74 4.87535 10.7766 4.96383 10.8418 5.02906C10.9071 5.0943 10.9956 5.13095 11.0878 5.13095H11.7835C12.0603 5.13095 12.3257 5.24089 12.5214 5.4366C12.7171 5.63231 12.8271 5.89774 12.8271 6.17451C12.8271 6.45128 12.7171 6.71672 12.5214 6.91243C12.3257 7.10813 12.0603 7.21808 11.7835 7.21808V7.56594C11.7835 7.65819 11.7469 7.74667 11.6816 7.81191C11.6164 7.87714 11.5279 7.91379 11.4357 7.91379C11.3434 7.91379 11.2549 7.87714 11.1897 7.81191C11.1245 7.74667 11.0878 7.65819 11.0878 7.56594V7.21808H10.3921C10.2998 7.21808 10.2114 7.18143 10.1461 7.1162C10.0809 7.05096 10.0442 6.96248 10.0442 6.87022C10.0442 6.77797 10.0809 6.68949 10.1461 6.62425C10.2114 6.55902 10.2998 6.52237 10.3921 6.52237H11.7835C11.8758 6.52237 11.9643 6.48572 12.0295 6.42049C12.0947 6.35525 12.1314 6.26677 12.1314 6.17451C12.1314 6.08226 12.0947 5.99378 12.0295 5.92854C11.9643 5.86331 11.8758 5.82666 11.7835 5.82666Z" />
																</svg>
															</span>
														</el-tooltip>
													</div>
												</div>
											</div>
										</div>
										<div class="bpa-mpay-item--foot">
											<div class="bpa-table-status-dropdown-wrapper" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-loader-active' : ''" v-if="bookingpress_edit_payment == 1">
												<div class="bpa-tsd--loader" v-if="scope.row.change_status_loader == 1" :class="(scope.row.change_status_loader == 1) ? '__bpa-is-active' : ''">
													<div class="bpa-btn--loader__circles">
														<div></div>
														<div></div>
														<div></div>
													</div>
												</div>
												<el-select class="bpa-form-control" :class="((scope.row.payment_status == '2') ? 'bpa-appointment-status--warning' : '') || (scope.row.payment_status == '3' ? 'bpa-appointment-status--rejected' : '') || (scope.row.payment_status == '4' ? 'bpa-appointment-status--approved' : '') || (scope.row.payment_status == '1' ? 'bpa-appointment-status--completed' : '') || (scope.row.payment_status == '5' ? 'bpa-appointment-status--refund-partial' : '')" v-model="scope.row.payment_status" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-payment-status-dropdown-popper" @change="bookingpress_change_status(scope.row.payment_log_id, $event)">
													<el-option-group label="<?php esc_html_e( 'Change status', 'bookingpress-appointment-booking' ); ?>">
														<el-option v-for="item in payment_status_data" :key="item.value" :label="item.text" :value="item.value"></el-option>
													</el-option-group>
												</el-select>
											</div>
											<el-tag class="bpa-front-pill " :class="((scope.row.payment_status == '2') ? '--warning' : '') || (scope.row.payment_status == '3' ? '--rejected' : '') || (scope.row.payment_status == '4' ? '--approved' : '') || (scope.row.payment_status == '1' ? '--completed' : '')" v-else>{{ scope.row.payment_status_label }}</el-tag>
											
											<div class="bpa-mpay-fi__actions" v-if="bookingpress_delete_payment == 1 || ( bookingpress_edit_payment == 1 && (scope.row.payment_status == '4') || (scope.row.appointment_status == '2' && scope.row.payment_status == '2') || (scope.row.is_cart == '1' && scope.row.payment_status == '2'))">
												<el-popover placement="bottom" trigger="click" popper-class="bpa-dialog bpa-dailog__small bpa-dialog--add-category bpa-complete-payment-dialog" v-if="bookingpress_edit_payment == 1 && (scope.row.payment_status == '4') || (scope.row.appointment_status == '2' && scope.row.payment_status == '2') || (scope.row.is_cart == '1' && scope.row.payment_status == '2')">
													<div class="bpa-dialog-heading">
														<el-row type="flex">
															<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																<h1 class="bpa-page-heading"><?php esc_html_e( 'Share Payment Link', 'bookingpress-appointment-booking' ); ?></h1>
															</el-col>
														</el-row>
													</div>
													<div class="bpa-dialog-body">
														<el-container class="bpa-grid-list-container bpa-add-categpry-container">
															<div class="bpa-form-row">				
																<el-row>
																	<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																		<el-form label-position="top">
																			<div class="bpa-form-body-row bpa-dsu__checkbox-row">
																				<el-row>
																					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																						<el-form-item>
																							<template #label>
																								<span class="bpa-form-label"><?php echo esc_html__('Share With', 'bookingpress-appointment-booking'); ?></span>
																							</template>
																							<el-checkbox-group v-model="bookingpress_complete_payment_link_send_options">
																								<el-checkbox class="bpa-front-label bpa-custom-checkbox--is-label" label="email"><?php esc_html_e( 'Through Email', 'bookingpress-appointment-booking' ); ?></el-checkbox>
																								<?php
																									do_action('bookingpress_add_more_complete_payment_link_option');
																								?>
																							</el-checkbox-group>
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
															<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" @click="bpa_send_complete_payment_link(scope.row.payment_log_id)" :class="(is_send_button_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_send_button_disabled">
																<span class="bpa-btn__label"><?php esc_html_e( 'Send Link', 'bookingpress-appointment-booking' ); ?></span>
																<div class="bpa-btn--loader__circles">				    
																	<div></div>
																	<div></div>
																	<div></div>
																</div>
															</el-button>
														</div>
													</div>
													<el-button type="text" slot="reference" class="bpa-btn bpa-btn__filled-light __secondary">
														<span class="material-icons-round">link</span>
													</el-button>
												</el-popover>
												<el-popconfirm 
													confirm-button-text='<?php esc_html_e( 'Approve', 'bookingpress-appointment-booking' ); ?>' 
													cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
													icon="false" 
													title="<?php esc_html_e( 'Are you sure you want to approve the Payment (Appointment will automatically be approved)?', 'bookingpress-appointment-booking' ); ?>" 
													@confirm="bpa_approve_appointment(scope.row.payment_log_id)" 
													confirm-button-type="bpa-btn bpa-btn__small bpa-btn--primary" 
													cancel-button-type="bpa-btn bpa-btn__small"
													v-if="scope.row.appointment_status == '2'">
													<el-button type="text" slot="reference" class="bpa-btn bpa-btn__filled-light __secondary">
														<span class="material-icons-round">done</span>
													</el-button>
												</el-popconfirm>
												<el-popconfirm 
													confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
													cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
													icon="false" 
													title="<?php esc_html_e( 'Are you sure you want to delete this payment transaction?', 'bookingpress-appointment-booking' ); ?>" 
													@confirm="deletePaymentLog(scope.row.payment_log_id)" 
													confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
													cancel-button-type="bpa-btn bpa-btn__small" v-if="bookingpress_delete_payment == 1">
													<el-button type="text" slot="reference" class="bpa-btn bpa-btn__filled-light __danger">
														<span class="material-icons-round">delete</span>
													</el-button>
												</el-popconfirm>
												<?php											
													$bookingpress_add_dynamic_action_btn_content = "";
													$bookingpress_add_dynamic_action_btn_content = apply_filters( 'bookingpress_payment_list_add_action_button', $bookingpress_add_dynamic_action_btn_content);
													echo $bookingpress_add_dynamic_action_btn_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												?>
											</div>
										</div>
									</div>
								</template>
							</el-table-column>
						</el-table>
					</div>
				</el-container>
			</el-col>
		</el-row>	
		<el-row class="bpa-pagination" type="flex" v-if="items.length > 0"> <!-- Pagination -->
			<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" >
				<div class="bpa-pagination-left">
					<p><?php esc_html_e( 'Showing', 'bookingpress-appointment-booking' ); ?>&nbsp;<strong><u>{{ items.length }}</u></strong> <?php esc_html_e( 'out of', 'bookingpress-appointment-booking' ); ?>&nbsp;<strong>{{ totalItems }}</strong></p>
					<div class="bpa-pagination-per-page">
						<p><?php esc_html_e( 'Per Page', 'bookingpress-appointment-booking' ); ?></p>
						<el-select v-model="pagination_length_val" placeholder="Select" @change="changePaginationSize($event)" class="bpa-form-control" popper-class="bpa-pagination-dropdown">
							<el-option v-for="item in pagination_val" :key="item.text" :label="item.text" :value="item.value"></el-option>
						</el-select>
					</div>
				</div>
			</el-col>
			<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
				<el-pagination ref="bpa_pagination" @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" layout="prev, pager, next" :total="totalItems" :page-sizes="pagination_length" :page-size="perPage"></el-pagination>
			</el-col>
			<el-container v-if="(bookingpress_edit_payment == 1|| bookingpress_delete_payment == 1) && multipleSelection.length > 0" class="bpa-default-card bpa-bulk-actions-card">
				<el-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="closeBulkAction">
					<span class="material-icons-round">close</span>
				</el-button>
				<el-row type="flex" class="bpa-bac__wrapper">
					<el-col class="bpa-bac__left-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
						<span class="material-icons-round">check_circle</span>
						<p>{{ multipleSelection.length }}<?php esc_html_e( ' Items Selected', 'bookingpress-appointment-booking' ); ?></p>
					</el-col>
					<el-col class="bpa-bac__right-area" :xs="24" :sm="12" :md="12" :lg="12" :xl="12">					
						<el-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?>" v-if="bookingpress_edit_payment == 1 && bookingpress_delete_payment == 0">
							<el-option-group v-for="bulk_option_data in bulk_options" :key="bulk_option_data.label" :label="bulk_option_data.label" :value="bulk_option_data.label">
								<el-option v-for="bulk_action_data in bulk_option_data.bulk_actions" :label="bulk_action_data.text" :value="bulk_action_data.value" v-if="bulk_action_data.value != 'delete'"></el-option>
							</el-option-group>
						</el-select>
						<el-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?>" v-if="bookingpress_edit_payment == 0 && bookingpress_delete_payment == 1">
							<el-option-group v-for="bulk_option_data in bulk_options" :key="bulk_option_data.label" :label="bulk_option_data.label" :value="bulk_option_data.label" v-if="bulk_option_data.value != 'change_status'">
								<el-option v-for="bulk_action_data in bulk_option_data.bulk_actions" :label="bulk_action_data.text" :value="bulk_action_data.value" v-if="bulk_action_data.value == 'delete' || bulk_action_data.value == 'bulk_action'"></el-option>
							</el-option-group>
						</el-select>
						<el-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?>" v-else>
							<el-option-group v-for="bulk_option_data in bulk_options" :key="bulk_option_data.label" :label="bulk_option_data.label" :value="bulk_option_data.label">
								<el-option v-for="bulk_action_data in bulk_option_data.bulk_actions" :label="bulk_action_data.text" :value="bulk_action_data.value"></el-option>
							</el-option-group>
						</el-select>							
						<el-button @click="bulk_actions" class="bpa-btn bpa-btn--primary bpa-btn__medium">
							<?php esc_html_e( 'Go', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</el-col>
				</el-row>
			</el-container>
		</el-row>
	</div>
</el-main>

<!-- View Payment Logs Modal -->

<el-dialog custom-class="bpa-dialog bpa-dialog--default bpa-dialog--manage-categories bpa-dialog--view-payment-info" title="" :visible.sync="view_payment_details_modal" :close-on-press-escape="close_modal_on_esc" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
	<div class="bpa-dialog-heading">
		<el-row type="flex" :gutter="24">
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'View Details', 'bookingpress-appointment-booking' ); ?></h1>
				<el-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="ClosePaymentModal()">
					<span class="material-icons-round">close</span>
				</el-button>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-back-loader-container" v-if="is_display_loader_view == '1'">
		<div class="bpa-back-loader"></div>
	</div>
	<div class="bpa-dialog-body">
		<div class="bpa-card bpa-card__body-row">
			<div class="bpa-dialog--vpi__body">
				<div class="bpa-dialog--vpi__body--head">
					<ul>
						<li :xs="12" :sm="12" :md="12" :lg="8" :xl="8">
							<span><?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?></span>
							<p v-text="view_payment_data.customer_name"></p>
						</li>
						<li :xs="12" :sm="12" :md="12" :lg="8" :xl="8">
							<span><?php esc_html_e( 'Appointment Date', 'bookingpress-appointment-booking' ); ?></span>
							<p v-text="view_payment_data.bookingpress_appointment_date"></p>
						</li>
						<li :xs="12" :sm="12" :md="12" :lg="8" :xl="8">
							<span><?php esc_html_e( 'Payment Status', 'bookingpress-appointment-booking' ); ?></span>
							<p v-text="view_payment_data.bookingpress_payment_status"></p>
						</li>
					</el-row>
				</div>
				<div class="bpa-dialog--vpi__body--extra-fields">
					<div class="bpa-dialog--vpi__body--ef-row">
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_service_name"></p>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-dialog--vpi__body--ef-row">
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Payment Gateway', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_payment_gateway"></p>
							</el-col>
						</el-row>
						</div>
					<div class="bpa-dialog--vpi__body--ef-row">
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Paid Amount', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_payment_amount"></p>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-dialog--vpi__body--ef-row">
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Transaction ID', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_transaction_id"></p>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-dialog--vpi__body--ef-row" v-if= "view_payment_data.bookingpress_applied_coupon_code != ''" >
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Coupon Text', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_applied_coupon_code"></p>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-dialog--vpi__body--ef-row" v-if= "view_payment_data.bookingpress_coupon_discount_amount != ''" >
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Coupon Amount', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_coupon_discount_amount"></p>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-dialog--vpi__body--ef-row" v-if= "view_payment_data.bookingpress_tax_percentage != ''" >
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Tax Percentage', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_tax_precentage"></p>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-dialog--vpi__body--ef-row" v-if= "view_payment_data.bookingpress_tax_amount != ''" >
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Tax Amount', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_tax_amount"></p>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-dialog--vpi__body--ef-row">
						<el-row type="flex">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<span><?php esc_html_e( 'Payer Email', 'bookingpress-appointment-booking' ); ?></span>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<p v-text="view_payment_data.bookingpress_payer_email"></p>
							</el-col>
						</el-row>
					</div>
				</div>
			</div>
		</div>
	</div>
</el-dialog>
<el-dialog custom-class="bpa-dialog bpa-dailog__small bpa-dialog--export-payments" id="payment_export_model" title="" :visible.sync="ExportPayment" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Export Data', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">				
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form label-position="top" @submit.native.prevent>
							<div class="bpa-form-body-row">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item>
											  <el-checkbox-group v-model="export_checked_field">								  							
												<el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" v-for="item in payment_export_field_list" :label="item.name">{{item.text}}</el-checkbox>
											  </el-checkbox-group>									  
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
			<el-button class="bpa-btn bpa-btn__medium" @click="close_export_payment_model" ><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" :class="(is_export_button_loader == '1') ?'bpa-btn--is-loader' : ''" @click="bookingpress_export_payments" :disabled="is_export_button_disabled" >
			  <span class="bpa-btn__label"><?php esc_html_e( 'Export', 'bookingpress-appointment-booking' ); ?></span>
			  <div class="bpa-btn--loader__circles">
				<div></div>
				  <div></div>
				  <div></div>
			  </div>
			</el-button>
		</div>
	</div>
</el-dialog>

<el-dialog id="refund_confirm_process" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--refund-process" title="" :visible.sync="refund_confirm_modal" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading" ><?php esc_html_e( 'Refund', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form ref="refund_confirm_form" :rules="rules_refund_confirm_form" :model="refund_confirm_form" label-position="top">
							<el-row>																								
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
									<el-form-item prop="refund_type">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Refund Type', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-radio v-model="refund_confirm_form.refund_type" label="full"><?php esc_html_e( 'Full refund', 'bookingpress-appointment-booking' ); ?></el-radio>
										<el-radio v-model="refund_confirm_form.refund_type" label="partial" v-if="refund_confirm_form.allow_partial_refund == 1"><?php esc_html_e( 'Partial refund', 'bookingpress-appointment-booking' ); ?></el-radio>
										<el-radio v-model="refund_confirm_form.refund_type" label="partial" disabled v-else><?php esc_html_e( 'Partial refund', 'bookingpress-appointment-booking' ); ?></el-radio>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item prop="refund_appointment_status">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Appointment status', 'bookingpress-appointment-booking' ); ?></span>
										</template> 
										<el-select v-model="refund_confirm_form.refund_appointment_status" class="bpa-form-control" placeholder="<?php esc_html_e( 'Select Status', 'bookingpress-appointment-booking' ); ?>">
											<el-option v-for="appointment_status in bookingpress_payment_appointment_status" :key="appointment_status.text" :label="appointment_status.text" :value="appointment_status.value"></el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="refund_confirm_form.refund_type == 'partial'">
									<el-form-item prop="refund_amount">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Refund Amount', 'bookingpress-appointment-booking' ); ?> ({{refund_confirm_form.refund_currency}})</span>
										</template>										
										<el-input @input="isValidateZeroDecimal" class="bpa-form-control" v-model="refund_confirm_form.refund_amount"></el-input>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item prop="refund_reason">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Refund Reason', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input class="bpa-form-control" v-model="refund_confirm_form.refund_reason" placeholder="<?php esc_html_e('Refund Reason','bookingpress-appointment-booking') ?>" type="textarea" :rows="3"></el-input>
									</el-form-item>
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
			<el-button class="bpa-btn bpa-btn__small" @click="close_refund_confirm_model"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" :class="(is_display_refund_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bookingpress_apply_for_refund(refund_confirm_form.payment_id,refund_confirm_form.appointment_id)" :disabled="is_refund_btn_disabled">
				<span class="bpa-btn__label"><?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?></span>
				<div class="bpa-btn--loader__circles">				    
					<div></div>
					<div></div>
					<div></div>
				</div>
			</el-button>
		</div>
	</div>
</el-dialog>

<?php do_action( 'bookingpress_payment_page_dialog_outside' ); ?>