<?php
/**
 * This File has class which handles all the debug logs file related functions.
 *
 * @package miniorange-saml-20-single-sign-on
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'class-mo-saml-utilities.php';
require_once __DIR__ . '/includes/lib/class-mo-saml-options-enum.php';
require_once 'class-mo-saml-wp-config-editor.php';

/**
 * Class includes all the functions like to create log file, to add logs, to get log file, and etc.. .
 *
 * @category Class
 */
class Mo_SAML_Logger {

	const INFO     = 'INFO';
	const DEBUG    = 'DEBUG';
	const ERROR    = 'ERROR';
	const CRITICAL = 'CRITICAL';

	/**
	 * Debug log constant name.
	 */
	const DEBUG_LOG_CONSTANT = 'MO_SAML_LOGGING';

	/**
	 * Debug log file path option name.
	 */
	const DEBUG_LOG_FILE_PATH_OPTION_NAME = 'mosaml_debug_log_file_path';

	/**
	 * Plugin name for log directory.
	 */
	const PLUGIN_NAME = 'miniorange-saml';

	/**
	 * To check if file is writable.
	 *
	 * @var boolean
	 */
	private static $log_file_writable = false;

	const HTACCESS_FILE_CONTENT = '<RequireAll>
								Require all denied
							</RequireAll>
							<IfModule !mod_authz_core.c>
								Order deny,allow
								Deny from all
							</IfModule>
							<Files "*">
								SetHandler default-handler
							</Files>
							Options -Indexes
							ServerSignature Off';

	/**
	 * Data of logs.
	 *
	 * @var array
	 */
	protected $cached_logs = array();

	/**
	 * Enable debug logging.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function mo_saml_enable_debug_log() {
		if ( defined( self::DEBUG_LOG_CONSTANT ) && true === constant( self::DEBUG_LOG_CONSTANT ) ) {
			return true;
		}
		if ( ! self::is_wp_config_writable() ) {
			return false;
		}

		if ( self::set_debug_log_constant_to_wp_config( true ) && self::mo_saml_create_debug_log_file() ) {
			return true;
		}
		return false;
	}

	/**
	 * Disable debug logging.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function mo_saml_disable_debug_log() {
		if ( ! defined( self::DEBUG_LOG_CONSTANT ) || false === constant( self::DEBUG_LOG_CONSTANT ) ) {
			return true;
		}
		if ( ! self::is_wp_config_writable() ) {
			return false;
		}
		if ( self::set_debug_log_constant_to_wp_config( false ) ) {
			return true;
		}
		return false;
	}

	/**
	 * The admin init actions which need to be taken regarding debug logs i.e., displaying the error/success message.
	 *
	 * @return void
	 */
	public static function mo_saml_debug_log_actions() {
		if ( ! self::is_wp_config_writable() && defined( self::DEBUG_LOG_CONSTANT ) && true === constant( self::DEBUG_LOG_CONSTANT ) ) {
			add_action(
				'admin_notices',
				function () {
					echo wp_kses_post(
						sprintf(
							/* translators: %1s: search term */
							'<div class="error" style=""><p/>' . __( 'To allow logging, make  <code>"%1s"</code> directory writable.miniOrange will not be able to log the errors.', 'miniorange-saml-20-single-sign-on' ) . '</div>',
							self::get_plugin_debug_log_directory()
						)
					);
				}
			);
		}
		if ( self::is_wp_config_writable() && defined( self::DEBUG_LOG_CONSTANT ) && true === constant( self::DEBUG_LOG_CONSTANT ) && current_user_can( 'manage_options' ) ) {
			add_action(
				'admin_notices',
				function () {
					echo wp_kses_post(
						sprintf(
							/* translators: %s: search term */
							'<div class="updated"><p/>' . __( ' miniOrange SAML 2.0 logs are active. Want to turn it off? <a href="%s">Learn more here.', 'miniorange-saml-20-single-sign-on' ) . '</a></div>',
							admin_url() . 'admin.php?page=mo_saml_enable_debug_logs'
						)
					);
				}
			);
		}
	}

