<?php
/**
 * This file takes care of rendering the support form.
 *
 * @package miniorange-saml-20-single-sign-on\views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The function displays the support form in the plugin.
 *
 * @param boolean $display_attrs flag to determine to display attributes or not.
 */
function mo_saml_display_support_form( $display_attrs = false ) {
	?>
	<div class="mo-saml-bootstrap-col-md-3 mo-saml-bootstrap-mt-4 mo-saml-bootstrap-ps-0">
		<?php

		if ( $display_attrs && ! empty( get_option( Mo_Saml_Options_Test_Configuration::TEST_CONFIG_ATTRS ) ) ) {
			mo_saml_display_attrs_list();
		} else {

			?>
			<div class="mo-saml-bootstrap-bg-white mo-saml-bootstrap-text-center shadow-cstm mo-saml-bootstrap-rounded contact-form-cstm">
				<form method="post" action="">
					<?php wp_nonce_field( 'mo_saml_contact_us_query_option' ); ?>
					<input type="hidden" name="option" value="mo_saml_contact_us_query_option" />

					<div class="contact-form-head">
						<div style="background: #f7f7f7;padding: 1rem;border-radius: 12px;">
						<p class="mo-saml-bootstrap-h5" style="color: #1c1c1c;"><?php esc_html_e( 'We\'re Here to Help 24x7', 'miniorange-saml-20-single-sign-on' ); ?> <br><span class="mo-saml-small-text"><?php esc_html_e( 'Send your queries or custom requirements', 'miniorange-saml-20-single-sign-on' ); ?><br></span></p>
						<span class="mo-saml-bootstrap-h6 info-block"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10" fill="none">
							<g clip-path="url(#clip0_7968_97316)">
							<path d="M5.76203 6.9035C5.84808 6.94302 5.94503 6.95205 6.0369 6.9291C6.12877 6.90615 6.21009 6.85259 6.26745 6.77725L6.41536 6.5835C6.49299 6.48 6.59364 6.396 6.70935 6.33814C6.82507 6.28028 6.95266 6.25016 7.08203 6.25016H8.33205C8.55305 6.25016 8.76498 6.33796 8.92127 6.49424C9.07755 6.65052 9.16534 6.86248 9.16534 7.0835V8.33352C9.16534 8.55452 9.07755 8.76645 8.92127 8.92273C8.76498 9.07902 8.55305 9.1668 8.33205 9.1668C6.3429 9.1668 4.43525 8.37666 3.02873 6.97013C1.6222 5.5636 0.832031 3.65595 0.832031 1.66682C0.832031 1.44581 0.919824 1.23385 1.07611 1.07757C1.23239 0.921289 1.44435 0.833496 1.66536 0.833496H2.91536C3.13637 0.833496 3.34834 0.921289 3.50462 1.07757C3.6609 1.23385 3.7487 1.44581 3.7487 1.66682V2.91682C3.7487 3.0462 3.71857 3.17379 3.66072 3.2895C3.60286 3.40522 3.51886 3.50587 3.41536 3.5835L3.22036 3.72975C3.14387 3.78815 3.08995 3.87124 3.06777 3.96489C3.0456 4.05854 3.05652 4.15698 3.0987 4.2435C3.66815 5.4001 4.6047 6.3355 5.76203 6.9035Z" stroke="#155DFC" stroke-width="1.11429" stroke-linecap="round" stroke-linejoin="round"/>
							</g>
							<defs>
							<clipPath id="clip0_7968_97316">
							<rect width="10" height="10" fill="white"/>
							</clipPath>
							</defs>
							</svg> +1 978 658 9387</span>
								<span class="mo-saml-bootstrap-h6 info-block"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10" fill="none">
								<path d="M9.16536 2.9165L5.41911 5.30275C5.29199 5.37659 5.14759 5.41548 5.00057 5.41548C4.85356 5.41548 4.70916 5.37659 4.58203 5.30275L0.832031 2.9165" stroke="#155DFC" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M8.33203 1.6665H1.66536C1.20513 1.6665 0.832031 2.0396 0.832031 2.49984V7.49984C0.832031 7.96007 1.20513 8.33317 1.66536 8.33317H8.33203C8.79227 8.33317 9.16537 7.96007 9.16537 7.49984V2.49984C9.16537 2.0396 8.79227 1.6665 8.33203 1.6665Z" stroke="#155DFC" stroke-width="1.16667" stroke-linecap="round" stroke-linejoin="round"/>
							</svg><a href="mailto:samlsupport@xecurify.com"> samlsupport@xecurify.com </a></span>
					</div>
	</div>
					<div class="contact-form-body mo-saml-bootstrap-p-3">
						<h6 class="saml-text"><?php esc_html_e( 'Send us your query via Email', 'miniorange-saml-20-single-sign-on' ); ?></h6>
						<input type="email" id="mo_saml_support_email" placeholder="<?php esc_html_e( 'Enter your email', 'miniorange-saml-20-single-sign-on' ); ?>" class="mo_saml_table_textbox mo-saml-bootstrap-mt-4" name="mo_saml_contact_us_email" value="<?php echo esc_attr( ( empty( get_option( Mo_Saml_Customer_Constants::ADMIN_EMAIL ) ) ) ? get_option( 'admin_email' ) : get_option( Mo_Saml_Customer_Constants::ADMIN_EMAIL ) ); ?>" required="">
						<textarea class="mo_saml_table_textbox mo-saml-bootstrap-mt-4" name="mo_saml_contact_us_query" rows="4" style="resize: vertical;" required="" placeholder="<?php esc_html_e( 'Write your query here', 'miniorange-saml-20-single-sign-on' ); ?>" id="mo_saml_query"></textarea>
						<input type="submit" value="<?php esc_html_e( 'Send Email', 'miniorange-saml-20-single-sign-on' ); ?>" class="mo-saml-bs-btn support_btn mo-saml-bootstrap-mt-3 mo-saml-bootstrap-w-100">
					</div>
				</form>
				<div class="divider"><span><?php esc_html_e( 'OR', 'miniorange-saml-20-single-sign-on' ); ?></span></div>
				<form method="post" action="">
					<?php wp_nonce_field( 'mo_saml_callback_request_query_option' ); ?>
					<input type="hidden" name="option" value="mo_saml_callback_request_query_option" />
					<div class="contact-form-body mo-saml-bootstrap-p-3">
						<h6 class="saml-text"><?php esc_html_e( 'Request Callback', 'miniorange-saml-20-single-sign-on' ); ?></h6>
						<input type="hidden" name="saml_setup_call" value="true" />
						<input type="tel" id="contact_us_phone" pattern="^\+?[0-9]{1,4}[\s]?[0-9]{4,12}$" class="mo_saml_table_textbox mo-saml-bootstrap-mt-4" name="mo_saml_contact_us_phone" required value="<?php echo esc_attr( empty( get_option( Mo_Saml_Customer_Constants::ADMIN_PHONE ) ) ? '' : get_option( Mo_Saml_Customer_Constants::ADMIN_PHONE ) ); ?>" placeholder="<?php esc_attr_e( 'Enter your Phone Number', 'miniorange-saml-20-single-sign-on' ); ?>">
						<input type="submit" value="<?php esc_html_e( 'Send Request', 'miniorange-saml-20-single-sign-on' ); ?>" class="mo-saml-bs-btn support_btn mo-saml-bootstrap-mt-3 mo-saml-bootstrap-w-100">
					</div>
				</form>
				<div class="call-setup-notice">
					<p class="mo-saml-small-text">
						<span class="dashicons dashicons-info"></span>
						<?php esc_html_e( 'Our support team will contact you soon after you submit your request. Please make sure your phone number is reachable.', 'miniorange-saml-20-single-sign-on' ); ?>
					</p>
				</div>

				<br>
			</div>

			<?php
		}
		//PHPCS:ignore -- WordPress.Security.NonceVerification.Recommended -- GET parameter for checking the current page name from the URL doesn't require nonce verification.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		mo_saml_display_keep_settings_intact_section();
		mo_saml_display_suggested_idp_integration();
		?>
	</div>

	<?php
}
