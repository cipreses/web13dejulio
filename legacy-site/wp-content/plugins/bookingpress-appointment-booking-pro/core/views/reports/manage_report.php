<?php
	global $BookingPress,$bookingpress_common_date_format,$BookingPressPro, $bookingpress_global_options;
	$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
	$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
	$bookingpress_plural_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) : esc_html_e('Staff Members', 'bookingpress-appointment-booking');

	$bookingpress_appointment_status_arr = $bookingpress_global_options_arr['appointment_status'];
	$approved_label = $pending_label = $cancelled_label = $rejected_label = $noshow_label = $completed_label = "";
	foreach($bookingpress_appointment_status_arr as $k => $v){
		if($v['value'] == '1'){
			$approved_label = $v['text'];
		}else if($v['value'] == '2'){
			$pending_label = $v['text'];
		}else if($v['value'] == '3'){
			$cancelled_label = $v['text'];
		}else if($v['value'] == '4'){
			$rejected_label = $v['text'];
		}else if($v['value'] == '5'){
			$noshow_label = $v['text'];
		}else if($v['value'] == '6'){
			$completed_label = $v['text'];
		}
	}

	$bookingpress_payment_status_arr = $bookingpress_global_options_arr['payment_status'];
	$paid_label = $payment_pending_label = $refunded_label = $partially_paid_label = "";

	foreach($bookingpress_payment_status_arr as $k => $v){
		if($v['value'] == '1'){
			$paid_label = $v['text'];
		}else if($v['value'] == '2'){
			$payment_pending_label = $v['text'];
		}else if($v['value'] == '3'){
			$refunded_label = $v['text'];
		}else if($v['value'] == '4'){
			$partially_paid_label = $v['text'];
		}
	}

	$bookingpress_revenue_filter_payment_gateway_list = array(
		array(
			'value' => 'all',
			'text' => esc_html__('All', 'bookingpress-appointment-booking')
		),
		array(
			'value' => 'manual',
			'text' => esc_html__('Manual ( Paid by admin )', 'bookingpress-appointment-booking')
		),
		array(
			'value' => 'paypal',
			'text' => 'Paypal'
		),
	);
	 
	$bookingpress_revenue_filter_payment_gateway_list = apply_filters('bookingpress_revenue_filter_payment_gateway_list_add', $bookingpress_revenue_filter_payment_gateway_list);
	
?>

