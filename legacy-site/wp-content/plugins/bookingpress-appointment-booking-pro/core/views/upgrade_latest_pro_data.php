<?php

global $BookingPress, $wpdb, $bookingpress_pro_version,$BookingPressPro;
$bookingpress_pro_old_version = get_option('bookingpress_pro_version', true);

if (version_compare($bookingpress_pro_old_version, '1.0.2', '<') ) {    
    $tbl_bookingpress_cron_email_notifications_logs = $wpdb->prefix . 'bookingpress_cron_email_notification_logs';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_cron_email_notifications_logs} ADD `bookingpress_notification_type` varchar(20) DEFAULT 'email' AFTER bookingpress_email_is_sent"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_cron_email_notifications_logs is table name defined globally. False Positive alarm
}

if (version_compare($bookingpress_pro_old_version, '1.0.5', '<') ) {
    $tbl_bookingpress_form_fields = $wpdb->prefix . 'bookingpress_form_fields';
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $args     = array(
        'bookingpress_field_required' => 1,
        );
    $wpdb->update($tbl_bookingpress_form_fields, $args, array( 'bookingpress_field_type' => 'email' ));

    $bookingpress_db_fields = array(
        'bookingpress_setting_name'  => 'slot_left_text',
        'bookingpress_setting_value' => __('Slots left','bookingpress-appointment-booking'),
        'bookingpress_setting_type'  => 'booking_form',
    );        
    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);
}

if (version_compare($bookingpress_pro_old_version, '1.1', '<') ) {
    $BookingPress->bookingpress_update_settings('price_settings_and_display', 'payment_setting', 'exclude_taxes');
    $BookingPress->bookingpress_update_settings('display_tax_order_summary', 'payment_setting', 'true');

    $bookingpress_included_tax_label = "(".esc_html__('Inc. GST', 'bookingpress-appointment-booking').")";
    $BookingPress->bookingpress_update_settings('included_tax_label', 'payment_setting', $bookingpress_included_tax_label);

    //add new column for price settings
    $tbl_bookingpress_entries = $wpdb->prefix."bookingpress_entries";
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix."bookingpress_appointment_bookings";
    $tbl_bookingpress_payment_logs = $wpdb->prefix."bookingpress_payment_transactions";
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD `bookingpress_price_display_setting` varchar(20) DEFAULT 'exclude_taxes' AFTER bookingpress_tax_amount"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD `bookingpress_price_display_setting` varchar(20) DEFAULT 'exclude_taxes' AFTER bookingpress_tax_amount"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD `bookingpress_price_display_setting` varchar(20) DEFAULT 'exclude_taxes' AFTER bookingpress_tax_amount"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm



    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD `bookingpress_display_tax_order_summary` smallint(6) DEFAULT 1 AFTER bookingpress_price_display_setting"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD `bookingpress_display_tax_order_summary` smallint(6) DEFAULT 1 AFTER bookingpress_price_display_setting"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD `bookingpress_display_tax_order_summary` smallint(6) DEFAULT 1 AFTER bookingpress_price_display_setting"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm



    $wpdb->query("ALTER TABLE {$tbl_bookingpress_entries} ADD `bookingpress_included_tax_label` varchar(255) DEFAULT NULL AFTER bookingpress_display_tax_order_summary"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD `bookingpress_included_tax_label` varchar(255) DEFAULT NULL AFTER bookingpress_display_tax_order_summary"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_payment_logs} ADD `bookingpress_included_tax_label` varchar(255) DEFAULT NULL AFTER bookingpress_display_tax_order_summary"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_payment_logs is table name defined globally. False Positive alarm

    $bookingpress_db_fields = array(
        'bookingpress_setting_name'  => 'cancel_button_title',
        'bookingpress_setting_value' => __('Cancel','bookingpress-appointment-booking'),
        'bookingpress_setting_type'  => 'booking_form',
    );
    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);
    $bookingpress_db_fields = array(
        'bookingpress_setting_name'  => 'continue_button_title',
        'bookingpress_setting_value' => __('Continue','bookingpress-appointment-booking'),
        'bookingpress_setting_type'  => 'booking_form',
    );
    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);
    $BookingPress->bookingpress_update_settings('no_staffmember_selected_for_the_booking', 'message_setting', 'Please select staff member');    
}

if (version_compare($bookingpress_pro_old_version, '1.2', '<') ) {
    global $wpdb;
    $bookingpress_booking_form_customize_setting = array(
        'subtotal_text'	=> __('Subtotal', 'bookingpress-appointment-booking'),
        'deposit_title'	=> __('Deposit', 'bookingpress-appointment-booking'),
        'full_payment_title' => __('Full Payment','bookingpress-appointment-booking'),
        'number_of_guest_title' => __( 'Number of guests', 'bookingpress-appointment-booking' ),
        'number_of_person_title' => __( 'Persons', 'bookingpress-appointment-booking' ),
        'any_staff_title' => __('Any Staff','bookingpress-appointment-booking'),
    );
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    foreach($bookingpress_booking_form_customize_setting as $key => $val){
        $bookingpress_bd_data = array(
            'bookingpress_setting_name' => $key,
            'bookingpress_setting_value' => $val,
            'bookingpress_setting_type' => 'booking_form',
        );
        $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_bd_data);        
    }
    $bookingpress_my_booking_customize_setting = array(
        'old_password_error_msg' => esc_html__('Please enter old password', 'bookingpress-appointment-booking'),
        'new_password_error_msg' => esc_html__('Please enter new password', 'bookingpress-appointment-booking'),
        'confirm_password_error_msg' => esc_html__('Please enter confirm password', 'bookingpress-appointment-booking'),
    );
    foreach($bookingpress_my_booking_customize_setting as $key => $val){
        $bookingpress_bd_data = array(
            'bookingpress_setting_name' => $key,
            'bookingpress_setting_value' => $val,
            'bookingpress_setting_type' => 'booking_my_booking',
        );
        $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_bd_data);        
    }
    $BookingPress->bookingpress_update_settings('coupon_code_not_valid', 'message_setting', 'Coupon code is not valid');
    $BookingPress->bookingpress_update_settings('coupon_code_not_allowed', 'message_setting', 'Coupon code not allowed');    
    $BookingPress->bookingpress_update_settings('coupon_code_expired', 'message_setting', 'Coupon code expired');    
    $BookingPress->bookingpress_update_settings('coupon_code_not_valid_for_service', 'message_setting', 'Coupon code is not valid for selected service');    
    $BookingPress->bookingpress_update_settings('coupon_code_no_longer_available', 'message_setting', 'Coupon code no longer available');    
    $BookingPress->bookingpress_update_settings('coupon_code_does_not_exist', 'message_setting', 'Coupon code does not exist');
    $BookingPress->bookingpress_update_settings('bookingpress_card_details_error_msg', 'message_setting', 'Please fill all fields value of card details');

    //Update company icon settings data
    $BookingPress->bookingpress_update_settings('company_icon_img', 'company_setting', '');
    $BookingPress->bookingpress_update_settings('company_icon_url', 'company_setting', '');
    $BookingPress->bookingpress_update_settings('company_icon_list', 'company_setting', '');
}


