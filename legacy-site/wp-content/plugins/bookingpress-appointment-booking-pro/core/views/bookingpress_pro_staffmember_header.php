<?php
global $bookingpress_slugs,$BookingPressPro, $bookingpress_pro_staff_members;
$request_module = ( ! empty( $_REQUEST['page'] ) && ( $_REQUEST['page'] != 'bookingpress' ) ) ? str_replace( 'bookingpress_', '', sanitize_text_field( $_REQUEST['page'] ) ) : 'dashboard';

$bookingpress_user_id        = get_current_user_id();
$bookingpress_staffmember_id = $bookingpress_pro_staff_members->bookingpress_get_staffmember_id_using_wp_user_id( $bookingpress_user_id );
$bookingpress_staffmember_avatar_url = BOOKINGPRESS_IMAGES_URL . '/default-avatar.jpg';
if(!empty($bookingpress_staffmember_id)) {
	$bookingpress_get_existing_avatar_details  = $bookingpress_pro_staff_members->get_bookingpress_staffmembersmeta( $bookingpress_staffmember_id, 'staffmember_avatar_details' );
	$bookingpress_get_existing_avatar_details  = ! empty( $bookingpress_get_existing_avatar_details ) ? maybe_unserialize( $bookingpress_get_existing_avatar_details ) : array(); 	
	if (! empty($bookingpress_get_existing_avatar_details[0]['url']) ) {
		$bookingpress_staffmember_avatar_url = esc_html($bookingpress_get_existing_avatar_details[0]['url']);
	} 
}	
if ( $BookingPressPro->bookingpress_check_user_role( 'bookingpress-staffmember' ) && ( ! $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation() || $bookingpress_pro_staff_members->bookingpress_current_login_staffmember_status() != 1 ) ) {

	wp_die( esc_html__( 'Sorry, you are not allowed to access this page', 'bookingpress-appointment-booking' ) );

} else {
	?>
<!-- 
    *** AFTER MERGE SHOULD BE TESTED IN LITE NEW & PRO OLD VERSION *** 
    ** Note for new header changes in mobile view **
    1. Task id(#85zt9z8wm) & url(https://app.clickup.com/t/85zt9z8wm)
    2. class "bpa-header-navbar--v2" is to support v1.0.64 new design.
    3. After few release developer need to merge new class css in orginal class "bpa-header-navbar".
    4. Remove unused CSS from orginal class while mergging new & old CSS. 
-->
<nav class="bpa-header-navbar bpa-header-navbar__staff-admin-mode bpa-header-navbar--v2">
	<div class="bpa-header-navbar-wrap">
		<?php
		if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress' ) ) {
			?>
		<div class="bpa-navbar-brand">
			<a href="<?php echo esc_url( admin_url() . 'admin.php?page=bookingpress' ); ?>" class="navbar-logo">
					<svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect width="64" height="64" rx="12"/>
						<path d="M50 18.9608V47.2745C50 49.3359 48.325 51 46.25 51H17.75C15.675 51 14 49.3359 14 47.2745V18.9608C14 16.8993 15.675 15.2353 17.75 15.2353H23V14.1176C23 13.7451 23.375 13 24.125 13C24.875 13 25.25 13.7451 25.25 14.1176V18.5882C25.25 18.9608 24.875 19.7059 24.125 19.7059C23.375 19.7059 23 18.9608 23 18.5882V17.4706H18.5C17.25 17.4706 16.25 18.4641 16.25 19.7059V46.5294C16.25 47.7712 17.25 48.7647 18.5 48.7647H45.5C46.75 48.7647 47.75 47.7712 47.75 46.5294V19.7059C47.75 18.4641 46.75 17.4706 45.5 17.4706H41C41 17.4706 41 18.0418 41 18.5882C41 18.9608 40.625 19.7059 39.875 19.7059C39.125 19.7059 38.75 18.9608 38.75 18.5882V17.4706H33.125C32.5 17.4706 32 16.9739 32 16.3529C32 15.732 32.5 15.2353 33.125 15.2353H38.75V14.1176C38.75 13.7451 39.125 13 39.875 13C40.625 13 41 13.7451 41 14.1176V15.2353H46.25C48.325 15.2353 50 16.8993 50 18.9608Z" fill="white"/>
						<path d="M37.2501 30.8823C37.2501 30.8823 38.0001 30.1372 38.0001 27.9019C38.0001 24.1765 35.7501 23.4314 32.7501 23.4314H26.0001V39.0784H30.5001V43.549H32.7501V39.0784C35.3501 39.0784 37.1751 39.0784 38.5251 37.4144C39.1751 36.6196 39.5001 35.6013 39.5001 34.5582C39.5001 34.0118 39.4251 33.4654 39.3001 33.1176C38.9751 32.2732 38.7501 31.6274 37.2501 30.8823ZM35.0001 36.8431C34.2501 36.8431 32.7501 36.8431 32.7501 36.8431C32.7501 36.8431 32.7501 36.098 32.7501 34.6078C32.7501 33.366 33.7501 32.3725 35.0001 32.3725C36.2001 32.3725 37.2501 33.3412 37.2501 34.6078C37.2501 35.9242 36.1501 36.8431 35.0001 36.8431ZM33.1751 30.6836C32.8001 30.8575 32.4251 31.081 32.0751 31.3294C31.2501 31.9503 30.5001 32.8444 30.5001 34.6078V36.8431H28.2501V25.6667H32.7501C34.5501 25.6667 35.7501 26.4118 35.7501 27.9019C35.7501 29.268 34.7251 29.9137 33.1751 30.6836Z" fill="white"/>
					</svg>
				</a>
			</div>
		<?php } ?>	
		<div class="bpa-navbar-nav" id="bpa-navbar-nav">
			<div class="bpa-menu-toggle" id="bpa-mobile-menu">
				<span class="bpa-mm-bar"></span>
				<span class="bpa-mm-bar"></span>
				<span class="bpa-mm-bar"></span>
			</div>
			<ul> 
					<?php
					if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_calendar' ) ) {
						?>
						
				<li class="bpa-nav-item <?php echo ( 'calendar' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>	
						<a href="<?php echo add_query_arg( 'page', esc_html($bookingpress_slugs->bookingpress_calendar), esc_url( admin_url() . 'admin.php?page=bookingpress' ) ); // phpcs:ignore ?>" class="bpa-nav-link">
							<div class="bpa-nav-link--icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M20 3h-1V2c0-.55-.45-1-1-1s-1 .45-1 1v1H7V2c0-.55-.45-1-1-1s-1 .45-1 1v1H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 18H5c-.55 0-1-.45-1-1V8h16v12c0 .55-.45 1-1 1z"/></svg>
							</div>
							<?php esc_html_e( 'Calendar', 'bookingpress-appointment-booking' ); ?>
						</a>
					</li>
						<?php
					}
					if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_appointments' ) ) {
						?>
				<li class="bpa-nav-item <?php echo ( 'appointments' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
						<a href="<?php echo add_query_arg( 'page', esc_html($bookingpress_slugs->bookingpress_appointments), esc_url( admin_url() . 'admin.php?page=bookingpress' ) ); // phpcs:ignore ?>" class="bpa-nav-link">
							<div class="bpa-nav-link--icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M16 12h-3c-.55 0-1 .45-1 1v3c0 .55.45 1 1 1h3c.55 0 1-.45 1-1v-3c0-.55-.45-1-1-1zm0-10v1H8V2c0-.55-.45-1-1-1s-1 .45-1 1v1H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm2 17H6c-.55 0-1-.45-1-1V8h14v10c0 .55-.45 1-1 1z"/></svg>
							</div>
							<?php esc_html_e( 'Appointments', 'bookingpress-appointment-booking' ); ?>
						</a>
					</li>
						<?php
					}
					if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_customers' ) ) {
						?>
						
				<li class="bpa-nav-item <?php echo ( 'customers' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>	
					<a href="<?php echo add_query_arg( 'page', esc_html($bookingpress_slugs->bookingpress_customers), esc_url( admin_url() . 'admin.php?page=bookingpress' ) ); // phpcs:ignore ?>" class="bpa-nav-link">
						<div class="bpa-nav-link--icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M16.5 12c1.38 0 2.49-1.12 2.49-2.5S17.88 7 16.5 7 14 8.12 14 9.5s1.12 2.5 2.5 2.5zM9 11c1.66 0 2.99-1.34 2.99-3S10.66 5 9 5 6 6.34 6 8s1.34 3 3 3zm7.5 3c-1.83 0-5.5.92-5.5 2.75V18c0 .55.45 1 1 1h9c.55 0 1-.45 1-1v-1.25c0-1.83-3.67-2.75-5.5-2.75zM9 13c-2.33 0-7 1.17-7 3.5V18c0 .55.45 1 1 1h6v-2.25c0-.85.33-2.34 2.37-3.47C10.5 13.1 9.66 13 9 13z"/></svg>
                        </div>
						<?php esc_html_e( 'Customers', 'bookingpress-appointment-booking' ); ?>
					</a>
				</li>
						<?php
					}
					if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_payments' ) ) {
						?>							
					<li class="bpa-nav-item <?php echo ( 'payments' == $request_module ) ? '__active' : ''; ?>">
						<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
						<a href="<?php echo add_query_arg( 'page', esc_html($bookingpress_slugs->bookingpress_payments), esc_url( admin_url() . 'admin.php?page=bookingpress' ) ); // phpcs:ignore ?>" class="bpa-nav-link">
							<div class="bpa-nav-link--icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09v.58c0 .73-.6 1.33-1.33 1.33h-.01c-.73 0-1.33-.6-1.33-1.33v-.6c-1.33-.28-2.51-1.01-3.01-2.24-.23-.55.2-1.16.8-1.16h.24c.37 0 .67.25.81.6.29.75 1.05 1.27 2.51 1.27 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21v-.6c0-.73.6-1.33 1.33-1.33h.01c.73 0 1.33.6 1.33 1.33v.62c1.38.34 2.25 1.2 2.63 2.26.2.55-.22 1.13-.81 1.13h-.26c-.37 0-.67-.26-.77-.62-.23-.76-.86-1.25-2.12-1.25-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.02 1.83-1.39 2.83-3.13 3.16z"/></svg>
							</div>
							<?php esc_html_e( 'Payments', 'bookingpress-appointment-booking' ); ?>
						</a>
					</li>
						<?php
					}
					if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_timesheet' ) ) {
						?>
					<li class="bpa-nav-item <?php echo ( 'timesheet' == $request_module ) ? '__active' : ''; ?>">
					<?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - URL is escaped properly ?>
						<a href="<?php echo add_query_arg( 'page', esc_html($bookingpress_slugs->bookingpress_timesheet), esc_url( admin_url() . 'admin.php?page=bookingpress' ) ); // phpcs:ignore ?>" class="bpa-nav-link">
							<div class="bpa-nav-link--icon">
								<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><g><rect fill="none" height="24" width="24"/><path d="M18,3h-3.18C14.4,1.84,13.3,1,12,1S9.6,1.84,9.18,3H6C4.9,3,4,3.9,4,5v15c0,1.1,0.9,2,2,2h6.11 c-0.59-0.57-1.07-1.25-1.42-2H6V5h2v1c0,1.1,0.9,2,2,2h4c1.1,0,2-0.9,2-2V5h2v5.08c0.71,0.1,1.38,0.31,2,0.6V5C20,3.9,19.1,3,18,3z M12,5c-0.55,0-1-0.45-1-1c0-0.55,0.45-1,1-1c0.55,0,1,0.45,1,1C13,4.55,12.55,5,12,5z M17,12c-2.76,0-5,2.24-5,5s2.24,5,5,5 c2.76,0,5-2.24,5-5S19.76,12,17,12z M18.29,19l-1.65-1.65c-0.09-0.09-0.15-0.22-0.15-0.35l0-2.49c0-0.28,0.22-0.5,0.5-0.5h0 c0.28,0,0.5,0.22,0.5,0.5l0,2.29l1.5,1.5c0.2,0.2,0.2,0.51,0,0.71v0C18.8,19.2,18.49,19.2,18.29,19z"/></g></svg>
							</div>
							<?php esc_html_e( 'TimeSheet', 'bookingpress-appointment-booking' ); ?>
						</a>
					</li>	
						<?php
					}
					?>
									
				</ul>
			</div>
			<div class="bpa-profile-dropdown">
				<el-dropdown class="bpa-nav-item-dropdown" trigger="click">
					<span class="el-dropdown-link bpa-nav-item-dropdown__link">
						<div class="bpa-pd__avatar">						
							<img src=<?php echo esc_url_raw( $bookingpress_staffmember_avatar_url ); ?> alt="img">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M8.12 9.29L12 13.17l3.88-3.88c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-4.59 4.59c-.39.39-1.02.39-1.41 0L6.7 10.7c-.39-.39-.39-1.02 0-1.41.39-.38 1.03-.39 1.42 0z"/></svg>
						</div>
					</span>
					<el-dropdown-menu slot="dropdown" class="bpa-ni-dropdown-menu" v-cloak>
						<?php
						if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_myprofile' ) ) {
							?>
						<el-dropdown-item class="bpa-ni-dropdown-menu--item <?php echo ( 'myprofile' == $request_module ) ? '__active' : ''; ?>">
							<a href="<?php echo add_query_arg( 'page', $bookingpress_slugs->bookingpress_myprofile, esc_url( admin_url() . 'admin.php?page=bookingpress' ) ); // phpcs:ignore ?>" class="bpa-dm--item-link">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="18px" viewBox="0 0 24 24" width="18px" fill="none"><g><rect fill="none" height="24" width="24"/><rect fill="none" height="24" width="24"/></g><g><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm0 14c-2.03 0-4.43-.82-6.14-2.88C7.55 15.8 9.68 15 12 15s4.45.8 6.14 2.12C16.43 19.18 14.03 20 12 20z"/></g></svg>
								</span>
								<?php esc_html_e( 'My Profile', 'bookingpress-appointment-booking' ); ?>
							</a>
						</el-dropdown-item>
							<?php
						}
						if ( $BookingPressPro->bookingpress_check_capability( 'bookingpress_myservices' ) ) {
							?>
						<el-dropdown-item class="bpa-ni-dropdown-menu--item bpa-dm__addon-item <?php echo ( 'myservices' == $request_module ) ? '__active' : ''; ?>">
							<a href="<?php echo add_query_arg( 'page', $bookingpress_slugs->bookingpress_myservices, esc_url( admin_url() . 'admin.php?page=bookingpress' ) ); // phpcs:ignore ?>" class="bpa-dm--item-link">
								<span>
									<svg width="18px" height="18px" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M14 9.5h3c.55 0 1-.45 1-1s-.45-1-1-1h-3c-.55 0-1 .45-1 1s.45 1 1 1zm0 7h3c.55 0 1-.45 1-1s-.45-1-1-1h-3c-.55 0-1 .45-1 1s.45 1 1 1zm5 4.5H5c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2h14c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2zM7 11h3c.55 0 1-.45 1-1V7c0-.55-.45-1-1-1H7c-.55 0-1 .45-1 1v3c0 .55.45 1 1 1zm0-4h3v3H7V7zm0 11h3c.55 0 1-.45 1-1v-3c0-.55-.45-1-1-1H7c-.55 0-1 .45-1 1v3c0 .55.45 1 1 1zm0-4h3v3H7v-3z"/></svg>
								</span>
								<?php esc_html_e( 'My Services', 'bookingpress-appointment-booking' ); ?>
							</a>
						</el-dropdown-item>
						<?php } ?>
						<el-dropdown-item class="bpa-ni-dropdown-menu--item bpa-dm__addon-item">
							<a href="<?php echo add_query_arg('staffmember_view','customize_view',get_permalink()); //phpcs:ignore ?>" class="bpa-dm--item-link">
								<span>
									<svg width="18px" height="18px" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
								</span>
								<?php esc_html_e( 'Compact Mode', 'bookingpress-appointment-booking' ); ?>
							</a>
						</el-dropdown-item>
						<el-dropdown-item class="bpa-ni-dropdown-menu--item bpa-dm__addon-item __bpa-is-logout">
							<a href="<?php echo add_query_arg( 'bookingpress_action', 'bookingpress_logout', esc_url( admin_url() . 'admin.php?page=bookingpress' ) ); // phpcs:ignore ?>" class="bpa-dm--item-link">
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" height="18px" viewBox="0 0 24 24" width="18px" fill="none"><g><path d="M0,0h24v24H0V0z" fill="none"/></g><g><g><path d="M5,5h6c0.55,0,1-0.45,1-1v0c0-0.55-0.45-1-1-1H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h6c0.55,0,1-0.45,1-1v0 c0-0.55-0.45-1-1-1H5V5z"/><path d="M20.65,11.65l-2.79-2.79C17.54,8.54,17,8.76,17,9.21V11h-7c-0.55,0-1,0.45-1,1v0c0,0.55,0.45,1,1,1h7v1.79 c0,0.45,0.54,0.67,0.85,0.35l2.79-2.79C20.84,12.16,20.84,11.84,20.65,11.65z"/></g></g></svg>	
								</span>
								<?php esc_html_e( 'Logout', 'bookingpress-appointment-booking' ); ?>
							</a>
						</el-dropdown-item>
					</el-dropdown-menu>
				</el-dropdown>
			</div>
		</div>
	</nav>
<?php } ?>
<div class="mob-nav-overlay" id="mob-nav-overlay"></div>