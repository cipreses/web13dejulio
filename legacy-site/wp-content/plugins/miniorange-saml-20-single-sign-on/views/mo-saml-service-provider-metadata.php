<?php
/**
 * File to show service provider metadata.
 *
 * @package  miniorange-saml-20-single-sign-on\views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Function to show the configuration urls including sp_base_url, acs_url, sp_entity_id and sp_metadata_url.
 *
 * @return void
 */
function mo_saml_configuration_steps() {
	$sp_base_url          = site_url();
	$acs_url              = $sp_base_url . '/';
	$configured_sp_entity = get_option( Mo_Saml_Options_Enum_Identity_Provider::SP_ENTITY_ID );
	$sp_entity_id         = $configured_sp_entity ? $configured_sp_entity : $sp_base_url . '/wp-content/plugins/miniorange-saml-20-single-sign-on/';
	$sp_metadata_url      = $sp_base_url . '/?option=mosaml_metadata';
	?>
	<div class="mo-saml-bootstrap-row mo-saml-bootstrap-container-fluid" id="sp-meta-tab-form">
		<div class="mo-saml-bootstrap-col-md-8 mo-saml-bootstrap-mt-4 mo-saml-bootstrap-ms-5">
			<?php
			mo_saml_display_sp_metadata( $sp_entity_id, $acs_url, $sp_metadata_url );
			mo_saml_display_sp_endpoints_config( $sp_base_url, $sp_entity_id );
			?>
		</div>
		<?php mo_saml_display_support_form(); ?>
	</div>
	<?php
}

/**
 * Displays configurable Service Provider Endpoints.
 *
 * @param string $sp_base_url SP base URL for the configuration.
 * @param string $sp_entity_id SP Entity ID for the configuration.
 */
function mo_saml_display_sp_endpoints_config( $sp_base_url, $sp_entity_id ) {
	?>
	<form width="98%" method="post" id="mo_saml_update_idp_settings_form" action="">
		<?php wp_nonce_field( 'mo_saml_update_idp_settings_option' ); ?>
		<input type="hidden" name="option" value="mo_saml_update_idp_settings_option" />
		<div class="mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white mo-saml-bootstrap-rounded mo-saml-bootstrap-mt-4">
			<div class="mo-saml-bootstrap-row align-items-center">
				<div class="mo-saml-bootstrap-col-md-11 entity-info">
					<h4 class="form-head"><?php esc_html_e( 'Service Provider Endpoints', 'miniorange-saml-20-single-sign-on' ); ?></h4>
				</div>
			
			</div>
		<div class="mo-saml-bootstrap-row align-items-center mo-saml-bootstrap-mt-4 mo-saml-bootstrap-mb-4">
		<div class="mo-saml-bootstrap-col-md-3">
			<h6 class="mo-saml-bootstrap-text-secondary">
				<?php esc_html_e( 'SP EntityID / Issuer ', 'miniorange-saml-20-single-sign-on' ); ?>
				<?php mo_saml_display_tooltip( wp_kses( __( 'If you have already shared the above URLs or Metadata with your IdP, do <strong>NOT</strong> change SP EntityID. It might break your existing login flow.', 'miniorange-saml-20-single-sign-on' ), array( 'strong' => array() ) ) ); ?>
			</h6>
		</div>
		<div class="mo-saml-bootstrap-col-md-9 mo-saml-bootstrap-d-flex mo-saml-bootstrap-align-items-center mo-saml-bootstrap-gap-2">
			<input type="text" name="mo_saml_sp_entity_id" title="Please enter a valid value" pattern="[^\s]+\s*$" placeholder="<?php esc_attr_e( 'Enter Service Provider Entity ID', 'miniorange-saml-20-single-sign-on' ); ?>" class="mo-saml-bootstrap-w-100" value="<?php echo esc_attr( $sp_entity_id ); ?>" required>
		</div>
		</div>
			<div class="mo-saml-bootstrap-align-items-center mo-saml-bootstrap-mt-4 mo-saml-bootstrap-rounded prem-info">
				<div class="prem-icn sso-btn-prem-img"><svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
					<p class="prem-info-text"><?php esc_html_e( 'Configurable ACS URL / SP Base URL available in the', 'miniorange-saml-20-single-sign-on' ); ?> <b><?php esc_html_e( 'Paid', 'miniorange-saml-20-single-sign-on' ); ?></b> <?php esc_html_e( 'versions of the plugin.', 'miniorange-saml-20-single-sign-on' ); ?> <a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '?utm_source=saml_plugin&utm_medium=sp_metadata_upgrade&utm_campaign=saml_plugin_internal#pricing' ); ?>" target="_blank" class="mo-saml-bootstrap-text-warning"><?php esc_html_e( 'Click here to upgrade', 'miniorange-saml-20-single-sign-on' ); ?></a></p>
				</div>
				<div class="mo-saml-bootstrap-row mo-saml-bootstrap-align-items-center mo-saml-bootstrap-mb-4">
					<div class="mo-saml-bootstrap-col-md-3">
						<h6 class="mo-saml-bootstrap-text-secondary"><?php esc_html_e( 'SP Base URL :', 'miniorange-saml-20-single-sign-on' ); ?></h6>
					</div>
					<div class="mo-saml-bootstrap-col-md-9 mo-saml-bootstrap-ps-0">
						<input type="text" placeholder="You site base URL" class="mo-saml-bootstrap-w-75 mo-saml-bootstrap-bg-light cursor-disabled" value="<?php echo esc_attr( $sp_base_url ); ?>" disabled="">
					</div>
				</div>
			</div>
			<div class="mo-saml-bootstrap-row mo-saml-bootstrap-align-items-center mo-saml-bootstrap-justify-content-center mo-saml-bootstrap-mt-3">
				<input type="submit" class="btn-cstm mo-saml-bootstrap-bg-info mo-saml-bootstrap-rounded mo-saml-bootstrap-w-25" name="submit" value="<?php esc_html_e( 'Update', 'miniorange-saml-20-single-sign-on' ); ?>">
			</div>
		</div>
	</form>
	<?php
}

