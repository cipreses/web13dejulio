<?php
/**
 * This file contains function which displays the error page on how to fix the Test Configuration errors.
 *
 * @package miniorange-20-single-sign-on\handlers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class displays the error page on how to fix the Test Configuration errors.
 */
class Mo_Saml_Test_Config_Error_Handler {

	/**
	 * This function will get the options to display the error message in test configuration.
	 *
	 * @return void
	 */
	public static function mo_saml_get_settings_handler() {
		if ( isset( $_SERVER['QUERY_STRING'] ) && ! Mo_SAML_Utilities::mo_saml_is_plugin_page( sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to view this page' );
		}

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing  -- Ignore the nonce verification for test config operation.
		if ( isset( $_REQUEST['option'] ) && empty( $_POST['option'] ) ) {
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignore the nonce verification for test config operation.
			$error_type = sanitize_text_field( wp_unslash( $_REQUEST['option'] ) );
			self::mo_saml_test_config_error_display( $error_type );
		}
	}

	/**
	 * This function will provide the actual values to fix the Test Configuration.
	 *
	 * @param string $error_type Contains the input from the test configuration to know which issue needs to be displayed.
	 * @return void
	 */
	public static function mo_saml_test_config_error_display( $error_type ) {

		switch ( $error_type ) {
			case 'test_config_error_wpsamlerr004':
				$error_code = Mo_Saml_Options_Enum_Error_Codes::$error_codes['WPSAMLERR004'];
				break;
			case 'test_config_error_wpsamlerr012':
				$error_code = Mo_Saml_Options_Enum_Error_Codes::$error_codes['WPSAMLERR012'];
				break;
			case 'test_config_error_wpsamlerr010':
				$error_code = Mo_Saml_Options_Enum_Error_Codes::$error_codes['WPSAMLERR010'];
				break;
		}
		mo_saml_display_test_config_error_page( $error_code );
	}
}
