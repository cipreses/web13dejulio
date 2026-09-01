<?php
/**
 * File to display debug logs.
 *
 * @package miniorange-saml-20-single-sign-on\views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Used to show the UI part of the log feature to user screen.
 *
 * @return void
 */
function mo_saml_display_log_page() {
	$debug_log_enabled = Mo_SAML_Logger::mo_saml_is_debugging_enabled() ? 'checked' : '';
	$disabled          = ! Mo_SAML_Logger::mo_saml_is_debugging_enabled() ? 'disabled' : '';
	$delete_disabled   = Mo_SAML_Logger::mo_saml_is_debugging_enabled() ? 'disabled' : '';
	mo_saml_display_plugin_header();
	?>  
	<?php
	$active_tab = mo_saml_get_active_debug_tab();
	mo_saml_display_tabs_troubleshoot_page( $active_tab );
	if ( 'debug-logs' === $active_tab ) {
		?>
	<div class="bg-main-cstm mo-saml-margin-left mo-saml-bootstrap-pb-5">
			<div class="mo-saml-bootstrap-row mo-saml-bootstrap-container-fluid">
				<div class="mo-saml-bootstrap-col-md-8 mo-saml-bootstrap-mt-4">
					<div class="mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white mo-saml-bootstrap-rounded">
						<form action="" method="post" id="mo_saml_logger">
							<?php wp_nonce_field( 'mo_saml_logger' ); ?>
							<input type="hidden" name="option" value="mo_saml_logger" />
							<div class="mo-saml-bootstrap-row">
								<div class="mo-saml-bootstrap-col-md-6">
									<h4><?php esc_html_e( 'Debug Logger Tools', 'miniorange-saml-20-single-sign-on' ); ?></h4>
								</div>
								<div class="mo-saml-bootstrap-col-md-6 mo-saml-bootstrap-text-end">
									<?php
									$server_uri = '';
									if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
										$server_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
									}
									?>
									<a href="<?php echo esc_url( mo_saml_add_query_arg( array( 'tab' => 'save' ), $server_uri ) ); ?>" class="mo-saml-bootstrap-btn btn-cstm mo-saml-bootstrap-ms-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
												<path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z" />
											</svg>&nbsp;<?php esc_html_e( 'Back To Plugin Configuration', 'miniorange-saml-20-single-sign-on' ); ?></a>
								</div>
							</div>
							<div class="form-head"></div>
							<h5 class="mo-saml-bootstrap-mt-4"><b><?php esc_html_e( 'If you are facing any issues with the SSO, please follow these steps for easier debugging:', 'miniorange-saml-20-single-sign-on' ); ?></b></h5>

							<h6 class="mo-saml-bootstrap-mt-4"><b><?php esc_html_e( 'Step 1:', 'miniorange-saml-20-single-sign-on' ); ?></b><?php esc_html_e( ' Enable the Debug Logs option below and reproduce the issue.', 'miniorange-saml-20-single-sign-on' ); ?></h6>
							<div class="mo-saml-bootstrap-row mo-saml-bootstrap-align-items-top mo-saml-bootstrap-mt-4">
								<div class="mo-saml-bootstrap-col-md-7">
									<h6 class="text-secondary"><b><?php esc_html_e( 'miniOrange Debug Logs', 'miniorange-saml-20-single-sign-on' ); ?></b></h6>
								</div>
								<div class="mo-saml-bootstrap-col-md-3 mo-saml-bootstrap-ps-0">
									<input type="checkbox" id="mo_saml_enable_debug_logs" name="mo_saml_enable_debug_logs" class="mo-saml-switch" value="checked" onchange="submit();" <?php echo esc_attr( $debug_log_enabled ); ?> />
									<label class="mo-saml-switch-label" for="mo_saml_enable_debug_logs"></label>
								</div>
							</div>

							<div class="mo-saml-bootstrap-text-center">
								<input type="submit" class="mo-saml-bootstrap-btn btn-cstm mo-saml-bootstrap-mt-4" name="mo_saml_clear_debug_logs" value="<?php esc_attr_e( 'Clear Debug Logs', 'miniorange-saml-20-single-sign-on' ); ?>" title="<?php esc_attr_e( 'Enable debug logs first', 'miniorange-saml-20-single-sign-on' ); ?>" <?php echo esc_attr( $disabled ); ?>>
							</div>
							<div class="call-setup-div mo-saml-bootstrap-mt-4">
								<h6 class="call-setup-heading"><strong>
										<span class="mo-saml-bootstrap-text-danger"><?php esc_html_e( 'Note: ', 'miniorange-saml-20-single-sign-on' ); ?></span><u><?php esc_html_e( 'If your wp-config.php is not writable', 'miniorange-saml-20-single-sign-on' ); ?></u>, <?php esc_html_e( 'follow the steps below to Enable debug logs Manually', 'miniorange-saml-20-single-sign-on' ); ?>
									</strong></h6>

								<ul class="mo-saml-bootstrap-mt-3" style="list-style-type: disc; padding-left: 20px;">
									<li><?php esc_html_e( 'Copy this code', 'miniorange-saml-20-single-sign-on' ); ?> <code>define('<?php echo esc_attr( Mo_SAML_Logger::DEBUG_LOG_CONSTANT ); ?>', true);</code></li>
									<li><?php esc_html_e( 'Paste it in the', 'miniorange-saml-20-single-sign-on' ); ?> <a href="https://wordpress.org/support/article/editing-wp-config-php/" target="_blank">wp-config.php</a>
									<?php esc_html_e( 'file before the line', 'miniorange-saml-20-single-sign-on' ); ?>
									<code>/* That's all, stop editing! Happy publishing. */</code> <?php esc_html_e( 'to enable the miniOrange debug logs.', 'miniorange-saml-20-single-sign-on' ); ?></li>
								</ul>

							</div>

							<h6 class="mo-saml-bootstrap-mt-4"><b><?php esc_html_e( 'Step 2:', 'miniorange-saml-20-single-sign-on' ); ?></b> <?php esc_html_e( ' Download the Debug Log File and Plugin Configurations.', 'miniorange-saml-20-single-sign-on' ); ?></h6>

							<div class="mo-saml-bootstrap-text-center mo-saml-bootstrap-mt-4">
								<input type="submit" class="mo-saml-bootstrap-btn btn-cstm mo-saml-bootstrap-mt-4" name="mo_saml_download_debug_logs" value="<?php esc_attr_e( 'Download Debug Logs', 'miniorange-saml-20-single-sign-on' ); ?>" title="<?php esc_attr_e( 'Enable debug logs first', 'miniorange-saml-20-single-sign-on' ); ?>" <?php echo esc_attr( $disabled ); ?>>
							</div>

							<h6 class="mo-saml-bootstrap-mt-4"><b><?php esc_html_e( 'Step 3:', 'miniorange-saml-20-single-sign-on' ); ?></b> <?php esc_html_e( ' Send the Debug Log File and Plugin Configurations to us at', 'miniorange-saml-20-single-sign-on' ); ?> <a class="mo-saml-bootstrap-text-danger" href="mailto:samlsupport@xecurify.com">samlsupport@xecurify.com</a>.</h6>

							<h6 class="mo-saml-bootstrap-mt-4"><b><?php esc_html_e( 'Step 4:', 'miniorange-saml-20-single-sign-on' ); ?></b> <?php esc_html_e( ' Issue Resolved? Then you can disable the debug logs and delete the Debug Log Files.', 'miniorange-saml-20-single-sign-on' ); ?></h6>

							<div class="mo-saml-bootstrap-text-center mo-saml-bootstrap-mt-4">
								<input type="submit" class="mo-saml-bootstrap-btn btn-cstm mo-saml-bootstrap-mt-4" name="mo_saml_delete_debug_log_files" value="<?php esc_attr_e( 'Delete Debug Log Files', 'miniorange-saml-20-single-sign-on' ); ?>" title="<?php esc_attr_e( 'Disable debug logs first', 'miniorange-saml-20-single-sign-on' ); ?>" <?php echo esc_attr( $delete_disabled ); ?>>
							</div>
						</form>
					</div>
				</div>
				<?php mo_saml_display_support_form(); ?>
			</div>
	</div>
		<?php
	} else {
		?>
		<div class="mo-saml-bootstrap-d-flex mo-saml-bootstrap-text-center mo-saml-bootstrap-pt-3 mo-saml-bootstrap-border-bottom mo-saml-bootstrap-justify-content-center">
			<a href="">FAQs</a>
		</div>
		<?php
	}
}
/**
 * This function returns the active tab in troubleshoot sub-menu.
 *
 * @return string
 */
