<?php
	global $bookingpress_ajaxurl,$BookingPressPro,$bookingpress_common_date_format, $bookingpress_global_options;
	$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
	$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
	$bookingpress_plural_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) : esc_html_e('Staff Members', 'bookingpress-appointment-booking');
?>
<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">
	<el-row type="flex" class="bpa-mlc-head-wrap">
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
			<h1 class="bpa-page-heading"><?php esc_html_e( 'Manage', 'bookingpress-appointment-booking' ); ?> <?php echo esc_html($bookingpress_plural_staffmember_name); ?></h1>
		</el-col>
		<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
			<div class="bpa-hw-right-btn-group">				
				<el-button class="bpa-btn bpa-btn--primary" @click="open_staff_member_modal_func()">
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
			<el-row type="flex" :gutter="32">
				<el-col :xs="24" :sm="24" :md="24" :lg="16" :xl="18">
					<span class="bpa-form-label"><?php echo esc_html($bookingpress_singular_staffmember_name); ?> <?php esc_html_e( 'Name', 'bookingpress-appointment-booking' ); ?></span>
					<el-input v-model="staff_member_search" class="bpa-form-control" placeholder="<?php echo esc_html($bookingpress_singular_staffmember_name); ?> <?php esc_html_e( 'Name', 'bookingpress-appointment-booking' ); ?>"></el-input>
				</el-col>	
				<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="6">
					<div class="bpa-tf-btn-group">
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="resetFilter">
							<?php esc_html_e( 'Reset', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary bpa-btn--full-width" @click="loadStaffmembers">
							<?php esc_html_e( 'Apply', 'bookingpress-appointment-booking' ); ?>
						</el-button>
						<?php
						if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_export_staffmembers' ) ) {
							?>
						<el-button class="bpa-btn bpa-btn--secondary bpa-btn__medium bpa-btn--full-width" @click="Bookingpress_export_staffmember_data">
							<span class="material-icons-round">open_in_new</span><?php esc_html_e( 'Export', 'bookingpress-appointment-booking' ); ?>
						</el-button>						
						<?php } ?>
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
						<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="open_staff_member_modal_func()"> 
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
                            <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <div class="bpa-card__item bpa-card__item--ischecbox">
                                    <el-checkbox v-model="is_multiple_checked" @change="selectAllStaffmembers($event)"></el-checkbox>
                                    <h4 class="bpa-card__item__heading"><?php esc_html_e('FullName', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                            </el-col>
                            <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <div class="bpa-card__item">
                                    <h4 class="bpa-card__item__heading"><?php esc_html_e('Email', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                            </el-col>
                            <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <div class="bpa-card__item">
                                    <h4 class="bpa-card__item__heading"><?php esc_html_e('Phone', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                            </el-col>
							<?php do_action('bookingpress_backend_staffmembers_extra_col_heading'); ?>
                            <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                                <div class="bpa-card__item">
                                    <h4 class="bpa-card__item__heading"><?php esc_html_e('Assigned Services', 'bookingpress-appointment-booking'); ?></h4>
                                </div>
                            </el-col>
                        </el-row>
                    </div>
                </el-col> 
				<draggable :list="items" :disabled="!enabled" class="list-group" ghost-class="ghost" @start="dragging = true" @end="updateStaffmemberPos($event)">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="items_data in items" :data-staffmember_id="items_data.staffmember_id">
						<div class="bpa-card bpa-card__body-row list-group-item" :class="items_data.staffmember_status == 1 ? 'bookingpress_enable_row' : 'bookingpress_disable_row bpa-card-body__disabled'">
							<div class="bpa-card__item--drag-icon-wrap">
								<span class="material-icons-round">drag_indicator</span>
							</div>
							<el-row type="flex">
								<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
									<div class="bpa-card__item bpa-card__item--ischecbox">
										<el-checkbox v-model="items_data.selected" :disabled=items_data.staffmember_bulk_action @change="handleSelectionChange(event, $event, items_data.staffmember_id)"></el-checkbox>
										<img :src="items_data.staffmember_avatar_url" alt="staffmember-thumbnail" class="bpa-card__item--service-thumbnail" v-if="items_data.staffmember_avatar_url != ''">						
										<h4 class="bpa-card__item__heading is--body-heading"> <span v-if="items_data.staffmember_firstname != '' || items_data.staffmember_lastname != ''" v-html="items_data.staffmember_firstname+' '+items_data.staffmember_lastname"></span><span v-else v-html="items_data.staffmember_email"></span> <span class="bpa-card__item--id">(<?php esc_html_e( 'ID', 'bookingpress-appointment-booking' ); ?>: {{ items_data.staffmember_id }} )</span></h4>
									</div>
								</el-col>
								<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
									<div class="bpa-card__item">
										<h4 class="bpa-card__item__heading is--body-heading">{{ items_data.staffmember_email }}</h4>
									</div>
								</el-col>
								<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
									<div class="bpa-card__item">
										<h4 class="bpa-card__item__heading is--body-heading">{{ items_data.staffmember_phone }}</h4>
									</div>
								</el-col>
								<?php do_action('bookingpress_backend_staffmembers_extra_col_body'); ?>
								<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
									<div class="bpa-card__item">
										<h4 class="bpa-card__item__heading is--body-heading">{{ items_data.staffmember_assigned_services }}</h4>
									</div>
								</el-col>
							</el-row>
							<div class="bpa-table-actions-wrap">
								<div class="bpa-table-actions">
									<el-tooltip effect="dark" content="" placement="top" open-delay="300" v-if="items_data.staffmember_status == 1">
										<?php
											$bookingpress_deactivate_msg = esc_html__('Are you sure you want to deactivate', 'bookingpress-appointment-booking')." ".esc_html($bookingpress_singular_staffmember_name)." ?" ;
											$bookingpress_activate_msg = esc_html__('Are you sure you want to activate', 'bookingpress-appointment-booking')." ".esc_html($bookingpress_singular_staffmember_name);
											$bookingpress_delete_staffmember_msg = esc_html__('Are you sure you want to delete this','bookingpress-appointment-booking')." ".esc_html($bookingpress_singular_staffmember_name)." ?";
										?>
										<div slot="content">
											<span><?php esc_html_e( 'Deactivate', 'bookingpress-appointment-booking' ); ?><?php echo esc_html($bookingpress_singular_staffmember_name); ?></span>
										</div>
										<el-popconfirm
											cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
											confirm-button-text='<?php esc_html_e( 'Deactivate', 'bookingpress-appointment-booking' ); ?>' 
											icon="false" 
											title="<?php echo $bookingpress_deactivate_msg; //phpcs:ignore ?>" 
											@confirm="bookingpress_change_staffmember_status(items_data.staffmember_id, 0)" 
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
											<span><?php esc_html_e( 'Activate', 'bookingpress-appointment-booking' ); ?><?php echo esc_html($bookingpress_singular_staffmember_name); ?></span>
										</div>
										<el-popconfirm
											cancel-button-text='<?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?>' 
											confirm-button-text='<?php esc_html_e( 'Activate', 'bookingpress-appointment-booking' ); ?>' 
											icon="false" 
											title="<?php echo $bookingpress_activate_msg; //phpcs:ignore ?>" 
											@confirm="bookingpress_change_staffmember_status(items_data.staffmember_id, 1)"
											confirm-button-type="bpa-btn bpa-btn__small bpa-btn--secondary" 
											cancel-button-type="bpa-btn bpa-btn__small">
											<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __secondary">
												<span class="material-icons-round">how_to_reg</span>
											</el-button>
										</el-popconfirm>
									</el-tooltip>	
									<el-tooltip effect="dark" content="" placement="top" open-delay="300">
										<div slot="content">
											<span><?php esc_html_e('Edit', 'bookingpress-appointment-booking'); ?></span>
										</div>
										<el-button class="bpa-btn bpa-btn--icon-without-box" @click.native.prevent="editStaffMember(items_data.staffmember_id)">
											<span class="material-icons-round">mode_edit</span>
										</el-button>
									</el-tooltip>              
									<el-tooltip effect="dark" content="" placement="top" open-delay="300">
										<div slot="content">
											<span><?php esc_html_e( 'Shift Management', 'bookingpress-appointment-booking' ); ?></span>
										</div>
										<el-button class="bpa-btn bpa-btn--icon-without-box" @click="bookingpress_open_shift_management_modal(items_data.staffmember_id,items_data.configure_specific_workhour)">
											<span class="material-icons-round">date_range</span>
										</el-button>
									</el-tooltip>
									<?php
									if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_delete_staffmembers' ) ) {
									?>
									<el-tooltip effect="dark" content="" placement="top" open-delay="300">
										<div slot="content">
											<span><?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?></span>
										</div>
										<el-popconfirm 
											confirm-button-text='<?php esc_html_e('Delete', 'bookingpress-appointment-booking'); ?>' 
											cancel-button-text='<?php esc_html_e('Cancel', 'bookingpress-appointment-booking'); ?>' 
											icon="false" 
											title="<?php echo $bookingpress_delete_staffmember_msg; //phpcs:ignore ?>" 
											@confirm="deleteStaffMember(items_data.staffmember_id)" 
											confirm-button-type="bpa-btn bpa-btn__small bpa-btn--danger" 
											cancel-button-type="bpa-btn bpa-btn__small">
											<el-button type="text" slot="reference" class="bpa-btn bpa-btn--icon-without-box __danger">
												<span class="material-icons-round">delete</span>
											</el-button>
										</el-popconfirm>
									</el-tooltip>
									<?php
									}
									?>
								</div>
							</div>
						</div>
					</el-col>
				</draggable>
            </el-row>
        </el-container>
		<el-row class="bpa-pagination" v-if="items.length > 0">
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
						<el-select class="bpa-form-control" v-model="bulk_action" placeholder="<?php esc_html_e( 'Select', 'bookingpress-appointment-booking' ); ?>"
						popper-class="bpa-dropdown--bulk-actions">
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
</el-main>
<el-dialog custom-class="bpa-dialog bpa-dialog--fullscreen bpa-dialog--staff-modal bpa--is-page-scrollable-tablet" modal-append-to-body=false :visible.sync="open_staff_member_modal" fullscreen=true :close-on-press-escape="close_modal_on_esc">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading" v-if="staff_members.update_id == 0"><?php esc_html_e( 'Add', 'bookingpress-appointment-booking' ); ?> <?php echo esc_html( $bookingpress_singular_staffmember_name ); ?></h1>
				<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?> <?php echo esc_html($bookingpress_singular_staffmember_name); ?></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">				
				<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveStaffMembersDetails()" :disabled="is_disabled">		
				  <span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
				  <div class="bpa-btn--loader__circles">				    
					  <div></div>
					  <div></div>
					  <div></div>
				  </div>
				</el-button>
				<el-button class="bpa-btn" @click="close_staff_member_modal_func"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
			<div class="bpa-back-loader"></div>
		</div>

		<?php // Basic details section ?>
		<?php // ----------------------------------------------------------------- ?>
		<div class="bpa-form-row">
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-db-sec-heading">						
						<el-row type="flex" align="middle">
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="bpa-db-sec-left">
									<h2 class="bpa-page-heading"><?php esc_html_e( 'Basic Details', 'bookingpress-appointment-booking' ); ?></h2>
								</div>
							</el-col>
						</el-row>
					</div>					
					<div class="bpa-default-card bpa-db-card">
						<el-form ref="staff_members" :rules="rules" :model="staff_members" label-position="top" @submit.native.prevent>
							<template>	
								<el-row :gutter="24">
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-form-group">										
										<el-upload class="bpa-upload-component" ref="avatarRef" action="<?php echo wp_nonce_url( admin_url('admin-ajax.php') . '?action=bookingpress_upload_staff_member_avatar', 'bookingpress_upload_staff_member_avatar' ); // phpcs:ignore ?>" :on-success="bookingpress_upload_staff_member_avatar_func" multiple="false" :file-list="staff_members.avatar_list" :show-file-list="staffShowFileList" limit="1" :on-exceed="bookingpress_image_upload_limit" :on-error="bookingpress_image_upload_err" :on-remove="bookingpress_remove_staff_members_avatar" :before-upload="checkUploadedFile" drag >
											<span class="material-icons-round bpa-upload-component__icon">cloud_upload</span>
										   <div class="bpa-upload-component__text" v-if="staff_members.avatar_url == ''"><?php esc_html_e( 'Please upload jpg/png/webp file', 'bookingpress-appointment-booking' ); ?>									   	
										   </div>
										</el-upload>										
										<div class="bpa-uploaded-avatar__preview" v-if="staff_members.avatar_url != ''">											
											<button class="bpa-avatar-close-icon" @click="bookingpress_remove_staff_members_avatar" >
												<span class="material-icons-round">close</span>
											</button>
											<el-avatar shape="square" :src="staff_members.avatar_url" class="bpa-uploaded-avatar__picture"></el-avatar>
										</div>
									</el-col>
								</el-row>
								<div class="bpa-form-body-row bpa-fbr--staffmember">
									<el-row type="flex" :gutter="32">										
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="wp_user">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'WordPress User', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-select class="bpa-form-control" v-model="staff_members.wp_user" filterable placeholder="<?php esc_html_e( 'Start typing to fetch user.', 'bookingpress-appointment-booking' ); ?>" @change="bookingpress_get_existing_user_details($event)"  remote reserve-keyword	 :remote-method="get_wordpress_users" :loading="bookingpress_loading">
													<el-option-group label="<?php esc_html_e( 'Create New User', 'bookingpress-appointment-booking' ); ?>">
														<template>
															<el-option value="add_new" label="<?php esc_html_e( 'Create New', 'bookingpress-appointment-booking' ); ?>">
																<i class="el-icon-plus" ></i>
																<span><?php esc_html_e( 'Create New', 'bookingpress-appointment-booking' ); ?></span>
															</el-option>
														</template>
													</el-option-group>
													<el-option-group v-for="wp_user_list_cat in wpUsersList" :key="wp_user_list_cat.category" :label="wp_user_list_cat.category">
														<template>
															<el-option v-for="item in wp_user_list_cat.wp_user_data" :key="item.wp_user" :label="item.label" :value="item.value" >
																<span>{{ item.label }}</span>
															</el-option>
														</template>
													</el-option-group>
												</el-select>
											</el-form-item>
										</el-col>										
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8" v-if="staff_members.wp_user == 'add_new'" >
											<el-form-item prop="password">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Password', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-input class="bpa-form-control" v-model="staff_members.password" id="password" type="password" name="password" placeholder="<?php esc_html_e( 'Enter Password', 'bookingpress-appointment-booking' ); ?>" ></el-input>
											</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="firstname">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'First Name', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-input class="bpa-form-control" v-model="staff_members.firstname" id="firstname" name="firstname" placeholder="<?php esc_html_e( 'Enter First Name', 'bookingpress-appointment-booking' ); ?>" ></el-input>
											</el-form-item>
										</el-col>
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="lastname">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Last Name', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-input class="bpa-form-control" v-model="staff_members.lastname" id="lastname" name="lastname" placeholder="<?php esc_html_e( 'Enter Last Name', 'bookingpress-appointment-booking' ); ?>" ></el-input>
											</el-form-item>
										</el-col>											
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="email">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Email', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-input class="bpa-form-control" v-model="staff_members.email" id="email" name="email" placeholder="<?php esc_html_e( 'Enter Email', 'bookingpress-appointment-booking' ); ?>" ></el-input>
											</el-form-item>
										</el-col>																						
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="phone">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Phone', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<vue-tel-input v-model="staff_members.phone" class="bpa-form-control --bpa-country-dropdown" @country-changed="bookingpress_phone_country_change_func($event)" v-bind="bookingpress_tel_input_props" ref="bpa_tel_input_field" :mode="vue_tel_mode" :auto-format="vue_tel_auto_format" >
													<template v-slot:arrow-icon>
														<span class="material-icons-round">keyboard_arrow_down</span>
													</template>
												</vue-tel-input>
											</el-form-item>
										</el-col> 
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="email">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Visibility', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-select class="bpa-form-control" v-model="staff_members.visibility" >		
													<el-option  label="<?php esc_html_e('Public','bookingpress-appointment-booking'); ?>" value="public">
													</el-option>	
													<el-option  label="<?php esc_html_e('Private','bookingpress-appointment-booking'); ?>" value="private">
													</el-option>	
												</el-select>
											</el-form-item>
										</el-col>																		
										<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
											<el-form-item prop="note">
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Note', 'bookingpress-appointment-booking' ); ?></span>
												</template>
												<el-input class="bpa-form-control" type="textarea" :rows="3" v-model="staff_members.note"> </el-input>
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

		<?php // ----------------------------------------------------------------- ?>


		<?php // Assigned Services section ?>
		<?php // ----------------------------------------------------------------- ?>
		<div class="bpa-form-row">
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-db-sec-heading">
						<el-row type="flex" align="middle">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<div class="bpa-db-sec-left">
									<h2 class="bpa-page-heading"><?php esc_html_e( 'Assigned Services', 'bookingpress-appointment-booking' ); ?></h2>
								</div>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<div class="bpa-hw-right-btn-group">
									<el-button class="bpa-btn bpa-btn__filled-light" @click="open_assign_service_modal_func(event)">
										<span class="material-icons-round">add</span>
										<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
									</el-button>
								</div>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-default-card bpa-db-card bpa-grid-list-container bpa-dc__staff--assigned-service">
						<el-row class="bpa-dc--sec-sub-head" v-if="total_assigned_services != 0">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<h2 class="bpa-sec--sub-heading">{{ total_assigned_services }} <?php esc_html_e( 'Assigned Services', 'bookingpress-appointment-booking' ); ?></h2>
							</el-col>
						</el-row>
						<div class="bpa-as__body">
							<el-row type="flex" class="bpa-as__empty-view" v-if="total_assigned_services == 0">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-data-empty-view">
										<div class="bpa-ev-left-vector">
											<picture>
												<source srcset="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp' ); ?>" type="image/webp">
												<img src="<?php echo esc_url( BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png' ); ?>">
											</picture>
										</div>				
										<div class="bpa-ev-right-content">					
											<h4><?php esc_html_e( 'No Services Available', 'bookingpress-appointment-booking' ); ?></h4>
										</div>				
									</div>
								</el-col>
							</el-row>
							<el-row v-else>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-card bpa-card__heading-row">
										<el-row type="flex">
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Service Name', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-if="typeof is_active_service_custom_duration !== 'undefined' && is_active_service_custom_duration == 1">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-if="is_bring_anyone_with_you_activated == 1">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Min Capacity', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Max Capacity', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
											<?php do_action( 'bookingpress_staff_assigned_services_column_name' ); ?>
											<el-col :xs="3" :sm="3" :md="3" :lg="3" :xl="3">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading"><?php esc_html_e( 'Action', 'bookingpress-appointment-booking' ); ?></h4>
												</div>
											</el-col>
										</el-row>
									</div>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="assigned_service_details in assign_service_form.assigned_service_list" v-show="(assigned_service_details.assign_service_display == 'true' || assigned_service_details.assign_service_display == true)">
									<div class="bpa-card bpa-card__body-row list-group-item">
										<el-row type="flex">
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading">{{ assigned_service_details.assign_service_name }}</h4>
												</div>
											</el-col>
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-if="assigned_service_details.bookingpress_custom_durations_data !== 'undefined' && assigned_service_details.bookingpress_custom_durations_data != '' && assigned_service_details.bookingpress_custom_durations_data != null">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading" v-for="(custom_service_duration_data, index) in assigned_service_details.bookingpress_custom_durations_data">{{ custom_service_duration_data.staff_duration_text }}</h4>
												</div>
											</el-col>
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-else-if="typeof is_active_service_custom_duration !== 'undefined' && is_active_service_custom_duration == 1 && assigned_service_details.staff_duration_text != 'undefined'">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading"> {{assigned_service_details.staff_duration_text}} </h4>
												</div>
											</el-col>
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-if="assigned_service_details.bookingpress_custom_durations_data !== 'undefined' && assigned_service_details.bookingpress_custom_durations_data != '' && assigned_service_details.bookingpress_custom_durations_data != null">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading" v-for="(custom_service_duration_data, index) in assigned_service_details.bookingpress_custom_durations_data" v-if="custom_service_duration_data.staff_service_price != ''">{{ custom_service_duration_data.staff_service_formatted_price }}</h4>
													<h4 class="bpa-card__item__heading is--body-heading" v-else></h4>
												</div>
											</el-col>											
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-else>
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading">{{ assigned_service_details.assign_service_formatted_price }}</h4>
													<h4 class="bpa-card__item__heading is--body-heading" v-else></h4>
												</div>
											</el-col>
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07" v-if="is_bring_anyone_with_you_activated == 1">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading">{{ assigned_service_details.assign_service_min_capacity }}</h4>
												</div>
											</el-col>
											<el-col :xs="07" :sm="07" :md="07" :lg="07" :xl="07">
												<div class="bpa-card__item">
													<h4 class="bpa-card__item__heading is--body-heading">{{ assigned_service_details.assign_service_capacity }}</h4>
												</div>
											</el-col>
											<?php do_action( 'bookingpress_staff_assigned_services_column_value'); ?>
											<el-col :xs="3" :sm="3" :md="3" :lg="3" :xl="3">
												<div>
													<el-tooltip effect="dark" content="" placement="top" open-delay="300">
														<div slot="content">
															<span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
														</div>
														<el-button class="bpa-btn bpa-btn--icon-without-box" @click="bookingpress_edit_assigned_service(assigned_service_details.assign_service_id, event)">
															<span class="material-icons-round">mode_edit</span>
														</el-button>
													</el-tooltip>
													<el-tooltip effect="dark" content="" placement="top" open-delay="300">
														<div slot="content">
															<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
														</div>
														<el-button class="bpa-btn bpa-btn--icon-without-box __danger" @click="bookingpress_delete_assigned_service(assigned_service_details.assign_service_id)">
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
				</el-col>
			</el-row>
		</div>
		<?php // ----------------------------------------------------------------- ?>

		<?php // Integrations section ?>
		<?php // ----------------------------------------------------------------- ?>
		<?php
		if ( is_plugin_active( 'bookingpress-zoom/bookingpress-zoom.php' ) || is_plugin_active( 'bookingpress-outlook-calendar/bookingpress-outlook-calendar.php' ) || is_plugin_active( 'bookingpress-google-calendar/bookingpress-google-calendar.php' ) ) {
			?>
		<div class="bpa-form-row">
			<el-row>
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<div class="bpa-db-sec-heading">						
						<el-row type="flex" align="middle">
							<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
								<div class="bpa-db-sec-left">
									<h2 class="bpa-page-heading"><?php esc_html_e( 'Integration', 'bookingpress-appointment-booking' ); ?></h2>
								</div>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-default-card bpa-db-card bpa-dc__integration-module">
						<?php do_action( 'bookingpress_staff_member_view' ); ?>
					</div>		
				</el-col>
			</el-row>
		</div>					
		<?php } ?>
		
		<?php // ----------------------------------------------------------------- ?>
		
	</div>
</el-dialog>
<el-dialog :custom-class="typeof is_active_service_custom_duration != 'undefined' && is_active_service_custom_duration == 1 ? 'bpa-dialog bpa-dailog__small bpa-dialog--add-assign-service bpa-dialog__is-custom-duration-addon-activated' : 'bpa-dialog bpa-dailog__small bpa-dialog--add-assign-service'" title="" :visible.sync="open_assign_service_modal" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Assign Service', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>			
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
					<el-form label-position="top" @submit.native.prevent>
						<div class="bpa-form-body-row">
							<el-row>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item>
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?></span>
										</template> 
										<el-select v-model="assign_service_form.assign_service_id" class="bpa-form-control" filterable collapse-tags placeholder="<?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?>" :popper-append-to-body="false" popper-class="bpa-el-select--is-with-navbar" @change="bookingpress_set_assign_service_name($event)">
											<el-option-group v-for="item in bookingpress_service_list" :key="item.category_name" :label="item.category_name">
												<el-option v-for="cat_services in item.category_services" :key="cat_services.service_id" :label="cat_services.service_name" :value="cat_services.service_id"></el-option>
											</el-option-group>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col v-if="is_bring_anyone_with_you_activated == 1" :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item>
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Min Capacity', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" :max="999" v-model="assign_service_form.assign_service_min_capacity" step-strictly></el-input-number>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item>
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Max Capacity', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" :max="999" v-model="assign_service_form.assign_service_capacity" step-strictly></el-input-number>
									</el-form-item>
								</el-col>
								<?php do_action( 'bookingpress_staff_assign_service_outside_control'); ?>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="is_display_default_price_field == true">
									<el-form-item>
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Price', 'bookingpress-appointment-booking' ); ?>({{ bookingpress_currency }})</span>
										</template>
										<el-input @input="staffmember_service_price_validate($event)" v-model="assign_service_form.assign_service_price" class="bpa-form-control" placeholder="0.00" />
									</el-form-item>
								</el-col>
								<?php
									do_action('bookingpress_add_staff_custom_service_duration_field');
								?>								
							</el-row>
						</div>
					</el-form>
				</el-col>
			</div>
		</el-container>
	</div>
	<div class="bpa-dialog-footer">
		<div class="bpa-hw-right-btn-group">
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="bookingpress_save_assigned_service()"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small" @click="close_assign_service_modal_func()"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>

<el-dialog custom-class="bpa-dialog bpa-dialog--fullscreen bpa-dialog__staff-shift-management bpa-dialog__shift-management bpa--is-page-scrollable-tablet" modal-append-to-body=false :visible.sync="open_shift_management_modal" fullscreen=true :close-on-press-escape="close_modal_on_esc">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading"><?php esc_html_e( 'Shift Management', 'bookingpress-appointment-booking' ); ?> - <span>{{ shift_mgmt_staff_name }}</span></h1>
			</el-col>
			<el-col :xs="12" :sm="12" :md="7" :lg="7" :xl="7" class="bpa-dh__btn-group-col">				
				<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="saveShiftManagementDetails()" :disabled="is_disabled">		
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
		<div class="bpa-form-row--parent" :class="bpa_staff_sm_shift_management_cls">
			<div class="bpa-form-row bpa-sm__working-hours">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<div class="bpa-db-sec-heading">						
							<el-row type="flex" align="middle">
								<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
									<div class="bpa-db-sec-left">
										<h2 class="bpa-page-heading"><?php esc_html_e( 'Working Hours', 'bookingpress-appointment-booking' ); ?></h2>
									</div>
								</el-col>
							</el-row>
						</div>					
						<div class="bpa-default-card bpa-db-card bpa-db-card-is-full-width-title" :class="bpa_service_shift_management_full_width_title_cls" id="bpa-db-card-is-full-width-title">
							<?php
								do_action('bookingpress_add_staffmember_shift_management_content');
							?>
							<template>
								<h3 class="bpa-wh__head-row-subheading"><?php esc_html_e( 'Configure Hours', 'bookingpress-appointment-booking' ); ?></h3>
								<div class="bpa-sm__wh-head-row">
									<div class="bpa-wh__head-row-title">
										<span class="bpa-form-label"><?php esc_html_e( 'Configure staff specific work hours', 'bookingpress-appointment-booking' ); ?></span>
									</div>
									<div class="bpa-wh__head-row-swtich">
										<el-switch class="bpa-swtich-control" v-model="bookingpress_configure_specific_workhour"></el-switch>
									</div>
								</div>
								<div class="bpa-sm__wh-items" v-if="bookingpress_configure_specific_workhour == true && display_staff_working_hours == true">
									<div class="bpa-sm__wh-body-row" v-for="work_hours_day in work_hours_days_arr">
										<el-row class="bpa-sm__wh-item-row" :gutter="24" :id="'weekday_'+work_hours_day.day_key">
											<el-col :xs="24" :sm="24" :md="18" :lg="20" :xl="22">
												<el-row type="flex" class="bpa-sm__wh-body-left">
													<el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="2">
														<span class="bpa-form-label" v-if="work_hours_day.day_name == 'Monday'"><?php esc_html_e('Monday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Tuesday'"><?php esc_html_e('Tuesday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Wednesday'"><?php esc_html_e('Wednesday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Thursday'"><?php esc_html_e('Thursday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Friday'"><?php esc_html_e('Friday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Saturday'"><?php esc_html_e('Saturday', 'bookingpress-appointment-booking'); ?></span>
														<span class="bpa-form-label" v-else-if="work_hours_day.day_name == 'Sunday'"><?php esc_html_e('Sunday', 'bookingpress-appointment-booking'); ?></span>
														<span v-else>{{ work_hours_day.day_name }}</span>
													</el-col>
													<el-col :xs="24" :sm="24" :md="18" :lg="18" :xl="22">
														<el-row :gutter="24">
															<el-col :xs="8" :sm="8" :md="12" :lg="12" :xl="12">												
																<el-select v-model="workhours_timings[work_hours_day.day_name].start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>"
																	@change="bookingpress_set_workhour_value($event,work_hours_day.day_name)" filterable>
																	<span slot="prefix" class="material-icons-round">access_time</span>
																	<el-option v-for="work_timings in work_hours_day.worktimes" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="work_timings.start_time != workhours_timings[work_hours_day.day_name].end_time || workhours_timings[work_hours_day.day_name].end_time == 'Off'"></el-option>
																</el-select>
															</el-col>
															<el-col :xs="8" :sm="8" :md="12" :lg="12" :xl="12" v-if="workhours_timings[work_hours_day.day_name].start_time != 'Off'">
																<el-select v-model="workhours_timings[work_hours_day.day_name].end_time" class="bpa-form-control bpa-form-control__left-icon" 
																	placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>"
																	@change="bookingpress_check_workhour_value($event,work_hours_day.day_name)"  filterable>
																	<span slot="prefix" class="material-icons-round">access_time</span>
																	<el-option v-for="work_timings in work_hours_day.worktimes" :label="work_timings.formatted_end_time" :value="work_timings.end_time" v-if="(work_timings.end_time > workhours_timings[work_hours_day.day_name].start_time ||  work_timings.end_time == '24:00:00')"></el-option>				
																</el-select>
															</el-col>
														</el-row>
														<el-row  v-if="selected_break_timings[work_hours_day.day_name].length > 0 && workhours_timings[work_hours_day.day_name].start_time != 'Off'">
															<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
																<div class="bpa-break-hours-wrapper">
																	<h4><?php esc_html_e( 'Breaks', 'bookingpress-appointment-booking' ); ?></h4>
																	<div class="bpa-bh--items">
																		<div class="bpa-bh__item" v-for="(break_data,index) in work_hours_day.break_times">
																			<p @click="edit_workhour_data(event,break_data.start_time, break_data.end_time, work_hours_day.day_name,index)">{{ break_data.formatted_start_time }} to {{ break_data.formatted_end_time }}</p>
																			<span class="material-icons-round" slot="reference" @click="bookingpress_remove_workhour(break_data.start_time, break_data.end_time, work_hours_day.day_name)">close</span>
																		</div>
																	</div>
																</div>
															</el-col>
														</el-row>
													</el-col>
												</el-row>
											</el-col>
											<el-col :xs="24" :sm="24" :md="6" :lg="4" :xl="2" v-if="workhours_timings[work_hours_day.day_name].start_time != 'Off'">
												<el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" :class="(break_selected_day == work_hours_day.day_name && open_add_break_modal == true) ? 'bpa-btn--primary' : ''" @click="open_add_break_modal_func(event, work_hours_day.day_name)">
													<?php esc_html_e( 'Add Break', 'bookingpress-appointment-booking' ); ?>
												</el-button>
											</el-col>
										</el-row>
									</div>
								</div>
								<?php do_action( 'bookingpress_staff_work_hour_content_outside') ?>
							</template>
						</div>				
					</el-col>
				</el-row>
			</div>
			<div class="bpa-form-row bpa-sm__special-days">
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
										<el-button class="bpa-btn bpa-btn__filled-light" @click="open_special_days_func(event)">
											<span class="material-icons-round">add</span>
											<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
										</el-button>
									</div>
								</el-col>
							</el-row>
						</div>
						<div class="bpa-default-card bpa-grid-list-container bpa-dc__staff--assigned-service bpa-sm__special-days-card" v-if="display_staff_working_hours == true">
							<el-row class="bpa-dc--sec-sub-head" v-if="staffmember_special_day_arr.length != 0">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<h2 class="bpa-sec--sub-heading"><?php esc_html_e( 'All Special Days', 'bookingpress-appointment-booking' ); ?></h2>
								</el-col>
							</el-row>
							<div class="bpa-as__body bpa-sm__doc-body">
								<el-row type="flex" class="bpa-as__empty-view" v-if="staffmember_special_day_arr.length == 0">
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
								<el-row class="bpa-assigned-service-body" v-if="staffmember_special_day_arr.length > 0">
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
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="staffmember_special_day in staffmember_special_day_arr">
										<div class="bpa-card bpa-card__body-row">
											<el-row type="flex">
												<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading is--body-heading">{{ staffmember_special_day.special_day_formatted_start_date }} - {{ staffmember_special_day.special_day_formatted_end_date }}</h4>
													</div>
												</el-col>
												<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading is--body-heading">( {{staffmember_special_day.formatted_start_time}} - {{staffmember_special_day.formatted_end_time}} )</h4>
													</div>
												</el-col>	
												<el-col :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
													<div class="bpa-card__item"> 
														<span v-if="staffmember_special_day.special_day_workhour != undefined && staffmember_special_day.special_day_workhour != ''">		
															<h4 class="bpa-card__item__heading is--body-heading" v-for="special_day_workhours in staffmember_special_day.special_day_workhour" v-if="special_day_workhours.formatted_start_time != undefined && special_day_workhours.formatted_start_time != '' && special_day_workhours.formatted_end_time != undefined && special_day_workhours.formatted_end_time != '' && special_day_workhours.start_time != '' && special_day_workhours.end_time != ''"> 
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
															<el-button class="bpa-btn bpa-btn--icon-without-box" @click="show_edit_special_day_div(staffmember_special_day.id, event)">
																<span class="material-icons-round">mode_edit</span>
															</el-button>
														</el-tooltip>
														<el-tooltip effect="dark" content="" placement="top" open-delay="300">
															<div slot="content">
																<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
															</div>
															<el-button class="bpa-btn bpa-btn--icon-without-box __danger" @click="delete_special_day_div(staffmember_special_day.id)">
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
						<?php do_action( 'bookingpress_staff_special_days_external_content'); ?>
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
									<el-button class="bpa-btn bpa-btn__filled-light" @click="open_days_off_modal_func(event)">
										<span class="material-icons-round">add</span>
										<?php esc_html_e( 'Add New', 'bookingpress-appointment-booking' ); ?>
									</el-button>
								</div>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-default-card bpa-grid-list-container bpa-dc__staff--assigned-service bpa-sm__days-off-card">
						<el-row class="bpa-dc--sec-sub-head" v-if="staffmember_dayoff_arr.length != 0">
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<h2 class="bpa-sec--sub-heading"><?php esc_html_e( 'All Holiday', 'bookingpress-appointment-booking' ); ?></h2>
							</el-col>
						</el-row>
						<div class="bpa-as__body bpa-sm__doc-body">
							<el-row type="flex" class="bpa-as__empty-view" v-if="staffmember_dayoff_arr.length == 0">
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
							<div v-if="staffmember_dayoff_arr.length > 0">
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
									<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-for="staffmember_day_off in staffmember_dayoff_arr">
										<div class="bpa-card bpa-card__body-row list-group-item">
											<el-row type="flex">
												<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
													<div class="bpa-card__item --bpa-sm-is-legends-item">
														<h4 v-if="staffmember_day_off.dayoff_date == staffmember_day_off.dayoff_date_end" class="bpa-card__item__heading is--body-heading">{{ staffmember_day_off.dayoff_date | bookingpress_format_date }}</h4>	
														<h4 v-else class="bpa-card__item__heading is--body-heading">{{ staffmember_day_off.dayoff_date | bookingpress_format_date }} - {{ staffmember_day_off.dayoff_date_end | bookingpress_format_date }}</h4>	
													</div>
												</el-col>
												<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading is--body-heading">{{ staffmember_day_off.dayoff_name }}</h4>
													</div>
												</el-col>
												<el-col :xs="10" :sm="10" :md="10" :lg="10" :xl="10">
													<div class="bpa-card__item">
														<h4 class="bpa-card__item__heading bpa--staff-repeat-holiday-label is--body-heading">{{ staffmember_day_off.dayoff_repeat_label }}</h4>
													</div>
												</el-col>
												<el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
													<div>
														<el-tooltip effect="dark" content="" placement="top" open-delay="300">
															<div slot="content">
																<span><?php esc_html_e( 'Edit', 'bookingpress-appointment-booking' ); ?></span>
															</div>
															<el-button class="bpa-btn bpa-btn--icon-without-box" @click="show_edit_dayoff_div(staffmember_day_off.id, event)">
																<span class="material-icons-round">mode_edit</span>
															</el-button>
														</el-tooltip>
														<el-tooltip effect="dark" content="" placement="top" open-delay="300">
															<div slot="content">
																<span><?php esc_html_e( 'Delete', 'bookingpress-appointment-booking' ); ?></span>
															</div>
															<el-button class="bpa-btn bpa-btn--icon-without-box __danger" @click="delete_dayoff_div(staffmember_day_off.id)">
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

<el-dialog id="staffmember_breaks_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--add-break" title="" :visible.sync="open_add_break_modal" :visible.sync="centerDialogVisible" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display">
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
						<el-form :rules="rules_add_break"  ref="break_timings" :model="break_timings" label-position="top" @submit.native.prevent>
							<div class="bpa-form-body-row">
								<el-row :gutter="24">
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<el-form-item prop="start_time">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-select class="bpa-form-control bpa-form-control__left-icon" v-model="break_timings.start_time" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable>
												<span slot="prefix" class="material-icons-round">access_time</span>
												<el-option v-for="break_times in default_break_timings" :key="break_times.start_time" :label="break_times.formatted_start_time" :value="break_times.start_time" v-if="break_times.start_time > workhours_timings[break_selected_day].start_time && break_times.start_time < workhours_timings[break_selected_day].end_time"></el-option>
											</el-select>
										</el-form-item>
									</el-col>
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<el-form-item prop="end_time">
											<template #label>
												<span class="bpa-form-label"><?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?></span>
											</template>
											<el-select class="bpa-form-control bpa-form-control__left-icon"  v-model="break_timings.end_time" placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>" filterable>
												<span slot="prefix" class="material-icons-round">access_time</span>
												<el-option v-for="break_times in default_break_timings" :key="break_times.start_time" :label="break_times.formatted_start_time" :value="break_times.start_time"
												v-if="(break_times.start_time > workhours_timings[break_selected_day].start_time && break_times.start_time < workhours_timings[break_selected_day].end_time) && (break_times.start_time > break_timings.start_time)"
												></el-option>
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
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="save_break_data"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small" @click="close_add_break_model"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>

<el-dialog id="days_off_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--days-off bpa-staff--days-off-dialog" title="" :visible.sync="days_off_add_modal" :close-on-press-escape="close_modal_on_esc" @close="closeStaffmemberDayoff" :modal="is_mask_display">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading" v-if="edit_staffmember_dayoff == ''"><?php esc_html_e( 'Add Holiday', 'bookingpress-appointment-booking' ); ?></h1>
				<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Holiday', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form ref="staffmember_dayoff_form" :rules="rules_dayoff" :model="staffmember_dayoff_form" label-position="top">
							<el-row>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item prop="dayoff_date">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Date:', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-date-picker @focus="bookingpress_remove_date_range_picker_focus" @change="bookingpress_after_select_staff_holiday" class="bpa-form-control bpa-form-control--date-range-picker" v-model="staffmember_dayoff_form.dayoff_date_range" type="daterange" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" start-placeholder="<?php esc_html_e( 'Start Date', 'bookingpress-appointment-booking' ); ?>" end-placeholder="<?php esc_html_e( 'End Date', 'bookingpress-appointment-booking' ); ?>" :picker-options="disablePastDates" value-format="yyyy-MM-dd"> </el-date-picker>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">									
									<el-form-item prop="dayoff_name">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Holiday Name:', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input class="bpa-form-control" @blur="staffmemebr_daysoff_name_validation(staffmember_dayoff_form.dayoff_name)" v-model="staffmember_dayoff_form.dayoff_name" id="dayoff_name" name="dayoff_name" placeholder="<?php esc_html_e( 'Enter holiday name', 'bookingpress-appointment-booking' ); ?>"></el-input>
									</el-form-item>
								</el-col>								
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item>
										<el-checkbox v-model="staffmember_dayoff_form.dayoff_repeat"><span class="bpa-form-label"><?php esc_html_e( 'Repeat Holiday', 'bookingpress-appointment-booking' ); ?></span></el-checkbox>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly" v-if="true == staffmember_dayoff_form.dayoff_repeat">
									<el-form-item>
										<template #label>
                                            <span class="bpa-form-label"><?php esc_html_e( 'Repeat Every', 'bookingpress-appointment-booking'); ?></span>
                                        </template>
										<el-row class="bpa-fbr__sub-row">
											<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
												<el-input-number v-model="staffmember_dayoff_form.dayoff_repeat_frequency" class="bpa-form-control bpa-form-control--number" :min="1" :max="999" placeholder="<?php esc_html_e( 'Enter Holiday Repeat Frequency', 'bookingpress-appointment-booking'); ?>" ></el-input-number>
											</el-col>
											<el-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
												<el-select class="bpa-form-control" v-model="staffmember_dayoff_form.dayoff_repeat_freq_type" popper-class="bpa-se--holiday-repeat-frequency-type-dropdown">
													<el-option v-for="( rf_label, rf_value ) in staff_dayoff_repeat_frequency_type_opts" :label="rf_label" :value="rf_value"></el-option>
												</el-select>
											</el-col>
										</el-row>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly" v-if="true == staffmember_dayoff_form.dayoff_repeat">
									<el-form-item prop="dayoff_repeat_duration">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Duration', 'bookingpress-appointment-booking'); ?></span>
										</template>
										<el-select class="bpa-form-control" v-model="staffmember_dayoff_form.dayoff_repeat_duration" popper-class="bpa-se--holiday-repeat-duration-type-dropdown">
											<el-option v-for="( rd_label, rd_value ) in repeat_duration_opts" :label="rd_label" :value="rd_value"></el-option>
										</el-select>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly" v-if="true == staffmember_dayoff_form.dayoff_repeat && 'no_of_times' == staffmember_dayoff_form.dayoff_repeat_duration">
									<el-form-item>
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Holiday Repeat Times', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input-number v-model="staffmember_dayoff_form.dayoff_repeat_times" class="bpa-form-control bpa-form-control--number" :min="1"></el-input-number>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-add-dayoff-col--is-repeat-yearly bpa-add-dayoff-col--repeat-date" v-if="true == staffmember_dayoff_form.dayoff_repeat && 'until' == staffmember_dayoff_form.dayoff_repeat_duration">
									<el-form-item prop="repeat_date">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Holiday Repeat Date', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-date-picker :picker-options="StaffholidayPickerOptions" class="bpa-form-control bpa-form-control--date-picker" v-model="staffmember_dayoff_form.dayoff_repeat_date"></el-date-picker>
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
			<el-button class="bpa-btn bpa-btn__small" @click="closeStaffmemberDayoff"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="addStaffmemberDayoff('staffmember_dayoff_form')" :disabled="disable_staff_holiday_btn"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>

<el-dialog id="special_days_add_modal" custom-class="bpa-dialog bpa-dailog__small bpa-dialog--special-days" title="" :visible.sync="special_days_add_modal" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display">
	<div class="bpa-dialog-heading">
		<el-row type="flex">
			<el-col :xs="12" :sm="12" :md="16" :lg="16" :xl="16">
				<h1 class="bpa-page-heading" v-if="edit_staffmember_special_day == ''"><?php esc_html_e( 'Add Special Days', 'bookingpress-appointment-booking' ); ?></h1>
				<h1 class="bpa-page-heading" v-else><?php esc_html_e( 'Edit Special Days', 'bookingpress-appointment-booking' ); ?></h1>
			</el-col>
		</el-row>
	</div>
	<div class="bpa-dialog-body">
		<el-container class="bpa-grid-list-container bpa-add-categpry-container">
			<div class="bpa-form-row">
				<el-row>
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form ref="staffmember_special_day_form" :rules="rules_special_day" :model="staffmember_special_day_form" label-position="top">
							<el-row>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item prop="special_day_date">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Date:', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-date-picker @focus="bookingpress_remove_date_range_picker_focus" class="bpa-form-control bpa-form-control--date-range-picker" v-model="staffmember_special_day_form.special_day_date" type="daterange" format="<?php echo esc_html( $bookingpress_common_date_format ); ?>" :picker-options="disablePastDates" placeholder="<?php esc_html_e( 'Select Date', 'bookingpress-appointment-booking' ); ?>" range-separator=" - " :popper-append-to-body="false" start-placeholder="<?php esc_html_e( 'Start date', 'bookingpress-appointment-booking' ); ?>" end-placeholder="<?php esc_html_e( 'End date', 'bookingpress-appointment-booking' ); ?>" value-format="yyyy-MM-dd">
										</el-date-picker>										
									</el-form-item>									
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-form-item prop="special_day_service">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Select Service', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-select class="bpa-form-control"  v-model="staffmember_special_day_form.special_day_service" name="special_day_service" multiple filterable collapse-tags  placeholder="<?php esc_html_e( 'Applied for all assigned services', 'bookingpress-appointment-booking' ); ?>"
											popper-class="bpa-el-select--is-with-modal">
											<el-option-group v-if="(typeof bookingpress_staff_assign_services_list  != 'undefined' && bookingpress_staff_assign_services_list.length > 0)" v-for="service_cat_data in bookingpress_staff_assign_services_list" :key="service_cat_data.category_name" :label="service_cat_data.category_name">
												<template v-if="service_data.service_id == 0" v-for="service_data in service_cat_data.category_services">
													<el-option :key="service_data.service_id" :label="service_data.service_name" :value="''" ></el-option>
												</template>
												<template v-else>
													<el-option :key="service_data.service_id" :label="service_data.service_name+' ('+service_data.service_price+' )'" :value="service_data.service_id"></el-option>
												</template>
											</el-option-group>
										</el-select>
									</el-form-item>
								</el-col>												
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<el-row type="flex" class="bpa-sd__time-selection">
										<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
											<el-form-item prop="start_time">												
												<template #label>
													<span class="bpa-form-label"><?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?></span>
												</template>
																
												<el-select v-model="staffmember_special_day_form.start_time" name ="start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable> 
													<span slot="prefix" class="material-icons-round">access_time</span>
													<el-option v-for="work_timings in specialday_hour_list"  :label="work_timings.formatted_start_time" :value="work_timings.start_time" ></el-option >
												</el-select>
											</el-form-item>	
										</el-col>
										<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
											<el-form-item prop="end_time">
												<el-select v-model="staffmember_special_day_form.end_time" name ="end_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>" filterable>
													<span slot="prefix" class="material-icons-round">access_time</span>
													<el-option v-for="work_timings in specialday_hour_list" :label="work_timings.formatted_end_time" :value="work_timings.end_time" v-if="work_timings.end_time > staffmember_special_day_form.start_time">
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
										<el-link class="bpa-sd__add-period-btn-link" @click="bookingpress_add_special_day_period()">
											<span class="material-icons-round">add_circle</span>
											<?php esc_html_e( 'Add Break', 'bookingpress-appointment-booking' ); ?>
										</el-link>
									</div>
								</el-col>
							</el-row>
							<el-row class="bpa-sd--add-period-row" v-for="special_day_workhours in staffmember_special_day_form.special_day_workhour">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
									<div class="bpa-ts__item">
										<div class="bpa-ts__item-left">
											<el-row type="flex" class="bpa-sd__time-selection">
												<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
													<el-form-item >											
														<template #label>
															<span class="bpa-form-label"><?php esc_html_e( 'Select Time', 'bookingpress-appointment-booking' ); ?></span>
														</template>
														<el-select v-model="special_day_workhours.start_time" name ="start_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'Start Time', 'bookingpress-appointment-booking' ); ?>" filterable> 
															<span slot="prefix" class="material-icons-round">access_time</span>
															<el-option v-for="work_timings in specialday_break_hour_list" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="work_timings.start_time > staffmember_special_day_form.start_time && work_timings.start_time < staffmember_special_day_form.end_time"></el-option >
														</el-select>																					
													</el-form-item>	
												</el-col>
												<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
													<el-form-item >
														<el-select v-model="special_day_workhours.end_time" name ="end_time" class="bpa-form-control bpa-form-control__left-icon" placeholder="<?php esc_html_e( 'End Time', 'bookingpress-appointment-booking' ); ?>" filterable>
															<span slot="prefix" class="material-icons-round">access_time</span>
															<el-option v-for="work_timings in specialday_break_hour_list" :label="work_timings.formatted_start_time" :value="work_timings.start_time" v-if="((work_timings.start_time > staffmember_special_day_form.start_time && work_timings.start_time < staffmember_special_day_form.end_time) && (work_timings.start_time > special_day_workhours.start_time))">
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
			<el-button class="bpa-btn bpa-btn__small bpa-btn--primary" @click="addStaffmemberSpecialday('staffmember_special_day_form')" :disabled="disable_staff_special_day_btn"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__small" @click="closeStaffmemberSpecialday"><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
		</div>
	</div>
</el-dialog>

<?php if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_export_staffmembers' ) ) { ?>		
<el-dialog custom-class="bpa-dialog bpa-dailog__small bpa-dialog--export-staffmembmers" id="staffmember_export_model" title="" :visible.sync="ExportStaffmember" :close-on-press-escape="close_modal_on_esc" :modal="is_mask_display" @open="bookingpress_enable_modal" @close="bookingpress_disable_modal">
	<div class="bpa-dialog-heading">
		<el-row>
			<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
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
												<el-checkbox class="bpa-form-label bpa-custom-checkbox--is-label" v-for="item in staffmember_export_field_list" :label="item.name">{{item.text}}</el-checkbox>  
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
			<el-button class="bpa-btn bpa-btn__medium" @click="close_export_staffmember_model" ><?php esc_html_e( 'Cancel', 'bookingpress-appointment-booking' ); ?></el-button>
			<el-button class="bpa-btn bpa-btn__medium bpa-btn--primary" :class="(is_export_button_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="
			bookingpress_export_staffmember" :disabled="is_export_button_disabled" >					
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
<?php } ?>