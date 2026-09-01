<?php
	global $bookingpress_ajaxurl, $BookingPressPro, $bookingpress_common_date_format;
?>
<el-main class="bpa-main-listing-card-container bpa-default-card bpa-myservices-contanier bpa--is-page-non-scrollable-mob" :class="(bookingpress_staff_customize_view == 1 ) ? 'bpa-main-list-card__is-staff-custom-view':''" id="all-page-main-container">
	<el-row type="flex" class="bpa-mlc-head-wrap">
		<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
			<h1 class="bpa-page-heading"><?php esc_html_e( 'My Services', 'bookingpress-appointment-booking' ); ?></h1>
			<span class="bpa-ms__count-pill">{{ assigned_services_details.length }} <?php esc_html_e( 'Assigned Services', 'bookingpress-appointment-booking' ); ?></span>
		</el-col>
	</el-row>
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>
	<div id="bpa-main-container">
		<div class="bpa-myservices--items-wrapper">
			<div class="bpa-myservice__items-row">
				<el-row type="flex" :gutter="28">
					<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="08" v-for="service_data in assigned_services_details">
						<div class="bpa-ms__item">
							<div class="bpa-ms-item__head">
								<div class="bpa-ih__left">
									<div class="bpa-ih--avatar">
										<el-image class="bpa-assigned-service-avatar" :src="service_data.bookingpress_service_img_url"></el-image>
									</div>
									<div class="bpa-ih--service-brief">
										<h4>{{ service_data.bookingpress_service_name }}</h4>
										<div class="bpa-sb__row">
											<div class="bpa-sb__item">
												<p><span class="material-icons-round">watch_later</span>{{ service_data.bookingpress_service_duration }}</p>
												<p><span class="material-icons-round">group</span><?php esc_html_e( 'Max', 'bookingpress-appointment-booking' ); ?>: {{ service_data.bookingpress_max_capacity }}</p>
											</div>
										</div>
									</div>
								</div>
								<div class="bpa-ih__right">
									<h4 class="bpa-ih--service-price">{{ service_data.bookingpress_service_formatted_price }}</h4>
									<span class="bpa-deposit-amount" v-if="service_data.bookingpress_deposit_amount != '0'"><?php esc_html_e( 'Deposit', 'bookingpress-appointment-booking' ); ?>: {{ service_data.bookingpress_deposit_amount }}</span>
								</div>
							</div>
							<div class="bpa-ms-item__category">
								<div class="bpa-category--item">
									<p><span class="material-icons-round">dns</span>{{ service_data.bookingpress_category }}</p>
								</div>
							</div>
							<div class="bpa-ms-item__desc">
								<p>{{ service_data.bookingpress_service_description }}</p>
							</div>
							<div class="bpa-ms-item__extras" v-if="service_data.bookingpress_extra_services.length > 0 && service_extra_activated == '1'" >
								<h5><?php esc_html_e( 'Extra\'s', 'bookingpress-appointment-booking' ); ?></h5>
								<div class="bpa-extra--item-row">
									<div class="bpa-extra__item" v-for="extra_service_data in service_data.bookingpress_extra_services">
										<h4><span class="material-icons-round">check</span>{{ extra_service_data.service_name }} - <strong> {{ extra_service_data.service_formatted_price }}</strong></h4>										
									</div>
								</div>
							</div>
							<?php do_action( 'bookingpress_myservices_staff_panel_external_content' ); ?>
							
						</div>
					</el-col>
				</el-row>
			</div>
		</div>
	</div>
</el-main>