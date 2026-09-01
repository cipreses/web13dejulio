<?php
	global $bookingpress_ajaxurl, $bookingpress_common_date_format, $bookingpress_global_options;
	$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
	$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
	$bookingpress_plural_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) : esc_html_e('Staff Members', 'bookingpress-appointment-booking');

?>
<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">
	<el-row type="flex" class="bpa-mlc-head-wrap __services-screen">
		<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="bpa-mlc-left-heading">
			<h1 class="bpa-page-heading"><?php esc_html_e( 'Manage Services', 'bookingpress-appointment-booking' ); ?></h1>
		</el-col>
		<el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
			<div class="bpa-hw-right-btn-group">
				<el-button class="bpa-btn bpa-btn--primary" @click="open_add_service_modal('add')">
					<span class="material-icons-round">add</span>
					<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
				</el-button>
				<el-button class="bpa-btn bpa-btn--default" @click="open_manage_category_modal = true">
					<span class="material-icons-round">dns</span>
					<?php esc_html_e( 'Manage Categories', 'bookingpress-appointment-booking' ); ?>
				</el-button>
			</div>
		</el-col>
	</el-row>
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>
	<div id="bpa-main-container">
		<div class="bpa-table-filter">
			<el-row type="flex" :gutter="32">
				<el-col :xs="24" :sm="24" :md="24" :lg="9" :xl="10">
					<span class="bpa-form-label"><?php esc_html_e( 'Service Name', 'bookingpress-appointment-booking' ); ?></span>
					<el-input class="bpa-form-control" v-model="search_service_name"
						placeholder="<?php esc_html_e( 'Enter Service Name', 'bookingpress-appointment-booking' ); ?>">
					</el-input>
				</el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="9" :xl="10">
                    <span class="bpa-form-label"><?php esc_html_e('Service Category', 'bookingpress-appointment-booking'); ?></span>
					<el-select class="bpa-form-control" v-model="search_service_category"
						placeholder="<?php esc_html_e( 'Select Category', 'bookingpress-appointment-booking' ); ?>"
						:popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar">
						<el-option v-for="item in search_categories" :key="item.bookingpress_category_id"
							:label="item.bookingpress_category_name" :value="item.bookingpress_category_id"></el-option>
					</el-select>
				</el-col>
				<el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="4">
					<div class="bpa-tf-btn-group">
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
							<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width"
							@click="loadServices">
							<?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?>
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
							<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>"
								type="image/webp">
							<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
						</picture>
					</div>
					<div class="bpa-ev-right-content">
						<h4><?php esc_html_e( 'No Record Found!', 'bookingpress-appointment-booking' ); ?></h4>
						
						<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="open_add_service_modal('add')"> 
							<span class="material-icons-round">add</span>
							<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</div>
				</div>
			</el-col>
		</el-row>
		<el-container class="bpa-grid-list-container bpa-grid-list--service-page">
			<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
				<div class="bpa-back-loader"></div>
			</div>
			<el-row v-if="items.length > 0">
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-card bpa-card__heading-row">
						<el-row type="flex">
                            <el-col :xs="24" :sm="8" :md="8" :lg="8" :xl="6">
								<div class="bpa-card__item bpa-card__item--ischecbox">
									<el-checkbox v-model="is_multiple_checked" @change="selectAllServices($event)"></el-checkbox>
									<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Name', 'bookingpress-appointment-booking' ); ?></h4>
								</div>
							</el-col>
                            <el-col :xs="24" :sm="4" :md="4" :lg="4" :xl="6">
								<div class="bpa-card__item">
									<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Category', 'bookingpress-appointment-booking' ); ?></h4>
								</div>
							</el-col>
							<el-col :xs="24" :sm="6" :md="6" :lg="6" :xl="6">
								<div class="bpa-card__item">
									<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?></h4>
								</div>
							</el-col>
							<el-col :xs="24" :sm="6" :md="6" :lg="6" :xl="6">
								<div class="bpa-card__item">
									<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?></h4>
								</div>
							</el-col>
						</el-row>
					</div>
				</el-col>
				<draggable :list="items" :disabled="!enabled" class="list-group" ghost-class="ghost" @start="dragging = true" @end="updateServicePos($event)">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="items_data in items" :data-service_id="items_data.service_id">
						<div class="bpa-card bpa-card__body-row list-group-item" :class="(items_data.bookingpress_service_enabled == 'true') ? 'bookingpress_enabled_service' : 'bpa-card-body__disabled'">
							<div class="bpa-card__item--drag-icon-wrap">
								<span class="material-icons-round">drag_indicator</span>
							</div>
							<el-row type="flex">
								<el-col :xs="24" :sm="8" :md="8" :lg="8" :xl="6">
									<div class="bpa-card__item bpa-card__item--ischecbox">
										<el-tooltip effect="dark" content="" placement="top" v-if="items_data.service_bulk_action">
											<div slot="content">
												<span><?php esc_html_e( 'One or more appointments are associated with this service,', 'bookingpress-appointment-booking' ); ?></span><br/>
												<span><?php esc_html_e( 'so you will not be able to delete it', 'bookingpress-appointment-booking' ); ?></span>
											</div>
											<el-checkbox v-model="items_data.selected" :disabled=items_data.service_bulk_action @change="handleSelectionChange(event, $event, items_data.service_id)"></el-checkbox>
										</el-tooltip>
										<el-checkbox v-model="items_data.selected" :disabled=items_data.service_bulk_action @change="handleSelectionChange(event, $event, items_data.service_id)" v-else></el-checkbox>
										<img :src="items_data.service_img_details" alt="service-thumbnail" class="bpa-card__item--service-thumbnail" v-if="items_data.service_img_details != ''">
										<h4 class="bpa-card__item__heading is--body-heading"> <span v-html="items_data.service_name"></span> <span class="bpa-card__item--id">(<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>: {{ items_data.service_id }} )</span></h4>
										<el-popover placement="top-start" title="<?php esc_html_e('Extras', 'bookingpress-appointment-booking'); ?>" width="280" trigger="hover" popper-class="bpa-card-item-extra-popover" v-if="items_data.is_extra_enable == 1 && items_data.extra_services_total_counter != '0'">
											<div class="bpa-card-item-extra-content">
												<div class="bpa-cec__item" v-for="extra_service in items_data.extra_services">{{ extra_service.extra_service_name }}</div>
												<div class="bpa-cec__item" v-if="items_data.extra_services_remain_counter != '0'">+{{ items_data.extra_services_remain_counter }}</div>
											</div>
											<div slot="reference" class="bpa-card__item-extra-tooltip">
												<el-link class="bpa-iet__label">{{ items_data.extra_services_total_counter }}</el-link>
											</div>
										</el-popover>
									</div>
								</el-col>
								<el-col :xs="24" :sm="4" :md="4" :lg="4" :xl="6">
									<div class="bpa-card__item">
										<h4 class="bpa-card__item__heading is--body-heading">{{ items_data.service_category }}</h4>
									</div>
								</el-col>
								<el-col :xs="24" :sm="6" :md="6" :lg="6" :xl="6">
									<div class="bpa-card__item">
										<h4 class="bpa-card__item__heading is--body-heading">{{ items_data.service_duration }}</h4>
									</div>
								</el-col>
								<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
									<div class="bpa-card__item">
										<h4 class="bpa-card__item__heading is--body-heading">{{ items_data.service_price }}</h4>
									</div>
								</el-col>
							</el-row>
							<div class="bpa-table-actions-wrap">
								<div class="bpa-table-actions">
									<el-tooltip popper-class="bpa-tooltip-with-confirm" effect="dark" content="" placement="top" open-delay="300" v-if="items_data.bookingpress_service_enabled == 'true'">
										<div slot="content">
											<span><?php esc_html_e( 'Disable Service', 'bookingpress-appointment-booking' ); ?></span>
										</div>
										<el-popconfirm
											cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
											confirm-button-text='<?php esc_html_e( 'Disable', 'bookingpress-appointment-booking' ); ?>' 
											icon="false" 
											title="<?php esc_html_e( 'Are you sure you want to disable service?', 'bookingpress-appointment-booking' ); ?>" 
											@confirm="bookingpress_service_change_status(items_data.service_id, 'false')" 
											confirm-button-type="bpa-btn bpa-btn__small bpa-btn--secondary" 
											cancel-button-type="bpa-btn bpa-btn__small">
											<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __secondary">
												<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path d="M10 16.6668L9.28333 15.9502C8.3 14.9668 8.30833 13.3668 9.3 12.4002L10 11.7168C9.675 11.6835 9.43333 11.6668 9.16667 11.6668C6.94167 11.6668 2.5 12.7835 2.5 15.0002V16.6668H10ZM9.16667 10.0002C11.0083 10.0002 12.5 8.5085 12.5 6.66683C12.5 4.82516 11.0083 3.3335 9.16667 3.3335C7.325 3.3335 5.83333 4.82516 5.83333 6.66683C5.83333 8.5085 7.325 10.0002 9.16667 10.0002Z" />
													<path d="M16.9992 14.5006C16.9992 16.4334 15.4324 18.0002 13.4996 18.0002C11.5668 18.0002 10 16.4334 10 14.5006C10 12.5678 11.5668 11.001 13.4996 11.001C15.4324 11.001 16.9992 12.5678 16.9992 14.5006ZM11.1199 14.5006C11.1199 15.8149 12.1853 16.8803 13.4996 16.8803C14.8139 16.8803 15.8793 15.8149 15.8793 14.5006C15.8793 13.1863 14.8139 12.1209 13.4996 12.1209C12.1853 12.1209 11.1199 13.1863 11.1199 14.5006Z" />
													<rect x="14.9219" y="11.7139" width="1.16654" height="5.83268" transform="rotate(35.8094 14.9219 11.7139)"/>
												</svg>
											</el-button>
										</el-popconfirm>
									</el-tooltip>
									<el-tooltip effect="dark" content="" placement="top" open-delay="300" v-else>
										<div slot="content">
											<span><?php esc_html_e( 'Enable Service', 'bookingpress-appointment-booking' ); ?></span>
										</div>
										<el-popconfirm
											cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
											confirm-button-text='<?php esc_html_e( 'Enable', 'bookingpress-appointment-booking' ); ?>' 
											icon="false" 
											title="<?php esc_html_e( 'Are you sure you want to enable service?', 'bookingpress-appointment-booking' ); ?>" 
											@confirm="bookingpress_service_change_status(items_data.service_id, 'true')"
											confirm-button-type="bpa-btn bpa-btn__small bpa-btn--secondary" 
											cancel-button-type="bpa-btn bpa-btn__small">
											<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __secondary">
												<span class="material-icons-round">how_to_reg</span>
											</el-button>
										</el-popconfirm>
									</el-tooltip>	
									<el-tooltip effect="dark" content="" placement="top" open-delay="300">
										<div slot="content">
											<span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
										</div>
										<el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editServiceData(items_data.service_id)">
											<span class="material-icons-round">mode_edit</span>
										</el-button>
									</el-tooltip>
									<el-tooltip effect="dark" content="" placement="top" open-delay="300">
										<div slot="content">
											<span><?php esc_html_e( 'Duplicate', 'bookingpress-appointment-booking' ); ?></span>
										</div>
										<el-button class="bpa-btn bpa-btn--icon-without-box __secondary" @click.native.prevent="bookingpress_duplicate_service(items_data.service_id)">
											<span class="material-icons-round">queue</span>
										</el-button>
									</el-tooltip>
									<el-tooltip effect="dark" content="" placement="top" open-delay="300">
										<div slot="content">
											<span><?php esc_html_e( 'Shift Management', 'bookingpress-appointment-booking' ); ?></span>
										</div>
										<el-button class="bpa-btn bpa-btn--icon-without-box" @click="bookingpress_open_shift_management_modal(items_data.service_id, items_data.service_name, items_data.is_special_workhour_configure)">
											<span class="material-icons-round">date_range</span>
										</el-button>
										</el-tooltip>    
									<el-tooltip popper-class="bpa-tooltip-with-confirm" effect="dark" content="" placement="top" open-delay="300">
										<div slot="content">
											<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
										</div>
										<el-popconfirm 
											confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
											cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
											icon="false" 
											title="<?php esc_html_e( 'Are you sure you want to delete this service?', 'bookingpress-appointment-booking' ); ?>" 
											@confirm="deleteService(items_data.service_id)" 
											confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
											cancel-button-type="bpa-btn bpa-btn__small">
											<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
												<span class="material-icons-round">delete</span>
											</el-button>
										</el-popconfirm>
									</el-tooltip>
								</div>
							</div>
						</div>
					</el-col>
				</draggable>
			</el-row>
		</el-container>
		<el-row class="bpa-pagination" v-if="items.length > 0">
			<el-container v-if="multipleSelection.length > 0" class="bpa-default-card bpa-bulk-actions-card">
				<el-button class="bpa-btn bpa-btn--icon-without-box bpa-bac__close-icon" @click="clearBulkAction">
					<span class="material-icons-round">close</span>
				</el-button>
				<el-row type="flex" class="bpa-bac__wrapper">
					<el-col class="bpa-bac__left-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
						<span class="material-icons-round">check_circle</span>
									<b>{{ multipleSelection.length }}<?php esc_html_e( ' Items Selected', 'bookingpress-appointment-booking' ); ?></b>
					</el-col>
					<el-col class="bpa-bac__right-area" :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
						<el-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?>" popper-class="bpa-dropdown--bulk-actions">
							<el-option v-for="item in bulk_options" :key="item.value" :label="item.label" :value="item.value"></el-option>
						</el-select>
						<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="delete_bulk_services()">
							<?php esc_html_e( 'Go', 'bookingpress-appointment-booking' ); ?>
						</el-button>
					</el-col>
				</el-row>
			</el-container>
		</el-row>
	</div>
