<?php
/**
 * This file contains a handler for user login flow.
 *
 * @package miniorange-saml-20-single-sign-on/handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-xmlseclibs-processing-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-invalid-xml-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-dom-extension-disabled-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-invalid-assertion-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-invalid-audience-uri-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-cert-mismatch-encoding-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-cert-mismatch-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-encrypted-assertion-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-invalid-entity-id-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-invalid-status-code-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-missing-nameid-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-signature-not-found-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-sp-clock-ahead-of-idp-clock-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-sp-clock-behind-of-idp-clock-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-user-creation-exception.php';
require_once dirname( __DIR__ ) . '/exceptions/class-mo-saml-username-length-limit-exceeded-exception.php';
require_once dirname( __DIR__ ) . '/handlers/class-mo-saml-exception-handler.php';
/**
 * Class to handle user login and catch any exceptions that are thrown.
 */
class Mo_Saml_User_Login_Handler {
	/**
	 * Stores Mo_Saml_User_Login_Handler object.
	 *
	 * @var object
	 */
	private static $instance;
	/**
	 * Returns Mo_Saml_User_Login_Handler class object.
	 */
	public static function mo_saml_get_object() {
		if ( ! isset( self::$instance ) ) {
			$class          = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}
	/**
	 * Wrapper for mo_login_validator, initiates SSO flow, catches exceptions.
	 *
	 * @return void
	 */
	public static function mo_saml_handle_login_validate() {
		try {
			$mo_saml_login_validate = new Mo_SAML_Login_Validate();
		} catch ( Mo_SAML_XMLSecLibs_Processing_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_DOM_Extension_Disabled_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_Invalid_XML_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_Invalid_Assertion_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_Invalid_Audience_URI_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_Saml_User_Creation_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_Saml_Username_Length_Limit_Exceeded_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_Invalid_Entity_ID_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_Encrypted_Assertion_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_Cert_Mismatch_Encoding_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_Cert_Mismatch_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_Invalid_Status_Code_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_Missing_NameID_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_Signature_Not_Found_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_SP_Clock_Ahead_Of_IDP_Clock_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_SAML_SP_Clock_Behind_Of_IDP_Clock_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Mo_Saml_Username_Length_Limit_Exceeded_Exception $ex ) {
			Mo_Saml_Exception_Handler::mo_saml_throw_exception( $ex );
		} catch ( Exception $ex ) {
			Mo_SAML_Logger::mo_saml_add_log( $ex->getMessage(), \Mo_SAML_Logger::ERROR );
			wp_die( 'We could not sign you in. Please contact your administrator.' );
		}
	}
}