if (version_compare($bookingpress_pro_old_version, '1.2.1', '<') ) {
    global $wpdb;
    $tbl_bookingpress_entries = $wpdb->prefix."bookingpress_entries";
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix."bookingpress_appointment_bookings";

    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD `bookingpress_dst_timezone` TINYINT NOT NULL DEFAULT '0' AFTER `bookingpress_customer_timezone`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_dst_timezone TINYINT NOT NULL DEFAULT 0 AFTER `bookingpress_appointment_timezone`" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
}

if (version_compare($bookingpress_pro_old_version, '1.3', '<') ) {
    global $wpdb, $BookingPress, $BookingPressPro;

    $tbl_bookingpress_notifications = $wpdb->prefix . 'bookingpress_notifications';

    //Install message options
    $BookingPress->bookingpress_update_settings('payment_token_failure_message', 'message_setting' , __('Payment token incorrect or mismatch', 'bookingpress-appointment-booking'));
    $BookingPress->bookingpress_update_settings('payment_already_paid_message', 'message_setting' , __('Payment already completed', 'bookingpress-appointment-booking'));
    $BookingPress->bookingpress_update_settings('complete_payment_success_message', 'message_setting' , __('Payment completed successfully', 'bookingpress-appointment-booking'));


    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $bookingpress_db_fields = array(
        'bookingpress_setting_name'  => 'complete_payment_deposit_amt_title',
        'bookingpress_setting_value' => __('Deposit Paid','bookingpress-appointment-booking'),
        'bookingpress_setting_type'  => 'booking_form',
    );        
    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);

    $bookingpress_db_fields = array(
        'bookingpress_setting_name'  => 'make_payment_button_title',
        'bookingpress_setting_value' => __('Make Payment','bookingpress-appointment-booking'),
        'bookingpress_setting_type'  => 'booking_form',
    );        
    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);

    //Install complete payment page
    $bookingpress_complete_payment_content = '[bookingpress_complete_payment]';
    $bookingpress_complete_payment_details = array(
        'post_title'   => esc_html__('Complete Payment', 'bookingpress-appointment-booking'),
        'post_name'    => 'bookingpress-complete-payment',
        'post_content' => $bookingpress_complete_payment_content,
        'post_status'  => 'publish',
        'post_parent'  => 0,
        'post_author'  => 1,
        'post_type'    => 'page',
    );
    $bookingpress_post_id = wp_insert_post($bookingpress_complete_payment_details);
    $BookingPress->bookingpress_update_settings('complete_payment_page_id', 'general_setting', $bookingpress_post_id);


    $tbl_bookingpress_entries = $wpdb->prefix."bookingpress_entries";
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix."bookingpress_appointment_bookings";
    $tbl_bookingpress_payment_logs = $wpdb->prefix."bookingpress_payment_transactions";

    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_complete_payment_url_selection varchar(20) DEFAULT NULL AFTER bookingpress_mark_as_paid" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_complete_payment_url_selection varchar(20) DEFAULT NULL AFTER bookingpress_mark_as_paid" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_complete_payment_url_selection varchar(20) DEFAULT NULL AFTER bookingpress_mark_as_paid" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_complete_payment_url_selection_method varchar(20) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_complete_payment_url_selection_method varchar(20) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_complete_payment_url_selection_method varchar(20) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_complete_payment_token varchar(255) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection_method" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} ADD bookingpress_complete_payment_token varchar(255) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection_method" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_complete_payment_token varchar(255) DEFAULT NULL AFTER bookingpress_complete_payment_url_selection_method" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

    $tbl_bookingpress_entries = $wpdb->prefix."bookingpress_entries";
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix."bookingpress_appointment_bookings";

    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} CHANGE bookingpress_dst_timezone bookingpress_dst_timezone TINYINT NULL DEFAULT '0'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} CHANGE bookingpress_dst_timezone bookingpress_dst_timezone TINYINT NULL DEFAULT '0'" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

    $bookingpress_default_notifications_name_arr = array( 'Complete Payment URL' );

    $bookingpress_default_notifications_message_arr        = array(
        'Complete Payment URL'    => 'Hi<br/>Please complete your payment with following URL: <br/>%complete_payment_url%<br/>Thanks,<br/>%company_name%',
    );
    
    foreach ( $bookingpress_default_notifications_name_arr as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
        $bookingpress_customer_notification_data = array(
            'bookingpress_notification_name'   => $bookingpress_default_notification_val,
            'bookingpress_notification_receiver_type' => 'customer',
            'bookingpress_notification_status' => 1,
            'bookingpress_notification_type'   => 'default',
            'bookingpress_notification_subject' => $bookingpress_default_notification_val,
            'bookingpress_notification_message' => $bookingpress_default_notifications_message_arr[ $bookingpress_default_notification_val ],
            'bookingpress_created_at'          => current_time( 'mysql' ),
        );
        $wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_customer_notification_data );
    }

    $bookingpress_default_notifications_arr2 = array(
        'Complete Payment URL'    => 'Hi administrator,<br/>Following payment URL is shared with customer. <br/>%complete_payment_url%<br/>Thanks,<br/>Thank you,<br>%company_name%',
    );
    foreach ( $bookingpress_default_notifications_name_arr as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
        $bookingpress_employee_notification_data = array(
            'bookingpress_notification_name'   => $bookingpress_default_notification_val,
            'bookingpress_notification_receiver_type' => 'employee',
            'bookingpress_notification_status' => 1,
            'bookingpress_notification_type'   => 'default',
            'bookingpress_notification_subject' => $bookingpress_default_notification_val,
            'bookingpress_notification_message' => $bookingpress_default_notifications_arr2[ $bookingpress_default_notification_val ],
            'bookingpress_created_at'          => current_time( 'mysql' ),
        );

        $wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_employee_notification_data );
    }
}

