<?php
/**
 * File to display sections of Rederection & SSO Links tab.
 *
 * @package miniorange-saml-20-single-sign-on\views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function to display Redirection & SSO Links tab.
 *
 * @return void
 */
function mo_saml_general_login_page() {

	//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter from the URL for checking tab name, doesn't require nonce verification.
	$active_subtab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'sso-links';
	if ( ! in_array( $active_subtab, array( 'redirection', 'sso-links' ), true ) ) {
		$active_subtab = 'sso-links';
	}

	$add_sso_button = get_option( Mo_Saml_Options_Enum_Sso_Login::SSO_BUTTON );
	if ( Mo_SAML_Utilities::mo_saml_is_sp_configured() && empty( $add_sso_button ) ) {
		update_option( Mo_Saml_Options_Enum_Sso_Login::SSO_BUTTON, 'true' );
		$add_sso_button = 'true';
	}
	?>
	<div class="mo-saml-bootstrap-row mo-saml-bootstrap-container-fluid" id="redir-sso-tab-form">
		<div class="mo-saml-bootstrap-col-md-8 mo-saml-bootstrap-mt-4 mo-saml-bootstrap-ms-5">
			
		<?php mo_saml_display_redirection_subtabs( $active_subtab ); ?>
		<div class="mo-saml-subtab-content">
		<?php if ( 'redirection' === $active_subtab ) : ?>
					<div class="mo-saml-subtab-pane" id="mo-saml-redirection-subtab">
						<?php
						mo_saml_display_auto_redirection_config();
						mo_saml_display_redirect_from_wp_login_config();
						?>
					</div>
				<?php else : ?>
					<div class="mo-saml-subtab-pane" id="mo-saml-link-button-subtab">
						<?php
						mo_saml_display_sso_button_config( $add_sso_button );
						mo_saml_display_sso_links_config();
						mo_saml_display_widget_config();
						mo_saml_display_shortcode_config();
						mo_saml_display_relaystate_config();
						?>
					</div>
				<?php endif; ?>
			</div>
			</div>
			<?php mo_saml_display_support_form(); ?>
		</div>
	<?php
}

/**
 * Function to display subtabs for Redirection & SSO Links.
 *
 * @param string $active_subtab The currently active subtab.
 * @return void
 */
function mo_saml_display_redirection_subtabs( $active_subtab ) {
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$current_url = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	} else {
		$current_url = '';
	}
	$redirection_url     = add_query_arg( array( 'tab' => 'redirection' ), $current_url );
	$sso_link_button_url = add_query_arg( array( 'tab' => 'sso-links' ), $current_url );
	?>
	<div class="mo-saml-subtabs">
		<div class="mo-saml-redirection-subtabs-nav mo-saml-bootstrap-d-flex">
			<a href="<?php echo esc_url( $redirection_url ); ?>" class="mo-saml-subtab-nav <?php echo esc_attr( 'redirection' === $active_subtab ? 'mo-saml-subtab-nav-active' : '' ); ?> mo-saml-bootstrap-me-2  mo-saml-bootstrap-pe-2 mo-saml-bootstrap-ps-2">
				<?php esc_html_e( 'SSO Redirection Settings', 'miniorange-saml-20-single-sign-on' ); ?> <svg style="margin:-4px auto;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 520 480" fill="none">
					<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
					<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
					<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
					<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
					<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
					<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
					</svg>
			</a>
			<a href="<?php echo esc_url( $sso_link_button_url ); ?>" class="mo-saml-subtab-nav <?php echo esc_attr( 'sso-links' === $active_subtab ? 'mo-saml-subtab-nav-active' : '' ); ?>">
				<?php esc_html_e( 'SSO Links and Button', 'miniorange-saml-20-single-sign-on' ); ?>
			</a>
		</div>
	</div>
	<?php
}

/**
 * Function to display the SSO button configurations.
 *
 * @param bool $add_sso_button_wp it adds SSO button according to configurations.
 * @return void
 */
