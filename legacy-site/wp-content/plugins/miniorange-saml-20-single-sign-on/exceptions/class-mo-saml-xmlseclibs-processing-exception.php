<?php
/**
 * This file contains the class Mo_SAML_XMLSecLibs_Processing_Exception
 * This exception is thrown when we are unable to process XML with XMLSecLibs.
 *
 * @package miniorange-saml-20-single-sign-on/exception
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This exception indicates that we are unable to process XML with XMLSecLibs.
 */
class Mo_SAML_XMLSecLibs_Processing_Exception extends Exception {
	/**
	 * Constructor function, which defines the `$code` and `$message` for
	 * the exception, and makes a call to the parent (`Exception`) constructor.
	 *
	 * @param mixed $message this contains the error message.
	 */
	public function __construct( $message ) {
		$message = $message;
		$code    = 28;
		parent::__construct( $message, $code, null );
	}
}