</el-main>


<!-- Service Category Listing Modal -->
<el-dialog custom-class="bpa-dialog bpa-dialog--manage-categories" title="" :visible.sync="open_manage_category_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16" class="bpa-mlc-left-heading--is-visible-help">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Manage Categories', 'bookingpress-appointment-booking' ); ?></h1>				
			</el-col>
			<el-col :xs="12" :sm="12" :md="8" :lg="8" :xl="8">
				<div class="bpa-hw-right-btn-group">
					<el-button class="bpa-btn bpa-btn__medium" slot="reference" :class="(open_add_category_modal == true) ? 'bpa-btn--primary' : ''" @click="open_add_category_modal_func(event)">
						<span class="material-icons-round">add</span>
						<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
					</el-button>
					<?php do_action('bookingpress_category_header_section_button'); ?>
				</div>				
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-grid-list--manage-categories">
			<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
				<div class="bpa-back-loader"></div>
			</div>
			<el-row type="flex" v-if="category_items.length == 1">
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-data-empty-view bpa-data-empty-view--vertical">
						<div class="bpa-ev-left-vector">
							<picture>
								<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>"
									type="image/webp">
								<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
							</picture>
						</div>
						<div class="bpa-ev-right-content">
							<h4><?php esc_html_e( 'No Records Found!', 'bookingpress-appointment-booking' ); ?></h4>
							<p><?php echo stripslashes_deep( esc_html__('Start by clicking the Add New button', 'bookingpress-appointment-booking') ); //phpcs:ignore ?></p>
						</div>
					</div>
				</el-col>
			</el-row>
			
			<el-row v-else>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-card bpa-card__heading-row">
						<el-row type="flex">
							<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
								<div class="bpa-card__item bpa-card__item--ischecbox">
									<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Category Name', 'bookingpress-appointment-booking' ); ?></h4>
								</div>
							</el-col>
							<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
								<div class="bpa-card__item">
									<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Total Services', 'bookingpress-appointment-booking' ); ?></h4>
								</div>
							</el-col>
						</el-row>
					</div>
				</el-col>
				
				<draggable :list="category_items" :disabled="!enabled" class="list-group" ghost-class="ghost" @start="dragging = true" @end="updateCategoryPos($event)">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="category_items_data in category_items" v-if="category_items_data.category_id != 'add_new'" >
						<div class="bpa-card bpa-card__body-row">
							<div class="bpa-card__item--drag-icon-wrap">
								<span class="material-icons-round">drag_indicator</span>
							</div>
							<el-row type="flex">
								<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
									<div class="bpa-card__item bpa-card__item--ischecbox">
										<h4 class="bpa-card__item__heading is--body-heading">{{ category_items_data.category_name }} <span class="bpa-card__item--id">(<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>: {{ category_items_data.category_id }} )</h4>
									</div>
								</el-col>
								<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
									<div class="bpa-card__item">
										<h4 class="bpa-card__item__heading is--body-heading"><el-link @click="searchCategoryData(category_items_data.category_id)">{{ category_items_data.total_services }}</el-link></h4>
									</div>
								</el-col>
							</el-row>
							<div class="bpa-table-actions-wrap">
								<div class="bpa-table-actions">									
									<el-tooltip effect="dark" content="" placement="top" open-delay="300">									
										<div slot="content">
											<span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
										</div>
										<el-button class="bpa-btn bpa-btn--icon-without-box" @click="editServiceCategoryData(category_items_data.category_id, event)">
											<span class="material-icons-round">mode_edit</span>
										</el-button>
									</el-tooltip>
									<el-tooltip effect="dark" content="" placement="top" open-delay="300">
										<div slot="content">
											<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
										</div>	
										<el-popconfirm 
											confirm-button-text='<?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?>' 
											cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
											icon="false" 
											title="<?php esc_html_e( 'Are you sure you want to delete this category?', 'bookingpress-appointment-booking' ); ?>" 
											@confirm="deleteServiceCategory(category_items_data.category_id)" 
											confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
											cancel-button-type="bpa-btn bpa-btn__small">
											<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
												<span class="material-icons-round">delete</span>
											</el-button>
										</el-popconfirm>
									</el-tooltip>
								</div>
							</div>
						</div>
					</el-col>
				</draggable>
			</el-row>
		</el-container>
	</div>
	<div class="bpa-dialog-footer">
		<el-row>
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<div class="bpa-df-btn-group">
					<el-button class="bpa-btn bpa-btn__medium" @click="open_manage_category_modal = false"><?php esc_html_e( 'Close', 'bookingpress-appointment-booking' ); ?></el-button>
				</div>
			</el-col>
		</el-row>
	</div>
</el-dialog>

