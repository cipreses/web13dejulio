<?php
	global $bookingpress_ajaxurl, $BookingPressPro, $bookingpress_common_date_format;
	$bpa_edit_basic_details           = $BookingPressPro->bookingpress_check_capability( 'bookingpress_myprofile' );
	$bpa_manage_calendar_integrations = $BookingPressPro->bookingpress_check_capability( 'bookingpress_manage_calendar_integration' );
?>

<el-main class="bpa-main-listing-card-container bpa-default-card bpa-staff-edit-profile-container bpa--is-page-non-scrollable-mob" :class="(bookingpress_staff_customize_view == 1 ) ? 'bpa-main-list-card__is-staff-custom-view':''" id="all-page-main-container">
	<el-row type="flex" class="bpa-mlc-head-wrap">
		<el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-mlc-left-heading">
			<h1 class="bpa-page-heading"><?php esc_html_e( 'Edit Profile', 'bookingpress-appointment-booking' ); ?></h1>
		</el-col>
		<el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
			<div class="bpa-hw-right-btn-group">
			<?php
			if ( $bpa_edit_basic_details ) {
				?>
				<el-button class="bpa-btn bpa-btn--primary" :class="(is_display_save_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bookingpress_save_staffmember_details()" :disabled="is_disabled">
					<span class="bpa-btn__label"><?php esc_html_e( 'Save', 'bookingpress-appointment-booking' ); ?></span>
					<div class="bpa-btn--loader__circles">
						<div></div>
						<div></div>
						<div></div>
					</div>
				</el-button>
				<?php
			}
			?>
			</div>
		</el-col>
	</el-row>
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>
	<div id="bpa-main-container">
		<div class="bpa-sep--body-content">
			<div class="bpa-form-row">
				<el-form @submit.native.prevent>
					<template>
						<div class="bpa-form-body-row">
							<el-row :gutter="24">
								<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-form-group">
									<?php if ( $bpa_edit_basic_details ) { ?>
									<el-upload class="bpa-upload-component" ref="avatarRef" action="<?php echo wp_nonce_url( admin_url('admin-ajax.php') . '?action=bookingpress_upload_staff_member_avatar', 'bookingpress_upload_staff_member_avatar' ); // phpcs:ignore ?>" :on-success="bookingpress_upload_staff_member_avatar_func" multiple="false" :file-list="edit_profile_details.avatar_list" :show-file-list="staffShowFileList" limit="1" :on-exceed="bookingpress_image_upload_limit" :on-error="bookingpress_image_upload_err" :on-remove="bookingpress_remove_staff_members_avatar" :before-upload="checkUploadedFile" drag >
										<span class="material-icons-round bpa-upload-component__icon">cloud_upload</span>
									<div class="bpa-upload-component__text" v-if="edit_profile_details.avatar_url == ''"><?php esc_html_e( 'Please upload jpg/png/webp file', 'bookingpress-appointment-booking' ); ?>									   	
									</div>
									</el-upload>
										<?php
									}
									?>
									<div class="bpa-uploaded-avatar__preview" v-if="edit_profile_details.avatar_url != ''">
										<?php if ( $bpa_edit_basic_details ) { ?>
										<button class="bpa-avatar-close-icon" @click="bookingpress_remove_staff_members_avatar" >
											<span class="material-icons-round">close</span>
										</button>
											<?php
										}
										?>
										<el-avatar shape="square" :src="edit_profile_details.avatar_url" class="bpa-uploaded-avatar__picture"></el-avatar>
									</div>
								</el-col>
							</el-row>
							<el-row :gutter="32">
								<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
									<el-form-item prop="firstname">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'First Name', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input class="bpa-form-control" v-model="edit_profile_details.firstname" id="firstname" name="firstname" placeholder="<?php esc_html_e( 'Enter First Name', 'bookingpress-appointment-booking' ); ?>" <?php echo ! $bpa_edit_basic_details ? 'disabled' : ''; ?> ></el-input>
									</el-form-item>
								</el-col>
								<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
									<el-form-item prop="lastname">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Last Name', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input class="bpa-form-control" v-model="edit_profile_details.lastname" id="lastname" name="lastname" placeholder="<?php esc_html_e( 'Enter Last Name', 'bookingpress-appointment-booking' ); ?>" <?php echo ! $bpa_edit_basic_details ? 'disabled' : ''; ?> ></el-input>
									</el-form-item>
								</el-col>											
								<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
									<el-form-item prop="email">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Email', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input class="bpa-form-control" v-model="edit_profile_details.email" id="email" name="email" placeholder="<?php esc_html_e( 'Enter Email', 'bookingpress-appointment-booking' ); ?>" disabled ></el-input>
									</el-form-item>
								</el-col>
								<br/><br/><br/><br/><br/>
								<el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
									<el-form-item prop="phone">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Phone', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<vue-tel-input v-model="edit_profile_details.phone" class="bpa-form-control --bpa-country-dropdown" @country-changed="bookingpress_phone_country_change_func($event)" v-bind="bookingpress_tel_input_props" ref="bpa_tel_input_field" <?php echo ! $bpa_edit_basic_details ? 'disabled' : ''; ?> >
											<template v-slot:arrow-icon>
												<span class="material-icons-round">keyboard_arrow_down</span>
											</template>
										</vue-tel-input>
									</el-form-item>
								</el-col>																		
								<el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
									<el-form-item prop="note">
										<template #label>
											<span class="bpa-form-label"><?php esc_html_e( 'Note', 'bookingpress-appointment-booking' ); ?></span>
										</template>
										<el-input class="bpa-form-control" type="textarea" :rows="3" v-model="edit_profile_details.note" <?php echo ! $bpa_edit_basic_details ? 'disabled' : ''; ?>> </el-input>
									</el-form-item>
								</el-col> 
							</el-row>									
						</div>
					</template>
				</el-form>
			</div>
			<?php if ( $bpa_manage_calendar_integrations  && ( is_plugin_active( 'bookingpress-zoom/bookingpress-zoom.php' ) || is_plugin_active( 'bookingpress-outlook-calendar/bookingpress-outlook-calendar.php' ) || is_plugin_active( 'bookingpress-google-calendar/bookingpress-google-calendar.php' ) ) )  { ?>
			<div class="bpa-form-row">
				<div class="bpa-sep--staff-integrations">
					<el-row type="flex" class="bpa-mlc-head-wrap">
						<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
							<h1 class="bpa-page-heading"><?php esc_html_e( 'Integrations', 'bookingpress-appointment-booking' ); ?></h1>
						</el-col>
					</el-row>
					<?php do_action( 'bookingpress_staff_member_view' ); ?>
				</div>
			</div>
			<?php } ?>
		</div>		
	</div>
</el-main>