if (version_compare($bookingpress_pro_old_version, '1.6', '<') ) {
    global $wpdb;
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $tbl_bookingpress_entries = $wpdb->prefix."bookingpress_entries";
    $tbl_bookingpress_appointment_bookings = $wpdb->prefix."bookingpress_appointment_bookings";

    $bookingpress_updated_sequance_val = '["service_selection","staff_selection"]';
    $booking_form_sequence_settings = $BookingPress->bookingpress_get_customize_settings('bookingpress_form_sequance', 'booking_form');
    if($booking_form_sequence_settings == "staff_selection"){
        $bookingpress_updated_sequance_val = '["staff_selection", "service_selection"]';
    }
    $wpdb->update($tbl_bookingpress_customize_settings, array('bookingpress_setting_value' => $bookingpress_updated_sequance_val), array('bookingpress_setting_name' => 'bookingpress_form_sequance'));

     $wpdb->query("UPDATE {$tbl_bookingpress_appointment_bookings} SET bookingpress_selected_extra_members = bookingpress_selected_extra_members + 1"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm

      $wpdb->query("UPDATE {$tbl_bookingpress_entries} SET bookingpress_selected_extra_members = bookingpress_selected_extra_members + 1"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
    
     $wpdb->query( "ALTER TABLE {$tbl_bookingpress_appointment_bookings} CHANGE `bookingpress_selected_extra_members` `bookingpress_selected_extra_members` SMALLINT(6) NULL DEFAULT '1'" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

     $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} CHANGE `bookingpress_selected_extra_members` `bookingpress_selected_extra_members` SMALLINT(6) NULL DEFAULT '1'" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

    $booking_guest_title = $BookingPress->bookingpress_get_customize_settings('booking_guest_title', 'booking_my_booking');
    $bring_anyone_title = $BookingPress->bookingpress_get_customize_settings('bring_anyone_title', 'booking_form');
    
    if($booking_guest_title == 'Guest' || $booking_guest_title == '') {
        $bookingpress_updated_title_value = __('No. of Person','bookingpress-appointment-booking');
        $wpdb->update($tbl_bookingpress_customize_settings, array('bookingpress_setting_value' => $bookingpress_updated_title_value), array('bookingpress_setting_name' => 'booking_guest_title'));
    }
    if($bring_anyone_title == 'Bring Guest With You ?' || $bring_anyone_title == '') {
        $bookingpress_updated_title_val = __('No. of Person','bookingpress-appointment-booking');
        $wpdb->update($tbl_bookingpress_customize_settings, array('bookingpress_setting_value' => $bookingpress_updated_title_val), array('bookingpress_setting_name' => 'bring_anyone_title'));
    }
    update_option('bookingpress_bring_anyone_changes_notice',1);    
}

if (version_compare($bookingpress_pro_old_version, '1.7', '<') ) {
    global $wpdb,$tbl_bookingpress_services,$tbl_bookingpress_staffmembers,$BookingPress, $tbl_bookingpress_customize_settings;
    $bookingpress_service_expiration_date_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_service_expiration_date'", DB_NAME, $tbl_bookingpress_services ) );
    if ( empty( $bookingpress_service_expiration_date_col ) ) {
        $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_services}` ADD `bookingpress_service_expiration_date` DATE DEFAULT NUll AFTER `bookingpress_service_position`" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm
    }
    $bookingpress_staffmember_position_col = $wpdb->get_results( $wpdb->prepare( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND column_name = 'bookingpress_staffmember_position'", DB_NAME, $tbl_bookingpress_staffmembers ) );
    if ( empty( $bookingpress_staffmember_position_col ) ) {
        $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_staffmembers}` ADD `bookingpress_staffmember_position` INT(11) NOT NULL AFTER `bookingpress_staffmember_id`" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
    }
    update_option('bookingpress_customize_changes_notice_1.0.51', 1);

    $bookingpress_staffmember_details = $wpdb->get_results("SELECT bookingpress_staffmember_position,bookingpress_staffmember_id FROM {$tbl_bookingpress_staffmembers} ORDER BY bookingpress_staffmember_id",ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_staffmembers is a table name. false alarm
    $service_position = 0;
    foreach($bookingpress_staffmember_details as $key => $val ) {
        $bookingpress_staffmember_id = isset($val['bookingpress_staffmember_id']) ? intval($val['bookingpress_staffmember_id']) : '';        
        if(!empty($bookingpress_staffmember_id)) {
            $bookingpress_update_data = array(
                'bookingpress_staffmember_position' => $service_position,
            );
            $bookingpress_where = array(
                'bookingpress_staffmember_id' => $bookingpress_staffmember_id,
            );
            $wpdb->update($tbl_bookingpress_staffmembers,$bookingpress_update_data,$bookingpress_where);
            $service_position++;
        }
    }

    
    $booking_form_sequence_settings = $BookingPress->bookingpress_get_customize_settings('bookingpress_form_sequance', 'booking_form');
    if( !empty( $booking_form_sequence_settings ) && is_string( $booking_form_sequence_settings ) ){
        $form_sequence = json_decode( $booking_form_sequence_settings );
        if( JSON_ERROR_NONE !== json_last_error() ){
            $bookingpress_updated_sequance_val = '["service_selection","staff_selection"]';
            if($booking_form_sequence_settings == "staff_selection"){
                $bookingpress_updated_sequance_val = '["staff_selection", "service_selection"]';
            }
			$wpdb->update($tbl_bookingpress_customize_settings, array('bookingpress_setting_value' => $bookingpress_updated_sequance_val), array('bookingpress_setting_name' => 'bookingpress_form_sequance'));
        }
    }
}

if (version_compare($bookingpress_pro_old_version, '1.8', '<') ) {

    global $wpdb;

    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';

    $bookingpress_db_fields = array(
        'bookingpress_setting_name'  => 'book_appointment_day_text',
        'bookingpress_setting_value' => 'd',
        'bookingpress_setting_type'  => 'booking_form',
    );        
    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);
}

if (version_compare($bookingpress_pro_old_version, '1.9', '<') ) {
    global $wpdb,$tbl_bookingpress_services,$tbl_bookingpress_staffmembers,$BookingPress, $tbl_bookingpress_customize_settings,$tbl_bookingpress_payment_logs,$tbl_bookingpress_notifications;    

    $BookingPress->bookingpress_update_settings('bookingpress_refund_on_cancellation', 'payment_setting', 'bookingpress_refund_on_cancellation');
    $BookingPress->bookingpress_update_settings('bookingpress_refund_mode', 'payment_setting', 'full');
    $BookingPress->bookingpress_update_settings('bookingpress_refund_on_partial', 'payment_setting', 'false');
    $BookingPress->bookingpress_update_settings('bookingpress_partial_refund_rules', 'payment_setting', '');

    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_refund_initiate_from smallint(1) DEFAULT 0 AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_entries is a table name. false alarm    
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_refund_type varchar(20) DEFAULT NULL AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm    
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_refund_amount float DEFAULT 0 AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_refund_reason TEXT DEFAULT NULL AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm
    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_payment_logs} ADD bookingpress_refund_response TEXT DEFAULT NULL AFTER bookingpress_due_amount" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_payment_logs is a table name. false alarm

    $bookingpress_default_notifications_name_arr = array( 'Refund Payment' );

    $bookingpress_default_notifications_message_arr        = array(
        'Refund Payment'    => 'Dear %customer_first_name% %customer_last_name%,<br /> Your appointment %booking_id% has been canceled successfully, and the refund is initiated for the same from our end.<br /> You should expect the refund to your original payment method within 3 to 5 working days. <br />Thanks,<br />%company_name%',
    );    
    foreach ( $bookingpress_default_notifications_name_arr as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
        $bookingpress_customer_notification_data = array(
            'bookingpress_notification_name'   => $bookingpress_default_notification_val,
            'bookingpress_notification_receiver_type' => 'customer',
            'bookingpress_notification_status' => 1,
            'bookingpress_notification_type'   => 'default',
            'bookingpress_notification_subject' => $bookingpress_default_notification_val,
            'bookingpress_notification_message' => $bookingpress_default_notifications_message_arr[ $bookingpress_default_notification_val ],
            'bookingpress_created_at'          => current_time( 'mysql' ),
        );
        $wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_customer_notification_data );
    }
    $bookingpress_default_notifications_arr2 = array(
        'Refund Payment'    => 'Dear Administrator,<br /> The appointment %booking_id% has been canceled successfully, and the refund is initiated for the same from our end.<br />Thanks,<br />%company_name%',
    );
    foreach ( $bookingpress_default_notifications_name_arr as $bookingpress_default_notification_key => $bookingpress_default_notification_val ) {
        $bookingpress_employee_notification_data = array(
            'bookingpress_notification_name'   => $bookingpress_default_notification_val,
            'bookingpress_notification_receiver_type' => 'employee',
            'bookingpress_notification_status' => 1,
            'bookingpress_notification_type'   => 'default',
            'bookingpress_notification_subject' => $bookingpress_default_notification_val,
            'bookingpress_notification_message' => $bookingpress_default_notifications_arr2[ $bookingpress_default_notification_val ],
            'bookingpress_created_at'          => current_time( 'mysql' ),
        );
        $wpdb->insert( $tbl_bookingpress_notifications, $bookingpress_employee_notification_data );
    }
    
    $bookingpress_booking_form_customize_setting = array(
        'paid_amount_text' => esc_html__('Paid Amount', 'bookingpress-appointment-booking'),
        'refund_amount_text' => esc_html__('Refund Amount', 'bookingpress-appointment-booking'),
        'refund_payment_gateway_text' => esc_html__('Payment Method', 'bookingpress-appointment-booking'),
        'refund_apply_text' => esc_html__('Apply', 'bookingpress-appointment-booking'),
        'refund_cancel_text' => esc_html__('Cancel', 'bookingpress-appointment-booking'),
    );
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    foreach($bookingpress_booking_form_customize_setting as $key => $val){
        $bookingpress_bd_data = array(
            'bookingpress_setting_name' => $key,
            'bookingpress_setting_value' => $val,
            'bookingpress_setting_type' => 'booking_my_booking',
        );
        $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_bd_data);        
    }
    $BookingPress->bookingpress_update_settings('refund_policy_message','message_setting', __('Refund policy message','bookingpress-appointment-booking'));
}