function mo_saml_display_sso_button_config( $add_sso_button_wp ) {
	?>
	<div class="mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white mo-saml-bootstrap-rounded">
		<div class="mo-saml-bootstrap-row align-items-top">
			<div class="mo-saml-bootstrap-col-md-12">
				<h4 class="form-head">
					<span class="entity-info"><?php esc_html_e( 'Option 1: Use a Single Sign-On button', 'miniorange-saml-20-single-sign-on' ); ?>
						<a href="https://developers.miniorange.com/docs/saml/wordpress/Redirection-SSO?utm_source=saml_plugin&utm_medium=redirection_single_sign_on_button&utm_campaign=saml_plugin_internal#Login-button" rel="noopener noreferrer" class="mo-saml-bootstrap-text-dark" target="_blank">
							<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
								<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path>
								<path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"></path>
							</svg>
						</a>
					</span>
				</h4>
			</div>
		</div>
		<div class="mo-saml-bootstrap-row mo-saml-bootstrap-align-items-center mo-saml-bootstrap-mt-3">
			<div class="mo-saml-bootstrap-col-md-9 mo_saml_width_fit" >
				<h6><?php esc_html_e( 'Add a Single Sign-On button on the WordPress login page', 'miniorange-saml-20-single-sign-on' ); ?></h6>
			</div>
			<div class="mo-saml-bootstrap-col-md-3">
				<form id="mo_saml_add_sso_button_wp_form" method="post" action="">
					<?php wp_nonce_field( 'mo_saml_add_sso_button_wp_option' ); ?>
					<input type="hidden" name="option" value="mo_saml_add_sso_button_wp_option" />
					<input type="checkbox" id="switch-sso-btn" name="mo_saml_add_sso_button_wp" <?php checked( 'true' === $add_sso_button_wp ); ?> class="mo-saml-switch mo-saml-bootstrap-mt-4" onchange="document.getElementById('mo_saml_add_sso_button_wp_form').submit();" value="true" />
					<label class="mo-saml-switch-label" for="switch-sso-btn" title="<?php esc_attr_e( 'You can only add a Single Sign On button after saving your Service Provider Settings.', 'miniorange-saml-20-single-sign-on' ); ?>"></label>
				</form>
			</div>
		<p>
			<div class="mo-saml-url-info-text">
				<small class="mo-saml-bootstrap-text-muted">
					<?php esc_html_e( 'Enabling this toggle will add a SSO Login button to the following URL:', 'miniorange-saml-20-single-sign-on' ); ?>
				</small>
				<code class="mo-saml-url-box bg-cstm mo-saml-bootstrap-text-dark mo-saml-bootstrap-rounded">
					<span id="wp_login_url" target="_blank"><?php echo esc_html( wp_login_url() ); ?></span>
				</code>
			</div>
		</p>
	</div>
		
		<div id="mo-saml-sso-button-customize-content">
		<div class="prem-info mo-saml-bootstrap-mt-3 mo-saml-padding-btm-0">
			<div class="mo-saml-flex">
			<h5 class="form-head form-head-bar mo-saml-bootstrap-mt-2 mo-customize-btn-head"><?php esc_html_e( 'Customize Single Sign-On Button', 'miniorange-saml-20-single-sign-on' ); ?></h5>
			<div class="prem-icn sso-btn-prem-img-sso" ><svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
				
				<p class=" prem-info-text"><?php esc_html_e( 'Customization of SSO/Login button is available in Premium, Enterprise and All-Inclusive versions of the plugin', 'miniorange-saml-20-single-sign-on' ); ?> <a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '?utm_source=saml_plugin&utm_medium=customize_sso_button&utm_campaign=saml_plugin_internal#pricing' ); ?>" target="_blank" class="mo-saml-bootstrap-text-warning"><?php esc_html_e( 'Click here to upgrade', 'miniorange-saml-20-single-sign-on' ); ?></a></p>
			</div>
			<a href="javascript:void(0);" onclick="moSamlToggleSSOButtonCustomize()" class="mo-saml-sso-customize-toggle-link" title="<?php esc_attr_e( 'Customize Single Sign-On Button', 'miniorange-saml-20-single-sign-on' ); ?>">
					<span id="mo-saml-sso-button-toggle-icon"><span id="mo-saml-sso-button-toggle-text"><?php esc_html_e( 'View More', 'miniorange-saml-20-single-sign-on' ); ?></span> <span id="mo-saml-drop-dwn"><svg height="15" viewBox="0 0 24 20" width="20" xmlns="http://www.w3.org/2000/svg" id="fi_2722987"><g id="_16" data-name="16"><path d="m12 16a1 1 0 0 1 -.71-.29l-6-6a1 1 0 0 1 1.42-1.42l5.29 5.3 5.29-5.29a1 1 0 0 1 1.41 1.41l-6 6a1 1 0 0 1 -.7.29z"></path></g></svg></span></span>
				</a>
			</div>
			<div>
			<p id="mo-saml-sso-button-hint"><i><?php esc_html_e( 'Click "View More" to customize button appearance, colors, size, and text options.', 'miniorange-saml-20-single-sign-on' ); ?></i></p>
			</div>
			<div id="mo-saml-sso-button-customize-table">
			<table class="mo-saml-bootstrap-w-100 mo-saml-bootstrap-mt-4">
				<tbody>
					<tr>
						<td>
							<b><?php esc_html_e( 'Shape', 'miniorange-saml-20-single-sign-on' ); ?></b>
						</td>
						<td>
							<b><?php esc_html_e( 'Theme', 'miniorange-saml-20-single-sign-on' ); ?></b>
						</td>
						<td>
							<b><?php esc_html_e( 'Size of the Button', 'miniorange-saml-20-single-sign-on' ); ?></b>
						</td>
					</tr>
					<tr>
						<td class="mo-saml-padding-block">
							<input type="radio" name="mo_saml_button_theme" class="mo-saml-bootstrap-d-inline-block" value="circle" disabled=""> <?php esc_html_e( 'Round', 'miniorange-saml-20-single-sign-on' ); ?>
						</td>
						<td>
							<table>
								<tbody>
									<tr class="mo-saml-padding-block">
										<td><?php esc_html_e( 'Button Color:', 'miniorange-saml-20-single-sign-on' ); ?></td>
										<td>
											<input type="text" name="mo_saml_button_color" class="color mo-saml-bootstrap-ms-2 mo-saml-bootstrap-text-white" value="#135e96" style="background-color: #135e96" disabled>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
						<td>
							<table>
								<tbody>
									<tr class="mo-saml-padding-block">
										<td><?php esc_html_e( 'Width:', 'miniorange-saml-20-single-sign-on' ); ?> </td>
										<td><input class="mo-saml-btn-size" type="text" name="mo_saml_button_width" value="200" disabled=""></td>
										<td><input type="button" class="button button-primary" value="-" disabled=""></td>
										<td><input type="button" class="button button-primary" value="+" disabled=""></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
					<tr>
						<td class=" mo-saml-padding-block">
							<input type="radio" name="mo_saml_button_theme" class="mo-saml-bootstrap-d-inline-block" value="oval" checked="" disabled=""><?php esc_html_e( 'Rounded Edges', 'miniorange-saml-20-single-sign-on' ); ?>
						</td>
						<td>
							<table>
								<tbody>
									<tr class="mo-saml-padding-block">
										<td><?php esc_html_e( 'Button Text:', 'miniorange-saml-20-single-sign-on' ); ?> </td>
										<td>
											<input class="mo-saml-bootstrap-ms-3 mo-saml-bootstrap-bg-light" type="text" name="mo_saml_button_text" value="<?php esc_attr_e( 'Login with #IDP#', 'miniorange-saml-20-single-sign-on' ); ?>" disabled="">
										</td>
									</tr>
								</tbody>
							</table>
						</td>
						<td>
							<table>
								<tbody>
									<tr class="longButton mo-saml-padding-block">
										<td><?php esc_html_e( 'Height:', 'miniorange-saml-20-single-sign-on' ); ?> </td>
										<td><input class="mo-saml-btn-size" type="text" name="mo_saml_button_height" value="50" disabled=""></td>
										<td><input type="button" class="button button-primary" value="-" disabled=""></td>
										<td><input type="button" class="button button-primary" value="+" disabled=""></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
					<tr>
						<td class="mo-saml-padding-block">
							<input type="radio" name="mo_saml_button_theme" class="mo-saml-bootstrap-d-inline-block" value="square" disabled=""> <?php esc_html_e( 'Square', 'miniorange-saml-20-single-sign-on' ); ?>
						</td>
						<td>
							<table>
								<tbody>
									<tr class="mo-saml-padding-block">
										<td><?php esc_html_e( 'Font Color:', 'miniorange-saml-20-single-sign-on' ); ?></td>
										<td>
											<input type="text" name="mo_saml_font_color" class="color mo-saml-bootstrap-ms-4" value="#ffffff" disabled="">
										</td>
									</tr>
								</tbody>
							</table>
						</td>
						<td>
							<table>
								<tbody>
									<tr class="longButton mo-saml-padding-block">
										<td><?php esc_html_e( 'Curve:', 'miniorange-saml-20-single-sign-on' ); ?> </td>
										<td><input class="mo-saml-btn-size" type="text" name="mo_saml_button_curve" value="5" disabled=""></td>
										<td><input type="button" class="button button-primary" value="-" disabled=""></td>
										<td><input type="button" class="button button-primary" value="+" disabled=""></td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
					<tr>
						<td class=" mo-saml-padding-block">
							<input type="radio" name="mo_saml_button_theme" class="mo-saml-bootstrap-d-inline-block" disabled=""> <?php esc_html_e( 'Long Button with Text', 'miniorange-saml-20-single-sign-on' ); ?>
						</td>
						<td>
							<table>
								<tbody>
									<tr class="mo-saml-padding-block">
										<td><?php esc_html_e( 'Font Size:', 'miniorange-saml-20-single-sign-on' ); ?></td>
										<td>
											<table>
												<tbody>
													<tr>
														<td><input type="text" class="mo-saml-btn-size mo-saml-bootstrap-ms-4" name="mo_saml_font_size" value="20" disabled=""></td>
														<td><input type="button" class="button button-primary" value="-" disabled=""></td>
														<td><input type="button" class="button button-primary" value="+" disabled=""></td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
			</div>
		</div>
		</div>
	</div>
	<?php
}

