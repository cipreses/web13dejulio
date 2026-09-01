<?php
/**
 * This file contains the class Mo_SAML_Metadata_Reader_Exception
 * This exception is thrown when your SP clock is behind the IDP clock.
 *
 * @package miniorange-saml-20-single-sign-on/exception
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This exception indicates that your SP clock is behind the IDP clock.
 */
class Mo_SAML_SP_Clock_Behind_Of_IDP_Clock_Exception extends Exception {
	/**
	 * Constructor function, which defines the `$code` and `$message` for
	 * the exception, and makes a call to the parent (`Exception`) constructor.
	 *
	 * @param mixed $message this contains the error message.
	 */
	public function __construct( $message ) {
		$message = $message;
		$code    = 07;
		parent::__construct( $message, $code, null );
	}
}