function mo_saml_get_active_debug_tab() {
	 //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter in the URL for checking tab name doesn't require nonce verification.
	if ( isset( $_GET['tab'] ) ) {
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter in the URL for checking tab name doesn't require nonce verification.
		$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
	} else {
		$active_tab = 'debug-logs';
	}
	return $active_tab;
}
/**
 * Display the troubleshoot tab.
 *
 * @param string $active_tab the selected tab in troubleshoot.
 * @return void
 */
function mo_saml_display_tabs_troubleshoot_page( $active_tab ) {
	?>
	<div class="bg-main-cstm mo-saml-bootstrap-pb-4 mo-saml-margin-left" id="container">
		<div class="mo-saml-bootstrap-d-flex mo-saml-bootstrap-text-center mo-saml-bootstrap-pt-3 mo-saml-bootstrap-border-bottom mo-saml-bootstrap-ps-5" id="mo-saml-tabs"> 

		<?php
		$server_uri = '';
		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$server_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}
		?>
			<a id="sp-setup-tab" class="mo-saml-nav-tab-cstm <?php echo esc_html( 'debug-logs' === $active_tab ? 'mo-saml-nav-tab-active' : '' ); ?>" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'debug-logs' ), $server_uri ) ); ?>"><?php esc_html_e( 'Debug Tools', 'miniorange-saml-20-single-sign-on' ); ?></a>
			<a id="sp-setup-tab" class="mo-saml-nav-tab-cstm" target="_blank" href="https://developers.miniorange.com/docs/saml/wordpress/error-codes"><?php esc_html_e( 'Error Codes', 'miniorange-saml-20-single-sign-on' ); ?></a>
		</div>
	</div>
	<?php
}