/**
 * Function to configure the login widget to be displayed.
 *
 * @return void
 */
function mo_saml_display_widget_config() {
	$theme_supports_widgets = current_theme_supports( 'widgets' );
	?>
	<div class="mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white mo-saml-bootstrap-rounded mo-saml-bootstrap-mt-4">
		<div class="mo-saml-bootstrap-row align-items-top">
			<div class="mo-saml-bootstrap-col-md-12">
				<h4 class="form-head">
					<span class="entity-info"><?php esc_html_e( 'Option 3: Use a Widget', 'miniorange-saml-20-single-sign-on' ); ?>
						<a href="https://developers.miniorange.com/docs/saml/wordpress/Redirection-SSO?utm_source=saml_plugin&utm_medium=sso_links_docs&utm_campaign=saml_plugin_internal#SSO-Links" rel="noopener noreferrer" class="mo-saml-bootstrap-text-dark" target="_blank">
							<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
								<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path>
								<path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"></path>
							</svg>
						</a>
					</span>
				</h4>
			</div>
		</div>
		<?php if ( ! $theme_supports_widgets ) : ?>
			<div class="mo-saml-widget-theme-notice">
				<p>
					<strong>⚠️ <?php esc_html_e( 'Widget Feature Not Supported by Current Theme', 'miniorange-saml-20-single-sign-on' ); ?></strong>
					<?php esc_html_e( 'Your current theme doesn\'t support widgets. You can use the SSO links above to add SSO functionality to your site.', 'miniorange-saml-20-single-sign-on' ); ?>
				</p>
			</div>
		<?php endif; ?>
		<div class="mo-saml-widget-content" <?php echo ! $theme_supports_widgets ? 'style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
			<h6 class="mo-saml-bootstrap-mt-4"><?php esc_html_e( 'Add the SSO Widget by following the instructions below. This will add the SSO link on your site.', 'miniorange-saml-20-single-sign-on' ); ?></h6>
			<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-4">
				<ol>
					<li><?php esc_html_e( 'Go to Appearances', 'miniorange-saml-20-single-sign-on' ); ?> &gt; <a href="<?php echo esc_url( get_admin_url() . 'widgets.php' ); ?>"><?php esc_html_e( 'Widgets.', 'miniorange-saml-20-single-sign-on' ); ?></a></li>
					<li><?php esc_html_e( 'Click on Add Block ("+" sign) at the top left corner, besides the heading Widgets.', 'miniorange-saml-20-single-sign-on' ); ?></li>
					<li><?php esc_html_e( 'In the search box, search for "Login with ", and drag and drop this block to your favourite location.', 'miniorange-saml-20-single-sign-on' ); ?></li>
					<li><?php esc_html_e( 'Click on the "Update" button at the top right to save the widget settings.', 'miniorange-saml-20-single-sign-on' ); ?></li>
				</ol>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Function to display the SSO links configuration.
 *
 * @return void
 */
function mo_saml_display_sso_links_config() {
	$sp_base_url = get_option( Mo_Saml_Options_Enum_Identity_Provider::SP_BASE_URL );
	if ( empty( $sp_base_url ) ) {
		$sp_base_url = site_url();
	}
	?>
	<div class="mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white mo-saml-bootstrap-rounded mo-saml-bootstrap-mt-4">
		<div class="mo-saml-bootstrap-row align-items-top">
			<div class="mo-saml-bootstrap-col-md-12">
				<h4 class="form-head">
					<span class="entity-info"><?php esc_html_e( 'Option 2: SSO Links', 'miniorange-saml-20-single-sign-on' ); ?>
						<a href="https://developers.miniorange.com/docs/saml/wordpress/Redirection-SSO?utm_source=saml_plugin&utm_medium=sso_links_docs&utm_campaign=saml_plugin_internal#SSO-Links" rel="noopener noreferrer" class="mo-saml-bootstrap-text-dark" target="_blank">
							<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
								<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path>
								<path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"></path>
							</svg>
						</a>
					</span>
				</h4>
			</div>
		</div>
		
			<h6 class="mo-saml-bootstrap-mt-4"><?php esc_html_e( 'Use the following link to add on your HTML Pages for users to initiate SSO from the site:', 'miniorange-saml-20-single-sign-on' ); ?></h6>
			
			<div class="mo-saml-bootstrap-row mo-saml-bootstrap-align-items-center mo-saml-bootstrap-mt-4">
			
			<div class="mo-saml-bootstrap-col-md-9 mo-saml-bootstrap-d-inline-flex mo-saml-bootstrap-align-items-center">
				<code class="mo-saml-bootstrap-me-2 mo-saml-bootstrap-rounded mo-saml-bootstrap-p-2 bg-cstm metadata_url_field"><b><a id="sso_link" target="_blank" href="<?php echo esc_url( $sp_base_url ) . '/?option=saml_user_login'; ?>" class="mo-saml-bootstrap-text-dark"><?php echo esc_html( $sp_base_url ) . '/?option=saml_user_login'; ?></a></b></code>
				<i class="icon-copy mo_copy copytooltip mo-saml-bootstrap-rounded-circle" onclick="copyToClipboard(this, '#sso_link', '#sso_link_copy');"><span id="sso_link_copy" class="copytooltiptext"><?php esc_html_e( 'Copy to Clipboard', 'miniorange-saml-20-single-sign-on' ); ?></span></i>
			</div>
			<div class="mo-saml-bootstrap-mt-4"><b><?php esc_html_e( 'Note:', 'miniorange-saml-20-single-sign-on' ); ?> </b><?php esc_html_e( 'If you want to redirect the user to a Page after the authentication, then use the SSO Link as given below:', 'miniorange-saml-20-single-sign-on' ); ?></div>
			<div class="mo-saml-bootstrap-col-md-10 mo-saml-bootstrap-d-inline-flex mo-saml-bootstrap-align-items-center">
				<code class="mo-saml-bootstrap-me-2 mo-saml-bootstrap-rounded mo-saml-bootstrap-p-2 bg-cstm metadata_url_field"><b><a id="sso_link_redirect" target="_blank" href="<?php echo esc_url( $sp_base_url ) . '/?option=saml_user_login&redirect_to=page_url'; ?>" class="mo-saml-bootstrap-text-dark"><?php echo esc_html( $sp_base_url ) . '/?option=saml_user_login&redirect_to=<span class="mo-saml-bootstrap-text-danger">page_url</span>'; ?></a></b></code>
				<i class="icon-copy mo_copy copytooltip mo-saml-bootstrap-rounded-circle" onclick="copyToClipboard(this, '#sso_link_redirect', '#sso_link_redirect_copy');"><span id="sso_link_redirect_copy" class="copytooltiptext"><?php esc_html_e( 'Copy to Clipboard', 'miniorange-saml-20-single-sign-on' ); ?></span></i>
			</div>
			<div class="mo-saml-bootstrap-mt-4"><?php echo wp_kses( __( 'Replace the <span class="mo-saml-bootstrap-text-danger">page_url</span> with the url of the Page.', 'miniorange-saml-20-single-sign-on' ), array( 'span' => array( 'class' => array() ) ) ); ?></div>
		</div>
	</div>
	<?php
}

/**
 * Function to display the shortcode configuration.
 *
 * @return void
 */
function mo_saml_display_shortcode_config() {
	?>
	<div class="mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white mo-saml-bootstrap-rounded mo-saml-bootstrap-mt-4">
		<div class="mo-saml-bootstrap-row align-items-top">
			<div class="mo-saml-bootstrap-col-md-12">
				<h4 class="form-head">
					<span class="entity-info"><?php esc_html_e( 'Option 4: Use a ShortCode', 'miniorange-saml-20-single-sign-on' ); ?>
						<a href="https://developers.miniorange.com/docs/saml/wordpress/Redirection-SSO?utm_source=saml_plugin&utm_medium=sso_links_docs&utm_campaign=saml_plugin_internal#SSO-Links" rel="noopener noreferrer" class="mo-saml-bootstrap-text-dark" target="_blank">
							<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
								<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path>
								<path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"></path>
							</svg>
						</a>
					</span>
				</h4>
			</div>
		</div>
		<div class="prem-info mo-saml-bootstrap-mt-4">
			<div class="prem-icn shortcode-prem-img" ><svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
				<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
				<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
				<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
				<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
				<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
				<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
				<p class="prem-info-text shortcode-text"><?php esc_html_e( 'These options are configurable in the Standard, Premium, Enterprise and All-Inclusive version of the plugin.', 'miniorange-saml-20-single-sign-on' ); ?> <a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '?utm_source=saml_plugin&utm_medium=shortcode_upgrade&utm_campaign=saml_plugin_internal#pricing' ); ?>" target="_blank" class="mo-saml-bootstrap-text-warning"><?php esc_html_e( 'Click here to upgrade', 'miniorange-saml-20-single-sign-on' ); ?></a></p>
			</div>
			<div class="mo-saml-bootstrap-row align-items-top" style="align-items:center;">
				<div class="mo-saml-bootstrap-col-md-8">
					<h6 class="mo-saml-bootstrap-text-secondary"><?php esc_html_e( 'Check this option if you want to add a shortcode to your page', 'miniorange-saml-20-single-sign-on' ); ?></h6>
				</div>
				<div class="mo-saml-bootstrap-col-md-4">
					<input type="checkbox" id="switch" class="mo-saml-switch" disabled /><label class="mo-saml-switch-label cursor-disabled" for="switch"><?php esc_html_e( 'Toggle', 'miniorange-saml-20-single-sign-on' ); ?></label>

				</div>
			</div>
			<p style="padding-left: 10px;" class="prem-note"><i><?php esc_html_e( 'Enable this option to easily add an IDP Login Button on your pages using a shortcode.', 'miniorange-saml-20-single-sign-on' ); ?></i></p>
		</div>
	</div>
	<?php
}

