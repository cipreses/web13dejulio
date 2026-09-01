<?php
if ( is_ssl() ) {
	define( 'BOOKINGPRESS_PRO_URL', str_replace( 'http://', 'https://', WP_PLUGIN_URL . '/' . BOOKINGPRESS_DIR_PRO_NAME_PRO ) );
	define( 'BOOKINGPRESS_PRO_HOME_URL', home_url( '', 'https' ) );
} else {
	define( 'BOOKINGPRESS_PRO_URL', WP_PLUGIN_URL . '/' . BOOKINGPRESS_DIR_PRO_NAME_PRO );
	define( 'BOOKINGPRESS_PRO_HOME_URL', home_url() );
}

define( 'BOOKINGPRESS_PRO_URL_1', WP_PLUGIN_URL . '/' . BOOKINGPRESS_DIR_PRO_NAME_PRO );

define( 'BOOKINGPRESS_PRO_URL_2', WP_PLUGIN_URL . '/' . BOOKINGPRESS_DIR_PRO_NAME_PRO );

define( 'BOOKINGPRESS_PRO_CORE_DIR', BOOKINGPRESS_DIR_PRO . '/core' );

define( 'BOOKINGPRESS_PRO_CLASSES_DIR', BOOKINGPRESS_DIR_PRO . '/core/classes' );
define( 'BOOKINGPRESS_PRO_CLASSES_URL', BOOKINGPRESS_PRO_URL . '/core/classes' );

define( 'BOOKINGPRESS_PRO_WIDGET_DIR', BOOKINGPRESS_DIR_PRO . '/core/widgets' );
define( 'BOOKINGPRESS_PRO_WIDGET_URL', BOOKINGPRESS_PRO_URL . '/core/widgets' );

define( 'BOOKINGPRESS_PRO_IMAGES_DIR', BOOKINGPRESS_DIR_PRO . '/images' );
define( 'BOOKINGPRESS_PRO_IMAGES_URL', BOOKINGPRESS_PRO_URL . '/images' );

define( 'BOOKINGPRESS_PRO_LIBRARY_DIR', BOOKINGPRESS_DIR_PRO . '/lib' );
define( 'BOOKINGPRESS_PRO_LIBRARY_URL', BOOKINGPRESS_PRO_URL . '/lib' );

define( 'BOOKINGPRESS_PRO_INC_DIR', BOOKINGPRESS_DIR_PRO . '/inc' );

define( 'BOOKINGPRESS_PRO_VIEWS_DIR', BOOKINGPRESS_DIR_PRO . '/core/views' );
define( 'BOOKINGPRESS_PRO_VIEWS_URL', BOOKINGPRESS_PRO_URL . '/core/views' );


if ( ! defined( 'FS_METHOD' ) ) {
	@define( 'FS_METHOD', 'direct' );
}

$bookingpress_wpupload_dir = wp_upload_dir();
$bookingpress_upload_dir   = $bookingpress_wpupload_dir['basedir'] . '/bookingpress';
$bookingpress_upload_url   = $bookingpress_wpupload_dir['baseurl'] . '/bookingpress';
if ( ! is_dir( $bookingpress_upload_dir ) ) {
	wp_mkdir_p( $bookingpress_upload_dir );
}
define( 'BOOKINGPRESS_PRO_UPLOAD_DIR', $bookingpress_upload_dir );
define( 'BOOKINGPRESS_PRO_UPLOAD_URL', $bookingpress_upload_url );

$bookingpress_upload_css_dir = $bookingpress_wpupload_dir['basedir'] . '/bookingpress/css';
$bookingpress_upload_css_url = $bookingpress_wpupload_dir['baseurl'] . '/bookingpress/css';
if ( ! is_dir( $bookingpress_upload_css_dir ) ) {
	wp_mkdir_p( $bookingpress_upload_css_dir );
}
define( 'BOOKINGPRESS_PRO_UPLOAD_CSS_DIR', $bookingpress_upload_css_dir );
define( 'BOOKINGPRESS_PRO_UPLOAD_CSS_URL', $bookingpress_upload_css_url );

$bookingpress_upload_form_file_dir = $bookingpress_wpupload_dir['basedir'] . '/bookingpress/bookingpress_form';
$bookingpress_upload_form_file_url = $bookingpress_wpupload_dir['baseurl'] . '/bookingpress/bookingpress_form';
if( !is_dir($bookingpress_upload_form_file_dir ) ){
	wp_mkdir_p( $bookingpress_upload_form_file_dir );
}
define( 'BOOKINGPRESS_PRO_FORM_FILE_DIR', $bookingpress_upload_form_file_dir );
define( 'BOOKINGPRESS_PRO_FORM_FILE_URL', $bookingpress_upload_form_file_url );

