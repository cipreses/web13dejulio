<?php
/**
 * This file contains a handler to process custom exception and pass it to be displayed.
 *
 * @package miniorange-saml-20-single-sign-on/handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Handler to process custom exception and pass it to be displayed.
 */
class Mo_Saml_Exception_Handler {
	/**
	 * Used to display exceptions, if the exception has a non 0 code this function fetches the error code defined by plugin.
	 *
	 * @param Exception $exception Exception object.
	 * @param bool      $is_notice Optional. Determines if the thrown exception should be shown as an admin notice. Default false.
	 * @return void
	 */
	public static function mo_saml_throw_exception( $exception, $is_notice = false ) {
		$code       = $exception->getCode();
		$error_code = 'WPSAMLERR';
		if ( 0 !== $code ) {
			if ( $code < 10 ) {
				$error_code .= '00' . $code;
			} else {
				$error_code .= '0' . $code;
			}
			if ( ! empty( Mo_Saml_Options_Enum_Error_Codes::$error_codes[ $error_code ] ) ) {
				if ( $is_notice ) {
					mo_saml_display_exception_notice_to_admin( Mo_Saml_Options_Enum_Error_Codes::$error_codes[ $error_code ] );
				} else {
					mo_saml_display_end_user_error_message_with_code( Mo_Saml_Options_Enum_Error_Codes::$error_codes[ $error_code ] );
				}
			}
		}
	}
}
