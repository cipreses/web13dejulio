<?php
/**
 * This file contains the class Mo_SAML_Cert_Mismatch_Exception
 * This exception is thrown when there is mismatch in certificate in SAML Response.
 *
 * @package miniorange-saml-20-single-sign-on/exception
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This exception indicates that there is mismatch in certificate in SAML Response.
 */
class Mo_SAML_Cert_Mismatch_Exception extends Exception {
	/**
	 * Constructor function, which defines the `$code` and `$message` for
	 * the exception, and makes a call to the parent (`Exception`) constructor.
	 *
	 * @param mixed $message this contains the error message.
	 */
	public function __construct( $message ) {
		$message = $message;
		$code    = 04;
		parent::__construct( $message, $code, null );
	}
}
