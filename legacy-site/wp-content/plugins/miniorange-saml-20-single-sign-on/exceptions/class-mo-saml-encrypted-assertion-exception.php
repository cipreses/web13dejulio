<?php
/**
 * This file contains the class Mo_SAML_Encrypted_Assertion_Exception
 * This exception is thrown when the Free Version of the plugin does not support encrypted assertion and IDP is sending Encrypted Assertion.
 *
 * @package miniorange-saml-20-single-sign-on/exception
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This exception indicates that the IDP is sending Encrypted Assertion.
 */
class Mo_SAML_Encrypted_Assertion_Exception extends Exception {
	/**
	 * Constructor function, which defines the `$code` and `$message` for
	 * the exception, and makes a call to the parent (`Exception`) constructor.
	 *
	 * @param mixed $message this contains the error message.
	 */
	public function __construct( $message ) {
		$message = $message;
		$code    = 01;
		parent::__construct( $message, $code, null );
	}
}