if (version_compare($bookingpress_pro_old_version, '2.1', '<') ) {
    global $BookingPress,$wpdb;  
    $BookingPress->bookingpress_update_settings('bookingpress_staffmember_access_admin','staffmember_setting', 'false');

    $bookingpress_background_color = $BookingPress->bookingpress_get_customize_settings('background_color', 'booking_form');
    $bookingpress_footer_background_color = $BookingPress->bookingpress_get_customize_settings('footer_background_color', 'booking_form');
    $bookingpress_primary_color = $BookingPress->bookingpress_get_customize_settings('primary_color', 'booking_form');
    $bookingpress_content_color = $BookingPress->bookingpress_get_customize_settings('content_color', 'booking_form');
    $bookingpress_label_title_color = $BookingPress->bookingpress_get_customize_settings('label_title_color', 'booking_form');
    $bookingpress_title_font_family = $BookingPress->bookingpress_get_customize_settings('title_font_family', 'booking_form');        
    $bookingpress_sub_title_color = $BookingPress->bookingpress_get_customize_settings('sub_title_color', 'booking_form');
    $bookingpress_price_button_text_color = $BookingPress->bookingpress_get_customize_settings('price_button_text_color', 'booking_form');    
    $bookingpress_primary_background_color = $BookingPress->bookingpress_get_customize_settings('primary_background_color', 'booking_form');
    $bookingpress_border_color= $BookingPress->bookingpress_get_customize_settings('border_color', 'booking_form');

    $bookingpress_background_color = !empty($bookingpress_background_color) ? $bookingpress_background_color : '#fff';
    $bookingpress_footer_background_color = !empty($bookingpress_footer_background_color) ? $bookingpress_footer_background_color : '#f4f7fb';
    $bookingpress_primary_color = !empty($bookingpress_primary_color) ? $bookingpress_primary_color : '#12D488';
    $bookingpress_content_color = !empty($bookingpress_content_color) ? $bookingpress_content_color : '#727E95';
    $bookingpress_label_title_color = !empty($bookingpress_label_title_color) ? $bookingpress_label_title_color : '#202C45';
    $bookingpress_title_font_family = !empty($bookingpress_title_font_family) ? $bookingpress_title_font_family : '';    
    $bookingpress_sub_title_color = !empty($bookingpress_sub_title_color) ? $bookingpress_sub_title_color : '#535D71';
    $bookingpress_price_button_text_color = !empty($bookingpress_price_button_text_color) ? $bookingpress_price_button_text_color : '#fff';    
    $bookingpress_primary_background_color = !empty($bookingpress_primary_background_color) ? $bookingpress_primary_background_color : '#e2faf1';
    $bookingpress_border_color = !empty($bookingpress_border_color) ? $bookingpress_border_color : '#CFD6E5';


    $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_my_booking_settings';
    $bookingpress_custom_data_arr['action'][] = 'bookingpress_save_booking_form_settings';

    $my_booking_form = array(
        'background_color' => $bookingpress_background_color,
        'row_background_color' => $bookingpress_footer_background_color,
        'primary_color' => $bookingpress_primary_color,
        'content_color' => $bookingpress_content_color,
        'label_title_color' => $bookingpress_label_title_color,
        'title_font_family' => $bookingpress_title_font_family,        
        'sub_title_color'   => $bookingpress_sub_title_color,
        'price_button_text_color' => $bookingpress_price_button_text_color,        
        'border_color'         => $bookingpress_border_color,
    );

    $booking_form = array(
        'background_color' => $bookingpress_background_color,
        'footer_background_color' => $bookingpress_footer_background_color,
        'primary_color' => $bookingpress_primary_color,
        'primary_background_color'=> $bookingpress_primary_background_color,
        'label_title_color' => $bookingpress_label_title_color,
        'title_font_family' => $bookingpress_title_font_family,                
        'content_color' => $bookingpress_content_color,                
        'price_button_text_color' => $bookingpress_price_button_text_color,
        'sub_title_color' => $bookingpress_sub_title_color,
        'border_color'         => $bookingpress_border_color,
    );

    $bookingpress_custom_data_arr['booking_form'] = $booking_form;
    $bookingpress_custom_data_arr['my_booking_form'] = $my_booking_form;

    $BookingPress->bookingpress_generate_customize_css_func($bookingpress_custom_data_arr);

}

