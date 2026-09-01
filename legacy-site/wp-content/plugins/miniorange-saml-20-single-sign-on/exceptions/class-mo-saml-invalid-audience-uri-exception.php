<?php
/**
 * This file contains the class Mo_SAML_Invalid_Audience_URI_Exception
 * This exception is thrown when Audience URI is not correctly configured at your Identity Provider.
 *
 * @package miniorange-saml-20-single-sign-on/exception
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This exception indicates that the Audience URI is not correctly configured at your Identity Provider.
 */
class Mo_SAML_Invalid_Audience_URI_Exception extends Exception {
	/**
	 * Constructor function, which defines the `$code` and `$message` for
	 * the exception, and makes a call to the parent (`Exception`) constructor.
	 *
	 * @param mixed $message this contains the error message.
	 */
	public function __construct( $message ) {
		$message = $message;
		$code    = 9;
		parent::__construct( $message, $code, null );
	}
}
