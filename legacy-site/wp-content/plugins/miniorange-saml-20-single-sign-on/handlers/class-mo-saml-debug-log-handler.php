<?php
/** This file contains functions to handle the SAML debugging logs.
 *
 * @package     miniorange-saml-20-single-sign-on\handlers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class Mo_SAML_Debug_Log_Handler contains functions to handle all SAML logs related functionalities.
 */
class Mo_SAML_Debug_Log_Handler {

	/**
	 * This function is used for performing actions like Downloading, clearing the log file or enabling the logs for the SAML plguin.
	 *
	 * @param array $post_array This contains form $_POST data.
	 *
	 * @return void
	 */
	public static function mo_saml_process_logging( $post_array ) {

		if ( isset( $post_array['mo_saml_clear_debug_logs'] ) ) {
			self::mo_saml_cleanup_logs();
		} elseif ( isset( $post_array['mo_saml_download_debug_logs'] ) ) {
			self::mo_saml_download_log_file();
		} elseif ( isset( $post_array['mo_saml_delete_debug_log_files'] ) ) {
			self::mo_saml_delete_log_files();
		} else {
			self::mo_saml_enable_logging( $post_array );
		}
	}

	/**
	 * This function is used for downloading the log file.
	 */
	public static function mo_saml_download_log_file() {
		if ( ! Mo_SAML_Logger::mo_saml_is_debugging_enabled() ) {
			return;
		}

		Mo_SAML_Logger::mo_saml_download_debug_logs();
	}

	/**
	 * This function is used for clearing all the SAML plugin related logs.
	 *
	 * @return void
	 */
	public static function mo_saml_cleanup_logs() {
		if ( ! Mo_SAML_Logger::mo_saml_is_debugging_enabled() ) {
			$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::ERROR, Mo_Saml_Messages::mo_saml_translate( 'LOG_FILE_NOT_FOUND' ) );
			$post_save->mo_saml_post_save_action();
			return;
		}

		Mo_SAML_Logger::mo_saml_clear_debug_logs();
		$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::SUCCESS, Mo_Saml_Messages::mo_saml_translate( 'LOG_FILE_CLEARED' ) );
		$post_save->mo_saml_post_save_action();
	}

	/**
	 * This function is used for deleting the log files.
	 *
	 * @return void
	 */
	public static function mo_saml_delete_log_files() {
		if ( Mo_SAML_Logger::mo_saml_is_debugging_enabled() ) {
			$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::ERROR, Mo_Saml_Messages::mo_saml_translate( 'DISABLE_DEBUG_LOGS_FIRST' ) );
			$post_save->mo_saml_post_save_action();
			return;
		}

		Mo_SAML_Logger::mo_saml_delete_debug_log_files();
		$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::SUCCESS, Mo_Saml_Messages::mo_saml_translate( 'LOG_FILES_DELETED' ) );
		$post_save->mo_saml_post_save_action();
	}

	/**
	 * This function is used for enabling the logs for the SAML plugin.
	 *
	 * @param array $post_array This contains form $_POST data.
	 *
	 * @return void
	 */
	public static function mo_saml_enable_logging( $post_array ) {

		if ( isset( $post_array['mo_saml_enable_debug_logs'] ) && 'checked' === $post_array['mo_saml_enable_debug_logs'] ) {
			$result = Mo_SAML_Logger::mo_saml_enable_debug_log();
		} else {
			$result = Mo_SAML_Logger::mo_saml_disable_debug_log();
		}

		if ( $result ) {
			$delay_for_file_write = (int) 2;
			sleep( $delay_for_file_write );
			wp_safe_redirect( Mo_SAML_Utilities::mo_saml_get_current_page_url() );
			exit();
		} else {
			$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::ERROR, Mo_Saml_Messages::mo_saml_translate( 'WPCONFIG_ERROR' ) );
			$post_save->mo_saml_post_save_action();
		}
	}
}