<el-dialog id="bpa_category_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-category" title="" :visible.sync="open_add_category_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc">
    <div class="bpa-dialog-heading">
        <el-row type="flex">
            <el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                <h1 class="bpa-page-heading" v-if="service_category.service_category_update_id == 0"><?php esc_html_e('Add Category', 'bookingpress-appointment-booking'); ?></h1>
                <h1 class="bpa-page-heading" v-else><?php esc_html_e('Edit Category', 'bookingpress-appointment-booking'); ?></h1>
            </el-col>
            
        </el-row>
    </div>
    <div class="bpa-dialog-body">
        <div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
            <div class="bpa-back-loader"></div>
        </div>
        <el-container class="bpa-grid-list-container bpa-add-categpry-container">
            <div class="bpa-form-row">
                <el-row>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <el-form ref="service_category" :rules="categoryRules" :model="service_category" label-position="top" @submit.native.prevent>
                            <div class="bpa-form-body-row">
                                <el-row>
                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                                        <el-form-item prop="service_category_name">
                                            <template #label>
                                                <span class="bpa-form-label"><?php esc_html_e('Category Name', 'bookingpress-appointment-booking'); ?></span>
                                            </template>
                                            <el-input class="bpa-form-control" v-model="service_category.service_category_name" id="service_category_name" name="service_category_name" placeholder="<?php esc_html_e('Enter Category Name', 'bookingpress-appointment-booking'); ?>" ref="serviceCatName"></el-input>
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
            <el-button class="bpa-btn bpa-btn--primary bpa-btn__small" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveCategoryDetails('service_category')" :disabled="is_disabled" >                    
                  <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                  <div class="bpa-btn--loader__circles">                    
                      <div></div>
                      <div></div>
                      <div></div>
                  </div>
            </el-button>
            <el-button class="bpa-btn bpa-btn__small" @click="open_add_category_modal = false"><?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></el-button>
        </div>
    </div>
</el-dialog>