<el-main class="bpa-main-listing-card-container bpa-reports-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">
	<el-row type="flex" class="bpa-mlc-head-wrap">		
		<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-gs__cb-item-left">
			<h3 class="bpa-page-heading"><?php esc_html_e( 'Reports', 'bookingpress-appointment-booking' ); ?></h3>
		</el-col>
	</el-row>
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>
	<div id="bpa-main-container">
		<div class="bpa-reports__body">
			<el-tabs type="card" tab-position="left" class="bpa-tabs bpa-tabs--vertical__left-side">
				<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body">
					<span slot="label">	
						<?php esc_html_e('Appointment Report', 'bookingpress-appointment-booking'); ?>
					</span>
					<div class="bpa-general-settings-tabs--pb__card">
						<div class="bpa-rb-tab-content__body">
							<h3 class="bpa-page-heading"><?php esc_html_e( 'Appointment Report', 'bookingpress-appointment-booking' ); ?></h3>
							<div class="bpa-rb-chart-item-wrapper">
								<div class="bpa-ciw__filter-row">
									<el-row type="flex">
										<el-col :xs="main_col_width.xs" :sm="main_col_width.sm" :md="main_col_width.md" :lg="main_col_width.lg" :xl="main_col_width.xl" class="bpa-ciw-fr--left">
											<el-row type="flex" :gutter="24">
												<el-col :xs="service_col_width.xs" :sm="service_col_width.sm" :md="service_col_width.md" :lg="service_col_width.lg" :xl="service_col_width.xl">
													<el-select class="bpa-form-control" v-model="appointment_search_service" multiple filterable collapse-tags 
														placeholder="<?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?>"
														:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar" @change="change_appointment_report_filter">
														<el-option-group v-for="service_cat_data in appointment_service_filter_data" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
															<el-option v-for="service_data in service_cat_data.category_services" :label="service_data.service_name" :value="service_data.service_id"></el-option>
														</el-option-group>
													</el-select>
												</el-col>
												<el-col :xs="staff_col_width.xs" :sm="staff_col_width.sm" :md="staff_col_width.md" :lg="staff_col_width.lg" :xl="staff_col_width.xl" v-if="is_staffmember_activated == '1'">
													<el-select class="bpa-form-control" v-model="appointment_search_staff" multiple filterable collapse-tags placeholder="<?php esc_html_e('Select', 'bookingpress-appointment-booking'); ?><?php echo " ".esc_html($bookingpress_plural_staffmember_name); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar" @change="change_appointment_report_filter">
														<el-option v-for="item in search_staff_member_list" :label="item.text" :value="item.value">	
														</el-option>
													</el-select>
												</el-col>
												<?php do_action( 'bookingpress_add_additional_filter_for_report') ?>
											</el-row>
										</el-col>
										<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-ciw-fr--right">
											<el-date-picker @focus="bookingpress_remove_date_range_picker_focus" ref="bookingpress_custom_filter_rangepicker" v-model="custom_filter_val" class="bpa-form-control bpa-form-control--date-range-picker" format="<?php echo esc_html($bookingpress_common_date_format); ?>" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e( 'End Date', 'bookingpress-appointment-booking'); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-date-range-picker__is-filter-enabled" range-separator="-" value-format="yyyy-MM-dd" :picker-options="bookingpress_picker_options" @change="select_appointment_report_filter('custom')"></el-date-picker>
										</el-col>
									</el-row>
								</div>
								<div class="bpa-ciw__chart-body">
									<el-row type="flex" :gutter="40">
										<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="18">
											<div class="bpa-cb__content">
												<canvas id="bookingpress_appointments_charts"></canvas>
											</div>
										</el-col>
										<el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="06">
											<div class="bpa-cb__chart-stats">
												<h4 class="bpa-cb-stats-title"><?php esc_html_e('Quick Stats', 'bookingpress-appointment-booking'); ?></h4>
												<div class="bpa-cb-stats-item --bpa-is-secondary">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php echo esc_html($pending_label); ?></div>
													<div class="bpa-stats-item-val">{{ appointment_stat[2] }}</div>
												</div>
												<div class="bpa-cb-stats-item --bpa-is-blue">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php echo esc_html($approved_label); ?></div>
													<div class="bpa-stats-item-val">{{ appointment_stat[1] }}</div>
												</div>
												<div class="bpa-cb-stats-item">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php echo esc_html($cancelled_label); ?></div>
													<div class="bpa-stats-item-val">{{ appointment_stat[3] }}</div>
												</div>
												<div class="bpa-cb-stats-item --bpa-is-danger">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php echo esc_html($rejected_label); ?></div>
													<div class="bpa-stats-item-val">{{ appointment_stat[4] }}</div>
												</div>
												<div class="bpa-cb-stats-item --bpa-is-main-green">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php echo esc_html($completed_label); ?></div>
													<div class="bpa-stats-item-val">{{ appointment_stat[6] }}</div>
												</div>
												<div class="bpa-cb-stats-item --bpa-is-brown">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php echo esc_html($noshow_label); ?></div>
													<div class="bpa-stats-item-val">{{ appointment_stat[5] }}</div>
												</div>
											</div>
										</el-col>
									</el-row>
								</div>
								<div class="bpa-ciw__grid-listing">
									<div class="bpa-ciw-gl__item">
										<h3 class="bpa-page-heading"><?php esc_html_e( 'Appointments Summary', 'bookingpress-appointment-booking' ); ?></h3>
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
												<el-container class="bpa-table-container">
													<el-table ref="multipleTable" class="bpa-manage-appointment-items" :data="items" fit="false">
														<el-table-column prop="booking_id" min-width="30" label="<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>">
															<template slot-scope="scope">
																<span>#{{ scope.row.booking_id }}</span>
															</template>
														</el-table-column>
														<el-table-column prop="appointment_date" min-width="120" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable>
															<template slot-scope="scope">
																<label class="bpa-item__date-col">{{ scope.row.appointment_date }}</label>
																<el-tooltip content="<?php esc_html_e('Rescheduled', 'bookingpress-appointment-booking'); ?>" placement="top" v-if="scope.row.is_rescheduled == 1">
																	<span class="material-icons-round bpa-rescheduled-appointment-icon" v-if="scope.row.is_rescheduled == 1">update</span>
																</el-tooltip>
															</template>
														</el-table-column>
														<el-table-column prop="customer_name" min-width="90" label="<?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
														<?php if ( ! $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) ) { ?>
														<el-table-column prop="staff_member_name" min-width="90" label="<?php echo esc_html($bookingpress_singular_staffmember_name); ?>" sortable v-if="is_staffmember_activated == 1"></el-table-column>
														<?php } ?>
														<el-table-column prop="service_name" min-width="110" label="<?php esc_html_e( 'Service', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
														<el-table-column prop="appointment_duration" min-width="70" label="<?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
														<el-table-column prop="appointment_status" min-width="80" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
															<template slot-scope="scope">
																<el-tag class="bpa-front-pill " :class="((scope.row.appointment_status == '2') ? '--warning' : '') || (scope.row.appointment_status == '3' ? '--info' : '') || (scope.row.appointment_status == '1' ? '--approved' : '') || (scope.row.appointment_status == '4' ? '--rejected' : '') || (scope.row.appointment_status == '5' ? '--no-show' : '') || (scope.row.appointment_status == '6' ? '--completed' : '') " >{{ scope.row.appointment_status_label }}</el-tag>
															</template>
														</el-table-column>
														<el-table-column prop="appointment_payment" min-width="100" label="<?php esc_html_e( 'Payment', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="payment_numberic_amount">
															<template slot-scope="scope">
																<div class="bpa-apc__amount-row">
																	<div class="bpa-apc__ar-body">
																		<span class="bpa-apc__amount" v-if="scope.row.bookingpress_is_deposit_enable == 1">{{ scope.row.bookingpress_deposit_amt_with_currency }}</span>
																		<span class="bpa-apc__amount" v-else>{{ scope.row.bookingpress_final_total_amt_with_currency }}</span>
																		<span v-if="scope.row.bookingpress_is_deposit_enable == 1" class="bpa-is-deposit-payment-val"><?php esc_html_e('of', 'bookingpress-appointment-booking'); ?> {{ scope.row.bookingpress_final_total_amt_with_currency }}</span>
																	</div>
																	<div class="bpa-apc__ar-icons">
																		<el-tooltip content="<?php esc_html_e('Cart Transaction', 'bookingpress-appointment-booking'); ?>" placement="top" v-if="scope.row.bookingpress_is_cart == 1">
																			<span class="material-icons-round bpa-appointment-cart-icon" v-if="scope.row.bookingpress_is_cart == 1">shopping_cart</span>
																		</el-tooltip>
																		<el-tooltip content="<?php esc_html_e('Deposit', 'bookingpress-appointment-booking'); ?>" placement="top" v-if="scope.row.bookingpress_is_deposit_enable == 1">
																			<span class="bpa-apc__deposit-icon" v-if="scope.row.bookingpress_is_deposit_enable == 1">
																				<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
																					<path d="M16.9596 12.2237C16.8902 12.0273 16.746 11.8662 16.5583 11.7756C16.3706 11.685 16.1548 11.6723 15.9578 11.7402L13.7872 12.4116C13.2376 12.9125 13.0288 12.7838 9.00068 12.7838C8.90842 12.7838 8.81994 12.7471 8.75471 12.6819C8.68947 12.6167 8.65282 12.5282 8.65282 12.4359C8.65282 12.3437 8.68947 12.2552 8.75471 12.1899C8.81994 12.1247 8.90842 12.0881 9.00068 12.0881C13.1749 12.0881 13.0323 12.1681 13.3384 11.862C13.4551 11.7331 13.5206 11.5661 13.5228 11.3923C13.5228 11.2078 13.4495 11.0309 13.319 10.9004C13.1886 10.7699 13.0116 10.6966 12.8271 10.6966H9.10504C8.62152 10.6966 8.21801 10.0009 6.80919 10.0009H4.47856V13.6256L4.92729 13.8795C6.20362 14.6153 7.63128 15.0496 9.10115 15.149C10.571 15.2485 12.0442 15.0106 13.408 14.4535L16.5387 13.1908C16.7162 13.1104 16.8576 12.967 16.9354 12.7883C17.0132 12.6096 17.0218 12.4084 16.9596 12.2237ZM1 14.523H3.78285V9.30521H1V14.523ZM2.0714 12.9994C2.09103 12.9518 2.12099 12.9092 2.1591 12.8746C2.19722 12.84 2.24255 12.8142 2.29181 12.7993C2.34107 12.7843 2.39304 12.7805 2.44398 12.788C2.49491 12.7956 2.54353 12.8143 2.58633 12.8429C2.62913 12.8716 2.66504 12.9093 2.69147 12.9535C2.71791 12.9977 2.7342 13.0472 2.73919 13.0984C2.74417 13.1497 2.73771 13.2014 2.72028 13.2499C2.70285 13.2983 2.67489 13.3423 2.6384 13.3786C2.58145 13.4353 2.50661 13.4705 2.42662 13.4783C2.34663 13.4861 2.26641 13.4659 2.1996 13.4213C2.13279 13.3766 2.08351 13.3102 2.06014 13.2333C2.03677 13.1564 2.04074 13.0737 2.0714 12.9994ZM11.4357 8.95736C12.1237 8.95736 12.7962 8.75334 13.3683 8.37112C13.9403 7.98889 14.3862 7.44561 14.6494 6.80999C14.9127 6.17437 14.9816 5.47494 14.8474 4.80017C14.7132 4.1254 14.3819 3.50558 13.8954 3.01909C13.4089 2.53261 12.7891 2.20131 12.1143 2.06709C11.4395 1.93286 10.7401 2.00175 10.1045 2.26504C9.46886 2.52832 8.92558 2.97417 8.54336 3.54622C8.16113 4.11827 7.95711 4.79081 7.95711 5.4788C7.95711 6.40137 8.3236 7.28616 8.97596 7.93851C9.62831 8.59087 10.5131 8.95736 11.4357 8.95736ZM11.7835 5.82666H11.0878C10.811 5.82666 10.5456 5.71671 10.3499 5.521C10.1542 5.3253 10.0442 5.05986 10.0442 4.78309C10.0442 4.50632 10.1542 4.24088 10.3499 4.04518C10.5456 3.84947 10.811 3.73952 11.0878 3.73952V3.39167C11.0878 3.29941 11.1245 3.21093 11.1897 3.1457C11.2549 3.08046 11.3434 3.04381 11.4357 3.04381C11.5279 3.04381 11.6164 3.08046 11.6816 3.1457C11.7469 3.21093 11.7835 3.29941 11.7835 3.39167V3.73952H12.4792C12.5715 3.73952 12.66 3.77617 12.7252 3.84141C12.7904 3.90664 12.8271 3.99512 12.8271 4.08738C12.8271 4.17964 12.7904 4.26812 12.7252 4.33335C12.66 4.39859 12.5715 4.43524 12.4792 4.43524H11.0878C10.9956 4.43524 10.9071 4.47188 10.8418 4.53712C10.7766 4.60236 10.74 4.69083 10.74 4.78309C10.74 4.87535 10.7766 4.96383 10.8418 5.02906C10.9071 5.0943 10.9956 5.13095 11.0878 5.13095H11.7835C12.0603 5.13095 12.3257 5.24089 12.5214 5.4366C12.7171 5.63231 12.8271 5.89774 12.8271 6.17451C12.8271 6.45128 12.7171 6.71672 12.5214 6.91243C12.3257 7.10813 12.0603 7.21808 11.7835 7.21808V7.56594C11.7835 7.65819 11.7469 7.74667 11.6816 7.81191C11.6164 7.87714 11.5279 7.91379 11.4357 7.91379C11.3434 7.91379 11.2549 7.87714 11.1897 7.81191C11.1245 7.74667 11.0878 7.65819 11.0878 7.56594V7.21808H10.3921C10.2998 7.21808 10.2114 7.18143 10.1461 7.1162C10.0809 7.05096 10.0442 6.96248 10.0442 6.87022C10.0442 6.77797 10.0809 6.68949 10.1461 6.62425C10.2114 6.55902 10.2998 6.52237 10.3921 6.52237H11.7835C11.8758 6.52237 11.9643 6.48572 12.0295 6.42049C12.0947 6.35525 12.1314 6.26677 12.1314 6.17451C12.1314 6.08226 12.0947 5.99378 12.0295 5.92854C11.9643 5.86331 11.8758 5.82666 11.7835 5.82666Z" />
																				</svg>
																			</span>
																		</el-tooltip>
																	</div>
																</div>
															</template>
														</el-table-column>
													</el-table>
												</el-container>
											</el-col>
										</el-row>
										<el-row class="bpa-pagination" type="flex" v-if="items.length > 0">
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" >
												<div class="bpa-pagination-left">
													<p><?php esc_html_e('Showing', 'bookingpress-appointment-booking'); ?>&nbsp;<strong><u>{{ items.length }}</u></strong>&nbsp;<p><?php esc_html_e('out of', 'bookingpress-appointment-booking'); ?></p>&nbsp;<strong>{{ appointment_total_items }}</strong></p>
													<div class="bpa-pagination-per-page">
														<p><?php esc_html_e('Per Page', 'bookingpress-appointment-booking'); ?></p>
														<el-select v-model="appointment_pagination_length" placeholder="Select" class="bpa-form-control" popper-class="bpa-pagination-dropdown" @change="change_appointment_PaginationSize($event)">
															<el-option v-for="item in pagination_val" :key="item.text" :label="item.text" :value="item.value"></el-option>
														</el-select>
													</div>
												</div>
											</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
												<el-pagination layout="prev, pager, next" @size-change="handle_appointment_size_change" @current-change="handle_current_change" :current-page.sync="appointment_current_page" :total="appointment_total_items" :page-sizes="appointment_pagination_length" :page-size="appointment_per_page"></el-pagination>
											</el-col>	
										</el-row>
									</div>
								</div>
							</div>
						</div>
					</div>
				</el-tab-pane>
				<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body">
					<span slot="label">						
						<?php esc_html_e('Revenue Report', 'bookingpress-appointment-booking'); ?>
					</span>
					<div class="bpa-general-settings-tabs--pb__card">
						<div class="bpa-rb-tab-content__body">
							<h3 class="bpa-page-heading"><?php esc_html_e( 'Revenue Report', 'bookingpress-appointment-booking' ); ?></h3>
							<div class="bpa-rb-chart-item-wrapper">
								<div class="bpa-ciw__filter-row">
									<el-row type="flex">
										<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-ciw-fr--left">
											<el-row type="flex" :gutter="24">
												<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="10">
													<el-select class="bpa-form-control" v-model="revenue_payment_gateway_filter" filterable placeholder="<?php esc_html_e( 'Select Payment Gateway', 'bookingpress-appointment-booking' ); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-revenue-report-status-dropdown" @change="select_revenue_report_filter">
														<?php
															foreach($bookingpress_revenue_filter_payment_gateway_list as $payment_gateway_key => $payment_gateway_val){
																$bookingpress_gateway_val = $payment_gateway_val['value'];
																$bookingpress_gateway_text = $payment_gateway_val['text'];
																?>
														<el-option label="<?php echo esc_html($bookingpress_gateway_text); ?>" value="<?php echo esc_html($bookingpress_gateway_val); ?>"></el-option>
																<?php
															}
														?>
													</el-select>
												</el-col>
											</el-row>
										</el-col>
										<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-ciw-fr--right">
											<el-date-picker @focus="bookingpress_remove_date_range_picker_focus" ref="bookingpress_custom_filter_rangepicker" v-model="revenue_custom_filter_val" class="bpa-form-control bpa-form-control--date-range-picker" format="<?php echo esc_html($bookingpress_common_date_format); ?>" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e( 'End Date', 'bookingpress-appointment-booking'); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-date-range-picker__is-filter-enabled" range-separator="-" :picker-options="bookingpress_picker_options" @change="select_revenue_report_filter" value-format="yyyy-MM-dd"></el-date-picker>
										</el-col>
									</el-row>
								</div>
								<div class="bpa-ciw__chart-body">
									<el-row type="flex" :gutter="40">
										<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="18">
											<div class="bpa-cb__content">
												<canvas id="bookingpress_revenue_charts"></canvas>
											</div>
										</el-col>
										<el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="06">
											<div class="bpa-cb__chart-stats">
												<h4 class="bpa-cb-stats-title"><?php esc_html_e('Quick Stats', 'bookingpress-appointment-booking'); ?></h4>
												<div class="bpa-cb-stats-item --bpa-is-main-green">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php echo esc_html($paid_label); ?></div>
													<div class="bpa-stats-item-val">{{ revenue_stat[1] }}</div>
												</div>
												<div class="bpa-cb-stats-item --bpa-is-secondary">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php echo esc_html($payment_pending_label); ?></div>
													<div class="bpa-stats-item-val">{{ revenue_stat[2] }}</div>
												</div>
												<div class="bpa-cb-stats-item --bpa-is-danger">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php echo esc_html($refunded_label); ?></div>
													<div class="bpa-stats-item-val">{{ revenue_stat[3] }}</div>
												</div>
												<div class="bpa-cb-stats-item --bpa-is-blue">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php echo esc_html($partially_paid_label); ?></div>
													<div class="bpa-stats-item-val">{{ revenue_stat[4] }}</div>
												</div>
											</div>
										</el-col>
									</el-row>
								</div>
								<div class="bpa-ciw__grid-listing">
									<div class="bpa-ciw-gl__item">
										<h3 class="bpa-page-heading"><?php esc_html_e( 'Revenue Summary', 'bookingpress-appointment-booking' ); ?></h3>
										<el-row type="flex" v-if="revenue_items.length == 0">
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
										<el-row v-if="revenue_items.length > 0">
											<el-col>
												<el-container class="bpa-table-container">
													<el-table ref="multipleTable" :data="revenue_items" class="bpa-manage-payment-items">
														<el-table-column prop="payment_date" min-width="80" label="<?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
														<el-table-column prop="payment_customer" min-width="100" label="<?php esc_html_e( 'Customer', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
														<el-table-column prop="staff_member_name" min-width="100" label="<?php echo esc_html($bookingpress_singular_staffmember_name); ?>" sortable v-if="is_staffmember_activated == 1"></el-table-column>
														<el-table-column prop="payment_service" min-width="100" label="<?php 
															$bookingpress_payment_transaction_item_label = esc_html__( 'Service', 'bookingpress-appointment-booking' ); 
															$bookingpress_payment_transaction_item_label = apply_filters('bookingpress_payment_transaction_item_label',$bookingpress_payment_transaction_item_label);
															echo esc_html($bookingpress_payment_transaction_item_label);														
														?>" sortable></el-table-column>
														<el-table-column prop="payment_gateway" min-width="70" label="<?php esc_html_e( 'Method', 'bookingpress-appointment-booking' ); ?>">
															<template slot-scope="scope">
																<div class="bpa-mpg__body">
																	<p> {{scope.row.payment_gateway}}</p>
																	<span v-if="scope.row.payment_gateway == 'manual'">(<?php esc_html_e('paid by admin', 'bookingpress-appointment-booking'); ?>)</span>
																</div>
															</template>
														</el-table-column>
														<el-table-column prop="payment_status" min-width="80" label="<?php esc_html_e( 'Status', 'bookingpress-appointment-booking' ); ?>">
															<template slot-scope="scope">
																<el-tag class="bpa-front-pill " :class="((scope.row.payment_status == '2') ? '--warning' : '') || (scope.row.payment_status == '3' ? '--rejected' : '') || (scope.row.payment_status == '4' ? '--approved' : '') || (scope.row.payment_status == '1' ? '--completed' : '')" >{{ scope.row.payment_status_label }}</el-tag>
															</template>
														</el-table-column>
														<el-table-column prop="payment_amount" min-width="100" label="<?php esc_html_e( 'Amount', 'bookingpress-appointment-booking' ); ?>" sortable sort-by="payment_numberic_amount">
															<template slot-scope="scope">
																<div class="bpa-mpi__amount-row">
																	<div class="bpa-mpi__ar-body">
																		<span class="bpa-mpi__amount" v-if="scope.row.is_deposit_enable == '1'">{{ scope.row.deposit_amount_with_currency }}</span>
																		<span class="bpa-mpi__amount" v-else>{{ scope.row.total_amount_with_currency }}</span>
																		<span v-if="scope.row.is_deposit_enable == 1" class="bpa-is-deposit-payment-val"><?php esc_html_e('of', 'bookingpress-appointment-booking'); ?> {{ scope.row.total_amount_with_currency }}</span>
																	</div>
																	<div class="bpa-mpi__ar-icons">
																		<el-tooltip content="<?php esc_html_e('Cart Transaction', 'bookingpress-appointment-booking'); ?>" placement="top" v-if="scope.row.is_cart == '1'">
																			<span class="material-icons-round">shopping_cart</span>
																		</el-tooltip>
																		<?php do_action('bookingpress_revenue_list_extra_icons'); ?>
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
													</el-table>
												</el-container>
											</el-col>
										</el-row>
										<el-row class="bpa-pagination" type="flex" v-if="revenue_items.length > 0">
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" >
												<div class="bpa-pagination-left">
													<p><?php esc_html_e('Showing', 'bookingpress-appointment-booking'); ?>&nbsp;<strong><u>{{ revenue_items.length }}</u></strong>&nbsp;<p><?php esc_html_e('out of', 'bookingpress-appointment-booking'); ?></p>&nbsp;<strong>{{ revenue_total_items }}</strong></p>
													<div class="bpa-pagination-per-page">
														<p><?php esc_html_e('Per Page', 'bookingpress-appointment-booking'); ?></p>
														<el-select v-model="revenue_pagination_length" placeholder="Select" class="bpa-form-control" popper-class="bpa-pagination-dropdown" @change="change_revenue_PaginationSize($event)">
															<el-option v-for="item in pagination_val" :key="item.text" :label="item.text" :value="item.value"></el-option>
														</el-select>
													</div>
												</div>
											</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
												<el-pagination layout="prev, pager, next" @size-change="handle_revenue_size_change" @current-change="handle_revenue_current_change" :current-page.sync="revenue_current_page" :total="revenue_total_items" :page-sizes="revenue_pagination_length" :page-size="revenue_per_page"></el-pagination>
											</el-col>	
										</el-row>
									</div>
								</div>
							</div>
						</div>
					</div>
				</el-tab-pane>
				<el-tab-pane class="bpa-tabs--v_ls__tab-item--pane-body">
					<span slot="label">						
						<?php esc_html_e('Customers Report', 'bookingpress-appointment-booking'); ?>
					</span>
					<div class="bpa-general-settings-tabs--pb__card">
						<div class="bpa-rb-tab-content__body">
							<h3 class="bpa-page-heading"><?php esc_html_e( 'Customers Report', 'bookingpress-appointment-booking' ); ?></h3>
							<div class="bpa-rb-chart-item-wrapper">
								<div class="bpa-ciw__filter-row">
									<el-row type="flex">										
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-ciw-fr--right">
											<el-date-picker @focus="bookingpress_remove_date_range_picker_focus" ref="bookingpress_custom_filter_rangepicker" v-model="customer_custom_filter_val" class="bpa-form-control bpa-form-control--date-range-picker" format="<?php echo esc_html($bookingpress_common_date_format); ?>" type="daterange" start-placeholder="<?php esc_html_e('Start date', 'bookingpress-appointment-booking'); ?>" end-placeholder="<?php esc_html_e( 'End Date', 'bookingpress-appointment-booking'); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-date-range-picker__is-filter-enabled" range-separator="-" :picker-options="bookingpress_picker_options" @change="select_customer_report_filter" value-format="yyyy-MM-dd"></el-date-picker>
										</el-col>
									</el-row>
								</div>
								<div class="bpa-ciw__chart-body">
									<el-row type="flex" :gutter="40">
										<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="18">
											<div class="bpa-cb__content">
												<canvas id="bookingpress_customers_charts"></canvas>
											</div>
										</el-col>
										<el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="06">
											<div class="bpa-cb__chart-stats">
												<h4 class="bpa-cb-stats-title"><?php esc_html_e('Quick Stats', 'bookingpress-appointment-booking'); ?></h4>
												<div class="bpa-cb-stats-item --bpa-is-main-green">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php esc_html_e('Existing Customers', 'bookingpress-appointment-booking'); ?></div>
													<div class="bpa-stats-item-val">{{ existing_customers_count }}</div>
												</div>
												<div class="bpa-cb-stats-item --bpa-is-secondary">
													<div class="bpa-stats-item-label"><span class="bpa-sil__cirlce"></span><?php esc_html_e('New Customers', 'bookingpress-appointment-booking'); ?></div>
													<div class="bpa-stats-item-val">{{ new_customers_count }}</div>
												</div>
											</div>
										</el-col>
									</el-row>
								</div>
								<div class="bpa-ciw__grid-listing">
									<div class="bpa-ciw-gl__item">
										<h3 class="bpa-page-heading"><?php esc_html_e( 'Customer Summary', 'bookingpress-appointment-booking' ); ?></h3>
										<el-row type="flex" v-if="customer_items.length == 0">
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
										<el-row v-if="customer_items.length > 0">
											<el-col>
												<el-container class="bpa-table-container">
													<el-table ref="multipleTable" :data="customer_items">
														<el-table-column prop="customer_fullname" label="<?php esc_html_e( 'Full Name', 'bookingpress-appointment-booking' ); ?>" sortable>
															<template slot-scope="scope">														
																<el-image class="bpa-table-column-avatar" :src="scope.row.customer_avatar"></el-image>
																<label v-if="scope.row.customer_firstname != '' && scope.row.customer_lastname != ''">{{ scope.row.customer_firstname }} {{ scope.row.customer_lastname }}</label>
																<label v-else>{{ scope.row.customer_email }}</label>
															</template>
														</el-table-column>
														<el-table-column  prop="customer_email" label="<?php esc_html_e( 'Email', 'bookingpress-appointment-booking' ); ?>" sortable></el-table-column>
														<el-table-column  prop="customer_phone" label="<?php esc_html_e( 'Phone', 'bookingpress-appointment-booking' ); ?>"></el-table-column>
														<el-table-column  prop="customer_last_appointment" label="<?php esc_html_e( 'Last Appointment Booked', 'bookingpress-appointment-booking' ); ?>" sortable>	
														</el-table-column>
														<el-table-column align="center" prop="customer_total_appointment" label="<?php esc_html_e( 'Total Appointments', 'bookingpress-appointment-booking' ); ?>">
															<template slot-scope="scope">
																<label>{{ scope.row.customer_total_appointment }}</label>
															</template>						
														</el-table-column>
													</el-table>
												</el-container>
											</el-col>
										</el-row>
										<el-row class="bpa-pagination" type="flex" v-if="customer_items.length > 0">
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" >
												<div class="bpa-pagination-left">
													<p><?php esc_html_e('Showing', 'bookingpress-appointment-booking'); ?>&nbsp;<strong><u>{{ customer_items.length }}</u></strong>&nbsp;<p><?php esc_html_e('out of', 'bookingpress-appointment-booking'); ?></p>&nbsp;<strong>{{ customer_total_items }}</strong></p>
													<div class="bpa-pagination-per-page">
														<p><?php esc_html_e('Per Page', 'bookingpress-appointment-booking'); ?></p>
														<el-select v-model="customer_pagination_length" placeholder="Select" class="bpa-form-control" popper-class="bpa-pagination-dropdown" @change="change_customer_PaginationSize($event)">
															<el-option v-for="item in pagination_val" :key="item.text" :label="item.text" :value="item.value"></el-option>
														</el-select>
													</div>
												</div>
											</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
												<el-pagination layout="prev, pager, next" @size-change="handle_customer_size_change" @current-change="handle_customer_current_change" :current-page.sync="customer_current_page" :total="customer_total_items" :page-sizes="customer_pagination_length" :page-size="customer_per_page"></el-pagination>
											</el-col>	
										</el-row>
									</div>
								</div>
							</div>
						</div>
					</div>
				</el-tab-pane>
				<?php
				do_action('bookingpress_add_advanced_report_section');
				?>
			</el-tabs>
		</div>
	</div>
</e/-main>