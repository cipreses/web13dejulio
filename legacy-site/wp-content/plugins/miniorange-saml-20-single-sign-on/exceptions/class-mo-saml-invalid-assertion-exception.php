<?php
/**
 * This file contains the class Mo_SAML_Invalid_Assertion_Exception
 * This exception is thrown when we get the invalid assertion.
 *
 * @package miniorange-saml-20-single-sign-on/exception
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This exception indicates that the received Assertion is incorrect or malformed.
 */
class Mo_SAML_Invalid_Assertion_Exception extends Exception {
	/**
	 * Constructor function, which defines the `$code` and `$message` for
	 * the exception, and makes a call to the parent (`Exception`) constructor.
	 *
	 * @param mixed $message this contains the error message.
	 */
	public function __construct( $message ) {
		$message = $message;
		$code    = 24;
		parent::__construct( $message, $code, null );
	}
}