<!-- Service Add Modal -->
<el-dialog custom-class="bpa-dialog bpa-dialog--fullscreen bpa-dialog--fullscreen__services bpa--is-page-scrollable-tablet" title=""
    :visible.sync="open_service_modal" top="32px" fullscreen=true :close-on-press-escape="close_modal_on_esc">
    <div class="bpa-dialog-heading">
        <el-row type="flex">
            <el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
                <h1 class="bpa-page-heading" v-if="service.service_update_id == 0">
                    <?php esc_html_e('Add Service', 'bookingpress-appointment-booking'); ?></h1>
                <h1 class="bpa-page-heading" v-else><?php esc_html_e('Edit Service', 'bookingpress-appointment-booking'); ?>
                </h1>
            </el-col>
            <el-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">
                <el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveServiceData" :disabled="is_disabled" >                    
                      <span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-appointment-booking'); ?></span>
                      <div class="bpa-btn--loader__circles">                    
                          <div></div>
                          <div></div>
                          <div></div>
                      </div>
                </el-button>    
                <el-button class="bpa-btn" @click="closeServiceModal()">
                    <?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?></el-button>
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
                    <div class="bpa-db-sec-heading">
                        <el-row type="flex" align="middle">
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <div class="db-sec-left">
                                    <h2 class="bpa-page-heading"><?php esc_html_e('Basic Details', 'bookingpress-appointment-booking'); ?></h2>
                                    
                                </div>
                            </el-col>
                            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                                <div class="bpa-hw-right-btn-group">
									<?php do_action('bookingpress_service_header_extra_button'); ?>
                                </div>
                            </el-col>
                        </el-row>
                    </div>
                    <div class="bpa-default-card bpa-db-card">
                        <el-form ref="service" :rules="rules" :model="service" label-position="top"
                            @submit.native.prevent>
                            <template>
                                <el-row :gutter="24">
                                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-form-group">
                                        <el-upload class="bpa-upload-component" ref="avatarRef"
                                            action="<?php echo wp_nonce_url(admin_url('admin-ajax.php') . '?action=bookingpress_upload_service', 'bookingpress_upload_service');//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - esc_html is already used by wp_nonce_url function and it's false positive ?>"
                                            :on-success="bookingpress_upload_service_func"
                                            :file-list="service.service_images_list" multiple="false"
                                            :show-file-list="serviceShowFileList" limit="1"
                                            :on-exceed="bookingpress_image_upload_limit"
                                            :on-error="bookingpress_image_upload_err"
                                            :on-remove="bookingpress_remove_service_img"
                                            :before-upload="checkUploadedFile" drag>
                                            <span
                                                class="material-icons-round bpa-upload-component__icon">cloud_upload</span>
                                            <div class="bpa-upload-component__text" v-if="service.service_image == ''"><?php esc_html_e('Please upload jpg/png/webp file', 'bookingpress-appointment-booking'); ?></div>
                                        </el-upload>
                                        <div class="bpa-uploaded-avatar__preview" v-if="service.service_image != ''">
                                            <button class="bpa-avatar-close-icon" @click="bookingpress_remove_service_img">
                                                <span class="material-icons-round">close</span>
                                            </button>
                                            <el-avatar shape="square" :src="service.service_image" class="bpa-uploaded-avatar__picture"></el-avatar>
                                        </div>
										<!--
										<el-upload class="bpa-upload-component" ref="avatarRef"
											action="<?php echo wp_nonce_url( admin_url('admin-ajax.php') . '?action=bookingpress_upload_service', 'bookingpress_upload_service' ); // phpcs:ignore ?>"
											:on-success="bookinpress_pro_upload_service"
											:file-list="service.service_images_list" multiple="true"
											:show-file-list="serviceShowFileList"
											:on-error="bookingpress_image_upload_err"
											:on-remove="bookingpress_remove_service_img"
											:before-upload="checkUploadedFile" drag>
											<span
												class="material-icons-round bpa-upload-component__icon">cloud_upload</span>
											<div class="bpa-upload-component__text"><?php esc_html_e( 'Please upload jpg/png file', 'bookingpress-appointment-booking' ); ?></div>
										</el-upload>
										<div class="bpa-uploaded-avatar__preivew--multiple" v-if="service.service_images_list !== undefined && service.service_images_list.length > 0">
											<div class="bpa-uam__items" v-if="is_gallery_expand == 0">
												<div class="bpa-uam__item" v-for="(img_data, img_key) in service.service_images_list.slice(0, 6)">
													<img :src="img_data.url" alt="img">
													<div class="bpa-uam__item-remove">
														<span class="material-icons-round" @click="bookingpress_pro_remove_gallery_image(img_key)">close</span>
													</div>
												</div>
											</div>
											<div class="bpa-uam__items bpa-expanded-gallery" v-if="is_gallery_expand == 1">
												<div class="bpa-uam__item" v-for="(img_data, img_key) in service.service_images_list">
													<img :src="img_data.url" alt="img">
													<div class="bpa-uam__item-remove">
														<span class="material-icons-round" @click="bookingpress_pro_remove_gallery_image(img_key)">close</span>
													</div>
												</div>
											</div>
											<div class="bpa-uam__expand-btn" v-if="service.service_images_list.length > 6 && is_gallery_expand == 0" @click="bookingpress_pro_expand_gallery()">
												<span>+{{service.service_images_list.length-6}}</span>
											</div>
											<div class="bpa-uam__expand-btn" v-if="is_gallery_expand == 1" @click="bookingpress_pro_collapse_gallery()">
												<span>-{{service.service_images_list.length-6}}</span>
											</div>
										</div>
										-->
									</el-col>
								</el-row>
								<div class="bpa-form-body-row">
									<el-row :gutter="32">
										<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
											<el-form-item prop="service_name">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Service Name:', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-input class="bpa-form-control" v-model="service.service_name" id="service_name" name="service_name"
													placeholder="<?php esc_html_e( 'Enter Service Name', 'bookingpress-appointment-booking' ); ?>">
												</el-input>
											</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
											<el-form-item prop="service_category">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Category:', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-select class="bpa-form-control" v-model="service.service_category" filterable
													placeholder="<?php esc_html_e( 'Select Category', 'bookingpress-appointment-booking' ); ?>"
													popper-class="bpa-el-select--is-with-modal" @change="check_category_type($event,event)">
													<el-option key="0" label="<?php esc_html_e( 'Select Category', 'bookingpress-appointment-booking' ); ?>" value="0"></el-option>
													<el-option v-for="item in serviceCatOptions" :key="item.value"
														:label="item.label" :value="item.value">																
														<i class="el-icon-plus" v-if="item.value == 'add_new'"></i>
															<span>{{ item.label }}</span>
													</el-option>
												</el-select>
											</el-form-item>
										</el-col>										
										<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08" >
											<el-form-item prop="service_price">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Price:', 'bookingpress-appointment-booking' ); ?>({{service_price_currency}})</span>
												</template>
												<el-input class="bpa-form-control" @input="isNumberValidate($event)" v-model="service.service_price" id="service_price" name="service_price" placeholder="0.00" v-if="price_number_of_decimals != '0'" :disabled="typeof service.enable_custom_service_duration !== 'undefined' ? service.enable_custom_service_duration : false"></el-input>
                                                <el-input class="bpa-form-control" @input="isValidateZeroDecimal($event)" v-model="service.service_price" id="service_price" name="service_price" placeholder="0" v-else :disabled="typeof service.enable_custom_service_duration !== 'undefined' ? service.enable_custom_service_duration : false"></el-input>
											</el-form-item>
										</el-col>
									</el-row>
								</div> 
								<div class="bpa-form-body-row">
									<el-row :gutter="32">
										<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
											<el-form-item prop="service_duration_val">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Duration:', 'bookingpress-appointment-booking' ); ?> </span>
												</template>
												<el-row :gutter="10">
													<el-col :xs="18" :sm="18" :md="18" :lg="16" :xl="18">
														<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" v-model="service.service_duration_val" id="service_duration_val" name="service_duration_val" step-strictly :disabled="typeof service.enable_custom_service_duration !== 'undefined' ? service.enable_custom_service_duration : false"></el-input-number>
													</el-col>
													<el-col :xs="6" :sm="6" :md="6" :lg="8" :xl="6">
														<el-select @change="change_service_unit_after" class="bpa-form-control" v-model="service.service_duration_unit" popper-class="bpa-el-select--is-with-modal bpa-service-number-control-dropdown bpa-el-select--is-sm-modal" 
														:disabled="typeof service.enable_custom_service_duration !== 'undefined' ? service.enable_custom_service_duration : false">
															<el-option key="m" label="<?php esc_html_e( 'Mins', 'bookingpress-appointment-booking' ); ?>" value="m"></el-option>
															<el-option key="h" label="<?php esc_html_e( 'Hours', 'bookingpress-appointment-booking' ); ?>" value="h"></el-option>
															<el-option key="d" label="<?php esc_html_e( 'Days', 'bookingpress-appointment-booking' ); ?>" value="d"></el-option>
														</el-select>
													</el-col>
												</el-row>
											</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
											<el-form-item prop="service_before_buffer_time">
												<template #label >
													<span class="bpa-form-label"><?php esc_html_e( 'Buffer Time Before:', 'bookingpress-appointment-booking' ); ?> </span>
												</template>
												<el-row :gutter="10">
													<el-col :xs="18" :sm="18" :md="18" :lg="16" :xl="18">
														<el-input-number class="bpa-form-control bpa-form-control--number" :min="0" id="buffer_time_before" name="buffer_time_before" v-model="service.service_before_buffer_time" step-strictly></el-input-number>
													</el-col>
													<el-col :xs="6" :sm="6" :md="6" :lg="8" :xl="6">
														<el-select class="bpa-form-control" v-model="service.service_before_buffer_time_unit" popper-class="bpa-el-select--is-with-modal bpa-service-number-control-dropdown bpa-el-select--is-sm-modal">
															<el-option key="m" label="<?php esc_html_e( 'Mins', 'bookingpress-appointment-booking' ); ?>" value="m"></el-option>
															<el-option key="h" label="<?php esc_html_e( 'Hours', 'bookingpress-appointment-booking' ); ?>" value="h"></el-option>
														</el-select>
													</el-col>
												</el-row>
											</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
											<el-form-item prop="service_after_buffer_time">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Buffer Time After:', 'bookingpress-appointment-booking' ); ?> </span>
												</template>
												<el-row :gutter="10">
													<el-col :xs="18" :sm="18" :md="18" :lg="16" :xl="18">
														<el-input-number class="bpa-form-control bpa-form-control--number" :min="0" id="buffer_time_after" name="buffer_time_after" v-model="service.service_after_buffer_time" step-strictly></el-input-number>
													</el-col>
													<el-col :xs="6" :sm="6" :md="6" :lg="8" :xl="6">
														<el-select class="bpa-form-control" v-model="service.service_after_buffer_time_unit" popper-class="bpa-el-select--is-with-modal bpa-service-number-control-dropdown bpa-el-select--is-sm-modal">
															<el-option key="m" label="<?php esc_html_e( 'Mins', 'bookingpress-appointment-booking' ); ?>" value="m"></el-option>
															<el-option key="h" label="<?php esc_html_e( 'Hours', 'bookingpress-appointment-booking' ); ?>" value="h"></el-option>
														</el-select>
													</el-col>
												</el-row>
											</el-form-item>
										</el-col>
									</el-row>
								</div>
								<div class="bpa-form-body-row">
									<el-row :gutter="32">
										<el-col v-if="is_bring_anyone_with_you_activated == 1" :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
											<el-form-item prop="min_capacity">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Min Capacity:', 'bookingpress-appointment-booking' ); ?> </span>
												</template>
												<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" :max="999" id="service_min_capacity" name="service_min_capacity" v-model="service.min_capacity" step-strictly></el-input-number>
											</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">								
											<el-form-item prop="max_capacity">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Max Capacity:', 'bookingpress-appointment-booking' ); ?> </span>
												</template>
												<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" :max="999" id="service_max_capacity" name="service_max_capacity" v-model="service.max_capacity" step-strictly></el-input-number>
											</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
											<el-form-item>
												<template #label>
													<span
														class="bpa-form-label"><?php esc_html_e( 'Description:', 'bookingpress-appointment-booking' ); ?> </span>
												</template>
												<el-input class="bpa-form-control" v-model="service.service_description"
													type="textarea" :rows="5" placeholder="<?php esc_html_e( 'Description', 'bookingpress-appointment-booking' ); ?>">
												</el-input>
											</el-form-item>
										</el-col>
									</el-row>
								</div>
								<div class="bpa-form-body-row bpa-deposit-payment__heading"></div>
									<div class="db-sec-left bpa-service-section-heading">
										<span class="bpa-serv__heading"> <?php esc_html_e('Set start and expiry date for the service', 'bookingpress-appointment-booking'); ?> </span>
									</div>
									<div class="bpa-form-body-row">
									<el-row :gutter="32">
										<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
											<el-form-item>
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Service Start Date (optional)', 'bookingpress-appointment-booking' ); ?> </span>
												</template>
												<el-date-picker class="bpa-form-control bpa-form-control--date-picker" format="<?php echo esc_html($bookingpress_common_date_format); ?>" placeholder='<?php echo esc_html__('Select Date','bookingpress-appointment-booking'); ?>' v-model="service.service_start_date" name="appointment_booked_date" type="date" popper-class="bpa-el-select--is-with-modal bpa-el-datepicker-widget-wrapper"  value-format="yyyy-MM-dd" :picker-options="filter_pickerOptions"></el-date-picker>
											</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
											<el-form-item>
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Service Expiration Date (optional)', 'bookingpress-appointment-booking' ); ?> </span>
												</template>
												<el-date-picker class="bpa-form-control bpa-form-control--date-picker" format="<?php echo esc_html($bookingpress_common_date_format); ?>" placeholder='<?php echo esc_html__('Select Date','bookingpress-appointment-booking'); ?>' v-model="service.service_expiration_date" name="appointment_booked_date" type="date" popper-class="bpa-el-select--is-with-modal bpa-el-datepicker-widget-wrapper"  value-format="yyyy-MM-dd" :picker-options="filter_pickerOptions"></el-date-picker>
											</el-form-item>
										</el-col>
									</el-row>
								</div>						
								<div v-if="(is_deposit_payment_activated == 1 || (typeof is_waiting_list_activated !== 'undefined' &&  is_waiting_list_activated == 1))">
									<div class="bpa-form-body-row bpa-deposit-payment__heading">									
									</div>									
									<div class="db-sec-left bpa-service-section-heading">
										<span class="bpa-serv__heading"> <?php esc_html_e('Configure Deposit options for the service', 'bookingpress-appointment-booking') ?> </span>
									</div>
									<div class="bpa-form-body-row">
										<el-row :gutter="32">
											<el-col v-if="is_deposit_payment_activated == 1" :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
												<el-form-item>
													<template #label>
														<span class="bpa-form-label"><?php esc_html_e( 'Deposit Type', 'bookingpress-appointment-booking' ); ?></span>
													</template>
													<el-select class="bpa-form-control" v-model="service.deposit_type">
														<el-option key="fixed" label="<?php esc_html_e( 'Fixed Amount', 'bookingpress-appointment-booking' ); ?>" value="fixed"></el-option>
														<el-option key="percentage" label="<?php esc_html_e( 'Percentage', 'bookingpress-appointment-booking' ); ?>" value="percentage"></el-option>
													</el-select>
												</el-form-item>
											</el-col>
											<el-col v-if="is_deposit_payment_activated == 1" :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
												<el-form-item>
													<template #label>
													<span class="bpa-form-label" v-if="service.deposit_type == 'fixed'"><?php esc_html_e( 'Deposit Amount:', 'bookingpress-appointment-booking' ); ?>({{service_price_currency}})</span>
													<span class="bpa-form-label" v-else><?php echo esc_html__( 'Deposit Amount', 'bookingpress-appointment-booking' ).'(%)'; ?></span>
													</template>
													<el-input class="bpa-form-control" @input="deposit_amount_validate($event, 'deposit_amount')" v-model="service.deposit_amount" id="deposit_amount" name="deposit_amount" placeholder="0.00"></el-input>
												</el-form-item>
											</el-col>
											<?php  do_action( 'bookingpress_add_service_deposit_field_inside' );  ?>											
										</el-row>
									</div>
								</div>
								<?php
									do_action( 'bookingpress_add_service_field_outside' );
								?>
							</template>
						</el-form>
					</div>
				</el-col>
			</el-row>
		</div>

		<?php // Extra Services section ?>
		<?php // ----------------------------------------------------------------- ?>
		<div class="bpa-form-row" v-if="is_service_extra_module_activated == 1">
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-db-sec-heading">
						<el-row type="flex" align="middle">
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="bpa-db-sec-left">
									<h2 class="bpa-page-heading"><?php esc_html_e( 'Extra Services', 'bookingpress-appointment-booking' ); ?></h2>
								</div>
							</el-col>
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="bpa-hw-right-btn-group">
									<el-button class="bpa-btn bpa-btn__filled-light" @click="open_extra_services_modal(event)">
										<span class="material-icons-round">add</span>
										<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
									</el-button>
								</div>
							</el-col>
						</el-row>
					</div>    
					<div class="bpa-default-card bpa-db-card bpa-grid-list-container bpa-dc__staff--assigned-service">                        
						<el-row class="bpa-dc--sec-sub-head" v-if="service.extraServicesData.length != 0">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<h2 class="bpa-sec--sub-heading">{{ service.extraServicesData.length }} <?php esc_html_e( 'Extra Services', 'bookingpress-appointment-booking' ); ?></h2>
							</el-col>
						</el-row>
						<div class="bpa-as__body">
							<el-row type="flex" class="bpa-as__empty-view" v-if="service.extraServicesData.length == 0">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-data-empty-view">
										<div class="bpa-ev-left-vector">
											<picture>
												<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
												<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
											</picture>
										</div>				
										<div class="bpa-ev-right-content">					
											<h4><?php esc_html_e( 'No Extra Services Found', 'bookingpress-appointment-booking' ); ?></h4>
										</div>				
									</div>
								</el-col>
							</el-row>
							<el-row v-else>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-card bpa-card__heading-row">
										<el-row type="flex">
											<el-col :xs="6" :sm="6" :md="6" :lg="5" :xl="5">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Title', 'bookingpress-appointment-booking' ); ?></h4>
												</div>    
											</el-col>
											<el-col :xs="6" :sm="6" :md="6" :lg="5" :xl="5">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
											<el-col :xs="6" :sm="6" :md="6" :lg="5" :xl="5">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
											<el-col :xs="6" :sm="6" :md="6" :lg="5" :xl="5">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Maximum Quantity', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
											<el-col :xs="6" :sm="6" :md="6" :lg="4" :xl="4">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Action', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
										</el-row>
									</div>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="(extra_service_data,index) in service.extraServicesData">
									<div class="bpa-card bpa-card__body-row list-group-item">
										<el-row type="flex">
											<el-col :xs="6" :sm="6" :md="6" :lg="5" :xl="5">
												<div class="bpa-card__item"> 
													<h4 class="bpa-card__item__heading is--body-heading">{{ extra_service_data.extra_service_titles }}</h4>
												</div>    
											</el-col>
											<el-col :xs="6" :sm="6" :md="6" :lg="5" :xl="5">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading">{{ extra_service_data.extra_service_durations }} {{ extra_service_data.extra_service_duration_units }}</h4>
												</div>
											</el-col>
											<el-col :xs="6" :sm="6" :md="6" :lg="5" :xl="5">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading">{{ extra_service_data.extra_service_prices_with_currency }}</h4>
												</div>
											</el-col>
											<el-col :xs="6" :sm="6" :md="6" :lg="5" :xl="5">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading">{{ extra_service_data.extra_service_maximum_quantitys }}</h4>
												</div>
											</el-col>
											<el-col :xs="6" :sm="6" :md="6" :lg="4" :xl="4">
												<div class="bpa-card__item">
													<el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editExtraService(event, index)">
														<span class="material-icons-round">mode_edit</span>
													</el-button>
													<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger" @click="deleteExtraService(index)">
														<span class="material-icons-round">delete</span>
													</el-button>
												</div>
											</el-col>
										</el-row>
									</div>
								</el-col>
							</el-row>
						</div>
						
					</div>
				</el-col>
			</el-row>
		</div>
		<?php
		 	do_action('bookingpress_add_service_extra_section');
		
			do_action('bookingpress_add_content_after_basic_details'); ?>
		<?php // ----------------------------------------------------------------- ?>
		
		<?php // Advance Options section ?>
		<?php // ----------------------------------------------------------------- ?>
		<div class="bpa-form-row">
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-db-sec-heading">
						<el-row type="flex" align="middle">
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="bpa-db-sec-left">
									<h2 class="bpa-page-heading"><?php esc_html_e( 'Advance Options', 'bookingpress-appointment-booking' ); ?></h2>
								</div>
							</el-col>
						</el-row>
					</div>    
					<div class="bpa-default-card bpa-db-card bpa-grid-list-container bpa-dc__staff--assigned-service">						
						<el-tabs class="bpa-tabs bpa-tabs--service-integration" v-model="bookingpress_advance_option_active_tab"> 							
							<el-tab-pane name="service_settings">
								<template #label>
									<span><?php esc_html_e( 'Service Settings', 'bookingpress-appointment-booking' ); ?></span>
								</template>
								<el-row :gutter="20">
									<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
										<label class="bpa-form-label"><?php esc_html_e('Minimum time required before booking:', 'bookingpress-appointment-booking'); ?></label>
										<el-select class="bpa-form-control" id="minimum_time_required_before_booking" name="minimum_time_required_before_booking" v-model="service.minimum_time_required_before_booking" 
											placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>" >								
											<el-option v-for="item in default_minimum_time_options" :key="item.text" :label="item.text" :value="item.value"></el-option>	
										</el-select>
									</el-col>
									<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
										<label class="bpa-form-label"><?php esc_html_e('Minimum time required before rescheduling:', 'bookingpress-appointment-booking'); ?></label>
										<el-select class="bpa-form-control" id="minimum_time_required_before_rescheduling" name="minimum_time_required_before_rescheduling" v-model="service.minimum_time_required_before_rescheduling" 
											placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>" >						
											<el-option v-for="item in default_minimum_time_options" :key="item.text" :label="item.text" :value="item.value"></el-option>	
										</el-select>
									</el-col>
									<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
										<label class="bpa-form-label"><?php esc_html_e('Minimum time required before cancelling:', 'bookingpress-appointment-booking'); ?></label>
										<el-select class="bpa-form-control" id="minimum_time_required_before_cancelling "name="minimum_time_required_before_cancelling" v-model="service.minimum_time_required_before_cancelling" 
											placeholder="<?php esc_html_e( 'Minutes', 'bookingpress-appointment-booking' ); ?>" >							
											<el-option v-for="item in default_minimum_time_options" :key="item.text" :label="item.text" :value="item.value"></el-option>	
										</el-select>
									</el-col>
								</el-row>
							</el-tab-pane>
							<?php
							if ( is_plugin_active( 'bookingpress-zoom/bookingpress-zoom.php' ) ) { ?>
							<el-tab-pane name="integrations">
								<template #label>
									<span><?php esc_html_e( 'Integrations', 'bookingpress-appointment-booking' ); ?></span>
								</template>														
								<el-row>									
									<?php do_action( 'bookingpress_service_integrations' ); ?>
								</el-row>
							</el-tab-pane>
							<?php } ?>
						</el-tabs>
					</div>
				</el-col>
			</el-row>
		</div>
	</div>