if( version_compare($bookingpress_pro_old_version, '2.6' , '<')){
    $tbl_bookingpress_notifications = $wpdb->prefix . 'bookingpress_notifications';
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} CHANGE bookingpress_notification_service bookingpress_notification_service TEXT DEFAULT NULL"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm
}

if( version_compare($bookingpress_pro_old_version, '2.7' , '<')){
    
    $bookingpress_share_timeslot_between_services_type = $BookingPress->bookingpress_get_settings('share_timeslot_between_services_type', 'general_setting');
    if(empty($bookingpress_share_timeslot_between_services_type)){
        $BookingPress->bookingpress_update_settings('share_timeslot_between_services_type', 'general_setting', 'all_service');
    }   

    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $bookingpress_db_fields = array(
        'bookingpress_setting_name'  => 'service_extras_label',
        'bookingpress_setting_value' => __('Extras','bookingpress-appointment-booking'),
        'bookingpress_setting_type'  => 'booking_form',
    );        
    $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_db_fields);
}

if( version_compare( $bookingpress_pro_old_version, '2.8', '<') ){
    $tbl_bookingpress_notifications = $wpdb->prefix . 'bookingpress_notifications';

    $get_notifications = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_status = %d AND bookingpress_notification_type = %s AND bookingpress_notification_name = %s", 1, 'default', 'Appointment Follow Up' // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_notifications is table name defined globally. False Positive alarm 
        )
    );

    if( !empty( $get_notifications ) ){
        foreach( $get_notifications as $notification_data ){
            $notification_id = $notification_data->bookingpress_notification_id;
            $wpdb->delete(
                $tbl_bookingpress_notifications,
                array(
                    'bookingpress_notification_id' => $notification_id
                )
            );
        }
    }
}

