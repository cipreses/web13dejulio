<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- cannot change main file's name
/**
 * Plugin Name: SAML Single Sign On – SSO Login
 * Plugin URI: https://miniorange.com/
 * Description: miniOrange SAML plugin allows sso/login using Azure, Azure B2C, Okta, ADFS, Keycloak, Onelogin, Salesforce, Google Apps (Gsuite), Salesforce, Shibboleth, Centrify, Ping, Auth0 and other Identity Providers. It acts as a SAML Service Provider which can be configured to establish a trust between the plugin and IDP to securely authenticate and login the user to WordPress site.
 * Version: 5.4.1
 * Author: miniOrange
 * Author URI: https://miniorange.com/
 * License: Expat
 * License URI: https://plugins.miniorange.com/mit-license
 * Text Domain: miniorange-saml-20-single-sign-on
 *
 * @package miniorange-saml-20-single-sign-on
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MO_SAML_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once 'handlers/class-mo-saml-base-handler.php';
require_once 'handlers/class-mo-saml-test-config-error-handler.php';
require_once 'class-mo-saml-idp-metadata-reader.php';
require_once 'class-mo-saml-login-widget.php';
require_once 'class-mo-saml-login-validate.php';
require_once 'class-mo-saml-customer.php';
require_once 'class-mo-saml-logger.php';
require_once 'mo-saml-settings-page.php';
require_once 'class-mo-saml-utilities.php';
require_once 'class-mo-saml-wp-config-editor.php';
require_once 'handlers/class-mo-saml-user-login-handler.php';
require_once 'notices/class-mo-saml-black-friday-sale.php';
require_once 'handlers/class-mo-saml-register-abilities.php';
/**
 * The Main class of the miniOrange SAML SSO Plugin.
 */
class Mo_SAML_Login {

	/**
	 * The Constructor for the main class. This takes care of initializing all the hooks used by the plugin.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_script' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_style' ) );
		register_activation_hook( __FILE__, array( $this, 'mo_saml_sso_activate' ) );
		add_action( 'admin_init', array( Mo_Saml_Test_Config_Error_Handler::class, 'mo_saml_get_settings_handler' ) );
		add_action( 'admin_init', array( Mo_SAML_Base_Handler::class, 'mo_saml_handle_save_settings' ) );
		add_action( 'admin_init', array( 'Mo_SAML_Logger', 'mo_saml_admin_notices' ) );
		add_action( 'init', array( 'Mo_SAML_Logger', 'mo_saml_force_update_htaccess' ) );
		add_action( 'admin_init', array( $this, 'mo_saml_do_plugin_extension_checks' ) );
		add_action( 'admin_footer', array( $this, 'feedback_request' ) );
		add_action( 'admin_menu', array( $this, 'miniorange_sso_menu' ) );
		add_action( 'admin_notices', array( $this, 'mo_saml_idp_notice' ) );
		add_action( 'login_form', array( $this, 'mo_saml_modify_login_form' ) );
		add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'mo_saml_plugin_action_links' ) );
		add_action( 'plugins_loaded', array( $this, 'mo_saml_load_translations' ) );
		add_action( 'wp_authenticate', array( $this, 'mo_saml_authenticate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'mo_saml_deactivate' ) );
		register_shutdown_function( array( $this, 'log_errors' ) );
		remove_action( 'admin_notices', array( Mo_SAML_Utilities::class, 'mo_saml_error_message' ) );
		remove_action( 'admin_notices', array( Mo_SAML_Utilities::class, 'mo_saml_success_message' ) );
		add_action( 'init', array( Mo_Saml_User_Login_Handler::class, 'mo_saml_handle_login_validate' ) );
		add_action(
			'admin_head',
			function () {
				$screen = get_current_screen();

				if ( $screen && 'toplevel_page_mo_saml_settings' === $screen->id ) {
					add_action( 'admin_notices', array( $this, 'mo_saml_show_multisite_upgrade_notice' ) );
				}
			}
		);
		add_action( 'wp_ajax_mo_saml_dismiss_notice', array( $this, 'mo_saml_dismiss_notice' ) );
		add_action( 'wp_ajax_mo_saml_toggle_sso_button', array( $this, 'mo_saml_toggle_sso_button' ) );
		add_action( 'wp_ajax_mo_saml_get_toggle_sso_nonce', array( $this, 'mo_saml_get_toggle_sso_nonce' ) );

		add_action( 'wp_abilities_api_categories_init', array( Mo_SAML_Register_Abilities::class, 'mo_saml_register_ability_category' ) );
		add_action( 'wp_abilities_api_init', array( Mo_SAML_Register_Abilities::class, 'mo_saml_register_all_abilities' ) );
	}

	/**
	 * Show multisite upgrade notice for free version.
	 */
	public function mo_saml_show_multisite_upgrade_notice() {

		if ( ! is_admin() ) {
			return;
		}

		if ( ! is_multisite() ) {
			return;
		}

		$dismissed_time = get_site_option( 'mo_saml_multisite_notice_dismissed_time', 0 );

		if ( $dismissed_time && ( time() - $dismissed_time ) < 7 * DAY_IN_SECONDS ) {
			return;
		}
		?>

		<div class="mo_saml-upgrade-box" id="mo_saml-multisite-notice">
			<span class="mo_saml-dismiss" id="mo_saml-dismiss-btn">&times;</span>

			<div class="mo_saml-upgrade-title">
				Unlock miniOrange Multisite SAML SSO Support
				<span class="mo_saml-upgrade-tag">Premium Feature</span>
			</div>

			<div class="mo_saml-upgrade-desc">
				Configure SSO once on the Network level and push configurations to selected subsites as needed.
				Includes global metadata management, centralized role/attribute mapping,
				and enterprise-grade support.
			</div>

			<a href="https://portal.miniorange.com/initializePayment?requestOrigin=wp_saml_sso_multisite_basic_plan" target="_blank" class="mo_saml-upgrade-btn">
				Upgrade to Premium
			</a>

			<a href="https://plugins.miniorange.com/wordpress-multisite-single-sign-on-sso-login?utm_source=saml_plugin&utm_medium=multisite_saml_sso_notice&utm_campaign=saml_plugin_internal" target="_blank" class="mo_saml-secondary-btn">
				Know More
			</a>
		</div>

		<script>
			document.getElementById("mo_saml-dismiss-btn").addEventListener("click", function() {
				document.getElementById("mo_saml-multisite-notice").style.display = "none";

				fetch(ajaxurl, {
					method: "POST",
					headers: {"Content-Type": "application/x-www-form-urlencoded"},
					body: "action=mo_saml_dismiss_notice&nonce=<?php echo esc_js( wp_create_nonce( 'mo_saml_dismiss_nonce' ) ); ?>"
				});
			});
		</script>
		<?php
	}

