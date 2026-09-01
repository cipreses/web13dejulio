<?php
	global $bookingpress_slugs, $bookingpress_pro_staff_members, $bookingpress_global_options;
	$setting_module                = ! empty( $_REQUEST['setting_page'] ) ? sanitize_text_field( $_REQUEST['setting_page'] ) : 'general';
	$bookingpress_setting_page_url = add_query_arg( 'page', $bookingpress_slugs->bookingpress_settings, esc_url( admin_url() . 'admin.php?page=bookingpress' ) );

	$general_settings_url      = $bookingpress_setting_page_url;
	$company_settings_url      = add_query_arg( 'setting_page', 'company', $bookingpress_setting_page_url );
	$notification_settings_url = add_query_arg( 'setting_page', 'notifications', $bookingpress_setting_page_url );
	$workhours_settings_url    = add_query_arg( 'setting_page', 'workhours', $bookingpress_setting_page_url );
	$daysoff_settings_url      = add_query_arg( 'setting_page', 'daysoff', $bookingpress_setting_page_url );
	$special_day_settings_url  = add_query_arg( 'setting_page', 'specialday', $bookingpress_setting_page_url );
	$payment_settings_url      = add_query_arg( 'setting_page', 'payment', $bookingpress_setting_page_url );
	$messages_settings_url     = add_query_arg( 'setting_page', 'messages', $bookingpress_setting_page_url );
	$debug_logs_settings_url   = add_query_arg( 'setting_page', 'debug_logs', $bookingpress_setting_page_url );
	$integrations_url   	   = add_query_arg( 'setting_page', 'integrations', $bookingpress_setting_page_url );

	$license_settings_url   = add_query_arg( 'setting_page', 'license', $bookingpress_setting_page_url );

	$is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
	$bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
	$bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) : esc_html_e('Staff Member', 'bookingpress-appointment-booking');
	$bookingpress_plural_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) ? stripslashes_deep($bookingpress_global_options_arr['bookingpress_staffmember_plural_name']) : esc_html_e('Staff Members', 'bookingpress-appointment-booking');
if ( $is_staffmember_module_activated ) {
	$staffmember_settings_url = add_query_arg( 'setting_page', 'staffmembers_settings', $bookingpress_setting_page_url );
}

?>
<el-main class="bpa-main-listing-card-container bpa-general-settings--main-container bpa-default-card bpa--is-page-scrollable-tablet" id="all-page-main-container" >
	<div class="bpa-back-loader-container" v-if="is_display_loader == '1'">
		<div class="bpa-back-loader"></div>
	</div>
	<div class="bpa-back-loader-container" id="bpa-page-loading-loader">
		<div class="bpa-back-loader"></div>
	</div>
	<div id="bpa-main-container">
		<el-tabs ref="bookingpress_setting_tabs" type="card" v-model="selected_tab_name" tab-position="left" class="bpa-tabs bpa-tabs--vertical__left-side" @tab-click="settings_tab_select($event)">
			<?php
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/general_setting_tab.php';
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/company_setting_tab.php';
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/notification_setting_tab.php';
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/customers_setting_tab.php';
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/workhours_setting_tab.php';
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/daysoff_setting_tab.php';
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/special_day_setting_tab.php';
				if( current_user_can( 'bookingpress_payment_settings') ){
					require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/payment_setting_tab.php';
				}
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/messages_setting_tab.php';
				if( current_user_can( 'bookingpress_license') ){
					require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/license_settings_tab.php';
				}
				if ( $is_staffmember_module_activated ) {
					require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/staffmembers_settings_tab.php';
				}
				$bookingpress_integration_addon_list = array();
				$bookingpress_integration_addon_list = apply_filters( 'bookingpress_available_integration_addon_list', $bookingpress_integration_addon_list );
				if(!empty($bookingpress_integration_addon_list)) {
					require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/integrations_setting_tab.php';	
				}

				$bookingpress_optins_addon_list = array();
				$bookingpress_optins_addon_list = apply_filters( 'bookingpress_available_optins_addon_list', $bookingpress_optins_addon_list );
				if(!empty($bookingpress_optins_addon_list)) {
					require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/optins_setting_tab.php';	
				}
				$bookingpress_file_url = array();
				$bookingpress_file_url = apply_filters( 'bookingpress_general_settings_add_tab_filter', $bookingpress_file_url );
				if ( ! empty( $bookingpress_file_url ) && is_array( $bookingpress_file_url ) ) {
					foreach ( $bookingpress_file_url as $bookingpress_file_key => $bookingpress_file_url_val ) {
						if ( ! empty( $bookingpress_file_url_val ) ) {
							require $bookingpress_file_url_val;
						}
					}
				}

				require BOOKINGPRESS_PRO_VIEWS_DIR . '/settings/debug_log_settings.php';
			?>
		</el-tabs>
	</div>
</el-main>
<?php do_action('bookingpress_setting_view_data_after'); ?>