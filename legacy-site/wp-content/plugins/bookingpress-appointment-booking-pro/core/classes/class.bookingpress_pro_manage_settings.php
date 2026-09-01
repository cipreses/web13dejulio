<?php
if ( ! class_exists( 'bookingpress_pro_manage_settings' ) ) {
	class bookingpress_pro_manage_settings Extends BookingPress_Core {
		function __construct() {
			add_filter( 'bookingpress_add_new_general_setting', array( $this, 'bookingpress_add_new_general_setting_func' ), 10, 1 );
			add_filter( 'bookingpress_modify_dynamic_setting_content', array( $this, 'bookingpress_modify_dynamic_setting_content_func' ), 10 );
			add_filter( 'bookingpress_add_setting_dynamic_data_fields', array( $this, 'bookingpress_add_setting_dynamic_data_fields_func' ), 10 );
		}

		function bookingpress_add_new_general_setting_func( $bookingpress_general_setting_arr ) {
			$bookingpress_general_setting_arr['appointment_setting'] = array(
				'name'     => 'Appointments',
				'icon'     => 'el-icon-date',
				'content'  => 'Use these settings to manage frontend bookings.',
				'position' => 5,
			);
			$bookingpress_general_setting_arr['integration_setting'] = array(
				'name'     => 'Integrations',
				'icon'     => 'el-icon-set-up',
				'content'  => 'Manage Google Calendar Integration, Outlook Calendar Integration, Zoom Integration and Web Hooks.',
				'position' => 6,
			);

			$bookingpress_pos = array();
			foreach ( $bookingpress_general_setting_arr as $bookingpress_general_setting_key => $bookingpress_general_setting_val ) {
				$bookingpress_pos[ $bookingpress_general_setting_key ] = $bookingpress_general_setting_val['position'];
			}

			array_multisort( $bookingpress_pos, SORT_ASC, $bookingpress_general_setting_arr );
			return $bookingpress_general_setting_arr;
		}


		function bookingpress_modify_dynamic_setting_content_func( $content ) {
			ob_start();

			// General settigns popup view file.
			if ( file_exists( BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/general_setting_view.php' ) ) {
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/general_setting_view.php';
			}

			// Company settings popup view file.
			if ( file_exists( BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/company_setting_view.php' ) ) {
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/company_setting_view.php';
			}

			// Notification setting view file.
			if ( file_exists( BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/notification_setting_view.php' ) ) {
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/notification_setting_view.php';
			}

			// Workhours and days off setting view file.
			if ( file_exists( BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/workhours_daysoff_setting_view.php' ) ) {
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/workhours_daysoff_setting_view.php';
			}

			// Labels setting view file.
			if ( file_exists( BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/labels_setting_view.php' ) ) {
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/labels_setting_view.php';
			}

			// Appointments setting view file.
			if ( file_exists( BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/appointment_setting_view.php' ) ) {
				require BOOKINGPRESS_PRO_VIEWS_DIR . '/general_setting_parts/appointment_setting_view.php';
			}

			$content = ob_get_clean();
			return $content;
		}


		function bookingpress_add_setting_dynamic_data_fields_func( $bookingpress_dynamic_setting_data_fields ) {
			$bookingpress_dynamic_setting_data_fields['appointment_setting_form'] = array(
				'allow_booking_above_maximum_capacity' => false,
				'allow_booking_below_minimum_capacity' => false,
			);
			return $bookingpress_dynamic_setting_data_fields;
		}
	}
}
global $bookingpress_pro_manage_settings;
$bookingpress_pro_manage_settings = new bookingpress_pro_manage_settings();