if( version_compare( $bookingpress_pro_old_version, '2.9', '<' ) ){
    $tbl_bookingpress_form_fields = $wpdb->prefix . 'bookingpress_form_fields';

    $get_invalid_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_field_type = %s OR bookingpress_field_type IS NULL", "custom") ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm 

    if( !empty( $get_invalid_fields ) ){
        foreach( $get_invalid_fields as $invalid_field_data ){
            $field_id = $invalid_field_data->bookingpress_form_field_id;
            $field_name = $invalid_field_data->bookingpress_form_field_name;
            $field_options = json_decode( $invalid_field_data->bookingpress_field_options, true );

            if( 'terms_and_conditions' == $field_name ){
                $field_options['visiblity'] = 'hidden';
                $wpdb->update(
                    $tbl_bookingpress_form_fields,
                    array(
                        'bookingpress_field_type' => 'terms_and_conditions',
                        'bookingpress_field_options'   => wp_json_encode( $field_options ),
                        'bookingpress_field_meta_key'  => 'terms_and_conditions_' . wp_generate_password( 6, false )
                    ),
                    array(
                        'bookingpress_form_field_id' => $field_id
                    )
                );
            } else if( 'username' == $field_name ){
                $wpdb->update(
                    $tbl_bookingpress_form_fields,
                    array(
                        'bookingpress_field_type' => 'text',
                        'bookingpress_field_options'   => wp_json_encode( $field_options ),
                        'bookingpress_field_meta_key'  => 'username_' . wp_generate_password( 6, false )
                    ),
                    array(
                        'bookingpress_form_field_id' => $field_id
                    )
                );
            }
        }
    }
}

