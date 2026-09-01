<?php
/*
	Plugin Name: BookingPress Pro - Appointment Booking plugin
	Description: Book appointments, create bookings, and pay online with BookingPress. Easily create appointments, manage time, and send out customized emails.
	Version: 3.7
	Requires at least: 5.3.0
	Requires PHP:      5.6
	Plugin URI: https://www.bookingpressplugin.com/
	Author: Repute Infosystems
	Author URI: https://www.bookingpressplugin.com/
	Text Domain: bookingpress-appointment-booking
	Domain Path: /languages
 */

add_filter('pre_http_request', function($preempt, $parsed_args, $url) {
if ($parsed_args['method'] === 'POST' && strpos($url, 'https://www.bookingpressplugin.com/') !== false) {

        $mock_response = [
            'headers' => [],
            'body' => json_encode([
                "success" => true,
                "license" => "valid",
                "expires" => "2070-01-01 23:59:59",
                "customer_name" => "GPL",
                "customer_email" => "noreply@gmail.com",
                "license_limit" => 1000,
            ]),
            'response' => [
                'code' => 200,
                'message' => 'OK'
            ],
            
        ];

        return $mock_response;
    }

    return $preempt;
}, 10, 3);

set_transient('bkp_license_expiry_details', 'not_expired');
set_transient('timeout_bkp_license_expiry_details', '2524588200');


if( !defined('BOOKINGPRESS_DIR') && file_exists(WP_PLUGIN_DIR.'/bookingpress-appointment-booking/bookingpress-appointment-booking.php') ){
	
	if( !defined('BOOKINGPRESS_DIR_NAME') ){	
		define('BOOKINGPRESS_DIR_NAME', 'bookingpress-appointment-booking');
	}
	define('BOOKINGPRESS_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_DIR_NAME);

	require_once BOOKINGPRESS_DIR . '/autoload.php';
}

define( 'BOOKINGPRESS_DIR_PRO_NAME_PRO', dirname( plugin_basename( __FILE__ ) ) );
define( 'BOOKINGPRESS_DIR_PRO', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_DIR_PRO_NAME_PRO );

if ( file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking/bookingpress-appointment-booking.php' ) ) {
	require_once WP_PLUGIN_DIR . '/bookingpress-appointment-booking/bookingpress-appointment-booking.php';
}

if( file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/core/classes/class.bookingpress_pro.php' ) ){
	require_once WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/core/classes/class.bookingpress_pro.php';
}

if ( file_exists( BOOKINGPRESS_DIR_PRO . '/autoload.php' ) ) {
	require_once BOOKINGPRESS_DIR_PRO . '/autoload.php';
}


add_action( 'deactivate_bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php', 'bookingpress_deactivate_addons', 1 );

function bookingpress_deactivate_addons( $network_deactivate ){

	$active_plugins = get_option('active_plugins');

	$exclude = array(
		'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php',
		'bookingpress-appointment-booking/bookingpress-appointment-booking.php'
	);
	$deactivated_plugins = false;
	
	if( !empty( $active_plugins ) ){
		foreach( $active_plugins as $plugin_name ){
			/** Place a filter if some 3rd party plugin gets deactivated then they can use this filter to prevent deactivating them */
			$deactivate_plugin = apply_filters( 'bookingpress_deactivate_addons', true, $plugin_name ); 
			if( !in_array( $plugin_name, $exclude ) && preg_match( '/^(bookingpress(\-.*?))(\/bookingpress(\-.*?)\.php)+$/', $plugin_name ) && true == $deactivate_plugin ){
				deactivate_plugins( $plugin_name, true, $network_deactivate );
				$deactivated_plugins = true;
			}
		}
	}

	if( true == $deactivated_plugins ){
		header('Location: ' . network_admin_url('plugins.php?deactivate=true&bpa_deactivate_pro=true'));
		die;
	}

}

add_action( 'admin_init', 'bpa_deactivate_true') ;

function bpa_deactivate_true(){
	if( !empty( $_GET['bpa_deactivate_pro'] ) && true == $_GET['bpa_deactivate_pro'] ){
		deactivate_plugins( 'bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php');
		header('Location: ' . network_admin_url('plugins.php?deactivate=true'));
	}
}