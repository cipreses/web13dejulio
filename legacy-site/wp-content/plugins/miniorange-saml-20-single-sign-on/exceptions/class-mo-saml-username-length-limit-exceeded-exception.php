<?php
/**
 * This file contains the class Mo_SAML_Invalid_Assertion_Exception
 * This exception is thrown when Username value is greater than 60 characters.
 *
 * @package miniorange-saml-20-single-sign-on/exception
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This exception indicates that the Username value is greater than 60 characters.
 */
class Mo_Saml_Username_Length_Limit_Exceeded_Exception extends Exception {
	/**
	 * Constructor function, which defines the `$code` and `$message` for
	 * the exception, and makes a call to the parent (`Exception`) constructor.
	 *
	 * @param mixed $message this contains the error message.
	 */
	public function __construct( $message ) {
		$message = $message;
		$code    = 11;
		parent::__construct( $message, $code, null );
	}
}