/**
 * Function to display auto-redirection configuration to users.
 *
 * @return void
 */
function mo_saml_display_auto_redirection_config() {
	?>
	<div class="mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white mo-saml-bootstrap-rounded mo-saml-bootstrap-mt-2">
		<div class="mo-saml-bootstrap-row align-items-top">
			<div class="mo-saml-bootstrap-col-md-12">
				<h4 class="form-head">
					<span class="entity-info"><?php esc_html_e( 'Enforce SSO on WordPress site', 'miniorange-saml-20-single-sign-on' ); ?>
						<a href="https://developers.miniorange.com/docs/saml/wordpress/Redirection-SSO?utm_source=saml_plugin&utm_medium=auto_redirection_from_site&utm_campaign=saml_plugin_internal#Auto-Redirection-from-site" rel="noopener noreferrer" class="mo-saml-bootstrap-text-dark" target="_blank">
							<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
								<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
								<path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z" />
							</svg>
						</a>
					</span>
				</h4>
			</div>
		</div>
		<div class="prem-info mo-saml-bootstrap-mt-4">
			<div class="prem-icn auto-redir-prem-img"><svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
				<p class="prem-info-text"><?php esc_html_e( 'Auto-Redirection from site is configurable in Standard, Premium, Enterprise and All-Inclusive versions of the plugin', 'miniorange-saml-20-single-sign-on' ); ?> <a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '?utm_source=saml_plugin&utm_medium=upgrade_site_redirect&utm_campaign=saml_plugin_internal#pricing' ); ?>" target="_blank" class="mo-saml-bootstrap-text-warning"><?php esc_html_e( 'Click here to upgrade', 'miniorange-saml-20-single-sign-on' ); ?></a></p>
			</div>
			<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-2" style="align-items:baseline;">
				<div class="mo-saml-bootstrap-col-md-9 mo_saml_width_fit mo_saml_redirect_desc_font_size " >
					<b ><?php esc_html_e( 'Redirect to IdP if user not logged in [PROTECT COMPLETE SITE]', 'miniorange-saml-20-single-sign-on' ); ?> <span class="mo-saml-bootstrap-text-danger">* </span>: </b>
				</div>
				<div class="mo-saml-bootstrap-col-md-3">
					<input type="checkbox" id="switch" class="mo-saml-switch" disabled /><label class="mo-saml-switch-label cursor-disabled" for="switch"><?php esc_html_e( 'Toggle', 'miniorange-saml-20-single-sign-on' ); ?></label>

				</div>
			</div>
			<p class="mo-saml-bootstrap-mt-2 mo-saml-bootstrap-text-secondary" style="margin:10px"><?php esc_html_e( 'Enable this option to restrict access to logged-in users. If a user is not logged in, they will be redirected to your IdP.', 'miniorange-saml-20-single-sign-on' ); ?></p>
			<hr />
				<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-2" style="align-items:baseline;">
				<div class="mo-saml-bootstrap-col-md-9 mo_saml_width_fit mo_saml_redirect_desc_font_size">
					<b style="margin:10px"><?php esc_html_e( 'Force authentication with your IdP on each login attempt', 'miniorange-saml-20-single-sign-on' ); ?> <span class="mo-saml-bootstrap-text-danger">* </span>: </b>
				</div>
				<div class="mo-saml-bootstrap-col-md-3">
					<input type="checkbox" id="switch" class="mo-saml-switch" disabled /><label class="mo-saml-switch-label cursor-disabled" for="switch"><?php esc_html_e( 'Toggle', 'miniorange-saml-20-single-sign-on' ); ?></label>

				</div>
			</div>
			<p class="mo-saml-bootstrap-text-secondary" style="margin:10px"><?php esc_html_e( 'This option makes users log in to the IdP every time, even if they already have an active session.', 'miniorange-saml-20-single-sign-on' ); ?></p>
		
		</div>
	</div>

	<?php
}

