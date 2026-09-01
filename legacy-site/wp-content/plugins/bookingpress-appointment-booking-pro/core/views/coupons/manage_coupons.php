<?php
	global $bookingpress_ajaxurl,$bookingpress_common_date_format;
?>

<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">
	<?php 
		  $bookingpress_before_coupon_list_files = array();
		  $bookingpress_before_coupon_list_files = apply_filters('bookingpress_before_coupon_list_load',$bookingpress_before_coupon_list_files); 
		  if(!empty($bookingpress_before_coupon_list_files)){
			foreach($bookingpress_before_coupon_list_files as $load_file){
				include $load_file;
			}	
		  }
	?>	
	<el-row type="flex" class="bpa-mlc-head-wrap">
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
			<h1 class="bpa-page-heading"><?php esc_html_e( 'Coupon Management', 'bookingpress-appointment-booking' ); ?></h1>
		</el-col>
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
			<div class="bpa-hw-right-btn-group">
				<el-button class="bpa-btn bpa-btn--primary" @click="openCouponModal">
					<span class="material-icons-round">add</span>
					<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
				</el-button>
			</div>
		</el-col>
	</el-row>
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>
	<div id="bpa-main-container">
		<div class="bpa-table-filter">
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
						<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="openCouponModal"> 
							<span class="material-icons-round">add</span> 
							<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</div>				
				</div>
			</el-col>
		</el-row>
		<el-row v-if="items.length > 0">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<el-container class="bpa-table-container">
					<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
						<div class="bpa-back-loader"></div>
					</div>
					<el-table ref="multipleTable" :data="items" @selection-change="handleSelectionChange" class="bpa-tc__coupons-module" :row-class-name="bookingpress_row_classname">
						<el-table-column type="selection"></el-table-column>
						<el-table-column prop="coupon_title" label="<?php esc_html_e( 'Title', 'bookingpress-appointment-booking' ); ?>" row-class-name="bpa-cm__code-col">
							<template slot-scope="scope">
								<label>{{ scope.row.coupon_title }}</label>								
							</template>
						</el-table-column>
						<el-table-column prop="coupon_code" label="<?php esc_html_e( 'Coupon Code', 'bookingpress-appointment-booking' ); ?>" class-name="bpa-coupon-code-display" row-class-name="bpa-cm__code-col bpa-coupon-code-display">
							<template slot-scope="scope">
								<label>{{ scope.row.coupon_code }}</label>
								<span class="material-icons-round bpa-cm__code-col-icon" @click="bookingpress_copy_code(scope.row.coupon_code)">content_copy</span>
							</template>
						</el-table-column>
						<el-table-column prop="coupon_discount_display" label="<?php esc_html_e( 'Discount', 'bookingpress-appointment-booking' ); ?>">
							<template slot-scope="scope">
								<label>
									{{ scope.row.coupon_discount_display }}<span v-if="scope.row.coupon_discount_type == 'Percentage'">&#37;</span>									
								</label>
							</template>
						</el-table-column>
						<el-table-column prop="coupon_period" label="<?php esc_html_e( 'Coupon Duration', 'bookingpress-appointment-booking' ); ?>">
							<template slot-scope="scope">
								<label v-if="scope.row.coupon_period == 'date_range'">
									{{ scope.row.coupon_start_date_formated }} <br/> {{ scope.row.coupon_end_date_formated }}
								</label>
								<label v-else>
									<?php esc_html_e( 'Never Expires', 'bookingpress-appointment-booking' ); ?>
								</label>
							</template>
						</el-table-column>
						<el-table-column prop="coupon_services_display" label="<?php esc_html_e( 'Services', 'bookingpress-appointment-booking' ); ?>">
							<template slot-scope="scope">
								<label v-if="scope.row.coupon_services_display != ''" v-html="scope.row.coupon_services_display"></label>
								<label v-else><?php esc_html_e( 'All Services', 'bookingpress-appointment-booking' ); ?></label>
							</template>
						</el-table-column>
						<!--<el-table-column prop="coupon_allowed_uses" label="<?php esc_html_e( 'Allowed Uses', 'bookingpress-appointment-booking' ); ?>"></el-table-column>-->
						<el-table-column prop="coupon_total_used" label="<?php esc_html_e( 'Usages', 'bookingpress-appointment-booking' ); ?>">
							<template slot-scope="scope">
								<label class="bpa-coupon-usages">{{ scope.row.coupon_total_used }}/<span v-if="scope.row.coupon_allowed_uses != 0">{{scope.row.coupon_allowed_uses}}</span><span class="bpa-infinate-symbol" v-else>&#8734;</span></label>
								<div class="bpa-table-actions-wrap">
									<div class="bpa-table-actions">
										<el-tooltip effect="dark" content="" placement="top" open-delay="300" v-if="scope.row.coupon_status == false">
											<div slot="content">
												<span><?php esc_html_e('Activate', 'bookingpress-appointment-booking'); ?></span>
											</div>
											<el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="bookingpress_coupon_status(scope.row.coupon_id)">
												<span class="material-icons-round">visibility</span>
											</el-button> 
										</el-tooltip>
										<el-tooltip effect="dark" content="" placement="top" open-delay="300" v-if="scope.row.coupon_status == true">
											<div slot="content">
												<span><?php esc_html_e('Deactivate', 'bookingpress-appointment-booking'); ?></span>
											</div>
											<el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="bookingpress_coupon_status(scope.row.coupon_id)">
												<span class="material-icons-round">visibility_off</span>
											</el-button>
										</el-tooltip>
										<el-tooltip effect="dark" content="" placement="top" open-delay="300">
											<div slot="content">
												<span><?php esc_html_e('Edit', 'bookingpress-appointment-booking'); ?></span>
											</div>
											<el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editCoupon(scope.row.coupon_id,scope.row)">
												<span class="material-icons-round">mode_edit</span>
											</el-button>
										</el-tooltip>
										<el-tooltip effect="dark" content="" placement="top" open-delay="300">
											<div slot="content">
												<span><?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?></span>
											</div>
											<el-popconfirm 
												cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
												confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
												icon="false" 
												title="<?php esc_html_e( 'Are you sure you want to delete this coupon?', 'bookingpress-appointment-booking' ); ?>" 
												@confirm="deleteCoupon(scope.row.coupon_id)"
												confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
												cancel-button-type="bpa-btn bpa-btn__small">
												<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
													<span class="material-icons-round">delete</span>
												</el-button>
											</el-popconfirm>
										</el-tooltip>
									</div>
								</div>
							</template>
						</el-table-column>
					</el-table>				
				</el-container>
			</el-col>
		</el-row>
		<el-row class="bpa-pagination" type="flex" v-if="items.length > 0"> <!-- Pagination -->
			<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" >
				<div class="bpa-pagination-left">
					<p><?php esc_html_e( 'Showing', 'bookingpress-appointment-booking' ); ?> <strong><u>{{ items.length }}</u> </strong><p><?php esc_html_e( 'out of', 'bookingpress-appointment-booking' ); ?></p> <strong>{{ totalItems }}</strong></p>
					<div class="bpa-pagination-per-page">
						<p><?php esc_html_e( 'Per Page', 'bookingpress-appointment-booking' ); ?></p>
						<el-select v-model="pagination_length_val" placeholder="Select" @change="changePaginationSize($event)" class="bpa-form-control" popper-class="bpa-pagination-dropdown">
							<el-option v-for="item in pagination_val" :key="item.text" :label="item.text" :value="item.value"></el-option>
						</el-select>
					</div>
				</div>
			</el-col>
			<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-pagination-nav">
				<el-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange" :current-page.sync="currentPage" layout="prev, pager, next" :total="totalItems" :page-sizes="pagination_length" :page-size="perPage"></el-pagination>
			</el-col>
			<el-container v-if="multipleSelection.length > 0" class="bpa-default-card bpa-bulk-actions-card">
				<el-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="closeBulkAction">
					<span class="material-icons-round">close</span>
				</el-button>
				<el-row type="flex" class="bpa-bac__wrapper">
					<el-col class="bpa-bac__left-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
						<span class="material-icons-round">check_circle</span>
						<p>{{ multipleSelection.length }}<?php esc_html_e( ' Items Selected', 'bookingpress-appointment-booking' ); ?></p>
					</el-col>
					<el-col class="bpa-bac__right-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
						<el-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?>">
							<el-option v-for="item in bulk_options" :key="item.value" :label="item.label" :value="item.value"></el-option>
						</el-select>
						<el-button @click="bulk_actions" class="bpa-btn bpa-btn--primary bpa-btn__medium">
							<?php esc_html_e( 'Go', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</el-col>
				</el-row>
			</el-container>		
		</el-row>
	</div>
	<el-dialog id="coupon_add_modal" custom-class="bpa-dialog bpa-dialog--fullscreen bpa-coupon-module-dialog--fullscreen bpa--is-page-scrollable-tablet" modal-append-to-body=false :visible.sync="open_coupon_modal" :before-close="closeCouponModal" fullscreen=true :close-on-press-escape="close_modal_on_esc">
		<div class="bpa-dialog-heading">
			<el-row type="flex">
				<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
			<h1 class="bpa-page-heading" v-if="coupon_details.update_id == 0"><?php esc_html_e( 'Add Coupon', 'bookingpress-appointment-booking' ); ?></h1>
			<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Coupon', 'bookingpress-appointment-booking' ); ?></h1>
				</el-col>
				<el-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">
					<el-button class="bpa-btn bpa-btn--primary " :class="is_display_save_loader == '1' ? 'bpa-btn--is-loader' : ''" @click="saveCouponDetails('coupon_details')" :disabled="is_disabled" >
						<span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
						<div class="bpa-btn--loader__circles">
							<div></div>
							<div></div>
							<div></div>
						</div>
					</el-button> 
					<el-button class="bpa-btn" @click="closeCouponModal()"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
				</el-col>
			</el-row>
		</div>	
		<div class="bpa-dialog-body">
			<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
				<div class="bpa-back-loader"></div>
			</div>
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<div class="bpa-default-card bpa-db-card">
							<el-form ref="coupon_details" :rules="rules" :model="coupon_details" label-position="top" @submit.native.prevent>
								<template>
									<div class="bpa-form-body-row">
										<el-row :gutter="32">
											<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
												<el-form-item prop="coupon_period_type">
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'Coupon Period Type', 'bookingpress-appointment-booking' ); ?></span>
													</template>
													<el-radio v-model="coupon_details.coupon_period_type" label="date_range"><?php esc_html_e( 'Date Range', 'bookingpress-appointment-booking' ); ?></el-radio>
													<el-radio v-model="coupon_details.coupon_period_type" label="unlimited"><?php esc_html_e( 'Never Expires', 'bookingpress-appointment-booking' ); ?></el-radio>
												</el-form-item>
											</el-col>		
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
												<el-form-item prop="coupon_title">
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'Title', 'bookingpress-appointment-booking' ); ?></span>
													</template>
													<el-input class="bpa-form-control" placeholder="<?php esc_html_e( 'Enter coupon title', 'bookingpress-appointment-booking' ); ?>" v-model="coupon_details.coupon_title" id="coupon_title" name="coupon_title"></el-input>
												</el-form-item>
											</el-col>																		
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8" v-if="coupon_details.coupon_period_type == 'date_range'">
												<el-form-item prop="coupon_start_date">
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'Start Date', 'bookingpress-appointment-booking' ); ?></span>
													</template>
													<el-date-picker class="bpa-form-control bpa-form-control--date-picker" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" v-model="coupon_details.coupon_start_date" type="date" placeholder="<?php esc_html_e( 'Select Start Date', 'bookingpress-appointment-booking' ); ?>" :picker-options="datepicker_disabled_dates" popper-class="bpa-el-datepicker-widget-wrapper" :append-to-body="false" value-format="yyyy-MM-dd"></el-date-picker>
												</el-form-item>
											</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8" v-if="coupon_details.coupon_period_type == 'date_range'">
												<el-form-item prop="coupon_end_date">
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'End Date', 'bookingpress-appointment-booking' ); ?></span>
													</template>
													<el-date-picker class="bpa-form-control bpa-form-control--date-picker" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" v-model="coupon_details.coupon_end_date" type="date" placeholder="<?php esc_html_e( 'Select End Date', 'bookingpress-appointment-booking' ); ?>" :picker-options="datepicker_disabled_dates" popper-class="bpa-el-datepicker-widget-wrapper" :append-to-body="false" value-format="yyyy-MM-dd"></el-date-picker>
												</el-form-item>
											</el-col>										
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
												<el-form-item prop="coupon_code">
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'Coupon Code', 'bookingpress-appointment-booking' ); ?></span>
													</template>
													<el-row :gutter="24">
														<el-col :xs="16" :sm="18" :md="18" :lg="18" :xl="18">
															<el-input class="bpa-form-control" v-model="coupon_details.coupon_code" id="coupon_code" name="coupon_code" placeholder="<?php esc_html_e( 'Enter Coupon Code', 'bookingpress-appointment-booking' ); ?>"></el-input>
														</el-col>
														<el-col :xs="8" :sm="6" :md="6" :lg="6" :xl="6">
															<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium bpa-btn--full-width" @click="generate_coupon()">
																<span class="bpa-btn__label"><?php esc_html_e( 'Generate', 'bookingpress-appointment-booking' ); ?></span>
															</el-button>
														</el-col>
													</el-row>
												</el-form-item>
											</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
												<el-form-item prop="coupon_discount">
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'Discount', 'bookingpress-appointment-booking' ); ?></span>
													</template>
													<el-row :gutter="10">
														<el-col :xs="18" :sm="18" :md="18" :lg="16" :xl="18">
															<el-input class="bpa-form-control" placeholder="<?php esc_html_e( 'Enter discount', 'bookingpress-appointment-booking' ); ?>" v-model="coupon_details.coupon_discount" id="coupon_discount" name="coupon_discount"></el-input>
														</el-col>
														<el-col :xs="6" :sm="6" :md="6" :lg="8" :xl="6">
															<el-select class="bpa-form-control" v-model="coupon_details.coupon_discount_type" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-coupon-services-selection">
																<el-option value="Fixed" :label="default_currency_symbol">{{default_currency_symbol}}</el-option>
																<el-option value="Percentage" label="<?php esc_html_e( '%', 'bookingpress-appointment-booking' ); ?>"><?php esc_html_e( '%', 'bookingpress-appointment-booking' ); ?></el-option>
															</el-select>
														</el-col>
													</el-row>																								
												</el-form-item>
											</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
												<el-form-item prop="coupon_services">
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'Select Services', 'bookingpress-appointment-booking' ); ?></span>
													</template>
													<el-select class="bpa-form-control" v-model="coupon_details.coupon_services" multiple filterable collapse-tags placeholder="<?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar bpa-coupon-services-selection">		
														<el-option-group v-for="item in coupon_services_list" :key="item.category_name" :label="item.category_name">
															<el-option v-for="cat_services in item.category_services" :key="cat_services.service_id" :label="cat_services.service_name" :value="cat_services.service_id"></el-option>
														</el-option-group>
													</el-select>
												</el-form-item>
											</el-col>
											<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="8">
												<el-form-item prop="coupon_allowed_uses">
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'No. of times uses allowed', 'bookingpress-appointment-booking' ); ?></span>
													</template>
														<el-input-number class="bpa-form-control bpa-form-control--number" v-model="coupon_details.coupon_allowed_uses" :min="0" step-strictly></el-input-number>
												</el-form-item>
											</el-col>
										</el-row>
									</div>
								</template>
							</el-form>
						</div>
					</el-col>
				</el-row>
			</div>
		</div>
	</el-dialog>	
	<?php 
		  $bookingpress_after_coupon_list_files = array();
		  $bookingpress_after_coupon_list_files = apply_filters('bookingpress_after_admin_coupon_list_load',$bookingpress_after_coupon_list_files); 
		  if(!empty($bookingpress_after_coupon_list_files)){
			foreach($bookingpress_after_coupon_list_files as $load_file){
				include $load_file;
			}	
		  }
	?>			
</el-main>