/**
 * Displays the Service Provider's Metadata information including Metadata file, Metadata URL and Metadata endpoints.
 *
 * @param string $sp_entity_id SP Entity ID for the configuration.
 * @param string $acs_url SP ACS URL for configuration.
 * @param string $sp_metadata_url SP Metadata URL for configuration.
 */
function mo_saml_display_sp_metadata( $sp_entity_id, $acs_url, $sp_metadata_url ) {
	?>
	<div class="mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white mo-saml-bootstrap-rounded">
		<div class="mo-saml-bootstrap-row align-items-top">
			<div class="mo-saml-bootstrap-col-md-12">
				<h4 class="form-head">
					<span class="entity-info"><?php esc_html_e( 'Provide Metadata to Identity Provider', 'miniorange-saml-20-single-sign-on' ); ?>
						<a href="https://developers.miniorange.com/docs/saml/wordpress/Service-Provider-Metadata?utm_source=saml_plugin&utm_medium=provide_metadata&utm_campaign=saml_plugin_internal" class="mo-saml-bootstrap-text-dark" target="_blank">
							<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
								<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
								<path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z" />
							</svg>
						</a>
					</span>
				</h4>
			</div>
		</div>
		<h5 class="form-head form-head-bar mo-saml-bootstrap-mt-3 mo-saml-bootstrap-mb-0"><?php esc_html_e( 'Provide Metadata URL', 'miniorange-saml-20-single-sign-on' ); ?></h5>
		<div class="mo-saml-bootstrap-row mo-saml-bootstrap-align-items-center mo-saml-bootstrap-mt-4">
			<div class="mo-saml-bootstrap-col-md-3">
				<h6 class="mt-2"><?php esc_html_e( 'Metadata URL :', 'miniorange-saml-20-single-sign-on' ); ?></h6>
			</div>
			<div class="mo-saml-bootstrap-col-md-9 mo-saml-bootstrap-d-inline-flex mo-saml-bootstrap-align-items-center">
				<code class="mo-saml-bootstrap-me-2 mo-saml-bootstrap-rounded mo-saml-bootstrap-p-2 bg-cstm metadata_url_field"><b><a id="sp_metadata_url" target="_blank" href="<?php echo esc_url( $sp_metadata_url ); ?>" class="mo-saml-bootstrap-text-dark"><?php echo esc_html( $sp_metadata_url ); ?></a></b></code>
				<i class="icon-copy mo_copy copytooltip mo-saml-bootstrap-rounded-circle" onclick="copyToClipboard(this, '#sp_metadata_url', '#metadata_url_copy');"><span id="metadata_url_copy" class="copytooltiptext"><?php esc_html_e( 'Copy to Clipboard', 'miniorange-saml-20-single-sign-on' ); ?></span></i>
			</div>
		</div>
		<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-4">
			<div class="mo-saml-bootstrap-col-md-3">
				<h6><?php esc_html_e( 'Metadata XML File :', 'miniorange-saml-20-single-sign-on' ); ?></h6>
			</div>
			<div class="mo-saml-bootstrap-col-md-7">
				<a class="btn-cstm mo-saml-bootstrap-bg-info mo-saml-bootstrap-rounded" onclick="document.forms['mo_saml_download_metadata'].submit();"><?php esc_html_e( 'Download', 'miniorange-saml-20-single-sign-on' ); ?></a>
			</div>
		</div>
		<div class="mo-saml-bootstrap-text-center">
			<div class="mo-saml-bootstrap-mt-4 form-head form-head-bar form-sep"><span class="mo-saml-bootstrap-bg-secondary mo-saml-bootstrap-rounded-circle mo-saml-bootstrap-p-2 mo-saml-bootstrap-text-white"><?php esc_html_e( 'OR', 'miniorange-saml-20-single-sign-on' ); ?></span></div>
		</div>
		<div class="mo-saml-bootstrap-row mo-saml-bootstrap-align-items-baseline">
			<div class="mo-saml-bootstrap-col-md-6">
				<h5 class="form-head form-head-bar mo-saml-bootstrap-mt-5"><?php esc_html_e( 'Note the following to configure the IDP', 'miniorange-saml-20-single-sign-on' ); ?></h5>
			</div>
			<div class="mo-saml-bootstrap-col-md-6 mo-saml-bootstrap-text-end">
				<a href="https://plugins.miniorange.com/wordpress-saml-guides?utm_source=saml_plugin&utm_medium=all_idp_setup_guide_button&utm_campaign=saml_plugin_internal" class="mo-saml-bs-btn btn-cstm mo-saml-bootstrap-ms-3 mo-saml-text-wrap" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
						<path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"></path>
					</svg>&nbsp; <?php esc_html_e( 'All IDP Setup Guides', 'miniorange-saml-20-single-sign-on' ); ?></a>
			</div>
		</div>
		<table class="meta-data-table mo-saml-bootstrap-rounded mo-saml-bootstrap-mt-0">
			<tbody>
				<tr>
					<td><b><?php esc_html_e( 'SP-EntityID / Issuer', 'miniorange-saml-20-single-sign-on' ); ?></b></td>
					<td>
						<table class="mo-saml-bootstrap-w-100">
							<tbody>
								<tr>
									<td><span id="entity_id"><?php echo esc_html( $sp_entity_id ); ?></span></td>
									<td><i class="icon-copy mo_copy copytooltip mo-saml-bootstrap-rounded-circle mo-saml-bootstrap-float-end" onclick="copyToClipboard(this, '#entity_id', '#entity_id_copy');"><span id="entity_id_copy" class="copytooltiptext"><?php esc_html_e( 'Copy to Clipboard', 'miniorange-saml-20-single-sign-on' ); ?></span></i></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td><b><?php esc_html_e( 'ACS (AssertionConsumerService) URL', 'miniorange-saml-20-single-sign-on' ); ?></b></td>
					<td>
						<table class="mo-saml-bootstrap-w-100">
							<tbody>
								<tr>
									<td><span id="base_url"><?php echo esc_html( $acs_url ); ?></span></td>
									<td><i class="icon-copy mo_copy copytooltip mo-saml-bootstrap-rounded-circle mo-saml-bootstrap-float-end" onclick="copyToClipboard(this, '#base_url', '#base_url_copy');"><span id="base_url_copy" class="copytooltiptext"><?php esc_html_e( 'Copy to Clipboard', 'miniorange-saml-20-single-sign-on' ); ?></span></i></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td><b><?php esc_html_e( 'Audience URI', 'miniorange-saml-20-single-sign-on' ); ?></b></td>
					<td>
						<table class="mo-saml-bootstrap-w-100">
							<tbody>
								<tr>
									<td><span id="audience"><?php echo esc_html( $sp_entity_id ); ?></span></td>
									<td><i class="icon-copy mo_copy copytooltip mo-saml-bootstrap-rounded-circle mo-saml-bootstrap-float-end" onclick="copyToClipboard(this, '#audience','#audience_copy');"><span id="audience_copy" class="copytooltiptext"><?php esc_html_e( 'Copy to Clipboard', 'miniorange-saml-20-single-sign-on' ); ?></span></i></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td><b><?php esc_html_e( 'NameID format', 'miniorange-saml-20-single-sign-on' ); ?></b></td>
					<td>
						<table class="mo-saml-bootstrap-w-100">
							<tbody>
								<tr>
									<td><span id="nameid">
											urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified
										</span></td>
									<td><i class="icon-copy mo_copy copytooltip mo-saml-bootstrap-rounded-circle mo-saml-bootstrap-float-end" onclick="copyToClipboard(this, '#nameid', '#nameid_copy');"><span id="nameid_copy" class="copytooltiptext"><?php esc_html_e( 'Copy to Clipboard', 'miniorange-saml-20-single-sign-on' ); ?></span></i></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td><b><?php esc_html_e( 'Recipient URL', 'miniorange-saml-20-single-sign-on' ); ?></b></td>
					<td>
						<table class="mo-saml-bootstrap-w-100">
							<tbody>
								<tr>
									<td><span id="recipient"><?php echo esc_html( $acs_url ); ?></span></td>
									<td><i class="icon-copy mo_copy copytooltip mo-saml-bootstrap-rounded-circle mo-saml-bootstrap-float-end" onclick="copyToClipboard(this, '#recipient','#recipient_copy');"><span id="recipient_copy" class="copytooltiptext"><?php esc_html_e( 'Copy to Clipboard', 'miniorange-saml-20-single-sign-on' ); ?></span></i></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td style="width:40%; padding: 15px;font-weight: 400"><b><?php esc_html_e( 'Destination URL', 'miniorange-saml-20-single-sign-on' ); ?></b></td>
					<td>
						<table class="mo-saml-bootstrap-w-100">
							<tbody>
								<tr>
									<td><span id="destination"><?php echo esc_html( $acs_url ); ?></span></td>
									<td><i class="icon-copy mo_copy copytooltip mo-saml-bootstrap-rounded-circle mo-saml-bootstrap-float-end" onclick="copyToClipboard(this, '#destination','#destination_copy');"><span id="destination_copy" class="copytooltiptext"><?php esc_html_e( 'Copy to Clipboard', 'miniorange-saml-20-single-sign-on' ); ?></span></i></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td class="mo-saml-bootstrap-p-3"><b><?php esc_html_e( 'Default Relay State (Optional)', 'miniorange-saml-20-single-sign-on' ); ?></b></td>
					<td>
						<table class="mo-saml-bootstrap-w-100">
							<tbody>
								<tr>
									<td><a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '#pricing' ); ?>" target="_blank" class="mo-saml-bs-btn btn-cstm mo-saml-bootstrap-ms-3"><?php esc_html_e( 'Premium', 'miniorange-saml-20-single-sign-on' ); ?></a></td>
									<td class="mo-saml-bootstrap-text-end"><svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td class="mo-saml-bootstrap-p-3"><b><?php esc_html_e( 'Certificate (Optional)', 'miniorange-saml-20-single-sign-on' ); ?></b></td>
					<td>
						<table class="mo-saml-bootstrap-w-100">
							<tbody>
								<tr>
									<td><a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '#pricing' ); ?>" target="_blank" class="mo-saml-bs-btn btn-cstm mo-saml-bootstrap-ms-3"><?php esc_html_e( 'Premium', 'miniorange-saml-20-single-sign-on' ); ?></a></td>
									<td class="mo-saml-bootstrap-text-end"><svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		<!-- <h6>Provide this metadata URL to your Identity Provider or download the .xml file to upload it in your idp:</h6> -->

	</div>
	<form name="mo_saml_download_metadata" method="post" action="">
		<?php wp_nonce_field( 'mosaml_metadata_download' ); ?>
		<input type="hidden" name="option" value="mosaml_metadata_download" />

	</form>
	<?php
}