/**
 * Function to display the redirect-from-wp-login-page configuration.
 *
 * @return void
 */
function mo_saml_display_redirect_from_wp_login_config() {
	?>
	<div class="mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white mo-saml-bootstrap-rounded mo-saml-bootstrap-mt-4">
		<div class="mo-saml-bootstrap-row align-items-top">
			<div class="mo-saml-bootstrap-col-md-12">
				<h4 class="form-head">
					<span class="entity-info"><?php esc_html_e( 'Enforce SSO on WordPress Login Page', 'miniorange-saml-20-single-sign-on' ); ?>
						<a href="https://developers.miniorange.com/docs/saml/wordpress/Redirection-SSO?utm_source=saml_plugin&utm_medium=auto_redirection_from_wp-login&utm_campaign=saml_plugin_internal#Auto-Redirection-from-WP-login" rel="noopener noreferrer" class="mo-saml-bootstrap-text-dark" target="_blank">
							<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
								<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
								<path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z" />
							</svg>
						</a>
					</span>
				</h4>
			</div>
		</div>
		<div class="prem-info mo-saml-bootstrap-mt-4">
			<div class="prem-icn auto-redir-prem-img"><svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
				<p class="prem-info-text"><?php esc_html_e( 'Auto-Redirection from WordPress login is configurable in Standard, Premium, Enterprise and All-Inclusive versions of the plugin', 'miniorange-saml-20-single-sign-on' ); ?> <a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '?utm_source=saml_plugin&utm_medium=upgrade_wp-login_redirect&utm_campaign=saml_plugin_internal#pricing' ); ?>" target="_blank" class="mo-saml-bootstrap-text-warning"><?php esc_html_e( 'Click here to upgrade', 'miniorange-saml-20-single-sign-on' ); ?></a></p>
			</div>
			<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-2" style="align-items:baseline;">
				<div class="mo-saml-bootstrap-col-md-7 mo_saml_width_fit mo_saml_redirect_desc_font_size">
					<b><?php esc_html_e( 'Redirect to IdP from WordPress Login Page', 'miniorange-saml-20-single-sign-on' ); ?> <span class="mo-saml-bootstrap-text-danger">* </span>: </b>
				</div>
				<div class="mo-saml-bootstrap-col-md-5">
					<input  type="checkbox" id="switch" class="mo-saml-switch" disabled /><label class="mo-saml-switch-label cursor-disabled" for="switch"><?php esc_html_e( 'Toggle', 'miniorange-saml-20-single-sign-on' ); ?></label>
				</div>
			</div>
			<p class="mo-saml-bootstrap-mt-2 mo-saml-bootstrap-text-secondary" style="margin:10px"><?php esc_html_e( 'Select this option if you want the users visiting any of the following URLs to get redirected to your configured IdP for authentication:', 'miniorange-saml-20-single-sign-on' ); ?></p>
			<h6 style="margin:10px;"><code class="bg-cstm mo-saml-bootstrap-text-dark mo-saml-bootstrap-rounded"><?php echo esc_url( wp_login_url() ); ?></code> <?php esc_html_e( 'or', 'miniorange-saml-20-single-sign-on' ); ?> <code class="bg-cstm mo-saml-bootstrap-text-dark mo-saml-bootstrap-rounded"><?php echo esc_url( admin_url() ); ?></code></h6>

			<hr>
			<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-2" style="align-items:baseline;">
				<div class="mo-saml-bootstrap-col-md-9 mo_saml_redirect_desc_font_size mo_saml_width_fit ">
					<b style="margin:10px;"><?php esc_html_e( 'Allows WordPress login if you are locked out of your IdP', 'miniorange-saml-20-single-sign-on' ); ?> <span class="mo-saml-bootstrap-text-danger">* </span>: </b>
				</div>
				<div class="mo-saml-bootstrap-col-md-3" >
					<input type="checkbox" id="switch" class="mo-saml-switch" disabled /><label class="mo-saml-switch-label cursor-disabled" for="switch"><?php esc_html_e( 'Toggle', 'miniorange-saml-20-single-sign-on' ); ?></label>

				</div>
			</div>
			<p class="mo-saml-bootstrap-mt-3 mo-saml-bootstrap-text-secondary" style="margin:10px"><?php esc_html_e( 'Select this option to enable backdoor login if auto-redirect from WordPress Login is enabled.', 'miniorange-saml-20-single-sign-on' ); ?></p>
		</div>
	</div>
	<?php
}


