<?php
/**
 * This file contains the class Mo_SAML_Invalid_Entity_ID_Exception
 * This exception is thrown when you have configured wrong IDP Entity ID in the plugin.
 *
 * @package miniorange-saml-20-single-sign-on/exception
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This exception indicates that you have configured wrong IDP Entity ID in the plugin.
 */
class Mo_SAML_Invalid_Entity_ID_Exception extends Exception {
	/**
	 * Constructor function, which defines the `$code` and `$message` for
	 * the exception, and makes a call to the parent (`Exception`) constructor.
	 *
	 * @param mixed $message this contains the error message.
	 */
	public function __construct( $message ) {
		$message = $message;
		$code    = 10;
		parent::__construct( $message, $code, null );
	}
}