if( version_compare( $bookingpress_pro_old_version, '3.1', '<' ) ){
    global $tbl_bookingpress_staffmembers_daysoff, $tbl_bookingpress_staffmembers_meta;

    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_staffmembers_daysoff}` ADD `bookingpress_staffmember_daysoff_enddate` DATE NULL DEFAULT NULL AFTER `bookingpress_staffmember_daysoff_date`, ADD `bookingpress_staffmember_daysoff_parent` int(11) NOT NULL DEFAULT 0 AFTER `bookingpress_staffmember_daysoff_enddate`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_daysoff is table name defined globally. False Positive alarm 

    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_staffmembers_meta} MODIFY bookingpress_staffmembermeta_value LONGTEXT" ); //phpcs:ignore
}

if( version_compare($bookingpress_pro_old_version, '3.3' , '<')){
    $tbl_bookingpress_coupons = $wpdb->prefix . 'bookingpress_coupons';
    $wpdb->query( "ALTER TABLE `{$tbl_bookingpress_coupons}` ADD `bookingpress_coupon_title` varchar(255) DEFAULT NULL AFTER `bookingpress_coupon_id`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_coupons is table name defined globally. False Positive alarm 

    $tbl_bookingpress_guests_data = $wpdb->prefix . 'bookingpress_guests_data';

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    @set_time_limit(0);

    $charset_collate = '';
    if ($wpdb->has_cap('collation') ) {
        if (! empty($wpdb->charset) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (! empty($wpdb->collate) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }
    $bookingpress_dbtbl_create = array();
    $sql_table                 = "DROP TABLE IF EXISTS `{$tbl_bookingpress_guests_data}`;
    CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_guests_data}`(
        `bookingpress_guest_data_id` bigint(11) NOT NULL AUTO_INCREMENT,
        `bookingpress_guest_data_appointment_id` bigint(11) NOT NULL DEFAULT 0,        
        `bookingpress_guest_data_repeater_id` bigint(11) NOT NULL DEFAULT 0,
        `bookingpress_guest_data_guest_no` bigint(11) NOT NULL DEFAULT 0,
        `bookingpress_guest_data_field_metakey` VARCHAR(255) NOT NULL,
        `bookingpress_guest_data_field_metavalue` TEXT DEFAULT NULL,
        `bookingpress_guest_data_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`bookingpress_guest_data_id`)
    ){$charset_collate};";

    $bookingpress_dbtbl_create[ $tbl_bookingpress_guests_data ] = dbDelta($sql_table);


    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $my_booking_guest_fields = array(
        'guests_section_title' => __('Guests', 'bookingpress-appointment-booking'),
        'guest_field_title' => __('Guest', 'bookingpress-appointment-booking'),
    );

    foreach($my_booking_guest_fields as $key => $value) {
        $bookingpress_get_customize_text = $BookingPress->bookingpress_get_customize_settings($key, 'booking_my_booking');
        if(empty($bookingpress_get_customize_text)){
            $bookingpress_customize_settings_db_fields = array(
                'bookingpress_setting_name'  => $key,
                'bookingpress_setting_value' => $value,
                'bookingpress_setting_type'  => 'booking_my_booking',
            );
            $wpdb->insert( $tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields );
        }
    }    

}

if( version_compare($bookingpress_pro_old_version, '3.4' , '<')){

    global $wpdb,$tbl_bookingpress_staffmembers_services, $tbl_bookingpress_servicesmeta, $tbl_bookingpress_services,$tbl_bookingpress_entries, $tbl_bookingpress_default_daysoff, $tbl_bookingpress_staffmembers_daysoff;

    $wpdb->query( "ALTER TABLE {$tbl_bookingpress_entries} ADD bookingpress_password varchar(255) DEFAULT NULL AFTER bookingpress_username" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_entries is table name defined globally. False Positive alarm
    
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_staffmembers_services} ADD `bookingpress_service_min_capacity` INT(11) NULL DEFAULT '1' AFTER bookingpress_service_capacity"); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_services is table name defined globally. False Positive alarm

    $wpdb->query( "INSERT INTO {$tbl_bookingpress_servicesmeta} ( bookingpress_service_id, bookingpress_servicemeta_name, bookingpress_servicemeta_value ) SELECT sm.bookingpress_service_id, 'min_capacity', '1' FROM {$tbl_bookingpress_services} as sm"); //phpcs:ignore

    $wpdb->query("ALTER TABLE `{$tbl_bookingpress_services}` ADD `bookingpress_service_start_date` DATE DEFAULT NUll AFTER `bookingpress_service_expiration_date`" );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_services is a table name. false alarm

    /** Default Off days repeat columns start */

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_default_daysoff} ADD `bookingpress_dayoff_repeat_frequency` INT(11) NOT NULL DEFAULT '1' AFTER `bookingpress_repeat`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table nam defined globally. False Positive alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_default_daysoff} ADD `bookingpress_dayoff_repeat_frequency_type`VARCHAR(10) NOT NULL DEFAULT 'year' AFTER `bookingpress_dayoff_repeat_frequency`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table nam defined globally. False Positive alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_default_daysoff} ADD `bookingpress_dayoff_repeat_duration` VARCHAR(15) NOT NULL DEFAULT 'forever' AFTER `bookingpress_dayoff_repeat_frequency_type`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table nam defined globally. False Positive alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_default_daysoff} ADD `bookingpress_dayoff_repeat_times` INT(11) NOT NULL DEFAULT '1' AFTER `bookingpress_dayoff_repeat_duration`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table nam defined globally. False Positive alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_default_daysoff} ADD `bookingpress_dayoff_repeat_date` DATE NOT NULL AFTER `bookingpress_dayoff_repeat_times`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_default_daysoff is table nam defined globally. False Positive alarm

    /** Default Off days repeat columns end */

    /** Staff member off days repeat columns start */

    /** Staff member off days repeat columns end */

    
}

if( version_compare($bookingpress_pro_old_version, '3.4.1' , '<')){

    global $wpdb, $tbl_bookingpress_staffmembers_daysoff;

    $staffmember_daysoff_repeat_frequency_exists = $wpdb->get_row("SHOW COLUMNS FROM {$tbl_bookingpress_staffmembers_daysoff} LIKE 'bookingpress_staffmember_daysoff_repeat_frequency'");// phpcs:ignore 
    if(empty($staffmember_daysoff_repeat_frequency_exists)){
        $wpdb->query("ALTER TABLE {$tbl_bookingpress_staffmembers_daysoff} ADD `bookingpress_staffmember_daysoff_repeat_frequency` INT(11) NOT NULL DEFAULT '1' AFTER `bookingpress_staffmember_daysoff_repeat`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_daysoff is table nam defined globally. False Positive alarm
    }
    $bookingpress_staffmember_daysoff_repeat_frequency_type_exists = $wpdb->get_row("SHOW COLUMNS FROM {$tbl_bookingpress_staffmembers_daysoff} LIKE 'bookingpress_staffmember_daysoff_repeat_frequency_type'");// phpcs:ignore 
    if(empty($bookingpress_staffmember_daysoff_repeat_frequency_type_exists)){
        $wpdb->query("ALTER TABLE {$tbl_bookingpress_staffmembers_daysoff} ADD `bookingpress_staffmember_daysoff_repeat_frequency_type`VARCHAR(10) NOT NULL DEFAULT 'year' AFTER `bookingpress_staffmember_daysoff_repeat_frequency`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_daysoff is table nam defined globally. False Positive alarm
    }
    $bookingpress_staffmember_daysoff_repeat_duration_exists = $wpdb->get_row("SHOW COLUMNS FROM {$tbl_bookingpress_staffmembers_daysoff} LIKE 'bookingpress_staffmember_daysoff_repeat_duration'");// phpcs:ignore 
    if(empty($bookingpress_staffmember_daysoff_repeat_duration_exists)){
        $wpdb->query("ALTER TABLE {$tbl_bookingpress_staffmembers_daysoff} ADD `bookingpress_staffmember_daysoff_repeat_duration` VARCHAR(15) NOT NULL DEFAULT 'forever' AFTER `bookingpress_staffmember_daysoff_repeat_frequency_type`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_daysoff is table nam defined globally. False Positive alarm
    }      
    $bookingpress_staffmember_daysoff_repeat_times_exists = $wpdb->get_row("SHOW COLUMNS FROM {$tbl_bookingpress_staffmembers_daysoff} LIKE 'bookingpress_staffmember_daysoff_repeat_times'");// phpcs:ignore 
    if(empty($bookingpress_staffmember_daysoff_repeat_times_exists)){
        $wpdb->query("ALTER TABLE {$tbl_bookingpress_staffmembers_daysoff} ADD `bookingpress_staffmember_daysoff_repeat_times` INT(11) NOT NULL DEFAULT '1' AFTER `bookingpress_staffmember_daysoff_repeat_duration`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_daysoff is table nam defined globally. False Positive alarm
    }
    $bookingpress_staffmember_daysoff_repeat_date_exists = $wpdb->get_row("SHOW COLUMNS FROM {$tbl_bookingpress_staffmembers_daysoff} LIKE 'bookingpress_staffmember_daysoff_repeat_date'");// phpcs:ignore 
    if(empty($bookingpress_staffmember_daysoff_repeat_date_exists)){
        $wpdb->query("ALTER TABLE {$tbl_bookingpress_staffmembers_daysoff} ADD `bookingpress_staffmember_daysoff_repeat_date` DATE NOT NULL AFTER `bookingpress_staffmember_daysoff_repeat_times`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_staffmembers_daysoff is table nam defined globally. False Positive alarm
    }    

}

if( version_compare( $bookingpress_pro_old_version, '3.5', '<') ){
    global $wpdb, $BookingPress;


    $BookingPress->bookingpress_update_settings('bpa_white_label_icon', 'general_setting', 'bpa_bookingpress_icon');
	
    $tbl_bookingpress_service_daysoff = $wpdb->prefix . 'bookingpress_service_daysoff';

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    @set_time_limit(0);

    $charset_collate = '';
    if ($wpdb->has_cap('collation') ) {
        if (! empty($wpdb->charset) ) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (! empty($wpdb->collate) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }

    $sql_table                 = "DROP TABLE IF EXISTS `{$tbl_bookingpress_service_daysoff}`;
    CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_service_daysoff}`(
        `bookingpress_service_daysoff_id` bigint(11) NOT NULL AUTO_INCREMENT,
        `bookingpress_service_id` bigint(11) NOT NULL,
        `bookingpress_service_daysoff_name` varchar(100) NOT NULL,
        `bookingpress_service_daysoff_date` date NOT NULL,
        `bookingpress_service_daysoff_enddate` date DEFAULT NULL,
        `bookingpress_service_daysoff_parent` int(11) NOT NULL DEFAULT 0,				
        `bookingpress_service_daysoff_repeat` int(1) DEFAULT 0,
        `bookingpress_service_daysoff_repeat_frequency` int(11) DEFAULT 1,
        `bookingpress_service_daysoff_repeat_frequency_type` VARCHAR(10) DEFAULT 'year',
        `bookingpress_service_daysoff_repeat_duration` VARCHAR( 15 ) DEFAULT 'forever',
        `bookingpress_service_daysoff_repeat_times` int(11) DEFAULT 1,
        `bookingpress_service_daysoff_repeat_date` DATE NOT NULL,
        `bookingpress_service_daysoff_created` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`bookingpress_service_daysoff_id`)
    ){$charset_collate};";
    dbDelta($sql_table);
}