	/**
	 * Dismiss multisite upgrade notice ajax handler.
	 */
	public function mo_saml_dismiss_notice() {
		check_ajax_referer( 'mo_saml_dismiss_nonce', 'nonce' );
		update_site_option( 'mo_saml_multisite_notice_dismissed_time', time() );
		wp_send_json_success();
	}

	/**
	 * Toggle SSO button on WordPress login page via AJAX.
	 */
	public function mo_saml_toggle_sso_button() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'miniorange-saml-20-single-sign-on' ) ) );
		}

		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'mo_saml_toggle_sso_button' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'miniorange-saml-20-single-sign-on' ) ) );
		}

		$action = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : '';

		if ( ! in_array( $action, array( 'enable', 'disable' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid action', 'miniorange-saml-20-single-sign-on' ) ) );
		}

		if ( ! Mo_SAML_Utilities::mo_saml_is_sp_configured() ) {
			wp_send_json_error( array( 'message' => __( 'Please complete Service Provider Configuration first.', 'miniorange-saml-20-single-sign-on' ) ) );
		}

		$sso_button_value = ( 'enable' === $action ) ? 'true' : 'false';
		update_option( Mo_Saml_Options_Enum_Sso_Login::SSO_BUTTON, $sso_button_value );

		$message = ( 'enable' === $action )
			? __( 'SSO button enabled on WordPress login page.', 'miniorange-saml-20-single-sign-on' )
			: __( 'SSO button disabled on WordPress login page.', 'miniorange-saml-20-single-sign-on' );

		wp_send_json_success(
			array(
				'message' => $message,
				'status'  => $sso_button_value,
			)
		);
	}

	/**
	 * Get nonce for SSO button toggle action.
	 */
	public function mo_saml_get_toggle_sso_nonce() {
		wp_send_json_success( array( 'nonce' => wp_create_nonce( 'mo_saml_toggle_sso_button' ) ) );
	}

	/**
	 * This function is responsible for redirecting the admin to the SAML SSO plugin settings after the plugin is activated.
	 *
	 * @param string $plugin The activated plugin's name.
	 * @param bool   $network_wide If the plugin was activated network wide or not.
	 * @return void
	 */
	public function mo_saml_redirect_after_activation( $plugin, $network_wide ) {
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter from the URL for checking if multiple plugins were activated, doesn't require nonce verification. 
		if ( ! isset( $_GET['activate-multi'] ) && 'miniorange-saml-20-single-sign-on/login.php' === $plugin && ! $network_wide ) {
			wp_safe_redirect( self_admin_url() . 'admin.php?page=mo_saml_settings' );
			exit;
		}
	}

	/**
	 * This function enabled the keep setting intact toggle if it is disabled when activating the plugin.
	 */
	public function mo_saml_sso_activate() {
		if ( ! get_option( Mo_Saml_Options_Enum_Sso_Login::MO_SAML_KEEP_SETTINGS_DELETION ) ) {
			add_option( Mo_Saml_Options_Enum_Sso_Login::MO_SAML_KEEP_SETTINGS_DELETION, 'true' );
		}
	}

	/**
	 * Ensures fatal errors are logged so they can be picked up in the status report.
	 *
	 * @since 4.9.09
	 */
	public function log_errors() {
		Mo_SAML_Logger::mo_saml_log_critical_errors();
	}

	/**
	 * Takes care of loading the pot files for translations.
	 *
	 * @return void
	 */
	public function mo_saml_load_translations() {
		$locale = determine_locale();
		$mofile = plugin_dir_path( __FILE__ ) . 'resources/lang/miniorange-saml-20-single-sign-on-' . $locale . '.mo';

		if ( file_exists( $mofile ) ) {
			load_textdomain( 'miniorange-saml-20-single-sign-on', $mofile );
		}
	}
	/**
	 * Function to display the notice on the specific pages.
	 */
	public function mo_saml_do_plugin_extension_checks() {
		$valid_pages = 'mo_saml_settings';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading page parameter.
		if ( ! ( ! empty( $_GET['page'] ) && ( $_GET['page'] === $valid_pages ) ) && current_user_can( 'manage_options' ) ) {
			add_action( 'admin_notices', array( $this, 'mo_saml_show_disabled_extension_notice' ) );
		}
	}

	/**
	 * Function shows a notice incase any required extensions are not enabled.
	 */
	public function mo_saml_show_disabled_extension_notice() {
		$disable_extension = Mo_SAML_Utilities::mo_saml_get_disabled_extensions();

		if ( ! empty( $disable_extension ) ) {
			$extension_display_line = implode( ', ', $disable_extension );
			echo '
			<div class="wrap">
            <div class="notice notice-warning mo-saml-trial-notice-banner">
                <div class="mo-saml-notice-content">
                    <img src="' . esc_attr( plugin_dir_url( __FILE__ ) ) . 'images/miniorange_logo.webp" class="mo-saml-logo">
                    <span class="mo-saml-warning-text">
                        <span class="mo-saml-warning-title">' . esc_html__( 'Warning:', 'miniorange-saml-20-single-sign-on' ) . '</span> ' . sprintf(
							/* translators: %s: List of PHP extension names */
						esc_html__( 'Following PHP extensions (%s) are disabled which are important for SSO configuration. Please enable these extensions to continue using SSO on your site.', 'miniorange-saml-20-single-sign-on' ),
						'<i class="mo-saml-extension-list">' . esc_html( $extension_display_line ) . '</i>'
					) . '
                    </span>
                </div>
				</div>
            </div>';
		}
	}

	/**
	 * Displays the feedback form upon plugin deactivation.
	 *
	 * @return void
	 */
	public function feedback_request() {
		mo_saml_display_saml_feedback_form();
	}

	/**
	 * The callback function for the plugin menu page. This is the starting point for displaying the plugin settings.
	 *
	 * @return void
	 */
	public function mo_login_widget_saml_options() {
		global $wpdb;

		mo_saml_register_saml_sso();
	}

	/**
	 * Takes care of cleaning some logger constant and welcome page flag once the plugin is deactivated.
	 *
	 * @return void
	 */
	public function mo_saml_deactivate() {
		delete_option( Mo_Saml_Options_Enum::NEW_USER );
		if ( ! Mo_SAML_Utilities::mo_saml_is_curl_installed() ) {
			return;
		}

		$site_home_path   = ABSPATH;
		$wp_config_path   = $site_home_path . 'wp-config.php';
		$wp_config_editor = new Mo_SAML_WP_Config_Editor( $wp_config_path );

		if ( wp_is_writable( $wp_config_path ) ) {
			$wp_config_editor->mo_saml_wp_config_update( 'MO_SAML_LOGGING', 'false' );
		}
		wp_safe_redirect( 'plugins.php' );
	}

	/**
	 * Enqueues all the css files required by the plugin
	 *
	 * @param string $page Contains the value of the page parameter from the URL along with its level and is used to make sure the css is loaded only where it is required.
	 * @return void
	 */
	public function plugin_settings_style( $page ) {
		wp_enqueue_style( 'mo_saml_notice_style', plugins_url( 'includes/css/notice.min.css', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, 'all' );
		wp_enqueue_style( 'mo_saml_black_friday_sale_style', plugins_url( 'includes/css/black-friday-sale-banner.min.css', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, 'all' );
		if ( 'toplevel_page_mo_saml_settings' !== $page && 'miniorange-saml-2-0-sso_page_mo_saml_enable_debug_logs' !== $page && 'miniorange-saml-2-0-sso_page_mo_saml_abilities_api' !== $page ) {
			return;
		} else {
			wp_enqueue_style( 'mo_saml_bootstrap_css', plugins_url( 'includes/css/bootstrap/mo-saml-bootstrap.min.css', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, 'all' );
			wp_enqueue_style( 'mo_saml_jquery_ui_style', plugins_url( 'includes/css/jquery-ui.min.css', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, 'all' );
			wp_enqueue_style( 'mo_saml_admin_settings_style', plugins_url( 'includes/css/style_settings.min.css', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, 'all' );
			wp_enqueue_style( 'mo_saml_admin_settings_phone_style', plugins_url( 'includes/css/phone.min.css', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, 'all' );
			wp_enqueue_style( 'mo_saml_time_settings_style', plugins_url( 'includes/css/datetime-style-settings.min.css', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, 'all' );
			wp_enqueue_style( 'mo_saml_wpb-fa', plugins_url( 'includes/css/style-icon.min.css', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, 'all' );
		}
	}

	/**
	 * Enqueues all the js files required by the plugin
	 *
	 * @param string $page Contains the value of the page parameter from the URL along with its level and is used to make sure the js is loaded only where it is required.
	 * @return void
	 */
	public function plugin_settings_script( $page ) {
		global $wp_version;
		if ( version_compare( $wp_version, '6.3', '>=' ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			wp_enqueue_script( 'mo_saml_command_palette', plugins_url( 'includes/js/command-palette.js', __FILE__ ), array( 'wp-data', 'wp-i18n' ), Mo_Saml_Options_Plugin_Constants::VERSION, true );
			wp_localize_script(
				'mo_saml_command_palette',
				'moSamlCommandPalette',
				array(
					'adminUrl' => admin_url(),
					'siteUrl'  => site_url(),
					'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				)
			);
		}
		if ( 'toplevel_page_mo_saml_settings' === $page || 'miniorange-saml-2-0-sso_page_mo_saml_enable_debug_logs' === $page || 'miniorange-saml-2-0-sso_page_mo_saml_abilities_api' === $page ) {
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'mo_fmo_saml_selected_idp_divct2_script', plugins_url( 'includes/js/select2.min.js', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, false );
			wp_enqueue_script( 'mo_saml_timepicker_script', plugins_url( 'includes/js/jquery.timepicker.min.js', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, false );
			wp_enqueue_script( 'mo_saml_admin_settings_script', plugins_url( 'includes/js/settings.min.js', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, false );
			wp_enqueue_script( 'mo_saml_admin_settings_phone_script', plugins_url( 'includes/js/phone.min.js', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, false );
		}
		wp_enqueue_script( 'mo_saml_notice_script', plugins_url( 'includes/js/notice.min.js', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, false );
		wp_enqueue_script( 'mo_saml_black_friday_sale_banner_script', plugins_url( 'includes/js/black-friday-sale-banner.min.js', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, false );
	}

	/**
	 * This function is responsible for adding the SSO button on the WordPress login page.
	 *
	 * @return void
	 */
	public function mo_saml_modify_login_form() {
		$sso_button = get_option( Mo_Saml_Options_Enum_Sso_Login::SSO_BUTTON );
		if ( 'false' !== $sso_button && Mo_SAML_Utilities::mo_saml_is_sp_configured() ) {
			$this->mo_saml_add_sso_button();
		}
	}

	/**
	 * Renders the SSO button for the configured IDP.
	 *
	 * @return void
	 */
	public function mo_saml_add_sso_button() {
		if ( ! is_user_logged_in() ) {
			$saml_idp_name      = get_option( Mo_Saml_Options_Enum_Service_Provider::IDENTITY_NAME );
			$custom_button_text = isset( $saml_idp_name ) ? 'Login with ' . $saml_idp_name : 'Login with SSO';
			wp_enqueue_script( 'mo_saml_login_button_script', plugins_url( 'includes/js/sso_button.min.js', __FILE__ ), array(), Mo_Saml_Options_Plugin_Constants::VERSION, false );
			echo '
                <input id="saml_user_login_input" type="hidden" name="option" value="">
                <div id="mo_saml_button" style="height:88px;">
                	<div id="mo_saml_login_sso_button" style="width:100%;display:flex;justify-content:center;align-items:center;font-size:14px;margin-bottom:1.3rem" class="button button-primary">
                    <img style="width:20px;height:15px;padding-right:1px" src="' . esc_url( Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() ) . 'images/lock-icon.webp">' . esc_html( $custom_button_text ) . '
                	</div>
                	<div style="padding:5px;font-size:14px;height:20px;text-align:center"><b>OR</b></div>
            	</div>';
		}
	}

	/**
	 * Takes care of displaying IDP specific solutions and add-ons in the admin notice bar.
	 *
	 * @return void
	 */
	public function mo_saml_idp_notice() {
		$mo_date_current_notice = gmdate( 'Y-m-d' );

		if ( isset( $_POST['mo_idp_close_notice_nonce'] ) && check_admin_referer( 'mo_idp_close_notice', 'mo_idp_close_notice_nonce' ) ) {
			$mo_date_expire_notice = gmdate( 'Y-m-d', strtotime( $mo_date_current_notice . '+7 day' ) );
			update_option( Mo_Saml_Sso_Constants::MO_SAML_EXPIRE_NOTICE, $mo_date_expire_notice );
			update_option( Mo_Saml_Sso_Constants::MO_SAML_CLOSE_NOTICE, 1 );
		}
		$mo_saml_identity_provider_identifier_name = get_option( Mo_Saml_Options_Enum_Service_Provider::IDENTITY_PROVIDER_NAME );
		?>
		<input type="hidden" name="mo_saml_identity_provider_identifier" id="mo_saml_identity_provider_identifier" value="<?php echo esc_attr( $mo_saml_identity_provider_identifier_name ); ?>" />
		<input type="hidden" name="idp_specific" id="idp_specific" value='<?php echo esc_attr( wp_json_encode( Mo_Saml_Options_Plugin_Idp::$idp_list ) ); ?>' />
		<?php
		$display = 'none';
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter from the URL for checking tab name, doesn't require nonce verification.
		if ( ! empty( Mo_Saml_Options_Plugin_Idp::$idp_list[ $mo_saml_identity_provider_identifier_name ] ) && ( ! isset( $_GET['tab'] ) || 'addons' !== $_GET['tab'] ) ) {
			$display = 'block';
		}

		if ( $mo_date_current_notice < get_option( Mo_Saml_Sso_Constants::MO_SAML_EXPIRE_NOTICE ) && ! empty( get_option( Mo_Saml_Sso_Constants::MO_SAML_CLOSE_NOTICE ) ) && get_option( Mo_Saml_Sso_Constants::MO_SAML_CLOSE_NOTICE ) ) {
			$display = 'none';
		}

		if ( current_user_can( 'manage_options' ) ) {
			mo_saml_display_plugin_notice( $display );
		}
	}

	/**
	 * Adds the menu and submenu for the miniOrange SAML SSO plugin.
	 *
	 * @return void
	 */
	public function miniorange_sso_menu() {
		$slug = 'mo_saml_settings';
		add_menu_page(
			'MO SAML Settings ' . __( 'Configure SAML Identity Provider for SSO', 'miniorange-saml-20-single-sign-on' ),
			'miniOrange SAML 2.0 SSO',
			'manage_options',
			$slug,
			array(
				$this,
				'mo_login_widget_saml_options',
			),
			Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() . 'images/miniorange.webp'
		);
		add_submenu_page(
			$slug,
			'miniOrange SAML 2.0 SSO',
			__( 'Plugin Configuration', 'miniorange-saml-20-single-sign-on' ),
			'manage_options',
			'mo_saml_settings',
			array( $this, 'mo_login_widget_saml_options' )
		);
		add_submenu_page(
			$slug,
			'miniOrange SAML 2.0 SSO',
			'<div style="color:orange" id="mo_saml_pricing_menu"><img src="' . esc_url( Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() ) . 'images/premium_plans_icon.webp" style="height:10px;width:12px"> ' . __( 'Premium Plans', 'miniorange-saml-20-single-sign-on' ) . '</div>',
			'manage_options',
			'mo_saml_settings',
			array( $this, 'mo_login_widget_saml_options' )
		);
		add_submenu_page(
			$slug,
			'miniOrange SAML 2.0 SSO',
			'<div id="mo_saml_addons_submenu">' . __( 'Add-Ons', 'miniorange-saml-20-single-sign-on' ) . '</div>',
			'manage_options',
			'mo_saml_settings&tab=addons',
			array( $this, 'mo_login_widget_saml_options' )
		);
		add_submenu_page(
			$slug,
			'miniOrange SAML 2.0 SSO',
			'<div id="mo_saml_troubleshoot">' . __( 'Troubleshoot', 'miniorange-saml-20-single-sign-on' ) . '</div>',
			'manage_options',
			'mo_saml_enable_debug_logs',
			array( 'Mo_SAML_Logger', 'mo_saml_log_page' )
		);
		add_submenu_page(
			$slug,
			'miniOrange SAML 2.0 SSO',
			'<div id="mo_saml_abilities_api">' . __( 'Abilities API', 'miniorange-saml-20-single-sign-on' ) . ' <span class="mo-saml-new-badge">NEW</span></div>',
			'manage_options',
			'mo_saml_abilities_api',
			array( 'Mo_SAML_Register_Abilities', 'mo_saml_abilities_api_page' )
		);
	}

	/**
	 * Handles redirection of a logged in user.
	 *
	 * @return void
	 */
	public function mo_saml_authenticate() {
		$redirect_to = '';
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter from the URL for checking the redirect_to parameter, doesn't require nonce verification.
		if ( isset( $_GET['redirect_to'] ) ) {
			//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter from the URL for checking the redirect_to parameter, doesn't require nonce verification.
			$redirect_to = esc_url_raw( wp_unslash( $_GET['redirect_to'] ) );
		}

		if ( is_user_logged_in() ) {
			$this->mo_saml_login_redirect( $redirect_to );
		}
	}

	/**
	 * Redirects the user to the redirect_to parameter after SSO.
	 *
	 * @param string $redirect_to The redirect_to query parameter from the URL.
	 * @return void
	 */
	public function mo_saml_login_redirect( $redirect_to ) {
		$is_admin_url = false;

		if ( strcmp( admin_url(), $redirect_to ) === 0 || strcmp( wp_login_url(), $redirect_to ) === 0 ) {
			$is_admin_url = true;
		}

		if ( ! empty( $redirect_to ) && ! $is_admin_url ) {
			wp_safe_redirect( $redirect_to );
		} else {
			wp_safe_redirect( site_url() );
		}
		exit();
	}

	/**
	 * Provides additional links for Settings and Premium Plans for the plugin listed under the Installed Plugins section.
	 *
	 * @param array $links The default links provided by WordPress for Settings and Deactivate.
	 * @return array
	 */
	public function mo_saml_plugin_action_links( $links ) {

		$settings_link = array( '<a href="' . esc_url( admin_url( 'admin.php?page=mo_saml_settings' ) ) . '">' . __( 'Settings', 'miniorange-saml-20-single-sign-on' ) . '</a>' );
		$license_link  = '<a href="' . Mo_Saml_External_Links::LANDING_PAGE . '?utm_source=saml_plugin&utm_medium=upgrade_button&utm_campaign=saml_plugin_internal#pricing" target="_blank">' . esc_html__( 'Premium Plans', 'miniorange-saml-20-single-sign-on' ) . '</a>';

		$links = array_merge( $settings_link, $links );

		array_push(
			$links,
			$license_link
		);
		return $links;
	}
}
new Mo_SAML_Login();
