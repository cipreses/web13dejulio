<?php
/**
 * File to handle SAML response, generate saml request from WP widget
 *
 * @package miniorange-saml-20-single-sign-on
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once __DIR__ . '/includes/lib/class-mo-saml-options-enum.php';
require_once __DIR__ . '/class-mo-saml-utilities.php';

/**
 * Class to create WordPress widget
 */
class Mo_SAML_Login_Widget extends WP_Widget {

	/**
	 * Initialize mo_login_wid
	 */
	public function __construct() {
		parent::__construct(
			'Saml_Login_Widget',
			'Login with ' . esc_html( get_option( Mo_Saml_Options_Enum_Service_Provider::IDENTITY_NAME ) ),
			array(
				'description'                 => esc_html__( 'This is a miniOrange SAML login widget.', 'miniorange-saml-20-single-sign-on' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Widget UI
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$wid_title = '';
		if ( ! empty( $instance['wid_title'] ) ) {
			$wid_title = $instance['wid_title'];
		}
		$wid_title = apply_filters( 'widget_title', $wid_title );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['before_widget'] is html that needs to render on dom escaping will not render html.
		echo $args['before_widget'];
		if ( ! empty( $wid_title ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['before_widget'] and $args['after_title'] is html that needs to render on dom escaping will not render html.
			echo $args['before_title'] . esc_html( $wid_title ) . $args['after_title'];
		}
		$this->loginForm();
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $args['after_widget'] is html that needs to render on dom escaping will not render html.
		echo $args['after_widget'];
	}

	/**
	 * MiniOrange method to override parent method
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance              = array();
		$instance['wid_title'] = isset( $new_instance['wid_title'] ) ? sanitize_text_field( $new_instance['wid_title'] ) : '';
		return $instance;
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @param array $instance Current settings.
	 * @return void
	 */
	public function form( $instance ) {
		$wid_title = '';
		if ( ! empty( $instance['wid_title'] ) ) {
			$wid_title = $instance['wid_title'];
		}
		echo '
		<p><label for="' . esc_attr( $this->get_field_id( 'wid_title' ) ) . ' ">' . esc_html_e( 'Title:', 'miniorange-saml-20-single-sign-on' ) . ' </label>
		<input class="widefat" id="' . esc_attr( $this->get_field_id( 'wid_title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'wid_title' ) ) . '" type="text" value="' . esc_attr( $wid_title ) . '" />
		</p>';
	}

	/**
	 * Outputs SSO Login & Logout Buttons in form of a WordPress Widget.
	 *
	 * @return void
	 */
	public function loginForm() {
		if ( ! is_user_logged_in() ) {
			$identity_provider     = get_option( Mo_Saml_Options_Enum_Service_Provider::IDENTITY_NAME );
			$saml_x509_certificate = get_option( Mo_Saml_Options_Enum_Service_Provider::X509_CERTIFICATE );

			if ( ! empty( $identity_provider ) && ! empty( $saml_x509_certificate ) ) {
				?>
				<form name="miniorange-saml-sp-sso-login-form" id="miniorange-saml-sp-sso-login-form" method="post" action="" style="display:inline;">
					<input type="hidden" name="option" value="saml_user_login" />
					<a href="#" onclick="document.getElementById('miniorange-saml-sp-sso-login-form').submit(); return false;">
						<?php echo esc_html( 'Login with ' . $identity_provider ); ?>
					</a>
				</form>
				<?php
			} else {
				echo '<p>' . esc_html__( 'Please configure the miniOrange SAML Plugin first.', 'miniorange-saml-20-single-sign-on' ) . '</p>';
			}

			if ( ! Mo_SAML_Utilities::mo_saml_check_empty_or_null( array( get_option( Mo_Saml_Sso_Constants::MO_SAML_REDIRECT_ERROR ) ) ) ) {
				echo '<div title="Login Error" style="color:red;">' . esc_html__( 'We could not sign you in. Please contact your Administrator.', 'miniorange-saml-20-single-sign-on' ) . '</div>';
				delete_option( Mo_Saml_Sso_Constants::MO_SAML_REDIRECT_ERROR );
				delete_option( Mo_Saml_Sso_Constants::MO_SAML_REDIRECT_ERROR_REASON );
			}
		} else {
			$current_user = wp_get_current_user();
			/* translators: %s: user's display name */
			$link_with_username = sprintf( __( 'Hello, %s', 'miniorange-saml-20-single-sign-on' ), $current_user->display_name );
			echo esc_html( $link_with_username );
			?>
			| <a href="<?php echo esc_url( wp_logout_url( Mo_SAML_Utilities::mo_saml_get_current_page_url() ) ); ?>" title="<?php esc_attr_e( 'Logout', 'miniorange-saml-20-single-sign-on' ); ?>"><?php esc_html_e( 'Logout', 'miniorange-saml-20-single-sign-on' ); ?></a>
			<?php
		}
	}
}

add_action(
	'widgets_init',
	function () {
		register_widget( 'Mo_SAML_Login_Widget' );}
);