if( version_compare( $bookingpress_pro_old_version, '3.7', '<') ){
    global $wpdb, $BookingPress, $wpdb, $tbl_bookingpress_form_fields;

    $post_table = $wpdb->posts;
    $tbl_bookingpress_customize_settings = $wpdb->prefix . 'bookingpress_customize_settings';
    $post_author = get_current_user_id();
    $bookingpress_reschedule_content= '<div class="wp-block-bookingpress-reschedule">[bookingpress_appointment_reschedule]</div>';
    $bookingpress_reschedule_page_details = array(
        'post_title'   => esc_html__('Appointment reschedule', 'bookingpress-appointment-booking'),
        'post_name'    => 'appointment-reschedule',
        'post_content' => $bookingpress_reschedule_content,
        'post_status'  => 'publish',
        'post_parent'  => 0,
        'post_author'  => 1,
        'post_type'    => 'page',
        'post_author'   => $post_author,
        'post_date'     => current_time( 'mysql' ),
        'post_date_gmt' => current_time( 'mysql', 1 ),
    );
    
    $wpdb->insert( $post_table, $bookingpress_reschedule_page_details );
    $bookingpress_reschedule_post_id = $wpdb->insert_id;

    $current_guid = get_post_field( 'guid', $bookingpress_reschedule_post_id );
    $where = array( 'ID' => $bookingpress_reschedule_post_id );
    if( '' === $current_guid ){
        $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $bookingpress_reschedule_post_id ) ), $where );
    }

    $bookingpress_reschedule_url = get_permalink($bookingpress_reschedule_post_id);
    if (! empty($bookingpress_reschedule_url) ) {
        $bookingpress_my_booking_customize_setting['appointment_reschedule_page'] = $bookingpress_reschedule_post_id;                
        foreach($bookingpress_my_booking_customize_setting as $key => $val){
            $bookingpress_bd_data = array(
                'bookingpress_setting_name' => $key,
                'bookingpress_setting_value' => $val,
                'bookingpress_setting_type' => 'booking_my_booking',
            );
            $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_bd_data);        
        }             
    }    

    $all_fields = $wpdb->get_results( "SELECT bookingpress_form_field_id FROM {$tbl_bookingpress_form_fields}  WHERE bookingpress_field_type = 'file' ORDER BY bookingpress_form_field_id ASC" );  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_form_fields is a table name. false alarm

    if ( ! empty( $all_fields ) ) {
        foreach ( $all_fields as $field_data ) {
            $bookingpress_field_id   = $field_data->bookingpress_form_field_id;
            $wpdb->update(
                $tbl_bookingpress_form_fields,
                array(
                    'bookingpress_field_placeholder' => __('Choose a file...', 'bookingpress-appointment-booking'),
                ),
                array(
                'bookingpress_form_field_id' => $bookingpress_field_id,
                )
            );

        }
    }

}


$BookingPressPro->update_bookingpress_lite();
$bookingpress_pro_new_version = '3.7';
update_option('bookingpress_pro_new_version_installed', 1);
update_option('bookingpress_pro_version', $bookingpress_pro_new_version);
update_option('bookingpress_pro_updated_date_' . $bookingpress_pro_new_version, current_time('mysql'));