global $bookingpress_user_status, $bookingpress_user_type;
$bookingpress_user_status = array(
	'1' => esc_html__( 'Active', 'bookingpress-appointment-booking' ),
	'2' => esc_html__( 'Inactive', 'bookingpress-appointment-booking' ),
	'3' => esc_html__( 'Pending', 'bookingpress-appointment-booking' ),
	'4' => esc_html__( 'Terminated', 'bookingpress-appointment-booking' ),
);

$bookingpress_user_type = array(
	'1' => esc_html__( 'Employee', 'bookingpress-appointment-booking' ),
	'2' => esc_html__( 'Customer', 'bookingpress-appointment-booking' ),
);

/* Defining BookingPress Plugin Version */
global $bookingpress_pro_version;
$bookingpress_pro_version = '3.7';
define( 'BOOKINGPRESS_PRO_VERSION', $bookingpress_pro_version );

global $bookingpress_ajaxurl;
$bookingpress_ajaxurl = admin_url( 'admin-ajax.php' );


define( 'BOOKINGPRESS_ITEM_NAME', 'Standard' );
define( 'BOOKINGPRESS_STORE_URL', 'https://www.bookingpressplugin.com/' );
define( 'BOOKINGPRESS_ITEM_ID', 4110 );

if ( ! class_exists( 'bookingpress_pro_updater' ) ) {
	require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_plugin_updater.php';
}

/**
 * Plugin Main Class
 */

if( class_exists( 'BookingPress_Core')  ){

	/* if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro.php';
	} */

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_services.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_services.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_staff_members.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_staff_members.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_payment.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_payment.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_appointment.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_appointment.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_calendar.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_calendar.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_addons.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_addons.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_customers.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_customers.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_manage_notifications.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_manage_notifications.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_global_options.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_global_options.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_email_notifications.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_email_notifications.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_coupon_management.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_coupon_management.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/frontend/class.bookingpress_appointment_bookings.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/frontend/class.bookingpress_appointment_bookings.php';
	}
	
	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/frontend/class.bookingpress_complete_payment.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/frontend/class.bookingpress_complete_payment.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_payment_gateways.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_payment_gateways.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/payment_gateways/class.bookingpress_paypal.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/payment_gateways/class.bookingpress_paypal.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_settings.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_settings.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_dashboard.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_dashboard.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_subscriptions.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_subscriptions.php';
	}
	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_customize.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_customize.php';
	}
	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_deposit_payment.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_deposit_payment.php';
	}
	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_bring_anyone.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_bring_anyone.php';
	}
	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_service_extra.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_service_extra.php';
	}
	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_reports.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_reports.php';
	}
	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_timesheet.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_timesheet.php';
	}
	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_myservices.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_myservices.php';
	}
	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_myprofile.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_myprofile.php';
	}
	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_crons.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_crons.php';
	}
	if( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_ics_generator.php') ){
		require_once BOOKINGPRESS_PRO_CLASSES_DIR  . '/class.bookingpress_ics_generator.php';
	}

	if( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_holiday_repeater.php') ){
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_holiday_repeater.php';
	}

	if ( file_exists( BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_import_export.php' ) ) {
		require_once BOOKINGPRESS_PRO_CLASSES_DIR . '/class.bookingpress_pro_import_export.php';
	}

}

add_action( 'plugins_loaded', 'bookingpress_pro_load_textdomain' );
/**
 * Loading plugin text domain
 */
function bookingpress_pro_load_textdomain() {
	load_plugin_textdomain( 'bookingpress-appointment-booking', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
define( 'BOOKINGPRESS_PRO_TXTDOMAIN', 'bookingpress-appointment-booking' );

function bookingpress_pro_plugin_updater() {

	$bookingpress_plugin_slug = 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php';
	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'bkp_license_key' ) );
	$package = trim( get_option( 'bkp_license_package' ) );

	// setup the updater
	$edd_updater = new bookingpress_pro_updater(
		BOOKINGPRESS_STORE_URL,
		$bookingpress_plugin_slug,
		array(
			'version' => BOOKINGPRESS_PRO_VERSION,  // current version number
			'license' => $license_key,             // license key (used get_option above to retrieve from DB)
			'item_id' => $package,       // ID of the product
			'author'  => 'Repute Infosystems', // author of this plugin
			'beta'    => false,
		)
	);

}
add_action( 'init', 'bookingpress_pro_plugin_updater' );
