<el-main class="bpa-main-listing-card-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container">	
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>	
	<div id="bpa-main-container">		
		<el-container class="bpa-addons-container">
			<div class="bpa-addon-sub-list-wrapper">
				<el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
						<h1 class="bpa-page-heading"><?php esc_html_e( 'Additional Modules', 'bookingpress-appointment-booking' ); ?></h1>
					</el-col>
				</el-row>
				<el-row :gutter="30" class="bpa-addons-items-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="6" v-for="addons in addons_list" class="bpa-addons-items-col">
						<div class="bpa-addon-item">
							<span class="bpa-ai-icon" :class="addons.icon_slug"></span>							
							<div class="bpa-ai-name">
								<h3>{{ addons.name }}</h3>
							</div>
							<div class="bpa-ai-desc">
								<p>{{ addons.description }}</p>
							</div>
							<div class="bpa-ai-btns">
								<el-row type="flex">
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<el-button class="bpa-btn bpa-btn--primary bpa-btn--full-width" @click="bookingpress_activate_addon(addons.key)" :class="typeof is_display_activate_loader !== 'undefined' && is_display_activate_loader  == addons.key ? 'bpa-btn--is-loader' : ''"  :disabled="is_disabled_activate == addons.key ? true : false" v-if="addons.is_active == ''" >
											<span class="bpa-btn__label"><?php esc_html_e( 'Activate', 'bookingpress-appointment-booking' ); ?></span>
											<div class="bpa-btn--loader__circles">
												<div></div>
												<div></div>
												<div></div>
											</div>
										</el-button> 
									</el-col>
								</el-row>
								<el-row type="flex" :gutter="16">
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<el-button class="bpa-btn bpa-btn__filled bpa-btn--full-width" @click="bookingpress_deactivate_addon(addons.key)" :class="typeof is_display_deactivate_loader !== 'undefined' && is_display_deactivate_loader  == addons.key ? 'bpa-btn--is-loader' : ''"  :disabled="is_disabled_deactivate == addons.key ? true : false" v-if="addons.is_active == 'true'" >
											<span class="bpa-btn__label"><?php esc_html_e( 'Deactivate', 'bookingpress-appointment-booking' ); ?></span>
											<div class="bpa-btn--loader__circles">
												<div></div>
												<div></div>
												<div></div>
											</div>
										</el-button> 
									</el-col>
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12"v-if="addons.addon_is_configurable == 1">
										<el-button class="bpa-btn bpa-btn--full-width" @click="bookingpress_configure_redirection(addons.configure_url)" v-if="addons.is_active == 'true'">
											<?php esc_html_e( 'Configure', 'bookingpress-appointment-booking' ); ?>
										</el-button>
									</el-col>
								</el-row>
							</div>
							<div class="bpa-ai-doc-link">
								<el-link :href="addons.documentation_url" target="_blank">
									<i class="material-icons-round">description</i><?php esc_html_e( 'Read More', 'bookingpress-appointment-booking' ); ?>
								</el-link>
							</div>
							<div class="bpa-ai-ribbon" v-if="addons.is_active == 'true'">
								<span>Active</span>
							</div>
						</div>
					</el-col>
				</el-row>
			</div>
			<div class="bpa-addon-sub-list-wrapper">
				<el-row type="flex" class="bpa-mlc-head-wrap">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="bpa-mlc-left-heading">
						<h1 class="bpa-page-heading"><?php esc_html_e( 'More Add-ons', 'bookingpress-appointment-booking' ); ?></h1>
					</el-col>
				</el-row>
				<el-row :gutter="30" class="bpa-addons-items-row">
					<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="6" v-for="addons in more_addons" class="bpa-addons-items-col">
						<div class="bpa-addon-item" :id="addons.addon_key+'_activate_addon'">
							<span class="bpa-ai-icon" :class="addons.addon_icon_slug"></span>
							<div class="bpa-ai-name">
								<h3>{{ addons.addon_name }}</h3>
							</div>
							<div class="bpa-ai-desc">
								<p>{{ addons.addon_description }}</p>							
							</div>
							<div class="bpa-ai-btns">
								<el-row type="flex">
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">										
										<el-button class="bpa-btn bpa-btn--primary bpa-btn--full-width" @click="bookingpress_open_addon_download_url(addons.addon_download_url)" v-if="addons.addon_isactive == '2'">
											<span class="bpa-btn__label"><?php esc_html_e( 'Get', 'bookingpress-appointment-booking' ); ?></span>
										</el-button>
									</el-col>
								</el-row>
								<el-row type="flex">
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<el-button class="bpa-btn bpa-btn--primary bpa-btn--full-width" @click="bookingpress_activate_plugin(addons.addon_installer, addons.addon_key)" :class="typeof is_display_activate_loader !== 'undefined' && is_display_activate_loader == addons.addon_installer ? 'bpa-btn--is-loader' : ''"  :disabled="is_disabled_activate == addons.addon_installer ? true : false" v-if="addons.addon_isactive == '0'" >
											<span class="bpa-btn__label"><?php esc_html_e( 'Activate', 'bookingpress-appointment-booking' ); ?></span>
											<div class="bpa-btn--loader__circles">
												<div></div>
												<div></div>
												<div></div>
											</div>
										</el-button> 
									</el-col>
								</el-row>
								<el-row type="flex" :gutter="16">
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
										<el-button class="bpa-btn bpa-btn__filled bpa-btn--full-width" @click="bookingpress_deactivate_plugin(addons.addon_installer)" :class="typeof is_display_deactivate_loader !== 'undefined' && is_display_deactivate_loader == addons.addon_installer ? 'bpa-btn--is-loader' : ''"  :disabled="is_disabled_deactivate == addons.addon_installer ? true : false" v-if="addons.addon_isactive == '1'" >
											<span class="bpa-btn__label"><?php esc_html_e( 'Deactivate', 'bookingpress-appointment-booking' ); ?></span>
											<div class="bpa-btn--loader__circles">
												<div></div>
												<div></div>
												<div></div>
											</div>
										</el-button> 
									</el-col>								
									<el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" v-if="addons.addon_isactive == '1' && addons.addon_is_configurable == '1'">
										<el-button class="bpa-btn bpa-btn--full-width" @click="bookingpress_configure_redirection(addons.addon_configure_url)" v-if="addons.addon_isactive == '1' && addons.addon_is_configurable == '1'">
											<?php esc_html_e( 'Configure', 'bookingpress-appointment-booking' ); ?>
										</el-button>
									</el-col>
								</el-row>
							</div>
							<div class="bpa-ai-doc-link">
								<el-link :href="addons.addon_documentation" target="_blank">
									<i class="material-icons-round">description</i><?php esc_html_e( 'Read More', 'bookingpress-appointment-booking' ); ?>
								</el-link>
							</div>
							<div class="bpa-ai-ribbon" v-if="addons.addon_isactive == '1'">
								<span><?php esc_html_e( 'Active', 'bookingpress-appointment-booking' ); ?></span>
							</div>
						</div>
					</el-col>
				</el-row>
			</div>
		</el-container>
	</div>
</el-main>