	/**
	 * Clear the debug logs.
	 *
	 * @return void
	 */
	public static function mo_saml_clear_debug_logs() {
		if ( ! defined( self::DEBUG_LOG_CONSTANT ) || true !== constant( self::DEBUG_LOG_CONSTANT ) ) {
			return;
		}

		$debug_file_path = get_option( self::DEBUG_LOG_FILE_PATH_OPTION_NAME, '' );
		if ( ! $debug_file_path ) {
			$debug_file_path = self::mo_saml_create_debug_log_file();
		}

		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$wp_filesystem->put_contents( $debug_file_path, '', FS_CHMOD_FILE );
	}

	/**
	 * Download the debug logs.
	 *
	 * @return void
	 */
	public static function mo_saml_download_debug_logs() {
		if ( ! defined( self::DEBUG_LOG_CONSTANT ) || true !== constant( self::DEBUG_LOG_CONSTANT ) ) {
			return;
		}

		$debug_file_path = get_option( self::DEBUG_LOG_FILE_PATH_OPTION_NAME, '' );
		if ( $debug_file_path ) {
			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				WP_Filesystem();
			}
			if ( $wp_filesystem->exists( $debug_file_path ) ) {
				$content = $wp_filesystem->get_contents( $debug_file_path );
				header( 'Content-Description: File Transfer' );
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename="' . basename( $debug_file_path ) . '"' );
				header( 'Content-Length: ' . strlen( $content ) );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This is to download the file.
				echo $content;
				exit;
			}
		}
	}

	/**
	 * Delete the debug log files.
	 *
	 * @return void
	 */
	public static function mo_saml_delete_debug_log_files() {
		if ( defined( self::DEBUG_LOG_CONSTANT ) && true === constant( self::DEBUG_LOG_CONSTANT ) ) {
			return;
		}
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$dir = self::get_plugin_debug_log_directory();

		$files = $wp_filesystem->dirlist( $dir );

		if ( $files ) {
			foreach ( $files as $filename => $fileinfo ) {
				if ( 'f' === $fileinfo['type'] && strpos( $filename, 'mosaml-debug-' ) !== false ) {
					$wp_filesystem->delete( $dir . DIRECTORY_SEPARATOR . $filename );
				}
			}
		}
	}

	/**
	 * Create the debug log file.
	 *
	 * @return string|false The file path on success, false on failure.
	 */
	private static function mo_saml_create_debug_log_file() {
		if ( ! self::mo_saml_create_debug_log_folder_if_not_exists() ) {
			return false;
		}
		$debug_log_file_name = 'mosaml-debug-' . str_replace( '-', '', wp_generate_uuid4() ) . '-' . gmdate( 'Ymd-His' ) . '.log';
		$log_file            = self::get_plugin_debug_log_directory() . DIRECTORY_SEPARATOR . $debug_log_file_name;
		$debug_log_file_path = self::create_file_if_not_exists( $log_file, '' );
		update_option( self::DEBUG_LOG_FILE_PATH_OPTION_NAME, $debug_log_file_path );
		return $debug_log_file_path;
	}

	/**
	 * Create the debug log folder if it doesn't exist.
	 *
	 * @return bool
	 */
	public static function mo_saml_create_debug_log_folder_if_not_exists() {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$plugin_debug_log_dir = self::get_plugin_debug_log_directory();
		if ( ! $wp_filesystem->is_dir( $plugin_debug_log_dir ) ) {
			$created = $wp_filesystem->mkdir( $plugin_debug_log_dir, FS_CHMOD_DIR );
			if ( ! $created ) {
				return false;
			}
			self::create_index_file_if_not_exists( $plugin_debug_log_dir, $wp_filesystem );
		}
		return true;
	}

	/**
	 * Create the index file if it doesn't exist.
	 *
	 * @param string $plugin_debug_log_dir The path to the plugin debug log directory.
	 * @param object $wp_filesystem The WordPress filesystem object.
	 * @return void
	 */
	private static function create_index_file_if_not_exists( $plugin_debug_log_dir, $wp_filesystem ) {
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$index_file    = $plugin_debug_log_dir . '/index.php';
		$index_content = "<?php\n// Silence is golden.\n";

		if ( ! $wp_filesystem->exists( $index_file ) || trim( $wp_filesystem->get_contents( $index_file ) ) !== trim( $index_content ) ) {
			$wp_filesystem->put_contents( $index_file, $index_content, FS_CHMOD_FILE );
		}
	}

	/**
	 * Get the plugin debug log directory path.
	 *
	 * @return string The absolute path to the plugin debug log directory.
	 */
	public static function get_plugin_debug_log_directory() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . DIRECTORY_SEPARATOR . self::PLUGIN_NAME;
	}

	/**
	 * Create the file if it doesn't exist.
	 *
	 * @param string $file_path The file path to create.
	 * @param string $content The content to write to the file.
	 * @return string|false The file path on success, false on failure.
	 */
	private static function create_file_if_not_exists( $file_path, $content ) {
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if ( ! $wp_filesystem->put_contents( $file_path, $content, FS_CHMOD_FILE ) ) {
			return false;
		}
		return $file_path;
	}

	/**
	 * Log a debug message.
	 *
	 * @param mixed  $content The message to log.
	 * @param string $log_level log entry info.
	 * @return void
	 */
	public static function log( $content, $log_level ) {
		if ( ! defined( self::DEBUG_LOG_CONSTANT ) || false === constant( self::DEBUG_LOG_CONSTANT ) ) {
			return;
		}

		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$debug_file_path = get_option( self::DEBUG_LOG_FILE_PATH_OPTION_NAME, '' );
		if ( ! $debug_file_path ) {
			$debug_file_path = self::mo_saml_create_debug_log_file();
		}

		$content = '[' . gmdate( 'Y-m-d H:i:s' ) . '] [' . $log_level . '] ' . $content . PHP_EOL;
		$content = $wp_filesystem->get_contents( $debug_file_path ) . $content;
		$wp_filesystem->put_contents( $debug_file_path, $content, FS_CHMOD_FILE );
	}

	/**
	 * Check if wp-config.php is writable.
	 *
	 * @return bool True if writable, false otherwise.
	 */
	private static function is_wp_config_writable() {
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		return $wp_filesystem->is_writable( self::get_wp_config_path() );
	}

	/**
	 * Get the path to wp-config.php file.
	 *
	 * @return string The path to wp-config.php file.
	 */
	private static function get_wp_config_path() {
		return ABSPATH . 'wp-config.php';
	}

	/**
	 * Add or update a constant definition in wp-config.php.
	 *
	 * @param bool $value The value to set the constant to.
	 * @return bool True on success, false on failure.
	 */
	private static function set_debug_log_constant_to_wp_config( $value ) {
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$wp_config_path    = self::get_wp_config_path();
		$wp_config_content = $wp_filesystem->get_contents( $wp_config_path );
		if ( false === $wp_config_content ) {
			return false;
		}

		$constant_pattern = '/define\s*\(\s*[\'"]' . preg_quote( self::DEBUG_LOG_CONSTANT, '/' ) . '[\'"]\s*,\s*[^)]+\)\s*;/';
		if ( preg_match( $constant_pattern, $wp_config_content ) ) {
			$new_content = preg_replace(
				$constant_pattern,
				"define( '" . self::DEBUG_LOG_CONSTANT . "', " . ( $value ? 'true' : 'false' ) . ' );',
				$wp_config_content
			);
		} else {
			$insert_position = strpos( $wp_config_content, "/* That's all, stop editing!" );
			if ( false === $insert_position ) {
				return false;
			}
			$insert_position = strrpos( substr( $wp_config_content, 0, $insert_position ), "\n" );
			if ( false === $insert_position ) {
				return false;
			}

			$new_content = substr( $wp_config_content, 0, $insert_position ) .
							"define( '" . self::DEBUG_LOG_CONSTANT . "', " . ( $value ? 'true' : 'false' ) . " );\n" .
							substr( $wp_config_content, $insert_position );
		}

		$result = $wp_filesystem->put_contents( $wp_config_path, $new_content, FS_CHMOD_FILE );
		return false !== $result;
	}

	/**
	 * Check if debug logging is enabled.
	 *
	 * @return bool True if debugging is enabled, false otherwise.
	 */
	public static function mo_saml_is_debugging_enabled() {
		if ( ! defined( self::DEBUG_LOG_CONSTANT ) ) {
			return false;
		} else {
			return constant( self::DEBUG_LOG_CONSTANT );
		}
	}

	/**
	 * Debug log enabled warning.
	 *
	 * @return array
	 */
	public static function debug_log_enabled_warning() {
		if ( ! defined( self::DEBUG_LOG_CONSTANT ) || false === constant( self::DEBUG_LOG_CONSTANT ) ) {
			return array(
				'label'       => __( 'Debug Log Disabled', 'miniorange-saml-20-single-sign-on' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Security', 'miniorange-saml-20-single-sign-on' ),
					'color' => 'blue',
				),
				'description' => __( 'Debug logging is currently disabled for the SAML plugin. Warning added successfully. This is the recommended setting for production environments.', 'miniorange-saml-20-single-sign-on' ),
				'test'        => 'mosaml_debug_log_enabled_warning',
			);
		} else {
			return array(
				'label'       => __( 'Debug Log Enabled', 'miniorange-saml-20-single-sign-on' ),
				'status'      => 'critical',
				'badge'       => array(
					'label' => __( 'Security', 'miniorange-saml-20-single-sign-on' ),
					'color' => 'red',
				),
				'description' => __( 'Debug logging is currently enabled for the SAML plugin. This may expose sensitive information and should be disabled in production environments.', 'miniorange-saml-20-single-sign-on' ),
				'actions'     => sprintf(
					'<a href="%s"">%s</a>',
					esc_url( admin_url( 'admin.php?page=mo_saml_settings' ) ),
					__( 'Disable Debug Logging', 'miniorange-saml-20-single-sign-on' )
				),
				'test'        => 'mosaml_debug_log_enabled_warning',
			);

		}
	}

	/**
	 * Force update .htaccess file - called during plugin upgrades.
	 * Only creates files if debugging/logging is enabled.
	 * Uses WordPress option to ensure update happens only once.
	 *
	 * @return void
	 */
	public static function mo_saml_force_update_htaccess() {
		if ( ! self::mo_saml_is_debugging_enabled() ) {
			return;
		}

		if ( get_option( 'mo_saml_htaccess_updated', false ) ) {
			return;
		}

		$log_dir = self::get_plugin_debug_log_directory();

		if ( wp_mkdir_p( $log_dir ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			global $wp_filesystem;
			if ( ! WP_Filesystem() ) {
				return;
			}

			$htaccess_content = self::HTACCESS_FILE_CONTENT;

			$file_path = trailingslashit( $log_dir ) . '.htaccess';
			$result    = $wp_filesystem->put_contents( $file_path, $htaccess_content, FS_CHMOD_FILE );

			if ( $result ) {
				update_option( 'mo_saml_htaccess_updated', true );
			}
		}
	}

	// ==================== Legacy methods for backward compatibility ====================

	/**
	 * Check if log file is writable (legacy method).
	 *
	 * @return bool
	 */
	public static function mo_saml_is_log_file_writable() {
		if ( is_dir( self::get_plugin_debug_log_directory() ) ) {
			return wp_is_writable( self::get_plugin_debug_log_directory() );
		}
		return false;
	}

	/**
	 * Initializes directory to write debug logs (legacy method).
	 *
	 * @throws Exception Directory Not created.
	 */
	public static function mo_saml_init() {
		self::mo_saml_create_debug_log_folder_if_not_exists();
	}

	/**
	 * Add a log entry along with the log level (legacy method).
	 *
	 * @param string $log_message log entry message.
	 * @param string $log_level log entry info.
	 */
	public static function mo_saml_add_log( $log_message = '', $log_level = self::INFO ) {
		if ( ! self::mo_saml_is_debugging_enabled() ) {
			return;
		}

		$message = str_replace( array( "\r", "\n", "\t" ), '', rtrim( $log_message ) );

		self::log( $message, $log_level );
	}

	/**
	 * This function is to Log critical errors (legacy method).
	 */
	public static function mo_saml_log_critical_errors() {
		$error = error_get_last();
		if ( $error && in_array(
			$error['type'],
			array(
				E_ERROR,
				E_PARSE,
				E_COMPILE_ERROR,
				E_USER_ERROR,
				E_RECOVERABLE_ERROR,
			),
			true
		) ) {
			self::mo_saml_add_log(
				/* translators: %1$s: message term  %2$s: file term %3$s: line term*/
				sprintf( __( '%1$s in %2$s on line %3$s', 'miniorange-saml-20-single-sign-on' ), $error['message'], $error['file'], $error['line'] ) . PHP_EOL,
				self::CRITICAL
			);
		}
	}

	/**
	 * This function is to get all log files in the log directory (legacy method).
	 *
	 * @return array
	 * @since 3.4.0
	 */
	public static function mo_saml_get_log_files() {
		$files  = is_dir( self::get_plugin_debug_log_directory() ) ? scandir( self::get_plugin_debug_log_directory() ) : '';
		$result = array();
		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $value ) {
				if ( ! in_array( $value, array( '.', '..' ), true ) ) {
					if ( ! is_dir( $value ) && strstr( $value, '.log' ) ) {
						$result[ sanitize_title( $value ) ] = $value;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Deletes all the files in the Log directory older than 7 Days (legacy method).
	 *
	 * @param int $timestamp time.
	 */
	public static function mo_saml_delete_logs_before_timestamp( $timestamp = 0 ) {
		if ( ! $timestamp ) {
			return;
		}
		$log_files = self::mo_saml_get_log_files();
		foreach ( $log_files as $log_file ) {
			$last_modified = filemtime( trailingslashit( self::get_plugin_debug_log_directory() ) . $log_file );
			if ( $last_modified < $timestamp ) {
				@unlink( trailingslashit( self::get_plugin_debug_log_directory() ) . $log_file ); // @codingStandardsIgnoreLine.
			}
		}
	}

	/**
	 * Get the file path of the current log file used by plugins (legacy method).
	 *
	 * @return string|false The log file path or false on failure.
	 */
	public static function mo_saml_get_log_file_path() {
		$result = get_option( self::DEBUG_LOG_FILE_PATH_OPTION_NAME );
		if ( ! empty( $result ) && file_exists( $result ) ) {
			return str_replace( '\\', '/', $result );
		}
		return false;
	}

	/**
	 * To get the log for based on the time (legacy method).
	 */
	public static function mo_saml_get_log_file_name() {
		$result = get_option( self::DEBUG_LOG_FILE_PATH_OPTION_NAME );
		if ( ! empty( $result ) ) {
			return basename( $result );
		}
		return false;
	}

	/**
	 * Used to show the UI part of the log feature to user screen (legacy method).
	 */
	public static function mo_saml_log_page() {
		mo_saml_display_log_page();
	}

	/**
	 * This function is to show admin notices (legacy method).
	 *
	 * @return void
	 */
	public static function mo_saml_admin_notices() {
		self::mo_saml_debug_log_actions();
	}

	/**
	 * This function is to show directory notice (legacy method).
	 *
	 * @return void
	 */
	public static function mo_saml_directory_notice() {
		$msg = esc_html( sprintf( 'Directory %1$s is not writeable, plugin will not able to write the file please update file permission', self::get_plugin_debug_log_directory() ) );
		echo '<div class="error"> <p>' . esc_html( $msg ) . '</p></div>';
	}
}
