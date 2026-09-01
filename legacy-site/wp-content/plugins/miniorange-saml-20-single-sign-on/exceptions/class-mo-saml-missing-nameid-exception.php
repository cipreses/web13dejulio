<?php
/**
 * This file contains the class Mo_SAML_Metadata_Reader_Exception
 * This exception is thrown when the plugin cant find the nameID attribute in the IDP response.
 *
 * @package miniorange-saml-20-single-sign-on/exception
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This exception indicates that the plugin cant find the nameID attribute in the IDP response.
 */
class Mo_SAML_Missing_NameID_Exception extends Exception {
	/**
	 * Constructor function, which defines the `$code` and `$message` for
	 * the exception, and makes a call to the parent (`Exception`) constructor.
	 *
	 * @param mixed $message this contains the error message.
	 */
	public function __construct( $message ) {
		$message = $message;
		$code    = 02;
		parent::__construct( $message, $code, null );
	}
}