</el-dialog>

<?php 
 	do_action('bookingpress_service_language_translation_popup');
?>

<el-dialog id="service_extras_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-extra-service" title="" :visible.sync="open_add_extra_services_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc" v-if="is_service_extra_module_activated == 1"> 
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<h1 class="bpa-page-heading" v-if="bookingpress_update_index !== ''"><?php esc_html_e( 'Edit Extra Service', 'bookingpress-appointment-booking' ); ?></h1>
				<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Add Extra Service', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form ref="service_extra_inputs_form" :rules="serviceExtraInputRules" :model="service_extra_inputs_form" label-position="top" @submit.native.prevent>
							<div class="bpa-form-body-row">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="extra_service_title">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Service Title', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-input class="bpa-form-control" v-model="service_extra_inputs_form.extra_service_title" placeholder="<?php esc_html_e( 'Enter Service Title', 'bookingpress-appointment-booking' ); ?>" @blur="service_extra_name_validation(service_extra_inputs_form.extra_service_title)"></el-input>
										</el-form-item> 
									</el-col>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="extra_service_duration">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-row class="bpa-fbr__sub-row">
												<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
													<el-input-number class="bpa-form-control bpa-form-control--number" :min="0" :max="service_extra_inputs_form.extra_service_duration_unit == 'm' ? 1440 : 24" v-model="service_extra_inputs_form.extra_service_duration" step-strictly> </el-input-number>
												</el-col>
												<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
													<el-select class="bpa-form-control" v-model="service_extra_inputs_form.extra_service_duration_unit" @change="bookingpress_change_extra_duration($event)" popper-class="bpa-se--duration-dropdown">
														<el-option key="m" label="<?php esc_html_e( 'Mins', 'bookingpress-appointment-booking' ); ?>" value="m"></el-option>
														<el-option key="h" label="<?php esc_html_e( 'Hours', 'bookingpress-appointment-booking' ); ?>" value="h"></el-option>
													</el-select>													
												</el-col>
											</el-row>
											<span class="bpa-sm__field-helper-label"><?php esc_html_e('Leave it 0 to hide extra duration.', 'bookingpress-appointment-booking'); ?></span>										
										</el-form-item>
									</el-col>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="extra_service_price">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Service Price', 'bookingpress-appointment-booking' ); ?>( {{service_price_currency}} )</span>
											</template>
											<el-input class="bpa-form-control" v-model="service_extra_inputs_form.extra_service_price" id="extra_service_price" name="extra_service_price" @input="is_extra_service_price_validate($event)" placeholder="<?php esc_html_e( 'Enter Service Price', 'bookingpress-appointment-booking' ); ?>" ></el-input>
										</el-form-item>
									</el-col>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item>
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Maximum Quantity', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" :max="999" v-model="service_extra_inputs_form.extra_service_maximum_quantity" id="extra_service_maximum_quantity" name="extra_service_maximum_quantity" step-strictly></el-input-number>
											<span class="bpa-sm__field-helper-label"><?php esc_html_e('Leave it 1 to hide extra quantity.', 'bookingpress-appointment-booking'); ?></span>
										</el-form-item>										
									</el-col>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Service Description', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-input type="textarea" class="bpa-form-control" v-model="service_extra_inputs_form.extra_service_description" id="service_description" name="extra_service_description" placeholder="<?php esc_html_e( 'Enter Service Description', 'bookingpress-appointment-booking' ); ?>"></el-input>
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
			<el-button class="bpa-btn bpa-btn__small" @click="close_extra_services_modal()"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="saveServiceExtraDetails()"><?php esc_html_e( 'Add', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>

<el-dialog :custom-class="bpa_service_shift_management_external_class" modal-append-to-body=false :visible.sync="open_shift_management_modal" fullscreen=true :close-on-press-escape="close_modal_on_esc">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Shift Management', 'bookingpress-appointment-booking' ); ?> - <span v-html="shift_management_service_title"></span></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">				
				<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" :disabled="is_disabled" @click="bookingpress_save_service_shift_mgmt_details()">
				  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
				  <div class="bpa-btn--loader__circles">				    
					  <div></div>
					  <div></div>
					  <div></div>
				  </div>
				</el-button>
				<el-button class="bpa-btn" @click="open_shift_management_modal = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
			<div class="bpa-back-loader"></div>
		</div>
		<div class="bpa-form-row--parent" :class="bpa_sm_shift_management_cls">
			<div class="bpa-form-row bpa-sm__working-hours">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<div class="bpa-db-sec-heading">
							<el-row type="flex" align="middle">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-db-sec-left">
										<h2 class="bpa-page-heading"><?php esc_html_e( 'Working Hours', 'bookingpress-appointment-booking' ); ?></h2>
									</div>
								</el-col>
							</el-row>
						</div>	
						<div class="bpa-default-card bpa-db-card bpa-db-card-is-full-width-title" :class="bpa_service_shift_management_full_width_title_cls" id="bpa-db-card-is-full-width-title">
							<?php
								do_action('bookingpress_add_service_shift_management_content');
							?>
							<template>	
								<h3 class="bpa-wh__head-row-subheading"><?php esc_html_e( 'Configure Hours', 'bookingpress-appointment-booking' ); ?></h3>
								<div class="bpa-sm__wh-head-row">
									<div class="bpa-wh__head-row-title">
										<span class="bpa-form-label"><?php esc_html_e( 'Configure service specific work hours', 'bookingpress-appointment-booking' ); ?></span>
									</div>
									<div class="bpa-wh__head-row-swtich">
										<el-switch class="bpa-swtich-control" v-model="service.bookingpress_configure_specific_service_workhour" ></el-switch>
									</div>								
								</div>
								<div class="bpa-sm__wh-items" v-if="service.bookingpress_configure_specific_service_workhour == true && display_service_workhours == true">
									<div class="bpa-sm__wh-body-row" v-for="work_hours_day in work_hours_days_arr">
										<el-row class="bpa-sm__wh-item-row" :gutter="24" :id="'weekday_'+work_hours_day.day_name">
											<el-col :xs="22" :sm="22" :md="20" :lg="20" :xl="22">
												<el-row type="flex" class="bpa-sm__wh-body-left">
													<el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="2">
														<span class="bpa-form-label" v-if="work_hours_day.day_name == 'Monday'"><?php esc_html_e('Monday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Tuesday'"><?php esc_html_e('Tuesday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Wednesday'"><?php esc_html_e('Wednesday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Thursday'"><?php esc_html_e('Thursday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Friday'"><?php esc_html_e('Friday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Saturday'"><?php esc_html_e('Saturday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Sunday'"><?php esc_html_e('Sunday', 'bookingpress-appointment-booking'); ?></span>
														<span v-else>{{ work_hours_day.day_name }}</span>
													</el-col>
													<el-col :xs="24" :sm="24" :md="20" :lg="20" :xl="22">
														<el-row :gutter="24">
															<el-col :xs="8" :sm="8" :md="12" :lg="12" :xl="12">
																<el-select v-model="service.workhours_timings[work_hours_day.day_name].start_time" class="bpa-form-control bpa-form-control__left-icon" 
																	placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" 
																	@change="bookingpress_set_workhour_value($event,work_hours_day.day_name)" filterable>
																	<span slot="prefix" class="material-icons-round">access_time</span>
																	<el-option v-for="work_timings in work_hours_day.worktimes" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="work_timings.start_time != service.workhours_timings[work_hours_day.day_name].end_time || service.workhours_timings[work_hours_day.day_name].end_time == 'Off'"></el-option>
																</el-select>
															</el-col>
															<el-col :xs="8" :sm="8" :md="12" :lg="12" :xl="12" v-if="service.workhours_timings[work_hours_day.day_name].start_time != 'Off'">
																<el-select v-model="service.workhours_timings[work_hours_day.day_name].end_time" class="bpa-form-control bpa-form-control__left-icon" 
																	placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>" 
																	@change="bookingpress_check_workhour_value($event,work_hours_day.day_name)" filterable>
																		<span slot="prefix" class="material-icons-round">access_time</span>
																		<el-option v-for="work_timings in work_hours_day.worktimes" :label="work_timings.formatted_end_time" :value="work_timings.end_time" v-if="(  work_timings.end_time > service.workhours_timings[work_hours_day.day_name].start_time ||  work_timings.end_time == '24:00:00')"></el-option>
																</el-select>
															</el-col>
														</el-row>
														<el-row v-if="service.selected_break_timings[work_hours_day.day_name].length > 0 && service.workhours_timings[work_hours_day.day_name].start_time != 'Off'">
															<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																<div class="bpa-break-hours-wrapper">
																	<h4><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h4>
																	<div class="bpa-bh--items">
																		<div class="bpa-bh__item" v-for="(break_data,index) in work_hours_day.break_times">
																			<p @click="edit_workhour_data(event,break_data.start_time, break_data.end_time, work_hours_day.day_name,index)">{{ break_data.formatted_start_time }} to {{ break_data.formatted_end_time }}</p>
																			<span class="material-icons-round" slot="reference" @click="delete_breakhour(break_data.start_time, break_data.end_time, work_hours_day.day_name)">close</span>
																		</div>
																	</div>
																</div>
															</el-col>
														</el-row>
													</el-col>
												</el-row>	
											</el-col>
											<el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="2" v-if="service.workhours_timings[work_hours_day.day_name].start_time != 'Off'">
												<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" :class="(break_selected_day == work_hours_day.day_name && open_add_break_modal == true) ? 'bpa-btn--primary' : ''" @click="open_add_break_modal_func(event, work_hours_day.day_name)">
													<?php esc_html_e( 'Add Break', 'bookingpress-appointment-booking' ); ?>
												</el-button>
											</el-col>
										</el-row>
									</div>
								</div>
								<?php do_action( 'bookingpress_service_workhour_external_content'); ?>
							</template>
						</div>
					</el-col>
				</el-row>
			</div>
			<div class="bpa-form-row bpa-sm__special-days" >
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<div class="bpa-db-sec-heading">
							<el-row type="flex" align="middle">
								<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
									<div class="bpa-db-sec-left">
										<h2 class="bpa-page-heading"><?php esc_html_e( 'Special Days', 'bookingpress-appointment-booking' ); ?></h2>
									</div>
								</el-col>
								<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
									<div class="bpa-hw-right-btn-group">
										<el-button class="bpa-btn bpa-btn__filled-light" @click="bookingpress_open_service_special_days_modal_func(event)">
											<span class="material-icons-round">add</span>
											<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
										</el-button>
									</div>
								</el-col>
							</el-row>
						</div>	
						<div class="bpa-default-card bpa-grid-list-container bpa-dc__staff--assigned-service bpa-sm__special-days-card" v-if="display_service_workhours == true">
							<el-row class="bpa-dc--sec-sub-head" v-if="special_day_data_arr.length != 0">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<h2 class="bpa-sec--sub-heading"><?php esc_html_e( 'All Special Days', 'bookingpress-appointment-booking' ); ?></h2>
								</el-col>
							</el-row>
							<div class="bpa-as__body bpa-sm__doc-body">
								<el-row type="flex" class="bpa-as__empty-view" v-if="special_day_data_arr.length == 0">
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<div class="bpa-data-empty-view">
											<div class="bpa-ev-left-vector">
												<picture>
													<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
													<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
												</picture>
											</div>				
											<div class="bpa-ev-right-content">					
												<h4><?php esc_html_e( 'No Special Days Available', 'bookingpress-appointment-booking' ); ?></h4>
											</div>				
										</div>
									</el-col>
								</el-row>
								<el-row class="bpa-assigned-service-body" v-if="special_day_data_arr.length > 0">
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<div class="bpa-card bpa-card__heading-row">
											<el-row type="flex">
												<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?></h4>
													</div>
												</el-col>
												<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Workhours', 'bookingpress-appointment-booking' ); ?></h4>
													</div>
												</el-col>
												<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h4>
													</div>
												</el-col>
												<el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Action', 'bookingpress-appointment-booking' ); ?></h4>
													</div>
												</el-col>
											</el-row>	
										</div>
									</el-col>	
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="special_day_data in special_day_data_arr">
										<div class="bpa-card bpa-card__body-row list-group-item">
											<el-row type="flex">
												<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading is--body-heading">{{ special_day_data.special_day_formatted_start_date }} - {{ special_day_data.special_day_formatted_end_date }}</h4>
													</div>
												</el-col>								
												<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading is--body-heading">( {{special_day_data.formatted_start_time}} - {{special_day_data.formatted_end_time}} )</h4>
													</div>
												</el-col>	
												<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
													<div class="bpa-card__item"> 
														<span v-if="special_day_data.special_day_workhour != undefined && special_day_data.special_day_workhour != ''">	
															<h4 class="bpa-card__item__heading is--body-heading" v-for="special_day_workhours in special_day_data.special_day_workhour" v-if="special_day_workhours.formatted_start_time != undefined && special_day_workhours.formatted_start_time != '' && special_day_workhours.formatted_end_time != undefined && special_day_workhours.formatted_end_time != '' && special_day_workhours.start_time != '' && special_day_workhours.end_time != ''">
															( {{ special_day_workhours.formatted_start_time }} - {{special_day_workhours.formatted_end_time}} )
															</h4>
														</span>	
														<span v-else>-</span>	
													</div>
												</el-col>
												<el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
													<div>
														<el-tooltip effect="dark" content="" placement="top" open-delay="300">
															<div slot="content">
																<span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
															</div>
															<el-button class="bpa-btn bpa-btn--icon-without-box" @click="show_edit_special_day_div(special_day_data.id,event)">
														<span class="material-icons-round">mode_edit</span>
													</el-button>
														</el-tooltip>
														<el-tooltip effect="dark" content="" placement="top" open-delay="300">
															<div slot="content">
																<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
															</div>
													<el-button class="bpa-btn bpa-btn--icon-without-box __danger" @click="delete_special_day_div(special_day_data.id)">
														<span class="material-icons-round">delete</span>
													</el-button>
														</el-tooltip>
													</div>
												</el-col>
											</el-row>	
										</div>
									</el-col>
								</el-row>
							</div>
						</div>
						<?php do_action( 'bookingpress_service_special_days_external_content'); ?>
					</el-col>
				</el-row>
			</div>
		</div>
		<div class="bpa-form-row bpa-sm__days-off">
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-db-sec-heading">
						<el-row type="flex" align="middle">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<div class="bpa-db-sec-left">
									<h2 class="bpa-page-heading"><?php esc_html_e( 'Holidays', 'bookingpress-appointment-booking' ); ?></h2>
								</div>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<div class="bpa-hw-right-btn-group">
									<el-button class="bpa-btn bpa-btn__filled-light" @click="open_service_days_off_modal_func(event)">
										<span class="material-icons-round">add</span>
										<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
									</el-button>
								</div>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-default-card bpa-grid-list-container bpa-dc__staff--assigned-service bpa-sm__days-off-card">
						<el-row class="bpa-dc--sec-sub-head" v-if="service_dayoff_arr.length != 0">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<h2 class="bpa-sec--sub-heading"><?php esc_html_e( 'All Holiday', 'bookingpress-appointment-booking' ); ?></h2>
							</el-col>
						</el-row>
						<div class="bpa-as__body bpa-sm__doc-body">
							<el-row type="flex" class="bpa-as__empty-view" v-if="service_dayoff_arr.length == 0">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-data-empty-view">
										<div class="bpa-ev-left-vector">
											<picture>
												<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
												<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
											</picture>
										</div>				
										<div class="bpa-ev-right-content">					
											<h4><?php esc_html_e( 'No Holiday Available', 'bookingpress-appointment-booking' ); ?></h4>
										</div>				
									</div>
								</el-col>
							</el-row>
							<div v-if="service_dayoff_arr.length > 0">
								<el-row class="bpa-assigned-service-body">
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<div class="bpa-card bpa-card__heading-row">
											<el-row type="flex">
													<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Date', 'bookingpress-appointment-booking' ); ?></h4>
														</div>
													</el-col>
													<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Holiday Name', 'bookingpress-appointment-booking' ); ?></h4>
														</div>
													</el-col>
													<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Repeat Holiday', 'bookingpress-appointment-booking'); ?></h4>
														</div>
													</el-col>
													<el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
														<div class="bpa-card__item">
															<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Action', 'bookingpress-appointment-booking' ); ?></h4>
														</div>
													</el-col>
											</el-row>
										</div>
									</el-col>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="service_day_off in service_dayoff_arr">
										<div class="bpa-card bpa-card__body-row list-group-item">
											<el-row type="flex">
												<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
													<div class="bpa-card__item --bpa-sm-is-legends-item">
														<h4 v-if="service_day_off.dayoff_date == service_day_off.dayoff_date_end" class="bpa-card__item__heading is--body-heading">{{ service_day_off.dayoff_date | bookingpress_format_date }}</h4>	
														<h4 v-else class="bpa-card__item__heading is--body-heading">{{ service_day_off.dayoff_date | bookingpress_format_date }} - {{ service_day_off.dayoff_date_end | bookingpress_format_date }}</h4>	
													</div>
												</el-col>
												<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading is--body-heading">{{ service_day_off.dayoff_name }}</h4>
													</div>
												</el-col>
												<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading bpa--staff-repeat-holiday-label is--body-heading">{{ service_day_off.dayoff_repeat_label }}</h4>
													</div>
												</el-col>
												<el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
													<div>
														<el-tooltip effect="dark" content="" placement="top" open-delay="300">
															<div slot="content">
																<span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
															</div>
															<el-button class="bpa-btn bpa-btn--icon-without-box" @click="show_edit_dayoff_div(service_day_off.id, event)">
																<span class="material-icons-round">mode_edit</span>
															</el-button>
														</el-tooltip>
														<el-tooltip effect="dark" content="" placement="top" open-delay="300">
															<div slot="content">
																<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
															</div>
															<el-button class="bpa-btn bpa-btn--icon-without-box __danger" @click="delete_dayoff_div(service_day_off.id)">
																<span class="material-icons-round">delete</span>
															</el-button>
														</el-tooltip>
													</div>
												</el-col>
											</el-row>
										</div>
									</el-col>
								</el-row>
							</div>
						</div>
					</div>
				</el-col>
			</el-row>
		</div>
	</div>
</el-dialog>

<el-dialog id="add_newcategory_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--manage-categories" title="" :visible.sync="open_add_new_category_popup" :visible.sync="centerDialogVisible" :style="'top: '+add_new_category_modal_pos_top+';right: '+add_new_category_modal_pos_right+';'" :close-on-press-escape="close_modal_on_esc" :before-close="before_close_modal_event">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">				
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Add Category', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>			
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-row>				
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form ref="service_category" :rules="categoryRules" :model="service_category" label-position="top" @submit.native.prevent>
							<div class="bpa-form-body-row">
								<el-row>
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
										<el-form-item prop="service_category_name">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Category Name', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-input class="bpa-form-control" v-model="service_category.service_category_name" id="service_category_name" name="service_category_name" placeholder="<?php esc_html_e( 'Enter Category Name', 'bookingpress-appointment-booking' ); ?>" ref="serviceCatName"></el-input>
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
			<el-button class="bpa-btn bpa-btn--primary bpa-btn__small" :class="(is_category_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''"  @click="save_add_newCategoryDetails(service_category.service_category_name)" :disabled="is_category_disabled">
					<span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
					<div class="bpa-btn--loader__circles">				    
						<div></div>
						<div></div>
						<div></div>
					</div>
			</el-button>
			<el-button class="bpa-btn bpa-btn__small" @click="close_add_new_category_modal"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>

<el-dialog id="service_breaks_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-break" title="" :visible.sync="open_add_break_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display" <?php echo ( !is_rtl() ) ? 'top="10px"' : ''; ?>>
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16" v-if="is_edit_break == '0'"> 
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Add Break', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16" v-else>
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Edit Break', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>			
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form :rules="rules_add_break" ref="break_timings" :model="break_timings" label-position="top" @submit.native.prevent>
							<div class="bpa-form-body-row">
								<el-row :gutter="24">
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<el-form-item prop="start_time">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-select v-model="break_timings.start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable>
												<span slot="prefix" class="material-icons-round">access_time</span>
												<el-option v-for="break_times in default_break_timings" :key="break_times.start_time" :label="break_times.formatted_start_time" :value="break_times.start_time" v-if="break_times.start_time > service.workhours_timings[break_selected_day].start_time && break_times.start_time < service.workhours_timings[break_selected_day].end_time"></el-option>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<el-form-item prop="end_time">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-select v-model="break_timings.end_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>" filterable>
												<span slot="prefix" class="material-icons-round">access_time</span>
												<el-option v-for="break_times in default_break_timings" :key="break_times.start_time" :label="break_times.formatted_start_time" :value="break_times.start_time" v-if="(break_times.start_time > service.workhours_timings[break_selected_day].start_time && break_times.start_time < service.workhours_timings[break_selected_day].end_time) && (break_times.start_time > break_timings.start_time)"></el-option>
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
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="savebreakdata"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small" @click="close_add_break_model()"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>

<el-dialog id="special_days_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--special-days" title="" :visible.sync="special_days_add_modal" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading" v-if="edit_special_day_id == 0"><?php esc_html_e( 'Add Special Days', 'bookingpress-appointment-booking' ); ?></h1>
				<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Special Days', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
			
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form ref="special_day_form" :rules="rules_special_day" :model="special_day_form" label-position="top">
							<el-row>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item prop="special_day_date">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Date:', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-date-picker @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control bpa-form-control--date-range-picker" v-model="special_day_form.special_day_date" type="daterange" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" value-format="yyyy-MM-dd" :picker-options="disablePastDates" placeholder="<?php esc_html_e( 'Select Date', 'bookingpress-appointment-booking' ); ?>" range-separator=" - " :popper-append-to-body="false" start-placeholder="<?php esc_html_e( 'Start date', 'bookingpress-appointment-booking' ); ?>" end-placeholder="<?php esc_html_e( 'End date', 'bookingpress-appointment-booking' ); ?>">
										</el-date-picker>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-row type="flex" class="bpa-sd__time-selection">
										<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
											<el-form-item prop="start_time">												
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-select v-model="special_day_form.start_time" name ="start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable> 
													<span slot="prefix" class="material-icons-round">access_time</span>
													<el-option v-for="work_timings in specialday_hour_list"  :label="work_timings.formatted_start_time" :value="work_timings.start_time" ></el-option >											
											</el-select>
											</el-form-item>	
										</el-col>
										<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
											<el-form-item prop="end_time">
												<el-select v-model="special_day_form.end_time" name ="end_time" class="bpa-form-control bpa-form-control__left-icon"	placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>" filterable>
													<span slot="prefix" class="material-icons-round">access_time</span>
													<el-option v-for="work_timings in specialday_hour_list" :label="work_timings.formatted_end_time" :value="work_timings.end_time" v-if="work_timings.end_time > special_day_form.start_time">
													</el-option>
												</el-select>
											</el-form-item>
										</el-col>
									</el-row>
								</el-col>
							</el-row>
							<el-row>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-sd__add-period-btn">
										<el-link class="bpa-sd__add-period-btn-link" @click="bookingpress_add_service_special_day_period()">
											<span class="material-icons-round">add_circle</span>
											<?php esc_html_e( 'Add Breaks', 'bookingpress-appointment-booking' ); ?>
										</el-link>
									</div>
								</el-col>
							</el-row>
							<el-row class="bpa-sd--add-period-row" v-for="special_day_workhours in special_day_form.special_day_workhour">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-ts__item">
										<div class="bpa-ts__item-left">
											<el-row type="flex" class="bpa-sd__time-selection">
												<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
													<el-form-item>											
														<template #label>
															<span class="bpa-form-label"><?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?></span>
														</template>
														<el-select v-model="special_day_workhours.start_time" name ="start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable> 
															<span slot="prefix" class="material-icons-round">access_time</span>
															<el-option v-for="work_timings in specialday_break_hour_list" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="work_timings.start_time > special_day_form.start_time && work_timings.start_time < special_day_form.end_time"></el-option >
														</el-select>
													</el-form-item>	
												</el-col>
												<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
													<el-form-item>
														<el-select v-model="special_day_workhours.end_time" name ="end_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>" filterable>
															<span slot="prefix" class="material-icons-round">access_time</span>
															<el-option v-for="work_timings in specialday_break_hour_list" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="((work_timings.start_time > special_day_form.start_time && work_timings.start_time < special_day_form.end_time) && (work_timings.start_time > special_day_workhours.start_time))">
															</el-option>
														</el-select>
													</el-form-item>
												</el-col>
											</el-row>
										</div>
										<div class="bpa-ts__item-right">
											<div class="bpa-sd__add-period-btn">
												<el-link class="bpa-sd__add-period-btn-link"  @click="bookingpress_remove_special_day_period(special_day_workhours.id)">
													<span class="material-icons-round">remove_circle</span>
												</el-link>
											</div>
										</div>
									</div>
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
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="addSpecialday('special_day_form')" :disabled="disable_service_special_day_btn"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small" @click="special_days_add_modal = false"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>

<el-dialog id="days_off_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--days-off bpa-service--days-off-dialog" title="" :visible.sync="days_off_add_modal" :close-on-press-escape="close_modal_on_esc" @close="close_service_dayoff" :modal="is_mask_display">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading" v-if="edit_service_dayoff == ''"><?php esc_html_e( 'Add Holiday', 'bookingpress-appointment-booking' ); ?></h1>
				<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Holiday', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form ref="service_dayoff_form" :rules="rules_dayoff" :model="service_dayoff_form" label-position="top">
							<el-row>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item prop="dayoff_date">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Date:', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-date-picker @focus="bookingpress_remove_date_range_picker_focus" @change="bookingpress_after_select_service_holiday" class="bpa-form-control bpa-form-control--date-range-picker" v-model="service_dayoff_form.dayoff_date_range" type="daterange" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" start-placeholder="<?php esc_html_e( 'Start Date', 'bookingpress-appointment-booking' ); ?>" end-placeholder="<?php esc_html_e( 'End Date', 'bookingpress-appointment-booking' ); ?>" :picker-options="disablePastDates" value-format="yyyy-MM-dd"> </el-date-picker>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">									
									<el-form-item prop="dayoff_name">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Holiday Name:', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input class="bpa-form-control" @blur="service_daysoff_name_validation(service_dayoff_form.dayoff_name)" v-model="service_dayoff_form.dayoff_name" id="dayoff_name" name="dayoff_name" placeholder="<?php esc_html_e( 'Enter holiday name', 'bookingpress-appointment-booking' ); ?>"></el-input>
									</el-form-item>
								</el-col>								
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item>
										<el-checkbox v-model="service_dayoff_form.dayoff_repeat"><span class="bpa-form-label"><?php esc_html_e( 'Repeat Holiday', 'bookingpress-appointment-booking' ); ?></span></el-checkbox>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly" v-if="true == service_dayoff_form.dayoff_repeat">
									<el-form-item>
										<template #label>
                                            <span class="bpa-form-label"><?php esc_html_e( 'Repeat Every', 'bookingpress-appointment-booking'); ?></span>
                                        </template>
										<el-row class="bpa-fbr__sub-row">
											<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
												<el-input-number v-model="service_dayoff_form.dayoff_repeat_frequency" class="bpa-form-control bpa-form-control--number" :min="1" :max="999" placeholder="<?php esc_html_e( 'Enter Holiday Repeat Frequency', 'bookingpress-appointment-booking'); ?>" ></el-input-number>
											</el-col>
											<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
												<el-select class="bpa-form-control" v-model="service_dayoff_form.dayoff_repeat_freq_type" popper-class="bpa-se--holiday-repeat-frequency-type-dropdown">
													<el-option v-for="( rf_label, rf_value ) in service_dayoff_repeat_frequency_type_opts" :label="rf_label" :value="rf_value"></el-option>
												</el-select>
											</el-col>
										</el-row>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly" v-if="true == service_dayoff_form.dayoff_repeat">
									<el-form-item prop="dayoff_repeat_duration">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking'); ?></span>
										</template>
										<el-select class="bpa-form-control" v-model="service_dayoff_form.dayoff_repeat_duration" popper-class="bpa-se--holiday-repeat-duration-type-dropdown">
											<el-option v-for="( rd_label, rd_value ) in service_repeat_duration_opts" :label="rd_label" :value="rd_value"></el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly" v-if="true == service_dayoff_form.dayoff_repeat && 'no_of_times' == service_dayoff_form.dayoff_repeat_duration">
									<el-form-item>
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Holiday Repeat Times', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input-number v-model="service_dayoff_form.dayoff_repeat_times" class="bpa-form-control bpa-form-control--number" :min="1"></el-input-number>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly bpa-add-dayoff-col--repeat-date" v-if="true == service_dayoff_form.dayoff_repeat && 'until' == service_dayoff_form.dayoff_repeat_duration">
									<el-form-item prop="repeat_date">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Holiday Repeat Date', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-date-picker :picker-options="ServiceholidayPickerOptions" class="bpa-form-control bpa-form-control--date-picker" v-model="service_dayoff_form.dayoff_repeat_date"></el-date-picker>
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
			<el-button class="bpa-btn bpa-btn__small" @click="close_service_dayoff"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="addServiceDayoff('service_dayoff_form')" :disabled="disable_service_holiday_btn"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>

<?php
do_action('bookingpress_service_dialog_outside');
?>