/**
 * Function to display the relaystate configuration.
 *
 * @return void
 */
function mo_saml_display_relaystate_config() {
	?>
	<div class="mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white mo-saml-bootstrap-rounded mo-saml-bootstrap-mt-4">
		<div class="mo-saml-bootstrap-row align-items-top">
			<div class="mo-saml-bootstrap-col-md-12">
				<h4 class="form-head">
					<span class="entity-info"><?php esc_html_e( 'Option 5: Set Relay State', 'miniorange-saml-20-single-sign-on' ); ?>
						<a href="https://developers.miniorange.com/docs/saml/wordpress/Redirection-SSO?utm_source=saml_plugin&utm_medium=sso_links_docs&utm_campaign=saml_plugin_internal#SSO-Links" rel="noopener noreferrer" class="mo-saml-bootstrap-text-dark" target="_blank">
							<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
								<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path>
								<path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"></path>
							</svg>
						</a>
					</span>
				</h4>
			</div>
		</div>
		<div class="prem-info mo-saml-bootstrap-mt-4">
			<div class="prem-icn shortcode-prem-img"><svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
				<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
				<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
				<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
				<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
				<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
				<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
				<p class="prem-info-text"><?php esc_html_e( 'These options are configurable in the Standard, Premium, Enterprise and All-Inclusive version of the plugin.', 'miniorange-saml-20-single-sign-on' ); ?> <a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '?utm_source=saml_plugin&utm_medium=relaystate_upgrade&utm_campaign=saml_plugin_internal#pricing' ); ?>" target="_blank" class="mo-saml-bootstrap-text-warning"><?php esc_html_e( 'Click here to upgrade', 'miniorange-saml-20-single-sign-on' ); ?></a></p>
			</div>
			<div class="mo-saml-bootstrap-row align-items-top" style="align-items:center;">
				<div class="mo-saml-bootstrap-col-md-3">
					<h6 class="mo-saml-bootstrap-text-secondary"><?php esc_html_e( 'Relay State URL: ', 'miniorange-saml-20-single-sign-on' ); ?></h6>
				</div>
				<div class="mo-saml-bootstrap-col-md-7" >
					<input style="width: 100%;" type="url"  disabled placeholder="<?php esc_url( site_url() ); ?>" /><label class=" cursor-disabled" for="switch"></label>
				</div>
			</div>
			<p style="padding-left: 10px;" class="prem-note"><i>
			<?php
			esc_html_e(
				'Users will always be redirected to this URL after SSO.
			When left blank, the users will be redirected to the same page from where the SSO was initiated.	',
				'miniorange-saml-20-single-sign-on'
			);
			?>
																</i></p>
		</div>
	</div>
	<?php
}